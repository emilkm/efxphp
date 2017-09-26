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
interface LoggerInterface
{
    /**
     * @param $level
     * @param $message
     * @param $code
     * @param $context
     */
    public function write($level, $message, $code = 0, $context = 'unknown');
}
