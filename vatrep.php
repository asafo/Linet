<?PHP
//M:חישוב מע"מ
/*
 | VAT calculatin script for Freelance accounting system
 | Written by Ori Idan
 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
global $montharr;

$montharr = array(_("January"), _("February"), _("March"), _("April"),
	_("May"), _("June"), _("July"), _("August"), _("September"), 
	_("October"), _("November"), _("December"));

// $montharr = array('ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני', 'יולי', 'אוגוסט',
//	'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר');

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

function GetAcctTotal($account, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	$n = mysql_num_rows($result);
//	print "($n)  ";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];	
	}
	return $total;
}

function GetLastDayOfMonth($month, $year) {
	$last = 31;
	
	if($month == 0)
		return $last;
	while(!checkdate($month, $last, $year)) {
	//	print "$last-$month-$year<br>\n";
		$last--;
	}
	return $last;
}

function PrintMonthSelect($def, $name) {
	global $montharr;
	
	print "<select name=\"$name\">\n";
	foreach($montharr as $i => $m) {
		$i++;
		$d = ($def == $i) ? " selected" : "";
		print "<option value=\"$i\"$d>$m</option>\n";
	}
	print "</select>\n";
}

function PrintYearSelect($year) {
	$max = $year + 1;
	
	print "<select name=\"year\" >\n";
	for($min = $year - 2; $min <= $max; $min++) {
		print "<option value=\"$min\" ";
		if($min == $year)
			print "selected";
		print ">$min</option>\n";
	}
	print "</select>\n";
}

function GetSumForAcct($acct, $begin, $end) {
	global $transactionstbl;
	global $prefix;
	
//	print "Calculating sum for $acct ($begin - $end)<BR>\n";
	$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' ";
	$query .= "AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br />\n";
	$result = mysql_query($query);	/* get accounts numbers */
	if(!$result) {
		echo mysql_error();
		exit;
	}
	$sum = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$s = $line[0];
//		print "$s<br>\n";	
		$sum += $line[0];
	}
//	print "Sum: $sum<BR>\n";
	return $sum;
}

function GetSumForAcctType($acct_type, $begin, $end, $usevat) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT num,src_tax FROM $accountstbl WHERE type='$acct_type' AND prefix='$prefix'";
	$result = mysql_query($query);	/* get accounts numbers */
	if(!$result) {
		echo mysql_error();
		exit;
	}
	$sum = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
		$vat = $line[1];
		if($usevat && (($vat != '') || ($vat > 0)))
			$sum += GetSumForAcct($num, $begin, $end);
		else if(($usevat == 0) && ($vat != '') && ($vat == 0))
			$sum += GetSumForAcct($num, $begin, $end);
