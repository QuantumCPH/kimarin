<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>

<div class="left-col">

    <?php include_partial('navigation', array('selected' => 'refill', 'customer_id' => $customer->getId())) ?>
    <div class="split-form">
        <div style="width:500px;">
            <h2><?php echo __("Payment");?></h2>
            <br/>
            <br/>
            <?php
            echo $product->getName();
            echo "<br/>";
            echo $product->getRegistrationFee();
            echo "<br/>";
            echo $product->getBonus();
            echo "<br/>";
            echo "IVA: " . $product->getRegistrationFee() * sfConfig::get('app_vat_percentage');
            echo "<br/>";
             echo "Airtime: " . $product->getInitialBalance(); if($product->getBonus()>0){ echo " PLUS ".$product->getBonus()." ".sfConfig::get('app_currency_code'); echo " = ".($product->getBonus()+$product->getInitialBalance())." ".sfConfig::get('app_currency_code'); }
            echo "<br/>";
            echo "to be paid on Paypal:" . $product->getRegistrationFee() * (sfConfig::get('app_vat_percentage') + 1);
            ?>
            <br/>
            <br/>
            <form method="post" action="<?php echo $target; ?>customer/sendRefilToPaypal">
                <input type="hidden" value="<?php echo $queryString; ?>" name="qstr" />
                <?php if($customerBalance+$order->getExtraRefill()<=250){ ?>
                <div style="margin-top:40px;">
                    <input type="submit" class="butonsigninsmall" name="button" style="width:101px;cursor: pointer;float: left; margin-left: -5px !important; margin-top: -5px;"  value="<?php echo __('Refill') ?>" />
                </div>
                <?php }else{ ?>

                <?php echo __("Sorry! You Cant do payment as your balance will excede from 250"); ?>
                <?php } ?>
            </form>
        </div>
    </div>
</div>
  <?php include_partial('sidebar') ?>