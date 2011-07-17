<?php
/*********************************************************************************
Adam bh
 ********************************************************************************/

//global $PurchaseOrder_no;
//global $org_name, $org_address, $org_city, $org_code, $org_country, $org_phone, $org_fax, $org_website;
$org_name='מחשוב מהיר';
 $org_address='גבעת גאולה 1'; 
 $org_city='רמת גן'; 
 $org_code='52215';
 $org_country='69924504';
 $org_phone='0772105001'; 
 $org_fax='0775611355';
 $org_website='www.speedcomp.co.il';
$pdf_strings = Array(
	'LANGUAGENAME' =>'עברית',
	'NUM_FACTURE_NAME' =>  'מס. מסמך:',
    'FACTURE' =>  'מסמך',
    'VALID_TILL' =>  'תאריך אספקה:',
    'PODATE' =>  'תאריך הזמנה:',
    'REQCODE' =>  'Tracking#:',
    'AGENT' =>  'סכון:',
    'OrderCode' => 'הזמנה#',
    'Description' => 'פריט',
    'Qty' => 'כמות',
    'UnitPrice' => 'מחיר ליחידה',
    'LineTotal' => 'כמות',
    'VAR_SUBTOTAL' => 'סה"כ ביניים',
    'VAR_SHIPCOST' => 'Freight',
    'Tax' => 'מס',
    'VAR_TOTAL' => 'סה"כ',
    'ACCOUNT_NUMBER' => 'Acc#:',
    'IBAN_NUMBER' => 'IBAN:',
    'ROUTING_NUMBER' =>  'RT#:',
    'SWIFT_NUMBER' => 'BIC:',
    'BANK_NAME' => 'בנק:',
	'VAR_TAX_SHIP' =>  'Tax Shipping',
	'LineTotal' =>  'סה"כ',
	'Discount' =>  'הנחה',	
	'VAR_TAXID' =>  'מספר ח.פ:',
	'VAR_ADJUSTMENT' =>  'התאמה',
	'Tax_NAME' =>  '% מס" => ',
	'INCLUDE_NAME' =>  ' כלול',
	'Unit' =>  'יחידה',
	'ORG_POSITION' =>  'מנכ"ל',
	'VAR_PAGE' =>  'עמוד',
	'Position' => 'Pos',
	'VAR_OF' =>  'מתוך',
	'TAX_GROUP' =>  'Tax Mode: group',
	'TAX_INDIVIDUAL' =>  'Tax Mode: individual',
	'ISSUER' =>  'מאת:',
	'PHONE' =>  'טלפון:',
	'VAR_PHONE' => 'טל.:',
	'VAR_FAX' => 'פקס:',
	'CARRIER' => 'שליח:',
	'VENDORID' => 'יצרן#:',
	'MISSING_IMAGE' => 'Logo not assigned',
	);
$PDF_MARGIN_LEFT=15;
$PDF_MARGIN_FOOTER=247;
$username='אדם בן חור';//$bla['first_name'].' '.$bla['last_name'];
	$usermobile='0525972834';//$bla['phone_mobile'];
	$useremail='adam@speedcomp.co.il'; //$bla['email1'];
