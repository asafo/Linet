<?PHP
/*
 | Open format files generation script for Drorit accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2009
 |
 | This program is a free software licensed under the GPL 
 */
/*
 | In 2007 Israel Tax authorities have issued a paper asking all accounting software who are
 | authorized for use in Israel to be able to export all transactions in a predefined format.
 | This script will create the files as needed by this format.
 */
global $prefix, $accountstbl, $companiestbl, $transactionstbl, $chequestbl, $creditcompanies, $docstbl, $itemstbl;
global $bkrecnum, $regnum, $mainid, $softregnum, $softwarename, $Version, $softwaremakerregnum, $softwaremaker;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	ErrorReport($l);
	return;
}

$step = isset($_GET['step']) ? $_GET['step'] : 0;

/* Global variables */
global $num_D120;
global $num_D110;
global $num_C100;
global $num_B110;
global $num_B100;

function utf8_to_windows1255($utf8) {
	return iconv("utf-8", "windows-1255", $utf8);
}//*///adam:

function TranslateDocumentType($doctype) {
	$DocTypeArr = array(DOC_PROFORMA => 300, DOC_ORDER => 100, DOC_DELIVERY => 200, DOC_INVOICE => 305, 
			DOC_CREDIT => 330, DOC_RETURN => 610, DOC_RECEIPT => 400, DOC_QUOTATION => 0);
	
	return $DocTypeArr[$doctype];
}

function ReceiptsDetails($bkmvdata, $bkrecnum, $regnum, $docnum) {
	global $prefix;
	global $chequestbl;
	global $creditcompanies;
	global $num_D120;
	//adam: need to cover DOC_INVRCPT
	$query = "SELECT issue_date FROM $docstbl WHERE prefix='$prefix' AND docnum='$docnum' AND doctype='".DOC_RECEIPT."'";
	$result = DoQuery($query, "ReceiptsDetails");
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$issue_date = $line['issue_date'];
	list($y, $m, $d) = explode('-', $issue_date);
	$issue_date = sprintf("%04d%02d%02d", $y, $m, $d);	

	$query = "SELECT * FROM $chequestbl WHERE prefix='$prefix' AND refnum='$docnum'";
	$result = DoQuery($query, "ReceiptsDetails");
	$n = 1;
	$doctype = TranslateDocumentType(DOC_RECEIPT);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$str = sprintf("D120%09d%09d%03d%20s%04d",
			$bkrecnum, $regnum, $doctype, $docnum, $n);
		fwrite($bkmvdata, $str);
		$bkrecnum++;
		$n++;
		
		/* From field 1306 in Tax authority document */
		$type = $line['type'];
		$bank = $line['bank'];
		$branch = $line['branch'];
		$cheque_acct = $line['cheque_acct'];
		$cheque_num = $line['cheque_num'];
		
		list($y, $m, $d) = explode('-', $line['cheque_date']);
		$chque_date = sprintf("%04d%02d%02d", $y, $m, $d);
		list($y, $m, $d) = explode('-', $line['dep_date']);
		$dep_date = sprintf("%04d%02d%02d", $y, $m, $d);	

		$sum = sprintf("%+015.0f", $line['sum'] * 100);
		$creditcompany = $line['creditcompany'];
		$creditstr = $creditcompanies[$creditcompany];
		$str = sprintf("%01d%010d%010d%015d%010d%08d%s0%20s1%7s%08d%07d%60s\r\n",
			$type, $bank, $branch, $cheque_acct, $cheque_num, $cheque_date, $sum, $creditstr,
			' ', $issue_date, $docnum, ' ');
		fwrite($bkmvdata, $str);
		$num_D120++;
	}
	return $bkrecnum;
}

