<?PHP
/*
 | Drorit accounting system version 2.0
 | Written by Ori Idan
 | Modfied By Adam BH
 */
/* 
 | Cookies must be sent first so check if someone wants us to send cookie...
 */
if(isset($_GET['cookie'])) {
	$cookiestr = $_GET['cookie'];
	$cookies = explode(':', $cookiestr);
	foreach($cookies as $cookie) {
		//print "cookie: $cookie<BR>\n";
		list($name, $val, $t) = explode(',', $cookie);
		$val = urlencode($val);
		setcookie($name, $val, $t);
	}
}

include('include/i18n.inc.php');

//print(md5('Replay'));

if(isset($_GET['begin']) && isset($_GET['end'])) {
	$begindmy = $_GET['begin'];
	$enddmy = $_GET['end'];
	setcookie('begin', $begindmy, time() + 24 * 3600);
	setcookie('end', $enddmy, time() + 24 * 3600);
}
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if($action == 'disconnect') {
	setcookie('name', '', -1);
	setcookie('data', '', -1);
	setcookie('company', '', -1);
	unset($_COOKIE['name']);
	unset($_COOKIE['data']);
	unset($_COOKIE['company']);
	$action = '';
}
else if($action == 'unsel') {
	setcookie('company', '', -1);
	unset($_COOKIE['company']);
	unset($action);
	unset($_GET['action']);
	$module = 'main';
}
//header('Content-type: text/html;charset=UTF-8');

include('config.inc.php');
include('include/core.inc.php');
include('include/version.inc.php');
include('include/func.inc.php');
include('include/edit.inc.php');
include('class/user.php');

//include('include/menu.inc.php');
$name = isset($_COOKIE['name']) ? $_COOKIE['name'] : '';
$data = isset($_COOKIE['data']) ? $_COOKIE['data'] : '';
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : $name;
$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : $data;
$showtext = isset($_COOKIE['showtext']) ? $_COOKIE['showtext'] : 1;
$showtext = isset($_GET['showtext']) ? $_GET['showtext'] : $showtext;
if(isset($_COOKIE['company'])) {
	$prefix =  $_COOKIE['company'];
	// print "Select company: $prefix<br />\n";
}
$prefix = isset($_GET['company']) ? $_GET['company'] : $prefix;

$stdheader = '';
$abspath = GetURI();
if(!isset($tinymcepath))
	$tinimcepath = $abspath;

if(!empty($name) && ($name != '')) {
	$loggedin = 1;
	$name = urldecode($name);
}
else
	$loggedin = 0;
if(isset($_GET['nonlogin']))
	$simulatenolog = 1;

// print "logged: $loggedin<br>name: $name<br>\n";

$id = isset($_GET['id']) ? $_GET['id'] : 0;
if($id)
	$module = 'text';
else {
	$module = isset($_GET['module']) ? $_GET['module'] : '';
}

if(($module == '') && ($action == '')) {
	if($loggedin && !$simulatenolog)
		$module = 'main';
	else {
		$id = 'main1';
		$module = 'text';
//		$action = 'login';
	}
}
// $action = isset($_GET['action']) ? $_GET['action'] : '';

if($module == '') {	/* check for default actions */
	if($action == 'doadd')
		$module = 'text';
}

$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");
mysql_select_db($database) or die("Could not select database: $database");
//adam: current user
$curuser=new user;
$curuser->name=$name;
$curuser->getUser();

if($loggedin) {
	$query = "SELECT * FROM $permissionstbl WHERE name='$name'";
	$result = DoQuery($query, "index.php");
	if(mysql_num_rows($result) == 0) {
		$demouser = 1;
		$prefix = 'demo';
		$name = 'demo';
	}
	else {
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$c = $line['company'];
		if($c == '*')
			$superuser = 1;
		else
			$prefix = $c;
	}
//	print "Name: $name, $superuser<br>\n";
}
//print "<br />".$action."<br />";
if ($action=='lister'){
		include('lister.php');
		die;//return '';
	}


if (isset($_COOKIE['cheaked'])) {$cheaked=true;} else {$cheaked=false;}
if (isset($_COOKIE['sversion'])) {$sVersion=$_COOKIE['sversion'];} else {$sVersion=getVersion(); }
$_SESSION['updatepop']=false;
//print 'help'.$_COOKIE['cheaked'];
if (!$cheaked){
		//$sVersion=getVersion();
	if($version<$sVersion){
		$_SESSION['updatepop']=true;
		setcookie('cheaked', 12, time() + 24 * 3600);//die;
		setcookie('sversion', $sVersion, time() + 24 * 3600);//die;
	}else{
		if($sVersion=='-1'){
			print 'Unable To Connect Update Server';
			//die;
		}
	}
}

