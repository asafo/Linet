<?PHP
/*
 | PrintDoc
 | Printing business document module for linet accounting system.
 | Written by: Ori Idan August 2009
 | Changed by: Adam Ben Hour 2011
 */
// header('Content-type: text/html;charset=UTF-8');
 
$print_win = isset($_GET['print_win']) ? $_GET['print_win'] : 0;
if (!$print_win==1)
 header('Content-type: text/html;charset=UTF-8');

include('config.inc.php');
include('include/core.inc.php');
include('include/func.inc.php');
include('include/i18n.inc.php');



$DocType[1] = _("Proforma");
//$DocType[1] = 'חשבון עסקה';
$DocType[2] = _("Delivery doc.");
//$DocType[2] = 'ת. משלוח';
$DocType[3] = _("Invoice");
//$DocType[3] = 'חשבונית מס';
$DocType[4] = _("Credit invoice");
//$DocType[4] = 'חשבונית זיכוי';
$DocType[5] = _("Return document");
//$DocType[5] = 'תעודת החזרה';
$DocType[6] = _("Receipt");
//$DocType[6] = 'קבלה';


$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");//adam:
mysql_select_db($database) or die("Could not select database: $database");

$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
$doctype = isset($_GET['doctype']) ? $_GET['doctype'] : DOC_INVOICE;
$docnum = isset($_GET['docnum']) ? $_GET['docnum'] : 0;
$vtigernum = isset($_GET['vtigernum']) ? $_GET['vtigernum'] : 0;

 if($vtigernum != 0) {//vtiger addon
	$query = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND doctype='$doctype' AND vtiger=$vtigernum";
	//print $query;
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	//print $query."<br>";
	if ($line[0]!='')
	$docnum=$line[3];
	//echo $docnum;
}
if ($docnum ==0){
	print('<center>לא קיים</center>');
	exit;
}
$stdheader = <<<STDHEAD
<script type="text/javascript">function PrintWin() {window.open('printdoc.php?doctype=$doctype&docnum=$docnum&prefix=$prefix&print_win=1', 'PrintWin', 'width=800,height=600,scrollbar=yes');}</script>
STDHEAD;

$ln = 0;	/* line number for multilines queries */
$lasttbl = '';	/* last table in query */
$docref = 0;
$result = 0;
$line = array();
if($doctype < DOC_RECEIPT) {
	$query = "SELECT doc_template FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	if($line[0])
		$template = $line[0];
	else
		//$template = "docstemplate.html";
		$template = "templates/docs.html";
//	print "template: $template<br>\n";
}
else if($doctype == DOC_RECEIPT) {
	$query = "SELECT receipt_template FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	if($line[0])
		$template = $line[0];
	else
		$template = "templates/receipt.html";//$template = "receipttemplate.html";
}	
else if($doctype > DOC_RECEIPT) {
	$query = "SELECT invoice_receipt_template FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	if($line[0])
		$template = $line[0];
	else
		//$template = "invrcptemplate.html";
		$template = "templates/invrcp.html";
}

function isdate($dt) {
	list($y, $m, $d) = explode('-', $dt);
	if(!$m || !$d)
		return 0;
	return checkdate($m, $d, $y);
}

