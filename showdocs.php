<?PHP
/*
 | ShowDocs
 | Show documents list and allow viewing and printing.
 | Written for Drorit accounting system
 | by Ori Idan August 2009
 | Modifed By adam BH 10/2010
 */
global $prefix, $accountstbl, $supdocstbl, $itemstbl;
//adam: global $receiptstbl; 
global $chequestbl;
global $docstbl, $docdetailstbl;
global $creditcompanies;
global $CompArray;
global $CurrArray;
global $DocType;
global $paymentarr;
global $creditarr;
global $banksarr;

$step = isset($_GET['step']) ? $_GET['step'] : 0;

/* Get begin and end dates */
$d = date("m-Y");
list($m, $y) = explode('-', $d);
if($m < 4)
	$y--;
$begindate = "1-1-$y";
$enddate = date("d-m-Y");

function PrintDocTypeSelect($def) {
	global $DocType;
	$text= "<select name=\"doctype\">\n";
	$l = _("Select document type");
	$text.= "<option value=\"0\">-- $l --</option>\n";
	foreach($DocType as $key => $val) {
		$s = ($key == $def) ? " selected" : "";
		$text.= "<option value=\"$key\"$s>$val</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}
/*
function PrintCustomerSelect($def) {
	global $accountstbl;
	global $prefix;

	$t = CUSTOMER;
	$query = "SELECT num,company FROM $accountstbl WHERE type='$t' AND prefix='$prefix' ORDER BY company ASC";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	 	
	print "<select name=\"account\">\n";
	$l = _("Select all");
	print "<option value=\"0\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$company = $line['company'];
		$s = ($num == $def) ? " selected" : "";
		print "<option value=\"$num\"$s>$company</option>\n";
	}
	print "</select>\n";
}*/

if($step > 0) {
	$doctype = $_POST['doctype'];
	if($doctype == 0) {
		$l = _("Document type must be chosen");
		print "<h1>$l</h1><br>\n";
		$step = 0;
	}
	else {
		$customer = (int)$_POST['account'];
		if(!empty($_POST['begindate'])) {
			$begindate = $_POST['begindate'];
			$enddate = $_POST['enddate'];
		}
		$begindate = FormatDate($begindate, "dmy", "mysql");
		$enddate = FormatDate($enddate, "dmy", "mysql");
		//if($doctype != DOC_RECEIPT) {
			//if($doctype > DOC_RECEIPT)
			//	$dt = DOC_INVOICE;
			//else
				$dt = $doctype;
			if(empty($customer))
				$query = "SELECT * FROM $docstbl WHERE ";
			else
				$query = "SELECT * FROM $docstbl WHERE account='$customer' AND ";
			$query .= "doctype='$dt' AND issue_date>='$begindate' AND issue_date<='$enddate'";
			$query .= " AND prefix='$prefix' ORDER BY docnum DESC";
		//}			
		/*else {
			if(empty($customer))
				$query = "SELECT * FROM $docstbl WHERE ";
			else
				$query = "SELECT * FROM $docstbl WHERE account='$customer' AND ";
			$query .= "issue_date>='$begindate' AND issue_date<='$enddate'";
			$query .= " AND prefix='$prefix' ORDER BY docnum DESC";
		}*/
//		print "Query: $query<br>\n";
		$result = DoQuery($query, "showdocs.php");
		print "<br />\n";
		$doctypestr = $DocType[$doctype];
		print "<h2>$doctypestr</h2>\n";
		print "<table width=\"100%\" border=\"0\"><tr><td>\n";
		print "<table border=\"0\" class=\"hovertbl\"><tr class=\"tblhead\">\n";
		$l = _("Doc. type");
		print "<td style=\"width:6em\">$l</td>\n";
		$l = _("Num");
		print "<td style=\"width:3em\">$l</td>\n";
		$l = _("Date");
		print "<td style=\"width:7em\">$l</td>\n";
		$l = _("Customer");
		print "<td style=\"width:10em\">$l</td>\n";
		if($doctype != DOC_RECEIPT) {
			$l = _("No VAT sum");
			print "<td style=\"width:8em\">$l</td>\n";
		}
		$l = _("Total sum");
		print "<td style=\"width:5em\">$l</td>\n";
		$l = _("Actions");
		print "<td>$l</td>\n";
		print "</tr>\n";
		$novatsum = 0.0;
		$totalsum = 0.0;
		$e = 0;
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
//			print "DocType: $doctype<br>\n";
			//if($doctype != DOC_RECEIPT)
				$docnum = $line['docnum'];
			//else
			//	$docnum = $line['refnum'];
//			print "DocNum: $docnum<br>\n";
			/*if(($doctype == DOC_RECEIPT) || ($doctype==DOC_INVRCPT)) {	// invoice and receipt together 
				//adam: need figure
				//$q = "SELECT * FROM $receiptstbl WHERE prefix='$prefix' AND refnum='$docnum' AND invoices='$docnum'";
				$q = "SELECT * FROM $docstbl WHERE prefix='$prefix' AND docnum='$docnum' AND doctype='$doctype'";
				//print "Query: $q<br />\n";
				$r = DoQuery($q, "showdocs.php");
				$n = mysql_num_rows($r);
				if(!$n)
					continue;
			}//*/
			
			$issue_date = $line['issue_date'];
			$accountstr = $line['company'];
			$account = $line['account'];
			//if($doctype != DOC_RECEIPT) {
				$sub_total = $line['sub_total'];
				$novat_total = $line['novat_total'];
				$sub_total += $novat_total;
				$total = $line['total'];
			//}
			//else
			//	$total = $line['sum'];	
			NewRow();

			$doctypestr = $DocType[$doctype];
			/*if($doctype == DOC_INVRCPT)
				$doctypestr = _("Invoice receipt");*/
			print "<td>$doctypestr</td>\n";
			print "<td>$docnum</td>\n";
			$issue_date = FormatDate($issue_date, "mysql", "dmy");
			print "<td>$issue_date</td>\n";
			print "<td>$accountstr</td>\n";
			if($doctype != DOC_RECEIPT) {
				print "<td>$sub_total</td>\n";
				$novatsum += $sub_total;
			}
			$tstr = number_format($total);
			print "<td>$tstr</td>\n";
			$totalsum += $total;
			print "<td>";
			if($step == 1) {
				$url = "printdoc.php?doctype=$doctype&amp;docnum=$docnum&amp;prefix=$prefix";
				$target = "docswin";
				$l = _("Display");
			}
			else {
				$targetdoc = $_POST['targetdoc'];
				$url = "?module=docsadmin&amp;step=4&amp;targetdoc=$targetdoc&amp;doctype=$doctype&amp;docnum=$docnum";
				$target = "window";
				$l = _("Copy");
			}
			if($target == 'docswin')
				print "<input type=\"button\" onclick=\"window.open('$url', 'docswin')\"";
			else
				print "<input type=\"button\" onclick=\"window.location.href='$url'\"";
			print "value=\"$l\">";
			print "</a>&nbsp;&nbsp;";
			$url = "?module=emaildoc&amp;account=$account&amp;doctype=$doctype&amp;docnum=$docnum";
//			print "<input type=\"button\" onclick=\"window.location.href='$url'\"";
//			print "value=\"׳©׳�׳— ׳‘׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™\">\n";
			print "</td></tr>\n";
		}
		print "<tr class=\"sumline\">\n";
		//if($doctype != DOC_RECEIPT)
			print "<td colspan=\"4\" align=\"left\">\n";
		//else
		//	print "<td colspan=\"4\" align=\"left\">\n";
		$l = _("Total");
		print "<b>$l: &nbsp;</b></td>\n";
		if($doctype != DOC_RECEIPT)
			print "<td>$novatsum</td>\n";
		print "<td>$totalsum</td>\n";
		print "<td>&nbsp;</td>\n";
		print "</tr>\n";
		print "</table>\n";
	/*	print "</td><td width=\"48%\" valign=\"top\">\n";
		ShowText('showdocs1'); */
		print "</td></tr></table>\n";
	}
}
if($step == 0) {
	//print "<br>\n";
	//print "<div class=\"form righthalf1\">\n";
	$haeder = _("Search business document");
	//print "<h3>$l</h3>\n";
	$text= "<form name=\"form1\" action=\"?module=showdocs&amp;step=1\" method=\"post\">\n";
	$text.= "<table border=\"0\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("Doc. type");
	$text.= "<td>$l: </td>\n";
	$text.= "<td>\n";
	$text.=PrintDocTypeSelect(0);
	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Select customer");
	$text.= "<td>$l: </td>\n";
	$text.= "<td>\n";
	$text.=PrintCustomerSelect(0);
	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("From date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$begindate\" size=\"8\">\n";
$text.='<script type="text/javascript">addDatePicker("#begindate","'.$begindate.'");</script>';

	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("To date");
	$text.= "<td>$l: </td>\n";
	$text.= "<td><input type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$enddate\" size=\"8\">\n";
$text.='<script type="text/javascript">addDatePicker("#enddate","'.$enddate.'");</script>';
	$text.= "</td>\n";
	$text.= "</tr><tr>\n";
	$l = _("Search");
	$text.= "<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\">\n";
	$text.= "</td></tr>\n";
	$text.= "</table>\n";
	$text.= "</form>\n";
	//print "</div>\n";
	createForm($text,$haeder,'',600);
	print "<div class=\"lefthalf1\">\n";
	ShowText('showdocs');
	print "</div>\n";
}
?>
