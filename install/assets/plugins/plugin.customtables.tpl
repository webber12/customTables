/**
 * customtables
 *
 * Запись данных в пользовательскую таблицу
 *
 * @author      webber (web-ber12@yandex.ru)
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnWebPageInit,OnManagerPageInit,OnBeforeDocFormSave,OnDocFormRender,OnDocFormPrerender,OnBeforeTVFormDelete,OnTVFormSave,OnTempFormSave
 * @internal    @properties &tmpl_ids=Templates ids comma separated;text;19,21
 * @internal    @installset base, sample
 * @internal    @modx_category Customtables
 */
 
//<?php

// ********************************** //
// в конфигурацию вносим через запятую id шаблонов-родителей, дочерние элементы которых будут вноситься в кастомные таблицы
// соответственно, для указанной в примере конфигурации &tmpl_ids=Templates ids comma separated;text;19,21
// создадутся таблицы modx_customtable_19 и modx_customtable_21, которые будут писаться, соотвественно, дети ресурсов-родителей, имеющих шаблоны 19 и 21
// конфигурации для них находятся, соответственно, в папке assets\plugins\CResource\config - это будут файлы customtable_19.data.json и customtable_21.data.json
// ********************************** //

require MODX_BASE_PATH . 'assets/plugins/customtables/plugin.customtables.php';