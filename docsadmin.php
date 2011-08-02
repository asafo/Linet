<?PHP
//M:ניהול מסמכים עסקיים
/*
 | docsadmin
 | Business document module for Drorit
 | Written by Ori Idan November 2009
 | Written by Adam Ben Hour 2011
 */
global $logo, $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl;
global $docstbl, $docdetailstbl, $currencytbl;
global $CompArray;
global $CurrArray;
global $DocType;
global $paymentarr;
global $creditarr;
global $banksarr;
$text='';
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

/* Set currency array used in Currency select */
$query = "SELECT * FROM $currencytbl";
$result = mysql_query($query);
if(!$result) {
	echo mysql_error();
	exit;
}
$l = _("NIS");
$CurrArray[0] = "0::$l";
$ci = 1;
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['curnum'];
	$sign = $line['sign'];
	$CurrArray[$c++] = "$num::$sign";
}

/* Get fiscal year */
$d = date("m-Y");
list($m, $fiscalyear) = explode('-', $d);
if($m < 4)
	$fiscalyear--;

for($i = 0; $i <= 5; $i++) {
	$qty[$i] = 1;
}

$step = isset($_POST['step']) ? $_POST['step'] : 0;
$step = isset($_GET['step']) ? (int)$_GET['step'] : $step;


/*
<script>
    $(document).ready(function() {
        $( "#AC" ).autocomplete({
		 source: "index.php?action=lister&amp;data=items",
        });
    });
</script>
	*/
	?>
<script type="text/javascript">

function PrintDocument(purl) {
	window.open(purl, 'printwin', 'width=800,height=600,scrollbar=yes');
}

function OpenRatesWin(date) {
	window.open("curadmin.php?date=" + date, 'ratewin1', 'width=500,height=400');
}

function GetCurrencyIndex(index, val) {
	curr = document.form1.currency[index];
	
<?PHP print "\tfor(i = 0; i < $ci; i++) {\n"; ?>
		if(curr.options[i].value == val) {
			curr.selectedIndex = i;
			break;
		}
	}
}

function CalcPrice(index) {
	var qty = document.getElementById('QTY'+index);
	//var qty = qtya[index];
	var uprice = document.getElementById('UNT'+index);
	//var uprice = upricea[index];
	var price = document.getElementById('PRICE'+index);
	//var price = pricea[index];
	
	price.value = uprice.value * qty.value;
}

function SetPartDetails(index) {
	var sbla='cat_num['+index+']';
	//alert(sbla);
	var part = index;//document.getElementById(sbla).value;
	//alert(sbla);
	var part = document.getElementById('AC'+index);
	//var  = parta[index].selectedIndex;
	var desc = document.getElementById('DESC'+index);
	//var desc = desca[index];
	var uprice = document.getElementById('UNT'+index);
	//var uprice = upricea[index];
	var cat_num = document.getElementById('AC'+index).value;
	//var cat_num = cat_numa['AC'+index].value;
	//	document.write('Hello World!');
	if(cat_num == -1) {
<?PHP
		print "\tvar url1 = 'additem.php?prefix=$prefix&amp;index='\n";
		print "\tvar url = url1 + index;\n";
		print "\twindow.open(url, 'additem', 'height=300,width=500');\n";
		print "\treturn;\n";
?>
	}

	switch (cat_num) {
<?PHP
	global $catalogtbl;
	$query = "SELECT num,name,defprice FROM $itemstbl WHERE prefix='$prefix' ORDER BY name";//adam:
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$cat_num = $line['num'];
		$name = addslashes($line['name']);
		$defprice = $line['defprice'];
		print "\t\tcase '$cat_num':\n";
		print "\t\t\tdesc.value = \"$name\";\n";
		print "\t\t\tuprice.value = \"$defprice\";\n";
		//print "\t\t\tqty = \"1\";\n"; //adam:
		print "\t\t\taddEvent()\n";
		print "\t\t\tvar bla =document.getElementById('QTY'+index).focus();\n"; //document.form1.qty['QTY'+index].focus();\n";
		print "\t\t\tbreak\n";;
	}
	/* reset variables */
	$cat_num = '';
	$name = '';
	$price = '';
?>
	}
}

function CalcDueDate(valdate, pay_terms, em) {
	<?PHP /* First convert valdate to day, month, year */ ?>
	var duedate = document.getElementById('due_date');//document.form1.due_date;
	var dstr = valdate;
	var darr = dstr.split("-"); <?PHP /* darr is now an array of day, month, year */ ?>
	var day = parseInt(darr[0]);
	var month = parseFloat(darr[1]);
	var year = parseInt(darr[2]);

	if(em) {	<?PHP /* go to beginning of next month */ ?>
		month += 1;
		if(month > 12) {
			month = 1;
			year += 1;
		}
		day = 1;
		duedate.value = day + "-" + month + "-" + year;
	}

	D = new Date(year, month - 1, day);
	D.setDate(D.getDate() + pay_terms);
	var day = D.getDate();
	var month = D.getMonth() + 1;
	if(month > 12) {
		month = 1;
		year += 1;
	}
	var year = D.getFullYear();
	duedate.value = day + "-" + month + "-" + year;
}


