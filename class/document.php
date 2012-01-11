<?php
include_once 'class/fields.php';
include('class/documentdetail.php');
include('class/receiptdetail.php');
class document extends fields{
	//public $arr;
	//private $_table;
	//private $_prefix;
	
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
	
	public function newDocument(){
		global $companiestbl;
		$newnum=maxSql(array('prefix'=>$this->_prefix),'num',$this->_table);
		$newdoc_num=maxSql(array('prefix'=>$this->_prefix,'doctype'=>$this->doctype),'docnum',$this->_table);
		$minDocnum=selectSql(array('prefix'=>$this->_prefix), $companiestbl,array('num'.$this->doctype));
		$minDocnum=$minDocnum[0]['num'.$this->doctype];
		//print(";$minDocnum;");
		///print_r($minDocnum);
		if($newdoc_num<$minDocnum)
			$newdoc_num=$minDocnum+1;
		$this->prefix=$this->_prefix;
		$this->num=$newnum;
		//if($this->docnum=='')
			$this->docnum=$newdoc_num;
		if($this->issue_date=='')$this->issue_date=date('d-m-Y');
		if($this->due_date=='')$this->due_date=date('d-m-Y');
		$array=get_object_vars($this);
		$array['issue_date']=date("Y-m-d",strtotime($array['issue_date']));
		$array['due_date']=date("Y-m-d",strtotime($array['due_date']));
		unset($array['_table']);
		unset($array['_prefix']);
	
		if (isset($array['docdetials'])){
			$docdetiales=$array['docdetials'];
			unset($array['docdetials']);
		}
		if (isset($array['rcptdetials'])){
			$rcptdetiales=$array['rcptdetials'];
			unset($array['rcptdetials']);
		}
		if (isset($newnum) && isset($newdoc_num)){
			// need to chek if invoice insert transrecpts
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
	public function transaction(){
		global $TransType;
		$transtype=$TransType[$this->doctype];
		$tnum = 0;
		if(($this->doctype == DOC_INVOICE) || ($this->doctype == DOC_CREDIT) || ($this->doctype== DOC_PROFORMA) || ($this->doctype== DOC_INVRCPT)) {
			/* Write transactions */
			if(($this->doctype == DOC_INVOICE) ||($this->doctype==DOC_PROFORMA)||($this->doctype==DOC_INVRCPT))
				$t = $this->total * -1.0;
			else
				$t = $this->total;
			$tnum = Transaction($tnum, $transtype, $this->account, $this->docnum, $this->refnum, $this->issue_date, $this->company, $t);
			if($this->doctype == DOC_CREDIT)
				$t = $this->vat * -1.0;
			else
				$t = $this->vat;
			$tnum = Transaction($tnum, $transtype, SELLVAT, $this->docnum, $this->refnum, $this->issue_date, $this->company, $t);
			$i = 0;
			$sum = 0.0;
			
			foreach($this->docdetials as $item){
				//print_r($item);
					$item->transaction($tnum,$transtype,$this->docnum,$this->company,$this->issue_date,$this->doctype,$this->refnum);//getfrom catnumber
						//	transaction($tnum,$transtype,$this->docnum,$this->company,$this->issue_date,$this->doctype,$this->refnum,$account)
					//	$itemquntfiy
					$np = $item->price;
					$sum += $np;
					$i++;
			}
			$r = ($sum + $this->vat) - $this->total;//(no vat)
			$r *= -1;
			// print "sum: $sum, vat: $vat, total: $total, r: $r<BR>\n";
			if($r) {
				if($this->doctype == DOC_CREDIT)
					$r *= -1.0;
				$tnum = Transaction($tnum, $transtype, ROUNDING, $this->docnum, $this->refnum, $this->issue_date, $this->company, $r);
			}
			//end reg doc detial:
		}
		if(($this->doctype==DOC_RECEIPT) || ($this->doctype==DOC_INVRCPT)){			/* now get cheques data */
			$cheques_sum = 0.0;
			$tnum = Transaction($tnum, $transtype, CUSTTAX, $this->docnum, '', $this->issue_date, '', $this->src_tax * -1.0);//reg source tax
			$tnum = Transaction($tnum, $transtype, $this->account, $this->docnum, '', $this->issue_date, '', $this->src_tax);
			foreach($this->rcptdetials as $item){
				$cheques_sum += $item->sum;
				$item->transaction($this->docnum,$this->account,$this->issue_date,$transtype,$tnum);
			}
		} //end recipt data
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