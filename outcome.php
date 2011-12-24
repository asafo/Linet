<?PHP
/*
 | Outcome
 | Outcome module for Drorit accounting system
 | Written Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $opt;
//global $EvenLine;
//global $lang, $dir;
$text='';
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}
$opt = isset($_GET['opt']) ? $_GET['opt'] : '';

$query = "SELECT vat FROM $companiestbl WHERE prefix='$prefix'";
$result = DoQuery($query, "outcome.php");
$line = mysql_fetch_array($result, MYSQL_NUM);
$vat = $line[0];

?>
<script type="text/javascript">


$(document).ready(function() 
	    { 
	        $("#outcome").tablesorter(); 
	    } 
	); 
function Fix2(v) {
	v = parseFloat(v) * 100.0;
	v = Math.round(v);
	
	return v / 100.0;
}

function Fix0(v) {
	return Math.round(v);
}

function CalcTotal() {
	var total = document.outcome.novattotal.value;
<?PHP	print "\tvat = $vat\n";?>
//	alert(document.outcome.pvat.value);
//	vat = vat * parseFloat(document.outcome.pvat.value) / 100.0;
	var calcvat = parseFloat(total) * parseFloat(vat) / 100.0;
//	alert(document.outcome.pvat.value);
	if(parseFloat(document.outcome.pvat.value) == parseFloat('0.0'))
		calcvat = parseFloat('0.0');
	document.outcome.vat.value = Fix2(calcvat);
	document.outcome.total.value = Fix2(parseFloat(total) + calcvat);
}

function CalcVAT() {
	var total = document.outcome.total.value;
<?PHP
	print "\tvat = $vat\n";
?>
//	vat = vat * parseFloat(document.outcome.pvat.value) / 100.0;
	if(parseFloat(document.outcome.pvat.value) == 0)
		vat = 0;
//	alert(vat);
	var v = 1.0 + parseFloat(vat) / 100.0;
	var novattotal = parseFloat(total) / v;
	document.outcome.novattotal.value = Fix2(novattotal);
	document.outcome.vat.value = Fix2(novattotal * parseFloat(vat) / 100.0);
}

function ochange() {
	var o = document.outcome.outcome.value;
	
//	alert(o);
	switch(o) {
<?PHP
	global $opt;

	if($opt == 'asset')
		$t = ASSETS;
	else
		$t = OUTCOME;
	$t2 = OBLIGATIONS;
	$query = "SELECT num,src_tax FROM $accountstbl WHERE prefix='$prefix' AND (type='$t' OR type='$t2')  ORDER BY company ASC";
	$result = DoQuery($query, "income.php");
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$t = $line[1];
		if($t == '')
			$t = 100;
		$n = $line[0];
		print "\t\tcase '$n':\n";
		print "\t\t\tdocument.outcome.pvat.value = $t;\n";
	//	print "alert(document.outcome.pvat.value);\n";
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
/*
function PrintSupplierSelect($def) {
	global $accountstbl;
	global $prefix;
	
	$t = SUPPLIER;
	$t1 = AUTHORITIES;
	$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND (type='$t' OR type='$t1')  ORDER BY company ASC";
	$result = DoQuery($query, "outcome.php");
	$text.= "<select name=\"supplier\">\n";
	$l = _("Choose supplier");
	$text.= "<option value=\"__NULL__\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$n = $line[0];
		$company = $line[1];
		if($n == $def)
			$text.= "<option value=\"$n\" selected>$company</option>\n";
		else
			$text.= "<option value=\"$n\">$company</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}*/
