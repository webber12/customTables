<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

class CustomTables
{

    public $tableId;
    public $tableName;
    public $tablePrefix;//дополнительный префикс таблиц для хранения данных (по умолчанию table_
    public $api;
    public $modx;
    public $tmpl_ids;
    public $tmpl_ids_array;
    public $plugin_params;

    public function __construct($modx, $params)
    {
        $this->plugin_params = $params;
        $this->modx = $modx;
        $this->loadAPI($this->modx);
        $this->tmpl_ids = $this->plugin_params['tmpl_ids'];
        $this->tmpl_ids_array = explode(',', $this->tmpl_ids);
        $this->tv_tmpl_table = $this->modx->getFullTableName('site_tmplvar_templates');
        $this->tmplvars_table = $this->modx->getFullTableName('site_tmplvars');
        $this->tableId = '8';
        $this->tablePrefix = "customtable_";
        $this->tableName = $this->modx->getFullTableName($this->tablePrefix.$this->tableId);
    }

    private function loadAPI($modx)
    {
        include_once MODX_BASE_PATH.'assets/plugins/CResource/lib/MODxAPI/modCustomTables.php';
        $this->api = new modCustomTables($modx);
    }

    public function addTable($id)
    {
        if (!$this->tableExists($this->modx->getFullTableName($this->tablePrefix.$id))) {
            $sql="
            CREATE TABLE IF NOT EXISTS ".$this->modx->getFullTableName($this->tablePrefix.$id)." (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `type` varchar(20) NOT NULL DEFAULT 'document',
                `contentType` varchar(50) NOT NULL DEFAULT 'text/html',
                `pagetitle` varchar(255) NOT NULL DEFAULT '',
                `longtitle` varchar(255) NOT NULL DEFAULT '',
                `description` varchar(255) NOT NULL DEFAULT '',
                `alias` varchar(255) DEFAULT '',
                `link_attributes` varchar(255) NOT NULL DEFAULT '',
                `published` int(1) NOT NULL DEFAULT '0',
                `pub_date` int(20) NOT NULL DEFAULT '0',
                `unpub_date` int(20) NOT NULL DEFAULT '0',
                `parent` int(10) NOT NULL DEFAULT '0',
                `isfolder` int(1) NOT NULL DEFAULT '0',
                `introtext` text,
                `content` mediumtext,
                `richtext` tinyint(1) NOT NULL DEFAULT '1',
                `template` int(10) NOT NULL DEFAULT '0',
                `menuindex` int(10) NOT NULL DEFAULT '0',
                `searchable` int(1) NOT NULL DEFAULT '1',
                `cacheable` int(1) NOT NULL DEFAULT '1',
                `createdby` int(10) NOT NULL DEFAULT '0',
                `createdon` int(20) NOT NULL DEFAULT '0',
                `editedby` int(10) NOT NULL DEFAULT '0',
                `editedon` int(20) NOT NULL DEFAULT '0',
                `deleted` int(1) NOT NULL DEFAULT '0',
                `deletedon` int(20) NOT NULL DEFAULT '0',
                `deletedby` int(10) NOT NULL DEFAULT '0',
                `publishedon` int(20) NOT NULL DEFAULT '0',
                `publishedby` int(10) NOT NULL DEFAULT '0',
                `menutitle` varchar(255) NOT NULL DEFAULT '',
                `donthit` tinyint(1) NOT NULL DEFAULT '0',
                `haskeywords` tinyint(1) NOT NULL DEFAULT '0',
                `hasmetatags` tinyint(1) NOT NULL DEFAULT '0',
                `privateweb` tinyint(1) NOT NULL DEFAULT '0',
                `privatemgr` tinyint(1) NOT NULL DEFAULT '0',
                `content_dispo` tinyint(1) NOT NULL DEFAULT '0',
                `hidemenu` tinyint(1) NOT NULL DEFAULT '0',
                `alias_visible` int(2) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`),
                KEY `id` (`id`),
                KEY `parent` (`parent`),
                KEY `aliasidx` (`alias`),
                KEY `typeidx` (`type`),
                FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;	
            ";
            $q = $this->modx->db->query($sql);
        }
        return true;
    }

    protected function getSQLType($type, $default='TEXT NULL')
    {
        $types = array(
            'date'=>"INT(20) NOT NULL DEFAULT '0'",
            'number'=>"DOUBLE NOT NULL DEFAULT '0'"
        );
        return isset($types[$type]) ? $types[$type] : $default;
    }

    protected function getTVInfo($tv_id)
    {
        $sql = "SELECT * FROM ".$this->tmplvars_table." WHERE id='".$tv_id."' LIMIT 0,1";
        $info = $this->modx->db->getRow($this->modx->db->query($sql));
        return $info;
    }

    protected function columnExists($column, $table)
    {
        if (!$this->tableExists($table)) {
            return false;
        } else {
            $sql='SHOW COLUMNS FROM '.$table;
            $res = $this->modx->db->query($sql);
            while ($row = $this->modx->db->getRow($res)) {
                if ($row['Field'] == $column) return true;
            }
        }
        return false;
    }
  
    protected function tableExists($table)
    {
        $sql = "SHOW TABLES LIKE '$table'";
        $res = $this->modx->db->query($sql);
        if($this->modx->db->getRecordCount($res) > 0) return true;
        return false;
    }

    public function createColumns($tv_id)
    {
        $tv_info = $this->getTVInfo($tv_id);
        $tmpls = array();
        $sql = "SELECT templateid FROM ".$this->tv_tmpl_table." WHERE tmplvarid='".$tv_id."' AND templateid IN(".$this->tmpl_ids.")";
        $q = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($q)) {
            $tmpls[] = $row['templateid'];
        }
        if (!empty($tmpls)) {
            foreach ($tmpls as $tmpl) {
                $this->addTable($tmpl);
                $tv_sql = '';
                $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$tmpl;
                $this->createColumn($table_name, $tv_info);
            }
        }
    }

    protected function createColumn($table_name, $tv_info=array(), $tv_id='')
    {
        if (empty($tv_info) && $tv_id != '') {
            $tv_info = $this->getTVInfo($tv_id);
        }
        if ($this->columnExists($tv_info['name'], $table_name)) {
            $tv_sql = 'ALTER IGNORE TABLE '.$table_name.' CHANGE `'. $tv_info['name'] .'` `'. $tv_info['name'] .'` '.$this->getSQLType($tv_info['type']);
        } else {
            $tv_sql = 'ALTER IGNORE TABLE '.$table_name.' ADD `'. $tv_info['name'] .'` '.$this->getSQLType($tv_info['type']);
        }
        if ($tv_sql!='') {
            $q = $this->modx->db->query($tv_sql);
        }
    }

    public function updateColumns($template_id)
    {
        $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$template_id;
        $sql = "SELECT tmplvarid FROM ".$this->tv_tmpl_table." WHERE templateid='$template_id' ORDER BY rank ASC";
        $q = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($q)) {
            $this->createColumn($table_name, array(), $row['tmplvarid']);
        }
    }

    public function deleteColumn($tv_id)
    {
        $tv_info = $this->getTVInfo($tv_id);
        $tmpls = array();
        $sql = "SELECT templateid FROM ".$this->tv_tmpl_table." WHERE tmplvarid='".$tv_id."' AND templateid IN(".$this->tmpl_ids.")";
        $q = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($q)) {
            $tmpls[] = $row['templateid'];
        }
        if (!empty($tmpls)) {
            foreach ($tmpls as $tmpl) {
                $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$tmpl;
                if ($this->tableExists($table_name)) {
                    if ($this->columnExists($tv_info['name'], $table_name)) {
                        $tv_sql = 'ALTER TABLE '.$table_name.' DROP `'. $tv_info['name'] .'`';
                        $q = $this->modx->db->query($tv_sql);
                    }
                }
            }
        }
    }

    public function checkTemplateById($id, $table=false)
    {
        $template = false;
        if (isset($_REQUEST['template']) && (int)$_REQUEST['template']!=0) {
            $template=(int)$_REQUEST['template'];
        }
        if (!$template&&$table) {
            $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$table;
            $template = $this->modx->db->getValue($this->modx->db->query("SELECT template FROM $table_name WHERE id='$id' LIMIT 0,1"));
        }
        if ($template && in_array($template,$this->tmpl_ids_array)) {
            return true;
        } else {return false;}
    }

    public function checkTemplate($template_id)
    {
        if (in_array($template_id,$this->tmpl_ids_array)) return true;
        else return false;
    }

    protected function getTVNames($template_id)
    {
        $TVNames = array();
        $q=$this->modx->db->query("SELECT a.id,a.name,a.default_text FROM ".$this->tmplvars_table." a,".$this->tv_tmpl_table." b WHERE a.id=b.tmplvarid AND b.templateid=".$template_id);
        while ($row=$this->modx->db->getRow($q)) {
            $TVNames[$row['id']]['name'] = $row['name'];
            $TVNames[$row['id']]['default_text'] = $row['default_text'];
        }
        return $TVNames;
    }

    public function updateDoc($post)
    {
        $data = $this->prepareData($_POST);
        $edit = $this->api->edit($_POST['id'])->fromArray($data)->save();
    }

    public function save2Doc($post)
    {
        $data = $this->prepareData($_POST);
        if (isset($data['parent']) && (int)$data['parent'] != 0) {
            $data['menuindex'] = $this->makeMenuIndex((int)$data['parent']);
        }
        $edit = $this->api->create($data)->save();
		return $edit; //new doc custom id
    }

    protected function makeMenuIndex($parent)
    {
        $sql="SELECT MAX(menuindex) FROM ".$this->modx->getFullTableName($this->api->getTable())." WHERE parent='$parent'";
        $q=$this->modx->db->getValue($this->modx->db->query($sql));
        return $q ? ($q+1) : 1; 
    }

    protected function prepareData($tmp)
    {
        $template_id = isset($tmp['customtable'])?$tmp['customtable']:$tmp['template'];
        $tmp['template'] = $template_id;//fix - сбивается POST['template'] при смене виз.редактора
        $TVNames = $this->getTVNames($template_id);
        $data = array();
        foreach ($tmp as $k=>$v) {
            if (strpos($k,'tv') === 0) {
                $k = str_replace('tv', '', $k);
                if (isset($TVNames[$k])) {
                    $data[$TVNames[$k]['name']] = is_array($v)?implode('||',$v):($v==''?$TVNames[$k]['default_text']:$v);	
                } else {
                    $data[$k] = is_array($v) ? implode('||',$v) : $v;
                }
            } else {
                if($k == 'ta'){
                    $k = 'content';
                }
                $data[$k] = is_array($v) ? implode('||',$v) : $v;
            }
        }
        foreach ($TVNames as $k=>$v) { //hack for empty checkboxes & radios
            if (!isset($tmp['tv'.$k])) {
                $data[$v['name']] = $v['default_text'];
            }
        }
        return $data;
    }

    public function getTVFromContent($content = array(), $template_id)
    {
        $alltvs = array();
        $tvs = array();
        $q = $this->modx->db->query("SELECT * FROM ".$this->tmplvars_table." a,".$this->tv_tmpl_table." b WHERE a.id=b.tmplvarid AND b.templateid=".$template_id." ORDER BY b.rank ASC");
        while ($row=$this->modx->db->getRow($q)) {
            if (isset($content[$row['name']])||is_null($content[$row['name']])) {
                $tvs[$row['name']] = $row;
                $tvs[$row['name']]['value'] = $content[$row['name']];
            }
        }
        return $tvs;
    }

    public function checkCacheEvents($field = 'published')
    {
        $timeNow= time() + $this->modx->config['server_offset_time'];

        foreach ($this->tmpl_ids_array as $tmpl_id) {
            $cacheRefreshTime = 0;
            $field = $tmpl_id == '12' ? 'published' : 'paid';
            $this->api->setTable('customtable_' . $tmpl_id);
            @include $this->modx->config["base_path"] . "assets/cache/customCacheEvent.customtable_" . $tmpl_id . ".php";
            if ($cacheRefreshTime <= $timeNow && $cacheRefreshTime != 0) {
                // now, check for documents that need publishing
                $table = $this->modx->getFullTableName("customtable_" . $tmpl_id);
                $sql = "UPDATE {$table} SET {$field}=1, publishedon=" . time() . " WHERE " . $table.".pub_date <= {$timeNow} AND " . $table . ".pub_date != 0 AND {$field} = 0";
                if (@ !$result= $this->modx->db->query($sql)) {
                    $this->modx->messageQuit("Execution of a query to the database failed", $sql);
                }
                // now, check for documents that need un-publishing
                $sql= "UPDATE {$table} SET {$field}=0, publishedon=0 WHERE " . $table . ".unpub_date <= {$timeNow} AND " . $table . ".unpub_date != 0 AND {$field} = 1";
                if (@ !$result= $this->modx->db->query($sql)) {
                    $this->modx->messageQuit("Execution of a query to the database failed", $sql);
                }
                
                $this->api->updateCacheEventTime();
                
                // clear the cache
                $this->modx->clearCache();
            }
        }
    }


}//end class
