<?php
		if(!empty($_POST) && array_key_exists('wc-os-coupons', $_POST)){

			if (! wp_verify_nonce( $_POST['wc_os_coupons_field'], 'wc_os_coupons_action' )
			) {
	?>
	<div class="alert alert-danger" role="alert">
	<?php _e('Sorry, your nonce did not verify.', 'woo-order-splitter'); ?>
	</div>           
	<?php		   
	
			} else {
				$wc_os_coupon_option = sanitize_wc_os_data($_POST['wc-os-coupons']);
				update_option('wc_os_coupon_option', $wc_os_coupon_option);
			}
		
		}
		$wc_os_coupon_option = get_option('wc_os_coupon_option');
?>

<form class="nav-tab-content tab-coupons pt-4 hides" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<?php wp_nonce_field( 'wc_os_coupons_action', 'wc_os_coupons_field' ); ?>    
    <ul>
    	<li><label for="wc-os-coupons-default"><input <?php checked($wc_os_coupon_option==''); ?> type="radio" name="wc-os-coupons" id="wc-os-coupons-default" value="" /><?php _e('Default', 'woo-order-splitter'); ?></label> <small>(<?php _e('Do not clone or distribute coupons amount to child order.', 'woo-order-splitter'); ?>)</small></li>
        <li><label for="wc-os-coupons-clone"><input <?php checked($wc_os_coupon_option=='clone'); ?> type="radio" name="wc-os-coupons" id="wc-os-coupons-clone" value="clone" /><?php _e('Clone', 'woo-order-splitter'); ?></label> <small>(<?php _e('Clone same coupon amount to child orders without any change.', 'woo-order-splitter'); ?>)</small></li>
        <li><label for="wc-os-coupons-ratio"><input <?php checked($wc_os_coupon_option=='ratio'); ?> type="radio" name="wc-os-coupons" id="wc-os-coupons-ratio" value="ratio" /><?php _e('Ratio', 'woo-order-splitter'); ?></label> <small>(<?php _e('It will calculate child order totals and distribute discounted amount accordingly.', 'woo-order-splitter'); ?>)</small></li>
    </ul>


<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>


<div style="display:none";>
wc_os_set_coupon_discount_amounts
wc_os_apply_coupon
wc_os_apply_coupon_child_orders
clone_order_items_by_item
clone_order_items
clone_order_coupons
splitted_order_data
split_order
cloned_order_data
wc_os_update_child_orders_stats
</div>
</form>