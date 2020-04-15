#!/bin/bash

echo "== Execute tests with fixtures ==
./test.sh - Append and execute regular size fixtures.
./test.sh 1 - Truncate and execute regular size fixtures.
./test.sh 1 1 - Truncate and execute huge size fixtures.
./test.sh 0 1 - Append and execute huge size fixtures.
";

# #52 #44 Fixture params.
MUST_TRUNCATE=false;
HUGE_IMPORT=false;
FIXTURE_ARGS="";

if [[ $1 == 1 ]]; then
	MUST_TRUNCATE=true;
fi

if [[ $2 == 1 ]]; then
	HUGE_IMPORT=true;
fi

if [[ $MUST_TRUNCATE == true ]]; then
	FIXTURE_ARGS="--purge-with-truncate";
else
	FIXTURE_ARGS="--append";
fi

if [[ $HUGE_IMPORT == true ]]; then
	FIXTURE_ARGS="${FIXTURE_ARGS} --group=huge";
else
	FIXTURE_ARGS="${FIXTURE_ARGS} --group=regular";
fi

# #43 Fill test tables before executing tests.
php bin/console -n doctrine:fixtures:load "${FIXTURE_ARGS}" -e test

./vendor/bin/phpunit tests/