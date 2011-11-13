<?PHP
/*
 | Monthly profit and loss graph
 | Written for Freelance accounting system by Ori Idan August 2009
 */

global $accountstbl, $prefix;

$mname = array("ינואר", "פבר'", "מרץ", "אפר'", "מאי", "יוני", "יולי", "אוג'", "ספט'", "אוקט'", "נוב'", "דצמבר");

if(!function_exists(GetLastDayOfMonth)) {
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
}

if(!function_exists(GetAcctType)) {
	function GetAcctType($acct) {
		global $prefix, $accountstbl;

		$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
		$result = DoQuery($query, "GetAcctType");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
}

if(!isset($begindmy)) {
	$y = date("Y");
	$m = 1;
}
else
	list($d, $m, $y) = explode('-', $begindmy);
if(isset($enddmy))
	list($d, $lm, $ly) = explode('-', $enddmy);

$sm = $m;
$sy = $y;

$grp = INCOME;
$data1 = array();
$m = $sm;
$y = $sy;
for($i = 0; $i < 12; $i++) {
	$bdate = "$y-$m-1";
	$l = GetLastDayOfMonth($m, $y);
	$edate = "$y-$m-$l";
	$t = round(GetGroupTotal($grp, $bdate, $edate), 0);
	$data1[$i] = $t;
	$label[$i] = $mname[$m-1];
//	print "$bdate $edate $t<br>\n";
	$m++;
	if($m > 12) {
		$m = 1;
		$y++;
	}
	if(($m == $lm) && ($y == $ly))
		break;
}

// print_r($data1);

$grp = OUTCOME;
$data2 = array();
$m = $sm;
$y = $sy;
for($i = 0; $i < 12; $i++) {
	$bdate = "$y-$m-1";
	$l = GetLastDayOfMonth($m, $y);
	$edate = "$y-$m-$l";
	$t = round(GetGroupTotal($grp, $bdate, $edate), 0);
	if($t < 0)
		$t *= -1.0;
	$data2[$i] = $t;
	$m++;
	if($m > 12) {
		$m = 1;
		$y++;
	}
	if(($m == $lm) && ($y == $ly))
		break;
}

$max = 0;
/* Find max for autoscale calculation */
for($i = 0; $i < count($data1); $i++) {
	if($data1[$i] > $max)
		$max = $data1[$i];
	if($data2[$i] > $max)
		$max = $data2[$i];
}
if($max > 10000) {
	for($i = 0; $i < count($data1); $i++) {
		$data1[$i] /= 1000;
		$data2[$i] /= 1000;
	}
}

// print_r($label);
$fname = "mgraph.png";
require('dbarchart.php');

?>
