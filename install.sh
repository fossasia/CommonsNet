#!/usr/bin/env bash
# Install essential packages from Apt
sudo apt-get update -y
sudo apt-get install --force-yes -y git
sudo apt-get install -y npm
sudo apt-get install -y libffi-dev
sudo apt-get install -y postgresql postgresql-contrib
sudo npm install -g bower
sudo ln -s /usr/bin/nodejs /usr/bin/node
bower install

APP_DB_USER=commonsnet_user
APP_DB_PASS=start
APP_DB_NAME=commonsnet

# Prepare postgres database
# cat << EOF | sudo -u postgres psql
# -- Create the database user:
# CREATE USER $APP_DB_USER WITH PASSWORD '$APP_DB_PASS';

# -- Create the database:
# CREATE DATABASE $APP_DB_NAME WITH OWNER=$APP_DB_USER
#                                   LC_COLLATE='en_US.utf8'
#                                   LC_CTYPE='en_US.utf8'
#                                   ENCODING='UTF8'
#                                   TEMPLATE=template0;
# EOF

# echo "exporting database url for app"
# export DATABASE_URL=postgresql://$APP_DB_USER:$APP_DB_PASS@localhost:5432/$APP_DB_NAME

# echo "export DATABASE_URL=$DATABASE_URL" >> /home/vagrant/.bashrc

# sudo chown -R $(whoami) ~/.npm
# sh selenium_install