<div id="sf_admin_container"><h1><?php echo __('Customer Detail') ?></h1></div>
<div class="borderDiv">
    
    
    
    <?php  
    $productPrice=$product->getRegistrationFee();
    $totalAmount=0;
    $vat=$product->getRegistrationFee() * (sfConfig::get('app_vat_percentage'));
      $totalAmount=$vat+$productPrice;
      $productRefillPrice=$productPrice+$product->getBonus();
    
    ?>
    <form name="" method="post"  action="<?php echo url_for($targetUrl.'affiliate/refillProcess') ?>">
    <input type="hidden" value="<?php echo  $customer->getId(); ?>" name="customer_id" />
    <input type="hidden" value="<?php echo  $product->getId();  ?>" name="product_id" />
    <input type="hidden" value="<?php echo  $totalAmount;  ?>" name="totalAmount" />
    <input type="hidden" value="<?php echo  $vat;  ?>" name="vat" />
     <input type="hidden" value="<?php echo  $productPrice;  ?>" name="productAmount" />
     <input type="hidden" value="<?php echo  $productRefillPrice;  ?>" name="productRefillAmount" />
    
    <ul class="fl col changenumber">
        
        <li>
            <label>Customer Name</label>
            <label><?php echo  $customer->getFirstName(); ?>&nbsp;<?php echo  $customer->getLastName(); ?></label><br />
        </li>
        <li>
            <label>Customer Mobile Number</label>
            <label><?php echo  $customer->getMobileNumber(); ?></label><br />
        </li>
        <li>
            <label>Refill Product Detail</label>
            <label><?php echo $product->getDescription(); ?></label><br />
        </li>
        <li>
            <label>Product Amount</label>
            <label><?php echo number_format($product->getRegistrationFee(),2); ?></label><br />
        </li>
         <li>
            <label>Vat</label>
            <label><?php echo   number_format($vat,2); ?></label><br />
        </li>
         <li>
            <label>Total Amount</label>
            <label><?php echo   number_format($totalAmount,2); ?></label><br />
        </li>
        <li style="margin-left:188px"><input type="submit" name="Pay" value="Pay" /><br /></li>
    </ul>
    </form>
    <div class="clr"></div>
</div>