<?php $current_theme = get_option('current_theme'); ?>
<div class="nav-tab-content hides tab-advanced-settings" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

    <h3 class="nav-tab-wrapper">

        <?php //if($wc_os_pro): ?>

        <a class="nav-tab nav-tab-active" id="wc_os_rules" data-selection="rules"><i class="fas fa-book"></i> <?php _e("Rules",'woo-order-splitter'); ?></a>

        <?php /* <a class="nav-tab" id="wc_os_meta_keys"><?php _e("Order Meta Keys",'woo-order-splitter'); ?></a> */ ?>

        <?php //endif; ?>

        <a class="nav-tab" id="wc_os_meta_keys" data-selection="troubleshoot"><i class="fas fa-bug"></i> <?php _e("Troubleshoot",'woo-order-splitter'); ?></a>





        <a class="nav-tab" data-selection="import_export"><i class="fas fa-file-import"></i> <?php _e("Import / Export",'woo-order-splitter'); ?></a>

        <a class="nav-tab" data-selection="cron_jobs"><i class="fas fa-robot"></i> <?php _e("Cron Jobs / Action Hooks",'woo-order-splitter'); ?></a>



        <a class="nav-tab" data-selection="documentation"><i class="fas fa-scroll"></i> <?php _e("Documentation",'woo-order-splitter'); ?></a>
        
        <a class="nav-tab" data-selection="speed_optimization"><i class="fas fa-rocket"></i> <?php _e("Speed Optimization",'woo-order-splitter'); ?></a>

    </h3>




    <!--Rules Tab Content-->
    <?php

    if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')):

		if(
				(array_key_exists('sub_tab', $_GET) && $_GET['sub_tab']=='rules')
			 ||
			 	array_key_exists('wc_os_rules', $_POST)
		){

			$classObj = new wc_os_bulk_order_splitter;
	
			$classObj->wc_os_rules();
		
		}else{
?>
<form class="sub-tab-content hides wc_os_rules" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<div class="alert alert-warning" role="alert">
  <a href="<?php echo admin_url('admin.php?page=wc_os_settings&t=7&sub_tab=rules'); ?>" class="btn-sm btn-warning"><?php _e('Click here', 'woo-order-splitter'); ?></a> <?php _e('to refresh and fetch all product/variations related metadata.', 'woo-order-splitter'); ?>
</div>
</form>
<?php			
		}





    else:

        ?>

        <form class="sub-tab-content ignore wc_os_rules rules" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

            <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
            <input type="hidden" name="sub_tab" value="<?php echo isset($_GET['sub_tab'])?esc_attr($_GET['sub_tab']):'0'; ?>" />





            <a href="https://www.youtube.com/embed/swHpd8-9H-s" target="_blank"><?php _e('Watch Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></a><br />
            <a href="https://www.youtube.com/embed/nX9ir93V-ug" target="_blank"><?php _e('Watch Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></a>





            <div class="wc_os_notes"><a href="<?php echo esc_url($wc_os_premium_copy); ?>" target="_blank"><?php _e('This is a premium feature.', 'woo-order-splitter'); ?></a></div>



        </form>

    <?php

    endif; ?>










    <!--Troubleshoot Tab Content-->
    <form class="sub-tab-content ignore wc_os_console hides troubleshoot" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

        <input type="text" name="wc_os_order_id" placeholder="<?php _e('Order ID', 'woo-order-splitter'); ?>" />

        <input type="button" name="wc_os_order_test" value="<?php _e('Test', 'woo-order-splitter'); ?>" />



    </form>





    <!--import export Tab Content-->
    <?php wc_os_import_export_section(); ?>

    <!--Cron jobs Tab Content-->
    <div class="sub-tab-content hides wc_os_cron_jobs cron_jobs">
        <form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
            <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
            <input type="hidden" name="sub_tab" value="<?php echo isset($_GET['sub_tab'])?esc_attr($_GET['sub_tab']):'0'; ?>" />

            <?php wp_nonce_field( 'wc_os_crons_action', 'wc_os_crons_field' ); ?><br /><br /> <br />
            <div class="alert alert-info" role="alert">
              <?php _e('You may turn Cron Jobs ON for WooCommerce Orders List Page as well, find the checkbox on settings page.', 'woo-order-splitter'); ?>
            </div>
           
            <div class="wc_os_alert">
                <h4><?php _e('In case your theme is not landing on woocommerce_thankyou so you can try the following options:', 'woo-order-splitter'); ?></h4>
                <h4>curl "<?php echo esc_attr(get_bloginfo('wpurl')); ?>?wc_os_cron_jobs"</h4>
                <h4><?php _e('OR', 'woo-order-splitter'); ?></h4>
                <h4><?php echo esc_attr(get_bloginfo('wpurl')); ?>?wc_os_cron_jobs&order_id=NUMERIC_VALUE</h4>
                
                <br /><br />
                <strong><?php _e('Action Hooks', 'woo-order-splitter'); ?>:</strong>
                <ul>
                	<li>add_action('wc_os_parcels_meta_data', 10, 3);</li>
                </ul>
            </div>
            
            <?php
            $order_statuses = wc_get_order_statuses();

            if(!empty($order_statuses)){

            global $wc_os_cron_settings;
			
            $wc_os_cron_settings['statuses'] = isset($wc_os_cron_settings['statuses'])?$wc_os_cron_settings['statuses']:array();
			$processed_checked = in_array('woocommerce_checkout_order_processed', $wc_os_cron_settings['statuses']);
            ?>
            <div class="wc_os_section_alert">
            	<div class="wc-os-cron-related">
            	<label for="wc-os-last-split-id"><strong><?php _e('Consider Split From Order#', 'woo-order-splitter'); ?></strong> <input type="number" id="wc-os-last-split-id" name="wc-os-last-split-id" value="<?php echo (array_key_exists('wc-os-last-split-id', $wc_os_cron_settings)?$wc_os_cron_settings['wc-os-last-split-id']:''); ?>" /> <small>(<?php _e('If entered a valid order number, it will ignore the previous and will consider upcoming inclusive.', 'woo-order-splitter'); ?>)</small></label>
                </div>
                
                
                <?php _e('By default, following action hook is in use but you can turn ON more hooks according to your requirement.', 'woo-order-splitter'); ?><br />
                <h4 style="color:#ECEC56">add_action('woocommerce_thankyou', 'wc_os_checkout_order_processed', 10, 1);</h4>
                <ul>
                    <li class="wc-status-default"><label for="wc-os-thankyou"><input disabled="disabled" type="checkbox" name="wc_os_statuses[]" id="wc-os-thankyou" value="thankyou" />woocommerce_thankyou (<?php _e('Default', 'woo-order-splitter'); ?>)</label></li>
                    <li class="wc-status-processed"><label for="wc-os-processed"><input <?php checked($processed_checked); ?> type="checkbox" name="wc_os_statuses[]" id="wc-os-processed" value="woocommerce_checkout_order_processed" />woocommerce_checkout_order_processed (<?php _e('Priority', 'woo-order-splitter'); ?>: 2)</label></li>
                    <?php foreach($order_statuses as $slug=>$title){
                        $status = str_replace('wc-', '', $slug);
                        $checked = in_array($status, $wc_os_cron_settings['statuses']);
                        ?>
                        <li class="<?php echo $checked?'wc-status-selected':''; ?>"><label for="wc-os-<?php echo esc_attr($status); ?>"><input <?php checked($checked); ?> type="checkbox" name="wc_os_statuses[]" id="wc-os-<?php echo esc_attr($status); ?>" value="<?php echo esc_attr($status); ?>" />woocommerce_order_status_<?php echo esc_html($status); ?></label></li>
                    <?php } ?>
                </ul>
                <?php }
                ?>
                <strong><?php _e('Recommendation:', 'woo-order-splitter'); ?></strong> <?php _e('Do not use all of above action hooks together. Try one at a time, multiple selection can lead towards unexpected outcomes.', 'woo-order-splitter'); ?>
            </div>

            <input type="hidden" name="wc_os_statuses[]" value="-" />
            <p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>

        </form>


    </div>




    <!--Documentation Tab Content-->
    <div class="sub-tab-content hides documentation pt-4">
    	<strong><?php _e('Plugin options and related topics:', 'woo-order-splitter'); ?></strong>

        <ul>
            <li><?php _e('Hide Parent Order on Thank You Page after Split?', 'woo-order-splitter'); ?>
                <ul>
                    <li><a href="https://ibulb.wordpress.com/2020/11/11/how-to-hide-parent-order-for-all-products-except-selected-ones-on-thankyou-page/" target="_blank"><?php _e('Hide parent order excluding selected products?', 'woo-order-splitter'); ?></a></li>
                    <li><a href="https://ibulb.wordpress.com/2021/04/23/hide-parent-order-force-display-for-a-product/" target="_blank"><?php _e('Hide parent order / force display for a product', 'woo-order-splitter'); ?></a></li>
                    
                </ul>
            </li>
        </ul>
        
        <br />
        <strong><?php _e('Following plugins are tested and ensured compatibility:', 'woo-order-splitter'); ?></strong>

        <ul>
            <li><a href="https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/" target="_blank">WooCommerce PDF Invoices & Packing Slips</a> - <a href="https://androidbubbles.files.wordpress.com/2020/10/woocommerce-pdf-invoices-packing-slips.pdf" target="_blank"><?php _e('PDF', 'woo-order-splitter'); ?></a> - <a href="https://www.youtube.com/embed/Ov7MtpHCZsc" target="_blank"><?php _e('Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></a> - <a href="https://ps.w.org/woo-order-splitter/assets/screenshot-40.png" target="_blank"><?php _e('Screenshot', 'woo-order-splitter'); ?></a> - <a href="https://ps.w.org/woo-order-splitter/assets/screenshot-41.png" target="_blank"><?php _e('Screenshot', 'woo-order-splitter'); ?></a></li>
            <li><a href="https://woocommerce.com/products/product-vendors/" target="_blank">WooCommerce Product Vendors</a> - <a href="https://www.youtube.com/embed/S7EL4z-Kd7k" target="_blank"><?php _e('Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></a> - <a href="https://ps.w.org/woo-order-splitter/assets/screenshot-42.png" target="_blank"><?php _e('Screenshot', 'woo-order-splitter'); ?></a> - <a href="https://ps.w.org/woo-order-splitter/assets/screenshot-43.png" target="_blank"><?php _e('Screenshot', 'woo-order-splitter'); ?></a></li>
            <li><a href="https://wpml.org/documentation/support/sending-emails-with-wpml/" target="_blank">WooCommerce Multilingual</a> - <a href="https://wpml.org/forums/topic/triggering-translated-email/" target="_blank"><?php _e('Triggering Translated Email', 'woo-order-splitter'); ?></a></li>
			<li><a href="https://gumpyguy.wordpress.com/2021/04/03/acf-field-values-how-it-works/" target="_blank">ACF (Advanced Custom Fields)</a> <a href="https://gumpyguy.wordpress.com/2021/04/03/acf-field-values-how-it-works/" target="_blank"><?php _e('How it works?', 'woo-order-splitter'); ?></a> <a href="https://www.youtube.com/embed/vQPe22hj8zU" target="_blank"><i class="fab fa-youtube"></i></a></li>

        </ul>
        
        <br />
        <strong><?php _e('Following meta keys can be retrieved from split orders:', 'woo-order-splitter'); ?></strong>
        <ul>
        	<li>_wos_group_name: <?php _e('Alphabetical group name in which items were splitted on checkout.', 'woo-order-splitter'); ?></li>
        </ul>
        
        <br />
        <strong><?php _e('Following action/filter hooks are available:', 'woo-order-splitter'); ?></strong>
        <ul class="wc-os-hooks-list">
        	<li>
            <code>function <?php echo wc_os_underscore($current_theme); ?>_products_list_name_column_callback($product){<br /><br />
            	echo ' <span style="color:purple">('.$product->get_sku().')</span>';<br />
            }<br /><br />
            add_action('wc_os_products_list_name_column', '<?php echo wc_os_underscore($current_theme); ?>_products_list_name_column_callback', 10, 1);<br />
            
            </code>
            </li>
            <li>
            	<code>function <?php echo wc_os_underscore($current_theme); ?>_os_orders_meta_keys_to_string_callback($str='', $order_id=0){<br /><br />
                    $str .= ',new_meta_key';<br />
                    return $str;<br /><br />
                }<br /><br />
                add_filter('wc_os_orders_meta_keys_to_string', '<?php echo wc_os_underscore($current_theme); ?>_os_orders_meta_keys_to_string_callback', 10, 2);<br />
                </code>
            </li>
        </ul>
                
    </div>

	<div class="sub-tab-content hides speed_optimization_wrapper pt-4">

        <?php

        $wc_os_speed_optimization = get_option('wc_os_speed_optimization', array());



        ?>

        <ul class="wos_speed_optimization mb-4">

            <li>

                <label>
                    <input type="checkbox" class="speed_inputs" name="minified_js" <?php echo checked(array_key_exists('minified_js', $wc_os_speed_optimization) && $wc_os_speed_optimization['minified_js'] == 'true'); ?>>
                    <?php _e('Use minified Javascript', 'woo-order-split') ?>
                </label>

            </li>

            <li>

                <label>
                    <input type="checkbox" class="speed_inputs" name="minified_css" <?php echo checked(array_key_exists('minified_css', $wc_os_speed_optimization) && $wc_os_speed_optimization['minified_css']  == 'true'); ?>>
                    <?php _e('Use minified CSS', 'woo-order-split') ?>
                </label>

            </li>


        </ul>
        
    
        <p>
            <button class="button button-primary"><?php _e('Save Changes', 'woo-order-splitter') ?></button>
            
        </p>

    </div>



</div>