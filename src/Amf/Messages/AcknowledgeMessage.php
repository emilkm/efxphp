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
class AcknowledgeMessage extends AsyncMessage
{
    /**
     * Outgoing: the request message must be provided.
     * Incoming: the deserializer will set all properties with values
     *           from the AMF packet.
     *
     * @param CommandMessage|RemotingMessage|null $message
     */
    public function __construct($message = null)
    {
        if ($message == null) {
            return;
        }

        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $this->$remoteClassField = 'flex.messaging.messages.AcknowledgeMessage';

        $this->destination  = null;
        $this->messageId    = $this->generateId();
        $this->timestamp    = $this->timestampMilli();
        $this->timeToLive   = 0;
        $this->body         = null;

        //set the client id
        if ($message->clientId != null) {
            $this->clientId = $message->clientId;
        } elseif (isset($message->headers->DSId)) {
            $this->clientId = $message->headers->DSId;
        }

        if ($this->clientId == null || $this->clientId == 'nil') {
            $this->clientId = $this->generateId();
        }

        //correlate the message
        if (isset($message->messageId)) {
            $this->correlationId = $message->messageId;
        }

        $this->headers      = (object) array('DSId' => $this->clientId);
    }
}
