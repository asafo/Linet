<?PHP
/*
 | Monthly profit and loss graph
 | Written for Freelance accounting system by Ori Idan November 2009
 */

global $accountstbl, $prefix;
global $begindmy, $enddmy;

// $demo = 1;

$mname = array("'ינואר", "פבר'", "מרץ", "אפר'", "מאי", "יוני", "יולי", "אוג'", "ספט'", "אוק'", "נוב'", 
"דצמ'");

function GetLastDayOfMonth($month, $year) {
	$last = 31;
	
	if($month == 0)
		return $last;
	while(!checkdate($month, $last, $year)) {
	//	print "$last-$month-$year<br>\n";
		$last--;
	}
	return $last;
}

if(!$demo) {
	function GetAcctType($acct) {
		global $prefix, $accountstbl;

		$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
		$result = DoQuery($query, "GetAcctType");
		$line = mysql_fetch_array($result, MYSQL_NUM);
		return $line[0];
	}

	if(!isset($begindmy)) {
		$y = date("Y");
		$m = 1;
	}
	else
		list($d, $m, $y) = explode('-', $begindmy);
	if(isset($enddmy))
		list($d, $lm, $ly) = explode('-', $enddmy);
//	print "$begindmy - $enddmy<br>\n";
	$sm = $m;
	$sy = $y;

	$grp = INCOME;
	$data1 = array();
	$m = $sm;
	$y = $sy;
	for($i = 0; $i < 12; $i++) {
		$bdate = "$y-$m-1";
		$l = GetLastDayOfMonth($m, $y);
		$edate = "$y-$m-$l";
		$t = round(GetGroupTotal($grp, $bdate, $edate), 0);
		$data1[$i] = $t;
		$label[$i] = $mname[$m-1];
//		print "$bdate $edate $t<br>\n";
		$m++;
		if($m > 12) {
			$m = 1;
			$y++;
		}
//		if(($m == $lm) && ($y == $ly))
//			break;
	}

	// print_r($data1);

	$grp = OUTCOME;
	$data2 = array();
	$m = $sm;
	$y = $sy;
	for($i = 0; $i < 12; $i++) {
		$bdate = "$y-$m-1";
		$l = GetLastDayOfMonth($m, $y);
		$edate = "$y-$m-$l";
		$t = round(GetGroupTotal($grp, $bdate, $edate), 0);
		if($t < 0)
			$t *= -1.0;
		$data2[$i] = $t;
		$m++;
		if($m > 12) {
			$m = 1;
			$y++;
		}
//		if(($m == $lm) && ($y == $ly))
//			break;
	}

//	print_r($data1);
//	print_r($data2);
	for($i = 0; $i < 12; $i++)
		$profloss[$i] = $data1[$i] - $data2[$i];
}

if($demo)
	$profloss = array(2000, 1200, -100, 100, -2200, -1000, 100, 200, -100, -300, 300, 400);	

$max = 0;
$min = 0;
// print_r($profloss);
/* Find max and min for autoscale calculation */
for($i = 0; $i < 12; $i++) {
	if($profloss[$i] > $max)
		$max = $profloss[$i];
	if($profloss[$i] < $min)
		$min = $profloss[$i];
}
// print "max: $max, min: $min<br>\n";

require_once('chart_func.php');

$graph_height = 100;
$toppadding = 10;
$bottompadding = 10;
$ptwidth = 30;
$topline = 5;	/* legend line height */
$bottomline = 10;
$fname = "profgraph.png";
$gcolor = "0000FF";
$fillcolor = "ACD6F5";
$ptcolor = "00FF00";
$label_color = "000000";	/* black */
$gray = "B0B0B0";

$minstr = number_format($min);
$maxstr = number_format($max);

$amin = abs($min);
if($amin > $max) {
	$max = $amin;
	$maxstr = number_format($max);
	$d = $amin / ($graph_height / 2);
	$numwidth = LabelWidth($minstr);
//	print "min: $min, d: $d<br>\n";
}
else {
	$d = $max / ($graph_height / 2);
//	if($d == 0)
//		$d = 1;
	$numwidth = LabelWidth("-$maxstr");
	$min = $max * -1.0;
	$minstr = number_format($min);
//	print "max: $max, d: $d<br>\n";
}

$width = $ptwidth * 12 + $numwidth;
$height = $topline + $graph_height + $toppadding + $bottompadding + 1 + $bottomline;

for($i = 0; $i < 12; $i++) {
	$val = $profloss[$i];
//	print "val: $val, ";
	$t = $val / $d;
//	print "h: $t, ";
	$h[$i] = $val / $d + ($graph_height / 2);
//	print "h[i]: $h[$i] <br>\n";
}

