<style>
    .error {
    color: #F00000;
    margin-left: 74px;
    width:auto !important;
}
</style>
    
    
<div id="sf_admin_container"><h1><?php echo __('Register a Customer') ?> <span class="active">- <?php echo __('Step 1') ?>: <?php echo __('Register') ?> </span></h1></div>

<div class="borderDiv"> 
    <form id="regForm" method="post">
        <div class="regForm">  
            <div class="frmleft">
                <div class="left"><?php echo __('Mobile Number') ?><em>*</em>&nbsp;:</div>
                <div class="right">
                    <label class="country-code" id="country-code">+34</label>
                    <input name="mobile_number" class="mobile"/>

                </div>
                <br clear="all" />
                <div class="left"><?php echo __('N.I.E. or passport<br />number') ?><em>*</em>&nbsp;:</div>
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
                            <option value="<?php echo $lang->getId(); ?>" > <?php echo $lang->getLanguage(); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <br clear="all" />
                <div class="left"><?php echo __('E-mail') ?><em>*</em>&nbsp;:</div>
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
                    <div class="left"><?php echo __('first name') ?><em>*</em>&nbsp;:</div>
                    <div class="right"><input name="first_name" type="text" class="input1"/></div>
                    <br clear="all" />
                    <div class="left"><?php echo __('last name') ?><em>*</em>&nbsp;:</div>
                    <div class="right"><input name="last_name" type="text" class="input1"/></div>
                    <br clear="all" />
                    <div class="left"><?php echo __('Address') ?><em>*</em>&nbsp;:</div>
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
            
            <div class="left">&nbsp;</div>
            <div class="right"><input type="submit" class="butonsigninsmall" value="<?php echo __('Submit') ?>" /></div>
        </div>
    </form>

    <div class="clr"></div>
</div>




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
                    remote: "<?php echo __("Invalid or Already Registerd Mobile Number"); ?>"
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