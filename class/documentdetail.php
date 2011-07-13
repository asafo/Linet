<?
class documentDetail{
	public $arr;
	private $_table='';
	private $_prefix;
	
	public function newDetial($array){
		$array['prefix']=$this->_prefix;
			if (isset($array['num']))
					if (inseretSql($array,$this->_table))
						return $newnum;
					else
						return $false;
	}
	public function getDetials($id){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$id;
		return selectSql($cond,$this->_table);
	}
	public function updateDetials($array){
		//rellay ugly need some work in th nir fuetre
		//if (!is_null($this->getItem($array['num'])))
		//	return updateSql($cond,$array,$this->_table);
		deleteDetials($array);
		foreach ($array as $detial){
			//if (isset($detial['num'])) {
				$detial['num']=$this->_prefix;
				newDetial($detial);
				
			//}
		}
		return true
	}
	public function deleteDetials($id){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$id;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $docdetailstbl;
		global $prefix;
		$this->_table = $docdetailstbl;
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		foreach ($values as $value) $this->arr[$value['Field']]='';
		return $this;
	}
}

?>