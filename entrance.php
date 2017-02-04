<?php
/**
 * Created by PhpStorm.
 * User: gaoyi
 * Date: 12/26/16
 * Time: 4:59 PM
 */


use TcpWorker\Worker;

require_once __DIR__ . '/TcpWorker/AutoLoader.php';


$worker = new Worker("http://0.0.0.0:2345", 4);

$worker->onMessage = function()
{
    echo "Worker start1";
};


$worker->run();