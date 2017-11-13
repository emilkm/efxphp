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

use emilkm\efxphp\Amf\Types\ByteArray;
use emilkm\efxphp\Amf\Types\Date;
use emilkm\efxphp\Amf\Types\Vector;
use emilkm\efxphp\Amf\Types\Xml;
use emilkm\efxphp\Amf\Types\XmlDocument;

use Exception;
use DateTime;
use DateTimeZone;
use SimpleXMLElement;
use DOMElement;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Input extends AbstractInput
{
    /**
     * @var array
     */
    private $objects0;

    /**
     * @var array
     */
    private $strings;

    /**
     * @var array
     */
    private $objects;

    /**
     * @var array
     */
    private $traits;

    public function __construct()
    {
        parent::__construct();
        $this->resetReferences();
    }

    /**
     * References must be reset before reading a top level object,
     * such as a header or a body.
     */
    public function resetReferences()
    {
        $this->objects0 = array();
        $this->strings = array();
        $this->objects = array();
        $this->traits = array();
        $this->avmPlus = false;
    }

    /**
     * Public entry point to read a top level AMF Object, such as
     * a header value or a message body.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function readObject()
    {
        $type = $this->readByte();
        if ($this->avmPlus) {
            $data = $this->readAmf3ObjectValue($type);
        } else {
            $data = $this->readAmf0ObjectValue($type);
        }
        return $data;
    }

    //##########################################################################
    // AMF0
    //##########################################################################

    /**
     * @param mixed $type The value AMF0 type
     *
     * @return mixed The PHP version of the data in the Packet block
     */
    protected function readAmf0ObjectValue($type)
    {
        $value = null;
        switch ($type) {
            case Constants::AMF0_AMF3:
                $this->avmPlus = true;
                $value = $this->readObject();
                break;
            case Constants::AMF0_UNDEFINED:
            case Constants::AMF0_NULL:
                $value = null;
                break;
            case Constants::AMF0_NUMBER:
                $value = $this->readDouble();
                break;
            case Constants::AMF0_BOOLEAN:
                $value = $this->readBoolean();
                break;
            case Constants::AMF0_STRING:
                $value = $this->readAmf0String(false);
                break;
            case Constants::AMF0_TYPEDOBJECT:
                $className = $this->readUtf();
                $value = $this->readAmf0ScriptObject($className);
                break;
            case Constants::AMF0_OBJECT:
                $value = $this->readAmf0ScriptObject();
                break;
            case Constants::AMF0_ARRAY:
                $value = $this->readAmf0Array();
                break;
            case Constants::AMF0_REFERENCE:
                $ref = $this->readShort();
                $value = $this->objects0[$ref];
                break;
            case Constants::AMF0_MIXEDARRAY:
                $value = $this->readAmf0MixedArray();
                break;
            case Constants::AMF0_LONGSTRING:
                $value = $this->readAmf0String(true);
                break;
            case Constants::AMF0_XMLDOCUMENT:
                $value = $this->readAmf0Xml();
                break;
            case Constants::AMF0_DATE:
                $value = $this->readAmf0Date();
                break;
            case Constants::AMF0_OBJECTEND:
                throw new Exception('Unexpected object end tag in AMF stream');
            default:
                throw new Exception('Unsupported Amf0 type encountered: ' . $type);
        }
        return $value;
    }

    /**
     * @param bool $long
     *
     * @return string
     */
    protected function readAmf0String($long)
    {
        if ($long) {
            $len = $this->readInt();

            return $this->readBytes($len);
        }
        return $this->readUtf();
    }

    /**
     * @return mixed stdClass or typed object
     */
    protected function readAmf0ScriptObject($className = '')
    {
        $obj = $this->resolveType($className);
        $this->objects0[] = &$obj;
        if (is_object($obj)) {
            $key = $this->readUtf();
            $type = $this->readByte();
            while ($type != Constants::AMF0_OBJECTEND) {
                $value = $this->readAmf0ObjectValue($type);
                $obj->$key = $value;
                $key = $this->readUtf();
                $type = $this->readByte();
            }
        } else {
            $key = $this->readUtf();
            $type = $this->readByte();
            while ($type != Constants::AMF0_OBJECTEND) {
                $value = $this->readAmf0ObjectValue($type);
                $obj[$key] = $value;
                $key = $this->readUtf();
                $type = $this->readByte();
            }
        }
        return $obj;
    }

    /**
     * @return array The php array
     */
    protected function readAmf0Array()
    {
        $arr = array();
        $this->objects0[] = &$arr;
        $len = $this->readInt();
        for ($i = 0; $i < $len; $i++) {
            $type = $this->peekByte();
            if ($type == Constants::AMF0_UNDEFINED) {
                $this->skipBytes(1);
                continue;
            } else {
                $value = $this->readObject();
            }
            $arr[$i] = $value;
        }
        return $arr;
    }

    /**
     * @return array
     */
    protected function readAmf0MixedArray()
    {
        $arr = array();
        $this->objects0[] = &$arr;
        $this->skipBytes(4); //$len = $this->in->readInt();
        $key = $this->readUtf();
        $type = $this->readByte();
        while ($type != Constants::AMF0_OBJECTEND) {
            if (is_numeric($key)) {
                $key = (float) $key;
            }
            $value = $this->readAmf0ObjectValue($type);
            $arr[$key] = $value;
            $key = $this->readUtf();
            $type = $this->readByte();
        }
        return $arr;
    }

    /**
     * @return emilkm\efxphp\Amf\Types\Xml|SimpleXMLElement depending on useInternalXmlType setting
     */
    protected function readAmf0Xml()
    {
        $len = $this->readInt();
        $xmlstring = $this->readBytes($len);
        $value = simplexml_load_string($xmlstring);
        if ($this->useInternalXmlType == true) {
            $value = new Xml($xmlstring);
        } else {
            $value = simplexml_load_string($xmlstring);
        }
        return $value;
    }

    /**
     * @return emilkm\efxphp\Amf\Types\Date|DateTime
     */
    protected function readAmf0Date()
    {
        $amfdate = $this->readDouble();
        $this->skipBytes(2); //timezone offset is always 0, so no point to readShort()
        if ($this->useInternalDateType == true) {
            $value = new Date($amfdate);
        } else {
            $timestamp = $amfdate / 1000;
            $milli = round($timestamp - ($timestamp >> 0), 3) * 1000;
            $timestamp = floor($timestamp);
            $datestr = date('Y-m-d H:i:s.', $timestamp) . $milli;
            $value = new DateTime($datestr, new DateTimeZone(date_default_timezone_get()));
        }
        return $value;
    }

    //##########################################################################
    // AMF3
    //##########################################################################

    protected function getAmf3StringReference($ref)
    {
        if ($ref >= count($this->strings)) {
            throw new Exception('Undefined string reference: ' . $ref);
        }
        return $this->strings[$ref];
    }

    protected function getAmf3ObjectReference($ref)
    {
        if ($ref >= count($this->objects)) {
            throw new Exception('Undefined object reference: ' . $ref);
        }

        return $this->objects[$ref];
    }

    protected function getAmf3TraitReference($ref)
    {
        if ($ref >= count($this->traits)) {
            throw new Exception('Undefined trait reference: ' . $ref);
        }
        return $this->traits[$ref];
    }

    /**
     * @param mixed $type The value AMF3 type
     * @return mixed The php version of the data
     */
    protected function readAmf3ObjectValue($type)
    {
        $value = null;
        switch ($type) {
            case Constants::AMF3_STRING:
                $value = $this->readAmf3String();
                break;
            case Constants::AMF3_UNDEFINED:
            case Constants::AMF3_NULL:
                $value = null;
                break;
            case Constants::AMF3_BOOLEAN_FALSE:
                $value = false;
                break;
            case Constants::AMF3_BOOLEAN_TRUE:
                $value = true;
                break;
            case Constants::AMF3_INTEGER:
                $value = $this->readAmf3UInt29();
                break;
            case Constants::AMF3_DOUBLE:
                $value = $this->readDouble();
                break;
            case Constants::AMF3_ARRAY:
                $value = $this->readAmf3Array();
                break;
            case Constants::AMF3_OBJECT:
                $value = $this->readAmf3ScriptObject();
                break;
            case Constants::AMF3_DATE:
                $value = $this->readAmf3Date();
                break;
            case Constants::AMF3_BYTEARRAY:
                $value = $this->readAmf3ByteArray();
                break;
            case Constants::AMF3_XML:
                $value = $this->readAmf3Xml();
                break;
            case Constants::AMF3_XMLDOCUMENT:
                $value = $this->readAmf3XmlDocument();
                break;
            case Constants::AMF3_VECTOR_INT:
            case Constants::AMF3_VECTOR_UINT:
            case Constants::AMF3_VECTOR_DOUBLE:
            case Constants::AMF3_VECTOR_OBJECT:
                $value = $this->readAmf3Vector($type);
                break;
            case Constants::AMF3_DICTIONARY:
                throw new Exception('Amf3 Dictionary type not supported');
            default:
                throw new Exception('Undefined Amf3 type encountered: ' . $type);
        }
        return $value;
    }

    /**
     * AMF 3 represents smaller integers with fewer bytes using the most
     * significant bit of each byte. The worst case uses 32-bits
     * to represent a 29-bit number, which is what we would have
     * done with no compression.
     * <pre>
     * 0x00000000 - 0x0000007F : 0xxxxxxx
     * 0x00000080 - 0x00003FFF : 1xxxxxxx 0xxxxxxx
     * 0x00004000 - 0x001FFFFF : 1xxxxxxx 1xxxxxxx 0xxxxxxx
     * 0x00200000 - 0x3FFFFFFF : 1xxxxxxx 1xxxxxxx 1xxxxxxx xxxxxxxx
     * 0x40000000 - 0xFFFFFFFF : throw range exception
     * </pre>
     *
     * @return int The value capable of holding an unsigned 29 bit integer
     */
    protected function readAmf3UInt29()
    {
        $value = $this->readByte();
        if ($value < 128) {
            return $value;
        } else {
            $value = ($value & 0x7f) << 7;
            $chr = $this->readByte();
            if ($chr < 128) {
                return $value | $chr;
            } else {
                $value = ($value | ($chr & 0x7f)) << 7;
                $chr = $this->readByte();
                if ($chr < 128) {
                    return $value | $chr;
                } else {
                    $value = ($value | ($chr & 0x7f)) << 8;
                    $chr = $this->readByte();
                    $value |= $chr;
                    if (($value & 0x10000000) !== 0) {
                        $value |= ~0x1fffffff; // extend the sign bit regardless of integer (bit) size
                    }
                    return $value;
                }
            }
        }
    }

    /**
     * @return emilkm\efxphp\Amf\Types\Date|DateTime
     */
    protected function readAmf3Date()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3ObjectReference($ref >> 1);
        }
        $amfdate = $this->readDouble();
        if ($this->useInternalDateType == true) {
            $value = new Date($amfdate);
        } else {
            $timestamp = $amfdate / 1000;
            $milli = round($timestamp - ($timestamp >> 0), 3) * 1000;
            $timestamp = floor($timestamp);
            $datestr = date('Y-m-d H:i:s.', $timestamp) . $milli;
            $value = new DateTime($datestr, new DateTimeZone(date_default_timezone_get()));
        }
        $this->objects[] = &$value;
        return $value;
    }

    /**
     * @return string
     */
    protected function readAmf3String()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3StringReference($ref >> 1);
        }
        $len = $ref >> 1;
        if ($len == 0) {
            return Constants::EMPTY_STRING;
        }
        $value = $this->readBytes($len);
        $this->strings[] = $value;
        return $value;
    }

    /**
     * @return emilkm\efxphp\Amf\Types\Xml|SimpleXMLElement depending on useInternalXmlType setting
     */
    protected function readAmf3Xml()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3ObjectReference($ref >> 1);
        }
        $len = $ref >> 1;
        $xmlstring = $this->readBytes($len);
        if ($this->useInternalXmlType == true) {
            $value = new Xml($xmlstring);
        } else {
            $value = simplexml_load_string($xmlstring);
        }
        $this->objects[] = &$value;
        return $value;
    }

    /**
     * @return emilkm\efxphp\Amf\Types\XmlDocument|DOMElement depending on useInternalXmlDocumentType setting
     */
    protected function readAmf3XmlDocument()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3ObjectReference($ref >> 1);
        }
        $len = $ref >> 1;
        $xmlstring = $this->readBytes($len);
        if ($this->useInternalXmlDocumentType == true) {
            $value = new XmlDocument($xmlstring);
        } else {
            $value = dom_import_simplexml(simplexml_load_string($xmlstring));
        }
        $this->objects[] = &$value;
        return $value;
    }

    /**
     * @return emilkm\efxphp\Amf\Types\ByteArray
     */
    protected function readAmf3ByteArray()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            $value = $this->getAmf3ObjectReference($ref >> 1);
        } else {
            $len = $ref >> 1;
            $bytes = $this->readBytes($len);
            $value = new ByteArray($bytes);
        }
        $this->objects[] = &$value;
        return $value;
    }

    /**
     * @return array
     */
    protected function readAmf3Array()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3ObjectReference($ref >> 1);
        }
        $len = $ref >> 1;
        $arr = array();
        $this->objects[] = &$arr;
        $key = $this->readAmf3String();
        while ($key != '') {
            $value = $this->readObject();
            $arr[$key] = $value;
            $key = $this->readAmf3String();
        }
        for ($i = 0; $i < $len; $i++) {
            $value = $this->readObject();
            $arr[$i] = $value;
        }
        return $arr;
    }

    /**
     * @return mixed stdClass or typed object
     */
    protected function readAmf3ScriptObject()
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3ObjectReference($ref >> 1);
        }
        $ti = $this->readAmf3Traits($ref);
        $obj = $this->resolveType($ti->className);
        $this->objects[] = &$obj;
        if ($ti->externalizable) {
            if ($ti->className == 'flex.messaging.io.ArrayCollection' || $ti->className == 'flex.messaging.io.ObjectProxy') {
                $obj = $this->readObject();
            } else {
                $externalizedDataField = Constants::EXTERNALIZED_DATA_FIELD;
                if (is_object($obj)) {
                    $obj->$externalizedDataField = $this->readObject();
                } else {
                    $obj[$externalizedDataField] = $this->readObject();
                }
            }
        } else {
            $len = $ti->length();
            if (is_object($obj)) {
                for ($i = 0; $i < $len; $i++) {
                    $key = $ti->properties[$i];
                    $value = $this->readObject();
                    $obj->$key = $value;
                }
                if ($ti->dynamic) {
                    $key = $this->readAmf3String();
                    while ($key != '') {
                        $value = $this->readObject();
                        $obj->$key = $value;
                        $key = $this->readAmf3String();
                    }
                }
            } else {
                for ($i = 0; $i < $len; $i++) {
                    $key = $ti->properties[$i];
                    $value = $this->readObject();
                    $obj[$key] = $value;
                }
                if ($ti->dynamic) {
                    $key = $this->readAmf3String();
                    while ($key != '') {
                        $value = $this->readObject();
                        $obj[$key] = $value;
                        $key = $this->readAmf3String();
                    }
                }
            }
        }
        return $obj;
    }

    /**
     * @param mixed $ref
     *
     * @return TraitsInfo
     */
    protected function readAmf3Traits($ref)
    {
        if (($ref & 3) == 1) {
            return $this->getAmf3TraitReference($ref >> 2);
        }
        $externalizable = (($ref & 4) == 4);
        $dynamic = (($ref & 8) == 8);
        $count = ($ref >> 4); /* uint29 */
        $className = $this->readAmf3String();
        $ti = new TraitsInfo($className, $dynamic, $externalizable);
        $this->traits[] = $ti;
        for ($i = 0; $i < $count; $i++) {
            $propName = $this->readAmf3String();
            $ti->addProperty($propName);
        }
        return $ti;
    }

    /**
     * Reads a vector array of objects from the AMF stream. This works for all vector arrays: vector-object, vector-int vector-uint and
     * vector-double. The Vector is cast to a PHP array. Please note that because of the way php handles integers, uints have to be cast as
     * floats. See {@link http://php.net/manual/en/language.types.integer.php}
     *
     * @param int $type
     *
     * @return Vector
     */
    protected function readAmf3Vector($type)
    {
        $ref = $this->readAmf3UInt29();
        if (($ref & 1) == 0) {
            return $this->getAmf3ObjectReference($ref >> 1);
        }
        $len = ($ref >> 1);
        $vector = new Vector($type, array());
        $vector->fixed = $this->readBoolean();
        $this->objects[] = &$vector;
        if ($type === Constants::AMF3_VECTOR_OBJECT) {
            $this->readAmf3String(); //className
            for ($i = 0; $i < $len; $i++) {
                $vector->data[] = $this->readObject();
            }
        } else {
            switch ($type) {
                case Constants::AMF3_VECTOR_INT:
                    $length = 4;
                    $format = 'ival';
                    break;
                case Constants::AMF3_VECTOR_UINT:
                    $length = 4;
                    $format = 'Ival';
                    break;
                case Constants::AMF3_VECTOR_DOUBLE:
                    $length = 8;
                    $format = 'dval';
                    break;
            }
            for ($i = 0; $i < $len; $i++) {
                $vector->data[] = $this->readAmf3VectorValue($length, $format);
            }
        }
        return $vector;
    }

    /**
     * Read numeric values from the AMF byte stream. Please be aware that unsigned integers are not really supported in PHP, and for this reason
     * unsigned integers are cast to float. {@link http://php.net/manual/en/language.types.integer.php}.
     *
     * @param int $length You can specify 4 for integers or 8 for double precision floating point.
     * @param string $format 'ival' for signed integers, 'Ival' for unsigned integers, and "dval" for double precision floating point
     *
     * @return <type>
     */
    protected function readAmf3VectorValue($length, $format)
    {
        $value = $this->readBytes($length);
        if (!$this->bigEndianMachine) {
            $value = strrev($value);
        }
        $array = unpack($format, $value);
        // Unsigned Integers don't work in PHP amazingly enough. If you go into the "upper" region
        // on the Actionscript side, this will come through as a negative without this cast to a float
        // see http://php.net/manual/en/language.types.integer.php
        if ($format === 'Ival') {
            $array['val'] = floatval(sprintf('%u', $array['val']));
        }
        return $array['val'];
    }
}
