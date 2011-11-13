<?PHP
// header('Content-type: text/html;charset=UTF-8');
/*
 | login handling script for Drorit free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
global $logintbl, $permissionstbl;
global $name;
//global $dir;
$text='';
if(isset($_POST['name']))
	$name = $_POST['name'];

if(!isset($prefix)) {
	if(isset($_COOKIE['company']))
		$prefix =  $_COOKIE['company'];
}

function UpdateLevel($uname, $level) {
	global $prefix;
	global $permissionstbl;
	
	$query = "SELECT * FROM $permissionstbl WHERE name='$uname' AND company='$prefix'";
	$result = DoQuery($query, "UpdateLevel");
	$n = mysql_num_rows($result);
	if($n == 0) {
		/* check for special case of system administrator that can work on all companies */
		$query = "SELECT * FROM $permissionstbl WHERE name='$uname' AND company='*'";
		$result = DoQuery($query, "UpdateLevel");
		$n = mysql_num_rows($result);
		if($n)
			return;
		$query = "INSERT INTO $permissionstbl VALUES('$uname', '$prefix', '$level')";
	}
	else
		$query = "UPDATE $permissionstbl SET level='$level' WHERE name='$uname' AND company='$prefix'";
//	print "Query: $query<br />\n";
	DoQuery($query, "UpdateLevel");
}

function AddUser() {
	//global $levelsarr;
	global $prefix;
	//global $dir;
	
	//print "<br />\n";
	//print "<div class=\"form righthalf1\">\n";
	$haeder = _("Add user");
	//print "<h3>$l</h3>";
	$text.= "<form action=\"?module=login&amp;action=doadduser\" method=\"post\">\n";
	$text.= "<table dir=\"$dir\" border=\"0\" align=\"center\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("Email");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"name\" /></td></tr>\n";
	$l = _("Full name");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td><input type=\"text\" name=\"fullname\" value=\"\" /></td></tr>\n";
	$l = _("Password");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td><input type=\"password\" name=\"password\" value=\"\" /></td></tr>\n";
	$l = _("Verify password");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td><input type=\"password\" name=\"verpassword\" /></td></tr>\n";
//	print "<tr>\n";
/*	print "<td>׳³ג€�׳³ֲ¨׳³ֲ©׳³ן¿½׳³ג€¢׳³ֳ—: </td>\n";
	print "<td>\n";
	print "<select name=level>\n";
	foreach($levelsarr as $key => $val) {
		print "<option value=$key";
		if($key == $level)
			print " selected";
		print ">$val</option>\n";
	}
	print "</select>\n"; */
	if($prefix == '')
		$text.= "<input type=hidden name=prefix value=\"*\" />\n";
//	print "</td></tr>\n";
	$l = _("Create");
	$text.= "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\" /></td></tr>\n";
	$text.= "</table>\n</form>\n";
	//print "</div>\n";
//	print "</tr></td></table>\n";
	createForm($text, $haeder,'',750,'','img/icon_adduser.png',1,getHelp());
}

/*if($action == 'delname') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$uname = $_POST['uname'];
	$query = "DELETE FROM $logintbl WHERE name='$uname'";
	// print "Query: $query<br>\n";
	DoQuery($query, "Del");
	$query = "DELETE FROM $permissionstbl WHERE name='$uname' AND company='$prefix'";
	DoQuery($query, "Del");
	$l = _("Name succesfully deleted");
	print "<h1  class=\"login\">$l</h1>\n";
	return;
}
if($action == 'removeperm') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$query = "DELETE FROM $permissionstbl WHERE name='$uname' AND prefix='$prefix'";
	DoQuery($query, "RemovePerm");
	return;
}*/
if($action == 'logout') {
	$name = $_COOKIE['name'];
	$query = "UPDATE $logintbl SET cookie='' WHERE name='$name'";
	if(!mysql_query($query)) {
		echo mysql_error();
	}
	setcookie('name', "", time() - 3600);	/* delete cookies */
	setcookie('data', "", time() - 3600);	/* delete cookies */
	
	return;
}

