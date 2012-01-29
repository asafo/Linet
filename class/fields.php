<?php 
class fields{
	protected $_table='';
	protected $_prefix='';
	public function Edit(){
		return $this->getFields($this->_table);
	}
	/*public function __construct(){
		global $prefix;
		$this->_prefix = $prefix;
	}*/
	
	public function View(){
		return $this->getFields($this->_table,true);
	}
	private function getFields($tablename,$display=false){
		$buffer=selectSql(array("tablename"=>$tablename), "fields",null,null,array("sort"));
		//print_r($buffer);
		$id=uniqid();
		if((get_class($this)=='documentDetail')||(get_class($this)=='receiptDetail'))
			$son=true;
		else 	
			$son=false;
		$data=array();
		foreach($buffer as $field){
			$value=0;
			$data[$field['name']]=$this->parseFieldData($field['name'],$field['data'],$field['desc'],$this->$field['name'],$display,$id,$son);
		}
		if(get_class($this)=='documentDetail')
			$data['svat']= "<input type=\"hidden\" id=\"SVAT$id\" name=\"svat\" value=\"100\" />";
		return $data;
	}
	
	private function parseFieldData($fieldname,$fielddata,$fieldesc,$value,$display,$id,$son){
		$fieldesc=""._($fieldesc)."";
		$split = explode("(", $fielddata);
   		if(isset($split[1])){
   			$fielddata=$split[0];
   			$split[1]=explode(",", $split[1]);
   			$split[1][0]=explode(".", $split[1][0]);
   			$ftablename=$split[1][0][0];
   			$ffieldname=$split[1][0][1];
   			$index=$split[1][1];
   			$index=str_replace(")","",$index);
   		}
   		$nid=$fieldname;
		if($son){
			$fieldname.="[]";
		}
		if(!$display)
			$readonly='';
		else{
			$readonly="readonly";
			return $this->showFieldData($fieldname,$fielddata,$fieldesc,$value,$readonly,$id);
		}
		switch ($fielddata){
			case "AUTO":
			case "PREFIX":
			case "HIDDEN":
				$field="<input type=\"hidden\" $readonly id=\"$nid$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" />";
				break;	
			case "AUTOCOMPLETE":
				$field="$fieldesc: <input type=\"text\" $readonly id=\"$nid$id\" class=\"required number\" name=\"$fieldname\" onblur=\"ChangeMe('$nid','$id')\" value=\"$value\" />\n";
				$field.="<a href=\"javascript:;\" onclick=\"\$('#$nid$id').autocomplete('search', $('#$nid$id').val());\">"._("Search")."</a>";
				if($nid=='doctype')//not  good
					$nid=$this->doctype;
				$field.='<script type="text/javascript">$(document).ready(function() {$("#'.$nid.$id.'").autocomplete({source: \'index.php?action=lister&data='.$ftablename.'&type='.${nid}.'&jsoncallback=?\'});});</script>';
				break;
			case "AUTOSELECT":
				global $withoutprefix;
				if($ftablename=='accounts'){
					if(get_class($this)=='receiptDetail')
						$query = "SELECT $index,$ffieldname FROM $ftablename WHERE (prefix='$this->_prefix') AND (type='".BANKS."')";//adam:
					else
						$query = "SELECT $index,$ffieldname FROM $ftablename WHERE (prefix='$this->_prefix') AND (type='".INCOME."')";//adam:
				}elseif(in_array($ftablename,$withoutprefix))//the selector is still not good needs screening effact
					$query = "SELECT $index,$ffieldname FROM $ftablename";
				else
					$query = "SELECT $index,$ffieldname FROM $ftablename WHERE (prefix='$this->_prefix')";//limit with prefix
				//print $query.";".$index;
				$result = DoQuery($query, __LINE__);
				//print "bla:$index";
				if($readonly!="")
					$readonly="disabled";
				$field.= "<label for=\"$nid$id\">$fieldesc :</label><select class=\"required\" $readonly id=\"$nid$id\" name=\"$fieldname\" onchange=\"ChangeMe('$nid','$id')\">\n";
				while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$num = $line[$index];
					$sign = str_replace("\n","",$line[$ffieldname]);
					$field.= "<option value=\"$num\"";
					if(($num == "$value")&&($num!=''))
						$field.= " selected";
					$field.= ">$sign</option>\n";
				}
				$field.= "</select>\n";
				break;
			case "DATE":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" $readonly id=\"$nid$id\" class=\"\"  name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" />";
				break;
			case "NUM":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" class=\"number\" $readonly id=\"$nid$id\" name=\"$fieldname\" onblur=\"ChangeMe('$nid','$id')\" value=\"".htmlspecialchars($value)."\" />";
				break;
			case "ADDRESS":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" $readonly id=\"$nid$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" />";
				break;
			case "WEB":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" class=\"url\" $readonly id=\"$nid$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" />";
				break;	
			case "EMAIL":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" class=\"email\" $readonly id=\"$nid$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" class=\"inputemail\" />";
				break;
			case "PHONE":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" class=\"number\" $readonly id=\"$nid$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" class=\"inputphone\" />";
				break;
			case "PRICE":
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" $readonly id=\"$nid$id\" name=\"$fieldname\" onblur=\"ChangeMe('$nid','$id')\" value=\"".htmlspecialchars($value)."\" class=\"inputprice\" />";
				break;
			case "TEXT":
			default:
				$field="<label for=\"$nid$id\">$fieldesc :</label><input type=\"text\" $readonly id=\"$nid$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" class=\"inputtext\"  />";
				break;
				//autocomplete(tabele.fieldname,index)
				//autoselect(table.fieldname,index)
		}
		
