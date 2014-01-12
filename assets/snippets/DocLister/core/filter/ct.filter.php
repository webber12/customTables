<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once 'content.filter.php';
/**
 * Filters DocLister results by values in custom table (controller customtable).
 * @author webber <web-ber12@yandex.ru>
 *
 */
class ct_DL_filter extends filterDocLister{

    const TableAlias = 'ct';

    public function __construct(){
        $this->setTableAlias(self::TableAlias);
    }

    public function get_where(){
        return $this->build_sql_where($this->getTableAlias(), $this->field, $this->operator, $this->value);
    }

    public function get_join(){
        return '';
	}
}
?>