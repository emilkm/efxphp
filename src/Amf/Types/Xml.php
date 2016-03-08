<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Amf\Types;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Xml
{
    /**
     * @var string XML data
     */
    public $data;

    /**
     * @param string $xmlstring
     */
    public function __construct($xmlstring)
    {
        $this->data = preg_replace('/\>(\n|\r|\r\n| |\t)*\</', '><', trim($xmlstring));
    }

    /**
     * @return string
     */
    public function __toSring()
    {
        return $this->data;
    }
}
