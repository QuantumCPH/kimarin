<?php
$countryid="";
if(isset($_REQUEST['countryid'])){
$countryid =$_REQUEST['countryid'];

}
if(isset($countryid) && $countryid!=""){
	$countryid=$countryid;
}else{
	$countryid=2;
}
$c = new Criteria();
$enablecountrys=EnableCountryPeer::doSelect($c);	
?>
                       
                       <script type="text/javascript">
		jQuery(document).ready(function()
		{
			 jQuery('#massageform').validate();
			
			jQuery("#paradigm_all").click(function()				
			{
				var checked_status = this.checked;
				jQuery("input[id=paradigm]").each(function()
				{
					this.checked = checked_status;
				});
			});					
		});
		
		</script>
        
        <script>
    jQuery(function(){
      // bind change event to select
      jQuery('#dynamic_select').bind('change', function () {
          var url = jQuery(this).val(); // get selected value
          if (url) { // require a URL
              window.location = url; // redirect
          }
          return false;
      });
    });
</script>

<style>
.error{
	
	color:#F00000;
}
</style>
<div id="sf_admin_container">
    <h1><?php echo  __('Send Bulk Messages') ?></h1><br />

      <form action="" method="post" id="massageform" >
        
<table cellspacing="0" cellpadding="2"  width="100%">


<tr><td colspan="2" style="border:0 !important;" >
 <?php if(isset($delievry) && $delievry!=""){ ?>

<h6>  

    <?php echo $delievry ?></h6>

<?php
} 
else 
{
}
?></td></tr>


 

<tr><td colspan="2" style="border:0 !important;">
  
<table cellspacing="0" cellpadding="3" class="tblAlign" width="100%">

<tr class="headings"> <th>Action </th> <th> Agent Name</th><th>Agent  Email </th><th>Mobile Number </th></tr>
<?php 
  $c = new Criteria();
    
		$agents=AgentCompanyPeer::doSelect($c);  ?>
        <?php  foreach($agents as $agentt){  ?>
				<tr> 
                    <td><input type="checkbox"   name="agent['<?php echo  $agentt->getId() ?>']"  id="paradigm" value="<?php echo  $agentt->getId() ?>"  /></td> 
                    <td> <?php  echo $agentt->getName();   ?> </td>
                     <td> <?php  echo $agentt->getEmail();   ?> </td>
                      <td> <?php  echo $agentt->getMobileNumber();   ?> </td>
			</tr>
<?php  } ?>
</table>

</td></tr>
<tr> <td colspan="4" style="border:0 !important;">	<input type="checkbox" id="paradigm_all" />&nbsp;Select All<br /></td></tr>
<tr><td align="left" valign="top" style="border:0 !important;">Message</td>
    <td  style="border:0 !important;" colspan="3" valign="top" align="left">
        <textarea name="massagetext" cols="25" rows="10" class="required"></textarea>
    </td>
</tr>

<tr> <td style="border:0 !important;">Send Message </td> <td style="border:0 !important;">Email&nbsp;<input type="radio"  name="massage" value="1" checked="checked"/>&nbsp;&nbsp;SMS&nbsp;<input type="radio" name="massage"  value="2" />&nbsp;&nbsp;Both&nbsp;<input type="radio"  name="massage" value="3" /> </td></tr>

<tr><td style="border:0 !important;"><div id="sf_admin_container" style="float:left !important;">
  <ul class="sf_admin_actions" style="float:left !important;">
   <li>
       <input type="submit" name="Send" value="Send" class="sf_admin_action_save_and_add" />   
   </li>
  </ul></div></td><td colspan="3" style="border:0 !important;"></td></tr>
</table>
</form>
</div>