

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




 
function removeElement(divNum) {
	var d = document.getElementById('docdet');
	var olddiv = document.getElementById(divNum);
	d.removeChild(olddiv);
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
