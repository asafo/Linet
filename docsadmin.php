<?PHP
//M:׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¡׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ¢׳³ֲ³ײ²ֲ¡׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
/*
 | docsadmin
 | Business document module for Linet
 | Written by Ori Idan November 2009
 | Written by Adam Ben Hour 2011
 */
global $logo, $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl;
global $docstbl,$chequestbl, $docdetailstbl, $currencytbl;
global $curcompany,$curuser;
//global $CompArray;
//global $CurrArray;
global $DocType;
global $paymentarr;
global $creditarr;
global $banksarr;
require_once 'class/document.php';
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
$CurrArray[0] = "$l";
$ci = 1;
//$CurrArray
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['code'];
	$sign = $line['sign'];
	$CurrArray[$num] = "$sign";
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
var Icredit=0;
function creditMe(){
   if(Icredit==0)
	   $('#docform').submit();
   else 
	   creditMe1();
	   
}
function addRcpt(last){
	var r  = document.createElement('tr');
	var ni = document.getElementById('rcptdet');

	var num=last+1;
	var trIdName = "my"+num+"Tr";
	//num
	
	text= "";
	text+="<td><?PHP print PrintPaymentType(); ?></td>";
	text+= "<td><?PHP print PrintBankSelect();?></td>";
	text+= "<td><input type=\"text\" id=\"cheque_num"+num+"\" name=\"cheque_num[]\" size=\"8\" /></td>\n";
	text+= "<td><input type=\"text\" id=\"bank"+num+"\" name=\"bank[]\" size=\"1\" /></td>\n";
	text+= "<td><input type=\"text\" id=\"branch"+num+"\" name=\"branch[]\" size=\"1\" /></td>\n";
	text+= "<td><input type=\"text\" id=\"cheque_acct"+num+"\" name=\"cheque_acct[]\" size=\"8\" /></td>\n";
	text+= "<td><input type=\"text\" id=\"date"+num+"\" value=\"<?php print date('d-m-Y');?>\" class=\"\" name=\"date[]\" size=\"7\" /></td>\n";
	text+= "<td><input type=\"text\" id=\"sum"+num+"\" class=\"sum\" name=\"sum[]\" size=\"6\" /></td>\n";

	r.innerHTML=text;
	ni.appendChild(r);
	$("#date"+num).hide();
	$("#banksel"+num).hide();
	$("#cheque_num"+num).hide();
	$("#bank"+num).hide();
	$("#branch"+num).hide();
	$("#cheque_acct"+num).hide();
}
function addItem(last) {
	var ni = document.getElementById('docdet');
	var num =last+1;
	var IdName = "My"+num;
	var r  = document.createElement('tr');
	var ca = document.createElement('td');
	var cb = document.createElement('td');
	var cc = document.createElement('td');
	var cd = document.createElement('td');
	var ce = document.createElement('td');
	var cf = document.createElement('td');
	var cg = document.createElement('td');
	r.setAttribute("id",'tr'+IdName);
	cg.setAttribute("id",'Action'+IdName);
	ca.innerHTML = "<input type=\"text\" id=\"AC"+num+"\" class=\"cat_num\" name=\"cat_num[]\" onblur=\"SetPartDetails("+num+")\" size=\"5\"/>\n";
	cb.innerHTML = "<input type=\"text\" id=\"DESC"+num+"\" class=\"description\" name=\"description[]\" size=\"20\" />";
	cc.innerHTML ="<input type=\"text\" id=\"QTY"+num+"\" class=\"qty\" name=\"qty[]\" size=\"3\" onblur=\"CalcPrice("+num+")\" />"+createNumBox("QTY",num,1);
	cd.innerHTML ="<input type=\"text\" id=\"UNT"+num+"\" class=\"unit_price\" name=\"unit_price[]\" size=\"6\" onblur=\"CalcPrice("+num+")\" />"+createNumBox("UNT",num,10);
	ce.innerHTML="<?php print PrintCurrencySelect(null,$CurrArray);?>";
	cf.innerHTML ="<input type=\"text\" class=\"sum\" id=\"PRICE"+num+"\" class=\"price\" name=\"price[]\" size=\"8\" readonly=\"yes\" />";
	cg.innerHTML="<a href=\"javascript:addItem("+num+");\" class=\"btnadd\">Add</a>";
		
	if(last!=0){
		var lastaction = document.getElementById('ActionMy'+last);
			lastaction.innerHTML="<a href=\"javascript:;\" onclick=\"removeElement(\'trMy"+last+"\')\" class=\"btnremove\"></a>";
	}
	//replace add button with remove

	r.appendChild(ca);
	r.appendChild(cb);
	r.appendChild(cc);
	r.appendChild(cd);
	r.appendChild(ce);
	r.appendChild(cf);
	r.appendChild(cg);
	
	ni.appendChild(r);
	$( "#AC"+num ).autocomplete({source: "index.php?action=lister&data=Item&jsoncallback=?"});
}

