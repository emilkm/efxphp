<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp;

use emilkm\efxphp\Amf\Constants;
use emilkm\efxphp\Amf\Deserializer;
use emilkm\efxphp\Amf\Serializer;
use emilkm\efxphp\Amf\ActionMessage;
use emilkm\efxphp\Amf\MessageHeader;
use emilkm\efxphp\Amf\MessageBody;
use emilkm\efxphp\Amf\Messages\AcknowledgeMessage;
use emilkm\efxphp\Amf\Messages\CommandMessage;
use emilkm\efxphp\Amf\Messages\ErrorMessage;
use emilkm\efxphp\Amf\Messages\RemotingMessage;

use Exception;

/**
 * @author  Emil Malinov
 * @package efxphp
 */
class Client
{
    const REQUEST_TIMEOUT = 30000; //30 seconds

    /**
     * Proxy to send requests through
     * @var mixed
     */
    private $proxy = null;

    /**
     * @var Deserializer
     */
    private $deserializer = null;

    /**
     * @var Serializer
     */
    private $serializer = null;

    /**
     * @var Request
     */
    private $request = null;

    /**
     * @var Response
     */
    private $response = null;

    private $sidPropagation = 'header'; // header or query

    private $sequence = 0;

    public $clientId = null;
    public $sessionId = null;
    public $remoteLogin = null;

    public $requestTimeout = self::REQUEST_TIMEOUT;
    public $messageQueue = array();

    public $destination = '';
    public $endpoint = '';
    public $headers = array();


    /**
     * @param Deserializer $deserializer
     * @param Serializer   $serializer
     * @param string       $destination
     * @param string       $endpoint
     * @param int          $requestTimeout
     */
    public function __construct(
        Deserializer $deserializer,
        Serializer $serializer,
        $destination,
        $endpoint,
        $requestTimeout = self::REQUEST_TIMEOUT
    ) {
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
        $this->destination = $destination;
        $this->endpoint = $endpoint;
        $this->requestTimeout = $requestTimeout;
    }

    /**
     * Set (debug) proxy information. To unset proxy information
     * call without parameters.
     *
     * @param string   $host Proxy hostname and port (localhost:8888)
     * @param constant $type CURL proxy type
     */
    public function setProxy($host = null, $type = CURLPROXY_HTTP)
    {
        if ($host == null) {
            $this->proxy = null;
        } else {
            $this->proxy = (object) array('host' => $host, 'type' => $type);
        }
    }

    /**
     * 'header' = through AMF RemoteMessage header sID, 'query' = through query string sID
     *
     * @param string $value
     */
    public function setSidPropagation($value)
    {
        if ($value != 'header' && $value != 'query') {
            throw new Exception("sidPropagation: 'header' = through AMF RemoteMessage header sID, 'query' = through query string sID");
        }
        $this->sidPropagation = $value;
    }

    /**
     * Set the session id for all further requests to the server.
     *
     * @param string $sessionId
     * @param bool   $releaseQueue
     */
    public function setSessionId($sessionId, $releaseQueue = false)
    {
        $this->sessionId = $sessionId;

        if ($this->sidPropagation == 'header')  {
            $this->headers['sID'] = $this->sessionId;
        } else {
            $this->endpoint .= '?sID=' . $this->sessionId;
        }

        if ($releaseQueue) {
            $this->releaseQueue();
        }
    }

    /**
     * Resume processing of the request queue, which may have be put on hold.
     */
    public function releaseQueue()
    {
        if (!$this->request->holdQueue) {
            return;
        }
        $this->processQueue();
    }

    /**
     * @param callable $onResult
     * @param callable $onStatus
     */
    public function ping(callable $onResult, callable $onStatus)
    {
        if ($this->clientId == null && $this->sequence == 0 && count($this->messageQueue) == 0) {
            $this->messageQueue[] = new Request('command', 'ping', array(), $onResult, $onStatus, 'command.ping');
            $this->processQueue();
        }
    }

