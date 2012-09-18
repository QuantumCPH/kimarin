 <div id="sf_admin_container"><h1><?php echo __('New Sim Card Purchase') ?></h1></div>
        
  <div class="borderDiv"> 
<form method="post"  class="split-form-sign-up" id="refill_form" action="<?php url_for('affiliate/refill') ?>">
        <?php if($error_msg){?>
            <strong><?php echo $error_msg ?></strong>
        <?php } ?>
             <div class="refillhead"><?php echo __('New Sim Card Purchase.') ?></div>
        
	<ul class="fl col">
            <li>
             <select name="sim_type" onchange="this.form.submit()" class="required newcard">
                            <option value=""><?php echo __("Select SIM type") ?></option>
                            <?php foreach($simtypes as $simtype){  ?>
                            <option value="<?php echo $simtype->getId(); ?>" <?php echo ($simtype->getId()==$product_id)?'selected="selected"':''?>><?php echo $simtype->getName(); ?></option>
                            <?php   }  ?>
                        </select>
            </li>
            
            
          
          <li class="">
               <input type="submit" value="<?php echo __('Pay') ?>" style="margin-left:50px !important;float:none !important;" />
          </li>

         
			  
	</ul>
</form>
      <div class="clr"></div>
  </div>