<?php

require_once(sfConfig::get('sf_lib_dir') . '/Browser.php');
require_once(sfConfig::get('sf_lib_dir') . '/emailLib.php');
require_once(sfConfig::get('sf_lib_dir') . '/changeLanguageCulture.php');
require_once(sfConfig::get('sf_lib_dir') . '/sms.class.php');
require_once(sfConfig::get('sf_lib_dir') . '/zerocall_out_sms.php');

/**
 * affiliate actions.
 * @package    zapnacrm
 * @subpackage affiliate
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php,v 1.2 2010-08-05 20:37:52 orehman Exp $
 */
class affiliateActions extends sfActions {

    private $currentCulture;
    
    private function getTargetUrl() {
        return sfConfig::get('app_agent_url');
    }

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {
        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->forward('default', 'module');
    }

    public function executeReceipts(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham

        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->targetUrl = $this->getTargetUrl();

        $c = new Criteria();
        $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');
        $c->add(AgentCompanyPeer::ID, $agent_company_id);
        $agent = AgentCompanyPeer::doSelectOne($c);

        $this->forward404Unless(AgentCompanyPeer::doSelectOne($c));

        $transactions = array();
        $registrations = array();
        $i = 1;

        //echo $agent_company_id;

        $c = new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, $agent_company_id);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $c->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
        $customers = CustomerPeer::doSelect($c);

        $startdate = $request->getParameter('startdate');
        $enddate = $request->getParameter('enddate');
        if ($startdate != '') {
            $startdate = date('d-m-Y 00:00:00', strtotime($startdate));
            $this->startdate = date('Y-m-d', strtotime($startdate));
        }else{
            $startdate = date('Y-m-d 00:00:00', strtotime($agent->getCreatedAt()));
            $this->startdate = $startdate;
        }

        if ($enddate != '') {
            $enddate = date('d-m-Y 23:59:59', strtotime($enddate));
            $this->enddate = date('Y-m-d', strtotime($enddate));
        }else{
           $enddate = date('Y-m-d 23:59:59');
           $this->enddate =$enddate;
        }


        foreach ($customers as $customer) {
            //echo $customer->getId().'<br>';
            $tc = new Criteria();
            $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
            $tc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            if ($startdate != "" && $enddate != "") {
                $tc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                $tc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
            }
            $tc->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $tc->addAnd(TransactionPeer::TRANSACTION_TYPE_ID,3);
            if (TransactionPeer::doSelectOne($tc)) {
                $registrations[$i] = TransactionPeer::doSelectOne($tc);
            }
            // echo $customer->getId().'__'.$agent_company_id.'<br>';
            $i = $i + 1;

//                           echo $customer->getMobileNumber();
//                           echo '<br/>';
        }

        //echo count($registrations);
        $ar = new Criteria();
        $ar->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
        $ar->add(TransactionPeer::TRANSACTION_TYPE_ID, 3, Criteria::NOT_EQUAL);
        $ar->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID,11, Criteria::EQUAL);
        //  $ar->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID,13, Criteria::NOT_EQUAL);
        if ($startdate != "" && $enddate != "") {
            $ar->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
            $ar->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
        }
        $ar->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $ar->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
        $refills = TransactionPeer::doSelect($ar);

