<?PHP
//M:׳ ׳™׳”׳•׳� ׳�׳¡׳�׳›׳™׳� ׳¢׳¡׳§׳™׳™׳�
/*
 | docsadmin
 | Business document module for Linet
 | Written by Ori Idan November 2009
 | Written by Adam Ben Hour 2011
 */
global $logo, $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl;
global $docstbl,$chequestbl, $docdetailstbl, $currencytbl;
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
$result = DoQuery($query,__FILE__.": ".__LINE__);
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

	?>
<script type="text/javascript">
function addRcpt(last){
	var r  = document.createElement('tr');
	var ni = document.getElementById('rcptdetials');
	var numi = document.getElementById('theValue');

	var num=numi.value;
	numi.value ++;//= (numi.value)+(1);
	var trIdName = "my"+num+"Tr";
	
	
	r.innerHTML= "<td>\n";
	r.innerHTML=r.innerHTML+ '<?PHP print PrintPaymentType(0); ?>';
	r.innerHTML=r.innerHTML+ "</td><td class=\"crdhide\">\n";
	r.innerHTML=r.innerHTML+ '<?PHP print PrintCreditCompany(0);?>';
	r.innerHTML=r.innerHTML+ "</td>\n";
	r.innerHTML=r.innerHTML+ "<td><input type=\"text\" name=\"cheque_num[]\" size=\"8\" /></td>\n";
	r.innerHTML=r.innerHTML+ "<td class=\"chkhide\"><input type=\"text\" name=\"bank[]\" size=\"3\" /></td>\n";
	r.innerHTML=r.innerHTML+ "<td class=\"chkhide\"><input type=\"text\" name=\"branch[]\" size=\"3\" /></td>\n";
	r.innerHTML=r.innerHTML+ "<td class=\"chkhide\"><input type=\"text\" name=\"cheque_acct[]\" size=\"8\" /></td>\n";
	r.innerHTML=r.innerHTML+ "<td><input type=\"text\" name=\"date[]\" size=\"7\" /></td>\n";
	r.innerHTML=r.innerHTML+ "<td><input type=\"text\" class=\"sum\" name=\"sum[]\" size=\"6\" /></td>\n";
	//$text.= "</tr>\n";
	ni.appendChild(r);
}
function addItem(last) {
	var ni = document.getElementById('docdet');
	var numi = document.getElementById('theValue');
	//var num = (document.getElementById("theValue").value -1)+ 2;
	
	var num=numi.value;
	numi.value ++;//= (numi.value)+(1);
	var trIdName = "my"+num+"Tr";
	var r  = document.createElement('tr');
	var ca = document.createElement('td');
	var cb = document.createElement('td');
	var cc = document.createElement('td');
	var cd = document.createElement('td');
	var ce = document.createElement('td');
	var cf = document.createElement('td');
	
	r.setAttribute("id",trIdName);
	
	ca.innerHTML = "<input type=\"text\" id=\"AC"+num+"\" class=\"cat_num\" name=\"cat_num[]\" onblur=\"SetPartDetails("+num+")\" size=\"10\"/>\n";
	cb.innerHTML = "<input type=\"text\" id=\"DESC"+num+"\" class=\"description\" name=\"description[]\" size=\"40\" />";
	cc.innerHTML ="<input type=\"text\" id=\"QTY"+num+"\" class=\"qty\" name=\"qty[]\" size=\"4\" onblur=\"CalcPrice("+num+")\" />"+createNumBox("QTY",num,1);
	cd.innerHTML ="<input type=\"text\" id=\"UNT"+num+"\" class=\"unit_price\" name=\"unit_price[]\" size=\"8\" onblur=\"CalcPrice("+num+")\" />"+createNumBox("UNT",num,10);
	ce.innerHTML ="<select class=\"currency\" id=\"CUR"+num+"\" name=\"currency[]\"><option value=\"0\">NIS</option></select>";
	cf.innerHTML ="<input type=\"text\" id=\"PRICE"+num+"\" class=\"price\" name=\"price[]\" size=\"8\" /><a href=\"javascript:;\" onclick=\"removeElement(\'"+trIdName+"\')\">Remove</a>";

	r.appendChild(ca);
	r.appendChild(cb);
	r.appendChild(cc);
	r.appendChild(cd);
	r.appendChild(ce);
	r.appendChild(cf);
	
	ni.appendChild(r);
	$( "#AC"+num ).autocomplete({source: "index.php?action=lister&data=items&jsoncallback=?"});
}
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
	var uprice = document.getElementById('UNT'+index);
	var price = document.getElementById('PRICE'+index);
	
	price.value = uprice.value * qty.value;
}
function SetPartDetails(index) {
	//alert(sbla);
	var part = document.getElementById('AC'+index);
	var desc = document.getElementById('DESC'+index);
	var uprice = document.getElementById('UNT'+index);
	var currency = document.getElementById('CUR'+index);
	
	$.post("index.php",  {"action": "lister" ,"selector" : 1, "data": "Item", "num": part.value},
			function(data) {

				desc.value = data.name;
				currency.value=data.currency;
				uprice.value = data.defprice;
				
				var bla =document.getElementById('QTY'+index).focus();
			}, "json")
			.error(function() { });
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
function SetCustomer(){
	var acc = document.getElementById('acc');
	var company = document.getElementById('company');
	var address = document.getElementById('address');
	var city = document.getElementById('city');
	var zip = document.getElementById('zip');
	var vatnum = document.getElementById('vatnum');
	var idate = document.getElementById('idate');
	$.post("index.php",  {"action": "lister" ,"selector" : 1, "data": "Account", "num": acc.value},
			function(data) {

				company.value = data.company;
				address.value=data.address;
				city.value = data.city;
				zip.value= data.zip;
				vatnum.value=data.vatnum;

				var pay_terms=data.pay_terms;
				var em;
				if(pay_terms < 0) {
					em = 1;	// pay_terms are days after end of current month 
					pay_terms = pay_terms * -1;
				}
				else
					em = 0;
				if(pay_terms == '')
					pay_terms = 0;

				CalcDueDate(idate.value, pay_terms, 1);
				//var bla =document.getElementById('QTY'+index).focus();
			}, "json")
			.error(function() { });
}
function TypeSelChange(i) {
	var i = document.getElementById(i);
	var b = i.value;
	if(b == 1) {
		//document.getElementByClass('bankdiv').style.display = 'block';
		//document.getElementByClass('crd').style.display = 'none';
	}else if(b == 3) {
		$(".crdhide").show();
		$(".chkhide").hide();
		//document.getElementByClass('crdhide').style.display = 'block';
		//document.getElementByClass('chkhide').style.display = 'none';
	}else {
		//document.getElementByClass('crd').style.display = 'none';
		//document.getElementByClass('bankdiv').style.display = 'none';
		//document.getElementByClass('refnum1').style.display = 'none';
	}
}

</script>
<?PHP
	function PrintPaymentType($type) {
		global $paymenttype;
		
		$text= '<select class="ptype" id="ptype'.$type.'" name="ptype[]" onchange="TypeSelChange("ptype'.$type.'\")" >';
		foreach($paymenttype as $num => $v) {
			$text.= '<option value="'.$num.'" ';
			if($type == $num)
				$text.= 'selected="selected"';
			$text.= '>'.$v.'</option>';
		}
		$text.= '</select>';
		return $text;
	}

	function PrintCreditCompany($c) {
		global $creditcompanies;
		
		$text= '<select name="creditcompany[]\" >';
		foreach($creditcompanies as $num => $v) {
			$text.= '<option value="'.$num.'" ';
			if($c == $num)
				$text.= 'selected="selected"';
			$text.= '>'.$v.'</option>';
		}
		$text.= '</select>';
		return $text;
	}


function CalcVAT($sum) {
	global $companiestbl;
	global $prefix;

	$query = "SELECT vat FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, __FILE__.": ".__LINE__);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$vat = $line[0];
	
	return round($sum * $vat / 100, 2);
}
/*
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
}*/
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
	$result = DoQuery($query, __FILE__.": ".__LINE__);

	$line = mysql_fetch_array($result, MYSQL_NUM);
	$n = $line[0];
	if($n == 0) {
		$query = "SELECT num1,num2,num3,num4,num5,num6,num7,num8 FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, __FILE__.": ".__LINE__);
	
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
		$result = DoQuery($query, __FILE__.": ".__LINE__);;
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

	global $docstbl,$prefix;
	/* Make sure there is no document with same details */
	$query = "SELECT num FROM $docstbl WHERE doctype='$doctype' AND docnum='$docnum' AND prefix='$prefix'";
	$result = DoQuery($query, "step3");
	$n = mysql_num_rows($result);
	//print "test";
	if($n == 0) {	/* this is first time */
		//print "test";
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
		$src_tax = GetPost('src_tax');
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
		global $curuser;
		/* Put data into table */
		$query = "INSERT INTO $docstbl VALUES(NULL, '$prefix', ";
		$query .= "'$doctype', '$docnum', '$account', '$company', '$address', '$city', '$zip', '$vatnum', ";
		$query .= "'$refnum', '$issue_date', '$due_date', ";
		$query .= "'$sub_total', '$novat_total', '$vat', '$total', '$src_tax', '0', '0', '$comments',0,'$curuser->id')";
		$result = DoQuery($query, "step3");
		$num = mysql_insert_id();
		
		$issue_date = FormatDate($issue_date, "mysql", "dmy");
		/* All general details are collected, now collect items */
		/* All items are array of items */
		
		//finsh haeder start details and transactions
		//print "test";
		global $TransType;
		$transtype=$TransType[$doctype];
		//print";$transtype;?";
		require_once 'class/document.php';
		if(($doctype!=DOC_RECEIPT)){
				$cat_num = $_POST['cat_num'];
				$description = $_POST['description'];
				$qty = $_POST['qty'];
				$unit_price = $_POST['unit_price'];
				$price = $_POST['price'];
				$currency = $_POST['currency'];
				$nisprice = $_POST['nisprice'];
				
				$i = 0;
				//global $docdetailstbl;
				
				foreach($cat_num as $val) {
					$acct = GetAccountFromCatNum($val);
					if($acct == 0) {
						$l = _("Income account not defined");
						ErrorReport("$l");
						exit;
					}
					//$query = "INSERT INTO $docdetailstbl VALUES('$prefix', '$num', '$val', ";
					$detial= new documentDetail();
					$detial->num=$num;
					$detial->description=$description[$i];
					$detial->qty=$qty[$i];
					$detial->cat_num=$val;
					$detial->unit_price=$unit_price[$i];
					$detial->currency=$currency[$i];
					$detial->price=$price[$i];
					$detial->nisprice=$nisprice[$i];
					$detial->newDetial();
					unset($detial);
					
					$i++;
					
					/* Update inventory inventory */
					$query = "SELECT ammount FROM $itemstbl ";
					$query .= "WHERE num='$val' AND prefix='$prefix'";
					$result = DoQuery($query, __FILE__.": ".__LINE__);
					DoQuery($query, __FILE__.": ".__LINE__);
					$line = mysql_fetch_array($result, MYSQL_NUM);
					$inventory_qty = $line[0];
					if($doctype == DOC_INVOICE)// or DOC_PROFORMA or DOC_DELIVERY
						$inventory_qty -= $n;
					else if($doctype == DOC_CREDIT)// or DOC_RETURN
						$inventory_qty += $n;
					$query = "UPDATE $itemstbl SET ammount='$inventory_qty' ";
					$query .= "WHERE num='$val' AND prefix='$prefix'";
					$result = DoQuery($query, __FILE__.": ".__LINE__);
				}
		}//end simple doc
		if(($doctype==DOC_RECEIPT) || ($doctype==DOC_INVRCPT)){
				/* now get cheques data */
				
				$type = $_POST['ptype'];
				$creditcompany = $_POST['creditcompany'];
				$cheque_num = $_POST['cheque_num'];
				$bank = $_POST['bank'];
				$branch = $_POST['branch'];
				$cheque_acct = $_POST['cheque_acct'];
				$date = $_POST['date'];
				$sum = $_POST['sum'];
				$src_tax=$_POST['src_tax'];
				
				$cheques_sum = 0.0;
				$tnum = 0;
				$tnum = Transaction($tnum, $transtype, CUSTTAX, $docnum, '', $issue_date, '', $src_tax * -1.0);
				$tnum = Transaction($tnum, $transtype, $account, $docnum, '', $issue_date, '', $src_tax);
				foreach($sum as $key => $val) {
					$type1 = $type[$key];
					$crcompany = $creditcompany[$key];
					$chknum = htmlspecialchars($cheque_num[$key]);
					$bnk = htmlspecialchars($bank[$key]);
					$brnch = htmlspecialchars($branch[$key]);
					$acct = htmlspecialchars($cheque_acct[$key]);
					$cheque_date = $date[$key];
					$cheque_date = FormatDate($cheque_date, "dmy", "mysql");
					
					//if($type1 != 1) {
					$query = "INSERT INTO $chequestbl VALUES ('$prefix', '$num', '$type1', '$crcompany', '$chknum', '$bnk', '$brnch', '$acct', '$cheque_date', '$val', '', '')";
					// print "Query: $query<BR>\n";
					$result = DoQuery($query, __FILE__.": ".__LINE__);
					if(!$result) {
						print "Query: $query<BR>\n";
						echo mysql_error();
						exit;
					}
					//}
					$cheques_sum += $val;
					$tnum = Transaction($tnum, $transtype, $account, $docnum, $chknum, $issue_date, '', $val);
					if($type != 1)
						$tnum = Transaction($tnum, $transtype, CHEQUE, $docnum, $chknum, $issue_date, '', $val * -1.0);
					else
						$tnum = Transaction($tnum, $transtype, CASH, $docnum, $chknum, $issue_date, '', $val * -1.0);
				}//end foreach
		} //end ercipt data
		/* Close from doc */
		if($fromdoc) {
			$query = "UPDATE $docstbl \n";
			$query .= "SET status='1' \n";	/* 1 means closed */
			$query .= "WHERE docnum='$fromnum' AND doctype='$fromdoc' AND prefix='$prefix'";
			$result = DoQuery($query, __FILE__.": ".__LINE__);
		}
		//print ";$doctype;";
		if(($doctype == DOC_INVOICE) || ($doctype == DOC_CREDIT) || ($doctype== DOC_PROFORMA) || ($doctype== DOC_INVRCPT)) {
			/* Write transactions */
			/* Transaction 1 ׳—׳•׳‘׳× ׳”׳�׳§׳•׳— ׳‘׳¡׳›׳•׳� ׳”׳—׳©׳‘׳•׳ ׳™׳× */
			if(($doctype == DOC_INVOICE) ||($doctype==DOC_PROFORMA)||($DOC_INVRCPT))
				$t = $total * -1.0;
			else
				$t = $total;
			//adam: $issue_date = FormatDate($issue_date, "mysql", "dmy");
			//print "tryyyy just a little bit harder";
			$tnum = Transaction(0, $transtype, $account, $docnum, $refnum, $issue_date, "$company", $t);
			/* Transaction 2 ׳–׳›׳•׳× ׳�׳¢"׳� ׳¢׳¡׳§׳�׳•׳× */
			if($doctype == DOC_CREDIT)
				$t = $vat * -1.0;
			else
				$t = $vat;
			$tnum = Transaction($tnum, $transtype, SELLVAT, $docnum, $refnum, $issue_date, "$company", $t);
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
				$tnum = Transaction($tnum, $transtype, $acct, $docnum, $refnum, $issue_date, "$company", $np);
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
				$tnum = Transaction($tnum, $transtype, ROUNDING, $docnum, $refnum, $issue_date, "$company", $r);
			}
			//adam: $option = isset($_POST['option']) ? $_POST['option'] : '';
			//reg recepit detial:
/*			if($option == 'receipt') {
				$refnum = $docnum;
				require('receipt.php'); 
			} */
		}
	}
/*	print "<script type=\"text/javascript>";
	print "PrintDocument(\"docprint.php?win=1&amp;doctype=$doctype&amp;docnum=$docnum\");\n";
	print "</script>\n"; */
} //end step 3
if($step == 4) {	/* copy document */
	$doctype = (int)$_GET['doctype'];
	$targetdoc = (int)$_GET['targetdoc'];
	$docnum = (int)$_GET['docnum'];
	
	$query = "SELECT * FROM $docstbl WHERE doctype='$doctype' AND docnum='$docnum' AND prefix='$prefix'";
	$result = DoQuery($query, __FILE__.": ".__LINE__);

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
	$result = DoQuery($query, __FILE__.": ".__LINE__);
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
}//end copy
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
	$text.= "<div style=\"padding:10px;\">\n";
	$text.= "<form name=\"form1\" action=\"?module=docsadmin&amp;step=1\" method=\"post\">\n";
	$text.= "<input type=\"hidden\" name=\"fromnum\" value=\"$docnum\" />\n";
	$text.= "<input type=\"hidden\" name=\"fromdoc\" value=\"$doctype\" />\n";
	$text.= "<table border=\"0\" width=\"100%\" align=\"center\" class=\"formtbl\"><tr>\n";
	$l = _("Customer");
	$text.= "<td style=\"width:70%\">$l: \n";
	$text.= PrintCustomerSelect($account);
	$text.= "<input type=\"hidden\" name=\"type\" value=\"$targetdoc\" />\n";
	$l = _("New customer");
	//$text.= "<a href=\"?module=acctadmin&amp;type=0&amp;ret=docsadmin&amp;targetdoc=$targetdoc\">$l</a>\n";
	$text.=newWindow($l,'?action=lister&form=account&type='.CUSTOMER,480,480);
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
	$text.= "<input type=\"text\" id=\"company\" name=\"company\" size=\"40\" value=\"$company\" /></td>\n";
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
	$text.= "<input type=\"text\" id=\"address\" name=\"address\" size=\"50\" value=\"$address\" /></td>\n";
	$l = _("Order number");
	$text.= "<td>$l: \n";
	$text.= "<input type=\"text\" name=\"refnum\" id=\"refnum\" value=\"$refnum\" size=\"8\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("City");
	$text.= "<td colspan=\"2\">$l: \n";//adam:
