׳�ֲ»ֲ¿<?PHP
/*
 | Receipts
 | This module is part of Freelance accounting system
 | Written for Shay Harel by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl;
global $paymentarr;
global $creditcompanies;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>׳³ן¿½׳³ן¿½ ׳³ֲ ׳³ג„¢׳³ֳ—׳³ן¿½ ׳³ן¿½׳³ג€˜׳³ֲ¦׳³ֲ¢ ׳³ג‚×׳³ֲ¢׳³ג€¢׳³ן¿½׳³ג€� ׳³ג€“׳³ג€¢ ׳³ן¿½׳³ן¿½׳³ן¿½ ׳³ג€˜׳³ג€”׳³ג„¢׳³ֲ¨׳³ֳ— ׳³ֲ¢׳³ֲ¡׳³ֲ§</h1>\n";
	return;
}

$query = "SELECT vat FROM $companiestbl WHERE prefix='$prefix'";
$result = DoQuery($query, "income.php");
$line = mysql_fetch_array($result, MYSQL_NUM);
$vat = $line[0];

?>
<script type="text/javascript">
function TypeSelChange() {
	var i = document.receipt.payment.selectedIndex;
	
	if(i == 3) {
		document.getElementById('crd').style.display = 'block';
	}
	else {
		document.getElementById('crd').style.display = 'none';
	}
}

function calcTotal() {
	var notaxsum = document.receipt.notaxsum.value;
	var tax = document.receipt.tax.value;
	
	document.receipt.sum.value = parseFloat(notaxsum) - parseFloat(tax);
}

</script>

<?PHP
/*function PrintCustomerSelect($def) {
	global $accountstbl;
	global $prefix;
	
	$t = CUSTOMER;
	$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	print "<select name=\"customer\">\n";
	print "<option value=\"__NULL__\">-- ׳³ג€˜׳³ג€”׳³ֲ¨ ׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€” --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		if($n == $def)
			print "<option value=\"$n\" selected>$company</option>\n";
		else
			print "<option value=\"$n\">$company</option>\n";
	}
	print "</select>\n";
}*/

function PrintPaymentSelect($def) {
	global $paymentarr;

	print "<select name=\"payment\" onchange=\"TypeSelChange()\">\n";
	foreach($paymentarr as $n => $v) {
		if($n == $def)
			print "<option value=\"$n\" selected>$v</option>\n";
		else
			print "<option value=\"$n\">$v</option>\n";
	}
	print "</select>\n";
}

function PrintCreditSelect($def, $payment) {
	global $creditcompanies;
	
	if($payment == 3)
		print "<select name=\"creditcomp\" id=\"crd\" style=\"display:block\">\n";
	else
		print "<select name=\"creditcomp\" id=\"crd\" style=\"display:none\">\n";
	foreach($creditcompanies as $n => $v) {
		if($n == $def)
			print "<option value=\"$n\" selected>$v</option>\n";
		else
			print "<option value=\"$n\">$v</option>\n";
	}
	print "</select>\n";
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}
			
