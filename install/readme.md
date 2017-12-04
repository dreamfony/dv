# Installation instructions

## Linux - Ubuntu/Mint

- do-release-upgrade
- sudo reboot

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


## Additional software

### Chrome (required)
- https://www.google.com/chrome/browser/desktop/index.html?brand=CHBD&gclid=Cj0KCQiAgZTRBRDmARIsAJvVWAteXkC1MBW19_nCIW8rYor7LO4XqslVVDnpcwifDkU6gw-D9vg-04QaAj7mEALw_wcB

### TeamViewer (required)
- https://download.teamviewer.com/download/linux/teamviewer_amd64.deb

### Flux
- sudo add-apt-repository ppa:nathan-renniewaldock/flux
- sudo apt-get update
- sudo apt-get install fluxgui

### Pinta
- sudo add-apt-repository ppa:pinta-maintainers/pinta-stable
- sudo apt-get update
- sudo apt-get install pinta