//                foreach ($refills as $refill){
//                    $transactions[$i]=$refill;
//                    $i=$i+1;
//                }
   $this->registrations = $registrations;
       $this->refills = $refills;
        $this->counter = $i - 1;
        
          /////////////////// Number Change  Area///////////////////////////////
        $cn = new Criteria();
        $cn->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
        $cn->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID,13, Criteria::EQUAL);
        if ($startdate != "" && $enddate != "") {
            $cn->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
            $cn->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
        }
        
        $cn->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $cn->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
        $numberchange = TransactionPeer::doSelect($cn);
        //var_dump($numberchange);
     
        $this->numberchanges = $numberchange;
    
        /////////////////// Change Product Area///////////////////////////////
        $cp = new Criteria();
        $cp->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
        $cp->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID,15, Criteria::EQUAL);
        if ($startdate != "" && $enddate != "") {
            $cp->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
            $cp->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
        }
        $cp->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $cp->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
        $changeProducts = TransactionPeer::doSelect($cp);
        
        
        $this->changeProducts = $changeProducts; 
         ///////////////////New Sim Sale Area Area///////////////////////////////
        $ns = new Criteria();
        $ns->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
        $ns->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID,14, Criteria::EQUAL);
        if ($startdate != "" && $enddate != "") {
            $ns->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
            $ns->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
        }
        $ns->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $ns->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
        $newSimSales = TransactionPeer::doSelect($ns);
        
        
        $this->newSimSales = $newSimSales; 
        
    }

    public function executePrintReceipt(sfWebRequest $request) {
        //is authenticated
        $this->forward404Unless($this->getUser()->isAuthenticated());
      

        //check to see if transaction id is there

        $transaction_id = $request->getParameter('tid');
        $this->forward404Unless($transaction_id);
       changeLanguageCulture::languageCulture($request, $this);

        //is authenticated
     

       
        //check to see if transaction id is there
        $lang = sfConfig::get('app_language_symbol');
        $this->lang = $lang;

        $countrylng = new Criteria();
        $countrylng->add(EnableCountryPeer::LANGUAGE_SYMBOL, $lang);
        $countrylng = EnableCountryPeer::doSelectOne($countrylng);
        if ($countrylng) {
            $countryName = $countrylng->getName();
            $languageSymbol = $countrylng->getLanguageSymbol();
            $lngId = $countrylng->getId();

            $postalcharges = new Criteria();
            $postalcharges->add(PostalChargesPeer::COUNTRY, $lngId);
            $postalcharges->add(PostalChargesPeer::STATUS, 1);
            $postalcharges = PostalChargesPeer::doSelectOne($postalcharges);
            //var_dump($postalcharges);
            if ($postalcharges) {
                $postalcharge = $postalcharges->getCharges();
                $forIphoneAdaptor = $postalcharge;
            } else {
                $postalcharge = '';
            }
        }
        $transaction_id = $request->getParameter('tid');

        $this->forward404Unless($transaction_id);

        //is this receipt really belongs to authenticated user

        $transaction = TransactionPeer::retrieveByPK($transaction_id);


           $this->customer = CustomerPeer::retrieveByPK($transaction->getCustomerId());
       
        
//        if($transaction_id>93){
//          $vatValue=sfConfig::get('app_vat_percentage');
//        }else{
//         $vatValue=(.18);
//        }
//        

        $this->forward404Unless($transaction->getCustomerId() == $this->customer->getId(), 'Not allowed');

        //set customer order
        $customer_order = CustomerOrderPeer::retrieveByPK($transaction->getOrderId());
        // $this->customer_order = $customer_order;
        $customerorder = $customer_order->getIsFirstOrder();

//        echo "CustomerOrder:".$customerorder;
//        echo "<br/>";
//        echo  $transaction->getTransactionTypeId();
//        echo "<br/>";
//        echo "Transcation ID:".$transaction_id;
//        echo "<br/>";
//        echo "Registration Fee".$customer_order->getProduct()->getRegistrationFee();
//        echo "<br/>";
//        echo "ProductID:".$customer_order->getProduct()->getId()."Product Name:".$customer_order->getProduct()->getName();
//        echo "<br/>";

        if ($customerorder == 1) {




            if ($transaction_id > 93) {
                $vat = ($customer_order->getProduct()->getRegistrationFee() + $postalcharge) * sfConfig::get('app_vat_percentage');
            } else {
                $vat = ($customer_order->getProduct()->getRegistrationFee() + $postalcharge) * (.18);
            }
        } elseif ($transaction->getTransactionTypeId() == 2) {
            $vat = 0;
        } elseif ($transaction->getTransactionDescriptionId() == 6 || $transaction->getTransactionDescriptionId() == 4) {
//            echo "Amount".$transaction->getAmount();
//            echo "<br/>";
            if ($transaction_id > 93) {

                $vat = $transaction->getAmount() - ($transaction->getAmount() / (sfConfig::get('app_vat_percentage') + 1));
//                echo "vat1:".$vat;
//                echo "<br/>";
            } else {
                $vat = $transaction->getAmount() - ($transaction->getAmount() / (1.18));
//                echo "vat2:".$vat;
//                echo "<br/>";
            }
//            echo $vat;
//            die;
        } else {
            if ($transaction_id > 93) {
                $vat = $customer_order->getProduct()->getRegistrationFee() * sfConfig::get('app_vat_percentage');
            } else {

                $vat = $customer_order->getProduct()->getRegistrationFee() * (.18);
            }
        }

        if ($transaction_id > 93) {
            $vatPerValue = sfConfig::get('app_vat_percentage');
        } else {
            $vatPerValue = (.18);
        }

        //  if(strstr($transaction->getDescription(),"Refill")||strstr($transaction->getDescription(),"Charge")){
//        if (strstr($transaction->getDescription(), "Refill")) {
//            $vat = $transaction->getAmount() - ($transaction->getAmount() / ($vatPerValue + 1));
//        }
        $registered_customer_name = false;
        $refferedC = new Criteria();
        $refferedC->add(InvitePeer::BONUS_TRANSACTION_ID, $transaction->getId());
        $refferedC->add(InvitePeer::INVITE_STATUS, 3);

        if (InvitePeer::doCount($refferedC) > 0) {

            $invite = InvitePeer::doSelectOne($refferedC);
            $invitedCustomer = CustomerPeer::retrieveByPK($invite->getInvitedCustomerId());
            $registered_customer_name = $invitedCustomer->getFirstName() . " " . $invitedCustomer->getLastName();
        }



        $this->renderPartial('affiliate/order_receipt', array(
            'customer' => $this->customer,
            'order' => CustomerOrderPeer::retrieveByPK($transaction->getOrderId()),
            'transaction' => $transaction,
            'vat' => $vat,
            'registered_customer_name' => $registered_customer_name,
            'postalcharge' => $postalcharge,
            'customerorder' => $customerorder,
        ));

        return sfView::NONE;
    }

    public function executeNewsListing(sfWebRequest $request) {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $c = new Criteria();
        $c->addDescendingOrderByColumn(NewupdatePeer::STARTING_DATE);
        $news = NewupdatePeer::doSelect($c);
        $this->news = $news;
    }

    public function executeReport(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham

        $this->forward404Unless($this->getUser()->isAuthenticated());
        $nc = new Criteria();
        $nc->addDescendingOrderByColumn(NewupdatePeer::STARTING_DATE);
        $this->updateNews = NewupdatePeer::doSelect($nc);
        //verify if agent is already logged in
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $this->forward404Unless($agent);
        $this->agent = $agent;

        $startdate = $request->getParameter('startdate');
        $enddate = $request->getParameter('enddate');
        if ($startdate != '') {
            $startdate = date('d-m-Y 00:00:00', strtotime($startdate));
            $this->startdate = date('Y-m-d', strtotime($startdate));
        }else{
            $startdate = date('Y-m-d 00:00:00', strtotime($this->agent->getCreatedAt()));
            $this->startdate = date('Y-m-d', strtotime($startdate));
        }
        if ($enddate != '') {
            $enddate = date('d-m-Y 23:59:59', strtotime($enddate));
            $this->enddate = date('Y-m-d', strtotime($enddate));
        }else{
            $enddate = date('Y-m-d 23:59:59');
             $this->enddate = $enddate;
        }





        //get All customer registrations from customer table
        try {
            $c = new Criteria();
            $c->add(CustomerPeer::REFERRER_ID, $agent_company_id);
            $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 4, Criteria::NOT_EQUAL);
            if ($startdate != "" && $enddate != "") {
                    $c->addAnd(CustomerPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $c->addAnd(CustomerPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }
            $c->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
            $customers = CustomerPeer::doSelect($c);
            $registration_sum = 0.00;
            $registration_commission = 0.00;
            $registrations = array();
            $comregistrations = array();
            $i = 1;
            foreach ($customers as $customer) {
                $tc = new Criteria();
                //echo $customer->getId();
                $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
                $tc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
                $tc->add(TransactionPeer::TRANSACTION_TYPE_ID,3);
                if (TransactionPeer::doSelectOne($tc)) {
                    $registrations[$i] = TransactionPeer::doSelectOne($tc);
                }
                $i = $i + 1;
                //
                //                           echo $customer->getId();
                //                           echo '<br/>';
            }
            //                       echo 'transactions';
            //                       echo '<br/>';
            //print_r($registrations);
            if (count($registrations) >= 1) {
                //echo count($registrations);
                foreach ($registrations as $registration) {
                    //                       echo $registration->getCustomerId();
                    //                       echo '<br/>';
                    $registration_sum = $registration_sum + $registration->getAmount();
                    if ($registration != NULL) {
                        $coc = new Criteria();
                        $coc->add(CustomerOrderPeer::ID, $registration->getOrderId());
                        $customer_order = CustomerOrderPeer::doSelectOne($coc);
                        $registration_commission = $registration_commission + ($registration->getCommissionAmount());
                    }
                }
            }
            $this->registrations = $registrations;
            $this->registration_revenue = $registration_sum;
            $this->registration_commission = $registration_commission;
            $cc = new Criteria();
            $cc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            $cc->addAnd(TransactionPeer::TRANSACTION_TYPE_ID, 1);
            $cc->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            if ($startdate != "" && $enddate != "") {
                    $cc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $cc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }


            $cc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $refills = TransactionPeer::doSelect($cc);
            $refill_sum = 0.00;
            $refill_com = 0.00;
            foreach ($refills as $refill) {
                $refill_sum = $refill_sum + $refill->getAmount();
                $refill_com = $refill_com + $refill->getCommissionAmount();
            }
            $this->refills = $refills;
            $this->refill_revenue = $refill_sum;
            $this->refill_com = $refill_com;
            $efc = new Criteria();
            $efc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            $efc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);


             if ($startdate != "" && $enddate != "") {
                    $efc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $efc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }

            $efc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $ef = TransactionPeer::doSelect($efc);
            $ef_sum = 0.00;
            $ef_com = 0.00;
            foreach ($ef as $efo) {
                $description = substr($efo->getDescription(), 0, 26);
                $stringfinds = 'Refill';
                if (strstr($efo->getDescription(), $stringfinds)) {
                    //if($description== 'LandNCall AB Refill via agent ')
                    $ef_sum = $ef_sum + $efo->getAmount();
                    $ef_com = $ef_com + $efo->getCommissionAmount();
                }
            }
            $this->ef = $ef;
            $this->ef_sum = $ef_sum;
            $this->ef_com = $ef_com;
            /////////// SMS Registrations
            $cs = new Criteria();
            $cs->add(CustomerPeer::REFERRER_ID, $agent_company_id);
            $cs->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $cs->add(CustomerPeer::REGISTRATION_TYPE_ID, 4);
            if ($startdate != "" && $enddate != "") {
                    $cs->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $cs->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }


            $cs->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
            $sms_customers = CustomerPeer::doSelect($cs);
            $sms_registrations = array();
            $sms_registration_earnings = 0.0;
            $sms_commission_earnings = 0.0;
            $i = 1;
            foreach ($sms_customers as $sms_customer) {
                $tc = new Criteria();
                $tc->add(TransactionPeer::CUSTOMER_ID, $sms_customer->getId());
                $tc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
                $tc->add(TransactionPeer::TRANSACTION_TYPE_ID,3);
                $sms_registrations[$i] = TransactionPeer::doSelectOne($tc);
                if (count($sms_registrations) >= 1) {
                    $sms_registration_earnings = $sms_registration_earnings + $sms_registrations[$i]->getAmount();
                    $sms_commission_earnings = $sms_commission_earnings + $sms_registrations[$i]->getCommissionAmount();
                }
                $i = $i + 1;
            }
            $this->sms_registrations = $sms_registrations;
            $this->sms_registration_earnings = $sms_registration_earnings;
            $this->sms_commission_earnings = $sms_commission_earnings;
            ////////// End SMS registrations

//////////////////// Number  Change/////////////////////////////////////////
            $nc = new Criteria();
            $nc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
           // $nc->addAnd(TransactionPeer::TRANSACTION_TYPE_ID, 2);
            $nc->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID, 13);
            $nc->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $nc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $number_changes = TransactionPeer::doSelect($nc);
            
            $numberChange_earnings = 0.00;
            $numberChange_commission = 0.00;
            foreach ($number_changes as $number_change) {
                $numberChange_earnings = $numberChange_earnings + $number_change->getAmount();
                $numberChange_commission = $numberChange_commission + $number_change->getCommissionAmount();
            }
            $this->number_changes = $number_changes;
            $this->numberChange_earnings = $numberChange_earnings;
            $this->numberChange_commission = $numberChange_commission;
////////////////////////////////Change Product ////////////////////
 
            $cp = new Criteria();
            $cp->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
         //   $cp->addAnd(TransactionPeer::TRANSACTION_TYPE_ID, 2);
            $cp->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID, 15);
            $cp->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $cp->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $change_products = TransactionPeer::doSelect($cp);

            $changeProduct_earnings = 0.00;
            $changeProduct_commission = 0.00;
            foreach ($change_products as $change_product) {
                $changeProduct_earnings = $changeProduct_earnings + $change_product->getAmount();
                $changeProduct_commission = $changeProduct_commission + $change_product->getCommissionAmount();
            }
            $this->change_products = $change_products;
            $this->changeProduct_earnings = $changeProduct_earnings;
            $this->changeProduct_commission = $changeProduct_commission;

            
            ////////////////////////////////New Sim Sale  ////////////////////
 
            $cp = new Criteria();
            $cp->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
           
            $cp->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID, 14);
            $cp->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $cp->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $sim_sales = TransactionPeer::doSelect($cp);

            $simSale_earnings = 0.00;
            $simSale_commission = 0.00;
            foreach ($sim_sales as $sim_sale) {
                $simSale_earnings = $simSale_earnings + $sim_sale->getAmount();
                $simSale_commission = $simSale_commission + $sim_sale->getCommissionAmount();
            }
            $this->sim_sales = $sim_sales;
            $this->simSale_earnings = $simSale_earnings;
            $this->simSale_commission = $simSale_commission;
            
            
            $this->sf_request = $request;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function executeRefill(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 03/09/11 - Ahtsham


        $this->browser = new Browser();
        $this->form = new AccountRefillAgent();
        $this->target = $this->getTargetUrl();
        $this->error_msg = "";
        $this->error_mobile_number = "";
        $validated = false;

        //get Agent
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
         $c = new Criteria();
        $c->add(ProductPeer::PRODUCT_TYPE_ID, 2);

        $this->refillProducts = ProductPeer::doSelect($c);
        //get Agent commission package
        $cpc = new Criteria();
        $cpc->add(AgentCommissionPackagePeer::ID, $agent->getAgentCommissionPackageId());
        $commission_package = AgentCommissionPackagePeer::doSelectOne($cpc);

        if ($request->getParameter('balance_error')) {
            $this->balance_error = $request->getParameter('balance_error');
        } else {
            $this->balance_error = 0;
        }

        if ($request->isMethod('post')) {
            $mobile_number = $request->getParameter('mobile_number');
            
            if(strlen($mobile_number)==0){
                 $this->error_mobile_number = $this->getContext()->getI18N()->__('You must fill in this field');
                   return;
            }
            $extra_refill = $request->getParameter('extra_refill');
           // $extra_refill = $extra_refill*(sfConfig::get('app_vat_percentage')+1);
            $is_recharged = true;

          
            $customer = NULL;
            $cc = new Criteria();
            $cc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
            $cc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $cc->add(CustomerPeer::BLOCK, 0);
            //$cc->add(CustomerPeer::FONET_CUSTOMER_ID, NULL, Criteria::ISNOTNULL);  // This Line disable becoz no need of fonet system in landncall -
            $customer = CustomerPeer::doSelectOne($cc);

            //echo $customer->getId();

            if ($customer and $mobile_number != "") {
                $validated = true;
            } else {
                $validated = false;
                $is_recharged = false;
                $this->error_mobile_number = $this->getContext()->getI18N()->__('invalid mobile number');
                return;
            }
            if ($validated) {
             
                  $this->redirect('affiliate/refillDetail?pid='.$extra_refill.'&cid='.$customer->getId());
                
                
            } else {
//                                        echo 'Form Invalid, redirecting';
                $this->balance_error = 1;
                //$this->getUser()->setFlash('message', 'Invalid mobile number');
                //$this->getUser()->setFlash('error_message', 'Customer Not Found.');
                $is_recharged = false;
                $this->error_mobile_number = $this->getContext()->getI18N()->__('invalid mobile number');
            }
        }
    }

    public function executeRegistrationstep1(sfWebRequest $request) {

        $mobile = "";

        if (isset($_REQUEST['mobileno']) && $_REQUEST['mobileno'] != "") {

            $mobile = $_REQUEST['mobileno'];

            $c = new Criteria();
            $c->addJoin(CustomerProductPeer::CUSTOMER_ID, CustomerPeer::ID, Criteria::LEFT_JOIN);
            $c->add(CustomerProductPeer::PRODUCT_ID, 7);
            $c->add(CustomerPeer::MOBILE_NUMBER, $mobile);
            $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $customer = CustomerPeer::doSelectOne($c);

            if ($customer) {
                $this->form = new CustomerForm(CustomerPeer::retrieveByPK($customer->getId()));
            } else {
                $this->getUser()->setFlash('message', 'Customer is not a Zerocall Free customer');
                $this->redirect('affiliate/conversionform');
            }
        }


        $c = new Criteria();
        $c->add(AgentCompanyPeer::ID, $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $referrer_id = AgentCompanyPeer::doSelectOne($c); //->getId();
        // $this->form = new CustomerForm(CustomerPeer::retrieveByPK($customer->getId()));
        if ($request->isMethod('post')) {
            if ($mobile == "") {

                $this->form = new CustomerForm(CustomerPeer::retrieveByPK($_REQUEST['customer']['id']));
                $this->form->bind($request->getParameter("newCustomerForm"), $request->getFiles("newCustomerForm"));
                $this->form->setDefault('referrer_id', $referrer_id);
                //   $this->form->setDefault('registration_type_id', 2);
                unset($this->form['terms_conditions']);
                unset($this->form['password']);
                unset($this->form['password_confirm']);



                $this->processFormone($request, $this->form);
            }

            //set referrer id

            $this->form->getWidget('mobile_number')->setAttribute('readonly', 'readonly');
            $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');
            $this->browser = new Browser();







            //  $this->form = new CustomerForm();
            //$this->setLayout();
            sfView::NONE;
        }
    }

    public function executeRegisterCustomer(sfWebRequest $request) {


        $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');
        $this->browser = new Browser();

        $c = new Criteria();
        $c->add(AgentCompanyPeer::ID, $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $referrer_id = AgentCompanyPeer::doSelectOne($c);

        if ($request->isMethod('post')) {

            $this->form = new CustomerForm();

            $this->form->bind($request->getParameter("newCustomerForm"), $request->getFiles("newCustomerForm"));
            $this->form->setDefault('referrer_id', $referrer_id);
            unset($this->form['terms_conditions']);
            unset($this->form['imsi']);
            unset($this->form['uniqueid']);
//                        //unset($this->form['password']);
//                        unset($this->form['terms_conditions']);
            // print_r($this->form);
            //  die;

            $this->processForm($request, $this->form);
        } else {

            $this->form = new CustomerForm();
        }

        //$this->setLayout();
        sfView::NONE;
    }

    protected function processFormone(sfWebRequest $request, sfForm $form) {
        //print_r($request->getParameter($form->getName()));
        $customer = $request->getParameter($form->getName());
        $product = $customer['product'];

        //$customer['referrer_id']= $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');
//echo $customer['id'];
//die;
        //  $this->form = new CustomerForm(CustomerPeer::retrieveByPK($customer['id']));
        unset($this->form['terms_conditions']);
        unset($this->form['imsi']);
        unset($this->form['uniqueid']);
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

        //print_r($form);
        // $this->redirect('@customer_registration_step3?customer_id='.$customer['id'].'&product_id='.$product);


        if ($form->isValid()) {
            // $customer=$customer['id'];
            //     $customer->setPlainText($request->getParameter($form->getPassword()));
            $customer = $form->save();

            $customer->setReferrerId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
            $customer->setRegistrationTypeId('2');

            $customer->save();

            if ($customer) {

            }

            echo "redirecting";
            $this->redirect('@customer_registration_step3?customer_id=' . $customer->getId() . '&product_id=' . $product);
            //$this->redirect(sfConfig::get('app_epay_relay_script_url').$this->getController()->genUrl('@signup_step2?customer_id='.$customer->getId().'&product_id='.$product, true));
        }
    }

    protected function processForm(sfWebRequest $request, sfForm $form) {
        //print_r($request->getParameter($form->getName()));
        $customer = $request->getParameter($form->getName());
        $product = $customer['product'];
        //$customer['referrer_id']= $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');
        $plainPws = $customer["password"];


        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));


        //   var_dump($customer);die;
        if ($form->isValid()) {
            //     $customer->setPlainText($request->getParameter($form->getPassword()));
            $customer = $form->save();
            $customer->setReferrerId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
            $customer->setRegistrationTypeId('2');
            $customer->setPlainText($plainPws);
            $customer->setBlock('0');
            $customer->save();
            if ($customer) {

            }
            $this->getUser()->setAttribute('customer_id', $customer->getId(), 'usersignup');
            $this->getUser()->setAttribute('product_id', $product, 'usersignup');
            echo "redirecting";
            $this->redirect('@customer_registration_step2?customer_id=' . $customer->getId() . '&product_id=' . $product);
            //$this->redirect(sfConfig::get('app_epay_relay_script_url').$this->getController()->genUrl('@signup_step2?customer_id='.$customer->getId().'&product_id='.$product, true));
        }
    }

    public function executeSetProductDetails(sfWebRequest $request) {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->browser = new Browser();
        $this->form = new PaymentForm();
        $this->target = $this->getTargetUrl();
        unset(
                $this->form['cardno'],
                $this->form['expmonth'],
                $this->form['expyear'],
                $this->form['cvc'],
                $this->form['cardtype']
        );


        $product_id = $request->getParameter('product_id');
        $customer_id = $request->getParameter('customer_id');

        if ($product_id == '' || $customer_id == '') {
            $this->forward404('Product id not found in session');
        }

        $order = new CustomerOrder();
        $transaction = new Transaction();

        $order->setProductId($product_id);
        $order->setCustomerId($customer_id);
        $order->setExtraRefill($order->getProduct()->getInitialBalance());

        //$extra_refil_choices = ProductPeer::getRefillChoices();
        //$order->setExtraRefill($extra_refil_choices[0]);//minumum refill amount
        $order->setIsFirstOrder(1);

        $order->save();

        $customer = CustomerPeer::retrieveByPk($customer_id);
        $customer->setReferrerId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $customer->save();

        $transaction->setAgentCompanyId($customer->getReferrerId());


        $transaction->setAmount($order->getProduct()->getPrice() + $order->getProduct()->getRegistrationFee() + ($order->getProduct()->getRegistrationFee() * sfConfig::get('app_vat_percentage')));
        $transactiondescription=TransactionDescriptionPeer::retrieveByPK(12);
        $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
        $transaction->setTransactionDescriptionId($transactiondescription->getId());
        $transaction->setDescription($transactiondescription->getTitle());
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($customer_id);
        $vat=$order->getProduct()->getRegistrationFee() * sfConfig::get('app_vat_percentage');
         $transaction->setVat($vat);
       //$transaction->setTransactionStatusId() // default value 1

        $transaction->save();
        $this->order = $order;
        $this->forward404Unless($this->order);

        $this->order_id = $order->getId();
        $this->amount = $transaction->getAmount();

        if ($request->getParameter('balance_error') == '1') {
            $this->getUser()->setFlash('decline', 'You Do not have enough Balance, Please Recharge');
            $this->getUser()->setFlash('error_message', 'You Do not have enough Balance, Please Recharge');
            $this->balance_error = $request->getParameter('balance_error');
        } else {

            $this->balance_error = "";
        }
    }

    public function executeCompleteCustomerRegistration(sfWebRequest $request) {



        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->browser = new Browser();


        //debug form value
        $order_id = $request->getParameter('orderid');
        //$request->getParameter('amount');
        $order_amount = ((double) $request->getParameter('amount'));
//        echo $order_id;
//        echo '<br />';
//        echo $order_amount;
//die;
        $this->forward404Unless($order_id || $order_amount);


        $order = CustomerOrderPeer::retrieveByPK($order_id);

        //if order is already completed > 404
        $this->forward404Unless($order->getOrderStatusId() != sfConfig::get('app_status_completed'));
        $this->forward404Unless($order);

        //get agent
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        //echo $agent->getId();
        //getting agent commission
        $cc = new Criteria();
        $cc->add(AgentCommissionPackagePeer::ID, $agent->getAgentCommissionPackageId());
        $commission_package = AgentCommissionPackagePeer::doSelectOne($cc);

        //get transaction
        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);

        $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed

        if ($transaction->getAmount() > $order_amount) {

            $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_error', 5)); //error in amount
        } else if ($transaction->getAmount() < $order_amount) {
            $transaction->setAmount($order_amount);
        }

        $is_transaction_completed = $transaction->getTransactionStatusId() == sfConfig::get('app_status_completed', 3);
        $agentcomession = Null;
        // if transaction ok
        if ($is_transaction_completed) {
            $product_price = $order->getProduct()->getPrice() + $order->getProduct()->getRegistrationFee();
            $product_price_vat = sfConfig::get('app_vat_percentage') * $order->getProduct()->getRegistrationFee();
            $order->setAgentCommissionPackageId($order->getCustomer()->getAgentCompany()->getAgentCommissionPackageId());
            ///////////////////////////commision calculation by agent product ///////////////////////////////////////
            $cp = new Criteria;
            $cp->add(AgentProductPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
            $cp->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
            $agentproductcount = AgentProductPeer::doCount($cp);

            if ($agentproductcount > 0) {
                $p = new Criteria;
                $p->add(AgentProductPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                $p->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());

                $agentproductcomesion = AgentProductPeer::doSelectOne($p);
                $agentcomession = $agentproductcomesion->getRegShareEnable();
            }

            ////////   commission setting  through  agent commision//////////////////////

            if ($agentcomession) {


                if ($order->getIsFirstOrder()==1) {
                    if ($agentproductcomesion->getIsRegShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $agentproductcomesion->getRegShareValue());
                    } else {

                        $transaction->setCommissionAmount($agentproductcomesion->getRegShareValue());
                    }
                } else {
                    if ($agentproductcomesion->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setAgentCommission(($transaction->getAmount() / 100) * $agentproductcomesion->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setAgentCommission($agentproductcomesion->getExtraPaymentsShareValue());
                    }
                }
            } else {

                if ($order->getIsFirstOrder()==1) {
                    if ($commission_package->getIsRegShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $commission_package->getRegShareValue());
                    } else {

                        $transaction->setCommissionAmount($commission_package->getRegShareValue());
                    }
                } else {
                    if ($commission_package->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setAgentCommission(($transaction->getAmount() / 100) * $commission_package->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setAgentCommission($commission_package->getExtraPaymentsShareValue());
                    }
                }
            }


            $transaction->save();

            if ($agent->getIsPrepaid() == true) {

                if ($agent->getBalance() < ($transaction->getAmount() - $transaction->getCommissionAmount())) {
                    $this->redirect('affiliate/setProductDetails?product_id=' . $order->getProductId() . '&customer_id=' . $transaction->getCustomerId() . '&balance_error=1');
                } else {
                    $agent->setBalance($agent->getBalance() - ($transaction->getAmount() - $transaction->getCommissionAmount()));
                    $agent->save();
                    ////////////////////////////////////
                    $remainingbalance = $agent->getBalance();
                    $amount = $transaction->getAmount() - $transaction->getCommissionAmount();
                    $amount = -$amount;
                    $aph = new AgentPaymentHistory();
                    $aph->setAgentId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                    $aph->setCustomerId($transaction->getCustomerId());
                    $aph->setExpeneseType(1);
                    $aph->setAmount($amount);
                    $aph->setRemainingBalance($remainingbalance);
                    $aph->save();

                    ////////////////////////////////////////////
                }
            }
        }
        $order->save();

        if ($is_transaction_completed) {

            $customer_product = new CustomerProduct();

            $customer_product->setCustomer($order->getCustomer());
            $customer_product->setProduct($order->getProduct());

            $customer_product->save();

            //register to fonet
            $this->customer = $order->getCustomer();
//	  	Fonet::registerFonet($this->customer);
//	  	Fonet::recharge($this->customer, $order->getExtraRefill());
            $uniqueid = $request->getParameter('uniqueid');
            $uc = new Criteria();
            $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 2);
            $uc->addAnd(UniqueIdsPeer::SIM_TYPE_ID, $this->customer->getSimTypeId());
            $uc->addAnd(UniqueIdsPeer::STATUS, 0);
            $uc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueid);
            $availableUniqueCount = UniqueIdsPeer::doCount($uc);
            $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);


            if ($availableUniqueCount == 0) {
                // Unique Ids are not avaialable.  send email to the support.
                emailLib::sendUniqueIdsIssueAgent($uniqueid, $this->customer);
            } else {
                $availableUniqueId->setStatus(1);
                $availableUniqueId->setAssignedAt(date('Y-m-d H:i:s'));
                $availableUniqueId->save();
            }
            $this->customer->setUniqueid(str_replace(' ', '', $uniqueid));
            $this->customer->save();

            $cc = new Criteria();
            $cc->add(EnableCountryPeer::ID, $this->customer->getCountryId());
            $country = EnableCountryPeer::doSelectOne($cc);

            $mobile = $country->getCallingCode() . $this->customer->getMobileNumber();

            $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
            if ($getFirstnumberofMobile == 0) {
                $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
            } else {
                $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
            }

            $callbacklog = new CallbackLog();
            $callbacklog->setMobileNumber($TelintaMobile);
            $callbacklog->setuniqueId($this->customer->getUniqueid());
            $callbacklog->setCheckStatus(3);
            $callbacklog->save();

            //Section For Telinta Add Cusomter
            $telintaObj = new Telienta();
            $telintaObj->ResgiterCustomer($this->customer, $order->getExtraRefill());
            $telintaObj->createAAccount($TelintaMobile, $this->customer);
            $telintaObj->createCBAccount($TelintaMobile, $this->customer);
            $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerRegistrationViaAgentEmail($this->customer, $order);
            $this->updatePreferredCulture();
