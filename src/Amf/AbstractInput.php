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

use Error;
use Exception;
use stdClass;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
abstract class AbstractInput
{
    /**
     * @var bool
     */
    protected $bigEndianMachine = false;

    /**
     * Switching to AMF0 or AMF3 is done automatically during deserialization
     * upon encountering the AMF0_AMF3 marker.
     *
     * @var bool
     */
    protected $avmPlus = false;

    /**
     * @var bool
     */
    protected $useInternalDateType = true;

    /**
     * @var bool
     */
    protected $useInternalXmlType = true;

    /**
     * @var bool
     */
    protected $useInternalXmlDocumentType = true;

    /**
     * Decode typed object or associative array.
     *
     * @var bool
     */
    protected $decodeAmfObjectAsArray = false;

    /**
     * @var string The encoded data
     */
    protected $data;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $pos;

    /**
     *
     */
    public function __construct()
    {
        $this->bigEndianMachine = (pack('l', 1) === "\x00\x00\x00\x01");
    }

    /**
     * References must be reset before reading a top level object,
     * such as a header or a body.
     *
     * @abstract
     */
    abstract public function resetReferences();

    /**
     * Public entry point to read a top level AMF Object, such as
     * a header value or a message body.
     *
     * @abstract
     */
    abstract public function readObject();

    /**
     * @param string $data
     */
    public function setData(&$data)
    {
        $this->data = $data;
        $this->length = strlen($data);
        $this->pos = 0;
    }

    /**
     * Date decoded as efxphp Date if true, PHP DateTime otherwise
     *
     * @param bool $value
     */
    public function setUseInternalDateType($value)
    {
        $this->useInternalDateType = $value;
    }

    /**
     * XML decoded as efxphp Xml if true, PHP SimpleXMLElement otherwise
     *
     * @param bool $value
     */
    public function setUseInternalXmlType($value)
    {
        $this->useInternalXmlType = $value;
    }

    /**
     * XMLDocument decoded as efxphp XmlDocument if true, PHP DOMElement otherwise
     *
     * @param bool $value
     */
    public function setUseInternalXmlDocumentType($value)
    {
        $this->useInternalXmlDocumentType = $value;
    }

    /**
     * Decode typed object or associative array.
     *
     * @param bool $value
     */
    public function setDecodeAmfObjectAsArray($value)
    {
        $this->decodeAmfObjectAsArray = $value;
    }

    /**
     * @param int $n The number of bytes to skip
     */
    public function skipBytes($n)
    {
        if ($this->pos + $n > $this->length) {
            throw new Exception('Cannot skip past the end of the data.');
        }
        $this->pos += $n;
    }

    /**
     * @param int $n The number of bytes to read
     * @return string The next $n bytes as a string
     */
    public function readBytes($n)
    {
        if ($this->pos + $n > $this->length) {
            throw new Exception('Cannot read past the end of the data.');
        }
        $value = '';
        for ($i = 0; $i < $n; $i++) {
            $value .= $this->data[$this->pos + $i];
        }
        $this->pos += $n;
        return $value;
    }

    /**
     * Does not advance the current position
     *
     * @param int $n The number of bytes to add to current position (default 0)
     * @return int The byte at the current position as an integer
     */
    public function peekByte($n = 0)
    {
        if ($this->pos + $n > $this->length) {
            throw new Exception('Cannot read past the end of the data.');
        }
        return ord($this->data[$this->pos + $n]);
    }

    /**
     * @return int The next byte as an integer
     */
    public function readByte()
    {
        if ($this->pos + 1 > $this->length) {
            throw new Exception('Cannot read past the end of the data.');
        }
        return ord($this->data[$this->pos++]);
    }

    /**
     * @return bool The next byte as a boolean
     */
    public function readBoolean()
    {
        return $this->readByte() == 1;
    }

    /**
     * @return int The next 2 bytes as an integer
     */
    public function readShort()
    {
        if ($this->pos + 2 > $this->length) {
            throw new Exception('Cannot read past the end of the data.');
        }
        return ((ord($this->data[$this->pos++]) << 8) |
                ord($this->data[$this->pos++]));
    }

    /**
     * @return int The next 4 bytes as an integer
     */
    public function readInt()
    {
        if ($this->pos + 4 > $this->length) {
            throw new Exception('Cannot read past the end of the data.');
        }
        return ((ord($this->data[$this->pos++]) << 24) |
                (ord($this->data[$this->pos++]) << 16) |
                (ord($this->data[$this->pos++]) << 8) |
                ord($this->data[$this->pos++]));
    }

    /**
     * @return float The next 8 bytes as a float
     */
    public function readDouble()
    {
        $value = $this->readBytes(8);
        if (!$this->bigEndianMachine) {
            $value = strrev($value);
        }
        $zz = unpack('dflt', $value);
        return $zz['flt'];
    }

    /**
     * @return string The UTF8 Unicode string
     */
    public function readUtf()
    {
        $len = $this->readShort();
        return $this->readBytes($len);
    }

    /**
     * Create an instance of a generic anonymous or specific typed object.
     *
     * @param string $className
     * @return stdClass, typed object, or stdClass with remoteClassField set
     */
    protected function resolveType($className)
    {
        if ($this->decodeAmfObjectAsArray && strpos($className, 'flex.messaging.messages.') === false) {
            $arr = [];
            if ($className != '' && $className != 'Object') {
                $arr[Constants::REMOTE_CLASS_FIELD] = $className;
            }
            return $arr;
        }

        try {
            $clazz = 'stdClass';
            if ($className == '' || $className == 'Object') {
                $obj = new $clazz();
            } else {
                /*if ($pos = strpos($className, 'flex.messaging.messages.') === 0) {
                    $class = substr($className, 24);
                    $clasx = 'emilkm\\efxphp\\Amf\\Messages\\' . $class;
                } else {*/
                    $clasx = str_replace('.', '\\', $className);
                //}
                if (class_exists($clasx)) {
                    $clazz = $clasx;
                }
                $obj = new $clazz();
                if ($clazz == 'stdClass') {
                    $remoteClassField = Constants::REMOTE_CLASS_FIELD;
                    $obj->$remoteClassField = $className;
                }
            }
        } catch (Exception | Error $e) {
            $obj = new stdClass();
            $remoteClassField = Constants::REMOTE_CLASS_FIELD;
            $obj->$remoteClassField = $className;
        }
        return $obj;
    }
}
