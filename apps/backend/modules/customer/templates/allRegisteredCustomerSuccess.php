<?php

/* 
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

?>

<div id="sf_admin_container"><h1><?php echo  __('All Registered Customer') ?></h1></div><br />
<p style="color: #0033DD;font-weight: bold;"><?php echo @$message;?></p>
<table width="75%" cellspacing="0" cellpadding="2" class="tblAlign">
    <thead>
        <tr class="headings">
            <th width="10%" style="text-align: left" >Id</th>
            <th  width="20%" style="text-align: left"  >Customer Number</th>
            <th  width="20%" style="text-align: left" >Mobile Number</th>
            <th width="20%" style="text-align: left" >Customer Name/Contact Person Name</th>
              <th width="20%" style="text-align: left" >Company Name</th>
          
            <th  width="20%"  style="text-align: left" >Unique ID</th>
            <th width="10%" style="text-align: left"  >Action</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align:center;font-weight: bold;">
                <?php echo count($customers)." - Results" ?>
            </td>
        </tr>
    </tfoot>
    <tbody>
        <?php   $incrment=1;    ?>
        <?php foreach($customers as $customer): ?>
        <?php
        if($incrment%2==0){
         $class= 'class="even"';
        }else{
         $class= 'class="odd"';
        }
        ?>
        <tr <?php echo $class;?>>
            <td><?php echo $incrment;  ?></td>
            <td><?php  echo $customer->getId() ?></td>
            <td><?php echo  $customer->getMobileNumber() ?></td>
            <td><?php if($customer->getBusiness()){    echo $customer->getLastName();  }else{   echo  $customer->getFirstName()."&nbsp".$customer->getLastName(); } ?></td>
              <td><?php if($customer->getBusiness()){ echo  $customer->getFirstName(); }else{     } ?></td>
             
            <td><?php echo  $customer->getUniqueid() ?></td>
            <td><a href="<?php echo url_for('customer/editcustomer?id='.$customer->getId()) ?>"><img src="<?php echo sfConfig::get('app_web_url');?>sf/sf_admin/images/edit_icon.png" title="edit" alt="edit" /></a>&nbsp;<a href="customerDetail?id=<?php  echo $customer->getId() ?>"><img alt="view Detail" title="view Detail" src="<?php echo sfConfig::get('app_web_url');?>sf/sf_admin/images/default_icon.png" /></a>
            </td>
        </tr>
        <?php   $incrment++;    ?>
        <?php endforeach; ?>
    </tbody>
</table>




