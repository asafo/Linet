<?PHP
/*
 | Accounts transactions match handling script for Drorit Free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

global $accountstbl, $transactionstbl;
global $namecache;
global $TranType;
global $dir;

function PrintAccountSelect() {
	global $accountstbl, $prefix;

	$type1 = CUSTOMER;
	$type2 = SUPPLIER;
	$text='';
	$query = "SELECT num,company FROM $accountstbl WHERE type='$type1' AND prefix='$prefix' ORDER BY company ASC";
	$result = DoQuery($query, __LINE__);
	$text.= "<select id=\"account\" name=\"account\">\n";
	$l = _("Select account");
	$text.= "<option value=\"0\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$text.= "<option value=\"$num\">$name</option>\n";
	}
	$query = "SELECT num,company FROM $accountstbl WHERE type='$type2' AND prefix='$prefix' ORDER BY company ASC";
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$text.= "<option value=\"$num\">$name</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function GetAccountName($account) {
	global $accountstbl, $prefix;
	global $namecache;	/* name cache for account names we already found */
	
	if($namecache) {
		$name = $namecache[$account];
		if($name) 	/* we have a cache hit */
			return $name;
	}
	
	$query = "SELECT company FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
//	print "Query: $query<BR>\n";
	$result = DoQuery($query, __LINE__);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$name = $line[0];
	$namecache[$account] = $name;
	return $name;
}

?>
<script type="text/javascript">
function CalcDebitSum() {
	var vals = document.getElementsByClassName('debit');
	var sum = document.getElementsByClassName('debit_sum');
	var t = document.form1.debit_total;
	
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
	t.value = total;
}

function CalcCreditSum() {
	var vals = document.getElementsByClassName('credit');
	var sum = document.getElementsByClassName('credit_sum');
	var t = document.form1.credit_total;
	
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
	t.value = total;
}
</script>

