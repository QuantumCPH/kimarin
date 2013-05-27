<?php
//	use_helper('I18N');
//	
//	echo $form->renderGlobalErrors();
//	
//	include_partial("step_1", array('form'=>$form,'sLang'=>$sLang));
?>

<?php
use_helper('I18N');
?>
<script type="text/javascript">
    jQuery(function(){
        postage = 0;
        jQuery("#regForm").validate({
            focusCleanup: true,
           
            rules: {
                mobile_number:{
                    required: true,
                    minlength: 8,
                    maxlength: 14,
                    digits: true,
                    remote: {url: "<?php echo sfConfig::get("app_customer_url"); ?>customer/validateMobileNumber" ,
                        type: "get",
                        dataType: 'json',
                        data: {
                            country_code: function() { return $('option:selected', "#country-select").attr('calling_code'); } 
                        }
                    } 
                },
                password:{
                    required: true,
                    minlength: 6
                },
                
                confirm_password:{
                    required: true,
                    equalTo: "#password"
                },email:{
                    required: true,
                    email: true
                  
                },
                simtype:{
                    required:  function(){
                        return ($('option:selected', "#product-select").attr('postage')==1)
                    }
                },
                first_name:{
                    required:  function(){
                        return ($('option:selected', "#product-select").attr('postage')==1)
                    }
                },
                last_name:{
                    required:  function(){
                        return ($('option:selected', "#product-select").attr('postage')==1)
                    }
                },
                address:{
                    required:  function(){
                        return ($('option:selected', "#product-select").attr('postage')==1)
                    }
                },
                city:{
                    required:  function(){
                        return ($('option:selected', "#product-select").attr('postage')==1)
                    }
                },
                post_code:{
                    required:  function(){
                        return ($('option:selected', "#product-select").attr('postage')==1)
                    }
                }
            },
            messages: {
                mobile_number:{
                    remote: "<?php echo __("Please enter a valid mobile number."); ?>",
                    minlength: "<?php echo __("Please enter a valid 8 to 14 digit mobile number."); ?>",
                    maxlength: "<?php echo __("Please enter a valid 8 to 14 digit mobile number."); ?>",
                    digits: "<?php echo __("Please enter a valid mobile number."); ?>"
                },
                password:{
                    minlength: "<?php echo __("Your password must be at least 6 digits or characters."); ?>"
                },
                
                confirm_password:{
                    equalTo: "<?php echo __("The passwords don’t match."); ?>"
                },email:{
                    email: "<?php echo __("Please enter a valid e-mail address."); ?>"
                }
            }
 
        });
        
        jQuery("#product-select").change(function(){
            if($('option:selected', this).attr('postage')==1){
                jQuery("#postal-info").show();
            }else{
                jQuery("#postal-info").hide(); 
            }
        });
        
        jQuery("#country-select").change(function(){
            var c_code =  jQuery("#country-select").find(':selected').attr('calling_code');
            //  alert(v);
            jQuery("#country-code").html("+"+c_code);
            //    Cufon.replace('.country-code',{fontFamily: 'Barmeno-Medium', fontSize:'13px'});
        });
        
//        jQuery("#country-select").ready(function(){
//            var c_code =  jQuery("#country-select").find(':selected').attr('calling_code');
//            //  alert(v);
//            jQuery("#country-code").html("+"+c_code);
//        });
        jQuery('.submitbuttun').mousedown(function(){
            jQuery(this).css('color', 'black');
        }); 
        jQuery('.submitbuttun').mouseup(function(){
            jQuery(this).css('color', 'white');
        });
        if(jQuery('option:selected', "#product-select").attr('postage')==1){
            jQuery("#postal-info").show();
        }else{
            jQuery("#postal-info").hide(); 
        }
        
    });
  
</script>
<div class="step-details"><br /><br /> <strong><?php echo __('Become a Business Customer') ?> <span class="active">- <?php echo __('Step 1') ?>: <?php echo __('Register') ?> </span><span class="inactive">- <?php echo __('Step 2') ?>: <?php echo __('Payment') ?></span></strong>
    <br /><br /><span class="requiretofill">* <?php echo __('You must fill in this field.') ?></span>  </div>
