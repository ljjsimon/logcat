<?php
$config = json_decode(file_get_contents('config.json'),true);
if(!isset($config['sender'])){
    die('wrong sender config');
}
set_time_limit(0);
extract($config['sender']);//$log_file, $sender_id, $server_addr
$pos = 0;

function send($server_addr,$sender_id,$log){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$server_addr);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,compact('sender_id','log'));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

while(1){
    echo '1';
    $fp = fopen($log_file,'r');
    fseek($fp,$pos);
    $log = '';
    while(!feof($fp)){
        $log .= fgets($fp);
        $pos = ftell($fp);
    }
    fclose($fp);
    if($log){
        send($server_addr,$sender_id,$log);
    }
    sleep(1);
}
