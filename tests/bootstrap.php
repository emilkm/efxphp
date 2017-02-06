<?php
//define the application root
define('BASE', str_replace(array('\\', '\\\\'), '/', realpath('../')) . '/');

require_once BASE . 'src/ClassLoader.php';
use Composer\Autoload\ClassLoader;
$autoLoader = new ClassLoader();
$autoLoader->setUseIncludePath(true);
$autoLoader->addPsr4('emilkm\\efxphp\\', BASE . 'src/');
$autoLoader->addPsr4('emilkm\\tests\\', BASE . 'tests/');

if (file_exists(BASE . 'vendor/autoload.php')) {
    $map = require BASE . 'vendor/composer/autoload_namespaces.php';
    foreach ($map as $namespace => $path) {
        $autoLoader->set($namespace, $path);
    }

    $map = require  BASE . 'vendor/composer/autoload_psr4.php';
    foreach ($map as $namespace => $path) {
        $autoLoader->setPsr4($namespace, $path);
    }

    $classMap = require  BASE . 'vendor/composer/autoload_classmap.php';
    if ($classMap) {
        $autoLoader->addClassMap($classMap);
    }
}

$autoLoader->register(true);



//use emilkm\tests\util\PhpServer;
//$phpServer = new PhpServer('127.0.0.1', 9999, BASE . 'tests/webapp/public');
//$pid = $phpServer->start();
