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
	ErrorReport($l);
	return;
}
$text='';
function PrintBankSelect() {
	global $accountstbl;
	global $prefix;

	$str= "<select name=\"account\">\n";
	
	$banks = BANKS;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$banks' AND prefix='$prefix'"; /* banks accounts */
	$result = DoQuery($query, "PrintBankSelect");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		if($num > 100) {
			$acct = $line['company'];
			$str.= "<option value=\"$num\">$acct</option>\n";
		}
	}
	$str.=  "</select>\n";
	return $str;
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

//print "<br>\n";
//print "<div class=\"form righthalf1\">\n";
$haeder = _("Cheque, credit and cash deposit");
$text= '';//"<h3>$l</h3>\n";
// print "<h3>׳³ג€�׳³ג‚×׳³ֲ§׳³ג€�׳³ֳ— ׳³ֲ©׳³ֲ§׳³ג„¢׳³ן¿½, ׳³ן¿½׳³ג€“׳³ג€¢׳³ן¿½׳³ֲ ׳³ג„¢׳³ן¿½ ׳³ג€¢׳³ֲ¨׳³ג„¢׳³ֲ©׳³ג€¢׳³ן¿½ ׳³ֲ¡׳³ן¿½׳³ג„¢׳³ֲ§׳³ג€�</h3>\n";

if($action == 'submit') {
	$account = $_POST['account'];
	if(!$account) {
		$l = _("Bank account not defined");
		ErrorReport("$l");
		return ;
	}
	$dep_date = $_POST['dep_date'];
	if(empty($dep_date)) {
		$l = _("Deposit date not defined");
		ErrorReport("$l");
		return;
	}
//	$dep_date = FormatDate($dep_date, "dmy", "mysql");
	
	$bank_refnum = GetPost('bank_refnum');
	if(empty($bank_refnum)) {
		$l = _("Bank refnum must be filled");
		ErrorReport("$l");
		return;
	}
	$cheque = $_POST['cheque'];
	if(empty($cheque)) {
		$l = _("Nothing to deposit");
		ErrorReport("$l");
		return;
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
			// First part ׳³ג€“׳³ג€÷׳³ג€¢׳³ֳ— ׳³ֲ§׳³ג€¢׳³ג‚×׳³ֳ— ׳³ֲ©׳³ג„¢׳³ֲ§׳³ג„¢׳³ן¿½ 
			$total = (float)$sum;
			if($t == 1)
				$cheque_acct = CASH;
			else
				$cheque_acct = CHEQUE;
			$tnum = Transaction($tnum, CHEQUEDEPOSIT, $cheque_acct, $bank_refnum, $val, $dep_date, '', $total);

			// Second part ׳³ג€”׳³ג€¢׳³ג€˜׳³ֳ— ׳³ג€�׳³ג€˜׳³ֲ ׳³ֲ§
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
	$text.= "<h2>$l</h2></br>\n";
}

$text.=  "<form name=\"form1\" action=\"?module=deposit&amp;action=submit\" method=\"post\">\n";
$text.=  "<table width=\"100%\" class=\"formtbl\"><tr>\n";
$l = _("Bank account");
$text.=  "<td>$l: \n";
$text.=PrintBankSelect();
$l = _("Deposit date");
$text.=  "</td><td>$l: \n";
$dep_date = date("d-m-Y");
$text.=  "<input class=\"date\" type=\"text\" id=\"dep_date\" name=\"dep_date\" value=\"$dep_date\" size=\"8\">\n";
//$text.= '<script type="text/javascript">addDatePicker("#dep_date","'.$dep_date.'");</script>';

$text.= "</td></tr>\n";
$text.= "<tr><td colspan=\"2\">\n";
$l = _("Bank refnum");
$text.= "$l: \n";
$text.= "<input type=\"text\" name=\"bank_refnum\">\n";
$text.= "</td></tr>\n";
$text.= "<tr><td colspan=\"2\">\n";
/* Internal cheques table */
$text.= "<table class=\"formy\" border=\"1\" width=\"100%\"><tr>\n";
$text.= "<th class=\"header\">&nbsp;</th>\n";		/* checkbox for selecting cheque */
$l = _("Type");
$text.= "<th class=\"header\">$l</th>\n";
$l = _("Receipt");
$text.= "<th class=\"header\">$l</th>\n";
$l = _("Refnum");
$text.= "<th class=\"header\">$l</th>\n";
$l = _("Bank");
$text.= "<th class=\"header\">$l</th>\n";
$l = _("Branch");
$text.= "<th class=\"header\">$l</th>\n";
$l = _("Account");
$text.= "<th class=\"header\">$l</th>\n";
$l = _("Date");
$text.= "<th class=\"header\">$l</td>\n";
$l = _("Sum");
$text.= "<th class=\"header\">$l</th>\n";
$text.= "</tr>\n";

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
	$text.= "<tr>\n";
	$text.= "<td><input type=\"checkbox\" id=\"cheque\" name=\"cheque[]\" value=\"$cheque_num:$sum\" onchange=\"CalcSum()\"></td>\n";
	$doctype = DOC_RECEIPT;
	$url = "printdoc.php?doctype=$doctype&amp;docnum=$refnum&amp;prefix=$prefix";
	$typestr = $paymenttype[$type];
	$text.= "<td>$typestr</td>\n";
	$text.= "<td><a href=\"javascript:void()\" onclick=PrintDocument(\"$url\")>$refnum</A></TD>\n";
	$text.= "<td>$cheque_num</td>\n";
	$text.= "<td>$bank</td>\n";
	$text.= "<td>$branch</td>\n";
	$text.= "<td>$cheque_acct</td>\n";
	$text.= "<td>$cheque_date</td>\n";
	$text.= "<td>$sum<input type=\"hidden\" id=\"sum\" name=\"sum[]\" value=\"$sum\"></td>\n";
	//$text.= "\n";
	$text.= "</tr>\n";
}
$text.= "<tr><td colspan=\"7\">&nbsp;</td>\n";		/* spacer */
$l = _("Total");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"total\" size=\"7\"></td>\n";
$text.= "</tr>\n";
$text.= "</table>\n";
$text.= "</td></tr>\n";
$l = _("Deposit");
$text.= "<tr><td colspan=\"2\" align=\"center\"><a href=\"javascript:document.form1.submit();\" class=\"btnaction\">$l</a></td>\n";
$text.= "</table>\n";
$text.= "</form>\n";
if(!$ismobile)
	createForm($text, $haeder,'',750,'','',1,getHelp());
else
	print $text;
//print "</div>\n";

?>
