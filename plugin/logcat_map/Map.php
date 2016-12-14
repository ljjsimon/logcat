<?php
class Map extends Log{
    protected function beforeGet(){
        $periodArr = $this->getPeriodArr($this->stime,$this->etime,$this->period);
        $this->dataArr = array_fill(0,count($periodArr),0);
        $this->periodArr = $periodArr;
        $this->lastDataKey = '';
    }
    
    protected function getFields($fields){
        $time = $fields[$this->config['time']];
        $ip = $fields['client_ip'];
        if(!is_numeric($time)){
            $time = strtotime($time);
        }
        
        $key = intval(($time - $this->stime)/$this->period);
        if($key != $this->lastDataKey){
            $this->lastDataKey = $key;
            $this->ips = [];
        }

        if(!in_array($ip,$this->ips)){
            $this->dataArr[$key] += 1;
            $this->ips[] = $ip;
        }
    }

    public function getHtml(){
        return file_get_contents('index.html');
    }
}