## Mailing List

All related code should be placed in [dmt_mailing_list](../../modules/custom/dmt_mailing_list/dmt_mailing_list.info.yml) module.

#### Mailing List is a bundle of group entity
 
##### **[Fields](http://local.dv.com/admin/group/types/manage/mailing_list/fields)**:

- Group Comments
  - Is used to store comments on particular Survey probably just for moderation purposes. Comments are handled by [ajax_comments](../../modules/custom/ajax_comments/ajax_comments.info.yml) module and [dmt_group_comments](../../modules/custom/dmt_group_comments/dmt_group_comments.info.yml) module.
- Panelizer
  - Is used for storing current view mode for the group. 
    Mailing List has 2 view modes:
    - Mailing List Default - Used for survey creation form
    - Default - Used for Viewing the survey
    
##### **[Group content plugins](http://local.dv.com/admin/group/types/manage/mailing_list/content)**:
- Group node (Content) 
  - Is used to add [content](content.md) to the group.
- Group membership
  - Member roles:
    - [Organisations](organisations.md)
    - Edit Mailing List	- Special role used to allow editing - not sure where it is used right now
    - Owner - Is the owner/creator of the survey	
    - Moderator - Survey moderators

##### **[Mailing list workflow](http://local.dv.com/admin/config/workflow/workflows/manage/mailing_list_workflow)**:

###### States

- Draft [draft]
- Email [email]
- Approved [approved]
- Archived [archived]
- Spam [spam]

###### Transitions

- **Create new Draft** [create_new_draft]
  - from: [draft]
  - to: [draft]
  - Create new Draft transition is never used find a way to remove it if possible

- **Send Email** [send_email plugin](../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/SendEmailTransition.php)
  - from: [draft]
  - to: [email]
  - uc:
    - [x] **own** - triggers this transition
    - [x] **sys** - validates [mailing_list] contains > 0 [node](../entities/node.md) [content] and > 0 recipients
    - [x] **sys** - remove administrator role from the [mailing_list] [group](../entities/group.md)
    - [x] **sys** - send [mailing_list_needs_approval] [message](../entities/message.md)

- **Approve Sending**	[approve plugin](../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/ApproveSendingTransition.php)
  - from: [email]
  - to: [approved]
  - uc:
    - [x] **mod** - triggers this transition
    - [x] **sys** - validates [mailing_list] contains > 0 [node](../entities/node.md) [content] and > 0 recipients
    - [x] **sys** - gets all the content form group of type [group_node:content]
    - [x] **sys** - foreach group content gets referenced entity [node] of type [content] and triggers [create_activity_action]
    - [x] **sys** - triggers [close_mailing_list_ticket] for the [group](../entities/group.md) [mailing_list]

- **Archive** [archive]
  - from: [draft], [email], [approved]
  - to: [archived]

- **Restore to Draft**	[restore_to_draft]
  - from: [send_email]
  - to: [draft]
  - uc:
    - [ ] **sys** - adds administrator role to user that created [mailing_list]

- **Spam** [spam]
  - from: [draft]
  - to: [spam]
  - uc:
    - [ ] **sys** - adds mailing list to queue to be deleted    
