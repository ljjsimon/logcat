<?php
class Llist extends Log{
    protected function filterInput($input){
        isset($input['table']) && $this->__set('table', $input['table']);
        isset($input['datetimerange']) && $this->__set('datetimerange', $input['datetimerange']);
        if(isset($input['where_f']) && isset($input['where_v'])){
            $this->__set('where', array_combine($input['where_f'], $input['where_v']));
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
        $time = strtotime($fields[$this->config['time']]);
        $this->dataArr[$time] = ['log'=>implode(" ", $fields)];
    }
    
    protected function getReduceData(){
        return $this->dataArr;
    }
    
    protected function reduceLog($results){
        $dataArr = call_user_func_array('array_merge',$results);
        krsort($dataArr);
        return array_slice($dataArr,0,200);
    }

    public function getHtml(){
        return file_get_contents(__DIR__ .'/index.html');
    }
}