<?PHP
/*
 | Create transactions report (׳×׳§׳‘׳•׳�׳™׳� ׳×׳©׳�׳•׳�׳™׳�)
 | This is part of Freelance accounting system.
 | Written by Ori Idan for Shay Harel
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = "׳¡׳₪׳¨ ׳×׳§׳‘׳•׳�׳™׳� ׳×׳©׳�׳•׳�׳™׳�";
	include('printhead.inc.php');
	print $header;
}

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;

/* open window script */
if(isset($module)) {
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}
if(!isset($prefix) || ($prefix == '')) {
	print "<h1>׳�׳� ׳ ׳™׳×׳� ׳�׳‘׳¦׳¢ ׳₪׳¢׳•׳�׳” ׳–׳• ׳�׳�׳� ׳‘׳—׳™׳¨׳× ׳¢׳¡׳§</h1>\n";
	return;
}

function GetAcctType($acct) {
	global $prefix, $accountstbl;

	$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctType");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
	print "<h1>׳¡׳₪׳¨ ׳×׳§׳‘׳•׳�׳™׳� ׳×׳©׳�׳•׳�׳™׳�</h1>\n";
}
else if($step != 0) {
	print "<h1>׳¡׳₪׳¨ ׳×׳§׳‘׳•׳�׳™׳� ׳×׳©׳�׳•׳�׳™׳�</h1>";
}
/* prepare temporary table */
$query = "DELETE FROM $tranreptbl WHERE prefix='$prefix'";
DoQuery($query, "inout.php");

$step = isset($_GET['step']) ? $_GET['step'] : 0;
if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	print "<div class=\"righthalf2\">\n";
	print "<div class=\"caption_out\"><div class=\"caption\">";
	print "<b>׳¡׳₪׳¨ ׳×׳§׳‘׳•׳�׳™׳� ׳×׳©׳�׳•׳�׳™׳�</b>\n";
	print "</div></div>\n";
	print "<form name=\"dtrange\" method=\"get\">\n";
	print "<input type=\"hidden\" name=\"module\" value=\"inout\">\n";
	print "<input type=\"hidden\" name=\"step\" value=\"1\">\n";
	print "<table dir=\"rtl\" cellpadding=\"20px\" cellspacing=\"20px\"><tr>\n";
	print "<td>׳‘׳—׳¨ ׳×׳�׳¨׳™׳� ׳×׳—׳™׳�׳”: </td>\n";
	print "<td><input type=\"text\" name=\"begindate\" value=\"$bdate\" size=\"7\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'dtrange',
		// input name
		'controlname': 'begindate'
	});
</script>
<?PHP
	print "</td>\n";
	print "<td>׳‘׳—׳¨ ׳×׳�׳¨׳™׳� ׳¡׳™׳•׳�: </td>\n";
	print "<td><input type=\"text\" name=\"enddate\" value=\"$edate\" size=\"7\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'dtrange',
		// input name
		'controlname': 'enddate'
	});
