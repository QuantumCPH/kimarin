<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer' => $customer, 'section' => __('Call History'))) ?>
<script type="text/javascript">
    jQuery(function() {

        jQuery( "#startdate" ).datepicker({ maxDate: '0m +0w', dateFormat: 'dd-mm-yy' });
        jQuery( "#enddate" ).datepicker({ maxDate: '0m +0w', dateFormat: 'dd-mm-yy'});


    });
</script>

<div class="alert_bar">
        <?php echo __('Your call history will be updated 5 - 10 minutes after you have finalised your call.') ?>
    </div>
<div class="left-col">
    <?php include_partial('navigation', array('selected' => 'callhistory', 'customer_id' => $customer->getId())) ?>
    
    <?php if ($customer->getC9CustomerNumber()): ?>
            <div style="clear: both;"></div>
            <span style="margin: 20px;">
                <center>

                    <form action="/index.php/customer/c9Callhistory" method="post">
                        <INPUT TYPE="submit" VALUE="<?php echo __('Se LandNCall AB Global opkaldsoversigt') ?>">
                    </form>
                </center>
            </span>
    <?php endif; ?>
            <div class="split-form">
                <form action="<?php //echo url_for(sfConfig::get('app_customer_url').'customer/callhistory')  ?>" id="searchform" method="POST" name="searchform" >

                    <div class="dateBox-pt">
                        <div class="formRow-pt" style="float:left;">
                            <label class="datelable"><?php echo __('From');?>:</label>
                            <input type="text"   name="startdate" autocomplete="off" id="startdate" style="width: 110px;" value="<?php echo @$fromdate1 ? $fromdate1 : date('d-m-Y', strtotime('-15 days')); ?>" />
                        </div>
                        <div class="formRow-pt1" style="float:left;margin-left:7px;">
                            &nbsp;<label class="datelable"><?php echo __('To');?>:</label>
                            <input type="text"   name="enddate" autocomplete="off" id="enddate" style="width: 110px;" value="<?php echo @$todate1 ? $todate1 : date('d-m-Y'); ?>" />
                        </div>
                        <div class="formRow-pt1" style="float:left;margin-left:7px;">

                            <span style="margin-left:10px;"><input type="submit" name="sök" value="<?php echo __('Search')?>" class="submitBtn" /></span>
                        </div>
                    </div>

                </form><br /><br />
                <div class="fl col">

            <?php ?>

            <?php
            $unid = $customer->getUniqueid();
            $cuid = $customer->getId();



            $cp = new Criteria();
            $cp->add(CustomerProductPeer::CUSTOMER_ID, $cuid);
            $custmpr = CustomerProductPeer::doSelectOne($cp);
            $p = new Criteria();
            $p->add(ProductPeer::ID, $custmpr->getProductId());
            $products = ProductPeer::doSelectOne($p);
            $pus = 0;

            $pus = $products->getProductCountryUs();


            if ($pus == 1) {
            ?>
                        <?php } else {

 ?>                 
                      <div class="callhistoryheadings"><h2><h2><?php echo __("Subscription Charges"); ?> </h2></div><br />
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="callhistory">
                        <tr>
                            <td width="26%" class="title"><?php echo __('Date and time') ?></td>
                            <td class="title" width="32%"><?php echo __('Description') ?></td>
                          <td width="42%" align="right" class="title" style="text-align:right !important;"><?php echo __('Amount') ?></td>
                      </tr>

                        <?php
                        $tilentaCallHistryResult = Telienta::callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59', false, 4);
                        if(count($tilentaCallHistryResult)>0){
                        foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                         ?>


                            <tr>
                                <td><?php echo date("d-m-Y H:i:s", strtotime($xdr->bill_time)); ?></td>
                                <td><?php echo $xdr->CLD; ?></td>
                                <td align="right"><?php echo  number_format($xdr->charged_amount,2); ?><?php echo sfConfig::get('app_currency_code');?></td>
                            </tr>
                            <?php } }else {

                                echo __('There are currently no  records to show.');

                            } ?>

                        </table><br/><br/>
                        
                    <div class="callhistoryheadings"><h2><?php echo __("Other events"); ?> </h2></div><br />
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="callhistory">
                        <tr>
                            <td class="title"><?php echo __('Date and time') ?></td>
                            <td class="title" width="40%"><?php echo __('Description') ?></td>
                                <td class="title" aligin="right" style="text-align: right;"><?php echo __('Amount') ?></td>
                            </tr>
                        <?php
                        $tilentaCallHistryResult = Telienta::callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59', false, 1);
                        if(count($tilentaCallHistryResult)>0){
                        foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                         ?>


                            <tr>
                                <td><?php echo date("d-m-Y H:i:s", strtotime($xdr->bill_time)); ?></td>
                                <td><?php echo __($xdr->CLD); ?></td>
                                <td aligin="right" style="text-align: right;"><?php echo number_format($xdr->charged_amount,2); ?>&nbsp;<?php echo sfConfig::get('app_currency_code')?></td>
                            </tr>
                            <?php } }else {

                                echo __('There are currently no call records to show.');

                            } ?>
                        </table><br/><br/>

