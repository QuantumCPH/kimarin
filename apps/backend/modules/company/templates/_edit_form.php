<?php echo form_tag('company/save', array(
  'id'        => 'sf_admin_edit_form',
  'name'      => 'sf_admin_edit_form',
  'multipart' => true,
)) ?>
<?php echo object_input_hidden_tag($company, 'getId') ?>

<fieldset>
<div class="form-row">
  <?php echo label_for('company[name]', __($labels['company{name}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{name}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{name}')): ?>
    <?php echo form_error('company{name}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getName', array (
  'size' => 80,
  'control_name' => 'company[name]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('company[vat_no]', __($labels['company{vat_no}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{vat_no}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{vat_no}')): ?>
    <?php echo form_error('company{vat_no}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>
<?php if ($company->isNew()){ ?>
  <?php $value = object_input_tag($company, 'getVatNo', array (
  'size' => 7,
  'control_name' => 'company[vat_no]',
)); echo $value ? $value : '&nbsp;' ?>
      <span id="msgbox" style="display:none"></span>
      <?php }else{

          $value = object_input_tag($company, 'getVatNo', array (
  'size' => 7,
  'readonly'=>'true',
  'control_name' => 'company[vat_no]',
)); echo $value ? $value : '&nbsp;' ;

      }?>
<!--      <br>write prefix 'test' with Vat number .-->
 
    </div>
</div>
<div class="form-row">
  <?php echo label_for('company[password]', __("Password"), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{password}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{password}')): ?>
    <?php echo form_error('company{password}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getPassword', array (
  'size' => 80,
  'minlength'=>6,
  'control_name' => 'company[password]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>
<div class="form-row">
  <?php echo label_for('company[address]', __($labels['company{address}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{address}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{address}')): ?>
    <?php echo form_error('company{address}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getAddress', array (
  'size' => 80,
  'control_name' => 'company[address]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('company[post_code]', __($labels['company{post_code}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{post_code}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{post_code}')): ?>
    <?php echo form_error('company{post_code}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getPostCode', array (
  'size' => 80,
   'minlength'=>4,
   'maxlength'=>5,
  'control_name' => 'company[post_code]',
)); echo $value ? $value : '&nbsp;' ?><br><label id="companyPost"></label>
    </div>
</div>
<div class="form-row">
   <?php echo label_for('company[country_id]', __($labels['company{country_id}']), 'class="required" ') ?>
          <div class="content<?php if ($sf_request->hasError('company{country_id}')): ?> form-error<?php endif; ?>">
              <?php if ($sf_request->hasError('company{country_id}')): ?>              
              <?php echo form_error('company{country_id}', array('class' => 'form-error-msg')) ?>
              <?php endif; ?>
              <?php $value = object_select_tag($company, 'getCountryId', array (
                      'related_class' => 'Country',
                      'control_name' => 'company[country_id]',
                      'peer_method'=>'getSortedCountries',
                      //'include_blank' => true,
                      'onchange'=> remote_function(array(
                                'update'  => 'citySelectList',
                                'url'     => 'company/countrycity',
                                'with' => "'country_id=' + this.options[this.selectedIndex].value"
                                ))
                    ),53); echo $value ? $value : '&nbsp;' ?>
          </div>
</div>
  
<div id="countrylist">
    <div class="form-row">
      <?php echo label_for('company[city_id]', __($labels['company{city_id}']), '') ?>
      <div class="content<?php if ($sf_request->hasError('company{city_id}')): ?> form-error<?php endif; ?>">
      <?php if ($sf_request->hasError('company{city_id}')): ?>
        <?php echo form_error('company{city_id}', array('class' => 'form-error-msg')) ?>
      <?php endif; ?>
          <div id="citySelectList">
    <?php $value = object_select_tag($company, 'getCityId', array (
      'related_class' => 'City',
      'control_name' => 'company[city_id]',
         'peer_method'=>'getSortedSweedishCities',
  
    )); echo $value ? $value : '&nbsp;' ?>
     </div>
        </div>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('company[contact_name]', __($labels['company{contact_name}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{contact_name}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{contact_name}')): ?>
    <?php echo form_error('company{contact_name}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

 <?php $value = object_input_tag($company, 'getContactName', array (
      'size' => 80,
      'control_name' => 'company[contact_name]',
    )); echo $value ? $value : '&nbsp;' ?>
   </div>
</div>
<div class="form-row">
  <?php echo label_for('company[email]', __($labels['company{email}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{email}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{email}')): ?>
    <?php echo form_error('company{email}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getEmail', array (
  'size' => 80,
  'control_name' => 'company[email]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('company[head_phone_number]', __($labels['company{head_phone_number}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{head_phone_number}')): ?> form-error<?php endif; ?>">
  
  <?php if ($sf_request->hasError('company{head_phone_number}')): ?>
    <?php echo form_error('company{head_phone_number}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>
<span id="countrycode" class="countrycode"></span>
  <?php $value = object_input_tag($company, 'getHeadPhoneNumber', array (
  'size' => 7,
  'control_name' => 'company[head_phone_number]',
  'class' => 'mobile_number'
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('company[fax_number]', __($labels['company{fax_number}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('company{fax_number}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{fax_number}')): ?>
    <?php echo form_error('company{fax_number}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getFaxNumber', array (
  'size' => 7,
  'control_name' => 'company[fax_number]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('company[website]', __($labels['company{website}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('company{website}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{website}')): ?>
    <?php echo form_error('company{website}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getWebsite', array (
  'size' => 80,
  'control_name' => 'company[website]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>
<?php //if (!$company->isNew()){ ?>
<div class="form-row">
  <?php echo label_for('company[credit_limit]', __($labels['company{credit_limit}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('company{credit_limit}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{credit_limit}')): ?>
    <?php echo form_error('company{credit_limit}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($company, 'getCreditLimit', array (
  'size' => 80,
  'control_name' => 'company[credit_limit]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>
<?php //} ?>
<div class="form-row">
  <?php echo label_for('company[status_id]', __($labels['company{status_id}']),  'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('company{status_id}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{status_id}')): ?>
    <?php echo form_error('company{status_id}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_select_tag($company, 'getStatusId', array (
  'related_class' => 'Status',
  'control_name' => 'company[status_id]',
     'include_custom' => ' ',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>
<?php   // 'include_custom' => 'Select Status',   ?>

<div class="form-row">
  <?php echo label_for('company[comments]', __($labels['company{comments}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('company{comments}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('company{comments}')): ?>
    <?php echo form_error('company{comments}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_textarea_tag($company, 'getComments', array (
  'size' => '30x3',
  'control_name' => 'company[comments]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div> 
<?php if($company->isNew()){ ?>

<input type="hidden" value="" id="error" name="error" style="display: none;" />
<?php }?>

</fieldset>


<?php include_partial('edit_actions', array('company' => $company)) ?>
<script type="text/javascript">
   jQuery(function(){
         jQuery("#company_country_id").change(function(){
            jQuery.post("<?php echo sfConfig::get('app_admin_url') ?>company/getCountryCallingCode",{country_id:jQuery(this).val() } ,function(data){
               // alert(data);
               jQuery("#countrycode").html("+"+data);
            });
        });
        jQuery("#company_country_id").trigger("change");
   });
</script>

</form>
