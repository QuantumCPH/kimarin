<?php

class ProductPeer extends BaseProductPeer
{
	public static $autorefill_choices = array(100, 200, 300);
	public static function getRefillChoices(){
		 return array(100, 200, 300);
            
             
			 
	}
	public static function getRefillHashChoices(){
              $countrylng = new Criteria();
                    $countrylng->add(EnableCountryPeer::ID, 1);
                    $countrylng = EnableCountryPeer::doSelectOne($countrylng);
                    $countryRefill = $countrylng->getRefill();
                    $countryRefill  = $countryRefill;
                    $countryRefill = explode(",", $countryRefill);


                        $c = new Criteria();
                        $c->add(ProductPeer::PRODUCT_TYPE_ID, 2);

                        $refillProducts = ProductPeer::doSelect($c);
                    //----------------------------       End Code -----------------------------------
                        //$countryRefills[] = array();
                        foreach ($refillProducts as &$refill) {
                           $countryRefills[$refill->getId()] =  $refill->getDescription();
                        }
                        return $countryRefills;
		//return array('100' => 100, '200' => 200, '500'=> 500);
	}
	
	public static function getAutoRefillLowerLimitHashChoices()
	{
		$limits = array(25, 50);
		 
		return array_combine($limits, $limits);
	}
}
