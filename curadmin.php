<?PHP
/*
 | Currency administration utility for Drorit free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
$ReqLevel = 1;
global $ratestbl, $currencytbl;
//global $dir;
/*
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('drorit.inc.php');
	include('func.inc.php');

	$sql_link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");

	print "<html>\n";
	print "<head>\n";
	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
	$l = _("Drorit Free accounting software - Currency rate management");
	print "<title>$l</title>\n";
	print "<link type=\"text/css\" rel=\"stylesheet\" href=\"style/drorit.css\">\n";
	print "<script type=\"text/javascript\" src=\"style/calendar_is.js\"></script>\n";
	print "<link rel=\"stylesheet\" href=\"style/calendar.css\">\n";
	print "</head>\n";
	print "<body>\n";
}*/

function UpdateRate($date, $key, $value) {
	global $ratestbl;

	if($value == 0)
		return;
	$date = FormatDate($date, "dmy", "mysql");
	/* first check if we already have rate for this date */
	$query = "DELETE FROM $ratestbl WHERE date=$date AND curnum='$key'";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<BR>\n";
		echo mysql_error();
		print "<BR>\n";
	}
	$query = "INSERT INTO $ratestbl VALUES('$key', '$date', '$value')";
	$result = mysql_query($query);
	if(!$result) {
		print "Query: $query<BR>\n";
		echo mysql_error();
		exit;
	}
}


$action = isset($_GET['action']) ? $_GET['action'] : 'rate';

if($action == 'updaterate') {
	$date = htmlspecialchars($_POST['date'], ENT_QUOTES);
	foreach($_POST as $key => $value) {
		if($key != 'date') {
			UpdateRate($date, $key, $value);
		}
	}
//	$date = FormatDate($date, "mysql", "dmy");
	$action = 'rate';	/* continue to update values */
}
if($action == 'newcurrency') {	/* Add new currency */
	$name = $_POST['name'];
	$name = htmlspecialchars($name, ENT_QUOTES);
	$name = str_replace(';', ' ', $name);

	$sign = htmlspecialchars($_POST['sign'], ENT_QUOTES);
	if($name != '') {
		$query = "INSERT INTO $currencytbl (name, sign) VALUES('$name', '$sign')";
		$result = DoQuery($query, __LINE__);
	}
	$action = 'rate';
}
if($action == 'delcurrency') {
	$num = (int)$_GET['num'];
	
	$query = "DELETE FROM $currencytbl WHERE curnum='$num'";	
	$result = DoQuery($query, __LINE__);
	$query = "DELETE FROM $ratestbl WHERE curnum='$num'";
	$result = DoQuery($query, __LINE__);
	$action = 'rate';
}

if($action == 'rate') {
	if(isset($_GET['date']))
		$date = $_GET['date'];
	if(empty($date)) {
		$today = getdate();
		$month = $today['mon'];
		$day = $today['mday'];
		$year = $today['year'];
		$date = "$year-$month-$day";
	}
	else {
		list ($day, $month, $year) = split ('[/.-]', $date);
		$date = "$year-$month-$day";
	}
	//print "<br />\n";
	//if(isset($module))
//		print "<div class=\"form righthalf1\">\n";
	$l = _("Currency rates");
	//print "<h3 style=\"text-align:right\"> $l </h3>";
	$header=$l;
	$text='';
	if(!isset($module))
		$text.= "<form name=\"form1\" action=\"curadmin.php?action=updaterate\" method=\"post\">\n";
	else
		$text.= "<form name=\"form1\" action=\"?module=curadmin&amp;action=updaterate\" method=\"post\">\n";
	$text.= "<table border=\"0\" dir=\"rtl\" width=\"100%\" class=\"formtbl\"><tr>\n";
	$l = _("Date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" id=\"date\" name=\"date\" size=\"7\" value=\"$day-$month-$year\">";

	$text.= '<script type="text/javascript">addDatePicker("#date","'.$day.'-'.$month.'-'.$year.'");</script>';

	$text.= "</td></tr><tr>\n";

	$text.= "<td colspan=\"2\">\n";
	$text.= "<table class=\"tablesorter\" ><thead><tr>\n";
	/* table headers */
	$l = _("Currency");
	$text.= "<th class=\"header\" style=\"width:10em\">$l</th>\n";
	$l = _("Sign");
	$text.= "<th class=\"header\" style=\"width:8em\">$l</th>\n";
	$l = _("Actions");
	$text.= "<th class=\"header\" style=\"width:5em\">$l</th>\n";
	$l = _("Rate");
	$text.= "<th class=\"header\" style=\"width:5em\">$l</th>\n";
	$text.= "</tr></thead><tbody>\n";
	$query = "SELECT * FROM $currencytbl";	/* get currency types */
	$curr = DoQuery($query, __LINE__);
	while($currarr = mysql_fetch_array($curr, MYSQL_ASSOC)) {
		$curnum = $currarr['curnum'];
		$name = $currarr['name'];
		$name = stripslashes($name);
		$sign = $currarr['sign'];
		//NewRow();
		$text.= "<tr><td>$name</td>\n";
		$text.= "<td>$sign</td>\n";
		//if(!isset($module))
		//	$url = "curadmin.php?action=delcurrency&amp;num=$curnum";
		//else
		$url = "?module=curadmin&amp;action=delcurrency&amp;num=$curnum";
		//$l = _("Delete");
		//$text.= "<td><input type=\"button\" value=\"$l\" onclick=\"window.location.href='$url'\"></td>\n";
		$text.= "<td><a href=\"$url\" class=\"btnremove\"></a></td>\n";
		$query = "SELECT rate FROM $ratestbl WHERE curnum='$curnum' AND date='$date'";
		$result = DoQuery($query, __LINE__);
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$rate = $line['rate'];
		$rate = number_format($rate, 3);
		$text.= "<td><input type=\"text\" size=\"5\" name=\"$curnum\" value=\"$rate\"></td>\n";
		$text.= "</tr>\n";
	}
	$text.= "</tbody></table>\n";
	$text.= "</td></tr>\n";
	$l = _("Update");
	$text.= "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td></tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
	
	//print "</div>";
	$l = _("Add currency");
	//print "<div class=\"form\"><h3 style=\"text-align:right\">$l</h3>\n";
	if(!isset($module))
		$text.= "<form action=\"curadmin.php?action=newcurrency\" method=\"post\">\n";
	else
		$text.= "<form action=\"?module=curadmin&amp;action=newcurrency\" method=\"post\">\n";
	$text.= "<table border=\"0\" dir=\"$dir\" class=\"formtbl\" width=\"100%\">\n<tr>\n";
	$l = _("Currency name");
	$text.= "<td>$l:</td>\n";
	$text.= "<td><input type=\"text\" name=\"name\" size=\"10\"></td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Sign");
	$text.= "<td>$l:</td>\n";
	$text.= "<td><input type=\"text\" name=\"sign\" size=\"8\"></td>\n";
	$text.= "</tr><tr>\n";
	$text.= "<td colspan=\"2\" align=\"center\">\n";
	$l = _("Update");
	$text.= "<input type=\"submit\" value=\"$l\"></td>\n";
	$text.= "</tr>\n</table>\n";
	$text.= "</form>\n";
	createForm($text,$header,'',750,'','logo',1,'help');
}
/*
if(!isset($module)) 
	print "</body>\n</html>\n";
else {
	print "</div>\n";
	
}*/
?>
