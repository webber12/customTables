<?php
include_once("CRcore.abstract.php");
class CRdata extends CRcore{
    private $_modClass = null;

    function __construct($modx, $config){
        parent::__construct($modx,$config);

        $class = $this->getOptions('class', 'modResource');

        if($this->loadModClass($class)){
            $this->_modClass = new $class($modx);
        }else{
            die('error Load');
        }
    }


    public function renameData($data){
        $rename = $this->getOptions('renameField', null);
        if(is_array($rename)){
            foreach($rename as $rules=>$id){
                $out = array();
                if(is_array($data)){
                    foreach($data as $key => $value){
                        $out[$this->_renameData($rules,$id,$key)] = $value;
                    }
                }else{
                    $out = $this->_renameData($rules, $id, $data);
                }
                $data = $out;
            }
        }
        return $data;
    }
    private function _renameData($rules,$id,$key){
        if(preg_match($rules,$key,$match)){
            $key = $match[$id];
        }
        return $key;
    }
    public function create(){
        if(!empty($_POST)){
            $data = $this->renameData($_POST);
            $this->_modClass->create($data)->save(true, true);
        }else{
            $out = false;
        }
        return $out;
    }

    public function delete(){
        $out = false;
        if(!empty($_POST) && isset($_POST[$this->_PKfield])){
            $this->_modClass->delete($_POST[$this->_PKfield]);
            $out = true;
        }
        return $out;
    }

    public function update(){
        $out = false;
        if(!empty($_POST) && isset($_POST[$this->_PKfield])){
           $data = $this->renameData($_POST);
           if($_POST[$this->_PKfield] == $this->_modClass->edit($_POST[$this->_PKfield])->fromArray($data)->save(true, true)){
                $out = $_POST[$this->_PKfield];
            }
        }
        return $out;
    }
    private function extractByKeyRules($data, $filtered_data = array()){
        $rename = $this->getOptions('renameSearch', false);
        if($rename && is_array($rename)){
            foreach($rename as $rules=>$id){
                $out = array();
                if(is_array($data)){
                    foreach($data as $key => $value){
                        $new_name=$this->_renameData($rules, $id, $key);
                        if($new_name != $key){
                            $filtered_data[$new_name] = $value;
                        }
                    }
                }
            }
        }
        return $filtered_data;
    }
    public function makeFilters($data, $filters = array()){
        $search_fields = $this->getOptions('searchFields', array());
        $filter_fields = $this->extractByKeyRules($data);
        if(is_array($filter_fields) && !empty($filter_fields)){
            foreach($filter_fields as $key => $value){
                if($value != ''){
                    $type=$this->getOptions('searchtype', 'eq', $search_fields[$key]);
                    $name=$this->getOptions('dbname', $key, $search_fields[$key]);
                    $filters[] = 'ct:'.$name.':'.$type.':'.$value;
                }
            }
        }
        return $filters;
    }

}