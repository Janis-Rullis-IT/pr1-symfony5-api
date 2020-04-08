#!/bin/sh

# #52 TODO: Pass paramst that truncates the db or executes large fixtures.

# #43 Fill test tables before executing tests.
# php bin/console doctrine:fixtures:load --append -e test

./vendor/bin/phpunit tests/