/*
function PrintOutcomeSelect($def) {
	global $accountstbl;
	global $prefix;
	global $opt;

	if($opt == 'asset')
		$t = ASSETS;
	else
		$t = OUTCOME;
	$t2 = OBLIGATIONS;
	$query = "SELECT num,company,src_tax FROM $accountstbl WHERE prefix='$prefix' AND (type='$t' OR type='$t2')  ORDER BY company ASC";
	$result = DoQuery($query, "outcome.php");
	$text.= "<select name=\"outcome\" onchange=\"ochange()\">\n";
	$l = _("Choose outcome");
	$text.= "<option value=\"__NULL__\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$n = $line['num'];
		$o = $line['company'];
		$v = $line['src_tax'];
		if($v == '')
			$v = 100;
		$l = _("Credited VAT");
		$val = "$o ($l: $v%)";
//		$val = "$o (׳³ן¿½׳³ֲ¢\"׳³ן¿½ ׳³ן¿½׳³ג€¢׳³ג€÷׳³ֲ¨: $v%)";
//		$val .= " $v";
		if($n == $def)
			$text.= "<option value=\"$n\" selected>$val</option>\n";
		else
			$text.= "<option value=\"$n\">$val</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}*/

$step = isset($_GET['step']) ? $_GET['step'] : 0;
if($step > 0) {
	$supplier = $_POST['supplier'];
	if($supplier == "") {
		$l = _("No supplier was chosen");
		ErrorReport("$l");
		return;
	}
	$outcome = $_POST['outcome'];
	$pvat = $_POST['pvat'];
	$date=$_POST['date'];
//	list($outcome, $pvat) = explode('|', $os);
	$refnum = GetPost('refnum');
	$details = GetPost('details');
	$novattotal = $_POST['novattotal'];
//	print "pvat: $pvat ";
	$av = $vat * $pvat / 100.0;
//	print "av: $av<br>\n";
	if($av == 0)
		$tvat = 0;
	else
		$tvat = $novattotal * $vat / 100.0;
	$tvat = round($tvat, 2);
	$total = round($novattotal + $tvat, 2);
	if(isset($_POST['date'])) {
		$dtmysql = FormatDate($_POST['date'], "dmy", "mysql");
		$dt = FormatDate($dtmysql, "mysql", "dmy");
	}
	else {
		$dtmysel = date("Y-m-d");
		$dt = FormatDate($dtmysql, "mysql", "dmy");
	}
	
	if(($outcome == "") && ($total > 0.1)) {
		$l = _("No outcome account was chosen");
		ErrorReport("$l");
		return;
	}
}
if($step == 2) {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	/* This is the actual data handling */
	if(@ValidDate($dt)) {
		$l = _("Invalid date");
		ErrorReport("$l");
		return;
	}
	if(abs($total) > 0.01) {	
		/* write transactions */
		//print "why wont you work";
		// Transaction 1 ׳³ג€“׳³ג€÷׳³ג€¢׳³ֳ— ׳³ג€�׳³ֲ¡׳³ג‚×׳³ֲ§ ׳³ג€˜׳³ג€÷׳³ן¿½ ׳³ג€�׳³ֲ¡׳³ג€÷׳³ג€¢׳³ן¿½
		$tnum = Transaction(0, SUPINV, $supplier, $refnum, '', $dt, $details, $total);
		// Transaction 2 ׳³ג€”׳³ג€¢׳³ג€˜׳³ֳ— ׳³ן¿½׳³ֲ¢"׳³ן¿½ ׳³ֳ—׳³ֲ©׳³ג€¢׳³ן¿½׳³ג€¢׳³ֳ— ׳³ן¿½׳³ג‚×׳³ג„¢ ׳³ג€�׳³ן¿½׳³ֲ¢"׳³ן¿½ ׳³ג€�׳³ן¿½׳³ג€¢׳³ג€™׳³ג€�׳³ֲ¨ ׳³ן¿½׳³ֲ¡׳³ֲ¢׳³ג„¢׳³ֲ£ ׳³ג€�׳³ג€�׳³ג€¢׳³ֲ¦׳³ן¿½׳³ג€�
		$tvat = $novattotal * $av / 100.0;
//		print "novattotal: $novattotal, av: $av, tvat: $tvat<br>\n";
		$acct = ($opt == 'asset') ? ASSETVAT : BUYVAT;
		$tnum = Transaction($tnum, SUPINV, $acct, $refnum, '', $dt, '', $tvat * -1.0);
		// Transaction 3 ׳³ג€”׳³ג€¢׳³ג€˜׳³ֳ— ׳³ֲ¡׳³ֲ¢׳³ג„¢׳³ֲ£ ׳³ג€�׳³ג€�׳³ג€¢׳³ֲ¦׳³ן¿½׳³ג€� ׳³ג€˜׳³ג„¢׳³ֳ—׳³ֲ¨׳³ג€�
		$novattotal = $total - $tvat;
		$tnum = Transaction($tnum, SUPINV, $outcome, $refnum, '', $dt, $details, $novattotal * -1.0);
		if($opt == 'asset') {
			$header = _("Asset outcome registered");
			//print "<h1>$l</h1>\n";
		}
		else {
			$header = _("Outcome registered");
			//print "<h1>$l</h1>\n";
		}
	}else{print "help:$total";}
	$outcome = "__NULL__";
	$supplier = "__NULL__";
	$refnum = '';
	$dt = '';
	$total = 0.0;
	$details = '';
	$novattotal = 0;
	$tvat = 0;
	$step = 0;
}
if($opt == 'asset')
	$optact = "&amp;opt=asset";
