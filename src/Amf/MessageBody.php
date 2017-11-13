<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Amf;

use emilkm\efxphp\Amf\Constants;
use flex\messaging\messages\AbstractMessage;
use flex\messaging\messages\AcknowledgeMessage;
use flex\messaging\messages\CommandMessage;
use flex\messaging\messages\ErrorMessage;
use flex\messaging\messages\RemotingMessage;

use Exception;
use stdClass;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class MessageBody
{
    /**
     * @var string
     */
    public $targetURI;

    /**
     * @var string
     */
    public $responseURI;

    /**
     * @var mixed
     */
    public $data;

    /**
     * Incoming: the deserializer will set all properties with values
     * from the AMF packet. Outgoing: parameters must be provided.
     *
     * @param string $targetURI
     * @param string $responseURI
     */
    public function __construct($targetURI = null, $responseURI = null)
    {
        if ($targetURI == null && $responseURI == null) {
            return;
        }

        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $this->$remoteClassField = 'flex.messaging.io.amf.MessageBody';

        $this->targetURI = $targetURI;
        $this->responseURI = $responseURI;
    }

    /**
     * @return AbstractMessage
     */
    public function getDataAsMessage()
    {
        if (is_array($this->data)) {
            $message = $this->data[0];
        } else {
            $message = $this->data;
        }
        if (!($message instanceof CommandMessage)
            && !($message instanceof RemotingMessage)
            && !($message instanceof AcknowledgeMessage)
            && !($message instanceof ErrorMessage)
        ) {
            throw new Exception('Unsupported message type.');
        }
        return $message;
    }
}
