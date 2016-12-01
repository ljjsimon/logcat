<?php
include "lib/Log.php";
include "lib/config.php";

$config = json_decode(file_get_contents('./config.json'),true);

$log = new Log;
$log->makeIndex(array_merge($sysConfig,$config));

if($_GET['select']){
    $log->select($_GET['select']);
}

if($_GET['table']){
    $log->table($_GET['table']);
}

if($_GET['where']){
    $log->where($_GET['where']);
}

if($_GET['period']){
    $log->period($_GET['period']);
}

if($_GET){
    $log->get();
}