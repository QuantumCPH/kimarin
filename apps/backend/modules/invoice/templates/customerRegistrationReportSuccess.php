<?php    
	use_helper('Number');
	$sf_user->setCulture('da_DK');
       
      
        
?>
<div id="sf_admin_container"><h3>Report on Dates  From :  <?php  echo date('d-m-Y', strtotime($startdate)); ?>  To : <?php  echo date('d-m-Y', strtotime($enddate)); ?></h3></div>
<div id="sf_admin_container"><h1><?php echo  __('Customer Registration Report') ?></h1></div>
<div id="sf_admin_container">
   <div class="sf_admin_filters">  
     
<?php echo form_tag('invoice/customerRegistrationReport') ?>
     <div class="form-row">
         <label> Start date/time</label> 
         <div class="content">
           <input type="text"   name="startdate" autocomplete="off" id="startdate" style="width: 90px;"  />
         </div>
     </div>    
     <div class="form-row">
       <label>  End date/time</label> 
       <div class="content">
           <input type="text"   name="enddate" autocomplete="off" id="enddate" style="width: 90px;"  />
       </div>    
     </div>   
     <div class="form-row">
            <div class="content"> 
              <input type="submit" value="Generate Report" class="user_external_link" />  
            </div>
     </div>   
       </form>
</fieldset>  
    </div>
</div>    








<table width="75%" cellspacing="0" cellpadding="2" class="tblAlign"><tbody>
 <?php
 $i=0;
      $conn = Propel::getConnection();
    $query = 'SELECT * from registration_type';
    $statement = $conn->prepare($query);
    $statement->execute();
  while ($rowObj = $statement->fetch(PDO::FETCH_OBJ))
    {
   
   ?>
        <tr class="headings"><th colspan="12"> Customer Registered Through <?php  echo $rowObj->description; ?></th></tr><br/>
        <tr class="headings">
            <th>Id</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Mobile No</th>
            <th>Password</th>
            <th>Address</th>
            <th>City</th>
            <th>PO-BOX</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Unique ID</th>
            <th>Product Name</th>
        </tr>
    
     <?php 
      $conn = Propel::getConnection();
     $queryc = 'SELECT * from customer where  customer_status_id=3 and registration_type_id="'.$rowObj->id.'"  and created_at>="'.$startdate.'" and created_at<="'.$enddate.'"';
    $statementc = $conn->prepare($queryc);
    $statementc->execute();
  while ($rowObjs = $statementc->fetch(PDO::FETCH_OBJ))
    {
   
    if($i%2==0){
        $class= 'class="even"';
    }else{
        $class= 'class="odd"';
    } ?>
      <tr  <?php echo $class;?>>
            <td> <?php  echo $rowObjs->id; ?></td>
            <td><?php  echo $rowObjs->first_name; ?></td>
            <td><?php  echo $rowObjs->last_name; ?></td>
            <td><?php  echo $rowObjs->mobile_number; ?></td>
            <td><?php  echo $rowObjs->plain_text; ?></td>
            <td><?php  echo $rowObjs->address; ?></td>
            <td><?php  echo $rowObjs->city; ?></td>
            <td><?php  echo $rowObjs->po_box_number; ?></td>
            <td><?php  echo $rowObjs->email; ?></td>
            <td><?php  echo $rowObjs->created_at; ?></td>
            <td><?php  echo $rowObjs->uniqueid; ?></td>
            <td><?php  echo $rowObjs->id; ?></td>
           
        </tr> 
        
    
    <?php  $i++; } ?>
        <tr    >
            <td colspan="12"> &nbsp; </td>
            
           
        </tr>  
        
     <?php   } ?>
        
      
<table width="75%" cellspacing="0" cellpadding="2" class="tblAlign">
   
    
    <tbody>
        <?php
         $i=0;
      $conn = Propel::getConnection();
    $query = 'SELECT * from registration_type';
    $statement = $conn->prepare($query);
    $statement->execute();
          while ($rowObj = $statement->fetch(PDO::FETCH_OBJ))
    {
   
   ?>
        <tr class="headings"><th colspan="12"> Not Completed Customer Registered Through <?php  echo $rowObj->description; ?></th></tr><br/>
        <tr class="headings">
            <th>Id</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Mobile No</th>
            <th>Password</th>
            <th>Address</th>
            <th>City</th>
            <th>PO-BOX</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Unique ID</th>
            <th>Product Name</th>
        </tr>
    
     <?php 
      $conn = Propel::getConnection();
     $queryc = 'SELECT * from customer where  customer_status_id<>3 and registration_type_id="'.$rowObj->id.'"  and created_at>="'.$startdate.'" and created_at<="'.$enddate.'"';
    $statementc = $conn->prepare($queryc);
    $statementc->execute();
  while ($rowObjs = $statementc->fetch(PDO::FETCH_OBJ))
    {
   
    if($i%2==0){
        $class= 'class="even"';
    }else{
        $class= 'class="odd"';
    } ?>
      <tr  <?php echo $class;?>>
            <td> <?php  echo $rowObjs->id; ?></td>
            <td><?php  echo $rowObjs->first_name; ?></td>
            <td><?php  echo $rowObjs->last_name; ?></td>
            <td><?php  echo $rowObjs->mobile_number; ?></td>
            <td><?php  echo $rowObjs->plain_text; ?></td>
            <td><?php  echo $rowObjs->address; ?></td>
            <td><?php  echo $rowObjs->city; ?></td>
            <td><?php  echo $rowObjs->po_box_number; ?></td>
            <td><?php  echo $rowObjs->email; ?></td>
            <td><?php  echo $rowObjs->created_at; ?></td>
            <td><?php  echo $rowObjs->uniqueid; ?></td>
            <td><?php  echo $rowObjs->id; ?></td>
           
        </tr> 
        
    
    <?php  $i++; } ?>
        <tr    >
            <td colspan="12"> &nbsp; </td>
            
           
        </tr>  
        
     <?php   } ?>
  </tbody>
</table>
