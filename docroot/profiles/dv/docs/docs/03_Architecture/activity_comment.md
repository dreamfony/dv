## Activity Comment

Comments attached to [Content](content.md) node bundle.

We are using forked [Ajax Comments](../../modules/custom/ajax_comments/ajax_comments.info.yml) contrib. module with some minor changes.

Every activity entity from bundle [mailing_list_activity](mailing_list_activity.md) 
when [inserted](../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/MailingListActivityStateMachine.php) 
creates a comment in this bundle that references created activity form Comment Activity field.

That comment is used as a place holder for answers and other comments for that activity.

**[Fields](http://local.dv.com/admin/structure/comment/manage/activity/fields):**

- Comment Activity
  - Entity reference to activity entity
