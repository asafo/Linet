<?PHP
/*
 | Contact list manager for Drorit ver. 2
 | Written by: Ori Idan December 2009
 | Modifed by adam bh
 */
global $accountstbl, $transactionstbl, $docstbl, $receiptstbl, $histtbl;
global $AcctType;
global $DocType;
global $dir;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

$company = isset($_POST['company']) ? GetPost('company') : '';
$contact = isset($_POST['contact']) ? GetPost('contact') : '';
$address = isset($_POST['address']) ? GetPost('address') : '';

$begindmy = isset($_COOKIE['begin']) ? $_COOKIE['begin'] : date("1-1-Y");
$enddmy = isset($_COOKIE['end']) ? $_COOKIE['end'] : date("d-m-Y");
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : $begindmy;
$enddmy = isset($_GET['end']) ? $_GET['end'] : $enddmy;
$beginmysql = FormatDate($begindmy, "dmy", "mysql");
$endmysql = FormatDate($enddmy, "dmy", "mysql");
$text='';
$num = isset($_GET['num']) ? (int)$_GET['num'] : 0;
$dt = date("d-m-Y");
$date1 = _("Date");
$submit = _("Submit");

function GetAcctTotal($account, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];	
	}
	return $total;
}

if($action == 'addhist') {
	$dt1 = FormatDate($_POST['dt'], "dmy", "mysql");
	$details = GetPost('details');
	$query = "INSERT INTO $histtbl VALUES('$prefix', '$num', '$dt1', '$details')";
	DoQuery($query, __LINE__);
	$action = 'edit';
}
if($action == 'delhist') {
	$dt1 = FormatDate($_GET['dt'], "dmy", "mysql");
	$query = "DELETE FROM $histtbl WHERE prefix='$prefix' AND num='$num' AND dt='$dt1'";
	DoQuery($query, __LINE__);
	$action = 'edit';
}
	