function SetCustomer() {
	var comp = document.form1.company;
	var add = document.form1.address;
	var city = document.form1.city;
	var zip = document.form1.zip;
	var vatnum = document.form1.vatnum;
	var valdate = document.form1.idate;
	var cust = document.form1.account.value;
	var cust3 = document.form3.account;
	var cust4 = document.form4.account;
	var cust5 = document.form5.account;
	
	switch (cust) {
<?PHP
	/* Print large switch structure for all customers */
	global $accountstbl;
	$query = "SELECT num,pay_terms,company,address,city,zip,vatnum FROM $accountstbl WHERE type='0' AND prefix='$prefix' ORDER BY company";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");

	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$pay_terms = $line['pay_terms'];
		if($pay_terms < 0) {
			$em = 1;	/* pay_terms are days after end of current month */
			$pay_terms = $pay_terms * -1;
		}
		else
			$em = 0;
		if($pay_terms == '')
			$pay_terms = 0;

		$CompArray[$num] = $line['company'];	/* for use later to print SELECT box */
		$company = FromHtml($line['company']);
		$address = FromHtml($line['address']);
		$city = FromHtml($line['city']);
		$zip = FromHtml($line['zip']);
		$vatnum = FromHtml($line['vatnum']);
		print "\t\tcase '$num': \n";
		print "\t\t\tcust3.value = \"$num\";\n";
		print "\t\t\tcust4.value = \"$num\";\n";
		print "\t\t\tcust5.value = \"$num\";\n";
		print "\t\t\tcomp.value = \"$company\";\n"; 
		print "\t\t\tadd.value = \"$address\";\n";
		print "\t\t\tcity.value =  \"$city\";\n";
		print "\t\t\tzip.value = \"$zip\";\n";
		print "\t\t\tvatnum.value = \"$vatnum\";\n";
		print "\t\t\tCalcDueDate(valdate.value, $pay_terms, $em);\n";//adam:$issue_date valdate.value
		print "\t\t\tbreak;\n";
	}
	/* Reset variables */
	$company = '';
	$address = '';
	$city = '';
	$zip = '';
?>
	}
}

function TypeSelChange() {
	var i = document.form1.payment.selectedIndex;
	if(i == 1) {
		document.getElementById('bankdiv').style.display = 'block';
		document.getElementById('crd').style.display = 'none';
	}else if(i == 3) {
		document.getElementById('crd').style.display = 'block';
		document.getElementById('bankdiv').style.display = 'none';
	}else {
		document.getElementById('crd').style.display = 'none';
		document.getElementById('bankdiv').style.display = 'none';
		document.getElementById('refnum1').style.display = 'none';
	}
}

