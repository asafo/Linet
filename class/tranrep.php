<?php //הוצעות לא טוב
class tranRep{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function newTranRep(){
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
	public function getTranReps(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$arr;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach ($list as $row){
				$bla=new tranRep;
				foreach($row as $key=>$value)
					$bla->{$key}= $value;
				$arr[]=$bla;
			}
			return $arr;
		}
		return false;
	}
	public function updateTranReps($array){
		//rellay ugly need some work in th nir fuetre
		//if (!is_null($this->getItem($array['num'])))
		//	return updateSql($cond,$array,$this->_table);
		$a=new tranRep;
		$a->num=$this->num;
		if ($a->deleteTranReps()){
			foreach ($array as $tranRep){
				//$a=new documentDetail;
				if (!$tranRep->newtranRep()) return false;
			}
			return true;
		}
		return false;
	}
	public function deleteTranReps(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $table;
		global $prefix;
		$this->_table = $table["tranrep"];
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>