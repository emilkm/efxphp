<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace testapp;

use Exception;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 *
 * @access public
 */
class MyService
{
    /**
     * @return bool
     */
    public function publicMethodNoParams()
    {
        return true;
    }

    /**
     * @param int $p1
     *
     * @return bool
     */
    public function publicMethodOptionalParam($p1 = null)
    {
        return true;
    }

    /**
     * @param int $p1
     */
    public function publicMethodMandatoryParam($p1)
    {

    }
}
