## Mailing List Activity Workflow [mailing_list_activity_workflow]

**Legend:**
  - TBD - to be decided
  - [] - machine name
  
### Related Entities

**Activity** [activity](../entities/activity.md)
  - Mailing List Activity [mailing_list_activity]

**Node** [node](../entities/node.md)
 - Content [content]

**Comment** [comment](../entities/comment.md)
 - Activity [activity]
   - references activity; recipient, has link to log which shows activity status + date
 - Comment [comment]
   - comment on activity or answer
 - Answer [answer] 
   - answer to a question
 - Log [log] 
   - question revision log written in comment
   
**Message** [message](../entities/message.md)

1. Email that is sent is constructed from Message entity type Question [question] and it creates Activity entity of type Mailing List Activity [mailing_list_activity]

### Workflow

Workflow [mailing_list_activity_workflow] states on Mailing List Activity should only be changed by system (in code).
States that describe quality of response (eg. Not Held / Rejected) are attached to Comment which represents response.
FOI law has SLA (Service Level Agreement) of 20 days and our states have to be aware of that. Time directly influences state.

### Mailing List Activity States

**Pending States**
- Pending (Waiting to be sent) [p_waiting]
- Pending (Delivery Error) [p_delivery_error]
- Pending (Rejected) [p_rejected]
- Pending (Auto response) [p_auto_response]

**Awaiting Response States**
- Awaiting Response (Sent) [ar_sent]
- Awaiting Response (Seen) [ar_seen]
- Awaiting Response (Delayed) [ar_delayed]

**Awaiting Classification States**
- Awaiting Classification [unclassified]

**Finished States**
- Finished (Successfully) [f_answered]
- Finished (Successfully with delay) [f_delayed]
- Finished (Unsuccessfully) [f_unsuccessful]
  - unsatisfactory answer and timed out
- Finished (Expired) [f_expired] 
  - Timed out
- Finished (Need more info) [f_more_info]

**Canceled States**
- Canceled [canceled]

### Mailing List Activity Transitions

**Mark as Delivery error** [delivery_error] 
  - from: [p_waiting]
  - to: [p_delivery_error]
  - triggers: 
    - system - mail service returns delivery error response
  - uc:
    - [ ] **sys** - sends [activity_delivery_error] [message](../entities/message.md)
    - [ ] **mod** - checks the validity of email address edits if necessary
    - [ ] **mod** - clicks Mark as Pending (Waiting to be sent)
    
**Mark as Pending (Waiting to be sent)** [waiting]
  - form: [p_delivery_error]
  - to: [p_waiting]
  - uc:
    - [ ] **mod, (own?)** - triggers [waiting] transition 
    - [ ] **sys** - triggers [email_activity_send] action
      - @see [activity_send_email_activity_insert](../../modules/custom/activity/activity_send/modules/activity_send_email/activity_send_email.module)
      
**Pending (Auto response)** [auto_response]
   - form: [unclassified]
   - to: [p_auto_response]
   - uc: 
     - [ ] **mod, own** - triggers [auto_response] transition
     - [ ] **sys** - adds flag on organisation that it sends auto response messages
       - will be used as one of the criteria to automatically set messages as auto_response
       - other criteria may include response time, message subject
       - [Detecting autoresponders](https://github.com/jpmckinney/multi_mail/wiki/Detecting-autoresponders)
    
**Mark as Sent** [sent]
  - from: [p_waiting]
  - to: [ar_sent]
  - uc:
    - [ ] **sys** - mail service returns sent response; if not possible mail sent to mailing service
 
**Mark as Seen** [seen]
	- from: [ar_sent]
	- to: [ar_seen]
  - uc:
    - [ ] **sys** - when mail service returns seen response, trigger this transaction 
 
**Mark as Answered**	[answer]
  - from: [p_waiting], [ar_sent], [ar_seen]
  - to: [f_answered]

**Cancel** [cancel]
  - from: [p_waiting], [p_delivery_error], [ar_sent], [ar_seen]
  - to: [canceled]
  - uc:
    - [ ] **sys** - unpublishes related [activity_comment]


___
