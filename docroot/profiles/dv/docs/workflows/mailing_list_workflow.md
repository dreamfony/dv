## Mailing list Workflow [mailing_list_workflow]

[Mailing list Workflow MSM](../../modules/custom/dmt_mailing_list/src/Plugin/ModerationStateMachine/MailingListStateMachine.php)

### Related entities

- **Group** [group](../entities/group.md)
  - mailing_list

### Usecases


### Workflow

#### Mailing list Workflow States

- Draft [draft]
- Email [email]
- Approved [approved]
- Archived [archived]
- Spam [spam]

#### Mailing List Activity Transitions

- **Create new Draft** [create_new_draft]
  - from: [draft]
  - to: [draft]
  - todo Create new Draft transition is never used find a way to remove it if possible

- **Send Email** [send_email]
  - from: [draft]
  - to: [email]
  - uc:
    - [x] **own** - triggers this transition
    - [x] **sys** - validates [mailing_list] contains > 0 [node](../entities/node.md) [content] and > 0 recipients
    - [x] **sys** - remove administrator role from the [mailing_list] [group](../entities/group.md)
    - [x] **sys** - send [mailing_list_needs_approval] [message](../entities/message.md)

- **Approve Sending**	[approve]
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


todo #28 implement Restore to Draft transition
- **Restore to Draft**	[restore_to_draft]
  - from: [send_email]
  - to: [draft]
  - uc:
    - [ ] **sys** - adds administrator role to user that created [mailing_list]

todo implement spam transition
- **Spam** [spam]
  - from: [draft]
  - to: [spam]
  - uc:
    - [ ] **sys** - adds mailing list to queue to be deleted
