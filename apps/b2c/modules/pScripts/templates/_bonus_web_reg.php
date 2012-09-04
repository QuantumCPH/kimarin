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
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Dear Customer') ?>&nbsp;<?php //echo $recepient_name;//$customer->getFirstName();?>,</p>
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('%1% has added 10.00%2% of airtime to your account balance for inviting a friend to register as a %1% customer. Thank you.',array('%1%'=>sfConfig::get('app_site_title'), '%2%'=>sfConfig::get('app_currency_code'))); ?>
	</p>
<!--        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
            <a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
	</p>-->
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('Best regards,') ?>
	</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __(sfConfig::get('app_site_title')) ?>
	</p>
	<br /><br />
<?php endif; ?>
<table width="600px">
	<tr style="border:0px solid #fff">
		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_site_url').'images/logo.png',array('width' => '170'));?></td>
	</tr>
</table>

<table class="receipt" cellspacing="0" width="600px" style='border: 2px solid #ccc;font-family:"Times New Roman", Times, serif;'>
  
<!-- <tr>
     <td colspan="4" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><b><?php echo __('Receiver of bonus for inviting a friend') ?>:</b> <?php echo $recepient_name; ?></td>
    </tr>-->
   <tr>
  <td  colspan="4"></td>
   </tr>
  <tr bgcolor="#CCCCCC" class="receipt_header" style="font-weight: bold;text-transform: uppercase;">
    <th colspan="3"  align="left"  style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('Order Receipt') ?></th>
    <th style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Order Number') ?>: <?php echo $order->getId() ?></th>
  </tr>
  <tr>
    <td colspan="4" class="payer_summary" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
      <?php echo __('Customer number') ?>:   <?php echo $customer->getUniqueId(); ?><br/>
      <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?><br/>
      <?php echo $customer->getAddress() ?><br/>
      <?php echo sprintf('%s %s', $customer->getPoBoxNumber(), $customer->getCity()) ?>
      <?php	
        $TDI=$transaction->getTransactionDescriptionId(); ?>
      <br /><br />
      <b><?php echo __('Registered friend')?>:</b> <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?>
      <br /><br />
      <?php echo __('Mobile Number') ?>: <br />
      <?php echo $customer->getMobileNumber() ?>   <br/>
      <?php //echo __('Paid Through'); ?> <?php //echo __('Agent'); ?>
    </td>
  </tr>
  <tr class="order_summary_header" bgcolor="#CCCCCC" style="font-weight: bold;text-transform: uppercase;">
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Date') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Description') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Quantity') ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Amount') ?><!--(<?php echo sfConfig::get('app_currency_code') ?>)--></td>
  </tr>
   
   
  <tr> 
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>   
    
     <?php   if($TDI==6){
         echo __('Airtime refill');
         }elseif($TDI==10){
           echo __('Airtime refill');    
    }else{
    
		 if($transaction->getDescription()=="Refill"){
           echo "Refill ".$transaction->getAmount();
        }else{
           echo __($transaction->getDescription());
        }  
    }
    ?>
	</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getQuantity() ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php  if($TDI==6){
       echo number_format($subtotal = $order->getExtraRefill()-$vat,2);
         }elseif($TDI==10){
          echo number_format($subtotal = $order->getExtraRefill()-$vat,2); 
    }else{ echo number_format($subtotal = $transaction->getAmount()-$vat,2); } ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  
  
    <?php   if($TDI==6){   ?>
                           <tr> 
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
      <?php  echo __('Airtime bonus');   ?>
	</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getQuantity() ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'>-<?php echo number_format($subtotal = $order->getExtraRefill()-$vat,2); ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
                         
              <?php       }elseif($TDI==10){  ?>
                             
                    <tr> 
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
      <?php  echo __('Bonus Invite a Friend');   ?>
	</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getQuantity() ?></td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'>-<?php echo number_format($subtotal = $order->getExtraRefill()-$vat,2) ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>            
                          
                 <?php    }   ?>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer" style="font-weight: bold;text-transform: uppercase;"> 
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Subtotal') ?></td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php   if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{ echo number_format($subtotal,2); } ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>  
  <tr class="footer" style="font-weight: bold;text-transform: uppercase;"> 
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('IVA');?><!-- (<?php //echo $vat==0?'0%':sfConfig::get('app_vat') ?>)--></td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php    if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{   echo number_format($vat,2); } ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
 
  <tr class="footer" style="font-weight: bold;text-transform: uppercase;">
    <td>&nbsp;</td>
    <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Total') ?></td>
    <td>&nbsp;</td>
    <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php   if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{ echo number_format($transaction->getAmount(),2); } ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr>
  	<td colspan="4" style='border-bottom: 2px solid #c0c0c0;'>&nbsp;</td>
  </tr>
  <tr class="footer">
    <td class="payer_summary" colspan="4" style='font-weight:normal; white-space: nowrap;font-family:"Times New Roman", Times, serif;font-size: 14px;'> 
    <?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')))?> </td>
  </tr>
</table>
<?php if($wrap_content): ?>
<br />
<p style='font-weight:normal;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
<?php
	$c = new  Criteria();
	$c->add(GlobalSettingPeer::NAME, 'expected_delivery_time_agent_order');
	
	$global_setting_expected_delivery = GlobalSettingPeer::doSelectOne($c);
	
	if ($global_setting_expected_delivery)
		$expected_delivery = $global_setting_expected_delivery->getValue();
	else
		$expected_delivery = "3 business days";
?>
</p> 
<?php endif; ?>

<p style='font-weight: bold;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
        <br><?php echo __('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>
    