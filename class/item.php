<?
class item{
	//public $arr;
	private $_table='';
	private $_prefix;
	public function newItem(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array(1=>1),'num',$this->_table);
		if (isset($newnum)){
			$array['num']=$newnum;
			if (isset($array['name'])){
					if (inseretSql($array,$this->_table)){
						return $newnum;
					}else{
						return false;
					}
			}
		}
		return false;	
	}
	public function getItem(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		//return selectSql($cond,$this->_table);
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach($list[0] as $key=>$value)
				$this->{$key}= $value;
			return true;
		}else{
			return false;
		}
	}
	public function updateItem(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		if (isset($array['num'])) {
			$cond['num']=$array['num'];
			$cond['prefix']=$this->_prefix;
			$array['prefix']=$this->_prefix;
			//$a=$this->getItem($array['num']);
			//print_r($a);
			$a=new item;
			$a->num=$this->num;
			if ($a->getItem())
				return updateSql($cond,$array,$this->_table);
			else
				return false;
			
		}
	}
	public function deleteItem(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$a=new item;
		$a->num=$this->num;
		if ($a->getItem())
			return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $itemstbl;
		global $prefix;
		//print $itemtbl;
		$this->_table = $itemstbl;
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		//->prefix=$this->_prefix;
		return $this;
	}
}
?>