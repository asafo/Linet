<?PHP
/*
 | TAX calculatin script for Freelance accounting system
 | Written by Ori Idan
 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
global $montharr;

$montharr = array(_("January"), _("February"), _("March"), _("April"),
	_("May"), _("June"), _("July"), _("August"), _("September"), 
	_("October"), _("November"), _("December"));

if(!isset($prefix) || ($prefix == '')) {
	ErrorReport(_("This operation can not be executed without choosing a business first"));
	return;
}
$text='';
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
		
function GetSumForAcct($acct, $begin, $end) {
	global $transactionstbl;
	global $prefix;
	
//	print "Calculating sum for $acct ($begin - $end)<BR>\n";
	$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' ";
	if($begin == 0)
		$query .= "AND date<='$end' AND prefix='$prefix'";
	else
		$query .= "AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br />\n";
	$result = DoQuery($query, "GetSumForAcct");	/* get accounts numbers */
	$sum = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$sum += $line[0];
	}
//	print "Sum: $sum<BR>\n";
	return $sum;
}

function GetSumForAcctType($acct_type, $begin, $end) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT num FROM $accountstbl WHERE type='$acct_type' AND prefix='$prefix'";
	$result = mysql_query($query);	/* get accounts numbers */
	if(!$result) {
		echo mysql_error();
		exit;
	}
	$sum = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
		$sum += GetSumForAcct($num, $begin, $end);
	}
	return $sum;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 0) {	/* print date select form */
	$today = date('d-m-Y');
	list($day, $month, $year) = split("-", $today);

	/* Check if we report each month or two month */
	//$query = "SELECT taxrep FROM $companiestbl WHERE prefix='$prefix'";
	//$result = DoQuery($query, "vatrep.php");
	//$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$taxrep = $line['taxrep'];
	
	$taxrep = $_SESSION['company']->taxrep;
	if($taxrep == 2) {
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
	
	//print "<div class=\"righthalf2\">\n";
	//$text.= "<div class=\"caption_out\"><div class=\"caption\">";
	$haeder= "מקדמות מס הכנסה";
	//$text.= "</div></div>\n";
	$text.= "<form action=\"?module=taxrep&step=1\" method=\"post\">\n";
	$text.= "<input type=\"hidden\" name=\"beginyear\" value=\"$beginyear\">\n";
	$text.= "<input type=\"hidden\" name=\"endyear\" value=\"$endyear\">\n";
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$text.= "<td>צור דוח מקדמות מס לחודשים: &nbsp;</td>\n";
	$text.= "<td>\n";
	$text.=PrintMonthSelect($beginmonth, 'beginmonth');
	$text.= "&nbsp;</td>\n";
	$text.= "<td> &nbsp; </td>\n";	/* just to create small space */
	$text.= "<td>\n";
	$text.=PrintMonthSelect($endmonth, 'endmonth');
	$text.= "</td>\n";
	$text.= "<td>\n";
	$text.= "&nbsp;&nbsp;<input type=\"submit\" value=\"בצע\"></td></tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
	$text.= "<br>\n";
	//print "</div>\n";
	createForm($text, $haeder,'',750,'','',1,getHelp());
	return;
}
	$beginmonth = GetPost('beginmonth');
	$endmonth = GetPost('endmonth');
	$beginyear = GetPost('beginyear');
	$endyear = GetPost('endyear');
	$begindate = "1-$beginmonth-$beginyear";
	$d = GetLastDayOfMonth($endmonth, $endyear);
	$enddate = "$d-$endmonth-$endyear";
//	print "$begindate - $enddate<br>\n";
	$begin = FormatDate($begindate, "dmy", "mysql");
	$end = FormatDate($enddate, "dmy", "mysql");
	
	$bm = $montharr[$beginmonth - 1];
	$em = $montharr[$endmonth - 1];
$header="דו\"ח מקדמות מס לתקופה: $bm - $em";
if($step == 1) {

	//print 
	
	//print "<div class=\"righthalf2\">\n";
	$text.= "<form action=\"?module=taxrep&step=2\" method=\"post\">\n";
	$text.= "<table dir=\"ltr\" border=\"0\" cellpadding=\"10\"><tr>\n";
	$text.= "<input type=\"hidden\" name=\"beginmonth\" value=\"$beginmonth\">\n";
	$text.= "<input type=\"hidden\" name=\"endmonth\" value=\"$endmonth\">\n";
	$text.= "<input type=\"hidden\" name=\"begindate\" value=\"$begindate\">\n";
	$text.= "<input type=\"hidden\" name=\"enddate\" value=\"$enddate\">\n";
	$text.= "<td align=\"center\">\n";
	$income = round(GetSumForAcctType(INCOME, $begin, $end), 0);
	$text.= "המחזור העסקי בש\"ח<br>";
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"income\" value=\"$income\">\n";
	$text.= "</td><td style=\"width:7em\">\n";
	$query = "SELECT tax FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "taxrep.php");
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$taxpercent = $line['tax'];
	$text.= "<br> &nbsp;X";
	$text.= "<input type=\"text\" readonly name=\"taxpercent\" value=\"$taxpercent\" size=\"4\">%\n";
	$text.= "</td><td align=\"center\">\n";
	$text.= "מקדמה ע\"פ % מהמחזור העסקי<br>\n";//"׳�׳§׳“׳�׳” ׳¢\"׳₪ % ׳�׳”׳�׳—׳–׳•׳¨ ׳”׳¢׳¡׳§׳™<br>\n";
	$tax = round($income * $taxpercent / 100.0, 0);
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"tax\" value=\"$tax\">\n";
	$text.= "</td></tr>\n";
	$text.= "<td align=\"center\">\n";
	$text.= "ניכוי במקור מלקוחות לתקופה<br>\n";//"׳ ׳™׳›׳•׳™ ׳‘׳�׳§׳•׳¨ ׳�׳�׳§׳•׳—׳•׳× ׳�׳×׳§׳•׳₪׳”<br>";
	$custtax = GetSumForAcct(CUSTTAX, $begin, $end) * -1.0;
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"custtaxtotal\" value=\"$custtax\">\n";
	$text.= "</td><td>\n";
	$text.= "&nbsp;\n";		/* spacer column */
	$text.= "</td><td align=\"center\">\n";
	$text.= "ניכויים במקור לקיזוז<br />\n";;
	$custtax = GetSumForAcct(CUSTTAX, 0, $end) * -1.0;
	if($custtax > $tax)
		$custtax = $tax;
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"custtax\" value=\"$custtax\">\n";
	
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">&nbsp;</td><td align=\"center\">\n";
	$text.= "סה\"כ לתשלום"."<br />";
	$taxtopay = $tax - $custtax;
	$text.= "<input dir=\"ltr\" type=\"text\" readonly name=\"taxtopay\" value=\"$taxtopay\">\n";
	$text.= "</tr><tr><td colspan=\"3\" align=\"center\">\n";
	$text.= "<input type=\"submit\" value=\""."רשום"."\"></td></tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
}
if($step == 2) {
	/*$begindate = $_POST['begindate'];
	$enddate = $_POST['enddate'];
	$beginmonth = $_POST['beginmonth'];
	$endmonth = $_POST['endmonth'];
	$income = $_POST['income'];
	$taxpercent = $_POST['taxpercent'];
	$tax = $_POST['tax'];
	$custtaxtotal = $_POST['custtaxtotal'];
	$custtax = $_POST['custtax'];
	$taxtopay = $_POST['taxtopay'];
	
	$bm = $montharr[$beginmonth - 1];
	$em = $montharr[$endmonth - 1];*/
	//$text.= "<br><h1>׳�׳§׳“׳�׳•׳× ׳�׳¡ ׳”׳›׳ ׳¡׳” ׳�׳×׳§׳•׳₪׳”: $bm - $em</h1>\n";

	// Now the real thing, register transactions */
		list($day1, $month1, $year1) = split("[/.-]", $begindate);
	list($day2, $month2, $year2) = split("[/.-]", $enddate);
	$ref1 = "$month1$year1";
	$ref2 = "$month2$year2";
//	$date = date('d-m-Y');
	$date = $enddate;	/* register transactions on last date of report */
	/* first check if we already have transactions */
	$t = TRAN_PRETAX;
	$query = "SELECT num FROM $transactionstbl WHERE type='$t' AND refnum1='$ref1' AND refnum2='$ref2' AND prefix='$prefix'";
	// Transaction 1 ׳–׳›׳•׳× ׳�׳¡ ׳”׳›׳ ׳¡׳” ׳—׳•׳‘׳× ׳�׳§׳“׳�׳•׳× ׳�׳¡ ׳”׳›׳ ׳¡׳”
	$tnum = Transaction(0, TRAN_PRETAX, TAX, $ref1, $ref2, $date, $tax);
	$tnum = Transaction($tnum, TRAN_PRETAX, PRETAX, $ref1, $ref2, $date, $tax * -1.0);
	// Transaction 2 ׳–׳›׳•׳× ׳ ׳™׳›׳•׳™ ׳‘׳�׳§׳•׳¨ ׳�׳�׳§׳•׳—׳•׳×, ׳—׳•׳‘׳× ׳�׳¡ ׳”׳›׳ ׳¡׳”
	$tnum = Transaction($tnum, TRAN_PRETAX, TAX, $ref1, $ref2, $date, $custtax);
	$tnum = Transaction($tnum, TRAN_PRETAX, CUSTTAX, $ref1, $ref2, $date, $custtax * -1.0);
}
createForm($text, $header,'',750,'','',1,getHelp());
?>

