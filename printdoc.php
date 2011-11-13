<?PHP
/*
 | PrintDoc
 | Printing business document module for linet accounting system.
 | Written by: Ori Idan August 2009
 | Changed by: Adam Ben Hour 2011
 */
 
$print_win = isset($_GET['print_win']) ? $_GET['print_win'] : 0;
$print_win=1;
if (!$print_win==1)
 header('Content-type: text/html;charset=UTF-8');

require('config.inc.php');
require('include/i18n.inc.php');
require('include/core.inc.php');
require('include/func.inc.php');
require('class/company.php');
require('class/document.php');
$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");//adam:
mysql_select_db($database) or die("Could not select database: $database");

$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
$doctype = isset($_GET['doctype']) ? $_GET['doctype'] : DOC_INVOICE;
$docnum = isset($_GET['docnum']) ? $_GET['docnum'] : 0;
$company=new company;
$company->prefix=$prefix;
if(!$company->getCompany()) exit;
//print_r($company);
/*$vtigernum = isset($_GET['vtigernum']) ? $_GET['vtigernum'] : 0;

 if($vtigernum != 0) {//vtiger addon
	$query = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND doctype='$doctype' AND vtiger=$vtigernum";
	//print $query;
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	//print $query."<br>";
	if ($line[0]!='')
	$docnum=$line[3];
	$idnum=$line["num"];
	//echo $docnum;
}else{*/
	$query = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND docnum='$docnum' AND doctype='$doctype'";
	//print $query;
	$result = DoQuery($query, "printdoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	//print $query."<br>";
	//if ($line[0]!='')
	//$docnum=$line[3];
	$idnum=$line[0];
	//print ";$idnum;<br />";
//}
if ($docnum ==0){
	print('<center>׳�׳� ׳§׳™׳™׳�</center>');
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
	if($company->doc_template)
		$template = $company->doc_template;
	else
		$template = "templates/docs.html";
}
else if($doctype == DOC_RECEIPT) {
	if($company->receipt_template)
		$template = $company->receipt_template;
	else
		$template = "templates/receipt.html";
}	
else if($doctype > DOC_RECEIPT) {
	if($company->invoice_receipt_template)
		$template = $company->invoice_receipt_template;
	else
		$template = "templates/invrcp.html";
}

function isdate($dt) {
	list($y, $m, $d) = explode('-', $dt);
	if(!$m || !$d)
		return 0;
	return checkdate($m, $d, $y);
}
/*get doc detiales in array*/
//SELECT * FROM $docdetailstbl WHERE num='$idnum' AND prefix='$prefix'
//$detiales=selectSql(array('num'=>$idnum,'prefix'=>$prefix),$docdetailstbl);
/* getdoc */
$doc=new document();
$doc->num=$idnum;
$doc->getDocument();
//print_r($doc);
function TemplateReplace($r) {
	global $prefix;
	global $doctype, $docnum,$idnum;
	global $DocType, $paymenttype,$banksarr, $creditcompanies;
	global $company,$doc;
	global $docstbl, $docdetailstbl,$chequestbl;
	global $ln, $lasttbl, $docref;
	global $result, $line;
	global $stdheader;
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
	else if($p == 'logo') {
		$logo = $company->logo;
		if($logo)
			return '<img src="img/logo/'.$logo.'">';//adam:'<img src="img/'.$logo.'">';
		else
			return "";
	}
	else if($p == 'dealer') {
		return _("Authorized dealer");
	}
	else if($p == 'copy') {
		$query = "SELECT printed FROM $docstbl WHERE prefix='$prefix' AND doctype='$doctype' AND docnum='$docnum'";
		$result = DoQuery($query, "printdoc.php");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		if($line[0] == 0)
			return _("Source");
		else
			return _("Copy");
	}
	else if($p == 'doctype') {
		$l = $DocType[$dt];
		return $l;
	}
	else if($p == 'docnum')
		return $docnum;
	else if(($p == 'header')||($p == 'footer')||($p == 'companyname')||($p == 'web')||($p == 'regnum')) {
		return $company->{$p};
	}
	else if ($p == 'address'){
		return $company->address.', '.$company->city." ".$company->zip;
	}
	else if ($p=='phones'){
		return _('phone').": ".$company->phone." | "._("fax").": ".$company->cellular;
	}
	else if($p=='docdet'){
		$detiales=$doc->docdetials;
		$str='';
		foreach ($detiales as $detial){
			$str.='<tr>';
			$str.='<td>'.$detial->description.'</td>';
			$str.='<td>'.$detial->qty.'</td>';
			$str.='<td>'.$detial->unit_price.'</td>';
			$str.='<td>'.$detial->price.'</td>';
			$str.='</tr>';
		}
		return $str;
	}
	else if ($p=='rcpt'){
		$rcpt=$doc->rcptdetials;
		$str='';
		foreach ($rcpt as $detial){
			$str.='<tr>';
			$str.='<td>'.$detial->type.'</td>';
			$str.='<td>'.$detial->creditcompany.'</td>';
			$str.='<td>'.$detial->cheque_num.'</td>';
			$str.='<td>'.$detial->bank.'</td>';
			$str.='<td>'.$detial->branch.'</td>';
			$str.='<td>'.$detial->cheque_acct.'</td>';
			$str.='<td>'.$detial->cheque_date.'</td>';
			$str.='<td>'.$detial->sum.'</td>';
			$str.='</tr>';
		}
		return $str;
	}
	//else 
	list($frm, $fld) = explode(':', $p);
	if(!$frm)
		return '';
	if($frm=='docs'){
		return $doc->{$fld};
	}
	return "$s";
	}
$file = fopen($template, "r");
if(!$file) {
	print "Unable to open: $template<br />\n";
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

$bla1="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0//EN\">
<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<link rel=\"stylesheet\" type=\"text/css\" href=\"style/documenet.css\" />
	<title>bla</title>
</head>
<body dir=\"rtl\">".$bla."<a href=\"tmp/$prefix.pdf\">Download</a></body></html>";

$bla="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\">
<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	
	<link rel=\"stylesheet\" type=\"text/css\" href=\"$path/style/documenet.css\" />
	<title>bla</title>
</head>
<body dir=\"rtl\">".$bla."</body></html>";

echo $bla1;

$myFile = "$path/tmp/$prefix.html";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $bla);
fclose($fh);
$a="xvfb-run -a -s \"-screen 0 1024x768x16\" wkhtmltopdf --dpi 96 --page-size A4 $myFile $path/tmp/$prefix.pdf";
shell_exec($a);

if(!$print_win==1) {
	print "<div style=\"width:100%;text-align:center\">\n";
	$l = _("Print");
	print "<form><input type=\"button\" value=\"$l\" ";
	print "onclick=\"PrintWin()\">\n";
	print "</form>\n";
	print "</div>\n";
}else {
	/* Increment copies printed */
	//if($doctype == DOC_RECEIPT)
		//$query = "SELECT printed FROM $docstbl WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
	//else {
		//if($doctype > $DOC_RECEIPT)
		//	$dt = DOC_INVOICE;
		//else
		//	$dt = $doctype;
	$query = "SELECT printed FROM $docstbl WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
	//}
	$result = DoQuery($query, __FILE__.": ".__LINE__);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$printed = $line[0];
	$printed++;
	//if($doctype < DOC_RECEIPT) {
	$query = "UPDATE $docstbl SET printed='$printed' ";
	$query .= "WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
	DoQuery($query, "printdoc.php");
	//}
	/*else if($doctype == DOC_RECEIPT) {
		$query = "UPDATE $docstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
		DoQuery($query, "printdoc.php");
	}
	else if($doctype > DOC_RECEIPT) {
		$query = "UPDATE $docstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
		DoQuery($query, "printdoc.php");
		$query = "UPDATE $docstbl SET printed='$printed' ";
		$query .= "WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
		DoQuery($query, "printdoc.php");
	}*/
}


fclose($file);
 
?>