<script language="javascript" type="text/javascript">
   // is_applied   product_price
  
  jQuery(document).ready(function () {
   jQuery("#products").change(function(){
        if(jQuery('option:selected', this).attr('postage')==1){
            jQuery("#simtypes").show();
            jQuery("#uniqueids").show();
        }else{
            jQuery("#simtypes").hide(); 
            jQuery("#uniqueids").hide();
        }
   });
   jQuery("#sf_admin_form").validate({
            focusCleanup: true,
           
            rules: {
                sim_type_id:{
                    required:  function(){
                        return (jQuery('option:selected', "#products").attr('postage')==1)
                    }
                },
                uniqueid:{
                    required:  function(){
                        return (jQuery('option:selected', "#products").attr('postage')==1)
                    }
                }                
            } 
        });
   jQuery("#products").trigger("change");
});


</script>
<div id="sf_admin_container">
<?php if(isset($_REQUEST['message']) && $_REQUEST['message']!=""){     
    if($_REQUEST['message']=="error"){ ?> 
        <div class="save-ok">
        <h2>Employee is not added please check email </h2>
        </div>
        
  <?php }else{  ?>
        <div class="save-ok">
        <h2>Employee is added successfully</h2>
        </div>
<?php  }  
}   ?>
<?php if ($sf_user->hasFlash('messageError')): ?>
  <div>
   <span style="color:#FF0000"><?php echo __($sf_user->getFlash('messageError')) ?></span>
  </div>
<?php endif; ?><br />
<h1>New My employee</h1>
<form id="sf_admin_form" name="sf_admin_edit_form" method="post" enctype="multipart/form-data" action="saveEmployee">
    <div id="sf_admin_content">
  <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign" border='0'>
        <tr>
        <td style="padding: 5px;">First name:</td>
        <td style="padding: 5px;"><input type="text" name="first_name" id="employee_first_name"  class="required"  size="25" /></td>
                </tr>
                 <tr>
        <td style="padding: 5px;">Last name:</td>
        <td style="padding: 5px;"> <input type="text" name="last_name" id="employee_last_name"   class="required"   size="25" /></td>
                </tr>
                 <tr>
        <td style="padding: 5px;">Company:</td>
        <td style="padding: 5px;">
  <select name="company_id" id="employee_company_id"    class="required"  style="width:190px;">
      <option value="">Select Company</option>
      <?php foreach($companys as $company){  ?>
<option value="<?php echo $company->getId(); ?>"<?php echo ($companyval==$company->getId())?"selected='selected'":''?>><?php echo $company->getName()   ?></option>
<?php   }  ?>
</select>  </td>
                </tr>
                <tr>
        <td style="padding: 5px;">Product:</td>
        <td style="padding: 5px;"> <select name="productid" id="products" class="required">
              <?php foreach($products as $product){  ?>
                    <option value="<?php echo $product->getId();   ?>" postage="<?php echo $product->getPostageApplicable(); ?>"><?php echo $product->getName()   ?></option>
        <?php   }  ?>
            </select>
        </td>
                </tr>
<!--                  <tr>
        <td style="padding: 5px;">Country Code:</td>
        <td style="padding: 5px;"> <input type="text" name="country_code" id="employee_country_code"   size="25"   class="required digits" /> </td>
                </tr>-->
      <tr id="simtypes">
        <td style="padding: 5px;">SIM Type:</td>
        <td style="padding: 5px;"> 
           <select name="sim_type_id" id="employee_sim_type_id"    class="required"  style="width:190px;">
                 <option value="">Select SIM Type</option>
            <?php foreach($simtypes as $simtype){  ?>
                    <option value="<?php echo $simtype->getId(); ?>"><?php echo $simtype->getTitle();   ?></option>
            <?php   }  ?>
           </select>
        </td>
      </tr>
        <tr id="uniqueids">
            <td style="padding: 5px;">UniqueId:</td>
            <td style="padding: 5px;">
                <select name="uniqueid" id="uniqueid-select"    class="required"  >
                </select>
            </td>
        </tr>
      <tr>
        <td style="padding: 5px;">Mobile number:</td>
        <td style="padding: 5px;"> <input type="text" name="mobile_number" id="employee_mobile_number"  size="25"   class="required digits"  minlength="8" /><span id="msgbox" style="display:none"></span> </td>
      </tr>
      <tr>
        <td style="padding: 5px;">Password:</td>
        <td style="padding: 5px;"> <input type="text" name="password" id="employee_password"  size="25"   class="required" /><span id="msgbox" style="display:none"></span> </td>
      </tr>
                 <tr>
        <td style="padding: 5px;">Email:</td>
        <td style="padding: 5px;"> <input type="text" name="email" id="employee_email"   class="required email"  size="25" /> </td>
                </tr>
                  <input type="hidden" name="registration_type" value="0" >


<!--                 <tr>
        <td style="padding: 5px;">Rese number:</td>
        <td style="padding: 5px;">
  <select name="registration_type" id="employee_registration_type">
         <option value="0"> no</option>
      <option value="1"> yes</option>
    
</select> </td>
                </tr>-->
  <!--<tr>
        <td>App code:</td>
        <td> <input type="text" name="app_code" id="employee_app_code" value="" size="25" /></td>
                </tr>
   <tr>
        <td>Is app registered:</td>
        <td><input type="checkbox" name="is_app_registered" id="employee_is_app_registered" value="1" /> </td>
                </tr>
             <tr>
        <td>Password:</td>
        <td>  <input type="text" name="password" id="employee_password" value="" size="25" /></td>
                </tr>    <tr>
        <td style="padding: 5px;">Product Price:</td>
        <td style="padding: 5px;"> <input type="text" name="price" id="employee_password"   size="25" />  </td>
                </tr>-->
                  

     <tr>
                    <td>Comments:</td>
                    <td><textarea name="comments"  id="employee_comments" style="width: 542px !important; height: 230px !important;"> </textarea>
                    </td>
                </tr>
  </table>
              
 <ul class="sf_admin_actions"><input type="hidden" value="" id="error" name="error">

  <li>  <input class="sf_admin_action_list" value="list" type="button" onclick="document.location.href='../employee';" /></li>
  <li><input type="submit" name="save" value="save" class="sf_admin_action_save" /> </li>

</ul>
           

    
    </div>    
</form>
</div>
<script type="text/javascript">
jQuery(function(){

    jQuery("#employee_sim_type_id").change(function(){
        jQuery.post("<?PHP echo sfConfig::get('app_admin_url') ?>employee/getUniqueIds",{ sim_type_id:jQuery(this).val() } ,function(data){
           jQuery("#uniqueid-select").html(data);
        });
    });
    jQuery("#employee_sim_type_id").trigger("change");
});
</script>