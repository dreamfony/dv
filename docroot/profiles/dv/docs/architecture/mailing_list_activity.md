# Mailing List Activity

All related code should be placed in [dmt_mailing_list_activity](../../modules/custom/dmt_mailing_list/modules/dmt_mailing_list_activity/dmt_mailing_list_activity.info.yml) module.

## Mailing List Activity is a bundle of [activity](../../modules/custom/activity/activity_creator/src/Entity/Activity.php) entity
 
#### **[Fields](http://local.dv.com/admin/structure/activity_type/mailing_list_activity/edit/fields)**

- Destinations [field_activity_destinations]    
  - type: List (text)
   
- Entity [field_activity_entity]
  - type: Entity reference [content](content.md)

- Hash [field_activity_hash]
  - type: Text (plain)
  - hash is generated with [ActivityLoggerRandom](../../modules/custom/activity/activity_logger/src/ActivityLoggerRandom.php)
  - and the field is populated in [ActivityFactory](../../modules/custom/activity/activity_creator/src/ActivityFactory.php)
    
- Mailing List [field_activity_mailing_list]
  - type: Entity reference [mailing_list](mailing_list.md)
  
- Message [field_activity_message]
  - type: Entity reference [question message](question_message.md)
   
- Output text [field_activity_output_text]
  - type: Text (formatted, long)
  
- Recipient group [field_activity_recipient_group]
  - type: Entity reference
  - not used but we wont remove it since its tied deep in to activity base module
  
- Recipient user [field_activity_recipient_user]
  - type: Entity reference [user](user.md)
   
- Reply [field_activity_reply]
  - type: Entity reference [answer](answer_comment.md) (comment)

#### **[View modes](http://local.dv.com/admin/group/types/manage/mailing_list/display)**
- Comment Activity
  - Moderation Information
  - Reply 
  
### **[Mailing List Activity Workflow](http://local.dv.com/admin/config/workflow/workflows/manage/mailing_list_activity_workflow)**

#### States

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

#### Transitions

**Mark as Delivery error** [delivery_error] (maybe change to sending error, is there a way to check if mail was delivered to the sender?)
  - from: [p_waiting]
  - to: [p_delivery_error]
  - triggers:
    - system - mail service returns delivery error response
  - uc:
    - [ ] **sys** - sends [activity_delivery_error] [message](activity_delivery_error_message.md)
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
       - make a module to handle auto response messages
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
  - uc:
    - [ ] **sys** - check if 

**Cancel** [cancel]
  - from: [p_waiting], [p_delivery_error], [ar_sent], [ar_seen]
  - to: [canceled]
  - uc:
    - [ ] **sys** - unpublishes related [activity_comment]

  
