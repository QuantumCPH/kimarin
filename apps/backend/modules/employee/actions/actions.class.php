<?php

require_once(sfConfig::get('sf_lib_dir') . '/company_employe_activation.class.php');
require_once(sfConfig::get('sf_lib_dir') . '/emailLib.php');

/**
 * employee actions.
 *
 * @package    zapnacrm
 * @subpackage employee
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 5125 2007-09-16 00:53:55Z dwhittle $
 */
class employeeActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $c = new Criteria();
        $companyid = $request->getParameter('company_id');
        $this->companyval = $companyid;
        if (isset($companyid) && $companyid != '') {
            $c->addAnd(EmployeePeer::COMPANY_ID, $companyid);
        }
        $c->add(EmployeePeer::STATUS_ID, 3);
        $this->employees = EmployeePeer::doSelect($c);
    }

    public function executeEdit(sfWebRequest $request) {

        $e = new Criteria();
        $e->add(EmployeePeer::ID, $request->getParameter('id'));
        $this->employee = EmployeePeer::doSelectOne($e);

        $cst = new Criteria();
        $this->simtypes = SimTypesPeer::doSelect($cst);

        $c = new Criteria();
        $this->companys = CompanyPeer::doSelect($c);

        $pr = new Criteria();
        $pr = new Criteria();
        $pr->add(ProductPeer::IS_IN_B2B, 1);
        $this->products = ProductPeer::doSelect($pr);
    }

    protected function addFiltersCriteria($c) {

        if (isset($this->filters['vat_no']) && $this->filters['vat_no'] !== '') {
            $c->add(CompanyPeer::VAT_NO, strtr($this->filters['vat_no'], '*', '%'), Criteria::LIKE);
            $c->addJoin(CompanyPeer::ID, EmployeePeer::COMPANY_ID);

            $this->filters['company_id'] = '';
        } else {
            parent::addFiltersCriteria($c);
        }

        //$c->add(CompanyPeer::VAT_NO, strtr($this->filters['vat_no'], '*', '%'), Criteria::LIKE);
        //$c->addJoin(CompanyPeer::ID, EmployeePeer::COMPANY_ID);
        //$tmp = $this->filters['vat_no'];
    }

    public function executeView($request) {
        $ComtelintaObj = new CompanyEmployeActivation();
        $this->employee = EmployeePeer::retrieveByPK($request->getParameter('id'));
        
       
        $ct = new Criteria();
        $ct->add(TelintaAccountsPeer::PARENT_ID, $this->employee->getId());
        $ct->addAnd(TelintaAccountsPeer::PARENT_TABLE, 'employee');
        $ct->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccounts = TelintaAccountsPeer::doSelect($ct);
        $balance = 0.0;
        foreach($telintaAccounts as $telintaAccount){
            $account_info = $ComtelintaObj->getAccountInfo($telintaAccount->getIAccount());
            $balance += $account_info->account_info->balance;

        }
        $this->balance = $balance;
    }

    public function executeAppCode($request) {
        $this->employee = EmployeePeer::retrieveByPK($request->getParameter('id'));


        $c = new Criteria();
        $c->add(EmployeePeer::APP_CODE, NULL);
        $employees = EmployeePeer::doSelect($c);

        foreach ($employees as $employee) {

            $emplyid = $employee->getId();
            $emplycompanyid = $employee->getCompanyId();

            $appcode = $emplyid . "" . $emplycompanyid;

            $applen = strlen($appcode);
            if (isset($applen) && $applen == 2) {

                $appcode = "00" . $appcode;
            }
            if (isset($applen) && $applen == 3) {

                $appcode = "0" . $appcode;
            }

            //   echo " <br/>".$appcode;
            //  $employee->setId($emplyid);
            $employee->setAppCode($appcode);


            $employee->save();
        }

        return $this->redirect('employee/index');
    }

    public function executeAdd($request) {

        $this->companyval = $request->getParameter('company_id');

        $c = new Criteria();
        $this->companys = CompanyPeer::doSelect($c);

        $cst = new Criteria();
        $this->simtypes = SimTypesPeer::doSelect($cst);

        $pr = new Criteria();
        $pr->add(ProductPeer::IS_IN_B2B, 1);
        $this->products = ProductPeer::doSelect($pr);
    }

    public function executeSaveEmployee($request) {

        $ComtelintaObj = new CompanyEmployeActivation();
        //$contrymobilenumber = $request->getParameter('country_code') . $request->getParameter('mobile_number');
        //$employeMobileNumber=$contrymobilenumber;


        if (substr($request->getParameter('mobile_number'), 0, 1) == 0) {
            $mobileNo = substr($request->getParameter('mobile_number'), 1);
        } else {
            $mobileNo = $request->getParameter('mobile_number');
        }

        $c = new Criteria();
        $c->addAnd(CompanyPeer::ID, $request->getParameter('company_id'));
        $this->companys = CompanyPeer::doSelectOne($c);
        $companyCVR = $this->companys->getVatNo();
        $countryID = $this->companys->getCountryId();
        $companyCVRNumber = $companyCVR;
        $employee = new Employee();
        $c1 = new Criteria();
        $c1->addAnd(CountryPeer::ID, $countryID);
        $this->country = CountryPeer::doSelectOne($c1);
        $contrymobilenumber = $this->country->getCallingCode() . $mobileNo;
        $employeMobileNumber = $contrymobilenumber;
        $employee->setCompanyId($request->getParameter('company_id'));
        $employee->setFirstName($request->getParameter('first_name'));
        $employee->setLastName($request->getParameter('last_name'));
        $employee->setCountryCode($this->country->getCallingCode());
        $employee->setCountryMobileNumber($contrymobilenumber);
        $employee->setMobileNumber($request->getParameter('mobile_number'));
        $employee->setEmail($request->getParameter('email'));
        $employee->setProductId($request->getParameter('productid'));
        $employee->setSimTypeId($request->getParameter('sim_type_id'));
        $employee->setPlainText($request->getParameter('password'));
        $employee->setPassword($request->getParameter('password'));
        $employee->setComments($request->getParameter('comments'));        
        $employee->setStatusId(sfConfig::get('app_status_new'));
        $employee->save();
        
//        if(!$ComtelintaObj->telintaRegisterEmployeeCB($employeMobileNumber, $this->companys)){
//            $employee->setStatusId(sfConfig::get('app_status_error')); //// error status is 5 defined in backend/config/app.yml
//            $employee->save();
//            $this->getUser()->setFlash('messageError', 'Employee  Call Back account is not registered please check email');
//            $this->redirect('employee/add');
//            die;
//        }
        
        if(!$ComtelintaObj->telintaRegisterEmployeeCT($employee, $employee->getProductId())){
          $employee->setStatusId(sfConfig::get('app_status_error')); //// error status is 5 defined in backend/config/app.yml
            $employee->save();
            $this->getUser()->setFlash('messageError', 'Employee  Call Back account is not registered please check email');
            $this->redirect('employee/add');
            die;  
        }
        if(!$ComtelintaObj->createDialAccount($employee)){
           $employee->setStatusId(sfConfig::get('app_status_error')); //// error status is 5 defined in backend/config/app.yml
            $employee->save();
            $this->getUser()->setFlash('messageError', 'Employee  Dial account is not registered please check email');
            $this->redirect('employee/add');
            die; 
        }
        
        $product = ProductPeer::retrieveByPK($request->getParameter('productid'));
        if ($product->getProductTypeId() == 10) {
            $employee->setUniqueId("Dial" . $employee->getId());
        } elseif ($product->getProductTypeId() == 11) {
            $employee->setUniqueId("app" . $employee->getId());
        }else{
            $employee->setUniqueId($request->getParameter('uniqueid'));
            $employee->save();
            $c = new Criteria();
            $c->add(UniqueIdsPeer::UNIQUE_NUMBER, $employee->getUniqueId());
            $uniqueIdObj = UniqueIdsPeer::doSelectOne($c);
            $uniqueIdObj->setAssignedAt(date("Y-m-d H:i:s"));
            $uniqueIdObj->setStatus(1);
            $uniqueIdObj->save();              
        }
        
        $employee->setStatusId(sfConfig::get('app_status_completed')); //// completed status is 3 defined in backend/config/app.yml
        $employee->save();
        $chrageamount = $product->getRegistrationFee();
        // $chrageamount=$product->getRegistrationFee()+$product->getRegistrationFee()*sfConfig::get('app_vat_percentage');

        if ($chrageamount > 0) {
            $description = "RegFee-" . $employee->getMobileNumber();
            $ComtelintaObj->charge($this->companys, $chrageamount, $description);
        }
        $ct = new Criteria();
        $ct->add(TransactionDescriptionPeer::TRANSACTION_TYPE_ID, 3); //// For registration
        $ct->addDescendingOrderByColumn(TransactionDescriptionPeer::ID);
        $transactionDesc = TransactionDescriptionPeer::doSelectOne($ct);
        $country = CountryPeer::retrieveByPK($this->companys->getCountryId());
        $vat = ($country->getVatPercentage() / 100);
        $vat_amount=$chrageamount * $vat;
        $withvat = $chrageamount + $vat_amount;
        
        $co_transaction = B2BTransactionProcessAdmin::StartTransaction($this->companys, $chrageamount, $transactionDesc,3, 3, 3); //expense type 3 = register
        
//        $transaction = new CompanyTransaction();
//        $transaction->setAmount(-$withvat);
//        $transaction->setCompanyId($request->getParameter('company_id'));
//        $transaction->setExtraRefill(-$chrageamount);
//        $transaction->setTransactionStatusId(3);
//        $transaction->setPaymenttype($transactionDesc->getId()); //Product Registration Fee
//        $transaction->setDescription($transactionDesc->getTitle());
//        $transaction->setVat($vat_amount);
//        $transaction->save();
        TransactionPeer::AssignB2bReceiptNumber($co_transaction);

        $rtype = $request->getParameter('registration_type');
        
        if ($rtype == 1) {
            ////////////////////////////////////////////////

            $this->getbalance = $ComtelintaObj->getBalance($this->companys);
            $c = new Criteria();
            $c->setLimit(1);
            $c->add(SeVoipNumberPeer::IS_ASSIGNED, 0);

            if (SeVoipNumberPeer::doCount($c) < 10) {
                emailLib::sendErrorInTelinta("Resenumber about to Finis", "Resenumbers in the landncall are lest then 10 . ");
            }

            if (!$voip_customer = SeVoipNumberPeer::doSelectOne($c)) {
                emailLib::sendErrorInTelinta("Resenumber Finished", "Resenumbers in the landncall are finished. This error is faced by Employee id: " . $request->getParameter('id'));
                $msg = "Resenummer is not activate";
                //return false;
            } else {
                $voip_customer->setUpdatedAt(date('Y-m-d H:i:s'));
                $voip_customer->setCustomerId($contrymobilenumber);
                $voip_customer->setIsAssigned(1);
                $voip_customer->save();
                //--------------------------Telinta------------------/
                $getvoipInfo = new Criteria();
                $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $contrymobilenumber);
                $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
                if (isset($getvoipInfos)) {
                    $voipnumbers = $getvoipInfos->getNumber();
                    $voipnumbers = substr($voipnumbers, 2);
                    $voip_customer = $getvoipInfos->getCustomerId();
                    //$TelintaMobile = sfConfig::get('app_country_code').$this->customer->getMobileNumber();
                    //This Condtion for if IC Active

                    $getFirstnumberofMobile = substr($contrymobilenumber, 0, 1);     // bcdef
                    if ($getFirstnumberofMobile == 0) {
                        $TelintaMobile = substr($contrymobilenumber, 1);
                    } else {
                        $TelintaMobile = $contrymobilenumber;
                    }

                    $telintaResenummerAccount = $ComtelintaObj->createReseNumberAccount($voipnumbers, $this->companys, $TelintaMobile);
                    if ($telintaResenummerAccount) {
                        $OpeningBalance = 40;
                        $employee->setRegistrationType($request->getParameter('registration_type'));
                        //$resenummerCharge=file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=account&action=manual_charge&name=' . $voipnumbers . '&amount=40&customer='.$companyCVR);
                        $ComtelintaObj->charge($this->companys, $OpeningBalance);
                        $transaction = new CompanyTransaction();
                        $transaction->setAmount(-40);
                        $transaction->setCompanyId($request->getParameter('company_id'));
                        $transaction->setExtraRefill(-40);
                        $transaction->setTransactionStatusId(3);
                        $transaction->setPaymenttype(3); //Resenummer Charge
                        $transaction->setDescription('Resenummer Charge');
                        $transaction->save();
                        TransactionPeer::AssignReceiptNumber($transaction);
                    } else {
                        $getvoipInfos->setUpdatedAt(Null);
                        $getvoipInfos->setCustomerId(Null);
                        $getvoipInfos->setIsAssigned(0);
                        $getvoipInfos->save();
                        $employee->setRegistrationType(0);
                        $msg = "Resenummer is not activate";
                    }
                }
            }
        }
        $this->getUser()->setFlash('messageAdd', 'Employee has been Add Sucessfully ' . (isset($msg) ? "and " . $msg : ''));
        $this->redirect('employee/index?message=add');
    }

    public function executeUpdateEmployee(sfWebRequest $request) {

        $ComtelintaObj = new CompanyEmployeActivation();
        //$contrymobilenumber = $request->getParameter('country_code') . $request->getParameter('mobile_number');
        //$employeMobileNumber=$contrymobilenumber;


        $c = new Criteria();

        $c->add(CompanyPeer::ID, $request->getParameter('company_id'));

        $compny = CompanyPeer::doSelectOne($c);

        $companyCVR = $compny->getVatNo();
        $rtype = $request->getParameter('registration_type');

        $employee = EmployeePeer::retrieveByPk($request->getParameter('id'));
        $contrymobilenumber = $employee->getCountryMobileNumber();

        $c = new Criteria();
        $c->addAnd(CompanyPeer::ID, $employee->getCompanyId());
        $this->companys = CompanyPeer::doSelectOne($c);
        $companyCVR = $this->companys->getVatNo();
        $companyCVRNumber = $companyCVR;

        if ($rtype == 1) {
            ////////////////////////////////////////////////
            $this->getbalance = $ComtelintaObj->getBalance($this->companys);
            if ($this->getbalance > 40) {
                $c = new Criteria();
                $c->setLimit(1);
                $c->add(SeVoipNumberPeer::IS_ASSIGNED, 0);

                if (SeVoipNumberPeer::doCount($c) < 10) {
                    emailLib::sendErrorInTelinta("Resenumber about to Finis", "Resenumbers in the landncall are lest then 10 . ");
                }
                if (!$voip_customer = SeVoipNumberPeer::doSelectOne($c)) {
                    emailLib::sendErrorInTelinta("Resenumber Finished", "Resenumbers in the landncall are finished. This error is faced by Employee id: " . $request->getParameter('id'));
                    $msg = "Resenummer is not activate";
                } else {
                    $voip_customer->setUpdatedAt(date('Y-m-d H:i:s'));
                    $voip_customer->setCustomerId($contrymobilenumber);
                    $voip_customer->setIsAssigned(1);
                    $voip_customer->save();
                    $voip_customer->getNumber();


                    //--------------------------Telinta------------------/
                    $getvoipInfo = new Criteria();
                    $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $contrymobilenumber);
                    $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();

                    $getvoipInfos->getNumber();

                    if (isset($getvoipInfos)) {
                        $voipnumbers = $getvoipInfos->getNumber();
                        $voipnumbers = substr($voipnumbers, 2);
                        $voip_customer = $getvoipInfos->getCustomerId();


                        //$TelintaMobile = sfConfig::get('app_country_code').$this->customer->getMobileNumber();
                        //This Condtion for if IC Active
                        //------------------------------


                        $getFirstnumberofMobile = substr($contrymobilenumber, 0, 1);     // bcdef
                        if ($getFirstnumberofMobile == 0) {
                            $TelintaMobile = substr($contrymobilenumber, 1);
                        } else {
                            $TelintaMobile = $contrymobilenumber;
                        }

                        $telintaResenummerAccount = $ComtelintaObj->createReseNumberAccount($voipnumbers, $this->companys, $TelintaMobile);
                        if ($telintaResenummerAccount) {

                            $OpeningBalance = 40;
                            $employee->setRegistrationType($rtype);
                            //$resenummerCharge=file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=account&action=manual_charge&name=' . $voipnumbers . '&amount=40&customer='.$companyCVR);

                            $ComtelintaObj->charge($this->companys, $OpeningBalance);
                            $transaction = new CompanyTransaction();
                            $transaction->setAmount(-40);
                            $transaction->setCompanyId($employee->getCompanyId());
                            $transaction->setExtraRefill(-40);
                            $transaction->setTransactionStatusId(3);
                            $transaction->setPaymenttype(3); //Resenummer Charge
                            $transaction->setDescription('Resenummer Charge');
                            $transaction->save();
                            TransactionPeer::AssignReceiptNumber($transaction);
                        } else {
                            $getvoipInfos->setUpdatedAt(Null);
                            $getvoipInfos->setCustomerId(Null);
                            $getvoipInfos->setIsAssigned(0);
                            $getvoipInfos->save();
                            $employee->setRegistrationType(0);
                            $msg = "Resenummer is not activate";
                        }
                    }
                }
            } else {

                $msg = "To activate Resenummer Refill account";
            }
        }




        // if($rtype==3){
        // $rtype=1;
        //}
        // $contrymobilenumber = $request->getParameter('country_code') . $request->getParameter('mobile_number');
        //  $employee->setCompanyId($request->getParameter('company_id'));
        $employee->setFirstName($request->getParameter('first_name'));
        $employee->setLastName($request->getParameter('last_name'));
        // $employee->setCountryCode($request->getParameter('country_code'));
        // $employee->setCountryMobileNumber($contrymobilenumber);
        $employee->setMobileNumber($request->getParameter('mobile_number'));
        $employee->setEmail($request->getParameter('email'));
        /*     $employee->setAppCode($request->getParameter('app_code'));
          $employee->setIsAppRegistered($request->getParameter('is_app_registered'));
          $employee->setPassword($request->getParameter('password')); */
        //$employee->setRegistrationType($rtype);
        $employee->setProductId($request->getParameter('productid'));

        //  $employee->setProductPrice($request->getParameter('price'));
        $employee->setComments($request->getParameter('comments'));
        $employee->setDeleted($request->getParameter('deleted'));
        $employee->save();
        $this->getUser()->setFlash('messageEdit', 'Employee has been modified Sucessfully ' . (isset($msg) ? "and " . $msg : ''));
        //$this->message = "employee added successfully";
        $this->redirect('employee/index?message=edit');
        // return sfView::NONE;
    }

    public function executeDel(sfWebRequest $request) {
        $request->checkCSRFProtection();
        $employeeid = $request->getParameter('id');
        $ComtelintaObj = new CompanyEmployeActivation();
        $c = new Criteria();
        $c->add(EmployeePeer::ID, $employeeid);
        $employee = EmployeePeer::doSelectOne($c);
        $registration = $employee->getRegistrationType();
        //$mobileNumber=$employees->getCountryMobileNumber();
        $companyid = $request->getParameter('company_id');
        
         $ct = new Criteria();
        $ct->add(TelintaAccountsPeer::PARENT_ID, $employee->getId());
        $ct->addAnd(TelintaAccountsPeer::PARENT_TABLE, 'employee');
        $ct->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccounts = TelintaAccountsPeer::doSelect($ct);
        foreach($telintaAccounts as $telintaAccount){
            $ComtelintaObj->terminateAccount($telintaAccount);
        }
        $employee->delete();
        $this->getUser()->setFlash('message', 'Employee has been deleted Sucessfully');
        if (isset($companyid) && $companyid != "") {
            $this->redirect('employee/index?company_id=' . $companyid . '&filter=filter');
        } else {
            $this->redirect('employee/index');
        }
    }

    public function executeUsage($request) {
        $this->employee = EmployeePeer::retrieveByPK($request->getParameter('employee_id'));
        $ComtelintaObj = new CompanyEmployeActivation();
        $c = new Criteria();
        $c->addAnd(CompanyPeer::ID, $this->employee->getCompanyId());
        $this->companys = CompanyPeer::doSelectOne($c);

        $tomorrow1 = mktime(0, 0, 0, date("m"), date("d") - 15, date("Y"));
        $fromdate = date("Y-m-d", $tomorrow1);
        $tomorrow = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
        $todate = date("Y-m-d", $tomorrow);

        $this->fromdate = $fromdate;
        $this->todate = $todate;
        
        $fromdate = $fromdate . " 00:00:00";
        $todate = $todate . " 23:59:59";
        
        $mobilenumber = $this->employee->getCountryMobileNumber();
        $ct = new Criteria();
        $ct->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'a' . $mobilenumber);
        $ct->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccount = TelintaAccountsPeer::doSelectOne($ct);
        if ($telintaAccount) {
            $this->callHistory = $ComtelintaObj->getAccountCallHistory($telintaAccount->getIAccount(), $fromdate, $todate);
        } else {
            $this->callHistory = "";
        }


        $cb = new Criteria();
        $cb->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'cb' . $mobilenumber);
        $cb->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccountcb = TelintaAccountsPeer::doSelectOne($cb);
        $this->cntcb = TelintaAccountsPeer::doSelectOne($cb);
        if ($this->cntcb > 0):
            $this->callHistorycb = $ComtelintaObj->getAccountCallHistory($telintaAccountcb->getIAccount(), $fromdate, $todate);
        endif;

        /*

          $getvoipInfo = new Criteria();
          $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $contrymobilenumber);
          $getvoipInfo->addAnd(SeVoipNumberPeer::IS_ASSIGNED, 1);
          $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
          if (isset($getvoipInfos)) {
          $voipnumbers = $getvoipInfos->getNumber();
          $voipnumbers = substr($voipnumbers, 2);

          $res = new Criteria();
          $res->add(TelintaAccountsPeer::ACCOUNT_TITLE, $voipnumbers);
          $res->addAnd(TelintaAccountsPeer::STATUS, 3);
          $telintaAccountres = TelintaAccountsPeer::doSelectOne($res);
          $this->callHistoryres = $ComtelintaObj->getAccountCallHistory($telintaAccountres->getIAccount(), $fromdate, $todate);
          } */
    }

    public function executeMobile(sfWebRequest $request) {

        $c = new Criteria();
        $mobile_no = $_POST['mobile_no'];
        $c->add(EmployeePeer::MOBILE_NUMBER, $mobile_no);
        if (EmployeePeer::doSelectOne($c)) {

            echo "no";
        } else {
            echo "yes";
        }
    }

    public function executeGetUniqueIds(sfWebRequest $request) {

        $c = new Criteria();
        //$c->setLimit(10);
        $sim_type_id = $request->getParameter('sim_type_id');
        $sim_type = SimTypesPeer::retrieveByPK($sim_type_id);

        $c->add(UniqueIdsPeer::SIM_TYPE_ID, $sim_type->getId());
        $c->addAnd(UniqueIdsPeer::REGISTRATION_TYPE_ID, 1);
        $c->addAnd(UniqueIdsPeer::STATUS, 0);
        $c->addAscendingOrderByColumn(UniqueIdsPeer::UNIQUE_NUMBER);



        $str = "";
        $uniqueIds = UniqueIdsPeer::doSelect($c);
        foreach ($uniqueIds as $uniqueId) {
            $str.="<option value='" . $uniqueId->getUniqueNumber() . "'>" . $uniqueId->getUniqueNumber() . "</option>";
        }
        echo $str;
        return sfView::NONE;
    }

}
