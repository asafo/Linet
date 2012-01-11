<?PHP
/*
 | Bank transaction match handling script for Drorit Free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
if(!isset($prefix) || ($prefix == '')) {
	ErrorReport(_("This operation can not be executed without choosing a business first"));
	// "<h1>$l</h1>\n";
	return;
}

global $namecache;
global $accountstbl, $bankbooktbl, $transactionstbl;
global $dir;
global $correlationtbl,$curuser;
function PrintAccountSelect() {
	global $prefix, $accountstbl;

	$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' ORDER BY company";
	$result = DoQuery($query,__FILE__.": ".__LINE__);
	
	$str= "<select id=\"account\" name=\"account[]\">\n";
	$l = _("Choose account");
	$str.= "<option value=\"0\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$str.= "<option value=\"$num\">$name</option>\n";
	}
	$str.= "</select>\n";
	return $str;
}



?>
<script type="text/javascript">
function CheckNeg(index) {
	var nega = document.getElementsByClassName('negsum');
	var neg = nega[index];
	var posa = document.getElementsByClassName('possum');
	var pos = posa[index];
	
	if(neg.value > 0)
		document.getElementsByClassName('possum')[index].value = 0;
}

function CheckPos(index) {
	var nega = document.getElementsByClassName('negsum');
	var neg = nega[index];
	var posa = document.getElementsByClassName('possum');
	var pos = posa[index];
	
	if(pos.value > 0)
		document.getElementsByClassName('negsum')[index].value = 0;
}

function CalcExtSum() {
	var vals = document.getElementsByClassName('ext');
	var sum = document.getElementsByClassName('ext_sum');
	var t = document.form1.ext_total;
	
	size = vals.length;
	total = parseFloat("0.0");
	t.value = '';
	if(size) {
		for(i = 0; i < size; i++) {
			if(vals[i].checked) {
				total += parseFloat(sum[i].value);
			}
		}
	}
	else {
		if(vals.checked)
			total = parseFloat(sum.value);
	}
	total = Math.round(total * 100)/100;
	t.value = total;
}

function CalcIntSum() {
	var vals = document.getElementsByClassName('int');
	var sum = document.getElementsByClassName('int_sum');
	var t = document.form1.int_total;
	
	size = vals.length;
	total = parseFloat("0.0");
	t.value = '';
	if(size) {
		for(i = 0; i < size; i++) {
			if(vals[i].checked) {
				total += parseFloat(sum[i].value);
			}
		}
	}
	else {
		if(vals.checked)
			total = parseFloat(sum.value);
	}
	total = Math.round(total * 100)/100;
	t.value = total;
}	


function go(){
	var bil=true;//CalcSum();
	if(parseFloat($('#ext_total').val())!=parseFloat($('#int_total').val())){
		var sum=(-1)*(parseFloat($('#ext_total').val())-parseFloat($('#int_total').val()));
		var account =parseFloat($('#account').val());
		//alert('we r not balanced!');
		var dialog = $('<div dir="rtl" id="dialogdiv"></div>').appendTo('body');
		dialog.load("?action=lister&form=voucher&sum="+sum+"&acc="+account, {}, 
		        function (responseText, textStatus, XMLHttpRequest) {
		        	var agreed = false; 
		            dialog.dialog({resizable: false,height:500,width:780,hide: 'clip',title: ''});
		            dialog.bind('dialogclose', function(event) {
			            var acc=$('#account').val();
		            	window.location.href='?module=extmatch&bankacc='+acc;
		            });
		        }
		    );
		bil=false;
	}
	if(bil)
		document.form1.submit();
}
$(document).ready(function(){
	$("#form").validate({
		   submitHandler: function(form) {
			   go();
		   }
	   });
});
</script>


<?PHP
//<div class="form righthalf1">
$haeder = _("Bank reconciliation");
//print "<h3>$l</h3>\n";
$text='';
$bankacc = isset($_GET['bankacc']) ? $_GET['bankacc'] : 0;

if(!$bankacc) {
	/* Choose account */
	$text.= "<form name=\"choosebank\" action=\"\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"extmatch\">\n";
	$text.= "<div class=\"formtbl\" style=\"padding-right:10px;font-size:16px\">\n";
	$t = BANKS;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$t' AND prefix='$prefix'";
	$result = DoQuery($query, "Select account");
	$l = _("Choose bank account");
	$text.= "<h2>$l</h2><br>\n";
	$i = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		if($num > 100) {
			$acctname = $line['company'];
			$text.= "<input type=\"radio\" name=\"bankacc\" value=\"$num\" ";
			if($i == 0)
				$text.= "checked";
			$i++;
			$text.= " />&nbsp;$acctname\n";
		}
	}
	$text.= "<br />\n";
	$l = _("Execute");
	$text.= "<div style=\"text-align:center\"><input type=\"submit\" value=\"$l\" class='btnaction' /></div>\n";
	$text.= "</div>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	createForm($text,$haeder,'',750,'','',1,getHelp());
	return;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'extbalance') {
	$int_str = GetPost('int_str');
	$int = split(',', $int_str);
	$ext_str = GetPost('ext_str');
	$ext = split(',', $ext_str);
	
	$date = $_POST['date'];
	list($day, $month, $year) = split('-', $date);
	if($year < 100) {
		if($year < 70)
			$year += 2000;
		else
			$year += 1900;
	}
	if(!checkdate($month, $day, $year)) {
		// print "$day - $month - $year<BR>\n";
		$l = _("Invalid date");
		ErrorReport("$l");
		exit;
	}
	$refnum1 = GetPost('refnum1');
	$refnum2 = GetPost('refnum2');
	$details = GetPost('details');
	
	$accounts = $_POST['account'];
	$negsum = $_POST['negsum'];
	$possum = $_POST['possum'];
	
	/* put sums into one array */
	$t = 0.0;
	foreach($accounts as $i => $val) {
		$sum[$i] = $negsum[$i] * -1;
		$sum[$i] += $possum[$i];
		$t += $sum[$i];
	}
	if($t != 0.0) {
		$l = _("Unbalanced transaction");
		ErrorReport("$l");
		exit;
	}
	$tnum = 0;
	foreach($accounts as $account) {
		if($account == 0) {
			$l = _("No account specified");
			ErrorReport($l);
			return;
		}
	}
	foreach($accounts as $i => $account) {
		if(empty($sum[$i]))
			continue;
		if(empty($account))
			continue;
		if(!strpos($sum[$i], '.'))
			$sum_str = "$sum[$i].00";
		else
			$sum_str = $sum[$i];
		$tnum = Transaction($tnum, BANKMATCH, $account, $refnum1, $refnum2, $date, $details, $sum[$i]);
	}
//	print "<h2>׳³ג€�׳³ֳ—׳³ֲ ׳³ג€¢׳³ֲ¢׳³ג€� ׳³ֲ ׳³ֲ¨׳³ֲ©׳³ן¿½׳³ג€� ׳³ג€˜׳³ג€�׳³ֲ¦׳³ן¿½׳³ג€”׳³ג€�</h2>\n";
	$s = $sum[$i] * -1.0;
	$int[] = "$tnum:$s";
	$action = 'match';
}
if($action == 'extmatch') {
	if(empty($int))
		$int = $_POST['int'];//zchot
	if(empty($ext))
		$ext = $_POST['ext'];//hova
	/* Claculate sum of transaction and create a string with all numbers */
	$int_str = '';
	$total = 0.0;
	if(is_array($int)) {
		foreach($int as $val) {
			if(!empty($int_str))
				$int_str .= ',';
			list($num, $sum) = explode(':', $val);
			$int_str .= $num;
			$total += $sum;
		}
	}
//	print "Transactions: $int_str<br />\n";
//	print "Internal transactions sum: $total<br /><br />\n";

	/* Claculate sum of external transaction and create a string with all numbers */
	$ext_str = '';
	$ext_total = 0.0;
	if(is_array($ext)) {
		foreach($ext as $val) {
			if(!empty($ext_str))
				$ext_str .= ',';
			$ext_str .= $val;
			$query = "SELECT sum FROM $bankbooktbl WHERE num='$val' AND prefix='$prefix'";
			$result = mysql_query($query);
			if(!$result) {
				echo mysql_error();
				exit;
			}
			while($line = mysql_fetch_array($result, MYSQL_NUM)) {
				$sum = $line[0];
				$ext_total += $sum;
			}
		}
	}
//	print "External transactions: $ext_str<BR>\n";
//	print "External transactions sum: $ext_total<BR>\n";
//	print "Internal transactions sum: $total<br>\n";
	$r = $total - $ext_total;
//	print "r: $r<br>\n";
	if(($r <= 0.01) && ($r >= 0)) {	/* balanced match */
		/* go over all internal transactions and update cor_num */
		//$query = "INSERT INTO $correlationtbl VALUES ('$prefix', '$cor_num', 'hova', 'zchot', '".OPEN."', '$uid;');";
		$cor_num=maxSql(array('prefix'=>$prefix), "num", $correlationtbl);
		$uid=$curuser->id;
		$query = "INSERT INTO $correlationtbl VALUES ('$prefix', '$cor_num', 'E$ext_str', '$int_str', '".OPEN."', '$uid');";
		DoQuery($query, __FILE__.":".__LINE__);
		if(is_array($int)) {
			foreach($int as $val) {
				list($num, $sum) = explode(':', $val);
				$sum = $sum * -1.0;
				$query = "UPDATE $transactionstbl SET cor_num='$cor_num' WHERE num='$num' AND sum='$sum' AND account='$bankacc' AND prefix='$prefix'";
				$result = mysql_query($query);
				if(!$result) {
					echo mysql_error();
					exit;
				}
			}
		}
		/* now do the same thing for external transactions */
		if(is_array($ext)) {
			foreach($ext as $val) {
				$query = "UPDATE $bankbooktbl SET cor_num='$cor_num' WHERE num='$val' AND account='$bankacc' AND prefix='$prefix'";
				$result = mysql_query($query);
				if(!$result) {
					echo mysql_error();
					exit;
				}
			}
		}
	}
	else {//shuld point out
		$l = _("Unbalanced reconciliation, please create balancing transaction");
		//$text.= $l;
		ErrorReport($l);
		return;
	}
}

