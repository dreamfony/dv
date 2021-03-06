<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityAction\CreateActivityAction.
 */

namespace Drupal\dmt_mailing_list\Plugin\ActivityModeration;

use Drupal\activity_moderation\ActivityModerationBase;

/**
 * Provides a 'ModerationAction' activity action.
 *
 * @ActivityModeration(
 *  id = "close_mailing_list_ticket",
 *  label = @Translation("Close Mailing List Moderation Ticket"),
 *  message_type_id = "mailing_list_needs_aproval"
 * )
 */
class CloseModerationTicket extends ActivityModerationBase {



}
