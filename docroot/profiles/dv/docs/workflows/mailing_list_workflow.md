##Mailing list Workflow [mailing_list_workflow]

[Mailing list Workflow MSM](../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/MailingListStateMachine.php)

###Related entities

- **Group** [group](../entities/group.md)
  - mailing_list

###Workflow

####Mailing list Workflow States

- Draft [draft]
- Email [email]
- Approved [approved]
- Archived [archived]
- Spam [spam]

####Mailing List Activity Transitions

- **Create new Draft** [crate_new_draft]
  - from: [draft]
  - to: [draft]
  - this transition is never used find a way to remove it
 
- **Send Email** [send_email]
  - from: [draft]
  - to: [email]    
  - uc:
    - **own** - triggers this transition
    - **sys** - validates [mailing_list] contains > 0 [node](../entities/node.md) [content] and > 0 recipients 
    - **sys** - remove administrator role from the [mailing_list] [group](../entities/group.md)
    - **sys** - send [mailing_list_needs_approval] [message](../entities/message.md)
    
- **Approve Sending**	[approve]
  - from: [email]
  - to: [approved]
  - uc:
    - **mod** - triggers this transition
    - **sys** - validates [mailing_list] contains > 0 [node](../entities/node.md) [content] and > 0 recipients 
    - **sys** - gets all the content form group of type [group_node:content] 
    - **sys** - foreach group content gets referenced entity [node] of type [content] and triggers [create_activity_action]
    - **sys** - triggers [close_mailing_list_ticket] for the [group](../entities/group.md) [mailing_list]
    
- **Archive	Email** [archive]
  - form: [draft], [email], [approved]
  - to: [archived]
  
- **Restore to Draft**	[restore_to_draft]
  - from: [send_email]
  - to: [draft]
  
- **Spam** [spam]
  - from: [draft]
  - to: [spam]