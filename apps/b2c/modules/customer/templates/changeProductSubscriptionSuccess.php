
<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Dashboard')) ) ?>
<?php if ($sf_user->hasFlash('message')): ?>
        <div class="alert_bar">
         <?php echo __($sf_user->getFlash('message')) ?>
        </div>
    <?php endif; ?>
<script type="text/javascript">
jQuery(function(){
    jQuery('#changeproduct').validate({
        rules: {
            product:{
                required: true
            }
        },
        messages: {
            product:{
                required: "<div class='error_cp'><?php echo __('You must fill in this field')?></div>"
            }
        }
    });
});
</script>
<div class="left-col">    
    <?php include_partial('navigation', array('selected' => 'dashboard', 'customer_id' => $customer->getId())) ?><br />
    
    <div class="split-form">
        <form method="post" name="changenumber" id="changeproduct" class="split-form-sign-up" action="<?php echo url_for($targetUrl.'customer/changeProductProcess') ?>">
          
             
<!--            <p><?php echo __('Your product change will be valid from the first day of the comming month.');?></p><br />-->
    	<ul class="fl col">
            <li>
                <label><?php echo __('Product Name') ?></label>
                <?php  $c = new Criteria();
                $c->add(ProductPeer::PRODUCT_TYPE_ID, 1);
                $c->addOr(ProductPeer::PRODUCT_TYPE_ID, 10);
                 $c->addAnd(ProductPeer::INCLUDE_IN_ZEROCALL, 1);
                  $c->addAnd(ProductPeer::ID, $customerProduct->getProductId(),Criteria::NOT_EQUAL);
                $products = ProductPeer::doSelect($c);  ?>
                <select name="product" class="required">
                    <?php foreach ($products as  $product){ ?>
                    <option value="<?php echo $product->getID(); ?>" ><?php echo $product->getName(); ?></option>
                    
                    <?php  } ?>
                </select>
            </li>
             
	          <li class="fr buttonplacement">
                   
	            <input  class="butonsigninsmall blockbutton" style="padding: 5px 5px 5px 5px; margin-right: 32px !important;" type="submit"  value="<?php echo __('Next')?>" />
	          </li>

	</ul>

</form>
    <div class="clr"></div>
    </div>
</div>    <?php include_partial('sidebar') ?>