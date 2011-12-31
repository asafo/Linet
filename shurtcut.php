<?php

$haeder = _("Short cuts");

$text='';
/*
$l = _("Invoice");
$text.= "<a href=\"?module=docsadmin&amp;targetdoc=3\" class=\"Surtcut\">$l</a><br />\n";
$l = _("Receipt");
$text.= "<a href=\"?module=docsadmin&amp;targetdoc=8\" class=\"Surtcut\">$l</a>\n<br />";
$l = _("Deposit");
$text.= "<a href=\"?module=deposit\" class=\"Surtcut\">$l</a>\n<br />";
$l = _("Outcome");
$text.= "<a href=\"?module=outcome\" class=\"Surtcut\">$l</a>\n<br />";
$l = _("Payment");
$text.= "<a href=\"?module=payment\" class=\"Surtcut\">$l</a>\n<br />";
// print "<div class=\"emptyshortcut\">&nbsp;</div>\n";
$l = _("Contacts");
$text.= "<a href=\"?module=contact\" class=\"Surtcut\">$l</a>\n<br />";
*/
$l = _("Invoice");
$text.= "<a href=\"?module=docsadmin&amp;targetdoc=3\" class=\"Surtcut\">$l</a><br />\n";
$l = _("Invoice receipt");
$text.= "<a href=\"?module=docsadmin&amp;targetdoc=9\" class=\"Surtcut\">$l</a>\n<br />";
$l = _("Receipt");
$text.= "<a href=\"?module=docsadmin&amp;targetdoc=8\" class=\"Surtcut\">$l</a>\n<br />";
$l = _("Outcome");
$text.= "<a href=\"?module=outcome\" class=\"Surtcut\">$l</a>\n<br />";
$l = _("Customers managmenet");
$text.= "<a href=\"?module=contact&type=0\" class=\"Surtcut\">$l</a>\n<br />";
// print "<div class=\"emptyshortcut\">&nbsp;</div>\n";
$l = _("Bank deposits");
$text.= "<a href=\"?module=deposit&type=2\" class=\"Surtcut\">$l</a>\n<br />";
$text.="<div class=\"sysmsg\"></div>";
createForm($text,$haeder,"shortsdiv",180,null,'img/icon_shurtcuts.png');

?>