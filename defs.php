<?PHP
/*
 | Definitions module for Drorit
 | Written by: Ori Idan.
 */
 
global $superuser, $companiestbl, $logintbl;
global $lang, $dir;
$text='';
if($action == 'defsubmit') {
	//$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
	//$passwd = $_POST['passwd'];
	//$verpasswd = $_POST['verpasswd'];
	//$fullname = $_POST['fullname'];
	$prefix = $_POST['prefix'];
	$manager = htmlspecialchars($_POST['manager'], ENT_QUOTES);
	$companyname = htmlspecialchars($_POST['companyname'], ENT_QUOTES);

	if($prefix == '') {
		//ErrorReport(_("Prefix must be english only with no spaces"));
		$prefix=sha1(rand());
		
		//return;
	}
	if($companyname == '') {
		ErrorReport(_("Business name not specified"));

		return;
	}
	/*
	if($fullname == '') {
		ErrorReport(_("Full name not specified"));
		return;
	}
	if($email == '') {
		ErrorReport(_("No email entered"));
		return;
	}
	if($passwd == '') {
		ErrorReport(_("No password entered"));
		return;
	}	
	if($passwd != $verpasswd) {
		ErrorReport(_("Passwords are not equal"));
		return;
	}
	*/
	//print "were out of the dead zone<br />";
	//$query = "SELECT name,password FROM $logintbl WHERE name='$email'";
	/*
	$result = DoQuery($query, "defs.php");
	if(mysql_num_rows($result) == 0) {
		$query = "INSERT INTO $logintbl ";
	//	$hash = md5($email);
		$query .= "VALUES('$email', '$fullname', PASSWORD('$passwd'), NOW(), '', '')";
		$result = mysql_query($query);
		if(!$result) {
			return;
		}
		print "<br>\n";
		$l = _("Creating new user");
		print "<h3>$l</h3>\n";
	//	if(!$resend)
		$l = _("New user created succesfully");
		print "<h2>$l</h2>\n";
			print "<h2>׳³ג€�׳³ן¿½׳³ֲ©׳³ֳ—׳³ן¿½׳³ֲ© ׳³ג€�׳³ג€”׳³ג€�׳³ֲ© ׳³ֲ ׳³ג€¢׳³ֲ¦׳³ֲ¨ ׳³ג€˜׳³ג€�׳³ֲ¦׳³ן¿½׳³ג€”׳³ג€�.</h2>\n";
	}
	else {
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$password = $line['password'];
		if($password == 'demo') {	 this is a demo user, update it 
			$query = "UPDATE $logintbl SET password=PASSWORD('$passwd'), ";
			$query .= "lastlogin=NOW() WHERE name='$email'";
			DoQuery($query, __LINE__);
			$l = _("User password updated");
			$text.= "<h2>$l</h2>\n";
		}
	}*/

	/* Create new company */
	$companyname = htmlspecialchars($_POST['companyname'], ENT_QUOTES);
	$regnum = htmlspecialchars($_POST['regnum'], ENT_QUOTES);
	$address = htmlspecialchars($_POST['address'], ENT_QUOTES);
	$city = htmlspecialchars($_POST['city'], ENT_QUOTES);
	$zip = htmlspecialchars($_POST['zip'], ENT_QUOTES);
	$phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);
	$cellular = htmlspecialchars($_POST['cellular'], ENT_QUOTES);
	$web = htmlspecialchars($_POST['web'], ENT_QUOTES);
	$vat = htmlspecialchars($_POST['vat'], ENT_QUOTES);
	$bidi=GetPost('bidi');
	$credit=GetPost('credit');
	$credituser=GetPost('credituser');
	$creditpwd=GetPost('creditpwd');
	$creditallow=serialize($_POST['creditallow']);
	//$creditallow=unserialize($line['creditallow']);
	$vatrep = (int)$_POST['vatrep'];
	$query = "INSERT INTO $companiestbl (companyname, prefix, manager, regnum, address, city, zip, phone, cellular, web, vat, vatrep, bidi,credit,credituser,creditpwd,creditallow)";
	$query .= " VALUES('$companyname', '$prefix', '$manager', '$regnum', '$address', '$city', '$zip', '$phone', '$cellular', '$web', '$vat', '$vatrep', '$bidi', '$credit', '$credituser', '$creditpwd','$creditallow')";
	//print $query;
	DoQuery($query, "defs.php");
	global $curuser;
	$email=$curuser->name;
	$query = "INSERT INTO $permissionstbl VALUES ('$email', '$prefix', 0)";
	DoQuery($query, "defs.php");
	$l = _("Business added succesfully");
	$text.= "<h2>$l</h2>\n";
	print "<meta http-equiv=\"refresh\" content=\"0;url=?action=unsel\" /> ";
	return;	
}
if($action == 'register') {
	$hash = $_GET['hash'];
	$query = "SELECT fullname FROM $logintbl WHERE hash='$hash'";
	$result = DoQuery($query, "defs.php");
	if(mysql_num_rows($result) == 0) {
		_("Error registering to system");
		$text.= "<h1>$l</h1>\n";

		return;
	}
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$fullname = $line[0];
	$query = "UPDATE $logintbl SET hash='' WHERE hash='$hash'";
	DoQuery($query, "defs.php");
	$l = _("Hello");
	$text.= "<h1>$l $fullname</h1>\n";
	$l = _("Your registration to linet is completed");
	$text.= "<h1>$l</h1>\n";
	$l1 = _("Click");
	$l2 = _("here to connect");
	$text.= "<h2>$l1 <a href=\"?action=login\">$l</a></h2>\n";
	$text.= "<h2>׳³ן¿½׳³ג€”׳³ֲ¦\\׳³ג„¢ <a href=\"?action=login\">׳³ג€÷׳³ן¿½׳³ן¿½ ׳³ן¿½׳³ג€�׳³ֳ—׳³ג€”׳³ג€˜׳³ֲ¨׳³ג€¢׳³ֳ—</a></h2>\n";
	return;
}
if($action == 'defupdate') {
//	print_r($_POST);
	if($name == 'demo') {
		$text.= "<h1>׳³ן¿½׳³ֲ©׳³ֳ—׳³ן¿½׳³ֲ© ׳³ג€�׳³ג€¢׳³ג€™׳³ן¿½׳³ג€� ׳³ן¿½׳³ג„¢׳³ֲ ׳³ג€¢ ׳³ֲ¨׳³ֲ©׳³ן¿½׳³ג„¢ ׳³ן¿½׳³ֲ¢׳³ג€�׳³ג€÷׳³ן¿½ ׳³ֲ ׳³ֳ—׳³ג€¢׳³ֲ ׳³ג„¢׳³ן¿½</h1>\n";
		return;
	}
	$companyname = htmlspecialchars($_POST['companyname'], ENT_QUOTES);
	$manager = htmlspecialchars($_POST['manager'], ENT_QUOTES);
	$regnum = htmlspecialchars($_POST['regnum'], ENT_QUOTES);
	//$logo = htmlspecialchars($_POST['logo'], ENT_QUOTES);
	//$regnum = "511923740";//adam ERR chek wtf?!
	if($regnum != $_POST['regnum']) {
		$text.= "Not equal <br>\n";
	}
	$address = htmlspecialchars($_POST['address'], ENT_QUOTES);
	$city = htmlspecialchars($_POST['city'], ENT_QUOTES);
	$zip = htmlspecialchars($_POST['zip'], ENT_QUOTES);
	$phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);
	$cellular = htmlspecialchars($_POST['cellular'], ENT_QUOTES);
	$web = htmlspecialchars($_POST['web'], ENT_QUOTES);
	$tax = (float)$_POST['tax'];
	$taxrep = (int)$_POST['taxrep'];
	$vat = (float)$_POST['vat'];
	$vatrep = (int)$_POST['vatrep'];
	$bidi = (int)$_POST['bidi'];
	$credit = (int)$_POST['credit'];
	$credituser = GetPost('credituser');
	$creditpwd = GetPost('creditpwd');
	$creditallow = serialize($_POST['creditallow']);
	$header = htmlspecialchars($_POST['header'], ENT_QUOTES);
	$footer = htmlspecialchars($_POST['footer'], ENT_QUOTES);

	$query = "UPDATE $companiestbl SET ";
	$query .= "companyname='$companyname', \n"; 
	$query .= "manager='$manager', \n";
	$query .= "regnum='$regnum', \n";
	$query .= "address='$address', \n";
	$query .= "city='$city', \n";
	$query .= "zip='$zip', \n";
	$query .= "phone='$phone', \n";
	$query .= "cellular='$cellular', \n";
	$query .= "tax='$tax', \n";
	$query .= "taxrep='$taxrep', \n";
	$query .= "vat='$vat', \n";
	$query .= "vatrep='$vatrep', \n";
	$query .= "bidi='$bidi', \n";
	$query .= "credit='$credit', \n";
	$query .= "credituser='$credituser', \n";
	$query .= "creditpwd='$creditpwd', \n";
	$query .= "creditallow='$creditallow' \n";
	$query .= "WHERE prefix='$prefix'";
