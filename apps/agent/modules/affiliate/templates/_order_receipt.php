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
	<p><?php echo __('Dear Customer') ?></p>
	<p>
	<?php echo __('Thank you for ordering <b>%1%</b> and becoming %2% Customer. We welcome you to a new and huge mobile world. ',array('%1%'=>$order->getProduct()->getName(),'%2%'=>sfConfig::get('app_site_title'))); echo __('Your customer number is '); ?>  <?php echo $customer->getUniqueid();?>. <?php echo __(' There, you can use in your dealings with customer service')?>
	</p>
	<p>
	<?php echo __('With <b>%1%</b>, you can easily call your friends and family for free.', array('%1%'=>$order->getProduct()->getName())) ?></p>
	<p>
	<?php echo __('Below is the receipt of the product indicated.') ?>
	</p>
	<br />
<?php endif; ?>
<table width="600px">
<tr style="border:0px solid #fff">

		<td colspan="4" align="right" style="text-align:right; border:0px solid #fff"><?php echo image_tag(sfConfig::get('app_web_url').'images/logo.png',array('width' => '170'));?></td>

	</tr>
</table>
<table class="receipt" cellspacing="0" width="600px">
	
  <tr bgcolor="#CCCCCC" class="receipt_header">   	
    <th colspan="3"><?php echo __('Order Receipt')?> <?php if ($order->getIsFirstOrder()==1)
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
    <th><?php echo __('Order Number') ?>: <?php echo $order->getId() ?></th>
  </tr>
  <tr> 
    <td colspan="4" class="payer_summary">
      <?php echo __('Customer number') ?>:   <?php echo $customer->getUniqueId(); ?><br/>
      <?php echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName())?><br/>
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
      <br /><br />
      <?php if($registered_customer_name) {?>
      <b><?php echo __('Registered friend')?>:</b> <?php echo $registered_customer_name;?>
      <br /><br />
      <?php } ?>


      <?php    $unid=$customer->getUniqueid(); ?>
     <?php     $customer->getMobileNumber()    ?>
      <?php echo __('Mobile number') ?>: <br />
      <?php echo $customer->getMobileNumber() ?>
  </td>
  </tr>
  <tr class="order_summary_header" bgcolor="#CCCCCC"> 
    <td><?php echo __('Date') ?></td>
    <td><?php echo __('Description') ?></td>
    <td><?php echo __('Quantity') ?></td>
  <td align="right" style="padding-right: 65px;"><?php echo __('Amount') ?><!--  (<?php //echo sfConfig::get('app_currency_code');?>)--></td>
  </tr>
<?php  if($customerorder==1){?>  
  <tr> 
    <td><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td>
    <?php 
        echo __("Kimarin Starter Package");
    
    ?>
	</td>
    <td><?php echo $order->getQuantity() ?></td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($order->getProduct()->getRegistrationFee(),2); ?><?php echo sfConfig::get('app_currency_code');?></td>
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
    <td align="right" style="padding-right: 65px;"><?php echo number_format($subtotal = $order->getProduct()->getPrice()+$order->getProduct()->getRegistrationFee(),2); ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
   <tr class="footer">
    <td>&nbsp;</td>
    <td><?php echo __('Delivery charges') ?>  </td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($postalcharge,2) ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('IVA') ;?><!-- (<?php //echo $vat==0?'0%':sfConfig::get('app_vat') ?>)--></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php echo number_format($vat,2) ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  <?php } else{  //////// for Othere orders
  ?>
  

  <tr> 
    <td><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td>
   
    
     <?php   if($TDI==6){
         
         echo __('Airtime refill');
         }elseif($TDI==10){
             $vat = 0;
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
    <td><?php echo $order->getQuantity() ?></td>
    <td align="right" style="padding-right: 65px;"><?php  if($TDI==6){
        
       // $vat += 1;
       echo number_format($subtotal = $order->getExtraRefill()-$vat,2);
         }elseif($TDI==10){
          echo number_format($subtotal = $order->getExtraRefill()-$vat,2); 
    }else{ echo number_format($subtotal = $transaction->getAmount()-$vat,2); } ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  
  
    <?php   if($TDI==6){   ?>
                           <tr> 
    <td><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td>
      <?php  echo __('Airtime bonus');   ?>
	</td>
    <td><?php echo $order->getQuantity() ?></td>
    <td align="right" style="padding-right: 65px;">-<?php echo number_format($subtotal = $order->getExtraRefill()-$vat,2); ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
                         
              <?php       }elseif($TDI==10){  ?>
                             
                    <tr> 
    <td><?php echo $order->getCreatedAt('d-m-Y') ?></td>
    <td>
      <?php  echo __('Bonus Invite a Friend');   ?>
	</td>
    <td><?php echo $order->getQuantity() ?></td>
    <td align="right" style="padding-right: 65px;">-<?php echo number_format($subtotal = $order->getExtraRefill()-$vat,2) ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>            
                          
                 <?php    }   ?>
  <tr>
  	<td colspan="4" style="border-bottom: 2px solid #c0c0c0;">&nbsp;</td>
  </tr>
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('Subtotal') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php   if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{ echo number_format($subtotal,2); } ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>  
  <tr class="footer"> 
    <td>&nbsp;</td>
    <td><?php echo __('IVA');?><!-- (<?php //echo $vat==0?'0%':sfConfig::get('app_vat') ?>)--></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php    if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{   echo number_format($vat,2); } ?><?php echo sfConfig::get('app_currency_code');?></td>
  </tr>
  <?php    
  }  ?>
  <tr class="footer">
    <td>&nbsp;</td>
    <td><?php echo __('Total') ?></td>
    <td>&nbsp;</td>
    <td align="right" style="padding-right: 65px;"><?php   if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{ echo number_format($transaction->getAmount(),2); } ?><?php echo sfConfig::get('app_currency_code')?></td>
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

