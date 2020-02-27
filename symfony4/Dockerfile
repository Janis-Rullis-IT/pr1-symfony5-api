FROM ubuntu:18.04

ENV DEBIAN_FRONTEND=noninteractive

# create source directory
RUN rm -rf /var/www
RUN mkdir /var/www

# Install debian packages
RUN apt-get clean && apt-get -y update && apt-get install -y locales && locale-gen en_US.UTF-8
RUN apt-get install --yes build-essential inotify-tools cron nano mc htop curl nginx iputils-ping

RUN apt install software-properties-common -y
RUN add-apt-repository ppa:ondrej/php -y
RUN apt update -y

RUN apt install php7.3-fpm -y
RUN apt install php7.3-common php7.3-mysql php7.3-xml php7.3-xmlrpc php7.3-curl php7.3-gd php7.3-imagick php7.3-cli php7.3-dev php7.3-imap php7.3-mbstring php7.3-opcache php7.3-soap php7.3-zip php7.3-intl -y

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# [Fix  `open() "/run/nginx.pid" failed (2: No such file or directory)`](https://github.com/gliderlabs/docker-alpine/issues/185#issuecomment-246595114)
RUN mkdir -p /run/nginx
RUN ln -s /var/www/build/nginx/site.conf /etc/nginx/sites-enabled/site.conf

RUN usermod -a -G www-data www-data
RUN chown www-data:www-data /var/www -R
RUN chmod a+rwX /var/www -R

WORKDIR /var/www

EXPOSE 80
CMD ["./run.sh"]