/*if($action == 'new') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}
	// first calculate next account number for this company 
	$query = "SELECT MAX(num) FROM $accountstbl WHERE prefix='$prefix'";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$max = $line[0] + 1;
	f($max < 200)	// start from 200 so we have place for more predefined accounts 
		$max = 201;

//	print_r($_POST);
	$type = $_POST['type'];
	
	$pay_terms = $_POST['pay_terms'];
	$p = strpos($pay_terms, '+');
	if($p === false) {
		$pay_terms = (integer)$pay_terms;
	}
	else {
		$pay_terms = (integer)$pay_terms * -1;
	}
	$id6111 = isset($_POST['id6111']) ? $_POST['id6111'] : '';
	$src_tax = isset($_POST['src_tax']) ? $_POST['src_tax'] : '';
	$src_date = isset($_POST['src_date']) ? FormatDate($_POST['src_date'], "dmy", "mysql") : '';
	$company = htmlspecialchars($_POST['company'], ENT_QUOTES);
	if($company == '') {
		$l = _("Account name must be specified");
		ErrorReport("$l");
		exit;
	}
	$contact = htmlspecialchars($_POST['contact'], ENT_QUOTES);
	$department = htmlspecialchars($_POST['department'], ENT_QUOTES);
	$vatnum = htmlspecialchars($_POST['vatnum'], ENT_QUOTES);
	$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
	$phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);
	$dir_phone = htmlspecialchars($_POST['dir_phone'], ENT_QUOTES);
	$cellular = htmlspecialchars($_POST['cellular'], ENT_QUOTES);
	$fax = htmlspecialchars($_POST['fax'], ENT_QUOTES);
	$web = htmlspecialchars($_POST['web'], ENT_QUOTES);
	$address = htmlspecialchars($_POST['address'], ENT_QUOTES);
	$city = htmlspecialchars($_POST['city'], ENT_QUOTES);
	$zip = htmlspecialchars($_POST['zip'], ENT_QUOTES);
	$comments = htmlspecialchars($_POST['comments'], ENT_QUOTES);

	$query = "INSERT INTO $accountstbl (num, prefix, type, id6111, pay_terms, src_tax, src_date, ";
	$query .= "company, contact, department, vatnum, email, phone, ";
	$query .= "dir_phone, cellular, fax, web, address, city, zip, comments) \n";
	$query .= "VALUES ('$max', '$prefix', '$type', '$id6111', '$pay_terms', '$src_tax', '$src_date', '$company', '$contact', '$department', ";
	$query .= "'$vatnum', '$email', '$phone', ";
	$query .= "'$dir_phone', '$cellular', '$fax', '$web', '$address', '$city', '$zip', '$comments')";
//	print "<br>type: $type<br>\n";
//	print "Query: $query<br>\n";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	$_GET['num'] = $max;
	$action = 'edit';
}//*/
if($action == 'update') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$num = $_GET['num'];
	$type = $_POST['type'];
	$pay_terms = $_POST['pay_terms'];
	// print "pay_terms: $pay_terms<BR>\n";
	$p = strpos($pay_terms, '+');
	if($p === false) {
		// print "pay_terms1: $pay_terms<BR>\n";
		$pay_terms = (integer)$pay_terms;
	}
	else {
	//	print "pay_terms: $pay_terms<BR>\n";
		$pay_terms = (integer)$pay_terms * -1;
	//	exit;
	}

	$id6111 = isset($_POST['id6111']) ? $_POST['id6111'] : '';
	$src_tax = isset($_POST['src_tax']) ? $_POST['src_tax'] : '';
	if($src_tax == '--')
		$src_tax = $_POST['src_tax1'];
	$src_date = isset($_POST['src_date']) ? FormatDate($_POST['src_date'], "dmy", "mysql") : '';

	$company = GetPost('company');
	$contact = GetPost('contact');
	$department = GetPost('department');
	$vatnum = GetPost('vatnum');
	$email = GetPost('email');
	$phone = GetPost('phone');
	$dir_phone = GetPost('dir_phone');
	$cellular = GetPost('cellular');
	$fax = GetPost('fax');
	$web = GetPost('web');
	$address = GetPost('address');
	$city = GetPost('city');
	$zip = GetPost('zip');
	$comments = GetPost('comments');
	
	$query = "UPDATE $accountstbl SET\n";
	$query .= "type='$type',\n";
	$query .= "id6111='$id6111',\n";
	$query .= "pay_terms='$pay_terms',\n";
	$query .= "src_tax = '$src_tax', \n";
	$query .= "src_date = '$src_date', \n";
	$query .= "company='$company',\n";
	$query .= "contact='$contact',\n";
	$query .= "department='$department',\n";
	$query .= "vatnum='$vatnum',\n";
	$query .= "email='$email',\n";
	$query .= "phone='$phone',\n";
	$query .= "dir_phone='$dir_phone',\n";
	$query .= "fax='$fax',\n";
	$query .= "web='$web',\n";
	$query .= "address='$address',\n";
	$query .= "city='$city',\n";
	$query .= "zip='$zip',\n";
	$query .= "comments='$comments' \n";
	$query .= "WHERE num='$num' AND prefix='$prefix'\n";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	$action = 'edit';
}
if($action == 'edit') {
	$num = (int)$_GET['num'];
	//print "<br>\n";
	//print "<div class=\"righthalf1\">\n";
	$text=EditAcct($num, 0);	// type is ignored for editing 
	createForm($text,$haeder,'',450);
	//print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	$l = _("Activity history");
	print "<h3>$l</h3>\n";
	$l = _("Business documents");
	print "<h2>$l</h2>\n";	
	// Search business documents 
	$query = "SELECT * FROM $docstbl WHERE account='$num' AND prefix='$prefix' ";
	$query .= "ORDER BY issue_date DESC";
	print $query;
	$result = DoQuery($query, __LINE__);
	print "<table class=\"hovertbl\">\n";
	if(mysql_num_rows($result)) {
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$doctype = $line['doctype'];
			$doctypestr = $DocType[$doctype];
			$docnum = $line['docnum'];
			$sum = (double)$line['sub_total'] + (double)$line['novat_total'];
			$sum = number_format($sum);
			$total = number_format($line['total']);
			$url = "printdoc.php?win=1&doctype=$doctype&docnum=$docnum&prefix=$prefix";
			NewRow();
			print "<td>\n";
			print "<a href=\"$url\">$doctypestr $docnum</a>\n";
			$l = _("Sum");
			print "$l: $sum ";
			$l = _("Including VAT");
			print "$l: $total</td>\n";
			print "</tr>\n";
		}
	}
	//adam: no need
	$query = "SELECT * FROM $docstbl WHERE account='$num' AND prefix='$prefix' ";
	$query .= "ORDER BY issue_date DESC";
	$result = DoQuery($query, __LINE__);
	if(mysql_num_rows($result)) {
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$type = DOC_RECEIPT;
			$doctypestr = _("Receipt");
			$docnum = $line['docnum'];
			$sum = (double)$line['total'];
			$sum = number_format($sum);
			$total = number_format($line['total']);
			$url = "printdoc.php?win=1&doctype=$type&docnum=$docnum&prefix=$prefix";
			NewRow();
			print "<td>\n";
			print "<a href=\"$url\">$doctypestr $docnum</a>\n";
			$l = _("Sum");
			print "$l: $sum </td>";
			print "</tr>\n";
		}
	}//*/
	print "</table>\n";
	$l = _("Contact history");
	print "<br><h2>$l</h2>\n";
