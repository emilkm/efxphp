<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */
 
namespace testapp;

use emilkm\efxphp\ServerConfig;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 *
 * List properties for intellisense in some IDEs
 *
 * @property array $databases
 */
class AppConfig extends ServerConfig
{
    /**
     * Array of database connection settings
     *
     * @var array
     */
    protected $databases;
    
    /**
     * Intialize inherited properties if necessary.
     */
    public function __construct()
    {
        $this->productionMode = false;
    }
}
