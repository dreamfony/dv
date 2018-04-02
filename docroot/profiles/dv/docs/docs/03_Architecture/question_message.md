## Question Message

// document Question mail template
// current template is called Content is hardcoded to Croatian language
// and does not have recipient-data and sender-data tokens

[Admin link](http://local.dv.com/admin/structure/message/manage/content)

**Settings**:
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