if($action == 'doadduser') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data"); //adam
		print "<h1>$l</h1>\n";
		return;
	}

	$name = $_POST['name'];
	/* first check if this is a new user that does not exist */
	$query = "SELECT name FROM $logintbl WHERE name='$name'";
	$result = DoQuery($query, "login.php");
	$n = mysql_num_rows($result);
	if($n) {
		$l = _("Email already exists"); //adam
		print "<h1 class=login>$l</h1>\n";
		if($prefix == '')
			$prefix = $_POST['prefix'];
//		$level = $_POST['level'];
		$level = 0;
		$query = "SELECT name FROM $permissionstbl WHERE name='$name'";
		$l = __LINE__;
		$f = __FILE__;
		$result = DoQuery($query, "$f $l");
		$n = mysql_num_rows($result);
//		print "<div dir=ltr>Query: $query<br />n: $n<br /></div>\n";
		if($n == 0) {
			$query = "INSERT INTO $permissionstbl VALUES('$name', '$prefix', '$level')";
			$l = __LINE__;
			$result = DoQuery($query, "$f $l");
			$l = _("User added to business");
			print "<h1  class=\"login\">$l</H1>\n";
			$l = _("Click here to continue");
			print "<h2  class=\"login\"><a href=index.php>$l</A></H2>\n";
			return;
		}
	}
	else {
		/* this is a new user... */
		$password = $_POST['password'];
		$verpassword = $_POST['verpassword'];
		if($password != $verpassword) {
			$l = _("Passwords are not equal");
			ErrorReport("$l");
			exit;
		}
		$fullname = htmlspecialchars($_POST['fullname'], ENT_QUOTES);
		$query = "INSERT INTO $logintbl (name, fullname, password) ";
		$query .= "VALUES ('$name', '$fullname', PASSWORD('$password'))";
//		print "<div dir=ltr>Query: $query<br /></div>\n";
		$result= DoQuery($query, "login add");
	
		if($prefix == '')
			$prefix = $_POST['prefix'];
		
//		$level = $_POST['level'];
		$level = 0;
		$query = "INSERT INTO $permissionstbl VALUES('$name', '$prefix', '$level')";
		$result = DoQuery($query, "login add");

		$l = _("User successfully added");
		print "<h1  class=\"login\">$l</H1>\n";
		$l = _("Click here to continue");
		print "<h2  class=\"login\"><a href=index.php>$l</a></h2>\n";
		return;
	}
}
if($action == 'updateuser') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$uname = $_POST['uname'];
	unset($password);	/* make sure $password is not set */
	if(isset($_POST['password']) && ($_POST['password'] != '')) {
		$password = $_POST['password'];
		$verpassword = $_POST['verpassword'];
		if($password != $verpassword) {
			$l = _("Passwords are not equal");
			ErrorReport("$l");
			exit;
		}
	}
//	$level = $_POST['level'];
	$level = 0;
	$fullname = addslashes($_POST['fullname']);
//	$email = $_POST['email'];

	$query = "UPDATE $logintbl SET fullname='$fullname'";
	if(isset($password))
		$query .= ", password=PASSWORD('$password')";
	$query .= " WHERE name='$uname'";
//	PageHeader();	
	$result = DoQuery($query, "login update");
	
	UpdateLevel($uname, $level);

	$l = _("User successfully updated");
	print "<h1>$l</h1>\n";
}

