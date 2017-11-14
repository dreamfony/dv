# Installation instructions

## Linux Ubuntu

- fork https://github.com/dreamfony/dv repo

- sudo apt-get install git -y
- git config --global user.name "John Doe"
- git config --global user.email "email@example.com"
- git config --global push.default matching
- sudo mkdir /var/www/dv
- sudo chown your_linux_user_name:your_linux_user_name /var/www/dv
- cd /var/www
- git clone your forked repo
- git checkout develop
- cd dv/lamp
- bash ./install.sh
- if you get some wierd page on local.dv.com clear chrome caches
- logout as your linux user and log back in to get zsh to work

todo:
 - add adminer to /etc/hosts
 - sort out drush aliases
 - install phpmyadmin ?
 - check if firewall is interrupting xdebug
 
## PHPSTORM

- Set xdebug port 9001

**Add Plugins**:
- .ignore
- Drupal Symphony Bridge
- set code style to Drupal


