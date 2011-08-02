<?PHP
/*
 | Drorit accounting system
 | Written by Ori Idan Helicon technologies Ltd.
 |
 | This script is only used to create tables, it has no other role
 */
 ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>דרורית - הנהלת חשבונות חופשית</title>
<style>
.para {display: none;}
table { font-size: 14px; font-family: arial, sans-serif}
body {margin:0; font-size: 12px, font-family: arial, sans-serif}
a:visited {color:blue}
a:link {color:navy; font-family:arial, sans-serif }
a:hover {color:red}
.text1 { font-size:10px; font-family: arial, sans-serif}
.text2 { font-size:11px; font-family: arial, sans-serif}
.text3 { font-size:14px; font-family: arial, sans-serif}
h1 {font-size: 24; font-weight:bold; font-family: arial, sans-serif; color: navy}
h2 {font-size: 18; font-weight:bold; font-family: arial, sans-serif; color: navy}
</style>
</head>
<body>

<?PHP
include('config.inc.php');
include('include/core.inc.php');
print "user: $user<br>\n";
print "pswd: $pswd<br>\n";
$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");

if (mysql_select_db($database))
  {
  echo "Selected database: $database<br>\n";
  }
else
  {
  mysql_query("CREATE DATABASE `$database` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;",$link);
  //mysql_close($link);
  mysql_select_db($database);
  echo "Database: '$database' created<br>\n";
  
  
  }

//



/*
 | First check that main tables, login and mainlist are present
 */
 
 
 
