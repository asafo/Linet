<?PHP
//M:׳³ֲ¨׳³ן¿½׳³ֲ©׳³ג„¢
/*
 | Linet accounting system main page
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
/*
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
}*/

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
		print "<br />\n";
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
		print "<br />\n";
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
//			print "<br><br><a href=\"?module=defs\">׳³ג€�׳³ג€™׳³ג€�׳³ֲ¨׳³ֳ— ׳³ג€”׳³ג€˜׳³ֲ¨׳³ג€� ׳³ג€”׳³ג€�׳³ֲ©׳³ג€�</a><br>\n";
			print "<br /><br /><a href=\"?module=defs\">$l</a><br />\n";
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




$haeder = _("Business details (NIS)");
//print "<br />$l\n";
$begin = FormatDate($begindmy, "dmy", "mysql");
$end = FormatDate($enddmy, "dmy", "mysql");
$income = GetGroupTotal(INCOME, $begin, $end);
$outcome = GetGroupTotal(OUTCOME, $begin, $end);
$text='';


/****************** dates form ***************************/
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : date("1-1-Y");
$enddmy = isset($_GET['end']) ? $_GET['end'] : date("d-m-Y");
$text.= "<form action=\"\" name=\"main\" method=\"get\">\n";
$text.= "<input type=\"hidden\" name=\"module\" value=\"main\" />\n";
//print "<br /><table dir=\"$dir\" style=\"height:100%\"><tr>\n";
$l = _("Begin date");
$text.= "<table><tr><td>$l: \n";
$text.= "<input type=\"text\" id=\"begin\" name=\"begin\" value=\"$begindmy\" size=\"7\" />\n";

$text.= "</td><td>\n";
$l = _("End date");
$text.= "$l: \n";
$text.= "<input type=\"text\" id=\"end\" name=\"end\" value=\"$enddmy\" size=\"7\" />\n";
$text.='<script type="text/javascript">	addDatePicker("#begin","'.$begindmy.'");addDatePicker("#end","'.$enddmy.'");</script>';
//print "&nbsp;&nbsp;<input type=\"submit\" value=\"׳³ג€˜׳³ֲ¦׳³ֲ¢\" />\n";
$l=_('Go');
$text.= "</td></tr></table><a href='javascript:document.main.submit();' class='btn'>$l</a>";
//print "</tr></table>\n";
$text.= "</form>\n";



$text.= "<table class=\"hovertbl1\" style=\"float:right\">\n";
//NewRow();
$n = number_format($income);
$l = _("Total income");
$text.= "<tr><td style=\"width:7.5em;font-weigh:normal;font-size:14px;\" ><a href=\"?module=acctadmin&amp;type=3&amp;option=rep\">$l</a></td>";
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
$text.='<tr>';
$t = GetGroupTotal(SUPPLIER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
$l = _("Total suppliers");
$text.= "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctadmin&amp;type=1&amp;option=rep\">$l</a></td><td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
$text.= "</tr></table>\n";

CreateProfitGraph($income, $outcome, $profit);
//$text.= "<br />\n";
$text.= "<img src=\"tmp/profit.png\" alt=\"graph\" style=\"margin-right:10px; float:left;\" />\n";
createForm($text,$haeder,"maindiv",460,null,'img/icon_detiales.png');

$haeder = _("Events according to date");

require('calendar.php');
createForm($text,$haeder,"caldiv",280,null,'img/icon_cel.png');
?>

