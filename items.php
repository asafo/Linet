<?PHP
//M:׳³ג‚×׳³ֲ¨׳³ג„¢׳³ֻ�׳³ג„¢׳³ן¿½
/*
 | items
 | This module is part of Drorit free accounting system
 | Written by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $supdocstbl, $itemstbl, $currencytbl;
//global $EvenLine;

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
	$text.= "<select name=\"income\">\n";
	$l = _("Choose income account");
	$text.= "<option value=\"__NULL__\" >-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		$company .= " ";
		$v = $line[2]; 
		if(($v != '') && ($v == 0))
			$company .= _("0% VAT");
//			$company .= " (׳³ן¿½׳³ֲ¢\"׳³ן¿½ 0%)";
		else 
			$company .= _("100% VAT");
//			$company .= " (׳³ן¿½׳³ֲ¢\"׳³ן¿½ 100%)";
		if($n == $def)
			$text.= "<option value=\"$n\" selected=\"selected\">$company</option>\n";
		else
			$text.= "<option value=\"$n\">$company</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function PrintCurrencySelect($defnum) {
	global $currencytbl;
	
	$query = "SELECT * FROM $currencytbl";
	$result = DoQuery($query, __LINE__);
	$text.= "<select id=\"currency\" name=\"currency\">\n";
	$l = _("NIS");
	$text.= "<option value=\"0\">$l</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['curnum'];
		$sign = $line['sign'];
		$text.= "<option value=\"$num\"";
		if($num == $defnum)
			$text.= " selected";
		$text.= ">$sign</option>\n";
	}
	$text.= "</select>\n";
	return $text;
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
		$text.= "<h3>$l</h3>";
		$text.= "<table align=\"left\" cellpadding=\"5\"><tr><td>\n";
		$text.= "<form name=\"itm\" action=\"?module=items&amp;action=updateitem&amp;num=$num\" method=\"post\">\n";
	}
	else {
		$l = _("New item");
		$text.= "<h3>$l</h3>";
		$text.= "<form name=\"itm\" action=\"?module=items&amp;action=additem\" method=\"post\">\n";
	}
	$text.= "<table border=\"0\" class=\"formtbl\" width=\"100%\">\n";
	$text.= "<tr>\n";
	$l = _("Income account");
	$text.= "<td>$l: </td>\n";
	$text.= "<td>\n";
	$text.=PrintIncomeSelect($account);
	$text.= "</td></tr>\n";
	$text.= "<tr>\n";
	$l = _("Item name");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"itemname\" value=\"".htmlspecialchars($itemname)."\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Supplier cat. num.");
	$text.= "<td>$l:</td>\n";
	$text.= "<td><input type=\"text\" name=\"excatnum\" value=\"".htmlspecialchars($excatnum)."\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Unit price");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"defprice\" value=\"".htmlspecialchars($defprice)."\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Currency");
	$text.= "<td>$l: </td>\n";
	$text.= "<td>\n";
	$text.= PrintCurrencySelect($cur_num);
	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Update");
	$text.= "<td colspan=\"2\" align=\"center\">";
	if (!$smallprint){
		$text.="<a href='javascript:document.itm.submit();' class='btnaction'>$l</a>";
	}else {
		$text.="<a href='javascript:document.itm.submit(); window.close();' class='btnaction'>$l</a>";		
	}
	
	//<input type=\"submit\" value=\"$l\" />";
	$text.= "</td>\n</tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
	if($num) {
		$text.= "</td><td>\n";
		$text.= "&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign=\"top\">\n";	/* spacing column */
		ShowText('edititem');
		$text.= "</td></tr>\n";
		$text.= "</table>\n</div>";
	}
	return $text;
}

$l = _("Items for business documents");
//print "<br /><h2>$l</h2>\n";
$haeder=$l;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$num = isset($_GET['num']) ? $_GET['num'] : 0;
$begindmy = isset($_COOKIE['begin']) ? $_COOKIE['begin'] : date("1-1-Y");
$enddmy = isset($_COOKIE['end']) ? $_COOKIE['end'] : date("d-m-Y");
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : $begindmy;
$enddmy = isset($_GET['end']) ? $_GET['end'] : $enddmy;

//print "<div class=\"form righthalf1\">\n";

if($action == 'edit') {
	$text=EditItem($num);
	createForm($text,$haeder,"",350);
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
	global $curuser;
	$query = "INSERT INTO $itemstbl (prefix, account, name, extcatnum, defprice, currency, owner) ";
	$query .= "VALUES('$prefix', '$account', '$itemname', '$excatnum', '$defprice', '$currency', '$curuser->id')";
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
$text=EditItem(0);
if ($smallprint)
	createForm($text,$haeder,"",350);

if (!$smallprint){
	$l = _("Existing items");
	$text.= "<h2>$l</h2>";
	
	$query = "SELECT num,name,account,defprice FROM $itemstbl WHERE prefix='$prefix' ORDER BY name";
	$result = DoQuery($query, "items.php");
	$text.=  "<table class=\"tablesorter\" width=\"100%\" ><thead>\n";
	$text.=  "<tr>\n";
	$l = _("Item name");
	$text.=  "<th class=\"header\">$l &nbsp;</th>\n";
	$l = _("Income account");
	$text.=  "<th class=\"header\">$l &nbsp;</th>\n";
	$l = _("Unit price");
	$text.=  "<th class=\"header\">$l &nbsp;</th>\n";
	$l = _("Actions");
	$text.=  "<th class=\"header\">$l &nbsp;</th>\n";
	$text.=  "</tr></thead><tbody>\n";
	
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$itemname = $line['name'];
		$num = $line['num'];
		$acct = $line['account'];
		$acctname = GetAccountName($acct);
		$defprice = $line['defprice'];
		//NewRow();
		$url = "?module=acctdisp&amp;account=$acct&amp;begin=$begindmy&amp;end=$enddmy";
		$text.=  "<tr><td>$itemname</td><td><a href=\"$url\">$acctname</a></td><td>$defprice</td>\n";
		$l = _("Edit");
		$text.=  "<td><a href=\"?module=items&amp;action=edit&amp;num=$num\" class=\"btnedit\" ></a>&nbsp;&nbsp;\n";
		$l = _("Delete");
		$text.=  "<a href=\"?module=items&amp;action=del&amp;num=$num\" class=\"btnremove\"></a>\n";
		$text.=  "</td>\n";
		$text.=  "</tr>\n";
	}
	$text.=  "</tbody></table>\n";
	
	//$text.=  "</div>\n";
	//print "<div class=\"lefthalf1\">\n";
	//ShowText('items');
	//print "</div>\n";
	createForm($text,$haeder,"",750);
}
?>
