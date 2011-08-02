<?PHP
/*
 | show graphs
 | This module is part of Freelance accounting system
 | Written for Shay Harel by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $companiestbl, $supdocstbl;
global $paymentarr;
global $creditcompanies;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>לא ניתן לבצע פעולה זו ללא בחירת עסק</h1>";
	return;
}

print "<div class=\"caption_out\"><div class=\"caption\">סיכום גרפים</div></div>";
print "<br>";
print "<table dir=\"rtl\">
<tr>";
print "<td>";
print "<div class=\"caption_out\"><div class=\"caption\">תמצית דוח רווח והפסד</div></div>";
print "<img src=\"tmp/profit.png\" alt=\"\תמצית דוח רווח והפסד\">";
print "</td>";
print "<td>&nbsp;&nbsp;</td>
<td></td>";
print "</tr><tr>";
print "<td>";
print "<div class=\"caption_out\"><div class=\"caption\">רווח והפסד לפי חודשים</div></div>";
print "<img src=\"tmp/profgraph.png\" alt=\"רווח והפסד לפי חודשים\">";

print "</td>";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
print "<td>";
print "<div class=\"caption_out\"><div class=\"caption\">הכנסות והוצאות לפי חודשים</div></div>";
print "<img src=\"tmp/mgraph.png\" alt=\"הכנסות והוצאות לפי חודשים\">";

print "</td>";

print "</tr><tr>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">התפלגות הכנסות</div></div>";
print "<img src=\"tmp/income.png\" alt=\"התפלגות הכנסות\">";
print "</td>";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">התפלגות הוצאות</div></div>";
print "<img src=\"tmp/outcome.png\" alt=\"התפלגות הוצאות\">";
print "</td>";

print "</tr><tr>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">התפלגות לקוחות</div></div>";
print "<img src=\"tmp/customers.png\" alt=\"התפלגות לקוחות\">";
print "</td>";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">התפלגות ספקים</div></div>";
print "<img src=\"tmp/suppliers.png\" alt=\"התפלגות ספקים\">";
print "</td>";

print "</table>";

?>