//		print "$num, $vat, $sum<br>\n";

	}
	return $sum;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 0) {	/* print date select form */
	$today = date('d-m-Y');
	list($day, $month, $year) = split("-", $today);

	/* Check if we report each month or two month */
	$query = "SELECT vatrep FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "vatrep.php");
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$vatrep = $line['vatrep'];
	if($vatrep == 2) {
		if($month % 2) {	/* this is odd month number */
			$beginmonth = $month - 2;
			if($beginmonth <= 0) {
				$beginyear = $year - 1;
				$beginmonth += 12;
			}
			$endmonth = $month - 1;
			if($endmonth <= 0) {
				$endyear = $year - 1;
				$endmonth += 12;
			}
		}
		else {
			$beginmonth = $month - 1;
			if($beginmonth <= 0) {
				$beginyear = $year - 1;
				$beginmonth += 12;
			}
			$endmonth = $month;
		}
	}
	else {
		$beginmonth = $month - 1;
		if($beginmonth <= 0) {
			$beginyear = $year - 1;
			$beginmonth += 12;
		}
		$endmonth = $beginmonth;
	}
	if(!$beginyear)
		$beginyear = $year;
	if(!$endyear)
		$endyear = $beginyear;
	
//	print "Endmonth: $endmonth<br>\n";
	$last2 = GetLastDayOfMonth($endmonth, $endyear);
	$begindate = "1-$beginmonth-$beginyear";
	$enddate = "$last2-$endmonth-$endyear";
	
	//print "<br>\n";
	print "<div class=\"form righthalf1\">\n";
	$l = _("VAT report for period");
	print "<h3>$l</h3>";
	print "<form action=\"?module=vatrep&amp;step=1\" method=\"post\">\n";
	print "<input type=\"hidden\" name=\"beginyear\" value=\"$beginyear\">\n";
	print "<input type=\"hidden\" name=\"endyear\" value=\"$endyear\">\n";
	print "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("Calculate VAT report for month");
	print "<td>$l: &nbsp;</td>\n";
	print "<td>\n";
	PrintMonthSelect($beginmonth, 'beginmonth');
	print "&nbsp;</td>\n";
	print "<td> &nbsp; </td>\n";	/* just to create small space */
	print "<td>\n";
	PrintMonthSelect($endmonth, 'endmonth');
	print "<td>\n";
	PrintYearSelect($beginyear);
	print "</td>\n";
	print "<td>\n";
	$l = _("Execute");
	print "&nbsp;&nbsp;<input type=\"submit\" value=\"$l\"></td></tr>\n";
	print "</table>\n";
	print "</form>\n";
	print "<br>\n";
	
	print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	ShowText('vatrep');
	print "</div>\n";
	return;
}
if($step == 1) {
	$beginmonth = $_POST['beginmonth'];
	$endmonth = $_POST['endmonth'];
	$beginyear = $_POST['beginyear'];
	$endyear = $_POST['endyear'];
	$year = $_POST['year'];
	$begindate = "1-$beginmonth-$year";
	$d = GetLastDayOfMonth($endmonth, $year);
	$enddate = "$d-$endmonth-$year";
//	print "$begindate - $enddate<br>\n";
	$begin = FormatDate($begindate, "dmy", "mysql");
	$end = FormatDate($enddate, "dmy", "mysql");
	
	$bm = $montharr[$beginmonth - 1];
	$em = $montharr[$endmonth - 1];
	$l = _("VAT report for period");
	print "<br><h1>$l: $bm - $em</h1>\n";
	print "<form action=\"?module=vatrep&amp;step=2\" method=\"post\">\n";
	print "<table dir=\"ltr\" border=\"0\" class=\"formtbl\">\n";
	print "<tr><td colspan=\"3\" align=center>\n";
	print "<input type=\"hidden\" name=\"beginmonth\" value=\"$beginmonth\">\n";
	print "<input type=\"hidden\" name=\"endmonth\" value=\"$endmonth\">\n";
	print "<input type=\"hidden\" name=\"begindate\" value=\"$begindate\">\n";
	print "<input type=\"hidden\" name=\"enddate\" value=\"$enddate\">\n";
	print "</td></tr><tr>\n";
	print "<td align=\"center\">\n";
	print "עסקאות פטורות<br>\n";
	$novatincome = GetSumForAcctType(INCOME, $begin, $end, 0);
	$novatincome = round($novatincome, 0);
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"novatincome\" value=\"$novatincome\">\n";
	print "</td><td dir=\"rtl\" align=\"center\">\n";
	print "עסקאות חייבות ללא מע\"מ<br>\n";
	$vatincome = GetSumForAcctType(INCOME, $begin, $end, 1);
	$vatincome = round($vatincome, 0);
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"vatincome\" value=\"$vatincome\">\n";
	print "</td><td dir=\"rtl\" align=\"center\">\n";
	print "המס על העסקאות<br>";
	$sellvat = round(GetSumForAcct(SELLVAT, $begin, $end), 0);
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"sellvat\" value=\"$sellvat\">\n";
	print "</td></tr><tr>\n";
	print "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	print "<td dir=\"rtl\" align=\"center\">\n";
	print "תשומות ציוד ונכסים<br>\n";
	$assetvat = round(GetSumForAcct(ASSETVAT, $begin, $end), 0);
	if($assetvat < 0)
		$assetvat *= -1.0;
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"assetvat\" value=\"$assetvat\">\n";
	print "</td></tr><tr>\n";
	print "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	print "<td dir=\"rtl\" align=\"center\">\n";
	print "תשומות אחרות<br>\n";
	$buyvat = round(GetSumForAcct(BUYVAT, $begin, $end), 0);
	if($buyvat < 0)
		$buyvat *= -1.0;
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"buyvat\" value=\"$buyvat\">\n";
	print "</td></tr><tr>\n";
	print "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	print "<td dir=\"rtl\" align=\"center\">\n";
	print "סכום לתשלום<br>\n";
	$payvat = $sellvat - $assetvat - $buyvat;
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"payvat\" value=\"$payvat\">\n";
	print "</td></tr>\n";
	print "<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" value=\"רשום\"></td></tr>\n"; 
	print "</table>\n"; 
	print "</form>\n";
}
if($step == 2) {
	$begindate = $_POST['begindate'];
	$enddate = $_POST['enddate'];
	$beginmonth = $_POST['beginmonth'];
	$endmonth = $_POST['endmonth'];
	$novatincome = $_POST['novatincome'];
	$vatincome = $_POST['vatincome'];
	$sellvat = $_POST['sellvat'];
	$assetvat = $_POST['assetvat'];
	$buyvat = $_POST['buyvat'];
	$payvat = $_POST['payvat'];

	$bm = $montharr[$beginmonth - 1];
	$em = $montharr[$endmonth - 1];
	print "<br><h1>דו\"ח מע\"מ לתקופה: $bm - $em</h1>\n";
	
	/* Now the real thing, register transactions... */
	list($day1, $month1, $year1) = split("[/.-]", $begindate);
	list($day2, $month2, $year2) = split("[/.-]", $enddate);
	$ref1 = "$month1$year1";
	$ref2 = "$month2$year2";
//	$date = date('d-m-Y');
	$date = $enddate;	/* register transactions on last date of report */
	/* first check if we already have transactions */
	$t = VAT;
	$query = "SELECT num FROM $transactionstbl WHERE type='$t' AND refnum1='$ref1' AND refnum2='$ref2' AND prefix='$prefix'";
	$result = DoQuery($query, "vatrep.php");
	$num = mysql_num_rows($result);
	if($num == 0) {
		// Transaction 1 חובת מע"מ עסקאות זכות מע"מ חו"ז
		$s = $sellvat * -1.0;
		$tnum = Transaction(0, VAT, SELLVAT, $ref1, $ref2, $date, 'מע\"מ', $s);
		$tnum = Transaction($tnum, VAT, PAYVAT, $ref1, $ref2, $date, 'מע\"מ', $sellvat);
		// Transaction 2 זכות מע"מ תשומות חובת מע"מ חו"ז
		$b = $buyvat * -1.0;
		$tnum = Transaction(0, VAT, BUYVAT, $ref1, $ref2, $date, 'מע\"מ', $buyvat);
		$tnum = Transaction($tnum, VAT, PAYVAT, $ref1, $ref2, $date, 'מע\"מ', $b);
		// Transaction 3 זכות מע"מ תשומות ונכסים, חובת מע"מ חו"ז
		$a = $assetvat * -1.0;
		$tnum = Transaction(0, VAT, ASSETVAT, $ref1, $ref2, $date, 'מע\"מ', $assetvat);
		$tnum = Transaction($tnum, VAT, PAYVAT, $ref1, $ref2, $date, 'מע\"מ', $a);
	}

	print "<table dir=\"ltr\" class=\"formtbl\" border=\"0\"><tr>\n";
	print "<td align=\"center\">\n";
	print "עסקאות פטורות<br>\n";
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"novatincome\" value=\"$novatincome\">\n";
	print "</td><td dir=\"rtl\" align=\"center\">\n";
	print "עסקאות חייבות ללא מע\"מ<br>\n";
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"vatincome\" value=\"$vatincome\">\n";
	print "</td><td dir=\"rtl\" align=\"center\">\n";
	print "המס על העסקאות<br>";
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"sellvat\" value=\"$sellvat\">\n";
	print "</td></tr><tr>\n";
	print "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	print "<td dir=\"rtl\" align=\"center\">\n";
	print "תשומות ציוד ונכסים<br>\n";
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"assetvat\" value=\"$assetvat\">\n";
	print "</td></tr><tr>\n";
	print "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	print "<td dir=\"rtl\" align=\"center\">\n";
	print "תשומות אחרות<br>\n";
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"buyvat\" value=\"$buyvat\">\n";
	print "</td></tr><tr>\n";
	print "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	print "<td dir=\"rtl\" align=\"center\">\n";
	print "סכום לתשלום<br>\n";
	print "<input dir=\"ltr\" type=\"text\" readonly name=\"payvat\" value=\"$payvat\">\n";
	print "</td></tr>\n";
	print "<tr><td colspan=\"3\" dir=\"rtl\" align=\"center\">\n";
	print "<table dir=\"rtl\" border=\"0\"><tr><td>\n";
	print "<form action=\"?module=payment&step=1&opt=vat\" method=\"post\">\n";
	$vatacc = PAYVAT;
	print "<input type=\"hidden\" name=\"account\" value=\"$vatacc\">\n";
	print "<input type=\"hidden\" name=\"refnum\" value=\"$ref1-$ref2\">\n";
	print "<input type=\"hidden\" name=\"total\" value=\"$payvat\">\n";
	print "<input type=\"submit\" value=\"תשלום\">\n";
	print "</form>\n";
	print "</td></tr></table>\n";
	print "</tr>\n";
	print "</table>\n";
}

