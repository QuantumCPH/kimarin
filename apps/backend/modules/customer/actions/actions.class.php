<?php
require_once(sfConfig::get('sf_lib_dir').'/parsecsv.lib.php');
require_once(sfConfig::get('sf_lib_dir').'/ForumTel.php');
/**
 * customer actions.
 *
 * @package    zapnacrm
 * @subpackage customer
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php,v 1.1 2010-08-05 20:37:53 orehman Exp $
 */
class customerActions extends autocustomerActions
{


     public function executeDeActivateCustomer(sfWebRequest $request)
  {

     $response_text = 'Response From Server: <br/>';
     $this->response_text=$response_text;

     if (isset($_GET['customer_id'])) {
     $deactive_code=6;
     $removal_code=0;

     $customer_id = $request->getParameter('customer_id');
     $response_text .= 'searching for customer id = '.$customer_id;
     $response_text .= '<br/>';

     $this->response_text=$response_text;

     $c = new Criteria();
     $c->add(CustomerPeer::ID,$customer_id );
     $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 6, Criteria::NOT_EQUAL);
     $customer = CustomerPeer::doSelectOne($c);
     if(!$customer){

        $response_text .= 'Customer not active, exiting';
        $response_text .= '<br/>';

        $this->response_text=$response_text;


     }else{

     $response_text .= "Customer Found";
     $response_text .="<br/>";

     $response_text .="Mobile Number = ".$customer->getMobileNumber()." , Fonet ID = ".$customer->getFonetCustomerId();
     $response_text .="<br/>";
     if (Fonet::unregister($customer, false))
                     $response_text .= sprintf("%s is unregistered<br />", $customer->getMobileNumber());
     if ($current_balance = Fonet::getBalance($customer, false))
                      $response_text .= sprintf("Current balance of custoemr is %s<br />", $current_balance);
      if (Fonet::recharge($customer, -$current_balance, false))
                      $response_text .= sprintf("current balance is made 0<br />");

      $con = Propel::getConnection();

      $con->exec('set foreign_key_checks=0');
      $response_text .= 'disabling foreign keys';
      $response_text .= '<br/>';


      $con->exec('UPDATE transaction  SET  transaction_status_id=6  where customer_id='.$customer_id);
      $response_text .= 'transactions deleted';
      $response_text .= '<br/>';

      $con->exec('UPDATE customer_order  SET  order_status_id=6 where customer_id='.$customer_id);
      $response_text .= 'customer order deleted';
      $response_text .= '<br/>';

   //   $con->exec('DELETE FROM customer_product where customer_id='.$customer_id);
     // $response_text .= 'customer product deleted';
     // $response_text .= '<br/>';

      if($customer->getFonetCustomerId()!=NULL){
      $con->exec("UPDATE fonet_customer SET activated_on = NULL where fonet_customer_id=".$customer->getFonetCustomerId());
      $response_text .= 'fonet id freed ';
      $response_text .= '<br/>';
      }

      $con->exec('Update customer set customer_status_id='.$deactive_code.' where id='.$customer_id);
      $response_text .= 'customer status set to 6 (de-activated)';
      $response_text .= '<br/>';

      $response_text .= "Customer De-activated, Customer Id=".$customer_id;
      $response_text .= '<br/>';

      $response_text .= "Exiting gracefully ... done!";
     


      $this->response_text=$response_text;
     }
     }

