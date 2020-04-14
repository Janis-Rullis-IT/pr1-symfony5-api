#!/bin/bash

echo "== Check and fix the code if possible ==";

# #58  #https://gist.github.com/mathiasverraes/3096500#gistcomment-2570105
find src/. -type f -name '*.php' -print0 | xargs -0 -n1 -P4 php -l -n | (! grep -v "No syntax errors detected" )
find tests/. -type f -name '*.php' -print0 | xargs -0 -n1 -P4 php -l -n | (! grep -v "No syntax errors detected" )

# #58 YAML check
php bin/console lint:yaml config/
