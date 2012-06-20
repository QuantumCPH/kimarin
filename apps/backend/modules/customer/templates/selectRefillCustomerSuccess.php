<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#agent_company_reill").validate();
      });
</script>
<div id="sf_admin_container">
       <?php if ($sf_user->hasFlash('message')): ?>
        <div class="alert_bar">
                <?php echo $sf_user->getFlash('message') ?>
        </div>
        <?php endif;?>
</div>   <br />
<div id="sf_admin_container"  style="border: 1px solid #D44D05;background-color: #FCD9C9;">
   
    <h1 style="margin-top: 0;"><?php echo __('Refill Customer') ?></h1><br />
    <form method="post" action="refillCustomer" id="agent_company_reill">
      <div class="form-row">
             <label for="agent_commission_agent_company_id"><strong>Customer Mobile Number</strong></label>
             <div class="content">
                 <input type="text" name="mobile_number"  class="required" />
            </div>
      </div>
        <div class="form-row">
             <label for="transaction_description">Transaction Description</label>
             <div class="content">
                 <select id="transaction_description" name="transaction_description" class="required">                    
                    <?php    foreach($transactionDescriptions as $transactionDescription){?>
                    <option value="<?php echo $transactionDescription->getTitle();   ?>"><?php echo $transactionDescription->getTitle();   ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <label for="agent_commission_agent_company_id"><strong>Refill Amount</strong></label>
            <div class="content">
                <input type="text" name="refill_amount"  class="required number" min="0" />  NOK
            </div>
        </div>
        <div class="form-row">
            <div class="content">
                <input type="submit" name="Refill Customer" value="Refill Customer"  style='background: url("http://admin.zapna.no/sf/sf_admin/images/b.png") repeat-x scroll left center transparent;
    border: 1px solid #D44D05 !important;color: #FFFFff;' />
            </div>
        </div>
    </form>
</div>


   