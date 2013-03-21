<?php

set_time_limit(10000000);
require_once(sfConfig::get('sf_lib_dir') . '/changeLanguageCulture.php');
require_once(sfConfig::get('sf_lib_dir') . '/emailLib.php');
require_once(sfConfig::get('sf_lib_dir') . '/ForumTel.php');
require_once(sfConfig::get('sf_lib_dir') . '/commissionLib.php');
require_once(sfConfig::get('sf_lib_dir') . '/curl_http_client.php');
require_once(sfConfig::get('sf_lib_dir') . '/smsCharacterReplacement.php');
require_once(sfConfig::get('sf_lib_dir') . '/zerocall_out_sms.php');

/**
 * scripts actions.
 *
 * @package    Zapna
 * @subpackage scripts
 * @author     Baran Khursheed Khan
 * @version    actions.class.php,v 1.5 2012-01-16 22:20:12 BK Exp $
 */
class pScriptsActions extends sfActions {

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    private $currentCulture;

    public function executeMobAccepted(sfWebRequest $request) {
        $order_id = $request->getParameter("orderid");

        $this->forward404Unless($order_id || $order_amount);

        $order = CustomerOrderPeer::retrieveByPK($order_id);

        $subscription_id = $request->getParameter("subscriptionid");
        $order_amount = ((double) $request->getParameter('amount')) / 100;

        $this->forward404Unless($order);

        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);

        $transaction = TransactionPeer::doSelectOne($c);

        //echo var_dump($transaction);

        $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
        //$order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed




        if ($transaction->getAmount() > $order_amount) {
            //error
            $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            //$order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_completed', 5)); //error in amount
        } else if ($transaction->getAmount() < $order_amount) {
            //$extra_refill_amount = $order_amount;
            $order->setExtraRefill($order_amount);
            $transaction->setAmount($order_amount);
        }





        //set active agent_package in case customer was registerred by an affiliate
        if ($order->getCustomer()->getAgentCompany()) {
            $order->setAgentCommissionPackageId($order->getCustomer()->getAgentCompany()->getAgentCommissionPackageId());
        }


        //set subscription id in case 'use current c.c for future auto refills' is set to 1
        if ($request->getParameter('USER_ATTR_20') == '1')
            $order->getCustomer()->setSubscriptionId($subscription_id);

        //set subscription id also when there is was no subscription for old customers
        if (!$order->getCustomer()->getSubscriptionId())
            $order->getCustomer()->setSubscriptionId($subscription_id);

        //set auto_refill amount
        if ($is_auto_refill_activated = $request->getParameter('USER_ATTR_1') == '1') {
            //set subscription id
            $order->getCustomer()->setSubscriptionId($subscription_id);

            //auto_refill_amount
            $auto_refill_amount_choices = array_keys(ProductPeer::getRefillHashChoices());

            $auto_refill_amount = in_array($request->getParameter('USER_ATTR_2'), $auto_refill_amount_choices) ? $request->getParameter('USER_ATTR_2') : $auto_refill_amount_choices[0];
            $order->getCustomer()->setAutoRefillAmount($auto_refill_amount);


            //auto_refill_lower_limit
            $auto_refill_lower_limit_choices = array_keys(ProductPeer::getAutoRefillLowerLimitHashChoices());

            $auto_refill_min_balance = in_array($request->getParameter('USER_ATTR_3'), $auto_refill_lower_limit_choices) ? $request->getParameter('USER_ATTR_3') : $auto_refill_lower_limit_choices[0];
            $order->getCustomer()->setAutoRefillMinBalance($auto_refill_min_balance);
        } else {
            //disable the auto-refill feature
            $order->getCustomer()->setAutoRefillAmount(0);
        }



        $order->save();
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);


        $this->customer = $order->getCustomer();
        $c = new Criteria;
        $c->add(CustomerPeer::ID, $order->getCustomerId());
        $customer = CustomerPeer::doSelectOne($c);
        $agentid = $customer->getReferrerId();
        $productid = $order->getProductId();
        $transactionid = $transaction->getId();
        if (isset($agentid) && $agentid != "") {
            commissionLib::refilCustomer($agentid, $productid, $transactionid);
        }

        //TODO ask if recharge to be done is same as the transaction amount
        Fonet::recharge($this->customer, $transaction->getAmount());





