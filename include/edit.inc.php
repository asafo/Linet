<?PHP
/*
 | Text edit functions for Drorit accounting software
 */

function NewId($ancestor) {
	global $articlestbl;
	global $site;
	
	$idnum = 1;
	if(strpos($ancestor, '_') !== FALSE)
		$delim = '.';
	else
		$delim = '_';
	while(1) {
		$query = "SELECT id FROM $articlestbl WHERE id='${ancestor}${delim}$idnum'";
		$result = mysql_query($query);
		if(!$result) {
			echo mysql_error();
			exit;
		}
		// print_r($line);
		$n = mysql_num_rows($result);
		// print "id: $idnum n: $n<BR>\n";
		if($n == 0) {
			return "${ancestor}${delim}$idnum";
		}
		$idnum++;
	}
}

function GetModuleDesc($file) {
//	print "open: $file<br>\n";
	$fd = fopen($file, "r");
//	$lines = file($file);
	for($i = 0; $i < 3; $i++) {
		$line = $lines[$i];
		$line = fgets($fd);
//		print "line: $line<br>\n";
		list($pre, $desc) = explode(':', $line);
		if($pre == "//M") {
			@fclose($fd);
			return $desc;
		}
	}
	@fclose($fd);
	return '';
}

function PrintModuleSelect($def) {
	$dir = opendir(".");
	while($file = readdir($dir)) {
		$parts = explode(".", $file);
		$ext = end($parts);
		if($ext == 'php') {
	//		print "file: $file<br>\n";
			$desc = GetModuleDesc($file);
			if($desc) {
				$modname = $parts[0];
				$modules[$modname] = $desc;
			}
		}
	}
	closedir($dir);
	
	print "<select name=\"module\">\n";
	$l = _("Select module");
	print "<option value=\"0\">-- $l --</option>\n";
	foreach($modules as $module => $desc) {
		print "<option value=\"$module\" ";
		if($def == $module)
			print "selected";
		print ">$desc</option>\n";
	}
	print "</select>\n";	
}

function PrintIdSelect($id) {
	global $articlestbl, $dir;
	global $mainsite;
	global $MainMenu;
	global $template;

	if(!isset($dir))
		$dir = "rtl";

	$specialid = array('title', 'header', 'text', 'login', 'username');

	if(!isset($template) || ($template == '')) {
		print "Warning, default template...";
		$template = "template.html";
	}
	
	$filestr = file_get_contents("$template");
	preg_match_all("/~[^\x20|^~]*~/", $filestr, $ids);
	$ids = $ids[0];
	/* now we have all Id's in one array, we create a new array with unique id's */
	/* first find what id's already exist in database */
	$query = "SELECT id FROM $articlestbl";
	$result = mysql_query($query);
	$tblidarr = array();
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		if($line[0])
			array_push($tblidarr, $line[0]);
	}
	// foreach($MainMenu as $key => $val)
	//	array_push($tblidarr, $key);
	/* now we have two arrays, go over idarr and check for each one if it exists in table array */
	$idarr = array();
	foreach($ids as $val) {
		$val = str_replace('~', '', $val);
		if(strpos($val, '=')) {
			list($ptype, $p) = explode('=', $val);
			$val = $p;
		}
		if(in_array($val, $specialid))
			continue;
		if($val[0] == '_')
			continue;	/* id name beginning with _ is special id */
		/* first check if it already exists in final array ($idarr) */
		if(!in_array($val, $idarr)) {
			/* it does not exsist already */
			if(!in_array($val, $tblidarr) && ($val != ''))
				array_push($idarr, $val);
		}
	}
	/* Now at last we have one array with all id's missing in main page */
	if(count($idarr) == 0) {	/* special case, no id's missing */
		print "<input type=\"text\" name=\"id\" dir=\"ltr\" />\n";
	}
	else {
		print "<table dir=\"rtl\" border=\"0\"><tr><td>\n";
		print "<select name=id onchange=ShowNewId()>\n";
		$l = 'בחר דף';
		$l = _("Choose page");
		print "<option value=\"__NULL__\">$l</option>\n";
		$l = 'דף חדש';
		$l = _("New page");
		print "<option value=\"__NEW__\">$l</option>\n";
		foreach($idarr as $val) {
			print "<option value=\"$val\">$val</option>\n";
		}
		print "</select>\n";
		print "</td><td>\n";
		print "<div style=\"display:none\" id=\"shownewid\"><input type=\"text\" name=\"newid\" dir=\"ltr\">\n";
		print "</div>\n";
		print "</td></tr>\n</table>\n";
	}
}

function EditAble($id) {
	global $permissionstbl;
	global $name;
	
	$query = "SELECT company,level FROM $permissionstbl WHERE name='$name'";
	$result = DoQuery($query, "EditAble");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	if($line[0] == '*')
		return 1;
	if($line[1] == 1)
		return 1;	/* user with edit permissions and no superuser permissions */
	return 0;	
}

