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

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Serializer
{
    protected $out;

    /**
     * @param AbstractOutput $output
     */
    public function __construct(AbstractOutput $output)
    {
        $this->out = $output;
    }

    /**
     * @param ActionMessage $message
     *
     * @return string The serialized message
     */
    public function writeMessage($message)
    {
        $this->out->data = '';
        $this->out->setAvmPlus($message->version >= 3);
        // Write packet header
        $this->out->writeShort($message->version);
        // Write headers
        $headerCount = $message->getHeaderCount();
        $this->out->writeShort($headerCount);
        for ($i = 0; $i < $headerCount; ++$i) {
            $header = $message->headers[$i];
            $this->writeHeader($header);
        }
        // Write bodies
        $bodyCount = $message->getBodyCount();
        $this->out->writeShort($bodyCount);
        for ($i = 0; $i < $bodyCount; ++$i) {
            $body = $message->bodies[$i];
            $this->writeBody($body);
        }
        return $this->out->data;
    }

    /**
     * Serialize a message header
     *
     * @param MessageHeader $header
     */
    protected function writeHeader($header)
    {
        $this->out->resetReferences();
        $this->out->writeUtf($header->name);
        $this->out->writeBoolean($header->mustUnderstand);
        $buffer = $this->out->data;
        $this->out->data = '';
        $this->out->writeObject($header->data);
        $data = $this->out->data;
        $this->out->data = $buffer;
        $this->out->writeInt(strlen($data));
        $this->out->data .= $data;
    }

    /**
     * Serialize a message body
     *
     * @param MessageBody $body
     */
    protected function writeBody($body)
    {
        $this->out->resetReferences();
        $this->out->writeUtf($body->targetURI);
        $this->out->writeUtf($body->responseURI);
        $buffer = $this->out->data;
        $this->out->data = '';
        $this->out->writeObject($body->data);
        $data = $this->out->data;
        $this->out->data = $buffer;
        $this->out->writeInt(strlen($data));
        $this->out->data .= $data;
    }
}
