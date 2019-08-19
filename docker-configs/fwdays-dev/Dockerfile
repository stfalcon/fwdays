FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-base:v1.0
RUN apt-get update && apt-get install -y sudo php7.3-xdebug
RUN echo 'www-data ALL=(ALL) NOPASSWD: ALL' > /etc/sudoers.d/10_www_data
ADD configs/www.conf /etc/php/7.3/fpm/pool.d/www.conf
ADD configs/xdebug.ini /etc/php/7.3/mods-available/xdebug.ini
ADD configs/xdebug-cli /usr/local/bin/xdebug-cli
RUN mkdir /.macos_conigs
ADD configs/mac_xdebug.ini /.macos_conigs/mac_xdebug.ini
ADD configs/mac_xdebug-cli /.macos_conigs/mac_xdebug-cli
RUN chmod a+x /usr/local/bin/xdebug-cli
ADD configs/start /usr/local/bin/start
RUN chmod a+x /usr/local/bin/start
RUN mkdir /app && chown www-data:www-data /app
RUN mkdir /var/www
ADD configs/bash-history-user /var/www/.bashrc
ADD configs/bash-history-root /root/.bashrc
USER www-data
WORKDIR /app
CMD sudo /usr/local/bin/start
