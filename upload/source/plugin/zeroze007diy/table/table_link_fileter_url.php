<?php
if(!defined('IN_DISCUZ')){
	exit('Access Denied');
}

class table_link_fileter_url extends discuz_table
{
	public function __construct(){
		$this->_table	= 'link_fileter_url';
		$this->_pk		= 'id';
		parent::__construct();
	}

	public function fetch_all_uid_ac(){
    	return DB::fetch_all('select * from %t order by uid asc ',array($this->_table));
    }

	public function fetch_first_by_id($id){
    	return DB::fetch_first('select * from %t where id=%d',array($this->_table, $id));
    }

	public function fetch_first_by_code($code){
    	return DB::fetch_first('select * from %t where Code=%d',array($this->_table, $code));
    }
	
    public function fetch_limit($offset,$limit){
    	return DB::fetch_all('select * from %t order by uid asc limit %d,%d',array($this->_table, $offset, $limit));
    }
    
  	public function fetch_count(){
    	return DB::result_first('select count(*) from %t',array($this->_table));
    }
}
?>