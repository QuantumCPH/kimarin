<?php use_helper('I18N') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo sfConfig::get('app_language_symbol')?>" lang="<?php echo sfConfig::get('app_language_symbol')?>">
<head>

<?php include_http_metas() ?>
<?php include_metas()  ?>
<?php include_title() ?>
     <link rel="shortcut icon" href="<?php echo sfConfig::get('app_web_url')?>images/favicon.ico" type="image/x-icon" />
<?php use_javascript('../zerocall/js/jquery-1.4.2.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/jquery.jcarousel.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/cufon-yui.js', '', array('absolute'=>true)) ?>
     <?php use_javascript('../zerocall/js/calibri.cufonfonts.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/cufon-replace.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/carousel.js', '', array('absolute'=>true)) ?>
<?php use_javascript('jquery.formatCurrency-1.3.0.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('i18n/jquery.formatCurrency.all.js', '', array('absolute'=>true)) ?>
    <?php use_javascript('jquery-ui-1.8.16.custom.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('jquery.corner.js');?>    
</head>
<body>
    <div style="vertical-align: top;float: right;">

                        <?php echo link_to(image_tag('/images/german.png'), 'customer/changeCulture?new=de'); ?>
                        <?php echo link_to(image_tag('/images/norway.png'), 'customer/changeCulture?new=es'); ?>
                         <?php echo link_to(image_tag('/images/english.png'), 'customer/changeCulture?new=en'); ?>

             </div>
<div id="wrap"><?php //echo $sf_user->getCulture();
// set alert if customer is not yet registered with fonet

//$alert_fonet_customer = CustomerPeer::

?>

<!-- end header --> <?php echo $sf_content; ?></div>
<!-- end wrap -->

<script type="text/javascript"> 
    jq1 = jQuery.noConflict();
     jq1(document).ready(function()
     {
        jq1('.sidebar_button').corner('round 5px');
        jq1('.butonsigninsmall').corner('round 5px');
        jq1('.buton').corner('round 5px');
        jq1('.loginbuttun').corner('round 5px');
        
     }); 
</script>

<?php if($sf_user->getCulture()=='en'){    ?>
   <script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validate1.js"></script>

   <?php }else{  ?>
      <script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validatede.js"></script>
 <?php  } ?>
</body>
</html>
