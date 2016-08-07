
To run tests you need to follow this simple instruction and write only a few simple commands in your terminal 
<br>Let's start 

* Clone CommonsNet repository 
```
  git clone https://github.com/fossasia/CommonsNet.git
```
* Open cloned repository 
```
cd Commonsnet 
```
* Install Vagrant
```
vagrant up
```
* Connect to Vagrant
```
vagrant ssh
```
* Open your Vagrant folder
```
cd /vagrant 
```
* Then run selenium file 
```
sh selenium_install.sh 
```
*  Next open provision folder
```
cd provision
```
* Install java-jar 
```
DISPLAY=:1 xvfb-run java -jar selenium-server-standalone-2.41.0.jar 
```
#### Your selenium server should be up and running

*  Then open a new terminal - remember not to close the first one!

*  Open your CommonsNet repository again 
```
cd CommonsNet
```
*  Connect to Vagrant again
```
vagrant ssh
```
*  Open Vagrant folder again
```
cd /vagrant
```
* Then open <b>tests</b> folder
```
cd tests
```
*  And finally run Protractor test
```
protractor conf.js 
```

#### That's it. You should see a result of your test in a terminal. 
