<?php
include "lib/Log.php";
include "lib/config.php";

$config = json_decode(file_get_contents('./config.json'),true);

$log = new Log;
$log->makeIndex(array_merge($sysConfig,$config));

if(isset($_GET['sum'])){
    $log->sum($_GET['sum']);
}

if(isset($_GET['count'])){
    $log->count($_GET['count']);
}

if(isset($_GET['select'])){
    $log->select($_GET['select']);
}

if(isset($_GET['table'])){
    $log->table($_GET['table']);
}

if(isset($_GET['where'])){
    $log->where($_GET['where']);
}

if(isset($_GET['period'])){
    $log->period($_GET['period']);
}

if($_GET){
    echo $log->get();
}else{
    echo file_get_contents('view/index.html');
}