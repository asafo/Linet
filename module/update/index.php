<?php
/*update writen by Adam BH*/
include '../../config.inc.php';
include '../../include/core.inc.php';
include '../../include/version.inc.php';
$sversion=getVersion();
$steps = array( 1 => 'בדיקת גירסה',
				2 => 'גיבויי',
				3 => 'עדכון',
				4  => 'סיום');
$allowcancel=true;

$step=1;

if(isset($_POST['step'])) $step=$_POST['step'];
if(isset($_GET['step'])) $step=$_GET['step'];
$name = isset($_COOKIE['name']) ? $_COOKIE['name'] : '';
$data = isset($_COOKIE['data']) ? $_COOKIE['data'] : '';
if($step<>count($steps)){
	$nextStep=$step+1;
}else{
	$nextStep=0;
}
$loggedin=false;
if(!empty($name) && ($name != ''))  {
	//$loggedin = 1;
	//$name = urldecode($name);
	global $permissionstbl;
	$name = urldecode($name);
	$query = "SELECT * FROM $permissionstbl WHERE name='$name' AND company='*'";
	$link = mysql_connect($host,$user,$pswd);
	mysql_select_db($database,$link);
	$result = mysql_query($query);
	//print($query);
	if ($row=mysql_fetch_array($result,MYSQL_ASSOC)){
	//print_r($row);
	
		$loggedin = true;}
	else {
		if (isset($_GET['non']))
			print 'למשתמש אין הרשאה לעדכון אנא פנה למנהל המערכת על מנת לבצע עדכון';
		
	}
}
else{
	//$loggedin = 0;
	if (isset($_GET['non']))
		print _("אתה חייב להיכנס למערכת על מנת לבצע עדכון")."<br />".'<a href="../../">חזור ללינט</a>';
	//print ;
	$step=0;
	$nextStep=0;
	}
//print $name.$data;
//print $step<>count($steps);
/*load page*/
$title="עדכון לינט: ".$steps[$step];
if ($loggedin){
if (($step==1)&&(isset($_GET['non']))){
	$nextStep=2;
	print "ברוך הבא לאשף העדכון של לינט גירסת המערכת שלך: ".$version."<br /> הגרסה העדכנית ביותר: ".$sversion."<br />מומלץ לעדכן את המערכת";
	//if ($nextStep) print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>הבא</a>";//bla
	print '<br /><a href="javascript:loadDoc('.$nextStep.')">'._("הבא").'</a>';
	}else{
	$content=_("אנא המתן");
}
/*backup*/
if (($step==2)&&(isset($_GET['non']))){
	include 'Backup.php';
	if (is_writeable($path."/backup")){
			print "מגבה מערכת ומסד נתונים אני מבקשים לשמור עותק מקומי של הגיבויי לפני כל עדכון.<br />";
			print "מגבה קבצי מערכת<br />";
			/*delete old files*/
			if ($handle = opendir($path."/backup")) {
				while (false !== ($file = readdir($handle))) {
					if((strpos($file, ".zip")) || (strpos($file, ".sql")))
						unlink($path."/backup/".$file);

				}
			}
			//unlink($myFile);
			Zip($path."/", $path.'/backup/files'.date('dmY').'.zip');
			print "סיים<br />";
			print "<a href=../../backup/files".date('dmY').".zip>הורד קבצי מערכת</a><br />";
			print "מגבה מסד נתונים<br />";
			$bkfile = $path.'/backup/db'.date('dmY').'.sql';//'.date('Ymd').'
			dbBackup($bkfile);
			print "סיים<br />";
			print "<a href=../../backup/db".date('dmY').".sql>הורד מסד נתונים</a><br />";
		}else{
			print "תקלה: בדוק הרשאות מערכת קבצים לתקייה backup.<br />";
			$nextStep=0;
		}
		if ($nextStep) print '<a href="javascript:loadDoc('.$nextStep.')">הבא</a>';
		//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>הבא</a>";//bla
}
/*update*/
if (($step==3)&&(isset($_GET['non']))){
	print "מבקש רשימת קבצים לעדכון.<br />";
	$nextStep=0;
	
	print "בוחן הרשאות.<br />";
	$logfile=$path."/tmp/updatelog".date('dmY').'.txt';
	if (permisionChk($logfile)){
		$log = fopen($logfile, 'w') or die("can't open file");
		fwrite($log, "Start Loging: ".date('d/m/y H:m')."\n");
		$updatefile=GetList($sversion);
		$safty=true;
	}else{
		$safty=false;
		print "תקלה: לא ניתן לכתוב קובץ LOG.<br />";
	}
	foreach ($updatefile as $value){
		$value=$path."/".$value;
		if(permisionChk($value)){
		
		}else{
				$safty=false;
				print "-אנא בדוק אישורי כתיבה עבור: ".$value.".<br />";
			}
	}

	if ($safty){//update all the files
		print "סיים בדיקת הרשאות. <br />התחלת עדכון קבצים:<br />";
		foreach ($updatefile as $value){
			//log: trying to ge file
			fwrite($log, "-מבקש קובץ:".$value."\n");
			$file=getFile($value,$sversion);
			$value=$path."/".$value;
			//log: writing file
			
			print "+כותב קובץ: $value<br />";
			$fh = fopen($value, 'w') or die("can't open file");
			fwrite($log, "+Wrote file: ".$value."\n");
			
			//fputs($fh,$file,strlen($file)); //dosnt write
			fwrite($fh, $file);//^better large files support
			fclose($fh);
		}
		//log:end
		fwrite($log, "finshed updating files"."\n");
		print "סיים כתיבת קבצים.<br />";
		/*update db*/
		$command=getSQL();
		if($command<>''){
			//connect mysql server
			fwrite($log, "Connect to MySql Server"."\n");
			$link = mysql_connect($host,$user,$pswd);
			//select db
			fwrite($log, "Select Database"."\n");
			mysql_select_db($database,$link);
			//run query
			fwrite($log, "Run query: ".$command."\n");
			$result = mysql_query($command);
			//log updated
			fwrite($log, "Finshed sqling"."\n");
		}else{
		fwrite($log, "no sql"."\n");
		//no command no need to sql
		}
    
		
	}else{
		print "Can Not Contnie Withot!<br />";
	}
	fwrite($log, "End Loging: ".date('d/m/y H:m')."\n");
	fclose($log);
	$nextStep=4;
	if ($nextStep) print '<a href="javascript:loadDoc('.$nextStep.')">'._("הבא").'</a>';
	//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>הבא</a>";//bla
}
/*end*/
if (($step==4)&&(isset($_GET['non']))){
print "לינט עודכנה בהצלחה אפש לראות את יומן העדכון ";
print '<a href="../../tmp/updatelog'.date('dmY').'.txt">פה.</a><br />';
print '<a href="../../">חזרה ללינט</a>';
}
}