//            $zeroCallOutSMSObject = new ZeroCallOutSMS();
//            $zeroCallOutSMSObject->toCustomerAfterReg($customer_product->getProductId(), $this->customer);
            

            $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Customer ') . $this->customer->getMobileNumber() . $this->getContext()->getI18N()->__(' is registered successfully'));
            $this->redirect('affiliate/receipts');
        }// die('here');
        
    }

    public function executeFaq(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham
        //----Query Get FAQs
        //get Agent
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $country_id = $agent->getCountryId();

        //-----------------------------------
        //        $countrylng = new Criteria();
        //        $countrylng->add(EnableCountryPeer::ID, $country_id);
        //        $countrylng = EnableCountryPeer::doSelectOne($countrylng);
        //        $countryRefill = $countrylng->getRefill();


        $Faqs = new Criteria();
        //$Faqs->add(FaqsPeer::COUNTRY_ID, $country_id);
        $Faqs->add(FaqsPeer::STATUS_ID, 1);
        $Faqs = FaqsPeer::doSelect($Faqs);

        $this->Faqs = $Faqs;
        //-----------
        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->browser = new Browser();
    }

    public function executeUserguide(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham
        //----Query Get UserGuide
        //get Agent
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $country_id = $agent->getCountryId();

        //-----------------------------------
        //        $countrylng = new Criteria();
        //        $countrylng->add(EnableCountryPeer::ID, $country_id);
        //        $countrylng = EnableCountryPeer::doSelectOne($countrylng);
        //        $countryRefill = $countrylng->getRefill();


        $Userguide = new Criteria();
        // $Userguide->add(UserguidePeer::COUNTRY_ID, $country_id);
        $Userguide->add(UserguidePeer::STATUS_ID, 1);
        $Userguide = UserguidePeer::doSelect($Userguide);

        $this->Userguide = $Userguide;
        //-----------

        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->browser = new Browser();
    }

    public function executeSupportingHandset(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham


        $this->updateNews = NewupdatePeer::doSelect(new Criteria());
        $this->browser = new Browser();
    }

    public function executeNonSupportingHandset(sfWebRequest $request) {

        
        $ch = new Criteria();
        $ch->add(HandsetsPeer::SUPPORTED,0);
        $nonsupported = HandsetsPeer::doSelect($ch);
        $this->handsets = $nonsupported;
        $this->browser = new Browser();
    }

    public function executeAccountRefill(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham

        $this->target = $this->getTargetUrl().'affiliate/';
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $this->agent = $agent;
        $this->forward404Unless($agent);


        if (isset($_REQUEST['error'])) {


            $agent_order_id = $request->getParameter('orderid');

            $aoc = new Criteria();
            $aoc->add(AgentOrderPeer::AGENT_ORDER_ID, $agent_order_id);
            $agent_order = AgentOrderPeer::doSelectOne($aoc);

            $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Your Credit Card Information was not approved'));
            $this->agent_order_id = $agent_order_id;
            $this->agent_order = $agent_order;
        } else {


            $c = new Criteria();
            $agent_order = new AgentOrder();
            $agent_order->setAgentCompanyId($agent->getId());
            $agent_order->setStatus('1');
            $agent_order->save();

            $agent_order->setAgentOrderId('a0' . $agent_order->getId());
            $agent_order->save();

            $this->agent_order = $agent_order;
        }
    }

    public function executeThankyou(sfWebRequest $request) {

        $Parameters = $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);
        $email2->save();

        $order_id = $request->getParameter('orderid');
        $amount = $request->getParameter('amount');

        if ($order_id and $amount) {
            $c = new Criteria();
            $c->add(AgentOrderPeer::AGENT_ORDER_ID, $order_id);
            $c->add(AgentOrderPeer::STATUS, 1);
            $agent_order = AgentOrderPeer::doSelectOne($c);

            $agent_order->setAmount($amount);
            $agent_order->setStatus(3);
            $agent_order->save();

            $agent = AgentCompanyPeer::retrieveByPK($agent_order->getAgentCompanyId());
            $agent->setBalance($agent->getBalance() + ($amount));
            $agent->save();
            $this->agent = $agent;

            $amount = $amount;
            $remainingbalance = $agent->getBalance();
            $aph = new AgentPaymentHistory();
            $aph->setAgentId($agent_order->getAgentCompanyId());
            $aph->setExpeneseType(3);
            $aph->setAmount($amount);
            $aph->setRemainingBalance($remainingbalance);
            $aph->save();

            $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Your Credit Card recharge of %1%%2% is approved ', array("%1%" => $amount, "%2%" => sfConfig::get('app_currency_code'))));
            emailLib::sendAgentRefilEmail($this->agent, $agent_order);
            $this->redirect('affiliate/agentOrder');
        }
        $this->redirect('affiliate/agentOrder');
    }

    public function executeAgentOrder(sfRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11 - Ahtsham


        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $this->forward404Unless($agent);

        $this->agent = $agent;
        $c = new Criteria();
        $c->add(AgentOrderPeer::AGENT_COMPANY_ID, $agent->getId());
        $c->add(AgentOrderPeer::STATUS, 3);
        $this->agentOrders = AgentOrderPeer::doSelect($c);
    }

    public function executePrintAgentReceipt(sfWebrequest $request) {
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $this->forward404Unless($agent);

        $aoid = $request->getParameter('aoid');
        $agent_order = AgentOrderPeer::retrieveByPk($aoid);
        $this->agent = $agent;
        $this->aoid = $aoid;
        $this->agent_order = $agent_order;

        $this->setLayout(false);
    }

    public function executePaymentHistory(sfWebrequest $request) {

        $ca = new Criteria();
        $ca->add(AgentPaymentHistoryPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentPaymentHistoryPeer::doSelect($ca);
        //$this->forward404Unless($agent);

        $this->agents = $agent;
    }

    public function executeGetmobilemodel(sfWebRequest $request) {

        if ($request->isXmlHttpRequest()) {
            // echo $request->getParameter('device_id').'pakistan';
            $device_id = (int) $request->getParameter('device_id');
            if ($device_id) {
                // Get The Mobile Model
                $Mobilemodel = new Criteria();
                $Mobilemodel->add(DevicePeer::MANUFACTURER_ID, $device_id);
                $mModel = DevicePeer::doSelect($Mobilemodel);
                //echo $mModel->getName();
                $output = '<option value=""></option>';
                foreach ($mModel as $mModels) {
                    echo $mModels->getName();
                    $output .= '<option value="' . $mModels->getId() . '">' . $mModels->getName() . '</option>';
                }
                return $this->renderText($output);
            }
        }
    }

    public function executeValidateUniqueId(sfWebRequest $request) {

        $uniqueId = $request->getParameter('uniqueid');
        $order = CustomerOrderPeer::retrieveByPK($request->getParameter('orderid'));
        $uc = new Criteria();
        $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 2);
        $uc->addAnd(UniqueIdsPeer::SIM_TYPE_ID, $order->getCustomer()->getSimTypeId());
        $uc->addAnd(UniqueIdsPeer::STATUS, 0);
        $uc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueId);
        $availableUniqueCount = UniqueIdsPeer::doCount($uc);
        if ($availableUniqueCount == 1) {
            echo "true";
        } else {
            echo "false";
        }
