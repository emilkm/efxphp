<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace flex\messaging\messages;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class AbstractMessage
{
    /**
     * @var string
     */
    public $messageId;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var double
     */
    public $timestamp;

    /**
     * @var double
     */
    public $timeToLive;

    /**
     * @var string
     */
    public $destination;

    /**
     * @var string
     */
    public $headers;

    /**
     * @var string
     */
    public $body;

    /**
     * Generate a unique Id
     *
     * Format is: ########-####-####-####-############
     * Where # is an uppercase letter or number
     * example: 6D9DC7EC-A273-83A9-ABE3-00005FD752D6
     *
     * @return string
     */
    public function generateId()
    {
        return sprintf(
            '%08X-%04X-%04X-%02X%02X-%012X',
            mt_rand(),
            mt_rand(0, 65535),
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)),
            bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
            mt_rand(0, 255),
            mt_rand()
        );
    }

    /**
     * Validate an Id has the correct format
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validId($value)
    {
        return (preg_match("/^([0-9A-F]{8})-([0-9A-F]{4})-([0-9A-F]{4})-([0-9A-F]{4})-([0-9A-F]{12})$/i", $value)) ? true : false;
    }

    /**
     * The number of milliseconds elapsed
     * since the epoch of midnight on 1st Jan 1970
     *
     * @return double
     */
    public function timestampMilli()
    {
        return round(microtime(true) * 1000);
    }
}
