<?PHP
//M:פריטים
/*
 | items
 | This module is part of Drorit free accounting system
 | Written by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl, $currencytbl;
global $EvenLine;

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

	if($num) {
		$query = "SELECT * FROM $itemstbl WHERE num='$num' AND prefix='$prefix'";
		$result = DoQuery($query, "items.php");
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$account = $line['account'];
		$itemname = $line['name'];
		$defprice = $line['defprice'];
		$excatnum = $line['excatnum'];
		$cur_num = $line['currency'];
		$l = _("Edit item");
		print "<h3>$l</h3>";
		print "<table align=\"left\" cellpadding=\"5\"><tr><td>\n";
		print "<form action=\"?module=items&amp;action=updateitem&amp;num=$num\" method=\"post\">\n";
	}
	else {
		$l = _("New item");
		print "<h3>$l</h3>";
		print "<form action=\"?module=items&amp;action=additem\" method=\"post\">\n";
	}
	print "<table border=\"0\" cellpadding=\"5px\" class=\"formtbl\" width=\"100%\">\n";
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
	if($num) {
		print "</td><td>\n";
		print "&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign=\"top\">\n";	/* spacing column */
		ShowText('edititem');
		print "</td></tr>\n";
		print "</table>\n";
	}
}

$l = _("Items for business documents");
print "<br><h2><a href=\"#list\">$l</a></h2>\n";

$action = isset($_GET['action']) ? $_GET['action'] : '';
$num = isset($_GET['num']) ? $_GET['num'] : 0;
$begindmy = isset($_COOKIE['begin']) ? $_COOKIE['begin'] : date("1-1-Y");
$enddmy = isset($_COOKIE['end']) ? $_COOKIE['end'] : date("d-m-Y");
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : $begindmy;
$enddmy = isset($_GET['end']) ? $_GET['end'] : $enddmy;

print "<div class=\"form righthalf1\">\n";
if($action == 'edit') {
	EditItem($num);
	return;
}
if($action == 'additem') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$itemname = GetPost('itemname');
	$account = GetPost('income');
	$defprice = (double)$_POST['defprice'];
	$excatnum = GetPost('excatnum');
	$currency = GetPost('currency');
	
	$query = "INSERT INTO $itemstbl (prefix, account, name, extcatnum, defprice, currency) ";
	$query .= "VALUES('$prefix', '$account', '$itemname', '$excatnum', '$defprice', '$currency')";
	DoQuery($query, "items.php");
}
else if($action == 'updateitem') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$itemname = GetPost('itemname');
	$account = GetPost('income');
	$defprice = (double)$_POST['defprice'];
	$excatnum = GetPost('excatnum');
	$currency = GetPost('currency');
	
	$query = "UPDATE $itemstbl SET account='$account', name='$itemname', extcatnum='$excatnum', ";
	$query .= "defprice='$defprice', currency='$currency'";
	$query .= " WHERE num='$num' AND prefix='$prefix'";
	DoQuery($query, "items.php");
}
else if($action == 'del') {
	$query = "DELETE FROM $itemtbl WHERE $num='$num' AND prefix='$prefix'";
	DoQuery($query, "items.php");
}

// print "<table dir=\"rtl\" border=\"0\"><tr><td>\n";
EditItem(0);
$l = _("Existing items");
print "<h2>$l</h2>";

$query = "SELECT num,name,account,defprice FROM $itemstbl WHERE prefix='$prefix' ORDER BY name";
$result = DoQuery($query, "items.php");
print "<table dir=\"rtl\" border=\"0\" class=\"hovertbl\" width=\"100%\" style=\"margin-top:5px\">\n";
print "<tr class=\"tblhead\">\n";
$l = _("Item name");
print "<td>$l &nbsp;</td>\n";
$l = _("Income account");
print "<td>$l &nbsp;</td>\n";
$l = _("Unit price");
print "<td>$l &nbsp;</td>\n";
$l = _("Actions");
print "<td>$l &nbsp;</td>\n";
print "</tr>\n";
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$itemname = $line['name'];
	$num = $line['num'];
	$acct = $line['account'];
	$acctname = GetAccountName($acct);
	$defprice = $line['defprice'];
	NewRow();
	$url = "?module=acctdisp&amp;account=$acct&amp;begin=$begindmy&amp;end=$enddmy";
	print "<td>$itemname</td><td><a href=\"$url\">$acctname</a></td><td>$defprice</td>\n";
	$l = _("Edit");
	print "<td><a href=\"?module=items&amp;action=edit&amp;num=$num\">$l</a>&nbsp;&nbsp;\n";
	$l = _("Delete");
	print "<a href=\"?module=items&amp;action=del&amp;num=$num\">$l</a>\n";
	print "</td>\n";
	print "</tr>\n";
}
print "</table>\n";
print "</div>\n";
print "<div class=\"lefthalf1\">\n";
ShowText('items');
print "</div>\n";
?>
