<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>

<?php
$customer_form = new CustomerForm($customer);
$customer_form->unsetAllExcept(array('auto_refill_amount', 'auto_refill_min_balance'));

$is_auto_refill_activated = $customer_form->getObject()->getAutoRefillAmount()!=null;
?>
 <?php

        $part2 = rand (99,99999);
        $part3 = date("s");
        $randomOrderId =  date('h-m').$part2.$part3;
           ?>
    
<div data-role="page"  data-theme="c">
     
  <form action="<?php echo $target;?>pScripts/appRefilTransaction" method="post" id="refill">
    


	<div data-role="header"  data-theme="c"><h1><?php echo __('Manual Refill') ?></h1></div>
    <div data-role="content"  data-theme="c">	
          <p > <?php echo __('You can refill your %1% Account with the following amounts:',array("%1%"=>sfConfig::get('app_site_title')))?></p> 
       
         	<!-- customer product -->
	<?php   
        
        $bonus="";
                foreach($refillProducts as $refill){ 
                    if($refill->getBonus()){
                        
                        $bonus = "<span>".__('PLUS %1%',array("%1%"=>number_format($refill->getBonus(),2)))."</span>".sfConfig::get('app_currency_code');
                    }
        ?>
            <p><span ><?php echo number_format($refill->getRegistrationFee(),2)?></span><?php echo sfConfig::get('app_currency_code');?>
                <span class='cufon'><?php echo __(" (airtime value:");?></span><?php echo __(" <span class='cufon'>%1%</span>%2% ",array("%1%"=>number_format($refill->getRegistrationFee(),2),"%2%"=>sfConfig::get('app_currency_code'))); echo $bonus."<span class='cufon'>)</span>";
                    //"&nbsp;Bonus:".$refill->getBonus()."&nbsp;Total Including Vat:".(sfConfig::get('app_vat_percentage')+1)*$refill->getRegistrationFee();?></p>
        <?php
        }       
        ?>
          
         <p ><?php echo __("All amounts are excl. IVA.");?></p>
         <p>
         
         <span ><?php echo __("The value of airtime on your account balance cannot  exceed 250.00");?></span><?php echo sfConfig::get('app_currency_code')?><span > <?php echo __("at any moment in time. The refill amount is valid for 180 days.");?></span>
         </p>
       
        <p>
              <label for="extra_refill" ><?php echo __('Select amount to be refilled:') ?></label>
              <span style="margin-left:40px;"><?php echo $form['extra_refill']?></span>
     </p>

           
       <p>
                <input  data-theme="c" type="submit" name="<?php echo __('Refill') ?>" value="<?php echo __('Refill') ?>">
         </p>
        <!-- hidden fields -->
      
        
        <input type="hidden" name="amount" id="total" value="" />
        
        <input type="hidden" name="cmd" value="_xclick" /> 
        <input type="hidden" name="no_note" value="1" />
        <input type="hidden" name="lc" value="UK" />
        <input type="hidden" name="currency_code" value="<?php echo sfConfig::get('app_currency_symbol')?>" />
        <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
        <input type="hidden" name="firstName" value="<?php echo $customer->getFirstName();?>"  />
        <input type="hidden" name="lastName" value="<?php echo $customer->getLastName();?>"  />
        <input type="hidden" name="payer_email" value="<?php echo $customer->getEmail();?>"  />
        <input type="hidden" name="item_number" value="00001" />
        <input type="hidden" name="rm" value="2" />     
        <input type="hidden" name="customer_id"  value="<?php echo $customer->getId();?>" />      
              </div>   
       </form> 
     
 </div>   
 
