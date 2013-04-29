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
<div class="appbody">
<form action="#">
    <?php //echo image_tag(sfConfig::get('app_web_url').'zerocall/images/Screenshot_2013-04-25-10-19-32.png');?>
<div class="gridContainer clearfix">
  <div id="LayoutDiv1">
   	<div class="intro_wrapper">
   	  <div class="dashboard_logo"><?php echo image_tag(sfConfig::get('app_web_url').'zerocall/images/app-heading.jpg');?></div>
   	  <div class="change_passwrod">
            	<ul>
                <li style=" text-align:center; line-height:50px">Enter your information and press Register</li>
                  <li>
                    <select name="select" id="select">
                      <option>Denmark(+45)</option>
                    </select>
                  </li>
                  <li><input name="input" type="text" placeholder="Enter phone number:" /><br /><span class="exphone">e.g. 6133243242</span></li>
                  <li><input name="input3" type="text" placeholder="Enter email:" /></li>
                  <li><input name="input3" type="text" placeholder="Enter name:" /></li>
                  <li><input name="input3" type="password" placeholder="Enter password:" /></li>
                  <li><input name="input3" type="password" placeholder="Confirm password:" /></li>
                  <li><div class="register"><input type="button" value="REGISTER" /></div></li>
                  <div style="clear:both;"></div>
                </ul>
      </div>
      
   	</div>
    
    </div> 
    <br clear="all" />
  </div>
  <div class="footer">2013 Kimarin Europe S.L Privacy policy</div>
</form>
</div><br clear="all" />