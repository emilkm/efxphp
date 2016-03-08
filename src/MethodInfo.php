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

class MethodInfo
{
    /**
     * @var string
     */
    public $className;
    /**
     * @var string
     */
    public $methodName;
    /**
     * @var array parameters to be passed to the api method
     */
    public $parameters = array();
    /**
     * @var array information on parameters in the form of array(name => index)
     */
    public $arguments = array();
    /**
     * @var array default values for parameters if any
     * in the form of array(index => value)
     */
    public $defaults = array();
    /**
     * @var array key => value pair of method meta information
     */
    public $metadata = array();
    /**
     * @var int access level
     * 0 - @public - available for all
     * 1 - @hybrid - both public and protected (enhanced info for authorized)
     * 2 - @protected comment - only for authenticated users
     * 3 - protected method - only for authenticated users
     */
    public $accessLevel = 0;
}