//print "</div>\n";	/* end of righthalf used for caption */
//print "<br><br><br>\n";
//print "<div class=\"innercontent\">\n";
$text.=   "<form id=\"form\" name=\"form1\" action=\"?module=extmatch&amp;action=extmatch&amp;bankacc=$bankacc\" method=\"post\">\n";
$text.=   "<input type=\"hidden\" id=\"account\" name=\"account\" value=\"$bankacc\" />";
$text.=  "<table><tr>\n";
$l = _("External page transactions");
$text.=  "<td align=\"right\"><h2>$l</h2></td>\n";
$text.=  "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
$l = _("Internal bank account transactions");
$text.=  "<td align=\"right\"><h2>$l</h2></td>\n";
// <td align=\"right\"><h2>׳³ֳ—׳³ֲ ׳³ג€¢׳³ֲ¢׳³ג€¢׳³ֳ— ׳³ג€˜׳³ג€÷׳³ֲ¨׳³ֻ�׳³ג„¢׳³ֲ¡ ׳³ג€˜׳³ֲ ׳³ֲ§</h2></td>
$text.= '</tr><tr><td valign="top">';

$text.=  "<table class=\"formy\"><tr>\n";
$text.=  "<th>&nbsp;</th>\n";

$l = _("Date");
$text.=  "<th>$l</th>\n";
$l = _("Ref. num.");
$text.=  "<th>$l</th>\n";
$l = _("Details");
$text.=  "<th>$l</th>\n";
$l = _("Sum");
$text.=  "<th>$l</th>\n";
$text.=  "</tr>\n";
/* Show external bank books */
$query = "SELECT * FROM $bankbooktbl WHERE cor_num='0' AND prefix='$prefix' AND account='$bankacc'";
	/* only unmatched transactions */
