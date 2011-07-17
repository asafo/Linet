<?PHP
//M:רווח והפסד חדשי
/*
 | Monthly Profit & Loss report for Drorit accounting system
 | Written by Ori Idan July 2009
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('drorit.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = _("Profit & loss per month");
	include('printhead.inc.php');
	print $header;
	
}
else {
	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;

$montharr = array(_("January"), _("February"), _("March"), _("April"),
	_("May"), _("June"), _("July"), _("August"), _("September"), 
	_("October"), _("November"), _("December"));
	
// $montharr = array('ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני', 'יולי', 'אוגוסט',
//	'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר');

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

$filerep = isset($_GET['file']) ? $_GET['file'] : 0;

function GetLastDayOfMonth($month, $year) {
	$last = 31;
	
	if($month == 0)
		return $last;
	while(!checkdate($month, $last, $year)) {
		if($last < 28) {
			print "$last-$month-$year<br>\n";
			break;
		}
		$last--;
	}
	return $last;
}

function GetAcctType($acct) {
	global $prefix, $accountstbl;

	$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctType");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetAcctTotal($acct, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetGroupTotal($grp, $begin, $end) {
	global $accountstbl, $prefix;
	
	$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$grp'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
//		print "Get total for: $num, ";
		$sub_total = GetAcctTotal($num, $begin, $end);
//		print "$sub_total<br>\n";
		$total += $sub_total;
	}
	return $total;
}

function PrintYearSelect($year) {
	$max = $year + 1;
	
	print "<select name=\"year\" onChange=\"document.yform.submit()\">\n";
	for($min = $year - 2; $min <= $max; $min++) {
		print "<option value=\"$min\" ";
		if($min == $year)
			print "selected";
		print ">$min</option>\n";
	}
	print "</select>\n";
}

$y = date("Y");
if(isset($_COOKIE['begin'])) {
	$begindmy = $_COOKIE['begin'];
	list($d, $m, $y) = explode('-', $begindmy);
}
if(isset($_GET['year']))
	$y = $_GET['year'];
if($y < 1900)
	$y = date("Y");

/* if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
} */

$reptitle = _("Profit & loss per month");
print "<div class=\"form\"><h3>$reptitle</h3>\n";
print "<form name=\"yform\" action=\"\" method=\"get\">\n";
print "<input type=\"hidden\" name=\"module\" value=\"mprofloss\">\n";
$l = _("For year");
print "<b>$l: </b>";
PrintYearSelect($y);
// print "<input type=\"text\" name=\"year\" size=\"6\" value=\"$y\">\n";
// print "<input type=\"submit\" value=\"בצע\">\n";
print "</form><br>\n";

if($filerep) {
	$filename = "tmp/mprofloss.csv";
	$fd = fopen($filename, "w");
	$l = _("Account");
	fwrite($fd, "\"$l\"");
	foreach($montharr as $m)
		fwrite($fd, ",\"$m\"");
	$l = _("Total");
	fwrite($fd, "\"$l\n");
	$l = _("Income");
	fwrite($fd, "$l\n");
}
else {
	print "<table border=\"0\" style=\"margin-right:2%\" class=\"hovertbl\">\n";
	if(!isset($module))
		print "<tr class=\"tblheadprt\">\n";
	else
		print "<tr class=\"tblhead\">\n";
	$l = _("Account");
	print "<td style=\"width:8em\">$l&nbsp;</td>\n";
	foreach($montharr as $m)
		print "<td style=\"width:4em\">$m</td>\n";
	$l = _("Total");
	print "<td>$l</td>\n";
	print "</tr><tr class=\"tblhead\">\n";
	$l = _("Income");
	print "<td colspan=\"14\"><u>$l</u></td>\n";
	print "</tr>\n";
}
$t = INCOME;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "profloss.php");
$tp = 0;
$e = 0;
$sumarr = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acct = $line['company'];
	$sum = 0;
	for($i = 0; $i < 12; $i++) {
		$m = $i + 1;
		$bdate = "$y-$m-1";
		$l = GetLastDayOfMonth($m, $y);
		$edate = "$y-$m-$l";
		$t = round(GetAcctTotal($num, $bdate, $edate), 0);
		$total[$i] = $t;
		$sum += $t;
	}
	if($sum == 0)
		continue;