function removeElement(divNum) {
	var d = document.getElementById('docdet');
	var olddiv = document.getElementById(divNum);
	d.removeChild(olddiv);
	CalcPriceSum();
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
	var qty = $('#QTY'+index).val();
	var uprice = $('#UNT'+index).val();
	$('#PRICE'+index).val((uprice * qty).toFixed(2));
	CalcPriceSum();
}
function CalcPriceSum() {
	var elements = $('.sum');
	var sum=0;
	var vat=<?php print $curcompany->vat; ?>;
	for (var i=0; i<elements.length; i++) {
	    sum+=parseFloat($('#'+elements[i].id).val());
	}
	var vatsum=Math.round((sum*(vat/100))*100)/100;
	$('#vatsum').val(vatsum.toFixed(2));
	$('#sum').val((sum+vatsum).toFixed(2));
}
function SetPartDetails(index) {
	var part = $('#AC'+index).val();
	$.post("index.php",  {"action": "lister" ,"selector" : 1, "data": "Item", "num": part},
			function(data) {
				$('#DESC'+index).val(data.name);
				$('#CUR'+index).val(data.currency);
				$('#UNT'+index).val(data.defprice);
				$('#QTY'+index).focus();
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
	var idate = document.getElementById('idate');
	$.post("index.php",  {"action": "lister" ,"selector" : 1, "data": "Account", "num": $("#acc").val()},
			function(data) {
				$("#company").val(data.company);
				$("#address").val(data.address);
				$("#city").val(data.city);
				$("#zip").val(data.zip);
				$("#vatnum").val(data.vatnum);

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
			}, "json")
			.error(function() { });
}
function TypeSelChange(num) {
	var val = $('#ptype'+num).val();
	if((val == 1)||(val==3)) {
		$("#date"+num).hide();
		$("#banksel"+num).hide();
		$("#cheque_num"+num).hide();
		$("#bank"+num).hide();
		$("#branch"+num).hide();
		$("#cheque_acct"+num).hide();
	}else if(val == 2) {
		$("#date"+num).show();
		$("#banksel"+num).hide();
		$("#cheque_num"+num).show();
		$("#bank"+num).show();
		$("#branch"+num).show();
		$("#cheque_acct"+num).show();
	}else if(val == 4) {
		$("#date"+num).show();
		$("#banksel"+num).show();
		$("#cheque_num"+num).show();
		$("#bank"+num).show();
		$("#branch"+num).show();
		$("#cheque_acct"+num).show();
	}
}

</script>
<?PHP
	function PrintPaymentType() {
		global $paymenttype;
		
		$text= <<<Hell
<select id=\"ptype"+num+"\" name=\"ptype[]\" onchange=\"TypeSelChange("+num+")\" >
Hell;
		foreach($paymenttype as $num => $v)
			$text.= '<option value=\"'.$num.'\" >'.$v.'</option>';
		$text.= '</select>';
		return $text;
	}

	function PrintBankSelect() {
		global $table,$prefix;
		$lines = selectSql(array("prefix"=>$prefix,"type"=>BANKS), $table['accounts'],array("num","company"));
	
		$str= '<select id=\"banksel"+num+"\" name=\"bankacc[]\" >';
		foreach($lines as $line) 
			$str.= '<option value=\"'.$line['num'].'\" >'.$line['company'].'</option>';
		return $str.'</select>';;
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

function PrintCurrencySelect($defnum='',$CurrArray) {
	//global $CurrArray;
	$str= '<select class=\"currency\" id=\"CUR"+num+"\" name=\"currency[]\">';
	foreach($CurrArray as $code=>$sign) {
		$str.= '<option value=\"'.$code.'\"';
		if($code == $defnum)
			$str.= " selected";
		$str.= '>'.htmlspecialchars($sign).'</option>';
	}
	//$text.= "</select>";
	return $str.= "</select>";
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
/*
function GetAccountFromCatNum($cat_num) {
	global $itemstbl;
	global $prefix;

	$query = "SELECT account FROM $itemstbl WHERE num='$cat_num' AND prefix='$prefix'";

	$result = DoQuery($query, "GetAccountFromCatNum");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$acct = $line[0];
	return $acct;
}
*/
function GetNextDocNum($doctype) {
	global $fiscalyear;
	global $docstbl, $companiestbl;
	global $prefix;
	//adam need new macnizem
	$query = "SELECT MAX(docnum) FROM $docstbl WHERE doctype='$doctype' AND prefix='$prefix'";
	$result = DoQuery($query, __FILE__.": ".__LINE__);

	$line = mysql_fetch_array($result, MYSQL_NUM);
	$n = $line[0];
	$n++;
	//if($n == 0) {
		$query = "SELECT num1,num2,num3,num4,num5,num6,num7,num8,num9,num10 FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, __FILE__.": ".__LINE__);
	
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$a = $line[$doctype - 1];
		if($a > 0)
			$a--;
		$a++;
	//}
	if($a>$n)
		return $a;
	else 
		return $n;
	//return $n + 1;
}

function GetCurrencySymbol($curnum,$CurrArray) {
	//global $CurrArray;

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
		$rate = $line['rate'];
		return round($price * $rate, 2);
	}
	return $price;
}

function showform($posturl){
	$str='<script type="text/javascript">
			Icredit++;
			function creditMe1(){
	            var dialog = $(\'<div style="display:none" id="dialogdiv"></div>\').appendTo(\'body\');
	            dialog.load("'.$posturl.'", {}, 
	                function (responseText, textStatus, XMLHttpRequest) {
	                	var agreed = false; 
	                    dialog.dialog({
	                        hide: \'clip\',
	        				beforeclose : function() { return agreed; },
	        				title: \'Credit Card billing\',
	        				modal: true,
	                    });
	                }
	            );
			}
	            
    </script>';
	
	return $str;
}
if($step == 5) {	/* Send email */
	$doctype = (int)$_POST['type'];
	$docnum = (int)$_POST['docnum'];
	$account = (int)$_POST['account'];
	require("emaildoc.php");
	return;
}
if($step == 3) {	/* final step, put data in tables */
	$doctype = (int)$_POST['type'];
	
	global $curuser;
	$doc=new document($doctype);
	
	
	$doc->account=GetPost('account');
	$doc->company=GetPost('company');
	$doc->address=GetPost('address');
	$doc->city=GetPost('city');
	$doc->zip=GetPost('zip');
	$doc->vatnum=GetPost('vatnum');
	$doc->refnum=GetPost('refnum');
	$doc->issue_date=GetPost('issue_date');
	$doc->due_date=GetPost('due_date');
	$doc->sub_total=GetPost('total')-GetPost('vat');
	$doc->src_tax=GetPost('src_tax');
	$doc->vat=GetPost('vat');
	$doc->total=GetPost('total');
	$doc->owner=$curuser->id;
	if(isset($_POST['cat_num'])){
		$i=0;
		$b=0;		
		foreach($_POST['cat_num'] as $docdet){
			if($_POST['cat_num'][$i]!=''){
				$det=new documentDetail();
				$det->cat_num=$_POST['cat_num'][$i];
				$det->description=$_POST['description'][$i];
				$det->qty=$_POST['qty'][$i];
				$det->unit_price=$_POST['unit_price'][$i];
				$det->currency=$_POST['currency'][$i];
				$det->price=$_POST['price'][$i];
				$doc->docdetials[$b]=$det;		
				//$detial->nisprice=$nisprice[$i];//??
				$b++;
			}
			$i++;
		}
		
	}
	if(isset($_POST['sum'])){
		$i=0;
		$b=0;		
		foreach($_POST['sum'] as $docdet){
			if($_POST['sum'][$i]!=''){
				$det=new receiptDetail();
				$det->type=$_POST['ptype'][$i];
				$det->creditcompany=$_POST['bankacc'][$i];
				$det->cheque_num=$_POST['cheque_num'][$i];
				$det->bank=$_POST['bank'][$i];
				$det->branch=$_POST['branch'][$i];
				$det->cheque_acct=$_POST['cheque_acct'][$i];
				$det->cheque_date=$_POST['date'][$i];
				$det->sum=$_POST['sum'][$i];
				$doc->rcptdetials[$b]=$det;
				$b++;
				//$src_tax=$_POST['src_tax'];		//???		
			}
			$i++;
		}
		$rcpttotal=GetPost('rcpttotal');
		if($doctype==DOC_INVRCPT)
			if($doc->total!=$rcpttotal){
				ErrorReport(_('Invoice sum is not equil to recipt sum'));
				exit();
			}
			else
				$doc->total=$rcptsum;
		if($doctype==DOC_RECEIPT)
			$doc->total=$rcptsum;
			
	}
	//print_r($doc);
	$num=$doc->newDocument();//!
	$docnum=$doc->docnum;
	//print($doc->docnum);
	$doc->transaction();
	
	print "<meta http-equiv=refresh content=\"0; url=?module=showdocs&step=2&doctype=$doctype&docnum=$docnum&prefix=$prefix\" />";
	
	
	/*if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}
	
	$fromnum = (int)$_POST['fromnum'];
	$fromdoc = (int)$_POST['fromdoc'];
	
	$doctype = (int)$_POST['type'];
	$docnum = (int)$_POST['docnum'];

	global $docstbl,$prefix;
	// Make sure there is no document with same details 
	$query = "SELECT num FROM $docstbl WHERE doctype='$doctype' AND docnum='$docnum' AND prefix='$prefix'";
	$result = DoQuery($query, "step3");
	$n = mysql_num_rows($result);
	//print "test";
	if($n == 0) {	// this is first time 
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
		
		// Collect total price and VAT information 
		$sub_total = (float)$_POST['sub_total'];
		$novat_total = (float)$_POST['novat_total'];
		$asset_vat = (float)$_POST['asset_vat'];
		$vat = (float)$_POST['vat'];
		$total = (float)$_POST['total'];
		//no need global $curuser;
		$uid=$curuser->id;
		// Put data into table 
		$query = "INSERT INTO $docstbl VALUES(NULL, '$prefix', ";
		$query .= "'$doctype', '$docnum', '$account', '$company', '$address', '$city', '$zip', '$vatnum', ";
		$query .= "'$refnum', '$issue_date', '$due_date', ";
		$query .= "'$sub_total', '$novat_total', '$vat', '$total', '$src_tax', '0', '0', '$comments','$uid')";
		$result = DoQuery($query, "step3");
		$num = mysql_insert_id();
		
		$issue_date = FormatDate($issue_date, "mysql", "dmy");
		// All general details are collected, now collect items 
		// All items are array of items 
		
		//finsh haeder start details and transactions
		global $TransType;
		$transtype=$TransType[$doctype];
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
					
					// Update inventory inventory 
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
				// now get cheques data 
				
				$type = $_POST['ptype'];
				$bankacc = $_POST['bankacc'];
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
					$bkacc = $bankacc[$key];
					$chknum = htmlspecialchars($cheque_num[$key]);
					$bnk = htmlspecialchars($bank[$key]);
					$brnch = htmlspecialchars($branch[$key]);
					$acct = htmlspecialchars($cheque_acct[$key]);
					$cheque_date = $date[$key];
					$cheque_date = FormatDate($cheque_date, "dmy", "mysql");
					
					//if($type1 != 1) {
					$query = "INSERT INTO $chequestbl VALUES ('$prefix', '$num', '$type1', '$bkacc', '$chknum', '$bnk', '$brnch', '$acct', '$cheque_date', '$val', '', '')";
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
					//if($type != 1)
					//	$tnum = Transaction($tnum, $transtype, CHEQUE, $docnum, $chknum, $issue_date, '', $val * -1.0);
					//else
					if($type == 1)
						$optacc=CASH;
					else if($type==2)
						$optacc=CHEQUE;
					else if ($type==3)
						$optacc=CREDIT;
					else if ($type==4)
						$optacc=$bkacc;
					$tnum = Transaction($tnum, $transtype, $optacc, $docnum, $chknum, $issue_date, '', $val * -1.0);
				}//end foreach
		} //end ercipt data
		// Close from doc 
		 
		 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if($fromdoc) {
			$query = "UPDATE $docstbl \n";
			$query .= "SET status='1' \n";	// 1 means closed 
			$query .= "WHERE docnum='$fromnum' AND doctype='$fromdoc' AND prefix='$prefix'";
			$result = DoQuery($query, __FILE__.": ".__LINE__);
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//print ";$doctype;";
		if(($doctype == DOC_INVOICE) || ($doctype == DOC_CREDIT) || ($doctype== DOC_PROFORMA) || ($doctype== DOC_INVRCPT)) {
			// Write transactions 
			if(($doctype == DOC_INVOICE) ||($doctype==DOC_PROFORMA)||($DOC_INVRCPT))
				$t = $total * -1.0;
			else
				$t = $total;
			//adam: $issue_date = FormatDate($issue_date, "mysql", "dmy");
			//print "tryyyy just a little bit harder";
			$tnum = Transaction(0, $transtype, $account, $docnum, $refnum, $issue_date, "$company", $t);
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
			//end reg recepit detial:

		}
	}

	$url="?module=showdocs&step=2&doctype=$doctype&docnum=$docnum&prefix=$prefix;";
	
	print "<meta http-equiv=\"refresh\" content=\"0;url=$url\"> ";*/
} //end step 3
if($step == 4) {	/* copy document */
	$doctype = (int)$_GET['doctype'];
	$targetdoc = (int)$_GET['targetdoc'];
	$docnum = (int)$_GET['docnum'];
	
	$query = "SELECT * FROM $docstbl WHERE doctype='$doctype' AND docnum='$docnum' AND prefix='$prefix'";
	$result = DoQuery($query, __FILE__.": ".__LINE__);

	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$num = $line['num'];

	
	$account = $line['account'];
	$company = $line['company'];
	$address = $line['address'];
	$vatnum = $line['vatnum'];
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
	$text.= "<div style=\"display:none;\" id=\"toinvoice\" $invisible>\n";
	$text.= "<form name=\"form3\" action=\"?module=showdocs&amp;step=2\" method=\"post\" style=\"display:inline\">\n";
	$text.= "<input type=\"hidden\" name=\"doctype\" value=\"1\" />\n";
	$text.= "<input type=\"hidden\" name=\"targetdoc\" value=\"3\" />\n";
	$text.= "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	$l = _("Copy proforma");
	$text.= "<input type=\"submit\" value=\"$l\" />\n";
	$text.= "</form>\n";
	$text.= "<form name=\"form4\" action=\"?module=showdocs&amp;step=2\" method=\"post\" >\n";
	$text.= PrintInput("hidden",null,"doctype","doctype",2);
	$text.= PrintInput("hidden",null,"targetdoc","targetdoc",3);
	$text.= PrintInput("hidden",null,"account","account",$account);
	$l = _("Copy delivery doc.");
	$text.= "<input type=\"submit\" value=\"$l\" />\n";
	$text.= "</form>\n";
	//$text.= "</div>\n";
	if($targetdoc == DOC_CREDIT)
		$invisible = "";
	else
		$invisible = "class=\"para\"";
	//$text.= "<div id=\"tocredit\" $invisible>\n";
	//$text.= "<table border=\"0\" width=\"90%\"><tr><td width=\"10%\">\n";
	$text.= "<form name=\"form5\" action=\"?module=showdocs&amp;step=2\" method=\"post\" >\n";
	$text.= "<input type=\"hidden\" name=\"doctype\" value=\"3\" />\n";
	$text.= "<input type=\"hidden\" name=\"targetdoc\" value=\"4\" />\n";
	$text.= "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	$l = _("Copy invoice");
	$text.= "<input type=\"submit\" value=\"$l\" />\n";
	$text.= "</form>";
	$text.= "</div>\n";//end div
	
	
	
	
	/* main form */
	$text.= "<div style=\"padding:10px;\">\n";
	$text.= "<form name=\"form1\" id=\"documenet\" action=\"?module=docsadmin&amp;step=1\" method=\"post\">\n";
	$text.= "<input type=\"hidden\" name=\"fromnum\" value=\"$docnum\" />\n";
	$text.= "<input type=\"hidden\" name=\"fromdoc\" value=\"$doctype\" />\n";
	$text.= "<input type=\"hidden\" name=\"type\" value=\"$targetdoc\" />\n";
	$text.= '<input type="hidden" value="0" id="theValue" />';
	$text.= "<table border=\"0\" width=\"100%\" align=\"center\" class=\"formtbl\"><tr>\n"; //start form table
	
	//table doc hader
	$text.="\t<td width=\"450px\">\n\t\t<table><tr>"; //small table
	if($targetdoc!=DOC_PARCHACEORDER){
		$l = _("Customer");
		$text.= "<td>$l: </td><td>\n";
		$text.= PrintCustomerSelect($account);
		$l = _("New customer");
		$text.=newWindow($l,'?action=lister&form=account&type='.CUSTOMER,480,480,$l,'btnsmall');
		$text.= "</td></tr>\n";
	}else{
		$l = _("Supplier");
		$text.= "<td>$l: </td><td>\n";
		$text.= PrintSupplierSelect($account);
		$l = _("New supplier");
		$text.=newWindow($l,'?action=lister&form=account&type='.SUPPLIER,480,480,$l,'btnsmall');
		$text.= "</td></tr>\n";
	}
	$l = _("Company");
	$text.= "<tr><td>$l: </td><td>\n";
	$company = htmlspecialchars($company);
	$text.= PrintInput("text",null,'company','company',$company,20);
	$text.= "</td></tr>\n";
	$l = _("Address");
	$text.= "<tr><td colspan=\"1\">$l: </td><td>\n";
	$address = htmlspecialchars($address);
	$text.= PrintInput("text",null,'address','address',$address,40,"str");
	$text.= "</td></tr>\n";
	$l = _("City");
	$text.= "<tr><td>$l: </td><td>\n";
	$city = htmlspecialchars($city);
	$text.= PrintInput("text",null,'city','city',$city,20,"str");
	$l = _("Zip");
	$text.= "$l: \n";
	$text.= PrintInput("text",null,'zip','zip',$zip,6,"int");
	$l = _("Reg. num");
	$text.= "</td></tr><tr><td>$l: </td><td>";
	$text.= PrintInput("text",null,'vatnum','vatnum',$vatnum,20,"longint");
	$text.="</td></tr></table></td><td>     <table><tr>";	//end small table start small table
	if(!$valdate) {
		$valdate = date('d-m-Y');
		if($doctype < 5)
			$due_date = $valdate;
	}
	else
	$valdate = FormatDate($valdate, "mysql", "dmy");
	$l = _("Date");
	$text.= "<td>$l: <br />\n";
	$text.= PrintInput("text","date",'idate','idate',$valdate,10,"date");
	$text.= "</td>\n";
	
	$l = _("To be paid until");
	$text.= "<td>$l: <br />\n";
	$text.= PrintInput("text","date",'due_date','due_date',$due_date,10,"date");
	$text.= "</td></tr><tr>\n";
	
	$l = _("Order number");
	$text.= "<td colspan=\"2\">$l:  <br />\n";
	$text.= PrintInput("text",null,'refnum','refnum',$refnum,20,"longint");
	$text.= "</td></tr></table>\n";  //end small table
	
	
	$text.= "</td></tr></table><hr />";    //end form table
	//adam:
	/* Now the real part of an invoice, the details part.. */
	if ($targetdoc!=DOC_RECEIPT){
		$l=_("New Item");
		$text.=newWindow($l,'?action=lister&form=items',400,350,$l,'btnsmall');
		$text.= "<table class=\"formy\">\n<thead>";		/* Internal table for details */
		/* header line */
		$text.= "<tr>\n";
			$l = htmlspecialchars(_("Item"));
			$text.= "<th class=\"header\">$l</th>\n";
			$l = htmlspecialchars(_("Description"));
			$text.= "<th class=\"header\">$l</th>\n";
			$l = htmlspecialchars(_("Qty."));
			$text.= "<th class=\"header\">$l</th>\n";
			$l = htmlspecialchars(_("Price"));
			$text.= "<th class=\"header\">$l</th>\n";
			$l = htmlspecialchars(_("Currency"));
			$text.= "<th class=\"header\">$l</th>\n";
			$l = htmlspecialchars(_("Total"));
			$text.= "<th class=\"header\">$l</th>\n";
			$l = htmlspecialchars(_("Remove"));
			$text.= "<th class=\"header\" width=\"36\">$l</th>\n";
		$text.= "</tr>\n";
		$l=htmlspecialchars(_("VAT"));
		$text.= "</thead><tfoot><tr><td colspan=\"4\"></td><td>$l</td><td>".PrintInput("text",null,"vatsum","vatsum",0,8,"readonly")."</td><td></td></tr>\n";
		$l=htmlspecialchars(_("Sum"));
		$text.= "<tr><td colspan=\"4\"></td><td>$l</td><td>".PrintInput("text",null,"sum","sum",0,8,"readonly")."</td><td></td></tr></tfoot><tbody id=\"docdet\"></tbody></table>\n";
		$text.= '<script type="text/javascript">addItem(0);</script>';
		
	}
	if (($targetdoc==DOC_RECEIPT) || ($targetdoc==DOC_INVRCPT)){
			$text.= "<table class=\"formy\">\n";		/* Internal table for details */
		/* header line */
		$text.= "<thead><tr>\n";
		$l = _("Payment method");
		$text.= "<th class=\"header\"  width=\"120\">$l</th>\n";
		$l = _("Income Bank");
		$text.= "<th class=\"header\" width=\"120\">$l</th>\n";
		$l = _("Number");
		$text.= "<th class=\"header\" width=\"120\">$l</th>\n";
		$l = _("Bank");
		$text.= "<th class=\"header\">$l</th>\n";
		$l = _("Branch");
		$text.= "<th class=\"header\">$l</th>\n";
		$l = _("Account no.");
		$text.= "<th class=\"header\" width=\"120\">$l</th>\n";
		$l = _("Date");
		$text.= "<th class=\"header\">$l</th>\n";
		$l = _("Sum");
		$text.= "<th class=\"header\">$l</th>\n";
		$text.="</tr></thead>";
		$l = _("Source tax");
		$text.= "<tfoot><tr><td colspan=\"7\" align=\"left\">$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"src_tax\" size=\"6\" /></td>\n\t</tr></tfoot>";
		$text.= "<tbody id=\"rcptdet\"></tbody></table>\n";
		$text.='<script type="text/javascript">addRcpt(0);addRcpt(1);addRcpt(2);addRcpt(3);addRcpt(5);</script>';
	}
	
	$text.= "<br />\n";
	$l = _("Comments");
	$text.= "$l: <br />\n";
	$text.= "<textarea name=\"comments\"  cols=\"75\" rows=\"4\">$comments</textarea>\n";
	$l = _("Next");
	//$text.= "<br /><input type=\"submit\" value=\"$l >>>\" />\n";
	$text.="<br /><a href=\"javascript:$('#documenet').submit();\" class=\"btnaction\">$l</a>";
	
	$text.= "</form>\n";
	$text.= "</div>\n";
	createForm($text,$header,'',750,null,'img/icon_acc.png',1,getHelp());
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

	$account = (int)$_POST['account'];
	if($account == 0) {
		$l = _("No customer was chosen");
		ErrorReport("$l");
		exit;
	}

	$due_date = GetPost('due_date');
	$company = GetPost('company');
	$address = GetPost('address');
	$city = GetPost('city');
	$zip = GetPost('zip');
	$vatnum = GetPost('vatnum');
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

	//print "<div style=\"border:1px solid;width:90%;margin:5px\">\n";
	$texty='';
	$texty.= "<form action=\"?module=docsadmin\" method=\"post\" id=\"docform\">\n"; /* step will be defined later as _POST */
	$texty.=  "<input type=\"hidden\" name=\"fromdoc\" value=\"$fromdoc\" />\n";
	$texty.=  "<input type=\"hidden\" name=\"fromnum\" value=\"$fromnum\" />\n";
	$texty.=  "<input type=\"hidden\" name=\"vatnum\" value=\"$vatnum\" />\n";
	$texty.=  "<table border=\"0\" width=\"100%\" align=\"center\" class=\"formtbl\"><tr>\n";
	$l = _("To");
	$texty.=  "<td style=\"width:10%\">$l: </td>\n";
	$texty.=  "<input type=\"hidden\" name=\"account\" value=\"$account\" />\n";
	$texty.=  "<td style=\"width:60%\">$company <input type=\"hidden\" name=\"company\" value=\"$company\" /></td>\n";
	$l = _("Date");
	$texty.=  "<td style=\"width:10%\">$l: </td>\n";
	$texty.=  "<td>$date <input type=\"hidden\" name=\"idate\" value=\"$date\" /></td>\n";
	$texty.=  "</tr><tr>\n";
	$texty.=  "<td>&nbsp;</td>\n";	/* empty column */
	$texty.=  "<td>$address <input type=\"hidden\" name=\"address\" value=\"$address\" /></td>\n";
	$l = _("Order number");
	$texty.=  "<td>$l: </td>\n";
	$texty.=  "<td>$refnum <input type=\"hidden\" name=\"refnum\" value=\"$refnum\" /></td>\n";
	$texty.=  "</tr><tr>\n";
	$texty.=  "<td>&nbsp;</td>\n";	/* empty column */
	$texty.=  "<td>$city $zip</td>\n";
	$texty.=  "<input type=\"hidden\" name=\"city\" value=\"$city\" /><input type=\"hidden\" name=\"zip\" value=\"$zip\" />\n";
	$texty.=  "</tr><tr>\n";
	$texty.=  "<input type=\"hidden\" name=\"type\" value=\"$doctype\" />\n";
	$t = $DocType[$doctype];
	$texty.=  "<td colspan=\"3\" align=\"center\"><h1 style=\"text-align:center\">$t ";
	if(!$docnum)	
		$n = GetNextDocNum($doctype);
	else
		$n = $docnum;
	$texty.=  "<input type=\"hidden\" name=\"docnum\" value=\"$n\" />\n";
	$texty.=  "$n</h1></td>\n";
	$l = _("Source");
	$texty.=  "<td><h1>$l</h1></td>\n";
	$texty.=  "</tr><tr><td colspan=\"4\">\n";
	/* Check items values */
	$texty.=  "<div style=\"border:1px solid;width:100%\">\n";
	if ($doctype!=DOC_RECEIPT){
		$texty.=  "<table dir=\"rtl\" border=\"1\" width=\"100%\">\n";		/* Internal table for details */
		/* header line */
		$texty.=  "<tr class=\"tblhead1\">\n";
		$l = _("Description");
		$texty.=  "<td style=\"width:70%\">$l</td>\n";
		$l = _("Qty.");
		$texty.=  "<td style=\"width:4em\">$l</td>\n";
		$l = _("Price");
		$texty.=  "<td style=\"width:5em\">$l</td>\n";
		$l = _("Currency");
		$texty.=  "<td style=\"width:4em\">$l</td>\n";
		$l = _("Total");
		$texty.=  "<td style=\"width:4em\">$l</td>\n";
		$l = _("Total NIS");
		$texty.=  "<td>$l</td>\n";
		$texty.=  "</tr>\n"; 
		$novattotal = 0.0;
		$vattotal = 0.0;
		
		for($i = 0; $i <= 20; $i++) {//adam: 5
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
			$needvat = NeedVat($cat);		// check if this account needs VAT
			$texty.=  "<tr>\n";
			$texty.=  "<input type=\"hidden\" name=\"cat_num[]\" value=\"$cat\" />\n";
			$desc = $description[$i];
			$desc = htmlspecialchars($desc, ENT_QUOTES);
			$texty.=  "<td>$desc <input type=\"hidden\" name=\"description[]\" value=\"$desc\" /></td>\n";
			$q = (int)$qty[$i];
			$texty.=  "<td>$q <input type=\"hidden\" name=\"qty[]\" value=\"$q\" />\n";
	//		PrintUnits($cat_num);
			$texty.=  "</td>\n";
			$texty.=  "<td>$unit_price[$i] <input type=\"hidden\" name=\"unit_price[]\" value=\"$unit_price[$i]\" /></td>\n";
			$currnum = (int)$currency[$i];
			$sign = GetCurrencySymbol($currnum,$CurrArray);
			$texty.=  "<td>$sign <input type=\"hidden\" name=\"currency[]\" value=\"$currnum\" /></td>\n";
			$texty.=  "<td>$p <input type=\"hidden\" name=\"price[]\" value=\"$p\" /></td>\n";
			$nisprice = CalcNISPrice($p, $currnum, "$year-$month-$day");
			if(($p != 0) && ($nisprice == 0) ) {	// we don't have rate... 
				if(!($currdone[$currnum])) {
					$l = _("Set rate");
					$texty.=  "<td><input type=\"button\" value=\"$l\" onclick=OpenRatesWin('$day-$month-$year') /></td>\n";
					$currdone[$currnum] = 1;
					$nextstep = 1;
				}
			}
			else {
				$texty.=  "<td>$nisprice <input type=\"hidden\" name=\"nisprice[]\" value=\"$nisprice\" /></td>\n";
				if($needvat)
					$vattotal += $nisprice;
				else
					$novattotal += $nisprice;
			}
			$texty.=  "</tr>\n";
		}	//*//* end of for loop */
		$vattotal = round($vattotal, 2);	
		/* Now print totals, calculate VAT and print total including VAT */
		$texty.=  "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";	/* empty space line */
		$texty.=  "<tr><td colspan=\"2\">&nbsp;</td>\n";	/* space column */
		$l = _("Total for VAT");
		$texty.=  "<td colspan=\"3\">$l: </td>\n";
		$texty.=  "<input type=hidden name=\"sub_total\" value=\"$vattotal\" />\n";
		$texty.=  "<td>$vattotal</td></tr>\n";
		$texty.=  "<tr><td colspan=\"2\">\n";
		if($due_date) {
			$l = _("To be paid until");
			$texty.=  "$l: \n";
			$texty.=  "<input type=\"hidden\" name=\"due_date\" value=\"$due_date\" />\n";
			$texty.=  "$due_date\n";
		}
		$texty.=  "</td>\n";
		$l = _("No vat total");
		$texty.=  "<td colspan=\"3\">$l: </td>\n";
		$novattotal = round($novattotal, 2);
		$texty.=  "<input type=\"hidden\" name=\"novat_total\" value=\"$novattotal\" />\n";
		$texty.=  "<td>$novattotal</td>\n";
		$texty.=  "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";	/* empty space line */
		$texty.=  "<tr><td colspan=\"2\">$comments</td>\n";
		$texty.=  "<input type=\"hidden\" name=\"comments\" value=\"$comments\" />\n";
		$l = _("VAT");
		$texty.=  "<td colspan=\"3\">$l: </td>\n";
		$vat = CalcVAT($vattotal);
		$texty.=  "<input type=\"hidden\" name=\"vat\" value=\"$vat\" />\n";
		$texty.=  "<td>$vat</td>\n";
		$texty.=  "</tr>\n";
		
		$texty.=  "<tr><td colspan=\"2\">&nbsp;</td>\n";	/* space column */
		$l = _("Total");
		$texty.=  "<td colspan=\"3\">$l: </td>\n";
		$totalpayment = round($vattotal + $vat + $novattotal, 0);
		$texty.=  "<input type=\"hidden\" name=\"total\" value=\"$totalpayment\" />\n";
		$texty.=  "<td>$totalpayment</td>\n";
		$texty.=  "</tr>\n";
		$texty.=  "</table>\n";
}
	$texty.=  "</div>\n";
	
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
		$texty.=  "<table border=\"0\">\n";
		/* header line */
		$texty.=  "<tr class=\"tblhead1\">\n";
		$l = _("Payment method");
		$texty.=  "<td style=\"width:9em\">$l </td>\n";
		$l = _("Income Bank");
		$texty.=  "<td style=\"width:10em\">$l</td>\n";
		$l = _("Number");
		$texty.=  "<td style=\"width:8em\">$l</td>\n";
		$l = _("Bank");
		$texty.=  "<td style=\"width:12em\">$l</td>\n";
		$l = _("Branch");
		$texty.=  "<td style=\"width:3em\">$l</td>\n";
		$l = _("Account no.");
		$texty.=  "<td style=\"width:6em\">$l</td>\n";
		$l = _("Date");
		$texty.=  "<td style=\"width:6em\">$l</td>\n";
		$l = _("Sum");
		$texty.=  "<td>$l</td>\n";
		$texty.=  "</tr>\n";
		$total_sum = 0.0;
		$creditbuffer=0;
		foreach($sum as $index => $val) {
			$i++;
			if(empty($val))
				continue;
			$texty.=  "<tr>\n";
			$texty.=  "<td>\n";
			$t = $type[$index];
			$ts = $paymenttype[$t];
			$texty.=  "$ts ";
			$texty.=  "<input type=\"hidden\" name=\"ptype[$i]\" value=\"$t\" readonly />\n";
			$texty.=  "</td><td>\n";
			$t = $creditcompany[$index];
			$ts = $creditcompanies[$t];
			$texty.=  "$ts <input type=\"hidden\" name=\"creditcompany[$i]\" value=\"$t\" readonly />\n</td>\n";
			//print "";
			$cn = htmlspecialchars($cheque_num[$index], ENT_QUOTES);
			$texty.=  "<td>$cn";
			$texty.=  "<input type=\"hidden\" id=\"cheque_num$i\" name=\"cheque_num[$i]\" value=\"$cn\" readonly />\n</td>\n";
			$bn = $bank[$index];
			$bs = $banksarr[$bn];
			$texty.=  "<td>$bn - $bs";
			$texty.=  "<input type=\"hidden\" name=\"bank[$i]\" value=\"$bank[$index]\" readonly />\n</td>\n";
			$texty.=  "<td>$branch[$index]";
			$texty.=  "<input type=\"hidden\" name=\"branch[$i]\" value=\"$branch[$index]\" readonly />\n</td>\n";
			$texty.=  "<td>$cheque_acct[$index]";
			$texty.=  "<input type=\"hidden\" name=\"cheque_acct[$i]\" value=\"$cheque_acct[$index]\" readonly />\n</td>\n";
			$texty.=  "<td>$date[$index]";
			$texty.=  "<input type=\"hidden\" name=\"date[$i]\" value=\"$date[$index]\" readonly />\n</td>\n";
			$texty.=  "<td>$val";
			$texty.=  "<input type=\"hidden\" name=\"sum[$i]\" value=\"$val\" readonly />\n</td>\n";
			$texty.=  "</tr>\n";
			$total_sum += $val;
			global $curcompany;
			if($type[$index]==3)
				if($curcompany->credit!=0){
					$texty.=showform("?action=lister&form=credit&itmnum=$i&sum=$val");
					$creditbuffer++;
				}
		}
		$texty.=  "<tr><td colspan=\"6\" >&nbsp;</td>\n";
		$l = _("Source tax");
		$texty.=  "<td>$l: </td>\n";
		$texty.=  "<td>$src_tax<input type=\"hidden\" name=\"src_tax\" value=\"$src_tax\" /></td></tr>\n";
		$texty.=  "<tr><td colspan=\"6\">&nbsp;</td>\n";		/* spacer */
		$l = _("Total");
		$texty.=  "<td><b>$l: </b></td>\n";
		$total_sum += $src_tax;
		$texty.=  "<td><b><input type=\"hidden\" name=\"total\" value=\"$total_sum\" />$total_sum</b></td>\n";
		$texty.=  "</tr>\n";
		$texty.=  "</table>\n";
	}
	$texty.=  "</td></tr><tr>\n";
	$texty.=  "<td colspan=\"4\" align=\"center\">\n";
	if(!$nextstep)
		$nextstep = 3;
	if($step < 3) {
		$texty.=  "<input type=\"hidden\" name=\"step\" value=\"$nextstep\" />\n";
		if($nextstep == 3) {
			if($doctype==DOC_INVRCPT){
				if($total_sum!=$totalpayment){
					$l = _("Back");
					$texty.=  _("The sum of the invoice and the receipt is not the same");
					$texty.=  "<a href=\"#\" class=\"btn\" onClick=\"history.go(-1)\">$l</a>\n";
					///
					$texty.=  "</td></tr>\n</table>\n";
					$texty.=  "</form>\n";
					$header=_("Documenet Preview");
					
					createForm($texty,$header,'',750,null,'img/icon_acc.png',1,getHelp());
					return;
				}
			}
			$l = _("Create & print");
			if($creditbuffer=0)
				$texty.="<a href=\"javascript:$('#docform').submit();\" class=\"btnaction\">$l</a>";
			else
				$texty.="<a href=\"javascript:creditMe();\" class=\"btnaction\">$l</a>";
			
		}
		else {
			$l = _("Next");
			$texty.="<a href=\"javascript:$('#docform').submit();\" class=\"btnaction\">$l</a>";
		}
	}
	else {	/* show email form or print */
		$texty.=  "<input type=\"hidden\" name=\"step\" value=\"5\" />\n";
		$l = _("Print");
		//$texty.=  "<input type=\"button\" value=\"$l\" ";
		$texty.=newWindow($l,"printdoc.php?doctype=$doctype&amp;docnum=$docnum&amp;prefix=$prefix&amp;print_win=1",800,600,_("Document Print"),'btnprint');
		//$texty.=  "onclick=\"', 'printwin', 'width=800,height=600,scrollbar=yes')\" />\n";
	}		
//	print "step: $step, nextstep: $nextstep\n";
	$texty.=  "</td></tr>\n</table>\n";
	$texty.=  "</form>\n";
	//print "</div>\n";
	$header=_("Documenet Preview");
	createForm($texty,$header,'',750,null,'img/icon_acc.png',1,getHelp());
}
?>