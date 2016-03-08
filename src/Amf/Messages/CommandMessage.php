<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Amf\Messages;

use emilkm\efxphp\Amf\Constants;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class CommandMessage extends AsyncMessage
{
    const SUBSCRIBE_OPERATION = 0;
    const UNSUSBSCRIBE_OPERATION = 1;
    const POLL_OPERATION = 2;
    const CLIENT_SYNC_OPERATION = 4;
    const CLIENT_PING_OPERATION = 5;
    const CLUSTER_REQUEST_OPERATION = 7;
    const LOGIN_OPERATION = 8;
    const LOGOUT_OPERATION = 9;
    const SESSION_INVALIDATE_OPERATION = 10;
    const MULTI_SUBSCRIBE_OPERATION = 11;
    const DISCONNECT_OPERATION = 12;
    const UNKNOWN_OPERATION = 10000;

    public $operation = self::UNKNOWN_OPERATION;

    /**
     * Incoming: the deserializer will set all properties with values
     *           from the AMF packet.
     * Outgoing: constructor parameters must be provided.
     *
     * @param mixed $operation
     */
    public function __construct($operation = null)
    {
        if ($operation == null) {
            return;
        }

        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $this->$remoteClassField = 'flex.messaging.messages.CommandMessage';

        if ($operation == self::CLIENT_PING_OPERATION) {
            $this->operation    = self::CLIENT_PING_OPERATION;
            $this->destination  = null;
            $this->messageId    = $this->generateId();
            $this->timestamp    = $this->timestampMilli();
            $this->timeToLive   = 0;
            $this->clientId     = null;
            $this->headers      = (object) array('DSId' => 'nil');
            $this->body         = (object) array();
        }
    }
}
