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
class MessageHeader
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $mustUnderstand;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param string $name
     * @param bool $mustUnderstand
     * @param mixed $data
     */
    public function __construct($name = '', $mustUnderstand = false, $data = null) {
        $this->name = $name;
        $this->mustUnderstand = (bool) $mustUnderstand;
        $this->data = $data;
    }
}

