FROM ubuntu:14.04
MAINTAINER zERobYTecODe
# Container Build
ENV DEBIAN_FRONTEND noninteractive
RUN localedef -v -c -i ja_JP -f UTF-8 ja_JP.UTF-8 || :
RUN echo "Asia/Tokyo" > /etc/timezone
RUN dpkg-reconfigure -f noninteractive tzdata

RUN apt-get update && apt-get upgrade -y
RUN apt-get -y install php5 php5-cli
RUN apt-get -y install php5-curl
RUN apt-get -y install php5-gd
RUN apt-get -y install php5-json
RUN apt-get -y install php5-mcrypt
RUN apt-get -y install php5-mhash
RUN apt-get -y install php5-mysqlnd
RUN apt-get -y install php5-xsl
RUN apt-get -y install php5-sqlite
RUN apt-get -y install zip
RUN apt-get -y install expect
RUN apt-get -y install cron
RUN apt-get -y install php5-intl
RUN apt-get -y install curl
RUN apt-get -y install git
RUN apt-get -y install postfix
RUN apt-get -y install mailutils
RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
ADD 000-default.conf /
RUN cp /000-default.conf /etc/apache2/sites-available/000-default.conf
RUN mkdir -p /var/www/fuelphp
RUN chown www-data:www-data /var/www/fuelphp
COPY . /var/www/fuelphp
RUN (cd /var/www/fuelphp && php composer.phar selfupdate && php composer.phar update)
RUN mkdir /var/www/fuelphp/fuel/app/tmp
RUN mkdir /var/www/fuelphp/fuel/app/config/production
RUN (cd /var/www/fuelphp && php oil r install)
RUN ln -sf /dev/stdout /var/log/apache2/access.log
RUN ln -sf /dev/stderr /var/log/apache2/error.log
RUN /usr/sbin/a2enmod rewrite

ADD cdn.sh /usr/bin/cdn
RUN chmod 755 /usr/bin/cdn

RUN echo '# FuelPHP Cron' > /etc/cron.d/fuelphp
RUN echo "* * * * * root /usr/bin/cdn batch" >> /etc/cron.d/fuelphp
RUN chmod 755 /etc/cron.d/fuelphp

ADD startup.sh /
RUN chmod 755 /startup.sh

VOLUME ["/var/www/fuelphp/fuel/app/config/production"]

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_LOG_DIR /var/log/apache2
ENV LANG=ja_JP.UTF-8
EXPOSE 80
ENTRYPOINT ["/bin/bash", "/startup.sh"]
