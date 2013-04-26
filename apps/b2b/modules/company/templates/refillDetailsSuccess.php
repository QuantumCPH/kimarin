<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<div id="sf_admin_container"><h1><?php echo __('Refill Detail') ?></h1>
<?php if ($sf_user->hasFlash('message')): ?>
<div class="save-ok">
  <h2><?php echo __($sf_user->getFlash('message')) ?></h2>
</div>
<?php endif; ?>
</div>
<form id="refillform" name="refillform" method="post" enctype="multipart/form-data" action="refillTransaction">
    <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign">     
       <tr>
           <td style="padding: 11px 0 3px 5px;font-weight:bold;" width="100" valign="top">Amount:<br /><small>(Airtime)</small></td>
           <td class="tdcss">
               <?php echo number_format($refillamount,2);?><b><?php echo sfConfig::get('app_currency_code');?></b>
               <input type="hidden" name="refillamount" value="<?php echo number_format($refillamount,2);?>" />
           </td>           
       </tr>
       <tr>
           <td style="padding: 11px 0 0 5px;font-weight:bold;" width="100" valign="top">VAT</td>
           <td class="tdcss">
               <?php echo number_format($vat,2)?><b><?php echo sfConfig::get('app_currency_code');?></b>
               <input type="hidden" name="vat" value="<?php echo number_format($vat,2);?>" />
           </td>
       </tr>
       <tr>
           <td style="padding: 11px 0 0 5px;font-weight:bold;" width="100" valign="top">Total Amount</td>
           <td class="tdcss">
               <?php echo number_format($refilltotal,2);?><b><?php echo sfConfig::get('app_currency_code');?></b>
               <input type="hidden" name="refilltotal" value="<?php echo number_format($refilltotal,2);?>" />
           </td>
       </tr>
       <tr><td></td><td><div class="nextbtndiv" style="margin-left:4px;">
                    <input type="submit" name="submit" value="Refill" />
                </div></td></tr>
    </table>
    <input type="hidden" name="amount" id="total" value="<?php echo number_format($refilltotal,2);?>" />
    <input type="hidden" name="cmd" value="_xclick" /> 
    <input type="hidden" name="no_note" value="1" />
    <input type="hidden" name="lc" value="UK" />
    <input type="hidden" name="currency_code" value="<?php echo sfConfig::get('app_currency_symbol')?>" />
    <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
    <input type="hidden" name="firstName" value="<?php //echo $company->getCustomer()->getFirstName();?>"  />
    <input type="hidden" name="lastName" value="<?php //echo $order->getCustomer()->getLastName();?>"  />
    <input type="hidden" name="payer_email" value="<?php echo $company->getEmail();?>"  />
    <input type="hidden" name="rm" value="2" />        
</form>

