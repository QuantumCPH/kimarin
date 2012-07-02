<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">
    
        
        window.print();
   
</script>
<h2><?php echo __('Earning Summary') ?></h2>
<table cellspacing="0" width="100%" class="summary">
<?php
if ($agent->getIsPrepaid()) {
?>
    <tr>
        <td><strong><?php echo __('Your Balance is:') ?></strong></td>
        <td align="right"><?php echo $agent->getBalance(); ?></td>
    </tr>
<?php } ?>
    <tr>
        <td><b><?php echo __('Customers') ?></b> <?php echo __('registered with you:') ?></td>
        <td align="right"><?php echo count($registrations) ?></td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?php echo __('Total') ?> <strong><?php echo __('revenue on registration') ?></strong></td>
        <td align="right">
<?php echo $registration_revenue
?>
        </td>
    </tr>
    <tr>
        <td><?php echo __('Total commission earned on registration:') ?></td>
        <td align="right">
<?php echo $registration_commission;
?>
        </td>
    </tr>

    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?php echo __('Total') ?> <strong><?php echo __('revenue on refill') ?></strong></td>
        <td align="right">
<?php echo $refill_revenue
?>
        </td>
    </tr>
    <tr>
        <td><?php echo __('Total commission earned on refill:') ?></td>
        <td align="right">
<?php echo $refill_com ?>
        </td>
    </tr>

    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?php echo __('Total') ?> <strong><?php echo __('revenue earned') ?>  </strong><?php echo __('on refill from shop:') ?></td>
        <td align="right">
<?php echo $ef_sum; ?>
        </td>
    </tr>
    <tr>
        <td><?php echo __('Total') ?> <strong><?php echo __('commission earned') ?> </strong><?php echo __('on refill from shop:') ?></td>
        <td align="right">
<?php echo $ef_com; ?>
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
<!--       <tr>
        <td><?php echo __('Total') ?> <strong>revenue </strong><?php echo __('on SMS Registeration:') ?></td>
            <td align="right">
<?php echo $sms_registration_earnings; ?>
		</td>
	</tr>
  <tr>
            <td><?php echo __('Total') ?> <strong> <?php echo __('Commission earned') ?> </strong><?php echo __('on SMS Registeration:') ?></td>
                    <td align="right">
<?php echo $sms_commission_earnings; ?>
            </td>
    </tr>
    -->


</table>