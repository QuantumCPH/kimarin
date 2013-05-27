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
   <table width="600px" cellspacing="0" cellpadding="0">
    <tr><td>       
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
               <?php echo $customer->getFirstName(); ?><br /><?php echo $customer->getEmail(); ?>
        </p>
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>APP</p>        
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Dear Customer,</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;text-align: right'>Marbella, <?php echo date("d-m-Y",strtotime($transaction->getCreatedAt()));?> </p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Welcome to Kimarin APP connected to telephone number: <?php echo $transaction->getCustomer()->getMobileNumber();?></p>
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Thank you for your order of Kimarin APP, where you can call friends, family and business partners abroad at very low prices.</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>It is very easy to get started.<br />
            Your Kimarin account balance starts with 1.00 € of free airtime, but you must refill your account within 3 days to be able to continue to use the APP. You can refill your Kimarin account in different ways:</p>
        <ul style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
            <li>Go to  www.kimarin.es and enter MY ACCOUNT</li>
            <li>Via the APP if you use an Android telephone</li>
            <li>Go to a Kimarin dealer</li>
        </ul>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
            For information on price plans, tariffs, etc. go to www.kimarin.es, where you on MY ACCOUNT also can update your personal information, see your call history, payment history, refill your account, change your price plan or use other services.
        </p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Please remember that you pay local mobile call charges in Spain in addition to the Kimarin tariffs.</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Best regards,</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>            
            <?php echo sfConfig::get('app_postal_address_app');?><br /><br />
            Tel.: <?php echo sfConfig::get('app_phone_no');?><br />
            E-mail: <?php echo sfConfig::get('app_support_email_id');?>
        </p>
        <br />
        <hr /><br />
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Estimado client,</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;text-align: right'>Marbella, <?php echo date("d-m-Y",strtotime($transaction->getCreatedAt()));?> </p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Bienvenido a Kimarin APP conectada  al número de teléfono: <?php echo $transaction->getCustomer()->getMobileNumber();?></p>
	<p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Gracias por su solicitud de Kimarin APP, con la cual puede llamar a sus amigos, familia o  compañeros de trabajo por precios muy reducidos.</p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>La aplicación es muy sencilla.<br />
            Su saldo empieza con 1.00€ gratuito para usar en llamadas, pero tiene que cargar su cuenta en los  3 días siguientes para poder seguir usando su APP. Puede cargar su cuenta de diferentes maneras:</p>
        <ul style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
            <li>En la página www.kimarin.es pulsando MI CUENTA</li>
            <li>Por medio del APP si tiene un teléfono Android</li>
            <li>Visitando un Agente de Kimarin</li>
        </ul>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
            Para más información sobre tarifas, visite www.kimarin.es En MI CUENTA  puede  actualizar y corregir sus datos, ver el historial de llamadas efectuadas, los pagos realizados, cargar su cuenta, cambiar el producto contratado al que mejor se ajuste a sus necesidades u otros servicios.
        </p>            
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
           Por favor recuerde que llamando al extranjero, a parte del pago correspondiente a las tarifas de Kimarin, pagará una llamada local con su contrato del móvil existente.
        </p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
             Saludos cordiales,
        </p>
        <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>            
            <?php echo sfConfig::get('app_postal_address_app');?><br /><br />
            Tel.: <?php echo sfConfig::get('app_phone_no');?><br />
            E-mail: <?php echo sfConfig::get('app_support_email_id');?>
        </p>
        
	<br />
      </td></tr>
</table>   
<?php endif; ?>
<table width="600px">
<tr style="border:0px solid #fff">

		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>