/*	print "<div dir=\"ltr\">\n";
	$qstr = nl2br($query);
	print "Query: $qstr<br>\n";
	print "</div>\n"; */
	//print $query;
	$result = DoQuery($query, "defs.php");
	//after update need to reload company to session with all users
	$l = _("Details succesfully updated");
	$text.= "<h1>$l</h1>\n";
}


/* Get data from table */
$query = "SELECT * FROM $companiestbl WHERE prefix='$prefix'";
// print "Query: $query<br>\n";
$result = DoQuery($query, "defs.php");
$line = mysql_fetch_array($result, MYSQL_ASSOC);
if(!$line) {
	if(!$superuser) {
		$l = _("Can not execute this operation without logging in to system");
		ErrorReport("$l");
		return;
	}
	//$text.= "<form name=\"form1\" action=\"\" method=\"post\" enctype=\"multipart/form-data\" class=\"valform\">\n";
	$url="?module=defs&amp;action=defsubmit";
	//	print "<div class=\"caption_out\"><div class=\"caption\">";
	$haeder = _("Entering new business");
	//print "<h3>$l</h3>\n<br>\n";
}
else {
	$url="?module=defs&amp;action=defupdate";
	$companyname = $line['companyname'];
	$manager = $line['manager'];
	$regnum = $line['regnum'];
	$address = $line['address'];
	$city = $line['city'];
	$zip = $line['zip'];
	$phone = $line['phone'];
	$cellular = $line['cellular'];
	$tax = $line['tax'];
	$taxrep = $line['taxrep'];
	$vat = $line['vat'];
	$vatrep = $line['vatrep'];
	$bidi=$line['bidi'];
	$credit=$line['credit'];
	$credituser=$line['credituser'];
	$creditpwd=$line['creditpwd'];
	$creditallow=unserialize($line['creditallow']);

	$editdata = 1;
}
$text.= "<form id=\"defs\" name=\"defs\" action=\"$url\" method=\"post\" enctype=\"multipart/form-data\" class=\"valform\">\n";
if($taxrep == 0)
	$taxrep = 2;
