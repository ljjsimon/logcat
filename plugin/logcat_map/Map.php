<?php
include "IP.class.php";
class Map extends Log{
    protected function filterInput($input){
        $this->ip = $input['ip'];
        isset($input['table']) && $this->__set('table', $input['table']);
        isset($input['period']) && $this->__set('period',$input['period']);
        isset($input['datetimerange']) && $this->__set('datetimerange', $input['datetimerange']);
        if(isset($input['where_f']) && isset($input['where_v'])){
            $this->__set('where', array_combine($input['where_f'], $input['where_v']));
        }
    }

    protected function beforeGet(){
        $this->dataArr = [];
        $this->ips = [];
    }
    
    protected function getFields($fields){
        $ip = $fields[$this->ip];
        if(!filter_var($ip,FILTER_VALIDATE_IP) || in_array($ip,$this->ips)){
            return;
        }
        $this->ips[] = $ip;
        $addr = IP::find($ip);
        if(!$addr[1]){
            return;
        }
        $province = $addr[1];
        if(isset($this->dataArr[$province])){
            $this->dataArr[$province]++;
        }else{
            $this->dataArr[$province] = 1;
        }
    }
    
    protected function getReduceData(){
        return $this->dataArr;
    }
    
    protected function reduceLog(array $results){
        $res = [];
        $dataArr = [];
        foreach($results as $result){
            if(!$dataArr){
                $dataArr = $result;
                continue;
            }
            foreach($result as $province => $count){
                if(isset($dataArr[$province])){
                    $dataArr[$province] += $count;
                }else{
                    $dataArr[$province] = $count;
                }
            }
        }
        foreach($dataArr as $province=>$count){
            $res[] = [
                'name' => $province,
                'value' => $count
            ];
        }
        return $res;
    }

    public function getHtml(){
        return file_get_contents(__DIR__ .'/index.html');
    }
}