$result = DoQuery($query, __FILE__.": ".__LINE__);

while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$date = FormatDate($line['date'], "mysql", "dmy");
	$refnum = $line['refnum'];
	$details = stripslashes($line['details']);
//	$details = htmlspecialchars($details);
	$sum = $line['sum'];
	$text.=  "<tr>\n";
	$text.=  "<td><input type=\"checkbox\" class=\"ext\" name=\"ext[]\" value=\"$num\" onchange=\"CalcExtSum()\" /></td>\n";
	$text.=  "<td>$date</td>\n";
	$text.=  "<td>$refnum</td>\n";
	$text.=  "<td>$details</td>\n";
	$text.=  "<td>$sum<input type=\"hidden\" class=\"ext_sum\" name=\"ext_sum[]\" value=\"$sum\" /></td>\n";
	$text.=  "</tr>\n";
}
$text.=  "<tr><td colspan=\"4\">&nbsp;</td>\n";
$text.=  "<td><input type=\"text\" id=\"ext_total\" name=\"ext_total\" size=\"6\" readonly value=\"0\" dir=\"ltr\"></td>\n";
$text.= '</table><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign="top">';


$text.=  "<table class=\"formy\"><tr>\n";
$text.=  "<th>&nbsp;</th>\n";

$l = _("Tran. type");
$text.=  "<th>$l</th>\n";
$l = _("Date");
$text.=  "<th>$l</th>\n";
$l = _("Ref. num.");
$text.=  "<th>$l</th>\n";
$l = _("Details");
$text.=  "<th>$l</th>\n";
$l = _("Sum");
$text.=  "<th>$l</th>\n";
$text.=  "</tr>\n";

