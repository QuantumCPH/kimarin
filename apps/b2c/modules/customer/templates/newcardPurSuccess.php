<script type="text/javascript">
    jQuery(function(){
        jQuery('#form').validate({
        });
    });
</script>
<style>
    .padding{ padding: 10px; padding-left: 30px}
</style>
<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Purchase New Sim Card') ) ) ?>

<div class="left-col">
    <?php include_partial('navigation', array('selected'=>'', 'customer_id'=>$customer->getId())) ?>
<br/><br/>&nbsp;<br/>&nbsp;<br/>
<div class="split-form">
  <div class="fl">
        <form id="form" method="POST" action="">
            <table width="100%" cellspacing="0" border="0">
                <tr>
                    <td><?php echo __("SIM type") ?>:</td>
                    <td class="padding">
                        <select name="sim_type" onchange="this.form.submit()" class="required newcard">
                            <option value=""><?php echo __("Select SIM type") ?></option>
                            <?php foreach($simtypes as $simtype){  ?>
                            <option value="<?php echo $simtype->getId(); ?>" <?php echo ($simtype->getId()==$product_id)?'selected="selected"':''?>><?php echo $simtype->getName(); ?></option>
                            <?php   }  ?>
                        </select>
                    </td>
                </tr>
             <?php if($price!=''){ ?>
                <tr>
                    <td><?php echo __("Product price") ?>:</td>
                    <td class="padding"><?php echo $price; ?></td>
                </tr>
                <tr>
                    <td><?php echo __("IVA") ?>:</td>
                    <td class="padding"><?php echo $vat ?></td>
                </tr>
                <tr>
                    <td><?php echo __("Total amount") ?>:</td>
                    <td class="padding"><?php echo $total; ?></td>
                </tr>
                <input type="hidden" name="amount" id="total" value="<?php echo $total; ?>" />
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
                
             <?php }?>
                <tr><td>&nbsp;</td><td class="padding"><input type="submit" class="butonsigninsmall" style="margin-left: 0px !important;" name="buy" value="<?php echo __('Pay') ?>" /></td></tr>
            </table><br />

            
            
        </form>
    </div>
  </div>
</div>
 <?php include_partial('sidebar') ?>