// print "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">\n";
/*
if(!$editdata) {
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr><td>\n";
	$l = _("Email");
	$text.= "$l: </td>";
	$text.= "<td><input type=\"text\" name=\"email\" value=\"\" dir=\"ltr\" /></td>\n";
	$l = _("Password");
	$text.= "</tr><tr><td>$l: </td>\n";
	$text.= "<td><input type=\"password\" name=\"passwd\" value=\"\" /></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Password verify");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"password\" name=\"verpasswd\" value=\"\" /></td></tr>\n";
	$text.= "<tr>\n";
	$l = _("Full name");
	$text.= "<td>$l: </td>\n";
//	print "<td>׳³ֲ©׳³ן¿½ ׳³ן¿½׳³ן¿½׳³ן¿½: </td>\n";
	
	$text.= "<td><input type=\"text\" name=\"fullname\" value=\"\"></td></tr>\n";

	$text.= "</table>\n";
	$text.= "<br />\n";
}*/
if($editdata) {
	$haeder = _("Edit business details");
	//print "<h3>$l</h3>\n\n";
}
$text.= "<table dir=\"$dir\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
$l = _("General definitions");
$text.= "<td colspan=\"2\"><b>$l: </b></td>\n";
// print "<td colspan=\"2\"><b>׳³ג€�׳³ג€™׳³ג€�׳³ֲ¨׳³ג€¢׳³ֳ— ׳³ג€÷׳³ן¿½׳³ן¿½׳³ג„¢׳³ג€¢׳³ֳ—: </b></td>\n";
$text.= "</tr><tr>\n";
$l = _("Business name");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"companyname\" value=\"$companyname\" class=\"required\" minlength=\"2\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("Manager name");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"manager\" value=\"$manager\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("Address");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"address\" value=\"$address\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("City");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"city\" value=\"$city\" size=\"10\" />&nbsp;&nbsp;\n";
$l = _("Zip");
$text.= "$l: \n";
// print "׳³ן¿½׳³ג„¢׳³ֲ§׳³ג€¢׳³ג€�: \n";
$text.= "<input type=\"text\" name=\"zip\" value=\"$zip\" size=\"5\" class=\"number\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("Phone");
$text.= "<td>$l: </td>\n";
// print "<td>׳³ֻ�׳³ן¿½׳³ג‚×׳³ג€¢׳³ן¿½: </td>\n";
$text.= "<td><input type=\"text\" name=\"phone\" value=\"$phone\" class=\"number\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("Fax");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"cellular\" value=\"$cellular\" class=\"number\" /></td>\n";

