<?php
use_helper('I18N');
use_helper('Number');
$vat=$order->getProduct()->getRegistrationFee()*sfConfig::get('app_vat_percentage')
?>
<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Dear customer') ?>&nbsp;<?php //echo $customer->getFirstName()." ".$customer->getLastName();?></p>

<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Thank you for your order of <b>%1%</b>.', array('%1%'=>$order->getProduct()->getName())) ?></p>

<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('The products you have ordered will be sent by mail shortly. Your customer number is '); echo $customer->getUniqueid();?>.</p>

<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Best regards,') ?></p>

<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __(sfConfig::get('app_site_title')) ?></p>
<br />

<table width="600px">
<tr style="border:0px solid #fff;">

		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>

	</tr>
</table>
<table class="receipt" cellspacing="0" width="600px" style='border: 2px solid #ccc;font-family:"Times New Roman", Times, serif;'>
   <tr bgcolor="#CCCCCC" class="receipt_header" style='font-weight: bold;text-transform: uppercase;font-family:"Times New Roman", Times, serif;font-size: 14px;'> 
    <th colspan="3" align="left" style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __('Order Receipt') ?></th>
    <th style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __('Order Number') ?>: <?php echo $order->getId() ?></th>
  </tr>
  <tr>
    <td colspan="4" class="payer_summary" style='font-size: 14px;font-family:"Times New Roman", Times, serif;'>
      <?php echo __('Customer number')?>:   <?php echo $customer->getUniqueId(); ?><br/>
      <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?><br/>
      <?php echo $customer->getAddress() ?><br/>
      <?php echo sprintf('%s %s',$customer->getPoBoxNumber(), $customer->getCity() ) ?><br/>
      <br /><br />
      <?php echo __('Mobile number') ?>: <br />
      <?php echo $customer->getMobileNumber() ?><br />
      <?php if($agent_name!=''){ echo __('Agent Name') ?>:  <?php echo $agent_name; } ?>
    </td>
  </tr>
  <tr class="order_summary_header" bgcolor="#CCCCCC" style='font-weight: bold;font-size: 14px;text-transform: uppercase;'> 
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __('Date') ?></td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __('Description') ?></td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __('Quantity') ?></td>
    <td align="right" style='padding-right: 65px;font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __('Amount') ?></td>
  </tr>
  <tr> 
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo __($transaction->getDescription());?></td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo $order->getQuantity() ?></td>
    <td align="right" style='padding-right: 65px;font-size: 14px;font-family:"Times New Roman", Times, serif;'><?php echo number_format($subtotal =$transaction->getAmount()-$vat,2) ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr><td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td></tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><strong><?php echo __('Subtotal') ?></strong></td>
    <td>&nbsp;</td>
    <td align="right" style='font-size: 14px;padding-right: 65px;font-family:"Times New Roman", Times, serif;'><strong><?php echo number_format($subtotal,2) ?><?php echo sfConfig::get('app_currency_code')?></strong></td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><strong><?php echo __('IVA') ?></strong></td>
    <td>&nbsp;</td>
    <td align="right" style='font-size: 14px;padding-right: 65px;font-family:"Times New Roman", Times, serif;'><strong><?php echo number_format($vat,2) ?><?php echo sfConfig::get('app_currency_code')?></strong></td>
  </tr>
  <tr class="footer">
    <td>&nbsp;</td>
    <td style='font-size: 14px;font-family:"Times New Roman", Times, serif;'><strong><?php echo __('Total') ?></strong></td>
    <td>&nbsp;</td>
    <td align="right" style='font-size: 14px;padding-right: 65px;font-family:"Times New Roman", Times, serif;'><strong><?php echo number_format($subtotal+$vat,2) ?><?php echo sfConfig::get('app_currency_code')?></strong></td>
  </tr>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer" style=''>
    <td class="payer_summary" colspan="4" style='font-weight:normal; white-space: nowrap;font-size: 14px;font-family:"Times New Roman", Times, serif;'> 
    <?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')))?> </td>
  </tr>
</table><br />
<p style='font-size: 14px;font-weight: bold;font-family:"Times New Roman", Times, serif;'>
	<?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
        <br><?php echo __('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>
        