//echo $order->getCustomer()->getSimTypeId();die;
        return sfView::NONE;
    }

    public function executeChangeCulture(sfWebRequest $request) {
        // var_dump($request->getParameter('new'));
        $this->getUser()->setCulture($request->getParameter('new'));
        //$this->redirect('affiliate/report?show_summary=1');
        $pathArray = $request->getPathInfoArray();
        $this->redirect($pathArray['HTTP_REFERER']);
    }

    public function executeChangenumberservice(sfWebRequest $request) {

        changeLanguageCulture::languageCulture($request, $this);
        $this->browser = new Browser();
        $this->targetUrl = $this->getTargetUrl();
    }

    public function executeChangenumber(sfWebRequest $request) {
        changeLanguageCulture::languageCulture($request, $this);
        $this->targetUrl = $this->getTargetUrl();

        $mobile = "";
        $existingNumber = $request->getParameter('existingNumber');
        $this->newNumber = $request->getParameter('newNumber');
        $this->countrycode = $request->getParameter('countrycode');
        
        if($request->getParameter('newNumber')==$request->getParameter('existingNumber'))
        {
           
          $this->getUser()->setFlash('message', 'Both Mobile numbers are same please enter different Mobile number');
                $this->redirect('affiliate/changenumberservice');    
            
        }
            
        if (isset($_REQUEST['existingNumber']) && $_REQUEST['existingNumber'] != "") {
            $mobile = $_REQUEST['existingNumber'];
            $product = $_REQUEST['product'];
            $cc = new Criteria();
            $cc->add(CustomerPeer::MOBILE_NUMBER, $mobile);
            $cc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);

            $c = new Criteria();
            $c->add(ProductPeer::ID, $product);
            $product = ProductPeer::doSelectOne($c);

            if (CustomerPeer::doCount($cc) == 0) {
                $this->getUser()->setFlash('message', 'Customer Mobile Number Does not exist');
                $this->redirect('affiliate/changenumberservice');
            }

            $customer = CustomerPeer::doSelectOne($cc);
            if ($customer) {
                $this->customer = $customer;
                $this->product = $product;
            } else {
                $this->getUser()->setFlash('message', 'Customer Does not exist');
                $this->redirect('affiliate/changenumberservice');
            }
        }
    }

    public function executeNumberProcess(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 03/09/11 - Ahtsham
        changeLanguageCulture::languageCulture($request, $this);

        $this->browser = new Browser();
        $this->form = new AccountRefillAgent();

        $this->error_msg = "";
        $this->error_mobile_number = "";
        $validated = false;

        //get Agent
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
//var_dump($agent);
        //get Agent commission package
        $cpc = new Criteria();
        $cpc->add(AgentCommissionPackagePeer::ID, $agent->getAgentCommissionPackageId());
        $commission_package = AgentCommissionPackagePeer::doSelectOne($cpc);

        if ($request->getParameter('balance_error')) {
            $this->balance_error = $request->getParameter('balance_error');
        } else {
            $this->balance_error = 0;
        }

        if ($request->isMethod('post')) {
            $mobile_number = $request->getParameter('mobile_number');
            $productid = $request->getParameter('productid');
            $extra_refill = $request->getParameter('extra_refill');
             $totalAmount = $request->getParameter('totalAmount');
              $vat = $request->getParameter('vat');
            
            $newnumber = $request->getParameter('newnumber');
            $countrycode = $request->getParameter('countrycode');

            $is_recharged = true;
            $transaction = new Transaction();
            $order = new CustomerOrder();
            $customer = NULL;
            $cc = new Criteria();
            $cc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
            $cc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);

            $customer = CustomerPeer::doSelectOne($cc);

            if ($customer and $mobile_number != "") {
                $validated = true;
            } else {
                $validated = false;
                $is_recharged = false;
                $this->error_mobile_number = 'invalid mobile number';
                return;
            }

            if ($validated) {

///////////////////////////////change number process///////////////////////////////////////////////////////////////////
                $order->setCustomerId($customer->getId());
                $order->setProductId($productid);
                $order->setQuantity(1);
                $order->setExtraRefill($extra_refill);
                $order->setOrderStatusId(sfConfig::get('app_status_new'));

                $order->save();

                //create transaction
                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customer->getId());
                $transaction->setAmount($totalAmount);
                   $transactiondescription=  TransactionDescriptionPeer::retrieveByPK(13);
                $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
                $transaction->setTransactionDescriptionId($transactiondescription->getId());
                $transaction->setDescription($transactiondescription->getTitle());
            //    $transaction->setDescription('Fee for change number (' . $agent->getName() . ')');
                $transaction->setAgentCompanyId($agent->getId());
                  $transaction->setVat($vat);
                          //assign commission to transaction;
                /////////////////////////////////////////////////////////////////////////////////////////////////
                $order->setAgentCommissionPackageId($agent->getAgentCommissionPackageId());
                ///////////////////////////commision calculation by agent product ///////////////////////////////////////
                $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');
                $cp = new Criteria;
                $cp->add(AgentProductPeer::AGENT_ID, $agent_company_id);
                $cp->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                $agentproductcount = AgentProductPeer::doCount($cp);
                if ($agentproductcount > 0) {
                    $p = new Criteria;
                    $p->add(AgentProductPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                    $p->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());

                    $agentproductcomesion = AgentProductPeer::doSelectOne($p);
                    $agentcomession = $agentproductcomesion->getExtraPaymentsShareEnable();
                }

                ////////   commission setting  through  agent commision//////////////////////

                if (isset($agentcomession) && $agentcomession != "") {

                    if ($agentproductcomesion->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $agentproductcomesion->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($agentproductcomesion->getExtraPaymentsShareValue());
                    }
                } else {
                    if ($commission_package->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $commission_package->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($commission_package->getExtraPaymentsShareValue());
                    }
                }
                //calculated amount for agent commission
                if ($agent->getIsPrepaid() == true) {
                    if ($agent->getBalance() < ($transaction->getAmount() - $transaction->getCommissionAmount())) {
                        $is_recharged = false;
                        $balance_error = 1;
                    }
                }
                // var_dump($customer);exit;

                if ($is_recharged) {

                    $transaction->save();
                    if ($customer) {
                        $getFirstnumberofMobile = substr($newnumber, 0, 1);
                        if ($getFirstnumberofMobile == 0) {
                            $newMobileNo = substr($newnumber, 1);
                            $newMobileNo = $countrycode . $newMobileNo;
                        } else {
                            $newMobileNo = $countrycode . $newnumber;
                        }

                        $customerids = $customer->getId();
                        $uniqueId = $customer->getUniqueid();
                        $customer->setMobileNumber($newnumber);
                        $customer->save();

                        $changenumberdetail = new ChangeNumberDetail();
                        $changenumberdetail->setOldNumber($mobile_number);
                        $changenumberdetail->setNewNumber($newnumber);
                        $changenumberdetail->setCustomerId($customerids);
                        $changenumberdetail->setStatus(3);
                        $changenumberdetail->save();

                        $un = new Criteria();
                        $un->add(CallbackLogPeer::UNIQUEID, $uniqueId);
                        $un->addDescendingOrderByColumn(CallbackLogPeer::CREATED);
                        $activeNumber = CallbackLogPeer::doSelectOne($un);

                        // As each customer have a single account search the previous account and terminate it.
                        $cp = new Criteria;
                        $cp->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'a' . $activeNumber->getMobileNumber());
                        $cp->addAnd(TelintaAccountsPeer::STATUS, 3);
                        $telintaObj = new Telienta();
                        if (TelintaAccountsPeer::doCount($cp) > 0) {
                            $telintaAccount = TelintaAccountsPeer::doSelectOne($cp);
                            $telintaObj->terminateAccount($telintaAccount);
                        }

                        $telintaObj->createAAccount($newMobileNo, $customer);

                        $cb = new Criteria;
                        $cb->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'cb' . $activeNumber->getMobileNumber());
                        $cb->addAnd(TelintaAccountsPeer::STATUS, 3);

                        if (TelintaAccountsPeer::doCount($cb) > 0) {
                            $telintaAccountsCB = TelintaAccountsPeer::doSelectOne($cb);
                            $telintaObj->terminateAccount($telintaAccountsCB);
                        }
                        

                        $getvoipInfo = new Criteria();
                        $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customerids);
                        $getvoipInfo->addAnd(SeVoipNumberPeer::IS_ASSIGNED, 1);
                        $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
                        if (isset($getvoipInfos)) {
                            $voipnumbers = $getvoipInfos->getNumber();
                            $voipnumbers = substr($voipnumbers, 2);

                            $tc = new Criteria();
                            $tc->add(TelintaAccountsPeer::ACCOUNT_TITLE, $voipnumbers);
                            $tc->add(TelintaAccountsPeer::STATUS, 3);
                            if (TelintaAccountsPeer::doCount($tc) > 0) {
                                $telintaAccountR = TelintaAccountsPeer::doSelectOne($tc);
                                $telintaObj->terminateAccount($telintaAccountR);
                            }
                            $telintaObj->createReseNumberAccount($voipnumbers, $customer, $newMobileNo);
                        } else {

                        }
                    }

                    $callbacklog = new CallbackLog();
                    $callbacklog->setMobileNumber($newMobileNo);
                    $callbacklog->setuniqueId($uniqueId);
                    $callbacklog->setcallingCode($countrycode);
                    $callbacklog->save();

                    $mobile_number = substr($mobile_number, 1);
                    $number = $countrycode . $mobile_number;
                    $sms = SmsTextPeer::retrieveByPK(1);
                    $sms_text = $sms->getMessageText();
                    $sms_text = str_replace(array("(oldnumber)", "(newnumber)"), array($mobile_number, $newnumber), $sms_text);

                    //ROUTED_SMS::Send($number, $sms_text,"Zapna");
                    //Send SMS ----
                    $number = $newMobileNo;
                    //ROUTED_SMS::Send($number, $sms_text,"Zapna");
                }
