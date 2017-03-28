Queue order:

[Create Activity Action](src/Plugin/ActivityAction/CreateActivityAction.php)

accepts the $data['action'] param
this needs to be changed to something else since we are trying to figure out context from this

[Message Queue Creator](../activity_logger/src/Plugin/QueueWorker/MessageQueueCreator.php) queue

Adds Item to MessageQueueCreator

[skipping few unimportant steps]

[Activity Logger Factory](../activity_logger/src/Service/ActivityLoggerFactory.php)::createMessages 

for each created message activity_creator_message_insert in [Activity Creator Module](../activity_creator/activity_creator.module) which prepares
variables and adds item to 

[ActivityWorkerLogger](../activity_creator/src/Plugin/QueueWorker/ActivityWorkerLogger.php) queue

- Gets the Recipients from context

