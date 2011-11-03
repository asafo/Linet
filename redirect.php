<?php
$module=$_GET['dest'];
$url='http://www.linet.org.il/index.php/support/';

$bla=array(
'defs'=>'user-help-navigate/8-configuration-help/18-biz-details-page',//עריכת פרטי עסק
'acctadmin'=>'user-help-navigate?id=19',//חשבונות
'acctadmin0'=>'user-help-navigate?id=48',
'acctadmin1'=>'user-help-navigate?id=45',
'acctadmin2'=>'user-help-navigate?id=44',
'acctadmin3'=>'user-help-navigate?id=47',
'acctadmin6'=>'user-help-navigate?id=52',///רשויות
'acctadmin5'=>'user-help-navigate?id=53',//התחייבויות
'acctadmin4'=>'user-help-navigate?id=56',///נכסים
'acctadmin7'=>'user-help-navigate?id=51',//בנקים
//'acctadmin9'=>'user-help-navigate?id=57',//מזומנים
//'acctadmin11'=>'user-help-navigate?id=55',//נירות ערך
//'acctadmin12'=>'user-help-navigate?id=56',//רכוש קבוע
'docnums'=>'user-help-navigate?id=23',//הגדרות למסמכים עסקיים
'items'=>'user-help-navigate?id=28',//פרטים
'curadmin'=>'user-help-navigate?id=29',//שערי מטבע
'opbalance'=>'user-help-navigate?id=30',//יתרות פתיחה
'loginedituser'=>'user-help-navigate?id=31',//עריכת פרטי משתמש
'loginadduser'=>'user-help-navigate?id=32',//הוסף משתמש
'contact0'=>'user-help-navigate?id=24',//ניהול לקוחות
'contactedit'=>'user-help-navigate?id=27',//מעקב
'docsadmin6'=>'user-help-navigate?id=33',//מסמכים: הצאות מחיר
'docsadmin7'=>'user-help-navigate?id=34',//הזמנת עבודה
'docsadmin1'=>'user-help-navigate?id=35',//חשבונית עסקה
'docsadmin3'=>'user-help-navigate?id=36',//חשבונית מס
'docsadmin9'=>'user-help-navigate?id=38',//חשבונית מס קבלה
'docsadmin4'=>'user-help-navigate?id=40',//חשבונית זיכוי
'deposit'=>'user-help-navigate?id=41',//הפקדות בנק
'contact1'=>'user-help-navigate?id=25',//ניהול ספקים
'outcome'=>'user-help-navigate?id=43',//רישום הוצאה
'outcomeasset'=>'user-help-navigate?id=46',//רישום נכס
'showdocs'=>'user-help-navigate?id=42',



'payment'=>'user-help-navigate?id=60',//תשלום לספק
'paymentvat'=>'user-help-navigate?id=61',//מעמ
'paymentnatins'=>'user-help-navigate?id=62',//ביטוח לאומי
'docsadmin8'=>'user-help-navigate?id=39',//קבלה
//דוחות
'tranrep'=>'user-help-navigate?id=68',//דוח הכנסות הוצאות
'owe'=>'user-help-navigate?id=71',//לקוחות
'profloss'=>'user-help-navigate?id=73',//דוח רווח והפסד
'mprofloss'=>'user-help-navigate?id=74',//דוח רווח והפסד לפי חודש
'vatrep'=>'user-help-navigate?id=75',//דוח מע"מ

//התאמות
'bankbook'=>'user-help-navigate?id=59',//קליטת דפי בנק
'extmatch'=>'user-help-navigate?id=65',//התאמות בנקים
'dispmatch'=>'user-help-navigate?id=66',//הצג התאמות
'intmatch'=>'user-help-navigate?id=67',//התאמות כרטיסים


//יבוא יצוא
'openfrmt'=>'user-help-navigate?id=77',//יצוא קובץ אחיד
'pcn874'=>'user-help-navigate?id=80',//דוח PCN874
'backup'=>'user-help-navigate?id=78',//גיבוי לינט
'backuprestore'=>'user-help-navigate?id=79',//שחזור גיבוי לינט
'openfrmtimport'=>'user-help-navigate?id=58',//יבוא קובץ אחיד

//תמיכה

//תמיכה בתשלום
//module=support,paid-support
//אודות
//module=about,user-help-navigate?id=50






);

$url=$url.$bla[$module];

print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=$url\">\n";
?>