//exit;
                if ($agent->getIsPrepaid() == true) {
                    $agent->setBalance($agent->getBalance() - ($transaction->getAmount() - $transaction->getCommissionAmount()));
                    $agent->save();
                    $remainingbalance = $agent->getBalance();
                    $amount = $transaction->getAmount() - $transaction->getCommissionAmount();
                    $amount = -$amount;
                    $aph = new AgentPaymentHistory();
                    $aph->setAgentId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                    $aph->setCustomerId($transaction->getCustomerId());
                    $aph->setExpeneseType(6);
                    $aph->setAmount($amount);
                    $aph->setRemainingBalance($remainingbalance);
                    $aph->save();
                }
                //set status
                $order->setOrderStatusId(sfConfig::get('app_status_completed'));
                $transaction->setTransactionStatusId(sfConfig::get('app_status_completed'));
                $order->save();
                $transaction->save();
                $this->customer = $order->getCustomer();
                $this->setPreferredCulture($this->customer);
                emailLib::sendChangeNumberEmail($this->customer, $order);
                $this->updatePreferredCulture();
                $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('%1% Mobile Number is changed successfully  with %2% %3%.', array("%1%" => $customer->getMobileNumber(), "%2%" => $transaction->getAmount(), "%3%" => sfConfig::get('app_currency_code'))));

                $this->redirect('affiliate/receipts');
            } else {

                $this->balance_error = 1;
                $this->getUser()->setFlash('error', 'You do not have enough balance, please recharge');
            } //end else
        } else {

            $this->balance_error = 1;
            $is_recharged = false;
            $this->error_mobile_number = 'invalid mobile number';
            $this->getUser()->setFlash('error', 'invalid mobile number');
        }
    }

    public function executeAgentRefil(sfWebRequest $request) { 
        
        $order_id = $request->getParameter('item_number');
        $item_amount = $request->getParameter('amount');

        $return_url = $this->getTargetUrl() . 'affiliate/accountRefill';
        $cancel_url = $this->getTargetUrl() . 'affiliate/thankyou/?accept=cancel';
        
        $callbackparameters = $order_id.'-'.$item_amount;
        $notify_url = sfConfig::get('app_customer_url') . 'pScripts/agentRefillThankyou?p='.$callbackparameters;

        $c = new Criteria;
        $c->add(AgentOrderPeer::AGENT_ORDER_ID, $order_id);
        $c->add(AgentOrderPeer::STATUS, 1);
        $agent_order = AgentOrderPeer::doSelectOne($c);

        $agent_order->setAmount($item_amount);
        $agent_order->save();

        $querystring = '';
        if (!isset($_POST["txn_id"]) && !isset($_POST["txn_type"])) {

            $item_name = "Agent Refill";

            //loop for posted values and append to querystring
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $querystring .= "$key=$value&";
            }

            $querystring .= "item_name=" . urlencode($item_name) . "&";
            $querystring .= "return=" . urldecode($return_url) . "&";
            $querystring .= "cancel_return=" . urldecode($cancel_url) . "&";
            $querystring .= "notify_url=" . urldecode($notify_url);

            //$environment = "sandbox";
//        echo $querystring;
            if ($order_id && $item_amount) {
                Payment::SendPayment($querystring);
            } else {
                echo 'error';
            }
            return sfView::NONE;
            //exit();
        }
    }

    public function executeOverview(sfWebRequest $request) {

        $this->forward404Unless($this->getUser()->isAuthenticated());
        $nc = new Criteria();
        $nc->addDescendingOrderByColumn(NewupdatePeer::STARTING_DATE);
        $this->updateNews = NewupdatePeer::doSelect($nc);
        //verify if agent is already logged in
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $this->forward404Unless($agent);
        $this->agent = $agent;

        $startdate = $request->getParameter('startdate');
        $enddate = $request->getParameter('enddate');
        if ($startdate != '') {
            $startdate = date('d-m-Y 00:00:00', strtotime($startdate));
            $this->startdate = date('Y-m-d', strtotime($startdate));
        }else{
            $startdate = date('Y-m-d 00:00:00', strtotime($this->agent->getCreatedAt()));
            $this->startdate = $startdate;
        }
        if ($enddate != '') {
            $enddate = date('d-m-Y 23:59:59', strtotime($enddate));
            $this->enddate = date('Y-m-d', strtotime($enddate));
        }else{
            $enddate = date('Y-m-d 23:59:59');
             $this->enddate = $enddate;
        }
        //get All customer registrations from customer table
        try {
            $c = new Criteria();
            $c->add(CustomerPeer::REFERRER_ID, $agent_company_id);
            $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 4, Criteria::NOT_EQUAL);
            $c->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
            $customers = CustomerPeer::doSelect($c);
            $registration_sum = 0.00;
            $registration_commission = 0.00;
            $registrations = array();
            $comregistrations = array();
            $i = 1;
            foreach ($customers as $customer) {
                $tc = new Criteria();
                //echo $customer->getId();
                $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
                $tc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
                $tc->add(TransactionPeer::TRANSACTION_TYPE_ID,3);
                if ($startdate != "" && $enddate != "") {
                    $tc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $tc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }
                if (TransactionPeer::doSelectOne($tc)) {
                    $registrations[$i] = TransactionPeer::doSelectOne($tc);
                }
                $i = $i + 1;
            }

            if (count($registrations) >= 1) {

                foreach ($registrations as $registration) {
                    $registration_sum = $registration_sum + $registration->getAmount();
                    if ($registration != NULL) {
                        $coc = new Criteria();
                        $coc->add(CustomerOrderPeer::ID, $registration->getOrderId());
                        $customer_order = CustomerOrderPeer::doSelectOne($coc);
                        $registration_commission = $registration_commission + ($registration->getCommissionAmount());
                    }
                }
            }
            $this->registrations = $registrations;
            $this->registration_revenue = $registration_sum;
            $this->registration_commission = $registration_commission;
            $cc = new Criteria();
            $cc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            $cc->addAnd(TransactionPeer::TRANSACTION_TYPE_ID,1);
            $cc->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            if ($startdate != "" && $enddate != "") {
                $cc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                $cc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
            }
            $cc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $refills = TransactionPeer::doSelect($cc);
            $refill_sum = 0.00;
            $refill_com = 0.00;
            foreach ($refills as $refill) {
                $refill_sum = $refill_sum + $refill->getAmount();
                $refill_com = $refill_com + $refill->getCommissionAmount();
            }
            $this->refills = $refills;
            $this->refill_revenue = $refill_sum;
            $this->refill_com = $refill_com;
            $efc = new Criteria();
            $efc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            $efc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            if ($startdate != "" && $enddate != "") {
                $efc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                $efc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
            }
            $efc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $ef = TransactionPeer::doSelect($efc);
            $ef_sum = 0.00;
            $ef_com = 0.00;
            foreach ($ef as $efo) {
                $description = substr($efo->getDescription(), 0, 26);
                $stringfinds = 'Refill';
                if (strstr($efo->getDescription(), $stringfinds)) {
                    //if($description== 'LandNCall AB Refill via agent ')
                    $ef_sum = $ef_sum + $efo->getAmount();
                    $ef_com = $ef_com + $efo->getCommissionAmount();
                }
            }
            $this->ef = $ef;
            $this->ef_sum = $ef_sum;
            $this->ef_com = $ef_com;
            /////////// SMS Registrations
            $cs = new Criteria();
            $cs->add(CustomerPeer::REFERRER_ID, $agent_company_id);
            $cs->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $cs->add(CustomerPeer::REGISTRATION_TYPE_ID, 4);
            $cs->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
            $sms_customers = CustomerPeer::doSelect($cs);
            $sms_registrations = array();
            $sms_registration_earnings = 0.0;
            $sms_commission_earnings = 0.0;
            $i = 1;
            foreach ($sms_customers as $sms_customer) {
                $tc = new Criteria();
                $tc->add(TransactionPeer::CUSTOMER_ID, $sms_customer->getId());
                $tc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
                $tc->add(TransactionPeer::TRANSACTION_TYPE_ID,3);
                if ($startdate != "" && $enddate != "") {
                    $tc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $tc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }
                $sms_registrations[$i] = TransactionPeer::doSelectOne($tc);
                if (count($sms_registrations) >= 1) {
                    $sms_registration_earnings = $sms_registration_earnings + $sms_registrations[$i]->getAmount();
                    $sms_commission_earnings = $sms_commission_earnings + $sms_registrations[$i]->getCommissionAmount();
                }
                $i = $i + 1;
            }
            $this->sms_registrations = $sms_registrations;
            $this->sms_registration_earnings = $sms_registration_earnings;
            $this->sms_commission_earnings = $sms_commission_earnings;
            
            
            
            
            
            
            

//////////////////// Number  Change/////////////////////////////////////////
            $nc = new Criteria();
            $nc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
           // $nc->addAnd(TransactionPeer::TRANSACTION_TYPE_ID, 2);
            $nc->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID, 13);
            $nc->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $nc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $number_changes = TransactionPeer::doSelect($nc);
            
            $numberChange_earnings = 0.00;
            $numberChange_commission = 0.00;
            foreach ($number_changes as $number_change) {
                $numberChange_earnings = $numberChange_earnings + $number_change->getAmount();
                $numberChange_commission = $numberChange_commission + $number_change->getCommissionAmount();
            }
            $this->number_changes = $number_changes;
            $this->numberChange_earnings = $numberChange_earnings;
            $this->numberChange_commission = $numberChange_commission;