		return $field;
	}
	
	private function showFieldData($fieldname,$fielddata,$fieldesc,$value,$readonly,$id){
		
		switch ($fielddata){
			case "AUTO":
			case "PREFIX":
			case "HIDDEN":
				$field="<input type=\"hidden\" $readonly id=\"$fieldname$id\" name=\"$fieldname\" value=\"".htmlspecialchars($value)."\" />";
				break;	
			case "AUTOCOMPLETE":
				$field="$fieldesc: <input type=\"text\" $readonly id=\"$fieldname$id\" class=\"\" name=\"$fieldname\" onblur=\"ChangeMe('$fieldname','$id')\" value=\"$value\" />\n";
				if($ffieldname=='doctype')//not  good
					$ffieldname=$this->doctype;
				$field.='<script type="text/javascript">
				var a =$("#'.$fieldname.$id.'").val();
				$(document).ready(function() {$("#'.$fieldname.$id.'").autocomplete({source: \'index.php?action=lister&data='.$ftablename.'&type='.${ffieldname}.'&jsoncallback=?\'});});
				$("#'.$fieldname.$id.'").val(a);
				
				</script>';
				break;
			/*case "AUTOSELECT":	
				$query = "SELECT * FROM $ftablename";//limit with prefix
				$result = DoQuery($query, __LINE__);
				if($readonly!="")
					$readonly="disabled";
				$field.= "$fieldesc: <select $readonly id=\"$fieldname$id\" name=\"$fieldname\">\n";
				while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$num = $line[$index];
					$sign = $line[$ffieldname];
					$field.= "<option value=\"$num\"";
					if($num == $value)
						$field.= " selected";
					$field.= ">$sign</option>\n";
				}
				$field.= "</select>\n";
				break;*/
			case "DATE":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value date\" id=\"$fieldname$id\" >".htmlspecialchars($value)."</span>";
				break;
			case "NUM":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value num\" id=\"$fieldname$id\" >".htmlspecialchars($value)."</span>";
				break;
			case "ADDRESS":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value address\" id=\"$fieldname$id\" >".htmlspecialchars($value)."</span>";
				break;
			case "WEB":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value web\" id=\"$fieldname$id\" ><a href='".htmlspecialchars($value)."'>".htmlspecialchars($value)."</a></span>";
				break;	
			case "EMAIL":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value email\" id=\"$fieldname$id\" ><a href='mailto://".htmlspecialchars($value)."'>".htmlspecialchars($value)."</a></span>";
				break;
			case "PHONE":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value phone\" id=\"$fieldname$id\" ><a href='tel:".htmlspecialchars($value)."'>".htmlspecialchars($value)."</a></span>";
				break;
			
			case "PRICE":
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value price\" id=\"$fieldname$id\" >".htmlspecialchars($value)."</span>";
				break;
			case "TEXT":
			default:
				$field="<label for=\"$fieldname$id\">$fieldesc :</label><span class=\"value text\" id=\"$fieldname$id\" >".htmlspecialchars($value)."</span>";
				break;
				//autocomplete(tabele.fieldname,index)
				//autoselect(table.fieldname,index)
		}
		
		return $field;
		
	}
}



?>