$step = isset($_GET['step']) ? $_GET['step'] : 0;
if($step > 0) {
	
	$customer = $_POST['customer'];
	if($customer == "__NULL__") {
		ErrorReport("׳³ן¿½׳³ן¿½ ׳³ֲ ׳³ג€˜׳³ג€”׳³ֲ¨ ׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”");
		return;
	}
	$refnum = $_POST['refnum'];
	$details = $_POST['details'];
	if(isset($_POST['date'])) {
		$dtmysql = FormatDate($_POST['date'], "dmy", "mysql");
		$dt = FormatDate($dtmysql, "mysql", "dmy");
	}
	else {
		$dtmysel = date("Y-m-d");
		$dt = FormatDate($dtmysql, "mysql", "dmy");
	}
	$payment = $_POST['payment'];
	$creditcomp = $_POST['creditcomp'];
	$ref2 = $_POST['refnum2'];
	$notaxsum = $_POST['notaxsum'];
	$tax = $_POST['tax'];
	$sum = $notaxsum - $tax;
}
if($step == 2) {
	if($name == 'demo') {
		print "<h1>׳³ן¿½׳³ֲ©׳³ֳ—׳³ן¿½׳³ֲ© ׳³ג€�׳³ג€¢׳³ג€™׳³ן¿½׳³ג€� ׳³ן¿½׳³ג„¢׳³ֲ ׳³ג€¢ ׳³ֲ¨׳³ֲ©׳³ן¿½׳³ג„¢ ׳³ן¿½׳³ֲ¢׳³ג€�׳³ג€÷׳³ן¿½ ׳³ֲ ׳³ֳ—׳³ג€¢׳³ֲ ׳³ג„¢׳³ן¿½</h1>\n";
		return;
	}

	/* This is the actual data handling */
	if($sum > 0.01) {	/* Write transactions of receipt */
		if($payment == 0) {
			ErrorReport("׳³ן¿½׳³ן¿½ ׳³ֲ ׳³ג€˜׳³ג€”׳³ֲ¨ ׳³ן¿½׳³ן¿½׳³ֲ¦׳³ן¿½׳³ג„¢ ׳³ֳ—׳³ֲ©׳³ן¿½׳³ג€¢׳³ן¿½");
			return;
		}
		// Transaction 1 ׳³ג€“׳³ג€÷׳³ג€¢׳³ֳ— ׳³ג€�׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€” ׳³ג€˜׳³ֲ¡׳³ג€÷׳³ג€¢׳³ן¿½ ׳³ן¿½׳³ג‚×׳³ֲ ׳³ג„¢ ׳³ֲ ׳³ג„¢׳³ג€÷׳³ג€¢׳³ג„¢ ׳³ג€˜׳³ן¿½׳³ֲ§׳³ג€¢׳³ֲ¨
		$tnum = Transaction(0, MANRECEIPT, $customer, $refnum, '', $dt, $details, $notaxsum);
		// Transaction 2 ׳³ג€”׳³ג€¢׳³ג€˜׳³ֳ— ׳³ֲ ׳³ג„¢׳³ג€÷׳³ג€¢׳³ג„¢ ׳³ג€˜׳³ן¿½׳³ֲ§׳³ג€¢׳³ֲ¨ ׳³ן¿½׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”׳³ג€¢׳³ֳ—
		$t2 = $tax * -1.0;
		$tnum = Transaction($tnum, MANRECEIPT, CUSTTAX, $refnum, '', $dt, $details, $t2);
		// Transaction 3 ׳³ג€”׳³ג€¢׳³ג€˜׳³ֳ— ׳³ֲ§׳³ג€¢׳³ג‚×׳³ג€�
		$t3 = $sum * -1.0;
		switch($payment) {
			case 1:
				$tnum = Transaction($tnum, MANRECEIPT, CHEQUE, $ref2, '', $dt, $details, $t3);
				break;
			case 2:
				$tnum = Transaction($tnum, MANRECEIPT, CASH, $refnum, '', $dt, $details, $t3);
				break;
			case 3:
				$tnum = Transaction($tnum, MANRECEIPT, CREDIT, $ref2, $creditcomp, $dt, $details, $t3);
				break;
		}
		$total = $sum + $t2 + $t3;
		$tnum = Transaction($tnum, MANRECEIPT, ROUNDING, $refnum, '', $dt, $details, $total);
	}
	$step = 0;
	print "<h1>׳³ג€�׳³ֳ—׳³ֲ©׳³ן¿½׳³ג€¢׳³ן¿½ ׳³ֲ ׳³ֲ¨׳³ֲ©׳³ן¿½ ׳³ג€˜׳³ג€�׳³ֲ¦׳³ן¿½׳³ג€”׳³ג€�</h1>\n";
	$step = 0;
	$refnum = '';
	$details = '';
	$payment = 0;
	$creditcomp = 0;
	$ref2 = '';
	$notaxsum = 0;
	$tax = 0;
	$sum = 0;
}
print "<div class=\"righthalf\">\n";
if($step == 1) {
	print "<div class=\"caption_out\"><div class=\"caption\">";
	print "<b>׳³ן¿½׳³ג„¢׳³ֲ©׳³ג€¢׳³ֲ¨ ׳³ֲ¨׳³ג„¢׳³ֲ©׳³ג€¢׳³ן¿½ ׳³ֲ§׳³ג€˜׳³ן¿½׳³ג€�</b>\n";
	print "</div></div>\n";
	print "<h2>׳³ג„¢׳³ֲ© ׳³ן¿½׳³ג€˜׳³ג€�׳³ג€¢׳³ֲ§ ׳³ן¿½׳³ֳ— ׳³ג€�׳³ג‚×׳³ֲ¨׳³ֻ�׳³ג„¢׳³ן¿½ ׳³ג€¢׳³ן¿½׳³ן¿½׳³ג€”׳³ג€¢׳³ֲ¥ ׳³ֲ¢׳³ג€�׳³ג€÷׳³ן¿½ ׳³ג€˜׳³ֲ©׳³ֲ ׳³ג„¢׳³ֳ— ׳³ֲ¢׳³ן¿½ ׳³ן¿½׳³ֲ ׳³ֳ— ׳³ן¿½׳³ג€˜׳³ֲ¦׳³ֲ¢ ׳³ן¿½׳³ֳ— ׳³ג€�׳³ֲ¨׳³ג„¢׳³ֲ©׳³ג€¢׳³ן¿½</h2>\n";
	$nextstep = 2;
}
else if($step == 0) {
	print "<div class=\"caption_out\"><div class=\"caption\">";
	print "<b>׳³ֲ§׳³ג€˜׳³ן¿½׳³ג€�</b>\n";
	print "</div></div>\n";
	$customer == "__NULL__";
	$nextstep = 1;
	$tax = 0;
}

