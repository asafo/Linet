<?PHP
/*
 | Balance report for Drorit accounting system
 | Written by Ori Idan July 2009
 */

	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
//	print "\talert(url);\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;
global $AcctType;

if(!isset($prefix) || ($prefix == '')) {
	ErrorReport(_("This operation can not be executed without choosing a business first"));
	//print "<h1>$l</h1>\n";
	return;
}


function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

/*
 | GetTotals:
 | Return associative array of 'credit', 'debit' and 'sum'
 */
function GetTotals($num, $begindate, $enddate) {
	global $transactionstbl;
	global $prefix;

	$query = "SELECT sum,num FROM $transactionstbl WHERE account='$num' AND date>='$begindate' AND date<='$enddate'";
	$query .= " AND prefix='$prefix'";
	$result = DoQuery($query, __LINE__);
	$debit = 0.0;
	$credit = 0.0;
	$sum = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$s = (double)$line['sum'];
		$n = $line['num'];
		if(($s < 0.01) && ($s > -0.01))
			$s = 0.0;
		$sum += $s;
		if($s > 0)
			$credit += $s;
		else {
			$s *= -1.0;
			$debit += $s;
		}
	}

	$totals['debit'] = (double)$debit;
	$totals['credit'] = (double)$credit;
	if(($sum < 0.01) && ($sum > -0.01))
		$sum = 0.0;
	$totals['sum'] = (double)$sum;
	return $totals;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;
$text='';
if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
}
//if($step != 0) {
	$header = _("Balance report");
	//print "<br /><h1>$l</h1>\n";
	
