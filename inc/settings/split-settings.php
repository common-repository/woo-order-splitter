<div class="nav-tab-content hides tab-split-settings">
<form class="nav-tab-content hides split-settings-dashboard" data-selected-method-type="" data-selected-method="<?php echo $wc_os_settings['wc_os_ie']; ?>" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<?php $save_changes = false; ?>

<input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
<input type="hidden" name="wos_pg" value="<?php echo esc_attr($current); ?>" />



<?php wp_nonce_field( 'wc_os_settings_action', 'wc_os_settings_field' ); ?>

<br />

<div class="wc_os_notes"><h3><?php _e('Select a split method to configure products, categories, metadata, attributes and/or groups', 'woo-order-splitter'); ?>: <a style="float:right; font-size:12px; text-decoration:none;" href="https://wordpress.org/support/plugin/woo-order-splitter" title="<?php _e('Click here to reach WordPress Plugins Developer', 'woo-order-splitter'); ?>" target="_blank"><i class="fab fa-pied-piper-alt"></i> <?php _e('Need help?', 'woo-order-splitter'); ?></a></h3> </div>



<div class="wc_os_ahead">

<?php

	$wc_os_settings['wc_os_qty_split_option'] = (isset($wc_os_settings['wc_os_qty_split_option'])?$wc_os_settings['wc_os_qty_split_option']:'');

	wc_os_get_ie_html();
?>



</div>



<?php 
	$total_pages = 0;
	$products = array();
	
	switch($wc_os_settings['wc_os_ie']){ 
	
		case 'group_by_gf_meta':
		case 'subscription_split':		
		case 'group_by_attributes_only':
		case 'group_by_attributes_value':
		case 'group_by_order_item_meta':
		
			$save_changes = true;

		break;
		

		case 'default': 
		case 'exclusive':
		case 'inclusive':
		case 'shredder':
		case 'io':
		case 'quantity_split':
		case 'groups':
				
			$save_changes = true;
			
			
			
			
			
			$get_count = $wpdb->get_row("SELECT COUNT(*) AS total FROM $wpdb->posts WHERE post_type='product'");
			   
			
			$total_products = $get_count->total;
			
			$products = wc_os_get_products($wc_os_per_page, $current);
			
		
		
			
		
		
			$wc_os_products_ids = $wc_os_settings['wc_os_products'];
		
		
			if(array_key_exists($wc_os_settings['wc_os_ie'], $wc_os_products_ids)){
		
				$wc_os_products_ids = $wc_os_settings['wc_os_products'][$wc_os_settings['wc_os_ie']];
		
			}
		
		
			
			$total_pages = ceil($total_products/$wc_os_per_page);	
			$radius = floor($total_pages/2);
			
		break;
		
		case 'groups_by_meta':
			$save_changes = true;
			
			include('groups_by_meta.php');
		break;
	}

?>



<div class="wos_attributes_only_list"><h3><?php _e('Products/Variations will be splitted into multiple orders separately or in grouping as defined here', 'woo-order-splitter'); ?></h3><br />
<small><?php _e('Default: All attributes are considered selected. On selection, only selected attributes will be considered for split method.', 'woo-order-splitter'); ?></small>
</div>
<div class="wos_attributes_values_list"><h3><?php _e('Products/Variations will be splitted into multiple orders group by attribute values', 'woo-order-splitter'); ?></h3><br />
<small><?php _e('Default: All attributes are considered as selected. On selection, only selected attributes will be considered for split method.', 'woo-order-splitter'); ?></small>
</div>
<div class="group_by_order_item_meta_list"><h3><?php _e('Order items metadata will be used to group items and form it into multiple orders', 'woo-order-splitter'); ?></h3><br />
<small><?php _e('Only selected meta keys+values will be considered as a group for new orders.', 'woo-order-splitter'); ?></small>
</div>
<div class="group_by_gf_meta_list"><h3><?php _e('Order items metadata from Gravity Forms will be used to group', 'woo-order-splitter'); ?></h3><br />
<small><?php _e('Only selected meta field will be considered for grouping in new orders.', 'woo-order-splitter'); ?></small>
</div>

<?php

switch($wc_os_settings['wc_os_ie']){

    case 'group_by_acf_group_fields':

    break;
    default:

?>

        <div class="wc_os_acf_values_list">
            <h3>
                <?php echo split_actions_display('group_by_acf_group_fields', 'title');?>
            </h3>

            <div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wc_os_acf_values_list">
                <?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
            </div>
        </div>



<?php

    break;

}

