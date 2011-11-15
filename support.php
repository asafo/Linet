<?PHP
/*
 | Support module for Drorit accounting system
 | Written by Ori Idan helicon technologies Ltd.
 */

$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: he\r\n" .
              "Cookie: my=sharona\r\n"
  )
);

$context = stream_context_create($opts);
$url='http://www.linet.org.il/index.php/support/paid-support';
$file = file_get_contents($url, false, $context);
//
$file=substr($file,strpos($file,'<div class="item-page">'));

$file=substr($file,0,strpos($file,'</div>'));
createForm($file, _("Paid support"),'',750,'','',1,getHelp());
//print($file);

?>

