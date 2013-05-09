<?php use_helper('I18N') ?><?php use_helper('Number') ?><div  id="sf_admin_container">
<h1><?php echo __('My employee List') ?></h1><br />
<?php if(isset($_REQUEST['message']) && $_REQUEST['message']=="edit"){  ?>

<?php if ($sf_user->hasFlash('messageEdit')): ?>
<div class="save-ok">
    <h2><?php echo __($sf_user->getFlash('messageEdit')) ?></h2>
</div>
<?php endif; ?>
<?php  }   ?>

<?php if(isset($_REQUEST['message']) && $_REQUEST['message']=="add"){  ?>
<?php if ($sf_user->hasFlash('messageAdd')): ?>
<div class="save-ok">
    <h2><?php echo __($sf_user->getFlash('messageAdd')) ?></h2>
</div>
<?php endif; ?>
<?php  }   ?>


<div id="sf_admin_header">
<a target="_self" class="external_link" href="<?php echo url_for('employee/add'); if(isset($companyval) && $companyval!=""){echo "?company_id=".$companyval;} ?>" style="text-decoration:none;">Create New</a>
</div>
<br>
<?php if ($sf_user->hasFlash('message')): ?>
<div class="save-ok">
 <?php echo __($sf_user->getFlash('message')) ?>
</div>
<?php endif; ?>

<br/>
<table width="950"  style="border: 1px;" class="sf_admin_list" cellspacing="0">
  <thead>
      <tr class="thheading">
      
      <th width="91" align="left"  id="sf_admin_list_th_name">Company</th>
      <th width="103" align="left"  id="sf_admin_list_th_name">First name</th>
     
      <th width="138" align="left"  id="sf_admin_list_th_name">product</th>
    
      <th width="105" align="left" id="sf_admin_list_th_name">Mobile number</th>
<!--       <th align="left">Resenumber</th>-->
       <?php  if(isset($companyval) && $companyval!=""){  ?>
        <th width="136" align="left"  id="sf_admin_list_th_name">Employee balance</th>
       
        <?php } ?>

        <th width="153" align="left"  id="sf_admin_list_th_name">Unique Id</th>
      <th width="116" align="left"  id="sf_admin_list_th_name">Created at</th>
   
 <!--         <th align="left">App code</th>
   
      <th align="left">Password</th>-->
        <th width="90" align="left"  id="sf_admin_list_th_name">Action</th>
    </tr>
  </thead>
  <tbody>
        <?php      $amount_total = 0;
                       $incrment=1;
   foreach ($employees as $employee): ?>

       <?php
                  if($incrment%2==0){
                  $class= 'class="even"';
                  }else{

                       $class= 'class="odd"';
                      }
 $incrment++;
                  ?>
    <tr <?php echo $class; ?>>
    
      <td><?php  $comid=$employee->getCompanyId();
      if(isset($comid) && $comid!=""){
               $c = new Criteria();
      $c->add(CompanyPeer::ID, $employee->getCompanyId());
  $companys = CompanyPeer::doSelectOne($c);

              echo $companys->getName();
      }
              ?></td>
      <td><?php echo htmlspecialchars($employee->getFirstName()); ?></td>
  
      <td>
          <?php  $pid=$employee->getProductId();
      if(isset($pid) && $pid!=""){
               $c = new Criteria();
      $c->add(ProductPeer::ID, $pid);
  $products = ProductPeer::doSelectOne($c);

              echo $products->getName();
      }
              ?>      </td>
      <td><?php echo $employee->getMobileNumber() ?></td>
<!--      <td>-->
           <?php

//
//                            $empid=$employee->getRegistrationType();
//                          if(isset($empid) && $empid==1){ ?>


				   <?php    $voip = new Criteria();
//        $voip->add(SeVoipNumberPeer::CUSTOMER_ID, $employee->getCountryMobileNumber());
//        $voip->addAnd(SeVoipNumberPeer::IS_ASSIGNED, 1);
//        $voipv = SeVoipNumberPeer::doSelectOne($voip);
//
//                         if(isset ($voipv)){echo $voipv->getNumber();} ?>
				 


                            <?php  //}else{echo "No";} ?>
