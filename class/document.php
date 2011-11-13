<?php
include('class/documentdetail.php');
include('class/receiptdetail.php');
class document{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function __construct($doctype=0){
		global $table;
		global $prefix;
		$this->_table = $table["docs"];
		$this->_prefix = $prefix;
		
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		$this->doctype = $doctype;
		if (DOC_RECEIPT!=$doctype){
			$a=new documentDetail;
			$this->docdetials[0]=$a;
		}
		
		if (($doctype==DOC_RECEIPT) || ($doctype==DOC_INVRCPT)){
			$a=new receiptDetail;
			$this->rcptdetials[0]=$a;
		}
		return $this;
	}
	
	public function newDocument($array){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
		$newnum=maxSql(array(1=>1),'num',$this->_table);
		$newdoc_num=maxSql(array('prefix'=>$this->_prefix,'doctype'=>$array['doctype']),'docnum',$this->_table);
		if (isset($array['docdetials'])){
			$docdetiales=$array['docdetials'];
			unset($array['docdetials']);
		}
		if (isset($array['rcptdetials'])){
			$rcptdetiales=$array['rcptdetials'];
			unset($array['rcptdetials']);
		}
		if (isset($newnum) && isset($newdoc_num)){
			$array['num']=$newnum;
			$array['docnum']=$newdoc_num;// need to chek if invoice insert transrecpts
			if (isset($array['company'])){
				if (isset($docdetiales))
					foreach ($docdetiales as $value) {
							//$b=new documentDetail;
							//$value['num']=$newnum;
							$value->num=$newnum;
							$value->prefix=$this->_prefix;
							if (!$value->newDetial()) return false;
						}
				if (isset($rcptdetiales))
					foreach ($rcptdetiales as $value) {
						//$b=new documentDetail;
						//$value['num']=$newnum;
						$value->refnum=$newnum;
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
		if ($a->getDocument())
			if (deleteSql($cond,$this->_table)){
				if ($this->doctype==DOC_RECEIPT){
					$c= new receiptDetail();
					$c->num=$this->num;
					return $c->deleteDetials($id);
				}
				if ($this->doctype==DOC_INVRCPT){
					$c= new receiptDetail();
					$c->num=$this->num;
					$c->deleteDetials($id);
				}
				$b=new documentDetail;
				$b->num=$this->num;
				return $b->deleteDetials($id);
				
			}
		return false;
	}
	public function updateDocument(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		unset($array['docdetials']);
		unset($array['rcptdetials']);
		if (isset($array['num'])) {
			$cond['num']=$array['num'];
			$cond['prefix']=$this->_prefix;
			$array['prefix']=$this->_prefix;
			$a= new document;
			$a->num=$this->num;
			//print_r($a);
			if ($a->getDocument()){
				if (updateSql($cond,$array,$this->_table)){
					if ($this->doctype==DOC_RECEIPT)
						return $a->rcptdetials[0]->updateDetials($this->rcptdetials);
					if ($this->doctype==DOC_INVRCPT)
						$a->rcptdetials[0]->updateDetials($this->rcptdetials);
					return $a->docdetials[0]->updateDetials($this->docdetials);
				}
			}
		}
		return false;
	}
	public function getDocument(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach($list[0] as $key=>$value)
				$this->{$key}= $value;
			if (DOC_RECEIPT!=$this->doctype){
				$b = new documentDetail;
				$b->num=$this->num;
				$this->docdetials=$b->getDetials();
			}
			if ((DOC_RECEIPT==$this->doctype) ||(DOC_INVRCPT==$this->doctype)){
				$b = new receiptDetail;
				$b->refnum=$this->num;
				$this->rcptdetials=$b->getDetials();	
			}
			//if ($b->getDetials()){
			return true;	
			//}
		}	
		return false;
	}
	public function addDocItem(){
		$a=new documentDetail;
		$this->docdetials[]=$a;
		return true;
	}
	public function removeDocItem($id){
		if (isset($this->docdetials[$id])){
			unset($this->docdetials[$id]);
			return true;
		}
		return false;
	}
	public function addRcptItem(){
		$a=new receiptDetail();
		$this->rcptdetials[]=$a;
		return true;
	}
	public function removeRcptItem($id){
		if (isset($this->rcptdetials[$id])){
			unset($this->rcptdetials[$id]);
			return true;
		}
		return false;
	}
}
?>