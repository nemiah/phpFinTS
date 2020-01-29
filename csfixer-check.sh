#!/bin/bash
#
# When this is run as part of a Travis test for a pull request, then it ensures that none of the touched files has any
# PHP CS Fixer warnings.
# From: https://github.com/FriendsOfPHP/PHP-CS-Fixer#using-php-cs-fixer-on-ci

if [ -z "$TRAVIS_COMMIT_RANGE" ]
then
# TRAVIS_COMMIT_RANGE "is empty for builds triggered by the initial commit of a new branch"
# From: https://docs.travis-ci.com/user/environment-variables/
  echo "Variable TRAVIS_COMMIT_RANGE not set, falling back to full git diff"
  TRAVIS_COMMIT_RANGE=.
fi

IFS='
'
CHANGED_FILES=$(git diff --name-only --diff-filter=ACMRTUXB "$TRAVIS_COMMIT_RANGE")
if [ "$?" -ne "0" ]
then
  echo "Error: git diff response code > 0, aborting"
  exit 1
fi

if [ -z "${CHANGED_FILES}" ]
then
  echo "0 changed files found, exiting"
  exit 0
fi

if ! echo "${CHANGED_FILES}" | grep -qE "^(\\.php_cs(\\.dist)?|composer\\.lock)$"; then EXTRA_ARGS=$(printf -- '--path-mode=intersection\n--\n%s' "${CHANGED_FILES}"); else EXTRA_ARGS=''; fi
vendor/bin/php-cs-fixer fix --config=.php_cs -v --dry-run --stop-on-violation --using-cache=no ${EXTRA_ARGS} || (echo "php-cs-fixer failed" && exit 1)