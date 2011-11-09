<?PHP
/*
 | Register demo user
 */

global $logintbl;

print "<br>\n";

$email = isset($_POST['email']) ? $_POST['email'] : '';
$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';

$formstr = <<<EOF
<div class="righthalf1">
<h3>׳¨׳™׳©׳•׳� ׳�׳—׳‘׳¨׳” ׳�׳“׳•׳’׳�׳�</h3>
<form action="?module=demoreg&amp;step=1" method="post">
<table class="formtbl" width="100%">
	<tr>
		<td>׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™: </td>
		<td><input type="text" name="email"></td>
	</tr>
	<tr>
		<td>׳©׳� ׳�׳�׳�: </td>
		<td><input type="text" name="fullname"></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" value="׳”׳¨׳©׳�"></td>
	</tr>
</table>
</form>
</div>
EOF;

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 0) {
	print $formstr;
}
else {
	$query = "SELECT password FROM $logintbl WHERE name='$email'";
	$result = DoQuery($query, __LINE__);
	if(mysql_num_rows($result)) {
		$line = mysql_fetch_array($result, MYSQL_NUM);
		if($line[0] == 'demo') {
			$l = _("You have registered in the past to the demo version");
			print "<h2>$l</h2>\n";
			$l = _("Login to software using this email with no password");
			print "<h2>$l</h2>\n";
			$l = _("Click here to login");
			print "<h2><a href=\"?action=login\">$l</a></h2>\n";
		}
		else {
			print "<h2>׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™ ׳§׳™׳™׳� ׳‘׳�׳¢׳¨׳›׳×</h2>\n";
			print "<h2>׳�׳—׳¥ ";
			print "<a href=\"?action=login\">׳›׳�׳� ";
			print "׳�׳›׳ ׳™׳¡׳” ׳�׳�׳¢׳¨׳›׳× ׳�׳• ׳×׳–׳›׳•׳¨׳× ׳¡׳¡׳�׳”";
			print "</a></h2>\n";
		}
		print "$formstr";
		
	}
	else {
		/* This email does not exist in system, insert it */
		$query = "INSERT INTO $logintbl (name, fullname, password, lastlogin) ";
		$query .= "VALUES('$email', '$fullname', 'demo', NOW())";
		DoQuery($query, __LINE__);
		$cookietime = time() + 60*60*24*30;
		$cookiestr = "name,$email,$cookietime:data,demo,$cookietime";
		$url = "index.php?cookie=$cookiestr&amp;name=$email&amp;data=demo";
		print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=$url\">\n";
	}
}
?>
