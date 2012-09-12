<?php

require_once(sfConfig::get('sf_lib_dir') . '/emailLib.php');
require_once(sfConfig::get('sf_lib_dir') . '/smsCharacterReplacement.php');
require_once(sfConfig::get('sf_lib_dir') . '/changeLanguageCulture.php');
require_once(sfConfig::get('sf_lib_dir') . '/parsecsv.lib.php');
require_once(sfConfig::get('sf_lib_dir') . '/telinta.class.php');
require_once(sfConfig::get('sf_lib_dir') . '/payment.class.php');
require_once(sfConfig::get('sf_lib_dir') . '/zerocall_out_sms.php');

/**
 * customer actions.
 *
 * @package    zapnacrm
 * @subpackage customer
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php,v 1.8 2010-09-19 22:20:12 orehman Exp $
 */
class customerActions extends sfActions {

    private $currentCulture;

    public function getTargetUrl() {
        return sfConfig::get('app_customer_url');
    }

    public function executeTest(sfWebRequest $request) {

    }

    protected function processForm(sfWebRequest $request, sfForm $form, $id) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11


        $customer = $request->getParameter($form->getName());
        $product = $customer['product'];
        $plainPws = $customer["password"];
        $refVal = $customer["referrer_id"];


        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

        if ($form->isValid()) {
//            var_dump($customer);
//            die;
            $customer = $form->save();
            $customer->setPlainText($plainPws);
            if (isset($refVal) && $refVal != '') {

                $customer->setRegistrationTypeId('3');
            } else {
                $customer->setRegistrationTypeId('1');
            }

            $mobile = "";
            $mobile = $customer->getMobileNumber();
            //$form->getMobileNumber();
            //$customer["mobile_number"];

            $sms_text = "";
            $number = $customer->getMobileNumber();
            $mtnumber = $customer->getMobileNumber();

            $numberlength = strlen($mobile);
            $endnumberlength = $numberlength - 2;
            $number = substr($number, 2, $endnumberlength);
            //$uniqueId  = $text;


            $uc = new Criteria();
            $uc->add(UniqueIdsPeer::REGISTRATION_TYPE_ID, 1);
            $uc->add(UniqueIdsPeer::SIM_TYPE_ID, $customer->getSimTypeId());
            $uc->addAnd(UniqueIdsPeer::STATUS, 0);
            $availableUniqueCount = UniqueIdsPeer::doCount($uc);
            $availableUniqueId = UniqueIdsPeer::doSelectOne($uc);

            if ($availableUniqueCount == 0) {
                echo $customer->getSimTypeId();
                // Unique Ids are not avaialable. Then Redirect to the sorry page and send email to the support.
                emailLib::sendUniqueIdsShortage();
                $this->redirect($this->getTargetUrl() . 'customer/shortUniqueIds');
            }
            $uniqueId = $availableUniqueId->getUniqueNumber();
            $customer->setUniqueid($uniqueId);
            $customer->save();
            $getFirstnumberofMobile = substr($mtnumber, 0, 1);     // bcdef
            if ($getFirstnumberofMobile == 0) {
                $TelintaMobile = substr($mtnumber, 1);
                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
            } else {
                $TelintaMobile = sfConfig::get('app_country_code') . $mtnumber;
            }
            //------save the callback data
            if ($id != NULL) {
                $invite = InvitePeer::retrieveByPK($id);
                if ($invite) {
                    $invite->setInviteNumber($customer->getMobileNumber());
                    $invite->save();
                }
            }
            $url = $this->getTargetUrl();
            $this->redirect($url . 'payments/signup?cid=' . $customer->getId() . '&pid=' . $product);
        }
    }

    public function executeSignupStep2(sfWebRequest $request) {



        $callbackdibs = '';
        if ($request->getParameter('transact')) {
            $this->callbackdibs = 'yes';
        } else {
            //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 01/24/11




            $this->form = new PaymentForm();
            $this->callbackdibs = 'Yes';
            $product_id = $request->getParameter('pid');
            $customer_id = $request->getParameter('cid');

            $this->getUser()->setAttribute('product_ids', $product_id);
            $this->getUser()->setAttribute('cusid', $customer_id);

            if ($product_id == '' || $customer_id == '') {
                $this->forward404('Product id not found in session');
            }

            $order = new CustomerOrder();
            $transaction = new Transaction();

            $order->setProductId($product_id);
            $order->setCustomerId($customer_id);
            $order->setExtraRefill($order->getProduct()->getInitialBalance());

            //$extra_refil_choices = ProductPeer::getRefillChoices();
            //TODO: restrict quantity to be 1
            $order->setQuantity(1);

            //$order->setExtraRefill($extra_refil_choices[0]);//minumum refill amount
            $order->setIsFirstOrder(1);

            $order->save();

            $transaction->setAmount($order->getProduct()->getPrice() - $order->getProduct()->getInitialBalance() + $order->getExtraRefill());
            //TODO: $transaction->setAmount($order->getProduct()->getPrice());
            // $this->getContext()->getI18N()->__('Registration and first product order')
            $transaction->setDescription('Registration');
            $transaction->setOrderId($order->getId());
            $transaction->setCustomerId($customer_id);
            //$transaction->setTransactionStatusId() // default value 1

            $transaction->save();

            $this->order = $order;
            $this->forward404Unless($this->order);

            $this->order_id = $order->getId();
            $this->amount = $transaction->getAmount();
        }
    }

    public function executeSignup(sfWebRequest $request) {


        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        if($request->getParameter('lang') != ''){
            $this->getUser()->setCulture($request->getParameter('lang'));
            $this->sLang = $request->getParameter('lang');
        }else{
            $this->sLang = 'en';
        }


        $this->form = new CustomerFormB2C();
        $id = $request->getParameter('invite_id');
        $visitor_id = $request->getParameter('visitor');
        //$this->form->widgetSchema->setLabel('the_field_id', false);
        if ($visitor_id != NULL) {
            $c = new Criteria();
            $c->add(VisitorsPeer::ID, $request->getParameter('visitor'));
            $visitor = VisitorsPeer::doSelectOne($c);
            $status = $visitor->getStatus();
            $visitor->setStatus($status . "> B2C Signup Page ");
            $visitor->save();
        }

        if ($id != NULL) {
            $c = new Criteria();
            //$c->add(InvitePeer::ID,$id);
            //$c->add(InvitePeer::INVITE_STATUS,'2');
            $invite = InvitePeer::retrieveByPK($id);
            if ($invite) {
                $invite->setInviteStatus('2');
                $invite->save();
            }
        }

        //set referrer id
        if ($referrer_id = $request->getParameter('ref')) {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);

            if (AgentCompanyPeer::doSelectOne($c))
                $this->form->setDefault('referrer_id', $referrer_id);
        }


        unset($this->form['manufacturer']);
        unset($this->form['device_id']);


        if ($request->isMethod('post')) {

            unset($this->form['imsi']);
            unset($this->form['uniqueid']);

            $this->processForm($request, $this->form, $id);
        }
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

    public function executeChangePassword(sfWebRequest $request) {
        //echo 'Request to support@landncall.com';
        //return sfView::NONE;
    }

    public function executeDashboard(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //echo "--- ".sfPropel::getDefaultCulture();
        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));
        $customer = $this->customer;
        $this->redirectUnless($this->customer, "@homepage");

        $this->customer_balance = -1;

        $country_id = $this->customer->getCountryId();
        $this->targe = $this->getTargetUrl();
        //This Section For Get the Language Symbol For Set Currency -
        $enableCountry = new Criteria();
        $enableCountry->add(EnableCountryPeer::ID, $country_id);
        $country_id = EnableCountryPeer::doSelectOne($enableCountry); //->getId();
        if ($country_id) {
            $langSym = $country_id->getLanguageSymbol();
        } else {
            $langSym = sfConfig::get('app_language_symbol');
        }
        //--------------------------------------------------------
        //$lang =  $this->getUser()->getAttribute('activelanguage');
        $lang = $langSym;
        $this->lang = $lang;

        //try to get balance again & again
        $num_tries = 3;

        for ($i = 0; ($i < 3) && $this->customer_balance == -1; $i++) {
            $this->customer_balance = (double) Fonet::getBalance($this->customer);
        }
        $cp = new Criteria();
        $cp->add(CustomerProductPeer::CUSTOMER_ID, $this->customer->getId());
        $cp->addAnd(CustomerProductPeer::STATUS_ID, 3);
        $this->customerProduct = CustomerProductPeer::doSelectOne($cp);
        $this->product = ProductPeer::retrieveByPK($this->customerProduct->getProductId());


        //echo  $TelintaMobile = sfConfig::get('app_country_code').$this->customer->getMobileNumber();
        $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
        if ($getFirstnumberofMobile == 0) {
            $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
            $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
        } else {
            $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
        }
        $emailId = $this->customer->getEmail();
        $uniqueId = $this->customer->getUniqueid();


        if ($uniqueId == '') {
            $message_body = "Uniqueid Is not assign Of this Mobile Number $TelintaMobile";
            //Send Email to User/Agent/Support --- when Customer Refilll --- 01/15/11
            emailLib::sendErrorTelinta($this->customer, $message_body);
        }
        //This is for Retrieve balance From Telinta
        // $telintaGetBalance = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=getbalance&name=' . $uniqueId . '&type=customer');
        $telintaGetBalance = Telienta::getBalance($this->customer);
        $this->customer_balance = $telintaGetBalance;

        if ($this->customer_balance != null)
            $this->customer_balance = $this->customer_balance;

        $c = new Criteria();
        $c->add(CustomerChangeProductPeer::CUSTOMER_ID, $customer->getId());
        $c->addAnd(CustomerChangeProductPeer::STATUS, 2);
        $ccpCount = CustomerChangeProductPeer::doCount($c);
        $this->ccpCount = $ccpCount;
    }

    //This Function add Again new Feature Wls2 --
    public function executeSubscribevoip(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);
        //-----------------------

        $customerids = $request->getParameter('cid');

        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));

        //$this->redirectUnless($this->customer, "@homepage");

        $this->customer_balance = -1;

        $country_id = $this->customer->getCountryId();

        //This Section For Get the Language Symbol For Set Currency -
        $enableCountry = new Criteria();
        $enableCountry->add(EnableCountryPeer::ID, $country_id);
        $country_id = EnableCountryPeer::doSelectOne($enableCountry); //->getId();
        if ($country_id) {
            $langSym = $country_id->getLanguageSymbol();
        } else {
            $langSym = sfConfig::get('app_language_symbol');
        }
        //--------------------------------------------------------
        //$lang =  $this->getUser()->getAttribute('activelanguage');
        $lang = $langSym;
        $this->lang = $lang;

        //try to get balance again & again
        $num_tries = 3;

        for ($i = 0; ($i < 3) && $this->customer_balance == -1; $i++) {
            $this->customer_balance = (double) Fonet::getBalance($this->customer);
        }

        //echo  $TelintaMobile = sfConfig::get('app_country_code').$this->customer->getMobileNumber();
        $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
        if ($getFirstnumberofMobile == 0) {
            $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
            $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
        } else {
            $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
        }
        $emailId = $this->customer->getEmail();
        $uniqueId = $this->customer->getUniqueid();
        //This is for Retrieve balance From Telinta
        $this->customer_balance = Telienta::getBalance($this->customer);


        //$this->customer_balance = 100;

        if ($request->isMethod('post')) {

            if ($this->customer_balance > 40) {
                $voipcharges = "-40";

                $order = new CustomerOrder();
                $order->setProductId(5);
                $order->setCustomerId($customerids);
                $order->setExtraRefill($voipcharges);
                // $order->setIsFirstOrder(1);
                $order->setOrderStatusId(3);
                echo 'order' . $order->save();

                echo '<br/>';
                $this->customer = $customerids;
                $transaction = new Transaction();
                $transaction->setAmount($voipcharges);
                $transaction->setDescription($this->getContext()->getI18N()->__('Transaction for VoIP Purchase'));
                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customerids);
                $transaction->setTransactionStatusId(3);
                echo 'transaction' . $transaction->save();
                echo '<br/>';

                $customer = new Criteria();
                $customer->add(CustomerPeer::ID, $customerids);
                $customer = CustomerPeer::doSelectOne($customer);
                //
                $rs = new Criteria();
                $rs->add(SeVoipNumberPeer::CUSTOMER_ID, $customerids);
                $rs->addAnd(SeVoipNumberPeer::IS_ASSIGNED, 3);
                $voip_customer = '';
                if (SeVoipNumberPeer::doCount($rs) > 0) {
                    $voip_customer = SeVoipNumberPeer::doSelectOne($rs);
                } else {

                    $c = new Criteria();
                    //$c->setLimit(1);
                    $c->add(SeVoipNumberPeer::IS_ASSIGNED, 0);
                    if (SeVoipNumberPeer::doCount($c) < 10) {
                        emailLib::sendErrorInTelinta("Resenumber about to Finis", "Resenumbers in the " . sfConfig::get('app_site_title') . " are lest then 10 . ");
                    }
                    if (!$voip_customer = SeVoipNumberPeer::doSelectOne($c)) {
                        emailLib::sendErrorInTelinta("Resenumber Finished", "Resenumbers in the " . sfConfig::get('app_site_title') . " are finished. This error is faced by customer id: " . $customerids);
                        return false;
                    }
                }
                // echo $voip_customer->getId()."Baran here<hr/>";
                $voip_customer->setUpdatedAt(date('Y-m-d H:i:s'));
                $voip_customer->setCustomerId($customerids);
                $voip_customer->setIsAssigned(1);
                $voip_customer->save();

                //  echo $voip_customer->getId()."Baran here<hr/>";
                // die;
                //--------------------------Telinta------------------/
                $getvoipInfo = new Criteria();
                $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customerids);
                $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
                if (isset($getvoipInfos)) {
                    $voipnumbers = $getvoipInfos->getNumber();
                    $firsttwocharcters = substr($voipnumbers, 0, 2);
                    if ($firsttwocharcters == "00") {
                        $voipnumbers = substr($voipnumbers, 2);
                    } else {
                        $voipnumbers = $voipnumbers;
                    }
                    // echo $voipnumbers;
                    // die;
                    $voip_customer = $getvoipInfos->getCustomerId();
                    $this->customer = $customer;
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

                    //This Condtion for if IC Active
                    $tc = new Criteria();
                    $tc->add(CallbackLogPeer::UNIQUEID, $uniqueId);
                    $tc->addDescendingOrderByColumn(CallbackLogPeer::CREATED);
                    $MaxUniqueRec = CallbackLogPeer::doSelectOne($tc);
                    if (isset($MaxUniqueRec)) {
                        $TelintaMobile = $MaxUniqueRec->getMobileNumber();
                    }
                    //------------------------------

                    Telienta::createReseNumberAccount($voipnumbers, $this->customer, $TelintaMobile);


                    $OpeningBalance = '40';

                    //type=<account_customer>&action=manual_charge&name=<name>&amount=<amount>
                    //This is for Recharge the Customer

                    Telienta::charge($this->customer, $OpeningBalance, "Resenumber Payment");
                }

