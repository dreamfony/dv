@api
Feature: Survey Moderation
  Benefit: So Organisations are getting clean and clear surveys to answer
  Persona: Moderator
  Goal/desire: I want to edit the survey so it is appropriate to send

  @javascript @group @user
  Scenario: Successfully Approve Survey
    Given organisations:
      | name       | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 | org3@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
    And user "Test User" has survey "Test Survey"
    And "Test Survey" survey has content:
      | body           | answer_format |
      | Test Content 1 | text          |
      | Test Content 2 | text          |
    And "Test Survey" survey has recipients:
      | name       |
      | Test Org 1 |
      | Test Org 2 |

    # Moderation
    And I am logged in as a user with the "moderator" persona
    And I click "Notification Centre"
    Then I should see the link "Test Survey needs approval"
    And I click "Test Survey needs approval"
    Then I should see the text "Test Survey" in the "Page Title" region
    And I should see the link "Approve"

    #And I wait for 30 seconds