<!--                                           <div class="callhistoryheadings"><h2><?php echo __("Payment History"); ?> </h2></div><br />
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="callhistory">
                        <tr>
                            <td class="title"><?php echo __('Date and time') ?></td>
                            <td class="title" width="40%"><?php echo __('Description') ?></td>
                                <td class="title"><?php echo __('Amount') ?></td>
                            </tr>
                        <?php
                        $tilentaCallHistryResult = Telienta::callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59', false, 2);
                        if(count($tilentaCallHistryResult)>0){
                        foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                         ?>


                            <tr>
                                <td><?php echo date("Y-m-d H:i:s", strtotime($xdr->bill_time)); ?></td>
                                <td><?php echo __($xdr->CLD); ?></td>
                                <td><?php echo $xdr->charged_amount*-1; ?>&nbsp;<?php echo sfConfig::get('app_currency_code')?></td>
                            </tr>
                            <?php } }else {

                                echo __('There are currently no call records to show.');

                            } ?>
                        </table><br/><br/>-->
                    <div class="callhistoryheadings"><h2><?php echo __("Calls"); ?> </h2></div><br />
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="callhistory">
                            <tr>
                                <td width="16%" class="title"><?php echo __('Date and time') ?></td>
                                <td class="title" width="31%"><?php echo __('Destination number') ?></td>
                                <td width="15%" class="title"><?php echo __('Duration') ?></td>
<!--                                <td width="5%" class="title"><?php echo __('IVA'); echo '('.sfConfig::get('app_vat').')'; ?></td>-->
                                <td width="17%" align="right" class="title"><?php echo __('Cost') ?></td>
                              <td width="24%" class="title"><?php echo __('Description') ?></td>
                          </tr>

<?php
                            $amount_total = 0;
                            $tilentaCallHistryResult = Telienta::callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59');
                          foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
?>
                               <tr>
                                    <td><?php echo date("Y-m-d H:i:s", strtotime($xdr->connect_time)); ?></td>
                                    <td><?php echo $xdr->CLD; ?></td>
                                    <td><?php
                                $callval = $xdr->charged_quantity;
                                if ($callval > 3600) {

                                    $hval = number_format($callval / 3600);

                                    $rval = $callval % 3600;

                                    $minute = date('i', $rval);
                                    $second = date('s', $rval);

                                    $minute = $minute + $hval * 60;

                                    echo $minute . ":" . $second;
                                } else {


                                    echo date('i:s', $callval);
                                } ?></td>
<!--                            <td align="right"><?php echo number_format($xdr->charged_amount / 4, 2); ?></td>-->
                            <td align="right" style="text-align: right;"><?php echo number_format($xdr->charged_amount, 2);
                                $amount_total+= number_format($xdr->charged_amount, 2); ?>&nbsp;<?php echo sfConfig::get('app_currency_code')?></td>
                            <td><?php
                                $typecall = substr($xdr->account_id, 0, 1);
                                if ($typecall == 'a') {
                                    echo "Int.";
                                }
                                if ($typecall == '4') {
                                    echo "R";
                                }
                                if ($typecall == 'c') {
                                      $cbtypecall = substr($xdr->account_id, 2);
                                    if ($xdr->CLD ==$cbtypecall) {
                                        echo "Cb M";
                                    } else {
                                        echo "Cb S";
                                    }
                                } 
                                /*if ($typecall == 'c') {
                                    if ($xdr->CLI == '**24') {
                                        echo "Cb M";
                                    } else {
                                        echo "Cb S";
                                    }
                                }*/ ?> </td>
                        </tr>

                        <?php
                                $callRecords = 1;
                            }
                        ?>

<?php if (count($tilentaCallHistryResult) == 0): ?>
                        <tr>
                            <td colspan="5"><p><?php echo __('There are currently no call records to show.') ?></p></td>
                                </tr>
<?php else: ?>
                                    <tr>
                                        <td colspan="3" align="right"><strong><?php echo __('Total') ?></strong></td><td align="right"><?php echo number_format($amount_total, 2) ?><?php echo sfConfig::get('app_currency_code')?></td><td>&nbsp;</td>
                                    </tr>
<?php
                                    endif;

                                    if ($pus == 0) {
?>
                                    <tr><td colspan="5" style="text-align: right"><?php echo __('All amounts excl. IVA.') ?></td></tr> <?php } ?>
                                </table>

                        <?php } ?>

                </div>
            </div> <!-- end split-form -->
        </div> <!-- end left-col -->
<?php include_partial('sidebar') ?>