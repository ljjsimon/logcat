<?php
class Index{
    protected $config,$cache,$logFormat;
    protected $tablePos,$timePos;
    
    public function __construct($config, $cache){
        $this->config = $config;
        $this->cache = $cache;

        $logFormat = str_replace(array_keys($config['formatReg']),array_values($config['formatReg']),$config['logFormat']);
        $logFormat = "/".$logFormat."/i";
        $this->logFormat = $logFormat;
        $fieldPos = $config['logFormatAs'];
        $fieldPos = array_flip($fieldPos);
        $this->tablePos = $fieldPos[$config['table']];
        $this->timePos = $fieldPos[$config['time']];
    }

    public function makeIndex(){
        $config = $this->config;
        $mainIndexChanged = false;
        $mainIndex = $this->getMainIndex($mainIndexChanged);
        //一级目录
        $dir = $config['rootPath'].'/'.$config['dataDir'];
        $_dh = opendir($dir);
        
        while(($_dir = readdir($_dh)) !== false){
            if($_dir == '.' || $_dir == '..' || $_dir == '.DS_Store'){
                continue;
            }
            $sub_dir = $dir.'/'.$_dir;
            if(!is_dir($sub_dir)){
                continue;
            }

            //二级目录
            $dh = opendir($sub_dir);

            while(($file = readdir($dh)) !== false){
                $_file = $sub_dir.'/'.$file;
                if(!is_file($_file) || $file == '.DS_Store'){
                    continue;
                }
                $extension = pathinfo($file,PATHINFO_EXTENSION);
                if($extension == 'index' || array_key_exists($_file,$mainIndex)){
                    continue;
                }
                $mainIndex[$_file] = $this->_makeIndex($_file);
                $mainIndexChanged = true;
            }
            closedir($dh);
        }
        closedir($_dh);
        $this->cache->set('mainIndex', $mainIndex);
        if($mainIndexChanged){
            $this->saveMainIndex($mainIndex);
        }
    }

    /*
     * unpack index into an array
     */
    private function unpackIndex($file){
        if(!is_file($file.'.index')){
            return [];
        }
        $mainIndex = $this->cache->get('mainIndex');
        if(!isset($mainIndex[$file]['tables'])){
            return [];
        }
        $index = [];
        $fp = fopen($file.'.index');
        foreach($mainIndex[$file]['tables'] as $table => $ipos){
            fseek($fp,$ipos[0]);
            $pos = unpack('I*',$fp,$ipos[1]-$ipos[0]);
            $index[$table] = array_values($pos);
        }
        return $index;
    }

    /*
     * make index for files by table
     */
    protected function _makeIndex($file,$epos = 0){
        $logFormat = $this->logFormat;
        $tablePos = $this->tablePos;
        $timePos = $this->timePos;
        $stime = 0;
        $etime = 0;
        $index = $this->cache->get($file);
        !is_array($index) && $index = [];
        $fp = fopen($file,'r');
        if(!$fp){
            return [];
        }

        //if true, it's append to a index
        if($epos){
            fseek($fp, $epos);
            if(!$index){
                $index = $this->unpackIndex($file);
            }
        }
        while(!feof($fp)){
            $pos = ftell($fp);
            $line = trim(fgets($fp));
            if(empty($line)){
                continue;
            }
            $res = preg_match($logFormat, $line, $match);
            if(!$res){
                $this->writeLog($this->config['dataDir'].'/error.log',"$file, pos $pos\n");
                continue;
            }
            $table = $match[$tablePos+1];
            $etime = $match[$timePos+1];
            if(!$stime){
                $stime = $etime;
            }

            if(!array_key_exists($table, $index)){
                $index[$table] = [];
            }
            $index[$table][] = $pos;
        }
        $epos = ftell($fp);
        fclose($fp);
        $this->cache->set($file,$index);
        $this->cache->expire($file, 600 + rand(5,60));

        //compress
        $tables = [];
        $ifp = fopen($file.'.index','w');
        foreach($index as $table=>$posArr){
            array_unshift($posArr,'I*');
            $posStr = call_user_func_array('pack', $posArr);
            $start = ftell($ifp);
            fwrite($ifp, $posStr);
            $end = ftell($ifp);
            $tables[$table] = [$start,$end];
        }
        fclose($ifp);
        return [
            'mtime'=>filemtime($file),
            'stime'=>strtotime($stime),
            'etime'=>strtotime($etime),
            'epos'=>$epos,
            'tables' => $tables
        ];
    }

    /*
     * make index for files by time
     */
    protected function getMainIndex(&$mainIndexChanged){
        $fileName = $this->config['rootPath'].'/'.$this->config['dataDir'].'/main.index';
        if(!file_exists($fileName)){
            return [];
        }
        $mainIndex = json_decode(file_get_contents($fileName),true);
        foreach($mainIndex as $file=>$arr){
            if(!file_exists($file) || filemtime($file) != $arr['mtime']){
                unset($mainIndex[$file]);
                $mainIndexChanged = true;
            }
        }
        return $mainIndex;
    }
    
    protected function saveMainIndex($mainIndex){
        $fileName = $this->config['rootPath'].'/'.$this->config['dataDir'].'/main.index';
        file_put_contents($fileName, json_encode($mainIndex));
    }
    
    protected function writeLog($file,$log){
        if($log===''){
            return;
        }
        file_put_contents($file,$log,FILE_APPEND);
    }

    public function collectLog($file, $log){
        $config = $this->config;
        if($log == ''){
            return;
        }

        $file = $config['rootPath'].'/'.$config['dataDir'].'/'.$file;
        file_put_contents($file,$log,FILE_APPEND);
        $mainIndex = $this->cache->get('mainIndex');
        $epos = isset($mainIndex[$file]['epos']) ? $mainIndex[$file]['epos'] : 0;
        $_mainIndex = $this->_makeIndex($file, $epos);
        if(!isset($mainIndex[$file])){
            $mainIndex[$file] = $_mainIndex;
        }else{
            $mainIndex[$file]['mtime'] = $_mainIndex['mtime'];
            $mainIndex[$file]['etime'] = $_mainIndex['etime'];
            $mainIndex[$file]['epos'] = $_mainIndex['epos'];
            $mainIndex[$file]['tables'] = array_merge($mainIndex[$file]['tables'],$_mainIndex['tables']); 
        }
        $this->cache->set('mainIndex',$mainIndex);
        $this->saveMainIndex($mainIndex);
    }
}