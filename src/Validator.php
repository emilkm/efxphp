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

/**
 * @author  Emil Malinov
 * @package efxphp
 */
class Validator
{
    /**
     * Validate Telephone number
     *
     * Check if the given value is numeric with or without a `+` prefix
     *
     * @param string         $input
     * @param ValidationInfo $info
     *
     * @return string
     * @throws ValidationException
     */
    public function tel($input, ValidationInfo $info = null)
    {
        if (is_numeric($input) && '-' != substr($input, 0, 1)) {
            return $input;
        }
        throw new Exception(
            'Expecting phone number, a numeric value '
            . 'with optional `+` prefix'
        );
    }

    /**
     * Validate Email
     *
     * Check if the given string is a valid email
     *
     * @param string         $input
     * @param ValidationInfo $info
     *
     * @return string
     * @throws ValidationException
     */
    public function email($input, ValidationInfo $info = null)
    {
        $r = filter_var($input, FILTER_VALIDATE_EMAIL);
        if ($r) {
            return $r;
        }
        throw new Exception('Expecting email in `name@example.com` format');
    }

    /**
     * MySQL Date
     *
     * Check if the given string is a valid date in YYYY-MM-DD format
     *
     * @param String         $input
     * @param ValidationInfo $info
     *
     * @return string
     * @throws ValidationException
     */
    public function date($input, ValidationInfo $info = null)
    {
        $result = preg_match(
            '#^(?P<year>\d{2}|\d{4})-(?P<month>\d{1,2})-(?P<day>\d{1,2})$#',
            $input,
            $date
        );
        if ($result && checkdate($date['month'], $date['day'], $date['year'])) {
            return $input;
        }
        throw new Exception(
            'Expecting date in `YYYY-MM-DD` format, such as `'
            . date("Y-m-d") . '`'
        );
    }

    /**
     * MySQL DateTime
     *
     * Check if the given string is a valid date and time in YYY-MM-DD HH:MM:SS format
     *
     * @param string         $input
     * @param ValidationInfo $info
     *
     * @return string
     *
     * @throws ValidationException
     */
    public function datetime($input, ValidationInfo $info = null)
    {
        $result = preg_match(
            '/^(?P<year>19\d\d|20\d\d)\-(?P<month>0[1-9]|1[0-2])\-'
            . '(?P<day>0\d|[1-2]\d|3[0-1]) (?P<h>0\d|1\d|2[0-3]'
            . ')\:(?P<i>[0-5][0-9])\:(?P<s>[0-5][0-9])$/',
            $input,
            $date
        );
        if ($result && checkdate($date['month'], $date['day'], $date['year'])) {
            return $input;
        }
        throw new Exception(
            'Expecting date and time in `YYYY-MM-DD HH:MM:SS` format, such as `'
            . date("Y-m-d H:i:s") . '`'
        );
    }

    /**
     * Alias for Time
     *
     * Check if the given string is a valid time in HH:MM:SS format
     *
     * @param string         $input
     * @param ValidationInfo $info
     *
     * @return string
     *
     * @throws ValidationException
     */
    public function time24($input, ValidationInfo $info = null)
    {
        return $this->time($input, $info);
    }