      $this->response_text=$response_text;

  }


    public function executeRegisteredByWeb(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, NULL );
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);       
        $this->customers = CustomerPeer::doSelect($c);
           
    }

    public function executeRegisteredByAgent(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, 0, Criteria::GREATER_THAN );

        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
         $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 2);
        $this->customers = CustomerPeer::doSelect($c);

    }

    public function executeRegisteredByApp(sfWebRequest $request)
    {
        $c= new Criteria();
        //$c->add(CustomerPeer::REFERRER_ID, 0, Criteria::GREATER_THAN );
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
         $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 5);
        $this->customers = CustomerPeer::doSelect($c);

    }
    
    public function executeRegisteredBySms(sfWebRequest $request)
    {
        $c= new Criteria();                
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
         $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 4);
        $this->customers = CustomerPeer::doSelect($c);
        
    }

    public function executeRegisteredByAgentLink(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, 0, Criteria::GREATER_THAN );
        $c->add(CustomerPeer::SUBSCRIPTION_ID, 0, Criteria::GREATER_THAN);
         $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 3);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $this->customers = CustomerPeer::doSelect($c);

    }


    public function executePartialRegisteredByWeb(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, NULL );
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3, Criteria::NOT_EQUAL);
         $c->add(CustomerPeer::REGISTRATION_TYPE_ID, 1);
        $this->customers = CustomerPeer::doSelect($c);

    }


    public function executePartialRegisteredByAgent(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, 0, Criteria::GREATER_THAN );
        $c->add(CustomerPeer::SUBSCRIPTION_ID, NULL);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3, Criteria::NOT_EQUAL);
        $this->customers = CustomerPeer::doSelect($c);
    }

    public function executePartialRegisteredByAgentLink(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::REFERRER_ID, 0, Criteria::GREATER_THAN );
        $c->add(CustomerPeer::SUBSCRIPTION_ID, 0, Criteria::GREATER_THAN);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3, Criteria::NOT_EQUAL);
        $this->customers = CustomerPeer::doSelect($c);

    }

     public function executeAllRegisteredCustomer(sfWebRequest $request)
    {
        $c= new Criteria();
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $this->customers = CustomerPeer::doSelect($c);

    }
  public function executeCustomerDetail(sfWebRequest $request)
    {

        $id = $request->getParameter('id');
        $c= new Criteria();
        $c->add(CustomerPeer::ID, $id);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $this->customer = CustomerPeer::doSelectOne($c);

//$this->customer_balance =Telienta::getBalance($this->customer->getUniqueid());
        $this->customer_balance =Telienta::getBalance($this->customer);
    }
