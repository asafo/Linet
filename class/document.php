<?
include('class/documentdetail.php');
class document{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function __construct(){
		global $docstbl;
		global $prefix;
		$this->_table = $docstbl;
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		$a=new documentDetail;
		$this->docdetials[0]=$a;
		return $this;
	}
	
	public function newDocument($array){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array(1=>1),'num',$this->_table);
		$newdoc_num=maxSql(array('prefix'=>$this->_prefix,'doctype'=>$array['doctype']),'docnum',$this->_table);
		$docdetiales=$array['docdetials'];
		unset($array['docdetials']);
		if (isset($newnum) && isset($newdoc_num)){
			$array['num']=$newnum;
			$array['docnum']=$newdoc_num;// need to chek if invoice insert transrecpts
			if (isset($array['company'])){
				foreach ($docdetiales as $value) {
						//$b=new documentDetail;
						//$value['num']=$newnum;
						$value->num=$newnum;
						$value->prefix=$this->_prefix;
						if (!$value->newDetial()) return false;
					}
				if (inseretSql($array,$this->_table))
					return $newnum;
			}
		}
		return false;
	} 
	public function deleteDocument($id){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$a=new document;
		$a->num=$this->num;
		$b=new documentDetail;
		$b->num=$this->num;
		if ($a->getDocument())
			if (deleteSql($cond,$this->_table))
				return $b->deleteDetials($id);
		return false;
	}
	public function updateDocument(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		unset($array['docdetials']);
		if (isset($array['num'])) {
			$cond['num']=$array['num'];
			$cond['prefix']=$this->_prefix;
			$array['prefix']=$this->_prefix;
			$a= new document;
			$a->num=$this->num;
			//print_r($a);
			if ($a->getDocument()){
				if (updateSql($cond,$array,$this->_table))
					return $a->docdetials[0]->updateDetials($this->docdetials);
			}
		}
		return false;
	}
	public function getDocument(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$list=selectSql($cond,$this->_table);
		if ($list){
			$b = new documentDetail;
			$b->num=$this->num;
			$this->docdetials=$b->getDetials();
			//if ($b->getDetials()){
				foreach($list[0] as $key=>$value)
					$this->{$key}= $value;
				return true;
			//}
		}	
		return false;
	}
	public function addItem(){
		$a=new documentDetail;
		$this->docdetials[]=$a;
		return true;
	}
	public function removeItem($id){
		if (isset($this->docdetials[$id])){
			unset($this->docdetials[$id]);
			return true;
		}
		return false;
	}
}
?>