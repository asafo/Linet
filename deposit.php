<?PHP
/*
 | Incoming cheques handling script for Drorit Free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */

global $chequestbl, $paymenttype;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

function PrintBankSelect() {
	global $accountstbl;
	global $prefix;

	print "<select name=\"account\">\n";
	
	$banks = BANKS;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$banks' AND prefix='$prefix'"; /* banks accounts */
	$result = DoQuery($query, "PrintBankSelect");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		if($num > 100) {
			$acct = $line['company'];
			print "<option value=\"$num\">$acct</option>\n";
		}
	}
	print "</select>\n";
}

?>
<script type="text/javascript">
function PrintDocument(purl) {

	window.open(purl, 'printwin', 'width=800,height=600,scrollbar=yes');
}

function CalcSum() {
	var vals = document.form1.cheque;
	var total = document.form1.sum;
	var t = document.form1.total;
	
	size = vals.length;
	
	// alert("Length: " + size);
		
	sum = parseFloat("0.0");
	t.value = '';
	if(size) {
		for(i = 0; i < size; i++) {
			if(vals[i].checked) {
				// alert("value: " + vals[i].value + ", " + total[i].value);
				sum += parseFloat(total[i].value);
			}
		}
	}
	else {
		if(vals.checked)
			sum = parseFloat(total.value);
	}
	t.value = sum;
}

</script>

<?PHP
$action = isset($_GET['action']) ? $_GET['action'] : '';

print "<br>\n";
print "<div class=\"form righthalf1\">\n";
$l = _("Cheque, credit and cash deposit");
print "<h3>$l</h3>\n";
// print "<h3>הפקדת שקים, מזומנים ורישום סליקה</h3>\n";

if($action == 'submit') {
	$account = $_POST['account'];
	if(!$account) {
		$l = _("Bank account not defined");
		ErrorReport("$l");
		exit;
	}
	$dep_date = $_POST['dep_date'];
	if(empty($dep_date)) {
		$l = _("Deposit date not defined");
		ErrorReport("$l");
		exit;
	}
//	$dep_date = FormatDate($dep_date, "dmy", "mysql");
	
	$bank_refnum = GetPost('bank_refnum');
	if(empty($bank_refnum)) {
		$l = _("Bank refnum must be filled");
		ErrorReport("$l");
		exit;
	}
	$cheque = $_POST['cheque'];
	if(empty($cheque)) {
		$l = _("Nothing to deposit");
		ErrorReport("$l");
		exit;
	}
	$sum = $_POST['sum'];
	
	$tnum = 0;
//	print_r($cheque);
	foreach($cheque as $i => $ch) {
		list($val, $sum) = split(":", $ch);
	//	print "val: $val, sum: $sum<br>\n";
		/* first check if this cheque already deposited */
		$query = "SELECT dep_date,type FROM $chequestbl WHERE cheque_num='$val' AND prefix='$prefix'";
		$result = DoQuery($query, "Check cheque");
		if(!$result)
			break;
		while(@$line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$n = $line['dep_date'];
		//	print "dep_date: $n<br>\n";
			if($n != '0000-00-00')
				continue;
			$t = $line['type'];
			// First part זכות קופת שיקים 
			$total = (float)$sum;
			if($t == 1)
				$cheque_acct = CASH;
			else
				$cheque_acct = CHEQUE;
			$tnum = Transaction($tnum, CHEQUEDEPOSIT, $cheque_acct, $bank_refnum, $val, $dep_date, '', $total);

			// Second part חובת הבנק
			$tnum = Transaction($tnum, CHEQUEDEPOSIT, $account, $bank_refnum, $val, $dep_date, '', $total * -1.0);
			/* Now mark cheque as deposited */
			$dep_date1 = FormatDate($dep_date, "dmy", "mysql");
			$query = "UPDATE $chequestbl SET ";
			$query .= "bank_refnum='$bank_refnum', \n";
			$query .= "dep_date='$dep_date1' \n";
			$query .= "WHERE cheque_num='$val' AND dep_date='0000-00-00' AND prefix='$prefix'";
			//	print "Query: $query<br>\n";
			$result = DoQuery($query, __LINE__);
		}
	}
//	print "<div dir=ltr>Sum: $total</div>\n";
	$l = _("Deposit executed successfully");
	print "<h2>$l</h2></br>\n";
}

