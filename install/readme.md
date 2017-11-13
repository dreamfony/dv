# Installation instructions

## Linux Ubuntu

- sudo apt-get install git -y
- git config --global user.name "John Doe"
- git config --global user.email "email@example.com"
- git config --global push.default matching
- sudo mkdir /var/www/dv
- sudo chown your_linux_user_name:your_linux_user_name /var/www/dv
- cd /var/www
- git clone https://github.com/dreamfony/dv.git
- cd dv/lamp
- bash ./install.sh
- if you get some wierd page on local.dv.com clear chrome caches
- logout as your linux user and log back in to get zsh to work


todo:
 - add adminer to /etc/hosts
 - sort out dv_secure
 - sort out drush aliases
 - check if firewall is interrupting xdebug
 
## PHPSTORM
xdebug port 9001