<form id="regForm" method="post">
    <div class="regForm">  
        <div class="frmleft">
            <div class="left"><?php echo __('Mobile Number') ?><em>*</em>&nbsp;:</div>
            <div class="right">
                <label class="country-code" id="country-code">+34</label>
                <input name="mobile_number" class="mobile"/>
            
                </div>
            <br clear="all" />
                <div class="left" style="padding-top: 0!important;"><?php echo __('Name of<br />contact person') ?><em>*</em>&nbsp;:</div>
                <div class="right"><input name="last_name" type="text" class="input1"/></div>
            <br clear="all" />
             <div class="left"><?php echo __('CIF number') ?><em>*</em>&nbsp;:</div>
            <div class="right">
                <input name="nie" class="input1 required"/><br/>
                
            </div>
            <br clear="all" />
            <div class="left"><?php echo __('Preferred language') ?>:</div>
            <div class="right">
                <select name="pref_lang" class="input">
                    <?php
                    foreach ($langs as $lang) {
                        ?>
                        <option value="<?php echo $lang->getId(); ?>" > <?php echo __($lang->getLanguage()); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <br clear="all" />
            <div class="left"><?php echo __('E-mail of<br />contact person') ?><em>*</em>&nbsp;:</div>
            <div class="right">
                <input type="text" name="email" class="input1" />
            </div>
            <br clear="all" />
            <div class="left">
                <?php echo __('Password') ?><em>*</em>&nbsp;:
            </div>
            <div class="right">
                <input name="password" class="input1 required" id="password" type="text"/>
            </div>
            <br clear="all" />
            <div class="left">
                <?php echo __('Confirm password') ?><em>*</em>&nbsp;:
            </div>
            <div class="right">
                <input name="confirm_password" class="input1 required" type="text"/>
            </div>
            <br clear="all" />
            <div class="left"><?php echo __('Product'); ?><em>*</em>&nbsp;:</div>
            <div class="right">
                <select id="product-select" class="input required" name="product">
                    <?php
                    foreach ($products as $product) {
                        ?>
                        <option value="<?php echo $product->getId(); ?>" postage="<?php echo $product->getPostageApplicable(); ?>" <?php
                    if ($product->getProductTypeId() == 10) {
                        echo "selected=selected";
                    }
                        ?> > <?php echo $product->getName(); ?></option>

                        <?php
                    }
                    ?>
                </select>
            </div>

            <br clear="all" />
        </div>
        <div class="frmright">
            <fieldset id="postal-info" style="display: none;border:0">
                
                <div class="left"><?php echo __('SIM type') ?><em>*</em>&nbsp;:</div>
                <div class="right"> 
                    <select name="simtype" class="input">
                        <option value="" > --------- </option>
                        <?php
                        foreach ($simTypes as $simtype) {
                            ?>
                            <option value="<?php echo $simtype->getId(); ?>" > <?php echo $simtype->getTitle(); ?></option>

                            <?php
                        }
                        ?>
                    </select>
                </div>   
                <br clear="all" />
                <div class="left"><?php echo __('Company name') ?><em>*</em>&nbsp;:</div>
                <div class="right"><input name="first_name" type="text" class="input1"/></div>                
                <br clear="all" />
                <div class="left"><?php echo __('Company address') ?><em>*</em>&nbsp;:</div>
                <div class="right"><input name="address" type="text" class="input1"/></div>
                <br clear="all" /> 
                <div class="left"><?php echo __('Postcode') ?><em>*</em>&nbsp;:</div>
                <div class="right"><input name="post_code" type="text" class="input1"/></div>             
                <br clear="all" />    
                <div class="left"><?php echo __('City') ?><em>*</em>&nbsp;:</div>
                <div class="right"><input name="city" type="text" class="input1"/></div>
                <br clear="all" />  
            </fieldset>
        </div>
        <br clear="all" />
    </div>  
    <br/>
    <div class="regForm">
        <div class="left"><input type="checkbox" name="terms_conditions" class="required" /></div>
        <div class="right">
            <?php
            if ($sf_user->getCulture() == 'de') {
                $url = "http://kimarin.es/de/terms-conditions-de.html";
            } elseif ($sf_user->getCulture() == 'es') {
                $url = "http://kimarin.es/es/terms-conditions-es.html";
            } elseif ($sf_user->getCulture() == 'ca') {
                $url = "http://kimarin.es/ca/terms-conditions-ca.html";
            } else {
                $url = "http://kimarin.es/terms-conditions-en.html";
            }
            ?>
            <a href="<?php echo $url; ?>" target="_blank" style="outline:none"><?php echo __('Please check this box to confirm that you have<br />read and accept the %1% terms and conditions.',array('%1%'=>sfConfig::get("app_site_title"))) ?></a>
        </div>
        <br clear="all"/>
        <br/>
        <div class="left">&nbsp;</div>
        <div class="right"><input type="submit" class="butonsigninsmall" value="<?php echo __('Submit') ?>" /></div>
    </div>
</form>
