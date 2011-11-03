<?PHP
/* Internal links dialog for freelance accounting system */
include('config.inc.php');

function DoQuery($query, $debugstr) {
	$result = mysql_query($query);
	if(!$result) {
		print "$debugstr Query: $query<br>\n";
		echo mysql_error();
		exit;
	}
	return $result;
}

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

function ShowCat() {
	global $articlestbl;
	global $num;
	global $maxlevel;
	global $path;
	global $priv, $ReqLevel;
	global $base, $mod_rewrite, $rewrite_opt;
	
	$query = "SELECT id,subject FROM $articlestbl ORDER BY subject";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		exit;
	}
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$n = $line['id'];
		$subject = $line['subject'];

		print "<a href=\"javascript:ReturnValue('${base}?id=$n')\">$subject</a><br>\n";
	}
}

$abspath = GetURI();
if(!isset($tinymcepath))
	$tinymcepath = $abspath;

$pageheader = <<< HEAD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<TITLE>בחירת קישור פנימי</TITLE>
<STYLE type=text/css>
body {margin:20px}

.para {display:none;
	padding-right:10px;
}
h1 {font-size:24px; font-weight:bold; font-family: arial, sans-serif; color: navy; text-align:center}
h2 {font-size:18px; font-weight:bold; font-family: arial, sans-serif; color: navy}
h3 {font-size:18px; font-weight:bold; font-family: arial, sans-serif; color: navy}
h4 {font-size:16px; font-family: times, new-roman; color:navy}
a:visited {color:blue; }
a:link {color:navy; font-family:arial, sans-serif; }
a:hover {color:red;}
</STYLE>
<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>
<SCRIPT Language=JavaScript type="text/javascript">
function ReturnValue(val) {
	var win = tinyMCE.getWindowArg("window");
	win.tinyMCE.setWindowArg('editor_id', 'mce_editor_0');
	win.document.getElementById(tinyMCE.getWindowArg("input")).value = val;
	tinyMCEPopup.close();
}
</SCRIPT>
<SCRIPT language=JavaScript type="text/javascript">
function blocking(nr, t) {
	var theIcon = document.getElementById(t);
	
	var current = (document.getElementById(nr).style.display == 'block') ? 'none' : 'block';
	document.getElementById(nr).style.display = current;
<?PHP
	print "\tif(current == 'none')\n";
	print "\t\t	theIcon.src = '${base}plus.gif'\n";
	print "\telse\n";
	print "\t\t	theIcon.src = '${base}minus.gif'\n";
?>
}
</SCRIPT>
</HEAD>
<BODY>
<H1 align=center>בחירת דף לקישור פנימי</H1>
<BR>
HEAD;

print "$pageheader\n";

$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_select_db($database) or die("Could not select database: $database");

$action = isset($_GET['action']) ? $_GET['action'] : '';
$src = isset($_GET['src']) ? $_GET['src'] : 'articles';

if(isset($_COOKIE[$cookiename]))
	$name = $_COOKIE[$cookiename];
if(isset($_GET["name"]))
	$name= $_GET['name'];

if(!isset($src))
	$src = 'articles';

if($src == 'articles') {
	print "<div dir=rtl style=\"text-align:right\">\n";
	ShowCat('', 0);
	print "</div>\n";
}
else {
	list($t, $n) = explode(',', $src);
	if($t == 'file') {
		$grp = urldecode($n);
		$query = "SELECT * FROM $filestbl WHERE grp='$grp'";
		$result = mysql_query($query);
		if(!$result) {
			print "Query: $query<BR>\n";
			echo mysql_error();
			exit;
		}

		print "<H1 align=center>$grp</H1>\n";
		print "<BR>\n";
		print "<div dir=rtl style=\"text-align:right;margin-right:30px\">\n";
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$description = $line['description'];
			$num = $line['num'];
			$ext = $line['ext'];
			print "<a href=\"javascript:ReturnValue('files/file$num.$ext')\">$description</a><br />\n";
		}
		print "</div>\n";
	}
	if($t == 'gal') {
		$gal = urldecode($n);
		$query = "SELECT * FROM $picstbl WHERE gallery='$gal'";
		$result = mysql_query($query);
		if(!$result) {
			print "Query: $query<BR>\n";
			echo mysql_error();
			exit;
		}

		print "<H1 align=center>$gal</H1>\n";
		print "<BR>\n";
		print "<TABLE border=1 dir=RTL align=center>\n";
		$i = 0;
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if($i == 0)
				print "<TR>\n";
			$num = $line['num'];
			$ext = $line['ext'];
			$description = $line['description'];
			$description = nl2br($description);
			print "<TD align=center valign=top><DIV class=text2>pics/pic$num.$ext</DIV>\n";
			print "<A HREF=\"javascript:ReturnValue('pics/pic$num.$ext')\"><IMG SRC=pics/pic$num.$ext width=180 alt=\"$description\"></A>\n";
			print "<BR>$description<BR>\n";
			print "</TD>\n";
			$i++;
			if($i == 4) {
				$i = 0;
				print "</TR>\n";
			}
		}
		print "</TABLE>\n";
	}
}
?>
</body>
</html>