$img = imagecreatetruecolor($width, $height);
$white = colorHex($img, "FFFFFF");
$gc = colorHex($img, $gray);
$lc = colorHex($img, $label_color);
imagefill($img, 0, 0, $white);

/* max */
$x = $numwidth - LabelWidth($maxstr);
imageline($img, $numwidth+1, $topline, $width, $topline, $gc);
// print "maxstr: $maxstr, label height: $y<br>\n";
$topline += $toppadding;
imagettftext($img, 10, 0, $x, $topline+5, $lc, "fonts/arial.ttf", $maxstr);
imageline($img, $numwidth+1, $topline, $width, $topline, $gc);
/* half */
$half = $max / 2;
$halfstr = number_format($half);
$x = $numwidth - LabelWidth($halfstr);
$y = $graph_height / 4 + $topline;
imagettftext($img, 10, 0, $x, $y+5, $lc, "fonts/arial.ttf", $halfstr);
imageline($img, $numwidth+1, $y, $width, $y, $gc);
/* zero */
$x = $numwidth - LabelWidth("0");
$y = $graph_height / 2 + $topline;
imagettftext($img, 10, 0, $x, $y+5, $lc, "fonts/arial.ttf", "0");
imageline($img, $numwidth+1, $y, $width, $y, $gc);

/* min */
$y = $topline + $graph_height;
$x = $numwidth - LabelWidth($minstr);
imagettftext($img, 10, 0, $x, $y+5, $lc, "fonts/arial.ttf", $minstr);
imageline($img, $numwidth+1, $y, $width, $y, $gc);

$y += $bottompadding;
imageline($img, $numwidth+1, $y, $width, $y, $gc);

/* half */
$half = $min / 2;
$halfstr = number_format($half);
$x = $numwidth - LabelWidth($halfstr);
$y = $graph_height * 3 / 4 + $topline;
imagettftext($img, 10, 0, $x, $y+5, $lc, "fonts/arial.ttf", $halfstr);
imageline($img, $numwidth+1, $y, $width, $y, $gc);

/* vertical start line */
imageline($img, $numwidth+1, $topline-$toppadding, $numwidth+1, $graph_height+$topline+$bottompadding, $gc);

/* month labels */
$xstart = $ptwidth / 2 + $numwidth + 1;
$ystart = $height;
for($i = 0; $i < 12; $i++) {
	$l = $mname[$i];
	$x = $xstart + $i * $ptwidth;
//	print "$l<br>\n";
	$lrev = utf8_strrev($l, false);
	$lw = LabelWidth($lrev);
	$lx = $x - $lw / 2;
	imagettftext($img, 10, 0, $lx, $ystart, $lc, "fonts/arial.ttf", $lrev);
	/* vertical line */
	imageline($img, $x, $topline-$toppadding, $x, $graph_height+$topline+$bottompadding, $gc);
}
imageline($img, $width-1, $topline-$toppadding, $width-1, $graph_height+$topline+$bottompadding, $gc);

$gc = colorHex($img, $gcolor);
foreach($h as $i => $val) {
//	imagefilledellipse($img, $x, $y, 7, 7, $gc);
	$x = $xstart + $i * $ptwidth;
	$y = ($graph_height + $topline) - $val;
	if($i > 0)
		imageline($img, $x, $y, $prevx, $prevy, $gc);
	$prevx = $x;
	$prevy = $y;
	$ax[$i] = $x;
	$ay[$i] = $y;
	imagefilledellipse($img, $x, $y, 7, 7, $gc);
}
/* fill below curve */
$fc = colorHex($img, $fillcolor);
$zeroline = ($graph_height / 2 + $topline);
for($i = 1; $i < 12; $i++) {
	$p = $i - 1;
	if(($y[$p] >= 0) && ($y[$p] >= 0)) {	/* both are above 0 */
		$a = array($ax[$p], $zeroline, $ax[$p], $ay[$p], $ax[$i], $ay[$i], $ax[$i], $zeroline);
		imagefilledpolygon($img, $a, 4, $fc);
	}
}
/* Draw lines again */
$pc = colorHex($img, $ptcolor);
foreach($h as $i => $val) {
	$x = $xstart + $i * $ptwidth;
	$y = ($graph_height + $topline) - $val;
	if($i > 0)
		imageline($img, $x, $y, $prevx, $prevy, $gc);
	$prevx = $x;
	$prevy = $y;
//	imagefilledellipse($img, $x, $y, 7, 7, $pc);
}
foreach($h as $i => $val) {
	$x = $xstart + $i * $ptwidth;
	$y = ($graph_height + $topline) - $val;
	imagefilledellipse($img, $x, $y, 7, 7, $pc);
}

imagepng($img, "tmp/$fname");
if($demo)
	print "<img src=\"tmp/$fname\">\n";

ImageDestroy($img);

?>
