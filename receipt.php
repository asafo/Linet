<?PHP
//M:קבלות
/*
 | receipts module for Drorit
 | Written by Ori Idan August 2009
 | Written by Adam Ben Hour 2011
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl;
global $receiptstbl, $chequestbl;
global $docstbl, $docdetailstbl;
global $creditcompanies;
global $CompArray;
global $CurrArray;
global $DocType;
global $paymenttype;
global $creditcompanies;
global $creditarr;
global $banksarr;

$step = isset($_GET['step']) ? $_GET['step'] : $step;
if (isset($_GET['step'])) $step=$_GET['step']; else $step=0;
// print "module: $module<br>\n";

?>
<script type="text/javascript">

function CalcSum() {
	var vals = document.form1.invoice;
	var inv_total = document.form1.inv_total;
	var t = document.form1.invtotal;
	
	size = vals.length;
	
	// alert("Length: " + size);
		
	sum = parseFloat("0.0");
	t.value = '';
	if(size) {
		for(i = 0; i < size; i++) {
			if(vals[i].checked) {
				// alert("value: " + vals[i].value + ", " + inv_total[i].value);
				sum += parseFloat(inv_total[i].value);
			}
		}
	}
	else {
		if(vals.checked)
			sum = parseFloat(inv_total.value);
	}
	t.value = sum;
}

<?PHP
if($module == 'receipt') {
	print "function SetCustomer() {\n";
	print "\tvar comp = document.form1.company;\n";
	print "\tvar add = document.form1.address;\n";
	print "\tvar city = document.form1.city;\n";
	print "\tvar zip = document.form1.zip;\n";
	print "\tvar vatnum = document.form1.vatnum;\n";
	print "\tvar cust = document.form1.account.value;\n";
	
	print "\tswitch (cust) {\n";
	/* Print large switch structure for all customers */
	global $accountstbl;
	$t = CUSTOMER;
	$query = "SELECT num,pay_terms,company,address,city,zip,vatnum FROM $accountstbl WHERE type='$t' AND prefix='$prefix' ORDER BY company";
	//print($query);
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");

	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];


		$company = trim($line['company']);
		$str1 = nl2br($company);/*
		if($str1 != $company) {
			print "company: $str1\n";
			$company = str_replace('\n', '', $company);
			
		}*///adam:
		$CompArray[$num] = $line['company'];	// for use later to print SELECT box
		$company = FromHtml($line['company']);//adam:
		$address = FromHtml($line['address']);
		$city = FromHtml($line['city']);
		$zip = FromHtml($line['zip']);
		$vatnum = FromHtml($line['vatnum']);
		
		print "\t\tcase '$num': \n";
		print "\t\t\tcomp.value = \"$company\";\n"; 
		print "\t\t\tadd.value = \"$address\";\n";
		print "\t\t\tcity.value =  \"$city\";\n";
		print "\t\t\tzip.value = \"$zip\";\n";
		print "\t\t\tvatnum.value = \"$vatnum\";\n";
		print "\t\t\tbreak;\n";

	} 
	/* Reset variables */
	$company = '';
	$address = '';
	$city = '';
	$zip = '';

	print "\t}\n";
	print "}\n";
}

?>

</script>

