<?PHP
/*
 | Linet accounting system version 2.0
 | Written by Ori Idan
 | Modfied By Adam BH
 */
/* 
 | Cookies must be sent first so check if someone wants us to send cookie...
 */
session_start();
if((!isset($_COOKIE['begin']))&&(!isset($_COOKIE['end']))){
	setcookie('begin', date('1-1-Y'), time() + 24 * 3600);
	setcookie('end', date('d-m-Y'), time() + 24 * 3600);
}

if(isset($_GET['begin']) && isset($_GET['end'])) {
	$begindmy = $_GET['begin'];
	$enddmy = $_GET['end'];
	setcookie('begin', $begindmy, time() + 24 * 3600);
	setcookie('end', $enddmy, time() + 24 * 3600);
}

include('include/i18n.inc.php');
include('config.inc.php');
include('include/core.inc.php');
include('include/version.inc.php');
include('include/func.inc.php');
require_once('class/user.php');
require_once('class/company.php');

$loggedin=isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : false;

//$prefix = isset($_COOKIE['company']) ? $prefix=$_COOKIE['company'] :$prefix='';
//$prefix = isset($_SESSION['company']) ? $prefix=$_SESSION['company'] :$prefix=$prefix;

//$_SESSION['']=$prefix;
//					 action
$action = GetPoster('action');
$module = GetPoster('module');
$begin =GetPoster('begin');//isset($_REQUEST['begin']) ? $_REQUEST['begin'] : '';
$end = GetPoster('end');
if($begin=='')$begin="1-1-".date("Y");
if($end=='')$end=date('d-m-Y');

$stdheader = '';
$abspath = GetURI();

if(($module == '') && ($action == '')) 
	$module = 'main';


$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
mysql_query("SET NAMES 'utf8'");
mysql_select_db($database) or include 'setup.php';//die("Could not select database: $database");



$name=isset($_COOKIE['name'])?$name=$_COOKIE['name'] :$name='';
$data=isset($_COOKIE['data'])?$data=$_COOKIE['data'] :$data='';
$name=isset($_SESSION['name'])?$name=$_SESSION['name']:$name=$name;
$data=isset($_SESSION['data'])?$data=$_SESSION['data']:$data=$data;

$curuser=new user();
//if session data =cookie data
//print("<br />user: $name <br $curcompany/>data: $data<br />");
$curuser->name=$name;
$curuser->data=$data;
$curuser->login($name,null,$data);

$prefix = isset($_GET['company']) ? $prefix=$_GET['company'] : $prefix=$prefix;
$curcompany=unserialize($_SESSION['company']);
if(!isset($_SESSION['company'])||($prefix!=$curcompany->prefix)){
	if($prefix!=''){
		$curcompany= new company;
		$curcompany->prefix=$prefix;
		if(!$curcompany->getCompany()){
			setcookie('company', '', -1);
			unset($_COOKIE['company']);
			unset($prefix);
		}
		$_SESSION['company']=serialize($curcompany);
	}
	
}


$name=$curuser->name;

$prefix=$curcompany->prefix;
$title = $curcompany->companyname;
$template = $curcompany->template;
$logo=$curcompany->logo;
if($loggedin) {
	$query = "SELECT * FROM $permissionstbl WHERE name='$name'";
	$result = DoQuery($query, "index.php");
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$c = $line['company'];
		if($c == '*')
			$superuser = 1;
		//else
			//$prefix = $c;//*/
	}

if($action == 'disconnect') {
	$curuser->logout();
}

else if($action == 'unsel') {
	setcookie('company', '', -1);
	unset($_COOKIE['company']);
	unset($_SESSION['company']);
	unset($action);
	unset($_GET['action']);
	$module = 'main';
	//print "<meta http-equiv=\"refresh\" content=\"0;url=?\" />";
	$curcompany->companyname='';
	$prefix='';
	//exit;
}
	
	
	
if ($action=='lister'){
		include('lister.php');
		exit;
	}

if(isset($_GET['ismobile'])){
	$ismobile=$_GET['ismobile'];
}else{
	if(isset($_SESSION['ismobile']))
		$ismobile=$_SESSION['ismobile'];
	else
		$ismobile=isMobile();
}
$_SESSION['ismobile']=$ismobile;
//print $ismobile;
if($ismobile==1){
	include('mobile/index.php');
	exit;
}
$cheaked=isset($_COOKIE['cheaked'])?$cheaked=true:$cheaked=false;
$sVersion=isset($_COOKIE['sversion'])?$sVersion=$_COOKIE['sversion']:$sVersion=getVersion(); 
setcookie('sversion', $sVersion, time() + 24 * 3600);
$_SESSION['updatepop']=false;
if (!$cheaked){
	if($version<$sVersion){
		$_SESSION['updatepop']=true;
		
		setcookie('cheaked', true, time() + 24 * 3600);//die;
		setcookie('sversion', $sVersion, time() + 24 * 3600);//die;
	}else{
		if(($sVersion=='-1')&&(!isset($_SESSION['conctionerror']))){
			//print 'Unable To Connect Update Server';
			$_SESSION['conctionerror']=12;
			$title1=_("The system cannot connect to Linet update server");
			$msg=_("Israeli tax authorities must have the ability to update the system (through our Linet update server) in order to comply with new tax regulations. Avoiding access to Linet update server, undermines the status of Linet 2.0 instance of yours as a legal and approved system for computerized book keeping system in Israel.
Kindly refer to the section at Linet manual for resolving this connectivity issue");
			$msg1="<a href=\"http://www.linet.org.il/index.php/support/user-help-navigate?id=96\" target=\"_blank\">"._("Here")."</a>";
			$servernotice= '
			<div id="dialog-confirm" title="'._("Server Notice").'">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span>'.$title1.'</span><br />'.$msg."&nbsp;".$msg1.'</p>
			</div>
			<script>
				$(document).ready(function(){
					$( "#dialog:ui-dialog" ).dialog( "destroy" );
					$( "#dialog-confirm" ).dialog({
						resizable: false,height:200,width:300,//modal: true,
						buttons: {
							"'._("Ok").'": function() {
								$( this ).dialog( "close" );
							}//,
							//"'._("no thanks").'": function() {
							//	$( this ).dialog( "close" );
							//}
						}
					});
				});
			</script>';
			//die;
		}
	}
}


$cssfile = 'style/linet.css';
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
}

