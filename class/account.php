<?
class account{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function getAccount(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach($list[0] as $key=>$value)
				$this->{$key}= $value;
			return true;
		}else{
			return false;
		}
	}
	public function newAccount(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array('prefix'=>$this->_prefix),'num',$this->_table);
		//print_r($newnum);
		if (isset($newnum)){
			$array['num']=$newnum;
			if (isset($array['company'])){
					if (inseretSql($array,$this->_table)){
						return $newnum;
					}else{
						return false;
					}
			}
		}
		return false;
	}
	public function updateAccount(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		if (isset($array['num'])) {
			$cond['num']=$array['num'];
			$cond['prefix']=$this->_prefix;
			$array['prefix']=$this->_prefix;
			$a= new account;
			$a->num=$this->num;
			//print_r($a);
			if ($a->getAccount()){
				return updateSql($cond,$array,$this->_table);
			}else{
				return false;
			}
		}
	}
	public function deleteAccount(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$a=new account;
		$a->num=$this->num;//=$this->getAccount($id);
		if ($a->getAccount())
			return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $accountstbl;
		global $prefix;
		$this->_table = $accountstbl;
		$this->_prefix=$prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>