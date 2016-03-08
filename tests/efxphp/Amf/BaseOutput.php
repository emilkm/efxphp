<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\tests\efxphp\Amf;

use emilkm\efxphp\Amf\AbstractOutput;

/**
 * Helper class that enables testing of the concrete methods of AbstractOutput.
 *
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class BaseOutput extends AbstractOutput
{
    /**
     * Dummy implementation
     */
    public function resetReferences()
    {
    }

    /**
     * Dummy implementation
     *
     * @param mixed $value
     */
    public function writeObject($value)
    {
    }
}
