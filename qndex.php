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

$index->collectLog('example/nginx_access.log',"\n".'123.65.150.10 - - [23/Aug/2016:14:50:59 +0800] "POST /wordpress3/wp-admin/admin-ajax.php?id=1234 HTTP/1.1" 200 2 "http://www.example.com/wordpress3/wp-admin/post-new.php" "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.25 Safari/534.3"');
exit;

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
            '.css' => 'text/css'
        ];
        $response->header("Content-Type", $contentType[$ext]);
        $response->end(file_get_contents($config['rootPath'].$request->server["request_uri"]));
    }
    
    //write log from collectors
    if(isset($request->post['collector_id']) && isset($request->post['log']) && isset($config['collector_log_file'][$request->post['collector_id']])) {
        $serv->task($request->post, -1, function (swoole_server $serv, $task_id, $data) use ($index, $config){
            $index->collectLog($config['collector_log_file'][$data['collector_id']], $data['log']);
        });
        return;
    }
    
    $_GET = isset($request->get) ? $request->get : [];
    $_POST = isset($request->post) ? $request->post : [];

    //echo pages
    $p = isset($_GET['p']) ? $_GET['p'] : '';
    unset($_GET['p']);
    $log = new Log;
    
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

$serv->on('Task', function(swoole_server $serv, $task_id, $from_id, $data){
    return $data;
});

$serv->on('Finish', function(swoole_server $serv, $task_id, $data){

});

$serv->start();