<?PHP
if($module == 'receipt') {
	function GetNextDocNum($doctype) {
		global $fiscalyear;
		global $docstbl, $receiptstbl, $companiestbl;
		global $prefix;
	
		if($doctype == DOC_RECEIPT)
			$query = "SELECT MAX(refnum) FROM $receiptstbl WHERE prefix='$prefix'";
		else
			$query = "SELECT MAX(docnum) FROM $docstbl WHERE doctype='$doctype' AND prefix='$prefix'";
		$line = __LINE__;
		$file = __FILE__;
		$result = DoQuery($query, "$file: $line");

		$line = mysql_fetch_array($result, MYSQL_NUM);
		$n = $line[0];
		if($n == 0) {
			$query = "SELECT num1,num2,num3,num4,num5,num6,num7,num8 FROM $companiestbl WHERE prefix='$prefix'";
			$line = __LINE__;
			$file = __FILE__;
			$result = DoQuery($query, "$file: $line");
	
			$line = mysql_fetch_array($result, MYSQL_NUM);
			$n = $line[$doctype - 1];
			if($n > 0)
				$n--;
		}
		return $n + 1;
	}
}
if(!function_exists(PrintPaymentType)) {
	function PrintPaymentType($type) {
		global $paymenttype;
		
		$text= "<select class=\"ptype\" name=\"ptype[]\" >\n";
		foreach($paymenttype as $num => $v) {
			$text.= "<option value=\"$num\" ";
			if($type == $num)
				$text.= "selected=\"selected\"";
			$text.= ">$v</option>\n";
		}
		$text.= "</select>\n";
		return $text;
	}

	function PrintCreditCompany($c) {
		global $creditcompanies;
		
		$text= "<select name=\"creditcompany[]\">\n";
		foreach($creditcompanies as $num => $v) {
			$text.= "<option value=\"$num\" ";
			if($c == $num)
				$text.= "selected=\"selected\"";
			$text.= ">$v</option>\n";
		}
		$text.= "</select>\n";
		return $text;
	}
}

if($step == 3) {
	/* $refnum is defined if we are called from docsadmin */
	if($module == 'receipt')
		$refnum = isset($_POST['refnum']) ? (int)$_POST['refnum'] : $refnum;
//	print "refnum: $refnum<br>\n";
	$account = (int)$_POST['account'];
	$company = GetPost('company');
	$address = GetPost('address');
	$city = GetPost('city');
	$zip = GetPost('zip');
	$vatnum = GetPost('vatnum');
	$due_date = $_POST['due_date'];
	$src_tax = (float)$_POST['src_tax'];
	
	/* now get cheques data */
	$type = $_POST['ptype'];
	$creditcompany = $_POST['creditcompany'];
	$cheque_num = $_POST['cheque_num'];
	$bank = $_POST['bank'];
	$branch = $_POST['branch'];
	$cheque_acct = $_POST['cheque_acct'];
	$date = $_POST['date'];
	$sum = $_POST['sum'];
	
	/* Now the real thing, generate transactions for receipt */
	/* first check if this receipt already registered */
	$query = "SELECT num FROM $receiptstbl WHERE refnum='$refnum' AND prefix='$prefix'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "receipt");
	$n = mysql_num_rows($result);
	if($n) {
		$l = _("Receipt");
		print "<br><h1>$l: \n";
		print "$refnum\n";
		$l = _("Already registered");
		print "$l</h1>\n";
		exit;
	}

	$cheques_sum = 0.0;
	$tnum = 0;
	$tnum = Transaction(0, RECEIPT, CUSTTAX, $refnum, '', $due_date, '', $src_tax * -1.0);
	$tnum = Transaction($tnum, RECEIPT, $account, $refnum, '', $due_date, '', $src_tax);
	foreach($sum as $key => $val) {
		$type1 = $type[$key];
		$crcompany = $creditcompany[$key];
		$chknum = htmlspecialchars($cheque_num[$key]);
		$bnk = htmlspecialchars($bank[$key]);
		$brnch = htmlspecialchars($branch[$key]);
		$acct = htmlspecialchars($cheque_acct[$key]);
		$cheque_date = $date[$key];
		$cheque_date = FormatDate($cheque_date, "dmy", "mysql");
		
		if($type1 != 1) {
			$query = "INSERT INTO $chequestbl VALUES ('$prefix', '$refnum', '$type1', '$crcompany', '$chknum', '$bnk', '$brnch', '$acct', '$cheque_date', '$val', '', '')";
			// print "Query: $query<BR>\n";
			$result = mysql_query($query);
			if(!$result) {
				print "Query: $query<BR>\n";
				echo mysql_error();
				exit;
			}
		}
		$cheques_sum += $val;
		$tnum = Transaction($tnum, RECEIPT, $account, $refnum, $chknum, $due_date, '', $val);
		if($type != 1)
			$tnum = Transaction($tnum, RECEIPT, CHEQUE, $refnum, $chknum, $due_date, '', $val * -1.0);
		else
			$tnum = Transaction($tnum, RECEIPT, CASH, $refnum, $chknum, $due_date, '', $val * -1.0);
	}
	if($module != 'receipt')
		$invoices = $refnum;
	else
		$invoices = '';
	$cheques_sum += $src_tax;
	$due_date = FormatDate($due_date, "dmy", "mysql");
	$query = "INSERT INTO $receiptstbl VALUES(NULL, '$prefix', ";
	$query .= "'$account', '$company', '$address', '$city', '$zip', '$vatnum', '$refnum', '$due_date', ";
	$query .= "'$invoices', '$comments', '$cheques_sum', '$src_tax', '0')";
	$result = DoQuery($query, 'receipt');
	
}

