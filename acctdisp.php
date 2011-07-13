<?PHP
//M:הצגת תנועות לכרטיס
/*
 | Display transactions for card
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = _("Display transactions for account");
//	$reptitle = "הצגת תנועות לכרטיס";
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
global $TranType;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

function GetOppBalance($account, $dt) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<'$dt' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
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

$acct = $_GET['account'];
$accttype = GetAccountType($acct);
$company = GetAccountName($acct);
$begin = isset($_GET['begin']) ? $_GET['begin'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$filerep = isset($_GET['file']) ? $_GET['file'] : 0;

if($end != '') {
	if($begin == 'start') {
		$d = date("m-Y");
		list($m, $y) = explode('-', $d);
		$begin = "1-1-$y";
	}
	if($end == 'today')
		$end = date("d-m-Y");
	$begindate = FormatDate($begin, "dmy", "mysql");
	$enddate = FormatDate($end, "dmy", "mysql");

	if(!isset($module)) {
		$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "GetAccountName");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$str = $line[0];
		print "<h1>$str</h1>\n";	
	}

	if($filerep) {
		$l = _("Transactions report for account");
		print "<br><h1>$l: $company</h1>\n<br>\n";
	}
	else {
		$l = _("Display transactions for account");
		print "<br><h1>$l: $company</h1>\n<br>\n";
	}
	if($begin != '') {
		$l = _("From date");
		print "<h2>$l: $begin ";
		$l = _("To date");
		print "$l: $end</h2>\n";
	}
	
//	print "<div class=\"innercontent\">\n";
	if($filerep) {
		$filename = "tmp/acct$acct.csv";
		$fd = fopen($filename, 'w');
		$l = _("Transaction");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Type");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Date");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Ref. num");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Details");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Opp. account");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Debit");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Credit");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Acct. balance");
		$l = iconv("UTF-8", "windows-1255", $l );
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
		$l = _("Operations");
		print "<td>$l</td>\n";
		print "</tr>\n";
	}
/*	$openonly = isset($_GET['openonly']) ? $_GET['openonly'] : 0; // הצג תנועות לא מתואמות בלבד 

	if($openonly)
		print "<H2 align=center dir=RTL>תנועות לא מתואמות בלבד</H2>\n<BR>\n";
*/

	if(($accttype != INCOME) && ($accttype != OUTCOME))
		$sub_total = round(GetOppBalance($acct, $begindate), 0);
	$debit_total = 0.0;
	$credit_total = 0.0;
	if($sub_total != 0.0) {
		print "<tr>\n";
		if($filerep) {
			$l = _("Openning balance");
			$l = iconv("UTF-8", "windows-1255", $l );
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
				fwrite($fd, iconv("UTF-8", "windows-1255", "$t, ,$sub_total\n" ));
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
				fwrite($fd, iconv("UTF-8", "windows-1255", " ,$sub_total,$sub_total\n"));
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
	$query .= "AND date<='$enddate' AND prefix='$prefix' ORDER BY date,num ";
	$result = DoQuery($query, "acctdisp.php");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	/*	$cor = $line['cor_num'];
		if($openonly && $cor)
			continue; */
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
			fwrite($fd, iconv("UTF-8", "windows-1255", "$num,$type_str,$date,$refnum1,$details,$acc_name,"));
			if($sum < 0) {
				$sum = $sum * -1.0;
				fwrite($fd, iconv("UTF-8", "windows-1255", "$sum, "));
				$debit_total += $sum;
			}
			else {
				$credit_total += $sum;
				fwrite($fd, iconv("UTF-8", "windows-1255", " ,$sum,"));
			}
			fwrite($fd, iconv("UTF-8", "windows-1255", "$sub_total\n"));
		}
		else {
			if($e) {
				print "<tr class=\"otherline\">\n";
				$e = 0;
			}
			else {
				print "<tr>\n";
				$e = 1;
			}
			print "<td>$num</td>\n";
			if($type == INVOICE) {
				$dt = DOC_INVOICE;
				if(isset($module)) {
					$url = "printdoc.php?doctype=$dt&amp;docnum=$refnum1&amp;prefix=$prefix";
					print "<td><a href=\"$url\" target=\"docswin\">$type_str</a></td>\n";
				}
				else
					print "<td>$type_str</td>\n";
			}
			else if($type == RECEIPT) {
				$dt = DOC_RECEIPT;
				if(isset($module)) {
					$url = "printdoc.php?doctype=$dt&amp;docnum=$refnum1&amp;prefix=$prefix";
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
				$url = "?module=acctdisp&amp;account=$opp_account&amp;begin=$bedin&amp;end=$end";
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
			if(isset($module)) {
				$l = _("Edit/Storeno");
				print "<td><a href=\"?module=tedit&amp;num=$num&amp;account=$acct&amp;begin=$begin&amp;end=$end\">$l</a></td>\n";
			}
			print "</tr>\n";
		}	
	}
	if($filerep) {
		fclose($fd);
		$l = _("Click here to download report");
		print "<h2>$l: ";
		$url = "/download.php?file=$filename&amp;name=acct$acct.csv";
		print "<a href=\"$filename\">acct$acct.csv</a></h2>\n";
		$l = _("Right click and choose 'save as...'");
		print "<h2>$l</h2>\n";
		print "<script type=\"text/javascript\">\n";
		print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		print "</script>\n";
	}
	else {
		if(!isset($module))
			print "<tr class=\"sumlineprt\">\n";
		else
			print "<tr class=\"sumline\">\n";
		$l = _("Total");
		print "<td colspan=\"6\" align=\"left\">$l: &nbsp;</td>\n";
		$tstr = number_format($debit_total);
		print "<td>$tstr</td>";
		$tstr = number_format($credit_total);
		print "<td>$tstr</td>";
		$tstr = number_format($sub_total);
		print "<td dir=\"ltr\" align=\"right\">$tstr</td>";
		print "</tr>\n";
		print "</table>\n";
//	print "</div>\n";
		if(isset($module)) {
			$url = "acctdisp.php?account=$acct&amp;begin=$begin&amp;end=$end";
			$url .= "&prefix=$prefix";
			print "<div class=\"repbottom\">\n";
			$l = _("Print");
			print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
			print "&nbsp;&nbsp;";
			print "<input type=\"button\" value=\"יצוא לקובץ\" onclick=\"window.location.href='?module=acctdisp&account=$acct&begin=$begin&end=$end&file=1'\">\n";
			print "</div>\n";
		}
	}
	return;
}

$l = _("Display transactions for account");
print "<br><h1>$l: $company</h1>\n<br>\n";
$d = date("m-Y");
list($m, $y) = explode('-', $d);
$begindate = "1-1-$y";
$enddate = date("d-m-Y");
print "<form method=\"get\">\n";
print "<input type=\"hidden\" name=\"module\" value=\"acctdisp\">\n";
print "<input type=\"hidden\" name=\"account\" value=\"$acct\">\n";
print "<table dir=\"rtl\"><tr>\n";
$l = _("From date: ");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"begin\" value=\"$begindate\"></td>\n";
$l = _("To date");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"end\" value=\"$enddate\"></td>\n";
print "</tr><tr>\n";
$l = _("Display");
print "<td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"$l\"></td></tr>\n";
print "</table>\n</form>\n";

?>

