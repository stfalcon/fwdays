FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-base:v2
RUN apt-get update && apt-get install -y nginx-extras
ADD configs/fpm-pool.conf /etc/php/7.1/fpm/pool.d/www.conf
ADD configs/php-fpm.ini /etc/php/7.1/fpm/php.ini
ADD configs/php-cli.ini /etc/php/7.1/cli/php.ini
ADD configs/nginx.conf /etc/nginx/nginx.conf
ADD configs/nginx-vhost.conf /etc/nginx/conf.d/fwdays.conf
ADD configs/start /usr/local/bin/start
RUN chmod a+x /usr/local/bin/start
CMD /usr/local/bin/start
