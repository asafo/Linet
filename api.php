<?php
/*
 *		api class is written by: Adam bh.
 */
require('config.inc.php');
require('include/core.inc.php');
require('include/func.inc.php');
require('class/user.php');
require('class/item.php');
require('class/account.php');
require('class/document.php');

//connect to db
$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");
mysql_select_db($database) or die("Could not select database: $database");

$output=array("sid"=>"-1");
session_start();
$prefix=$_SESSION['prefix'];
//print(":$prefix:");
if (isset($_SESSION["logedin"])){
	if (isset($_REQUEST["action"])){
		switch($_REQUEST["action"]){
			case "Logout":
				$output=Logout();
				break;
			case "Get":
				$output=GetData($_REQUEST['data'],$_REQUEST['num']);
				break;
			case "New":
				$output=SetData($_REQUEST['data']);
				break;
			case "Update":
				if (isset($_REQUEST['num']))
					$output=UpdateData($_REQUEST['data'],$_REQUEST['num']);
				else
					$output= array('data'=>'-3');
				break;
			default:
				$output= array("sid"=>"-2");
		}
	}
}else if (isset($_REQUEST["action"])){
	if ($_REQUEST["action"]=="Login"){
			$output= Login($_REQUEST['username'],$_REQUEST['appKey']);
	}
	
}
print $_REQUEST['jsoncallback'].'('.json_encode($output).')';
/*
##function login
gets:
	username
	applction key
return:
	session_id or false
*/
function Login($username,$appKey,$company=0){
	if (isset($_SESSION['logedin'])){
		session_unregister("logedin");
	}
	//getuser
	if (isset($username)){
		$usr=new user;
		$usr->name=$username;
	    $usr->getUser();
		if ($usr->name==$username)
			if ($usr->hash==$appKey){
				//session_unregister("logedin");
				 $_SESSION['logedin']=sha1(mt_rand().$usr->password);
				 $_SESSION['username']=$username;
				 //$_SESSION['prefix']=$usr->permissions[$company]['prefix'];//need to be
				 //$_SESSION['prefix']='testme';
				 return array('sid'=>$_SESSION['logedin']);
			}
	}
	return array('sid'=>'-1');
}
/*
##function logout
gets:
	username
return:
	true:succses
	false:not loged in
*/
function Logout(){
	session_unregister("logedin");
	session_unregister('username');
	return array('sid'=>'-1');
}
/*
 * function get
 * 
 * 
 */
function GetData($data,$id){
	switch ($data){
		case 'Item':
			$item=new item;
			$item->num =$id;
			$item->getItem();
			return $item;
			break;
		case 'Account':
			$acc=new account;
			$acc->num =$id;
			$acc->getAccount();
			return $acc;
			break;
		case 'Document':
			$doc=new document;
			$doc->num =$id;
			$doc->getDocument();
			return $doc;
			break;
		case "Company":
			global $permissionstbl,$companiestbl;
			$permtionlist=selectSql(array('name'=>$_SESSION['username']), $permissionstbl);
			$list1=array();
			foreach($permtionlist as $rec){
				if($rec['company']=='*'){
					$companylist=selectSql(array(1=>1),$companiestbl,array('companyname','prefix'));
					return $companylist;
				}else{
					$companylist=selectSql(array('prefix'=>$rec['company']),$companiestbl,array('companyname','prefix'));
					$list1=array_merge($list1,$companylist);
				}
			}
			return $list1;
			break;
		default:
			return array('data'=>'-2');
	}	
}
function SetData($data){
	$arr=$_REQUEST;
	switch ($data){
		case 'Item':
			$item=new item;
			$tmp=get_object_vars($item);
			foreach ($arr as $key=>$value){
				foreach ($tmp as $subkey=>$subvalue){
					if ($key==$subkey)
						$item->{$key}=$value;
				}
			}
			return  array("data"=>$item->newItem());
			break;
		case 'Account':
			$acc=new account;
			$tmp=get_object_vars($acc);
			foreach ($arr as $key=>$value){
				foreach ($tmp as $subkey=>$subvalue){
					if ($key==$subkey)
						$item->{$key}=$value;
				}
			}
			return  array("data"=>$acc->newAccount());
			break;
		case 'Document';
			$doc=new document;
			$tmp=get_object_vars($doc);
			foreach ($arr as $key=>$value){
				foreach ($tmp as $subkey=>$subvalue){
					if ($key==$subkey)
						$doc->{$key}=$value;
				}
			}
			return  array("data"=>$doc->newDocument());
			break;
		case "Company":
			$newprefix=$arr['company'];
			global $permissionstbl,$companiestbl;
			$permtionlist=selectSql(array('name'=>$_SESSION['username']), $permissionstbl);
			$list1=array();
			foreach($permtionlist as $rec){
				if($rec['company']=='*'){
					$list1=selectSql(array(1=>1),$companiestbl,array('companyname','prefix'));
				}else{
					$companylist=selectSql(array('prefix'=>$rec['company']),$companiestbl,array('companyname','prefix'));
					$list1=array_merge($list1,$companylist);
				}
			}
			foreach($list1 as $rec){
				if($rec['prefix']==$newprefix)
					$_SESSION['prefix']=$newprefix;
			}
			return array("data"=>1);
		default:
			
			return array('data'=>'-2');
	}
}
function UpdateData($data,$id){
	$arr=$_REQUEST;
	switch ($data){
		case 'Item':
			$item=new item;
			$tmp=get_object_vars($item);
			foreach ($arr as $key=>$value){
				foreach ($tmp as $subkey=>$subvalue){
					if ($key==$subkey){
						$item->{$key}=$value;
					}
				}
			}
			return array("data"=>$item->updateItem());
			break;
		case 'Account':
			$acc=new account;
			$tmp=get_object_vars($acc);
			foreach ($arr as $key=>$value){
				foreach ($tmp as $subkey=>$subvalue){
					if ($key==$subkey)
						$acc->{$key}=$value;
				}
			}
			return array("data"=>$acc->updateAccount());
			break;
		case 'Document';
			$doc=new document;
			$tmp=get_object_vars($doc);
			foreach ($arr as $key=>$value){
				foreach ($tmp as $subkey=>$subvalue){
					if ($key==$subkey)
						$doc->{$key}=$value;
				}
			}
			//if type is inv or inv recipet then return data -4
			return array("data"=>$doc->updateDocument());
			break;
		default:
			return array('data'=>'-2');
	}
}
?>
