<?php
class Log{
    protected $config,$mainIndex,$logFormat,$fieldPos,$errorLog='';
    protected $sum,$count,$distinct,$table,$where,$whereSign,$group;
    protected $stime,$etime,$period;
    
    public function __construct($config){
        $this->config = $config;
    }

    public function makeIndex(){
        $config = $this->config;
        $logFormat = str_replace(array_keys($config['formatReg']),array_values($config['formatReg']),$config['logFormat']);
        $logFormat = "/".$logFormat."/i";
        $this->logFormat = $logFormat;
        $fieldPos = $config['logFormatAs'];
        $fieldPos = array_flip($fieldPos);
        $this->fieldPos = $fieldPos;
        $tablePos = $fieldPos[$config['table']];
        $timePos = $fieldPos[$config['time']];
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
                $mainIndex[$_file] = $this->_makeIndex($_file,$logFormat,$tablePos,$timePos);
                $mainIndexChanged = true;
            }
            closedir($dh);
        }
        closedir($_dh);
        $this->mainIndex = $mainIndex;
        if($mainIndexChanged){
            $this->saveMainIndex($mainIndex);
        }
        $this->writeErrorLog();
    }

    protected function writeErrorLog(){
        if(empty($this->errorLog)){
            return;
        }
        file_put_contents($this->config['dataDir'].'/error.log', $this->errorLog, FILE_APPEND);
    }

    /*
     * make index for files by table
     */
    protected function _makeIndex($file,$logFormat,$tablePos,$timePos){
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
            $res = preg_match($logFormat, $line, $match);
            if(!$res){
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
            'mtime'=>filemtime($file),
            'stime'=>strtotime($stime),
            'etime'=>strtotime($etime),
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

    public function __set($name,$val){
        is_string($val) && $val = trim($val);
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
            case 'where':
                $_val = $val;
                $val = [];
                $whereSign = [];
                foreach($_val as $field=>$value){
                    $pos = strpos($field,'#');
                    $sign = '';
                    if($pos){
                        $sign = substr($field,$pos+1);
                        $field = substr($field,0,$pos);
                    }else{
                        $sign = '=';
                    }
                    $val[$field] = $value;
                    $whereSign[$field] = $sign == '==' ? '=' : $sign;
                }
                $this->whereSign = $whereSign;
                break;
        }
        
        $this->$name = $val;
    }

    protected function getLogFiles($stime,$etime){
        $logFiles = [];
        foreach($this->mainIndex as $file=>$fileIndex){
            if($etime >= $fileIndex['stime'] && $stime <= $fileIndex['etime']){
                $logFiles[$file] = $fileIndex['tables'];
            }
        }
        return $logFiles;
    }
    
    protected function buildFields($log){
        preg_match($this->logFormat, $log, $match);
        if(!$match){
            return $fields;
        }
        array_shift($match);
        $fields = array_combine($this->config['logFormatAs'],$match);
        
        if(isset($this->config['url_query'])){
            $http_query = $this->config['url_query'];
            if($fields[$http_query]){
                $query = explode('&',$fields[$http_query]);
                foreach($query as $v){
                    $pos = strpos($v,'=');
                    if($pos===false){
                        continue;
                    }
                    $fields[$http_query.'.'.substr($v,0,$pos)] = substr($v,$pos+1);
                }
                unset($fields[$http_query]);
            }
        }
        return $fields;
    }

    protected function filterWhere(&$match){
        $where = $this->where;
        $whereSign = $this->whereSign;
        foreach($where as $field=>$value){
            $sign = isset($whereSign[$field]) ? $whereSign[$field] : '=';
            if(!isset($match[$field])){
                return false;
            }
            $_value = $match[$field];
            if($sign == '<' && $_value >= $value){
                return false;
            }elseif($sign == '<=' && $_value > $value){
                return false;
            }elseif($sign == '>' && $_value <= $value){
                return false;
            }elseif($sign == '>=' && $_value < $value){
                return false;
            }elseif($sign == '=' && $_value !== $value){
                return false;
            }elseif($sign == 'like'){
                extract($this->prepareLike($value));//$string,$start,$end
                $value = $string;
                $len = strlen($table);
                if(!$this->matchLike($_value,$value,$start,$end,$len)){
                    return false;
                }
            }
        }
        return true;
    }
    
    /*
     * @indexFile 索引文件名
     * @tables 日志文件包含的所有table
     * @table 要查找的table
     */
    protected function getPos($indexFile,$tables,$table){
        if($table == '*'){
            $pos = unpack('I*',file_get_contents($indexFile));
            sort($pos);
            return $pos;
        }

        $pos = [];
        extract($this->prepareLike($table));//$string,$start,$end
        $table = $string;
        $len = strlen($table);

        $fp = fopen($indexFile,'r');
        foreach($tables as $_table=>$ipos){
            if(!$this->matchLike($_table,$table,$start,$end,$len)){
                continue;
            }
            fseek($fp,$ipos[0]);
            $_pos = unpack('I*',fread($fp,$ipos[1]-$ipos[0]));
            $pos = array_merge($pos,array_values($_pos));
        }
        fclose($fp);
        return $pos;
    }
    
    protected function prepareLike($string){
        $start = true; //前面严格匹配
        $end = true; //后面严格匹配
        $len = strlen($string);
        if($string[$len-1] == '%'){
            $end = false;
            $string = substr($string,0,$len-1);
        }
        if($string[0] == '%'){
            $start = false;
            $string = substr($string,1,strlen($string));
        }
        return compact('string','start','end');
    }
    
    protected function matchLike($string,$str,$start,$end,$len){
        $i = strpos($string,$str);
        return !($i === false || ($start && $i!=0) || ($end && (strlen($string)-$i)!=$len));
    }
    
    protected function filterInput(){
        isset($_GET['sum']) && $this->__set('sum', $_GET['sum']);
        isset($_GET['count']) && $_GET['count']!='false' && $this->__set('count', $_GET['count']);
        isset($_GET['distinct']) && $this->__set('distinct', $_GET['distinct']);
        isset($_GET['table']) && $this->__set('table', $_GET['table']);
        isset($_GET['period']) && $this->__set('period',$_GET['period']);
        isset($_GET['stime']) && $this->__set('stime', $_GET['stime']);
        isset($_GET['etime']) && $this->__set('etime', $_GET['etime']);
        isset($_GET['datetimerange']) && $this->__set('datetimerange', $_GET['datetimerange']);
        isset($_GET['group']) && $this->__set('group', $_GET['group']);
        if(isset($_GET['where_f']) && isset($_GET['where_v'])){
            $this->__set('where', array_combine($_GET['where_f'], $_GET['where_v']));
        }
    }

    protected function prepareQuery(){
        !$this->etime && $this->etime = time();
        !$this->stime && $this->stime = $this->etime - 3600;
        !$this->period && $this->period = 3600;
        !$this->where && $this->where = [];
        !$this->whereSign && $this->whereSign = [];
        !$this->table && $this->table = '*';
    }
    
    /*
     * 得到时间区间数组
     */
    protected function getPeriodArr($stime,$etime,$period){
        $periodArr = [];
        for($i=$stime+$period; $i<=$etime; $i+=$period){
            $periodArr[] = $i;
        }
        $periodArr[] = $i;
        return $periodArr;
    }

    protected function beforeGet(){
        if(!$this->group){
            $periodArr = $this->getPeriodArr($this->stime,$this->etime,$this->period);
            $dataArr = array_fill(0,count($periodArr),0);
            $this->periodArr = $periodArr;
        }else{
            $dataArr = [];
        }
        
        if($this->distinct){
            $this->lastDataKey = '';
        }
        $this->dataArr = $dataArr;
    }
    
    protected function getFields($fields){
        $group = $this->group;
        $stime = $this->stime;
        $period = $this->period;
        $time = $fields[$this->config['time']];
        if(!is_numeric($time)){
            $time = strtotime($time);
        }
        
        $key = $group ? $fields[$group] : intval(($time - $stime)/$period);
        if($this->count){
            $value = 1;
        }elseif($this->sum){
            $value = $fields[$this->sum];
        }elseif($this->distinct){
            $value = $fields[$this->distinct];
            if($key != $this->lastDataKey){
                $this->lastDataKey = $key;
                $this->distinctArr = [];
            }
            if(!in_array($value,$this->distinctArr)){
                $this->dataArr[$key] += 1;
                $this->distinctArr[] = $value;
            }
        }

        if(isset($this->dataArr[$key])){
            $this->dataArr[$key] += $value;
        }else{
            $this->dataArr[$key] = $value;
        }
    }
    
    protected function got(){
        $xData = $this->group ? array_keys($this->dataArr) : array_map(function($v){return date('Y-m-d H:i:s',$v);}, $this->periodArr);

        return [
            'xData' => $xData,
            'yData' => array_values($this->dataArr)
        ];
    }
    
    public function get(){
        ini_set('memory_limit','1024M');
        $this->filterInput();
        $this->prepareQuery();
        $this->beforeGet();
        $stime = $this->stime;
        $etime = $this->etime;
        $table = $this->table;
        $logFiles = $this->getLogFiles($stime,$etime);

        foreach($logFiles as $file=>$tables){
            if(!is_file($file)){
                continue;
            }
            
            $posArr = $this->getPos($file.'.index',$tables,$table);
            $logFp = fopen($file,'r');
            $logs = [];
            foreach($posArr as $pos){
                fseek($logFp,$pos);
                $log = fgets($logFp);
                if($log){
                    $logs[] = $log;
                }
            }
            fclose($logFp);
            foreach($logs as $log){
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

                $this->getFields($fields);
            }
        }

        return $this->got();
    }
    
    protected function writeLog($file,$log){
        if($log===''){
            return;
        }
        file_put_contents($file,$log,FILE_APPEND);
    }
    
    public function getHtml(){
        return file_get_contents($this->config['rootPath'].'/view/index.html');
    }
}