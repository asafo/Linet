<?PHP
/*
 | Definitions module for Drorit
 | Written by: Ori Idan.
 */
 
global $superuser, $companiestbl, $logintbl;
global $lang, $dir;

if($action == 'defsubmit') {
	$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
	$passwd = $_POST['passwd'];
	$verpasswd = $_POST['verpasswd'];
	$fullname = $_POST['fullname'];
	$prefix = $_POST['prefix'];
	$manager = htmlspecialchars($_POST['manager'], ENT_QUOTES);
	$companyname = htmlspecialchars($_POST['companyname'], ENT_QUOTES);

	if($prefix == '') {
		ErrorReport(_("Prefix must be english only with no spaces"));
//		ErrorReport("יש להכניס קידומת חברה באנגלית ללא רווחים");
		return;
	}
	if($companyname == '') {
		ErrorReport(_("Business name not specified"));
//		ErrorReport("לא צוין שם עסק");
		return;
	}
	if($fullname == '') {
		ErrorReport(_("Full name not specified"));
//		ErrorReport("לא צוין שם מלא");
		return;
	}
	if($email == '') {
		ErrorReport(_("No email entered"));
//		ErrorReport("לא הוכנס דואר אלקטרוני");
		return;
	}
	if($passwd == '') {
		ErrorReport(_("No password entered"));
//		ErrorReport("לא הוכנסה סיסמה");
		return;
	}	
	if($passwd != $verpasswd) {
		ErrorReport(_("Passwords are not equal"));
//		ErrorReport("ססמאות אינן זהות");
		return;
	}

	$query = "SELECT name,password FROM $logintbl WHERE name='$email'";

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
			print "<h2>המשתמש החדש נוצר בהצלחה.</h2>\n";
	}
	else {
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$password = $line['password'];
		if($password == 'demo') {	/* this is a demo user, update it */
			$query = "UPDATE $logintbl SET password=PASSWORD('$passwd'), ";
			$query .= "lastlogin=NOW() WHERE name='$email'";
			DoQuery($query, __LINE__);
			print "<h2>";
			$l = _("User password updated");
			print "$l";
//			print "ססמת משתמש עודכנה";
			print "</h2>\n";
		}
	}

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
	$vatrep = (int)$_POST['vatrep'];
	$query = "INSERT INTO $companiestbl (companyname, prefix, manager, regnum, address, city, zip, phone, cellular, web, vat, vatrep)";
	$query .= " VALUES('$companyname', '$prefix', '$manager', '$regnum', '$address', '$city', '$zip', '$phone', '$cellular', '$web', '$vat', '$vatrep')";
	DoQuery($query, "defs.php");
	$query = "INSERT INTO $permissionstbl VALUES ('$email', '$prefix', 0)";
	DoQuery($query, "defs.php");
	$l = _("Business added succesfully");
	print "<h2>$l</h2>\n";