function browser_info($agent=null) {
  // Declare known browsers to look for
 // $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape','konqueror', 'gecko');

  $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	//print $agent;//,
	//print ":".strstr ($agent,"msie").":";
 if((strstr($agent,"msie 6.0"))||(strstr($agent,"msie 7.0"))){
 	if(!isset($_SESSION['wrongbrowser'])){
 			$_SESSION['wrongbrowser']=true;
  			$title1=_("The system has noticed that you try to use linet accounting with unsupported browser. Kindly refer to the list of the supported browsers");
  			$msg="<a target=\"_blank\" href=\"http://www.linet.org.il/index.php/support/user-help-navigate?id=91\">"._("Here")."</a>";
  			$msg1=_(". and upgrade your browser accordingly.");
			
			print '
			<div id="dialog-confirm" title="'._("Server Notice").'">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span>'.$title1.'</span><br />'.$msg.$msg1.'</p>
			</div>
			<script>
				$(function() {
					$( "#dialog:ui-dialog" ).dialog( "destroy" );
					$( "#dialog-confirm" ).dialog({
						resizable: false,height:200,width:300,//modal: true,
						buttons: {
							"'._("Ok").'": function() {
								$( this ).dialog( "close" );
							},
							"'._("no thanks").'": function() {
								$( this ).dialog( "close" );
							}
						}
					});
				});
			</script>';
 	}
}
  
  
  
  return "";
}
function isMobile(){
	$mobile_ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	$mobile_agents = array('iphone','ipad','android');
	//print "got it:$mobile_ua";
	foreach($mobile_agents as $agent)
		if (strstr($mobile_ua,$agent)) {
		    return 1;
		}
 	return 0;	
}
function RunModule() {
	global $module, $action, $id, $lang;
	global $logintbl, $permissionstbl;
	global $name, $prefix;
	global $loggedin, $superuser;
	global $ModuleAction;
	global $menuprinted;
	//print $module;
	if(!$loggedin) {
		include('login.php');
		return '';
	}
	//print_r($_POST);print_r($_GET);
	$btype = browser_info(NULL);
		if(file_exists("$module.php")) {
			require('shurtcut.php');
			//print("were r we? $module");
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
	global $curuser;
	global $module;
	global $stdheader, $action;
	global $logintbl, $permissiontbl;
	global $articlestbl;
	global $id;
	global $loggedin, $simulatenolog, $superuser;

	$p = str_replace('~', '', $r[0]);
	if($p =='updatepop'){
		global $servernotice;
		print $servernotice;
		//global 
		//$updatepop=true;//rethink
		if ($_SESSION['updatepop']) {
			$title=_("Your Linet accounting version is obsolete");
			$msg=_("Working with un updated version undermines the status of Linet 2.0 instance of yours as a legal and approved system for computerized book keeping system in Israel.
Kindly execute Linet update wizard here in order to resolve this issue ");
			$msg1="<a href=\"module/update\">"._("Here")."</a>";
			print '
			<div id="dialog-confirm" title="'._("Update Notice").'">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span>'.$title.'</span><br />'.$msg."&nbsp;".$msg1.'</p>
			</div>
			<script>
				$(function() {
					$( "#dialog:ui-dialog" ).dialog( "destroy" );
					$( "#dialog-confirm" ).dialog({
						resizable: false,height:200,width:300,modal: true,
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
			print "<div class=\"warning\">$format $link</div>";
		}
	}
	else if($p == 'text') {
		return RunModule();
	}
	else if($p == 'title') {
		return '';//$title;
	}
	else if($p == 'css')
		return $cssfile;
	else if($p == 'logo')
		return $small_logo;
	else if($p=='complogo'){
		if($logo!='')
			if(file_exists("img/logo/$logo"))
				return '<a href="?module=main"><img src="img/logo/'.$logo.'" alt="'.$title.'" height="80" /></a>';
		return "<a href=\"?module=main\"><img src=\"\" alt=\"\" height=\"60\" /><h1>$title</h1></a>";
	}else if($p == 'version') {
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
			return "<a href=\"?ismobile=1\">"._("Mobile")."</a>&nbsp;|&nbsp;<a href=\"index.php?action=disconnect\"><img src=\"img/icon_logout.png\" alt=\"icon logout\" />$l</a>\n";
		}
	}
	else if($p == 'recomendfirefox')
		return RecomendFirefox();
	else if($p == 'isoc')	
		return isocDiv();
	else if($p == 'osi')
		return osiDiv();
	else if($p == 'username') {
		//$name = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];
		$name = $curuser->name;
		$query = "SELECT fullname FROM $logintbl WHERE name='$name'";
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