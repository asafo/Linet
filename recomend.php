<?PHP
/*
 | Recommend to a friend module for Freelance accounting system
 | Written by Ori Idan September 2009
 */

$subject = "bla";
$body = <<<RCMD
body
http://www.linet.org.il
RCMD;

$step = isset($_GET['step']) ? $_GET['step'] : 0;

print "<br><h1>window</h1>\n";
if($step == 1) {
	$from = $name;
	$headers = "From: $from\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
	
	$hbody = $_POST['body'];
//	$hbody = nl2br($hbody);
//	$hbody = "<html><head><title>$subject</title></head><body dir=rtl><p dir=\"rtl\">$hbody</p></body></html>\n";
	$subject = $_POST['subject'];
	$s = "=?utf-8?B?" . base64_encode("$subject") . "?=";
	$to = $_POST['tomail'];
//	print "<pre>$hbody</pre>\n";
	
	mail($to, $s, $body, $headers);
	print "<h2>title</h2>\n";
	print "<h2>דונ אןאךק</h2>\n";
}

//print "<div class=\"righthalf\">\n";
print "<form action=\"?module=recomend&step=1\" method=\"post\">\n";
print "<table class=\"formtbl\" cellpadding=\"5px\"><tr>\n";
print "<td>׳›׳×׳•׳‘׳× ׳�׳™׳™׳�: </td>\n";
print "<td><input type=\"text\" name=\"tomail\" dir=\"ltr\"></td>\n";
print "</tr><tr>\n";
print "<td>׳ ׳•׳©׳�: </td>\n";
print "<td><input type=\"text\" name=\"subject\" value=\"$subject\"></td>\n";
print "</tr><tr>\n";
print "<td valign=\"top\">׳˜׳§׳¡׳˜: </td>\n";
print "<td><textarea name=\"body\" rows=\"7\" cols=\"50\">$body</textarea>\n";
print "</tr><tr>\n";
print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"׳©׳�׳—\"></td>\n";
print "</tr>\n";
print "</table>\n";
print "</form>\n";
//print "</div>\n";


?>

