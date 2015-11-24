/**
 * CResource - Тегованные
 *
 * Вывод ресурсов из стандартной таблицы modx_site_content, но с учетом тегов tagSaver (контроллер site_content_tags от DocLister)
 *
 * @author      webber (web-ber12@yandex.ru)
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnManagerPageInit,OnDocFormPrerender
 * @internal    @properties &which_jquery=Подключить jQuery;list;Не подключать,/site/js/,google code,custom url;custom url &js_src_type=Свой url к библиотеке jQuery;text;http://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js &jqname=Имя Jquery переменной в noConflict;text;jq &config=Конфиг;text;tagSaver
 * @internal    @installset base, sample
 * @internal    @modx_category CResource
 */
 
//<?php

// ********************************** //
// вывод в админку ресурсов из стандартной таблицы modx_site_content
// с учетом тегов tagSaver (контроллер site_content_tags от DocLister)
// согласно конфигу category3
// 
// все конфиги находятся в папке assets\plugins\CResource\config
// 
// ********************************** //

require MODX_BASE_PATH . 'assets/plugins/CResource/plugin.cresource.php';