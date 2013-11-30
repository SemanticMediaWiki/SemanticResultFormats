#! /bin/bash

set -x

cd ../phase3/extensions/SemanticResultFormats

if [ "$MW-$DBTYPE" == "master-mysql" ]
then
	phpunit --coverage-clover ../../extensions/SemanticMediaWiki/build/logs/clover.xml
else
	phpunit
fi