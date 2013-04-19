<?php

//require_once(sfConfig::get('sf_lib_dir') . '/commissionLib.class.php');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class TransactionProcess {
    /*
     * @@prductName: This will be sent in the sms.
     * @@telephoneNumber: this will be sent in the SMS.
     * @@password: password of the account to be sent in sms.
     * @@recepientMobileNumber: This needs to be a complete number where user will be receiving the sms.
     *
     */

    public static function StartTransaction($customer, $productId, $decriptionid, $expenceType = 1, $transactionFrom = 1, $transactionStatus = 1, $commision = false, $agentCompanyId = false) {
        // id,amount,description,order_id,customer_id,transaction_status_id,created_at,agent_company_id,commission_amount,transaction_from,transaction_type_id,transaction_description_id,vat,email_tempalte	receipt_no,customer_first_name,customer_last_name,customer_email,customer_city,customer_mobile_number,customer_po_box_number,customer_address,product_id,product_name,product_description,product_initial_balance,product_registration_fee,product_subscription_fee,product_bonus,product_type_id,product_sim_type_id,product_price,agent_company_name,country_id,country_name,postal_charges,vat_percentage,amount_with_vat,amount_without_vat



        $product = ProductPeer::retrieveByPK($productId);
        $decription = TransactionDescriptionPeer::retrieveByPK($decriptionid);
        $country = CountryPeer::retrieveByPK($customer->getCountryId());
        // var_dump($product->getVatApplicable());
        $vatMultiple = 0;
        if ($product->getVatApplicable()) {
            $vatMultiple = $country->getVatPercentage() / 100;
            $vatPercentage = $country->getVatPercentage();
        } else {
            $vatMultiple = 0;
            $vatPercentage = 0;
        }
        // echo $product->getPostageApplicable()?"true":"false";die;
        if ($product->getPostageApplicable() && $transactionFrom != 2 && $transactionFrom != 6) {
            $postalCharges = $country->getPostalCharges();
        } else {

            $postalCharges = 0;
        }

        $vat = ($postalCharges + $product->getRegistrationFee()) * $vatMultiple;
        // var_dump($vat);die;
        $totalBalanceWithoutVat = $product->getSubscriptionFee() + $postalCharges + $product->getRegistrationFee() + $product->getPrice();
        $totalBalanceWithVat = $product->getSubscriptionFee() + $postalCharges + $product->getRegistrationFee() + $product->getPrice() + $vat;
        $vat = number_format($vat, 2);
        $totalBalanceWithoutVat = number_format($totalBalanceWithoutVat, 2);
        $totalBalanceWithVat = number_format($totalBalanceWithVat, 2);
        $order = new CustomerOrder();
        $order->setProductId($product->getId());
        $order->setCustomerId($customer->getId());
        $order->setExtraRefill($product->getInitialBalance());
        $order->setIsFirstOrder($product->getProductTypeId());
        $order->setOrderStatusId($transactionStatus);
        if ($agentCompanyId) {
            $agentCompany = AgentCompanyPeer::retrieveByPK($agentCompanyId);
            $order->setAgentCommissionPackageId($agentCompany->getAgentCommissionPackageId());
        }
        $order->save();

        $transaction = new Transaction();
        $transaction->setAmount($totalBalanceWithVat);
        $transaction->setDescription($decription->getTitle());
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($customer->getId());
        $transaction->setTransactionStatusId($transactionStatus);

        if ($agentCompanyId) {


            $transaction->setAgentCompanyId($agentCompany->getId());
            $transaction->setAgentCompanyName($agentCompany->getName());
            $transaction->setAgentCommissionPackageId($agentCompany->getAgentCommissionPackageId());
            $transaction->setAgentCompanyEmail($agentCompany->getEmail());
        }

        $transaction->setTransactionFrom($transactionFrom);
        $transaction->setTransactionTypeId($decription->getTransactionTypeId());
        $transaction->setTransactionDescriptionId($decription->getId());
        $transaction->setVat($vat);
        $transaction->setCustomerFirstName($customer->getFirstName());
        $transaction->setCustomerLastName($customer->getLastName());
        $transaction->setCustomerEmail($customer->getEmail());
        $transaction->setCustomerCity($customer->getCity());
        $transaction->setCustomerMobileNumber($customer->getMobileNumber());
        $transaction->setCustomerPoBoxNumber($customer->getPoBoxNumber());
        $transaction->setCustomerAddress($customer->getAddress());
        $transaction->setCustomerSimType($customer->getSimTypeId());
        $transaction->setCustomerUniqueId($customer->getUniqueid());
        $transaction->setCustomerNumber($customer->getCustomerNumber());
        $transaction->setCustomerRegistrationTypeId($customer->getRegistrationTypeId());
        $transaction->setProductId($product->getId());
        $transaction->setProductName($product->getName());
        $transaction->setProductDescription($product->getDescription());
        $transaction->setProductInitialBalance($product->getInitialBalance());
        $transaction->setProductRegistrationFee($product->getRegistrationFee());
        $transaction->setProductSubscriptionFee($product->getSubscriptionFee());
        $transaction->setProductBonus($product->getBonus());
        $transaction->setProductTypeId($product->getProductTypeId());
        $transaction->setProductSimTypeId($product->getSimTypeId());
        $transaction->setProductPrice($product->getPrice());
        $transaction->setProductPostageApplicable($product->getPostageApplicable());
        $transaction->setProductVatApplicable($product->getVatApplicable());
        $transaction->setCountryId($country->getId());
        $transaction->setCountryName($country->getName());
        $transaction->setPostalCharges($postalCharges);
        $transaction->setVatPercentage($vatPercentage);
        $transaction->setAmountWithVat($totalBalanceWithVat);
        $transaction->setAmountWithoutVat($totalBalanceWithoutVat);
        $transaction->setProductCustomerProductTypeId($product->getCustomerProductTypeId());
        $transaction->setInitialBalance($product->getInitialBalance());
        $transaction->save();
        if ($commision) {
            commissionLib::commissionCalculation($agentCompanyId, $product->getId(), $transaction->getId(), $expenceType);
        }



        return $transaction;
    }
    
    public static function StartScratchCardTransaction($customer, $productId, $scratcCardPrice, $decriptionid, $expenceType = 1, $transactionFrom = 1, $transactionStatus = 1, $commision = false, $agentCompanyId = false) {
        // id,amount,description,order_id,customer_id,transaction_status_id,created_at,agent_company_id,commission_amount,transaction_from,transaction_type_id,transaction_description_id,vat,email_tempalte	receipt_no,customer_first_name,customer_last_name,customer_email,customer_city,customer_mobile_number,customer_po_box_number,customer_address,product_id,product_name,product_description,product_initial_balance,product_registration_fee,product_subscription_fee,product_bonus,product_type_id,product_sim_type_id,product_price,agent_company_name,country_id,country_name,postal_charges,vat_percentage,amount_with_vat,amount_without_vat



        $product = ProductPeer::retrieveByPK($productId);
        $decription = TransactionDescriptionPeer::retrieveByPK($decriptionid);
        $country = CountryPeer::retrieveByPK($customer->getCountryId());
        // var_dump($product->getVatApplicable());
        $vatMultiple   = 0;
        $vatPercentage = 0;
        $postalCharges = 0;
//        if ($product->getVatApplicable()) {
//            $vatMultiple = $country->getVatPercentage() / 100;
//            $vatPercentage = $country->getVatPercentage();
//        } else {
//            $vatMultiple = 0;
//            $vatPercentage = 0;
//        }
//        
//        if ($product->getPostageApplicable() && $transactionFrom != 2) {
//            $postalCharges = $country->getPostalCharges();
//        } else {
//
//            $postalCharges = 0;
//        }

        $vat = $scratcCardPrice * $vatMultiple;
        // var_dump($vat);die;
        $totalBalanceWithoutVat = $scratcCardPrice;
        $totalBalanceWithVat    = $scratcCardPrice + $vat;
        $vat = number_format($vat, 2);
        $totalBalanceWithoutVat = number_format($totalBalanceWithoutVat, 2);
        $totalBalanceWithVat = number_format($totalBalanceWithVat, 2);
        $order = new CustomerOrder();
        $order->setProductId($product->getId());
        $order->setCustomerId($customer->getId());
        $order->setExtraRefill($scratcCardPrice);
        $order->setIsFirstOrder(2);
        $order->setOrderStatusId($transactionStatus);
        if ($agentCompanyId) {
            $agentCompany = AgentCompanyPeer::retrieveByPK($agentCompanyId);
            $order->setAgentCommissionPackageId($agentCompany->getAgentCommissionPackageId());
        }
        $order->save();

        $transaction = new Transaction();
        $transaction->setAmount($totalBalanceWithVat);
        $transaction->setDescription($decription->getTitle());
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($customer->getId());
        $transaction->setTransactionStatusId($transactionStatus);

        if ($agentCompanyId) {

            $transaction->setAgentCompanyId($agentCompany->getId());
            $transaction->setAgentCompanyName($agentCompany->getName());
            $transaction->setAgentCommissionPackageId($agentCompany->getAgentCommissionPackageId());
            $transaction->setAgentCompanyEmail($agentCompany->getEmail());
        }

        $transaction->setTransactionFrom($transactionFrom);
        $transaction->setTransactionTypeId($decription->getTransactionTypeId());
        $transaction->setTransactionDescriptionId($decription->getId());
        $transaction->setVat($vat);
        $transaction->setCustomerFirstName($customer->getFirstName());
        $transaction->setCustomerLastName($customer->getLastName());
        $transaction->setCustomerEmail($customer->getEmail());
        $transaction->setCustomerCity($customer->getCity());
        $transaction->setCustomerMobileNumber($customer->getMobileNumber());
        $transaction->setCustomerPoBoxNumber($customer->getPoBoxNumber());
        $transaction->setCustomerAddress($customer->getAddress());
        $transaction->setCustomerSimType($customer->getSimTypeId());
        $transaction->setCustomerUniqueId($customer->getUniqueid());
        $transaction->setCustomerNumber($customer->getCustomerNumber());
        $transaction->setCustomerRegistrationTypeId($customer->getRegistrationTypeId());
        $transaction->setProductId($product->getId());
        $transaction->setProductName($product->getName());
        $transaction->setProductDescription($product->getDescription());
        $transaction->setProductInitialBalance($scratcCardPrice);
        $transaction->setProductRegistrationFee(0);
        $transaction->setProductSubscriptionFee(0);
        $transaction->setProductBonus(0);
        $transaction->setProductTypeId(2);
        $transaction->setProductSimTypeId($product->getSimTypeId());
        $transaction->setProductPrice(0);
        $transaction->setProductPostageApplicable(0);
        $transaction->setProductVatApplicable(0);
        $transaction->setCountryId($country->getId());
        $transaction->setCountryName($country->getName());
        $transaction->setPostalCharges(0);
        $transaction->setVatPercentage(0);
        $transaction->setAmountWithVat($totalBalanceWithVat);
        $transaction->setAmountWithoutVat($totalBalanceWithoutVat);
        $transaction->setProductCustomerProductTypeId($product->getCustomerProductTypeId());
        $transaction->setInitialBalance($totalBalanceWithoutVat);
        $transaction->save();
        if ($commision) {
            commissionLib::commissionCalculation($agentCompanyId, $product->getId(), $transaction->getId(), $expenceType);
        }



        return $transaction;
    }
}

