<?php
class Cache{
    private $cache=[],$expire=[];

    public function set($key,$value){
        $this->cache[$key] = $value;
        $this->expire[$key] = [
            'updated_at' => time(),
            'expire' => 600 + rand(5,60)
        ];
    }
    
    public function get($key){
        $now = time();
        $expire = $this->expire['expire'];
        if($expire && ($expire+$this->expire[$key]['updated_at'] < $now)){
            unset($this->cache[$key]);
            unset($this->expire[$key]);
        }
        return isset($this->cache[$key]) ? $this->cache[$key] : false;
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
}
