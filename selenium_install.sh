sudo npm cache clean -f
sudo npm install -g n
sudo n 6.2.2
sudo ln -sf /usr/local/n/versions/node/6.2.2/bin/node /usr/bin/node 

sudo apt-get install -y default-jdk
sudo apt-get install xvfb -y
sudo apt-get install -y chromium-browser
sudo cp provision/chromedriver /usr/bin
sudo chmod a+x /usr/bin/chromedriver
sudo webdriver-manager update
sudo npm install -g protractor