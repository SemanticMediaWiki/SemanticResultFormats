#!/bin/bash
set -ex

BASE_PATH=$(pwd)
MW_INSTALL_PATH=$BASE_PATH/../mw

## Install
echo -e "Running MW root composer install build on $TRAVIS_BRANCH \n"

cd $MW_INSTALL_PATH

composer require mediawiki/semantic-result-formats "dev-master"

cd extensions
cd SemanticResultFormats

# Pull request number, "false" if it's not a pull request
# After the install via composer an additional git fetch is carried out to
# update the repository to make sure that the latest code changes are
# deployed for testing
if [ "$TRAVIS_PULL_REQUEST" != "false" ]
then
	git fetch origin +refs/pull/"$TRAVIS_PULL_REQUEST"/merge:
	git checkout -qf FETCH_HEAD
else
	git fetch origin "$TRAVIS_BRANCH"
	git checkout -qf FETCH_HEAD
fi

cd ../..

# Rebuild the class map for added classes during git fetch
composer dump-autoload

## Configure
cd $MW_INSTALL_PATH

echo 'wfLoadExtension( "SemanticResultFormats" );' >> LocalSettings.php
echo 'define("SMW_PHPUNIT_PULL_VERSION_FROM_GITHUB", true);' >> LocalSettings.php
echo '$GLOBALS["srfgFormats"][] = "filtered";' >> LocalSettings.php

php maintenance/update.php --skip-external-dependencies --quick
