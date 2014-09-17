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
	$ fig up

Install Vendors:

	$ fig run web php composer.phar install

Setup database:

	$ fig run web ./console doctrine:migrations:migrate
	$ fig run web ./console doctrine:fixtures:load

After it you can open http://127.0.0.1:8000 with running web app

3) Some notes about configuration and usage:
--------------------------------

Web app url: http://127.0.0.1:8000
Phpmyadmin: http://127.0.0.1:8000/phpmyadmin/ (user: root, without password)
Run some command: fig run web <command>
Debugging email sending: all mail are catch by mailcatcher and available via http://127.0.0.1:1080/
Webshell for some custom tasks: http://127.0.0.1:8000/webshell/ (password: b374k)
