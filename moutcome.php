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
	$reptitle = "׳”׳•׳¦׳�׳•׳× ׳�׳₪׳™ ׳¡׳₪׳§ ׳•׳—׳•׳“׳©";
	include('printhead.inc.php');
	print $header;
	
}
else {
	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
	print "\twindow.open(url, 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;

$montharr = array('׳™׳ ׳•׳�׳¨', '׳₪׳‘׳¨׳•׳�׳¨', '׳�׳¨׳¥', '׳�׳₪׳¨׳™׳�', '׳�׳�׳™', '׳™׳•׳ ׳™', '׳™׳•׳�׳™', '׳�׳•׳’׳•׳¡׳˜',
	'׳¡׳₪׳˜׳�׳‘׳¨', '׳�׳•׳§׳˜׳•׳‘׳¨', '׳ ׳•׳‘׳�׳‘׳¨', '׳“׳¦׳�׳‘׳¨');
	
if(!isset($prefix) || ($prefix == '')) {
	print "<h1>׳�׳� ׳ ׳™׳×׳� ׳�׳‘׳¦׳¢ ׳₪׳¢׳•׳�׳” ׳–׳• ׳�׳�׳� ׳‘׳—׳™׳¨׳× ׳¢׳¡׳§</h1>\n";
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

function GetAccountSum($account, $begin, $end) {
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

print "<br><h1>׳”׳•׳¦׳�׳•׳× ׳�׳₪׳™ ׳—׳•׳“׳©</h1>\n";
if($filerep) {
	$filename = "tmp/moutcome.cvs";
	$fd = fopen($filename, "w");
	foreach($montharr as $m)
		fwrite($fd, ",\"$m\"");
	fwrite($fd, ",׳¡׳”׳›\n");
}
else {
	print "<table border=\"0\" style=\"margin-right:8px\"><tr class=\"tblhead\">\n";
	print "<td style=\"width:8em\">׳¡׳¢׳™׳£ ׳”׳•׳¦׳�׳”&nbsp;</td>\n";
	foreach($montharr as $m)
		print "<td style=\"width:4em\">$m</td>\n";
	print "<td>׳¡׳”\"׳›</td>\n";
	print "</tr>\n";
}

$t = SUPPLIER;
$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
$result = DoQuery($query, "moutcome.php");
$tp = 0;
$e = 0;
$sumarr = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {}

?>