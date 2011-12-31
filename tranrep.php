<?PHP
/*
 | Create transactions report
 | This is part of Drorit accounting system.
 | Written by Ori Idan
 */
if(!isset($module)) {
	/*header('Content-type: text/html;charset=UTF-8');

	include('include/config.inc.php');
	include('includ/func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = _("Income outcome report");
	include('printhead.inc.php');
	print $header;*/
}
$text='';
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
	$l = _("This operation can not be executed without choosing a business first");
	ErrorReport($l);
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

$step = isset($_GET['step']) ? $_GET['step'] : 0;

$reptitle = _("Income outcome report");
/*if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";
	print "<h1>$reptitle</h1>\n";
}
else*/
 if($step != 0) {
	//adam: no need print "<h1>$reptitle</h1>";
	//print "<br />test";
}
/* prepare temporary table */
$query = "DELETE FROM $tranreptbl WHERE prefix='$prefix'";
DoQuery($query, "tranrep.php");

if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	//print "<br>\n";
	//print "<div class=\"form righthalf1\">\n";
	//print "<h3>$reptitle</h3>\n";
	$text.= "<form name=\"dtrange\" action=\"\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"tranrep\" />\n";
	$text.= "<input type=\"hidden\" name=\"step\" value=\"1\" />\n";
	$text.= "<table dir=\"rtl\" cellpadding=\"20px\" cellspacing=\"20px\" class=\"formtbl\" width=\"100%\" style=\"height:40px\"><tr>\n";
	$l = _("From date");
	$text.= "<td valign=\"middle\">$l: </td>\n";
	$text.= "<td valign=\"middle\"><input class=\"date\" type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$bdate\" size=\"7\" />\n";
//$text.='<script type="text/javascript">addDatePicker("#begindate","'.$bdate.'");</script>';

	$text.= "</td>\n";
	$l = _("To date");
	$text.= "<td valign=\"middle\">$l: </td>\n";
	$text.= "<td valign=\"middle\"><input class=\"date\" type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$edate\" size=\"7\" />\n";
