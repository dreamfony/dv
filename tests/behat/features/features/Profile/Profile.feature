@api
Feature: Profile
  Benefit: So users can present them self better
  Persona: Journalist, Moderator
  Goal/desire: I want to edit my profile

  Check: https://github.com/goalgorilla/open_social/tree/8.x-1.x/tests/behat/features/capabilities/profile for some ideas

  @javascript @group @user @skip
  Scenario: Delete orphaned profiles
    Given organisations:
      | name       | mail        | address                       |
      | Test Org 1 | org1@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 2 | org2@dv.com | 10000 Zagreb, Trg sv. Marka 6 |
      | Test Org 3 | org3@dv.com | 10000 Zagreb, Trg sv. Marka 6 |

