<?PHP
/*
 | Balance report for Drorit accounting system
 | Written by Ori Idan July 2009
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = _("Balance report");
//	$reptitle = "מאזן";
	include('printhead.inc.php');
	print $header;
	
}
else {
	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
//	print "\talert(url);\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
global $AcctType;

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

/*
 | GetTotals:
 | Return associative array of 'credit', 'debit' and 'sum'
 */
function GetTotals($num, $begindate, $enddate) {
	global $transactionstbl;
	global $prefix;

	$query = "SELECT sum,num FROM $transactionstbl WHERE account='$num' AND date>='$begindate' AND date<='$enddate'";
	$query .= " AND prefix='$prefix'";
	$result = DoQuery($query, __LINE__);
	$debit = 0.0;
	$credit = 0.0;
	$sum = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$s = (double)$line['sum'];
		$n = $line['num'];
		if(($s < 0.01) && ($s > -0.01))
			$s = 0.0;
		$sum += $s;
		if($s > 0)
			$credit += $s;
		else {
			$s *= -1.0;
			$debit += $s;
		}
	}

	$totals['debit'] = (double)$debit;
	$totals['credit'] = (double)$credit;
	if(($sum < 0.01) && ($sum > -0.01))
		$sum = 0.0;
	$totals['sum'] = (double)$sum;
	return $totals;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
}
if($step != 0) {
	$l = _("Balance report");
	print "<br><h1>$l</h1>\n";
}
if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	//print "<br>\n";
	print "<div class=\"form righthalf1\">\n";
	$l = _("Balance report");
	print "<h3>$l</h3>\n";
	print "<form name=\"dtrange\" action=\"\" method=\"get\">\n";
	print "<input type=\"hidden\" name=\"module\" value=\"balance\">\n";
	print "<input type=\"hidden\" name=\"step\" value=\"1\">\n";
	print "<table cellpadding=\"5px\" cellspacing=\"5px\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("From date");
	print "<td>$l: </td>\n";
	print "<td><input type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$bdate\" size=\"7\">\n";
?>
<script type="text/javascript">
	addDatePicker("#begindate","<?print "$bdate"; ?>");
</script>
<?PHP
	print "</td>\n";
	$l = _("To date");
	print "<td>$l: </td>\n";
	print "<td><input type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$edate\" size=\"7\">\n";
?>
<script type="text/javascript">
		addDatePicker("#enddate","<?print "$edate"; ?>");
