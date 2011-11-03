<?PHP
/*
 | compass:
 | This is part of freelance accounting system.
 | This modulle will display general data about business or list of companies if
 | no company is active
 */
global $prefix;
global $companiestbl;
global $permissionstbl;
global $transactionstbl;
global $accountstbl;
global $itemstbl;

// print "prefix: $prefix<br>\n";

$query = "SELECT fullname FROM $logintbl WHERE name='$name'";
// print "Query: $query<br>\n";
$result = DoQuery($query, "compass.php");
$line = mysql_fetch_array($result, MYSQL_NUM);	
$fullname = $line[0];
// print "<h1 style=\"font-size:36;color:#00B7E3\">שלום: $fullname!</h1>\n";
print "<p style=\"font-size:14px;margin-bottom:10px\"><b>שלום $fullname!</b></p>\n";

/*
// quick nav 
print "<div class=\"navBar\" style=\"margin-right:-5px;margin-bottom:5px\">\n";
print "<ul id=\"compassnav\" dir=\"rtl\">\n";
print "<li><a href=\"?module=docsadmin&targetdoc=5\">חשבונית מס</a></li>\n";
print "<li><a href=\"?module=docsadmin&targetdoc=5&option=receipt\">חשבונית מס קבלה</a></li>\n";
print "<li><a href=\"?module=autoreceipt\">קבלה</a></li>\n";
print "<li><a href=\"?module=outcome\">רישום הוצאה</a></li>\n";
print "</ul>\n";
print "</div>\n";
*/
/*
$action = isset($_GET['action']) ? $_GET['action'] : '';
if($action == 'delcomp') {
	$p = $_GET['company'];
//	print "Transactions <br>\n";
	$query = "DELETE FROM $transactionstbl WHERE prefix='$p'";
	DoQuery($query, "compass");
//	print "accounts<br>\n";
	$query = "DELETE FROM $accountstbl WHERE prefix='$p'";
	DoQuery($query, "compass");
	$query = "DELETE FROM $itemstbl WHERE prefix='$p'";
	DoQuery($query, "compass");
	$query = "DELETE FROM $companiestbl WHERE prefix='$p'";
	DoQuery($query, "compass");
}*/

if(!isset($prefix)) {	/* Display list of companies */
	$query = "SELECT company FROM $permissionstbl WHERE name='$name'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$n = mysql_num_rows($result);
/*	if($n == 1) {
		$line = mysql_fetch_array($result, MYSQL_NUM);
		$prefix = $line[0];
		$company = $line[0];
		print "prefix: $prefix<br>\n";
	} */
	if($n == 0) {
		print "<h1>אין חברות למשתמש זה</h1>\n";
		return;
	}
	if($n >= 1) {
		$line = mysql_fetch_array($result, MYSQL_NUM);
		if($line[0] == '*') {
			$query = "SELECT prefix FROM $companiestbl";
			$result = DoQuery($query, "compass.php");
			$n = mysql_num_rows($result);
			// print "n: $n<br>\n";
		}
		print "<h1>בחר חברה לעבודה</h1>\n";
		print "<div style=\"margin-right:10%\">\n";
		print "<ul>\n";
		while($line = mysql_fetch_array($result, MYSQL_NUM)) {
			$s = $line[0];
			// print "prefix: $s<br>\n";
			$query = "SELECT companyname FROM $companiestbl WHERE prefix='$s'";
			$r = DoQuery($query, "compass.php");
			$line = mysql_fetch_array($r, MYSQL_NUM);
			$n = $line[0];
			$cookietime = time() + 60*60*24*30;
			$url = "index.php?cookie=company,$s,$cookietime&company=$s";
			print "<li><a href=\"$url\">$n</a>&nbsp;\n";
			if($name == 'admin')
				print "<a href=\"?module=compass&action=delcomp&company=$s\">מחק</a>";
			print "</li>\n";
		}
		print "</ul>\n</div>\n";
		return;
	}
}

function CopyDemoGraphs() {
	copy("img/profit.png", "tmp/profit.png");
	copy("img/mgraph.png", "tmp/mgraph.png");
	copy("img/income.png", "tmp/income.png");
	copy("img/outcome.png", "tmp/outcome.png");
	copy("img/suppliers.png", "tmp/suppliers.png");
}

