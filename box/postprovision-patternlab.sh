#!/bin/bash
#
# Install zsh
#

DASING_DIR=/var/www/dv/docroot/themes/custom/dashing
PATTERNLAB_DIR=/var/www/dv/docroot/themes/custom/dashing/pattern-lab
PATTERNLAB_DIR_ALIAS=/var/www/patternlab

PATTERNLAB_ALIAS_BOWER=bower_components
PATTERNLAB_ALIAS_JS=js
PATTERNLAB_ALIAS_NODE=node_modules
PATTERNLAB_ALIAS_DIST=dist
PATTERNLAB_ALIAS_DIST=logo.svg

# Create public directory
if [ ! -d "$PATTERNLAB_DIR/vendor" ]; then
  cd "$PATTERNLAB_DIR"
  composer install
fi

# Create public directory
if [ ! -d "$DASHING_DIR/node_modules" ]; then
  cd "$DASHING_DIR"
  sudo npm install
fi

# Create /public directory aliases
if [ ! -d "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_BOWER" ]; then
  sudo ln -s "$DASING_DIR"/"$PATTERNLAB_ALIAS_BOWER" "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_BOWER"
fi

if [ ! -d "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_JS" ]; then
  sudo ln -s "$DASING_DIR"/"$PATTERNLAB_ALIAS_JS" "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_JS"
fi

if [ ! -d "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_NODE" ]; then
  sudo ln -s "$DASING_DIR"/"$PATTERNLAB_ALIAS_NODE" "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_NODE"
fi

if [ ! -d "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_DIST" ]; then
  sudo ln -s "$DASING_DIR"/"$PATTERNLAB_ALIAS_DIST" "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_DIST"
fi

if [ ! -e "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_LOGO" ]; then
  sudo ln -s "$DASING_DIR"/"$PATTERNLAB_ALIAS_LOGO" "$PATTERNLAB_DIR"/public/"$PATTERNLAB_ALIAS_LOGO"
fi

# add pattern lab alias to /var/www
if [ ! -d "$PATTERNLAB_DIR_ALIAS" ]; then
  sudo ln -s "$PATTERNLAB_DIR"/public "$PATTERNLAB_DIR_ALIAS"
fi

exit 0
