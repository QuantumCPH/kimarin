<script type="text/javascript">
jQuery(function(){
    jQuery('#changenumber').validate({
        rules: {
            existingNumber:{
                required: true,
                minlength: 8,
                digits: true
            },
           newNumber:{
                required: true,
                minlength: 8,
                digits: true
            }
        },
        messages: {
            existingNumber:{
                required: "Please Enter Old Mobile Number",
                minlength: "Atleast 8 digits are required",
                digits: "Please Enter only digits"
            },
            newNumber:{
                required: "Please Enter New Mobile Number",
                minlength: "Atleast 8 digits are required",
                digits: "Please Enter only digits"
            }
        }
    });
});
</script>
<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Dashboard')) ) ?>
<?php if ($sf_user->hasFlash('message')): ?>
        <div class="alert_bar">
         <?php echo __($sf_user->getFlash('message')) ?>
        </div>
    <?php endif; ?>
<div class="left-col">    
    <?php include_partial('navigation', array('selected' => 'dashboard', 'customer_id' => $customer->getId())) ?><br />
    
    <div class="split-form">
        <form method="post" name="changenumber" id="changenumber" class="split-form-sign-up" action="<?php echo url_for($targetUrl.'customer/changeNumber') ?>">
<h1><?php //echo __('Change Number');?></h1>
    	<ul class="fl col">
            <li>
                <label><?php echo __('Old Mobile Number') ?></label>
                <input type="text" name="existingNumber" style="margin-bottom:0px" value="<?php echo $customer->getMobileNumber();?>" readonly="readonly" />
<!--                <label class="validnumber">Enter mobile number without leading 0</label>-->
            </li>
            <li>
                <label><?php echo __('New Mobile Number') ?><br />0034*</label>
                <input type="text" name="newNumber" style="margin-bottom:0px"/>
<!--                <label class="validnumber">Enter mobile number without leading 0</label>-->
            </li>
           
            <li>
                <label><?php echo __('Product Name') ?></label>
                <?php  $c = new Criteria();
                $c->add(ProductPeer::ID, 3);
                $product = ProductPeer::doSelectOne($c);  ?>
                <select name="product">
                    <option value="<?php echo $product->getID(); ?>" ><?php echo $product->getName(); ?></option>
                </select>
            </li>
             
	          <li class="fr buttonplacement">
	            <input  class="butonsigninsmall blockbutton" style="padding: 5px 5px 5px 5px; margin-right: 12px !important;" type="submit" value="<?php echo __('Next')?>" />
	          </li>

	</ul>

</form>
    <div class="clr"></div>
    </div>
</div>    <?php include_partial('sidebar') ?>