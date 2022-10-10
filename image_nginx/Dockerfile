# DOWNLOAD AND USE LATEST NGINX STABLE VERSION
FROM nginx:stable

# GET LATEST COMPONENT LIST BASED ON CONFIGURED COMPONENTS
RUN apt-get update
RUN apt-get install -y curl
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev

# INSTALL NANO EDITOR
RUN apt-get install -y nano

# INSTALL IPUTILS-PING
RUN apt-get install -y iputils-ping

# INSTALL NET-TOOLS (utilities include: arp, hostname, ifconfig, netstat, rarp, route, plipconfig, slattach, mii-tool, iptunnel and ipmaddr)
RUN apt-get install -y net-tools

# INSTALL COMPOSER AND MOVED INTO DIRECTORY PATH (GLOBAL INSTALL)
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# DEFINED WORKING DIRECTORY
WORKDIR /var/www/html

# REPLACE DEFAULT NGINX CONFIGURATION BY PREPARED ONE
COPY vhost/default.conf /etc/nginx/conf.d/default.conf
