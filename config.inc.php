<?PHP
/*
 | Configuration file for Drorit
 */
$host = 'localhost';
$user = 'root';
$pswd = 'passbla';

$database = 'linet';
$path = '/var/www/linet1.1';
//$tblprefix = 'tmp';
//$version = '1.0';
$updatesrv = 'http://localhost/server/';//https://update.linet.org.il
/*===================== No Need to change anything below this line =====================================*/

if(!isset($prefix) || ($prefix == '')) {
	if(isset($_COOKIE['company']))
		$prefix =  $_COOKIE['company'];
	else
		$prefix = '';
}
?>
