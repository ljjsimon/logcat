<?php
class Cache{
    private $cache=[],$expire=[];

    public function set($key,$value){
        $this->cache[$key] = $value;
        $this->expire[$key] = [
            'updated_at' => time(),
            'expire' => 0
        ];
    }
    
    public function get($key){
        if(!isset($this->expire[$key])){
            return null;
        }
        $now = time();
        $expire = $this->expire[$key]['expire'];
        if($expire && ($expire+$this->expire[$key]['updated_at'] < $now)){
            unset($this->cache[$key]);
            unset($this->expire[$key]);
        }
        return isset($this->cache[$key]) ? $this->cache[$key] : null;
    }
    
    public function loopExpire(){
        $now = time();
        foreach($this->expire as $key=>$data){
            if($data['updated_at']+$data['expire']>$now){
            }
            unset($this->expire[$key]);
            unset($this->cache[$key]);
        }
    }
    
    public function expire($key,$sec){
        $this->expire[$key]['expire'] = $sec;
    }
    
    public function del($key){
        unset($this->expire[$key]);
        unset($this->cache[$key]);
    }
}
