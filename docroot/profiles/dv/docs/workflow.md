# Workflow

## Development
- relations: Code repository
- meta:
  - roles:
     - Developer
     - DevOps
  - tools:
     - Drupal
- doc: http://some-path/doc.md#drupal
### Test h3

## Code repository
- relations: Documentation, Continous integration
- icons:
  - roles:
     - Developer
     - DevOps
  - tools:
     - Drupal
- doc: http://some-path/doc1.md#github

## Documentation
- icons:
  - roles:
     - Developer
     - DevOps
  - tools:
     - Drupal
- doc: http://some-path/doc-about-doc.md#read-the-docs

## Continous integration
- relations: "Notifications"
- icons:
  - roles:
     - Developer
     - DevOps
  - tools:
     - Drupal
- doc: http://some-path/doc.md

## Notifications
- relations: "Test passed?"
- icons:
  - roles:
     - Developer
     - DevOps
  - tools:
     - Drupal
- doc: http://some-path/doc3.md#slack

## Test passed?
- type: decision
- relations: Yes|Artifact, No|Development

## Artifact
- role: "DevOps"
- tool: "Travis"
- doc: http://some-path/doc.md#artifact;




