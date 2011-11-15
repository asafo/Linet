<?PHP
/*
 | Auxiliary functions for freelance
 */
function PrintCustomerSelect($defaccount) {	
	$text="<input type=\"text\"  id=\"acc\" class=\"cat_num\" name=\"account\" onblur=\"SetCustomer()\" />\n";//name=\"cat_num[]\"
	$text.='<script type="text/javascript">$(document).ready(function() {$( "#acc" ).autocomplete({source: \'index.php?action=lister&data=acc&type='.CUSTOMER.'"&jsoncallback=?\'});});</script>';
	return $text;
}
function PrintSupplierSelect($defaccount) {	
	$text="<input type=\"text\"  id=\"supplier\" class=\"\" name=\"supplier\" onchange=\"ochange()\" />\n";//name=\"cat_num[]\"
	$text.='<script type="text/javascript">$(document).ready(function() {$( "#supplier" ).autocomplete({source: \'index.php?action=lister&data=acc&type='.SUPPLIER.'&jsoncallback=?\'});});</script>';
	return $text;
}
function PrintSelect($defaccount,$type){
	$text="<input type=\"text\"  id=\"outcome\" class=\"\" name=\"outcome\" onchange=\"ochange()\" />\n";//name=\"cat_num[]\"
	$text.='<script type="text/javascript">$(document).ready(function() {$( "#outcome" ).autocomplete({source: \'index.php?action=lister&data=acc&type='.$type.'&jsoncallback=?\'});});</script>';
	return $text;
}
function ErrorReport($str) {
	print "<div style=\"display: inline-block;\"><H1>$str</H1>\n";
	$l=_("To correct the mistake hit back");
	print "<H2>$l</H2>\n";
	$l=_("Back");
	print "<form><input type=\"button\" value=\"$l\" onclick=\"history.back()\"></form></div>\n";
}
function newWindow($text,$href,$width,$height,$title=0,$class=0){
	if(!$title)$title=_("New");
	if(!$class)$class='';else $class='class="'.$class.'"';
	$text= "<a href=\"$href\" $class onClick=\"window.open('$href','$title','width=$width,height=$height,menubar=no,status=no,directories=no,toolbar=no,location=no,resizable=no'); return false;\" target=\"_blank\"\">$text</a>\n";
	return $text;
}
function printHtml(){
	print '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<link rel="stylesheet" type="text/css" href="style/mcalendar.css" />
	<link rel="stylesheet" href="js/jquery-ui-1.8.13.custom.css" type="text/css" />
	<link rel="stylesheet" href="js/jquery.tablesorter.min.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="style/linet.css" />
	<script type="text/javascript" src=\'js/jquery.min.js\'></script>
	<script type="text/javascript" src=\'js/jquery.ui.custom.min.js\'></script>

	<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script> 
	<script type="text/javascript" src="js/java.js"></script>

	
	
	
</head>
	<body dir="rtl">
	
	
	
	
	
	';
	
}
/*
 | GetPost
 | Get a value from $_POST array escaping special HTML characters to prevent XSS
 */
function GetPost($n) {
//	$search = array('&quot;', '&#039;', '&lt;', '&gt;');
//	$rep = array("\"", '\'', '<', '>');
	$str = $_POST[$n];
	//$str = stripslashes($str);
//	$str = str_replace($search, $rep, $str);
//	$str = str_replace('&amp;', '&', $str);
	return  mysql_real_escape_string($str);//htmlspecialchars($str, ENT_QUOTES);
}
function GetGet($n) {
//	$search = array('&quot;', '&#039;', '&lt;', '&gt;');
//	$rep = array("\"", '\'', '<', '>');
	$str = $_GET[$n];
	$str = stripslashes($str);
//	$str = str_replace($search, $rep, $str);
//	$str = str_replace('&amp;', '&', $str);
	return  mysql_real_escape_string($str);//htmlspecialchars($str, ENT_QUOTES);
}
function GetRequest($n) {
//	$search = array('&quot;', '&#039;', '&lt;', '&gt;');
//	$rep = array("\"", '\'', '<', '>');
	$str = $_REQUEST[$n];
	$str = stripslashes($str);
//	$str = str_replace($search, $rep, $str);
//	$str = str_replace('&amp;', '&', $str);
	return mysql_real_escape_string($str);//htmlspecialchars($str, ENT_QUOTES);
}
/*
 | Return 0 if date is valid
 */
