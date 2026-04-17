-include .env
export

# setup for docker-compose-ci build directory
# delete "build" directory to update docker-compose-ci

ifeq (,$(wildcard ./build/))
    $(shell git submodule update --init --remote)
endif

EXTENSION=SemanticResultFormats

# docker images
MW_VERSION?=1.43
PHP_VERSION?=8.3
DB_TYPE?=mysql
DB_IMAGE?="mariadb:11.2"

# extensions
# SMW 6.0.1 has a bug where #ask returns empty results in the prepareContentForEdit context
# used by JSONScript parser-type tests. Fixed in dev-master; pin to 7.x once released.
SMW_VERSION ?= dev-master
PF_VERSION ?= 6.0.5
SFS_VERSION ?= 4.0.0-beta
MM_VERSION ?= 6.0.2

# composer
# Enables "composer update" inside of extension
COMPOSER_EXT?=true

# OS packages and PHP extensions required for optional formats:
# - libzip-dev + zip: prerequisite for phpoffice/phpspreadsheet (format=spreadsheet)
# - gd: required by phpoffice/phpspreadsheet at install time
OS_PACKAGES?=libzip-dev libpng-dev
PHP_EXTENSIONS?=zip gd

# nodejs
# Enables node.js related tests and "npm install"
# NODE_JS?=true

# check for build dir and git submodule init if it does not exist
include build/Makefile

# Chain install-spreadsheet into make install so phpspreadsheet is always available
# after a full install. The prerequisite order ensures it runs after .install completes.
install: install-spreadsheet

# Install phpoffice/phpspreadsheet for format=spreadsheet tests.
# phpspreadsheet is listed as "suggest" in SRF's composer.json (not "require"), so it
# cannot be pulled in via the composer-merge-plugin; an explicit install step is needed.
# composer-require.sh only updates composer.local.json; the follow-up "composer update"
# actually downloads and installs the package into the running container.
.PHONY: install-spreadsheet
install-spreadsheet: .init
	$(compose-exec-wiki) bash -c "composer-require.sh phpoffice/phpspreadsheet 1.22.0 && composer update phpoffice/phpspreadsheet --with-all-dependencies"

.PHONY: composer-phan
composer-phan: .init
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phan $(COMPOSER_PARAMS)"

