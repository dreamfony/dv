---
Title: Contribute
Diagram: Yes
---

# Contribute code to upstream repository

## Sync the fork with upstream repository
- relations: Create new branch
- meta:
  - tools:
    - GitHub

```
git fetch upstream
git checkout develop
git rebase upstream/develop
git push
```
## Create new branch
- relations: Commit the code

```
git checkout -b dreamfony/dv#issueNumber-[feature / fix / misc]
```

## Commit the code
- relations: Push the branch

```
git status
git add [file1] [file2]
git commit -m "dreamfony/dv#issueNuber Description shorter than 50 characters"
```

## Push the branch
- relations: Open or update a Pull Request

```
git push -u origin new_branch_name
```

## Open or update a Pull Request
- relations: CI status check, Update issue status, Notifications
- meta:
  - tools:
    - GitHub
    - hub

**Tips for PR Authors**


- Once the pull request is ready for review, add reviewers to it through the web interface

**Using GitHub UI**

Create a [pull request](https://help.github.com/articles/creating-a-pull-request/) from newly pushed branch.

**Using hub**


```
hub pull-request
```

**Using PhpStorm**

```
VCS->GIT->Create Pull Request
```


## CI status check
- relations: Discuss and peer review Code
- meta:
  - roles:
    - System
  - tools:
    - Travis

## Update issue status
- meta:
  - roles:
    - System
  - tools:
    - Waffle.io
* Move Issue to Review column

## Notifications
- meta:
  - roles:
    - System
  - tools:
      - Slack

## Discuss and peer review Code
- relations: Pull request accepted|Deploy, Pull request rejected|Commit the code
- meta:
  - tools:
    - GitHub

**Tips for PR Reviewers**

[Review the changes](https://help.github.com/categories/collaborating-with-issues-and-pull-requests/) in the pull request, and optionally, comment on specific lines.

## Deploy
- meta:
  - tools:
    - GitHub
    - Acquia
