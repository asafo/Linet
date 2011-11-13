<?PHP
/*
 | Bank transaction match display script for Drorit Free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	ErrorReport($l);
	return;
}
global $accountstbl, $transactionstbl, $bankbooktbl;
global $TranType;
global $dir;
$text='';
//print "<br>\n";
//print "<div class=\"form righthalf1\">\n";
$haeder = _("Display bank reconciliations");
//print "<h3>$l</h3>\n";

$bankacc = isset($_GET['bankacc']) ? $_GET['bankacc'] : 0;
$begindate = isset($_GET['begindate']) ? $_GET['begindate'] : date("1-1-Y");
$enddate = isset($_GET['enddate']) ? $_GET['enddate'] : date("d-m-Y");

if(!$bankacc) {
	/* Choose account */
	$text.= "<form name=\"choosebank\" action=\"\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"dispmatch\">\n";
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
			$text.= ">&nbsp;$acctname\n";
		}
	}
	$text.= "<br><br>\n";
	$l = _("From date");
	$text.= "$l: ";
	$begindate = date("1-1-Y");
	$text.= "<input class=\"date\" type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$begindate\" size=\"7\" />\n";
//$text.='<script type="text/javascript">	addDatePicker("#begindate","'.$begindate.'");</script>';
	$l = _("To date");
	$text.= "&nbsp;&nbsp;$l: ";
	$enddate = date("d-m-Y");
	$text.= "<input class=\"date\" type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$enddate\" size=\"7\" />\n";
//$text.='<script type="text/javascript">addDatePicker("#enddate","'.$enddate.'");</script>';
	$text.= "<br>\n";
	$l = _("Display");
	$text.= "<div style=\"text-align:center\"><br><a href=\"javascript:document.choosebank.submit();\" class=\"btnaction\">$l</a></div>\n";
	$text.= "</div>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	createForm($text,$haeder,'',750,'','',1,getHelp());
	
	return;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'unmatch') {
	$matches = $_POST['matches'];
	foreach($matches as $match) {
		list($int_str, $ext_str) = split('\|', $match);
		$int = split(',', $int_str);
		$ext = split(',', $ext_str);
		foreach($int as $val) {
			$query = "UPDATE $transactionstbl SET cor_num='0' WHERE num='$val' AND prefix='$prefix'";
			// print "$query<BR>\n";
			$result = mysql_query($query);
			if(!$result) { echo mysql_error(); exit; }
		}
		foreach($ext as $val) {
			$query = "UPDATE bankbook SET cor_num='0' WHERE num='$val' AND prefix='$prefix' AND account='$bankacc'";
			// print "$query<BR>\n";
			$result = mysql_query($query);
			if(!$result) { echo mysql_error(); exit; }
		}
	}
}

//print "</div>\n";	/* end of righthalf used for caption */
//print "<br><br><br>\n";
//print "<div class=\"innercontent\">\n";
$text.= "<form name=\"form1\" action=\"?module=dispmatch&amp;action=unmatch&amp;bankacc=$bankacc&amp;begindate=$begindate&amp;enddate=$enddate\" method=\"post\">\n";
$text.='<div style="border:1px solid"><table border="1" width="100%"><tr>';

$l = _("External page transactions");
$text.= "<td colspan=\"4\" align=\"right\"><h2>$l</h2></td>\n";
$text.= "<td>&nbsp;</td>\n";
$l = _("Internal bank account transactions");
$text.= "<td colspan=\"4\" align=\"right\"><h2>$l</h2></td>\n";
$text.='<td>&nbsp;</td></tr><tr><td colspan="4"><table class="formy" width="100%"><tr>';
$l = _("Details");
$text.= "<th align=\"right\" width=\"40%\">$l</th>\n";
$l = _("Sum");
$text.= "<th align=\"right\" width=\"20%\">$l</th>\n";
$l = _("Ref. num");
$text.= "<th align=\"right\" width=\"20%\">$l</th>\n";
$l = _("Date");
$text.= "<th align=\"right\" width=\"25%\">$l</th>\n";
$text.='</tr></table></td><td>&nbsp</td><td align="right" colspan="4"><table class="formy" width="100%"><tr>';

