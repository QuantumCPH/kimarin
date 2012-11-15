<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$pus=0;
?>
<div id="sf_admin_container">
     <ul class="customerMenu" style="margin:10px 0;">
            <li><a class="external_link" href="allRegisteredCustomer"><?php echo  __('View All Customer') ?></a></li>
            <li><a class="external_link" href="paymenthistory?id=<?php echo $_REQUEST['id'];  ?>"><?php echo  __('Payment History') ?></a></li>
            <li><a class="external_link"  href="callhistory?id=<?php echo $_REQUEST['id'];  ?>"><?php echo  __('Call History') ?></a></li>
        </ul>
<h1><?php echo  __('Customer Detail') ?></h1>
    <table width="100%" cellspacing="0" cellpadding="2" class="tblAlign">



                          <tr>
                    <td width="17%" class="leftHeadign">Customer Balance</td>
                     <td width="83%"  ><?php
                           $uniqueId=$customer->getUniqueid();
                           $cuid=$customer->getId();
                                  $cp = new Criteria();
                                  $cp->add(CustomerProductPeer::CUSTOMER_ID, $cuid);
                                  $custmpr = CustomerProductPeer::doSelectOne($cp);
                                   $p = new Criteria();
                                   $p->add(ProductPeer::ID, $custmpr->getProductId());
                                   $products=ProductPeer::doSelectOne($p);
                                   $pus = 0;

                                  $pus=$products->getProductCountryUs();
               if($pus==1){
                            $Tes=ForumTel::getBalanceForumtel($customer->getId());
                               echo  $amt=CurrencyConverter::convertUsdToSek($Tes);
   echo sfConfig::get('app_currency_code');
                            }else{


        echo   number_format($customer_balance,2);
          echo sfConfig::get('app_currency_code');
                            }
                          
                     ?> </td>
                      </tr>

                     
                   <tr>
                    <td    class="leftHeadign"  >Id</td>
                     <td  ><?php  echo $customer->getId() ?></td>
                      </tr>
                      <?php  if($customer->getBusiness()){    ?>
                    
                            <tr>
                        <td id="sf_admin_list_th_first_name" class="leftHeadign" >Company Name</td>
                        <td><?php echo  $customer->getFirstName() ?></td>
                       </tr>
                   
                      <tr >
                    <td id="sf_admin_list_th_last_name"  class="leftHeadign" >Name of contact person</td>
                       <td><?php echo  $customer->getLastName() ?></td>
                          </tr>
                            <tr >
                    <td id="sf_admin_list_th_last_name"  class="leftHeadign" >E-mail of contact person</td>
                    <td><?php echo  $customer->getEmail(); ?></td>
                          </tr>
                            <?php   }else{ ?>
                          
                            <tr>
                        <td id="sf_admin_list_th_first_name" class="leftHeadign" >First Name</td>
                        <td><?php echo  $customer->getFirstName() ?></td>
                       </tr>
                       <tr >
                    <td id="sf_admin_list_th_last_name"  class="leftHeadign" >Middle Name</td>
                    <td><?php echo  $customer->getSecondLastName() ?></td>
                          </tr> 
                      <tr >
                    <td id="sf_admin_list_th_last_name"  class="leftHeadign" >Last Name</td>
                       <td><?php echo  $customer->getLastName() ?></td>
                          </tr>
                          
                            <tr >
                    <td id="sf_admin_list_th_last_name"  class="leftHeadign" >Email</td>
                    <td><?php echo  $customer->getEmail(); ?></td>
                          </tr>
                            <?php   } ?>
                           
                      <tr>
		        <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >Mobile Number</td>
                      <td><?php echo  $customer->getMobileNumber() ?></td>
                         </tr>
                         
                        <tr>
		        <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >N.I.E./Passport Number</td>
                        <td><?php echo  $customer->getNiePassportNumber() ?></td>
                         </tr>                         
                         <tr>
		           <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >Password</td>
                         <td><?php echo  $customer->getPlainText() ?></td>
                       </tr>
                        <tr>
		          <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >Nationality</td>
                          <td><?php echo  $customer->getNationalityTitle(); ?></td>
                         </tr> 
                          <tr>
		          <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >Sim Types</td>
                          <td><?php echo  $customer->getSimType(); ?></td>
                         </tr>
                         <tr>
		          <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >Province</td>
                          <td><?php echo  $customer->getProvinceName(); ?></td>
                         </tr>
                         <tr>
		          <td id="sf_admin_list_th_mobile_number" class="leftHeadign"  >Preferred Language</td>
                          <td><?php echo  $customer->getPreferredLanguage(); ?></td>
                         </tr>
                         <tr>
		          <td id="sf_admin_list_th_mobile_number" class="leftHeadign">Current Product</td>
                          <td><?php echo  $customer->getCurrentProduct(); ?></td>
                         </tr>
                         <tr>
		          <td id="sf_admin_list_th_mobile_number" class="leftHeadign">Subscribed Product</td>
                          <td><?php echo  $customer->getSubscribedProduct(); ?></td>
                         </tr>
                       
