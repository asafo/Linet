<?
class user{
	public $arr;
	private $_table='';
	/**
	//new user
	**/
	public function newUser($array){
		if (isset($array['name'])){//if not not a valid user
			$a=$this->getUser($array['name']);
			if (!isset($a['name'])){//if user exsits
				return inseretSql($array,$this->_table);
			}
			return false;	
		}
		//if courrent user have permtion to create user
	}
	public function updateUser($array){
		if (isset($array['name'])) {
			$cond['name']=$array['name'];	
			if (!is_null($this->getUser($array['name'])))
				return updateSql($cond,$array,$this->_table);
		}
		//if sucsses return true else if dosnt exsits call inseret else return false
	}
	public function deleteUser($uid){
	$cond['name']=$uid;
	return deleteSql($cond,$this->_table);
	
	}
	public function getUser($uid){
		$cond['name']=$uid;
		return selectSql($cond,$this->_table);
	//if exsts return array else return false
	}
	public function __construct(){
		global $logintbl;
		$this->_table = $logintbl;
		$values=listCol($this->_table);
		foreach ($values as $value) $this->arr[$value['Field']]='';
		return $this;
	}
	public function listUserTypeCol(){
		$values=listCol($this->_table);
		foreach ($values as $value) $arr[]=$value['Type'];
		return $arr;
	}
}
?>