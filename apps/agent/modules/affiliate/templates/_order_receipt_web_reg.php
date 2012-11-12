<?php
use_helper('I18N');
use_helper('Number');
?>
<style>
	p {
		margin: 8px auto;       
	}
	
	table.receipt {
		width: 600px;

		
		border: 2px solid #ccc;
	}
	
	table.receipt td, table.receipt th {
		padding:5px;
		font-size:14px;
	}
	
	table.receipt th {
		text-align: left;
	}
	
	table.receipt .payer_details {
		padding: 10px 0;
	}
	
	table.receipt .receipt_header, table.receipt .order_summary_header {
		font-weight: bold;
		text-transform: uppercase;
	}
	
	table.receipt .footer
	{
		font-weight: bold;
	}
	
	
</style>

<?php
$wrap_content  = isset($wrap)?$wrap:false;

//wrap_content also tells  wheather its a refill or 
//a product order. we wrap the receipt with extra
// text only if its a product order.

 ?>
 
<?php if($wrap_content): ?>
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Dear customer') ?>&nbsp;<?php //echo $customer->getFirstName()." ".$customer->getLastName();?></p>
	
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('Thank you for your order of <b>%1%</b>.', array('%1%'=>$order->getProduct()->getName())) ?>
	</p>
	
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('The products you have ordered will be sent by mail shortly. Your customer number is '); echo $customer->getUniqueid();?>. <?php //echo __(' There, you can use in your dealings with customer service'); ?></p>
	
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('Best regards,') ?>
	</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __(sfConfig::get('app_site_title')) ?>
	</p>
	<br />
<?php endif; ?>
<table width="600px">
	<tr style="border:0px solid #fff">
		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>
	</tr>
</table>
<table class="receipt" cellspacing="0" width="600px" style='border: 2px solid #ccc;'>
  <tr bgcolor="#CCCCCC" class="receipt_header" style="font-weight: bold;text-transform: uppercase;"> 
    <th colspan="3" align="left" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Order Receipt');?><span style='font-family:"Times New Roman", Times, serif;font-size: 12px;'><?php echo " (".$order->getProduct()->getName()." [".__('Registration')."])" ?></span></th>
    <th style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Order number') ?>: <?php echo $order->getId() ?></th>
  </tr>
  <tr> 
    <td colspan="4" class="payer_summary" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
      <?php echo __('Customer number') ?>:   <?php echo $customer->getUniqueId(); ?><br/>
     <?php    if($customer->getBusiness()){  ?>
      <?php echo $customer->getFirstName(); ?><br/>
        <?php echo $customer->getNiePassportNumber(); ?><br/>
<?php      }else{  ?>
          <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?><br/>
      
  <?php     }   ?>
      <?php echo $customer->getAddress() ?><br/>
      <?php echo sprintf('%s %s', $customer->getPoBoxNumber(), $customer->getCity()) ?><br/>
      <?php 
	  /*$eC = new Criteria();
	  $eC->add(EnableCountryPeer::ID, $customer->getCountryId());
	  $eC = EnableCountryPeer::doSelectOne($eC);
	  echo $eC->getName();*/
      ?>    <br /><br />
      <?php echo __('Mobile number') ?>: <br />
      <?php echo $customer->getMobileNumber() ?><br />

      <?php if($agent_name!=''){ echo __('Agent Name') ?>:  <?php echo $agent_name; } ?>
    </td>
  </tr>
  <tr class="order_summary_header" bgcolor="#CCCCCC" style="font-weight: bold;text-transform: uppercase;"> 
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Date') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Description') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Quantity') ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Amount') ?><!--(<?php echo sfConfig::get('app_currency_code')?>)--></td>
  </tr>
  <tr> 
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
    <?php 
         echo __("Kimarin Starter Package");
    
    ?>
	</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getQuantity() ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($order->getProduct()->getRegistrationFee(),2); ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <?php if($order->getProduct()->getPrice() > 0){?><tr>
    <td></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
    <?php
         echo __("Product price");

    ?>
	</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getQuantity() ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($order->getProduct()->getPrice(),2); ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <?php } ?>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('Subtotal') ?></td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($subTotal = $order->getProduct()->getPrice()+$order->getProduct()->getRegistrationFee(),2) ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>  
  <tr class="footer">
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('Delivery charges') ?>  </td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($postalcharge,2) ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr class="footer">
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('IVA') ?> <!--(<?php echo $vat==0?'0%':sfConfig::get('app_vat') ?>)--></td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($vat,2) ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
     
  <tr class="footer">
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('Total') ?></td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($subTotal+$vat+$postalcharge,2) ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer">
    <td class="payer_summary" colspan="4" style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight:normal; white-space: nowrap;'> 
    <?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')))?> </td>
  </tr>
</table>
<br />
<p style='font-weight: bold;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
        <br /><?php echo __('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br /><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>