<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Change Product') ) ) ?>
<br />
<div class="left-col">

    <?php include_partial('navigation', array('selected' => 'dashboard', 'customer_id' => $customer->getId())) ?>
         <div class="split-form">
          <form name="" method="post"  action="<?php echo url_for($targetUrl.'customer/changeNumberProcessPay') ?>">
   
    <input type="hidden" value="<?php echo  $product->getId();  ?>" name="productid" />
    <input type="hidden" value="<?php echo  $product->getPrice();  ?>" name="extra_refill" />
    <input type="hidden" value="<?php echo  $ccp->getId();  ?>" name="ccpid" />
   
    <ul class="fl col changenumber">
        <li>
            <label><?php echo __("Product Name");?>:</label>
            <label><?php echo  $oldProduct->getName();  ?></label><br />
        </li>        
        <li>
            <label><?php echo __("Amount");?>:</label>
            <label><?php echo  number_format($prodPrice = $product->getRegistrationFee(),2); ?><?php echo  sfConfig::get("app_currency_code"); ?></label><br />
        </li>
        <li>
            <label><?php echo __("IVA");?>:</label>
            <label><?php echo  number_format($refillvat=$product->getRegistrationFee() * sfConfig::get('app_vat_percentage'),2) ?><?php echo  sfConfig::get("app_currency_code"); ?></label><br />
        </li>
        <li>
            <label><?php echo __("Total");?>:</label>
            <label><?php echo  $amount = number_format($prodPrice + $refillvat,2); ?><?php echo  sfConfig::get("app_currency_code"); ?></label><br />
        </li>
        <li><input type="submit" class="butonsigninsmall changeNum" name="Pay" value="<?php echo __("Pay");?>" /><br /></li>
    </ul>
        <input type="hidden" name="amount" id="total" value="<?php echo $amount;?>" />
        
        <input type="hidden" name="cmd" value="_xclick" /> 
        <input type="hidden" name="no_note" value="1" />
        <input type="hidden" name="lc" value="UK" />
        <input type="hidden" name="currency_code" value="<?php echo sfConfig::get('app_currency_symbol')?>" />
        <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
        <input type="hidden" name="firstName" value="<?php echo $order->getCustomer()->getFirstName();?>"  />
        <input type="hidden" name="lastName" value="<?php echo $order->getCustomer()->getLastName();?>"  />
        <input type="hidden" name="payer_email" value="<?php echo $order->getCustomer()->getEmail();?>"  />
        <input type="hidden" name="item_number" value="<?php echo $order->getId();?>" />
        <input type="hidden" name="rm" value="2" /> 
    </form>
    <div class="clr"></div>
</div>
</div>    <?php include_partial('sidebar') ?>