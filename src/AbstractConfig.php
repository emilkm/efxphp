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
    private $_skipPropertyExistsCheck = false;

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
        if (!$this->_skipPropertyExistsCheck && !property_exists($this, $setting)) {
            throw new Exception('Configuration setting does not exist.');
        }

        $this->$setting = $value;
        $this->_skipPropertyExistsCheck = false;
    }

    /**
     * @param string $setting
     * @param mixed $ifnxornull The value to return when setting does not exist or is null.
     *
     * @return string The setting value or null if setting does not exist.
     */
    public function get($setting, $ifnxornull = null)
    {
        if (!property_exists($this, $setting)) {
            return $ifnxornull;
        }

        return ($this->$setting === null) ? $ifnxornull : $this->$setting;
    }

    /**
     * Set the value of a setting if it exists, or force set it either way.
     *
     * @param string $setting
     * @param mixed $value
     * @param $force
     */
    public function set($setting, $value, $force = false)
    {
        if (!$force && !property_exists($this, $setting)) {
            return;
        }

        $this->_skipPropertyExistsCheck = ($force === true);
        $this->$setting = $value;
    }
}

