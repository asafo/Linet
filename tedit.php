<?PHP
/*
 | Edit trnasaction for Freelance accounting.
 | Written by Ori Idan September 2009 
 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
global $TranType;

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

function StoreNo($tnum) {
	global $transactionstbl;
	global $prefix;

	$query = "SELECT * FROM $transactionstbl WHERE num='$tnum' AND prefix='$prefix'";
	$result = DoQuery($query, 'storeno');
	$tnum = 0;
	print "<table dir=\"rtl\"><tr class=tblhead>\n";
	$l = _("Account");
	print "<td style=\"width:10em\">$l</td>\n";
	$l = _("Debit");
	print "<td style=\"width:4em\">$l</td>\n";
	$l = _("Credit");
	print "<td style=\"width:4em\">$l</td>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$account = $line['account'];
		$refnum1 = $line['refnum1'];
		$refnum2 = $line['refnum2'];
		$sum = $line['sum'];
		$details = $line['details'];
		$account_str = GetAccountName($account);
		print "<tr>\n";
		print "<td>$account_str</td>\n";
		$date = FormatDate($line['date'], "mysql", "dmy");
		if($sum < 0) {
			$sum *= -1.0;
			$sumstr = number_format($sum);
			print "<td>&nbsp;</td><td dir=\"ltr\" align=\"right\">$sumstr</td>\n";
		}
		else {
			$sum *= -1.0;
			$sumstr = number_format($sum);
			print "<td>$sumstr</td><td>&nbsp;</td>\n";
		}
		print "</tr>\n";
		$tnum = Transaction($tnum, STORENO, $account, $refnum1, $refnum2, $date, $details, $sum);
	}
	print "</table>\n";
	$l = _("Storeno transaction registered");
	print "<h1>$l</h1>\n";
//	print "<h1>תנועת ביטול נרשמה </h1>\n";
}
$dt = '';
$refnum1 = '';

$tnum = $_GET['num'];
$acct = $_GET['account'];
$begin = $_GET['begin'];
$end = $_GET['end'];
$step = isset($_GET['step']) ? $_GET['step'] : 0; 

if($step == 1) {
	if($_POST['storeno'])
		StoreNo($tnum);
	else {
		$dtdmy = $_POST['date'];
		$dt = FormatDate($dtdmy, "dmy", "mysql");
		$refnum1 = $_POST['refnum1'];
		$details = $_POST['details'];
	
		$query = "UPDATE $transactionstbl SET date='$dt', refnum1='$refnum1', details='$details' ";
		$query .= "WHERE num='$tnum' AND prefix='$prefix'";
		DoQuery($query, 'tedit');
		$l = _("Transaction updated");
		print "<br><h1>$l</h1>\n";
	}
	$url = "?module=acctdisp&amp;account=$acct&amp;begin=$begin&amp;end=$end";
	print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"1; URL=$url\">\n";
}
$query = "SELECT * FROM $transactionstbl WHERE num='$tnum' AND prefix='$prefix'";
$result = DoQuery($query, "tedit");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if($dt == '') {
		$dt = $line['date'];
		$dtdmy = FormatDate($dt, "mysql", "dmy");
	}
	if($refnum1 == '')
		$refnum1 = $line['refnum1'];
	if($details == '')
		$details == $line['details'];
	$type = $line['type'];
	$sum = $line['sum'];
	$account = $line['account'];
	if(($sum > 0) && ($account > 100))
		$cacct = $account;
	else if(($sum < 0) && ($account > 100))
		$dacct = $account;
}

print "<br>\n";
print "<div class=\"righthalf1\">\n";
$l = _("Edit transaction");
print "<h3>$l</h3>\n";
print "<table class=\"formtbl\" width=\"100%\">\n";
print "<form name=\"tedit\" action=\"?module=tedit&amp;num=$tnum&amp;account=$acct&amp;begin=$begin&amp;end=$end&amp;step=1\" method=\"post\">\n";
print "<tr>\n";
$l = _("Transaction number");
print "<td>$l: &nbsp;</td>\n";
print "<td><b>$tnum</b></td>\n";
print "</tr><tr>\n";
$l = _("Tran. type");
print "<td>$l</td>\n";
$tstr = $TranType[$type];
print "<td>$tstr</td>\n";
print "</tr><tr>\n";
$l = _("Debit account");
print "<td>$l: </td>\n";
$acctname = GetAccountName($dacct);
print "<td>$acctname</td>\n";
print "</tr><tr>\n";
$l = _("Credit account");
print "<td>$l: </td>\n";
$acctname = GetAccountName($cacct);
print "<td>$acctname</td>\n";
print "</tr><tr>\n";
$l = _("Date");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"date\" size=\"7\" value=\"$dtdmy\">\n";
?>
<script type="text/javascript">
	new tcal ({
		// form name
		'formname': 'tedit',
		// input name
		'controlname': 'date'
	});
</script>
<?PHP
print "</td>\n";
print "</tr><tr>\n";
$l = _("Ref. num");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"refnum1\" value=\"$refnum1\"></td>\n";
print "</tr><tr>\n";
$l = _("Details");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"details\" value=\"$details\"></td>\n";
print "</tr><tr>\n";
$l = _("Storeno transaction");
print "<td colspan=\"2\"><input type=\"checkbox\" name=\"storeno\">$l</td>\n";
print "</tr><tr>\n";
$l = _("Submit");
print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
print "</tr></table>\n";
print "</form>\n";
print "</div>\n";
print "<div class=\"lefthalf1\">\n";
ShowText('tedit');
print "</div>\n";

?>
