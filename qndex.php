<?php
require 'lib/config.php'; //$sysConfig
$config = json_decode(file_get_contents('config.json'),true);
$config = array_merge($sysConfig, $config);
$config['rootPath'] = __DIR__;
date_default_timezone_set($config['timezone']);


require 'lib/Log.php';
require 'Lib/Index.php';
require 'Lib/Cache.php';
$cache = new Cache;
$mainIndex = [];
$index = [];
$index = new Index($config,$cache);
$index->makeIndex();

$serv = new swoole_http_server("0.0.0.0", 8080);
$serv->set([
    'worker_num' => 1,
    'task_worker_num' => 1
]);

$serv->on('Request', function($request, $response) use($serv, $config, $cache, $index){
    //static files
    $ext = strtolower(strrchr($request->server["request_uri"],'.'));
    if( $ext && $ext != '.php'){
        $contentType = [
            '.css' => 'text/css',
            '.js' => 'application/x-javascript',
            '.svg' => 'text/xml'
        ];
        $response->header("Content-Type", $contentType[$ext]);
        $response->sendfile($config['rootPath'].$request->server["request_uri"]);
        return;
    }
    
    //write log from collectors
    if(isset($request->post['collector_id']) && isset($request->post['log']) && isset($config['collector_log_file'][$request->post['collector_id']])) {
        $serv->task($request->post, -1, function (swoole_server $serv, $task_id, $data) use ($index, $config){
            $index->collectLog($data['collector_id'], $data['log']);
        });
        return;
    }
    
    $_GET = isset($request->get) ? $request->get : [];
    $_POST = isset($request->post) ? $request->post : [];

    //echo pages
    $p = isset($_GET['p']) ? $_GET['p'] : '';
    unset($_GET['p']);
    $log = new Log($config,$cache);

    $log->test($serv);
    return;

    if(isset($_GET['getConfig'])){
        $config['p'] = $p;
        $response->end(json_encode($config));
        return;
    }elseif(empty($_GET) && empty($_POST)){
        $header = file_get_contents('view/header.html');
        $footer = file_get_contents('view/footer.html');
        $response->header("Content-Type", 'text/html');
        $response->end($header.$log->getHtml().$footer);
        return;
    }
    
    $response->end(json_encode($log->get(array_merge($_GET,$_POST))));
});

$serv->on('Task', function(swoole_server $serv, $task_id, $from_id, $data){
    return $data;
});

$serv->on('Finish', function(swoole_server $serv, $task_id, $data){

});

$serv->start();
