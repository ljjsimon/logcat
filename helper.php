<?php
function makeIndex($config){    
    $dir = $config->data;
    $dh = opendir($dir);
    
    while(($_dir = readdir($dh)) !== false){
        if($_dir == '.' || $_dir == '..'){
            continue;
        }
        $sub_dir = $dir.'/'.$_dir;
        if(is_dir($sub_dir)){
            _makeIndex($config,$sub_dir);
        }
    }
    closedir($dh);
}

function _makeIndex($config,$sub_dir){
    $dh = opendir($sub_dir);
    while(($file = readdir($dh)) !== false){
        if(!is_file($file)){
            continue;
        }
        $extension = pathinfo($file,PHPINFO_EXTENSION);
        $basename = substr($file, 0, strlen($file)-strlen($extension));
        if($extension == 'index' || file_exists($basename.'index')){
            continue;
        }

        $fp = fopen($file,'r');
        while(!feof($fp)){
            $line = $fgets($fp);
        }
        fclose($fp);
    }
    closedir($dh);
}