if($step > 0) {
	$account = (int)$_POST['account'];
	$company = GetPost('company');
	$address = GetPost('address');
//	$address = stripslashes($address);
	$city = GetPost('city');
	$zip = GetPost('zip');
	$vatnum = GetPost('vatnum');
	$due_date = $_POST['due_date'];
	$src_tax = (float)$_POST['src_tax'];
	
	/* now get cheques data */
	$type = $_POST['ptype'];
	$creditcompany = $_POST['creditcompany'];
	$cheque_num = $_POST['cheque_num'];
	$bank = $_POST['bank'];
	$branch = $_POST['branch'];
	$cheque_acct = $_POST['cheque_acct'];
	$date = $_POST['date'];
	$sum = $_POST['sum'];

	if($module == 'receipt') {
		$refnum = isset($_POST['refnum']) ? $_POST['refnum'] : GetNextDocNum(DOC_RECEIPT);

		if($step < 3)
			print "<form name=\"form1\" action=\"?module=receipt&step=3\" method=\"post\">\n";
		print "<div style=\"border:1px solid;width:90%;margin:5px\">\n";
		print "<table border=\"0\" width=\"100%\" align=\"center\" class=\"formtbl\"><tr>\n";
		$l = _("To");
		print "<td style=\"width:10%\">$l: </td>\n";
		print "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
		print "<td style=\"width:60%\">$company <input type=\"hidden\" name=\"company\" value=\"$company\" /></td>\n";
		$l = _("Date");
		print "<td style=\"width:10%\">$l: </td>\n";
		print "<td>$due_date <input type=\"hidden\" name=\"due_date\" value=\"$due_date\" /></td>\n";
		print "</tr><tr>\n";
		print "<td>&nbsp;</td>\n";	/* empty column */
		print "<td>$address <input type=\"hidden\" name=\"address\" value=\"$address\" /></td>\n";
		print "</tr><tr>\n";
		print "<td>&nbsp;</td>\n";	/* empty column */
		print "<td>$city $zip \n";
		$l = _("Reg. num");
		print "$l: $vatnum</td>\n";
		print "<input type=\"hidden\" name=\"city\" value=\"$city\" /><input type=\"hidden\" name=\"zip\" value=\"$zip\" />\n";
		print "<input type=\"hidden\" name=\"vatnum\" value=\"$vatnum\" />\n";
		print "</tr><tr>\n";
		$l = _("Receipt number");
		print "<td colspan=\"4\" align=\"center\"><h1 style=\"text-align:center\">$l: \n";
		print "$refnum </h1></td>\n";
		print "<input type=\"hidden\" name=\"refnum\" value=\"$refnum\" />\n";
		print "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
		print "</tr><tr>\n";
		print "</tr><tr><td colspan=\"4\" align=\"center\">\n";
	}
	/* table for cheques data */
	print "<table border=\"0\">\n";
	/* header line */
	print "<tr class=\"tblhead1\">\n";
	print "<td style=\"width:7em\">אמצאי תשלום </td>\n";
	print "<td style=\"width:6em\">חברת אשראי&nbsp;</td>\n";
	print "<td style=\"width:8em\">אסמכתא\\מס' שיק</td>\n";
	print "<td style=\"width:12em\">בנק&nbsp;</td>\n";
	print "<td style=\"width:3em\">סניף&nbsp;</td>\n";
	print "<td style=\"width:6em\">מס' חשבון&nbsp;</td>\n";
	print "<td style=\"width:6em\">תאריך&nbsp;</td>\n";
	print "<td>סכום</td>\n";
	print "</tr>\n";
	$total_sum = 0.0;
	foreach($sum as $index => $val) {
		// print "val: $val<br/>\n";	/* debug */
		if(empty($val))
			continue;
		print "<tr>\n";
		print "<td>\n";
		$t = $type[$index];
		$ts = $paymenttype[$t];
		print "$ts ";
		print "<input type=\"hidden\" name=\"ptype[]\" value=\"$t\" />\n";
		print "</td><td>\n";
		$t = $creditcompany[$index];
		$ts = $creditcompanies[$t];
		print "$ts <input type=\"hidden\" name=\"creditcompany[]\" value=\"$t\" />\n";
		print "</td>\n";
		$cn = htmlspecialchars($cheque_num[$index], ENT_QUOTES);
		print "<td>$cn</td>\n";
		print "<input type=\"hidden\" name=\"cheque_num[]\" value=\"$cn\" />\n";
		$bn = $bank[$index];
//		print_r($banksarr);
		$bs = $banksarr[$bn];
		print "<td>$bn - $bs</td>\n";
		print "<input type=\"hidden\" name=\"bank[]\" value=\"$bank[$index]\" />\n";
		print "<td>$branch[$index]</td>\n";
		print "<input type=\"hidden\" name=\"branch[]\" value=\"$branch[$index]\" />\n";
		print "<td>$cheque_acct[$index]</td>\n";
		print "<input type=\"hidden\" name=\"cheque_acct[]\" value=\"$cheque_acct[$index]\" />\n";
		print "<td>$date[$index]</td>\n";
		print "<input type=\"hidden\" name=\"date[]\" value=\"$date[$index]\" />\n";
		print "<td>$val</td>\n";
		print "<input type=\"hidden\" name=\"sum[]\" value=\"$val\" />\n";
		print "</tr>\n";
		$total_sum += $val;
	}
	print "<tr><td colspan=\"6\" >&nbsp;</td>\n";
	$l = _("Source tax");
	print "<td>$l: </td>\n";
	print "<td>$src_tax<input type=\"hidden\" name=\"src_tax\" value=\"$src_tax\" /></td></tr>\n";
	print "<tr><td colspan=\"6\">&nbsp;</td>\n";		/* spacer */
	$l = _("Total");
	print "<td><b>$l: </b></td>\n";
	$total_sum += $src_tax;
	print "<td><b>$total_sum</b></td>\n";
	print "</tr>\n";
	print "</table>\n";
	if($module == 'receipt') {
		print "</td></tr>\n";
		print "<tr><td colspan=\"4\" align=\"center\">\n";
		if($step < 3) {
			$l = _("Submit");
			print "<input type=\"submit\" value=\"$l\" />\n";
		}
		else {	/* show email form or print */
//			print "<input type=\"button\" value=\"שלח בדואר אלקטרוני\" ";
//			print "onclick=\"window.location.href='?module=emaildoc&doctype=$doctype&docnum=$docnum'\">\n";
			print "&nbsp;&nbsp;<input type=\"button\" value=\"הדפס\" ";
			print "onclick=\"window.open('printdoc.php?doctype=$doctype&docnum=$docnum&prefix=$prefix&print_win=1', 'printwin', 'width=800,height=600,scrollbar=yes')\" />\n";
		}

		print "</td></tr>\n";
		print "</table>\n";
		if($step < 3)
			print "</form>\n";
	}
	else
		return;
}

