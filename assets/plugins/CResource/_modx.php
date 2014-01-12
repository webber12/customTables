<?php
if(!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, "/");
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/");
}
$dir = dirname(__FILE__);
$root_dir = dirname(dirname(dirname($dir)));

define('MODX_API_MODE', true);

include_once($root_dir."/index.php");

define("IN_MANAGER_MODE", true);

if(!isset($_SESSION['mgrValidated'])){
    echo "Not Logged In!";
    exit;
}