/*	print "$bdate $edate $acct<br>\n";
	print_r($t); */
	if($filerep) {
		fwrite($fd, "\"$acct\"");
		for($i = 0; $i < 12; $i++) {
			$t = $total[$i];
			$sumarr[$i] += $t;
			fwrite($fd, ",$t");
		}
		$sumarr[$i] += $sum;
		fwrite($fd, ",$sum\n");
	}
	else {
		if($e) {
			print "<tr class=\"otherline\">\n";
			$e = 0;
		}
		else {
			print "<tr>\n";
			$e = 1;
		}
		if(isset($module))
			print "<td><a href=\"?module=acctdisp&amp;account=$num&amp;begin=start&amp;end=today\">$acct</a></td>\n";
		else
			print "<td>$acct</td>\n";
		for($i = 0; $i < 12; $i++) {
			$t = $total[$i];
			$sumarr[$i] += $t;
			$tstr = number_format($t);
			print "<td>$tstr</td>\n";
		}
		$sumarr[$i] += $sum;
		$tstr = number_format($sum);
		print "<td>$tstr</td>\n";
		print "</tr>\n";
	}
}
if($filerep) {
	$l = _("Total");
	fwrite($fd, "$l");
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr[$i];
		fwrite($fd, ",$t");
	}
	fwrite($fd, "\n");
//	fwrite($fd, "עלות המכירות\n");
}
else {
	if(!isset($module))
		print "<tr class=\"sumlineprt\">\n";
	else
		print "<tr class=\"sumline\">\n";
	$l = _("Total");
	print "<td><b>$l: </b></td>\n";
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr[$i];
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
	}
	print "</tr>\n";
//	print "<tr class=\"tblhead\"><td colspan=\"14\"><u>עלות המכירות<u></td></tr>\n";
}
/*
for($i = 0; $i < 12; $i++) {
	$m = $i + 1;
	$bdate = "$y-$m-1";
	$l = GetLastDayOfMonth($m, $y);
	$edate = "$y-$m-$l";
	$open_stock[$i] = round(abs(GetAcctTotal(OPEN_STOCK, $bdate, $edate)), 0);
	$close_stock[$i] = round(abs(GetAcctTotal(CLOSE_STOCK, $bdate, $edate)), 0);
	$buy_stock[$i] = round(abs(GetAcctTotal(BUY_STOCK, $bdate, $edate)), 0);
	$sale_cost[$i] = $open_stock[$i] + $buy_stock[$i] - $close_stock[$i];
}
if($filerep) {
	fwrite($fd, "\"מלאי פתיחה\"");
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $open_stock[$i];
		fwrite($fd, ",$t");
		$sum += $t;
	}
	fwrite($fd, ",$sum\n");
}
else {
	if($e) {
		print "<tr class=\"otherline\">\n";
		$e = 0;
	}
	else {
		print "<tr>\n";
		$e = 1;
	}
	print "<td>מלאי פתיחה</td>\n";
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $open_stock[$i];
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
		$sum += $t;
	}
	$tstr = number_format($sum);
	print "<td>$tstr</td>\n";
	print "</tr>\n";
}
if($filerep) {
	fwrite($fd, "קניות");
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $buy_stock[$i];
		fwrite($fd, ",$t");
		$sum += $t;
	}
	fwrite($fd, ",$sum\n");
}
else {
	if($e) {
		print "<tr class=\"otherline\">\n";
		$e = 0;
	}
	else {
		print "<tr>\n";
		$e = 1;
	}
	print "<td>קניות</td>\n";
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $buy_stock[$i];
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
		$sum += $t;
	}
	$tstr = number_format($sum);
	print "<td>$tstr</td>\n";
	print "</tr>\n";
}
if($filerep) {
	fwrite($fd, "\"מלאי סופי\"");
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $close_stock[$i];
		fwrite($fd, ",$t");
		$sum += $t;
	}
	fwrite($fd, ",$sum\n");
}
else {
	if($e) {
		print "<tr class=\"otherline\">\n";
		$e = 0;
	}
	else {
		print "<tr>\n";
		$e = 1;
	}
	print "<td>מלאי סופי</td>\n";
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $close_stock[$i];
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
		$sum += $t;
	}
	$tstr = number_format($sum);
	print "<td>$sum</td>\n";
	print "</tr>\n";
}
if($filerep) {
	fwrite($fd, "\"סהכ עלות המכר\"");
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $sale_stock[$i];
		fwrite($fd, ",$t");
		$sum += $t;
	}
	fwrite($fd, ",$sum\n");
}
else {
	if($e) {
		print "<tr class=\"otherline\">\n";
		$e = 0;
	}
	else {
		print "<tr>\n";
		$e = 1;
	}
	print "<td>סה\"כ עלות המכר</td>\n";
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $sale_cost[$i];
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
		$sum += $t;
	}
	$tstr = number_format($sum);
	print "<td>$tstr</td>\n";
	print "</tr>\n";
}
if($filerep) {
	fwrite($fd, "\"רווח גולמי\"");
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $sale_cost[$i];
		$t = $sumarr[$i] - $t;
		$t = round($t, 0);
		fwrite($fd, ",$t");
		$sum += $t;
	}
	fwrite($fd, ",$sum\n");
}
else {
	if(!isset($module))
		print "<tr class=\"sumlineprt\" align=\"right\">\n";
	else
		print "<tr class=\"sumline\" align=\"right\">\n";

	print "<td>רווח גולמי</td>\n";
	$sum = 0.0;
	for($i = 0; $i < 12; $i++) {
		$t = $sale_cost[$i];
		$t = $sumarr[$i] - $t;
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
		$sum += $t;
	}
	$tstr = number_format($sum);
	print "<td>$tstr</td>\n";
	print "</tr>\n";
} */
if($filerep) {
	$l = _("Outcome");
	fwrite($fd, "\"$l\"\n");
}
else {
	print "<tr class=\"tblhead\">\n";
	$l = _("Outcome");
	print "<td colspan=\"14\"><u>$l</u></td>\n";
	print "</tr>\n";
}
$t = OUTCOME;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "profloss.php");
$tp = 0;
$e = 0;
$sumarr1 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$acct = $line['company'];
	$sum = 0;
	for($i = 0; $i < 12; $i++) {
		$m = $i + 1;
		$bdate = "$y-$m-1";
		$l = GetLastDayOfMonth($m, $y);
		$edate = "$y-$m-$l";
		$t = round(GetAcctTotal($num, $bdate, $edate), 0);
		$total[$i] = $t;
		$sum += $t;
	}
	if($sum == 0)
		continue;
