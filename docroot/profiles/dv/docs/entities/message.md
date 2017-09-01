## Message [message]

Entity: [Message.php](../../../../modules/contrib/message/src/Entity/Message.php)

### Bundles

**Question**
  - subject: 
  - body: 
  - recipients:
    - question recipient

**Mailing List Needs Approval** [mailing_list_needs_approval]
  - subject: Mailing list needs approval.
  - body:
    - Mailing list [link_to_mailing_list] needs approval.
  - recipients:
    - moderator
   
**Activity Delivery Error** [activity_delivery_error]
  - subject: Delivery error when sending Email message occurred
  - body:
    - Delivery error occurred when sending email to [organisation_link]. Please check the organisation email address and try to resend.
      [activity_comment_link].
  - recipients:
    - moderator
