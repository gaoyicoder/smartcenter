<?php
/**
 * Created by PhpStorm.
 * User: gaoyi
 * Date: 12/28/16
 * Time: 11:37 AM
 */

namespace TcpWorker;
date_default_timezone_set('Asia/Shanghai');

use Exception;

class Worker
{


    protected $_socketName = '';
    protected $_autoloadRootPath = '';
    protected $_count = 1;
    protected $_startFile;
    protected $_isDaemon = false;

    public $pidFile = "";
    public $logFile = "";
    public $onMessage = null;

    protected function checkSapiEnv()
    {
        if (php_sapi_name() != "cli") {
            exit("Only run in command line mode \n");
        }
    }

    protected function init()
    {
        $backtrace = debug_backtrace();
        $this->_startFile = $backtrace[count($backtrace) - 1]['file'];
        if ($this->logFile == "") {
            $this->logFile = __DIR__ . '/../TcpWorker.log';
        }
        if ($this->pidFile == "") {
            $this->pidFile = __DIR__ . "/../" . str_replace('/', '_', $this->_startFile) . ".pid";
        }

        touch($this->logFile);
        chmod($this->logFile, 0622);

        $this->setProcessTitle('TcpWorker: master process start_file=' . $this->_startFile);
    }

    protected function parseCommand()
    {
        global $argv;

        $start_file = $argv[0];

        if (!isset($argv[1])) {
            exit("Usage: php ".$start_file ."{start|stop|restart|status}\n");
        }

        $command = trim($argv[1]);
        $command2 = isset($argv[2]) ? trim($argv[2]) : '';

        $mode = "";

        if ($command === 'start') {
            if ($command2 === '-d') {
                $mode = 'in DAEMON mode';
            } else {
                $mode = 'in DEBUG mode';
            }
        }
        $this->log("TcpWorker[$start_file] $command $mode");

        $master_pid = @file_get_contents($this->pidFile);
        $master_is_alive = $master_pid && @posix_kill($master_pid, 0);

        if ($master_is_alive) {
            if ($command === 'start') {
                $this->log("TcpWorker[$start_file] already running");
                exit();
            }
        } elseif ($command !== 'start' && $command !== 'restart') {
            $this->log("TcpWorker[$start_file] not run");
            exit();
        }

        switch ($command) {
            case 'start':
                if ($command2 == '-d') {
                    $this->_isDaemon = true;
                }
                break;
            case 'stop':
                //TODO stop operation
                break;
            case 'restart':
                //TODO restart operation
                break;
            case 'status':
                //TODO status operation
                break;
            default:
                exit("Usage: php".$start_file ."{start|stop|restart|status}\n");
        }
    }

    protected function daemonize()
    {
        if(!$this->_isDaemon) {
            return ;
        }

        umask(0);
        $pid = pcntl_fork();

        if ($pid === -1) {
            throw new Exception('fork fail');
        } elseif ($pid !== 0) {
            exit(0);
        }

        if (posix_setsid() === -1) {
            throw new Exception('Set sid fail');
        }

        $pid = pcntl_fork();

        if ($pid === -1) {
            throw new Exception('fork fail');
        } elseif ($pid !== 0) {
            exit(0);
        }
    }

    protected function log($msg)
    {

        $msg = $msg . "\n";
        if (!$this->_isDaemon) {
            echo $msg;
        }

        file_put_contents($this->logFile, date('Y-m-d H:i:s').' '.'pid:'.posix_getegid().' '.$msg, FILE_APPEND | LOCK_EX);
    }

    protected function setProcessTitle($title)
    {
        @cli_set_process_title($title);
    }

    public function __construct($socket_name,$count=0)
    {
        $backtrace = debug_backtrace();
        $this->_autoloadRootPath = dirname($backtrace[0]['file']);
        Autoloader::setRootPath($this->_autoloadRootPath);

        if ($socket_name) {
            $this->_socketName = $socket_name;
        }

        if ($count === 0) {
            $this->_count = $count;
        }
        // Set an empty onMessage callback.
        $this->onMessage = function () {
        };
    }

    public function run()
    {
        $this->checkSapiEnv();
        $this->init();
        $this->parseCommand();
        $this->daemonize();
        while(1) {

        }
    }
}