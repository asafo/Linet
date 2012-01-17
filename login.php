<?PHP
/*
 | login handling script for Drorit free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 | Modfied By Adam BH
 | This program is a free software licensed under the GPL 
 */
global $logintbl, $permissionstbl;
global $name;
require_once 'class/user.php';
//global $dir;
$text='';
if(isset($_POST['name']))
	$name = sqlText($_POST['name']);

if(!isset($prefix)) {
	if(isset($_COOKIE['company']))
		$prefix =  sqlText($_COOKIE['company']);
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
	global $text;
	//print "<br />\n";
	//print "<div class=\"form righthalf1\">\n";
	$haeder = _("Add user");
	//print "<h3>$l</h3>";
	$text.= "<form id=\"edituser\" action=\"?module=login&amp;action=doadduser\" method=\"post\">\n";
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
	if($prefix == '')
		$text.= "<input type=hidden name=prefix value=\"*\" />\n";
//	print "</td></tr>\n";
	$l = _("Create");
	//$text.="";
	$text.= "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\" class='btnaction' /></td></tr>\n";
	$text.= "</table>\n</form>\n";
	//print "</div>\n";
//	print "</tr></td></table>\n";
	createForm($text, $haeder,'',750,'','img/icon_adduser.png',1,getHelp());
}


if($action == 'logout') {
	$name = sqlText($_COOKIE['name']);
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

	$name = sqlText($_POST['name']);
	/* first check if this is a new user that does not exist */
	$query = "SELECT name FROM $logintbl WHERE name='$name'";
	$result = DoQuery($query, "login.php");
	$n = mysql_num_rows($result);
	if($n) {
		$l = _("Email already exists"); //adam
		print "<h1 class=login>$l</h1>\n";
		if($prefix == '')
			$prefix = sqlText($_POST['prefix']);
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
			$text.= "<h1  class=\"login\">$l</H1>\n";
			$l = _("Click here to continue");
			$text.= "<h2  class=\"login\"><a href=index.php>$l</A></H2>\n";
			if($prefix!='*')
				$action='adduser';
			else 
				$action='login';
			//return;
		}
	}
	else {
		/* this is a new user... */
		//$first=$_POST["prefix"];
		$password = $_POST['password'];
		$verpassword = $_POST['verpassword'];
		if($password != $verpassword) {
			$l = _("Passwords are not equal");
			ErrorReport("$l");
			exit;
		}
		$password=sha1($password);
		$hash=sha1(rand());
		
		$fullname = htmlspecialchars($_POST['fullname'], ENT_QUOTES);
		$query = "INSERT INTO $logintbl (name, fullname, password,hash) ";
		$query .= "VALUES ('$name', '$fullname', '$password','$hash')";
//		print "<div dir=ltr>Query: $query<br /></div>\n";
		$result= DoQuery($query, "login add");
	
		if($prefix == '')
			$prefix = sqlText($_POST['prefix']);
		
//		$level = $_POST['level'];
		$level = 0;
		$query = "INSERT INTO $permissionstbl VALUES('$name', '$prefix', '$level')";
		$result = DoQuery($query, "login add");

		$l = _("User successfully added");
		$text.= "<h1  class=\"login\">$l</H1>\n";
		$l = _("Click here to continue");
		$text.= "<h2  class=\"login\"><a href=index.php>$l</a></h2>\n";
		$action='login';
		//return;
	}
}
if($action == 'updateuser') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$uname = sqlText($_POST['uname']);
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
	if(isset($password)){
		$password=sha1($password);
		$query .= ", password='$password'";
	}
	$query .= " WHERE name='$uname'";
//	PageHeader();	
	$result = DoQuery($query, "login update");
	
	UpdateLevel($uname, $level);

	$l = _("User successfully updated");
	$text.= "<h1>$l</h1>\n";
	$action='edituser';
}

if($action == 'forgot') {
	$email = sqlText($_POST['email']);
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
	$pwd = sha1(substr($str, $r, 6));
	$query = "UPDATE $logintbl SET password='$pwd' WHERE name='$email'";
	//	echo "<br />".$pwd."; <br />";
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
}
if($action == 'login') {
	$password = GetPost('password');
	$name = GetPost('name');
	$rememberme=GetPost('rememberme');
	global $curuser;

	$a=$curuser->login($name,$password,null,null);
	if($a==1){
		$l = _("You have succesfully entered the system");
		print "<h1  class=\"login\">$l</h1>\n";
		$url='index.php';
		print "<script type=\"text/javascript\">window.location.href='$url';</script>\n";
		exit;
	}else $text.=  _("Password or user name is not correct");
	
}

if($action == 'edituser') {
	global $levelsarr, $name;
	global $logintbl;
	
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
	//adam usr list:
	$text.= "<table class=\"tablesorter\">\n";
	$text.= "\t<thead><tr><th class=\"header\">"._("name")."</th><th class=\"header\">"._("Full Name")."</th><th class=\"header\">"._("Actions")."</th><tr></thead>\n";
	$text.= "\t<tfoot></tfoot>\n";
	$text.= "\t<tbody>\n";
	global $permissionstbl;
	global $logintbl;
	$ulist=selectSql(array("company"=>$prefix), $permissionstbl);
	foreach($ulist as $usr){
		$aname=$usr['name'];
		$blist=selectSql(array("name"=>$aname), $logintbl);
		$bname=$blist[0]['fullname'];
		$text.= "\t\t<tr><td>$aname</td><td>$bname</td><td>$actions</td></tr>\n";
	}
	$text.= "\t</tbody>\n";
	$text.= "</table>\n";
	
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
	$text.="<input type=\"submit\" value=\"$l\" class='btnaction' />";	
	$text.="</td></tr>\n";
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
	
	
//	print "<div class=\"caption_out\"><div class=\"caption\">\n<h3>";
	$l = _("Dear Custmer,");
	$text.= "<h3>$l</h3>";
	$l = _("Entrance is only for registerd users");
	$text.= "$l";
//	print "</h3></div></div>\n";
	$text.=  "<form name=\"loginform\" class=\"login1\" id=\"login\" action=\"index.php?action=login\" method=\"post\">\n";
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
	//$l = _("I forgot my password");
	//$text.='<a href="javascript:showme();">'.$l.'</a></td></tr>';
	$text.=  "<tr><td colspan=\"2\" align=\"center\">";
	$l = _("Login");
	$text.="<input type=\"submit\" value=\"$l\" class='btnaction' />";	
	$text.="</td></tr>\n";
	
	$text.=  "</table>\n";
	
	$text.=  "</form>\n";
	$text.=  "<br />\n";
	$javas=<<<bla
<script type="text/javascript">
function showme(){ 
        $("#forgat").show('slow');
        $("#login").hide(1000);
       
} 
/*function hideme(){
        $("#login").show('slow');
        $("#forgat").hide(1000);
    };*/
</script>
bla;
	$text.=$javas;
	//$text.="<div>";
	/*$text.=  "<form class=\"login1\" id=\"forgat\" action=\"?module=login&amp;action=forgot\" method=\"post\">\n";
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
	$text.='<tr><td><a href="javascript:hideme();">'.$l.'</a>';
	
	$text.=  "</td></tr></table></form>\n";//*/
	
	//$text.="</div>";
	$haeder=_("Login");
	//global $ismobile;
	if(!$ismobile)
		createForm($text, $haeder, 'login',500,400,'img/icon_login.png',null,getHelp());
	else
		print $text;
}

?>