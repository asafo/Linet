<?PHP
/*
 | Display transactions for card
 | Written by Ori Idan Helicon technologies Ltd. 2004
 | Modifed By Adam Ben Hour 10/2011
 | This program is a free software licensed under the GPL 
 */
global $transactionstbl, $accountstbl;
global $TranType;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	ErrorReport($l);
	return;
}

function GetOppBalance($account, $dt) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<'$dt' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetAcctTotal($account, $dt) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<='$dt' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetOppositAccount($num, $sum) {
	global $transactionstbl, $prefix;

	$query = "SELECT account,sum FROM $transactionstbl WHERE num='$num' AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	if($sum < 0.0)
		$neg = 1;
	else
		$neg = 0;
	$maxsum = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$account = $line['account'];
		$tsum = $line['sum'];
		if($neg == 1) {	/* we are looking for positive sums */
			if($tsum > 0.0) {
				if($tsum > $maxsum) {
					$maxsum = $tsum;
					$retacct = $account;
				}
			}
		}
		else {		/* we are looking for negative sums */
			if($tsum < 0.0) {
				if($tsum < $maxsum) {
					$maxsum = $tsum;
					$retacct = $account;
				}
			}
		}
			
	}
	return $retacct;
}

function GetAccountName($account) {
	global $accountstbl, $prefix;
	
	$query = "SELECT company FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br />\n";
		echo mysql_error();
		exit;
	}
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$name = $line['company'];
	return $name;
}

function GetAccountType($account) {
	global $accountstbl, $prefix;
	
	$query = "SELECT type FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br />\n";
		echo mysql_error();
		exit;
	}
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$type = $line['type'];
	return $type;
}

$acct = $_GET['account'];
$accttype = GetAccountType($acct);
$company = GetAccountName($acct);
$begin = isset($_REQUEST['begin']) ? $_REQUEST['begin'] : '';
$end = isset($_REQUEST['end']) ? $_REQUEST['end'] : date('d-m-Y');
$filerep = isset($_REQUEST['file']) ? $_REQUEST['file'] : 0;

if($end != '') {
	if($begin == 'start') {
		$d = date("m-Y");
		list($m, $y) = explode('-', $d);
		$begin = "1-1-$y";
	}
	if($end == 'today')
		$end = date("d-m-Y");
	$begindate = FormatDate($begin, "dmy", "mysql");
	$enddate = FormatDate($end, "dmy", "mysql");

	if(!isset($module)) {
		$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "GetAccountName");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$str = $line[0];
		print "<h1>$str</h1>\n";	
	}

	if($filerep) {
		$l = _("Transactions report for account");
		//$haeder= "$l: $company\n";
	}
	else {
		$l = _("Display transactions for account");
	
	}	
	$haeder= "$l: $company";
	$text='';
	if($begin != '') {
		$l = _("From date");
		$text.= "<h2>$l: $begin ";
		$l = _("To date");
		$text.= "$l: $end</h2>\n";
	}
	
//	print "<div class=\"innercontent\">\n";
	if($filerep) {
		$filename = "tmp/acct$acct.csv";
		$fd = fopen($filename, 'w');
		$l = _("Transaction");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Type");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Date");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Ref. num");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Details");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Opp. account");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Debit");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Credit");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\",");
		$l = _("Acct. balance");
		$l = iconv("UTF-8", "windows-1255", $l );
		fwrite($fd, "\"$l\"\n");
	}
	else {
		//print "<table dir=\"rtl\" border=\"0\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
		$l = _("Transaction");
		$curtablehd= "<thead><tr><th>$l&nbsp;</th>\n";
		$l = _("Type");
		$curtablehd.= "<th style=\"width:7em\">$l&nbsp;</th>\n";
		$l = _("Date");
		$curtablehd.= "<th style=\"width:6em\">$l</th>\n";
		$l = _("Ref. num");
		$curtablehd.= "<th style=\"width:5em\">$l&nbsp;</th>\n";
		$l = _("Details");
		$curtablehd.= "<th style=\"width:10em\">$l&nbsp;</th>\n";
		$l = _("Opp. account");
		$curtablehd.= "<th style=\"width:10em\">$l&nbsp;</th>\n";
		$l = _("Debit");
		$curtablehd.= "<th style=\"width:5em\">$l&nbsp;</th>\n";
		$l = _("Credit");
		$curtablehd.= "<th style=\"width:5em\">$l&nbsp;</th>\n";
		$l = _("Acct. balance");
		$curtablehd.= "<th style=\"width:5em\">$l&nbsp;</th>\n";
		$l = _("Operations");
		$curtablehd.= "<th>$l</th>\n";
		$curtablehd.= "</tr></thead>\n";
		$curtablebody='<tbody>';
	}
