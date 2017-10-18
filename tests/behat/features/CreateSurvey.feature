@api
Feature: Create Survey
  Benefit: So I can discover new facts about the country
  Role: As a LU
  Goal/desire: I want see and get notified when content is created

  @javascript
  Scenario: Successfully Create Survey
    Given I am logged in as a user with the "journalist" persona
    Then I should see the link "Edit"
    Then I should see the link "Create Survey"
    When I click "Create Survey"
    Then I should see "Edit Title"
    And I click "Edit Title"
    And I wait for AJAX to finish
    When I fill in "Title" with "Test Survey"
    And I press the "Save" button
    And I wait for AJAX to finish
    Then I should see "Test Survey"
