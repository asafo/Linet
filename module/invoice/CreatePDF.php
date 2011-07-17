<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/



function get_pdf($html,$docnum,$user,$mail,$phone){
	require_once('module/tcpdf/pdf.php');
	require_once('module/tcpdf/pdfconfig.php');

	$pdf = new PDF( 'P', 'mm', 'A4' );
	$pdf->Open();


	

	$pdf->AddPage();
	$imageBlock=array("10","3","60","20");
	$logo_name='img/logo.jpg';
	$pdf->addImage( $logo_name, $imageBlock);
	//////////////////////////////////////////////
	// set some language dependent data:
	$lg = Array();
	$lg['a_meta_charset'] = 'UTF-8';
	$lg['a_meta_dir'] = 'rtl';
	$lg['a_meta_language'] = 'he';
	$lg['w_page'] = 'page';
	//set some language-dependent strings
	$pdf->setLanguageArray($lg); 
	define('USD',"$");
	define('EURO', chr(128) );
	//output the HTML content
	$pdf->writeHTML($html, true, false, true, false, '');
	//ob_end_clean();
	//$pdf->Output('Invoice.pdf','D');
	require_once('templates/header.php');
	////add footer
	require_once('templates/footer.php');
	return $pdf;
}
?>