# Organisations

## Pages
- View Organisation
  - Block Organisation details form Profile
    - Name
    - Phone
    - Email

## Group bundle "organisation"

#### **[Fields](http://local.dv.com/admin/group/types/manage/organisation/fields)**
- Currently organisation group has no fields

#### **[View modes](http://local.dv.com/admin/group/types/manage/organisation/display)**
- Default
  - Showing different types of organisations could be solved with panelizer like in [mailing_list](mailing_list.md) group

#### **[Group content plugins](http://local.dv.com/admin/group/types/manage/mailing_list/content)**
- Group node (Content) 
  - Is used to add [content](content.md) to the group.
- Group membership
  - Member roles:
    - Organisation
    - Moderator
- Membership history - currently does not exist
  - The idea behind this plugin is to better solve what [positions](positions.md) entity is doing currently
  - Get some ideas form: https://www.drupal.org/node/2801603#comment-12031080

## User

Every organisation has its own user entry.

## Profile bundle "organisation_profile"

Every organisation has a organisation_profile. Using [Profile](https://www.drupal.org/project/profile) contrib. module

#### Import

Organisations are imported from xml file using [Hr Organisations Module](../../../../modules/migrations/hr_organisation_migrate/hr_organisation_migrate.info.yml)

