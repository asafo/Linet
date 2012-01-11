<?PHP
/*
 | Customer source tax report for Freelance accounting system
 | Written by Ori Idan September 2009
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = "דוח ניכוי מס במקור";
	include('printhead.inc.php');
	print $header;
	
}
else {
	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
	print "\twindow.open(url, 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}


function GetAcctType($acct) {
	global $prefix, $accountstbl;

	$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctType");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}


function PrintTitle($customer, $step, $fd) {
	if($step < 2) {
		print "<br><h2>$customer</h2>\n";
		print "<table border=\"1\" dir=\"rtl\"><tr class=\"tblhead\">\n";
		print "<td>תנועה</td>\n";
		print "<td>קבלה</td>\n";
		print "<td>תאריך</td>\n";
		print "<td>סכום</td>\n";
		print "</tr>\n";
	}
	else {
		fwrite($fd, "$customer\n");
		fwrite($fd, "\"תנועה\",");
		fwrite($fd, "\"קבלה\",\"תאריך\",\"סכום\"\n");
	}
}

if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
}
else if($step != 0)
	print "<br><h1>דוח ניכוי מס במקור מלקוחות</h1>\n";

$step = isset($_GET['step']) ? $_GET['step'] : 0;
if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	print "<div class=\"righthalf2\">\n";
	print "<div class=\"caption_out\"><div class=\"caption\">";
	print "<b>דו\"ח ניכוי מס במקור מלקוחות</b>\n";
	print "</div></div>\n";
	print "<form name=\"dtrange\" method=\"get\">\n";
	print "<input type=\"hidden\" name=\"module\" value=\"srctax\">\n";
	print "<input type=\"hidden\" name=\"step\" value=\"1\">\n";
	print "<table dir=\"rtl\" cellpadding=\"20px\" cellspacing=\"20px\"><tr>\n";
	print "<td>בחר תאריך תחילה: </td>\n";
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
	print "<td>בחר תאריך סיום: </td>\n";
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
	print "<td><input type=\"submit\" value=\"הפק\"></td>\n";
	print "</tr></table>\n";
	print "</form>\n";
	print "</div>\n";
	print "<div class=\"lefthalf2\">\n";
	ShowText('srctax');
	print "</div>\n";
}
if($step == 2) {
	$filename = "tmp/srctax.csv";
	$fd = fopen($filename, "w");
}
if($step >= 1) {
	$begindate = $_GET['begindate'];
	$enddate = $_GET['enddate'];
	print "<h2>לתקופה: $begindate - $enddate</h2>\n";

	$bdate = FormatDate($begindate, "dmy", "mysql");
	$edate = FormatDate($enddate, "dmy", "mysql");

	if($step == 1) {
		print "<table dir=\"rtl\" border=\"0\" width=\"90%\" style=\"margin-right:10px\">\n";
		print "<tr><td width=\"50%\">\n";
	}
	$lastacct = 0;
	$srctaxacct = CUSTTAX;
	$t = CUSTOMER;
	$q1 = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t' ORDER BY company";
	$r1 = DoQuery($q1, "srctax.php");
	while($line = mysql_fetch_array($r1, MYSQL_ASSOC)) {
		$customer = $line['company'];
		$custacct = $line['num'];
		/* Find transactions for this customer */
		$query = "SELECT num FROM $transactionstbl WHERE account='$custacct' ";
		$query .= "AND prefix='$prefix' ";
		$query .= " AND date>='$bdate' AND date<='$edate' ORDER BY date";
		/* For each customer transaction, check if there is CUSTTAX transaction */
		$result = DoQuery($query, "srctax.php");
		while($line = mysql_fetch_array($result, MYSQL_NUM)) {
			$num = $line[0];	/* Got transaction number */
			$query = "SELECT * FROM $transactionstbl WHERE num='$num' AND prefix='$prefix' ";
			$query .= "AND account='$srctaxacct'";
			$r2 = DoQuery($query, "srctax.php");
			if(mysql_num_rows($r2)) {
				$line = mysql_fetch_array($r2, MYSQL_ASSOC);
//				print_r($line);
//				print "<br>\n";
				$num = $line['num'];
				$refnum = $line['refnum1'];
				$dt = FormatDate($line['date'], "mysql", "dmy");
				$sum = $line['sum'];
				$sum *= -1.0;
				if($custacct != $lastacct) {
					if($lastacct != 0) {
						if($step == 2)
							fwrite($fd, "\"סהכ ללקוח\", $custsum\n");
						else {
							if(!isset($module))
								print "<tr class=\"sumlineprt\">\n";
							else
								print "<tr class=\"sumline\">\n";
							print "<td colspan=\"3\">סה\"כ ללקוח</td>\n";
							print "<td>$custsum</td></tr>\n";
							print "</table>\n";	/* close previous table */
						}
					}
//					print "<h2>$customer</h2>\n";
					PrintTitle($customer, $step, $fd);
					$custsum = 0.0;
					$lastacct = $custacct;
				}
				$custsum += $sum;
				if($step == 2)
					fwrite($fd, "$num,$refnum,$dt,$sum\n");
				else {
					print "<tr>\n";
					print "<td>$num</td>\n";
					print "<td>$refnum</td>\n";
					print "<td>$dt</td>\n";
					print "<td>$sum</td>\n";
					print "</tr>\n";
				}
			}
		}
	}
	if($step == 1) {
		if($lastacct != 0) {
			if(!isset($module))
				print "<tr class=\"sumlineprt\">\n";
			else
				print "<tr class=\"sumline\">\n";
			print "<td colspan=\"3\">סה\"כ ללקוח</td>\n";
			print "<td>$custsum</td></tr>\n";
			print "</table>\n";
		}
		print "</td><td valign=\"top\">\n";
		ShowText('srctax1');
		print "</td></tr></table>\n";
		print "<br>\n";
	}
	if(isset($module) && ($step == 1)) {
		print "&nbsp;&nbsp;";
		$url = "srctax.php?print=1&step=1&begindate=$begindate&enddate=$enddate";
		$url .= "&prefix=$prefix";
		print "<input type=\"button\" value=\"הדפס\" onclick=\"PrintWin('$url')\">\n";
		print "&nbsp;&nbsp;";
		print "<input type=\"button\" value=\"יצוא לקובץ\" onclick=\"window.location.href='?module=srctax&step=2&begindate=$begindate&enddate=$enddate'\">\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		print "<h2>להורדת הדוח לחץ כאן: ";
		$url = "/download.php?file=$filename&name=srctax.csv";
		print "<a href=\"$filename\">srctax.csv</a></h2>\n";
		print "<h2>לחץ על שם הקובץ עם כפתור ימני ובחר \"שמור בשם\"</h2>\n";
		print "<script type=\"text/javascript\">\n";
		print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		print "</script>\n";
	}
}
?>
