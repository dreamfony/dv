<?php

namespace Drupal\activity_creator\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\file\Entity\File;

/**
 * Defines the Activity entity.
 *
 * @ingroup activity_creator
 *
 * @ContentEntityType(
 *   id = "activity",
 *   label = @Translation("Activity"),
 *   bundle_label = @Translation("Activity type"),
 *   handlers = {
 *     "storage" = "Drupal\activity_creator\ActivityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\activity_creator\ActivityListBuilder",
 *     "views_data" = "Drupal\activity_creator\Entity\ActivityViewsData",
 *     "translation" = "Drupal\activity_creator\ActivityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\activity_creator\Form\ActivityForm",
 *       "add" = "Drupal\activity_creator\Form\ActivityForm",
 *       "edit" = "Drupal\activity_creator\Form\ActivityForm",
 *       "delete" = "Drupal\activity_creator\Form\ActivityDeleteForm",
 *     },
 *     "access" = "Drupal\activity_creator\ActivityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\activity_creator\ActivityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "activity",
 *   data_table = "activity_field_data",
 *   revision_table = "activity_revision",
 *   revision_data_table = "activity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer activity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/activity/{activity}",
 *     "add-page" = "/admin/structure/activity/add",
 *     "add-form" = "/admin/structure/activity/add/{activity_type}",
 *     "edit-form" = "/admin/structure/activity/{activity}/edit",
 *     "delete-form" = "/admin/structure/activity/{activity}/delete",
 *     "version-history" = "/admin/structure/activity/{activity}/revisions",
 *     "revision" = "/admin/structure/activity/{activity}/revisions/{activity_revision}/view",
 *     "revision_revert" = "/admin/structure/activity/{activity}/revisions/{activity_revision}/revert",
 *     "translation_revert" = "/admin/structure/activity/{activity}/revisions/{activity_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/activity/{activity}/revisions/{activity_revision}/delete",
 *     "collection" = "/admin/structure/activity",
 *   },
 *   bundle_entity_type = "activity_type",
 *   field_ui_base_route = "entity.activity_type.edit_form"
 * )
 */
class Activity extends RevisionableContentEntityBase implements ActivityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the activity owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUser() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUserId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Activity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Activity is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Get related entity url.
   *
   * @return \Drupal\Core\Url|string
   *   Returns empty string or URL object of related entity canonical url.
   */
  public function getRelatedEntityUrl() {
    $link = "";
    $related_object = $this->get('field_activity_entity')->getValue();
    if (!empty($related_object)) {
      $entity = entity_load($related_object['0']['target_type'], $related_object['0']['target_id']);
      if (!empty($entity)) {
        /** @var \Drupal\Core\Url $link */
        $link = $entity->urlInfo('canonical');
      }
    }
    return $link;
  }

  /**
   * Get related entity.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getRelatedEntity() {
    $related_object = $this->get('field_activity_entity')->getValue();
    if (!empty($related_object)) {
      $entity = entity_load($related_object['0']['target_type'], $related_object['0']['target_id']);
      if (!empty($entity)) {
        /** @var \Drupal\Core\Url $link */
        return $entity;
      }
    }
    return FALSE;
  }

  /**
   * Get related entity attachments.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getRelatedEntityAttachments() {
    $attachments = [];

    $related_object = $this->get('field_activity_entity')->getValue();
    if (!empty($related_object)) {
      /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
      $entity = entity_load($related_object['0']['target_type'], $related_object['0']['target_id']);
      if (!empty($entity)) {
        $attachments_field = 'field_q_attachments';
        /** @var \Drupal\Core\Url $link */
        \Drupal::moduleHandler()->alter('attachments_field', $attachments_field);
        if($attachments_field && $entity->hasField($attachments_field)) {
          /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $files */
          $files = $entity->{$attachments_field};
          foreach ($files as $file_item) {
            /** @var \Drupal\multiversion\FileItem $fid */
            $file = File::load($file_item->getValue()['target_id']);
            if($file) {
              $attachments[] = \Drupal::service('file_system')->realpath($file->getFileUri());
            }
          }
        }
      }
    }

    return $attachments;
  }

  /**
   * Get destinations.
   */
  public function getDestinations() {
    $values = [];
    $field_activity_destinations = $this->field_activity_destinations;
    if(isset($field_activity_destinations)){
      $destinations = $field_activity_destinations->getValue();
      foreach ($destinations as $key => $destination) {
        $values[] = $destination['value'];
      }
    }
    return $values;
  }

  /**
   * Get recipient.
   * Assume that activity can't have recipient group and user at the same time.
   * @TODO: Split it to two separate functions.
   */
  public function getRecipient() {
    $value = NULL;

    $field_activity_recipient_user = $this->field_activity_recipient_user;
    if (isset($field_activity_recipient_user)) {
      $recipient_user = $field_activity_recipient_user->getValue();
      if (!empty($recipient_user)) {
        $recipient_user['0']['target_type'] = 'user';
        return $recipient_user;
      }
    }

    $field_activity_recipient_group = $this->field_activity_recipient_group;
    if (isset($field_activity_recipient_group)) {
      $recipient_group = $field_activity_recipient_group->getValue();
      if (!empty($recipient_group)) {
        $recipient_group['0']['target_type'] = 'group';
        return $recipient_group;
      }
    }

    return $value;
  }

  public static function getActivityIdByHash($hash) {
    $query = \Drupal::entityQuery('activity')
      ->condition('field_activity_hash', $hash);
    $activity_ids = $query->execute();

    return reset($activity_ids);
  }

  public static function getActivityEntityByHash($hash) {
    $activity_id = static::getActivityIdByHash($hash);
    if ($activity_id){
      return \Drupal::entityTypeManager()->getStorage('activity')->load($activity_id);
    }
    return false;
  }

  public function setModerationState($state) {
    $this->set('moderation_state', $state);
  }

  public function getActivityState() {
    return $this->moderation_state->value;
  }

  public function getMessageTypeId() {
    /** @var \Drupal\message\Entity\Message $message */
    $message = \Drupal::entityTypeManager()->getStorage('message')->load($this->field_activity_message->target_id);
    return $message->getTemplate()->id();
  }

}
