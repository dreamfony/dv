Email related processes are handled by two entities (activity and comment)

1. Email that is sent is derived from message (question) and constructed as Activity entity (mailing_list_activity)

**Workflow**

Workflow (mailing_list_activity_workflow) states on Mailing List Activity should only be changed by system (in code).
And so transitions are unnecessary in this workflow.
States that describe quality of response (eg. Not Held / Rejected) are attached to Comment which represents response.

**States that are set by transactionl email service via webhook**
Canceled
Pending
Delivery Error
Rejected (TODO this state)
Sent = Awaiting response
Seen = Awaiting response
Delayed = Awaiting response

**Any matching response will set activity to state**
Response

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



2. Comment entity of bundle email_reply that is then transformed to either answer_number , answer_text or answer_list

Usecase

As a Journalist I want to gather data so I can make insights into logic behind smart government decisions.

S - System
J - Journalist
W - Worker (can be program or a human)

S: Email is imported
S: Comment is created and attached to proper Activity
S: Comment state is set to _Awaiting classification_
S: Create Notification
W: Sets State to comment
W: Translates email text into proper data type field (number / list) in comment

**Workflow**

Awaiting classification
Rejected : Possible Action - Needs admin attention
Needs admin attention = Awaiting classification (Request internal review)
Partial success = Successful (?)
Not an FOI request  = Successful
Not Held = Successful
Successful

To calculate score we need to merge all states into 3 possible
- Awaiting classification (answer was not yet moderated = ignored)
- Successful (satisfactory answer was given)
- Rejected (non-satisfactory answer was given)

**Explanation**
Eg. When institution replies by saying that they do not hold requested information then Worker must either agree with institution and set answer to **Not Held** or Reject Response.

If Response is Delayed (not received by required time), System created comment and sets it to Rejected state (?)

Maybe we could immediately create empty comment and set it to Awaiting response (?)

**Needs admin attention Process**
