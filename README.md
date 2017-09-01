It's engine for site of conference Frameworks Days
========================================

[![Build Status](https://secure.travis-ci.org/stfalcon/fwdays.png?branch=master)](https://travis-ci.org/stfalcon/fwdays)

1) Download
--------------------------------

### Clone the git Repository from the main repository or fork it to your github account:

Note that you **must** have git installed and be able to execute the `git`
command.

	$ git clone git://github.com/stfalcon/fwdays.git .

2) Installation
---------------

### a) Check your System Configuration

Before you begin, make sure that your local system is properly configured
for Symfony2. To do this, execute the following:

	$ ./app/check.php

If you get any warnings or recommendations, fix these now before moving on.

**Requirements**

* PHP needs to be a minimum version of PHP 5.3.3
* Sqlite3 needs to be enabled
* JSON needs to be enabled
* ctype needs to be enabled
* Your PHP.ini needs to have the date.timezone setting
* Intl needs to be installed with ICU 4+
* APC 3.0.17+ (or another opcode cache needs to be installed)


### b) Change the permissions of the "app/cache/" and "app/logs" directories so that the web server can write into it.

	$ chmod 0777 app/cache/ app/logs

### c) Install Composer

	$ curl -s https://getcomposer.org/installer | php

### d) Install the Vendor Libraries

    $ ./composer.phar install

### e) Change DBAL settings, create DB, update it and load fixtures

Change DBAL setting in `app/config/config.yml`, `app/config/config_dev.yml` or
`app/config/config_test.yml`. After that execute the following:

    $ ./console doctrine:database:create
    $ ./console doctrine:migrations:migrate
    $ ./console doctrine:fixtures:load

You can set environment `test` for command if you add `--env=test` to it.

### f) Install Assets (if they hadn't been installed in **e** step or if you want to update them )

    $ ./console assets:install web --symlink

### g) Import translations from translate files to bd

    $ ./console lexik:translations:import

Setup dev-env via docker and fig
========================================

1) Install docker and fig
--------------------------------

        $ sudo wget https://get.docker.io/builds/Linux/x86_64/docker-latest -O /usr/local/bin/docker
        $ sudo chmod a+x /usr/local/bin/docker

Then add "/usr/local/bin/docker -d -G your_username" to /etc/rc.local and start docker daemon

        $ sudo wget https://github.com/docker/fig/releases/download/0.5.2/linux -O /usr/local/bin/fig
        $ sudo chmod a+x /usr/local/bin/fig

2) Running containers
--------------------------------

Clone git repository:

        $ git clone git://github.com/stfalcon/fwdays.git .
        $ cd fwdays && docker-compose build

After it run:

        $ docker-compose up

It's installs vendors and setup database.
After it you can open http://127.0.0.1:8888 with running web app

3) Some notes about configuration and usage:
--------------------------------

Web app url: http://127.0.0.1:8888

Phpmyadmin: http://127.0.0.1:8888/phpmyadmin/ (user: root, password: qwerty)
container access: $ docker exec -ti fwdays_fwdays_1 /bin/bash 

