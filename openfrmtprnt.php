<?PHP
/*
 | Print open format report
 | Written by Ori Idan for freelance accounting software
 */
 
$prefix = $_GET['prefix'];
$reptitle = "הפקת קבצים במבנה אחיד";
include('printhead.inc.php');
print $header;

//

readfile("tmp/$prefix.html");

print "</body>\n</html>\n";

?>