public function executePaymenthistory(sfWebRequest $request)
	{


		$this->customer = CustomerPeer::retrieveByPK($request->getParameter('id'));

		$this->redirectUnless($this->customer, "@homepage");

		//get  transactions
		$c = new Criteria();

		$c->add(TransactionPeer::CUSTOMER_ID, $this->customer->getId());
		$c->add(TransactionPeer::TRANSACTION_STATUS_ID,3);
		/*
		if (isset($request->getParameter('filter')))
		{
			$filter = $request->getParameter('filter');

			$phone_number = isset($filter['phone_number'])?$filter['phone_number']:null;

			$from_date = isset($filter['from_date'])?$filter['from_date']:null;
			$to_date = isset($filter['to_date'])?$filter['to_date']:null;

			if ($phone_number)
				$c->add(CustomerPeer::MOBILE_NUMBER, $phone_number);
			if ($from_date)
				$c->add(TransactionPeer::CREATED_AT, $from_date, Criteria::GREATER_EQUAL);
			if ($to_date && !$from_date)
				$c->add(TransactionPeer::CREATED_AT, $to_date . ' 23:59:59', Criteria::LESS_EQUAL);
			elseif ($to_date && $from_date)
				$c->addAnd(TransactionPeer::CREATED_AT, $to_date . ' 23:59:59', Criteria::LESS_EQUAL);

		}
		*/
                
                $country_id = $this->customer->getCountryId();
                $enableCountry = new Criteria();
                $enableCountry->add(EnableCountryPeer::ID, $country_id);
                $country_id = EnableCountryPeer::doSelectOne($enableCountry);//->getId();
                if($country_id){
                    $langSym = $country_id->getLanguageSymbol();
                }else{
                    $langSym = 'no';
                }
                //--------------------------------------------------------
                //$lang =  $this->getUser()->getAttribute('activelanguage');
                $lang =  $langSym;
                $this->lang = $lang;
                //--------------------------------------------------------

		$c->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
             $this->transactions =TransactionPeer::doSelect($c);
		//set paging
//		$items_per_page = 10; //shouldn't be 0
//		$this->page = $request->getParameter('page');
//        if($this->page == '') $this->page = 1;
//
//        $pager = new sfPropelPager('Transaction', $items_per_page);
//        $pager->setPage($this->page);
//
//        $pager->setCriteria($c);
//
//        $pager->init();

      //  $this->transactions = $pager->getResults();
		//$this->total_pages = $pager->getNbResults() / $items_per_page;



	}
    public function executeCallhistory(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK($request->getParameter('id'));
        $this->redirectUnless($this->customer, "@homepage");

        $fromdate = mktime(0, 0, 0, date("m"), date("d") - 15, date("Y"));
        $this->fromdate = date("Y-m-d", $fromdate);
        $todate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $this->todate = date("Y-m-d", $todate);

        if ($request->isMethod('post')) {
            $this->fromdate = $request->getParameter('startdate');
            $this->todate = $request->getParameter('enddate');
        }



        $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);
        if ($getFirstnumberofMobile == 0) {
            $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
            $this->TelintaMobile = '47' . $TelintaMobile;
        } else {
            $this->TelintaMobile = '47' . $this->customer->getMobileNumber();
        }

        $this->numbername = $this->customer->getUniqueid();
    }

    public function executeEditcustomer(sfWebRequest $request)
    {
     if($request->getParameter('id')){
         $customer = new Criteria();
         $customer->add(CustomerPeer::ID, $request->getParameter('id'));
         $this->editCust = CustomerPeer::doSelectOne($customer);

     }
     if($request->getParameter('customerID')){
      $dob = $request->getParameter('dy')."-".$request->getParameter('dm')."-".$request->getParameter('dd');
      $dob = date('Y-m-d',strtotime($dob));

      $usage_email = $request->getParameter('usage_email');
      ($usage_email=="")?$usage_email = 0:$usage_email = 1;

      $usage_sms = $request->getParameter('usage_sms');
      ($usage_sms=="")?$usage_sms = 0:$usage_sms = 1;

      $customer = CustomerPeer::retrieveByPK($request->getParameter('customerID'));
      $customer->setFirstName($request->getParameter('firstName'));
      $customer->setLastName($request->getParameter('lastName'));
      $customer->setAddress($request->getParameter('address'));
      $customer->setCity($request->getParameter('city'));
      $customer->setPoBoxNumber($request->getParameter('pob'));
      $customer->setEmail($request->getParameter('email'));
      $customer->setDateOfBirth($dob);
      $customer->setUsageAlertEmail($usage_email);
      $customer->setUsageAlertSMS($usage_sms);

      $customer->save();

          $this->message = "Customer has been updated.";

     }
    }


 public function executeChargeCustomer(sfWebRequest $request)
    {
          if(($request->getParameter('mobile_number')) && $request->getParameter('charge_amount')!=''){
             $validated = false;
            $mobile_number = $request->getParameter('mobile_number');
            $extra_refill = $request->getParameter('charge_amount');
            $is_recharged = true;
            $transaction = new Transaction();
            $order = new CustomerOrder();
            $customer = NULL;
            $cc = new Criteria();
            $cc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
            $cc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            //$cc->add(CustomerPeer::FONET_CUSTOMER_ID, NULL, Criteria::ISNOTNULL);
            $customer = CustomerPeer::doSelectOne($cc);
            //echo $customer->getId();
            if ($customer and $mobile_number != "") {
                $validated = true;
            } else {
                $validated = false;
                $is_recharged = false;
                $this->error_mobile_number = 'invalid mobile number';
                return;
            }
//			echo 'validating form';
            if ($validated) {
                 $c = new Criteria();
                $c->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
                $customer_product = CustomerProductPeer::doSelectOne($c)->getProduct();
                $order->setCustomerId($customer->getId());
                $order->setProductId($customer_product->getId());
                $order->setQuantity(1);
                $order->setExtraRefill(-$extra_refill);
                $order->setIsFirstOrder(false);
                $order->setOrderStatusId(1);
                //$order->setAgentCommissionPackageId($agent->getAgentCommissionPackageId());
                $order->save();
                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customer->getId());
                $transaction->setAmount(-$extra_refill);
                //get agent name
                $transaction->setDescription($request->getParameter('transaction_description'));
                $transaction->setTransactionFrom(2);

                    $transaction->save();
                    Telienta::charge($customer, $extra_refill,$request->getParameter('transaction_description'));
                    //set status
                    $order->setOrderStatusId(3);
                    $transaction->setTransactionStatusId(3);
                    $order->save();
                    $transaction->save();
                    $this->customer = $order->getCustomer();
                    emailLib::sendAdminRefillEmail($this->customer, $order);
                    $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('%1% account is successfully charged with %2% NOK.', array("%1%" => $customer->getMobileNumber(), "%2%" => $transaction->getAmount())));
//                                        echo 'rehcarged, redirecting';
                    $this->redirect($this->getTargetURL() . 'customer/selectChargeCustomer');
                } else {
//                                        echo 'NOT rehcarged, redirecting';
                    $this->balance_error = 1;

                } //end else
            } else {
//              echo 'Form Invalid, redirecting';
                $this->balance_error = 1;
                //$this->getUser()->setFlash('message', 'Invalid mobile number');
                //$this->getUser()->setFlash('error_message', 'Customer Not Found.');
                $is_recharged = false;
                $this->error_mobile_number = 'invalid mobile number';
                 $this->redirect($this->getTargetURL() . 'customer/selectChargeCustomer');

        }
  $this->redirect($this->getTargetURL() . 'customer/selectChargeCustomer');
        return sfView::NONE;
    }
 public function executeRefillCustomer(sfWebRequest $request)
    {
      if(($request->getParameter('mobile_number')) && $request->getParameter('refill_amount')!=''){
            $validated = false;
            $mobile_number = $request->getParameter('mobile_number');
            $extra_refill = $request->getParameter('refill_amount');
            $is_recharged = true;
            $transaction = new Transaction();
            $order = new CustomerOrder();
            $customer = NULL;
            $cc = new Criteria();
            $cc->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
            $cc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            //$cc->add(CustomerPeer::FONET_CUSTOMER_ID, NULL, Criteria::ISNOTNULL);
            $customer = CustomerPeer::doSelectOne($cc);
            //echo $customer->getId();

            if ($customer and $mobile_number != "") {
                $validated = true;
            } else {
                $validated = false;
                $is_recharged = false;
                $this->error_mobile_number = 'invalid mobile number';
                return;
            }
//			echo 'validating form';
            if ($validated) {
                //if(true)
//                                echo "form valid";
//                                echo '<br />';
                //create order
                //get customer first product purchase
                $c = new Criteria();
                $c->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
                $customer_product = CustomerProductPeer::doSelectOne($c)->getProduct();
                $order->setCustomerId($customer->getId());
                $order->setProductId($customer_product->getId());
                $order->setQuantity(1);
                $order->setExtraRefill($extra_refill);
                $order->setIsFirstOrder(false);
                $order->setOrderStatusId(1);
                //$order->setAgentCommissionPackageId($agent->getAgentCommissionPackageId());
                $order->save();
                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customer->getId());
                $transaction->setAmount($extra_refill);
                //get agent name
                $transaction->setDescription($request->getParameter('transaction_description'));
                $transaction->setTransactionFrom('2');
                $transaction->save();
                Telienta::recharge($customer, $transaction->getAmount(),$request->getParameter('transaction_description'));
                //set status
                $order->setOrderStatusId(3);
                $transaction->setTransactionStatusId(3);
                $order->save();
                $transaction->save();
                $this->customer = $order->getCustomer();
                emailLib::sendAdminRefillEmail($this->customer, $order);
                $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('%1% account is successfully refilled with %2% NOK.', array("%1%" => $customer->getMobileNumber(), "%2%" => $transaction->getAmount())));
                //                                        echo 'rehcarged, redirecting';
                $this->redirect($this->getTargetURL() . 'customer/selectRefillCustomer');
                } else {
                //                                        echo 'NOT rehcarged, redirecting';
                $this->balance_error = 1;

                } //end else
                } else {
                //              echo 'Form Invalid, redirecting';
                $this->balance_error = 1;
                //$this->getUser()->setFlash('message', 'Invalid mobile number');
                //$this->getUser()->setFlash('error_message', 'Customer Not Found.');
                $is_recharged = false;
                $this->error_mobile_number = 'invalid mobile number';
                $this->redirect($this->getTargetURL() . 'customer/selectRefillCustomer');

        }
  $this->redirect($this->getTargetURL() . 'customer/selectRefillCustomer');
    return sfView::NONE;
    }

 public function executeSelectRefillCustomer($request){
        $ct = new Criteria();
        $ct->add(TransactionDescriptionPeer::TRANSACTION_TYPE_ID,1); // For refill
        $ct->add(TransactionDescriptionPeer::B2C,1); // For refill
        $ct->addAnd(TransactionDescriptionPeer::TRANSACTION_SECTION_ID,1); // 1, Description is for Admin and 2, for  Agent
        $this->transactionDescriptions = TransactionDescriptionPeer::doSelect($ct);
     }

  public function executeSelectChargeCustomer($request){
        $ct = new Criteria();
        $ct->add(TransactionDescriptionPeer::TRANSACTION_TYPE_ID,2); // For charge
        $ct->add(TransactionDescriptionPeer::B2C,1); // For charge
        $ct->addAnd(TransactionDescriptionPeer::TRANSACTION_SECTION_ID,1); // 1, Description is for Admin and 2, for  Agent
        $this->transactionDescriptions = TransactionDescriptionPeer::doSelect($ct);
    }
