<?php
class transaction{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function newTransactions(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
			if (isset($array['num']))
					if (inseretSql($array,$this->_table))
						return true;
					else
						return false;
	}
	public function getTransactions(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$arr;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach ($list as $row){
				$bla=new transaction;
				foreach($row as $key=>$value)
					$bla->{$key}= $value;
				$arr[]=$bla;
			}
			return $arr;
		}
		return false;
	}
	public function updateTransactions($array){
		//rellay ugly need some work in th nir fuetre
		//if (!is_null($this->getItem($array['num'])))
		//	return updateSql($cond,$array,$this->_table);
		$a=new transaction;
		$a->num=$this->num;
		if ($a->deleteTranReps()){
			foreach ($array as $transaction){
				//$a=new documentDetail;
				if (!$transaction->newtTransaction()) return false;
			}
			return true;
		}
		return false;
	}
	public function deleteTransactionss(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $table;
		global $prefix;
		$this->_table = $table["transactions"];
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>