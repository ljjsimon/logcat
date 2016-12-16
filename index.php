<?php
//system setting
set_time_limit(0);
require "lib/config.php"; //$sysConfig
$config = json_decode(file_get_contents('config.json'),true);
$config = array_merge($sysConfig,$config);
$config['rootPath'] = dirname(__FILE__);
date_default_timezone_set($config['timezone']);

//init controller
include "lib/Log.php";
if(isset($_GET['p']) && isset($config['plugin'][$_GET['p']])){
    $p = $_GET['p'];
    include $config['plugin'][$p]['file'];
    $class = ucfirst($p);
    $log = new $class($config);
    $p=$_GET['p'];
    unset($_GET['p']);
}else{
    $log = new Log($config);
}

//write log from collectors
if(isset($_POST['collector_id']) && isset($_POST['log']) && isset($config['collector_log_file'][$_POST['collector_id']])){
    $log->writeLog($config['dataDir'].'/'.$config['collector_log_file'][$_POST['collector_id']],$_POST['log']);
    exit;
}

//echo pages
$header = file_get_contents('view/header.html');
$footer = file_get_contents('view/footer.html');
if(empty($_GET) && empty($_POST) && php_sapi_name()!='cli'){
    echo $header.$log->getHtml().$footer;
    exit;
}elseif(isset($_GET['getConfig'])){
    echo json_encode($config);
    exit;
}

//make index
$log->makeIndex();
//echo data
if($_GET){
    $data = $log->get();
    echo json_encode($data);
}