<?

if(isset($_GET['term']))
	$letters = $_GET['term'];
else 
	$letters='*';
	
if(isset($_GET['type']))
	$type = $_GET['type'];
else 
	$type=0;

if ($letters=='*') $letters='';

if (isset($_GET['data'])){
	$data=$_GET['data'];
	if ($data=='items'){
		$res = mysql_query("SELECT * FROM $itemstbl WHERE name like '%".$letters."%' AND prefix='$prefix'") or die(mysql_error());
		while($inf = mysql_fetch_array($res)){
			if ($str<>'')$str.=',';
				$str.='{"label":"'.$inf["name"].'", "value":'.$inf["num"].'}';
		}
	}
	if ($data=='acc'){
		$res = mysql_query("SELECT * FROM $accountstbl WHERE company like '%".$letters."%' AND prefix='$prefix' AND type='$type'") or die(mysql_error());
		while($inf = mysql_fetch_array($res)){
			if ($str<>'')$str.=',';
				$str.='{"label":"'.$inf["company"].'", "value":'.$inf["num"].'}';
		}
	}
	print '['.$str.']';
}
?>
