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
	var ca = document.createElement('td');
	var cb = document.createElement('td');
	var cc = document.createElement('td');
	var cd = document.createElement('td');
	var ce = document.createElement('td');
	var cf = document.createElement('td');
	var cg = document.createElement('td');
	var ch = document.createElement('td');
	
	ca.innerHTML= "<?PHP print PrintPaymentType(); ?>";
	cb.innerHTML= "<?PHP print PrintBankSelect();?>";
	cc.innerHTML= "<input type=\"text\" class=\"number\" id=\"cheque_num"+num+"\" name=\"cheque_num[]\" size=\"8\" />";
	cd.innerHTML= "<input type=\"text\" class=\"number\" id=\"bank"+num+"\" name=\"bank[]\" size=\"1\" />";
	ce.innerHTML= "<input type=\"text\" class=\"number\" id=\"branch"+num+"\" name=\"branch[]\" size=\"1\" />";
	cf.innerHTML= "<input type=\"text\" class=\"number\" id=\"cheque_acct"+num+"\" name=\"cheque_acct[]\" size=\"8\" />";
	cg.innerHTML= "<input type=\"text\" class=\"required\" id=\"date"+num+"\" value=\"<?php print date('d-m-Y');?>\" class=\"\" name=\"date[]\" size=\"7\" />";
	ch.innerHTML= "<input type=\"text\" class=\"number\" id=\"sum"+num+"\" class=\"sum\" name=\"sum[]\" onblur=\"CalcRcptSum('sum','"+num+"')\" size=\"6\" />";

	r.appendChild(ca);
	r.appendChild(cb);
	r.appendChild(cc);
	r.appendChild(cd);
	r.appendChild(ce);
	r.appendChild(cf);
	r.appendChild(cg);
	r.appendChild(ch);
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
	ca.innerHTML = "<input type=\"text\" id=\"AC"+num+"\" placeholder=\"<?php print _("Fill me …"); ?>\" class=\"number cat_num\" name=\"cat_num[]\" onblur=\"SetPartDetails("+num+")\" size=\"5\"/>\n";
	ca.innerHTML += "<input type=\"hidden\" id=\"SVAT"+num+"\" value=\"100\" name=\"svat[]\" />";
	cb.innerHTML = "<input type=\"text\" id=\"DESC"+num+"\" class=\"description\" name=\"description[]\" size=\"20\" />";
	cc.innerHTML ="<input type=\"text\" id=\"QTY"+num+"\" class=\"number qty\" name=\"qty[]\" size=\"3\" onblur=\"CalcPrice("+num+")\" />"+createNumBox("QTY",num,1);
	cd.innerHTML ="<input type=\"text\" id=\"UNT"+num+"\" class=\"number unit_price\" name=\"unit_price[]\" size=\"6\" onblur=\"CalcPrice("+num+")\" />"+createNumBox("UNT",num,10);
	ce.innerHTML="<?php print PrintCurrencySelect(null,$CurrArray);?>";
	cf.innerHTML ="<input type=\"text\" class=\"sum\" id=\"PRICE"+num+"\" class=\"number price\" name=\"price[]\" size=\"8\" readonly=\"yes\" />";
	cg.innerHTML="<a href=\"javascript:addItem("+num+");\" class=\"btnadd\"><?php print _("Add"); ?></a>";
		
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
	var elements = $('[id^=PRICE]');
	var selements = $('[id^=SVAT]');
	var vattotal=0;
	var subtotal=0;
	var novat_total=0;
	var vat=<?php print $curcompany->vat; ?>;
	for (var i=0; i<elements.length; i++) {
		var itemtotal=parseFloat($('#'+elements[i].id).val());
		var vatper= parseFloat($('#'+selements[i].id).val());
		if(vatper!=0){
			subtotal+=itemtotal;
			vattotal+=itemtotal*(vat/100);
		}else{
			novat_total+=itemtotal;
		}
	}
	$('#vatsum').val(vattotal.toFixed(2));
	$('#sub_total').val(subtotal.toFixed(2));
	$('#novat_total').val(novat_total.toFixed(2));
	$('#total').val((subtotal+novat_total+vattotal).toFixed(2));
}
function CalcRcptSum(type,id){
	var elements = $("[id^=sum]");
	var sum=0;
	for (var i=0; i<elements.length; i++) {
		//alert(elements[i].id);
		if(elements[i].id=='sum') continue;
		if($('#'+elements[i].id).val()!='')
			sum+=parseFloat($('#'+elements[i].id).val());
	}
	$('#rcptsum').val((sum).toFixed(2));
}
function SetPartDetails(index) {
	var part = $('#AC'+index).val();
	$.post("index.php",  {"action": "lister" ,"selector" : 1, "data": "Item", "num": part},
			function(data) {
				$('#DESC'+index).val(data.name);
				$('#CUR'+index).val(data.currency);
				$('#UNT'+index).val(data.defprice);
				$('#SVAT'+index).val(data.vat);
				$('#QTY'+index).focus();
			}, "json")
			.error(function() { });
}

