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

$text.= "<div class=\"caption_out\" style=\"margin-top:5px\"><div class=\"caption\">";
$text.= "<b>הכנסות אחרונות</b>\n";
$text.= "</div></div><br>\n";
$text.= "<table border=\"0\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
$text.= "<td style=\"width:2.5em\">מספר</td>\n";
$text.= "<td style=\"width:6em\">תאריך</td>\n";
$text.= "<td style=\"width:10em\">לקוח</td>\n";
$text.= "<td style=\"width:10em\">חשבון הכנסות</td>\n";
$text.= "<td style=\"width:5em\">אסמכתא</td>\n";
$text.= "<td style=\"width:5em\">פרטים</td>\n";
$text.= "<td style=\"width:5em\">לפני מע\"מ</td>\n";
$text.= "<td style=\"width:4em\">סכום</td>\n";
$text.= "</tr>\n";
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
	$text.='<tr>';
	$text.= "<td>$num</td>\n";
	$text.= "<td>$date</td>\n";
	$url = "?module=acctdisp&account=$custacct&begin=start&end=today";
	$text.= "<td><a href=\"$url\">$custname</a></td>\n";
	$url = "?module=acctdisp&account=$inacct&begin=start&end=today";
	$text.= "<td><a href=\"$url\">$inname</a></td>\n";
	$text.= "<td>$refnum1</td>\n";
	$text.= "<td>$details</td>\n";
	$text.= "<td>$novatsum</td>\n";
	$sum = number_format($sum);
	$text.= "<td>$sum</td>\n";
	$text.= "</tr>\n";
}
$text.= "</table>\n";

?>