 <div id="sf_admin_container"><h1><?php echo __('New Sim Card Purchase') ?></h1></div>
     
  <div class="borderDiv"> 
<form method="post"  class="split-form-sign-up" id="refill_form" action="<?php echo url_for('affiliate/purchaseNewSimProcess') ?>">
        <?php if($error_msg){?>
            <strong><?php echo $error_msg ?></strong>
        <?php } ?>
             
            
            <input type="hidden" name="productId"  id="productId"  value="<?php echo  $product->getId(); ?>">
            <input type="hidden" name="customerId" id="customerId" value="<?php echo $customer->getId(); ?>">
             
        	<ul class="fl col">
                      <li>
                <label> <?php echo __("Customer Name") ?>:</label>
                <label><?php echo $customer->getFirstName()." ".$customer->getLastName();  ?></label>  
                </li>
                  <li>
                <label> <?php echo __("Customer Mobile Number") ?>:</label>
                <label><?php echo $customer->getMobileNumber(); ?></label>  
                </li>
                  <li>
                <label> <?php echo __("Product Name") ?>:</label>
            <label>  <?php echo  $product->getName(); ?></label>
                </li>
            <li>
                <label> <?php echo __("Product price") ?>:</label>
            <label>  <?php echo number_format($price, 2);echo sfConfig::get('app_currency_code'); ?></label>  
                </li>
                
                <li> <label><?php echo __("IVA") ?>: </label> <label> <?php echo number_format($vat, 2);echo sfConfig::get('app_currency_code'); ?></label>  </li>
                  
                    <li> <label> <?php echo __("Total amount") ?>:</label>  <label> <?php echo number_format($total, 2);echo sfConfig::get('app_currency_code'); ?></label>  </li>
                
                    
                    <li>  <input type="submit" value="<?php echo __('Pay') ?>" style="margin-left:50px !important;float:none !important;" /></li>
                </ul>
             
             
              
</form>
      <div class="clr"></div>
  </div>