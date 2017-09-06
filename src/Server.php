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

use ReflectionMethod;
use ReflectionProperty;
use Exception;

/**
 * @author  Emil Malinov
 * @package efxphp
 */
class Server
{
    const VERSION = '1.0.0';

    /**
     * @var ActionContext
     */
    protected $actionContext;

    /**
     * @var mixed
     */
    protected $responseData;

    /**
     * @var Dice
     */
    protected $dice;

    /**
     * @var ServerConfig
     */
    protected $config;

    /**
     * @var Deserializer
     */
    protected $deserializer;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var MetadataCache
     */
    protected $metadataCache;

    /**
     * @var CommentParser
     */
    protected $commentParser;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var IdentificationInteface
     */
    protected $identProvider;

    /**
     * @var AuthorizationInterface
     */
    protected $autzProvider;

    /**
     * lots of seams
     *
     * @param Dice                    $dice
     * @param ServerConfig            $config
     * @param Deserializer            $deserializer
     * @param Serializer              $serializer
     * @param MetadataCache           $metadataCache
     * @param CommentParser           $commentParser
     * @param Validator               $validator
     * @param Router                  $router
     * @param AuthenticationInterface $identProvider
     * @param AuthorizationInterface  $autzProvider
     */
    public function __construct(
        Dice $dice,
        ServerConfig $config,
        Deserializer $deserializer,
        Serializer $serializer,
        MetadataCache $metadataCache,
        CommentParser $commentParser,
        Validator $validator,
        Router $router,
        IdentificationInterface $identProvider,
        AuthorizationInterface $autzProvider
    ) {
        $this->dice = $dice;
        $this->config = $config;
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
        $this->metadataCache = $metadataCache;
        $this->commentParser = $commentParser;
        $this->validator = $validator;
        $this->router = $router;
        $this->identProvider = $identProvider;
        $this->autzProvider = $autzProvider;
    }

    /**
     * Write metadata cache unless class reflection is disabled and there is no
     * class metadata to cache.
     */
    public function __destruct()
    {
        if ($this->config->get('classReflectionDisabled', false)) {
            return;
        }

        try {
            if ($this->actionContext
                && is_array($this->actionContext->classMetadata)
                && count($this->actionContext->classMetadata) > 0
            ) {
                $this->metadataCache->set($this->actionContext->classMetadata);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }

    /**
     * Handle the request
     */
    public function handle()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if ($requestMethod == 'OPTIONS') {
            $this->negotiateCORS();
        } elseif ($requestMethod != 'POST') {
            throw new Exception('Unsupported request method.');
        }

        try {
            $this->actionContext = new ActionContext();
            $this->decode();
            $this->identify();
            $this->process();
            $this->encode();
            $this->respond();
        } catch (Exception $e) {
            throw new Exception('Could not handle the request.');
        }
    }

    protected function negotiateCORS()
    {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: POST');
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        }
        header('Access-Control-Allow-Origin: ' . (
            $this->config->accessControlAllowOrigin == '*' && isset($_SERVER['HTTP_ORIGIN'])
                ? $_SERVER['HTTP_ORIGIN']
                : $this->config->accessControlAllowOrigin
        ));
        header('Access-Control-Allow-Credentials: false');
        exit(0);
    }

