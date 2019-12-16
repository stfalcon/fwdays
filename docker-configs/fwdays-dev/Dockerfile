FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-dev:v1.1
RUN sudo wget https://chromedriver.storage.googleapis.com/79.0.3945.36/chromedriver_linux64.zip -O /tmp/chromedriver.zip && \
    sudo unzip /tmp/chromedriver.zip -d /opt/ && sudo rm /tmp/chromedriver.zip
RUN sudo wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -O /tmp/chrome.deb && \
    sudo dpkg -i /tmp/chrome.deb; exit 0
RUN sudo rm /tmp/chrome.deb && sudo chown -R www-data:www-data /tmp
RUN sudo apt-get install -fy && sudo apt-get update && sudo apt-get install -y php7.3-zip --option=Dpkg::Options::=--force-confdef

#FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-base:v1.0
#RUN apt-get update && apt-get install -y sudo php-xdebug=2.5.0-1
##RUN pecl install xdebug
#RUN echo 'www-data ALL=(ALL) NOPASSWD: ALL' > /etc/sudoers.d/10_www_data
#ADD configs/www.conf /etc/php/7.3/fpm/pool.d/www.conf
#ADD configs/xdebug.ini /etc/php/7.3/mods-available/xdebug.ini
#ADD configs/xdebug-cli /usr/local/bin/xdebug-cli
#RUN mkdir /.macos_conigs
#ADD configs/mac_xdebug.ini /.macos_conigs/mac_xdebug.ini
#ADD configs/mac_xdebug-cli /.macos_conigs/mac_xdebug-cli
#RUN chmod a+x /usr/local/bin/xdebug-cli
#ADD configs/start /usr/local/bin/start
#RUN chmod a+x /usr/local/bin/start
#RUN mkdir /app && chown www-data:www-data /app
#RUN mkdir /var/www
#ADD configs/bash-history-user /var/www/.bashrc
#ADD configs/bash-history-root /root/.bashrc
#USER www-data
#WORKDIR /app
#CMD sudo /usr/local/bin/start