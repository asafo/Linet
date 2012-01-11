<?php 
global $prefix;
global $accountstbl, $bankbooktbl, $transactionstbl;
global $correlationtbl,$curuser,$begin,$end,$TranType;
$text='';
$step=(int)GetPoster('step');
$acc=(int)GetPoster('account');
$num=(int)GetPoster('cornum');
/*
function PrintAccountSelect() {
	global $accountstbl, $prefix;

	$type1 = CUSTOMER;
	$type2 = SUPPLIER;
	$text='';
	$query = "SELECT num,company FROM $accountstbl WHERE type='$type1' AND prefix='$prefix' ORDER BY company ASC";
	$result = DoQuery($query, __LINE__);
	$text.= "<select id=\"account\" name=\"account\">\n";
	$l = _("Select account");
	$text.= "<option value=\"0\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$text.= "<option value=\"$num\">$name</option>\n";
	}
	$query = "SELECT num,company FROM $accountstbl WHERE type='$type2' AND prefix='$prefix' ORDER BY company ASC";
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$text.= "<option value=\"$num\">$name</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}*/

if($step==2){//do action and set step 1
	if($num!=0){
		if(updateSql(array('prefix'=>$prefix,'num'=>$num), array("status"=>CLOSED), $correlationtbl))
			if(updateSql(array('prefix'=>$prefix,'cor_num'=>$num), array("cor_num"=>0), $transactionstbl))
				$text.=_("Correlation No.:")." $num "._("was canceld");
	}else{
		$text.=_("no correlation was selected");
	}
	$step=1;
}
if($step==1){//account chosen display detiales
	if($acc!=0){
		$query = "SELECT * FROM $transactionstbl WHERE account='$acc' AND prefix='$prefix' AND cor_num!='0' AND (date>=$begin OR date<=$end)";
		$result = DoQuery($query, "Select account");
		$cors=array();
		$transa=array();
		while($trans = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$transa[$trans["num"].":".$trans["id"]]=$trans;
			if(!isset($cors[$trans["cor_num"]])){
				$cor=selectSql(array("prefix"=>$prefix,"num"=>$trans["cor_num"],"status"=>OPEN), $correlationtbl);
				$cors[$cor[0]['num']]=$cor[0];
			}
		}
		$text.="<form>";
		$text.="<table class=\"tablesorter\"><thead><tr><th >"._("Debit")."</th><th>"._("Credit")."</th><th>"._("Action")."</th></tr></thead>";
		$text.="<tfoot><tr><td></td><td></td><td></td></tr><tfoot>";
		$text.="<tbody>";
		foreach($cors as $cor){
			$num=$cor['num'];
			$text.="<tr><td>";//Debit
			$text.="<table class=\"small\" style=\"width:100%\">";
			$debit=explode(',',$cor['hova']);
			foreach($debit as $rec){
				$text.="<tr>";
				if(isset($transa[$rec])){
							$text.="<td>".$transa[$rec]["num"]."</td>";
							$text.="<td>".$TranType[$transa[$rec]["type"]]."</td>";
							$text.="<td>".$transa[$rec]["date"]."</td>";
							$text.="<td>".$transa[$rec]["sum"]."</td>";
				}else {
					print "bad transaction detailes: $rec";
					exit;
				}
				$text.="</tr>";
			}
			$text.="</table>";
			$text.="</td><td>";//Credit
			$text.="<table class=\"small\" style=\"width:100%\">";
			$credit=explode(',',$cor['zchot']);
			foreach($credit as $rec){
				$text.="<tr>";
				if(isset($transa[$rec])){
							$text.="<td>".$transa[$rec]["num"]."</td>";
							$text.="<td>".$TranType[$transa[$rec]["type"]]."</td>";
							$text.="<td>".$transa[$rec]["date"]."</td>";
							$text.="<td>".$transa[$rec]["sum"]."</td>";
				}else{
					print "bad transaction detailes: $rec";
					exit;
				}
				$text.="</tr>";
			}
			$text.="</table>";
			
			$text.="</td><td>";//action
			$l=_("Cancel Correlation");
			$text.="<a href=\"?module=dispmatch&step=2&account=$acc&cornum=$num\" class=\"btnsmall\">$l</a></td></tr>";
		}
		$text.="</tbody>";
		$text.="</table>";
		$text.="</form>";
	}else{
		$step=0;
	}
	
}
if($step==0){//choose account
	$text.="<form name=\"dispmatch\" action=\"?module=dispmatch&step=1\" class=\"valform\" id=\"dispm\" method=\"post\">";
	$l=_("Please choose an account to match");
	$text.="$l<br />";
	$text.=PrintAccSelect($acc,'account','A');
	$text.="<span id=\"accountname\"></span><br />";
	
	$l=_("From Date:");
	$text.="$l<input id=\"begin\" type=\"text\" value=\"$begin\" class=\"date\" name=\"begin\" />";
	$l=_("To Date:");
	$text.="$l<input id=\"end\" type=\"text\" value=\"$end\" class=\"date\" name=\"end\" />";
	$l = _("Execute");
	$text.="<input type=\"submit\" value=\"$l\" class=\"btnaction\" />";
	
	$text.="</form>";
}
$haeder=_("Correlation Display");
createForm($text, $haeder,"",750,"","$logo",true,$help);
?>