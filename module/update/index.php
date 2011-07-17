<?php
/*update writen by Adam BH*/
include '../../config.inc.php';
$sversion=getVersion();
$steps = array( 1 => '׳‘׳“׳™׳§׳× ׳’׳™׳¨׳¡׳”',
				2 => '׳’׳™׳‘׳•׳™׳™',
				3 => '׳¢׳“׳›׳•׳�',
				4  => '׳¡׳™׳•׳�');
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
if(!empty($name) && ($name != '')) {
	$loggedin = 1;
	$name = urldecode($name);
}
else{
	$loggedin = 0;
	print _("You must be loged in to update linet")."<br />";
	print '<a href="../../">׳—׳–׳•׳¨ ׳�׳�׳™׳ ׳˜</a>';
	$step=0;
	$nextStep=0;
	}
//print $name.$data;
//print $step<>count($steps);
/*load page*/
$title="׳¢׳“׳›׳•׳� ׳�׳™׳ ׳˜: ".$steps[$step];
	
if (($step==1)&&(isset($_GET['non']))){
	$changelog=getFile('changelog', $sversion);
	$nextStep=2;
	print "Welcome to linet Update Wizard youre version is: ".$version."<br /> The Most Recent Version is: ".$sversion."<br />It is recomnded thet youll update to lataset version<br />";
	print $changelog.'<br />';//if ($nextStep) print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳”׳‘׳�</a>";//bla
	print '<br /><a href="javascript:loadDoc('.$nextStep.')">'._("Next").'</a>';
	}else{
	$content=_("Working Please Wait");
}
/*backup*/
if (($step==2)&&(isset($_GET['non']))){
	include 'Backup.php';
	if (is_writeable($path."/backup")){
			print "Backing up files and data base it is highly recomnded thet you will download the files and save them in a known place before each update.<br />";
			print "Saving Files<br />";
			/*delete old files*/
			if ($handle = opendir($path."/backup")) {
				while (false !== ($file = readdir($handle))) {
					if((strpos($file, ".zip")) || (strpos($file, ".sql")))
						unlink($path."/backup/".$file);

				}
			}
			//unlink($myFile);
			Zip($path."/", $path.'/backup/files'.date('dmY').'.zip');
			print "Done<br />";
			print "<a href=../../backup/files".date('dmY').".zip>Download The Zip</a><br />";
			print "Dumping Data Base<br />";
			$bkfile = $path.'/backup/db'.date('dmY').'.sql';//'.date('Ymd').'
			dbBackup($bkfile);
			print "Done<br />";
			print "<a href=../../backup/db".date('dmY').".sql>Download The DataBase</a><br />";
		}else{
			print "Unable to Write in the backup folder chek permsions.<br />";
			$nextStep=0;
		}
		if ($nextStep) print '<a href="javascript:loadDoc('.$nextStep.')">׳”׳‘׳�</a>';
		//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳”׳‘׳�</a>";//bla
}
/*update*/
if (($step==3)&&(isset($_GET['non']))){
	print "Get Update File list.<br />";
	$nextStep=0;
	
	print "Cheking File Permisions.<br />";
	$logfile=$path."/tmp/updatelog".date('dmY').'.txt';
	if (permisionChk($logfile)){
		$log = fopen($logfile, 'w') or die("can't open file");
		fwrite($log, "Start Loging: ".date('d/m/y H:m')."\n");
		$updatefile=GetList($sversion);
		$safty=true;
	}else{
		$safty=false;
		print "Unable To log wont update.<br />";
	}
	foreach ($updatefile as $value){
		$value=$path."/".$value;
		if(permisionChk($value)){
		
		}else{
				$safty=false;
				print "-Chek write permision for: ".$value." or the folder.<br />";
			}
	}

	if ($safty){//update all the files
		print "Done Cheking Permisons. <br />Start Updating Files:<br />";
		foreach ($updatefile as $value){
			//log: trying to ge file
			fwrite($log, "-Get file:".$value."\n");
			$file=getFile($value,$sversion);
			$value=$path."/".$value;
			//log: writing file
			
			print "+Writing: $value<br />";
			$fh = fopen($value, 'w') or die("can't open file");
			fwrite($log, "+Wrote file: ".$value."\n");
			
			//fputs($fh,$file,strlen($file)); //dosnt write
			fwrite($fh, $file);//^better large files support
			fclose($fh);
		}
		//log:end
		fwrite($log, "finshed updating files"."\n");
		print "Done Updating Files.<br />";
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
	if ($nextStep) print '<a href="javascript:loadDoc('.$nextStep.')">'._("Next").'</a>';
	//print "<a href=javascript:postwith('index.php',{step:'".$nextStep."'})>׳”׳‘׳�</a>";//bla
}
/*end*/
if (($step==4)&&(isset($_GET['non']))){
print "Linet Has Been Updated you can review the ";
print '<a href="../../tmp/updatelog'.date('dmY').'.txt">logs here.</a><br />';
print '<a href="../../">׳—׳–׳¨׳” ׳�׳�׳™׳ ׳˜</a>';
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
	return $content;
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