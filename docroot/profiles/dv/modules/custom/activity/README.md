### How it works

[Create Activity Action](activity_basics/src/Plugin/ActivityAction/CreateActivityAction.php) creates an entry in activity_logger_message queue.

##### Process 1 activity_logger_message.

[Message Queue Creator](activity_logger/src/Plugin/QueueWorker/MessageQueueCreator.php) adds item back to queue if it is not older than 5 seconds or creates a message using [Activity Logger Factory](activity_logger/src/Service/ActivityLoggerFactory.php)::createMessages

for each created message activity_creator_message_insert hook in [Activity Creator Module](activity_creator/activity_creator.module) prepares
variables and adds item to activity_creator_logger queue.

##### Process 2 activity_creator_logger.

[Activity Worker Logger](activity_creator/src/Plugin/QueueWorker/ActivityWorkerLogger.php) worker gets the Recipients from context and adds them to activity_creator_activities queue.

##### Process 3 activity_creator_activities.

[Activity Worker Activities](activity_creator/src/Plugin/QueueWorker/ActivityWorkerActivities.php) worker creates the activities.

**This is where processes stop except if activity needs to be sent by email in that case activity_send_email_worker is used**

##### Process 4 activity_send_email_worker.

[Activity Send Email Worker](activity_send/modules/activity_send_email/src/Plugin/QueueWorker/ActivitySendEmailWorker.php) worker.
