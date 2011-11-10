<?php
/*
 | Calendar functions for freelance accounting system
 | Written by: Ori Idan
 | Modfied by Adam Ben Hour
 */

global $prefix, $accountstbl, $transactionstbl, $docstbl;
global $histtbl, $chequstbl;//, $receiptstbl;
global $DocType;
$text='';
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	$text.= "<h1>$l</h1>\n";
	return;
}

if(!function_exists('GetAcctType')) {
	function GetAcctType($acct) {
		global $prefix, $accountstbl, $recriptstbl;

		$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
		$result = DoQuery($query, "GetAcctType");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
}

if(!function_exists('GetAccountName')) {
	function GetAccountName($val) {
		global $accountstbl;
		global $prefix;

		$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
		$result = DoQuery($query, "GetAccountName");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
}

function NumEvents($d, $m, $y) {
	global $transactionstbl, $docstbl, $receiptstbl, $chequestbl, $histtbl, $prefix;
	global $table;
	
	$events = 0;
	/* look for supplier invoices */
	$mdate = "$y-$m-$d";
	$t = SUPINV;
	$query = "SELECT num FROM $transactionstbl WHERE date='$mdate' AND type='$t' AND prefix='$prefix'";
//	$text.= "Query: $query<br>\n";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);
	
	$t = SUPPLIERPAYMENT;
	$query = "SELECT num FROM $transactionstbl WHERE date='$mdate' AND type='$t' AND prefix='$prefix'";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);
	
	$t = VAT;
	$vatacc = PAYVAT;
	$query = "SELECT sum FROM $transactionstbl WHERE date='$mdate' AND type='$t' AND account='$vatacc' AND prefix='$prefix'";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);	

	$query = "SELECT * FROM $docstbl WHERE due_date='$mdate' AND prefix='$prefix'";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);
	//print $table["docs"]."bla";
	$query = "SELECT * FROM ".$table["docs"]." WHERE issue_date='$mdate' AND prefix='$prefix' AND doctype='".DOC_RECEIPT."' AND doctype='".DOC_INVRCPT."'";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);

	$query = "SELECT * FROM $chequestbl WHERE cheque_date='$mdate' AND prefix='$prefix'";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);

	$query = "SELECT * FROM $histtbl WHERE dt='$mdate' AND prefix='$prefix'";
	$result = DoQuery($query, "NumEvents");
	$events += mysql_num_rows($result);
	return $events;
}

function FindEvents($d, $m, $y) {
	//global $text;
	global $transactionstbl, $docstbl, $receiptstbl, $chequestbl, $histtbl, $prefix;
	global $DocType;

	$begindate = "1-1-$y";
	$enddate = "31-12-$y";
//	$text.= "<br><h1>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $d-$m-$y</h1>\n";
	
	$text.= "<table class=\"eventtbl\">\n";
	$mdate = "$y-$m-$d";
	/* look for supplier invoices */
	$t = SUPINV;
	$query = "SELECT num FROM $transactionstbl WHERE date='$mdate' AND type='$t' AND prefix='$prefix'";
	
	$result = DoQuery($query, "FindEvents");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tnum = $line['num'];
		if($tnum == $lastnum)
			continue;
		$lastnum = $tnum;
		$query = "SELECT * FROM $transactionstbl WHERE num='$tnum' AND prefix='$prefix'";
		$r = DoQuery($query, "FindEvents");
		while($l = mysql_fetch_array($r, MYSQL_ASSOC)) {
			$acct = $l['account'];
			$accttype = GetAcctType($acct);
	//		$text.= "account: $acct, type: $accttype<br>\n";
			if($accttype == SUPPLIER) {
				if($l['sum'] < 0)
					break;
				//NewRow();
				$text.="<tr>";
				$acctname = GetAccountName($acct);
				$refnum = $l['refnum1'];
				$details = $l['details'];
				$sum = number_format($l['sum']);
				$l = _("Pay supplier");
				$text.= "<td>$l: ";
//				$text.= "<td>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ©׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ§: ";
				$text.= "<a href=\"?module=acctdisp&amp;account=$acct&amp;begin=$begindate&amp;end=$enddate\">$acctname</a>&nbsp;";
				if($refnum) {
					$l = _("Reference");
					$text.= "$l: $refnum ";
//					$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $refnum ";
				}
				if($details) {
					$l = _("Details");
					$text.= "$l: $details ";
//					$text.= " ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ»׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $details ";
				}
				$l = _("Sum");
				$text.= "$l: $sum </td>\n";
//				$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum </td>\n";
				$text.= "</tr>\n";
				break;
			}	
		}
	}
	
	$t = SUPPLIERPAYMENT;
	$query = "SELECT num FROM $transactionstbl WHERE date='$mdate' AND type='$t' AND prefix='$prefix'";
