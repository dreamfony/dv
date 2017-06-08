<?php

namespace Drupal\dmt_mailing_list\Controller;

use Drupal\ajax_comments\Ajax\ajaxCommentsScrollToElementCommand;
use Drupal\ajax_comments\TempStore;
use Drupal\ajax_comments\Utility;
use Drupal\comment\CommentInterface;
use Drupal\comment\Controller\CommentController;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\BeforeCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for AJAX comments routes.
 */
class ReplyLinkController extends ControllerBase {

  /**
   * Class prefix to apply to each comment.
   *
   * @var string
   *   A prefix used to build class name applied to each comment.
   */
  public static $commentClassPrefix = 'js-ajax-comments-id-';

  /**
   * Service to turn render arrays into HTML strings.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The TempStore service.
   *
   * This service stores temporary data to be used across HTTP requests.
   *
   * @var \Drupal\ajax_comments\TempStore
   */
  protected $tempStore;

  /**
   * Constructs a AjaxCommentsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The render service.
   * @param \Drupal\ajax_comments\TempStore $temp_store
   *   The TempStore service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, RendererInterface $renderer, TempStore $temp_store) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->renderer = $renderer;
    $this->tempStore = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('ajax_comments.temp_store')
    );
  }

  /**
   * Get the prefix for a selector class for an individual comment.
   *
   * @return string
   *   The portion of a CSS class name that prepends the comment ID.
   */
  public static function getCommentSelectorPrefix() {
    return '.' . static::$commentClassPrefix;
  }

  /**
   * Builds ajax response to display a form to reply to another comment.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity this comment belongs to.
   * @param string $field_name
   *   The field_name to which the comment belongs.
   * @param int $pid
   *   The parent comment's comment ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The Ajax response, or a redirect response if not using ajax.
   *
   * @see \Drupal\comment\Controller\CommentController::getReplyForm()
   */
  public function reply(Request $request, EntityInterface $entity, $field_name, $pid) {
    $is_ajax = Utility::isAjaxRequest($request);

    if ($is_ajax) {
      $response = new AjaxResponse();

      // Get the selectors.
      $selectors = $this->tempStore->getSelectors($request, $overwrite = TRUE);
      $wrapper_html_id = $selectors['wrapper_html_id'];

      // Check the user's access to reply.
      // The user should not have made it this far without proper permission,
      // but adding this access check as a fallback.
      $this->replyAccess($request, $response, $entity, $field_name, $pid);

      // If $this->replyAccess() added any commands to the AjaxResponse,
      // it means that access was denied, so we should NOT ajax load the
      // reply form. Instead, return the response with the error messages
      // immediately.
      if (!empty($response->getCommands())) {
        return $response;
      }

      // Remove any existing status messages in the comment field,
      // if applicable.
      $response->addCommand(new RemoveCommand($wrapper_html_id . ' .js-ajax-comments-messages'));

      // Build the comment entity form.
      // This approach is very similar to the one taken in
      // \Drupal\comment\CommentLazyBuilders::renderForm().
      $comment = $this->entityTypeManager()->getStorage('comment')->create(array(
        'entity_id' => $entity->id(),
        'pid' => $pid,
        'entity_type' => $entity->getEntityTypeId(),
        'field_name' => $field_name,
      ));
      // Build the comment form.
      $form = $this->entityFormBuilder()->getForm($comment);
      $response->addCommand(new AfterCommand(static::getCommentSelectorPrefix() . $pid, $form));

      // Don't delete the tempStore variables here; we need them
      // to persist for the saveReply() method, where the form returned
      // here will be submitted.
      // Instead, return the response without calling $this->tempStore->deleteAll().
      return $response;
    }
    else {
      // If the user attempts to access the comment reply form with JavaScript
      // disabled, degrade gracefully by redirecting to the core comment
      // reply form.
      $redirect = Url::fromRoute(
        'comment.reply',
        [
          'entity_type' => $entity->getEntityTypeId(),
          'entity' => $entity->id(),
          'field_name' => $field_name,
          'pid' => $pid,
        ]
      )
        ->setAbsolute()
        ->toString();
      $response = new RedirectResponse($redirect);
      return $response;
    }
  }

