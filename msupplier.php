<?PHP
/*
 | monthly Supplier report for Freelance accounting system
 | Written by Ori Idan August 2009
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = "הוצאות לפי ספק וחודש";
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

$montharr = array('ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני', 'יולי', 'אוגוסט',
	'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר');
	
if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}

$filerep = isset($_GET['file']) ? $_GET['file'] : 0;

function GetLastDayOfMonth($month, $year) {
	$last = 31;
	
	if($month == 0)
		return $last;
	while(!checkdate($month, $last, $year)) {
	//	print "$last-$month-$year<br>\n";
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

function GetSupplierOutcome($acct, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$t = $line[0];
		if($t > 0)
			$total += $t;
	}
	return $total;
}

$y = date("Y");
if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
}
print "<br><h1>הוצאות לפי ספק וחודש</h1>\n";
if($filerep) {
	$filename = "tmp/mcustomer.cvs";
	$fd = fopen($filename, "w");
	fwrite($fd, "\"ספק\"");
	foreach($montharr as $m)
		fwrite($fd, ",\"$m\"");
	fwrite($fd, ",סהכ\n");
}
else {
	print "<table border=\"0\" style=\"margin-right:8px\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
	print "<td style=\"width:8em\">ספק&nbsp;</td>\n";
	foreach($montharr as $m)
		print "<td style=\"width:4em\">$m</td>\n";
	print "<td>סה\"כ</td>\n";
	print "</tr><tr class=\"tblhead\">\n";
	print "<td colspan=\"14\">הוצאות</td>\n";
	print "</tr>\n";
}
$t = SUPPLIER;
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
		$t = round(GetSupplierOutcome($num, $bdate, $edate), 0);
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
			print "<td><a href=\"?module=acctdisp&account=$num&begin=start&end=today\">$acct</a></td>\n";
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
	fwrite($fd, "\"סהכ\"");
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr[$i];
		fwrite($fd, ",$t");
	}
	fwrite($fd, "\n");	
}
else {
	if(!isset($module))
		print "<tr class=\"sumlineprt\" align=\"right\">\n";
	else
		print "<tr class=\"sumline\" align=\"right\">\n";
	print "<td><b>סה\"כ: </b></td>\n";
	for($i = 0; $i < 13; $i++) {
		$t = $sumarr[$i];
		$tstr = number_format($t);
		print "<td>$tstr</td>\n";
	}
	print "</tr>\n";
	print "</table>\n";
}

if(isset($module) && !$filerep) {
	$url = "msupplier.php";
	$url .= "?prefix=$prefix";
	print "<div class=\"repbottom\">\n";
	print "<input type=\"button\" value=\"הדפס\" onclick=\"PrintWin('$url')\">\n";
	print "&nbsp;&nbsp;";
	print "<input type=\"button\" value=\"יצוא לקובץ\" onclick=\"window.location.href='?module=msupplier&file=1'\">\n";
	print "</div>\n";
}
if($filerep) {
	fclose($fd);
	Conv1255($filename);
	print "<h2>להורדת הדוח לחץ כאן: ";
	$url = "/download.php?file=$filename&name=profloss.csv";
	print "<a href=\"$filename\">msupplier.csv</a></h2>\n";
	print "<h2>לחץ על שם הקובץ עם כפתור ימני ובחר \"שמור בשם\"</h2>\n";
	print "<script type=\"text/javascript\">\n";
	print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
	print "</script>\n";
}

?>

