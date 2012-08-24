
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
        <form method="post" name="changenumber" id="changenumber" class="split-form-sign-up" action="<?php echo url_for($targetUrl.'customer/changeProductProcess') ?>">
            <?php if($disable){?> 
            <span class="alertmsg"><?php echo __("You have already subscribed for change of Product/subscription");?></span>
            <?php } 
            ?>
            <p><?php echo __('Product change will be implemented in 1 day of comming month.');?></p><br />
    	<ul class="fl col">
            <li>
                <label><?php echo __('Product Name') ?></label>
                <?php  $c = new Criteria();
                $c->add(ProductPeer::PRODUCT_TYPE_ID, 1);
                 $c->addAnd(ProductPeer::INCLUDE_IN_ZEROCALL, 1);
                  $c->addAnd(ProductPeer::ID, $customerProduct->getProductId(),Criteria::NOT_EQUAL);
                $products = ProductPeer::doSelect($c);  ?>
                <select name="product">
                    <?php foreach ($products as  $product){ ?>
                    <option value="<?php echo $product->getID(); ?>" ><?php echo $product->getName(); ?></option>
                    
                    <?php  } ?>
                </select>
            </li>
             
	          <li class="fr buttonplacement">
                    <?php $button_disable='';
                    if($disable){ 
                           $button_disable = 'disabled="disabled"';
                    }
                    ?>  
	            <input  class="butonsigninsmall blockbutton" style="padding: 5px 5px 5px 5px; margin-right: 12px !important;" type="submit" <?php echo $button_disable;?> value="<?php echo __('Next')?>" />
	          </li>

	</ul>

</form>
    <div class="clr"></div>
    </div>
</div>    <?php include_partial('sidebar') ?>