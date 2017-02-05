#!/bin/bash

EFXPHP_PATH=$(pwd)

# PHP Settings
PHP_NAME="php-$PHP_VERSION"
PHP_PACKAGE="php-$PHP_VERSION.tar.gz"
PHP_URL="http://us1.php.net/get/$PHP_PACKAGE/from/this/mirror"

# Move out of project 
cd ../
BASE_PATH=$(pwd)

# Get and extract PHP
wget $PHP_URL -O $PHP_PACKAGE
tar -xf $PHP_PACKAGE 

# Get AMFEXT
git clone --depth 1 https://github.com/emilkm/amfext.git amfext
mv amfext $PHP_NAME/ext/amf


# Build PHP
cd $PHP_NAME
./buildconf --force
./configure --enable-debug --disable-all --enable-libxml --enable-simplexml --enable-dom --with-amf
make

# Run tests
export REPORT_EXIT_STATUS=1
export TEST_PHP_EXECUTABLE=sapi/cli/php
export NO_INTERACTION=1
sapi/cli/php run-tests.php ext/amf  

cd $EFXPHP_PATH
$BASE_PATH/$PHP_NAME/sapi/cli/php phpunit --configuration test/phpunit.xml