$font_size_footer=8;
$default_font='helvetica';
$liney=254;
//$pdf->SetFont($default_font,'',$font_size_footer);

	$pdf->SetTextColor(120,120,120);
	//*** first column
	$pdf->SetFont($default_font,'',$font_size_footer);
	$pdf->SetXY($PDF_MARGIN_LEFT , $PDF_MARGIN_FOOTER+8);
	$pdf->Cell($pdf->GetStringWidth($org_name),$pdf->getFontSize(),$org_name,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT , $PDF_MARGIN_FOOTER+12);
	$pdf->Cell($pdf->GetStringWidth($org_address),$pdf->getFontSize(),$org_address,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT , $PDF_MARGIN_FOOTER+16);
	$pdf->Cell($pdf->GetStringWidth($org_code),$pdf->getFontSize(),$org_code." ".$org_city,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT , $PDF_MARGIN_FOOTER+20);
	$pdf->Cell($pdf->GetStringWidth($org_country),$pdf->getFontSize(),$org_country,0,0,'R');//adam L/R
	//draw line
	$x =$PDF_MARGIN_LEFT+43;
	$pdf->SetDrawColor(120,120,120);
	$pdf->Line($x,$liney,$x,$liney+16);
	//*** second column
	$pdf->SetXY($PDF_MARGIN_LEFT+45 , $PDF_MARGIN_FOOTER+8);
	$pdf->Cell($pdf->GetStringWidth($pdf_strings['VAR_PHONE']." ".$org_phone),$pdf->getFontSize(),$pdf_strings['VAR_PHONE']." ".$org_phone,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+45 , $PDF_MARGIN_FOOTER+12);
	$pdf->Cell($pdf->GetStringWidth($pdf_strings['VAR_FAX']." ".$org_fax),$pdf->getFontSize(),$pdf_strings['VAR_FAX']." ".$org_fax,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+45 , $PDF_MARGIN_FOOTER+16);
	//$pdf->Cell($pdf->GetStringWidth($pdf_strings['VAR_TAXID'].' '.$org_taxid),$pdf->getFontSize(),$pdf_strings['VAR_TAXID'].' '.$org_taxid,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+45 , $PDF_MARGIN_FOOTER+20);
	//$pdf->Cell($pdf->GetStringWidth($org_irs),$pdf->getFontSize(),$org_country,0,0,'R');//adam L/R
	
	//draw line
	$x =$PDF_MARGIN_LEFT+83;
	//$pdf->Line($x,$pdf->h - PDF_MARGIN_FOOTER+9,$x,$pdf->h - PDF_MARGIN_FOOTER+23);

	//third column
	$pdf->SetXY($PDF_MARGIN_LEFT+85 , $PDF_MARGIN_FOOTER+8);
	//$pdf->Cell($pdf->GetStringWidth($bank_name),$pdf->getFontSize(),$bank_name,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+85 , $PDF_MARGIN_FOOTER+12);
	//$pdf->Cell($pdf->GetStringWidth($pdf_strings['ACCOUNT_NUMBER']." ".$bank_account),$pdf->getFontSize(),$pdf_strings['ACCOUNT_NUMBER']." ".$bank_account,0,0,'R');//adam L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+85 , $PDF_MARGIN_FOOTER+16);
	//$pdf->Cell($pdf->GetStringWidth($pdf_strings['ROUTING_NUMBER']." ".$bank_routing),$pdf->getFontSize(),$pdf_strings['ROUTING_NUMBER']." ".$bank_routing,0,0,'R');//adam L/R
	//draw line
	$x =$PDF_MARGIN_LEFT+130;
	$pdf->Line($x,$liney,$x,$liney+16);//draw line
	//fourth column
	$pdf->SetXY($PDF_MARGIN_LEFT+132 , $PDF_MARGIN_FOOTER+8);
	$pdf->Cell(20,4,$username,0,0,'R');//adam:L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+132 , $PDF_MARGIN_FOOTER+12);
	$pdf->Cell(20,4,$usermobile,0,0,'R');//adam:L/R
	$pdf->SetXY($PDF_MARGIN_LEFT+132 , $PDF_MARGIN_FOOTER+16);
	$pdf->Cell(20,4,$useremail,0,0,'R');//adam:L/R
	
	//reset colors
	$pdf->SetTextColor(0,0,0);				
	//Print page number with po id
	$pdf->SetXY($PDF_MARGIN_LEFT, $PDF_MARGIN_FOOTER+19);
	$pdf->Cell(0,10,$pdf_strings['NUM_FACTURE_NAME'].' '.$docnum.', '.$pdf_strings['VAR_PAGE'].' '.$pdf->PageNo().' '.$pdf_strings['VAR_OF'].' '.'1',0,0,'C');

	//reset colors
	$pdf->SetTextColor(0,0,0);		//*/		
?>