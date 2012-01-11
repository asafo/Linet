<?php
$updatesrv ='https://update.linet.org.il';//http://localhost/server/';

$table["articles"]="articles";
$table["companies"] = "companies";
$table["contacthist"] = "contacthist";
$table["login"] = "login";
$table["premissions"] = "premissions";
$table["currency"] = "currency";
$table["rates"] = "rates";
$table["accounts"] = "accounts";
$table["contacts"] = "contacts";
$table["docs"] = "docs";
$table["docdetails"] = "docdetails";
$table["catalog"] = "catalog";
$table["transactions"] = "transactions";
$table["supdocs"] = "supdocs";
$table["supdocdetails"] = "supdocdetails";
$table["bankbook"] = "bankbook";
$table["cheques"] = "cheques";
$table["tranrep"] = "tranrep";
$table["stat"] = "items";
$table["articles"] = "stat";
$table["openformat"] ="openformat";
$table["openformattype"]="openformattype";
$table["creditErrorCode"]="creditErrorCode";
$table["correlation"]="correlation";
$withoutprefix=array(
'articles',
'creditErrorCode',
'currency',
'fields',
'login',
'openformat', 
'openformattype',
'paymentType',
'pics',
'rates',
'units'
);
$articlestbl = "articles";
$companiestbl = "companies";
$histtbl = "contacthist";
$logintbl = "login";
$permissionstbl = "premissions";
$currencytbl = "currency";
$ratestbl = "rates";
$accountstbl = "accounts";
$docstbl = "docs";
$docdetailstbl = "docdetails";
$catalogtbl = "catalog";
$transactionstbl = "transactions";
$supdocstbl = "supdocs";
$supdocdetailstbl = "supdocdetails";
$bankbooktbl = "bankbook";
$chequestbl = "cheques";
$correlationtbl="correlation";
$tranreptbl = "tranrep";
$itemstbl = "items";
$stattbl = "stat";

$softwarename = "Linet";		 /* up to 20 characters */
$softwarenameheb = _("Linet - Free accounting software based on Drorit");
$softwaremaker = "Speedcomp"; /* up to 20 characters */
$softwaremakerregnum = "069924504";
$softregnum = "00179402";	/* Software registration number in tax authorities */
//include "include/version.inc.php";
$title = $softwarenameheb;

// Account types 
$AcctType[0] = _("Customers");
// $AcctType[0] = '׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”';
$AcctType[1] = _("Suppliers");
// $AcctType[1] = '׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½';
$AcctType[2] = _("Outcomes");
// $AcctType[2] = '׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¦׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ן¿½ ׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”';
$AcctType[3] = _("Incomes");
// $AcctType[3] = '׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³ײ²ֲ ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”';
$AcctType[4] = _("Assets");
$AcctType[5] = _("Liabilities");
$AcctType[6] = _("Authorities");
$AcctType[7] = _("Banks");
// $AcctType[4] = '׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”';

// $AcctType[5] = '׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³ײ³ג€”׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”';
//$AcctType[6] = _("Equity");
//$AcctType[6] = _("financing expenses");

// $AcctType[6] = '׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ¢׳³ֲ³ײ²ֲ¦׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢';
//$AcctType[7] = _("Buys");
// $AcctType[7] = '׳³ֲ³ײ²ֲ§׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”';


// $AcctType[8] = '׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³ײ²ֲ ׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½';
//$AcctType[9] = _("Cash");
// $AcctType[9] = '׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½';

// $AcctType[10] = '׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¦׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½';
//$AcctType[11] = _("Stocks");
// $AcctType[11] = '׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢"׳³ֲ³ײ²ֲ¢';

// $AcctType[12] = '׳³ֲ³ײ²ֲ¨׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ© ׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¢';


//account types in the database
define("CUSTOMER", 0);
define("SUPPLIER", 1);
define("OUTCOME", 2);
define("INCOME", 3);
define("ASSETS", 4);
define("OBLIGATIONS", 5);
//define("CAPITAL", 6);
//define("FINANCING", 6);
//define("BUYS", 7);
define("AUTHORITIES", 6);
define("BANKS", 7);

//define("CASH", 9);

//define("STOCKS", 11); //papers

//define("CONTACT", 20);