?>

    <?php

    switch($wc_os_settings['wc_os_ie']){

        case 'group_by_partial_payment':

		break;
        default:

            ?>

            <div class="wc_os_partial_payment_list">
                <h3>
                    <?php echo split_actions_display('group_by_partial_payment', 'title');?>
                </h3>

                <div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wc_os_partial_payment_list">
                    <?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
                </div>
            </div>



            <?php

            break;

    }

    ?>



    <?php
		if(function_exists('wc_os_inclusive_method_options')){ wc_os_inclusive_method_options(); }
      


    ?>

        <div class="wc_os_io_options" data-method="io" <?php echo ($wc_os_settings['wc_os_ie']=='io'?'':'style="display:none;"'); ?>>

			
            
            <div class="vendor-left">
            	<div class="status-default">
                    <p><?php _e('Default Order Status', 'woo-order-splitter'); ?> <small>(<?php _e('Statuses work without Split as well', 'woo-order-splitter'); ?>)</small><a title="<?php _e('How it works?', 'woo-order-splitter'); ?>" href="https://www.youtube.com/embed/irYqylj84Wg" target="_blank" class="default-io-status"><i class="fab fa-youtube"></i></a></p>
                    <?php if(function_exists('wc_os_get_statuses_select_html') && function_exists('wc_os_get_io_setting')){wc_os_get_statuses_select_html('wc_os_settings[io_options]', 'default_stock', $wc_os_order_statuses_keys, false);} ?>
                </div>
                <div class="wc_os_select_wrapper" style="width:45%;">
                    <p><?php _e('In Stock Order Status', 'woo-order-splitter'); ?></p>
                    <?php if(function_exists('wc_os_get_statuses_select_html') && function_exists('wc_os_get_io_setting')){wc_os_get_statuses_select_html('wc_os_settings[io_options]', 'in_stock', $wc_os_order_statuses_keys);} ?>
                </div>
                <div class="wc_os_select_wrapper" style="width:55%;">
                    <p><?php _e('Out Stock Order Status', 'woo-order-splitter'); ?></p>
                    <?php if(function_exists('wc_os_get_statuses_select_html') && function_exists('wc_os_get_io_setting')){wc_os_get_statuses_select_html('wc_os_settings[io_options]', 'out_stock',  $wc_os_order_statuses_keys);} ?>
                </div>
            </div>

            <div class="switch-right">
            
            	<div class="switch-sub">
                
                    <input type="radio" value="yes" <?php echo function_exists('wc_os_get_io_setting')?checked(wc_os_get_io_setting('out_stock_amount', 'yes') == 'yes', true, false):''; ?> name="wc_os_settings[io_options][out_stock_amount]" />
                    <input type="radio" value="no" <?php echo function_exists('wc_os_get_io_setting')?checked(wc_os_get_io_setting('out_stock_amount') == 'no', true, false):''; ?> name="wc_os_settings[io_options][out_stock_amount]" />
                    <div class="switch-rtop">
                        <?php _e('Get in stock items payment','woo-order-splitter')?>
    
                        <i class="fas fa-money-bill-wave"></i>
    
                    </div>
                    <div class="switch-rmiddle">
                        <?php _e('and','woo-order-splitter')?>
                    </div>
                    <div class="switch-rbottom">
    
                        <div class="out-stock-amount" data-text="no" <?php echo function_exists('wc_os_get_io_setting')?(wc_os_get_io_setting('out_stock_amount', 'yes') == 'yes' ?'style="display:block;"':''):''; ?>>
                        <?php _e('Get out of stock items payment','woo-order-splitter')?>
    
                        <i class="fas fa-money-bill-wave"></i>
                        </div>
    
                        <div title="<?php _e('But still get the payment for the partial in stock items','woo-order-splitter')?>" class="out-stock-amount" data-text="yes" <?php echo function_exists('wc_os_get_io_setting')?(wc_os_get_io_setting('out_stock_amount') == 'no' ?'style="display:block;"':''):''; ?>>
                        <?php _e('Do not get out of stock items payment','woo-order-splitter')?>
                            <i class="fas fa-money-bill-wave"></i>
                            <div class="wos_cross"></div>
                        </div>
    
                    </div>
               </div>     	
<?php
	$io_items_remaining = function_exists('wc_os_get_io_setting')?wc_os_get_io_setting('io_items_remaining', 'group'):'group';
	
?>        
                <div class="switch-sub">
                    <input type="radio" value="group" <?php echo checked($io_items_remaining == 'group', true, false); ?> name="wc_os_settings[io_options][io_items_remaining]" />
                    <input type="radio" value="separate" <?php echo checked($io_items_remaining == 'separate', true, false); ?> name="wc_os_settings[io_options][io_items_remaining]" />
                    <div class="switch-rtop">
                        <?php _e('Group in-stock items together','woo-order-splitter')?>
                        
                        <i class="fas fa-boxes"></i>
                        
                    </div>
                    <div class="switch-rmiddle">
                        <?php _e('and','woo-order-splitter')?>
                    </div>
                    <div class="switch-rbottom">
                    
                        <div class="switch-group-remaining io-remaining-items" data-text="separate" <?php echo ($io_items_remaining=='group'?'style="display:block;"':''); ?>>
                        <?php _e('Group all out-stock items in one order','woo-order-splitter')?>
                        
                        <i class="fas fa-boxes"></i>
                        </div>
                        
                        <div class="switch-split-remaining io-remaining-items" data-text="group" <?php echo ($io_items_remaining=='separate'?'style="display:block;"':''); ?>>
                        <?php _e('Separate all out-stock items into individual orders','woo-order-splitter')?>
                            <i class="fas fa-square"></i>
                            <i class="fas fa-square"></i>
                            <i class="fas fa-square"></i>
                        
                        </div>
                    
                    </div>
                    
                </div>            


            </div>
            <?php if(!array_key_exists('wc_os_effect_parent', $wc_os_general_settings)): ?>
            <div class="w-100 float-left pt-1">
  				
                <div role="alert" class="alert alert-warning float-left w-100 text-center">
                  <?php _e('It is recommended that you TURN ON "Remove Items from Parent Order on Split" option from settings page so parent order can have only In Stock items after split.', 'woo-order-splitter'); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>


