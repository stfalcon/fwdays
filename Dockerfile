FROM debian:jessie
MAINTAINER Andrij Tomchyshyn
RUN apt-get update && apt-get install -y aptitude wget curl ca-certificates procps locales git
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 php-cache-lite php-db php-pear php5 php5-apcu php5-curl php5-dev php5-gd php5-imagick php5-intl php5-mcrypt php5-mysqlnd php5-sqlite phpmyadmin ruby git

ADD docker-files/000-default.conf /etc/apache2/sites-enabled/000-default.conf
ADD docker-files/config-db.php /etc/phpmyadmin/config-db.php
ADD docker-files/phpmyadmin.conf /etc/apache2/conf-enabled/phpmyadmin.conf
ADD docker-files/xdebug.ini /etc/php5/mods-available/xdebug.ini
ADD docker-files/import-db /usr/local/bin/import-db
ADD docker-files/config.inc.php /etc/phpmyadmin/config.inc.php
RUN chmod a+x /usr/local/bin/import-db
RUN a2enmod rewrite
ADD docker-files/start /usr/local/bin/start
ADD docker-files/apache_php.ini /etc/php5/apache2/php.ini
ADD docker-files/cli_php.ini /etc/php5/cli/php.ini
RUN chmod a+x /usr/local/bin/start
WORKDIR /var/www/html
CMD /usr/local/bin/start
