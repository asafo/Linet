function addItem(a,b) {
      var r  = document.createElement('tr');
      var ca = document.createElement('td');
      var cb = document.createElement('td');
      var ta = document.createTextNode(a);
      var tb = document.createTextNode(b);
      var t  = document.getElementById('docdet');


      ca.appendChild(ta);
      cb.appendChild(tb);

      r.appendChild(ca);
      r.appendChild(cb);

      t.tBodies(0).appendChild(r);
    }
function addEvent(last) {
	var ni = document.getElementById('docdet');
	var numi = document.getElementById('theValue');
	//var num = (document.getElementById("theValue").value -1)+ 2;
	
	var num=numi.value
	numi.value ++;//= (numi.value)+(1);
	var trIdName = "my"+num+"Tr";
	var r  = document.createElement('tr');
	var ca = document.createElement('td');
	var cb = document.createElement('td');
	var cc = document.createElement('td');
	var cd = document.createElement('td');
	var ce = document.createElement('td');
	var cf = document.createElement('td');
	
	r.setAttribute("id",trIdName);
	
	ca.innerHTML = "<input type=\"text\" id=\"AC"+num+"\" class=\"cat_num\" name=\"cat_num[]\" onblur=\"SetPartDetails("+num+")\" >\n";
	cb.innerHTML = "<input type=\"text\" id=\"DESC"+num+"\" class=\"description\" name=\"description[]\" size=\"45\">";
	cc.innerHTML ="<input type=\"text\" id=\"QTY"+num+"\" class=\"qty\" name=\"qty[]\" size=\"6\" onblur=\"CalcPrice("+num+")\">";
	cd.innerHTML ="<input type=\"text\" id=\"UNT"+num+"\" class=\"unit_price\" name=\"unit_price[]\" size=\"10\" onblur=\"CalcPrice("+num+")\">";
	ce.innerHTML ="<select class=\"currency\" id=\"CUR"+num+"\" name=\"currency[]\"><option value=\"0\">NIS</option></select>";
	cf.innerHTML ="<input type=\"text\" id=\"PRICE"+num+"\" class=\"price\" name=\"price[]\" size=\"10\"><a href=\"javascript:;\" onclick=\"removeElement(\'"+trIdName+"\')\">Remove</a>";

	r.appendChild(ca);
	r.appendChild(cb);
	r.appendChild(cc);
	r.appendChild(cd);
	r.appendChild(ce);
	r.appendChild(cf);
	
	ni.appendChild(r);
	$( "#AC"+num ).autocomplete({source: "index.php?action=lister&data=items",});
}
 
function removeElement(divNum) {
	var d = document.getElementById('docdet');
	var olddiv = document.getElementById(divNum);
	d.removeChild(olddiv);
}

function addDatePicker(name,value){
		$.datepicker.setDefaults( $.datepicker.regional["he"] );
		$(name).datepicker({showButtonPanel: true,showOtherMonths: true,selectOtherMonths: true,changeMonth: true,changeYear: true,});
		$(name).datepicker( "option", "dateFormat", "dd-mm-yy" );
		$(name).val(value);
}