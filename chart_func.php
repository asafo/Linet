<?PHP	
if(!function_exists(colorHex)) {
	function colorHex($img, $HexColorString) 
	{
		$R = hexdec(substr($HexColorString, 0, 2));
		$G = hexdec(substr($HexColorString, 2, 2));
		$B = hexdec(substr($HexColorString, 4, 2));
		return ImageColorAllocate($img, $R, $G, $B);
	}

	function colorHexshadow($img, $HexColorString, $mork) 
	{
		$R = hexdec(substr($HexColorString, 0, 2));
		$G = hexdec(substr($HexColorString, 2, 2));
		$B = hexdec(substr($HexColorString, 4, 2));

		if ($mork)
		{
			($R > 99) ? $R -= 100 : $R = 0;
			($G > 99) ? $G -= 100 : $G = 0;
			($B > 99) ? $B -= 100 : $B = 0;
		}
		else
		{
			($R < 220) ? $R += 35 : $R = 255;
			($G < 220) ? $G += 35 : $G = 255;
			($B < 220) ? $B += 35 : $B = 255;				
		}			
		
		return ImageColorAllocate($img, $R, $G, $B);
	}

	function random_color()
	{
		mt_srand((double)microtime()*1000000);
		$c = '';
		while (strlen($c) < 6)
		{
			$c .= sprintf("%02X", mt_rand(0, 255));
		}
		return $c;
	}

	function utf8_strrev($str, $reverse_numbers) {
	  preg_match_all('/./us', $str, $ar);
	  if ($reverse_numbers)
		return join('',array_reverse($ar[0]));
	  else {
		  $temp = array();
		  foreach ($ar[0] as $value) {
			 if (is_numeric($value) && !empty($temp[0]) && is_numeric($temp[0])) {
				foreach ($temp as $key => $value2) {
				   if (is_numeric($value2))
					 $pos = ($key + 1);
				   else
					  break;
				}
				$temp2 = array_splice($temp, $pos);
				$temp = array_merge($temp, array($value), $temp2);
			 } else
				array_unshift($temp, $value);
		  }
		  return implode('', $temp);
	  }
	}
	
	function LabelWidth($label) {
		$sizearr = imagettfbbox(10, 0, "fonts/arial.ttf", $label);
		return  ($sizearr[2] - $sizearr[0]);
	}
	
	function LabelHeight($label) {
		$sizearr = imagettfbbox(10, 0, "fonts/arial.ttf", $label);
		return ($sizearr[7] - $sizearr[1]);
	}
}

?>
