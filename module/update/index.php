<?php
/*update writen by Adam BH*/
include '../../config.inc.php';
include '../../include/core.inc.php';
include '../../include/version.inc.php';
$sversion=getVersion();
$steps = array( 1 => _('Version Verify'),
				2 => _('Backup'),
				3 => _('Update'),
				4  => _('Finish'));
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
			print 'This User has no permssion to update the system';
		
	}
}
else{
	//$loggedin = 0;
	if (isset($_GET['non']))
		print _("You Must login to Update the system")."<br />".'<a href="../../">׳—׳–׳•׳¨ ׳�׳�׳™׳ ׳˜</a>';
	//print ;
	$step=0;
	$nextStep=0;
	}
//print $name.$data;
//print $step<>count($steps);
/*load page*/
$title=_("Linet Update: ").$steps[$step];
if ($loggedin){
if (($step==1)&&(isset($_GET['non']))){
	$nextStep=2;
	print _("Welcome To Linet update wizard your system version is: ").$version."<br />"._("The current version is: ").$sversion."<br />"._("You need to update your system.");
	//if ($nextStep) print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳”׳‘׳�</a>";//bla
	print '<br /><a href="javascript:loadDoc('.$nextStep.')">'._("Next").'</a>';
	}else{
	$content=_("Please Wait");
}
/*backup*/
if (($step==2)&&(isset($_GET['non']))){
	include 'Backup.php';
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
			print _("Finished")."<br />";
			print "<a href=../../backup/files".date('dmY').".zip>"._("Downlod System Files")."</a><br />";
			print "׳�׳’׳‘׳” ׳�׳¡׳“ ׳ ׳×׳•׳ ׳™׳�<br />";
			$bkfile = $path.'/backup/db'.date('dmY').'.sql';//'.date('Ymd').'
			dbBackup($bkfile);
			print "׳¡׳™׳™׳�<br />";
			print "<a href=../../backup/db".date('dmY').".sql>׳”׳•׳¨׳“ ׳�׳¡׳“ ׳ ׳×׳•׳ ׳™׳�</a><br />";
		}else{
			print "׳×׳§׳�׳”: ׳‘׳“׳•׳§ ׳”׳¨׳©׳�׳•׳× ׳�׳¢׳¨׳›׳× ׳§׳‘׳¦׳™׳� ׳�׳×׳§׳™׳™׳” backup.<br />";
			$nextStep=0;
		}
		if ($nextStep) print '<a href="javascript:loadDoc('.$nextStep.')">׳”׳‘׳�</a>';
		//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳”׳‘׳�</a>";//bla
}
/*update*/
if (($step==3)&&(isset($_GET['non']))){
	print "׳�׳‘׳§׳© ׳¨׳©׳™׳�׳× ׳§׳‘׳¦׳™׳� ׳�׳¢׳“׳›׳•׳�.<br />";
	$nextStep=0;
	
	print "׳‘׳•׳—׳� ׳”׳¨׳©׳�׳•׳×.<br />";
	$logfile=$path."/tmp/updatelog".date('dmY').'.txt';
	if (permisionChk($logfile)){
		$log = fopen($logfile, 'w') or die("can't open file");
		fwrite($log, "Start Loging: ".date('d/m/y H:m')."\n");
		$updatefile=GetList($sversion);
		$safty=true;
	}else{
		$safty=false;
		print "׳×׳§׳�׳”: ׳�׳� ׳ ׳™׳×׳� ׳�׳›׳×׳•׳‘ ׳§׳•׳‘׳¥ LOG.<br />";
	}
	foreach ($updatefile as $value){
		$value=$path."/".$value;
		if(permisionChk($value)){
		
		}else{
				$safty=false;
				print "-׳�׳ ׳� ׳‘׳“׳•׳§ ׳�׳™׳©׳•׳¨׳™ ׳›׳×׳™׳‘׳” ׳¢׳‘׳•׳¨: ".$value.".<br />";
			}
	}

	if ($safty){//update all the files
		print "׳¡׳™׳™׳� ׳‘׳“׳™׳§׳× ׳”׳¨׳©׳�׳•׳×. <br />׳”׳×׳—׳�׳× ׳¢׳“׳›׳•׳� ׳§׳‘׳¦׳™׳�:<br />";
		foreach ($updatefile as $value){
			//log: trying to ge file
			fwrite($log, "-׳�׳‘׳§׳© ׳§׳•׳‘׳¥:".$value."\n");
			$file=getFile($value,$sversion);
			$value=$path."/".$value;
			//log: writing file
			
			print "+׳›׳•׳×׳‘ ׳§׳•׳‘׳¥: $value<br />";
			$fh = fopen($value, 'w') or die("can't open file");
			fwrite($log, "+Wrote file: ".$value."\n");
			
			//fputs($fh,$file,strlen($file)); //dosnt write
			fwrite($fh, $file);//^better large files support
			fclose($fh);
		}
		//log:end
		fwrite($log, "finshed updating files"."\n");
		print "׳¡׳™׳™׳� ׳›׳×׳™׳‘׳× ׳§׳‘׳¦׳™׳�.<br />";
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
	if ($nextStep) print '<a href="javascript:loadDoc('.$nextStep.')">'._("׳”׳‘׳�").'</a>';
	//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳”׳‘׳�</a>";//bla
}
/*end*/
if (($step==4)&&(isset($_GET['non']))){
print "׳�׳™׳ ׳˜ ׳¢׳•׳“׳›׳ ׳” ׳‘׳”׳¦׳�׳—׳” ׳�׳₪׳© ׳�׳¨׳�׳•׳× ׳�׳× ׳™׳•׳�׳� ׳”׳¢׳“׳›׳•׳� ";
print '<a href="../../tmp/updatelog'.date('dmY').'.txt">׳₪׳”.</a><br />';
print '<a href="../../">׳—׳–׳¨׳” ׳�׳�׳™׳ ׳˜</a>';
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