// Update cloud 9
        c9Wrapper::equateBalance($this->customer);


        //set vat
        $vat = 0;
        $subject = $this->getContext()->getI18N()->__('Payment Confirmation');
        $sender_email = sfConfig::get('app_email_sender_email', 'support@kimarin.es');
        $sender_name = sfConfig::get('app_email_sender_name', 'Kimarin support');

        $recepient_email = trim($this->customer->getEmail());
        $recepient_name = sprintf('%s %s', $this->customer->getFirstName(), $this->customer->getLastName());
        $referrer_id = trim($this->customer->getReferrerId());

        if ($referrer_id):
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);

            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        endif;

        //send email
        $message_body = $this->getPartial('payments/order_receipt', array(
            'customer' => $this->customer,
            'order' => $order,
            'transaction' => $transaction,
            'vat' => $vat,
            'wrap' => false
        ));



        /*
          require_once(sfConfig::get('sf_lib_dir').'/swift/lib/swift_init.php');

          $connection = Swift_SmtpTransport::newInstance()
          ->setHost(sfConfig::get('app_email_smtp_host'))
          ->setPort(sfConfig::get('app_email_smtp_port'))
          ->setUsername(sfConfig::get('app_email_smtp_username'))
          ->setPassword(sfConfig::get('app_email_smtp_password'));

          $mailer = new Swift_Mailer($connection);

          $message_1 = Swift_Message::newInstance($subject)
          ->setFrom(array($sender_email => $sender_name))
          ->setTo(array($recepient_email => $recepient_name))
          ->setBody($message_body, 'text/html')
          ;

          $message_2 = Swift_Message::newInstance($subject)
          ->setFrom(array($sender_email => $sender_name))
          ->setTo(array($sender_email => $sender_name))
          ->setBody($message_body, 'text/html')
          ;

          if (!($mailer->send($message_1) && $mailer->send($message_2)))
          $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__(
          "Email confirmation is not sent" ));
         */

        //This Seciton For Make The Log History When Complete registration complete - Agent
        //echo sfConfig::get('sf_data_dir');
        $invite_data_file = sfConfig::get('sf_data_dir') . '/invite.txt';
        $invite2 = "Customer Refill Account \n";
        $invite2 .= "Recepient Email: " . $recepient_email . ' \r\n';
        $invite2 .= " Agent Email: " . $recepient_agent_email . ' \r\n';
        $invite2 .= " Sender Email: " . $sender_email . ' \r\n';

        file_put_contents($invite_data_file, $invite2, FILE_APPEND);


        //Send Email to User/Agent/Support --- when Customer Refilll --- 01/15/11
        $this->setPreferredCulture($this->customer);
        emailLib::sendCustomerRefillEmail($this->customer, $order, $transaction);
        $this->updatePreferredCulture();
        $this->setLayout(false);
    }

    public function executeAutoRefill(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);

        //get customers to refill
        $c = new Criteria();

        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, sfConfig::get('app_status_completed'));
        $c->add(CustomerPeer::AUTO_REFILL_AMOUNT, 0, Criteria::NOT_EQUAL);
        $c->add(CustomerPeer::SUBSCRIPTION_ID, null, Criteria::ISNOTNULL);

        //$c1 = $c->getNewCriterion(CustomerPeer::LAST_AUTO_REFILL, 'TIMESTAMPDIFF(MINUTE, LAST_AUTO_REFILL, NOW()) > 1' , Criteria::CUSTOM);
        $c1 = $c->getNewCriterion(CustomerPeer::ID, null, Criteria::ISNOTNULL); //just accomodate missing disabled $c1
        $c2 = $c->getNewCriterion(CustomerPeer::LAST_AUTO_REFILL, null, Criteria::ISNULL);

        $c1->addOr($c2);

        $c->add($c1);

        $epay_con = new EPay();

        $customer = new Customer();

        var_dump(CustomerPeer::doCount($c));


        try {
            foreach (CustomerPeer::doSelect($c) as $customer) {

                $customer_balance = Fonet::getBalance($customer);

                var_dump($customer_balance);
                //if customer balance is less than 10
                if ($customer_balance != null && $customer_balance <= $customer->getAutoRefillMinBalance()) {



                    //create an order and transaction
                    $customer_order = new CustomerOrder();
                    $customer_order->setCustomer($customer);

                    //select order product
                    $c = new Criteria();
                    $c->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
                    $customer_product = CustomerProductPeer::doSelectOne($c);

                    var_dump(CustomerProductPeer::doCount($c));



                    $customer_order->setProduct($customer_product->getProduct());
                    $customer_order->setQuantity(1);
                    $customer_order->setExtraRefill($customer->getAutoRefillAmount());


                    //create a transaction
                    $transaction = new Transaction();
                    $transaction->setCustomer($customer);
                    $transaction->setAmount($customer->getAutoRefillAmount());
                    $transaction->setDescription('Auto refill');



                    //associate transaction with customer order
                    $customer_order->addTransaction($transaction);

                    //save order to get order_id that is required to create a transaction via epay api
                    $customer_order->save();



                    if ($epay_con->authorize(sfConfig::get('app_epay_merchant_number'), $customer->getSubscriptionId(), $customer_order->getId(), $customer->getAutoRefillAmount(), 208, 1)) {
                        $customer->setLastAutoRefill(date('Y-m-d H:i:s'));
                        $customer_order->setOrderStatusId(sfConfig::get('app_status_completed'));
                        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed'));
                    } else {
                        die('unauthorized epay');
                    }

                    $customer->save();
                    $customer_order->save();

                    if ($customer_order->getOrderStatusId() == sfConfig::get('app_status_completed') &&
                            Fonet::recharge($customer, $customer->getAutoRefillAmount())) {

                        $this->customer = $customer;
                        $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
                        $emailId = $this->customer->getEmail();
                        $OpeningBalance = $customer->getAutoRefillAmount();
                        $customerPassword = $this->customer->getPlainText();
                        $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
                        if ($getFirstnumberofMobile == 0) {
                            $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
                            $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
                        } else {
                            $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
                        }
                        $uniqueId = $this->customer->getUniqueid();
                        //This is for Recharge the Customer
                        $MinuesOpeningBalance = $OpeningBalance * 3;
                        $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=' . $uniqueId . '&amount=' . $OpeningBalance . '&type=customer');
                        //This is for Recharge the Account
                        //this condition for if follow me is Active
                        $getvoipInfo = new Criteria();
                        $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $this->customer->getMobileNumber());
                        $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
                        if (isset($getvoipInfos)) {
                            $voipnumbers = $getvoipInfos->getNumber();
                            $voip_customer = $getvoipInfos->getCustomerId();
                            //$telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name='.$voipnumbers.'&amount='.$OpeningBalance.'&type=account');
                        } else {
                            // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name='.$uniqueId.'&amount='.$OpeningBalance.'&type=account');
                        }

                        // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=a'.$TelintaMobile.'&amount='.$OpeningBalance.'&type=account');
                        // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=cb'.$TelintaMobile.'&amount='.$OpeningBalance.'&type=account');

                        $MinuesOpeningBalance = $OpeningBalance * 3;
                        //type=<account_customer>&action=manual_charge&name=<name>&amount=<amount>
                        //This is for Recharge the Customer
                        // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=customer&action=manual_charge&name='.$uniqueId.'&amount='.$MinuesOpeningBalance);
                        //update cloud 9
                        c9Wrapper::equateBalance($customer);


                        //send invoices

                        $message_body = $this->getPartial('customer/order_receipt', array(
                            'customer' => $customer,
                            'order' => $customer_order,
                            'transaction' => $transaction,
                            'vat' => 0,
                            'wrap' => false
                        ));

                        $subject = $this->getContext()->getI18N()->__('Payment Confirmation');
                        $sender_email = sfConfig::get('app_email_sender_email', 'support@landncall.com');
                        $sender_name = sfConfig::get('app_email_sender_name', 'LandNCall AB support');

                        $recepient_email = trim($this->customer->getEmail());
                        $recepient_name = sprintf('%s %s', $this->customer->getFirstName(), $this->customer->getLastName());


                        //This Seciton For Make The Log History When Complete registration complete - Agent
                        //echo sfConfig::get('sf_data_dir');
                        $invite_data_file = sfConfig::get('sf_data_dir') . '/invite.txt';
                        $invite2 = " AutoRefill - pScript \n";
                        $invite2 = "Recepient Email: " . $recepient_email . ' \r\n';


                        //Send Email to User/Agent/Support --- when Agent register Customer --- 01/15/11
                        emailLib::sendCustomerAutoRefillEmail($this->customer, $message_body);
                    }
                }
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        return sfView::NONE;
    }

    public function executeRemoveInactiveUsers(sfWebRequest $request) {
        $c = new Criteria();

        $c->add(CustomerOrderPeer::CUSTOMER_ID, 'customer_id IN (SELECT id FROM customer WHERE TIMESTAMPDIFF(MINUTE, NOW(), created_at) >= -30 AND customer_status_id = 1)'
                , Criteria::CUSTOM);

        $this->remove_propel_object_list(CustomerOrderPeer::doSelect($c));

        //now transaction
        $c = new Criteria();

        $c->add(TransactionPeer::CUSTOMER_ID, 'customer_id IN (SELECT id FROM customer WHERE TIMESTAMPDIFF(MINUTE, NOW(), created_at) >= -30 AND customer_status_id = 1)'
                , Criteria::CUSTOM);

        $this->remove_propel_object_list(TransactionPeer::doSelect($c));

        //now customer
        $c = new Criteria();

        $c->add(CustomerPeer::ID, 'id IN (SELECT id FROM customer WHERE TIMESTAMPDIFF(MINUTE, NOW(), created_at) >= -30 AND customer_status_id = 1)'
                , Criteria::CUSTOM);

        $this->remove_propel_object_list(CustomerPeer::doSelect($c));

        $this->renderText('last deleted on ' . date(DATE_RFC822));

        return sfView::NONE;
    }

    public function executeSMS(sfWebRequest $request) {


        $sms = SMS::receive($request);

        if ($sms) {
            //take action
            $valid_keywords = array('ZEROCALLS', 'ZEROCALLR', 'ZEROCALLN');

            if (in_array($sms->getKeyword(), $valid_keywords)) {
                //get voucher info
                $c = new Criteria();

                $c->add(VoucherPeer::PIN_CODE, $sms->getMessage());
                $c->add(VoucherPeer::USED_ON, null, CRITERIA::ISNULL);

                $is_voucher_ok = false;
                $voucher = VoucherPeer::doSelectOne($c);

                switch (strtolower($sms->getKeyword())) {
                    case 'zerocalls': //register + refill
                        //purchaes a product in 0 rs, and 200 refill
                        //create customer
                        //create order for a product
                        //don't create trnsaction for product order
                        //create refill order for product
                        //create transaction for refill order

                        if ($voucher) {
                            $is_voucher_ok = $voucher->getType() == 's';

                            $is_voucher_ok = $is_voucher_ok &&
                                    ($voucher->getAmount() == 200);
                        }

                        if ($is_voucher_ok) {
                            //check if customer already exists
                            if ($this->is_mobile_number_exists($sms->getMobileNumber())) {
                                $message = $this->getContext()->getI18N()->__('
		  						You mobile number is already registered with %1%.
		  					', array('%1%' => sfConfig::get('app_site_title')));

                                echo $message;
                                SMS::send($message, $sms->getMobileNumber());
                                break;
                            }

                            //This Function For Get the Enable Country Id =
                            $calingcode = sfConfig::get("app_country_code");
                            $countryId = $this->getEnableCountryId($calingcode);

                            //create a customer
                            $customer = new Customer();

                            $customer->setMobileNumber($sms->getMobileNumber());
                            $customer->setCountryId($countryId); //denmark;
                            $customer->setAddress('Street address');
                            $customer->setCity('City');
                            $customer->setDeviceId(1);
                            $customer->setEmail($sms->getMobileNumber() . '@zerocall.com');
                            $customer->setFirstName('First name');
                            $customer->setLastName('Last name');

                            $password = substr(md5($customer->getMobileNumber() . 'jhom$brabar_x'), 0, 8);
                            $customer->setPassword($password);

                            //crete an order of startpackage
                            $customer_order = new CustomerOrder();
                            $customer_order->setCustomer($customer);
                            $customer_order->setProductId(1);
                            $customer_order->setExtraRefill($voucher->getAmount());
                            $customer_order->setQuantity(0);
                            $customer_order->setIsFirstOrder(true);

                            //set customer_product

                            $customer_product = new CustomerProduct();

                            $customer_product->setCustomer($customer);
                            $customer_product->setProduct($customer_order->getProduct());

                            //crete a transaction of product price
                            $transaction = new Transaction();
                            $transaction->setAmount($voucher->getAmount());
                            $transaction->setDescription($this->getContext()->getI18N()->__('Product  purchase & refill, via voucher'));
                            $transaction->setOrderId($customer_order->getId());
                            $transaction->setCustomer($customer);


                            $customer->setCustomerStatusId(sfConfig::get('app_status_completed', 3));
                            $customer_order->setOrderStatusId(sfConfig::get('app_status_completed', 3));
                            $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3));


                            $customer->save();
                            $customer_order->save();
                            $customer_product->save();
                            $transaction->save();
                            TransactionPeer::AssignReceiptNumber($transaction);


                            //save voucher so it can't be reused
                            $voucher->setUsedOn(date('Y-m-d'));

                            $voucher->save();

                            //register with fonet
                            Fonet::registerFonet($customer);
                            Fonet::recharge($customer, $transaction->getAmount());

                            $this->customer = $customer;
                            $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
                            if ($getFirstnumberofMobile == 0) {
                                $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
                                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
                            } else {
                                $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
                            }
                            $uniqueId = $this->customer->getUniqueid();
                            $emailId = $this->customer->getEmail();
                            $OpeningBalance = $transaction->getAmount();
                            $customerPassword = $this->customer->getPlainText();

                            //Section For Telinta Add Cusomter
                            $telintaRegisterCus = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?reseller=R_WLS_Kimarin_ES&action=add&name=' . $uniqueId . '&currency=' . sfConfig::get('app_currency_symbol') . '&opening_balance=0&credit_limit=0&enable_dialingrules=Yes&int_dial_pre=00&email=' . $emailId . '&type=customer');

                            // For Telinta Add Account
                            $telintaAddAccount = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=account&action=activate&name=' . $uniqueId . '&customer=' . $uniqueId . '&opening_balance=-' . $OpeningBalance . '&product=YYYLandncall_Forwarding&outgoing_default_r_r=2034&activate_follow_me=Yes&follow_me_number=0&billing_model=1&password=' . $customerPassword);
                            $telintaAddAccountA = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=account&action=activate&name=a' . $TelintaMobile . '&customer=' . $TelintaMobile . '&opening_balance=-' . $OpeningBalance . '&product=YYYLandncall_CT&outgoing_default_r_r=2034&billing_model=1&password=' . $customerPassword);
                            $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=account&action=activate&name=cb' . $TelintaMobile . '&customer=' . $TelintaMobile . '&opening_balance=-' . $OpeningBalance . '&product=YYYLandncall_callback&outgoing_default_r_r=2034&billing_model=1&password=' . $customerPassword);

                            //This is for Recharge the Customer
                            $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=' . $uniqueId . '&amount=' . $OpeningBalance . '&type=customer');



                            $message = $this->getContext()->getI18N()->__('
			  			You have been registered to ZerOcall.' /* \n
                                      You can use following login information to access your account.\n
                                      Email: '. $customer->getEmail(). '\n' .
                                      'Password: ' . $password */
                            );

                            echo $message;
                            SMS::send($message, $customer->getMobileNumber());
                        } else {
                            $invalid_pin_sms = SMS::send($this->getContext()->getI18N()->__('Invalid pin code.'), $sms->getMobileNumber());
                            echo $invalid_pin_sms;
                            $this->logMessage('invaild pin sms sent to ' . $sms->getMobileNumber());
                        }

                        break;
                    case 'zerocallr': //refill
                        //check if mobile number exists?
                        //create an order for sms refill
                        //create a transaction
                        if ($voucher) {
                            $is_voucher_ok = $voucher->getType() == 'r';

                            $valid_refills = array(100, 200, 500);

                            $is_voucher_ok = $is_voucher_ok && in_array($voucher->getAmount(), $valid_refills);
                        }

                        if ($is_voucher_ok) {
                            //check if customer already exists
                            if (!$this->is_mobile_number_exists($sms->getMobileNumber())) {
                                $message = $this->getContext()->getI18N()->__('
		  						Your mobile number is not registered with LandNCall AB.
		  					');

                                echo $message;
                                SMS::send($message, $sms->getMobileNumber());
                                break;
                            }
                            //get the customer

                            $c = new Criteria();
                            $c->add(CustomerPeer::MOBILE_NUMBER, $sms->getMobileNumber());


                            $customer = CustomerPeer::doSelectOne($c);

                            //create new customer order
                            $customer_order = new CustomerOrder();
                            $customer_order->setCustomer($customer);

                            //get customer product

                            $c = new Criteria();
                            $c->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());

                            $customer_product = CustomerProductPeer::doSelectOne($c);

                            //set customer product
                            $customer_order->setProduct($customer_product->getProduct());

                            $customer_order->setExtraRefill($voucher->getAmount());
                            $customer_order->setQuantity(0);
                            $customer_order->setIsFirstOrder(false);


                            //crete a transaction of product price
                            $transaction = new Transaction();
                            $transaction->setAmount($voucher->getAmount());
                            $transaction->setDescription($this->getContext()->getI18N()->__('LandNCall AB  Refill, via voucher'));
                            $transaction->setOrderId($customer_order->getId());
                            $transaction->setCustomer($customer);


                            $customer_order->setOrderStatusId(sfConfig::get('app_status_completed', 3));
                            $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3));

                            $customer_order->save();
                            $transaction->save();
                            TransactionPeer::AssignReceiptNumber($transaction);
                            Fonet::recharge($customer, $transaction->getAmount());


                            //save voucher so it can't be reused
                            $voucher->setUsedOn(date('Y-m-d H:i:s'));

                            $voucher->save();

                            $message = $this->getContext()->getI18N()->__('
			  			You account has been topped up.' /* \n
                                      You can use following login information to access your account.\n
                                      Email: '. $customer->getEmail(). '\n' .
                                      'Password: ' . $password */
                            );

                            echo $message;
                            SMS::send($message, $sms->getMobileNumber());
                        } else {
                            $invalid_pin_sms = SMS::send($this->getContext()->getI18N()->__('Invalid pin code.'), $sms->getMobileNumber());
                            echo $invalid_pin_sms;
                            $this->logMessage('invaild pin sms sent to ' . $sms->getMobileNumber());
                        }

                        break;
                    case 'zerocalln':
                        //purchases a 100 product, no refill
                        //check if pin code
                        // pin code matches
                        // not used before
                        //	type is n, amount eq to gt than product price



                        if ($voucher) {
                            $is_voucher_ok = $voucher->getType() == 'n';

                            $is_voucher_ok = $is_voucher_ok &&
                                    ($voucher->getAmount() >= ProductPeer::retrieveByPK(1)->getPrice());
                        }

                        if ($is_voucher_ok) {
                            //check if customer already exists
                            if ($this->is_mobile_number_exists($sms->getMobileNumber())) {
                                $message = $this->getContext()->getI18N()->__('
		  						You mobile number is already registered with %1%.
		  					', array('%1%', sfConfig::get('app_site_title')));

                                echo $message;
                                SMS::send($message, $sms->getMobileNumber());
                                break;
                            }

                            //This Function For Get the Enable Country Id =
                            $calingcode = sfConfig::get("app_country_code");
                            $countryId = $this->getEnableCountryId($calingcode);

                            //create a customer
                            $customer = new Customer();

                            $customer->setMobileNumber($sms->getMobileNumber());
                            $customer->setCountryId($countryId); //denmark;
                            $customer->setAddress('Street address');
                            $customer->setCity('City');
                            $customer->setDeviceId(1);
                            $customer->setEmail($sms->getMobileNumber() . '@zerocall.com');
                            $customer->setFirstName('First name');
                            $customer->setLastName('Last name');

                            $password = substr(md5($customer->getMobileNumber() . 'jhom$brabar_x'), 0, 8);
                            $customer->setPassword($password);

                            //crete an order of startpackage
                            $customer_order = new CustomerOrder();
                            $customer_order->setCustomer($customer);
                            $customer_order->setProductId(1);
                            $customer_order->setExtraRefill(0);
                            $customer_order->setQuantity(1);
                            $customer_order->setIsFirstOrder(true);

                            //set customer_product

                            $customer_product = new CustomerProduct();

                            $customer_product->setCustomer($customer);
                            $customer_product->setProduct($customer_order->getProduct());

                            //crete a transaction of product price
                            $transaction = new Transaction();
                            $transaction->setAmount($customer_order->getProduct()->getPrice() * $customer_order->getQuantity());
                            $transaction->setDescription($this->getContext()->getI18N()->__('Product  purchase, via voucher'));
                            $transaction->setOrderId($customer_order->getId());
                            $transaction->setCustomer($customer);


                            $customer->setCustomerStatusId(sfConfig::get('app_status_completed', 3));
                            $customer_order->setOrderStatusId(sfConfig::get('app_status_completed', 3));
                            $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3));


                            $customer->save();
                            $customer_order->save();
                            $customer_product->save();
                            $transaction->save();
                            TransactionPeer::AssignReceiptNumber($transaction);

                            //save voucher so it can't be reused
                            $voucher->setUsedOn(date('Y-m-d'));

                            $voucher->save();

                            //register with fonet
                            Fonet::registerFonet($customer);

                            $message = $this->getContext()->getI18N()->__('
			  			You have been registered to %1%.' /* \n
                                      You can use following login information to access your account.\n
                                      Email: '. $customer->getEmail(). '\n' .
                                      'Password: ' . $password */
                                    , array('%1%', sfConfig::get('app_site_title')));

                            echo $message;
                            SMS::send($message, $sms->getMobileNumber());
                        } else {
                            $invalid_pin_sms = SMS::send($this->getContext()->getI18N()->__('Invalid pin code.'), $sms->getMobileNumber());
                            echo $invalid_pin_sms;
                            $this->logMessage('invaild pin sms sent to ' . $sms->getMobileNumber());
                        }

                        break;
                }
            }
        }

        $this->renderText('completed');

        return sfView::NONE;
    }

    private function is_mobile_number_exists($mobile_number) {
        $c = new Criteria();

        $c->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);

        if (CustomerPeer::doSelectOne($c))
            return true;
    }

    private function remove_propel_object_list($list) {
        foreach ($list as $list_item) {
            $list_item->delete();
        }
    }

    public function executeSendEmails(sfWebRequest $request) {

        require_once(sfConfig::get('sf_lib_dir') . '/swift/lib/swift_init.php');


        echo 'starting the debug';
        echo '<br/>';
        echo sfConfig::get('app_email_smtp_host');
        echo '<br/>';
        echo sfConfig::get('app_email_smtp_port');
        echo '<br/>';
        echo sfConfig::get('app_email_smtp_username');
        echo '<br/>';
        echo sfConfig::get('app_email_smtp_password');
        echo '<br/>';
        echo sfConfig::get('app_email_sender_email', 'support@kimarin.es');
        echo '<br/>';
        echo sfConfig::get('app_email_sender_name', 'Kimarin support');


        $connection = Swift_SmtpTransport::newInstance()
                ->setHost(sfConfig::get('app_email_smtp_host'))
                ->setPort(sfConfig::get('app_email_smtp_port'))
                ->setUsername(sfConfig::get('app_email_smtp_username'))
                ->setPassword(sfConfig::get('app_email_smtp_password'));




        $sender_email = sfConfig::get('app_email_support_email');
        $sender_name = sfConfig::get('app_email_support_name');

        echo '<br/>';
        echo $sender_email;
        echo '<br/>';
        echo $sender_name;


        $mailer = new Swift_Mailer($connection);

        $c = new Criteria();
        $c->add(EmailQueuePeer::EMAIL_STATUS_ID, sfConfig::get('app_status_completed'), Criteria::NOT_EQUAL);
        $emails = EmailQueuePeer::doSelect($c);
        try {
            foreach ($emails as $email) {


                $message = Swift_Message::newInstance($email->getSubject())
                        ->setFrom(array($sender_email => $sender_name))
                        ->setTo(array($email->getReceipientEmail() => $email->getReceipientName()))
                        ->setBody($email->getMessage(), 'text/html')
                ;

//                $message = Swift_Message::newInstance($email->getSubject())
//		         ->setFrom(array("support@landncall.com"))
//		         ->setTo(array("mohammadali110@gmail.com"=>"Mohammad Ali"))
//		         ->setBody($email->getMessage(), 'text/html')
//		         ;
                echo 'inside loop';
                echo '<br/>';

                echo $email->getId();
                echo '<br/>';
                echo '<br/>';

                //This Conditon Add Update Row Which Have the 
                if ($email->getReceipientEmail() != '') {
                    @$mailer->send($message);
                    $email->setEmailStatusId(sfConfig::get('app_status_completed'));
                    //TODO:: add sent_at too
                    $email->save();
                    echo sprintf("Send to %s<br />", $email->getReceipientEmail());
                }
            }
        } catch (Exception $e) {

            echo $e->getLine();
            echo $e->getMessage();
        }
        return sfView::NONE;
    }

    public function executeC9invoke(sfWebRequest $request) {

        $this->logMessage(print_r($_POST, true));

        // creating model object
        $c9Data = new cloud9_data();

        //setting data in model
        $c9Data->setRequestType($request->getParameter('request_type'));
        $c9Data->setC9Timestamp($request->getParameter('timestamp'));
        $c9Data->setTransactionID($request->getParameter('transactionid'));
        $c9Data->setCallDate($request->getParameter('call_date'));
        $c9Data->setCdr($request->getParameter('cdr_id'));
        $c9Data->setCid($request->getParameter('carrierid'));
        $c9Data->setMcc($request->getParameter('mcc'));
        $c9Data->setMnc($request->getParameter('mnc'));
        $c9Data->setImsi($request->getParameter('imsi'));
        $c9Data->setMsisdn($request->getParameter('msisdn'));
        $c9Data->setDestination($request->getParameter('destination'));
        $c9Data->setLeg($request->getParameter('leg'));
        $c9Data->setLegDuration($request->getParameter('leg_duration'));
        $c9Data->setResellerCharge($request->getParameter('reseller_charge'));
        $c9Data->setClientCharge($request->getParameter('client_charge'));
        $c9Data->setUserCharge($request->getParameter('user_charge'));
        $c9Data->setIot($request->getParameter('IOT'));
        $c9Data->setUserBalance($request->getParameter('user_balance'));

//saving model object in Database	
        $c9Data->save();



        $conversion_rate = CurrencyConversionPeer::retrieveByPK(1);

        $exchange_rate = $conversion_rate->getBppDkk();

        $amt_bpp = $c9Data->getUserBalance();

        $amt_dkk = $amt_bpp * $exchange_rate;

//find the customer.

        $c = new Criteria();
        $c->add(CustomerPeer::C9_CUSTOMER_NUMBER, $c9Data->getMsisdn());
        $customer = CustomerPeer::doSelectOne($c);


//get fonet balance

        $fonet = new Fonet();
        $balance = $fonet->getBalance($customer, true);

//update Balance on Fonet if there's a difference

        if ($fonet->recharge($customer, number_format($amt_dkk - $balance, 2), true)) {

//if fonet customer found, send success response.

            $this->getResponse()->setContentType("text/xml");
            $this->getResponse()->setContent("<?xml version=\"1.0\"?>
        <CDR_response>
        <cdr_id>" . $request->getParameter('cdr_id') . "</cdr_id>
        <cdr_status>1</cdr_status>
        </CDR_response> ");
        }

        return sfView::NONE;
    }

    public function c9_follow_up(Cloud9Data $c9Data) {

        echo("inside follow up \n: ");



        echo("calculcated amount: ");
        echo($amt_dkk);

//
//        $balance = $amt * $exchange_rate->getBppDkk();
//
//        echo($balance);
//
//        //echo($user_balance_dkk);
//
//        $cust = CustomerPeer::retrieveByPK(22);
//
//        $cust->setC9CustomerNumber($balance);
//
//        $cust->save();
//
//        return $cust;
//            echo('hello/');
//            $customer = CustomerPeer::retrieveByPK(1);
//            echo('world/');
//
//            $fonet = new Fonet();
//            $balance = $fonet->getBalance($customer, true);
//            echo('hilo/');
//            echo($balance);
//            echo('verden/');
//
//            $fonet->recharge($customer, -20, true);
//            echo('hilo 2/');
//            $balance = $fonet->getBalance($customer, true);
//            echo('hilo 3/');
//            echo($balance);
//            echo('world');
        //echo($balance->getBalance(&$customer));
    }

    public function executeBalanceAlert(sfWebRequest $request) {
        $username = 'zerocall';
        $password = 'ok20717786';
        //$c=new Criteria();
        //$fonet=new Fonet();
        //  $customers=CustomerPeer::doSelect($c);
        $balance = $request->getParameter('balance');
        $mobileNo = $request->getParameter('mobile');
        //foreach($customers as $customer)
        //{
        $balance_data_file = sfConfig::get('sf_data_dir') . '/balanceTest.txt';
        $baltext = "";
        $baltext .= "Mobile No: {$mobileNo} , Balance: {$balance} \r\n";

        file_put_contents($balance_data_file, $baltext, FILE_APPEND);

        if ($mobileNo) {
            if ($balance < 25 && $balance > 10) {

                $baltext .= "balance < 25 && balance > 10";
                $data = array(
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobileNo,
                    'message' => "You balance is below 25 " . sfConfig::get('app_currency_code') . ", Please refill your account. " . sfConfig::get('app_site_title') . " - Support "
                );
                $queryString = http_build_query($data, '', '&');
                $this->response_text = file_get_contents('http://sms.gratisgateway.dk/send.php?' . $queryString);
                echo $this->response_text;
            } else if ($balance < 10.00 && $balance > 0.00) {

                $data = array(
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobileNo,
                    'message' => "You balance is below 10 " . sfConfig::get('app_currency_code') . ", Please refill your account. " . sfConfig::get('app_site_title') . " - Support"
                );
                $queryString = http_build_query($data, '', '&');
                $this->response_text = file_get_contents('http://sms.gratisgateway.dk/send.php?' . $queryString);
                $baltext .= "balance < 10 && balance > 0";
            } else if ($balance <= 0.00) {


                $data = array(
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobileNo,
                    'message' => "You balance is 0 " . sfConfig::get('app_currency_code') . ", Please refill your account. " . sfConfig::get('app_site_title') . " - Support "
                );
                $queryString = http_build_query($data, '', '&');
                $this->response_text = file_get_contents('http://sms.gratisgateway.dk/send.php?' . $queryString);
                $baltext .= "balance 0";
            }
        }


        $baltext .= $this->response_text;
        file_put_contents($balance_data_file, $baltext, FILE_APPEND);


        $data = array(
            'mobile' => $mobileNo,
            'balance' => $balance
        );

        $queryString = http_build_query($data, '', '&');
        $this->redirect('pScripts/balanceAlert?' . $queryString);



        return sfView::NONE;
    }

    public function executeBalanceEmail(sfWebRequest $request) {


        $balance = $request->getParameter('balance');
        $mobileNo = $request->getParameter('mobile');

        $email_data_file = sfConfig::get('sf_data_dir') . '/EmailAlert.txt';
        $email_msg = "";
        $email_msg .= "Mobile No: {$mobileNo} , Balance: {$balance} \r\n";
        file_put_contents($email_data_file, $email_msg, FILE_APPEND);

        //$fonet=new Fonet();
        //
      
      $c = new Criteria();
        $c->add(CustomerPeer::MOBILE_NUMBER, $mobileNo);
        $customers = CustomerPeer::doSelect($c);
        $recepient_name = '';
        $recepient_email = '';
        foreach ($customers as $customer) {
            $recepient_name = $customer->getFirstName() . ' ' . $customer->getLastName();
            $recepient_email = $customer->getEmail();
        }


        //$recepient_name=
        //foreach($customers as $customer)
        //{

        file_put_contents($email_data_file, $email_msg, FILE_APPEND);

        if ($mobileNo) {
            if ($balance < 25.00 && $balance > 10.00) {
                $email_msg .= "\r\n balance < 25 && balance > 10";
                //echo 'mail sent to you';
                $subject = 'Test Email: Balance Email ';
                $message_body = "Test Email:  Your balance is below 25" . sfConfig::get('app_currency_code') . " , please refill otherwise your account will be closed. \r\n - " . sfConfig::get('app_site_title') . " Support \r\n Company Contact Info";

                //This Seciton For Make The Log History When Complete registration complete - Agent
                //echo sfConfig::get('sf_data_dir');
                $invite_data_file = sfConfig::get('sf_data_dir') . '/invite.txt';
                $invite2 = " Balance Email - pScript \n";
                if ($recepient_email):
                    $invite2 = "Recepient Email: " . $recepient_email . ' \r\n';
                endif;

                //Send Email to Customer For Balance --- 01/15/11
                emailLib::sendCustomerBalanceEmail($customers, $message_body);
            }
            else if ($balance < 10.00 && $balance > 0.00) {

                $email_msg .= "\r\n balance < 10 && balance > 0";
                $subject = 'Test Email: Balance Email ';
                $message_body = "Test Email:  Your balance is below 10" . sfConfig::get('app_currency_code') . " , please refill otherwise your account will be closed. \r\n - " . sfConfig::get('app_site_title') . " Support \r\n Company Contact Info";

                //This Seciton For Make The Log History When Complete registration complete - Agent
                //echo sfConfig::get('sf_data_dir');
                $invite_data_file = sfConfig::get('sf_data_dir') . '/invite.txt';
                $invite2 = " Balance Email - pScript \n";
                if ($recepient_email):
                    $invite2 = "Recepient Email: " . $recepient_email;
                endif;

                //Send Email to Customer For Balance --- 01/15/11
                emailLib::sendCustomerBalanceEmail($customers, $message_body);
            }
            else if ($balance <= 0.00) {
                $email_msg .= "\r\n balance < 10 && balance > 0";
                $subject = 'Test Email: Balance Email ';
                $message_body = "Test Email:  Your balance is 0 " . sfConfig::get('app_currency_symbol') . ", please refill otherwise your account will be closed. \r\n - " . sfConfig::get('app_site_title') . " Support \r\n Company Contact Info";

                //This Seciton For Make The Log History When Complete registration complete - Agent
                //echo sfConfig::get('sf_data_dir');
                $invite_data_file = sfConfig::get('sf_data_dir') . '/invite.txt';
                $invite2 = " Balance Email - pScript \n";
                if ($recepient_email):
                    $invite2 = "Recepient Email: " . $recepient_email;
                endif;

                //Send Email to Customer For Balance --- 01/15/11
                emailLib::sendCustomerBalanceEmail($customers, $message_body);
            }
        }


        $email_msg .= $message_body;
        $email_msg .= "\r\n Email Sent";
        file_put_contents($email_data_file, $email_msg, FILE_APPEND);
        return sfView::NONE;
    }

    public function executeWebSms(sfWebRequest $request) {
        require_once(sfConfig::get('sf_lib_dir') . '\SendSMS.php');
        require_once(sfConfig::get('sf_lib_dir') . '\IncomingFormat.php');
        require_once(sfConfig::get('sf_lib_dir') . '\ClientPolled.php');


        //$sms_username = "zapna01";
        //$sms_password = "Zapna2010";




        $replies = send_sms_full("923454375829", "CBF", "Test SMS: Taisys Test SMS form test.Zerocall.com"); //or die ("Error: " .$errstr. " \n");
        //$replies = send_sms("44123456789,44987654321,44214365870","SMS_Service", "This is a message from me.") or die ("Error: " . $errstr . "\n");

        echo "<br /> Response from Taisys <br />";
        echo $replies;
        echo $errstr;
        echo "<br />";

        file_get_contents("http://sms1.cardboardfish.com:9001/HTTPSMS?S=H&UN=zapna1&P=Zapna2010&DA=923454375829&ST=5&SA=Zerocall&M=Test+SMS%3A+Taisys+Test+SMS+form+test.Zerocall.com");

        return sfView::NONE;
    }

    public function executeTaisys(sfWebrequest $request) {

        $taisys = new Taisys();

        $taisys->setServ($request->getParameter('serv'));
        $taisys->setImsi($request->getParameter('imsi'));
        $taisys->setDn($request->getParameter('dest'));
        $taisys->setSmscontent($request->getParameter('content'));
        $taisys->setChecksum($request->getParameter('mac'));
        $taisys->setChecksumVerification(true);

        $taisys->save();

        $data = array(
            'S' => 'H',
            'UN' => 'zapna1',
            'P' => 'Zapna2010',
            'DA' => $taisys->getDn(),
            'SA' => 'Zerocall',
            'M' => $taisys->getSmscontent(),
            'ST' => '5'
        );


        $queryString = http_build_query($data, '', '&');
        $queryString = smsCharacter::smsCharacterReplacement($queryString);
        $res = file_get_contents('http://sms1.cardboardfish.com:9001/HTTPSMS?' . $queryString);
        $this->res_cbf = 'Response from CBF is: ';
        $this->res_cbf .= $res;

        echo $this->res_cbf;
        return sfView::NONE;
    }

    public function executeSmsRegistration(sfWebrequest $request) {

        $number = $request->getParameter('mobile');
        $customercount = 0;
        $agentCount = 0;
        $productCount = 0;
        $mnc = new Criteria();
        $mnc->add(CustomerPeer::MOBILE_NUMBER, substr($number, 2));
        $mnc->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customercount = CustomerPeer::doCount($mnc);
        if ($customercount > 0) {
            echo "Mobile number Already exist";
            $sm = new Criteria();
            $sm->add(SmsTextPeer::ID, 1);
            $smstext = SmsTextPeer::doSelectOne($sm);
            $sms_text = $smstext->getMessageText();
            CARBORDFISH_SMS::Send($number, $sms_text);
            die;
        }
        $message = $request->getParameter('message');
        $keyword = $request->getParameter('keyword');
        $agent_code = substr($message, 0, 4);
        $product_code = substr($message, 4, 2);
        $uniqueid = substr($message, 6, 6);

        $c = new Criteria();
        $c->add(AgentCompanyPeer::SMS_CODE, $agent_code);
        $agentCount = AgentCompanyPeer::doCount($c);
        if ($agentCount == 0) {
            echo "Agent not found";
            $sm = new Criteria();
            $sm->add(SmsTextPeer::ID, 3);
            $smstext = SmsTextPeer::doSelectOne($sm);
            $sms_text = $smstext->getMessageText();
            CARBORDFISH_SMS::Send($number, $sms_text);
            die;
        }

        $c = new Criteria();
        $c->add(AgentCompanyPeer::SMS_CODE, $agent_code);
        $agent = AgentCompanyPeer::doSelectOne($c);
        //geting product sms code
        $pc = new Criteria();
        $pc->add(ProductPeer::SMS_CODE, $product_code);
        $productCount = ProductPeer::doCount($pc);
        if ($productCount == 0) {
            echo 'Product not found';
            $sm = new Criteria();
            $sm->add(SmsTextPeer::ID, 4);
            $smstext = SmsTextPeer::doSelectOne($sm);
            $sms_text = $smstext->getMessageText();
            CARBORDFISH_SMS::Send($number, $sms_text);
            die;
        }


        $pc = new Criteria();
        $pc->add(ProductPeer::SMS_CODE, $product_code);
        $product = ProductPeer::doSelectOne($pc);
        $mobile = substr($number, 2);
        //This Function For Get the Enable Country Id =
        $calingcode = sfConfig::get('app_country_code');
        $customer = new Customer();
        $customer->setFirstName($mobile);
        $customer->setLastName($mobile);
        $customer->setMobileNumber($mobile);
        $customer->setPassword($mobile);
        $customer->setEmail($agent->getEmail());
        $customer->setReferrerId($agent->getId());
        $customer->setCountryId(1);
        $customer->setCity("");
        $customer->setAddress("");
        $customer->setTelecomOperatorId(1);
        $customer->setDeviceId(1474);
        $customer->setCustomerStatusId(1);
        $customer->setPlainText($mobile);
        $customer->setRegistrationTypeId(4);
        $customer->save();


        $order = new CustomerOrder();
        $order->setProductId($product->getId());
        $order->setCustomerId($customer->getId());
        $order->setExtraRefill($order->getProduct()->getInitialBalance());
        $order->setIsFirstOrder(1);
        $order->setOrderStatusId(1);
        $order->save();

        $this->customer = $customer;
        $transaction = new Transaction();
        $transaction->setAgentCompanyId($customer->getReferrerId());
        $transaction->setAmount($order->getProduct()->getPrice() + $order->getProduct()->getRegistrationFee() + ($order->getProduct()->getRegistrationFee() * sfConfig::get('app_vat_percentage')));
        $transaction->setDescription('Registration');
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($customer->getId());
        $transaction->setTransactionStatusId(1);
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);
        $customer_product = new CustomerProduct();
        $customer_product->setCustomer($order->getCustomer());
        $customer_product->setProduct($order->getProduct());
        $customer_product->save();

        $uc = new Criteria();
        $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 2);
        $uc->addAnd(UniqueIdsPeer::STATUS, 0);
        $uc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueid);
        $availableUniqueCount = UniqueIdsPeer::doCount($uc);
        $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);

        if ($availableUniqueCount == 0) {
            echo $this->getContext()->getI18N()->__("Unique Ids are not avaialable.  send email to the support.");
            $sm = new Criteria();
            $sm->add(SmsTextPeer::ID, 6);
            $smstext = SmsTextPeer::doSelectOne($sm);
            $sms_text = $smstext->getMessageText();
            CARBORDFISH_SMS::Send($number, $sms_text);
            die;
        } else {
            $availableUniqueId->setStatus(1);
            $availableUniqueId->setAssignedAt(date('Y-m-d H:i:s'));
            $availableUniqueId->save();
        }
        $this->customer->setUniqueid(str_replace(' ', '', $uniqueid));
        $this->customer->save();


        $agentid = $agent->getId();
        $productid = $product->getId();
        $transactionid = $transaction->getId();

        $massage = commissionLib::registrationCommission($agentid, $productid, $transactionid);
        if (isset($massage) && $massage == "balance_error") {
            echo $this->getContext()->getI18N()->__('balance issue');
            $sm = new Criteria();
            $sm->add(SmsTextPeer::ID, 7);
            $smstext = SmsTextPeer::doSelectOne($sm);
            $sms_text = $smstext->getMessageText();
            CARBORDFISH_SMS::Send($number, $sms_text);
            $availableUniqueId->setStatus(0);
            $availableUniqueId->setAssignedAt(" ");
            $availableUniqueId->save();
            die;
        }

        $sm = new Criteria();
        $sm->add(SmsTextPeer::ID, 1);
        $smstext = SmsTextPeer::doSelectOne($sm);
        $sms_text = $smstext->getMessageText();
        CARBORDFISH_SMS::Send($number, $sms_text);

        $transaction->setTransactionStatusId(3);
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);
        $order->setOrderStatusId(3);
        $order->save();


        $callbacklog = new CallbackLog();
        $callbacklog->setMobileNumber($calingcode . $this->customer->getMobileNumber());
        $callbacklog->setuniqueId($this->customer->getUniqueid());
        $callbacklog->setCheckStatus(3);
        $callbacklog->save();

        $customer->setCustomerStatusId(3);
        $customer->save();

        $telintaObj = new Telienta();
        $telintaObj->ResgiterCustomer($this->customer, $order->getExtraRefill());
        $telintaObj->createAAccount($calingcode . $this->customer->getMobileNumber(), $this->customer);

        emailLib::sendCustomerRegistrationViaAgentSMSEmail($this->customer, $order);
        return sfView::NONE;
    }

    public function executeSmsCode(sfWebRequest $request) {

        $c = new Criteria();
        $agents = AgentCompanyPeer::doSelect($c);

        $count = 1;
        foreach ($agents as $agent) {
            $cvr = $agent->getCvrNumber();
            if (strlen($cvr) == 4) {
                $agent->setSmsCode($cvr);
                $agent->save();
            } else {
                $cvr = substr($cvr, 0, 4);
                $agent->setSmsCode($cvr);
                $agent->save();
            }
            echo $agent->getCvrNumber();
            echo ' : ';
            echo $cvr;
            echo '<br/>';
            $count = $count + 1;
        }

        return sfView::NONE;
    }

    public function executeDeleteValues(sfWebRequest $request) {

        $c = new Criteria();
        $orders = CustomerOrderPeer::doSelect($c);

        foreach ($orders as $order) {
            $cr = new Criteria();
            $cr->add(CustomerPeer::ID, $order->getCustomerId());
            $customer = CustomerPeer::doSelectOne($cr);

            if (!$customer) {
                //$order->delete();
                echo $order->getCustomerId();
                echo "<br/>";
            }
        }

        echo "transactions";
        $ct = new Criteria();
        $transactions = TransactionPeer::doSelect($ct);

        foreach ($transactions as $transaction) {
            $cr = new Criteria();
            $cr->add(CustomerPeer::ID, $transaction->getCustomerId());
            $customer = CustomerPeer::doSelectOne($cr);

            if (!$customer) {
                //$transaction->delete();
                echo $transaction->getCustomerId();
                echo "<br/>";
            }
        }

        echo "customer products";
        $cp = new Criteria();
        $cps = CustomerProductPeer::doSelect($cp);

        foreach ($cps as $cp) {
            $cr = new Criteria();
            $cr->add(CustomerPeer::ID, $cp->getCustomerId());
            $customer = CustomerPeer::doSelectOne($cr);

            if (!$customer) {
                //$cp->delete();
                echo $cp->getCustomerId();
                echo "<br/>";
            }
        }

        return sfView::NONE;
    }

    public function executeRegistrationType(sfWebRequest $request) {

        $c = new Criteria();
        $customers = CustomerPeer::doSelect($c);

        foreach ($customers as $customer) {
            if ($customer->getReferrerId()) {
                if (!$customer->getRegistrationTypeId()) {
                    $customer->setRegistrationTypeId(2);
                    $customer->save();
                }
            } else {
                $customer->setRegistrationTypeId(1);
                $customer->save();
            }
        }
        return sfView::NONE;
    }

    public function executeGetBalanceAll() {

        $balance = 0;
        $total_unassigned = 0;
        $total_assigned = 0;

        $c = new Criteria();
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customers = CustomerPeer::doSelect($c);

        echo "Total customers: " . count($customers);
        foreach ($customers as $customer) {
            $balance = Fonet::getBalance($customer);
            if ($balance > 0) {
                echo "<br/>";
                echo "Registered: " . $customer->getMobileNumber() . ", Balance: " . $balance;
                $total_assigned++;
            } else {
                echo "<br/>";
                echo "Not Registered: " . $customer->getMobileNumber() . ", Balance: " . $balance;
                $total_unassigned++;
            }
        }

        echo "<br/>";
        echo "Total UnRegistered: " . $total_unassigned++;
        echo "<br/>";
        echo "Total Registered: " . $total_assigned++;
    }

    public function executeRescueRegister() {

        $balance = 0;
        $already_registered = 0;
        $newly_registered = 0;
        $not_registered = 0;

        $c = new Criteria();
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 0, Criteria::GREATER_THAN);
        $customers = CustomerPeer::doSelect($c);

        echo "Total customers: " . count($customers);

        foreach ($customers as $customer) {

            $balance = Fonet::getBalance($customer);
            if ($balance > 0) {
                echo "<br/>";
                echo++$already_registered . ") Already Registered: " . $customer->getMobileNumber() . ", Balance: " . $balance;
                echo "<br/>";
            } else {
                echo "<br/>";
                echo++$not_registered . ") Not Registered: " . $customer->getMobileNumber() . ", Balance: " . $balance;


                $query_vars = array(
                    'Action' => 'Activate',
                    'ParentCustomID' => 1393238,
                    'AniNo' => $customer->getMobileNumber(),
                    'DdiNo' => 25998893,
                    'CustomID' => $customer->getFonetCustomerId()
                );

                $url = 'http://fax.fonet.dk/cgi-bin/ZeroCallV2Control.pl' . '?' . http_build_query($query_vars);
                $res = file_get_contents($url);
                echo "<br/>";
                echo 'Registered :' . $customer->getMobileNumber() . ", status: " . substr($res, 0, 2);
                echo++$newly_registered;
            }
        }
    }

    public function executeRescueDefaultBalance(sfWebRequest $request) {

        $balance = 0;
        $already_registered = 0;
        $newly_registered = 0;
        $not_registered = 0;

        $c = new Criteria();
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 0, Criteria::GREATER_THAN);
        $customers = CustomerPeer::doSelect($c);

        echo "Total customers: " . count($customers);

        foreach ($customers as $customer) {

            $balance = Fonet::getBalance($customer);
            if ($balance > 0) {
                echo "<br/>";
                echo++$already_registered . ") Already Registered: " . $customer->getMobileNumber() . ", Balance: " . $balance;
                echo "<br/>";
            } else {
                $cp = new Criteria();
                $cp->add(CustomerProductPeer::PRODUCT_ID, 7, Criteria::NOT_EQUAL);
                $cp->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
                $customer_product = CustomerProductPeer::doSelectOne($cp);

                if ($customer_product) {
                    $query_vars = array(
                        'Action' => 'Recharge',
                        'ParentCustomID' => 1393238,
                        'CustomID' => $customer->getFonetCustomerId(),
                        'ChargeValue' => 20 * 100
                    );

                    $url = 'http://fax.fonet.dk/cgi-bin/ZeroCallV2Control.pl' . '?' . http_build_query($query_vars);
                    $res = file_get_contents($url);
                    echo "<br/>";
                    echo++$balance_assigned . ')Recharged :' . $customer->getMobileNumber() . ", status: " . substr($res, 0, 2);
                    echo "<br/>";
                }
            }
        }
    }

    public function getEnableCountryId($calingcode) {
        // echo $full_mobile_number = $calingcode;
        $enableCountry = new Criteria();
        $enableCountry->add(EnableCountryPeer::STATUS, 1);
        $enableCountry->add(EnableCountryPeer::LANGUAGE_SYMBOL, 'en', Criteria::NOT_EQUAL);
        $enableCountry->add(EnableCountryPeer::CALLING_CODE, '%' . $calingcode . '%', Criteria::LIKE);
        $country_id = EnableCountryPeer::doSelectOne($enableCountry);
        $countryId = $country_id->getId();
        return $countryId;
    }

    public function executeSmsRegisterationwcb(sfWebrequest $request) {
        $urlval = "WCR-" . $request->getURI();
        $dibsCall = new DibsCall();
        $dibsCall->setCallurl($urlval);
        $dibsCall->save();

        die;
        $number = $request->getParameter('from');
        $mobileNumber = substr($number, 2, strlen($number) - 2);
        if ($mobileNumber[0] != "0") {
            $mobileNumber = "0" . $mobileNumber;
        }
        $textParamter = $request->getParameter('text');
        $requestType = substr($textParamter, 0, 2);
        $requestType = strtolower($requestType);



        if ($requestType == "hc") {

            /* $dialerIdLenght = strlen($textParamter);
              $uniqueId = substr($textParamter, $dialerIdLenght - 7, $dialerIdLenght - 1);
              $mnc = new Criteria();
              $mnc->add(CustomerPeer::MOBILE_NUMBER, $mobileNumber);
              $mnc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
              $cusCount = CustomerPeer::doCount($mnc);
              if ($cusCount < 1) {
              $uc = new Criteria();
              $uc->add(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueId);
              $uc->addAnd(UniqueIdsPeer::STATUS, 0);
              $callbackq = UniqueIdsPeer::doCount($uc);
              if ($callbackq== 1) {
              $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);
              $pc = new Criteria();
              $pc->add(ProductPeer::SMS_CODE, "50");
              $product = ProductPeer::doSelectOne($pc);
              $calingcode = sfConfig::get('app_country_code');
              $password = $this->randomNumbers(6);
              $customer = new Customer();
              $customer->setFirstName($mobileNumber);
              $customer->setLastName($mobileNumber);
              $customer->setMobileNumber($mobileNumber);
              $customer->setPassword($password);
              $customer->setEmail("retail@example.com");
              $customer->setCountryId(2);
              $customer->setCity("");
              $customer->setAddress("");
              $customer->setSimTypeId($availableUniqueId->getSimTypeId());
              $customer->setTelecomOperatorId(1);
              $customer->setDeviceId(1474);
              $customer->setUniqueId($uniqueId);
              $customer->setCustomerStatusId(3);
              $customer->setPlainText($password);
              $customer->setRegistrationTypeId(6);
              $customer->save();

              $order = new CustomerOrder();
              $order->setProductId($product->getId());
              $order->setCustomerId($customer->getId());
              $order->setExtraRefill($order->getProduct()->getInitialBalance());
              $order->setIsFirstOrder(1);
              $order->setOrderStatusId(3);
              $order->save();

              $transaction = new Transaction();
              $transaction->setAgentCompanyId($customer->getReferrerId());
              $transaction->setAmount($order->getProduct()->getPrice());
              $transactiondescription=  TransactionDescriptionPeer::retrieveByPK(8);
              $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
              $transaction->setTransactionDescriptionId($transactiondescription->getId());
              $transaction->setDescription($transactiondescription->getTitle());
              $transaction->setOrderId($order->getId());
              $transaction->setCustomerId($customer->getId());
              $transaction->setTransactionStatusId(3);
              $transaction->save();

              $customer_product = new CustomerProduct();
              $customer_product->setCustomer($order->getCustomer());
              $customer_product->setProduct($order->getProduct());
              $customer_product->save();

              $callbacklog = new CallbackLog();
              $callbacklog->setMobileNumber($number);
              $callbacklog->setuniqueId($uniqueId);
              $callbacklog->setImei($splitedText[1]);
              $callbacklog->setImsi($splitedText[2]);
              $callbacklog->setCheckStatus(3);
              $callbacklog->save();
              $telintaObj = new Telienta();
              if ($telintaObj->ResgiterCustomer($customer, $order->getExtraRefill())) {
              $availableUniqueId->setAssignedAt(date("Y-m-d H:i:s"));
              $availableUniqueId->setStatus(1);
              $availableUniqueId->setRegistrationTypeId(4);
              $availableUniqueId->save();
              $telintaObj->createAAccount($number, $customer);
              $telintaObj->createCBAccount($number, $customer);
              }

              $sms = SmsTextPeer::retrieveByPK(10);
              $smsText = $sms->getMessageText();
              $smsText = str_replace("(balance)", $order->getExtraRefill(), $smsText);
              ROUTED_SMS::Send($number, $smsText);

              $sms = SmsTextPeer::retrieveByPK(12);
              $smsText = $sms->getMessageText();
              $smsText = str_replace("(username)", $mobileNumber, $smsText);
              $smsText = str_replace("(password)", $password, $smsText);
              ROUTED_SMS::Send($number, $smsText);
              emailLib::sendCustomerRegistrationViaRetail($customer, $order);
              die;
              }

              $smstext = SmsTextPeer::retrieveByPK(8);
              echo $smstext->getMessageText();
              ROUTED_SMS::Send($number, $smstext->getMessageText());
              $message="HC Registration Failed".$smstext->getMessageText()."<br>".$urlval;
              emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
              die;
              }/*
              /*
              $customer = CustomerPeer::doSelectOne($mnc);

              $callbackq = new Criteria();
              $callbackq->add(CallbackLogPeer::UNIQUEID, $uniqueId);
              $callbackq = CallbackLogPeer::doCount($callbackq);

              if ($callbackq < 1) {
              $smstext = SmsTextPeer::retrieveByPK(7);
              echo $smstext->getMessageText();
              ROUTED_SMS::Send($number, $smstext->getMessageText());
              $message=$smstext->getMessageText()."<br>".$urlval;
              emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
              die;
              }

              $callbacklog = new CallbackLog();
              $callbacklog->setMobileNumber($number);
              $callbacklog->setuniqueId($uniqueId);
              $callbacklog->save();

              $getvoipInfo = new Criteria();
              $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customer->getId());
              $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
              if (isset($getvoipInfos)) {
              $voipnumbers = $getvoipInfos->getNumber();
              $voipnumbers = substr($voipnumbers, 2);
              }

              $tc = new Criteria();
              $tc->add(TelintaAccountsPeer::ACCOUNT_TITLE, $voipnumbers);
              $tc->add(TelintaAccountsPeer::STATUS, 3);
              if (TelintaAccountsPeer::doCount($tc) > 0) {
              $telintaAccount = TelintaAccountsPeer::doSelectOne($tc);
              $telintaObj = new Telienta();
              $telintaObj->terminateAccount($telintaAccount);
              }
              $telintaObj = new Telienta();
              $telintaObj->createReseNumberAccount($voipnumbers, $customer, $number);

              $smstext = SmsTextPeer::retrieveByPK(2);
              ROUTED_SMS::Send($number, $smstext->getMessageText());
              die; */
            return sfView::NONE;
        } elseif ($requestType == "ic") {

            /* $dialerIdLenght = strlen($textParamter);
              $uniqueId = substr($textParamter, 3);
              echo "<br/>";
              echo $uniqueId."<hr/>";

              $callbackq = new Criteria();
              $callbackq->add(CallbackLogPeer::UNIQUEID, $uniqueId);
              $callbackq = CallbackLogPeer::doCount($callbackq);

              if ($callbackq < 1) {
              $smstext = SmsTextPeer::retrieveByPK(7);
              ROUTED_SMS::Send($number, $smstext->getMessageText());
              $message=$smstext->getMessageText()."<br>".$urlval;
              emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
              die;
              }

              $mnc = new Criteria();
              $mnc->add(CustomerPeer::UNIQUEID, $uniqueId);
              $mnc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
              $cusCount = CustomerPeer::doCount($mnc);

              if ($cusCount < 1) {
              $smstext = SmsTextPeer::retrieveByPK(7);
              ROUTED_SMS::Send($number, $smstext->getMessageText());
              $message=$smstext->getMessageText()."<br>".$urlval;
              emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
              die;
              }
              $customer = CustomerPeer::doSelectOne($mnc);

              $callbacklog = new CallbackLog();
              $callbacklog->setMobileNumber($number);
              $callbacklog->setuniqueId($uniqueId);
              $callbacklog->setcallingCode(46);
              $callbacklog->save();
              $telintaObj = new Telienta();
              $telintaObj->createCBAccount($number, $customer,11648);  //11648 is Call back product for IC call

              $telintaGetBalance = $telintaObj->getBalance($customer);

              $getvoipInfo = new Criteria();
              $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customer->getId());
              $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
              if (isset($getvoipInfos)) {
              $voipnumbers = $getvoipInfos->getNumber();
              $voipnumbers = substr($voipnumbers, 2);

              $tc = new Criteria();
              $tc->add(TelintaAccountsPeer::ACCOUNT_TITLE, $voipnumbers);
              $tc->add(TelintaAccountsPeer::STATUS, 3);
              if (TelintaAccountsPeer::doCount($tc) > 0) {
              $telintaAccount = TelintaAccountsPeer::doSelectOne($tc);
              $telintaObj = new Telienta();
              $telintaObj->terminateAccount($telintaAccount);
              }
              $telintaObj = new Telienta();
              $telintaObj->createReseNumberAccount($voipnumbers, $customer, $number);
              }

              $smstext = SmsTextPeer::retrieveByPK(3);
              ROUTED_SMS::Send($number, $smstext->getMessageText());
              die; */
            return sfView::NONE;
        } else {

            $text = $this->hextostr($request->getParameter('text'));
            $splitedText = explode(";", $text);
            if ($splitedText[3] != sfConfig::get("app_dialer_pin") && $splitedText[3] != "9998888999" && $splitedText[4] != sfConfig::get("app_dialer_pin") && $splitedText[4] != "9998888999") {
                echo "Invalid Request Dialer Pin<br/>";
                $sms = SmsTextPeer::retrieveByPK(8);
                ROUTED_SMS::Send($number, $sms->getMessageText());
                $message = $sms->getMessageText() . "Invalid Request due to dialer Pin:" . $splitedText[3] . "<br>Mobile Number=" . $number . "<br>Text=" . $text;
                emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
                die;
            }
            $mobileNumber = substr($number, 2, strlen($number) - 2);

            echo "<hr/>";
            echo count($splitedText);
            echo "<hr/>";
            if (count($splitedText) == 4) {
                $dialerIdLenght = strlen($splitedText[0]);
                $uniqueId = substr($splitedText[0], $dialerIdLenght - 7, $dialerIdLenght - 1);
                echo "uniqueid:" . $uniqueId;
            } else {
                echo strtolower(substr($splitedText[0], 0, 2));
                echo "<br/>";
                echo $splitedText[0];
                if (strtolower(substr($splitedText[0], 0, 2)) == "re" && strlen($splitedText[0]) == 12) {
                    $dialerIdLenght = strlen($splitedText[0]);
                    echo $location = 4;
                    echo "<br/>";
                    $uniqueId = substr($splitedText[0], $dialerIdLenght - 7, $dialerIdLenght - 1);
                    echo "uniqueid:" . $uniqueId;
                } else {
                    $dialerIdLenght = strlen($splitedText[1]);
                    echo "DialerLenght:" . $dialerIdLenght . "<br/>";
                    $uniqueId = substr($splitedText[1], $dialerIdLenght - 7, $dialerIdLenght - 1);
                    echo $location = 5;
                    echo "<br/>";
                    echo "uniqueid:" . $uniqueId;
                }
            }

            $c = new Criteria();
            $c->add(CustomerPeer::MOBILE_NUMBER, $mobileNumber);
            $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $c->addAnd(CustomerPeer::UNIQUEID, $uniqueId);


            if ($dialerIdLenght == 10 && count($splitedText) == 4) {/*
              echo "Register Customer<br/>";
              //Registration Call, Register Customer In this block
              $uc = new Criteria();
              $uc->addAnd(UniqueIdsPeer::STATUS, 0);
              $uc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueId);

              $ucc = new Criteria();
              $ucc->addAnd(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueId);

              if (UniqueIdsPeer::doCount($ucc) == 0) {
              echo "Unique Id Not Found";
              $sms = SmsTextPeer::retrieveByPK(8);
              ROUTED_SMS::Send($number, $sms->getMessageText());
              $message=$sms->getMessageText()."<br>Unique Id Not Found:".$uniqueId."<br/>Mobile Number=".$number."<br>Text=".$text;
              emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
              die;
              }

              $cc = new Criteria();
              $cc->add(CustomerPeer::MOBILE_NUMBER, $mobileNumber);
              $cc->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);

              if (CustomerPeer::doCount($cc) > 0) {
              echo "Already Registerd";
              //$sms = SmsTextPeer::retrieveByPK(10);
              //ROUTED_SMS::Send($number, $sms->getMessageText());
              die;
              }

              if (UniqueIdsPeer::doCount($uc) > 0) {
              $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);

              $pc = new Criteria();
              $pc->add(ProductPeer::SMS_CODE, "50");
              $product = ProductPeer::doSelectOne($pc);

              $calingcode = sfConfig::get('app_country_code');
              $password = $this->randomNumbers(6);
              $customer = new Customer();
              $customer->setFirstName($mobileNumber);
              $customer->setLastName($mobileNumber);
              $customer->setMobileNumber($mobileNumber);
              $customer->setNiePassportNumber($mobileNumber);

              $customer->setPassword($password);
              $customer->setSimTypeId($availableUniqueId->getSimTypeId());
              $customer->setEmail("retail@example.com");
              $customer->setCountryId(1);
              $customer->setCity("");
              $customer->setAddress($mobileNumber);
              $customer->setTelecomOperatorId(1);
              $customer->setDeviceId(1474);
              $customer->setUniqueId($uniqueId);
              $customer->setCustomerStatusId(3);
              $customer->setPlainText($password);
              $customer->setRegistrationTypeId(6);
              $customer->save();

              $order = new CustomerOrder();
              $order->setProductId($product->getId());
              $order->setCustomerId($customer->getId());
              $order->setExtraRefill($order->getProduct()->getInitialBalance());
              $order->setIsFirstOrder(1);
              $order->setOrderStatusId(3);
              $order->save();

              $transaction = new Transaction();
              $transaction->setAgentCompanyId($customer->getReferrerId());
              $transaction->setAmount($order->getProduct()->getPrice());
              $transactiondescription =  TransactionDescriptionPeer::retrieveByPK(8);
              $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
              $transaction->setTransactionDescriptionId($transactiondescription->getId());
              $transaction->setDescription($transactiondescription->getTitle());
              $transaction->setOrderId($order->getId());
              $transaction->setCustomerId($customer->getId());
              $transaction->setTransactionStatusId(3);
              $transaction->save();

              $customer_product = new CustomerProduct();
              $customer_product->setCustomer($order->getCustomer());
              $customer_product->setProduct($order->getProduct());
              $customer_product->save();

              $callbacklog = new CallbackLog();
              $callbacklog->setMobileNumber($number);
              $callbacklog->setuniqueId($uniqueId);
              $callbacklog->setImei($splitedText[1]);
              $callbacklog->setImsi($splitedText[2]);
              $callbacklog->setCheckStatus(3);
              $callbacklog->save();
              $telintaObj = new Telienta();
              if ($telintaObj->ResgiterCustomer($customer, $order->getExtraRefill())) {
              $availableUniqueId->setAssignedAt(date("Y-m-d H:i:s"));
              $availableUniqueId->setStatus(1);
              $availableUniqueId->setRegistrationTypeId(4);
              $availableUniqueId->save();
              $telintaObj->createAAccount($number, $customer);
              $telintaObj->createCBAccount($number, $customer);
              }

              $sms = SmsTextPeer::retrieveByPK(10);
              $smsText = $sms->getMessageText();
              $smsText = str_replace("(balance)", $order->getExtraRefill(), $smsText);
              ROUTED_SMS::Send($number, $smsText);

              $sms = SmsTextPeer::retrieveByPK(12);
              $smsText = $sms->getMessageText();
              $smsText = str_replace("(username)", $mobileNumber, $smsText);
              $smsText = str_replace("(password)", $password, $smsText);
              ROUTED_SMS::Send($number, $smsText);
              emailLib::sendCustomerRegistrationViaRetail($customer, $order);
              } else {
              $sms = SmsTextPeer::retrieveByPK(7);
              $smsText = $sms->getMessageText();
              ROUTED_SMS::Send($number, $smsText);
              $message=$sms->getMessageText()."<br>Mobile Number=".$number."<br>Text=".$text;
              emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
              die;
              }

              //End of Registration.
             */
            } else {
                $c = new Criteria();
                $c->add(CustomerPeer::MOBILE_NUMBER, $mobileNumber);
                $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
                $c->addAnd(CustomerPeer::UNIQUEID, $uniqueId);

                if (CustomerPeer::doCount($c) > 0) {

                    $command = substr($splitedText[0], 0, 2);


                    $command = strtolower($command);
                    echo "<hr/>";
                    echo $command;
                    echo "<hr/>";
                    $customer = CustomerPeer::doSelectOne($c);
                    if ($command == "cb") {

                        echo "Check Balance Request<br/>";
                        $telintaObj = new Telienta();
                        $balance = $telintaObj->getBalance($customer);
                        $sms = SmsTextPeer::retrieveByPK(6);
                        $smsText = $sms->getMessageText();
                        $smsText = str_replace("(balance)", $balance, $smsText);
                        $number;
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $c = new Criteria();
                        $c->add(SmsLogPeer::MOBILE_NUMBER, $number);
                        $c->addAnd(SmsLogPeer::SMS_TYPE, 2);
                        $c->addDescendingOrderByColumn(SmsLogPeer::CREATED_AT);
                        $value = SmsLogPeer::doCount($c);

                        if ($value > 0) {
                            $smsRow = SmsLogPeer::doSelectOne($c);
                            $createdAtValue = $smsRow->getCreatedAt();
                            echo $date1 = $createdAtValue;
                            $asd = 0;
                            $d1 = $date1;
                            $d2 = date("Y-m-d h:m:s");
                            $asd = ((strtotime($d2) - strtotime($d1)) / 3600);
                            $asd = intval($asd);


                            if ($asd > 3) {
                                ROUTED_SMS::Send($number, $smsText, null, 2);
                                die;
                            }
                        } else {
                            ROUTED_SMS::Send($number, $smsText, null, 2);
                            die;
                        }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    } elseif ($command == "re") {/*
                      echo "Recharge Request<br/>";
                      $cc = new Criteria();

                      if(count($splitedText)==5){
                      $cardNumber= $splitedText[4];
                      }else{
                      $cardNumber= $splitedText[$location];
                      }

                      $cc->add(CardNumbersPeer::CARD_NUMBER,"00880".$cardNumber);
                      $cc->addAnd(CardNumbersPeer::STATUS, 0);
                      if (CardNumbersPeer::doCount($cc) == 1) {
                      $scratchCard = CardNumbersPeer::doSelectOne($cc);
                      //new order
                      $order = new CustomerOrder();
                      $customer_products = $customer->getProducts();
                      $order->setProduct($customer_products[0]);
                      $order->setCustomer($customer);
                      $order->setQuantity(1);
                      $order->setExtraRefill($scratchCard->getCardPrice());
                      $order->save();

                      //new transaction
                      $transaction = new Transaction();
                      $transaction->setAmount($scratchCard->getCardPrice());
                      $transactiondescription =  TransactionDescriptionPeer::retrieveByPK(8);
                      $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
                      $transaction->setTransactionDescriptionId($transactiondescription->getId());
                      $transaction->setDescription($transactiondescription->getTitle());
                      $transaction->setOrderId($order->getId());
                      $transaction->setCustomerId($order->getCustomerId());
                      $transaction->save();
                      $telintaObj = new Telienta();
                      if ($telintaObj->recharge($customer, $scratchCard->getCardPrice(), $transactiondescription->getTitle())) {
                      $scratchCard->setStatus(1);
                      $scratchCard->setUsedAt(date("Y-m-d H:i:s"));
                      $scratchCard->setCustomerId($customer->getId());
                      $scratchCard->save();
                      $order->setOrderStatusId(3);
                      $order->save();
                      $transaction->setTransactionStatusId(3);
                      $transaction->save();

                      // Send Customer Balance SMS after succesful recharge
                      $balance = $telintaObj->getBalance($customer);
                      $sms = SmsTextPeer::retrieveByPK(6);
                      $smsText = $sms->getMessageText();
                      $smsText = str_replace("(balance)", $balance, $smsText);
                      ROUTED_SMS::Send($number, $smsText);
                      // Send email to Support after Recharge
                      emailLib::sendRetailRefillEmail($customer, $order);
                      } else {
                      echo "Unable to charge";
                      $sms = SmsTextPeer::retrieveByPK(8);
                      ROUTED_SMS::Send($number, $sms->getMessageText());
                      $message=$sms->getMessageText()."<br>Unable to charge due to telinta issue Mobile Number=".$number."<br>Text=".$text;
                      emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
                      }
                      } else {
                      echo "CARD ALREADY USED<br/>";
                      $sms = SmsTextPeer::retrieveByPK(7);
                      ROUTED_SMS::Send($number, $sms->getMessageText());
                      $message=$sms->getMessageText()."<br>CARD:".$cardNumber."  ALREADY USED<br/>Mobile Number=".$number."<br>Text=".$text;
                      emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
                      }
                      die; */
                    }
                } else {
                    echo "Invalid Command 1";
                    $sms = SmsTextPeer::retrieveByPK(8);
                    ROUTED_SMS::Send($number, $sms->getMessageText());
                    $message = $sms->getMessageText() . "<br>Invalid Command<br/>Mobile Number=" . $number . "<br>Text=" . $text;
                    emailLib::sendErrorInAutoReg("Auto Registration Error:", $message);
                    die;
                }
            }
        }
        return sfView::NONE;
    }

    public function executeAutorefil(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //echo "get customers to refill";
        $c = new Criteria();

        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $c->addAnd(CustomerPeer::AUTO_REFILL_AMOUNT, 0, Criteria::NOT_EQUAL);
        //$c->addAnd(CustomerPeer::UNIQUEID, 99999, Criteria::GREATER_EQUAL);
        $c->addAnd(CustomerPeer::TICKETVAL, null, Criteria::ISNOTNULL);
        $c->addDescendingOrderByColumn(CustomerPeer::CREATED_AT);
        //$c1 = $c->getNewCriterion(CustomerPeer::LAST_AUTO_REFILL, 'TIMESTAMPDIFF(MINUTE, LAST_AUTO_REFILL, NOW()) > 1' , Criteria::CUSTOM);
        $c1 = $c->getNewCriterion(CustomerPeer::ID, null, Criteria::ISNOTNULL); //just accomodate missing disabled $c1
        $c2 = $c->getNewCriterion(CustomerPeer::LAST_AUTO_REFILL, null, Criteria::ISNULL);

        //$c1->addOr($c2);
        //$c->add($c1);

        $vt = 0;

        $customer = new Customer();

        $vt = CustomerPeer::doCount($c);


        if ($vt > 0) {

            $i = 0;
            $customers = CustomerPeer::doSelect($c);

            foreach ($customers as $customer) {

                //echo "UniqueID:";
                $uniqueId = $customer->getUniqueid();
                if ((int) $uniqueId > 200000) {
                    $Tes = ForumTel::getBalanceForumtel($customer->getId());

                    $customer_balance = $Tes;
                } else {
                    //echo "This is for Retrieve balance From Telinta"."<br/>";
                    $telintaGetBalance = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=getbalance&name=' . $uniqueId . '&type=customer');
                    sleep(0.25);
                    if (!$telintaGetBalance) {
                        //emailLib::sendErrorInTelinta("Error in Balance Fetching", "We have faced an issue in autorefill on telinta. this is the error on the following url https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=getbalance&name=" . $uniqueId . "&type=customer. <br/> Please Investigate.");
                        continue;
                    }
                    parse_str($telintaGetBalance);
                    if (isset($success) && $success != "OK") {
                        emailLib::sendErrorInTelinta("Error in Balance Status", "We have faced an issue in autorefill on telinta. after fetching data from the following url https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=getbalance&name=" . $uniqueId . "&type=customer. we are unable to find the status in the string <br/> Please Investigate.");
                        continue;
                    }
                    $customer_balance = $Balance * (-1);
                }
                echo "<br/>";
                // $customer_balance = Fonet::getBalance($customer);
                //if customer balance is less than 10
                if ($customer_balance != null && (float) $customer_balance <= (float) $customer->getAutoRefillMinBalance()) {


                    echo $customer_balance;
                    $customer_id = $customer->getId();

                    $this->customer = CustomerPeer::retrieveByPK($customer_id);

                    $this->order = new CustomerOrder();

                    $customer_products = $this->customer->getProducts();

                    $this->order->setProduct($customer_products[0]);
                    $this->order->setCustomer($this->customer);
                    $this->order->setQuantity(1);
                    $this->order->setExtraRefill($customer->getAutoRefillAmount());
                    $this->order->save();


                    $transaction = new Transaction();

                    $transaction->setAmount($this->order->getExtraRefill());
                    $transaction->setDescription($this->getContext()->getI18N()->__('Auto Refill'));
                    $transaction->setOrderId($this->order->getId());
                    $transaction->setCustomerId($this->order->getCustomerId());


                    $transaction->save();



                    $order_id = $this->order->getId();
                    $total = 100 * $this->order->getExtraRefill();
                    $tickvalue = $this->customer->getTicketval();
                    $form = new Curl_HTTP_Client();


//echo "pretend to be IE6 on windows";
///////$post_data = array(
//    'merchant' => '90049676',
//    'amount' => $total,
//    'currency' => '752',
//    'orderid' => $order_id,
//    'textreply' => true,
//    'test' => 'foo',
//    'account' => 'YTIP',
//    'status' => '',
//    'ticket' =>$tickvalue,
//    'lang' => 'sv',
//    'HTTP_COOKIE' => getenv("HTTP_COOKIE"),
//    'cancelurl' => "http://landncall.zerocall.com/b2c.php/",
//    'callbackurl' => "http://landncall.zerocall.com/b2c_dev.php/pScripts/autorefilconfirmation?accept=yes&subscriptionid=&orderid=$order_id&amount=$total",
//    'accepturl' => "http://landncall.zerocall.com/b2c.php/"
//);
                    $form->set_user_agent("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                    $form->set_referrer(sfConfig::get('app_customer_url'));
                    $post_data = array(
                        'merchant' => '90049676',
                        'amount' => $total,
                        'currency' => '752',
                        'orderid' => $order_id,
                        'textreply' => true,
                        'account' => 'YTIP',
                        'status' => '',
                        'ticket' => $tickvalue,
                        'lang' => 'sv',
                        'HTTP_COOKIE' => getenv("HTTP_COOKIE"),
                        'cancelurl' => sfConfig::get('app_customer_url'),
                        'callbackurl' => sfConfig::get('app_customer_url') . "pScripts/autorefilconfirmation?accept=yes&subscriptionid=&orderid=$order_id&amount=$total",
                        'accepturl' => sfConfig::get('app_customer_url')
                    );
//var_dump($post_data);
//echo "<br/>Baran<br/>";

                    $html_data = $form->send_post_data("https://payment.architrade.com/cgi-ssl/ticket_auth.cgi", $post_data);
//echo $html_data;
//echo "<br/>";
                    // die("khan");
                }

                sleep(0.5);
            }
        }

        return sfView::NONE;
        // $this->setLayout(false);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    public function executeAutorefilconfirmation(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);

        $urlval = 0;
        $urlval = "autorefil-" . $request->getParameter('transact');

        $email2 = new DibsCall();
        $email2->setCallurl($urlval);

        $email2->save();
        $urlval = $request->getParameter('transact');
        if (isset($urlval) && $urlval > 0) {
            $order_id = $request->getParameter("orderid");

            $this->forward404Unless($order_id || $order_amount);

            $order = CustomerOrderPeer::retrieveByPK($order_id);

            $order_amount = ((double) $request->getParameter('amount')) / 100;

            $this->forward404Unless($order);

            $c = new Criteria;
            $c->add(TransactionPeer::ORDER_ID, $order_id);

            $transaction = TransactionPeer::doSelectOne($c);

            //echo var_dump($transaction);

            $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
            //$order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_completed', 3)); //completed
            $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed




            if ($transaction->getAmount() > $order_amount) {
                //error
                $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
                $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
                //$order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_completed', 5)); //error in amount
            } else if ($transaction->getAmount() < $order_amount) {
                //$extra_refill_amount = $order_amount;
                $order->setExtraRefill($order_amount);
                $transaction->setAmount($order_amount);
            }
            //set active agent_package in case customer was registerred by an affiliate
            if ($order->getCustomer()->getAgentCompany()) {
                $order->setAgentCommissionPackageId($order->getCustomer()->getAgentCompany()->getAgentCommissionPackageId());
            }

            //set subscription id in case 'use current c.c for future auto refills' is set to 1
            //set auto_refill amount

            $order->save();
            $transaction->save();
            TransactionPeer::AssignReceiptNumber($transaction);
            $this->customer = $order->getCustomer();
            $c = new Criteria;
            $c->add(CustomerPeer::ID, $order->getCustomerId());
            $customer = CustomerPeer::doSelectOne($c);

            $customer->setLastAutoRefill(date('Y-m-d H:i:s'));
            $customer->save();
            echo "ag" . $agentid = $customer->getReferrerId();
            echo "prid" . $productid = $order->getProductId();
            echo "trid" . $transactionid = $transaction->getId();
            if (isset($agentid) && $agentid != "") {
                echo "getagentid";
                commissionLib::refilCustomer($agentid, $productid, $transactionid);
            }
            //TODO ask if recharge to be done is same as the transaction amount
            //die;
            //  Fonet::recharge($this->customer, $transaction->getAmount());
            $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
            if ($getFirstnumberofMobile == 0) {
                $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
            } else {
                $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
            }
            //$TelintaMobile = sfConfig::get('app_country_code').$this->customer->getMobileNumber();
            $emailId = $this->customer->getEmail();
            $uniqueId = $this->customer->getUniqueid();
            $OpeningBalance = $transaction->getAmount();
            //This is for Recharge the Customer
            if ((int) $uniqueId > 200000) {
                $cuserid = $this->customer->getId();
                $amt = $OpeningBalance;
                $amt = CurrencyConverter::convertSekToUsd($amt);
                $Test = ForumTel::rechargeForumtel($cuserid, $amt);
            } else {


                $MinuesOpeningBalance = $OpeningBalance * 3;
                $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=' . $uniqueId . '&amount=' . $OpeningBalance . '&type=customer');
            }
            //This is for Recharge the Account
            //this condition for if follow me is Active
            $getvoipInfo = new Criteria();
            $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $this->customer->getMobileNumber());
            $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
            if (isset($getvoipInfos)) {
                $voipnumbers = $getvoipInfos->getNumber();
                $voip_customer = $getvoipInfos->getCustomerId();
                //  $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name='.$voipnumbers.'&amount='.$OpeningBalance.'&type=account');
            } else {
                //  $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name='.$uniqueId.'&amount='.$OpeningBalance.'&type=account');
            }
            // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=a'.$TelintaMobile.'&amount='.$OpeningBalance.'&type=account');
            // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=cb'.$TelintaMobile.'&amount='.$OpeningBalance.'&type=account');

            $MinuesOpeningBalance = $OpeningBalance * 3;
            //type=<account_customer>&action=manual_charge&name=<name>&amount=<amount>
            //This is for Recharge the Customer
            // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=customer&action=manual_charge&name='.$uniqueId.'&amount='.$MinuesOpeningBalance);
//echo 'NOOO';
// Update cloud 9
            //c9Wrapper::equateBalance($this->customer);
//echo 'Comeing';
            //set vat
            $vat = 0;
            $subject = $this->getContext()->getI18N()->__('Payment Confirmation');
            $sender_email = sfConfig::get('app_email_sender_email', 'support@landncall.com');
            $sender_name = sfConfig::get('app_email_sender_name', 'LandNCall AB support');

            $recepient_email = trim($this->customer->getEmail());
            $recepient_name = sprintf('%s %s', $this->customer->getFirstName(), $this->customer->getLastName());
            $referrer_id = trim($this->customer->getReferrerId());
            if ($referrer_id):
                $c = new Criteria();
                $c->add(AgentCompanyPeer::ID, $referrer_id);

                $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
                $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
            endif;

            //send email
            $message_body = $this->getPartial('pScripts/order_receipt', array(
                'customer' => $this->customer,
                'order' => $order,
                'transaction' => $transaction,
                'vat' => $vat,
                'wrap' => false
            ));


            $this->setPreferredCulture($this->customer);

            emailLib::sendCustomerRefillEmail($this->customer, $order, $transaction);
            $this->updatePreferredCulture();
        }
    }

    public function executeUsageAlert(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);
        //-----------------------

        $CallCode = sfConfig::get('app_country_code');
        $countryId = "1";

        $usagealerts = new Criteria();
        $usagealerts->add(UsageAlertPeer::COUNTRY, $countryId);
        $usageAlerts = UsageAlertPeer::doSelect($usagealerts);
        $c = new Criteria();
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $c->addAnd(CustomerPeer::COUNTRY_ID, $countryId);
        $customers = CustomerPeer::doSelect($c);
        $telintaObj = new Telienta();
        foreach ($customers as $customer) {
            $retries = 0;
            $maxRetries = 5;
            do {

                $customer_balance = $telintaObj->getBalance($customer);
                $retries++;
                echo $customer->getId() . ":" . $customer_balance . ":" . $retries . "<br/>";
            } while (!$customer_balance && $retries <= $maxRetries);

            if ($retries == ++$maxRetries) {
                continue;
            }

            $customer_balance = (double) $customer_balance;

            $actual_balance = $customer_balance;
            if ($customer_balance < 1) {
                $customer_balance = 0;
            }
            foreach ($usageAlerts as $usageAlert) {
                //echo "<hr/>".$usageAlert->getId()."<hr/>";
                if ($customer_balance >= $usageAlert->getAlertAmountMin() && $customer_balance < $usageAlert->getAlertAmountMax()) {

                    $sender = new Criteria();
                    $sender->add(UsageAlertSenderPeer::ID, $usageAlert->getSenderName());
                    $senders = UsageAlertSenderPeer::doSelectOne($sender);
                    echo $senderName = $senders->getName();
                    echo "<br />";
                    echo $usageAlert->getId();

                    $regType = RegistrationTypePeer::retrieveByPK($customer->getRegistrationTypeId()); // && $customer->getFonetCustomerId()!=''
                    $referer = $customer->getReferrerId();
                    if (isset($referer) && $referer > 0) {
                        $Cname = new Criteria();
                        $Cname->add(AgentCompanyPeer::ID, $referer);
                        $Companies = AgentCompanyPeer::doSelectOne($Cname);
                        $comName = $Companies->getName();
                    } else {
                        $comName = "";
                    }
                    $Prod = new Criteria();
                    $Prod->addJoin(ProductPeer::ID, CustomerProductPeer::PRODUCT_ID, Criteria::LEFT_JOIN);
                    $Prod->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
                    $Product = ProductPeer::doSelectOne($Prod);

                    $cSMSent = new Criteria();
                    $cSMSent->add(SmsAlertSentPeer::USAGE_ALERT_STATUS_ID, $usageAlert->getId());
                    $cSMSent->addAnd(SmsAlertSentPeer::CUSTOMER_ID, $customer->getId());
                    $cSMSentCount = SmsAlertSentPeer::doCount($cSMSent);

                    if ($usageAlert->getSmsActive() && $cSMSentCount == 0) {
                        echo "Sms Alert Sent:";
                        $msgSent = new SmsAlertSent();
                        $msgSent->setCustomerId($customer->getId());
                        $msgSent->setCustomerName($customer->getFirstName());
                        $msgSent->setCustomerProduct($Product->getName());
                        $msgSent->setRegistrationType($regType->getDescription());
                        $msgSent->setAgentName($comName);
                        $msgSent->setCustomerEmail($customer->getEmail());
                        $msgSent->setMobileNumber($customer->getMobileNumber());
                        $msgSent->setUsageAlertStatusId($usageAlert->getId());
                        $msgSent->setAlertActivated($customer->getUsageAlertSMS());
                        //$msgSent->setFonetCustomerId($customer->getFonetCustomerId());
                        $msgSent->setMessageDescerption("Current Balance: " . $actual_balance);
                        //$msgSent->save();
                        /**
                         * SMS Sending Code
                         * */
                        if ($customer->getUsageAlertSMS()) {
                            echo "SMS Active<br/>";
                            $customerMobileNumber = $CallCode . $customer->getMobileNumber();
                            //die($customerMobileNumber);
                            //    $customerMobileNumber = "923334414765";
                            $sms_text = $usageAlert->getSmsAlertMessage();
                            $this->setPreferredCulture($customer);
                            //$sms_text = $this->getContext()->getI18N()->__("Sms Alert Sent");
                            $response = ROUTED_SMS::Send($customerMobileNumber, $sms_text, $senderName);
                            $this->updatePreferredCulture();
                            if ($response) {
                                $msgSent->setAlertSent(1);
                            }
                        }
                        $msgSent->save();
                    }

                    $cEmailSent = new Criteria();
                    $cEmailSent->add(EmailAlertSentPeer::USAGE_ALERT_STATUS_ID, $usageAlert->getId());
                    $cEmailSent->addAnd(EmailAlertSentPeer::CUSTOMER_ID, $customer->getId());
                    $cEmailSentCount = EmailAlertSentPeer::doCount($cEmailSent);

                    if ($usageAlert->getEmailActive() && $cEmailSentCount == 0) {
                        echo "Email Alert Sent:";
                        $msgSentE = new EmailAlertSent();
                        $msgSentE->setCustomerId($customer->getId());
                        $msgSentE->setCustomerName($customer->getFirstName());
                        $msgSentE->setCustomerProduct($Product->getName());
                        $msgSentE->setRegistrationType($regType->getDescription());
                        $msgSentE->setAgentName($comName);
                        $msgSentE->setCustomerEmail($customer->getEmail());
                        $msgSentE->setMobileNumber($customer->getMobileNumber());
                        $msgSentE->setUsageAlertStatusId($usageAlert->getId());
                        $msgSentE->setAlertActivated($customer->getUsageAlertEmail());
                        //$msgSentE->setFonetCustomerId($customer->getFonetCustomerId());
                        $msgSentE->setMessageDescerption("Current Balance: " . $actual_balance);
                        //$msgSentE->save();

                        if ($customer->getUsageAlertEmail()) {
                            echo "Email Active<br/>";
                            $message = $usageAlert->getEmailAlertMessage();
                            $this->setPreferredCulture($customer);
                            emailLib::sendCustomerBalanceEmail($customer, $message);
                            $this->updatePreferredCulture();
                            $msgSentE->setAlertSent(1);
                        }
                        $msgSentE->save();
                    }
                }
            }
        }

        return sfView::NONE;
    }

    public function executeAbcTest(sfWebRequest $request) {
        $Parameters = "testtsts" . $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);

        $email2->save();
        return sfView::NONE;
    }

    public function executeAgentRefillThankyou(sfWebRequest $request) {

        $Parameters = $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);
        $email2->save();

        //$order_id = $request->getParameter('orderid');
        //$amount = $request->getParameter('amount');

        $callbackparameters = $request->getParameter("p");
        $params = explode("-", $callbackparameters);

        $order_id = $params[0];
        $order_amount = $params[1];

        if ($order_id) {
            $c = new Criteria();
            $c->add(AgentOrderPeer::AGENT_ORDER_ID, $order_id);
            $c->add(AgentOrderPeer::STATUS, 1);
            $agent_order = AgentOrderPeer::doSelectOne($c);

            // $agent_order->setAmount($amount);
            $agent_order->setStatus(3);
            $agent_order->save();
            TransactionPeer::AssignAgentReceiptNumber($agent_order);
            $agent = AgentCompanyPeer::retrieveByPK($agent_order->getAgentCompanyId());
            $agent->setBalance($agent->getBalance() + ($agent_order->getAmount()));
            $agent->save();
            $this->agent = $agent;

            $amount = $agent_order->getAmount();
            $remainingbalance = $agent->getBalance();
            $aph = new AgentPaymentHistory();
            $aph->setAgentId($agent_order->getAgentCompanyId());
            $aph->setExpeneseType(3);
            $aph->setAmount($agent_order->getAmount());
            $aph->setRemainingBalance($remainingbalance);
            $aph->save();

            emailLib::sendAgentRefilEmail($this->agent, $agent_order);
        }

        return sfView::NONE;
    }

    public function executeCalbackrefill(sfWebRequest $request) {

        $Parameters = $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);
        $email2->save();

        // call back url $p="es-297-100"; lang_orderid_amount

        $callbackparameters = $request->getParameter("p");
        $params = explode("-", $callbackparameters);

        $lang = $params[0];
        $order_id = $params[1];
        $order_amount = $params[2];

        $this->getUser()->setCulture($lang);

        //$order_id = $request->getParameter("order_id");
        $this->forward404Unless($order_id);

        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $this->forward404Unless($order);

        //$order_amount = ((double) $request->getParameter('amount'));

        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);
        if ($order_amount == "")
            $order_amount = $transaction->getAmount();

        $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed
        if ($transaction->getAmount() > $order_amount) {
            //error
            $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->save();
            die;
        } else if (number_format($transaction->getAmount(), 2) < $order_amount) {
            //$extra_refill_amount = $order_amount;
            $order->setExtraRefill($order_amount);
            $transaction->setAmount($order_amount);
        }
        //set active agent_package in case customer was registerred by an affiliate
        if ($order->getCustomer()->getAgentCompany()) {
            $order->setAgentCommissionPackageId($order->getCustomer()->getAgentCompany()->getAgentCommissionPackageId());
        }
        $ticket_id = $request->getParameter('transact');

        $order->save();
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);
        $this->customer = $order->getCustomer();
        $c = new Criteria;
        $c->add(CustomerPeer::ID, $order->getCustomerId());
        $customer = CustomerPeer::doSelectOne($c);
        echo "ag" . $agentid = $customer->getReferrerId();
        echo "prid" . $productid = $order->getProductId();
        echo "trid" . $transactionid = $transaction->getId();
        if (isset($agentid) && $agentid != "") {
            echo "getagentid";
            commissionLib::refilCustomer($agentid, $productid, $transactionid);
            $transaction->setAgentCompanyId($agentid);
            $transaction->save();
        }

        //TODO ask if recharge to be done is same as the transaction amount
        //die;
        $exest = $order->getExeStatus();
        if ($exest == 1) {
            
        } else {
            //  Fonet::recharge($this->customer, $transaction->getAmount());
            $vat = 0;

            $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
            $emailId = $this->customer->getEmail();
            $OpeningBalance = $transaction->getAmount();
            $customerPassword = $this->customer->getPlainText();
            $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
            if ($getFirstnumberofMobile == 0) {
                $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
            } else {
                $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
            }

            $unidc = $this->customer->getUniqueid();

            echo $unidc;
            echo "<br/>";
            $OpeningBalance = $order->getExtraRefill();
            $telintaObj = new Telienta();
            $telintaObj->recharge($this->customer, $OpeningBalance, 'Refill');

            $getvoipInfo = new Criteria();
            $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $this->customer->getMobileNumber());
            $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
            if (isset($getvoipInfos)) {
                $voipnumbers = $getvoipInfos->getNumber();
                $voip_customer = $getvoipInfos->getCustomerId();
            } else {
                
            }
            $MinuesOpeningBalance = $OpeningBalance * 3;

            $subject = $this->getContext()->getI18N()->__('Payment Confirmation');
            $sender_email = sfConfig::get('app_email_sender_email', 'support@kimarin.es');
            $sender_name = sfConfig::get('app_email_sender_name', 'Kimarin support');

            $recepient_email = trim($this->customer->getEmail());
            $recepient_name = sprintf('%s %s', $this->customer->getFirstName(), $this->customer->getLastName());
            $referrer_id = trim($this->customer->getReferrerId());

            if ($referrer_id):
                $c = new Criteria();
                $c->add(AgentCompanyPeer::ID, $referrer_id);

                $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
                $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
            endif;

            //send email

            $unidid = $this->customer->getUniqueid();
            $agent_company_id = $transaction->getAgentCompanyId();
            if ($agent_company_id != '') {
                $c = new Criteria();
                $c->add(AgentCompanyPeer::ID, $agent_company_id);

                $agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
            } else {
                $agent_name = '';
            }
            $message_body = $this->getPartial('payments/order_receipt', array(
                'customer' => $this->customer,
                'order' => $order,
                'transaction' => $transaction,
                'vat' => $vat,
                'agent_name' => $agent_name,
                'wrap' => false
            ));


            $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerRefillEmail($this->customer, $order, $transaction);
            $this->updatePreferredCulture();
        }

        $order->setExeStatus(1);
        $order->save();
        echo 'Yes';
        return sfView::NONE;
    }

    public function executeConfirmpayment(sfWebRequest $request) {

        $Parameters = $request->getURI();

        // $Parameters=$Parameters.$request->getParameter('amount');
        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);

        $email2->save();

        $order_id = "";
        $order_amount = "";

        // call back url $p="es-297-100"; lang-orderid-amount

        $callbackparameters = $request->getParameter("p");
        $params = explode("-", $callbackparameters);

        $lang = $params[0];
        $order_id = $params[1];
        $order_amount = $params[2];
        $this->getUser()->setCulture($lang);

        $ticket_id = "";
        //  $this->getUser()->setCulture($request->getParameter('lng'));


        if ($order_id != '') {

            $this->logMessage(print_r($_GET, true));

            $is_transaction_ok = false;
            $subscription_id = '';

            $this->forward404Unless($order_id);
            //$this->forward404Unless($order_id || $order_amount);
            //get order object
            $order = CustomerOrderPeer::retrieveByPK($order_id);


            if (isset($ticket_id) && $ticket_id != "") {

                $subscriptionvalue = 0;

                $subscriptionvalue = $request->getParameter('subscriptionid');


                if (isset($subscriptionvalue) && $subscriptionvalue > 1) {
//  echo 'is autorefill activated';
                    //auto_refill_amount
                    $auto_refill_amount_choices = array_keys(ProductPeer::getRefillHashChoices());

                    $auto_refill_amount = in_array($request->getParameter('user_attr_2'), $auto_refill_amount_choices) ? $request->getParameter('user_attr_2') : $auto_refill_amount_choices[0];
                    $order->getCustomer()->setAutoRefillAmount($auto_refill_amount);


                    //auto_refill_lower_limit
                    $auto_refill_lower_limit_choices = array_keys(ProductPeer::getAutoRefillLowerLimitHashChoices());

                    $auto_refill_min_balance = in_array($request->getParameter('user_attr_3'), $auto_refill_lower_limit_choices) ? $request->getParameter('user_attr_3') : $auto_refill_lower_limit_choices[0];
                    $order->getCustomer()->setAutoRefillMinBalance($auto_refill_min_balance);

                    $order->getCustomer()->setTicketval($ticket_id);
                    $order->save();
                    $auto_refill_amount = "refill amount" . $auto_refill_amount;
                    $email2d = new DibsCall();
                    $email2d->setCallurl($auto_refill_amount);
                    $email2d->save();
                    $minbalance = "min balance" . $auto_refill_min_balance;
                    $email2dm = new DibsCall();
                    $email2dm->setCallurl($minbalance);
                    $email2dm->save();
                }
            }
            //check to see if that customer has already purchased this product
            $c = new Criteria();
            $c->add(CustomerProductPeer::CUSTOMER_ID, $order->getCustomerId());
            $c->addAnd(CustomerProductPeer::PRODUCT_ID, $order->getProductId());
            $c->addJoin(CustomerProductPeer::CUSTOMER_ID, CustomerPeer::ID);
            $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, sfConfig::get('app_status_new'), Criteria::NOT_EQUAL);

            // echo 'retrieve order id: '.$order->getId().'<br />';

            if (CustomerProductPeer::doCount($c) != 0) {

                //Customer is already registered.
                //echo __('The customer is already registered.');
                //exit the script successfully
                return sfView::NONE;
            }

            //set subscription id
            //$order->getCustomer()->setSubscriptionId($subscription_id);
            //set auto_refill amount
            //if order is already completed > 404
            $this->forward404Unless($order->getOrderStatusId() != sfConfig::get('app_status_completed'));
            $this->forward404Unless($order);

            //  echo 'processing order <br />';

            $c = new Criteria;
            $c->add(TransactionPeer::ORDER_ID, $order_id);
            $transaction = TransactionPeer::doSelectOne($c);
            $order_amount = $transaction->getAmount();
            //  echo 'retrieved transaction<br />';

            if ($transaction->getAmount() > $order_amount) {
                //error
                $order->setOrderStatusId(sfConfig::get('app_status_error')); //error in amount
                $transaction->setTransactionStatusId(sfConfig::get('app_status_error')); //error in amount
                $order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_error')); //error in amount
                echo 'setting error <br /> ';
            } elseif (number_format($transaction->getAmount(), 2) < $order_amount) {
                $transaction->setAmount($order_amount);
            }

            $order->setOrderStatusId(sfConfig::get('app_status_completed')); //completed
            $order->getCustomer()->setCustomerStatusId(sfConfig::get('app_status_completed')); //completed
            $transaction->setTransactionStatusId(3); //completed
            $transactiondescription = TransactionDescriptionPeer::retrieveByPK(8);
            $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
            $transaction->setTransactionDescriptionId($transactiondescription->getId());
            $transaction->setDescription($transactiondescription->getTitle());
            // echo 'transaction=ok <br /> ';
            $is_transaction_ok = true;

            $order->setQuantity(1);
            // $order->getCustomer()->getAgentCompany();
            //set active agent_package in case customer
            if ($order->getCustomer()->getAgentCompany()) {
                $order->setAgentCommissionPackageId($order->getCustomer()->getAgentCompany()->getAgentCommissionPackageId());
                $transaction->setAgentCompanyId($order->getCustomer()->getReferrerId()); //completed
            }

            $order->save();
            $transaction->save();
            TransactionPeer::AssignReceiptNumber($transaction);
            if ($is_transaction_ok) {

                // echo 'Assigning Customer ID <br/>';
                //set customer's proudcts in use
                $customer_product = new CustomerProduct();

                $customer_product->setCustomer($order->getCustomer());
                $customer_product->setProduct($order->getProduct());

                $customer_product->save();

                //register to fonet
                $this->customer = $order->getCustomer();

                //Fonet::registerFonet($this->customer);
                //recharge the extra_refill/initial balance of the prouduct
                //Fonet::recharge($this->customer, $order->getExtraRefill());

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


                $uniqueId = $this->customer->getUniqueid();
                echo $uniqueId . "<br/>";
                $uc = new Criteria();
                $uc->add(UniqueIdsPeer::UNIQUE_NUMBER, $uniqueId);
                $selectedUniqueId = UniqueIdsPeer::doSelectOne($uc);
                echo $selectedUniqueId->getStatus() . "<br/>Baran";

                if ($selectedUniqueId->getStatus() == 0) {
                    echo "inside";
                    $selectedUniqueId->setStatus(1);
                    $selectedUniqueId->setAssignedAt(date('Y-m-d H:i:s'));
                    $selectedUniqueId->save();
                } else {
                    $uc = new Criteria();
                    $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 1);
                    $uc->addAnd(UniqueIdsPeer::STATUS, 0);
                    $uc->addAnd(UniqueIdsPeer::SIM_TYPE_ID, $this->customer->getSimTypeId());
                    $availableUniqueCount = UniqueIdsPeer::doCount($uc);
                    $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);

                    if ($availableUniqueCount == 0) {
                        // Unique Ids are not avaialable. Then Redirect to the sorry page and send email to the support.
                        emailLib::sendUniqueIdsShortage($this->customer->getSimTypeId());
                        exit;
                        //$this->redirect($this->getTargetUrl().'customer/shortUniqueIds');
                    }
                    $uniqueId = $availableUniqueId->getUniqueNumber();
                    $this->customer->setUniqueid($uniqueId);
                    $this->customer->save();
                    $availableUniqueId->setStatus(1);
                    $availableUniqueId->setAssignedAt(date('Y-m-d H:i:s'));
                    $availableUniqueId->save();
                }



                $callbacklog = new CallbackLog();
                $callbacklog->setMobileNumber($TelintaMobile);
                $callbacklog->setuniqueId($uniqueId);
                $callbacklog->setCallingcode(sfConfig::get("app_country_code"));
                $callbacklog->setCheckStatus(3);
                $callbacklog->save();




                $emailId = $this->customer->getEmail();
                $OpeningBalance = $order->getExtraRefill();
                $customerPassword = $this->customer->getPlainText();
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //Section For Telinta Add Cusomter
                $telintaObj = new Telienta();
                $telintaObj->ResgiterCustomer($this->customer, $OpeningBalance);
                // For Telinta Add Account

                $telintaObj->createAAccount($TelintaMobile, $this->customer);
                $telintaObj->createCBAccount($TelintaMobile, $this->customer);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //if the customer is invited, Give the invited customer a bonus of 10
                $invite_c = new Criteria();
                $invite_c->add(InvitePeer::INVITE_NUMBER, $this->customer->getMobileNumber());
                $invite_c->add(InvitePeer::INVITE_STATUS, 2);
                $invite = InvitePeer::doSelectOne($invite_c);
                if ($invite) {
                    $invite->setInviteStatus(3);
                    $invite->setInvitedCustomerId($this->customer->getId());
                    $products = new Criteria();
                    $products->add(ProductPeer::ID, 2);
                    $products = ProductPeer::doSelectOne($products);
                    $extrarefill = $products->getInitialBalance();
                    //if the customer is invited, Give the invited customer a bonus of 10
                    $inviteOrder = new CustomerOrder();
                    $inviteOrder->setProductId(2);
                    $inviteOrder->setQuantity(1);
                    $inviteOrder->setOrderStatusId(3);
                    $inviteOrder->setIsFirstOrder(4);
                    $inviteOrder->setCustomerId($invite->getCustomerId());
                    $inviteOrder->setExtraRefill($extrarefill);
                    $inviteOrder->save();
                    $OrderId = $inviteOrder->getId();
                    // make a new transaction to show in payment history
                    $transaction_i = new Transaction();

                    $transaction_i->setAmount($extrarefill);
                    $transactiondescriptionB = TransactionDescriptionPeer::retrieveByPK(10);
                    $transaction_i->setTransactionTypeId($transactiondescriptionB->getTransactionType());
                    $transaction_i->setTransactionDescriptionId($transactiondescriptionB->getId());
                    $transaction_i->setDescription($transactiondescriptionB->getTitle());

                    $transaction_i->setCustomerId($invite->getCustomerId());
                    $transaction_i->setOrderId($OrderId);
                    $transaction_i->setTransactionStatusId(3);

                    $this->customers = CustomerPeer::retrieveByPK($invite->getCustomerId());

                    //send Telinta query to update the balance of invite by 10
                    $getFirstnumberofMobile = substr($this->customers->getMobileNumber(), 0, 1);     // bcdef
                    if ($getFirstnumberofMobile == 0) {
                        $TelintaMobile = substr($this->customers->getMobileNumber(), 1);
                        $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
                    } else {
                        $TelintaMobile = sfConfig::get('app_country_code') . $this->customers->getMobileNumber();
                    }
                    $uniqueId = $this->customers->getUniqueid();
                    $OpeningBalance = $extrarefill;
                    //This is for Recharge the Customer
                    $telintaObj = new Telienta();
                    $telintaObj->recharge($this->customers, $OpeningBalance, $transactiondescriptionB->getTitle());

                    //This is for Recharge the Account

                    $transaction_i->save();
                    TransactionPeer::AssignReceiptNumber($transaction_i);
                    $invite->setBonusTransactionId($transaction_i->getId());
                    $invite->save();

                    $invitevar = $invite->getCustomerId();
                    if (isset($invitevar)) {


                        $inviterCustomer = CustomerPeer::retrieveByPK($invitevar);
                        $this->setPreferredCulture($inviterCustomer);




                        emailLib::sendCustomerConfirmRegistrationEmail($invite->getCustomerId(), $this->customer, NULL, $inviteOrder, $transaction_i);
                        $this->updatePreferredCulture();
                    }
                }
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
                    if ($postalcharges) {
                        $postalcharge = $postalcharges->getCharges();
                    } else {
                        $postalcharge = '';
                    }
                }
                //$product_price = $order->getProduct()->getPrice() - $order->getExtraRefill();
                $product_price = $order->getProduct()->getPrice() - $order->getExtraRefill();

                $product_price_vat = sfConfig::get('app_vat_percentage') * ($order->getProduct()->getRegistrationFee() + $postalcharge);
                $message_body = $this->getPartial('payments/order_receipt', array(
                    'customer' => $this->customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $product_price_vat,
                    'postalcharge' => $postalcharge,
                    'wrap' => true
                ));

                $subject = $this->getContext()->getI18N()->__('Payment Confirmation');
                $sender_email = sfConfig::get('app_email_sender_email', 'support@kimarin.es');
                $sender_name = sfConfig::get('app_email_sender_name', 'Kimarin support');

                $recepient_email = trim($this->customer->getEmail());
                $recepient_name = sprintf('%s %s', $this->customer->getFirstName(), $this->customer->getLastName());


                $agentid = $this->customer->getReferrerId();

                $cp = new Criteria;
                $cp->add(CustomerProductPeer::CUSTOMER_ID, $order->getCustomerId());
                $customerproduct = CustomerProductPeer::doSelectOne($cp);
                $productid = $customerproduct->getId();

                $transactionid = $transaction->getId();
                if (isset($agentid) && $agentid != "") {
                    commissionLib::registrationCommissionCustomer($agentid, $productid, $transactionid);
                }
                $this->setPreferredCulture($this->customer);
                emailLib::sendCustomerRegistrationViaWebEmail($this->customer, $order);
                $this->updatePreferredCulture();