if($action == 'forgot') {
	$email = $_POST['email'];
	$query = "SELECT * FROM $logintbl WHERE name='$email'";
	$result = DoQuery($query, "login.php");
	$n = mysql_num_rows($result);
	if($n == 0) {
		$l = _("No user with this email");
		ErrorReport("$l");
		exit;
	}
	$str = md5($email);
	$r = rand(0, 26);
	$pwd = substr($str, $r, 6);
	$query = "UPDATE $logintbl SET password=PASSWORD('$pwd') WHERE name='$email'";
		echo "<br />".$pwd."; <br />";
	DoQuery($query, "login.php");
	$l = _("New password for Linet accounting software");
	$subject = "=?utf-8?B?" . base64_encode("$l") . "?=";
	$headers = "Content-type: text/html; charset=UTF-8\r\n";
	$headers .= "From: no-reply@linet.org.il\r\n";
	$body .= "<div dir=\"$dir\">\n";
	$body .= _("Your new Linet password is: ");
	$body .= "$pwd\n";
	$body .= "</div>\n";
	mail($email, $subject, $body, $headers);
	
	
	
	$l = _("Password sent to email");
	print "<br /><h1>$l</h1>\n";
	$action = '';
}
if($action == 'login') {
	//$bla=Get
	$password = GetPost('password');//[];
	$name = GetPost('name');//['name'];
	$rememberme=GetPost('rememberme');
//	print "<div dir=ltr>\n";
//	print "Name: $name, password: $password<br />\n";
//	print "</div>\n";
	$query = "SELECT name,hash FROM $logintbl WHERE name='$name' AND password=PASSWORD('$password')";
//	print "Query: $query<br />\n";
//	print "</div>\n";
	$f = __FILE__;
	$l = __LINE__;
	$result = DoQuery($query, "$f $l");
	$n = mysql_num_rows($result);
	if($n == 0) {
		/* Test if this is a registered demo user */
		$query = "SELECT name FROM $logintbl WHERE name='$name' AND password='demo'";
		$result = DoQuery($query, __LINE__);
		$n = mysql_num_rows($result);
		if($n == 0) {
			$l = _("Incorrect email or password");
			ErrorReport("$l");
			exit;
		}
		else
			$demouser = 1;
	}
/*	$line = mysql_fetch_array($result, MYSQL_NUM);
	if($line[1] != '') {
	//	print_r($line);
		print "<h1>׳³ן¿½׳³ֲ©׳³ֳ—׳³ן¿½׳³ֲ© ׳³ן¿½׳³ן¿½ ׳³ֲ¡׳³ג„¢׳³ג„¢׳³ן¿½ ׳³ן¿½׳³ֳ— ׳³ֳ—׳³ג€�׳³ן¿½׳³ג„¢׳³ן¿½ ׳³ג€�׳³ג€�׳³ֲ¨׳³ֲ©׳³ן¿½׳³ג€�</h1>\n";
		return;
	} */
	
	$query = "UPDATE $logintbl SET lastlogin=NOW() WHERE name='$name'";
	$result = DoQuery($query, "dologin");
	/* now get it back again */
	$query = "SELECT lastlogin FROM $logintbl WHERE name='$name'";
	$result = DoQuery($query, "dologin");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$data = md5($line[0]);
	//print $rememberme;
	if($rememberme='on')
		$query = "UPDATE $logintbl SET cookie='$data' WHERE name='$name'";
	else 
		$query = "UPDATE $logintbl SET cookie='' WHERE name='$name'";
	$result = DoQuery($query, __FILE__.": ".__LINE__);
	//if(!$result) {
	//	echo mysql_error();
	//}
	//	exit;
	$cookietime = time() + 60*60*24*30;
	//$cookiestr = "name,$name,$cookietime:data,$data,$cookietime";
	$url = "index.php";
	setcookie('name', $name, $cookietime);
	setcookie('data', $data, $cookietime);
	$_SESSION['name']=$name;
	$_SESSION['data']=$data;
	$l = _("You have succesfully entered the system");
	print "<h1  class=\"login\">$l</h1>\n";
//	print "<script type=\"text/javascript\">location.href='$url'</script>\n";
	print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL=$url\">\n";
	exit;
}

