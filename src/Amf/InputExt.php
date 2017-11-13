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
class InputExt extends AbstractInput
{
    const AMF_AMF3 = 1;
    const AMF_BIGENDIAN = 2;
    const AMF_OBJECT_AS_ASSOC = 4;
    const AMF3_NSND_ARRAY_AS_OBJECT = 8;
    const AMF_USE_RLAND_DATE = 16;
    const AMF_USE_RLAND_XML = 32;
    const AMF_USE_RLAND_XMLDOCUMENT = 64;

    const AMFC_DATE = 0;
    const AMFC_BYTEARRAY = 1;
    const AMFC_XML = 2;
    const AMFC_XMLDOCUMENT = 3;
    const AMFC_VECTOR_INT = 4;
    const AMFC_VECTOR_UINT = 5;
    const AMFC_VECTOR_DOUBLE = 6;
    const AMFC_VECTOR_OBJECT = 7;
    const AMFC_EXTERNALIZABLE = 8;

	private $userlandTypes = [
        'emilkm\\efxphp\\Amf\\Types\\Date' => self::AMFC_DATE,
        'emilkm\\efxphp\\Amf\\Types\\ByteArray' => self::AMFC_BYTEARRAY,
        'emilkm\\efxphp\\Amf\\Types\\Xml' => self::AMFC_XML,
        'emilkm\\efxphp\\Amf\\Types\\XmlDocument' => self::AMFC_XMLDOCUMENT,
        'emilkm\\efxphp\\Amf\\Types\\Vector' => self::AMFC_VECTOR_OBJECT
    ];

    public $decodeFlags;

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
     * Public entry point to read a top level AMF Object, such as
     * a header value or a message body.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function readObject()
    {
        $this->decodeFlags = ((!$this->bigEndianMachine ? self::AMF_BIGENDIAN : 0)
            | ($this->decodeAmfObjectAsArray ? self::AMF_OBJECT_AS_ASSOC : 0));
        $data = amf_decode($this->data, $this->pos, $this->decodeFlags, $this->userlandTypes, array(&$this, 'decodeCallback'));

        return $data;
    }

    /**
     * @param mixed $type The AMF callback type
     * @param mixed $arg
     *
     * @return {\DateTime|Types\ByteArray|\SimpleXMLElement|\stdClass|mixed}
     */
    private function decodeCallback($type, $arg)
    {
        switch ($type) {
            case self::AMFC_DATE:
                if ($this->useInternalDateType == true) {
                    $value = new Date($arg);
                } else {
                    $timestamp = $arg / 1000;
                    $milli = round($timestamp - ($timestamp >> 0), 3) * 1000;
                    $timestamp = floor($timestamp);
                    $datestr = date('Y-m-d H:i:s.', $timestamp) . $milli;
                    $value = new DateTime($datestr, new DateTimeZone(date_default_timezone_get()));
                }

                return $value;
            case self::AMFC_BYTEARRAY:
                return new ByteArray($arg);
            case self::AMFC_XML:
                if ($this->useInternalXmlType == true) {
                    $value = new Xml($arg);
                } else {
                    $value = simplexml_load_string($arg);
                }

                return $value;
            case self::AMFC_XMLDOCUMENT:
                if ($this->useInternalXmlDocumentType == true) {
                    $value = new XmlDocument($arg);
                } else {
                    $value = dom_import_simplexml(simplexml_load_string($arg));
                }

                return $value;
            case self::AMFC_VECTOR_INT:
                return new Vector(Vector::AMF3_VECTOR_INT, $arg);
            case self::AMFC_VECTOR_UINT:
                return new Vector(Vector::AMF3_VECTOR_UINT, $arg);
            case self::AMFC_VECTOR_DOUBLE:
                return new Vector(Vector::AMF3_VECTOR_DOUBLE, $arg);
            case self::AMFC_VECTOR_OBJECT:
                return new Vector(Vector::AMF3_VECTOR_OBJECT, $arg);
            case self::AMFC_EXTERNALIZABLE:
                if ($arg == 'flex.messaging.io.ArrayCollection' || $arg == 'flex.messaging.io.ObjectProxy') {
                    //returning NULL means that the externalized data is used directly. For example an array collection will not be deserialized
                    //as an array collection with an _externalizedData field containing the source array. Rather it will be deserialized directly as the source array
                    return;
                } else {
                    //externalized data we don't know what to do with. log an error, return an empty object typed with the class name.
                    //note: this is due to a limitation in the C code.
                    trigger_error('Unable to read externalizable data type ' . $arg, E_USER_ERROR);

                    return 'error';
                }
                break;
            default:
                throw new Exception('invalid type in decode callback : ' . $type);
        }
    }
}
