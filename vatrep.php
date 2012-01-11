<?PHP
/*
 | VAT calculatin script for Freelance accounting system
 | Written by Ori Idan
 | modfied by Adam BH
 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
global $montharr;

if(!isset($prefix) || ($prefix == '')) {
	ErrorReport(_("This operation can not be executed without choosing a business first"));
	 //"<h1>$l</h1>\n";
	return;
}
$text='';
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
	
	$str= "<select name=\"$name\">\n";
	foreach($montharr as $i => $m) {
		$i++;
		$d = ($def == $i) ? " selected" : "";
		$str.= "<option value=\"$i\"$d>$m</option>\n";
	}
	$str.= "</select>\n";
	return $str;
}

function PrintYearSelect($year) {
	$max = $year + 1;
	
	$str= "<select name=\"year\" >\n";
	for($min = $year - 2; $min <= $max; $min++) {
		$str.= "<option value=\"$min\" ";
		if($min == $year)
			$str.= "selected";
		$str.= ">$min</option>\n";
	}
	$str.= "</select>\n";
	return $str;
}

function GetSumForAcct($acct, $begin, $end) {
	global $transactionstbl;
	global $prefix;
	
//	print "Calculating sum for $acct ($begin - $end)<BR>\n";
	$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' ";
	$query .= "AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br />\n";
	$result = DoQuery($query,__FILE__.": ".__LINE__);	/* get accounts numbers */
	
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
	$result = DoQuery($query,__FILE__.": ".__LINE__);	/* get accounts numbers */
	
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
	//print "<div class=\"form righthalf1\">\n";
	$haeder = _("VAT report for period");
	//print "<h3>$l</h3>";
	$text.= "<form action=\"?module=vatrep&amp;step=1\" method=\"post\">\n";
	$text.= "<input type=\"hidden\" name=\"beginyear\" value=\"$beginyear\">\n";
	$text.= "<input type=\"hidden\" name=\"endyear\" value=\"$endyear\">\n";
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("Calculate VAT report for month");
	$text.= "<td>$l: &nbsp;</td>\n";
	$text.= "<td>\n";
	$text.=PrintMonthSelect($beginmonth, 'beginmonth');
	$text.= "&nbsp;</td>\n";
	$text.= "<td> &nbsp; </td>\n";	/* just to create small space */
	$text.= "<td>\n";
	$text.=PrintMonthSelect($endmonth, 'endmonth');
	$text.= "</td><td>\n";
	$text.=PrintYearSelect($beginyear);
	$text.= "</td>\n";
	$text.= "<td>\n";
	$l = _("Execute");
	$text.= "&nbsp;&nbsp;<input  class=\"btnaction\" type=\"submit\" value=\"$l\"></td></tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
	//print "<br>\n";
	
	//print "</div>\n";
	createForm($text, $haeder,'',750,'','',1,getHelp());
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
	$haeder= "$l: $bm - $em";
	$text.= "<form action=\"?module=vatrep&amp;step=2\" method=\"post\">\n";
	$text.= "<table dir=\"ltr\" border=\"0\" class=\"formtbl\">\n";
	$text.= "<tr><td colspan=\"3\" align=center>\n";
	$text.= "<input type=\"hidden\" name=\"beginmonth\" value=\"$beginmonth\" />\n";
	$text.= "<input type=\"hidden\" name=\"endmonth\" value=\"$endmonth\" />\n";
	$text.= "<input type=\"hidden\" name=\"begindate\" value=\"$begindate\" />\n";
	$text.= "<input type=\"hidden\" name=\"enddate\" value=\"$enddate\" />\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td align=\"center\">\n";
	//print "bla<br>\n";
	$novatincome = GetSumForAcctType(INCOME, $begin, $end, 0);
	$novatincome = round($novatincome, 0);
	$text.=_("VAT exempt transactions")."<br />";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"novatincome\" value=\"$novatincome\">\n";
	$text.= "</td><td dir=\"rtl\" align=\"center\">\n";
	$text.= _("Sales without VAT")."<br />\n";
	$vatincome = GetSumForAcctType(INCOME, $begin, $end, 1);
	$vatincome = round($vatincome, 0);
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"vatincome\" value=\"$vatincome\">\n";
	$text.= "</td><td dir=\"rtl\" align=\"center\">\n";
	$text.= _("Sales VAT")."<br />";
	$sellvat = round(GetSumForAcct(SELLVAT, $begin, $end), 0);
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"sellvat\" value=\"$sellvat\">\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	$text.= "<td dir=\"rtl\" align=\"center\">\n";
	$text.= _("VAT paid for Inputs and assets")."<br />\n";
	$assetvat = round(GetSumForAcct(ASSETVAT, $begin, $end), 0);
	if($assetvat < 0)
		$assetvat *= -1.0;
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"assetvat\" value=\"$assetvat\">\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	$text.= "<td dir=\"rtl\" align=\"center\">\n";
	$text.= _("VAT paid for other input")."<br />\n";
	$buyvat = round(GetSumForAcct(BUYVAT, $begin, $end), 0);
	if($buyvat < 0)
		$buyvat *= -1.0;
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"buyvat\" value=\"$buyvat\">\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	$text.= "<td dir=\"rtl\" align=\"center\">\n";
	$text.= _("Sum to pay")."<br />\n";
	$payvat = $sellvat - $assetvat - $buyvat;
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"payvat\" value=\"$payvat\">\n";
	$text.= "</td></tr>\n";
	$text.= "<tr><td colspan=\"3\" align=\"center\"><input  class=\"btnaction\" type=\"submit\" value=\""._("register")."\"></td></tr>\n"; 
	$text.= "</table>\n"; 
	$text.= "</form>\n";
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
	//$haeder "<br><h1>׳“׳•\"׳— ׳�׳¢\"׳� ׳�׳×׳§׳•׳₪׳”: $bm - $em</h1>\n";
	$haeder = _("VAT report for period").": $bm - $em</h1>\n";
	/* Now the real thing, register transactions... */
	list($day1, $month1, $year1) = split("[/.-]", $begindate);
	list($day2, $month2, $year2) = split("[/.-]", $enddate);
	$ref1 = "$month1$year1";
	$ref2 = "$month2$year2";
	$date = date('d-m-Y');
	//$date = $enddate;	/* register transactions on last date of report */
	/* first check if we already have transactions */
	$t = VAT;
	$query = "SELECT num FROM $transactionstbl WHERE type='$t' AND refnum1='$ref1' AND refnum2='$ref2' AND prefix='$prefix'";
	$result = DoQuery($query, "vatrep.php");
	$num = mysql_num_rows($result);
	if($num == 0) {
		$s = $sellvat * -1.0;
		$tnum = Transaction(0, VAT, SELLVAT, $ref1, $ref2, $date, _('VAT'), $s);
		$tnum = Transaction($tnum, VAT, PAYVAT, $ref1, $ref2, $date, _('VAT'), $sellvat);
		$b = $buyvat * -1.0;
		$tnum = Transaction(0, VAT, BUYVAT, $ref1, $ref2, $date, _('VAT'), $buyvat);
		$tnum = Transaction($tnum, VAT, PAYVAT, $ref1, $ref2, $date, _('VAT'), $b);
		$a = $assetvat * -1.0;
		$tnum = Transaction(0, VAT, ASSETVAT, $ref1, $ref2, $date, _('VAT'), $assetvat);
		$tnum = Transaction($tnum, VAT, PAYVAT, $ref1, $ref2, $date, _('VAT'), $a);
	}

	$text.= "<table dir=\"ltr\" class=\"formtbl\" border=\"0\"><tr>\n";
	$text.= "<td align=\"center\">\n";
	$text.= _("VAT exempt transactions")."<br />\n";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"novatincome\" value=\"$novatincome\" />\n";
	$text.= "</td><td dir=\"rtl\" align=\"center\">\n";
	$text.= _("Sales without VAT")."<br />\n";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"vatincome\" value=\"$vatincome\">\n";
	$text.= "</td><td dir=\"rtl\" align=\"center\">\n";
	$text.= _("Sales VAT")."<br>";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"sellvat\" value=\"$sellvat\">\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	$text.= "<td dir=\"rtl\" align=\"center\">\n";
	$text.= _("VAT paid for Inputs and assets")."<br />\n";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"assetvat\" value=\"$assetvat\">\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	$text.= "<td dir=\"rtl\" align=\"center\">\n";
	$text.= _("VAT paid for other input")."<br />\n";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"buyvat\" value=\"$buyvat\">\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td>\n";		/* space column */
	$text.= "<td dir=\"rtl\" align=\"center\">\n";
	$text.= _("Sum to pay")."<br />\n";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"payvat\" value=\"$payvat\">\n";
	$text.= "</td></tr>\n";
	$text.= "<tr><td colspan=\"3\" dir=\"rtl\" align=\"center\">\n";
	$text.= "<table dir=\"rtl\" border=\"0\"><tr><td>\n";
	$text.= "<form action=\"?module=payment&step=1&opt=vat\" method=\"post\">\n";
	$vatacc = PAYVAT;
	$text.= "<input type=\"hidden\" name=\"account\" value=\"$vatacc\">\n";
	$text.= "<input type=\"hidden\" name=\"refnum\" value=\"$ref1-$ref2\">\n";
	$text.= "<input type=\"hidden\" name=\"total\" value=\"$payvat\">\n";
	$text.= "<input  class=\"btnaction\" type=\"submit\" value=\""._("pay")."\">\n";
	$text.= "</form>\n";
	$text.= "</td></tr></table>\n";
	$text.= "</tr>\n";
	$text.= "</table>\n";
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
	$haeder= _("View VAT Transactions");
	//$text.= "<div class=\"righthalf2\">\n";
	$text.= "<form name=\"vattran\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"vatrep\">\n";
	$text.= "<input type=\"hidden\" name=\"step\" value=\"3\">\n";
	$text.= "<br>"._("begin").": \n";
	$text.= "<input class=\"date\" type=\"text\" name=\"begin\" id=\"begin\" size=\"7\" value=\"$bdate\" />\n";

	$text.= _("end").": ";
	$text.= "<input class=\"date\" type=\"text\" name=\"end\" id=\"end\" size=\"7\" value=\"$edate\" />\n";

	$text.= "<input  class=\"btnaction\" type=\"submit\" value=\"submit\">\n";
	$text.= "</form>\n";
	$text.= "<br><br>\n";
	//$text.= "<h2>";
	$text.= "<a href=\"?module=acctdisp&account=1&begin=$bdate&end=$edate\">מע\"מ תשומות</a>\n";
	$total = GetAcctTotal(1, $begin, $end);
	$text.= "<span dir=\"ltr\">$total</span>";
	$text.= "&nbsp;&nbsp;&nbsp;&nbsp;\n";
	$text.= "<a href=\"?module=acctdisp&account=3&begin=$bdate&end=$edate\">מע\"מ עסקאות</a>\n";
	$total = GetAcctTotal(3, $begin, $end);
	$text.= "<span dir=\"ltr\">$total</span>";
	//$text.= "</h2>\n";
	//print "</div>\n";
	
}
createForm($text, $haeder,'',750,'','',1,getHelp());
?>

