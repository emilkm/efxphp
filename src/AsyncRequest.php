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
class Request
{
    public $source;
    public $operation;
    public $params;
    public $onResult;
    public $onStatus;
    public $token;
    public $holdQueue;

    /**
     * @param string   $source
     * @param stirng   $operation
     * @param mixed    $params
     * @param callable $onResult
     * @param callable $onStatus
     * @param mixed    $token
     * @param bool     $holdQueue
     */
    public function __construct($source, $operation, $params, $onResult, $onStatus, $token = null, $holdQueue = false)
    {
        $this->source = $source;
        $this->operation = $operation;
        $this->params = $params;
        $this->onResult = $onResult;
        $this->onStatus = $onStatus;
        $this->token = $token;
        $this->holdQueue = $holdQueue;
    }
}
