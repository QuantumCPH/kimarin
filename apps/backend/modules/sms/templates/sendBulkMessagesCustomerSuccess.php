<?php
$id="";
$pid="";
if(isset($_REQUEST['id'])){
$id =$_REQUEST['id'];

}
if(isset($_REQUEST['pid'])){
$pid =$_REQUEST['pid'];

}
if(isset($id) && $id!=""){
	$id=$id;
}else{
	$id=0;
}
if(isset($pid) && $pid!=""){
	$pid=$pid;
}else{
	$pid=0;
}



//  $criteria->addJoin(PhotoPeer::ID_BUILDING, BuildingPeer::ID, Criteria::LEFT_JOIN);
//    $criteria->add(BuildingPeer::IS_PUBLIC, 1, Criteria::EQUAL);
//    $criteria->addAscendingOrderByColumn(BuildingPeer::PRICE);

  	  $c = new Criteria();
		$registrations=RegistrationTypePeer::doSelect($c);	
		
			  $p = new Criteria();
             // 
                $p->addJoin(ProductPeer::ID,CustomerProductPeer::PRODUCT_ID, Criteria::LEFT_JOIN);
               $p->addJoin(CustomerProductPeer::CUSTOMER_ID,CustomerPeer::ID, Criteria::LEFT_JOIN);
                 if(isset($id) && $id>0){
                 $p->addAnd(CustomerPeer::REGISTRATION_TYPE_ID,$id);
                 }
                 
              $p->addAnd(CustomerPeer::CUSTOMER_STATUS_ID,3);
  
                $p->addGroupByColumn(ProductPeer::DESCRIPTION);
		$products=ProductPeer::doSelect($p);
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
       jQuery('#dynamic_selectp').bind('change', function () {
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
</style><div id="sf_admin_container">
    <h1><?php echo  __('Send Bulk Messages') ?></h1><br />
      <form action="" method="post" id="massageform" >
        
          <table cellspacing="0" cellpadding="2" width="100%">


<tr><td colspan="4" style="border:0 !important;">
 <?php if(isset($delievry) && $delievry!=""){ ?>

<h6>  

    <?php echo $delievry ?></h6>

<?php
} 
else 
{
}
?></td></tr>


<tr><td style="border:0 !important;">Select Registration Type</td>  <td style="border:0 !important;"> <select id="dynamic_select"><?php  foreach($registrations as $registration){  ?>
	
    <option value="sendBulkMessagesCustomer?id=<?php  echo $idcountry=$registration->getId(); ?>"  
     <?php if(isset($id) && $idcountry==$id){ ?> selected="selected"  <?php  } ?>  > <?php  echo $registration->getDescription();   ?></option > 
    
   
<?php	
} ?> <option value="sendBulkMessagesCustomer?id=0"    <?php if(isset($id) && $id==0){ ?> selected="selected"  <?php  } ?>   >All Customer</option>
     </select>  </td> 
<td style="border:0 !important;">Product Name</td>
    <td style="border:0 !important;"> <select id="dynamic_selectp"><?php  foreach($products as $product){  ?>

    <option value="sendBulkMessagesCustomer?id=<?php  echo $id;   ?>&pid=<?php  echo $idcountry=$product->getId(); ?>"
     <?php if(isset($pid) && $idcountry==$pid){ ?> selected="selected"  <?php  } ?>  > <?php  echo $product->getName();   ?></option >


<?php
} ?> 
     </select>  </td>  </tr>
<tr></tr>

<tr><td colspan="4" style="border:0 !important;"><br />
        <div style="overflow: scroll;height:300px;">
        <table  width="100%" cellspacing="0" cellpadding="3" class="tblAlign">

<tr class="headings"> <th>Action </th> <th> Customer  Name</th><th>Customer  Email </th><th>Mobile Number </th></tr>
<?php 
  $c = new Criteria();

  $c->addAnd(CustomerPeer::CUSTOMER_STATUS_ID,3);
  
  if(isset($id) && $id==0){
  if(isset($pid) && $pid>0){
     $c->addJoin(CustomerPeer::ID,CustomerProductPeer::CUSTOMER_ID, Criteria::LEFT_JOIN);
     $c->addAnd(CustomerProductPeer::PRODUCT_ID, $pid);
}
  }else{
if(isset($pid) && $pid>0){
     $c->addJoin(CustomerPeer::ID,CustomerProductPeer::CUSTOMER_ID, Criteria::LEFT_JOIN);
     $c->addAnd(CustomerProductPeer::PRODUCT_ID, $pid);
}
   $c->addAnd(CustomerPeer::REGISTRATION_TYPE_ID, $id);
  }
    
		$agents=CustomerPeer::doSelect($c);  ?>
        <?php  foreach($agents as $agentt){  ?>
				<tr> 
                    <td><input type="checkbox"   name="agent['<?php echo  $agentt->getId() ?>']"  id="paradigm" value="<?php echo  $agentt->getId() ?>"  /></td> 
                    <td> <?php  echo $agentt->getFirstName();   ?> <?php  echo $agentt->getLastName();   ?></td>
                     <td> <?php  echo $agentt->getEmail();   ?> </td>
                      <td> <?php  echo $agentt->getMobileNumber();   ?> </td>
			</tr>
<?php  } ?>
</table>
        </div>
</td></tr>
<tr> <td colspan="4" style="border:0 !important;">
        <input type="checkbox" id="paradigm_all" />&nbsp;Select All<br /></td></tr>
<tr><td align="left" valign="top" style="border:0 !important;">Message</td> <td colspan="3" valign="top" align="left" style="border:0 !important;"><textarea name="massagetext" cols="25" rows="10" class="required"></textarea></td></tr>

<tr> <td style="border:0 !important;">Send Message </td> <td style="border:0 !important;"> Email&nbsp;<input type="radio"  name="massage" value="1" checked="checked" />&nbsp;&nbsp;SMS&nbsp;<input type="radio" name="massage"  value="2" />&nbsp;&nbsp;Both&nbsp;<input type="radio"  name="massage" value="3" /> </td></tr>

<tr><td style="border:0 !important;"><div id="sf_admin_container" style="float:left !important;">
  <ul class="sf_admin_actions" style="float:left !important;">
   <li>
       <input type="submit" name="Send" value="Send" class="sf_admin_action_save_and_add" />   
   </li>
  </ul></div></td><td colspan="3" style="border:0 !important;"></td></tr>
</table>
</form>
</div>