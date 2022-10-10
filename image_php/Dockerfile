# DOWNLOAD AND USE PHP 8 IMAGE
FROM php:8.1.4-fpm

# GET LATEST COMPONENT LIST BASED ON CONFIGURED COMPONENTS
RUN apt-get update

# INSTALL NANO EDITOR
RUN apt-get install -y nano

# INSTALL IPUTILS-PING
RUN apt-get install -y iputils-ping

# INSTALL NET-TOOLS (utilities include: arp, hostname, ifconfig, netstat, rarp, route, plipconfig, slattach, mii-tool, iptunnel and ipmaddr)
RUN apt-get install -y net-tools

# INSTALL COMPOSER AND MOVED INTO DIRECTORY PATH (GLOBAL INSTALL)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# SET USER
ARG UNAME=www-data
ARG UGROUP=www-data
ARG UID=1000
ARG GID=1001
RUN usermod  --uid $UID $UNAME
RUN groupmod --gid $GID $UGROUP

# MAKE SURE APT IS UP TO DATE
RUN apt-get update
RUN apt-get install -y curl
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev

ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 9.2.0

# INSTALL NVM WITH NODE AND NPM
RUN curl https://raw.githubusercontent.com/creationix/nvm/v0.33.8/install.sh | bash \
    && source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH      $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

# DEFINED WORKING DIRECTORY
WORKDIR /var/www/html

# INSTALL GIT
RUN apt-get install -y git

# INSTALL SUPERVISOR
RUN apt-get install -y supervisor

# INSTALL CRON JOB
RUN apt-get install -y cron

# INSTALL EXTENSION
# Install "curl", "libmemcached-dev", "libpq-dev", "libjpeg-dev",
#         "libpng-dev", "libfreetype6-dev", "libssl-dev", "libmcrypt-dev",
RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
            curl \
            libmemcached-dev \
            libz-dev \
            libpq-dev \
            libjpeg-dev \
            libpng-dev \
            libfreetype6-dev \
            libssl-dev \
            libwebp-dev \
            libxpm-dev \
            libmcrypt-dev \
            libonig-dev; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    # Install the PHP pdo_mysql extention
    docker-php-ext-install pdo_mysql; \
    # Install the PHP pdo_pgsql extention
    docker-php-ext-install pdo_pgsql; \
    # Install the PHP gd library
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-xpm \
            --with-freetype; \
    docker-php-ext-install gd; \
    php -r 'var_dump(gd_info());'

RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install zip