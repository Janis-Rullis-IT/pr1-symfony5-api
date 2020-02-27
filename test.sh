#!/bin/bash
# #7 Execute PHPUnit tests inside the container.
docker exec -it pr1-symfony4 bash  -c " ./vendor/bin/phpunit tests/"
