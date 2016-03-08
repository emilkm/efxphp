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

use Exception;

class ValidationException extends Exception
{
    public $errors;

    public function __construct($message, $errors) {
        $this->errors = $errors;
        parent::__construct($message);
    }
}