if($step == 3) {
	if(isset($_GET['begin'])) {
		$bdate = $_GET['begin'];
		$edate = $_GET['edate'];
	}
	else {
		$edate = date("d-m-Y");
		list($d, $m, $y) = explode('-', $edate);
		$bdate = "1-1-$y";
	}
	$begin = FormatDate($bdate, "dmy", "mysql");
	$end = FormatDate($edate, "dmy", "mysql");
	print "<br><h1>הצג תנועות</h1>\n";
	print "<div class=\"righthalf2\">\n";
	print "<form name=\"vattran\" method=\"get\">\n";
	print "<input type=\"hidden\" name=\"module\" value=\"vatrep\">\n";
	print "<input type=\"hidden\" name=\"step\" value=\"3\">\n";
	print "<br>מתאריך: \n";
	print "<input type=\"text\" name=\"begin\" size=\"7\" value=\"$bdate\">\n";
?>
<script type="text/javascript">
	new tcal ({
		// form name
		'formname': 'vattran',
		// input name
		'controlname': 'begin'
	});
</script>
<?PHP
	print "עד תאריך: ";
	print "<input type=\"text\" name=\"end\" size=\"7\" value=\"$edate\">\n";
?>
<script type="text/javascript">
	new tcal ({
		// form name
		'formname': 'vattran',
		// input name
		'controlname': 'end'
	});
</script>
<?PHP
	print "<input type=\"submit\" value=\"הצג\">\n";
	print "</form>\n";
	print "<br><br>\n";
	print "<h2>";
	print "<a href=\"?module=acctdisp&account=1&begin=$bdate&end=$edate\">מע\"מ תשומות</a>\n";
	$total = GetAcctTotal(1, $begin, $end);
	print "<span dir=\"ltr\">$total</span>";
	print "&nbsp;&nbsp;&nbsp;&nbsp;\n";
	print "<a href=\"?module=acctdisp&account=3&begin=$bdate&end=$edate\">מע\"מ עסקאות</a>\n";
	$total = GetAcctTotal(3, $begin, $end);
	print "<span dir=\"ltr\">$total</span>";
	print "</h2>\n";
	print "</div>\n";
	print "<div class=\"lefthalf2\">\n";
	ShowText('vatrep2');
	print "</div>\n";
}

?>

