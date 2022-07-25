<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class MDB{
    public static $_app;
	public $datalist = array();
	public $memberlist = array('common_member_archive','common_member_count','common_member_count_archive','common_member_status','common_member_profile','common_member_profile_archive','common_member_field_forum','common_member_field_forum_archive','common_member_field_home','common_member_field_home_archive','common_member_validate','common_member_verify','common_member_verify_info');
    public function __construct()
    {
        $this->config = C::app()->config;
        self::$_app = $this;
    }
    public static function app()
    {
		if(!is_object(self::$_app)) {
			self::$_app = new MDB();
		}
        return self::$_app;
    }
    public function dbconfig($table)
    {
		if(!empty($this->datalist[$table])) return $this->datalist[$table];
		$tablename = $table;
		if(!empty($this->config['db']['map'])){
			if(preg_match('/\_\d+$/',$tablename)){
				$tablename = preg_replace('/\_\d+$/','',$tablename);
				$this->datalist[$table] = $this->dbconfig($tablename);
				$this->datalist[$table]['tablename'] = str_replace($tablename,$table,$this->datalist[$table]['tablename']);
				return $this->datalist[$table];
			}else if(isset($this->map['common_member'])&&in_array($table,$this->memberlist)) {
				$this->datalist[$table] = $this->dbconfig('common_member');
				$this->datalist[$table]['tablename'] = str_replace($tablename,$table,$this->datalist[$table]['tablename']);
				return $this->datalist[$table];
			}
		}
		$id = 1;
		if(isset($this->config['db']['map'][$table])&&isset($this->config['db'][$this->config['db']['map'][$table]])){
			$id = (int) $this->config['db']['map'][$table];
		}
        $config = array(
            'host'=>$this->config['db'][$id]['dbhost'],
            'user'=>$this->config['db'][$id]['dbuser'],
            'pw'=>$this->config['db'][$id]['dbpw'],
            'charset'=>$this->config['db'][$id]['dbcharset'],
            'name'=>$this->config['db'][$id]['dbname'],
            'host'=>$this->config['db'][$id]['dbhost'],
            'port'=>!empty($this->config['db'][$id]['port'])?$this->config['db'][$id]['port']:3306,
            'tablename'=>$this->config['db'][$id]['tablepre'].$tablename,
            'id'=>$id,
        );
        if(empty($config['port'])&&strpos($config['host'],':')!==false){
            $host = strstr($config['host'],':',true);
            $config['port'] = substr($config['host'],strlen($host)+1);
            $config['host'] = $host;
        }
		$this->datalist[$table] = $config;
        return $config;
    }
    public function connect($table)
    {
        $_conf = $this->dbconfig($table);
        if($_conf['id']==1&&!empty($this->master)) return $this->master;
        elseif(!empty($this->datalist[$table]['link'])) return $this->datalist[$table]['link'];
        if(class_exists('PDO')){
            $dbname = isset($_conf['name']) ? 'dbname='.$_conf['name'].';':'';
            $attr = array(
                PDO::ATTR_TIMEOUT => 1,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
            $link = new PDO('mysql:host='.$_conf['host'].';port='.$_conf['port'].';'.$dbname.'charset='.$_conf['charset'],$_conf['user'], $_conf['pw'], $attr);
            $link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            $this->datalist[$table]['driver'] = 'PDO';

        }else if(class_exists('mysqli')){
            $dbname = isset($_conf['name']) ?  $_conf['name']:'';
            $link = new mysqli($_conf['host'],$_conf['user'],$_conf['pw'], $dbname,$_conf['port']);
            $link->set_charset($_conf['charset']);
            $this->datalist[$table]['driver'] = 'mysqli';
        }else{
            echo 'you must open the pdo_mysql.dll';
            exit;
        }
        if($_conf['id']==1)$this->master = $link;
        else $this->datalist[$table]['link'] = $link;
        return $link;
    }
	public function sql_prepare(Array $arr)
	{
		$link = $this->connect($arr['table']);
        $config = $this->datalist[$arr['table']];
		if(empty($arr['method']))$arr['method'] = 'SELECT';
		$arr['method'] = strtoupper($arr['method']);
		if($arr['method']=='INSERT')$arr['method'] = 'INSERT INTO';
		$sql = $arr['method'].' ';
		$tablename = '';
		//SELECT/INSERT INTO/UPDATE/REPLACE INTO/INSERT IGNORE INTO/INSERT DELAYED INTO
		//REPLACE INTO
		//ON DUPLICATE KEY UPDATE
		//DELETE FROM
		//SHOW
		//contents=values(contents),title=values(title);
		if(!empty($arr['table']))$tablename = ' `'.$this->table($arr['table']).'` ';
		$where = '';
		if(in_array($arr['method'],array('SELECT','UPDATE','DELETE'))){
			$blind = array();
			if($arr['method'] == 'DELETE'){
				$sql .= ' FROM '.$tablename;
			}else if($arr['method'] == 'SELECT'){
				$isjoin = !empty($arr['join'])&&is_array($arr['join']);
				if(!empty($arr['order'])&&is_array($arr['order'])){
					if(!empty($arr['order'][0])){
						$orderid = preg_replace('/\s/','',$arr['order'][0]);
						$where .= ' ORDER BY `'.$orderid.'` '.(!isset($arr['order'][1])&&strtoupper($arr['order'][1])=='ASC' ? 'ASC':'DESC');
					}else{
						$orderdata = '';
						foreach($arr['order'] as $orderkey=>$order){
							$orderkey2 = preg_replace('/\s/','',$orderkey);
							if(!empty($orderkey2)){
								$orderdata .='`'.$orderkey2.'` '.($order&&strtoupper($order)=='ASC'?'ASC':'DESC').',';
							}
						}
						$where .= ' ORDER BY '.substr($orderdata,0,-1);
					}
				}
				if(!empty($arr['limit'])&&is_array($arr['limit'])){
					$arr['limit'] = array_map(fn($v)=>intval($v),$arr['limit']);
					$where .= ' LIMIT '.implode(',',$arr['limit']);
				}
				if(isset($arr['select'])&&$arr['select']!=='*'&&!empty($arr['select'])){;
					if(is_string($arr['select'])&&stripos($arr['select'],'COUNT(')!==false || is_array($arr['select'])&&stripos($arr['select'][0],'COUNT(')!==false){
						$selecttxt = is_array($arr['select']) ? $arr['select'][0]:$arr['select'];
						$arr['selectext'] = 'COUNT('.($isjoin?'`dxA`.':'').substr($selecttxt,6);
						$arr['FetchMode'] = 'NUM';
						$arr['countmode'] = true;
						$arr['mode'] = 'fetch';
					}else{
						$arr['selectext'] = $this->prepare_select_array($arr['select'],$isjoin?'`dxA`.':'');
					}
				}else{
					$arr['selectext'] = $isjoin?'`dxA`.*':' * ';
				}
				$_sql =' FROM '. $tablename;
				if($isjoin){
					$_sql.=' as dxA ';
					foreach($arr['join'] as $k=>$v){
						if(!empty($v['key'])&&(!empty($v['table']) || !empty($arr['tabletxt']))){
							$v['type'] = empty($v['type'])?'left':$v['type'];
							$_sql.= $v['type'].' join ';
							if(!empty($v['table'])){
								$_sql.=' `'.$this->table($v['table']).'` ';
							}elseif(!empty($arr['tabletxt'])){
								$_sql.=$arr['tabletxt'];
							}
							$_sql .= ' as dxB'.$k.' ';
							if(is_string($v['key'])){
								$_sql .= ' on `dxB'.$k.'`.`'.$v['key'].'`=`dxA`.`'.$v['key'].'` ';
							}else{
								$mkey = [];
								foreach($v['key'] as $m){
									$mkey = ' on `dxB'.$k.'`.'.$m.'=`dxA`.'.$m.' ';
								}
								$_sql .= implode(' AND ',$mkey);
							}
							if(empty($arr['countmode'])){
								if(!empty($v['select'])){
									$newselect = $this->prepare_select_array($v['select'],'`dxB'.$k.'`.');
									if(!empty($newselect)){
										$arr['selectext'].=','. $newselect;			
									}
									$newselect = '';
								}else{
									$arr['selectext'].=',`dxB'.$k.'`.*';
								}
							}
						}
					}
				}
				$sql .= $arr['selectext'].$_sql;
			}else if(isset($arr['data'])){
				$setvar = [];
				$sql .= $tablename;
				foreach($arr['data'] as $k=>$v){
					$spt = strstr($k,':',true);
					$key = $k;
					if($spt){
						$key = substr($k,strlen($spt)+1);
						if($spt == 'file'){
							$setvar[] = '`'.$k.'`=values(load_file(:'.$k.')';
						}else{
							if($spt == '+')$spt = '=`'.$key.'` + ';
							if($spt == '-')$spt = '=`'.$key.'` - ';
							$setvar[] = '`'.$key.'` '.$spt.' :'.$key.' ';
						}
						
					}else{
						$setvar[] = '`'.$key.'`= :'.$key.' ';
					}
					$blind[':'.$key] = is_array($v) ? serialize($v):$v;
				}
				$sql .= "\nSET ".implode(",\n",$setvar);
			}else{
				return array();
			}
			if(!empty($arr['wdata'])&&empty($arr['where'])){
				$arr =array_merge($arr,self::filter_fetch_where($arr['wdata'],$arr));
			}
			if(!empty($arr['where'])){
				$where .= ' WHERE ';
				if(is_string($arr['where'])){
					if(!empty($arr['wdata'])&&is_array($arr['wdata'])){
						foreach($arr['wdata'] as $k=>$v){
							if(!is_array($v)&&stripos($k,':dx_')!==false&&stripos($arr['where'],$k)!==false){
								$blind[$k] = $v;
							}elseif(stripos($arr['where'],':'.$k)!==false){
								if(is_array($v)){
									if(preg_match("/[\'\"\`]?{$k}[\'\"\`]?\s*?(in|not\sin|\=|\!\=)\s*?:{$k}/i",$arr['where'],$mathches)){
										list($arr['where'],$blind) = $this->filter_prepare_where($arr['where'],in_array($mathches[1],array('=','in'))?1:0,$k,$v,$blind);
									}
								}else{
									$arr['where'] = str_replace(":$k"," :dx_$k ",$arr['where']);
									$blind[':dx_'.$k]  = $v;
								}
							}
						}
					}
					$where .= ' '.$arr['where'].' ';
				}
			}
			$where .=' ;'; 
			$sql .=$where;
			$t = microtime(1);
            if($config['driver']=='mysqli'){
                $blinds = '';
                $blind = $this->mysqli_set_blind($sql,$blind);
                foreach($blind  as $k=>$v){
                    if(is_integer($v)){
                        $blinds .='i';
                        $blind[$k] = (int) $v;
                    }else{
                        $blinds .='s';
                    }
                }
                $sth = $link->prepare($sql);
                if(!empty($blind)){
                    $sth->bind_param($blinds,...$blind);
                }
                $sth->execute();
            }else{
                $sth = $link->prepare($sql);
                if (empty($blind))$sth->execute();
                else $sth->execute($blind);
            }
			if($arr['method'] == 'SELECT'){
                if($config['driver']=='PDO'){
                    if(!empty($arr['FetchMode'])){
                        $this->pdo_setFetchMode($arr['FetchMode'],$sth);
                    }
                    if(!empty($arr['mode'])&&$arr['mode']=='fetch'){
                        $result = $sth->fetch();
                    }else{
                        $result = $sth->fetchAll();
                        if(isset($arr['PRIMARY'])&&!empty($arr['PRIMARY'])){
                            $newresult = [];
                            foreach($result as $v){
                                if(isset($v[$arr['PRIMARY']])){
                                    $newresult[$v[$arr['PRIMARY']]] = $v;
                                }
                            }
                            if(!empty($newresult)){
                                $result = $newresult;
                            }
                        }
                    }
                }else{
                    $result_data = $sth->get_result();
                    if(!empty($arr['mode'])&&$arr['mode']=='fetch'){
                        $result = call_user_func(array($result_data,$arr['FetchMode']?'fetch_row':'fetch_assoc'));
                    }else{
                        for ($result = array (); $row = call_user_func(array($result_data,$arr['FetchMode']?'fetch_row':'fetch_assoc'));){
                            if(isset($arr['PRIMARY'])&&!empty($arr['PRIMARY'])&&isset($row[$arr['PRIMARY']])){
                                $result[$row[$arr['PRIMARY']]] = $row;
                            }else{
                                $result[] = $row;
                            }
                        }

                    }
                }
                $this->close($sth);
				$this->prepare_add_sqllist($sql,$t);
				return empty($result)?array():$result;
			}else{
				$result = !empty($sth->affected_rows)?$sth->affected_rows:$sth->rowCount();
				$this->close($sth);
				$this->prepare_add_sqllist($sql,$t);
				return $result>0 ? array('line'=>$result):array();
			}
		}else {
			$sql .= $tablename;
			if(isset($arr['mdata'])){
				list($sqlname,$temp,$update) = $this->filter_prepare_keys($arr['mdata'][0]);
                if($config['driver']=='mysqli')$temp = preg_replace('/\:[^\s]+/','?',$temp);
				$msql = $sql.$sqlname."\nVALUES".$temp;
				if(isset($arr['update'])){
					$msql .= "\nON DUPLICATE KEY UPDATE \n".$update;
				}
				$msql .= ';';
				$result = 0;
				$lastid = [];
				foreach($arr['mdata'] as $k=>$v){
					$t = microtime(1);
					$sth = $link->prepare($msql);
                    if($config['driver']=='mysqli'){
                        if(!empty($v)){
                            $vs = '';
                            $vp = array();
                            foreach($v  as $vk=>$vv){
                                if(is_integer($vv)){
                                    $vs .='i';
                                    $vp[] = (int) $vv;
                                }else{
                                    $vs .='s';
                                    $vp[] = $vv;
                                }
                            }
                            $sth->bind_param($vs,...$vp);
                        }
                        $sth->execute();
                        $result +=  $sth->affected_rows;
                        $lastid[] =  $sth->insert_id;
                    }else{
                        if (empty($v))$sth->execute();
                        else $sth->execute($v);
                        $result += $sth->rowCount();
                        $lastid[] = $link->lastInsertId();
                    }
					$this->close($sth);
					$this->prepare_add_sqllist($msql,$t);
				}
				return $result>0||!empty($lastid) ? array('lastid'=>$lastid,'line'=>$result) :array();
			}else if(isset($arr['data'])){
				list($sqlname,$temp,$update) = $this->filter_prepare_keys($arr['data']);
                if($config['driver']=='mysqli')$temp = preg_replace('/\:[^\s]+/','?',$temp);
				$msql = $sql.$sqlname."\nVALUES".$temp;
				$sqldata = array();
				foreach($arr['data'] as $k=>$v){
					$sqldata[':'.$k] = $v;
				}
				if(isset($arr['update'])&&$arr['update']==true){
					$msql .= "\nON DUPLICATE KEY UPDATE \n".$update;
				}
				$msql .= ';';
				$t = microtime(1);
				$sth = $link->prepare($msql);
                if(empty($sth)) return array();
                if($config['driver']=='mysqli'){
                    if(!empty($sqldata)){
                        $sqldata2s = '';
                        $sqldata2 = array();
                        foreach($sqldata  as $k=>$v){
                            if(is_integer($v)){
                                $sqldata2s .='i';
                                $sqldata2[] = (int) $v;
                            }else{
                                $sqldata2s .='s';
                                $sqldata2[] = $v;
                            }
                        }
                        $sth->bind_param($sqldata2s,...$sqldata2);
                    }
                    $sth->execute();
                }else{
                    if (empty($sqldata))$sth->execute();
                    else $sth->execute($sqldata);
                }
				//影响行数 line rowCount
                if($config['driver']=='mysqli'){
                    if($sth->error){
                        discuz_error::system_error($sth->error, true, false, true);
                    }
                    $result =  $sth->affected_rows;
                    $lastid =  $sth->insert_id;
                }else{
                    $result = $sth->rowCount();
                    $lastid = $link->lastInsertId();

                }
				$this->close($sth);
				$this->prepare_add_sqllist($msql,$t);
				return $result>0||!empty($lastid) ? array('lastid'=>$lastid,'line'=>$result) :array();
			}
		}
	}

	public function pdo_setFetchMode($mode,$sth)
	{
		$mode = strtoupper($mode);
		if($mode=='NUM')$sth->setFetchMode(PDO::FETCH_NUM);
		else if($mode=='CLASS'){
			$sth->setFetchMode(PDO::FETCH_BOTH|PDO::FETCH_OBJ);
			//$sth->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_CLASSTYPE);
		}
	}
	public function prepare_add_sqllist($sql,$t)
	{
		//if(count($this->sqls) < 1000) $this->sqls[] = substr(microtime(1)-$t, 0, 6).' '.$sql;
	}
	public function prepare_select_array($select,$join)
	{	
		if(is_string($select)){
			$select = array_map(fn($str)=>trim($str),explode(',',$select));
		}
		return $join.implode(','.$join,$this->filter_prepare_quote($select));
	}
	public function filter_prepare_where($where,$p,$k,$v,$blind)
	{
		//$newsql = '(';
		//$spt = $p ? '=' :'!=';
		//$endspt = $p  ? ' or ':' AND ';
		$newsql = [];
		foreach($v as $i=>$j){
			//$newsql .= '`'.$k .'` '.$spt.' :dx_'.$k.$i.$endspt;
			$newsql[] = ' :dx_'.$k.$i.' ';
			$blind[':dx_'.$k.$i] =  $j;
		}
		//$newsql = substr($newsql, 0, strlen($endspt)*-1);
		//$newsql .= ')';
		//$where = preg_replace("/[\'\"\`]?{$k}[\'\"\`]?\s*?[\w\=\!]+?\s*?:{$k}/",$newsql,$where);
		$spt = $p ? ' IN ' :' NOT IN ';
		$newsql = ' '.$k.$spt.'('.implode(',',$newsql).') ';
		$where = preg_replace("/[\'\"\`]?{$k}[\'\"\`]?\s*?[\w\=\!]+?\s*?:{$k}/",$newsql,$where);
		return [$where,$blind];
	}
	public function filter_prepare_quote($arr,$k="")
	{
		return array_map(fn($str)=>$k?" :{$str} ":"`{$str}`",$arr);
	}
	public function filter_prepare_keys($arr)
	{
		$arr = array_keys($arr);
		//$k."=values($k)";
		return array(
			0=>'('.implode(',',$this->filter_prepare_quote($arr)).')',
			1=>'('.implode(',',$this->filter_prepare_quote($arr,true)).')',
			2=>implode(",\n",array_map(fn($str)=>$str.'=values('.$str.')',$arr))
		);
	}
	public static function filter_fetch_where($where,$query=array())
	{
		$where_str = '';
		$result = array('condition'=>false);
		if(!empty($query['condition']))$result['condition'] = true;
		$cstr = $result['condition']==true ? ' OR ':' AND ';
		$where_arr = array();
		if(!empty($where)){
			if(is_array($where)){
				foreach($where as $k=>$v){
					$spt = strstr($k, ':',true);
					if($spt!==false){
						$key = substr($k,strlen($spt)+1); 
						$where_str .= ' `'.$key.'` '.$spt.' :'.$key.$cstr.' ';
						$where_arr[$key] = $v;
					}else{
						$where_str .= ' `'.$k.'` = :'.$k.$cstr.' ';
						$where_arr[$k] = $v;
					}
				}
				$result['where'] = substr($where_str, 0, strlen($cstr)*-1);
				$result['wdata'] = $where_arr;
			}elseif(is_string($where)&&!empty($query['wdata'])){
				$result['where'] = $where;
			}
		}
		return $result;
	}
	public function table($table){
		if(!empty($this->datalist[$table])){
			$this->dbconfig($table);
		}
		return $this->datalist[$table]['tablename'];
	}
	public static function update($table,$arr,$where,$condition=false,$query=array())
	{
		return self::insert($table,$arr,$where,false,'update',$condition,$query);
	}
	public static function insert($table,$arr,$where='',$update=false,$method='insert',$condition=false,$query=array()){
		$query['method']=$method;
		$query['table']=$table;
		if($condition==true)$query['condition'] = true;
		if(!empty($arr[0])){
			$query['mdata'] = $arr;
		}else{
			$query['data'] = $arr;
		}
		if(!empty($where)){
			if(is_array($where)){
				if(!empty($query['where']))unset($query['where']);
				$query['wdata'] = $where;
			}elseif(!empty($query['wdata'])){
				$query['where'] = $where;
			}
		}
		$query['update'] = $update;
		return self::prepare($query);
	}
	public static function delete($table,$where='',$query=array()){
		return self::fetch($table,$where,$query,'','DELETE');
	}
	public static function fetchAll($table,$where='',$query=array()){
		return self::fetch($table,$where,$query,'fetchAll');
	}
	public static function fetch(String $table,$where='',Array $query=array(),String $mode='fetch',String $method='SELECT'){
		$query['method'] = $method;
		$query['table'] = $table;
		$query['mode'] = $mode;
		if(!empty($where)){
			if(is_array($where)){
				if(!empty($query['where']))unset($query['where']);
				$query['wdata'] = $where;
			}elseif(!empty($query['wdata'])){
				$query['where'] = $where;
			}
		}
		if(empty($mode)){
			$query['mode']='fetch';
		}
		if($query['mode']=='fetch'){
			$query['limit'] = array(1);
		}
		return self::prepare($query);
	}
	public static function fetch_count($table,$select='',$where='',$condition=false)
	{
		$query = array(
			'method'=>'SELECT',
			'table'=>$table,
			'select'=>'COUNT('.(empty($select) || $select=='*'?'*':'`'.$select.'`').')',
			'condition'=>$condition
		);
		if(!empty($where)){
			if(is_array($where)){
				if(!empty($query['where']))unset($query['where']);
				$query['wdata'] = $where;
			}elseif(!empty($query['wdata'])){
				$query['where'] = $where;
			}
		}
		$result = self::prepare($query);
		if(empty($result) || empty($result[0])){
			$result = 0;
		}else{
			$result = $result[0];
		}
		return $result;
	}
	public static function prepare(Array $arr)
	{
		return self::app()->sql_prepare($arr);
	}
    public function close($sth)
    {
        method_exists($sth,'closeCursor')?$sth->closeCursor():$sth->close();
    }
    public function mysqli_set_blind(&$sql,$blind)
    {
        $this->newblind = array();
        if(!empty($blind)){
            $this->tmpblind = $blind;
            $blind = $blind||array();
            $sql = preg_replace_callback('/(\:[^\s]+)/',array($this,'mysqli_set_blind_callback'),$sql);
        }
        return $this->newblind;
    }
    public function mysqli_set_blind_callback($result)
    {
        $this->newblind[] = $this->tmpblind[$result[0]];
        return ' ? ';
    }
}