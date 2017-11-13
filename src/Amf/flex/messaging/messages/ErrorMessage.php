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

use emilkm\efxphp\Amf\Constants;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class ErrorMessage extends AcknowledgeMessage
{
    public $extendedData = null;
    public $faultCode;
    public $faultDetail;
    public $faultString = '';
    public $rootCause = null;

    /**
     * Outgoing: the request message must be provided.
     * Incoming: the deserializer will set all properties with values
     *           from the AMF packet.
     *
     * @param CommandMessage|RemotingMessage|null $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message);

        if ($message == null) {
            return;
        }

        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $this->$remoteClassField = 'flex.messaging.messages.ErrorMessage';
    }
}