function DocDetails($bkmvdata, $bkrecnum, $regnum, $doctype, $docnum, $num) {
	global $prefix;
	global $docdetailstbl, $itemstbl, $docstbl;
	global $UnitArr, $vat;
	global $num_D110;
	
	$query = "SELECT issue_date FROM $docstbl WHERE num='$num' AND prefix='$prefix'";
	$result = DoQuery($query, "DocDetails");
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$issue_date = $line['issue_date'];
	list($y, $m, $d) = explode('-', $issue_date);
	$issue_date = sprintf("%04d%02d%02d", $y, $m, $d);

	$query = "SELECT * FROM $docdetailstbl WHERE num='$num' AND prefix='$prefix'";
	$result = DoQuery($query, "DocDetails");
	$n = 1;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$str = sprintf("D110%09d%09d%03d%20s%04d000%20s",
			$bkrecnum, $regnum, $doctype, $docnum, $n, ' ');
		fwrite($bkmvdata, $str);

		$cat_num = $line['cat_num'];
		/* Get data from inventory table */
/*
		$query = "SELECT * FROM $itemstbl WHERE num='$cat_num' AND prefix='$prefix'";
		$r = DoQuery($query, "DocDetails");
		$l = mysql_fetch_array($r, MYSQL_ASSOC);
		$unit = $l['unit'];
		if($unit <= 1)	// Is unit inventory managed?
			$dt = 1;	// no, this is service oriented deal
		else
			$dt = 2;	// sale oriented deal
*/
		$dt = 1;
		/* Starting with field 1258 of Tax authorities document */
		$description = utf8_to_windows1255($line['description']);
		$description = substr($description, 0, 30);
//		$manufacturer = utf8_to_windows1255($l['manufacturer']);
		$manufacturer = ' ';
//		$unitname = $UnitArr[$unit];
		$unitname = ' ';
		$qty = sprintf("%+017.0f", $line['qty'] * 10000);
//		$nisprice = $line['nisprice'];
		$price = sprintf("%+015.0f", $line['price'] * 100);
//		$unit_price = $nisprice / $qty;	/* We need unit price in NIS not in forign currency */
		$unit_price = sprintf("%+015.0f", $line['unit_price'] * 100);
		$vatstr = sprintf("%04.0f", $vat * 100);
		$str = sprintf("%01d%20s%30s%50s%30s%20s%s%s+%014d%s%s%7s%08d%07d%28s\r\n",
			0, $cat_num, $description, $manufacturer, ' ', $unitname, $qty, $unit_price,
			0, $price, $vatstr, ' ', $issue_date, $num, ' ');
//		print "$str<br>\n";
		fwrite($bkmvdata, $str);
		$bkrecnum++;
		$n++;
		$num_D110++;
	}
	return $bkrecnum;	
}

function ExportDocuments($bkmvdata, $bkrecnum, $mainid, $regnum, $begindate, $enddate) {
	global $prefix;
	global $docstbl, $accountstbl;
	global $num_C100;

	$query = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND ";
	$query .= "issue_date>='$begindate' AND issue_date<='$enddate'";
	//print $query;
	$result = DoQuery($query, "ExportDocuments");
	$numdocs = mysql_num_rows($result);
	$n = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$n++;
		$num = $line['num'];
		$doctype = $line['doctype'];
		$doctype = TranslateDocumentType($doctype);
		$docnum = $line['docnum'];
		$issue_date = $line['issue_date'];
		list($y, $m, $d) = explode('-', $issue_date);
		$issue_date = sprintf("%04d%02d%02d", $y, $m, $d);
		$company = utf8_to_windows1255($line['company']);
		$company = substr($company, 0, 50);
		$addr = utf8_to_windows1255($line['address']);
		$addr = substr($addr, 0, 50);
		$city = utf8_to_windows1255($line['city']);
		$city = substr($city, 0, 30);
		$zip = $line['zip'];
		$zip = substr($zip, 0, 8);
		$due_date = $line['due_date'];
		list($y, $m, $d) = explode('-', $due_date);
		$due_date = sprintf("%04d%02d%02d", $y, $m, $d);
		$account = $line['account'];
		$query = "SELECT * FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
		$r = DoQuery($query, "ExportDocuments");
		$l = mysql_fetch_array($r, MYSQL_ASSOC);
		$phone = $l['phone'];
//		$phone = ' ';
//		$vatnum = $l['vatnum'];
//		$vatnum = substr($vatnum, 0, 8);
		$idnum = $l['idnum'];
		$str = sprintf("C100%09d%09d%03d%20s%08d0000%50s%50s%10s%30s%8s%30s!!%15s%09d%08d%15s!!!", 
			$bkrecnum, $regnum, $doctype, $docnum, $issue_date, $company, $addr, ' ', 
			$city, $zip, ' ', $phone, $vatnum, $due_date, ' ');
//		print "$str<br>\n";
		fwrite($bkmvdata, $str);
		$bkrecnum++;
		/* now print sums (starting at field 1219 in tax authority document) */
		$novat_total = sprintf("%+015.0f", $line['novat_total'] * 100);
		$sub_total1 = $line[sub_total] + $line[novat_total];
		$sub_total1 = sprintf("%+015.0f", $sub_total1 * 100);
		$vat = sprintf("%+015.0f", $line['vat'] * 100);
		$total = sprintf("%+015.0f", $line['total'] * 100);
		$str = sprintf("%s+%014d%s%s%s+%011d%15s%10s0%08d%7s%9s%07d%13s\r\n",
			$sub_total1, 0, $sub_total1, $vat, $total, 0, $account, ' ', $issue_date, ' ', ' ', $num, ' ');
		fwrite($bkmvdata, $str);
		$num_C100++;
		$bkrecnum = DocDetails($bkmvdata, $bkrecnum, $regnum, $doctype, $docnum, $num);
	}
	return $bkrecnum;
}

