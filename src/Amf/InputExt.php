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
    const AMF_POST_DECODE = 8;
    const AMF_AS_STRING_BUILDER = 16;
    const AMF_TRANSLATE_CHARSET = 32;
    const AMF_TRANSLATE_CHARSET_FAST = 96; //32|64
    const AMF3_NSD_ARRAY_AS_OBJECT = 128;

    const AMFE_MAP = 1;
    const AMFE_POST_OBJECT = 2;
    const AMFE_POST_XML = 3;
    const AMFE_MAP_EXTERNALIZABLE = 4;
    const AMFE_POST_BYTEARRAY = 5;
    const AMFE_TRANSLATE_CHARSET = 6;
    const AMFE_POST_DATE = 7;
    const AMFE_POST_XMLDOCUMENT = 8;
    const AMFE_VECTOR_INT = 9;
    const AMFE_VECTOR_UINT = 10;
    const AMFE_VECTOR_DOUBLE = 11;
    const AMFE_VECTOR_OBJECT = 12;

    public $decodeFlags;

    public function __construct()
    {
        parent::__construct();
        $this->decodeFlags = (!$this->bigEndianMachine ? self::AMF_BIGENDIAN : 0);
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
        $data = amf_decode($this->data, $this->decodeFlags, $this->pos, array(&$this, 'decodeCallback'));

        return $data;
    }

    /**
     * @param mixed $event The AMFEvent
     * @param mixed $arg
     *
     * @return {\DateTime|Types\ByteArray|\SimpleXMLElement|\stdClass|mixed}
     */
    private function decodeCallback($event, $arg)
    {
        switch ($event) {
            case self::AMFE_MAP:
                return $this->resolveType($arg);
            case self::AMFE_POST_OBJECT:
                return $arg;
            case self::AMFE_POST_DATE:
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
            case self::AMFE_POST_XML:
                if ($this->useInternalXmlType == true) {
                    $value = new Xml($arg);
                } else {
                    $value = simplexml_load_string($arg);
                }

                return $value;
            case self::AMFE_POST_XMLDOCUMENT:
                if ($this->useInternalXmlDocumentType == true) {
                    $value = new XmlDocument($arg);
                } else {
                    $value = dom_import_simplexml(simplexml_load_string($arg));
                }

                return $value;
            case self::AMFE_MAP_EXTERNALIZABLE:
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
            case self::AMFE_POST_BYTEARRAY:
                return new ByteArray($arg);
            case self::AMFE_VECTOR_INT:
                return new Vector(Constants::AMF3_VECTOR_INT, $arg);
            case self::AMFE_VECTOR_UINT:
                return new Vector(Constants::AMF3_VECTOR_UINT, $arg);
            case self::AMFE_VECTOR_DOUBLE:
                return new Vector(Constants::AMF3_VECTOR_DOUBLE, $arg);
            case self::AMFE_VECTOR_OBJECT:
                return new Vector(Constants::AMF3_VECTOR_OBJECT, $arg);
            default:
                throw new Exception('invalid event in decode callback : ' . $event);
        }
    }
}
