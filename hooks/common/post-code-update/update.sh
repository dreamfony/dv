#!/bin/sh
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

echo "Cache-Rebuild"
drush @$site.$target_env -y cache-rebuild

echo "UpdateDB"
drush @$site.$target_env -y updatedb

echo "Config-import"
drush @$site.$target_env -y config-import

echo "entup"
drush @$site.$target_env -y entup


# TODO clear varnish
# TODO do this on code deploy also.
# TODO If deploy Backup DB in first step!!!!
# drush vset maintenance_mode 1
# Flush Caches to empty the cache tables and ensure maintenance mode is set
# drush sql-dump > backup-yyyy-mm-dd.sql
# revert features via hook_update, never revert all features via drush on production