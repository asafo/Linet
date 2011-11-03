<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=9" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <meta name="author" content="Super User" />
  <title><?php print $title; ?></title>

<link rel="stylesheet" href="../../style/linet.css" type="text/css" />


<script type="text/javascript">
function loadDoc(step){
var xmlhttp;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("main").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","?step="+step+"&non",true);
xmlhttp.send();
}
</script>
</head>
	<body onload="loadDoc(1)">
		<form action="index.php" method="post">
			<input type="hidden" name="step" value="1">
		</form>
		<div id="haed">
			<img style="float: right;"src="../../img/logo.png" alt="logo" />
			<?php 
			print "<div style=\"float: right;margin-right: 15px;margin-top: 30px;\">".$steps[$step]."</div><div style=\"float: left; margin-left: 10px; margin-top: 30px;\">"; 
			if ($allowcancel) 
					print '<a href="../../">'._("ביטול עדכון").'</a>'; 
				else 
					print _("ביטול עדכון");
			?>
			</div>
		</div>
		<div id="main">
			<?php  print $content;  ?>	
		</div>
		
		<div id="footer">
			<ul>
			<?php
			foreach ($steps as $name){
				if ($name==$steps[$step]){
						print '<il class="active">'.$name.'  </il>';
					}else{
						print '<il>'.$name.'  </il>';
					}
			}
			?>
			</ul>
		</div>
	</body>
</html>