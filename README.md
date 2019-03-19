It's engine for site of conference Frameworks Days
========================================

[![Build Status](https://secure.travis-ci.org/stfalcon/fwdays.png?branch=master)](https://travis-ci.org/stfalcon/fwdays)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/stfalcon/fwdays/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/stfalcon/fwdays/?branch=master)

### a) Install Composer 

	$ curl -s https://getcomposer.org/installer | php

### b) Install the Vendor Libraries

    $ ./composer.phar install

### c) Create DB, update it and load fixtures

    $ ./console doctrine:database:create
    $ ./console doctrine:migrations:migrate
    $ ./console doctrine:fixtures:load

### d) Import translations from translate files to DB

    $ ./console lexik:translations:import

### e) Install npm

    $ sudo apt-get install nodejs
    $ sudo apt-get install npm
    $ nvm install 6.9.2
    $ nvm use 6.9.2
    $ npm run gulp-dev

Setup dev-env via docker and fig
========================================

1) Running containers
--------------------------------

After it run:

    $ docker-compose up -d
        
After it run once for initialization:
        
    $ docker-compose exec php /app/init 

It's installs vendors, npm and setup database.

2) Some notes about configuration and usage:
--------------------------------

Web app url: http://127.0.0.1:8000
Phpmyadmin: http://127.0.0.1:6789/ (user: root, password: qwerty)
mailcatcher: http://127.0.0.1:1080

container access: $ docker-compose exec php bash 
