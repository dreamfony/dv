Please see the [BLT documentation](http://blt.readthedocs.io/en/latest/) for information on build, testing, and deployment processes.

develop:
[![Build Status develop](https://travis-ci.org/dreamfony/dv.svg?branch=develop)](https://travis-ci.org/dreamfony/dv)

master:
[![Build Status master](https://travis-ci.org/dreamfony/dv.svg?branch=master)](https://travis-ci.org/dreamfony/dv)




### Onboarding

For building, testing and launching drupal sites we use bundle of scripts called [BLT](https://github.com/acquia/blt)
BLT works as a composer plugin.

http://blt.readthedocs.io/en/latest/INSTALL/
http://blt.readthedocs.io/en/latest/readme/onboarding/

#### System requirements

- Git
	- git flow
- Composer
- PHP 5.6+
 - If you are on Ubuntu you will also need additional php packages
 	  - sudo apt-get install php-xml
 	  - sudo apt-get install php7.0-mbstring
 	  - sudo apt-get install php-curl
- Vagrant (comes with virtualbox)


#### Initial setup (update this with instructions when forking repos and making pull requests)

- git clone
- composer install
- vagrant up
- vagrant ssh
 - composer blt-alias
 - blt local:setup

#### Pushing Local changes and Ongoing development

- export configuration into feature
- git commit
- git pull / merge / create pull request
- blt local:refresh (which does)
  - composer install
  - enable / uninstall local modules
  - config-import --partial
  - updb
  - config-import --partial
  - drush fra -- bundle (bundle names are defined in project.yml)
  - drush cr
- git push

Automated testing ensures that the feature can be installed from scratch on a new site as well as imported without conflicts on an existing site.
After the feature is deployed, deployment hooks automatically import the new or updated configuration.

In the beginning of the project life cycle we use `blt local:setup` to reinstall drupal, instead of `blt local:refresh` so we dont have to write hook_updates when we do big structural configuration changes.
When the site goes to production then `local:refresh` is enough.

    <target name="local:refresh" description="Refreshes local environment from upstream testing database." depends="setup:build, local:sync, local:update"/>


#### Updating Module

When updating module, it can happen that modules configuration has changed.
Thats why the process of updating module is:
- composer update drupal/{module_name}
- on features page check if configuration has changed
- export feature if neccessary
- only now you can push or pull and refresh local

If someone pushes lock file with new updated module and doesnt export configuration,
then when other persons pull and do local:refresh they will override new configuration of the module with the old!

#### Various

**CI**

http://blt.readthedocs.io/en/latest/readme/ci/

The repository is never pushed directly to the cloud. Instead, changes to the repository on GitHub trigger tests to be run via Continuous Integration. Changes that pass testing on master branch will automatically cause a build artifact to be created and deployed to the cloud.

We use Travis for CI

If you don’t want to run a build for a particular commit for any reason add [ci skip] or **[skip ci]** to the git commit message. Commits that have [ci skip] or [skip ci] anywhere in the commit messages are ignored by Travis CI.


**Phing**
Common project tasks are executed via a build tool (Phing) so that they can be executed exactly the same in all circumstances. 
Custom and overridden Commands can be found in **/custom/blt_custom_phing_commands.xml**
Phing Variables are here
https://github.com/acquia/blt/blob/8.x/phing/build.yml

To use syslog instead of DB log

    blt pimpmylog

### Acquia
#### Cloud Hooks

post-code-update.sh 

- blt deploy:update

db-scrub.sh 

- scrubs db before copy from production **TODO - test**


#### Cron

You should use the Scheduled Jobs page for scheduled jobs, rather than the default Drupal cron or any of the contributed cron modules, such as Elysia Cron or Ultimate Cron . Compared to other cron solutions, using the Scheduled Jobs page is more reliable and provides extensive and integrated logging for Acquia Cloud applications.
The default Drupal cron (poor man's cron) is enabled by default and you should disable it. Click Never.

    drush core-cron



### Vagrant

Install the Vagrant::Hostsupdater plugin with
`vagrant plugin install vagrant-hostsupdater` 
which will manage the host’s /etc/hosts file by adding and removing hostname entries for you

    vagrant plugin install vagrant-cachier
    vagrant plugin install vagrant-vbguest
 
 On windows u can try

    vagrant plugin install vagrant-winnfsd

BLT uses [DrupalVM](https://www.drupalvm.com/)
Configuration file can be found at `/box/config.yml`
For local overrides use local.config.yml

DrupalVM configuration file settings are used in both vagrantfile and ansible playbook.yml
Thats why sometimes it is necessary to re provision machine depending on what settings you change inside file.
DrupalVM Configuration is merged:
- config.yml
- local.config.yml
The merge of the variables in these two files is shallow, so if you want to override a single item in a list, you will need to re-define all items in that list.

**commands**

    vagrant up
    vagrant halt
    vagrant reload
    
    vagrant login
    #share http
    vagrant share
    #share ssh
    vagrant share –ssh


### BlackFire

- create account on blackfire.io
- enter server and client id
https://github.com/geerlingguy/ansible-role-blackfire#requirements

Restart appache after init and registration

    sudo /etc/init.d/blackfire-agent restart
    sudo systemctl restart apache2.service
    
    blackfire curl http://local.dv.com/


### Testing

http://blt.readthedocs.io/en/latest/readme/testing/

### Github

In order to more easily identify developers in a project, please be sure to set a name and profile picture in your GitHub profile.

**Tools**
https://chrome.google.com/webstore/detail/octotree/bkhaagjahfmjljalopjnoealnfndnagc

https://github.com/github/hub

### Drush

Check that your drush alias is set up correctly

    /var/www/dv/docroot drush @dv.local status

To be able to use drush from any folder for this site and in this session type:
 

    drush use @dv.local

### Drupal Console

**TODO**
Initialize drupal console after provision


### Phpstorm

You can SSH to Vagrant machine using phpstorm by ShiftShift, type: Start SSH

**TODO** - repo with shared liveTemplates
https://www.drupal.org/project/phpstorm_templates
and sharing of code snippets via gists or snip2code
https://youtrack.jetbrains.com/issue/IDEA-155623
**TODO END**

### Composer

http://blt.readthedocs.io/en/latest/readme/dependency-management/

We use composer to build our dependencies, add patches etc.. , that means you do not use drupal console or drush to download modules but you do it with composer. You must run composer from DV root folder, not docroot

    composer global require "hirak/prestissimo:^0.3"


#### Add dependencies

    composer require drupal/devel:8.*

**examples:**
Latest stable that is greater then or equal to 1.0
^1.0
Always Dev (ignore stables)
1.x-dev
Exact version
1.14

^ - sticks to semantic versioning, and will always allow non-breaking updates.

    ~1.2.3 is equivalent to >=1.2.3 <1.3.0

vs.

    ^1.2.3 is equivalent to >=1.2.3 <2.0.0

#### Update dependencies (core, profile, module, theme, libraries)

    composer update drupal/panels --with-dependencies


#### Remove dependencies

    composer remove drupal/pathauto

When adding new patch you can just update existing project and patch will be applied.


### Debuging

if u need to debug drush or any site code that is initiated within drush (PHP CLI) follow this [tutorial](http://blokspeed.net/blog/2016/02/debugging-drush-scripts-with-xdebug-and-phpstorm-on-vagrant-in-2016/)


- In PhpStorm set up a “PHP Web Application” for debugging the command line. The sole purpose of this is to be able to provide a **path mapping** when running the command in Vagrant.
- Enable xdebug debugging for the command line in your Vagrant box. In my case, this simply meant symlinking the same xdebug.ini from my /etc/php5/cli/conf.d directory as I was using in the /etc/php5/apache/conf.d for web debugging.
- `export XDEBUG_CONFIG="idekey=phpstorm remote_host=192.168.33.1"`
- `export PHP_IDE_CONFIG="serverName=cli"`
- `../vendor/drush/drush/drush.launcher status`


#### Twig Debugging

services.local.php
twig.config degbug:true


**TODO**

blt clidebug
blt clidebug --on
i off
ili tako nesto pametno
za palit gasit xdebug mozes koristit
sudo phpenmod -s cli xdebug
i phpdismod


Saznat zašto krepava debugger. Timeout


**TODO END**

### Configuration Management

http://blt.readthedocs.io/en/latest/readme/features-workflow/

The main use case for Features 3.x is to assist with building and maintaining well designed and interoperable Drupal distributions.

> As great as CMI is in Drupal 8, it is likely that you will still want
> to use Features to organize your configuration as you develop your
> site.  Whether you use Features or CMI (or both) to deploy your
> development into production is your choice.  In my narrow experience
> with features on Drupal 8, I've found the "features for development
> and config for deployment" idea, the more natural way to do it

Sites will often need to contain a single "sitewide" feature that defines global configuration and is required by all other features. This can be viewed as a "core" feature but should not be abused as a dumping ground for miscellany.

Our core feature module is **dv_core**

#### Sharing configuration of development modules and different configuration for differnet environments

Instead of using conf_split module, we store partial various configuration that is used only in devel environment in config/devel folder.
This should be in your local.settings.php

    $config_directories['devel'] = $dir . "$config_directories['devel'] = $dir . "/docroot/profiles/custom/dv/modules/environment/dmt_devel/optional";

Import with

    drush cim devel --partial
    
Configuration files that should be exported to that module are listed in dmt_devel.info.yml
And should be exported with the help of config_devel module using

    drush config-devel-export dmt_devel

Also you can use configuration override system in local.settings.php

Exclude settings from beeing exported in features by configuring features bundle

Modules that should be enabled uninstalled for each environment are listed in project.yml

Production configuration is made read only using
https://www.drupal.org/project/config_readonly


All site code should reside in `docroot\profiles\custom\dv`

There are many reasons that features can fail to install or import properly. The most frequent cause is circular dependencies. For instance, imagine that feature A depends on a field exported in feature B, and feature B depends on a field exported in feature B. Neither feature can be enabled first, and site installs will break. 

A safer alternative is to create a separate wrapper module to contain any custom functionality and have this module depend on your feature in order to segregate Feature-managed and manually-managed code.

#### Hook_updates

https://www.drupal.org/docs/8/api/update-api/updating-configuration-in-drupal-8

All site specific configuration updates should be written in **dv_core.install**

**TODO add snippets for**
Deleting a field
Reverting features and feature components features_revert_module()
Enable / disable module module_enable()
Adding indexes to databases db_add_index()

For specific use cases see
https://www.drupal.org/project/hook_update_deploy_tools
also we will continuously update this document as we find out which things can not be deployed through features and must be deployed using hook_updates

#### Environments
We have 4 environments

- Local environment build with vagrant
- and 3 remote environments which reside on Acquia
 - Devel ( master-build branch)
 - Staging (master branch)
 - Live (TAG from master)

We are using an install profile driven development. Install profile is build by features. 
We will also for now deploy site with features although this is not best practice.

Best practice would be to 

- Clone DB from Live env to Staging env
- Revert Features on Staging env
- Test
- Export Configuration on Staging and push to master
- git TAG release
- Import Configuration on Live

Other options include installing site from existing configuration either with drush --config option or with config_installer profile which also sets site UUID from existing configuration.


##### Managing roles and permissions

Features is patched so that it exports permissions with roles

##### Managing Secrets (TODO)

- exlude this settings from beeing exported in features bundle settings

  `// Store API Keys and things outside of version control.`
  
  `// @see settings/sample-secrets.settings.php for sample code.`
  
  `$secrets_file = sprintf('/mnt/gfs/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require $secrets_file;
  }`


#### Using Features Bundles

#### Config vs. content

If exported configuration view will contain a defined dependency on a content object (referenced by UUID). If that content doesn’t exist when the feature is installed, the installation will fail.

The solution is to make sure that the referenced content exists before the feature is installed.

Use the default_content module to export the referenced content as JSON files, and store these files with your feature or in a dependency.

**TODO** Create default_content module just for that

#### Local Settings

local.settings.php

 
### Naming Conventions

We utilize Features **Namespace** assignment plugin

so For example, a date field specific to events and attached to an **event content type** could be named **field_event_date**, while a vocabulary of event types could be named **event_type**, with a corresponding entity reference field of field_event_type on the event content type. By following this naming convention, you ensure that by default your event-related field storages and taxonomies are assigned to the event feature.


### Release Process

It is expected that at this point build artifact has been deployed to master-build on dev.
master-build has been merged to master on stage. Db from live has been copied to stage.

- Put the site into maintenance mode `drush vset maintenance_mode 1`
- Flush Caches to empty the cache tables and ensure maintenance mode is set. `drush cc all`
- Perform any necessary backups, notably the database drush sql-dump > backup-yyyy-mm-dd.sql (or via UI)
- Pull the latest code onto the server git pull origin/master (Drag&Drop in UI)
 - Doing this in Acquia UI will tag release.
- Run update.php `drush updb -y`
 -  Feature should be explicitly reverted via a call to features_revert_module() in a  hook_update_N()
- Take the site out of maintenance mode `drush vset maintenance_mode 0`
- Clear Drupal caches `drush cc all`

### Tips & Tricks


## TODO