</tr>
</table>
<table class="receipt" cellspacing="0" width="600px">
	
  <tr bgcolor="#CCCCCC" class="receipt_header">   	
    <th colspan="3" align="left"><?php echo __('Order Receipt')?> <?php if ($order->getIsFirstOrder()==1)
    {
        ?>
       <span style='font-family:"Times New Roman", Times, serif;font-size: 12px;'><?php echo " (".$order->getProduct()->getName()." [".__('Registration')."])" ?></span>
       <?php

    }
    else
    {
	 
        //  echo __($transaction->getDescription());
        
    }
    ?> </th>
    <th><?php echo __('Order Number') ?>: <?php echo $transaction->getReceiptNo(); ?></th>
  </tr>
  <tr> 
    <td colspan="4" class="payer_summary">
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
	  $eC = new Criteria();
	  $eC->add(EnableCountryPeer::ID, $customer->getCountryId());
	  $eC = EnableCountryPeer::doSelectOne($eC);
	 // echo $eC->getName();
	  //echo $customer->getCountry()->getName() ?> 
      
      <?php  
      $order=CustomerOrderPeer::retrieveByPK($transaction->getOrderId()); 
      $TDI=$transaction->getTransactionDescriptionId();   ?>
      <br />
      <?php    $unid=$customer->getUniqueid(); ?>
     <?php     $customer->getMobileNumber()    ?>
      <?php echo __('Mobile number') ?>: <br />
      <?php echo $customer->getMobileNumber() ?> <br />
          <?php if($agent_name!=''){ echo __('Agent Name') ?>:  <?php echo $agent_name; } ?>
  </td>
  </tr>
  <tr class="order_summary_header" bgcolor="#CCCCCC"> 
    <td><?php echo __('Date') ?></td>
    <td><?php echo __('Description') ?></td>
    <td><?php echo __('Quantity') ?></td>
  <td align="right" style="padding-right: 65px;"><?php echo __('Amount') ?><!--  (<?php //echo sfConfig::get('app_currency_code');?>)--></td>
  </tr>
<?php  if($order->getIsFirstOrder()==1){?>  
  <tr> 
    <td><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td>
    <?php 
        echo __("Kimarin Starter Package");
        //echo $transaction->getDescription();
    ?>
	</td>
    <td><?php echo $order->getQuantity() ?></td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($transaction->getAmount()-$vat,2); //echo number_format($order->getProduct()->getRegistrationFee(),2); ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
<?php if($order->getProduct()->getPrice()> 0){?> 
  <tr>
    <td></td>
    <td>
    <?php
         echo __("Product Price");

    ?>
	</td>
    <td><?php echo $order->getQuantity() ?></td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($order->getProduct()->getPrice(),2); ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
 <?php } ?>  
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('Subtotal') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php echo $subtotal = number_format($transaction->getAmount()-$vat,2); //number_format($subtotal = $order->getProduct()->getPrice()+$order->getProduct()->getRegistrationFee(),2); ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('Delivery charges') ;?><!-- (<?php //echo $vat==0?'0%':sfConfig::get('app_vat') ?>)--></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($postalcharge,2) ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('IVA') ;?><!-- (<?php //echo $vat==0?'0%':sfConfig::get('app_vat') ?>)--></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($vat,2) ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  <?php } ?>
  <tr class="footer">
    <td>&nbsp;</td>
    <td><?php echo __('Total') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($transaction->getAmount(),2);?><?php echo sfConfig::get('app_currency_code')?></td>
  </tr>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer">
    <td class="payer_summary" colspan="4" style="font-weight:normal; white-space: nowrap;"> 
    <?php echo __('%1%',array('%1%'=>sfConfig::get('app_postal_address_bottom')))?> </td>
  </tr>
</table>

<p style='font-weight: bold;font-family:"Times New Roman", Times, serif;font-size: 14px;'>
	<?php echo __('If you have any inquiries please contact %1% Customer Support.',array('%1%' => sfConfig::get('app_site_title'))); ?>
        <br><?php echo __('E-mail') ?>:&nbsp;
	<a href="mailto:<?php echo sfConfig::get('app_support_email_id');?>"><?php echo sfConfig::get('app_support_email_id');?></a>
        <br><?php echo __('Telephone') ?>:&nbsp;<?php echo sfConfig::get('app_phone_no');?>
</p>

