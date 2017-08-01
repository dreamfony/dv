#Mailing List Activity Workflow

**Legend:**
  - TBD - to be decided
  - [] - machine name
  
###Related Entities

**Activity** [activity]
  - Mailing List Activity [mailing_list_activity]

**Node** [node]
 - Content [content]

**Comment** [comment]
 - Activity [activity] 
   - references activity; recipient, has link to log which shows activity status + date
 - Comment [comment]
   - comment on activity or answer
 - Answer [answer] 
   - answer to a question
 - Log [log] 
   - question revision log written in comment
   
**Message**

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

**TODO Begin**
 ACTIVITY_STATUS_REJECTED is undefined
 Maybe rename all const, eg. ACTIVITY_STATUS_PENDING to ACTIVITY_STATUS_EMAIL_PENDING ?
**TODO End**


dmt_mail handles incoming emails and webhook requests from Mailgun.

[Mailgun webhook settings](https://app.mailgun.com/app/webhooks) should be configured to point to
`[domain]/webhook/mailgun`

When a valid webhook event occurs data is placed in 'dv_mailgun_webhook' Queue

Currently webhook data is serialized and saved as revision_log_message in activity.
**TODO** save data as **collect** entity


2. Comment entity of bundle email_reply that is then transformed to either answer_number , answer or answer_list

## Usecase 1
As a Journalist I want to gather data so I can make insights into logic behind smart government decisions.

**Roles**
- S - System
- J - Journalist
- W - Worker (can be program or a human)

**Scenario**
- S: Email is imported
- S: Comment is created and attached to proper Activity
- S: Comment state is set to _Awaiting classification_
- S: Create Notification
- W: Sets State to comment
- W: Translates email text into proper data type field (number / list) in comment

**Email Workflow States**

- Awaiting classification
- Rejected : Possible Action - Needs admin attention
- Needs admin attention = Awaiting classification (Request internal review)
- Partial success = Successful (?)
- Not an FOI request  = Successful
- Not Held = Successful
- Successful

To calculate score we will merge all states into 4 possible
- Awaiting classification / In progress (answer was not yet moderated = ignored)
- Successful (satisfactory answer was given in time)
- Partially Successful (Partially Answered or Answered with Delay)
- Rejected (non-satisfactory answer was given)

**Explanation**
Eg. When institution replies by saying that they do not hold requested information then Worker must either agree with institution and set answer to **Not Held** or Reject Response.

If Response is Delayed (not received by required time), System creates activity_comment and sets it to Rejected state (?)

Maybe we could immediately create empty comment and set it to Awaiting response (?)

### Needs admin attention Process

---

## Question Content Type

Question = Task = Action entity states

Category: Eg - Suggestion, Compliment, Bug, Question, Task

States:

- New / Pending_approval - Ako je source FB ili twitter onda prvo ide u taj state
- Draft
- Open
- Active
- Closed

https://www.screencast.com/t/BMeKXHSaD



There are comments which are general messages and activities as tasks.


Comment state - "More info" po tome se automatski kreira komentar koji se šalje instituciji
"Praise" šalju instituciji komentar


## Flow

Title: Mailing List: U = User / M = Moderator
Draft->Pending_approval: Moderate (U)
Note left of Pending_approval: S: Validation
Note left of Pending_approval: S: Notify \n Moderators
Pending_approval->Pending_approval: M: Finds problems and \n informs user about \n them via comment
Pending_approval->Draft: Back to Draft (M)
Note right of Draft: S: Notify User
Draft->Pending_approval: Moderate (U)
Pending_approval->Pending_approval: M: Approves sending \n of Mailing List
Pending_approval->Approved: Approve (M)
Note left of Approved: S: Notify User
Note left of Approved: S: Sends Emails
Pending_approval->Rejected: Reject = Delete (M)
Draft->Rejected: Reject = Delete (U)
Approved->Archived: Archive (U)
Draft->Spam: Spam (M)
Pending_approval->Spam: Spam (M)
Note right of Spam: Unpublished
