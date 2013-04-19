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
                font-family:Verdana, Arial, Helvetica, sans-serif;
                font-size: 12px;
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


<table width="600px">
    <tr style="border:0px solid #fff">
        <td colspan="3" align="left" style="text-align:left; border:0px solid #fff;padding-bottom: 10px;"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.jpg' , array("width"=>'170px'));?></td>
    </tr>
</table>
<table class="receipt" cellspacing="0" width="600px">
    <tr bgcolor="#CCCCCC" class="receipt_header">
        <th colspan="2"><?php echo __('Order Receipt') ?></th>
        <th align="right"><?php echo __('Receipt No.') ?> <?php echo $transaction->getReceiptNo(); ?></th>
    </tr>
    <tr> 
        <td colspan="3" class="payer_summary">
            <?php echo __('VAT Number') ?>:   <?php echo $company->getVatNo(); ?><br/>
            <?php echo sprintf("%s", $company->getName()) ?><br/>
            <?php echo $company->getAddress() ?><br/>
            <?php echo sprintf('%s %s', $company->getPostCode(), $company->getCity()) ?><br/>
            <br /><br />
        </td>
    </tr>
    <tr class="order_summary_header" bgcolor="#CCCCCC">
        <td><?php echo __('Date') ?></td>
        <td><?php echo __('Description') ?></td>
        <td align="right" style="padding-right: 65px;"><?php echo __('Amount') ?></td>
    </tr>
    <tr>
        <td><?php echo $transaction->getCreatedAt('d-m-Y') ?></td>
        <td>
            <?php
            echo $transaction->getDescription();
            ?>
        </td>
        <td align="right" style="padding-right: 65px;"><?php echo number_format($subtotal = $transaction->getExtraRefill(), 2) ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr>
        <td colspan="3" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
    </tr>
    <tr class="footer">
        <td>&nbsp;</td>
        <td><?php echo __('Subtotal') ?></td>
        <td align="right" style="padding-right: 65px;"><?php echo number_format($subtotal, 2) ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr class="footer">
        <td>&nbsp;</td>
        <td><?php echo __('VAT') ?> </td>
        <td align="right" style="padding-right: 65px;"><?php echo number_format($vat, 2) ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr class="footer">
        <td>&nbsp;</td>
        <td><?php echo __('Total') ?></td>
        <td align="right" style="padding-right: 65px;"><?php echo number_format($transaction->getAmount(), 2) ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
</table>
<table width="600px" >
    <tr><td valign="top">
    <p style='font-weight: bold;font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 13px;'>
        <?php echo __('If you have any questions, are you most welcome to contact our customer support through'); ?>&nbsp;
            <?php echo __('e-mail') ?>:&nbsp;
            <a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>, 
            <?php echo __("we are open from Manday-Friday: at. 08.00 - 22.00.")?>
    </p>
    </td></tr>
</table>