public function getTargetURL() {
        return sfConfig::get('backend_url');
        //return $this->targetURL;
    }

public function executeCompletePaymenthistory(sfWebRequest $request)
	{


                 $tr = new Criteria();
  		 $tr->add(TransactionPeer::TRANSACTION_STATUS_ID,3);
                 $tr->addGroupbycolumn(TransactionPeer::DESCRIPTION);
                 $alltransaction= TransactionPeer::doSelect($tr);
                 $this->alltransactions=$alltransaction;
		$c = new Criteria();
		$c->add(TransactionPeer::TRANSACTION_STATUS_ID,3);
    if(isset($_POST['startdate']) && $_POST['startdate']!=""){

                    $this->startdate=$request->getParameter('startdate');
                    $startdate=$request->getParameter('startdate')." 00:00:00";
                    $c->addAnd(TransactionPeer::CREATED_AT,$startdate,Criteria::GREATER_THAN);
                    }
                    if(isset($_POST['enddate']) && $_POST['enddate']!=""){
                    $this->enddate=$request->getParameter('enddate');
                    $enddate=$request->getParameter('enddate')." 23:59:59";
                    $c->addAnd(TransactionPeer::CREATED_AT,$enddate,Criteria::LESS_THAN);
                 }
               if(isset($_POST['description']) && $_POST['description']!=""){
                    $this->description=$request->getParameter('description');
                    $c->addAnd(TransactionPeer::DESCRIPTION,$request->getParameter('description'));
                    }
                    $enableCountry = new Criteria();
                    $enableCountry->add(EnableCountryPeer::ID,2);
                    $country_id = EnableCountryPeer::doSelectOne($enableCountry);//->getId();
                    if($country_id){
                    $langSym = $country_id->getLanguageSymbol();
                    }else{
                    $langSym = 'no';
                    }
                    //--------------------------------------------------------
                    //$lang =  $this->getUser()->getAttribute('activelanguage');
                    $lang =  $langSym;
                    $this->lang = $lang;
                    //--------------------------------------------------------

                    $c->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
                    //set paging
                    $items_per_page = 50000; //shouldn't be 0
                    $this->page = $request->getParameter('page');
                    if($this->page == '') $this->page = 1;
                    $pager = new sfPropelPager('Transaction', $items_per_page);
                    $pager->setPage($this->page);
                    $pager->setCriteria($c);
                    $pager->init();
                    $this->transactions = $pager->getResults();
                    $this->total_pages = $pager->getNbResults() / $items_per_page;

	}


}
