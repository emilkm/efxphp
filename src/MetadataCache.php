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
 * Human readable cache based on Restler's
 *
 * @author  Emil Malinov
 * @package efxphp
 */
class MetadataCache
{
    /**
     * @var string path of the folder to hold cache files
     */
    public $cacheDir;

    /**
     * @param string $cacheDirectory
     */
    public function __construct($cacheDirectory)
    {
        $this->cacheDir = $cacheDirectory;
        if (!is_writable($this->cacheDir)) {
            $this->throwException();
        }
    }

    /**
     * Store data in the cache
     *
     * @param mixed $data
     *
     * @return bool True if successful
     *
     * @throws Exception
     */
    public function set($data)
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $service) {
                $s = '$o = array();' . PHP_EOL;
                $s .= '$o[\'className\'] = \'' . $service['className'] . '\';';
                foreach ($service['methods'] as $methodName => $method) {
                    $s .= PHP_EOL . PHP_EOL .
                        "//==================== $methodName ===================="
                        . PHP_EOL . PHP_EOL;
                    if (is_array($method)) {
                        $s .= '$o[\'methods\'][\'' . $methodName . '\'] = '
                            . str_replace('  ', '    ', var_export($method, true))
                            . ';';
                    }
                }
                $s .= PHP_EOL . PHP_EOL . 'return $o;';

                $file = $this->_file($service['classAndPackage']);
                $r = @file_put_contents($file, "<?php
/**
 * DO NOT EDIT BY HAND! THIS FILE WAS AUTO GENERAED BY
 *
 * efxphp (http://emilmalinov.com/efxphp)
 *
 * @copyright Copyright (c) 2015 Emil Malinov
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://github.com/emilkm/efxphp
 * @package   efxphp
 */

$s");
                @chmod($file, 0777);
                if ($r === false) {
                    $this->throwException();
                }
            }
        }
    }

    /**
     * Retrieve data from the cache
     *
     * @param string $name
     * @param bool   $ignoreErrors
     *
     * @return mixed
     */
    public function get($name, $ignoreErrors = false)
    {
        $file = $this->_file($name);
        if (file_exists($file)) {
            include($file);
        }
    }

    /**
     * Delete data from the cache
     *
     * @param string $name
     * @param bool   $ignoreErrors
     *
     * @return bool True if successful
     */
    public function clear($name, $ignoreErrors = false)
    {
        return @unlink($this->_file($name));
    }

    /**
     * Check if the given name is cached
     *
     * @param string $name
     *
     * @return bool True if cached
     */
    public function isCached($name)
    {
        return file_exists($this->_file($name));
    }

    private function _file($name)
    {
        return rtrim(str_replace(array('\\', '\\\\'), '/', $this->cacheDir), '/')
            . '/' . $name . '.php';
    }

    private function throwException()
    {
        throw new Exception(
            'The cache directory `'
            . $this->cacheDir . '` should exist with write permission.'
        );
    }
}