function ExportReceipts($bkmvdata, $bkrecnum, $regnum, $begindate, $enddate) {
	global $prefix;
	global $accountstbl;
	global $num_C100;

	$query = "SELECT * FROM $docsstbl WHERE prefix='$prefix' ";
	$query .= "AND issue_date>='$begindate' AND issue_date<='$enddate' ";
	$query .= "AND doctype='".DOC_RECEIPT."'";
	$result = DoQuery($query, "ExportReceipts");
	$n = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$n++;
		$num = $line['num'];
		$doctype = TranslateDocumentType(DOC_RECEIPT);
		$docnum = $line['docnum'];
		$issue_date = $line['issue_date'];
		list($y, $m, $d) = explode('-', $issue_date);
		$issue_date = sprintf("%04d%02d%02d", $y, $m, $d);
		$company = utf8_to_windows1255($line['company']);
		$addr = utf8_to_windows1255($line['address']);
		$city = utf8_to_windows1255($line['city']);
		$zip = utf8_to_windows1255($line['zip']);
		$account = $line['account'];
		$query = "SELECT * FROM $accountstbl WHERE num='$account' AND prefix='$prefix'";
		$r = DoQuery($query, "ExportDocuments");
		$l = mysql_fetch_array($r, MYSQL_ASSOC);
		$phone = $l['phone'];
		$vatnum = $l['vatnum'];
		$idnum = $l['idnum'];
		$str = sprintf("C100%09d%09d%03d%20s%08d0000%50s%50s%10s%30s%8s%30s!!%15s%09d%08d%15s!!!", 
			$bkrecnum, $regnum, $doctype, $docnum, $issue_date, $company, $addr, ' ', 
			$city, $zip, ' ', $phone, $vatnum, $issue_date, ' ');
		fwrite($bkmvdata, $str);
		$bkrecnum++;
		/* now print sums (starting at field 1219 in tax authority document) */
		$total = $line['sum'];
		$src_tax = $line['src_tax'];
		$str = sprintf("+%014d+%014d+%014d+%014d%+015.0f%+012.0f%15s%10s0%08d%7s%9s%07d%13s\n",
			0, 0, 0, 0, $total * 100, $src_tax * 100, $account, ' ', $issue_date, ' ', ' ', $num, ' ');
		fwrite($bkmvdata, $str);
		$num_C100++;
		$bkrecnum = ReceiptsDetails($bkmvdata, $bkrecnum, $regnum, $docnum);
	}
	return $bkrecnum;
}

