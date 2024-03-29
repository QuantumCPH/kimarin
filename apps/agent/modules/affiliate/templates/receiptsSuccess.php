<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<style type="text/css">
	table {
		margin-bottom: 10px;
	}
	
	table.summary td {
		font-size: 1.2em;
		font-weight: normal;
	}
</style>
<link href="<?php echo sfConfig::get('app_web_url');?>css/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo sfConfig::get('app_web_url');?>js/jquery-1.6.2.min.js"></script>
  <script src="<?php echo sfConfig::get('app_web_url');?>js/jquery-ui.min.js"></script>
<script>
jQuery(function() {
      
	jQuery( "#startdate" ).datepicker({ minDate: '-2m +0w',maxDate: '0m +0w', dateFormat: 'dd-mm-yy' });
	jQuery( "#enddate" ).datepicker({ minDate: '-2m +0w',maxDate: '0m +0w', dateFormat: 'dd-mm-yy'});
	
		
});
</script>
<div class="report_container">    
    <table cellpadding="0" cellspacing="0" class="tbldatefilter" align="center">
    <tr><td><h1><?php echo __('Date Filter') ?></h1></td></tr>
    <tr>
    <td>
        <form action="" id="searchform" method="POST" name="searchform" >
        <div class="dateBox-pt">
          <div class="formRow-pt" style="float:left;">
            <label class="datelable" style="text-align:left">From:</label>
            <input type="text"   name="startdate" autocomplete="off" id="startdate" style="width: 110px;"  value="<?php echo date('d-m-Y',strtotime($startdate));    ?>"  />
          </div>
          <div class="formRow-pt" style="float:left;">
              <label class="datelable" style="text-align:left">To:</label>
            <input type="text"   name="enddate" autocomplete="off" id="enddate" style="width: 110px;"    value="<?php echo date('d-m-Y',strtotime($enddate));    ?>"   />
          </div>
           <span><input type="submit" name="søg" value="Search" class="datefilterBtn" /></span>
        </div>
        </form>
    </td>
        </tr>
</table>
</div>
<div class="report_container">
  <div id="sf_admin_container"><h1><?php echo __('Registration Receipts') ?> (<?php echo (count($registrations));echo __(" receipts"); ?>)</h1></div>
        
  <div class="borderDiv"> 
   <table cellspacing="0" width="100%" class="summary">	
	<tr>
		<th>&nbsp;</th>
		<th><?php echo __('Date and time') ?></th>
		<th><?php echo __('Customer Name/Contact Person Name ') ?></th>
                        	<th><?php echo __('Company Name') ?></th>
		<th><?php echo __('Mobile Number') ?></th>
		<th><?php echo __('Transaction Amount') ?></th>
		<th><?php echo __('Description') ?></th>
		<th><?php echo __('Show Receipt') ?></th>
		
	</tr>
	<?php 
	$i = 0;
	foreach($registrations as $registration):
	?>
	<tr <?php echo 'class="'.($i%2 == 0?'odd':'even').'"' ?>>
		<td><?php echo ++$i ?>.</td>
             
		<td><?php echo $registration->getCreatedAt('d-m-Y H:i:s');  ?></td>
		<td><?php 
			$customer = CustomerPeer::retrieveByPK($registration->getCustomerId());
			//$customer2 = CustomerPeer::retrieveByPK(72);
			//echo $transaction->getCustomerId();
		  if($customer->getBusiness()){
                             echo $customer->getLastName();
                        }else{
			echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName());
                        }
			?>
            
		</td>
                  <td><?php  if($customer->getBusiness()){
                            echo  $customer->getFirstName();
                        } ?></td>
		<td><?php echo $customer->getMobileNumber()?></td>
		<td align="right" style="text-align:right;padding-right: 50px;">
			<?php // echo BaseUtil::format_number($registration->getAmount()) 
                          echo number_format($registration->getAmount(),2);
                        ?><?php echo sfConfig::get('app_currency_code');?>
		</td>
		<td>
		<?php echo __($registration->getDescription()) ?>
		</td>
		<td><a href="#" class="receipt" onclick="javascript: window.open('<?php echo url_for('affiliate/printReceipt?tid='.$registration->getId(), true) ?>')"><?php echo __('Receipt') ?></a>
		</td>
		
	</tr>
	<?php endforeach; ?>
        
