<?PHP
/*
 | personalization module for freelance accouting system.
 | This module will allow you to change the color scheme and logo.
 | Each color scheme is one css file.
 */
$cssfiles = array(
	'default.png' => 'freelance.css',
);

global $companiestbl, $prefix;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>\n";
	return;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'setfile') {
	$file = $_GET['file'];
	$query = "SELECT css FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "personalization");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	if($file != $line[0]) {
		$query = "UPDATE $companiestbl SET css='$file' WHERE prefix='$prefix'";
		DoQuery($query, "personalization");
		print "<script>window.location.reload(true);</script>\n";
	}
}
if($action == 'setlogo') {
	$size = (int)$_FILES['logo']['size'];
	if($size > 0) {	/* we have a file */
		$tmpname = $_FILES['logo']['tmp_name'];
		$orgname = $_FILES['logo']['name'];
		/* find extension */
		$offset = strrpos($orgname, '.');
		$offset++;
		$ext = substr($orgname, $offset);
		$logo = "pics/t_$prefix.$ext";
//		print "logo: $logo<br>\n";
		move_uploaded_file($tmpname, "$logo");
		$query = "UPDATE $companiestbl SET small_logo='$logo' WHERE prefix='$prefix'";
		DoQuery($query, "personalization");
		print "<script>window.location='index.php?module=personalization'</script>\n";
	}
}
if($action == 'dellogo') {
	$query = "SELECT small_logo FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "personalization");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	unlink($line[0]);
	$query = "UPDATE $companiestbl SET small_logo='' WHERE prefix='$prefix'";
	DoQuery($query, "personalization");
	print "<script>window.location='index.php?module=personalization'</script>\n";
}

print "<div class=\"caption_out\" style=\"margin-bottom:10px\">";
print "<div class=\"caption\">שינוי לוגו</div></div>\n";
print "<form action=\"?module=personalization&action=setlogo\" method=\"post\" enctype=\"multipart/form-data\">\n";
print "<table class=\"formtbl\">\n";
print "<tr><td valign=\"top\">קובץ לוגו: </td>\n";
print "<td><input type=\"file\" name=\"logo\" ><br>\n";
print "הקובץ חייב להיות בסיומת: jpg, png או gif";
print "<br>בגודל של";
print "250X97 פיקסלים";
print "</td>\n";
print "</tr><tr>\n";
print "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"עדכן\"></td>\n";
print "</tr><tr>\n";
print "<td colspan=\"2\"><a href=\"?module=personalization&action=dellogo\">מחק לוגו</a>";
print " וחזור לברירת המחדל";
print "</td>\n";
print "</tr>\n";
print "</table>\n";
print "</form>\n";
print "<br>\n";
// print_r($cssfiles);
/* Display table with default pictures */
print "<div class=\"caption_out\" style=\"margin-bottom:10px\">";
print "<div class=\"caption\">בחר ערכת צבעים</div></div>\n";
print "<table dir=\"ltr\" border=\"0\" cellpadding=\"10\">\n";
$i = 0;
foreach($cssfiles as $img => $file) {
	if(!($i % 4)) {
		if($i > 0)
			print "</tr>\n";
		print "<tr>\n";
	}
	$i++;
	print "<td><a href=\"?module=personalization&action=setfile&file=$file\">";
	print "<img border=\"0\" src=\"$img\" alt=\"image $i\" width=\"250px\"></a></td>\n";
}
print "</tr></table>\n";

?>