/*documenet*/
if (!isset($_GET['non']))
	include('look.php');
/*

*/


function getSQL($version){
global $updatesrv;
	//print $updatesrv.'?GetSql&Version='.$version;
	if ($fp = fopen($updatesrv.'?GetSql&Version='.$version, 'r')) {
		$content = fread($fp, 1024);
		while ($line = fread($fp, 1024)) {
			$content .= $line;
		}
	}
	return $content;
}
function getFile($fileName, $version){
	global $updatesrv;
	//print $updatesrv.'?GetFile='.$fileName.'&Version='.$version;
	if ($fp = fopen($updatesrv.'?GetFile='.$fileName.'&Version='.$version, 'r')) {
		$content = fread($fp, 1024);
		while ($line = fread($fp, 1024)) {
			$content .= $line;
		}
	}
	//$filelist=explode('<br />',$content);
	//$a=array_pop($filelist);
	return base64_decode($content);
}
function getVersion(){
	global $updatesrv;
	//print $updatesrv.'?GetLateset';
	if ($fp = fopen($updatesrv.'?GetLateset', 'r')) {
   $content = fread($fp, 1024);
   // keep reading until there's nothing left
   /*while ($line = fread($fp, 1024)) {
      $content .= $line;
   }*/
   return $content;
	}
}
function getList($version){
	global $updatesrv;
	//print $updatesrv.'?GetList&Version='.$version;
	if ($fp = fopen($updatesrv.'?GetList&Version='.$version, 'r')) {
		$content = fread($fp, 1024);
		while ($line = fread($fp, 1024)) {
			$content .= $line;
		}
	}
	$filelist=explode('<br />',$content);
	$a=array_pop($filelist);
	return $filelist;
}
function permisionChk($filename){
	if(file_exists($filename)){
		if(is_writeable($filename)){
			return true;
		} else {
			return false;
		}
	}else{
		print dirname($filename);
		if(is_writeable(dirname($filename))){
			return true;
		}else{
			return false;
		}
		
	}
}
?>