//	$city = htmlspecialchars($city);
	$text.= "<input type=\"text\" id=\"city\" name=\"city\" value=\"$city\" />&nbsp;&nbsp;\n";
	$l = _("Zip");
	$text.= "$l: \n";
	$text.= "<input type=\"text\" id=\"zip\" name=\"zip\" value=\"$zip\" size=\"5\" />\n";
	$l = _("Reg. num");
	$text.= "$l: ";
	$text.= "<input type=\"text\" id=\"vatnum\" name=\"vatnum\" value=\"$vatnum\" size=\"8\" />\n";
	$text.= "</td></tr><tr><td colspan=\"2\" align=\"center\">\n";
	$text.= "<br />\n";
	//adam:
	$text.= '<input type="hidden" value="0" id="theValue" />';
	/* Now the real part of an invoice, the details part.. */
	if ($targetdoc!=DOC_RECEIPT){
		$l=_("New Item");
		$text.=newWindow($l,'?action=lister&form=items',400,350);
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
		$text.= '<script type="text/javascript">addItem();</script>';
		$text.= "<a href=\"javascript:addItem();\">Add</a>\n";
		$text.= "</td></tr>\n";
		$text.= "</table>\n";
	}
	if (($targetdoc==DOC_RECEIPT) || ($targetdoc==DOC_INVRCPT)){
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
		$text.="</tr>";
		//$text.= "</tr><tr><td colspan=\"8\" align=\"center\">\n\n";
		//$text.= '<input type="hidden" value="0" id="rcptDetialNum" />';
		//$text.= "<table border=\"0\" id=\"rcptdetials\">\n";
		for($i = 0; $i < 4; $i++) {
			$text.= "<tr>\n";
			$text.= "<td>\n";
			$text.= PrintPaymentType(i);
			$text.= "</td><td class=\"crdhide\">\n";
			$text.= PrintCreditCompany(0);
			$text.= "</td>\n";
			$text.= "<td><input type=\"text\" name=\"cheque_num[]\" size=\"8\" /></td>\n";
			$text.= "<td class=\"chkhide\"><input type=\"text\" name=\"bank[]\" size=\"3\" /></td>\n";
			$text.= "<td class=\"chkhide\"><input type=\"text\" name=\"branch[]\" size=\"3\" /></td>\n";
			$text.= "<td class=\"chkhide\"><input type=\"text\" name=\"cheque_acct[]\" size=\"8\" /></td>\n";
			$text.= "<td><input type=\"text\" name=\"date[]\" size=\"7\" /></td>\n";
			$text.= "<td><input type=\"text\" class=\"sum\" name=\"sum[]\" size=\"6\" /></td>\n";
			$text.= "</tr>\n";
		}//*/
		//$text.= "</table></td></tr>";
		
		$l = _("Source tax");
		$text.= "<tr><td colspan=\"7\" align=\"left\">$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"src_tax\" size=\"6\" /></td>\n\t</tr>";
		$text.= "</table>\n";
		//$text.='<script type="text/javascript">addRcpt();</script>';
	}
	
	$text.= "<br />\n";
	$l = _("Comments");
	$text.= "$l: \n";
	$text.= "<textarea name=\"comments\"  cols=\"80\" rows=\"4\">$comments</textarea>\n";
	$l = _("Next");
	$text.= "<input type=\"submit\" value=\"$l >>>\" />\n";
	
	$text.= "</form>\n";
	$text.= "</div>\n";
	createForm($text,$header,'',900);
	//$text.= "</div>";//adam: form div
}
if($step > 0) {//preview
	$fromnum = (int)$_POST['fromnum'];
	$fromdoc = (int)$_POST['fromdoc'];
	
	/* Check if all needed fields are set */
	$doctype = (int)$_POST['type'];
	if($doctype == 0) {
		$l = _("No document type chosen");
		ErrorReport("$l");
		exit;
	}
	//adam: $option = isset($_POST['option']) ? $_POST['option'] : '';
	
	$account = (int)$_POST['account'];
	if($account == 0) {
		$l = _("No customer was chosen");
		ErrorReport("$l");
		exit;
	}
	//if($doctype < DOC_CREDIT)
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
	if ($doctype!=DOC_RECEIPT){
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
}
	print "</div>\n";
	
	//adam:
	if (($doctype==DOC_RECEIPT) || ($doctype==DOC_INVRCPT)){
		
		global $paymenttype,$creditcompanies;
		/* now get cheques data */
		$type = $_POST['ptype'];
		$creditcompany = $_POST['creditcompany'];
		$cheque_num = $_POST['cheque_num'];
		$bank = $_POST['bank'];
		$branch = $_POST['branch'];
		$cheque_acct = $_POST['cheque_acct'];
		$date = $_POST['date'];
		$sum = $_POST['sum'];
		$src_tax=$_POST['src_tax'];
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
			print "<input type=\"hidden\" name=\"ptype[]\" value=\"$t\" readonly />\n";
			print "</td><td>\n";
			$t = $creditcompany[$index];
			$ts = $creditcompanies[$t];
			print "$ts <input type=\"hidden\" name=\"creditcompany[]\" value=\"$t\" readonly />\n</td>\n";
			//print "";
			$cn = htmlspecialchars($cheque_num[$index], ENT_QUOTES);
			print "<td>$cn";
			print "<input type=\"hidden\" name=\"cheque_num[]\" value=\"$cn\" readonly />\n</td>\n";
			$bn = $bank[$index];
			$bs = $banksarr[$bn];
			print "<td>$bn - $bs";
			print "<input type=\"hidden\" name=\"bank[]\" value=\"$bank[$index]\" readonly />\n</td>\n";
			print "<td>$branch[$index]";
			print "<input type=\"hidden\" name=\"branch[]\" value=\"$branch[$index]\" readonly />\n</td>\n";
			print "<td>$cheque_acct[$index]";
			print "<input type=\"hidden\" name=\"cheque_acct[]\" value=\"$cheque_acct[$index]\" readonly />\n</td>\n";
			print "<td>$date[$index]";
			print "<input type=\"hidden\" name=\"date[]\" value=\"$date[$index]\" readonly />\n</td>\n";
			print "<td>$val";
			print "<input type=\"hidden\" name=\"sum[]\" value=\"$val\" readonly />\n</td>\n";
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
		print "<td><b><input type=\"hidden\" name=\"total\" value=\"$total_sum\" />$total_sum</b></td>\n";
		print "</tr>\n";
		print "</table>\n";
	}
	print "</td></tr><tr>\n";
	print "<td colspan=\"4\" align=\"center\">\n";
	if(!$nextstep)
		$nextstep = 3;
	if($step < 3) {
		print "<input type=\"hidden\" name=\"step\" value=\"$nextstep\" />\n";
		if($nextstep == 3) {
			if($doctype==DOC_INVRCPT){
				
				//print "<input type=\"submit\" value=\"$l\" />\n";
				if($total_sum!=$totalpayment){
					$l = _("Back");
					print _("The sum of the invoice and the recipet is not the same");
					print "<a href=\"#\" class=\"btn\" onClick=\"history.go(-1)\">$l</a>\n";
					return;
				}
				
			}
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
//		print "<input type=\"submit\" value=\"׳©׳�׳— ׳‘׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™\">\n";
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