function ValidDate($dt) {
	list($d, $m, $y) = explode('-', $dt);
	return (checkdate($m, $d, $y) ? 0 : 1);
}

function FormatDate($str, $intype, $outtype) {
	if($intype == 'mysql') {
		list($year, $month, $day) = split("-", $str);
	}
	else if($intype == 'dmy') {
			list($day, $month, $year) = split("[/.-]", $str);
	}
	else if($intype == 'mdy') {
		list($month, $day, $year) = split("[/.-]", $str);
	}

	if($year < 2000) {
		if($year > 70)		/* year 1970 used as the pivot year */
			$year += 1900;
		else
			$year += 2000;
	}	
	if($outtype == 'mysql') {
		return "$year-$month-$day";
	}
	else if($outtype == 'dmy') {
		return "$day-$month-$year";
	}
	else if($outtype == 'mdy') {
		return "$month-$day-$year";
	}
}

function DoQuery($query, $debugstr) {
	global $sql_link;

	if($sql_link)
		$result = mysql_query($query, $sql_link);
	else
		$result = mysql_query($query);
	if(!$result) {
		print "$debugstr Query: $query<br />\n";
		echo mysql_error();
		exit;
	}
	return $result;
}
function delCompany($prefix){
	DoQuery("DELETE FROM companies WHERE prefix = '$prefix'", $debugstr);
	DoQuery("DELETE FROM accounts WHERE prefix = '$prefix'", $debugstr);
	DoQuery("DELETE FROM docs WHERE prefix = '$prefix'", $debugstr);
	DoQuery("DELETE FROM docdetails WHERE prefix = '$prefix'", $debugstr);
	DoQuery("DELETE FROM transactions WHERE prefix = '$prefix'", $debugstr);
	DoQuery("DELETE FROM bankbook WHERE prefix = '$prefix'", $debugstr);
	DoQuery("DELETE FROM items WHERE prefix = '$prefix'", $debugstr);
}
function GetURI() {
	$server = $_SERVER['SERVER_NAME'];
	$uri = $_SERVER['REQUEST_URI'];
	$uriarr = split('/', $uri);
	
	$uri = "http://$server";
	$i = count($uriarr);
	foreach($uriarr as $val) {
		if($i > 1)
			$uri .= "$val/";
		$i--;
	}
	return $uri;
}

function GetNextTransaction() {
	global $transactionstbl;
	global $prefix;

	$query = "SELECT MAX(num) FROM $transactionstbl WHERE prefix='$prefix'";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$n = $line[0];
	$n = $n + 1;
	if($n == 0)
		$n = 1;
	return $n;
}

function Transaction($tnum, $type, $acct, $ref1, $ref2, $date, $details, $sum) {
	global $transactionstbl;
	global $prefix;
		
	if($tnum == 0) {	/* new transaction, get number */
		$tnum = GetNextTransaction();
	}
	if($sum == 0)
		return $tnum;		/* special case */

	$date = FormatDate($date, "dmy", "mysql");
	$query = "INSERT INTO $transactionstbl VALUES ('$prefix', '$tnum', '$type', '$acct', '$ref1', '$ref2', '$date', '$details', '$sum', '0')";
	//print $query;
	$result = mysql_query($query);
	if(!$result) {
		echo "Transaction $tnum write error: <br />\n";
		echo mysql_error();
		exit;
	}
	return $tnum;
}

function FromHtml($str) {
	$str = html_entity_decode($str);
	$str = str_replace('&#039;', '\'', $str);
	return addslashes($str);
}

function Conv1255($filename) {
	$contents = file_get_contents($filename);
	$contents1255 = iconv("utf-8", "windows-1255", $contents);
	$fd = fopen($filename, "wb");
	fwrite($fd, $contents1255);
	fclose($fd);
}

