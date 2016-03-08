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

use emilkm\tests\util\AccessPropertyTrait;
use emilkm\efxphp\Amf\Constants;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class AbstractInputTest extends \PHPUnit_Framework_TestCase
{
    use AccessPropertyTrait;

    protected $in;
    protected $request;

    protected function setUp()
    {
        $this->in = new BaseInput();
        $this->request = file_get_contents(__DIR__ . '/../../asset/request/pingCommandMessageWithPacketHeaders.amf');
    }

    /**
     *
     */
    public function testavmPlusDefaultIsFalse()
    {
        $this->assertEquals(false, $this->getPropertyValue($this->in, 'avmPlus'));
    }

    /**
     *
     */
    public function testuseInternalDateTypeDefaultIsTrue()
    {
        $this->assertEquals(true, $this->getPropertyValue($this->in, 'useInternalDateType'));
    }

    /**
     *
     */
    public function testsetUseInternalDateTypeToFalse()
    {
        $this->in->setUseInternalDateType(false);
        $this->assertEquals(false, $this->getPropertyValue($this->in, 'useInternalDateType'));
    }

    /**
     *
     */
    public function testuseInternalXmlTypeDefaultIsTrue()
    {
        $this->assertEquals(true, $this->getPropertyValue($this->in, 'useInternalXmlType'));
    }

    /**
     *
     */
    public function testsetUseInternalXmlTypeToFalse()
    {
        $this->in->setUseInternalXmlType(false);
        $this->assertEquals(false, $this->getPropertyValue($this->in, 'useInternalXmlType'));
    }

    /**
     *
     */
    public function testuseInternalXmlDocumentDefaultTypeIsTrue()
    {
        $this->assertEquals(true, $this->getPropertyValue($this->in, 'useInternalXmlDocumentType'));
    }

    /**
     *
     */
    public function testsetUseInternalXmlDocumentTypeToFalse()
    {
        $this->in->setUseInternalXmlDocumentType(false);
        $this->assertEquals(false, $this->getPropertyValue($this->in, 'useInternalXmlDocumentType'));
    }

    /**
     *
     */
    public function testsetDataSetsData()
    {
        $data = 'abc';
        $this->in->setData($data);
        $this->assertEquals($data, $this->getPropertyValue($this->in, 'data'));
    }

    /**
     *
     */
    public function testsetDataSetsLength()
    {
        $data = 'abc';
        $this->in->setData($data);
        $this->assertEquals(3, $this->getPropertyValue($this->in, 'length'));
    }

    /**
     *
     */
    public function testsetDataSetsPosition()
    {
        $data = 'abc';
        $this->in->setData($data);
        $this->assertEquals(0, $this->getPropertyValue($this->in, 'pos'));
    }

    /**
     * Read the avmPlus switch
     */
    public function testreadBytes()
    {
        $this->in->setData($this->request);
        $this->in->skipBytes(6);
        $value = $this->in->readBytes(6);
        $this->assertEquals('string', $value);

        $this->in->setData($this->request);
        $this->in->skipBytes(25);
        $value = $this->in->readBytes(6);
        $this->assertEquals('number', $value);
    }

    /**
     * Read the avmPlus switch
     */
    public function testreadByte()
    {
        $this->in->setData($this->request);
        $this->in->skipBytes(58);
        $value = $this->in->readByte();
        $this->assertEquals(Constants::AMF0_AMF3, $value);

        $this->in->setData($this->request);
        $this->in->skipBytes(82);
        $value = $this->in->readByte();
        $this->assertEquals(Constants::AMF0_AMF3, $value);
    }

    /**
     * Read the AMF packet headers mustUnderstand flags
     */
    public function testreadBoolean()
    {
        $this->in->setData($this->request);
        $this->in->skipBytes(12);
        $value = $this->in->readBoolean();
        $this->assertEquals(false, $value);

        $this->in->setData($this->request);
        $this->in->skipBytes(31);
        $value = $this->in->readBoolean();
        $this->assertEquals(true, $value);
    }

    /**
     * Read the AMF packet version
     */
    public function testreadShort()
    {
        $this->in->setData($this->request);
        $value = $this->in->readShort();
        $this->assertEquals(3, $value);
    }

    /**
     * Read the array length
     */
    public function testreadInt()
    {
        $this->in->setData($this->request);
        $this->in->skipBytes(177);
        $value = $this->in->readInt();
        $this->assertEquals(1, $value);
    }

    /**
     * Read number header value
     */
    public function testreadDouble()
    {
        $this->in->setData($this->request);
        $this->in->skipBytes(37);
        $value = $this->in->readDouble();
        $this->assertEquals(123, $value);
    }

    /**
     * Read the AMF packet headers names
     */
    public function testreadUtf()
    {
        $this->in->setData($this->request);
        $this->in->skipBytes(4);
        $value = $this->in->readUtf();
        $this->assertEquals('string', $value);

        $this->in->setData($this->request);
        $this->in->skipBytes(23);
        $value = $this->in->readUtf();
        $this->assertEquals('number', $value);
    }
}
