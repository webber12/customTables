<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * controller for show info from custom table with filters (ct.filter.php)
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author webber <web-ber12@yandex.ru>
 *
 */

class customtableDocLister extends DocLister
{
    protected $table = 'site_content';

    /**
     * @absctract
     */

    public function getUrl($id = 0)
    {
        $id = $id > 0 ? $id : $this->modx->documentIdentifier;
        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : $this->getRequest();
        $url = ($id == $this->modx->config['site_start']) ? $this->modx->config['site_url'] . ($link != '' ? "?{$link}" : "") : $this->modx->makeUrl($id, '', $link, 'full');
        return $url;
    }

    /**
    * @absctract
    */
    public function getDocs($tvlist = '')
    {
        $this->table = $this->getTable($this->getCFGDef('table', 'site_content'));
        $this->idField = $this->getCFGDef('idField', 'id');

        if ($this->checkExtender('paginate')) {
            $pages = $this->extender['paginate']->init($this);
        } else {
            $this->config->setConfig(array('start' => 0));
        }
        $this->_docs = $this->getDocList();

        return $this->_docs;
    }

    public function _render($tpl = '')
    {
        $out = '';
        if ($tpl == '') {
            $tpl = $this->getCFGDef('tpl', '');
        }
        if ($tpl != '') {
            $this->toPlaceholders(count($this->_docs), 1, "display"); // [+display+] - сколько показано на странице.

            $i = 1;
            $sysPlh = $this->renameKeyArr($this->_plh, $this->getCFGDef("sysKey", "dl"));
            $noneTPL = $this->getCFGDef("noneTPL", "");
            if (count($this->_docs) == 0 && $noneTPL != '') {
                $out = $this->parseChunk($noneTPL, $sysPlh);
            } else {
                if ($this->checkExtender('user')) {
                    $this->extender['user']->init($this, array('fields' => $this->getCFGDef("userFields", "")));
                }
				
				/**
                 * @var $extGetViews getviews_DL_Extender
                 */
                $extGetViews = $this->getCFGdef('getviews',0) ? $this->getExtender('getviews',true) : NULL;

				if ($extGetViews) {
					$views_count = $extGetViews->countViews(array_keys($this->_docs));
				}

                foreach ($this->_docs as $item) {
                    $subTpl = '';
                    if ($this->checkExtender('user')) {
                        $item = $this->extender['user']->setUserData($item); //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

                    if ($this->checkExtender('summary')) {
                        $introField = $this->getCFGDef("introField", "");
                        if (isset($item[$introField]) && mb_strlen($item[$introField], 'UTF-8') > 0) {
                            $item[$this->getCFGDef("sysKey", "dl") . '.summary'] = $item[$introField];
                        } else {
                            $contentField = $this->getCFGDef("contentField", "");
                            if (isset($item[$contentField])) {
                                $item[$this->getCFGDef("sysKey", "dl") . '.summary'] = $this->extender['summary']->init($this, array("content" => $item[$contentField], "summary" => $this->getCFGDef("summary", "")));
                            } else {
                                $item[$this->getCFGDef("sysKey", "dl") . '.summary'] = '';
                            }
                        }
                    }

                    $item = array_merge($item, $sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
                    $item[$this->getCFGDef("sysKey", "dl") . '.iteration'] = $i; //[+iteration+] - Number element. Starting from zero

                    $date = $this->getCFGDef('dateSource', 'pub_date');
                    $item['date'] = (isset($item[$date]) && $date != 'createdon' && $item[$date] != 0 && $item[$date] == (int)$item[$date]) ? $item[$date] : $item['createdon'];
                    $item['date'] = $item['date'] + $this->modx->config['server_offset_time'];
                    if ($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M') != '') {
                        $item['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $item['date']);
                    }

                    $class = array();
                    $class[] = ($i % 2 == 0) ? 'odd' : 'even';
                    if ($i == 0) {
                        $subTpl = $this->getCFGDef('tplFirst', $tpl);
                        $class[] = 'first';
                    }
                    if ($i == count($this->_docs)) {
                        $subTpl = $this->getCFGDef('tplLast', $tpl);
                        $class[] = 'last';
                    }
                    if ($this->modx->documentIdentifier == $item['id']) {
                        $subTpl = $this->getCFGDef('tplCurrent', $tpl);
                        $item[$this->getCFGDef("sysKey", "dl") . '.active'] = 1; //[+active+] - 1 if $modx->documentIdentifer equal ID this element
                        $class[] = 'current';
                    } else {
                        $item[$this->getCFGDef("sysKey", "dl") . '.active'] = 0;
                    }
                    $item[$this->getCFGDef("sysKey", "dl") . '.iteration'] = $i; //[+iteration+] - Number element. Starting from zero
                    $item[$this->getCFGDef("sysKey", "dl") . '.full_iteration'] = ($this->checkExtender('paginate')) ? ($i + $this->getCFGDef('display', 0) * ($this->extender['paginate']->currentPage()-1)) : $i;

                    if($subTpl==''){
                        $subTpl = $tpl;
                    }
                    $class = implode(" ", $class);
                    $item[$this->getCFGDef("sysKey", "dl") . '.class'] = $class;

                    if($this->checkExtender('prepare')){
                        $item = $this->extender['prepare']->init($this, array(
                            'data'      => $item,
                            'nameParam' => 'prepare'
                        ));
                    }
                    $tmp = $this->parseChunk($subTpl, $item);
                    if ($this->getCFGDef('contentPlaceholder', 0) !== 0) {
                        $this->toPlaceholders($tmp, 1, "item[" . $i . "]"); // [+item[x]+] – individual placeholder for each iteration documents on this page
                    }
                    $out .= $tmp;
                    $i++;
                }
            }
            if (($this->getCFGDef("noneWrapOuter", "1") && count($this->_docs) == 0) || count($this->_docs) > 0) {
                $ownerTPL = $this->getCFGDef("ownerTPL", "");
                if ($ownerTPL != '') {
                    $out = $this->parseChunk($ownerTPL, array($this->getCFGDef("sysKey", "dl") . ".wrap" => $out));
                }
            }
        } else {
            $out = 'none TPL';
        }

        return $this->toPlaceholders($out);
    }

    public function getJSON($data, $fields, $array = array())
    {
        $out = array();
        $fields = is_array($fields) ? $fields : explode(",", $fields);
        $date = $this->getCFGDef('dateSource', 'pub_date');

        foreach ($data as $num => $item) {
            switch (true) {
                case ((array('1') == $fields || in_array('summary', $fields)) && $this->checkExtender('summary')):
                {
                    $out[$num]['summary'] = (mb_strlen($this->_docs[$num]['introtext'], 'UTF-8') > 0) ? $this->_docs[$num]['introtext'] : $this->extender['summary']->init($this, array("content" => $this->_docs[$num]['content'], "summary" => $this->getCFGDef("summary", "")));
                    //without break
                }
                case ((array('1') == $fields || in_array('date', $fields)) && $date != 'date'):
                {
                    $tmp = (isset($this->_docs[$num][$date]) && $date != 'createdon' && $this->_docs[$num][$date] != 0 && $this->_docs[$num][$date] == (int)$this->_docs[$num][$date]) ? $this->_docs[$num][$date] : $this->_docs[$num]['createdon'];
                    $out[$num]['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $tmp + $this->modx->config['server_offset_time']);
                    //without break
                }
            }
        }

        return parent::getJSON($data, $fields, $out);
    }

    protected function getDocList()
    {
        $out = array();
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
            $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            $where = rtrim($where, " AND ");
			
            if ($where != '') {
                $where = explode(" AND ", $where);
            }
            if($sanitarInIDs != "''"){
                $where[] = "{$this->getPK()} IN ({$sanitarInIDs})";
            }
			if($this->getCFGDef('showNoPublish', 0)){
			}
			else{
				$where[] = "ct.deleted=0";
				$where[] = "ct.published=1";
			}

            if(!empty($where)){
                $where = "WHERE ".implode(" AND ",$where);
            }
			
			$sort = $this->SortOrderSQL("if(ct.pub_date=0,ct.createdon,ct.pub_date)");
			
            $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));
            $rs = $this->dbQuery("SELECT ct.* FROM {$this->table} ct {$where} {$sort} {$limit}");

            $rows = $this->modx->db->makeArray($rs);
            $out = array();
            foreach ($rows as $item) {
                $out[$item[$this->getPK()]] = $item;
            }
        }
        return $out;
    }

    // @abstract
    public function getChildrenCount()
    {
        $where = $this->getCFGDef('addWhereList', '');
        $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
        $where = rtrim($where, " AND ");
		$where .= $where == '' ? " ct.deleted=0 " : " AND ct.deleted=0 ";
		if($this->getCFGDef('showNoPublish', 0)){
		
			}
		else{
				$where .= $where == '' ? " ct.published=1 " : " AND ct.published=1 ";
		}
		
        if(!empty($where)){
            $where = "WHERE ".$where;
        }
			
        $fields = "count(`ct`.`{$this->getPK()}`) as `count`";
				
        $rs = $this->dbQuery("SELECT {$fields} FROM {$this->table} ct {$where}");
        return $this->modx->db->getValue($rs);
    }

    public function getChildrenFolder($id)
    {
        $where = $this->getCFGDef('addWhereFolder', '');
        if(!empty($where)){
            $where = "WHERE ".$where;
        }
        $rs = $this->dbQuery("SELECT {$this->getPK()} FROM {$this->table} {$where}");

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[] = $item[$this->getPK()];
        }
        return $out;
    } 
}