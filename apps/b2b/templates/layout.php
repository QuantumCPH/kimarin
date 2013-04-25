<?php use_helper('I18N') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="http://zerocall.com/images/zerocall.ico" type="image/x-icon" />
<!--        <link rel="shortcut icon" href="<?php echo sfConfig::get('app_web_url');?>images/favicon.ico" type="image/x-icon" />-->
         <?php use_javascript('jquery.validate1.js', '', array('absolute' => true)) ?>
    </head>

    <body>
        <div id="basic">
            <div id="header">
                <div class="logo">
                    <?php echo image_tag('/images/logo.jpg'); // link_to(image_tag('/images/logo.gif'), '@homepage');  ?>
                </div>
            </div>
            <div id="slogan">
                
                <?php if ($sf_user->getAttribute('companyname', '', 'companysession')) {
                ?>
                <h1><?php echo __('B2B Portal'); ?></h1>
                        <div id="loggedInUser">
                    <?php echo __('Logged in as:') ?><b>&nbsp;<?php echo $sf_user->getAttribute('companyname', '', 'companysession') ?></b><br />
                    <?php
                       // if ($company) {
                           // if ($ompany->getIsPrepaid()) {
                    ?>
                    <?php //echo __('Your Balance is:') ?> <b><?php //echo $company->getBalance(); ?></b>
                    <?php //} ?>
                    <?php // } ?>
                    </div>
                <?php } ?>

                    <div style="vertical-align: top;float: right;margin-right: 10px;display: none;">

                    <?php echo link_to(image_tag('/images/german.png'), 'affiliate/changeCulture?new=de'); ?>

                    <?php echo link_to(image_tag('/images/english.png'), 'affiliate/changeCulture?new=en'); ?>

                   </div>
                <div class="clr"></div>
            </div>
            <div class="clr"></div>
            
                <!--                <h1>menu</h1>-->
                <?php
                    if ($sf_user->isAuthenticated()) {
                        $modulName = $sf_context->getModuleName();
                        $actionName = $sf_context->getActionName();
//                        echo "M ".$modulName;
//                        echo "<br />";
//                        echo "A ".$actionName;
                ?><div class="menuarrange" id="sddm">           
   <ul class="menu">
      <li class="dropdown">
                        <?php
                        if ($actionName == 'dashboard' && $modulName == "company") {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/dashboard" class="current"><?php echo  __('Dashboard');?></a>
                        <?php    
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/dashboard"><?php echo  __('Dashboard');?></a>
                        <?php    
                        }
                        ?>
                    </li>
                    <li class="dropdown">
                        <?php
                        if ($modulName == "company" && $actionName == 'paymentHistory') {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/paymentHistory" class="current"><?php echo  __('Refill Receipts');?></a>
                        <?php     
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/paymentHistory"><?php echo  __('Refill Receipts');?></a>
                        <?php  
                        }
                        ?>
                    </li>
                    <li class="dropdown">
                        <?php
                        if ($modulName == "company" && $actionName == 'refill') {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/refill" class="current"><?php echo  __('Refill');?></a>
                        <?php     
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/refill"><?php echo  __('Refill');?></a>
                        <?php  
                        }
                        ?>
                    </li>
                    <li class="dropdown"><?php
                        if ($modulName == "company" && $actionName == 'callHisotry') {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/callHisotry" class="current"><?php echo  __('Call History');?></a>
                        <?php 
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/callHisotry"><?php echo  __('Call History');?></a>
                        <?php 
                        }
                        ?>
                    </li>
                    <li class="dropdown"><?php
                        if ($modulName == "company" && $actionName == 'invoices') {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/invoices" class="current"><?php echo  __('Invoices');?></a>
                        <?php    
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/invoices"><?php echo  __('Invoices');?></a>
                        <?php     
                        }
                        ?>
                    </li>
                    <li class="dropdown"><?php
                        if ($modulName == "company" && $actionName == 'view') {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/view" class="current"><?php echo  __('Company Info');?></a>
                        <?php    
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/view"><?php echo  __('Company Info');?></a>
                        <?php     
                        }
                        ?>
                    </li>
<!--                    <li class="dropdown"><?php
                        if ($modulName == "rates" && $actionName == 'company') {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/rates" class="current"><?php echo  __('Rates');?></a>
                        <?php 
                        } else {
                        ?>
                            <a href="<?php echo sfConfig::get('app_b2b_url');?>company/rates" ><?php echo  __('Rates');?></a>
                        <?php 
                        }
                        ?>
                    </li>-->
                    <li class="dropdown last"><a href="<?php echo sfConfig::get('app_b2b_url');?>company/logout" ><?php echo  __('Logout');?></a></li>

                </ul>
                    <div class="clr"></div>
                </div> <br />
                <?php } ?>
                    
                <div id="content">
                <script type="text/javascript">
                 jQuery(function(){
                  if(jQuery("#startdate").length > 0){
                    jQuery("#startdate").datepicker({ dateFormat: 'yy-mm-dd' });  
                  }      
                  if(jQuery("#enddate").length > 0){
                    jQuery("#enddate").datepicker({ dateFormat: 'yy-mm-dd' });  
                  }
                  if(jQuery("#trigger_startdate").length > 0){
                    jQuery("#trigger_startdate").datepicker({ minDate: '-2m +0w',maxDate: '0m +0w', dateFormat: 'yy-mm-dd' });  
                  }
                  if(jQuery("#trigger_enddate").length > 0){
                    jQuery("#trigger_enddate").datepicker({ minDate: '-2m +0w',maxDate: '0m +0w', dateFormat: 'yy-mm-dd' });  
                  }
                 });
                </script>
                <?php echo $sf_content ?>
            </div>
            <div class="clear"></div>
        </div>

        <script type="text/javascript">
    //<![CDATA[
