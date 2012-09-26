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

 
<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Dear customer') ?></p>
   <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Your %1% account has been blocked',array('%1%' => sfConfig::get('app_site_title'))) ?>.</p>
         <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Your customer number is') ?>: <?php   echo $customer->getUniqueId();   ?></p>

<p style='font-weight: bold; font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Please contact %1% Customer Support to re-open your account',array('%1%' => sfConfig::get('app_site_title'))) ?>.</p>
 <p style='font-weight: bold; font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
        <br><?php echo __('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>
 <p style="font-weight: bold;"><?php echo __('Best regards,'); ?></p>
<p style="font-weight: bold;"><?php echo  sfConfig::get('app_site_title'); ?></p> 