//                $zeroCallOutSMSObject = new ZeroCallOutSMS();
//                $zeroCallOutSMSObject->toCustomerAfterReg($order->getProductId(), $this->customer);
                $this->order = $order;
            }//end if
            else {
                $this->logMessage('Error in transaction.');
            }
        }
        //header('HTTP/1.1 200 OK');
        return sfView::NONE;
    }

    public function executeEmailTest(sfWebRequest $request) {


        $order_id = $request->getParameter('orderId');

        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $customer = CustomerPeer::retrieveByPK($order->getCustomerId());

        if ($order->getIsFirstOrder() == 1) {
            emailLib::sendCustomerRegistrationViaWebEmail($customer, $order);
        } else {
            $c = new Criteria;
            $c->add(TransactionPeer::ORDER_ID, $order_id);
            $transaction = TransactionPeer::doSelectOne($c);
            emailLib::sendCustomerRefillEmail($customer, $order, $transaction);
        }
        return sfView::NONE;
    }

    private function setPreferredCulture(Customer $customer) {
        $this->currentCulture = $this->getUser()->getCulture();
        $preferredLang = PreferredLanguagesPeer::retrieveByPK($customer->getPreferredLanguageId());
        $this->getUser()->setCulture($preferredLang->getLanguageCode());
    }

    private function updatePreferredCulture() {
        $this->getUser()->setCulture($this->currentCulture);
    }

    public function executeSaveCustomerCallHistory(sfWebRequest $request) {
//

        $c = new Criteria;
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customers = CustomerPeer::doSelect($c);
//         $fromdate = mktime(0, 0, 0, 9, 15, 12);
//    echo $this->fromdate = date("Y-m-d", $fromdate);
//    echo "<br/>";
//          $todate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
//       echo $this->todate =date("Y-m-d", $todate);
        foreach ($customers as $customer) {

//        $fromdate = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
//        $this->fromdate = date("Y-m-d", $fromdate);
//        $this->todate = $fromdate;

            $fromdate = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
            $this->fromdate = date("Y-m-d", $fromdate);
            $todate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $this->todate = date("Y-m-d", $todate);
            $telintaObj = new Telienta();
            $tilentaCallHistryResult = $telintaObj->callHistory($customer, $this->fromdate . ' 00:00:00', $this->todate . ' 23:59:59');
            //  var_dump($tilentaCallHistryResult);


            if ($tilentaCallHistryResult) {
                foreach ($tilentaCallHistryResult->xdr_list as $xdr) {


                    $emCalls = new EmployeeCustomerCallhistory();
                    $emCalls->setAccountId($xdr->account_id);
                    $emCalls->setBillStatus($xdr->bill_status);
                    $emCalls->setBillTime($xdr->bill_time);
                    $emCalls->setChargedAmount($xdr->charged_amount);
                    $emCalls->setChargedQuantity($xdr->charged_quantity);
                    $emCalls->setPhoneNumber($xdr->CLD);
                    $emCalls->setCli($xdr->CLI);
                    $emCalls->setConnectTime($xdr->connect_time);

                    $country = $xdr->country;
                    $cc = new Criteria();
                    $cc->add(CountryPeer::NAME, $country, Criteria::LIKE);
                    $ccount = CountryPeer::doCount($cc);
                    if ($ccount > 0) {
                        $csel = CountryPeer::doSelectOne($cc);
                        $countryid = $csel->getId();
                    } else {
                        $cin = new Country();
                        $cin->setName($country);
                        $cin->save();
                        $countryid = $cin->getId();
                    }
                    $emCalls->setParentTable('customer');
                    $emCalls->setCountryId($countryid);
                    $ce = new Criteria();
                    $ce->add(TelintaAccountsPeer::ACCOUNT_TITLE, $xdr->account_id);
                    $ce->addAnd(TelintaAccountsPeer::PARENT_TABLE, 'customer');
                    $ce->add(TelintaAccountsPeer::STATUS, 3);
                    if (TelintaAccountsPeer::doCount($ce) > 0) {
                        $emp = TelintaAccountsPeer::doSelectOne($ce);
                        $emCalls->setParentId($emp->getParentId());
                    }

                    $emCalls->setDescription($xdr->description);
                    $emCalls->setDisconnectCause($xdr->disconnect_cause);
                    $emCalls->setDisconnectTime($xdr->disconnect_time);
                    // $emCalls->setDurationMinutes($duration_minutes);
                    $emCalls->setICustomer($customer->getICustomer());
                    $emCalls->setIXdr($xdr->i_xdr);
                    $emCalls->setStatus(3);
                    $emCalls->setSubdivision($xdr->subdivision);
                    $emCalls->setUnixConnectTime($xdr->unix_connect_time);
                    $emCalls->setUnixDisconnectTime($xdr->unix_disconnect_time);
                    $emCalls->save();
                }
            } else {
                $callsHistory = new CallHistoryCallsLog();
                $callsHistory->setParent('customer');
                $callsHistory->setParentId($customer->getId());
                $callsHistory->setTodate($this->todate);
                $callsHistory->setFromdate($this->fromdate);
                $callsHistory->save();
            }
        }
        return sfView::NONE;
    }

    public function executeSaveResellerCallHistory(sfWebRequest $request) {


        $fromdate = mktime(0, 0, 0, 9, 15, 12);
        echo $this->fromdate = date("Y-m-d", $fromdate);
        echo "<br/>";
        $todate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        echo $this->todate = date("Y-m-d", $todate);

        $telintaObj = new Telienta();
        $tilentaCallHistryResult = $telintaObj->callHistory(82829, $this->fromdate . ' 00:00:00', $this->todate . ' 23:59:59', true);
        //  var_dump($tilentaCallHistryResult);


        if ($tilentaCallHistryResult) {
            foreach ($tilentaCallHistryResult->xdr_list as $xdr) {


                $emCalls = new EmployeeCustomerCallhistory();
                $emCalls->setAccountId($xdr->account_id);
                $emCalls->setBillStatus($xdr->bill_status);
                $emCalls->setBillTime($xdr->bill_time);
                $emCalls->setChargedAmount($xdr->charged_amount);
                $emCalls->setChargedQuantity($xdr->charged_quantity);
                $emCalls->setPhoneNumber($xdr->CLD);
                $emCalls->setCli($xdr->CLI);
                $emCalls->setConnectTime($xdr->connect_time);

                $country = $xdr->country;
                $cc = new Criteria();
                $cc->add(CountryPeer::NAME, $country, Criteria::LIKE);
                $ccount = CountryPeer::doCount($cc);
                if ($ccount > 0) {
                    $csel = CountryPeer::doSelectOne($cc);
                    $countryid = $csel->getId();
                } else {
                    $cin = new Country();
                    $cin->setName($country);
                    $cin->save();
                    $countryid = $cin->getId();
                }
                $emCalls->setParentTable('customer');
                $emCalls->setCountryId($countryid);
                $ce = new Criteria();
                $ce->add(TelintaAccountsPeer::ACCOUNT_TITLE, $xdr->account_id);
                $ce->addAnd(TelintaAccountsPeer::PARENT_TABLE, 'customer');
                $ce->add(TelintaAccountsPeer::STATUS, 3);
                if (TelintaAccountsPeer::doCount($ce) > 0) {
                    $emp = TelintaAccountsPeer::doSelectOne($ce);
                    $emCalls->setParentId($emp->getParentId());
                }

                $emCalls->setDescription($xdr->description);
                $emCalls->setDisconnectCause($xdr->disconnect_cause);
                $emCalls->setDisconnectTime($xdr->disconnect_time);
                // $emCalls->setDurationMinutes($duration_minutes);
                // $emCalls->setICustomer($customer->getICustomer());
                $emCalls->setIXdr($xdr->i_xdr);
                $emCalls->setStatus(3);
                $emCalls->setSubdivision($xdr->subdivision);
                $emCalls->setUnixConnectTime($xdr->unix_connect_time);
                $emCalls->setUnixDisconnectTime($xdr->unix_disconnect_time);
                $emCalls->save();
            }
        } else {
            $callsHistory = new CallHistoryCallsLog();
            $callsHistory->setParent('customer');
            $callsHistory->setParentId($customer->getId());
            $callsHistory->setTodate($this->todate);
            $callsHistory->setFromdate($this->fromdate);
            $callsHistory->save();
        }




        return sfView::NONE;
    }

    public function executeCallHistoryNotFetch(sfWebRequest $request) {

        $c = new Criteria;
        $c->add(CallHistoryCallsLogPeer::STATUS, 1);
        $callLogs = CallHistoryCallsLogPeer::doSelect($c);


        foreach ($callLogs as $callLog) {
            $this->fromdate = $callLog->getFromdate();
            $this->todate = $callLog->getTodate();
            $customer = CustomerPeer::retrieveByPK($callLog->getCustomerId());
            $telintaObj = new Telienta();
            $tilentaCallHistryResult = $telintaObj->callHistory($customer, $this->fromdate . ' 00:00:00', $this->todate . ' 23:59:59');
            if ($tilentaCallHistryResult) {
                foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                    $emCalls = new EmployeeCustomerCallhistory();
                    $emCalls->setAccountId($xdr->account_id);
                    $emCalls->setBillStatus($xdr->bill_status);
                    $emCalls->setBillTime($xdr->bill_time);
                    $emCalls->setChargedAmount($xdr->charged_amount);
                    $emCalls->setChargedQuantity($xdr->charged_quantity);
                    $emCalls->setPhoneNumber($xdr->CLD);
                    $emCalls->setCli($xdr->CLI);
                    $emCalls->setConnectTime($xdr->connect_time);

                    $country = $xdr->country;
                    $cc = new Criteria();
                    $cc->add(CountryPeer::NAME, $country, Criteria::LIKE);
                    $ccount = CountryPeer::doCount($cc);
                    if ($ccount > 0) {
                        $csel = CountryPeer::doSelectOne($cc);
                        $countryid = $csel->getId();
                    } else {
                        $cin = new Country();
                        $cin->setName($country);
                        $cin->save();
                        $countryid = $cin->getId();
                    }
                    $emCalls->setParentTable('customer');
                    $emCalls->setCountryId($countryid);
                    $ce = new Criteria();
                    $ce->add(TelintaAccountsPeer::ACCOUNT_TITLE, $xdr->account_id);
                    $ce->addAnd(TelintaAccountsPeer::PARENT_TABLE, 'customer');
                    $ce->add(TelintaAccountsPeer::STATUS, 3);
                    if (TelintaAccountsPeer::doCount($ce) > 0) {
                        $emp = TelintaAccountsPeer::doSelectOne($ce);
                        $emCalls->setParentId($emp->getParentId());
                    }

                    $emCalls->setDescription($xdr->description);
                    $emCalls->setDisconnectCause($xdr->disconnect_cause);
                    $emCalls->setDisconnectTime($xdr->disconnect_time);
                    // $emCalls->setDurationMinutes($duration_minutes);
                    $emCalls->setICustomer($customer->getICustomer());
                    $emCalls->setIXdr($xdr->i_xdr);
                    $emCalls->setStatus(3);
                    $emCalls->setSubdivision($xdr->subdivision);
                    $emCalls->setUnixConnectTime($xdr->unix_connect_time);
                    $emCalls->setUnixDisconnectTime($xdr->unix_disconnect_time);
                    $emCalls->save();
                }


                $callLogs->setStatus(3);
                $callLogs->save();
            }
        }

        return sfView::NONE;
    }

    /*
     * To remove Last Refill after 180 days. if not refilled again
     *
     */

    public function executeRemoveRefilBalance(sfWebRequest $request) {

        $date = date('Y-m-d 00:00:00', strtotime('-180 Days'));

        //       old Logic 
//        $c = new Criteria;
//        $c->addJoin(CustomerPeer::ID, CustomerOrderPeer::CUSTOMER_ID, Criteria::LEFT_JOIN);
//        $c->addJoin(CustomerOrderPeer::PRODUCT_ID, ProductPeer::ID, Criteria::LEFT_JOIN);
//        $c->addJoin(ProductPeer::PRODUCT_TYPE_ID, ProductTypePeer::ID, Criteria::LEFT_JOIN);
//        $c->addAnd(CustomerOrderPeer::CREATED_AT, $date, Criteria::LESS_THAN);
//        $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
//        $c->addAnd(ProductTypePeer::ID, 2);
//      
//        $c->addAnd(CustomerOrderPeer::ORDER_STATUS_ID, 3);
//        $c->addGroupByColumn(CustomerPeer::ID);
//        $c->addDescendingOrderByColumn(CustomerOrderPeer::CREATED_AT);
//        
        //       New Logic for geting record  by  kmmalik
        $c = new Criteria;
        $c->addJoin(CustomerPeer::ID, TransactionPeer::CUSTOMER_ID, Criteria::LEFT_JOIN);
        $c->addAnd(TransactionPeer::CREATED_AT, $date, Criteria::LESS_THAN);
        $c->addAnd(TransactionPeer::TRANSACTION_TYPE_ID, 1);
        $c->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $c->addGroupByColumn(CustomerPeer::ID);
        $customers = CustomerPeer::doSelect($c);

        foreach ($customers as $customer) {
            echo $customer->getId() . "<br/>";



            $t = new Criteria;
            $t->addAnd(TransactionPeer::CUSTOMER_ID, $customer->getId());
            $t->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
            $t->addAnd(TransactionPeer::CREATED_AT, $date, Criteria::GREATER_THAN);

            $countT = TransactionPeer::doCount($t);
            if ($countT > 0) {
                $transaction = TransactionPeer::doSelectOne($t);
                echo $transaction->getCreatedAt() . "-----" . $date . "<hr/>";
            } else {






                $telintaObj = new Telienta();
                $balance = $telintaObj->getBalance($customer);
                if ($balance > 0) {
                    $order = new CustomerOrder();
                    $order->setExtraRefill(-$balance);
                    $order->setCustomerId($customer->getId());
                    $order->setProductId(17);
                    $order->setOrderStatusId(3);
                    $order->setIsFirstOrder(10);  //// product type remove 
                    $order->save();

                    $transaction = new Transaction();
                    $transactiondescription = TransactionDescriptionPeer::retrieveByPK(17);
                    $transaction->setAmount(-$balance);
                    $transaction->setOrderId($order->getId());
                    $transaction->setTransactionStatusId(3);
                    $transaction->setCustomerId($customer->getId());
                    $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
                    $transaction->setTransactionDescriptionId($transactiondescription->getId());
                    $transaction->setDescription($transactiondescription->getTitle());
                    $transaction->save();
                    TransactionPeer::AssignReceiptNumber($transaction);
                    $telintaObj = new Telienta();
                    $telintaObj->charge($customer, $balance, $transactiondescription->getTitle());
                }
            }
        }


        return sfView::NONE;
    }

    public function executeKs(sfWebRequest $request) {

        $customer = CustomerPeer::retrieveByPK(4);

        $c = new Criteria();
        $c->addJoin(CustomerPeer::ID, CustomerProductPeer::CUSTOMER_ID, Criteria::LEFT_JOIN);
        $c->addJoin(CustomerProductPeer::PRODUCT_ID, ProductPeer::ID, Criteria::LEFT_JOIN);
        $c->addJoin(ProductPeer::BILLING_PRODUCT_ID, BillingProductsPeer::ID, Criteria::LEFT_JOIN);
        $c->addAnd(CustomerProductPeer::STATUS_ID, 3);
        $c->addAnd(CustomerPeer::ID, $customer->getId());
        $product = BillingProductsPeer::doSelectOne($c);
        echo $product->getAIproduct();

        die;
        return sfView::NONE;
    }

    public function executeCalbackChangeNumber(sfWebRequest $request) {

        $Parameters = $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);
        $email2->save();

        // call back url $p="es-297-100"; lang_orderid_amount

        $callbackparameters = $request->getParameter("p");
        $params = explode("-", $callbackparameters);

        $lang = $params[0];
        $order_id = $params[1];
        $order_amount = $params[2];

        $this->getUser()->setCulture($lang);

        $this->forward404Unless($order_id);

        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $this->forward404Unless($order);


        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);
        if ($order_amount == "")
            $order_amount = $transaction->getAmount();

        $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed
        if ($transaction->getAmount() > $order_amount) {
            //error
            $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->save();
            die;
        } else if (number_format($transaction->getAmount(), 2) < $order_amount) {
            //$extra_refill_amount = $order_amount;
            // $order->setExtraRefill($order_amount);
            $transaction->setAmount($order_amount);
        }

        $customer = $order->getCustomer();
        $old_mobile_number = $customer->getMobileNumber();

        $cn = new Criteria();
        $cn->add(ChangeNumberDetailPeer::CUSTOMER_ID, $customer->getId());
        $cn->addAnd(ChangeNumberDetailPeer::OLD_NUMBER, $old_mobile_number);
        $cn->addAnd(ChangeNumberDetailPeer::STATUS, 0);
        $change_number = ChangeNumberDetailPeer::doSelectOne($cn);
        // var_dump($change_number);
        $new_mobile = $change_number->getNewNumber();
        $countrycode = sfConfig::get("app_country_code");

        $uniqueId = $customer->getUniqueid();

        $un = new Criteria();
        $un->add(CallbackLogPeer::UNIQUEID, $uniqueId);
        $un->addDescendingOrderByColumn(CallbackLogPeer::CREATED);
        $activeNumber = CallbackLogPeer::doSelectOne($un);
