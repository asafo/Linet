<?PHP
/*
 | Get last rows from transactions tbl
 */
global $transactionstbl;

function GetAcctType($acct) {
	global $prefix, $accountstbl;

	$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctType");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetOppositAccount($num, $sum) {
	global $transactionstbl, $prefix;

	$query = "SELECT account,sum FROM $transactionstbl WHERE num='$num' AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	if($sum < 0.0)
		$neg = 1;
	else
		$neg = 0;
	$maxsum = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$account = $line['account'];
		$tsum = $line['sum'];
		if($neg == 1) {	/* we are looking for positive sums */
			if($tsum > 0.0) {
				if($tsum > $maxsum) {
					$maxsum = $tsum;
					$retacct = $account;
				}
			}
		}
		else {		/* we are looking for negative sums */
			if($tsum < 0.0) {
				if($tsum < $maxsum) {
					$maxsum = $tsum;
					$retacct = $account;
				}
			}
		}
			
	}
	return $retacct;
}

print "<div class=\"caption_out\" style=\"margin-top:5px\"><div class=\"caption\">";
print "<b>הכנסות אחרונות</b>\n";
print "</div></div><br>\n";
print "<table border=\"0\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
print "<td style=\"width:2.5em\">מספר</td>\n";
print "<td style=\"width:6em\">תאריך</td>\n";
print "<td style=\"width:10em\">לקוח</td>\n";
print "<td style=\"width:10em\">חשבון הכנסות</td>\n";
print "<td style=\"width:5em\">אסמכתא</td>\n";
print "<td style=\"width:5em\">פרטים</td>\n";
print "<td style=\"width:5em\">לפני מע\"מ</td>\n";
print "<td style=\"width:4em\">סכום</td>\n";
print "</tr>\n";
$t = MANINVOICE;
$query = "SELECT * FROM $transactionstbl WHERE prefix='$prefix' ";
$query .= "AND type='$t' AND sum<'0' ORDER BY num DESC LIMIT 10";
$result = DoQuery($query, "tranrep.php");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$custacct == 0;
	$inacct == 0;
	$num = $line['num'];
	$acct1 = $line['account'];
	$date = FormatDate($line['date'], "mysql", "dmy");
	$sum = $line['sum'];
	$details = $line['details'];
	$refnum1 = $line['refnum1'];
	$refnum2 = $line['refnum2'];
	$acct2 = GetOppositAccount($num, $sum);
	
	if(GetAcctType($acct1) == CUSTOMER) {
		$custacct = $acct1;
		$sum *= -1.0;
	}
	if(GetAcctType($acct1) == INCOME)
		$inacct = $acct1;
	if(GetAcctType($acct2) == CUSTOMER) {
		$custacct = $acct2;
	}
	if(GetAcctType($acct2) == INCOME)
		$inacct = $acct2;
	$query = "SELECT sum FROM $transactionstbl WHERE num='$num' AND account='$inacct'";
	$r = DoQuery($query, "lastincome");
	$l = mysql_fetch_array($r, MYSQL_NUM);
	$novatsum = number_format(abs($l[0]));

	$custname = GetAccountName($custacct);
	$inname = GetAccountName($inacct);
	if(($custacct == 0) || ($inacct == 0))
		continue;
	NewRow();
	print "<td>$num</td>\n";
	print "<td>$date</td>\n";
	$url = "?module=acctdisp&account=$custacct&begin=start&end=today";
	print "<td><a href=\"$url\">$custname</a></td>\n";
	$url = "?module=acctdisp&account=$inacct&begin=start&end=today";
	print "<td><a href=\"$url\">$inname</a></td>\n";
	print "<td>$refnum1</td>\n";
	print "<td>$details</td>\n";
	print "<td>$novatsum</td>\n";
	$sum = number_format($sum);
	print "<td>$sum</td>\n";
	print "</tr>\n";
}
print "</table>\n";

?>

