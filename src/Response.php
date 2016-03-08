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

/**
 * @author  Emil Malinov
 * @package efxphp
 */
class Response
{
    /**
     * @var string
     */
    public $_explicitType = 'emilkm.efxphp.Response';

    /**
     * @var int
     */
    public $code = 0;

    /**
     * @var string
     */
    public $message;

    /**
     * @var mixed
     */
    public $detail;

    /**
     * @var mixed
     */
    public $data;

    /**
     * Incoming: the deserializer will set all properties with values
     *           from the AMF packet.
     *
     * @param mixed $data
     * @param mixed $detail
     */
    public function __construct($data = null, $detail = null)
    {
        $this->data = $data;
        $this->detail = $detail;
    }
}
