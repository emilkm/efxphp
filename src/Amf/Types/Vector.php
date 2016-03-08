<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Amf\Types;

use emilkm\efxphp\Amf\Constants;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Vector
{
    /**
     * @var int The type of vector.
     */
    public $type = Constants::AMF3_VECTOR_OBJECT;

    /**
     * @var bool fixed or variable length
     */
    public $fixed = false;

    /**
     * @var array of primitives or objects
     */
    public $data;

    /**
     * @param int   $type
     * @param array $data
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toSring()
    {
        $typestr = 'object';
        switch ($this->type) {
            case Constants::AMF3_VECTOR_INT:
                $typestr = 'int';
                break;
            case Constants::AMF3_VECTOR_UINT:
                $typestr = 'uint';
                break;
            case Constants::AMF3_VECTOR_DOUBLE:
                $typestr = 'double';
                break;
            case Constants::AMF3_VECTOR_OBJECT:
                $typestr = 'object';
                break;
        }
        return 'A ' . ($this->fixed ? 'fixed' : 'variable') . ' Vector ' . $typestr;
    }
}
