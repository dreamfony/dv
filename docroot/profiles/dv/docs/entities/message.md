## Message [message]

Entity: [Message.php](../../../../modules/contrib/message/src/Entity/Message.php)

### Bundles

// TODO document Question mail template correctly
// current template is called Content is hardcoded to Croatian language
// and does not have recipient-data and sender-data tokens
**Question**
  - subject: ?
  - body:
    - [activity:recipient-data]

      [activity:sender-data]

      Molimo odgovoriti na sljedeće pitanje

      [message:node-body]

      Molimo da kada odgovarate na pitanje koristite sljedeći email:

      [activity:replyto-email]
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
