<?PHP
/*
 | menu for Linet
 */
$help=getHelp();
global $curcompany;
$MainMenu = array(
	_("Settings") . "|module=main" => array(
			 _("Bussines details") . '|?module=defs',
			 _("Accounts") . '|?module=acctadmin',
			 _("Business docs") . '|?module=docnums',
		
			 _("Items") . '|?module=items',
			 _("Currency rates") . '|?module=curadmin',
			 _("Openning balances") . '|?module=opbalance',
			 _("Edit user") . '|?module=login&amp;action=edituser',
			 _("Add user") . '|?module=login&amp;action=adduser'
		),
	_("Income") . '|#' => array(
			_("Manage Customers") . '|?module=contact&amp;type=0',
			 _("Proforma") . '|?module=docsadmin&amp;targetdoc=1',
			 _("Delivery doc.") . '|?module=docsadmin&amp;targetdoc=2',
			 _("Invoice") . '|?module=docsadmin&amp;targetdoc=3',
			 _("Credit inv.") . '|?module=docsadmin&amp;targetdoc=4',
			 _("Return doc.") . '|?module=docsadmin&amp;targetdoc=5',
			 _("Quote") . '|?module=docsadmin&amp;targetdoc=6',
			 _("Sales Order") . '|?module=docsadmin&amp;targetdoc=7',
			
		     _("Invoice receipt") . '|?module=docsadmin&amp;targetdoc=9',
		   //  _("Parchace Order") . '|module=docsadmin&amp;targetdoc=10',
		  	 _("Print docs.") . '|?module=showdocs',
		  	// _("Register manual income") . '|?module=income',
			 
	),
	 _("Outcome") . '|#'=>array(
	 		 _("Manage Suppliers") . '|?module=contact&amp;type=1',
		     _("Parchace Order") . '|?module=docsadmin&amp;targetdoc=10',
		     _("inseret Buisness outcome") . '|?module=outcome',
			 _("insert Asstes outcome") . '|?module=outcome&amp;opt=asset',
			 
	 ),
		//'menu26' => '׳�׳©׳›׳•׳¨׳× ׳�׳•׳¨׳™|module=orisalary',
	 //_("Bussiness docs") . '|'=> array(
	_("Register") .'|#'=> array(
	 		_("Receipt") . '|?module=docsadmin&amp;targetdoc=8',//adam:
	 		_("Bank deposits") . '|?module=deposit&amp;type=2',
	 		_("Payment") . '|?module=payment',
			 _("VAT payment") . '|?module=payment&amp;opt=vat',
			 _("Nat. Ins. payment") . '|?module=payment&amp;opt=natins',
			// _("Manual recipet").'|?module=receipts'
	 ),
	  _("Reconciliations") . '|#'=>array(
		 _("Bank docs entry") . '|?module=bankbook',
		 _("Bank recon.") . '|?module=extmatch',
		 _("Show recon.") . '|?module=dispmatch',
		 _("Accts. recon.") . '|?module=intmatch'
	 ),
	 _("Reports") . '|#'=> array(
			 _("Incomes outcomes") . '|?module=tranrep',
			 _("Customers owes") . '|?module=owe',
			 _("Profit & loss") . '|?module=profloss',
			 _("Monthly Prof. & loss") . '|?module=mprofloss',
			 _("VAT calculation") . '|?module=vatrep',
			 _("Balance") . '|?module=balance',
			 _("Income tax advances").'|?module=taxrep',
			// _("Nikoi mass Bamkor").'|?module=srctax',
			 _("Receipts and payments").'|?module=inout'
	 ),
	 _("Import Export") . '|#' => array(
			 _("Open docs") . '|?module=openfrmt',
			 _("Open docs Import") . '|?module=openfrmtimport',
			 _("General backup") . '|?module=backup',
			 _("Backup restore") . '|?module=backup&amp;step=restore',
			 _("PCN874") . '|?module=pcn874',
			 
	 ),
	 ' '=>array(),
	 _("Support") . '|#' => array(
		 _("Update"). '|module/update/',
		 _("Support") . '|?module=support',
		 _("About"). '|?module=about',
		 _("Bag Report"). '|?module=about'
	 ),
	//'demo' => _("Demo") . '|module=demoreg',
	//'main1' => _("Main") . '|id=main',
	//'mainlogin' => _("Login") . '|action=login'
);
if ($curcompany->bidi!=2)
	unset($MainMenu[_("Reconciliations") . '|#']);
else
	unset($MainMenu[' ']);
$str='<div class="navBar" dir="rtl"><ul id="drop_down_menu" dir="rtl">';
$firsty=true;
$numItems = count($MainMenu);
$i=0;
foreach ($MainMenu as $key=>$value){
	$class='';
	//start li
	list($n, $l) = explode('|', $key);
	if($firsty){
		$class=" class=\"first\"";
		$firsty=false;
	}
	
	if($i+1==$numItems) $class=" class=\"last\"";
	$str.="<li$class><span><p>$n</p></span>";
	$str.="<ul>";
	$last=count($value);
	$i1=0;
	foreach ($value as $innerkey){
		list($n, $l) = explode('|', $innerkey);
		if($i1+1==$last) $classy=" class=\"last\"";else $classy='';
		$str.="<li$classy><a href=\"$l\">$n</a></li>";
		$i1++;
	}
	//end li
	$str.="</ul></li>";
	$i++;
}
$str.='</ul></div>'
?>
