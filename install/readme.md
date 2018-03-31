# Installation instructions

### Linux - Ubuntu/Mint

Ctrl + Alt + T to open terminal.

```bash
- do-release-upgrade
- sudo reboot
```

Fork https://github.com/dreamfony/dv git repository.

```bash
- sudo apt-get install git -y
- git config --global user.name "John Doe"
- git config --global user.email "email@example.com"
- sudo mkdir /var/www/dv -p
- sudo chown your_linux_user_name:your_linux_user_name /var/www/dv
- cd /var/www
- git clone your forked repo
- bash /var/www/dv/install/install.sh
```

Logout as your linux user and log back in to get zsh to work. if you get some weird page on local.dv.com clear chrome caches

> todo:
> - add adminer to /etc/hosts
> - install phpmyadmin ?
> - figure out weather we want to keep built css files in repo

 
### PHPSTORM

Install https://www.jetbrains.com/phpstorm/download/#section=linux

#### Plugins:
- .ignore
- Drupal Symphony Bridge
- set code style to Drupal

#### PHP Debug

Follow instructions https://www.shooflydesign.org/buzz/configuring-phpstorm-to-debugging-command-line-php-scripts

> - Set xdebug port 9001
> - Max connections 20 (to be on a safe side)
> - Turn off all Break on first line checkboxes there should be 3 of them

```bash
- sudo phpenmod -v 7.0 -cli xdebug
- sudo service php7.0-fpm restart
- export XDEBUG_CONFIG="idekey=PHPSTORM"
~ export PHP_IDE_CONFIG="serverName=localhost"
```

### Additional software

#### Chrome (required)
- https://www.google.com/chrome/browser/desktop/index.html
Make sure you pick Ubuntu version.

#### TeamViewer (required)
- https://download.teamviewer.com/download/linux/teamviewer_amd64.deb

#### Flux
```bash
- sudo add-apt-repository ppa:nathan-renniewaldock/flux
- sudo apt-get update
- sudo apt-get install fluxgui
```

#### Pinta
```bash
- sudo add-apt-repository ppa:pinta-maintainers/pinta-stable
- sudo apt-get update
- sudo apt-get install pinta
```



