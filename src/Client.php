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

    public $sessionId = null;
    public $remoteLogin = null;

    public $requestTimeout = self::REQUEST_TIMEOUT; //30 seconds
    public $messageQueue = array();
    public $clientId = null;
    private $sequence = 0;
    public $destination = '';
    public $endpoint = '';
    public $headers = null;
    private $response = null;

    /**
     * @param Deserializer $deserializer
     * @param Serializer   $serializer
     * @param mixed        $destination
     * @param mixed        $endpoint
     * @param mixed        $requestTimeout
     * @param ClientLogin  $remoteLogin
     */
    public function __construct(
        Deserializer $deserializer,
        Serializer $serializer,
        $destination,
        $endpoint,
        $requestTimeout = self::REQUEST_TIMEOUT,
        ClientLogin $remoteLogin = null
    ) {
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
        $this->destination = $destination;
        $this->endpoint = $endpoint;
        $this->requestTimeout = $requestTimeout;
        $this->remoteLogin = $remoteLogin;
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
     * @param callable $onResult
     * @param callable $onStatus
     */
    public function ping(callable $onResult, callable $onStatus)
    {
        /*if (!is_callable($onStatus) || !is_callable($onResult)) {
            throw new Exception('onStatus and onResult must be callable');
        }*/
        if ($this->clientId == null && $this->sequence == 0 && count($this->messageQueue) == 0) {
            $this->messageQueue[] = new PendingRequest('command', 'ping', array(null), $onResult, $onStatus);
            $this->processQueue();
        }
    }

    /**
     * @param string   $source
     * @param string   $operation
     * @param array    $params
     * @param callable $onResult
     * @param callable $onStatus
     */
    public function invoke($source, $operation, $params, callable $onResult, callable $onStatus)
    {
        /*if (!is_callable($onStatus) || !is_callable($onResult)) {
            throw new Exception('onStatus and onResult must be callable');
        }*/
        $params = $this->safeParams($params);
        if ($this->clientId == null && $this->sequence == 0 && count($this->messageQueue) == 0) {
            $this->messageQueue[] = new PendingRequest('command', 'ping', array(null), $onResult, $onStatus);
            if ($this->remoteLogin != null) {
                $this->messageQueue[] = new PendingRequest(
                    $this->remoteLogin->service,
                    $this->remoteLogin->method,
                    array(
                       array($this->remoteLogin->clientBuild, $this->remoteLogin->password),
                    ),
                    $onResult,
                    $onStatus
                );
            }
            $this->messageQueue[] = new PendingRequest($source, $operation, $params, $onResult, $onStatus);
            $this->processQueue();
            return;
        }
        $this->messageQueue[] = new PendingRequest($source, $operation, $params, $onResult, $onStatus);
        if ($this->clientId == null || ($this->sessionId == null && $this->remoteLogin != null)) {
            return;
        }
        $this->processQueue();
    }

    private function processQueue()
    {
        while (count($this->messageQueue) > 0) {
            $pendingRequest = array_shift($this->messageQueue);
            if ($this->sequence == 1 || ($this->sequence == 2 && $this->remoteLogin != null)) {
                $this->send($pendingRequest);
                return;
            } else {
                $this->send($pendingRequest);
            }
        }
    }

    private function createMessage(PendingRequest $pendingRequest)
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
                $pendingRequest->source,
                $pendingRequest->operation,
                $pendingRequest->params
            );

            /*for ($i = 0; $i < count($headers); $i++) {
                $header = $this->headers[$i];
                foreach ($headerName header) {
                    $message->headers[$headerName] = $header[$headerName];
                }
            }*/
        }

        $messageBody->data[0] = $message;
        $actionMessage->bodies[] = $messageBody;
        return $actionMessage;
    }

    private function send(PendingRequest $pendingRequest)
    {
        $outMessage = $this->createMessage($pendingRequest);
        $requestData = $this->serializer->writeMessage($outMessage);

        $this->response = (object) array(
            'status' => '',
            'error' => false,
            'headers' => array(),
            'data' => null,
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
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
            return;
        } elseif ($this->response->status != 200) {
            $this->response->error = (object) array(
                'code' => 0,
                'message' => 'HTTP status not 200.',
                'detail' => null,
            );
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
            return;
        } elseif (!isset($this->response->headers['content-type'])
            || (isset($this->response->headers['content-type']) && strpos($this->response->headers['content-type'], 'application/x-amf') === false)
        ) {
            $this->response->error = (object) array(
                'code' => 1,
                'message' => 'Unsupported content type.',
                'detail' => $this->response->headers['content-type'],
            );
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
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
                    call_user_func_array($pendingRequest->onStatus, array($this->response->error));
                    return;
                }
                if (!($decoded = @gzdecode($this->response->data))) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Failed to decode the response data.',
                        'detail' => 'gzdecode failed.',
                    );
                    call_user_func_array($pendingRequest->onStatus, array($this->response->error));
                    return;
                }
            } elseif (strtolower($this->response->headers['content-encoding']) == 'deflate') {
                if (!function_exists('gzinflate')) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Cannot decode the response data.',
                        'detail' => 'gzinflate not available.',
                    );
                    call_user_func_array($pendingRequest->onStatus, array($this->response->error));
                    return;
                }
                if (!($decoded = @gzinflate($this->response->data))) {
                    $this->response->error = (object) array(
                        'code' => 1,
                        'message' => 'Failed to decode the response data.',
                        'detail' => 'gzinflate failed.',
                    );
                    call_user_func_array($pendingRequest->onStatus, array($this->response->error));
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
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
            return;
        }

        $bodyCount = $inMessage->getBodyCount();

        if ($bodyCount == 0) {
            $this->response->error = (object) array(
                'code' => 3,
                'message' => 'Malformed AMF packet.',
                'detail' => 'Missing AMF body.',
            );
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
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
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
            return;
        }

        if (!($responseMessage instanceof AcknowledgeMessage) && !($responseMessage instanceof ErrorMessage)) {
            $this->response->error = (object) array(
                'code' => 5,
                'message' => 'Malformed AMF packet.',
                'detail' => 'Unexpected AMF message type.',
            );
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
            return;
        }

        if ($responseMessage instanceof ErrorMessage) {
            $this->response->error = (object) array(
                'code' => $responseMessage->faultCode,
                'message' => $responseMessage->faultString,
                'detail' => $responseMessage->faultDetail,
            );
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
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
            call_user_func_array($pendingRequest->onStatus, array($this->response->error));
        } else {
            if ($responseBody->targetURI == '/2/onResult' && $this->remoteLogin != null) {
                $this->setSessionId($responseMessage->body->data);
                $this->processQueue();
            } else {
                call_user_func_array($pendingRequest->onResult, array($responseMessage->body));
                $this->processQueue();
            }
        }
    }

    private function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->endpoint .= '?sid=' . $this->sessionId;
    }

    private function safeParams($params)
    {
        if (is_null($params)) {
            $params = array(null);
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

class ClientLogin
{
    public $service = '';
    public $method = 'login';
    public $clientBuild = 0;
    public $password = '';

    /**
     * @param string $service
     * @param mixed  $clientBuild
     * @param mixed  $password
     */
    public function __construct($service, $clientBuild, $password)
    {
        $this->service = $service;
        $this->clientBuild = $clientBuild;
        $this->password = $password;
    }
}

class PendingRequest
{
    public $source;
    public $operation;
    public $params;
    public $onResult;
    public $onStatus;

    /**
     * @param string   $source
     * @param stirng   $operation
     * @param mixed    $params
     * @param callable $onResult
     * @param callable $onStatus
     */
    public function __construct($source, $operation, $params, $onResult, $onStatus)
    {
        $this->source = $source;
        $this->operation = $operation;
        $this->params = $params;
        $this->onResult = $onResult;
        $this->onStatus = $onStatus;
    }
}
