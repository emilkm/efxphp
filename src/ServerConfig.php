<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp;

/**
 * @author  Emil Malinov
 * @package efxphp
 *
 * List properties for intellisense in some IDEs
 *
 * @property bool $productionMode
 * @property string $servicesRootDirectory
 * @property string $servicesRootNamespace
 * @property string $cacheDirectory
 * @property string $responseClass
 * @property array $supportedCharsets
 * @property bool $crossOriginResourceSharing
 * @property string $accessControlAllowOrigin
 * @property bool $contentEncodingEnabled
 * @property string $preferContentEncoding
 */
class ServerConfig extends AbstractConfig
{
    /**
     * When set to false, it will run in debug mode
     * and parse the class files every time
     *
     * @var boolean
     */
    protected $productionMode = false;

    /**
     * root directory where services and VOs are located
     *
     * @var string
     */
    protected $servicesRootDirectory;

    /**
     * false = no namespacing | '' = namespace as is | 'root' = root\as is
     *
     * @var string
     */
    protected $servicesRootNamespace = false;

    /**
     * @var string full path of the directory where all the generated files will
     * be kept. When set to null (default) it will use the cache folder that is
     * in the same folder as index.php (gateway)
     */
    protected $cacheDirectory;

    // ==================================================================
    //
    // Class Mappings
    //
    // ------------------------------------------------------------------

    /**
     * @var string name of the class that wraps the response
     */
    protected $responseClass = 'emilkm\\efxphp\\Response';

    // ==================================================================
    // Response
    // ------------------------------------------------------------------

    /**
     * @var bool enables CORS support
     */
    protected $crossOriginResourceSharing = false;
    
    /**
     * @var string
     */
    protected $accessControlAllowOrigin = '*';

    /**
     * @var bool
     */
    protected $contentEncodingEnabled = true;
}