/*	$openonly = isset($_GET['openonly']) ? $_GET['openonly'] : 0;  

	if($openonly)
		print "<H2 align=center dir=RTL>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ»׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ»׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳³ן¿½ײ²ֲ¿ײ²ֲ½</H2>\n<BR>\n";
*/

	if(($accttype != INCOME) && ($accttype != OUTCOME))
		$sub_total = round(GetOppBalance($acct, $begindate), 0);
	$debit_total = 0.0;
	$credit_total = 0.0;
	if($sub_total != 0.0) {
		$curtablebody.= "<tr>\n";
		if($filerep) {
			$l = _("Openning balance");
			$l = iconv("UTF-8", "windows-1255", $l );
			fwrite($fd, "\"$l\",");
		}
		else {
			$l = _("Openning balance");
			$curtablebody.= "<td colspan=\"6\">$l</td>\n";
		}
		if($sub_total < 0.0) {
			$t = $sub_total * -1.0;
			$debit_total += $t;
			if($filerep)
				fwrite($fd, iconv("UTF-8", "windows-1255", "$t, ,$sub_total\n" ));
			else {
				$tstr = number_format($t);
				$curtablebody.= "<td dir=\"ltr\">$tstr</td>\n";
				$curtablebody.= "<td>&nbsp;</td>\n";
				$tstr = number_format($sub_total);
				$curtablebody.= "<td dir=\"ltr\">$tstr</td>\n";
				$curtablebody.= "<td>&nbsp;</td>\n";
			}
		}
		else {
			$credit_total += $sub_total;
			if($filerep)
				fwrite($fd, iconv("UTF-8", "windows-1255", " ,$sub_total,$sub_total\n"));
			else {
				$curtablebody.= "<td>&nbsp;</td>\n";
				$tstr = number_format($sub_total);
				$curtablebody.= "<td dir=\"ltr\">$tstr</td>\n";
				$curtablebody.= "<td dir=\"ltr\">$tstr</td>\n";
				$curtablebody.= "<td>&nbsp;</td>\n";
			}
		}
		$e = 1;
	}
	else
		$e = 0;
	$query = "SELECT * FROM $transactionstbl WHERE account='$acct' ";
	if($begin != '')
		$query .= "AND date>='$begindate' ";
	$query .= "AND date<='$enddate' AND prefix='$prefix' ORDER BY date,num ";
	$result = DoQuery($query, "acctdisp.php");
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	/*	$cor = $line['cor_num'];
		if($openonly && $cor)
			continue; */
		$num = $line['num'];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$type = $line['type'];
		$type_str = stripslashes($TranType[$type]);
		$refnum1 = substr($line['refnum1'], -6);
		$details = $line['details'];
		$sum = $line['sum'];
		$opp_account = GetOppositAccount($num, $sum);
		$acc_name = stripslashes(GetAccountName($opp_account));
		$sub_total += $sum;
		if($filerep) {
			fwrite($fd, iconv("UTF-8", "windows-1255", "$num,$type_str,$date,$refnum1,$details,$acc_name,"));
			if($sum < 0) {
				$sum = $sum * -1.0;
				fwrite($fd, iconv("UTF-8", "windows-1255", "$sum, "));
				$debit_total += $sum;
			}
			else {
				$credit_total += $sum;
				fwrite($fd, iconv("UTF-8", "windows-1255", " ,$sum,"));
			}
			fwrite($fd, iconv("UTF-8", "windows-1255", "$sub_total\n"));
		}
		else {
			//if($e) {
			//	$curtablebody.= "<tr class=\"otherline\">\n";
			//	$e = 0;
			//}
			//else {
				$curtablebody.= "<tr>\n";
			//	$e = 1;
			//}
			$curtablebody.= "<td>$num</td>\n";
			global $TransType;
			$flip=array_flip($TransType);
			if (isset($flip[$type])){
					$dt = $flip[$type];
					$url = "?module=showdocs&amp;step=2&amp;doctype=$dt&amp;docnum=$refnum1&amp;prefix=$prefix";
					$curtablebody.= "<td><a href=\"$url\">$type_str</a></td>\n";
			}else{
				$curtablebody.= "<td>$type_str</td>\n";
			}
			
			$curtablebody.= "<td>$date</td>\n";
			$curtablebody.= "<td>$refnum1</td>\n";
			$curtablebody.= "<td>$details</td>\n";
			if(isset($module)) {
				$url = "?module=acctdisp&amp;account=$opp_account&amp;begin=$bedin&amp;end=$end";
				$curtablebody.= "<td><a href=\"$url\">$acc_name</a></td>\n";
			}
			else
				$curtablebody.= "<td>$acc_name</td>\n";
			if($sum < 0) {
				$sum = $sum * -1.0;
				$debit_total += $sum;
				$tstr = number_format($sum);
				$curtablebody.= "<td>$tstr</td><td>&nbsp;</td>\n";
			}
			else {
				$credit_total += $sum;
				$tstr = number_format($sum);
				$curtablebody.= "<td>&nbsp;</td><td>$tstr</td>\n";
			}
			$tstr = number_format($sub_total);
			$curtablebody.= "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
			if(isset($module)) {
				$l = _("Storeno");
				$curtablebody.= "<td><a href=\"?module=tedit&amp;num=$num&amp;account=$acct&amp;begin=$begin&amp;end=$end\">$l</a></td>\n";
			}//else$curtablebody.= "<td></td>";
			$curtablebody.= "</tr>\n";
		}	
	}
	if($filerep) {
		fclose($fd);
		$l = _("Click here to download report");
		$text.= "<h2>$l: ";
		$url = "download.php?file=$filename&amp;name=acct$acct.csv";
		$text.= "<a href=\"$filename\">acct$acct.csv</a></h2>\n";
		$l = _("Right click and choose 'save as...'");
		//$text.= "<h2>$l</h2>\n";
		//$text.= "<script type=\"text/javascript\">\n";
		//$text.= "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		//$text.= "</script>\n";
	}
	else {
		if(!isset($module))
			$curtablefoot = "<tfoot><tr class=\"sumlineprt\">\n";
		else
			$curtablefoot ="<tfoot><tr class=\"sumline\">\n";
		$l = _("Total");
		$curtablefoot.= "<td colspan=\"6\" align=\"left\">$l: &nbsp;</td>\n";
		$tstr = number_format($debit_total);
		$curtablefoot.= "<td>$tstr</td>";
		$tstr = number_format($credit_total);
		$curtablefoot.= "<td>$tstr</td>";
		$tstr = number_format($sub_total);
		$curtablefoot.= "<td dir=\"ltr\" align=\"right\">$tstr</td>";
		$curtablefoot.= "<td></td></tr></tfoot>\n";
		$curtablebody.="</tbody>";
		$text.= "<table class=\"tablesorter\" id=\"acctbl\">$curtablehd $curtablefoot $curtablebody</table>\n";
//<script type=\"text/javascript\">\$(\"#acctbl\").tablesorter(); </script>";
 		//printform
		
//	print "</div>\n";
	 /*	if(isset($module)) {
			$url = "acctdisp.php?account=$acct&begin=$begin&end=$end&prefix=$prefix";
			//$url .= "";
			$text.= "<div class=\"repbottom\">\n";
			$l = _("Export");
			//print "<a href=\"javascript:PrintWin('$url');\" class='btn'>$l</a>";
			//print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
			$text.= "&nbsp;&nbsp;";
			//print "<input type=\"button\" value=\"׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¦׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¿׳²ֲ²ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ§׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ»׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¥\" onclick=\"window.location.href='?module=acctdisp&account=$acct&begin=$begin&end=$end&file=1'\">\n";
			$text.= "<a href=\"?module=acctdisp&account=$acct&begin=$begin&end=$end&file=1\" class='btnsmall'>$l</a>";
			$text.= "</div>\n";
		}*/
	}
	//return;
}

