<?php
include "IP.class.php";
class Llist extends Log{
    protected function filterInput(){
        $this->ip = $_GET['ip'];
        isset($_GET['table']) && $this->__set('table', $_GET['table']);
        isset($_GET['period']) && $this->__set('period',$_GET['period']);
        isset($_GET['datetimerange']) && $this->__set('datetimerange', $_GET['datetimerange']);
        if(isset($_GET['where_f']) && isset($_GET['where_v'])){
            $this->__set('where', array_combine($_GET['where_f'], $_GET['where_v']));
        }
    }

    protected function beforeGet(){
        $this->dataArr = [];
    }
    
    protected function getFields($fields){
        if(isset($this->config['url_query'])){
            $url_query = $this->config['url_query'].'.';
            foreach($fields as $k=>$v){
                if(strpos($k,$url_query)===0){
                    unset($fields[$k]);
                }
            }
        }
        $time = $fields[$this->config['time']];
        $this->dataArr[$time] = $fields;
    }
    
    protected function got(){
        krsort($this->dataArr);
        return array_slice($this->dataArr,200);
    }

    public function getHtml(){
        return file_get_contents(__DIR__ .'/index.html');
    }
}