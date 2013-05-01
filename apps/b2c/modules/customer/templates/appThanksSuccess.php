<?php
use_helper('I18N');
?>
<div class="">
    <?php
    $os = $_SERVER['HTTP_USER_AGENT'];    
    if(strpos($os, "Android")!== false){ ?>
    <p>
        Descargar su Kimarin APP / download your Kimarin APP:<br />
        Android: yyyy<br />
    </p>
    <?php
    }elseif (strpos($os, "Iphone") !==false){
    ?>
    <p>
        Descargar su Kimarin APP / download your Kimarin APP:<br />
        iPhone: xxxxxx<br />
    </p>
    <?php     
    }else{
    ?>
    <p>
        <strong>Download Instructions</strong>
    </p>
    <?php
    }
    ?>
    
</div><br clear="all" />