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
use flex\messaging\messages\AcknowledgeMessage;
use flex\messaging\messages\CommandMessage;
use flex\messaging\messages\ErrorMessage;

use ReflectionMethod;
use ReflectionProperty;
use Exception;
use Error;
use DateTime;

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
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var string
     */
    private $amfDdFileName;

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
        AuthorizationInterface $autzProvider,
        LoggerInterface $logger
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
        $this->log = $logger;
    }

    /**
     * Write metadata cache unless class reflection is disabled and there is no
     * class metadata to cache.
     *
     * @throws \Exception
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
            throw new Exception('Could not write to cache: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle the request
     *
     * @throws \Exception
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
            throw new Exception('Could not handle the request.', 0, $e);
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
        $allowOrigin = is_string($this->config->accessControlAllowOrigin) ? $this->config->accessControlAllowOrigin : 'http://localhost';
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            if ($this->config->accessControlAllowOrigin == '*'
                || (is_array($this->config->accessControlAllowOrigin) && in_array($_SERVER['HTTP_ORIGIN'], $this->config->accessControlAllowOrigin))
            ) {
                $allowOrigin = $_SERVER['HTTP_ORIGIN'];
            }
        }
        header('Access-Control-Allow-Origin: ' . $allowOrigin);
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

        if ($this->config->logAmfSerializationData) {
            $date = new DateTime('now');
            $this->amfDdFileName = $this->config->logDirectory . '/amf_' . $date->format('Y-m-d.H-i-s.v') . '.dd';
            file_put_contents($this->amfDdFileName, serialize($data));
        }

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
                } elseif (isset($_REQUEST['sid'])) { //HACK: Remove when we get rid of Flex, other clients should be able to supply correct case sID
                    $sessionId = $_REQUEST['sid'];
                }
            }
        }

        if ($this->actionContext->requestIsCommand) {
            return;
        }

        try {
            $this->identProvider->identify($clientId, $sessionId);
        } catch (Exception $e) {
            for ($this->actionContext->messageIndex = 0; $this->actionContext->messageIndex < $bodyCount; $this->actionContext->messageIndex++) {
                $this->actionContext->errors[$this->actionContext->messageIndex] = $e;
            }
        }
    }

    protected function process()
    {
        $this->actionContext->responseMessage = new ActionMessage($this->actionContext->requestMessage->version);
        $bodyCount = $this->actionContext->requestMessage->getBodyCount();
        for ($this->actionContext->messageIndex = 0; $this->actionContext->messageIndex < $bodyCount; $this->actionContext->messageIndex++) {
            /** @var MessageBody $requestBody */
            $requestBody = $this->actionContext->requestMessage->bodies[$this->actionContext->messageIndex];
            $requestMessage = $requestBody->getDataAsMessage();
            $responseBody = new MessageBody($requestBody->responseURI);
            $this->actionContext->responseMessage->bodies[$this->actionContext->messageIndex] = $responseBody;

            try {
                if (count($this->actionContext->errors) > 0 && $this->actionContext->errors[$this->actionContext->messageIndex] instanceof Exception) {
                    throw $this->actionContext->errors[$this->actionContext->messageIndex];
                }

                $responseBody->data = $responseMessage = new AcknowledgeMessage($requestMessage);

                if ($requestMessage instanceof CommandMessage) {
                    if ($requestMessage->operation != CommandMessage::CLIENT_PING_OPERATION) {
                        throw new Exception('Operation not supported.');
                    }
                    if ($this->config->logAmfSerializationData) {
                        @unlink($this->amfDdFileName);
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
                $this->invoke($requestMessage, $responseMessage);
                $responseBody->targetURI .= Constants::RESULT_METHOD;
            } catch (Exception | Error $e) {
                if ($this->config->logAmfSerializationData) {
                    @unlink($this->amfDdFileName);
                }
                $responseBody->targetURI .= Constants::STATUS_METHOD;
                $responseBody->data = $errorMessage = new ErrorMessage($requestMessage);

                $errorMessage->faultCode = $e->getCode();
                $errorMessage->faultString = $e->getMessage();

                if ($this->config->serverOperationMode != ServerConfig::OPMODE_PRODUCTION) {
                    $errorMessage->extendedData = $e->getTraceAsString();
                }

                if ($e instanceof ValidationException) {
                    $errorMessage->faultDetail = $e->errors;
                } else {
                    $ectx = property_exists($e, 'context') ? $e->context : 'unknown';
                    $etrc = ($this->config->serverOperationMode != ServerConfig::OPMODE_PRODUCTION) ? $errorMessage->extendedData : $e->getTraceAsString();
                    $this->log->write('error', $e->getMessage() . PHP_EOL . PHP_EOL . '>>>> TRACE >>>>' . PHP_EOL . $etrc, $e->getCode(), $ectx);
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
                $this->actionContext->classMetadata[$this->actionContext->messageIndex]
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
     *
     * @throws \Exception
     */
    protected function authorize($message)
    {
        //Service not found, or could not parse comments
        if (count($this->actionContext->errors) > $this->actionContext->messageIndex) {
            $error = $this->actionContext->errors[$this->actionContext->messageIndex];
            if ($error instanceof Exception) {
                throw $error;
            }
        }

        if ($this->config->get('classReflectionDisabled', false)) {
            $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageIndex];
            $className = $classMetadata['className'];
            $serviceInstance = $this->dice->create($className);
            $methodAccess = null;

            try {
                $method = new ReflectionMethod($className, $message->operation);

                //Method not found
                if ($method->isPrivate()) {
                    throw new Exception('Method not found.');
                }
            } catch (Exception $e) {
                 new Exception('Method not found.');
            }

            try {
                $methodAccess = $serviceInstance->getMethodAccess();
            } catch (Exception $e) {

            }

            if (!$this->autzProvider->authorize($classMetadata['className'], $message->operation, $methodAccess)) {
                throw new Exception('Not Authorized');
            }
            return $serviceInstance;
        }

        //Method not found
        $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageIndex];
        if (!isset($classMetadata['methods'][$message->operation]) || ($method['access'] == 'private')) {
            throw new Exception('Method not found.');
        }

        //authorization
        $method = $classMetadata['methods'][$message->operation];
        if ($method['access'] == 'public') {
            return null;
        }

        if (!$this->autzProvider->authorize($classMetadata['className'], $message->operation, $method['access'])) {
            throw new Exception('Not Authorized');
        }

        return null;
    }

    /**
     * @param RemotingMessage $message
     *
     * @throws \Exception
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
        $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageIndex];
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
     *
     * @throws \Exception
     */
    protected function invoke($requestMessage, $responseMessage, $serviceInstance)
    {
        if ($this->config->logAmfSerializationData) {
            @unlink($this->amfDdFileName);
        }

        try {
            if ($serviceInstance == null) {
                $classMetadata = $this->actionContext->classMetadata[$this->actionContext->messageIndex];
                $className = $classMetadata['className'];
                $serviceInstance = $this->dice->create($className);
            }
            $methodName = $requestMessage->operation;

            if ($requestMessage->body == null) {
                $result = call_user_func(
                    array(
                        $serviceInstance,
                        $methodName,
                    )
                );
            } else {
                $parameters = $requestMessage->body;
                $paramrefs = [];
                foreach ($parameters as $key => $value) {
                    $paramrefs[$key] = &$parameters[$key];
                }

                $result = call_user_func_array(
                    array(
                        $serviceInstance,
                        $methodName,
                    ),
                    $paramrefs
                );
            }

            if ($this->config->responseClass != null) {
                $className = $this->config->responseClass;
                if ($result instanceof $className) {
                    $response = $result;
                } else {
                    //HACK: Third parameter only for Artena Flex. Remove when we get rid of Flex or returnFaultAsResult logic.
                    $response = $this->dice->create($className, [$result, null, null]);
                }
                $responseMessage->body = $response;
            } else {
                $responseMessage->body = $result;
            }
        } catch (Exception | Error $e) {
            throw $e;
        }
    }

    protected function encode()
    {
        if ($this->config->logAmfSerializationData) {
            $date = new DateTime('now');
            $filename = $this->config->logDirectory . '/amf_' . $date->format('Y-m-d.H-i-s.v') . '.ed';
            file_put_contents($filename, serialize($this->actionContext->responseMessage));
        }

        $this->responseData = $this->serializer->writeMessage($this->actionContext->responseMessage);

        if ($this->config->logAmfSerializationData) {
            @unlink($filename);
        }
    }

    protected function respond()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Wed, 24 June 1987 08:55:00 GMT');
        header('X-Powered-By: EFXPHP v' . self::VERSION);

        $allowOrigin = is_string($this->config->accessControlAllowOrigin) ? $this->config->accessControlAllowOrigin : 'http://localhost';
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            if ($this->config->accessControlAllowOrigin == '*'
                || (is_array($this->config->accessControlAllowOrigin) && in_array($_SERVER['HTTP_ORIGIN'], $this->config->accessControlAllowOrigin))
            ) {
                $allowOrigin = $_SERVER['HTTP_ORIGIN'];
            }
        }
        header('Access-Control-Allow-Origin: ' . $allowOrigin);

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
