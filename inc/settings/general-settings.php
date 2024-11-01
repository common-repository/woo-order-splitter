<?php
	$subscription_split = in_array('subscription_split', $wc_os_settings['wc_os_additional']);
	
	
?>	
<form class="nav-tab-content tab-general-settings" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

<?php wp_nonce_field( 'wc_os_settings_action', 'wc_os_settings_field' ); ?>





<div class="wc_os_optional" title="<?php _e("Optional",'woo-order-splitter'); ?>">




    <fieldset>



        <ul>
        
        
        <li>
        	<strong><i class="fas fa-robot"></i> <?php _e("Cron Jobs",'woo-order-splitter'); ?> <small>(<?php _e("Optional",'woo-order-splitter'); ?>)</small></strong>

            <ul>
        
                <li <?php echo (array_key_exists('wc_os_cron_shop_order_page', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e("Shop Order Page - Perform scheduled queries on Orders List Page",'woo-order-splitter'); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_cron_shop_order_page" name="wc_os_general_settings[wc_os_cron_shop_order_page]" type="checkbox" value="1" <?php checked(array_key_exists('wc_os_cron_shop_order_page', $wc_os_general_settings)); ?> /><label for="wc_os_cron_shop_order_page"><?php _e("Orders List Page",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>   
                
                <li <?php echo (array_key_exists('wc_os_cron_my_account_page', $wc_os_general_settings)?'class="selected"':''); ?> title='<?php _e("My Account Page - Perform scheduled queries on Customer > My Account Page",'woo-order-splitter'); ?>'>
        
                <input class="wc_os_checkout_options" id="wc_os_cron_my_account_page" name="wc_os_general_settings[wc_os_cron_my_account_page]" type="checkbox" value="1" <?php checked(array_key_exists('wc_os_cron_my_account_page', $wc_os_general_settings)); ?> /><label for="wc_os_cron_my_account_page"><?php _e("My Account Page",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>                   
                
                <li>
                	<a class="wc-os-cron-jobs"><?php _e("Click here for Cron Jobs",'woo-order-splitter'); ?></a>
                </li>      
                  
                                 
            
            </ul>
        
        </li>
        
        <li>
        	<strong><i class="fas fa-project-diagram"></i> <?php _e("Split",'woo-order-splitter'); ?></strong>

            <ul>
        
                <li <?php echo (array_key_exists('wc_os_auto_forced', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e("On checkout page, on place order action, automatically split order into child orders.",'woo-order-splitter'); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_auto_forced" name="wc_os_general_settings[wc_os_auto_forced]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_auto_forced', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_auto_forced"><?php _e("Auto Split",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>         
                
                <li class="<?php echo (array_key_exists('wc_os_effect_parent', $wc_os_general_settings)?'selected':''); ?>" title="<?php _e("Remove splitted items from parent order. This option will not work with Quantity Split method.",'woo-order-splitter'); ?>">
        
                <input <?php disabled(in_array($wc_os_settings['wc_os_ie'], array('quantity_split'))); ?> class="wc_os_checkout_options" id="wc_os_effect_parent" name="wc_os_general_settings[wc_os_effect_parent]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_effect_parent', $wc_os_general_settings) && !in_array($wc_os_settings['wc_os_ie'], array('quantity_split'))?'checked="checked"':''); ?> /><label for="wc_os_effect_parent"><?php _e("Remove Items from Parent Order on Split?",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>
        
                </li>    
                
                <li class="wc_os_threshold_option <?php echo (array_key_exists('wc_os_threshold', $wc_os_general_settings)?'selected':''); ?>" title="<?php _e("Split threshold value for items in child orders?",'woo-order-splitter'); ?>">
        
                <input <?php disabled(!in_array($wc_os_settings['wc_os_ie'], array('group_cats'))); ?> class="wc_os_checkout_options" id="wc_os_threshold" name="wc_os_general_settings[wc_os_threshold]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_threshold', $wc_os_general_settings) && in_array($wc_os_settings['wc_os_ie'], array('group_cats'))?'checked="checked"':''); ?> /><label for="wc_os_threshold"><?php _e("Maximum number of items per child order?",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>
                
                <input type="number" value="<?php echo (array_key_exists('wc_os_threshold_value', $wc_os_general_settings)?$wc_os_general_settings['wc_os_threshold_value']:0); ?>" name="wc_os_general_settings[wc_os_threshold_value]" />
        
                </li>   
                                 
            
            </ul>
        
        </li>
        
        
        <li>
        	<strong><?php _e("Show / Hide",'woo-order-splitter'); ?> (<?php _e("Orders",'woocommerce'); ?>)</strong>

            <ul> 
                            
                <li <?php echo (array_key_exists('wc_os_order_comments_off', $wc_os_general_settings)?'class="selected"':''); ?>>
        
                <input class="wc_os_checkout_options" id="wc_os_order_comments_off" name="wc_os_general_settings[wc_os_order_comments_off]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_order_comments_off', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_comments_off"><?php _e("Display Order Notes Fields on Checkout Page",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>
        
                </li>
        
                <li <?php echo (array_key_exists('wc_os_billing_off', $wc_os_general_settings)?'class="selected"':''); ?>>
        
                <input class="wc_os_checkout_options" id="wc_os_billing_off" name="wc_os_general_settings[wc_os_billing_off]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_billing_off', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_billing_off"><?php _e("Display Billing Details Form on Checkout Page",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>
    
                </li>
        
                <li <?php echo (array_key_exists('wc_os_shipping_off', $wc_os_general_settings)?'class="selected"':''); ?>>
                
                <input class="wc_os_checkout_options" id="wc_os_shipping_off" name="wc_os_general_settings[wc_os_shipping_off]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_shipping_off', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_shipping_off"><?php _e("Display Shipping Details Form on Checkout Page",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>    
    
                </li>            
            
                <li <?php echo (array_key_exists('wc_os_display_child', $wc_os_general_settings)?'class="selected"':''); ?>>    
        
                <input class="wc_os_checkout_options" id="wc_os_display_child" name="wc_os_general_settings[wc_os_display_child]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_display_child', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_display_child"><?php _e("Hide Child Order on Thank You Page after Split?",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>    
                
                </li>
                
                <li>    
        
                <textarea class="wc_os_checkout_options" id="wc_os_display_child_number" name="wc_os_general_settings[wc_os_display_child_number]" title="<?php echo $default_child_number = __("Optional message to inform your customers if split happens that [NUMBER_OF_CHILD_ORDERS] parcels, packages, orders they will receive instead of one.",'woo-order-splitter'); ?>" placeholder="<?php echo esc_attr($default_child_number); ?>"><?php echo esc_textarea((array_key_exists('wc_os_display_child_number', $wc_os_general_settings))?$wc_os_general_settings['wc_os_display_child_number']:__('You will receive [NUMBER_OF_CHILD_ORDERS] packages.', 'woo-order-splitter')); ?></textarea></label>    
                
                </li>                     
                                
                <li <?php echo (array_key_exists('wc_os_display_parent', $wc_os_general_settings)?'class="selected"':''); ?>>    
        
                <input class="wc_os_checkout_options" id="wc_os_display_parent" name="wc_os_general_settings[wc_os_display_parent]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_display_parent', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_display_parent"><?php _e("Hide Parent Order on Thank You Page after Split?",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>    
        
                </li>       
                <li class="<?php echo (array_key_exists('wc_os_order_items_count_column', $wc_os_general_settings)?'selected':''); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_order_items_count_column" name="wc_os_general_settings[wc_os_order_items_count_column]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_order_items_count_column', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_items_count_column"><?php _e('Admin > Orders List > "Items Count" Column','woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>    
        
                </li>
        
                <li class="<?php echo (array_key_exists('wc_os_order_splitf_column', $wc_os_general_settings)?'selected':''); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_order_splitf_column" name="wc_os_general_settings[wc_os_order_splitf_column]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_order_splitf_column', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_splitf_column"><?php _e('Admin > Orders List > "Split From" Column','woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>    
        
                </li>
        
                <li class="<?php echo (array_key_exists('wc_os_order_clonef_column', $wc_os_general_settings)?'selected':''); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_order_clonef_column" name="wc_os_general_settings[wc_os_order_clonef_column]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_order_clonef_column', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_clonef_column"><?php _e('Admin > Orders List > "Parent Order" Column','woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>      
                
                <li class="<?php echo (array_key_exists('wc_os_extend_groups', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>" title="<?php _e("e.g. Grouped Categories Split Method > Turn ON unlimited grouping options",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_extend_groups" name="wc_os_general_settings[wc_os_extend_groups]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_extend_groups', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_extend_groups"><?php _e("Extend List of Groups",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>
                
                <li class="<?php echo (array_key_exists('wc_os_view_order_button', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>" title="<?php _e("View Order button will appear adjacent to Add Order button on edit order page.",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_view_order_button" name="wc_os_general_settings[wc_os_view_order_button]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_view_order_button', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_view_order_button"><?php _e("View Order Button",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>                

                <li class="wc_os_input_options wc_os_extend_groups <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>">

                    <?php

                        $wc_os_limit_alphabets = (array_key_exists('wc_os_limit_alphabets', $wc_os_general_settings) ? $wc_os_general_settings['wc_os_limit_alphabets'] : '');

                    ?>

                    <label for="wc_os_limit_alphabets"><?php _e("Limit Alphabets in groups (by default 26)",'woo-order-splitter'); ?></label>
                    <input class="wc_os_input_options" min="1" max="702" id="wc_os_limit_alphabets" name="wc_os_general_settings[wc_os_limit_alphabets]" type="number" value="<?php echo esc_attr($wc_os_limit_alphabets); ?>"  />

                </li>                
                
                
                
            
            </ul>
		
        </li>
        
        <?php if($subscription_split): ?>
        <li>
        
        	<strong><?php _e("Show / Hide",'woo-order-splitter'); ?> (<?php _e("Subscriptions",'woocommerce'); ?>)</strong>
            
			<ul>                
                
                
				<li class="<?php echo (array_key_exists('wc_os_subscription_items_count_column', $wc_os_general_settings)?'selected':''); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_subscription_items_count_column" name="wc_os_general_settings[wc_os_subscription_items_count_column]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_subscription_items_count_column', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_subscription_items_count_column"><?php _e('Admin > Subscription List > "Items Count" Column','woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>    
        
                </li>
        
                <li class="<?php echo (array_key_exists('wc_os_subscription_splitf_column', $wc_os_general_settings)?'selected':''); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_subscription_splitf_column" name="wc_os_general_settings[wc_os_subscription_splitf_column]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_subscription_splitf_column', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_subscription_splitf_column"><?php _e('Admin > Subscription List > "Split From" Column','woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>    
        
                </li>
        
                <li class="<?php echo (array_key_exists('wc_os_subscription_clonef_column', $wc_os_general_settings)?'selected':''); ?>">
        
                <input class="wc_os_checkout_options" id="wc_os_subscription_clonef_column" name="wc_os_general_settings[wc_os_subscription_clonef_column]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_subscription_clonef_column', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_subscription_clonef_column"><?php _e('Admin > Subscription List > "Parent Order" Column','woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>
        
                </li>      
         
        		</ul>        
        </li>
        <?php endif; ?>
         <li>
        	<strong><?php _e("Before Split",'woo-order-splitter'); ?></strong>

            <ul>          

                <li <?php echo (array_key_exists('wc_os_auto_clone', $wc_os_general_settings)?'class="selected"':''); ?>>
        
                    <input class="wc_os_checkout_options" id="wc_os_auto_clone" name="wc_os_general_settings[wc_os_auto_clone]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_auto_clone', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_auto_clone"><?php _e("Auto Clone",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></label>


<div class="wc_os_auto_clone_status_wrapper" <?php echo (array_key_exists('wc_os_auto_clone', $wc_os_general_settings)?'':'style="display:none;"'); ?>>

    <label for="wc_os_auto_clone_status">
        <?php _e('Status for auto cloned order', 'woo-order-splitter'); ?>
    </label>
    <select name="wc_os_general_settings[wc_os_auto_clone_status]" id="wc_os_auto_clone_status">


        <?php

            $auto_clone_selected_status = wc_os_get_auto_clone_status();
            if(function_exists('wc_os_status_options_html')){
                wc_os_status_options_html($auto_clone_selected_status);
            }

        ?>

    </select>
</div>        
                </li>
                                
        
                <li <?php echo (array_key_exists('wc_os_customer_permission', $wc_os_general_settings)? 'class="selected"' : ''); ?> title="<?php _e('This permission works only if Auto Split is on', 'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_customer_permission" name="wc_os_general_settings[wc_os_customer_permission]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_customer_permission', $wc_os_general_settings)  ? 'checked="checked"' : ''); ?> /><label for="wc_os_customer_permission"><?php _e("Customer permission for split (Checkout Page)", 'woo-order-splitter'); ?> <strong><?php _e("Off", 'woo-order-splitter'); ?></strong>/<strong><?php _e("On", 'woo-order-splitter'); ?></strong></label>
        
                </li>
        
                <li id="wc_os_customer_permission_text_wrapper" <?php echo (!array_key_exists('wc_os_customer_permission', $wc_os_general_settings)? 'style="display:none"' : ''); ?>>
                
        
        
                    <div class="wc_os_customer_permission_text"><span><?php echo __('Caption: No Split', 'woo-order-splitter'); ?></span><input type="text" class="wc_os_checkout_options" id="wc_os_customer_permission_text_no" name="wc_os_general_settings[wc_os_customer_permission_text_no]"  style="width: 100%" value="<?php echo esc_textarea(trim((array_key_exists('wc_os_customer_permission_text_no', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_customer_permission_text_no'])? $wc_os_general_settings['wc_os_customer_permission_text_no'] : __('Continue Single Order', 'woo-order-splitter')))) ;?>" /></div>
                    
                    <div class="wc_os_customer_permission_text"><span><?php echo __('Caption: Split Order', 'woo-order-splitter'); ?></span><input type="text" class="wc_os_checkout_options" id="wc_os_customer_permission_text_yes" name="wc_os_general_settings[wc_os_customer_permission_text_yes]"  style="width: 100%" value="<?php echo esc_textarea(trim((array_key_exists('wc_os_customer_permission_text_yes', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_customer_permission_text_yes'])? $wc_os_general_settings['wc_os_customer_permission_text_yes'] : __('Split Order', 'woo-order-splitter')))) ;?>" /></div>
        
                </li>        
                <li class="<?php echo (array_key_exists('wc_os_packages_overview', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>" title="<?php _e("Distribution of items in packages will appear for review",'woo-order-splitter'); ?>">
        
                    <input <?php disabled(!$wc_os_pro); ?> class="wc_os_checkout_options" id="wc_os_packages_overview" name="wc_os_general_settings[wc_os_packages_overview]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_packages_overview', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_packages_overview"><?php _e("Split Overview on Checkout Page",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong> <a href="https://www.youtube.com/embed/ol_U6ghhJSk" target="_blank"><small><?php _e('Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></small></a></label>
        
                </li>   
                
                <li class="<?php echo (array_key_exists('wc_os_packages_overview', $wc_os_general_settings)?'':'hide'); ?> <?php echo (array_key_exists('wc_os_packages_price', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?> wc_os_packages_overview_items" title="<?php _e("Hide Package Price In Split Overview",'woo-order-splitter'); ?>">
        
                    <input <?php disabled(!$wc_os_pro); ?> class="wc_os_checkout_options" id="wc_os_packages_price" name="wc_os_general_settings[wc_os_packages_price]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_packages_price', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_packages_price"><?php _e("Hide Package Price In Split Overview",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label>
        
                </li>   
                
                <li class="<?php echo (array_key_exists('wc_os_packages_overview', $wc_os_general_settings)?'':'hide'); ?> <?php echo (array_key_exists('wc_os_vendor_name', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?> wc_os_packages_overview_items" title="<?php _e("Display Vendor Name In Split Overview",'woo-order-splitter'); ?>">
        
                    <input <?php disabled(!$wc_os_pro); ?> class="wc_os_checkout_options" id="wc_os_vendor_name" name="wc_os_general_settings[wc_os_vendor_name]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_vendor_name', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_vendor_name"><?php _e("Display Vendor Name In Split Overview",'woo-order-splitter'); ?> <strong><?php _e("Yes",'woo-order-splitter'); ?></strong>/<strong><?php _e("No",'woo-order-splitter'); ?></strong> </label>
        
                </li>                                   
                <li class="<?php echo (array_key_exists('wc_os_shipping_methods', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>" title="<?php echo __("Customer will choose to pay for each Child Order shipment separately.",'woo-order-splitter')." ".__("It will work if customer permission will be enabled.",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_shipping_methods" name="wc_os_general_settings[wc_os_shipping_methods]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_shipping_methods', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_shipping_methods"><?php _e("Shipping Method for Child Orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label>
        
                </li>                
                             
        	</ul>
        </li>    
                
        <li>
        	<strong><?php _e("After Split",'woo-order-splitter'); ?></strong>

            <ul>      
        		
                <li style="display:none" <?php echo (array_key_exists('wc_os_reduce_stock', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e("This option will work better if items are completely moved to child orders not partially as quantity split does",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_reduce_stock" name="wc_os_general_settings[wc_os_reduce_stock]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_reduce_stock', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_reduce_stock"><?php _e("Reduce stock from all orders including child orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>
        
                </li>  

                <li <?php echo (array_key_exists('wc_os_order_total', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e("Display Summation of Parent and Child Orders on Order Receipt",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_order_total" name="wc_os_general_settings[wc_os_order_total]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_order_total', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_total"><?php _e("Display Summation of Parent and Child Orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>
        
                </li>        
        
                <li <?php echo ($wc_os_shipping_cost?'class="selected"':''); ?> title="<?php _e("Shipping details like shipped to Name, Address and Contact etc.",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_shipping_cost" name="wc_os_general_settings[wc_os_shipping_cost]" type="checkbox" value="1" <?php echo ($wc_os_shipping_cost?'checked="checked"':''); ?> /><label for="wc_os_shipping_cost"><?php _e("Add Shipping Details in Split Orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> <a href="https://www.youtube.com/embed/5yKoAWYQMgY" target="_blank"><small><?php _e('Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></small></a></label>
        
                </li>
        
                <li title="<?php _e("It is recommended to keep order notes OFF",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_customer_notes" name="wc_os_general_settings[wc_os_customer_notes]" type="checkbox" value="1" <?php checked(array_key_exists('wc_os_customer_notes', $wc_os_general_settings)); ?> /><label for="wc_os_customer_notes"><?php _e("Add Customer Notes to Split Orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label>
        
                </li>
                
                <li <?php echo (array_key_exists('wc_os_tax_cost', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e("Cloning of tax details from Parent Order",'woo-order-splitter'); ?>" style="display:none">
        
                    <input class="wc_os_checkout_options" id="wc_os_tax_cost" name="wc_os_general_settings[wc_os_tax_cost]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_tax_cost', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_tax_cost"><?php _e("Add Tax Details in Split Orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> <a href="https://www.youtube.com/embed/C_EDYXy3ZMw" target="_blank"><small><?php _e('Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></small></a></label>
        
                </li>
        
                <li <?php echo (array_key_exists('wc_os_backorder_mail_notification', $wc_os_general_settings)?'class="selected"':''); ?>>
        
                    <input class="wc_os_checkout_options" id="wc_os_backorder_mail_notification" name="wc_os_general_settings[wc_os_backorder_mail_notification]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_backorder_mail_notification', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_backorder_mail_notification" title="<?php echo __( "Don't send email notifications when a backorder is made.", 'woo-order-splitter' ); ?>"><?php echo __( 'Disable backorder email notifications', 'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong></a></label>
        
                </li>
        
                <li class="<?php echo (get_splitted_order_title_status()?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>" title="<?php _e("Child Orders will be masked with Parent Order Sequence with a Postfix",'woo-order-splitter'); ?>">
        
                    <input class="wc_os_checkout_options" id="wc_os_order_title_splitted" name="wc_os_general_settings[wc_os_order_title_splitted]" type="checkbox" value="1" <?php echo (get_splitted_order_title_status()?'checked="checked"':''); ?> /><label for="wc_os_order_title_splitted"><?php _e("Mask Child Orders with a Sequential Format",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong> <a href="https://www.youtube.com/embed/6QKnpC2xcoM" target="_blank"><small><?php _e('Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></small></a></label>
        
                </li>

                <li class="<?php echo (array_key_exists('wc_os_show_customer_name_list', $wc_os_general_settings)?'selected':''); ?> <?php echo (!$wc_os_pro?'wc_os_premium':''); ?>" title="<?php _e("Customer column will be displayed in order list",'woo-order-splitter'); ?>">

                    <input class="wc_os_checkout_options" id="wc_os_show_customer_name_list" name="wc_os_general_settings[wc_os_show_customer_name_list]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_show_customer_name_list', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_show_customer_name_list"><?php _e("Show customer column in order list",'woo-order-splitter'); ?> <strong><?php _e("Off",'woo-order-splitter'); ?></strong>/<strong><?php _e("On",'woo-order-splitter'); ?></strong> </label>

                </li>
                
                <li <?php echo (array_key_exists('wc_os_remove_fees_from_child', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e('Remove fees from child order','woo-order-splitter'); ?>">
                
                	<input class="wc_os_checkout_options" id="wc_os_remove_fees_from_child" name="wc_os_general_settings[wc_os_remove_fees_from_child]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_remove_fees_from_child', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_remove_fees_from_child"><?php _e("Remove Fees from Child Order",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong></label>

                </li>   
                
                <li <?php echo (array_key_exists('wc_os_remove_price_from_child', $wc_os_general_settings)?'class="selected"':''); ?> title="<?php _e('Remove Price from child order','woo-order-splitter'); ?>">
                
                	<input class="wc_os_checkout_options" id="wc_os_remove_price_from_child" name="wc_os_general_settings[wc_os_remove_price_from_child]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_remove_price_from_child', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_remove_price_from_child"><?php _e("Remove Price from Child Order",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label>
                
                </li>                
                                
            </ul>
            
        </li>          
		<li>
        	<strong><?php _e("Consolidate/Combine/Merge Orders",'woo-order-splitter'); ?></strong>

            <ul>            
            	<li <?php echo (array_key_exists('wc_os_merge_shipping', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wc_os_merge_shipping" name="wc_os_general_settings[wc_os_merge_shipping]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_merge_shipping', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_merge_shipping"><?php _e("Add Shipping Fee",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
                <li <?php echo (array_key_exists('wc_os_merge_tax', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wc_os_merge_tax" name="wc_os_general_settings[wc_os_merge_tax]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_merge_tax', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_merge_tax"><?php _e("Add Tax",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
				<li data-id="remove_combined" title="<?php _e('Enable original order removal on consolidation', 'woo-order-splitter'); ?>" <?php echo (array_key_exists('wc_os_remove_combined', $wc_os_general_settings)?'class="selected"':''); ?>><input <?php disabled(!$wc_os_pro); ?> class="wc_os_checkout_options" id="wc_os_remove_combined" name="wc_os_general_settings[wc_os_remove_combined]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_remove_combined', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_remove_combined"><?php _e("Remove Original/Parent Orders",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label> <a href="https://www.youtube.com/embed/qrZMZAuv-VU" target="_blank" title="<?php echo __('Watch Tutorial', 'woo-order-splitter').' - '.__('Consolidated/Merged/Combined orders should be removed after action.', 'woo-order-splitter'); ?>"> <i class="fab fa-youtube"></i></a></li>
                
                
			</ul>
		</li>
		<li>
        	<strong><?php _e("Assets (Optional)",'woo-order-splitter'); ?></strong>

            <ul>            
            	<li <?php echo (array_key_exists('wc_os_fa', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wc_os_fa" name="wc_os_general_settings[wc_os_fa]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_fa', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_fa"><?php _e("Disable Font Awesome",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
                <li <?php echo (array_key_exists('wc_os_bs', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wc_os_bs" name="wc_os_general_settings[wc_os_bs]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_bs', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_bs"><?php _e("Disable Bootstrap",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
			</ul>
		</li>
<?php if($wc_os_wcfm_installed): ?>        
        <li>
        	<strong><?php _e("WCFM Marketplace",'woo-order-splitter'); ?> <a href="https://ps.w.org/woo-order-splitter/assets/screenshot-54.png" target="_blank"><?php _e('How it works?', 'woo-order-splitter'); ?> <i class="fas fa-image"></i></a></strong>

            <ul>            
            	<li <?php echo (array_key_exists('wcfm_order_status', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wcfm_order_status" name="wc_os_general_settings[wcfm_order_status]" type="checkbox" value="1" <?php echo (array_key_exists('wcfm_order_status', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wcfm_order_status"><?php _e("Update Order Status",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
                <li <?php echo (array_key_exists('wcfm_shipping_status', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wcfm_shipping_status" name="wc_os_general_settings[wcfm_shipping_status]" type="checkbox" value="1" <?php echo (array_key_exists('wcfm_shipping_status', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wcfm_shipping_status"><?php _e("Update Shipping Status",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
                <li <?php echo (array_key_exists('wcfm_commission_status', $wc_os_general_settings)?'class="selected"':''); ?>><input class="wc_os_checkout_options" id="wcfm_commission_status" name="wc_os_general_settings[wcfm_commission_status]" type="checkbox" value="1" <?php echo (array_key_exists('wcfm_commission_status', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wcfm_commission_status"><?php _e("Update Commission Status",'woo-order-splitter'); ?> <strong><?php _e("No",'woo-order-splitter'); ?></strong>/<strong><?php _e("Yes",'woo-order-splitter'); ?></strong> </label></li>
			</ul>
		</li>
<?php endif; ?>
        <li></li><li></li><li></li>



        <li><a href="https://www.youtube.com/embed/wjClFEeYEzo" target="_blank"><?php _e('Watch Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></a></li>          

        

        <li><a href="https://www.youtube.com/embed/tOT4l7_GCIw" target="_blank"><?php _e('Watch Video Tutorial', 'woo-order-splitter'); ?> <i class="fab fa-youtube"></i></a></li>            

        <li></li>



        </ul>



    </fieldset>





</div>





<br />

<div class="wc_os_notes"></div>





<table class="wos_general">

<tbody>

<?php	

		

		$cloning = in_array('cloning', $wc_os_settings['wc_os_additional']);



?>

<tr>

<td colspan="2"><?php _e('Order cloning is an for bulk action dropdown and orders list.', 'woo-order-splitter'); ?></td>

</tr>

<tr>

<td><input id="wip-cloning" <?php checked($cloning); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="cloning" /></td>

<td><label for="wip-cloning"><?php _e('Enable order duplication or cloning', 'woo-order-splitter'); ?></label>

</td>

</tr>





<?php	

		

		$disable_split = in_array('split', $wc_os_settings['wc_os_additional']);
		

		$split_lock = (isset($wc_os_settings['wc_os_additional']['split_lock'])?$wc_os_settings['wc_os_additional']['split_lock']:array());
		$split_lock = is_array($split_lock)?$split_lock:array();
	
		$shipped_status_value = (isset($wc_os_settings['wc_os_additional']['shipped_status'])?$wc_os_settings['wc_os_additional']['shipped_status']:'');


		$split_status_value = wc_os_order_split_status_action();

?>

<tr>

<td colspan="2">

<div class="split_lock_bars">

<strong><?php _e('Split ON/OFF', 'woo-order-splitter'); ?></strong><br />

&nbsp;&nbsp;&nbsp;<input id="wip-split" <?php checked($disable_split); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="split" />

<label for="wip-split"><?php _e('Disable split order option', 'woo-order-splitter'); ?></label>
<br />

&nbsp;&nbsp;&nbsp;<input id="wips-split" <?php checked($subscription_split); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="subscription_split" />

<label for="wips-split"><?php _e('Enable subscription split option', 'woo-order-splitter'); ?></label>


<?php if(!$disable_split): 


?>



<div class="inner_split_lock">

&nbsp;&nbsp;&nbsp;<input id="wip-split-refresh" <?php checked(in_array('split-refresh', $wc_os_settings['wc_os_additional'])); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="split-refresh" />

<label for="wip-split-refresh"><?php _e('On Split, refresh orders list', 'woo-order-splitter'); ?></label><br />



&nbsp;&nbsp;&nbsp;<input id="wip-order-refresh" <?php checked(in_array('order-refresh', $wc_os_settings['wc_os_additional'])); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="order-refresh" />

<label for="wip-order-refresh"><?php _e('Do not refresh "Thank You" page to display child orders on split', 'woo-order-splitter'); ?></label>

<br /><br />



<strong class="split-lock"><?php _e('Split lock, do not split other than this status', 'woo-order-splitter'); ?></strong>

<br />

&nbsp;&nbsp;&nbsp;<select name="wc_os_settings[wc_os_additional][split_lock][]" multiple="multiple">



<?php foreach($wc_os_order_statuses_keys as $order_status){ $order_status = str_replace('wc-','', $order_status); ?>

<option value="<?php echo esc_attr($order_status); ?>" <?php selected(in_array($order_status, $split_lock)); ?>><?php echo __('Split', 'woo-order-splitter').' '.str_replace('WC-', '', (strtoupper($order_status))).' '.__('Orders', 'woocommerce');//.' '.__('Only', 'woo-order-splitter'); ?></option>

<?php } ?>

</select>

</div>



<strong class="split-status"><?php _e('Splitted order(s) status', 'woo-order-splitter'); ?></strong>

<br />

&nbsp;&nbsp;&nbsp;<select name="wc_os_settings[wc_os_additional][split_status_lock]">

<option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

<?php foreach($wc_os_order_statuses_keys as $order_status){ ?>

<option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status==$split_status_value); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

<?php } ?>

</select>
<br /><br />
<code><small>add_filter('wc_os_split_order_status_logic_hook', '<?php echo $wc_os_current_theme; ?>_order_status_logic_func', 20, 3);</small> <i class="fas fa-lightbulb"></i></code>


<?php endif; ?>


<?php
	$wc_os_shipping_platforms = get_option('wc_os_shipping_platforms');
	$wc_os_shipping_platforms = (is_array($wc_os_shipping_platforms)?$wc_os_shipping_platforms:array());
	if(in_array('shipstation', $wc_os_shipping_platforms)){
		
		$ss = '<span style="font-size: 16px;font-weight: bold;font-style: italic;float: right;margin: 0 18px 0px 0;position: relative;top: -4px;">ShipStati<i class="fas fa-cog" style="color:#92c43e"></i>n</span>';
	
?>	
<strong class="shipped-status"><?php _e('Shipped order(s) status', 'woo-order-splitter'); ?><?php echo $ss; ?></strong>

<br />

&nbsp;&nbsp;&nbsp;<select name="wc_os_settings[wc_os_additional][shipped_status]">

<option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

<?php foreach($wc_os_order_statuses_keys as $order_status){ ?>

<option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status==$shipped_status_value); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

<?php } ?>

</select>

<?php

	}
	
?>	

</div>

</td>

</tr>

<?php	

		

		$removal = in_array('removal', $wc_os_settings['wc_os_additional']);

		$removal_lock = (isset($wc_os_settings['wc_os_additional']['removal_lock'])?$wc_os_settings['wc_os_additional']['removal_lock']:'');

		$removal_lock_split = (isset($wc_os_settings['wc_os_additional']['removal_lock_split'])?$wc_os_settings['wc_os_additional']['removal_lock_split']:'');

		$empty_order_status = (isset($wc_os_settings['wc_os_additional']['empty_order_status'])?$wc_os_settings['wc_os_additional']['empty_order_status']:'');


?>













<tr>

<td colspan="2">

<div class="order_removal_bars">

<strong><?php _e('Order removal is an optional feature, once order has been splitted so original order can be removed.', 'woo-order-splitter'); ?></strong><br />

&nbsp;&nbsp;&nbsp;<input id="wip-removal" <?php checked($removal); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="removal" />

<label for="wip-removal"><?php _e('Enable original order removal on splitting', 'woo-order-splitter'); ?></label>

<?php if(!$removal): 

$wc_os_order_statuses = wc_get_order_statuses(); 

$wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);

?>

<div class="inner_removal_lock">

<br /><br />

<strong><?php _e('Alternatively, orginal order can be updated with a different available order status.', 'woo-order-splitter'); ?></strong>

<br />

<strong class="single_removal"><?php echo __('Single Order Case', 'woo-order-splitter'); ?>: <small>(<?php echo __('When there is no splitting and parent order remains as it is.', 'woo-order-splitter'); ?>)</small></strong> <br /><select name="wc_os_settings[wc_os_additional][removal_lock]">

<option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

<?php foreach($wc_os_order_statuses_keys as $order_status){ ?>

<option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status==$removal_lock); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

<?php } ?>

</select>

    <?php if($wc_os_pro): ?>

    <span class="wc_os_rules_case">

        <?php

            $wc_os_rules = (function_exists('wc_os_get_rules_sorted')?wc_os_get_rules_sorted():array());
            $rule_main_title = empty($wc_os_rules) ? __('No rule defined', 'woo-order-splitter') : __('Defined Rules', 'woo-order-splitter');
            $check_title = __('Use rule based status when order not split and there is only single item in order.', 'woo-order-splitter');
        ?>

        <label for="wo_os_rule_switch">
            <?php _e('Rule based status (Single Item Case)', 'woo-order-splitter'); ?>
            <input type="checkbox" title="<?php echo esc_attr($check_title); ?>" id="wo_os_rule_switch" name="wc_os_general_settings[wo_os_rule_switch]" <?php echo checked(array_key_exists('wo_os_rule_switch', $wc_os_general_settings)); ?>>
        </label>
        <select>
            <option value=""><?php echo esc_html($rule_main_title); ?></option>
            <?php

                if(!empty($wc_os_rules)){

                    foreach($wc_os_rules as $rule_key => $rule_value){

                        if(!empty($rule_value)){

                            foreach($rule_value as $r_key => $r_value){

                                $rule_title = $rule_key. ' > '. $r_key .' > '.$r_value;
                                echo "<option disabled>$rule_title</option>";

                            }
                        }


                    }
                }
            ?>

        </select>
        
        <a data-target="advanced_tab" data-sub_target="wc_os_rules"><?php _e('Configure', 'woo-order-splitter') ?></a>

    </span>

    <?php endif; ?>
<br /><br />


<strong class="split_removal"><?php echo __('Split Order Case', 'woo-order-splitter'); ?>: <small>(<?php echo __('When a split happens and child orders are generated.', 'woo-order-splitter'); ?>)</small></strong> <br /><select name="wc_os_settings[wc_os_additional][removal_lock_split]">

<option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

<?php foreach($wc_os_order_statuses_keys as $order_status){ ?>

<option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status==$removal_lock_split); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

<?php } ?>

<option value="trash" <?php selected('trash'==$removal_lock_split); ?>><?php echo __('Trash', 'woocommerce'); ?> (<?php echo __('Empty Order', 'woo-order-splitter'); ?>)</option>


</select>

<div class="wos_empty_order_wrapper">

<strong>
    <?php echo __('Empty Order Case', 'woo-order-splitter'); ?>: <small>(<?php echo __('When a split happens and original order left with no items in it.', 'woo-order-splitter'); ?>)</small>
</strong>
    <br />
<select name="wc_os_settings[wc_os_additional][empty_order_status]">

    <option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

    <?php foreach($wc_os_order_statuses_keys as $order_status){ ?>

        <option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status==$empty_order_status); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

    <?php } ?>
    
    <option value="trash" <?php selected('trash'==$empty_order_status); ?>><?php echo __('Trash', 'woocommerce'); ?> (<?php echo __('Empty Order', 'woo-order-splitter'); ?>)</option>

</select>

</div>


</div>

<?php endif; ?>

</div>

</td>

</tr>












</tbody>

</table>	



<input type="hidden" name="wc_os_settings[wc_os_additional][]" value="0" />

<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>







</form>