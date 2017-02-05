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
use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\Serializer;
use emilkm\efxphp\Amf\Output;
use emilkm\efxphp\Amf\OutputExt;
use emilkm\efxphp\Amf\ActionMessage;
use emilkm\efxphp\Amf\MessageBody;
use emilkm\efxphp\Amf\Messages\AcknowledgeMessage;
use emilkm\efxphp\Amf\Messages\CommandMessage;
use emilkm\efxphp\Amf\Messages\RemotingMessage;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    use SendToJamfTrait;

    protected $serializer;
    protected $serializerExt;

    /**
     *
     */
    protected function setUp()
    {
        $this->serializer = new Serializer(new Output());
        $this->serializerExt = new Serializer(new OutputExt());
    }
    
    /**
     *
     */
    public function testserializePingCommandMessageAmf0()
    {
        $actionMessage = new ActionMessage(0);
        $messageBody = new MessageBody(null, '/1');
        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $messageBody->data = $commandMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializer->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/ping-command.amf0', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf0'));
        $this->assertEquals($data, $mesg);
    }
    
    /**
     *
     */
    public function testserializePingCommandMessageAmf0Ext()
    {
        $actionMessage = new ActionMessage(0);
        $messageBody = new MessageBody(null, '/1');
        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $messageBody->data = $commandMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializerExt->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/ping-command.amf0', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf0'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializePingCommandMessageAmf3()
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody(null, '/1');
        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $messageBody->data = $commandMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializer->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/ping-command.amf3', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf3'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializePingCommandMessageAmf3Ext()
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody(null, '/1');
        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $messageBody->data = $commandMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializerExt->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/ping-command.amf3', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/ping-command.amf3'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeRemotingMessageAmf0()
    {
        $actionMessage = new ActionMessage(0);
        $messageBody = new MessageBody(null, '/1');
        $remotingMessage = new RemotingMessage(
            'F9F98C89-5099-E7BF-7997-9B41FC79A0D4',
            'efxphp',
            'SomeService',
            'theMethod',
            null
        );

        //reset properties manually so we can compare output
        $remotingMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $remotingMessage->timestamp = round(1437179933687);

        $messageBody->data = $remotingMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializer->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/some-service-remoting-message.amf0', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf0'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeRemotingMessageAmf0Ext()
    {
        $actionMessage = new ActionMessage(0);
        $messageBody = new MessageBody(null, '/1');
        $remotingMessage = new RemotingMessage(
            'F9F98C89-5099-E7BF-7997-9B41FC79A0D4',
            'efxphp',
            'SomeService',
            'theMethod',
            null
        );

        //reset properties manually so we can compare output
        $remotingMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $remotingMessage->timestamp = round(1437179933687);

        $messageBody->data = $remotingMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializerExt->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/some-service-remoting-message.amf0', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf0'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeRemotingMessageAmf3()
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody(null, '/1');
        $remotingMessage = new RemotingMessage(
            'F9F98C89-5099-E7BF-7997-9B41FC79A0D4',
            'efxphp',
            'SomeService',
            'theMethod',
            null
        );

        //reset properties manually so we can compare output
        $remotingMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $remotingMessage->timestamp = round(1437179933687);

        $messageBody->data = $remotingMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializer->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/some-service-remoting-message.amf3', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf3'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeRemotingMessageAmf3Ext()
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody(null, '/1');
        $remotingMessage = new RemotingMessage(
            'F9F98C89-5099-E7BF-7997-9B41FC79A0D4',
            'efxphp',
            'SomeService',
            'theMethod',
            null
        );

        //reset properties manually so we can compare output
        $remotingMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $remotingMessage->timestamp = round(1437179933687);

        $messageBody->data = $remotingMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializerExt->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/some-service-remoting-message.amf3', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-remoting-message.amf3'));
        $this->assertEquals($data, $mesg);
    }
