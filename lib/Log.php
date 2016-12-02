<?php
class Log{
    private $config,$mainIndex,$logFormat,$fieldPos;
    private $select,$sum,$count,$from,$where;
    private $stime,$etime,$period;

    public function __construct(){
        $this->etime = time();
        $this->stime = $this->etime - 3600;
        $this->period = 1;
        $this->where = [];
    }

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
        $this->mainIndex = $this->_makeMainIndex($mainIndex);
    }

    /*
     * make index for every file by table
     */
    private function _makeIndex($file,$logFormat,$tablePos,$timePos){
        $stime = 0;
        $etime = 0;
        $index = [];
        $fp = fopen($file,'r');
        while(!feof($fp)){
            $pos = ftell($fp);
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
            $index[$table][] = $pos;
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

    /*
     * make index for files by time
     */
    private function _makeMainIndex(array $index){
        $mainIndex = [];
        $fileName = $this->config['rootPath'].$this->config['dataDir'].'/main.index';
        if(file_exists($fileName)){
            $mainIndex = json_decode(file_get_contents($fileName));
            foreach($mainIndex as $file=>$arr){
                if(!file_exists($file)){
                    unset($mainIndex[$file]);
                }
            }
        }
        if(empty($index)){
            return;
        }
        $mainIndex = array_merge($mainIndex,$index);
        $this->mainIndex = $mainIndex;
        file_put_contents($fileName, json_encode($mainIndex));
        return $mainIndex;
    }

    public function __set($name,$val){
        //$period 小时
        if($name == 'period'){
            $val *= 3600;
        }
        $this->$name = $val;
    }

    private function getLogFiles($stime,$etime){
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
        foreach($where as $field=>$value){
            if($match[$field] != $value){
                return false;
            }
        }
        return true;
    }
    
    private function getPos(array $index,$table){
        $pos = [];
        foreach($index as $_table=>$_pos){
            if(strpos($table,$_table)===0){
                $pos = array_merge($pos,$_pos);
            }
        }
        return $pos;
    }

    public function get(){
        $logFiles = $this->getLogFiles($this->stime,$this->etime);
        $table = $this->table;
        $fieldPos = $this->fieldPos;
        $period = $this->period;
        $limit = 200;
        $res = 0;
        $_time = 0;
        $xData = [];
        $yData = [];
        foreach($logFiles as $file){
            if(!is_file($file)){
                continue;
            }
            
            $index = json_decode(file_get_contents($file.'.index'),true);
            $posArr = $this->getPos($index,$table);
            $logFp = fopen($file,'r');
            foreach($posArr as $pos){
                fseek($logFp,$pos);
                $log = fgets($logFp);
                $fields = $this->buildFields($log);
                $time = $fields[$this->config['time']];
                if(!is_numeric($time)){
                    $time = strtotime($time);
                }
                if(!$this->filterWhere($fields)){
                    continue;
                }

                //count
                if($this->count){
                    $res++;
                }

                //sum
                elseif($this->sum){
                    $res += $fields[$this->sum];
                }

                //period
                $_time == 0 && $_time = $time;
                if($this->period && ($time - $_time >= $this->period)){
                    $res = 0;
                    $_time = $time;
                    $xData[] = data('Y-m-d H:i:s',$time);
                    $yData[] = $res;
                }
            }
            fclose($logFp);
        }

        return compact('xData','yData');
    }
}