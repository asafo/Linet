<?php
/*Written By Adam BH pcn874 for linet*/
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $chequestbl, $receiptstbl, $creditcompanies, $docstbl, $itemstbl;
global $bkrecnum, $regnum, $mainid, $softregnum, $softwarename, $Version, $softwaremakerregnum, $softwaremaker;
include 'class/account.php';
$text='';
if(!isset($prefix) || ($prefix == '')) {
	ErrorReport(_("This operation can not be executed without choosing a business first"));
	//print "<h1>$l</h1>\n";
	return;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 0) {	/* First stage, choose dates for report */
	$date = date('m-Y',mktime(0, 0, 0, (date('m')), 0, date('Y'))); //date("31-12-$y");

	//print "<div class=\"form righthalf1\">\n";
	$header = _("Export pcn874 files for tax authorities"); 
	$text.= "<form name=\"dtrange\" action=\"?module=pcn874&amp;step=1\" method=\"post\">\n";
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";

	$text.= "</td>\n";
	$text.= "</tr><tr><td colspan=\"2\">&nbsp;</td></tr><tr>\n";
	//$l = _("To date");
	$l = _("To date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" id=\"date\" name=\"date\" value=\"$date\" size=\"10\" />\n";
//l$text.='<script type="text/javascript">addDatePicker("#enddate","'.$enddate.'");</script>';
$text.="<script type=\"text/javascript\">$(function() {
    $('#date').datepicker( {
        changeMonth: true,changeYear: true,showButtonPanel: true,dateFormat: 'm-yy',
        onClose: function(dateText, inst) { 
            var month = $(\"#ui-datepicker-div .ui-datepicker-month :selected\").val();
            var year = $(\"#ui-datepicker-div .ui-datepicker-year :selected\").val();
            $(this).datepicker('setDate', new Date(year, month, 1));}
    });
});</script>";
	
/*help included:*/
	$text.= "</td>\n";
	$text.= "</tr><tr><td colspan=\"2\">&nbsp;</td></tr><tr>\n";
	$l = _("Submit");
	$text.= "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\" /></td>\n";
	$text.= "</tr>\n";
	$text.= "</table>\n</form>\n";
	//print "</div>\n";
	createForm($text,$header,'',750);
	
}
else if($step == 1) {
	$b = $_POST['date'];

	$begindate='01-'.$b;
	$enddate=date("t",strtotime($begindate))."-".$b;
	$begindate = strftime('%Y-%m-%d',strtotime($begindate));
	$enddate = strftime('%Y-%m-%d',strtotime($enddate));
	//print "<br />(".$begindate.";".$enddate.")<br />";
	global $transactionstbl;
	$cond['prefix']=$prefix;

	$list=selectSql($cond,$transactionstbl,null,array('min'=>$begindate,'max'=>$enddate),array('num','date'));
	$correntnum=-1;
	$rhp=selectSql($cond,$companiestbl,array('regnum'));
	$rType='-1';//sami
	$rHp='000000000';//sami
	$rDate='YYYYMMDD';//go;
	$rRefGroup='0000';//go;
	$rRef='000000000';
	$rTax='000000000';//go;
	$rPlus='+';//go;
	$rSum='0000000000';//go;
	$rMore='000000000';//go;
	
	$list[]=array(0,0,0,0);
	$sFile;//main file lines
	
	$fHp=$rhp[0]['regnum'];//9  //first line haeders vars
	$fDate=str_replace('-', '', $b);//6
	$fType=1;//1
	$fPDate=date('Ymd');//8
	
	
	$fSum=0;//11+
	$fSumVat=0;//9+
	$fSumA=0;//11+
	$fSumVatA=0;//9+
	$fInvNum=0;//9
	$fNoVatSum=0;//11+
	
	$fExpSumOthers=0;//9+
	$fExpSumGears=0;//9+
	$fExpNum=0;//9
	$fAll=0;//11+
	foreach ($list as $key=>$row) {
		if ($correntnum==-1) $correntnum=$row['num'];
		if ($correntnum!=$row['num']){
			//print '<br />'.$correntnum.":";//writeline
			if ($rType!='-1')
				//print '['.$rType.$rHp.']'.$rDate.$rRefGroup.'['.$rRef.']'.$rTax.'['.$rSum.']'.$rMore;
				$sFile.=$rType.$rHp.$rDate.$rRefGroup.$rRef.$rTax.$rSum.$rMore."\r\n";
			$correntnum=$row['num'];
			//unset
			$rType='-1';//sami
			$rHp='000000000';//sami
			$rDate='YYYYMMDD';//go;
			$rRefGroup='0000';//go;
			$rRef='000000000';
			$rTax='000000000';//go;
			$rPlus='+';//go;
			$rSum='0000000000';//go;
			$rMore='000000000';//go;
		}if ($row['account']==SELLVAT) {
			$rTax=strrip((int)$row['sum'],9);
			$fSumVat+=-1*(int)$row['sum'];
		}else if ($row['account']==BUYVAT) {
			$rTax=strrip((int)$row['sum'],9);
			$fExpSumOthers+=(int)$row['sum'];
		}else if ($row['account']==ROUNDING) print '';
		else {
			$rDate=strrip($row['date'],8);
			$acc=new account;
			$acc->num =$row['account'];
			if ($acc->getAccount()){
				if (($acc->type==CUSTOMER && ($row['type']==1))){
						$rType='S';
						$rHp=strrip((int)$acc->vatnum,9);//need to find invoice
						if ($rHp=='000000000')
							$rType='L';
						$rRef=strrip((int)$row['refnum1'],9);
						$rSum=strrip(-1*(int)$row['sum'],10,'+');
						$fInvNum++;
				}else if ($acc->type==SUPPLIER){
							$rType='T';
							$rHp=strrip((int)$acc->vatnum,9);//or shuld be acc?
							//print "<br />\n\r[".$rHp.']';
							if ($rHp=='000000000')
								$rType='K';
							$rRef=strrip((int)$row['refnum1'],9);
							$rSum=strrip((int)$row['sum'],10,'+');
							$fExpNum++;
				}else if ($acc->type==INCOME){
						$fSum+=(int)$row['sum'];//first line sum
				}
			}
		}
	}
	
	/*end for*/
	
	$fAll=$fSumVat+$fSumVatA-$fExpSumOthers-$fExpSumGears;
	//print summary
	//first line
	$fHp=strrip($fHp,9);//9
	$fDate=strrip($fDate,6);//6
	$fType=strrip($fType,1);//1
	$fPDate=strrip($fPDate,8);//8
	
	
	$fSum=strrip($fSum,11,'+');//11
	$fSumVat=strrip($fSumVat,11,'+');//9+
	$fSumA=strrip($fSumA,11,'+');//11+
	$fSumVatA=strrip($fSumVatA,9,'+');//9+
	$fInvNum=strrip($fInvNum,9);//9
	$fNoVatSum=strrip($fNoVatSum,11,'+');//11+
	
	$fExpSumOthers=strrip($fExpSumOthers,9,'+');//9+
	$fExpSumGears=strrip($fExpSumGears,9,'+');//9+
	$fExpNum=strrip($fExpNum,9);//9
	$fAll=strrip($fAll,11,'+');//11+
	
	$firstline='O'.$fHp.$fDate.$fType.$fPDate.$fSum.$fSumVat.$fSumA.$fSumVatA.$fInvNum.$fNoVatSum.$fExpSumOthers.$fExpSumGears.$fExpNum.$fAll."\r\n";
	//last line
	$lastline='X'.$fHp;
	//	print $firstline.$sFile.$lastline;
	
	$dir="tmp/$prefix";
	//if 
	if ((mkdir($dir)) || (is_dir($dir))){
		
		$pcndata = fopen("$dir/pcn874.txt", "w") or die("can't open file");
		fwrite($pcndata, $firstline.$sFile.$lastline);
		fclose($pcndata);
		
		
		//print "<div class=\"form righthalf1\">\n";
		$l = _("Link to file");
		$text.= "<br />$l: ";
		$text.= "<a href=\"$dir/pcn874.txt\">pcn874.txt</a><br />\n";
		}else{
		$text.= '׳�׳™׳� ׳�׳₪׳©׳¨׳•׳× ׳�׳™׳¦׳•׳¨ ׳§׳•׳‘׳¥';
	}
	//print "</div>";
	
	createForm($text,$header,'',750);
}
function strrip($str,$i,$plus=null,$zero='0'){
	if (!is_null($plus)) 
		if ($str>=0) $plus='+'; else $plus='-';
	$str=str_replace('-', '', $str);
	$str=str_pad($str, $i, $zero,STR_PAD_LEFT);
	while (strlen($str)!=$i){
		$str =substr($str ,1);
	}
	if (!is_null($plus)) $str=$plus.$str;
	return $str;
}
?>