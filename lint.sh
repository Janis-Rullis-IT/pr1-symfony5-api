#!/bin/bash

# #53 Execute PHPUnit tests inside the container.
docker exec -it pr1-symfony5 bash  -c " ./lint.sh"

# #53 http://www.skybert.net/bash/bash-linter/ shellcheck advisor.
 find . -maxdepth 2 -type f -name '*.sh' -print0 | xargs -0 -n1 -P4 shellcheck | (! grep -v "No syntax errors detected" )