<script type="text/javascript">
	
    jq = jQuery.noConflict();
	
    jq(document).ready(function(){

        jq("#purchaseNewSimDetail").validate({
            rules: {
                uniqueId: {
                    remote: "<?php echo sfConfig::get('app_agent_url'); ?>affiliate/validateUniqeId?sim_type=<?php echo $product->getSimTypeId(); ?>"
                }
            },
    messages: {
      
        uniqueId: {
            required: "Please Enter a 6 digit Unique Number",
            remote: jq.format("Please Enter a Valid Unique Number")
        }
    }
        });

    });
	</script>

<div id="sf_admin_container"><h1><?php echo __('New Sim Card Purchase') ?></h1></div>
     
  <div class="borderDiv"> 
<form method="post"  class="split-form-sign-up" id="purchaseNewSimDetail" action="<?php echo url_for('affiliate/purchaseNewSimProcess') ?>">
        <?php if($error_msg){?>
            <strong><?php echo $error_msg ?></strong>
        <?php } ?>
             
            
            <input type="hidden" name="productId"  id="productId"  value="<?php echo  $product->getId(); ?>">
            <input type="hidden" name="customerId" id="customerId" value="<?php echo $customer->getId(); ?>">
             
        	<ul class="fl col">
                      <li>
                <label> <?php echo __("Unique ID") ?>:</label>
                <input type="text" name="uniqueId" id="uniqueId" class="required"> 
                </li>
                     
                           <li>
         <?php if($customer->getBusiness()){ ?>        <label>Name of contact person:</label>  <?php }else{  ?>    <label>Customer Name</label>   <?php  } ?>
            <label><?php if($customer->getBusiness()){ echo  $customer->getLastName(); }else{  echo $customer->getFirstName()."&nbsp".$customer->getLastName();  } ?></label><br />
        </li>
                
                  <li>
                <label> <?php echo __("Customer Mobile Number") ?>:</label>
                <label><?php echo $customer->getMobileNumber(); ?></label>  
                </li>
                  <li>
                <label> <?php echo __("SIM type") ?>:</label>
            <label>  <?php echo  $product->getName(); ?></label>
                </li>
            <li>
                <label> <?php echo __("Product price") ?>:</label>
            <label>  <?php echo number_format($price, 2);echo sfConfig::get('app_currency_code'); ?></label>  
                </li>
                
                <li> <label><?php echo __("IVA") ?>: </label> <label> <?php echo number_format($vat, 2);echo sfConfig::get('app_currency_code'); ?></label>  </li>
                  
                    <li> <label> <?php echo __("Total amount") ?>:</label>  <label> <?php echo number_format($total, 2);echo sfConfig::get('app_currency_code'); ?></label>  </li>
                   
                    <li>  <input type="submit" value="<?php echo __('Submit') ?>" style="margin-left:50px !important;float:none !important;" /></li>
                </ul>
                        
</form>
      <div class="clr"></div>
  </div>