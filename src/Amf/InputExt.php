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
    const AMFC_VECTOR = 4;
    const AMFC_EXTERNALIZABLE = 5;

	private $userlandTypes = [
        'emilkm\\efxphp\\Amf\\Types\\Date' => self::AMFC_DATE,
        'emilkm\\efxphp\\Amf\\Types\\ByteArray' => self::AMFC_BYTEARRAY,
        'emilkm\\efxphp\\Amf\\Types\\Xml' => self::AMFC_XML,
        'emilkm\\efxphp\\Amf\\Types\\XmlDocument' => self::AMFC_XMLDOCUMENT,
        'emilkm\\efxphp\\Amf\\Types\\Vector' => self::AMFC_VECTOR
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
            | ($this->decodeAmfObjectAsArray ? self::AMF_OBJECT_AS_ASSOC : 0)
            | ($this->useRlandDateType ? self::AMF_USE_RLAND_DATE : 0)
        );
        $data = amf_decode($this->data, $this->pos, $this->decodeFlags, $this->userlandTypes);

        return $data;
    }
}
