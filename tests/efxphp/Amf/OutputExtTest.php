<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\tests\efxphp\Amf;

use emilkm\tests\util\SendToJamfTrait;
use emilkm\tests\util\InvokeMethodTrait;
use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\OutputExt;
use emilkm\efxphp\Amf\Types\Date;
use emilkm\efxphp\Amf\Types\ByteArray;
use emilkm\efxphp\Amf\Types\Vector;
use emilkm\efxphp\Amf\Types\Xml;
use emilkm\efxphp\Amf\Types\XmlDocument;

use stdClass;
use DateTime;
use DateTimeZone;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class OutputExtTest extends \PHPUnit_Framework_TestCase
{
    use SendToJamfTrait;
    use InvokeMethodTrait;

    protected $out;

    protected function setUp()
    {
        $this->out = new OutputExt();
    }

    /**
     *
     */
    public function testwriteAmf0Number()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = 31.57;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/number.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/number.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0Boolean()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = true;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/boolean.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/boolean.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0DateFromEfxphpDate()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = new Date(1422995025123); //2015-02-04 09:23:45.123
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/date.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0DateFromPhpDateTime()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $datestr = date('Y-m-d H:i:s.', 1422995025) . 123; //2015-02-04 09:23:45.123
        $obj->value = new DateTime($datestr);
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/date.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0String()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = 'abc';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/string.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0StringBlank()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = '';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/string-blank.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-blank.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0StringUnicode()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = 'витоша';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/string-unicode.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-unicode.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0Null()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = null;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/null.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/null.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ArrayEmpty()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-empty.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-empty.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ArrayDense()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $obj[0] = 'a';
        $obj[1] = 'b';
        $obj[2] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-dense.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-dense.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * Undefined entries in the sparse regions between indices are serialized as undefined.
     * Undefined entries in the sparse regions between indices are skipped when deserialized.
     */
    public function testwriteAmf0ArraySparse()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $obj[0] = 'a';
        $obj[2] = 'b';
        $obj[4] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-sparse.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-sparse.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ArrayString()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $obj['a'] = 1;
        $obj['b'] = 2;
        $obj['c'] = 3;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-string.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-string.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ArrayMixed()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $obj[0] = 'a';
        $obj['b'] = 2;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-mixed.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-mixed.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ArrayNegative()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $obj[-1] = 'a';
        $obj[0] = 'b';
        $obj[1] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-negative.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-negative.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ArrayNested()
    {
        $this->out->setAvmPlus(false);
        $obj = array();
        $obj['items'] = array();
        $obj['items'][0] = 'a';
        $obj['items'][1] = 'b';
        $obj['items'][2] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-nested.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-nested.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * The pure anonymous case.
     */
    public function testwriteAmf0ObjectAnonymous()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-anonymous.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * Anonymous object with blank _explicitType field, do not write the _explicitType field.
     */
    public function testwriteAmf0ObjectAnonymousFromBlankExplicitType()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $obj->$remoteClassField = '';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-anonymous.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf0'));
        $this->assertEquals($data, $this->out->data);
    }
    
    /**
     * Typed object with blank _explicitType field, do not write the _explicitType field.
     * There is a conflict between the object type and the _explicitType. Since _explicitType takes precedence,
     * output an anonymous object.
     */
    public function testwriteAmf0ObjectAnonymousFromTypeConflict()
    {
        $this->out->setAvmPlus(false);
        $obj = new \emilkm\tests\asset\value\VoExplicitTypeBlank();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-anonymous.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * When the type cannot be resolved, simply write stdClass and set the _explicitType field.
     * Do not write an anonymous object, because the remote client may be able to resolve it.
     */
    public function testwriteAmf0ObjectTypedNotResolvableToStdClass()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $obj->$remoteClassField = 'SomeClass';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-someclass.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-someclass.amf0'));
        $this->assertEquals($data, $this->out->data);
    }
    
    

    /**
     *
     */
    public function testwriteAmf0ObjectTypedFromNamespace()
    {
        $this->out->setAvmPlus(false);
        $obj = new \emilkm\tests\asset\value\VoExplicitTypeNotSet();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-explicit-from-namespace.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-namespace.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0ObjectTypedFromField()
    {
        $this->out->setAvmPlus(false);
        $obj = new \emilkm\tests\asset\value\VoExplicitTypeNotBlank();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-explicit-from-field.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-field.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0XmlFromEfxphpXml()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = new Xml('<x><string>abc</string><number>123</number></x>');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xml.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xml.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0XmlFromSimpleXMLElement()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = simplexml_load_string('<x><string>abc</string><number>123</number></x>');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xmlelement.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmlelement.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0XmlDocumentFromEfxphpXmlDocument()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = new XmlDocument('<x><string>abc</string><number>123</number></x>');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xmldocument.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmldocument.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf0XmlDocumentFromDOMElement()
    {
        $this->out->setAvmPlus(false);
        $obj = new stdClass();
        $obj->value = dom_import_simplexml(simplexml_load_string('<x><string>abc</string><number>123</number></x>'));
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/domelement.amf0', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/domelement.amf0'));
        $this->assertEquals($data, $this->out->data);
    }

    //##########################################################################
    // AMF3
    //##########################################################################

    /**
     * TODO:
     */
    public function testwriteAmf3Undefined()
    {
    }

    /**
     *
     */
    public function testwriteAmf3Null()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = null;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/null.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/null.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3BooleanTrue()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = true;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/boolean-true.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/boolean-true.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3BooleanFalse()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = false;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/boolean-false.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/boolean-false.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3Integer()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = 123;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/integer.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/integer.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3Double()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = 31.57;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/double.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/double.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3String()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = 'abc';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/string.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3StringBlank()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = '';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/string-blank.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-blank.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3StringUnicode()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = 'витоша';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/string-unicode.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-unicode.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3DateFromEfxphpDate()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = new Date(1422995025123); //2015-02-04 09:23:45.123
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/date.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3DateFromPhpDateTime()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $datestr = date('Y-m-d H:i:s.', 1422995025) . 123; //2015-02-04 09:23:45.123
        $obj->value = new DateTime($datestr);
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/date.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3DateAndReferenceFromEfxphpDate()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $adate = new Date(1422995025123); //2015-02-04 09:23:45.123
        $obj->value1 = $adate;
        $obj->value2 = $adate;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/date-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3DateAndReferenceFromPhpDateTime()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $datestr = date('Y-m-d H:i:s.', 1422995025) . 123; //2015-02-04 09:23:45.123
        $adate = new DateTime($datestr);
        $obj->value1 = $adate;
        $obj->value2 = $adate;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/date-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayEmpty()
    {
        $this->out->setAvmPlus(true);
        $obj = array();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-empty.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-empty.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayDense()
    {
        $this->out->setAvmPlus(true);
        $obj = array();
        $obj[0] = 'a';
        $obj[1] = 'b';
        $obj[2] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-dense.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-dense.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArraySparseAsObject()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(true);
        $obj = array();
        $obj[0] = 'a';
        $obj[2] = 'b';
        $obj[4] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-sparse-as-object.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-sparse-as-object.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArraySparseAsAssoc()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(false);
        $obj = array();
        $obj[0] = 'a';
        $obj[2] = 'b';
        $obj[4] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-sparse-as-assoc.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-sparse-as-assoc.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayStringAsObject()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(true);
        $obj = array();
        $obj['a'] = 1;
        $obj['b'] = 2;
        $obj['c'] = 3;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-string-as-object.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-string-as-object.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayStringAsAssoc()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(false);
        $obj = array();
        $obj['a'] = 1;
        $obj['b'] = 2;
        $obj['c'] = 3;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-string-as-assoc.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-string-as-assoc.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayMixedAsObject()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(true);
        $obj = array();
        $obj[0] = 'a';
        $obj['b'] = 2;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-mixed-as-object.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-mixed-as-object.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayMixedAsAssoc()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(false);
        $obj = array();
        $obj[0] = 'a';
        $obj['b'] = 2;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-mixed-as-assoc.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-mixed-as-assoc.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayNegativeAsObject()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(true);
        $obj = array();
        $obj[-1] = 'a';
        $obj[0] = 'b';
        $obj[1] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-negative-as-object.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-negative-as-object.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayNegativeAsAssoc()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(false);
        $obj = array();
        $obj[-1] = 'a';
        $obj[0] = 'b';
        $obj[1] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-negative-as-assoc.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-negative-as-assoc.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayNestedAsObject()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(true);
        $obj = array();
        $obj['items'] = array();
        $obj['items'][0] = 'a';
        $obj['items'][1] = 'b';
        $obj['items'][2] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-nested-as-object.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-nested-as-object.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ArrayNestedAsAssoc()
    {
        $this->out->setAvmPlus(true);
        $this->out->encodeAmf3nsndArrayAsObject(false);
        $obj = array();
        $obj['items'] = array();
        $obj['items'][0] = 'a';
        $obj['items'][1] = 'b';
        $obj['items'][2] = 'c';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/array-nested-as-assoc.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-nested-as-assoc.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * The pure anonymous case.
     */
    public function testwriteAmf3Anonymous()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-anonymous.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * Anonymous object with blank _explicitType field, do not write the _explicitType field.
     */
    public function testwriteAmf3ObjectAnonymousFromBlankExplicitType()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $obj->$remoteClassField = '';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-anonymous.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf3'));
        $this->assertEquals($data, $this->out->data);
    }
    
    /**
     * Typed object with blank _explicitType field, do not write the _explicitType field.
     * There is a conflict between the object type and the _explicitType. Since _explicitType takes precedence,
     * output an anonymous object.
     */
    public function testwriteAmf3ObjectAnonymousFromTypeConflict()
    {
        $this->out->setAvmPlus(true);
        $obj = new \emilkm\tests\asset\value\VoExplicitTypeBlank();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-anonymous.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * When the type cannot be resolved, simply write stdClass and set the _explicitType field.
     * Do not write an anonymous object, because the remote client may be able to resolve it.
     */
    public function testwriteAmf3TypedToStdClass()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $obj->$remoteClassField = 'SomeClass';
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-someclass.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-someclass.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     * Type comes from the namespace. Do not write _explicitType, it is not needed.
     */
    public function testwriteAmf3ObjectTypedFromNamespace()
    {
        $this->out->setAvmPlus(true);
        $obj = new \emilkm\tests\asset\value\VoExplicitTypeNotSet();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-explicit-from-namespace.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-namespace.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    

    /**
     *
     */
    public function testwriteAmf3ObjectTypedFromField()
    {
        $this->out->setAvmPlus(true);
        $obj = new \emilkm\tests\asset\value\VoExplicitTypeNotBlank();
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-explicit-from-field.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-field.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ObjectTypedFromFieldAndTraitsReference()
    {
        $this->out->setAvmPlus(true);
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $v1 = new stdClass();
        $v1->$remoteClassField = 'LightClass';
        $v1->id = 1;
        $v1->name = 'a';
        $v2 = new stdClass();
        $v2->$remoteClassField = 'LightClass';
        $v2->id = 2;
        $v2->name = 'b';
        $obj = new stdClass();
        $obj->value = array($v1, $v2);
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-lightclass-and-traits-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-lightclass-and-traits-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ObjectTypedFromFieldAndTraitsReferenceMissingProperty()
    {
        $this->out->setAvmPlus(true);
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $v1 = new stdClass();
        $v1->$remoteClassField = 'LightClass';
        $v1->id = 1;
        $v1->name = 'a';
        $v2 = new stdClass();
        $v2->$remoteClassField = 'LightClass';
        $v2->id = 2;
        //$v2->name = 'b'; //v2 is missing one of its properties
        $obj = new stdClass();
        $obj->value = array($v1, $v2);
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/object-typed-lightclass-and-traits-reference-missing-property.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-lightclass-and-traits-reference-missing-property.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ByteArray()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = new ByteArray('1a2b3c');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/bytearray.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/bytearray.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ByteArrayAndReference()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $bytearr = new ByteArray('1a2b3c');
        $obj->value1 = $bytearr;
        $obj->value2 = $bytearr;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/bytearray-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/bytearray-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlFromEfxphpXml()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = new Xml('<x><string>abc</string><number>123</number></x>');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xml.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xml.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlFromSimpleXMLElement()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = simplexml_load_string('<x><string>abc</string><number>123</number></x>');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xmlelement.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmlelement.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlFromEfxphpXmlAndReference()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $xmlobj = new Xml('<x><string>abc</string><number>123</number></x>');
        $obj->value1 = $xmlobj;
        $obj->value2 = $xmlobj;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xml-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xml-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlFromSimpleXMLElementAndReference()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $xmlobj = simplexml_load_string('<x><string>abc</string><number>123</number></x>');
        $obj->value1 = $xmlobj;
        $obj->value2 = $xmlobj;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xmlelement-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmlelement-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlDocumentFromEfxphpXmlDocument()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = new XmlDocument('<x><string>abc</string><number>123</number></x>');
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xmldocument.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmldocument.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlDocumentFromDOMElement()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $obj->value = dom_import_simplexml(simplexml_load_string('<x><string>abc</string><number>123</number></x>'));
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/domelement.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/domelement.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlDocumentFromEfxphpXmlDocumentAndReference()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $xmlobj = new XmlDocument('<x><string>abc</string><number>123</number></x>');
        $obj->value1 = $xmlobj;
        $obj->value2 = $xmlobj;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/xmldocument-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmldocument-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3XmlDocumentFromDOMElementAndReference()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $xmlobj = dom_import_simplexml(simplexml_load_string('<x><string>abc</string><number>123</number></x>'));
        $obj->value1 = $xmlobj;
        $obj->value2 = $xmlobj;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/domelement-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/domelement-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3VectorInt()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $vector = new Vector(Constants::AMF3_VECTOR_INT, array(1, 2, 3));
        $obj->value = $vector;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/vector-int.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-int.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3VectorIntNegative()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $vector = new Vector(Constants::AMF3_VECTOR_INT, array(-3, -2, -1));
        $obj->value = $vector;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/vector-int-negative.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-int-negative.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3VectorUint()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $vector = new Vector(Constants::AMF3_VECTOR_UINT, array(2147483647, 2147483648, 4294967295));
        $obj->value = $vector;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/vector-uint.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-uint.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3VectorDouble()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $vector = new Vector(Constants::AMF3_VECTOR_DOUBLE, array(-31.57, 0, 31.57));
        $obj->value = $vector;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/vector-double.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-double.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3VectorObject()
    {
        $this->out->setAvmPlus(true);
        $v1 = new stdClass();
        $v1->value = 1;
        $v2 = new stdClass();
        $v2->value = 2;
        $v3 = new stdClass();
        $v3->value = 3;
        $obj = new stdClass();
        $vector = new Vector(Constants::AMF3_VECTOR_OBJECT, array($v1, $v2, $v3));
        $obj->value = $vector;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/vector-object.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-object.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3VectorObjectAndReference()
    {
        $this->out->setAvmPlus(true);
        $v1 = new stdClass();
        $v1->value = 1;
        $v2 = new stdClass();
        $v2->value = 2;
        $v3 = new stdClass();
        $v3->value = 3;
        $obj = new stdClass();
        $vector = new Vector(Constants::AMF3_VECTOR_OBJECT, array($v1, $v2, $v3));
        $obj->value1 = $vector;
        $obj->value2 = $vector;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/vector-object-and-reference.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-object-and-reference.amf3'));
        $this->assertEquals($data, $this->out->data);
    }

    /**
     *
     */
    public function testwriteAmf3ComplexObjectGraph()
    {
        $this->out->setAvmPlus(true);
        $obj = new stdClass();
        $arr = array();
        $a = new stdClass();
        $a->name = 'a';
        $a->parent = null;
        $a->children = array();
        $arr[] = $a;
        $a1 = new stdClass();
        $a1->name = 'a1';
        $a1->parent = $a;
        $a1->children = null;
        $a->children[] = $a1;
        $b = new stdClass();
        $b->name = 'b';
        $b->parent = null;
        $b->children = array();
        $arr[] = $b;
        $b1 = new stdClass();
        $b1->name = 'b1';
        $b1->parent = $b;
        $b1->children = array();
        $b->children[] = $b1;
        $bb1 = new stdClass();
        $bb1->name = 'bb1';
        $bb1->parent = $b1;
        $bb1->children = array();
        $b1->children[] = $bb1;
        $obj->value = $arr;
        $this->out->writeObject($obj);
        //$this->sendToJamf($this->out->data);
        //file_put_contents(__DIR__ . '/../asset/value/complex-object-graph.amf3', serialize($this->out->data));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/complex-object-graph.amf3'));
        $this->assertEquals($data, $this->out->data);
    }
}
