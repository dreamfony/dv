Default moderation groups are created during site install (group type - "moderation")
In which moderation group ticket will show is an option of the related "message" entity (mailing_list_needs_approval)
  // @todo: make states for this form element hide if activity_action is not moderation_action
Moderation Workflow defines states of moderation tickets

Moderation tickets are activity entites of type "activity_moderation" and are created when Mailing list state is transitioned to email

Code that Creates activity in pending state:
$activity_moderation = $this->activityModerationManager->createInstance('open_moderation_ticket');
$activity_moderation->createModerationActivity($group);

When the Mailing list is approved, related Moderation activity is set to "resolved" state

--


TODO set up basic moderation group features
- add moderation group memeber roles
- main stream is a view of activites that are not closed