</table>

  </div>
  
  <div id="sf_admin_container"><h1><?php echo __('Refill Receipts') ?> (<?php echo (count($refills));echo __(" receipts"); ?>)</h1></div>
        
  <div class="borderDiv"> 

   <table cellspacing="0" width="100%" class="summary">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo __('Date and time') ?></th>
		<th><?php echo __('Customer Name/Contact Person Name ') ?></th>
                        	<th><?php echo __('Company Name') ?></th>
		<th><?php echo __('Mobile Number') ?></th>
		<th style="text-align:right;padding-right: 25px;"><?php echo __('Transaction Amount') ?></th>
		<th><?php echo __('Description') ?></th>
		<th><?php echo __('Show Receipt') ?></th>

	</tr>
	<?php
	$i = 0;
	foreach($refills as $refill):
	?>
	<tr <?php echo 'class="'.($i%2 == 0?'odd':'even').'"' ?>>
		<td><?php echo ++$i ?>.</td>

		<td><?php echo $refill->getCreatedAt('d-m-Y H:i:s');  ?>
		<td><?php
			$customer = CustomerPeer::retrieveByPK($refill->getCustomerId());
			//$customer2 = CustomerPeer::retrieveByPK(72);
			//echo $transaction->getCustomerId();
			  if($customer->getBusiness()){
                              echo $customer->getLastName();
                        }else{
			echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName());
                        }
			?>

		</td>
                  <td><?php  if($customer->getBusiness()){
                            echo  $customer->getFirstName();
                        } ?></td>
		<td><?php echo $customer->getMobileNumber()?></td>
		<td style="text-align:right;padding-right: 25px;">
			<?php echo number_format($refill->getAmount(),2);?><?php echo sfConfig::get('app_currency_code');?>
		</td>
		<td>
		<?php echo __($refill->getDescription()) ?>
		</td>
		<td><a href="#" class="receipt" onclick="javascript: window.open('<?php echo url_for('affiliate/printReceipt?tid='.$refill->getId(), true) ?>')"> <?php echo __('Receipt') ?></a>
		</td>

	</tr>
	<?php endforeach; ?>

</table>
        
  </div>
   
      
 <div id="sf_admin_container"><h1><?php echo __('Mobile Number Change Receipts') ?> (<?php echo (count($numberchanges))." receipts" ?>)</h1></div>

  <div class="borderDiv">
   <table cellspacing="0" width="100%" class="summary">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo __('Date and time') ?></th>
		<th><?php echo __('Customer Name/Contact Person Name ') ?></th>
                        	<th><?php echo __('Company Name') ?></th>
		<th><?php echo __('Mobile Number') ?></th>
		<th style="text-align:right;padding-right: 25px;"><?php echo __('Transaction Amount') ?></th>
		<th><?php echo __('Description') ?></th>
		<th><?php echo __('Show Receipt') ?></th>

	</tr>
	<?php
	$i = 0;
	foreach($numberchanges as $numberchange):
	?>
	<tr <?php echo 'class="'.($i%2 == 0?'odd':'even').'"' ?>>
		<td><?php echo ++$i ?>.</td>

		<td><?php echo $numberchange->getCreatedAt('d-m-Y H:i:s');  ?>
		<td><?php
			$customer = CustomerPeer::retrieveByPK($numberchange->getCustomerId());
			//$customer2 = CustomerPeer::retrieveByPK(72);
			//echo $transaction->getCustomerId();
		  if($customer->getBusiness()){
                             echo $customer->getLastName(); 
                        }else{
			echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName());
                        }
			?>

		</td> 
                <td><?php  if($customer->getBusiness()){
                            echo  $customer->getFirstName();
                        } ?></td>
		<td><?php echo $customer->getMobileNumber()?></td>
		<td style="text-align:right;padding-right: 25px;">
			<?php echo number_format($numberchange->getAmount(),2);?><?php echo sfConfig::get('app_currency_code');?>
		</td>
		<td>
		<?php echo $numberchange->getDescription() ?>
		</td>
		<td>
                    <a href="#" class="receipt" onclick="javascript: window.open('<?php echo url_for($targetUrl.'affiliate/printReceipt?tid='.$numberchange->getId(), true) ?>')"> <?php echo __('Receipt') ?></a>
		</td>

	</tr>
	<?php endforeach; ?>

