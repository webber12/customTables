<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once 'content.filter.php';
/**
 * Filters DocLister results by value of a given MODx Template Variables (TVs).
 * @author kabachello <kabachnik@hotmail.com>
 *
 */
class quid_DL_filter extends content_DL_filter{


	public function get_join(){
		return '';
	//	return ($this->totalFilters==1?', modx_tvs_11':'');
	}
	
	public function get_where(){
		//$QFTable=$this->DocLister->getCFGDef('QFTable','');
		$QFTable=$this->DocLister->getCFGDef('QFTable','');
		if($QFTable!=''){
			$addWhereList=$this->DocLister->getCFGDef('addWhereList','');
			if(strpos($addWhereList,$QFTable)!==false){
		        $addWhereList=substr($addWhereList,0,-1);//чтобы убрать закрывающую скобку для всех условий, кроме последнего
			}
		    $addWhereList.=($this->totalFilters==1?($addWhereList==''?'':' AND ').'c.id IN (SELECT '.$QFTable.'.cid FROM '.$QFTable.' WHERE c.id='.$QFTable.'.cid AND ':'');
		    $addWhereList.=($this->totalFilters==1?'':' AND ').$this->build_sql_where($QFTable, $this->field, $this->operator, $this->value).')';
		    $this->DocLister->setConfig(array('addWhereList'=>$addWhereList));
		}
		return '';
	//	return $this->build_sql_where('modx_tvs_11', $this->field, $this->operator, $this->value).($this->totalFilters==1?' AND c.id=modx_tvs_11.cid':'');
	}
}
?>