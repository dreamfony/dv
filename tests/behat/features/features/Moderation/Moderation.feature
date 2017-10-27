@api
Feature: Moderation
  Benefit: Clean site content
  Persona: Moderator
  Goal/Desire: Clean site content

  @javascript
  Scenario: Visit Moderation Dashboard
    Given I am logged in as a user with the "moderator" persona
    # Moderation Dashboard page exists on admin/dashboard but it should be moved to moderation
    When I click "Moderation Dashboard" in the "Secondary Navigation" region
    # view: http://local.dv.com/admin/structure/views/view/manage_org_view_org_without_email
    Then I should see the text "Organisations without email"
    # view: http://local.dv.com/admin/structure/views/view/survey_moderation
    And I should see the text "Survey Moderation"
