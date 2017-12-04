# Installation instructions

## Linux - Ubuntu/Mint

- fork https://github.com/dreamfony/dv repo
- sudo apt-get install git -y
- git config --global user.name "John Doe"
- git config --global user.email "email@example.com"
- sudo mkdir /var/www/dv -p
- sudo chown your_linux_user_name:your_linux_user_name /var/www/dv
- cd /var/www
- git clone your forked repo
- bash /var/www/dv/install/install.sh
- logout as your linux user and log back in to get zsh to work
- if you get some weird page on local.dv.com clear chrome caches

todo:
 - add adminer to /etc/hosts
 - install phpmyadmin ?
 - figure out weather we want to keep built css files in repo

 
## PHPSTORM

- Set xdebug port 9001
- Max connections 100 (to be on a safe side)
- Turn off all Break on first line checkboxes there should be 3 of them

## Setup CLI debugging
- sudo phpenmod -v 7.0 -cli xdebug
- sudo service php7.0-fpm restart
- https://www.shooflydesign.org/buzz/configuring-phpstorm-to-debugging-command-line-php-scripts
- export XDEBUG_CONFIG="idekey=PHPSTORM"
~ export PHP_IDE_CONFIG="serverName=localhost"      

**Add Plugins**:
- .ignore
- Drupal Symphony Bridge
- set code style to Drupal