//	print "<h2>החברה נוספה בהצלחה</h2>\n";
	return;	
}
if($action == 'register') {
	$hash = $_GET['hash'];
	$query = "SELECT fullname FROM $logintbl WHERE hash='$hash'";
	$result = DoQuery($query, "defs.php");
	if(mysql_num_rows($result) == 0) {
		_("Error registering to system");
		print "<h1>$l</h1>\n";
//		print "<h1>תקלה בהרשמה למערכת</h1>\n";
		return;
	}
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$fullname = $line[0];
	$query = "UPDATE $logintbl SET hash='' WHERE hash='$hash'";
	DoQuery($query, "defs.php");
	$l = _("Hello");
	print "<h1>$l $fullname</h1>\n";
//	print "<h1>שלום $fullname</h1>\n";
	$l = _("Your registration to drorit is completed");
	print "<h1>$l</h1>\n";
//	print "<h1>הרשמתך למערכת דרורית הושלמה</h1>\n";
	$l1 = _("Click");
	$l2 = _("here to connect");
	print "<h2>$l1 <a href=\"?action=login\">$l</a></h2>\n";
;;	print "<h2>לחצ\\י <a href=\"?action=login\">כאן להתחברות</a></h2>\n";
	return;
}
if($action == 'defupdate') {
//	print_r($_POST);
	if($name == 'demo') {
		print "<h1>משתמש דוגמה אינו רשאי לעדכן נתונים</h1>\n";
		return;
	}
	$companyname = htmlspecialchars($_POST['companyname'], ENT_QUOTES);
	$manager = htmlspecialchars($_POST['manager'], ENT_QUOTES);
	$regnum = htmlspecialchars($_POST['regnum'], ENT_QUOTES);
	$logo = htmlspecialchars($_POST['logo'], ENT_QUOTES);
	//$regnum = "511923740";//adam ERR chek wtf?!
	if($regnum != $_POST['regnum']) {
		print "Not equal <br>\n";
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
	$query .= "vatrep='$vatrep' \n";
	$query .= "WHERE prefix='$prefix'";
/*	print "<div dir=\"ltr\">\n";
	$qstr = nl2br($query);
	print "Query: $qstr<br>\n";
	print "</div>\n"; */
	$result = DoQuery($query, "defs.php");
	$l = _("Details succesfully updated");
	print "<h1>$l</h1>\n";
//	print "<h1>הנתונים עודכנו בהצלחה</h1>\n";
}

$text='';
//print "<div class=\"form righthalf1\">\n";
/* Get data from table */
$query = "SELECT * FROM $companiestbl WHERE prefix='$prefix'";
// print "Query: $query<br>\n";
$result = DoQuery($query, "defs.php");
$line = mysql_fetch_array($result, MYSQL_ASSOC);
if(!$line) {
	if(!$superuser) {
		$l = _("Can not execute this operation without logging in to system");
		$text.= "<br><br><h1>$l</h1>\n";
//		print "<br><br><h1>לא ניתן לבצע פעולה זו ללא התחברות למערכת</h1>\n";
		return;
	}
	$text.= "<form action=\"?module=defs&amp;action=defsubmit\" method=post>\n";
//	print "<div class=\"caption_out\"><div class=\"caption\">";
	$haeder = _("Entering new business");
	//print "<h3>$l</h3>\n<br>\n";
//	print "<h3>הגדרת חברה חדשה</h3>\n<br>\n";
}
else {
	$text.= "<form action=\"?module=defs&amp;action=defupdate\" method=\"post\">\n";
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
/*	$logo = $line['logo'];
	$header = $line['header'];
	$footer = $line['footer']; */
/*	$num1 = $line['num1'];
	$num2 = $line['num2'];
	$num3 = $line['num3'];
	$num4 = $line['num4'];
	$num5 = $line['num5'];
	$num6 = $line['num6']; */
	$editdata = 1;
}

if($taxrep == 0)
	$taxrep = 2;
// print "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">\n";

if(!$editdata) {
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr><td>\n";
	$l = _("Email");
	$text.= "$l: </td>";
//	print "דואר אלקטרוני: </td>";
	$text.= "<td><input type=\"text\" name=\"email\" value=\"\" dir=\"ltr\"></td>\n";
	$l = _("Password");
	$text.= "</tr><tr><td>$l: </td>\n";
//	print "</tr><tr><td>סיסמה: </td>\n";
	$text.= "<td><input type=\"password\" name=\"passwd\" value=\"\"></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Password verify");
	$text.= "<td>$l: </td>\n";
//	print "<td>אימות סיסמה: </td>\n";
	$text.= "<td><input type=\"password\" name=\"verpasswd\" value=\"\"></td></tr>\n";
	$text.= "<tr>\n";
	$l = _("Full name");
	$text.= "<td>$l: </td>\n";
//	print "<td>שם מלא: </td>\n";
	
	$text.= "<td><input type=\"text\" name=\"fullname\" value=\"\"></td></tr>\n";
	$text.= "<tr>\n";
	$l = _("Prefix: ");
	$text.= "<td>$l: </td>\n";
//	print "<td>קידומת חברה: </td>\n";
	$text.= "<td><input type=\"text\" name=\"prefix\" value=\"\" dir=\"ltr\"></td></tr>\n";
	$text.= "</table>\n";
	$text.= "<br>\n";
}
if($editdata) {
	$haeder = _("Edit business details");
	//print "<h3>$l</h3>\n\n";
}
$text.= "<table dir=\"$dir\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
$l = _("General definitions");
$text.= "<td colspan=\"2\"><b>$l: </b></td>\n";
// print "<td colspan=\"2\"><b>הגדרות כלליות: </b></td>\n";
$text.= "</tr><tr>\n";
$l = _("Business name");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"companyname\" value=\"$companyname\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("Manager name");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"manager\" value=\"$manager\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("Address");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"address\" value=\"$address\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("City");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"city\" value=\"$city\" size=\"10\">&nbsp;&nbsp;\n";
$l = _("Zip");
$text.= "$l: \n";
// print "מיקוד: \n";
$text.= "<input type=\"text\" name=\"zip\" value=\"$zip\" size=\"5\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("Phone");
$text.= "<td>$l: </td>\n";
// print "<td>טלפון: </td>\n";
$text.= "<td><input type=\"text\" name=\"phone\" value=\"$phone\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("Cellular");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"cellular\" value=\"$cellular\"></td>\n";
//adam:logo
$text.= "</tr><tr>\n";
$l = _("Logo");
$text.= "<td>$l: </td>\n";
$text.= "<td>";
$text.= "<form enctype=\"multipart/form-data\" action=\"uploader.php\" method=\"POST\">
<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"900000\" />
<input name=\"uploadedfile\" type=\"file\" value=\"$logo\"/><br />
<input type=\"submit\" value=\"Upload File\" /></form>"."</td>\n";
//adam:logo
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
// print "מספר עוסק: \n";
$text.= "<td><input type=\"text\" name=\"regnum\" value=\"$regnum\" size=\"8\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("Tax rep. period");
$text.= "<td>$l: </td>\n";
// print "<td> דיווח מקדמות: </td>\n";
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
// print "<td>מקדמות מס הכנסה: </td>\n";
$text.= "<td><input type=\"text\" name=\"tax\" value=\"$tax\" size=\"5\"></td>\n";
$text.= "</tr><tr>\n";
$text.= "<td>\n";
$l = _("VAT report");
$text.= "$l: </td>\n";
// print "דיווח מע\"מ: </td>\n";
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
// print "<td>אחוז מע\"מ: ";
$text.= "<td><input type=\"text\" name=\"vat\" value=\"$vat\" size=\"5\"></td>\n";
$text.= "</tr>\n";
/*
if(!$editdata) {
	print "<tr><td colspan=\"4\">\n";
	print "<input type=\"checkbox\" name=\"read\">קראתי ואני מסכים\\ה ל";
	print "<a href=\"?id=conditions\">תנאי השימוש</a><br>\n";
	print "</td></tr>\n";

} */
$text.= "<tr><td colspan=\"4\" align=\"center\">\n";
$l = _("Update");
$text.= "<br><input type=\"submit\" value=\"$l\">&nbsp;&nbsp;";
// print "<input type=\"button\" onclick=\"parent.location='index.php?module=defs'\" value=\"בטל שינויים\">\n";
$text.= "</td></tr>\n";
$text.= "</table>\n";
$text.= "</form>\n";
createForm($text,$haeder,"righthalf1",410);
//print "</div>\n";

print "<div class=\"lefthalf1\">\n";
ShowText('defs');
if(!$editdata) {
	print "<br><br><table border=\"0\" dir=\"$dir\"><tr><td>\n";
	/* firefox affiliate code */
	print "<a href='http://www.mozilla.com/en-US/?from=sfx&amp;uid=96935&amp;t=438'><img src='http://sfx-images.mozilla.org/affiliates/Buttons/Firefox3.5/96x31_blue.png' alt='Spread Firefox Affiliate Button' border='0' /></a>\n";
	print "</td><td valign=\"top\">\n";
	$l = _("We advise to use this software with Firefox browser");
	print "$l<br>\n";
	$l = _("To install press the logo on the left");
	print "$l<br>\n";
	$l = _("For more information");
	$l1 = _("Click here");
	print "$l <a href=\"id=firefox\">$l1</a>\n";
//	print "מומלץ להשתמש בתוכנה עם דפדפן פיירפוקס<br>\n";
//	print "להתקנה לחץ על הלוגו, לפרטים נוספים לחץ ";
//	print "<a href=\"?id=firefox\">כאן</a>\n";
	print "</td></tr></table>\n";
}
print "</div>\n";	/* close left half */
print "<br>\n";

?>
