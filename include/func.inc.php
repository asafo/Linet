<?PHP
/*
 | Auxiliary functions for freelance
 */
function PrintCustomerSelect($defaccount) {	
	$text="<input type=\"text\"  id=\"acc\" class=\"cat_num\" name=\"account\" onblur=\"SetCustomer()\" />\n";//name=\"cat_num[]\"
	$text.='<script type="text/javascript">$(document).ready(function() {$( "#acc" ).autocomplete({source: \'index.php?action=lister&data=acc&type=0&jsoncallback=?\'});});</script>';
	return $text;
}
function ErrorReport($str) {
	print "<H1>$str</H1>\n";
	print "<H2>׳�׳—׳¥ ׳¢׳� ׳—׳–׳•׳¨ ׳•׳×׳§׳� ׳�׳× ׳”׳©׳’׳™׳�׳”</H2>\n";
	print "<form><input type=\"button\" value=\"׳—׳–׳•׳¨\" onclick=\"history.back()\"></form>\n";
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
	$str .= "<a href='http://www.mozilla.org/firefox?WT.mc_id=aff_en02&amp;WT.mc_ev=click'><img src='http://www.mozilla.org/contribute/buttons/110x32arrow_g.png' alt='Firefox Download Button' border='0' /></a>\n";
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
function createForm($text,$haeder,$sClass,$width=200){
$newform='
	<div class="form '.$sClass.'">
		<div class="up side" style="float:right;"></div>
		<div class="up" style="width:'.$width.'px; float:right;"></div>
		
		
		<div class="up side" style="clear: right; float:right;"><img src="img/formright.png" alt="formright"  /></div>
		<div style="
		background-image: url(img/formmenu.png);
		background-repeat: repeat-x;
		color: white;
		float: right;
		font-size: 12px;
		font-weight: bold; 
		height: 30px; 
		padding-top: 5px;
		text-align: center; 
		width:'.($width-12).'px;"
		>'.$haeder.'</div>
		<div class="up side"  style="float:right;"><img src="img/formleft.png" alt="formleft" /></div>
		
		<div class="up side" style="clear: right; float:right;"></div>
		<div class="cont" style="width:'.$width.'px; float:right;">
			'.$text.'
		</div>
	</div>';
	print $newform;
}



function EditAcct($num, $type) {
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
//		print "<h3>׳¢׳¨׳™׳›׳× ׳₪׳¨׳˜׳™ ׳›׳¨׳˜׳™׳¡</h3>";
		$text.= "<form name=\"acct\" action=\"?module=acctadmin&amp;action=updateacct&amp;num=$num\" method=\"post\">\n";
	}
	else {
		$l = _("New account");
		
		$text.= "<input type=\"button\" onClick=\"editshow()\" id=\"b1\" value=\"$l\">\n";
		$text.= "<div id=\"editformdiv\" style=\"display:none\">\n";
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
	$text.= "<input type=\"hidden\" name=\"type\" value=\"$type\"><b>$s</b>\n";
	$text.= "</td></tr>\n";
	$text.= "<tr>\n";
	$l = _("Account name");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"company\" value=\"$company\" size=\"15\"></td>\n";
	$text.= "</tr><tr>\n";
	if($type < 2) {
		$l = _("Payment terms");
		$text.= "<td>$l: </td>\n";
		$l = _("Add + for current +");
		$text.= "<td colspan=\"2\"><input type=\"text\" name=\"pay_terms\" size=\"5\" value=\"$plus$pay_terms\">$l</td>\n";
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
		$text.= "<input type=\"text\" name='src_tax1' value=\"$src_tax\" style=\"display:none\" size=5>\n";
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
			$text.= "<td><input type=text name=src_tax size=5 value=\"$src_tax\"> %</td>\n";
			$text.= "</tr><tr>\n";
			$l = _("Valid date");
			$text.= "<td>$l: </td>\n";
			$text.= "<td><input type=text name=src_date size=8 value=\"$src_date\"></td>\n";
			$text.= "</tr><tr>\n";
		}

		$l = _("Contact");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=text name=contact value=\"$contact\" size=\"15\"></td>\n";
//		print "</TR><TR>\n";
		$l = _("Department");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"department\" value=\"$department\" size=\"15\"></TD>\n";
		$text.= "</tr><tr>\n";
		if($type == BANKS) {
			$l = _("Account number");
			$text.= "<td>$l: </td>\n";
		}
		else {
			$l = _("Registration number");
			$text.= "<td>$l: </td>\n";
		}
		$text.= "<td><input type=\"text\" name=vatnum value=\"$vatnum\" size=\"15\"></TD>\n";
//		print "</TR><TR>\n";
		$l = _("Email");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"email\" value=\"$email\" size=\"15\"></TD>\n";
		$text.= "</tr><tr>\n";
		$l = _("Phone");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"phone\" value=\"$phone\" size=\"15\"></td>\n";
//		print "</TR><TR>\n";
		$l = _("Direct phone");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"dir_phone\" value=\"$dir_phone\" size=\"15\"></TD>\n";
		$text.= "</tr><tr>\n";
		$l = _("Fax");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"fax\" value=\"$fax\" size=\"15\"></td>\n";
		$text.= "</tr><tr>\n";
		$l = _("Web site");
		$text.= "<td>$l: </td>\n";
		$text.= "<TD><INPUT type=text name=web value=\"$web\" size=\"15\"></TD>\n";
		$text.= "</TR><TR>\n";
		$l = _("Address");
		$text.= "<td>$l: </td>\n";
		$text.= "<TD><INPUT type=text name=address value=\"$address\" size=\"15\"></TD>\n";
		$text.= "</TR><TR>\n";
		$l = _("City");
		$text.= "<td>$l: </td>\n";
		$text.= "<td><input type=\"text\" name=\"city\" value=\"$city\" size=\"10\">\n";
//		print "</TR><TR>\n";
		$l = _("Zip");
		$text.= "<TD>$l: </TD>\n";
		$text.= "<td><input type=\"text\" name=\"zip\" value=\"$zip\" size=\"5\"></td>\n";
		$text.= "</tr><tr>\n";
	}
	$l = _("Comments");
	$text.= "<td valign=top>$l: </td>\n";
	$text.= "<td colspan=\"3\"><textarea name=\"comments\" rows=\"3\" cols=\"40\">$comments</textarea></td>\n";
	$text.= "</tr><tr><td colspan=\"5\" align=\"center\">";
	$l = _("Submit");
	$text.= "<br /><input type=\"submit\" value=\"$l\"></td></tr>\n";
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
	print $sql;
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
	print $sql;
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
		foreach ($fields as &$value) $value="'".sqlText($value)."'";
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