function AcctPrevSum($account, $begindate) {
	global $prefix;
	global $transactionstbl;
	
	$sum = 0;
	$query = "SELECT sum FROM $transactionstbl WHERE prefix='$prefix' AND account='$account' AND ";
	$query .= "date<'$begindate'";
	$result = DoQuery($query, "AcctPrevSum");
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$sum += $line[0];
	}
	return $sum;
}

function AcctSum($account, $begindate, $enddate) {
	global $prefix;
	global $transactionstbl;
	
	$ar = array(0.0, 0.0);
	$query = "SELECT sum FROM $transactionstbl WHERE prefix='$prefix' AND account='$account' AND ";
	$query .= "date>='$begindate' AND date<='$enddate'";
	$result = DoQuery($query, "AcctSum");
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$sum = $line[0];
		if($sum < 0.0) {
			$sum *= -1.0;
			$ar[0] += $sum;
		}
		else
			$ar[1] += $sum;
	}
	return $ar;
}

function ExportAcct($bkmvdata, $bkrecnum, $regnum, $begindate, $enddate) {
	global $prefix;
	global $AcctType;
	global $accountstbl;
	global $num_B110;
	
	$query = "SELECT * FROM $accountstbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "ExportAcct");
	$n = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$n++;
		$account = $line['num'];
		$acctname = $line['company'];
//		print "<pre>$bkrecnum Account: ($account) $acctname</pre>\n";
		$acctname = utf8_to_windows1255($line['company']);
		$acctname = trim($acctname);
		$type = $line['type'];
		$typestr = utf8_to_windows1255($AcctType[$type]);
		$str = sprintf("B110%09d%09d%15s%50s%15d%30s",
			$bkrecnum, $regnum, $account, $acctname, $type, $typestr);
		fwrite($bkmvdata, $str);
		$bkrecnum++;
		/* Starting at field 1407 */
		$addr = utf8_to_windows1255($line['address']);
		$city = utf8_to_windows1255($line['city']);
		$zip = $line['zip'];
		$str = sprintf("%50s%10s%30s%8s%30s%2s%15s",
			 $addr, ' ', $city, $zip, ' ', ' ', ' ');
		fwrite($bkmvdata, $str);
		/* Starting at field 1414 */
		$psum = AcctPrevSum($account, $begindate);
		$sum = AcctSum($account, $begindate, $enddate);
		$vatnum = $line['vatnum'];
		$psum = sprintf("%+015.0f", $psum * 100);
		$sum0 = sprintf("%+015.0f", $sum[0] * 100);
		$sum1 = sprintf("%+015.0f", $sum[1] * 100);
		$str = sprintf("%s%s%s%04d%09d%41s\r\n",
			$psum, $sum0, $sum1, 0, $vatnum, ' ');
		$num_B110++;
		fwrite($bkmvdata, $str);
	}
	return $bkrecnum;
}

function GetRefType($type) {
	switch($type) {
		case MANUAL:
			return 0;
		case MAN_INVOICE:
		case INVOICE:
			return TranslateDocumentType(DOC_INVOICE);
		case SUPINV:
			return 700;	
		case RECEIPT:
			return TranslateDocumentType(DOC_RECEIPT);
		case CHEQUEDEPOSIT:
			return 0;
		case SUPPLIERPAYMENT:
			return 0;
		default:
			return 0;
	}
}

