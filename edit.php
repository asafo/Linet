<?PHP
/*
 | Edit content pages for Drorit accounting system 
 | Written by Ori Idan December 2009
 */
global $articlestbl;
global $dir;

print "<br><div dir=\"$dir\" class=\"form righthalf1\">\n";
$l = _("Manage content pages");
print "<h3>$l</h3>\n";
$query = "SELECT id,subject FROM $articlestbl WHERE ancestor='' ORDER BY subject ASC";
$result = DoQuery($query, __LINE__);
print "<div style=\"margin:5px;margin-right:20px;font-size:14px\">\n";
print "<ul>\n";
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$id = $line['id'];
	$subject = $line['subject'];
	if($subject == '')
		$subject = $id;
	$url = "?module=text&amp;&action=add&amp;ancestor=$id";
	print "<li><a href=\"?id=$id&amp;action=edit\">$subject</a> &nbsp; ";
	$l = _("Add page");
	print "<a href=\"$url\">$l</a> ";
	$q1 = "SELECT id,subject FROM $articlestbl WHERE ancestor='$id' ORDER BY subject ASC";
	$r1 = DoQuery($q1, __LINE__);
	if(mysql_num_rows($r1)) {
		print "\n<ul>\n";
		while($l1 = mysql_fetch_array($r1, MYSQL_ASSOC)) {
			$subject = $l1['subject'];
			$id = $l1['id'];
			$url = "?id=$id&amp;action=edit";
			print "<li><a href=\"$url\">$subject</a></li>";
		}
		print "</ul>\n";
	}
	print "</li>\n";
}
print "</ul>";
$l = _("New page");
print "<a href=\"?module=text&amp;action=add\">$l</a>\n";
print "</div>\n";
print "</div>\n";


?>
