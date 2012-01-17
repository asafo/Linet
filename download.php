<?PHP
$file_path = str_replace('/','',$_GET['file']);
$file_path=str_replace(".csv","",$file_path);
$asfname = $_GET['name'];

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=\"$asfname\"");
header("Content-Transfer-Encoding: binary");
//header("Content-Length: " . $fsize);

// download
// @readfile($file_path);
$file = @fopen("tmp/$file_path.csv","rb");
if ($file) {
  while(!feof($file)) {
    print(fread($file, 1024*8));
    flush();
    if (connection_status()!=0) {
      @fclose($file);
      die();
    }
  }
  @fclose($file);
}
?>

