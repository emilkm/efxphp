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
class ActionMessage
{
    /**
     * @var int
     */
    public $version;

    /**
     * @var array
     */
    public $headers;

    /**
     * @var array
     */
    public $bodies;

    /**
     * @param int $version
     */
    public function __construct($version)
    {
        $this->version = $version;
        $this->bodies = array();
    }

    /**
     * @return int The number of headers
     */
    public function getHeaderCount()
    {
        return (is_array($this->headers)) ? count($this->headers) : 0;
    }

    /**
     * @return int The number of bodies
     */
    public function getBodyCount()
    {
        return (is_array($this->bodies)) ? count($this->bodies) : 0;
    }

    /**
     * @param mixed $index
     *
     * @return MessageBody
     */
    public function getBody($index)
    {
        return $this->bodies[$index];
    }

    /*public function sessionId() {
        foreach ($this->headers as $header) {
            if ($header->name == 'sid') {
                return $header->data;
            }
        }
        return null;
    }*/
}
