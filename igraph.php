<?PHP
/*
 | Income graph for freelnace accounting.
 | Written by Ori Idan, September 2009
 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;

global $label, $data;

$label = array();
$data = array();

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}

if(!isset($begindmy)) {
	$begindmy = isset($_GET['begin']) ? $_GET['begin'] : date("1-1-Y");
	$enddmy = isset($_GET['end']) ? $_GET['end'] : date("d-m-Y");
}

if(!isset($type))
	$type = INCOME;
if($type == INCOME)
	$fname = "income.png";
else if($type == CUSTOMER)
	$fname = "customers.png";
else if($type == OUTCOME)
	$fname = "outcome.png";
else if($type == SUPPLIER)
	$fname = "suppliers.png";

if(!function_exists('GetAcctTotal')) {
	function GetAcctTotal($acct, $begin, $end) {
		global $transactionstbl, $prefix;
		
		if($begin != 0)
			$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
		else 
			$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date<='$end' AND prefix='$prefix'";
	//	print "query: $query<br>\n";
		$result = DoQuery($query, "igraph.php");
		$total = 0.0;
		while($line = mysql_fetch_array($result, MYSQL_NUM)) {
			$total += $line[0];
		}
		return $total;
	}
}

if(!function_exists('GetGroupData')) {
	function GetGroupData($grp, $begin, $end) {
		global $accountstbl, $prefix;
		global $label, $data;
		
		$query = "SELECT num,company FROM $accountstbl WHERE prefix='$prefix' AND type='$grp'";
		$result = DoQuery($query, "compass.php");
		$total = 0.0;
		$i = 0;
		while($line = mysql_fetch_array($result, MYSQL_NUM)) {
			$num = $line[0];
			$acct = $line[1];
	//		print "Get total for: $num, ";
			$sub_total = GetAcctTotal($num, $begin, $end);
			if($sub_total) {
				if($sub_total < 0)
					$sub_total *= -1.0;
				$label[$i] = html_entity_decode($acct, ENT_QUOTES, "utf-8");
				$data[$i] = $sub_total;
				$i++;
			}
	//		print "$sub_total<br>\n";
			$total += $sub_total;
		}
		return $total;
	}
}

$begin = FormatDate($begindmy, "dmy", "mysql");
$end = FormatDate($enddmy, "dmy", "mysql");

GetGroupData($type, $begin, $end);

// print_r($data);

// print "Creating graph...<br>\n";
include('chart.php');

// print "<img src=\"tmp/$fname\">\n";

?>
