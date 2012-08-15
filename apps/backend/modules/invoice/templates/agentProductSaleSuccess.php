<?php    
	use_helper('Number');
	$sf_user->setCulture('da_DK');
        
        
        
        // $year=Date('Y');
         
         if(isset($_REQUEST['month']) && $_REQUEST['month']!=""){
 $month=$_REQUEST['month'];
 
         }else{
           $month=Date('m');
         }
         
         
           if(isset($_REQUEST['year']) && $_REQUEST['year']!=""){
  $year=$_REQUEST['year'];
 
         }else{
           $year=Date('Y');
         }
?>
<div id="sf_admin_container"> <form method="get" action="">   Filter 
        <select id="customer_date_of_birth_month" name="month" class="shrinked_select_box">
 
<option value="01" <?php if($month=='01'){ echo 'selected="selected"';  }   ?>>January</option>
<option value="02" <?php if($month=='02'){ echo 'selected="selected"';  }   ?>>Febuary</option>
<option value="03" <?php if($month=='03'){ echo 'selected="selected"';  }   ?>>March</option>
<option value="04" <?php if($month=='04'){ echo 'selected="selected"';  }   ?>>Aprail</option>
<option value="05" <?php if($month=='05'){ echo 'selected="selected"';  }   ?>>May</option>
<option value="06" <?php if($month=='06'){ echo 'selected="selected"';  }   ?>>June</option>
<option value="07" <?php if($month=='07'){ echo 'selected="selected"';  }   ?>>July</option>
<option value="08" <?php if($month=='08'){ echo 'selected="selected"';  }   ?>>Auguest</option>
<option value="09" <?php if($month=='09'){ echo 'selected="selected"';  }   ?>>September</option>
<option value="10" <?php if($month=='10'){ echo 'selected="selected"';  }   ?>>October</option>
<option value="11" <?php if($month=='11'){ echo 'selected="selected"';  }   ?>>November</option>
<option value="12" <?php if($month=='12'){ echo 'selected="selected"';  }   ?>>December</option>
</select><select   name="year" class="shrinked_select_box">
 
<option value="2012" <?php if($year=='2012'){ echo 'selected="selected"';  }   ?>>2012</option>
<option value="2013" <?php if($year=='2013'){ echo 'selected="selected"';  }   ?>>2013</option>
<option value="2014" <?php if($year=='2014'){ echo 'selected="selected"';  }   ?>>2014</option>
<option value="2015" <?php if($year=='2015'){ echo 'selected="selected"';  }   ?>>2015</option>
 
</select>
        <input type="submit"  name="submit" value="search">
    
    </form><br/><h3>Report on Month :  <?php  echo $month."-".$year; ?> </h3></div>
<div id="sf_admin_container"><h1><?php echo  __('Kimarin Total Sale Stat') ?></h1></div>
<table width="75%" cellspacing="0" cellpadding="2" class="tblAlign">
    <thead>
        <tr class="headings">
        
<!--    <th>ID</th>-->
    <th>Description</th>
    <th>Day 1</th>
    <th>Day 2</th>
    <th>Day 3</th>
    <th>Day 4</th>
    <th>Day 5</th>
    <th>Day 6</th>
    <th>Day 7</th>
    <th>Day 8</th>
    <th>Day 9</th>
    <th>Day 10</th>
    <th>Day 11</th>
    <th>Day 12</th>
    <th>Day 13</th>
    <th>Day 14</th>
    <th>Day 15</th>
    <th>Day 16</th>
    <th>Day 17</th>
    <th>Day 18</th>
    <th>Day 19</th>
    <th>Day 20</th>
    <th>Day 21</th>
    <th>Day 22</th>
    <th>Day 23</th>
    <th>Day 24</th>
    <th>Day 25</th>
    <th>Day 26</th>
    <th>Day 27</th>
    <th>Day 28</th>
    <th>Day 29</th>
    <th>Day 30</th>
     <th>Day 31</th>
  <th>Total</th>
        </tr>
    </thead>
   
    <tbody>
   <?php
