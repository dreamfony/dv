@api
Feature: Organisation Moderation
  Benefit: So we can manage the content on the site better
  Persona: Moderator

  @javascript @group @user
  Scenario: Moderate Organisation Emails
    Given organisations:
      | name       | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 |             | 10000 Zagreb, Trg sv. Marka 6 |
    And I am logged in as a user with the "moderator" persona
    When I click "Moderation Dashboard" in the "Secondary Navigation" region
    Then I should see the text "Organisations without email"
    And I should see the link "Test Org 3"
    And I should not see the link "Test Org 2"
    And I should not see the link "Test Org 3"
    And I should see the link "Edit"
    When I click "Edit"
    #Then I should see ...
