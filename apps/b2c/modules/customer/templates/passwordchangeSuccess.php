<?php use_helper('I18N') ?>
<?php include_partial('dashboard_header', array('customer'=> $customer, 'section'=>__('Settings')) ) ?>
<?php echo $form->renderGlobalErrors() ?>

<?php if ($sf_user->hasFlash('updated')): ?>
<div class="ok_alert_bar">
	<?php echo $sf_user->getFlash('updated') ?>
</div>
<?php endif;?>
<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>
<form method="post" action="<?php url_for('customer/settings') ?>" id="settingsForm" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
  <div class="left-col">
    <?php include_partial('navigation', array('selected'=>'settings', 'customer_id'=>$customer->getId())) ?>
	<div class="split-form">

 


		<div class="fl col" style="width:345px !important;">
        <?php echo $form->renderHiddenFields() ?>
          <ul>
 
            
              <li style="width: 287px !important;">
             <label for="customer_password" class="required pcon"><?php echo __('Old Password') ?> </label><em class="required">*</em>
                <input type="password" id="customer_old_password" name="customer[oldpassword]" value="<?php if ($oldpassword){}?>">
               
                <?php if ($oldpasswordError): ?>
                <span id="cardno_decl" class="alertstep1">
			  	<?php echo image_tag('../zerocall/images/decl.png', array('absolute'=>true)) ?>
			 </span>
                <?php endif; ?>
             <div class='inline-error' style="margin-right: 18px !important;"><?php if ($oldpasswordError){echo __('Your old password is not correct.');} ?>&nbsp;</div>
            </li>
            <?php
            $error_password = false;;
            if($form['password']->hasError())
            	$error_password = true;
            ?>
            <li style="width: 287px !important;">
             <?php echo $form['password']->renderLabel(null, array('class'=>'pcon')) ?>
             <?php echo $form['password']->render(array('value' => '')) ?>
             <?php if ($error_password): ?>
             <span id="cardno_decl" class="alertstep1">
			  	<?php echo image_tag('../zerocall/images/decl.png', array('absolute'=>true)) ?>
			 </span>
			 <?php endif; ?>
             <div class='inline-error' style="margin-right: 18px !important;"><?php echo $error_password?$form['password']->renderError():'&nbsp;'?></div>
            </li>
            <!-- end password -->
            <?php
            $error_password_confirm = false;
            if($form['password_confirm']->hasError())
            	$error_password_confirm = true;
            ?>
            <li style="width: 287px !important;">
             <?php echo $form['password_confirm']->renderLabel(null, array('class'=>'pcon')) ?>
             <?php echo $form['password_confirm'] ?>
             <?php if ($error_password_confirm): ?>
             <span id="cardno_decl" class="alertstep1">
			  	<?php echo image_tag('../zerocall/images/decl.png', array('absolute'=>true)) ?>
			 </span>
			 <?php endif; ?>
             <div class='inline-error' style="margin-right: 18px !important;"><?php echo $error_password_confirm?$form['password_confirm']->renderError():'&nbsp;'?></div>
            </li>
            <!-- end confirm password -->
           <li style="width: 287px !important;"> 
           <div>
               <input type="submit" class="butonsigninsmall"  name="submit"  style="cursor: pointer;margin-left:0px !important;margin-top: 10px !important;"  value="<?php echo __('Update') ?>" />
          </div>
           </li>
          </ul>

      </div>
      
    </div> <!-- end split-form -->
  </div> <!-- end left-col -->
</form>
  <?php include_partial('sidebar') ?>
