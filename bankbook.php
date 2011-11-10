<?PHP
/*
 | Accounting documents handling script for Drorit Free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 | Modified By Adam BH
 | This program is a free software licensed under the GPL 
 */
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}
$text='';
$linesperpage = 20;

global $bankbooktbl, $accountstbl;

$start = isset($_GET['start']) ? $_GET['start'] : -1;
$account = isset($_GET['account']) ? $_GET['account'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$step=isset($_REQUEST['step']) ? $_REQUEST['step'] : 0;

if($start > 0) {
	$query = "SELECT total FROM $bankbooktbl WHERE account='$account' AND prefix='$prefix' AND num='$start'";
	$result = DoQuery($query, __LINE__);
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$lasttotal = $line[0];
}
	
?>
<script type="text/javascript">
function DebitChange(index) {
	var totala = document.getElementsByClassName('total');

	if(index > 0)
		var lasttotal = parseFloat(totala[index-1].value);
	else
		<?PHP print "\t\tvar lasttotal = parseFloat($lasttotal)\n"; ?>
	var debita = document.getElementsByClassName('debit');
	var debit = parseFloat(debita[index].value);
	var credita = document.getElementsByClassName('credit');
	var credit = credita[index];
	var totala = document.getElementsByClassName('total');
	var total = totala[index];
	var lastdatea = document.getElementsByClassName('date');
	var lastdate = lastdatea[index].value;

	if(debit > 0) {
		credit.value = '';
		total.value = lasttotal - debit;
		total.value = Math.round(total.value * 100)/100;
	}
		
	index = index + 1;
	var lastdate1 = lastdatea[index].value;
	if(lastdate1 == '') {
		document.getElementsByClassName('date')[index].value = lastdate;
	}
}

function CreditChange(index) {
	var totala = document.getElementsByClassName('total');
	
	if(index > 0)
		var lasttotal = parseFloat(totala[index-1].value);
	else
		<?PHP print "\t\tvar lasttotal = parseFloat($lasttotal)\n"; ?>
	var debita = document.getElementsByClassName('debit');
	var debit = debita[index];
	var credita = document.getElementsByClassName('credit');
	var credit = parseFloat(credita[index].value);
	var total = totala[index];
	var lastdatea = document.getElementsByClassName('date');
	var lastdate = lastdatea[index].value;

	if(credit > 0) {
		debit.value = '';
		total.value = lasttotal + credit;
		total.value = Math.round(total.value * 100)/100;
	}
		
	index = index + 1;
	var lastdate1 = lastdatea[index].value;
	if(lastdate1 == '') {
		document.getElementsByClassName('date')[index].value = lastdate;
	}	
}
</script>
<?PHP

//adam read movin dat:
function readlineHashDos($line,$account){//mizrahi
	$refnum=ltrim(substr($line,12,6),' ');
	if ($refnum>0){
		$bank = new bankbook;
		$bank->account=$account;
		
		$bank->refnum=ltrim(substr($line,12,5),' ');	
		$bank->details=iconv("ISO-8859-8","utf-8",hebrev(iconv("ibm862","ISO-8859-8",substr($line,19,11))));
		$bank->date=date("Y")."-".substr($line,34,2)."-".substr($line,31,2);
		
		$zachot=ltrim(substr($line,38,12),' ');
		$hova=ltrim(substr($line,51,13),' ');
		$bank->sum=$zachot-$hova;
		$bank->total=ltrim(substr($line,65,14),' ');
		if (!$bank->searchBankbook()){
			$bank->cor_num=0;
			//mybe save output? num
			return $bank->newBankbook();
		}
		
	}
}
function readlineHashWin($line,$account){//discount
	if(strlen($line)==56)
		return false;
	$refnum=ltrim(substr($line,21,9),'0 ');
	if ($refnum>0){
		$bank = new bankbook;
		$bank->account=$account;
		
		$bank->refnum=$refnum;//ltrim(substr($line,22,7),' ');	
		$bank->details=iconv("ISO-8859-8","utf-8",hebrev(iconv("ibm862","ISO-8859-8",substr($line,30,7))));
		$bank->date="20".substr($line,2,2)."-".substr($line,4,2)."-".substr($line,6,2);
		$sighn=substr($line,20,1).'1';
		$value=(ltrim(substr($line,9,12),'0 '))*$sighn/100;
		//$hova=ltrim(substr($line,51,13),' ');
		$bank->sum=$value;//$zachot-$hova;
		
		//$bank->total=ltrim(substr($line,65,14),' ');
		if (!$bank->searchBankbook()){
			//mybe save output? num
			$bank->cor_num=0;
			//print_r($bank);
			return $bank->newBankbook();
		}
	}
}
function readlineLeumi($line,$account){//leumi wtf???
	//if(strlen($line)==56)
	//	return false;
	$refnum=ltrim(substr($line,0,7),'0');
	if ($refnum>0){
		$bank = new bankbook;
		$bank->account=$account;
		
		$bank->refnum=$refnum;//ltrim(substr($line,22,7),' ');	
		$bank->details=iconv("ISO-8859-8","utf-8",hebrev(iconv("ibm862","ISO-8859-8",substr($line,16,14))));
		$bank->date="20".substr($line,12,2)."-".substr($line,10,2)."-".substr($line,8,2);
		$sighn=substr($line,32,1).'1';
		$value=(ltrim(substr($line,33,12),'0 '))*$sighn;
		$bank->sum=$value;//$zachot-$hova;
		
		$sighn=substr($line,46,1).'1';
		$bank->total=(ltrim(substr($line,47,12),'0 '))*$sighn;
		if (!$bank->searchBankbook()){
			//mybe save output? num
			$bank->cor_num=0;
			return $bank->newBankbook();
		}
	}
}


/* Get last line */
// First get last line number
$query = "SELECT MAX(num) FROM $bankbooktbl WHERE prefix='$prefix' AND account='$account'";
$result = DoQuery($query, __LINE__);
$line = mysql_fetch_array($result, MYSQL_NUM);
$LastLine = $line[0];
// print "LastLine: $LastLine<br />\n";
if($start == -1)
	$start = $LastLine - ($linesperpage - 2);
// print "start: $start<br>\n";

if($action == 'banksubmit') {
	$numarr = $_POST['num'];
	$date = $_POST['date'];
	$details = $_POST['details'];
	$refnum = $_POST['refnum'];
	$debit = $_POST['debit'];
	$credit = $_POST['credit'];
	$total = $_POST['total'];
	
	foreach($numarr as $i => $num) {
		$date1 = FormatDate($date[$i], "dmy", "mysql");
		$darr = explode('-', $date1);
		if($darr[0] == 0) {
			$l = _("Invalid date at line: ");
			ErrorReport("$l: $i");
			exit;
		}
		$details1 = sqlText($details[$i]);
		if(empty($details1))
			continue;
		$refnum1 = sqlText($refnum[$i]);
		$debit1 = (double)$debit[$i]; 
		$credit1 = (double)$credit[$i];
		$total1 = (double)$total[$i];

		if($debit1)
			$sum = $debit1 *= -1.0;
		else
			$sum = $credit1;
		
		if(!$num) {
			$LastLine++;
			$query = "INSERT INTO $bankbooktbl VALUES($LastLine, '$prefix', '$account', ";
			$query .= "'$date1', '$details1', '$refnum1', '$sum', '$total1', '0')";
			$result = DoQuery($query, __LINE__);
		}
		else {
			/* first check cor_num, we can not change if cor_num != 0 */
			$query = "SELECT cor_num FROM $bankbooktbl WHERE num='$num' AND prefix='$prefix'";
			$result = mysql_query($query);
			if(!$result) { echo mysql_error(); exit; }
			$line = mysql_fetch_array($result, MYSQL_NUM);
			$n = $line[0];
			if($n == 0) {
				$query = "UPDATE $bankbooktbl SET \n";
				$query .= "date='$date1', \n";
				$query .= "details='$details1', \n";
				$query .= "refnum='$refnum1', \n";
				$query .= "sum='$sum', \n";
				$query .= "total='$total1' \n";
				$query .= "WHERE num='$num' AND prefix='$prefix'";
				// print "Query: $query<BR>\n";
				$result = mysql_query($query);
				if(!$result) {
					echo mysql_error();
					exit;
				}
			}
		}
	}
}

if($step==1) {

	$tnout = "tmp/tnout$prefix.txt";
	$inisize = (int)$_FILES['tnout']['size'];
	if($inisize > 0) {	/* we have a file */
		$tmpname = $_FILES['tnout']['tmp_name'];
		if (file_exists($tmpname)){   
			$orgname = $_FILES['tnout']['name'];
			move_uploaded_file($tmpname, $tnout);
	   }else{ 
	   		print 'error';
	   }
	}
	
	if ($fp = fopen($tnout, 'r')) {
		require_once 'class/bankbook.php';
		$first=true;
		while ($line = fgets($fp)){
			if($first){
				if((strlen($line)==83)&&(substr($line,0,1)=='#')) $type='HashDos';
				else if(strlen($line)==2) $type='HashWin';
				else if((strlen($line)==81)&&(substr($line,7,1)==',')) $type='leumi';
				$first=false;
				//return;
			}
			if(isset($type))
				switch ($type){
					case 'HashDos':
						readlineHashDos($line,$account);
						break;
					case 'HashWin':
						readlineHashWin($line,$account);
						break;
					case 'leumi':
						readlineLeumi($line,$account);
						break;
				}
			else {
				print _("Unkown file format");
				return;
			}
			
		}//while end
		print _("Import Complted!");
		print "<meta http-equiv=\"refresh\" content=\"3;url=?module=bankbook&account=$account\" />  ";
		return;
	}
	unset($tnout);
} //end step
if(!$account) {
	$text='';
	$header = _("Bank papers input");
	//print "<h3>$l</h3>\n";

	/* Choose account */
	$text.= "<form name=\"choosebank\" method=\"get\">\n";
	$text.= "<input type=\"hidden\" name=\"module\" value=\"bankbook\"/>\n";
	$text.= "<div class=\"formtbl\" style=\"padding-right:10px;font-size:16px\">\n";
	$t = BANKS;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$t' AND prefix='$prefix'";
	$result = DoQuery($query, "Select account");
	$l = _("Choose bank account");
	$text.= "<h2>$l</h2><br>\n";
	$i = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		if($num > 100) {
			//print $line['company'];
			$acctname = $line['company'];
			$text.= "<input type=\"radio\" name=\"account\" value=\"$num\" ";
			if($i == 0)
				$text.= "checked";
			$i++;
//			print ">&nbsp;$acctname</td></tr>\n";
			$text.= "/>&nbsp;$acctname\n";
		}
	}
	$text.= "<br />\n";
	$l = _("Display");
	$text.= "<div style=\"text-align:center\"><br><input type=submit value=\"$l\" /></div>\n";
	$text.= "</div>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	createForm($text,$haeder,$sClass,500,600,'',1,getHelp());
	exit;
}