print "<form name=\"receipt\" action=\"?module=receipts&step=$nextstep\" method=\"post\">\n";
print "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
print "<td>\n";
print "׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”: </td><td>\n";
print PrintCustomerSelect($customer);
print "</td>\n";
print "</tr><tr>\n";
print "<td>׳³ֲ¡׳³ג€÷׳³ג€¢׳³ן¿½ ׳³ן¿½׳³ג‚×׳³ֲ ׳³ג„¢ ׳³ֲ ׳³ג„¢׳³ג€÷׳³ג€¢׳³ג„¢ ׳³ן¿½׳³ֲ¡: </td>\n";
print "<td><input type=\"text\" name=\"notaxsum\" value=\"$notaxsum\" size=\"10\"></td>\n";
print "</tr><tr>\n";

print "<td>׳³ֲ ׳³ג„¢׳³ג€÷׳³ג€¢׳³ג„¢ ׳³ג€˜׳³ן¿½׳³ֲ§׳³ג€¢׳³ֲ¨: </td>\n";
print "<td><input type=\"text\" name=\"tax\" size=\"10\" value=\"$tax\" onblur=\"calcTotal()\"></td>\n";
print "</tr><tr>\n";

print "<td>׳³ֲ¡׳³ג€÷׳³ג€¢׳³ן¿½ ׳³ן¿½׳³ן¿½׳³ג€”׳³ֲ¨ ׳³ֲ ׳³ג„¢׳³ג€÷׳³ג€¢׳³ג„¢ ׳³ן¿½׳³ֲ¡: </td>\n";
print "<td><input type=\"text\" name=\"sum\" size=\"10\" value=\"$sum\"></td>\n";
print "</tr><tr>\n";

print "<td>׳³ן¿½׳³ֲ¡׳³ג‚×׳³ֲ¨ ׳³ֲ§׳³ג€˜׳³ן¿½׳³ג€�: </td>\n";
print "<td><input type=\"text\" name=\"refnum\" size=\"10\" value=\"$refnum\"></td>\n";
print "</tr><tr>\n";

print "<td>׳³ֳ—׳³ן¿½׳³ֲ¨׳³ג„¢׳³ן¿½: </td>\n";
print "<td><input type=\"text\" name=\"date\" size=\"7\" value=\"$dt\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'receipt',
		// input name
		'controlname': 'date'
	});

</script>
<?PHP
print "</td>\n";
print "</tr><tr>\n";

print "<td>׳³ג‚×׳³ֲ¨׳³ֻ�׳³ג„¢׳³ן¿½: </td>\n";
print "<td><input type=\"text\" name=\"details\" value=\"$details\" size=\"25\"></td>\n";
print "</tr><tr>\n";

print "<td>׳³ן¿½׳³ן¿½׳³ֲ¦׳³ן¿½׳³ג„¢ ׳³ֳ—׳³ֲ©׳³ן¿½׳³ג€¢׳³ן¿½: </td>\n";
print "<td>\n";
PrintPaymentSelect($payment);
PrintCreditSelect($creditcomp, $payment);
// print "</div>\n";
print "</tr><tr>\n";

print "<td>׳³ן¿½׳³ֲ¡׳³ן¿½׳³ג€÷׳³ֳ—׳³ן¿½: </td>\n";
print "<td><input type=\"text\" name=\"refnum2\" value=\"$ref2\" size=\"15\"></td>\n";
print "</tr><tr>\n";

print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"׳³ֲ¢׳³ג€�׳³ג€÷׳³ן¿½\">\n";
print "</td></tr>\n";
print "</table>\n";
print "</form>\n";
print "</div>\n";
//print "<div class=\"lefthalf\">\n";
//ShowText('receipts');
//print "</div>\n";
?>