// Predefined accounts 
define("BUYVAT", 1);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¢"׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ³ג€”׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("ASSETVAT", 2);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¢"׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ³ג€”׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³ײ²ֲ¦׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ן¿½ ׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
define("SELLVAT", 3);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¢"׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ¢׳³ֲ³ײ²ֲ¡׳³ֲ³ײ²ֲ§׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("PAYVAT", 4);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¢"׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢"׳³ֲ³׳’ג‚¬ג€�
define("OUTCOMECLEAR", 5);	// ׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֲ¢ ׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¨ ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
define("ROUNDING", 6);		// ׳³ֲ³ײ²ֲ¢׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
define("CHEQUE", 7);	// ׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ³ג€” ׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
define("CUSTTAX", 8);	// ׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֲ¢ ׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¨ ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("OPENBALANCE", 9);	// ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ³ג€”׳³ֲ³ײ²ֲ¨׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ³ג€”׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ן¿½
define("ACCTCASH", 10);	// ׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ³ג€” ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
define("CREDIT", 11);	// ׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ³ג€” ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ©׳³ֲ³ײ²ֲ¨׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢
define("DEPOSITS", 12);	// ׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("PRETAX", 13);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¡ ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³ײ²ֲ ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ן¿½ ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("NATINSPAY", 14);	// ׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ»ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג€� ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢ ׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢"׳³ֲ³׳’ג‚¬ג€�
define("NATINS", 15);	// ׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ»ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג€� ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢
define("IRS", 16);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¡ ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³ײ²ֲ ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ן¿½
define("EQOUTCOME", 17);	// ׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֲ¢ ׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ©
define("SALARY", 18);
//30-104 is taken check out accounts.txt
//define("PRETAX", 105); // ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¡ ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³ײ²ֲ ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ן¿½
define("OPEN_STOCK", 106);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢ ׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ³ג€”׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ן¿½
define("BUY_STOCK", 107);	// ׳³ֲ³ײ²ֲ§׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("CLOSE_STOCK", 108);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢ ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג‚¬ג„¢׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ²ֲ¨׳³ֲ³׳’ג‚¬ן¿½
define("GENOUTCOME", 109); // ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¦׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”
define("EMPLOYEESALARY", 110);	// ׳³ֲ³ײ²ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢"׳³ֲ³׳’ג‚¬ג€�
define("CARGAS", 111);	// ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§
define("COMMUNICATION", 112);	// ׳³ֲ³ײ³ג€”׳³ֲ³ײ²ֲ§׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ¨׳³ֲ³ײ³ג€” 
define("GENCUSTOMER", 113);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½
define("GENSUPPLIER", 114);	// ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½


//doc types in the database
$DocType[1] = _("Proforma");
// $DocType[1] = '׳³ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³ײ²ֲ¢׳³ֲ³ײ²ֲ¡׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ן¿½';
$DocType[2] = _("Delivery doc.");
// $DocType[2] = '׳³ֲ³ײ³ג€”. ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ©׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג€�';
$DocType[3] = _("Invoice");
// $DocType[3] = '׳³ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¡';
$DocType[4] = _("Credit invoice");
// $DocType[4] = '׳³ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ֳ·׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג€�ֲ¢';
$DocType[5] = _("Return document");
// $DocType[5] = '׳³ֲ³ײ³ג€”׳³ֲ³ײ²ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³ײ³ג€” ׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ¨׳³ֲ³׳’ג‚¬ן¿½';
$DocType[8] = _("Receipt");
// $DocType[6] = '׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ן¿½';

$DocType[6] = _("Quote");//adam:
$DocType[7] = _("Sales Order");
$DocType[9] = _("Invoice Receipt");
$DocType[10] = _("Parchace Order");

define("DOC_PROFORMA", 1);//300
define("DOC_DELIVERY", 2);//200
define("DOC_INVOICE", 3);//305
define("DOC_CREDIT", 4);//330
define("DOC_RETURN", 5);//210
define("DOC_QUOTE", 6);//adam:
define("DOC_SALES", 7);//none 
define("DOC_RECEIPT", 8);//400
define("DOC_INVRCPT", 9);//320
define('DOC_PARCHACEORDER',10);//500
//define('DOC_*',11);//810
// Document status definitions
define("OPEN", 0);
define("CLOSED", 1);



// Predefined types of transactions
define("MANUAL", 0);
define("INVOICE", 1);	// ׳³ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ³ג€”
define("SUPINV", 2);	// ׳³ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ©׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ²ֲ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³ײ²ֲ¡׳³ֲ³׳’ג€�ֳ—׳³ֲ³ײ²ֲ§
define("RECEIPT", 3);	// ׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ן¿½
define("CHEQUEDEPOSIT", 4);
define("SUPPLIERPAYMENT", 5);
define("VAT", 6);	// ׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ¢"׳³ֲ³׳�ֲ¿ֲ½
define("STORENO", 7);
define("BANKMATCH", 8);
define("SRCTAX", 9);
define("PATTERN", 10);
define("MANINVOICE", 11);
define("MANRECEIPT", 13);
define("TRAN_PRETAX", 14);
define("TRAN_SALARY", 15);
define("OPBALANCE", 16);

define("RETURNINV",17);
define("INVRCPT",18);
define("DOCREDIT",19);
define("DOCPROFORMA",20);
define("DELIVERY",21);
$DocOpenType=array(200=>DOC_DELIVERY,
					300=>DOC_PROFORMA,
					305=>DOC_INVOICE,
					330=>DOC_CREDIT,
					210=>DOC_RETURN,
					500=>DOC_PARCHACEORDER,
					400=>DOC_RECEIPT,
					320=>DOC_INVRCPT,
					);
