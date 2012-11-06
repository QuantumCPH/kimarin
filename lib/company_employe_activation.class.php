<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of company_employe_activation
 *
 * @author baran
 */
set_time_limit(10000000);

class CompanyEmployeActivation {

    //put your code here
    //put your code here
    private $iParent;                //Company Resller ID on Telinta
    private $currency;
    private $a_iProduct;
    private $CBProduct;
    private $VoipProduct;
    private $telintaSOAPUrl;
    //public $telintaSOAPUser = 'API_login';
    //public $telintaSOAPPassword = 'ee4eriny';

    public function __construct() {
        $this->iParent        = sfConfig::get("app_telinta_b2b_reseller");
        $this->currency       = sfConfig::get("app_telinta_currency");
        $this->telintaSOAPUrl = sfConfig::get("app_telinta_soap_uri");
        $this->CBProduct      = sfConfig::get("app_telinta_cb_product");
        $this->VoipProduct    = sfConfig::get("app_telinta_voip_product");
    }
    public function telintaRegisterCompany(Company $company) {
        $tCustomer = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Customer');
        
        $vatNumber = "KB2B" . $company->getVatNo();

        while (!$tCustomer && $retry_count < $max_retries) {
            try {
                $tCustomer = $pb->add_customer(array('customer_info' => array(
                                'name' => $vatNumber, //75583 03344090514
                                'iso_4217' => $this->currency,
                                'i_parent' => $this->iParent,
                                'i_customer_type' => 1,
                                'opening_balance' => 0,
                                'credit_limit' => '5000',
                                'dialing_rules' => array('ip' => '00'),
                                'email' => 'okh@zapna.com'
                                )));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Error in Company Registration", "We have faced an issue in Company registration on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Error in Company Registration", "We have faced an issue in Company registration on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        $company->setICustomer($tCustomer->i_customer);
        //$company->save();
        
        return true;
    }

    public function telintaRegisterEmployeeCT($employeMobileNumber, Company $company,$productId) {
        $c = new Criteria();
        $c->addAnd(ProductPeer::ID, $productId);
        $product = ProductPeer::doSelectOne($c);
        $product = BillingProductsPeer::retrieveByPK($product->getBillingProductId());
        return $this->createAccount($company, $employeMobileNumber, 'a', $product->getAIproduct());
    }

    public function telintaRegisterEmployeeCB($employeMobileNumber, Company $company) {

        return $this->createAccount($company, $employeMobileNumber, 'cb', $this->CBProduct);
    }

    public function createReseNumberAccount($VOIPNumber, Company $company, $currentActiveNumber) {

        if ($this->createAccount($company, $VOIPNumber, '', $this->VoipProduct, 'Y')) {

            $accounts = false;
            $max_retries = 10;
            $retry_count = 0;

            $ct = new Criteria();
            $ct->add(TelintaAccountsPeer::ACCOUNT_TITLE, $VOIPNumber);
            $ct->addAnd(TelintaAccountsPeer::STATUS, 3);
            $telintaAccount = TelintaAccountsPeer::doSelectOne($ct);
            $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Account');
            
            while (!$accounts && $retry_count < $max_retries) {
                try {
                    $accounts = $pb->update_account_followme(array('i_account' => $telintaAccount->getIAccount(),
                                "followme_info" => array(
                                    'i_account' => $telintaAccount->getIAccount(),
                                    'period' => '',
                                    'sequence' => 'Order',
                                    'timeout' => 30
                                    )));
                } catch (SoapFault $e) {
                    if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                        emailLib::sendErrorInTelinta("Account update_account_followme: " . $telintaAccount->getIAccount() . " Error!", "We have faced an issue in Customer Account update_account_followme on telinta. this is the error for cusotmer with  id: " . $company->getId() . " error is " . $e->faultstring . ".  <br/> Please Investigate.");
                        
                        return false;
                    }
                }


                sleep(0.5);
                $retry_count++;
            }

            if ($retry_count == $max_retries) {
                emailLib::sendErrorInTelinta("Account update_account_followme: " . $telintaAccount->getIAccount() . " Error!", "We have faced an issue in Customer Account update_account_followme on telinta. Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
                return false;
            }
            
            $accounts = false;
            $max_retries = 10;
            $retry_count = 0;
            while (!$accounts && $retry_count < $max_retries) {
                try {
                    $accounts = $pb->add_followme_number(array('i_account' => $telintaAccount->getIAccount(),
                                'number_info' => array(
                                    'i_account' => $telintaAccount->getIAccount(),
                                    'name' => $currentActiveNumber,
                                    'redirect_number' => $currentActiveNumber,
                                    'period' => 'always',
                                    'period_description' => 'Always',
                                    'active' => 'Y',
                                    'timeout' => 15
                                )
                            ));
                } catch (SoapFault $e) {
                    if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                        emailLib::sendErrorInTelinta("Account add_followme_number: " . $telintaAccount->getIAccount() . " Error!", "We have faced an issue in Customer Account Deletion on telinta. this is the error for cusotmer with  id: " . $company->getId() . " error is " . $e->faultstring . ".  <br/> Please Investigate.");
                        
                        return false;
                    }
                }
                sleep(0.5);
                $retry_count++;
            }
            if ($retry_count == $max_retries) {
                emailLib::sendErrorInTelinta("Account add_account_followme: " . $telintaAccount->getIAccount() . " Error!", "We have faced an issue in Customer Account add_account_followme on telinta. Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
                return false;
            }
        }
        return true;
    }

    public function recharge(Company $company, $amount) {
        return $this->makeTransaction($company, "Manual payment", $amount);
    }

    public function charge(Company $company, $amount) {
        return $this->makeTransaction($company, "Manual charge", $amount);
    }

    public function terminateAccount(TelintaAccounts $telintaAccount) {
        $account = false;
        $max_retries = 10;
        $retry_count = 0;
        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Account');
        
        while (!$account && $retry_count < $max_retries) {
            try {

                $account = $pb->terminate_account(array('i_account' => $telintaAccount->getIAccount()));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Account Deletion: " . $telintaAccount->getIAccount() . " Error!", "We have faced an issue in Company Account Deletion on telinta. This is the error for cusotmer with IAccount: " . $telintaAccount->getIAccount() . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Account Deletion: " . $telintaAccount->getIAccount() . " Error!", "We have faced an issue in Company Account Deletion on telinta. This is the error for cusotmer with IAccount: " . $telintaAccount->getIAccount() . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        $telintaAccount->setStatus(5);
        $telintaAccount->save();
        
        return true;
    }

    public function getAccountCallHistory($iAccount, $fromDate, $toDate) {
        $xdrList = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Account');
        
        while (!$xdrList && $retry_count < $max_retries) {
            try {
                $xdrList = $pb->get_xdr_list(array('i_account' => $iAccount, 'from_date' => $fromDate, 'to_date' => $toDate));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Employee Call History: " . $iAccount . " Error!", "We have faced an issue with Employee while Fetching Call History. This is the error for cusotmer with IAccount: " . $iAccount . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    return false;
                    
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Employee Call History: " . $iAccount . " Error!", "We have faced an issue with Employee while Fetching Call History on telinta. This is the error for cusotmer with IAccount: " . $iAccount . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        
        return $xdrList;
    }

    public function getAccountInfo($iAccount) {
        $aInfo = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Account');
        
        while (!$aInfo && $retry_count < $max_retries) {
            try {
                $aInfo = $pb->get_account_info(array(
                            'i_account' => $iAccount,
                        ));
                
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Employee Account info Fetching: " . $iAccount . " Error!", "We have faced an issue in Employee Account Info Fetch on telinta. This is the error for cusotmer with account: " . $iAccount . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Employee Account info Fetching: " . $iAccount . " Error!", "We have faced an issue in Employee Account Info Fetch on telinta. This is the error for cusotmer with account: " . $iAccount . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        
        return $aInfo;
    }

    public function getBalance(Company $company) {
        
        $cInfo = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Customer');
        
        while (!$cInfo && $retry_count < $max_retries) {
            try {

                $cInfo = $pb->get_customer_info(array(
                            'i_customer' => $company->getICustomer(),
                        ));
                $Balance = $cInfo->customer_info->balance;
                
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Company Balance Fetching: " . $company->getId() . " Error!", "We have faced an issue in Company Account Balance Fetch on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Company Balance Fetching: " . $company->getId() . " Error!", "We have faced an issue in Company Account Balance Fetch on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        
        if ($Balance == 0)
            return $Balance;
        else
            return -1 * $Balance;
    }

    private function createAccount(Company $company, $mobileNumber, $accountType, $iProduct, $followMeEnabled='N') {
        $account = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Account');
        
        $accountName = $accountType . $mobileNumber;
        while (!$account && $retry_count < $max_retries) {
            try {

                $account = $pb->add_account(array('account_info' => array(
                                'i_customer' => $company->getICustomer(),
                                'name' => $accountName, //75583 03344090514
                                'id' => $accountName,
                                'iso_4217' => $this->currency,
                                'opening_balance' => 0,
                                'credit_limit' => null,
                                'i_product' => $iProduct,
                                'i_routing_plan' => 2782,
                                'billing_model' => 1,
                                'password' => 'asdf1asd',
                                'h323_password' => 'asdf1asd',
                                'activation_date' => date('Y-m-d'),
                                'batch_name' => "KB2B" . $company->getVatNo(),
                                'follow_me_enabled' => $followMeEnabled
                                )));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Account Creation: " . $accountName . " Error!", "We have faced an issue in Company Account Creation on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . " and on Account " . $accountName . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Account Creation: " . $accountName . " Error!", "We have faced an issue in Company Account Creation on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . " and on Account " . $accountName . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }

        $telintaAccount = new TelintaAccounts();
        $telintaAccount->setAccountTitle($accountName);
        $telintaAccount->setParentId($company->getId());
        $telintaAccount->setParentTable("company");
        $telintaAccount->setICustomer($company->getICustomer());
        $telintaAccount->setIAccount($account->i_account);
         if($accountType==""){
         $accountType='r';
        }
           $telintaAccount->setAccountType($accountType);
        $telintaAccount->save();
        return true;
    }

    private function makeTransaction(Company $company, $action, $amount) {
        $accounts = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Customer');
        
        while (!$accounts && $retry_count < $max_retries) {
            try {
                $accounts = $pb->make_transaction(array(
                            'i_customer' => $company->getICustomer(),
                            'action' => $action, //Manual payment, Manual charge
                            'amount' => $amount,
                            'visible_comment' => 'charge by SOAP ' . $action
                        ));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Customer Transcation: " . $company->getId() . " Error!", "We have faced an issue with Customer while making transaction " . $action . ". This is the error for cusotmer with Company Id: " . $company->getId() . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Customer Transcation: " . $company->getId() . " Error!", "We have faced an issue with Customer while making transaction " . $action . ". This is the error for cusotmer with Company Id: " . $company->getId() . ". Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        
        return true;
    }

    public function callHistory(Company $company, $fromDate, $toDate) {
        $xdrList = false;
        $max_retries = 10;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Customer');
        
        while (!$xdrList && $retry_count < $max_retries) {
            try {
                $xdrList = $pb->get_customer_xdr_list(array('i_customer' => $company->getICustomer(), 'from_date' => $fromDate, 'to_date' => $toDate));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host' && $e->faultstring != 'Internal Server Error') {
                    emailLib::sendErrorInTelinta("Company Call History: " . $company->getId() . " Error!", "We have faced an issue with Company while Fetching Call History. This is the error for cusotmer with Company Id: " . $company->getId() . " and error is " . $e->faultstring . ".  <br/> Please Investigate.");
                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Company Call History: " . $company->getId() . " Error!", "We have faced an issue with Company while Fetching Call History on telinta. This is the error for cusotmer with Company Id: " . $company->getId() . " . Error is Even After Max Retries " . $max_retries . ".  <br/> Please Investigate.");
            return false;
        }
        
        return $xdrList;
    }

    public function updateCustomer($update_customer_request){
        $customer = false;
        $max_retries = 5;
        $retry_count = 0;

        $pb = new PortaBillingSoapClient($this->telintaSOAPUrl, 'Admin', 'Customer');

        while (!$customer && $retry_count < $max_retries) {
            try {
                $customer = $pb->update_customer(array('customer_info' => $update_customer_request));
            } catch (SoapFault $e) {
                if ($e->faultstring != 'Could not connect to host') {
                    emailLib::sendErrorInTelinta("Customer Update: " . $update_customer_request["i_customer"] . " Error!", "We have faced an issue in Company updation on telinta. This is the error for comapny with ICustomer: " . $update_customer_request["i_customer"] . " and error is " . $e->faultstring . ". <br/> Please Investigate.");

                    return false;
                }
            }
            sleep(0.5);
            $retry_count++;
        }
        if ($retry_count == $max_retries) {
            emailLib::sendErrorInTelinta("Customer Update: " . $update_customer_request["i_customer"] . " Error!", "We have faced an issue in Company updation on telinta. This is the error for comapny with ICustomer: " . $update_customer_request["i_customer"] . ". Error is Even After Max Retries ".$max_retries.". <br/> Please Investigate.");
            return false;
        }
        return true;

    }

    public function randomPrefix($length) {
        $random = "";
        srand((double) microtime() * 1000000);
        $data = "abcdefghijklmnopqrstuvwxyz";
        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }
        return $random;
    }

    public function randomPrefixq($length) {
        $random = "";
        srand((double) microtime() * 1000000);
        $data = "0123456789";
        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }
        return $random;
    }

    public function randomPrefixw($length) {
        $random = "";
        srand((double) microtime() * 1000000);
        $data = "abcdefghijklmnopqrstuvwxyz";
        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }
        return $random;
    }

}

?>
