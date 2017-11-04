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
    function removeSuffix($url, $folder='0'){
        global $modx;
        $suffix = ($folder == '1' && $modx->config['make_folders'] == '1') ? '/' : $modx->config['friendly_url_suffix'];
        $pos = strripos($url, $suffix);
        return ($pos === false) ? rtrim($url) : substr($url, 0, $pos);
    }
}

switch($modx->event->name){
    case 'OnPageNotFound':{
        $_alias = '';
        $q = explode('/', ltrim(removeSuffix($_SERVER['REQUEST_URI']), '/'));
        $_alias = array_pop($q);
        $parent_alias = implode('/', $q);
        if ($modx->config['use_alias_path'] == 1) {;
            if (isset($modx->documentListing[$parent_alias])) {
                $pid = $modx->documentListing[$parent_alias];
            }
        } else {
                $pid = $modx->documentListing[$parent_alias];
        }
        if (isset($pid)) {
            $parent_template = $modx->db->getValue($modx->db->select('template', $modx->getFullTableName('site_content'), 'id=' . $pid));
            if ($parent_template && $parent_template == $parent_tpl_id) {
                $sql= "SELECT " . $pkname . " FROM " . $modx->getFullTableName($tablename) . " WHERE `" . $fieldname . "`='" . $_alias . "'";
                $res = $modx->db->query($sql);
                if ($modx->db->getRecordCount($res) == 1) {
                    $custom_id = (int)$modx->db->getValue($res);
                    $modx->customDocID = $custom_id;
                    $modx->systemCacheKey = 'notfound-customtables-' . $custom_id;
                    $modx->sendForward($target_doc_id);
                }
            }
        }
        break;
    }
    case 'OnLoadWebDocument':
    case 'OnLoadWebPageCache':{
        if ($modx->documentObject['id'] == $target_doc_id) {
            $flag = true;
            if (isset($modx->customDocID) && (int)$modx->customDocID>0) {
                $q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName($tablename) . " WHERE `" . $pkname . "`='" . $modx->customDocID . "'");
                $flag = ($modx->db->getRecordCount($q)==1);
            } else {
                $flag = false;
            }
            if ($flag) {
                $out = $modx->db->getRow($q);
                $plh = array();
                foreach ($out as $key => $data) {
                    $plh[$prefix . "_" . $key] = $data;
                }
                $modx->customDocID = (isset($plh[$prefix . "_" . $pkname])) ? $plh[$prefix . "_" . $pkname] : false;
                $modx->documentObject = array_merge($modx->documentObject, $plh);
            } else {
                $modx->customDocID = false;
                if ($sendparent=='true') {
                    $url = $modx->makeUrl($modx->documentObject['parent'], '', '', 'full');
                    $modx->sendRedirect($url, 0, 'REDIRECT_HEADER', 'HTTP/1.1 301 Moved Permanently');
                } else {
                    $modx->sendErrorPage();
                }
            }
        }
        break;
    }
}
