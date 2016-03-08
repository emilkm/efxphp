<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */

//define the application root
define('BASE', str_replace(array('\\', '\\\\'), '/', realpath('../../../../')) . '/');

require_once BASE . 'src/ClassLoader.php';
use Composer\Autoload\ClassLoader;
$autoLoader = new ClassLoader();
$autoLoader->addPsr4('emilkm\\efxphp\\', BASE . 'src');
$autoLoader->addPsr4('testapp\\', BASE . 'tests/webapp/services/testapp');
$autoLoader->register(true);

use testapp\AppConfig;
$appConfig = new AppConfig();
$appConfig->servicesRootDirectory = BASE . 'tests/webapp/services';
$appConfig->servicesRootNamespace = '';
$appConfig->cacheDirectory = BASE . 'tests/webapp/services/cache';
$appConfig->contentEncodingEnabled = true;
$appConfig->responseClass = null;

use emilkm\efxphp\Dice;

$dice = new Dice();
$dice->addInstance('emilkm\\efxphp\\Dice', $dice);
$dice->addInstance('testapp\\AppConfig', $appConfig);

//Identification, Authorization, ...
$rule1 = [];
$rule1['shared'] = true;
$rule1['substitutions']['emilkm\\efxphp\\IdentificationInterface'] = ['instance' => 'testapp\\AccessManager'];
$rule1['substitutions']['emilkm\\efxphp\\AuthorizationInterface'] = ['instance' => 'testapp\\AccessManager'];
$rule1['substitutions']['emilkm\\efxphp\\ServerConfig'] = ['instance' => 'testapp\\AppConfig'];
$rule1['shareInstances'] = ['emilkm\\efxphp\\Dice'];
$dice->addRule('*', $rule1);

//AMF Input & Output
$rule2 = [];
$rule2['substitutions']['emilkm\\efxphp\\Amf\\AbstractInput'] = ['instance' => (function () {
    if (function_exists('amf_decode')) {
        return new emilkm\efxphp\Amf\InputExt();
    } else {
        return new emilkm\efxphp\Amf\Input();
    }
})];
$dice->addRule('emilkm\\efxphp\\Amf\\Deserializer', $rule2);

$rule3 = [];
$rule3['substitutions']['emilkm\\efxphp\\Amf\\AbstractOutput'] = ['instance' => (function () {
    if (function_exists('amf_encode')) {
        return new emilkm\efxphp\Amf\OutputExt();
    } else {
        return new emilkm\efxphp\Amf\Output();
    }
})];
$dice->addRule('emilkm\\efxphp\\Amf\\Serializer', $rule3);

//MetadataCache
$rule4 = ['constructParams' => [$appConfig->cacheDirectory]];
$dice->addRule('emilkm\\efxphp\\MetadataCache', $rule4);

//Router rule
$rule5 = ['constructParams' => [$appConfig->servicesRootNamespace]];
$dice->addRule('emilkm\\efxphp\\Router', $rule5);

//##############################################################################

//Create the server and handle the request
use emilkm\efxphp\Server;
$server = $dice->create('emilkm\\efxphp\\Server');
$server->handle();