/* Make sure we have a valid company and set $title */
if(isset($prefix)) {
	$query = "SELECT companyname,template,logo FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "main");
	if(mysql_num_rows($result)) {
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$title = $line['companyname'];
		$template = $line['template'];
		$logo=$line['logo'];
	}
	else
		unset($prefix);
}
if($cssfile == '')
	$cssfile = 'style/linet.css';
if($small_logo == '')
	$small_logo = 'img/logo.jpg';

if($template == '') {
	if($lang == 'he') {
		if(!$loggedin)
			$template = "nonreg.html";
		else
			$template = "template.html";
	}
	else {
		if(!$loggedin)
			$template = "nonreg_ltr.html";
		else
			$template = "template_ltr.html";
	}
}


/*

*/

function url_exists($url) {
    $hdrs = @get_headers($url);
    //print_r($hdr);
    return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
} 
function getVersion(){
	global $updatesrv;
	//print $updatesrv.'?GetLateset';
	if (url_exists($updatesrv.'?GetLateset')){
		$fp = fopen($updatesrv.'?GetLateset', 'r');
		$content = fread($fp, 1024);
		return $content;
	}else{
		return -1;
	}
   
   // keep reading until there's nothing left
   /*while ($line = fread($fp, 1024)) {
      $content .= $line;
   }*/
   

}

function ShowText($id,$print=true) {
	global $articlestbl;
	global $lang, $dir;
	global $superuser;
	global $menuprinted;
	global $module;
	global $showtext;
	$text='';
	if($dir == 'ltr')
		$align = 'left';
	else
		$align = 'right';
	/*adam: option to edit*/
	if($module != 'text')
		if(($module != 'text') && EditAble($id)) {
			$l = _("Edit");
			$text.= "<span class=\"text1\"><a href=\"?id=$id&amp;action=edit\">$l</a></span><br />\n";
		}
	$query = "SELECT contents,subject FROM $articlestbl WHERE id='$id' AND lang='$lang'";
	$result = DoQuery($query, "ShowText");
	if(mysql_num_rows($result) == 0) {
		/* Check default of no language */
		$query = "SELECT contents,subject FROM $articlestbl WHERE id='$id'";
		$result = DoQuery($query, "ShowText");
		if(mysql_num_rows($result) == 0) {
			$l = _("Page does not exist {id}:$id" );
			$text.= "<h1 align=\"center\">$l</h1>\n";
			if($module != 'text')
				return '';
		}
	}
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$contents = $line['contents'];
	
	$a = explode(':', $contents);
	if(($k = array_search('plugin', $a)) !== FALSE) {
		for($i = 0; $i < $k; $i++) {
			$text.= $a[$i];
			if($i != ($k - 1))
				$text.= ":";
		}
		$p = trim($a[$k + 1]);

		$after = $a[$k + 2];
		include("plugins/$p");
		for($i = $k + 2; $i < count($a); $i++) {
			$text.= $a[$i];
			if($i != (count($a) - 1))
				$text.= ":";
		}
		if($module != 'text')
			$text.= "</div>\n";
		return '';
	}
	$str = "";

	$arr = explode('\n', $contents);
	foreach($arr as $l) {
		$str .= preg_replace_callback("/~[^\x20|^~]*~/", "TemplateReplace", $l);
	}
	$text.= "$str";
	if ($print) {
		print $text;
		return "";
	}
	else
	{
		return $text;
	}

}

function browser_info($agent=null) {
  // Declare known browsers to look for
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
    'konqueror', 'gecko');

  // Clean up agent and build regex that matches phrases for known browsers
  // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
  // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

  // Find all phrases (or return empty array if none found)
  if (!preg_match_all($pattern, $agent, $matches)) return array();

  // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
  // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
  // in the UA).  That's usually the most correct.
  $i = count($matches['browser'])-1;
  return array($matches['browser'][$i] => $matches['version'][$i]);
}

