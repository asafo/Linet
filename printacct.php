<?PHP
//M:הדפסת כרטסת
/*
 | Display transactions for all accounts
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('drorit.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");

	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = "הדפסת כרטסת";
	include('printhead.inc.php');
	print $header;
}
else {
	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}

global $transactionstbl, $accountstbl;
global $TranType, $AcctType;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}

function GetAcctTotal($account, $dt) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<'$dt' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetTotalForPeriod($account, $begindate, $enddate) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date>'$begindate' AND date<='$enddate' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetNumTransactions($account, $begindate, $enddate) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date>'$begindate' AND date<='$enddate' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$n = mysql_num_rows($result);
	return $n;
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
			if(($tsum > 0) && (abs($tsum) == abs($sum)))
				return $account;
			if($tsum > 0.0) {
				if($tsum > $maxsum) {
					$maxsum = $tsum;
					$retacct = $account;
				}
			}
		}
		else {		/* we are looking for negative sums */
			if(($tsum < 0) && (abs($tsum) == $sum))
				return $account;
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

function GetAccountName($account) {
	global $accountstbl, $prefix;
	
	$query = "SELECT company FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br />\n";
		echo mysql_error();
		exit;
	}
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$name = $line['company'];
	return $name;
}

function GetAccountType($account) {
	global $accountstbl, $prefix;
	
	$query = "SELECT type FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br />\n";
		echo mysql_error();
		exit;
	}
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$type = $line['type'];
	return $type;
}

