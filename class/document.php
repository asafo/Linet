<?
require_once('/class/documentdetail.php');
class document{
	public $arr;
	private $_table='';
	private $_prefix;
	
	public function __construct(){
		global $docstbl;
		global $prefix;
		$this->_table = $docstbl;
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		foreach ($values as $value) $this->arr[$value['Field']]='';
		$this->arr['docdetials'][0]=new documentDetial;
		return $this;
	}
	public function newDocument($array){
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array(),'num',$this->_table);
		$newdoc_num=maxSql(array('prefix'=>$prefix,'doctype'=>$array['doctype']),'num',$this->_table);
		$docdetiales=$this->arr['docdetials'];
		unset($this->arr['docdetials']);
		if (isset($newnum) && isset($newdoc_num)){
			$array['num']=$newnum;
			$array['docnum']=$newdoc_num;
			if (isset($array['name']))
					if (inseretSql($array,$this->_table))
						foreach ($docdetiales as $value) inseretSql($value,$this->_table);
						return $newdoc_num;
					else
						return $false;
		}
	}
}