//exit;
                //----------------------------------------------------
                //----------------Send Email--------------
                //set vat

                $this->customer = $customer;
                $vat = 0;
                $subject = $this->getContext()->getI18N()->__('Transation for VoIP Purchase');
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
                emailLib::sendvoipemail($this->customer, $order, $transaction);
                $this->updatePreferredCulture();

                //------------------------------
                $this->redirect($this->getTargetUrl() . 'customer/voippurchased');
            }
        }
    }

    public function executeVoiptermsandcondition(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------
        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );

        $this->redirectUnless($this->getUser()->isAuthenticated(), "@homepage");
    }

    public function executeVoippurchased(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );

        $this->redirectUnless($this->getUser()->isAuthenticated(), "@homepage");
    }

    public function executeUnsubscribevoip(sfWebRequest $request) {
        changeLanguageCulture::languageCulture($request, $this);

        $customerids = $this->getUser()->getAttribute('customer_id', '', 'usersession');
        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));

        //--------------------------Telinta------------------/
        $getvoipInfo = new Criteria();
        $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customerids);
        $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
        if (isset($getvoipInfos)) {
            $voipnumbers = $getvoipInfos->getNumber();
            $voipnumbers = substr($voipnumbers, 2);
            $voip_customer = $getvoipInfos->getCustomerId();

            $getvoipInfos->setIsAssigned(3);
            $getvoipInfos->save();

            $res = new Criteria();
            $res->add(TelintaAccountsPeer::ACCOUNT_TITLE, $voipnumbers);
            $res->addAnd(TelintaAccountsPeer::STATUS, 3);
            if (TelintaAccountsPeer::doCount($res) > 0) {
                $telintaAccountres = TelintaAccountsPeer::doSelectOne($res);
                Telienta::terminateAccount($telintaAccountres);
            } else {
                $this->redirect('customer/dashboard');
            }
        }
    }

    public function executeRefill(sfWebRequest $request) {
        $this->target = $this->getTargetUrl();
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------
        //$this->redirectUnless($this->getUser()->isAuthenticated(),'@b2c_homepage');
        //$customer_id = $this->getUser()->getAttribute('customer_id',null, 'usersession');
        //TODO: authentication is missing

        $customer_id = $request->getParameter('customer_id');

        $this->customer = CustomerPeer::retrieveByPK($customer_id);

        $this->redirectUnless($this->customer, "@homepage");

        $this->form = new ManualRefillForm($customer_id);
        $c = new Criteria();
        $c->add(ProductPeer::PRODUCT_TYPE_ID, 2);

        $this->refillProducts = ProductPeer::doSelect($c);

        //new order
        $this->order = new CustomerOrder();

        $customer_products = $this->customer->getProducts();

        $this->order->setProduct($customer_products[0]);
        $this->order->setCustomer($this->customer);
        $this->order->setQuantity(1);
        $refills_options = ProductPeer::getRefillChoices();
        $this->order->setExtraRefill($refills_options[0]);
        $this->order->save();

        //new transaction
        $transaction = new Transaction();

        $transaction->setAmount($this->order->getExtraRefill() * (sfConfig::get('app_vat_percentage') + 1));
        $transactiondescription = TransactionDescriptionPeer::retrieveByPK(9);
        $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
        $transaction->setTransactionDescriptionId($transactiondescription->getId());
        $transaction->setDescription($transactiondescription->getTitle());
        $transaction->setOrderId($this->order->getId());
        $transaction->setCustomerId($this->order->getCustomerId());
         $transaction->setVat($this->order->getExtraRefill()*sfConfig::get('app_vat_percentage'));

        //save
        $transaction->save();
    }

    public function executeRefillAccept(sfWebRequest $request) {
        $this->getUser()->setCulture($request->getParameter('lng'));

        $this->redirect('customer/dashboard');
    }

    public function executeRefillReject(sfWebRequest $request) {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11



        $order_id = $request->getParameter('orderid');
        //$error_text = substr($request->getParameter('errortext'), 0, strpos($request->getParameter('errortext'), '!'));
        $error_text = $this->getContext()->getI18N()->__('Payment is unfortunately not accepted because your information is incorrect, please try again by entering correct credit card information');

        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $c = new Criteria();
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);

        $this->forward404Unless($order);

        $order->setOrderStatusId(sfConfig::get('app_status_cancelled')); //cancelled

        $this->getUser()->setFlash('error_message',
                $error_text
        );

        $this->order = $order;
        $this->forward404Unless($this->order);

        //required for some templates
        $this->customer = $this->order->getCustomer();

        $this->order_id = $order->getId();
        $this->amount = $transaction->getAmount();
        $this->form = new ManualRefillForm($this->order->getCustomerId());
        $this->setTemplate('refill');
    }

    public function executeCallhistory(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);

        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        $this->redirectUnless($this->customer, "@homepage");

        $fromdate = mktime(0, 0, 0, date("m"), date("d") - 15, date("Y"));
        $this->fromdate1 = date("d-m-Y", $fromdate);
        $this->fromdate = date("Y-m-d", $fromdate);
        $todate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $this->todate1 = date("d-m-Y", $todate);
        $this->todate = date("Y-m-d", $todate);

        if ($request->isMethod('post')) {
            $this->fromdate1 = $request->getParameter('startdate');
            $this->todate1 = $request->getParameter('enddate');

            $this->fromdate = date("Y-m-d", strtotime($request->getParameter('startdate')));
            $this->todate = date("Y-m-d", strtotime($request->getParameter('enddate')));
        }



        $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);
        if ($getFirstnumberofMobile == 0) {
            $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
            $this->TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
        } else {
            $this->TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
        }

        $this->numbername = $this->customer->getUniqueid();
    }

    public function executePaymenthistory(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //$this->customer = CustomerPeer::retrieveByPK(58);

        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );

        $this->redirectUnless($this->customer, "@homepage");

        //get  transactions
        $c = new Criteria();
        $c->add(TransactionPeer::CUSTOMER_ID, $this->customer->getId());
        $c->add(TransactionPeer::TRANSACTION_STATUS_ID, sfConfig::get('app_status_completed'));
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
        //This Section For Get the Language Symbol For Set Currency - kmmalik.com
        $country_id = $this->customer->getCountryId();
        $enableCountry = new Criteria();
        $enableCountry->add(EnableCountryPeer::ID, $country_id);
        $country_id = EnableCountryPeer::doSelectOne($enableCountry); //->getId();
        if ($country_id) {
            $langSym = $country_id->getLanguageSymbol();
        } else {
            $langSym = sfConfig::get('app_language_symbol');
        }
        //--------------------------------------------------------
        //$lang =  $this->getUser()->getAttribute('activelanguage');
        $lang = $langSym;
        $this->lang = $lang;
        //--------------------------------------------------------

        $c->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);

        //set paging
        $items_per_page = 10; //shouldn't be 0
        $this->page = $request->getParameter('page');
        if ($this->page == '')
            $this->page = 1;

        $pager = new sfPropelPager('Transaction', $items_per_page);
        $pager->setPage($this->page);

        $pager->setCriteria($c);

        $pager->init();

        $this->transactions = $pager->getResults();
        $this->total_pages = $pager->getNbResults() / $items_per_page;
    }

    public function executeRefillpaymenthistory(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //$this->customer = CustomerPeer::retrieveByPK(58);

        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );

        $this->redirectUnless($this->customer, "@homepage");

        //get  transactions
        $c = new Criteria();

        $c->add(TransactionPeer::CUSTOMER_ID, $this->customer->getId());
        $c->add(TransactionPeer::TRANSACTION_STATUS_ID, sfConfig::get('app_status_completed')
        );
        // Here we can simple check the transaction stats and we can meet our requirements but here use the description value equel which is i dnt
        // Good approch but me not edit this i just pass one more "Resenummer bekräftelse" - ahtsham
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
        //This Section For Get the Language Symbol For Set Currency - Ahtsham - LandNCall AB
        $country_id = $this->customer->getCountryId();
        $enableCountry = new Criteria();
        $enableCountry->add(EnableCountryPeer::ID, $country_id);
        $country_id = EnableCountryPeer::doSelectOne($enableCountry); //->getId();
        if ($country_id) {
            $langSym = $country_id->getLanguageSymbol();
        } else {
            $langSym = sfConfig::get('app_language_symbol');
        }
        //--------------------------------------------------------
        //$lang =  $this->getUser()->getAttribute('activelanguage');
        $lang = $langSym;
        $this->lang = $lang;
        //--------------------------------------------------------

        $c->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);

        //set paging
        $items_per_page = 10; //shouldn't be 0
        $this->page = $request->getParameter('page');
        if ($this->page == '')
            $this->page = 1;

        $pager = new sfPropelPager('Transaction', $items_per_page);
        $pager->setPage($this->page);

        $pager->setCriteria($c);

        $pager->init();

        $this->transactions = $pager->getResults();
        $this->total_pages = round($pager->getNbResults() / $items_per_page,0);
    }

    public function executePasswordchange(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------

        $this->redirectUnless($this->getUser()->isAuthenticated(), "@homepage");
        //$this->customer = CustomerPeer::retrieveByPK(58);
        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        //$this->forward404Unless($this->customer);
        $this->redirectUnless($this->customer, "@homepage");

        $this->form = new CustomerForm(CustomerPeer::retrieveByPK($this->customer->getId()));


        unset($this->form['first_name']);
        unset($this->form['last_name']);
        unset($this->form['country_id']);
        unset($this->form['city']);
        unset($this->form['po_box_number']);
        unset($this->form['mobile_number']);
        unset($this->form['device_id']);
        unset($this->form['email']);
        unset($this->form['is_newsletter_subscriber']);
        unset($this->form['created_at']);
        unset($this->form['updated_at']);
        unset($this->form['customer_status_id']);
        unset($this->form['address']);
        unset($this->form['fonet_customer_id']);
        unset($this->form['referrer_id']);
        unset($this->form['telecom_operator_id']);
        unset($this->form['date_of_birth']);
        unset($this->form['other']);
        unset($this->form['subscription_type']);
        unset($this->form['auto_refill_amount']);
        unset($this->form['subscription_id']);
        unset($this->form['last_auto_refill']);
        unset($this->form['auto_refill_min_balance']);
        unset($this->form['c9_customer_number']);
        unset($this->form['registration_type_id']);
        unset($this->form['imsi']);
        unset($this->form['uniqueid']);
        unset($this->form['plain_text']);
        unset($this->form['ticketval']);
        unset($this->form['to_date']);
        unset($this->form['from_date']);
        unset($this->form['uniqueid']);
        unset($this->form['plain_text']);
        unset($this->form['ticketval']);
        unset($this->form['to_date']);
        unset($this->form['from_date']);
        unset($this->form['i_customer']);
        unset($this->form['terms_conditions']);
        unset($this->form['manufacturer']);
        unset($this->form['product']);
        unset($this->form['second_last_name']);
        unset($this->form['nie_passport_number']);
        unset($this->form['preferred_language_id']);
        unset($this->form['province_id']);
        unset($this->form['sim_type_id']);
        unset($this->form['nationality_id']);
        unset($this->form['date_of_birth']);
        unset($this->form['comments']);
        unset($this->form['block']);
        unset($this->form['usage_alert_sms']);
        unset($this->form['usage_alert_email']);

        //  unset($this->form['password']);
        // unset($this->form['password_confirm']);
        /////////////////////////////////////
        //   $this->form->getWidget('password')->setAttribute('value', '');
        /////////////////////////////////////////
        $this->oldpasswordError = '';
        $this->oldpassword = '';
        if ($request->isMethod('post')) {
            $customers = $request->getParameter($this->form->getName());
            $customerId = $customers["id"];
            //echo '<br>';
            $getcusInfo = new Criteria();
            $getcusInfo->add(CustomerPeer::ID, $customerId);
            $getcusInfos = CustomerPeer::doSelectOne($getcusInfo); //->getId();
            $customeroldpass = $getcusInfos->getPlainText();
            if ($customeroldpass == $customers["oldpassword"]) {
                $this->oldpasswordError = '';
            } else {
                $this->oldpasswordError = 'wrong';
            }
            $this->oldpassword = $customers["oldpassword"];
            $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));



            if ($this->form->isValid() && $this->oldpasswordError == '') {
                //	echo 'validated';
                $customer = $this->form->save();

                $plainPws = $customers["password"];


                $customer->setPlainText($plainPws);

                $customer->save();

                $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Your Password has been saved.'));
            }
            // echo 'after';
        }
    }

    public function executeSettings(sfWebRequest $request) {


        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);
        //-----------------------

        $this->redirectUnless($this->getUser()->isAuthenticated(), "@homepage");
        //$this->customer = CustomerPeer::retrieveByPK(58);
        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        //$this->forward404Unless($this->customer);
        $this->redirectUnless($this->customer, "@homepage");

        $this->form = new CustomerForm(CustomerPeer::retrieveByPK($this->customer->getId()));
        unset($this->form['terms_conditions']);
        unset($this->form['password']);
        unset($this->form['product']);
        unset($this->form['password_confirm']);
        /////////////////////////////////////
        unset($this->form['created_at']);
        unset($this->form['fonet_customer_id']);
        unset($this->form['referrer_id']);
        unset($this->form['registration_type_id']);
        unset($this->form['plain_text']);
        unset($this->form['uniqueid']);
        unset($this->form['auto_refill_min_balance']);
        unset($this->form['auto_refill_amount']);
        unset($this->form['last_auto_refill']);
        unset($this->form['manufacturer']);
        unset($this->form['device_id']);
        unset($this->form['ticketval']);
        unset($this->form['i_customer']);
        unset($this->form['usage_alert_sms']);
        unset($this->form['usage_alert_email']);
        unset($this->form['sim_type_id']);
        unset($this->form['comments']);
        unset($this->form['block']);
        unset($this->form['usage_alert_sms']);
        unset($this->form['usage_alert_email']);

        $this->uniqueidValue = $this->customer->getUniqueId();
        //This Section For Get the Language Symbol For Set Currency -
        $getvoipInfo = new Criteria();
        $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $this->customer->getId());
        $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
        if (isset($getvoipInfos)) {
            $this->voipnumbers = $getvoipInfos->getNumber();
            $this->voip_customer = $getvoipInfos->getCustomerId();
        } else {
            $this->voipnumbers = '';
            $this->voip_customer = '';
        }


        /////////////////////////////////////////

        if ($request->isMethod('post')) {
            $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
            //  echo 'before validation';

            if ($this->form->isValid()) {
                //	echo 'validated';
                $customer = $this->form->save();

                $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Your settings changes have been saved.'));
            }
            // echo 'after';
        }

        $this->form->getWidget('mobile_number')->setAttribute('readonly', 'readonly');
        $this->form->getWidget('nie_passport_number')->setAttribute('readonly', 'readonly');
    }

    public function executeLogin(sfWebRequest $request) {

        $this->target = $this->getTargetUrl();
        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));

        if($request->getParameter('lang') != ''){
            $this->getUser()->setCulture($request->getParameter('lang')); 
        }


        if ($this->customer) {

            $this->redirect($this->getTargetUrl() . 'customer/dashboard');
            
        } else {

            if ($request->isMethod('post') && $request->getParameter('mobile_number') != '' && $request->getParameter('password') != '') {
                $paswordval = $request->getParameter('password');
                $mobile_number = $request->getParameter('mobile_number');
                $password = sha1($request->getParameter('password'));

                $c = new Criteria();
                $c->add(CustomerPeer::MOBILE_NUMBER, $mobile_number);
                $c->addAnd(CustomerPeer::PASSWORD, $password);
                $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
                $c->addAnd(CustomerPeer::BLOCK, 0);
                $cnt = CustomerPeer::doCount($c);
                if ($cnt > 0) {
                    $customer = CustomerPeer::doSelectOne($c);
                } else {
                    $c = new Criteria();
                    $c->add(CustomerPeer::NIE_PASSPORT_NUMBER, $mobile_number);
                    $c->addAnd(CustomerPeer::PASSWORD, $password);
                    $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID, 3);
                    $c->addAnd(CustomerPeer::BLOCK, 0);
                    $customer = CustomerPeer::doSelectOne($c);
                }


//die;
                if ($customer) {

                    header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
                    $this->getUser()->setAttribute('customer_id', $customer->getId(), 'usersession');
                    $this->getUser()->setAuthenticated(true);

                    $lang = PreferredLanguagesPeer::retrieveByPK($customer->getPreferredLanguageId());
                    $this->getUser()->setCulture($lang->getLanguageCode());



                    $customer->setPlainText($paswordval);
                    $customer->save();


                    //$this->redirect('@customer_dashboard');
                    if ($request->isXmlHttpRequest())
                        $this->renderText('ok');
                    else {


                        if (isset($pathArray['HTTP_REFERER']) && $pathArray['HTTP_REFERER'] != '') {
                            $this->redirect($pathArray['HTTP_REFERER']);
                        } else {
                            $this->redirect($this->getTargetUrl() . 'customer/dashboard');
                        }
                    }
                } else {
                    //
                    if ($request->isXmlHttpRequest())
                        $this->renderText('invalid');
                    else {
                        $this->getUser()->setFlash('error_message', $this->getContext()->getI18N()->__('Invalid mobile number or password.'));
                    }
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $this->renderPartial('login');
                    return sfView::NONE;
                }
            }
        }
    }

    public function executeLogout(sfWebRequest $request) {

        $this->getUser()->getAttributeHolder()->removeNameSpace('usersession');
        $this->getUser()->setAuthenticated(false);
        $this->redirect('@b2c_homepage');

        return sfView::NONE;
    }

    public function executeSendPassword(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //$this->forward404Unless($request->isMethod('post'));
        $this->redirectUnless($request->isMethod('post'), "@homepage");

        $c = new Criteria();

        $c->add(CustomerPeer::MOBILE_NUMBER, $request->getParameter('mobile'));
        $c->add(CustomerPeer::CUSTOMER_STATUS_ID, sfConfig::get('app_status_completed', 3));

        //echo $c->toString(); exit;
        $customer = CustomerPeer::doSelectOne($c);

        if ($customer) {
            //change the password to some thing uniuque and complex
            $new_password = substr(base64_encode($customer->getPassword()), 0, 8);
            //echo $new_password.''.$customer->getPassword();
            $customer->setPlainText($new_password);
            $customer->setPassword($new_password);
            $this->setPreferredCulture($customer);
            $message_body = $this->getContext()->getI18N()->__('Dear customer'); //. ' ' . $customer->getFirstName() . '&nbsp;' . $customer->getLastName() . '!';
            $message_body .= '<br /><br />';

            $message_body .= $this->getContext()->getI18N()->__('Your password has been changed. Please use the following information to enter MY ACCOUNT.', array('%1%' => sfConfig::get('app_site_title')));

            $message_body .= '<br /><br />';
            $message_body .= sprintf($this->getContext()->getI18N()->__('Mobile number: %s'), $customer->getMobileNumber());
            $message_body .= '<br />';
            $message_body .= $this->getContext()->getI18N()->__('Password') . ': ' . $new_password;

            $customer->save();

            //$this->renderText($message_body);
            //send email


            $subject = $this->getContext()->getI18N()->__('Password Request');
            $sender_email = sfConfig::get('app_email_sender_email', 'rs@zapna.com');
            $sender_name = sfConfig::get('app_email_sender_name', 'support');

            $message = $message_body;

            $receipient_email = trim($customer->getEmail());
            $receipient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());


            //This Seciton For Make The Log History When Complete registration complete - Agent
            //echo sfConfig::get('sf_data_dir');
            $invite_data_file = sfConfig::get('sf_data_dir') . '/invite.txt';
            $invite2 = "Reset Password \n";
            if ($receipient_name):
                $invite2 = "Recepient Email: " . $receipient_email;
            endif;

            file_put_contents($invite_data_file, $invite2, FILE_APPEND);

            //Send Email to User --- when Forget Password Request Come --- 01/15/11

            emailLib::sendForgetPasswordEmail($customer, $message, $subject);
            $this->updatePreferredCulture();

            $this->getUser()->setFlash('send_password_message', $this->getContext()->getI18N()->__('Your account details have been sent to your e-mail address.'));
        }
        else {
            $this->getUser()->setFlash('send_password_error_message', $this->getContext()->getI18N()->__('No customer is registered with this mobile number.'));
        }

        return $this->redirect('customer/login');
    }

