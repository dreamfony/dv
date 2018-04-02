# Features

Behat tests tips:
- blt tests:behat:definitions
  - shows all defined assertions
- blt tests:behat 
  - runs all behat tests without @skip tag
- blt tests:behat -D behat.tags=@f1s1 
  - runs only a test with @f1s1 this does not ignore @skip tag

## Survey

**Goal:** Fight the corruption and crime like Batman.

**Scenarios:**
- [Try to send Survey before filling in Content and Recipients](../../../../tests/behat/features/features/Survey/Survey.feature)
- [I click on Create Survey previously changing only the title](../../../../tests/behat/features/features/Survey/Survey.feature)
- [I click on Create Survey more than once without any changes](../../../../tests/behat/features/features/Survey/Survey.feature)
- [Successfully Create Survey](../../../../tests/behat/features/features/Survey/Survey.feature)

## Organisations

**Scenarios:**
- Add new Organisation
- Edit existing Organisation
- [View existing Organisation](../../../../tests/behat/features/features/Organisations/ViewOrganisation.feature)
- Find existing Organisation

## Positions
- This needs to be refactored to use group membership in organisations

## Moderation

**Scenarios:**
- moderation.feature:
  - [Visit Moderation Dashboard](../../../../tests/behat/features/features/Moderation/Moderation.feature)
- OrganisationModeation.feature
  - [Moderate Organisation Emails](../../../../tests/behat/features/features/Moderation/OrganisationModeration.feature)
- tttretrt
  - [Successfully Approve Survey](../../../../tests/behat/features/features/Moderation/SurveyModeration.feature)

## User Profile


## User Account
- Login
- Register
- Change account details