function TemplateReplace($r) {
//include('linet.inc.php');
	global $prefix;
	global $doctype, $docnum;
	global $DocType, $paymenttype,$banksarr, $creditcompanies;
	global $companiestbl;
	global $receiptstbl, $docstbl, $docdetailstbl,$chequestbl;
	global $ln, $lasttbl, $docref;
	global $result, $line;
	global $stdheader;
	//print($banksarr);
	$dt = ($doctype > DOC_RECEIPT) ? DOC_INVOICE : $doctype;
	$p = str_replace('~', '', $r[0]);
	if($p == 'head') {
		return "$stdheader";
	}
	else if($p == 'title') {
		$l = $DocType[$dt];
		$dts = $l;
		return "$dts $docnum";
	}
	else if($p == 'header') {
		$lasttbl = $companiestbl;
		$query = "SELECT header FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "TemplateReplace");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	else if($p == 'footer') {
		$lasttbl = $companiestbl;
		$query = "SELECT footer FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "TemplateReplace");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	else if($p == 'logo') {
		$lasttbl = $companiestbl;
		$query = "SELECT logo FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "TemplateReplace");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$logo = $line[0];
		if($logo)
			return 'bla';//adam:'<img src="img/'.$logo.'">';
		else
			return "";
	}
	else if($p == 'regnum') {
		$lasttbl = $companiestbl;
		$query = "SELECT regnum FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "TemplateReplace");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	else if($p == 'dealer') {
		return "עוסק מורשה";
	}
	else if($p == 'copy') {
		if($doctype < DOC_RECEIPT) {
			$query = "SELECT printed FROM $docstbl WHERE prefix='$prefix' AND doctype='$doctype' AND docnum='$docnum'";
		}
		else {
			$query = "SELECT printed FROM $receiptstbl WHERE prefix='$prefix' AND refnum='$docnum'";
		}
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		if($line[0] == 0)
			//return _("Source");
			return "מקור";
		else
			//return _("Copy");
			return "העתק";
	}
	else if($p == 'doctype') {
		$l = $DocType[$dt];
		return $l;
	}
	else if($p == 'docnum')
		return $docnum;
	else if($p == 'company') {
		$lasttbl = $companiestbl;
		$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}	
	else if($p == 'address') {
		$lasttbl = $companiestbl;
		$query = "SELECT address FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	else if($p == 'phone') {
		$query = "SELECT phone FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	else if($p == 'cellular') {
		$query = "SELECT cellular FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}	
	else if($p == 'city') {
		$lasttbl = $companiestbl;
		$query = "SELECT city FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	else if($p == 'zip') {
		$lasttbl = $companiestbl;
		$query = "SELECT zip FROM $companiestbl WHERE prefix='$prefix'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}
	list($tbl, $fld, $n) = explode(':', $p);
	if(!$fld)
		return '';
//	print "$tbl, $fld, $n, $lasttbl<br>\n";
	if($lasttbl != $tbl) {
		if($tbl == 'docs')
			$query = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
		else if($tbl == 'receipts')//adam:  || ($tbl == 'cheques'))
			$query = "SELECT * FROM $receiptstbl WHERE prefix='$prefix' AND refnum='$docnum'";
		else if($tbl == 'docdetails')
			$query = "SELECT * FROM $docdetailstbl WHERE num='$docref' AND prefix='$prefix'";
		else if($tbl == 'cheques')//adam:
			$query = "SELECT * FROM $chequestbl WHERE refnum='$docnum' AND prefix='$prefix'";
		$lasttbl = $tbl;
		//print "tbl: $tbl, fld: $fld Query: $query<br>\n";
		$result = DoQuery($query, "TemplateReplace");
		$ln = 0;
	}
	if(($n && ($n != $ln)) || ($ln == 0)) {
//	print "n: $n, ln: $ln<br>\n";
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		if(($tbl == 'docs') || ($tbl == 'receipts'))
			$docref = $line['num'];
		$ln++;
	}
	$s = $line[$fld];
	/*if(isdate($s))//adam: no need
		$s = FormatDate($s, "mysql", "dmy");
	*/
	if($fld == 'type'){		/* Payment type */
		if (!$s=='') return '<tr><td>'.$paymenttype[$s].'</td>';
	}else if($fld == 'creditcompany'){
		if (!$creditcompanies[$s]=='') return '<td>'.$creditcompanies[$s].'</td>';//$creditcompanies[$s]
	}
	else if($fld == 'bank') {
		$bs = $banksarr[$s];
		if (!$s=='')  return '<td>'."$s-$bs".'</td>';
	}else if(($fld=='cheque_num')  || ($fld=='branch') || ($fld=='cheque_acct') || ($fld=='cheque_date')){
		if (!$s=='') return '<td>'.$s.'</td>';
	}else if($fld=='sum'){
		if ($tbl == 'cheques') {if (!$s=='') {return '<td>'.$s.'</td></tr>';}}
		return $s;
	}else if($fld=='description'){//adam: invoice
		if (!$s=='') return '<tr><td width="370">'.$s.'</td>';
	}else if(($fld=='qty')  || ($fld=='unit_price')){
		if (!$s=='') return '<td width="60">'.$s.'</td>';
	}else if ($fld=='price'){
		if (!$s=='') return '<td width="60">'.$s.'</td></tr>';
	}
	if($s == '')//&nbsp;adam fucks the pdf
		$s = "";
	//print($fld);
	return "$s";
}
$file = fopen($template, "r");
if(!$file) {
	print "Unable to open: $template<BR>\n";
	exit;
}
//$bla='<htm><haed><body>';
$found=false;
$bla='';
while(!feof($file)) {
	$str = fgets($file);
	$new = preg_replace_callback("/~[^\x20|^~]*~/", "TemplateReplace", $str);
	if (substr_count($new,'</body')>=1) $found=false;
	if(!$print_win==1)	print("$new");
	if ($found)	$bla.=$new;
	if (substr_count($new,'<body dir="rtl">')>=1) $found=true;
}

//$bla=$bla.'</body></html>';
//echo 'start world <br>'.$bla.'end world!<br>';
//ob_end_clean();
require_once("module/invoice/CreatePDF.php");
$pdf=get_pdf($bla,$docnum,$user,$mail,$phone);
//print($dir.$iface_lang);
global $path;
$filepath=$path.'/tmp/tmp.pdf';//adam: full file path here
//$pdf->Output($filepath,'F'); //added file name to make it work in IE, also forces the download giving the user the option to save
//print '<a href="tmp/Invoice.pdf">PDF</a><br>fighting all dune!<br>';
ob_end_clean();
$pdf->Output('Invoice.pdf','D'); //added file name to make it work in IE, also forces the download giving the user the option to save


	
	
if(!$print_win==1) {
	print "<div style=\"width:100%;text-align:center\">\n";
	$l = _("Print");
	print "<form><input type=\"button\" value=\"$l\" ";
	print "onclick=\"PrintWin()\">\n";
	print "</form>\n";
	print "</div>\n";
}
else {

	/* Increment copies printed */
	if($doctype == DOC_RECEIPT)
		$query = "SELECT printed FROM $receiptstbl WHERE prefix='$prefix' AND refnum='$docnum'";
	else {
		if($doctype > $DOC_RECEIPT)
			$dt = DOC_INVOICE;
		else
			$dt = $doctype;
		$query = "SELECT printed FROM $docstbl WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
	}
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$printed = $line[0];
	$printed++;
	if($doctype < DOC_RECEIPT) {
		$query = "UPDATE $docstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
		DoQuery($query, "printdoc.php");
	}
	else if($doctype == DOC_RECEIPT) {
		$query = "UPDATE $receiptstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND refnum='$docnum'";
		DoQuery($query, "printdoc.php");
	}
	else if($doctype > DOC_RECEIPT) {
		$query = "UPDATE $docstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
		DoQuery($query, "printdoc.php");
		$query = "UPDATE $receiptstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND refnum='$docnum";
		DoQuery($query, "printdoc.php");
	}
	
	$certificate = 'templates/069924504.crt';

// set additional information
$info = array(
    'Name' => 'TCPDF',
    'Location' => 'Office',
    'Reason' => 'Testing TCPDF',
    'ContactInfo' => 'http://www.tcpdf.org',
    );
// set document signature
//$pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);
	$pdf->Output('Invoice.pdf','D'); //added file name to make it work in IE, also forces the download giving the user the option to save

	//send here
}


fclose($file);
 
?>
