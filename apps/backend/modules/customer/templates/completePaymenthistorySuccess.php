<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
 
              <!--Always use tables for tabular data-->
<div id="sf_admin_container"><h1><?php echo  __('Payment History') ?></h1><br />
<form action="" id="searchform" method="POST" name="searchform" >


                <div class="dateBox-pt">
           <div class="formRow-pt" style="float:left;">
                    <label class="datelable" style="width:35px;margin-top: 3px;">From:</label>
                    <input type="text"   name="startdate" autocomplete="off" id="stdate" style="width: 110px;" value="<?php  if(isset($startdate)){ echo $startdate; }  ?>" />
                </div>
                <div class="formRow-pt1" style="float:left;margin-left:7px;">
                    &nbsp;<label class="datelable" style="width:35px;margin-top: 3px;">To:</label>
                    <input type="text"   name="enddate" autocomplete="off" id="endate" style="width: 110px;" value="<?php   if(isset($enddate)){ echo $enddate; }  ?>" />
                </div>
                <div class="formRow-pt1" style="float:left;margin-left:7px;">
                    &nbsp;<label class="datelable" style="width:20px;margin-top: 3px;">Type </label> <select name="description">
                        <option value="">All</option>
                    <?php  foreach($alltransactions as $alltransaction){  ?>

                    <option value="<?php  echo $alltransaction->getDescription();  ?>" <?php echo ($alltransaction->getDescription()==$description)?'selected="selected"':''?>><?php  echo $alltransaction->getDescription();  ?></option>
                  <?php  }
                    ?>



                    </select>
                 &nbsp;
               <span style="margin-left:10px;"><input type="submit" name="Search" value="Search" class="searchbtn user_external_link" /></span>
                </div>

            </div><br clear="all" />

            </form><br /></div>
<div id="sf_admin_container">
    
    <table width="100%" cellspacing="0" cellpadding="2" class="callhistory tblAlign">
    
    <tr class="headings">
      <th  width="15%"  class="title"><?php echo __('Sr #') ?></th>
      <th  width="15%"  class="title"><?php echo __('Receipt #') ?></th>
      <th  width="20%" class="title"><?php echo __('Date') ?></th>
      <th  width="55%" class="title"><?php echo __('Description') ?></th>
      <th width="10%" class="title"  align="right" style="text-align: right;"><?php echo __('Amount') ?></th>
    </tr>
                <?php 
                $amount_total = 0;
                $incrment=1;
                foreach($transactions as $transaction): ?>
   <?php      $order=CustomerOrderPeer::retrieveByPK($transaction->getOrderId()); 
      $TDI=$transaction->getTransactionDescriptionId();?>
                 <?php
                  if($incrment%2==0){
                   $class= 'class="even"';
                  }else{
                    $class= 'class="odd"';
                      }
 $incrment++;
                  ?>
                <tr <?php echo $class;?>>
                  <td><?php  echo $incrment-1; ?></td>
                  <td><?php echo  $transaction->getReceiptNo();?></td>
                  <td><?php echo  $transaction->getCreatedAt('d-m-Y') ?></td>
                  <td><?php echo $transaction->getDescription(); 
                  if($TDI==6){
                             $tramount=$order->getExtraRefill()/(sfConfig::get('app_vat_percentage')+1);
                              echo "(".number_format($tramount,2).")";
                         
                     }elseif($TDI==10){
                           
                              echo "(".number_format($order->getExtraRefill(),2).")";
                     }  ?></td>
                  <td align="right">
                      
                      
                      <?php 
                        if($TDI==6){
                             echo  "0.00" ;
                         
                     }elseif($TDI==10){
                           echo  "0.00" ;   
                     }else{
                      echo  number_format($transaction->getAmount(),2);  $amount_total += $transaction->getAmount();
                     }
                      ?>
                            <?php 
//                            if($lang=="pl"){
//                                echo ('plz');
//                            }else if($lang=="en"){
//                                echo ('eur');
//                            }else{
                                echo (sfConfig::get('app_currency_code'));
//                            } ?></td>
                
                </tr>
                <?php endforeach; ?>
                <?php if(count($transactions)==0): ?>
                <tr>
                	<td colspan="4"><p><?php echo __('There are currently no transactions to show.') ?></p></td>
                </tr>
                <?php else: ?>
                <tr>
                	<td align="right" colspan="3"><strong>Total</strong></td>
                	<td  align="right"><?php echo  number_format($amount_total,2) ?>
                            <?php 
//                            if($lang=="pl"){
//                                echo ('plz');
//                            }else if($lang=="en"){
//                                echo ('eur');
//                            }else{
                                echo (sfConfig::get('app_currency_code'));
//                            } ?></td>
                </tr>	
                <?php endif; ?>
              </table>
</div>         