<?php use_helper('I18N') ?>
<?php use_helper('Number') ?>
 
<?php 
 if($sf_user->getCulture()=='de'){
     $class = 'refill_de';
 }  else {
     $class = '';
 }
 ?>
<div data-role="page"  data-theme="c">

	<div data-role="header"  data-theme="c"><h1><?php echo __("%1% Refill Payment details:",array("%1%"=>sfConfig::get("app_site_title")));?></h1></div>
        
        
       <?php if($customerBalance+$order->getExtraRefill() >= 250){   ?>
    <?php echo "<div class='alert_bar ".$class."'>".__("Your payment has not been accepted as your account balance will exceed 250%1%.",array("%1%"=>sfConfig::get("app_currency_code"))).'</div>'; ?>
 <?php } ?>
            <table width="234" cellpadding="5" cellspacing="0"> 
<tr>
                    <td width="118" height="30" class="cufon"><?php echo __('Refill amount');?></td>
                    <td width="94" align="right">
                    <?php $refillamount = $transaction->getAmount()-$transaction->getVat();?>
                    <span class="cufon"><?php echo __(number_format($refillamount,2));?></span> <?php echo sfConfig::get('app_currency_code');?></td>
              </tr>    
                    
               <tr>  
                   <td height="30" class="cufon"><?php echo __('IVA');?></td><td align="right"><span class="cufon"><?php echo __(number_format($transaction->getVat(),2));?></span><?php echo sfConfig::get('app_currency_code');?></td>
               </tr>
               <tr class="refilltotal">
                    <td height="30" class="cufon"><?php echo __('Total');?></td><td align="right"><span class="cufon"><?php echo __(number_format($transaction->getAmount(),2));?></span><?php echo sfConfig::get('app_currency_code');?></td>
                </tr>
            </table>
              <div data-role="content">	
           
            <p>
            <?php 
             $refillbonus=0; 
             if($product->getBonus() > 0):
            $refillbonus = $product->getBonus();
          ?>
            <?php 
               //$refilltext = __('Airtime value refilled on your account %1%%2% PLUS %3%%2% = %4%%2%',array("%1%"=>number_format($refillamount,2),"%2%"=>sfConfig::get('app_currency_code'),"%3%"=>number_format($refillbonus,2),"%4%"=>number_format($refillamount+$refillbonus,2)));
               $refilltext = "<span class='cufon'>".__('Airtime value refilled on your account ').number_format($refillamount,2)."</span>".sfConfig::get('app_currency_code')." <span class='cufon'>".__('PLUS ').number_format($refillbonus,2)."</span>".sfConfig::get('app_currency_code')." <span class='cufon'>= ".number_format($refillamount+$refillbonus,2)."</span>".sfConfig::get('app_currency_code');
            else:
               //$refilltext = __('Airtime value refilled on your account %1%%2%',array("%1%"=>number_format($refillamount,2),"%2%"=>sfConfig::get('app_currency_code'))); 
                $refilltext = "<span class='cufon'>".__('Airtime value refilled on your account ').number_format($refillamount,2)."</span>".sfConfig::get('app_currency_code');
            ?>
            <?php  endif;?>  
              <?php echo $refilltext;?>  
            </p>
            <br />
            <p>
            <?php echo __("The refill amount is valid for 180 days.")?>
            </p>
            
            <br/>
            <form method="post" action="<?php echo $target; ?>pScripts/appRefilToPaypal" target="_parent">
                <input type="hidden" value="<?php echo $queryString; ?>" name="qstr" />
                <?php if($customerBalance+$order->getExtraRefill() < 250){ ?>
             
                    <input type="submit"  name="<?php echo __('Pay') ?>"    value="<?php echo __('Pay') ?>" />
                
                <?php }?>
            </form>
       
    </div>
</div>
  