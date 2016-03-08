<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */
namespace testapp;

use emilkm\efxphp\IdentificationInterface;
use emilkm\efxphp\AuthorizationInterface;

use Exception;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class AccessManager implements IdentificationInterface, AuthorizationInterface
{
    /**
     * @param string $clientId
     * @param string $sessionId
     */
    public function identify($clientId, $sessionId)
    {

    }

    /**
     *
     */
    public function isAuthenticated()
    {

    }

    /**
     * @param mixed $serviceName
     * @param mixed $methodName
     * @param mixed $access
     */
    public function authorize($serviceName, $methodName, $access)
    {
        if ($access == 'authenticated') {
            throw new Exception('Not authenticated');
        }
    }
}
