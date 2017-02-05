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

use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\Deserializer;
use emilkm\efxphp\Amf\Input;
use emilkm\efxphp\Amf\InputExt;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class DeserializerTest extends \PHPUnit_Framework_TestCase
{
    protected $deserializer;
    protected $deserializerExt;

    /**
     *
     */
    protected function setUp()
    {
        $this->deserializer = new Deserializer(new Input());
        $this->deserializerExt = new Deserializer(new InputExt());
    }

    /**
     *
     */
    public function testdeserializePingCommandMessageAmf0()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf0'));
        $message = $this->deserializer->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\CommandMessage', $message->bodies[0]->data);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializePingCommandMessageAmf0Ext()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf0'));
        $message = $this->deserializerExt->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\CommandMessage', $message->bodies[0]->data);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializePingCommandMessageAmf3()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf3'));
        $message = $this->deserializer->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\CommandMessage', $message->bodies[0]->data);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializePingCommandMessageAmf3Ext()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf3'));
        $message = $this->deserializerExt->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\CommandMessage', $message->bodies[0]->data);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializeRemotingMessageAmf0()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf0'));
        $message = $this->deserializer->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\RemotingMessage', $message->bodies[0]->data);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->clientId);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
        $this->assertEquals('SomeService', $message->bodies[0]->data->source);
        $this->assertEquals('theMethod', $message->bodies[0]->data->operation);
    }

    /**
     *
     */
    public function testdeserializeRemotingMessageAmf0Ext()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf0'));
        $message = $this->deserializerExt->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\RemotingMessage', $message->bodies[0]->data);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->clientId);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
        $this->assertEquals('SomeService', $message->bodies[0]->data->source);
        $this->assertEquals('theMethod', $message->bodies[0]->data->operation);
    }

    /**
     *
     */
    public function testdeserializeRemotingMessageAmf3()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf3'));
        $message = $this->deserializer->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\RemotingMessage', $message->bodies[0]->data);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->clientId);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
        $this->assertEquals('SomeService', $message->bodies[0]->data->source);
        $this->assertEquals('theMethod', $message->bodies[0]->data->operation);
    }

    /**
     *
     */
    public function testdeserializeRemotingMessageAmf3Ext()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf3'));
        $message = $this->deserializerExt->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\RemotingMessage', $message->bodies[0]->data);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->clientId);
        $this->assertEquals('F9F98C89-5099-E7BF-7997-9B41FC79A0D4', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->messageId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
        $this->assertEquals('SomeService', $message->bodies[0]->data->source);
        $this->assertEquals('theMethod', $message->bodies[0]->data->operation);
    }

    /**
     *
     */
    public function testdeserializeAcknowledgeMessageAmf0()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf0'));
        $message = $this->deserializer->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\AcknowledgeMessage', $message->bodies[0]->data);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->clientId);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('B1510529-D1A0-6A62-25DA-A00B1BF47BF6', $message->bodies[0]->data->messageId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->correlationId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializeAcknowledgeMessageAmf0Ext()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf0'));
        $message = $this->deserializerExt->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\AcknowledgeMessage', $message->bodies[0]->data);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->clientId);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('B1510529-D1A0-6A62-25DA-A00B1BF47BF6', $message->bodies[0]->data->messageId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->correlationId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializeAcknowledgeMessageAmf3()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf3'));
        $message = $this->deserializer->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\AcknowledgeMessage', $message->bodies[0]->data);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->clientId);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('B1510529-D1A0-6A62-25DA-A00B1BF47BF6', $message->bodies[0]->data->messageId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->correlationId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }

    /**
     *
     */
    public function testdeserializeAcknowledgeMessageAmf3Ext()
    {
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf3'));
        $message = $this->deserializerExt->readMessage($data);
        $this->assertInstanceOf('emilkm\efxphp\Amf\ActionMessage', $message);
        $this->assertInstanceOf('emilkm\efxphp\Amf\MessageBody', $message->bodies[0]);
        $this->assertInstanceOf('emilkm\efxphp\Amf\Messages\AcknowledgeMessage', $message->bodies[0]->data);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->clientId);
        $this->assertEquals('3182A13C-AF4A-A148-AA73-000028716D94', $message->bodies[0]->data->headers->DSId);
        $this->assertEquals('B1510529-D1A0-6A62-25DA-A00B1BF47BF6', $message->bodies[0]->data->messageId);
        $this->assertEquals('63FCE70D-F447-ED49-83E6-00001695D4AF', $message->bodies[0]->data->correlationId);
        $this->assertEquals('1437179933687', $message->bodies[0]->data->timestamp);
    }
}
