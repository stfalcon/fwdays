FROM debian:stretch
RUN apt-get update && apt-get install -y wget curl ca-certificates procps locales zip apt-transport-https git libpng-dev libxml2 librsvg2-bin imagemagick
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo 'deb https://packages.sury.org/php/ stretch main' > /etc/apt/sources.list.d/php.list
RUN apt-get update && apt-get install -y php-pear php7.1 php7.1-apcu php7.1-cli php7.1-common php7.1-curl php7.1-dev php7.1-gd php7.1-imagick php7.1-imap php7.1-intl php7.1-mcrypt php7.1-redis php7.1-sqlite3 php7.1-bcmath php7.1-mbstring php7.1-xml php-amqplib php7.1-fpm
RUN wget https://repo.percona.com/apt/percona-release_0.1-4.stretch_all.deb -O /tmp/percona.deb
RUN dpkg -i /tmp/percona.deb && rm /tmp/percona.deb && apt-get update
RUN apt-get install -y percona-server-client-5.6 php7.1-mysql
RUN cd /tmp && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN cd /tmp && php -r "if (hash_file('SHA384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN cd /tmp && php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN cd /tmp && php -r "unlink('composer-setup.php');"
ENV NODE_VERSION 6.11.3
RUN curl -SLO https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.gz
RUN tar -xvzf node-v$NODE_VERSION-linux-x64.tar.gz -C / --strip-components=1
RUN npm install -g grunt
