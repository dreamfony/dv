# VM installation instructions

#### System requirements

Make sure you have the latest versions of packages

- Git
	- git flow
- Composer
- PHP 5.6+
 - If you are on Ubuntu you will also need additional php packages
 	  - sudo apt-get install php-xml
 	  - sudo apt-get install php7.0-mbstring
 	  - sudo apt-get install php-curl
- Vagrant (comes with virtualbox / ansible)
- nodejs (for patternLab; it should install gulp / bower etc)


#### First Time Installation
@todo: update this with instructions when forking repos and making pull requests)

- sudo apt-get install php7.0-bz2

- git clone https://github.com/dreamfony/dv.git
- cd dv
- git checkout develop
- obtain dv_secure module directory from stakholders and put it in proper place /dv/docroot/profiles/dv/modules/dv_features/dv_secure (* we should make this more automatic)
- composer install
- vagrant up
- vagrant ssh
- cd /var/www/dv
- composer blt-alias
- (restart ssh terminal: exit / vagrant ssh)
- go to /var/www/dv/blt/project.local.yml and add environment: 'local'
- cd /var/www/dv
- blt setup
- cd docroot
- drupal init
- to install alias for blt on local machine, run "sudo composer run-script blt-alias"
- blt custom:import-content


### Vagrant

*VM on Windows*

https://www.jeffgeerling.com/blog/2017/drupal-vm-on-windows-fast-container-blt-project-development

Install the Vagrant::Hostsupdater plugin with
`vagrant plugin install vagrant-hostsupdater`
which will manage the host’s /etc/hosts file by adding and removing hostname entries for you

    vagrant plugin install vagrant-cachier
    vagrant plugin install vagrant-vbguest

 On windows you have to do multiple things to make NFS sharing work

    1. vagrant plugin install vagrant-winnfsd

    2. COPY EXAMPLE FILES from custom folder
    local.config.yml
    Vagrantfile.local

    3. Download and run WinNFSd.exe C:\DV
    4. Delete node_modules folder to make it speedier
    5. Sometimes also settings.php needs to be chmod ed

    It works with thoose 4 steps, no single article online is correct
    But some info and code parts can be found at:
    https://hollyit.net/blog/windowsvagrantwinnfsd-without-file-update-problems
    http://docs.drupalvm.com/en/latest/other/performance/#improving-performance-on-windows

  after that you will have to destroy machine and provision it again

    vagrant destroy
    vagrant up

{{BUG}}
On MAC we had to change VirtualBox Machine network setting: Adapter1 / NAT / Cable connect

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
