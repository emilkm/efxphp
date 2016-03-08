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
class OutputExt extends AbstractOutput
{
    const AMF_AMF3 = 1;
    const AMF_BIGENDIAN = 2;
    const AMF0_ASSOC = 4;
    const AMF_POST_DECODE = 8;
    const AMF_AS_STRING_BUILDER = 16;
    const AMF_TRANSLATE_CHARSET = 32;
    const AMF_TRANSLATE_CHARSET_FAST = 96; //32|64
    const AMF3_NSND_ARRAY_AS_OBJECT = 128;

    const AMFC_RAW = 0;
    const AMFC_XML = 1;
    const AMFC_OBJECT = 2;
    const AMFC_TYPEDOBJECT = 3;
    const AMFC_ANY = 4;
    const AMFC_ARRAY = 5;
    const AMFC_NONE = 6;
    const AMFC_BYTEARRAY = 7;
    const AMFC_EXTERNAL = 8;
    const AMFC_DATE = 9;
    const AMFC_XMLDOCUMENT = 10;
    const AMFC_VECTOR_OBJECT = 11;

    public $encodeFlags;

    /**
     * Constuct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * References are handled within the extension, nothing to do here.
     */
    public function resetReferences()
    {
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
        $this->encodeFlags = ($this->avmPlus ? self::AMF_AMF3 : 0)
            | (!$this->bigEndianMachine ? self::AMF_BIGENDIAN : 0)
            | ($this->amf3nsndArrayAsObject ? self::AMF3_NSND_ARRAY_AS_OBJECT : 0);
        $data = amf_encode($value, $this->encodeFlags, array(&$this, 'encodeCallback'));
        $this->data .= $data;
    }

    private function encodeCallback($value)
    {
        if (is_object($value)) {
            if ($value instanceof Date) {
                $amfdate = (float) $value->timestamp . $value->milli + 0.0;
                return array($amfdate, self::AMFC_DATE);
            } elseif ($value instanceof DateTime) {
                $amfdate = (float) $value->getTimeStamp() . floor($value->format('u') / 1000) + 0.0;
                return array($amfdate, self::AMFC_DATE);
            } elseif ($value instanceof ByteArray) {
                return array($value->data, self::AMFC_BYTEARRAY);
            } elseif ($value instanceof Xml) {
                return array($value->data, self::AMFC_XML);
            } elseif ($value instanceof SimpleXMLElement) {
                $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($value->asXML()));
                return array($xmlstring, self::AMFC_XML);
            } elseif ($value instanceof XmlDocument) {
                return array($value->data, self::AMFC_XMLDOCUMENT);
            } elseif ($value instanceof DOMElement) {
                $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($value->ownerDocument->saveXML($value)));
                return array($xmlstring, self::AMFC_XMLDOCUMENT);
            } elseif ($value instanceof Vector) {
                return array($value, self::AMFC_VECTOR_OBJECT);
            } else {
                $className = get_class($value);
                $remoteClassField = Constants::REMOTE_CLASS_FIELD;
                if (isset($value->$remoteClassField)) {
                    $className = $value->$remoteClassField;
                    unset($value->$remoteClassField);
                } else {
                    $className = str_replace('\\', '.', $className);
                }
                if ($className == '') {
                    return array($value, self::AMFC_OBJECT, $className);
                } else {
                    return array($value, self::AMFC_TYPEDOBJECT, $className);
                }
            }
        }
    }
}
