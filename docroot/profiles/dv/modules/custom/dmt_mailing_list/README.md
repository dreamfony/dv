Mailing List States:

- Draft
- Pending_approval
- Approved
- Rejected
- Archived
- Spam






Use Case 1: Create Mailig List / Survey

- U = user
- M = moderator user
- S = system

- ML = Mailing List

- U: visits s/create
  - S: find if there is existing group that is empty and in draft (for reuse)
  - S: load either existing group or create new
  - S: redirect to group/[id] (TODO configure pathauto survey/[id])
- U: enters organisation group name in the autocomplete field and clicks "Add Recipient" (repeatable action)
  - S: adds all Organisation members of the selected group to current ML Group
- U: enters question and Answer type, clicks save (repeatable action)
  - S: create Content node (content nodes will be reusable later)
- U: clicks Send Email
  - S: validates that there is at least one content and one recipient in ML Group
  - S: changes ML state from Draft to Moderate (see STATE CHANGE DIAGRAM)
- M: Picks up notification about new moderation activity (see Use Case 2: Moderation Stream)
- M: SCENARIO a. Click Approve Sending ML
  - S: TODO Open confirm dialog
  - M: Clicks Yes
  - S: changes ML state from Moderate to Published (see STATE CHANGE DIAGRAM)
  - S: Create Queue items that create activities and send emails (see [Activity README](../activity/activity_basics/README.md))
  - S: When last activity is created switch ML group view mode that uses View dependent on activities (mailing_list_answers_group_by_recipient)
  - S: TODO notify User that ML is approved
     SCENARIO b. Send Mailing List back to User with comment
  - M: clicks Back to Draft
  - S: TODO Opens confirm dialog with Form where M enters reason for not approving.
    - Reason is saved as comment on ML
  - S: changes ML state from Moderate to Draft (see STATE CHANGE DIAGRAM)


Permission

- U: can only edit group while in "draft" state
- M: can only edit group while in "moderate" state

Requirement

- System logs all state changes as comment (project/nodechanges)


[State Change Diagram] (https://bramp.github.io/js-sequence-diagrams/)


Title: Mailing List: U = User / M = Moderator
Draft->Pending_approval: Moderate (U)
Note left of Pending_approval: S: Validation
Note left of Pending_approval: S: Notify \n Moderators
Pending_approval->Pending_approval: M: Finds problems and \n informs user about \n them via comment
Pending_approval->Draft: Back to Draft (M)
Note right of Draft: S: Notify User
Draft->Pending_approval: Moderate (U)
Pending_approval->Pending_approval: M: Approves sending \n of Mailing List
Pending_approval->Approved: Approve (M)
Note left of Approved: S: Notify User
Note left of Approved: S: Sends Emails
Pending_approval->Rejected: Reject = Delete (M)
Draft->Rejected: Reject = Delete (U)
Approved->Archived: Archive (U)
Draft->Spam: Spam (M)
Pending_approval->Spam: Spam (M)
Note right of Spam: Unpublished

- 


TODO
Use Case 2: Moderation Stream