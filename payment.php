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
print "here";
global $prefix, $accountstbl, $supdocstbl;
global $payemnttype;

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

function PrintPaymentSelect($def) {
	global $accountstbl;
	global $prefix;

	print "<select name=\"payment\">\n";
	$l = _("Payment type");
	print "<option value=\"0\">-- $l --</option>\n";
	
	$banks = BANKS;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$banks' AND prefix='$prefix'"; // banks accounts
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		if($num > 100) {
			$acct = $line['company'];
			print "<option value=\"$num\" ";
			if($num == $def)
				print "selected";
			print ">$acct</option>\n";
		}
	}
	print "</select>\n";
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
		print "<h1>$l</h1>\n";		
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
	// Transaction 1 ׳—׳•׳‘׳× ׳”׳¡׳₪׳§
	$tnum = Transaction(0, $t, $account, $refnum, '', $dt, $comment, $total * -1.0);
	// Transaction 2 ׳–׳›׳•׳× ׳�׳�׳¦׳�׳™ ׳”׳×׳©׳�׳•׳�
	$tnum = Transaction($tnum, $t, $payment, $refnum, '', $dt, $comment, $total);
	$step = 0;
	if($opt) {
		$l = _("Payment executed successfully");
		print "<h1>$l</h1>\n";
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
	
	print "<br /><div class=\"form righthalf1\">\n";
	if($opt == 'vat') {
		$l = _("VAT payment");
		print "<h3>$l</h3>\n";
	}
	else if($opt == 'natins') {
		$l = _("National insurance payment");
		print "<h3>$l</h3>\n";
	}
	else {
		$l = _("Supplier payment");
		print "<h3>$l</h3>\n";
		$l = _("Check data and press update to register payment");
		print "<h2>$l</h2>\n";
	}

	$url = "?module=payment&amp;step=2";
	if($opt)
		$url .= "&amp;opt=$opt";
	print "<form name=\"payment\" action=\"$url\" method=\"post\">\n";
	print "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr><td>";
	$l = _("Supplier");
	print "$l: </td>\n";
	print "<td>\n";
	if($opt != '') {
		$str = GetAccountName($supplier);
		print "<input type=\"hidden\" name=\"supplier\" value=\"$supplier:0\" />\n";
		print "$str\n";
	}
	else
		print PrintSupplierSelect($supplier);
	print "</td>\n";
	print "</tr><tr>\n";
	$l = _("Sum");
	print "<td>$l: </td>";
	print "<td><input type=\"text\" name=\"total\" value=\"$total\" size=\"7\" /></td>\n";
	print "</tr><tr>\n";
	$l = _("Payment type");
	print "<td valign=\"top\">$l: </td><td>";
	PrintPaymentSelect($payment);
	print "</td>\n";
	print "</tr><tr>\n";
	$l = _("Ref. num");
	print "<td>$l: </td>";
	print "<td><input type=\"text\" name=\"refnum\" value=\"$refnum\" /></td>\n";
	print "</tr><tr>\n";
	$l = _("Details");
	print "<td>$l: </td>\n";
	print "<td><input type=\"text\" name=\"comment\" value=\"$comment\" /></td>\n";
	print "</tr><tr>\n";
	$l = _("Date");
	print "<td>$l: </td>";
	if($dt == '')
		$dt = date("d-m-Y");
	print "<td><input type=\"text\" id=\"date\" name=\"date\" value=\"$dt\" size=\"7\" />\n";
?>
<script type="text/javascript">
	addDatePicker("#date","<?print "$dt"; ?>");
</script>
<?PHP
	print "</td>\n";
	print "</tr><tr>\n";
	$l = _("Update");
	print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\" /></td>\n";
	print "</tr></table>\n";
	print "</form>\n";
	print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	ShowText('payment');
	print "</div>\n";
	return;
}
print "<br />\n";
// print "<br>\n";
print "<div class=\"form righthalf1\">\n";
$l = _("Supplier payment");
print "<h3>$l</h3>\n";
print "<form name=\"payment\" action=\"?module=payment&amp;step=1\" method=\"post\">\n";
print "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
$l = _("Choose supplier");
print "<td>$l: </td>\n";
print "<td>\n";
print PrintSupplierSelect(0);
print "</td>\n";
print "</tr><tr>\n";
$l = _("Total payment");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"total\" size=\"7\" /></td>\n";
print "</tr>\n";
$l = _("Payment type");
print "<tr><td valign=\"top\">$l: </td>";
print "<td>\n";
PrintPaymentSelect(0);
print "</td></tr><tr><td>";
$l = _("Ref. num");
print "$l: </td>";
print "<td><input type=\"text\" name=\"refnum\" /></td>\n";
print "</tr><tr>\n";
$l = _("Details");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"comment\" value=\"\" /></td>\n";
print "</tr><tr><td>";
$l = _("Date");
print "$l: </td>";
$dt = date("d-m-Y");
print "<td><input type=\"text\" id=\"date\" name=\"date\" value=\"$dt\" size=\"7\" />\n";
?>
<script type="text/javascript">addDatePicker("#date","<?print "$dt"; ?>");</script>
<?PHP
print "</td></tr>\n";

print "<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"׳”׳�׳©׳�\" /></td>\n";
print "</tr></table>\n";
print "</form>\n";
print "</div>\n";
print "<div class=\"lefthalf1\">\n";
$t = SUPPLIER;
$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "payment.php");
print "<div class=\"form\">";
$l = _("Suppliers accounts");
print "<h3>$l</h3> \n";
print "<br />\n";
print "<table border=\"0\" dir=\"rtl\" cellpadding=\"5px\" cellspacing=\"5px\" width=\"100%\">\n";
print "<tr class=\"tblhead\">\n";
$l = _("Supplier");
print "<td>$l &nbsp;&nbsp;</td>\n";			// supplier account 
$l = _("Acc. balance");
print "<td>$l</td>\n";
print "</tr>\n";
	
$today = date("Y-m-d");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$total = GetAcctTotal($num, $today);
	$acctname = GetAccountName($num);
//	print "$num $total, $acctname<br>\n";
	if($total == 0.0)
		continue;
	print "<tr>\n";
	$url = "?module=acctdisp&amp;account=$num&amp;begin=start&amp;end=today";
	print "<td><a href=\"$url\">$acctname</a></td>\n";
	print "<td>$total</td>\n";
	print "</tr>\n";	
}
print "</table>\n";
print "</div>";
ShowText('payment');
print "</div>\n";
?>