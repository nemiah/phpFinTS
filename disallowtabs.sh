#!/bin/bash
#
# When this is run as part of a Travis test for a pull request, then it ensures that none of the added lines (compared
# to the base branch of the pull request) use tabs for indentations.
# Adapted from https://github.com/mrc/git-hook-library/blob/master/pre-commit.no-tabs

# Abort if any of the inner commands (particularly the git commands) fails.
set -e
set -o pipefail

if [ -z ${TRAVIS_PULL_REQUEST} ]; then
    echo "Expected environment variable TRAVIS_PULL_REQUEST"
    exit 2
elif [ "${TRAVIS_PULL_REQUEST}" == "false" ]; then
    echo "Not a Travis pull request, skipping."
    exit 0
fi

# Make sure that we have a local copy of the relevant commits (otherwise git diff won't work).
git remote set-branches --add origin ${TRAVIS_BRNACH}
git fetch

# Compute the diff from the PR's target branch to its HEAD commit.
target_branch="origin/${TRAVIS_BRANCH}"
the_diff=$(git diff "${target_branch}...HEAD")

# Make sure that there are no tabs in the indentation part of added lines.
if echo "${the_diff}" | egrep '^\+\s*	' >/dev/null; then
    echo -e "\e[31mError: The changes contain a tab for indentation\e[0m, which is against this repo's policy."
    echo "Target branch: origin/${TRAVIS_BRANCH}"
    echo "Commit range: ${TRAVIS_COMMIT_RANGE}"
    echo "The following tabs were detected:"
    echo "${the_diff}" | egrep '^(\+\s*	|\+\+\+|@@)'
    exit 1
else
    echo "No new tabs detected."
fi
