#!/bin/bash

EFXPHP_PATH=$(pwd)

# PHP Settings
PHP_NAME="php-$PHP_VERSION"
PHP_PACKAGE="php-$PHP_VERSION-travis.tar.gz"
PHP_URL="http://www.emilmalinov.com/travis/$PHP_PACKAGE"
PHP_CLI="/home/travis/build/emilkm/php-7.1.1/install/bin/php"

# Move out of project 
cd ../
BASE_PATH=$(pwd)

# Get and extract prebuilt PHP
wget "$PHP_URL" -O $PHP_PACKAGE
tar -xf $PHP_PACKAGE 

# Get AMFEXT
git clone --depth 1 https://github.com/emilkm/amfext.git amfext
mv amfext $PHP_NAME/ext/amf


# Build AMFEXT
cd $PHP_NAME/ext/amf
../../install/bin/phpize
./configure --with-php-config=/home/travis/build/emilkm/php-7.1.1/install/bin/php-config
make install

# Get PHPUNIT
wget -O https://phar.phpunit.de/phpunit.phar

# Run tests
export REPORT_EXIT_STATUS=1
export NO_INTERACTION=1

cd $EFXPHP_PATH
$PHP_CLI -dextension=amf.so -m
$PHP_CLI -dextension=amf.so phpunit --configuration test/phpunit.xml

