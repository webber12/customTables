<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
 * CResource
 *
 * Плагин для редактирования данных БД через админку
 *
 * @license     GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author      Agel_Nash <Agel_Nash@xaker.ru>
 * @version     0.1
* @internal        @properties     &which_jquery=Подключить jQuery;list;Не подключать,/assets/js/,google code,custom url;custom url &js_src_type=Свой url к библиотеке jQuery;text;http://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.0/jquery.min.js &jqname=Имя Jquery переменной в noConflict;text;j
* @internal        @events         OnManagerPageInit, OnDocFormPrerender
*/

include_once($modx->config['base_path'].'assets/plugins/CResource/lib/CRGrid.class.php');
	
switch($modx->event->name){
	case 'OnManagerPageInit':{	
		if($action==3){
            $config = isset($config) ? $config : '';
            $action = isset($modx->event->params['action']) ? $modx->event->params['action'] : null;
            $CRGrid = new CRGrid($modx, $config);
            if($CRGrid->checkRules()){
			    echo $CRGrid->render($which_jquery, $jqname, $js_src_type);
			    die();
            }
		}
		break;
	}
	case 'OnDocFormPrerender':{
		//exit();
		break; 
	}
}