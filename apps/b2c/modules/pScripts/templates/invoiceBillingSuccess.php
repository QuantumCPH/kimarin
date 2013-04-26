<?php
	use_helper('Number');
	ob_start();
	$totalcost = 0.00;
	$totalSubFee = 0.00;
	$totalPayments = 0.00;
	$totalEventFee = 0.00;
    //$call_rate_table = CallRateTablePeer::doSelect(new Criteria());
?>
<html>
    <head>
        <title><?php echo date('dmy') . $invoice_meta->getId() ?>- <?php echo $company_meta->getName() ?></title>
        <style>
		    body{
			  font-family:Verdana, Arial, Helvetica, sans-serif;
			  font-size:11px;
			}
			h2{
			   font-size:17px;
			   color:#000!important;
			 }
            fieldset {
                 -moz-border-radius:10px;
                 border-radius: 10px;
                 -webkit-border-radius: 10px;
                 border: 2px solid #000;
             }
             .border{
                border-bottom: 1px solid #000 !important;
                border-top:1px solid #000 !important;
             }
             .borderleft{
                    border-left: 1px solid #000 !important;
             }
             .borderright{
                    border-right:1px solid #000 !important;
             }
             .padding{
                padding-top:10px!important;
                padding-bottom:10px!important;
                padding-left:5px!important;
             }
             .padbot{
                    padding-bottom:10px;
             }
             .trbg{
                    font-weight:bold;
                    background-color:#CCCCCC;
             }
             .table{
                    padding-top:30px;
             }
             .table td{
                    padding-left:5px;
                    padding-top:5px;
             }
             table td{
                    border:none!important;
             }
        </style>
    </head>
    <body>
		<table width="90%" cellpadding="0" cellspacing="0" border="0" style="padding:30px;margin:0 auto;">
	<tr>
		<td colspan="2">
			<fieldset>
			<table width="100%">
				<tr>
					<td> <?php echo image_tag(sfConfig::get('app_web_url').'images/logo.jpg');?></td>
					<td>
						
					</td>
					<td align="right" style="padding-right:10px;">
						<b>Kimarin Europe S.L.</b><br />
						Urb. Golf R&iacute;o Real<br />Greenlife Village ad. 11 29603 Marbella
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>

	<tr>
		<td width="61%">
	  <table width="60%" border="0" style="padding-top:30px">
				<tr>
					<td>Invoice Number:</td>
					<td><?php echo $invoice_meta->getId(); ?></td>
				</tr>
				<tr>
					<td>Invoice Period:</td>
					<td><?php echo $invoice_meta->getBillingStartingDate('d M.') . ' - ' . $invoice_meta->getBillingEndingDate('d M.') ?></td>
				</tr>
				<tr>
					<td>Invoice Date:</td>
					<td><?php echo $invoice_meta->getCreatedAt('d M. Y') ?></td>
				</tr>
				<tr>
					<td>Due date:</td>
					<td><?php echo $invoice_meta->getDueDate('d M. Y') ?></td>
				</tr>
				<tr>
					<td>Company VAT:</td>
					<td><?php echo $company_meta->getVatNo() ?></td>
				</tr>

			</table>
		</td>
  <td width="39%" align="right">
