<?PHP
global $abspath;
global $tinymcepath;
global $lang;
global $dir;

$tinymce = <<<MCE
<!-- TinyMCE -->
<script type="text/javascript" src="/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		language : "he",
		plugins : "table,print,advimage,emotions,insertdatetime,preview,searchreplace,paste,contextmenu,directionality,fullscreen,noneditable,visualchars,nonbreaking",
//		theme_advanced_buttons1_add_before : "save,newdocument,separator",
		theme_advanced_buttons1_add : "fontselect,fontsizeselect",
		theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor,advsearchreplace",
		theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
		theme_advanced_buttons3_add_before : "tablecontrols,separator",
		theme_advanced_buttons3_add : "emotions,styleprops,separator,print,separator,rtl,ltr,separator,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "right",
		theme_advanced_path : "false",
//		theme_advanced_path_location : "bottom",
		content_css : "http://www.drorit.co.il/drorit.css",
	    	plugin_insertdate_dateFormat : "%d-%m-%Y",
	    	plugin_insertdate_timeFormat : "%H:%M:%S",
		extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color],span[class|align]",
		directionality : "$dir",
//		external_image_list_url : "example_image_list.js",
		file_browser_callback : "fileBrowserCallBack",
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing : true,
		nonbreaking_force_tab : true,
		apply_source_formatting : true,
		document_base_url : "${abspath}index.php",
		relative_urls: false,
		convert_urls: false
	});

	function fileBrowserCallBack(field_name, url, type, win) {
		// This is where you insert your custom filebrowser logic
		var win1 = new Array();
		if(type == 'image')
			win1["file"] = "${abspath}pics.php";
		else
			win1["file"] = "${abspath}intlink.php";
		win1["title"] = "קישור פנימי";
		win1["title"] = _("Internal link");
    		win1["width"] = "420";
    		win1["height"] = "400";
    		win1["close_previous"] = "no";
    		tinyMCE.openWindow(win1, {
						        window : win,
      							input : field_name,
      							resizable : "yes",
      							scrollbars : "yes",
      							inline : "yes"
    		});
    		return false;
		// Insert new URL, this would normaly be done in a popup
		// win.document.forms[0].elements[field_name].value = "someurl.htm";
	}
	<!-- /TinyMCE -->
	
	function ShowNewGroup(nr) {
		current = document.getElementById(nr).style.display = 'block';
		document.getElementById(nr).style.display = current;
	}

	function HideNewGroup(nr) {
		current = document.getElementById(nr).style.display = 'none';
		document.getElementById(nr).style.display = current;
	}

	function ShowNewId() {
		var val = document.editform.id.value;
	
		if(val == "__NEW__") {
			ShowNewGroup('shownewid');
		}
		else {
			HideNewGroup('shownewid');
		}
	}

</script>
MCE;

?>