    /**
     * @param string   $source
     * @param string   $operation
     * @param array    $params
     * @param callable $onResult
     * @param callable $onStatus
     * @param mixed    $token
     * @param bool     $holdQueue
     */
    public function invoke($source, $operation, $params, callable $onResult, callable $onStatus, $token = null, $holdQueue = false)
    {
        $params = $this->safeParams($params);
        if ($this->clientId == null && $this->sequence == 0 && count($this->messageQueue) == 0) {
            $this->messageQueue[] = new Request('command', 'ping', array(), $onResult, $onStatus, 'command.ping');
            $this->messageQueue[] = new Request($source, $operation, $params, $onResult, $onStatus, $token, $holdQueue);
            $this->processQueue();
            return;
        }
        $this->messageQueue[] = new Request($source, $operation, $params, $onResult, $onStatus, $token, $holdQueue);
        if ($this->clientId == null || ($this->request != null && $this->request->holdQueue)) {
            return;
        }
        $this->processQueue();
    }

    private function processQueue()
    {
        while (count($this->messageQueue) > 0) {
            $this->request = array_shift($this->messageQueue);
            if ($this->sequence == 1 || ($this->request != null && $this->request->holdQueue)) {
                $this->send($this->request);
                return;
            } else {
                $this->send($this->request);
            }
        }
    }

    private function createMessage(Request $request)
    {
        $actionMessage = new ActionMessage(3);
        $messageBody = new MessageBody();
        $message;
        if ($this->sequence == 0) {
            $this->sequence++;
            $messageBody->targetURI = 'null';
            $messageBody->responseURI = '/' . $this->sequence;
            $message = new CommandMessage(CommandMessage::CLIENT_PING_OPERATION);
            $message->destination = $this->destination;
        } else {
            $this->sequence++;
            $messageBody->targetURI = 'null';
            $messageBody->responseURI = '/' . $this->sequence;
            $message = new RemotingMessage(
                $this->clientId,
                $this->destination,
                $request->source,
                $request->operation,
                $request->params
            );

            foreach ($this->headers as $key => $value) {
                $message->headers->$key = $value;
            }
        }

        $messageBody->data[0] = $message;
        $actionMessage->bodies[] = $messageBody;
        return $actionMessage;
    }

    private function send(Request $request)
    {
        $outMessage = $this->createMessage($request);
        $requestData = $this->serializer->writeMessage($outMessage);

        $this->response = (object) array(
            'status' => '',
            'error' => false,
            'headers' => array(),
            'data' => null,
            'token' => $request->token
        );

        // Basic setup
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'EfxphpClient/php');
        curl_setopt($curl, CURLOPT_URL, $this->endpoint);