jQuery(window).load(function(){
jQuery(function()
{
    var $dropdowns = jQuery('li.dropdown'); // Specifying the element is faster for older browsers

    /**
     * Mouse events
     *
     * @description Mimic hoverIntent plugin by waiting for the mouse to 'settle' within the target before triggering
     */
    $dropdowns
        .on('mouseover', function() // Mouseenter (used with .hover()) does not trigger when user enters from outside document window
        {
            var $this = jQuery(this);

            if ($this.prop('hoverTimeout'))
            {
                $this.prop('hoverTimeout', clearTimeout($this.prop('hoverTimeout')));
            }

            $this.prop('hoverIntent', setTimeout(function()
            {
                $this.addClass('hover');
            }, 250));
        })
        .on('mouseleave', function()
        {
            var $this = jQuery(this);

            if ($this.prop('hoverIntent'))
            {
                $this.prop('hoverIntent', clearTimeout($this.prop('hoverIntent')));
            }

            $this.prop('hoverTimeout', setTimeout(function()
            {
                $this.removeClass('hover');
            }, 250));
        });

    /**
     * Touch events
     *
     * @description Support click to open if we're dealing with a touchscreen
     */
    if ('ontouchstart' in document.documentElement)
    {
        $dropdowns.each(function()
        {
            var $this = $(this);

            this.addEventListener('touchstart', function(e)
            {
                if (e.touches.length === 1)
                {
                    // Prevent touch events within dropdown bubbling down to document
                    e.stopPropagation();

                    // Toggle hover
                    if (!$this.hasClass('hover'))
                    {
                        // Prevent link on first touch
                        if (e.target === this || e.target.parentNode === this)
                        {
                            e.preventDefault();
                        }

                        // Hide other open dropdowns
                        $dropdowns.removeClass('hover');
                        $this.addClass('hover');

                        // Hide dropdown on touch outside
                        document.addEventListener('touchstart', closeDropdown = function(e)
                        {
                            e.stopPropagation();

                            $this.removeClass('hover');
                            document.removeEventListener('touchstart', closeDropdown);
                        });
                    }
                }
            }, false);
        });
    }

});
});//]]>
    </script>
    </body>
</html>

