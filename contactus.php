<?PHP
/*
 | Contact us module for Drorit accounting system
 */
global $dir;

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'submit') {
	$fullname = GetPost('fullname');
	$email = GetPost('email');
	$message = GetPost('message');
	
	if($email == '') {
		$noemail = _("If you don't specify email, we will not be able to answer");
		print "<h1>$noemail</h1>\n";
	}
	else {
		$to = "helicontech@gmail.com";
		$from = "From: $email";
		$body = "From: $fullname <$email>\n$message";
		mail($to, "Drorit contact form", $body, $from);
		$thanks = _("Message sent to drorit team");
		print "<h1>$thanks</h1>\n";
		$l = _("We will do our best to answer you shortly");
		print "<h1>$l</h1>\n";
		$l = _("Click here to continue");
		print "<br><h2><a href=\"index.php\">$l</a></h2>\n";
		return;
	}
}

$fullname1 = _("Full name");
$email1 = _("Email");
$message1 = _("Message");
$submit = _("Submit");

$contactform = <<<EOF
<form action="?module=contactus&action=submit" method="post">
<table class="formtbl" dir="$dir" width="100%">
<tr>
<td>$fullname1: </td>
<td><input type="text" name="fullname" value="$fullname"></td>
</tr><tr>
<td>$email1: </td>
<td><input type="text" name="email" value="$email"></td>
</tr><tr>
<td valign="top">$message1: </td>
<td>
<textarea rows="15" cols="35" name="message">$message</textarea>
</td>
</tr><tr>
<td colspan="2" align="center"><input type="submit" value="$submit"></td>
</tr>
</table>
</form>
EOF;

print "<br>\n";
print "<div class=\"righthalf1\">\n";
$l = _("Contact us");
print "<h3>$l</h3>\n";
print "$contactform";
print "</div>\n";


?>

