<?php
//define the application root
define('BASE', str_replace(array('\\', '\\\\'), '/', realpath('../')) . '/');

if (!class_exists('PHPUnit_Framework_TestCase') && file_exists('phpunit.phar')) {
    include 'phpunit.phar';
}

require_once BASE . 'src/ClassLoader.php';
use Composer\Autoload\ClassLoader;
$autoLoader = new ClassLoader();
$autoLoader->setUseIncludePath(true);
$autoLoader->addPsr4('emilkm\\efxphp\\', BASE . 'src/');
$autoLoader->addPsr4('emilkm\\tests\\', BASE . 'tests/');
$autoLoader->register(true);

use emilkm\tests\util\PhpServer;
$phpServer = new PhpServer('127.0.0.1', 9999, BASE . 'tests/webapp/public');
$pid = $phpServer->start();
