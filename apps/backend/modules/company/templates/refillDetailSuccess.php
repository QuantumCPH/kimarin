<div id="sf_admin_container">
   <?php if ($sf_user->hasFlash('message')): ?>
    <div class="save-ok">
      <h2><?php echo __($sf_user->getFlash('message')) ?></h2>
    </div>
    <?php endif; ?>
</div>  
<div id="sf_admin_container"><h1>Payment Detail</h1></div>

<form id="sf_admin_form" name="sf_admin_edit_form" method="post" enctype="multipart/form-data">
    <div id="sf_admin_content">
        <table style="padding: 0px;"  id="sf_admin_container" class="tblAlign" cellspacing="0" cellpadding="2" width="100%" >
            <tr>
                <td width="16%" style="padding: 5px;">Company:</td>
          <td width="84%" style="padding: 5px;">
    <input type="hidden" name="company_id" value="<?php echo $company->getId()?>" />
                    <?php echo $company->getName();?>                    
                </td>
          </tr>
            <tr>
                <td style="padding: 5px;"><?php echo __('Transaction Desc.:') ?></td>
                <td style="padding: 5px;">
                    <input type="hidden" name="descid" value="<?php echo $description->getId()?>" />                    
                    <?php echo $description->getTitle();?>                     
                </td>
            </tr>
            <tr>
                <td style="padding: 5px;">Refill Amount:</td>
                <td style="padding: 5px;">
                    
                    <input type="hidden" name="refillAmt" value="<?php echo $refillAmt-$vat?>" />                    
                    <?php echo number_format($refillAmt-$vat, 2);?><b><?php echo sfConfig::get('app_currency_code');?></b>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px;">VAT:</td>
                <td style="padding: 5px;">
                    
                    <input type="hidden" name="vat" value="<?php echo $vat?>" />                    
                    <?php echo number_format($vat,2);?><b><?php echo sfConfig::get('app_currency_code');?></b>
                </td>
            </tr>
            
            <tr>
                <td style="padding: 5px;">Total Payment:</td>
                <td style="padding: 5px;">
                    
                    <input type="hidden" name="totalPayment" value="<?php echo $refillAmt?>" />                    
                    <?php echo number_format($refillAmt,2);?><b><?php echo sfConfig::get('app_currency_code');?></b>
                </td>
            </tr>
            
        </table>
        <div id="sf_admin_container">
            <ul class="sf_admin_actions">
                <li><input type="submit" name="refillsave" value="Pay" class="sf_admin_action_save" /></li>
            </ul>
        </div>
    </div>
</form>


