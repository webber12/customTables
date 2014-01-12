<?php
include_once(dirname(__FILE__)."/autoTable.abstract.php");

class modCatalog extends autoTable{
    public $table = "table_14";
	public $alias_field = "alias";
	
	private $alias_table=array('"'=>'_',"'"=>'_',' '=>'_','.'=>'_',','=>'_','а'=>'a','б'=>'b','в'=>'v',
		'г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k',
		'л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
		'ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ь'=>'','ы'=>'y','ъ'=>'',
		'э'=>'e','ю'=>'yu','я'=>'ya','А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E',
		'Ё'=>'E','Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N',
		'О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'C',
		'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch','Ь'=>'','Ы'=>'Y','Ъ'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
	);

	public function create($data=array()){
		parent::create($data);
		if($this->newDoc){
			$this->set('createdon',time());
			$this->set('createdby',$this->modx->getLoginUserID());
		}
		return $this;
	}
	
	public function save($fire_events = null,$clearCache = false){
		
        $fld = $this->toArray();
		$this->set($this->alias_field,$this->getAlias());
		$this->set('editedon',time());
		$this->set('editedby',$this->modx->getLoginUserID());
		if($this->get('pub_date')!=''){
			$this->prepareDate('pub_date',$this->get('pub_date'));
		}
		if($this->get('unpub_date')!=''){
			$this->prepareDate('unpub_date',$this->get('unpub_date'));
		}

        foreach($this->default_field as $key=>$value){
            if ($this->newDoc && $this->get($key) == '' && $this->get($key)!==$value){
                $this->set($key,$value);
            }
            $this->Uset($key);
            unset($fld[$key]);
        }
        if (!empty($this->set)){
            if($this->newDoc){
                $SQL = "INSERT into {$this->makeTable($this->table)} SET ".implode(', ', $this->set);
            }else{
                $SQL = "UPDATE {$this->makeTable($this->table)} SET ".implode(', ', $this->set)." WHERE ".$this->pkName." = ".$this->id;
            }
            $this->query($SQL);
        }

        if($this->newDoc) $this->id = $this->modx->db->getInsertId();
        if($clearCache){
            $this->clearCache($fire_events);
        }
        return $this->id;
    }
	
	private function getAlias(){
		if ($this->modx->config['friendly_urls'] && $this->modx->config['automatic_alias'] && $this->get('alias') == ''){
			$alias = strtr($this->get('pagetitle'), $this->alias_table);
		}else{
			if($this->get('alias')!=''){
				$alias = $this->get('alias');
			}else{
				$alias = '';
			}
		}
		$alias = $this->modx->stripAlias($alias);
		return $this->checkAlias($alias);
	}
	
	private function checkAlias($alias){
		$alias = strtolower($alias);
		if($this->modxConfig('friendly_urls')){
			$flag = false;
			$_alias = $this->modx->db->escape($alias);
			if((!$this->modxConfig('allow_duplicate_alias') && !$this->modxConfig('use_alias_path')) || ($this->modxConfig('allow_duplicate_alias') && $this->modxConfig('use_alias_path'))){
				$flag = $this->modx->db->getValue($this->query("SELECT id FROM {$this->makeTable($this->table)} WHERE alias='{$_alias}' AND parent={$this->get('parent')} LIMIT 1"));
			} else {
				$flag = $this->modx->db->getValue($this->query("SELECT id FROM {$this->makeTable($this->table)} WHERE alias='{$_alias}' LIMIT 1"));
			}
			if(($flag && $this->newDoc) || (!$this->newDoc && $flag && $this->id != $flag)){
				$suffix = substr($alias, -2);
				if(preg_match('/-(\d+)/',$suffix,$tmp) && isset($tmp[1]) && (int)$tmp[1]>1){
					$suffix = (int)$tmp[1] + 1;
					$alias = substr($alias, 0, -2) . '-'. $suffix;
				}else{
					$alias .= '-2';
				}
				$alias = $this->checkAlias($alias);
			}
		}
		return $alias;
	}
		
	public function prepareDate($key,$value){
		if($value!='0'){
			$time=$this->modx->toTimeStamp($value);
			$this->set($key,$this->modx->toTimeStamp($value));
			if($key=='pub_date'&&$time>time()){
				$this->set('published','0');
			}
		}
	}
}
