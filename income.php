<?PHP
/*
 | Income
 | This file is part of freelance accounting system
 | Written for Shay Harel by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}

$query = "SELECT vat FROM $companiestbl WHERE prefix='$prefix'";
$result = DoQuery($query, "income.php");
$line = mysql_fetch_array($result, MYSQL_NUM);
$vat = $line[0];

?>
<script type="text/javascript">
function Fix2(v) {
	v = parseFloat(v) * 100.0;
	v = Math.round(v);
	
	return v / 100.0;
}

function Fix0(v) {
	return Math.round(v);
}

function CalcTotal() {
	var total = document.income.novattotal.value;
	var d = document.getElementById('vatd').style.display;
<?PHP
	print "\tvat = $vat\n";
?>
	if(d != 'block')
		vat = 0;
	var calcvat = parseFloat(total) * parseFloat(vat) / 100.0;
	document.income.vat.value = Fix2(calcvat);
	document.income.total.value = Fix0(parseFloat(total) + calcvat);
}

function CalcVAT() {
	var total = document.income.total.value;
	var d = document.getElementById('vatd').style.display;
<?PHP
	print "\tvat = $vat\n";
?>
	if(d != 'block') {
		vat = 0;
	}
	var v = 1.0 + parseFloat(vat) / 100.0;
	var novattotal = parseFloat(total) / v;
	document.income.novattotal.value = Fix2(novattotal);
	document.income.vat.value = Fix2(novattotal * parseFloat(vat) / 100.0);
}

function TypeSelChange() {
	var i = document.income.payment.selectedIndex;
	
	if(i == 3) {
		document.getElementById('crd').style.display = 'block';
	}
	else {
		document.getElementById('crd').style.display = 'none';
	}
}

function calcTotalTax() {
	var notaxsum = document.income.notaxsum.value;
	var tax = document.income.tax.value;
	var d = document.getElementById('vatd').style.display;
	
	if(d == 'block') {
		document.income.sum.value = parseFloat(notaxsum) + parseFloat(tax);
	}
	else {
		document.income.sum.value = parsetFloat(notaxsum);
	}
}

function inchange() {
	var i = document.income.income.value;
	
//	alert(i);
	switch(i) {
<?PHP
	$t = INCOME;
	$query = "SELECT num,src_tax FROM $accountstbl WHERE prefix='$prefix' AND type='$t'  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$t = $line[1];
		if($t == '')
			$t = 100;
		$n = $line[0];
		print "\t\tcase '$n':\n";
		if($t != 100) {
			print "\t\t\tdocument.getElementById('vatd').style.display = 'none';\n";
			print "\t\t\tdocument.getElementById('vatd1').style.display = 'none';\n";
		}
		else {
		//	print "\t\t\talert('block');\n";
			print "\t\t\tdocument.getElementById('vatd').style.display = 'block';\n";
			print "\t\t\tdocument.getElementById('vatd1').style.display = 'block';\n";
		}
		print "\t\t\tbreak;\n";
	}
?>
	}
}
</script>

<?PHP
function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function PrintCustomerSelect($def) {
	global $accountstbl;
	global $prefix;
	
	$t = CUSTOMER;
	$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	print "<select name=\"customer\" style=\"z-index:0\">\n";
	print "<option value=\"__NULL__\">-- בחר לקוח --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		if($n == $def)
			print "<option value=\"$n\" selected>$company</option>\n";
		else
			print "<option value=\"$n\">$company</option>\n";
	}
	print "</select>\n";
}

function PrintIncomeSelect($def) {
	global $accountstbl;
	global $prefix;
	
	$t = INCOME;
	$query = "SELECT num,company,src_tax FROM $accountstbl WHERE prefix='$prefix' AND type='$t'  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	print "<select name=\"income\" onchange=\"inchange()\">\n";
	print "<option value=\"__NULL__\" >-- בחר סעיף הכנסה --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		$v = $line[2]; 
		if(($v != '') && ($v == 0))
			$company .= " (מע\"מ 0%)";
		else
			$company .= " (מע\"מ 100%)";
		if($n == $def)
			print "<option value=\"$n\" selected>$company</option>\n";
		else
			print "<option value=\"$n\">$company</option>\n";
	}
	print "</select>\n";
}

function PrintPaymentSelect($def) {
	$paymentarr = array('-- בחר אמצעי תשלום --', 
	'שיק',
	'מזומן',
	'אשראי');
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
	$creditarr = array(
		'visa' => 'ויזה',
		'isracard' => 'ישראכרט',
		'mastercard' => 'מאסטרכרד',
		'diners' => 'דיינרס',
		'amex' => 'אמריקן אקספרס'
	);
	
	if($payment != 3)
		print "<select name=\"creditcomp\" id=\"crd\" style=\"display:none\">\n";
	else
		print "<select name=\"creditcomp\" id=\"crd\" style=\"display:block\">\n";
	foreach($creditarr as $n => $v) {
		if($n == $def)
			print "<option value=\"$n\" selected>$v</option>\n";
		else
			print "<option value=\"$n\">$v</option>\n";
	}	
	print "</select>\n";
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;
$option = isset($_GET['option']) ? $_GET['option'] : '';
if($step > 0) {
	$customer = $_POST['customer'];
	if($customer == "__NULL__") {
		ErrorReport("לא נבחר לקוח");
		return;
	}
	$income = $_POST['income'];
	$query = "SELECT src_tax FROM $accountstbl WHERE prefix='$prefix' AND num='$income'";
	$result = DoQuery($query, "income.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$vatp = $line[0];
	$refnum = $_POST['refnum'];
	$refnum2 = $_POST['refnum2'];
	$details = $_POST['details'];
	$novattotal = $_POST['novattotal'];
	if(($vatp == '') || ($vatp == 100))
		$tvat = $novattotal * $vat / 100.0;
	else
		$tvat = 0;
	$total = round($novattotal + $tvat);
	if(isset($_POST['date'])) {
		$dtmysql = FormatDate($_POST['date'], "dmy", "mysql");
		$dt = FormatDate($dtmysql, "mysql", "dmy");
	}
	else {
		$dtmysel = date("Y-m-d");
		$dt = FormatDate($dtmysql, "mysql", "dmy");
	}
	
	if(($income == "__NULL__") && ($total > 0.1)) {
		ErrorReport("לא נבחר סעיף הכנסה");
		return;
	}
	if($option == 'receipt') {
		$payment = $_POST['payment'];
		$creditcomp = $_POST['creditcomp'];
		$ref2 = $_POST['refnum2'];
		$notaxsum = $_POST['notaxsum'];
		$tax = $_POST['tax'];
		$sum = $notaxsum + $tax;
	}
}
if($step == 2) {
	if($name == 'demo') {
		print "<h1>משתמש דוגמה אינו רשאי לעדכן נתונים</h1>\n";
		return;
	}

	/* This is the actual data handling */
	if(@ValidDate($dt)) {
		ErrorReport("תאריך לא תקין");
		return;
	}
	if(abs($total) > 0.01) {	/* write transactions as it invoice */
		//‎ Transaction 1 חובת הלקוח בסכום החשבונית
		$t1 = $total * -1.0;
		$tnum = Transaction(0, MANINVOICE, $customer, $refnum, '', $dt, $details, $t1);
		// Transaction 2 זכות מע"מ עסקאות
		$tnum = Transaction($tnum, MANINVOICE, SELLVAT, $refnum, '', $dt, '', $tvat);
		// Transaction 3 זכות חשבון ההכנסות
		$tnum = Transaction($tnum, MANINVOICE, $income, $refnum, '', $dt, $details, $novattotal);
		$total = $t1 + $tvat + $novattotal;
		$total *= 1.0;
		$tnum = Transaction($tnum, MANINVOICE, ROUNDING, $refnum, '', $dt, $details, $total);
		if($option == 'receipt') {
			if($payment == 0) {
				ErrorReport("לא נבחר אמצאי תשלום");
				return;
			}
			// Transaction 1 זכות הלקוח בסכום אחרי ניכוי במקור
			$tnum = Transaction(0, MANRECEIPT, $customer, $refnum, '', $dt, $details, $sum);
			// Transaction 2 חובת ניכוי במקור מלקוחות
			$t2 = $tax * -1.0;
			$tnum = Transaction($tnum, MANRECEIPT, CUSTTAX, $refnum, '', $dt, $details, $t2);
			// Transaction 3 חובת קופה
			$t3 = $notaxsum * -1.0;
			switch($payment) {
				case 1:
					$tnum = Transaction($tnum, MANRECEIPT, CHEQUE, $refnum2, '', $dt, $details, $t3);
					break;
				case 2:
					$tnum = Transaction($tnum, MANRECEIPT, CASH, $refnum, '', $dt, $details, $t3);
					break;
				case 3:
					$tnum = Transaction($tnum, MANRECEIPT, CREDIT, $refnum2, $creditcomp, $dt, $details, $t3);
					break;
			}
			$t4 = $sum + $t2 + $t3;
			$tnum = Transaction($tnum, MANRECEIPT, ROUNDING, $refnum, '', $dt, $details, $t4);
		}
		print "<h1>ההכנסה נרשמה בהצלחה</h1>\n";
	}
	$step = 0;
	$customer = "__NULL__";
	$income = "__NULL__";
	$dt = '';
	$refnum = '';
	$details = '';
	$novattotal = 0.0;
	$tvat = 0.0;
	$total = 0.0;
	$refnum2 = '';
	$tax = 0;
	$notaxsum = 0.0;
	$sum = 0.0;
}
$url = "?module=income&step=$nextstep";
if($option != '')
	$url .= "&option=$option";
