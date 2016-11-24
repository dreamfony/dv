#!/bin/bash
#
# Install zsh
#

ZSH_SETUP_COMPLETE_FILE=/etc/zsh_install_complete

# Check to see if we've already performed this setup.
if [ ! -e "$ZSH_SETUP_COMPLETE_FILE" ]; then

  sudo apt-get -y install zsh
  sudo apt-get -y install zsh

  sudo wget https://github.com/robbyrussell/oh-my-zsh/raw/master/tools/install.sh -O - | zsh

  sudo chsh -s `which zsh` vagrant

  sh -c "$(wget https://raw.githubusercontent.com/robbyrussell/oh-my-zsh/master/tools/install.sh -O -)"

  # Create a file to indicate this script has already run.
  sudo touch $ZSH_SETUP_COMPLETE_FILE

else
  exit 0
fi
