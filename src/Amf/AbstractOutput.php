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
abstract class AbstractOutput
{
    /**
     * @var bool
     */
    protected $bigEndianMachine = false;

    /**
     * AVM+ Encoding.
     *
     * @var bool
     */
    protected $avmPlus = false;

    /**
     * Encode non-strict non-dense array as anonymous object or associative array.
     *
     * @var bool
     */
    protected $amf3nsndArrayAsObject = false;

    /**
     * @var string
     */
    public $data;

    /**
     * Sets the bigEndianMachine property of the Output[Ext] instance
     */
    public function __construct()
    {
        $this->bigEndianMachine = (pack('l', 1) === "\x00\x00\x00\x01");
    }

    /**
     * References must be reset before writing a top level object,
     * such as a header or a body.
     *
     * @abstract
     */
    abstract public function resetReferences();

    /**
     * Public entry point to write a top level AMF Object, such as
     * a header value or a message body.
     *
     * If we're using AMF 3, and a complex object is encountered,
     * encoding is switched to AVM+ format.
     *
     * @abstract
     * @param mixed $value The object to write
     */
    abstract public function writeObject($value);

    /**
     * @return string The encoded data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set to true if the AMF0 stream should switch to use AMF3 on encountering
     * the first complex Object during serialization.
     *
     * @param bool $value
     */
    public function setAvmPlus($value)
    {
        $this->avmPlus = $value;
    }

    /**
     * Encode string, mixed, sparse, or negative index arrays as anonymous object,
     * associative array otherwise.
     *
     * @param bool $value
     */
    public function encodeAmf3nsndArrayAsObject($value)
    {
        $this->amf3nsndArrayAsObject = $value;
    }

    /**
     * writeByte writes a single byte to the output stream
     * 0-255 range
     *
     * @param int $value An int that can be converted to a byte
     */
    public function writeByte($value)
    {
        $this->data .= pack('c', $value);
    }

    /**
     * writeBoolean writes the boolean code (0x01) and the data to the output stream
     *
     * @param bool $value The boolean value
     */
    public function writeBoolean($value)
    {
        $this->writeByte($value);
    }

    /**
     * writeShort takes an int and writes it as 2 bytes to the output stream
     * 0-65535 range
     *
     * @param int $value An integer to convert to a 2 byte binary string
     */
    public function writeShort($value)
    {
        $this->data .= pack('n', $value);
    }

    /**
     * writeInt takes an int, float, or double and converts it to a 4 byte binary string and
     * adds it to the output buffer
     *
     * @param long $value A long to convert to a 4 byte binary string
     */
    public function writeInt($value)
    {
        $this->data .= pack('N', $value);
    }

    /**
     * writeDouble takes a float as the input and writes it to the output stream.
     * Then if the system is little-endian, it reverses the bytes order because all
     * doubles passed via remoting are passed big-endian.
     *
     * @param double $value The double to add to the output buffer
     */
    public function writeDouble($value)
    {
        $b = pack('d', $value);
        $r = (!$this->bigEndianMachine) ? strrev($b) : $b;
        $this->data .= $r;
    }

    /**
     * writeUtf takes and input string, writes the length as an int and then
     * appends the string to the output buffer
     *
     * @param string $value The string less than 65535 characters to add to the stream
     */
    public function writeUtf($value)
    {
        $this->writeShort(strlen($value)); // write the string length - max 65535
        $this->data .= $value;
    }

    /**
     * writeLongUtf will write a string longer than 65535 characters.
     * It works exactly as writeUTF does except uses a long for the length
     * flag.
     *
     * @param string $value A string to add to the byte stream
     */
    protected function writeLongUtf($value)
    {
        $this->writeInt(strlen($value));
        $this->data .= $value;
    }
}