        if ($this->proxy != null) {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy->host);
            curl_setopt($curl, CURLOPT_PROXYTYPE, $this->proxy->type);
        }

        // Headers
        $headers = array();
        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Content-Type: application/x-amf';
        $headers[] = 'Content-length: ' . strlen($requestData);

        if (function_exists('gzdecode')) {
            if (function_exists('gzinflate')) {
                $headers[] = 'Accept-Encoding: gzip, deflate';
            } else {
                $headers[] = 'Accept-Encoding: gzip';
            }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, 'responseHeaderCallback'));
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, 'responseWriteCallback'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);

        // Execute, grab errors
        if (curl_exec($curl)) {
            $this->response->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        } else {
            $this->response->error = (object) array(
                'code' => curl_errno($curl),
                'message' => curl_error($curl),
                'detail' => null,
            );
        }
        @curl_close($curl);


        if ($this->response->error !== false) {
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        } elseif ($this->response->status != 200) {
            $this->response->error = (object) array(
                'code' => 0,
                'message' => 'HTTP status not 200.',
                'detail' => null,
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        } elseif (!isset($this->response->headers['content-type'])
            || (isset($this->response->headers['content-type']) && strpos($this->response->headers['content-type'], 'application/x-amf') === false)
        ) {
            $this->response->error = (object) array(
                'code' => 1,
                'message' => 'Unsupported content type.',
                'detail' => $this->response->headers['content-type'],
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        }

        if (isset($this->response->headers['content-encoding'])) {
            if (strtolower($this->response->headers['content-encoding']) == 'gzip') {
                if (!function_exists('gzdecode')) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Cannot decode the response data.',
                        'detail' => 'gzdecode not available.',
                    );
                    call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
                    return;
                }
                if (!($decoded = @gzdecode($this->response->data))) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Failed to decode the response data.',
                        'detail' => 'gzdecode failed.',
                    );
                    call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
                    return;
                }
            } elseif (strtolower($this->response->headers['content-encoding']) == 'deflate') {
                if (!function_exists('gzinflate')) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Cannot decode the response data.',
                        'detail' => 'gzinflate not available.',
                    );
                    call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
                    return;
                }
                if (!($decoded = @gzinflate($this->response->data))) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Failed to decode the response data.',
                        'detail' => 'gzinflate failed.',
                    );
                    call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
                    return;
                }
            }
            $this->response->data = $decoded;
        }

        try {
            $inMessage = $this->deserializer->readMessage($this->response->data);
        } catch (Exception $e) {
            $this->response->error = (object) array(
                'code' => 2,
                'message' => 'Failed to deserialize response data.',
                'detail' => $e->getMessage(),
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        }

        $bodyCount = $inMessage->getBodyCount();

        if ($bodyCount == 0) {
            $this->response->error = (object) array(
                'code' => 3,
                'message' => 'Malformed AMF packet.',
                'detail' => 'Missing AMF body.',
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        }

        $responseBody = $inMessage->getBody(0);

        try {
            $responseMessage = $responseBody->getDataAsMessage();
        } catch (Exception $e) {
            $this->response->error = (object) array(
                'code' => 4,
                'message' => 'Malformed AMF packet.',
                'detail' => 'Unsupported AMF message type.',
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        }

        if (!($responseMessage instanceof AcknowledgeMessage) && !($responseMessage instanceof ErrorMessage)) {
            $this->response->error = (object) array(
                'code' => 5,
                'message' => 'Malformed AMF packet.',
                'detail' => 'Unexpected AMF message type.',
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        }

        if ($responseMessage instanceof ErrorMessage) {
            $this->response->error = (object) array(
                'code' => $responseMessage->faultCode,
                'message' => $responseMessage->faultString,
                'detail' => $responseMessage->faultDetail,
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
            return;
        }

        if ($responseBody->targetURI == '/1/onResult') {
            $this->clientId = $responseMessage->clientId;
            $this->processQueue();
        } elseif ($responseMessage->body instanceof Response && $responseMessage->body->code < 0) {
            $this->response->error = (object) array(
                'code' => $responseMessage->body->code,
                'message' => $responseMessage->body->message,
                'detail' => $responseMessage->body->detail,
            );
            call_user_func_array($request->onStatus, array($this->response->error, $this->response->token));
        } else {
            if ($request->holdQueue) {
                call_user_func_array($request->onResult, array($responseMessage->body, $this->response->token));
                //caller must release the queue
            } else {
                call_user_func_array($request->onResult, array($responseMessage->body, $this->response->token));
                $this->processQueue();
            }
        }
    }

    private function safeParams($params)
    {
        if (is_null($params)) {
            $params = array();
        } elseif (!is_array($params)) {
            $params = array($params);
        }
        return $params;
    }

    /**
     * CURL header callback
     *
     * @param resource $curl CURL resource
     * @param string $data Data
     * @return integer
     */
    private function responseHeaderCallback($curl, $data)
    {
        if (($strlen = strlen($data)) <= 2) {
            return $strlen;
        }
        if (substr($data, 0, 4) == 'HTTP') {
            $this->response->status = (int) substr($data, 9, 3);
        } else {
            $data = trim($data);
            if (strpos($data, ': ') === false) {
                return $strlen;
            }
            list($header, $value) = explode(': ', $data, 2);
            $this->response->headers[strtolower($header)] = $value;
        }
        return $strlen;
    }

    /**
     * CURL write callback
     *
     * @param resource &$curl CURL resource
     * @param string &$data Data
     * @return integer
     */
    private function responseWriteCallback($curl, $data)
    {
        $this->response->data = $data;
        return strlen($data);
    }
}