$l = _("Sum");
$text.= "<th  align=\"right\" width=\"25%\">$l</th>\n";
$l = _("Ref. num.");
$text.= "<th  align=\"right\" width=\"25%\">$l</th>\n";
$l = _("Date");
$text.= "<th  align=\"right\" width=\"25%\">$l</th>\n";
$l = _("Tran. type");
$text.= "<th  align=\"right\" width=\"25%\">$l</th>\n";
$text.='
</tr>
</table>
</td>
</tr>';

$match_str = '';	/* this string will contain both internal and external transactions numbers */
/* Display transactions from bank books */
$beginmysql = FormatDate($begindate, "dmy", "mysql");
$endmysql = FormatDate($enddate, "dmy", "mysql");
$query = "SELECT cor_num,sum FROM $bankbooktbl WHERE cor_num!='0' AND prefix='$prefix' AND account='$bankacc' AND date>='$beginmysql' AND date<='$endmysql'";
// print "Query: $query<br>\n";
$bankbook = DoQuery($query, __LINE__);
/* we now have a list of all matches from bank side */
/* Now we shall print for each match, the bank matches and internal bank card matches */
while($match = mysql_fetch_array($bankbook, MYSQL_NUM)) {
	$str = $match[0];
	$sum = $match[1];
	$match_str = $str;
	$matches = split(',', $str);
	$matches = array_unique($matches);
	/* now we know how many matches we will have on the other side */
	$query = "SELECT * FROM $bankbooktbl WHERE cor_num='$str' AND sum='$sum' AND prefix='$prefix' AND account='$bankacc'";
	$text.= "<tr>\n";
	$text.= "<td colspan=\"4\" valign=\"top\">\n";
	$text.= "<table border=\"1\" dir=\"ltr\" width=\"100%\">\n";
	$result = mysql_query($query);	/* now we get all bankbook transactions for this match */
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$refnum = $line['refnum'];
		$details = stripslashes($line['details']);
		$details = htmlspecialchars($details);
		$sum = $line['sum'];
		$text.= "<tr>\n";
		$text.= "<td align=\"right\" dir=\"$dir\" width=\"40%\">$details</td>\n";
		$text.= "<td dir=\"ltr\" width=\"20%\" align=\"right\">$sum</td>\n";
		$text.= "<td dir=\"ltr\" width=\"20%\" align=\"right\">$refnum</td>\n";
		$text.= "<td dir=\"ltr\" width=\"25%\">$date</td>\n";
		$text.= "</tr>\n";
	}
	$text.= "</table>\n";
	$text.= "</td>\n";
	$text.= "<td>&nbsp;</td>\n";
	$text.= "<td colspan=\"4\" valign=\"top\">\n";
	$text.= "<table border=\"1\" dir=\"ltr\" width=\"100%\">\n";
	/* now get matches for internal bank account */
	$sum = $sum * -1.0;
	foreach($matches as $m) {
		$query = "SELECT * FROM $transactionstbl WHERE account='$bankacc' AND num='$m' AND sum='$sum' AND prefix='$prefix'";
		$result = mysql_query($query);
		if(!$result) { print "Query: $query<BR>\n"; echo mysql_error(); exit; };
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$num = $line['num'];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$refnum = $line['refnum1'];
		$type = $line['type'];
		$sum1 = $line['sum'];
		$sum1 *= -1;
		list($d,$m,$y) = explode('-', $date);
		if(($d == 0) && ($sum1 == 0.0))
			continue;
		$cor_num = $line['cor_num'];
		$text.= "<tr>\n";
		$text.= "<td align=\"right\" width=\"25%\">$sum1</td>\n";
		$text.= "<td align=\"right\" width=\"25%\">$refnum</td>\n";
		$text.= "<td align=\"right\" width=\"25%\">$date</td>\n";
		$text.= "<td align=\"right\" width=\"25%\">$TranType[$type]</td>\n";
		$text.= "</tr>\n";
	}
	$match_str .= "|$cor_num";
	$text.= "</table>\n";
	$text.= "</td>\n";
	$text.= "<td valign=\"middle\"><input type=\"checkbox\" name=\"matches[]\" value=\"$match_str\"></td>\n";
	$text.= "</tr>\n";
}
$l = _("Open reconciliations");
$text.= "<tr><td colspan=\"10\" align=\"center\"><a href=\"javascript:document.form1.submit();\" class=\"btnaction\">$l</a></td></tr>\n";
$text.='</table></div></form></div>';
createForm($text,$haeder,'',750,'','',1,getHelp());

?>