efxphp
======

PHP server library for Action Message Format (AMF) applications, with support for AMF0 and AMF3.

The library provides a complete AMF implementation in PHP. Encoding/decoding can be sped up with a custom PHP extension https://github.com/emilkm/amfext

Extension or not, encoded output and decoded input are the same, and are OK by Adobe/Apache BlazeDS.

# PHP7

Works on PHP 7.1

Can be made to work on PHP 7.0 easily if needed. (Only using the convenience of handling multiple exceptions in single catch block, in a couple of places.)

[![Build Status](https://travis-ci.org/emilkm/efxphp.svg?branch=master)](https://travis-ci.org/emilkm/efxphp)

# PHP5

No longer works on PHP 5.6 out of the box. (Can be made to if really necessary.)

Unfortunately the PHP5 branch of amfext has a few outstanding memory leaks. Time to move to PHP 7+ :)

# DONE

* Works well with https://github.com/emilkm/amfext
* Extension or not, encoded output and decoded input are the same, and are OK by Adobe/Apache BlazeDS.
* Setup Travis CI.

# TODO

* Run the AMF client tests on travis.
* Write docs.
* Add more tests.
* ...