function ExportTransactions($bkmvdata, $bkrecnum, $regnum, $begindate, $enddate) {
	global $prefix;
	global $transactionstbl;
	global $TranType;
	global $num_B100;
	
	$query = "SELECT * FROM $transactionstbl WHERE prefix='$prefix' ";
	$query .= "AND date>='$begindate' AND date<='$enddate'";
	$result = DoQuery($query, "ExportTransactions");
	$lastnum = 0;
	$TotalTransactions = 0;
	$n = mysql_num_rows($result);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$TotalTransactions++;
		$num = $line['num'];
		if($num != $lastnum) {
			$n = 1;
			$lastnum = $num;
		}
		$type = $line['type'];
		$refnum1 = $line['refnum1'];
		$ref1type = GetRefType($type);
		$refnum2 = $line['refnum2'];
		$str = sprintf("B100%09d%09d%010d%05d%08d%15s%20s%03d%20s%03d",
			$bkrecnum, $regnum, $num, $n, 0, $type, $refnum1, $ref1type, $refnum2, 0);
		fwrite($bkmvdata, $str);
		$bkrecnum++;
		/* Starting at field 1361 */
		$t = utf8_to_windows1255($TranType[$type]);
		list($y, $m, $d) = explode('-', $line['date']);
		$date = sprintf("%04d%02d%02d", $y, $m, $d);
		$account = $line['account'];
		$sum = $line['sum'];
		if($sum < 0.0) {
			$sign = 1;
			$sum *= -1.0;
		}
		else
			$sign = 2;
		$cor_num = $line['cor_num'];
		$sumstr = sprintf("+%015.2f", $sum);
		$sumstr = str_replace('.', '', $sumstr);
		$str = sprintf("%50s%08d%08d%15s%15s%01d%3s%15s+%014d+%011d%10s%10s%07s%08d%34s\r\n",
			$t, $date, $date, $account, ' ', $sign, ' ', $sumstr, 0, 0, $cor_num, ' ', ' ', $date, ' ');
		fwrite($bkmvdata, $str);
		$num_B100++;
	}
	return $bkrecnum;
}
$text='';
if($step == 0) {	/* First stage, choose dates for report */
	/* Get begin and end dates */
	$d = date("m-Y");
	list($m, $y) = explode('-', $d);
	if($m < 4)
		$y--;
	$begindate = "1-1-$y";
	$enddate = date("31-12-$y");
	//print "<div class=\"form righthalf1\">\n";
	$header = _("Export open format files for tax authorities"); 
	//print "<h3>$l</h3>\n";
	$text.= "<form name=\"dtrange\" action=\"?module=openfrmt&amp;step=1\" method=\"post\">\n";
	$text.= "<table dir=\"rtl\" border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("From date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input class=\"date\" type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$begindate\" size=\"7\">\n";
//$text.='<script type="text/javascript">addDatePicker("#begindate","'.$begindate.'");</script>';
	$text.= "</td>\n";
	$text.= "</tr><tr><td colspan=\"2\">&nbsp;</td></tr><tr>\n";
	$l = _("To date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input class=\"date\" type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$enddate\" size=\"7\">\n";
//$text.='<script type="text/javascript">addDatePicker("#enddate","'.$enddate.'");</script>';

	$text.= "</td>\n";
	$text.= "</tr><tr><td colspan=\"2\">&nbsp;</td></tr><tr>\n";
	$l = _("Submit");
	$text.= "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
	$text.= "</tr>\n";
	$text.= "</table>\n</form>\n";
	createForm($text,$header,'',750,'','',1,getHelp());
	//print "</div>\n";
	
}
else if($step == 1) {
	$b = $_POST['begindate'];
	$e = $_POST['enddate'];

	//$begindate = FormatDate($b, "d-m-Y", "mysql");
	//$enddate = FormatDate($e, "d-m-Y", "mysql");
	
	$begindate = strftime('%Y-%m-%d',strtotime($b));
	$enddate = strftime('%Y-%m-%d',strtotime($e));
	/* The actual work begins... */
	/* store current working directory */
	$cwd = getcwd();
	/* change to reports directory */
	if(!chdir('openfrmt')) {
		ErrorReport("Error: openfrmt directory does not exist");
		exit;
	}
	/* Get the files path */
	/*
	 | Files are stored under directory 'openfrmt'
	 */
	$query = "SELECT regnum,vat,companyname,bidi FROM $companiestbl WHERE prefix='$prefix'";
	$f = __FILE__;
	$l = __LINE__;
	$result = DoQuery($query, "$f $l");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$regnum = $line[0];
	$vat = $line[1];
	$companyname = $line[2];
	$bidi=$line[3];//adam:
	/*$a = explode('-', $begindate); //adam this is a mistake openformat 1.31
	$y = $a[0] % 100;	// two digit representation of year 
	if($y < 10)
		$y = "0$y";*/
	$y = date('y');
	$regnum1 = substr($regnum, 0, 8);
	$basepath = "$regnum1.$y";
	if(!@chdir($basepath)) {
		if(!mkdir("$basepath")) {
			ErrorReport( _("Unable to create directory").": $basepath<br />\n");
			chdir($cwd);
			exit;
		}
		chdir($basepath);
	}

	// actual files will be in directory name composed of date, month, hour, date 
	$dir = date("mdHi");
	if(!@chdir($dir)) {
		if(!mkdir($dir)) {
			ErrorReport(_("Unable to create directory").": $dir<br />\n");
			chdir($cwd);
			exit;
		}
		chdir($dir);
	} 
	$filesdir = "openfrmt/$basepath/$dir";
	//$filesdir = "openfrmt/$basepath"; //adam
	// print "<div class=text3 dir=rtl>\n";
	
	/* We are now in directory for files, create the files */
	/* start with bkmvdata */
	/* Opening record of bkmvdata, section 4.1 in Tax authorities document */
	$bkmvdata = fopen("bkmvdata.txt", "w");
	$bkrecnum = 1;	/* start with 1 */
	$mainid = crc32($regnum);
	$str = sprintf("A100%09d%09d%015u&OF1.31&%50s\r\n", $bkrecnum, $regnum, $mainid, ' ');
	fwrite($bkmvdata, $str);
	$bkrecnum++;

	/* Now go over documents and generate document head and document details records */
	$bkrecnum = ExportDocuments($bkmvdata, $bkrecnum, $mainid, $regnum, $begindate, $enddate);
//	print "Documents: $bkrecnum<br>\n";
	//adam:! need to be crafted
	//$bkrecnum = ExportReceipts($bkmvdata, $bkrecnum, $regnum, $begindate, $enddate);
//	print "Receipts: $bkrecnum<br>\n";
	$bkrecnum = ExportAcct($bkmvdata, $bkrecnum, $regnum, $begindate, $enddate);
//	print "Accounts: $bkrecnum<br>\n";
	$bkrecnum = ExportTransactions($bkmvdata, $bkrecnum, $regnum, $begindate, $enddate);
//	print "Transactions: $bkrecnum<br>\n";
	
	$str = sprintf("Z900%09d%09d%015u&OF1.31&%015d%50s\r\n", 
			$bkrecnum, $regnum, $mainid, $bkrecnum, ' ');
	fwrite($bkmvdata, $str);
	fclose($bkmvdata);
	
	$inifd = fopen("ini.txt", "w");
	$str = sprintf("A000%5s%015d%09d%015u&OF1.31&%8s%20s%20s%09d%20s2",
			' ', $bkrecnum, $regnum, $mainid, $softregnum, $softwarename, $Version,
			$softwaremakerregnum, $softwaremaker);
	fwrite($inifd, $str);
	/* Starting at field 1012 */
	list($y, $m, $d) = explode('-', $begindate);
	$bd = sprintf("%04d%02d%02d", $y, $m, $d);
	list($y, $m, $d) = explode('-', $enddate);
	$ed = sprintf("%04d%02d%02d", $y, $m, $d);
	$timenow = date("YmdHi");
	$companyname1 = utf8_to_windows1255($companyname);
	$regnum1 = substr($regnum, 0, 8);
	/* This is fake path since tax autorities are to much linked to windows OS */
	$filesdir1 = "F:\\OPENFRMT\\$basepath\\" . date("mdHi");
	$str = sprintf("%50s21%09d%09d%10s%50s%50s%10s%30s%8s0000%8d%8d%12s01",
			$filesdir1, $regnum, 0, ' ', $companyname1, ' ', ' ', ' ', ' ', $bd, $ed, $timenow);
	fwrite($inifd, $str);
	/* Starting at field 1030 */
	$str = sprintf("%20s%3s0%46s\r\n",
			"zip", "ILS", ' ');
	fwrite($inifd, $str);
	$str = sprintf("B100%015d\r\n", $num_B100);
	fwrite($inifd, $str);
	$str = sprintf("B110%015d\r\n", $num_B110);
	fwrite($inifd, $str);
	$str = sprintf("C100%015d\r\n", $num_C100);
	fwrite($inifd, $str);
	$str = sprintf("D110%015d\r\n", $num_D110);
	fwrite($inifd, $str);
	$str = sprintf("D120%015d\r\n", $num_D120);
	fwrite($inifd, $str);
	fclose($inifd);
	chdir($cwd);

	//print "<div class=\"form righthalf1\">\n";
	$l = _("Link to file");
	$text.= "<br>$l: ";
	$text.=  "<a href=download.php?file=openfrmt/$basepath/$dir/bkmvdata.txt&name=bkmvdata.txt>bkmvdata.txt</a><br />\n";
	$text.=  "$l: ";
	$text.=  "<a href=download.php?file=openfrmt/$basepath/$dir/ini.txt&name=ini.txt>ini.txt</a><br />\n";

/* //adam can be delted now need to delete Right click and choose 'save as...'
	$l = _("Right click and choose 'save as...'");
	print "$l<br>\n";
*/
	$fd = fopen("tmp/$prefix.html", "wt");
	$l = _("Create open format files");
	fwrite($fd, "<br><h1>$l</h1>\n");
	$l = _("Company");
	fwrite($fd, "$l: $companyname<br>\n");
	$l = _("Reg. num");
	fwrite($fd, "$l: $regnum<br>\n");
	$l = _("From date");
	fwrite($fd, "$l: $b\n");
	$l = _("To date");
	fwrite($fd, "$l: $e<br>\n");
	$l = _("Open format files created successfully");
	fwrite($fd, "<br>$l<br>\n");

	$l = _("Records types details");
	fwrite($fd, "<h2>$l</h2>\n");
	fwrite($fd, "<table border=\"1\"><tr class=\"tblhead\">\n");
	$l1 = _("Total records");
	$l2 = _("Record description");
	$l3 = _("Record code");
	fwrite($fd, "<td>$l3</td><td>$l2</td><td>$l1</td>\n");
	$l = _("Opening record");
	fwrite($fd, "</tr><tr>\n");
	fwrite($fd, "<td>A100</td><td>$l</td><td>1</td>\n");
	fwrite($fd, "</tr><tr>\n");
	$l = _("Transactions in accounting");
	fwrite($fd, "<td>B100</td><td>$l</td><td>$num_B100</td>\n");
	fwrite($fd, "</tr><tr>\n");
	$l = _("Account in accounting");
	fwrite($fd, "<td>B110</td><td>$l</td><td>$num_B110</td>\n");
	fwrite($fd, "</tr><tr>\n");
	$l = _("Document header");
	fwrite($fd, "<td>C100</td><td>$l</td><td>$num_C100</td>\n");
	fwrite($fd, "</tr><tr>\n");
	$l = _("Document details");
	fwrite($fd, "<td>D110</td><td>$l</td><td>$num_D110</td>\n");
	fwrite($fd, "</tr><tr>\n");
	$l = _("Receipts details");
	fwrite($fd, "<td>D120</td><td>$l</td><td>$num_D120</td>\n");
	fwrite($fd, "</tr><tr>\n");
	$l = _("End record");
	fwrite($fd, "<td>Z900</td><td>$l</td><td>1</td>\n");
	fwrite($fd, "</tr>\n</table>\n");
	fclose($fd);	

	$text.=file_get_contents("tmp/$prefix.html");
	
	$text.=  "<br>&nbsp;&nbsp;&nbsp;\n";
	$l = _("Print");
	$text.=  "<input type=\"button\" value=\"$l\" ";
	$text.=  "onclick=\"window.open('openfrmtprnt.php?prefix=$prefix')\">\n";
	createForm($text,$header,'',750,'','',1,getHelp());
	//print "</div>\n";
	
}

?>
