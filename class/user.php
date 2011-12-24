<?php
class user{
	//public $arr;
	private $_table='';
	/**
	//new user
	**/
	public function login($username='',$password=null,$data=null,$hash=null){
		//print "bla";
		//global $curuser;
		if($username==''){
			return $this->logout();
		}else {
			
			
			//$curuser=new user;
			//print_r($curuser);
			$this->name=$username;
			//print_r($curuser);
			//print $username;
			$this->getUser();
			//print_r($curuser);
			//print "password1: ".sha1($password)."<br />";
			//print "password2: ".$curuser->password."<br />";
			if(!is_null($password)){
				if($this->password==sha1($password))
					return $this->dologin();
				else
					return "pass";
			}else if(!is_null($data)){
				if($_SESSION['data']==$data)
					return $this->dologin($data);
				else
					if($this->cookie==$data)
						return $this->dologin($data);
					else
						//print "error";
						return "sessionordbtocookie";
			//}else if(!is_null($cookie)){
				
				//return "cookie";
			}else if(!is_null($hash)){
				if($this->hash==$hash)
					return $this->dologin();
				else
					return "hash";
			}	else return "no login";
		}
	}
	private function dologin($data=null){

		
		//global $curuser;
		$cookietime = time() + 60*60*24*30;
		if(is_null($data))
			$data = md5(sha1(rand()));
		$name=$this->name;
		
		setcookie('name', $name, $cookietime);
		setcookie('data', $data, $cookietime);
		//print "bla";
		$_SESSION['loggedin']=true;
		$_SESSION['name']=$name;
		$_SESSION['data']=$data;
		$_SESSION['user']=serialize($curuser);
		//print_r($_SESSION);
		$curuser->lastlogin=time();
		if($remmberme)
			$this->cookie=$data;
		return true;//$curuser->updateUser();
		
	}
	public function logout(){
		
		global $loggedin;
		if($loggedin){
			setcookie('name', '', -1);
			setcookie('data', '', -1);
			setcookie('company', '', -1);
	
			unset($_SESSION['loggedin']);
			unset($_SESSION['name']);
			unset($_SESSION['data']);
			unset($_SESSION['user']);
			unset($_SESSION['company']);
			unset($_SESSION);
			$action="";
			$module="";
			
			
			 $loggedin=false;
			return true;//$curuser->updateUser();
		}else 
			return false;
		
	}
	public function newUser(){
		$array=get_object_vars($this);
		unset($array['_table']);
		$array['password']=sha1($array['password']);
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