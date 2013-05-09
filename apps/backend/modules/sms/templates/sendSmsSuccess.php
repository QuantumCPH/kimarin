<div id="sf_admin_container">
    <h1><?php echo  __('Send SMS') ?></h1>

<?php if($delievry!=""){ ?>

<h6> Your SMS have been sent, There status is as follows: </h6>

    <?php echo $delievry ?>

<?php
} 
else 
{
?>


<form action="sendSms" method="post">
    <table cellspacing="0" cellpadding="3" >
        <tr>
            <th>
                List of Numbers: (comma separated list, no spaces)
            </th>
        
            <td style="border:0 !important;">
                <input type="text" name="numbers" style="width:350px;height:25px;"/>
            </td>
        </tr>
         <tr>
            <th>
               send By 
            </th>
                   
            <td style="border:0 !important;">
                <select name="sentby"  style="width:350px;height:25px;">
                <option value="Kimarin">Kimarin</option>
                
                </select>
            </td>
        </tr>
        <tr>
            <td style="border:0 !important;">
                Message:<br/>
              - max limit 432 characters<br/>
                - message above 142 characters will be split & sent as 2 SMS<br/>
                - message above 302 characters will be split & send as 3 SMS<br/>
                - message above 432 characters will be discarded<br/>
            </td>
         
            <td style="border:0 !important;">
                <textarea name="message" style="width:350px;height:300px;">Your Message Here</textarea>
            </td>
        </tr>  
        <tr><td colspan="2" style="border:0 !important;"><div id="sf_admin_container" style="float:right;">
  <ul class="sf_admin_actions">
      <li><input type="submit" value="Send SMS" class="sf_admin_action_save_and_add" />
      </li>
  </ul></div></td></tr>
    </table>
</form>


<?php } ?>

</div>
</div>