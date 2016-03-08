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
 * AVM+ Serialization optimizes object serialization by
 * serializing the traits of a type once, and then
 * sending only the values of each instance of the type
 * as it occurs in the stream.
 *
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class TraitsInfo
{
    /**
     * @var string
     */
    public $className;

    /**
     * @var mixed
     */
    public $dynamic;

    /**
     * @var mixed
     */
    public $externalizable;

    /**
     * @var mixed
     */
    public $properties;

    /**
     * @param string $className
     * @param mixed  $dynamic
     * @param mixed  $externalizable
     * @param mixed  $properties
     */
    public function __construct($className, $dynamic, $externalizable, $properties = null)
    {
        $this->className = $className;
        $this->dynamic = $dynamic;
        $this->externalizable = $externalizable;
        $this->properties = ($properties == null) ? array() : $properties;
    }

    /**
     * @param mixed $value
     */
    public function addProperty($value)
    {
        $this->properties[] = $value;
    }

    /**
     * @return int The count of properties
     */
    public function length()
    {
        return count($this->properties);
    }
}
