<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<div id="sf_admin_container"><h1><?php echo __('Refill Detail') ?></h1>
<?php if ($sf_user->hasFlash('message')): ?>
<div class="save-ok">
  <h2><?php echo __($sf_user->getFlash('message')) ?></h2>
</div>
<?php endif; ?>
</div>
    <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign">     
       <tr>
           <td class="tdcss">
               <p>Payment canceled.</p>
           </td>           
       </tr>       
    </table>
