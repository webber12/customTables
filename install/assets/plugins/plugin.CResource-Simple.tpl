/**
 * CResource - Дочерние
 *
 * Вывод дочерних ресурсов из стандартной таблицы modx_site_content
 *
 * @author      webber (web-ber12@yandex.ru)
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnManagerPageInit,OnDocFormPrerender
 * @internal    @properties &which_jquery=Подключить jQuery;list;Не подключать,/site/js/,google code,custom url;custom url &js_src_type=Свой url к библиотеке jQuery;text;http://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js &jqname=Имя Jquery переменной в noConflict;text;jq &config=Конфиг;text;category
 * @internal    @installset base, sample
 * @internal    @modx_category CResource
 */
 
//<?php

// ********************************** //
// вывод в админку дочерних ресурсов из стандартной таблицы modx_site_content
// согласно конфигу category
// 
// все конфиги находятся в папке assets\plugins\CResource\config
//
// ********************************** //

require MODX_BASE_PATH . 'assets/plugins/CResource/plugin.cresource.php';