//	$text.= "Query: $query<br>\n";
	$result = DoQuery($query, "FindEvents");
//	$n = mysql_num_rows($result);
//	$text.= "n: $n<br>\n";
	$lastnum = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tnum = $line['num'];
//		$text.= "tnum: $tnum<br>\n";
		if($tnum == $lastnum)
			continue;
		$lastnum = $tnum;
		$query = "SELECT * FROM $transactionstbl WHERE num='$tnum' AND prefix='$prefix'";
//		$text.= "Query: $query<br>\n";
		$r = DoQuery($query, "FindEvents");
		while($l = mysql_fetch_array($r, MYSQL_ASSOC)) {
			$acct = $l['account'];
			$accttype = GetAcctType($acct);
//			$text.= "account: $acct, type: $accttype<br>\n";
			if($accttype == SUPPLIER) {
				$sum = $l['sum'];
				if($sum > 0)
					continue;
				$sum *= -1.0;
				$acctname = GetAccountName($acct);
				$refnum = $l['refnum1'];
				$details = $l['details'];
				$sum = number_format($sum);
				//NewRow();
				$text.="<tr>";
				$l = _("Supplier payment");
				$text.= "<td>$l: ";
				$text.= "<a href=\"?module=acctdisp&amp;account=$acct&amp;begin=$begindate&amp;end=$enddate\">$acctname</a>&nbsp;";
				if($refnum) {
					$l = _("Reference");
					$text.= "$l: $refnum ";
//					$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $refnum ";
				}
				if($details) {
					$l = _("Details");
					$text.= "$l: $details ";
//					$text.= " ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ»׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $details ";
				}
				$l = _("Sum");
				$text.= "$l: $sum </td>\n";
//				$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum </td>\n";
				$text.= "</tr>\n";
				break;
			}
			if($accttype == AUTHORITIES) {
				$sum = $l['sum'];
				if($sum > 0)
					continue;
				$sum *= -1.0;
				$acctname = GetAccountName($acct);
				$refnum = $l['refnum1'];
				$details = $l['details'];
				$sum = number_format($sum);
				//NewRow();
				$text.="<tr>";
//				$l = _("Payment to");
//				$text.= "<td>$l: ";
				$text.= "<td>";
//				$text.= "<td>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ©׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ§: ";
				$text.= "<a href=\"?module=acctdisp&amp;account=$acct&amp;begin=$begindate&amp;end=$enddate\">$acctname</a>&nbsp;";
				if($refnum) {
					$l = _("Reference");
					$text.= "$l: $refnum ";
//					$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $refnum ";
				}
				if($details) {
					$l = _("Details");
					$text.= "$l: $details ";
//					$text.= " ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ»׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $details ";
				}
				$l = _("Sum");
				$text.= "$l: $sum </td>\n";
//				$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum </td>\n";
				$text.= "</tr>\n";
				break;
			}				
		}
	}
	
	$t = VAT;
	$query = "SELECT num FROM $transactionstbl WHERE date='$mdate' AND type='$t' AND prefix='$prefix'";
//	$text.= "Query: $query<br>\n";
	$result = DoQuery($query, "FindEvents");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tnum = $line['num'];
//		$text.= "tnum: $tnum<br>\n";
		if($tnum == $lastnum)
			continue;
		$lastnum = $tnum;
		$query = "SELECT * FROM $transactionstbl WHERE num='$tnum' AND prefix='$prefix'";
