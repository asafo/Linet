<?
class item{
	public $arr;
	private $_table='';
	private $_prefix;
	public function newItem($array){
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array('prefix'=>$prefix),'num',$this->_table);
		if isset($newnum){
			$array['num']=$newnum;
			if (isset($array['name']))
					if (inseretSql($array,$this->_table))
						return $newnum;
					else
						return $false;
		}
	}
	public function getItem($id){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$id;
		return selectSql($cond,$this->_table);
	}
	public function updateItem($array){
		if (isset($array['num'])) {
			$cond['num']=$array['num'];
			$cond['num']=$this->_prefix;
			if (!is_null($this->getItem($array['num'])))
				return updateSql($cond,$array,$this->_table);
		}
	}
	public function deleteItem($id){
		$cond['prefix']=$this->_prefix;
		$cond['name']=$id;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $itemtbl;
		global $prefix;
		$this->_table = $itemtbl;
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		foreach ($values as $value) $this->arr[$value['Field']]='';
		return $this;
	}
}
?>