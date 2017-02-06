#!/bin/bash


# PHP Settings
PHP_NAME="php-$PHP_VERSION"
PHP_PACKAGE="php-$PHP_VERSION.tar.gz"
PHP_URL="http://us1.php.net/get/$PHP_PACKAGE/from/this/mirror"


# Move out of project
cd ..
BASE_PATH=$(pwd)
PHP_CLI="$BASE_PATH/$PHP_NAME/install/bin/php"


if [ -e "$BASE_PATH/$PHP_NAME/configure" ] 
then
	echo "File $BASE_PATH/$PHP_NAME/configure exists." 
else
    # Get and extract prebuilt PHP
	wget "$PHP_URL" -O $PHP_PACKAGE
	tar -xf $PHP_PACKAGE

	# Build PHP
	cd $PHP_NAME
	./buildconf --force
	./configure --enable-debug --disable-all --enable-libxml --enable-simplexml --enable-dom --enable-phar --prefix=$BASE_PATH/$PHP_NAME/install
	make install
fi

# Get AMFEXT
cd $BASE_PATH/$PHP_NAME/ext
rm -fr ./amf
git clone --depth 1 https://github.com/emilkm/amfext.git amf


# Build AMFEXT
cd $BASE_PATH/$PHP_NAME/ext/amf
../../install/bin/phpize
./configure --with-php-config=$BASE_PATH/$PHP_NAME/install/bin/php-config
make install
rm -fr $BASE_PATH/$PHP_NAME/ext/amf

# Run tests
export REPORT_EXIT_STATUS=1
export NO_INTERACTION=1

cd $TRAVIS_BUILD_DIR
composer update dev
cd $TRAVIS_BUILD_DIR/tests
$PHP_CLI -dextension=amf.so -m
$PHP_CLI -dextension=amf.so $TRAVIS_BUILD_DIR/vendor/bin/phpunit --configuration phpunit.xml efxphp/Amf

