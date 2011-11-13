<?PHP
/*
 | docnums
 | This module is part of Drorit accounting system
 | Written by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl, $itemstbl;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'update') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";		
		return;
	}

	$num1 = (int)$_POST['num1'];
	$num2 = (int)$_POST['num2'];
	$num3 = (int)$_POST['num3'];
	$num4 = (int)$_POST['num4'];
	$num5 = (int)$_POST['num5'];
	$num6 = (int)$_POST['num6'];
	$num8 = (int)$_POST['num8'];
	$header = htmlspecialchars($_POST['header'], ENT_QUOTES);
	$footer = htmlspecialchars($_POST['footer'], ENT_QUOTES);
	$size = (int)$_FILES['logo']['size'];
	if($size > 0) {	/* we have a file */
		$tmpname = $_FILES['logo']['tmp_name'];
		
	       // $name = $_FILES['logofile']['name'];
	       // $mime = $_FILES['logofile']['type'];
	       //print 'a['.$size.']b['.$name.']c['.$mime;
	   if (file_exists($tmpname)){   
		$orgname = $_FILES['logo']['name'];
		/* find extension */
		$offset = strrpos($orgname, '.');
		$offset++;
		$ext = substr($orgname, $offset);
		$logo = "$prefix.$ext";
//		print "logo: $logo<br>\n";
		
		move_uploaded_file($tmpname, "img/logo/$logo");
		//$img = base64_encode($img); 

	   }else{ 
	   	print 'error';
	   }

	}

	$query = "UPDATE $companiestbl SET num1='$num1', num2='$num2', num3='$num3', ";
	$query .= "num4='$num4', num5='$num5', num6='$num6', num8='$num8', ";
	$query .= "header='$header', footer='$footer' ";
	if($logo != '')
		$query .= ", logo='$logo' ";
	$query .= "WHERE prefix='$prefix'";
	//print $query;
	DoQuery($query, "docnums.php");
	
}
if($action == 'logodel') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";		return;
	}

	$query = "SELECT logo FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, 'docnums');
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$logo = $line['logo'];
	unlink("img/logo/$logo");
	$query = "UPDATE $companiestbl SET logo='' WHERE prefix='$prefix'";
	DoQuery($query, 'docnums');
}

$query = "SELECT num1,num2,num3,num4,num5,num6,num8,header,footer,logo FROM $companiestbl ";
$query .= "WHERE prefix='$prefix'";
$result = DoQuery($query, "docnums.php");
$line = mysql_fetch_array($result, MYSQL_ASSOC);
$num1 = $line['num1'];
$num2 = $line['num2'];
$num3 = $line['num3'];
$num4 = $line['num4'];
$num5 = $line['num5'];
$num6 = $line['num6'];
$num8 = $line['num8'];
$header = $line['header'];
$footer = $line['footer'];
$logo = $line['logo'];

//print "<br>\n";
//print "<div class=\"form righthalf1\">\n";
$haeder = _("Business documents definitions");
//print "<h3>$l</h3>\n";
$text= '';
$text.= "<form name=\"form1\" enctype=\"multipart/form-data\" action=\"?module=docnums&amp;action=update\" method=\"post\">\n";
$text.= "<table border=\"0\" cellpadding=\"1px\" class=\"formtbl\" width=\"100%\"><tr>\n";
$l = _("Base numbers");
$text.= "<td colspan=\"4\"><h2>$l</h2></td>\n";
$text.= "</tr><tr>\n";

$l = _("Proforma");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"num1\" value=\"$num1\" size=\"4\"></td>\n";
$l = _("Delivery doc.");
$text.= "<td>&nbsp;&nbsp;$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"num2\" value=\"$num2\" size=\"4\"></td>\n";
$text.= "</tr><tr>\n";
$l = _("Invoice");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"num3\" value=\"$num3\" size=\"4\"></td>\n";
$l = _("Credit invoice");
$text.= "<td>&nbsp;&nbsp;$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"num4\" value=\"$num4\" size=\"4\"></td>\n";
$text.= "</tr><tr>\n";

$l = _("Return document");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"num5\" value=\"$num5\" size=\"4\"></td>\n";
$text.= "</tr><tr>\n";

$l = _("Receipt");
$text.= "<td>$l: </td>\n";
$text.= "<td><input type=\"text\" name=\"num6\" value=\"$num6\" size=\"4\"></td>\n";
$text.= "</tr><tr>\n";

$l = _("Document layout");
$text.= "<td colspan=\"4\"><h2>$l</h2></td>\n";
$text.= "</tr><tr>\n";

$l = _("Header");
$text.= "<td colspan=\"2\">$l: </td>\n";
$text.= "<td colspan=\"2\"><input type=\"text\" name=\"header\" value=\"$header\"></td>\n";
$text.= "</tr><tr>\n";

$l = _("Footer");
$text.= "<td colspan=\"2\">$l: </td>\n";
$text.= "<td colspan=\"2\"><input type=\"text\" name=\"footer\" value=\"$footer\"></td>\n";
$text.= "</tr><tr>\n";

$l = _("Logo");
$text.= "<td colspan=\"2\" valign=\"top\">$l: </td>\n";
$text.= "<td colspan=\"2\" valign=\"top\"><input type=\"file\" name=\"logo\" value=\"\"></td>\n";
if($logo) {
	$text.= "</tr><tr>\n";
	$text.= "<td colspan=\"3\" align=\"center\"><img src=\"img/logo/$logo\" alt=\"$l\" width=\"100px\"></td>\n";
	$l1 = _("Delete");
	$text.= "<td><a class=\"btnsmall\" href=\"?module=docnums&amp;action=logodel\">$l1 $l</a></td>";
	//$text.= "onClick=\"window.location.href=''\"></td>\n";
}
$text.= "</tr>\n";
$text.= "<tr><td colspan=\"4\" align=\"center\">\n";
$l = _("Update");
$text.= "<a href=\"javascript:document.form1.submit();\" class=\"btnaction\">$l</a>&nbsp;&nbsp;\n";
$text.= "</td></tr>\n";
$text.= "</table>\n";
$text.= "</form>\n";
//print "</div>\n";
createForm($text,$haeder,'',750,'',"img/icon_docnums.png",1,getHelp());

?>
