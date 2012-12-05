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

<table width="600px">
	<tr style="border:0px solid #fff">
		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_site_url').'images/logo.png',array('width' => '170'));?></td>
	</tr>
</table>

<table class="receipt" cellspacing="0" width="600px" style='font-family:"Times New Roman", Times, serif;'>
  <tr>
    <td colspan="4" class="payer_summary" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
      <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo 'Dear customer' ?>,</p>
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo $message_body; ?>
	</p>

        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo 'Best regards,' ?>
	</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo sfConfig::get('app_site_title') ?>
	</p>
	<br /><br />
      </td>
  </tr>  
  <tr>
  	<td colspan="4" style='border-bottom: 2px solid #c0c0c0;'>&nbsp;</td>
  </tr>
  <tr class="footer">
    <td class="payer_summary" colspan="4" style='font-weight:normal; white-space: nowrap;font-family:"Times New Roman", Times, serif;font-size: 14px;'> 
    <?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')))?> </td>
  </tr>
</table>


<p style='font-weight: bold;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo ('If you have any inquiries please contact '. sfConfig::get('app_site_title') .' Customer Support.'); ?>
        <br><?php echo('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br><?php echo ('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>