$l = _("Display transactions for account");
//$text.= "<br /><h1>$l: $company</h1>\n<br />\n";
$d = date("m-Y");
list($m, $y) = explode('-', $d);
$begindate = "1-1-$y";
$enddate = date("d-m-Y");
$text.= "<form method=\"post\" name=\"accdisplay\">\n";
$text.= "	<input type=\"hidden\" name=\"module\" value=\"acctdisp\" />\n";
$text.= "	<input type=\"hidden\" name=\"account\" value=\"$acct\" />\n";
$text.= "	<table dir=\"rtl\"><tr>\n";
$l = _("From date: ");
$text.= "		<td>$l: </td>\n";
$text.= "		<td><input class=\"date\" type=\"text\" id=\"begin\" name=\"begin\" value=\"$begindate\" /></td>\n";
$l = _("To date");
$text.= "		<td>$l: </td>\n";
$text.= "		<td><input class=\"date\" type=\"text\" id=\"end\" name=\"end\" value=\"$enddate\" /></td>\n";
$text.= "	</tr><tr>\n";
$l = _("Display");
$l1=_("Export");
$text.= "		<td colspan=\"4\" align=\"center\">
<a href=\"javascript:document.accdisplay.submit();\" class='btnsmall'>$l</a>
<a href=\"?module=acctdisp&account=$acct&begin=$begin&end=$end&file=1\" class='btnsmall'>$l1</a>

</td></tr>\n";
$text.= "	</table>\n</form>\n";
global $ismobile;
if($ismobile)
	print $text;
else
	createForm($text, $haeder,'',750,'','img/icon_acctdisp.png',1,getHelp());

?>

