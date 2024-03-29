<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<script type="text/javascript">
    jQuery(function() {

        jQuery( "#trigger_startdate" ).hide();
        jQuery( "#trigger_enddate" ).hide();


    });
</script>
<div id="sf_admin_container">
    <div class="alert_bar">
        <?php echo __('Call history is a 5 -10 min delay.') ?>
    </div>

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
    <?php
    } else {

        /* if(isset($_POST['startdate']) && isset($_POST['enddate'])){
          $fromdate=$_POST['startdate']. ' 00:00:00';
          $todate=$_POST['enddate']. ' 23:59:59';
          }else{
          $tomorrow1 = mktime(0,0,0,date("m"),date("d")-15,date("Y"));
          $fromdate=date("Y-m-d", $tomorrow1). ' 00:00:00';
          //$tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
          $todate=date("Y-m-d"). ' 23:59:59';
          } */
        ?>
        <div id="sf_admin_content">
            <ul class="customerMenu" style="margin:10px 0;">
                <li><a class="external_link" href="allRegisteredCustomer">View All Customer</a></li>
                <li><a class="external_link" href="paymenthistory?id=<?php echo $_REQUEST['id']; ?>">Payment History</a></li>
                <li><a class="external_link" href="customerDetail?id=<?php echo $_REQUEST['id']; ?>">Customer Detail</a></li>
            </ul></div>
        <div class="sf_admin_filters">
            <form action="" id="searchform" method="POST" name="searchform">
                <fieldset>
                    <div class="form-row">
                        <label><?php echo __('From'); ?>:</label>
                        <div class="content">
    <!--                            <input type="text" value="2012-05-31"  id="startdates" autocomplete="off" name="startdate" class="hasDatepicker" />-->
                            <input type="text"   name="startdate" autocomplete="off" id="startdate" style="width: 110px;" value="<?php echo @$fromdate1; ?>" />
                            <?php //echo input_date_tag('startdate', $fromdate1, 'rich=true')  ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <label><?php echo __('To'); ?>:</label>
                        <div class="content">
    <!--                                <input id="enddates" class="hasDatepicker" type="text" size="12" value="2012-05-31" name="enddate" autocomplete="off" />-->
                            <input type="text"   name="enddate" autocomplete="off" id="enddate" style="width: 110px;" value="<?php echo @$todate1; ?>" />
                            <?php //echo input_date_tag('enddate', $todate1, 'rich=true')  ?>
                        </div>
                    </div>

                </fieldset>

                <ul class="sf_admin_actions">
                    <li><input type="submit" class="sf_admin_action_filter" value="filter" name="filter"></li>
                </ul>
            </form>
        </div>
        <h1><?php echo 'Subscription Charges'; ?> </h1>
        <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign" border='0'>
            <tr class="headings">
                <th class="title"><?php echo __('Date &amp; time') ?></th>
                <th class="title" width="40%"><?php echo __('Description') ?></th>

                <th class="title" align="right" style="text-align:right !important;"><?php echo __('Amount') ?></th>

            </tr>
            <?php
            $tilentaCallHistryResult = $telintaObj->callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59', false, 4);
            if (count($tilentaCallHistryResult) > 0) {
                foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                    ?>


                    <tr>
                        <td><?php echo date("d-m-Y H:i:s", strtotime($xdr->bill_time)); ?></td>
                        <td><?php echo $xdr->CLD; ?></td>
                        <td align="right"><?php echo number_format($xdr->charged_amount, 2); ?><?php echo sfConfig::get('app_currency_code'); ?></td>
                    </tr>
                <?php
                }
            } else {

                echo __('There are currently no  records to show.');
            }
            ?>
        </table><br/><br/>

        <h1><?php echo 'Other Events'; ?> </h1>
        <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign" border='0'>
            <tr class="headings">
                <th class="title"><?php echo __('Date &amp; time') ?></th>
                <th class="title" width="40%"><?php echo __('Description') ?></th>

                <th class="title" align="right" style="text-align:right !important;"><?php echo __('Amount') ?></th>

            </tr>
            <?php
            $tilentaCallHistryResult = $telintaObj->callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59', false, 1);
            if (count($tilentaCallHistryResult) > 0) {
                foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                    ?>


                    <tr>
                        <td><?php echo date("d-m-Y H:i:s", strtotime($xdr->bill_time)); ?></td>
                        <td><?php echo $xdr->CLD; ?></td>
                        <td align="right"><?php echo number_format($xdr->charged_amount, 2); ?><?php echo sfConfig::get('app_currency_code'); ?></td>
                    </tr>
                <?php
                }
            } else {

                echo __('There are currently no call records to show.');
            }
            ?>
        </table><br/><br/>
        <!--
                                   <h1><?php echo 'Payment History'; ?> </h1>
                            <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign" border='0'>
                                <tr class="headings">
                                    <th class="title"><?php echo __('Date &amp; time') ?></th>
                                    <th class="title" width="40%"><?php echo __('Description') ?></th>
                                        <th class="title"><?php echo __('Amount') ?></th>
                                    </tr>
        <?php
        $tilentaCallHistryResult = $telintaObj->callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59', false, 2);
        if (count($tilentaCallHistryResult) > 0) {
            foreach ($tilentaCallHistryResult->xdr_list as $xdr) {
                ?>
                
                
                                            <tr>
                                                <td><?php echo date("Y-m-d H:i:s", strtotime($xdr->bill_time)); ?></td>
                                                <td><?php echo $xdr->CLD; ?></td>
                                                <td><?php echo number_format($xdr->charged_amount * -1, 2); ?></td>
                                            </tr>
            <?php
            }
        } else {

            echo __('There are currently no call records to show.');
        }
        ?>
                                </table><br/><br/>-->



        <h1>Call</h1>

        <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign" border='0'>
            <tr class="headings">
                <th width="20%"   align="left"><?php echo __('Date &amp; time') ?></th>
                <th  width="20%"  align="left"><?php echo __('Phone Number') ?></th>
                <th width="10%"   align="left"><?php echo __('Duration') ?></th>
    <!--                    <th  width="10%"  align="left"><?php echo __('VAT') ?></th>-->
                <th width="20%"   align="right"><?php echo __('Cost') ?> <?php echo sfConfig::get('app_currency_code'); ?></th>

                <th  width="20%"   align="left">Call Type</th>
            </tr>
            <?php
            $amount_total = 0;


  
 






            $getFirstnumberofMobile = substr($customer->getMobileNumber(), 0, 1);
            if ($getFirstnumberofMobile == 0) {
                $TelintaMobile = substr($customer->getMobileNumber(), 1);
                $TelintaMobile = sfConfig::get('app_country_code') . $TelintaMobile;
            } else {
                $TelintaMobile = sfConfig::get('app_country_code') . $customer->getMobileNumber();
            }
            $numbername = $customer->getUniqueid();


            $tilentaCallHistryResult = $telintaObj->callHistory($customer, $fromdate . ' 00:00:00', $todate . ' 23:59:59');


            foreach ($tilentaCallHistryResult->xdr_list as $xdr) { //echo "<pre>";echo var_dump($tilentaCallHistryResult);echo "</pre>";
                //  die;
                ?>


                <tr>
                    <td><?php echo date("d-m-Y H:i:s", strtotime($xdr->connect_time)); ?></td>
                    <td><?php echo $xdr->CLD; ?></td>
                    <td><?php
                $callval = $xdr->charged_quantity;
                if ($callval > 3600) {

                    $hval = number_format($callval / 3600);
                    $rval = $callval % 60;
                    $minute = $callval / 60;
                    $second = date('s', $rval);
                    $minute = number_format($minute, 0);
                   

                    echo $minute . ":" . $second;
                } else {


                    echo date('i:s', $callval);
                }
                ?></td>
        <!--                                    <td><?php echo number_format($xdr->charged_amount / 4, 2); ?></td>-->
                    <td align="right"><?php echo number_format($xdr->charged_amount, 2);
                $amount_total+= number_format($xdr->charged_amount, 2);
                ?> <?php echo sfConfig::get('app_currency_code'); ?></td>

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
                    if ($xdr->CLD == $cbtypecall) {
                        echo "Cb M";
                    } else {
                        echo "Cb S";
                    }
                }
                ?> </td>
                </tr>

        <?php
        $callRecords = 1;
    }
    ?>


                <?php
                $callRecords = 1;
            }
            ?>


<?php if (count($callRecords) == 0): ?>
            <tr>
    <?php if ($pus == 0) { ?>
                    <td colspan="6"><p><?php echo __('There are currently no call records to show.') ?></p></td>
            <?php } ?>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="3" align="right"><strong><?php echo __('Subtotal') ?></strong></td>
                <!--
                <td><?php echo format_number($amount_total - $amount_total * sfConfig::get('app_vat_percentage')) ?> <?php echo sfConfig::get('app_currency_code') ?></td>
                -->
                <td><?php echo number_format($amount_total, 2) ?> <?php echo sfConfig::get('app_currency_code'); ?></td>
                <td>&nbsp;</td>
            </tr>
<?php
endif;
if ($pus == 0) {
    ?>
    <!--                    <tr><td colspan="6" align="left">Samtalstyp  type detail <br/> Int. = Internationella samtal<br/>
                        Cb M = Callback mottaga<br/>
                            Cb S = Callback samtal<br/>
                            R = resenummer samtal<br/>
                    </td></tr>-->

<?php } ?>
        <tr><td colspan="6" align="right">  All amounts excl. IVA.</td></tr>
    </table>



<?php //}  ?>




    <!-- end split-form -->
</div> <!-- end left-col -->
