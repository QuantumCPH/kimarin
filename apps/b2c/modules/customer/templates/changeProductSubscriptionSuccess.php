
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
<h1><?php //echo __('Change Number');?></h1>
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
	            <input  class="butonsigninsmall blockbutton" style="padding: 5px 5px 5px 5px; margin-right: 12px !important;" type="submit" value="<?php echo __('Submit')?>" />
	          </li>

	</ul>

</form>
    <div class="clr"></div>
    </div>
</div>    <?php include_partial('sidebar') ?>