//$text.='<script type="text/javascript">addDatePicker("#enddate","'.$edate.'");</script>';
	$text.= "</td>\n";
	$l = _("Execute");
	$text.= "<td><input type=\"submit\" value=\"$l\" class='btnaction' /></td>\n";
	//$text.= "<td><input type=\"submit\" value=\"$l\" /></td>\n";
	$text.= "</tr></table>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	//print "test";
	createForm($text,$reptitle,'',750,'','',1,getHelp());

	
	//print "test";
}
if($step == 2) {
	$filename = "tmp/tranrep.csv";
	$fd = fopen($filename, 'w');
}
if($step >= 1) {
	$RelevantTypes = array(INVOICE, SUPINV, MANINVOICE);
	$order = isset($_GET['order']) ? $_GET['order'] : '';
	$begindate = $_GET['begindate'];
	$enddate = $_GET['enddate'];
	$bdate = FormatDate($begindate, "dmy", "mysql");
	$edate = FormatDate($enddate, "dmy", "mysql");
	//$text= '';
	$l=_("For period");
	$text.= "<h2>$l: $begindate - $enddate</h2>\n";
	//print "test";
	//$numorderurl = "?module=tranrep&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate&amp;order=num";
	//$dtorderurl = "?module=tranrep&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate&amp;order=date";
	//$typeorderurl = "?module=tranrep&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate&amp;order=opacctname";
	if($step == 1) {
		$text.= "<table border=\"0\" class=\"formy\"><tr>\n";
		$text.= "<th colspan=\"6\">&nbsp;</th>\n";
		$l = _("Incomes");
		$text.= "<th colspan=\"3\" align=\"center\">$l</th>\n";
		$l = _("Outcomes");
		$text.= "<th colspan=\"3\" align=\"center\">$l</th>\n";
		$text.= "</tr><tr>\n";
		$l = _("Transaction");
		if(isset($module))
			$text.= "<td>$l&nbsp;</td>\n";
		else
			$text.= "<td>$l&nbsp;</td>\n";
		if(isset($module)) {
			$l = _("Date");
			$text.= "<td style=\"width:5.5em\">$l</td>\n";
			$l = _("Account");
			$text.= "<td>$l&nbsp;</td>\n";
		}
		else {
			$l = _("Date");
			$text.= "<td style=\"width:5.5em\">$l</td>\n";
			$l = _("Account");
			$text.= "<td>$l&nbsp;</td>\n";
		}
		$l = _("Ref. num");
		$text.= "<td style=\"width:3.5em\">&nbsp;$l&nbsp;</td>\n";
		$l = _("Customer//Supplier");
		$text.= "<td>$l&nbsp;</td>\n";
		$l = _("Details");
		$text.= "<td>$l&nbsp;&nbsp;&nbsp;</td>\n";
		$l = _("Sum");
		$text.= "<td style=\"width:4em\">$l&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
		$l = _("VAT");
		$text.= "<td style=\"width:3.5em\">$l&nbsp;&nbsp;&nbsp;</td>\n";
		$l = _("Inc. VAT");
		$text.= "<td >$l&nbsp;&nbsp;&nbsp;</td>\n";
		$l = _("Sum");
		$text.= "<td style=\"width:4em\">$l&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
		$l = _("VAT");
		$text.= "<td style=\"width:3.5em\">$l&nbsp;&nbsp;&nbsp;</td>\n";
		$l = _("Inc. VAT");
		$text.= "<td>$l&nbsp;&nbsp;&nbsp;</td>\n";
		$text.= "</tr>\n";
	}
	else if($step == 2) {
		$l = _("Tran.");
		fwrite($fd, "\"$l\",");
		$l = _("Date");
		fwrite($fd, "\"$l\",");
		$l = _("Account");
		fwrite($fd, "\"$l\",");
		$l = _("Ref. num");
		fwrite($fd, "\"$l\",");
		$l = _("Customer/Supplier");
		fwrite($fd, "\"$l\",");
		$l = _("Details");
		fwrite($fd, "\"$l\",");
		$l = _("Income");
		fwrite($fd, "\"$l\",");
		$l = _("Income VAT");
		fwrite($fd, "\"$l\",");
		$l = _("Total income");
		fwrite($fd, "\"$l\",");
		$l = _("Outcome");
		fwrite($fd, "\"$l\",");
		$l = _("Outcome VAT");
		fwrite($fd, "\"$l\",");
		$l = _("Total outcome");
		fwrite($fd, "\"$l\"\n");
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
			$query .= "'$sum', '$tvat', '$novattotal')";
//				print "oppaccount: $opaccount<br>\n";
//				print "<div dir=\"ltr\">Query: $query<br></div>\n";
			DoQuery($query, "tranrep.php");
			$wnum = $tnum;
			$sum = 0.0;
			$tvat = 0.0;
			$novattotal = 0.0;
			$details = '';
			$oppaccount = 0;
			$acctname = '';
			$refnum = '';
		}
		$type = $line['type'];
		$dt = $line['date'];
		
		/*
		 | We are interested only in: INVOICE, SUPINV, MANINVOICE
		 */
		if(!in_array($type, $RelevantTypes))
			continue;
		$lastnum = $tnum;
		
		/* We now have a transaction with relevant type */
		$account = $line['account'];
		$actype = GetAcctType($account);
		if($account == BUYVAT) {
			$tvat = $line['sum'];
			$tvat *= -1.0;
		}
		if($account == SELLVAT) {	/* probably INVOICE or MANINVOICE */
			$tvat = $line['sum'];
		}
		else if($actype == CUSTOMER) {
			$acctname = GetAccountName($account);
			$acctnum = $account;
			$details = $line['details'];
			$refnum = substr($line['refnum1'], -6);
			$sum = $line['sum'];
			$sum *= -1.0;
		}
		else if($actype == INCOME) {
			$novattotal += $line['sum'];
			$opaccount = $account;
//			print "tnum: $tnum, opacct: $opaccount<br>\n";
		}
		else if($actype == SUPPLIER) {
			$acctname = GetAccountName($account);
			$acctnum = $account;
			$sum = $line['sum'];
			$refnum = substr($line['refnum1'], -6);
			$details = $line['details'];
		}
		else if(($actype == OUTCOME) || ($actype == OBLIGATIONS)) {
			$novattotal += $line['sum'];
			$opaccount = $account;
			$opacctname = GetAccountName($opaccount);
//			print "tnum: $tnum, Outcome: $oppaccount<br>\n";
		}
	}
	if(($type == MANINVOICE) || ($type == INVOICE) || ($type == SUPINV) || ($wnum == 0)) {
		$opacctname = GetAccountName($opaccount);
		$query = "INSERT INTO $tranreptbl VALUES('$prefix', '$lastnum', '$dt', ";
		$query .= "'$refnum', '$acctnum', '$acctname', '$opaccount', '$opacctname', '$details', ";
//		$novattotal = $sum - $tvat;
		$query .= "'$sum', '$tvat', '$novattotal')";
//		print "Query: $query<br>\n";
		DoQuery($query, "tranrep1.php");
	}
	
	$tc_sum = 0.0;
	$tc_tvat = 0.0;
	$tc_novattotal = 0.0;
	$td_sum = 0.0;
	$td_tvat = 0.0;
	$tc_novattotal = 0.0;
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
		$tvat = $line['vat'];
		$novattotal = abs($line['sum']);

		if($step == 2) {
			fwrite($fd, "$num,$dtmy,\"$opacctname\",\"$refnum\",\"$acctname\",\"$details\",");
			if($accttype == SUPPLIER) {
				fwrite($fd, "\" \",\" \",\" \",");
				fwrite($fd, "$novattotal,$tvat,$sum\n");
			}
			else {
				fwrite($fd, "$novattotal,$tvat,$sum");
				fwrite($fd, " , , \n");
			}
		}
		if($step == 1) {
			if($e == 1) {
				$text.= "<tr class=\"otherline\">\n";
				$e = 0;
			}
			else {
				$text.= "<tr>\n";
				$e = 1;
			}
			$text.= "<td>$num</td><td>$dtdmy</td><td>";
			if(isset($module)) {
				$text.= "<a href=\"?module=acctdisp&amp;account=$opacct&amp;begin=$begindate&amp;end=$enddate\">$opacctname</a></td><td>&nbsp;$refnum</td>";
				$text.= "<td><a href=\"?module=acctdisp&amp;account=$acctnum&amp;begin=$begindate&amp;end=$enddate\">$acctname</a></td>";
			}
			else {
				$text.= "$opacctname</td><td>&nbsp;$refnum</td>";
				$text.= "<td>$acctname</td>";
			}
			$text.= "<td>$details</td>\n";
			if($accttype == SUPPLIER) {
				$text.= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>\n";
				$tstr = number_format($novattotal);
				$text.= "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
				$tstr = number_format($tvat);
				$text.= "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
				$tstr = number_format($sum);
				$text.= "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$td_sum += $sum;
				$td_tvat += $tvat;
				$td_novattotal += $novattotal;
			}
			else {
/*				if($sum != ($tvat + $novattotal))
					$novattotal = $sum - $tvat; */

				$tstr = number_format($novattotal);
				$text.= "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$tstr = number_format($tvat);
				$text.= "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$tstr = number_format($sum);
				$text.= "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$text.= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>\n";
				$tc_sum += $sum;
				$tc_tvat += $tvat;
				$tc_novattotal += $novattotal;
			}
			$text.= "</tr>\n";
		}
	}
	if($step == 1) {
		if(!isset($module))
			$text.= "<tr class=\"sumlineprt\">\n";
		else
			$text.= "<tr class=\"sumline\">\n";
		$l = _("Total");
		$text.= "<td colspan=\"6\" align=\"left\"><b>$l: &nbsp;</b></td>\n";
		$tstr = number_format($tc_novattotal);
		$text.= "<td>$tstr</td>\n";
		$tstr = number_format($tc_tvat);
		$text.= "<td>$tstr</td>\n";
		$tstr = number_format($tc_sum);
		$text.= "<td>$tstr</td>\n";
		$tstr = number_format($td_novattotal);
		$text.= "<td>$tstr</td>\n";
		$tstr = number_format($td_tvat);
		$text.= "<td>$tstr</td>\n";
		$tstr = number_format($td_sum);
		$text.= "<td>$tstr</td>\n";
		$text.= "</tr>\n";
		$text.= "</table>\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		$l = _("Click here to download report");
		$text.= "<h2>$l: ";
		$url = "download.php?file=$filename&amp;name=tranrep.csv";
		$text.= "<a href=\"$url\">tranrep.csv</a></h2>\n";
		//$l = _("Right click and choose 'save as...'");
		//$text.= "<h2>$l</h2>\n";
		//$text.= "<script type=\"text/javascript\">\n";
		//$text.= "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		//$text.= "</script>\n";
	}

	if(isset($module) && ($step == 1)) {
		$url = "tranrep.php?print=1&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate";
		$url .= "&amp;prefix=$prefix";
		if($order)
			$url .= "&amp;order=$order";
		//print "<div class=\"repbottom\">\n";
		$l = _("Print");
		//$text.= "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
		$text.= "&nbsp;&nbsp;";
		$l = _("File export");
		$text.= "<a class=\"btnsmall\" href='?module=tranrep&amp;step=2&amp;begindate=$begindate&amp;enddate=$enddate'\">$l</a>\n";
		//print "</div>\n";
	}
createForm($text,$reptitle,'',750,'','',1,getHelp());
}

//if(!isset($module))
	//print "</body>\n</html>\n";

?>
