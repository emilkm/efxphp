<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\tests\util;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
trait SendToJamfTrait
{
    public function sendToJamf($data)
    {
        $headers = array(
            'Content-Type: application/x-amf',
            'Content-Length: ' . strlen($data)
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'efxphp');
        curl_setopt($curl, CURLOPT_URL, 'http://localhost.com:8008/test');
        curl_setopt($curl, CURLOPT_PROXY, '127.0.0.1:8888');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        return curl_exec($curl);
    }
}
