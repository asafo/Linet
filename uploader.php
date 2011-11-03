<?php
if(isset($_POST['upload']) && $_FILES['uploadedfile']['size'] > 0)
{
$fileName = $_FILES['uploadedfile']['name'];
$tmpName  = $_FILES['uploadedfile']['tmp_name'];
$fileSize = $_FILES['uploadedfile']['size'];
$fileType = $_FILES['uploadedfile']['type'];

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
$content = addslashes($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}

//include 'library/config.php';
//include 'library/opendb.php';

//$query = "INSERT INTO upload (name, size, type, content ) "."VALUES ('$fileName', '$fileSize', '$fileType', '$content')";

//mysql_query($query) or die('Error, query failed');
//include 'library/closedb.php';

echo "<br>File $fileName uploaded<br>";
}
?>