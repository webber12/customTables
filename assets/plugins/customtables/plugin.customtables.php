<?php
// @params &tmpl_ids=Templates ids comma separated;text;5,8
// @events OnManagerPageInit, OnBeforeDocFormSave, OnDocFormPrerender,OnBeforeTVFormDelete,OnTVFormSave,OnTempFormSave

if(!defined('MODX_BASE_PATH')){die ('What are you doing? Get out of here!');}

include_once('class/customtables.php');
$oT = new CustomTables($modx, $params);

$evt = &$modx->event;
$output = '';
switch ($evt->name) {
    case 'OnTempFormSave':
        if(isset($evt->params['id']) && in_array($evt->params['id'], $oT->tmpl_ids_array)){
            $oT->addTable($evt->params['id']);
            $oT->updateColumns($evt->params['id']);
        }
        break;
    case 'OnTVFormSave':
        $oT->createColumns($evt->params['id']);
        break;
    case 'OnBeforeTVFormDelete':
        $oT->deleteColumn($evt->params['id']);
        break;
    case 'OnBeforeDocFormSave':
        if (isset($_POST['template']) && $oT->checkTemplate($_POST['template'])) {
            $oT->api->setTable('customtable_'.$_POST['template']);
            if ($evt->params['mode'] == 'new') {
                $new=$oT->save2Doc($_POST);
                if ($new) {
                    $oT->modx->clearCache();
                    header('Location:index.php?a=27&customtable=' . $_REQUEST['customtable'] . '&id=' . $new);
                    echo $new . 'saved';
                }
                die();
            }
        }
        break;
    case 'OnDocFormPrerender':
        if (isset($_REQUEST['customtable'])) {
            $script = '<script>
                window.addEvent("domready", function(){
                    document.getElementById("template").getParent().getParent().setStyle("display","none");
                })</script>';
            $output .= $script.'<input type="hidden" name="customtable" value="'.$_REQUEST['customtable'].'">';
        }
        break;
    case 'OnDocFormRender':
        if (isset($_REQUEST['customtable']) && (int)$_REQUEST['customtable'] != 0) {
            global $content;
            $content['template'] = (int)$_REQUEST['customtable'];
        }
        break;
    case 'OnManagerPageInit':
        if ($action == 27 && isset($_REQUEST['customtable'])) {
            global $_lang, $_style, $which_editor, $use_editor;
            if ($oT->checkTemplateById($_REQUEST['id'], $_REQUEST['customtable'])) {
                $tbl = (int)$_REQUEST['customtable'];
                $onetbl = $modx->getFullTableName('customtable_'.$tbl);
                $manager_theme = $modx->config['manager_theme'];
                include_once "header.inc.php";
                include_once MODX_BASE_PATH."/assets/plugins/customtables/mutate_content.dynamic.php";
                include_once "footer.inc.php";
                die();
            }
        }
        if ($action == 5 && isset($_REQUEST['customtable']) && $_POST['mode'] == '27') {
            if (isset($_REQUEST['customtable']) && $oT->checkTemplate($_REQUEST['customtable'])) {
                if (!$modx->hasPermission('save_document')) {
                    include_once MODX_MANAGER_PATH."includes/error.class.inc.php";
                    $err = new errorHandler;
                    $err->setError(3,"You don't have enough privileges for this action!");
                    $err->dumpError();
                }
                $oT->api->setTable('customtable_'.$_REQUEST['customtable']);
                $oT->updateDoc($_POST);
                $oT->modx->clearCache();
                header('Location:index.php?a=27&customtable=' . $_REQUEST['customtable'] . '&id=' . (int)$_POST['id']);
                echo 'updated';
                die();
            }
        }
        break;
    case 'OnWebPageInit':
        $oT->checkCacheEvents();
        break;
    default:
        break;
}

$evt->output($output);

//end