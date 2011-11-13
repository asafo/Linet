<?php
class receiptDetail{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function newDetial(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
			if (isset($array['refnum']))
					if (inseretSql($array,$this->_table))
						return true;
					else
						return false;
	}
	public function getDetials(){
		$cond['prefix']=$this->_prefix;
		$cond['refnum']=$this->refnum;
		$arr;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach ($list as $row){
				$bla=new receiptDetail;
				foreach($row as $key=>$value)
					$bla->{$key}= $value;
				$arr[]=$bla;
			}
			return $arr;
		}
		return false;
	}
	public function updateDetials($array){
		//rellay ugly need some work in th nir fuetre
		//if (!is_null($this->getItem($array['num'])))
		//	return updateSql($cond,$array,$this->_table);
		$a=new receiptDetail();
		$a->refnum=$this->refnum;
		if ($a->deleteDetials()){
			foreach ($array as $detial){
				//$a=new documentDetail;
				if (!$detial->newDetial()) return false;
			}
			return true;
		}
		return false;
	}
	public function deleteDetials(){
		$cond['prefix']=$this->_prefix;
		$cond['refnum']=$this->refnum;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $table;
		global $prefix;
		$this->_table = $table["cheques"];
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>