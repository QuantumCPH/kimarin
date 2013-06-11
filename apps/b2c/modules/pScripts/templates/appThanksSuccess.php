<?php
use_helper('I18N');
?>
<div style="width:400px;">
    <?php 
    $os = strtolower($_SERVER['HTTP_USER_AGENT']);
    
    if(strpos($os, "android")!== false || strpos($os, "iphone") !==false){ ?>
    <p style='font-size: 14px;'>
       Thank you for your order of Kimarin APP.</p><br />
    <p style='font-size: 14px;'>
* If you have an Android phone you can download the APP here (link to Google Play)<br />
* If you have an iPhone you can download the APP here (link to App Store)<br />
    </p><br />
    <p style='font-size: 14px;'>
Gracias por su solicitud de Kimarin APP.</p><br />
    <p style='font-size: 14px;'>
*	Si usted tiene un teléfono Android puedes descargar la aplicación aquí (link to Google Play)<br />
*	Si usted tiene un iPhone puedes descargar la aplicación aquí (link to App Store)<br />

    </p><br />
    <?php
    }else{
    ?>
    <p style='font-size: 14px;'>Thank you for your order of Kimarin APP.</p><br />
    <p style='font-size: 14px;'>
        * If you have an Android phone you can download the APP on Google Play. Look for Kimarin.</p><br />
    <p style='font-size: 14px;'>
* If you have an iPhone you can download the APP on App Store. Look for Kimarin.</p><br />
   <p style='font-size: 14px;'>
Gracias por su solicitud de Kimarin APP.</p><br />
   <p style='font-size: 14px;'>
*	Si usted tiene un teléfono Android puedes descargar la aplicación en Google Play. Busque Kimarin.<br />
*	Si usted tiene un iPhone puedes descargar la aplicación en App Store. Busque Kimarin.

    </p>
    <?php
    }
    ?>
   <?php echo sfConfig::get("app_conversion_code")?> 
</div><br clear="all" />