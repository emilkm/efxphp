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

use emilkm\efxphp\Amf\AbstractOutput;
use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\TraitsInfo;
use emilkm\efxphp\Amf\Types\ByteArray;
use emilkm\efxphp\Amf\Types\Date;
use emilkm\efxphp\Amf\Types\Vector;
use emilkm\efxphp\Amf\Types\Xml;
use emilkm\efxphp\Amf\Types\XmlDocument;

use Exception;
use DateTime;
use SimpleXMLElement;
use DOMElement;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Output extends AbstractOutput
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

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->resetReferences();
    }

    /**
     * References must be reset before writing a top level object,
     * such as a header or a body.
     */
    public function resetReferences()
    {
        $this->objects0 = array();
        $this->strings = array();
        $this->objects = array();
        $this->traits = array();
    }

    /**
     * Public entry point to write a top level AMF Object, such as
     * a header value or a message body.
     *
     * If we're using AMF 3, and a complex object is encountered,
     * encoding is switched to AVM+ format.
     *
     * @param mixed $value The object to write
     */
    public function writeObject($value)
    {
        $this->writeAmf0Object($value);
    }

    //##########################################################################
    // AMF0
    //##########################################################################

    protected function writeAmf0Object($value)
    {
        if (is_null($value)) {
            $this->writeAmf0Null();
            return;
        } elseif (is_int($value)) {
            $this->writeAmf0Number($value);
            return;
        } elseif (is_float($value)) {
            if ($value > Constants::AMF_U32_MAX) {
                // BlazeDS checks for BigInteger or BigDecimal here. For consistency
                // between 64bit and 32bit PHP and C we go with AMF_U32_MAX.
                $this->writeAmf0String($value);
            } else {
                $this->writeAmf0Number($value);
            }
            return;
        } elseif (is_string($value)) {
            $this->writeAmf0String($value);
            return;
        } elseif (is_bool($value)) {
            $this->writeAmf0Boolean($value);
            return;
        } elseif ($value instanceof Date || $value instanceof DateTime) {
            $this->writeAmf0Date($value);
            return;
        } else {
            if ($this->avmPlus) {
                $this->writeByte(Constants::AMF0_AMF3);
                $this->writeAmf3Object($value);
                return;
            } else {
                if (is_array($value)) {
                    $this->writeAmf0Array($value);
                    return;
                } elseif ($value instanceof Xml || $value instanceof XmlDocument
                    || $value instanceof SimpleXMLElement || $value instanceof DOMElement
                ) {
                    $this->writeAmf0XML($value);
                    return;
                } elseif (is_object($value)) {
                    $this->writeAmf0CustomObject($value);
                    return;
                }
            }
        }
        throw new Exception("Error writing AMF object " . print_r($value, false));
    }

    protected function writeAmf0Null()
    {
        $this->writeByte(Constants::AMF0_NULL);
    }

    protected function writeAmf0Undefined()
    {
        $this->writeByte(Constants::AMF0_UNDEFINED);
    }

    /**
     * @param bool $value
     */
    protected function writeAmf0Boolean($value)
    {
        $this->writeByte(Constants::AMF0_BOOLEAN);
        $this->writeBoolean($value);
    }

    /**
     * @param int $value
     */
    protected function writeAmf0Reference($value)
    {
        $this->writeByte(Constants::AMF0_REFERENCE);
        $this->writeShort($value);
    }

    /**
     * @param string $value The string data
     */
    protected function writeAmf0String($value)
    {
        $len = strlen($value);
        if ($len < Constants::AMF_U16_MAX) {
            $this->writeByte(Constants::AMF0_STRING);
            $this->writeUtf($value);
        } else {
            $this->writeByte(Constants::AMF0_LONGSTRING);
            $this->writeLongUtf($value);
        }
    }

    protected function writeAmf0Number($value)
    {
        $this->writeByte(Constants::AMF0_NUMBER);
        $this->writeDouble($value);
    }

    /**
     * @param emilkm\efxphp\Amf\Types\Date|DateTime $value
     */
    protected function writeAmf0Date($value)
    {
        $this->writeByte(Constants::AMF0_DATE);
        if ($value instanceof Date) {
            $amfdate = (float) $value->timestamp . $value->milli + 0.0;
        } else {
            $amfdate = (float) $value->getTimestamp() . floor($value->format("u") / 1000) + 0.0;
        }
        $this->writeDouble($amfdate);
        $this->writeShort(0);
    }

    /**
     * @param emilkm\efxphp\Amf\Types\Xml|emilkm\efxphp\Amf\Types\XmlDocument|SimpleXMLElement|DOMElement $value
     */
    protected function writeAmf0Xml($value)
    {
        if ($this->handleReference($value, $this->objects0)) {
            return;
        }
        $this->writeByte(Constants::AMF0_XMLDOCUMENT);
        if ($value instanceof Xml || $value instanceof XmlDocument) {
            $this->writeLongUtf($value->data);
        } elseif ($value instanceof SimpleXMLElement) {
            $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($value->asXML()));
            $this->writeLongUtf($xmlstring);
        } else {
            $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($value->ownerDocument->saveXML($value)));
            $this->writeLongUtf($xmlstring);
        }
    }

    /**
     * If stdClass and _explicitType property is not set write anonymous,
     * otherwise typed.
     *
     * @param mixed $value
     */
    protected function writeAmf0CustomObject($value)
    {
        if ($this->handleReference($value, $this->objects0)) {
            return;
        }
        $className = get_class($value);
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        if (($className == 'stdClass' && !isset($value->$remoteClassField))
            || (isset($value->$remoteClassField) && $value->$remoteClassField == '')
        ) {
            $this->writeAmf0AnonymousObject($value);
            return;
        }
        if (isset($value->$remoteClassField)) {
            $className = $value->$remoteClassField;
        } else {
            $className = str_replace('\\', '.', $className);
        }
        $this->writeByte(Constants::AMF0_TYPEDOBJECT);
        $this->writeUtf($className);
        foreach ($value as $key => $val) {
            //Don't write protected properties or explicit type
            if ($key[0] != "\0" && "$key" != $remoteClassField) {
                $this->writeUtf($key);
                $this->writeAmf0Object($val);
            }
        }
        //write object end 009
        $this->writeShort(0);
        $this->writeByte(Constants::AMF0_OBJECTEND);
    }

    protected function writeAmf0AnonymousObject($value, $writeTypeMarker = true)
    {
        if ($writeTypeMarker) {
            $this->writeByte(Constants::AMF0_OBJECT);
            //$this->data .= "\3";
        }
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        foreach ($value as $key => $val) {
            //Don't write protected properties or explicit type
            if ("$key" != $remoteClassField) {
                $this->writeUtf($key);
                $this->writeAmf0Object($val);
            }
        }
        //write object end 009
        $this->writeShort(0);
        $this->writeByte(Constants::AMF0_OBJECTEND);
    }

    protected function writeAmf0Array(array $value)
    {
        $count = count($this->objects0);
        if ($count <= Constants::MAX_STORED_OBJECTS) {
            $this->objects0[$count] = &$value;
        }
        $len = count($value);
        if ($len == 0) {
            $this->writeByte(Constants::AMF0_ARRAY);
            $this->writeInt(0);
            return;
        }
        $numCount = 0;
        $strCount = 0;
        $maxIndex = -1;
        $hasNegative = false;
        foreach ($value as $key => $val) {
            if (is_int($key)) {
                if ($key > $maxIndex) {
                    $maxIndex = $key;
                }
                if ($key < 0) {
                    $hasNegative = true;
                    $strCount++;
                } else {
                    $numCount++;
                }
            } else {
                $strCount++;
            }
        }
        if ($numCount > 0 && ($strCount > 0 || $hasNegative)) {
            //associative
            $this->writeByte(Constants::AMF0_MIXEDARRAY);
            $this->writeInt($len);
            $this->writeAmf0AnonymousObject($value, false);
        } elseif ($numCount > 0) {
            //strict
            $this->writeByte(Constants::AMF0_ARRAY);
            if ($maxIndex == $numCount - 1) {
                //dense
                $this->writeInt($numCount);
                foreach ($value as $key => $val) {
                    $this->writeAmf0Object($val);
                }
            } else {
                //sparse
                $this->writeInt($maxIndex + 1);
                for ($i = 0; $i < $maxIndex + 1; $i++) {
                    if (!array_key_exists($i, $value)) {
                        $this->writeAmf0Undefined();
                    } else {
                        $this->writeAmf0Object($value[$i]);
                    }
                }
            }
        } else {
            //string keys only
            $this->writeAmf0AnonymousObject($value);
        }
    }

    //##########################################################################
    // AMF3
    //##########################################################################

    protected function writeAmf3Object($value)
    {
        if (is_null($value)) {
            $this->writeAmf3Null();
            return;
        } elseif (is_int($value)) {
            $this->writeAmf3Int($value);
            return;
        } elseif (is_float($value)) {
            $this->data .= "\5";
            $this->writeDouble($value);
            return;
        } elseif (is_string($value)) {
            $this->data .= "\6";
            $this->writeAmf3String($value);
            return;
        } elseif (is_bool($value)) {
            $this->writeAmf3Boolean($value);
            return;
        } elseif ($value instanceof Date || $value instanceof DateTime) {
            $this->writeAmf3Date($value);
            return;
        } elseif (is_array($value)) {
            $this->writeAmf3Array($value);
            return;
        } elseif ($value instanceof ByteArray) {
            $this->writeAmf3ByteArray($value);
            return;
        } elseif ($value instanceof Xml || $value instanceof SimpleXMLElement) {
            $this->writeAmf3Xml($value);
            return;
        } elseif ($value instanceof XmlDocument || $value instanceof DOMElement) {
            $this->writeAmf3XmlDocument($value);
            return;
        } elseif ($value instanceof Vector) {
            $this->writeAmf3Vector($value);
            return;
        } elseif (is_object($value)) {
            $this->writeAmf3CustomObject($value);
            return;
        }
        throw new Exception("couldn't write object " . print_r($value, false));
    }

    protected function writeAmf3Undefined()
    {
        $this->data .= "\0";
    }

    protected function writeAmf3Null()
    {
        $this->data .= "\1";
    }

    /**
     * @param bool $value
     */
    protected function writeAmf3Boolean($value)
    {
        $this->data .= $value ? "\3" : "\2";
    }

    /**
     * Write an (un-)signed integer ().
     *
     * Represent smaller integers with fewer bytes using the most
     * significant bit of each byte. The worst case uses 32-bits
     * to represent a 29-bit number, which is what we would have
     * done with no compression.
     *
     * 0x00000000 - 0x0000007F : 0xxxxxxx
     * 0x00000080 - 0x00003FFF : 1xxxxxxx 0xxxxxxx
     * 0x00004000 - 0x001FFFFF : 1xxxxxxx 1xxxxxxx 0xxxxxxx
     * 0x00200000 - 0x3FFFFFFF : 1xxxxxxx 1xxxxxxx 1xxxxxxx xxxxxxxx
     * 0x40000000 - 0xFFFFFFFF : throw range exception
     *
     * @param int $value The integer to serialise
     */
    protected function writeAmf3UInt29($value)
    {
        $value &= 0x1fffffff;
        if ($value < 0x80) {
            $this->data .=
                chr($value);
        } elseif ($value < 0x4000) {
            $this->data .=
                chr($value >> 7 & 0x7f | 0x80) .
                chr($value & 0x7f);
        } elseif ($value < 0x200000) {
            $this->data .=
                chr($value >> 14 & 0x7f | 0x80) .
                chr($value >> 7 & 0x7f | 0x80) .
                chr($value & 0x7f);
        } elseif ($value < 0x40000000) {
            $this->data .=
                chr($value >> 22 & 0x7f | 0x80) .
                chr($value >> 15 & 0x7f | 0x80) .
                chr($value >> 8 & 0x7f | 0x80) .
                chr($value & 0xff);
        } else {
            throw new Exception('Integer out of range');
        }
    }

    /**
     * @param number $value
     */
    protected function writeAmf3Int($value)
    {
        if (is_int($value) && $value >= Constants::AMF3_INT28_MIN && $value <= Constants::AMF3_INT28_MAX) {
            $this->data .= "\4";
            $this->writeAmf3UInt29($value);
        } else {
            //overflow condition would occur upon int conversion
            $this->data .= "\5";
            $this->writeDouble($value);
        }
    }

    /**
     * Sending strings larger than 268435455 (2^28-1 byte) will (silently) fail!
     * The string marker must be written already, if needed.
     *
     * @param string $value
     */
    protected function writeAmf3String($value)
    {
        if ($value === '') {
            //Write 0x01 to specify the empty string ('UTF-8-empty')
            $this->data .= "\1"; //$this->writeAmf3UInt29(1);
            return;
        }
        if ($this->handleReference($value, $this->strings)) {
            return;
        }
        $len = strlen($value);
        $this->writeAmf3UInt29($len << 1 | 1);
        $this->data .= $value;
    }

    /**
     * @param emilkm\efxphp\Amf\Types\Xml|SimpleXMLElement $value
     */
    protected function writeAmf3Xml($value)
    {
        $this->writeByte(Constants::AMF3_XML);
        if ($this->handleReference($value, $this->objects)) {
            return;
        }
        if ($value instanceof Xml) {
            $this->writeAmf3String($value->data);
        } else {
            $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($value->asXML()));
            $this->writeAmf3String($xmlstring);
        }
    }

    /**
     * @param emilkm\efxphp\Amf\Types\XmlDocument|DOMElement $value
     */
    protected function writeAmf3XmlDocument($value)
    {
        $this->writeByte(Constants::AMF3_XMLDOCUMENT);
        if ($this->handleReference($value, $this->objects)) {
            return;
        }
        if ($value instanceof XmlDocument) {
            $this->writeAmf3String($value->data);
        } else {
            $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($value->ownerDocument->saveXML($value)));
            $this->writeAmf3String($xmlstring);
        }
    }

    /**
     * @param emilkm\efxphp\Amf\Types\Date|DateTime $value
     */
    protected function writeAmf3Date($value)
    {
        $this->writeByte(Constants::AMF3_DATE);
        if ($this->handleReference($value, $this->objects)) {
            return;
        }
        if ($value instanceof Date) {
            $amfdate = $value->timestamp . $value->milli;
        } else {
            $amfdate = $value->getTimestamp() . floor($value->format('u') / 1000);
        }
        $this->writeAmf3UInt29(1);
        $this->writeDouble($amfdate);
    }

    /**
     * @param ByteArray $value
     */
    protected function writeAmf3ByteArray(ByteArray $value)
    {
        $this->writeByte(Constants::AMF3_BYTEARRAY);
        if ($this->handleReference($value, $this->objects)) {
            return;
        }
        $len = strlen($value->data);
        $this->writeAmf3UInt29($len << 1 | 1);
        $this->data .= $value->data;
    }

    /**
     * looks if $obj already has a reference. If it does, write it, and return true. If not, add it to the references array.
     * Depending on whether or not the spl_object_hash function can be used ( available (PHP >= 5.2), and can only be used on an object)
     * things are handled a bit differently:
     * - if possible, objects are hashed and the hash is used as a key to the references array. So the array has the structure hash => reference
     * - if not, the object is pushed to the references array, and array_search is used. So the array has the structure reference => object.
     * maxing out the number of stored references improves performance(tested with an array of 9000 identical objects). This may be because isset's performance
     * is linked to the size of the array. weird...
     * note on using $references[$count] = &$obj; rather than
     * $references[] = &$obj;
     * the first one is right, the second is not, as with the second one we could end up with the following:
     * some object hash => 0, 0 => array. (it should be 1 => array)
     *
     * This also means that 2 completely separate instances of a class but with the same values will be written fully twice if we can't use the hash system
     *
     * @param mixed $obj
     * @param array $references
     */
    protected function handleReference(&$obj, array &$references)
    {
        $key = false;
        $count = count($references);
        if (is_object($obj) && function_exists('spl_object_hash')) {
            $hash = spl_object_hash($obj);
            if (isset($references[$hash])) {
                $key = $references[$hash];
            } else {
                if ($count <= Constants::MAX_STORED_OBJECTS) {
                    //there is some space left, store object for reference
                    $references[$hash] = $count;
                }
            }
        } else {
            //no hash available, use array with simple numeric keys
            $key = array_search($obj, $references, true);
            if (($key === false) && ($count <= Constants::MAX_STORED_OBJECTS)) {
                // $key === false means the object isn't already stored
                // count... means there is still space
                //so only store if these 2 conditions are met
                $references[$count] = &$obj;
            }
        }
        if ($key !== false) {
            //reference exists. write it and return true
            if ($this->avmPlus) {
                $ref = $key << 1;
                $this->writeAmf3UInt29($ref);
            } else {
                $this->writeAmf0Reference($key);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * If stdClass and _explicitType property is not set,
     * or _explicitType is blank, write anonymous, otherwise typed.
     *
     * @param object $value
     */
    protected function writeAmf3CustomObject($value)
    {
        $this->data .= "\12"; //$this->writeByte(Constants::AMF3_OBJECT);
        if ($this->handleReference($value, $this->objects)) {
            return;
        }
        $className = get_class($value);
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $explicitTypeIsSet = isset($value->$remoteClassField);
        if (($className == 'stdClass' && !$explicitTypeIsSet)
            || ($explicitTypeIsSet && $value->$remoteClassField == '')
        ) {
            $this->writeAmf3AnonymousObject($value);
            return;
        }
        if ($explicitTypeIsSet) {
            $className = $value->$remoteClassField;
        } else {
            $className = str_replace('\\', '.', $className);
        }
        $externalizable = false;
        $dynamic = false;
        $properties = array();
        foreach ($value as $key => $val) {
            //Don't write protected properties or explicit type
            if ($key[0] != "\0" && "$key" != $remoteClassField) {
                $properties[] = $key;
            }
        }
        if (isset($this->traits[$className])) {
            $ti = $this->traits[$className];
            if ($ti['properties'] === $properties) {
                $ref = $ti['reference'] << 2 | 1;
                $this->writeAmf3UInt29($ref);
            } else {
                //bogus traits entry
                $this->traits[] = array();
                $dynamic = true;
                $ref = Constants::AMF3_OBJECT_ENCODING | ($externalizable ? 4 : 0) | ($dynamic ? 8 : 0) | (0 << 4);
                $this->writeAmf3UInt29($ref);
                $this->writeAmf3String($className);
            }
        } else {
            $ti = array(
                'reference' => count($this->traits),
                'properties' => $properties,
            );
            $this->traits[$className] = $ti;
            $count = count($properties);
            $ref = Constants::AMF3_OBJECT_ENCODING | ($externalizable ? 4 : 0) | ($dynamic ? 8 : 0) | ($count << 4);
            $this->writeAmf3UInt29($ref);
            $this->writeAmf3String($className);
            foreach ($properties as $property) {
                $this->writeAmf3String($property);
            }
        }
        if (!$dynamic) {
            foreach ($properties as $property) {
                $this->writeAmf3Object($value->$property);
            }
        } else {
            foreach ($properties as $property) {
                $this->writeAmf3String($property);
                $this->writeAmf3Object($value->$property);
            }
            $this->data .= "\1"; //$this->writeAmf3String('');
        }
    }

    /**
     * Handles writing an anonymous object (stdClass), can also be a reference.
     *
     * @param stdClass $value
     * @param bool $writeTypeMarker Write type marker if not already written.
     */
    protected function writeAmf3AnonymousObject($value, $writeTypeMarker = false)
    {
        if ($writeTypeMarker == true) {
            $this->data .= "\12"; //$this->writeByte(Constants::AMF3_OBJECT);
        }
        //bogus traits entry
        $this->traits[] = array();
        //anonymous object. So type this as a dynamic object with no sealed members.
        //U29O-traits : 1011.
        $this->writeAmf3UInt29(0xB); //3 | 0 | 8 | (0 << 4)
        //no class name. empty string for anonymous object
        $this->data .= "\1"; //$this->writeAmf3String('');
        //name/value pairs for dynamic properties
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        foreach ($value as $key => $val) {
            //Don't write protected properties or explicit type
            if ("$key" != $remoteClassField) {
                $this->writeAmf3String($key);
                $this->writeAmf3Object($val);
            }
        }
        //empty string, marks end of dynamic members
        $this->data .= "\1"; //$this->writeAmf3String('');
    }

    /**
     * write amf3 array
     * @param array $value
     */
    protected function writeAmf3Array(array $value)
    {
        //Referencing is disabled in arrays because
        //if the array contains only primitive values,
        //then the identity operator === will say that the two arrays are strictly equal
        //when they contain the same values, even though they maybe be distinct.
        $count = count($this->objects);
        if ($count <= Constants::MAX_STORED_OBJECTS) {
            $this->objects[$count] = &$value;
        }
        $len = count($value);
        if ($len == 0) {
            $this->writeByte(Constants::AMF3_ARRAY);
            $this->writeAmf3UInt29(0 | 1);
            $this->data .= "\1"; //$this->writeAmf3String('');
            return;
        }
        $maxIndex = -1;
        $denseCount = 0;
        $assocCount = 0;
        foreach ($value as $key => $val) {
            if (is_int($key)) {
                if ($key > $maxIndex) {
                    $maxIndex = $key;
                }
                if (($key > 0 && $maxIndex != $denseCount) || $key < 0) {
                    $assocCount++;
                } else {
                    $denseCount++;
                }
            } else {
                $assocCount++;
            }
        }
        if ($this->amf3nsndArrayAsObject == true && ($assocCount > 0 || $maxIndex != $denseCount - 1)
            //&& (($assocCount > 0 && $denseCount == 0) || ($denseCount > 0 && $maxIndex != $denseCount - 1))
        ) {
            $this->writeAmf3AnonymousObject($value, true);
        } else {
            $this->data .= "\11"; //$this->writeByte(Constants::AMF3_ARRAY);
            $this->writeAmf3UInt29($denseCount << 1 | 1);
            if ($assocCount > 0) {
                foreach ($value as $key => $val) {
                    if (is_int($key) && $key >= 0 && $key < $denseCount) {
                        continue;
                    }
                    $this->writeAmf3String($key);
                    $this->writeAmf3Object($val);
                }
            }
            $this->data .= "\1"; //$this->writeAmf3String('');
            if ($denseCount > 0) {
                /*foreach ($value as $key => $val) {
                    if (is_int($key) && $key >= 0 && $key < $denseCount) {
                        $this->writeAmf3Object($val);
                    }
                }*/
                for ($i = 0; $i < $denseCount; $i++) {
                    $this->writeAmf3Object($value[$i]);
                }
            }
        }
    }

    /**
     * @param Vector $value
     */
    protected function writeAmf3Vector(Vector $value)
    {
        $this->writeByte($value->type);
        if ($this->handleReference($value, $this->objects)) {
            return;
        }
        $len = count($value->data);
        $this->writeAmf3UInt29($len << 1 | 1);
        $this->writeBoolean($value->fixed);
        if ($value->type === Constants::AMF3_VECTOR_OBJECT) {
            $className = '';
            if ($len > 0) {
                $className = get_class($value->data[0]);
                $remoteClassField = Constants::REMOTE_CLASS_FIELD;
                if (($className == 'stdClass' && !isset($value->$remoteClassField))
                    || (isset($value->$remoteClassField) && $value->$remoteClassField == '')
                ) {
                    $className = 'Object';
                } elseif (isset($value->$remoteClassField)) {
                    $className = $value->$remoteClassField;
                } else {
                    $className = str_replace('\\', '.', $className);
                }
            }
            $this->writeAmf3String($className);
            for ($i = 0; $i < $len; $i++) {
                $this->writeAmf3CustomObject($value->data[$i]);
            }
        } else {
            if ($value->type == Constants::AMF3_VECTOR_INT) {
                $format = 'i';
            } elseif ($value->type == Constants::AMF3_VECTOR_UINT) {
                $format = 'I';
            } elseif ($value->type == Constants::AMF3_VECTOR_DOUBLE) {
                $format = 'd';
            }
            for ($i = 0; $i < $len; $i++) {
                $this->writeAmf3VectorValue($value->data[$i], $format);
            }
        }
    }

    /**
     * Writes numeric values for int, uint, and double (floating point) vectors to the AMF byte stream.
     *
     * @param   mixed  $value  But should be either an integer (signed or unsigned) or a floating point.
     * @param   string $format 'i' for signed integers, 'I' for unsigned integers, and 'd' for double precision floating point
     */
    private function writeAmf3VectorValue($value, $format)
    {
        $bytes = pack($format, $value);
        if (!$this->bigEndianMachine) {
            $bytes = strrev($bytes);
        }
        $this->data .= $bytes;
    }
}