//var_dump($activeNumber);
        // As each customer have a single account search the previous account and terminate it.
        $cp = new Criteria;
        $cp->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'a' . $activeNumber->getMobileNumber());
        $cp->addAnd(TelintaAccountsPeer::STATUS, 3);

        $getFirstnumberofMobile = substr($new_mobile, 0, 1);
        if ($getFirstnumberofMobile == 0) {
            $TelintaMobile = substr($new_mobile, 1);
            $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
        } else {
            $TelintaMobile = sfConfig::get('app_country_code') . $new_mobile;
        }
        $new_mobile_number = $TelintaMobile;

        if (TelintaAccountsPeer::doCount($cp) > 0) {
            $telintaAccount = TelintaAccountsPeer::doSelectOne($cp);
            $a_acount = "a" . $new_mobile_number;

            $accountInfo = array('i_account' => $telintaAccount->getIAccount(), "id" => $a_acount);
            $telintaObj = new Telienta();
            if ($telintaObj->updateAccount($accountInfo)) {
                $telintaAccount->setStatus(5);
                $telintaAccount->save();

                $ta = new TelintaAccounts();
                $ta->setParentTable("customer");
                $ta->setParentId($customer->getId());
                $ta->setIAccount($telintaAccount->getIAccount());
                $ta->setICustomer($customer->getICustomer());
                $ta->setAccountTitle($a_acount);
                $ta->setAccountType('a');
                $ta->setStatus(3);
                $ta->save();
            }
        }


        $cb = new Criteria;
        $cb->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'cb' . $activeNumber->getMobileNumber());
        $cb->addAnd(TelintaAccountsPeer::STATUS, 3);

        if (TelintaAccountsPeer::doCount($cb) > 0) {
            $telintaAccountsCB = TelintaAccountsPeer::doSelectOne($cb);
            $cb_acount = "cb" . $new_mobile_number;

            $accountInfo = array('i_account' => $telintaAccount->getIAccount(), "id" => $cb_acount);
            $telintaObj = new Telienta();
            if ($telintaObj->updateAccount($accountInfo)) {
                $telintaAccountsCB->setStatus(5);
                $telintaAccountsCB->save();

                $tcb = new TelintaAccounts();
                $tcb->setParentTable("customer");
                $tcb->setParentId($customer->getId());
                $tcb->setIAccount($telintaAccountsCB->getIAccount());
                $tcb->setICustomer($customer->getICustomer());
                $tcb->setAccountTitle($cb_acount);
                $tcb->setAccountType('cb');
                $tcb->setStatus(3);
                $tcb->save();
            }
        }

        $getvoipInfo = new Criteria();
        $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customer->getId());
        $getvoipInfo->addAnd(SeVoipNumberPeer::IS_ASSIGNED, 1);
        $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
        if (isset($getvoipInfos)) {
            $voipnumbers = $getvoipInfos->getNumber();
            $getFirstnumberofMobile = substr($voipnumbers, 0, 2);
            if ($getFirstnumberofMobile == sfConfig::get('app_country_code')) {
                $voipnumbers = substr($voipnumbers, 2);
            } else {
                $voipnumbers = $getvoipInfos->getNumber();
            }

            $tc = new Criteria();
            $tc->add(TelintaAccountsPeer::ACCOUNT_TITLE, $voipnumbers);
            $tc->add(TelintaAccountsPeer::STATUS, 3);
            if (TelintaAccountsPeer::doCount($tc) > 0) {
                $telintaAccountR = TelintaAccountsPeer::doSelectOne($tc);

                $accountInfo = array('i_account' => $telintaAccountR->getIAccount(), "id" => $voipnumbers);
                $telintaObj = new Telienta();
                if ($telintaObj->updateAccount($accountInfo)) {
                    $telintaAccountR->setStatus(5);
                    $telintaAccountR->save();

                    $tcb = new TelintaAccounts();
                    $tcb->setParentTable("customer");
                    $tcb->setParentId($customer->getId());
                    $tcb->setIAccount($telintaAccountR->getIAccount());
                    $tcb->setICustomer($customer->getICustomer());
                    $tcb->setAccountTitle($voipnumbers);
                    $tcb->setAccountType('r');
                    $tcb->setStatus(3);
                    $tcb->save();
                }
            }
        }

        $change_number->setStatus(1);
        $change_number->save();

        $customer->setMobileNumber($new_mobile);
        $customer->save();


        $order->save();
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);
        $callbacklog = new CallbackLog();
        $callbacklog->setMobileNumber($new_mobile_number);
        $callbacklog->setuniqueId($uniqueId);
        $callbacklog->setcallingCode($countrycode);
        $callbacklog->save();
        $this->setPreferredCulture($customer);
        emailLib::sendCustomerChangeNumberEmail($customer, $order);
        $this->updatePreferredCulture();
        return sfView::NONE;
    }

    public function executeChangeCustomerProduct(sfWebRequest $request) {
        if (date("d") != 1) {
            die;
        }
        $ccp = new Criteria();
        $ccp->add(CustomerChangeProductPeer::STATUS, 2);
        $ChangeCustomers = CustomerChangeProductPeer::doSelect($ccp);

        foreach ($ChangeCustomers as $changeCustomer) {


            $customer = CustomerPeer::retrieveByPK($changeCustomer->getCustomerId());
            $this->customer = $customer;
            $product = ProductPeer::retrieveByPK($changeCustomer->getProductId());
            $order = CustomerOrderPeer::retrieveByPK($changeCustomer->getOrderId());
            $transaction = TransactionPeer::retrieveByPK($changeCustomer->getTransactionId());
            $Bproducts = BillingProductsPeer::retrieveByPK($product->getBillingProductId());
            $c = new Criteria;
            $c->add(TelintaAccountsPeer::I_CUSTOMER, $customer->getICustomer());
            $c->add(TelintaAccountsPeer::STATUS, 3);
            $tilentAccount = TelintaAccountsPeer::doSelectOne($c);
            //  foreach($tilentAccounts as $tilentAccount){
            $accountInfo['i_account'] = $tilentAccount->getIAccount();
            $accountInfo['i_product'] = $Bproducts->getAIproduct();
            $telintaObj = new Telienta();
            if ($telintaObj->updateAccount($accountInfo)) {
                $changeCustomer->setStatus(3);
                $changeCustomer->Save();
            }
            //   }  

            $cp = new Criteria();
            $cp->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
            $cp->addAnd(CustomerProductPeer::STATUS_ID, 3);
            $customerProduct = CustomerProductPeer::doSelectOne($cp);
            $customerProduct->setStatusId(7);
            $customerProduct->Save();

            $cProduct = new CustomerProduct();
            $cProduct->setProductId($changeCustomer->getProductId());
            $cProduct->setCustomerId($changeCustomer->getCustomerId());
            $cProduct->setStatusId(3);
            $cProduct->save();


            $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerChangeProductConfirm($this->customer, $order, $transaction);
            $this->updatePreferredCulture();
            //  order_receipt_product_change
        }
        return sfView::NONE;
    }

    public function executeCalbacknewcard(sfWebRequest $request) {

        $Parameters = $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl($Parameters);
        $email2->save();

        // call back url $p="es-297-100"; lang_orderid_amount

        $callbackparameters = $request->getParameter("p");
        $params = explode("-", $callbackparameters);

        $lang = $params[0];
        $order_id = $params[1];
        $order_amount = $params[2];

        $this->getUser()->setCulture($lang);

        $this->forward404Unless($order_id);

        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $this->forward404Unless($order);

        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);
        if ($order_amount == "")
            $order_amount = $transaction->getAmount();

        $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed
        if ($transaction->getAmount() > $order_amount) {
            //error
            $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->save();
            die;
        } else if (number_format($transaction->getAmount(), 2) < $order_amount) {
            $transaction->setAmount($order_amount);
        }
        //set active agent_package in case customer was registerred by an affiliate
        /* if ($order->getCustomer()->getAgentCompany()) {
          $order->setAgentCommissionPackageId($order->getCustomer()->getAgentCompany()->getAgentCommissionPackageId());
          } */
        $order->save();
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);
        $this->customer = $order->getCustomer();
        /* echo "ag" . $agentid = $this->customer->getReferrerId();
          echo "prid" . $productid = $order->getProductId();
          echo "trid" . $transactionid = $transaction->getId();
          if (isset($agentid) && $agentid != "") {
          echo "getagentid";
          commissionLib::refilCustomer($agentid, $productid, $transactionid);
          $transaction->setAgentCompanyId($agentid);
          $transaction->save();
          } */
        $cst = new Criteria();
        $cst->add(SimTypesPeer::ID, $order->getProduct()->getSimTypeId());
        $simtype = SimTypesPeer::doSelectOne($cst);
        echo $sim_type_id = $simtype->getId();
        $exest = $order->getExeStatus();
        if ($exest != 1) {

            $uniqueId = $this->customer->getUniqueid();
            $cb = new Criteria();
            $cb->add(CallbackLogPeer::UNIQUEID, $uniqueId);
            $cb->addDescendingOrderByColumn(CallbackLogPeer::CREATED);
            $activeNumber = CallbackLogPeer::doSelectOne($cb);

            $uc = new Criteria();
            $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 1);
            $uc->addAnd(UniqueIdsPeer::STATUS, 0);
            $uc->addAnd(UniqueIdsPeer::SIM_TYPE_ID, $sim_type_id);
            $availableUniqueCount = UniqueIdsPeer::doCount($uc);
            $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);

            if ($availableUniqueCount == 0) {
                // Unique Ids are not avaialable. Then Redirect to the sorry page and send email to the support.
                emailLib::sendUniqueIdsShortage($sim_type_id);
                exit;
                //$this->redirect($this->getTargetUrl().'customer/shortUniqueIds');
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

            $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerNewcardEmail($this->customer, $order, $transaction);
            $this->updatePreferredCulture();
        }

        $order->setExeStatus(1);
        $order->save();
        echo 'Yes';
        return sfView::NONE;
    }

    public function executeCalbackChangeProduct(sfWebRequest $request) {

        $Parameters = $request->getURI();

        $email2 = new DibsCall();
        $email2->setCallurl("Received:--" . $Parameters);
        $email2->save();

        // call back url $p="es-297-100"; lang_orderid_amount

        $callbackparameters = $request->getParameter("p");
        $params = explode("-", $callbackparameters);

        $lang = $params[0];
        $order_id = $params[1];
        $order_amount = $params[2];
        $ccpid = $params[3];

        $this->getUser()->setCulture($lang);

        $this->forward404Unless($order_id);
        $CCP = CustomerChangeProductPeer::retrieveByPK($ccpid);
        $CCP->setStatus(2);
        $CCP->save();

        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $this->forward404Unless($order);

        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);
        if ($order_amount == "")
            $order_amount = $transaction->getAmount();

        $order->setOrderStatusId(sfConfig::get('app_status_completed', 3)); //completed
        $transaction->setTransactionStatusId(sfConfig::get('app_status_completed', 3)); //completed
        if ($transaction->getAmount() > $order_amount) {
            //error
            $order->setOrderStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->setTransactionStatusId(sfConfig::get('app_status_error', 5)); //error in amount
            $transaction->save();
            die;
        } else if (number_format($transaction->getAmount(), 2) < $order_amount) {
            $transaction->setAmount($order_amount);
        }

        $order->save();
        $transaction->save();
        TransactionPeer::AssignReceiptNumber($transaction);

        $this->customer = $order->getCustomer();
        $exest = $order->getExeStatus();
        $uniqueId = $this->customer->getUniqueid();

        $this->setPreferredCulture($this->customer);
        emailLib::sendCustomerChangeProduct($this->customer, $order, $transaction);
        $this->updatePreferredCulture();

        $order->setExeStatus(1);
        $order->save();
        echo 'Yes';

        /*         * ************Change customer product ***************** */
        $customer = $this->customer;
        $product = ProductPeer::retrieveByPK($CCP->getProductId());
        $order = CustomerOrderPeer::retrieveByPK($CCP->getOrderId());
        $transaction = TransactionPeer::retrieveByPK($CCP->getTransactionId());
        $Bproducts = BillingProductsPeer::retrieveByPK($product->getBillingProductId());
        $c = new Criteria;
        $c->add(TelintaAccountsPeer::I_CUSTOMER, $customer->getICustomer());
        $c->add(TelintaAccountsPeer::STATUS, 3);
        $tilentAccount = TelintaAccountsPeer::doSelectOne($c);
        //  foreach($tilentAccounts as $tilentAccount){
        $accountInfo['i_account'] = $tilentAccount->getIAccount();
        $accountInfo['i_product'] = $Bproducts->getAIproduct();
        $telintaObj = new Telienta();
        if ($telintaObj->updateAccount($accountInfo)) {
            $CCP->setStatus(3);
            $CCP->Save();
        }
        //   }  

        $cp = new Criteria();
        $cp->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
        $cp->addAnd(CustomerProductPeer::STATUS_ID, 3);
        $customerProduct = CustomerProductPeer::doSelectOne($cp);
        $customerProduct->setStatusId(7);
        $customerProduct->Save();

        $cProduct = new CustomerProduct();
        $cProduct->setProductId($CCP->getProductId());
        $cProduct->setCustomerId($CCP->getCustomerId());
        $cProduct->setStatusId(3);
        $cProduct->save();


        $this->setPreferredCulture($this->customer);
        emailLib::sendCustomerChangeProductConfirm($this->customer, $order, $transaction);
        $this->updatePreferredCulture();


        return sfView::NONE;
    }

    private function hextostr($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex) - 1; $i+=2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $str;
    }

    public function executeGenrateTestString(sfWebRequest $request) {

        if ($request->isMethod('post')) {
            if ($request->getParameter("hex") == "on") {
                echo $this->hexToStr($request->getParameter("inputstr"));
            } else {
                echo $this->strToHex($request->getParameter("inputstr"));
            }
        }
    }

    private function randomNumbers($length) {
        $random = "";
        srand((double) microtime() * 1000000);
        $data = "0123456789";
        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }
        return $random;
    }

    private function strToHex($string) {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    public function executeEmailTestCenter(sfWebRequest $request) {



        $inviterCustomer = CustomerPeer::retrieveByPK(38);
        $this->setPreferredCulture($inviterCustomer);
        $inviteOrder = CustomerOrderPeer::retrieveByPK(304);
        $transaction_i = TransactionPeer::retrieveByPK(296);
        $this->customer = CustomerPeer::retrieveByPK(34);


        emailLib::sendCustomerConfirmRegistrationEmail($inviterCustomer->getId(), $this->customer, null, $inviteOrder, $transaction_i);
        $this->updatePreferredCulture();
        return sfView::NONE;
    }

    public function executeCardNumber(sfWebRequest $request) {


        function random($len) {


            $return = '';
            for ($i = 0; $i < $len; ++$i) {
                if (!isset($urandom)) {
                    if ($i % 2 == 0)
                        mt_srand(time() % 2147 * 1000000 + (double) microtime() * 1000000);
                    $rand = 48 + mt_rand() % 64;
                }
                else
                    $rand = 48 + ord($urandom[$i]) % 64;

                if ($rand > 57)
                    $rand+=7;
                if ($rand > 90)
                    $rand+=6;
                if ($rand > 80)
                    $rand-=5;


                if ($rand == 123)
                    $rand = 45;
                if ($rand == 124)
                    $rand = 46;
                $return.=$rand;
            }
            return $return;
        }

        $cardcount = 0;
        $serial = 100000;
        $i = 1;
        while ($i <= 20000) {


            $val = random(20);

            $randLength = strlen($val);

            if ($randLength > 11) {
                $resultvalue = (int) $randLength - 11;

                $rtvalue = mt_rand(1, $resultvalue);

                $resultvalue = substr($val, $rtvalue, 11);

                $cardnumber = "02149" . $resultvalue;
            }

            $CRcardcount = 0;
            $cq = new Criteria();
            $cq->add(CardNumbersPeer::CARD_NUMBER, $cardnumber);
            $CRcardcount = CardNumbersPeer::doCount($cq);

            if ($CRcardcount == 1) {
                
            } else {

                $cardTotalcount = 0;
                $ct = new Criteria();
                $cardTotalcount = CardNumbersPeer::doCount($ct);
                if ($cardTotalcount < 4000) {
                    $cardcount = 0;

                    $c = new Criteria();
                    $c->add(CardNumbersPeer::CARD_PRICE, 100);
                    $cardcount = CardNumbersPeer::doCount($c);
                    if ($cardcount < 2000) {

                        $price = 100;
                        $cr = new CardNumbers();
                        $cr->setCardNumber($cardnumber);
                        $cr->setCardPrice($price);
                        $cr->setCardSerial($serial);
                        $cr->save();
                        $serial++;
                    }
                } else {
                    $i = 2000;
                }
            }
            $i++;
        }


        return sfView::NONE;
    }

    public function executeAppGetBalance(sfWebRequest $request) {

        $mobile_number = $request->getParameter('mobile_number');
        $mobile_number = $this->mobileNumberWithoutCountryCode($mobile_number);
        $c = new Criteria();
        $c->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customer = CustomerPeer::doSelectOne($c);
        if ($customer) {
            $telintaObj = new Telienta();
            echo number_format($telintaObj->getBalance($customer), 2);
        } else {
            echo "0.00";
        }
        return sfView::NONE;
    }

    public function executeAppLogin(sfWebrequest $request) {

        $urlval = "App LoginURL - " . $request->getURI();
        $cmobile_number = $request->getParameter('mobile_number');
        $mobile_number = $this->mobileNumberWithoutCountryCode($cmobile_number);
        $password = sha1($request->getParameter('pwd'));
        $app = $request->getParameter('app');
        $c = $password . "_" . $request->getParameter('pwd');
        ///////////////////////login parameter////////////////// 

        $applog = new AppLoginLogs();
        $applog->setMobileNumber($mobile_number);
        $applog->setPwd($c);
        $applog->setStatusId(1);
        $applog->setUrl($urlval);
        $applog->setApplicationId($app);
        $applog->save();

        $c = new Criteria();
        $c->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $c->addAnd(CustomerPeer::PASSWORD, $password);
        $c->addAnd(CustomerPeer::I_CUSTOMER, null, Criteria::ISNOTNULL);
        $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        //  $c->add(CustomerPeer::BLOCK,0);
        $customer = CustomerPeer::doSelectOne($c);
        //  var_dump($customer);
        if ($customer) {
            $uid = $customer->getUniqueid();
            echo $reponseVar = "OK;Port=6000;VoipIP=208.89.105.21;uid=1393238;Username=$mobile_number;";
            //    echo "OK;Port=6000;VoipIP=208.89.105.21;uid=$uid;isoCode=$isocode;Username=$mbnumber;Password=".$tilintapassword.";name=".$customer->getFirstName().";mobile_number=".$customer->getMobileNumber().";email=".$customer->getEmail();
            $applog->setStatusId(3);
            $applog->setCustomerId($customer->getId());
            $applog->setResponse($reponseVar);
            $applog->save();
        } else {
            echo "failure, invalid phone number";
        }
        return sfView::NONE;
    }

    public function executeAppPasswordRecovery(sfWebrequest $request) {

        $c_mobile_number = $request->getParameter('mobile_number');
        $mobile_number = $this->mobileNumberWithoutCountryCode($c_mobile_number);
        $c = new Criteria();
        $c->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        //echo $c->toString(); exit;
        $customer = CustomerPeer::doSelectOne($c);

        if ($customer) {
            $chars = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";

            $new_password = substr(str_shuffle($chars), 0, 6);
            $customer->setPassword($new_password);
            $customer->setPlainText($new_password);
            $customer->save();
            $this->setPreferredCulture($customer);
            $message_body = $this->getContext()->getI18N()->__('Dear customer'); //. ' ' . $customer->getFirstName() . '&nbsp;' . $customer->getLastName() . '!';
            $message_body .= '<br /><br />';

            $message_body .= $this->getContext()->getI18N()->__('Your password has been changed. Please use the following information to enter MY ACCOUNT.', array('%1%' => sfConfig::get('app_site_title')));

            $message_body .= '<br /><br />';
            $message_body .= sprintf($this->getContext()->getI18N()->__('Mobile number: %s'), $customer->getMobileNumber());
            $message_body .= '<br />';
            $message_body .= $this->getContext()->getI18N()->__('Password') . ': ' . $new_password;

            $subject = $this->getContext()->getI18N()->__('Password Request');
            emailLib::sendForgetPasswordEmail($customer, $message_body, $subject);
//
//            //Send Email to User --- when Forget Password Request Come --- 01/15/11
//            emailLib::sendForgetPasswordEmail($customer, $message_body);

            $sms_text = $this->getContext()->getI18N()->__("New password") . ": " . $new_password;

            ROUTED_SMS::send($c_mobile_number, $sms_text);
            echo 'OK,Login Information successfully sent to your email.';
        } else {
            echo 'error, mobile number does not exists';
        }

        return sfView::NONE;
    }

    public function executeAppWebSMS(sfWebrequest $request) {

        $sender = $request->getParameter('sender');
        $message = $request->getParameter('message');
        $number = $request->getParameter('number');
        $destination = $number;

        $mobile = $this->mobileNumberWithoutCountryCode($sender);
        $cr = new Criteria();
        $cr->add(CustomerPeer::MOBILE_NUMBER, $mobile);
        $cr->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customer = CustomerPeer::doSelectOne($cr);
        if (!$customer) {
            echo 'error, Mobile Number Not Registered';
            return sfView::NONE;
        }

        $did = 0;
        ////////////////////Code Review by barankhan.com //////////////////////////
        $call_charge = 0.00;
        $destinations = 10;
        $count = 1;
        $result = 0;
        $reversearecode = 0;
        $return_result = NULL;
        $sub_result = NULL;
        //
        $number = str_replace(' ', '', str_replace('+', '', str_replace(')', '', str_replace('(', '', $number))));
        while ($destinations > 0) {
            $nmbr = substr($number, 0, 2);
            if ($nmbr == '00') {
                $number = substr($number, 2);
            } else {
                $number = $number;
            }

            $area_code = substr($number, 0, $count);

            $cct = new Criteria();
            $cct->addOr(CountryPeer::CALLING_CODE, $area_code . '%', Criteria::LIKE);
            $countrycode = CountryPeer::doCount($cct);
            $destinations = $countrycode;

            if ($destinations >= 1) {


                $cctf = new Criteria();
                $cctf->addOr(CountryPeer::CALLING_CODE, $area_code . '%', Criteria::LIKE);
                $countrycodef = CountryPeer::doSelectOne($cctf);
                $reversearecode = 0;
                $reversearecode = $area_code;
                $did = $countrycodef->getId();
            }
            $count++;
        }

        $resverselength = strlen($reversearecode);
        $cct = new Criteria();
        $cct->add(CountryPeer::CALLING_CODE, $reversearecode);
        $countrycode = CountryPeer::doCount($cct);
        $destinationsr = $countrycode;
        $reversedir = 0;
        while ($destinationsr == 0) {
            $reversearecodes = substr($reversearecode, 0, $resverselength);
            $cct = new Criteria();
            $cct->add(CountryPeer::CALLING_CODE, $reversearecodes);
            $countrycode = CountryPeer::doCount($cct);
            $destinationsr = $countrycode;
            if ($destinationsr >= 1) {
                $cct->add(CountryPeer::CALLING_CODE, $reversearecodes);
                $countrycodea = CountryPeer::doCount($cct);
                $did = $countrycodea->getId;
            }
            $resverselength--;
            $reversedir++;
            if ($reversedir == 4) {
                $destinationsr = 1;
            }
        }
        //////////////////////////////////////
        // echo "khan".$did;
        if (isset($did) && $did > 0) {
            
        } else {
            $did = 183;
        }
        $cct = new Criteria();
        $cct->add(CountryPeer::ID, $did);
        $country = CountryPeer::doSelectOne($cct);
        if (!$country) {
            echo 'error, Country code not recgnized';
            return sfView::NONE;
        }
        if ($customer) {
            if ($sender and $message and $number) {
                $messages = array();

                if (strlen($message) < 144) {
                    $messages[1] = $message . $this->getContext()->getI18N()->__("-Sent by") . " Kimarin-";
                } else if (strlen($message) > 144 and strlen($message) < 302) {
                    $messages[1] = substr($message, 1, 144) . $this->getContext()->getI18N()->__("-Sent by") . " Kimarin-";
                    $messages[2] = substr($message, 145) . $this->getContext()->getI18N()->__("-Sent by") . " Kimarin-";
                } else if (strlen($message) > 382) {
                    $messages[1] = substr($message, 1, 144) . $this->getContext()->getI18N()->__("-Sent by") . " Kimarin-";
                    $messages[2] = substr($message, 145, 302) . $this->getContext()->getI18N()->__("-Sent by") . " Kimarin-";
                    $messages[3] = substr($message, 303, 432) . $this->getContext()->getI18N()->__("-Sent by") . " Kimarin-";
                }
                // $cc = CurrencyConversionPeer::retrieveByPK(1);
                foreach ($messages as $sms_text) {
                    $cbf = new Cbf();
                    $cbf->setS('H');
                    $cbf->setDa($destination);
                    $cbf->setMessage($sms_text);
                    $cbf->setCountryId($country->getId());

                    $cbf->setMobileNumber($customer->getMobileNumber());

                    //get balance
                    $telintaObj = new Telienta();
                    $balance = $telintaObj->getBalance($customer);
                    $amt = number_format($country->getCbfRate(), 2);
                    if ($balance < $amt) {
                        echo "error, Not Enough Balance, Please Recharge";
                        return sfView::NONE;
                    } else {
                        
                    }
                    $res = ROUTED_SMS::Send($request->getParameter('number'), $sms_text, $customer->getMobileNumber());
                    if ($res) {
                        $telintaObj = new Telienta();
                        $description = "SMS Charges";
                        $telintaObj->charge($customer, $amt, $description);
                    }
                }

                if ($res) {
                    echo "Message successfully sent.";
                } else {
                    echo "sms could not be sent";
                }
            } else {
                echo "sms could not be sent";
            }

            return sfView::NONE;
        }
        echo "sms could not be sent";

        return sfView::NONE;
    }

    public function executeAppRegistration(sfWebrequest $request) {


        //get request parameters
        $name = $request->getParameter('name');
        $password = $request->getParameter('pwd');
        $email = $request->getParameter('email');
        $ccode = $request->getParameter('ccode');
        $mobilenumber = $request->getParameter('mobile_number');

        $full_mobile_number = $ccode . $mobilenumber;
        $code = $request->getParameter('code');
        $app = $request->getParameter('app');
        ///////////////////////registration parameter//////////////////
        $urlval = "App Registration URL - " . $request->getURI();
        $applog = new AppRegistrationLogs();
        $applog->setMobileNumber($full_mobile_number);
        $applog->setPwd($password);
        $applog->setEmail($email);
        $applog->setCcode($ccode);
        $applog->setCode($code);
        $applog->setStatusId(1);
        $applog->setUrl($urlval);
        $applog->setApplicationId($app);
        $applog->save();




///////////////////zeroCall app product Registration
        $mnc = new Criteria();
        $mnc->add(CustomerPeer::MOBILE_NUMBER, $mobilenumber);
        $mnc->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customerCount = CustomerPeer::doCount($mnc);
        if ($customerCount > 0) {
            echo 'Customer already exists.';
            die;
        }

        $ccc = new Criteria();
        $ccc->add(CountryPeer::CALLING_CODE, $ccode);
        $CountryCount = CountryPeer::doCount($ccc);
        if ($CountryCount > 0) {
            $country = CountryPeer::doSelectOne($ccc);
        } else {
            echo 'Country does not exists.';
            die;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            echo " The email address is not valid";
            die;
        }

        $product = ProductPeer::retrieveByPK(18);
        $customer = new Customer();
        $customer->setCountryId($country->getId());
        $customer->setFirstName($name);
        $customer->setMobileNumber($mobilenumber);
        $customer->setPassword($password);
        $customer->setEmail($email);
        $customer->setRegistrationTypeId(5);
        $customer->setPlainText($password);
        $customer->setPassword($password);
        $customer->setPreferredLanguageId(3);
        $customer->setCustomerStatusId(1);
        $customer->save();

        $customer->setUniqueid("app" . $customer->getId());
        $customer->save();


        $agentid = $customer->getReferrerId();
        if ($agentid) {
            $commision = TRUE;
            $agentCompanyId = $agentid;
        } else {
            $commision = FALSE;
            $agentCompanyId = FALSE;
        }
        // TransactionProcess::StartTransaction($customer, $productId, $decriptionid, $expenceType, $transactionFrom, $transactionStatus, $commision, $agentCompanyId);
        $order = new CustomerOrder();
        $order->setProductId($product->getId());
        $order->setCustomerId($customer->getId());
        $order->setExtraRefill($order->getProduct()->getInitialBalance() + $order->getProduct()->getBonus());
        $order->setIsFirstOrder(1);
        $order->setOrderStatusId(1);
        $order->save();

        $transaction = new Transaction();
        $transaction->setAmount($order->getProduct()->getPrice() + $order->getProduct()->getRegistrationFee() + (($order->getProduct()->getRegistrationFee()) * sfConfig::get('app_vat_percentage')));
        $transactiondescription = TransactionDescriptionPeer::retrieveByPK(8);
        $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
        $transaction->setTransactionDescriptionId($transactiondescription->getId());
        $transaction->setDescription($transactiondescription->getTitle());
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($customer->getId());
        $transaction->setTransactionStatusId(1); // default value 1
        $transaction->setVat((($order->getProduct()->getRegistrationFee()) * sfConfig::get('app_vat_percentage')));
        $transaction->save();


        TransactionPeer::AssignReceiptNumber($transaction);


        // echo 'Assigning Customer ID <br/>';
        //set customer's proudcts in use
        $customer_product = new CustomerProduct();
        $customer_product->setCustomerId($transaction->getCustomerId());
        $customer_product->setProductId($order->getProductId());
        $customer_product->save();

        $this->customer = $customer;
        $TelintaMobile = $full_mobile_number;
        $OpeningBalance = $order->getExtraRefill();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $telintaObj = new Telienta();
        if ($telintaObj->ResgiterCustomer($this->customer, $OpeningBalance)) {
            $transaction->setTransactionStatusId(3); // default value 1
            $transaction->save();
            $order->setOrderStatusId(3);
            $order->save();
            $customer->setCustomerStatusId(3);
            $customer->save();
            TransactionPeer::AssignReceiptNumber($transaction);
            // For Telinta Add Account

            $telintaObj->createAAccount($TelintaMobile, $this->customer);
            $telintaObj->createCBAccount($TelintaMobile, $this->customer);
///////////////////////////////////////////////////////////////////////////////////////////////////////
            $this->setPreferredCulture($this->customer);
            emailLib::sendCustomerRegistrationViaAPPEmail($transaction, "payments");
            $this->updatePreferredCulture();


            echo "OK,customer registered successfully";
            $callbacklog = new CallbackLog();
            $callbacklog->setMobileNumber($TelintaMobile);
            $callbacklog->setuniqueId($customer->getUniqueid());
            $callbacklog->setCallingcode(sfConfig::get("app_country_code"));
            $callbacklog->setCheckStatus(3);
            $callbacklog->save();
//            $zerocall_sms = new ZeroCallOutSMS();
//            $zerocall_sms->toCustomerAfterAppReg($customer);
//            if ($applog->getApplicationId() == 1) {
//
//                $zerocall_sms = new ZeroCallOutSMS();
//                $zerocall_sms->SmsAppIphoneRefill($customer->getMobileNumber());
//            }
            $applog->setStatusId(3);
            $applog->setCustomerId($customer->getId());
            $applog->setResponse('customer registered successfully');
            $applog->save();
        }

        return sfView::NONE;
    }

    private function mobileNumberWithoutCountryCode($mobile_number) {

        $prefix04 = substr($mobile_number, 0, 4);
        $prefix03 = substr($mobile_number, 0, 3);
        $prefix02 = substr($mobile_number, 0, 2);

        if ($prefix04 == '0034') {
            $mobile_number = substr($mobile_number, 4);
        } elseif ($prefix03 == "+34") {
            $mobile_number = substr($mobile_number, 3);
        } elseif ($prefix02 == "34") {
            $mobile_number = substr($mobile_number, 2);
        }

        return $mobile_number;
    }

    public function executeAppRefill(sfWebRequest $request) {
        $this->target = sfConfig::get('app_customer_url');
        $cmobile_number = $request->getParameter('mobile_number');
        $mobile_number = $this->mobileNumberWithoutCountryCode($cmobile_number);
        $this->customer = NULL;
        $c = new Criteria();
        $c->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
        $customer = CustomerPeer::doSelectOne($c);
        if ($customer != NULL) {

            $customer_id = $customer->getId();

            $this->customer = $customer;
            $this->redirectUnless($this->customer, "@homepage");
            $this->form = new ManualRefillForm($customer_id);
            $c = new Criteria();
            $c->add(ProductPeer::PRODUCT_TYPE_ID, 2);
            $this->refillProducts = ProductPeer::doSelect($c);
        } else {
            echo 'error, customer not found';
            return sfView::NONE;
        }

        $this->setLayout('mobile');
    }

    public function executeAppRefilTransaction(sfWebRequest $request) {
        $this->target = sfConfig::get('app_customer_url');

        $product = ProductPeer::retrieveByPK($request->getParameter('extra_refill'));
        $request->getParameter('extra_refill');

        $this->customer = CustomerPeer::retrieveByPK($request->getParameter('customer_id'));
        $customer = $this->customer;
        $this->redirectUnless($this->customer, "@homepage");

        $lang = $this->getUser()->getCulture();

        $agentid = $customer->getReferrerId();
        $mobileNumber = $customer->getMobileNumber();
        if ($agentid) {
            $commision = TRUE;
            $agentCompanyId = $agentid;
        } else {
            $commision = FALSE;
            $agentCompanyId = FALSE;
        }
        //  TransactionProcess::StartTransaction($customer, $productId, $decriptionid, $expenceType, $transactionFrom, $transactionStatus, $commision, $agentCompanyId);
        //$transaction = TransactionProcess::StartTransaction($this->customer, $product->getId(), 9, 1, 5, 1, $commision, $agentCompanyId);
        $this->order = new CustomerOrder();
        $this->order->setProduct($product);
        $this->order->setCustomer($this->customer);
        $this->order->setQuantity(1);
        $this->order->setExtraRefill($product->getInitialBalance() + $product->getBonus());
        $this->order->setIsFirstOrder(2);
        $this->order->save();

        $transaction = new Transaction();

        $transaction->setAmount($this->order->getExtraRefill() * (sfConfig::get('app_vat_percentage') + 1));
        $transactiondescription = TransactionDescriptionPeer::retrieveByPK(9);
        $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
        $transaction->setTransactionDescriptionId($transactiondescription->getId());
        $transaction->setDescription($transactiondescription->getTitle());
        $transaction->setOrderId($this->order->getId());
        $transaction->setCustomerId($this->order->getCustomerId());
        $transaction->setVat($this->order->getExtraRefill() * sfConfig::get('app_vat_percentage'));

        //save
        $transaction->save();
        $this->transaction = $transaction;
        $order_id = $this->order->getId();
        $item_amount = $transaction->getAmount();

        $return_url = $this->target . "pScripts/appRefillThanks";
        $cancel_url = $this->target . "pScripts/appRefill?mobile_number=" . $mobileNumber;



        $callbackparameters = $lang . '-' . $order_id . '-' . $item_amount;
        $notify_url = $this->target . 'pScripts/calbackrefill?p=' . $callbackparameters;

        $email2 = new DibsCall();
        $email2->setCallurl($notify_url);

        $email2->save();

        $querystring = '';
        $_POST["amount"] = number_format($item_amount, 2);
        if (!isset($_POST["txn_id"]) && !isset($_POST["txn_type"])) {

            $order = CustomerOrderPeer::retrieveByPK($order_id);
            $item_name = "Refill";

            //loop for posted values and append to querystring
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $querystring .= "$key=$value&";
            }

            $querystring .= "item_name=" . urlencode($item_name) . "&";
            $querystring .= "return=" . urldecode($return_url) . "&";
            $querystring .= "cancel_return=" . urldecode($cancel_url) . "&";
            $querystring .= "notify_url=" . urldecode($notify_url);

            $this->queryString = $querystring;
            $this->customer = $order->getCustomer();
            $this->order = $order;
            $telintaObj = new Telienta();
            $this->customerBalance = $telintaObj->getBalance($this->customer);
            $this->product = $product;
        }
        $this->setLayout('mobile');
    }

    public function executeAppRefilToPaypal(sfWebRequest $request) {
        $querystring = $request->getParameter('qstr');
        Payment::SendPayment($querystring);
        return sfView::NONE;
    }

    public function executeAppRefillThanks(sfWebRequest $request) {
        $this->setLayout('mobile');
    }

    public function executeAppTermsConditions(sfWebRequest $request) {
        $this->setLayout(false);
    }

}
