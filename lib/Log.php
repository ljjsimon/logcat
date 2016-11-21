<?php
class Log{
    private $config,$mainIndex,$logFormat,$fieldPos;
    private $select,$from,$where;
    private $stime,$etime,$period;

    public function makeIndex($config){
        $this->config = $config;
        $logFormat = str_replace(array_keys($config['formatReg']),array_values($config['formatReg']),$config['logFormat']);
        $logFormat = "/".$logFormat."/i";
        $this->logFormat = $logFormat;
        $fieldPos = $config['logFormatAs'];
        $fieldPos = array_flip($fieldPos);
        $this->fieldPos = $fieldPos;
        $tablePos = $fieldPos['table'];
        $timePos = $fieldPos['time'];
        $mainIndex = [];
        //一级目录
        $dir = $config['rootPath'].$config['dataDir'];
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
                if($extension == 'index' || file_exists($_file.'.index')){
                    continue;
                }
                $mainIndex = array_merge($mainIndex, $this->_makeIndex($_file,$logFormat,$tablePos,$timePos));
            }
            closedir($dh);
        }
        closedir($_dh);
        $this->mainIndex = $this->_makeMainIndex($mainIndex);
    }

    private function _makeIndex($file,$logFormat,$tablePos,$timePos){
        $stime = 0;
        $etime = 0;
        $index = [];
        $fp = fopen($file,'r');
        while(!feof($fp)){
            $line = fgets($fp);
            if(empty($line)){
                continue;
            }
            preg_match($logFormat, $line, $match);
            $table = $match[$tablePos+1];
            $etime = $match[$timePos+1];
            if(!$stime){
                $stime = $etime;
            }

            if(!array_key_exists($table, $index)){
                $index[$table] = [];
            }
            $index[$table][] = ftell($fp);
        }
        fclose($fp);
        file_put_contents($file.'.index', json_encode($index));
        return [
            $file => [
                'stime'=>strtotime($stime),
                'etime'=>strtotime($etime)
            ]
        ];
    }

    private function _makeMainIndex(array $index){
        $mainIndex = [];
        $fileName = $this->config['rootPath'].$this->config['dataDir'].'/main.index';
        if(file_exists($fileName)){
            $mainIndex = json_decode(file_get_contents($fileName));
        }
        $this->mainIndex = $mainIndex;
        if(empty($index)){
            return;
        }
        $mainIndex = array_merge($mainIndex,$index);
        file_put_contents($fileName, json_encode($mainIndex));
        return $mainIndex;
    }

    public function select($select){
        if(!is_array($select)){
            $select = [$select];
        }
        $this->select = $select;
    }

    public function table($table){
        $this->table = $table;
    }

    public function where(array $where){
        foreach($where as $k=>$v){
            switch($k){
                case 'stime':
                    $this->stime = $v;
                    break;
                case 'etime':
                    $this->etime = $v;
                    break;
            }
        }
        $this->where = $where;
    }

    private function prepareQuery(){
        if(!$this->select){
            throw new Exception;
        }
        $now = time();
        if(!$this->stime){
            $this->stime = $now - 24*3600;
        }
        if(!$this->etime){
            $this->etime = $now;
        }
    }

    private function getLogFiles(){
        $stime = $this->stime;
        $etime = $this->etime;
        $logFiles = [];
        foreach($this->mainIndex as $file=>$timeArr){
            if($etime >= $timeArr['stime']){
                $indexFile[] = $file;
            }
            if($stime <= $timeArr['etime']){
                $indexFile[] = $file;
            }
        }
        return array_unique($logFiles);
    }

    private function filterWhere($match){
        $where = $this->where;
        $fieldPos = $this->fieldPos;
        foreach($where as $field=>$value){
            $pos = $fieldPos[$field] + 1;
            if($match[$pos] != $value){
                return false;
            }
        }
        return true;
    }

    public function period($period){
        $this->period = $period;
    }

    public function get(){
        $this->prepareQuery();
        $logFiles = $this->getLogFiles();
        $table = $this->table;
        $fieldPos = $this->fieldPos;
        $rows = [];
        $limit = 200;
        foreach($logFiles as $file){
            if(!is_file($file)){
                continue;
            }
            
            $index = json_decode(file_get_contents($file.'.index'),true);
            $posArr = $index[$table];
            $logFp = fopen($file,'r');
            foreach($posArr as $pos){
                fseek($logFp,$pos);
                $log = fgets($logFp);
                preg_match($this->logFormat, $log, $match);
                if(!$this->filterWhere($match)){
                    continue;
                }
                $row = [];
                foreach($this->select as $select){
                    $pos = $fieldPos[$field] + 1;
                    $row[$select] = $match[$pos];
                }
                $rows[] = $row;
            }
            fclose($logFp);
        }

        return $rows;
    }
}