  /**
   * Builds ajax response to save a submitted reply to another comment.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity this comment belongs to.
   * @param string $field_name
   *   The field_name to which the comment belongs.
   * @param int $pid
   *   The parent comment's comment ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The Ajax response.
   */
  public function saveReply(Request $request, EntityInterface $entity, $field_name, $pid) {
    $response = new AjaxResponse();

    // Check the user's access to reply.
    // The user should not have made it this far without proper permission,
    // but adding this access check as a fallback.
    $this->replyAccess($request, $response, $entity, $field_name, $pid);

    // If $this->replyAccess() added any commands to the AjaxResponse,
    // it means that access was denied, so we should NOT submit the form
    // and rebuild the comment field. Instead, return the response
    // immediately and abort the save.
    if (!empty($response->getCommands())) {
      return $response;
    }

    // Build a dummy comment entity to pass to $this->save(), which will use
    // it to rebuild the comment entity form to trigger form submission.
    // @code
    // $form = $this->entityFormBuilder()->getForm($comment, 'default', ['editing' => TRUE]);
    // @endcode
    // Note that this approach will correctly process the form submission
    // even though we are passing in an empty, dummy comment, because two steps
    // later in the call stack, \Drupal\Core\Form\FormBuilder::buildForm() is
    // called, and it checks the current request object for form submission
    // values if there aren't any in the form state, yet:
    // @code
    // $input = $form_state->getUserInput();
    // if (!isset($input)) {
    //   $input = $form_state->isMethodType('get') ? $request->query->all() : $request->request->all();
    //   $form_state->setUserInput($input);
    // }
    // @endcode
    // This approach is very similar to the one taken in
    // \Drupal\comment\CommentLazyBuilders::renderForm().
    $comment = $this->entityTypeManager()->getStorage('comment')->create(array(
      'entity_id' => $entity->id(),
      'pid' => $pid,
      'entity_type' => $entity->getEntityTypeId(),
      'field_name' => $field_name,
    ));
    // Rebuild the form to trigger form submission.
    return $this->save($request, $comment);
  }

  /**
   * Check the user's permission to post a comment.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The response object being built.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity this comment belongs to.
   * @param string $field_name
   *   The field_name to which the comment belongs.
   * @param int $pid
   *   (optional) Some comments are replies to other comments. In those cases,
   *   $pid is the parent comment's comment ID. Defaults to NULL.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response, if access is denied.
   */
  public function replyAccess(Request $request, AjaxResponse $response, EntityInterface $entity, $field_name, $pid = NULL) {

    // Get the selectors.
    $selectors = $this->tempStore->getSelectors($request);
    $wrapper_html_id = $selectors['wrapper_html_id'];
    $form_html_id = $selectors['form_html_id'];

    $access = CommentController::create(\Drupal::getContainer())
      ->replyFormAccess($entity, $field_name, $pid);

    // If access is not explicitly allowed, then we forbid it.
    if (!$access->isAllowed()) {
      $selector = $form_html_id;
      if (empty($selector)) {
        $selector = $wrapper_html_id;
      }
      drupal_set_message(t('You do not have permission to post a comment.'), 'error');
      // If this is a new top-level comment (not a reply to another comment so
      // no $pid), replace the comment form with the error message.
      if (empty($pid)) {
        // Remove any existing status messages in the comment field,
        // if applicable.
        $response->addCommand(new RemoveCommand($wrapper_html_id . ' .js-ajax-comments-messages'));
        // Add the error message.
        $response = $this->addMessages($request, $response, $selector, 'replace');
      }
      // Otherwise, if this is a reply, reload the field without reply links
      // or a reply form, and insert the error message at the top.
      else {
        $response = $this->buildCommentFieldResponse($request, $response, $entity, $field_name, $pid);
        // The wrapper_html_id should have been updated when
        // $this->buildCommentFieldResponse() was called, so retrieve
        // the updated selector values for use in building the response.
        $selectors = $this->tempStore->getSelectors($request);
        $selector = $selectors['wrapper_html_id'];
        $response = $this->addMessages($request, $response, $selector, 'prepend');
      }

      // Clear out the tempStore variables.
      $this->tempStore->deleteAll();

      return $response;
    }
  }

}
