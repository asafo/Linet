<?
class user{
	//public $arr;
	private $_table='';
	/**
	//new user
	**/
	public function newUser(){
		$array=get_object_vars($this);
		unset($array['_table']);
		if (isset($array['name'])){//if not not a valid user
			//$a=$this->getUser($array['name']);
			$a=new user;
			$a->name=$array['name'];
			//print_r($a);
			//print ':'.$a->getUser();
			//print_r($a);
			if (!($a->getUser()))//if user exsits
				return inseretSql($array,$this->_table);
			return false;	
		}
		//if courrent user have permtion to create user
	}
	public function updateUser(){
		$array=get_object_vars($this);
		unset($array['_table']);
		if (isset($array['name'])) {
			$cond['name']=$array['name'];
			$a=new user;
			$a->name=$array['name'];
			//print_r($this->getUser($array['name']));
			if ($a->getUser())
				return updateSql($cond,$array,$this->_table);
		}
		return false;
		//if sucsses return true else if dosnt exsits call inseret else return false
	}
	public function deleteUser(){
		$cond['name']=$this->name;
		$a=new user;
		$a->name=$this->name;
		if ($this->getUser())
			return deleteSql($cond,$this->_table);
		return false;
	}
	public function getUser(){
		$cond['name']=$this->name;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach($list[0] as $key=>$value)
				$this->{$key}= $value;
			//print_r($this);
			return true;
		}
		return false;
	//if exsts return array else return false
	}
	public function __construct(){
		global $logintbl;
		$this->_table = $logintbl;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
	public function listUserTypeCol(){
		$values=listCol($this->_table);
		foreach ($values as $value) $arr[]=$value['Type'];
		return $arr;
	}
}
?>