<?php

class Employee extends BaseEmployee
{
	public function __toString(){
		return $this->getFirstName() .' '.$this->getLastName();
	}
	
	public function getVatNo()
	{
		if ($company = $this->getCompany())
			return $company->getVatNo();
		else
			0;
	}
        public function getSimType(){
            $simTypeTitle ="";
            $cst = new Criteria();
            $cst->add(SimTypesPeer::ID,$this->getSimTypeId());
            $simTypes = SimTypesPeer::doSelectOne($cst);
              if(SimTypesPeer::doCount($cst)>0) $simTypeTitle = $simTypes->getTitle();
            return $simTypeTitle;
        }
       public function save(PropelPDO $con = null)
	{
		
	    if (($this->isModified() && $this->isColumnModified(EmployeePeer::PASSWORD)) ||
	    	($this->isNew() && $this->getPassword())
	    	)
	    {
	    	$this->setPassword(sha1($this->getPassword()));
	    }
	    
	    parent::save($con);
	} 
}