$openTransType=array(200=>DELIVERY,
					300=>DOCPROFORMA,
					305=>INVOICE,
					330=>DOCREDIT,
					210=>RETURNINV,
					//500=>DOC_PARCHACEORDER,
					400=>RECEIPT,
					320=>INVRCPT
);
$TransType=array(
	//$doctype=>$transtype,
	DOC_INVOICE=>INVOICE,
	DOC_PROFORMA=>DOCPROFORMA,
	DOC_CREDIT=>DOCREDIT,
	DOC_RETURN=>RETURNINV,
	DOC_INVRCPT=>INVRCPT,
	DOC_RECEIPT=>RECEIPT,
	DOC_DELIVERY=>DELIVERY,
	
);
//trnsactions type in the book
$TranType[0] = _("Manual");
$TranType[1] = _("Invoice");
$TranType[2] = _("Supplier invoice");
$TranType[3] = _("Receipt");
$TranType[4] = _("Cheque deposit");
$TranType[5] = _("Supplier payment");
$TranType[6] = _("VAT");
$TranType[7] = _("Storeno");
$TranType[8] = _("Bank reconciliation");
$TranType[9] = _("Source tax");
$TranType[10] = _("Transaction pattern");
$TranType[11] = _("Manual invoice");
$TranType[13] = _("Manual receipt");
$TranType[14] = _("Tax prepayment");
$TranType[15] = _("Salary");
$TranType[16] = _("Openning balance");
$TranType[17] = _("Return Invoice");
$TranType[18] = _("Invoice Receipt");
$TranType[19] = _("Credit Invoice");
$TranType[20] = _("Proforma");
//Closing Entries

// $UnitArr = array('׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳�ֲ¿ֲ½ ׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”', '׳³ֲ³ײ²ֲ©׳³ֲ³ײ²ֲ¢׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€” ׳³ֲ³ײ²ֲ¢׳³ֲ³׳’ג‚¬ֻ�׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ן¿½', '׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ג€�׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳’ג‚¬ן¿½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³ײ³ג€”', '׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג€�ֲ¢׳³ֲ³ײ»ן¿½׳³ֲ³ײ²ֲ¨', '׳³ֲ³׳’ג‚¬ג„¢׳³ֲ³ײ²ֲ¨׳³ֲ³׳�ֲ¿ֲ½', '׳³ֲ³ײ²ֲ§׳³ֲ³׳’ג€�ֲ¢׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³׳’ג‚¬ֲ¢׳³ֲ³׳’ג‚¬ג„¢׳³ֲ³ײ²ֲ¨׳³ֲ³׳�ֲ¿ֲ½', '׳³ֲ³׳�ֲ¿ֲ½׳³ֲ³ײ»ן¿½׳³ֲ³ײ²ֲ¨');
//$UnitArr = array(_("No units"), _("work hours"), _("units"), _("liter"), _("gram"), _("Kilo gram"), _("Meter"));

$paymenttype = array(
		1 => _("Cash"), 
		//1 => _("מזומן"), 
		2 => _("Cheque"),
		//2 => _("צ'ק"),
		3 => _("Credit card"),
		//3 => _("כרטיס אשראי"),
		4 => _("Bank transfer")
		//4 => _("העברה בנקאית")
	);
$credittype=array(
	1=>'אשראי רגיל',
2=>'+30',
3=>'חיוב מיידי',
4=>'קרדיט מועדון',
5=>'סופר קרדיט',
6=>'קרדיט',
8=>'תשלומים',
9=>'תשלומים מועדון',
	);
	/*
$creditcompanies = array(
		0 => _("No credit"),
		//0 => _("אין אשראי"),
		1 => _("Isracard"),
		//1 => _("ישראקארד"),
		2 => _("Cal"),
		//2 => _("קאל"),
		3 => _("Diners"),
		//3 => _("דיינרס"),
		4 => _("American express"),
		//4 => _("אמריקן אקספרס"),
		6 => _("Leumi card")
		//6 => _("לאומי קארד")
		);*/

$banksarr = array(10 => 'לאומי',
	12 => 'פועלים',
	20 => 'מזרחי טפחות',
	11 => 'בנק דיסקונט לישראל',
	01 => 'יווטרייד',
	99 => 'ישראל',
	14 => 'אוצר החייל',
/*
בנק אגוד לישראל-13
בנק המזרחי המאוחד-20
בנק אמריקאי ישראל-24
בנק הבינלאומי הראשון לישראל-31
בנק יהב-04
בנק ספנות-08
בנק מרכנתיל דיסקונט-17
בנק כללי לישראל-26
בנק קונטיננטל לישראל-28
בנק עולמי להשקעות-47
סיטי בנק - 22 */
);
$montharr = array(_("January"), _("February"), _("March"), _("April"),
	_("May"), _("June"), _("July"), _("August"), _("September"), 
	_("October"), _("November"), _("December"));
	
?>