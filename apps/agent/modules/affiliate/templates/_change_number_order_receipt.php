<?php
use_helper('I18N');
use_helper('Number');
?>
<table width="600px">
    <tr style="border:0px solid #fff">
        <td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>
    </tr>
</table>
<table cellspacing="0" width="600px" style="border: 2px solid #ccc;">
    <tr bgcolor="#CCCCCC" style="font-weight: bold;text-transform: uppercase;">
        <th colspan="3" align="left" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Order Receipt') ?></th>
        <th style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Order number') ?>: <?php echo $order->getId() ?></th>
    </tr>
    <tr>
        <td colspan="4" style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
          <?php echo __('Customer number') ?>:   <?php echo $customer->getUniqueId(); ?><br/>
          <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?><br/>
          <?php echo $customer->getAddress() ?><br/>
          <?php echo sprintf('%s %s', $customer->getPoBoxNumber(),$customer->getCity() ) ?><br/>
          <?php
          $eC = new Criteria();
          $eC->add(EnableCountryPeer::ID, $customer->getCountryId());
          $eC = EnableCountryPeer::doSelectOne($eC);
         // echo $eC->getName();
          ?>
          <br /><br />
          <?php echo __('Mobile number') ?>: <br />
          <?php echo $customer->getMobileNumber() ?><br />
          <?php if($agent_name!=''){ echo __('Agent Name') ?>:  <?php echo $agent_name; } ?>
        </td>
    </tr>
    <tr bgcolor="#CCCCCC" style="font-weight: bold;text-transform: uppercase;">
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Date') ?></td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Description') ?></td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Quantity') ?></td>
        <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('Amount') ?></td>
    </tr>
    <tr>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getCreatedAt('d-m-Y') ?></td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __($transaction->getDescription());?></td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo $order->getQuantity() ?></td>
        <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo number_format($subtotal = $transaction->getAmount()-$vat,2); ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr>
        <td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('Subtotal') ?></td>
        <td>&nbsp;</td>
        <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo number_format($subtotal,2);  ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('IVA') ?></td>
        <td>&nbsp;</td>
        <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo number_format($vat,2); ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td style='font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo __('Total') ?></td>
        <td>&nbsp;</td>
        <td align="right" style='padding-right: 65px;font-family:"Times New Roman", Times, serif;font-size: 14px;font-weight: bold;'><?php echo number_format(($subtotal+$vat),2); ?><?php echo sfConfig::get('app_currency_code');?></td>
    </tr>
    <tr>
        <td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4" style='font-weight:normal; white-space: nowrap;font-family:"Times New Roman", Times, serif;font-size: 14px;'><?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')));?> </td>
    </tr>
</table>
<p style='font-weight: bold;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
    <?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
    <br><?php echo __('E-mail') ?>:&nbsp;
    <a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
    <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>