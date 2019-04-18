FROM debian:stretch
RUN apt-get update && apt-get install -y wget curl ca-certificates procps locales zip apt-transport-https git libpng-dev libxml2 librsvg2-bin imagemagick gnupg jpegoptim optipng && rm -rf /var/lib/apt/lists/*
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo 'deb https://packages.sury.org/php/ stretch main' > /etc/apt/sources.list.d/php.list
RUN wget https://repo.percona.com/apt/percona-release_0.1-4.stretch_all.deb -O /tmp/percona.deb && dpkg -i /tmp/percona.deb && rm /tmp/percona.deb
RUN apt-get update && apt-get install -y php7.1-mcrypt php7.1-redis php7.1-sqlite3 php7.1-bcmath php7.1-mbstring php7.1-xml php-amqplib php7.1-fpm php-pear php7.1 php7.1-apcu php7.1-cli php7.1-common php7.1-curl php7.1-dev php7.1-gd php7.1-imagick php7.1-imap php7.1-intl percona-server-client-5.6 php7.1-mysql && rm -rf /var/lib/apt/lists/*
RUN cd /usr/local/bin && curl -s https://getcomposer.org/installer | php
ENV NODE_VERSION 6.11.3
RUN curl -SLO https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.gz && tar -xvzf node-v$NODE_VERSION-linux-x64.tar.gz -C / --strip-components=1 && rm node-v$NODE_VERSION-linux-x64.tar.gz
RUN npm install -g grunt