global $TranType;
/* Show internal bank account */
$query = "SELECT * FROM $transactionstbl WHERE account='$bankacc' AND cor_num='0' AND prefix='$prefix'";	/* only unmatched transactions */
$result = DoQuery($query, __LINE__);
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	//if ($type==OPBALANCE)
	//	continue;
	$num = $line['num'];
	$date = FormatDate($line['date'], "mysql", "dmy");
	$refnum = $line['refnum1'];
	$type = $line['type'];
	$details = $line['details'];
	$sum = $line['sum'];
	$sum *= -1;
	if ($type==OPBALANCE)
		continue;
	$text.=  "<tr>\n";
	$text.=  "<td><input type=\"checkbox\" class=\"int\" name=\"int[]\" value=\"$num:$sum\" onchange=\"CalcIntSum()\"></td>\n";
	$text.=  "<td>$TranType[$type]</td>\n";
	$text.=  "<td>$date</td>\n";
	$text.=  "<td>$refnum</td>\n";
	$text.=  "<td>$details</td>\n";
	$text.=  "<td dir=\"ltr\">$sum<input type=\"hidden\" class=\"int_sum\" name=\"int_sum[]\" value=\"$sum\"></td>\n";
	$text.=  "</tr>\n";
	
}
$text.=  "<tr><td colspan=\"5\">&nbsp;</td>\n";
$text.=  "<td><input type=\"text\" id=\"int_total\" name=\"int_total\" size=\"6\" readonly value=\"0\" dir=\"ltr\"></td>\n";
$text.= '</table></td>';

$l = _("Reconciliate");
$text.=  "</tr><tr><td colspan=\"3\" align=\"center\"><br><input type=\"submit\" value=\"$l\" class='btnaction' /></td></tr>\n";
$text.=  "</table>\n</form>\n</div>";
createForm($text,$haeder,'',750,'','',1,getHelp());
?>
