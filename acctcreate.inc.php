<?PHP
/*
 | Define accounts from ASCII text
 | Written for Drorit Accounting system by Ori Idan
 | Modifed By adam bh 10/2011 
 */

function FindConst($constname, $filename = 'include/core.inc.php') {
	
	$lines = file($filename);
	
	foreach($lines as $line) {
		if(preg_match("/define\(\"$constname\",(.*)\)/", $line, $matcharr))
			break;
	}
	if($matcharr[1])
		return $matcharr[1];
	else
		return $constname;
}

$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND num<200";
$result = DoQuery($query, "acctadmin.php");
$num = mysql_num_rows($result);
if(!$num) {
//	Importing accounts from file";
	$lines = file('accounts.txt');

	foreach($lines as $line) {
		if($line[0] == '#')
			continue;
		$acc=new account;
//		print "line: $line<br>\n";
		list($type, $num, $name, $id6111, $src_tax) = split(',', $line);
//		print "$type, $num,<br>\n";
		
		$type = (int)FindConst($type, 'include/core.inc.php');
		
		$num = (int)FindConst($num, 'include/core.inc.php');
		//print ";$num;$type;<br />";
		$acc->type=$type;
		$acc->num=$num;
		$acc->company=$name;
		$acc->id6111=$id6111;
		$acc->src_tax=$src_tax;
//		print "$type, $num,<br>\n";
		if($num) {
			$acc->newAccount($num);
			//$query = "INSERT INTO $accountstbl (num, prefix, type, company, id6111, src_tax) ";
			//$query .= "VALUES('$num', '$prefix', '$type', '$name', '$id6111', '$src_tax')";
//			print "Query: $query<br>\n";
			//DoQuery($query, __LINE__); 
		}
	}
}