</table>
      
   </div>
 
 
 <div id="sf_admin_container"><h1><?php echo __('New Sim Sale Receipt') ?> (<?php echo (count($newSimSales))." receipts" ?>)</h1></div>

  <div class="borderDiv">
   <table cellspacing="0" width="100%" class="summary">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo __('Date and time') ?></th>
		<th><?php echo __('Customer Name/Contact Person Name ') ?></th>
                        	<th><?php echo __('Company Name') ?></th>
		<th><?php echo __('Mobile Number') ?></th>
		<th style="text-align:right;padding-right: 25px;"><?php echo __('Transaction Amount') ?></th>
		<th><?php echo __('Description') ?></th>
		<th><?php echo __('Show Receipt') ?></th>

	</tr>
	<?php
	$i = 0;
	foreach($newSimSales as $newSimSale):
	?>
	<tr <?php echo 'class="'.($i%2 == 0?'odd':'even').'"' ?>>
		<td><?php echo ++$i ?>.</td>

		<td><?php echo $newSimSale->getCreatedAt('d-m-Y H:i:s');  ?>
		<td><?php
			$customer = CustomerPeer::retrieveByPK($newSimSale->getCustomerId());
			//$customer2 = CustomerPeer::retrieveByPK(72);
			//echo $transaction->getCustomerId();
			  if($customer->getBusiness()){
                               echo $customer->getLastName();
                        }else{
			echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName());
                        }
			?>

		</td>
                 <td><?php  if($customer->getBusiness()){
                            echo  $customer->getFirstName();
                        } ?></td>
		<td><?php echo $customer->getMobileNumber()?></td>
		<td style="text-align:right;padding-right: 25px;">
			<?php echo number_format($newSimSale->getAmount(),2);?><?php echo sfConfig::get('app_currency_code');?>
		</td>
		<td>
		<?php echo $newSimSale->getDescription() ?>
		</td>
		<td>
                    <a href="#" class="receipt" onclick="javascript: window.open('<?php echo url_for($targetUrl.'affiliate/printReceipt?tid='.$newSimSale->getId(), true) ?>')"> <?php echo __('Receipt') ?></a>
		</td>

	</tr>
	<?php endforeach; ?>

</table>
    </div>
      
      
 <div id="sf_admin_container"><h1><?php echo __('Change Product  Receipts') ?> (<?php echo (count($changeProducts))." receipts" ?>)</h1></div>

  <div class="borderDiv">
   <table cellspacing="0" width="100%" class="summary">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo __('Date and time') ?></th>
<th><?php echo __('Customer Name/Contact Person Name ') ?></th>
                        	<th><?php echo __('Company Name') ?></th>
		<th><?php echo __('Mobile Number') ?></th>
		<th style="text-align:right;padding-right: 25px;"><?php echo __('Transaction Amount') ?></th>
		<th><?php echo __('Description') ?></th>
		<th><?php echo __('Show Receipt') ?></th>

	</tr>
	<?php
	$i = 0;
	foreach($changeProducts as $changeProduct):
	?>
	<tr <?php echo 'class="'.($i%2 == 0?'odd':'even').'"' ?>>
		<td><?php echo ++$i ?>.</td>

		<td><?php echo $changeProduct->getCreatedAt('d-m-Y H:i:s');  ?>
		<td><?php
			$customer = CustomerPeer::retrieveByPK($changeProduct->getCustomerId());
			//$customer2 = CustomerPeer::retrieveByPK(72);
			//echo $transaction->getCustomerId();
                        if($customer->getBusiness()){
                          echo $customer->getLastName();
                        }else{
			echo sprintf("%s %s", $customer->getFirstName(), $customer->getLastName());
                        }
			?>
		</td>
                <td><?php  if($customer->getBusiness()){
                            echo  $customer->getFirstName();
                        } ?></td>
		<td><?php echo $customer->getMobileNumber()?></td>
		<td style="text-align:right;padding-right: 25px;">
			<?php echo number_format($changeProduct->getAmount(),2);?><?php echo sfConfig::get('app_currency_code');?>
		</td>
		<td>
		<?php echo $changeProduct->getDescription() ?>
		</td>
		<td>
                    <a href="#" class="receipt" onclick="javascript: window.open('<?php echo url_for($targetUrl.'affiliate/printReceipt?tid='.$changeProduct->getId(), true) ?>')"> <?php echo __('Receipt') ?></a>
		</td>

	</tr>
	<?php endforeach; ?>

</table>
  </div>
<!--        <p class="pTotal"><?php //echo __('Total Receipts for transactions:') ?> <?php echo (count($registrations)+count($refills)+count($numberchanges)) ?></p>-->

</div>