//import gui here
$text.= "<form name=\"import\" action=\"?module=bankbook&amp;account=$account&amp;start=1&step=1\" method=\"post\"  enctype=\"multipart/form-data\">\n";
$text.=_("Import bank book from Hashvsvet file(tnout.dat):");
$text.="<input type=\"file\" name=\"tnout\" /><br />\n";
$text.="<a class=\"btn\" href=\"javascript:document.import.submit();\">בצע</a>";
$text.="</form>";


$text.= "<form name=\"form1\" action=\"?module=bankbook&amp;action=banksubmit&amp;account=$account&amp;start=$start\" method=\"post\">\n";
//$text.=
$text.=  "<table>\n";
$text.=  "<tr class=\"tblhead\">\n";
$text.=  "<td>&nbsp;</td>\n";
$text.=  "<td>#</td>\n";
$l = _("Date");
$text.=  "<td>$l</td>\n";
$l = _("Details");
$text.=  "<td>$l</td>\n";
$l = _("Ref. num");
$text.=  "<td>$l</td>\n";
$l = _("Debit");
$text.=  "<td>$l</td>\n";
$l = _("Credit");
$text.=  "<td>$l</td>\n";
$l = _("Acc. balance");
$text.=  "<td>$l</td>\n";
$text.=  "</tr>\n";

