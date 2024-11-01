<?php

	use \Automattic\WooCommerce\Admin\API\Reports\Cache as ReportsCache;
	use Automattic\WooCommerce\Admin\API\Reports\Coupons\DataStore as CouponsDataStore;
	use Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore as OrdersStatsDataStore;
	use Automattic\WooCommerce\Admin\API\Reports\Products\DataStore as ProductsDataStore;
	use Automattic\WooCommerce\Admin\API\Reports\Taxes\DataStore as TaxesDataStore;
	

	add_action('wc_os_after_group_category_headings', 'wc_os_after_group_category_headings_callback');
	
	function wc_os_after_group_category_headings_callback(){
			global $wc_os_settings;
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			
			$wc_os_settings['wc_os_cats'] = (isset($wc_os_settings['wc_os_cats']) && is_array($wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']:array());
			$wc_os_settings['wc_os_cats']['group_cats'] = (isset($wc_os_settings['wc_os_cats']['group_cats']) && is_array($wc_os_settings['wc_os_cats']['group_cats'])?$wc_os_settings['wc_os_cats']['group_cats']:array());
		
			$selected_group = array_keys($wc_os_settings['wc_os_cats']['group_cats']);
		
			global $wpdb;
	
				$results_query = "SELECT p.ID,pm.meta_key FROM `" . $wpdb->posts . "` p LEFT JOIN `" . $wpdb->postmeta . "` pm ON p.ID=pm.post_id WHERE p.post_type = '$wc_os_get_post_type_default' AND pm.meta_key!='NULL' GROUP BY pm.meta_key ";				
				$orders_unique_meta_keys = $wpdb->get_results($results_query);
				
				$shipping_items = "SELECT p.order_item_id,p.order_item_name FROM `".$wpdb->prefix."woocommerce_order_items` p WHERE p.order_item_type = 'shipping' GROUP BY p.order_item_name ORDER BY p.order_item_name ASC";
				$shipping_items_unique_keys = $wpdb->get_results($shipping_items);
				
				
				$line_items = "SELECT p.order_item_id,pm.meta_key FROM `".$wpdb->prefix."woocommerce_order_items` p LEFT JOIN `".$wpdb->prefix."woocommerce_order_itemmeta` pm ON p.order_item_id=pm.order_item_id WHERE p.order_item_type = 'line_item' AND pm.meta_key!='NULL' GROUP BY pm.meta_key ";
				$line_items_unique_meta_keys = $wpdb->get_results($line_items);
				
				
				$shipping_items = "SELECT p.order_item_id,pm.meta_key FROM `".$wpdb->prefix."woocommerce_order_items` p LEFT JOIN `".$wpdb->prefix."woocommerce_order_itemmeta` pm ON p.order_item_id=pm.order_item_id WHERE p.order_item_type = 'shipping' AND pm.meta_key!='NULL' GROUP pm.BY meta_key ";
				$shipping_items_unique_meta_keys = array();//$wpdb->get_results($shipping_items);

		
		?>
        	<a class="wc_os_group_cat_meta_switch button button-secondary"><?php _e('Click here to manage order meta keys for each category group after split', 'woo-order-splitter'); ?></a>
			<div class="wc_os_group_cat_meta_wrapper">
            	<p><?php _e('You may select order meta keys to be cloned into child orders after split action. Select a group and allow/disallow meta keys from the following. Leave blank for all allowed.', 'woo-order-splitter'); ?></p>
				<table style="width: 100%" align="left">
					<tr>
					<td valign="top">
						<strong><?php _e('Category Groups', 'woo-order-splitter'); ?> (<?php _e('Active', 'woo-order-splitter'); ?>):</strong><br /><br />


						<select class="wc_os_selected_group">
							<?php foreach($selected_group as $v): 
								
								if($v === 0){ continue;}
								$k = strtoupper($v);
								
							?>
							<option  value="<?php echo esc_attr($v); ?>"><?php echo esc_html($k); ?></option>
							<?php endforeach; ?>
						</select>
	
					</td>
					
					<td valign="top" align="left">
						<strong><?php _e('Available Order Meta Keys', 'woo-order-splitter'); ?> (<?php _e('Hold ctrl for multiple select', 'woo-order-splitter'); ?>):</strong><br /><br />


					<select multiple="multiple" size="7" class="wc_os_selected_meta">
						<option value="none"><?php _e('None', 'woo-order-splitter'); ?></option>
                        

						
                        
                        <?php if (!empty($line_items_unique_meta_keys)) : asort($line_items_unique_meta_keys);  
						?>
                        <optgroup label="<?php _e('Line Item Meta', 'woo-order-splitter'); ?>">
							<?php foreach ($line_items_unique_meta_keys as $meta_key) : ?>
								<option value="line_item_meta|<?php echo esc_attr($meta_key->meta_key); ?>" ><?php echo esc_html($meta_key->meta_key); ?></option>
							<?php endforeach; ?>
                        </optgroup>    
						<?php endif; ?>
                        
                        
                        
                        <?php if (!empty($shipping_items_unique_keys)) : asort($shipping_items_unique_keys);  
						?>
                        <optgroup label="<?php _e('Shipping Items', 'woo-order-splitter'); ?>">
							<?php foreach ($shipping_items_unique_keys as $order_item) : ?>
								<option value="shipping|<?php echo esc_attr($order_item->order_item_name); ?>" ><?php echo esc_html($order_item->order_item_name); ?></option>
							<?php endforeach; ?>
                        </optgroup>
						<?php endif; ?>
                                                
                        
                        
                        <?php if (!empty($shipping_items_unique_meta_keys)) : asort($shipping_items_unique_meta_keys);  
						?>
                        <optgroup label="<?php _e('Shipping Item Meta', 'woo-order-splitter'); ?>">
							<?php foreach ($shipping_items_unique_meta_keys as $meta_key) : ?>
								<option value="shipping_item_meta|<?php echo esc_attr($meta_key->meta_key); ?>" ><?php echo esc_html($meta_key->meta_key); ?></option>
							<?php endforeach; ?>
                        </optgroup>
						<?php endif; ?>
                        
                        
						
						<?php if (!empty($orders_unique_meta_keys)) : asort($orders_unique_meta_keys); 
						?>
                        <optgroup label="<?php _e('Order Meta', 'woo-order-splitter'); ?>">
							<?php foreach ($orders_unique_meta_keys as $meta_key) : ?>
								<option value="order_meta|<?php echo esc_attr($meta_key->meta_key); ?>" ><?php echo esc_html($meta_key->meta_key); ?></option>
							<?php endforeach; ?>
					    </optgroup>                            
						<?php endif; ?>
                       
                        
                        
                        
                        
					</select>
						
					</td>
					
                    </tr>
                    <tr>
                    
					<td colspan="2" align="left">
						
						<button class="button button-secondary wc_os_group_cat_meta_save"><?php _e('Save Meta', 'woo-order-splitter'); ?></button>
					
					</td>
                    <tr>
                    
                    <td colspan="2" align="left">
                    <span class="wc_os_group_cat_meta_msg">
                    	<?php _e('Updated successfully.', 'woo-order-splitter'); ?>
                    </span>
                    </td>
					</tr>
				</table>
			</div>
		<?php
	}
	
	function wc_os_meta_keys_array($index='', $group=''){
		
		$return = array();
		$wc_os_group_meta = get_option('wc_os_group_meta', array());	
		$wc_os_group_meta = (is_array($wc_os_group_meta)?$wc_os_group_meta:array());
		
		if(
			!empty($wc_os_group_meta) 
			&& 
			array_key_exists('wc_os_cats', $wc_os_group_meta) 
			&& 
			array_key_exists($index, $wc_os_group_meta['wc_os_cats'])
			&&
			array_key_exists($group, $wc_os_group_meta['wc_os_cats'][$index])
		){
			//this array contains line_item, shipping, order_meta with | seperator
			$return = $wc_os_group_meta['wc_os_cats'][$index][$group];
		}
		
		return $return;
	}
			
	function wc_os_group_cat_meta_post(){
		
		
		if(isset($_POST['action']) && 'wc_os_group_cat_meta_post' == $_POST['action']){
			
			$selected_group = sanitize_wc_os_data($_POST['wc_os_selected_group']);
			$selected_meta = sanitize_wc_os_data($_POST['wc_os_selected_meta']);	
	
			$wc_os_group_meta = get_option('wc_os_group_meta', array());	
			$wc_os_group_meta = (is_array($wc_os_group_meta)?$wc_os_group_meta:array());
	
			$wc_os_group_meta['wc_os_cats']['group_cats'][$selected_group] = $selected_meta;
	
			update_option('wc_os_group_meta', $wc_os_group_meta);
	
			$wc_os_group_meta = get_option('wc_os_group_meta', array());	
			$wc_os_group_meta = (is_array($wc_os_group_meta)?$wc_os_group_meta:array());
	
	
	
		
			echo json_encode($wc_os_group_meta);
			exit;
	
		}
		
	}
	
	add_action('wp_ajax_wc_os_group_cat_meta_post', 'wc_os_group_cat_meta_post');
	
	add_filter( 'woocommerce_order_query_args', 'wc_os_woocommerce_order_query_args_callback');
	
	function wc_os_woocommerce_order_query_args_callback( $get_query_vars ) {
		
		global $wc_os_custom_orders_table_enabled;
		
		if($wc_os_custom_orders_table_enabled && isset($_GET['parent-order']) && is_numeric($_GET['parent-order'])){
			$parent_order = sanitize_wc_os_data($_GET['parent-order']);
			
			$get_query_vars['meta_query']['relation'] = 'OR';
			
			$get_query_vars['meta_query'][] = array(
				'key' => 'cloned_from',
				'value' => $parent_order,
				'compare' => '=',
			);
			
			$get_query_vars['meta_query'][] = array(
				'key' => 'splitted_from',
				'value' => $parent_order,
				'compare' => '=',
			);
			
			//pree($get_query_vars);
		}
		
		return $get_query_vars;
	}
	
		
	add_action('parse_query','filter_posts_per_meta_value');
	
	function filter_posts_per_meta_value( $query ) {
		//pree($query);
		
		global $pagenow, $post_type;
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		//pree($_GET);
		
		if(isset($_GET['parent-order'])){
		
		}
		// Only add parmeeters if on shop_order and if all is not selected
		if( $pagenow == 'edit.php' && $post_type == $wc_os_get_post_type_default && isset($_GET['parent-order']) && $_GET['parent-order'] != '' && is_numeric($_GET['parent-order']) ) {
			
			
			$parent_order = sanitize_wc_os_data($_GET['parent-order']);
			
	
			$query->query_vars['post_status'] = 'any';
			$query->query_vars['meta_query']['relation'] = 'OR';
			
			$query->query_vars['meta_query'][] = array(
				'key' => 'cloned_from',
				'value' => $parent_order,
				'compare' => '=',
			);
			
			$query->query_vars['meta_query'][] = array(
				'key' => 'splitted_from',
				'value' => $parent_order,
				'compare' => '=',
			);
			//pree($query->query_vars);
			
			
	
		}
		

		
		
		if( $pagenow == 'edit.php' && $post_type == $wc_os_get_post_type_default && isset($_GET['split_status']) && in_array($_GET['split_status'], array('yes', 'no')) ) {
			
			$split_status = sanitize_wc_os_data($_GET['split_status']);
			$split_status =(in_array($split_status, array('yes', '1', 'true'))?1:0);
			
	
			$query->query_vars['post_status'] = 'any';
			$query->query_vars['meta_query']['relation'] = 'OR';
			
			$query->query_vars['meta_query'][] = array(
				'key' => 'split_status',
				'value' => $split_status,
				'compare' => '=',
			);
			if($split_status){
				$query->query_vars['meta_query'][] = array(
					'key' => 'split_status',
					'compare' => 'EXISTS' 
				);
			}else{
				$query->query_vars['meta_query'][] = array(
					'key' => 'split_status',
					'compare' => 'NOT EXISTS' 
				);
			}
			
			
			//pree($query->query_vars);exit;
	
		}		
		
	
		//pree($query->query_vars);
	}
	
	if(!function_exists('wc_os_expected_child_orders_by_cart')){
		function wc_os_expected_child_orders_by_cart(){
			WC()->session = new WC_Session_Handler();
			WC()->session->init();						
			return wc_os_get_session('wc_os_expected_child_orders');//WC()->session->get('wc_os_expected_child_orders');
		}
	}
	
	
	
	if(!function_exists('wc_os_child_orders_by_order_id')){
		function wc_os_child_orders_by_order_id($order_id=0, $ids_only=false){
			
			global $wc_os_custom_orders_table_enabled, $wpdb;
			
			$ret = array();
			
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			
			if(!$order_id){
				return $ret;
			}
		
			$args = array(
				 'posts_per_page' => -1,
				 'post_type'      => $wc_os_get_post_type_default,
				 'post_status'    => 'any',
				 
			);

			if($ids_only){
				$args['fields'] = 'ids';
			}
			
			$args['meta_query']['relation'] = 'OR';
			
			$args['meta_query'][] = array(
				'key' => 'cloned_from',
				'value' => $order_id,
				'compare' => '=',
			);
			
			$args['meta_query'][] = array(
				'key' => 'splitted_from',
				'value' => $order_id,
				'compare' => '=',
			);				
	
			if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders_meta')){
				$res_obj = $wpdb->get_results("SELECT order_id FROM ".$wpdb->prefix."wc_orders_meta WHERE meta_key IN ('cloned_from', 'splitted_from') AND meta_value=$order_id");

				if(!empty($res_obj)){
					foreach($res_obj as $child_order_ob){
						$ret[] = $child_order_ob->order_id;
					}
				}
				
			}else{
				$ret = get_posts( $args );
			}
			
			//wc_orders_meta
			
			//pree($args);
			
			//pree($ret);
		
			return $ret;
		}
	}
	
	
	add_action('woocommerce_checkout_after_customer_details', 'wc_os_before_cart_content_premssion');

	if(!function_exists('wc_os_before_cart_content_premssion')){
		function wc_os_before_cart_content_premssion($return = false){
			
			global $wc_os_auto_forced;
		
			if(!$wc_os_auto_forced){ return; }
			
			global $wc_os_general_settings, $wc_os_pro;
			
			$wc_os_customer_permitted = '';
			
			$wc_os_packages_overview = array_key_exists('wc_os_packages_overview', $wc_os_general_settings);
			$wc_os_customer_permission_text_no = array_key_exists('wc_os_customer_permission_text_no', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_customer_permission_text_no'])?$wc_os_general_settings['wc_os_customer_permission_text_no']:__('Continue Single Order', 'woo-order-splitter');
			$wc_os_customer_permission_text_yes = array_key_exists('wc_os_customer_permission_text_yes', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_customer_permission_text_yes'])?$wc_os_general_settings['wc_os_customer_permission_text_yes']:__('Split Order', 'woo-order-splitter');
			
			$wc_os_customer_permission = array_key_exists('wc_os_customer_permission', $wc_os_general_settings);
			

			
			if($wc_os_customer_permission){
				$wc_os_customer_permitted = '<div class="wc_os_customer_permission"><label for="wc_os_customer_permitted_no"><input type="radio" value="no"  id="wc_os_customer_permitted_no" name="wc_os_customer_permitted" '.(wc_os_get_session( 'wc_os_customer_permitted')!='on'?'checked="checked"':'').' />'.$wc_os_customer_permission_text_no.'</label><br /><label for="wc_os_customer_permitted_yes"><input type="radio" value="yes"  id="wc_os_customer_permitted_yes" name="wc_os_customer_permitted" '.(wc_os_get_session( 'wc_os_customer_permitted')=='on'?'checked="checked"':'').' />'.$wc_os_customer_permission_text_yes.'</label></div>';
				
				if(wc_os_get_session( 'wc_os_customer_permitted') == 'on'){
				
					$wc_os_customer_permitted .= '<style type="text/css">.woocommerce-checkout-review-order-table thead, .woocommerce-checkout-review-order-table tbody{display: none}</style>';
				
				}				
			}else{
				
			}

			if($return){
				return $wc_os_customer_permitted;
			}elseif(
					$wc_os_customer_permission
				
				&&
					(
							!$wc_os_pro
						||
							($wc_os_pro && !$wc_os_packages_overview)
					)
			){     
			    echo $wc_os_customer_permitted;
            }


		}
	}


		

	
	function shipping_items_test(){
		if(isset($_GET['shipping_items_test'])){
				
			$order_id = sanitize_wc_os_data($_GET['post']);
			
			$original_order = wc_get_order($order_id);
			
			pree(wc_os_get_order_meta($order_id));
			
			$original_order_shipping_items = $original_order->get_items('shipping');
		
			foreach ( $original_order_shipping_items as $order_item ) {
	
				$order_item_meta = wc_get_order_item_meta( $order_item->get_id(), '');
				pree($order_item_meta);
	
				$vendor_id = wc_get_order_item_meta( $order_item->get_id(), 'vendor_id',  true);
				pree($vendor_id);
			}
			exit;
		}
	}
	
	function wc_os_update_shipping_for_multiple_addresses($order_id=0, $order=array()){
		
		if(!$order_id){ return; }
		
		$_multiple_shipping = wc_os_get_order_meta($order_id, '_multiple_shipping', true);//'yes';//
		
		//wc_os_pree($_multiple_shipping);
		
		if($_multiple_shipping=='yes'){
			$_multiple_shipping_updated = false;
			$order = (is_object($order)?$order:wc_get_order($order_id));
			$_shipping_addresses = wc_os_get_order_meta($order_id, '_shipping_addresses', true);
			$_wos_include_item_key = wc_os_get_order_meta($order_id, '_wos_include_item_key', true);
			//wc_os_pree($_shipping_addresses);
			//wc_os_pree($_wos_include_item_key);
			if(is_array($_shipping_addresses)){
				foreach($order->get_items() as $key=>$val){
					$order_product_id =  $val->get_product_id();
					$multiple_items_to_multiple_addresses = array();
					foreach($_shipping_addresses as $_shipping_key=>$_shipping_val){
						$substr_key = stristr($_shipping_key, '_'.$order_product_id.'_');
						//wc_os_pree($substr_key);
						if($substr_key){
							//wc_os_pree($_shipping_key.': '.$_shipping_val);
							$shipping_item_index = explode('_', $_shipping_key);
							array_splice($shipping_item_index, count($shipping_item_index) - 3, 3);
							$shipping_item_index = implode('_', $shipping_item_index);
							//update_post_meta($order_id, '_'.$shipping_item_index, $_shipping_val);
							$multiple_items_to_multiple_addresses[$substr_key][$shipping_item_index] = $_shipping_val;
							$_multiple_shipping_updated = true;
						}
					}
					if(!empty($multiple_items_to_multiple_addresses)){
						foreach($multiple_items_to_multiple_addresses as $multiple_key=>$multiple_address_data){
							$multiple_address_data_str = str_replace(array(' '), '', strtolower(implode('', $multiple_address_data)));
							if(stristr($_wos_include_item_key, $multiple_address_data_str)){
								$multiple_items_to_multiple_addresses[$multiple_address_data_str] = $multiple_address_data;
								
								foreach($multiple_address_data as $_shipping_key=>$_shipping_val){
									wc_os_pree($order_id.', _'.$_shipping_key.', '.$_shipping_val);
									wc_os_update_order_meta($order_id, '_'.$_shipping_key, $_shipping_val);
								}
							}
							unset($multiple_items_to_multiple_addresses[$multiple_key]);
						}
					}
					
					//wc_os_pree($multiple_items_to_multiple_addresses);
				}
			}
			if($_multiple_shipping_updated){
				wc_os_update_order_meta($order_id, '_multiple_shipping', false);
			}
		}
		
		
	}


    add_action('wc_os_after_order_split', 'wc_os_after_order_split_callback', 10, 2 );

    if(!function_exists('wc_os_after_order_split_callback')){

        function wc_os_after_order_split_callback($new_order_ids=array(), $originalorderid=0){
			
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			$is_shop_order = (get_post_type($originalorderid)==$wc_os_get_post_type_default);
			

			
			if(!$is_shop_order){
				$wc_os_logger_str = $originalorderid.' is not a Valid Order';
				return;
			}
			
			$wc_os_logger_str = 'PARENT ORDER #'.$originalorderid.' - CHILDREN '.implode(', ', $new_order_ids);

								
			
			global $woocommerce_account_funds, $wc_os_general_settings, $woocommerce, $wc_os_shipping_cost;
			
		
			$mailer = $woocommerce->mailer();
			

			if(function_exists('wc_os_calculate_delivery_date')){
                wc_os_calculate_delivery_date($new_order_ids, $originalorderid);				
			}
						
            if(function_exists('wc_os_apply_coupon_child_orders')){
                wc_os_apply_coupon_child_orders($new_order_ids, $originalorderid);
            }
			
			$wc_os_logger_str = 'wc_os_apply_coupon_child_orders - CLEARED #1';

			
			
						
			if(function_exists('wc_os_update_method_order_status')){
                wc_os_update_method_order_status($new_order_ids, $originalorderid);
			}
			
			$wc_os_logger_str = 'wc_os_update_method_order_status - CLEARED #3';


			if(function_exists('wc_os_update_group_order_status')){
				
                wc_os_update_group_order_status($new_order_ids, $originalorderid);				
			}	
			
			$wc_os_logger_str = 'wc_os_update_group_order_status - CLEARED #4';

								
			if($woocommerce_account_funds){
				wc_os_add_funds_used_child($originalorderid, $new_order_ids);
			}

			$wc_os_logger_str = 'wc_os_add_funds_used_child - CLEARED #5';

			
						
						
            $wc_os_order_combine_email = array_key_exists('wc_os_order_combine_email', $wc_os_general_settings);
			$order_split_summary = array_key_exists('wc_os_order_split_email', $wc_os_general_settings);
            $order_split_created = array_key_exists('wc_os_order_created_email', $wc_os_general_settings); //New Orders Created After Split - Customer
            $order_split_created_admin = array_key_exists('wc_os_order_created_email_admin', $wc_os_general_settings); //New Orders Created After Split - Admin		
			$order_split_created_vendor = wc_os_order_vendor_email_status();
			

            if($order_split_summary){

                wos_email_notification(array('new'=>$new_order_ids, 'original'=>$originalorderid), 'split');

            }

			
            if(!empty($new_order_ids)){
     			
				
				
                foreach ($new_order_ids as $new_oder){

					
					$new_oder = is_array($new_oder)?current($new_oder):$new_oder;
					
					if(!is_numeric($new_oder))
					continue;
					
					$is_shop_order = (get_post_type($new_oder)==$wc_os_get_post_type_default);
					
					if(!$is_shop_order)
					continue;
					
					
							
					if(class_exists('WCML_Emails')){
						global $sitepress;
						$wpml_language = wc_os_get_order_meta($new_oder, 'wpml_language', true);
						$language = $wpml_language?$wpml_language:'en';
						$sitepress->switch_lang( $language, true );
						$sitepress->set_admin_language( $language );				
					}


                    wc_downloadable_product_permissions($new_oder);
					
					$order = new WC_Order($new_oder);
						
					wc_os_update_shipping_for_multiple_addresses($new_oder, $order); //19/11/2022 - WooCommerce Ship to Multiple Addresses
					

					$proceed_email_for_cav = ($order_split_created || $order_split_created_admin || $order_split_created_vendor);
					
					//wc_os_pre($proceed_email_for_cav);exit;
						
                    if($proceed_email_for_cav){
						
						
						wc_os_delete_order_meta($new_oder, '_new_order_email_sent');



                        $wc_mailer = WC()->mailer();
                        $mails = $wc_mailer->get_emails();
						
					

                        

                        $order_status = $order->get_status();

                        switch($order_status){

                            case 'completed':

                                $email_template = new WC_Email_Customer_Completed_Order();//$mails['WC_Email_Customer_Completed_Order'];

							break;

                            case 'pending':
							case 'on-hold':

                                $email_template = new WC_Email_Customer_On_Hold_Order();//$mails['WC_Email_Customer_On_Hold_Order'];

							break;

                            case 'processing':

                                $email_template = new WC_Email_Customer_Processing_Order();//$mails['WC_Email_Customer_Processing_Order'];

							break;


                            case 'cancelled':

                                $email_template = new WC_Email_Cancelled_Order();//$mails['WC_Email_Cancelled_Order'];

							break;


                            case 'refunded':

                                $email_template = new WC_Email_Customer_Refunded_Order();//$mails['WC_Email_Customer_Refunded_Order'];

							break;

                            case 'failed':

                                $email_template = new WC_Email_Failed_Order();//$mails['WC_Email_Failed_Order'];

							break;


                            default:

                                if($order->is_paid()){

                                    $email_template = new WC_Email_Customer_Processing_Order();//$mails['WC_Email_Customer_Processing_Order'];

                                }else{

                                    $email_template = new WC_Email_Customer_On_Hold_Order();//$mails['WC_Email_Customer_On_Hold_Order'];

                                }


                           break;


                        }

						$new_order_email_template = new WC_Email_New_Order();//$mails['WC_Email_New_Order'];

                        if($order_split_created){
							$req_order_status = wc_os_get_status_setting('customer_email', false);
							
							
							$wc_os_logger_str = $new_oder.' - '.$order_status.' - New Orders Created After Split - Customer';
							
							$required_order_status = (!$req_order_status || ($req_order_status==$order_status));
							
							$wc_os_logger_str .= ' [$req_order_status = '.$req_order_status.' & $order_status = '.$order_status.']';
							
							
							
							if($required_order_status){
								
								$email_template->trigger($new_oder);	//New Orders Created After Split - Customer
								$wc_os_logger_str .= ' > <b class="green">SENT</b>';

							}
							
							wc_os_logger('debug', $wc_os_logger_str, true);

							
					
							
                        }

						
                        if($order_split_created_vendor){
							
							$wc_os_vendor_email_template =  $new_order_email_template;
							
							
							if(array_key_exists('wc_os_vendor_email_template', $wc_os_general_settings) && $wc_os_general_settings['wc_os_vendor_email_template']!=''){
								$wc_os_vendor_email_template_str = $wc_os_general_settings['wc_os_vendor_email_template'];
								if(array_key_exists($wc_os_vendor_email_template_str, $mails)){
									$wc_os_vendor_email_template =  $mails[$wc_os_vendor_email_template_str];
								}
							}else{
								
							}
							
						
                            $user_info = wc_os_get_order_vendor($new_oder);
							
                            if(!empty($user_info)){
								
                                $wc_os_vendor_email_template->recipient = $wc_os_vendor_email_template->settings['recipient'] = $user_info->user_email;
                         
								
								if(is_object($wc_os_vendor_email_template)){
									
									$trigger_email_data = wc_os_trigger_email($new_oder, $wc_os_vendor_email_template, 'body');
									
									$trigger_email_status = $mailer->send($wc_os_vendor_email_template->recipient, $trigger_email_data['subject'], $trigger_email_data['body'], $trigger_email_data['headers']);//$trigger_email_data['message']
							

								}else{
									
								}
							
								$wc_os_logger_str = $new_oder.' - '.$order_status.' - New Order Email <i class="fas fa-envelope-open-text"></i> - to Vendor - Sent to '.$user_info->user_email;
								
							
                               

                            }else{
								//$wc_os_logger_str = 'Vendor Information Missing';
								
							}

                        }
						if($order_split_created_admin){

                 
							$wc_os_logger_str = 'Stacking ORDER# '.$new_oder.' for Email';
							
							
							

                            
							$emails_stack = get_option('wc_os_emails_to_admin', array());
							$emails_stack = (is_array($emails_stack)?$emails_stack:array());
							if(!array_key_exists($new_oder, $emails_stack)){
								
								$emails_stack[$new_oder] = false;
								
								update_option('wc_os_emails_to_admin', $emails_stack);
							}
							
							//$emails_stack = get_option('wc_os_emails_to_admin', array());
							
							
							
							


                        }


                    }

                   	if(class_exists('WCML_Emails')){		
						do_action('wpml_restore_language_from_email');		
					}

                }
				
				if(function_exists('wc_os_smart_order_notes')){
					wc_os_smart_order_notes($originalorderid); //UPDATING STOCK LEVEL REDUCE NOTES TO INDIVIDUAL ORDERS FOR EACH ORDER ITEMS
				}

            }
			
			
		



        }
    }

 
	
	
			
	if(!function_exists('wc_os_add_funds_used_child')){
		
		function wc_os_add_funds_used_child($original_id, $child_orders){
		
			global $wc_os_general_settings, $wc_os_settings;
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			$split_type = $wc_os_settings['wc_os_ie'];
	
			$remove_parent_types = array('io');
	
			$remove_items_from_parents = array_key_exists('wc_os_effect_parent', $wc_os_general_settings);
			
			$args = array(
				
				'post_type' => $wc_os_get_post_type_default,
				'post_status' => 'any',
				'numberposts' => -1,
				'include' => array($original_id),
				'meta_query' => array(
					array(
						'key' => '_funds_used',
						'compare' => 'EXIST'
					)
				)
			
			);
			
			$funds_order = get_posts($args);
			$total_funds = wc_os_get_order_meta($original_id, '_funds_used', true);
			$funds_used = 0;
	
			if(in_array($split_type, $remove_parent_types) && $remove_items_from_parents){
				array_unshift($child_orders,$original_id);
			}
			
			
			
			if(empty($funds_order) || $total_funds <= 0) {return;}
			
			
			
			
			if(!empty($child_orders)){
			
				foreach ($child_orders as $child_order_id){
				
				
					
					$child_order = new WC_Order($child_order_id);
					$full_total_child = $child_order->get_subtotal();
					
					
					
					if($full_total_child > $total_funds){
					
					
						
						$child_new_total = $full_total_child - $total_funds;
						$funds_used = $total_funds;
						$funds_used_by_current = $total_funds;
						$total_funds = $total_funds - $funds_used;
						
					
					
					}else{
					
					
						$total_funds = $total_funds - $full_total_child;
						$child_new_total = 0;
						$funds_used = $funds_used + $full_total_child;
						$funds_used_by_current = $full_total_child;
						
					}
					
					
					
					$child_order->set_total($child_new_total);
					$child_order->save();
					
					
					wc_os_update_order_meta($child_order_id, '_funds_used', $funds_used_by_current);
					wc_os_update_order_meta($child_order_id, '_funds_removed', 1);
				
				
				}
			
			
			
			}
		
		
		}
		
	}

	add_action('woocommerce_analytics_update_order_stats', 'wc_os_update_order_lookup_tables');
    add_action('woocommerce_analytics_update_product', 'wc_os_update_product_lookup_tables', 10, 2);
    add_action('woocommerce_analytics_update_coupon', 'wc_os_update_coupon_lookup_tables', 10, 2);
    add_action('woocommerce_analytics_update_tax', 'wc_os_update_tax_lookup_tables', 10, 2);


    function wc_os_update_lookup_tables($order_id, $table_name_without_prefix){

		global $wpdb, $wc_os_general_settings, $wc_os_settings;

		$wc_os_settings['wc_os_additional'] = isset($wc_os_settings['wc_os_additional']) ? $wc_os_settings['wc_os_additional'] : array();

		$is_split_off = in_array('split', $wc_os_settings['wc_os_additional']);

		if($is_split_off){return;}


		$split_type = $wc_os_settings['wc_os_ie'];
		$remove_parent_types = array('io', 'exclusive', 'inclusive');
		$remove_items_from_parents = array_key_exists('wc_os_effect_parent', $wc_os_general_settings);
		$is_parent_effected = in_array($split_type, $remove_parent_types) && $remove_items_from_parents;


		$parent_order_id = wc_os_get_order_meta($order_id, 'splitted_from', true);
		$parent_order_items = array();

		if($parent_order_id){
			$parent_order = new WC_Order($parent_order_id);
			$parent_order_items = $parent_order->get_items();

			if(empty($parent_order_items)){

				$order_stats_table = $wpdb->prefix.'wc_order_stats';
				$query_order = "DELETE FROM $order_stats_table WHERE order_id = $parent_order_id";
				$wpdb->query($query_order);



				wc_os_update_order_meta($order_id, '_wc_os_report_included', 'yes');
				wc_os_update_order_meta($parent_order_id, '_wc_os_report_included', 'no');
				delete_option( '_transient_wc_report_sales_by_date');	

			}



		}

		$wc_os_child_order =  wc_os_get_order_meta($order_id, '_wc_os_child_order', true);
		$force_sync =  wc_os_get_order_meta($order_id, '_wc_os_force_sync', true);


        if(!wc_os_order_removal() && $wc_os_child_order == 'yes' && !$force_sync && !$is_parent_effected && !empty($parent_order_items )){

            $prodcut_lookup_table = $wpdb->prefix.$table_name_without_prefix;
            $query_product = "DELETE FROM $prodcut_lookup_table WHERE order_id = $order_id";
            $result = $wpdb->query($query_product);

        }

    }

    function wc_os_update_order_lookup_tables ($order_id){

		global $wpdb, $wc_os_general_settings, $wc_os_settings;

		$wc_os_settings['wc_os_additional'] = isset($wc_os_settings['wc_os_additional']) ? $wc_os_settings['wc_os_additional'] : array();

		$is_split_off = in_array('split', $wc_os_settings['wc_os_additional']);

		if($is_split_off){return;}


		$split_type = $wc_os_settings['wc_os_ie'];
		$remove_parent_types = array('io', 'exclusive', 'inclusive');
		$remove_items_from_parents = array_key_exists('wc_os_effect_parent', $wc_os_general_settings);
		$is_parent_effected = in_array($split_type, $remove_parent_types) && $remove_items_from_parents;
		if($is_parent_effected){

			wc_os_update_order_meta($order_id, '_wc_os_report_included', 'yes');
			delete_option( '_transient_wc_report_sales_by_date');

		}

		$parent_order_id = wc_os_get_order_meta($order_id, 'splitted_from', true);
		$parent_order_items = array();

		if($parent_order_id){
			$parent_order = new WC_Order($parent_order_id);
			$parent_order_items = $parent_order->get_items();

			if(empty($parent_order_items)){

				$order_stats_table = $wpdb->prefix.'wc_order_stats';
				$query_order = "DELETE FROM $order_stats_table WHERE order_id = $parent_order_id";
				$wpdb->query($query_order);



				wc_os_update_order_meta($order_id, '_wc_os_report_included', 'yes');
				wc_os_update_order_meta($parent_order_id, '_wc_os_report_included', 'no');
				delete_option( '_transient_wc_report_sales_by_date');	

			}



		}

		$wc_os_child_order =  wc_os_get_order_meta($order_id, '_wc_os_child_order', true);

		$force_sync =  wc_os_get_order_meta($order_id, '_wc_os_force_sync', true);
	   
	  

        if(!wc_os_order_removal() && $wc_os_child_order == 'yes' && !$force_sync && !$is_parent_effected && !empty($parent_order_items)){

            $order_stats_table = $wpdb->prefix.'wc_order_stats';
            $query_order = "DELETE FROM $order_stats_table WHERE order_id = $order_id";
            $wpdb->query($query_order);

        }
		if(class_exists('ReportsCache')){
			ReportsCache::invalidate();
		}


    }

    function wc_os_update_product_lookup_tables ($order_item_id, $order_id){

        wc_os_update_lookup_tables($order_id, 'wc_order_product_lookup');

		if(class_exists('ReportsCache')){
			ReportsCache::invalidate();
		}

    }

    function wc_os_update_coupon_lookup_tables ($coupon_id, $order_id){

        wc_os_update_lookup_tables($order_id, 'wc_order_coupon_lookup');

		if(class_exists('ReportsCache')){
			ReportsCache::invalidate();
		}

    }

    function wc_os_update_tax_lookup_tables ($tax_item_rate_id, $order_id){

        wc_os_update_lookup_tables($order_id, 'wc_order_tax_lookup');

		if(class_exists('ReportsCache')){
			ReportsCache::invalidate();
		}

    }

    add_action('wc_os_after_order_split', 'wc_os_add_order_meta_after_split', 9, 2 );

    function wc_os_add_order_meta_after_split($new_order_ids, $original_order){
		
		global $wc_os_settings, $wpdb;
		$wc_os_ie_selected = $wc_os_settings['wc_os_ie'];
		
		$_wc_os_parent_order = wc_os_get_order_meta($original_order, '_wc_os_parent_order', true);

        if($original_order && !$_wc_os_parent_order){

			wc_os_update_order_meta($original_order, '_wc_os_parent_order', 'yes');
			
        }

        if(!empty($new_order_ids)){
			
			global $wc_os_wcfm_installed;
			
            foreach ($new_order_ids as $order_id){

                wc_os_update_order_meta($order_id, '_wc_os_child_order', 'yes');
                wc_os_update_order_meta($order_id, '_wc_os_report_included', 'no');
				
				
				
				if ($wc_os_ie_selected=='group_by_vendors'){
					if($wc_os_wcfm_installed) {
						$vendor_shipping_rate = 0;
						
						$newOrder = wc_get_order($order_id);
						//$get_order = wc_get_order($order_id);
						if(!empty($newOrder)){
							//$vendor_shipping_rate = $get_order->get_shipping_total();
							$newOrderData = $newOrder->data;
							$shippingTotal = $newOrderData['shipping_total'];

							$wpdb->query("UPDATE ".$wpdb->prefix."wcfm_marketplace_orders SET shipping='$shippingTotal' WHERE order_id=$order_id");
						}
					}
				}
				
				
				if ($wc_os_ie_selected=='group_by_vendors'){
					if(wc_os_table_exists('wcfm_marketplace_orders')) {
				  
					  $vendor_shipping_rate = 0;
					  
					  $get_order = wc_get_order($order_id);
					  
					  if(!empty($get_order)){
						  $vendor_shipping_rate = $get_order->get_shipping_total();				  
						  $wpdb->query("UPDATE ".$wpdb->prefix."wcfm_marketplace_orders SET shipping='$vendor_shipping_rate' WHERE order_id=$order_id");
					  }					  
					}					  
				}

            }
			
			
        }
		
		

    }

	


    add_action('woocommerce_reports_get_order_report_query', function($query){
		global $wpdb;

        $query['join'] .= " LEFT JOIN $wpdb->postmeta as wc_os_postmeta ON (posts.ID = wc_os_postmeta.post_id AND wc_os_postmeta.meta_key = '_wc_os_report_included' )";
        $query['where'] .= " AND ((wc_os_postmeta.meta_key IN ('_wc_os_report_included') AND wc_os_postmeta.meta_value = 'yes') OR wc_os_postmeta.post_id IS NULL) ";
        return $query;

	});
	

	add_action('wp_trash_post', 'wc_os_update_child_orders_stats');

	function wc_os_update_child_orders_stats($order_id){

		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		$wc_os_parent_order = wc_os_get_order_meta($order_id, '_wc_os_parent_order', true);
		if($wc_os_parent_order){

				$args = array(

					'numberposts' => -1,
					'post_type' => $wc_os_get_post_type_default,
					'post_status' => 'any',
					'fields' => 'ids',
					'meta_query' => array(

						array(

							'key' => 'splitted_from',
							'value' => $order_id,
							'compare' => '=',
						),

					),


				);


				$get_child_ids = get_posts($args);

				if(!empty($get_child_ids)){
					foreach($get_child_ids as $child_id){

						wc_os_update_order_meta($child_id, '_wc_os_force_sync', true);

						$results = array(


							class_exists('OrdersStatsDataStore')?OrdersStatsDataStore::sync_order( $child_id ):'',
							class_exists('ProductsDataStore')?ProductsDataStore::sync_order_products( $child_id ):'',
							class_exists('CouponsDataStore')?CouponsDataStore::sync_order_coupons( $child_id ):'',
							class_exists('TaxesDataStore')?TaxesDataStore::sync_order_taxes( $child_id ):'',
	
						);

						wc_os_update_order_meta($child_id, '_wc_os_report_included', 'yes');
						wc_os_delete_order_meta($child_id, '_wc_os_force_sync');


					}

					wc_os_update_order_meta($order_id, '_wc_os_report_included', 'no');
					if(class_exists('ReportsCache')){
						ReportsCache::invalidate();
					}
					delete_option( '_transient_wc_report_sales_by_date');

				}

		}

	}
		
	
	if(!function_exists('wos_get_order_name')){
		function wos_get_order_name(){
	
			global $wpdb;
	
			$name = __('order', 'woocommerce').'-';
			$name .= strftime( _x( '%B-%d-%Y-%I%M-', 'Order date parsed by strftime', 'woo-order-splitter' ) );
			$name .= date('a', time());
	
	
			$name_posts = "SELECT * FROM $wpdb->posts WHERE ID = (SELECT MAX(ID) FROM $wpdb->posts WHERE post_name LIKE '$name%')";
			$name_row = $wpdb->get_row($name_posts);
			$prev_name = $name_row ? $name_row->post_name : $name;
			$prev_name_explode = explode('-', $prev_name);
			$prev_name = end($prev_name_explode);
	
	
			if($prev_name != 'am' && $prev_name != 'pm'){
				$prev_name++;
				$name .= '-'.$prev_name;
			}else{
				$name .= '-'.'1';
	
			}
	
			return $name;
		}
	}
	
	
	if(!function_exists('wos_get_post_title')){
	
		function wos_get_post_title() {
			// @codingStandardsIgnoreStart
			/* translators: %s: Order date */
			return __( 'Order', 'woo-order-splitter' ).' &ndash; '.sprintf( '%s', strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woo-order-splitter' ) ) );

			// @codingStandardsIgnoreEnd
		}
	
	}
	
	if(!function_exists('wos_insert_post')){
		function wos_insert_post($postarr, $wp_error = false){
			global $wpdb;
	
			$unsanitized_postarr = $postarr;
	
			$user_id = get_current_user_id();
	
			$defaults = array(
				'post_author'           => $user_id,
				'post_date'             => current_time( 'mysql' ),
				'post_date_gmt'         => current_time( 'mysql' , 1),
				'post_content'          => '',
				'post_title'            => wos_get_post_title(),
				'post_excerpt'          => '',
				'post_status'           => 'draft',
				'comment_status'        => 'open',
				'ping_status'           => '',
				'post_password'         => '',
				'post_name'             => wos_get_order_name(),//
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => current_time( 'mysql' ),
				'post_modified_gmt'     => current_time( 'mysql', 1 ),
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => '',//
				'menu_order'            => 0,
				'post_type'             => 'post',
				'post_mime_type'        => '',
				'comment_count'         => 0,
	
			);
	
	
	
			$postarr = wp_parse_args( $postarr, $defaults );
	
			unset( $postarr['filter'] );
	
			$postarr = sanitize_post( $postarr, 'db' );
	
			unset( $postarr['filter'] );
			unset( $postarr['ID'] );
	
	
			$data = $postarr;
	
	
			if ( false === $wpdb->insert( $wpdb->posts, $data ) ) {
				if ( $wp_error ) {
	
					$message = __( 'Could not insert post into the database.' );
	
					return new WP_Error( 'db_insert_error', $message, $wpdb->last_error );
				} else {
					return 0;
				}
			}
	
			$post_ID = (int) $wpdb->insert_id;
	
			$where = array( 'ID' => $post_ID );
	
	
			$current_guid = get_post_field( 'guid', $post_ID );
	
			// Set GUID.
			if ('' === $current_guid ) {
				$wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_ID ) ), $where );
			}
	
			return $post_ID;
	
		}
	}
	
	if(!function_exists('wos_update_post')){
	
		function wos_update_post( $postarr = array(), $wp_error = false ) {
	
			global $wpdb;
	
			if ( is_object( $postarr ) ) {
				// Non-escaped post was passed.
				$postarr = get_object_vars( $postarr );
				$postarr = wp_slash( $postarr );
			}
	
	
			// First, get all of the original fields.
			$post = get_post( $postarr['ID'] );
	
			if ( is_null( $post ) ) {
				if ( $wp_error ) {
					return new WP_Error( 'invalid_post', __( 'Invalid post ID.' ) );
				}
				return 0;
			}
			$post = (array) $post;
	
			unset($post['filter']);
	
	
			// Escape data pulled from DB.
			$post = wp_slash( $post );
	
	
			$postarr = array_merge( $post, $postarr );
	
	
			$where = array( 'ID' => $postarr['ID'] );
	
			if ( false === $wpdb->update( $wpdb->posts, $postarr, $where ) ) {
				if ( $wp_error ) {
	
					  $message = __( 'Could not update post in the database.' );
	
	
					return new WP_Error( 'db_update_error', $message, $wpdb->last_error );
				} else {
					return 0;
				}
			}else{
				return $postarr['ID'];
			}
	
	
		}
	
	
	}	
	
	function wc_os_logger($type='email', $data=array(), $method_based = false){
		
		global $wc_os_settings;
		$wc_os_ie = (array_key_exists('wc_os_ie', $wc_os_settings)?$wc_os_settings['wc_os_ie']:'');

		$wc_os_logger = array();
		
		if(empty($data) && !in_array($type, array('order', 'debug', 'email'))){
			 $data = $type;
		}
		
		
		
		switch($type){
			case 'debug':
			default:
				
				
				$wc_os_logger = get_option('wc_os_debug_logger');
				
				$wc_os_logger = is_array($wc_os_logger)?$wc_os_logger:array();

				if(get_option('wc_os_debug_log') && $data){
					if($method_based && $wc_os_ie){
						$wc_os_logger[$wc_os_ie][] = $data;
					}else{
						$wc_os_logger[] = $data;
					}
					update_option('wc_os_debug_logger', $wc_os_logger);
				}
			break;			
			case 'email':
				$wc_os_logger = get_option('wc_os_logger');
				
				$wc_os_logger = is_array($wc_os_logger)?$wc_os_logger:array();
				
				if(get_option('wc_os_email_log') && $data){
					if($method_based && $wc_os_ie){
						$wc_os_logger[$wc_os_ie][] = $data;
					}else{
						$wc_os_logger[] = $data;	
					}
					update_option('wc_os_logger', $wc_os_logger);
				}
			break;
			case 'order':
				$wc_os_logger = get_option('wc_os_remove_order_log', array());
				$wc_os_logger = is_array($wc_os_logger)?$wc_os_logger:array();
				
				if(get_option('wc_os_order_log') && !empty($data)){
					if($method_based && $wc_os_ie){
						$wc_os_logger[$wc_os_ie][] = $data;
					}else{
						$wc_os_logger[] = $data;
					}
					update_option('wc_os_remove_order_log', $wc_os_logger);		
				}
			break;
		}
		
		return $wc_os_logger;
	}
	
	if(!function_exists('wc_os_highlighter')){
		function wc_os_highlighter($str, $type = 1){
			
			
			
			$str = str_replace(
				
						array(
							'Customer',
							'New'
						),
						array(
							'<span class="wc-os-highlighter-'.$type.'">Customer</span>',
							'<span class="wc-os-highlighter-2">New</span>'
						),
						(string)$str
						
					);
			
			
			return $str;
		}
	}
	function wc_os_clear_order_log_callback(){
		if(isset($_GET['wc_os_clear_order_log'])){
			wc_os_clear_order_log(true);
		}
	};
	
	if(!function_exists('wc_os_method_based_default_order_status')){
		function wc_os_method_based_default_order_status($default_action='', $order_id=0, $expected=''){	
			
			global $wc_os_settings;
			
			$order_meta = array();
			$_wc_os_parent_order = false;
			
			if($order_id)
			$order_meta = wc_os_get_order_meta($order_id);
			
			$_wc_os_parent_order = array_key_exists('_wc_os_parent_order', $order_meta);
				
			switch($wc_os_settings['wc_os_ie']){
				
				case 'io':
				
					$io_options = wc_os_quick_get('wc_os_io_options', $order_id);
					$default_action = (array_key_exists('default_stock_status', $io_options) && $io_options['default_stock_status']? $io_options['default_stock_status'] : $default_action);					
					
					wc_os_pre('$_wc_os_parent_order: '. $_wc_os_parent_order, '');
					wc_os_pre('$default_action: '.$default_action, '');
					wc_os_pre('$io_options: ', '');
					wc_os_pre($io_options, '');
					

					if(!empty($order_meta) || $order_id==0){
						if(array_key_exists('_wos_in_stock', $order_meta) || $expected=='_wos_in_stock'){
							$default_action = (array_key_exists('in_stock_status', $io_options) && $io_options['in_stock_status']!=''? $io_options['in_stock_status'] : $default_action);
						}
						if(array_key_exists('_wos_out_of_stock', $order_meta) || $expected=='_wos_out_of_stock'){
							$default_action = (array_key_exists('out_stock_status', $io_options) && $io_options['out_stock_status']!=''? $io_options['out_stock_status'] : $default_action);
						}
					}
				
				break;
				
			}
				
			wc_os_pre('$default_action: '.$default_action, '');
			
			return $default_action;
		}
	}
	if(!function_exists('wc_os_get_products_list')){
		function wc_os_get_products_list($products=array()){
		
		global $wc_os_settings, $wc_os_multiple_warning, $wc_os_pro, $wc_os_currency;
		
		$wc_os_currency = wc_os_currency_symbol();
		
		
		$groups_arr = wc_os_get_groups_range();
		$wc_os_ie_name = ($wc_os_settings['wc_os_ie']?'wc_os_settings[wc_os_products]['.$wc_os_settings['wc_os_ie'].'][]':'wc_os_settings[wc_os_products][]');
		
		$products_array = array();
		$wc_os_all_action = array(

            'default' => split_actions_display('default', 'title'),
            'exclusive' => split_actions_display('exclusive', 'title'),
            'inclusive' => split_actions_display('inclusive', 'title'),
            'shredder' => split_actions_display('shredder', 'title'),
            'io' => split_actions_display('io', 'title'),
            'quantity_split' => split_actions_display('quantity_split', 'title'),

        );		
		
		foreach($products as $prod){
		
					$product = wc_get_product($prod->ID);
					$products_array[] = $prod->ID;
					
		
		
		
					
		
					
		
					$default_ticked = (isset($wc_os_settings['wc_os_products']['default']) && in_array($prod->ID, ($wc_os_settings['wc_os_products']['default']))?true:false);
		
					$exclusive_ticked = (isset($wc_os_settings['wc_os_products']['exclusive']) && in_array($prod->ID, ($wc_os_settings['wc_os_products']['exclusive']))?true:false);
		
					$inclusive_ticked = (isset($wc_os_settings['wc_os_products']['inclusive']) && in_array($prod->ID, ($wc_os_settings['wc_os_products']['inclusive']))?true:false);
		
					$shredder_ticked = (isset($wc_os_settings['wc_os_products']['shredder']) && in_array($prod->ID, ($wc_os_settings['wc_os_products']['shredder']))?true:false);
		
					$io_ticked = (isset($wc_os_settings['wc_os_products']['io']) && in_array($prod->ID, ($wc_os_settings['wc_os_products']['io']))?true:false);
		
					$qty_ticked = (isset($wc_os_settings['wc_os_products']['quantity_split']) && in_array($prod->ID, ($wc_os_settings['wc_os_products']['quantity_split']))?true:false);
		
					
		
		
		
					$ticked = ($default_ticked || $exclusive_ticked || $inclusive_ticked || $shredder_ticked || $io_ticked || $qty_ticked);//in_array($prod->ID, $wc_os_products_ids);
		
				
		
					$product_cats = wp_get_post_terms($prod->ID, 'product_cat', array('fields' => 'names'));
		
					
		
					$multiple_cats = (count($product_cats)>1);
					$ticked_method = '';
					
					$ticked_method = ($default_ticked?'default':$ticked_method);
					$ticked_method = ($exclusive_ticked?'exclusive':$ticked_method);
					$ticked_method = ($inclusive_ticked?'inclusive':$ticked_method);
					$ticked_method = ($shredder_ticked?'shredder':$ticked_method);
					$ticked_method = ($io_ticked?'io':$ticked_method);
					$ticked_method = ($qty_ticked?'quantity_split':$ticked_method);
					
					if($ticked_method){
						$wc_os_ie_name = ($wc_os_settings['wc_os_ie']?'wc_os_settings[wc_os_products]['.$ticked_method.'][]':'wc_os_settings[wc_os_products][]');
					}
				
					$product_group = "";
		
		?>
		
		
		
		<tr data-ticked="<?php echo esc_attr($ticked_method); ?>">
		
		<td>
		
		<input id="wip-<?php echo esc_attr($prod->ID); ?>" <?php checked($ticked); ?> type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($prod->ID); ?>" class="<?php echo $ticked?'':'hides'; ?>" />
		

		
		</td>
		
		<td><label for="wio-<?php echo esc_attr($prod->ID); ?>"><?php echo wp_kses_post($product->managing_stock()?(($product->is_in_stock() && $product->get_stock_quantity()>0)?'<span class="green"><b>'.__('In', 'woo-order-splitter').'</b></span>':'<span class="red">'.__('Out', 'woo-order-splitter').'</span>'):'<small class="faded">'.__('N/A', 'woo-order-splitter').'</small>'); ?></label></td>
		
		<td><label for="wip-<?php echo esc_attr($prod->ID); ?>"><?php echo esc_html($prod->post_title.' '.$wc_os_currency.$product->get_price()); do_action('wc_os_products_list_name_column', $product); ?></label></td>
		
		<td <?php echo count($product_cats)>1?'class="wc-os-multiple-warning" title="'.$wc_os_multiple_warning.'"':''; ?>><label for="win-<?php echo esc_attr($prod->ID); ?>"><?php echo wp_kses_post(implode(', ',  $product_cats)); ?></label></td>
		
		<td class="split-actions">
		
			<select name="products_split_action[<?php echo esc_attr($prod->ID);  ?>]" id="split_action_<?php echo esc_attr($prod->ID);  ?>" class="wos_product_action">
                <option value=""></option>
                <?php

                    if(!empty($wc_os_all_action)){

                        $premium_action = array(
//                                'io',
//                                'quantity_split',
                        );

                        foreach($wc_os_all_action as $action_key => $action_title){

                            $selected_action = ((isset($wc_os_settings['wc_os_products'][$action_key]) && in_array($prod->ID, ($wc_os_settings['wc_os_products'][$action_key]))?true:false) ? 'selected' : '');
                            $disabled = disabled((!$wc_os_pro && in_array($action_key, $premium_action)));
                            echo "<option value='$action_key' $selected_action $disabled>$action_title</option>";

                        }

                    }

                ?>
		</select>
		
		
		
			<select id="group-<?php echo esc_attr($prod->ID); ?>" name="split_action[groups][<?php echo esc_attr($prod->ID); ?>]" data-ie="groups">
		
			<option value=""></option>
		
				<?php foreach($groups_arr as $v): 
		
					$k = strtolower($v);
		
					$groups_selected = (isset($wc_os_settings['wc_os_products']['groups'][$k])?$wc_os_settings['wc_os_products']['groups'][$k]:array());
                    if(!$product_group && in_array($prod->ID, $groups_selected)){
                        $product_group = $k;
                    }		
				?>
		
				<option <?php selected(in_array($prod->ID, $groups_selected)); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
		
				<?php endforeach; ?>
		
			</select>
		
		  
		
		</td>

        <?php

            if (function_exists('wc_os_get_group_status_select')) {
                wc_os_get_group_status_select('groups', $product_group);
            }

        ?>		
		<td><a href="<?php echo esc_attr(get_edit_post_link($prod->ID)); ?>" target="_blank"><?php _e('Edit', 'woo-order-splitter'); ?></a> - <a href="<?php echo esc_attr(get_permalink($prod->ID)); ?>" target="_blank"><?php _e('View', 'woo-order-splitter'); ?></a>
		
		</td>
		
		</tr>

<?php

		}
		
?>
		<tr style="display:none">
        <td colspan="6"><textarea name="wos_valid_product_actions"><?php echo esc_textarea(implode(',', $products_array)); ?></textarea></td>
        </tr>
<?php		
		
	}
	}
	
	function wc_os_underscore($str){
		return strtolower(str_replace(array(' ', '-'), '_', $str));
	}

		
	function wc_os_get_attributes_list($attrib_only=false, $offset=0, $ajax=false, $alphabetic_grouping=true){
		

		global $wc_os_settings, $wc_os_multiple_warning, $wc_os_pro, $wpdb, $wc_os_per_page;
		
		$LIMIT = ($wc_os_per_page?'LIMIT '.($offset>0?$offset:0).','.$wc_os_per_page:'');
		
		$attrib_jar = array();		
		

		$product_attributes = get_option('_transient_wc_attribute_taxonomies');		

		
		
		if(!empty($product_attributes)){			
			foreach($product_attributes as $product_attributes_arr){
				$attrib = 'pa_'.$product_attributes_arr->attribute_name;//$product_attributes_arr->attribute_id;
				$attrib_jar[$attrib]['label'] = $product_attributes_arr->attribute_label.' / '.$product_attributes_arr->attribute_type;
				$attrib_jar[$attrib]['name'] = $product_attributes_arr->attribute_name;
				$term_args = array(
					'taxonomy' => $attrib,
					'hide_empty' => false,
					'number' => 10,
				);
				$attrib_terms = get_terms( $term_args );
				if(!empty($attrib_terms)){
					foreach($attrib_terms as $attrib_term){
						$attrib_jar[$attrib]['values'][] = $attrib_term->name;
					}
				}
			}
			
		}else{
			
			
			$attributes_query = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key='_product_attributes' GROUP BY meta_value $LIMIT";
			
			$product_attributes = $wpdb->get_results($attributes_query);
			
			if(!empty($product_attributes)){
				foreach($product_attributes as $product_attributes_arr){
					$product_attributes_arr = $product_attributes_arr->meta_value;
					$attribs = maybe_unserialize($product_attributes_arr);
					foreach($attribs as $attrib=>$attrib_data){
						$attrib_jar[$attrib]['name'] = $attrib_data['name'];
						$attrib_jar[$attrib]['label'] = $attrib_jar[$attrib]['name'];
						$attrib_jar[$attrib]['values'] = (array_key_exists('values', $attrib_jar[$attrib]) && is_array($attrib_jar[$attrib]['values'])?$attrib_jar[$attrib]['values']:array());
						$attrib_data['value'] = explode('|', $attrib_data['value']);
						$attrib_data['value'] = array_filter($attrib_data['value']);
						if(!empty($attrib_data['value'])){
							foreach($attrib_data['value'] as $attrib_value){
								$attrib_value = trim($attrib_value);
								if(!in_array($attrib_value, $attrib_jar[$attrib]['values'])){
									$attrib_jar[$attrib]['values'][] = $attrib_value;
								}
							}
						}
					}
				}
			}
			
		}
		if($attrib_only){
			return $attrib_jar;
		}
		

		$groups_arr = wc_os_get_groups_range();

		$selected_attribs = $alphabetic_grouping?$wc_os_settings['wc_os_attributes']:$wc_os_settings['wc_os_attributes_values'];
		
		$wc_os_attributes_group = $wc_os_settings['wc_os_attributes_group'];
		
		
		
		$current = (isset($_POST['wos_pg'])?sanitize_wc_os_data($_POST['wos_pg']):0);
		
		$get_count = $wpdb->get_row("SELECT COUNT(*) AS total FROM ".$wpdb->prefix."postmeta WHERE meta_key='_product_attributes' GROUP BY meta_value");

		$total_attributes = $get_count->total;
						
		$total_pages = ($wc_os_per_page?ceil($total_attributes/$wc_os_per_page):0);	
		$radius = floor($total_pages/2);
				
?>
<?php if($total_pages>1 && !$ajax): ?>
<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_pagination.php')); ?>
<?php endif; ?>
<?php if(!$ajax): ?>
<?php get_wos_pg_limit_select('A'); ?>
<?php endif; ?>
<table border="0" class="wos_attributes_list_items dash-1">
<thead>

<th><input <?php checked(isset($wc_os_settings['wc_os_all_attributes']) && $wc_os_settings['wc_os_all_attributes']=='all_attributes')?> id="wc_os_all_attributes" name="wc_os_settings[wc_os_all_attributes]" type="checkbox" value="all_attributes" title="<?php _e('All listed and future attributes are included.','woo-order-splitter'); ?>" /></th>

<th><?php _e('Attribute', 'woo-order-splitter'); ?></th>

<th><?php _e('Values', 'woo-order-splitter'); ?></th>

<?php if($alphabetic_grouping): ?>
<th><?php _e('Split Group', 'woo-order-splitter'); ?></th>
<?php if(function_exists('wc_os_get_group_status_select')){ ?>
<th class="group_status_heading"><?php _e('Group Status', 'woo-order-splitter'); ?></th>
<?php } ?>
<?php endif; ?>


</thead>

<tbody>
<?php

		foreach($attrib_jar as $attrib_key=>$attrib_data){
		
					$wc_os_ie_name = $alphabetic_grouping?'wc_os_attributes[]':'wc_os_attributes_values[]';
					
					$attribute_group = '';
					
					
		
		?>
		
		
		
		<tr>
		
		<td valign="top">
		<input <?php checked(in_array($attrib_key, $selected_attribs)); ?>  id="wia-<?php echo esc_attr($attrib_key); ?>" type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($attrib_key); ?>" />
		
		</td>
		
		
		<td valign="top"><label for="wia-<?php echo esc_attr($attrib_key); ?>"><?php echo esc_html($attrib_data['label']); ?></label></td>
		
		<td><label class="avs-attribs" for="wia-<?php echo esc_attr($attrib_key); ?>"><?php echo wp_kses_post((is_array($attrib_data['values'])?implode(', ',  $attrib_data['values']):$attrib_data['values'])); ?></label>
        <?php if(function_exists('attrib_value_nodes')){ attrib_value_nodes($attrib_data['values'], $attrib_key); } ?>
        </td>

<?php if($alphabetic_grouping): ?>        
        <td class="split-group-actions">
            
        <select id="group-variation-<?php echo esc_attr($attrib_key); ?>" name="wc_os_attributes_group[<?php echo esc_attr($attrib_key); ?>]" data-ie="group_by_attributes">
    
        <option value=""></option>
    
            <?php foreach($groups_arr as $v): 
    
                $k = strtolower($v);
    
                $groups_selected = (array_key_exists($attrib_key, $wc_os_attributes_group)?$wc_os_attributes_group[$attrib_key]:array());

                if(!$attribute_group && $groups_selected == $k){
                    $attribute_group = $k;
                }                
    
            ?>
    
            <option <?php selected(array_key_exists($attrib_key, $wc_os_attributes_group) && $wc_os_attributes_group[$attrib_key]==$k); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
    
            <?php endforeach; ?>
    
        </select>        
        
        </td>
        
        <?php if(function_exists('wc_os_get_group_status_select')){ wc_os_get_group_status_select('group_by_attributes_only', $attribute_group); } ?>
        
<?php endif; ?>		
		
		</tr>

<?php

		}
?>

		<tr style="display:none">
        <td colspan="<?php echo $alphabetic_grouping?4:3; ?>"><textarea name="wos_valid_attrib_actions"><?php echo esc_textarea((is_array($attrib_jar) && !empty($attrib_jar))?implode(',', array_keys($attrib_jar)):''); ?></textarea></td>
        </tr>
</tbody>
</table>
<?php		
		
	}	
	
	
	
	function wc_os_get_order_item_meta_data($attrib_only=false, $offset=0, $ajax=false, $alphabetic_grouping=true){
		
		global $wc_os_settings, $wc_os_multiple_warning, $wc_os_pro, $wpdb, $wc_os_per_page;
		
		
		
		$LIMIT = ($wc_os_per_page?'LIMIT '.($offset>0?$offset:0).','.$wc_os_per_page:'');
		
		$where_clause = "im.meta_key NOT IN ('_line_qty', '_line_subtotal', '_line_subtotal_tax', '_line_tax', '_line_tax_data', '_line_total', '_product_id', '_qty', '_reduced_stock', '_tax_class', '_tax_status', '_variation_id', '_vendor_commission', '_vendor_order_item_id', 'Backordered', 'pa_color', 'pa_size', 'Note')";
		
		$itemmeta_query = "SELECT im.* FROM ".$wpdb->prefix."woocommerce_order_itemmeta im WHERE $where_clause GROUP BY im.meta_key";
		
		$product_attributes = $wpdb->get_results($itemmeta_query.' '.$LIMIT);
		
		
		$attrib_jar = array();
		if(!empty($product_attributes)){
			foreach($product_attributes as $product_attributes_arr){

				$values = maybe_unserialize($product_attributes_arr->meta_value);
				$attrib_jar[$product_attributes_arr->meta_key]['name'] = trim(ucwords(str_replace(array('_'), ' ', $product_attributes_arr->meta_key)));
				$attrib_jar[$product_attributes_arr->meta_key]['values'][] = (is_array($values)?implode(', ', $values):$values);

			}
		}
		

		$groups_arr = wc_os_get_groups_range();
		
		
		$selected_attribs = $alphabetic_grouping?$wc_os_settings['wc_os_order_item_meta']:$wc_os_settings['wc_os_order_item_meta'];
		$selected_attribs = (is_array($selected_attribs)?$selected_attribs:array());

		$wc_os_attributes_group = $wc_os_settings['wc_os_order_item_meta_group'];

		
		$current = (isset($_POST['wos_pg'])?sanitize_wc_os_data($_POST['wos_pg']):0);
		
		$g_query = "SELECT COUNT(*) AS total FROM ".$wpdb->prefix."woocommerce_order_itemmeta im WHERE $where_clause GROUP BY im.meta_key";

		$get_count = $wpdb->get_row($g_query);

		$total_attributes = $get_count->total;
		
	

		$total_pages = ($wc_os_per_page?floor($total_attributes/$wc_os_per_page):0);	
		$radius = floor($total_pages/2);
				
?>
<?php if($total_pages>1 && !$ajax): ?>
<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_pagination.php')); ?>
<?php endif; ?>
<?php if(!$ajax): ?>
<?php //get_wos_pg_limit_select('B'); ?>
<?php endif; ?>
<table border="0" class="wos_attributes_list_items dash-2">
<thead>

<th><input <?php checked(isset($wc_os_settings['wc_os_all_attributes']) && $wc_os_settings['wc_os_all_attributes']=='all_attributes')?> id="wc_os_all_attributes" name="wc_os_settings[wc_os_all_order_item_meta]" type="checkbox" value="all_order_item_meta" title="<?php _e('All listed and future order item meta are included.','woo-order-splitter'); ?>" /></th>

<th><?php _e('Meta Key', 'woo-order-splitter'); ?></th>

<th><?php _e('Recorded Values', 'woo-order-splitter'); ?><br /><small>(<?php _e('for example purpose only', 'woo-order-splitter'); ?>)</small></th>

<?php if($alphabetic_grouping): ?>
<th><?php _e('Split Group', 'woo-order-splitter'); ?></th>
<?php if(function_exists('wc_os_get_group_status_select')){ ?>
<th class="group_status_heading"><?php _e('Group Status', 'woo-order-splitter'); ?></th>
<?php } ?>
<?php endif; ?>


</thead>

<tbody>
<?php

		foreach($attrib_jar as $attrib_key=>$attrib_data){
		
					$wc_os_ie_name = $alphabetic_grouping?'wc_os_order_item_meta[]':'wc_os_order_item_meta[]';
					
					$attribute_group = '';
					
					
		
		?>
		
		
		
		<tr>
		
		<td>
		
		<input <?php checked(in_array($attrib_key, $selected_attribs)); ?>  id="wia-<?php echo esc_attr($attrib_key); ?>" type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($attrib_key); ?>" />
		
		</td>
		
		
		<td><label for="wia-<?php echo esc_attr($attrib_key); ?>"><?php echo esc_html($attrib_data['name']); ?></label></td>
		
		<td><label for="wia-<?php echo esc_attr($attrib_key); ?>"><?php echo wp_kses_post(implode(', ',  $attrib_data['values'])); ?></label></td>

<?php if($alphabetic_grouping): ?>        
        <td class="split-group-actions">
            
        <select id="group-variation-<?php echo esc_attr($attrib_key); ?>" name="wc_os_order_item_meta_group[<?php echo esc_attr($attrib_key); ?>]" data-ie="group_by_attributes">
    
        <option value=""></option>
    
            <?php foreach($groups_arr as $v): 
    
                $k = strtolower($v);
    
                $groups_selected = (array_key_exists($attrib_key, $wc_os_attributes_group)?$wc_os_attributes_group[$attrib_key]:array());

                if(!$attribute_group && $groups_selected == $k){
                    $attribute_group = $k;
                }                
    
            ?>
    
            <option <?php selected(array_key_exists($attrib_key, $wc_os_attributes_group) && $wc_os_attributes_group[$attrib_key]==$k); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
    
            <?php endforeach; ?>
    
        </select>        
        
        </td>
        
        
<?php endif; ?>		
		
		</tr>

<?php

		}
?>

		<tr style="display:none">
        <td colspan="<?php echo $alphabetic_grouping?4:3; ?>"><textarea name="wos_valid_order_item_meta"><?php echo esc_textarea((is_array($attrib_jar) && !empty($attrib_jar))?implode(',', array_keys($attrib_jar)):''); ?></textarea></td>
        </tr>
</tbody>
</table>
<?php		
		
	}		
	
	add_action( 'wp_ajax_wos_load_paginated', 'wos_load_paginated' );
	
	function wos_load_paginated(){

		if (
			! isset( $_POST['wc_os_settings_field'] )
			|| ! wp_verify_nonce( $_POST['wc_os_settings_field'], 'wc_os_settings_action' )
		) {
	
		   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
		   exit;
	
		} else {		
		
			$posted = sanitize_wc_os_data($_POST);
		
			global $wc_os_per_page;
			$current = $posted['num'];
			
			switch($posted['wos_list_type']){
				case 'products_list':
					$products = wc_os_get_products($wc_os_per_page, $current);
					wc_os_get_products_list($products);				
				break;
				case 'attributes_list':
					wc_os_get_attributes_list(false, ($current-1)*$wc_os_per_page, true);
				break;
				case 'attributes_values':
					wc_os_get_attributes_list(false, ($current-1)*$wc_os_per_page, true, false);
				break;
				case 'order_item_meta':
					wc_os_get_order_item_meta_data(false, ($current-1)*$wc_os_per_page, true, false);
				break;	
				case 'categories_qty':
					$product_categories = wc_os_get_product_categories($current, $wc_os_per_page);
					wc_os_get_product_categories_qty($product_categories);
				break;	
				case 'categories_list':
					$product_categories = wc_os_get_product_categories($current, $wc_os_per_page);
					wc_os_get_product_categories_list($product_categories);
				break;			
			}
			exit;
		}
	}
	
	
	add_action( 'wp_ajax_wos_items_paginated', 'wos_items_paginated' );
	
	function wos_items_paginated(){

		if (
			! isset( $_POST['wc_os_settings_field'] )
			|| ! wp_verify_nonce( $_POST['wc_os_settings_field'], 'wc_os_settings_action' )
		) {
	
		   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
		   exit;
	
		} else {		
		
			$wos_pg_limit = sanitize_wc_os_data($_POST['wos_pg_limit']);
		
			update_option('wos_pg_limit', $wos_pg_limit);
			
			
			exit;
		}
	}	
	
	
	function get_wos_pg_limit_select($placeholder='default'){
		global $wc_os_per_page, $wc_os_products_per_order, $wc_os_settings;
		
		$wc_os_ie = $wc_os_settings['wc_os_ie'];
		
?>
<div class="wos_pg_limit_wrapper" data-placeholder="<?php echo $placeholder; ?>">
<?php 
	switch($wc_os_ie){
			case 'default':
?>			
	<label for="wc_os_products_per_order"><?php _e('Items Per Order', 'woo-order-splitter'); ?>: <input type="text" name="wc_os_products_per_order" value="<?php echo $wc_os_products_per_order; ?>" /></label>
<?php
			break;
	}
?>	
    
    
	<label for="wos_pg_limit"><?php _e('Items Per Page', 'woo-order-splitter'); ?>:</label>
    <select name="wos_pg_limit" id="wos_pg_limit">        
        <option <?php selected($wc_os_per_page==''); ?> value=""><?php _e('All', 'woo-order-splitter'); ?></option>
        <option <?php selected($wc_os_per_page==5); ?> value="5">5</option>
        <option <?php selected($wc_os_per_page==10); ?> value="10">10</option>
        <option <?php selected($wc_os_per_page==50); ?> value="50">50</option>
        <option <?php selected($wc_os_per_page==100); ?> value="100">100</option>
        <option <?php selected($wc_os_per_page==250); ?> value="250">250</option>
        <option <?php selected($wc_os_per_page==500); ?> value="500">500</option>
        <option <?php selected($wc_os_per_page==1000); ?> value="1000">1000</option>
        
    </select>
</div>
<?php		
	}
	function wc_os_recursive_array_search($needle, $haystack, $currentKey = '') {
		foreach($haystack as $key=>$value) {
			if (is_array($value)) {
				$nextKey = wc_os_recursive_array_search($needle, $value, $key);
				if ($nextKey) {
					return $nextKey;
				}
			}
			else if($value==$needle) {
				
				return $currentKey;
			}
		}
		return false;
	}
	
	function wc_os_get_product_categories_qty($product_categories=array()){
		
		global $wc_os_settings, $wc_os_general_settings, $wc_os_pro;
		
		if(!empty($product_categories)){
			
			$groups_arr = wc_os_get_groups_range();
			$ticked = '';
			
			$wc_os_extend_groups = array_key_exists('wc_os_extend_groups', $wc_os_general_settings);
			$group_cats = $wc_os_settings['wc_os_cats']['cats'];
			$group_cats = is_array($group_cats)?$group_cats:array();

			
			
			$valid_cats_array = array();
			
			foreach ($product_categories as $key => $category) {
				$valid_cats_array[] = $category->term_id;
				$wc_os_ie_name = 'wc_os_cats[cats]['.$category->term_id.']';
				
				$split_group = wc_os_recursive_array_search($category->term_id, $group_cats);
				

				
?>
<tr>

<td>

<input id="wic-<?php echo esc_attr($category->term_id); ?>" <?php checked($split_group!=''); ?> type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($category->term_id); ?>" /></td>

<td><a target="_blank" href="<?php echo admin_url(); ?>edit.php?product_cat=<?php echo esc_attr($category->slug); ?>&post_type=product"><?php echo esc_html($category->count); ?></a></td>

<td><label for="wic-<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></label></td>

<td class="split-actions" data-id="<?php echo esc_attr($category->term_id); ?>">

	<input type="text" name="split_action[cats][<?php echo esc_attr($category->term_id); ?>]" value="<?php echo ($split_group?$split_group:''); ?>" />

</td>

<td><a href="<?php echo esc_attr(get_edit_term_link($category)); ?>" target="_blank"><?php _e('Edit', 'woo-order-splitter'); ?></a> - <a href="<?php echo esc_attr(get_term_link($category)); ?>" target="_blank"><?php _e('View', 'woo-order-splitter'); ?></a>

</td>

</tr>

<?php		
			}
			
?>
<tr style="display:none">
	<td colspan="5"><textarea name="wos_valid_category_actions"><?php echo esc_textarea(implode(',', $valid_cats_array)); ?></textarea></td>
</tr>
<?php			

		}
	}
	
	function wc_os_get_product_categories_list($product_categories=array()){
		
		global $wc_os_settings, $wc_os_general_settings, $wc_os_pro, $wc_os_delivery_date_activated, $wc_os_days;
		
		if(!empty($product_categories)){
			
			$groups_arr = wc_os_get_groups_range();
			$ticked = '';
			
			$wc_os_extend_groups = array_key_exists('wc_os_extend_groups', $wc_os_general_settings);
			$group_cats = $wc_os_settings['wc_os_cats']['group_cats'];
			$group_cats = is_array($group_cats)?$group_cats:array();
		
			
			$valid_cats_array = array();
			
			$wc_os_category_weekday = get_option('wc_os_category_weekday');
			$wc_os_category_weekday = is_array($wc_os_category_weekday)?$wc_os_category_weekday:array();

						
			foreach ($product_categories as $key => $category) {
				$valid_cats_array[] = $category->term_id;
				$wc_os_ie_name = 'wc_os_cats[]';
				$category_group = "";
				
				$category_name = $category->name;
				
				if($wc_os_delivery_date_activated && array_key_exists($category->term_id, $wc_os_category_weekday)){
					
					
					$di = $wc_os_category_weekday[$category->term_id];
					if($di>=0){
						$category_name .= ' / <small title="'.get_option( 'orddd_delivery_date_field_label' ).'" class="wc-os-cat-weekday">'.$wc_os_days[$di].'</small>';
					}
				}
				
?>
<tr>

<td><input id="wic-<?php echo esc_attr($category->term_id); ?>" <?php checked($ticked); ?> type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($category->term_id); ?>" class="<?php echo $ticked?'':'hides'; ?>" /></td>

<td><a target="_blank" href="<?php echo admin_url(); ?>edit.php?product_cat=<?php echo esc_attr($category->slug); ?>&post_type=product"><?php echo esc_html($category->count); ?></a></td>

<td><label for="wic-<?php echo esc_attr($category->term_id); ?>"><?php echo wp_kses_post($category_name); ?></label></td>

<td class="split-actions" data-id="<?php echo esc_attr($category->term_id); ?>">



	<ul>

    	<li><input id="default-<?php echo esc_attr($category->term_id); ?>" type="radio" value="default" name="split_action[<?php echo esc_attr($category->term_id); ?>]" /><label for="default-<?php echo esc_attr($category->term_id); ?>"><?php echo split_actions_display('default', 'title'); ?></label></li>

        <li><input id="exclusive-<?php echo esc_attr($category->term_id); ?>" type="radio" value="exclusive" name="split_action[<?php echo esc_attr($category->term_id); ?>]" /><label for="exclusive-<?php echo esc_attr($category->term_id); ?>"><?php echo split_actions_display('exclusive', 'title'); ?></label></li>

        <li><input id="inclusive-<?php echo esc_attr($category->term_id); ?>" type="radio" value="inclusive" name="split_action[<?php echo esc_attr($category->term_id); ?>]" /><label for="inclusive-<?php echo esc_attr($category->term_id); ?>"><?php echo split_actions_display('inclusive', 'title'); ?></label></li>

        <li><input id="shredder-<?php echo esc_attr($category->term_id); ?>" type="radio" value="shredder" name="split_action[<?php echo esc_attr($category->term_id); ?>]" /><label for="shredder-<?php echo esc_attr($category->term_id); ?>"><?php echo split_actions_display('shredder', 'title'); ?></label></li>

        <li><input id="io-<?php echo esc_attr($category->term_id); ?>"  <?php disabled(!$wc_os_pro); ?> type="radio" value="io" name="split_action[<?php echo esc_attr($category->term_id); ?>]" /><label for="io-<?php echo esc_attr($category->term_id); ?>"><?php echo split_actions_display('io', 'title'); ?></label></li>

        <li><input id="none-<?php echo esc_attr($category->term_id); ?>" type="radio" value="none" name="split_action[<?php echo esc_attr($category->term_id); ?>]" /><label for="none-<?php echo esc_attr($category->term_id); ?>"><?php echo split_actions_display('none', 'title'); ?></label></li>

    </ul>

    

    <select id="group-cat-<?php echo esc_attr($category->term_id); ?>" name="split_action[group_cats][<?php echo esc_attr($category->term_id); ?>]" data-ie="group_cats">

    <option value=""></option>

    <optgroup label="<?php _e('Alphabetical', 'woo-order-splitter'); ?>">

    	<?php foreach($groups_arr as $v): 

			$k = strtolower($v);

			$groups_selected = (array_key_exists($k, $group_cats)?$group_cats[$k]:array());
            if(!$category_group && in_array($category->term_id, $groups_selected)){
                $category_group = $k;
            }
		?>

        <option <?php selected(in_array($category->term_id, $groups_selected)); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>

        <?php endforeach; ?>

	</optgroup>  

    <?php if($wc_os_extend_groups): ?>

    <optgroup label="<?php _e('Categorial', 'woo-order-splitter'); ?>">

    	<?php foreach ($product_categories as $keyi => $categoryi):

			

			$groups_selected = (isset($wc_os_settings['wc_os_products']['group_cats'][$categoryi->term_id])?$wc_os_settings['wc_os_products']['group_cats'][$categoryi->term_id]:array());
            if(!$category_group && in_array($category->term_id, $groups_selected)){
                $category_group = $keyi;
            }
		?>

        <option <?php selected(in_array($categoryi->term_id, $groups_selected)); ?> value="<?php echo esc_attr($categoryi->term_id); ?>"><?php echo esc_html($categoryi->name); ?></option>

        <?php endforeach; ?>

	</optgroup>            

    <?php endif; ?>

    </select>

</td>
<?php if(function_exists('wc_os_get_group_status_select')){wc_os_get_group_status_select('group_cats', $category_group);} ?>
<td><a href="<?php echo esc_attr(get_edit_term_link($category)); ?>" target="_blank"><?php _e('Edit', 'woo-order-splitter'); ?></a> - <a href="<?php echo esc_attr(get_term_link($category)); ?>" target="_blank"><?php _e('View', 'woo-order-splitter'); ?></a>

</td>

</tr>

<?php		
			}
			
?>
<tr style="display:none">
	<td colspan="5"><textarea name="wos_valid_category_actions"><?php echo esc_textarea(implode(',', $valid_cats_array)); ?></textarea></td>
</tr>
<?php			

		}
	}
	


    if(!function_exists('wc_os_get_statuses_select_html')){
        function wc_os_get_statuses_select_html($input_name, $field_prefix, $wc_os_order_statuses, $package_title=true, $label_prefix='', $type=''){
			
			$wos_packages_strings = (function_exists('wc_os_get_packages_strings')?wc_os_get_packages_strings():array());
			
            ?>

            <select name="<?php echo esc_attr($input_name.'['.$field_prefix.'_status]');?>">

                <option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

                <?php foreach($wc_os_order_statuses as $order_status){ ?>
                
<?php
	$selected = '';
	switch($type){
		default:
			$label_prefix = ($label_prefix?$label_prefix:__('Change', 'woo-order-splitter').' '.__('to', 'woo-order-splitter').' ');
			$selected = function_exists('wc_os_get_io_setting')?($order_status==wc_os_get_io_setting($field_prefix.'_status')):'';
		break;
		case 'email':
			$selected = function_exists('wc_os_get_status_setting')?($order_status==wc_os_get_status_setting($field_prefix.'_status')):'';
		break;
	}
?>	

                    <option value="<?php echo esc_attr($order_status); ?>" <?php echo selected($selected, true, false); ?>><?php echo $label_prefix.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

                <?php } ?>

            </select>
            
            
			<?php if($package_title): ?>

            <p style="margin-top:10px;"><?php _e('Package Title', 'woo-order-splitter'); ?>: <small>(<?php _e('Example', 'woo-order-splitter'); ?>: <?php _e('Parcel #', 'woo-order-splitter'); ?>1)</small></p>

            <input name="<?php echo esc_attr($input_name.'['.$field_prefix.'_heading]');?>" value="<?php echo function_exists('wc_os_get_io_setting')?wc_os_get_io_setting($field_prefix.'_heading'):''; ?>" type="text" placeholder="<?php echo (isset($wos_packages_strings['parcel-heading']) && trim($wos_packages_strings['parcel-heading'])!=''?$wos_packages_strings['parcel-heading']:__('Parcel #', 'woo-order-splitter')); ?>" />
			<?php endif; ?>
<?php if($field_prefix=='out_stock'): ?>
<?php $outstock_automation = (wc_os_get_io_setting($field_prefix.'_automation')=='yes'?'yes':'no');  ?>
<a title="<?php _e('Backorder Automation', 'woo-order-splitter'); ?> (<?php _e('Optional', 'woo-order-splitter'); ?>)" class="backorder-automation <?php echo ($outstock_automation=='yes'?'selected':''); ?>"><i class="fas fa-exchange-alt"></i><small><?php _e('Split again whenever stock is available.', 'woo-order-splitter'); ?></small></a>

<input type="hidden" id="backorder-automation" name="<?php echo esc_attr($input_name.'['.$field_prefix.'_automation]');?>" value="<?php echo esc_attr($outstock_automation); ?>" />
<a title="<?php _e('Click here to see backorder automation in action', 'woo-order-splitter'); ?>" href="https://www.youtube.com/embed/AWBLwmF_Op0" target="_blank" class="backorder-automation-tutorial"><i class="fab fa-youtube"></i></a>

<a title="<?php _e('Click here to see backorder automation in action for multi-tier split', 'woo-order-splitter'); ?>" href="https://www.youtube.com/embed/jHKa4NZ26Tc" target="_blank" class="backorder-automation-tutorial"><i style="color:#FFEF00" class="fab fa-youtube"></i></a>
<?php endif; ?>



            <?php
        }
    }


    if(!function_exists('wc_os_get_io_setting')){
        function wc_os_get_io_setting($name, $default = ''){
            $io_options = wc_os_quick_get('wc_os_io_options');
            return array_key_exists($name, $io_options) ? $io_options[$name] : $default;

        }
    }
	
    if(!function_exists('wc_os_get_status_setting')){
        function wc_os_get_status_setting($name, $default = ''){
            $status_setting = get_option('wc_os_status_setting', array());
			$status_setting = is_array($status_setting)?$status_setting:array();
            return array_key_exists($name, $status_setting) ? $status_setting[$name] : $default;

        }
    }	


    if(!function_exists('wc_os_get_acf_field_values')){
        function wc_os_get_acf_field_values($field_ids = array()){

            global $is_acf, $wpdb;
            $values_array = array();
            if($is_acf && !empty($field_ids)){

                $current_field_id = current($field_ids);
                $acf_field = acf_get_field($current_field_id);
                $field_name = $acf_field['name'];
                $value_query = "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '$field_name' ORDER BY meta_value";
                $values_result = $wpdb->get_results($value_query, ARRAY_N);
                $values_result = array_map('current', $values_result);
                if(!empty($values_result)){

                    $values_array[$field_name] = $values_result;

                }
            }
            return $values_array;


        }
    }


    if(!function_exists('wc_os_acf_method_settings_html')){
        function wc_os_acf_method_settings_html(){

            global $wc_os_settings, $is_acf, $wos_acf_string, $wc_os_acf_settings;


            if($wc_os_settings['wc_os_ie'] != 'group_by_acf_group_fields'){return;}

            $wc_os_acf_settings = get_option('wc_os_acf_settings', array());
            ?>


            <div class="wos_acf_wrapper">
                <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
					<div class="wc-os-plugin-icon"></div>
                    <label></label>

                    <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

                    <?php wp_nonce_field( 'wc_os_acf_fields_action', 'wc_os_acf_fields_nonce' ); ?>


                    <div class="wc_os_acf_values_list">
                        <h3 style="display: none;">
                            <?php echo split_actions_display('group_by_acf_group_fields', 'title') ?>
                        </h3>
                    </div>




                    <div class="wc_os_acf_values_list" >


                        <?php

                        if(!$is_acf):


                            ?>


                            <div class="wc_os_acf_notice">
                                <?php echo ($wos_acf_string); ?>
                            </div>


                        <?php

                        else:


                            $wc_os_acf_fields = $wc_os_acf_settings;
                            $wc_os_acf_group_selection = get_option('wc_os_acf_group_selection', array());
							


                            $group_args = array(

                                'numberposts' => -1,
                                'post_status' => 'any',
                                'post_type' => 'acf-field-group',
                                'post__in' => $wc_os_acf_group_selection,

                            );

                            $selected_groups = get_posts($group_args);

                            if(!empty($wc_os_acf_group_selection) && !empty($selected_groups)){
                                ?>

                                <div class="wos-accordion-wrapper">


                                    <?php
                                    foreach($selected_groups as $index => $single_group){


                                        $fields_args = array(
                                            'numberposts' => -1,
                                            'post_status' => 'any',
                                            'post_type' => 'acf-field',
                                            'post_parent' => $single_group->ID,
                                        );

                                        $acf_group_object = acf_get_field_group($single_group->ID);
                                        $is_belong_to_product = acf_get_field_group_visibility($acf_group_object, array('post_type' => 'product'));
                                        $split_notice = __('On checkout, parcels will be formed by the items with the identical values found against the selected field', 'woo-order-splitter');
                                        $split_notice .= ' "<span class="acf_split_notice_field"></span>" ';
                                        $split_notice .= __('among cart items.', 'woo-order-splitter');

                                        $group_fields = get_posts($fields_args);
                                        $alert_icon = ($is_belong_to_product ? 'yes-alt success' : 'warning danger');
                                        $notice_class = ($is_belong_to_product ? 'split' : '');

                                        ?>
                                        <button class="wos-accordion"><span class="dashicons dashicons-<?php echo esc_attr($alert_icon); ?>"></span> <?php echo esc_html($single_group->post_title); ?> <span class="dashicons dashicons-plus wos-accordion-plus a_icon"></span><span class="dashicons dashicons-minus wos-accordion-minus a_icon"></span></button>

                                        <div class="panel">


                                            <?php

                                            if(!$is_belong_to_product):
                                                $split_notice = __("Warning! This group of fields do not belong to the WooCommerce Products.", 'woo-order-splitter');
                                            endif;
                                            ?>

                                            <div class="wc_os_acf_notice <?php echo esc_attr($notice_class); ?>">
                                                <?php echo $split_notice; ?>
                                            </div>
                                            <table class="group_fields_table" border="0">
                                                <thead>

                                                <tr>

                                                    <th class="group_fields_checkbox"></th>

                                                    <th><?php _e('Field Name', 'woo-order-splitter')  ?></th>

                                                    <th><?php _e('Values', 'woo-order-splitter') ?></th>



                                                </tr>

                                                </thead>

                                                <tbody>

                                                <?php

                                                if(!empty($group_fields)){

                                                    foreach($group_fields as $field_index => $single_field){

                                                        ?>

                                                        <tr>

                                                            <td>

                                                                <input <?php echo disabled(!$is_belong_to_product); ?> <?php echo checked(in_array($single_field->ID, $wc_os_acf_fields)); ?> type="radio" name="wc_os_acf_fields[]" value="<?php echo esc_attr($single_field->ID); ?>">

                                                            </td>

                                                            <td class="title">
                                                                <?php echo esc_html($single_field->post_title); ?>
                                                            </td>

                                                            <td>
                                                                <?php
                                                                if(in_array($single_field->ID, $wc_os_acf_fields)){

                                                                    $field_unique_values = wc_os_get_acf_field_values($wc_os_acf_fields);

                                                                    if(!empty($field_unique_values)){
                                                                        $field_name = current(array_keys($field_unique_values));
                                                                        $filed_values = $field_unique_values[$field_name];

                                                                        echo "<div class='wos_acf_values close'><span class='toggle'>$field_name <span class='dashicons'></span></span><ul>";
                                                                        if(!empty($filed_values)){
                                                                            foreach($filed_values as $field_value){
                                                                                echo "<li>$field_value</li>";
                                                                            }
                                                                        }
                                                                        echo "</ul></div>";
                                                                    }else{

                                                                        _e('Values not found', 'woo-order-splitter');
                                                                    }

                                                                }
                                                                ?>
                                                            </td>



                                                        </tr>

                                                        <?php

                                                    }


                                                }


                                                ?>


                                                </tbody>
                                            </table>

                                        </div>


                                        <?php

                                    }

                                    ?>

                                </div>

                                <p class="submit" style="display: block;">
                                    <input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit">
                                </p>
                                <?php

                            }else{



                                ?>

                                <div class="wc_os_acf_notice">

                                    <?php _e('No ACF group selected, please select groups from method selection to display fields.', 'woo-order-splitter'); ?> <small>(<?php _e('Screen Options', 'woo-order-splitter'); ?>)</small>
                                    
                                    <a class="wc-os-screen-options"><?php _e('Click here to select groups', 'woo-order-splitter'); ?></a>
                                </div>

                                <?php

                            }

                        endif;

                        ?>

                    </div>

                </form>
            </div>


            <?php

        }
    }



    if(!function_exists('wc_os_get_product_group_by_acf')){
        function wc_os_get_product_group_by_acf($acf_field, $product_ids){
            global $is_acf, $wpdb;
            $product_groups = array();

            if($is_acf && !empty($product_ids)){

                $acf_field_data = acf_get_field($acf_field);
                $acf_field_name = $acf_field_data['name'];
                $product_ids_str = implode(', ', $product_ids);
                $query = "SELECT `post_id`, `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = '$acf_field_name' AND `post_id` IN ($product_ids_str) ORDER BY `meta_value`";
                $query_results = $wpdb->get_results($query, ARRAY_A);

                if(!empty($query_results)){
                    foreach($query_results as $index => $single_group){
                        $group_value = trim($single_group['meta_value']);
                        $product_id = trim($single_group['post_id']);
                        $group_value = strtolower($group_value);
                        $product_groups[$group_value][] = $product_id;
                    }
                }

            }
            return $product_groups;

        }
    }
    if(!function_exists('wc_os_get_group_status_select')){
        function wc_os_get_group_status_select($split_action, $selected_group = ''  ){
            global $wc_os_settings;

            $wc_os_order_statuses = wc_get_order_statuses();
            $wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);
            $input_name = ($selected_group ? "wc_os_group_statuses[$split_action][$selected_group]" : '');
            $all_group_statuses = (array_key_exists('wc_os_group_statuses', $wc_os_settings) ? $wc_os_settings['wc_os_group_statuses'] : array());
            $current_action_status = (array_key_exists($split_action, $all_group_statuses) && is_array($all_group_statuses[$split_action]) ? $all_group_statuses[$split_action] : array());
            $selected_status = ($selected_group && array_key_exists($selected_group, $current_action_status) ? $current_action_status[$selected_group] : '');


            ?>

            <td class="wc_os_group_status wc_<?php echo esc_attr($split_action); ?>">

                <select name="<?php echo esc_attr($input_name); ?>" data-action="<?php echo esc_attr($split_action); ?>">

                    <option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

                    <?php

                    foreach($wc_os_order_statuses_keys as $order_status){

                        $selected = selected($order_status == $selected_status)

                        ?>

                        <option value="<?php echo esc_attr($order_status); ?>" <?php echo $selected; ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

                    <?php } ?>

                </select>
            </td>

            <?php

        }
    }

    if(!function_exists('wc_os_save_group_statuses')){
        function wc_os_save_group_statuses(&$wc_os_settings){

            if(isset($_POST['wc_os_group_statuses'])){

                $posted_group_statuses = sanitize_wc_os_data($_POST['wc_os_group_statuses']);
                $existing_group_statuses = (array_key_exists('wc_os_group_statuses', $wc_os_settings) ? $wc_os_settings['wc_os_group_statuses'] : array());

                if(!empty($posted_group_statuses)){
                    foreach($posted_group_statuses as $method => $current_statuses){
                        $existing_current_method = (array_key_exists($method, $existing_group_statuses) ? $existing_group_statuses[$method] : array());

                        if(!empty($existing_current_method)){
                            foreach($existing_current_method as $group => $status){
                                if(array_key_exists($group, $current_statuses)){
                                    $existing_current_method[$group] = $current_statuses[$group];
                                    unset($current_statuses[$group]);
                                }
                            }
                        }


                        $existing_current_method = array_merge($existing_current_method, $current_statuses);
                        $existing_current_method = array_filter($existing_current_method);
                        $existing_group_statuses[$method] = $existing_current_method;
                    }
                }

                $wc_os_settings['wc_os_group_statuses'] = $existing_group_statuses;

            }
        }
    }

    if(!function_exists('wc_os_statuses_by_action')){
        function wc_os_statuses_by_action($action){
            global $wc_os_settings;
            $existing_group_statuses = (array_key_exists('wc_os_group_statuses', $wc_os_settings) ? $wc_os_settings['wc_os_group_statuses'] : array());
            return (array_key_exists($action, $existing_group_statuses) && is_array($existing_group_statuses[$action]) ? $existing_group_statuses[$action] : array());
        }
    }

    if(!function_exists('wc_os_get_group_status')){
        function wc_os_get_group_status($action, $group_name){

            $action_statuses = wc_os_statuses_by_action($action);
            $group_status = '';

            if(!empty($action_statuses)){

                $group_status = (array_key_exists($group_name, $action_statuses) ? $action_statuses[$group_name] : '');

            }

            return $group_status;

        }
    }

    if(!function_exists('wc_os_update_group_order_status')){

        function wc_os_update_group_order_status($child_orders=array(), $original_order_id=0){

            global $wc_os_settings;

            $wc_os_ie = array_key_exists('wc_os_ie', $wc_os_settings) ? $wc_os_settings['wc_os_ie'] : '';
			

            if(!$wc_os_ie){ return; }

            if(!empty($child_orders)){
				
                foreach($child_orders as $child_id){
					$_wos_custom_status_update = wc_os_get_order_meta($child_id, '_wos_custom_status_update', true);
                    $group_name = wc_os_get_order_meta($child_id, '_wos_group_name', true);
					$group_status = wc_os_get_group_status($wc_os_ie, $group_name);
					


                    if($group_name && $_wos_custom_status_update!=$group_status){

						

                        if($group_status && function_exists('wc_os_update_order_status')){
							
							


							wc_os_set_order_status($child_id, $group_status, true, 3045);//force 10-12-2021
                            wc_os_update_order_meta($child_id, '_wos_custom_status_update', $group_status);

                        }

                    }else{
						
					}

                }
            }
			
			
			
        }

    }	
	if(!function_exists('wc_os_add_prefix')){
		function wc_os_add_prefix($str, $prefix='', $line_no=''){
			
			$debug_backtrace = debug_backtrace();
			
			$function = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];
			$function .= ' / $str: '.$str;
			$function .= ' / line no. '.($line_no?$line_no:'2818');
			
			//wc_os_logger('debug', $function, true);
						
			return $prefix.str_replace($prefix, '', $str);
		}
	}
	if(!function_exists('wc_os_remove_str')){
		function wc_os_remove_str($str, $prefix){
			return str_replace($prefix, '', $str);
		}
	}
    if(!function_exists('wc_os_partial_payment_html')){
        function wc_os_partial_payment_html(){

            global $wc_os_settings, $is_wc_booking, $is_partial_addon;


            if($wc_os_settings['wc_os_ie'] != 'group_by_partial_payment' || !$is_wc_booking){return;}

            $wc_os_partial_payment = get_option('wc_os_partial_payment', array());
            $split_type = (array_key_exists('split_type', $wc_os_partial_payment) && $wc_os_partial_payment['split_type'] ? $wc_os_partial_payment['split_type'] : 'none');
            $group_by = (array_key_exists('group_by', $wc_os_partial_payment) && is_array($wc_os_partial_payment['group_by']) ? $wc_os_partial_payment['group_by'] : array());
            $group_by_date = (array_key_exists('date', $group_by) && is_array($group_by['date']) ? $group_by['date'] : array());
            $group_by_payment = (array_key_exists('payment', $group_by) && $group_by['payment'] ? $group_by['payment'] : 'none');
?>


                <form class="wc_os_partial_wrapper" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

                    <label></label>

                    <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

                    <?php wp_nonce_field( 'wc_os_partial_payment_action', 'wc_os_partial_payment_nonce' ); ?>


                    <div class="wc_os_partial_payment_list">
                        <h3 style="display: none;">
                            <?php echo split_actions_display('group_by_partial_payment', 'title') ?>
                        </h3>
                    </div>







                    <div class="wc_os_partial_payment_list" >

                        <div style="margin-bottom: 20px;">
                            <strong><?php _e('Note'); ?>: </strong>
                            <span>
                                <?php _e("After successfully order split all bookings will be attached to child orders and detached from parent order items", 'woo-order-splitter') ?>
                            </span>
                        </div>



                        <div class="wos_form_group">
                            <label for="wc_os_pp_none">
                                <input type="radio" id="wc_os_pp_none" name="wc_os_partial_payment[split_type]" <?php echo checked($split_type == 'none'); ?>  value="none">
                                <?php _e('None', 'woo-order-splitter'); ?>
                            </label>
                        </div>

                        <div class="wos_form_group">
                            <label for="wc_os_pp_split_all">
                                <input type="radio" id="wc_os_pp_split_all" name="wc_os_partial_payment[split_type]" <?php echo checked($split_type == 'all'); ?> value="all">
                                <?php _e('Split all', 'woo-order-splitter'); ?>
                            </label>
                        </div>

                        <div class="wos_form_group">
                            <label for="wc_os_pp_split_group">
                                <input type="radio" id="wc_os_pp_split_group" name="wc_os_partial_payment[split_type]" <?php echo checked($split_type == 'group_by'); ?> value="group_by">
                                <?php _e('Group by', 'woo-order-splitter'); ?>
                            </label>
                        </div>



                        <div class="wc_os_payment_group_by" style="<?php echo ($split_type == 'group_by' ? '' : 'display :none;'); ?>">



                            <fieldset>
                                <legend><?php _e('Booking Date', 'woo-order-splitter');?></legend>

                                <div class="wos_form_group">
                                    <label for="wc_os_group_day">
                                        <input type="checkbox" id="wc_os_group_day" name="wc_os_partial_payment[group_by][date][]" <?php echo checked(in_array('day', $group_by_date)); ?>  value="day">
                                        <?php _e('Day', 'woo-order-splitter'); ?>
                                    </label>
                                </div>

                                <div class="wos_form_group">
                                    <label for="wc_os_group_month">
                                        <input type="checkbox" id="wc_os_group_month" name="wc_os_partial_payment[group_by][date][]" <?php echo checked(in_array('month', $group_by_date)); ?> value="month">
                                        <?php _e('Month', 'woo-order-splitter'); ?>
                                    </label>
                                </div>

                                <div class="wos_form_group">
                                    <label for="wc_os_group_year">
                                        <input type="checkbox" id="wc_os_group_year" name="wc_os_partial_payment[group_by][date][]" <?php echo checked(in_array('year', $group_by_date)); ?> value="year">
                                        <?php _e('Year', 'woo-order-splitter'); ?>
                                    </label>
                                </div>

                                <div class="wc_os_group_by_date_msg">
                                    <strong><?php _e('Split note', 'woo-order-splitter')?>: </strong>
                                    <span class="wc_os_msg"></span>
                                </div>

                            </fieldset>



                            <?php

                                if($is_partial_addon){

                            ?>

                                <strong>
                                    <?php _e('And', 'woo-order-splitter'); ?>
                                </strong>

                                <fieldset>
                                    <legend><?php _e('Payment Type', 'woo-order-splitter');?></legend>
                                    <div class="wos_form_group">
                                        <label for="wc_os_payment_none">
                                            <input type="radio" id="wc_os_payment_none" name="wc_os_partial_payment[group_by][payment]" <?php echo checked($group_by_payment == 'none'); ?> value="none">
                                            <?php _e('None', 'woo-order-splitter'); ?>
                                        </label>
                                    </div>

                                    <div class="wos_form_group">
                                        <label for="wc_os_payment_partial">
                                            <input type="radio" id="wc_os_payment_partial" name="wc_os_partial_payment[group_by][payment]" <?php echo checked($group_by_payment == 'full'); ?> value="full">
                                            <?php _e('Full Payment', 'woo-order-splitter'); ?>
                                        </label>
                                    </div>

                                    <div class="wos_form_group">
                                        <label for="wc_os_payment_full">
                                            <input type="radio" id="wc_os_payment_full" name="wc_os_partial_payment[group_by][payment]" <?php echo checked($group_by_payment == 'partial'); ?> value="partial">
                                            <?php _e('Partial Payment', 'woo-order-splitter'); ?>
                                        </label>
                                    </div>

                                </fieldset>


                            <?php

                                }
                            ?>

                        </div>


                        <p class="submit" style="display: block;">
                            <input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit">
                        </p>


                    </div>

                </form>



<?php

        }
    }	
	
	add_action( 'wp_ajax_wc_os_search_products_ajax', 'wc_os_search_products_ajax' );



    function wc_os_search_products_ajax(){

        if (
            ! isset( $_POST['wc_os_settings_field'] )
            || ! wp_verify_nonce( $_POST['wc_os_settings_field'], 'wc_os_settings_action' )
        ) {

            _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
            exit;

        } else {

            $posted = sanitize_wc_os_data($_POST);
            $search_text = array_key_exists('search_text', $posted) ? $posted['search_text'] : '';
            $result_array = array(
                    'status' => false,
                    'products_html' => ''
            );


            if($search_text){


                $args = array(
                    'numberposts' => -1,
                    'post_type' => 'product',
                    'orderby' => 'title',
                    'order' => 'ASC',
                    's' => $search_text,
                );

                $products = get_posts( $args );

                ob_start();

                if(!empty($products)){

                    wc_os_get_products_list($products);

                }else{

                    ?>

                        <tobdy>
                            <tr>
                                <td colspan="6" style="text-align: center">
                                    <?php _e('No products found', 'woo-order-splitter'); ?>
                                </td>
                            </tr>
                        </tobdy>


                    <?php

                }


                $result_array = array(
                    'status' => true,
                    'products_html' => ob_get_clean(),
                );

            }

            wp_send_json($result_array);

        }
    }
	if(!function_exists('custom_bulk_select')){
		function custom_bulk_select() {
		 
			global $post_type, $wc_os_settings;
			
			$wc_os_order_cloning = wc_os_order_cloning();
			
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			 
			if($post_type == $wc_os_get_post_type_default) {
				
				$disable_split = in_array('split', $wc_os_settings['wc_os_additional']);
				
			}
		}	
	}
	
	
	
	if(!function_exists('wc_os_parcels_meta_data_callback')){
		function wc_os_parcels_meta_data_callback($meta_data=array(), $product_id=0, $variation_id=0){
			
		}
		add_action('wc_os_parcels_meta_data', 'wc_os_parcels_meta_data_callback', 10, 3);
	}
	
	if(!function_exists('wc_os_update_shipping_cost')){
		function wc_os_update_shipping_cost($total_shipping_cost=0, $actual_shipping_cost=0, $parcels_array=array(), $wc_os_customer_permitted=false, $wrappers=false){


			
			if($wrappers){
?>				
        <div class="wc_os_update_shipping_cost">
            <?php 
			}
                echo ($total_shipping_cost?wp_kses_post(wc_price($total_shipping_cost)):''); 
                
                WC()->session->set('wc_os_calculated_shipping_cost', $total_shipping_cost);
                WC()->session->set('wc_os_parcels_array', $parcels_array);
                
                WC()->session->set('wc_os_actual_shipping_cost', $actual_shipping_cost);
				
                if($wc_os_customer_permitted && count($parcels_array)>1){
                    WC()->session->set('wc_os_total_shipping_cost', $total_shipping_cost);
                }else{
                    WC()->session->set('wc_os_total_shipping_cost', 0);
                }
				if($wrappers){
            ?>
        </div>
<?php
				}
		}
	}
	
	if(!function_exists('attrib_value_nodes')){
		function attrib_value_nodes($values=array(), $attrib_key=''){
			global $wc_os_settings;
			$wc_os_attributes_nodes = (array_key_exists('wc_os_attributes_nodes', $wc_os_settings)?$wc_os_settings['wc_os_attributes_nodes']:array());
			$attributes_nodes = (is_array($wc_os_attributes_nodes)?(array_key_exists($attrib_key, $wc_os_attributes_nodes)?$wc_os_attributes_nodes[$attrib_key]:array()):array());
			
?>
<div class="attrib_value_nodes">
<a title="<?php _e('Click here to manage exceptions', 'woo-order-splitter'); ?>"><i class="fas fa-braille"></i></a>
<?php if(!empty($values)){ ?><ul><?php foreach($values as $avn=>$value){ ?>
<li class="<?php echo (in_array($value, $attributes_nodes)?'ticked':''); ?>"><label for="avn-<?php echo $avn.'-'.$attrib_key; ?>"><i class="fas fa-check-circle"></i><input <?php checked(in_array($value, $attributes_nodes)); ?> type="checkbox" name="wc_os_attributes_nodes[<?php echo $attrib_key; ?>][]" value="<?php echo $value; ?>" id="avn-<?php echo $avn.'-'.$attrib_key; ?>" /><i><?php echo $value; ?></i></label></li>
<?php } ?></ul><?php } ?>
<small><?php _e('Note:', 'woo-order-splitter'); ?> <?php _e('Unselected attributes will have their separate parcels.', 'woo-order-splitter'); ?></small>
</div>
<?php			
		}
	}
	
	if(!function_exists('wc_os_smart_order_notes')){
		function wc_os_smart_order_notes($order_id=0){
			
			global $wpdb;
			
			$order_children = wc_os_child_orders_by_order_id($order_id, true);
			
			$order_items = array();
			
			if(!empty($order_children)){
				foreach($order_children as $child_id){
					$order_obj = wc_get_order($child_id);
					
					if(is_object($order_obj)){
						if(!empty($order_obj->get_items())){
							foreach($order_obj->get_items() as $item_key=>$order_item){
								$product_id = $order_item->get_product_id();
								$_sku = get_post_field( '_sku', $product_id ); 
								$order_items[$child_id][] = $order_item->get_name().($_sku?' ('.$_sku.')':'');
							}
						}
					}
				}
			}
			
			if(!empty($order_items)){				
				$order_notes = $wpdb->get_results("SELECT comment_content FROM $wpdb->comments WHERE comment_post_ID=$order_id AND comment_type='order_note'");
				if(!empty($order_notes)){
					foreach($order_notes as $order_note){
						$comment_content = $order_note->comment_content;
						$str_stock_reduced = __( 'Stock levels reduced:', 'woocommerce' );
						if(substr($comment_content, 0, strlen($str_stock_reduced))==$str_stock_reduced){
							$comment_content_parts = explode($str_stock_reduced, $comment_content);
							if(!empty($comment_content_parts)){
								$comment_content_parts = array_filter($comment_content_parts);
								$comment_content_parts = current($comment_content_parts);
								$comment_content_parts = explode(',', $comment_content_parts);
								$comment_content_parts = array_map('trim', $comment_content_parts);
								if(!empty($comment_content_parts)){
									foreach($comment_content_parts as $comment_content_part){
										foreach($order_items as $child_order_id=>$child_order_items){
											if(!empty($child_order_items)){
												$child_order = wc_get_order($child_order_id);
												$child_order_notes = array();
												foreach($child_order_items as $child_order_item){
													if(substr($comment_content_part, 0, strlen($child_order_item))==$child_order_item){
														$child_order_notes[] = $comment_content_part;														
													}
												}
												if(!empty($child_order_notes)){
													$child_order->add_order_note($str_stock_reduced.' '.implode(', ', $child_order_notes));
												}
											}
										}
									}
								}
							}
							
						}
					}
				}
				
			}
		}		
	}
	
	if(!function_exists('wc_os_get_order_taxes')){
	
		function wc_os_get_order_taxes($order=array()){
			
			$tax_total = 0;
			
			if(is_numeric($order)){
				$order = wc_get_order($order);	
			}
			
			$order = (is_object($order)?$order:array());
			
			if(is_object($order)){
				$taxes = $order->get_items('tax');
				foreach($taxes as $item_id => $item ) {
					$tax_total += $item->get_tax_total();
				}	
			}
			
			return $tax_total;
		}
		
	}
	
	if(!function_exists('wc_os_get_post_type')){
		function wc_os_get_post_type($order_id=0){
			$post_type = get_post_type($order_id);
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			$is_order = substr($post_type, 0, strlen($wc_os_get_post_type_default))==$wc_os_get_post_type_default;
			$is_subscription = substr($post_type, 0, strlen('shop_subscription'))=='shop_subscription';
			
			return array('is_order'=>$is_order, 'is_subscription'=>$is_subscription);
		}
	}


	if(!function_exists('wc_os_remove_taxes')){
		function wc_os_remove_taxes($wc_os_order_key_cron=array()){	
		
			$order = (is_object($wc_os_order_key_cron)?$wc_os_order_key_cron:(is_numeric($wc_os_order_key_cron)?wc_get_order($wc_os_order_key_cron):0));
			$order_id = ((is_object($order) && method_exists($order, 'get_id')) ? $order->get_id() : 0);
			
			if(is_numeric($order_id) && $order_id>0 && !is_array($wc_os_order_key_cron)){
				$wc_os_order_key_cron = array((object)array('ID'=>$order_id));				
			}
	
			if(!empty($wc_os_order_key_cron)){
				
				//add_action( 'woocommerce_calculate_totals', 'wc_os_crons_wos_calculate_totals', 10, 1 );
				add_filter('woocommerce_order_is_vat_exempt', function(){ return true; });
				
				foreach($wc_os_order_key_cron as $all_crons_items){ //pree($all_crons_items);exit;
					
					$_shipping_postcode = wc_os_get_order_meta($all_crons_items->ID, '_shipping_postcode', true);
					
					//wc_os_delete_order_meta($all_crons_items->ID, '_shipping_postcode');
					
					$order_data = wc_get_order( $all_crons_items->ID );
					
					
					//$woocommerce->customer->set_is_vat_exempt( true );
					wc_os_delete_order_meta($all_crons_items->ID, 'wos_remove_taxes');
					wc_os_delete_order_meta($all_crons_items->ID, '_order_tax');
					wc_os_delete_order_meta($all_crons_items->ID, '_tax_status');
					wc_os_delete_order_meta($all_crons_items->ID, '_tax_class');
					
					if(is_object($order_data)){
					
						foreach($order_data->get_items() as $item_id => $item_data){		
										
							update_metadata( 'order_item', $item_id, '_line_subtotal_tax', 0);
							update_metadata( 'order_item', $item_id, '_line_tax_data', 0);
							update_metadata( 'order_item', $item_id, '_line_tax_data', array());
													
						}
						
						$order_data->calculate_totals();
						
					}
					
					wc_os_update_order_meta($all_crons_items->ID, '_shipping_postcode', $_shipping_postcode);
				
					//$order_data->save();
				}
	
			}
			
		}
	}
	
	add_action('admin_init', function(){
		if(isset($_GET['wc_os_reduce_order_stock'])){
			wc_os_reduce_order_stock(537, true);exit;
		}
	});