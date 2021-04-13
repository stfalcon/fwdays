FROM debian:stretch
RUN apt-get update && apt-get install -y wget curl ca-certificates procps locales zip apt-transport-https git libpng-dev libxml2 librsvg2-bin imagemagick gnupg jpegoptim optipng && rm -rf /var/lib/apt/lists/*
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo 'deb https://packages.sury.org/php/ stretch main' > /etc/apt/sources.list.d/php.list
RUN wget https://repo.percona.com/apt/percona-release_0.1-4.stretch_all.deb -O /tmp/percona.deb && dpkg -i /tmp/percona.deb && rm /tmp/percona.deb
RUN apt-get update && apt-get install -y php7.3-redis php7.3-sqlite3 php7.3-bcmath php7.3-mbstring php7.3-xml php-amqplib php7.3-fpm php-pear php7.3 php7.3-apcu php7.3-cli php7.3-common php7.3-curl php7.3-dev php7.3-gd php7.3-imagick php7.3-imap php7.3-intl percona-server-client-5.6 php7.3-mysql php7.3-zip && rm -rf /var/lib/apt/lists/*
RUN cd /usr/local/bin && curl -s https://getcomposer.org/installer | php -- --version=1.10.21
ENV NODE_VERSION 6.11.3
RUN curl -SLO https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.gz && tar -xvzf node-v$NODE_VERSION-linux-x64.tar.gz -C / --strip-components=1 && rm node-v$NODE_VERSION-linux-x64.tar.gz
RUN npm install -g grunt
