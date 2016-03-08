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

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Deserializer
{
    protected $in;

    /**
     * @param AbstractInput $input
     */
    public function __construct(AbstractInput $input)
    {
        $this->in = $input;
    }

    /**
     * @param mixed $data
     *
     * @return ActionMessage
     */
    public function readMessage(&$data)
    {
        $this->in->setData($data);
        $version = $this->in->readShort();
        $message = new ActionMessage($version);
        // Read headers
        $headerCount = $this->in->readShort();
        for ($i = 0; $i < $headerCount; ++$i) {
            $message->headers[] = $this->readHeader();
        }
        // Read bodies
        $bodyCount = $this->in->readShort();
        for ($i = 0; $i < $bodyCount; ++$i) {
            $message->bodies[] = $this->readBody();
        }
        return $message;
    }

    /**
     * Deserialize a message header
     *
     * @return MessageHeader
     */
    protected function readHeader()
    {
        $this->in->resetReferences();
        $header = new MessageHeader();
        $header->name = $this->in->readUtf();
        $header->mustUnderstand = $this->in->readBoolean();
        $this->in->skipBytes(4); // skip header length
        $header->data = $this->in->readObject();
        return $header;
    }

    /**
     * Deserialize a message body
     *
     * @return MessageBody
     */
    protected function readBody()
    {
        $this->in->resetReferences();
        $message = new MessageBody();
        $message->targetURI = $this->in->readUtf();
        $message->responseURI = $this->in->readUtf();
        $this->in->skipBytes(4); // skip message length
        $message->data = $this->in->readObject();
        return $message;
    }
}
