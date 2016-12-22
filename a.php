<?php
require 'lib/config.php'; //$sysConfig
$config = json_decode(file_get_contents('config.json'),true);
$config = array_merge($sysConfig, $config);
$config['rootPath'] = __DIR__;
date_default_timezone_set($config['timezone']);

$serv = new swoole_http_server("127.0.0.1", 8080);
$serv->set([
    //'worker_num' => 1,
    //'task_worker_num' => 1
]);

$mainIndex = [];
$index = [];

require 'lib/Log.php';
$serv->on('Request', function($request, $response) use($config, $mainIndex, $index){
    $log = new Log($config);
    $response->end($log->get());
});

$serv->on('Task', function(){

});

$serv->start();
