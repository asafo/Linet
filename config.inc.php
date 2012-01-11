<?PHP
/*
 | Configuration file for Linet
 */
//dbConfig
$host = 'localhost';
$user = 'root';
$pswd = 'passbla';
$database = 'linet';

$path = '/var/www/linet1.3';
$serverpath='http://172.22.102.20/linet1.3';

$sendMailAddress="support@linet.org.il";


//pdf command only change this if you know what you are doing
$wkhtmltopdfstr="xvfb-run -a -s \"-screen 0 1024x768x16\" wkhtmltopdf";
?>