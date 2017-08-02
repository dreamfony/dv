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
  - triggers:
    - own - mail service returns delivery error response
  - uc:
    - **sys** - remove administrator role from the mailing_list group
    - **sys** - send message to moderator
    - **mod** - clicks Mark as Pending (Waiting to be sent)
    - **sys** - puts message in queue again
- Approve Sending	EmailApproved	Approved	
- Archive	EmailApproved	Archived	
- Restore to Draft	Archived	Draft	
- Spam
