<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\tests\util;

/**
 * @author     Emil Malinov
 * @package    efxphp-amf
 * @subpackage tests
 */
trait AccessPropertyTrait
{
    /**
     * Read protected/private property of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $propertyName Property name to read.
     *
     * @return mixed Property value.
     */
    public function getPropertyValue(&$object, $propertyName) {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