$text.= "</tr>\n";
$text.= "</table>\n";
// print "<br>\n";
$text.= "<table dir=\"$dir\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
$l = _("Definitions for tax");
$text.= "<td colspan=\"2\"><br><b>$l: </b></td>\n";
$text.= "</tr><tr>\n";
$text.= "<td>\n";
$l = _("Registration number");
$text.= "$l: \n";
// print "׳³ן¿½׳³ֲ¡׳³ג‚×׳³ֲ¨ ׳³ֲ¢׳³ג€¢׳³ֲ¡׳³ֲ§: \n";
$text.= "<td><input type=\"text\" name=\"regnum\" value=\"$regnum\" size=\"8\" class=\"number\" maxlength=\"9\" /></td>\n";
$text.= "</tr><tr>\n";
$l = _("Accounting Type");
$text.= "<td>$l: </td>\n";
// print "<td> ׳³ג€�׳³ג„¢׳³ג€¢׳³ג€¢׳³ג€” ׳³ן¿½׳³ֲ§׳³ג€�׳³ן¿½׳³ג€¢׳³ֳ—: </td>\n";
$text.= "<td><select name=\"bidi\">\n";
$l = _("One Sided");
$text.= "<option value=\"1\">$l</option>\n";
$l = _("Double Sided");
if($bidi == 2)
	$text.= "<option value=\"2\" selected>$l</option>\n";
else
	$text.= "<option value=\"2\">$l</option>\n";
$text.= "</select></td>\n";
$text.= "</tr><tr>\n";
$l = _("Tax rep. period");
$text.= "<td>$l: </td>\n";
// print "<td> ׳³ג€�׳³ג„¢׳³ג€¢׳³ג€¢׳³ג€” ׳³ן¿½׳³ֲ§׳³ג€�׳³ן¿½׳³ג€¢׳³ֳ—: </td>\n";
$text.= "<td><select name=\"taxrep\">\n";
$l = _("Monthly");
$text.= "<option value=\"1\">$l</option>\n";
$l = _("BiMonthly");
if($taxrep == 2)
	$text.= "<option value=\"2\" selected>$l</option>\n";
else
	$text.= "<option value=\"2\">$l</option>\n";
