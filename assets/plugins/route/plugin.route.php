<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
 * CustomTables Route
 *
 * Плагин для кастомной маршрутизации
 *
 * @license     GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author      Agel_Nash <Agel_Nash@xaker.ru>
 * @version     0.1
 *
 * @internal        @events         OnPageNotFound,OnLoadWebDocument,OnLoadWebPageCache
 * @internal        @properties     &docid=ID документов через запятую;text; &tablename=Имя таблицы;text;content &fieldname=Уникальное поле в таблице;text;id &prefix=Префикс плейсхолдеров документа;text;custom &pkname=PrimaryKey;text;id &sendparent=При просмотре документа перенаправлять на родителя?;list;true,false;true
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
        $target_id='';
        $_alias='';
        $q = explode('/', ltrim(removeSuffix($_SERVER['REQUEST_URI']), '/'));
        $_alias=end($q);
        array_pop($q);
        $docids=explode(',', trim($docid));
        foreach ($docids as $_docid) {
            $_url=$modx->makeUrl($_docid);
            $_url=removeSuffix($_url);
            $_url = ltrim($_url, '/');
            $_tmp = explode('/', $_url);
            if($_tmp == $q){
                $target_id = $_docid;
            }
        }

        if ($target_id != '' && $_alias != '') {
            $sql = "SELECT id FROM ".$modx->getFullTableName($tablename)." WHERE `".$fieldname."`='".$modx->db->escape($_alias)."'";
            $q = $modx->db->query($sql);
            if ($modx->db->getRecordCount($q) == 1) {
                $modx->customDocID = (int)$modx->db->getValue($q);
                $modx->sendForward($target_id);
            }
        }
        break;
    }
    case 'OnLoadWebDocument':
    case 'OnLoadWebPageCache':{
        $docids=explode(',', trim($docid));
        if(in_array($modx->documentObject['id'],$docids)){
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
