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
interface IdentificationInterface
{
    /**
     * @abstract
     * @param string $clientId
     * @param string $sessionId
     *
     * @throws Exception
     */
    public function identify($clientId, $sessionId);

    /**
     * @abstract
     * @return bool
     */
    public function isAuthenticated();
}
