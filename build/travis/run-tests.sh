#! /bin/bash
set -ex

BASE_PATH=$(pwd)
MW_INSTALL_PATH=$BASE_PATH/../mw

function uploadCoverageReport {
	wget https://scrutinizer-ci.com/ocular.phar
	php ocular.phar code-coverage:upload --format=php-clover coverage.clover
}

cd $MW_INSTALL_PATH/extensions/SemanticResultFormats

if [ "$TYPE" == "coverage" ]
then
	composer phpunit -- --coverage-clover coverage.clover
	uploadCoverageReport
else
	composer phpunit
fi