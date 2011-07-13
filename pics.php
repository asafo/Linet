<?PHP
header('Content-type: text/html;charset=UTF-8');
include('config.inc.php');

if(isset($_COOKIE[$cookiename]))
	$name = $_COOKIE[$cookiename];

function GetURI() {
        $server = $_SERVER['SERVER_NAME'];
        $uri = $_SERVER['REQUEST_URI'];
        $uriarr = split('/', $uri);

        $uri = "http://$server";
        $i = count($uriarr);
        foreach($uriarr as $val) {
                if($i > 1)
                        $uri .= "$val/";
                $i--;
        }
        return $uri;
}

$abspath = GetURI();
if(!isset($tinymcepath))
	$tinymcepath = $abspath;

$pghead = <<< HEAD
<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<title>מערכת תמונות</title>
<style>
.para {display: none;}
table { font-size: 14px; font-family: arial, sans-serif}
body {margin:0; font-size: 12px, font-family: arial, sans-serif}
a:visited {color:blue}
a:link {color:navy; font-family:arial, sans-serif }
a:hover {color:red}
.text1 { font-size:10px; font-family: arial, sans-serif}
.text2 { font-size:11px; font-family: arial, sans-serif}
.text3 { font-size:14px; font-family: arial, sans-serif}
h1 {font-size: 24; font-weight:bold; font-family: arial, sans-serif; color: navy}
h2 {font-size: 18; font-weight:bold; font-family: arial, sans-serif; color: navy}
</style>
<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>
<SCRIPT Language=JavaScript>
function ReturnValue(val) {
	var win = tinyMCE.getWindowArg("window");
	win.tinyMCE.setWindowArg('editor_id', 'mce_editor_0');
	win.document.getElementById(tinyMCE.getWindowArg("input")).value = val;
	if (win.getImageData) win.getImageData();
	win.showPreviewImage(val);
	tinyMCEPopup.close();
}
function formsubmit() {
	document.galsel.submit();
}
</script>
</head>
<body>
HEAD;

print "$pghead\n";

$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_select_db($database) or die("Could not select database: $database");

if(isset($_GET['gallery'])) {
	$gallery = $_GET['gallery'];
	$galurlencoded = urlencode($gallery);
}
else {
	$query = "SELECT gallery FROM $picstbl GROUP BY 'gallery'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<br>\n";
		echo mysql_error();
		exit;
	}
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$gallery = $line[0];
}

if(isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = '';

if($action == 'update') {
	$num = $_GET['num'];
	
	$size = (int)$_FILES['pic']['size'];
	if($size > 0) {	/* we seem to have a file */
		$tmpname = $_FILES['pic']['tmp_name'];
		$orgname = $_FILES['pic']['name'];
		/* find extension */
		$offset = strrpos($orgname, '.');
		$offset++;
		$ext = substr($orgname, $offset);
		$query = "UPDATE $picstbl SET ext='$ext' WHERE num='$num'";
		$result = mysql_query($query);
		move_uploaded_file($tmpname, "pics/pic$num.$ext");
	}
	$description = $_POST['description'];
	$query = "UPDATE $picstbl SET description='$description' WHERE num='$num'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<BR>\n";
		echo mysql_error();
		exit;
	}
}
if($action == 'add') {
	$size = (int)$_FILES['pic']['size'];
	if($size > 0) {	/* we seem to have a file */
		$tmpname = $_FILES['pic']['tmp_name'];
		$orgname = $_FILES['pic']['name'];
		/* find extension */
		$offset = strrpos($orgname, '.');
		$offset++;
		$ext = substr($orgname, $offset);
		$description = $_POST['description'];
		$query = "INSERT INTO $picstbl (ext, gallery, description) ";
		$query .= "VALUES ('$ext', '$gallery', '$description')";
		$result = mysql_query($query);
		if(!$result) {
			print "Query: $query<BR>\n";
			echo mysql_error();
			exit;
		}
		$num = mysql_insert_id();
		move_uploaded_file($tmpname, "pics/pic$num.$ext");
	}
}
if($action == 'del') {
	$num = $_GET['num'];
	
	$query = "SELECT ext FROM $picstbl WHERE num='$num'";
	$result = mysql_query($query);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$ext = $line[0];
	if(!empty($ext)) {
		unlink("pics/pic$num.$ext");
		$query = "DELETE FROM $picstbl WHERE num='$num'";
		$result = mysql_query($query);
	}
}
if($action == 'edit') {
	$num = $_GET['num'];
	
	$query = "SELECT ext,description FROM $picstbl WHERE num='$num'";
	$result = mysql_query($query);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$ext = $line[0];
	$desc = $line[1];
	print "<h1 align=center>עריכת טקסט לתמונה</h1>\n";
	print "<img align=center src=pics/pic$num.$ext><br><br>\n";
	print "<form enctype=multipart/form-data action=pics.php?action=update&num=$num&gallery=$galurlencoded method=post>\n";
	print "<table dir=rtl align=center border=8><tr>\n";
	print "<td>תמונה: </td>\n";
	print "<td><input type=file name=pic></td>\n";
	print "</tr><tr>\n";
	print "<td>תיאור: </td>\n";
	print "<td><textarea name=description rows=5 cols=40>$desc</textarea></td>\n";
	print "</tr><tr>\n";
	print "<td colspan=2 align=center><input type=submit value=שלח></td>\n";
	print "</tr>\n";
	print "</table>\n";
	print "</form>\n";
	exit;
}

