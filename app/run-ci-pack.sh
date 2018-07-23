#!/usr/bin/env bash

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

OK="OK"
FAIL="FAIL"
ENV="dev"

for i in "$@"
do
case $i in
    -c)
        # Colored results
        OK="${GREEN}OK${NC}"
        FAIL="${RED}FAIL${NC}"
        ;;
    -e=*)
        ENV="${i#*=}"
        ;;
esac
done

ERROR=0

echo -e "Results of CI tasks:"

if composer validate --no-check-all --strict > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - Validity of composer.json & composer.lock: ${RESULT}"

if php app/console security:check > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - No issues in project dependencies: ${RESULT}"

if php app/console doctrine:schema:validate --env=${ENV} > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - Doctrine schema validity: ${RESULT}"

if php app/console lint:twig src/Application/Bundle/DefaultBundle/Resources/views/ --env=${ENV} > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - Twig templates validity: ${RESULT}"

if php app/console lint:yaml app/config/ --env=${ENV} > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - YAML config files validity: ${RESULT}"

if php bin/phpcs src/ > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - Correct code style for .php files: ${RESULT}"

if php bin/php-cs-fixer fix -q --dry-run --rules=@Symfony src/ > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - Additional php-cs-fixer checks for source .php files: ${RESULT}"

touch /tmp/fwdays_test_coverage.txt

if php bin/phpunit -c app/phpunit.xml.dist --coverage-text=/tmp/fwdays_test_coverage.txt > /dev/null; then
    RESULT=${OK}
else
    RESULT=${FAIL}
    ERROR=1
fi
echo -e " - Fwdays Test Suite: ${RESULT}"

i=1
cat /tmp/fwdays_test_coverage.txt | while read LINE
do
    i=$(($i+1))

    if [ "$i" -eq 12 ]
    then
        break
    fi

    if [ "$i" -gt 7 ] && [ "$i" -lt 10 ]
    then
        echo -e "  ├ ${LINE}"
    fi

    if [ "$i" -gt 9 ] && [ "$i" -lt 11 ]
    then
        echo -e "  └ ${LINE}"
    fi
done

exit ${ERROR}
