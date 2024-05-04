-include .env
export

# setup for docker-compose-ci build directory
# delete "build" directory to update docker-compose-ci

ifeq (,$(wildcard ./build/))
    $(shell git submodule update --init --remote)
endif

EXTENSION=SemanticResultFormats

# docker images
MW_VERSION?=1.35
PHP_VERSION?=7.4
DB_TYPE?=sqlite
DB_IMAGE?=""

# extensions
SMW_VERSION?=4.1.3
PF_VERSION ?= 5.5.1
SFS_VERSION ?= 4.0.0-beta
MM_VERSION ?= 3.1.0

# composer
# Enables "composer update" inside of extension
COMPOSER_EXT?=true

# nodejs
# Enables node.js related tests and "npm install"
# NODE_JS?=true

# check for build dir and git submodule init if it does not exist
include build/Makefile

