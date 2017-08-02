##Mailing List Activity Workflow [mailing_list_activity_workflow]

**Legend:**
  - TBD - to be decided
  - [] - machine name
  
###Related Entities

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

###Workflow

Workflow [mailing_list_activity_workflow] states on Mailing List Activity should only be changed by system (in code).
States that describe quality of response (eg. Not Held / Rejected) are attached to Comment which represents response.
FOI law has SLA (Service Level Agreement) of 20 days and our states have to be aware of that. Time directly influences state.

###Mailing List Activity States

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

###Mailing List Activity Transitions

**Mark as Delivery error** [erred] 
  - from: [p_waiting]
  - to: [p_delivery_error]
  - triggers: 
    - system - mail service returns delivery error response
  - uc:
    - **sys** - sends a message to moderator with link to activity view
    - **mod** - checks the validity of email address edits if necessary
    - **mod** - clicks Mark as Pending (Waiting to be sent)
    - **sys** - puts message in queue again
    
**Mark as Pending (Waiting to be sent)** [p_waiting]
  - form: [p_delivery_error]
  - to: [p_waiting]
    
**Mark as Sent** [sent]
  - from: [p_waiting]
  - to: [ar_sent]
  - triggers:
    - **sys** - mail service returns sent response; if not possible mail sent to mailing service
 
**Mark as Seen** [seen]
	- from: [ar_sent]
	- to: [ar_seen]
  - triggers:
    - **sys** - mail service returns seen response 

**Mark as Answered**	[answer]
  - from: [p_waiting], [p_delivery_error], [ar_sent], [ar_seen]
  - to: [f_answered]

**Cancel** [cancel]
  - from: [p_waiting], [p_delivery_error], [ar_sent], [ar_seen]
  - to: [canceled]


___