function CalcDueDate(valdate, pay_terms) {
	var em=0;
	//var matchPos1 =pay_terms.search(/\+/);
	pay_terms=parseInt(pay_terms);
	if(pay_terms>=0){
		em=0;
	}else{
		em=1;
		pay_terms=pay_terms*-1;
		}
	
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
		
	}
	
	D = new Date(year, month - 1, day);
	D.setDate(D.getDate()+pay_terms);
	day = D.getDate();
	month = D.getMonth()+1;
	year = D.getFullYear();
	if(month >= 12) {
		month = 1;
		year += 1;
	}
	duedate.value = day + "-" + month + "-" + year;
}
function ochange(){
	var idate = document.getElementById('idate');
	$.post("index.php",  {"action": "lister" ,"selector" : 1, "data": "Account", "num": $("#acc").val()},
			function(data) {
				$("#company").val(data.company);
				$("#address").val(data.address);
				$("#city").val(data.city);
				$("#zip").val(data.zip);
				$("#vatnum").val(data.vatnum);

				var pay_terms=data.pay_terms;
				
				CalcDueDate(idate.value, pay_terms);
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
function creditMe1(i,sum){//
    var dialog = $('<div style="" id="dialogdiv"></div>').appendTo('body');
    dialog.load("?action=lister&form=credit&itmnum="+i+"&sum="+sum, {}, 
        function (responseText, textStatus, XMLHttpRequest) {
        	var agreed = false; 
            dialog.dialog({
                hide: 'clip',
				beforeclose : function() { return agreed; },
				title: 'Credit Card billing',
				modal: true
            });
        }
    );
}
function billMe(url){
	$("#documenet").validate({
		   submitHandler: function(form) {
			   billMe1();
		   }
	   });
}
function billMe1(){
		var credit=false;
		var elements = $('[id^=ptype]');
		for (var i=0; i<elements.length; i++) {
			if($('#'+elements[i].id).val()=='3'){//credit card
				credit=true;
				creditMe1(i,$("#sum"+(i+1)).val());
			}
		}
		if(!credit)
			$('#documenet').Submit();
}
function invrcptMe(url){
	 $("#documenet").validate({
		   submitHandler: function(form) {
				if($('#rcptsum').val()==$('#sum').val()){
					billMe1();
				}else{
					alert('<?php print _("Invoice sum is not equil to recipt sum");?>');
					return ;
				}
			}
	});
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
	$doc->sub_total=GetPost('sub_total');
	$doc->novat_total=GetPost('novat_total');
	$doc->total=GetPost('total');
	$doc->comments=GetPost('comments');
	$doc->owner=$curuser->id;
	if(isset($_POST['cat_num'])){
		$i=0;
		$b=0;		
		foreach($_POST['cat_num'] as $docdet){
			
			if($_POST['cat_num'][$i]!=''){
				$acct = GetAccountFromCatNum($_POST['cat_num'][$i]);
				if($acct == 0) {
					$l = _("Income account not defined").", ";
					$l .= _("for item:").$_POST['description'][$i];
					ErrorReport("$l");
					exit;
				}
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
		$rcpttotal=GetPost('rcptsum');
		if($doctype==DOC_INVRCPT)
			if($doc->total!=$rcpttotal){
				ErrorReport(_('Invoice sum is not equil to recipt sum'));
				exit();
			}
			//else
			//	$doc->total=$rcpttotal;
		if($doctype==DOC_RECEIPT)
			$doc->total=$rcpttotal;
			
	}
	//print_r($doc);
	$num=$doc->newDocument();//!
	$docnum=$doc->docnum;
	//print($doc->docnum);
	$doc->transaction();
	
	print "<meta http-equiv=refresh content=\"0; url=?module=showdocs&step=2&doctype=$doctype&docnum=$docnum&prefix=$prefix\" />";
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
	$text.= "<form name=\"documenet\" id=\"documenet\" action=\"?module=docsadmin&amp;step=3\" method=\"post\" class=\"valform\">\n";//adam:step used to be 1
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
	$text.= PrintInput("text","number",'zip','zip',$zip,6,"int");
	$l = _("Reg. num");
	$text.= "</td></tr><tr><td>$l: </td><td>";
	$text.= PrintInput("text","number",'vatnum','vatnum',$vatnum,20,"longint");
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
	$text.= PrintInput("text","number",'refnum','refnum',$refnum,20,"longint");
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
		$text.= "</thead><tfoot><tr><td colspan=\"4\"></td><td>$l</td><td>".PrintInput("text",null,"vat","vatsum",0,8,"readonly")."</td><td></td></tr>\n";
		$text.=PrintInput("hidden",null,"sub_total","sub_total");
		$text.=PrintInput("hidden",null,"novat_total","novat_total");
		$l=htmlspecialchars(_("Sum"));
		$text.= "<tr><td colspan=\"4\"></td><td>$l</td><td>".PrintInput("text",null,"total","total",0,8,"readonly")."</td><td></td></tr></tfoot><tbody id=\"docdet\"></tbody></table>\n";
		$text.= '<script type="text/javascript">$(document).ready(function(){addItem(0);});</script>';
		
	}
	if (($targetdoc==DOC_RECEIPT) || ($targetdoc==DOC_INVRCPT)){
			$text.= "<table class=\"formy\">\n";		/* Internal table for details */
		/* header line */
		$text.= "<thead><tr>\n";
		$l = _("Payment method");
		$text.= "<th class=\"header\" width=\"120\">$l</th>\n";
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
		$text.= "<td><input type=\"text\" name=\"src_tax\" size=\"6\" /></td>\n\t</tr>";
		$l = _("Total");
		$text.= "<tr><td colspan=\"7\" align=\"left\">$l: </td>\n<td><input type=\"text\" id=\"rcptsum\" name=\"rcptsum\" size=\"6\" /></td></tr></tfoot>";
		$text.= "<tbody id=\"rcptdet\"></tbody></table>\n";
		$text.='<script type="text/javascript">$(document).ready(function(){addRcpt(0);addRcpt(1);addRcpt(2);addRcpt(3);addRcpt(5);});</script>';
	}
	
	$text.= "<br />\n";
	$l = _("Comments");
	$text.= "$l: <br />\n";
	$text.= "<textarea name=\"comments\"  cols=\"75\" rows=\"4\">$comments</textarea>\n";
	$l = _("Next");
	$text.= "<br /><input type=\"submit\" value=\"$l >>>\" />\n";
	if($targetdoc==DOC_INVRCPT)
		$text.="<script type=\"text/javascript\">invrcptMe('$url');</script>";//addsubmit event handler
		//$text.="<br /><a href=\"javascript:invrcptMe();\" class=\"btnaction\">$l</a>";
	elseif($targetdoc==DOC_RECEIPT)
			$text.="<script type=\"text/javascript\">billMe('$url');</script>";//addsubmit event handler
			//$text.="<br /><a href=\"javascript:billMe();\" class=\"btnaction\">$l</a>";//chek invoicesum==recptsum credit billing
		//else
			//$text.="<br /><a href=\"javascript:$('#documenet').submit();\" class=\"btnaction\">$l</a>";//chek invoicesum==recptsum credit billing
	$text.= "</form>\n";
	$text.= "</div>\n";
	createForm($text,$header,'',750,null,'img/icon_acc.png',1,getHelp());
}
if($step > 0) {//preview
}
?>