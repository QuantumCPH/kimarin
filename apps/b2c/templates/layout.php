<?php use_helper('I18N') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">
<head>

<?php include_http_metas() ?>
<?php include_metas()  ?>
<?php include_title() ?>
     <link rel="shortcut icon" href="http://customer.zapna.no/images/favicon.ico" type="image/x-icon">
<?php use_javascript('../zerocall/js/jquery-1.4.2.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/cufon-yui.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/Barmeno_400-Barmeno_400.font.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/Barmeno-Medium_400.font.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/cufon-replace.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/jquery.jcarousel.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('../zerocall/js/carousel.js', '', array('absolute'=>true)) ?>

<?php use_javascript('jquery.formatCurrency-1.3.0.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('i18n/jquery.formatCurrency.all.js', '', array('absolute'=>true)) ?>
    <?php use_javascript('jquery-ui-1.8.16.custom.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('jquery.corner.js');?>                             
<!--[if IE]>
 <link href="<?php echo stylesheet_path('../zerocall/style/ie-7.css', true) ?>" rel="stylesheet" type="text/css" />
<?php use_stylesheet('../zerocall/style/styleie.css', 'last', array('absolute'=>true)) ?>
 <?php use_stylesheet('ui-lightness/jquery-ui-1.8.16.custom.css', 'last', array('absolute'=>true)) ?>

<![endif]-->
  <!--[if !IE]><!-->
      <?php use_stylesheet('../zerocall/style/style.css', 'last', array('absolute'=>true)) ?>

<?php use_stylesheet('../sf/sf_admin/css/main.css', '', array('absolute'=>true)) ?>
  <?php use_stylesheet('ui-lightness/jquery-ui-1.8.16.custom.css', '', array('absolute'=>true)) ?>
  <!--<![endif]-->
  
</head>
<body>
    <div style="vertical-align: top;float: right;">

                        <?php echo link_to(image_tag('/images/norway.png'), 'customer/changeCulture?new=no'); ?>

                         <?php echo link_to(image_tag('/images/english.png'), 'customer/changeCulture?new=en'); ?>

             </div>
<div id="wrap"><?php
// set alert if customer is not yet registered with fonet

//$alert_fonet_customer = CustomerPeer::

?>

<!-- end header --> <?php echo $sf_content; ?></div>
<!-- end wrap -->

<script type="text/javascript"> 
	Cufon.now(); 
     $(document).ready(function()
     {
        $('.sidebar_button').corner('round 5px');
        $('.butonsigninsmall').corner('round 5px');
        $('.buton').corner('round 5px');
        $('.loginbuttun').corner('round 5px');
        
     }); 
</script>

<?php if($sf_user->getCulture()=='en'){    ?>
   <script type="text/javascript" src="http://customer.zapna.no/js/jquery.validate1.js"></script>

   <?php }else{  ?>
      <script type="text/javascript" src="http://customer.zapna.no/js/jquery.validatede.js"></script>
 <?php  } ?>
</body>
</html>
