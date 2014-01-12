<?php
/*
* LOAD Config
*/

include_once(dirname(__FILE__) . "/lib/CRdata.class.php");

$CRdata = new CRdata($modx, isset($_REQUEST['data']) ? $_REQUEST['data'] : '');
$json = $CRdata->_config;
$data = $CRdata->_idConfig;
$param = array(
    "controller"=>"onetable",
    "table" => isset($json['table']) ? $json['table'] : 'site_content',
    'api' => 1,
    'JSONformat'=>'new',
	'ignoreEmpty'=>1
);