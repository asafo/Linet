<?php
if(isset($_REQUEST['term']))
	$letters = $_REQUEST['term'];
else 
	$letters='*';
	
if(isset($_REQUEST['type']))
	$type = $_REQUEST['type'];
else 
	$type=0;

if ($letters=='*') $letters='';

if (isset($_REQUEST['selector'])){
	if (isset($_REQUEST['data'])){
		//print 'sos: '.$_REQUEST['data'];
		//$data =  new $_REQUEST['data'];
		$className = $_REQUEST['data'];
		require 'class/item.php';
		require 'class/account.php';
		//print $className;
		$data = new $className();
		//$data= new item;
		//print_r($data);
		$data->num=$_REQUEST['num'];
		$get='get'.$_REQUEST['data'];
		//print $get;
		$data->$get();
		//$data->getAccount();
		//print_r($data);
		print json_encode($data);
		exit;
	}	
}
if (isset($_REQUEST['data'])){
	$data=$_REQUEST['data'];
	//print $data;
	if ($data=='Item'){
		$res = mysql_query("SELECT * FROM $itemstbl WHERE name like '%".$letters."%' AND prefix='$prefix'") or die(mysql_error());
		$data=array();
		while($inf = mysql_fetch_array($res)){
			$data[]=array("label"=>$inf["name"],"value"=>$inf["num"]);
		}
	}
	if ($data=='Account'){
		$res = mysql_query("SELECT * FROM $accountstbl WHERE company like '%".$letters."%' AND prefix='$prefix' AND type='$type'") or die(mysql_error());
		$data=array();
		while($inf = mysql_fetch_array($res)){
			$data[]=array("label"=>$inf["company"],"value"=>$inf["num"]);
		}
	}	
	//print json_encode($data);
	print $_REQUEST['jsoncallback'].'('.json_encode($data).')';
}
//print 'bla';
if (isset($_REQUEST['form'])){
	$form=$_REQUEST['form'];
	$smallprint=true;
	
	if ($form=='items'){
		printHtml();
		include('items.php');
	}
	if ($form=='account'){
		printHtml();
		include('acctadmin.php');
	}
	if($form=='credit'){
		//printHtml();
		include('credit.php');
	}
}
	

?>