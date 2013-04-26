<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
<script type="text/javascript">
    jQuery(function() {
        jQuery("#validation_result").hide();
        jQuery("#refillform").submit(function(){
          var valu = jQuery("#refillamount").val();
          var t =  /^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(valu);   
          
         // alert(t);
          if(t==false){  
              jQuery("#validation_result").show();
              jQuery("#validation_result").html('Please enter valid amount');
              
              return false;
          }else if(t==true){
              jQuery("#validation_result").html('');
              jQuery("#validation_result").hide();
              return true;
          } 
      });
    });
</script>

<div id="sf_admin_container"><h1><?php echo __('Refill') ?></h1>
<?php if ($sf_user->hasFlash('message')): ?>
<div class="save-ok">
  <h2><?php echo __($sf_user->getFlash('message')) ?></h2>
</div>
<?php endif; ?>
</div>
<form id="refillform" name="refillform" method="post" enctype="multipart/form-data" action="refillDetails">
    <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign">     
       <tr>
           <td style="padding: 11px 0 0 5px;font-weight:bold;" width="100" valign="top">Amount:<br /><small>(Airtime)</small></td>
           <td class="tdcss">
               <div>
                  <input type="text" name="refillamount" id="refillamount" value="" class="refllamount" /> <b><?php echo sfConfig::get('app_currency_code');?></b><br />
                   <label id="validation_result" class="errorresult" ></label>
               </div> 
                <div style="clear:both;"></div>
                
                <div class="nextbtndiv" style="margin-left:17px;">
                    <input type="submit" name="submit" value="Next" />
                </div>
           </td>
       </tr>   
    </table>
</form>
