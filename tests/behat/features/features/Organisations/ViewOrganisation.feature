@api
Feature: View Organisation
  Benefit: So I can find more detail about the organisation
  Persona: All
  Goal/desire: So I can find more detail about the organisation

  @javascript @groups
  Scenario: View Organisation
    Given organisations:
      | name       | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 | org3@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 4 | org4@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
    And I click "Organisations"
    Then I should not see the text "Organisations" in the "Title Region" region
    Then I should see the link "Test Org 1"
    And I should see the link "Test Org 2"
    # TODO And I should see


    #Given I am logged in as a user with the "journalist" persona
    # When I click "Profile of TEST_journalist" ## we need a method here to get current logged in profile link
    # ...
