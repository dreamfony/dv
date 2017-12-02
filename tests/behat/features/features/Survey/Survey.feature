@api
Feature: Create Survey
  Benefit: So I can discover new facts about my country :)
  Persona: Journalist
  Goal/desire: I want to get answers to my questions

  @javascript
  Scenario: Try to send Survey before filling in Content and Recipients
    Given I am logged in as a user with the "journalist" persona
    Then I should see the link "Create Survey"
    When I click "Create Survey"
    Then I should see the text "Empty." in the "Survey content list" region
    And I should see the text "No Recipients selected yet." in the "Survey recipients list" region
    When I click "Send Email"
    Then I should see the error message "Please add content and recipients before sending for approval."
    When I close the error message
    Then I should not see the error message "Please add content and recipients before sending for approval."

  @javascript
  Scenario: I click on Create Survey previously changing only the title
    Given I am logged in as a user with the "journalist" persona
    Then I should see the link "Create Survey"
    When I click "Create Survey"
    And I should see the text "New Survey"
    When I click "Edit Title" in the "Page title" region
    And I wait for AJAX to finish
    When I fill in "Title" with "New survey title"
    And I press the "Save" button
    And I wait for AJAX to finish
    Then I should see "New survey title" in the "Page title" region
    When I click "Create Survey"
    # We expect the "New Survey to be created"
    And I should see the text "New Survey" in the "Page title" region

  @javascript
  Scenario: I click on Create Survey more than once without any changes
    Given I am logged in as a user with the "journalist" persona
    Then I should see the link "Create Survey"
    When I click "Create Survey"
    And I should see the text "New Survey" in the "Page title" region
    And I remember the current url
    When I click "Create Survey"
    And I should see the text "New Survey" in the "Page title" region
    Then I check that the url is the same as I remembered it

  @javascript @groups @user
  Scenario: Successfully Create Survey
    Given organisations:
      | name       | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 | org3@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 4 | org4@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
    And I am logged in as a user with the "journalist" persona
    Then I should see the link "Create Survey"
    When I click "Create Survey"
    Then I should see the link "Edit Title"
    And I should see the text "New Survey"
    And I should see the text "Empty." in the "Survey content list" region
    And I should see the text "No Recipients selected yet." in the "Survey recipients list" region

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

    # Delete content
    When I click "Delete" in the "Survey content list" region
    And I wait for AJAX to finish
    Then I should not see the text "Test Test" in the "Survey content list"
    ### TODO Then I should see the text "Empty." in the "Survey content list" region

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

    # Add one recipient
    When I select the first autocomplete option for "Test Org 1" on the "Recipients" field
    ### TODO Then the "Recipients" field should contain "Test Org 1" // make a method for partial string matching
    And I press "Add Recipients"
    And I wait for AJAX to finish
    Then I should see the link "Test Org 1"

    # Remove recipient
    When I click "Remove" in the "Survey recipients list" region
    And I wait for AJAX to finish
    Then I should not see the link "Test Org 1"
    And I should see the text "No Recipients selected yet." in the "Survey recipients list" region

    # Add Multiple recipients
    When I select the first autocomplete option for "Test Act" on the "Recipients" field
    ### TODO Then the "Recipients" field should contain "Test Activity Group (54)" // make a method for partial string matching
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
    ### TODO And I should see the message ...
    And I logout