if($action == 'edituser') {
	global $levelsarr, $name;
	global $logintbl;
	//adam test area
	/*user test*/
	
	//require('class/user.php');
	//$usr=new user;
	//$usr->name='adam23146@gmail.com';
    //$usr->fullname='Adam bh';
	//$usr->password='qwe123';
	//print(';'.$usr->newUser().';');
	//$usr->fullname='Adambba bh';
	//print(';'.$usr->updateUser().';;');
	//$usr->getUser();
	//print_r($usr);
	//print(';;'.$usr->deleteUser().';');
	
	
	/* item test */
	/*
	require('class/item.php');
	$item=new item;

	
	$item->account ='220'; 
	$item->name ='new item';
	$item->unit ='Meters';
	$item->extcatnum ='';
	$item->manufacturer ='20111';
	$item->defprice ='40';
	$item->currency =0;
	$item->ammount=14;
	print(';'.$item->newItem($newitem).';');
	$item->num ='2';
	$item->name ='updatedbla';
	$item->defprice ='450';
	//$item->getItem();
	//print_r($item);
	print(';'.$item->updateItem().';');
	$item->num ='11';
	print(';'.$item->deleteItem().';');
	*/
	
	/* Account test */
	/*require('class/account.php');
	$acc=new account;
	$acc->num ='1';
	$acc->getAccount();
	print_r($acc);
	
    $acc->pay_terms ='60';
    $acc->company ='׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€” ׳³ג€”׳³ג€�׳³ג„¢׳³ֲ©';
	print(';'.$acc->newAccount().';');
	$acc->num ='210';
	$acc->vatnum ='300777778';
	$acc->zip ='90210';
	print(';'.$acc->updateAccount().';');
	print(';'.$acc->deleteAccount().';');*/
	
	/* Document test */
	/*require('class/document.php');
	$doc=new document(DOC_RECEIPT);
	print(":".DOC_INVOICE.":");
	//$newdoc=$doc->arr;
	//print_r($newdoc);
	//print_r($doc);
	//$doc->num=15;
	//$doc->getDocument();
	//print_r($doc);
	$doc->company='zuzu lifa';
	//$doc->doctype ='3';
	//$doc->account ='201';
	$doc->issue_date ='2011-05-23'; 
	$doc->due_date ='2011-05-23';
	$doc->sub_total ='0.00';
	$doc->novat_total ='20000.00';
	//$doc->total ='20000.00';
	//$doc->printed ='0';
	//$doc->comments ='';
	//$doc->vtiger ='0';
	//$doc->docdetials[0]->description='description';
	//$doc->docdetials[0]->cat_num=5;
	print(';'.$doc->newDocument().';');
	
	//print(';'.$doc->newDocument($newdoc).';');
	//$doc->num=10;
	//$doc->getDocument();
	//$doc->company='just zuzu without the lifa';
	//$doc->docdetials[0]->description='descr12iption';
	//print(';'.$doc->updateDocument().';');
	//$doc->num=11;
	//print(';'.$doc->deleteDocument().';');*/
	//print_r($doc);
	//end adam
	
	$query = "SELECT * FROM $logintbl WHERE name='$name'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "login, edit");
	
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
//	print_r($line);
	$fullname = stripslashes($line['fullname']);
	$level = $line['level'];
	$email = $line['email'];
	$hash=$line['hash'];
	
	//print "<br />\n";
	//print "<div class=\"form righthalf1\">\n";
	$l = _("Edit user details");
	$haeder=$l;
	//print "<h3>$l</h3>";
	$text.= "<form id=\"edituser\" action=\"?module=login&amp;action=updateuser\" method=\"post\">\n";
	$text.= "<table border=\"0\" dir=\"$dir\" align=\"center\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("Email");
	$text.= "<td>$l: </td>\n";
	$text.= "<td>$name</td></tr>\n";

	$l = _("Full name");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td>\n";
	$text.= "<input type=\"hidden\" name=\"uname\" value=\"$name\" />\n";
	$text.= "<input type=\"text\" name=\"fullname\" value=\"$fullname\" /></td></tr>\n";
	$l = _("Password");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td><input type=\"password\" name=\"password\" /></td></tr>\n";
	$l = _("Verify password");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td><input type=\"password\" name=\"verpassword\" /></td></tr>\n";
	$l = _("App Key");
	$text.= "<tr><td>$l: </td>\n";
	$text.= "<td><font>$hash</font></td></tr>\n";
/*	print "<tr><td>׳³ג€�׳³ֲ¨׳³ֲ©׳³ן¿½׳³ג€¢׳³ֳ—: </td>\n";
	print "<td>\n";
	print "<select name=\"level\">\n";
	foreach($levelsarr as $key => $val) {
		print "<option value=\"$key\"";
		if($key == $level)
			print " selected";
		print ">$val</option>\n";
	}
	print "</select>\n"; 
	print "</td></tr>\n" */
	$l = _("Update");
	$text.= "<tr><td colspan=\"2\" align=\"center\">";
	$text.="<a href=\"javascript:$('#edituser').submit();\" class=\"btnaction\">$l</a></td></tr>\n";
	$text.= "</table>\n</form>\n";
	//print "</div>\n";
	createForm($text, $haeder, '',750,'','img/icon_edituser.png',1,getHelp());
	return;
}
if($action == 'adduser') {
	if(!isset($prefix) || ($prefix == '')) {
		$l = _("This operation can not be executed without choosing a business first");
		print "<h1>$l</h1>\n";
		return;
	}
	AddUser();
	return;
}