$begin = isset($_GET['begin']) ? $_GET['begin'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$filerep = isset($_GET['file']) ? $_GET['file'] : 0;
$begindate = FormatDate($begin, "dmy", "mysql");
$enddate = FormatDate($end, "dmy", "mysql");

if($begin == '') {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	print "<br>\n";
	print "<div class=\"righthalf1\">\n";
	print "<h3>$reptitle</h3>\n";
	print "<form name=\"dtrange\" action=\"\" method=\"get\">\n";
	print "<input type=\"hidden\" name=\"module\" value=\"printacct\">\n";
	print "<table dir=\"rtl\" cellpadding=\"20px\" cellspacing=\"20px\" class=\"formtbl\" width=\"100%\" style=\"height:40px\"><tr>\n";
	$l = _("From date");
	print "<td valign=\"middle\">$l: </td>\n";
	print "<td valign=\"middle\"><input type=\"text\" name=\"begin\" value=\"$bdate\" size=\"7\">\n";
?>
<script type="text/javascript">
	new tcal ({
		// form name
		'formname': 'dtrange',
		// input name
		'controlname': 'begin'
	});
</script>
<?PHP
	print "</td>\n";
	$l = _("To date");
	print "<td valign=\"middle\">$l: </td>\n";
	print "<td valign=\"middle\"><input type=\"text\" name=\"end\" value=\"$edate\" size=\"7\">\n";
?>
<script type="text/javascript">
	new tcal ({
		// form name
		'formname': 'dtrange',
		// input name
		'controlname': 'end'
	});
</script>
<?PHP
	print "</td>\n";
	$l = _("Execute");
	print "<td><input type=\"submit\" value=\"$l\"></td>\n";
	print "</tr></table>\n";
	print "</form>\n";
	print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	ShowText('printacct');
	print "</div>\n";
	return;
}

if(isset($module)) {
	$url = "printacct.php?account=$acct&begin=$begin&end=$end";
	$url .= "&prefix=$prefix";
	print "<div class=\"repbottom\">\n";
	$l = _("Print");
	print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
//	print "&nbsp;&nbsp;";
//	print "<input type=\"button\" value=\"יצוא לקובץ\" onclick=\"window.location.href='?module=acctdisp&account=$acct&begin=$begin&end=$end&file=1'\">\n";
	print "</div>\n";
}

$l1 = _("Print accounts from");
$l2 = _("To");
print "<h1>$l1: $begin $l2: $end</h1>\n";

// $accttypes = array(INCOME, OUTCOME, ASSETS, SUPPLIER, CUSTOMER, AUTHORITIES, OBLIGATIONS, );
for($accttype = 0; $accttype <= 12; $accttype++) {
	$typename = $AcctType[$accttype];
	$l = _("Accounts of");
	print "<br><h1>$l: $typename</h1><br>\n";
//	print "<br><h1>כרטסת: $typename</h1><br>\n";

	$query = "SELECT num,company FROM $accountstbl WHERE type='$accttype' AND prefix='$prefix'";
	$r = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$acct = $line['num'];
		$company = $line['company'];

		if(($accttype == SUPPLIER) || ($accttype == CUSTOMER) || ($accttype == AUTHORITIES))
			$total = GetAcctTotal($acct, $enddate);
		else
			$total = GetTotalForPeriod($acct, $begindate, $enddate);
		$total = round($total, 2);
		$n = GetNumTransactions($acct, $begindate, $enddate);
		if(($total == 0) && ($n == 0))
			continue;
		
		$l = _("Transactions for account");
		print "<br><h2>$l: $company</h2>\n";
//		print "<br><h2>תנועות לכרטיס: $company</h2>\n";

		if($filerep) {
			$filename = "tmp/acct$acct.csv";
			$fd = fopen($filename, 'w');
			$l = _("Transaction");
			fwrite($fd, "\"$l\",");
			$l = _("Type");
			fwrite($fd, "\"$l\",");
			$l = _("Date");
			fwrite($fd, "\"$l\",");
			$l = _("Ref. num");
			fwrite($fd, "\"$l\",");
			$l = _("Details");
			fwrite($fd, "\"$l\",");
			$l = _("Opp. account");
			fwrite($fd, "\"$l\",");
			$l = _("Debit");
			fwrite($fd, "\"$l\",");
			$l = _("Credit");
			fwrite($fd, "\"$l\",");
			$l = _("Acct. balance");
			fwrite($fd, "\"$l\"\n");
		}
		else {
			print "<table dir=\"rtl\" border=\"0\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
			$l = _("Transaction");
			print "<td>$l&nbsp;</td>\n";
			$l = _("Type");
			print "<td style=\"width:7em\">$l&nbsp;</td>\n";
			$l = _("Date");
			print "<td style=\"width:6em\">$l</td>\n";
			$l = _("Ref. num");
			print "<td style=\"width:5em\">$l&nbsp;</td>\n";
			$l = _("Details");
			print "<td style=\"width:10em\">$l&nbsp;</td>\n";
			$l = _("Opp. account");
			print "<td style=\"width:10em\">$l&nbsp;</td>\n";
			$l = _("Debit");
			print "<td style=\"width:5em\">$l&nbsp;</td>\n";
			$l = _("Credit");
			print "<td style=\"width:5em\">$l&nbsp;</td>\n";
			$l = _("Acct. balance");
			print "<td style=\"width:5em\">$l&nbsp;</td>\n";
	/*		$l = _("Operations");
			print "<td>$l</td>\n"; */
			print "</tr>\n";
		}

		if(($accttype == SUPPLIER) || ($accttype == CUSTOMER) || ($accttype == AUTHORITIES))
			$sub_total = round(GetAcctTotal($acct, $begindate), 0);
		else
			$sub_total = 0.0;
		$debit_total = 0.0;
		$credit_total = 0.0;
		if($sub_total != 0.0) {
			print "<tr>\n";
			if($filerep) {
				$l = _("Openning balance");
				fwrite($fd, "\"$l\",");
			}
			else {
				$l = _("Openning balance");
				print "<td colspan=\"6\">$l</td>\n";
			}
			if($sub_total < 0.0) {
				$t = $sub_total * -1.0;
				$debit_total += $t;
				if($filerep)
					fwrite($fd, "$t, ,$sub_total\n");
				else {
					$tstr = number_format($t);
					print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
					print "<td>&nbsp;</td>\n";
					$tstr = number_format($sub_total);
					print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
				}
			}
			else {
				$credit_total += $sub_total;
				if($filerep)
					fwrite($fd, " ,$sub_total,$sub_total\n");
				else {
					print "<td>&nbsp;</td>\n";
					$tstr = number_format($sub_total);
					print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
					print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
				}
			}
			$e = 1;
		}
		else
			$e = 0;
		$query = "SELECT * FROM $transactionstbl WHERE account='$acct' ";
		if($begin != '')
			$query .= "AND date>='$begindate' ";
		$query .= "AND date<='$enddate' AND prefix='$prefix' ORDER BY date";
		$result = DoQuery($query, "acctdisp.php");
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$num = $line['num'];
			$date = FormatDate($line['date'], "mysql", "dmy");
			$type = $line['type'];
			$type_str = stripslashes($TranType[$type]);
			$refnum1 = substr($line['refnum1'], -6);
			$details = $line['details'];
			$sum = $line['sum'];
			$opp_account = GetOppositAccount($num, $sum);
			$acc_name = stripslashes(GetAccountName($opp_account));
			$sub_total += $sum;
			if($filerep) {
				fwrite($fd, "$num,$type_str,$date,$refnum1,$details,$acc_name,");
				if($sum < 0) {
					$sum = $sum * -1.0;
					fwrite($fd, "$sum, ");
					$debit_total += $sum;
				}
				else {
					$credit_total += $sum;
					fwrite($fd, " ,$sum,");
				}
				fwrite($fd, "$sub_total\n");
			}
			else {
				NewRow();
				print "<td>$num</td>\n";
				if($type == INVOICE) {
					$dt = DOC_INVOICE;
					if(isset($module)) {
						$url = "printdoc.php?doctype=$dt&docnum=$refnum1&prefix=$prefix";
						print "<td><a href=\"$url\" target=\"docswin\">$type_str</a></td>\n";
					}
					else
						print "<td>$type_str</td>\n";
				}
				else if($type == RECEIPT) {
					$dt = DOC_RECEIPT;
					if(isset($module)) {
						$url = "printdoc.php?doctype=$dt&docnum=$refnum1&prefix=$prefix";
						print "<td><a href=\"$url\" target=\"docswin\">$type_str</a></td>\n";
					}
					else
						print "<td>$type_str</td>\n";
				}
				else if($type == CREDIT_INVOICE) {
					$dt = DOC_CREDIT;
					if(isset($module)) {
						$url = "printdoc.php?doctype=$dt&docnum=$refnum1&prefix=$prefix";
						print "<td><a href=\"$url\" target=\"docswin\">$type_str</a></td>\n";
					}
					else
						print "<td>$type_str</td>\n";
				}
				else
					print "<td>$type_str</td>\n";
				print "<td>$date</td>\n";
				print "<td>$refnum1</td>\n";
				print "<td>$details</td>\n";
				if(isset($module)) {
					$url = "?module=acctdisp&account=$opp_account&begin=$bedin&end=$end";
					print "<td><a href=\"$url\">$acc_name</a></td>\n";
				}
				else
					print "<td>$acc_name</td>\n";
				if($sum < 0) {
					$sum = $sum * -1.0;
					$debit_total += $sum;
					$tstr = number_format($sum);
					print "<td>$tstr</td><td>&nbsp;</td>\n";
				}
				else {
					$credit_total += $sum;
					$tstr = number_format($sum);
					print "<td>&nbsp;</td><td>$tstr</td>\n";
				}
				$tstr = number_format($sub_total);
				print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
				print "</tr>\n";
			}
		}
				if(!isset($module))
			print "<tr class=\"sumlineprt\">\n";
		else
			print "<tr class=\"sumline\">\n";
		print "<td colspan=\"6\" align=\"left\">סה\"כ: &nbsp;</td>\n";
		$tstr = number_format($debit_total);
		print "<td>$tstr</td>";
		$tstr = number_format($credit_total);
		print "<td>$tstr</td>";
		$tstr = number_format($sub_total);
		print "<td dir=\"ltr\" align=\"right\">$tstr</td>";
		print "</tr>\n";
		print "</table>\n";
	}
}
if($filerep) {
	fclose($fd);
//	Conv1255($filename);
	$l = _("To download report click here");
	print "<h2>$l: ";
	$url = "/download.php?file=$filename&name=acct$acct.csv";
	print "<a href=\"$filename\">acct$acct.csv</a></h2>\n";
	$l = _("Right click file name and choose Save as...");
	print "<h2>$l</h2>\n";
	print "<script type=\"text/javascript\">\n";
	print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
	print "</script>\n";
}
else {
	if(isset($module)) {
		$url = "printacct.php?account=$acct&begin=$begin&end=$end";
		$url .= "&prefix=$prefix";
		print "<div class=\"repbottom\">\n";
		$l = _("Print");
		print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
	//	print "&nbsp;&nbsp;";
	//	print "<input type=\"button\" value=\"יצוא לקובץ\" onclick=\"window.location.href='?module=acctdisp&account=$acct&begin=$begin&end=$end&file=1'\">\n";
		print "</div>\n";
	}
	else
		print "</body></html>\n";
}

?>