$query = "SHOW TABLES";
$result = mysql_query($query);
if(!$result) {
  echo mysql_error();
  exit;
}
while($line = mysql_fetch_array($result, MYSQL_NUM)) {
	if($line[0] == $articlestbl)
		$articles = 1;
	if($line[0] == $picstbl)
		$pics = 1;
	if($line[0] == $logintbl)
		$login = 1;
	if($line[0] == $permissionstbl)
		$permissions = 1;
	if($line[0] == $companiestbl)
		$companies = 1;
	if($line[0] == $histtbl)
		$history = 1;
	if($line[0] == $accountstbl)
		$accounts = 1;
	if($line[0] == $contactstbl)
		$contacts = 1;
	if($line[0] == $transactionstbl)
		$transactions = 1;
	if($line[0] == $accttrantbl)
		$accttran = 1;
	if($line[0] == $docstbl)
		$docs = 1;
	if($line[0] == $docdetailstbl)
		$docdetails = 1;
	if($line[0] == $tranreptbl)
		$tranrep = 1;
	if($line[0] == $itemstbl)
		$items = 1;
	if($line[0] == $chequestbl)
		$cheques = 1;
	if($line[0] == $receiptstbl)
		$receipts = 1;
	if($line[0] == $currencytbl)
		$currency = 1;
	if($line[0] == $ratestbl)
		$rates = 1;
	if($line[0] == $bankbooktbl)
		$bankbook = 1;

}
if(!$articles) {
	print "Table $articlestbl does not exist...<BR>\n";
	$query = "CREATE TABLE $articlestbl (\n";
	$query .= "id VARCHAR(40) NOT NULL, PRIMARY KEY (id), \n";
	$query .= "ancestor VARCHAR(40), \n";
	$query .= "lang CHAR(3), \n";
	$query .= "subject VARCHAR(100), \n";
	$query .= "module VARCHAR(64), \n";
	$query .= "params VARCHAR(80), \n";
	$query .= "lastmod DATETIME, \n";
	$query .= "contents TEXT \n";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<BR>\n";
		echo mysql_error();
		exit;
	}
	print "Table $articlestbl created.<BR>\n";
}
if(!$pics) {
	print "Table pics does not exist...<BR>\n";
	$query = "CREATE TABLE $picstbl (\n";
	$query .= "num INTEGER UNSIGNED AUTO_INCREMENT, PRIMARY KEY (num), ";
	$query .= "ext CHAR(5), \n";
	$query .= "gallery VARCHAR(50), \n";
	$query .= "description TEXT \n";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<BR>\n";
		echo mysql_error();
		exit;
	}
	print "Table $picstbl created.<BR>\n";
}
if(!$login) {
	print "Login table does not exist...<BR>\n";
	$query = "CREATE TABLE $logintbl (";
	$query .= "name VARCHAR(100) NOT NULL PRIMARY KEY, ";
	$query .= "fullname VARCHAR(80), ";
/*	$query .= "email VARCHAR(60), "; */
	$query .= "password CHAR(41), ";	/* large enough for 4.1 server passwords */
	$query .= "lastlogin DATETIME, ";
	$query .= "cookie CHAR(32), ";		/* value to be stored in cookie along with name */
	$query .= "hash CHAR(32) ";
	$query .= ")";

	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br>\n";
		echo mysql_error();
		exit;
	}
	print "Login table created.<BR>\n";
}
if(!$permissions) {
	print "Permissions table does not exist...<br>\n";
	$query = "CREATE TABLE $permissionstbl (";
	$query .= "name VARCHAR(100), ";
	$query .= "company VARCHAR(40), ";
	$query .= "level INTEGER ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br>\n";
		echo mysql_error();
		exit;
	}
	print "Permissions table created.<br>\n";
}
if(!$companies) {
	print "Companies table does not exist...<BR>\n";
	$query = "CREATE TABLE $companiestbl (";
	$query .= "companyname VARCHAR(80), ";
	$query .= "prefix VARCHAR(40) NOT NULL PRIMARY KEY, ";
	$query .= "manager VARCHAR(80), ";
	$query .= "regnum CHAR(10), ";
	$query .= "address VARCHAR(128), ";
	$query .= "city VARCHAR(50), ";
	$query .= "zip CHAR(6), ";
	$query .= "phone VARCHAR(50), ";
	$query .= "cellular VARCHAR(50), ";
	$query .= "web VARCHAR(128), ";
	$query .= "tax DECIMAL(4,2), ";
	$query .= "taxrep INTEGER, ";
	$query .= "vat DECIMAL(4,2), ";
	$query .= "vatrep INTEGER, ";
	$query .= "template VARCHAR(128), ";
	$query .= "logo VARCHAR(255), ";
	$query .= "header VARCHAR(255), ";
	$query .= "footer VARCHAR(255), ";
	//$query .= "template VARCHAR(128), "; adam:
	$query .= "doc_template VARCHAR(128), ";
	$query .= "receipt_template VARCHAR(128), ";
	$query .= "invoice_receipt_template VARCHAR(128), ";
	$query .= "num1 INTEGER, ";		/* beginning number for הצעת מחיר */
	$query .= "num2 INTEGER, ";		/* beginning number for הזמנה */
	$query .= "num3 INTEGER, ";		/* beginning number for חשבון עסקה */		
	$query .= "num4 INTEGER, ";		/* beginning number for תעודת משלוח */
	$query .= "num5 INTEGER, ";		/* beginning number for חשבונית */
	$query .= "num6 INTEGER, ";		/* beginning number for חשבונית זיכוי */	
	$query .= "num7 INTEGER, ";		/* beginning number for תעודת החזרה (not used) */
	$query .= "num8 INTEGER ";		/* beginning number for קבלה */
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "Definitions table (deftable) created.<BR>\n";
}
if(!$history) {
	print "History table does not exist...<br>\n";
	$query = "CREATE TABLE $histtbl (";
	$query .= "prefix VARCHAR(40), ";
	$query .= "num INTEGER UNSIGNED, ";
	$query .= "dt DATE, ";
	$query .= "details TEXT ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "History table created.<br>\n";
}
if(!$accounts) {
	print "Accounts table does not exist...<BR>\n";
	$query = "CREATE TABLE $accountstbl (";
	$query .= "num INTEGER UNSIGNED, ";
	$query .= "prefix VARCHAR(40), ";
	$query .= "type INTEGER, ";
	$query .= "id6111 INTEGER UNSIGNED, ";	/* new field, not supported yet */
	$query .= "pay_terms INTEGER,";	/* 0 means no payment terms, neg num means end of month + days */
	$query .= "src_tax DECIMAL(5,2), ";	/* used for VAT percentage in case of outcome */
	$query .= "src_date DATE, ";
	$query .= "grp VARCHAR(80), ";	/* group for customer management */
	$query .= "company VARCHAR(80), ";
	$query .= "contact VARCHAR(80), ";
	$query .= "department VARCHAR(60), ";
	$query .= "vatnum VARCHAR(20), ";
	$query .= "email VARCHAR(50), ";
	$query .= "phone VARCHAR(20), ";
	$query .= "dir_phone VARCHAR(20), ";
	$query .= "cellular VARCHAR(20), ";
	$query .= "fax VARCHAR(20), ";
	$query .= "web VARCHAR(60), ";
	$query .= "address VARCHAR(80), ";
	$query .= "city VARCHAR(40), ";
	$query .= "zip VARCHAR(10), ";
	$query .= "comments TEXT";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "Accounts table created.<BR>\n";
}
if(!$contacts) {
	print "Contacts table does not exist...<BR>\n";
	$query = "CREATE TABLE $contactstbl (";
	$query .= "num INTEGER UNSIGNED, ";
	$query .= "prefix VARCHAR(40), ";
	$query .= "account INTEGER UNSIGNED, ";
	$query .= "name VARCHAR(80), ";
	$query .= "role VARCHAR(80), ";
	$query .= "company VARCHAR(80), ";
	$query .= "department VARCHAR(60), ";
	$query .= "email VARCHAR(50), ";
	$query .= "phone VARCHAR(20), ";
	$query .= "dir_phone VARCHAR(20), ";
	$query .= "cellular VARCHAR(20), ";
	$query .= "fax VARCHAR(20), ";
	$query .= "web VARCHAR(60), ";
	$query .= "address VARCHAR(80), ";
	$query .= "city VARCHAR(40), ";
	$query .= "zip VARCHAR(10), ";
	$query .= "comments TEXT";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "Contacts table created.<BR>\n";
}
if(!$transactions) {
	print "Transactions table does not exist...<BR>\n";
	$query = "CREATE TABLE $transactionstbl (";
	$query .= "prefix VARCHAR(40), ";
	$query .= "num INTEGER UNSIGNED, ";
	$query .= "type INTEGER UNSIGNED, ";
	$query .= "account INTEGER UNSIGNED, ";
	$query .= "refnum1 CHAR(20), ";
	$query .= "refnum2 CHAR(20), ";
	$query .= "date DATE, ";
	$query .= "details VARCHAR(256), ";
	$query .= "sum DECIMAL(8,2), ";
	$query .= "cor_num VARCHAR(100)";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "Transactions table created.<BR>\n";
}
if(!$tranrep) {
	print "Table tranrep does not exist...<br>\n";
	$query = "CREATE TABLE $tranreptbl (";
	$query .= "prefix VARCHAR(40), ";
	$query .= "num INTEGER unsigned, ";
	$query .= "date DATE, ";
	$query .= "refnum CHAR(20), ";
	$query .= "acctnum INTEGER UNSIGNED, ";
	$query .= "acctname VARCHAR(80), ";
	$query .= "opacct INTEGER UNSIGNED, ";
	$query .= "opacctname VARCHAR(40), ";
	$query .= "details VARCHAR(256), ";
	$query .= "total DECIMAL(8,2), ";
	$query .= "vat DECIMAL(8,2), ";
	$query .= "sum DECIMAL(8,2) ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br>\n";
		echo mysql_error();
		exit;
	}
	print "tranrep table created.<BR>\n";
}
if(!$items) {
	print "Items table does not exist...<br>\n";
	$query = "CREATE TABLE $itemstbl (";
	$query .= "num INTEGER UNSIGNED AUTO_INCREMENT, PRIMARY KEY(num), ";
	$query .= "prefix VARCHAR(40), ";
	$query .= "account INTEGER UNSIGNED, ";
	$query .= "name VARCHAR(100), ";
	$query .= "unit INTEGER, ";		/* not used in this version */
	$query .= "extcatnum VARCHAR(30), "; /* not used in this version */
	$query .= "manufacturer VARCHAR(40), "; /* not used in this version */
	$query .= "defprice DECIMAL(8,2), ";
	$query .= "currency INTEGER, ";
	$query .= "ammount INTEGER ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "supdocs table created.<BR>\n";
}
if(!$docs) {
	print "Table $docstbl does not exist...<BR>\n";
	$query = "CREATE TABLE $docstbl (";
	$query .= "num INTEGER UNSIGNED AUTO_INCREMENT, PRIMARY KEY (num), ";
	$query .= "prefix VARCHAR(40), ";
	$query .= "doctype INTEGER UNSIGNED, ";
	$query .= "docnum INTEGER UNSIGNED, ";
	$query .= "account INTEGER UNSIGNED, ";
	$query .= "company VARCHAR(80), ";
	$query .= "address VARCHAR(80), ";
	$query .= "city VARCHAR(40), ";
	$query .= "zip VARCHAR(10), ";
	$query .= "vatnum VARCHAR(10), ";
	$query .= "refnum VARCHAR(20), ";
	$query .= "issue_date DATE, ";
	$query .= "due_date DATE, ";
	$query .= "sub_total DECIMAL(8,2), ";
	$query .= "novat_total DECIMAL(8,2), ";
	$query .= "vat DECIMAL(8,2), ";
	$query .= "total DECIMAL(8,2), ";
	$query .= "status INTEGER, ";	/* open, inprogress, closed */
	$query .= "printed INTEGER, ";	/* 1 means printed */
	$query .= "comments VARCHAR(128) ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "$docs table created.<BR>\n";
}
if(!$docdetails) {
	print "$docdetailstbl table does not exist...<BR>\n";
	$query = "CREATE TABLE $docdetailstbl (";
	$query .= "prefix VARCHAR(40), ";
	$query .= "num INTEGER UNSIGNED, ";
	$query .= "cat_num INTEGER UNSIGNED, ";
	$query .= "description VARCHAR(128), ";
	$query .= "qty DECIMAL(5,2), ";
	$query .= "unit_price DECIMAL(8,2), ";
	$query .= "currency INTEGER UNSIGNED, ";
	$query .= "price DECIMAL(8,2), ";
	$query .= "nisprice DECIMAL(8,2)";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "$docdetails table created.<BR>\n";
}
if(!$cheques) {
	print "Cheques table does not exist...<BR>\n";
	$query = "CREATE TABLE $chequestbl (";
	$query .= "prefix VARCHAR(40), ";
	$query .= "refnum VARCHAR(10), ";		/* receipt number */
	$query .= "type INTEGER, ";
	$query .= "creditcompany INTEGER, ";
	$query .= "cheque_num CHAR(10), ";
	$query .= "bank CHAR(3), ";
	$query .= "branch CHAR(3), ";
	$query .= "cheque_acct CHAR(20), ";
	$query .= "cheque_date DATE, ";
	$query .= "sum DECIMAL(8,2), ";
	$query .= "bank_refnum CHAR(10), ";
	$query .= "dep_date DATE";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br>\n";
		echo mysql_error();
		exit;
	}
	print "Cheques table created.<BR>\n";
}
if(!$receipts) {
	print "Receipts table does not exist...<BR>\n";
	$query = "CREATE TABLE $receiptstbl (";
	$query .= "num INTEGER UNSIGNED AUTO_INCREMENT, PRIMARY KEY(num), ";
	$query .= "prefix VARCHAR(40), ";
	$query .= "account INTEGER UNSIGNED, ";
	$query .= "company VARCHAR(80), ";
	$query .= "address VARCHAR(80), ";
	$query .= "city VARCHAR(40), ";
	$query .= "zip VARCHAR(10), ";
	$query .= "vatnum VARCHAR(10), ";
	$query .= "refnum INTEGER UNSIGNED, ";
	$query .= "issue_date DATE, ";
	$query .= "invoices TEXT, "; /* comma seperated list of invoices numbers to close */
	$query .= "comments TEXT, ";
	$query .= "sum DECIMAL(8,2), ";
	$query .= "src_tax DECIMAL(5,2), ";
	$query .= "printed INTEGER UNSIGNED";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "Receipts table created.<BR>\n";
}

