<?php

namespace Drupal\moderation_state_machine\Plugin\ExtraField\FieldFormatter;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldFormatterBase;
use Drupal\moderation_state_machine\ModerationStateLinks;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example Extra field formatter.
 *
 * @ExtraFieldFormatter(
 *   id = "moderation_transition",
 *   label = @Translation("Moderation Transition"),
 *   bundles = {
 *     "group.*",
 *     "comment.*",
 *     "activity.*"
 *   },
 *   weight = -30,
 *   visible = true
 * )
 */
class ModerationTransitionField extends ExtraFieldFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\moderation_state_machine\ModerationStateLinks
   */
  protected $moderationStateLinks;

  /**
   * ModerationTransitionBlock constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\moderation_state_machine\ModerationStateLinks $moderationStateLinks
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModerationStateLinks $moderationStateLinks) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moderationStateLinks = $moderationStateLinks;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('moderation_state_machine.moderation_state_links')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(EntityInterface $entity) {
    $links = $this->moderationStateLinks->getLinks($entity);
    $rendered_links = render($links);
    return $rendered_links;
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    if($this->moderationStateLinks->getLinksAccess($entity)) {
      $markup = ['#markup' => $this->build($entity)];
      return $markup;
    }

    return FALSE;
  }

}
