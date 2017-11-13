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
class ResponseError
{
    /**
     * @var string
     */
    public $_explicitType = 'emilkm.efxphp.ResponseError';

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
     * @param mixed $message
     * @param mixed $code
     * @param mixed $data
     * @param mixed $detail
     */
    public function __construct($message, $code = 0, $detail = null, $data = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->detail = $detail;
        $this->data = $data;
    }
}
