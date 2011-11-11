<?PHP
/*
 | Receipts
 | This module is part of Freelance accounting system
 | Written for Shay Harel by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl;
global $paymenttype;
global $creditcompanies;

if(!isset($prefix) || ($prefix == '')) {
	$text.= "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}

$query = "SELECT vat FROM $companiestbl WHERE prefix='$prefix'";
$result = DoQuery($query, "income.php");
$line = mysql_fetch_array($result, MYSQL_NUM);
$vat = $line[0];
$text='';
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
	var tax = document.receipt.src_tax.value;
	
	document.receipt.total.value = parseFloat(notaxsum) - parseFloat(tax);
}

</script>

<?PHP
/*
function PrintCustomerSelect($def) {
	global $accountstbl;
	global $prefix;
	
	$t = CUSTOMER;
	$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	$text.= "<select name=\"customer\">\n";
	$text.= "<option value=\"__NULL__\">-- בחר לקוח --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		if($n == $def)
			$text.= "<option value=\"$n\" selected>$company</option>\n";
		else
			$text.= "<option value=\"$n\">$company</option>\n";
	}
	$text.= "</select>\n";
}*/

function PrintPaymentSelect($def) {
	global $paymenttype;

	$str= "<select name=\"payment\" onchange=\"TypeSelChange()\">\n";
	foreach($paymenttype as $n => $v) {
		if($n == $def)
			$str.= "<option value=\"$n\" selected>$v</option>\n";
		else
			$str.= "<option value=\"$n\">$v</option>\n";
	}
	$str.= "</select>\n";
	return $str;
}

function PrintCreditSelect($def, $payment) {
	global $creditcompanies;
	
	if($payment == 3)
		$str= "<select name=\"creditcomp\" id=\"crd\" style=\"display:block\">\n";
	else
		$str= "<select name=\"creditcomp\" id=\"crd\" style=\"display:none\">\n";
	foreach($creditcompanies as $n => $v) {
		if($n == $def)
			$str.= "<option value=\"$n\" selected>$v</option>\n";
		else
			$str.= "<option value=\"$n\">$v</option>\n";
	}
	$str.= "</select>\n";
	return $str;
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
		ErrorReport("לא נבחר לקוח");
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
		ErrorReport("<h1>משתמש דוגמה אינו רשאי לעדכן נתונים</h1>\n");
		return;
	}

	/* This is the actual data handling */
	if($sum > 0.01) {	/* Write transactions of receipt */
		if($payment == 0) {
			ErrorReport("לא נבחר אמצאי תשלום");
			return;
		}
		// Transaction 1 זכות הלקוח בסכום לפני ניכוי במקור
		$tnum = Transaction(0, MANRECEIPT, $customer, $refnum, '', $dt, $details, $notaxsum);
		// Transaction 2 חובת ניכוי במקור מלקוחות
		$t2 = $tax * -1.0;
		$tnum = Transaction($tnum, MANRECEIPT, CUSTTAX, $refnum, '', $dt, $details, $t2);
		// Transaction 3 חובת קופה
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
	$text.= "<h1>התשלום נרשם בהצלחה</h1>\n";
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
//$text.= "<div class=\"righthalf\">\n";
if($step == 1) {
	//$text.= "<div class=\"caption_out\"><div class=\"caption\">";
	$header= "אישור רישום קבלה";
	//$text.= "</div></div>\n";
	$text.= "<h2>יש לבדוק את הפרטים וללחוץ עדכן בשנית על מנת לבצע את הרישום</h2>\n";
	$nextstep = 2;
}
else if($step == 0) {
	//$text.= "<div class=\"caption_out\"><div class=\"caption\">";
	$header= "קבלה";
	//$text.= "</div></div>\n";
	$customer == "__NULL__";
	$nextstep = 1;
	$tax = 0;
}

$text.= "<form name=\"receipt\" action=\"?module=docsadmin&targetdoc=8&step=$nextstep\" method=\"post\"><input type=\"hidden\" name=\"type\" value=\"8\" />\n";
$text.= "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
$text.= "<td>\n";
$text.= "לקוח: </td><td>\n";
$text.= PrintCustomerSelect($customer);
$text.= "</td>\n";
$text.= "</tr><tr>\n";
$text.= "<td>סכום לפני ניכוי מס: </td>\n";
$text.= "<td><input type=\"text\" name=\"notaxsum\" value=\"$notaxsum\" size=\"10\"></td>\n";
$text.= "</tr><tr>\n";

$text.= "<td>ניכוי במקור: </td>\n";
$text.= "<td><input type=\"text\" name=\"src_tax\" size=\"10\" value=\"$tax\" onblur=\"calcTotal()\"></td>\n";
$text.= "</tr><tr>\n";

$text.= "<td>סכום לאחר ניכוי מס: </td>\n";
$text.= "<td><input type=\"text\" name=\"total\" size=\"10\" value=\"$sum\"></td>\n";
$text.= "</tr><tr>\n";

$text.= "<td>מספר קבלה: </td>\n";
$text.= "<td><input type=\"text\" name=\"refnum\" size=\"10\" value=\"$refnum\"></td>\n";
$text.= "</tr><tr>\n";

$text.= "<td>תאריך: </td>\n";
$text.= "<td><input class=\"date\" type=\"text\" name=\"idate\" id=\"idate\" size=\"7\" value=\"$dt\">\n";

$text.= "</td>\n";
$text.= "</tr><tr>\n";

$text.= "<td>פרטים: </td>\n";
$text.= "<td><input type=\"text\" name=\"comments\" value=\"$details\" size=\"25\"></td>\n";
$text.= "</tr><tr>\n";

$text.= "<td>אמצאי תשלום: </td>\n";
$text.= "<td>\n";
$text.= PrintPaymentSelect($payment);
$text.= PrintCreditSelect($creditcomp, $payment);
// $text.= "</div>\n";
$text.= "</tr><tr>\n";

$text.= "<td>אסמכתא: </td>\n";
$text.= "<td><input type=\"text\" name=\"refnum2\" value=\"$ref2\" size=\"15\"></td>\n";
$text.= "</tr><tr>\n";

$text.= "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"עדכן\">\n";
$text.= "</td></tr>\n";
$text.= "</table>\n";
$text.= "</form>\n";
//$text.= "</div>\n";
createForm($text, $header,'',750,'','icon',1,getHelp());
?>