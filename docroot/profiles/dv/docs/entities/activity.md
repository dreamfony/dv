## Activity [activity]

Entity: [Activity.php](../../modules/custom/activity/activity_creator/src/Entity/Activity.php)

### Activity Bundles

**Mailing List Activity** [mailing_list_activity]

- Destinations [field_activity_destinations]    
  - type: List (text)
   
- Entity [field_activity_entity]
  - type: Entity reference [node::question](../entities/node.md)

- Hash [field_activity_hash]
  - type: Text (plain)
  
- Mailing List [field_activity_mailing_list]
  - type: Entity reference [group::mailing_list](../entities/group.md)
  
- Message [field_activity_message]
  - type: Entity reference [message::question](../entities/message.md)
   
- Output text [field_activity_output_text]
  - type: Text (formatted, long)
  
- Recipient group [field_activity_recipient_group]
  - type: Entity reference
  - not used but we wont remove it since its tied deep in to activity base module
  
- Recipient user [field_activity_recipient_user]
  - type: Entity reference [user](../entities/user.md)
   
- Reply [field_activity_reply]
  - type: Entity reference [comment::answer](../entities/comment.md)

**Moderation Activity**