//}
if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	//print "<br>\n";
	//print "<div class=\"form righthalf1\">\n";
	$l = _("Balance report");
	//print "<h3>$l</h3>\n";
	$text.= "<form name=\"dtrange\" action=\"\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"balance\">\n";
	$text.= "<input type=\"hidden\" name=\"step\" value=\"1\">\n";
	$text.= "<table cellpadding=\"5px\" cellspacing=\"5px\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("From date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input class=\"date\" type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$bdate\" size=\"7\">\n";

	$text.= "</td>\n";
	$l = _("To date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input class=\"date\" type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$edate\" size=\"7\">\n";

	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Execute");
	$text.= "<td colspan=\"4\" align=\"center\"><input class=\"btnaction\" type=\"submit\" value=\"$l\"></td>\n";
	$text.= "</tr></table>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	
}
if($step == 2) {
	$filename = "tmp/balance.csv";
	$fd = fopen($filename, "w");
}
if($step >= 1) {
	$percent = isset($_GET['percent']) ? $_GET['percent'] : 0;
	$d6111 = isset($_GET['d6111']) ? $_GET['d6111'] : 0;
	$begindate = $_GET['begindate'];
	$enddate = $_GET['enddate'];
	$l = _("For period");
	$text.= "<h2>$l: $begindate - $enddate</h2>\n";

	$bdate = FormatDate($begindate, "dmy", "mysql");
	$edate = FormatDate($enddate, "dmy", "mysql");
	if($step == 1) {
		if(!isset($module)) {
			$text.= "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">\n";
			$text.= "<table border=\"0\" cellpadding=\"3px\" class=\"printtbl\" align=\"center\">\n";
		}
		else
			$text.= "<table id=\"balancetbl\" border=\"0\" style=\"margin-right:2%\" cellpadding=\"3px\" class=\"tablesorter\">\n";
		if(!isset($module))
			$text.= "<tr class=\"tblheadprt\" style=\"border-top:1px solid\">\n";
		else
			$curtablehd= "<thead><tr class=\"tblhead\" style=\"border-top:1px solid;border-bottom:1px solid\">\n";
			$l = _("Account");
			$curtablehd.= "<th style=\"width:15em\">$l</th>\n";
			$l = _("6111 clause");
			$curtablehd.= "<th style=\"width:8em\">$l</th>\n";
			$l = _("Debit");
			$curtablehd.= "<th style=\"width:7em\">$l</th>\n";
			$l = _("Credit");
			$curtablehd.= "<th style=\"width:7em\">$l</th>\n";
			$l = _("Acc. balance");
			$curtablehd.= "<th style=\"width:7em\">$l</th></tr></thead>\n";
	}
	else {
		$l1 = _("Account");
		$l2 = _("6111 clause");
		$l3 = _("Debit");
		$l4 = _("Credit");
		$l5 = _("Acc. balance");
		fwrite($fd, "$l1,$l2,$l3,$l4,$l5");
//		fwrite($fd, "׳³ֲ¡׳³ֲ¢׳³ג„¢׳³ֲ£,׳³ֲ¡׳³ֲ¢׳³ג„¢׳³ֲ£ 6111,׳³ג€”׳³ג€¢׳³ג€˜׳³ג€�,׳³ג€“׳³ג€÷׳³ג€¢׳³ֳ—,׳³ֲ¡׳³ג€÷׳³ג€¢׳³ן¿½");
		fwrite($fd, "\n");
	}
	$totaldb = 0;
	$totalcrd = 0;
	$total = 0;
	for($type = 0; $type <= 7; $type++) {
		$tstr = $AcctType[$type];
		if($step == 1) {
			if(!isset($module))
				$text.= "<tr class=\"tblheadprt\" style=\"border-top:1px solid\">\n";
			else
				$text.= "<tr class=\"tblhead\" style=\"border-top:1px solid;border-bottom:1px solid\">\n";
			$text.= "<td colspan=\"5\" align=\"right\">$tstr</td></tr>\n<tr><td colspan=\"5\">";
			//$curtable="<table id=\"tbl$tstr\"><thead>$tblhd</thead><tfoot>";
			$tables.="$(\"#tbl$tstr\").tablesorter();"; //"tbl$module";
			$curtablebody="<tbody>";
		}
		else 
			fwrite($fd, "$tstr\n");

		$query = "SELECT num,company,id6111 FROM $accountstbl WHERE prefix='$prefix' AND type='$type'";
		$result = DoQuery($query, "balance.php");
		$tp = 0;
		$e = 0;
		$db = 0;
		$crd = 0;
		$sum = 0;
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$num = $line['num'];
			$acct = $line['company'];
			$id6111 = $line['id6111'];
			$tarr = GetTotals($num, $bdate, $edate);
		//	print "$num $acct";
		//	print_r($tarr);
		//	print "<br>\n"; 
			if(($tarr['debit'] == 0) && ($tarr['credit'] == 0))
				continue;
			if($step == 1) {
				//NewRow();
				$curtablebody.= "<tr>";
				$url = "?module=acctdisp&amp;account=$num&amp;begin=$begindate&amp;end=$enddate";
				if(isset($module))
					$curtablebody.= "<td><a href=\"$url\">$acct</a></td>\n";
				else
					$curtablebody.= "<td>$acct</td>\n";
				$curtablebody.= "<td>$id6111</td>\n";
				$t = $tarr['debit'];
				$db += $t;
				$ts = number_format($t);
				$curtablebody.= "<td dir=\"ltr\">$ts</td>\n";
				$t = $tarr['credit'];
				$crd +=$t;
				$ts = number_format($t);
				$curtablebody.= "<td dir=\"ltr\">$ts</td>\n";
				$t = $tarr['sum'];
				$sum +=$t;
				$ts = number_format($t);
				$curtablebody.= "<td dir=\"ltr\">$ts</td>\n";
				$curtablebody.= "</tr>\n";
			}
			else {
				$d = $tarr['debit'];
				$c = $tarr['credit'];
				$t = $tarr['sum'];
				fwrite($fd, "$acct,$id6111,$d,$c,$t\n");
			}
		}
		$curtablebody.= "</tbody>";
		$curtablefoot="<tfoot>";
		$curtablefoot.= "<tr class=\"sumline\">\n";
		$l = _("Total");
		$curtablefoot.= "<td colspan=\"2\">$l $tstr:</td>\n";
		$curtablefoot.= "<td>$db</td><td>$crd</td>\n";
		$curtablefoot.= "<td dir=\"ltr\">$sum</td></tr>\n";
		$totaldb += $db;
		$totalcrd += $crd;
		$total += $sum;
		$curtablefoot.= "</tfoot>";
		//print table
		$text.= "<table id=\"tbl$tstr\">$curtablehd $curtablefoot $curtablebody</table>";
		$text.= "</td></tr>\n";
	}
	$text.= "<tr class=\"sumline\">\n";
	$l = _("Total");
	$text.= "<td colspan=\"2\" align=\"left\"><b>$l: &nbsp;</b></td>\n";
	$text.= "<td>$totaldb</td><td>$totalcrd</td>\n";
	$tstr = number_format($total);
	$text.= "<td>$tstr</td>\n";
	$text.= "</tr>\n";
	$text.= "</table>\n";
	$text.= "	<script type=\"text/javascript\">\n	$(document).ready(function()  { $tables  } ); </script>	";
	if(isset($module) && ($step == 1)) {
		$url = "balance.php?print=1&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate";
		$url .= "&amp;prefix=$prefix";
		$text.= "<div class=\"repbottom\">\n";
		$l = _("Print");
		//print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\" />\n";
		//print "&nbsp;&nbsp;";
		$url = "?module=balance&amp;step=2&amp;begindate=$begindate&amp;enddate=$enddate";
		if($percent)
			$url .= "&amp;percent=on";
		$l = _("File export");
		$text.= "<a class='btnsmall' href='$url'\" >$l</a>\n";
		$text.= "</div>\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		$l = _("Click here to download");
		$text= "<h2>$l: ";
		$url = "download.php?file=$filename&amp;name=profloss.csv";
		$text.= "<a href=\"$filename\">balance.csv</a></h2>\n";
		$l = _("Right click and choose 'save as...'");
		$text.= "<h2>$l</h2>\n";
		//$text.= "<script type=\"text/javascript\">\n";
		//$text.= "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		//$text.= "</script>\n";
	}
}
createForm($text, $header,'',750,'','',1,getHelp());
?>
