<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Amf;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class MethodInfo
{
    /**
     * @var string class name may include namespace
     */
    public $className;

    /**
    * @var string
    */
    public $methodName;

    /**
     * @var string the absolute path to the file containing the class definition
     */
    public $absolutePath;

    /**
     * @param string $className
     * @param string $methodName
     * @param string $absolutePath
     */
    public function __construct($className, $methodName, $absolutePath) {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->absolutePath = $absolutePath;
    }
}

