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

/**
 * @author  Emil Malinov
 * @package efxphp
 */
interface AuthorizationInterface
{
    /**
     * Access verification method.
     *
     * API access will be denied when this method returns false
     *
     * @abstract
     *
     * @param string $serviceName
     * @param string $methodName
     * @param string $requiredAccess
     *
     * @return bool True when service method access is allowed, false otherwise
     */
    public function authorize($serviceName, $methodName, $requiredAccess);
}
