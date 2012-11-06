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

<a href="#" onclick="showCompany()" title="company info" class="external_link"><?php echo __('Agent/Company info') ?></a>

<?php if($sf_user->isAuthenticated()): ?>
     <?php if($agent_company->getIsPrepaid()): ?>

        <?php echo link_to(__('Account Recharge'), 'affiliate/accountRefill',array('class'=>'external_link')) ?>

    

        <?php echo link_to(__('Recharge Receipts'), 'affiliate/agentOrder',array('class'=>'external_link')) ?>

    
    
    <?php endif; ?>
<?php endif; ?>
<!--<a href="#" onclick="showBank()" title="bank info"><?php echo __('Bank info') ?></a>
&nbsp; | &nbsp;
<a href="#" onclick="showCommission()" title="commission info"><?php echo __('Commission info') ?></a>
&nbsp; | &nbsp;
-->
 <?php if($agent_company->getIsPrepaid()): ?>
 <?php echo link_to(__('Payment History'), 'affiliate/paymentHistory',array('class'=>'external_link')) ?>
<?php endif; ?>
<br/>


<div class="report_container">
<div id="sf_admin_container"><h1><?php echo __('Reciepts For Agent Account Refills') ?></h1></div>
        
  <div class="borderDiv">
<table cellspacing="0" width="100%" class="summary">
	<tr>
		<th width="4%" style="text-align:left">&nbsp;</th>
		<th width="13%" style="text-align:left"><?php echo __('Date');?></th>
		<th width="14%" style="text-align:right;padding-right: 55px;"><?php echo __('Amount');?></th>
		<th width="69%" style="text-align:left"><?php echo __('Show Reciept');?></th>

	</tr>
        <?php $i=0 ?>
        <?php foreach($agentOrders as $agentOrder){ ?>
        <tr <?php echo 'class="'.($i%2 == 0?'odd':'even').'"' ?>>
            <td><?php echo ++$i ?>.</td>
            <td><?php echo $agentOrder->getCreatedAt('d-m-Y') ?></td>
            <td style="text-align:right;padding-right: 55px;"><?php echo BaseUtil::format_number($agentOrder->getAmount(),2)?><?php echo sfConfig::get('app_currency_code');?></td>
            <td><a href="<?php echo url_for('affiliate/printAgentReceipt?aoid='.$agentOrder->getId(), true) ?>" ><?php echo __('Receipt');?> </a>
            </td>
            
        </tr>

        <?php } ?>
</table>
  </div>
</div>