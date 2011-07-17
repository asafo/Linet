<?PHP
include('config.inc.php');
include('func.inc.php');
global $companiestbl;

$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_select_db($database) or die("Could not select database: $database");

$query = "SELECT * FROM $companiestbl WHERE prefix='$prefix'";
//echo $query;
$result = DoQuery($query, 'PrintHead');//


$line = mysql_fetch_array($result, MYSQL_ASSOC);
$company = $line['companyname'];
$address = $line['address'];
$city = $line['city'];
$zip = $line['zip'];

$header = <<<HD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<!--[if lt IE 8]>
			<script src="http://ie7-js.googlecode.com/svn/version/2.0(beta3)/IE8.js" type="text/javascript"></script>
			<![endif]-->
		<link rel="stylesheet" type="text/css" href="style/freelance.css" />
		<link rel="stylesheet" type="text/css" href="style/yawiki.css" />
		<style type="text/css">
			.top {text-align:center;}
			.contents {
				border:1px solid;
				width:90%;
				margin:5px;}
		</style>
		<title>$reptitle</title>
	</head>
	<body onload="window.print()" dir="rtl">
		<div style="text-align:right;">
			<h1>$company</h1>
			<!-- $address<br>
			$city $zip<br> -->
		</div>
HD;

?>
