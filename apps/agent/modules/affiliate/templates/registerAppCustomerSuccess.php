<style>
    .error {
    color: #F00000;
    margin-left: 74px;
    width:auto !important;
}
</style>
    
 <?php if($disable!=""){?>
   <p class="err-msg"><?php echo $product_active; ?></p>
 <?php }?><br />
    <div class="appbody">
<form action="<?php echo $target;?>affiliate/appRegistration" method="post" id="appRegForm">
    <input type='hidden' value='<?php echo $product_id ?>' name='product_id' />
<div class="gridContainer clearfix">
  <div id="LayoutDiv1">
   	<div class="intro_wrapper">
   	  <div class="dashboard_logo"><?php echo image_tag(sfConfig::get('app_web_url').'zerocall/images/app-heading.jpg');?></div>
   	  <div class="app_reg_fields">
            	<ul>
                <li style=" text-align:center; line-height:50px">Enter your information and press Register</li>
                  <li>
                    <select name="ccode" id="country-select">
                     <?php foreach($countries as $country){ ?>
                       <option value="<?php echo $country->getCallingCode();?>" calling_code="<?php echo $country->getCallingCode();?>"><?php echo $country->getName();?></option>  
                     <?php }?>   
                    </select>
                  </li>
                  <li><label class="cc_code">+34</label><input name="mobile_number" id="mobile_number" type="text" placeholder="Phone number / Número de telefóno" class="mnumber" />
                      <span class="exphone">e.g. 6133243242</span></li>
                  <li><input name="email" type="text" placeholder="Email" /></li>
                  <li><input name="name" type="text" placeholder="Name / Nombre y apellido" /></li>
                  <li><input id="pwd" name="pwd" type="password" placeholder="Password / Contraseña" /></li>
                  <li><input name="confirm_password" type="password" placeholder="Confirm password / Confirma contraseña" /></li>
                  <li><div class="register"><input type="submit" value="REGISTER" class="appregbtn" <?php echo $disable;?> /></div></li>
                  <div style="clear:both;"></div>
                </ul>
              
              <input type="hidden" value="Agent" name="registerFrom" />
      </div>
      
   	</div>
    
    </div> 
    <br clear="all" />
  </div>
  <div class="footer"><?php echo date("Y");?> Kimarin Europe S.L Privacy policy</div>
</form>
</div>
<br clear="all" />

    <div class="clr"></div>




<script type="text/javascript">
    jQuery(function(){
        postage = 0;
        jQuery("#appRegForm").validate({
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
                pwd:{
                    required: true,
                    minlength: 6
                },                
                confirm_password:{
                    required: true,
                    equalTo: "#pwd"
                },
                email:{
                    required: true,
                    email: true
                  
                },
                name:{
                    required:  true
                }                
            },
            messages: {
                mobile_number:{
                    remote: "<?php echo __("Please enter a valid mobile number."); ?>",
                    minlength: "<?php echo __("Please enter a valid 8 to 14 digit mobile number."); ?>",
                    maxlength: "<?php echo __("Please enter a valid 8 to 14 digit mobile number."); ?>",
                    digits: "<?php echo __("Please enter a valid mobile number."); ?>"
                },
                pwd:{
                    minlength: "<?php echo __("Your password must be at least 6 digits or characters."); ?>"
                },
                
                confirm_password:{
                    equalTo: "<?php echo __("The passwords don’t match."); ?>"
                },
                email:{
                    email: "<?php echo __("Please enter a valid e-mail address."); ?>"
                }
            }
 
        });
        
    });
  
  
</script>