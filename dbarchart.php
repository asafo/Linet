<?PHP
/*
 | Dual bars bar chart.
 | Written by Ori Idan for freelnace accounting software.
 */

/*
 | Input is 3 arrays and two labels:
 | * label - array of labels
 | * data1 - first array of values
 | * data2 - second array of values
 | * l1 - Label for bar1
 | * l2 - Label for bar2
 |
 | Draws two bars for each label, bar for data1 and bar for data 2
 */

global $lang;

if(!isset($label)) {
	/* Default values for testing */
	$demo = 1;
	$label = array("ינואר", "פבר'", "מרץ", "אפר'", "מאי", "יוני", "יולי", "אוג'", "ספט'", "אוקט'", "נוב'", "דצמבר");
	$data1 = array(80, 20, 50, 40, 20, 90, 10, 30, 50, 60, 70, 80);
	$data2 = array(70, 30, 60, 30, 10, 80, 20, 20, 40, 50, 70, 68);
	$fname = "dbarchart1.png";
}

require_once('chart_func.php');

if(!isset($l1)) {
	$l1 = _("Income");
	$l2 = _("Outcome");
//	$l1 = "הכנסות";
//	$l2 = "הוצאות";
}
$graph_height = 100;
if(!isset($bar_width))
	$bar_width = 15;
$shadow_width = 2;
if(!isset($barsep_width))
	$barsep_width = 7;
$topline = 15;	/* legend line height */
$bottomline = 10;
$color1 = "00B7E3";
$color2 = "FF005C";
/*
$color2 = "FFB3BE";
$color1 = "ACD6F5";
*/
$label_color = "000000";	/* black */
$shadow = "666666";		/* shadow from hel :-) */
$gray = "B0B0B0";

/* Find max value for scaling */
$max = 0.0;
foreach($data1 as $val) {
	if($val > $max)
		$max = $val;
}
foreach($data2 as $val) {
	if($val > $max)
		$max = $val;
}

$d = $max / $graph_height;	/* graph division */
if($d == 0) {
	$d = 1;
//	$max = 1000;
}
foreach($data1 as $i => $val)
	$h1[$i] = $val / $d;
foreach($data2 as $i => $val)
	$h2[$i] = $val / $d;

/* calculate graph width */
$maxstr = number_format($max);
$numwidth = LabelWidth($maxstr);

$numbars = count($label);
$width = $numwidth + $numbars * 2 * ($bar_width + $shadow_width + $barsep_width) + 1;
if($numbars > 2)
	$width -= $bar_width;	/* a bug gives greater results for $width */
else
	$width += 60;	/* Special case for one pair of bars */
$height = $topline + $graph_height + 1 + $bottomline;

// print "width: $width, height: $height<br>\n";
$img = imagecreatetruecolor($width, $height);
$white = colorHex($img, "FFFFFF");
$gc = colorHex($img, $gray);
imagefill($img, 0, 0, $white);

$c1 = colorHex($img, $color1);
$c2 = colorHex($img, $color2);
$sc = colorHex($img, $shadow);
$lc = colorHex($img, $label_color);

/* Display grid numbers */
/* max */
$x = 0;
$y = $topline + 5;	/* top line + half label height */
// print "maxstr: $maxstr, label height: $y<br>\n";
imagettftext($img, 10, 0, 0, $y, $lc, "fonts/arial.ttf", $maxstr);
imageline($img, $numwidth+1, $topline, $width, $topline, $gc);
/* half */
$half = $max / 2;
$halfstr = number_format($half);
$x = $numwidth - LabelWidth($halfstr);
$y = $graph_height / 2 + $topline;
imagettftext($img, 10, 0, $x, $y+5, $lc, "fonts/arial.ttf", $halfstr);
imageline($img, $numwidth+1, $y, $width, $y, $gc);
/* zero */
$x = $numwidth - LabelWidth("0");
$y = $graph_height + $topline;
imagettftext($img, 10, 0, $x, $y, $lc, "fonts/arial.ttf", "0");
$x = $numwidth + 1;
$y1 = 10;
$y2 = $topline + $graph_height;
imageline($img, $x, $y1, $x, $y2, $sc);
$x1 = $x;
$x2 = $width;
$y = $y2;
imageline($img, $x1, $y, $x2, $y, $sc);

$x = $width - 5;
$y = 5;
imagefilledellipse($img, $x, $y, 10, 10, $sc);
imagefilledellipse($img, $x-$shadow_width, $y+$shadow_width, 10, 10, $c2);
$w = LabelWidth($l2);
$x = $x - 10 - $w;
$y = 10;
if($lang == 'he')
	$l2rev = utf8_strrev($l2, false);
else
	$l2rev = $l2;
imagettftext($img, 10, 0, $x, $y, $lc, "fonts/arial.ttf", $l2rev);

$x -= 20;
$x -= 5;
$y = 5;
imagefilledellipse($img, $x, $y, 10, 10, $sc);
imagefilledellipse($img, $x-$shadow_width, $y+$shadow_width, 10, 10, $c1);
$w = LabelWidth($l1);
$x = $x - 10 - $w;
$y = 10;
if($lang == 'he')
	$l1rev = utf8_strrev($l1, false);
else
	$l1rev = $l1;
imagettftext($img, 10, 0, $x, $y, $lc, "fonts/arial.ttf", $l1rev);

$xstart = $numwidth + 5;
if(count($label) < 2)
	$xstart += 20;
$ystart = $height;
foreach($label as $i => $l) {
	$x = $xstart + $i * 2 * ($bar_width + $barsep_width);
	$lrev = utf8_strrev($l, false);
	$lw = LabelWidth($lrev);
	$lx = $x + ($bar_width + $shadow_width + 2) - $lw / 2;
	imagettftext($img, 10, 0, $lx, $ystart, $lc, "fonts/arial.ttf", $lrev);
	$y = $topline + $graph_height - $h1[$i];
	$y1 = $topline + $graph_height;
//	if($y < 0)
//		print "y: $y y1: $y1<br>\n";
	imagefilledrectangle($img, $x+$shadow_width, $y-$shadow_width, $x+$shadow_width + $bar_width, $y2, $sc);
	imagefilledrectangle($img, $x, $y, $x + $bar_width, $y2, $c1);
	$x += $bar_width + $shadow_width + 2;
	if(count($label) < 2)
		$x += 10;
	$y = $topline + $graph_height - $h2[$i];
	imagefilledrectangle($img, $x+$shadow_width, $y-$shadow_width, $x+$shadow_width + $bar_width, $y2, $sc);
	imagefilledrectangle($img, $x, $y, $x + $bar_width, $y2, $c2);
}

// header('Content-type: image/jpg');
imagepng($img, "tmp/$fname");
if($demo)
	print "<img src=\"tmp/$fname\">\n";

ImageDestroy($img);

?>
