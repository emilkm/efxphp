<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\tests\efxphp;

use emilkm\efxphp\Client;
use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\Deserializer;
use emilkm\efxphp\Amf\Serializer;
use emilkm\efxphp\Amf\Input;
use emilkm\efxphp\Amf\Output;
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
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected static $serializer;
    protected static $deserializer;
    protected static $amfClient;

    public static function setUpBeforeClass()
    {
        self::$serializer = new Serializer(new Output());
        self::$deserializer = new Deserializer(new Input());

        self::$amfClient = new Client(
            self::$deserializer,
            self::$serializer,
            'efxphp',
            'http://127.0.0.1:9999/server/index.php'
        );

        self::$amfClient->setProxy('127.0.0.1:8888');
        self::$amfClient->ping(
            function ($result) use (&$response) {
                $response = $result;
            },
            function ($error) use (&$response) {
                $response = $error;
            }
        );
    }

    public function testpublicServicePublicMethodNoParams()
    {
        $response;
        self::$amfClient->invoke(
            'testapp.MyService',
            'publicMethodNoParams',
            null,
            function ($result) use (&$response) {
                $response = $result;
            },
            function ($error) use (&$response) {
                $response = $error;
            }
        );
        $this->assertTrue($response);
    }

    public function testpublicServicePublicMethodOptionalParam()
    {
        $response;
        self::$amfClient->invoke(
            'testapp.MyService',
            'publicMethodOptionalParam',
            null,
            function ($result) use (&$response) {
                $response = $result;
            },
            function ($error) use (&$response) {
                $response = $error;
            }
        );
        $this->assertTrue($response);
    }
}
