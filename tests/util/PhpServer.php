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

use Exception;

/**
 * @author     Emil Malinov
 * @package    efxphp
 * @subpackage tests
 */
class PhpServer
{
    /**
     * @var string
     */
    public $addr;

    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $docRoot;

    /**
     * Explicit path to php if needed
     *
     * @var string
     */
    public $phpPath;

    /**
     * Explicit path to php INI if needed
     *
     * @var string
     */
    public $iniPath;

    /**
     * @var int
     */
    private $pid;

    /**
     *
     *
     * @param string $addr    The hostname or IP address
     * @param int    $port    The port number to use
     * @param string $docRoot The path to the document root
     * @param string $phpPath
     * @param string $iniPath
     *
     * @return int The process ID of the PHP server
     */
    public function __construct(
        $addr,
        $port,
        $docRoot,
        $phpPath = 'php',
        $iniPath = null
    ) {
        $this->addr = $addr;
        $this->port = $port;
        $this->docRoot = $docRoot;
        $this->phpPath = $phpPath;
        $this->iniPath = $iniPath;
    }

    /**
     * Starts the PHP build-in server at the address and port provided,
     * and returns the process ID, if it was started successfully,
     * false otherwise.
     *
     * @param bool $stopOnShutdown Stop the server when script exection completes
     * @param int  $pauseDuration  Pause for a number of seconds after starting the server
     * @return int|false The process ID of the PHP server
     */
    public function start($stopOnShutdown = true, $pauseDuration = 1)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Command that starts the built-in web server
            $command = sprintf(
                $this->phpPath . (($this->iniPath != null) ? '-c ' . $this->iniPath : '') . ' -S %s:%d -t %s',
                $this->addr,
                $this->port,
                $this->docRoot
            );

            $descriptorspec = array (
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
            );

            // Execute the command and store the process ID of the parent
            $prog = proc_open($command, $descriptorspec, $pipes, '.', null);
            $ppid = proc_get_status($prog)['pid'];

            // this gets us the process ID of the child (i.e. the server we just started)
            $output = array_filter(explode(" ", shell_exec("wmic process get parentprocessid,processid | find \"$ppid\"")));
            array_pop($output);
            $pid = end($output);
        } else {
            // Command that starts the built-in web server
            $command = sprintf(
                $this->phpPath . (($this->iniPath != null) ? '-c ' . $this->iniPath : '') . ' -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
                $addr,
                $port,
                $docRoot
            );

            // Execute the command and store the process ID
            $output = array();
            exec($command, $output);
            $pid = (int) $output[0];
        }

        if (!isset($pid)) {
            return false;
        }

        $this->pid = $pid;

        if ($stopOnShutdown) {
            register_shutdown_function(array(&$this, 'stop'));
        }

        if ($pauseDuration > 0) {
            sleep($pauseDuration);
        }

        return $this->pid;
    }

    /**
     * Stops the server running under the local process ID.
     */
    public function stop()
    {
        if ((int) $this->pid != $this->pid) {
            return;
        }
        echo sprintf('%s - Killing process with ID %d', date('r'), $this->pid) . PHP_EOL;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /F /pid {$this->pid}");
        } else {
            exec("kill -9 {$this->pid}");
        }
    }

    /**
     * Checks to see if there is a server running locally under the process ID (PID) provided.
     *
     * @return bool True if there is a server process running under the PID, false otherwise
     */
    public function isRunning()
    {
        if ((int) $this->pid != $this->pid) {
            return;
        }
        $output = array();
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("tasklist /fi \"PID eq {$this->pid}\"", $output);
            if (isset($output[0]) && $output[0] == 'INFO: No tasks are running which match the specified criteria.') {
                return false;
            } else {
                return true;
            }
        } else {
            exec("ps -ef | grep {$this->pid} | grep -v grep", $output);
            if (count($output) == 0) {
                return false;
            } else {
                return true;
            }
        }
    }
}
