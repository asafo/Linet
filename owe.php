׳�ֲ»ֲ¿<?PHP
//M:׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”׳³ג€¢׳³ֳ— ׳³ג€”׳³ג„¢׳³ג„¢׳³ג€˜׳³ג„¢׳³ן¿½
/*
 | Customers owing money report for Drorit accounting system
 | Written by Ori Idan 2009
 */
global $prefix, $accountstbl, $supdocstbl;
global $dir;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetAcctTotal($account, $dt) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<='$dt' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

print "<br>\n";
print "<div class=\"form righthalf1\">\n";
$l = _("Customers owing money");
print "<h3>$l</h3>";
$t = CUSTOMER;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "owe.php");
print "<table border=\"0\" dir=\"$dir\" class=\"hovertbl\" width=\"100%\"><tr class=\"tblhead\">\n";
$l = _("Customer");
print "<td style=\"width:20em\">$l &nbsp;&nbsp;</td>\n";			/* customer account */
$l = _("Acc. balance");
print "<td>$l</td>\n";
print "</tr>\n";

$e = 0;
$total = 0;
$dt = date("Y-m-d");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$sum = GetAcctTotal($num, $dt);
	if($sum == 0.0)
		continue;
	$sum *= -1.0;
	$total += $sum;
	NewRow();
	$url = "?module=acctdisp&amp;account=$num&amp;end=today";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	$tstr = number_format($sum);
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";	
}
$tstr = number_format($total);
print "<tr class=\"sumline\"><td><b>׳³ֲ¡׳³ג€�\"׳³ג€÷</b></td><td>$tstr</td></tr>\n";
print "</table>\n";
print "</div>\n";

?>
