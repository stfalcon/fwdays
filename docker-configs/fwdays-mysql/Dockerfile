FROM debian:stretch
MAINTAINER Evgeniy Gurinovich "jeka@stfalcon.com"
RUN apt-get update && apt-get install -y wget curl ca-certificates procps locales zip apt-transport-https gnupg2
RUN wget https://repo.percona.com/apt/percona-release_0.1-4.stretch_all.deb -O /tmp/percona.deb
RUN dpkg -i /tmp/percona.deb && rm /tmp/percona.deb && apt-get update
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y percona-server-server-5.6 mysqltuner
ADD configs/my.cnf /etc/mysql/my.cnf
ADD configs/start /usr/local/bin/start
RUN chmod a+x /usr/local/bin/start
CMD /usr/local/bin/start