function RunModule() {
	global $module, $action, $id, $lang;
	global $articlestbl, $logintbl, $permissionstbl;
	global $name, $prefix;
	global $loggedin, $superuser;
	global $ModuleAction;
	global $menuprinted;
	
	if(($action == 'login') || ($action == 'dologin') || (!$loggedin)) {
		include('login.php');
		return '';
	}
	$btype = browser_info(NULL);
//	print_r($btype);
//	if($btype['msie']) {
//	}
//	print "module: $module, id: $id<br>\n";
//	if($action && ($module == 'compass'))
//		$module = $ModuleAction[$action];
//	print "module: $module, id: $id<br>\n";
	if($module == 'text') {		/* built in module */
		/* Special case for edit actions */
		if($action == 'doadd') {
			$id = $_POST['id'];
			$ancestor = $_GET['ancestor'];
			$modname = htmlspecialchars($_POST['module'], ENT_QUOTES);
			$params = htmlspecialchars($_POST['params'], ENT_QUOTES);
			$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
			$contents = $_POST['contents'];
			$keepedit = isset($_POST['keepediting']);

			if($id == '__NULL__') {
				$l = _("No identifying name specidied");
				print "<H1 align=\"center\">$l</H1>\n";
				exit;
			}
			else if($id == '__NEW__')
				$id = $_POST['newid'];
			$id1 = urlencode($id);
			if($id1 != $id) {
				$l = _("Invalid identifying name");
				print "<H1 align=\"center\">$l</H1>\n";
				exit;
			}
			$query = "SELECT * FROM $articlestbl WHERE id='$id'";
			$result = DoQuery($query, "index.php");
			if(mysql_num_rows($result) == 0) {
				$query = "INSERT INTO $articlestbl  \n";
				$query .= "VALUES('$id', '$ancestor', '$lang', '$subject', '$modname', '$params', NOW(), '$contents')";
				$result = DoQuery($query, "index.php");
			}
			else {
				$query = "UPDATE $articlestbl SET $subject='$subject', lastmode=NOW(), ";
				$query .= "module='$modname', params='$params', ";
				$query .= "contents='$contents' lang='$lang' WHERE id='$id'";
				$result = DoQuery($query, "index.php");
			}
			if($keepedit)
				$action = 'edit';
		}
		else if(($action == 'update') && isset($_POST['id'])) {
			$id = $_POST['id'];
			$ancestor = $_GET['ancestor'];
			$newid = $_POST['newid'];
			$modname = htmlspecialchars($_POST['module'], ENT_QUOTES);
			$params = htmlspecialchars($_POST['params'], ENT_QUOTES);
			$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
			$contents = $_POST['contents'];
			$keepedit = isset($_POST['keepediting']);

			$query = "SELECT * FROM $articlestbl WHERE id='$id' AND lang='$lang'";
			$result = DoQuery($query, "index.php");
			if(mysql_num_rows($result) == 0) {	/* special case, add article */
				$query = "SELECT * FROM $articlestbl WHERE id='$id'";
//				print "Query1: $query<br>\n";
				$result = DoQuery($query, "index.php");
				if(!mysql_num_rows($result)) {
					$query = "INSERT INTO $articlestbl  \n";
					$query .= "VALUES('$id', '$ancestor', '$lang', '$subject', '$modname', '$params', NOW(), '$contents')";
				}
				else {
					$query = "UPDATE $articlestbl SET id='$newid', lang='$lang', ";
					$query .= "subject='$subject', module='$modname', params='$params', lastmod=NOW(), ";
					$query .= "contents='$contents' WHERE id='$id'";
				}
			}
			else {
				$query = "UPDATE $articlestbl SET id='$newid', lang='$lang', ";
				$query .= "subject='$subject', module='$modname', params='$params', lastmod=NOW(), ";
				$query .= "contents='$contents' WHERE id='$id'";
			}
			$result = mysql_query($query);
			if(!$result) {
				print "Query: $query<BR>\n";
				echo mysql_error();
				exit;
			}
			if($keepedit)
				$action = 'edit';
			$id = $newid;
		}
		else if($action == 'del') {
			$query = "DELETE FROM $articlestbl WHERE id='$id'";
			$result = DoQuery($query, "DelPage");
		}

		$menuprinted = 0;
		$nocommands = array('add', 'edit', 'login');
		if(!in_array($action, $nocommands)) {
			$str = PrintCommands();
			$menuprinted = 1;
			print $str;
		}
		if(($action == 'add') || ($action == 'edit')) {
			$ancestor = isset($_GET['ancestor']) ? $_GET['ancestor'] : '';
			AddEdit($action, $id, $ancestor);
			return;
		}
		ShowText($id);
		return "";
	}
	else {
		if(file_exists("$module.php")) {
//			print "module: $module.php<br>\n";
			if(!isset($name) || ($name == '')) {
				if(($module != 'contactus') && ($module != 'demoreg') && ($module != 'defs') && ($module != 'login')) {
					print "<br><div style=\"text-align:center\">\n";
					print "<h3>" . _("This module can not be used without logging in") . "</h3>";
					print "<br>\n";
					$l = _("Login to Linet");
					print "<h2><a href=\"?action=login\">$l</a></h2>";
					$url = "index.php";
					print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=$url\">\n";
					print "</div>\n";
					return "";
				}
			} 
			require("$module.php");					
			return "";
		}
		$l = _("Module not implemented yet");
		return "<h1>$l</h1>\n";
	}
}

