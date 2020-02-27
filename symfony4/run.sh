#!/bin/sh
# #5 Dockerize the pr1-symfony4.

set -e
service php7.3-fpm start
composer install
echo y | php bin/console doctrine:migrations:migrate

service nginx start && tail -F /var/log/nginx/error.log
