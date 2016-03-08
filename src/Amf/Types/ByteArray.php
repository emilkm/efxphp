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

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class ByteArray
{
    /**
     * @var string ByteString data
     */
    public $data;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toSring()
    {
        return $this->data;
    }
}
