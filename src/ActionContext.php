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
 * A simple context used by the server while handling a request.
 */
class ActionContext
{
    /**
     * True if requestMessage contains a CommandMessage
     *
     * @var bool
     */
    public $requestIsCommand = false;

    /**
     * Current message number diring processing
     *
     * @var int
     */
    public $messageNumber;

    /**
     * @var ActionMessage
     */
    public $requestMessage;

    /**
     * @var ActionMessage
     */
    public $responseMessage;

    /**
     * Class metadata.
     *
     * @var array
     */
    public $classMetadata = array();

    /**
     * Routing, authorization, validation, and other errors.
     *
     * @var array
     */
    public $errors = array();
}
