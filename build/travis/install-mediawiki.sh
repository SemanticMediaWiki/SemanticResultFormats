#!/bin/bash
set -ex

cd ..

wget https://github.com/wikimedia/mediawiki/archive/$MW.tar.gz
tar -zxf $MW.tar.gz
mv mediawiki-$MW mw

cd mw

## MW 1.25 requires Psr\Logger
if [ -f composer.json ]
then
  composer install
fi

if [ "$DB" == "postgres" ]
then
  psql -c 'create database its_a_mw;' -U postgres
  php maintenance/install.php --dbtype $DB --dbuser postgres --dbname its_a_mw --pass nyan TravisWiki admin --scriptpath /TravisWiki
else
  mysql -e 'create database its_a_mw;'
  php maintenance/install.php --dbtype $DB --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan TravisWiki admin --scriptpath /TravisWiki
fi
