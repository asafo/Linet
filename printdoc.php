<?PHP
/*
 | PrintDoc
 | Printing business document module for linet accounting system.
 | Written by: Ori Idan August 2009
 | Changed by: Adam Ben Hour 2011
 */
$print_win = isset($_GET['print_win']) ? $_GET['print_win'] : 0;

//if(!isset($_GET['module'])){
	require('config.inc.php');
	require('include/i18n.inc.php');
	require('include/core.inc.php');
	require('include/func.inc.php');
	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_query("SET NAMES 'utf8'");//adam:
	mysql_select_db($database) or die("Could not select database: $database");
	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	include_once('class/company.php');
	$compy=new company;
	$compy->prefix=$prefix;
	if(!$compy->getCompany()) exit;
	//$_SESSION['company']=serialize($compy);
//}else{
//	global $docstbl;
//	global $prefix;
//}

include_once('class/document.php');
//$curcompany=unserialize($_SESSION['company']);
$doctype = isset($_GET['doctype']) ? $_GET['doctype'] : DOC_INVOICE;
$docnum = isset($_GET['docnum']) ? $_GET['docnum'] : 0;


$query = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND docnum='$docnum' AND doctype='$doctype'";
$result = DoQuery($query, "printdoc.php");
$line = mysql_fetch_array($result, MYSQL_NUM);
$idnum=$line[0];


if ($docnum ==0){
	print('<center>׳�׳� ׳§׳™׳™׳�</center>');
	exit;
}
$stdheader = <<<STDHEAD
<script type="text/javascript">function PrintWin() {window.open('printdoc.php?doctype=$doctype&docnum=$docnum&prefix=$prefix&print_win=1', 'PrintWin', 'width=800,height=600,scrollbar=yes');}</script>
STDHEAD;

$ln = 0;	// line number for multilines queries 
$lasttbl = '';	// last table in query 
$docref = 0;
$result = 0;
$line = array();
if($doctype == DOC_RECEIPT) {
	if($curcompany->receipt_template)
		$template = $curcompany->receipt_template;
	else
		$template = "templates/receipt.html";
}else if($doctype == DOC_INVRCPT) {
	if($curcompany->invoice_receipt_template)
		$template = $curcompany->invoice_receipt_template;
	else
		$template = "templates/invrcp.html";
}else	if($curcompany->doc_template)
		$template = $curcompany->doc_template;
	else
		$template = "templates/docs.html";


function isdate($dt) {
	list($y, $m, $d) = explode('-', $dt);
	if(!$m || !$d)
		return 0;
	return checkdate($m, $d, $y);
}
// getdoc 
$docy=new document();
$docy->num=$idnum;
$docy->getDocument();
//print_r($doc);

function SmallReplace($r) {
	//return 'bla';
	global $prefix,$serverpath;
	global $doctype, $docnum,$idnum;
	global $DocType, $paymenttype,$banksarr, $creditcompanies;
	global $docy,$compy;
	global $docstbl, $docdetailstbl,$chequestbl;
	global $ln, $lasttbl, $docref;
	global $result, $line;
	global $stdheader;
	//$dt = ($doctype > DOC_RECEIPT) ? DOC_INVOICE : $doctype;
	$dt=$doctype;
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
		$logo = $compy->logo;
		if($logo)
			return '<img src="'.$serverpath.'/img/logo/'.$logo.'"  height="100" />';//adam:'<img src="img/'.$logo.'">';
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
	}else if($p == 'docnum')
		return $docnum;
	else if($p == 'footer')
		if(($doctype==DOC_INVOICE) ||($doctype==DOC_PROFORMA)||($doctype==DOC_DELIVERY)||($doctype==DOC_PARCHACEORDER))
				return $compy->{$p}."<br /><br />חתימת הלקוח:__________________";
			else
				return $compy->{$p};
	else if(($p == 'header')||($p == 'companyname')||($p == 'web')||($p == 'regnum')) {
		return $compy->{$p};
	}
	else if ($p == 'address'){
		return $compy->address.', '.$compy->city." ".$compy->zip;
	}
	else if ($p=='phones'){
		return _('phone').": ".$compy->phone." <br /> "._("fax").": ".$compy->cellular;
	}
	else if($p=='docdet'){
		$detiales=$docy->docdetials;
		$str='';
		foreach ($detiales as $detial){
			$str.='<div class="row">';
			$str.='<div class="rdata">'.$detial->description.'</div>';
			$str.='<div class="rdata">'.$detial->qty.'</div>';
			$str.='<div class="rdata">'.$detial->unit_price.'</div>';
			$str.='<div class="rdata">'.$detial->price.'</div>';
			$str.='</div>';
		}
		return $str;
	}
	else if ($p=='rcpt'){
		$rcpt=$docy->rcptdetials;
		$str='';
		foreach ($rcpt as $detial){
			$str.='<div class="row">';
			$type=selectSql(array('id'=>$detial->type), 'paymentType',array('name'));
			$str.='<div class="rdata">'._($type[0][name]).'</div>';
			//$str.='<div class="rdata">'.$detial->creditcompany.'</div>';
			$str.='<div class="rdata">'.$detial->cheque_num.'</div>';
			$str.='<div class="rdata">'.$detial->bank.'</div>';
			$str.='<div class="rdata">'.$detial->branch.'</div>';
			$str.='<div class="rdata">'.$detial->cheque_acct.'</div>';
			$str.='<div class="rdata">'.$detial->cheque_date.'</div>';
			$str.='<div class="rdata">'.$detial->sum.'</div>';
			$str.='</div>';
		}
		return $str;
	}
	//else 
	list($frm, $fld) = explode(':', $p);
	if(!$frm)
		return '';
	if($frm=='docs'){
		if($fld=='issue_date'){
			return FormatDate($docy->issue_date,'mysql','dmyy');
		}else if($fld=='due_date'){
			//~docs:due_date~
			if(($doctype==DOC_INVOICE)||($doctype==DOC_PROFORMA)||($doctype==DOC_SALES)||($doctype==DOC_PARCHACEORDER))
				return "<td>מועד פרעון:</td><td>".FormatDate($docy->due_date,'mysql','dmyy')."</td>";
			else
				return "";
		}
		return $docy->{$fld};
	}
	return "$s";
	}