//print "<div class=\"righthalf2\">\n";
print "<table dir=\"rtl\" border=\"0\"><tr><td>\n";

if($step == 1) {
	print "<div class=\"caption_out\"><div class=\"caption\">";
	print "<b>אישור רישום הכנסה</b>\n";
	print "</div></div>\n";
	print "<h2>יש לבדוק את הפרטים וללחוץ עדכן בשנית על מנת לבצע את הרישום</h2>\n";
	$nextstep = 2;
}
if($step == 0) {
	print "<div class=\"caption_out\"><div class=\"caption\">";
	print "<b>רישום הכנסה</b>\n";
	print "</div></div>\n";
	$customer == "__NULL__";
	$income == "__NULL__";
	$nextstep = 1;
}

print "<form name=\"income\" action=\"$url\" method=\"post\">\n";
print "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
print "<td>לקוח: </td>\n";
print "<td>\n";
PrintCustomerSelect($customer);
if($step == 0) {
	$t = CUSTOMER;
	print "&nbsp;&nbsp;<a href=\"index.php?module=acctadmin&action=addacct&type=$t\">הגדר לקוח חדש</a>\n";
}
print "</td></tr>\n";
print "<tr>\n";

print "<td>סעיף הכנסה: </td>\n";
print "<td>\n";
PrintIncomeSelect($income);
if($step == 0) {
	$t = INCOME;
	print "<br><a href=\"index.php?module=acctadmin&action=addacct&type=$t\">הגדר סעיף הכנסה חדש</a>\n";
}
print "</td></tr>\n";
print "<tr>\n";

