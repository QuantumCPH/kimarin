<div id="sf_admin_container"><h1><?php echo __('Customer Detail') ?></h1></div>
<div class="borderDiv">
    <form name="" method="post"  action="<?php echo url_for($targetUrl.'affiliate/numberProcess') ?>">
    <input type="hidden" value="<?php echo  $customer->getMobileNumber(); ?>" name="mobile_number" />
    <input type="hidden" value="<?php echo  $product->getId();  ?>" name="productid" />
    <input type="hidden" value="<?php echo  $price=$product->getRegistrationFee();  ?>" name="extra_refill" />
    
    <?php  
        $vat = $price * sfConfig::get('app_vat_percentage');
    $totalAmount=$vat+$price;
    ?>
    <input type="hidden" value="<?php echo  $vat;  ?>" name="vat" />
    <input type="hidden" value="<?php echo  $totalAmount;  ?>" name="totalAmount" />
    <input type="hidden" value="<?php echo  $newNumber;  ?>" name="newnumber" />
    <input type="hidden" value="<?php echo  $countrycode;  ?>" name="countrycode" />
    <ul class="fl col changenumber">
        <li>
            <label>New mobile number:</label>
            <label><?php echo  $newNumber;  ?></label><br />
        </li>
       
                           <li>
         <?php if($customer->getBusiness()){ ?>        <label>Name of contact person:</label>  <?php }else{  ?>    <label>Customer Name:</label>   <?php  } ?>
            <label><?php if($customer->getBusiness()){ echo  $customer->getLastName(); }else{  echo $customer->getFirstName()." ".$customer->getLastName();  } ?></label><br />
        </li>
        <li>
            <label>Old mobile number:</label>
            <label><?php echo  $customer->getMobileNumber(); ?></label><br />
        </li>
<!--        <li>
            <label>Product Detail</label>
            <label><?php //echo $product->getDescription(); ?></label><br />
        </li>-->
        <li>
            <label>Amount:</label>
            
            <label><?php echo   number_format($product->getRegistrationFee(),2); echo sfConfig::get('app_currency_code');  ?></label><br />
        </li>
         <li>
            <label>IVA:</label>
            <label><?php echo number_format($vat, 2);echo sfConfig::get('app_currency_code'); ?></label><br />
        </li>
         <li>
            <label>Total:</label>
            <label><?php echo   number_format($totalAmount,2); echo sfConfig::get('app_currency_code');  ?></label><br />
        </li>
        <li style="margin-left:188px"><input type="submit" name="Pay" value="Submit" /><br /></li>
    </ul>
    </form>
    <div class="clr"></div>
</div>