</script>
<?PHP
	print "</td>\n";
	print "<td><input type=\"submit\" value=\"׳”׳₪׳§\"></td>\n";
	print "</tr></table>\n";
	print "</form>\n";
	print "</div>\n";
	
}
if($step == 2) {
	$filename = "tmp/inoutrep.csv";
	$fd = fopen($filename, 'w');
}
if($step >= 1) {
	$RelevantTypes = array(RECEIPT, MANRECEIPT, SUPPLIERPAYMENT);
	$order = isset($_GET['order']) ? $_GET['order'] : '';
	$begindate = $_GET['begindate'];
	$enddate = $_GET['enddate'];
	$bdate = FormatDate($begindate, "dmy", "mysql");
	$edate = FormatDate($enddate, "dmy", "mysql");
	
	print "<h2>׳�׳×׳§׳•׳₪׳”: $begindate - $enddate</h2>\n";
	
	$numorderurl = "?module=tranrep&step=1&begindate=$begindate&enddate=$enddate&order=num";
	$dtorderurl = "?module=tranrep&step=1&begindate=$begindate&enddate=$enddate&order=date";
	$typeorderurl = "?module=tranrep&step=1&begindate=$begindate&enddate=$enddate&order=opacctname";
	if($step == 1) {
		print "<table border=\"0\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
		if(isset($module))
			print "<td><a href=\"$numorderurl\">׳×׳ ׳•׳¢׳”</a>&nbsp;</td>\n";
		else
			print "<td>׳×׳ ׳•׳¢׳”&nbsp;</td>\n";
		if(isset($module)) {
			print "<td style=\"width:5.5em\"><a href=\"$dtorderurl\">׳×׳�׳¨׳™׳�</a></td>\n";
			print "<td><a href=\"$typeorderurl\">׳¡׳¢׳™׳£&nbsp;</a></td>\n";
		}
		else {
			print "<td style=\"width:5.5em\">׳×׳�׳¨׳™׳�</td>\n";
			print "<td>׳¡׳¢׳™׳£&nbsp;</td>\n";
		}
		print "<td style=\"width:3.5em\">&nbsp;׳�׳¡׳�׳›׳×׳�&nbsp;</td>\n";
		print "<td>׳�׳§׳•׳—\\׳¡׳₪׳§&nbsp;</td>\n";
		print "<td>׳₪׳™׳¨׳•׳˜&nbsp;&nbsp;&nbsp;</td>\n";
		print "<td style=\"width:4em\">׳×׳§׳‘׳•׳�&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
		print "<td style=\"width:4em\">׳×׳©׳�׳•׳�&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
		print "</tr>\n";
	}
	else if($step == 2) {
		fwrite($fd, "\"׳×׳ ׳•׳¢׳”\",");
		fwrite($fd, "\"׳×׳�׳¨׳™׳�\",");
		fwrite($fd, "\"׳¡׳¢׳™׳£\",");
		fwrite($fd, "\"׳�׳¡׳�׳›׳×׳�\",");
		fwrite($fd, "\"׳�׳§׳•׳—\׳¡׳₪׳§\",");
		fwrite($fd, "\"׳₪׳™׳¨׳•׳˜\",");
		fwrite($fd, "\"׳×׳§׳‘׳•׳�\",");
		fwrite($fd, "\"׳×׳©׳�׳•׳�\",");
	}
//	print "order: $order<br>\n";
	$query = "SELECT * FROM $transactionstbl WHERE prefix='$prefix' ";
	$query .= "AND date>='$bdate' AND date<='$edate' ORDER BY num DESC";
	$result = DoQuery($query, "tranrep.php");
	$tnum = 0;
	$state = 0;
	$lastnum = 0;
	$wnum = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tnum = $line['num'];
		if(($lastnum > 0) && ($tnum != $lastnum) && (abs($sum) > 0.01)) {
			$opaccountname = GetAccountName($opaccount);
			$query = "INSERT INTO $tranreptbl VALUES('$prefix', '$lastnum', '$dt', ";
			$query .= "'$refnum', '$acctnum', '$acctname', '$opaccount', '$opaccountname', '$details', ";
			$query .= "'$sum', '0', '0')";
//			print "oppaccount: $oppaccount<br>\n";
//			print "<div dir=\"ltr\">Query: $query<br></div>\n";
			DoQuery($query, "inout.php");
			$wnum = $tnum;
			$sum = 0.0;
			$details = '';
			$oppaccount = 0;
			$acctname = '';
			$refnum = '';
		}
		$type = $line['type'];
		$dt = $line['date'];
		
		/*
		 | We are interested only in: RECEIPT, MANRECEIPT, SUPPLIERPAYMENT
		 */
		if(!in_array($type, $RelevantTypes))
			continue;
		$lastnum = $tnum;
		
		/* We now have a transaction with relevant type */
		$account = $line['account'];
		if(GetAcctType($account) == CUSTOMER) {
			$acctname = GetAccountName($account);
			$acctnum = $account;
			$details = $line['details'];
			$refnum = substr($line['refnum1'], -6);
			$sum = $line['sum'];
		}
		else if(GetAcctType($account) == CASH) {
			$novattotal = $line['sum'];
			$opaccount = $account;
			$opacctname = GetAccountName($account);
		}
		else if(GetAcctType($account) == SUPPLIER) {
			$acctname = GetAccountName($account);
			$acctnum = $account;
			$sum = $line['sum'];
			$refnum = substr($line['refnum1'], -6);
			$details = $line['details'];
		}
		else if(($account == CASH) || (GetAcctType($account) == BANKS)) {
			$sum = $line['sum'];
			$opaccount = $account;
//			print "Outcome: $oppaccount<br>\n";
		}
	}
	if(($type == MANRECEIPT) || ($type == RECEIPT) || ($type == SUPPLIERPAYMENT) || ($wnum == 0)) {
		$opacctname = GetAccountName($opaccount);
		$query = "INSERT INTO $tranreptbl VALUES('$prefix', '$lastnum', '$dt', ";
		$query .= "'$refnum', '$acctnum', '$acctname', '$opaccount', '$opacctname', '$details', ";
		$query .= "'$sum', '0', '0')";
//		print "Query: $query<br>\n";
		DoQuery($query, "inout1.php");
	}
	
	$tc_sum = 0.0;
	$td_sum = 0.0;
	if($order == '')
		$order = 'num';
	/* Now take data out of temporary table and display on screen table */
	$query = "SELECT * FROM $tranreptbl WHERE prefix='$prefix'";
	if($order) {
		$query .= " ORDER BY $order";
		if($order == 'num')
			$query .= " DESC";
	}
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "tranrep.php");
	$e = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$dtdmy = FormatDate($line['date'], "mysql", "dmy");
		$refnum = substr($line['refnum'], -6);
		$acctnum = $line['acctnum'];
		$accttype = GetAcctType($line['acctnum']);
		$acctname = $line['acctname'];
		$opacct = $line['opacct'];