$query = "SELECT * FROM $logintbl";
$result = DoQuery($query, "Login1");
$n = mysql_num_rows($result);
if($n == 0) {	/* Special case, no users in system */
	AddUser();
}
else {
	
	$text="";
//	print "<div class=\"caption_out\"><div class=\"caption\">\n<h3>";
	$l = _("Dear Custmer,");
	$text.= "<h3>$l</h3>";
	$l = _("Entrance is only for registerd users");
	$text.= "$l";
//	print "</h3></div></div>\n";
	$text.=  "<form class=\"login1\" id=\"login\" action=\"index.php?action=login\" method=\"post\">\n";
	$text.=  "<table border=\"0\" cellpadding=\"5px\" width=\"300px\"><tr>\n";
	$l = _("Email");
	$text.=  "<td>$l: <br />";
	$text.=  "<input type=\"text\" name=\"name\" size=\"17\" /></td></tr>\n";
	$l = _("Password");
	$text.=  "<tr><td>$l: <br />";
	$text.=  "<input type=\"password\" name=\"password\" size=\"17\" /></td></tr>\n";
	$l = _("Remember me");
	$text.=  "<tr><td>";
	$text.=  "<input type=\"checkbox\" name=\"rememberme\" />$l\n";
	$l = _("I forgot my password");
	$text.='<a href="#" id="btnfrgt">'.$l.'</a></td></tr>';
	$l = _("Login");
	$text.=  "<tr><td colspan=\"2\" align=\"center\">";
	$text.="<a href=\"javascript:$('#login').submit();\" class=\"btnaction\">$l</a></td></tr>\n";
	$text.=  "</table>\n";
	
	$text.=  "</form>\n";
	$text.=  "<br />\n";

	
	//$text.="<div>";
		$text.=  "<form class=\"login1\" id=\"forgat\" action=\"?module=login&amp;action=forgot\" method=\"post\">\n";
		$text.=  "<table width=\"300px\">\n";
	//$text.=  "<td colspan=\"2\">";
	
	//$text.=  "<h2>$l</h2></td></tr>\n";
	$l = _("Email");
	$text.=  "<tr><td>$l: <br />\n";
	$text.=  "<input type=\"text\" name=\"email\" size=\"30\" /></td>\n";
	$text.=  "</tr><tr>\n";
	$l = _("Submit");
	$text.=  "<td align=\"center\"><a href=\"javascript:$('#forgat').submit();\" class=\"btnaction\">$l</a></td></tr>\n";
	$l = _("Cancel");
	$text.='<tr><td><a href="#" id="btncancel">'.$l.'</a>';
	
	$text.=  "</td></tr></table></form>\n";
	
	//$text.="</div>";
	$haeder=_("Login");
	createForm($text, $haeder, 'login',500,400,'img/icon_login.png',null,getHelp());

}

?>

