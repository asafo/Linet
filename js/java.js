

var RandomIDToUse = 1000;
function AddActionsToTable(  ){
	$(" table:not(.noExcel)").each( function(i){
		var TableID = this.id ;
		if ( $(this).data('ToolBarAdded') === undefined) {
			$(this).data('ToolBarAdded',1);
			var TableName = this.id ;
			if ( this.id == '' || this.id === undefined ){
				TableName = "MyRandomID" + RandomIDToUse ;
				$(this).attr('id',TableName);
				RandomIDToUse++ ;
			}
			var FormID = 'form' + TableName ;
			var SavetoID = 'saveto' + TableName ;
			var DataToDisplayName = "datatodisplay" + TableName ;
			var SaveToDisk =
				"<div class='TableToolBar'>" +
				"<form action='/reports/SaveData/SaveToExcel.php' method='post' target='_blank' id='" + FormID + "'" +
				"onsubmit='$(\".DataToDisplay\", this ).val( $(\"<div>\").append( $(\"#" + TableName + "\").eq(0).clone() ).html() )'>" +
				"<input type='hidden' id='" + DataToDisplayName + "' name='DataToDisplay' class='DataToDisplay' />" +
				"<input type='hidden' id='" + SavetoID + "' name='SaveTo' val=\" />" +
				"</form>" +
				"<input  type='image' src='/images/icons/page_excel.png' width='16? height='16? alt='Save to Excel' title='Save to Excel'" +
				" onclick='$(\"input:checked\").attr(\"checked\",true); $(\"#" + SavetoID + "\").val(\"Excel\"); $(\"#" + FormID + "\").submit();' />" +
				"&nbsp;&nbsp;" +
				"<input  type='image' src='/images/icons/doc_table.png' width='16? height='16? alt='Save to HTML' title='Save to HTML'" +
				" onclick='$(\"input:checked\").attr(\"checked\",true); $(\"#" + SavetoID + "\").val(\"HTML\"); $(\"#" + FormID + "\").submit();' />" +
				"&nbsp;&nbsp;" +
				"<input  type='image' src='/images/icons/doc_pdf.png' width='16? height='16? alt='Save to PDF' title='Save to PDF'" +
				" onclick='$(\"input:checked\").attr(\"checked\",true); $(\"#" + SavetoID + "\").val(\"PDF\"); $(\"#" + FormID + "\").submit();' />" +
				"</div>" ;
			$(this).after( SaveToDisk ) ;
		}
	} ) ;
}

function addDatePicker(name,value){
		$.datepicker.setDefaults( $.datepicker.regional["he"] );
		$(name).datepicker({showButtonPanel: true,showOtherMonths: true,selectOtherMonths: true,changeMonth: true,changeYear: true});
		$(name).datepicker( "option", "dateFormat", "dd-mm-yy" );
		$(name).val(value);
}

function createNumBox(name,num,amnt){
	var str;
	str='<a href="javascript:;" onClick="qtyAdd(\''+name+'\','+num+','+amnt+');" class="btnAddQty"></a>';
	str+='<a href="javascript:;" onClick="qtySub(\''+name+'\','+num+','+amnt+');" class="btnSubQty"></a>';
	return str;
}

function qtyAdd(name,num,amnt){
	//$(name+num).attr(value, value+1);
	var a=name+num;
	var d =document.getElementById(a);
	d.value=(d.value)-1+amnt+1;
	CalcPrice(num);
}
function qtySub(name,num,amnt){
	//$(name+num).attr('value', 1);
	var a=name+num;
	var d =document.getElementById(a);
	d.value=(d.value)-amnt;
	CalcPrice(num);
}


function sendForm(formid,url1,papa){
	   $.ajax({
		   type: "POST",
		   url: url1,
		   data: $("#"+formid).serialize(),
		   dataType:'html'
		 }).done(function( msg ) {
			 $('#'+papa).html(msg);
		 });
}

function submitFormy(formy,url1){
	 $("#"+formy).validate({
		   submitHandler: function(form) {
			   $.ajax({
				   type: "POST",
				   url: url1,
				   data: $("#"+formy).serialize(),
				   dataType:'html'
				 }).done(function( msg ) {
				   window.close();
				 });
		   }
		});
	}

//form saver data loader
function setCookie( name, value, expires, path, domain, secure ) {
	var today = new Date();
	today.setTime( today.getTime() );
	if ( expires ) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name+"="+escape( value ) +
	( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) +
	( ( path ) ? ";path=" + path : "" ) +
	( ( domain ) ? ";domain=" + domain : "" ) +
	( ( secure ) ? ";secure" : "" );
}

$(document).ready(function(){
	$(".tablesorter").tablesorter();
	
	var elements = $('.date');
	for (var i=0; i<elements.length; i++) {
		//alert(elements[i].name);
	    var id=elements[i].id;
	    var value=$('#'+id).val();
		$.datepicker.setDefaults( $.datepicker.regional["he"] );
		$('#'+id).datepicker({showButtonPanel: true,showOtherMonths: true,selectOtherMonths: true,changeMonth: true,changeYear: true});
		$('#'+id).datepicker( "option", "dateFormat", "dd-mm-yy" );
		$('#'+id).val(value);
		$('#'+id).after('<img src="img/icon_cel.png" width="18" onclick="$(\'#'+id+'\').focus()"/>');
		
		
		 
			
	}
	$(".valform").validate();
});