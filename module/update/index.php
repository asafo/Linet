<?php
/*update writen by Adam BH*/
session_start();
//$_GET['lang'] = 'he';
$update=1;
include '../../include/i18n.inc.php';
include '../../config.inc.php';

include '../../include/core.inc.php';
include '../../include/version.inc.php';
$sversion=getVersion();
$steps = array( 1 => _('Version Verify'),
				2 => _('Backup'),
				3 => _('Update'),
				4  => _('Finish'));
$allowcancel=true;
//$cookietime = time() + 60*60*24*30;
//setcookie("lang", 'he', $cookietime);

$step=1;

if(isset($_POST['step'])) $step=$_POST['step'];
//if(isset($_GET['step'])) $step=$_GET['step'];
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
		$loggedin = true;
	}else
		if (isset($_POST['non']))	print _('This User has no permssion to update the system');
}elseif (isset($_POST['non'])){
		print _("You Must login to Update the system")."<br />".'<a href="../../">׳³ג€”׳³ג€“׳³ג€¢׳³ֲ¨ ׳³ן¿½׳³ן¿½׳³ג„¢׳³ֲ ׳³ֻ�</a>';
	//print ;
	$step=0;
	$nextStep=0;
	}
	
if (isset($_POST['non'])){
	
		
	print "<ul>";
		foreach ($steps as $name){
			$i++;
			if ($name==$steps[$step]){
					print '<il class="active"><img src="../../img/btnUpdateActive.png" alt="step" /><p class="num">'.$i.'</p><p>'.$name.'</p></il>';
				}else{
					print '<il><img src="../../img/btnUpdate.png" alt="step" /><p class="num">'.$i.'</p><p>'.$name.'</p></il>';
				}
		}
	print "</ul>";						
	
	
}
//print $name.$data;
//print $step<>count($steps);
/*load page*/
$title=_("Linet Update ");
if ($loggedin){
if (($step==1)&&(isset($_POST['non']))){
	$nextStep=2;
	print "<div class=\"updatetext\">";
	print _("Welcome To Linet update wizard your system version is: ").$version."<br />"._("The current version is: ").$sversion."<br />"._("You need to update your system.");
	print "</div>";
	//if ($nextStep) print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳³ג€�׳³ג€˜׳³ן¿½</a>";//bla
	
	print '<div class="control"><a class="btnaction" href="../../">'._("Cancel").'</a>';
	print '<a class="btnaction" href="javascript:loadDoc('.$nextStep.')">'._("Next").'</a></div>';
	}else{
		$content=_("Please Wait");//error
}
/*backup*/
if (($step==2)&&(isset($_POST['non']))){
	include 'Backup.php';
	print "<div class=\"updatetext\">";
	if (is_writeable($path."/backup")){
			print _("Backup system and database, it is heighly advised to save a local copy of the bakup files")."<br />";
			print _("Backuping system files")."...<br />";
			/*delete old files*/
			if ($handle = opendir($path."/backup")) {
				while (false !== ($file = readdir($handle))) {
					if((strpos($file, ".zip")) || (strpos($file, ".sql")))
						unlink($path."/backup/".$file);
				}
			}
			//unlink($myFile);
			Zip($path."/", $path.'/backup/files'.date('dmY').'.zip');
			print _("Done")."<br />";
			print "<a href=../../backup/files".date('dmY').".zip>"._("Downlod System Files")."</a><br />";
			print _("Backuping database")."...<br />";
			$bkfile = $path.'/backup/db'.date('dmY').'.sql';//'.date('Ymd').'
			dbBackup($bkfile);
			print _("Done")."<br />";

			print "<a href=../../backup/db".date('dmY').".sql>"._("Download Database file")."</a><br />";
		}else{
			print "Error: Unable to write into Backup folder please check permissions<br />";
			$nextStep=0;
		}
	print "</div>";	
				print '<div class="control"><a class="btnaction" href="../../">'._("Cancel").'</a>';
	//if ($nextStep)
	 print '<a class="btnaction" href="javascript:loadDoc('.$nextStep.')">'._("Next").'</a></div>';
		//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳³ג€�׳³ג€˜׳³ן¿½</a>";//bla
}
/*update*/
if (($step==3)&&(isset($_POST['non']))){
	print _("aksing file list for update")."<br />";
	$nextStep=0;
	
	print _("checking permissions").".<br />";
	$logfile=$path."/tmp/updatelog".date('dmY').'.txt';
	if (permisionChk($logfile)){
		$log = fopen($logfile, 'w') or die("can't open file");
		fwrite($log, "Start Loging: ".date('d/m/y H:m')."\n");
		$updatefile=GetList($sversion);
		$safty=true;
	}else{
		$safty=false;
		print _("Error: cant write log file").".<br />";
	}
	foreach ($updatefile as $value){
		$value=$path."/".$value;
		if(permisionChk($value)){
		
		}else{
				$safty=false;
				print _("-Please check write permissions to: ").$value.".<br />";
			}
	}

	if ($safty){//update all the files
		print _("Finished permission check")."<br />"._("Begin updating files")."<br />";
		foreach ($updatefile as $value){
			//log: trying to ge file
			fwrite($log, "-GetFile:".$value."\n");
			$file=getFile($value,$sversion);
			$value=$path."/".$value;
			
			print "+Writing file: $value<br />";
			fwrite($log, "+Writing file: ".$value."\n");
			$fh = fopen($value, 'w') or die("can't open file");
			fwrite($log, "+Wrote file: ".$value."\n");

			fwrite($fh, $file);//^better large files support
			fclose($fh);
		}
		//log:end
		
		fwrite($log, "Finished updating files"."\n");
		print _("Finished updating files").".<br />";
		/*update db*/
		print _("aksing database update")."<br />";
		fwrite($log, "Get database update"."\n");
		$command=getSQL();
		fwrite($log, "Got DB updae"."\n");
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
		print _("Can Not Continue Withot!")."<br />";
	}
	fwrite($log, "End Loging: ".date('d/m/y H:m')."\n");
	fclose($log);
	$nextStep=4;
	if ($nextStep) print '<a class="btnaction" href="javascript:loadDoc('.$nextStep.')">'._("Next").'</a>';
	//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳³ג€�׳³ג€˜׳³ן¿½</a>";//bla
}
/*end*/
if (($step==4)&&(isset($_POST['non']))){
print _("Linet has been successfully updated")."<br />";
print '<a href="../../tmp/updatelog'.date('dmY').'.txt">'._("log file").'</a><br />';
print '<a class="btnaction" href="../../">'._("Finished").'</a>';
}
}


/*documenet*/
if (!isset($_POST['non']))
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