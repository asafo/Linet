<?PHP
/*
 | backup script for Drorit accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2009
 |
 | This program is a free software licensed under the GPL 
 */
include('config.inc.php');
if(!isset($prefix)) {
	if(isset($_COOKIE['company']))
		$prefix =  $_COOKIE['company'];
		
}
elseif ($prefix=='') echo "cannot backup without setting company";
$cwd = getcwd();
chdir("backup");
@mkdir($path."/backup/".$prefix);
chdir($path."/backup/".$prefix);


function BackupTable($fd, $tbl) {
	global $prefix;

	fwrite($fd, "-- Table: $tbl\n");
	fwrite($fd, "DELETE FROM $tbl WHERE prefix='$prefix';\n");
	/* Check if we have prefix field for table */
	$query = "SELECT * FROM $tbl WHERE prefix='$prefix';";
	$result = DoQuery($query, "BackupTable");
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		fwrite($fd, "INSERT INTO $tbl VALUES (");
		foreach($line as $key => $val) {
			if($key > 0)
				fwrite($fd, ", ");
			$val = addslashes($val);
			fwrite($fd, "'$val'");
		}
		fwrite($fd, ");\n");
	}
	fwrite($fd, "-- Done with table $tbl\n\n");
}

$step = isset($_GET['step']) ? $_GET['step'] : 'backup';

if($step == 'backup') {
	//print "<div class=\"form righthalf1\">\n";
	$header = _("Data backup");
	//print "<h3>$l</h3>\n";
	$bakname = date('Ymd');
	$bakname .= ".bak";
	$fd = fopen("$bakname", "w");
	$query = "SHOW TABLES";
	$result = mysql_query($query);
	if(!$result) {
		echo mysql_error();
		chdir($cwd);
		exit;
	}
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$tbl = $line[0];
		$q = "DESC $tbl 'prefix'";
		$r = DoQuery($q, "BackupTable");
		$n = mysql_num_rows($r);
		if($n) {
			$l = _("Backuping table");
			$text.= "$l: $tbl<br>\n";
			BackupTable($fd, $tbl);
		}
	}
	$l = _("Backup done");
	$text.= "<h2>$l</h2>\n";
	$l = _("Click here to download backup file");
	$text.= "<h2><a href=\"download.php?file=$bakname&amp;name=$bakname\" target=\"_blank\">$l</a></h2>\n";
	//print "</div>\n";
	createForm($text,$header,'',350);
	print "<div class=\"lefthalf1\">\n";
	ShowText('backup');
	print "</div>\n";
}

if($step == 'delbak') {
	$fname = $_GET['file'];
	unlink($fname);
	$step = 'restore';
	$l = _("File deleted successfully");
	print "<br><h1>$l</h1>\n";
}
if($step == 'dorestore') {
	if(isset($_GET['file'])) {
		$fname = $_GET['file'];
		if(file_exists($fname)) {
			$lines = file($fname);
			foreach($lines as $query) {
				if(trim($query) == '')
					continue;
	//			print "Query: $query<br>\n";
				$result = mysql_query($query);
				if(!$result) {
					print "<div dir=\"ltr\">\n";
					print "Query: $query<br>\n";
					echo mysql_error();
					print "</div>\n";
					chdir($cwd);
					exit;
				}
			}
		}
	}
	else {
		$size = $_FILES['bakname']['size'];
		if($size == 0) {
			$l = _("Error transfering file or empty file");
			ErrorReport("$l");
			exit;
		}
		$fname = $_FILES['bakname']['tmp_name'];
		$orgname = $_FILES['bakname']['name'];
		$lines = file($fname);
		foreach($lines as $query) {
			if(trim($query) == '')
				continue;
//			print "Query: $query<br>\n";
			$result = mysql_query($query);
			if(!$result) {
				print "<div dir=\"ltr\">\n";
				print "Query: $query<br>\n";
				echo mysql_error();
				print "</div>\n";
				exit;
			}
		}
	}
	$l = _("Data restored successfully");
	print "<br><h1>$l</h1>\n";
	$step = 'restore';
}
if($step == 'restore') {
	
	//print "<div class=\"form righthalf1\">\n";
	$header = _("Restore data from backup");
	//print "<h3>$l</h3>\n";
	$text.= "<form enctype=\"multipart/form-data\" action=\"?module=backup&amp;step=dorestore\" method=\"post\">\n";
	$text.= "<table width=\"100%\" class=\"formtbl\"><tr>\n";
	$l = _("Backup file");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"file\" name=\"bakname\"></TD>\n";
	$l = _("Execute");
	$text.= "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td></tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
	
	$text.= "<br>\n";
	$l = _("Choose file from server");
	$text.= "<h3>$l</h3>\n";
	
	$text.= "<table width=\"100%\"><tr class=\"tblhead\">\n";
	$l = _("File");
	$text.=  "<td style=\"width:10em;text-align:right\" align=\"right\">$l</td>\n";
	$l = _("Actions");
	$text.= "<td>$l</td>\n";
	$dh = opendir(".");
	while(($file = readdir($dh)) !== false) {
		if(!is_dir("$file")) {
			$text.= "<tr><td dir=\"ltr\" align=\"right\">$file</td>\n<td>";
			$l = _("Restore");
			$text.= "<a href=\"?module=backup&amp;step=dorestore&amp;file=$file\">$l</a>\n";
			$l = _("Delete");
			$text.= "&nbsp;&nbsp;<a href=\"?module=backup&amp;step=delbak&amp;file=$file\">$l</a>\n";
			$text.= "</td></tr>\n";
		}
	}
	$text.= "</table>\n";
	//print "</div>\n";
	createForm($text,$header,'',350);
	print "<div class=\"lefthalf1\">\n";
	ShowText('restore');
	print "</div>\n";
}

chdir($cwd);
?>

