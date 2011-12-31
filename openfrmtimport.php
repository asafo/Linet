<?php
/*
 * open format import 
 * written by Adam BH.
 * 
 * this page relays deaply on new db tables make sure you have the latest db
 * 
 * 
 */
global $table;
require_once('class/company.php');
require_once('class/account.php');
require_once('class/document.php');
require_once('class/documentdetail.php');
require_once('class/receiptdetail.php');
require_once('class/transaction.php');
require_once('class/item.php');
if ((!isset($_REQUEST['step'])) || ($_REQUEST['step']<0)) {
	$haeder="Select files to Import";
	$text= "<form action=\"?module=$module&amp;step=1\" method=\"post\" name='main' enctype=\"multipart/form-data\">\n<br />";
	$text.="<div style=\"border: 2px solid red; margin-right: 50px; color: red;   padding: 10px; text-align: justify;  width: 600px;\" class=\"worning\">";
	$text.="
	תהליך הייבוא של חומרים ללינט מתוך תוכנות הנה\"ח אחרות באמצעות ייבוא קובץ אחיד הוא תהליך מורכב הדורש זהירות ותשומת לב רבים.
אנא קרא בעיון את הוראות מדריך הייבוא <a href=\"http://www.linet.org.il/index.php/support/user-help-navigate?id=58\">כאן</a><br /> לפני ביצוע הייבוא.
לאחר ביצוע הייבוא ולפני שאת/ה מזין נתונים חדשים בתוכנה או מפיק/ה מסמכים בדוק/י בתשומת לב רבה שהחשבונות והמסמכים שייבאת נמצאים במקומם הנכון. למשל: בדוק/י שחשבונות לקוחות נמצאים בסוג חשבונות \"לקוחות\", ספקים בסוג \"ספקים\" וכן הלאה.<br />
כמו כן, אם בחרת לייבא גם מסמכים ותנועות, עיין/י בכרטסות החשבון במדגם מייצג של לפחות שלשה סוגי חשבונות והשווה/י לתנועות ולמסמכים המופיעים בכרטסת החשבון בתוכנה ממנה ייצאת את הנתונים.<br />
במידה וכל ההשוואות עולות יפה ויש התאמה, ברוך הבא ללינט, התחל/י לעבוד עם התוכנה. במידה ולא, מחק/י את החברה שהייבוא יצר (קישור \"החלף חברה\" למעלה משמאל->לחיצה על כפתור X בשורה של החברה אותה ייבאת תמחק אותה) ונסה/י לייבא את החומר מחדש.<br />
בהצלחה!
	
	";
	$text.="</div>";
	
	// http://www.linet.org.il/index.php/support/user-help-navigate?id=58
	$text.="<div style=\"margin-top: 10px; margin-right: 50px;  width: 600px;\" class=\"worning\">";
	
	$text.="load ini file <input type=\"file\" name=\"ini\" /><br />\n";
	$text.="load bkmvdata <input type=\"file\" name=\"bkmv\" /><br />\n";
	
	$text.=	_("Data to Import:");
	$text.= "<select id=\"data\" name=\"data\">\n";
	$l = _("Accounts");
	$text.= "<option value=\"0\">$l</option>\n";
	$l = _("Accounts And Items");
	$text.= "<option value=\"1\">$l</option>\n";
	$l = _("Everything");
	$text.= "<option value=\"2\">$l</option>\n";
	$text.= "</select>\n<br />";
	
	
	
	$l = _("Begin date");
	$text.= "&nbsp;&nbsp;$l: \n";
	$text.= "<input class=\"date\" type=\"text\" id=\"begin\" name=\"begin\" value=\"$begindmy\" size=\"7\" />\n";
	$text.= "&nbsp;&nbsp;\n";
	$l = _("End date");
	$text.= "$l: \n";
	$text.= "<input class=\"date\" type=\"text\" id=\"end\" name=\"end\" value=\"$enddmy\" size=\"7\" />\n";
	
	//$text.="<script type=\"text/javascript\">addDatePicker(\"#begin\",\"$begindmy\");addDatePicker(\"#end\",\"$enddmy\");</script>";
		
	
	$l = _("Next");
	$text.= "<input type=\"submit\" value=\"$l\" class='btnaction' />";
	//$text.="<input type=\"submit\" value=\"$l\" />";
	$text.="</div>";
	$text.= "</form>\n";
	
	createForm($text,$haeder,'',750,'','',1,getHelp());
	
}
if ($_REQUEST['step']==1){
	$bkmv = "tmp/bkmv$prefix.txt";
	$ini = "tmp/ini$prefix.txt";
	$inisize = (int)$_FILES['ini']['size'];
	$begindmy=strtotime($_REQUEST['begin']);
	$enddmy=strtotime($_REQUEST['end']);
	if($begindmy<$enddmy)
		$check=true;
	else 
		$check=false;
	if($inisize > 0) {	/* we have a file */
		$tmpname = $_FILES['ini']['tmp_name'];
		if (file_exists($tmpname)){   
			$orgname = $_FILES['ini']['name'];
			move_uploaded_file($tmpname, $ini);
	   }else{ 
	   		print 'error: unable to save ini';
	   }
	}
	$bkmvsize = (int)$_FILES['bkmv']['size'];
	if($bkmvsize > 0) {	/* we have a file */
		$tmpname = $_FILES['bkmv']['tmp_name'];
		if (file_exists($tmpname)){   
			$orgname = $_FILES['bkmv']['name'];
			move_uploaded_file($tmpname, $bkmv);
	   }else{ 
	   		print 'error: unable to save data file';
	   }
	}
	
	//get ini filter
	$filtera['inifile']=selectSql(array('type'=>'INI'),$table["openformattype"],null,null,array('id'));
	foreach ($filtera['inifile'] as &$type){
			$filter['inifile'][$type['str']]=selectSql(array('record'=>$type['id']),$table["openformat"],null,null,array('id'));
		}
	//set new company 
	if ($fp = fopen($ini, 'r')) {
		$line = fgets($fp);
		//get encoding
		if (substr($line,395,1)=='2')
			$encoding="ibm862";
		else 
			$encoding="windows-1255";

		$line=iconv($encoding,"utf-8",$line);
		$type=typeline(substr($line,0,4),$filter['inifile']);
		$obj=readline($line,$filter['inifile']["$type"]);
		
		
		if (!$obj){
			
		}else{
			if ($type=='A000'){

				global $prefix;
				$mainprefix=$prefix;
				$prefix=sha1($obj['prefix'].rand());
				
				//if company exists cancel
				//add permtions on company
				
				if ($obj['bidi']!=2) $obj['bidi']=1;
				$comp=new company;
				
				//$comp->getCompany;
				foreach($obj as $key=>$value){
					$softvendorregnum=$obj['softvendorregnum'];
					unset($obj['softvendorregnum']);
					if ($encoding=="ibm862") 
						$value = iconv("ISO-8859-8", "UTF-8", hebrev(iconv("UTF-8", "ISO-8859-8", $value)));
					$comp->$key=$value;
				}
				
				//$bidi 0irelvent 1 one side2 duuble
				$bidi=$obj['bidi'];
				
				delCompany($prefix);
					
				//$comp->companyname=hebrev($comp->companyname);
				if (!$comp->newCompany()){
					print 'error must stop cannot create company';
					return;
				}
			}
		}
		//}
	}else{
		die("unable to read ini");
	}
	//remove ini

	unlink($ini);
	/*data start */
	//get keys

	$filtera['bkmvfile']=selectSql(array('type'=>'BKMVDATA'),$table["openformattype"],null,null,array('id'));
	foreach ($filtera['bkmvfile'] as &$type){
		$filter['bkmvfile'][$type['str']]=selectSql(array('record'=>$type['id']),$table["openformat"],null,null,array('id'));
	}
	//sort file
	if ($fp = fopen($bkmv, 'r')) {
		//$newfile='';
		$Z900='';
		$fhb100 = fopen($bkmv."b100", 'w') or die("can't open file");
		$fhb110 = fopen($bkmv."b110", 'w') or die("can't open file");
		$fhc100 = fopen($bkmv."c100", 'w') or die("can't open file");
		$fhd110 = fopen($bkmv."d110", 'w') or die("can't open file");
		$fhd120 = fopen($bkmv."d120", 'w') or die("can't open file");
		$fhm100 = fopen($bkmv."m100", 'w') or die("can't open file");
		$data=$_REQUEST['data'];
		//fwrite($fh, $newfile);
		while ($line = fgets($fp)) {
			switch (substr($line,0,4)){
				case "A100":
					$newline=$line;
					break;
				case "B110":
					fwrite($fhb110, $line);
					break;
				case "B100":
					if ($data>=2)
						fwrite($fhb100, $line);
					break;
				case "C100":
					if ($data>=2)
						fwrite($fhc100, $line);
					break;
				case "D110":
					if ($data>=2)
						fwrite($fhd110, $line);
					break;
				case "D120":
					if ($data>=2)
						fwrite($fhd120, $line);
					break;
				case "M100":
					if ($data>=1)
						fwrite($fhm100, $line);
					break;
				case "Z900":
					$Z900=$line;
					break;
			}//*/
		}
		//fclose($fp);
		fclose($fhb100);
		fclose($fhb110);
		fclose($fhc100);
		fclose($fhd110);
		fclose($fhd120);
		fclose($fhm100);
		
		$fh = fopen($bkmv, 'w') or die("can't open file");

		fwrite($fh, $newline);
		
		$fp = fopen($bkmv."b110", 'r');
		while ($line = fgets($fp)) 
			fwrite($fh, $line);
		fclose($fp);
		$fp = fopen($bkmv."c100", 'r');
		while ($line = fgets($fp)) 
			fwrite($fh, $line);
		fclose($fp);
		$fp = fopen($bkmv."d110", 'r');
		while ($line = fgets($fp)) 
			fwrite($fh, $line);
		fclose($fp);
		$fp = fopen($bkmv."d120", 'r');
		while ($line = fgets($fp)) 
			fwrite($fh, $line);
		fclose($fp);
		$fp = fopen($bkmv."m100", 'r');
		while ($line = fgets($fp)) 
			fwrite($fh, $line);
		fclose($fp);
		$fp = fopen($bkmv."b100", 'r');
		while ($line = fgets($fp)) 
			fwrite($fh, $line);
		fclose($fp);
		unlink($bkmv."b100");
		unlink($bkmv."m100");
		unlink($bkmv."d120");
		unlink($bkmv."d110");
		unlink($bkmv."c100");
		unlink($bkmv."b110");
		
		fwrite($fh, $Z900);
		fclose($fh);
		
	}else{
		print "must die unable to sort data";
		die;
	}
	//print "rock n roll";
	if ($fp = fopen($bkmv, 'r')) {
		while ($line = fgets($fp)) {
			$line=iconv($encoding,"utf-8",$line);
			$type=typeline(substr($line,0,4),$filter['bkmvfile']);
			$obj=readline($line,$filter['bkmvfile']["$type"]);
			
			
			if (!$obj){
				$suc[$type]--;
			}else{
				foreach ($obj as &$value)
					if ($encoding=="ibm862") 
						$value = iconv("ISO-8859-8", "UTF-8", hebrev(iconv("UTF-8", "ISO-8859-8", $value)));
				
				if ($type=='B110'){//Acc Haeder
					/* Account Import */
					$acc=new account;
					//($softvendorregnum)
					//print $softvendorregnum;
					$obj["type"]=(int)$obj["type"]+50;					
					if(isset($accTypeIndex[$obj["type"]]))
							$accTypeIndex[$obj["type"]]=$accTypeIndex[$obj["type"]].",".$obj["company"];
						else 
							$accTypeIndex[$obj["type"]]=$obj["typedesc"].":".$obj["company"];
					unset($obj["typedesc"]);
					//1405 acc type code
					//1406 acc type name
					foreach($obj as $key=>$value){
						$acc->$key=$value;
					}
					//print_r($acc);
					$accIndex[$obj["num"]]=$acc->newAccount();
					//get new acc index save old
					unset($acc);
					
				}
				if ($type=='C100'){//Doc Haeder
					//find type
					global $DocOpenType;
					//print_r($accIndex);
					//print($obj['doctype'].";".$obj['account'].";<br />");
					if ((isset($DocOpenType[$obj['doctype']])) && (isset($accIndex[$obj['account']]))){
						$obj['doctype']=$DocOpenType[$obj['doctype']];
						$doc=new document($obj['doctype']);
						$stype=$obj['doctype'];
						//unset($obj['doctype']);
						foreach($obj as $key=>$value){
							$doc->$key=$value;//print "$key <br />";
						}
						$doc->account=$accIndex[$doc->account];
						//search for old acc index
						if (isset($doc->rcptdetials)) unset($doc->rcptdetials);
						if (isset($doc->docdetials)) unset($doc->docdetials);
						//print_r($doc);
						
						if($check){
							if((strtotime($doc->issue_date)>$begindmy)&&(strtotime($doc->issue_date)<$enddmy))
								$docIndex[$stype.$obj["docnum"]]=$doc->newDocument();
							//print "we are chking!";
						}else{
							$docIndex[$stype.$obj["docnum"]]=$doc->newDocument();
						}
						//get new doc index save old
						unset($doc);
					}
				}
				if ($type=='D110'){//Doc Detial
					global $DocOpenType;
					$stype=$DocOpenType[$obj['doctype']];
					//print_r($obj);
					if (isset($docIndex[$stype.$obj["num"]])){		
						$docdetial=new documentDetail;
						$docdetial->price=$obj['price'];
						unset($obj['doctype']);
						unset($obj['price']);
						
						foreach($obj as $key=>$value){
							$docdetial->{$key}=$value;//print "$key <br />";
							
						}
						
						//print_r($docdetial);
						$docdetial->num=$docIndex[$stype.$obj["num"]];	
						$docdetial->newDetial();
						
						//search for old doc index
						//die;
						//update to new index
						unset($docdetial);
					}
				}
				if ($type=='D120') {//Kaballa Detial
					global $DocOpenType;
					$stype=$DocOpenType[$obj['doctype']];
					//print_r($obj);
					if (isset($docIndex[$stype.$obj["refnum"]])){
						$rcptdetial=new receiptDetail();
						//$stype=$DocOpenType[$obj['doctype']];
						$rcptdetial->sum=(float)$obj['sum'];
						unset($obj['sum']);
						unset($obj['doctype']);
						foreach($obj as $key=>$value){
							$rcptdetial->$key=$value;
						}
						//print_r($rcptdetial);
						$rcptdetial->refnum=$docIndex[$stype.$obj["refnum"]];	
						$rcptdetial->newDetial();
						//search for old doc index
						//update to new index
						unset($rcptdetial);
					}
				}
				//print "?";
				if ($type=='B100'){//Move Recored
					//print $obj['value'].":".$obj['type'];
					global $openTransType;
					//print_r($openTransType);
					//print_r($accIndex);
					/*
					if ((isset($accIndex[$obj['account']])) && (isset($accIndex[$obj['account1']]))){
						$bsum= $obj['sum'];
						if($obj['value']==1)
							$bsum= -1 * $obj['sum'];
						//print $bsum."<br />\n";
						$usum=$bsum*-1;
						
						$uaccount=$obj['account1'];
						$stype=$openTransType[$obj['type']];
						unset($obj['value']);
						unset($obj['account1']);
						//adam:! need to reset type of action!
						$transaction=new transaction;
						foreach($obj as $key=>$value){
							$transaction->$key=$value;//print "$key <br />";
						}
						$transaction->type=$stype;
						$transaction->sum=$bsum;
						$transaction->account=$accIndex[$obj['account']];
						
						
						//only if bi side
						$transactiona=new transaction;
						foreach($obj as $key=>$value){
							$transactiona->$key=$value;
						}
						$transactiona->type=$openTransType[$obj['type']];
						$transactiona->account=$accIndex[$uaccount];
						$transactiona->sum=$usum;
						if($check){
								if((strtotime($transaction->date)>$begindmy)&&(strtotime($transaction->date)<$enddmy)){
									$transaction->newTransactions();
									$transactiona->newTransactions();
								}
									
							}else{
								$transaction->newTransactions();
								$transactiona->newTransactions();
							}
						unset($transaction);
						unset($transactiona);
					}else//*/
				if (isset($accIndex[$obj['account']])){
						$bsum= $obj['sum'];
						if($obj['value']==1)
							$bsum= -1 * $obj['sum'];
						//print $bsum."<br />\n";
						$usum=$bsum*-1;
						
						$uaccount=$obj['account1'];
						$stype=$openTransType[$obj['type']];
						unset($obj['sum']);
						unset($obj['value']);
						unset($obj['account1']);
						//adam:! need to reset type of action!
						$transaction=new transaction;
						foreach($obj as $key=>$value){
							$transaction->$key=$value;//print "$key <br />";
						}
						$transaction->type=$stype;
						$transaction->sum=$bsum;
						$transaction->account=$accIndex[$obj['account']];
						
						if($check){
								if((strtotime($transaction->date)>$begindmy)&&(strtotime($transaction->date)<$enddmy)){
									$transaction->newTransactions();
								}
									
							}else{
								$transaction->newTransactions();
							}
						unset($transaction);
					}
				}
				if ($type=='M100'){//Item In Stock
					$item=new item;
					foreach($obj as $key=>$value){
						$item->$key=$value;
					}
					$item->newItem();
					unset($item);
				}
				unset($obj);
				$suc[$type]++;
			}
			$analze[$type]++;
			//if ($analze[$type]>100)	break;
		}
		
		//print_r($docIndex);
		//end loop
		//print_r($accIndex);
	}else{
		print "error cant open file!";
	}
	
	
	$haeder="Select Matching Account Types";
	$text= "<form action=\"?module=$module&prefix=$prefix&step=2\" method=\"post\" name=\"main\" enctype=\"multipart/form-data\">\n<br />";
	//$text.="load ini file <input type=\"file\" name=\"ini\" /><br />\n";
	//$text.="load bkmvdata <input type=\"file\" name=\"bkmv\" /><br />\n";
	$text.="<table>";
	foreach ($accTypeIndex as $key=>$type){
			$text.="<tr><td>$type</td><td>".PrintAccountType($key)."</td></tr>";
			//print "Type: ".$type."<br />";
		}
	$text.="</table>";
	$l = _("Next");
	$text.= "<input type=\"submit\" value=\"$l\" class='btnaction' />";
	//$text.="<input type=\"submit\" value=\"$l\" />";
		
	$text.= "</form>\n";
	
	createForm($text,$haeder,'',750,'','',1,getHelp());
	
	//unlink($bkmv);
	//print_r($analze);
	//print_r($suc);
}
if ($_REQUEST['step']==2){
	global $table;
	$type=$_REQUEST['type'];
	$prefix=$_REQUEST['prefix'];
	foreach ($type as $oldtype=>$newtype){	  
		$query =  "UPDATE ".$table['accounts']." SET type='$newtype' WHERE prefix='$prefix' AND type='$oldtype'" ;
		$result = mysql_query($query);
		//print $query."<br />";
	}
	print _("Data Import Complted Sucsesfuly");
	$l=_("Finish");
	print "<a href=\"index.php?action=unsel\">$l</a>";
}

function PrintAccountType($id) {
	global $AcctType;
	$text='';
	$text.= "<select id=\"type[$id]\" name=\"type[$id]\">\n";
	$l = _("Select type");
	$text.= "<option value=\"0\">-- $l --</option>\n";
	foreach ($AcctType as $id=>$name)
		$text.= "<option value=\"$id\">$name</option>\n";
	$text.= "</select>\n";
	return $text;
}//*/
function readline($line, $filter){
	$pos=0;
	$object=array();
	$first=true;
	foreach ($filter as $value){
		$str=mb_substr($line,$pos,$value['size'],"utf-8");
		$pos+=$value['size'];
		if(fieldvalid($str,$value['type'])){
			if (($value['action']!="??") && ($value["action"]!="NA")){
				if ($first){
					
					$first=false;
				}else{
					$object[$value["action"]]=fieldvalue($str,$value['type'],$value['action']);
				}
			}
			//store field into var
		}else{
			return false;
		}
	}

	return $object;
}
function typeline($str, $filter){	
	foreach ($filter as $key=>$value){
			if ($str==$key)
				return $key;
		}
		return "UNKO";//need to stop from entring readline
}
function fieldvalid($str,$type){
	return true;
	//chek aginst type
	
}
function fieldvalue($str,$type,$action){
	switch ($type){
		case "date":
			return substr($str,0,4)."-".substr($str,4,2)."-".substr($str,6,2);
			break;
		case "hour":
			return $str;
			break;
		case "v99":
			$a=substr($str,0,1);
			$str=substr($str,1)/100;
			return number_format($str, 2, '.', '');;
			break;
		case "v9999":
			$a=substr($str,0,1);
			$str=substr($str,1)/1000;
			return number_format($str, 4, '.', '');
			break;
		case "s":
			return ltrim( $str , ' 0!'  ); //iconv("windows-1255","utf-8",$str);
			break;
		case "n":
			$str=ltrim( $str , ' 0!'  );
			return (int)$str;
			break;
		default:
			return ltrim( $str , ' 0!'  );
	}
	//chek aginst type
	//and parse by action
}
?>