#!/bin/bash
#
# When this is run as part of a Travis test for a pull request, then it ensures that none of the touched files has any
# PHP CS Fixer warnings.
# From: https://github.com/FriendsOfPHP/PHP-CS-Fixer#using-php-cs-fixer-on-ci

IFS='
'
CHANGED_FILES=$(git diff --name-only --diff-filter=ACMRTUXB "${COMMIT_RANGE}")
if ! echo "${CHANGED_FILES}" | grep -qE "^(\\.php_cs(\\.dist)?|composer\\.lock)$"; then EXTRA_ARGS=$(printf -- '--path-mode=intersection\n--\n%s' "${CHANGED_FILES}"); else EXTRA_ARGS=''; fi
vendor/bin/php-cs-fixer fix --config=.php_cs -v --dry-run --stop-on-violation --using-cache=no ${EXTRA_ARGS}