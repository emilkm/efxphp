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
class Date
{
    /**
     * @var int
     */
    public $timestamp;

    /**
     * @var string
     */
    public $milli;

    /**
     * AMF serialized date as the number of milliseconds elapsed
     * since the epoch of midnight on 1st Jan 1970
     *
     * @param int $amfdate
     */
    public function __construct($amfdate)
    {
        $timestamp = $amfdate / 1000;
        $this->milli = round($timestamp - ($timestamp >> 0), 3) * 1000;
        $this->timestamp = floor($timestamp);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return date('Y-m-d H:i:s.', $this->timestamp) . $this->milli;
    }
}
