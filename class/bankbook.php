<?php
class bankbook{
	private $_table;
	private $_prefix;
	
	public function __construct(){
		global $table;
		global $prefix;
		$this->_table = $table["bankbook"];
		$this->_prefix = $prefix;
		
		$values=listCol($this->_table);
		foreach($values as $value)
			$this->{$value['Field']}= '';
		
		return $this;
	}
	
	public function newBankbook(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array('prefix'=>$this->_prefix,'account'=>$this->account),'num',$this->_table);
		if (isset($newnum)){
			$array['num']=$newnum;
			if (isset($array['account'])){
				if (inseretSql($array,$this->_table))
					return $newnum;
			}
		}
		return false;
	} 
	public function deleteBankbook($id){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$a=new document;
		$a->num=$this->num;
		if ($a->getDocument())
			return (deleteSql($cond,$this->_table));
		return false;
	}
	public function updateBankbook(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		if (isset($array['num'])) {
			$cond['num']=$array['num'];
			$cond['prefix']=$this->_prefix;
			$array['prefix']=$this->_prefix;
			$a= new bankbook;
			$a->num=$this->num;
			//print_r($a);
			if ($a->getBankbook()){
				return (updateSql($cond,$array,$this->_table));	
			}
		}
		return false;
	}
	public function getBankbook(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach($list[0] as $key=>$value)
				$this->{$key}= $value;
			return true;	
		}	
		return false;
	}
	public function searchBankbook(){
		//$cond['prefix']=$this->_prefix;
		//$cond['num']=$this->num;
		$cond=get_object_vars($this);
		$cond['prefix']=$this->_prefix;
		unset($cond['_table']);
		unset($cond['_prefix']);
		unset($cond['num']);
		unset($cond['cor_num']);
		unset($cond['total']);
		//print_r($cond);
		$list= selectSql($cond,$this->_table);
		
		if ($list){
			//foreach($list[0] as $key=>$value)
			//	$this->{$key}= $value;
			return true;	
		}	
		return false;
	}
}
?>