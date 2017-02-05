<?php
/**
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

namespace emilkm\efxphp\Amf;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage amf
 */
class Constants
{
    //flex.messaging.io.MessageIOConstants
    const REMOTE_CLASS_FIELD        = '_explicitType';
    const EXTERNALIZED_DATA_FIELD   = '_externalizedData';

    const RESULT_METHOD             = '/onResult';
    const STATUS_METHOD             = '/onStatus';

    const EMPTY_STRING              = '';
    const NULL_STRING               = 'null';

    const AMF0_OBJECT_ENCODING      = 0;

    const AMF0_NUMBER               = 0;
    const AMF0_BOOLEAN              = 1;
    const AMF0_STRING               = 2;
    const AMF0_OBJECT               = 3;
    const AMF0_MOVIECLIP            = 4;
    const AMF0_NULL                 = 5;
    const AMF0_UNDEFINED            = 6;
    const AMF0_REFERENCE            = 7;
    const AMF0_MIXEDARRAY           = 8; //ECMAArray
    const AMF0_OBJECTEND            = 9;
    const AMF0_ARRAY                = 10; //StrictArray
    const AMF0_DATE                 = 11;
    const AMF0_LONGSTRING           = 12;
    const AMF0_UNSUPPORTED          = 13;
    const AMF0_RECORDSET            = 14;
    const AMF0_XMLDOCUMENT          = 15;
    const AMF0_TYPEDOBJECT          = 16;
    const AMF0_AMF3                 = 17;

    const AMF3_OBJECT_ENCODING      = 3;

    const AMF3_UNDEFINED            = 0;
    const AMF3_NULL                 = 1;
    const AMF3_BOOLEAN_FALSE        = 2;
    const AMF3_BOOLEAN_TRUE         = 3;
    const AMF3_INTEGER              = 4;
    const AMF3_DOUBLE               = 5;
    const AMF3_STRING               = 6;
    const AMF3_XMLDOCUMENT          = 7;
    const AMF3_DATE                 = 8;
    const AMF3_ARRAY                = 9;
    const AMF3_OBJECT               = 10;
    const AMF3_XML                  = 11;
    const AMF3_BYTEARRAY            = 12;
    const AMF3_VECTOR_INT           = 13;
    const AMF3_VECTOR_UINT          = 14;
    const AMF3_VECTOR_DOUBLE        = 15;
    const AMF3_VECTOR_OBJECT        = 16;
    const AMF3_DICTIONARY           = 17;

    // Object encodings for AMF3 object types
    const ET_PROPLIST               = 0;
    const ET_EXTERNAL               = 1;
    const ET_DYNAMIC                = 2;
    const ET_PROXY                  = 3;

    const FMS_OBJECT_ENCODING       = 1;

    const UNKNOWN_CONTENT_LENGTH    = 1;

    const AMF_U8_MAX                = 255;
    const AMF_U16_MAX               = 65535;
    const AMF_U32_MAX               = 4294967295;
    const AMF3_INT28_MAX            = 268435455;
    const AMF3_INT28_MIN            = -268435456;
    const AMF3_UINT29_MAX           = 536870911;

    const MAX_STORED_OBJECTS        = 1024;
}
