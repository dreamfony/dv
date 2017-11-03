## Sending Emails - Mail OUT
   - [message](../../../../modules/contrib/message/message.info.yml) contrib. module

   - Activity Module Suite
     - how it works:
       - [Create Activity Action](activity_basics/src/Plugin/ActivityAction/CreateActivityAction.php) creates an entry in activity_logger_message queue.
         
       - Process 1 activity_logger_message         
         - [Message Queue Creator](activity_logger/src/Plugin/QueueWorker/MessageQueueCreator.php) adds item back to queue if it is not older than 5 seconds or creates a message using [Activity Logger Factory](activity_logger/src/Service/ActivityLoggerFactory.php)::createMessages
         - for each created message activity_creator_message_insert hook in [Activity Creator Module](activity_creator/activity_creator.module) prepares
         variables and adds item to activity_creator_logger queue.
         
       - Process 2 activity_creator_logger         
         - [Activity Worker Logger](activity_creator/src/Plugin/QueueWorker/ActivityWorkerLogger.php) worker gets the Recipients from context and adds them to activity_creator_activities queue.
         
       - Process 3 activity_creator_activities         
         - [Activity Worker Activities](activity_creator/src/Plugin/QueueWorker/ActivityWorkerActivities.php) worker creates the activities.

       - Process 4 activity_send_email_worker
         - process is used if activity needs to be sent by email     
         - [Activity Send Email Worker](activity_send/modules/activity_send_email/src/Plugin/QueueWorker/ActivitySendEmailWorker.php) worker.

   - Mailgun service using contrib module [mailgun](../../../modules/contrib/mailgun/mailgun.info.yml)

   - [dmt_mail](../../modules/custom/dmt_mail/dmt_mail.info.yml) module:
     - Webhook is configured [here](http://local.dv.com/admin/config/services/webhook/mailgun/edit).
     - [Event Subscriber](../../modules/custom/dmt_mail/src/EventSubscriber/MailgunWebhookEvent.php) creates items for [Queue Worker](../../modules/custom/dmt_mail/src/Plugin/QueueWorker/MailgunWorker.php)
