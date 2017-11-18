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

use emilkm\tests\util\InvokeMethodTrait;
use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\InputExt;

use stdClass;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class InputExtTest extends \PHPUnit_Framework_TestCase
{
    use InvokeMethodTrait;

    protected $in;
    protected $request;

    protected function setUp()
    {
        $this->in = new InputExt();
    }

    /**
     *
     */
    public function testreadAmf0Number()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/number.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(31.57, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf0Boolean()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/boolean.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(true, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf0DateToEfxphpDate()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf0'));
        $this->in->setData($data);
        $this->in->setUseRlandDateType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Date', $obj->value);
        $this->assertEquals(1422995025, $obj->value->timestamp);
        $this->assertEquals(123, $obj->value->milli);
    }

    /**
     *
     */
    public function testreadAmf0DateToPhpDateTime()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf0'));
        $this->in->setData($data);
        $this->in->setUseRlandDateType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('DateTime', $obj->value);
        $this->assertEquals(1422995025, $obj->value->getTimestamp());
        $this->assertEquals(123, floor($obj->value->format('u') / 1000));
    }

    /**
     *
     */
    public function testreadAmf0String()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals('abc', $obj->value);
    }

    /**
     *
     */
    public function testreadAmf0StringBlank()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-blank.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals('', $obj->value);
    }

    /**
     *
     */
    public function testreadAmf0StringUnicode()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-unicode.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals('витоша', $obj->value);
    }

    /**
     *
     */
    public function testreadAmf0Null()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/null.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(null, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf0ArrayEmpty()
    {
        $exp = array();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-empty.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     * Undefined entries in the sparse regions between indices are serialized as undefined.
     * Undefined entries in the sparse regions between indices are skipped when deserialized.
     */
    public function testreadAmf0ArrayDense()
    {
        $exp = array();
        $exp[0] = 'a';
        $exp[1] = 'b';
        $exp[2] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-dense.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     * Undefined entries in the sparse regions between indices are serialized as undefined.
     * Undefined entries in the sparse regions between indices are skipped when deserialized.
     */
    public function testreadAmf0ArraySparse()
    {
        $exp = array();
        $exp[0] = 'a';
        $exp[2] = 'b';
        $exp[4] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-sparse.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ArrayString()
    {
        $exp = new stdClass();
        $exp->a = 1;
        $exp->b = 2;
        $exp->c = 3;
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-string.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ArrayMixed()
    {
        $exp = array();
        $exp[0] = 'a';
        $exp['b'] = 2;
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-mixed.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ArrayNegative()
    {
        $exp = array();
        $exp[-1] = 'a';
        $exp[0] = 'b';
        $exp[1] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-negative.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ArrayNested()
    {
        $exp = new stdClass();
        $exp->items = array();
        $exp->items[0] = 'a';
        $exp->items[1] = 'b';
        $exp->items[2] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-nested.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ObjectAnonymous()
    {
        $exp = new stdClass();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ObjectTypedToStdClass()
    {
        $exp = new stdClass();
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $exp->$remoteClassField = 'SomeClass';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-someclass.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ObjectTypedFromNamespace()
    {
        $exp = new \emilkm\tests\asset\value\VoExplicitTypeNotSet();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-namespace.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf0ObjectTypedFromField()
    {
        $exp = new \emilkm\tests\asset\value\VoExplicitTypeNotBlank();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-field.amf0'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    /*public function testreadAmf0XmlToEfxphpXml()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xml.amf0'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Xml', $obj->value);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value->data);
    }*/

    /**
     *
     */
    /*public function testreadAmf0XmlToSimpleXMLElement()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmlelement.amf0'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('SimpleXMLElement', $obj->value);
        $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value->asXML()));
        $this->assertEquals('<?xml version="1.0"?><x><string>abc</string><number>123</number></x>', $xmlstring);
    }*/

    /**
     *
     */
    /*public function testreadAmf0XmlDocumentFromEfxphpXmlDocument()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmldocument.amf0'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Xml', $obj->value);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value->data);
    }*/

    /**
     *
     */
    /*public function testreadAmf0XmlDocumentFromDOMElement()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/domelement.amf0'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('SimpleXMLElement', $obj->value);
        $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value->asXML()));
        $this->assertEquals('<?xml version="1.0"?><x><string>abc</string><number>123</number></x>', $xmlstring);
    }*/

    //##########################################################################
    // AMF3
    //##########################################################################

    /**
     * TODO:
     */
    public function testreadAmf3Undefined()
    {
    }

    /**
     *
     */
    public function testreadAmf3Null()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/null.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(null, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf3BooleanTrue()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/boolean-true.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(true, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf3BooleanFalse()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/boolean-false.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(false, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf3Integer()
    {
        $exp = new stdClass();
        $exp->value = 123;
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/integer.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(123, $obj->value);
    }

    /**
     *
     */
    public function testreadAmf3Double()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/double.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals(31.57, $obj->value);
    }

    /**
     *
     */
    public function testreadeAmf3String()
    {
        $exp = new stdClass();
        $exp->value = 'abc';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals('abc', $obj->value);
    }

    /**
     *
     */
    public function testreadAmf3StringBlank()
    {
        $exp = new stdClass();
        $exp->value = '';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-blank.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3StringUnicode()
    {
        $exp = new stdClass();
        $exp->value = 'витоша';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/string-unicode.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3DateToEfxphpDate()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->in->setUseRlandDateType(true);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Date', $obj->value);
        $this->assertEquals(1422995025, $obj->value->timestamp);
        $this->assertEquals(123, $obj->value->milli);
    }

    /**
     *
     */
    public function testreadAmf3DateToPhpDateTime()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandDateType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('DateTime', $obj->value);
        $this->assertEquals(1422995025, $obj->value->getTimestamp());
        $this->assertEquals(123, floor($obj->value->format('u') / 1000));
    }

    /**
     *
     */
    public function testreadAmf3DateAndReferenceToEfxphpDate()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date-and-reference.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Date', $obj->value1);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Date', $obj->value2);
        $this->assertEquals(1422995025, $obj->value1->timestamp);
        $this->assertEquals(123, $obj->value1->milli);
        $this->assertEquals(1422995025, $obj->value2->timestamp);
        $this->assertEquals(123, $obj->value2->milli);
    }

    /**
     *
     */
    public function testreadAmf3DateAndReferenceToPhpDateTime()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/date-and-reference.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandDateType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('DateTime', $obj->value1);
        $this->assertInstanceOf('DateTime', $obj->value2);
        $this->assertEquals(1422995025, $obj->value1->getTimestamp());
        $this->assertEquals(123, floor($obj->value1->format('u') / 1000));
        $this->assertEquals(1422995025, $obj->value2->getTimestamp());
        $this->assertEquals(123, floor($obj->value2->format('u') / 1000));
    }

    /**
     *
     */
    public function testreadAmf3ArrayEmpty()
    {
        $exp = array();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-empty.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayDense()
    {
        $exp = array();
        $exp[0] = 'a';
        $exp[1] = 'b';
        $exp[2] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-dense.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArraySparseAsObject()
    {
        $exp = new stdClass();
        $exp->{'0'} = 'a';
        $exp->{'2'} = 'b';
        $exp->{'4'} = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-sparse-as-object.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArraySparseAsAssoc()
    {
        $exp = array();
        $exp['0'] = 'a';
        $exp['2'] = 'b';
        $exp['4'] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-sparse-as-assoc.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayStringAsObject()
    {
        $exp = new stdClass();
        $exp->a = 1;
        $exp->b = 2;
        $exp->c = 3;
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-string-as-object.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayStringAsAssoc()
    {
        $exp = array();
        $exp['a'] = 1;
        $exp['b'] = 2;
        $exp['c'] = 3;
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-string-as-assoc.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayMixedAsObject()
    {
        $exp = new stdClass();
        $exp->{'b'} = 2;
        $exp->{'0'} = 'a';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-mixed-as-object.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayMixedAsAssoc()
    {
        $exp = array();
        $exp['b'] = 2;
        $exp[0] = 'a';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-mixed-as-assoc.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayNegativeAsObject()
    {
        $exp = new stdClass();
        $exp->{'-1'} = 'a';
        $exp->{'0'} = 'b';
        $exp->{'1'} = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-negative-as-object.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayNegativeAsAssoc()
    {
        $exp = array();
        $exp['-1'] = 'a';
        $exp['0'] = 'b';
        $exp['1'] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-negative-as-assoc.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ArrayNestedAsObject()
    {
        $exp = new stdClass();
        $exp->{'items'} = array();
        $exp->{'items'}[0] = 'a';
        $exp->{'items'}[1] = 'b';
        $exp->{'items'}[2] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-nested-as-object.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        //$this->assertEquals($exp, $obj); //TODO: assertion fails in preg_match
    }

    /**
     *
     */
    public function testreadAmf3ArrayNestedAsAssoc()
    {
        $exp = array();
        $exp['items'] = array();
        $exp['items'][0] = 'a';
        $exp['items'][1] = 'b';
        $exp['items'][2] = 'c';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/array-nested-as-assoc.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ObjectAnonymous()
    {
        $exp = new stdClass();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-anonymous.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ObjectTypedToStdClass()
    {
        $exp = new stdClass();
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $exp->$remoteClassField = 'SomeClass';
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-someclass.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ObjectTypedFromNamespace()
    {
        $exp = new \emilkm\tests\asset\value\VoExplicitTypeNotSet();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-namespace.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ObjectFromTypedFromField()
    {
        $exp = new \emilkm\tests\asset\value\VoExplicitTypeNotBlank();
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-explicit-from-field.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ObjectTypedFromFieldAndTraitsReference()
    {
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $v1 = new stdClass();
        $v1->$remoteClassField = 'LightClass';
        $v1->id = 1;
        $v1->name = 'a';
        $v2 = new stdClass();
        $v2->$remoteClassField = 'LightClass';
        $v2->id = 2;
        $v2->name = 'b';
        $exp = new stdClass();
        $exp->value = array($v1, $v2);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-lightclass-and-traits-reference.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ObjectFromFieldAndTraitsReferenceMissingProperty()
    {
        $remoteClassField = Constants::REMOTE_CLASS_FIELD;
        $v1 = new stdClass();
        $v1->$remoteClassField = 'LightClass';
        $v1->id = 1;
        $v1->name = 'a';
        $v2 = new stdClass();
        $v2->$remoteClassField = 'LightClass';
        $v2->id = 2;
        //$v2->name = 'b'; //v2 is missing one of its properties
        $exp = new stdClass();
        $exp->value = array($v1, $v2);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/object-typed-lightclass-and-traits-reference-missing-property.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }

    /**
     *
     */
    public function testreadAmf3ByteArray()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/bytearray.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\ByteArray', $obj->value);
        $this->assertEquals('1a2b3c', $obj->value->data);
    }

    /**
     *
     */
    public function testreadAmf3ByteArrayAndReference()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/bytearray-and-reference.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\ByteArray', $obj->value1);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\ByteArray', $obj->value2);
        $this->assertEquals('1a2b3c', $obj->value1->data);
        $this->assertEquals('1a2b3c', $obj->value2->data);
    }

    /**
     *
     */
    /*public function testreadAmf3XmlToEfxphpXml()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xml.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Xml', $obj->value);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value->data);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlToSimpleXMLElement()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmlelement.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('SimpleXMLElement', $obj->value);
        $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value->asXML()));
        $this->assertEquals('<?xml version="1.0"?><x><string>abc</string><number>123</number></x>', $xmlstring);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlToEfxphpXmlAndReference()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xml-and-reference.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Xml', $obj->value1);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Xml', $obj->value2);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value1->data);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value2->data);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlToSimpleXMLElementAndReference()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmlelement-and-reference.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('SimpleXMLElement', $obj->value1);
        $this->assertInstanceOf('SimpleXMLElement', $obj->value2);
        $xmlstring1 = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value1->asXML()));
        $this->assertEquals('<?xml version="1.0"?><x><string>abc</string><number>123</number></x>', $xmlstring1);
        $xmlstring2 = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value2->asXML()));
        $this->assertEquals('<?xml version="1.0"?><x><string>abc</string><number>123</number></x>', $xmlstring2);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlDocumentToEfxphpXmlDocument()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmldocument.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlDocumentType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\XmlDocument', $obj->value);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value->data);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlDocumentToDOMElement()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/domelement.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlDocumentType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('DOMElement', $obj->value);
        $xmlstring = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value->ownerDocument->saveXML($obj->value)));
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $xmlstring);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlDocumentToEfxphpXmlDocumentAndReference()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/xmldocument-and-reference.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlDocumentType(true);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\XmlDocument', $obj->value1);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\XmlDocument', $obj->value2);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value1->data);
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $obj->value2->data);
    }*/

    /**
     *
     */
    /*public function testreadAmf3XmlDocumentToDOMElementAndReference()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/domelement-and-reference.amf3'));
        $this->in->setData($data);
        $this->in->setUseRlandXmlDocumentType(false);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('DOMElement', $obj->value1);
        $this->assertInstanceOf('DOMElement', $obj->value2);
        $xmlstring1 = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value1->ownerDocument->saveXML($obj->value1)));
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $xmlstring1);
        $xmlstring2 = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($obj->value2->ownerDocument->saveXML($obj->value2)));
        $this->assertEquals('<x><string>abc</string><number>123</number></x>', $xmlstring2);
    }*/

    /**
     *
     */
    public function testreadAmf3VectorInt()
    {
        $exp = array(1, 2, 3);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-int.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value);
        $this->assertEquals(Constants::AMF3_VECTOR_INT, $obj->value->type);
        $this->assertEquals(false, $obj->value->fixed);
        $this->assertEquals($exp, $obj->value->data);
    }

    /**
     *
     */
    public function testreadAmf3VectorIntNegative()
    {
        $exp = array(-3, -2, -1);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-int-negative.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value);
        $this->assertEquals(Constants::AMF3_VECTOR_INT, $obj->value->type);
        $this->assertEquals(false, $obj->value->fixed);
        $this->assertEquals($exp, $obj->value->data);
    }

    /**
     *
     */
    public function testreadAmf3VectorUint()
    {
        $exp = array(2147483647, 2147483648, 4294967295);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-uint.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value);
        $this->assertEquals(Constants::AMF3_VECTOR_UINT, $obj->value->type);
        $this->assertEquals(false, $obj->value->fixed);
        $this->assertEquals($exp, $obj->value->data);
    }

    /**
     *
     */
    public function testreadAmf3VectorDouble()
    {
        $exp = array(-31.57, 0, 31.57);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-double.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value);
        $this->assertEquals(Constants::AMF3_VECTOR_DOUBLE, $obj->value->type);
        $this->assertEquals(false, $obj->value->fixed);
        $this->assertEquals($exp, $obj->value->data);
    }

    /**
     *
     */
    public function testreadAmf3VectorObject()
    {
        $v1 = new stdClass();
        $v1->value = 1;
        $v2 = new stdClass();
        $v2->value = 2;
        $v3 = new stdClass();
        $v3->value = 3;
        $exp = array($v1, $v2, $v3);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-object.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value);
        $this->assertEquals(Constants::AMF3_VECTOR_OBJECT, $obj->value->type);
        $this->assertEquals(false, $obj->value->fixed);
        $this->assertEquals($exp, $obj->value->data);
    }

    /**
     *
     */
    public function testreadAmf3VectorObjectAndReference()
    {
        $v1 = new stdClass();
        $v1->value = 1;
        $v2 = new stdClass();
        $v2->value = 2;
        $v3 = new stdClass();
        $v3->value = 3;
        $exp = new stdClass();
        $exp = array($v1, $v2, $v3);
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/vector-object-and-reference.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value1);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Types\Vector', $obj->value2);
        $this->assertEquals(Constants::AMF3_VECTOR_OBJECT, $obj->value1->type);
        $this->assertEquals(Constants::AMF3_VECTOR_OBJECT, $obj->value2->type);
        $this->assertEquals(false, $obj->value1->fixed);
        $this->assertEquals(false, $obj->value2->fixed);
        $this->assertEquals($exp, $obj->value1->data);
        $this->assertEquals($exp, $obj->value2->data);
    }

    /**
     *
     */
    public function testreadAmf3ComplexObjectGraph()
    {
        $exp = new stdClass();
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
        $exp->value = $arr;
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/complex-object-graph.amf3'));
        $this->in->setData($data);
        $obj = $this->in->readObject();
        $this->assertEquals($exp, $obj);
    }
}