if(!$currency) {
	print "Currency table does not exist...<BR>\n";
	$query = "CREATE TABLE $currencytbl (";
	$query .= "curnum INTEGER unsigned AUTO_INCREMENT, PRIMARY KEY (curnum), ";
	$query .= "name VARCHAR(40), ";
	$query .= "sign VARCHAR(16) ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "currencty table created.<BR>\n";
}
if(!$rates) {
	print "Rates table does not exist...<br>\n";
	$query = "CREATE TABLE $ratestbl (";
	$query .= "curnum INTEGER UNSIGNED, ";
	$query .= "date DATE, ";
	$query .= "rate DECIMAL(7,6) ";
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "Rates table created.<br>\n";
}

if(!$bankbook) {
	print "bankbook table does not exist...<BR>\n";
	$query = "CREATE TABLE $bankbooktbl (";
	$query .= "num INTEGER unsigned, ";
	$query .= "prefix VARCHAR(40), ";
	$query .= "account INTEGER, ";
	$query .= "date DATE, ";
	$query .= "details VARCHAR(60), ";
	$query .= "refnum CHAR(10), ";
	$query .= "sum DECIMAL(8,2), ";
	$query .= "total DECIMAL(8,2), ";
	$query .= "cor_num VARCHAR(30) ";	// מספר התאמה
	$query .= ")";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	print "bankbook table created.<BR>\n";
}

?>
