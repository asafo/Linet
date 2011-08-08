<?php
$updatesrv ='http://82.80.233.231';//http://localhost/server/';

$table["articles"]="articles";
$table["pics"] = "pics";
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
$table["receipts"] = "receipts";
$table["bankbook"] = "bankbook";
$table["cheques"] = "cheques";
$table["tranpatt"] = "tranpatt";
$table["tranrep"] = "tranrep";
$table["stat"] = "items";
$table["articles"] = "stat";
$table["openformat"] ="openformat";
$table["openformattype"]="openformattype";

$articlestbl = "articles";
$picstbl = "pics";
$companiestbl = "companies";
$histtbl = "contacthist";
$logintbl = "login";
$permissionstbl = "premissions";
$currencytbl = "currency";
$ratestbl = "rates";
$accountstbl = "accounts";
$contactstbl = "contacts";
$docstbl = "docs";
$docdetailstbl = "docdetails";
$catalogtbl = "catalog";
$transactionstbl = "transactions";
$supdocstbl = "supdocs";
$supdocdetailstbl = "supdocdetails";
$receiptstbl = "receipts";
$bankbooktbl = "bankbook";
$chequestbl = "cheques";
$tranpattbl = "tranpatt";
$tranreptbl = "tranrep";
$itemstbl = "items";
$stattbl = "stat";

$softwarename = "Linet";		 /* up to 20 characters */
$softwarenameheb = _("Linet - Free accounting software based on Drorit");
$softwaremaker = "Speedcomp"; /* up to 20 characters */
$softwaremakerregnum = "069924504";
$softregnum = "00179401";	/* Software registration number in tax authorities */

$title = $softwarenameheb;

// Account types 
$AcctType[0] = _("Customers");
// $AcctType[0] = 'לקוחות';
$AcctType[1] = _("Suppliers");
// $AcctType[1] = 'ספקים';
$AcctType[2] = _("Outcomes");
// $AcctType[2] = 'הוצאות הנהלה וכלליות';
$AcctType[3] = _("Incomes");
// $AcctType[3] = 'הכנסות';
$AcctType[4] = _("Authorities");
// $AcctType[4] = 'מוסדות';
$AcctType[5] = _("Liabilities");
// $AcctType[5] = 'התחיבויות';
$AcctType[6] = _("Equity");
// $AcctType[6] = 'הון עצמי';
$AcctType[7] = _("Buys");
// $AcctType[7] = 'קניות';
$AcctType[8] = _("Banks");
// $AcctType[8] = 'בנקים';
$AcctType[9] = _("Cash");
// $AcctType[9] = 'מזומנים';
$AcctType[10] = _("financing expenses");
// $AcctType[10] = 'הוצאות מימון';
$AcctType[11] = _("Stocks");
// $AcctType[11] = 'ני"ע';
$AcctType[12] = _("Assets");
// $AcctType[12] = 'רכוש קבוע';

define("CUSTOMER", 0);
define("SUPPLIER", 1);
define("OUTCOME", 2);
define("INCOME", 3);
define("AUTHORITIES", 4);
define("OBLIGATIONS", 5);
define("CAPITAL", 6);
define("BUYS", 7);
define("BANKS", 8);
define("CASH", 9);
define("FINANCING", 10);
define("STOCKS", 11);
define("ASSETS", 12);
define("CONTACT", 20);

// Predefined accounts 
define("BUYVAT", 1);	// מע"מ תשומות
define("ASSETVAT", 2);	// מע"מ תשומות ציוד ונכסים
define("SELLVAT", 3);	// מע"מ עסקאות
define("PAYVAT", 4);	// מע"מ חו"ז
define("OUTCOMECLEAR", 5);	// ניכוי במקור מספקים
define("ROUNDING", 6);		// עיגול סכומים
define("CHEQUE", 7);	// קופת שיקים
define("CUSTTAX", 8);	// ניכוי במקור מלקוחות
define("OPENBALANCE", 9);	// יתרות פתיחה
define("ACCTCASH", 10);	// קופת מזומנים
define("CREDIT", 11);	// קופת אשראי
define("DEPOSITS", 12);	// פקדונות
define("PRETAX", 13);	// מס הכנסה מקדמות
define("NATINSPAY", 14);	// ביטוח לאומי חו"ז
define("NATINS", 15);	// ביטוח לאומי
define("IRS", 16);	// מס הכנסה
define("EQOUTCOME", 17);	// שווי שימוש
define("SALARY", 18);	// משכורות
define("PRETAX", 105); // מקדמות מס הכנסה
define("OPEN_STOCK", 106);	// מלאי פתיחה
define("BUY_STOCK", 107);	// קניות
define("CLOSE_STOCK", 108);	// מלאי סגירה
define("GENOUTCOME", 109); // הוצאות שונות
define("EMPLOYEESALARY", 110);	// עובדים חו"ז
define("CARGAS", 111);	// דלק
define("COMMUNICATION", 112);	// תקשורת 
define("GENCUSTOMER", 113);	// לקוחות שונים
define("GENSUPPLIER", 114);	// ספקים שונים

