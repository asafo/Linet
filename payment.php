<?PHP
/*
 | Supplier payment module for Freelance accounting software
 | Written by Ori Idan for Shay Harel
 | Modifeid By Adam BH 10/2011
 */
?>
<script type="text/javascript">
function CalcSum() {
	var str = document.payment.supplier.value;
	
/*	alert(str); */
	arr = str.split(":");
	document.payment.total.value = arr[1];
}

function TypeSelChange() {
	var i = document.payment.payment.selectedIndex;
	
	if(i == 3) {
		document.getElementById('crd').style.display = 'block';
	}
	else {
		document.getElementById('crd').style.display = 'none';
	}
}

</script>
<?PHP
//print "here";
$text='';
global $prefix, $accountstbl, $supdocstbl;
global $payemnttype;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	ErrorReport("$l");
	//print "<h1>$l</h1>\n";
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

function PrintPaymentSelect($def) {
	global $accountstbl;
	global $prefix;

	$str= "<select name=\"payment\">\n";
	$l = _("Payment type");
	$str.= "<option value=\"0\">-- $l --</option>\n";
	
	$banks = BANKS;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$banks' AND prefix='$prefix'"; // banks accounts
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		if($num > 100) {
			$acct = $line['company'];
			$str.= "<option value=\"$num\" ";
			if($num == $def)
				$str.= "selected";
			$str.= ">$acct</option>\n";
		}
	}
	$str.= "</select>\n";
	return  $str;
}

function GetAcctTotal($account, $dt) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<='$dt' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	if($total < 0.01)
		return 0;
	return $total;
}
/*
function PrintSupplierSelect($def) {
	global $accountstbl, $prefix;

	$t = SUPPLIER;
	$t1 = AUTHORITIES;
	$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND ";
	$query .= "(type='$t' OR type='$t1')";
	$result = DoQuery($query, "payment.php");
	$today = date("Y-m-d");
	print "<select name=\"supplier\" onchange=\"CalcSum()\">\n";
	$l = _("Choose supplier");
	print "<option value=\"0:0\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$acctname = $line['company'];
		$total = GetAcctTotal($num, $today);
		$acctname = GetAccountName($num);
		if($total == 0.0)
			continue;
		$d = ($def == $num) ? " selected" : "";
		print "<option value=\"$num:$total\"$d>$acctname</option>\n";
	}
	print "</select>\n";
}//*/

$step = isset($_GET['step']) ? $_GET['step'] : 0;
$opt = isset($_GET['opt']) ? $_GET['opt'] : '';
if($opt == 'vat') {
	$supplier = PAYVAT;
	if($step == 0)
		$step = 1;
}
else if($opt == 'natins') {
	$supplier = NATINSPAY;
	if($step == 0)
		$step = 1;
}

if($step == 2) {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		ErrorReport("$l");
		//print "<h1>$l</h1>\n";		
		return;
	}

	if($opt == 'vat')
		$account = PAYVAT;
	else if($opt == 'natins')
		$account = NATINSPAY;
	else {
		$supplier = $_POST['supplier'];
		list($account, $t) = explode(':', $supplier);
	}
	if($account == 0) {
		$l = _("No supplier was chosen");
		ErrorReport("$l");
		return;
	}
	$total = (double)$_POST['total'];
	$payment = (double)$_POST['payment'];
	if($payment == 0) {
		ErrorReport(_("No payment method selected"));
		return;
	}
	$refnum = GetPost('refnum');
	$comment = GetPost('comment');
	$dt = $_POST['date'];
	if(ValidDate($dt)) {
		$l = _("Invalid date");
		ErrorReport("$l");
		return;
	} 

	$t = SUPPLIERPAYMENT;
	if($opt == 'vat')
		$t = VAT;
	// Transaction 1 ׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ²ֲ¢׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ»ן¿½׳³ֲ³ײ²ֲ³׳²ֲ³׳’ג‚¬ג€� ׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳³ג€™׳’ג‚¬ן¿½ײ³ג€”׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ§
	$tnum = Transaction(0, $t, $account, $refnum, '', $dt, $comment, $total * -1.0);
	// Transaction 2 ׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ³ֲ·׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ³׳’ג‚¬ג€� ׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¦׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¢ ׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ©׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ²ֲ¢׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½
	$tnum = Transaction($tnum, $t, $payment, $refnum, '', $dt, $comment, $total);
	$step = 0;
	if($opt) {
		$l = _("Payment executed successfully");
		ErrorReport($l);
		return;
	}
}

