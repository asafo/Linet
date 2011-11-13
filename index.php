<?PHP
/*
 | Drorit accounting system version 2.0
 | Written by Ori Idan
 | Modfied By Adam BH
 */
/* 
 | Cookies must be sent first so check if someone wants us to send cookie...
 */
session_start();
if(isset($_GET['begin']) && isset($_GET['end'])) {
	$begindmy = $_GET['begin'];
	$enddmy = $_GET['end'];
	setcookie('begin', $begindmy, time() + 24 * 3600);
	setcookie('end', $enddmy, time() + 24 * 3600);
}
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$module = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';

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
include('include/i18n.inc.php');
include('config.inc.php');
include('include/core.inc.php');
include('include/version.inc.php');
include('include/func.inc.php');
include('class/user.php');
include('class/company.php');
$Uname = isset($_COOKIE['name']) ? $_COOKIE['name'] : '';
$Udata = isset($_COOKIE['data']) ? $_COOKIE['data'] : '';
$Sname = isset($_SESSION['name']) ? $_SESSION['name'] : '';
$Sdata = isset($_SESSION['data']) ? $_SESSION['data'] : '';


$prefix =  isset($_COOKIE['company']) ? $_COOKIE['company'] :'';
$prefix = isset($_GET['company']) ? $_GET['company'] : $prefix;

$stdheader = '';
$abspath = GetURI();
if(!isset($tinymcepath))//adam:?
	$tinimcepath = $abspath;


if(($module == '') && ($action == '')) 
		$module = 'main';


$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");
mysql_select_db($database) or die("Could not select database: $database");
$loggedin = 0;
/*chk if logged in improved */
if(isset($Uname) && ($Uname != '')) 
	if((isset($Udata)) && ($Udata!='')){
		//if session isnt set get from db
		//adam: current user
		$name = urldecode($Uname);
		$curuser=new user;
		$curuser->name=$name;
		$curuser->getUser();
		if($Sdata=='') $Sdata=$curuser->cookie;
		if($Sname=='') $Sname=$curuser->name;
		if(($Uname==$Sname) && ($Udata==$Sdata)){
			$loggedin = 1;
			
			$_SESSION['name']=$Uname;
			$_SESSION['data']= $Udata;
			$cookietime = time() + 60*60*24*30;
			//chk if user has permisions to company
			setcookie('company', $prefix, $cookietime);
		}
	}

$curcompany= new company;
$curcompany->prefix=$prefix;
if(!$curcompany->getCompany()){
	setcookie('company', '', -1);
	unset($_COOKIE['company']);
	unset($prefix);
}

$title = $curcompany->companyname;
$template = $curcompany->template;
$logo=$curcompany->logo;
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
	//print "Name: $name, $superuser<br>\n";
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
	//$query = "SELECT companyname,template,logo FROM $companiestbl WHERE prefix='$prefix'";
	//$result = DoQuery($query, "main");
	
	//if(mysql_num_rows($result)) {
		//$line = mysql_fetch_array($result, MYSQL_ASSOC);
		//$title = //$line['companyname'];
		//$template = $line['template'];
		//$logo=$line['logo'];
	//}
	//else
		//unset($prefix);
}
//if($cssfile == '')
	$cssfile = 'style/linet.css';
//if($small_logo == '')
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

//print 'were here';
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
	global $logintbl, $permissionstbl;
	global $name, $prefix;
	global $loggedin, $superuser;
	global $ModuleAction;
	//global $menuprinted;
	//print $module;
	if(!$loggedin) {
		include('login.php');
		return '';
	}
	$btype = browser_info(NULL);
		if(file_exists("$module.php")) {
			require('shurtcut.php');
			require("$module.php");					
			return "";
		}
		$l = _("Module not implemented yet");
		return "<h1>$l</h1>\n";
	
}

function TemplateReplace($r) {
	global $title, $cssfile, $small_logo;
	global $Version, $softwarenameheb;
	global $top, $sub;
	global $name, $prefix, $lang,$logo;
	global $MainMenu;
	global $module;
	global $stdheader, $action;
	global $logintbl, $permissiontbl;
	global $articlestbl;
	global $id;
	global $loggedin, $simulatenolog, $superuser;

	$p = str_replace('~', '', $r[0]);
	/*if($p == 'header') {
		//print "$stdheader\n";
		if($module == 'text') {
			if(($action != 'edit') && ($action != 'add'))
				return '';
			require_once("inittinymce.inc.php");
		}
		return $tinymce;
	}*/
	if($p =='updatepop'){
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
	else if($p=='complogo')
		return '<a href="?module=main"><img src="img/logo/'.$logo.'" alt="Company Logo" height=80/></a>';
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
			return "<a href=\"index.php?action=disconnect\"><img src=\"img/icon_logout.png\" alt=\"icon logout\" />$l</a>\n";
		}
	}
	else if($p == 'recomendfirefox')
		return RecomendFirefox();
	else if($p == 'isoc')	
		return isocDiv();
	else if($p == 'osi')
		return osiDiv();
	else if($p == 'username') {
		$name1 = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];
		$name1 = urldecode($name1);
		$query = "SELECT fullname FROM $logintbl WHERE name='$name1'";
		$result = DoQuery($query, __LINE__);
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$username = _("Hello").":".stripslashes($line[0]);
		$username .= "&nbsp;|&nbsp;";
		if($superuser) {
			$l = _("Change company");
			$username .= "<a href=\"index.php?action=unsel\">$l</a>\n";
			$username .= "&nbsp;|&nbsp;";
		}
		return "$username\n";
	}

	else if($p=='MainMenu') {
		include_once 'include/menu.inc.php';
		return $str;
	}
	/*else if($MainMenu[$p]) {
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
	}*/
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