<br>
<div class="form righthalf1">
<?PHP
$l = _("Accounts reconciliations");
print "<h3>$l</h3>\n";

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 2) {
	$debit = $_POST['debit'];
	$credit = $_POST['credit'];
	$account = $_POST['account'];
	
	$debit_str = '';
	$total = 0.0;

	if(is_array($debit)) {
	//	$debit = array_unique($debit);
		foreach($debit as $val) {
			/* $val is transaction number in debit side */
			$query = "SELECT sum FROM $transactionstbl WHERE num='$val' AND account='$account' AND prefix='$prefix'";
			$result = mysql_query($query);
			if(!$result) {
				echo mysql_error();
				exit;
			}
			while($line = mysql_fetch_array($result, MYSQL_NUM)) {
				$sum = $line[0];
				$total += $sum;
				if(!empty($debit_str))
					$debit_str .= ',';
				$debit_str .= $val;
			}
		}
	}
	$credit_str = '';
	if(is_array($credit)) {
	//	$credit = array_unique($credit);
		foreach($credit as $val) {
			/* $val is transaction number in debit side */
			$query = "SELECT sum FROM $transactionstbl WHERE num='$val' AND account='$account' AND prefix='$prefix'";
			$result = mysql_query($query);
			if(!$result) {
				echo mysql_error();
				exit;
			}
			while($line = mysql_fetch_array($result, MYSQL_NUM)) {
				$sum = $line[0];
//				print "sum: $sum<br />\n";
				$total += $sum;
				if(!empty($credit_str))
					$credit_str .= ',';
				$credit_str .= $val;
			}
		}
	}
//	print "total: $total<BR>\n";
//	print "debit_str: $debit_str<BR>\n";
//	print "credit_str: $credit_str<BR>\n";
	if(($total > 0.01) || ($total < -0.01)) {
		$l = _("Unbalanced reconciliation");
		ErrorReport("$l");
		exit;
	}
	/* balanced match so put debit_str for all credit side transactions and credit_str for all debit side */
//	print_r($credit);
//	print_r($debit);
//	print "<BR>debit: $debit_str<BR>credit: $credit_str<BR>\n";
	foreach($credit as $val) {
		$query = "UPDATE $transactionstbl SET cor_num='$debit_str' ";
		$query .= "WHERE num='$val' AND account='$account' AND prefix='$prefix'";
//		print "Query: $query<BR>\n";
		$result = mysql_query($query);
		if(!$result) {
			echo mysql_error();
			exit;
		}
	}
	foreach($debit as $val) {
		$query = "UPDATE $transactionstbl SET cor_num='$credit_str' ";
		$query .= "WHERE num='$val' AND account='$account' AND prefix='$prefix'";
		$result = mysql_query($query);
		if(!$result) {
			echo mysql_error();
			exit;
		}
	}
	$step = 1;
}
if($step == 1) {
	$account = $_POST['account'];
	
	if($account == 0) {
		$l = _("No account chosen");
		ErrorReport("$l");
		exit;
	}
	
	print "</div>\n";	/* end of righthalf */
	print "<div class=\"form innercontent\">\n";
	$l = _("Account");
	print "<h2>$l: \n";
	echo GetAccountName($account);
	print "</h2>\n";
	
	print "<form name=\"form1\" action=\"?module=intmatch&amp;step=2\" method=\"post\">\n";
	print "<input type=\"hidden\" name=\"account\" value=\"$account\">\n";
	print "<table><tr>\n";
	$l = _("Debit transactions");
	print "<td align=\"right\"><h2>$l</h2></td>\n";
	print "<td style=\"background:white\">&nbsp;&nbsp;</td>\n";
	$l = _("Credit transactions");
	print "<td align=\"right\"><h2>$l</h2></td>\n";
	print "</tr><tr><td valign=\"top\">\n";
	print "<table dir=\ltr\" border=\"1\"><tr class=\"tblhead\">\n";
	print "<td>&nbsp;</td>\n";
	$l = _("Tran. type");
	print "<td>$l</td>\n";
	$l = _("Date");
	print "<td>$l</td>\n";
	$l = _("Ref. num");
	print "<td>$l</td>\n";
	$l = _("Sum");
	print "<td>$l</td>\n";
	print "</tr>\n";
	
	/* Now the actual work of printing transactions in debit side */
	$query = "SELECT * FROM $transactionstbl WHERE account='$account' AND sum<0 AND prefix='$prefix'";
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$cor = $line['cor_num'];
		if(($cor != '') && ($cor != 0))
			continue;
		$num = $line['num'];
		$type_str = $TranType[$line['type']];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$refnum = $line['refnum1'];
		$sum = $line['sum'];
		$sum *= -1.0;
		print "<tr>\n";
		print "<td><input type=\"checkbox\" class=\"debit\" name=\"debit[]\" value=\"$num\" onchange=\"CalcDebitSum()\"></td>\n";
		print "<td>$type_str</td>\n";
		print "<td>$date</td>\n";
		print "<td>$refnum</td>\n";
		print "<td>$sum</td><input type=\"hidden\" class=\"debit_sum name=\"debit_sum[]\" value=\"$sum\"></TD>\n";
		print "</TR>\n";
	}
	print "<tr><td colspan=\"4\">&nbsp;</td>\n";
	print "<td><input type=\"text\" name=\"debit_total\" value=\"0\" size=\"5\" readonly></td></tr>\n";
	print "</table>\n";
	print "<td style=\"background:white\">&nbsp;&nbsp;</td>\n";
	print "</td><td valign=\"top\">\n";
	print "<table dir=\"$dir\" border=\"1\"><tr class=\"tblhead\">\n";
	print "<td>&nbsp;</td>\n";
	$l = _("Tran. type");
	print "<td>$l</td>\n";
	$l = _("Date");
	print "<td>$l</td>\n";
	$l = _("Ref. num");
	print "<td>$l</td>\n";
	$l = _("Sum");
	print "<td>$l</td>\n";
	print "</tr>\n";
	
	/* Now the actual work of printing transactions in credit side */
	$query = "SELECT * FROM $transactionstbl WHERE account='$account' AND sum>0 AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$cor = $line['cor_num'];
		if(($cor != '') && ($cor != 0))
			continue;
		$num = $line['num'];
		$type_str = $TranType[$line['type']];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$refnum = $line['refnum1'];
		$sum = $line['sum'];
		print "<tr>\n";
		print "<td><input type=\"checkbox\" class=\"credit\" name=\"credit[]\" value=\"$num\" onchange=\"CalcCreditSum()\"></td>\n";
		print "<td>$type_str</td>\n";
		print "<td>$date</td>\n";
		print "<td>$refnum</td>\n";
		print "<td>$sum<input type=\"hidden\" class=\"credit_sum\" name=\"credit_sum[]\" value=\"$sum\"></td>\n";
		print "</tr>\n";
	}
	print "<tr><td colspan=\"4\">&nbsp;</td>\n";
	print "<td><input type=\"text\" name=\"credit_total\" value=\"0\" size=\"5\" readonly></td></tr>\n";
	print "</table>\n";
	print "</td></tr>\n";
	$l = _("Reconciliate");
	print "<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" value=\"$l\"></td></tr>\n";
	print "</table>\n";
	print "</form>\n";
	print "<br><br>\n";
	print "</div>\n";
	print "<div class=\"form righthalf1\">\n";
}

?>
<form action="?module=intmatch&amp;step=1" method="post">
<table border="0" width="100%" class="formtbl">
<?PHP
$l = _("Select account");
print "<tr><td>$l: </td>\n";
?>
<td>
<?PHP PrintAccountSelect(); ?>
</td>
</tr><tr><td>&nbsp;</td></tr>
<?PHP
$l = _("Select");
print "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td></tr>\n";
?>
</table>
</form>
</div>
<?PHP
if($step == 0) {
	print "<div class=\"lefthalf1\">\n";
	ShowText('intmatch');
	print "</div>\n";
}
?>
