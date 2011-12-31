<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=9" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <meta name="author" content="Super User" />
  <title><?php print $title; ?></title>

<link rel="stylesheet" href="../../style/linet.css" type="text/css" />
<script type="text/javascript" src='../../js/jquery.min.js'></script>


<script type="text/javascript">
function loadDoc(step){
	$('#main').html("Loading...");
$.post("?", { "step": step, "non": true }, function(data) {
	   $('#main').html(data);
	 });
}
</script>
</head>
	<body onload="loadDoc(1)" dir="rtl">
		<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
		<table class="form" id="haed">
			<tr>
				<td class="ftr"><img src="../../img/ftr.png" alt="formright"  /></td>
				<td class="ftc">
					<div class="formtitle"><p><?php print _("Linet Update Wizard")?></p></div>
					<div class="formhelp"><a class="help" target="_blank" href="http://www.linet.org.il/index.php/support/user-help-navigate?id=83"><img src="../../img/icon_help.png" alt="Icon help" /><span>עזרה</span></a></div>				
				</td>
				<td class="ftl"><img src="../../img/ftl.png" alt="formleft" /></td>
			</tr>
			<tr>
				<td class="fcr"></td>
				<td class="fcc" style="width:700px;height: 400px;" id="main">
						Loading...	
				</td>
				<td class="fcl"></td>
			</tr>
			<tr>
				<td class="fbr"><img src="../../img/fbr.png" alt="formright" /></td>
				<td class="fbc">
						</td>
				<td class="fbl"><img src="../../img/fbl.png" alt="formleft" /></td>
			</tr>
		</table>
	</body>
</html>