<?php
$val="";
$val=$customer->getReferrerId();
if(isset($val) && $val!=""){  ?>
                      <tr>
		    <td id="sf_admin_list_th_agent" class="leftHeadign" >Agent</td>
                    <?php $agent = AgentCompanyPeer::retrieveByPK( $customer->getReferrerId()) ?>
                  <td><?php echo  $agent->getName() ?></td>
                      </tr>
                         <tr>
                      <td id="sf_admin_list_th_agent" class="leftHeadign" >Agent CVR</td>
                      <td><?php echo  $agent->getCvrNumber() ?></td>
		      </tr>

                      <?php } ?>
                         <tr>
                      <td id="sf_admin_list_th_address"  class="leftHeadign" >Address</td>
                        <td><?php echo  $customer->getAddress() ?></td>
                      </tr>
                         <tr>
                      <td id="sf_admin_list_th_city"  class="leftHeadign" >City</td>
                        <td><?php echo  $customer->getCity() ?></td>
                      </tr>
                         <tr>
                      <td id="sf_admin_list_th_po_box_number"  class="leftHeadign" >PO-BOX Number</td>
                      <td><?php echo  $customer->getPoBoxNumber() ?></td>

                      </tr>
                       
                         <tr>
                      <td id="sf_admin_list_th_created_at"  class="leftHeadign" >Created At</td>
                            <td><?php echo  $customer->getCreatedAt('d-m-Y') ?></td>

  </tr>
                         <tr>

                    <td id="sf_admin_list_th_date_of_birth" class="leftHeadign" >Date Of Birth</td>
                      <td><?php echo  $customer->getDateOfBirth() ?></td>
                      </tr>
<!--                         <tr>
                      <td id="sf_admin_list_th_auto_refill" class="leftHeadign" >Auto Refill</td>
                        <?php if ($customer->getAutoRefillAmount()!=NULL && $customer->getAutoRefillAmount()>1){ ?>
                  <td>Yes</td>
                  <?php } else
                      { ?>
                  <td>No</td>
                  <?php } ?>
                        </tr>-->
                         <tr>
                        <td id="sf_admin_list_th_auto_refill" class="leftHeadign" >Unique ID</td>
                         <td>  <?php  echo $customer->getUniqueid();     ?>   </td>
                        </tr  >
                        <?php
                            $oun = new Criteria();
                            $oun->add(UniqueidLogPeer::CUSTOMER_ID, $cuid);
                            $oun -> addAscendingOrderByColumn(UniqueidLogPeer::CREATED_AT);
                            $old_number = UniqueidLogPeer::doSelectOne($oun);
                            if($old_number!=''){
                        ?>
                        <tr>
                            <td id="sf_admin_list_th_auto_refill" class="leftHeadign" >Old Unique ID</td>
                            <td><?php  echo $old_number->getUniqueNumber();?></td>
                        </tr>
                        <?php }?>
