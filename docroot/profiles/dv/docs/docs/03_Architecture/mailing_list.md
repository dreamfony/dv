# Mailing List

All related code should be placed in [dmt_mailing_list](../../../modules/custom/dmt_mailing_list/dmt_mailing_list.info.yml) module.

## Tests

[Survey.feature](../../../../../tests/behat/features/features/Survey/Survey.feature)

## Pages

- **Create Survey page**
  - Survey header - region
    - Edit Title - link
    - Title
  - Survey recipients add - region
    - Add recipients - autocomplete field
    - Add recipients - button
  - Survey recipients list - region
    - Recipients - view
      - Remove recipient - link - view row
      - Recipient - view row
        - Organisation Profile - display mode
          - Name - field
          - Address - field
  - Survey content add - region
    - Survey content add - form
      - Content - field
      - Type - field
      - Attachments - field
      - Submit - button
  - Survey content list
    - Survey content - view
      - Content Teaser - display mode
        - Edit - link
        - Remove - link
        - Content - field view
        - Type - field view
        - Attachments - field view
- **Survey view page**
  - View of all recipients
  
  
  
## Mailing List is a bundle of group entity
 
#### **[Fields](http://local.dv.com/admin/group/types/manage/mailing_list/fields)**

- Group Comments
  - Is used to store comments on particular Survey probably just for moderation purposes. Comments are handled by [ajax_comments](../../../modules/custom/ajax_comments/ajax_comments.info.yml) module and [dmt_group_comments](../../../modules/custom/dmt_group_comments/dmt_group_comments.info.yml) module.
- Panelizer
  - Is used for storing current view mode for the group. 

#### **[View modes](http://local.dv.com/admin/group/types/manage/mailing_list/display)**
- Full
  - Full view mode is split in to 2 sub modes with [panelizer](https://www.drupal.org/project/panelizer) contrib. module
    - Mailing List Default - Used for survey creation ui
      - see [Content](content.md)
      - see [Mailing List Recipients](mailing_list_list_recipients.md)
    - Default - Used for Viewing the survey using [Mailing List Answers :: Group By Recipient view](http://local.dv.com/admin/structure/views/view/mailing_list_answers_group_by_recipient)
- Comments
- My Survey Teaser

#### **[Group content plugins](http://local.dv.com/admin/group/types/manage/mailing_list/content)**
- Group node (Content) 
  - Is used to add [content](content.md) to the group.
- Group membership
  - Member roles:
    - [Organisations](organisations.md)
    - Edit Mailing List	- Special role used to allow editing - not sure where it is used right now
    - Owner - Is the owner/creator of the survey	
    - Moderator - Survey moderators
  

### **[Mailing list workflow](http://local.dv.com/admin/config/workflow/workflows/manage/mailing_list_workflow)**

#### States

- Draft [draft](Draft.md)
- Email [email]
- Approved [approved]
- Archived [archived]
- Spam [spam]

#### Transitions

- **Create new Draft** [create_new_draft]
  - from: [draft]
  - to: [draft]
  - Create new Draft transition is never used find a way to remove it if possible

- **Create new Draft**
  - from: Draft
    - x: ttrtt
      p: jkfjkdkfjdik
  - to: Email
  - link: http://www.google.com
  
  
    
- **Send Email** [send_email plugin](../../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/SendEmailTransition.php)
  - from: [draft]
  - to: [email]
  - uc:
    - [x] **own** - triggers this transition
    - [x] **sys** - validates [mailing_list] contains > 0 [node](../entities/node.md) [content] and > 0 recipients
    - [x] **sys** - remove administrator role from the [mailing_list] [group](../entities/group.md)
    - [x] **sys** - send [mailing_list_needs_approval](mailing_list_needs_approval_message.md) message

- **Approve Sending**	[approve plugin](../../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/ApproveSendingTransition.php)
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
      
      
