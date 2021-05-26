FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-base:v1.4
RUN apt-get update && apt-get install -y sudo php7.3-xdebug
RUN wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -O /tmp/chrome.deb && apt install -y /tmp/chrome.deb && rm /tmp/chrome.deb
RUN CHROME_VER=`apt-cache show google-chrome-stable|grep "Version:"|awk '{print $2}'|cut -d '.' -f 1,2,3` && \ 
    CHROMEDRIVER_VER=`curl https://chromedriver.storage.googleapis.com/LATEST_RELEASE_$CHROME_VER` && \
    wget https://chromedriver.storage.googleapis.com/$CHROMEDRIVER_VER/chromedriver_linux64.zip -O /tmp/chromedriver.zip && unzip /tmp/chromedriver.zip -d /opt/ && \
    rm /tmp/chromedriver.zip
RUN echo 'www-data ALL=(ALL) NOPASSWD: ALL' > /etc/sudoers.d/10_www_data
ADD configs/www.conf /etc/php/7.3/fpm/pool.d/www.conf
ADD configs/xdebug.ini /etc/php/7.3/mods-available/xdebug.ini
ADD configs/start /usr/local/bin/start
RUN chmod a+x /usr/local/bin/start
RUN mkdir /app && chown www-data:www-data /app
RUN mkdir /var/www
ADD configs/bash-history-user /var/www/.bashrc
ADD configs/bash-history-root /root/.bashrc
USER www-data
WORKDIR /app
CMD sudo /usr/local/bin/start

