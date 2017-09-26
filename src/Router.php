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

use emilkm\efxphp\Amf\Messages\AbstractMessage;
use emilkm\efxphp\Amf\Messages\CommandMessage;
use emilkm\efxphp\Amf\Messages\RemotingMessage;
use emilkm\efxphp\Amf\Messages\AcknowledgeMessage;
use emilkm\efxphp\Util\NestedValueTrait;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Exception;

/**
 * Human readable cache based on Restler's
 *
 * @author  Emil Malinov
 * @package efxphp
 */
class Router
{
    use NestedValueTrait;

    public $classAliases = array();
    protected $models = array();

    /**
     * @var CommentParser
     */
    protected $commentParser;

    /**
     * false = no namespacing | '' = namespace as is | 'root' = root\as is
     *
     * @var string
     */
    protected $servicesRootNamespace;

    /**
     * @param CommentParser $commentParser
     * @param mixed         $servicesRootNamespace
     */
    public function __construct(CommentParser $commentParser, $servicesRootNamespace)
    {
        $this->commentParser = $commentParser;
        $this->servicesRootNamespace = $servicesRootNamespace;
    }

    /**
     * @param ActionContext   $actionContext
     * @param RemotingMessage $message
     * @param bool            $skipReflection
     *
     * @throws Exception 
     */
    public function find($actionContext, $message, $skipReflection)
    {
        $actionContext->classMetadata[$actionContext->messageIndex] = null;

        $classExists = str_replace('.', '\\', $message->source);

        if ($this->servicesRootNamespace === false) {
            $pos = strrpos($message->source, '.');
            $className = ($pos !== false) ? substr($message->source, $pos + 1) : $message->source;
        } else {
            if ($this->servicesRootNamespace != '') {
                $className = $this->servicesRootNamespace . '\\' . $classExists;
                $classExists = $className;
            } else {
                $className = $classExists;
            }
        }

        if (isset($this->classAliases[$className])) {
            $className = $this->classAliases[$className];
        }

        if (!class_exists($classExists)) {
            $ex = new Exception('Service not found.');
            $actionContext->errors[$actionContext->messageIndex] = $ex;
            throw $ex;
        }

        $actionContext->classMetadata[$actionContext->messageIndex]['classAndPackage'] = $message->source;
        $actionContext->classMetadata[$actionContext->messageIndex]['className'] = $className;

        if ($skipReflection) {
            return;
        }

        try {
            $class = new ReflectionClass($className);

            $classMetadata = $this->commentParser->parse($class->getDocComment());

            $scope = $this->scope($class);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC); //+ ReflectionMethod::IS_PROTECTED
            foreach ($methods as $method) {
                $methodName = $method->getName();
                //skip methods beginning with _
                if ($methodName{0} == '_') {
                    continue;
                }

                $metadata = $this->commentParser->parse($method->getDocComment()) + $classMetadata;
                //skip private @access methods
                if (isset($metadata['access']) && $metadata['access'] == 'private') {
                    continue;
                }

                $arguments = array();
                $defaults = array();
                $params = $method->getParameters();
                $position = 0;

                if (isset($classMetadata['description'])) {
                    $metadata['classDescription'] = $classMetadata['description'];
                }
                if (isset($classMetadata['classLongDescription'])) {
                    $metadata['classLongDescription'] = $classMetadata['longDescription'];
                }
                if (!isset($metadata['param'])) {
                    $metadata['param'] = array();
                }
                if (isset($metadata['return']['type'])) {
                    if ($qualified = $this->resolve($metadata['return']['type'], $scope)) {
                        list($metadata['return']['type'], $metadata['return']['children']) =
                            $this->getTypeAndModel(new ReflectionClass($qualified), $scope);
                    }
                } else {
                    //assume return type is array
                    $metadata['return']['type'] = 'array';
                }
                foreach ($params as $param) {
                    $children = array();
                    $type = $param->isArray() ? 'array' : $param->getClass();
                    $arguments[$param->getName()] = $position;
                    $defaults[$position] = $param->isDefaultValueAvailable()
                        ? $param->getDefaultValue()
                        : null;
                    if (!isset($metadata['param'][$position])) {
                        $metadata['param'][$position] = array();
                    }
                    $m = &$metadata['param'][$position];
                    $m['name'] = $param->getName();
                    if (empty($m['label'])) {
                        $m['label'] = $this->label($m['name']);
                    }
                    if (is_null($type) && isset($m['type'])) {
                        $type = $m['type'];
                    }
                    if ($m['name'] == 'email' && empty($m[$this->commentParser->embeddedDataName]['type']) && $type == 'string') {
                        $m[$this->commentParser->embeddedDataName]['type'] = 'email';
                    }
                    $m['default'] = $defaults[$position];
                    $m['required'] = !$param->isOptional();
                    $contentType = $this->nestedValue(
                        $m,
                        $this->commentParser->embeddedDataName,
                        'type'
                    );
                    if ($contentType && $qualified = $this->resolve($contentType, $scope)) {
                        list($contentType, $children) = $this->getTypeAndModel(new ReflectionClass($qualified), $scope);
                    }
                    if ($type instanceof ReflectionClass) {
                        list($type, $children) = $this->getTypeAndModel($type, $scope);
                    } elseif ($type && is_string($type) && $qualified = $this->resolve($type, $scope)) {
                        list($type, $children)
                            = $this->getTypeAndModel(new ReflectionClass($qualified), $scope);
                    }
                    if (isset($type)) {
                        $m['type'] = $type;
                    }
                    $m['children'] = $children;
                    if (!isset($m['type'])) {
                        $type = $m['type'] = $this->type($defaults[$position]);
                    }
                    $position++;
                }
                $access = 'private';
                if (isset($metadata['access'])) {
                    $access = $metadata['access'];
                }

                $actionContext->classMetadata[$actionContext->messageIndex]['methods'][$methodName] = array(
                    'arguments' => $arguments,
                    'defaults' => $defaults,
                    'metadata' => $metadata,
                    'access' => $access,
                );
            }
        } catch (Exception $e) {
            $ex = new Exception("Error while parsing comments of `$className` class. " . $e->getMessage());
            $actionContext->errors[$actionContext->messageIndex] = $ex;
            throw $ex;
        }
    }

    /**
     * @param mixed $var
     *
     * @return string
     */
    public function type($var)
    {
        if (is_object($var)) {
            return get_class($var);
        }
        if (is_array($var)) {
            return 'array';
        }
        if (is_bool($var)) {
            return 'boolean';
        }
        if (is_numeric($var)) {
            return is_float($var) ? 'float' : 'int';
        }
        return 'string';
    }

    /**
     * Create a label from name of the parameter or property
     *
     * Convert `camelCase` style names into proper `Title Case` names
     *
     * @param string $name
     *
     * @return string
     */
    public function label($name)
    {
        return ucfirst(preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', $name));
    }

    /**
     * Get fully qualified class name for the given scope
     *
     * @param string $className
     * @param array  $scope     local scope
     *
     * @return string|bool The class name or false
     */
    public function resolve($className, array $scope)
    {
        if (empty($className) || !is_string($className)) {
            return false;
        }
        $divider = '\\';
        $qualified = false;
        if ($className[0] == $divider) {
            $qualified = trim($className, $divider);
        } elseif (array_key_exists($className, $scope)) {
            $qualified = $scope[$className];
        } else {
            $qualified = $scope['*'] . $className;
        }
        if (class_exists($qualified)) {
            return $qualified;
        }
        if (isset($this->classAliases[$className])) {
            $qualified = $this->classAliases[$className];
            if (class_exists($qualified)) {
                return $qualified;
            }
        }

        return false;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return string[] imports
     */
    public function scope(ReflectionClass $class)
    {
        $namespace = $class->getNamespaceName();
        $imports = array('*' => empty($namespace) ? '' : $namespace . '\\');
        $file = file_get_contents($class->getFileName());
        $tokens = token_get_all($file);
        $namespace = '';
        $alias = '';
        $reading = false;
        $last = 0;
        foreach ($tokens as $token) {
            if (is_string($token)) {
                if ($reading && ',' == $token) {
                    //===== STOP =====//
                    $reading = false;
                    if (!empty($namespace)) {
                        $imports[$alias] = trim($namespace, '\\');
                    }
                    //===== START =====//
                    $reading = true;
                    $namespace = '';
                    $alias = '';
                } else {
                    //===== STOP =====//
                    $reading = false;
                    if (!empty($namespace)) {
                        $imports[$alias] = trim($namespace, '\\');
                    }
                }
            } elseif (T_USE == $token[0]) {
                //===== START =====//
                $reading = true;
                $namespace = '';
                $alias = '';
            } elseif ($reading) {
                //echo token_name($token[0]) . ' ' . $token[1] . PHP_EOL;
                switch ($token[0]) {
                    case T_WHITESPACE:
                        continue 2;
                    case T_STRING:
                        $alias = $token[1];
                        if (T_AS == $last) {
                            break;
                        }
                    //don't break;
                    case T_NS_SEPARATOR:
                        $namespace .= $token[1];
                        break;
                }
                $last = $token[0];
            }
        }
        return $imports;
    }

    /**
     * Get the type and associated model
     *
     * @param ReflectionClass $class
     * @param array           $scope
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getTypeAndModel(ReflectionClass $class, array $scope)
    {
        $className = $class->getName();
        if (isset($this->models[$className])) {
            return $this->models[$className];
        }
        $children = array();
        try {
            $props = $class->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($props as $prop) {
                $name = $prop->getName();
                $child = array('name' => $name);
                if ($c = $prop->getDocComment()) {
                    $child += $this->nestedValue($this->commentParser->parse($c), 'var');
                } else {
                    $o = $class->newInstance();
                    $p = $prop->getValue($o);
                    if (is_object($p)) {
                        $child['type'] = get_class($p);
                    } elseif (is_array($p)) {
                        $child['type'] = 'array';
                        if (count($p)) {
                            $pc = reset($p);
                            if (is_object($pc)) {
                                $child['contentType'] = get_class($pc);
                            }
                        }
                    }
                }
                $child += array(
                    'type' => $child['name'] == 'email' ? 'email' : 'string',
                    'label' => $this->label($child['name']),
                );
                isset($child[$this->commentParser->embeddedDataName])
                    ? $child[$this->commentParser->embeddedDataName] += array('required' => true)
                    : $child[$this->commentParser->embeddedDataName]['required'] = true;
                if ($className == $prop->class) {
                    //not sure what to do here, but calling getTypeAndModel causes endless recursion
                } elseif ($qualified = $this->resolve($child['type'], $scope)) {
                    list($child['type'], $child['children'])
                        = $this->getTypeAndModel(new ReflectionClass($qualified), $scope);
                } elseif (($contentType = $this->nestedValue($child, $this->commentParser->embeddedDataName, 'type'))
                    && ($qualified = $this->resolve($contentType, $scope))
                ) {
                    list($child['contentType'], $child['children'])
                        = $this->getTypeAndModel(new ReflectionClass($qualified), $scope);
                }
                $children[$name] = $child;
            }
        } catch (Exception $e) {
            if (stripos($e->getFile(), 'CommentParser.php') !== false) {
                throw new Exception("Error while parsing comments of `$className` class. " . $e->getMessage());
            }
            throw $e;
        }
        $this->models[$className] = array($class->getName(), $children);
        return $this->models[$className];
    }
}
