<?php use_helper('I18N') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo sfConfig::get('app_language_symbol') ?>" lang="<?php echo sfConfig::get('app_language_symbol') ?>">
    <head>

        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1" /> 
            <link rel="stylesheet" href="<?php echo sfConfig::get("app_web_url")?>mobile/css/jquery.mobile-1.2.0.css" />
            <link rel="stylesheet" href="<?php echo sfConfig::get("app_web_url")?>mobile/css/style.css" />
            <script src="<?php echo sfConfig::get("app_web_url")?>mobile/js/jquery-1.8.2.min.js"></script>
            <script type="text/javascript">
                $(document).bind("mobileinit", function () {
                    $.mobile.ajaxEnabled = false;
                });
            </script>

            <script src="<?php echo sfConfig::get("app_web_url")?>mobile/js/jquery.mobile-1.2.0.min.js"></script>

    </head>


    <body>

        <!--
            <div style="vertical-align: top;float: right;">
        
        <?php echo link_to(image_tag('/images/lang_spa1.png'), 'customer/changeCulture?new=es', array('id' => 'lang_spa', 'title' => 'es')); ?>
        <?php echo link_to(image_tag('/images/lang_eng1.png'), 'customer/changeCulture?new=en', array('title' => 'en')); ?>
        <?php echo link_to(image_tag('/images/lang_de1.png'), 'customer/changeCulture?new=de', array('title' => 'de')); ?>    
        <?php echo link_to(image_tag('/images/lang_cat1.png'), 'customer/changeCulture?new=ca', array('id' => 'lang_spa', 'title' => 'ca')); ?>
            
        
                     </div> -->
        <div id="wrap"><?php
        //echo $sf_user->getCulture();
// set alert if customer is not yet registered with fonet
//$alert_fonet_customer = CustomerPeer::
        ?>

            <!-- end header --> <?php echo $sf_content; ?></div>
        <!-- end wrap -->


<script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validate1.js"></script>
    </body>
</html>
