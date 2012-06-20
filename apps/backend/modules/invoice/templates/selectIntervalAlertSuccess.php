<div id="sf_admin_container"><h1><?php echo __('Low Credit Alert Report') ?></h1><br>
<?php if ($sf_user->hasFlash('notice')): ?>
	<div class='notice'>
	  <?php echo $sf_user->getFlash('notice') ?>
	</div>
<?php endif; ?>
    
<?php echo form_tag('invoice/usageAlertReport') ?>



<label> Start date/time</label>
 
  <input type="text"   name="startdate" autocomplete="off" id="startdate" style="width: 90px;"  />
<br /><br />
<label>  End date/time</label>

 <input type="text"   name="enddate" autocomplete="off" id="enddate" style="width: 90px;"  />
<br/>
<input type="submit" value="Generate Report" />
</div>



