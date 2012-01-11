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
	if (isset($_POST['data'])){
		//print 'sos: '.$_REQUEST['data'];
		//$data =  new $_REQUEST['data'];
		$className = $_POST['data'];
		require 'class/item.php';
		require 'class/account.php';
		//print $className;
		$data = new $className();
		//$data= new item;
		//print_r($data);
		$data->num=$_REQUEST['num'];
		$get='get'.$_POST['data'];
		//print $get;
		$data->$get();
		//$data->getAccount();
		//print_r($data);
		if($className=='Item'){
			//$acc=($data->account);
			$a =new account();
			$a->num=$data->account;
			$a->getAccount();
			//if(!is_null($a->vat))
				$data->vat=$a->src_tax;
			//else
			//	$data->vat=100;
		}
		print json_encode($data);
		exit;
	}	
}
if ((isset($_GET['data'])||isset($_POST['data']))){
	$data=GetPoster('data');
	//print $data;
	if ($data=='Item'){
		$res = mysql_query("SELECT * FROM $itemstbl WHERE name like '%".$letters."%' AND prefix='$prefix'") or die(mysql_error());
		$data=array();
		while($inf = mysql_fetch_array($res)){
			$data[]=array("label"=>$inf["name"],"value"=>$inf["num"]);
		}
	}
	if ($data=='Account'){
		if($type=='B')
			$query="SELECT * FROM $accountstbl WHERE company like '%".$letters."%' AND prefix='$prefix' AND type!='".OUTCOME."' AND type!='".INCOME."'";
		elseif($type=='A')
			$query="SELECT * FROM $accountstbl WHERE company like '%".$letters."%' AND prefix='$prefix'";
		else
			$query="SELECT * FROM $accountstbl WHERE company like '%".$letters."%' AND prefix='$prefix' AND type='$type'";
		//print $type;
		$res = mysql_query($query) or die(mysql_error());
		$data=array();
		if($type==OUTCOME){
			while($inf = mysql_fetch_array($res)){
				$data[]=array("label"=>$inf["company"]." (%".$inf["src_tax"].")","value"=>$inf["num"]);
			}
		}else{
			while($inf = mysql_fetch_array($res)){
				$data[]=array("label"=>$inf["company"],"value"=>$inf["num"]);
			}
		}
	}	
	//print json_encode($data);
	print $_REQUEST['jsoncallback'].'('.json_encode($data).')';
}
//print 'bla';
if (isset($_REQUEST['form'])){
	$form=$_REQUEST['form'];
	$smallprint=true;
	if ($form=='voucher'){
		//printHtml();
		include('voucher.php');
		//print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){\$(\".valform\").validate();});</script></html>";
	}elseif ($form=='items'){
		printHtml();
		include('items.php');
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){\$(\".valform\").validate();});</script></html>";
	}elseif ($form=='account'){
		printHtml();
		include('acctadmin.php');
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){\$(\".valform\").validate();});</script></html>";
	}elseif($form=='credit'){
		//printHtml();
		include('credit.php');
	}elseif($form=='journal'){
		printHtml();
		include('journal.php');
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){window.print();});</script></html>";
	}elseif($form=='acctdisp'){
		printHtml();
		include('acctdisp.php');
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){window.print();});</script></html>";
	}elseif($form=='balance'){
		printHtml();
		include('balance.php');
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){window.print();});</script></html>";
	}elseif($form=='profloss'){
		printHtml();
		include('profloss.php');//window.print();
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){window.print();});</script></html>";
	}elseif($form=='mprofloss'){
		printHtml();
		include('mprofloss.php');//window.print();
		print "	</body>	<script type=\"text/javascript\">$(document).ready(function(){window.print();});</script></html>";
	}
}
	

?>