</script>
<?PHP
	print "</td>\n";
	print "</tr><tr>\n";
	$l = _("Execute");
	print "<td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
	print "</tr></table>\n";
	print "</form>\n";
	print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	ShowText('balance');
	print "</div>\n";
}
if($step == 2) {
	$filename = "tmp/balance.csv";
	$fd = fopen($filename, "w");
}
if($step >= 1) {
	$percent = isset($_GET['percent']) ? $_GET['percent'] : 0;
	$d6111 = isset($_GET['d6111']) ? $_GET['d6111'] : 0;
	$begindate = $_GET['begindate'];
	$enddate = $_GET['enddate'];
	$l = _("For period");
	print "<h2>$l: $begindate - $enddate</h2>\n";

	$bdate = FormatDate($begindate, "dmy", "mysql");
	$edate = FormatDate($enddate, "dmy", "mysql");
	if($step == 1) {
		if(!isset($module)) {
			print "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">\n";
			print "<table border=\"0\" cellpadding=\"3px\" class=\"printtbl\" align=\"center\">\n";
		}
		else
			print "<table border=\"0\" style=\"margin-right:2%\" cellpadding=\"3px\" class=\"hovertbl\">\n";
		if(!isset($module))
			print "<tr class=\"tblheadprt\" align=\"right\" style=\"border-top:1px solid\">\n";
		else
			print "<tr class=\"tblhead\" align=\"right\" style=\"border-top:1px solid;border-bottom:1px solid\">\n";
		$l = _("Account");
		print "<td style=\"width:15em\" align=\"right\">$l</td>\n";
		$l = _("6111 clause");
		print "<td style=\"width:8em\" align=\"right\">$l</td>\n";
		$l = _("Debit");
		print "<td style=\"width:7em\" align=\"right\">$l</td>\n";
		$l = _("Credit");
		print "<td style=\"width:7em\" align=\"right\">$l</td>\n";
		$l = _("Acc. balance");
		print "<td style=\"width:7em\" align=\"right\">$l</td>\n";
	}
	else {
		$l1 = _("Account");
		$l2 = _("6111 clause");
		$l3 = _("Debit");
		$l4 = _("Credit");
		$l5 = _("Acc. balance");
		fwrite($fd, "$l1,$l2,$l3,$l4,$l5");
//		fwrite($fd, "סעיף,סעיף 6111,חובה,זכות,סכום");
		fwrite($fd, "\n");
	}
	$totaldb = 0;
	$totalcrd = 0;
	$total = 0;
	for($type = 0; $type <= 12; $type++) {
		$tstr = $AcctType[$type];
		if($step == 1) {
			if(!isset($module))
				print "<tr class=\"tblheadprt\" align=\"right\" style=\"border-top:1px solid\">\n";
			else
				print "<tr class=\"tblhead\" align=\"right\" style=\"border-top:1px solid;border-bottom:1px solid\">\n";
			print "<td colspan=\"5\" align=\"right\">$tstr</td>\n";
		}
		else 
			fwrite($fd, "$tstr\n");

		$query = "SELECT num,company,id6111 FROM $accountstbl WHERE prefix='$prefix' AND type='$type'";
		$result = DoQuery($query, "balance.php");
		$tp = 0;
		$e = 0;
		$db = 0;
		$crd = 0;
		$sum = 0;
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$num = $line['num'];
			$acct = $line['company'];
			$id6111 = $line['id6111'];
			$tarr = GetTotals($num, $bdate, $edate);
		//	print "$num $acct";
		//	print_r($tarr);
		//	print "<br>\n"; 
			if(($tarr['debit'] == 0) && ($tarr['credit'] == 0))
				continue;
			if($step == 1) {
				NewRow();
				$url = "/?module=acctdisp&amp;account=$num&amp;begin=$begindate&amp;end=$enddate";
				if(isset($module))
					print "<td><a href=\"$url\">$acct</a></td>\n";
				else
					print "<td>$acct</td>\n";
				print "<td>$id6111</td>\n";
				$t = $tarr['debit'];
				$db += $t;
				$ts = number_format($t);
				print "<td>$ts</td>\n";
				$t = $tarr['credit'];
				$crd +=$t;
				$ts = number_format($t);
				print "<td>$ts</td>\n";
				$t = $tarr['sum'];
				$sum +=$t;
				$ts = number_format($t);
				print "<td dir=\"ltr\" align=\"right\">$ts</td>\n";
				print "</tr>\n";
			}
			else {
				$d = $tarr['debit'];
				$c = $tarr['credit'];
				$t = $tarr['sum'];
				fwrite($fd, "$acct,$id6111,$d,$c,$t\n");
			}
		}
		print "<tr class=\"sumline\">\n";
		$l = _("Total");
		print "<td colspan=\"2\">$l $tstr:</td>\n";
		print "<td>$db</td><td>$crd</td>\n";
		print "<td dir=\"ltr\" align=\"right\">$sum</td>\n";
		$totaldb += $db;
		$totalcrd += $crd;
		$total += $sum;
		print "</tr>\n";
	}
	print "<tr class=\"sumline\">\n";
	$l = _("Total");
	print "<td colspan=\"2\" align=\"left\"><b>$l: &nbsp;</b></td>\n";
	print "<td>$totaldb</td><td>$totalcrd</td>\n";
	$tstr = number_format($total);
	print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
	print "</tr>\n";
	print "</table>\n";
	if(isset($module) && ($step == 1)) {
		$url = "balance.php?print=1&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate";
		$url .= "&amp;prefix=$prefix";
		print "<div class=\"repbottom\">\n";
		$l = _("Print");
		print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
		print "&nbsp;&nbsp;";
		$url = "?module=profloss&amp;step=2&amp;begindate=$begindate&amp;enddate=$enddate";
		if($percent)
			$url .= "&amp;percent=on";
		$l = _("File export");
		print "<input type=\"button\" value=\"$l\" onclick=\"window.location.href='$url'\">\n";
		print "</div>\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		$l = _("Click here to download");
		print "<h2>$l: ";
		$url = "/download.php?file=$filename&amp;name=profloss.csv";
		print "<a href=\"$filename\">profloss.csv</a></h2>\n";
		$l = _("Right click and choose 'save as...'");
		print "<h2>$l</h2>\n";
		print "<script type=\"text/javascript\">\n";
		print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		print "</script>\n";
	}
}

?>