if($step == 1) {
//	print "<div dir=\"ltr\" style=\"align:left\">\n";
//	print_r($_POST);
//	print "</div>\n";

	switch($opt) {
		case 'vat':
			$supplier = PAYVAT;
			break;
		case 'natins':
			$supplier = NATINSPAY;
			break;
		default:
			list($supplier, $total) = explode(':', $_POST['supplier']);
			break;
	}
//	print "Supplier: $supplier<br>\n";

	$total = isset($_POST['total']) ? $_POST['total'] : $total;
	$payment = (int)$_POST['payment'];
	$dt = $_POST['date'];
	$refnum = GetPost('refnum');
	$comment = GetPost('comment');
	
	//print "<br /><div class=\"form righthalf1\">\n";
	if($opt == 'vat') {
		$l = _("VAT payment");
		$text.= "<h3>$l</h3>\n";
	}
	else if($opt == 'natins') {
		$l = _("National insurance payment");
		$text.= "<h3>$l</h3>\n";
	}
	else {
		$l = _("Supplier payment");
		$text.= "<h3>$l</h3>\n";
		$l = _("Check data and press update to register payment");
		$text.= "<h2>$l</h2>\n";
	}

	$url = "?module=payment&amp;step=2";
	if($opt)
		$url .= "&amp;opt=$opt";
	$text.= "<form name=\"payment\" action=\"$url\" method=\"post\">\n";
	$text.= "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr><td>";
	$l = _("Supplier");
	$text.= "$l: </td>\n";
	$text.= "<td>\n";
	if($opt != '') {
		$str = GetAccountName($supplier);
		$text.= "<input type=\"hidden\" name=\"supplier\" value=\"$supplier:0\" />\n";
		$text.= "$str\n";
	}
	else
		$text.= PrintSupplierSelect($supplier);
	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Sum");
	$text.= "<td>$l: </td>";
	$text.= "<td><input type=\"text\" name=\"total\" value=\"$total\" size=\"7\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Payment type");
	$text.= "<td valign=\"top\">$l: </td><td>";
	PrintPaymentSelect($payment);
	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Ref. num");
	$text.= "<td>$l: </td>";
	$text.= "<td><input type=\"text\" name=\"refnum\" value=\"$refnum\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Details");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"comment\" value=\"$comment\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Date");
	$text.= "<td>$l: </td>";
	if($dt == '')
		$dt = date("d-m-Y");
	$text.= "<td><input class=\"date\" type=\"text\" id=\"date\" name=\"date\" value=\"$dt\" size=\"7\" />\n";

//$text.='<script type="text/javascript">addDatePicker("#date","'.$dt.'");</script>';

	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Update");
	$text.= "<td colspan=\"2\" align=\"center\"><a href=\"javascript:document.payment.submit();\" class=\"btnaction\">$l</a></td>\n";
	$text.= "</tr></table>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	createForm($text, $haeder,'',750,'','',1,getHelp());
	return;
}
//print "<br />\n";
// print "<br>\n";
//print "<div class=\"form righthalf1\">\n";
$haeder = _("Supplier payment");
//$text.= "<h3>$l</h3>\n";
$text.= "<form name=\"payment\" action=\"?module=payment&amp;step=1\" method=\"post\">\n";
$text.= "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
$l = _("Choose supplier");
$text.= "<td>$l: </td>\n";
$text.= "<td>\n";
$text.= PrintSupplierSelect(0);
$text.= "</td>\n";
$text.= "</tr><tr>\n";
$l = _("Total payment");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"total\" size=\"7\" /></td>\n";
$text.= "</tr>\n";
$l = _("Payment type");
$text.= "<tr><td valign=\"top\">$l: </td>";
$text.= "<td>\n";
$text.=PrintPaymentSelect(0);
$text.= "</td></tr><tr><td>";
$l = _("Ref. num");
$text.= "$l: </td>";
$text.= "<td><input type=\"text\" name=\"refnum\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("Details");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"comment\" value=\"\" /></td>\n";
$text.= "</tr><tr><td>";
$l = _("Date");
$text.= "$l: </td>";
$dt = date("d-m-Y");
$text.= "<td><input class=\"date\" type=\"text\" id=\"date\" name=\"date\" value=\"$dt\" size=\"7\" />\n";
//$text.='<script type="text/javascript">addDatePicker("#date","'.$dt.'");</script>';

$text.= "</td></tr>\n";
$l = _("Update");
$text.= "<tr><td align=\"center\" colspan=\"2\"><a href=\"javascript:document.payment.submit();\" class=\"btnaction\">$l</a></td>\n";
$text.= "</tr></table>\n";
$text.= "</form>\n";
//print "</div>\n";
//print "<div class=\"lefthalf1\">\n";
$t = SUPPLIER;
$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
$text. "<div class=\"form\">";
$l = _("Suppliers accounts");
$text. "<h3>$l</h3> \n";
$text. "<br />\n";
$text. "<table class=\"tablesorter\" width=\"100%\">\n";
$text. "<tr>\n";
$l = _("Supplier");
$text. "<th class=\"header\">$l &nbsp;&nbsp;</th>\n";			// supplier account 
$l = _("Acc. balance");
$text. "<th class=\"header\">$l</th>\n";
$text. "</tr>\n";
	
$today = date("Y-m-d");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$total = GetAcctTotal($num, $today);
	$acctname = GetAccountName($num);
//	print "$num $total, $acctname<br>\n";
	if($total == 0.0)
		continue;
	$text. "<tr>\n";
	$url = "?module=acctdisp&amp;account=$num&amp;begin=start&amp;end=today";
	$text. "<td><a href=\"$url\">$acctname</a></td>\n";
	$text. "<td>$total</td>\n";
	$text. "</tr>\n";	
}
$text. "</table>\n";
$text. "</div>";
createForm($text, $haeder,'',750,'','',1,getHelp());
//print "</div>\n";
?>