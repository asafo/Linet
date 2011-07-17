<?PHP
/*
 | Define accounts from ASCII text
 | Written for Drorit Accounting system by Ori Idan
 */

function FindConst($constname, $filename = 'linet.inc.php') {
	
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

if($action == 'delaccounts') {
	$query = "DELETE FROM $accountstbl WHERE prefix='$prefix' AND num<200";
	DoQuery($query, __LINE__);
//	print "Accounts deleted<br>\n";
}

$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND num<200";
$result = DoQuery($query, "acctadmin.php");
$num = mysql_num_rows($result);
if(!$num) {
//	print "Adding accounts<br>\n";
	$lines = file('accounts.txt');

	foreach($lines as $line) {
		if($line[0] == '#')
			continue;
//		print "line: $line<br>\n";
		list($type, $num, $name, $id6111, $src_tax) = split(',', $line);
//		print "$type, $num,<br>\n";

		$type = (int)FindConst($type, 'linet.inc.php');
		$num = (int)FindConst($num, 'linet.inc.php');
//		print "$type, $num,<br>\n";
		if($num) {
			$query = "INSERT INTO $accountstbl (num, prefix, type, company, id6111, src_tax) ";
			$query .= "VALUES('$num', '$prefix', '$type', '$name', '$id6111', '$src_tax')";
//			print "Query: $query<br>\n";
			DoQuery($query, __LINE__); 
		}
	}
}
