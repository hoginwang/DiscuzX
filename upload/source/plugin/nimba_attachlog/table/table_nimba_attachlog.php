<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_nimba_attachlog extends discuz_table{
	public function __construct() {

		$this->_table = 'nimba_attachlog';
		$this->_pk    = 'id';
		$this->_pre_cache_key = 'nimba_attachlog_';

		parent::__construct();
	}
	public function count(){
		$count = DB::result_first('SELECT COUNT(*) FROM %t', array($this->_table));
		return $count;
	}
	public function count_by_aid($aid){
		$count = DB::result_first('SELECT COUNT(*) FROM %t where aid=%d', array($this->_table,$aid));
		return $count;
	}
	public function count_by_uid($uid){
		$count = DB::result_first('SELECT COUNT(*) FROM %t where uid=%d', array($this->_table,$uid));
		return $count;
	}	
	public function count_by_aid_uid($aid,$uid){
		$count = DB::result_first('SELECT COUNT(*) FROM %t where aid=%d and uid=%d', array($this->_table,$aid,$uid));
		return $count;
	}	
	public function count_by_aid_ip($aid,$ip){
		$count = DB::result_first('SELECT COUNT(*) FROM %t where aid=%d and ip=%s', array($this->_table,$aid,$ip));
		return $count;
	}		
	public function max_id() {
		return DB::result_first('SELECT MAX(id) FROM %t', array($this->_table));
	}

	public function fetch_all_by_range($start,$end) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY id DESC LIMIT %d,%d', array($this->_table,$start,$end), $this->_pk);
	}
	public function fetch_all_by_aid_range($aid,$start,$end) {
		return DB::fetch_all('SELECT * FROM %t where aid=%d ORDER BY id DESC LIMIT %d,%d', array($this->_table,$aid,$start,$end), $this->_pk);
	}
	public function fetch_all_by_uid_range($uid,$start,$end) {
		return DB::fetch_all('SELECT * FROM %t where uid=%d ORDER BY id DESC LIMIT %d,%d', array($this->_table,$uid,$start,$end), $this->_pk);
	}	
	public function clear() {
		return DB::query('delete from %t',array($this->_table));
	}	
	public function drop() {
		return DB::query('DROP TABLE IF EXISTS %t',array($this->_table));
	}
}

?>