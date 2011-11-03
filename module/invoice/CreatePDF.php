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
/* Not present in PHP5? */
if(!function_exists('http_build_query')) {
	function http_build_query( $formdata, $numeric_prefix = null, $key = null ) {
		$res = array();
		
		foreach ((array)$formdata as $k=>$v) {
			$tmp_key = urlencode(is_int($k) ? $numeric_prefix.$k : $k);
			if ($key) {
				$tmp_key = $key.'['.$tmp_key.']';
			}
			if ( is_array($v) || is_object($v) ) {
				$res[] = http_build_query($v, null, $tmp_key);
			} else {
				$res[] = $tmp_key."=".urlencode($v);
			}
		}
	
	return implode("&", $res);
	}
}
require_once('module/html2ps/config.inc.php');
require_once('module/html2ps/pipeline.factory.class.php');
parse_config_file('module/html2ps/html2ps.config');

class PDF {
	var $_url;
	var $_tmp_path;
	var $_html_file;
	var $_html_url;
	var $_html2ps_url;
	var $_pdf_data;
	
	function PDF($html, $html2ps_options = NULL, $tmp_path = NULL, $html2ps_path = NULL){
		$this->url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
		
		if ($tmp_path) $this->tmp_path = $tmp_path;
		else $this->tmp_path = 'tmp';
	
		if (!$html2ps_options) {
			$html2ps_options['pixels'] = 1024;
			$html2ps_options['scalepoints'] = true;
			$html2ps_options['renderimages'] = true;
			$html2ps_options['renderlinks'] = true;
			$html2ps_options['media'] = 'A4';
			$html2ps_options['cssmedia'] = 'screen';
			$html2ps_options['leftmargin'] = 10;
			$html2ps_options['rightmargin'] = 10;
			$html2ps_options['topmargin'] = 10;
			$html2ps_options['bottommargin'] = 10;
			$html2ps_options['landscape'] = false;
			$html2ps_options['pageborder'] = false;
			$html2ps_options['debugbox'] = false;
			$html2ps_options['encoding'] = NULL;
			$html2ps_options['method'] = 'fpdf';
			$html2ps_options['pdfversion'] = 1.3;
			$html2ps_options['compress'] = true;
			$html2ps_options['transparency_workaround'] = false;
			$html2ps_options['imagequality_workaround'] = false;
			$html2ps_options['URL'] = $this->url.'/'.$this->tmp_path.'/'.session_id().'.html';
		}
		
		foreach ($html2ps_options as $key => $value) {
			if (is_bool($value)) {
				if ($value) {
					$html2ps_options[$key] = 1;
				} else {
					unset($html2ps_options[$key]);
				}
			}
		}
	
		if ($html2ps_path) $this->html2ps_url = $html2ps_path;
		else $this->html2ps_url = 'module/html2ps';
		$this->html2ps_url = $this->url.'/'.$this->html2ps_url.'/html2ps.php?';
		$this->html2ps_url .= http_build_query($html2ps_options);
		
		$html_file = fopen($this->tmp_path.'/'.session_id().'.html', 'w');
		fwrite ($html_file, $html);
		fclose ($html_file);
		$this->html_file = $this->tmp_path.'/'.session_id().'.html';
	}
	
	function createPDF(){
		$this->pdf_data = file_get_contents($this->html2ps_url.'&output=1');
		unlink($this->html_file);
		
		$pdf_file = fopen($this->tmp_path.'/'.session_id().'.pdf', 'w');
		fwrite($pdf_file, $this->pdf_data);
		fclose($pdf_file);
	}
	
	function getURL(){
		if (! $this->pdf_data) $this->createPDF();
		return $this->url.'/'.$this->tmp_path.'/'.session_id().'.pdf';
	}
}
/*
function get_pdf($html,$docnum,$user,$mail,$phone,$logo){
	//require_once('module/tcpdf/pdf.php');
	require_once('module/tcpdf/tcpdf.php');
	//require_once('module/tcpdf/pdfconfig.php');

	
	//global $logo;
	
	
	$pdf = new TCPDF( 'P', 'mm', 'A4' );
	$pdf->Open();

	$pdf->AddPage();
	//return $pdf;
	
	$imageBlock=array("10","3","60","20");
	$logo_name=$logo;
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
	
	//$pdf->writeHTML($html, true, false, true, false, '');
	//ob_end_clean();
	//$pdf->Output('Invoice.pdf','D');
	//require_once('templates/header.php');
	require_once('templates/body.php');
	////add footer
	require_once('templates/footer.php');
	return $pdf;
}*/
?>