function NewRow() {
	global $EvenLine;
	
	if($EvenLine)
		print "<tr class=\"otherline\">\n";
	else
		print "<tr>\n";
	$EvenLine = !$EvenLine;
}
function getHelp(){
	global $module;
	$type=GetGet('type');
	$action=GetGet('action');
	$typeo=GetGet('targetdoc');
	$opt=GetGet('opt');
	$step=GetGet('step');
	$help=$module;
	if(($help=='acctadmin') ||($help=='contact')) $help.=$type;
	if(($help=='login')||($help=='contact')) $help.=$action;
	elseif($help=='docsadmin') $help.=$typeo;
	elseif(($help=='outcome')||($help=='payment')) $help.=$opt;
	elseif($help=='backup') $help.=$step;
	
	return "?module=redirect&amp;dest=$help";
	
}
function RecomendFirefox() {
	global $lang;
	global $dir;
	global $id;
	
	$str = '<div class="firefox">';
	/* firefox affiliate code */
	$str .= "<table border=\"0\" dir=\"$dir\"><tr><td>\n";
	$str .= "<a href='http://www.mozilla.org/firefox?WT.mc_id=aff_en02&amp;WT.mc_ev=click'><img src='img/logo_firefox.png' alt='Firefox Download Button' border='0' /></a>\n";
	$str .= "</td><td valign=\"top\">\n";
	$l = _("We advise to use this software with Firefox browser");
	$str .= "$l<br />\n";
	$l = _("To install press the logo on the left");
	//$str .= "$l \n";
	//$l = _("For more information");
	//$l1 = _("Click here");
	//$str .= "$l <a href=\"?id=firefox\">$l1</a>\n";
	$str .= "$l</td></tr></table>\n</div>";
	
	return $str;

}
function isocDiv(){
	
	$text='<div class="isoc">
		<table cellpadding="13">
			<tr>
				<td>
					'._('Linet accounting Project is<br /> suported by the ISOC-IL').'
				</td>
				<td>
					<a href="http://www.isoc.org.il"><img src="img/logo_isoc.png" alt="isoc logo" /></a>
				</td>
			</tr>
		</table>
		</div>';
	return $text;
}
function osiDiv(){
	
	$text='<div class="osi">
					<img src="img/logo_osi2.png" alt="logo osi2" /><img src="img/logo_osi.png" alt="osi logo" />
		</div>';
	return $text;
}
function createForm($text,$haeder,$sClass='',$width=200,$height=null,$logo=null,$back=null,$help=null){
	if(isset($logo))$logo="<img src=\"$logo\" alt=\"$logo\" />";else $logo='';
	if(isset($back)){
		$l=_("Back");
		$back='<div class="formback"><a href="javascript:history.go(-1)">'.$l.'&nbsp;<img src="img/icon_back.png" alt="Icon back" /></a></div>';
	}else 	
		$back='';
	if (isset($help)){
		$l=_("Help");
		$help='<div class="formhelp"><a class="help" target="_blank" href="'.$help.'"><img src="img/icon_help.png" alt="Icon help" /><p>'.$l.'</p></a></div>';
	}else{
		$help='';
	}
	if(!isset($height))$height='';
	$newform='
	<table class="form '.$sClass.'" style="width:'.$width.'px;">
		<tr>
			<td class="ftr"><img src="img/ftr.png" alt="formright"  /></td>
			<td class="ftc"><div class="formtitle">'.$logo.'<p>'.$haeder.'</p></div>'.$back.$help.'</td>
			<td class="ftl"><img src="img/ftl.png" alt="formleft" /></td>
		</tr>
		<tr>
			<td class="fcr"></td>
			<td class="fcc" style="width:'.($width-40).'px;height:'.($height-140).'px;">
				'.$text.'
			</td>
			<td class="fcl"></td>
		</tr>
		<tr>
		<td class="fbr"><img src="img/fbr.png" alt="formright" /></td>
		<td class="fbc"></td>
		<td class="fbl"><img src="img/fbl.png" alt="formleft" /></td>
		</tr>
	</table>
	
	
	';

	print $newform;
}



