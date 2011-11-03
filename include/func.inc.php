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
	print "<H1>$str</H1>\n";
	print "<H2>לחץ על חזור ותקן את השגיאה</H2>\n";
	print "<form><input type=\"button\" value=\"חזור\" onclick=\"history.back()\"></form>\n";
}
function newWindow($text,$href,$width,$height){
	$text= "<a href=\"$href\" onClick=\"window.open('$href','newAccount','width=$width,height=$height,menubar=no,status=no,directories=no,toolbar=no,location=no,resizable=no'); return false;\" target=\"_blank\"\">$text</a>\n";
	return $text;
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

function RecomendFirefox() {
	global $lang;
	global $dir;
	global $id;
	
	$str = '';
	/* firefox affiliate code */
	$str = "<table border=\"0\" dir=\"$dir\"><tr><td>\n";
	$str .= "<a href='http://www.mozilla.org/firefox?WT.mc_id=aff_en02&amp;WT.mc_ev=click'><img src='img/firefox.png' alt='Firefox Download Button' border='0' /></a>\n";
	$str .= "</td><td valign=\"top\">\n";
	$l = _("We advise to use this software with Firefox browser");
	$str .= "$l<br />\n";
	$l = _("To install press the logo on the left");
	$str .= "$l \n";
	$l = _("For more information");
	$l1 = _("Click here");
	$str .= "$l <a href=\"?id=firefox\">$l1</a>\n";
	$str .= "</td></tr></table>\n";
	
	return $str;

}
function createForm($text,$haeder,$sClass,$width=200,$height=480,$logo=null){
	if(isset($logo))$haeder="<img src=\"$logo\" alt=\"$logo\" />".$haeder;
	if(!isset($height))$height=480;
	$newform='
	<div class="form '.$sClass.'" style="width:'.$width.'px;">
		<div class="ftr"><img src="img/ftr.png" alt="formright"  /></div>
		<div class="ftc" style="width:'.($width-30).'px;">'.$haeder.'</div>
		<div class="ftl"><img src="img/ftl.png" alt="formleft" /></div>
		
		<div class="fcr" style="height:'.($height-110).'px;"></div>
		<div class="fcc" style="width:'.($width-40).'px;height:'.($height-140).'px;">
			'.$text.'
		</div>
		<div class="fcl" style="height:'.($height-110).'px;"></div>

		<div class="fbr"><img src="img/fbr.png" alt="formright" /></div>
		<div class="fbc" style="width:'.($width-30).'px;"></div>
		<div class="fbl"><img src="img/fbl.png" alt="formleft" /></div>
	</div>';

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
//		print "<h3>׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¢׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ³ֲ·׳³ֲ³ײ²ֲ³׳²ֲ³׳’ג‚¬ג€� ׳³ֲ³ײ²ֲ³׳³ג€™׳’ג‚¬ן¿½ײ³ג€”׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ»׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¢ ׳³ֲ³ײ²ֲ³׳³ג€™׳’ג€�ֲ¬ײ³ֲ·׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¨׳³ֲ³ײ²ֲ³׳²ֲ»׳�ֲ¿ֲ½׳³ֲ³ײ²ֲ³׳³ג€™׳’ג‚¬ן¿½ײ²ֲ¢׳³ֲ³ײ²ֲ³׳²ֲ²ײ²ֲ¡</h3>";
		$text.= "<form name=\"acct\" action=\"?module=acctadmin&amp;action=updateacct&amp;num=$num\" method=\"post\">\n";
	}
	else {
		$l = _("New account");
		if (!$smallprint){
			$text.= "<a href=\"javascript:editshow();\" id=\"b1\" class=\"btn\">$l</a>\n";
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
	$text.= "</tr><tr>\n";
	if($type < 2) {
		$l = _("Payment terms");
		$text.= "<td>$l: </td>\n";
		$l = _("Add + for current +");
		$text.= "<td colspan=\"2\"><input type=\"text\" name=\"pay_terms\" size=\"5\" value=\"$plus$pay_terms\" />$l</td>\n";
		$text.= "</tr><tr>\n";
	}

	if(($type == INCOME) || ($type == OUTCOME) || ($type == ASSETS)) {
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
		$l = _("Recocnized VAT");
		$text.= "<td>$l: </td>\n";
		$text.= "<td>\n";
		$text.= PrintVatPercent($src_tax);
		$text.= "<input type=\"text\" name='src_tax1' value=\"$src_tax\" style=\"display:none\" size=5 />\n";
		$text.= "</td></tr><tr>\n";
	}
	else if($type == INCOME) {
		$l = _("Recocnized VAT");
		$text.= "<td>$l: </td>\n";
		$text.= "<td>\n";
		$text.= IncomeVatPercent($src_tax);
		$text.= "</td></tr><tr>\n";
	}
	if(($type < 2) || ($type == 10)) {
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
//		print "</TR><TR>\n";
		$l = _("Department");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"department\" value=\"$department\" size=\"15\" /></td>\n";
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
//		print "</TR><TR>\n";
		$l = _("Email");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"email\" value=\"$email\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("Phone");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"phone\" value=\"$phone\" size=\"15\" /></td>\n";
//		print "</TR><TR>\n";
		$l = _("Direct phone");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"dir_phone\" value=\"$dir_phone\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("Fax");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"fax\" value=\"$fax\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("Web site");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"web\" value=\"$web\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("Address");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"address\" value=\"$address\" size=\"15\" /></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("City");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"city\" value=\"$city\" size=\"10\" /></td>\n";
//		print "</TR><TR>\n";
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
		$text.="<a href='javascript:document.acct.submit();' class='btn'>$l</a>";
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