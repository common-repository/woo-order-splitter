<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="wos_categories_list hides">
<?php

	$category_shipping_class = (array_key_exists('wc_os_shipping_selection', $WC_OS_Shipping_Settings) && $WC_OS_Shipping_Settings['wc_os_shipping_selection']=='category_shipping_class');
	
?>
<label></label>



<input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
<input type="hidden" name="wos_pg" value="<?php echo esc_attr($current); ?>" />

<?php wp_nonce_field( 'wc_os_cats_action', 'wc_os_cats_field' ); ?>


<?php	

switch($wc_os_settings['wc_os_ie']){ 
	
	case 'group_cats': 
		
	//do_action('wc_os_after_group_category_headings');
		
	$product_categories = wc_os_get_product_categories($current, $wc_os_per_page);
	
	
	$total_categories = count(wc_os_get_product_categories());
	
	
	$total_pages = ceil($total_categories/$wc_os_per_page);	
	$radius = floor($total_pages/2);
	
		
	
	if( !empty($product_categories) ){
		
		

		



?>
<div class="wos_categories_list_pagination">


<?php
	if($category_shipping_class){
?>
	<div class="alert alert-warning wc-os-gc wc-os-gc-shipping-class-order" role="alert">
      <?php _e('Shipping Class will be applied according to the following selection.', 'woo-order-splitter'); ?>      
    </div>
    <a title="<?php _e('Video Tutorial for Shipping Calculation', 'woo-order-splitter'); ?>" class="wc-os-gc-shipping-class-order" href="https://www.youtube.com/embed/HiMXcSvc40I" target="_blank"><img src="<?php echo esc_url($wc_os_url); ?>img/shipping-per-class-order.png" /><i class="fab fa-youtube"></i></a>
    <a title="<?php _e('Video Tutorial for Shipping Classes', 'woo-order-splitter'); ?>" class="wc-os-gc-shipping-class-order" href="https://www.youtube.com/embed/Vyt5xIewlOs" target="_blank"><i class="fab fa-youtube"></i></a>
    
<?php		
	}
?>	
	<div class="alert alert-primary wc-os-gc wc-os-gc-no-selection hides" role="alert">
      <?php _e('All products will be grouped by categories in separate orders.', 'woo-order-splitter'); ?>
    </div>
	<div class="alert alert-primary wc-os-gc wc-os-gc-one-selection hides" role="alert">
      <?php _e('All selected categories will have their products grouped in a separate order. Remaining items will stay in original order.', 'woo-order-splitter'); ?>
    </div>
	<div class="alert alert-primary wc-os-gc wc-os-gc-multi-selection hides" role="alert">
      <?php _e('All categories from same group will have their products grouped in a separate order. Remaining items will stay in original order.', 'woo-order-splitter'); ?>
    </div>

<?php
if($total_pages>1): ?>
<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_pagination.php')); ?>
<?php endif; ?>

<?php get_wos_pg_limit_select('D'); ?>

</div>

<table border="0" class="">

<thead>

<th><?php _e('Enable/Disable', 'woo-order-splitter'); ?></th>

<th><?php _e('No. of Products', 'woo-order-splitter'); ?></th>

<th><?php _e('Category Name', 'woo-order-splitter'); ?></th>

<th><?php _e('Split Group', 'woo-order-splitter'); ?></th>

<th class="group_status_heading wc_<?php echo esc_attr($wc_os_settings['wc_os_ie']); ?>"><?php _e('Group Status', 'woo-order-splitter'); ?></th>

<th><?php _e('Category Actions', 'woo-order-splitter'); ?></th>

</thead>

<tbody>

<?php wc_os_get_product_categories_list($product_categories); ?>

</tbody>

</table>



<div class="wc_os_multiple_warning under-group_cats"><br />

<small><?php echo wp_kses_post($wc_os_multiple_warning); ?></small>

</div>

<?php	

	}

		
	
	break;
	
	default:
?>	
<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_categories_list">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>
<?php
	break;
}
?>
<input type="hidden" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="0" />



<input type="hidden" name="split_action[group_cats][0]" value="" />

<input type="hidden" name="wc_os_cats[group_cats][0][]" value="" />

<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>

</form>