<?PHP
/*
 | Opening balance module for Drorit
 | 
 */
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

global $accountstbl, $transactionstbl;
global $TranType;
//global $dir;

function PrintYearSelect() {
	
	$year = date("Y");
	$max = $year + 1;
	
	$str= "<select name=\"year\">\n";
	for($min = $year - 2; $min <= $max; $min++) {
		$str.= "<option value=\"$min\" ";
		if($min == $year)
			$str.= "selected";
		$str.= ">$min</option>\n";
	}
	$str.= "</select>\n";
	return $str;
}

function PrintAccountSelect() {
	global $accountstbl, $prefix;
	//adam!:
	$types = array(CUSTOMER, SUPPLIER, BANKS, AUTHORITIES, OBLIGATIONS,
			CAPITAL, CASH, FINANCING, ASSETS);

	
	$str= "<select class=\"account\" name=\"account[]\">\n";
	$l = _("Select account");
	$str.= "<option value=\"0\">-- $l --</option>\n";
	foreach($types as $type) {
		$query = "SELECT num,company FROM $accountstbl WHERE type='$type' AND prefix='$prefix' ORDER BY company ASC";
		$result = DoQuery($query, __LINE__);
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$num = $line['num'];
			$name = stripslashes($line['company']);
			$str.= "<option value=\"$num\">$name</option>\n";
		}
	}
	$str.= "</select>\n";
	return $str;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;


//print "<br><div class=\"form righthalf1\">\n";
$t = _("Openning balances");
//print "<h3>$t</h3>\n";
$header=$t;
$text='';
if($step == 1) {
	$year = $_POST['year'];
	$date = "1-1-$year";
	$acctarr = $_POST['account'];
	$balarr = $_POST['bal'];
	foreach($acctarr as $i => $acct) {
		$sum = $balarr[$i];
		if($acct) {
	//		print "$acct, $sum<br>\n";
			$tnum = Transaction(0, OPBALANCE, $acct, '', '', $date, '', $sum);
			$sum *= -1.0;
			Transaction($tnum, OPBALANCE, OPENBALANCE, '', '', $date, '', $sum);
		}
	}
	$l = _("Openning balances updated");
	$text.= "<h2>$l</h2>\n";
}

$text.= "<form action=\"?module=opbalance&amp;step=1\" method=\"post\">\n";
$text.= "<table class=\"formtbl\" width=\"100%\"><tr>\n";
$text.= "<td colspan=\"2\">\n";
$l = _("Select year");
$text.= "$l: \n";
$text.=PrintYearSelect();
$text.= "</td></tr>\n";
$text.= "<tr class=\"tblhead\">\n";
$l = _("Account");
$text.= "<td>$l</td>\n";
$l = _("Acct. balance");
$text.= "<td>$l</td>\n";
$text.= "</tr>\n";
for($i = 0; $i < 10; $i++) {
	$text.= "<tr>\n";
	$text.= "<td>\n";
	$text.=PrintAccountSelect();
	$text.= "</td><td>\n";
	$text.= "<input type=\"text\" class=\"bal\" name=\"bal[]\" dir=\"ltr\" />\n";
	$text.= "</td>\n";
	$text.= "</tr>\n";
}
$l = _("Update");
$text.= "<tr><td colspan=\"2\" align=\"center\">\n";
$text.= "<input type=\"submit\" value=\"$l\"  class='btnaction' /></td></tr>\n";
$text.= "</table>\n";
$text.= "</form>\n";
createForm($text,$header,'',750,'','img/icon_opbalance.png',1,getHelp());
//print "</div>\n";
?>