$addhistfrm = <<<EHF
<form name="addhist" action="?module=contact&amp;num=$num&amp;action=addhist" method="post">
$date1: 
<input type="text" name="dt" value="$dt" size="8" />
<script type="text/javascript">
new tcal ({
	// form name
	'formname': 'addhist',
	// input name
	'controlname': 'dt'
});
</script>
<br />
<textarea name="details" cols="40" rows="4"></textarea>
<br />
<input type="submit" value="$submit">
</form>
EHF;
	print "$addhistfrm<br>\n";
	
	$query = "SELECT * FROM $histtbl WHERE num='$num' AND prefix='$prefix' ";
	$query .= "ORDER BY dt DESC";
	$result = DoQuery($query, __LINE__);
	print "<table class=\"tablesorter\" >\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		print "<tr>\n";
		$dt = FormatDate($line['dt'], "mysql", "dmy");
		print "<td>$date1: $dt<br>\n";
		$details = nl2br($line['details']);
		print "$details";
		$url = "?module=contact&action=delhist&num=$num&dt=$dt";
		$del = _("Delete");
		print "<a href=\"$url\">$del</a>\n";
		print "</td></tr>\n";
	}
	print "</table>\n";		
	print "</div>\n";
	return;
}
/*if($action == 'add') {
	$type = (int)$_GET['type'];
	print "<br />\n";
	//print "<div class=\"righthalf1\">\n";
	$text=EditAcct(0, $type);
	createForm($text,$haeder,'',450);
	//print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	ShowText('addcontact');
	print "</div>\n";
	return;
}*/
$type = (int)$_GET['type'];	
$company1 = _("Company");
$contact1 = _("Contact");
$address1 = _("Address");
$city1 = _("City");
$zip1 = _("Zip");
$search = _("Search");
$srchform = <<<EOF1
<form action="?module=contact&amp;action=search&amp;type=$type" method="post">
<table class="formtbl" width="100%">
	<tr>
		<td>$company1: </td>
		<td><input type="text" name="company" value="$company" /></td>

		<td>$contact1: </td>
		<td><input type="text" name="contact" value="$contact" /></td>
	</tr>
	<tr>
		<td>$address1: </td>
		<td><input type="text" name="address" value="$address" /></td>
	
		<td>$city1: </td>
		<td><input type="text" name="city" value="$city" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" value="$search" /></td>
	</tr>
</table>
</form>
EOF1;

$n = _("Num");

