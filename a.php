<?php
require 'lib/config.php'; //$sysConfig
$config = json_decode(file_get_contents('config.json'),true);
$config = array_merge($sysConfig, $config);
$config['rootPath'] = __DIR__;
date_default_timezone_set($config['timezone']);

$serv = new swoole_http_server("0.0.0.0", 8080);
$serv->set([
    //'worker_num' => 1,
    //'task_worker_num' => 1
]);

require 'lib/Log.php';
$mainIndex = [];
$index = [];
$log = new Log($config);
$log->makeIndex();

$serv->on('Request', function($request, $response) use($config, $log){
    //static files
    $ext = strtolower(strrchr($request->server["request_uri"],'.'));
    if( $ext && $ext != '.php'){
        $contentType = [
            '.css' => 'text/css'
        ];
        $response->header("Content-Type", $contentType[$ext]);
        $response->end(file_get_contents($config['rootPath'].$request->server["request_uri"]));
    }
    
    
    $_GET = isset($request->get) ? $request->get : [];
    $_POST = isset($request->post) ? $request->post : [];
    
    $p = isset($_GET['p']) ? $_GET['p'] : '';
    unset($_GET['p']);
    
    if(isset($_GET['getConfig'])){
        $config['p'] = $p;
        $response->end(json_encode($config));
    }elseif(empty($_GET) && empty($_POST)){
        $header = file_get_contents('view/header.html');
        $footer = file_get_contents('view/footer.html');
        $response->end($header.$log->getHtml().$footer);
    }
    
    $response->end(json_encode($log->get(array_merge($_GET,$_POST))));
});

$serv->on('Task', function(){

});

$serv->start();
