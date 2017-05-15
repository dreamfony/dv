Use Case for Mailig List / Survey

U = user
M = moderator user
S = system

ML = Mailing List

- U: visits s/create
  - S: find if there is existing group that is empty (for reuse)
  - S: load either existing group or create new
  - S: redirect to group/[id] (TODO configure pathauto survey/)
- U: enters organisation group name in the autocomplete field and clicks "Add Recipient" (repeatable action)
  - S: adds all Organisation members of the selected group to current ML Group
- U: enters question and Answer type, clicks save (repeatable action)
  - S: create Question node (question nodes will be reusable later)
- U: clicks Send Email
  - S: validates that there is at least one question and one recipient in ML Group
  - S: changes ML state from draft to email
  - S: Adds Moderation Message
  - S: TODO system logs state change as comment (project/nodechanges)
- U: can not edit group while in "email" state
- M: Picks up notification about new moderation activity (TODO describe Moderation Stream)
- M: SCENARIO a. Approve Sending
  - S: TODO Open confirm dialog
  - S: changes ML state from email to published
  - S: Create Queue items that create activities and send emails
  - S: When last activity is created switch ML group view mode that uses View dependent on activities (mailing_list_answers_group_by_recipient)
  - S: TODO notify User that ML is approved
     SCENARIO b. Send Mailing List back to User with comment
  - M: clicks Draft
  - S: TODO Opens confirm dialog with Form where M enters reason for not approving.
    - Reason is saved as comment on ML
  - S: changes ML state from email to draft
  - S: TODO notify User that ML is back in Draft