//end funcition

    public function executeGetHeader() {
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11


        echo $this->getPartial("header", array('test_dir' => '/testwp'));
        sfConfig::set('sf_web_debug', false);
        return sfView::NONE;
    }

    public function executeC9Callhistory(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
//try to find customer in session (only possible if user already logged in.)
        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        $this->redirectUnless($this->customer, "@homepage");

//create criteria

        $c = new Criteria();
        $c->add(Cloud9DataPeer::MSISDN, $this->customer->getC9CustomerNumber());
        $c->add(Cloud9DataPeer::LEG, "B");


//set paging
        $items_per_page = 25; //shouldn't be 0
        $this->page = $request->getParameter('page');
        if ($this->page == '')
            $this->page = 1;

        $pager = new sfPropelPager('Cloud9Data', $items_per_page);
        $pager->setPage($this->page);

        $pager->setCriteria($c);

        $pager->init();

        $this->callRecords = $pager->getResults();
        $this->total_pages = $pager->getNbResults() / $items_per_page;
    }

    public function executeWebsms(sfWebRequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------


        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        $this->redirectUnless($this->customer, "@homepage");


        $cunt = new Criteria();
        //$cunt->add(CountryPeer::WEB_SMS_STATUS,3);
        $cunt->addAscendingOrderByColumn(CountryPeer::NAME);
        $countries = CountryPeer::doSelect($cunt);
        $this->msgSent = "";
        $this->countries = $countries;
        $this->res_cbf = "";
        $this->balance = (double) Telienta::getBalance($this->customer);

        $message = $request->getParameter('message');

       // echo $this->getContext()->getI18N()->__("-Sent by");
        if ($message) {
            $this->msgSent = "No";
            $country_code = $request->getParameter('country');
            $number = $request->getParameter('number');
            $destination = $country_code . $number;

            $c = new Criteria();
            $c->add(CountryPeer::CALLING_CODE, $request->getParameter('country'));
            $country = CountryPeer::doSelectOne($c);

            $messages = array();
            if (strlen($message) < 142) {
                $messages[1] = $message . $this->getContext()->getI18N()->__("-Sent by")." Kimarin-";
            } else if (strlen($message) > 142 and strlen($message) < 302) {

                $messages[1] = substr($message, 1, 142) . $this->getContext()->getI18N()->__("-Sent by")." Kimarin-";
                $messages[2] = substr($message, 143) . $this->getContext()->getI18N()->__("-Sent by")." Kimarin-";
            } else if (strlen($message) > 382) {
                $messages[1] = substr($message, 1, 142) . $this->getContext()->getI18N()->__("-Sent by")." Kimarin-";
                $messages[2] = substr($message, 143, 302) . $this->getContext()->getI18N()->__("-Sent by")." Kimarin-";
                $messages[3] = substr($message, 303, 432) . $this->getContext()->getI18N()->__("-Sent by")." Kimarin-";
            }

            foreach ($messages as $sms_text) {
                $cbf = new Cbf();
                $cbf->setS('H');
                $cbf->setDa($destination);
                $cbf->setMessage($sms_text);
                $cbf->setCountryId($country->getId());
                $cbf->setMobileNumber($this->customer->getMobileNumber());
                $cbf->setCustomerId($this->customer->getId());
                $cbf->save();

                $amt = $country->getCbfRate();
                $amt = number_format($amt, 2);


                if (CARBORDFISH_SMS::Send($destination, $sms_text, $this->customer->getMobileNumber())) {
                    $cbf->setStatus(3);
                    $cbf->save();
                    $description = "SMS charges";
                    Telienta::charge($this->customer, $amt, $description);
                    $this->msgSent = "Yes";
                    $this->balance = (double) Telienta::getBalance($this->customer);
                } else {
                    $this->res_cbf = "Response from CBF is:";
                }
            }
        }
    }

    public function executeSmsHistory(sfWebrequest $request) {

        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        changeLanguageCulture::languageCulture($request, $this);
        //-----------------------
        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        $this->redirectUnless($this->customer, "@homepage");
        $c = new Criteria();
        $c->add(CbfPeer::CUSTOMER_ID, $this->customer->getId());
        $c->add(CbfPeer::STATUS, 3);
        $c->addDescendingOrderByColumn(CbfPeer::CREATED_AT);
        $items_per_page = 25; //shouldn't be 0
        $this->page = $request->getParameter('page');
        if ($this->page == '')
            $this->page = 1;

        $pager = new sfPropelPager('Cbf', $items_per_page);
        $pager->setPage($this->page);

        $pager->setCriteria($c);

        $pager->init();

        $this->smsRecords = $pager->getResults();
        $this->total_pages = $pager->getNbResults() / $items_per_page;
    }

    public function executeTellAFriend(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );

        $this->redirectUnless($this->getUser()->isAuthenticated(), "@homepage");


        $invite = new Invite();


        if ($request->isMethod('post')) {

            //$this->form = new ContactForm();
            $this->setPreferredCulture($this->customer);
            $recepient_email = $request->getParameter('email');
            $recepient_name = $request->getParameter('name');

            $message = $request->getParameter('message');
            $invite->setEmail($recepient_email);
            $invite->setInviteName($recepient_name);
            $invite->setInviteStatus('1');
            $invite->setCustomerId($this->customer->getId());
            $invite->setMessage($message);
            $invite->save();
            $message = "<p style='font-family:\"Times New Roman\", Times, serif;font-size: 14px;'>".$message.'</p>';

            $subject = $this->getContext()->getI18N()->__("%1% invitation", array('%1%' => sfConfig::get('app_site_title')));

            $name = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
            $message_body = "<p style='font-family:\"Times New Roman\", Times, serif;font-size: 14px;'>";
            $message_body .= /* $this->getContext()->getI18N()->__('Hi ') . */$recepient_name . ',<br /> ' . $this->getContext()->getI18N()->__("This invitation has been sent to you by") . ' ' . $name . ', ' . $this->getContext()->getI18N()->__("who is a registered %1% customer.", array('%1%' => sfConfig::get('app_site_title')));
            $message_body .= '</p>';

            $message_body_end ="<p style='font-family:\"Times New Roman\", Times, serif;font-size: 14px;'>";
            $message_body_end .= /* $this->getContext()->getI18N()->__('Please click accept to start saving money immediately with Smartsim.') . */' <a  href="' . sfConfig::get('app_customer_url') . 'customer/signup?invite_id=' . $invite->getId() . '"> ' . $this->getContext()->getI18N()->__("Go to %1%'s web site for registration.", array('%1%' => sfConfig::get('app_site_title'))) . '</a><br/>' . $this->getContext()->getI18N()->__('Read more') . ' <a href="' . sfConfig::get('app_live_site_url') . '">' . sfConfig::get('app_live_site_url') . '</a>';
            $message_body_end .= '</p>';

            //send email
            if ($recepient_name != ''):
                $email = new EmailQueue();
                $email->setSubject($subject);
                $email->setMessage($message_body .  $message .  $message_body_end);
                $email->setReceipientName($recepient_name);
                $email->setReceipientEmail($recepient_email);

                $email->save();
            endif;
            $this->updatePreferredCulture();
        }
    }

    public function executeRegister(sfWebRequest $request) {

        //validation patterns
        $svk_pattern = "/^[0-9]*$/";
        $product_pattern = "/^[0-9]{2}$/";

        //initialize flags
        $alreadyRegistered = false;
        $invalidMobile = false;
        $invalidEmail = false;
        $valid_product_code = false;
        $customer_registered = false;
        $valid_country_code = false;

        //initialize parameter variables
        $mobile = "";
        $product_code = "";
        $name = "";
        $email = "";
        $country_code = "";
        $city = "";

        //initialize object variables
        $product = NULL;
        $country = NULL;
        $customer = new Customer();
        $order = new CustomerOrder();
        $customer_product = new CustomerProduct();
        $transaction = new Transaction();

        //start function execution
        try {

            //get request parameters
            $name = $request->getParameter('name');
            $mobile = $request->getParameter('mobile');
            $email = $request->getParameter('email');
            $product_code = $request->getParameter('product_code');
            $city = $request->getParameter('city');
            $country_code = $request->getParameter('country');

            //geting product sms code
            if (preg_match($product_pattern, $product_code)) {
                $pc = new Criteria();
                $pc->add(ProductPeer::SMS_CODE, $product_code);
                $product = ProductPeer::doSelectOne($pc);

                if ($product != NULL) {
                    $valid_product_code = true;
//             echo 'product code valid';
//             echo '<br/>';
                }//end if(preg_match($product_pattern,$product_code))
            } else {
                $valid_product_code = false;
//         echo 'product code in-valid';
//             echo '<br/>';
            }//if ($code!="")
//  echo 'checking mobile pattern';
//  echo '<br/>';
            echo preg_match($svk_pattern, $mobile);
//  echo '<br/>';

            if (preg_match($svk_pattern, $mobile)) {

                $invalidMobile = false;
//      echo 'mobile valid';
//      echo '<br/>';
                $mnc = new Criteria();
                $mnc->add(CustomerPeer::MOBILE_NUMBER, $mobile);
                $mnc->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
                $cus = CustomerPeer::doSelectOne($mnc);

                $cc = new Criteria();
                $cc->add(EnableCountryPeer::ID, $country_code);
                $country = EnableCountryPeer::doSelectOne($cc);

                if ($country != NULL) {
                    $valid_country_code = true;
//          echo 'country code valid';
//          echo '<br/>';
                }

                if ($cus != NULL) {
//        echo 'Already Registered';
//        echo '<br/>';
                    $alreadyRegistered = true;
                }
            }


            if (!$invalidMobile and !$alreadyRegistered and !$invalidEmail and $valid_product_code and $valid_country_code) {

//        echo "inside if(invalidMobile and !alreadyRegistered and valid_code and !invalidEmail)";

                $customer->setFirstName($name);
                $customer->setLastName(".");
                $customer->setMobileNumber($mobile);
                $customer->setPassword($mobile);
                $customer->setEmail($email);
                $customer->setCountryId($country->getId());
                $customer->setCity($city);
                $customer->setAddress("not given");
                $customer->setTelecomOperatorId(13);
                $customer->setDeviceId(2191);
                $customer->setCustomerStatusId(sfConfig::get('app_status_completed'));
                $customer->setRegistrationTypeId(6);
                $customer->setPlainText($mobile);
                $customer->setReferrerId(154);

                $mobile = "";
                $numberlength = strlen($mobile);
                $endnumberlength = $numberlength - 2;
                $mnumber = $mobile;
                $number = substr($number, 2, $endnumberlength);
                $message = substr($text, 3, 6);
                $uniqueId = $text;
                $uniqueId = substr($uniqueId, 3, 6);
                $customer->setUniqueid($uniqueId);


                $customer->save();
//        echo '<br/>';
//        echo 'customer'.$customer->save();
//        echo '<br/>';

                $order->setProductId($product->getId());
                $order->setCustomerId($customer->getId());
                $order->setExtraRefill($order->getProduct()->getInitialBalance());
                $order->setIsFirstOrder(1);
                $order->setOrderStatusId(sfConfig::get('app_status_completed'));
                $order->save();
//            echo 'order with product'.$order->save();
//            echo '<br/>';

                $customer_product->setCustomerId($order->getCustomerId());
                $customer_product->setProductId($order->getProductId());
                $customer_product->save();
//            echo 'customer_product'.$customer_product->save();
//            echo '<br/>';


                $transaction->setAgentCompanyId($customer->getReferrerId());
                $transaction->setAmount($order->getProduct()->getPrice() - $order->getProduct()->getInitialBalance() + $order->getExtraRefill());
                $transaction->setDescription($this->getContext()->getI18N()->__('Anmeldung inc. sprechen'));
                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customer->getId());
                $transaction->setTransactionStatusId(3);
                $transaction->save();
//        echo 'transaction'.$transaction->save();
//        echo '<br/>';

                $customer_registered = true;






//////////////////////////////////////////////////////////////////////////////////////
                $this->customer = $customer;
                $gentid = $customer->getReferrerId();
                $productid = $product->getId();
                $transactionid = $transaction->getId();
                if (isset($gentid) && $gentid != "") {
                    $massage = commissionLib::registrationCommission($gentid, $productid, $transactionid);
                }//end if isset
//////////////////////////////////////////////////////////////////////////     /
                Fonet::registerFonet($customer);
                Fonet::recharge($customer, $order->getExtraRefill());

////////////////////////////////////////////////////////////////////////////////////
                $this->customer = $cus;
                //emailLib::sendCustomerRegistrationViaAgentEmail($this->customer,$order);
                echo "success, Customer Registered";
            } else {

//     $sms_text = "Customer not registered, please retry. ";
                if ($invalidMobile) {
//         $sms_text = "error, invalid Mobile Number";
                    echo 'error, invalid Mobile Number';
                } else if ($invalidEmail) {
//         $sms_text = "error, invalid Email";
                    echo 'error, invalid email';
                } else if ($alreadyRegistered) {
//         $sms_text = "error, Number Already Registered";
                    echo "error, Number Already Registered";
                } else if (!$product) {
//         $sms_text = "error, invalid promo code";
                    echo "error, invalid product code";
                } else if (!$valid_country_code) {
                    echo "error, invalid country code";
                }
            }
        } catch (Exception $e) {

            echo "error, internal server error ";
            echo "<br/>";
            echo $e->getLine() . ' : ' . $e->getMessage();
        }

        return sfView::NONE;
    }

    public function executeLandncallRefill(sfWebRequest $request) {

        $valid_amount_pattern = false;
        $valid_amount = false;
        $valid_mobile_pattern = false;
        $valid_number = false;

        $customer = NULL;
        $order = new CustomerOrder();
        $transaction = new Transaction();

        $mobile = "";
        $amount = "";


        $mobile = $request->getParameter('mobile');
        $amount = $request->getParameter('amount');
        $svk_pattern = "/^[0-9]*$/";
        $amount_pattern = "/^[0-9]*$/";

        if (preg_match($amount_pattern, $amount)) {
            $valid_amount_pattern = true;
        }

        if ($amount > 0) {
            $valid_amount = true;
        }

        if (preg_match($svk_pattern, $mobile)) {
            $valid_mobile_pattern = true;
        }


        $mpc = new Criteria();
        $mpc->add(CustomerPeer::MOBILE_NUMBER, $mobile);
        $customer = CustomerPeer::doSelectOne($mpc);

        if ($customer != NULL) {
            $valid_number = true;
        }

        if ($valid_number and $valid_mobile_pattern and $valid_amount and $valid_amount_pattern) {

            $cpc = new Criteria();
            $cpc->add(CustomerPeer::ID, $customer->getId());
            $customer_product = CustomerProductPeer::doSelectOne($cpc);

            $order->setProductId($customer_product->getProductId());
            $order->setCustomerId($customer->getId());
            $order->setExtraRefill($amount / 100);
            $order->setIsFirstOrder(0);
            $order->setOrderStatusId(sfConfig::get('app_status_completed'));
            $order->save();
//            echo 'order with product'.$order->save();
//            echo '<br/>';

            $transaction->setAgentCompanyId($customer->getReferrerId());
            $transaction->setAmount($order->getExtraRefill());
            $transaction->setDescription($this->getContext()->getI18N()->__('Customer Refill'));
            $transaction->setOrderId($order->getId());
            $transaction->setCustomerId($customer->getId());
            $transaction->setTransactionStatusId(3);
            $transaction->save();

            // Fonet::recharge($customer, $order->getExtraRefill() );
            $this->customer = $customer;
            //////////////////////////////////////////////////////////////////////////////////////
            $gentid = $customer->getReferrerId();
            $productid = $customer_product->getCustomerId();
            $transactionid = $transaction->getId();
            if (isset($gentid) && $gentid != "") {
                $massage = commissionLib::refilCustomer($gentid, $productid, $transactionid);
            }//end if isset


            $getFirstnumberofMobile = substr($this->customer->getMobileNumber(), 0, 1);     // bcdef
            if ($getFirstnumberofMobile == 0) {
                $TelintaMobile = substr($this->customer->getMobileNumber(), 1);
                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
            } else {
                $TelintaMobile = sfConfig::get('app_country_code') . $this->customer->getMobileNumber();
            }
            $uniqueId = $this->customer->getUniqueid();
            $OpeningBalance = $order->getExtraRefill();
            //This is for Recharge the Customer
            $MinuesOpeningBalance = $OpeningBalance * 3;
            $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=' . $uniqueId . '&amount=' . $OpeningBalance . '&type=customer');
            //This is for Recharge the Account
            $find = '';
            $string = $telintaAddAccountCB;
            $find = 'ERROR';
            if (strpos($string, $find)) {
                $message_body = "Error ON Refill Customer within Environment <br> Unique Id :$uniqueId <br / >Amount: $OpeningBalance";
                //Send Email to User/Agent/Support --- when Customer Refilll --- 01/15/11
                emailLib::sendErrorTelinta($this->customer, $message_body);
            } else {
                
            }
            //this condition for if follow me is Active
            $getvoipInfo = new Criteria();
            $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $this->customer->getMobileNumber());
            $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
            if (isset($getvoipInfos)) {
                $voipnumbers = $getvoipInfos->getNumber();
                $voip_customer = $getvoipInfos->getCustomerId();
                //$telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name='.$voipnumbers.'&amount='.$OpeningBalance.'&type=account');
            } else {
                //$telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name='.$uniqueId.'&amount='.$OpeningBalance.'&type=account');
            }
            //$telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=a'.$TelintaMobile.'&amount='.$OpeningBalance.'&type=account');
            // $telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?action=recharge&name=cb'.$TelintaMobile.'&amount='.$OpeningBalance.'&type=account');

            $MinuesOpeningBalance = $OpeningBalance * 3;
            //type=<account_customer>&action=manual_charge&name=<name>&amount=<amount>
            //This is for Recharge the Customer
            //$telintaAddAccountCB = file_get_contents('https://mybilling.telinta.com/htdocs/zapna/zapna.pl?type=customer&action=manual_charge&name='.$uniqueId.'&amount='.$MinuesOpeningBalance);


            echo "success, Amount: " . ($amount / 100) . " recharged to mobile: " . $mobile;
            return sfView::NONE;
        } else {

            if (!$valid_amount_pattern) {
                echo "error, incorrect Amount";
            } else if (!$valid_amount) {
                echo "error, incorrect Amount";
            } else if (!$valid_mobile_pattern) {
                echo "error, Invalid Mobile Number";
            } else if (!$valid_number) {
                echo "error, Mobile Number does not exists";
            }
            return sfView::NONE;
        }
    }

    public function executeDeActivateAutoRefill(sfWebRequest $request) {



        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------
        $customerid = $request->getParameter('customer_id');
        $ca = new Criteria();
        $ca->add(CustomerPeer::ID, $customerid);
        $customer = CustomerPeer::doSelectOne($ca);

        $customer->setAutoRefillMinBalance(NULL);
        $customer->setAutoRefillAmount(NULL);
        $customer->save();
        $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Auto Refill is deactivated.'));
        return $this->redirect('customer/dashboard');
        // return sfView::NONE;
    }

    public function executeActivateAutoRefill(sfWebRequest $request) {
        $this->getUser()->setCulture($request->getParameter('lng'));
        $urlval = $request->getParameter('transact');
        $customerid = $request->getParameter('customerid');
        $user_attr_3 = $request->getParameter('user_attr_3');
        $user_attr_2 = $request->getParameter('user_attr_2');
        $db1 = new DibsCall();
        $db1->setCallurl($urlval);
        $db1->save();
        $db2 = new DibsCall();
        $db2->setCallurl($customerid);
        $db2->save();
        $db3 = new DibsCall();
        $db3->setCallurl($user_attr_3);
        $db3->save();
        $db4 = new DibsCall();
        $db4->setCallurl($user_attr_2);
        $db4->save();
        //call Culture Method For Get Current Set Culture - Against Feature# 6.1 --- 02/28/11
        //-----------------------

        $ca = new Criteria();
        $ca->add(CustomerPeer::ID, $customerid);
        $customer = CustomerPeer::doSelectOne($ca);

        $customer->setAutoRefillMinBalance($user_attr_3);
        $customer->setAutoRefillAmount($user_attr_2);
        $customer->setTicketval($urlval);
        $customer->save();
        $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Auto Refill is Activated.'));
        return $this->redirect('customer/dashboard');
    }

    public function executeShortUniqueIds(sfWebRequest $request) {
        
    }

    public function executeChangeCulture(sfWebRequest $request) {

        // var_dump($request->getParameter('new'));
        $this->getUser()->setCulture($request->getParameter('new'));
        //$this->redirect('customer/dashboard');
        $pathArray = $request->getPathInfoArray();
        $this->redirect($pathArray['HTTP_REFERER']);
    }

    public function executeTermsAndCondition(sfWebRequest $request) {

    }

    public function executeSendRefilToPaypal(sfWebRequest $request) {
        $querystring = $request->getParameter('qstr');
        Payment::SendPayment($querystring);
        return sfView::NONE;
    }

    public function executeRefilTransaction(sfWebRequest $request) {
        $this->target = $this->getTargetUrl();
        $order_id = $request->getParameter('item_number');
        $product = ProductPeer::retrieveByPK($request->getParameter('extra_refill'));
        $item_amount = $product->getRegistrationFee() * (sfConfig::get('app_vat_percentage') + 1);
        $order = CustomerOrderPeer::retrieveByPK($order_id);
        $order->setProductId($product->getId());
        $order->setExtraRefill($product->getInitialBalance() + $product->getBonus());
        $order->save();

        $c = new Criteria;
        $c->add(TransactionPeer::ORDER_ID, $order_id);
        $transaction = TransactionPeer::doSelectOne($c);
        $transaction->setAmount($item_amount);
        $transaction->setDescription($product->getDescription());
         $transaction->setVat($product->getRegistrationFee()*sfConfig::get('app_vat_percentage'));
        $transaction->save();


        $lang = $this->getUser()->getCulture();
        
        
           if($lang=='en'){
              $return_url = "http://www.kimarin.es/refill-thanks.html";
        $cancel_url = "http://www.kimarin.es/refill-reject.html";   
           }else{
                             $return_url = "http://www.kimarin.es/".$lang."/refill-thanks_".$lang.".html";
        $cancel_url = "http://www.kimarin.es/".$lang."/refill-reject_".$lang.".html";   
           }
      
//        $return_url = "http://www.kimarin.es/".$langPara."refill-thanks_".$langPara.".html";
//        $cancel_url = "http://www.kimarin.es/refill-reject_".$langPara.".html";
        //   $notify_url = $this->getTargetUrl().'pScripts/calbackrefill?lang='.$lang.'&order_id='.$order_id.'&amountval='.$item_amount;

        $callbackparameters = $lang . '-' . $order_id . '-' . $item_amount;
        $notify_url = $this->getTargetUrl() . 'pScripts/calbackrefill?p=' . $callbackparameters;

        $email2 = new DibsCall();
        $email2->setCallurl($notify_url);

        $email2->save();

        $querystring = '';
        $_POST["amount"] = $item_amount;
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
            $this->customerBalance = Telienta::getBalance($this->customer);
            $this->product = $product;

            //   $environment = "sandbox";

            /* if ($order_id && $item_amount) {
              Payment::SendPayment($querystring);
              } else {
              echo 'error';
              }
              return sfView::NONE; */
            //exit();
        }
    }

    private function setPreferredCulture(Customer $customer) {
        $this->currentCulture = $this->getUser()->getCulture();
        $preferredLang = PreferredLanguagesPeer::retrieveByPK($customer->getPreferredLanguageId());
        $this->getUser()->setCulture($preferredLang->getLanguageCode());
    }

    private function updatePreferredCulture() {
        $this->getUser()->setCulture($this->currentCulture);
    }

    public function executeBlockCustomer(sfWebRequest $request) {

        $this->redirectUnless($this->getUser()->isAuthenticated(), "@homepage");
        //$this->customer = CustomerPeer::retrieveByPK(58);
        $customer = CustomerPeer::retrieveByPK(
                        $this->getUser()->getAttribute('customer_id', null, 'usersession')
        );
        //$this->forward404Unless($this->customer);
        $this->redirectUnless($customer, "@homepage");

        $c = new Criteria;
        $c->add(TelintaAccountsPeer::I_CUSTOMER, $customer->getICustomer());
        $c->add(TelintaAccountsPeer::STATUS, 3);
        $tilentAccounts = TelintaAccountsPeer::doSelect($c);

        foreach ($tilentAccounts as $tilentAccount) {
            $accountInfo['i_account'] = $tilentAccount->getIAccount();
            $accountInfo['blocked'] = "Y";
            Telienta::updateAccount($accountInfo);
        }
        $customer->setBlock(1);
        $customer->save();

        $this->setPreferredCulture($customer);
        emailLib::sendBlockCustomerEmail($customer);
        $this->updatePreferredCulture();

        $this->getUser()->setFlash('message', $this->getContext()->getI18N()->__('Konto er deaktivert.'));
        $this->getUser()->getAttributeHolder()->removeNameSpace('usersession');
        $this->getUser()->setAuthenticated(false);
        $this->redirect('@b2c_homepage');

        return sfView::NONE;
    }

    public function executeChangenumberservice(sfWebRequest $request) {
        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));

        $this->redirectUnless($this->customer, "@homepage");
        $this->targetUrl = $this->getTargetUrl();

        $change_no_startdate = date('Y-m-1 h:i:s');
        $change_no_enddate = date('Y-m-t h:i:s');

        $cn = new Criteria();
        $cn->add(ChangeNumberDetailPeer::CUSTOMER_ID, $this->customer->getId());
        $cn->addAnd(ChangeNumberDetailPeer::CREATED_AT, $change_no_startdate, Criteria::GREATER_EQUAL);
        $cn->addAnd(ChangeNumberDetailPeer::CREATED_AT, $change_no_enddate, Criteria::LESS_EQUAL);
        $cn->addAnd(ChangeNumberDetailPeer::STATUS, 1);
        $change_number_count = ChangeNumberDetailPeer::doCount($cn);
        $this->change_number_count = $change_number_count;
        $this->disable = false;
        if ($change_number_count >= 2) {
            $this->disable = true;
            $this->getUser()->setFlash('change_number_message', $this->getContext()->getI18N()->__("You can't change your number more than two times in a month."));
        }
    }

    public function executeChangeNumber(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));

        $this->redirectUnless($this->customer, "@homepage");
        $this->targetUrl = $this->getTargetUrl();

        $existingNumber = $this->customer->getMobileNumber();
        $this->newNumber = $request->getParameter('newNumber');
        $newNumber = $this->newNumber;
        $product_id = $request->getParameter('product');
        $this->product = ProductPeer::retrieveByPK($product_id);

        $this->vat = $this->product->getRegistrationFee() * sfConfig::get('app_vat_percentage');
        $this->amount = $this->product->getRegistrationFee() + $this->vat;
        $amount = $this->amount;
        $this->countrycode = sfConfig::get('app_country_code');
        $customer = $this->customer;

        if ($newNumber != "") {
            $ccu = new Criteria();
            $ccu->add(CustomerPeer::MOBILE_NUMBER, $newNumber);
            $ccu->add(CustomerPeer::CUSTOMER_STATUS_ID, 3);
            $ccheck = CustomerPeer::doCount($ccu);
            if ($ccheck > 0) {
                $this->getUser()->setFlash('change_number_message', $this->getContext()->getI18N()->__('This mobile number is already registered to a %1% customer.',array("%1%"=>sfConfig::get("app_site_title"))));
                return $this->redirect('customer/changenumberservice');
            } else {
                $order = new CustomerOrder();
                $order->setCustomerId($customer->getId());
                $order->setProductId($product_id);
                $order->setQuantity(1);
                // $order->setExtraRefill($extra_refill);
                $order->setOrderStatusId(sfConfig::get('app_status_new'));

                $order->save();
                $this->order = $order;
                //create transaction
                $transaction = new Transaction();
                $transaction->setOrderId($order->getId());
                $transaction->setCustomerId($customer->getId());
                $transaction->setAmount($amount);
                $transactiondescription = TransactionDescriptionPeer::retrieveByPK(13);
                $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
                $transaction->setTransactionDescriptionId($transactiondescription->getId());
                $transaction->setDescription($transactiondescription->getTitle());
                $transaction->setVat($this->vat);
                $transaction->save();
            }
        }
    }

    public function executeNumberProcess(sfWebRequest $request) {

        $lang = $this->getUser()->getCulture();

//        $return_url = $this->getTargetUrl()."customer/dashboard";
//        $cancel_url = $this->getTargetUrl()."customer/dashboard";
        
        
          if($lang=='en'){
              
        $return_url ="http://www.kimarin.es/changenumber-payment-thanks.html";
        $cancel_url = "http://www.kimarin.es/changenumber-payment-reject.html";    
          }else{
         $return_url ="http://www.kimarin.es/".$lang."/changenumber-payment-thanks_".$lang.".html";
        $cancel_url = "http://www.kimarin.es/".$lang."/changenumber-payment-reject_".$lang.".html";
          }
        
        $order_id = $request->getParameter('item_number'); 

        $order = CustomerOrderPeer::retrieveByPK($order_id);

        $item_amount = $request->getParameter('amount');
        if ($item_amount == "") {
            $item_amount = number_format($order->getExtraRefill(), 2);
        }
        $callbackparameters = $lang . '-' . $order_id . '-' . $item_amount;

        $notify_url = $this->getTargetUrl() . 'pScripts/CalbackChangeNumber?p=' . $callbackparameters;

        $email2 = new DibsCall();
        $email2->setCallurl($notify_url);

        $email2->save();

        $mobile_number = $request->getParameter('mobile_number');
        $newnumber = $request->getParameter('newnumber');
        $customerid = $order->getCustomerId();

        $changenumberdetail = new ChangeNumberDetail();
        $changenumberdetail->setOldNumber($mobile_number);
        $changenumberdetail->setNewNumber($newnumber);
        $changenumberdetail->setCustomerId($customerid);
        $changenumberdetail->setStatus(0);
        $changenumberdetail->save();

        $querystring = '';

        $ct = new Criteria();
        $ct->add(TransactionPeer::ORDER_ID, $order_id);
        $tCount = TransactionPeer::doCount($ct);
        if ($tCount > 0) {
            $transaction = TransactionPeer::doSelectOne($ct);
            $item_name = $transaction->getDescription();
        } else {
            $item_name = "Fee for change number";
        }


        //loop for posted values and append to querystring
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $querystring .= "$key=$value&";
        }

        $querystring .= "item_name=" . urlencode($item_name) . "&";
        $querystring .= "return=" . urldecode($return_url) . "&";
        $querystring .= "cancel_return=" . urldecode($cancel_url) . "&";
        $querystring .= "notify_url=" . urldecode($notify_url);


        echo $querystring;
        echo "<br />";
        echo $notify_url;
        if ($order_id && $item_amount) {
            Payment::SendPayment($querystring);
        } else {
            echo 'error';
        }
        return sfView::NONE;
        exit();
    }

    public function executeNewcardPur(sfWebRequest $request) {
        $this->price = '';
        $this->product_id = '';
        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', null, 'usersession'));
        $this->redirectUnless($this->customer, "@homepage");
        $this->targetUrl = $this->getTargetUrl();
        $cst = new Criteria();
        $cst->add(ProductPeer::PRODUCT_TYPE_ID, 6);
        $this->simtypes = ProductPeer::doSelect($cst);


        if ($request->getParameter('sim_type') != '') {
            $st = new Criteria();
            $st->add(ProductPeer::ID, $request->getParameter('sim_type'));
            $simtype = ProductPeer::doSelectOne($st);
            $this->product_id = $simtype->getId();
            $this->price = $simtype->getRegistrationFee();
            $this->vat = $this->price * sfConfig::get('app_vat_percentage');
            $this->total = $this->price + $this->vat;
            
            //$product_name=$simtype->getName();

            $this->order = new CustomerOrder();

            $this->order->setProductId($this->product_id);
            $this->order->setCustomer($this->customer);
            $this->order->setQuantity(1);
            $this->order->setExtraRefill(0);
            $this->order->save();

            //new transaction
            $transaction = new Transaction();

            $transaction->setAmount($this->total);
            $transaction->setOrderId($this->order->getId());
            $transaction->setCustomerId($this->order->getCustomerId());

            $transactiondescription = TransactionDescriptionPeer::retrieveByPK(14);
            $transaction->setTransactionTypeId($transactiondescription->getTransactionTypeId());
            $transaction->setTransactionDescriptionId($transactiondescription->getId());
            $this->transaction_title=$transactiondescription->getTitle();
            $transaction->setDescription($this->transaction_title);
            $transaction->setVat($this->vat);
            $transaction->save();

           
        }
        if ($request->getParameter('buy') != '') {

            $order_id = $request->getParameter('item_number');
            $item_amount = $request->getParameter('amount');
            $lang = $this->getUser()->getCulture();
            
            
             if($lang=='en'){
               $return_url ="http://www.kimarin.es/newsim-payment-thanks.html";
                $cancel_url ="http://www.kimarin.es/newsim-payment-reject.html";  
                 
             }else{
               $return_url ="http://www.kimarin.es/".$lang."/newsim-payment-thanks_".$lang.".html"; 
                $cancel_url = "http://www.kimarin.es/".$lang."/newsim-payment-reject_".$lang.".html";
                 
             }
//            $return_url = $this->targetUrl . "customer/dashboard";
//            $cancel_url = $this->targetUrl . "customer/dashboard";


            $callbackparameters = $lang . '-' . $order_id . '-' . $item_amount;
            $notify_url = $this->getTargetUrl() . 'pScripts/calbacknewcard?p=' . $callbackparameters;

            $email2 = new DibsCall();
            $email2->setCallurl($notify_url);

            $email2->save();

            $querystring = '';

            $item_name = $request->getParameter('item_name');

            //loop for posted values and append to querystring
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $querystring .= "$key=$value&";
            }

            $querystring .= "item_name=" . urlencode($item_name) . "&";
            $querystring .= "return=" . urldecode($return_url) . "&";
            $querystring .= "cancel_return=" . urldecode($cancel_url) . "&";
            $querystring .= "notify_url=" . urldecode($notify_url);
            if ($order_id && $item_amount) {
                Payment::SendPayment($querystring);
            } else {
                echo 'error';
            }
        }
        
    }

    public function executeChangeProductSubscription(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));

        $this->redirectUnless($this->customer, "@homepage");
        $this->targetUrl = $this->getTargetUrl();

        $cp = new Criteria();
        $cp->add(CustomerProductPeer::CUSTOMER_ID, $this->customer->getId());
        $cp->addAnd(CustomerProductPeer::STATUS_ID, 3);
        $this->customerProduct = CustomerProductPeer::doSelectOne($cp);

        $c = new Criteria();
        $c->add(CustomerChangeProductPeer::CUSTOMER_ID, $this->customer->getId());
        $c->addAnd(CustomerChangeProductPeer::STATUS, 2);
        $ccpCount = CustomerChangeProductPeer::doCount($c);
    }

    public function executeChangeProductProcess(sfWebRequest $request) {

        $this->customer = CustomerPeer::retrieveByPK($this->getUser()->getAttribute('customer_id', '', 'usersession'));
        $this->redirectUnless($this->customer, "@homepage");
        $this->targetUrl = $this->getTargetUrl();

        $product_id = $request->getParameter('product');
          $this->oldProduct = ProductPeer::retrieveByPK($product_id);

        $product = ProductPeer::retrieveByPK(16);
        $this->product = $product;
        $this->product_id = $product->getId();
        $this->price = $product->getRegistrationFee();
        $this->vat = $this->price * sfConfig::get('app_vat_percentage');
        $this->total = $this->price + $this->vat;

        $order = new CustomerOrder();
        $order->setCustomerId($this->customer->getId());
        $order->setProductId($product->getId());
        $order->setQuantity(1);
        $order->setExtraRefill($product->getInitialBalance());
        $order->setOrderStatusId(1);

        $order->save();
        $this->order = $order;
        //create transaction
        $transaction = new Transaction();
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($this->customer->getId());
        $transaction->setAmount($this->total);
        $transactiondescription = TransactionDescriptionPeer::retrieveByPK(15);
        $transaction->setTransactionTypeId($transactiondescription->getTransactionType());
        $transaction->setTransactionDescriptionId($transactiondescription->getId());
        $transaction->setDescription($transactiondescription->getTitle());
        $transaction->setTransactionStatusId(1);
        $transaction->setVat($this->vat);
        $transaction->save();
        $ccp = new CustomerChangeProduct();
        $ccp->setCustomerId($this->customer->getId());
        $ccp->setProductId($product_id);
        $ccp->setCreatedAt(Date());
        $ccp->setStatus(1);
        $ccp->setOrderId($order->getId());
        $ccp->setTransactionId($transaction->getId());
        $ccp->save();
        $this->ccp = $ccp;
    }

    public function executeChangeNumberProcessPay(sfWebRequest $request) {


        $this->target = $this->getTargetUrl();

        $order_id = $request->getParameter('item_number');
        $item_amount = $request->getParameter('amount');
        $ccpid = $request->getParameter('ccpid');
        $lang = $this->getUser()->getCulture();
        
        
       
       
//        $return_url = $this->target . "customer/dashboard";
//        $cancel_url = $this->target . "customer/dashboard";
        
          if($lang=='en'){
              
        $return_url ="http://www.kimarin.es/changeproduct-thanks.html";
        $cancel_url = "http://www.kimarin.es/changeproduct-reject.html";    
          }else{
         $return_url ="http://www.kimarin.es/".$lang."/changeproduct-thanks_".$lang.".html";
        $cancel_url = "http://www.kimarin.es/".$lang."/changeproduct-reject_".$lang.".html";
          }
        $callbackparameters = $lang . '-' . $order_id . '-' . $item_amount . '-' . $ccpid;
        $notify_url = $this->getTargetUrl() . 'pScripts/calbackChangeProduct?p=' . $callbackparameters;

        $email2 = new DibsCall();
        $email2->setCallurl($notify_url);

        $email2->save();

        $querystring = '';

        $item_name = "Change Product";



        //loop for posted values and append to querystring
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $querystring .= "$key=$value&";
        }

        $querystring .= "item_name=" . urlencode($item_name) . "&";
        $querystring .= "return=" . urldecode($return_url) . "&";
        $querystring .= "cancel_return=" . urldecode($cancel_url) . "&";
        $querystring .= "notify_url=" . urldecode($notify_url);
        if ($order_id && $item_amount) {
            Payment::SendPayment($querystring);
        } else {
            echo 'error';
        }
        return sfView::NONE;
    }

}
