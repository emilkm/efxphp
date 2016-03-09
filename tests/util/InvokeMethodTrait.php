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
 * @package    efxphp
 * @subpackage tests
 */
trait InvokeMethodTrait
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instance of an object.
     * @param string $methodName Method to invoke.
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return value.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
