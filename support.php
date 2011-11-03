<?PHP
/*
 | Support module for Drorit accounting system
 | Written by Ori Idan helicon technologies Ltd.
 */
global $dir;
global $logintbl, $companiestbl;
global $prefix;

$action = isset($_GET['action']) ? $_GET['action'] : '';
$name1 = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];
$name1 = urldecode($name1);
$email = $name1;
$query = "SELECT fullname FROM $logintbl WHERE name='$name1'";
$l = __LINE__;
$result = DoQuery($query, "$l");
$line = mysql_fetch_array($result, MYSQL_NUM);
$fullname = stripslashes($line[0]);

$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
$result = DoQuery($query, __LINE__);
$line = mysql_fetch_array($result, MYSQL_NUM);
$company = $line[0];

$subject1 = _("Subject");
$fullname1 = _("Full name");
$email1 = _("Email");
$message1 = _("Message");
$submit = _("Submit");

$contactform = <<<EOF
<form action="?module=support&action=submit" method="post">
<table class="formtbl" dir="$dir" width="100%">
<tr>
<td>$subject1: </td>
<td><input type="text" name="subject" size=\"30\" value="$subject"></td>
</tr><tr>
<td valign="top">$message1: </td>
<td>
<textarea rows="15" style="width:90%" name="message">$message</textarea>
</td>
</tr><tr>
<td colspan="2" align="center"><input type="submit" value="$submit"></td>
</tr>
</table>
</form>
EOF;

if($action == 'submit') {
	$s = GetPost('subject');
	$message = GetPost('message');
	
	$to = "helicontech@gmail.com";
	$from = "From: $email";
	$subject = "=?utf-8?B?" . base64_encode("[Linet] $s") . "?=";
	
	$body .= "<div dir=\"$dir\">\n";
	$l = _("From: ");
	$l1 = _("Regarding company");
	$body .= "$l$fullname <$email><br>$l1: $company<br>\n$message";
	$body .= "</div>\n";
	$headers = "Content-type: text/html; charset=UTF-8\r\n";
	$headers .= "From: $email\r\n";
	mail($to, $subject, $body, $headers);
	$thanks = _("Message sent to linet team");
	print "<h1>$thanks</h1>\n";
	$l = _("We will do our best to answer you shortly");
	print "<h1>$l</h1>\n";
	$l = _("Click here to continue");
	print "<br><h2><a href=\"index.php\">$l</a></h2>\n";
	return;
}

//print "<div class=\"form righthalf1\">\n";
$haeder = _("Support");
//print "<h3>$l</h3>\n";
//print "$contactform";
//print "</div>\n";
createForm($contactform,$haeder,$sClass,400);
print "<div class=\"lefthalf1\">\n";
$l = _("Hello");
print "<h2>$l: $fullname</h2><br>\n";

ShowText('support');
print "</div>\n";

?>

