<?php
require 'lib/config.php'; //$sysConfig
$config = json_decode(file_get_contents('config.json'),true);
$config = array_merge($sysConfig, $config);
$config['rootPath'] = __DIR__;
date_default_timezone_set($config['timezone']);

spl_autoload_register(function ($class){
    global $config;
    $class = strtolower($class);
    include 'plugin/'.$config['plugin'][$class]['file'];
});

require 'lib/Log.php';
require 'lib/Index.php';
require 'lib/Cache.php';
$cache = new Cache;
$mainIndex = [];
$index = [];
$index = new Index($config,$cache);
echo 'making index...';
$index->makeIndex();
echo "done\n";

$serv = new swoole_http_server("0.0.0.0", 8080);
$serv->set([
    'worker_num' => 4,
    'task_worker_num' => 4
]);

$serv->on('Request', function(swoole_http_request $request, swoole_http_response $response) use($serv, $config, $cache, $index){
    //static files
    $ext = strtolower(strrchr($request->server["request_uri"],'.'));
    if( $ext && $ext != '.php'){
        $contentType = [
            '.css' => 'text/css',
            '.js' => 'application/x-javascript',
            '.svg' => 'text/xml',
            '.ico' => 'image/x-icon',
            '.woff' => 'application/octet-stream'
        ];
        if(isset($contentType[$ext])){
            $response->header("Content-Type", $contentType[$ext]);
        }
        $response->header('Cache-Control','max-age:8640000');
        $response->sendfile($config['rootPath'].$request->server["request_uri"]);
        return;
    }
    
    //write log from collectors
    if(isset($request->post['collector_id']) && isset($request->post['log']) && isset($config['collector_log_file'][$request->post['collector_id']])) {
        $serv->task($request->post, -1, function (swoole_server $serv, $task_id, $data) use ($index, $config){
            $file = $config['rootPath'].'/'.$config['dataDir'].'/'.$config['collector_log_file'][$data['collector_id']].'_'.data('Ymd');
            $index->collectLog($file, $data['log']);
        });
        return;
    }

    $_GET = isset($request->get) ? $request->get : [];
    $_POST = isset($request->post) ? $request->post : [];

    //plugin
    $p = isset($_GET['p']) ? $_GET['p'] : '';
    unset($_GET['p']);
    if($p && isset($config['plugin'][$p])){
        $class = ucfirst($p);
        $log = new $class($config,$cache);
    }else{
        $log = new Log($config,$cache);
    }

    //echo pages
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
    
    $response->end(json_encode($log->get(array_merge($_GET,$_POST),$serv)));
});

$serv->on('Task', function(swoole_server $serv, $task_id, $from_id, $data){
    return call_user_func_array($data[0], $data[1]);
});

$serv->on('Finish', function(swoole_server $serv, $task_id, $data){

});

$serv->start();
