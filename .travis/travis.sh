#!/bin/bash

EFXPHP_PATH=$(pwd)

# PHP Settings
PHP_NAME="php-$PHP_VERSION"
PHP_PACKAGE="php-$PHP_VERSION.tar.gz"
PHP_URL="http://us1.php.net/get/$PHP_PACKAGE/from/this/mirror"
PHP_CLI="/home/travis/build/emilkm/efxphp/php-7.1.1/install/bin/php"

# Move out of project
BASE_PATH=$(pwd)


if [ -e "/home/travis/build/emilkm/efxphp/php-7.1.1/configure" ] 
then
	echo "File /home/travis/build/emilkm/efxphp/php-7.1.1/configure exists." 
else
    # Get and extract prebuilt PHP
	wget "$PHP_URL" -O $PHP_PACKAGE
	tar -xf $PHP_PACKAGE

	# Build PHP
	cd $PHP_NAME
	./buildconf --force
	./configure --enable-debug --disable-all --enable-libxml --enable-simplexml --enable-dom --enable-phar --prefix=/home/travis/build/emilkm/efxphp/php-7.1.1/install
	make install
fi

# Get AMFEXT
cd ..
git clone --depth 1 https://github.com/emilkm/amfext.git amfext
mv amfext $PHP_NAME/ext/amf


# Build AMFEXT
cd $PHP_NAME/ext/amf
../../install/bin/phpize
./configure --with-php-config=/home/travis/build/emilkm/efxphp/php-7.1.1/install/bin/php-config
make install

# Run tests
export REPORT_EXIT_STATUS=1
export NO_INTERACTION=1

cd $EFXPHP_PATH
wget https://phar.phpunit.de/phpunit.phar -O phpunit.phar
$PHP_CLI -dextension=amf.so -m
$PHP_CLI -dextension=amf.so phpunit.phar --configuration test/phpunit.xml

