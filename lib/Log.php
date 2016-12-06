<?php
class Log{
    private $config,$mainIndex,$logFormat,$fieldPos,$errorLog=[];
    private $select,$sum,$count,$table,$where;
    private $stime,$etime,$period;

    public function makeIndex($config){
        $this->config = $config;
        $logFormat = str_replace(array_keys($config['formatReg']),array_values($config['formatReg']),$config['logFormat']);
        $logFormat = "/".$logFormat."/i";
        $this->logFormat = $logFormat;
        $fieldPos = $config['logFormatAs'];
        $fieldPos = array_flip($fieldPos);
        $this->fieldPos = $fieldPos;
        $tablePos = $fieldPos[$config['table']];
        $timePos = $fieldPos[$config['time']];
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
        $this->mainIndex = $this->_makeMainIndex($this->config['rootPath'].$this->config['dataDir'].'/main.index',$mainIndex);
        $this->writeErrorLog();
    }

    private function writeErrorLog(){
        if(empty($this->errorLog)){
            return;
        }
        file_put_contents($this->config['dataDir'].'/error.log', $this->errorLog, FILE_APPEND);
    }

    /*
     * make index for files by table
     */
    private function _makeIndex($file,$logFormat,$tablePos,$timePos){
        $stime = 0;
        $etime = 0;
        $index = [];
        $fp = fopen($file,'r');
        $l=0;
        while(!feof($fp)){
            $l++;
            $pos = ftell($fp);
            $line = fgets($fp);
            if(empty($line)){
                continue;
            }
            preg_match($logFormat, $line, $match);
            if(empty($match)){
                $this->errorLog .= "$file, line $l\n";
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
        fclose($fp);

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
            $file => [
                'stime'=>strtotime($stime),
                'etime'=>strtotime($etime),
                'tables' => $tables
            ]
        ];
    }

    /*
     * make index for files by time
     */
    private function _makeMainIndex($fileName,array $index){
        $mainIndex = [];
        
        if(file_exists($fileName)){
            $mainIndex = json_decode(file_get_contents($fileName),true);
            foreach($mainIndex as $file=>$arr){
                if(!file_exists($file)){
                    unset($mainIndex[$file]);
                }
            }
        }
        
        if(!empty($index)){
            $mainIndex = array_merge($mainIndex,$index);
            file_put_contents($fileName, json_encode($mainIndex));
        }
        return $mainIndex;
    }

    public function __set($name,$val){
        if($val === ''){
            return;
        }
        switch($name){
            case 'period':
                //$period 小时
                $val *= 3600;
                break;
            case 'datetimerange':
                $this->stime = strtotime($val[0]);
                $this->etime = strtotime($val[1]);
                break;
            case 'stime':
            case 'etime':
                if(!is_numeric($val)){
                    $val = strtotime($val);
                }
                break;
        }
        
        $this->$name = $val;
    }

    private function getLogFiles($stime,$etime){
        $logFiles = [];
        foreach($this->mainIndex as $file=>$fileIndex){
            if($etime >= $fileIndex['stime'] && $stime <= $fileIndex['etime']){
                $logFiles[$file] = $fileIndex['tables'];
            }
        }
        return $logFiles;
    }
    
    private function buildFields($log){
        $fields = [];
        preg_match($this->logFormat, $log, $match);
        if(!$match){
            return $fields;
        }
        $fieldPos = $this->fieldPos;
        foreach($fieldPos as $name=>$pos){
            $field = $match[$pos+1];
            if($name==$this->config['http_query']){
                parse_str($field,$field);
            }
            $fields[$name] = $field;
        }
        return $fields;
    }

    private function filterWhere($match){
        $where = $this->where;
        if(isset($this->config['http_query'])){
            $http_query = $this->config['http_query'];
            if($http_query && $match[$http_query]){
                $query = explode('&',$match[$http_query]);
                foreach($query as $v){
                    $pos = $strpos($v,'=');
                    $match[$http_query.'.'.substr($v,0,$pos)] = $substr($v,$pos+1);
                }
            }
        }
        
        foreach($where as $field=>$value){
            if($match[$field] != $value){
                return false;
            }
        }
        return true;
    }
    
    private function getPos($indexFile,$tables,$table){
        $pos = [];
        foreach($tables as $_table=>$ipos){
            if($table == '*' || strpos($_table,$table)!==false){
                $fp = fopen($indexFile,'r');
                fseek($fp,$ipos[0]);
                $_pos = unpack('I*',fread($fp,$ipos[1]-$ipos[0]));
                fclose($fp);
                $pos = array_merge($pos,array_values($_pos));
            }
        }
        return $pos;
    }

    private function prepareQuery(){
        !$this->etime && $this->etime = time();
        !$this->stime && $this->stime = $this->etime - 3600;
        !$this->period && $this->period = 3600;
        !$this->where && $this->where = [];
        !$this->table && $this->table = '*';
    }
    
    /*
     * 得到时间区间数组
     */
    private function getPeriodArr($stime,$etime,$period){
        $periodArr = [];
        for($i=$stime+$period; $i<=$etime; $i+=$period){
            $periodArr[] = $i;
        }
        $periodArr[] = $i;
        return $periodArr;
    }

    public function get(){
        $this->prepareQuery();
        $stime = $this->stime;
        $etime = $this->etime;
        $period = $this->period;
        $table = $this->table;
        $fieldPos = $this->fieldPos;
        $periodArr = $this->getPeriodArr($stime,$etime,$period);
        $dataArr = array_fill(0,count($periodArr),0);
        $logFiles = $this->getLogFiles($stime,$etime);
        foreach($logFiles as $file=>$tables){
            if(!is_file($file)){
                continue;
            }
            
            $posArr = $this->getPos($file.'.index',$tables,$table);
            $logFp = fopen($file,'r');
            foreach($posArr as $pos){
                fseek($logFp,$pos);
                $log = fgets($logFp);
                $fields = $this->buildFields($log);
                $time = $fields[$this->config['time']];
                if(!is_numeric($time)){
                    $time = strtotime($time);
                }
                if($time > $etime || $time < $stime){
                    continue;
                }
                if(!$this->filterWhere($fields)){
                    continue;
                }
                
                $timePos = intval(($time - $stime)/$period);
                //count
                if($this->count){
                    $dataArr[$timePos]++;
                }

                //sum
                elseif($this->sum){
                    $dataArr[$timePos] += $fields[$this->sum];
                }
            }
            fclose($logFp);
        }

        return [
            'xData' => array_map(function($v){return date('Y-m-d H:i:s',$v);}, $periodArr),
            'yData' => $dataArr
        ];
    }
}