function PrintGraphTypes() {
	$imgarr = array(
	"tmp/profgraph.png" => "רווח והפסד לפי חודשים",
	"tmp/profit.png" => "תמצית דו\"ח רווח והפסד",
	"tmp/mgraph.png" => "הכנסות והוצאות לפי חודש",
	"tmp/income.png" => "התפלגות הכנסות",
	"tmp/outcome.png" => "התפלגות הוצאות",
	"tmp/customers.png" => "התפלגות לקוחות",
	"tmp/suppliers.png" => "התפלגות ספקים"
	);
	
	print "<script type=\"text/javascript\">\n";
	print "function ShowGraph() {\n";
	print "\tvar img = document.grform.graphsel.value\n;";
//	print "\talert(img);\n";
	print "\tdocument.graph.src=img;\n";
	print "}\n";
	print "</script>\n";
	print "<form name=\"grform\">\n";
	print "<span style=\"color:#00000\"><b>בחר גרף להצגה </b></span>";
	print "<select name=\"graphsel\" onchange=\"ShowGraph()\">\n";
	foreach($imgarr as $key => $val) {
		print "<option value=\"$key\">$val</option>\n";
	}	
	print "</select><br>\n";
	print "</form>\n";
}

function GetAcctTotal($acct, $begin, $end) {
	global $transactionstbl, $prefix;
	
	if($begin != 0)
		$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
	else 
		$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date<='$end' AND prefix='$prefix'";
//	print "query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetGroupTotal($grp, $begin, $end) {
	global $accountstbl, $prefix;

	$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$grp'";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	list($y, $m, $d) = explode('-', $begin);
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
		$sub_total = GetAcctTotal($num, $begin, $end);
		$total += $sub_total;
	}
	return $total;
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}


function CreateProfitGraph($income, $outcome, $profit) {
	$data1 = array($income);
	if($outcome < 0)
		$outcome *= -1.0;
	$data2 = array($outcome);
	$label = array("");
	$bar_width = 30;
	$fname = "profit.png";
	require('dbarchart.php');
}

// print "<h1>מצפן עסקי</h1><br>\n"; //
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : date("1-1-Y");
$enddmy = isset($_GET['end']) ? $_GET['end'] : date("d-m-Y");
print "<table dir=\"rtl\" border=\"0\"><tr><td colspan=\"6\">\n";
print "<form name=\"compass\" method=\"get\">\n";
print "<input type=\"hidden\" name=\"module\" value=\"compass\">\n";
print "<table border=\"0\"><tr>\n";
print "<td>בחר תאריך תחילה: </td>\n";
print "<td><input type=\"text\" name=\"begin\" value=\"$begindmy\" size=\"7\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'compass',
		// input name
		'controlname': 'begin'
	});
</script>
<?PHP
print "&nbsp;&nbsp;</td>\n";
print "<td>בחר תאריך סיום: </td>\n";
print "<td><input type=\"text\" name=\"end\" value=\"$enddmy\" size=\"7\">\n";
?>
<script language="JavaScript">
	new tcal ({
		// form name
		'formname': 'compass',
		// input name
		'controlname': 'end'
	});
</script>
<?PHP
print "&nbsp;&nbsp;</td><td><input type=\"submit\" value=\"בצע\"></td></tr>\n";
print "</table></form><br>\n";
print "</td></tr>\n";
$begin = FormatDate($begindmy, "dmy", "mysql");
$end = FormatDate($enddmy, "dmy", "mysql");
$income = GetGroupTotal(INCOME, $begin, $end);
$outcome = GetGroupTotal(OUTCOME, $begin, $end);
// $income = number_format($income);
// $outcome = number_format($outcome);
/* if(($income == 0) && ($outcome == 0)) {
	print "<br><h1>אין תנועות עסקיות</h1>\n";
	ShowText('compass1');
	return;
} */
print "<tr><td valign=\"top\" style=\"width:15em\">\n";
print "<div class=\"caption_out\"><div class=\"caption\"><b>נתונים עסקיים בש\"ח</b></div></div>\n";
// print "<h1 style=\"margin-bottom:8px;margin-top:0px\">נתונים עסקיים</h1>\n";
//print "<br>\n";
$EvenLine = 0;
print "<b>בחר נתון להצגה</b><br><br>\n";
print "<table class=\"hovertbl1\">\n";
NewRow();
$n = number_format($income);
print "<td style=\"width:7.5em;font-weigh:normal;font-size:14px;\"><a href=\"?module=acctrep\">סה\"כ הכנסות </a></td>";
print "<td style=\"color:black;font-weight:normal;font-size:14px;\" >$n</td></tr>\n";
NewRow();
$o = $outcome * -1.0;
$n = number_format($o);
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctrep\">סה\"כ הוצאות </td>";
print "<td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td></tr>\n";
NewRow();
$profit = $income + $outcome;
$url = "?module=profloss&step=1&begindate=$begindmy&enddate=$enddmy";
if($profit >= 0.0)
	print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"$url\">סה\"כ רווח</a> </td>";