//print "<div class=\"form righthalf1\">\n";
if($step == 1) {
	if($opt == 'asset') {
		$header = _("Validate registration of asset outcome");
		//print "<h3>$l</h3>\n";
	}
	else {
		$header = _("Validate registration of outcome");
		//print "<h3>$l</h3>\n";
	}
	$l = _("Check the details and press update");
	//adam:
	$text.= "<h2>$l</h2>\n";
	$nextstep = 2;
}
if($step == 0) {
	if($opt == 'asset') {
		$header = _("Registration of asset outcome");
		//print "<h3>$l</h3>\n";
	}
	else {
		$header = _("Registration of outcome");
		//print "<h3>$l</h3>\n";
	}
	$nextstep = 1;
	$supplier = "";
	$outcome = "";
}
$text.= "<form name=\"outcome\" id=\"outcome\" action=\"?module=outcome&amp;step=$nextstep$optact\" method=\"post\">\n";
$text.= "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
//$l = _("Supplier");
$l = _("Outcome account");
$text.= "<td>$l: </td>\n";
$text.= "<td>\n";

$text.=PrintSelect($outcome,OUTCOME);
if($step == 0) {
	$l = _("new outcome");
	$text.=newWindow($l,'?action=lister&form=account&type='.OUTCOME,480,480,'','btnsmall');
	//$t = SUPPLIER;
	//$l = _("new supplier");
	//$text.=newWindow($l,'?action=lister&form=account&type='.SUPPLIER,480,480,'','btnsmall');
	/////$text.= "&nbsp;&nbsp;<a href=\"index.php?module=acctadmin&amp;type=$t&amp;ret=outcome\">$l</a>\n";
}
$text.= "</td></tr>\n";
$text.= "<tr>\n";

if($opt == 'asset') {
	$l = _("Asset outcome account");
	$text.= "<td>$l: </td>\n";
}
else {
	$l = _("Supplier");
	$text.= "<td>$l: </td>\n";
}
$text.= "<td>\n";

/* print "(׳³ן¿½׳³ֲ¢\"׳³ן¿½ ׳³ן¿½׳³ג€¢׳³ג€÷׳³ֲ¨: "; */
if(!isset($pvat))
	$pvat = 100;
