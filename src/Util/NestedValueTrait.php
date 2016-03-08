<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Util;

trait NestedValueTrait
{
    /**
     * Get the value deeply nested inside an array / object
     *
     * Using isset() to test the presence of nested value can give a false positive
     *
     * This method serves that need
     *
     * When the deeply nested property is found its value is returned, otherwise
     * false is returned.
     *
     * @param array        $from    array to extract the value from
     * @param string|array $key     ... pass more to go deeply inside the array
     *                              alternatively you can pass a single value
     *
     * @return null|mixed null when not found, value otherwise
     */
    public function nestedValue($from, $key) /**, $key2 ... $key`n` */
    {
        if (is_array($key)) {
            $keys = $key;
        } else {
            $keys = func_get_args();
            array_shift($keys);
        }
        foreach ($keys as $key) {
            if (is_array($from) && isset($from[$key])) {
                $from = $from[$key];
                continue;
            } elseif (is_object($from) && isset($from->{$key})) {
                $from = $from->{$key};
                continue;
            }
            return null;
        }
        return $from;
    }
}