else
	print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"$url\">סה\"כ הפסד</a> </td>";
$n = number_format(abs($profit));
print "<td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
if($profit > 0) {
//	print "profit: $profit, income: $income<br>";
	$profitpercent = $profit / $income * 100;
}
else
	$profitpercent = 0;
print "</tr>\n";
NewRow();
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"$url\">אחוז רווח נקי</a></td>\n";
$n = number_format($profitpercent);
print "<td style=\"color:black;font-weight:normal;font-size:14px;\">$n %</td>\n";

print "</tr>";
NewRow();
$t = GetGroupTotal(CUSTOMER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctrep\">סה\"כ לקוחות </td><td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
print "</tr>\n";
NewRow();
$t = GetGroupTotal(SUPPLIER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=acctrep\">סה\"כ ספקים </td><td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
print "</tr></table>\n";

print "</td>\n";
print "<td style=\"width:20px\">&nbsp;</td>\n";	/* spacer column */
print "<td valign=\"top\" style=\"width:400px\">\n";
print "<div class=\"caption_out\"><div class=\"caption\"><b>גרפים לניתוח מצב העסק</b></div></div>\n";
// print "<br>\n";
// print "<h1 style=\"margin-bottom:2px;margin-top:0px\">גרפים</h1>\n";
if(($income == 0) || ($outcome == 0)) {
//	PrintGraphTypes();
//	CopyDemoGraphs();
	CreateProfitGraph(0, 0, 0);
	print "<table><tr><td valign=\"top\">\n";
	print "<img name=\"graph\" alt=\"graph\" src=\"tmp/profit.png\" align=\"right\">\n";
	print "</td><td valign=\"center\">\n";
	print "<h2>&nbsp;&nbsp; אין תנועות ליצירת גרף</h2>";
	print "</td></tr>\n";
	print "</table>\n";
//	print "<br><h2>גרף להדגמה בלבד. לא ניתן ליצור גרפים ללא תנועות</h2>\n";
}
else {
	PrintGraphTypes();
//	print "<br>\n";
	// Create graphs
	require('chart_func.php');
	require('profgraph.php');
	CreateProfitGraph($income, $outcome, $profit);
	require('mgraph.php');
	$type = INCOME;
	require('igraph.php');
	$type = OUTCOME;
	require('igraph.php');
	$type = SUPPLIER;
	require('igraph.php');
	$type = CUSTOMER;
	require('igraph.php');

	print "<img name=\"graph\" alt=\"graph\" src=\"tmp/profgraph.png\">\n";
//	print "<img name=\"graph\" alt=\"graph\" src=\"tmp/profit.png\">\n";
}
print "</td>\n";
print "<td style=\"width:20px\">&nbsp</td>\n";	/* spacer column */
print "<td valign=\"top\" style=\"width:27em\">\n";
print "<table border=\"0\" width=\"100%\"><tr><td valign=\"top\" align=\"right\">\n";
// quick start 
/* 
print "<div class=\"caption_out\"><div class=\"caption\"><b>התחלה מהירה</b></div></div>\n";
// print "<div style=\"margin-right:20px;font-size:16px\">\n";
print "<table class=\"hovertbl1\" style=\"font-weight:normal;font-size:12px\">\n";
NewRow();
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=docsadmin&targetdoc=5\">הפק חשבונית מס</a></td></tr>\n";
NewRow();
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=docsadmin&targetdoc=5&option=receipt\">הפק חשבונית מס קבלה</a></td></tr>\n";
NewRow();
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=autoreceipt\">הפק קבלה</a></td></tr>\n";
NewRow();
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=outcome\">רישום הוצאה</a></td></tr>\n";
NewRow();
print "<td style=\"font-weight:normal;font-size:14px;\"><a href=\"?module=payment\">תשלום לספק</a></td></tr>\n";

print "</table>\n";
*/
print "</td><td valign=\"top\" align=\"right\">\n";
print "<div class=\"caption_out\"><div class=\"caption\"><b>יומן ארועים על פי תאריך</b></div></div>\n";
print "<b>בחר תאריך להצגה<b><br><br>\n";
require('calendar.php');
print "</td>\n"; 
print "</tr></table>\n";

print "</td><td style=\"width:20px\">&nbsp;&nbsp;</td>\n";
print "</tr><tr>\n";
print "<td colspan=\"3\" valign=\"top\">\n";
// print "<h2><u>הודעות מערכת</u></h2>\n";
print "<div class=\"caption_out\"><div class=\"caption\"><b>הודעות, עדכונים ודברים חשובים ממערכת תוכנת פרילאנס</b></div></div>\n";
ShowText('msg');
print "</td>\n";
print "<td>&nbsp;</td>\n";
print "<td valign=\"top\">\n";
ShowText('compass');

print "</td>\n";


print "</tr><tr>\n";
print "<td colspan=\"6\" valign=\"top\">\n";
print "<br><br>\n";
print "<table border=\"0\" dir=\"rtl\"><tr><td valign=\"top\" align=\"center\">\n";
print "<a href=\"http://www.rlcpa.co.il\"><img border=\"0\" src=\"shayharel.jpg\" width=\"
300\" height=\"45\" alt=\"לוגו של שי הראל\"></a>\n";
print "<br>פיקוח ובקרה מקצועית שי הראל <a href=\"http://www.rlcpa.co.il\">רואי חשבון</a>\n";
print "</td><td>\n";
print "&nbsp;&nbsp;</td><td>\n";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign=\"top\" align=\"center\">\n";
print "<a href=\"?id=page1\">";
print "<img border=\"0\" src=\"taxauthority.jpg\" height=\"45\" alt=\"לוגו תוכנה מאושרת\"></a>\n";
print "<br><a href=\"?id=page1\">תוכנה רשומה</a></td>\n";
print "<td>\n";

print "&nbsp;&nbsp;&nbsp;&nbsp;\n";
print "</td><td>\n";
print "&nbsp;&nbsp;&nbsp;&nbsp;\n";
print "</td><td valign=\"top\" align=\"center\">\n";
print "<a href=\"?id=page2\">";
print "<img border=\"0\" src=\"locked_32.png\" height=\"45\" alt=\"Secured web site\"></a>\n";
print "<br><a href=\"?id=page2\">תוכנה מאובטחת</a></td>\n";
print "<td>\n";
print "&nbsp;&nbsp;&nbsp;&nbsp;\n";
print "</td>";

print "<td valign=\"top\" align=\"center\">";
print "<a href=\"?id=page4\">";
print "<img border=\"0\" src=\"terms.png\" height=\"45\" alt=\"terms and conditions\"><br>";
print "תנאי שימוש</a>\n";
print "</td><td>";
print "&nbsp;&nbsp;&nbsp;&nbsp;\n";
print "</td>\n";

print "<td valign=\"top\" align=\"center\">";
/* firefox affiliate code */
print "<a href='http://www.mozilla.com/en-US/?from=sfx&amp;uid=96935&amp;t=438'><img src='http://sfx-images.mozilla.org/affiliates/Buttons/Firefox3.5/96x31_blue.png' alt='Spread Firefox Affiliate Button' border='0' /></a>\n";
print "<br>\n";
print "מומלץ להשתמש בדפדפן פיירפוקס<br>\n";
print "לפרטים נוספים לחץ ";
print "<a href=\"?id=firefox\">כאן</a>\n";
print "</td></tr></table>\n";
// ShowText('compass');
print "</td>\n";
print "</tr>\n";
print "</table>\n";
?>

