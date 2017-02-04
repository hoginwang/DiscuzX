<?php
if(!defined('IN_DISCUZ')){
	exit('Access Denied');
}

class table_common_member_loginlog extends discuz_table
{
	public function __construct(){
		$this->_table	= 'common_member_loginlog';
		$this->_pk		= 'lid';
		parent::__construct();
	}
	
  	public function fetch_count(){
    	return DB::result_first('select count(*) from %t ',array($this->_table));
    }
 	public function fetch_limit($offset,$limit){
    	return DB::fetch_all('select * from %t order by createtime desc limit %d,%d ',array($this->_table,$offset,$limit));
    }
	public function fetch_all_my(){
    	return DB::fetch_all('select * from %t order by createtime desc ',array($this->_table));
    }
	public function fetch_first_by_id($lid){
    	return DB::fetch_first('select * from %t where lid=%d',array($this->_table,$lid));
    }
    
    public function fetch_all_by_uid_today($uid,$today){
    	return DB::fetch_all('select * from %t where uid=%d and createtime>=%d',array($this->_table,$uid,$today));
    }
    public function fetch_limit_search( $where,$offset,$limit){
    	return DB::fetch_all('select * from %t %i  order by createtime desc limit %d,%d',array($this->_table,$where,$offset,$limit));
    }
  	public function fetch_search_count($where){
    	return DB::result_first('select count(*) from %t %i',array($this->_table,$where));
    }
}
?>