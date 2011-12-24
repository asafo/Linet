<?PHP
//M:׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”׳³ג€¢׳³ֳ— ׳³ג€”׳³ג„¢׳³ג„¢׳³ג€˜׳³ג„¢׳³ן¿½
/*
 | Customers owing money report for Drorit accounting system
 | Written by Ori Idan 2009
 */
global $prefix, $accountstbl, $supdocstbl;
global $dir;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
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
$text='';
//print "<br>\n";
//print "<div class=\"form righthalf1\">\n";
$haeder = _("Customers owing money");
//print "<h3>$l</h3>";
$t = CUSTOMER;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "owe.php");
$text.= "<table class=\"tablesorter\"><thead><tr>\n";
$l = _("Customer");
$text.= "<th style=\"width:20em\">$l &nbsp;&nbsp;</th>\n";			/* customer account */
$l = _("Acc. balance");
$text.= "<th>$l</th>\n";
$text.= "</tr></thead>\n";

$e = 0;
$total = 0;
$dt = date("Y-m-d");
$body='<tbody>';
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acctname = $line['company'];
	$sum = GetAcctTotal($num, $dt);
	if($sum == 0.0)
		continue;
	$sum *= -1.0;
	$total += $sum;
	$url = "?module=acctdisp&amp;account=$num&amp;end=today";
	$body.= "<tr><td><a href=\"$url\">$acctname</a></td>\n";
	$tstr = number_format($sum);
	$body.= "<td dir=\"ltr\">$tstr</td>\n";
	$body.= "</tr>\n";	
}
$body.="</tbody>";
$tstr = number_format($total);
$text.= "<tfoot><tr class=\"sumline\"><td><b>"._("Total")."</b></td><td>$tstr</td></tr><tfoot>\n";
$text.=$body;
$text.= "</table>\n";
//print "</div>\n";
global $ismobile;
if($ismobile)
	print $text;
else
	createForm($text, $haeder,'',750,'','',1,getHelp());
?>