$text.= "<input type=\"hidden\" name=\"pvat\" value=\"$pvat\" size=\"3\" />\n";
if($step == 0) {
	if($opt == 'asset') {
		$t = ASSETS;
		$l = _("new assets");
		//$text.= "&nbsp;&nbsp;<a href=\"index.php?module=acctadmin&amp;type=$t&amp;ret=outcome\">$l</a>\n";
		$text.=PrintSelect($outcome,ASSETS);
		$text.=newWindow($l,'?action=lister&form=account&type='.ASSETS,480,480,'','btnsmall');
	}
	else {
		//$t = OUTCOME;
		//$l = _("new outcome");
		//$text.= "&nbsp;&nbsp;<a href=\"index.php?module=acctadmin&amp;type=$t&amp;ret=outcome\">$l</a>\n";
		//$text.=PrintSelect($outcome,OUTCOME);
		$t = SUPPLIER;
		$l = _("new supplier");
		$text.=PrintSupplierSelect($supplier);
		$text.=newWindow($l,'?action=lister&form=account&type='.SUPPLIER,480,480,'','btnsmall');
		//$text.=newWindow($l,'?action=lister&form=account&type='.OUTCOME,480,480,'','btnsmall');
	}
}else{
	if($opt == 'asset') {
		$t = ASSETS;
		$l = _("new assets");
		//$text.= "&nbsp;&nbsp;<a href=\"index.php?module=acctadmin&amp;type=$t&amp;ret=outcome\">$l</a>\n";
		$text.=PrintSelect($outcome,ASSETS);
		//$text.=newWindow($l,'?action=lister&form=account&type='.ASSETS,480,480,'','btnsmall');
	}
	else {
		$t = SUPPLIER;
		$l = _("new supplier");
		$text.=PrintSupplierSelect($supplier);
		//$text.=newWindow($l,'?action=lister&form=account&type='.SUPPLIER,480,480,'','btnsmall');
		//$t = OUTCOME;
		//$l = _("new outcome");
		//$text.= "&nbsp;&nbsp;<a href=\"index.php?module=acctadmin&amp;type=$t&amp;ret=outcome\">$l</a>\n";
		//$text.=PrintSelect($outcome,OUTCOME);
		//$text.=newWindow($l,'?action=lister&form=account&type='.OUTCOME,480,480,'','btnsmall');
	}
}
$text.= "</td></tr>\n";
$text.= "<tr>\n";

$l = _("Date");
$text.= "<td>$l: </td>\n";
$text.= "<td><input class=\"date\" id=\"date\" type=\"text\" name=\"date\" value=\"$dt\" size=\"7\" />\n";
//$text.='<script type="text/javascript">addDatePicker("#date","'.$dt.'");</script>';
$text.= "</td>\n";
$text.= "</tr><tr>\n";

$l = _("Reference");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"refnum\" value=\"$refnum\" size=\"15\" /></td>\n";
$text.= "</tr><tr>\n";

$l = _("Details");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"details\" value=\"$details\" size=\"25\" /></td>\n";
$text.= "</tr><tr>\n";

$l = _("Sum before VAT");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"novattotal\" value=\"$novattotal\" dir=\"ltr\" size=\"10\" onblur=\"CalcTotal()\" /></td>\n";
$text.= "</tr><tr>\n";

$l = _("VAT");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"vat\" size=\"10\" dir=\"ltr\" value=\"$tvat\" readonly=\"readonly\" /></td>\n";
$text.= "</tr><tr>\n";

$l = _("Sum including VAT");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"total\" size=\"10\" dir=\"ltr\" value=\"$total\" onblur=\"CalcVAT()\" /></td>\n";
$text.= "</tr><tr>\n";

$l = _("Update");
$text.= "<td colspan=\"2\" align=\"center\"><a href=\"javascript:$('#outcome').submit();\" class=\"btnaction\">$l</a>\n";
$text.= "</td></tr>\n";
$text.= "</table>\n";
$text.= "</form>\n";
//print "</div>\n";
if($step == 0) {
	require('lasttran.inc.php');
}

if(!$ismobile)
	createForm($text,$header,'',750,'','img/icon_acc.png',1,getHelp());
else
	print $text;

//print "<div class=\"innercontent\">\n";

//print "</div>\n";

?>