////////////////////////////////Change Product ////////////////////
 
            $cp = new Criteria();
            $cp->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
         //   $cp->addAnd(TransactionPeer::TRANSACTION_TYPE_ID, 2);
            $cp->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID, 15);
            $cp->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $cp->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $change_products = TransactionPeer::doSelect($cp);

            $changeProduct_earnings = 0.00;
            $changeProduct_commission = 0.00;
            foreach ($change_products as $change_product) {
                $changeProduct_earnings = $changeProduct_earnings + $change_product->getAmount();
                $changeProduct_commission = $changeProduct_commission + $change_product->getCommissionAmount();
            }
            $this->change_products = $change_products;
            $this->changeProduct_earnings = $changeProduct_earnings;
            $this->changeProduct_commission = $changeProduct_commission;

            
            ////////////////////////////////New Sim Sale  ////////////////////
 
            $cp = new Criteria();
            $cp->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
           
            $cp->addAnd(TransactionPeer::TRANSACTION_DESCRIPTION_ID, 14);
            $cp->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            $cp->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $sim_sales = TransactionPeer::doSelect($cp);

            $simSale_earnings = 0.00;
            $simSale_commission = 0.00;
            foreach ($sim_sales as $sim_sale) {
                $simSale_earnings = $simSale_earnings + $sim_sale->getAmount();
                $simSale_commission = $simSale_commission + $sim_sale->getCommissionAmount();
            }
            $this->sim_sales = $sim_sales;
            $this->simSale_earnings = $simSale_earnings;
            $this->simSale_commission = $simSale_commission;
            
                        
            
            
            ////////// End SMS registrations
            $this->sf_request = $request;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function executePrintOverview(sfWebRequest $request) {

        $this->forward404Unless($this->getUser()->isAuthenticated());
        $nc = new Criteria();
        $nc->addDescendingOrderByColumn(NewupdatePeer::STARTING_DATE);
        $this->updateNews = NewupdatePeer::doSelect($nc);
        //verify if agent is already logged in
        $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
        $this->forward404Unless($agent);
        $this->agent = $agent;

        $startdate = $request->getParameter('startdate');
        $enddate = $request->getParameter('enddate');
        if ($startdate != '') {
            $startdate = date('Y-m-d 00:00:00', strtotime($startdate));
            $this->startdate = date('Y-m-d', strtotime($startdate));
        }
        if ($enddate != '') {
            $enddate = date('Y-m-d 23:59:59', strtotime($enddate));
            $this->enddate = date('Y-m-d', strtotime($enddate));
        }
        //get All customer registrations from customer table
        try {
            $c = new Criteria();
            $c->add(CustomerPeer::REFERRER_ID, $agent_company_id);
            $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 4, Criteria::NOT_EQUAL);
            $c->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
            $customers = CustomerPeer::doSelect($c);
            $registration_sum = 0.00;
            $registration_commission = 0.00;
            $registrations = array();
            $comregistrations = array();
            $i = 1;
            foreach ($customers as $customer) {
                $tc = new Criteria();
                //echo $customer->getId();
                $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
                $tc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
                $tc->add(TransactionPeer::TRANSACTION_TYPE_ID,3);
                if ($startdate != "" && $enddate != "") {
                    $tc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $tc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }
                if (TransactionPeer::doSelectOne($tc)) {
                    $registrations[$i] = TransactionPeer::doSelectOne($tc);
                }
                $i = $i + 1;
            }

            if (count($registrations) >= 1) {

                foreach ($registrations as $registration) {
                    $registration_sum = $registration_sum + $registration->getAmount();
                    if ($registration != NULL) {
                        $coc = new Criteria();
                        $coc->add(CustomerOrderPeer::ID, $registration->getOrderId());
                        $customer_order = CustomerOrderPeer::doSelectOne($coc);
                        $registration_commission = $registration_commission + ($registration->getCommissionAmount());
                    }
                }
            }
            $this->registrations = $registrations;
            $this->registration_revenue = $registration_sum;
            $this->registration_commission = $registration_commission;
            $cc = new Criteria();
            $cc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            $cc->addAnd(TransactionPeer::TRANSACTION_TYPE_ID,1);
            $cc->addAnd(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            if ($startdate != "" && $enddate != "") {
                $cc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                $cc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
            }
            $cc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $refills = TransactionPeer::doSelect($cc);
            $refill_sum = 0.00;
            $refill_com = 0.00;
            foreach ($refills as $refill) {
                $refill_sum = $refill_sum + $refill->getAmount();
                $refill_com = $refill_com + $refill->getCommissionAmount();
            }
            $this->refills = $refills;
            $this->refill_revenue = $refill_sum;
            $this->refill_com = $refill_com;
            $efc = new Criteria();
            $efc->add(TransactionPeer::AGENT_COMPANY_ID, $agent_company_id);
            $efc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
            if ($startdate != "" && $enddate != "") {
                $efc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                $efc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
            }
            $efc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $ef = TransactionPeer::doSelect($efc);
            $ef_sum = 0.00;
            $ef_com = 0.00;
            foreach ($ef as $efo) {
                $description = substr($efo->getDescription(), 0, 26);
                $stringfinds = 'Refill';
                if (strstr($efo->getDescription(), $stringfinds)) {
                    //if($description== 'LandNCall AB Refill via agent ')
                    $ef_sum = $ef_sum + $efo->getAmount();
                    $ef_com = $ef_com + $efo->getCommissionAmount();
                }
            }
            $this->ef = $ef;
            $this->ef_sum = $ef_sum;
            $this->ef_com = $ef_com;
            /////////// SMS Registrations
            $cs = new Criteria();
            $cs->add(CustomerPeer::REFERRER_ID, $agent_company_id);
            $cs->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $cs->add(CustomerPeer::REGISTRATION_TYPE_ID, 4);
            $cs->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
            $sms_customers = CustomerPeer::doSelect($cs);
            $sms_registrations = array();
            $sms_registration_earnings = 0.0;
            $sms_commission_earnings = 0.0;
            $i = 1;
            foreach ($sms_customers as $sms_customer) {
                $tc = new Criteria();
                $tc->add(TransactionPeer::CUSTOMER_ID, $sms_customer->getId());
                $tc->add(TransactionPeer::TRANSACTION_STATUS_ID, 3);
                $tc->add(TransactionPeer::TRANSACTION_TYPE_ID,3);
                if ($startdate != "" && $enddate != "") {
                    $tc->addAnd(TransactionPeer::CREATED_AT, $startdate, Criteria::GREATER_EQUAL);
                    $tc->addAnd(TransactionPeer::CREATED_AT, $enddate, Criteria::LESS_EQUAL);
                }
                $sms_registrations[$i] = TransactionPeer::doSelectOne($tc);
                if (count($sms_registrations) >= 1) {
                    $sms_registration_earnings = $sms_registration_earnings + $sms_registrations[$i]->getAmount();
                    $sms_commission_earnings = $sms_commission_earnings + $sms_registrations[$i]->getCommissionAmount();
                }
                $i = $i + 1;
            }
            $this->sms_registrations = $sms_registrations;
            $this->sms_registration_earnings = $sms_registration_earnings;
            $this->sms_commission_earnings = $sms_commission_earnings;
            ////////// End SMS registrations
            $this->sf_request = $request;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $this->setLayout(false);
    }
    private function setPreferredCulture(Customer $customer) {
        $this->currentCulture = $this->getUser()->getCulture();
        $preferredLang = PreferredLanguagesPeer::retrieveByPK($customer->getPreferredLanguageId());
        $this->getUser()->setCulture($preferredLang->getLanguageCode());
    }

    private function updatePreferredCulture() {
        $this->getUser()->setCulture($this->currentCulture);
    }
    public function executeRefillProcess(sfWebRequest $request) {
        
            $customer=  CustomerPeer::retrieveByPK($request->getParameter('customer_id'));
        $product=  ProductPeer::retrieveByPK($request->getParameter('product_id'));
          $is_recharged = true;
          $agentcomession=FALSE;
          $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
         $c = new Criteria();
       
        //get Agent commission package
        $cpc = new Criteria();
        $cpc->add(AgentCommissionPackagePeer::ID, $agent->getAgentCommissionPackageId());
        $commission_package = AgentCommissionPackagePeer::doSelectOne($cpc);
        
      $transaction = new Transaction();
            $order = new CustomerOrder();
            $extra_refill=$request->getParameter('totalAmount');
      
                $order->setCustomerId($customer->getId());
                $order->setProductId($product->getId());
                $order->setQuantity(1);
                $order->setExtraRefill($request->getParameter('productRefillAmount'));
                $order->setIsFirstOrder(false);
                $order->setOrderStatusId(sfConfig::get('app_status_new'));
                $order->save();

                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customer->getId());
                $transaction->setAmount($extra_refill);
                $transactiondescription=TransactionDescriptionPeer::retrieveByPK(11);
                $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
                $transaction->setTransactionDescriptionId($transactiondescription->getId());
                $transaction->setDescription($transactiondescription->getTitle());
                $transaction->setVat($request->getParameter('vat'));
                $transaction->setAgentCompanyId($agent->getId());

                $order->setAgentCommissionPackageId($agent->getAgentCommissionPackageId());

                $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');

                $cp = new Criteria;
                $cp->add(AgentProductPeer::AGENT_ID, $agent_company_id);
                $cp->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                $agentproductcount = AgentProductPeer::doCount($cp);
                if ($agentproductcount > 0) {
                    $p = new Criteria;
                    $p->add(AgentProductPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                    $p->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                    $agentproductcomesion = AgentProductPeer::doSelectOne($p);
                    $agentcomession = $agentproductcomesion->getExtraPaymentsShareEnable();
                }

                ////////   commission setting  through  agent commision//////////////////////

                if ($agentcomession) {
                    if ($agentproductcomesion->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $agentproductcomesion->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($agentproductcomesion->getExtraPaymentsShareValue());
                    }
                } else {
                    if ($commission_package->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $commission_package->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($commission_package->getExtraPaymentsShareValue());
                    }
                }
                //calculated amount for agent commission
                if ($agent->getIsPrepaid() == true) {
                    if ($agent->getBalance() < ($transaction->getAmount() - $transaction->getCommissionAmount())) {
                        $is_recharged = false;
                        $balance_error = 1;
                    }
                }

                if ($is_recharged) {
                    $transaction->save();
                    if ($agent->getIsPrepaid() == true) {
                        $agent->setBalance($agent->getBalance() - ($transaction->getAmount() - $transaction->getCommissionAmount()));
                        $agent->save();
                        $remainingbalance = $agent->getBalance();
                        $amount = $transaction->getAmount() - $transaction->getCommissionAmount();
                        $amount = -$amount;
                        $aph = new AgentPaymentHistory();
                        $aph->setAgentId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                        $aph->setCustomerId($transaction->getCustomerId());
                        $aph->setExpeneseType(2);
                        $aph->setAmount($amount);
                        $aph->setRemainingBalance($remainingbalance);
                        $aph->save();
                    }

                    $uniqueId = $customer->getUniqueid();
                    $OpeningBalance = $order->getExtraRefill();
                    $telintaObj = new Telienta();
                    $telintaObj->recharge($customer, $OpeningBalance,"Refill");
                    //set status
                    $order->setOrderStatusId(sfConfig::get('app_status_completed'));
                    $transaction->setTransactionStatusId(sfConfig::get('app_status_completed'));
                    $order->setExeStatus(1);
                    $order->save();
                    $transaction->save();
                    $this->customer = $order->getCustomer();
                    //  $this->getUser()->setCulture('de');
                    $this->setPreferredCulture($this->customer);
                        emailLib::sendRefillEmail($this->customer, $order);
                    $this->updatePreferredCulture();
                    //   $this->getUser()->setCulture('en');
                    $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('%1% account is successfully refilled with %2% %3%.', array("%1%" => $customer->getMobileNumber(), "%2%" => $order->getExtraRefill(), "%3%" => sfConfig::get('app_currency_code'))));
//                                      echo 'rehcarged, redirecting';
                    $this->redirect('affiliate/receipts');
                } else {
//                                        echo 'NOT rehcarged, redirecting';
                    $this->balance_error = 1;
                    $this->getUser()->setFlash('error', 'You do not have enough balance, please recharge');
                } //end else
    
     return sfView::NONE;
    }
    
    
     public function executeRefillDetail(sfWebRequest $request) {
          changeLanguageCulture::languageCulture($request, $this);
        $this->targetUrl = $this->getTargetUrl(); 
        $this->customer=  CustomerPeer::retrieveByPK($request->getParameter('cid'));
        $this->product=  ProductPeer::retrieveByPK($request->getParameter('pid'));
     }
      public function executePurchaseNewSim(sfWebRequest $request) {
          changeLanguageCulture::languageCulture($request, $this);
          $this->error_msg="";
           $this->product_id = '';
        $cst = new Criteria();
        $cst->add(ProductPeer::PRODUCT_TYPE_ID, 6);
        $this->simtypes = ProductPeer::doSelect($cst);
       
     }
      public function executePurchaseNewSimDetail(sfWebRequest $request) {
          
      
          changeLanguageCulture::languageCulture($request, $this);
        $simTypeId=$request->getParameter('sim_type');
         $mobile_number = $request->getParameter('mobile_number');
            $uc = new Criteria();
           $uc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $uc->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $uc->addAnd(CustomerPeer::BLOCK, 0);
         $this->customer = CustomerPeer::doSelectOne($uc);
         
         
         
          $this->error_msg="";
           $this->product_id = '';
        $cst = new Criteria();
        $cst->add(ProductPeer::ID, $simTypeId);
        $simtype = ProductPeer::doSelectOne($cst);
         $this->product= $simtype;
        $this->product_id = $simtype->getId();
           $this->price = $simtype->getRegistrationFee();
            $this->vat = $this->price * sfConfig::get('app_vat_percentage');
            $this->total = $this->price + $this->vat;
        
        
        
        
        
        
        
        
       
     }    
      public function executeValidateCustomer(sfWebRequest $request){

        $mobile_number = $request->getParameter('mobile_number');
        $uc = new Criteria();
        $uc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $uc->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $uc->addAnd(CustomerPeer::BLOCK, 0);
        $availableUniqueCount = CustomerPeer::doCount($uc);
        if($availableUniqueCount == 1){
            echo "true";
        }else{
            echo "false";
        }

        return sfView::NONE;
   } 
   
   public function executePurchaseNewSimProcess(sfWebRequest $request) { 
   
      $NewUniqueId=  $request->getParameter('uniqueId');
  $st = new Criteria();
            $st->add(ProductPeer::ID, $request->getParameter('productId'));
            $simtype = ProductPeer::doSelectOne($st);
            $this->product_id = $simtype->getId();
            $this->price = $simtype->getRegistrationFee();
            $this->vat = $this->price * sfConfig::get('app_vat_percentage');
            $this->total = $this->price + $this->vat;
            
              $this->customer=  CustomerPeer::retrieveByPK($request->getParameter('customerId')); 
              $customer=$this->customer;
              $product=$simtype;
        //////////////////////////////      
       $is_recharged = true;
          $agentcomession=FALSE;
          $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
         $c = new Criteria();
       
        //get Agent commission package
        $cpc = new Criteria();
        $cpc->add(AgentCommissionPackagePeer::ID, $agent->getAgentCommissionPackageId());
        $commission_package = AgentCommissionPackagePeer::doSelectOne($cpc);
        
      $transaction = new Transaction();
            $order = new CustomerOrder();
            $extra_refill=$request->getParameter('totalAmount');
      
                $order->setCustomerId($customer->getId());
                $order->setProductId($product->getId());
                $order->setQuantity(1);
               $order->setExtraRefill(0);
                 $order->setIsFirstOrder(6);
                $order->setOrderStatusId(sfConfig::get('app_status_new'));
                $order->save();
                 
                 $transaction = new Transaction();

            $transaction->setAmount($this->total);
            $transaction->setOrderId($order->getId());
            $transaction->setCustomerId($customer->getId());
            $transactiondescription = TransactionDescriptionPeer::retrieveByPK(14);
            $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
            $transaction->setTransactionDescriptionId($transactiondescription->getId());
            $this->transaction_title=$transactiondescription->getTitle();
            $transaction->setDescription($this->transaction_title);
            $transaction->setVat($this->vat);
            $transaction->setTransactionStatusId(1);
            $transaction->save();
            /////////////////////////////////////////////
            
            
             $cst = new Criteria();
        $cst->add(SimTypesPeer::ID, $order->getProduct()->getSimTypeId());
        $simtype = SimTypesPeer::doSelectOne($cst);
      $sim_type_id=$simtype->getId();
        $exest = $order->getExeStatus();
        if ($exest!=1) {

            $uniqueId=$this->customer->getUniqueid();
            $cb = new Criteria();
            $cb->add(CallbackLogPeer::UNIQUEID, $uniqueId);
            $cb->addDescendingOrderByColumn(CallbackLogPeer::CREATED);
            $activeNumber = CallbackLogPeer::doSelectOne($cb);

            $uc = new Criteria();
            $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 2);
            $uc->addAnd(UniqueIdsPeer::STATUS, 0);
            $uc->addAnd(UniqueIdsPeer::SIM_TYPE_ID,$sim_type_id);
            $uc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER,$NewUniqueId);
            
           
            $availableUniqueCount = UniqueIdsPeer::doCount($uc);
            
            $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);

            if($availableUniqueCount  == 0){
                // Unique Ids are not avaialable. Then Redirect to the sorry page and send email to the support.
                emailLib::sendUniqueIdsShortage();
                $this->redirect($this->getTargetUrl().'customer/shortUniqueIds');
            }
  $callbacklog = new CallbackLog();
            $callbacklog->setMobileNumber($activeNumber->getMobileNumber());
            $callbacklog->setuniqueId($availableUniqueId->getUniqueNumber());
            $callbacklog->setcallingCode(sfConfig::get('app_country_code'));
            $callbacklog->save();

            $uniqueidlog = new UniqueidLog();
            $uniqueidlog->setCustomerId($this->customer->getId());
            $uniqueidlog->setUniqueNumber($uniqueId);
            $uniqueidlog->save();

            $availableUniqueId->setStatus(1);
            $availableUniqueId->setAssignedAt(date('Y-m-d H:i:s'));
            $availableUniqueId->save();

            $this->customer->setUniqueid($availableUniqueId->getUniqueNumber());
            $this->customer->setSimTypeId($sim_type_id);
            $this->customer->save();

          
        }
            
            /////////////////////////////////////////////////////
            
                $transaction->setAgentCompanyId($agent->getId());

                $order->setAgentCommissionPackageId($agent->getAgentCommissionPackageId());

                $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');

                $cp = new Criteria;
                $cp->add(AgentProductPeer::AGENT_ID, $agent_company_id);
                $cp->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                $agentproductcount = AgentProductPeer::doCount($cp);
                if ($agentproductcount > 0) {
                    $p = new Criteria;
                    $p->add(AgentProductPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                    $p->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                    $agentproductcomesion = AgentProductPeer::doSelectOne($p);
                    $agentcomession = $agentproductcomesion->getExtraPaymentsShareEnable();
                }

                ////////   commission setting  through  agent commision//////////////////////

                if ($agentcomession) {
                    if ($agentproductcomesion->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $agentproductcomesion->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($agentproductcomesion->getExtraPaymentsShareValue());
                    }
                } else {
                    if ($commission_package->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $commission_package->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($commission_package->getExtraPaymentsShareValue());
                    }
                }
                //calculated amount for agent commission
                if ($agent->getIsPrepaid() == true) {
                    if ($agent->getBalance() < ($transaction->getAmount() - $transaction->getCommissionAmount())) {
                        $is_recharged = false;
                        $balance_error = 1;
                    }
                }

                if ($is_recharged) {
                    $transaction->save();
                    if ($agent->getIsPrepaid() == true) {
                        $agent->setBalance($agent->getBalance() - ($transaction->getAmount() - $transaction->getCommissionAmount()));
                        $agent->save();
                        $remainingbalance = $agent->getBalance();
                        $amount = $transaction->getAmount() - $transaction->getCommissionAmount();
                        $amount = -$amount;
                        $aph = new AgentPaymentHistory();
                        $aph->setAgentId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                        $aph->setCustomerId($transaction->getCustomerId());
                        $aph->setExpeneseType(2);
                        $aph->setAmount($amount);
                        $aph->setRemainingBalance($remainingbalance);
                        $aph->save();
                    }

                  
                    $order->setOrderStatusId(sfConfig::get('app_status_completed'));
                    $transaction->setTransactionStatusId(sfConfig::get('app_status_completed'));
                    $order->setExeStatus(1);
                    $order->save();
                    $transaction->save();
                    $this->customer = $order->getCustomer();
                    //  $this->getUser()->setCulture('de');
                   
                    
                      $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerNewcardEmailAgent($this->customer, $order, $transaction,$agent_company_id);
            $this->updatePreferredCulture();
                    //   $this->getUser()->setCulture('en');
                    $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('new sim is purchased successfully '));
//                                      echo 'rehcarged, redirecting';
                    $this->redirect('affiliate/receipts');
                } else {
//                                        echo 'NOT rehcarged, redirecting';
                    $this->balance_error = 1;
                    $this->getUser()->setFlash('error', 'You do not have enough balance, please recharge');
                } //end else
    
     return sfView::NONE;

        ////////////////////////////      
           
            //new transaction
           
    
          
        }

       public function executeValidateUniqeId(sfWebRequest $request){

        $uniqueId= $request->getParameter('uniqueId');
        $sim_type_id= $request->getParameter('sim_type');
        $uc = new Criteria();
        $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 2);
        $uc->addAnd(UniqueIdsPeer::STATUS, 0);
        $uc->addAnd(UniqueIdsPeer::SIM_TYPE_ID,$sim_type_id);
         $uc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER,$uniqueId);
        $availableUniqueCount = UniqueIdsPeer::doCount($uc);
        if($availableUniqueCount == 1){
            echo "true";
        }else{
            echo "false";
        }

        return sfView::NONE;
   }  
   public function executeChangeProductService(sfWebRequest $request) {
          changeLanguageCulture::languageCulture($request, $this);
          $this->error_msg="";
           $this->product_id = '';
        $cst = new Criteria();
        $cst->add(ProductPeer::PRODUCT_TYPE_ID, 1);
         $cst->addAnd(ProductPeer::IS_IN_STORE, 1);
        $this->simtypes = ProductPeer::doSelect($cst);
     }
      public function executeChangeProductServiceDetail(sfWebRequest $request){
      
          changeLanguageCulture::languageCulture($request, $this);
        $simTypeId=$request->getParameter('sim_type');
         $mobile_number = $request->getParameter('mobile_number');
            $uc = new Criteria();
           $uc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $uc->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $uc->addAnd(CustomerPeer::BLOCK, 0);
         $this->customer = CustomerPeer::doSelectOne($uc);
         
         
         
         
           $op = new Criteria();
           $op->add(CustomerProductPeer::CUSTOMER_ID, $this->customer->getId());
        $op->addAnd(CustomerProductPeer::STATUS_ID, 3);
    //    $uc->addAnd(CustomerPeer::BLOCK, 0);
         $opproduct = CustomerProductPeer::doSelectOne($op);
       
         if($opproduct->getId==$simTypeId){
          
               $this->getUser()->setFlash('message', 'Customer Already have this Product');
                $this->redirect('affiliate/changeProductService');    
            
             
         }
         
            $cop = new Criteria();
           $cop->add(CustomerChangeProductPeer::CUSTOMER_ID, $this->customer->getId());
        $cop->addAnd(CustomerChangeProductPeer::STATUS, 2);
            $cop->addAnd(CustomerChangeProductPeer::PRODUCT_ID, $simTypeId);
     
         $copproductCount = CustomerChangeProductPeer::doCount($cop);
       
         if($copproductCount>0){
            $this->getUser()->setFlash('message', 'Customer Already Subscribed for this Product');
                $this->redirect('affiliate/changeProductService');      
         }
         
         
          $this->error_msg="";
           $this->product_id = '';
        $cst = new Criteria();
        $cst->add(ProductPeer::ID, $simTypeId);
        $simtype = ProductPeer::doSelectOne($cst);
         $this->product= $simtype;
           $cstx = new Criteria();
        $cstx->add(ProductPeer::ID, 16);
        $simtypex = ProductPeer::doSelectOne($cstx);
         $this->productx= $simtypex;
         
         
         
        $this->product_id = $simtype->getId();
           $this->price = $simtypex->getRegistrationFee();
            $this->vat = $this->price * sfConfig::get('app_vat_percentage');
            $this->total = $this->price + $this->vat;
               
     }     
  
    public function executeChangeProductProcess(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK($request->getParameter('customerId'));
      
        $this->targetUrl = $this->getTargetUrl();

        $product_id = $request->getParameter('productId');
        $this->oldProduct = ProductPeer::retrieveByPK($product_id);

        $product = ProductPeer::retrieveByPK(16);
        $this->product = $product;
        $this->product_id = $product->getId();
        $this->price = $product->getRegistrationFee();
        $this->vat = $this->price * sfConfig::get('app_vat_percentage');
        $this->total = $this->price + $this->vat;

        
         $is_recharged = true;
          $agentcomession=FALSE;
          $ca = new Criteria();
        $ca->add(AgentCompanyPeer::ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
        $agent = AgentCompanyPeer::doSelectOne($ca);
         $c = new Criteria();
       
        //get Agent commission package
        $cpc = new Criteria();
        $cpc->add(AgentCommissionPackagePeer::ID, $agent->getAgentCommissionPackageId());
        $commission_package = AgentCommissionPackagePeer::doSelectOne($cpc);
        
           $transaction = new Transaction();
        $order = new CustomerOrder();
        $order->setCustomerId($this->customer->getId());
        $order->setProductId($product->getId());
        $order->setQuantity(1);
        $order->setExtraRefill($product->getInitialBalance());
        $order->setOrderStatusId(3);
         $order->setExeStatus(1);
        $order->setIsFirstOrder(7);
///////////////////agent area //////////////////////////
           $transaction->setAgentCompanyId($agent->getId());

                $order->setAgentCommissionPackageId($agent->getAgentCommissionPackageId());

                $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession');

                $cp = new Criteria;
                $cp->add(AgentProductPeer::AGENT_ID, $agent_company_id);
                $cp->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                $agentproductcount = AgentProductPeer::doCount($cp);
                if ($agentproductcount > 0) {
                    $p = new Criteria;
                    $p->add(AgentProductPeer::AGENT_ID, $agent_company_id = $this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                    $p->add(AgentProductPeer::PRODUCT_ID, $order->getProductId());
                    $agentproductcomesion = AgentProductPeer::doSelectOne($p);
                    $agentcomession = $agentproductcomesion->getExtraPaymentsShareEnable();
                }

                ////////   commission setting  through  agent commision//////////////////////

                if ($agentcomession) {
                    if ($agentproductcomesion->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $agentproductcomesion->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($agentproductcomesion->getExtraPaymentsShareValue());
                    }
                } else {
                    if ($commission_package->getIsExtraPaymentsShareValuePc()) {
                        $transaction->setCommissionAmount(($transaction->getAmount() / 100) * $commission_package->getExtraPaymentsShareValue());
                    } else {
                        $transaction->setCommissionAmount($commission_package->getExtraPaymentsShareValue());
                    }
                }
                //calculated amount for agent commission
                if ($agent->getIsPrepaid() == true) {
                    if ($agent->getBalance() < ($transaction->getAmount() - $transaction->getCommissionAmount())) {
                        $is_recharged = false;
                        $balance_error = 1;
                    }
                }

                if ($is_recharged) {
                    $transaction->save();
                    if ($agent->getIsPrepaid() == true) {
                        $agent->setBalance($agent->getBalance() - ($transaction->getAmount() - $transaction->getCommissionAmount()));
                        $agent->save();
                        $remainingbalance = $agent->getBalance();
                        $amount = $transaction->getAmount() - $transaction->getCommissionAmount();
                        $amount = -$amount;
                        $aph = new AgentPaymentHistory();
                        $aph->setAgentId($this->getUser()->getAttribute('agent_company_id', '', 'agentsession'));
                        $aph->setCustomerId($transaction->getCustomerId());
                        $aph->setExpeneseType(2);
                        $aph->setAmount($amount);
                        $aph->setRemainingBalance($remainingbalance);
                        $aph->save();
                    }

        ////////////////////////agent area end/////////////////
        
        
        
        
        

        $order->save();
        $this->order = $order;
        //create transaction
     
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($this->customer->getId());
        $transaction->setAmount($this->total);
        $transactiondescription = TransactionDescriptionPeer::retrieveByPK(15);
        $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
        $transaction->setTransactionDescriptionId($transactiondescription->getId());
        $transaction->setDescription($transactiondescription->getTitle());
        $transaction->setTransactionStatusId(3);
        $transaction->setVat($this->vat);
           
        $transaction->save();
        $ccp = new CustomerChangeProduct();
        $ccp->setCustomerId($this->customer->getId());
        $ccp->setProductId($product_id);
        $ccp->setCreatedAt(Date());
        $ccp->setStatus(2);
        $ccp->setOrderId($order->getId());
        $ccp->setTransactionId($transaction->getId());
        $ccp->save();
               
            $uniqueId=$this->customer->getUniqueid();
            $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerChangeProductAgent($this->customer, $order, $transaction);
            $this->updatePreferredCulture();
            
            $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Customer  is Subscribed  for new Product'));
//                                      echo 'rehcarged, redirecting';
                    $this->redirect('affiliate/receipts');
               
        
            } else {
//                                        echo 'NOT rehcarged, redirecting';
                    $this->balance_error = 1;
                    $this->getUser()->setFlash('error', 'You do not have enough balance, please recharge');
                }  
                 return sfView::NONE;
    }


         
     
}