function TemplateReplace($r) {
	global $title, $cssfile, $small_logo;
	global $Version, $softwarenameheb;
	global $top, $sub;
	global $name, $prefix, $lang;
	global $MainMenu;
	global $module;
	global $stdheader, $action;
	global $logintbl, $permissiontbl;
	global $articlestbl;
	global $id;
	global $loggedin, $simulatenolog, $superuser;

	$p = str_replace('~', '', $r[0]);
	if($p == 'header') {
		//print "$stdheader\n";
		if($module == 'text') {
			if(($action != 'edit') && ($action != 'add'))
				return '';
			require_once("inittinymce.inc.php");
		}
		return $tinymce;
	}
	else if($p =='updatepop'){
		//global 
		//$updatepop=true;//rethink
		if ($_SESSION['updatepop']) {
			print '
			<div id="dialog-confirm" title="'._("Update Notice").'">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'._("You are working on an old Version of Linet you must update").'</p>
			</div>
			<script>
				$(function() {
					$( "#dialog:ui-dialog" ).dialog( "destroy" );
					$( "#dialog-confirm" ).dialog({
						resizable: false,height:140,modal: true,
						buttons: {
							"'._("Update Now").'": function() {
								$( this ).dialog( "close" );
								window.location.replace("module/update/");
							},
							"'._("no thanks").'": function() {
								$( this ).dialog( "close" );
							}
						}
					});
				});
			</script>';
		}
		global $version,$sVersion;
		if($version<$sVersion){
			$format=_('Working in a ');
			$link='<a href="module/update">'._('OLD Version').'</a>';
			print $format.$link;
		}
	}
	else if($p == 'text') {
		return RunModule();
	}
	else if($p == 'title') {
		return $title;
	}
	else if($p == 'css')
		return $cssfile;
	else if($p == 'logo')
		return $small_logo;
	else if($p == 'version') {
		return $Version;
	}
	else if($p == 'softwarenameheb') {
		return $softwarenameheb;
	}
	else if($p == 'login') {
		if(!$loggedin) {
			$l = _("Log in to system");
			return "<a href=\"index.php?action=login\">$l</a>\n";
		}
		else {
			$l = _("Logout");
			return "<a href=\"index.php?action=disconnect\">$l</a>\n";
		}
	}
	else if($p == 'recomendfirefox')
		return RecomendFirefox();
	else if($p == 'isoc')
		return '<div class="isoc"><a href="http://www.isoc.org.il"><img src="img/isoc_logo.png" alt="isoc logo" /></a><br />'._('This software is supported by ISOC').'</div>';	
	else if($p == 'username') {
		$name1 = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];
		$name1 = urldecode($name1);
		$query = "SELECT fullname FROM $logintbl WHERE name='$name1'";
		$result = DoQuery($query, __LINE__);
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$username = stripslashes($line[0]);
		$username .= "&nbsp;|&nbsp;";
		if($superuser) {
			$l = _("Select company");
			$username .= "<a href=\"index.php?action=unsel\">$l</a>\n";
			$username .= "&nbsp;|&nbsp;";
		}
		if(EditAble('')) {
			$l = _("Content management");
			$username .= "<a href=\"index.php?module=edit\">$l</a>\n";
			$username .= "&nbsp;|&nbsp;";
		}
		return "$username\n";
	}
//	print "loggedin: $loggedin<br>\n";
/*	if(!$loggedin || $simulatenolog) {	// not logged in, act as CMS
		$query = "SELECT subject FROM $articlestbl WHERE id='$p'";
//		print "Query: $query<BR>\n";
		$result = DoQuery($query, "TemplateReplace");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$d = $line[0];
		if($simulatenolog)
			return "<a href=\"${base}?id=$p&amp;nonlogin=1\">$d</a>\n";
		else
			return "<a href=\"${base}?id=$p\">$d</a>\n";
	}*/
	else if($p=='MainMenu') {
		include_once 'include/menu.inc.php';
		//return print_r($MainMenu,true);
		return $str;
	}
	else if($MainMenu[$p]) {
		$query = "SELECT subject,module,params FROM $articlestbl WHERE id='$p'";
		$query .= " AND lang='$lang'";
		$result = DoQuery($query, __LINE__);
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		if(mysql_num_rows($result)) {
			$modname = $line['module'];
			$params = $line['params'];
			$subject = $line['subject'];
			if($modname) {
				$url = "?module=$modname";
				if($params)
					$url .= "&amp;$params";
			}
			else
				$url = "?id=$p";
			return "<a href=\"$url\">$subject</a>\n";
		}
	}
	return "";
}

$file = fopen($template, "r");
if(!$file) {
	print "Unable to open: $template<BR>\n";
}
//print 'we start';
while(!feof($file)) {
	$str = fgets($file, 1024);
	
	$new = preg_replace_callback("/~[^\x20|^~]*~/", "TemplateReplace", $str);
	print $new;
}
fclose($file);
//if(!$loggedin) include('login.php');
?>