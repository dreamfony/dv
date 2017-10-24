@api
Feature: Survey Moderation
  Benefit: So Organisations are getting clean and clear surveys to answer
  Persona: Moderator
  Goal/desire: I want to edit the survey so it is appropriate to send

  @javascript @groups
  Scenario: Successfully Moderate the Survey
    # TODO all this before moderator logs in should be in one Given step
    # Given surveys:
    #  | title       |
    #  | Test Survey |
    Given organisations:
      | name       | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 | org3@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 4 | org4@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
    And I am logged in as a user with the "journalist" persona
    When I click "Create Survey"

    # Edit title
    When I click "Edit Title" in the "Page title" region
    And I wait for AJAX to finish
    When I fill in "Title" with "New survey title"
    And I press the "Save" button
    And I wait for AJAX to finish
    Then I should see "New survey title" in the "Page title" region

    # Add more than one content
    When I fill in "Content" with "Test content 1" in the "Survey content add" region
    And I press the "Add Content" button
    And I wait for AJAX to finish
    Then the "Content" field should contain ""
    And I should see the text "Test content 1"
    When I fill in "Content" with "Test content 2" in the "Survey content add" region
    And I press the "Add Content" button
    And I wait for AJAX to finish
    Then the "Content" field should contain ""
    And I should see the text "Test content 2"

    # Add Multiple recipients
    When I select the first autocomplete option for "Test Act" on the "Recipients" field
    And I press "Add Recipients"
    And I wait for AJAX to finish
    Then I should see the link "Test Org 1" in the "Survey recipients list" region
    And I should see the link "Test Org 2" in the "Survey recipients list" region

    # Send email
    When I click "Send Email"
    Then I should not see the link "Edit Title"
    And I should see the text "Test Org 1" in the "Survey recipients list" region
    And I should see the text "Test Org 2" in the "Survey recipients list" region
    And I should see the text "Test content 1"
    And I should see the text "Test content 2"
    ### if you get error on this step go to http://local.dv.com/admin/config/system/queue-ui and clear the queue manually
    ### TODO find a way to remove items in queue that lost reference automatically
    And I wait for the queue to be empty

    # Logout
    And I logout

    # Moderation
    And I am logged in as a user with the "moderator" persona
    And I click "Notification Centre"
    # Then I should see ....
    And I wait for 30 seconds






