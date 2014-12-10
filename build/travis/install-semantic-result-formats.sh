#!/bin/bash
set -ex

BASE_PATH=$(pwd)
MW_INSTALL_PATH=$BASE_PATH/../mw

# Run Composer installation from the MW root directory
function installToMediaWikiRoot {
	echo -e "Running MW root composer install build on $TRAVIS_BRANCH \n"

	cd $MW_INSTALL_PATH

	composer init --stability dev

	composer require 'phpunit/phpunit=3.7.*' --prefer-source --update-with-dependencies
	composer require mediawiki/semantic-result-formats "dev-master" --prefer-source --dev

	cd extensions
	cd SemanticResultFormats

	# Pull request number, "false" if it's not a pull request
	# After the install via composer an additional get fetch is carried out to
	# update th repository to make sure that the latests code changes are
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
}

function updateConfiguration {

	cd $MW_INSTALL_PATH

	echo 'require_once( __DIR__ . "/extensions/SemanticResultFormats/SemanticResultFormats.php" );' >> LocalSettings.php

	echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
	echo 'ini_set("display_errors", 1);' >> LocalSettings.php
	echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
	echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
	echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

	php maintenance/update.php --quick
}

installToMediaWikiRoot
updateConfiguration
