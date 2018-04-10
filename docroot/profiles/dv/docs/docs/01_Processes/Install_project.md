---
Title: Install Project
Diagram: Yes
---

# Install project

**Requirements**

To use our build system and run documentation locally you will need
- Fresh installation of **Ubuntu 16.04 LTS**
- Github account

and then follow the steps bellow:

## Setup local environment
- relations: Fork the repository

**Upgrade Linux**
```
do-release-upgrade
sudo reboot
```
**Install and configure git**
```
sudo apt-get install git -y
git config --global user.name "github_username"
git config --global user.email "github_email"
```
You can edit the ```~/.gitconfig``` file in your home folder. This is where all ```--global``` settings are saved.

## Fork the repository
- relations: Clone the forked repository

[How to fork a repository?](https://help.github.com/articles/fork-a-repo/)

## Clone the forked repository
- relations: Install project requirements
```
sudo mkdir /var/www/dv -p
sudo chown $USERNAME:$USERNAME /var/www/dv
cd /var/www
git clone https://github.com/github_username/dv
```

## Install project requirements
- relations: Test the installation

```
bash /var/www/dv/install/install.sh
```

Project requirements are installed using ansible playbooks taken from drupal VM


Install.sh script will among other things run
- composer install
- blt custom:reinstall

## Test the installation

Visit [local.dv.com](http://local.dv.com)
