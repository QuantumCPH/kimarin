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
<?php use_javascript('../zerocall/js/carousel.js', '', array('absolute'=>true)) ?>

<?php use_javascript('jquery.formatCurrency-1.3.0.min.js', '', array('absolute'=>true)) ?>
<?php use_javascript('i18n/jquery.formatCurrency.all.js', '', array('absolute'=>true)) ?>
    <?php use_javascript('jquery-ui-1.8.16.custom.min.js', '', array('absolute'=>true)) ?>
       <?php use_javascript('jquery.ui.draggable.js', '', array('absolute'=>true)) ?>
<?php use_javascript('jquery.corner.js');?>    
     
<!--[if IE]>
 <link href="<?php echo stylesheet_path('../zerocall/style/ie-7.css', true) ?>" rel="stylesheet" type="text/css" />

<?php use_stylesheet('../zerocall/style/styleie.css', 'last', array('absolute'=>true)) ?>
 <?php use_stylesheet('ui-lightness/jquery-ui-1.8.16.custom.css', 'last', array('absolute'=>true)) ?>

<![endif]-->
  <!--[if !IE]><!-->
      <?php use_stylesheet('../zerocall/style/style.css', 'last', array('absolute'=>true)) ?>


  <?php use_stylesheet('ui-lightness/jquery-ui-1.8.16.custom.css', '', array('absolute'=>true)) ?>
 <!--<![endif]-->
   <?php use_stylesheet('jquery.alerts.css', 'last', array('absolute'=>true)) ?> 
   <?php use_javascript('jquery.alerts.js', '', array('absolute'=>true)) ?>

 
</head>
<body>

<!--
    <div style="vertical-align: top;float: right;">

    <?php echo link_to(image_tag('/images/lang_spa1.png'), 'customer/changeCulture?new=es', array('id'=>'lang_spa','title'=>'es')); ?>
    <?php echo link_to(image_tag('/images/lang_eng1.png'), 'customer/changeCulture?new=en',array('title'=>'en')); ?>
    <?php echo link_to(image_tag('/images/lang_de1.png'), 'customer/changeCulture?new=de',array('title'=>'de')); ?>    
    <?php echo link_to(image_tag('/images/lang_cat1.png'), 'customer/changeCulture?new=ca', array('id'=>'lang_spa','title'=>'ca')); ?>
    

             </div> -->
<div id="wrap"><?php //echo $sf_user->getCulture();
// set alert if customer is not yet registered with fonet

//$alert_fonet_customer = CustomerPeer::

?>

<!-- end header --> <?php echo $sf_content; ?></div>
<!-- end wrap -->

<script type="text/javascript"> 	
    //Cufon.now(); 
     jQuery(document).ready(function()
     {
        jQuery('.submitBtn').corner('round 5px');
        jQuery('.sidebar_button').corner('round 5px');
        jQuery('.butonsigninsmall').corner('round 5px');
        jQuery('.buton').corner('round 5px');
        jQuery('.loginbuttun').corner('round 5px');
        
     }); 
</script>

<?php if($sf_user->getCulture()=='en'){    ?>
   <script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validate1.js"></script>
<?php }elseif($sf_user->getCulture()=='es'){    ?>
   <script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validatees.js"></script>
<?php }elseif($sf_user->getCulture()=='ca'){    ?>
   <script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validateca.js"></script>
<?php }else{  ?>
  <script type="text/javascript" src="<?php echo sfConfig::get('app_web_url')?>js/jquery.validatede.js"></script>
 <?php  } ?>
  <?php echo sfConfig::get("app_clicky_code")?>
</body>
</html>
