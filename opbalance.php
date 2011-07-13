<?PHP
/*
 | Opening balance module for Drorit
 | 
 */
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

global $accountstbl, $transactionstbl;
global $TranType;
global $dir;

function PrintYearSelect() {
	
	$year = date("Y");
	$max = $year + 1;
	
	print "<select name=\"year\">\n";
	for($min = $year - 2; $min <= $max; $min++) {
		print "<option value=\"$min\" ";
		if($min == $year)
			print "selected";
		print ">$min</option>\n";
	}
	print "</select>\n";
}

function PrintAccountSelect() {
	global $accountstbl, $prefix;
	
	$types = array(CUSTOMER, SUPPLIER, BANKS, AUTHORITIES, OBLIGATIONS,
			CAPITAL, CASH, FINANCING, ASSETS);

	
	print "<select class=\"account\" name=\"account[]\">\n";
	$l = _("Select account");
	print "<option value=\"0\">-- $l --</option>\n";
	foreach($types as $type) {
		$query = "SELECT num,company FROM $accountstbl WHERE type='$type' AND prefix='$prefix' ORDER BY company ASC";
		$result = DoQuery($query, __LINE__);
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$num = $line['num'];
			$name = stripslashes($line['company']);
			print "<option value=\"$num\">$name</option>\n";
		}
	}
	print "</select>\n";
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;


print "<br><div class=\"form righthalf1\">\n";
$t = _("Openning balances");
print "<h3>$t</h3>\n";

if($step == 1) {
	$year = $_POST['year'];
	$date = "1-1-$year";
	$acctarr = $_POST['account'];
	$balarr = $_POST['bal'];
	foreach($acctarr as $i => $acct) {
		$sum = $balarr[$i];
		if($acct) {
	//		print "$acct, $sum<br>\n";
			$tnum = Transaction(0, OPBALANCE, $acct, '', '', $date, '', $sum);
			$sum *= -1.0;
			Transaction($tnum, OPBALANCE, OPENBALANCE, '', '', $date, '', $sum);
		}
	}
	$l = _("Openning balances updated");
	print "<h2>$l</h2>\n";
}

print "<form action=\"?module=opbalance&amp;step=1\" method=\"post\">\n";
print "<table class=\"formtbl\" width=\"100%\"><tr>\n";
print "<td colspan=\"2\">\n";
$l = _("Select year");
print "$l: \n";
PrintYearSelect();
print "</td></tr>\n";
print "<tr class=\"tblhead\">\n";
$l = _("Account");
print "<td>$l</td>\n";
$l = _("Acct. balance");
print "<td>$l</td>\n";
print "</tr>\n";
for($i = 0; $i < 10; $i++) {
	print "<tr>\n";
	print "<td>\n";
	PrintAccountSelect();
	print "</td><td>\n";
	print "<input type=\"text\" class=\"bal\" name=\"bal[]\" dir=\"ltr\" >\n";
	print "</td>\n";
	print "</tr>\n";
}
$l = _("Update");
print "<tr><td colspan=\"2\" align=\"center\">\n";
print "<input type=\"submit\" value=\"$l\"></td></tr>\n";
print "</table>\n";
print "</form>\n";
print "</div>\n";
?>