<!--                        <tr>
                        <td id="sf_admin_list_th_auto_refill" class="leftHeadign" >Usage Email Alerts</td>
                         <td>  <?php  echo ($customer->getUsageAlertEmail()==1)?"Yes":"No";     ?>   </td>
                        </tr  >
                        <tr>
                        <td id="sf_admin_list_th_auto_refill" class="leftHeadign" >Usage SMS Alerts</td>
                         <td>  <?php  echo ($customer->getUsageAlertSMS()==1)?"Yes":"No";     ?>   </td>
                        </tr  >-->
                          <tr><td  id="sf_admin_list_th_auto_refill" class="leftHeadign" >Comments</td>
                  <td><?php echo $customer->getComments(); ?></td>
                </tr>
                         <tr style="background-color:#EEEEFF">
                       <td id="sf_admin_list_th_auto_refill" class="leftHeadign" >Active No</td>
                        <td>  <?php  $unid   =  $customer->getUniqueid(); 
        if(isset($unid) && $unid!=""){
            $un = new Criteria();
            $un->add(CallbackLogPeer::UNIQUEID, $unid);
            $un -> addDescendingOrderByColumn(CallbackLogPeer::CREATED);
            $unumber = CallbackLogPeer::doSelectOne($un);
            // This Condition For - It register Via Web
            if($unumber->getCheckStatus()=='2'){
                $getFirstnumberofMobile = substr($unumber->getMobileNumber(), 0,1);     // bcdef
                if($getFirstnumberofMobile==0){
                  $TelintaMobile = substr($unumber->getMobileNumber(), 1);
                  $TelintaMobile =  sfConfig::get('app_country_code').$TelintaMobile ;
                }else{
                  $TelintaMobile = ''.$unumber->getMobileNumber();
                }
              $TelintaMobile="00".$TelintaMobile;

                 $Telintambs=$TelintaMobile;

 echo substr($Telintambs, 0,4); echo " "; echo substr($Telintambs, 4,3);
echo "    ";   echo substr($Telintambs, 7,2);
echo " ";   echo substr($Telintambs, 9,2);
echo " ";   echo substr($Telintambs, 11,2);
echo " ";   echo substr($Telintambs, 13,2);
echo " ";   echo substr($Telintambs, 15,2);
            }else{
               $TelintaMobile="00".$unumber->getMobileNumber();



                 $Telintambs=$TelintaMobile;

 echo substr($Telintambs, 0,4); echo " ";   echo substr($Telintambs, 4,3);
echo " ";   echo substr($Telintambs, 7,2);
echo " ";   echo substr($Telintambs, 9,2);
echo " ";   echo substr($Telintambs, 11,2);
echo " ";   echo substr($Telintambs, 13,2);
echo " ";   echo substr($Telintambs, 15,2);
            }
         }else{
                $getFirstnumberofMobile = substr($customer->getMobileNumber(), 0,1);     // bcdef
                if($getFirstnumberofMobile==0){
                    $TelintaMobile = substr($customer->getMobileNumber(), 1);
                   $TelintaMobile =  '0034'.$TelintaMobile ;
  $Telintambs=$TelintaMobile;

 echo substr($Telintambs, 0,4); echo " ";   echo substr($Telintambs, 4,3);
echo " ";   echo substr($Telintambs, 7,2);
echo " ";   echo substr($Telintambs, 9,2);
echo " ";   echo substr($Telintambs, 11,2);
echo " ";   echo substr($Telintambs, 13,2);
echo " ";   echo substr($Telintambs, 15,2);
                }else{
                  $TelintaMobile = '0034'.$customer->getMobileNumber();

                    $Telintambs=$TelintaMobile;

 echo substr($Telintambs, 0,4); echo " ";   echo substr($Telintambs, 4,3);
echo " ";   echo substr($Telintambs, 7,2);
echo " ";   echo substr($Telintambs, 9,2);
echo " ";   echo substr($Telintambs, 11,2);
echo " ";   echo substr($Telintambs, 13,2);
echo " ";   echo substr($Telintambs, 15,2);
                }
           
          
         }?> </td>
                         </tr>
                         <?php  $uid=0;
                      $uid=$customer->getUniqueid();
                      if(isset($uid) && $uid>0){
                      ?>

                       <tr  style="background-color:#FFFFFF">
                    <td    class="leftHeadign"  >IMSI number</td>
                     <td  ><?php  echo $unumber->getImsi();  ?></td>
                      </tr>
                        <tr>
                    <td    class="leftHeadign"  >IMSI Registration Date</td>
                     <td  ><?php  echo $unumber->getCreated();  ?></td>
                      </tr>

                      <?php } ?>
             <tr style="background-color:#EEEEFF">
                     <td id="sf_admin_list_th_mobile_number" class="leftHeadign">Mobile service
provider</td>
                        <td>  <?php  $tcid   =  $customer->getTelecomOperatorId();
        
            $un = new Criteria();
            $un->add(TelecomOperatorPeer::ID, $tcid);
          
             $vounumber = TelecomOperatorPeer::doSelectOne($un);
            
            echo $vounumber->getName();
             
          ?> </td>
                         </tr>  



                  
              </table>
              
        




