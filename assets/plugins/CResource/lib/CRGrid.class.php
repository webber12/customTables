<?php
include_once("CRcore.abstract.php");
class CRGrid extends CRcore{
    /** @var string  Имя jQuery переменной с которой дальше будем работать */
    private $jqname='';
    /** @var string Название папки с плагином */

    private $_template = array();

    /**
     * Функция которая инъектит javascript код сгенерированный функцией {@link render}
     * @see {@link render}
     * @param string $which_jquery Тип подключения jquery к странице (google code | /assets/js/ | custom url | none). По умолчанию /assets/js/
     * @param string $jqname Имя jQuery переменной с которой дальше будем работать. По умолчанию j
     * @param string $url Адрес по которому будем грузить jQuery библиотеку если which_jquery установлен в custom url. По умолчанию пусто.
     * @return string HTML
     */
    private function loadJS($which_jquery='/assets/js/',$jqname='j',$url=''){
        $js_include='';

        $this->jqname=$jqname;
        switch ($which_jquery){
            case 'google code':{
                $js_include  = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
                break;
            }
            case '/assets/js/':{
                $js_include  = '<script src="'.$this->_modx->config['site_url']. '/assets/js/jquery-1.4.4.min.js" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
                break;
            }
            case 'custom url':{
                if($url!=''){
                    $js_include  = '<script src="'.$url.'" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
                }else{
                    $js_include='';
                }
                break;
            }
            default:{ //no include;
            $js_include='';
            }
        }
       return $js_include;
    }

    /**
     * Формируем html с JavaScript'ом для отображения текста в нужных местах
     * @access public
     * @return string HTML
     */
    public function render($which_jquery='/assets/js/',$jqname='j',$url=''){
        $js = $this->loadJS($which_jquery,$jqname,$url);
        if($this->jqname==''){
            exit('ErrorJS');
        }
        $out = $this->template('header');
        $out .= $js;

        $DocID = (isset($_REQUEST['id'])) ? (int)$_REQUEST['id'] : 0;
            $data = array();
            $tpl = $this->loadTPLdata();
            $tmp = "?data={$this->_idConfig}&parent={$DocID}";
            $data['docEditURL'] = MODX_BASE_URL."/".MGR_DIR."/".$this->getOptions(array('docURL','edit'),"index.php?a=27&id=");
            $data['docNewURL'] = MODX_BASE_URL."/".MGR_DIR."/".$this->getOptions(array('docURL','new'),"index.php?a=4");
            $data['saveURL'] = $tpl['dir']."action.php{$tmp}&mode=save";
            $data['updateURL'] = $tpl['dir']."action.php{$tmp}&mode=update";
            $data['delURL'] = $tpl['dir']."action.php{$tmp}&mode=delete";
            $data['listURL'] = $tpl['dir']."action.php{$tmp}&mode=list";
            $out.=$this->gridData($data);

        $out.= $this->template('footer');
        return $out;
    }

    public function checkRules(){
        $DocID = (isset($_REQUEST['id'])) ? (int)$_REQUEST['id'] : 0;
        $rules = $this->getOptions("rules", array());
        $flag = empty($rules);
        if(!$flag && $DocID && $this->loadModClass("modResource")){
            $DOC = new modResource($this->_modx);
            $data = $DOC->edit($DocID)->toArray();
            $flag = true;
            foreach($rules as $item => $value){
                switch($item){
                    case 'id':{
                        if($DOC->getID() != $value){
                            $flag = false;
                        }
                        break;
                    }
                    default:{
                        if(!isset($data[$item]) || $data[$item]!=$value){
                            $flag = false;
                        }
                        break;
                    }
                }
            }
        }
        return $flag;
    }
    private function gridData($data){
        $out = '';
        $data['idField'] = $this->getOptions('idField','id');
		$data['pid'] = isset($_REQUEST['id'])?$_REQUEST['id']:'0';
        $grid = $this->getOptions('grid',array());
        foreach($grid as $item=>$value){
              $name = isset($value['name']) ? $value['name'] : $item;
              $options = isset($value['options']) ? $value['options'] : "field: '{$item}'";
              $out .= "<th data-options=\"{$options}\" sortable=\"true\">{$name}</th>";
        }
        $data['header'] = $out;
		$data['searchData'] = $this->makeSearchData();
	//	$data['searchFields'] = $this->makeSearchFields();
        return $this->template('grid',$data);
    }

    private function loadTPLdata(){
        if(empty($this->_template)){
            $modx = $this->_modx;

            $this->_template['jqname'] = '$'.$this->jqname;
            $this->_template['dir'] = MODX_BASE_URL."/".str_replace(MODX_BASE_PATH,'',$this->_dir)."/";

            if(isset($modx->config['manager_language']) && file_exists(MODX_MANAGER_PATH."includes/lang/".$modx->config['manager_language'].".inc.php")) {

                include MODX_MANAGER_PATH."includes/lang/".$modx->config['manager_language'].".inc.php";
            }
            $this->_template['_lang'] = $_lang;
            $this->_template['modx_lang_attribute'] = $modx_lang_attribute;
            $this->_template['modx_manager_charset'] = $modx_manager_charset;

            if(isset($modx->config['manager_theme']) && file_exists(MODX_MANAGER_PATH."media/style/".$modx->config['manager_theme']."/style.php")) {
                include MODX_MANAGER_PATH."media/style/".$modx->config['manager_theme']."/style.php";
            }
            $this->_template['_style'] = $_style;
            $this->_template['style_path'] = $style_path;
        }
        return $this->_template;
    }

    private function template($name,$data=array()){
        $modx = $this->_modx;
        extract($modx->config, EXTR_OVERWRITE);

        $tpl = $this->loadTPLdata();
        extract($tpl, EXTR_OVERWRITE);

        if(!empty($data)){
            extract($data, EXTR_OVERWRITE);
        }

        if(file_exists($this->_dir.'/template/'.$name.'.inc.php')){
            ob_start();
            include $this->_dir.'/template/'.$name.'.inc.php';
            $out = ob_get_contents();
            ob_end_clean();
        }else{
            $out = '';
        }
        return $out;
    }
    private function makeSearchData($searchScripts = '', $searchFlds = ''){
        $searchFields = $this->getOptions('searchFields', array());
        $tmp = array();
        $tmp2 = array();
        if(!empty($searchFields)){
            foreach($searchFields as $key => $value){
                $tmp[] = 'search_'.$key.': $'.$this->jqname.'(\'#search_'.$key.'\').val()';
                $tmp2[] = '<label>'.$value['name'].' <input id="search_'.$key.'" style="width:100px"></label>';
            }
            $searchScripts = implode(',', $tmp);
            $searchFlds = implode('', $tmp2);
        }
        if($searchScripts != ''){
            $searchScripts = '$'.$this->jqname.'(\'#dataGrid\').datagrid(\'load\',{'.$searchScripts.'});';
        }
        if($searchFlds != ''){
            $searchFlds .= '<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="findBtn()">Найти</a>';
        }
        $searchData['scripts']=$searchScripts;
        $searchData['fields']=$searchFlds;
        return $searchData;
    }

}