function EditAcct($num, $type,$smallprint=false) {
	global $AcctType;	/* defined in config.inc.php */
	global $accountstbl;
	global $prefix;
	global $RetModule;
	global $arr6111;
	global $dir;

	if($num) {	
		$query = "SELECT * FROM $accountstbl WHERE num='$num' AND prefix='$prefix'";
		$result = DoQuery($query, "EditAcct");
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$type = $line['type'];
		$pay_terms = $line['pay_terms'];
		if($pay_terms < 0) {
			$pay_terms *= -1;
			$plus = '+';
		}
		else
			$plus = '';
		$id6111 = $line['id6111'];
		$src_tax = $line['src_tax'];
		$src_date = FormatDate($line['src_date'], 'mysql', 'dmy');
		$company = $line['company'];
		$contact = stripslashes($line['contact']);
		$department = stripslashes($line['department']);
		$vatnum = $line['vatnum'];
		$email = $line['email'];
		$phone = $line['phone'];
		$dir_phone = $line['dir_phone'];
		$cellular = $line['cellular'];
		$fax = $line['fax'];
		$web = $line['web'];
		$address = stripslashes($line['address']);
		$city = stripslashes($line['city']);
		$zip = $line['zip'];
		$comments = stripslashes($line['comments']);
	}
	if($num) {
		$haeder = _("Edit account details");
		//print "<h3>$l</h3>\n";
//		print "<h3>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ³ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ³׳³ג€™׳’ג€�ֲ¬׳’ג‚¬ן¿½ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ³׳’ג‚¬ג€�׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ»׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¢ ׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¬׳²ֲ³ײ²ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ»׳³ן¿½ײ²ֲ¿ײ²ֲ½׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ³׳’ג‚¬ג„¢׳³ג€™׳’ג€�ֲ¬׳�ֲ¿ֲ½׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ³׳³ֲ²ײ²ֲ²׳²ֲ²ײ²ֲ¡</h3>";
		$text.= "<form name=\"acct\" action=\"?module=acctadmin&amp;action=updateacct&amp;num=$num\" method=\"post\">\n";
	}
	else {
		$l = _("New account");
		if (!$smallprint){
			$text.= "<a href=\"javascript:editshow();\" id=\"b1\" class=\"btnsmall\">$l</a>\n";
			$text.= "<div id=\"editformdiv\" style=\"display:none\">\n";
		}else{
			$text.= "<div id=\"editformdiv\">\n";
		}
		if($RetModule) {
			if($RetModule == 'docsadmin')
				$targetdoc = $_GET['targetdoc'];
			$text.= "<form name=\"acct\" action=\"?module=acctadmin&amp;action=newacct&amp;ret=$RetModule&targetdoc=$targetdoc\" method=\"post\">\n";
		}
		else
			$text.= "<form name=\"acct\" action=\"?module=acctadmin&amp;action=newacct\" method=\"post\">\n";
	}
	$text.= "<table dir=\"$dir\" border=\"0\" class=\"formtbl\" width=\"100%\">\n";
	$text.= "<tr>\n";
	$l = _("Account type");
	$text.= "<td>$l:</td>\n";
	$text.= "<td>\n";
	$s = stripslashes($AcctType[$type]);
	$text.= "<input type=\"hidden\" name=\"type\" value=\"$type\" /><b>$s</b>\n";
	$text.= "</td></tr>\n";
	$text.= "<tr>\n";
	$l = _("Account name");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"company\" value=\"$company\" size=\"15\" /></td>\n";
	//$text.= "</tr><tr>\n";
	if($type < 2) {
		
	}

	if(($type == INCOME) || ($type == OUTCOME) || ($type == ASSETS)) {
		$text.= "</tr><tr>\n";
		$l = _("6111 clause");
		$text.= "<td>$l: </td>\n";
		$text.= "<td>\n";
		$text.= Print6111id($id6111);
		$text.= "</td>\n";
/*		print "<td><input type=\"text\" name=\"id6111\" value=\"$id6111\" size=\"5\" onblur=\"Set6111()\">\n";
		$details6111 = $arr6111[$id6111];
		print "<input type=\"text\" name=\"details6111\" value=\"$details6111\" size=\"15\">\n";
		print "</td>\n"; */
		$text.= "</tr><tr>\n";
	}
	if(($type == OUTCOME) || ($type == ASSETS)) {
		$text.= "</tr><tr>\n";
		$l = _("Recocnized VAT");
		$text.= "<td>$l: </td>\n";
		$text.= "<td>\n";
		$text.= PrintVatPercent($src_tax);
		$text.= "<input type=\"text\" name='src_tax1' value=\"$src_tax\" style=\"display:none\" size=5 />\n";
		$text.= "</td></tr><tr>\n";
	}
	else if($type == INCOME) {
		$text.= "</tr><tr>\n";
		$l = _("Recocnized VAT");
		$text.= "<td>$l: </td>\n";
		$text.= "<td>\n";
		$text.= IncomeVatPercent($src_tax);
		$text.= "</td></tr><tr>\n";
	}
	if(($type < 2) || ($type == 10)) {
		
		$l = _("Phone");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"phone\" value=\"$phone\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
	
		if($type == BANKS) {
			$l = _("Account number");
			$text.= "<td>$l: </td>\n";
		}
		else {
			$l = _("Registration number");
			$text.= "<td>$l: </td>\n";
		}
		$text.= "<td><input type=\"text\" name=\"vatnum\" value=\"$vatnum\" size=\"15\" /></td>\n";
		
		$l = _("Fax");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"fax\" value=\"$fax\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		
		if($type == SUPPLIER) {
			$l = _("Source clearing");
			$text.= "<td>$l: </td>\n";
			$text.= "<td><input type=\"text\" name=\"src_tax\" size=5 value=\"$src_tax\" /> %</td>\n";
			$text.= "</tr><tr>\n";
			$l = _("Valid date");
			$text.= "<td>$l: </td>\n";
			$text.= "<td><input type=\"text\" name=\"src_date\" size=8 value=\"$src_date\" /></td>\n";
			$text.= "</tr><tr>\n";
		}

		$l = _("Contact");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"contact\" value=\"$contact\" size=\"15\" /></td>\n";
		
		$l = _("Direct phone");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"dir_phone\" value=\"$dir_phone\" size=\"15\" /></td>\n";
//		print "</TR><TR>\n";
		
		$text.= "</tr><tr>\n";
		
//		print "</TR><TR>\n";
		$l = _("Email");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"email\" value=\"$email\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";

//		print "</TR><TR>\n";
		$l = _("Department");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"department\" value=\"$department\" size=\"15\" /></td>\n";
		
		//adam:
		
		//$text.= "</tr><tr>\n";
		
		//$text.= "</tr><tr>\n";
		$l = _("Address");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"address\" value=\"$address\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		
		$l = _("Payment terms");
		$text.= "<td>$l: </td>\n";
		$l = _("Add + for current +");
		$text.= "<td><input type=\"text\" name=\"pay_terms\" size=\"5\" value=\"$plus$pay_terms\" />$l</td>\n";
		$l = _("City");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"city\" value=\"$city\" size=\"10\" /></td>\n";
		$text.= "</tr><tr>\n";
		
		$l = _("Web site");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"web\" value=\"$web\" size=\"15\" /></td>\n";
		$l = _("Zip");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"zip\" value=\"$zip\" size=\"5\" /></td>\n";
		$text.= "</tr><tr>\n";
	}
	$l = _("Comments");
	$text.= "<td valign=\"top\">$l: </td>\n";
	$text.= "<td colspan=\"3\"><textarea name=\"comments\" rows=\"3\" cols=\"40\">$comments</textarea></td>\n";
	$text.= "</tr><tr><td colspan=\"5\" align=\"center\">";
	$l = _("Submit");
	if (!$smallprint){
		$text.="<a href='javascript:document.acct.submit();' class='btnaction'>$l</a>";
	}else {
		$text.="<a href='javascript:document.acct.submit(); window.close();' class='btn'>$l</a>";		
	}
	$text.= "</table>\n";
	$text.= "</form>\n";
	if(!$num)
		$text.= "</div>\n";
	return $text;
}

