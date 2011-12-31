<?PHP
/*
 | Create transactions report (תקבולים תשלומים)
 | This is part of Freelance accounting system.
 | Written by Ori Idan for Shay Harel
 | Modified by Adam BH 11/2011

 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
$text='';
/* open window script */
if(isset($module)) {
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}
if(!isset($prefix) || ($prefix == '')) {
	ErrorReport("לא ניתן לבצע פעולה זו ללא בחירת עסק");

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
$haeder='ספר תקבולים תשלומים';
if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	//print "<h1>$str</h1>\n";	
	//print "<h1>ספר תקבולים תשלומים</h1>\n";
}
else if($step != 0) {
	//print "<h1>ספר תקבולים תשלומים</h1>";
}
/* prepare temporary table */
$query = "DELETE FROM $tranreptbl WHERE prefix='$prefix'";
DoQuery($query, "inout.php");

$step = isset($_GET['step']) ? $_GET['step'] : 0;
if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	//$text.= "<div class=\"righthalf2\">\n";
	//$text.= "<div class=\"caption_out\"><div class=\"caption\">";
	$text.= "<b>ספר תקבולים תשלומים</b>\n";
	//$text.= "</div></div>\n";
	$text.= "<form name=\"dtrange\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"inout\">\n";
	$text.= "<input type=\"hidden\" name=\"step\" value=\"1\">\n";
	$text.= "<table dir=\"rtl\" cellpadding=\"20px\" cellspacing=\"20px\"><tr>\n";
	$text.= "<td>בחר תאריך תחילה: </td>\n";
	$text.= "<td><input class=\"date\" id=\"begindate\" type=\"text\" name=\"begindate\" value=\"$bdate\" size=\"7\">\n";

	$text.= "</td>\n";
	$text.= "<td>בחר תאריך סיום: </td>\n";
	$text.= "<td><input class=\"date\" type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$edate\" size=\"7\">\n";

	$text.= "</td>\n";
	$text.= "<td><input class=\"btnaction\" type=\"submit\" value=\"הפק\"></td>\n";
	$text.= "</tr></table>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	//print "<div class=\"lefthalf2\">\n";
	//ShowText('inout');
	//print "</div>\n";

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
	
	$text.= "<h2>לתקופה: $begindate - $enddate</h2>\n";
	
	//$numorderurl = "?module=tranrep&step=1&begindate=$begindate&enddate=$enddate&order=num";
	//$dtorderurl = "?module=tranrep&step=1&begindate=$begindate&enddate=$enddate&order=date";
	//$typeorderurl = "?module=tranrep&step=1&begindate=$begindate&enddate=$enddate&order=opacctname";
	if($step == 1) {
		$text.= "<table class=\"tablesorter\"><thead><tr>\n";
		//if(isset($module))
			$text.= "<th>תנועה</th>\n";
		//else
		//	$text.= "<th>תנועה&nbsp;</th>\n";
		//if(isset($module)) {
		//	$text.= "<td style=\"width:5.5em\"><a href=\"$dtorderurl\">תאריך</a></th>\n";
		//	$text.= "<td><a href=\"$typeorderurl\">סעיף&nbsp;</a></td>\n";
		//}
		//else {
			$text.= "<th style=\"width:5.5em\">תאריך</th>\n";
			$text.= "<th>סעיף&nbsp;</th>\n";
		//}
		$text.= "<th style=\"width:3.5em\">&nbsp;אסמכתא&nbsp;</th>\n";
		$text.= "<th>לקוח\\ספק&nbsp;</th>\n";
		$text.= "<th>פירוט&nbsp;&nbsp;&nbsp;</th>\n";
		$text.= "<th style=\"width:4em\">תקבול&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>\n";
		$text.= "<th style=\"width:4em\">תשלום&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>\n";
		$text.= "</tr></thead>\n";
	}
	else if($step == 2) {
		fwrite($fd, "\"תנועה\",");
		fwrite($fd, "\"תאריך\",");

		fwrite($fd, "\"סעיף\",");
		fwrite($fd, "\"אסמכתא\",");
		fwrite($fd, "\"לקוח\ספק\",");


		fwrite($fd, "\"פירוט\",");
		fwrite($fd, "\"תקבול\",");
		fwrite($fd, "\"תשלום\",");


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
	$result = DoQuery($query, __FILE__.": ".__LINE__);
	$e = 0;
	$tbody='';
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
			
			$tbody.= "<tr>\n";
			
			$tbody.= "<td>$num</td><td>$dtdmy</td><td>";
			//if(isset($module)) {
			$tbody.= "<a href=\"?module=acctdisp&account=$opacct&begin=$begindate&end=$enddate\">$opacctname</a></td><td>&nbsp;$refnum</td>";
			$tbody.= "<td><a href=\"?module=acctdisp&account=$acctnum&begin=$begindate&end=$enddate\">$acctname</a></td>";
			}
			//else {
			//	$text.= "$opacctname</td><td>&nbsp;$refnum</td>";
			//	$text.= "<td>$acctname</td>";
			//}
			$tbody.= "<td>$details</td>\n";
			if($accttype == SUPPLIER) {
				$tbody.= "<td>&nbsp;</td>\n";
				$tstr = number_format($sum);
				$tbody.= "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$td_sum += $sum;
			}
			else {
				$tstr = number_format($sum);
				$tbody.= "<td dir=\"ltr\" align=\"right\" >$tstr</td>\n";
				$tbody.= "<td>&nbsp;</td>\n";
				$tc_sum += $sum;
			}
			$tbody.= "</tr>\n";
		}
	}
	if($step == 1) {
		//if(!isset($module))
			$text.= "<tfoot><tr class=\"sumlineprt\">\n";
		//else
		//	$text.= "<tr class=\"sumline\">\n";
		$text.= "<td colspan=\"6\" align=\"left\"><b>סה\"כ: &nbsp;</b></td>\n";
		$tstr = number_format($tc_sum);
		$text.= "<td>$tstr</td>\n";
		$tstr = number_format($td_sum);
		$text.= "<td>$tstr</td>\n";
		$text.= "</tr></tfoot>\n";
		$text.= "$tbody</table>\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		$text.= "<h2>להורדת הדוח לחץ כאן: ";
		$url = "download.php?file=$filename&name=inout.csv";
		$text.= "<a href=\"$url\">inout.csv</a></h2>\n";
		//$//text.= "<h2>לחץ על שם הקובץ עם כפתור ימני ובחר \"שמור בשם\"</h2>\n";
		//$text.= "<script type=\"text/javascript\">\n";
		//$text.= "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		//$text.= "</script>\n";
	}

	if(isset($module) && ($step == 1)) {
		$url = "?module=tranrep&print=1&step=1&begindate=$begindate&enddate=$enddate";
		$url .= "&prefix=$prefix";
		if($order)
			$url .= "&order=$order";
		$text.= "<div class=\"repbottom\">\n";
		//$text.= "<input type=\"button\" value=\"הדפס\" onclick=\"PrintWin('$url')\">\n";
		//$text.= "&nbsp;&nbsp;";
		$text.= "<a class=\"btnsmall\" href='?module=inout&step=2&begindate=$begindate&enddate=$enddate'\">יצוא לקובץ</a>\n";
		$text.= "</div>\n";
	}

//}
createForm($text,$haeder,'',750,'','',1,getHelp());

?>