<?php if($total_pages>1): ?>
<div class="wc_os_notes wos_products_list">
<h3><?php _e('Select products to trigger the above selected split order action', 'woo-order-splitter'); ?>:</h3>
<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_pagination.php')); ?>
</div>
<?php endif; ?>

<?php if(function_exists('get_wos_pg_limit_select')){ get_wos_pg_limit_select('E'); }else{  } ?>







<div class="wos_attributes_only_list attribs">


<?php	

	
switch($wc_os_settings['wc_os_ie']){ 
	
	case 'group_by_attributes_only':
		
		wc_os_get_attributes_list(false, ($current-1)*$wc_os_per_page, false, true);

	break;
	default:
?>	
<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_attributes_only_list">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>
<?php
	break;	
}
?>

</div>



<div class="wos_attributes_values_list attribs">


<?php	

switch($wc_os_settings['wc_os_ie']){ 
	
	case 'group_by_attributes_value': 
		

		wc_os_get_attributes_list(false, ($current-1)*$wc_os_per_page, false, false);
	
	break;
	
	
	
	default:
?>	
<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_attributes_values_list">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>
<?php
	break;
}
?>

</div>


<div class="group_by_order_item_meta_list attribs">


<?php	

switch($wc_os_settings['wc_os_ie']){ 
	
	
	case 'group_by_order_item_meta':
	
		wc_os_get_order_item_meta_data(false, ($current-1)*$wc_os_per_page, false, false);
		
	break;
	
	default:
?>	
<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-group_by_order_item_meta_list">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>
<?php
	break;
}
?>

</div>






<?php
	$wc_os_ie_name = ($wc_os_settings['wc_os_ie']?'wc_os_settings[wc_os_products]['.$wc_os_settings['wc_os_ie'].'][]':'wc_os_settings[wc_os_products][]');
	if($products_related_methods){
		
	$wc_os_all_product = (isset($wc_os_settings['wc_os_all_product']) && $wc_os_settings['wc_os_all_product']=='all_products');	
	
?>		

<div class="wos_products_search">
    <input type="text" name="wc_os_product_search" placeholder="<?php _e('Search Products', 'woo-order-splitter'); ?>">
    <button class="button button-primary"><i class="fa fa-search"></i></button>
</div>		

<table border="0" class="wos_products_list <?php echo $wc_os_settings['wc_os_ie']; ?>">

<?php if(!empty($products)): ?>	
<thead>

<th><input <?php checked($wc_os_all_product); ?> id="wc_os_all_product" name="wc_os_settings[wc_os_all_product]" type="checkbox" value="all_products" title="<?php _e('All products listed and all future products added.','woo-order-splitter'); ?>" /></th>

<th><?php _e('Stock', 'woo-order-splitter'); ?></th>

<th><?php _e('Product Name', 'woo-order-splitter'); ?></th>

<th><?php _e('Category', 'woo-order-splitter'); ?></th>

<th><?php _e('Split Group', 'woo-order-splitter'); ?></th>

<th class="group_status_heading"><?php _e('Group Status', 'woo-order-splitter'); ?></th>

<th><?php _e('Product Actions', 'woo-order-splitter'); ?></th>

</thead>
<?php endif; ?>
<tbody>

<?php	

		



		if(!empty($products) && function_exists('wc_os_get_products_list')):		
			
			wc_os_get_products_list($products);
		
		else:
?>	
<tr><td colspan="6">
<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_products_list">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>
</td></tr>
<?php		
		endif;

?>

</tbody>

</table>
	






<?php if(!empty($products)): ?>	
<div class="wc_os_multiple_warning under-products_list"><br />

<small><?php echo wp_kses_post($wc_os_multiple_warning); ?></small>

</div>
<?php endif; ?>

<?php 
	
	}
?>
		


        

<p class="submit dashboard-settings-form-submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>


<input type="hidden" name="wc_os_attributes_group[]" value="0" />
<input type="hidden" name="wc_os_attributes[]" value="0" />
<input type="hidden" name="wc_os_attributes_values[]" value="0" />
<input type="hidden" name="wc_os_metadata[]" value="0" />
<input type="hidden" name="wc_os_settings[wc_os_products][]" value="0" />



</form>


<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/cat-qty-split-settings.php')); ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/grouped-cat-settings.php')); ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/vendor-user-role.php')); ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/vendor-user-terms.php')); ?>


<?php if(function_exists('wc_os_acf_method_settings_html')){wc_os_acf_method_settings_html();} ?>

<?php if(function_exists('wc_os_partial_payment_html')){wc_os_partial_payment_html();} ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/gravity-forms-meta-based.php')); ?>
</div>