function maxSql($cond,$max,$tablename){
	$tablename=sqlText($tablename);
	$max=sqlText($max);
	foreach($cond as $key=>$value){
		$value="'".sqlText($value)."'";
		if ($key=='password') $value="PASSWORD(".$value.")";
		$con.="(".$key."=".$value.") AND";
	}
	$con=substr($con,0,-3);
	$sql = "SELECT MAX($max) FROM $tablename WHERE $con";
	//print $sql;
	$result = mysql_query($sql);
	if(!$result) {
		echo mysql_error();
		exit;
	} else {
		$line = mysql_fetch_array($result, MYSQL_NUM);
		//print_r($line);
		$a=-1;
		$a=$line[0];
		return $a+1;
	}
}
function inseretSql($array,$tablename){
	$tablename=sqlText($tablename);
	foreach ($array as &$value) $value="'".sqlText($value)."'";
	
	if (isset($array['password'])) $array['password']="PASSWORD(".$array['password'].")";
	$data=implode(",", $array);
	$fildes=implode(",", array_keys($array));
	$sql = "INSERT INTO $tablename ($fildes) ";
	$sql .= "VALUES ($data)";
	//print $sql."\n";
	$result = mysql_query($sql);
	if(!$result) {
		echo mysql_error();
		return false;
	} else {
		//$line = mysql_fetch_array($result, MYSQL_NUM);
		return true;
	}
}
function updateSql($cond,$array,$tablename){
	$tablename=sqlText($tablename);

	$con='';
	foreach($cond as $key=>$value){
		$value="'".sqlText($value)."'";
		if ($key=='password') $value="PASSWORD(".$value.")";
		$con.="(".$key."=".$value.") AND";
	}
	$con=substr($con,0,-3);
	$data='';
	foreach($array as $key=>$value){
		$value="'".sqlText($value)."'";
		if ($key=='password') $value="PASSWORD(".$value.")";
		$data.=$key."=".$value." ,";
	}
	$data=substr($data,0,-1);
	$sql = "UPDATE $tablename SET $data WHERE $con";
	//print $sql;
	$result = mysql_query($sql);
	if(!$result) {
		echo mysql_error();
		return true;
	} else {
		//$line = mysql_fetch_array($result, MYSQL_NUM);
		return true;
	}
}
function selectSql($cond,$tablename,$fields=NULL,$date=null,$sort=null){
	if  (is_null($fields)){
		$data='*';
	}else{
		foreach ($fields as &$value) $value=sqlText($value);
		$data=implode(",", $fields);
	}
	$tablename=sqlText($tablename);

	$con='';
	foreach($cond as $key=>$value){
		$value="'".sqlText($value)."'";
		if ($key=='password') $value="PASSWORD(".$value.")";
		$con.="(".$key."=".$value.") AND";
	}
	if  (!is_null($date)){
		$con.= " ((date>=DATE('".sqlText($date['min'])."')) AND (date<=DATE('".sqlText($date['max'])."'))) AND";
	}
	$con=substr($con,0,-3);
	if  (!is_null($sort)){
		//'ORDER BY `prefix` ASC'
		foreach ($sort as &$value) $value="'".sqlText($value)."'";
		$sorts=implode(" ASC,", $sort);
		$sorts= "ORDER BY ".$sorts.' ASC';
		$con.=$sorts;
	}
	$sql = "SELECT $data FROM $tablename WHERE $con";
	//print $sql;
	$result = mysql_query($sql);
	if(!$result) {
		echo mysql_error();
		return false;
	} else {
		$bla;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$bla[]=$row;
		}
		return $bla;
	}
}
function deleteSql($cond,$tablename){

	$con='';
	foreach($cond as $key=>$value){
		$value="'".sqlText($value)."'";
		if ($key=='password') $value="PASSWORD(".$value.")";
		$con.="(".$key."=".$value.") AND";
	}
	$con=substr($con,0,-3);
	$sql="DELETE FROM $tablename WHERE $con";
	//print $sql;
	$result = mysql_query($sql);
	if(!$result) {
		echo mysql_error();
		return false;
	} else {
		return true;
	}
}
function sqlText($str){
	$str=str_replace('*','',$str);
	return mysql_real_escape_string($str);
}

function listCol($tablename){
	$tablename=sqlText($tablename);
	$query = "SHOW columns FROM $tablename";
	$result = mysql_query($query);
	if(!$result) {
		echo "Table $tablename dosn't exsists <br />\n";
		echo mysql_error();
		exit;
	}
	$arr=array();
	$col=0;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$arr[$col]=$row;
		$col+=1;
	}
	return $arr;
}
?>