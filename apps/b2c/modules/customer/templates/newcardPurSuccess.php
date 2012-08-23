<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Purchase New Sim Card') ) ) ?>
<?php

            //if(isset ($_POST['email']) && isset ($_POST['name'])&& isset ($_POST['message'] ))
            //{?>
<!--<div class="alert_bar">

              <?php //echo __("Your invitation to ").$_POST['name'].__(" has been sent."); ?>



</div>-->
<?php //}

?>
<div class="left-col">
    <?php include_partial('navigation', array('selected'=>'', 'customer_id'=>$customer->getId())) ?>
<br/><br/>&nbsp;<br/>&nbsp;<br/>
<div class="split-form">
  <div class="fl">
        <form  id="form1" method="POST" action="<?php //echo url_for('customer/tellAFriend', true) ?>">
            <table width="100%" cellspacing="20">
                <tr>
                    <td><?php echo __("SIM Type:") ?></td>
                    <td>
                        <select name="sim_type" id="employee_sim_type_id" class="required" style="width:190px;" onchange="this.form.submit()">
                            <option value="">Select SIM Type</option>
                            <?php foreach($simtypes as $simtype){  ?>
                            <option value="<?php echo $simtype->getTitle(); ?>" <?php echo ($simtype->getTitle()==$sim)?'selected="selected"':''?>><?php echo $simtype->getTitle(); ?></option>
                            <?php   }  ?>
                        </select>
                    </td>
                </tr>
             <?php if($price!=''){ ?>
                <tr>
                    <td><?php echo __("Product Price:") ?></td>
                    <td><?php echo $price; ?></td>
                </tr>
                <tr>
                    <td><?php echo __("IVA:") ?></td>
                    <td><?php echo $vat ?></td>
                </tr>
                <tr>
                    <td><?php echo __("Total Amount:") ?></td>
                    <td><?php echo $total; ?></td>
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
            </table><br />

            
            <input type="submit" class="butonsigninsmall" style="margin-left: 0px !important;" name="buy" value="<?php echo __('Buy') ?>" />
        </form>
    </div>
  </div>
</div>
 <?php include_partial('sidebar') ?>

