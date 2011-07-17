<?php
/*Written By Adam BH pcn874 for linet*/
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $chequestbl, $receiptstbl, $creditcompanies, $docstbl, $itemstbl;
global $bkrecnum, $regnum, $mainid, $softregnum, $softwarename, $Version, $softwaremakerregnum, $softwaremaker;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 0) {	/* First stage, choose dates for report */

	$begindate = date('d-m-Y',mktime(0, 0, 0, date('m')-1, 1, date('Y')));//"1-1-$y";
	$enddate = date('d-m-Y',mktime(0, 0, 0, (date('m')), 0, date('Y'))); //date("31-12-$y");

	//print "<div class=\"form righthalf1\">\n";
	$header = _("Export pcn874 files for tax authorities"); 
	//print "<h3>$l</h3>\n";
	$text.= "<form name=\"dtrange\" action=\"?module=pcn874&amp;step=1\" method=\"post\">\n";
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("From date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$begindate\" size=\"7\">\n";
$text.='
<script type="text/javascript">
	addDatePicker("#begindate","<?print "$begindate"; ?>");
</script>';

	$text.= "</td>\n";
	$text.= "</tr><tr><td colspan=\"2\">&nbsp;</td></tr><tr>\n";
	$l = _("To date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$enddate\" size=\"7\">\n";
$text.='
<script type="text/javascript">
	addDatePicker("#enddate","<?print "$enddate"; ?>");
</script>';

/*help included:*/
	$text.= "</td>\n";
	$text.= "</tr><tr><td colspan=\"2\">&nbsp;</td></tr><tr>\n";
	$l = _("Submit");
	$text.= "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
	$text.= "</tr>\n";
	$text.= "</table>\n</form>\n";
	//print "</div>\n";
	createForm($text,$header,'',350);
	print "<div class=\"lefthalf1\">\n";
	ShowText('pcn874');
	print "</div>\n";
}
else if($step == 1) {
	$b = $_POST['begindate'];
	$e = $_POST['enddate'];
	print $b;
	$begindate = strftime('%D-%m-%y',strtotime($b));
	$enddate = strftime('%D-%m-%y',strtotime($e));
		$rdate=strftime('%Y%m',strtotime($b));
print "<br />(".$rdate.")<br />";

	/*write file haeder*/
	$hp=123456789;
	$n=1;
	$line='o'.$hp.$rdate.$n.date('Y-m-d');
	print $line;
		print "<div class=\"form righthalf1\">\n";
	$l = _("Link to file");
	print "<br />$l: ";
	print "<a href=download.php?file=openfrmt/$basepath/tmp/pcn874.txt&name=pcn974.txt>pcn974.txt</a><br />\n";
	
	print "</div>";
	
}

?>