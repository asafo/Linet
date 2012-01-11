<?PHP
/*
 | I18N initialization
 | Written by: Ori Idan
 */
$iface_lang="he_IL";
$lang = 'he';
$dir = "rtl";
	
	
$accept_lang = " " . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
// print "HTTP_ACCEPT_LANGUAGE: $accept_lang<br />\n";
if(strpos($accept_lang, "he") > 0)
	$lang = 'he';
if(isset($_SESSION['lang']))
	$lang = $_SESSION['lang'];
if(isset($_COOKIE['lang']))
	$lang = $_COOKIE['lang'];
if(isset($_GET['lang']))
	$lang = $_GET['lang'];
	
$_SESSION['lang'] = $lang;
if(!setcookie("lang", $lang, time() + 3600 * 24 * 30))
	print "Set lang cookie failed<br/>\n";

 //$lang = 'he'; 	// default language is hebrew 
//print "lang: $lang<br />\n";
if($lang == 'he') {
	$dir = "rtl";
	$iface_lang="he_IL";

}
else {
	$dir = "ltr";
	$iface_lang="en_US";
}

$txt_domain = 'messages';
//print $iface_lang;
setlocale(LC_ALL, $iface_lang);


@putenv('LANG='.$iface_lang);
//@putenv('LANGUAGE='.$iface_lang);

textdomain($txt_domain);
if(isset($update))
	bindtextdomain($txt_domain, "../../locale");
else
	bindtextdomain($txt_domain, "./locale");
bind_textdomain_codeset($txt_domain, 'UTF-8');
?>