if($step == 0) {
	if($module == 'receipt') {
		$haeder = _("Receipts");
		//$text.= "<div class=\"form\"><h3>$l</h3>\n";

		$text.= "<form name=\"form1\" action=\"?module=receipt&amp;step=1\" method=\"post\">\n";
		$text.= "<table border=\"0\" width=\"80%\" align=\"center\" class=\"formtbl\">\n";
		$l = _("Customer");
		$text.= "<tr><td width=\"70%\">$l: \n";
		$text.= PrintCustomerSelect($account);
		$text.= "</td>\n";
		$l = _("Date");
		$text.= "<td>$l: \n";
	
		$valdate = date('d-m-Y');
		$due_date = $valdate;

		$text.= "<input type=\"text\" id=\"due_date\" name=\"due_date\" value=\"$valdate\" size=\"8\" />\n";
$text.='<script type="text/javascript">addDatePicker("#due_date","'.$valdate.'");</script>';

		$text.= "</td>\n";
	//	print "<input type=hidden name=valdate value=\"$valdate\">\n";
		$text.= "</tr><tr>\n";
		$l = _("Company");
		$text.= "<td colspan=\"2\">$l: \n";
		$text.= "<input type=\"text\" name=\"company\" size=\"40\" value=\"$company\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("Address");
		$text.= "<td colspan=\"2\">$l: \n";
		$address = htmlspecialchars($address);
		$text.= "<input type=\"text\" name=\"address\" size=\"50\" value=\"$address\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("City");
		$text.= "<td colspan=\"4\">$l: \n";
		$text.= "<input type=\"text\" name=\"city\" value=\"$city\" />&nbsp;&nbsp;\n";
		$l = _("Zip");
		$text.= "$l: \n";
		$text.= "<input type=\"text\" name=\"zip\" value=\"$zip\" size=\"6\" />\n";
		$l = _("Reg. num");
		$text.= "$l: \n";
		$text.= "<input type=\"text\" name=\"vatnum\" value=\"$vatnum\" size=\"8\" />\n";
		$text.= "</td>\n";
		$text.= "</tr><tr><td colspan=\"2\" align=\"center\">\n";
		
	}
	$text.= "<table border=\"1\">\n";		/* Internal table for details */
	/* header line */
	$text.= "<tr class=\"tblhead1\">\n";
	$l = _("Payment method");
	$text.= "<td>$l</td>\n";
	$l = _("Credit company");
	$text.= "<td class=\"crdhide\">$l</td>\n";
	$l = _("Number");
	$text.= "<td>$l</td>\n";
	$l = _("Bank");
	$text.= "<td class=\"chkhide\">$l</td>\n";
	$l = _("Branch");
	$text.= "<td class=\"chkhide\">$l</td>\n";
	$l = _("Account no.");
	$text.= "<td class=\"chkhide\">$l</td>\n";
	$l = _("Date");
	$text.= "<td>$l</td>\n";
	$l = _("Sum");
	$text.= "<td>$l</td>\n";
	$text.= "</tr>\n";
	for($i = 0; $i < 4; $i++) {
		$text.= "<tr>\n";
		$text.= "<td>\n";
		$text.= PrintPaymentType(0);
		$text.= "</td><td class=\"crdhide\">\n";
		$text.=PrintCreditCompany(0);
		$text.= "</td>\n";
		$text.= "<td><input type=\"text\" name=\"cheque_num[]\" size=\"8\" /></td>\n";
		$text.= "<td class=\"chkhide\"><input type=\"text\" name=\"bank[]\" size=\"3\" /></td>\n";
		$text.= "<td class=\"chkhide\"><input type=\"text\" name=\"branch[]\" size=\"3\" /></td>\n";
		$text.= "<td class=\"chkhide\"><input type=\"text\" name=\"cheque_acct[]\" size=\"8\" /></td>\n";
		$text.= "<td><input type=\"text\" name=\"date[]\" size=\"7\" /></td>\n";
		$text.= "<td><input type=\"text\" class=\"sum\" name=\"sum[]\" size=\"6\" /></td>\n";
		$text.= "</tr>\n";
	}
	$l = _("Source tax");
	$text.= "<tr><td colspan=\"7\" align=\"left\">$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"src_tax\" size=\"6\" /></td>\n\t</tr>";
	$text.= "</table>\n";
	if($module == 'receipt') {
		$text.= "</td></tr>\n";
		$l = _("Continue");
		$text.= "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\" /></td></tr>\n";
		$text.= "</table>\n";
		$text.= "</form>\n";
		createForm($text,$haeder,'',800);
		//print "</div>";//adam:form
	}
}

?>