//$l1 = _("Add customer");
//$l2 = _("Add supplier");
//$t1 = CUSTOMER;
//$t2 = SUPPLIER;
$type1 = _("Type");
$accbalance1 = _("Acc. balance");

if($type=='0'){
	$l=_("Add customer");
	$text=newWindow($l,'?action=lister&form=account&type='.CUSTOMER,480,480);
}else{
	$l=_("Add supplier");
	$text.=newWindow($l,'?action=lister&form=account&type='.SUPPLIER,480,480);
}
$tblheader = <<<EOT
$text

<table class="tablesorter">
	<thead>
<tr >
		<th class="header" style="width:3em">$n </th>
		<th class="header" style="width:4em">$type1</th>
		<th class="header" style="width:12em">$company1</th>
		<th class="header" style="width:12em">$contact1</th>
		<th class="header" style="width:8em">$address1</th>
		<th class="header" style="width:8em">$city1</th>
		<th class="header" style="width:4em">$zip1</th>
		<th class="header" style="width:4em">$accbalance1</th>
		<th class="header" ><!-- actions --></th>
	</tr>
	</thead>
	<tbody>
EOT;

//print "<br />\n";

$type = (int)$_GET['type'];
if($type=='0')
	$l = _("Customers managmenet");
else
	$l = _("Suppliers managmenet");
//print "<h3>$l</h3>\n";
//print "$srchform";





//ShowText('contact');



$company = str_replace('*', '%', $company);
$contact = str_replace('*', '%', $contact);
$address = str_replace('*', '%', $address);
$city = str_replace('*', '%', $city);

if(!$company && !$contact && !$address && !$city)
	$company = '%';
if(strpos($company, '%') === FALSE)
	$company .= '%';
if($contact && (strpos($contact, '%') === FALSE))
	$contact .= '%';
if($address && (strpos($address, '%') === FALSE))
	$address .= '%';
if($city && (strpos($city, '%') === FALSE))
	$city .= '%';

//adam: $t1 = CUSTOMER;
//$t2 = SUPPLIER;
$type = (int)$_GET['type'];
$query = "SELECT * FROM $accountstbl WHERE prefix='$prefix' AND type='$type' ";
$query1 = '';
if($company)
	$query1 .= "AND company LIKE '$company' ";
if($contact)
	$query1 .= "AND contact LIKE '$contact' ";
if($address)
	$query1 .= "AND address LIKE '$address' ";
if($city)
	$query1 .= "AND city LIKE '$city' ";
$query1 .= "ORDER BY contact";
$query .= $query1;
// print "Query: $query<br>\n";
$result = DoQuery($query, __LINE__);
$text= "$tblheader";

while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$company = $line['company'];
	$contact = $line['contact'];
	$address = $line['address'];
	$city = $line['city'];
	$zip = $line['zip'];
	//NewRow();
	$url = "?module=contact&amp;action=edit&amp;num=$num";
	$text.= "<tr><td><a href=\"$url\">$num</a></td>\n";
	if ($line['type']== CUSTOMER) $t = _("Customer");
	if ($line['type']== SUPPLIER) $t=  _("Supplier");
	$text.= "<td>$t</td>\n";
	$text.= "<td><a href=\"$url\">$company</a></td>\n";
	$text.= "<td>$contact</td>\n";
	$text.= "<td>$address</td>\n";
	$text.= "<td>$city</td>\n";
	$text.= "<td>$zip</td>\n";
	$sum = GetAcctTotal($num, $beginmysql, $endmysql);
	$text.= "<td>$sum</td>\n";
	$url = "?module=acctdisp&amp;account=$num&amp;begin=$begindmy&amp;end=$enddmy";
	$l = _("Transactions");
	$text.= "<td><input type=\"button\" value=\"$l\" onClick=\"window.location.href='$url'\" /></td>\n";
	$text.= "</tr>\n";
}

$text.= "</tbody></table>\n";

createForm($srchform.$text, $l,'',700);
?>

