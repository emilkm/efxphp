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

use Exception;

/**
 * @author  Emil Malinov
 * @package efxphp
 */
abstract class AbstractConfig
{
    /**
     * @param string $setting
     *
     * @return string The setting value
     */
    public function __get($setting)
    {
        if (!property_exists($this, $setting)) {
            throw new Exception('Configuration setting does not exist.');
        }
        return $this->$setting;
    }

    /**
     * @param string $setting
     * @param mixed $value
     */
    public function __set($setting, $value)
    {
        if (!property_exists($this, $setting)) {
            throw new Exception('Configuration setting does not exist.');
        }
        $this->$setting = $value;
    }
}