print "<td>תאריך: </td>\n";
print "<td><input type=\"text\" name=\"date\" size=\"7\" value=\"$dt\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'income',
		// input name
		'controlname': 'date'
	});

</script>
<?PHP
print "</td>\n";
print "</tr><tr>\n";

print "<td>אסמכתא: </td>\n";
print "<td><input type=\"text\" name=\"refnum\" value=\"$refnum\" size=\"15\"></td>\n";
print "</tr><tr>\n";

print "<td>פרטים: </td>\n";
print "<td><input type=\"text\" name=\"details\" value=\"$details\" size=\"25\"></td>\n";
print "</tr><tr>\n";

print "<td>סכום לפני מע\"מ: </td>\n";
print "<td><input type=\"text\" dir=\"ltr\" name=\"novattotal\" value=\"$novattotal\" size=\"10\" onblur=\"CalcTotal()\"></td>\n";
print "</tr><tr>\n";

print "<td><div id=\"vatd\">מע\"מ: </td></td>\n";
print "<td>\n";
print "<div id=\"vatd1\">";
print "<input type=\"text\" name=\"vat\" dir=\"ltr\" size=\"10\" value=\"$tvat\" readonly>\n";
print "</div></td>\n";
print "</tr><tr>\n";

print "<td>סכום כולל מע\"מ: </td>\n";
print "<td><input type=\"text\" name=\"total\" dir=\"ltr\" size=\"10\" value=\"$total\" onblur=\"CalcVAT()\"></td>\n";
print "</tr><tr>\n";

if($option == 'receipt') {
	print "<td>אמצאי תשלום: </td>\n";
	print "<td>\n";
	PrintPaymentSelect($payment);
	PrintCreditSelect($creditcomp, $payment);
	// print "</div>\n";
	print "</tr><tr>\n";
	print "<td>אסמכתא: </td>\n";
	print "<td><input type=\"text\" name=\"refnum2\" value=\"$refnum2\"></td>\n";
	print "</tr><tr>\n";
	print "<td>תשלום לפני ניכוי במקור: </td>\n";
	print "<td><input type=\"text\" name=\"notaxsum\" size=\"10\" value=\"$notaxsum\"></td>\n";
	print "</tr><tr>\n";

	print "<td>ניכוי במקור: </td>\n";
	print "<td><input type=\"text\" name=\"tax\" size=\"10\" value=\"$tax\" onblur=\"calcTotalTax()\"></td>\n";
	print "</tr><tr>\n";

	print "<td>סכום כולל ניכוי במקור: </td>\n";
	print "<td><input type=\"text\" name=\"sum\" size=\"10\" value=\"$sum\"></td>\n";
	print "</tr><tr>\n";
}
print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"עדכן\">\n";
print "</td></tr>\n";
print "</table>\n";
print "</form>\n";
print "</td><td valign=\"top\">\n";
print "<div style=\"margin-right:10px\">\n";
if($option == 'receipt')
	ShowText('income_receipt');
else
	ShowText('income');
print "</div>\n";
print "</td></tr>";
print "<tr><td colspan=\"2\">\n";
if($step == 0)
	require('lastincome.inc.php');
print "</td></tr>\n";
print "</table>\n";
?>