//		print "opacct: $opacct<br>\n";
		$opacctname = GetAccountName($opacct);
		$details = $line['details'];
		$sum = $line['total'];
		$total_sum += $sum;

		if($step == 2) {
			fwrite($fd, "$num,$dtmy,\"$opacctname\",\"$refnum\",\"$acctname\",\"$details\",");
			if($accttype == SUPPLIER) {
				fwrite($fd, "\" \"");
				fwrite($fd, "$sum\n");
			}
			else {
				fwrite($fd, "$sum");
				fwrite($fd, "\n");
			}
		}
		if($step == 1) {
			if($e == 1) {
				print "<tr class=\"otherline\">\n";
				$e = 0;
			}
			else {
				print "<tr>\n";
				$e = 1;
			}
			print "<td>$num</td><td>$dtdmy</td><td>";
			if(isset($module)) {
				print "<a href=\"?module=acctdisp&account=$opacct&begin=$begindate&end=$enddate\">$opacctname</a></td><td>&nbsp;$refnum</td>";
				print "<td><a href=\"?module=acctdisp&account=$acctnum&begin=$begindate&end=$enddate\">$acctname</a></td>";
			}
			else {
				print "$opacctname</td><td>&nbsp;$refnum</td>";
				print "<td>$acctname</td>";
			}
			print "<td>$details</td>\n";
			if($accttype == SUPPLIER) {
				print "<td>&nbsp;</td>\n";
				$tstr = number_format($sum);
				print "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$td_sum += $sum;
			}
			else {
				$tstr = number_format($sum);
				print "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				print "<td>&nbsp;</td>\n";
				$tc_sum += $sum;
			}
			print "</tr>\n";
		}
	}
	if($step == 1) {
		if(!isset($module))
			print "<tr class=\"sumlineprt\">\n";
		else
			print "<tr class=\"sumline\">\n";
		print "<td colspan=\"6\" align=\"left\"><b>׳¡׳”\"׳›: &nbsp;</b></td>\n";
		$tstr = number_format($tc_sum);
		print "<td>$tstr</td>\n";
		$tstr = number_format($td_sum);
		print "<td>$tstr</td>\n";
		print "</tr>\n";
		print "</table>\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		print "<h2>׳�׳”׳•׳¨׳“׳× ׳”׳“׳•׳— ׳�׳—׳¥ ׳›׳�׳�: ";
		$url = "/download.php?file=$filename&name=tranrep.csv";
		print "<a href=\"$filename\">tranrep.csv</a></h2>\n";
		print "<h2>׳�׳—׳¥ ׳¢׳� ׳©׳� ׳”׳§׳•׳‘׳¥ ׳¢׳� ׳›׳₪׳×׳•׳¨ ׳™׳�׳ ׳™ ׳•׳‘׳—׳¨ \"׳©׳�׳•׳¨ ׳‘׳©׳�\"</h2>\n";
		print "<script type=\"text/javascript\">\n";
		print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		print "</script>\n";
	}

	if(isset($module) && ($step == 1)) {
		$url = "tranrep.php?print=1&step=1&begindate=$begindate&enddate=$enddate";
		$url .= "&prefix=$prefix";
		if($order)
			$url .= "&order=$order";
		print "<div class=\"repbottom\">\n";
		print "<input type=\"button\" value=\"׳”׳“׳₪׳¡\" onclick=\"PrintWin('$url')\">\n";
		print "&nbsp;&nbsp;";
		print "<input type=\"button\" value=\"׳™׳¦׳•׳� ׳�׳§׳•׳‘׳¥\" onclick=\"window.location.href='?module=tranrep&step=2&begindate=$begindate&enddate=$enddate'\">\n";
		print "</div>\n";
	}

}

if(!isset($module))
	print "</body>\n</html>\n";

?>
