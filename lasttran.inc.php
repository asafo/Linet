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

$l = _("Last outcomes");
$text.= "<h3>$l</h3><br />\n";// class=\"hovertbl\"
$text.= "<table border=\"0\" id=\"outcome\" class=\"tablesorter\"><thead><tr>\n";
$l = _("Num.");
$text.= "<th style=\"width:2.5em\">$l</th>\n";
$l = _("Date");
$text.= "<th style=\"width:6em\">$l</th>\n";
$l = _("Supplier");
$text.= "<th style=\"width:11em\">$l</th>\n";
$l = _("Outcome acc.");
$text.= "<th style=\"width:10em\">$l</th>\n";
$l = _("Ref. num");
$text.= "<th style=\"width:5em\">$l</th>\n";
$l = _("Details");
$text.= "<th style=\"width:15em\">$l</th>\n";
$l = _("Before VAT");
$text.= "<th style=\"width:5em\">$l</th>\n";
$l = _("Sum");
$text.= "<th style=\"width:4em\">$l</th>\n";
$text.= "</tr></thead><tbody>\n";
$t = SUPINV;
$query = "SELECT * FROM $transactionstbl WHERE prefix='$prefix' ";
$query .= "AND type='$t' AND sum>'0' ORDER BY num DESC LIMIT 10";
$result = DoQuery($query, "lasttran");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$supacct == 0;
	$outacct == 0;
	$sum1 = 0;
	$sum2 = 0;
	$num = $line['num'];
	$acct1 = $line['account'];
	$date = FormatDate($line['date'], "mysql", "dmy");
	$sum = $line['sum'];
	$details = $line['details'];
	$refnum1 = $line['refnum1'];
	$refnum2 = $line['refnum2'];
	$acct2 = GetOppositAccount($num, $sum);
	if(GetAcctType($acct1) == SUPPLIER) {
		$supacct = $acct1;
	}
	if(GetAcctType($acct1) == OUTCOME)
		$outacct = $acct1;
	if(GetAcctType($acct2) == SUPPLIER) {
		$supacct = $acct2;
		$sum *= -1.0;
	}
	if(GetAcctType($acct2) == OUTCOME)
		$outacct = $acct2;
	$query = "SELECT sum FROM $transactionstbl WHERE num='$num' AND account='$outacct' AND prefix='$prefix'";
	$r = DoQuery($query, "lasttran");
	$l = mysql_fetch_array($r, MYSQL_NUM);
	$novatsum = number_format(abs($l[0]));
	$supname = GetAccountName($supacct);
	$outname = GetAccountName($outacct);
//	print "Sup: $supname, out: $outname<br>\n";
	if(($supacct == 0) || ($outacct == 0))
		continue;
	$text.='<tr>';
	$text.= "<td>$num</td>\n";
	$text.= "<td>$date</td>\n";
	$url = "?module=acctdisp&amp;account=$supacct&amp;begin=start&amp;end=today";
	$text.= "<td><a href=\"$url\">$supname</a></td>\n";
	$url = "?module=acctdisp&amp;account=$outacct&amp;begin=start&amp;end=today";
	$text.= "<td><a href=\"$url\">$outname</a></td>\n";
	$text.= "<td>$refnum1</td>\n";
	$text.= "<td>$details</td>\n";
	$text.= "<td dir=\"ltr\" align=\"right\">$novatsum</td>\n";
	$tstr = number_format($sum);
	$text.= "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
	$text.= "</tr>\n";
}
$text.= "</tbody></table>\n";

?>

