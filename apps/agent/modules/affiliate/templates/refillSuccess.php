 <div id="sf_admin_container"><h1><?php echo __('Refill') ?></h1></div>
        
  <div class="borderDiv"> 
<form method="post"  class="split-form-sign-up" id="refill_form" action="<?php url_for('affiliate/refill') ?>">
      
             <div class="refillhead"><?php echo __('Manual refill.') ?></div>
          <p> <?php echo __('You can refill your %1% Account with the following amounts:',array("%1%"=>sfConfig::get('app_site_title')))?></p>
       
              <ul class="welcome">
         	<!-- customer product -->
	<?php   
                $bonus ="";
                foreach($refillProducts as $refill){ 
                    if($refill->getBonus()) $bonus = __('PLUS %1%%2%',array("%1%"=>number_format($refill->getBonus(),2),"%2%"=>sfConfig::get('app_currency_code')));
        ?>
            <li><?php   echo number_format($refill->getRegistrationFee(),2).sfConfig::get('app_currency_code'); echo __(" (airtime value:");echo __(" %1%%2% %3%)",array("%1%"=>number_format($refill->getRegistrationFee(),2),"%2%"=>sfConfig::get('app_currency_code'),"%3%"=>$bonus));
                    //"&nbsp;Bonus:".$refill->getBonus()."&nbsp;Total Including Vat:".(sfConfig::get('app_vat_percentage')+1)*$refill->getRegistrationFee();?></li>
        <?php
        }       
        ?>
         </ul> 
         <p><?php echo __("All amounts are excl. IVA.");?></p>
         <p><?php echo __("The value of airtime on your account balance cannot  exceed 250.00%1% at any moment in time. ",array("%1%"=>sfConfig::get('app_currency_code')));echo __("The refill amount is valid for 180 days.");?></p>
         <br clear="all" />
         <?php if($error_msg){?>
         <div id="error-message" class="grid_9 save-decl"><?php echo $error_msg ?></div><br/><br/>
        <?php } ?> 
	<ul class="fl col">
            <li>
             <?php echo $form['mobile_number']->renderLabel() ?>
             <?php echo $form['mobile_number'] ?>
             <?php if ($error_mobile_number): ?>
             <span id="cardno_decl" class="alertstep1">
			  	<?php echo image_tag('../zerocall/images/decl.png', array('absolute'=>true)) ?>
			 </span>
			 <?php endif; ?>
             <div class='inline-error'><?php echo $error_mobile_number ?></div>
            </li>
            
             <?php
            $error_extra_refill = false;
            if($form['extra_refill']->hasError())
            	$error_extra_refill = true;
            ?>
            <li>
             <?php echo $form['extra_refill']->renderLabel() ?>
             <?php echo $form['extra_refill'] ?>
             <?php if ($error_extra_refill): ?>
             <span id="cardno_decl" class="alertstep1">
			  	<?php echo image_tag('../zerocall/images/decl.png', array('absolute'=>true)) ?>
			 </span>
			 <?php endif; ?>
             <div class='inline-error'><?php echo $error_extra_refill?></div>
            </li>
             <?php
          if( $browser->getBrowser() == Browser::BROWSER_IE  )
          {
                   ?>
          <li class="">
               <input type="submit" value="<?php echo __('Refill') ?>" style="margin-left:50px !important;float:none !important;" />
          </li>

          <?php } else{ ?>
	          <li class="fr buttonplacement">
	            <button onclick="$('#refill_form').submit();" style="cursor: pointer;margin-left: 15px !important;"><?php echo __('Refill') ?></button>
	          </li>
	<?php }?>
		<br clear="all" />	  
	</ul><br clear="all" />
</form>
      <div class="clr"><br clear="all" /></div>
  </div>