    /**
     * Time
     *
     * Check if the given string is a valid time in HH:MM:SS format
     *
     * @param String         $input
     * @param ValidationInfo $info
     *
     * @return string
     *
     * @throws ValidationException
     */
    public function time($input, ValidationInfo $info = null)
    {
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $input)) {
            return $input;
        }
        throw new Exception(
            'Expecting time in `HH:MM:SS` format, such as `'
            . date("H:i:s") . '`'
        );
    }

    /**
     * Time in 12 hour format
     *
     * Check if the given string is a valid time 12 hour format
     *
     * @param string         $input
     * @param ValidationInfo $info
     *
     * @return string
     *
     * @throws ValidationException
     */
    public function time12($input, ValidationInfo $info = null)
    {
        if (preg_match(
            '/^([1-9]|1[0-2]|0[1-9]){1}(:[0-5][0-9])?\s?([aApP][mM]{1})?$/',
            $input
        )) {
            return $input;
        }
        throw new Exception(
            'Expecting time in 12 hour format, such as `08:00AM` and `10:05:11`'
        );
    }

    /**
     * Unix Timestamp
     *
     * Check if the given value is a valid timestamp
     *
     * @param string         $input
     * @param ValidationInfo $info
     *
     * @return int
     *
     * @throws ValidationException
     */
    public function timestamp($input, ValidationInfo $info = null)
    {
        if ((string) (int) $input == $input
            && ($input <= PHP_INT_MAX)
            && ($input >= ~PHP_INT_MAX)
        ) {
            return (int) $input;
        }
        throw new Exception('Expecting unix timestamp, such as ' . time());
    }

    /**
     * Validate the given input
     *
     * Validates the input and attempts to fix it when fix is requested
     *
     * @param mixed          $input
     * @param ValidationInfo $info
     * @param array          $errors
     */
    public function validate(&$input, ValidationInfo $info, &$errors)
    {
        $error = '';
        $name = "`$info->name`";
        try {
            if (is_null($input)) {
                if ($info->required) {
                    throw new Exception("$name is required.");
                }
                return;
            }

            // when type is an array check if it passes for any type
            if (is_array($info->type)) {
                $types = $info->type;
                foreach ($types as $type) {
                    $info->type = $type;
                    try {
                        $this->validate($input, $info, $errors);
                        if (count($errors) > 0) {
                            return;
                        }
                    } catch (Exception $e) {
                        // just continue
                    }
                }
                throw new Exception($error);
            }

            //patterns are supported only for non numeric types
            if (isset($info->pattern)
                && $info->type != 'int'
                && $info->type != 'float'
                && $info->type != 'number'
            ) {
                if (!preg_match($info->pattern, $input)) {
                    throw new Exception($error);
                }
            }

            if (isset($info->choice)) {
                if (is_array($input)) {
                    foreach ($input as $i) {
                        if (!in_array($i, $info->choice)) {
                            $error .= ". Expected one of (" . implode(',', $info->choice) . ").";
                            throw new Exception($error);
                        }
                    }
                } elseif (!in_array($input, $info->choice)) {
                    $error .= ". Expected one of (" . implode(',', $info->choice) . ").";
                    throw new Exception($error);
                }
            }

            switch ($info->type) {
                case 'int':
                case 'float':
                case 'number':
                    if (!is_numeric($input)) {
                        $error .= '. Expecting '
                            . ($info->type == 'int' ? 'integer' : 'numeric')
                            . ' value';
                        break;
                    }
                    if ($info->type == 'int' && (int) $input != $input) {
                        if ($info->fix) {
                            $input = (int) $input;
                        } else {
                            $error .= '. Expecting integer value';
                            break;
                        }
                    } else {
                        $r = $info->numericValue($input);
                    }
                    if (isset($info->min) && $r < $info->min) {
                        if ($info->fix) {
                            $input = $info->min;
                        } else {
                            $error .= ". Minimum required value is $info->min.";
                            break;
                        }
                    }
                    if (isset($info->max) && $r > $info->max) {
                        if ($info->fix) {
                            $input = $info->max;
                        } else {
                            $error .= ". Maximum allowed value is $info->max.";
                            break;
                        }
                    }
                    return;

                case 'string':
                    if (!is_string($input)) {
                        $error .= '. Expecting alpha numeric value';
                        break;
                    }
                    if ($info->required && empty($input) && $input != 0) {
                        $error = "$name is required.";
                        break;
                    }
                    $r = strlen($input);
                    if (isset($info->min) && $r < $info->min) {
                        if ($info->fix) {
                            $input = str_pad($input, $info->min, $input);
                        } else {
                            $char = $info->min > 1 ? 'characters' : 'character';
                            $error .= ". Minimum $info->min $char required.";
                            break;
                        }
                    }
                    if (isset($info->max) && $r > $info->max) {
                        if ($info->fix) {
                            $input = substr($input, 0, $info->max);
                        } else {
                            $char = $info->max > 1 ? 'characters' : 'character';
                            $error .= ". Maximum $info->max $char allowed.";
                            break;
                        }
                    }
                    return;

                case 'bool':
                case 'boolean':
                    if ($input === 'true' || $input === true) {
                        return;
                    }
                    if (is_numeric($input)) {
                        ($input > 0);
                    }
                    return;

                case 'array':
                    if ($info->fix && is_string($input)) {
                        $input = explode(',', $input);
                    }
                    if (is_array($input)) {
                        $contentType = $this->nestedValue($info, 'contentType') ? : null;
                        if ($info->fix) {
                            if ($contentType == 'indexed') {
                                $input = $info->filterArray($input, true);
                            } elseif ($contentType == 'associative') {
                                $input = $info->filterArray($input, true);
                            }
                        } elseif ($contentType == 'indexed'
                            && array_values($input) != $input
                        ) {
                            $error .= '. Expecting a list of items but an item is given';
                            break;
                        } elseif ($contentType == 'associative'
                            && array_values($input) == $input
                            && count($input)
                        ) {
                            $error .= '. Expecting an item but a list is given';
                            break;
                        }
                        $r = count($input);
                        if (isset($info->min) && $r < $info->min) {
                            $item = $info->max > 1 ? 'items' : 'item';
                            $error .= ". Minimum $info->min $item required.";
                            break;
                        }
                        if (isset($info->max) && $r > $info->max) {
                            if ($info->fix) {
                                $input = array_slice($input, 0, $info->max);
                            } else {
                                $item = $info->max > 1 ? 'items' : 'item';
                                $error .= ". Maximum $info->max $item allowed.";
                                break;
                            }
                        }
                        if (isset($contentType)
                            && $contentType != 'associative'
                            && $contentType != 'indexed'
                        ) {
                            $name = $info->name;
                            $info->type = $contentType;
                            unset($info->contentType);
                            foreach ($input as $key => $chinput) {
                                $info->name = "{$name}[$key]";
                                $input[$key] = $this->validate($chinput, $info, $errors);
                            }
                        }
                        return;

                    } elseif (isset($contentType)) {
                        $error .= ". Expecting items of type `$contentType`";
                        break;
                    } else {
                        $error .= ". Expecting items of type `$info->type`";
                        break;
                    }
                    break;
                case 'mixed':
                case 'unknown_type':
                case 'unknown':
                case null: //treat as unknown
                    return;
                default:
                    if (!is_object($input) && !is_array($input)) {
                        $error .= ". Expecting an item of type `$info->type`";
                        break;
                    }
                    return;
                    //TODO: type validation, the type conversion
            }
            throw new Exception($error);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