$DocType[1] = _("Proforma");
// $DocType[1] = 'חשבון עסקה';
$DocType[2] = _("Delivery doc.");
// $DocType[2] = 'ת. משלוח';
$DocType[3] = _("Invoice");
// $DocType[3] = 'חשבונית מס';
$DocType[4] = _("Credit invoice");
// $DocType[4] = 'חשבונית זיכוי';
$DocType[5] = _("Return document");
// $DocType[5] = 'תעודת החזרה';
$DocType[6] = _("Receipt");
// $DocType[6] = 'קבלה';
$DocType[7] = _("Quote");//adam:
$DocType[8] = _("Sales Order");

define("DOC_PROFORMA", 1);
define("DOC_DELIVERY", 2);
define("DOC_INVOICE", 3);
define("DOC_CREDIT", 4);
define("DOC_RETURN", 5);
define("DOC_RECEIPT", 6);

// Document status definitions
define("OPEN", 0);
define("CLOSED", 1);

// Predefined types of transactions
define("MANUAL", 0);
define("INVOICE", 1);	// חשבונית
define("SUPINV", 2);	// חשבונית ספק
define("RECEIPT", 3);	// קבלה
define("CHEQUEDEPOSIT", 4);
define("SUPPLIERPAYMENT", 5);
define("VAT", 6);	// מע"מ
define("STORENO", 7);
define("BANKMATCH", 8);
define("SRCTAX", 9);
define("PATTERN", 10);
define("MANINVOICE", 11);
define("MANRECEIPT", 13);
define("TRAN_PRETAX", 14);
define("TRAN_SALARY", 15);
define("OPBALANCE", 16);

$TranType[0] = _("Manual");
// $TranType[0] = 'רשום ידני';
$TranType[1] = _("Invoice");
// $TranType[1] = 'חשבונית';
$TranType[2] = _("Supplier invoice");
// $TranType[2] = 'חשבונית ספק';
$TranType[3] = _("Receipt");
// $TranType[3] = 'קבלה';
$TranType[4] = _("Cheque deposit");
// $TranType[4] = 'הפקדת שיק';
$TranType[5] = _("Supplier payment");
// $TranType[5] = 'תשלום לספק';
$TranType[6] = _("VAT");
// $TranType[6] = 'מע\"מ';
$TranType[7] = _("Storeno");
// $TranType[7] = 'סטורנו';
$TranType[8] = _("Bank reconciliation");
// $TranType[8] = 'התאמת בנק';
$TranType[9] = _("Source tax");
// $TranType[9] = 'ניכוי במקור';
$TranType[10] = _("Transaction pattern");
// $TranType[10] = 'תבנית תנועה';
$TranType[11] = _("Manual invoice");
// $TranType[11] = 'חשבונית ידנית';
$TranType[13] = _("Manual receipt");
// $TranType[13] = 'קבלה ידנית';
$TranType[14] = _("Tax prepayment");
// $TranType[14] = 'מס הכנסה מקדמות';
$TranType[15] = _("Salary");
// $TranType[15] = 'משכורת';
$TranType[16] = _("Openning balance");

// $UnitArr = array('ללא יחידות', 'שעות עבודה', 'יחידות', 'ליטר', 'גרם', 'קילוגרם', 'מטר');
$UnitArr = array(_("No units"), _("work hours"), _("units"), _("liter"), _("gram"), _("Kilo gram"), _("Meter"));

$paymenttype = array(
		//1 => _("Cash"), 
		1 => _("מזומן"), 
		//2 => _("Cheque"),
		2 => _("צ'ק"),
		//3 => _("Credit card"),
		3 => _("כרטיס אשראי"),
		//4 => _("Bank transfer")
		4 => _("העברה בנקאית")
	);

$creditcompanies = array(
		//0 => _("No credit"),
		0 => _("אין אשראי"),
		//1 => _("Isracard"),
		1 => _("ישראקארד"),
		//2 => _("Cal"),
		2 => _("קאל"),
		//3 => _("Diners"),
		3 => _("דיינרס"),
		//4 => _("American express"),
		4 => _("אמריקן אקספרס"),
		//6 => _("Leumi card")
		6 => _("לאומי קארד")
		);

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

?>