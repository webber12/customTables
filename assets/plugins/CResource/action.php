<?php
include_once(dirname(__FILE__) . "/_modx.php");
include_once(dirname(__FILE__) . "/_config.php");

$param = array_merge($param, (isset($json->DocLister) ? (array)$json->DocLister : array()));

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : null;
switch($mode){
    case 'update':{
        if($id = $CRdata->update()){
            $param['addWhereList'] = $CRdata->getOptions('idField','id')." = '".$id."'";
            $out = $modx->runSnippet("DocLister", $param);
            $out = json_decode($out);
            $out = (array)$out->rows;
            $out = json_encode($out[0]);
        }else{
            $out = array();
            $out['error'] = "Ошибка обновления записи";
            $out = json_encode($out);
        }
        break;
    }
    case 'save':{
        if($id = $CRdata->create()){
            $param['addWhereList'] = $CRdata->getOptions('idField','id')." = '".$id."'";
            $out = $modx->runSnippet("DocLister", $param);
            $out = json_decode($out);
            $out = (array)$out->rows;
            $out = json_encode($out[0]);
        }else{
            $out = array();
            $out['error'] = "Ошибка сохранения записи";
            $out = json_encode($out);
        }
        break;
    }
    case 'delete':{
        if($id = $CRdata->delete()){
            $out = array();
            $out['success'] = true;
            /*
             * @TODO: нужно добавить в WHERE к DocLister ID документа который мы ищим.
             * Имя поля ID: $CData->getOptions('idField','id');
             * Значение: $id
             * Пример ответа: {"id":13682,"firstname":"ghg2","lastname":"ghghg","phone":"g","email":"ghh@qsd.qs"}
             */
        }else{
            $out = array();
            $out['error'] = "Ошибка удаления записи";
        }
        $out = json_encode($out);
        break;
    }
    case 'list':{
        $out = '';
        $default = $CRdata->getOptions('DocLister',array());

        //$display = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 10;
        $display = isset($default['display']) ? (int)$default['display'] : 10;
        $offset = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $offset = $display*($offset-1);

        $param['display'] = $display;
        $param['offset'] = $offset;

        if(isset($_REQUEST['sort'])){
            $sort = $CRdata->renameData($_REQUEST['sort']);
            $param['sortBy'] = preg_replace('/[^A-Za-z0-9_\-]/', '', $sort);
            if(''==$param['sortBy']){
                unset($param['sortBy']);
            }
        }
        if(isset($_REQUEST['order']) && in_array(strtoupper($_REQUEST['order']), array("ASC","DESC"))){
            $param['sortDir'] = $_REQUEST['order'];
        }
        $param = array_merge($param, $default);

        $param['idField'] = $CRdata->getOptions('idField','id');
        $tmp = $CRdata->getOptions('parentField',null);

        if(isset($_REQUEST['parent']) && !empty($tmp) && (int)$_REQUEST['parent']>=0){
            $param['addWhereList'] = $tmp." = '".(int)$_REQUEST['parent']."'";
        }

        $filters = $CRdata->makeFilters($_REQUEST);
        if(!empty($filters)){
            $fs = implode(";", $filters);
            $param['filters'] = 'AND('.$fs.')';
        }

        $out=$modx->runSnippet("DocLister",$param);
        break;
    }
}

echo $out;
