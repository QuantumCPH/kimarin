<?php use_helper('I18N') ?><div id="sf_admin_container">
	<div id="sf_admin_content">
	<!-- employee/list?filters[company_id]=1 -->
	<a href="<?php echo url_for('employee/index').'?company_id='.$company->getId()."&filter=filter" ?>" class="external_link" target="_self"><?php echo  __('Employees') ?> (<?php echo $count ?>)</a>
	<a href="<?php echo url_for('company/usage').'?company_id='.$company->getId(); ?>" class="external_link" target="_self"><?php echo  __('Usage') ?></a>
        <a href="<?php echo url_for('company/paymenthistory').'?company_id='.$company->getId().'&filter=filter' ?>" class="external_link" target="_self"><?php echo  __('Receipts') ?></a>
        <a href="<?php echo url_for('company/invoices') . '?company_id=' . $company->getId()?>" class="external_link" target="_self"><?php echo __('Invoices') ?></a>
	<!--
	<a onclick="companyShow();" style="cursor:pointer;">Company Info</a>
	&nbsp; | &nbsp;
	<a onclick="salesShow();" style="cursor:pointer;">Sales Activity</a>
	&nbsp; | &nbsp;
	<a onclick="supportShow();" style="cursor:pointer;">Support Activity</a>
	 -->
		<div id="company-info">
		    <h1><?php echo  __('company details') ?></h1>
			<fieldset>
				<div class="form-row">
				  <label class="required"><?php echo  __('Company Name:') ?></label>
				  <div class="content">
				  	<?php echo $company->getName() ?> &nbsp; <?php echo link_to(__('edit info'), 'company/edit?id='.$company->getId()) ?>
				  </div>
				</div>

	<div class="form-row">
				  <label class="required"><?php echo  __('Airtime:') ?> <br /><small>(excluding vat)</small></label>
				  <div class="content"><?php
                                 echo number_format($balance,2);echo sfConfig::get('app_currency_code');
                           ?>
				   
				  </div>
				</div>
                                <div class="form-row">
				  <label class="required"><?php echo  __('Credit Limit:') ?><br /><small>(excluding vat)</small></label>
				  <div class="content">
				  	<?php echo number_format($company->getCreditLimit(),2);echo sfConfig::get('app_currency_code'); ?>
                                      
				  </div>
				</div>
                                
				<div class="form-row">
				  <label class="required"><?php echo  __('Vat Number:') ?></label>
				  <div class="content">
				  	<?php echo $company->getVatNo()?>
				  </div>
				</div>
                                <div class="form-row">
				  <label class="required"><?php echo  __('Password:') ?></label>
				  <div class="content">
				  	<?php echo $company->getPassword()?>
				  </div>
				</div>
				<div class="form-row">
				  <label class="required"><?php echo  __('Address:') ?></label>
				  <div class="content">
				  	<?php echo $company->getAddress() ?>
				  </div>
				</div>


				<div class="form-row">
				  <label class="required"><?php echo  __('Country:') ?></label>
				  <div class="content">
				  	<?php echo $company->getCountry()?$company->getCountry()->getName():'N/A' ?>
				  </div>
				</div>

				<div class="form-row">
				  <label class="required"><?php echo  __('City:') ?></label>
				  <div class="content">
				  	<?php echo $company->getCity()?$company->getCity()->getName():'N/A' ?>
				  </div>
				</div>


				<div class="form-row">
				  <label class="required"><?php echo  __('Contact Name:') ?></label>
				  <div class="content">
				  <?php echo $company->getContactName()?>
				  </div>
				</div>

                                <div class="form-row">
				  <label class="required"><?php echo  __('Contact e-mail:') ?></label>
				  <div class="content">
				  <?php echo $company->getEmail()?>
				  </div>
				</div>

				<div class="form-row">
				  <label class="required"><?php echo  __('Head Phone No:') ?></label>
				  <div class="content">
				  	<?php echo $company->getHeadPhoneNumber() ?>
				  </div>
				</div>


				<div class="form-row">
				  <label class="required"><?php echo  __('Fax Number:') ?></label>
				  <div class="content">
				  	<?php echo $company->getFaxNumber()?$company->getFaxNumber():'N/A' ?>
				  </div>
				</div>
				
				<div class="form-row">
				  <label class="required"><?php echo  __('Website:') ?></label>
				  <div class="content">
				  	<?php echo $company->getWebsite()?$company->getWebsite():'N/A' ?>
				  </div>
				</div>

					

				
				<div class="form-row">
				  <label class="required"><?php echo  __('Status:') ?></label>
				  <div class="content">
				  	<?php echo ''.$company->getStatus()?$company->getStatus():'N/A' ?>
				  </div>
				</div>

<!--				<div class="form-row">
				  <label class="required"><?php echo  __('Registered at:') ?></label>
				  <div class="content">
				  	<?php echo $company->getRegistrationDate()?$company->getRegistrationDate():'N/A' ?>
				  </div>
				</div>-->

			 		
				
<!--
				
				<div class="form-row">
				  <label class="required">Package:</label>
				  <div class="content">
				  	<?php //echo $company->getPackage()?$company->getPackage():'N/A' ?>
				  </div>
				</div>
				
				<div class="form-row">
				  <label class="required">Usage Discount %:</label>
				  <div class="content">
				  	<?php //echo $company->getUsageDiscountPc(). '%' ?>
				  </div>
				</div>		-->

                            
<!--				<div class="form-row">
				  <label class="required"><?php echo  __('Registration Doc:') ?></label>
				  <div class="content">
					<?php if($company->getFilePath()): ?>
						<a href="<?php echo public_path('/uploads/'.$company->getFilePath()) ?>" target="_blank"><?php echo  __('Download attachement') ?></a>
					<?php else: ?>
				<?php		echo __('None');   ?>
					<?php endif; ?>
				  </div>
				</div>-->
                                  <div class="form-row">
				  <label class="required">Comments:</label>
				  <div class="content">
				  	<?php echo $company->getComments(); ?>
				  </div>
				</div>	
			</fieldset>
		</div>
	
	</div>
</div>