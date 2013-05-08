<?php
use_helper('I18N');
?>
<div class="">
    <?php 
    $os = strtolower($_SERVER['HTTP_USER_AGENT']);
    
    if(strpos($os, "android")!== false || strpos($os, "iphone") !==false){ ?>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
       Thank you for your order of Kimarin APP.</p>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
* If you have an Android phone you can download the APP here (link to Google Play)<br />
* If you have an iPhone you can download the APP here (link to App Store)<br />
    </p>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
Gracias por su solicitud de Kimarin APP.</p>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
*	Si usted tiene un teléfono Android puedes descargar la aplicación aquí (link to Google Play)<br />
*	Si usted tiene un iPhone puedes descargar la aplicación aquí (link to App Store)<br />

    </p>
    <?php
    }else{
    ?>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>Thank you for your order of Kimarin APP.</p>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
        * If you have an Android phone you can download the APP on Google Play. Look for Kimarin.</p>
    <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
* If you have an iPhone you can download the APP on App Store. Look for Kimarin.</p>
   <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
Gracias por su solicitud de Kimarin APP.</p>
   <p style='font-family:"Times New Roman", Times, serif;font-size: 14px;'>
*	Si usted tiene un teléfono Android puedes descargar la aplicación en Google Play. Busque Kimarin.<br />
*	Si usted tiene un iPhone puedes descargar la aplicación en App Store. Busque Kimarin.

    </p>
    <?php
    }
    ?>
    
</div><br clear="all" />