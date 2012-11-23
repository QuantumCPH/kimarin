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
	<p><?php echo __('Dear Agent') ?></p>
	
	<p>
	<?php echo __('Thank you for Payment'); ?>
	
	
	
	<?php echo __('Below is the receipt of the order indicated.') ?>
	</p>
	<br />
<?php endif; ?>
        <table width="600px">
<tr style="border:0px solid #fff;">
<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>
	</tr>
</table>
<table class="receipt" cellspacing="0" width="600px">
 <tr bgcolor="#CCCCCC" class="receipt_header"> 
    <th colspan="3"  align="left" ><?php echo __('Order Receipt') ?></th>
    <th><?php echo __('Order No.') ?> <?php echo $order; ?></th>
  </tr>
  <tr>
  <td colspan="4" class="payer_summary">
	<?php echo sprintf("%s", $agent->getName())?><br/>
      <?php echo $agent->getAddress() ?><br/>
      <?php echo sprintf('%s', $agent->getPostCode(),$agent->getCity()) ?><br/>
	
	<br />
	<?php echo __("CVR Number") ?>: <?php echo sprintf('%s', $agent->getCvrNumber()) ?>
	<br />
        <?php echo __("Contact Person") ?>: <?php echo $agent->getFirstName()." ".$agent->getMiddleName()." ".$agent->getLastName(); ?><br/>
  </td>
  </tr>
 
  <tr class="order_summary_header" bgcolor="#CCCCCC"> 
    <td><?php echo __('Date') ?></td>
    <td><?php echo __('Description') ?></td>
    <td><?php echo __('Quantity') ?></td>
    <td align="right" style="padding-right:65px;"><?php echo __('Amount') ?><!-- (<?php //echo sfConfig::get('app_currency_code')?>)--></td>
  </tr>
    <tr> 
    <td><?php echo $createddate ?></td>
    <td>
   <?php 
    if($orderdescription){
               $c = new Criteria();
                $c->add(TransactionDescriptionPeer::ID,$orderdescription);
                $transaction_desc = TransactionDescriptionPeer::doSelectOne($c);
                echo $transaction_desc->getTitle();
           } 
    ?>  
	</td>
    <td>1</td>
    <td align="right" style="padding-right:65px;"><?php   format_number($subtotal=$transaction);  echo number_format($subtotal,2); //($order->getProduct()->getPrice() - $order->getProduct()->getPrice()*.2) * $order->getQuantity()) ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('Subtotal') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right:65px;"><?php echo number_format($subtotal,2); ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
  <td><?php echo __('IVA') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right:65px;"><?php echo number_format($vat,2); ?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr class="footer">
    <td>&nbsp;</td>
    <td><?php echo __('Total') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right:65px;"><?php echo number_format($subtotal,2); ?> <?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
</table>
<p style='font-weight: bold;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
    <?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
    <br><?php echo __('E-mail') ?>:&nbsp;
    <a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
    <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>
