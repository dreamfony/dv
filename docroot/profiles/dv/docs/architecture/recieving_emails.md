# Receiving Emails - Mail IN
- [inmail](../../../../modules/contrib/inmail/inmail.info.yml) contrib. module
  - dmt_mail module
    - [ToAnalizer](../../modules/custom/dmt_mail/src/Plugin/inmail/Analyzer/ToAnalyzer.php)
      - gets hash from email 
        - @see [Mailing list activity](mailing_list_activity.md) field Hash
  - dmt_mailing_list module
    - [InMailAnswer](../../modules/custom/dmt_mailing_list/src/Plugin/inmail/Handler/InMailAnswer.php)
      - uses hash to create answer for the right content
       
## Auto response handling

- split [ToAnalizer](../../modules/custom/dmt_mail/src/Plugin/inmail/Analyzer/ToAnalyzer.php) in two ToAnalyzer and HashAnalyzer
  - ToAnalyzer should have only findTo method
  - HashAnalyzer
    - findHash
    
- implement ActivityAnalyzer:
  - use HashAnalyzer result
    - findActivity (set Context Activity entity)
    - return activity context = activity entity

- implement AutoreponseAnalyzer in dmt_mail module
  - implement config field for difference check int in seconds
  - use findActivity result to:
  - get last revision from activity with [ar_sent]
  - if (mail received time) - [ar_sent] <= config value
  - set context autoresponse bool  

- use the result in [InMailAnswer](../../modules/custom/dmt_mailing_list/src/Plugin/inmail/Handler/InMailAnswer.php) to ignore autoresponse mails
- make changes to InMailAnswer to use ActivityAnalyzer context to get the entity
                       
- think about making DmtInMail handler to make the checks and then extend it for InMailAnswer                       