//		$text.= "Query: $query<br>\n";
		$r = DoQuery($query, __LINE__);
		while($l = mysql_fetch_array($r, MYSQL_ASSOC)) {
			$acct = $l['account'];
			$accttype = GetAcctType($acct);
//			$text.= "$acct, $accttype<br>\n";
			if($accttype == AUTHORITIES) {
				if($acct == PAYVAT) {
					$sum = $l['sum'];
					if($sum > 0)
						break;
					$sum *= -1.0;
					$refnum = $l['refnum1'];
					$details = $l['details'];
					$sum = number_format($sum);
					//NewRow();
					$text.="<tr>";
					$l = _("VAT payment");
					$text.= "<td><a href=\"?module=acctdisp&amp;account=$acct&amp;begin=$begindate&amp;end=$enddate\">$l</a>&nbsp;";
					if($refnum) {
						$l = _("Reference");
						$text.= "$l: $refnum ";
//						$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $refnum ";
					}
					if($details) {
						$l = _("Details");
						$text.= "$l: $details ";
//						$text.= " ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ»׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $details ";
					}
					$l = _("Sum");
					$text.= "$l: $sum </td>\n";
//					$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum </td>\n";
					$text.= "</tr>\n";
					break;
				}
			}
		}
	}

	$query = "SELECT * FROM $docstbl WHERE due_date='$mdate' AND prefix='$prefix'";
	$result = DoQuery($query, "FindEvents");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$type = $line['doctype'];
		$docnum = $line['docnum'];
		$company = $line['company'];
		$account = $line['account'];
		$sum = (double)$line['sub_total'] + (double)$line['novat_total'];
		$sum = number_format($sum);
		$total = number_format($line['total']);
		$DocTypeStr = $DocType[$type];
		//NewRow();
		$text.="<tr>";
		$text.= "<td>$DocTypeStr <a href=\"printdoc.php?win=1&amp;doctype=$type&amp;docnum=$docnum&amp;prefix=$prefix\">$docnum</a> ";
		$l = _("To customer");
		$text.= "$l <a href=\"?module=acctdisp&amp;account=$account&amp;begin=$begindate&amp;end=$enddate\">$company</a> ";
		$l = _("Sum");
		$text.= "$l: $sum ";
//		$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum ";
		$l = _("Including VAT");
		$text.= "$l: $total</td>\n";
//		$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢\"׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $total</td>\n";
		$text.= "</tr>\n";
	}
	/*
	$query = "SELECT * FROM $docstbl WHERE issue_date='$mdate' AND doctype='".DOC_RECEIPT."' AND prefix='$prefix'";
	$result = DoQuery($query, "FindEvents");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$type = DOC_RECEIPT;
		$docnum = $line['num'];//adam:
		$sum = (double)$line['sum'];
		$sum = number_format($sum);
		$DocTypeStr = $DocType[$type];
		//NewRow();
		$text.="<tr>";
		$text.= "<td>$DocTypeStr <a href=\"printdoc.php?win=1&amp;doctype=$type&amp;docnum=$docnum&amp;prefix=$prefix\">$docnum</a> ";
		$text.= _("Sum") . ": $sum </td>\n";
//		$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ³׳²ֲ²ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum </td>\n";
		$text.= "</tr>\n";
	}*/
	
	$query = "SELECT * FROM $chequestbl WHERE cheque_date='$mdate' AND prefix='$prefix'";
	$result = DoQuery($query, "FindEvents");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$type = $line['type'];
		$cheque_num = $line['cheque_num'];
		$sum = $line['sum'];
		$refnum = $line['refnum'];
		$q = "SELECT company FROM $docstbl WHERE num='$refnum'";
		$r = DoQuery($q, "FindEvents");
		$l = mysql_fetch_array($r, MYSQL_NUM);
		$company = $l[0];
		//NewRow();
		$text.="<tr>";
		$l = _("Cheque deposit");
		$text.= "<td>$l $cheque_num ";
//		$text.= "<td>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ§׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ©׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ§ $cheque_num ";
		$text.= _("From: ") . "$company ";
