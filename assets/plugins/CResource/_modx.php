<?php
if(!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, "/");
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/");
}
$dir = dirname(__FILE__);
$root_dir = dirname(dirname(dirname($dir)));

define('MODX_API_MODE', true);

include_once($root_dir . "/index.php");

/** Принудительно обновляем конфиг */
$modx->config = array();
unlink(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php');
$modx->getSettings();

$modx->documentMethod = "id";
$modx->documentIdentifier = (isset($_REQUEST['id'])) ? (int)$_REQUEST['id'] : $modx->getConfig('error_page');
//$modx->documentObject = $modx->getDocumentObject('id', $modx->documentIdentifier);

//$modx->invokeEvent("OnWebPageInit");

define("IN_MANAGER_MODE", true);

if (!isset($_SESSION['mgrValidated'])) {
    echo "Not Logged In!";
    exit;
}