<?php

require_once(sfConfig::get('sf_lib_dir') . '/changeLanguageCulture.php');

class emailLib {

    public static function sendAgentRefilEmail(AgentCompany $agent, $agent_order) {
        $vat = 0;

        //create transaction
        //This Section For Get The Agent Information
        $agent_company_id = $agent->getId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        //$this->renderPartial('affiliate/order_receipt', array(
        $agentamount = $agent_order->getAmount();
        $createddate = $agent_order->getCreatedAt('d-m-Y');
        $agentid = $agent_order->getAgentOrderId();
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/agent_order_receipt', array(
                    'order' => $agentid,
                    'transaction' => $agentamount,
                    'createddate' => $createddate,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                    'agent' => $agent
                ));


        $subject = __('Agent Payment Confirmation');


        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');
        
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //--------------Sent The Email To Kimarin order email
        if (trim($sender_email_orders) != ''):
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email1->setMessage($message_body);
            $email1->save();
        endif;
        //-----------------------------------------
        
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To cdu
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
        
        
    }

    public static function sendRefillEmail(Customer $customer, $order) {
       

        //create transaction
//        $transaction = new Transaction();
//        $transaction->setOrderId($order->getId());
//        $transaction->setCustomer($customer);
//        $transaction->setAmount($order->getExtraRefill());


        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);
        $vat = $transaction->getAmount() - ($transaction->getAmount()/(sfConfig::get('app_vat_percentage')+1));
        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('affiliate/refill_order_receipt', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Payment Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin order email
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($agent_company_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To cdu
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendCustomerRegistrationViaAgentEmail(Customer $customer, $order) {


        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);


        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        $vat = $order->getProduct()->getRegistrationFee() * sfConfig::get('app_vat_percentage');
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('affiliate/order_receipt', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Payment Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin order email
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via agent');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($agent_company_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via agent');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via agent');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To Okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via agent');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via agent');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendForgetPasswordEmail(Customer $customer, $message_body, $subject) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        // $subject = __("Request for password");
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        //Support Information
        //$sender_email = sfConfig::get('app_email_sender_email', 'support@Zapna.com');
        //$sender_name = sfConfig::get('app_email_sender_name', 'Zapna support');

        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setCutomerId($customer_id);
            $email->setAgentId($referrer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Forget Password');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
    }

    public static function sendCustomerRefillEmail(Customer $customer, $order, $transaction) {

        //set vat
        

        $vat = $transaction->getAmount() - ($transaction->getAmount()/(sfConfig::get('app_vat_percentage')+1));
        $subject = __('Payment Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        if ($referrer_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('payments/refill_order_receipt', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));


        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin Order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($referrer_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer Registration');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($referrer_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer Registration');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($referrer_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer Registration');
            $email2->setMessage($message_body);
            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' refill via agent');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendCustomerAutoRefillEmail(Customer $customer, $message_body) {

        $subject = __('Payment Confirmation');

        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");

        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());
        
        //send to email to Kimain order 
        if (trim($sender_email_orders) != ''):
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setMessage($message_body);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setReceipientName($sender_name_orders);
            $email1->setCutomerId($customer_id);
            $email1->setAgentId($referrer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer Auto Refill');
            $email1->save();
        endif;
        
        
        //send to user
        if (trim($recepient_email) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setMessage($message_body);
            $email->setReceipientEmail($recepient_email);
            $email->setReceipientName($recepient_name);
            $email->setCutomerId($customer_id);
            $email->setAgentId($referrer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer Auto Refill');

            $email->save();
        endif;

        //send to OKHAN
        if (trim($sender_email) != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setMessage($message_body);
            $email2->setReceipientEmail($sender_email);
            $email2->setReceipientName($sender_name);
            $email2->setCutomerId($customer_id);
            $email2->setAgentId($referrer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer Auto Refill');
            $email2->save();
        endif;
////////////////////////////////////////////////////////
        //send to CDU
        if (trim($sender_emailcdu) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setMessage($message_body);
            $email3->setReceipientEmail($sender_emailcdu);
            $email3->setReceipientName($sender_namecdu);
            $email3->setCutomerId($customer_id);
            $email3->setAgentId($referrer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Customer Auto Refill');
            $email3->save();
        endif;
    }

    public static function sendCustomerConfirmPaymentEmail(Customer $customer, $message_body) {


        $subject = __('Payment Confirmation');

        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        //send to Kimarin order 
        if (trim($sender_email_orders) != ''):
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setMessage($message_body);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setReceipientName($sender_name_orders);
            $email1->setCutomerId($customer_id);
            $email1->setAgentId($referrer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Payment');

            $email1->save();
        endif;
        
        //send to user
        if (trim($recepient_email) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setMessage($message_body);
            $email->setReceipientEmail($recepient_email);
            $email->setReceipientName($recepient_name);
            $email->setCutomerId($customer_id);
            $email->setAgentId($referrer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Payment');

            $email->save();
        endif;

        //send to okhan
        if (trim($sender_email) != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setMessage($message_body);
            $email2->setReceipientEmail($sender_email);
            $email2->setReceipientName($sender_name);
            $email2->setCutomerId($customer_id);
            $email2->setAgentId($referrer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Payment');
            $email2->save();
        endif;
        //send to cdu
        if (trim($sender_emailcdu) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setMessage($message_body);
            $email3->setReceipientEmail($sender_emailcdu);
            $email3->setReceipientName($sender_namecdu);
            $email3->setCutomerId($customer_id);
            $email3->setAgentId($referrer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Payment');
            $email3->save();
        endif;
    }

    public static function sendCustomerConfirmRegistrationEmail($inviteuserid, $customerr, $subject=null,$order,$transaction) {

        $c = new Criteria();
        $c->add(CustomerPeer::ID, $inviteuserid);        
        $customer = CustomerPeer::doSelectOne($c);
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $sender_name = sfConfig::get('app_email_sender_name_sup');
        $sender_email = sfConfig::get('app_email_sender_email_sup');
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        $registered_customer_name = sprintf('%s %s', $customerr->getFirstName(), $customerr->getLastName());
        
        $vat=0;
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/bonus_web_reg', array(
                    'customer' => $customer,
                    'recepient_name' => $recepient_name,
                    'registered_customer_name' => $registered_customer_name,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,                   
                    'wrap' => true,
                ));
        $subject =__('Bonus awarded');

        //send to Kimarin order
        if ($sender_email_orders != ''):
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setMessage($message_body);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setReceipientName($sender_name_orders);
            $email1->setCutomerId($customer_id);
            //$email->setAgentId($referrer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Bonus');

            $email1->save();
        endif;
        
        //send to user
        if ($recepient_email != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setMessage($message_body);
            $email->setReceipientEmail($recepient_email);
            $email->setReceipientName($recepient_name);
            $email->setCutomerId($customer_id);
            //$email->setAgentId($referrer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Bonus');

            $email->save();
        endif;

        //send to okhan
        if ($sender_email != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setMessage($message_body);
            $email2->setReceipientEmail($sender_email);
            $email2->setReceipientName($sender_name);
            $email2->setCutomerId($customer_id);
            //$email2->setAgentId($referrer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Bonus');
            $email2->save();
        endif;
        //////////////////////////////////////////////////////////////////
        if ($sender_emailcdu != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setMessage($message_body);
            $email3->setReceipientName($sender_namecdu);
            $email3->setReceipientEmail($sender_emailcdu);
            $email3->setCutomerId($customer_id);
            //$email3->setAgentId($referrer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Customer Confirm Bonus');
            $email3->save();
        endif;
    }

//////////////////////////////////////////////////////////////

    public static function sendCustomerRegistrationViaWebEmail(Customer $customer, $order) {


        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);


        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        $lang = sfConfig::get('app_language_symbol');
        // $this->lang = $lang;

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

        $vat = ($order->getProduct()->getRegistrationFee() + $postalcharge) * sfConfig::get('app_vat_percentage');

        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/order_receipt_web_reg', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'postalcharge' => $postalcharge,
                    'wrap' => true,
                ));


        $subject = __('Registration Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin order
        if ($sender_email_orders != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via link');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
       //------------------Sent The Email To Customer
        if ($recepient_email != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via link');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        //--------------Sent The Email To Support
        if ($sender_email != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via link');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To Support
        if ($sender_emailcdu != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via link');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    ///////////////////////////////////////////////////////////

    public static function sendCustomerRegistrationViaAgentSMSEmail(Customer $customer, $order) {


        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);
        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        $vat = ($order->getProduct()->getRegistrationFee()) * sfConfig::get('app_vat_percentage');
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/order_receipt_sms', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Registration  Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent the Email To Kimarin order

        if (trim($sender_email_orders) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($sender_name_orders);
            $email2->setReceipientEmail($sender_email_orders);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType('Customer registration via agent SMS ');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        
        //------------------Sent the Email To Agent

        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType('Customer registration via agent SMS ');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        
        //--------------Sent The Email To Okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Customer registration via agent SMS ');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Customer registration via agent SMS ');

            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendCustomerRegistrationViaAgentAPPEmail(Customer $customer, $order) {

        echo 'sending email';
        echo '<br/>';
        $product_price = $order->getProduct()->getPrice() - $order->getExtraRefill();
        echo $product_price;
        echo '<br/>';
        $vat = .20 * $product_price;
        echo $vat;
        echo '<br/>';

//        //create transaction
//        $transaction = new Transaction();
//        $transaction->setOrderId($order->getId());
//        $transaction->setCustomer($customer);
//        $transaction->setAmount($form['extra_refill']);

        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);


        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/order_receipt_sms', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Registration Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");

        //------------------Sent The Email To Kimarin order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via APP');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($agent_company_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via APP');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via APP');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To Okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via APP');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' Customer registration via APP');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendvoipemail(Customer $customer, $order, $transaction) {

        //set vat
        $vat = 0;
        $subject = 'Bekräftelse - nytt resenummer frän ' . sfConfig::get('app_site_title');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        if ($referrer_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
//        $message_body = get_partial('payments/order_receipt', array(
//                'customer'=>$customer,
//                'order'=>$order,
//                'transaction'=>$transaction,
//                'vat'=>$vat,
//                'agent_name'=>$recepient_agent_name,
//                'wrap'=>false,
//        ));
        // Please remove the receipt that is sent out when activating
        $getvoipInfo = new Criteria();
        $getvoipInfo->add(SeVoipNumberPeer::CUSTOMER_ID, $customer->getMobileNumber());
        $getvoipInfos = SeVoipNumberPeer::doSelectOne($getvoipInfo); //->getId();
        if (isset($getvoipInfos)) {
            $voipnumbers = $getvoipInfos->getNumber();
            $voip_customer = $getvoipInfos->getCustomerId();
        } else {
            $voipnumbers = '';
            $voip_customer = '';
        }



        $message_body = "<table width='600px'><tr style='border:0px solid #fff'><td colspan='4' align='right' style='text-align:right; border:0px solid #fff'>" . image_tag('https://wls2.zerocall.com/images/zapna_logo_small.jpg', array('width' => '170')) . "</tr></table><table cellspacing='0' width='600px'><tr><td>Grattis till ditt nya resenummer. Detta nummer är alltid kopplat till den telefon där du har Smartsim aktiverat. Med resenumret blir du nådd utomlands då du har ett lokalt SIM-kort. Se prislistan för hur mycket det kostar att ta emot samtal.
Ditt resenummer är $voipnumbers.<br/><br/>
Med vänlig hälsning<br/><br/>
" . sfConfig::get('app_site_title') . "<br/><a href='" . sfConfig::get('app_site_url') . "'>" . sfConfig::get('app_site_url') . "</a></td></tr></table>";

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($referrer_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType('Transation for VoIP Purchase');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):
//            $email2 = new EmailQueue();
//            $email2->setSubject($subject);
//            $email2->setReceipientName($recepient_agent_name);
//            $email2->setReceipientEmail($recepient_agent_email);
//            $email2->setAgentId($referrer_id);
//            $email2->setCutomerId($customer_id);
//            $email2->setEmailType('Transation for VoIP Purchase');
//            $email2->setMessage($message_body);
//            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Transation for VoIP Purchase');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Transation for VoIP Purchase');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendCustomerBalanceEmail(Customer $customer, $message_body) {

        $subject = ' Balance Email ';
        $recepient_name = '';
        $recepient_email = '';

        $recepient_name = $customer->getFirstName() . ' ' . $customer->getLastName();
        $recepient_email = $customer->getEmail();
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        if (trim($recepient_email) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setMessage($message_body);
            $email->setReceipientEmail($recepient_email);
            $email->setCutomerId($customer_id);
            $email->setAgentId($referrer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer Balance');
            $email->setReceipientName($recepient_name);
            $email->save();
        endif;
    }

    public static function sendErrorTelinta(Customer $customer, $message) {

        $subject = 'Error In Telinta';
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = "<table width='600px'><tr style='border:0px solid #fff'><td colspan='4' align='right' style='text-align:right; border:0px solid #fff'></tr></table><table cellspacing='0' width='600px'><tr><td>
             " . $message . " <br/><br/>
Med vänlig hälsning<br/><br/>
" . sfConfig::get('app_site_title') . "<br/><a href='" . sfConfig::get('app_site_url') . "'>" . sfConfig::get('app_site_url') . "</a></td></tr></table>";

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Error In Telinta');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Error In Telinta');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendUniqueIdsShortage() {

        $subject = 'Unique Ids finished.';
        $message_body = "<table cellspacing='0' width='600px'><tr><td>Uniuqe Ids finsihed.<br/><br/>
                        " . sfConfig::get('app_site_title') . "<br/><a href='" . sfConfig::get('app_site_url') . "'>" . sfConfig::get('app_site_url') . "</a>
                            </td></tr></table>";

        $recipient_name_rs = sfConfig::get('app_email_sender_name_cdu');
        $recipient_email_rs = sfConfig::get('app_email_sender_email_cdu');

        $recipient_name_support = sfConfig::get('app_recipient_name_support');
        $recipient_email_support = sfConfig::get('app_recipient_email_support');

        //********************Sent The Email To RS******************************
        if (trim($recipient_email_rs) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recipient_name_rs);
            $email->setReceipientEmail($recipient_email_rs);
            $email->setEmailType('Unique Ids Finished');
            $email->setMessage($message_body);
            $email->save();
        endif;
        //**********************************************************************

        //********************Sent The Email To Support*************************
        if (trim($recipient_email_support) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recipient_name_support);
            $email->setReceipientEmail($recipient_email_support);
            $email->setEmailType('Unique Ids Finished');
            $email->setMessage($message_body);
            $email->save();
        endif;
        //**********************************************************************
    }

    public static function sendUniqueIdsIssueAgent($uniqueid, Customer $customer) {

        $subject = 'Unique Ids finished.';
        $message_body = "<table width='600px'><tr style='border:0px solid #fff'><td colspan='4' align='right' style='text-align:right; border:0px solid #fff'></tr></table><table cellspacing='0' width='600px'><tr><td>
             " . $message . " <br/><br/>
Uniuqe Id " . $uniqueid . " has issue while assigning on " . $customer->getMobileNumber() . "<br/><br/>
" . sfConfig::get('app_site_title') . "<br/><a href='" . sfConfig::get('app_site_url') . "'>" . sfConfig::get('app_site_url') . "</a></td></tr></table>";

        //Support Informationt
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        //$sender_emailcdu = sfConfig::get('app_email_sender_email', 'zerocallengineering@googlegroups.com');
        
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Unique Ids Finished');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Unique Ids Finished');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendUniqueIdsIssueSmsReg($uniqueid, Customer $customer) {

        $subject = 'Unique Ids finished.';
        $message_body = "<table width='600px'><tr style='border:0px solid #fff'><td colspan='4' align='right' style='text-align:right; border:0px solid #fff'></tr></table><table cellspacing='0' width='600px'><tr><td>
             " . $message . " <br/><br/>
Uniuqe Id " . $uniqueid . " has issue while assigning on " . $customer->getMobileNumber() . " in sms registration<br/><br/>
" . sfConfig::get('app_site_title') . "<br/><a href='" . sfConfig::get('app_site_url') . "'>" . sfConfig::get('app_site_url') . "</a></td></tr></table>";

        //Support Informationt
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        //$sender_emailcdu = sfConfig::get('app_email_sender_email', 'zerocallengineering@googlegroups.com');
        


        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Unique Ids Finished');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Unique Ids Finished');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendErrorInTelinta($subject, $message) {

        $recipient_name_rs = sfConfig::get('app_email_sender_name_cdu');
        $recipient_email_rs = sfConfig::get('app_email_sender_email_cdu');

        $recipient_name_support = sfConfig::get('app_recipient_name_support');
        $recipient_email_support = sfConfig::get('app_recipient_email_support');

        //********************Sent The Email To RS******************************
        if (trim($recipient_email_rs) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recipient_name_rs);
            $email->setReceipientEmail($recipient_email_rs);
            $email->setEmailType('Telinta Error');
            $email->setMessage($message);
            $email->save();
        endif;
        //**********************************************************************

        //********************Sent The Email To Support*************************
        if (trim($recipient_email_support) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recipient_name_support);
            $email->setReceipientEmail($recipient_email_support);
            $email->setEmailType('Telinta Error');
            $email->setMessage($message);
            $email->save();
        endif;
        //**********************************************************************
    }

    public static function sendAdminRefilEmail(AgentCompany $agent, $agent_order) {
        $vat = 0;

        //create transaction
        //This Section For Get The Agent Information
        $agent_company_id = $agent->getId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        //$this->renderPartial('affiliate/order_receipt', array(
        $agentamount = $agent_order->getAmount();
        $createddate = $agent_order->getCreatedAt('d-m-Y');
        $agentid = $agent_order->getAgentOrderId();
        $order_des = $agent_order->getOrderDescription();
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('agent_company/agent_order_receipt', array(
                    'order' => $agentid,
                    'transaction' => $agentamount,
                    'createddate' => $createddate,
                    'description' => $order_des,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                    'agent' => $agent
                ));


        $subject = __('Agent Payment Confirmation');


        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name', sfConfig::get('app_site_title'));
        $sender_email = sfConfig::get('app_email_sender_email');
        

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu', sfConfig::get('app_site_title'));
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");

        //------------------Sent the Email To Kimarin order
        if (trim($sender_email_orders) != ''):

            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Agent refill via admin');
            $email1->setMessage($message_body);

            $email1->save();
        endif;
        //---------------------------------------
        
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Agent refill via admin');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Agent refill via admin');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To cdu
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' Agent refill via admin');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendChangeNumberEmail(Customer $customer, $order) {
        $vat = 0;

        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);

        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('affiliate/change_number_order_receipt', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Change Number Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
         //------------------Sent The Email To Kimarin order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType('Change Number');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------

        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($agent_company_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType('Change Number');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType('Change Number');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Change Number ');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To cdu
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Change Number');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

    public static function sendAdminRefillEmail(Customer $customer, $order) {
        $vat = 0;


        if ($order) {
            $vat = $order->getIsFirstOrder()==1 ?
                    ($order->getProduct()->getPrice() * $order->getQuantity() -
                    $order->getProduct()->getInitialBalance()) * .20 :
                    0;
        }
        //create transaction
        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);
        //if(strstr($transaction->getDescription(),"Refill") || strstr($transaction->getDescription(),"Charge")){
        //if(strstr($transaction->getDescription(),"Refill")){
         $vat = $transaction->getAmount() - ($transaction->getAmount()/(sfConfig::get('app_vat_percentage')+1));
        //}
        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('customer/order_receipt_simple', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Payment Confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');
        
        $sender_name_sup = sfConfig::get('app_email_sender_name_sup');
        $sender_email_sup = sfConfig::get('app_email_sender_email_sup');
        
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        $sender_name_rs = "Zapna Support";
        $sender_email_rs = "rs@zapna.com";
        
        //------------------Sent The Email To Kimarin Order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' refill/charge via admin');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
      
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($agent_company_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' refill/charge via admin');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Refill/charge via admin');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . ' Refill/charge via admin');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To cdu
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . ' Refill/charge via admin');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
       
        
        
    }


    public static function sendCustomerRegistrationViaRetail(Customer $customer, $order) {
        $product_price = $order->getProduct()->getPrice() - $order->getExtraRefill();
        $vat = sfConfig::get("app_vat_percentage") * $product_price;



        $tc = new Criteria();
        $tc->add(TransactionPeer::ORDER_ID, $order->getId());
        $transaction = TransactionPeer::doSelectOne($tc);


        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/order_receipt_sms', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Payment Confirmation');



        $sender_email = sfConfig::get('app_email_sender_email', 'okhan@zapna.com');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu', 'rs@zapna.com');
        $sender_name = sfConfig::get('app_email_sender_name', 'Kimarin');
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu', 'Kimarin');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        /// Email to Kimarin order
        if($sender_email_orders !=""){
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setEmailType('Retail Activation');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //---------------------------------------
        //--------------Sent The Email To Support

        $email3 = new EmailQueue();
        $email3->setSubject($subject);
        $email3->setReceipientName($sender_name);
        $email3->setReceipientEmail($sender_email);
        $email3->setEmailType('Retail Activation');
        $email3->setMessage($message_body);
        $email3->save();

        $email3 = new EmailQueue();
        $email3->setSubject($subject);
        $email3->setReceipientName($sender_namecdu);
        $email3->setReceipientEmail($sender_emailcdu);
        $email3->setEmailType('Retail Activation');
        $email3->setMessage($message_body);
        $email3->save();
        //-----------------------------------------
    }

        public static function sendErrorInAutoReg($subject, $message) {

        $recipient_name_rs = sfConfig::get('app_email_sender_name_cdu');
        $recipient_email_rs = sfConfig::get('app_email_sender_email_cdu');

        $recipient_name_support = sfConfig::get('app_recipient_name_support');
        $recipient_email_support = sfConfig::get('app_recipient_email_support');

        //********************Sent The Email To RS******************************
        if (trim($recipient_email_rs) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recipient_name_rs);
            $email->setReceipientEmail($recipient_email_rs);
            $email->setEmailType('Auto Registration Error');
            $email->setMessage($message);
            $email->save();
        endif;
        //**********************************************************************

        //********************Sent The Email To Support*************************
        if (trim($recipient_email_support) != ''):
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recipient_name_support);
            $email->setReceipientEmail($recipient_email_support);
            $email->setEmailType('Auto Registration Error');
            $email->setMessage($message);
            $email->save();
        endif;
        //**********************************************************************

    }

    public static function sendRetailRefillEmail(Customer $customer, $order) {
        $vat = 0;

        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);

        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/order_receipt_sms', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Payment Confirmation');
        $sender_email = sfConfig::get('app_email_sender_email', 'okhan@zapna.com');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu', 'rs@zapna.com');
        $sender_name = sfConfig::get('app_email_sender_name', 'Kimarin');
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu', 'Kimarin');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        if($sender_email_orders !=""){
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setEmailType('Retail Refil');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //---------------------------------------
        //--------------Sent The Email To Support

        $email3 = new EmailQueue();
        $email3->setSubject($subject);
        $email3->setReceipientName($sender_name);
        $email3->setReceipientEmail($sender_email);
        $email3->setEmailType('Retail Refil');
        $email3->setMessage($message_body);
        $email3->save();

        $email3 = new EmailQueue();
        $email3->setSubject($subject);
        $email3->setReceipientName($sender_namecdu);
        $email3->setReceipientEmail($sender_emailcdu);
        $email3->setEmailType('Retail Refil');
        $email3->setMessage($message_body);
        $email3->save();


        //-----------------------------------------

    }

    public static function sendCustomerNewcardEmail(Customer $customer, $order, $transaction) {

        
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());
        if ($referrer_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/newcard_receipt', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    /*'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,*/
                ));

        $subject = __('New SIM-card confirmation');
        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');

        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($referrer_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType('New Sim Card Purchase');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($referrer_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType('New Sim Card Purchase');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        
        //------------------Sent the Email To Agent
        /*if (trim($recepient_agent_email) != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($referrer_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType('New Sim Card Purchase');
            $email2->setMessage($message_body);
            $email2->save();
        endif;*/
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('New Sim Card Purchase');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('New Sim Card Purchase');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------

    }
    
    public static function sendCustomerChangeNumberEmail(Customer $customer, $order) {
        
        $vat = $order->getProduct()->getRegistrationFee() * sfConfig::get('app_vat_percentage');

        $tc = new Criteria();
        $tc->add(TransactionPeer::CUSTOMER_ID, $customer->getId());
        $tc->addDescendingOrderByColumn(TransactionPeer::CREATED_AT);
        $transaction = TransactionPeer::doSelectOne($tc);

        //This Section For Get The Agent Information
        $agent_company_id = $customer->getReferrerId();
        if ($agent_company_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $agent_company_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('pScripts/change_number_order_receipt', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Change number - payment confirmation');
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());

        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($agent_company_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType('Change number');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($agent_company_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType('Change number');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):

            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($agent_company_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType('Change number');
            $email2->setMessage($message_body);

            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($agent_company_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType('Change number');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To cdu
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($agent_company_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType('Change number');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
        //-----------------------------------------
    }

  
    public static function sendCustomerChangeProduct(Customer $customer, $order,$transaction) {
     
        $vat = $transaction->getAmount() - ($transaction->getAmount()/(sfConfig::get('app_vat_percentage')+1));
    
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        if ($referrer_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('payments/order_receipt_payment', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Change product - payment confirmation');
        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');

        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        $sender_namers = sfConfig::get('app_email_sender_name_sup');
        $sender_emailrs = sfConfig::get('app_email_sender_email_sup');
        
        $sender_name_orders = sfConfig::get("app_email_sender_name_order");
        $sender_email_orders = sfConfig::get("app_email_sender_email_order");
        
        //------------------Sent The Email To Kimarin order
        if (trim($sender_email_orders) != '') {
            $email1 = new EmailQueue();
            $email1->setSubject($subject);
            $email1->setReceipientName($sender_name_orders);
            $email1->setReceipientEmail($sender_email_orders);
            $email1->setAgentId($referrer_id);
            $email1->setCutomerId($customer_id);
            $email1->setEmailType(sfConfig::get('app_site_title') . ' Customer Change Product');
            $email1->setMessage($message_body);
            $email1->save();
        }
        //----------------------------------------
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($referrer_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . ' Customer Change Product');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($referrer_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . ' Customer  Change Product');
            $email2->setMessage($message_body);
            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . 'Customer  Change Product');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . 'Customer  Change Product');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
          if (trim($sender_emailrs) != ''):
            $email5 = new EmailQueue();
            $email5->setSubject($subject);
            $email5->setReceipientName($sender_namers);
            $email5->setReceipientEmail($sender_emailrs);
            $email5->setAgentId($referrer_id);
            $email5->setCutomerId($customer_id);
            $email5->setEmailType(sfConfig::get('app_site_title') . 'Customer  Change Product');
            $email5->setMessage($message_body);
            $email5->save();
        endif;
    }
  
    
    
   
    public static function sendCustomerChangeProductConfirm(Customer $customer, $order,$transaction) {
     
        $vat = $transaction->getAmount() - ($transaction->getAmount()/(sfConfig::get('app_vat_percentage')+1));
    
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());

        if ($referrer_id != '') {
            $c = new Criteria();
            $c->add(AgentCompanyPeer::ID, $referrer_id);
            $recepient_agent_email = AgentCompanyPeer::doSelectOne($c)->getEmail();
            $recepient_agent_name = AgentCompanyPeer::doSelectOne($c)->getName();
        } else {
            $recepient_agent_email = '';
            $recepient_agent_name = '';
        }

        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('payments/order_receipt_product_change', array(
                    'customer' => $customer,
                    'order' => $order,
                    'transaction' => $transaction,
                    'vat' => $vat,
                    'agent_name' => $recepient_agent_name,
                    'wrap' => false,
                ));

        $subject = __('Confirmation of product change');
        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
        $sender_namers = sfConfig::get('app_email_sender_name_sup');
        $sender_emailrs = sfConfig::get('app_email_sender_email_sup');
        
        
        //------------------Sent the Email To Agent
        if (trim($recepient_agent_email) != ''):
            $email2 = new EmailQueue();
            $email2->setSubject($subject);
            $email2->setReceipientName($recepient_agent_name);
            $email2->setReceipientEmail($recepient_agent_email);
            $email2->setAgentId($referrer_id);
            $email2->setCutomerId($customer_id);
            $email2->setEmailType(sfConfig::get('app_site_title') . 'Confirmation of product change');
            $email2->setMessage($message_body);
            $email2->save();
        endif;
        //---------------------------------------
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . 'Confirmation of product change');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . 'Confirmation of product change');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
          if (trim($sender_emailrs) != ''):
            $email5 = new EmailQueue();
            $email5->setSubject($subject);
            $email5->setReceipientName($sender_namers);
            $email5->setReceipientEmail($sender_emailrs);
            $email5->setAgentId($referrer_id);
            $email5->setCutomerId($customer_id);
            $email5->setEmailType(sfConfig::get('app_site_title') . 'Confirmation of product change');
            $email5->setMessage($message_body);
            $email5->save();
        endif;
    }
  
    
    
    
   
    public static function sendBlockCustomerEmail(Customer $customer) {
     
        
        $recepient_email = trim($customer->getEmail());
        $recepient_name = sprintf('%s %s', $customer->getFirstName(), $customer->getLastName());
        $customer_id = trim($customer->getId());
        $referrer_id = trim($customer->getReferrerId());
 
        //$this->renderPartial('affiliate/order_receipt', array(
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
        $message_body = get_partial('customer/block_customer', array(
                    'customer' => $customer,
                    'wrap' => false,
                ));

        $subject = __('Block account');
        //Support Information
        $sender_name = sfConfig::get('app_email_sender_name');
        $sender_email = sfConfig::get('app_email_sender_email');
        $sender_namecdu = sfConfig::get('app_email_sender_name_cdu');
        $sender_emailcdu = sfConfig::get('app_email_sender_email_cdu');
           $sender_namers = sfConfig::get('app_email_sender_name_sup');
        $sender_emailrs = sfConfig::get('app_email_sender_email_sup');
        
        //------------------Sent The Email To Customer
        if (trim($recepient_email) != '') {
            $email = new EmailQueue();
            $email->setSubject($subject);
            $email->setReceipientName($recepient_name);
            $email->setReceipientEmail($recepient_email);
            $email->setAgentId($referrer_id);
            $email->setCutomerId($customer_id);
            $email->setEmailType(sfConfig::get('app_site_title') . 'Block account');
            $email->setMessage($message_body);
            $email->save();
        }
        //----------------------------------------
        
        //--------------Sent The Email To okhan
        if (trim($sender_email) != ''):
            $email3 = new EmailQueue();
            $email3->setSubject($subject);
            $email3->setReceipientName($sender_name);
            $email3->setReceipientEmail($sender_email);
            $email3->setAgentId($referrer_id);
            $email3->setCutomerId($customer_id);
            $email3->setEmailType(sfConfig::get('app_site_title') . 'Block account');
            $email3->setMessage($message_body);
            $email3->save();
        endif;
        //-----------------------------------------
        //--------------Sent The Email To CDU
        if (trim($sender_emailcdu) != ''):
            $email4 = new EmailQueue();
            $email4->setSubject($subject);
            $email4->setReceipientName($sender_namecdu);
            $email4->setReceipientEmail($sender_emailcdu);
            $email4->setAgentId($referrer_id);
            $email4->setCutomerId($customer_id);
            $email4->setEmailType(sfConfig::get('app_site_title') . 'Block account');
            $email4->setMessage($message_body);
            $email4->save();
        endif;
          if (trim($sender_emailrs) != ''):
            $email5 = new EmailQueue();
            $email5->setSubject($subject);
            $email5->setReceipientName($sender_namers);
            $email5->setReceipientEmail($sender_emailrs);
            $email5->setAgentId($referrer_id);
            $email5->setCutomerId($customer_id);
            $email5->setEmailType(sfConfig::get('app_site_title') . 'Block account');
            $email5->setMessage($message_body);
            $email5->save();
        endif;
    }
  
       
    
    
}

?>