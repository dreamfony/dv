@api
Feature: Create Survey
  Benefit: So I can discover new facts about the country
  Role: As a LU
  Goal/desire: I want see and get notified when content is created

  @javascript @groups
  Scenario: Successfully Create Survey
    Given organisations:
      | name  | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 | org3@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 4 | org4@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
    And I am logged in as a user with the "journalist" persona
    Then I should see the link "Create Survey"
    When I click "Create Survey"
    Then I should see the link "Edit Title"
    And I should see the text "New Survey"

    # Edit title
    When I click "Edit Title" in the "Page title" region
    And I wait for AJAX to finish
    When I fill in "Title" with "New survey title"
    And I press the "Save" button
    And I wait for AJAX to finish
    Then I should see "New survey title" in the "Page title" region

    # Add content
    When I fill in "Content" with "Test content 1" in the "Survey content add" region
    And I press the "Add Content" button
    And I wait for AJAX to finish
    Then the "Content" field should contain ""
    And I should see the text "Test content 1"

    # Edit content
    When I click "Edit" in the "Survey content list" region
    And I wait for AJAX to finish
    Then I should see the link "Cancel" in the "Survey content list" region
    And I should see the button "Save"
    And I should see the text "Content" in the "Survey content list" region
    And I should see the text "Answer format" in the "Survey content list" region
    And I should see the text "Attachments" in the "Survey content list" region
    And the "Content" field should contain "Test content 1" in the "Survey content list" region
    When I fill in "Test Test" for "Content" in the "Survey content list"
    When I press "Save" in the "Survey content list"
    And I wait for AJAX to finish
    Then I should see the text "Test Test" in the "Survey content list"

    # Add one recipient
    When I select the first autocomplete option for "Test Org" on the "Recipients" field
    # Then the "Recipients" field should contain "HRVATSKI SABOR (55)"
    And I press "Add Recipients"
    And I wait for AJAX to finish
    Then I should see the link "Test Org 1"

    # Remove recipient
    When I click "Remove" in the "Survey recipients list" region
    And I wait for AJAX to finish
    Then I should not see the link "Org 1"
    And I should see the text "No Recipients selected yet." in the "Survey recipients list" region

    # Add Multiple recipients
    When I select the first autocomplete option for "Test Activity Group" on the "Recipients" field
    # Then the "Recipients" field should contain "Politika i javna vlast (54)"
    And I press "Add Recipients"
    And I wait for AJAX to finish
    Then I should see the link "Test Org 1" in the "Survey recipients list" region
    And I should see the link "Test Org 2" in the "Survey recipients list" region

    And I wait for 5 seconds
