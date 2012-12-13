<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('SMS history')) ) ?>

<div class="alert_bar">
	<?php echo __('Your SMS history will be updated 5 - 10 minutes after you have sent your SMS.') ?>
</div>
  <div class="left-col">
    <?php include_partial('navigation', array('selected'=>'websms', 'customer_id'=>$customer->getId())) ?>
	
	<div class="split-form">
      <div class="fl col">
        <form action="<?php echo url_for('customer/smshistory') ?>" method="post">
          <ul>
<?php /*
            <li>
              <label><?php echo $filter['to_no']->renderLabel() ?>:</label>
              <?php echo $filter['to_no'] ?>
            </li>
            <li>
            	<?php echo $filter['date']->render(array('class' => 'quater')) ?>
            </li>
            <!--
            <li>
              <label><?php echo __('From') ?>:</label>
              <select class="quater">
                <option>&nbsp;</option>
              </select>
              <select class="quater">
                <option>&nbsp;</option>
              </select>
              <select class="quater">
                <option>&nbsp;</option>
              </select>
            </li>

            <li>
              <label><?php echo __('To') ?>:</label>
              <select class="quater">
                <option>&nbsp;</option>
              </select>
              <select class="quater">
                <option>&nbsp;</option>
              </select>
              <select class="quater">
                <option>&nbsp;</option>
              </select>
            </li>
             -->
            <li>
              <button><?php echo __('Show') ?></button>
            </li>
 */ ?>
            <li>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="callhistory">
                  <tr>
                    <td class="title"><strong><?php echo __('Date and time') ?></strong></td>
                    <td class="title" width="40%"><strong><?php echo __('Destination number') ?></strong></td>                    
                    <td class="title" align="right" style="padding-right: 20px;"><strong><?php echo __('Cost') ?></strong></td>
                  </tr>

                <?php
                $amount_total = 0;
                foreach($smsRecords as $smsRecord): ?>
                <tr>
                  <td><?php  echo $smsRecord->getCreatedAt('d-m-Y H:i:s') ?></td>
                  <td><?php echo  $smsRecord->getDa() ?></td>
                  

                  <?php
                  $c = new Criteria();
                  $c->add(CountryPeer::ID, $smsRecord->getCountryId());
                  $country = CountryPeer::doSelectOne($c);
                  $amt = $country->getCbfRate();
                  
                  ?>
                  <td align="right"><?php $amount_total += $amt; echo number_format($amt, 2) ?> <?php echo sfConfig::get('app_currency_code')?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($smsRecords)==0): ?>
                <tr>
                	<td colspan="5"><p><?php echo __('There are currently no SMS records to show.') ?></p></td>
                </tr>
                <?php else: ?>
                <tr>
                	<td colspan="2" align="right"><strong><?php echo __('Total') ?></strong></td>
                	<!--
                	<td><?php echo format_number($amount_total-$amount_total*sfConfig::get('app_vat_percentage')) ?> <?php echo sfConfig::get('app_currency_code')?></td>
                	 -->
                         <td align="right"><?php echo number_format($amount_total, 2) ?> <?php echo sfConfig::get('app_currency_code')?></td>
                </tr>
                <?php endif; ?>
                <tr><td colspan="3" style="text-align: right"><?php echo __('All amounts excl. IVA.');?></td></tr>
              </table>
            </li>
            <?php if($total_pages>1): ?>
            <li>
            	<ul class="paging">
            	<?php for ($i=1; $i<=$total_pages; $i++): ?>
            		<li <?php echo $i==$page?'class="selected"':'' ?>><a href="<?php echo url_for('customer/smsHistory?page='.$i) ?>"><?php echo $i ?></a></li>
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