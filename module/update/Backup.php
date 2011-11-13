<?php
/*Writen by Adam BH*/
function dbBackup($filename){
	//include '../../config.inc.php';
	global $host;
	global $user;
	global $pswd;
	global $database;
	$tables = '*';
	  $link = mysql_connect($host,$user,$pswd);
	  mysql_select_db($database,$link);
	  //get all of the tables
	  if($tables == '*'){
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result)) {
		  $tables[] = $row[0];
		}
	  }else{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	  }
	  
	  //cycle through
	  foreach($tables as $table){
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);
		
		$return.= 'DROP TABLE '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++){
		  while($row = mysql_fetch_row($result)){
			$return.= 'INSERT INTO '.$table.' VALUES(';
			for($j=0; $j<$num_fields; $j++){
			  $row[$j] = addslashes($row[$j]);
			  $row[$j] = ereg_replace("\n","\\n",$row[$j]);
			  if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
			  if ($j<($num_fields-1)) { $return.= ','; }
			}
			$return.= ");\n";
		  }
		}
		$return.="\n\n\n";
	  }
	  $handle = fopen($filename,'w') or die("can't open file");
	  fwrite($handle,$return);
	  fclose($handle);
}


function Zip($source, $destination){
if (extension_loaded('zip') === true){
	if (file_exists($source) === true){
		$zip = new ZipArchive();
		if ($zip->open($destination, ZIPARCHIVE::CREATE) === true){
			$source = realpath($source);
			if (is_dir($source) === true){
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
				foreach ($files as $file){
					$file = realpath($file);
					if (is_dir($file) === true){
						$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
					}  else if (is_file($file) === true){
						$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
					}
				}
			}
			else if (is_file($source) === true){
				$zip->addFromString(basename($source), file_get_contents($source));
			}
		}
		return $zip->close();
	}
}
return false;
}

?>