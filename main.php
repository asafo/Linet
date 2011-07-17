<?PHP
//M:ראשי
/*
 | Drorit accounting system main page
 */
global $prefix, $dir, $lang;
global $logintbl, $companiestbl;
global $permissionstbl;
global $transactionstbl;
global $accountstbl;
global $stattbl, $modnames;
global $itemstbl;
global $superuser;

if($lang == 'he')
	$align = 'right';
else
	$align = 'left';

$action = isset($_GET['action']) ? $_GET['action'] : '';
if($action == 'delcomp') {
	$p = $_GET['company'];
//	print "Transactions <br>\n";
	$query = "DELETE FROM $transactionstbl WHERE prefix='$p'";
	DoQuery($query, "main");
//	print "accounts<br>\n";
	$query = "DELETE FROM $accountstbl WHERE prefix='$p'";
	DoQuery($query, "main");
	$query = "DELETE FROM $itemstbl WHERE prefix='$p'";
	DoQuery($query, "main");
	$query = "DELETE FROM $companiestbl WHERE prefix='$p'";
	DoQuery($query, "main");
}

if(!isset($prefix) || ($prefix == '')) {	/* Display list of companies */
	$query = "SELECT company FROM $permissionstbl WHERE name='$name'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "main.php");
	$n = mysql_num_rows($result);
/*	if($n == 1) {
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$prefix = $line[0];
		$company = $line[0];
		print "prefix: $prefix<br>\n";
	} */
	if($n == 0) {
		print "<br>\n";
		$l = _("No companies for this user");
		print "<h1>$l</h1>\n";
		return;
	}
	if($n >= 1) {
		$line = mysql_fetch_array($result, MYSQL_NUM);
		if($line[0] == '*') {
			$query = "SELECT prefix FROM $companiestbl";
			$result = DoQuery($query, "compass.php");
			$n = mysql_num_rows($result);
			// print "n: $n<br>\n";
		}
		print "<br>\n";
		print "<div class=\"righthalf1\">\n";
		$l = _("Choose business to work on");
		print "<h3>$l</h3>\n";
		print "<div style=\"margin-right:10%;font-size:14px\">\n";
		print "<ul>\n";
		while($line = mysql_fetch_array($result, MYSQL_NUM)) {
			$s = $line[0];
			// print "prefix: $s<br>\n";
			$query = "SELECT companyname FROM $companiestbl WHERE prefix='$s'";
			$r = DoQuery($query, "compass.php");
			$line = mysql_fetch_array($r, MYSQL_NUM);
			$n = $line[0];
			$cookietime = time() + 60*60*24*30;
			$url = "index.php?cookie=company,$s,$cookietime&amp;company=$s";
			print "<li><a href=\"$url\">$n</a>&nbsp;\n";
			if($name == 'admin') {
				$l = _("Delete");
				print "<a href=\"?module=main&amp;action=delcomp&amp;company=$s\">$l</a>";
			}
			print "</li>\n";
		}
		print "</ul>\n";
		if($superuser) {
			$l = _("Add new business");
//			print "<br><br><a href=\"?module=defs\">הגדרת חברה חדשה</a><br>\n";
			print "<br><br><a href=\"?module=defs\">$l</a><br>\n";
		}
		print "</div>\n";
		print "</div>\n";
		print "<div class=\"lefthalf1\">\n";
		ShowText('compselect');
		print "</div>\n";
		return;
	}
}

function GetAcctTotal($acct, $begin, $end) {
	global $transactionstbl, $prefix;
	
	if($begin != 0)
		$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
	else 
		$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date<='$end' AND prefix='$prefix'";
//	print "query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetGroupTotal($grp, $begin, $end) {
	global $accountstbl, $prefix;

	$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$grp'";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	list($y, $m, $d) = explode('-', $begin);
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
		$sub_total = GetAcctTotal($num, $begin, $end);
		$total += $sub_total;
	}
	return $total;
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function CreateProfitGraph($income, $outcome, $profit) {
	$data1 = array($income);
	if($outcome < 0)
		$outcome *= -1.0;
	$data2 = array($outcome);
	$label = array("");
	$bar_width = 30;
	$fname = "profit.png";
	require('dbarchart.php');
}

print "<div class=\"dateform\">\n";
print "<table dir=\"$dir\" class=\"formtbl\" width=\"100%\"><tr>\n";
print "<td>\n";	

/******************* user name data *********************
$name1 = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];
$name1 = urldecode($name1);
$query = "SELECT fullname FROM $logintbl WHERE name='$name1'";
$l = __LINE__;
$result = DoQuery($query, "main.php $l");
$line = mysql_fetch_array($result, MYSQL_NUM);
$username = stripslashes($line[0]);
print "<br>\n";
print "&nbsp;&nbsp;<span style=\"color:#0000FF;font-size:14;font-weight:bold\">$username </span>";
if($superuser) {
	$l = _("Choose business");
	print " &nbsp;| <a href=\"index.php?action=unsel\">$l</a>\n";
	$l = _("Content management");
	print "&nbsp;&nbsp;| <a href=\"index.php?module=edit\">$l</a>\n";
}
$l = _("Logout");
print " | <a href=\"?action=disconnect\">$l</a>\n";
***************** end user name data *******************/

