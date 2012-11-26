<style>
    .split-form-btn {
    clear: both;
    float: left;
    padding-top: 20px;
    width: 515px;
}

</style>
<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Payment history')) ) ?>
  <div class="left-col">
    <?php include_partial('navigation', array('selected'=>'paymenthistory', 'customer_id'=>$customer->getId())) ?>
      
	<div class="split-form">
      <div class="fl col">
        <form>
          <ul>
<?php  ?>
            <li>
              <!--Always use tables for tabular data-->
			  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="callhistory">
                <tr>
                  <td width="91" class="title"><strong><?php echo __('Order number') ?></strong></td>
                  <td width="104" class="title" nowrap><strong><?php echo __('Date and time') ?></strong></td>
                  <td width="117" class="title"><strong><?php echo __('Description') ?></strong></td>
                  <td width="103" class="title" align="right"><strong><?php echo __('Amount') ?></strong></td>
                  <td width="82" class="title"><strong><?php echo __('Type') ?></strong></td>
                  <td width="18" class="title"></td>
                </tr>
                <?php 
                $amount_total = 0;
                foreach($transactions as $transaction): ?>
                
                <?php      $order=CustomerOrderPeer::retrieveByPK($transaction->getOrderId());   ?>
                <tr>
                  <td><?php  echo $transaction->getReceiptNo() ?></td>
                  <td ><?php echo  $transaction->getCreatedAt('d-m-Y H:i:s'); ?></td>
                  <td nowrap><?php 
                      $TDI=$transaction->getTransactionDescriptionId();
                  if($transaction->getDescription()=="Registrering inkl. taletid"){
                      echo "SmartSim inkludert Pott";                      
                  }else{
                        if($transaction->getDescription()=="Zapna Refill"){
                          echo __("Refill ".$transaction->getAmount());
                        }else{
                          echo __($tdescription = $transaction->getDescription());  
                        } 
                         if($TDI==6){
                             $tramount=$order->getExtraRefill()/(sfConfig::get('app_vat_percentage')+1);
                              echo "(".number_format($tramount,2).")";
                         
                     }elseif($TDI==10){
                           
                              echo "(".number_format($order->getExtraRefill(),2).")";
                     } 
                  }?></td>
                  <td align="right"><?php
                 
                  
                     if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{
                    echo number_format($transaction->getAmount(),2); $amount_total += $transaction->getAmount(); 
                    
                     }
                    ?>
                            <?php 
//                            if($lang=="pl"){
//                               // echo ('plz');
//                            }else if($lang=="en"){
//                               // echo ('eur');
//                            }else{
                                echo sfConfig::get('app_currency_code');
//                            } ?></td>
                  <td>
                      
                   <?php    // onclick="javascript: window.open('<?php echo url_for('payments/showReceipt?tid='.$transaction->getId(), true) //  ')"  ?>
                      <a href="<?php echo url_for('payments/showReceipt?tid='.$transaction->getId(), true) ?>" class="receipt"    >
                            <?php //echo $tdescription;
                              if(strstr($tdescription, "bonus")){
                                echo __('Bonus');
                              }else{
                                echo $transaction->getAmount()>=0?__('Paid'):__('Charged');  
                              }  
                            ?>
                      </a></td>
 
                 
                     
                 
                 
                </tr>
                <?php endforeach; ?>
                <?php if(count($transactions)==0): ?>
                <tr>
                	<td colspan="5"><p><?php echo __('There are currently no transactions to show.') ?></p></td>
                </tr>
                <?php else: ?>
                <tr>
                	<td colspan="3" align="right"><strong><?php echo __('Total') ?></strong></td>
                        <td  align="left" style="text-align:right;"><?php echo number_format($total,2);//echo number_format($amount_total,2) ?>
                            <?php 
//                            if($lang=="pl"){
//                                echo ('plz');
//                            }else if($lang=="en"){
//                                echo ('eur');
//                            }else{
                                echo sfConfig::get('app_currency_code');
//                            } ?></td><td>&nbsp;</td>
                </tr>	
                <?php endif; ?>
              </table>
            </li>
            <?php if($total_pages>1): ?>
            <li>
            	<ul class="paging">
            	<?php for ($i=1; $i<=$total_pages; $i++): ?>
            		<li <?php echo $i==$page?'class="selected"':'' ?>><a href="<?php echo url_for('customer/refillpaymenthistory?page='.$i) ?>"><?php echo $i ?></a></li>
            	<?php endfor; ?>
            	</ul>
            </li>
            <?php endif; ?>
          </ul>
        </form>
      </div>
    </div> <!-- end split-form -->
  </div> <!-- end left-col -->
  <?php include_partial('sidebar') ?>