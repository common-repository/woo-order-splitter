<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="wos_categories_qty">

<label></label>

<?php //do_action('wc_os_after_group_category_headings'); ?>

<input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

<?php wp_nonce_field( 'wc_os_cats_action', 'wc_os_cats_field' ); ?>

<?php	

switch($wc_os_settings['wc_os_ie']){ 
	
	case 'cats': 


	$product_categories = wc_os_get_product_categories($current, $wc_os_per_page);
	
	
	$total_categories = count(wc_os_get_product_categories());
	
	
	$total_pages = ceil($total_categories/$wc_os_per_page);	
	$radius = floor($total_pages/2);
	
		
	
	if( !empty($product_categories) ){
		
		

		




?>
<div class="cbqse-wrapper">
	<ul>
    	<li><a data-id="1"><?php _e('Example', 'woo-order-splitter'); ?> #1</a></li>
        <li><a data-id="2"><?php _e('Example', 'woo-order-splitter'); ?> #2</a></li>
        <li><a data-id="3"><?php _e('Example', 'woo-order-splitter'); ?> #3</a></li>
    </ul>
    
    <img src="<?php echo esc_url($wc_os_url); ?>img/cbqse-1.png" />
    <img src="<?php echo esc_url($wc_os_url); ?>img/cbqse-2.png" />
    <img src="<?php echo esc_url($wc_os_url); ?>img/cbqse-3.png" />
	
</div>
<div class="wos_categories_qty_pagination">

<?php
if($total_pages>1): ?>
<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_pagination.php')); ?>
<?php endif; ?>

<?php get_wos_pg_limit_select('C'); ?>
</div>

<table border="0">
    <thead>
    
        <th>&nbsp;</th>
        
        <th>&nbsp;</th>
        
        <th>&nbsp;</th>
    
		<th title="<?php echo __('Default value, which will work for all categories.', 'woo-order-splitter').' '.__('Leave empty for category based split without qty. split.', 'woo-order-splitter'); ?>"><?php _e('Global Split Ratio', 'woo-order-splitter'); ?> (e.g. 3:3:2)</th>        
        <th>&nbsp;</th>
    
    </thead>   
    <tbody>
        <tr>
        
            <td>&nbsp;</td>
            
            <td>&nbsp;</td>
            
            <td>&nbsp;</td>
            
            <td><input type="text" name="wc_os_cats[split_ratio]" value="<?php echo array_key_exists('split_ratio', $wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']['split_ratio']:''; ?>" /></td>
            
            <td>&nbsp;</td>
        
        </tr>
    </tbody>
    
</table>
    
<table border="0" class="cheese-pocket">

<thead>

<th><?php _e('Enable/Disable', 'woo-order-splitter'); ?></th>

<th><?php _e('No. of Products', 'woo-order-splitter'); ?></th>

<th><?php _e('Category Name', 'woo-order-splitter'); ?></th>

<th title="<?php echo __('Specific value which will overwrite default global value.', 'woo-order-splitter').' '.__('Leave empty for global value or default settings.', 'woo-order-splitter'); ?>"><?php _e('Specific Split Ratio', 'woo-order-splitter'); ?> (e.g. 3:3:2)</th>

<th><?php _e('Category Actions', 'woo-order-splitter'); ?></th>


</thead>

<tbody>

<?php wc_os_get_product_categories_qty($product_categories); ?>

</tbody>

</table>



<div class="wc_os_multiple_warning under-cats"><br />

<small><?php echo wp_kses_post($wc_os_multiple_warning); ?></small>

</div>

<?php	

	}
	break;
	
	default:
?>	

<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_categories_qty">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>

<?php 
	break;
}
?>

<input type="hidden" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="0" />



<input type="hidden" name="split_action[cats][]" value="" />

<input type="hidden" name="wc_os_cats[cats][]" value="" />

<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>

</form>