<?php

namespace Drupal\dmt_moderation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\dmt_moderation\ModerationStateLinks;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Demo' block.
 *
 * @Block(
 *   id = "moderation_state_switch",
 *   admin_label = @Translation("Switch Moderation State"),
 * )
 */
class ModerationTransitionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\dmt_moderation\ModerationStateLinks
   */
  protected $moderationStateLinks;

  /**
   * ModerationTransitionBlock constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\dmt_moderation\ModerationStateLinks $moderationStateLinks
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
      $container->get('dmt_moderation.moderation_state_links')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $links = $this->moderationStateLinks->getLinks($this->getEntityFromRoute());
    $content['block_content'] = $links;
    return $content;
  }

  protected function getEntityFromRoute() {
    $route_object = \Drupal::routeMatch();
    foreach ($route_object->getParameters() as $parameter) {
      if($parameter instanceof ContentEntityInterface) {
        return $parameter;
      }
    }
  }

  public function getCacheTags() {
    // When entity changes rebuild block block will rebuild
    if ($entity = $this->getEntityFromRoute()) {
      /** @var ContentEntityInterface $entity */
      //if there is an entity add its cachetag
      return Cache::mergeTags(parent::getCacheTags(), array($entity->getEntityTypeId() . ':' . $entity->id()));
    }

    return parent::getCacheTags();
  }

  public function getCacheContexts() {
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param bool $return_as_object
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $entity = $this->getEntityFromRoute();

    if($entity && $this->moderationStateLinks->getLinksAccess($entity)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
