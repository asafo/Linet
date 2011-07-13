<?PHP
/*
 | Configuration file for Drorit
 */
$host = 'localhost';
$user = 'root';
$pswd = 'passbla';

$database = 'linet';
$path = '/var/www/linet1.1';
$tblprefix = 'tmp';
$version = '1.0';
$updatesrv = 'http://localhost/server/';//https://update.linet.org.il
/*===================== No Need to change anything below this line =====================================*/

if(!isset($prefix) || ($prefix == '')) {
	if(isset($_COOKIE['company']))
		$prefix =  $_COOKIE['company'];
	else
		$prefix = '';
}
$articlestbl = "${tblprefix}articles";
$picstbl = "${tblprefix}pics";
$companiestbl = "${tblprefix}companies";
$histtbl = "${tblprefix}contacthist";
$logintbl = "${tblprefix}login";
$permissionstbl = "${tblprefix}premissions";
$currencytbl = "${tblprefix}currency";
$ratestbl = "${tblprefix}rates";
$accountstbl = "${tblprefix}accounts";
$contactstbl = "${tblprefix}contacts";
$docstbl = "${tblprefix}docs";
$docdetailstbl = "${tblprefix}docdetails";
$catalogtbl = "${tblprefix}catalog";
$transactionstbl = "${tblprefix}transactions";
$supdocstbl = "${tblprefix}supdocs";
$supdocdetailstbl = "${tblprefix}supdocdetails";
$receiptstbl = "${tblprefix}receipts";
$bankbooktbl = "${tblprefix}bankbook";
$chequestbl = "${tblprefix}cheques";
$tranpattbl = "${tblprefix}tranpatt";
$tranreptbl = "${tblprefix}tranrep";
$itemstbl = "${tblprefix}items";
$stattbl = "${tblprefix}stat";

?>
