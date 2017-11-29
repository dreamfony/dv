#!/bin/bash

alias drush="/var/www/dv/vendor/drush/drush/drush"

PHP_DIR="/etc/php/7.0"

cd $PHP_DIR

sudo rm $PHP_DIR/mods-available/xdebug.ini

# remove xdebug form cli it can be enabled "sudo phpenmod -s cli xdebug"
sudo mv $PHP_DIR/cli/conf.d/20-xdebug.ini $PHP_DIR/mods-available/xdebug.ini

sudo rm $PHP_DIR/fpm/conf.d/20-xdebug.ini
sudo phpenmod -s fpm xdebug
sudo service php7.0-fpm restart






