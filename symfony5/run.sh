#!/bin/sh
# #5 Dockerize the pr1-symfony5.

set -e
service php7.3-fpm start
composer install
# #38 #23 Import the database structure into testing database too.
echo y | php bin/console doctrine:migrations:migrate --env=test
echo y | php bin/console doctrine:migrations:migrate

service nginx start && tail -F /var/log/nginx/error.log
