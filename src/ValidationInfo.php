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

use emilkm\efxphp\Util\NestedValueTrait;

/**
 * @category   Framework
 * @package    Restler
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 * @version    3.0.0rc5
 */
class ValidationInfo
{
    use NestedValueTrait;

    private $embeddedDataName;

    /**
     * @var mixed given value for the parameter
     */
    public $value;
    /**
     * @var string proper name for given parameter
     */
    public $label;
    /**
     * @var string html element that can be used to represent the parameter for
     *             input
     */
    public $field;
    /**
     * @var mixed default value for the parameter
     */
    public $default;
    /**
     * Name of the variable being validated
     *
     * @var string variable name
     */
    public $name;

    /**
     * @var bool is it required or not
     */
    public $required;

    /**
     * @var string body or header or query where this parameter is coming from
     * in the http request
     */
    public $from;

    /**
     * Data type of the variable being validated.
     * It will be mostly string
     *
     * @var string|array multiple types are specified it will be of
     *      type array otherwise it will be a string
     */
    public $type;

    /**
     * When the type is array, this field is used to define the type of the
     * contents of the array
     *
     * @var string|null when all the items in an array are of certain type, we
     * can set this property. It will be null if the items can be of any type
     */
    public $contentType;

    /**
     * Should we attempt to fix the value?
     * When set to false validation class should throw
     * an exception or return false for the validate call.
     * When set to true it will attempt to fix the value if possible
     * or throw an exception or return false when it cant be fixed.
     *
     * @var boolean true or false
     */
    public $fix = false;

    /**
     * @var array of children to be validated
     */
    public $children = null;

    // ==================================================================
    //
    // VALUE RANGE
    //
    // ------------------------------------------------------------------
    /**
     * Given value should match one of the values in the array
     *
     * @var array of choices to match to
     */
    public $choice;
    /**
     * If the type is string it will set the lower limit for length
     * else will specify the lower limit for the value
     *
     * @var number minimum value
     */
    public $min;
    /**
     * If the type is string it will set the upper limit limit for length
     * else will specify the upper limit for the value
     *
     * @var number maximum value
     */
    public $max;

    // ==================================================================
    //
    // REGEX VALIDATION
    //
    // ------------------------------------------------------------------
    /**
     * RegEx pattern to match the value
     *
     * @var string regular expression
     */
    public $pattern;

    // ==================================================================
    //
    // CUSTOM VALIDATION
    //
    // ------------------------------------------------------------------
    /**
     * Rules specified for the parameter in the php doc comment.
     * It is passed to the validation method as the second parameter
     *
     * @var array custom rule set
     */
    public $rules;

    /**
     * Specifying a custom error message will override the standard error
     * message return by the validator class
     *
     * @var string custom error response
     */
    public $message;

    public function numericValue($value)
    {
        return (int)$value == $value
            ? (int)$value
            : floatval($value);
    }

    public function arrayValue($value)
    {
        return is_array($value)
            ? $value
            : array($value);
    }

    public function stringValue($value, $glue = ',')
    {
        return is_array($value)
            ? implode($glue, $value)
            : (string)$value;
    }

    public function booleanValue($value)
    {
        return is_bool($value)
            ? $value
            : $value !== 'false';
    }

    public function filterArray(array $data, $keepNumericKeys)
    {
        $r = array();
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                if ($keepNumericKeys) {
                    $r[$key] = $value;
                }
            } elseif (!$keepNumericKeys) {
                $r[$key] = $value;
            }
        }
        return $r;
    }

    private function getProperty(array &$from, $property)
    {
        $p = $this->nestedValue($from, $property);
        unset($from[$property]);
        $p2 = $this->nestedValue(
            $from, $this->embeddedDataName, $property
        );
        unset($from[$this->embeddedDataName][$property]);

        if ($property == 'type' && $p == 'array' && $p2) {
            $this->contentType = $p2;
            return $p;
        }
        $r = $p2 ? : $p ? : null;
        if (!is_null($r)) {
            if ($property == 'min' || $property == 'max') {
                return $this->numericValue($r);
            } elseif ($property == 'required' || $property == 'fix') {
                return $this->booleanValue($r);
            } elseif ($property == 'choice') {
                return $this->arrayValue($r);
            } elseif ($property == 'pattern') {
                return $this->stringValue($r);
            }
        }
        return $r;
    }

    public function __construct(array $info, $embeddedDataName)
    {
        $this->embeddedDataName = $embeddedDataName;

        $properties = get_object_vars($this);
        unset($properties['contentType']);
        foreach ($properties as $property => $value) {
            $this->{$property} = $this->getProperty($info, $property);
        }
        $inner = $this->nestedValue($info, 'properties');
        $this->rules = !empty($inner) ? $inner + $info : $info;
        unset($this->rules['properties']);
        if (is_string($this->type) && $this->type == 'integer') {
            $this->type = 'int';
        }
    }
}

