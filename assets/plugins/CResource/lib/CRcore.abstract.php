<?php
abstract class CRcore{
    public $_modx = null;
    public $_config = array();
    public $_idConfig = null;
    public $_PKfield = 'id';
    public $_table = 'site_content';
    public $_dir='';

    public function __construct($modx, $config){

        $this->_modx = $modx;
        if(!is_object($this->_modx)){
            exit(langVer::err_nomodx);
        }

        $this->_dir = str_replace("\\","/",dirname(dirname(__FILE__)));

        $tmp = $this->loadConfig($config);
        $this->_config = isset($tmp['cfg']) ? $tmp['cfg'] : array();

        $this->_idConfig = isset($tmp['name']) ? $tmp['name'] : null;

        $this->_table = $this->getOptions('table','site_content');
        $this->_PKfield = $this->getOptions('idField','id');

    }
    public static function objectToArray($array){
        foreach($array as $key => &$value ){
            if($value instanceof stdClass) $value = self::objectToArray($value);
        }
        return (array)$array;
    }

    protected function loadModClass($class){
        if(!class_exists($class) && file_exists(dirname(__FILE__)."/MODxAPI/".$class.".php")){
            require_once(dirname(__FILE__)."/MODxAPI/".$class.".php");
        }
        return class_exists($class);
    }

    //$this->getOptions(array('DocLister','api','2'), 'none')
    public function getOptions($name,$default=null, $from = null){
        $out = null;

        $from = isset($from) ? $from : $this->_config;
        if(is_array($name)){
            $flag = true;
            foreach($name as $item){
                if(isset($from[$item])){
                    $from = $from[$item];
                }else{
                    $out = $default;
                    $flag = false;
                    break;
                }
            }
            if($flag){
                $out = $from;
            }
        }else{
            $out = (!empty($name) && isset($from[$name])) ? $from[$name] : $default;
        }
        return $out;
    }

    public function setOptions($name,$value){
        $this->_config[$name] = $value;
    }

    public function loadConfig($cfg){
        $out = array();
        if($cfg!='' && is_scalar($cfg) && !stristr($cfg,"..")){


            $cfg = preg_replace('/[^A-Za-z0-9_\-]/','',$cfg);

            if(file_exists($this->_dir."/config/".$cfg.".data.json")){
                $out['name'] = $cfg;
                $json = file_get_contents($this->_dir."/config/".$cfg.".data.json");
                $json = json_decode($json);

                $out['cfg'] = ($json instanceof stdClass) ? $this->objectToArray($json) : array();
            }
        }
        return $out;
    }

}