    protected function getRawPostData()
    {
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            return $GLOBALS['HTTP_RAW_POST_DATA'];
        } else {
            return file_get_contents('php://input');
        }
    }

    protected function getRequestStream()
    {
        $rawInput = fopen('php://input', 'r');
        $tempStream = fopen('php://temp', 'r+');
        stream_copy_to_stream($rawInput, $tempStream);
        rewind($tempStream);
        return $tempStream;
    }

    protected function decode()
    {
        $data = $this->getRawPostData();
        $this->actionContext->requestMessage = $this->deserializer->readMessage($data);
    }

    protected function identify()
    {
        $clientId = null;
        $sessionId = null;
        $bodyCount = $this->actionContext->requestMessage->getBodyCount();
        for ($i = 0; $i < $bodyCount; $i++) {
            $requestBody = $this->actionContext->requestMessage->getBody($i);
            $requestMessage = $requestBody->getDataAsMessage();
            if ($requestMessage instanceof CommandMessage) {
                $this->actionContext->requestIsCommand = true;
                break;
            }
            $clientId = $requestMessage->headers->DSId;
            if ($this->config->sidPropagation == 'header') {
                if (isset($requestMessage->headers->sID)) {
                    $sessionId = $requestMessage->headers->sID;
                }
            } else {
                if (isset($_REQUEST['sID'])) {
                    $sessionId = $_REQUEST['sID'];
                }
            }
        }

        if ($this->actionContext->requestIsCommand) {
            return;
        }

        try {
            $this->identProvider->identify($clientId, $sessionId);
        } catch (Exception $e) {
            for ($this->actionContext->messageNumber = 0; $this->actionContext->messageNumber < $bodyCount; $this->actionContext->messageNumber++) {
                $this->actionContext->errors[$this->actionContext->messageNumber] = $e;
            }
        }
    }

    protected function process()
    {
        $this->actionContext->responseMessage = new ActionMessage($this->actionContext->requestMessage->version);
        $bodyCount = $this->actionContext->requestMessage->getBodyCount();
        for ($this->actionContext->messageNumber = 0; $this->actionContext->messageNumber < $bodyCount; $this->actionContext->messageNumber++) {
            /** @var MessageBody $requestBody */
            $requestBody = $this->actionContext->requestMessage->bodies[$this->actionContext->messageNumber];
            $requestMessage = $requestBody->getDataAsMessage();
            $responseBody = new MessageBody($requestBody->responseURI);
            $this->actionContext->responseMessage->bodies[$this->actionContext->messageNumber] = $responseBody;

            try {
                if ($this->actionContext->errors[$this->actionContext->messageNumber] instanceof Exception) {
                    throw $this->actionContext->errors[$this->actionContext->messageNumber];
                }

                $responseBody->data = $responseMessage = new AcknowledgeMessage($requestMessage);

                if ($requestMessage instanceof CommandMessage) {
                    if ($requestMessage->operation != CommandMessage::CLIENT_PING_OPERATION) {
                        throw new Exception('Operation not supported.');
                    }
                    $responseBody->targetURI .= Constants::RESULT_METHOD;
                    break;
                }

                if ($this->actionContext->requestIsCommand && $bodyCount > 1) {
                    throw new Exception('CommandMessage cannot not be part of a batch.');
                }

                $this->route($requestMessage);
                $serviceInstance = $this->authorize($requestMessage);
                $this->validate($requestMessage);
                $this->invoke($requestMessage, $responseMessage, $serviceInstance);
                $responseBody->targetURI .= Constants::RESULT_METHOD;
            } catch (Exception $e) {
                $responseBody->targetURI .= Constants::STATUS_METHOD;
                $responseBody->data = $errorMessage = new ErrorMessage($requestMessage);

                $errorMessage->faultCode = $e->getCode();
                $errorMessage->faultString = $e->getMessage();

                if ($e instanceof ValidationException) {
                    $errorMessage->faultDetail = $e->errors;
                }

                if ($this->config->serverOperationMode != ServerConfig::OPMODE_PRODUCTION) {
                    $errorMessage->extendedData = $e->getTraceAsString();
                }
            }
        }
    }

    /**
     * @param RemotingMessage $message
     */
    protected function route($message)
    {
        if ($this->config->get('classReflectionDisabled', false)) {
            $this->router->find($this->actionContext, $message, true);
            return;
        }

        $found = false;
        if ($this->config->serverOperationMode != ServerConfig::OPMODE_DEVELOPMENT) {
            if ($this->metadataCache->isCached($message->source)) {
                $this->actionContext->classMetadata[$this->actionContext->messageNumber]
                    = $this->metadataCache->get($message->source);
                $found = true;
            }
        }

        if (!$found) {
            $this->router->find($this->actionContext, $message, false);
        }
    }

    /**
     * @param RemotingMessage $message
     * @return mixed Method access array or null.
     */
    protected function authorize($message)
    {
        //Service not found, or could not parse comments
        if (count($this->actionContext->errors) > $this->actionContext->messageNumber) {
            $error = $this->actionContext->errors[$this->actionContext->messageNumber];
            if ($error instanceof Exception) {
                throw $error;
            }
        }

        if ($this->config->get('classReflectionDisabled', false)) {
            $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageNumber];
            $className = $classMetadata['className'];
            $serviceInstance = $this->dice->create($className);

            try {
                $method = new ReflectionMethod($className, $message->operation);

                //Method not found
                if ($method->isPrivate()) {
                    throw new Exception('Method not found.');
                }
            } catch (Exception $e) {
                 new Exception('Method not found.');
            }

            $methodAccess = isset($serviceInstance->methodAccess) && is_array($serviceInstance->methodAccess) ?  $serviceInstance->methodAccess : null;

            $this->autzProvider->authorize($classMetadata['className'], $message->operation, $methodAccess);

            return $serviceInstance;
        }

        //Method not found
        $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageNumber];
        if (!isset($classMetadata['methods'][$message->operation]) || ($method['access'] == 'private')) {
            throw new Exception('Method not found.');
        }

        //authorization
        $method = $classMetadata['methods'][$message->operation];
        if ($method['access'] == 'public') {
            return null;
        }

        $this->autzProvider->authorize($classMetadata['className'], $message->operation, $method['access']);

        return null;
    }

    /**
     * @param RemotingMessage $message
     */
    protected function validate($message)
    {
        if ($this->config->get('classReflectionDisabled', false)) {
            return;
        }

        if (is_null($message->body)) {
            $message->body = array(null);
        } elseif (!is_array($message->body)) {
            $message->body = array($message->body);
        }
        $validationErrors = array();
        $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageNumber];
        $method = $classMetadata['methods'][$message->operation];
        foreach ($method['metadata']['param'] as $index => $param) {
            if (isset($message->body[$index])) {
                $input = &$message->body[$index];
            } else {
                $input = null;
            }
            $info = new ValidationInfo($classMetadata['className'], $message->operation, $param, $this->commentParser->embeddedDataName);
            $errors = array();
            $this->validator->validate($input, $info, $errors);
            if (count($errors) > 0) {
                $validationErrors = array_merge($validationErrors, $errors);
            }
        }

        if (count($validationErrors) > 0) {
            throw new ValidationException('Validation Errors.', $validationErrors);
        }
    }

    /**
     * @param RemotingMessage $requestMessage
     * @param AcknowledgeMessage $responseMessage
     * @param mixed $serviceInstance
     */
    protected function invoke($requestMessage, $responseMessage, $serviceInstance)
    {
        try {
            if ($serviceInstance == null) {
                $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageNumber];
                $className = $classMetadata['className'];
                $serviceInstance = $this->dice->create($className);
            }
            $methodName = $requestMessage->operation;
            $parameters = &$requestMessage->body;

            $result = call_user_func_array(
                array(
                    $serviceInstance,
                    $methodName,
                ),
                $parameters
            );

            if ($this->config->responseClass != null) {
                $className = $this->config->responseClass;
                if ($result instanceof $className) {
                    $response = $result;
                } else {
                    $response = $this->dice->create($className, [$result, null]);
                }
                $responseMessage->body = $response;
            } else {
                $responseMessage->body = $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function encode()
    {
        $this->responseData = $this->serializer->writeMessage($this->actionContext->responseMessage);
    }

    protected function respond()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Wed, 24 June 1987 08:55:00 GMT');
        header('X-Powered-By: EFXPHP v' . self::VERSION);

        if ($this->config->crossOriginResourceSharing) {
            header('Access-Control-Allow-Origin: ' . (
                $this->config->accessControlAllowOrigin == '*' && isset($_SERVER['HTTP_ORIGIN'])
                    ? $_SERVER['HTTP_ORIGIN']
                    : $this->config->accessControlAllowOrigin
            ));
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        header('Connection: Keep-Alive');
        header('Content-Type: application/x-amf');

        if (!$this->config->contentEncodingEnabled) {
            $data = $this->responseData;
        } else {
            $clientAcceptEncodingGzip = false;
            $clientAcceptEncodingDeflate = false;
            $serverSupportEncodingGzip = function_exists('gzencode');
            $serverSupportEncodingDeflate = function_exists('gzdeflate');

            if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
                if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                    $clientAcceptEncodingGzip = true;
                }
                if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false) {
                    $clientAcceptEncodingDeflate = true;
                }
            }

            if ((!$clientAcceptEncodingGzip && !$clientAcceptEncodingDeflate)
                || (!$serverSupportEncodingGzip && !$serverSupportEncodingDeflate)
                || ($clientAcceptEncodingGzip && !$clientAcceptEncodingDeflate && !$serverSupportEncodingGzip)
                || (!$clientAcceptEncodingGzip && $clientAcceptEncodingDeflate && $serverSupportEncodingDeflate)
            ) {
                $data = $this->responseData;
            } elseif (!$clientAcceptEncodingGzip || !$serverSupportEncodingGzip) {
                $data = gzdeflate($this->responseData, 5);
                header('Content-Encoding: deflate');
                header('Vary: Accept-Encoding');
            } else {
                $data = gzencode($this->responseData, 5);
                header('Content-Encoding: gzip');
                header('Vary: Accept-Encoding');
            }
        }

        header('Content-Length: ' . strlen($data));

        echo $data;
    }
}
