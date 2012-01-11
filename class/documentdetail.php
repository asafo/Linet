<?php
include_once 'class/fields.php';
class documentDetail extends fields{
	//public $arr;
	//private $_table;
	//private $_prefix;
	
	public function newDetial(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
		
		if (isset($array['num'])){
			$array['id']=maxSql(array("prefix"=>$this->_prefix,"num"=>$array['num']),"id", $this->_table);
			$this->id=$array['id'];
				if (inseretSql($array,$this->_table))
					return true;
				else
					return false;
		}
	}
	public function transaction($tnum,$transtype,$docnum,$company,$issue_date,$doctype,$refnum){
		//print "we r in";
		$acct = GetAccountFromCatNum($this->cat_num);
		if($acct == 0) {
			$l = _("Income account not defined");
			ErrorReport("$l");
			exit;
		}
		$np = $this->price;
		if($doctype == DOC_CREDIT)
			$np *= -1.0;				
		$tnum = Transaction($tnum, $transtype, $acct, $docnum, $refnum, $issue_date, $company, $np);
	}
	public function getDetials(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$arr;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach ($list as $row){
				$bla=new documentDetail;
				foreach($row as $key=>$value)
					$bla->{$key}= $value;
				$arr[]=$bla;
			}
			return $arr;
		}
		return false;
	}
	public function updateDetials($array){
		//rellay ugly need some work in th nir fuetre
		//if (!is_null($this->getItem($array['num'])))
		//	return updateSql($cond,$array,$this->_table);
		$a=new documentDetail();
		$a->num=$this->num;
		if ($a->deleteDetials()){
			foreach ($array as $detial){
				//$a=new documentDetail;
				if (!$detial->newDetial()) return false;
			}
			return true;
		}
		return false;
	}
	public function deleteDetials(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $table;
		global $prefix;
		$this->_table = $table["docdetails"];
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>