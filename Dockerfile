FROM debian:jessie
MAINTAINER adam.stipak@gmail.com

ENV DEBIAN_FRONTEND noninteractive

# upgrade the system
RUN apt-get update
#RUN apt-get upgrade -y

# base tools
RUN apt-get install mc curl vim wget htop openssh-client -y

# mysql
RUN apt-get install mysql-client mysql-server -y

# php
RUN apt-get install php5 php5-cli php5-curl php5-intl php5-mysql php5-ldap php5-mcrypt php5-gd php5-xdebug -y

# apache2
RUN apt-get install apache2 libapache2-mod-php5
RUN a2enmod rewrite

# composer
RUN cd && curl -sS https://getcomposer.org/installer | php && ln -s /root/composer.phar /usr/local/bin/composer

# setup vhost
RUN mkdir -p /data

COPY .docker/addons.nette.org.conf /etc/apache2/sites-available/addons.nette.org.conf

RUN a2dissite 000-default.conf && \
  a2ensite addons.nette.org.conf

# setup mysql user
RUN service mysql start && \
  mysql -u root -e "FLUSH PRIVILEGES; SET PASSWORD FOR 'root'@'localhost' = PASSWORD('root');"
