<?php
use_helper('Number');
$sf_user->setCulture('da_DK');



// $year=Date('Y');

if (isset($_REQUEST['month']) && $_REQUEST['month'] != "") {
    $month = $_REQUEST['month'];
} else {
    $month = Date('m');
}


if (isset($_REQUEST['year']) && $_REQUEST['year'] != "") {
    $year = $_REQUEST['year'];
} else {
    $year = Date('Y');
}

$csv_hdr="";
$csv_output="";
?>
<div id="sf_admin_container"> <form method="get" action="">   Filter 
        <select id="customer_date_of_birth_month" name="month" class="shrinked_select_box">

            <option value="01" <?php
if ($month == '01') {
    echo 'selected="selected"';
}
?>>January</option>
            <option value="02" <?php
                    if ($month == '02') {
                        echo 'selected="selected"';
                    }
?>>February</option>
            <option value="03" <?php
                    if ($month == '03') {
                        echo 'selected="selected"';
                    }
?>>March</option>
            <option value="04" <?php
                    if ($month == '04') {
                        echo 'selected="selected"';
                    }
?>>April</option>
            <option value="05" <?php
                    if ($month == '05') {
                        echo 'selected="selected"';
                    }
?>>May</option>
            <option value="06" <?php
            if ($month == '06') {
                echo 'selected="selected"';
            }
?>>June</option>
            <option value="07" <?php
            if ($month == '07') {
                echo 'selected="selected"';
            }
?>>July</option>
            <option value="08" <?php
            if ($month == '08') {
                echo 'selected="selected"';
            }
?>>August</option>
            <option value="09" <?php
            if ($month == '09') {
                echo 'selected="selected"';
            }
?>>September</option>
            <option value="10" <?php
                    if ($month == '10') {
                        echo 'selected="selected"';
                    }
?>>October</option>
            <option value="11" <?php
                    if ($month == '11') {
                        echo 'selected="selected"';
                    }
?>>November</option>
            <option value="12" <?php
                    if ($month == '12') {
                        echo 'selected="selected"';
                    }
?>>December</option>
        </select><select   name="year" class="shrinked_select_box">

            <option value="2012" <?php
            if ($year == '2012') {
                echo 'selected="selected"';
            }
?>>2012</option>
            <option value="2013" <?php
            if ($year == '2013') {
                echo 'selected="selected"';
            }
?>>2013</option>
            <option value="2014" <?php
            if ($year == '2014') {
                echo 'selected="selected"';
            }
?>>2014</option>
            <option value="2015" <?php
            if ($year == '2015') {
                echo 'selected="selected"';
            }
?>>2015</option>

        </select>
        <input type="submit"  name="submit" value="search">

    </form><br/>
</div>

<h2>Report on Month :  <?php echo $month . "-" . $year; ?> </h2>
<div id=""><h1><?php echo __('Country Stats') ?></h1></div>
<table width="75%" cellspacing="0" cellpadding="2" class="tblAlign">
    <thead>
        <tr class="headings">

 
            <td>Modules<?php $csv_hdr.= "Modules,";    ?></td>
            <td colspan="4" align="center"><b> <?php $csv_hdr.= "Total,,,,";    ?> Modules Total </b></td>
<?php
$date = "$year-$month-05";
$totalDays = date('t', strtotime($date));
for ($i = 1; $i <= $totalDays; $i++) {
    ?>
                <td colspan="4" align="center"> <?php if($totalDays==$i){   $csv_hdr.= "Day ".$i.",,,\n"; }else{ $csv_hdr.= "Day ".$i.",,,,";} ?> Day <?php echo $i; ?></td>
            <?php } ?></tr> 
        <tr>
 
            <td> <?php $csv_output.=",";   ?></td>
            <td colspan="2" align="center">Fixed <?php $csv_output.="Fixed".",, ";   ?></td>
            <td colspan="2" align="center">Mobile<?php $csv_output.="Mobile".",, ";   ?></td>
