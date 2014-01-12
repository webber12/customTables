<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
 * Onetable Route
 *
 * Плагин для кастомной маршрутизации
 *
 * @license     GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author      Agel_Nash <Agel_Nash@xaker.ru>
 * @version     0.1
 *
 * @internal        @events         OnPageNotFound,OnLoadWebDocument,OnLoadWebPageCache
 * @internal        @properties     &docid=ID документа;int;2 &tablename=Имя таблицы;text;content &fieldname=Уникальное поле в таблице;text;id &prefix=Префикс плейсхолдеров документа;text;custom &pkname=PrimaryKey;text;id &sendparent=При просмотре документа перенаправлять на родителя?;list;true,false;true
 */

if(!function_exists(removeSuffix)){
    function removeSuffix($url){
        global $modx;
        $pos = strripos($url, $modx->config['friendly_url_suffix']);
        return ($pos===false) ? rtrim($url) : substr($url, 0, $pos);
    }
}

switch($modx->event->name){
    case 'OnPageNotFound':{
        $brand = '';
        $q = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
        $url = $modx->makeURL($docid);
        //$url = rtrim($url, $modx->config['friendly_url_suffix']);
        $url = removeSuffix($url);
        $url = ltrim($url, '/');
        $tmp = explode('/', $url);
        if(!isset($modx->customDocID) && count($q)==(count($tmp)+1)){
        $endKey = end($q);
            //$find = rtrim($endKey, $modx->config['friendly_url_suffix']);
            $find = removeSuffix($endKey);
            $sql="SELECT id FROM ".$modx->getFullTableName($tablename)." WHERE `".$fieldname."`='".$modx->db->escape($find)."'";
            $q = $modx->db->query($sql);
            if($modx->db->getRecordCount($q)==1 && $find.$modx->config['friendly_url_suffix'] == $endKey){
                $modx->customDocID = (int)$modx->db->getValue($q);
                $modx->sendForward($docid);
            }
        }
        break;
    }
    case 'OnLoadWebDocument':
    case 'OnLoadWebPageCache':{
        if($modx->documentObject['id']==$docid){
            $flag = true;
            if(isset($modx->customDocID) && (int)$modx->customDocID>0){
                $q = $modx->db->query("SELECT * FROM ".$modx->getFullTableName($tablename)." WHERE `".$pkname."`='".$modx->customDocID."'");
                $flag = ($modx->db->getRecordCount($q)==1);
            }else{
                $flag = false;
            }
            if($flag){
                $out = $modx->db->getRow($q);
                $plh = array();
                foreach($out as $key => $data){
                    $plh[$prefix."_".$key] = $data;
                }
                $modx->customDocID = (isset($plh[$prefix."_".$pkname])) ? $plh[$prefix."_".$pkname] : false;
                $modx->documentObject = array_merge($modx->documentObject,$plh);
            }else{
                $modx->customDocID = false;
                if($sendparent=='true'){
                    $url = $modx->makeUrl($modx->documentObject['parent'], '', '', 'full');
                    $modx->sendRedirect($url, 0, 'REDIRECT_HEADER', 'HTTP/1.1 301 Moved Permanently');
                }else{
                    $modx->sendErrorPage();
                }
            }
        }
        break;
    }
}