<table width="50%" border="0" cellpadding="0" cellspacing="0">
				<tr style="background-color:#CCCCCC;">
					<td colspan="2" style="padding:5px">
						<?php echo $company_meta->getName() ?><br />
                    	<?php echo $company_meta->getAddress() ?><br />
                    	<?php echo $company_meta->getPostCode() ?>,
                    	<?php echo $company_meta->getCity() ?><br />
	    				Att: <?php echo $company_meta->getContactName() ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" class="table">
				<tr><td colspan="3" class="padbot"><h2>Voice Calls</h2></td></tr>
				<tr height="40px" class="trbg" bgcolor="#CCCCCC" style="background:#CCCCCC">
                  <td width="21%" class="border borderleft">Date &amp; Time</td>				  
				  <td width="21%" class="border">Destination</td>
				  <td width="16%" class="border">Duration</td>
                  <td width="21%" class="border ">Country</td>
				  <td width="21%" class="border borderright" align="right" style="padding-right:10px">Charged Amount</td>
			  </tr>
			<?php
				$billings = array();
				$ratings = array();
				$bilcharge = 00.00;
				$invoiceFlag = false;
				$count = 1;
				$billing_details = array();
				foreach ($employees as $employee) {
					$subFlag = false;
					$regFlag = false;
					$billingFlag = false;

					$prdPrice = 0;
					$startdate = strtotime($billing_start_date);
					$enddate = strtotime($billing_end_date);

					$bc = new Criteria();
					$bc->add(EmployeeCustomerCallhistoryPeer::PARENT_TABLE, "employee");
					$bc->addAnd(EmployeeCustomerCallhistoryPeer::PARENT_ID, $employee->getId());
					$bc->addAnd(EmployeeCustomerCallhistoryPeer::CONNECT_TIME, " connect_time > '" . $billing_start_date . "' ", Criteria::CUSTOM);
					$bc->addAnd(EmployeeCustomerCallhistoryPeer::DISCONNECT_TIME, " disconnect_time < '" . $billing_end_date . "' ", Criteria::CUSTOM);
					//$bc->addGroupByColumn(EmployeeCustomerCallhistoryPeer::PHONE_NUMBER);
//					$bc->addGroupByColumn(EmployeeCustomerCallhistoryPeer::COUNTRY_ID);
					if (EmployeeCustomerCallhistoryPeer::doCount($bc) > 0) {
						$billingFlag = true;
					}
					if ($billingFlag) {
			?>
				<tr>
					<td colspan="5" class="padding"><strong><?php echo 'From Number: ' . $employee->getMobileNumber() ?></strong></td>
				</tr>
			<?php
				$invoiceFlag = true;
				$billings = EmployeeCustomerCallhistoryPeer::doSelect($bc);
				foreach ($billings as $billing) {?>
				<tr>
                    <td><?php echo $billing->getConnectTime();?></td>					
					<td><?php echo $billing->getPhoneNumber(); ?></td>
						<td>
							<?php
								$calculated_cost = $billing->getChargedAmount();
								$call_duration = EmployeeCustomerCallhistoryPeer::getTotalCallDuration($employee, $billing);
							?>
							<?php echo $call_duration ?>						</td>
                     <td><?php echo $billing->getCountry()->getName()//.'-'.$billing->getCountryId(); ?></td>       
						<td align="right" style="padding-right:10px">
							<?php echo  number_format($calculated_cost, 2) ;
							$temp_cost = $calculated_cost;
							?><?php echo sfConfig::get('app_currency_code')?></td>
					</tr>				
					<?php  $totalcost += $temp_cost;
			}
		 }
	}
	?>
                 <tr>
				  <td colspan="5">
                    <hr />
                  </td>
			   </tr>
               <tr>
				  <td colspan="5">
                     <table cellpadding="3" cellspacing="0" width="100%">
                         <tr>
                              <td width="87%" align="right"><strong>Subtotal :</strong></td>
                           <td width="13%" align="right" style="padding-right:10px"><?php echo number_format($totalcost,2);?><?php echo sfConfig::get('app_currency_code')?></td>
                       </tr>
                     </table>
                  </td>
			   </tr>
              <tr>
				  <td colspan="5">
                    <hr />
                  </td>
			   </tr> 
	</table>
	  </td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" class="table">
				<tr><td colspan="3" class="padbot"><h2>Subscriptions</h2></td></tr>
				<tr  class="trbg" height="40px">
                <td width="21%" class="border borderleft">Date & Time</td>
				  <td width="21%" class="border">Mobile Number</td>
				  <td width="39%" class="border">Description</td>
				  <td width="19%" class="border borderright" align="right" style="padding-right:10px">Amount</td>
			  </tr>
			   <?php
				 foreach ($employees as $emps) {
				  $cSub = new Criteria();
				  $cSub->add(OdrsPeer::PARENT_TABLE,'employee');
				  $cSub->addAnd(OdrsPeer::PARENT_ID,$emps->getId());
				  $cSub->addAnd(OdrsPeer::I_SERVICE,4);
				  $cSub->addAnd(OdrsPeer::BILL_START,$billing_start_date, Criteria::GREATER_EQUAL);
                                  $cSub->addAnd(OdrsPeer::BILL_END,$billing_end_date, Criteria::LESS_EQUAL);
				  $scount = OdrsPeer::doCount($cSub);
				  if($scount > 0){
                                      $invoiceFlag = true;
				   	$subscriptions =  OdrsPeer::doSelect($cSub);
    				foreach($subscriptions as $subs){
				?>
				<tr>
                   <td><?php echo $subs->getConnectTime();?></td>
				   <td><?php echo $emps->getMobileNumber();?></td>
				   <td><?php echo $subs->getDescription();?></td>
				   <td align="right" style="padding-right:10px"><?php echo number_format($subs->getChargedAmount(),2);$totalSubFee += $subs->getChargedAmount();?><?php echo sfConfig::get('app_currency_code')?></td>
				</tr>
				<?php
					}
				  }
				 } //end employee second foreach loop
			    ?>
                <tr>
				  <td colspan="4">
                    <hr />
                  </td>
			   </tr>
                <tr>
                  <td colspan="4">
                    <table cellpadding="3" cellspacing="0" width="100%">
                       <tr>
                         <td colspan="2" align="right"><strong>Subtotal :</strong></td>
                          <td width="13%" align="right" style="padding-right:10px"><?php echo number_format($totalSubFee,2);?><?php echo sfConfig::get('app_currency_code')?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
				  <td colspan="4">
                    <hr />
                  </td>
			   </tr>
			</table>
	  </td>
	</tr>
	<?php  if ($otherCount > 0) { 
            $invoiceFlag = true; ?>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" class="table" style="padding-bottom:30px;">
				<tr><td colspan="2" class="padbot"><h2>Other Events</h2></td></tr>
				<tr class="trbg" height="40px">
                <td width="41%" class="border borderleft">Date & Time</td>
				  <td width="41%" class="border">Description</td>
				  <td width="18%" class="border borderright" align="right" style="padding-right:10px">Amount</td>
			  </tr>
                <?php foreach($otherevents as $event){?>
				<tr>
                    <td><?php echo $event->getConnectTime();?></td>
                    <td><?php echo $event->getDescription();?></td>
                    <td align="right" style="padding-right:10px"><?php echo number_format($event->getChargedAmount(),2);$totalEventFee += $event->getChargedAmount();?><?php echo sfConfig::get('app_currency_code')?>
					</td>
                </tr>
				<?php }?>
                <tr>
				  <td colspan="3">
                    <hr />
                  </td>
			   </tr>
                 <tr>
                  <td colspan="3">
                    <table cellpadding="3" cellspacing="0" width="100%">
                      <tr>
                         <td width="88%" align="right"><strong>Subtotal :</strong></td>
                        <td width="12%" align="right" style="padding-right:10px"><?php echo number_format($totalEventFee,2);?><?php echo sfConfig::get('app_currency_code')?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
			</table>
	  </td>
	</tr>
   <?php
	  }
          $invoice_cost = ($invoiceFlag) ? $invoice_cost : '0.00'; 
          
	   $totalcost = $totalcost + $totalSubFee + $totalEventFee;
   ?>
   <tr height="30px">
		<td colspan="2"><hr /></td>
	</tr>
	<tr>
      <td colspan="2" align="right">
         <table cellpadding="0" cellspacing="0" width="100%">
            <tr>
            <td width="88%" align="right" class="padding" style="padding-right:10px"><strong>Total cost:</strong></td>
            <td width="12%" align="right" style="padding-right:10px"><?php echo number_format($totalcost, 2); ?><?php echo sfConfig::get('app_currency_code');?></td>
           </tr>
            <tr>
            <td class="padding" align="right" style="padding-right:10px"><strong>Invoice Fees:</strong></td>
            <td align="right" style="padding-right:10px"><?php echo number_format($invoice_cost, 2);echo sfConfig::get('app_currency_code'); ?></td>
            </tr>
            <tr>
            <td colspan="2" ><hr /></td>
            </tr>
            <tr>
            <td class="padding" align="right" style="padding-right:10px"><strong>Total Inc. invoice cost:</strong></td>
            <td align="right" style="padding-right:10px"><?php echo number_format($net_cost = $totalcost + $invoice_cost, 2);echo sfConfig::get('app_currency_code'); ?></td>
            </tr>
            <tr>
            <td class="padding" align="right" style="padding-right:10px"><strong>Vat:</strong></td>
            <td align="right" style="padding-right:10px"><?php echo number_format($moms = $net_cost * ($vatPercentage/100), 2);echo sfConfig::get('app_currency_code'); ?></td>
            </tr>
            <tr>
            <td colspan="2" ><hr /></td>
            </tr>
            <tr>
            <td class="padding" align="right" style="padding-right:10px"><strong>Total Inc. Vat:</strong></td>
            <td align="right" style="padding-right:10px">
            <?php echo number_format($net_cost = $net_cost+$moms, 2);echo sfConfig::get('app_currency_code');
                  util::saveTotalPayment($invoice_meta->getId(),$net_cost); ?>
            </td>
            </tr>
            <tr>
            <td colspan="2" ><hr /></td>
            </tr>
            <tr>
            <td class="padding" align="right" style="padding-right:10px"><strong>Previous Balance:</strong></td>
            <td align="right" style="padding-right:10px"><?php echo number_format($netbalance , 2);echo sfConfig::get('app_currency_code');  ?></td>
            </tr>
            <tr>
            <td class="padding" align="right" style="padding-right:10px"><strong>Total Payable Balance:</strong></td>
            <td align="right" style="padding-right:10px"><?php echo number_format($net_payment = $net_cost + $netbalance , 2);echo sfConfig::get('app_currency_code');  ?></td>
            </tr>
         </table>
      </td>
    </tr>
	<tr height="30px">
		<td colspan="2" class="border borderleft borderright" style="background-color:#CCCCCC;">&nbsp;</td>
	</tr>
	<?php if ($payCount > 0) {
	$vat_in=0.00;
        $vatIncluded=0.00;
        $vatinc=0.00;
	?>
  	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" class="table">
				<tr><td colspan="2" class="padbot"><h2>Payment History</h2></td></tr>
				<tr class="trbg" height="40px">
                    <td class="border borderleft">Date & Time</td>
					<td class="border">Description</td>
					<td class="border" align="right">Airtime</td>
					<td class="border" align="right">Vat</td>
					<td  class="border borderright" align="right" style="padding-right:10px">Total </td>
				</tr>
				<?php foreach($payments as $payment){?>
				<tr>
                                   <td><?php echo $payment->getConnectTime();?></td>
				   <td><?php echo $payment->getDescription();?></td>
				   <td align="right"><?php echo number_format($chargedAmount = $payment->getChargedAmount(),2);echo sfConfig::get('app_currency_code');
				         $totalPayments += $chargedAmount;?>                   </td>
				   <td align="right"><?php echo number_format($payment->getChargedAmount()*$payment->getChargedVatValue(),2);echo sfConfig::get('app_currency_code');
                                   $vat_in += $payment->getChargedAmount()*$payment->getChargedVatValue();
                                   ?></td>
				   <td align="right" style="padding-right:10px"><?php echo number_format($vatIncluded =$payment->getVatIncludedAmount(),2);echo sfConfig::get('app_currency_code');
                                   $vatinc +=$vatIncluded ;
                                   ?></td>
				</tr>				
				<?php }?>
                            <tr>
                                <td colspan="2" align="right"><strong>Total:</strong></td>
                                <td align="right"><strong><?php echo number_format($totalPayments, 2); ?><?php echo sfConfig::get('app_currency_code'); ?>
                                        </strong></td>
                                <td align="right"><strong><?php echo number_format($vat_in, 2); ?><?php echo sfConfig::get('app_currency_code'); ?>
                                        </strong></td>
                                <td align="right" style="padding-right:10px"><strong><?php echo number_format($vatinc, 2); ?><?php echo sfConfig::get('app_currency_code'); ?>
                                        </strong></td>
                            </tr>
			</table>
	  </td>
	</tr>
	<?php } ?>
	         <?php if ($invoiceCount > 0) { ?>
                <tr>
                    <td colspan="2">
                        <table width="100%" cellpadding="0" cellspacing="0" class="table" style="padding-bottom:30px">
                            <tr><td colspan="2" class="padbot"><h2>Previous Invoices</h2></td></tr>
                            <tr height="40px" class="trbg">
                                <td width="28%" class="border borderleft">Bill Duration</td>
                                <td width="36%" class="border borderleft">Invoice Total </td>
                                <td width="36%" class="border borderleft borderright">Total Payable Balance </td>
                            </tr>
                <?php foreach ($preInvoices as $preInvoice) { ?>
                                <tr>
                                    <td><?php echo $preInvoice->getBillingStartingDate("M d"); ?> - <?php echo $preInvoice->getBillingEndingDate("M d"); ?></td>
                                    <td><?php echo number_format($preInvoice->getTotalPayment(), 2); ?><?php echo sfConfig::get('app_currency_code') ?></td>
                                    <td><?php echo number_format($preInvoice->getTotalPayableBalance(), 2); ?><?php echo sfConfig::get('app_currency_code') ?></td>
                                </tr>
    <?php } ?>
                        </table>
                    </td>
                </tr>
<?php } ?>
	 <tr>
		<td colspan="2"><br />
			<fieldset>
			<table width="100%">
				<tr>
					<td>Tel: +34 000 0000</td>
					<td>Fax: +34 000 0000</td>
					<td>Email: support@kimarin.es</td>
					<td>Web: www.kimarin.es</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
</body>
</html>
<?php
	$html_content = ob_get_contents();
	util::saveHtmlToInvoice($invoice_meta->getId(), $html_content);
	$ci = new Criteria();
	$ci->add(InvoicePeer::ID,$invoice_meta->getId());
	$in = InvoicePeer::doSelectOne($ci);
	$in->setSubscriptionFee($totalSubFee);
	$in->setRegistrationFee($totalEventFee);
	$in->setPaymentHistoryTotal($totalPayments);
	$in->setMoms($moms);
	$in->setTotalusage($totalcost);
	$in->setCurrentBill($net_cost);
	$in->setInvoiceCost($invoice_cost);
	$in->setNetPayment($net_cost); 
        $in->setTotalPayableBalance($net_payment); /// previous balance and current invoice payment
	$in->setInvoiceStatusId(1);
	$in->save();

	$fileName = str_replace("/", "_", $in->getCompany()->getName());
	$fileName = str_replace(" ", "_", $fileName);

	$fileName = $in->getId().'-'.$fileName;

	util::html2pdf($invoice_meta->getId(),$in->getInvoiceHtml(),$fileName);
?>
