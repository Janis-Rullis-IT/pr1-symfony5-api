#!/bin/bash

echo "== Check and fix the code if possible ==";

# #58  #https://gist.github.com/mathiasverraes/3096500#gistcomment-2570105
find src/. -type f -name '*.php' -print0 | xargs -0 -n1 -P4 php -l -n | (! grep -v "No syntax errors detected" )
find tests/. -type f -name '*.php' -print0 | xargs -0 -n1 -P4 php -l -n | (! grep -v "No syntax errors detected" )

# #58 YAML check
php bin/console lint:yaml config/

# #58 https://github.com/phpmd/phpmd - advisor.
vendor/bin/phpmd src,tests html cleancode, codesize, controversial, design, naming, unusedcode  --reportfile var/log/lint-phpmd.html

# #58 https://cs.symfony.com/ - formatter.
vendor/bin/php-cs-fixer fix src --rules=@Symfony,align_multiline_comment,array_indentation,ordered_class_elements
vendor/bin/php-cs-fixer fix tests --rules=@Symfony,align_multiline_comment,array_indentation,ordered_class_elements