

<div id="sf_admin_container"><h1><?php echo __('Change Product Detail') ?></h1></div>
     
  <div class="borderDiv"> 
<form method="post"  class="split-form-sign-up" id="purchaseNewSimDetail" action="<?php echo url_for('affiliate/changeProductProcess') ?>">
        <?php if($error_msg){?>
            <strong><?php echo $error_msg ?></strong>
        <?php } ?>
             
            
            <input type="hidden" name="productId"  id="productId"  value="<?php echo  $product->getId(); ?>">
            <input type="hidden" name="customerId" id="customerId" value="<?php echo $customer->getId(); ?>">
             
        	<ul class="fl col">
                   
                    
                           <li>
         <?php if($customer->getBusiness()){ ?>        <label>Company name</label>  <?php }else{  ?>    <label>Customer Name</label>   <?php  } ?>
            <label><?php if($customer->getBusiness()){ echo  $customer->getFirstName(); }else{  echo $customer->getFirstName()." ".$customer->getLastName();  } ?></label><br />
        </li>
                  <li>
                <label> <?php echo __("Customer Mobile Number") ?>:</label>
                <label><?php echo $customer->getMobileNumber(); ?></label>  
                </li>
                   <li>
                <label> <?php echo __("New Product") ?>:</label>
            <label>  <?php echo  $product->getName(); ?></label>
                </li>
 
            <li>
                <label> <?php echo __("Amount") ?>:</label>
            <label>  <?php echo number_format($price, 2);echo sfConfig::get('app_currency_code'); ?></label>  
                </li>
                
                <li> <label><?php echo __("IVA") ?>: </label> <label> <?php echo number_format($vat, 2);echo sfConfig::get('app_currency_code'); ?></label>  </li>
                  
                    <li> <label> <?php echo __("Total") ?>:</label>  <label> <?php echo number_format($total, 2);echo sfConfig::get('app_currency_code'); ?></label>  </li>
                   
                    <li>  <input type="submit" value="<?php echo __('Submit') ?>" style="margin-left:50px !important;float:none !important;" /></li>
                </ul>
                        
</form>
      <div class="clr"></div>
  </div>