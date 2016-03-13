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
     * The implementation of this interface may indetify and establish
     * a client session. If the API is public and no sessions
     * are needed, simply do nothing.
     *
     * @abstract
     *
     * @param string $clientId The request message header DSId
     * @param string $sessionId The request message header sID
     *
     * @throws Exception when the client and/or session could not be identified
     */
    public function identify($clientId, $sessionId);

    /**
     * @abstract
     *
     * @return bool
     */
    public function isAuthenticated();
}