function PrintCommands() {
	global $id;
	global $base;
	global $permissionstbl, $logintbl;
	global $name;
	$n = 0;

	$str = "<script type=\"text/javascript\">\n";
	$str .= "function toglecmd() {\n";
	$str .= "\tcur = (document.getElementById('cmds').style.display == 'block') ? 'none' : 'block';\n";
	$str .= "\tdocument.getElementById('cmds').style.display = cur;\n";
	$str .= "\tdocument['cmdimg'].src = (cur == 'block') ? '${base}flip_up.gif' : '${base}flip_down.gif';\n";
	$str .= "}\n</script>\n";
	$str .= "<a href=\"#\" onclick=\"toglecmd()\">";
	$str .= "<img name=\"cmdimg\" border=\"0\" src=\"${base}flip_down.gif\" alt=\"commands\" />";
	$str .= "</a><br />\n";
	$str .= "<div id=\"cmds\">\n";	/* invisible div that will change to block */
	$str .= "<div class=\"cmdtbl\">\n";
	/* can we edit this page ? */
	if(EditAble($id)) {
		$n++;
		$l = 'עריכה'; 
		$l = _("Edit");
		$str .= "<div class=\"cmditem\"><a href=\"${base}index.php?id=$id&amp;action=edit\">$l</a></div>\n";
		$str .= "<div class=\"cmdspace\"></div>\n";
		$l = 'מחיקה';
		$l = _("Delete");
		if(EditAble($id)) {
			$n++;
			$str .= "<div class=\"cmditem\"><a href=\"${base}index.php?id=$id&amp;action=del\">$l</a></div>\n";
			$str .= "<div class=\"cmdspace\"></div>\n";
		}
		$l = 'הוסף דף';
		$l = _("Add page");
		$str .= "<div class=\"cmditem\"><a href=\"${base}index.php?action=add&amp;ancestor=$id\">$l</a></div>\n";
		$str .= "<div class=\"cmdspace\"></div>\n";
		$l = _("New page");
		$str .= "<div class=\"cmditem\"><a href=\"${base}index.php?action=add\">$l</a></div>\n";
	}
	$str .= "</div>\n";
	$str .= "</div>\n";
	if($n)
		return $str;
	return '';
}

function AddEdit($action, $id, $ancestor) {
	global $articlestbl;
	global $UserPriv;
	global $base;
	global $dir;
	
	if($action == 'add') {
		$url = "${base}index.php?action=doadd";
		if($ancestor)
			$url .= "&amp;ancestor=$ancestor";
		print "<form name=\"editform\" action=\"$url\"";
		print " method=\"post\">\n";
		$subject = '';
		$ord = 0;
		$contents = '';
	}
	if($action == 'edit') {
		$url = "${base}index.php?action=update&amp;id=$id";
		if($ancestor)
			$url .= "&amp;ancestor=$ancestor";
		print "<form name=\"editform\" action=\"$url\" method=\"post\">\n";
		$query = "SELECT subject,module,params,contents FROM $articlestbl WHERE id='$id'";
		$result = DoQuery($query, "AddEdit");
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$module = $line['module'];
		$params = $line['params'];
		$subject = stripslashes($line['subject']);
		$contents = stripslashes($line['contents']);
	}
	$l = 'שם מזהה';
	$l = _("Identifying name");
	if($lang != 'he')
		$dir = "rtl";
	else
		$dir = "ltr";
	print "<table dir=\"$dir\" border=\"0\" width=\"100%\"><tr><td>$l: \n</td>\n";
	print "<td colspan=\"2\">";
	if(($action != 'add') && isset($id) && ($id != '')) {	/* special case, we have id, so print it */
		print "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
		print "<input type=\"text\" name=\"newid\" value=\"$id\" />\n";
	}
	else
		PrintIdSelect($id);
//	print "</td></tr></table>\n";
	print "</td></tr>\n";
	print "<tr>\n";
	$l = 'נושא';
	$l = _("Title");
	print "<td>$l: </td>\n";
	print "<td colspan=\"2\">\n";
	print "<input type=\"text\" name=\"subject\" value=\"$subject\" size=\"50\" />\n";
	print "</td></tr>\n";
	
	print "<tr>\n";
	$l = _("Module");
	print "<td>$l: </td>\n";
	print "<td colspan=\"2\">\n";
	PrintModuleSelect($module);
	$l = _("Parameters");
	print "$l: <input type=\"text\" name=\"params\" value=\"$params\" size=\"30\" />\n";
	print "</td></tr>\n";
	
	print "<tr><td colspan=\"3\">\n";
	print "<textarea name=\"contents\" rows=\"40\" cols=\"80\" style=\"width:100%\">";
	print "$contents</textarea>\n";
	print "</td></tr>\n";
	$align = "right";
	print "<tr>\n";
	print "<td colspan=\"3\">\n";
//	print "<div align=\"$align\">\n";
	$l = 'השאר בעריכה';
	$l = _("Keep editing");
	print "<input type=\"checkbox\" name=\"keepediting\" /> $l\n";
	print "</td></tr><tr><td colspan=\"3\" align=\"center\">\n";
	$l = 'עדכן';
	$l = _("Update");
	print "<input type=\"submit\" value=\"$l\" />\n";
	print "</td></tr>\n";
	print "</table>\n";
	print "</form>\n";
}

?>