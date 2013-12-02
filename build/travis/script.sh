#! /bin/bash

set -x

cd ../phase3/extensions/SemanticResultFormats

if [ "$MW-$DBTYPE" == "master-mysql" ]
then
	phpunit --coverage-clover ../../extensions/SemanticResultFormats/build/logs/clover.xml
else
	phpunit
fi