$day1A=0;
$day2A=0;
$day3A=0;
$day4A=0;
$day5A=0;
$day6A=0;
$day7A=0;
$day8A=0;
$day9A=0;
$day10A=0;
$day11A=0;
$day12A=0;
$day13A=0;
$day14A=0;
$day15A=0;
$day16A=0;
$day17A=0;
$day18A=0;
$day19A=0;
$day20A=0;
$day21A=0;
$day22A=0;
$day23A=0;
$day24A=0;
$day25A=0;
$day26A=0;
$day27A=0;
$day28A=0;
$day29A=0;
$day30A=0;
$day31A=0;
$daytotal1A=0;     

?>
        
 <?php
            $conn = Propel::getConnection();
   $queryC = 'select ac.id,ac.name as agentname from customer as c left join agent_company  as ac on c.referrer_id=ac.id where c.referrer_id>0 group by ac.id';
    $statementC = $conn->prepare($queryC);
    $statementC->execute();
  while ($rowObjCus = $statementC->fetch(PDO::FETCH_OBJ))
    {  ?> 
   <tr>
       <td colspan="33"  ><b><?php echo $rowObjCus->agentname; ?></b></td>
             
  </tr>      
        
<?php
$day1=0;
$day2=0;
$day3=0;
$day4=0;
$day5=0;
$day6=0;
$day7=0;
$day8=0;
$day9=0;
$day10=0;
$day11=0;
$day12=0;
$day13=0;
$day14=0;
$day15=0;
$day16=0;
$day17=0;
$day18=0;
$day19=0;
$day20=0;
$day21=0;
$day22=0;
$day23=0;
$day24=0;
$day25=0;
$day26=0;
$day27=0;
$day28=0;
$day29=0;
$day30=0;
$day31=0;
$daytotal1=0;
  $i=0;

    
      $conn = Propel::getConnection();
   $query = 'SELECT product.id, product.name
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'" AND DATE( c.created_at ) ="'.$year.'-'.$month.'-01  ") AS day1_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-02  ") AS day2_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-03  ") AS day3_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-04  ") AS day4_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-05  ") AS day5_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-06  ") AS day6_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-07  ") AS day7_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-08  ") AS day8_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-09  ") AS day9_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-10  ") AS day10_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-11  ") AS day11_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-12 ") AS day12_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-13  ") AS day13_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-14  ") AS day14_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-15  ") AS day15_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-16 ") AS day16_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-17  ") AS day17_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-18  ") AS day18_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-19  ") AS day19_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-20  ") AS day20_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-21  ") AS day21_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-22  ") AS day22_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-23  ") AS day23_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-24  ") AS day24_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-25  ") AS day25_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-26  ") AS day26_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-27  ") AS day27_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-28  ") AS day28_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-29  ") AS day29_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-30  ") AS day30_calls
,(SELECT count( c.id ) FROM customer as c left join customer_product as cp  on c.id=cp.customer_id   WHERE product.id = cp.product_id AND c.customer_status_id=3 AND   c.referrer_id="'.$rowObjCus->id.'"  AND DATE( c.created_at ) ="'.$year.'-'.$month.'-31  ") AS day31_calls
FROM product where product.is_in_store=1 or product.include_in_zerocall=1';
    $statement = $conn->prepare($query);
    $statement->execute();
  while ($rowObj = $statement->fetch(PDO::FETCH_OBJ))
    {
   
    if($i%2==0){
        $class= 'class="even"';
    }else{
        $class= 'class="odd"';
    }
?>

        <tr <?php echo $class;?>>
         
<!--   <td><?php  //echo $rowObj->is_first_order;   ?></td>-->
    <td><?php  echo $rowObj->name;    ?></td>
   <td><?php   $day1+=$rowObj->day1_calls;   echo $rowObj->day1_calls;?></td>
   <td><?php  $day2+=$rowObj->day2_calls; echo $rowObj->day2_calls;   ?></td>
  <td><?php  $day3+=$rowObj->day3_calls; echo $rowObj->day3_calls;   ?></td>
   <td><?php  $day4+=$rowObj->day4_calls; echo $rowObj->day4_calls;   ?></td>
    <td><?php  $day5+=$rowObj->day5_calls; echo $rowObj->day5_calls;   ?></td>
     <td><?php  $day6+=$rowObj->day6_calls; echo $rowObj->day6_calls;   ?></td>
    <td><?php  $day7+=$rowObj->day7_calls; echo $rowObj->day7_calls;   ?></td>
 <td><?php  $day8+=$rowObj->day8_calls; echo $rowObj->day8_calls;   ?></td>
 <td><?php  $day9+=$rowObj->day9_calls; echo $rowObj->day9_calls;   ?></td>
 <td><?php  $day10+=$rowObj->day10_calls; echo $rowObj->day10_calls;   ?></td>
 <td><?php  $day11+=$rowObj->day11_calls; echo $rowObj->day11_calls;   ?></td>
 <td><?php  $day12+=$rowObj->day12_calls; echo $rowObj->day12_calls;   ?></td>
 <td><?php  $day13+=$rowObj->day13_calls; echo $rowObj->day13_calls;   ?></td>
 <td><?php  $day14+=$rowObj->day14_calls; echo $rowObj->day14_calls;   ?></td>
 <td><?php  $day15+=$rowObj->day15_calls; echo $rowObj->day15_calls;   ?></td>
 <td><?php  $day16+=$rowObj->day16_calls; echo $rowObj->day16_calls;   ?></td>
   <td><?php  $day17+=$rowObj->day17_calls; echo $rowObj->day17_calls;   ?></td>
  <td><?php  $day18+=$rowObj->day18_calls; echo $rowObj->day18_calls;   ?></td>
<td><?php  $day19+=$rowObj->day19_calls; echo $rowObj->day19_calls;   ?></td>
<td><?php  $day20+=$rowObj->day20_calls; echo $rowObj->day20_calls;   ?></td>
 <td><?php  $day21+=$rowObj->day21_calls; echo $rowObj->day21_calls;   ?></td>
<td><?php  $day22+=$rowObj->day22_calls; echo $rowObj->day22_calls;   ?></td>
<td><?php  $day23+=$rowObj->day23_calls; echo $rowObj->day23_calls;   ?></td>
<td><?php  $day24+=$rowObj->day24_calls; echo $rowObj->day24_calls;   ?></td>
<td><?php  $day25+=$rowObj->day25_calls; echo $rowObj->day25_calls;   ?></td>
<td><?php  $day26+=$rowObj->day26_calls; echo $rowObj->day26_calls;   ?></td>
<td><?php  $day27+=$rowObj->day27_calls; echo $rowObj->day27_calls;   ?></td>
<td><?php  $day28+=$rowObj->day28_calls; echo $rowObj->day28_calls;   ?></td>
<td><?php  $day29+=$rowObj->day29_calls; echo $rowObj->day29_calls;   ?></td>
<td><?php  $day30+=$rowObj->day30_calls; echo $rowObj->day30_calls;   ?></td>
 <td><?php  $day31+=$rowObj->day31_calls;  echo $rowObj->day31_calls;   ?></td>
    <td>  <?php echo $total=$rowObj->day1_calls+$rowObj->day2_calls+$rowObj->day3_calls+$rowObj->day4_calls+$rowObj->day5_calls+$rowObj->day6_calls+$rowObj->day7_calls+$rowObj->day8_calls+$rowObj->day9_calls+$rowObj->day10_calls+$rowObj->day11_calls+$rowObj->day12_calls+$rowObj->day13_calls+$rowObj->day14_calls+$rowObj->day15_calls+$rowObj->day16_calls+$rowObj->day17_calls+$rowObj->day18_calls+$rowObj->day19_calls+$rowObj->day19_calls+$rowObj->day20_calls+$rowObj->day21_calls+$rowObj->day22_calls+$rowObj->day23_calls+$rowObj->day24_calls+$rowObj->day25_calls+$rowObj->day26_calls+$rowObj->day27_calls+$rowObj->day28_calls+$rowObj->day29_calls+$rowObj->day30_calls+$rowObj->day31_calls;
        $daytotal1+=$total;    ?>    </td>   
            
            
        </tr>
<?php

$i++;

}  ?>
        
          <tr>
              <td><b> Agent Total</b></td>
    <td><?php echo $day1;  $day1A+=$day1;    ?></td>
   <td><?php echo $day2; $day2A+=$day2;    ?></td>
     <td><?php echo $day3;  $day3A+=$day3;    ?></td>
      <td><?php echo $day4; $day4A+=$day4;    ?></td>
   <td><?php echo  $day5; $day5A+=$day5;    ?></td>
     <td><?php echo $day6;  $day6A+=$day6;    ?></td>
    <td><?php echo $day7; $day7A+=$day7;    ?></td>
     <td><?php echo $day8; $day8A+=$day8;    ?></td>
     <td><?php echo $day9;  $day9A+=$day9;    ?></td>
     <td><?php echo $day10; $day10A+=$day10;    ?></td>
      <td><?php echo $day11; $day11A+=$day11;    ?></td>
      <td><?php echo $day12;  $day12A+=$day12;    ?></td>
     <td><?php echo $day13; $day13A+=$day13;    ?></td>
     <td><?php echo $day14; $day14A+=$day14;    ?></td>
     <td><?php echo $day15;  $day15A+=$day15;    ?></td>
    <td><?php echo $day16;   $day16A+=$day16;    ?></td>
    <td><?php echo $day17;  $day17A+=$day17;    ?></td>
    <td><?php echo $day18; $day18A+=$day18;    ?></td>
     <td><?php echo $day19; $day19A+=$day19;    ?></td>
     <td><?php echo $day20; $day20A+=$day20;    ?></td>
     <td><?php echo $day21; $day21A+=$day21;    ?></td>
   <td><?php echo $day22; $day22A+=$day22;    ?></td>
     <td><?php echo $day23; $day23A+=$day23;    ?></td>
     <td><?php echo $day24; $day24A+=$day24;    ?></td>
        <td><?php echo $day25; $day25A+=$day25;    ?></td>
        <td><?php echo $day26;  $day26A+=$day26;    ?></td>
       <td><?php echo $day27; $day27A+=$day27;    ?></td>
      <td><?php echo $day28; $day28A+=$day28;    ?></td>
       <td><?php echo $day29; $day29A+=$day29;    ?></td>
         <td><?php echo $day30; $day30A+=$day30;    ?></td>
       <td><?php echo $day31;  $day31A+=$day31;    ?></td>
         <td><?php  echo $daytotal1; $daytotal1A+=$daytotal1;    ?></td>
  </tr>
  <?php  } ?>
    <tr>
              <td><b>ALL Agent Total</b></td>
    <td><?php echo  $day1A;    ?></td>
   <td><?php echo  $day2A;    ?></td>
     <td><?php echo  $day3A;    ?></td>
      <td><?php echo  $day4A;    ?></td>
   <td><?php echo  $day5A;    ?></td>
     <td><?php echo  $day6A;    ?></td>
    <td><?php echo  $day7A;    ?></td>
     <td><?php echo  $day8A;    ?></td>
     <td><?php echo  $day9A;    ?></td>
     <td><?php echo  $day10A;    ?></td>
      <td><?php echo  $day11A;    ?></td>
      <td><?php echo  $day12A;    ?></td>
     <td><?php echo  $day13A;    ?></td>
     <td><?php echo  $day14A;    ?></td>
     <td><?php echo  $day15A;    ?></td>
    <td><?php echo  $day16A;    ?></td>
    <td><?php echo  $day17A;    ?></td>
    <td><?php echo  $day18A;    ?></td>
     <td><?php echo  $day19A;    ?></td>
     <td><?php echo  $day20A;    ?></td>
     <td><?php echo  $day21A;    ?></td>
   <td><?php echo  $day22A;    ?></td>
     <td><?php echo  $day23A;    ?></td>
     <td><?php echo  $day24A;    ?></td>
        <td><?php echo  $day25A;    ?></td>
        <td><?php echo  $day26A;    ?></td>
       <td><?php echo  $day27A;    ?></td>
      <td><?php echo  $day28A;    ?></td>
       <td><?php echo  $day29A;    ?></td>
         <td><?php echo  $day30A;    ?></td>
       <td><?php echo  $day31A;    ?></td>
         <td><?php  echo  $daytotal1A    ?></td>
  </tr>
  </tbody>
</table>
 