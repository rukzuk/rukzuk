# based on ubuntu 18.04 (bionic)
FROM phusion/baseimage:0.11

VOLUME /srv/rukzuk/htdocs/cms

# proposed breaks some php packages (e.g. php-intl)
RUN rm -rf /etc/apt/sources.list.d/proposed.list

# phusion/baseimage is not always up to date. :-(
RUN apt-get update  && \
    apt-get dist-upgrade -y --no-install-recommends

# Set Timezone
RUN echo "Europe/Berlin" > /etc/timezone

# Install Depencies
RUN apt-get install -y --no-install-recommends \
        tzdata \
        sudo

RUN dpkg-reconfigure --frontend noninteractive tzdata

# Make Debian/Ubuntu and Docker friends
ENV DEBIAN_FRONTEND noninteractive

# install phantomjs
RUN apt-get install -y --no-install-recommends phantomjs

# PhantomJS Offscreen wrapper
COPY phantomjs-offscreen /usr/local/bin/phantomjs
RUN chmod +x /usr/local/bin/phantomjs

# add php source list
RUN apt-get install -y --no-install-recommends software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update

# Install Apache httpd
RUN apt-get install -y --no-install-recommends \
        sqlite3 \
        apache2 \
        libapache2-mod-php7.3 \
        msmtp \
        msmtp-mta \
        php7.3 \
        php7.3-sqlite \
        php7.3-cli \
        php7.3-curl \
        php7.3-gd \
        php7.3-intl \
        php7.3-xml \
        php7.3-zip \
        php7.3-mbstring

# dev/build stuff (pecl for mcrypt and v8js)
RUN apt-get install -y --no-install-recommends \
        build-essential \
        libmcrypt-dev \
        libmcrypt4 \
        php7.3-dev \
        php-pear

# install mcrypt (now pecl) DISABLED because it is deprecated
#RUN sudo CFLAGS=-w CPPFLAGS=-w pecl install mcrypt
#RUN echo "extension = mcrypt.so" | sudo tee -a /etc/php/7.3/mods-available/mcrypt.ini
#RUN phpenmod mcrypt

# lib v8
RUN add-apt-repository ppa:stesie/libv8 && apt-get update
RUN apt-get install -y libv8-7.5-dev

RUN echo "/opt/libv8-7.5/lib" | sudo tee -a /etc/ld.so.conf.d/libv8.conf
RUN ldconfig

RUN printf "\/opt/libv8-7.5\n" | sudo CFLAGS=-w CPPFLAGS=-w pecl install v8js-2.1.1
RUN echo "extension = v8js.so" | sudo tee -a /etc/php/7.3/mods-available/v8js.ini 

# Activate PHP mods
RUN phpenmod v8js

# Activate Apache mods
RUN a2enmod ssl && \
    a2enmod rewrite

# Activate apache2 in runit
RUN mkdir -p /etc/service/apache2
COPY apache2.runit /etc/service/apache2/run
RUN chmod +x /etc/service/apache2/run

# Create folder
ENV CMS_PATH /opt/rukzuk/htdocs
ENV INSTANCE_PATH /srv/rukzuk
RUN mkdir -p ${CMS_PATH}
RUN mkdir -p ${INSTANCE_PATH}/htdocs/cms
RUN chown -R www-data:www-data ${INSTANCE_PATH}/htdocs

# Install the release/cmsrelase.tar.gz (a version from our Jenkins)
RUN curl -L $(curl -s https://api.github.com/repos/rukzuk/rukzuk/releases/latest | grep browser_download_url | grep 'tgz\|tar.gz' | cut -d '"' -f 4) | tar -xz --strip 1 -C ${CMS_PATH}/..


RUN ln -s ${CMS_PATH}/app/server/environment ${INSTANCE_PATH}/environment
RUN ln -s ${CMS_PATH} ${INSTANCE_PATH}/application

# Initial
ENV APPLICATION_ENV standalone
ENV CMS_SQLITE_DB ${INSTANCE_PATH}/htdocs/cms/db.sqlite3
COPY config.php ${INSTANCE_PATH}/config.php
COPY cms.apache /etc/apache2/sites-available/000-default.conf
RUN mkdir -p /etc/my_init.d
COPY init.sh /etc/my_init.d/rukzuk_init.sh
RUN chmod +x /etc/my_init.d/rukzuk_init.sh
COPY msmtprc.tpl /etc/msmtprc.tpl

# Remove unused packages (only used to build)  & Cleanup 
RUN apt-get purge php7.3-dev libmcrypt-dev php-pear build-essential
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/*

EXPOSE 80