<?php for ($i = 1; $i <= $totalDays; $i++) {
    ?>
                <td colspan="2" align="center">Fixed <?php $csv_output.="Fixed".", ,";   ?></td>
                <td colspan="2" align="center">Mobile <?php   if($totalDays==$i){  $csv_output.="Mobile".",\n";  }else{  $csv_output.="Mobile".",, "; }  ?></td>
<?php } ?>

        </tr>
        <tr>
<!--                    <td>ID</td>-->
            <td>Name <?php $csv_output.="Name".", ";   ?></td>
            <td align="center">Traf <?php $csv_output.="Traf".", ";   ?></td>
            <td  align="center">Reve <?php $csv_output.="Reve".", ";   ?></td>
            <td  align="center">Traf <?php $csv_output.="Traf".", ";   ?></td>
            <td  align="center">Reve <?php $csv_output.="Reve".", ";   ?></td>  
<?php for ($i = 1; $i <= $totalDays; $i++) {
    ?>
                <td align="center">Traf <?php $csv_output.="Traf".", ";   ?></td>
                <td  align="center">Reve <?php $csv_output.="Reve".", ";   ?></td>
                <td  align="center">Traf <?php $csv_output.="Traf".", ";   ?></td>
                <td  align="center">Reve <?php  if($totalDays==$i){   $csv_output.="Reve"."\n"; }else{   $csv_output.="Reve".", "; }  ?></td>
        <?php } ?>

        </tr>
    </thead>

    <tbody>
        <?php
        $total = array();


        $conn = Propel::getConnection();
        $query = 'SELECT country.id, country.name';

        for ($i = 1; $i <= $totalDays; $i++) {
            $query .=',(SELECT CONCAT(COALESCE(ROUND(sum(charged_quantity)/60,2),0),"-",COALESCE(sum(charged_amount),0)) FROM employee_customer_callhistory WHERE country_id = country.id AND DATE( connect_time ) = "' . $year . '-' . $month . '-' . $i . '" AND (description like "%Cellular%" or description like "%mobile%")) AS day' . $i . '_mobile
              ,(SELECT CONCAT(COALESCE(ROUND(sum(charged_quantity)/60,2),0),"-",COALESCE(sum(charged_amount),0)) FROM employee_customer_callhistory WHERE country_id = country.id AND DATE( connect_time ) = "' . $year . '-' . $month . '-' . $i . '" AND (description not like "%Cellular%" AND description not like "%mobile%") ) AS day' . $i . '_fixed ';
        }

        $query .= 'FROM country right join employee_customer_callhistory on  employee_customer_callhistory.country_id=country.id group by country.id order by country.name';