/*	print "$acct $bdate - $edate<br>\n";
	print_r($total);
	print "<br>\n"; */

	if($filerep) {
		fwrite($fd, "\"$acct\"");
		for($i = 0; $i < 12; $i++) {
			$t = $total[$i];
			$sumarr1[$i] += $t;
			fwrite($fd, ",$t");
		}
		$sumarr1[$i] += $sum;
		fwrite($fd, ",$sum\n");
	}
	else {
		if($e) {
			print "<tr class=\"otherline\">\n";
			$e = 0;
		}
		else {
			print "<tr>\n";
			$e = 1;
		}
		if(isset($module))
			print "<td><a href=\"?module=acctdisp&amp;account=$num&amp;begin=start&amp;end=today\">$acct</a></td>\n";
		else
			print "<td>$acct</td>\n";
		for($i = 0; $i < 12; $i++) {
			$t = $total[$i];
			$sumarr1[$i] += $t;
			$tstr = number_format($t);
			print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
		}
		$sumarr1[$i] += $sum;
		$tstr = number_format($sum);
		print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
		print "</tr>\n";
	}
}
if($filerep) {
	$l = _("Total");
	fwrite($fd, "\"$l\"");
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr1[$i];
		fwrite($fd, ",$t");
	}
	fwrite($fd, "\n");
}
else {
	if(!isset($module))
		print "<tr class=\"sumlineprt\" align=\"right\">\n";
	else
		print "<tr class=\"sumline\" align=\"right\">\n";
	$l = _("Total");
	print "<td><b>$l: </b></td>\n";
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr1[$i];
		$tstr = number_format($t);
		print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
	}
	print "</tr>\n";
}
if($filerep) {
	$l = _("Profit & loss");
	fwrite($fd, "\"$l\"");
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr[$i] + $sumarr1[$i];
		fwrite($fd, ",$t");
	}
	fwrite($fd, "\n");
}
else {
	if(!isset($module))
		print "<tr class=\"sumlineprt\" align=\"right\">\n";
	else
		print "<tr class=\"sumline\" align=\"right\">\n";
	$l = _("Profit & loss");
	print "<td><b>$l: </b></td>\n";
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr[$i] + $sumarr1[$i];
		$tstr = number_format($t);
		print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
	}
	print "</tr>\n";
	print "</table>\n";
}

if(isset($module) && (!$filerep)) {
	$url = "mprofloss.php";
	$url .= "?prefix=$prefix";
	print "<div class=\"repbottom\">\n";
	$l = _("Print");
	print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
	print "&nbsp;&nbsp;";
	$l = _("File export");
	print "<input type=\"button\" value=\"$l\" onclick=\"window.location.href='?module=mprofloss&amp;file=1'\">\n";
	print "</div>\n";
}
if($filerep) {
	fclose($fd);
	Conv1255($filename);
	$l = _("File export");
	print "<h2>$l: ";
	$url = "/download.php?file=$filename&amp;name=profloss.csv";
	print "<a href=\"$filename\">mprofloss.csv</a></h2>\n";
	$l = _("Right click and choose 'save as...'");
	print "<h2>$l</h2>\n";
	print "<script type=\"text/javascript\">\n";
	print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
	print "</script>\n";
}
print "</div>";//adam: form div
?>