</script>
<?PHP
function PrintPaymentSelect($def) {
	global $paymentarr;

	$text.= "<select name=\"payment\" onchange=\"TypeSelChange()\">\n";
	foreach($paymentarr as $n => $v) {
		if($n == $def)
			$text.= "<option value=\"$n\" selected>$v</option>\n";
		else
			$text.= "<option value=\"$n\">$v</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function PrintCreditSelect($def, $payment) {
	global $creditarr;
	
	if($payment == 3)
		$text.= "<select name=\"creditcomp\" id=\"crd\" style=\"display:block\">\n";
	else
		$text.= "<select name=\"creditcomp\" id=\"crd\" style=\"display:none\">\n";
	foreach($creditarr as $n => $v) {
		if($n == $def)
			$text.= "<option value=\"$n\" selected>$v</option>\n";
		else
			$text.= "<option value=\"$n\">$v</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function PrintBankSelect($def) {
	global $banksarr;
	
	$text.= "<select name=\"bank\">\n";
	foreach($banksarr as $n => $b) {
		$s = ($b == $def) ? " selected" : "";
		$text.= "<option value=\"$n\"$s>$n - $b</option>\n";
	}
	return $text;
}

function CalcVAT($sum) {
	global $companiestbl;
	global $prefix;

	$query = "SELECT vat FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, __LINE__);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$vat = $line[0];
	
	return round($sum * $vat / 100, 2);
}

function PrintPartNumSelect($i, $defnum) {
	global $itemstbl;
	global $prefix;

	$text.= "<select class=\"cat_num\" name=\"cat_num[]\" onchange=\"SetPartDetails($i)\">\n";
	$query = "SELECT num,name FROM $itemstbl WHERE prefix='$prefix' ORDER by name";//adam:
	$result = DoQuery($query, "docsadmin.php");
	$l = _("Choose item");
	$text.= "<option value=\"0\">-- $l --</option>\n";
	$l = _("Add item");
	$text.= "<option value=\"-1\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$cat_num = $line['num'];
		$name = $line['name'];
		$text.= "<option value=\"$cat_num\"";
		if($cat_num == $defnum)
			$text.= " selected";
		$text.= ">$name</option>\n";
	}
	$text.= "</select>\n";//*/
	return $text;
}

function PrintCurrencySelect($defnum) {
	global $CurrArray;
	
	$text.= "<select class=\"currency\" name=\"currency[]\">\n";
	foreach($CurrArray as $val) {
		list($num, $sign) = split("::", $val);
		$text.= "<option value=\"$num\"";
		if($num == $defnum)
			$text.= " selected";
		$text.= ">$sign</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function NeedVat($cat_num) {
	global $itemstbl, $accountstbl;
	global $prefix;

	$query = "SELECT account FROM $itemstbl WHERE num='$cat_num' AND prefix='$prefix'";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$n = $line[0];
	/* $n now contains account number, now get account type from accounts table */
	$query = "SELECT type,src_tax FROM $accountstbl WHERE num='$n' AND prefix='$prefix'";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$n = $line[0];
	$v = $line[1];

	if($v == 0)
		return 0;
	if($n == ASSETS)
		return 2;	/* used for ASSETS VAT */
	return 1;
}

function GetAccountFromCatNum($cat_num) {
	global $itemstbl;
	global $prefix;

	$query = "SELECT account FROM $itemstbl WHERE num='$cat_num' AND prefix='$prefix'";

	$result = DoQuery($query, "GetAccountFromCatNum");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$acct = $line[0];
	return $acct;
}

function GetNextDocNum($doctype) {
	global $fiscalyear;
	global $docstbl, $companiestbl;
	global $prefix;
	
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

function GetCurrencySymbol($curnum) {
	global $CurrArray;

	foreach($CurrArray as $val) {
		list($num, $sign) = split("::", $val);
		if($num == $curnum) {
			return $sign;
		}
	}
}

function CalcNISPrice($price, $currnum, $date) {
	global $ratestbl;

	if($currnum != 0) {
		$query = "SELECT rate FROM $ratestbl WHERE curnum='$currnum' AND date='$date'";
		//print "Query: $query<BR>\n";
		$result = mysql_query($query);
		if(!$result) {
			echo mysql_error();
			exit;
		}
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		//print_r($line);
		$rate = $line['rate'];
		return round($price * $rate, 2);
	}
	return $price;
}

if($step == 5) {	/* Send email */
	$doctype = (int)$_POST['type'];
	$docnum = (int)$_POST['docnum'];
	$account = (int)$_POST['account'];
	require("emaildoc.php");
	return;
}
if($step == 3) {	/* final step, put data in tables */
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$fromnum = (int)$_POST['fromnum'];
	$fromdoc = (int)$_POST['fromdoc'];
	
	$doctype = (int)$_POST['type'];
	$docnum = (int)$_POST['docnum'];

	global $docstbl;
	/* Make sure there is no document with same details */
	$query = "SELECT num FROM $docstbl WHERE doctype='$doctype' AND docnum='$docnum' AND prefix='$prefix'";
	$result = DoQuery($query, "step3");
	$n = mysql_num_rows($result);
	if($n == 0) {	/* this is first time */
		$account = (int)$_POST['account'];	
		$due_date = GetPost('due_date');
		list($day, $month, $year) = split("[/.-]", $due_date);
		$due_date = "$year-$month-$day";	
		$company = GetPost('company');
		$address = GetPost('address');
		$city = GetPost('city');
		$zip = GetPost('zip');
		$vatnum = GetPost('vatnum');
		$comments = GetPost('comments');
		
		$issue_date = GetPost('valdate');
		if(empty($issue_date))
			$issue_date = GetPost('idate');
		list($day, $month, $year) = split("[/.-]", $issue_date);
		$issue_date = "$year-$month-$day";	
		$refnum = GetPost('refnum');
		
		/* Collect total price and VAT information */
		$sub_total = (float)$_POST['sub_total'];
		$novat_total = (float)$_POST['novat_total'];
		$asset_vat = (float)$_POST['asset_vat'];
		$vat = (float)$_POST['vat'];
		$total = (float)$_POST['total'];
		
		/* Put data into table */
		$query = "INSERT INTO $docstbl VALUES(NULL, '$prefix', ";
		$query .= "'$doctype', '$docnum', '$account', '$company', '$address', '$city', '$zip', '$vatnum', ";
		$query .= "'$refnum', '$issue_date', '$due_date', ";
		$query .= "'$sub_total', '$novat_total', '$vat', '$total', '0', '0', '$comments',0)";
		$result = DoQuery($query, "step3");
		$num = mysql_insert_id();
		
		/* All general details are collected, now collect items */
		/* All items are array of items */
		$cat_num = $_POST['cat_num'];
		$description = $_POST['description'];
		$qty = $_POST['qty'];
		$unit_price = $_POST['unit_price'];
		$price = $_POST['price'];
		$currency = $_POST['currency'];
		$nisprice = $_POST['nisprice'];
		
		$i = 0;
		global $docdetailstbl;
		foreach($cat_num as $val) {
			$acct = GetAccountFromCatNum($val);
			if($acct == 0) {
				$l = _("Income account not defined");
				ErrorReport("$l");
				exit;
			}
			$query = "INSERT INTO $docdetailstbl VALUES('$prefix', '$num', '$val', ";
			$desc = htmlspecialchars($description[$i], ENT_QUOTES);
			$n = (int)$qty[$i];
			$uprice = (float)$unit_price[$i];
			$p = (float)$price[$i];
			$c = (int)$currency[$i];
			$np = (float)$nisprice[$i];
			
			$query .= "'$desc', '$n', '$uprice', '$c', '$p', '$np')";
//			print "<div dir=ltr>\n";
//			print "Query: $query<BR>\n";
//			print "</div>\n";
			$result = DoQuery($query, "step3");
			
			$i++;
			
			/* Update inventory inventory */
			$query = "SELECT ammount FROM $itemstbl ";
			$query .= "WHERE num='$val' AND prefix='$prefix'";
			$line = __LINE__;
			$file = __FILE__;
			$result = DoQuery($query, "$file: $line");
			$line = mysql_fetch_array($result, MYSQL_NUM);
			$inventory_qty = $line[0];
			if($doctype == DOC_INVOICE) 
				$inventory_qty -= $n;
			else if($doctype == DOC_CREDIT)
				$inventory_qty += $n;
			$query = "UPDATE $itemstbl SET ammount='$inventory_qty' ";
			$query .= "WHERE num='$val' AND prefix='$prefix'";
			$line = __LINE__;
			$file = __FILE__;
			$result = DoQuery($query, "$file: $line");
		}
		/* Close from doc */
		if($fromdoc) {
			$query = "UPDATE $docstbl \n";
			$query .= "SET status='1' \n";	/* 1 means closed */
			$query .= "WHERE docnum='$fromnum' AND doctype='$fromdoc' AND prefix='$prefix'";
			$line = __LINE__;
			$file = __FILE__;
			$result = DoQuery($query, "$file: $line");
		}
		if(($doctype == DOC_INVOICE) || ($doctype == DOC_CREDIT)) {
			/* Write transactions */
			/* Transaction 1 חובת הלקוח בסכום החשבונית */
			if($doctype == DOC_INVOICE)
				$t = $total * -1.0;
			else
				$t = $total;
			$issue_date = FormatDate($issue_date, "mysql", "dmy");
			$tnum = Transaction(0, INVOICE, $account, $docnum, $refnum, $issue_date, "$company", $t);
			/* Transaction 2 זכות מע"מ עסקאות */
			if($doctype == DOC_CREDIT)
				$t = $vat * -1.0;
			else
				$t = $vat;
			$tnum = Transaction($tnum, INVOICE, SELLVAT, $docnum, $refnum, $issue_date, "$company", $t);
			$i = 0;
			$sum = 0.0;
			foreach($cat_num as $val) {
				$acct = GetAccountFromCatNum($val);
				if($acct == 0) {
					$l = _("Income account not defined");
					ErrorReport("$l");
					exit;
				}
				$np = $price[$i];
				if($doctype == DOC_CREDIT)
					$np *= -1.0;				
				$tnum = Transaction($tnum, INVOICE, $acct, $docnum, $refnum, $issue_date, "$company", $np);
				$np = $price[$i];
				$sum += $np;
				$i++;
			}
			$r = ($sum + $vat) - $total;
			$r *= -1;
			// print "sum: $sum, vat: $vat, total: $total, r: $r<BR>\n";
			if($r) {
				if($doctype == DOC_CREDIT)
					$r *= -1.0;
				$tnum = Transaction($tnum, INVOICE, ROUNDING, $docnum, $refnum, $issue_date, "$company", $r);
			}
			$option = isset($_POST['option']) ? $_POST['option'] : '';
/*			if($option == 'receipt') {
				$refnum = $docnum;
				require('receipt.php'); 
			} */
		}
	}
/*	print "<script type=\"text/javascript>";
	print "PrintDocument(\"docprint.php?win=1&amp;doctype=$doctype&amp;docnum=$docnum\");\n";
	print "</script>\n"; */
}
if($step == 4) {	/* copy document */
	$doctype = (int)$_GET['doctype'];
	$targetdoc = (int)$_GET['targetdoc'];
	$docnum = (int)$_GET['docnum'];
	
	$query = "SELECT * FROM $docstbl WHERE doctype='$doctype' AND docnum='$docnum' AND prefix='$prefix'";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");

	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$num = $line['num'];
//	$valdate = $line['issue_date'];
//	$due_date = $line['due_date'];
//	$due_date = FormatDate($due_date, "mysql", "dmy");
	
	$account = $line['account'];
	$company = $line['company'];
	$address = $line['address'];
	$city = $line['city'];
	$zip = $line['zip'];
	$comments = $line['comments'];
	
	$query = "SELECT * FROM $docdetailstbl WHERE num='$num' AND prefix='$prefix'";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	$i = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		// print_r($line);
		$cat_num[$i] = $line['cat_num'];
		$description[$i] = stripslashes($line['description']);
		$qty[$i] = $line['qty'];
		$unit_price[$i] = $line['unit_price'];
		$pricearr[$i] = $line['price'];
		$i++;
	}
	$step = 0;
}
if($step == 0) {	/* First step, select document type and customer */
	//print "<div class=\"form\"> ";
	if(isset($_GET['targetdoc'])) {
		$targetdoc = $_GET['targetdoc'];
		$docstr = $DocType[$targetdoc];
		//print "<br>";
		$header = _("Create")." ".$docstr;
		//print "<h3>$l ";
		//print "$docstr</h3>";
	}
	else {
		$l = _("Document type not defined");
		print "<h1>$l</h1>\n";
		exit;
	}

	/* Copy document form */
	if($targetdoc == DOC_INVOICE)
		$invisible = "";
	else
		$invisible = "class=\"para\"";
	$text.= "<div id=\"toinvoice\" $invisible>\n";
	$text.= "<form name=\"form3\" action=\"?module=showdocs&amp;step=2\" method=\"post\" style=\"display:inline\">\n";
	$text.= "<input type=\"hidden\" name=\"doctype\" value=\"1\" />\n";
	$text.= "<input type=\"hidden\" name=\"targetdoc\" value=\"3\" />\n";
	$text.= "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	$l = _("Copy proforma");
	$text.= "<input type=\"submit\" value=\"$l\" />\n";
	$text.= "</form>\n";
	$text.= "<form name=\"form4\" action=\"?module=showdocs&amp;step=2\" method=\"post\" style=\"display:inline\">\n";
	$text.= "<input type=\"hidden\" name=\"doctype\" value=\"2\" />\n";
	$text.= "<input type=\"hidden\" name=\"targetdoc\" value=\"3\" />\n";
	$text.= "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	$l = _("Copy delivery doc.");
	$text.= "<input type=\"submit\" value=\"$l\" />\n";
	$text.= "</form>\n";
	$text.= "</div>\n";
	if($targetdoc == DOC_CREDIT)
		$invisible = "";
	else
		$invisible = "class=\"para\"";
	$text.= "<div id=\"tocredit\" $invisible>\n";
	$text.= "<table border=\"0\" width=\"90%\"><tr><td width=\"10%\">\n";
	$text.= "<form name=\"form5\" action=\"?module=showdocs&amp;step=2\" method=\"post\">\n";
	$text.= "<input type=\"hidden\" name=\"doctype\" value=\"3\" />\n";
	$text.= "<input type=\"hidden\" name=\"targetdoc\" value=\"4\" />\n";
	$text.= "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	$l = _("Copy invoice");
	$text.= "<input type=\"submit\" value=\"$l\" />\n";
	$text.= "</form>\n</td></tr></table>\n";
	$text.= "</div>\n";
	
	/* main form */
	$text.= "<div style=\"margin:5px\">\n";
	$text.= "<form name=\"form1\" action=\"?module=docsadmin&amp;step=1\" method=\"post\">\n";
	$text.= "<input type=\"hidden\" name=\"fromnum\" value=\"$docnum\" />\n";
	$text.= "<input type=\"hidden\" name=\"fromdoc\" value=\"$doctype\" />\n";
	$text.= "<table border=\"0\" width=\"100%\" align=\"center\" class=\"formtbl\"><tr>\n";
	$l = _("Customer");
	$text.= "<td style=\"width:70%\">$l: \n";
	$text.= PrintCustomerSelect($account);
	$text.= "<input type=\"hidden\" name=\"type\" value=\"$targetdoc\" />\n";
	$l = _("New customer");
	$text.= "<a href=\"?module=acctadmin&amp;type=0&amp;ret=docsadmin&amp;targetdoc=$targetdoc\">$l</a>\n";
	$text.= "</td>\n";
	$l = _("Date");
	$text.= "<td>$l: \n";
	
	if(!$valdate) {
		$valdate = date('d-m-Y');
		if($doctype < 5)
			$due_date = $valdate;
	}
	else
		$valdate = FormatDate($valdate, "mysql", "dmy");

	$text.= "<input type=\"text\" id=\"idate\" name=\"idate\" value=\"$valdate\" size=\"8\" />\n";
	$text.= "</td>\n";
//	print "<INPUT type=hidden name=valdate value=\"$valdate\">\n";
	$text.= "</tr><tr>\n";
	$l = _("Company");
	$text.= "<td style=\"width:70%\">$l: \n";
//	$company = htmlspecialchars($company);
	$text.= "<input type=\"text\" name=\"company\" size=\"40\" value=\"$company\" /></td>\n";
	$l = _("To be paid until");
	$text.= "<td>$l: \n";
	$text.= "<input type=\"text\" id=\"due_date\" name=\"due_date\" value=\"$due_date\" size=\"8\" />\n";
	$text.= '
<script type="text/javascript">
	addDatePicker("#idate","'.$valdate.'");
	addDatePicker("#due_date","'.$due_date.'");
</script>
';
	$text.= "</td></tr><tr>\n";
	$l = _("Address");
	$text.= "<td colspan=\"1\">$l: \n";
//	$address = htmlspecialchars($address);
	$text.= "<input type=\"text\" name=\"address\" size=\"50\" value=\"$address\" /></td>\n";
	$l = _("Order number");
	$text.= "<td>$l: \n";
	$text.= "<input type=\"text\" name=\"refnum\" value=\"$refnum\" size=\"8\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("City");
	$text.= "<td colspan=\"4\">$l: \n";
//	$city = htmlspecialchars($city);
	$text.= "<input type=\"text\" name=\"city\" value=\"$city\" />&nbsp;&nbsp;\n";
	$l = _("Zip");
	$text.= "$l: \n";
	$text.= "<input type=\"text\" name=\"zip\" value=\"$zip\" size=\"5\" />\n";
	$l = _("Reg. num");
	$text.= "$l: ";
	$text.= "<input type=\"text\" name=\"vatnum\" value=\"$vatnum\" size=\"8\" />\n";
	$text.= "</td></tr><tr><td colspan=\"2\" align=\"center\">\n";
	$text.= "<br />\n";
	//adam:
	$text.= '<input type="hidden" value="0" id="theValue" />';
	/* Now the real part of an invoice, the details part.. */
	$text.= "<table border=\"0\" id=\"docdet\">\n";		/* Internal table for details */
	/* header line */
	$text.= "<tr class=\"tblhead1\">\n";
		$l = _("Item");
		$text.= "<td>$l</td>\n";
		$l = _("Description");
		$text.= "<td>$l</td>\n";
		$l = _("Qty.");
		$text.= "<td>$l</td>\n";
		$l = _("Price");
		$text.= "<td>$l</td>\n";
		$l = _("Currency");
		$text.= "<td>$l</td>\n";
		$l = _("Total");
		$text.= "<td>$l</td>\n";
	$text.= "</tr>\n";
	$text.= "</table>\n";
	$text.= '<script type="text/javascript">addEvent()</script>';
	$text.= "<a href=\"javascript:addEvent();\">Add</a>\n";
	$text.= "</td></tr><tr>\n";
	$text.= "<td colspan=\"2\">\n";
	$text.= "<br />\n";
	$l = _("Comments");
	$text.= "$l: \n";
	$text.= "<textarea name=\"comments\"  cols=\"80\" rows=\"4\">$comments</textarea></td>\n";
	$text.= "</tr><tr>\n";
	$opt = isset($_GET['option']) ? $_GET['option'] : '';
	if($opt == 'receipt') {
		$text.= "<td colspan=\"2\" align=\"center\">\n";
		$text.= "<input type=\"hidden\" name=\"option\" value=\"$opt\" />\n";
		require('receipt.php');
		$text.= "</td></tr><tr>\n";
	}
	
	$l = _("Next");
	$text.= "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l >>>\" /></td>\n";
	$text.= "</tr></table>\n";
	$text.= "</form>\n";
	$text.= "</div>\n";
	createForm($text,$header,'',850);
	//$text.= "</div>";//adam: form div
}
if($step > 0) {
	$fromnum = (int)$_POST['fromnum'];
	$fromdoc = (int)$_POST['fromdoc'];
	
	/* Check if all needed fields are set */
	$doctype = (int)$_POST['type'];
	if($doctype == 0) {
		$l = _("No document type chosen");
		ErrorReport("$l");
		exit;
	}
	$option = isset($_POST['option']) ? $_POST['option'] : '';
	
	$account = (int)$_POST['account'];
	if($account == 0) {
		$l = _("No customer was chosen");
		ErrorReport("$l");
		exit;
	}
	if($doctype < DOC_CREDIT)
		$due_date = GetPost('due_date');
	$company = GetPost('company');
	$address = GetPost('address');
	$city = GetPost('city');
	$zip = GetPost('zip');

	$date = GetPost('idate');
	list($day, $month, $year) = split("[/.-]", $date);
	if(!checkdate($month, (int)$day, $year)) {
		$l = _("Invalid date");
		ErrorReport("$l");
		exit;
	}
	$refnum = GetPost('refnum');
	$comments = GetPost('comments');
	/* All general details are collected, now collect items */
	/* All items are array of items */
	$cat_num = $_POST['cat_num'];
	$cat_numh = $_POST['cat_numh'];
	$description = $_POST['description'];
	$qty = $_POST['qty'];
	$unit_price = $_POST['unit_price'];
	$price = $_POST['price'];
	$currency = $_POST['currency'];

	print "<div style=\"border:1px solid;width:90%;margin:5px\">\n";
	print "<form action=\"?module=docsadmin\" method=\"post\">\n"; /* step will be defined later as _POST */
	print "<input type=\"hidden\" name=\"fromdoc\" value=\"$fromdoc\" />\n";
	print "<input type=\"hidden\" name=\"fromnum\" value=\"$fromnum\" />\n";
	print "<table border=\"0\" width=\"100%\" align=\"center\" class=\"formtbl\"><tr>\n";
	$l = _("To");
	print "<td style=\"width:10%\">$l: </td>\n";
	print "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	print "<td style=\"width:60%\">$company <input type=\"hidden\" name=\"company\" value=\"$company\" /></td>\n";
	$l = _("Date");
	print "<td style=\"width:10%\">$l: </td>\n";
	print "<td>$date <input type=\"hidden\" name=\"idate\" value=\"$date\" /></td>\n";
	print "</tr><tr>\n";
	print "<td>&nbsp;</td>\n";	/* empty column */
	print "<td>$address <input type=\"hidden\" name=\"address\" value=\"$address\" /></td>\n";
	$l = _("Order number");
	print "<td>$l: </td>\n";
	print "<td>$refnum <input type=\"hidden\" name=\"refnum\" value=\"$refnum\" /></td>\n";
	print "</tr><tr>\n";
	print "<td>&nbsp;</td>\n";	/* empty column */
	print "<td>$city $zip</td>\n";
	print "<input type=\"hidden\" name=\"city\" value=\"$city\" /><input type=\"hidden\" name=\"zip\" value=\"$zip\" />\n";
	print "</tr><tr>\n";
	print "<input type=\"hidden\" name=\"type\" value=\"$doctype\" />\n";
	$t = $DocType[$doctype];
	print "<td colspan=\"3\" align=\"center\"><h1 style=\"text-align:center\">$t ";
	if(!$docnum)	
		$n = GetNextDocNum($doctype);
	else
		$n = $docnum;
	print "<input type=\"hidden\" name=\"docnum\" value=\"$n\" />\n";
	print "$n</h1></td>\n";
	$l = _("Source");
	print "<td><h1>$l</h1></td>\n";
	print "</tr><tr><td colspan=\"4\">\n";
	/* Check items values */
	print "<div style=\"border:1px solid;width:100%\">\n";
	print "<table dir=\"rtl\" border=\"1\" width=\"100%\">\n";		/* Internal table for details */
	/* header line */
	print "<tr class=\"tblhead1\">\n";
	$l = _("Description");
	print "<td style=\"width:70%\">$l</td>\n";
	$l = _("Qty.");
	print "<td style=\"width:4em\">$l</td>\n";
	$l = _("Price");
	print "<td style=\"width:5em\">$l</td>\n";
	$l = _("Currency");
	print "<td style=\"width:4em\">$l</td>\n";
	$l = _("Total");
	print "<td style=\"width:4em\">$l</td>\n";
	$l = _("Total NIS");
	print "<td>$l</td>\n";
	print "</tr>\n"; 
	$novattotal = 0.0;
	$vattotal = 0.0;
	for($i = 0; $i <= 10; $i++) {//adam: 5
		$cat = (int)$cat_num[$i];
		if($cat == -1)
			$cat = (int)$cat_numh[$i];
		$p = (double)$price[$i];
		if($cat == 0) {
			if($p > 0.0) {
				$i++;
				$l = _("No item was chosen on line: ");
				ErrorReport("$l $i");
				return;
			}
			continue;
		}
		$needvat = NeedVat($cat);		/* check if this account needs VAT */
		print "<tr>\n";
		print "<input type=\"hidden\" name=\"cat_num[]\" value=\"$cat\" />\n";
		$desc = $description[$i];
		$desc = htmlspecialchars($desc, ENT_QUOTES);
		print "<td>$desc <input type=\"hidden\" name=\"description[]\" value=\"$desc\" /></td>\n";
		$q = (int)$qty[$i];
		print "<td>$q <input type=\"hidden\" name=\"qty[]\" value=\"$q\" />\n";
//		PrintUnits($cat_num);
		print "</td>\n";
		print "<td>$unit_price[$i] <input type=\"hidden\" name=\"unit_price[]\" value=\"$unit_price[$i]\" /></td>\n";
		$currnum = (int)$currency[$i];
		$sign = GetCurrencySymbol($currnum);
		print "<td>$sign <input type=\"hidden\" name=\"currency[]\" value=\"$currnum\" /></td>\n";
		print "<td>$p <input type=\"hidden\" name=\"price[]\" value=\"$p\" /></td>\n";
		$nisprice = CalcNISPrice($p, $currnum, "$year-$month-$day");
		if(($p != 0) && ($nisprice == 0) ) {	/* we don't have rate... */
			if(!($currdone[$currnum])) {
				$l = _("Set rate");
				print "<td><input type=\"button\" value=\"$l\" onclick=OpenRatesWin('$day-$month-$year') /></td>\n";
				$currdone[$currnum] = 1;
				$nextstep = 1;
			}
		}
		else {
			print "<td>$nisprice <input type=\"hidden\" name=\"nisprice[]\" value=\"$nisprice\" /></td>\n";
			if($needvat)
				$vattotal += $nisprice;
			else
				$novattotal += $nisprice;
		}
		print "</tr>\n";
	}	/* end of for loop */
	$vattotal = round($vattotal, 2);	
	/* Now print totals, calculate VAT and print total including VAT */
	print "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";	/* empty space line */
	print "<tr><td colspan=\"2\">&nbsp;</td>\n";	/* space column */
	$l = _("Total for VAT");
	print "<td colspan=\"3\">$l: </td>\n";
	print "<input type=hidden name=\"sub_total\" value=\"$vattotal\" />\n";
	print "<td>$vattotal</td></tr>\n";
	print "<tr><td colspan=\"2\">\n";
	if($due_date) {
		$l = _("To be paid until");
		print "$l: \n";
		print "<input type=\"hidden\" name=\"due_date\" value=\"$due_date\" />\n";
		print "$due_date\n";
	}
	print "</td>\n";
	$l = _("No vat total");
	print "<td colspan=\"3\">$l: </td>\n";
	$novattotal = round($novattotal, 2);
	print "<input type=\"hidden\" name=\"novat_total\" value=\"$novattotal\" />\n";
	print "<td>$novattotal</td>\n";
	print "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";	/* empty space line */
	print "<tr><td colspan=\"2\">$comments</td>\n";
	print "<input type=\"hidden\" name=\"comments\" value=\"$comments\" />\n";
	$l = _("VAT");
	print "<td colspan=\"3\">$l: </td>\n";
	$vat = CalcVAT($vattotal);
	print "<input type=\"hidden\" name=\"vat\" value=\"$vat\" />\n";
	print "<td>$vat</td>\n";
	print "</tr>\n";
	
	print "<tr><td colspan=\"2\">&nbsp;</td>\n";	/* space column */
	$l = _("Total");
	print "<td colspan=\"3\">$l: </td>\n";
	$totalpayment = round($vattotal + $vat + $novattotal, 0);
	print "<input type=\"hidden\" name=\"total\" value=\"$totalpayment\" />\n";
	print "<td>$totalpayment</td>\n";
	print "</tr>\n";
	print "</table>\n";
	print "</div>\n";
	$opt = isset($_POST['option']) ? $_POST['option'] : '';
	if($opt == 'receipt') {
		$l = _("Receipt");
		print "<h1>$l: </h1>\n";
		print "<input type=\"hidden\" name=\"option\" value=\"receipt\" />\n";
		$refnum = $docnum;
		require('receipt.php');
	}
	print "</td></tr><tr>\n";
	print "<td colspan=\"4\" align=\"center\">\n";
	if(!$nextstep)
		$nextstep = 3;
	if($step < 3) {
		print "<input type=\"hidden\" name=\"step\" value=\"$nextstep\" />\n";
		if($nextstep == 3) {
			$l = _("Create & print");
			print "<input type=\"submit\" value=\"$l\" />\n";
		}
		else {
			$l = _("Next");
			print "<input type=\"submit\" value=\"$l >>>\" />\n";
		}
	}
	else {	/* show email form or print */
		print "<input type=\"hidden\" name=\"step\" value=\"5\" />\n";
//		print "<input type=\"submit\" value=\"שלח בדואר אלקטרוני\">\n";
		$l = _("Print");
		print "<input type=\"button\" value=\"$l\" ";
		print "onclick=\"window.open('printdoc.php?doctype=$doctype&amp;docnum=$docnum&amp;prefix=$prefix&amp;print_win=1', 'printwin', 'width=800,height=600,scrollbar=yes')\" />\n";
	}		
//	print "step: $step, nextstep: $nextstep\n";
	print "</td></tr>\n</table>\n";
	print "</form>\n";
	print "</div>\n";
}
?>