print "</td>\n";
print "<td valign=\"middle\">\n";	
/****************** dates form ***************************/
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : date("1-1-Y");
$enddmy = isset($_GET['end']) ? $_GET['end'] : date("d-m-Y");
print "<form action=\"\" name=\"main\" method=\"get\">\n";
print "<input type=\"hidden\" name=\"module\" value=\"main\">\n";
print "<br><table dir=\"$dir\" style=\"height:100%\"><tr>\n";
$l = _("Begin date");
print "<td>&nbsp;&nbsp;$l: </td>\n";
print "<td><input type=\"text\" id=\"begin\" name=\"begin\" value=\"$begindmy\" size=\"7\">\n";

print "&nbsp;&nbsp;</td>\n";
$l = _("End date");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" id=\"end\" name=\"end\" value=\"$enddmy\" size=\"7\">\n";
?>
<script type="text/javascript">
	addDatePicker("#begin","<?print $begindmy; ?>");
	addDatePicker("#end","<?print $enddmy; ?>");
	
</script>
<?PHP
print "&nbsp;&nbsp;</td><td><input type=\"submit\" value=\"בצע\"></td>\n";
print "</tr></table>\n";
print "</form>\n";
print "</td></tr></table>\n";
print "</div>\n";
?>


<?PHP
$haeder = _("Business details (NIS)");
//print "<br />$l\n";
$begin = FormatDate($begindmy, "dmy", "mysql");
$end = FormatDate($enddmy, "dmy", "mysql");
$income = GetGroupTotal(INCOME, $begin, $end);
$outcome = GetGroupTotal(OUTCOME, $begin, $end);
$text='';
$text.= "<table class=\"hovertbl1\">\n";
//NewRow();
$n = number_format($income);
$l = _("Total income");
$text.= "<tr><td style=\"width:7.5em;font-weigh:normal;font-size:14px;\"><a href=\"?module=acctadmin&amp;type=3&amp;option=rep\">$l</a></td>";
$text.= "<td style=\"color:black;font-weight:normal;font-size:14px;\" >$n</td></tr>\n";
//NewRow();
$o = $outcome * -1.0;
$n = number_format($o);
$l = _("Total outcome");
$text.= "<tr><td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctadmin&amp;type=2&amp;option=rep\">$l</a></td>";
$text.= "<td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td></tr>\n";
//NewRow();
$profit = $income + $outcome;
$url = "?module=profloss&amp;step=1&amp;begindate=$begindmy&amp;enddate=$enddmy";
if($profit >= 0.0) {
	$l = _("Total profit");
	$text.= "<tr><td style=\"font-weight:normal;font-size:14px;\"><a href=\"$url\">$l</a></td>";
}
else {
	$l = _("Total loss");
	$text.= "<tr><td style=\"font-weight:normal;font-size:14px;\"><a href=\"$url\">$l</a></td>";
}
$n = number_format(abs($profit));
$text.= "<td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
$text.= "</tr>"; 
//NewRow();
$text.="<tr>";
$t = GetGroupTotal(CUSTOMER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
$l = _("Total customers");
$text.= "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctadmin&amp;type=0&amp;option=rep\">$l</a></td><td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
$text.= "</tr>\n";
NewRow();
$t = GetGroupTotal(SUPPLIER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
$l = _("Total suppliers");
$text.= "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctadmin&amp;type=1&amp;option=rep\">$l</a></td><td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
$text.= "</tr></table>\n";

CreateProfitGraph($income, $outcome, $profit);
$text.= "<br>\n";
$text.= "<img src=\"tmp/profit.png\" alt=\"graph\" style=\"margin-right:10px\">\n";
createForm($text,$haeder,"maindiv",210);
?>
		
<?PHP
$haeder = _("Short cuts");
//print "<h3 style=\"text-align:$align\">$l</h3>\n";
$l = _("Invoice");
$text='';
$text.= "<div class=\"shortcut\"><a href=\"?module=docsadmin&amp;targetdoc=3\">$l</a></div>\n";
$l = _("Receipt");
$text.= "<div class=\"shortcut\"><a href=\"?module=receipt\">$l</a></div>\n";
$l = _("Deposit");
$text.= "<div class=\"shortcut\"><a href=\"?module=deposit\">$l</a></div>\n<br />";
$l = _("Outcome");
$text.= "<div class=\"shortcut\"><a href=\"?module=outcome\">$l</a></div>\n";
$l = _("Payment");
$text.= "<div class=\"shortcut\"><a href=\"?module=payment\">$l</a></div>\n";
// print "<div class=\"emptyshortcut\">&nbsp;</div>\n";
$l = _("Contacts");
$text.= "<div class=\"shortcut\"><a href=\"?module=contact\">$l</a></div>\n";
$text.="<div class=\"sysmsg\">".ShowText('sysmsg',false)."</div>";
createForm($text,$haeder,"shortsdiv",345);
?>



<link rel="stylesheet" type="text/css" href="style/mcalendar.css" >
<?PHP
$haeder = _("Events according to date");

require('calendar.php');
createForm($text,$haeder,"caldiv",220);
?>

