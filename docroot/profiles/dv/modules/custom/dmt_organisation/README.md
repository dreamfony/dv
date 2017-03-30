##Create Organisation

/o/create

- Create User
  - randomly generate email consisting of numbers
  - set user persona to "Organisation"
- Redirect to users organisation_profile edit page
- Editor fills Organisation Profile data
- profile_presave (dmt_organisation)
  - extract and set Organisation ID from email
- profile_insert (dmt_organisation_group)
  - create Organisation group
  - Add Profile Owner as Member
  - Add group to subgroup
  
  To clean up users without profile for now we have Moderator View
  TODO create cron job
  
##Update Organisation

- If field_org_title is changed then update related group label

##Disable Organisation
  

##TODO
Bolje hendlat no-email i "odbori, povjerenstva i druga radna tijela Hrvatskoga sabora". 
Dali to treba biti user ili taxonomy.
Objasnit Organisation Group Taxonomy.
Objasnit i dodat descriptone na fieldove - field_org_parent_organisation i field_org_organisation_group