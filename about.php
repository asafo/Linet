<?php 
$haeder=_("About");
global $version,$softregnum;
$text="<h1>"._("Linet")."</h1><br />\n";
$text.="<h3>"._("Accounting software")."</h3><br />\n";
$text.="<h3>"._("Version").": ".$version."</h3><br />\n";
$url='http://www.opensource.org/licenses/gpl-3.0.html';
$text.=_("Software license").": "._("General-Public-License")." "."<a href='$url' target='_blank'>GPLv3</a><br />";
//אישור רישום מרשות המיסים:
//מס XXXXX
$text.=_("Registration certificate from the Tax Israeli Authority").": "._("No.")." ".$softregnum;


$text.="<br /><br />";
$text.="<div style=\"border: 2px solid red; margin-right: 50px; color: red;   padding: 10px; text-align: justify;  width: 440px;\" class=\"worning\">";
$text.="<h1 style=\"color: red; text-align: center; text-decoration: underline;\">אזהרה:</h1>";
$text.="<span>למרות שרישיון ה-GPLv3 מאפשר לך לשנות את קוד התוכנה, כל שינוי כאמור בקוד, יוצר למעשה גרסה חדשה לתוכנה, ובכך מבטל אוטומאטית את אישור הרישום של רשות המסים לגרסת תוכנת לינט שנמצאה בשימושך עד לפני השינוי. </span>";
$text.="<br />";
$text.="<span>במילים אחרות: כל שינוי שהוא בקוד התוכנה הופך את גרסת תוכנת הנה\"ח ששונתה על ידך לבלתי חוקית לשימוש בתור מערכת ממוחשבת לניהול ספרים בישראל.</span>";
$text.="<br />";
$text.="<span>רק רישום גרסת התוכנה ששינית ברשות המסים על ידך ובאחריותך, יוכל להפוך את גרסת התוכנה ששינית חזרה לתוכנה חוקית לשימוש בתור מערכת ממוחשבת לניהול ספרים בישראל.</span>";
$text.="</div>";

$text.='<br /><br />';
//$url='';
$text.="ניתן לעיין בתנאי הרישיון החוקי המלא באנגלית <a href='$url' target='_blank'>כאן</a>.<br />";
$url='http://www.law.co.il/media/computer-law/gplv3-hebrew.html';
$text.="ניתן לעיין בגרסא עברית בלתי רשמית לתנאי הרישיון <a href='$url' target='_blank'>כאן</a>.<br />";
createForm($text, $haeder,'',750,"",'',1,getHelp());
?>