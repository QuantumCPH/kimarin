 <script type="text/javascript">
	
    jq = jQuery.noConflict();
	
    jq(document).ready(function(){

        jq("#purchaseNewSim").validate({
            rules: {
                mobile_number: {
                    remote: "<?php echo sfConfig::get('app_agent_url'); ?>affiliate/validateCustomer"
                }
            }
        });

    });
	</script>


<div id="sf_admin_container"><h1><?php echo __('Change Customer Product') ?></h1></div>
     
  <div class="borderDiv"> 
<form method="post"  class="split-form-sign-up" id="purchaseNewSim" action="<?php echo url_for('affiliate/changeProductServiceDetail') ?>">
        <?php if($error_msg){?>
            <strong><?php echo $error_msg ?></strong>
        <?php } ?>
             <div class="refillhead"><?php echo __('Change Customer Product.') ?></div>
        	<ul class="fl col">
                    <li>
                       <label>Customer Mobile Number</label>  
                       <input type="text" name="mobile_number"  id="mobile_number" class="required" >
                    </li>    <li>
                <label>Product</label>
             <select name="sim_type"  class="required newcard" >
                            <option value=""><?php echo __("Select Product") ?></option>
                            <?php foreach($simtypes as $simtype){  ?>
                            <option value="<?php echo $simtype->getId(); ?>" <?php echo ($simtype->getId()==$product_id)?'selected="selected"':''?>><?php echo $simtype->getName(); ?></option>
                            <?php   }  ?>
                        </select>
            </li>   <li class="">
               <input type="submit" value="<?php echo __('Next') ?>" style="margin-left:50px !important;float:none !important;" />
          </li></ul>
             
             
             
             
</form>
      <div class="clr"></div>
  </div>