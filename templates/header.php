<?php
// ************** Begin Top-Left Header **************
// Address
$default_font='helvetica';
$org_name='מחשוב מהיר';
 $org_address='גבעת גאולה 1'; 
 $org_city='רמת גן'; 
 $org_code='52215';
 $org_country='69924504';
 $org_phone='0772105001'; 
 $org_fax='0775611355';
 $org_website='www.speedcomp.co.il';
$xmargin = '110';
$ymargin = '22';//adam: 55
//senders info
$pdf->SetTextColor(120,120,120);
// companyBlockPositions -> x,y,width
$companyText=$org_name." - ".$org_address." - ".$org_code." ".$org_city;
$pdf->SetFont($default_font,'B',6);
$pdf->SetXY($xmargin, $ymargin);
$pdf->MultiCell(80,$pdf->getFontSize(), $companyText,0,'L',0);
$pdf->SetTextColor(0,0,0);//*/

?>