class TransactionProcessAdmin {
    /*
     * @@prductName: This will be sent in the sms.
     * @@telephoneNumber: this will be sent in the SMS.
     * @@password: password of the account to be sent in sms.
     * @@recepientMobileNumber: This needs to be a complete number where user will be receiving the sms.
     *
     */

    public static function StartTransaction($customer, $refillAmount, $decriptionid, $expenceType = 1, $transactionFrom = 1, $transactionStatus = 1) {
        // id,amount,description,order_id,customer_id,transaction_status_id,created_at,agent_company_id,commission_amount,transaction_from,transaction_type_id,transaction_description_id,vat,email_tempalte	receipt_no,customer_first_name,customer_last_name,customer_email,customer_city,customer_mobile_number,customer_po_box_number,customer_address,product_id,product_name,product_description,product_initial_balance,product_registration_fee,product_subscription_fee,product_bonus,product_type_id,product_sim_type_id,product_price,agent_company_name,country_id,country_name,postal_charges,vat_percentage,amount_with_vat,amount_without_vat
        // var_dump($customer);

        $decription = TransactionDescriptionPeer::retrieveByPK($decriptionid);
        $country = CountryPeer::retrieveByPK($customer->getCountryId());

        $cp = new Criteria();
        $cp->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
        $cp->addAnd(CustomerProductPeer::STATUS_ID, 3);
        $customer_product = CustomerProductPeer::doSelectOne($cp);

        if($decription->getTransactionTypeId()!=2){
          $vatPercentage = $country->getVatPercentage();
          $vatMultiple = $country->getVatPercentage() / 100;
          $vat = $refillAmount * $vatMultiple;
          
        }else{
           $vatPercentage =0; 
           $vatMultiple = 0 ;
           $vat = 0;
        }
        $totalBalanceWithoutVat = $refillAmount;
          $totalBalanceWithVat = $refillAmount + $vat;
          $vat = number_format($vat, 2);
          $totalBalanceWithoutVat = number_format($totalBalanceWithoutVat, 2);
          $totalBalanceWithVat = number_format($totalBalanceWithVat, 2);
        $order = new CustomerOrder();
        $order->setProductId($customer_product->getProductId());
        $order->setCustomerId($customer->getId());
        $order->setExtraRefill($refillAmount);
        $order->setIsFirstOrder(2);
        $order->setOrderStatusId($transactionStatus);
        $order->save();

        $transaction = new Transaction();
        $transaction->setAmount($totalBalanceWithVat);
        $transaction->setDescription($decription->getTitle());
        $transaction->setOrderId($order->getId());
        $transaction->setCustomerId($customer->getId());
        $transaction->setTransactionStatusId($transactionStatus);

        $transaction->setTransactionFrom($transactionFrom);
        $transaction->setTransactionTypeId($decription->getTransactionTypeId());
        $transaction->setTransactionDescriptionId($decription->getId());
        $transaction->setVat($vat);
        $transaction->setCustomerFirstName($customer->getFirstName());
        $transaction->setCustomerLastName($customer->getLastName());
        $transaction->setCustomerEmail($customer->getEmail());
        $transaction->setCustomerCity($customer->getCity());
        $transaction->setCustomerMobileNumber($customer->getMobileNumber());
        $transaction->setCustomerPoBoxNumber($customer->getPoBoxNumber());
        $transaction->setCustomerAddress($customer->getAddress());
        $transaction->setCustomerSimType($customer->getSimTypeId());
        $transaction->setCustomerUniqueId($customer->getUniqueid());
        $transaction->setCustomerNumber($customer->getCustomerNumber());
//            $transaction->setProductId($product->getId());
//            $transaction->setProductName($product->getName());
//            $transaction->setProductDescription($product->getDescription());
//            $transaction->setProductInitialBalance($product->getInitialBalance());
//            $transaction->setProductRegistrationFee($product->getRegistrationFee());
//            $transaction->setProductSubscriptionFee($product->getSubscriptionFee());
//            $transaction->setProductBonus($product->getBonus());
//            $transaction->setProductTypeId($product->getProductTypeId());
//            $transaction->setProductSimTypeId($product->getSimTypeId());
//            $transaction->setProductPrice($product->getPrice());
//            $transaction->setProductPostageApplicable($product->getPostageApplicable());
//            $transaction->setProductVatApplicable($product->getVatApplicable());
        $transaction->setCountryId($country->getId());
        $transaction->setCountryName($country->getName());
        $transaction->setVatPercentage($vatPercentage);
        $transaction->setAmountWithVat($totalBalanceWithVat);
        $transaction->setAmountWithoutVat($totalBalanceWithoutVat);
        $transaction->setInitialBalance($totalBalanceWithoutVat);
        $transaction->save();

        return $transaction;
    }   
   
}

class B2BTransactionProcessAdmin {
    
    public static function StartTransaction($company, $amount, $description,$expenceType = 1, $transactionFrom = 1, $transactionStatus = 1) {
        
        if($expenceType == 1){
            $with_vat_amount = $amount * (1+($company->getCountry()->getVatPercentage()/100));
            $vat = $with_vat_amount - $amount;
            $vat = number_format($vat,2);
            $with_vat_amount = number_format($with_vat_amount,2);
        }else{
            $vat = 0;
            $vat = number_format($vat,2);
            $with_vat_amount = $vat + $amount;
            $amount = -$amount;
            $with_vat_amount = -(number_format($with_vat_amount,2));
        }
        $transaction = new CompanyTransaction();
        $transaction->setCompanyId($company->getId());
        $transaction->setAmount($with_vat_amount);
        $transaction->setExtraRefill($amount);
        $transaction->setPaymentType($description->getId());//Refill Transaction Description id
        $transaction->setDescription($description->getTitle());
        $transaction->setTransactionStatusId($transactionStatus);
        $transaction->setTransactionTypeId($expenceType);
        $transaction->setVat($vat);
        $transaction->save();
        
        return $transaction;
    }
}
?>