$file = fopen($template, "r");
if(!$file) {
	print "Unable to open: $template<br />\n";
	exit;
}
$found=false;
$bla='';
while(!feof($file)) {
	$str = fgets($file);
	$new = preg_replace_callback("/~[^\x20|^~]*~/", "SmallReplace", $str);
	if (substr_count($new,'</body')>=1) $found=false;
	//if(!$print_win==1)	print("$new");
	if ($found)	$bla.=$new;
	if (substr_count($new,'<body dir="rtl">')>=1) $found=true;
}
fclose($file);

$bla="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\">
<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	
	<!--<link rel=\"stylesheet\" type=\"text/css\" href=\"$path/style/documenet.css\" />-->
	<link rel=\"stylesheet\" type=\"text/css\" href=\"../style/documenet.css\" />
	<title>bla</title>
	<script type=\"text/javascript\" src='js/jquery.min.js'></script>
</head>

	<script type=\"text/javascript\">
	function printDoc(doctype,docnum,prefix){
			$('body').append('<div style=\"position: fixed;top: 14%;left: 40%;\"><img src=\"img/docgo.gif\" /><br />loadind please wait...</div>');
			   $.ajax({
				   type: \"GET\",
				   url: \"printdoc.php\",
				   data: {\"print_win\":1,\"prefix\":prefix,\"doctype\":doctype,\"docnum\":docnum}
				  // dataType:'html'
				 }).done(function( msg ) {
					 var url='tmp/'+prefix+'.pdf';
					 window.open(url);
				
					location.reload(true);
					 
				   //alert( \"Data Saved:\" + msg );
				  // window.close();
				 });
	}
	
	</script>
<body dir=\"rtl\">".$bla."</body></html>";

//echo $bla1;
$bla1="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\">
<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	
	<link rel=\"stylesheet\" type=\"text/css\" href=\"style/documenet.css\" />
	<title>bla</title>
</head>
<body dir=\"rtl\">".$bla."</body></html>";



if($print_win==1) {
	$query = "SELECT printed FROM $docstbl WHERE prefix='$prefix' AND doctype='$dt' AND docnum='$docnum'";
	$result = DoQuery($query, __FILE__.": ".__LINE__);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$printed = $line[0];
	$printed++;
	
	$query = "UPDATE $docstbl SET printed='$printed' ";
	$query .= "WHERE prefix='$prefix' AND doctype='$doctype' AND docnum='$docnum'";
	//print $myfile;
	DoQuery($query, "printdoc.php");
	$dom=false;
	if($dom){
		require_once("module/domPdf/dompdf_config.inc.php");
		$dompdf = new DOMPDF();
		$dompdf->load_html($bla1);
		$dompdf->render();
		$pdfoutput = $dompdf->output(); 
		$filename = "$path/tmp/$prefix.pdf"; 
		$fp = fopen($filename, "a"); 
		fwrite($fp, $pdfoutput); 
		fclose($fp); 
	}else{
		$myFile = "$path/tmp/$prefix.html";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $bla1);
		fclose($fh);
		$myfile="$serverpath/tmp/$prefix.html";
		
		global $wkhtmltopdfstr;
		$os_string = php_uname('s');
		
		if (strstr(strtoupper($os_string), 'WIN')){
			$a="\"\"$wkhtmltopdfstr\" \"$myFile\" \"$path/tmp/$prefix.pdf\"\"";
		}else{
			$a="$wkhtmltopdfstr \"$myFile\" \"$path/tmp/$prefix.pdf\"";
		}
		shell_exec($a);
		//print "<meta http-equiv=\"refresh\" content=\"0;url=tmp/$prefix.pdf\"> ";
	}

}else {
	print $bla1;
	//createForm($bla,'','',780,'','',1,getHelp());
	print "<div class=\"printme\" style=\"text-align:center;\">\n";
	$l = _("Print");
	//print "<form><input type=\"button\" value=\"$l\" ";
	//print "onclick=\"PrintWin()\">\n";
	//print "</form>\n";
	//$doctype = isset($_GET['doctype']) ? $_GET['doctype'] : DOC_INVOICE;
//$docnum = isset($_GET['docnum']) ? $_GET['docnum'] : 0;
	$l="<img src='img/btnprint.png' alt='' />";
	//$href="printdoc.php?docnum=$docnum&amp;doctype=$doctype&amp;prefix=$prefix&amp;print_win=1";
	//print newWindow($l, $href,20,20);
	print "<a href='javascript:printDoc(\"$doctype\",\"$docnum\",\"$prefix\");'>$l</a>";
	print "</div>\n";	
}
?>