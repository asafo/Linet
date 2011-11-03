<?PHP
/*
 | show graphs
 | This module is part of Freelance accounting system
 | Written for Shay Harel by Ori Idan helicon technologies Ltd.
 */
global $prefix, $accountstbl, $supdocstbl;
global $paymentarr;
global $creditcompanies;

if(!isset($prefix) || ($prefix == '')) {
	print "<h1>׳³ן¿½׳³ן¿½ ׳³ֲ ׳³ג„¢׳³ֳ—׳³ן¿½ ׳³ן¿½׳³ג€˜׳³ֲ¦׳³ֲ¢ ׳³ג‚×׳³ֲ¢׳³ג€¢׳³ן¿½׳³ג€� ׳³ג€“׳³ג€¢ ׳³ן¿½׳³ן¿½׳³ן¿½ ׳³ג€˜׳³ג€”׳³ג„¢׳³ֲ¨׳³ֳ— ׳³ֲ¢׳³ֲ¡׳³ֲ§</h1>";
	return;
}

print "<div class=\"caption_out\"><div class=\"caption\">׳³ֲ¡׳³ג„¢׳³ג€÷׳³ג€¢׳³ן¿½ ׳³ג€™׳³ֲ¨׳³ג‚×׳³ג„¢׳³ן¿½</div></div>";
print "<br>";
print "<table dir=\"rtl\">
<tr>";
print "<td>";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ֳ—׳³ן¿½׳³ֲ¦׳³ג„¢׳³ֳ— ׳³ג€�׳³ג€¢׳³ג€” ׳³ֲ¨׳³ג€¢׳³ג€¢׳³ג€” ׳³ג€¢׳³ג€�׳³ג‚×׳³ֲ¡׳³ג€�</div></div>";
print "<img src=\"tmp/profit.png\" alt=\"\׳³ֳ—׳³ן¿½׳³ֲ¦׳³ג„¢׳³ֳ— ׳³ג€�׳³ג€¢׳³ג€” ׳³ֲ¨׳³ג€¢׳³ג€¢׳³ג€” ׳³ג€¢׳³ג€�׳³ג‚×׳³ֲ¡׳³ג€�\">";
print "</td>";
print "<td>&nbsp;&nbsp;</td>
<td></td>";
print "</tr><tr>";
print "<td>";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ֲ¨׳³ג€¢׳³ג€¢׳³ג€” ׳³ג€¢׳³ג€�׳³ג‚×׳³ֲ¡׳³ג€� ׳³ן¿½׳³ג‚×׳³ג„¢ ׳³ג€”׳³ג€¢׳³ג€�׳³ֲ©׳³ג„¢׳³ן¿½</div></div>";
print "<img src=\"tmp/profgraph.png\" alt=\"׳³ֲ¨׳³ג€¢׳³ג€¢׳³ג€” ׳³ג€¢׳³ג€�׳³ג‚×׳³ֲ¡׳³ג€� ׳³ן¿½׳³ג‚×׳³ג„¢ ׳³ג€”׳³ג€¢׳³ג€�׳³ֲ©׳³ג„¢׳³ן¿½\">";

print "</td>";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
print "<td>";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ג€�׳³ג€÷׳³ֲ ׳³ֲ¡׳³ג€¢׳³ֳ— ׳³ג€¢׳³ג€�׳³ג€¢׳³ֲ¦׳³ן¿½׳³ג€¢׳³ֳ— ׳³ן¿½׳³ג‚×׳³ג„¢ ׳³ג€”׳³ג€¢׳³ג€�׳³ֲ©׳³ג„¢׳³ן¿½</div></div>";
print "<img src=\"tmp/mgraph.png\" alt=\"׳³ג€�׳³ג€÷׳³ֲ ׳³ֲ¡׳³ג€¢׳³ֳ— ׳³ג€¢׳³ג€�׳³ג€¢׳³ֲ¦׳³ן¿½׳³ג€¢׳³ֳ— ׳³ן¿½׳³ג‚×׳³ג„¢ ׳³ג€”׳³ג€¢׳³ג€�׳³ֲ©׳³ג„¢׳³ן¿½\">";

print "</td>";

print "</tr><tr>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ג€�׳³ג€÷׳³ֲ ׳³ֲ¡׳³ג€¢׳³ֳ—</div></div>";
print "<img src=\"tmp/income.png\" alt=\"׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ג€�׳³ג€÷׳³ֲ ׳³ֲ¡׳³ג€¢׳³ֳ—\">";
print "</td>";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ג€�׳³ג€¢׳³ֲ¦׳³ן¿½׳³ג€¢׳³ֳ—</div></div>";
print "<img src=\"tmp/outcome.png\" alt=\"׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ג€�׳³ג€¢׳³ֲ¦׳³ן¿½׳³ג€¢׳³ֳ—\">";
print "</td>";

print "</tr><tr>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”׳³ג€¢׳³ֳ—</div></div>";
print "<img src=\"tmp/customers.png\" alt=\"׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ן¿½׳³ֲ§׳³ג€¢׳³ג€”׳³ג€¢׳³ֳ—\">";
print "</td>";
print "<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
print "<td valign=\"top\">";
print "<div class=\"caption_out\"><div class=\"caption\">׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ֲ¡׳³ג‚×׳³ֲ§׳³ג„¢׳³ן¿½</div></div>";
print "<img src=\"tmp/suppliers.png\" alt=\"׳³ג€�׳³ֳ—׳³ג‚×׳³ן¿½׳³ג€™׳³ג€¢׳³ֳ— ׳³ֲ¡׳³ג‚×׳³ֲ§׳³ג„¢׳³ן¿½\">";
print "</td>";

print "</table>";

?>
