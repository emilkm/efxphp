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
 * @property string $serverOperationMode
 * @property string $servicesRootDirectory
 * @property string $servicesRootNamespace
 * @property string $cacheDirectory
 * @property string $logDirectory
 * @property string $responseClass
 * @property string $accessControlAllowOrigin
 * @property bool $contentEncodingEnabled
 * @property string $sidPropagation
 * @property array $databases
 */
class ServerConfig extends AbstractConfig
{
    const OPMODE_PRODUCTION  = 'production';
    const OPMODE_DEBUG       = 'debug';
    const OPMODE_DEVELOPMENT = 'development';

    /**
     * 'production' = Error messages sent back to client do not include debug info.
     * 'debug' = Error messages sent back to clietn include debug info.
     * 'development' = Error messages like debug, and service class files parsed on each request.
     *
     * @var string
     */
    protected $serverOperationMode = OPMODE_PRODUCTION;

    /**
     * @var string The root directory where services and VOs are located.
     */
    protected $servicesRootDirectory;

    /**
     * Used by the Router to find the service class based on the message source.
     *
     * false = no namespacing | '' = namespace as is | 'root' = root\as is
     *
     * @var string
     */
    protected $servicesRootNamespace = false;

    /**
     * @var string The full path of the directory where all the service metadata
     * files are kept. When value does not point to a writable directory, caching is disabled.
     */
    protected $cacheDirectory;

    /**
     * @var string The full path of the directory where application log database
     * is located. When value does not point to a writable directory, logging is disabled.
     */
    protected $logDirectory;

    /**
     * 'header' = through AMF RemoteMessage header sID | 'query' = through query string sID
     *
     * @var string
     */
    protected $sidPropagation = 'header';

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
     * @var string | array CORS origin(s)
     */
    protected $accessControlAllowOrigin = 'http://localhost';

    /**
     * @var bool
     */
    protected $contentEncodingEnabled = true;

    // ==================================================================
    // Databases
    // ------------------------------------------------------------------

    /**
     * @var array Database configuration settings.
     */
    protected $databases;
}