print "<form name=\"form1\" action=\"?module=deposit&amp;action=submit\" method=\"post\">\n";
print "<table width=\"100%\" class=\"formtbl\"><tr>\n";
$l = _("Bank account");
print "<td>$l: \n";
PrintBankSelect();
$l = _("Deposit date");
print "</td><td>$l: \n";
$dep_date = date("d-m-Y");
print "<input type=\"text\" id=\"dep_date\" name=\"dep_date\" value=\"$dep_date\" size=\"8\">\n";
?>
<script type="text/javascript">
	addDatePicker("#dep_date","<?print "$dep_date"; ?>");
</script>
<?PHP
print "</td></tr>\n";
print "<tr><td colspan=\"2\">\n";
$l = _("Bank refnum");
print "$l: \n";
print "<input type=\"text\" name=\"bank_refnum\">\n";
print "</td></tr>\n";
print "<tr><td colspan=\"2\">\n";
/* Internal cheques table */
print "<table border=\"1\" width=\"100%\"><tr class=\"tblhead\">\n";
print "<td>&nbsp;</td>\n";		/* checkbox for selecting cheque */
$l = _("Type");
print "<td>$l</td>\n";
$l = _("Receipt");
print "<td>$l</td>\n";
$l = _("Refnum");
print "<td>$l</td>\n";
$l = _("Bank");
print "<td>$l</td>\n";
$l = _("Branch");
print "<td>$l</td>\n";
$l = _("Account");
print "<td>$l</td>\n";
$l = _("Date");
print "<td>$l</td>\n";
$l = _("Sum");
print "<td>$l</td>\n";
print "</tr>\n";

$query = "SELECT * FROM $chequestbl WHERE bank_refnum='' AND prefix='$prefix'";	/* all cheques with no bank refnum */
$result = DoQuery($query, __LINE__);
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$type = $line['type'];
	$refnum = $line['refnum'];
	$cheque_num = $line['cheque_num'];
	$bank = $line['bank'];
	$branch = $line['branch'];
	$cheque_acct = $line['cheque_acct'];
	$cheque_date = FormatDate($line['cheque_date'], "mysql", "dmy");
	$sum = $line['sum'];
	print "<tr>\n";
	print "<td><input type=\"checkbox\" id=\"cheque\" name=\"cheque[]\" value=\"$cheque_num:$sum\" onchange=\"CalcSum()\"></td>\n";
	$doctype = DOC_RECEIPT;
	$url = "printdoc.php?doctype=$doctype&amp;docnum=$refnum&amp;prefix=$prefix";
	$typestr = $paymenttype[$type];
	print "<td>$typestr</td>\n";
	print "<td><a href=\"javascript:void()\" onclick=PrintDocument(\"$url\")>$refnum</A></TD>\n";
	print "<td>$cheque_num</td>\n";
	print "<td>$bank</td>\n";
	print "<td>$branch</td>\n";
	print "<td>$cheque_acct</td>\n";
	print "<td>$cheque_date</td>\n";
	print "<td>$sum</td>\n";
	print "<input type=\"hidden\" id=\"sum\" name=\"sum[]\" value=\"$sum\">\n";
	print "</tr>\n";
}
print "<tr><td colspan=\"7\">&nbsp;</td>\n";		/* spacer */
$l = _("Total");
print "<td>$l: </td>\n";
print "<td><input type=\"text\" name=\"total\" size=\"7\"></td>\n";
print "</tr>\n";
print "</table>\n";
print "</td></tr>\n";
$l = _("Deposit");
print "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
print "</table>\n";
print "</form>\n";
print "</div>\n";
print "<div class=\"lefthalf1\">\n";
ShowText('deposit');
print "</div>\n";
?>
