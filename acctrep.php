<?PHP
/*
 | Account report module for Freelance accounting system.
 | Written by Ori Idan July 2009
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl, $transactionstbl;
global $EvenLine;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}
$opt = isset($_GET['option']) ? $_GET['option'] : '';

function GetAcctNumTran($account, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "GetAcctTotal");
	return mysql_num_rows($result);
}

function GetAcctTotal($account, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];	
	}
	return $total;
}


$begindmy = isset($_GET['begin']) ? $_GET['begin'] : date("1-1-Y");
$enddmy = isset($_GET['end']) ? $_GET['end'] : date("d-m-Y");
print "<table dir=\"rtl\" border=\"0\"><tr><td colspan=\"3\">\n";
print "<div class=\"caption_out\" style=\"margin-bottom:5px\"><div class=\"caption\">";
print "<b>כרטיסי חשבונות</b>\n";
print "</div></div>\n";
print "<form name=\"acctrep\" method=\"get\">\n";
print "<input type=\"hidden\" name=\"module\" value=\"acctrep\">\n";
if($opt)
	print "<input type=\"hidden\" name=\"option\" value=\"$opt\">\n";
print "<table border=\"0\"><tr>\n";
print "<td>בחר תאריך תחילה: </td>\n";
print "<td><input type=\"text\" name=\"begin\" value=\"$begindmy\" size=\"7\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'acctrep',
		// input name
		'controlname': 'begin'
	});
</script>
<?PHP
print "&nbsp;&nbsp;</td>\n";
print "<td>בחר תאריך סיום: </td>\n";
print "<td><input type=\"text\" name=\"end\" value=\"$enddmy\" size=\"7\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'acctrep',
		// input name
		'controlname': 'end'
	});
</script>
<?PHP
print "&nbsp;&nbsp;</td><td><input type=\"submit\" value=\"בצע\"></td></tr>\n";
print "</table></form>\n";
print "</td></tr>\n";

print "<tr><td valign=\"top\">\n";
print "<h2>הכנסות</h2>\n";
$t = INCOME;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
print "<table border=\"0\" cellpadding=\"5px\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
print "<td>כרטיס &nbsp;&nbsp;</td>\n";
print "<td>יתרה &nbsp;&nbsp;</td>\n";
print "</tr>\n";
$EvenLine = 0;
$end = FormatDate($enddmy, "dmy", "mysql");
$today = date("Y-m-d");
$total = 0.0;
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$t = GetAcctTotal($num, $begin, $end);
	$r = GetAcctNumTran($num, $begin, $end);
	if($r == 0)
		continue;
	$total += $t;
	$tstr = number_format($t);
	NewRow();
	$url = "?module=acctdisp&account=$num&begin=$begindmy&end=$enddmy";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";
}
$tstr = number_format($total);
print "<tr class=\"sumline\"><td><b>סה\"כ: </b></td><td>$tstr</td></tr>\n";
print "</table>\n";
print "</td>\n";	/* End of first column */
print "<td>&nbsp;&nbsp;</td>\n";
print "<td valign=\"top\">\n";
print "<h2>הוצאות ורכוש קבוע</h2>\n";
$t = OUTCOME;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
print "<table border=\"0\" cellpadding=\"5px\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
print "<td>כרטיס &nbsp;&nbsp;</td>\n";
print "<td>יתרה &nbsp;&nbsp;</td>\n";
print "</tr>\n";
$EvenLine = 0;
$end = FormatDate($enddmy, "dmy", "mysql");
$total = 0.0;
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$t = GetAcctTotal($num, $begin, $end);
	$r = GetAcctNumTran($num, $begin, $end);
	if($r == 0)
		continue;
	$tstr = number_format($t);
	$total += $t;
	NewRow();
	$url = "?module=acctdisp&account=$num&begin=$begindmy&end=$enddmy";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";
}
$t = ASSETS;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$t = GetAcctTotal($num, $begin, $end);
	$r = GetAcctNumTran($num, $begin, $end);
	if($r == 0)
		continue;
	$tstr = number_format($t);
	$total += $t;
	NewRow();
	$url = "?module=acctdisp&account=$num&begin=$begindmy&end=$enddmy";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";
}
$tstr = number_format($total);
print "<tr class=\"sumline\"><td><b>סה\"כ: </b></td><td dir=\"ltr\">$tstr</td></tr>\n";
print "</table>\n";
print "</td>\n";	/* End of second column */
print "<td>&nbsp;&nbsp;</td>\n";
/* Third column */
print "<td valign=\"top\">\n";
print "<h2>לקוחות</h2>\n";
$t = CUSTOMER;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
print "<table border=\"0\" cellpadding=\"5px\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
print "<td>כרטיס &nbsp;&nbsp;</td>\n";
print "<td>יתרה &nbsp;&nbsp;</td>\n";
print "</tr>\n";
$EvenLine = 0;
$end = FormatDate($enddmy, "dmy", "mysql");
$total = 0.0;
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$t = GetAcctTotal($num, $begin, $end);
	$r = GetAcctNumTran($num, $begin, $end);
	if($r == 0)
		continue;
	$tstr = number_format($t);
	$total += $t;
	NewRow();
	$url = "?module=acctdisp&account=$num&begin=$begindmy&end=$enddmy";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";
}
$tstr = number_format($total);
print "<tr class=\"sumline\"><td><b>סה\"כ: </b></td><td dir=\"ltr\">$tstr</td></tr>\n";
print "</table>\n";
print "</td>\n";	/* End of third column */
print "<td>&nbsp;&nbsp;</td>\n";
/* Fourth column */
print "<td valign=\"top\">\n";
print "<h2>ספקים</h2>\n";
$t = SUPPLIER;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
print "<table border=\"0\" cellpadding=\"5px\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
print "<td>כרטיס &nbsp;&nbsp;</td>\n";
print "<td>יתרה &nbsp;&nbsp;</td>\n";
print "</tr>\n";
$EvenLine = 0;
$end = FormatDate($enddmy, "dmy", "mysql");
$total = 0.0;
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$t = GetAcctTotal($num, $begin, $end);
	$r = GetAcctNumTran($num, $begin, $end);
	if($r == 0)
		continue;
	$total += $t;
	$tstr = number_format($t);
	NewRow();
	$url = "?module=acctdisp&account=$num&begin=$begindmy&end=$enddmy";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";
}
$tstr = number_format($total);
print "<tr class=\"sumline\"><td><b>סה\"כ: </b></td><td dir=\"ltr\">$tstr</td></tr>\n";
print "</table>\n";
print "</td>\n";	/* End of fourth column */
print "<td>&nbsp;&nbsp;</td>\n";
/* Fifth column */
print "<td valign=\"top\">\n";
print "<h2>מע\"מ</h2>\n";
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' ORDER By num LIMIT 3";
$result = DoQuery($query, "payment.php");
print "<table border=\"0\" cellpadding=\"5px\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
print "<td>כרטיס &nbsp;&nbsp;</td>\n";
print "<td>יתרה &nbsp;&nbsp;</td>\n";
print "</tr>\n";
$EvenLine = 0;
$end = FormatDate($enddmy, "dmy", "mysql");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$total = GetAcctTotal($num, $begin, $end);
//	if($total < 0)
//		$total *= -1.0;
	$tstr = number_format($total);
	NewRow();
	$url = "?module=acctdisp&account=$num&begin=$begindmy&end=$enddmy";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td dir=\"ltr\">$tstr</td>\n";
	print "</tr>\n";
}
print "</table>\n";
print "</td>\n";	/* End of fourth column */

print "</tr></table>\n";

?>