//"\x0\x0\x0\x0\x0\x1\x0\xB/1/onResult\x0\x0\x0\x0\x1H\x10\x0*flex.messaging.messages.AcknowledgeMessage\x0\rcorrelationId\x2\x0\$63FCE70D-F447-ED49-83E6-00001695D4AF\x0\tmessageId\x2\x0\$B1510529-D1A0-6A62-25DA-A00B1BF47BF6\x0\x8clientId\x2\x0\$3182A13C-AF4A-A148-AA73-000028716D94\x0\ttimestamp\x2\x0\r1437179933789\x0\ntimeToLive\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\xBdestination\x5\x0\x7headers\x3\x0\x4DSId\x2\x0\$3182A13C-AF4A-A148-AA73-000028716D94\x0\x0\t\x0\x4body\x5\x0\x0\t"
//"\x0\x0\x0\x0\x0\x1\x0\xB/1/onResult\x0\x0\x0\x0\x1H\x10\x0*flex.messaging.messages.AcknowledgeMessage\x0\rcorrelationId\x2\x0\$63FCE70D-F447-ED49-83E6-00001695D4AF\x0\tmessageId\x2\x0\$B1510529-D1A0-6A62-25DA-A00B1BF47BF6\x0\x8clientId\x2\x0\$3182A13C-AF4A-A148-AA73-000028716D94\x0\ttimestamp\x2\x0\r1437179933687\x0\ntimeToLive\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\xBdestination\x5\x0\x7headers\x3\x0\x4DSId\x2\x0\$3182A13C-AF4A-A148-AA73-000028716D94\x0\x0\t\x0\x4body\x5\x0\x0\t"
    /**
     *
     */
    public function testserializeAcknowledgeMessageAmf0()
    {
        $actionMessage = new ActionMessage(0);
        $messageBody = new MessageBody('/1' . Constants::RESULT_METHOD);

        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $acknowledgeMessage = new AcknowledgeMessage($commandMessage);

        //reset properties manually so we can compare output
        $acknowledgeMessage->clientId       = '3182A13C-AF4A-A148-AA73-000028716D94';
        $acknowledgeMessage->messageId      = 'B1510529-D1A0-6A62-25DA-A00B1BF47BF6';
        $acknowledgeMessage->correlationId  = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $acknowledgeMessage->timestamp      = round(1437179933687);
        $acknowledgeMessage->headers        = (object) array('DSId' => $acknowledgeMessage->clientId);

        $messageBody->data = $acknowledgeMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializer->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf0', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf0'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeAcknowledgeMessageAmf0Ext()
    {
        $actionMessage = new ActionMessage(0);
        $messageBody = new MessageBody('/1' . Constants::RESULT_METHOD);

        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $acknowledgeMessage = new AcknowledgeMessage($commandMessage);

        //reset properties manually so we can compare output
        $acknowledgeMessage->clientId       = '3182A13C-AF4A-A148-AA73-000028716D94';
        $acknowledgeMessage->messageId      = 'B1510529-D1A0-6A62-25DA-A00B1BF47BF6';
        $acknowledgeMessage->correlationId  = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $acknowledgeMessage->timestamp      = round(1437179933687);
        $acknowledgeMessage->headers        = (object) array('DSId' => $acknowledgeMessage->clientId);

        $messageBody->data = $acknowledgeMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializerExt->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/some-service-acknowledge-message.amf0', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf0'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeAcknowledgeMessageAmf3()
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody('/1' . Constants::RESULT_METHOD);

        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $acknowledgeMessage = new AcknowledgeMessage($commandMessage);

        //reset properties manually so we can compare output
        $acknowledgeMessage->clientId       = '3182A13C-AF4A-A148-AA73-000028716D94';
        $acknowledgeMessage->messageId      = 'B1510529-D1A0-6A62-25DA-A00B1BF47BF6';
        $acknowledgeMessage->correlationId  = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $acknowledgeMessage->timestamp      = round(1437179933687);
        $acknowledgeMessage->headers        = (object) array('DSId' => $acknowledgeMessage->clientId);

        $messageBody->data = $acknowledgeMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializer->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf3', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf3'));
        $this->assertEquals($data, $mesg);
    }

    /**
     *
     */
    public function testserializeAcknowledgeMessageAmf3Ext()
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody('/1' . Constants::RESULT_METHOD);

        $commandMessage = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);

        //reset properties manually so we can compare output
        $commandMessage->messageId = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $commandMessage->timestamp = round(1437179933687);

        $acknowledgeMessage = new AcknowledgeMessage($commandMessage);

        //reset properties manually so we can compare output
        $acknowledgeMessage->clientId       = '3182A13C-AF4A-A148-AA73-000028716D94';
        $acknowledgeMessage->messageId      = 'B1510529-D1A0-6A62-25DA-A00B1BF47BF6';
        $acknowledgeMessage->correlationId  = '63FCE70D-F447-ED49-83E6-00001695D4AF';
        $acknowledgeMessage->timestamp      = round(1437179933687);
        $acknowledgeMessage->headers        = (object) array('DSId' => $acknowledgeMessage->clientId);

        $messageBody->data = $acknowledgeMessage;
        $actionMessage->bodies[0] = $messageBody;
        $mesg = $this->serializerExt->writeMessage($actionMessage);
        //$this->sendToJamf($mesg);
        //file_put_contents(__DIR__ . '/../asset/value/some-service-acknowledge-message.amf3', serialize($mesg));
        $data = unserialize(file_get_contents(__DIR__ . '/../../asset/value/some-service-acknowledge-message.amf3'));
        $this->assertEquals($data, $mesg);
    }
}
