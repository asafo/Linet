<?php
class company{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function getCompany(){
		$cond['prefix']=$this->_prefix;
		//$cond['num']=$this->num;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach($list[0] as $key=>$value)
				$this->{$key}= $value;
			return true;
		}else{
			return false;
		}
	}
	public function newCompany(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;	
		$a= new company();
		$a->prefix=$this->prefix;
		if (!$a->getCompany()){
			if (isset($array['prefix'])){
					if (inseretSql($array,$this->_table)){
						//add new accounts
						return true;
					}else{
						return false;
					}
			}
		}
		return false;
	}
	public function updateCompany(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		if (isset($array['prefix'])) {
			//$cond['num']=$array['num'];
			$cond['prefix']=$this->_prefix;
			$array['prefix']=$this->_prefix;
			$a= new company();
			$a->prefix=$this->prefix;
			//print_r($a);
			if ($a->getAccount()){
				return updateSql($cond,$array,$this->_table);
			}else{
				return false;
			}
		}
	}
	public function deleteCompany(){
		$cond['prefix']=$this->_prefix;
		//$cond['num']=$this->num;
		$a=new company();
		$a->prefix=$this->prefix;//=$this->getAccount($id);
		if ($a->getCompany())
			return deleteSql($cond,$this->_table);
			//needs to delete all other tables
	}
	public function __construct(){
		global $table;
		global $prefix;
		$this->_table = $table["companies"];
		$this->_prefix=$prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>