$text.= "</select></td>\n";
$l = _("Tax percent");
$text.= "<td>$l: </td>\n";
// print "<td>׳³ן¿½׳³ֲ§׳³ג€�׳³ן¿½׳³ג€¢׳³ֳ— ׳³ן¿½׳³ֲ¡ ׳³ג€�׳³ג€÷׳³ֲ ׳³ֲ¡׳³ג€�: </td>\n";
$text.= "<td><input type=\"text\" name=\"tax\" value=\"$tax\" size=\"5\" class=\"number\" maxlength=\"8\" /></td>\n";
$text.= "</tr><tr>\n";
$text.= "<td>\n";
$l = _("VAT report");
$text.= "$l: </td>\n";
// print "׳³ג€�׳³ג„¢׳³ג€¢׳³ג€¢׳³ג€” ׳³ן¿½׳³ֲ¢\"׳³ן¿½: </td>\n";
$text.= "<td><select name=\"vatrep\">\n";
$l = _("Monthly");
$text.= "<option value=\"1\">$l</option>\n";
$text.= "<option value=\"2\" ";
if($vatrep == 2)
	$text.= "selected>";
else
	$text.= ">";
$l = _("BiMonthly");
$text.= "$l";
$text.= "</option>\n";
$text.= "</select></td>\n";
$l = _("VAT percent");
$text.= "<td>$l: </td>\n";
// print "<td>׳³ן¿½׳³ג€”׳³ג€¢׳³ג€“ ׳³ן¿½׳³ֲ¢\"׳³ן¿½: ";
$text.= "<td><input type=\"text\" name=\"vat\" value=\"$vat\" size=\"5\" class=\"number\" maxlength=\"8\" /></td>\n";
$text.= "</tr>\n";
$l = _("Credit card clearing");
$text.= "<tr><td colspan=\"4\"><b>$l</b></td></tr>";
$text.= "<tr><td>";
$l = _("Clearing House");
$text.="$l:</td><td>";
$creditclearing=array(
	0=>'None',
	1=>'EasyCard',
	2=>'Tranzila'
);
$text.="<select name=\"credit\">";
foreach($creditclearing as $key=>$value)
	if($credit==$key)
		$text.="<option value=\"$key\" selected>$value</option>";
	else
		$text.="<option value=\"$key\">$value</option>";
$text.="</select>";
$text.="</td><td></td><td></td></tr>";

$text.= "<tr class=\"trcredit\"><td>";
$l = _("User");
$text.="$l:</td><td><input type=\"text\" id=\"\" name=\"credituser\" value=\"$credituser\"/></td>";
$l = _("Password");
$text.="<td>$l:</td><td><input type=\"text\" name=\"creditpwd\" value=\"$creditpwd\" /></td></tr>";
global $credittype;
$l = _("Possible payment types");
$text.= "<tr class=\"trcredit\"><td colspan=\"4\"><b>$l</b></td></tr>";
foreach($credittype as $key=>$value){
	$i++;
	$checked='';
	$text.= "<tr class=\"trcredit\"><td>";
	if($creditallow[$i]=='on') $checked='checked';
	
	$text.="<input type=\"checkbox\" name=\"creditallow[$i]\" $checked /></td><td>$value</td>";
	$text.="<td colspan=\"2\"></td></tr>";
}

$text.= "<tr><td colspan=\"4\" align=\"center\">\n";
$l = _("Update");
//$text.= "<br><a href=\"javascript:document.form1.submit();\" class=\"btnaction\">$l</a>&nbsp;&nbsp;";
$text.="<input type=\"submit\" value=\"$l\" class='btnaction' />";	
// print "<input type=\"button\" onclick=\"parent.location='index.php?module=defs'\" value=\"׳³ג€˜׳³ֻ�׳³ן¿½ ׳³ֲ©׳³ג„¢׳³ֲ ׳³ג€¢׳³ג„¢׳³ג„¢׳³ן¿½\">\n";
$text.= "</td></tr>\n";
$text.= "</table>\n";
$text.= "</form>\n";
createForm($text,$haeder,"",750,'','img/icon_defs.png',1,getHelp());
//print "</div>\n";

?>