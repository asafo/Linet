<?PHP
/*
 | Add item
 | Add a new item script. to be called from business document script of linet accounting system
 | Written by: Ori Idan September 2009
 | Changed by: Adam Ben Hour 2011 
 */
header('Content-type: text/html;charset=UTF-8');
include('i18n.inc.php');

$title = _("Add item");
$header = <<<HD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/drorit.css" />
<link rel="stylesheet" type="text/css" href="/yawiki.css" />
<style type="text/css">
.top {
	text-align:center;
}
.contents {
	border:1px solid;
	width:90%;
	margin:5px;
}
</style>
<title>$l</title>
</head>
<body dir="rtl">
HD;

include('config.inc.php');
include('linet.inc.php');
include('func.inc.php');

$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");//adam:
mysql_select_db($database) or die("Could not select database: $database");

global $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl;

print "$header\n";

$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
$cat_num = isset($_GET['num']) ? $_GET['num'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$index = isset($_GET['index']) ? $_GET['index'] : 0;

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function PrintIncomeSelect($def) {
	global $accountstbl;
	global $prefix;
	
	$t = INCOME;
	$query = "SELECT num,company,src_tax FROM $accountstbl WHERE prefix='$prefix' AND type='$t'  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	print "<select name=\"income\">\n";
	$l = _("Choose income account");
	print "<option value=\"__NULL__\" >-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		$company .= " ";
		$v = $line[2]; 
		if(($v != '') && ($v == 0))
			$company .= _("0% VAT");
//			$company .= " (מע\"מ 0%)";
		else 
			$company .= _("100% VAT");
//			$company .= " (מע\"מ 100%)";
		if($n == $def)
			print "<option value=\"$n\" selected=\"selected\">$company</option>\n";
		else
			print "<option value=\"$n\">$company</option>\n";
	}
	print "</select>\n";
}

function PrintCurrencySelect($defnum) {
	global $currencytbl;
	
	$query = "SELECT * FROM $currencytbl";
	$result = DoQuery($query, __LINE__);
	print "<select id=\"currency\" name=\"currency[]\">\n";
	$l = _("NIS");
	print "<option value=\"0\">$l</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['curnum'];
		$sign = $line['sign'];
		print "<option value=\"$num\"";
		if($num == $defnum)
			print " selected";
		print ">$sign</option>\n";
	}
	print "</select>\n";
}

function EditItem($num) {
	global $prefix, $itemstbl;
	global $index;
	global $dir;

	$l = _("New item");
	print "<h3>$l</h3>";
	print "<form action=\"additem.php?action=additem&index=$index&prefix=$prefix\" method=\"post\">\n";

	print "<table dir=\"$dir\" class=\"formtbl\" width=\"100%\">\n";
	print "<tr>\n";
	$l = _("Income account");
	print "<td>$l: </td>\n";
	print "<td>\n";
	PrintIncomeSelect($account);
	print "</td></tr>\n";
	print "<tr>\n";
	$l = _("Item name");
	print "<td>$l: </td>\n";
	print "<td><input type=\"text\" name=\"itemname\" value=\"$itemname\"></td>\n";
	print "</tr><tr>\n";
	$l = _("Supplier cat. num.");
	print "<td>$l:</td>\n";
	print "<td><input type=\"text\" name=\"excatnum\" value=\"$excatnum\"></td>\n";
	print "</tr><tr>\n";
	$l = _("Unit price");
	print "<td>$l: </td>\n";
	print "<td><input type=\"text\" name=\"defprice\" value=\"$defprice\"></td>\n";
	print "</tr><tr>\n";
	$l = _("Currency");
	print "<td>$l: </td>\n";
	print "<td>\n";
	PrintCurrencySelect($cur_num);
	print "</td>\n";
	print "</tr><tr>\n";
	$l = _("Update");
	print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
	print "</tr>\n";
	print "</table>\n";
	print "</form>\n";

}

if($action == 'additem') {
	$itemname = $_POST['itemname'];
	$account = $_POST['income'];
	$defprice = (double)$_POST['defprice'];
	
	$query = "INSERT INTO $itemstbl (prefix, account, name, defprice) ";
	$query .= "VALUES('$prefix', '$account', '$itemname', '$defprice')";
	DoQuery($query, "items.php");
	$num = mysql_insert_id();
	
	print "<script type=\"text/javascript\">\n";
	print "function closewin() {\n";
	print "\topener.document.form1.cat_numh[$index].value='$num';\n";
	print "\topener.document.form1.description[$index].value='$itemname';\n";
	print "\topener.document.form1.unit_price[$index].value='$defprice';\n";
	print "\twindow.close();\n";
	print "}\n";
	print "</script>\n";
	$l = _("Item number");
	print "<h1>$l: $num</h1>\n";
	$l = _("Description");
	print "<h1>$l: $itemname</h1>\n";
	$l = _("Unit price");
	print "<h1>$l: $defprice</h1>\n";
	print "<br>\n";
	$l = _("Close window");
	print "<input type=\"button\" value=\"$l\" onclick=\"closewin()\">\n";
	print "</body>\n</html>\n";
	exit;
}

EditItem(0);

