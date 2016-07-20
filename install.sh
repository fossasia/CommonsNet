#!/usr/bin/env bash
# Install essential packages from Apt
sudo apt-get update -y
sudo apt-get install --force-yes -y git
sudo apt-get install -y npm
sudo npm install -g bower
sudo ln -s /usr/bin/nodejs /usr/bin/node
bower install
sudo chown -R $(whoami) ~/.npm