<!--</td>-->
 <?php  if(isset($companyval) && $companyval!=""){  ?>
      <td> <?php  $mobileID= $employee->getCountryMobileNumber();
        $ComtelintaObj = new CompanyEmployeActivation();
        $ct = new Criteria();
        $ct->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'a'.$mobileID);
        $ct->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccount = TelintaAccountsPeer::doSelectOne($ct);
        $accountInfo = $ComtelintaObj->getAccountInfo($telintaAccount->getIAccount());
        $telintaGetBalance = $accountInfo->account_info->balance;
       
       
        $cb = new Criteria();
        $cb->add(TelintaAccountsPeer::ACCOUNT_TITLE, 'cb'.$mobileID);
        $cb->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccountcb = TelintaAccountsPeer::doSelectOne($cb);
        if($telintaAccountcb){
            $accountInfocb = $ComtelintaObj->getAccountInfo($telintaAccountcb->getIAccount());
            $telintaGetBalancecb = $accountInfocb->account_info->balance;
        }else{
            $telintaGetBalancecb=0; 
        }
            
        $cd = new Criteria();
        $cd->add(TelintaAccountsPeer::ACCOUNT_TITLE, $employee->getMobileNumber());
        $cd->addAnd(TelintaAccountsPeer::STATUS, 3);
        $telintaAccountcd = TelintaAccountsPeer::doSelectOne($cd);
        if($telintaAccountcd){
            $accountInfocb = $ComtelintaObj->getAccountInfo($telintaAccountcd->getIAccount());
            $telintaGetBalancecd = $accountInfocb->account_info->balance;
        }else{
            $telintaGetBalancecd=0; 
        }

         $regtype=$employee->getRegistrationType();
        $telintaGetBalanceres = 0;
        if(isset($regtype) && $regtype==1){
        $voip = new Criteria();

        $voip->add(SeVoipNumberPeer::CUSTOMER_ID, $mobileID);
        $voip->addAnd(SeVoipNumberPeer::IS_ASSIGNED, 1);
        $voipv = SeVoipNumberPeer::doSelectOne($voip);

            if(isset ($voipv)){

                $resenummer=$voipv->getNumber();
                $resenummer = substr($resenummer, 2);

                $res = new Criteria();
                $res->add(TelintaAccountsPeer::ACCOUNT_TITLE, $resenummer);
                $res->addAnd(TelintaAccountsPeer::STATUS, 3);
                $telintaAccountres = TelintaAccountsPeer::doSelectOne($res);
                if($telintaAccountres){
                  $accountInfores = $ComtelintaObj->getAccountInfo($telintaAccountres->getIAccount());
                    $telintaGetBalanceres = $accountInfores->account_info->balance;  
                }else{
                    $telintaGetBalanceres = 0;
                }
            }
        }
      echo  $balnc=(float)$telintaGetBalance+(float)$telintaGetBalancecb+(float)$telintaGetBalancecd+(float)$telintaGetBalanceres;
          echo sfConfig::get('app_currency_code');
     ?></td>

      
      <?php } ?>
      <td><?php echo $employee->getUniqueId(); ?></td>
      <td><?php echo $employee->getCreatedAt('d-m-Y'); ?></td>
   
    <!--  <td align="center">  <?php //$appval=$employee->getIsAppRegistered();  if(isset($appval) && $appval==1){   ?> <img alt="Tick" src="/sf/sf_admin/images/tick.png">  <?php //} ?></td>
       <td><?php //echo $employee->getAppCode() ?></td>
       <td><?php //echo $employee->getPassword() ?></td>-->
       <td><a href="<?php echo url_for('employee/edit?id='.$employee->getId()) ?>"><img src="<?php echo sfConfig::get('app_web_url') ?>/sf/sf_admin/images/edit_icon.png" title="edit" alt="edit" /></a><a href="employee/del?id=<?php echo $employee->getId(); if(isset($companyval) && $companyval!=""){echo "&company_id=".$companyval;} ?>" ><img src="<?php echo sfConfig::get('app_web_url') ?>/sf/sf_admin/images/delete_icon.png" title="delete" alt="delete" /></a>
       <a href="<?php echo url_for('employee/view?id='.$employee->getId()) ?>"><img src="<?php echo sfConfig::get('app_web_url') ?>/sf/sf_admin/images/default_icon.png" title="view" alt="view" /></a>
        <!--    <a href="<?php echo url_for('employee/view?id='.$employee->getId()) ?>"><img src="/sf/sf_admin/images/default_icon.png" title="view" alt="call history"></a>
     -->  </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<br/>
  <div id="sf_admin_header">
<a target="_self" class="external_link" href="<?php echo url_for('employee/add'); if(isset($companyval) && $companyval!=""){echo "?company_id=".$companyval;} ?>" style="text-decoration:none;">Create New</a>

</div>

	