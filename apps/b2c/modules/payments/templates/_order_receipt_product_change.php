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
 

<table width="600px">
<tr style="border:0px solid #fff">

		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>

	</tr>
</table>
<table class="receipt" cellspacing="0" width="600px">
	
    <?php 
	  $eC = new Criteria();
	  $eC->add(CustomerProductPeer::CUSTOMER_ID, $customer->getId());
          $eC->addAnd(CustomerProductPeer::STATUS_ID,3);
	  $cProduct = CustomerProductPeer::doSelectOne($eC);
          $product=  ProductPeer::retrieveByPK($cProduct->getProductId());
	
          ?> 
   
   <tr>
  	<td colspan="4"  > 
            <?php echo __('Dear'); ?>   <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?>  ,<br/>
 
   <?php echo __('your order for change of product has been successfully implemented.');  ?>
 <?php echo __('Now your current product is'); ?> ( <?php $product->getName();   ?>).
  </td>
  </tr>
  
  
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer">
    <td class="payer_summary" colspan="4" style="font-weight:normal; white-space: nowrap;"> 
    <?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')))?> </td>
  </tr>
</table>






<?php if($wrap_content): ?>
<br />
<p>
<?php
	$c = new  Criteria();
	$c->add(GlobalSettingPeer::NAME, 'expected_delivery_time_agent_order');
	
	$global_setting_expected_delivery = GlobalSettingPeer::doSelectOne($c);
	
	if ($global_setting_expected_delivery)
		$expected_delivery = $global_setting_expected_delivery->getValue();
	else
		$expected_delivery = "3 business days";
?>
<p style="font-weight: bold;">
	<?php echo __('You will receive your package within %1%.', array('%1%'=>$expected_delivery)) ?> 
</p>
<?php endif; ?>

<p style="font-weight: bold;">
	<?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
        <br><?php echo __('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>

<!--<p style="font-weight: bold;"><?php echo __('Cheers') ?></p>

<p style="font-weight: bold;">

<?php echo __('%1% Support',array('%1%'=>sfConfig::get('app_site_title'))) ?>&nbsp;

</p>-->