if($start == '')
	$start = 0;
$query = "SELECT * FROM $bankbooktbl WHERE prefix='$prefix' AND account='$account' AND num>'$start' ORDER BY num LIMIT $linesperpage";
// print "Query: $query<br>\n";
$result = DoQuery($query, __LINE__);
$firstnum = $start;
for($i = 0; $i < $linesperpage; $i++) {
	$line = @mysql_fetch_array($result, MYSQL_ASSOC);
	if($line) {
		$cor_num = $line['cor_num'];
		$num = $line['num'];
	/*	if($i == 0)
			$firstnum = $num; */
		$date = FormatDate($line['date'], "mysql", "dmy");
		$details = stripslashes($line['details']);
//		$details = htmlspecialchars($details);
		$refnum = $line['refnum'];
		$sum = $line['sum'];
		if($sum < 0) {
			$debit = $sum * -1.0;
			$credit = '';
		}
		else {
			$debit = '';
			$credit = $sum;
		}
		$total = $line['total'];
	}
	else {
		$cor_num = 0;
		$num = 0;
		$date = '';
		$details = '';
		$refnum = '';
		$debit = '';
		$credit = '';
		$total = '';
		$result = 0;
	}
	$text.=  "<tr>\n";
	$text.=  "<td>\n";
	if($cor_num)
		$text.=  "<ul><li>&nbsp</li></ul></td>\n";	/* print a dot */
	else
		$text.=  "&nbsp;</td>\n";
	if($num == 0)
		$text.=  "<td><input type=\"text\" class=\"num\" name=\"num[]\" value=\"\" size=\"4\" readonly=\"readonly\" /></td>\n";
	else
		$text.=  "<td><input type=\"text\" class=\"num\" name=\"num[]\" value=\"$num\" size=\"4\" readonly=\"readonly\" /></td>\n";
	$text.=  "<td><input type=\"text\" class=\"date\" name=\"date[]\" value=\"$date\" size=\"9\" /></td>\n";
	$lastdate = '';	/* last date should be printed only once */
	
	$text.=  "<td><input type=\"text\"  name=\"details[]\" value=\"$details\" size=\"20\" /></td>\n";
	$text.=  "<td><input type=\"text\"  name=\"refnum[]\" size=\"10\" value=\"$refnum\" /></td>\n";
	$text.=  "<td><input type=\"text\" dir=\"ltr\" class=\"debit\" name=\"debit[]\" onblur=\"DebitChange($i)\" size=\"6\" value=\"$debit\" /></td>\n";
	$text.=  "<td><input type=\"text\" dir=\"ltr\" class=\"credit\" name=\"credit[]\" onblur=\"CreditChange($i)\" size=\"6\" value=\"$credit\" /></td>\n";
	if($num == ($start + 1))
		$total = $line['total'];
	$text.=  "<td><input type=\"text\" dir=\"ltr\" class=\"total\" readonly=\"readonly\" name=\"total[]\" size=\"6\" value=\"$total\" /></td>\n";
	$text.=  "</tr>\n";
}
$text.=  "<tr><td colspan=\"8\" align=\"center\">\n";
if($start > 1) {
	if($start > ($linesperpage-1))
		$prev = $start - ($linesperpage-1);
	else
		$prev = 0;
	$l = _("Prev. page");
	$text.=  "<input type=\"button\" value=\"$l\" onclick=\"document.location='index.php?module=bankbook&amp;start=$prev&amp;account=$account'\" />&nbsp;\n";
}
$l = _("Update");
$text.=  "&nbsp;<input type=\"submit\" value=\"$l\" />&nbsp;&nbsp;\n";
if($LastLine >= ($start + ($linesperpage-1))) {
	$next = $start + ($linesperpage-1);
	$l = _("Next page");
	$text.=  "<input type=\"button\" value=\"$l\" onclick=\"document.location='index.php?module=bankbook&amp;start=$next&amp;account=$account'\" />\n";
}
$text.=  "</td></tr>";
$text.=  "</table>\n</form>";
createForm($text,$haeder,$sClass,750,600,'',1,getHelp());
?>