//                  echo $query;
//                    die;

        $statement = $conn->prepare($query);
        $statement->execute();
        $k = 0;
        while ($rowObj = $statement->fetch()) {

            if ($k % 2 == 0) {
                $class = 'class="even"';
            } else {
                $class = 'class="odd"';
            }
            ?>

            <tr <?php echo $class; ?>>

    <!--                            <td><?php //echo $rowObj['id'] ?></td>-->
                <td><b><?php echo $rowObj['name'];   $csv_output.=$rowObj['name'].", ";  ?></b></td>

    <?php
    /////////////////////////////////////////////////
    $mobile_t = 0;
    $mobile_r = 0;
    $fixed_t = 0;
    $fixed_r = 0;
    for ($i = 1; $i <= $totalDays; $i++) {
        ?>


                    <?php $mobile = explode('-', $rowObj['day' . $i . '_mobile']); ?>
                    <?php $fixed = explode('-', $rowObj['day' . $i . '_fixed']); ?>

                    <?php
                    $mobile_r+=$mobile[1];

                    $mobile_t+=$mobile[0];

                    $fixed_r+=$fixed[1];

                    $fixed_t+=$fixed[0];
                }

                ///////////////////////
                ?>
                <td  align="center"><b><?php echo $ft=number_format($fixed_t, 2); $csv_output.=$ft.", ";   ?></b></td>
                <td  align="center"><b><?php echo  $fr=number_format($fixed_r, 2); $csv_output.=$fr.", ";  ?></b></td>
                <td align="center"><b><?php echo $mt=number_format($mobile_t, 2); $csv_output.=$mt.", ";  ?></b></td>
                <td  align="center"><b><?php echo $mr=number_format($mobile_r, 2); $csv_output.=$mr.", ";  ?></b></td>





                <?php for ($i = 1; $i <= $totalDays; $i++) {
                    ?>


                        <?php $mobile = explode('-', $rowObj['day' . $i . '_mobile']); ?>
        <?php $fixed = explode('-', $rowObj['day' . $i . '_fixed']); ?>


                    <td><?php echo number_format($fixed[0], 2);
        $total[$i]['fixed_0']+=$fixed[0];
        $csv_output.=number_format($fixed[0], 2).", "; 
        ?>
                    </td>
                    <td><?php echo number_format($fixed[1], 2);
        $total[$i]['fixed_1']+=$fixed[1];
        $csv_output.=number_format($fixed[1], 2).", "; 
        ?>
                    </td>
                    <td><?php echo number_format($mobile[0], 2);
                $total[$i]['mobile_0']+=$mobile[0];
                    $csv_output.=number_format($mobile[0], 2).", "; 
        ?>
                    </td>  
                    <td>
        <?php echo number_format($mobile[1], 2);
        $total[$i]['mobile_1']+=$mobile[1];
       if($totalDays==$i){       $csv_output.=number_format($mobile[0], 2)."\n"; }else{    $csv_output.=number_format($mobile[0], 2).", ";  }
        
        ?>
                    </td> 


            <?php } ?>




            </tr>
                <?php
                $k++;
            }
            ?>

        <tr>
            <td><b>Total<?php $csv_output.="Total".", ";   ?></b></td>

            <?php
            $T_mobile_t = 0;
            $T_mobile_r = 0;
            $T_fixed_t = 0;
            $T_fixed_r = 0;
            ?>
<?php
for ($i = 1; $i <= $totalDays; $i++) {

    $T_mobile_r+=$total[$i]['mobile_1'];
    $T_mobile_t+=$total[$i]['mobile_0'];
    $T_fixed_r+=$total[$i]['fixed_1'];
    $T_fixed_t+=$total[$i]['fixed_0'];
}
?>





            <td  align="center"><b><?php echo number_format($T_fixed_t, 2);    $csv_output.=number_format($T_fixed_t, 2).", "; ?></b></td>
            <td  align="center"><b><?php echo number_format($T_fixed_r, 2);   $csv_output.=number_format($T_fixed_r, 2).", "; ?></b></td>      
            <td align="center"><b><?php echo number_format($T_mobile_t, 2);   $csv_output.=number_format($T_mobile_t, 2).", ";  ?></b></td>
            <td  align="center"><b><?php echo number_format($T_mobile_r, 2);   $csv_output.=number_format($T_mobile_r, 2).", "; ?></b></td>


<?php for ($i = 1; $i <= $totalDays; $i++) {
    ?>
            <td><b><?php echo number_format($total[$i]['fixed_0'], 2);  $csv_output.=number_format($total[$i]['fixed_0'], 2).", "; ?></b></td>    
            <td><b><?php echo number_format($total[$i]['fixed_1'], 2);  $csv_output.=number_format($total[$i]['fixed_1'], 2).", "; ?></b></td>
                <td><b><?php echo number_format($total[$i]['mobile_0'], 2);  $csv_output.=number_format($total[$i]['mobile_0'], 2).", "; ?></b></td>       
                <td><b><?php echo number_format($total[$i]['mobile_1'], 2);   if($totalDays==$i){        $csv_output.=number_format($total[$i]['mobile_1'], 2)."\n"; }else{    $csv_output.=number_format($total[$i]['mobile_1'], 2).", ";  }?></b></td>
                


<? } ?>



        </tr>

<!--       <tr>
<td colspan="115">
<form name="export" action="exportExcel" method="post">
<input type="submit" value="Export Data">
<input type="hidden" value="<?php  //echo $csv_hdr;  ?>" name="csv_hdr">
<input type="hidden" value="Country-Stat-Report" name="file_name">
<input type="hidden" value="<?php // echo $csv_output;  ?>" name="csv_output">
</form>
</td>

</tr> -->

    </tbody>
</table>
