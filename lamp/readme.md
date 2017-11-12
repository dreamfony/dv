# Installation instructions

## Linux Ubuntu

- sudo apt-get install git
- git config --global user.name "John Doe"
- git config --global user.email "email@example.com"
- mkdir ~/projects
- cd ~/projects
- git clone https://github.com/dreamfony/dv.git
- cd dv/lamp
- sudo mkdir /var/www
- sudo ln -s ~/projects/dv /var/www/dv
- bash ./install.sh