//		$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½$company ";
		$text.= _("Total of") . ": $sum" . _("NIS");
//		$text.= "׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¿׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ½: $sum ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ©\"׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½";
		$text.= "</td></tr>\n";
	}
	
	$query = "SELECT * FROM $histtbl WHERE dt='$mdate' AND prefix='$prefix'";
	$result = DoQuery($query, "FindEvents");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$acctname = GetAccountName($num);
		$details = nl2br($line['details']);
		$l = _("Contact with");
		//NewRow();
		$text.="<tr>";
		$text.= "<td>$l $acctname<br>";
		$text.= "$details";
		$text.= "</td></tr>\n";
	}
	$text.= "</table>\n";
	return $text;
}

function calendar($d, $m, $y) {
	global $module, $lang;

//	$text.= "lang: $lang<br>\n";
	$daysarr = array("S", "M", "T", "W", "T", "F", "Sat");
	$montharr = array("", _("Jan."), _("Feb."), _("March"), _("Apr."),
		_("May"), _("June"), _("July"), _("Aug."), _("Sep."), _("Oct."), _("Nov."), _("Dec.")
	);
	
	$hebday = array('׳³ֲ³׳’ג‚¬ן¿½', '׳³ֲ³׳’ג‚¬ן¿½', '׳³ֲ³׳’ג‚¬ג„¢', '׳³ֲ³׳’ג‚¬ֻ�', '׳³ֲ³׳�ֲ¿ֲ½', '׳³ֲ³׳’ג‚¬ֲ¢','׳³ֲ³ײ²ֲ©');
	$hebday = array(_("Sun"), _("Mon"), _("Tue"), _("Wed"), _("Thu"), _("Fri"), _("Sat"));
	
	$curdate = getDate();
	//If no parameter is passed use the current date.
	if($d == 0) {
		$date = $curdate;
		$d = $date['mday'];
		$m = $date['mon'];
		$y = $date['year'];
	}
	else
		$date = getDate(mktime(0, 0, 0, $m, $d, $y));

	$day = $date["mday"];
	$month = $date["mon"];
	$month_name = $montharr[$date["mon"]];
	$year = $date["year"];

	$this_month = getDate(mktime(0, 0, 0, $month, 1, $year));
	$next_month = getDate(mktime(0, 0, 0, $month + 1, 1, $year));

	//Find out when this month starts and ends.
	$first_week_day = $this_month["wday"];
	$days_in_this_month = round(($next_month[0] - $this_month[0]) / (60 * 60 * 24));

	$calendar_html = "<div id=\"mcal\">\n";
	$calendar_html .= "<table dir=\"ltr\">";

	/* previous year link */
	$py = $y - 1;
	$calendar_html .= "<tr><td><a href=\"?module=$module&amp;d=$d&amp;m=$m&amp;y=$py\">";
	$calendar_html .= "<img src=\"img/prev_year.gif\" style=\"border:none\" alt=\"prev_year\" /></a></td>\n";
	/* calculate previous month link */
	$pd = $d;
	$pm = $m - 1;
	if($pm == 0) {
		$py = $y - 1;
		
		$pm = 12;
	}
	else
		$py = $y;
	$purl = "?module=$module&amp;d=$pd&amp;m=$pm&amp;y=$py";
	$calendar_html .= "<td><a href=\"$purl\">&nbsp;<img src=\"img/prev_mon.gif\" style=\"border:none\" alt=\"prev_month\" />&nbsp;</a></td>\n";
	$calendar_html .= "<td colspan=\"3\" style=\"border:none\">\n";
/*	$calendar_html .= "<table border=\"0\"><tr>";
	$calendar_html .= "<td>$year</td>\n";
	$calendar_html .= "<td align=\"right\" dir=\"rtl\" style=\"font-size:11;font-family:arial\">"; */
	$calendar_html .= "<div>";
	$calendar_html .= $month_name . " " . $year . "</div></td>\n";
//	$calendar_html .= $month_name . "</td>\n";
//	$calendar_html .= "</tr></table></td>\n";
	/* calculate next month link */
	$nd = $d;
	$nm = $m + 1;
	if($nm == 13) {
		$ny = $y + 1;
		$nm = 1;
	}
	else
		$ny = $y;
	$nurl = "?module=$module&amp;d=$nd&amp;m=$nm&amp;y=$ny";
	$calendar_html .= "<td><a href=\"$nurl\">&nbsp;<img src=\"img/next_mon.gif\" alt=\"next_month\" style=\"border:none\" />&nbsp;</a></td>";
	$ny = $y + 1;
	$calendar_html .= "<td><a href=\"?module=$module&amp;d=$d&amp;m=$m&amp;y=$ny\">";
	$calendar_html .= "<img src=\"img/next_year.gif\" alt=\"next_year\" style=\"border:none\" /></a></td>\n";
	$calendar_html .= "</tr><tr>";
	for($i = 0; $i < 7; $i++) {
		if($lang != 'he')
			$ds = $daysarr[$i];
		else
			$ds = $hebday[$i];
		$calendar_html .= "<th>$ds</th>\n";
	}
	$calendar_html .= "</tr>\n";

	//Fill the first week of the month with the appropriate number of blanks.
	$st = $this_month[0] - ($first_week_day * 60 * 60 * 24);
	$sa = getDate($st);
	$fd = $sa['mday'];
	$calendar_html .= "<tr>";
	for($week_day = 0; $week_day < $first_week_day; $week_day++) {
		$cl = ($week_day == 6) ? "weekend othermonth" : "othermonth";
		$calendar_html .= "<td class=\"$cl\">$fd</td>";
		$fd++;
	}

	$week_day = $first_week_day;
	for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++) {
		$week_day %= 7;

		if($week_day == 0)
			$calendar_html .= "</tr><tr>";

		$n = NumEvents($day_counter, $month, $year);
		if($n > 0) { /* Events url */
			$url = "?module=$module&amp;d=$day_counter&amp;m=$month&amp;y=$year";
			$durl = "<a href=\"$url\">$day_counter</a>";
		}
		else
			$durl = $day_counter;
		$cl = ($week_day == 6) ? "class=\"weekend\"" : '';

		//Do something different for the current day.
//		$text.= "day_counter: $day_counter, day: $day<br>\n";
		if(($day == $day_counter) && ($month == $curdate['mon']) && ($year == $curdate['year'])) {
			if($cl)
				$cl = "class=\"weekend selected\"";
			else
				$cl = "class=\"selected\"";
		}
		$calendar_html .= "<td align=\"center\" $cl>" .  $durl . " </td>";

		$week_day++;
	}
	// fill the last week of the month
	$fd = 1;
	for( ; $week_day < 7; $week_day++) {
		$cl = ($week_day == 6) ? "weekend othermonth" : "othermonth";
		$calendar_html .= "<td class=\"$cl\">$fd</td>";
		$fd++;
	}

	$calendar_html .= "</tr>";
	$calendar_html .= "</table>";
	$calendar_html .= "</div>\n";

	return($calendar_html);
}

$today = date("d-m-Y");
list($d, $m, $y) = split('-', $today);
$d = isset($_GET['d']) ? $_GET['d'] : $d;
$m = isset($_GET['m']) ? $_GET['m'] : $m;
$y = isset($_GET['y']) ? $_GET['y'] : $y;
$calendar_html = calendar($d, $m, $y);

$action = isset($_GET['action']) ? $_GET['action'] : '';
if($action == 'showevents') {
	$text.= FindEvents($d, $m, $y);
	
	$text.= "<br />\n";
	$text.= "<form><input type=\"button\" value=\"׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¬׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ³׳³ֲ³ײ²ֲ²׳²ֲ²ײ²ֲ²׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨\" onclick=\"history.back()\"></form>\n";
}
else {
	$text.= $calendar_html;
	$n = NumEvents($d, $m, $y);
	if($n > 0) {
		$l = _("Events for date");
		$text.= "<br /><h2>$l: $d-$m-$y</h2>\n";
		$text.=FindEvents($d, $m, $y);
	}
}
?>