if($gallery) {
	$query = "SELECT * FROM $picstbl WHERE gallery='$gallery'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<BR>\n";
		echo mysql_error();
		exit;
	}

	print "<h1 align=center>$gallery</h1>\n";
	print "<br>\n";
	print "<table border=\"1\" dir=\"rtl\" align=\"center\">\n";
	$galurlencoded = urlencode($gallery);
	$i = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if($i == 0)
			print "<tr>\n";
		$num = $line['num'];
		$ext = $line['ext'];
		$description = $line['description'];
		$description = nl2br($description);
		print "<td align=center valign=top><div class=text2>/pics/pic$num.$ext</div>\n";
		print "<A HREF=javascript:ReturnValue('/pics/pic$num.$ext')><img src=pics/pic$num.$ext width=180 alt=\"$description\"></A>\n";
		print "<BR>$description<BR>\n";
		print "<A HREF=pics.php?gallery=$galurlencoded&action=edit&num=$num>ערוך</A>&nbsp;&nbsp;&nbsp;\n";
		print "<A HREF=pics.php?gallery=$galurlencoded&action=del&num=$num>מחק</A></TD>\n";
		$i++;
		if($i == 4) {
			$i = 0;
			print "</TR>\n";
		}
	}
	print "</TABLE>\n";

	print "<BR>\n";
	print "<H1 align=center>הוספת תמונה</H1>\n";

	print "<FORM enctype=multipart/form-data action=pics.php?action=add&gallery=$galurlencoded method=post>\n";
	print "<TABLE dir=RTL border=8 align=center><TR>\n";
	print "<TD>תמונה: </TD>\n";
	print "<TD><INPUT type=file name=pic></TD>\n";
	print "</TR><TR>\n";
	print "<TD>תיאור: </TD>\n";
	print "<TD><TEXTAREA name=description rows=5 cols=40></TEXTAREA></TD>\n";
	print "</TR><TR>\n";
	print "<TD colspan=2 align=center><INPUT type=submit value=שלח></TD>\n";
	print "</TR>\n";
	print "</TABLE>\n";
	print "</FORM>\n";
}

$query = "SELECT gallery FROM $picstbl GROUP BY 'gallery'";
$result = mysql_query($query);
if(!$result) {
	print "Query: $query<br>\n";
	echo mysql_error();
	exit;
}
$n = mysql_num_rows($result);

print "<form name=galsel method=get>\n";
print "<TABLE dir=RTL border=0 align=center>\n";
print "<tr><td>בחר גלריה: </td>\n";
print "<td>\n";
print "<select name=gallery onchange=formsubmit()>\n";
print "<option value=\"\">-- בחירת גלריה --</option>\n";
while($line = mysql_fetch_array($result, MYSQL_NUM)) {
	$gal = $line[0];
//	$galurlencoded = urlencode($gal);
	print "<option value=\"$gal\" ";
	if($gal == $gallery)
		print "selected";
	print ">$gal</option>\n";
}
print "</select>\n";
print "</td></tr>\n";
print "</table>\n";
print "</form>\n";

print "<BR><BR><BR>\n";
print "<FORM action=pics.php method=GET>\n";
print "<TABLE border=5 dir=RTL align=center>\n";
print "<TR><TD><H3 align=center>הוסף גלריה</H3></TD></TR>\n";
print "<TR>\n";
print "<TD>\n";
print "<INPUT type=text name=gallery>\n";
print "<INPUT type=submit value=הוסף>\n";
print "</TD></TR>\n";
print "</TABLE></FORM>\n";
?>
</BODY>
</HTML>
