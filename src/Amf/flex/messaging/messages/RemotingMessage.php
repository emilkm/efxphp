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
class RemotingMessage extends AbstractMessage
{
    /**
     * The name of the service to be called including package name
     * @var String
     */
    public $source;

    /**
     * The name of the method to be called
     * @var string
     */
    public $operation;

    /**
     * Incoming: the deserializer will set all properties with values
     *           from the AMF packet.
     * Outgoing: constructor parameters must be provided.
     *
     * @param string $clientId
     * @param string $destination
     * @param string $source
     * @param string $operation
     * @param mixed  $params
     */
    public function __construct(
        $clientId = null,
        $destination = null,
        $source = null,
        $operation = null,
        $params = null
    ) {
        if ($clientId == null) {
            return;
        }

        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $this->$remoteClassField = 'flex.messaging.messages.RemotingMessage';

        $this->clientId     = $clientId;
        $this->destination  = $destination;
        $this->messageId    = $this->generateId();
        $this->timestamp    = $this->timestampMilli();
        $this->timeToLive   = 0;
        $this->headers      = (object) array('DSId' => $this->clientId);
        $this->body         = $params;
        $this->source       = $source;
        $this->operation    = $operation;
    }
}
