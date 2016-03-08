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
class AbstractOutputTest extends \PHPUnit_Framework_TestCase
{
    use AccessPropertyTrait;

    protected $out;
    protected $request;

    protected function setUp()
    {
        $this->out = new BaseOutput();
        $this->request = file_get_contents(__DIR__ . '/../../asset/request/pingCommandMessageWithPacketHeaders.amf');
    }

    /**
     *
     */
    public function testavmPlusDefaultIsFalse()
    {
        $this->assertEquals(false, $this->getPropertyValue($this->out, 'avmPlus'));
    }

    /**
     *
     */
    public function testsetAvmPlusToTrue()
    {
        $this->out->setAvmPlus(true);
        $this->assertEquals(true, $this->getPropertyValue($this->out, 'avmPlus'));
    }

    /**
     *
     */
    public function testamf3nsndArrayAsObjectDefaultIsFalse()
    {
        $this->assertEquals(false, $this->getPropertyValue($this->out, 'amf3nsndArrayAsObject'));
    }

    /**
     *
     */
    public function testsetAmf3nsndArrayAsObjectToTrue()
    {
        $this->out->encodeAmf3nsndArrayAsObject(true);
        $this->assertEquals(true, $this->getPropertyValue($this->out, 'amf3nsndArrayAsObject'));
    }

    /**
     *
     */
    public function testwriteByte()
    {
        $this->out->writeByte(10);
        $this->assertEquals("\n", $this->out->data);
    }

    /**
     *
     */
    public function testwriteBoolean()
    {
        $this->out->writeBoolean(false);
        $this->assertEquals("\x0", $this->out->data);
        $this->out->data = null;
        $this->out->writeBoolean(true);
        $this->assertEquals("\x1", $this->out->data);
    }

    /**
     *
     */
    public function testwriteShort()
    {
        $this->out->writeShort(3);
        $this->assertEquals("\x0\x3", $this->out->data);
    }

    /**
     *
     */
    public function testwriteInt()
    {
        $this->out->writeInt(1);
        $this->assertEquals("\x0\x0\x0\x1", $this->out->data);
    }

    /**
     *
     */
    public function testwriteDouble()
    {
        $this->out->writeDouble(31.57);
        //$this->assertEquals("@?‘ë…\x1E¸R", $this->out->data);
    }

    /**
     *
     */
    public function testwriteUtf()
    {
        $this->out->writeUtf('abc');
        //$this->assertEquals("\x0\x3abc", $this->out->data);
    }

    /**
     *
     */
    public function testwriteLongUtf()
    {

    }
}
