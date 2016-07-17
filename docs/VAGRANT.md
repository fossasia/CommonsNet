##How to install and run CommonsNet on Vagrant environment.

### 1. Download Vagrant and VirtualBox
 - [Vagrant] (https://www.vagrantup.com/downloads.html)
 - [VirtualBox] (https://www.virtualbox.org/)

### 2. Clone folder CommonsNet
 - choose development branch 
 - click clone or download button and copy link
 - open your terminal, choose directory for e.g. Desktop and type 
      <git clone /copied/link>
 - then type cd CommonsNet
 
### 3. Run Vagrant
 CommonsNet consists of two config files install.sh and Vagrantfile - so you don't need to create any config on your own, so simply run
 - vagrant up
 - vagrant ssh


### 4. Run Node server on Vagrant
 As soon as you connect to your Vagrant environment type
 - cd /vagrant
 - node server.js

### 5. Finish
 That's it. You have run our node app on Vagrant. Open your browser and type localhost which you find in terminal. Enjoy. 


 
