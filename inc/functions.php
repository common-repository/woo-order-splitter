<?php if ( ! defined( 'ABSPATH' ) ) exit;

	
	global $wc_os_order_splitter;
	$wc_os_order_splitter = new wc_os_order_splitter;
	

    class wc_os_order_splitter {
		
		/** @var original order ID. */
		public $original_order_id;
		public $processing;
		public $auto_split;
		public $lock_released;
		public $re_split;
		public $exclude_items;
		public $include_items;
		public $include_item_keys;
		public $include_items_qty;
		public $general_array;
		public $cron_in_progress;
		public $clone_in_progress;
		public $meta_keys_intersection;
		public $cron_light_in_progress;
		public $force_clone_in_progress;
		
	
		/**
		 * Fire clone_order function on clone request.
		 */
		
		function __construct() {
			
			$this->processing = true;
			$this->auto_split = false;
			$this->lock_released = false;
			$this->re_split = false;
			$this->exclude_items = array();
			$this->include_items = array();
			$this->include_item_keys = false;
			$this->include_items_qty = array();
			$this->general_array = array();
			$this->cron_in_progress = false;
			$this->clone_in_progress = false;
			$this->meta_keys_intersection = array();
			$this->cron_light_in_progress = false;
			
			add_action( 'plugins_loaded', array($this, 'duplicationCheck') );
			add_action( 'plugins_loaded', array($this, 'splitCheck') );
			
			global $wc_os_bulk_instantiated, $wc_os_pro, $wc_os_custom_orders_table_enabled, $post;
			
			
			if(!$wc_os_bulk_instantiated){
				
				
				
				$wc_os_bulk_instantiated = true;
			
				$wc_os_order_items_count_column = array_key_exists('wc_os_order_items_count_column', get_option('wc_os_general_settings', array()));
				
				if($wc_os_order_items_count_column){		
					if($wc_os_custom_orders_table_enabled){
						add_filter( 'woocommerce_shop_order_list_table_columns', array($this, 'wc_new_order_column_0') ); //01/06/2024
						add_action( 'woocommerce_shop_order_list_table_custom_column', array($this, 'wc_os_orders_list_columns_content'), 10, 2 );
					}else{
						add_filter( 'manage_edit-shop_order_columns', array($this, 'wc_new_order_column_0') );	   
						add_filter( 'manage_woocommerce_page_wc-orders_columns', array($this, 'wc_new_order_column_0') ); //02/04/2024
						//add_filter( 'manage_shop_order_posts_custom_column', array($this, 'sv_wc_cogs_add_order_profit_column_content') );
					}
				}
	
				$wc_os_order_splitf_column = array_key_exists('wc_os_order_splitf_column', get_option('wc_os_general_settings', array()));
				
				if($wc_os_order_splitf_column){		
					//add_filter( 'manage_edit-shop_order_columns', array($this, 'wc_new_order_column') );	   
					if($wc_os_custom_orders_table_enabled){
						add_filter( 'woocommerce_shop_order_list_table_columns', array($this, 'wc_new_order_column') ); //01/06/2024
						add_action( 'woocommerce_shop_order_list_table_custom_column', array($this, 'wc_os_orders_list_columns_content'), 10, 2 );
					}else{
						add_filter( 'manage_edit-shop_order_columns', array($this, 'wc_new_order_column') ); //02/04/2024
						add_filter( 'manage_shop_order_posts_custom_column', array($this, 'wc_os_orders_list_columns_content'), 10, 2 );
					}
					//add_filter( 'manage_shop_order_posts_custom_column', array($this, 'sv_wc_cogs_add_order_profit_column_content') );
				}
				
				$wc_os_order_clonef_column = array_key_exists('wc_os_order_clonef_column', get_option('wc_os_general_settings', array()));
				
				if($wc_os_order_clonef_column){		
					if($wc_os_custom_orders_table_enabled){
						add_filter( 'woocommerce_shop_order_list_table_columns', array($this, 'wc_new_order_column_1') ); //01/06/2024
						add_action( 'woocommerce_shop_order_list_table_custom_column', array($this, 'wc_os_orders_list_columns_content'), 10, 2 );
					}else{
						//add_filter( 'manage_edit-shop_order_columns', array($this, 'wc_new_order_column_1') );	   		
						add_filter( 'manage_edit-shop_order_columns', array($this, 'wc_new_order_column_1') ); //02/04/2024			
						add_filter( 'manage_shop_order_posts_custom_column', array($this, 'wc_os_orders_list_columns_content'), 10, 2 );
					}
				}
				
				
				//add_filter( 'manage_woocommerce_page_wc-orders_custom_column', array($this, 'wc_os_orders_list_columns_content', $column, $post), 10, 2 ); //02/04/2024
				
			}
		}
		
		
		
		public function wc_os_orders_list_columns_content( $column, $wc_order ) {
			
			//pree($column); pree($wc_order);
			global $post;
			
			if(is_object($wc_order)){
				$order_id = $wc_order->get_id();
			}elseif(is_numeric($wc_order)){
				$order_id = $wc_order;
				$wc_order = wc_get_order($order_id);
			}
			
			
			switch($column){
				case 'split_from':
				
					$wc_os_order_splitf_column = array_key_exists('wc_os_order_splitf_column', get_option('wc_os_general_settings', array()));
					
					if($wc_os_order_splitf_column){
						$splitted_from = wc_os_get_order_meta($order_id, 'splitted_from', true);
						$splitted_order = wc_get_order($splitted_from);
						//pree($splitted_from);
						if(is_object($splitted_order) && $splitted_from)
						echo '<a href="'.$splitted_order->get_edit_order_url().'">#'.$splitted_from.'</a>';
						else
						echo '<div class="wos_table_hyphen">-</div>';
					}
				break;
				case 'clone_from':	
				
					$wc_os_order_clonef_column = array_key_exists('wc_os_order_clonef_column', get_option('wc_os_general_settings', array()));
					if($wc_os_order_clonef_column){
						$cloned_from = wc_os_get_order_meta($order_id, 'cloned_from', true);
						
						//pree($cloned_from);
						
								
								
						if($cloned_from){
							
							$cloned_order = wc_get_order($cloned_from);
						
							if(is_object($cloned_order)){
								echo '<a href="'.$cloned_order->get_edit_order_url().'">#'.$cloned_from.'</a>';
							}else{
								  echo '<div class="wos_table_hyphen">-</div>';
							}
						
						}else{
						
							$is_combined = wc_os_get_order_meta($order_id, 'auto_combined', true);
							$wc_os_child_order = wc_os_get_order_meta($order_id, '_wc_os_child_order', true);
						
							if($is_combined){
						
								echo '<span class="dashicons dashicons-flag" style="position: relative; top: 4px;" title="'.__('Combined Order', 'woo-order-splitter').'"></span> '.__('Parent Order', 'woo-order-splitter');
						
							}elseif($wc_os_child_order == 'yes'){

							    echo '<div class="wos_table_hyphen">-</div>';

                            }else{
						
						
								echo __('Parent Order', 'woo-order-splitter');
						
							}
						
						}
												
					}
				break;
				case 'order_number_s':
				
					if(get_splitted_order_title_status() && function_exists('wc_os_get_order_title_html')){

						wc_os_get_order_title_html($order_id);

					}
					
				break;
				case 'items_count':
				
					$_wc_os_total_items =  wc_os_get_order_meta($order_id, '_wc_os_total_items', true);
					$_wc_os_total_items = (is_array($_wc_os_total_items)?count($_wc_os_total_items):$_wc_os_total_items);
					//pree($_wc_os_total_items);
					if(!$_wc_os_total_items || $_wc_os_total_items==0){
						$_wc_os_total_items_arr = array_keys($wc_order->get_items());
						//pree($_wc_os_total_items);
						$_wc_os_total_items = count($_wc_os_total_items_arr);
						
						//wc_os_update_order_meta($order_id, '_wc_os_total_items', $_wc_os_total_items_arr);
					}
					
					echo ($_wc_os_total_items?$_wc_os_total_items:'<div class="wos_table_hyphen">-</div>');
					
					
				break;
				default:
					return apply_filters('wc_os_orders_list_columns_content', $column, $post);
				break;
				
				
			}
		}

		public function wc_new_order_column_0( $columns ) {
			$columns['items_count'] = __('Items Count', 'woo-order-splitter');
			return $columns;
		}
		
		public function wc_new_order_column( $columns ) {
			$columns['split_from'] = __('Split From', 'woo-order-splitter');
			return $columns;
		}
		
		public function wc_new_order_column_1( $columns ) {
			$columns['clone_from'] = __('Parent Order', 'woo-order-splitter');
			return $columns;
		}		
		
				
		public function duplicationCheck() {
			
			if (isset($_GET['clone']) && $_GET['clone'] == 'yes' && isset($_GET['_wpnonce'])){// && isset($_GET['clone-session']) && $_GET['clone-session'] == date('Ymhi')) {
				
				if ( is_user_logged_in() ) {
				
					if( current_user_can('manage_woocommerce') && wc_os_order_cloning()) {
				
						add_action('init', array($this, 'clone_order'));
						
					
					} else {
						
						wp_die(__('You do not have permission to complete this action.', 'woo-order-splitter'));
						
					}
					
				} else {
				
					wp_die(__('You have to be logged in to complete this action', 'woo-order-splitter'));
					
				}
				
			}
			
		}
		
		public function splitCheck($split='', $nonce='', $order_id=0, $quick_split=false) {
			
			
			$split = (isset($_GET['split'])?sanitize_wc_os_data($_GET['split']):$split);
			$nonce = (isset($_GET['_wpnonce'])?sanitize_wc_os_data($_GET['_wpnonce']):$nonce);
			$order_id = (isset($_GET['order_id'])?sanitize_wc_os_data($_GET['order_id']):$order_id);
			$subscription_id = (isset($_GET['subscription_id'])?sanitize_wc_os_data($_GET['subscription_id']):$order_id);
			

			if ($split == 'init' && $nonce){// && isset($_GET['split-session']) && $_GET['split-session'] == date('Ymhi')) {
				
				if ( is_user_logged_in() ) {
					
					//wc_os_pree($subscription_id);exit;
					global $wc_os_settings;
					
					$subscription_split = in_array('subscription_split', $wc_os_settings['wc_os_additional']);
					
					
					
					$wc_os_split = (($order_id && wc_os_order_split($order_id)) || ($subscription_id && $subscription_split));
					
					if( current_user_can('manage_woocommerce') && $wc_os_split) {
						
						global $wc_os_settings;
						
						switch($wc_os_settings['wc_os_ie']){
							default:								
								
								if($order_id){
									$originalorderid = $order_id;
				
									$get_post_meta = wc_os_get_order_meta($originalorderid);
									if(is_array($get_post_meta) && !array_key_exists('split_status', $get_post_meta)){
										wc_os_set_splitter_cron($originalorderid, true, 228);
									}
									
								}

							break;
							case 'default':
								
								
								
								if($order_id){
									add_action('init', array($this, 'split_order'));
								}elseif($subscription_id){
									add_action('init', array($this, 'split_subscription'));
								}
								
							break;								
						}
												
					
					} else {
						
						if(!wp_doing_ajax()){
							wp_die(__('You do not have permission to complete this action.', 'woo-order-splitter'));
						}
						
					}
					
				} else {
				
					wp_die(__('You have to be logged in to complete this action.', 'woo-order-splitter'));
					
				}
				
			}
			
		}	
		
		
		
		
		/**
		 * Create replicated order post and initiate cloned_order_data function.
		 */
	  
						
		public function clone_order($originalorderid = null, $force=false){

			global $wc_os_shipping_cost;
			
			if($this->cron_in_progress && !$force){
				return false;
			}
			
			$_wos_cloned = wc_os_get_order_meta($originalorderid, '_wos_cloned', true);
			
			if($_wos_cloned){
				return false;
			}
			//$currentUser = wp_get_current_user();
			
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			
			$original_order = new WC_Order($originalorderid);
			
			$user_id = $original_order->get_user_id();
			
			$order_data =  array(
				'post_type'     => $wc_os_get_post_type_default,
				'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-'),
				'ping_status'   => 'closed',
				'post_author'   => $user_id,
				'post_password' => uniqid( 'order_' ),
			);
			//clone_order
			$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
			
			//update_post_meta($order_id, 'testing_position', 'clone_order');

	
			if ( is_wp_error( $order_id ) ) {
				
				if(!$this->cron_in_progress)
				add_action( 'admin_notices', array($this, 'clone__error'));
			} else {
				$this->clone_in_progress = true;
				$this->force_clone_in_progress = true;
				
				$this->cloned_order_data($order_id, $originalorderid, true, false, true);//clone_order
				if($force){	
					wc_os_update_order_meta($originalorderid, '_wos_cloned', true);
				}
			}
			
			return $order_id;
			
			
		}
		
		
		
		public function products_with_actions($filter=''){
			
			$ret = array();
			
			global $wc_os_settings;
			
			$wc_os_products = $wc_os_settings['wc_os_products'];
			
			if(!empty($wc_os_products)){
				$current_keys = array_keys($wc_os_products);
				if(is_numeric(current($current_keys))){
					if(!empty($wc_os_products)){
						foreach($wc_os_products as $product_id){
								if($filter=='' || ($filter!='' && $filter==$wc_os_settings['wc_os_ie'])){
									$ret[$product_id] = $wc_os_settings['wc_os_ie'];
								}
						}
					}					
				}else{
					if(!empty($wc_os_products)){
						foreach($wc_os_products as $action=>$products){
							
							
							foreach($products as $product_id){
								
								$product_id_arr = (is_array($product_id)?$product_id:array($product_id));
								
								foreach($product_id_arr as $product_id){
									if($filter=='' || ($filter!='' && $filter==$action)){
										$ret[$product_id] = $action;
									}
								}
							}
						}
					}					
				}
				
			}
			
			return $ret;
			
		}
		
		/*
			START - 
			07 January 2019
			Automatic Settings Added 
		*/
			
		public function split_order_logic($originalorderid = null, $wc_os_cart = false, $return_ids = false){
			
			//pree('$originalorderid: '.$originalorderid);exit;
			
			
			global $woocommerce, $wc_os_settings, $wc_os_pro, $wc_os_debug, $wc_os_shipping_cost, $wc_os_effect_parent, $wc_os_general_settings, $wc_os_tax_cost, $wc_os_schedule_delivery_for_woocommerce,
			$is_woocommerce_subscriptions, $wc_os_delivery_date_activated, $wc_os_products_per_order;
			
			$wc_os_get_post_type_default = wc_os_get_post_type_default();

			$wc_os_packages_overview = array_key_exists('wc_os_packages_overview', $wc_os_general_settings);
			$consider_action_for_all = false;
			$new_order_ids = array();
			$new_order_ids_qty = array();
			$order_id = 0;
			
			if($originalorderid==0 && isset($_GET['order_id']) && isset($_GET['split']) && $_GET['split'] == 'init' && isset($_GET['_wpnonce'])){
				$originalorderid = sanitize_wc_os_data($_GET['order_id']);
				$this->processing = false;								
			}
			$this->auto_split = $wc_os_settings['wc_os_ie'];
			
			
			
			$get_post_meta = ($originalorderid?wc_os_get_order_meta($originalorderid):array());
			$_order_key = ($originalorderid?wc_os_get_order_meta($originalorderid, '_order_key', true):false);
			
			$split_lock = (isset($wc_os_settings['wc_os_additional']['split_lock'])?$wc_os_settings['wc_os_additional']['split_lock']:array());
			$split_lock = is_array($split_lock)?$split_lock:array();
			
			
			if(array_key_exists('split_status', $get_post_meta) || (!$wc_os_cart && !$_order_key)){
				return false;
			}
			
			
			
			$original_order = array();
			$user_id = is_user_logged_in()?get_current_user_id():0;
			if($originalorderid){
				$original_order = new WC_Order($originalorderid);
				$user_id = $original_order->get_user_id();
			}

			$wc_os_order_statuses = wc_get_order_statuses(); 
			$wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);		
			$wc_os_order_statuses_keys = array_unique($wc_os_order_statuses_keys);			
			

			$status_lock_released = empty($split_lock);
			

			
			if(!empty($split_lock) && !empty($original_order)){
				
				foreach($split_lock as $split_lock_i){				
					if(!$status_lock_released){
						$status_lock_released = $original_order->has_status($split_lock_i);
					}
				}
			}			
			

			$status_lock_released = ($this->lock_released?$this->lock_released:$status_lock_released);

			
			if((empty($original_order) || !$status_lock_released) && !$wc_os_cart){
				return false;
			}
			
			
			$wc_os_all_products = (isset($wc_os_settings['wc_os_all_product']) && $wc_os_settings['wc_os_all_product']) ? true : false; //flag indicating to all products are subject to splitting
			$wc_os_products = $wc_os_settings['wc_os_products'];

			$products_with_actions = $this->products_with_actions($this->auto_split);//23/10/2019 > from > $this->products_with_actions();

			
			$wc_order_items = array();
			$wc_order_items_variations = array();
			$wc_order_items_qty_split = array();
			$wc_order_items_qty = array();
			$this->include_items_qty = array();
			if(!empty($original_order) && empty($original_order->get_items()) && !$wc_os_cart){
				return false;
			}

			
			
			if(array_key_exists('wc_os_auto_clone', $wc_os_general_settings) && $originalorderid){
				
				//$this->clone_order($originalorderid, true);
					
				$cloned_order_id = $this->clone_order($originalorderid, true);
				
				
				if($cloned_order_id){
					$auto_cloned_status = wc_os_get_auto_clone_status();
					if($auto_cloned_status){
						wc_os_update_order_status($cloned_order_id, $auto_cloned_status);
					}
				}					
			
			}
			
			//pree('$wc_os_cart: '.$wc_os_cart);
			//pree($woocommerce->cart);
			
			//wc_os_pree($original_order);
			
			if(!empty($original_order)){
				
				foreach($original_order->get_items() as $item_id=>$item_data){
					
					$formatted_meta_data = $item_data->get_formatted_meta_data();
					$formatted_meta_data = empty($formatted_meta_data)?$item_data->get_meta_data():$formatted_meta_data;
					
					$product_item_id = 0;
					
					if($item_data->get_variation_id()){
						$product_item_id = $item_data->get_variation_id();
						$wc_order_items_variations[$item_data->get_product_id()][] = $item_data->get_variation_id();
					}else{
						$product_item_id = $item_data->get_product_id();
						$wc_order_items_variations[$item_data->get_product_id()][] = 0;
					}
					$wc_order_items[$item_id] = $product_item_id;
					$wc_order_items_qty_split[$item_id] = $item_data->get_product_id();
					
					$wc_order_items_qty[$product_item_id] = $item_data->get_quantity();
					
					//wc_os_pree($wc_order_items_qty);
					
					if(!empty($formatted_meta_data)){
						$formatted_meta_data = current($formatted_meta_data);
						$formatted_meta_data = (array)$formatted_meta_data;

						if(!empty($formatted_meta_data) && !array_key_exists('key', $formatted_meta_data)){
							$formatted_meta_data = current($formatted_meta_data);
						}

						if(!empty($formatted_meta_data) && array_key_exists('key', $formatted_meta_data)){
								
							extract($formatted_meta_data);
							$key = strtolower($key);

							
							switch($wc_os_settings['wc_os_ie']){
								
								default:
									switch($key){
										case 'backordered':
										case '_reduced_stock':
										
											$this->include_items_qty[$product_item_id] = $wc_order_items_qty[$product_item_id];
										break;							
										default:

											$this->include_items_qty[$product_item_id] = $wc_order_items_qty[$product_item_id];
										break;
									}
								break;
								
								case 'io':	
									$this->include_items_qty[$product_item_id] = $wc_order_items_qty[$product_item_id];
								break;
							}
								
						
						}
					}else{
						switch($wc_os_settings['wc_os_ie']){
								
							default:
							break;
							
							case 'io':	
								$this->include_items_qty[$product_item_id] = $wc_order_items_qty[$product_item_id];
							break;
						}
					}
					
				}
			}elseif($wc_os_cart && !is_null($woocommerce->cart)){
				global $woocommerce;
				$items = $woocommerce->cart->get_cart();	
				//pree($woocommerce);
				foreach($items as $item => $values) { 
				
					
				
					$item_data = $values['data'];
					$product_attributes = ($item_data->get_attributes());
					$_product =  wc_get_product( $item_data->get_id()); 
					


					$product_item_id = 0;
					
					if($values['variation_id']){
						$product_item_id = $values['variation_id'];
						$wc_order_items_variations[$values['product_id']][] = $values['variation_id'];
					}else{
						$product_item_id = $values['product_id'];
						$wc_order_items_variations[$values['product_id']][] = 0;
					}
					$wc_order_items[$item] = $product_item_id;
					
					$wc_order_items_qty_split[$item] = $values['product_id'];					
					$wc_order_items_qty[$product_item_id] = $values['quantity'];					
					
					if(!empty($product_attributes) && is_array($product_attributes) && !is_object(current($product_attributes))){

					}				
					
					switch($wc_os_settings['wc_os_ie']){
						case 'io':
							$this->include_items_qty[$product_item_id] = $wc_order_items_qty[$product_item_id];
						break;
					}
					
				}	
				
			}
			
			if($wc_os_debug)
			wc_os_pree($this->include_items_qty);
			
			
			wc_os_delete_order_meta($originalorderid, '_wc_os_total_items');

			//pree('$this->auto_split: '.$this->auto_split);
			
			switch($this->auto_split){ //06/05/2019	
				case 'exclusive':
				case 'inclusive':
				case 'shredder':
					if(!empty($wc_order_items) && !empty($wc_order_items_variations)){

						foreach($wc_order_items_variations as $main_product_id=>$product_variations){
							foreach($wc_order_items as $possible_variation_id){
								if(in_array($possible_variation_id, $product_variations)){

									if(array_key_exists($main_product_id, $products_with_actions)){
										$products_with_actions[$possible_variation_id] = $products_with_actions[$main_product_id];
									}
								}
							}
						}
					}
				break;
			}
	
			$wc_order_items_diff = $wc_order_items_matched = array();
			if($wc_os_all_products){				
				$wc_order_items_matched = $wc_order_items;
			}
			
			if(!empty($products_with_actions)){
				$wc_order_items_diff = array_diff($wc_order_items, array_keys($products_with_actions));
				$wc_order_items_diff = array_filter($wc_order_items_diff);			
	
				$wc_order_items_matched = array_intersect(array_keys($products_with_actions), $wc_order_items);
				$wc_order_items_matched = $wc_order_items_matched_items = array_filter($wc_order_items_matched);
			}
			
			if(!empty($wc_order_items_matched_items)){
				$wc_order_items_matched = array();
				foreach($wc_order_items_matched_items as $wc_order_items_matched_item){
					$wc_order_items_matched_key = array_search($wc_order_items_matched_item, $wc_order_items);

					if($wc_order_items_matched_key){
						$wc_order_items_matched[$wc_order_items_matched_key] = $wc_order_items_matched_item;
					}
				}
			}
			
			
			
			
			
			if(!empty($wc_os_products) && !empty($wc_order_items_diff)){
				//echo ':)';
			}
			
			

			//pree('$wc_order_items: ');pree($wc_order_items);
				
			if(!empty($wc_order_items)){
				
				$wos_forced_ie = ($originalorderid?wc_os_get_order_meta($originalorderid, 'wos_forced_ie', true):false);	
				
				if($this->auto_split=='cats' || $this->auto_split=='group_cats'){			
					$wc_os_ie_selected = $this->auto_split;
				}else{
					
					$actions_arr = array();
					if(!empty($wc_order_items_matched)){
						foreach($wc_order_items_matched as $matched_items){					
							if(array_key_exists($matched_items, $products_with_actions)){
								$actions_arr[] = $products_with_actions[$matched_items];
							}
						}
					}
					
					$actions_arr = array_unique($actions_arr);

					$wc_os_ie_selected = $wc_os_settings['wc_os_ie'];

					if(count($actions_arr)==0){ //BACKWARDS COMPATIBILITY
						$consider_action_for_all = true;
					}elseif(count($actions_arr)==1){ //REGULAR/VALID/NORMAL CASE
						$wc_os_ie_selected = current($actions_arr);
					}elseif(count($actions_arr)>1){ //EXPECTED/INVALID/CONFLICT CASE
						if($wos_forced_ie){
							$wc_os_ie_selected = $wos_forced_ie;
						}else{
							switch($wc_os_ie_selected){
								case 'groups':
								break;
								default:
									
									if($originalorderid){								
										wc_os_update_order_meta($originalorderid, 'conflict_status', true);
										wc_os_update_split_status($originalorderid,762);//, 'split_status', 'ACE');
										return;
									}
								break;
							}
						}
					}
					
				}
				
				
				$n_plus_1 = (count($wc_order_items) - count($wc_order_items_matched));
				
				//wc_os_pree($originalorderid.' - '.$this->auto_split.' - '.$wc_os_ie_selected);exit;
				//pree('$this->auto_split: '.$this->auto_split);
				if($this->auto_split){
					switch($wc_os_ie_selected){ //06/05/2019
						default:
						case 'default':

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}

							if($wc_os_cart){
								$number_of_products = $wc_os_products_per_order;
								$order_set = 1;
								$items_count = 0;
								foreach($wc_order_items as $item_key=>$order_item){ 
									if($items_count%$number_of_products==0){ $order_set++; }
									$new_order_ids[$order_set][$item_key] = $order_item;
									$items_count++;
								}

							}
							
						break;	
						case 'exclusive':
						
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}


							$wc_order_items_unique = array_unique($wc_order_items);
							//if(count($wc_order_items)<=1 || empty($wc_order_items_matched)){ //03/11/2020
							if(count($wc_order_items_unique)<=1 || empty($wc_order_items_matched)){	
								if($originalorderid){
									wc_os_update_split_status($originalorderid,819);//, 'split_status', 'Gamma');
								}
								return;
							}

							$this_include_items = array();

							$this->exclude_items = array();
							
							
							$diff_array = array_diff($wc_order_items_unique, $wc_order_items_matched);

							if(!empty($wc_order_items_matched)){
								
								foreach($wc_order_items_matched as $item){
									
									
									$this->include_items = array();
									
									$this->include_items[] = $item;
									
									$this_include_items[] = $item;
									
									if($wc_os_cart){
										
										$new_order_ids[] = array($item);
										
									}else{
										
										$order_data =  array(
											'post_type'     => $wc_os_get_post_type_default,
											'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '779 / '.$wc_os_ie_selected),
											'ping_status'   => 'closed',
											'post_author'   => $user_id,
											'post_password' => uniqid( 'order_' ),
										);
										//exclusive
										$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
										//update_post_meta($order_id, 'testing_position', 'exclusive');

								
										if ( is_wp_error( $order_id ) ) {
											
											if(!$this->cron_in_progress)
											add_action( 'admin_notices', array($this, 'clone__error'));
										} else {
											$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic	
											
											wc_os_update_split_status($order_id,873);
											
											$new_order_ids[] = $order_id;
										}	
											
									}
								}
								
								if($wc_os_cart){
									$new_order_ids[] = $diff_array;
								}
							}else{
							
							}
							
							 
							//if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && $wc_os_effect_parent){
							if($wc_os_effect_parent){
								
								if($originalorderid){
									$this->wos_remove_order_item($originalorderid, $this_include_items);
								}
								
							}else{
							 
								if($n_plus_1){
									
									
									//exit;
									$this->exclude_items = array();
									$this->include_items = array();
									
									foreach($wc_order_items_matched as $item){
										$this->exclude_items[] = $item;
									}
									
									if($wc_os_cart){
										
										$this_exclude_items = array();

										foreach($wc_order_items as $item){
											
											if(!in_array($item, $wc_order_items_matched)){
												$this_exclude_items[] = $item;
											}
										}
										
										//$new_order_ids[] = $this_exclude_items;
										
									}else{
									
										$order_data =  array(
											'post_type'     => $wc_os_get_post_type_default,
											'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '849 / '.$wc_os_ie_selected),
											'ping_status'   => 'closed',
											'post_author'   => $user_id,
											'post_password' => uniqid( 'order_' ),
										);
									
										$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
										//update_post_meta($order_id, 'testing_position', 'exclusive2');
								
										if ( is_wp_error( $order_id ) ) {									
											
											
											
											if(!$this->cron_in_progress)
											add_action( 'admin_notices', array($this, 'clone__error'));
											
											
											
										} else {
											$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic
											
											wc_os_update_split_status($order_id,955);
											
											$new_order_ids[] = $order_id;
										}	
									
									}
									
								}else{

								}
								
							}
							
							
							
							
						break;	
						case 'inclusive':


							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							
							$proceed_split = true;

							if(
									(count($wc_order_items)<=1 || empty($wc_order_items_matched))
								||
									(
											count($wc_order_items)==count($wc_order_items_matched)
										&&
											empty($wc_order_items_diff)
									)
							){

								if($originalorderid){

								}
								$proceed_split = false;
							}
							
							$this->exclude_items = array();
							$this->include_items = array();
							
							
							if(!empty($wc_order_items_matched)){
								
								foreach($wc_order_items_matched as $item){
									
									$this->include_items[] = $item;
									
								}

								if($wc_os_cart){
									
									$new_order_ids[] = $this->include_items;
									if(!empty($wc_order_items_diff)){
										$new_order_ids[] = $wc_order_items_diff;
									}
								}else{

									$order_data =  array(
										'post_type'     => $wc_os_get_post_type_default,
										'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '932 / '.$wc_os_ie_selected),
										'ping_status'   => 'closed',
										'post_author'   => $user_id,
										'post_password' => uniqid( 'order_' ),
									);
									//inclusive
									if($proceed_split){
										$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );

								
										if ( is_wp_error( $order_id ) ) {
											
											if(!$this->cron_in_progress)
											add_action( 'admin_notices', array($this, 'clone__error'));
										} else {
											$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic		
											
											wc_os_update_split_status($order_id,1046);
											
											$new_order_ids[] = $order_id;
											
											wc_os_reduce_order_stock($order_id, true);
											
										}	
									}

									
								}
							}
														
							
							//if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && $wc_os_effect_parent){

							if($wc_os_effect_parent){
								
								if($originalorderid && $proceed_split){
									$this->wos_remove_order_item($originalorderid, $this->include_items);
								}
								
								
							}else{
							 							
								if($n_plus_1){
									
									$this->exclude_items = array();
									$this->include_items = array();
									
							
									foreach($wc_order_items as $item){
										
										if(!in_array($item, $wc_order_items_matched)){
											$this->include_items[] = $item;
										}
									}
								
									if($wc_os_cart){
										
										$this_exclude_items = array();
										
										foreach($wc_order_items as $item){
											
											if(!in_array($item, $wc_order_items_matched)){
												$this_exclude_items[] = $item;
											}
										}
										
										$new_order_ids[] = $this_exclude_items;
										
									}else{
										
										
										
										$order_data =  array(
											'post_type'     => $wc_os_get_post_type_default,
											'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '1007 / '.$wc_os_ie_selected),
											'ping_status'   => 'closed',
											'post_author'   => $user_id,
											'post_password' => uniqid( 'order_' ),
										);
									
										if($proceed_split){
											$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );

									
											if ( is_wp_error( $order_id ) ) {
												
												if(!$this->cron_in_progress)
												add_action( 'admin_notices', array($this, 'clone__error'));
											} else {
												$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic		
												
												wc_os_update_split_status($order_id,1123);
												
												$new_order_ids[] = $order_id;
												
												wc_os_reduce_order_stock($order_id, true);
											}	
										}
									}
									
								}
								
							}
							
							if(!$proceed_split){
								$new_order_ids[] = $originalorderid;								
							}
							
							
							
							
						break;	
						
						
						case 'shredder':
						
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}

												
							if($n_plus_1){
								
								$this->exclude_items = array();
								$this->include_items = array();
								
								foreach($wc_order_items_diff as $item){
									$this->exclude_items[] = $item;
								}
								
								
								if($wc_os_cart){
									
									$this_exclude_items = array();
									
									foreach($wc_order_items as $item){
										if(!in_array($item, $wc_order_items_diff)){
											$this_exclude_items[] = $item;
										}
									}
									if(!empty($this_exclude_items)){
										$new_order_ids[] = $this_exclude_items;
									}
									
									
									
								}elseif(!empty($this_exclude_items)){
								
									$order_data =  array(
										'post_type'     => $wc_os_get_post_type_default,
										'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '1084 / '.$wc_os_ie_selected),
										'ping_status'   => 'closed',
										'post_author'   => $user_id,
										'post_password' => uniqid( 'order_' ),
									);
								
									$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );


							
									if ( is_wp_error( $order_id ) ) {									
										if(!$this->cron_in_progress)
										add_action( 'admin_notices', array($this, 'clone__error'));
									} else {
										$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic	
										
										wc_os_update_split_status($order_id,1205);
										
										$new_order_ids[] = $order_id;	
									}	
									
								}
								
							}
						
						
							
							
								
							$this->exclude_items = array();
							$this_include_items = array();
							
							
							foreach($wc_order_items_diff as $item){
							
								
								$this->include_items = array();
								
								$this->include_items[] = $item;
								
								$this_include_items[] = $item;
								
								
								if($wc_os_cart){
									
									$new_order_ids[] = array($item);
									
								}else{ //other items > new order items for each item
									
									$order_data =  array(
										'post_type'     => $wc_os_get_post_type_default,
										'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '1135 / '.$wc_os_ie_selected),
										'ping_status'   => 'closed',
										'post_author'   => $user_id,
										'post_password' => uniqid( 'order_' ),
									);
								
									$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
									//update_post_meta($order_id, 'testing_position', 'shredder');

							
									if ( is_wp_error( $order_id ) ) {
										
										if(!$this->cron_in_progress)
										add_action( 'admin_notices', array($this, 'clone__error'));
									} else {
										$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic	
										
										wc_os_update_split_status($order_id,1262);
										
										$new_order_ids[] = $order_id;
									}	
									
								}
																
							}
							
							if($originalorderid){
								

								
								if($wc_os_effect_parent){
                                    $this->wos_remove_order_item($originalorderid, $this_include_items);

								}else{

                                    if($n_plus_1){

                                        $this->exclude_items = array();
                                        $this->include_items = array();

                                        foreach($wc_order_items_matched as $item){
                                            //$this->exclude_items[] = $item;
											$this->include_items[] = $item;
                                        }

                                        if($wc_os_cart){

                                            $this_exclude_items = array();
                   
                                            foreach($wc_order_items as $item){

                                                if(!in_array($item, $wc_order_items_matched)){
                                                    $this_exclude_items[] = $item;
                                                }
                                            }

                                            $new_order_ids[] = $this_exclude_items;

                                        }elseif(!empty($this->include_items)){

                                            $order_data =  array(
                                                'post_type'     => $wc_os_get_post_type_default,
                                                'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '1197 / '.$wc_os_ie_selected),
                                                'ping_status'   => 'closed',
                                                'post_author'   => $user_id,
                                                'post_password' => uniqid( 'order_' ),
                                            );

                                            $order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
                                            //update_post_meta($order_id, 'testing_position', 'inclusive2');


                                            if ( is_wp_error( $order_id ) ) {

                                                if(!$this->cron_in_progress)
                                                    add_action( 'admin_notices', array($this, 'clone__error'));
                                            } else {
                                                $this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic

                                                wc_os_update_split_status($order_id,1324);

                                                $new_order_ids[] = $order_id;
                                            }
                                        }

                                    }

//									$this->wos_remove_order_item($originalorderid, $this_include_items);
								}
								
							
								
							}
														
							
						break;		
						
						
						case 'io':
						
							$split_status_value = '';
							

							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
								
						
								
								$classObj = new wc_os_bulk_order_splitter;
								
								
								$_wc_os_stock_status = ($originalorderid?get_post_meta($originalorderid, '_wc_os_stock_status', true):array());
								$_wc_os_stock_status = is_array($_wc_os_stock_status)?$_wc_os_stock_status:array();
								
								//wc_os_pree($this->include_items_qty);
								//wc_os_pree($_wc_os_stock_status);
			
								$items_io = $classObj->separate_io_items($wc_order_items, $this->include_items_qty, $wc_os_cart, $this->re_split, $_wc_os_stock_status);
								
								
								
								//wc_os_pree('cron_light_in_progress: '.$this->cron_light_in_progress);
								//wc_os_pree($items_io);exit;

								
								if($this->cron_light_in_progress){//  && (!is_admin() && !$this->auto_split)){ // 
									//wc_os_pree(debug_backtrace());
									
									//exit;
									return array('items_io'=>$items_io, 'wc_os_ie_selected'=>$wc_os_ie_selected);
								}
								
								//wc_os_pree($items_io);exit;
								
								$proceed_split = true;
								$in_stock_exists = (isset($items_io['in_stock']) && !empty($items_io['in_stock']) && isset($items_io['in_stock']['items']) && !empty($items_io['in_stock']['items']));
								$backorder_exists = (isset($items_io['backorder']) && !empty($items_io['backorder']) && isset($items_io['backorder']['items']) && !empty($items_io['backorder']['items']));

								//wc_os_pree($originalorderid);exit;

								
								
								if( $wc_os_cart || ($in_stock_exists && $backorder_exists) ){	

									wc_os_delete_order_meta($originalorderid, '_wos_out_of_stock');
									wc_os_update_order_meta($originalorderid, '_wos_in_stock', true);
									$proceed_split = true;
									
								}else{									
									$proceed_split = false;
									
									if($in_stock_exists){

										wc_os_delete_order_meta($originalorderid, '_wos_out_of_stock');
										wc_os_update_order_meta($originalorderid, '_wos_in_stock', true);
									}
									if($backorder_exists){
										wc_os_delete_order_meta($originalorderid, '_wos_in_stock');
										wc_os_update_order_meta($originalorderid, '_wos_out_of_stock', true);
									}							
									
									$split_status_value = wc_os_order_split_status_action();								
									$split_status_value = wc_os_method_based_default_order_status($split_status_value, $originalorderid);		
									if($split_status_value){										
										wc_os_set_order_status($originalorderid, '', false, 1271);//$split_status_value
									}
								}
								
								
								//wc_os_pree($proceed_split);pree($in_stock_exists);pree($backorder_exists);exit;
								
								if($proceed_split){

								
									$this->exclude_items = array();
									$this->include_items = array();
									$backorder_only = array();
									
									$save_quantity = $this->include_items_qty;
															
									
									
									// || $consider_action_for_all
									
									if((isset($items_io['in_stock']) && !empty($items_io['in_stock']))){ //create order of in-stock items
										
										//set items to include in order
										$this->include_items = $items_io['in_stock']['items'];
										
										//set quantities for items
										$this->include_items_qty = $items_io['in_stock']['quantity'];
										
									
										if(isset($items_io['backorder'])){
											$backorder_items = (isset($items_io['backorder']['items'])?$items_io['backorder']['items']:array());
											$backorder_only = array_diff($backorder_items, $items_io['in_stock']['items']);
										}
										
										if($wc_os_cart){
											
											if(!empty($this->include_items_qty) && !empty($this->include_items)){
												$this->include_items = array();
												foreach($this->include_items_qty as $include_item=>$include_item_qty){
													if($include_item_qty>0){
														$this->include_items[] = array('product_id'=>$include_item, 'quantity'=>$include_item_qty);
													}
												}
											}
											
											$new_order_ids['in_stock'] = $this->include_items;
											
										}elseif($originalorderid){
											
											//pree($wc_os_effect_parent);exit;
											
											if($wc_os_effect_parent){
												$classObj->wos_update_order_item($originalorderid, $this->include_items, $this->include_items_qty);
												
												if(is_admin()){ //THIS BLOCK IS NOT WORKING ON THE CHECKOUT PAGE
												
													wc_os_update_order_meta($originalorderid, '_wos_update_order_item', array('include_items'=>$this->include_items, 'include_items_qty'=>$this->include_items_qty));
													
													if($items_io['backorder_split_required'] && empty($backorder_only)){
														
														wc_os_reduce_order_stock($originalorderid, true);
	
														wc_os_delete_order_meta($originalorderid, '_wos_out_of_stock');
														wc_os_update_order_meta($originalorderid, '_wos_in_stock', true);
														
																				
														//$split_status_value = wc_os_method_based_default_order_status($split_status_value, $originalorderid);
														//if($split_status_value){										
															wc_os_set_order_status($originalorderid, '', false, 1313);
															
														//}
														
													}
												}else{
													//pree('$wc_os_effect_parent: '.$wc_os_effect_parent);exit;
												}
												//exit;
											}else{
												if(isset($items_io['in_stock']) && !empty($items_io['in_stock']) && !empty($items_io['in_stock']['items'])){
													
													$split_status_value = wc_os_order_split_status_action();
													
	
													//set items to include in order
													$this->include_items = $items_io['in_stock']['items'];
													$items_io['in_stock']['items'] = array();
	
													//set quantities for items
													$this->include_items_qty = $items_io['in_stock']['quantity'];
													$items_io['in_stock']['quantity'] = array();
	
													//create post order data
	
	
													$order_data =  array(
														'post_type'     => $wc_os_get_post_type_default,
														'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '1377 / '.$wc_os_ie_selected),
														'ping_status'   => 'closed',
														'post_author'   => $user_id,
														'post_password' => uniqid( 'order_' ),
													);
	
													//save order to database
													if(!$wc_os_debug)
														$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
													//update_post_meta($order_id, 'testing_position', 'io+backorder');
													
													wc_os_logger('debug', 'CREATING #'.$order_id, true);
	
													if ( is_wp_error( $order_id ) ) {
	
														if(!$this->cron_in_progress)
															add_action( 'admin_notices', array($this, 'clone__error'));
													} else { //add data to new post
														if(!$wc_os_debug) //save order
															$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic
	
														wc_os_update_split_status($order_id,1362);
	
														wc_os_delete_order_meta($order_id, '_wos_out_of_stock');
														wc_os_update_order_meta($order_id, '_wos_in_stock', true);
														
														
	
														$new_order_ids['in_stock'] = $order_id;
	
														//$outofstock_order = wc_get_order( $order_id );
														
														//$split_status_value = wc_os_method_based_default_order_status($split_status_value, $order_id);
														
														
														
														//if($split_status_value){
															//wp_update_post(array('ID'=>$order_id, 'post_status'=>$split_status_value));
															wc_os_set_order_status($order_id, '', false, 1503);//$split_status_value
															//update_wcfm_order_status($order_id, $split_status_value);
														//}else{
	
														   // wp_update_post(array('ID'=>$order_id, 'post_status'=>'wc-on-hold'));
	
															//update_wcfm_order_status($order_id, 'wc-on-hold');
														//}
														
														wc_os_reduce_order_stock($order_id, true);
													}	
													
												}
											}
											
										}
	
										
									}
									
									//BACKORDER SECTION 
									
									//$items_io = $classObj->separate_io_items($wc_order_items, $save_quantity, $wc_os_cart, $this->re_split, array());
									
									if(isset($items_io['backorder']) && !empty($items_io['backorder']) && is_array($items_io['backorder']['items']) && !empty($items_io['backorder']['items'])) //create order of backorder items
									{
										$split_status_value = wc_os_order_split_status_action();
										
										
										//wc_os_pree('$split_status_value: '.$split_status_value);exit;
										
										if($items_io['backorder_split_required']){
											
											$io_items_remaining = function_exists('wc_os_get_io_setting')?wc_os_get_io_setting('io_items_remaining', 'group'):'group';
											
											//set items to include in order
											$this->include_items = $items_io['backorder']['items'];
											$items_io['backorder']['items'] = array();
											
											//set quantities for items
											$this->include_items_qty = $items_io['backorder']['quantity'];
											$items_io['backorder']['quantity'] = array();
											
											//wc_os_pree($this->include_items);wc_os_pree($this->include_items_qty);exit;
											
											
											if($wc_os_cart){
												
												if(!empty($this->include_items_qty) && !empty($this->include_items)){
													$this->include_items = array();
													foreach($this->include_items_qty as $include_item=>$include_item_qty){
														if($include_item_qty>0){
															$this->include_items[] = array('product_id'=>$include_item, 'quantity'=>$include_item_qty);
														}
													}
												}
												
												$new_order_ids['out_stock'] = $this->include_items;
											
											}else{
												
												
												//pree('$io_items_remaining: '.$io_items_remaining);exit;
														
												switch($io_items_remaining){
													
													case 'separate':
													
														$backorder_items_arr = $this->include_items;
														$backorder_items_qty_arr = $this->include_items_qty;
														
														
													break;
													
													case 'group':
														
														$backorder_items_arr = array($this->include_items);
														$backorder_items_qty_arr = array($this->include_items_qty);
													
													break;
														
												}
												
												//wc_os_pree($backorder_items_arr);wc_os_pree($backorder_items_qty_arr);exit;
												
													$backorder_items_arr = (is_array(current($backorder_items_arr))?current($backorder_items_arr):$backorder_items_arr);
													$backorder_items_qty_arr = (is_array(current($backorder_items_qty_arr))?current($backorder_items_qty_arr):$backorder_items_qty_arr);
													
													$this->include_items = $backorder_items_arr;
													$this->include_items_qty = $backorder_items_qty_arr;
														
													
													//wc_os_pree($this->include_items);wc_os_pree($this->include_items_qty);exit;
													//continue;
													
													$order_split_status = wc_os_order_split_status_action($original_order, $this->include_items);
													
													$order_split_status = ($order_split_status?$order_split_status:$original_order->get_status());
													
													$order_split_status = wc_os_method_based_default_order_status($order_split_status, 0, '_wos_out_of_stock');
													
													
		
													$order_data =  array(
														'post_type'     => $wc_os_get_post_type_default,
														'post_status'   => wc_os_add_prefix($order_split_status, 'wc-', 1530),
														'ping_status'   => 'closed',
														'post_author'   => $user_id,
														'post_password' => uniqid( 'order_' ),
													);
													
													//pree($order_data);exit;
													
													//save order to database
													if(!$wc_os_debug){
														$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
													}
													//wc_os_logger('debug', 'CREATING #'.$order_id, true);
													//pree($order_id.' > '.$order_data['post_status']);
													//update_post_meta($order_id, 'testing_position', 'io+backorder');
													
													if ( is_wp_error( $order_id ) ) {
													
														if(!$this->cron_in_progress)
														add_action( 'admin_notices', array($this, 'clone__error'));
													} else { //add data to new post
														if(!$wc_os_debug){
															
															//pree('$order_id: '.$order_id.', $originalorderid: '.$originalorderid.', $wc_os_shipping_cost: '.$wc_os_shipping_cost);
															
															$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic
														}
														
														
														
														
														//wc_os_update_split_status($order_id, 1447);
														wc_os_delete_order_meta($order_id, '_wos_in_stock');
														wc_os_update_order_meta($order_id, '_wos_out_of_stock', true);
														
														
														
														
														$new_order_ids['out_stock'] = $order_id;
														
														//pree('wc_os_reduce_order_stock('.$order_id.');');
														wc_os_reduce_order_stock($order_id, true);
														
														
													}
													
												}
												
												//exit;
												
											
											//exit;
											
										}else{
											
											if($originalorderid){
												wc_os_delete_order_meta($order_id, '_wos_in_stock');
												wc_os_update_order_meta($originalorderid, '_wos_out_of_stock', true);
	
												//wp_update_post(array('ID'=>$order_id, 'post_status'=>'wc-on-hold'));
												
												wc_os_set_order_status($order_id, 'wc-on-hold', false, 1619);
												//update_wcfm_order_status($order_id, 'wc-on-hold');
												
											}
										}
									}
									
									//exit;							
					
									//restore saved quantity
									$this->include_items_qty = $save_quantity;		
									
									
													
									
	
									if($originalorderid){
										
										//pree('$backorder_only: ');pree($backorder_only);pree('$wc_os_effect_parent: '.$wc_os_effect_parent);
										
										if(!empty($backorder_only)){
											if($wc_os_effect_parent){
												$this->wos_delete_order_item($originalorderid, $backorder_only);
												
												wc_os_reduce_order_stock($originalorderid, true);
												
												
											}else{
	
	
												if(isset($items_io['in_stock']) && !empty($items_io['in_stock']) && !empty($items_io['in_stock']['items'])) //create order of backorder items
												{
													$split_status_value = wc_os_order_split_status_action();
	
													//set items to include in order
													$this->include_items = $items_io['in_stock']['items'];
													$items_io['in_stock']['items'] = array();
	
													//set quantities for items
													$this->include_items_qty = $items_io['in_stock']['quantity'];
													$items_io['in_stock']['quantity'] = array();
	
													//create post order data
													
	
	
													$order_data =  array(
														'post_type'     => $wc_os_get_post_type_default,
														'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '1618 / '.$wc_os_ie_selected),
														'ping_status'   => 'closed',
														'post_author'   => $user_id,
														'post_password' => uniqid( 'order_' ),
													);
	
													//save order to database
													if(!$wc_os_debug)
														$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );
														
														//wc_os_logger('debug', 'CREATING #'.$order_id, true);
													//update_post_meta($order_id, 'testing_position', 'io+backorder');
	
													if ( is_wp_error( $order_id ) ) {
	
														if(!$this->cron_in_progress)
															add_action( 'admin_notices', array($this, 'clone__error'));
													} else { //add data to new post
														if(!$wc_os_debug) //save order
															$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic
	
														wc_os_update_split_status($order_id,1555);
	
														wc_os_delete_order_meta($order_id, '_wos_out_of_stock');
														wc_os_update_order_meta($order_id, '_wos_in_stock', true);
	
														$new_order_ids['in_stock'] = $order_id;
														
														$split_status_value = wc_os_method_based_default_order_status($split_status_value, $order_id);
														//$outofstock_order = wc_get_order( $order_id );
														
														
														
														//if($split_status_value){
															//wp_update_post(array('ID'=>$order_id, 'post_status'=>$split_status_value));
															wc_os_set_order_status($order_id, '', false, 1687);//$split_status_value
															//update_wcfm_order_status($order_id, $split_status_value);
													   // }else{
	
															//wp_update_post(array('ID'=>$order_id, 'post_status'=>'wc-on-hold'));
	
															//update_wcfm_order_status($order_id, 'wc-on-hold');
													   // }
														
														wc_os_reduce_order_stock($order_id, true);
													}
	
	
												}
	
	
	
											}
										}
										wc_os_update_order_meta($originalorderid, '_wos_calculate_totals', true);
										
									}
									
									
								
								}
							
							
							}
							
								
							
							if(!is_admin()){
								//exit;
							}
							
						break;

						case 'quantity_split':


							if(!$wc_os_pro){ return; }

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}


							$qty_split_option = (in_array($wc_os_settings['wc_os_qty_split_option'], array('custom', 'default', 'eric_logic'))?$wc_os_settings['wc_os_qty_split_option']:'custom');//$consider_action_for_all < 12/05/2020
						


							if($wc_os_all_products){

								$wc_order_items_matched = $wc_order_items_qty_split;

							}else{

								$wc_order_items_matched = array_intersect(array_keys($products_with_actions), $wc_order_items_qty_split);
								$wc_order_items_matched = array_filter($wc_order_items_matched);
							}




							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){

								$classObj = new wc_os_bulk_order_splitter;



								$qty_split_group = array('parent_group'=>array(), 'child_group'=>array());
								
								
								
								if($wc_os_cart){
																		
									if (!empty($wc_order_items_matched)) {
										foreach($wc_order_items_matched as $matched_item) {
									
											if (array_key_exists($matched_item, $wc_order_items_qty)) {
									
												$items_qty = $wc_order_items_qty[$matched_item];
									
												switch ($qty_split_option) {
													default:
													case 'default':
									
														for ($i = 1; $i <= $items_qty; $i++) {
															$new_order_ids[] = array($matched_item);
															$new_order_ids_qty[] = array($matched_item => 1);
														}
									
														break;
													case 'custom':
													case 'eric_logic':
									
														break;
												}
											}
										}
									
										if (!empty($wc_order_items_diff)) {
									
											$new_order_ids[] = $wc_order_items_diff;
									
										}
									
									}
									elseif(!empty($wc_order_items_diff)) {
									
										foreach($wc_order_items_diff as $dif_item) {
									
											if (array_key_exists($dif_item, $wc_order_items_qty)) {
									
												$items_qty = $wc_order_items_qty[$dif_item];
									
												switch ($qty_split_option) {
													default:
													case 'default':
									
														for ($i = 1; $i <= $items_qty; $i++) {
															$new_order_ids[] = array($dif_item);
														}
									
														break;
													case 'custom':
													case 'eric_logic':
									
														break;
												}
											}
										}
									
									
									}
									
						
									
								}else{
									
									foreach($original_order->get_items() as $item_key=>$item_values){
	
										$order_item_id = $item_values->get_id();
										$order_item_total = $item_values->get_total();

	
	
										$pid = $item_values->get_product_id();
										$vid = $item_values->get_variation_id();
										$product_item = $vid?$vid:$pid;
	
										$order_id_new = 0;
										if(($item_values->get_product_id() && (in_array($item_values->get_product_id(), $wc_order_items_matched)) || $consider_action_for_all)){ //$consider_action_for_all < 23/10/2019
	
											$qty = $item_values->get_quantity();
											$qty_split_arr = $classObj->get_qty_split_arr($qty_split_option, $item_key, $qty);

											switch($qty_split_option){
												case 'custom':
													if(empty($qty_split_arr)){ continue 2; }
												break;
											}
	
											$item = $item_values->get_data();
											$product_id = $item['product_id'];
											$variation_id = $item['variation_id'];
	
											if ($variation_id != 0) {
												$product = new WC_Product_Variation($variation_id);
	
											} else {
												$product = new WC_Product($product_id);
											}
											
	
											//if($item_values['total']==$product->get_price()){
												//$unit_price = $product->get_price();
											//}else{
												$unit_price = ($item_values['total']/$qty); //06 March 2019 - with Sean Owen
											//}
											//11 May 2021 - with Bertjan Hopster to resolve wholesale price thing woocommerce-wholesale-prices-premium
											
	
											$order_data = array(
												'post_type' => $wc_os_get_post_type_default,
												'post_status' => wc_os_add_prefix($original_order->get_status(), 'wc-', '1829 / '.$wc_os_ie_selected),
												'ping_status' => 'closed',
												'post_author' => $user_id,
												'post_password' => uniqid( 'order_' ),
											);
	
	
											$wc_pos_order_type = wc_os_get_order_meta($originalorderid, 'wc_pos_order_type', true);
											$wc_pos_order_type = ($wc_pos_order_type?$wc_pos_order_type:'online');
											

											switch($qty_split_option){
												case 'custom':
												case 'eric_logic':
											

		
													if(empty($qty_split_arr)){
														
														//$qty_split_group['parent_group'][$product_item] = array('qty'=>array($qty), 'item_key'=>$item_key, 'item_values'=>$item_values, 'product'=>$product);
														
													}else{
														
		
														if(count($qty_split_arr)==1){
															$qty_split_group['child_group'][$product_item] = array('qty'=>$qty_split_arr, 'item_key'=>$item_key, 'item_values'=>$item_values, 'product'=>$product);
															$remaining = ($qty - array_sum($qty_split_arr));
															if($remaining>0){
																$qty_split_group['parent_group'][$product_item] = array('qty'=>array($remaining), 'item_key'=>$item_key, 'item_values'=>$item_values, 'product'=>$product);
															}
														}elseif(count($qty_split_arr)>1){
															$iter = 0;
															
															foreach($qty_split_arr as $split_figure){ $iter++;
															
																//$item_values = $product = array(); 

																if($iter==1){
																	$qty_split_group['parent_group'][$product_item] = array('qty'=>array($split_figure), 'item_key'=>$item_key, 'item_values'=>$item_values, 'product'=>$product);
																}else{
																	$qty_split_group['child_group'][$product_item][] = array('qty'=>array($split_figure), 'item_key'=>$item_key, 'item_values'=>$item_values, 'product'=>$product);
																}
																
															}
														}
		
		
													}
	
	
												break;
												default:
	
	
												if($qty<=1 && count($original_order->get_items())==1){ //22/08/2019 - Added this condition while talking to Paul Rodarte
													continue 2;
												}
	
	

												if(!empty($qty_split_arr)){
	
													foreach($qty_split_arr as $qty_split){
	

														$order_id_new = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );

														wc_os_update_order_meta($order_id_new, 'wc_pos_order_type', sanitize_wc_os_data($wc_pos_order_type)); // NO QTY. SPLIT
														wc_os_update_order_meta($order_id_new, 'qty_splitted', true);
	
														if ( is_wp_error( $order_id_new ) ) {
	
															if(!$this->cron_in_progress)
																add_action( 'admin_notices', array($this, 'split__error'));
														} else {															

															$this->splitted_order_data($order_id_new, $originalorderid, $product_id, $variation_id, $qty_split, $unit_price);
	
	
															$valid_process = true;
	
															if(method_exists($this, 'ywpo_add_order_item_meta')){
																$this->ywpo_add_order_item_meta($item_key, $product);
															}
														}
	
	//}
													}
												}else{
	
													for($q=1; $q<=$qty; $q++){
	
														$order_id_new = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data), true );

	
														wc_os_update_order_meta($order_id_new, 'wc_pos_order_type', sanitize_wc_os_data($wc_pos_order_type)); // YES QTY. SPLIT
														wc_os_update_order_meta($order_id_new, 'qty_splitted', true);
	
														if ( is_wp_error( $order_id_new ) ) {
	
															if(!$this->cron_in_progress)
																add_action( 'admin_notices', array($this, 'split__error'));
														} else {

															
															$this->splitted_order_data($order_id_new, $originalorderid, $product_id, $variation_id, 1, $unit_price, false, $order_item_id);

															$valid_process = true;
	
															if(method_exists($this, 'ywpo_add_order_item_meta')){
																$this->ywpo_add_order_item_meta($item_key, $product);
															}
														}
	
	
														$new_order_ids[] = $order_id_new;
			
	
													}
	
												
	
												}
												
												break;
	
											}
											
	
	
											if($order_id_new){
												
												if(!in_array($order_id_new, $new_order_ids)){
													$new_order_ids[] = $order_id_new;
												}
	
												$this->wos_delete_order_item($originalorderid, array($item_values->get_product_id())); //REMOVING SPLITTED ITEMS FROM ORIGINAL ORDER 19/10/2019
												$order_data_updated = wc_get_order( $originalorderid );
												$order_data_updated->calculate_totals();
												wc_os_delete_order_meta($order_id_new, 'wc_os_order_splitter_cron');//quantity_split //in case this meta_key was inherited from parent order

												
												
												wc_os_update_split_status($order_id_new,2019);
	
											}
										}
										
										
									}
									
								
								}
								//exit;
								if(!is_admin()){

								}

								if($wc_os_cart){
								}else{
									if(!empty($qty_split_group)){
										
										$parent_group = $qty_split_group['parent_group'];
										
										if(!empty($parent_group)){
											
											//MOVED INSIDE CONDITION ON BUG REPORTED BY "ryonwhyte" ON 22/11/2019
											$new_order_ids = $classObj->split_order_by_qty_split_groups($qty_split_group, $originalorderid, $order_data);
											
											$modify_original_order = wc_get_order($originalorderid);

											foreach($modify_original_order->get_items() as $cart_item_key=>$cart_item_data){
												$pid = $cart_item_data->get_product_id();
												$vid = $cart_item_data->get_variation_id();	
												$product_item = $vid?$vid:$pid;											
												if(array_key_exists($product_item, $parent_group)){
													$group_item_data = $parent_group[$product_item];
													$product = $group_item_data['product'];
													$cart_item_data['quantity'] = array_sum($group_item_data['qty']); //uncommented on 03/06/2021 - Bhupinder Gill

													//$unit_price = $product->get_price();
													$unit_price = ($cart_item_data['total']/$cart_item_data['quantity']);
													//11 May 2021 - with Bertjan Hopster to resolve wholesale price thing woocommerce-wholesale-prices-premium

													$cart_item_data['subtotal'] = $unit_price*$cart_item_data['quantity'];
													$cart_item_data['total'] = $cart_item_data['subtotal'];
													

													$modify_original_order->save();
												}
											}
											//$modify_original_order->calculate_totals();
											//update_post_meta($originalorderid, '_wos_calculate_totals', true);
										}
										
									}
								}
								
								
								if($originalorderid){
									
									$order_data_check = wc_get_order( $originalorderid );
									$order_data_check->calculate_totals();
									if(count($order_data_check->get_items())<1){
										wos_clone_order_notes($originalorderid, $new_order_ids);
										if(wc_os_order_removal()){
											wc_os_trash_post($originalorderid);
										}
									}
								}
								
							}
							if(!is_admin()){
								//exit;
							}
							
				
							
						break;	
						case 'subscription_split':
							
							if(!$wc_os_pro){ return; }


							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							//pree($wc_order_items_qty_split);exit;
							$classObj = new wc_os_bulk_order_splitter;
							$new_order_ids = $classObj->subscription_split_machine($wc_os_ie_selected,$originalorderid, $wc_os_all_products, $wc_order_items_matched, $wc_order_items_qty_split, $original_order, $consider_action_for_all, $wc_os_cart, $wc_order_items_diff, $wc_order_items_qty);
							//exit;
						break;
						case 'cats':
						
							if(!$wc_os_pro){ return; }
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								if(!$wc_os_cart){
									$new_order_ids = $classObj->split_order_by_category_qty($original_order->get_items(), $originalorderid, $user_id, $wc_os_cart, $split_status);
								}else{
									$new_order_ids = $classObj->split_order_by_category_qty($wc_order_items, $originalorderid, $user_id, $wc_os_cart);

								}
								
							}
							
							
						break;										
						case 'cats_only':			
						
							if(!$wc_os_pro){ return; }

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$wos_forced_ie = wc_os_get_order_meta($originalorderid, 'wos_forced_ie', true);
								$wos_delete_order = wc_os_get_order_meta($originalorderid, 'wos_delete_order', true);
								
								if($wos_forced_ie && $wos_delete_order){
									$wc_os_ie_selected = $wos_forced_ie;
									//delete_post_meta($originalorderid, 'wos_forced_ie');

									
									$new_order_ids = $classObj->split_order_by_category($wos_forced_ie, $wc_order_items, $originalorderid, $user_id);
									
									if(!empty($new_order_ids)){
										$original_cloned_from = wc_os_get_order_meta($originalorderid, 'cloned_from', true);
										$original_splitted_from = wc_os_get_order_meta($originalorderid, 'splitted_from', true);
										foreach($new_order_ids as $new_ordder_id){
											wc_os_update_order_meta($new_ordder_id, 'cloned_from', $original_cloned_from);
											wc_os_update_order_meta($new_ordder_id, 'splitted_from', $original_splitted_from);											
											wc_os_delete_order_meta($new_ordder_id, 'wos_delete_order');
											wc_os_delete_order_meta($new_ordder_id, 'wos_forced_ie');
										}
										//delete_post_meta($originalorderid, 'wos_delete_order');								
										//wc_os_trash_post($originalorderid);
										$originalorderid = $original_cloned_from;
									}
									
	
									
								}else{			
								
									$wc_os_cats = $wc_os_settings['wc_os_cats'];
									$wc_os_cats_arr = array();
									foreach($wc_os_cats as $item_group=>$cat_items){								
										foreach($cat_items as $cat_item){
											$wc_os_cats_arr[$cat_item] = $item_group;
										}
									} 
								
									$category_arr = array();
									foreach($wc_order_items as $item){
										
										$product_cats = get_the_terms ( $item, 'product_cat' );								
										foreach($product_cats as $product_cat){
											$category_arr[$product_cat->term_id][] = $item;
										}
									}
									
									
									
									if(!empty($category_arr)){
										foreach($category_arr as $cat_group=>$items_arr){
											
											$order_split_action = (array_key_exists($cat_group, $wc_os_cats_arr)?$wc_os_cats_arr[$cat_group]:'');
											
											$this->exclude_items = array();
											$this->include_items = array();									
											foreach($items_arr as $item_id){
												$this->include_items[] = $item_id;
											}		
											$order_data_args =  array(
												'post_type'     => $wc_os_get_post_type_default,
												'post_status'   => wc_os_add_prefix($original_order->get_status(), 'wc-', '2156 / '.$wc_os_ie_selected),
												'ping_status'   => 'closed',
												'post_author'   => $user_id,
												'post_password' => uniqid( 'order_' ),
											);
											
											
											$order_id = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data_args), true );
											//update_post_meta($order_id, 'testing_position', 'cats');

									
											if ( is_wp_error( $order_id ) ) {
												
												if(!$this->cron_in_progress)
												add_action( 'admin_notices', array($this, 'clone__error'));
											} else {
												$this->cloned_order_data($order_id, $originalorderid, true, false, $wc_os_shipping_cost);//split_order_logic	
												
												if($order_split_action && count($this->include_items)>1){
													wc_os_update_order_meta($order_id, 'wos_forced_ie', $order_split_action);
													wc_os_update_order_meta($order_id, 'wos_delete_order', true);
													//update_post_meta($order_id, 'conflict_status', true);			
													
													wc_os_set_splitter_cron($order_id, true, 2101);
												}else{
													wc_os_update_split_status($order_id,2103);
												}
												
												
												$new_order_ids[] = $order_id;
												
											}	
										}
									}
									
								}
								
							}
							//exit;
						break;
						case 'groups':

							if(!$wc_os_pro){ return; }
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							

							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$is_bundle = false;
								
								
								
								if($originalorderid && count($original_order->get_items())==1){
									foreach($original_order->get_items() as $item_id=>$item_values){
										$pid = $item_values->get_product_id();
										$vid = $item_values->get_variation_id();
										$product_item = $vid?$vid:$pid;				
										$product = wc_get_product($product_item);
										$is_bundle = (method_exists($product, 'get_type') && $product->get_type()=='bundle');
									}
								}		


								if(count($wc_order_items)>1){

									$new_order_ids = $classObj->split_order_by_groups($wc_order_items, $originalorderid, $user_id, $wc_order_items_variations, $wc_os_cart);

									if($originalorderid){
										$order_data_check = wc_get_order($originalorderid);			

										if(count($order_data_check->get_items())<1){
											wos_clone_order_notes($originalorderid, $new_order_ids);
											if(wc_os_order_removal()){
												wc_os_trash_post($originalorderid);
											}
										}
									}
								}elseif(count($wc_order_items)==1 && $is_bundle){

								}
								//exit;
								
							}
							//exit;
							
						break;	
						case 'groups_by_meta':

							if(!$wc_os_pro){ return; }
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){
								
								$classObj = new wc_os_bulk_order_splitter;
								$new_order_ids = $classObj->split_order_groups_by_meta($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
								
								
								if(!$wc_os_cart && !empty($new_order_ids) && $originalorderid){
									$order_data_check = wc_get_order( $originalorderid );
									
									if(count($order_data_check->get_items())<1){
										wos_clone_order_notes($originalorderid, $new_order_ids);
										if(wc_os_order_removal()){
											wc_os_trash_post($originalorderid);
										}
									}	
								}

							}
							
						break;
						case 'group_cats':
							
							if(!$wc_os_pro){ return; }
							
							

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								global $wc_os_woocommerce_shipping_multiple_addresses;
								
								//wc_os_pree($wc_os_woocommerce_shipping_multiple_addresses.' * '.count($wc_order_items));exit;
								
								if($wc_os_woocommerce_shipping_multiple_addresses && count($wc_order_items)>0){// && (!LIVE || (is_user_logged_in() && current_user_can('administrator')))){
									
									$new_order_ids = $classObj->split_order_by_group_cats_shipping_multiple($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
									//wc_os_pree($new_order_ids);exit;
									
									
								}elseif(count($wc_order_items)>1){
								
									$new_order_ids = $classObj->split_order_by_group_cats($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
									
								}
								
								
								
								
								if(!$wc_os_cart){
									if($originalorderid){
										
										if(!empty($new_order_ids)){
											$order_data_check = wc_get_order( $originalorderid );
											
											
											
											if(count($order_data_check->get_items())<1){
												wos_clone_order_notes($originalorderid, $new_order_ids);
												if(wc_os_order_removal()){
													wc_os_trash_post($originalorderid);
												}
											}	
										}else{
											
										}
									}
								}

							}
							
						break;	
						case 'group_by_vendors':
						
							if(!$wc_os_pro){ return; }


							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							

							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){
								
								$classObj = new wc_os_bulk_order_splitter;
								if($originalorderid){
									
								}
								$new_order_ids = $classObj->split_order_by_vendor_groups($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
								
								
							}

							if($originalorderid){
								
								$order_data_check = wc_get_order( $originalorderid );
								
								$order_data_check->calculate_totals();
								
								if(count($order_data_check->get_items())<1){
									wos_clone_order_notes($originalorderid, $new_order_ids);
									if(wc_os_order_removal()){
										wc_os_trash_post($originalorderid);
									}
								}	
									
							}
							
						break;			
                       case 'group_by_woo_vendors':

                            if(!$wc_os_pro){ return; }

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}


                            if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){

                                $classObj = new wc_os_bulk_order_splitter;

                                $new_order_ids = $classObj->split_order_by_woo_vendor_groups($wc_order_items, $originalorderid, $user_id, $wc_os_cart);

                            }

                            if($originalorderid){

                                $order_data_check = wc_get_order( $originalorderid );

                                $order_data_check->calculate_totals();

                                if(count($order_data_check->get_items())<1){
                                    wos_clone_order_notes($originalorderid, $new_order_ids);
                                    if(wc_os_order_removal()){
                                        wc_os_trash_post($originalorderid);
                                    }
                                }

                            }

						break;												
						case 'group_by_attributes_only':
						
							if(!$wc_os_pro){ return; }
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$new_order_ids = $classObj->split_order_by_attributes_groups($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
								
								
								
							}
						break;
						case 'group_by_attributes_value':
						
							if(!$wc_os_pro){ return; }
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}
							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$new_order_ids = $classObj->split_order_by_attributes_values_grouped($wc_order_items, $originalorderid, $user_id, $wc_os_cart);

								
							}
						break;
						
                        case 'group_by_acf_group_fields':

                            if(!$wc_os_pro){ return; }

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}

                            if(class_exists('wc_os_bulk_order_splitter')){

                                $classObj = new wc_os_bulk_order_splitter;

                                $is_bundle = false;

                                if($originalorderid && count($original_order->get_items())==1){
                                    foreach($original_order->get_items() as $item_id=>$item_values){
                                        $pid = $item_values->get_product_id();
                                        $vid = $item_values->get_variation_id();
                                        $product_item = $vid?$vid:$pid;
                                        $product = wc_get_product($product_item);
                                        $is_bundle = (method_exists($product, 'get_type') && $product->get_type()=='bundle');
										
										
										do_action('group_by_acf_group_fields_inner_hook_1', $original_order);
										
                                    }
                                }

                                if(count($wc_order_items)>1){

                                    $new_order_ids = $classObj->split_order_acf_fields_values($wc_order_items, $originalorderid, $user_id, $wc_order_items_variations, $wc_os_cart);
									//$new_order_ids = array();
                                    //exit;
                                    if($originalorderid){
                                        $order_data_check = wc_get_order($originalorderid);

                                        if(count($order_data_check->get_items())<1){
                                            wos_clone_order_notes($originalorderid, $new_order_ids);
                                            if(wc_os_order_removal()){
                                                wc_os_trash_post($originalorderid);
                                            }
                                        }else{
											do_action('group_by_acf_group_fields_inner_hook_1', $original_order);
										}
                                    }
                                }elseif(count($wc_order_items)==1 && $is_bundle){

                                }

                            }
                            //exit;


                        break;
						
                       	case 'group_by_partial_payment':
                            if(!$wc_os_pro){ return; }

							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}

                            if(class_exists('wc_os_bulk_order_splitter')){

                                $classObj = new wc_os_bulk_order_splitter;

                                $is_bundle = false;

                                if($originalorderid && count($original_order->get_items())==1){
                                    foreach($original_order->get_items() as $item_id=>$item_values){
                                        $pid = $item_values->get_product_id();
                                        $vid = $item_values->get_variation_id();
                                        $product_item = $vid?$vid:$pid;
                                        $product = wc_get_product($product_item);
                                        $is_bundle = (method_exists($product, 'get_type') && $product->get_type()=='bundle');
                                    }
                                }

                                if(count($wc_order_items)>1){

                                    $new_order_ids = $classObj->split_order_partial_payment($wc_order_items, $originalorderid, $user_id, $wc_order_items_variations, $wc_os_cart);
                                    //exit;
                                    if($originalorderid){
                                        $order_data_check = wc_get_order($originalorderid);
                                      
                                        if(count($order_data_check->get_items())<1){
                                            wos_clone_order_notes($originalorderid, $new_order_ids);
                                            if(wc_os_order_removal()){
                                                wc_os_trash_post($originalorderid);
                                            }
                                        }
                                    }
                                }elseif(count($wc_order_items)==1 && $is_bundle){

                                }

                            }
                            //exit;

                        break;			
						case 'group_by_order_item_meta':
						
							if(!$wc_os_pro){ return; }
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}


							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$new_order_ids = $classObj->split_group_by_order_item_meta($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
								
							}
						break;	
						case 'group_by_gf_meta':
						
							if(!$wc_os_pro){ return; }
							
							if($this->cron_light_in_progress){
								return array('wc_os_ie_selected'=>$wc_os_ie_selected);
							}


							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter') && count($wc_order_items)>1){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$new_order_ids = $classObj->split_group_by_gf_meta($wc_order_items, $originalorderid, $user_id, $wc_os_cart);
								
							}
						break;								
						
							
					}		
				}				
				
				//pree('$originalorderid: '.$originalorderid);
				
				if($originalorderid){
					
					$wc_os_reduce_stock = array_key_exists('wc_os_reduce_stock', $wc_os_general_settings);
					$reduce_stock_as_default = (!in_array($wc_os_settings['wc_os_ie'], array('inclusive', 'default')) && !$wc_os_reduce_stock);//'io', 
					
					if($reduce_stock_as_default){
						wc_maybe_reduce_stock_levels($originalorderid);
					}
					
					wc_os_update_split_status($originalorderid,2493,true,$new_order_ids);//, 'split_status', 'Delta');
					

					//wc_os_logger('debug', 'CREATING #'.$order_id, true);
					
					//wc_os_logger('debug', $new_order_ids, true);
										
					if(!empty($new_order_ids)){
						
						wc_os_update_order_meta($originalorderid, '_wos_calculate_totals', true);
						//update_post_meta($originalorderid, 'order_splitted', date('H:i:s A'));
						wos_clone_order_notes($originalorderid, $new_order_ids);

						//wos_email_notification(array('new'=>$new_order_ids, 'original'=>$originalorderid), 'split');
						do_action('wc_os_after_order_split', $new_order_ids, $originalorderid);//exit;
						
						foreach($new_order_ids as $order_id){
							
							$new_order_obj = wc_get_order($order_id);
	
							$_customer_user_agent = wc_os_get_order_meta($originalorderid, '_customer_user_agent', true);
							
							$_wos_out_of_stock = wc_os_get_order_meta($order_id, '_wos_out_of_stock', true);
							
							wc_os_update_order_meta($order_id, 'splitted_from', $originalorderid);
							
							

							
							if($_customer_user_agent)
							wc_os_update_order_meta( $order_id, '_customer_user_agent',  $_customer_user_agent);
							
							//if(!$wc_os_tax_cost) //26/02/2024
							//update_post_meta($order_id, 'wos_remove_taxes', true);
							
							if(function_exists('wc_os_order_remove_fee')){
								wc_os_order_remove_fee($order_id, '_line_total');
							}							
							if(function_exists('wc_os_add_shipping_fee')){							
								wc_os_add_shipping_fee($order_id);
							}						
							
							wc_os_update_order_meta($order_id, '_wos_calculate_totals', true);		
							
								
							//wc_os_logger('debug', '$order_id: '.$order_id.', $original_order->get_status(): '.$original_order->get_status().', $_wos_out_of_stock: '.$_wos_out_of_stock, true);
							
							if(is_object($new_order_obj)){
								
								wc_os_update_order_meta($order_id, '_wc_os_total_items', (is_object($new_order_obj)?count($new_order_obj->get_items()):0));
								
								if(!$_wos_out_of_stock){ //23/01/2024 Steve Senella - Sleemo Trading
									$new_order_obj->set_status(wc_os_add_prefix('on-hold', 'wc-')); // 20/01/2024 Daniel Chan - Snapshades
									$new_order_obj->set_status(wc_os_add_prefix($original_order->get_status(), 'wc-'));
								}
								
								if(function_exists('wc_avatax')){
									wc_os_logger('debug', 'DURING CHECKOUT for CHILD ORDER: wc_avatax()->get_order_handler()->estimate_tax for #'.$order_id, true);
									
									wc_avatax()->get_order_handler()->estimate_tax( $new_order_obj );
								}
								$new_order_obj->calculate_totals(); //11/07/2024
								$new_order_obj->save();
							}
								
							
							
						}	
						
						if(function_exists('wc_os_order_remove_fee')){
							wc_os_order_remove_fee($originalorderid, '_line_total');
						}		
						if(function_exists('wc_os_add_shipping_fee')){							
							wc_os_add_shipping_fee($originalorderid, false, true);
						}			
						//$parent_order_status = wc_os_order_split_removal_action('string', 2470);
						
						//if($parent_order_status){
							
							//wc_os_set_order_status($originalorderid, '', false, 2497); //10-12-2021
						//}	
						
						wc_os_delete_order_meta($originalorderid, 'wc_os_order_splitter_cron');//split_order_logic

						
					}else{
						
						wc_os_update_order_meta($originalorderid, 'wc_os_order_splitter_cron', true);			

					}
					
					if(function_exists('wc_os_set_empty_order_status')){
						wc_os_set_empty_order_status($originalorderid);
						//update_post_meta($originalorderid, 'order_empty', date('H:i:s A'));
					}
										
				}elseif($wc_os_pro && $wc_os_packages_overview && $wc_os_cart){
					

					if(!empty($new_order_ids)){
					
						if($return_ids){
							return $new_order_ids;
						}else{
							
							wc_os_packages_overview($new_order_ids, $new_order_ids_qty);
						}
						
					}else{
						if(function_exists('wc_os_update_shipping_cost')){
							wc_os_update_shipping_cost(0, 0, array(), true);
						}												
					}
					
				}

				if($wc_os_cart){
					WC()->session->set( 'wc_os_expected_child_orders', count($new_order_ids));
				}

			}else{
				
				//06 March 2019 - with Sean Owen
				$split_qty = wc_os_order_qty_split();
				
				if($split_qty && $originalorderid){
					$this->split_order($originalorderid, $wc_os_products);
					
					wc_os_update_split_status($originalorderid,2673);//, 'split_status', 'Beta');

				}
			}
			
			if($originalorderid){
				
				wc_os_update_order_item_admin($originalorderid);
	
				if(is_object($original_order) && function_exists('wc_avatax')){
					
					wc_os_logger('debug', 'DURING CHECKOUT: wc_avatax()->get_order_handler()->estimate_tax for #'.$originalorderid, true);
	
					wc_avatax()->get_order_handler()->estimate_tax( $original_order );
					
				}
			}
			//exit;
			
			return true;
		}		
		
		
		/*
			- END
			07 January 2019
			Automatic Settings Added 
		*/			
		
		public function wos_delete_order_item($originalorderid=0, $product_ids=array(), $delete=true){
			
			global $wc_os_order_items, $wc_os_effect_parent;
			
			if(!$wc_os_effect_parent)
			return;

			$product_ids = is_array($product_ids)?$product_ids:array($product_ids);
			//wc_os_pree($product_ids);exit;
			$original_order = wc_get_order($originalorderid);
			
			$to_delete = array();
			
			//wc_os_pree($product_ids);
			
			foreach($original_order->get_items() as $item_id=>$item_data){

				if($item_data->get_variation_id()){
					$product_id = $item_data->get_variation_id();
				}else{
					$product_id = $item_data->get_product_id();
				}				
				//wc_os_pree($product_id.' - '.$this->include_item_keys);
				$product_id_to_check = ($this->include_item_keys?$item_id.'|':'').$product_id;
				
				
				
				$items_exist = in_array($product_id_to_check, $product_ids);
				
				if($items_exist){				
				
					$to_delete[] = $item_id; //Jason Mederios - 20/04/2022
						
					/*if(count($original_order->get_items())==count($product_ids)){
					
					}else{
						
					}*/
				}
			}	
			
			//wc_os_pree($to_delete);
			if(!empty($to_delete)){
				if($delete){
					foreach($to_delete as $item_id){
						//wc_os_pree($item_id);
						wc_delete_order_item( $item_id );	
						wc_os_update_order_meta($originalorderid, '_wc_os_effected_order', true);				
					}	
					$original_order = wc_get_order($originalorderid);
					$wc_os_order_items = (empty($original_order->get_items())?array('empty'):$wc_os_order_items);				
				}else{
				}
			}else{
				$product_ids = array();	
				
				wc_os_set_order_status($originalorderid, '', false, 2581, true);
				
			}
			
			return $product_ids;
		
		}
			
		public function wos_remove_order_item($originalorderid, $product_ids){
			
			global $wc_os_order_items;

			$wc_os_ps = (array_key_exists('wc_os_ps', $_POST)?$_POST['wc_os_ps']:array());
			$product_ids = is_array($product_ids)?$product_ids:array($product_ids);

			$original_order = wc_get_order($originalorderid);
			foreach($original_order->get_items() as $item_id=>$item_data){
				//
				//$product_id = $item_data->get_product_id();
				if($item_data->get_variation_id()){
					$product_id = $item_data->get_variation_id();
				}else{
					$product_id = $item_data->get_product_id();
				}				
					
				$unique_id = $item_id.'|'.$product_id;
				
				if(empty($wc_os_ps) && (in_array($product_id, $product_ids) || in_array($unique_id, $product_ids))){
					
					wc_delete_order_item( $item_id );
					wc_os_update_order_meta($originalorderid, '_wc_os_effected_order', true);
				}
		
				if(!empty($wc_os_ps) && in_array($item_id, $_POST['wc_os_ps'])){
					wc_delete_order_item( $item_id );
					wc_os_update_order_meta($originalorderid, '_wc_os_effected_order', true);
				}				
			}	
			$original_order = wc_get_order($originalorderid);
			$wc_os_order_items = (empty($original_order->get_items())?array('empty'):$wc_os_order_items);
			
		}
		
		//
	  	public function split_subscription($originalorderid = 0, $wc_os_products=array()){
			
			global $wc_os_pro, $wc_os_effect_parent, $wc_os_settings, $wc_os_shipping_cost, $yith_pre_order, $wc_os_products_per_order;
			
			$subscription_split = in_array('subscription_split', $wc_os_settings['wc_os_additional']);
		
			$wc_os_ie = $wc_os_settings['wc_os_ie'];
			
			if(!$originalorderid){
				$originalorderid = sanitize_wc_os_data($_GET['subscription_id']);
				$this->processing = false;
			}
			//wc_os_pree($originalorderid);exit;
			if($originalorderid>0){
				$order_data = wcs_get_subscription( $originalorderid );
				
				if(is_object($order_data) && empty($order_data))
				return;
				
				if(count($order_data->get_items())>1){
					foreach( $order_data->get_items() as $item_key => $item_values ){
						
						$consider = true;//($wc_os_all_products || (((in_array($item_key, $wc_os_products) || in_array($item_values->get_product_id(), $wc_os_products)))));

						if($consider){
							$wc_os_products_arr[$item_key] = $item_values->get_product_id();	
						}
					}
				}

		
				$user_id = wc_os_get_order_meta($originalorderid, '_customer_user', true);
				$get_post_meta = wc_os_get_order_meta($originalorderid);
				
		
				$splitting_items = array();
				$item_counter = 0;		
				
				
				//wc_os_pree($wc_os_products_per_order);wc_os_pree($wc_os_products_arr);exit;
				if(!empty($wc_os_products_arr)){
					
					$number_of_products = $wc_os_products_per_order;
					
					$items_count = 0;					
					$order_set = 0;
					$orders_arr = array();
					
					
					foreach( $order_data->get_items() as $item_key => $item_values ){
						$item_counter++;
						
					
						$qty = $item_values->get_quantity();						
					
						
						$item = $item_values->get_data();
						$product_id = $item['product_id'];
						$variation_id = $item['variation_id'];						
						if ($variation_id != 0) {
							$product = new WC_Product_Variation($variation_id);			
						} else {
							$product = new WC_Product($product_id);	
						}	
						
						if($item_values['total']==$product->get_price()){
							$unit_price = $product->get_price();
						}else{
							$unit_price = ($item_values['total']/$qty); //06 March 2019 - with Sean Owen
						}									
						
						
						
						
						$splitting_items[$item_key] = array(							
							'qty' => $qty,
							'product_id' => $product_id,
							'variation_id' => $variation_id,
							'unit_price' => $unit_price,
							'item_key' => $item_key,
							'product' => $product,
							'item_id' => ($variation_id?$variation_id:$product_id),
							
						);
						
						switch($wc_os_ie){
							case 'default':									
								
								if($items_count%$number_of_products==0){ $order_set++; $this->include_items=array(); }	
								
								$items_count++;
								
								$orders_arr[$order_set][$item_key] = $splitting_items[$item_key]['item_key'].'|'.$splitting_items[$item_key]['item_id'];
								
							break;
							
						}
						
					}
					
					//wc_os_pree($orders_arr);exit;
					if(!empty($orders_arr)){
						
						foreach($orders_arr as $orders_items){
								
								$this->include_items = $orders_items;
								
								$order_data_args =  array(
									'post_type'     => 'shop_subscription',
									'post_status'   => wc_os_add_prefix($order_data->get_status(), 'wc-'),
									'ping_status'   => 'closed',
									'post_author'   => $user_id,
									'post_password' => uniqid( 'order_' ),
								);
								

								$order_id_new = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data_args), true );
								
								
								if ( is_wp_error( $order_id_new ) ) {									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'split__error'));
								} else {									
									


									
									$this->include_item_keys = true;
									$this->cloned_order_data($order_id_new, $originalorderid, true, false, $wc_os_shipping_cost, '', true);							
									wc_os_update_split_status($order_id_new, 2909);
									wc_os_update_order_meta($order_id_new, 'splitted_from', $originalorderid);
								}																				
								
							
								$new_order_ids[] = $order_id_new;
								
								if(!empty($this->include_items) && $wc_os_effect_parent){
									$this->include_item_keys = true;
									$this->wos_delete_order_item($originalorderid, $this->include_items);
								}
								
								
								
							}
							
					}
				}
		
				
				if(!empty($splitting_items) && $group_selected_items){
					
					$this->include_items = array();
						
					foreach($splitting_items as $item_key => $splitting_set){
												
						$this->include_items[$item_key] = $splitting_items[$item_key]['item_key'].'|'.$splitting_items[$item_key]['item_id'];						
						
					}
					
					if(!empty($this->include_items) && $wc_os_effect_parent){
						$this->include_item_keys = true;
						$this->include_items = $this->wos_delete_order_item($originalorderid, $this->include_items, false);
					}
					
					if(!empty($this->include_items)){
						$order_data_args =  array(
							'post_type'     => 'shop_subscription',
							'post_status'   => wc_os_add_prefix($order_data->get_status(), 'wc-'),
							'ping_status'   => 'closed',
							'post_author'   => $user_id,
							'post_password' => uniqid( 'order_' ),
						);
						
						$order_id_new = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data_args), true );
						
						if ( is_wp_error( $order_id_new ) ) {									
							if(!$this->cron_in_progress)
							add_action( 'admin_notices', array($this, 'split__error'));
						} else {								
						
								
							$this->include_item_keys = true;
							$this->cloned_order_data($order_id_new, $originalorderid, true, false, $wc_os_shipping_cost, '', true);							
							wc_os_update_split_status($order_id_new, 2881);
						}																				
					
						$new_order_ids[] = $order_id_new;
						
						if(!empty($this->include_items) && $wc_os_effect_parent){
							$this->include_item_keys = true;
							$this->wos_remove_order_item($originalorderid, $this->include_items);
						}
						
					}
						
					
				}

				wc_os_update_split_status($originalorderid,3001);//, 'split_status', 'Alpha');
				$order_data = wc_get_order( $originalorderid );
				$order_data->calculate_totals();

				if(wc_os_order_removal() && empty($_POST['wc_os_ps'])){
					wc_os_trash_post($originalorderid);
				}else{			
					if(is_admin() && !$this->cron_in_progress){
						wp_redirect('edit.php?post_type=shop_subscription&parent_order='.$originalorderid.'&orderby=ID&order=desc');exit;
					}
				}
				
			}
			
			//return true;
		}	
		public function split_order($originalorderid = null, $wc_os_products=array()){
			
			//pree('$originalorderid: '.$originalorderid);
			
			global $wc_os_pro, $wc_os_effect_parent, $wc_os_settings, $wc_os_shipping_cost, $yith_pre_order, $wc_os_products_per_order;

			$wc_os_all_products = (isset($wc_os_settings['wc_os_all_product']) && $wc_os_settings['wc_os_all_product']) ? true : false; //flag indicating to all products are subject to splitting
			
			//if(is_admin()){ return; }

			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			
			$wc_os_order_split_proceed = wc_os_order_split($originalorderid);
			
			//pree('wc_os_order_split($originalorderid) = '.$wc_os_order_split_proceed);exit;
			
			if(!$wc_os_order_split_proceed)
			return;
			
			wc_os_delete_order_meta($originalorderid, '_wc_os_total_items');
			
			$wc_os_ie = $wc_os_settings['wc_os_ie'];
			
			$group_selected_items = true;
			
			
			
			if(!$wc_os_pro){ wc_maybe_reduce_stock_levels($originalorderid); } //wc_os_reduce_order_stock($originalorderid, true);
			
			
			if(!empty($_POST['wc_os_ps'])){//empty($wc_os_products) && 
				$wc_os_products = sanitize_wc_os_data($_POST['wc_os_ps']);
				
				$wc_os_products = array_map(function($item){ return (is_numeric($item)?$item:''); }, $wc_os_products);
				$wc_os_products = array_filter($wc_os_products);
			}
			
			//pree($wc_os_products);exit;
			
			if(empty($wc_os_products)){
				
				switch($wc_os_ie){
					case 'default':
						
						$wc_os_products = $wc_os_settings['wc_os_products'];
						$wc_os_products = ((is_array($wc_os_products) && !empty($wc_os_products))?$wc_os_products:array());
						$wc_os_products = (array_key_exists($wc_os_ie, $wc_os_products)?$wc_os_products[$wc_os_ie]:array());
						$wc_os_products = array_filter($wc_os_products);
						//pree($wc_os_products);exit;
						
						$group_selected_items = false;
						
						
					break;
				}
			}
			
			$proceed = (!empty($wc_os_products) || (array_key_exists('wc_os_ps', $_POST) && !empty($_POST['wc_os_ps'])) || $wc_os_all_products);
			//pree($proceed);exit;
			if($proceed){
			}else{
				return;
			}
			
			
			

			$split_lock = (isset($wc_os_settings['wc_os_additional']['split_lock'])?$wc_os_settings['wc_os_additional']['split_lock']:array());
			$split_lock = is_array($split_lock)?$split_lock:array();
			
			$status_lock_released = empty($split_lock);			
			$new_order_ids = $wc_os_products_arr = array();	
			$proceed = true;
			
			//pree($split_lock);exit;
			if($originalorderid==0){
				$originalorderid = sanitize_wc_os_data($_GET['order_id']);
				$this->processing = false;
			}
			
			//pree('$originalorderid: '.$originalorderid);exit;
			if($originalorderid>0){
				$order_data = wc_get_order( $originalorderid );
				if(is_object($order_data) && empty($order_data))
				return;
				
				$proceed = (!empty($split_lock) && !empty($order_data));
				
				
				if(!empty($wc_os_products) || $wc_os_all_products){
					
					if(count($order_data->get_items())>1){
						
						//pree($order_data->get_items());
						
						foreach( $order_data->get_items() as $item_key => $item_values ){
							
							//pree($wc_os_products);
							//pree('get_product_id: '.$item_values->get_product_id());
							
							$consider = ($wc_os_all_products || (((in_array($item_key, $wc_os_products) || in_array($item_values->get_product_id(), $wc_os_products)))));
							
							//pree('$consider: '.$consider);//exit;

							if($consider){
								$wc_os_products_arr[$item_key] = $item_values->get_product_id();	
							}
						}
						
						//pree($wc_os_products_arr);exit;
						
					}else{
						
						
						wc_os_delete_order_meta($originalorderid, 'wc_os_order_splitter_cron');

						return;
					}
				}

				if($proceed){
					foreach($split_lock as $split_lock_i){				
						if(!$status_lock_released){
							$status_lock_released = $order_data->has_status($split_lock_i);
						}
					}
				}	

				
				if(!$status_lock_released)
				return;
				
				$split_qty = wc_os_order_qty_split();
				$user_id = wc_os_get_order_meta($originalorderid, '_customer_user', true);
				$get_post_meta = wc_os_get_order_meta($originalorderid);
				$qty_splitted = wc_os_get_order_meta($originalorderid, 'qty_splitted', true);
				
				$qty_split_check = ($split_qty && !$qty_splitted);
				$multiple_items_check = (count($order_data->get_items())>1);// && !array_key_exists('split_status', $get_post_meta));
				
				//pree($qty_split_check);pree($multiple_items_check.' - '.count($order_data->get_items()).'  -  '.array_key_exists('split_status', $get_post_meta));pree(debug_backtrace());exit;
				
				if($qty_split_check || $multiple_items_check){
					
				}else{
					return;
				}
				
				$splitting_items = array();
				$item_counter = 0;		
				
				
				$wc_pos_order_type = wc_os_get_order_meta($originalorderid, 'wc_pos_order_type', true);
				$wc_pos_order_type = ($wc_pos_order_type?$wc_pos_order_type:'online');
				
				$_order_stock_reduced = wc_os_get_order_meta($originalorderid, '_order_stock_reduced', true);
				
				
				if(!empty($wc_os_products_arr)){
					
					$number_of_products = $wc_os_products_per_order;
					
					$items_count = 0;					
					$order_set = 0;
					$orders_arr = array();
					
					
					foreach( $order_data->get_items() as $item_key => $item_values ){
						$item_counter++;
						
						//pree('$originalorderid: '.$originalorderid.' / $item_counter: '.$item_counter);
						
						if($item_values->get_product_id() && ($wc_os_all_products || (in_array($item_key, $wc_os_products_arr) || array_key_exists($item_key, $wc_os_products_arr)))){
							$qty = $item_values->get_quantity();						
							$qty_check = ($qty_split_check && $qty>1);

							if($multiple_items_check || $qty_check){
							}else{
								continue;
							}
							
							
							
							$item = $item_values->get_data();
							$product_id = $item['product_id'];
							$variation_id = $item['variation_id'];						
							if ($variation_id != 0) {
								$product = new WC_Product_Variation($variation_id);			
							} else {
								$product = new WC_Product($product_id);	
							}	
							
							if($item_values['total']==$product->get_price()){
								$unit_price = $product->get_price();
							}else{
								$unit_price = ($item_values['total']/$qty); //06 March 2019 - with Sean Owen
							}									
							
							
							
							
							$splitting_items[$item_key] = array(
								'qty_check' => $qty_check,
								'qty' => $qty,
								'product_id' => $product_id,
								'variation_id' => $variation_id,
								'unit_price' => $unit_price,
								'item_key' => $item_key,
								'multiple_items_check' => $multiple_items_check,
								'product' => $product,
								'qty_check' => $qty_check,
								'item_id' => ($variation_id?$variation_id:$product_id),
								
							);
							
							switch($wc_os_ie){
								case 'default':									
									
									if($items_count%$number_of_products==0){ $order_set++; $this->include_items=array(); }	
									
									$items_count++;
									
									$orders_arr[$order_set][$item_key] = $splitting_items[$item_key]['item_key'].'|'.$splitting_items[$item_key]['item_id'];
									
								break;
								
								default:
									$orders_arr[][$item_key] = $splitting_items[$item_key]['item_key'].'|'.$splitting_items[$item_key]['item_id'];
								break;
							}
							
							
							
							
							
							
							
						
												
						}
					}
					
					//pree($orders_arr);pree($splitting_items);exit;
					if(!$group_selected_items && !empty($orders_arr)){
						
						foreach($orders_arr as $orders_items){
								
								$this->include_items = $orders_items;
								
								$order_data_args =  array(
									'post_type'     => $wc_os_get_post_type_default,
									'post_status'   => wc_os_add_prefix($order_data->get_status(), 'wc-'),
									'ping_status'   => 'closed',
									'post_author'   => $user_id,
									'post_password' => uniqid( 'order_' ),
								);
								

								$order_id_new = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data_args), true );
								
								//pree('$order_id_new A: '.$order_id_new);
								//pree($this->include_items);
								
								if ( is_wp_error( $order_id_new ) ) {									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'split__error'));
								} else {			
								
															
									
									wc_os_update_order_meta($order_id_new, '_order_stock_reduced', $_order_stock_reduced);
									wc_os_update_order_meta($order_id_new, 'wc_pos_order_type', sanitize_wc_os_data($wc_pos_order_type)); // NO QTY. SPLIT	
									
									$this->include_item_keys = true;
									$this->cloned_order_data($order_id_new, $originalorderid, true, false, $wc_os_shipping_cost, '', true);							
									wc_os_update_split_status($order_id_new, 2909);
									wc_os_update_order_meta($order_id_new, 'splitted_from', $originalorderid);
									wc_os_update_order_meta($order_id_new, '_wc_os_total_items', count($this->include_items));
									
									//if(!$wc_os_pro){ wc_os_reduce_order_stock($order_id_new, true); }
									
									if(method_exists($this, 'ywpo_add_order_item_meta')){
										$this->ywpo_add_order_item_meta($splitting_set['item_key'], $splitting_set['product']);
									}
									
									
								}																				
								
								//if($yith_pre_order && function_exists('wos_update_orders_again'))				
								//wos_update_orders_again($order_id_new, $originalorderid);
					
							
								$new_order_ids[] = $order_id_new;
								
								if(!empty($this->include_items)){
									if($wc_os_effect_parent){
										$this->include_item_keys = true;
										$this->wos_delete_order_item($originalorderid, $this->include_items);
									}
									foreach($this->include_items as $item_key_used=>$item_key_piped){
										if(array_key_exists($item_key_used, $splitting_items)){
											unset($splitting_items[$item_key_used]);
										}
									}
								}
								
								
								
							}
							
					}
				}
				//wc_os_pree($orders_arr);
				//wc_os_pree($new_order_ids);
				//exit;
				//pree($splitting_items);pree($group_selected_items);//exit;
				if(!empty($splitting_items) && $group_selected_items){
					
					$this->include_items = array();
						
					foreach($splitting_items as $item_key => $splitting_set){
												
						$this->include_items[$item_key] = $splitting_items[$item_key]['item_key'].'|'.$splitting_items[$item_key]['item_id'];						
						
					}
					
					if(!empty($this->include_items) && $wc_os_effect_parent){
						$this->include_item_keys = true;
						$this->include_items = $this->wos_delete_order_item($originalorderid, $this->include_items, false);
					}
					
					if(!empty($this->include_items)){
						$order_data_args =  array(
							'post_type'     => $wc_os_get_post_type_default,
							'post_status'   => wc_os_add_prefix($order_data->get_status(), 'wc-'),
							'ping_status'   => 'closed',
							'post_author'   => $user_id,
							'post_password' => uniqid( 'order_' ),
						);
						
						$order_id_new = wc_os_create_order( apply_filters( 'woocommerce_new_order_data', $order_data_args), true );
						
						//pree('$order_id_new B: '.$order_id_new);
						
						if ( is_wp_error( $order_id_new ) ) {									
							if(!$this->cron_in_progress)
							add_action( 'admin_notices', array($this, 'split__error'));
						} else {								
						
							wc_os_update_order_meta($order_id_new, 'wc_pos_order_type', sanitize_wc_os_data($wc_pos_order_type)); // NO QTY. SPLIT
							wc_os_update_order_meta($order_id_new, '_order_stock_reduced', $_order_stock_reduced);
								
							$this->include_item_keys = true;
							$this->cloned_order_data($order_id_new, $originalorderid, true, false, $wc_os_shipping_cost, '', true);							
							wc_os_update_split_status($order_id_new, 2881);
							wc_os_update_order_meta($order_id_new, 'splitted_from', $originalorderid);
							wc_os_update_order_meta($order_id_new, '_wc_os_total_items', count($this->include_items));
							
							//if(!$wc_os_pro){ wc_os_reduce_order_stock($order_id_new, true); }
							
							if(method_exists($this, 'ywpo_add_order_item_meta')){
								$this->ywpo_add_order_item_meta($splitting_set['item_key'], $splitting_set['product']);
							}
						}																				
						
						//if($yith_pre_order && function_exists('wos_update_orders_again'))				
						//wos_update_orders_again($order_id_new, $originalorderid);
			
					
						$new_order_ids[] = $order_id_new;
						
						if(!empty($this->include_items) && $wc_os_effect_parent){
							$this->include_item_keys = true;
							$this->wos_remove_order_item($originalorderid, $this->include_items);
						}
						
					}
						
					
				}

				wc_os_delete_order_meta($originalorderid, 'wc_os_order_splitter_cron');//split_order

				
				wc_os_update_split_status($originalorderid, 3313, true);//, 'Alpha');
				$order_data = wc_get_order( $originalorderid );
				$order_data->calculate_totals();
				if(!empty($new_order_ids)){
					do_action('wc_os_after_order_split', $new_order_ids, $originalorderid);
				}			
				//pree($new_order_ids);	
				if(wc_os_order_removal() && empty($_POST['wc_os_ps'])){
					wc_os_trash_post($originalorderid);
				}else{			
					if(is_admin() && !$this->cron_in_progress){
						wp_redirect('edit.php?post_type=shop_order&parent_order='.$originalorderid.'&orderby=ID&order=desc');exit;
					}
				}
				
				
				
			}
			return $new_order_ids;
			//return true;
		}	
		/**
		 * Create new WC_Order and clone all exisiting data
		 */
		
		public function cloned_order_data($order_id, $originalorderids = null, $clone_order=true, $reduce_stock=false, $clone_shipping=true, $parent_iter='', $update_order_status=false){
			

			global $yith_pre_order, $wc_os_debug, $wc_os_pro, $wc_os_general_settings, $wc_os_settings, $wc_os_is_combine;
			
			$order_status_directed = '';
			$order = new WC_Order($order_id);
			
			
		
			$originalorderids = (is_array($originalorderids)?$originalorderids:array($originalorderids));
			

			$iter = 0;
			foreach( $originalorderids as $originalorderid ) {	$iter++;
			
				
				
				if ($originalorderid) {
					$this->original_order_id = $originalorderid;
				} else {
					$this->original_order_id = sanitize_wc_os_data($_GET['order_id']);
				}
				
				wc_os_delete_order_meta($this->original_order_id, '_wc_os_total_items');
				
				if(!$wc_os_debug){
					
					wc_os_update_order_meta($order_id, 'cloned_from', $this->original_order_id);
					
					if(!$this->clone_in_progress)				
					wc_os_update_order_meta($order_id, 'splitted_from', $this->original_order_id);
					
					wc_os_update_order_meta($order_id, '_wc_os_total_items', count($order->get_items()));
					
				}

				$original_order = new WC_Order($this->original_order_id);
				
				$get_order_taxes = wc_os_get_order_taxes($original_order);
				
				
				
				
				$order_status = $original_order->get_status();
		
				
				// Check if Sequential Numbering is installed
				
				if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
					
					// Set sequential order number 
					
					$setnumber = new WC_Seq_Order_Number_Pro;
					$setnumber->set_sequential_order_number($order_id);
					
				}
				
				if(!$wc_os_debug){
				
					$this->clone_order_header($order_id);
					$this->clone_order_billing($order_id);
					
					

					
					if(!array_key_exists('wc_os_remove_fees_from_child', $wc_os_general_settings) && !$wc_os_is_combine){
					
						//$this->clone_order_fees($order, $original_order); //02/04/2024
					
					}				
					
					
					
					//$this->clone_order_coupons($order, $original_order);
				
				}
				
				add_filter( 'woocommerce_can_reduce_order_stock','wos_filter_woocommerce_can_reduce_order_stock', 10, 2 );
				
				//pree('$clone_order: '.$clone_order);pree('$wc_os_ie: '.$wc_os_settings['wc_os_ie']);
						
				if($clone_order){
					

					
					if($wc_os_settings['wc_os_ie'] == 'group_by_partial_payment'){

				        $this->auto_split = $wc_os_settings['wc_os_ie'];

                        $this->clone_order_items_by_item($order, $original_order);

                    }else{ //io
						
						
						
				        $this->clone_order_items($order, $original_order, $clone_order);
						

                    }					
					
					
				}elseif($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
					
					$classObj = new wc_os_bulk_order_splitter;

					

					if(method_exists($classObj, 'add_order_items')){

						
						$classObj->include_items = (empty($classObj->include_items)?$this->include_items:$classObj->include_items);
						$classObj->general_array = (empty($classObj->general_array)?$this->general_array:$classObj->general_array);
						//pree($classObj->include_items);pree($classObj->general_array);exit;
						$classObj->add_order_items($order, $get_order_taxes);
						
					}
				}

				//exit;
				
				if(!$wc_os_debug){
					wc_os_update_order_meta( $order_id, '_payment_method', wc_os_get_order_meta($this->original_order_id, '_payment_method', true) );
					wc_os_update_order_meta( $order_id, '_payment_method_title', wc_os_get_order_meta($this->original_order_id, '_payment_method_title', true) );
				}
				
				// POSSIBLE CHANGE? - Set status to on hold as payment is not received		
				if(!$wc_os_debug){
					
					$order->calculate_totals();
					
					if(!$wc_os_is_combine)
					$this->clone_order_shipping_items($order_id, $original_order, false, $clone_shipping);//exit;
					
					$order = new WC_Order($order_id);
					$order->calculate_totals();
					
					
				}

				// Set order note of original cloned order
				
				
				if(function_exists('wos_update_orders_again')){

					if($yith_pre_order){
						wos_update_orders_again($order_id, $this->original_order_id);
					}else{
			
						if($clone_order){		
							wos_clone_order_notes($this->original_order_id, $order_id);
						}
					}
				}
				
	
				$this->meta_keys_clone_from_to($order_id, $this->original_order_id);//exit;
				
				$order->add_order_note(__('Parent Order').' #'.$this->original_order_id.'');
				
				$debug_backtrace = debug_backtrace();
				$function = $debug_backtrace[1]['function'];

				
				if($wc_os_pro){
					$classObj = new wc_os_bulk_order_splitter;

					
					$order_status_directed = $classObj->get_order_status_by_rule($order_id);
					//$order_status_directed = ($order_status_directed?$order_status_directed:$order_status);


				}
				
				
				
				
				//pree('$function: '.$function);pree('$update_order_status: '.$update_order_status);
				
				switch($function){
					default:					
						$order_status_directed = ($order_status_directed?$order_status_directed:wc_os_order_split_status_action());
						
						//pree('$order_status_directed: '.$order_status_directed);
						
						if($update_order_status){						
							$split_status_value = wc_os_method_based_default_order_status($order_status_directed, $originalorderid);		
							if($split_status_value){										
								wc_os_set_order_status($order_id, '', false, 3250);//$split_status_value
							}
						}
					break;
					case 'clone_order':
					
					break;
				}
				
				
				
				// Returns success message on clone completion
				if(!$this->cron_in_progress)
				add_action( 'admin_notices', array($this, 'clone__success'));

			
			}
			
		}
		
		
		
		public function meta_keys_clone_from_to($order_id_to=0, $order_id_from=0){
			
			if(class_exists('vxc_zoho')){
				$vxc_zoho = new vxc_zoho();
				if(method_exists($vxc_zoho, 'order_submit')){					
					//$vxc_zoho->order_submit($order_id_to);
					//$vxc_zoho->order_submit($order_id_to);
					//$vxc_zoho->push($order_id_to);
				}
			}else{
				
			}
			
			global $wpdb, $wc_os_meta_handling_arr, $is_wc_booking, $wc_os_settings;
			$this->auto_split = $wc_os_settings['wc_os_ie'];
	

			if($order_id_from && $order_id_to){
				
				
				wc_os_update_order_meta( $order_id_to, '_billing_address_index', wc_os_get_order_meta($order_id_from, '_billing_address_index', true) );
				wc_os_update_order_meta( $order_id_to, '_shipping_address_index', wc_os_get_order_meta($order_id_from, '_shipping_address_index', true) );
				
				
				
				//$this->meta_keys_intersection = wc_os_meta_keys_array('group_cats', 'a');
				$diff_key_arr = array('wc_os_order_splitter_cron', 'wos_update_status');
				if(function_exists('WC_Order_Barcodes')){
					$diff_key_arr[] = '_barcode_text';
					$diff_key_arr[] = '_barcode_image';
				}
				
				$order_id_to_meta = wc_os_get_order_meta($order_id_to);
				$order_id_to_keys = array_keys($order_id_to_meta);
				
				$order_id_from_meta = wc_os_get_order_meta($order_id_from);
				$order_id_from_keys = array_keys($order_id_from_meta);
				
				$arr_diff = array_diff($order_id_from_keys, $order_id_to_keys);
				
				$exclude_arr = array('vxc_zoho_order', '_edit_lock');
				$arr_diff = array_diff($arr_diff, $exclude_arr);
				
				
				if(!empty($arr_diff)){
					foreach($arr_diff as $diff_key){


                        $diff_key_check = 'order_meta|'.$diff_key;
						//pree($diff_key);pree($order_id_from_meta);
						if(array_key_exists($diff_key, $order_id_from_meta)){
							$diff_value = (is_array($order_id_from_meta[$diff_key])?current($order_id_from_meta[$diff_key]):$order_id_from_meta[$diff_key]);
							
							
							
							if(
									!in_array($diff_key, $diff_key_arr) 
								&& 
									(
											empty($this->meta_keys_intersection)
										||
											
											(
													!empty($this->meta_keys_intersection)
												&&
												
													(
															!in_array('none', $this->meta_keys_intersection)
														&&												
															in_array($diff_key_check, $this->meta_keys_intersection)
													)
													
											)
									)
							){
								
										$diff_value = maybe_unserialize($diff_value);
										

										wc_os_update_order_meta($order_id_to, $diff_key, $diff_value);

                            }elseif(in_array($diff_key, $diff_key_arr)){
								switch($diff_key){
									case '_barcode_text':	
																			
									break;
									case '_barcode_image':
										if(class_exists('WooCommerce_Order_Barcodes') && function_exists('wos_barcode')){
											$WooCommerce_Order_Barcodes  = new WooCommerce_Order_Barcodes;
											if( 'yes' == $WooCommerce_Order_Barcodes->barcode_enable ) {
												$barcode_string = $WooCommerce_Order_Barcodes->get_barcode_string();
												$_barcode_text = wc_os_update_order_meta($order_id_to, '_barcode_text', $barcode_string);
											
												$diff_value = wos_barcode($_barcode_text, $WooCommerce_Order_Barcodes->barcode_type);	
											
												wc_os_update_order_meta($order_id_to, $diff_key, $diff_value);		
												
											}
										}
									break;									
								}
							}
						}
					}
				}
				
				$original_order = wc_get_order($order_id_from);
				$new_order = wc_get_order($order_id_to);
		
				
				$old_order_items = array();
				$old_order_items_by_item = array();
				if(!empty($original_order->get_items())){

				    foreach($original_order->get_items() as $item_id=>$item_data){
			
	
						$item_meta = $this->wc_os_get_order_item_meta($item_id);

						
						$pid = $item_data->get_product_id();
						$vid = $item_data->get_variation_id();	
						
						$old_order_items_by_item[$item_id] = $item_meta;
						
						$old_order_items[$pid][$vid] = $item_meta;
										
					
					}

                }
				
				$order_items = array();
				
				$old_order_items_mapped = array();

				if(!empty($new_order->get_items())){
					

				    foreach($new_order->get_items() as $item_id=>$item_data){
						
						
                        $pid = $item_data->get_product_id();
                        $vid = $item_data->get_variation_id();
						
						$order_items[] = ($vid?$vid:$pid);
						$old_meta_id = $item_data->get_meta('_old_item_id');
		
						$old_order_items_mapped[$item_id] = $old_meta_id;
						if(in_array($this->auto_split, $wc_os_meta_handling_arr)){

							
							
							if($is_wc_booking && !empty($this->booking_ids)){
							    $current_booking_id = array_key_exists($old_meta_id, $this->booking_ids) ? $this->booking_ids[$old_meta_id] : 0;

							    if($current_booking_id){
							        wc_os_update_order_meta($current_booking_id, '_bkap_order_item_id', $item_id);
                                }
                            }							

                            if($old_meta_id && !empty($old_order_items_by_item) && array_key_exists($old_meta_id, $old_order_items_by_item)){

                                $item_meta = $old_order_items_by_item[$old_meta_id];
                                foreach($item_meta as $key=>$value){

                                    $diff_key_check = 'line_item_meta|'.$key;
                                    $value = (is_array($value)?current($value):$value);

            

                                    $existing_value = wc_get_order_item_meta($item_id, $key, true);

               
                                    if(empty($this->meta_keys_intersection)){

                            

                                        if($existing_value==''){

                                            //$mval = is_array($value)?current($value):$value;

                                            $mval = maybe_unserialize($value);

                                            wc_update_order_item_meta($item_id, $key, $mval);

                                        }

                                    }elseif (



                                        !empty($this->meta_keys_intersection)
                                        &&
                                        (
                                            !in_array('none', $this->meta_keys_intersection)
                                            &&
                                            in_array($diff_key_check, $this->meta_keys_intersection)
                                        )

                                    ){

                                        //$mval = is_array($value)?current($value):$value;

                                        $mval = maybe_unserialize($value);
										
                                        wc_update_order_item_meta($item_id, $key, $mval);

                                    }





                                }


                            }

														
						}elseif(!empty($old_order_items) && array_key_exists($pid, $old_order_items)){
							
							
							
						    if(array_key_exists($vid, $old_order_items[$pid])){
                                $item_meta = $old_order_items[$pid][$vid];

                                foreach($item_meta as $key=>$value){

                                    $diff_key_check = 'line_item_meta|'.$key;
                                    $value = (is_array($value)?current($value):$value);
									
									

                                    $existing_value = wc_get_order_item_meta($item_id, $key, true);
									

                                    if(empty($this->meta_keys_intersection)){
										
									

                                        if($existing_value==''){

											//$mval = is_array($value)?current($value):$value;
	
											$mval = maybe_unserialize($value);
											
                                            wc_update_order_item_meta($item_id, $key, $mval);
											
											
                                        }

                                    }elseif (
									
										

                                        !empty($this->meta_keys_intersection)
                                        &&
                                        (
                                            !in_array('none', $this->meta_keys_intersection)
                                            &&
                                            in_array($diff_key_check, $this->meta_keys_intersection)
                                        )

                                    ){
										
										//$mval = is_array($value)?current($value):$value;
										

										$mval = maybe_unserialize($value);							
                                        wc_update_order_item_meta($item_id, $key, $mval);
										
                                    }


							    }
						    }
					    }
						$item_data->delete_meta_data('_old_item_id');
						$item_data->save();

				    }
					
					wc_os_update_order_meta($order_id_to, '_old_order_items_mapped', $old_order_items_mapped);
                }
				
				//exit;	
				
				$shipping_items = "SELECT p.* FROM `".$wpdb->prefix."woocommerce_order_items` p WHERE p.order_item_type = 'shipping' AND p.order_id=".$order_id_from;

				$shipping_items_unique_keys = $wpdb->get_results($shipping_items);

				
				if(!empty($shipping_items_unique_keys)){
					foreach($shipping_items_unique_keys as $shipping_item_meta){			
					
				
						$shipping_itemmeta_query = 'SELECT * FROM `'.$wpdb->prefix.'woocommerce_order_itemmeta` WHERE order_item_id IN ('.$shipping_item_meta->order_item_id.')';	
						

						$shipping_itemmeta_result = $wpdb->get_results($shipping_itemmeta_query);

						if(!empty($shipping_itemmeta_result)){

                            $method_id = 0;
                            $cost = 0;
							foreach($shipping_itemmeta_result as $k=>$itemmeta_row){

							    if($itemmeta_row->meta_key == 'method_id' ){
							        $method_id = $itemmeta_row->meta_value;
                                }
                                if($itemmeta_row->meta_key == 'cost' ){
                                    $cost = $itemmeta_row->meta_value;
                                }
								$itemmeta_row_arr = maybe_unserialize($itemmeta_row->meta_value);
								if(is_array($itemmeta_row_arr) && array_key_exists('items', $itemmeta_row_arr) ){
								    foreach ($itemmeta_row_arr['items'] as $product_item => $flag){
								        if($flag && in_array($product_item, $order_items)){

                                            $order_shipping_data = array(
                                                'order_item_name'       => $shipping_item_meta->order_item_name,
                                                'order_item_type'       => 'shipping'
                                            );
                                            $item_id = wc_add_order_item( $order_id_to, $order_shipping_data );
											
											
                                            if ( $item_id ) {

                                                wc_add_order_item_meta( $item_id, 'method_id', $method_id );
                                                wc_add_order_item_meta( $item_id, 'cost',  $cost );
												
												wc_os_update_order_meta($order_id_to, '_wos_keep_shipping', $item_id);
                                            }


                                        }
                                    }

								}
							}
						}
						//exit;
						
					}
					
				}
				

//				exit;



				$this->meta_keys_intersection = array();
			}			
		}
		
		function wc_os_get_order_item_meta($item_id){
			$obj = array();
			if($item_id){
				global $wpdb;
				$item_meta_query = 'SELECT * FROM `'.$wpdb->prefix.'woocommerce_order_itemmeta` WHERE order_item_id='.$item_id;

				$results = $wpdb->get_results($item_meta_query);
				if(!empty($results)){
					foreach($results as $result){
						$obj[$result->meta_key] = $result->meta_value;//array($result->meta_value);//
					}
				}
			}
			return $obj;
		}

	    public function splitted_order_data($order_id=0, $originalorderid = null, $product_id=0, $variation_id=0, $qty=false, $_order_total=false, $reduce_stock=false, $order_item_id=0){
			
			
			
			
		    global $wc_os_pro, $yith_pre_order, $wc_os_general_settings, $wc_os_tax_cost;
			
			$product_item = $variation_id?$variation_id:$product_id;			

		    if($wc_os_pro){
			    $classObj = new wc_os_bulk_order_splitter;
		    }

		    $order = new WC_Order($order_id);

		    if ($originalorderid != null) {
			    $this->original_order_id = $originalorderid;
		    } else {
			    $this->original_order_id = sanitize_wc_os_data($_GET['order_id']);
		    }


		    $original_order = new WC_Order($this->original_order_id);

// Check if Sequential Numbering is installed

		    if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {

// Set sequential order number

			    $setnumber = new WC_Seq_Order_Number_Pro;
			    $setnumber->set_sequential_order_number($order_id);

		    }


		    wc_os_update_order_meta($order_id, 'splitted_from', $originalorderid);
			wc_os_update_order_meta($order_id, '_wc_os_total_items', count($order->get_items()));

		    $this->clone_order_header($order_id, $_order_total);
		    $this->clone_order_billing($order_id);
		    $this->clone_order_shipping($order_id);

		    if ($variation_id != 0) {
			    $product = new WC_Product_Variation($variation_id);

		    } else {
			    $product = new WC_Product($product_id);
		    }

		    $is_virtual = ($product->is_virtual('yes'));//($product->virtual=='yes');






			
			if(!array_key_exists('wc_os_remove_fees_from_child', $wc_os_general_settings)){
			
				$this->clone_order_fees($order, $original_order);
			
			}			

		    $this->clone_order_coupons($order, $original_order);
			
		    $this->splitted_order_items($order, $original_order, $product_id, $variation_id, $qty, $_order_total, $order_item_id);



		    if(!$is_virtual) //14/11/2018
			    $this->clone_order_shipping_items($order_id, $original_order, $qty);

		    if(!$original_order->get_total_tax()){
			    wc_os_delete_order_meta($order_id, 'vat_compliance_country_info');
			    wc_os_delete_order_meta($order_id, 'wceuvat_conversion_rates');
			    wc_os_delete_order_meta($order_id, 'vat_compliance_vat_paid');
//delete_post_meta($order_id, 'wc_pos_order_type');
//$order->set_total(0);
				//if(!$wc_os_tax_cost) //26/02/2024
			    //update_post_meta($order_id, 'wos_remove_taxes', true);


		    }
//vat_compliance_country_info

//exit;



		    //update_post_meta( $order_id, '_payment_method', get_post_meta($this->original_order_id, '_payment_method', true) );
		    //update_post_meta( $order_id, '_payment_method_title', get_post_meta($this->original_order_id, '_payment_method_title', true) );
			
			wc_os_update_order_meta( $order_id, '_payment_method', wc_os_get_order_meta($this->original_order_id, '_payment_method', true) );
			wc_os_update_order_meta( $order_id, '_payment_method_title', wc_os_get_order_meta($this->original_order_id, '_payment_method_title', true) );

// Reduce Order Stock
//if($reduce_stock)
//wc_reduce_stock_levels($order_id);

// POSSIBLE CHANGE? - Set status to on hold as payment is not received
		    $order_status = $original_order->get_status();


			$order_status_directed = '';
		    if($wc_os_pro){


				//START >> 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES



			    $order_status_by_rule = $classObj->get_order_status_by_rule($order_id, $product_item);


				
				if($order_status_by_rule){
				
					$order_status_directed = $order_status_by_rule?'wc-'.str_replace('wc-', '', $order_status_by_rule):'';
					
				}else{
				
				    //$order_status = ($order_status_by_rule?$order_status_by_rule:$order_status);
					
				}
				
				
				//END << 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES

		    }

			
		    $order_status_directed = ($order_status_directed?$order_status_directed:wc_os_order_split_status_action());


		    $order->add_order_note(__('Cloned Order from').' #'.$this->original_order_id.'');


		    $order->calculate_totals();
		    $_order_total = $order->calculate_totals();
		    wc_os_update_order_meta( $order_id, '_order_total', $_order_total);
// Returns success message on clone completion

		    if(!$this->cron_in_progress){
			    add_action( 'admin_notices', array($this, 'split__success'));
			}

		
		    $this->meta_keys_clone_from_to($order_id, $originalorderid);
			
		    if($wc_os_pro){
			    $classObj->wos_clone_order_item_meta_data($order_id, $product_id, $variation_id, $order_item_id);
		    }
			
			//sleep(1);
		    if($order_status_directed){
			    //wp_update_post(array('ID'=>$order_id, 'post_status'=>$order_status_directed));
				wc_os_set_order_status($order_id, $order_status_directed, false, 3856);
				wc_os_update_ignored_cron_array($order_id);
				//update_wcfm_order_status($order_id, $order_status_directed);
				
		    }else{

				$update_post = array('ID'=>$order_id, 'post_status'=>'wc-'.$order_status);
				
				
				wc_os_set_order_status($order_id, 'wc-'.$order_status, false, 3867);
				//update_wcfm_order_status($order_id, 'wc-'.$order_status);
		    }

			//exit;
	    }

		/**
		 * Duplicate Order Header meta
		 */
		
		public function clone_order_header($order_id, $_order_total=false){
			
			global $wpdb, $wc_os_custom_orders_table_enabled;

			if($_order_total){
				
			}else{
				
				$_order_total = wc_os_get_order_meta($this->original_order_id, '_order_total', true);
				
			}
			
			
			wc_os_update_order_meta( $order_id, '_order_shipping', wc_os_get_order_meta($this->original_order_id, '_order_shipping', true) );
			wc_os_update_order_meta( $order_id, '_order_discount', wc_os_get_order_meta($this->original_order_id, '_order_discount', true) );
			//update_post_meta( $order_id, '_cart_discount', wc_os_get_order_meta($this->original_order_id, '_cart_discount', true) ); //02/04/2024
			wc_os_update_order_meta( $order_id, '_order_tax', wc_os_get_order_meta($this->original_order_id, '_order_tax', true) );
			wc_os_update_order_meta( $order_id, '_order_shipping_tax', wc_os_get_order_meta($this->original_order_id, '_order_shipping_tax', true) );
			wc_os_update_order_meta( $order_id, '_order_total',  sanitize_wc_os_data($_order_total));
	
			wc_os_update_order_meta( $order_id, '_order_key', 'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
			wc_os_update_order_meta( $order_id, '_customer_user', wc_os_get_order_meta($this->original_order_id, '_customer_user', true) );
			wc_os_update_order_meta( $order_id, '_order_currency', wc_os_get_order_meta($this->original_order_id, '_order_currency', true) );
			wc_os_update_order_meta( $order_id, '_prices_include_tax', wc_os_get_order_meta($this->original_order_id, '_prices_include_tax', true) );
			wc_os_update_order_meta( $order_id, '_customer_ip_address', wc_os_get_order_meta($this->original_order_id, '_customer_ip_address', true) );
			wc_os_update_order_meta( $order_id, '_customer_user_agent', wc_os_get_order_meta($this->original_order_id, '_customer_user_agent', true) );
			
	
			$_tribe_tickets_meta = wc_os_get_order_meta($this->original_order_id, '_tribe_tickets_meta', true);
			if($_tribe_tickets_meta)
			wc_os_update_order_meta( $order_id, '_tribe_tickets_meta', wc_os_get_order_meta($this->original_order_id, '_tribe_tickets_meta', true) );
			
			if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders')){
				
				$original_order_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."wc_orders WHERE id=".$this->original_order_id, ARRAY_A);
				
				if(!empty($original_order_data)){
					
					$data = $original_order_data;
					unset($data['id']);
					//unset($data['status']);
					unset($data['tax_amount']);
					unset($data['total_amount']);
					unset($data['date_created_gmt']);
					unset($data['date_updated_gmt']);
					
					$where = array('id' => $order_id);
					
					$updated = $wpdb->update( $wpdb->prefix.'wc_orders', $data, $where );
				}
			
			}
			
			if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_order_addresses')){
			
				$original_order_addresses = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_order_addresses WHERE order_id=".$this->original_order_id, ARRAY_A);
				$new_order_address_billing = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_order_addresses WHERE order_id=".$order_id." AND address_type='billing'", ARRAY_A);
				$new_order_address_shipping = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_order_addresses WHERE order_id=".$order_id." AND address_type='shipping'", ARRAY_A);
				
				//pree($order_id);
				//pree($original_order_addresses);exit;
				//pree($new_order_addresses);
				//exit;
				
				if(!empty($original_order_addresses)){
					
					foreach($original_order_addresses as $original_order_address){
						
						
						$data = $original_order_address;
						
						
						$data['order_id'] = $order_id;					
						
						unset($data['id']);
						
						$where = array();
						
						switch($data['address_type']){
							 case 'billing':
								if(!empty($new_order_address_billing)){
									$where = array('id'=>$new_order_address_billing['id']);
									$updated = $wpdb->update( $wpdb->prefix.'wc_order_addresses', $data, $where );
								}
							 break;
							 case 'shipping':
								 if(!empty($new_order_address_shipping)){
									$where = array('id'=>$new_order_address_shipping['id']);
									$updated = $wpdb->update( $wpdb->prefix.'wc_order_addresses', $data, $where );
								}
							 break;
						}
						
						if(empty($where)){
						
							$inserted = $wpdb->insert( $wpdb->prefix.'wc_order_addresses', $data );
							
						}else{
							
							
						}
						
						//pree($data);pree($inserted);pree($wpdb->last_query);
					}				
					
				}
				
			}
			//exit;
			
		}
		
		/**
		 * Duplicate Order Billing meta
		 */
		
		public function clone_order_billing($order_id){
	
			wc_os_update_order_meta( $order_id, '_billing_city', wc_os_get_order_meta($this->original_order_id, '_billing_city', true));
			wc_os_update_order_meta( $order_id, '_billing_state', wc_os_get_order_meta($this->original_order_id, '_billing_state', true));
			wc_os_update_order_meta( $order_id, '_billing_postcode', wc_os_get_order_meta($this->original_order_id, '_billing_postcode', true));
			wc_os_update_order_meta( $order_id, '_billing_email', wc_os_get_order_meta($this->original_order_id, '_billing_email', true));
			wc_os_update_order_meta( $order_id, '_billing_phone', wc_os_get_order_meta($this->original_order_id, '_billing_phone', true));
			wc_os_update_order_meta( $order_id, '_billing_address_1', wc_os_get_order_meta($this->original_order_id, '_billing_address_1', true));
			wc_os_update_order_meta( $order_id, '_billing_address_2', wc_os_get_order_meta($this->original_order_id, '_billing_address_2', true));
			wc_os_update_order_meta( $order_id, '_billing_country', wc_os_get_order_meta($this->original_order_id, '_billing_country', true));
			wc_os_update_order_meta( $order_id, '_billing_first_name', wc_os_get_order_meta($this->original_order_id, '_billing_first_name', true));
			wc_os_update_order_meta( $order_id, '_billing_last_name', wc_os_get_order_meta($this->original_order_id, '_billing_last_name', true));
			wc_os_update_order_meta( $order_id, '_billing_company', wc_os_get_order_meta($this->original_order_id, '_billing_company', true));
			
			do_action('clone_extra_billing_fields_hook', $order_id, $this->original_order_id);
			
		}
		
		/**
		 * Duplicate Order Shipping meta
		 */
		
		public function clone_order_shipping($order_id){
	
			wc_os_update_order_meta( $order_id, '_shipping_country', wc_os_get_order_meta($this->original_order_id, '_shipping_country', true));
			wc_os_update_order_meta( $order_id, '_shipping_first_name', wc_os_get_order_meta($this->original_order_id, '_shipping_first_name', true));
			wc_os_update_order_meta( $order_id, '_shipping_last_name', wc_os_get_order_meta($this->original_order_id, '_shipping_last_name', true));
			wc_os_update_order_meta( $order_id, '_shipping_company', wc_os_get_order_meta($this->original_order_id, '_shipping_company', true));
			wc_os_update_order_meta( $order_id, '_shipping_address_1', wc_os_get_order_meta($this->original_order_id, '_shipping_address_1', true));
			wc_os_update_order_meta( $order_id, '_shipping_address_2', wc_os_get_order_meta($this->original_order_id, '_shipping_address_2', true));
			wc_os_update_order_meta( $order_id, '_shipping_city', wc_os_get_order_meta($this->original_order_id, '_shipping_city', true));
			wc_os_update_order_meta( $order_id, '_shipping_state', wc_os_get_order_meta($this->original_order_id, '_shipping_state', true));
			wc_os_update_order_meta( $order_id, '_shipping_postcode', wc_os_get_order_meta($this->original_order_id, '_shipping_postcode', true));
			
			do_action('clone_extra_shipping_fields_hook', $order_id, $this->original_order_id);
			
					
		
		
		}
		
		
		/**
		 * Duplicate Order Fees
		 */
		
		public function clone_order_fees($order, $original_order=array()){
			
			$order = is_object($order)?$order:wc_get_order($order);
	
			if(is_object($original_order)){
				$fee_items = $original_order->get_fees();
		 
				if (empty($fee_items)) {
					
				} else {
					
					foreach($fee_items as $fee_key => $fee_value){
						
						$fee_item  = new WC_Order_Item_Fee();
		
						$fee_item->set_props( array(
							'name'        => $fee_value->get_name(),
							'tax_class'   => $fee_value['tax_class'],
							'tax_status'  => $fee_value['tax_status'],
							'total'       => $fee_value['total'],
							'total_tax'   => $fee_value['total_tax'],
							'taxes'       => $fee_value['taxes'],
						) );
						
						$order->add_item( $fee_item );	 
						
					}
					
				}
				
			}elseif(is_array($original_order)){

				foreach($original_order as $order_id){
					
					$order_object = wc_get_order($order_id);
					$fee_items = $order_object->get_fees();
					
					if (empty($fee_items)) {
					
					} else {
						$fees_arr = array();
						foreach($fee_items as $fee_key => $fee_value){
							
							
							$fee_item  = new WC_Order_Item_Fee();
							
							$fee_data = array(
								'name'        => $fee_value->get_name(),
								'tax_class'   => $fee_value['tax_class'],
								'tax_status'  => $fee_value['tax_status'],
								'total'       => $fee_value['total'],
								'total_tax'   => $fee_value['total_tax'],
								'taxes'       => $fee_value['taxes'],
							);
							
			
							$fee_item->set_props( $fee_data );
							
							
							$order->add_item( $fee_item );	 
							$order->save();
							
							
						}
						
					}
					
				}
				//exit;
			}
	   
		}
		
		/**
		 * Duplicate Order Coupon
		 */

        public function clone_order_coupons($order, $original_order){
			

            if(is_object($original_order) && method_exists($original_order, 'get_coupon_codes')) {

                $coupon_items = $original_order->get_coupon_codes();
				$wc_os_skip_default_coupon = wc_os_get_order_meta($original_order, '_wc_os_skip_default_coupon', true);

                if ( empty( $coupon_items ) || $wc_os_skip_default_coupon) {

                } else {

                    foreach ( $original_order->get_items( 'coupon' ) as $coupon_key => $coupon_values ) {

                        $coupon_item = new WC_Order_Item_Coupon();

                        $coupon_item->set_props( array(
                            'name'         => $coupon_values['name'],
                            'code'         => $coupon_values['code'],
                            'discount'     => $coupon_values['discount'],
                            'discount_tax' => $coupon_values['discount_tax'],
                        ) );
						
                        $order->add_item( $coupon_item );
						
                    }
					$order->save();
                }

            }

        }


		
		/**
		 * Clone Items - v 1.3
		 */
		
		public function clone_order_items($order, $original_order, $clone_order=false){
			
			
			
			//if(is_admin()){ return; }
			
			global $wc_os_pro, $yith_pre_order, $wc_os_debug, $wc_os_meta_handling_arr, $wpdb;
			
			$order_id = $order->get_order_number();
			
			$debug_backtrace = debug_backtrace();
			
			$function = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];
			
			wc_os_logger('debug', 'clone_order_items $order #'.$order_id, true); 
			wc_os_logger('debug', 'clone_order_items $original_order #'.$original_order->get_id(), true);
			wc_os_logger('debug', $function, true);
			
			
			$order_status = $original_order->get_status();
			
			$wc_os_ps = (array_key_exists('wc_os_ps', $_POST)?$_POST['wc_os_ps']:array());
			
			$get_order_taxes = wc_os_get_order_taxes($original_order);
				
			$wc_os_tax_cost = ($get_order_taxes>0);

			$original_order_coupon = $original_order->get_items('coupon');					
			foreach($original_order->get_items() as $order_key => $values){

				$unit_price = ($values->get_total()/$values->get_quantity());

				
				if($values->get_variation_id()){
					$applied_product_id = $values->get_variation_id();
					$applied_product_id_to_check = ($this->include_item_keys?$order_key.'|':'').$applied_product_id;
				}else{
					$applied_product_id = $values->get_product_id();
					$applied_product_id_to_check = ($this->include_item_keys?$order_key.'|':'').$applied_product_id;
				}	


				if(!empty($this->exclude_items) && (in_array($applied_product_id_to_check, $this->exclude_items))){ //07 January 2019 - So we can clone, slice, partially clone and/or partially split an order
					continue;
				}
				
				if(!empty($this->include_items) && (!in_array($applied_product_id_to_check, $this->include_items))){ //07 January 2019 - So we can clone, slice, partially clone and/or partially split an order
					continue;
				}				
				
				if(!empty($wc_os_ps) && !in_array($order_key, $wc_os_ps)){					
					continue;
				}
					
		

				if($wc_os_pro){
					//START >> 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES	
					$meta_kv = get_post_meta($applied_product_id);
					$meta_kv = (is_array($meta_kv)?$meta_kv:array());
					if($yith_pre_order){
							
						if(array_key_exists('_ywpo_preorder', $meta_kv)){						
							$order_status = 'wc-on-hold';
							wc_os_update_order_meta( $order_id, '_order_has_preorder', $meta_kv['_ywpo_preorder'][0]);
						}

					}
					//END << 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES
					
				}
				
				if ($values['variation_id'] != 0) {
					$product = new WC_Product_Variation($values['variation_id']);
				
				} else {
					$product = new WC_Product($values['product_id']);	
				}
				

				
				$product_id = (method_exists($product, 'get_type') && $product->get_type()=='variation') ? $product->get_parent_id() : $product->get_id();
				$qty_item_id = (method_exists($product, 'get_type') && $product->get_type()=='variation' ? $product->get_id():$values['product_id']);
				

				
				$unit_price = ($unit_price!=$product->get_price() && $unit_price>0?$unit_price:$product->get_price());

				//pree('$unit_price: '.$unit_price.', $product->get_price(): '.$product->get_price().', $values->get_total(): '.$values->get_total());
				
				$item                       = new WC_Order_Item_Product();
				$item->legacy_values        = $values;
				$item->legacy_cart_item_key = $order_key;				
				
				$product_qty = ((is_array($this->include_items_qty) && (array_key_exists($qty_item_id, $this->include_items_qty)))?$this->include_items_qty[$qty_item_id]:$values['quantity']);
				
				if(in_array($this->auto_split , $wc_os_meta_handling_arr)){	
                    $product_qty = $values['quantity'];
                }
				
				$line_price = $unit_price*$product_qty;

				$discount_price = ($values['subtotal'] - $values['total']);
				$line_price = (!empty($original_order_coupon) && $discount_price > 0 ? $line_price + $discount_price : $line_price);

				
				$set_props = array(
					'quantity'     => $product_qty,
					//'quantity'     => ($product_qty<=$values['quantity']?$product_qty:$values['quantity']),//24/10/2019 because new provided qty should be within ordered qty.
					'variation'    => $values['variation'],					
					'subtotal_tax' => $values['subtotal_tax'],
					'total_tax'    => $values['total_tax'],
					'taxes'        => $values['taxes'],
				);
				//wc_os_logger('debug', '4514 #'.$order->get_order_number().' $wc_os_tax_cost: '.$get_order_taxes, true);
				if($wc_os_tax_cost){
					if($values['line_subtotal_tax'])
					$set_props['subtotal_tax'] = $values['line_subtotal_tax'];
					
					if($values['line_tax'])
					$set_props['total_tax'] = $values['line_tax'];
					
					if($values['line_tax_data'])
					$set_props['taxes'] = $values['line_tax_data'];
				}else{
					wc_os_update_order_meta($order, 'wos_remove_taxes', true);
					$order_obj = array((object)array('ID' => $order));
					wc_os_remove_taxes($order_obj);
				}
				
				$set_props['subtotal'] = $unit_price*$set_props['quantity'];
				$set_props['total'] = $set_props['subtotal'];
				
				//pree($set_props);
				
				$item->set_props($set_props);
				
				

				
				if ( $product ) {
					$item->set_props( array(
						'name'         => $values->get_name(),
						'tax_class'    => $product->get_tax_class(),
						'product_id'   => $product_id,
						'variation_id' => (method_exists($product, 'get_type') && $product->get_type()=='variation') ? $product->get_id() : 0,
					) );
				}

				$item->add_meta_data('_old_item_id', $order_key);
				
				//if(array_key_exists($product_id, $this->include_items_qty))
				$item->set_backorder_meta();

				if($product_qty){
					
					
					$wc_get_order_item_meta = wc_get_order_item_meta($order_key, '');
					
					if(!empty($wc_get_order_item_meta)){
						foreach($wc_get_order_item_meta as $item_id=>$item_data){
							
							if(in_array($item_id, array('product_id', '_product_id', '_variation_id', '_tax_class'))){ continue; }
							
							if(!empty($item_data)){
								foreach($item_data as $item_key=>$item_value){
									$item_value = maybe_unserialize($item_value);
									$item->add_meta_data($item_id, $item_value);
								}
							}
							
							
							
						}
					}
					
					
					$order->add_item( $item );
					

					
					//wc_delete_order_item_meta(, 'split');
					
					
				}else{

				}
			 
			}
			//exit;
			
		}
		
		public function clone_order_items_by_item($order, $original_order){
			
			
            //if(is_admin()){ return; }

            global $wc_os_pro, $yith_pre_order, $wc_os_debug, $wc_os_meta_handling_arr;

            $order_id = $order->get_order_number();
            $order_status = $original_order->get_status();


            $original_order_coupon = $original_order->get_items('coupon');
			
			$get_order_taxes = wc_os_get_order_taxes($original_order);
				
			$wc_os_tax_cost = ($get_order_taxes>0);
				
            foreach($original_order->get_items() as $order_key => $values){

                if($values->get_variation_id()){
                    $applied_product_id = $values->get_variation_id();
                }else{
                    $applied_product_id = $values->get_product_id();
                }

				
                if(!empty($this->exclude_items) && (in_array($order_key, $this->exclude_items))){ //07 January 2019 - So we can clone, slice, partially clone and/or partially split an order
                    continue;
                }

                if(!empty($this->include_items) && (!in_array($order_key, $this->include_items))){ //07 January 2019 - So we can clone, slice, partially clone and/or partially split an order
                    continue;
                }



                if($wc_os_pro){
                    //START >> 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES

                    $meta_kv = get_post_meta($applied_product_id);
                    $meta_kv = (is_array($meta_kv)?$meta_kv:array());
					if($yith_pre_order){

						if(array_key_exists('_ywpo_preorder', $meta_kv)){
							$order_status = 'wc-on-hold';
							wc_os_update_order_meta( $order_id, '_order_has_preorder', $meta_kv['_ywpo_preorder'][0]);
						}

					}

                    //END << 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES

                }

                if ($values['variation_id'] != 0) {
                    $product = new WC_Product_Variation($values['variation_id']);

                } else {
                    $product = new WC_Product($values['product_id']);
                }



                $product_id = (method_exists($product, 'get_type') && $product->get_type()=='variation') ? $product->get_parent_id() : $product->get_id();
                $qty_item_id = (method_exists($product, 'get_type') && $product->get_type()=='variation' ? $product->get_id():$values['product_id']);


                $unit_price = $product->get_price();

                $item                       = new WC_Order_Item_Product();
                $item->legacy_values        = $values;
                $item->legacy_cart_item_key = $order_key;

                $product_qty = $values['quantity'];

                $line_price = ($product_qty>=1?($values['line_total']/$product_qty):$values['line_total']);
                $discount_price = ($values['subtotal'] - $values['total']);
                $line_price = (!empty($original_order_coupon) && $discount_price > 0 ? $line_price + $discount_price : $line_price);


                $set_props = array(
                    'quantity'     => $product_qty,
                    'variation'    => $values['variation'],
                    'subtotal_tax' => 0,
                    'total_tax'    => 0,
                    'taxes'        => array(),
                );
				wc_os_logger('debug', '4678 #'.$order->get_order_number().' $wc_os_tax_cost: '.$get_order_taxes, true);
                if($wc_os_tax_cost){
                    $set_props['subtotal_tax'] = $values['line_subtotal_tax'];
                    $set_props['total_tax'] = $values['line_tax'];
                    $set_props['taxes'] = $values['line_tax_data'];
                }else{
					wc_os_update_order_meta($order, 'wos_remove_taxes', true);
					$order_obj = array((object)array('ID' => $order));
					wc_os_remove_taxes($order_obj);
				}

                if($line_price!=$unit_price){
                    $total = $line_price*$set_props['quantity'];
                }else{
                    $total = false;
                }

                $set_props['subtotal'] = ($total?$total:$unit_price*$set_props['quantity']);
                $set_props['total'] = ($total?$total:$unit_price*$set_props['quantity']);

                $item->set_props($set_props);

                if ( $product ) {
                    $item->set_props( array(
                        'name'         => $values->get_name(),
                        'tax_class'    => $product->get_tax_class(),
                        'product_id'   => $product_id,
                        'variation_id' => (method_exists($product, 'get_type') && $product->get_type()=='variation') ? $product->get_id() : 0,
                    ) );
                }
				
                $item->add_meta_data('_old_item_id', $order_key);
                


                $item->set_backorder_meta();



                if($product_qty){
					
                    $order->add_item( $item );
                }

            }

        }

	    public function splitted_order_items($order, $original_order, $product_id, $variation_id, $qty=false, $total=false, $order_item_id=0){


			
			global $wc_os_general_settings;
			
			$item_meta_data = array();
			
			if($order_item_id){
				
				$item_meta_data = wc_get_order_item_meta($order_item_id, '');

				
				if(!empty($item_meta_data)){
					
					$item_meta_data['_line_total'] = (array_key_exists('_line_total', $item_meta_data)?$item_meta_data['_line_total']:array());
							
					$item_meta_data['_line_total'] = round(current($item_meta_data['_line_total']), 2);
					
					$order_item_uniqueness = base64_encode(serialize($item_meta_data));
					
				}
			
			}
			
			$get_order_taxes = wc_os_get_order_taxes($original_order);
				
			$wc_os_tax_cost = ($get_order_taxes>0);
			
			
		    foreach($original_order->get_items() as $order_key => $values){

			    $get_total = round($values->get_total(), 2);
				

			    if ($values['variation_id'] != 0) {
				    $product = new WC_Product_Variation($values['variation_id']);

			    } else {
				    $product = new WC_Product($values['product_id']);
			    }

			    $unit_price = $product->get_price();
				
				$cond_1 = (
				
						($values['variation_id'] != 0 && $variation_id==$values['variation_id'] && $product_id==$values['product_id'])
					||
						($values['variation_id'] == 0 && $product_id==$values['product_id'])

				);
				
				$cond_2 = (
							   $order_item_id == 0 || ($order_item_id > 0 && $order_item_id==$order_key) //$item_meta_data['_line_total']==$get_total && 
							   
				);
				

			    if($cond_1 && $cond_2){
			    }else{
				    continue;
			    }


			    $item = new WC_Order_Item_Product();
			    $item->legacy_values = $values;
			    $item->legacy_cart_item_key = $order_key;




			    $line_price = ($values['quantity']>=1?($values['line_total']/$values['quantity']):$values['line_total']);



			    $set_props = array(
				    'quantity' => ($qty?$qty:$values['quantity']),
				    'variation' => $values['variation'],
				    'subtotal_tax' => 0,
				    'total_tax' => 0,
				    'taxes' => array(),
			    );
				wc_os_logger('debug', '4806 #'.$order->get_order_number().' $wc_os_tax_cost: '.$get_order_taxes, true);
				if($wc_os_tax_cost){
					$set_props['subtotal_tax'] = $values['line_subtotal_tax'];
					$set_props['total_tax'] = $values['line_tax'];
					$set_props['taxes'] = $values['line_tax_data'];
				}else{
					wc_os_update_order_meta($order, 'wos_remove_taxes', true);
					$order_obj = array((object)array('ID' => $order));
					wc_os_remove_taxes($order_obj);
				}

			    if($line_price!=$unit_price){
				    $total = $line_price*$set_props['quantity'];
			    }else{
				    $total = false;
			    }
			    $set_props['subtotal'] = ($total?$total:$unit_price*$set_props['quantity']);
			    $set_props['total'] = ($total?$total:$unit_price*$set_props['quantity']);
				
				if(array_key_exists('wc_os_remove_price_from_child', $wc_os_general_settings)){
					$set_props['total'] = $set_props['subtotal'] = 0;
				}
				


			    $item->set_props( $set_props );

			    if ( is_object($product) ) {
				    $set_pros = array(
					    'name' => $product->get_name(),
					    'tax_class' => $product->get_tax_class(),
					    'product_id' => (method_exists($product, 'get_type') && $product->get_type()=='variation') ? $product->get_parent_id() : $product->get_id(),
					    'variation_id' => (method_exists($product, 'get_type') && $product->get_type()=='variation' ) ? $product->get_id() : 0,
				    );

				    $item->set_props( $set_pros );
			    }

			    $item->set_backorder_meta();


				$item->add_meta_data('_old_item_id', $order_key);
				
			    $order->add_item( $item );

			    /*
				*/


		    }




			//exit;
	    }


		/**
		 * Clone success
		 */
		
		function clone__success() {
		
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Order has been updated successfully.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	
		}
		
		function split__success() {
		
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Order Splitted.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	
		}	
		
		/**
		 * Clone error
		 */
		
		function merge__error() {
			$class = 'notice notice-error';
			$message = __( 'Consolidation Failed an error has occurred.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		}
				
		function clone__error() {
			$class = 'notice notice-error';
			$message = __( 'Duplication Failed an error has occurred.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		}
		
		
		function split__error() {
			$class = 'notice notice-error';
			$message = __( 'Split Failed an error has occurred.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		}
			
		
		/**
		 * Duplicate Shipping Item Meta
		 * v1.4 - Shipping is added with order items
		 */
		
		public function clone_order_shipping_items($order_id=0, $original_order=array(), $qty=false, $clone_shipping=false){
			
			global $wc_os_is_combine, $is_woocommerce_shipping_usps;
			
			$wc_os_clone_shipping = false;
			
			if(empty($original_order) && !$order_id){ //TROUBLESHOOTING SECTION
				$order_id = $order_id?$order_id:sanitize_wc_os_data($_GET['post']);
				$parent_order_id = wc_os_get_order_meta($order_id, 'splitted_from', true);

				$original_order = $parent_order_id?wc_get_order($parent_order_id):$original_order;

			}

			$original_order_shipping_items = !empty($original_order)?$original_order->get_items('shipping'):array();

			
			$_vendor_id = wc_os_get_order_meta($order_id, '_vendor_id', true);

			$_vendor_id = (is_numeric($_vendor_id)?$_vendor_id:0);

			
			$vendor_related_items = array();
			$items_data = array();

			$child_order = wc_get_order($order_id);

			$shipclass_off = '';
			if(!empty($child_order)){
				foreach($child_order->get_items() as $cart_key=>$item_values){
					$pid = $item_values->get_product_id();
					$vid = $item_values->get_variation_id();	
					$product_item = $vid?$vid:$pid;
					
					if($product_item){
						$_product = wc_get_product($product_item);
						$get_shipping_class = $_product->get_shipping_class();
	
						if(!$shipclass_off){
							$shipclass_off = ($get_shipping_class=='');
						}
					}
					
			
				}
			
			}
			
			
			$_clone_shipping = ($shipclass_off=='' || $shipclass_off);
			
			if($is_woocommerce_shipping_usps && $clone_shipping){
				$wc_os_clone_shipping = true;
			}else{
				$clone_shipping = (($clone_shipping==true && $_clone_shipping==true) || $this->force_clone_in_progress);
			}
			
			$clone_shipping = ((!$clone_shipping && $wc_os_is_combine)?$wc_os_is_combine:$clone_shipping);
			
			if($clone_shipping){
				$this->clone_order_shipping($order_id);
				//add_filter('woocommerce_order_is_vat_exempt', function(){ return true; });				
			}
			//return; //18/01/2022 - MUTED THIS FUNCTION BECAUSE NOW WE HAVE OUR OWN SHIPPING MODULE
			
			
			
			foreach ( $original_order_shipping_items as $order_item ) {
				$item_id = 0;
				
				$cost = wc_format_decimal( $order_item['cost'] );

				if($cost && $qty){ //22/05/2019
					$qty_total = wc_order_total_qty($original_order);

					$per_item_cost = ($cost/$qty_total);

					$cost = ($qty*$per_item_cost);
				
				}
				
				if($clone_shipping){
					
					$item_id = wc_add_order_item( $order_id, array(
						'order_item_name'       => $order_item['name'],
						'order_item_type'       => 'shipping'
					) );
				}else{
					
				}
				
				if ( $item_id ) {
										
					
					$order_item_meta = wc_get_order_item_meta( $order_item->get_id(), '');
					
					
					
					
					if(!empty($order_item_meta)){
						
						foreach($order_item_meta as $order_item_key => $order_item_value){
							
							$order_item_value = maybe_unserialize(current($order_item_value));							
							$items_data[$item_id][$order_item_key] = $order_item_value;
							
							switch($order_item_key){
								case 'vendor_id':
								
									if($_vendor_id && $_vendor_id==$order_item_value){
										$vendor_related_items[$_vendor_id] = $item_id;
									}
								break;
							}
						}
						
						if($wc_os_is_combine || $wc_os_clone_shipping){
							
							wc_add_order_item_meta( $item_id, 'method_id', $order_item['method_id'] );
							wc_add_order_item_meta( $item_id, 'cost',  $cost );
						}
					}
				}
	
			}
			
			if(!empty($items_data)){
				
				if(!empty($vendor_related_items)){
					$items_data_updated = array();
					foreach($vendor_related_items as $vendor_id=>$item_id){						
						$items_data_updated[$item_id] = $items_data[$item_id];
					}
					if(!empty($items_data_updated)){
						$items_data = $items_data_updated;
					}
				}
				
				foreach($items_data as $item_id=>$item_data){
					
					if(!empty($item_data)){
						foreach($item_data as $item_key=>$item_value){
							wc_add_order_item_meta( $item_id, $item_key,  $item_value );
						}
					}
					
				}
			}
			//exit;   
			
		}
		
	}
	
	
	
	
	
	
	function wc_os_currency_symbol(){
		return (function_exists('get_woocommerce_currency_symbol')?get_woocommerce_currency_symbol():'$');
	}
	
	include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/wos-emails.php'));
	
	function sanitize_wc_os_data( $input ) {
		if(is_array($input)){		
			$new_input = array();	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_wc_os_data($val):stripslashes(sanitize_text_field( $val )));
			}			
		}else{
			$new_input = stripslashes(sanitize_text_field($input));			
			if(stripos($new_input, '@') && is_email($new_input)){
				$new_input = sanitize_email($new_input);
			}
			if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
				$new_input = sanitize_url($new_input);
			}			
		}	
		return $new_input;
	}	
	
	
	if(!function_exists('wc_os_pre')){
	function wc_os_pre($data, $param='', $exit=false){
			if(isset($_GET['debug'])){
				if(!$param || ($param && array_key_exists($param, $_GET))){
					wc_os_pree($data);
					if($exit){ exit; }
				}
			}
		}	 
	} 	
	if(!function_exists('wc_os_pree')){
	function wc_os_pree($data=array(), $force=false){
			if((is_user_logged_in() && current_user_can('administrator')) || $force){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
				
			}
		
		}	 
	} 
	include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/wos-stocks.php'));
	include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/functions-inner.php'));
	include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/functions-temp.php'));
	include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/wos-taxonomies.php'));
	
	function wc_os_is_orders_list(){
		//wp-admin/edit.php?post_type=shop_order
		global $pagenow;
		$post_type = (array_key_exists('post_type', $_GET)?sanitize_wc_os_data($_GET['post_type']):'');
		$wc_os_get_post_type_default = wc_os_get_post_type_default();

		return (is_admin() && $pagenow=='edit.php' && $post_type==$wc_os_get_post_type_default)?true:false;
		
		//(isset($_GET['post_type']) && $_GET['post_type']=='shop_order');
	}

	function wc_os_is_subscription_list(){
		//wp-admin/edit.php?post_type=shop_order
		global $pagenow, $wc_os_settings;
					
		$subscription_split = in_array('subscription_split', $wc_os_settings['wc_os_additional']);
					
		$post_type = (array_key_exists('post_type', $_GET)?sanitize_wc_os_data($_GET['post_type']):'');

		return (is_admin() && $pagenow=='edit.php' && ($subscription_split && $post_type=='shop_subscription'))?true:false;
		
		//(isset($_GET['post_type']) && $_GET['post_type']=='shop_order');
	}	
	

	
	function wc_os_update_cron_status_hooks(){
		
		global $wc_os_cron_settings;
		
		$status_hooks = WC_OS_PLUGIN_DIR . '/inc/sections/wc_os_order_status_hooks.php';
		
		
						
		
		$status_hooks_data = '<?php 
		
		';
		
		$order_statuses = function_exists('wc_get_order_statuses')?wc_get_order_statuses():array();
		
		if(in_array('thankyou', $wc_os_cron_settings['statuses'])){
			$status_hooks_data .= '
add_action("woocommerce_thankyou", "wc_os_checkout_order_processed", 10, 1);
				';
		}
		if(in_array('woocommerce_checkout_order_processed', $wc_os_cron_settings['statuses'])){
			$status_hooks_data .= '
add_action("woocommerce_checkout_order_processed", "wc_os_checkout_order_processed", 2, 1);
				';
			
		}
		
		if(!empty($order_statuses)){
			
			foreach($order_statuses as $slug=>$title){ 
				$status = str_replace('wc-', '', $slug);
				if(in_array($status, $wc_os_cron_settings['statuses'])){
					$status_hooks_data .= '
	add_action("woocommerce_order_status_'.$status.'", "wc_os_checkout_order_processed", 10, 1);
					';
				}
			}
			
			try{
				$f = @fopen($status_hooks, 'w');
				if(!is_bool($f) && get_resource_type($f)=='stream'){
					@fwrite($f, $status_hooks_data);
					@fclose($f);
				}
			}						
			catch(Exception $e) {
			  echo 'Message: ' .$e->getMessage();
			}
		}
						
	}
	
	function wc_os_settings_update(){

        global $wc_os_general_settings, $wc_os_per_page, $wc_os_products_per_order;
		
		
		wc_os_crons();

		if(!empty($_POST)){

			global $wc_os_currency, $wc_os_settings, $wc_os_cron_settings;
			$split_action = (isset($_POST['split_action'])?sanitize_wc_os_data($_POST['split_action']):array());
            $products_split_action = isset($_POST['products_split_action']) ? sanitize_wc_os_data($_POST['products_split_action']): array();
            $products_split_action_filtered = array_filter($products_split_action);
			
			

			if(!empty($_POST) && isset($_POST['wc_os_statuses'])){

		        if (
			        ! isset( $_POST['wc_os_crons_field'] )
			        || ! wp_verify_nonce( $_POST['wc_os_crons_field'], 'wc_os_crons_action' )
		        ) {

			        _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
			        exit;

		        } else {


					
					$wc_os_cron_settings['wc-os-last-split-id'] = sanitize_wc_os_data($_POST['wc-os-last-split-id']);
					$wc_os_cron_settings['statuses'] = sanitize_wc_os_data($_POST['wc_os_statuses']);
					
			        update_option('wc_os_cron_settings', $wc_os_cron_settings);
					
					

		        }
	        }
			
			
			if(!empty($_POST) && isset($_POST['wc_os_settings']) && isset( $_POST['wc_os_settings_field'])){


				$wc_os_currency = wc_os_currency_symbol();

				wc_os_settings_refresh();





				if (! wp_verify_nonce( $_POST['wc_os_settings_field'], 'wc_os_settings_action' )
				) {

				   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
				   exit;

				} else {

				   

                    if(array_key_exists('io_options', $_POST['wc_os_settings'])){

                        $io_options = sanitize_wc_os_data($_POST['wc_os_settings']['io_options']);
                        update_option('wc_os_io_options', $io_options);

                    }	
					
					if(array_key_exists('wc_os_products_per_order', $_POST)){

                        $wc_os_products_per_order = sanitize_wc_os_data($_POST['wc_os_products_per_order']);
                        update_option('wc_os_products_per_order', $wc_os_products_per_order);
						

                    }	
						
					
					
					
                    if(isset($_POST['wc_os_method_options'])){

                        $wc_os_method_options = sanitize_wc_os_data($_POST['wc_os_method_options']);

                        wc_os_update_method_options($wc_os_method_options);

                    }
							   		
						
					if(!isset($_POST['wc_os_general_settings'])) {
												
				   		$wos_valid_product_actions = array_key_exists('wos_valid_product_actions', $_POST)?sanitize_wc_os_data($_POST['wos_valid_product_actions']):array();
						$wos_valid_product_actions = (!empty($wos_valid_product_actions)?explode(',', $wos_valid_product_actions):array());
						$wos_valid_product_actions = array_filter($wos_valid_product_actions);
						

				   		$split_action_existing = array();
						if(!empty($wc_os_settings['wc_os_products'])){
							foreach($wc_os_settings['wc_os_products'] as $action_old=>$data_old){
								if(!empty($data_old)){

									foreach($data_old as $item_old){

										$item_old_arr = (is_array($item_old)?$item_old:array($item_old));

										foreach($item_old_arr as $item_old){
											$split_action_existing[$item_old] = $action_old;
										}


									}
								}
							}
						}

						$wc_os_additional = (isset($_POST['wc_os_settings']['wc_os_additional']));

						$wc_os_settings_updated = sanitize_wc_os_data($_POST['wc_os_settings'] );
						
						//wc_os_pree($wc_os_settings_updated);

						$wc_os_settings_updated['wc_os_products'] = (isset($wc_os_settings_updated['wc_os_products']) && is_array($wc_os_settings_updated['wc_os_products']))?$wc_os_settings_updated['wc_os_products']:(isset($wc_os_settings['wc_os_products'])?$wc_os_settings['wc_os_products']:'');
						
						$wc_os_settings_updated['wc_os_group_statuses'] = (array_key_exists('wc_os_group_statuses', $wc_os_settings) ? $wc_os_settings['wc_os_group_statuses'] : array());

						$wc_os_settings_updated['wc_os_ie'] = isset($wc_os_settings_updated['wc_os_ie'])?$wc_os_settings_updated['wc_os_ie']:$wc_os_settings['wc_os_ie'];

						$wc_os_settings_updated['wc_os_cats'] = ((isset($wc_os_settings['wc_os_cats']) && is_array($wc_os_settings['wc_os_cats']))?$wc_os_settings['wc_os_cats']:array());

						$wc_os_settings_updated['wc_os_qty_split_option'] = isset($wc_os_settings_updated['wc_os_qty_split_option'])?$wc_os_settings_updated['wc_os_qty_split_option']:(isset($wc_os_settings['wc_os_qty_split_option'])?$wc_os_settings['wc_os_qty_split_option']:'');

						$wc_os_settings_updated['wc_os_additional'] = (isset($wc_os_settings_updated['wc_os_additional']) && is_array($wc_os_settings_updated['wc_os_additional']))?$wc_os_settings_updated['wc_os_additional']:(isset($wc_os_settings['wc_os_additional'])?$wc_os_settings['wc_os_additional']:'');
						
						$wc_os_settings_updated['wc_os_cats']['split_ratio'] = ((isset($wc_os_settings_updated['wc_os_cats']['split_ratio']) && $wc_os_settings_updated['wc_os_cats']['split_ratio']!='')?$wc_os_settings_updated['wc_os_cats']['split_ratio']:'');

						$wc_os_products_existing = $wc_os_settings['wc_os_products'];
						$wc_os_products_groups_existing = array_key_exists('groups', $wc_os_settings['wc_os_products'])?$wc_os_settings['wc_os_products']['groups']:array();
						

						
						$items_methods = array();
						
						if(!empty($wc_os_products_existing)){
							foreach($wc_os_products_existing as $method=>$items_arr){		
								if(!empty($items_arr)){
									foreach($items_arr as $item_single){						
										if(!is_array($item_single) && $item_single){
											$items_methods[$item_single] = $method;
										}
									}
								}
							}
						}
						
						$items_groups = array();

						if(!empty($wc_os_products_groups_existing)){
							foreach($wc_os_products_groups_existing as $method=>$items_arr){		

								if(is_array($items_arr) && !empty($items_arr)){
									foreach($items_arr as $item_single){						
										if(!is_array($item_single) && $item_single){
											$items_groups[$item_single] = $method;
										}
									}
								}
							}
						}			
						
						if(!empty($items_methods)){
							foreach($items_methods as $item_key=>$item_method){
								if(!in_array($item_key, $wos_valid_product_actions)){
									$wc_os_settings_updated['wc_os_products'][$item_method][] = $item_key;
								}
							}
						}
						if(!empty($items_groups)){
							foreach($items_groups as $item_key=>$item_method){
								if(!in_array($item_key, $wos_valid_product_actions)){
									$wc_os_settings_updated['wc_os_products']['groups'][$item_method][] = $item_key;
								}
							}
						}						
						$wc_os_settings_updated['wc_os_all_products'] = isset($wc_os_settings_updated['wc_os_all_product'],$wc_os_settings_updated['wc_os_ie'])?true:(isset($wc_os_settings['wc_os_all_product'])?$wc_os_settings['wc_os_all_product']:'');

						
						
						
						$wc_os_settings_updated['wc_os_vendors'] = isset($wc_os_settings['wc_os_vendors'])?$wc_os_settings['wc_os_vendors']:array();
						$wc_os_settings_updated['wc_os_woo_vendors'] = isset($wc_os_settings['wc_os_woo_vendors'])?$wc_os_settings['wc_os_woo_vendors']:array();						

						$wc_os_settings_updated['wc_os_products'] = array_merge($wc_os_products_existing, $wc_os_settings_updated['wc_os_products']);

						$wc_os_settings_updated['wc_os_products'] = (!empty($wc_os_settings_updated['wc_os_products'])?array_filter($wc_os_settings_updated['wc_os_products'], 'is_array'):array());
						
						$wc_os_settings_updated['wc_os_attributes'] = isset($wc_os_settings['wc_os_attributes'])?$wc_os_settings['wc_os_attributes']:array();
						
						$wc_os_settings_updated['wc_os_metadata'] = isset($wc_os_settings['wc_os_metadata'])?$wc_os_settings['wc_os_metadata']:array();
						$wc_os_settings_updated['wc_os_attributes_values'] = isset($wc_os_settings['wc_os_attributes_values'])?$wc_os_settings['wc_os_attributes_values']:array();
						$wc_os_settings_updated['wc_os_attributes_nodes'] = isset($wc_os_settings['wc_os_attributes_nodes'])?$wc_os_settings['wc_os_attributes_nodes']:array();
						
						$wc_os_settings_updated['wc_os_attributes_group'] = isset($wc_os_settings['wc_os_attributes_group'])?$wc_os_settings['wc_os_attributes_group']:array();
						
						$wc_os_settings_updated['group_by_order_item_meta'] = isset($wc_os_settings['group_by_order_item_meta'])?$wc_os_settings['group_by_order_item_meta']:array();
						
						
						if(!empty($wc_os_settings_updated['wc_os_products'])){ //07-05-2019
							foreach($wc_os_settings_updated['wc_os_products'] as $item_action=>$item_ids){

								if(!empty($item_ids)){
									foreach($item_ids as $index=>$item_id_item){

										$item_id_arr = (is_array($item_id_item)?$item_id_item:array($item_id_item));



										foreach($item_id_arr as $item_id){



											$item_action_valid = ((is_array($products_split_action_filtered) && isset($products_split_action_filtered[$item_id]))?$products_split_action_filtered[$item_id]:(isset($split_action_existing[$item_id])?$split_action_existing[$item_id]:''));

											if($item_action_valid==$item_action){
											}elseif($item_action && $item_action_valid){


												if(is_numeric($index)){
													unset($wc_os_settings_updated['wc_os_products'][$item_action][$index]);
												}
												$wc_os_settings_updated['wc_os_products'][$item_action_valid][] = $item_id;
											}
										}
									}
								}
							}
							$wc_os_settings_updated['wc_os_products'] = wos_array_unique_recursive($wc_os_settings_updated['wc_os_products']);
						}
						
                        if(!empty($wc_os_settings_updated['wc_os_products'])){
						    foreach($wc_os_settings_updated['wc_os_products'] as $method => $method_products){
						        if($method == 'groups'){
						            continue;
                                }

						        if(!empty($method_products)){
						            foreach($method_products as $product_index => $product_id){

						                if(array_key_exists($product_id, $products_split_action) && !$products_split_action[$product_id]){
                                            unset($wc_os_settings_updated['wc_os_products'][$method][$product_index]);
                                        }
                                    }
                                }
                            }
                        }
                        if(function_exists('wc_os_save_group_statuses')){wc_os_save_group_statuses($wc_os_settings_updated);}		
						

						update_option('wc_os_settings', sanitize_wc_os_data($wc_os_settings_updated));
						//exit;

						//add_action( 'admin_notices', 'wc_os_admin_notice_success' );

					}
					
					if(isset($_POST['wc_os_general_settings'])){
												
						
						if(array_key_exists('wc_os_settings', $_POST) && array_key_exists('wc_os_additional', $_POST['wc_os_settings'])){
							
							$wc_os_additional_settings = isset($_POST['wc_os_settings']['wc_os_additional']) ? sanitize_wc_os_data($_POST['wc_os_settings']['wc_os_additional']) : '';
							
							$wc_os_settings['wc_os_additional'] = $wc_os_additional_settings;
							
							update_option('wc_os_settings', $wc_os_settings);
						
						}						
	
						$wc_os_general_settings_post = (isset($_POST['wc_os_general_settings']))?sanitize_wc_os_data($_POST['wc_os_general_settings']):($wc_os_additional?array():get_option('wc_os_customer_permission', array()));
						if(array_key_exists('wc_os_auto_forced', $wc_os_general_settings_post)
							&& array_key_exists('wc_os_customer_permission', $wc_os_general_settings_post)){
	
						}else{
	
							//unset($wc_os_general_settings_post['wc_os_customer_permission']);
	
						}
						
						
						$wc_os_optional_settings_array = array(		
							
							'wc_os_cron_shop_order_page',
							'wc_os_cron_my_account_page',
							'wc_os_auto_forced',
							'wc_os_customer_permission',
							'wc_os_auto_clone',
							'wc_os_auto_clone_status',
							'wc_os_order_comments_off',
							'wc_os_billing_off',
							'wc_os_shipping_off',
							'wc_os_reduce_stock',
							'wc_os_order_total',
							'wc_os_shipping_cost',
							'wc_os_customer_notes',
							'wc_os_tax_cost',
							'wc_os_effect_parent',
							'wc_os_threshold',
							'wc_os_display_child',
							'wc_os_display_parent',							
							'wc_os_backorder_mail_notification',
							'wc_os_order_title_splitted',							
							'wc_os_extend_groups',	
							'wc_os_view_order_button',		
							'wc_os_packages_overview',	
							'wc_os_shipping_methods',	
							'wc_os_order_items_count_column',		
							'wc_os_order_splitf_column',
							'wc_os_order_clonef_column',
							'wc_os_remove_fees_from_child',
							'wc_os_remove_price_from_child',
							'wc_os_show_customer_name_list',
							'wc_os_merge_shipping',
							'wc_os_merge_tax',
							'wc_os_remove_combined',
							'wc_os_fa',
							'wc_os_bs',
							'wcfm_order_status',
							'wcfm_shipping_status',
							'wcfm_commission_status',
							'wo_os_rule_switch',
						);
						$wc_os_general_settings_post = array_merge($wc_os_general_settings, $wc_os_general_settings_post);

						
						foreach($wc_os_optional_settings_array as $optional_item){
							
							if(array_key_exists($optional_item, $_POST['wc_os_general_settings'])){
								
								if(array_key_exists('wc_os_auto_forced', $_POST['wc_os_general_settings'])){ //26/01/2021 - AUTO SPLIT AND CUSTOMER PERMISSION WILL NOT WORK TOGETHER
									unset($wc_os_general_settings_post['wc_os_customer_permission']);
								}
								
							}elseif(array_key_exists($optional_item, $wc_os_general_settings_post)){
								unset($wc_os_general_settings_post[$optional_item]);
							}
							
						}
						
	
						update_option( 'wc_os_general_settings', $wc_os_general_settings_post );
						$wc_os_general_settings = get_option('wc_os_general_settings', array());

					}


						wc_os_settings_refresh();

				}






			}




			if(!empty($_POST) && isset($_POST['wos_actions'])){


				if (
					! isset( $_POST['wc_os_cuztomization_field'] )
					|| ! wp_verify_nonce( $_POST['wc_os_cuztomization_field'], 'wc_os_cuztomization' )
				) {

				   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
				   exit;

				} else {


					update_option( 'wc_os_cuztomization', sanitize_wc_os_data($_POST['wos_actions']) );
					update_option( 'wc_os_cart_notices', sanitize_wc_os_data($_POST['wos_cart_notices']) );
					update_option( 'wc_os_packages_strings', sanitize_wc_os_data($_POST['wos_packages_strings']) );

				}

			}


            if(!empty($_POST) && isset($_POST['wc_os_child_email_field'])){


                if (                   
                    ! wp_verify_nonce( $_POST['wc_os_child_email_field'], 'wc_os_child_email' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {


                    $email_option_array = array(

                            'wc_os_order_combine_email',
                            'wc_os_order_split_email',
                            'wc_os_order_created_email',
                            'wc_os_order_created_email_admin',
                            'wc_os_parent_order_email',
							'wc_os_parent_order_email_admin',
							'wc_os_parent_order_email_customer',
							'wc_os_order_created_email_vendor'

                    );
					
					if(array_key_exists('wc_os_status_setting', $_POST)){
						
						$status_setting = get_option('wc_os_status_setting');
						$status_setting_1 = is_array($status_setting)?$status_setting:array();

                        $status_setting_2 = sanitize_wc_os_data($_POST['wc_os_status_setting']);

						$status_setting = array_merge($status_setting_1, $status_setting_2);
                        update_option('wc_os_status_setting', $status_setting);
						//exit;

                    }	

					
					if(isset($_POST['wc_os_email_settings'])){

						$email_genral_settings = isset($_POST['wc_os_email_settings']) ? sanitize_wc_os_data($_POST['wc_os_email_settings']) : array();
						
						
						foreach ($email_option_array as $email_option){
	
							if(!isset($_POST[$email_option])){
								unset($wc_os_general_settings[$email_option]);
							}
							
						}
						
	
						$wc_os_general_settings = array_merge($wc_os_general_settings, $email_genral_settings);
						

	
						update_option( 'wc_os_general_settings', $wc_os_general_settings );
						
					}
					
					if(isset($_POST['wc_os_child_email']) && !empty($_POST['wc_os_child_email'])){
						
						$wc_os_child_email = get_option( 'wc_os_child_email');
						$wc_os_child_email = is_array($wc_os_child_email)?$wc_os_child_email:array();
	
					
						foreach($_POST['wc_os_child_email'] as $key=>$val){
							
							$wc_os_child_email[$key] = sanitize_wc_os_data($val);
							
						}
						if(!array_key_exists('co_total', $_POST['wc_os_child_email'])){
							unset($wc_os_child_email['co_total']);
						}							
		                update_option( 'wc_os_child_email', sanitize_wc_os_data($wc_os_child_email) );
					}


                }

            }


			if(!empty($_POST) && isset($_POST['wc_os_cats'])){

				
				if (
					! isset( $_POST['wc_os_cats_field'] )
					|| ! wp_verify_nonce( $_POST['wc_os_cats_field'], 'wc_os_cats_action' )
				) {

				   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
				   exit;

				} else {
					
					$c_grouped = array();
					$wc_os_settings['wc_os_cats'] = (array_key_exists('wc_os_cats', $wc_os_settings)?$wc_os_settings['wc_os_cats']:array());
					$existing_group_cats_set = (array_key_exists('group_cats', $wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']['group_cats']:array());
					if(!empty($existing_group_cats_set)){
						foreach($existing_group_cats_set as $group_c=>$arr_c){
							if(!empty($arr_c)){
								foreach($arr_c as $item_c){
									if($item_c){
										$c_grouped[$item_c] = $group_c;
									}
								}
							}
						}
					}

					
					$wos_valid_category_actions = isset($_POST['wos_valid_category_actions'])?sanitize_wc_os_data($_POST['wos_valid_category_actions']):'';
					$wos_valid_category_actions = ($wos_valid_category_actions!=''?explode(',', $wos_valid_category_actions):'');
					$wos_valid_category_actions = !empty($wos_valid_category_actions)?array_filter($wos_valid_category_actions):array();


									
					$wc_os_settings['wc_os_ie'] = (isset($_POST['wc_os_cats']['group_cats'])?'group_cats':'cats');

					$updated_split_action = array();
					
					
					switch($wc_os_settings['wc_os_ie']){
						
						case 'group_cats':
							
							$split_action = (array_key_exists($wc_os_settings['wc_os_ie'], $split_action)?$split_action[$wc_os_settings['wc_os_ie']]:array());
							
							if(!empty($split_action)){
								
								foreach($split_action as $sa_name=>$sa_data){
									
									$updated_split_action[$sa_name] = $sa_data;

								}
							}//exit;
							
						break;
						case 'cats':
							if(!empty($split_action)){
								
								$wc_os_cats = sanitize_wc_os_data($_POST['wc_os_cats']['cats']);
								$wc_os_cats = array_filter($wc_os_cats);

								$group_cats = $wc_os_settings['wc_os_cats']['cats'];

								$updated_split_action = is_array($group_cats)?$group_cats:array();

								foreach($wos_valid_category_actions as $valid_cat){
									$old_entry = wc_os_recursive_array_search($valid_cat, $updated_split_action);
									if($old_entry && !in_array($valid_cat, $wc_os_cats)){
										
										
										if (($key = array_search($valid_cat, $updated_split_action[$old_entry])) !== false) {
											unset($updated_split_action[$old_entry][$key]);
										}										
									}
								}
								$updated_split_action = array_filter($updated_split_action);
								
								foreach($split_action as $sa_name=>$sa_data){
								
									foreach($sa_data as $sa_cat=>$sa_item){
										if(!is_array($sa_item) && $sa_item!=''){
								
											$updated_split_action[$sa_item][] = $sa_cat;
											$updated_split_action[$sa_item] = array_unique($updated_split_action[$sa_item]);
										}else{
								
										}
									}
								}
								
							}
						break;
					}
					$split_action_existing = array();

					if(!empty($wc_os_settings['wc_os_cats'])){
						foreach($wc_os_settings['wc_os_cats'] as $action_old=>$data_old){
							if(is_array($data_old) && !empty($data_old)){
								foreach($data_old as $item_old_item){

									$item_old_arr = (is_array($item_old_item)?$item_old_item:array($item_old_item));

									foreach($item_old_arr as $item_old){
										if($item_old!=''){
											$split_action_existing[$item_old] = $action_old;
										}else{

										}
									}
								}
							}
						}

					}
					
					$wc_os_settings_updated = sanitize_wc_os_data($_POST['wc_os_cats']);
					

					
					$wc_os_products_existing = (isset($wc_os_settings['wc_os_cats']) && is_array($wc_os_settings['wc_os_cats']))?$wc_os_settings['wc_os_cats']:array();

					$wc_os_settings_updated['wc_os_cats'] = array_merge($wc_os_products_existing, $wc_os_settings_updated);
					
					
					
					
					
					
					$wc_os_settings_updated['wc_os_cats'] = (!empty($wc_os_settings_updated['wc_os_cats'])?array_filter($wc_os_settings_updated['wc_os_cats'], 'is_array'):array());
					
					$split_ratio = ((isset($wc_os_settings_updated['split_ratio']))?$wc_os_settings_updated['split_ratio']:$wc_os_products_existing['split_ratio']);


					$wc_os_settings_group_cats = (is_array($wc_os_settings_updated['wc_os_cats']) && array_key_exists('group_cats', $wc_os_settings_updated['wc_os_cats']) ?  $wc_os_settings_updated['wc_os_cats']['group_cats'] : array());
					$wc_os_settings_cats = array_key_exists('cats', $wc_os_settings_updated['wc_os_cats'])?$wc_os_settings_updated['wc_os_cats']['cats']:array();
					

					
					
					if(!empty($wc_os_settings_updated['wc_os_cats'])){ //07-05-2019
						$wc_os_settings_refreshed = array();
						foreach($wc_os_settings_updated['wc_os_cats'] as $item_action=>$item_ids){
							//$wc_os_settings_updated['wc_os_products']

							if(!empty($item_ids)){
								foreach($item_ids as $index=>$item_id_item){

									$item_id_arr = (is_array($item_id_item)?$item_id_item:array($item_id_item));

									foreach($item_id_arr as $item_id){

										$item_action_valid = ((is_array($split_action) && isset($split_action[$item_id]))?$split_action[$item_id]:(isset($split_action_existing[$item_id])?$split_action_existing[$item_id]:''));
										//$item_action_valid = (isset($split_action[$item_id])?$split_action[$item_id]:'');//:$split_action_existing[$item_id]);

										$wc_os_settings_refreshed[$item_action][] = $item_id;
										/*
										if($item_action_valid==$item_action){
											$wc_os_settings_refreshed[$item_action][]=$item_id;
										}elseif($item_action && $item_action_valid){

											unset($wc_os_settings_updated['wc_os_cats'][$item_action][$index]);


											//if($split_action[$item_id])
											//$wc_os_settings_updated['wc_os_cats'][$item_action_valid][]=$item_id;
										}
										*/

									}
								}
							}
						}


						$wc_os_settings_updated = wos_array_unique_recursive($wc_os_settings_refreshed);

					}
					$wc_os_settings['wc_os_cats'] = (isset($wc_os_settings['wc_os_cats']) && is_array($wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']:array());
					$existing_group_cats = array_key_exists('group_cats', $wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']['group_cats']:array();
					$existing_cats = array_key_exists('cats', $wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']['cats']:array();
					$wc_os_settings['wc_os_cats'] = $wc_os_settings_updated;
					
					
					
					switch($wc_os_settings['wc_os_ie']){
						case 'group_cats':

							$wc_os_settings['wc_os_cats']['cats'] = $existing_cats;
							$wc_os_settings['wc_os_cats']['group_cats'] = $wc_os_settings_group_cats;

						break;

						case 'cats':
			
							$wc_os_settings['wc_os_cats']['cats'] = $updated_split_action;
							$wc_os_settings['wc_os_cats']['group_cats'] = $wc_os_settings_group_cats;

						break;
					}
					
					if(!empty($c_grouped)){

						$split_action_group_cats = array_key_exists('group_cats', $split_action)?$split_action['group_cats']:array();

						$missing_items = array();
						if(!empty($split_action_group_cats)){
							
							foreach($split_action_group_cats as $item=>$group){
								if(trim($group)=='' && $item){
									$missing_items[] = $item;
								}
							}
						}

						
						foreach($c_grouped as $item_key=>$item_group){
		
							
							if(!in_array($item_key, $wos_valid_category_actions)){
						
								$wc_os_settings['wc_os_cats']['group_cats'][$item_group][] = $item_key;
							}else{
			
							}
							
							//$wc_os_settings['wc_os_cats']['group_cats'][$item_group] = array_unique($wc_os_settings['wc_os_cats']['group_cats'][$item_group]);
						}
						
						
		
						
					}
	
					if(!empty($missing_items) && !empty($wc_os_settings['wc_os_cats']['group_cats'])){

						foreach($missing_items as $del_val){
						
							foreach($wc_os_settings['wc_os_cats']['group_cats'] as $group=>$items){

								$key_exists = array_search($del_val, $wc_os_settings['wc_os_cats']['group_cats'][$group]);

								if($key_exists !== false) {
									unset($wc_os_settings['wc_os_cats']['group_cats'][$group][$key]);
								}	
								//$wc_os_settings['wc_os_cats']['group_cats'][$group] = array_unique($wc_os_settings['wc_os_cats']['group_cats'][$group]);						
							}
							
						}

					}
					
					$wc_os_settings['wc_os_cats']['split_ratio'] = $split_ratio;
					if(function_exists('wc_os_save_group_statuses')){ wc_os_save_group_statuses($wc_os_settings); }
					update_option('wc_os_settings', sanitize_wc_os_data($wc_os_settings));
					//exit;

				}

			}

			if(!empty($_POST) && isset($_POST['wc_os_vendors'])){


				if (
					! isset( $_POST['wc_os_vendors_field'] )
					|| ! wp_verify_nonce( $_POST['wc_os_vendors_field'], 'wc_os_vendors_action' )
				) {

				   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
				   exit;

				} else {
					$wc_os_settings['wc_os_ie'] = (array_key_exists('wc_os_vendors', $_POST['split_action'])?'group_by_vendors':$wc_os_settings['wc_os_ie']);
					$wc_os_settings['wc_os_vendors'] = ($wc_os_settings['wc_os_ie']=='group_by_vendors'?sanitize_wc_os_data($_POST['wc_os_vendors']):(isset($wc_os_settings['wc_os_vendors'])?$wc_os_settings['wc_os_vendors']:array()));
					
					if(function_exists('wc_os_save_group_statuses')){ wc_os_save_group_statuses($wc_os_settings); }
					update_option('wc_os_settings', sanitize_wc_os_data($wc_os_settings));
					
					if(isset($_POST['vendors_remaining'])){
						$vendors_remaining =  ($_POST['vendors_remaining'] !=  '' ? $_POST['vendors_remaining'] : 'group');

						update_option('wc_os_vendors_remaining', $vendors_remaining);					
					}
										

				}
			}
            if(!empty($_POST) && isset($_POST['wc_os_woo_vendors'])){

                if (
                    ! isset( $_POST['wc_os_woo_vendors_field'] )
                    || ! wp_verify_nonce( $_POST['wc_os_woo_vendors_field'], 'wc_os_woo_vendors_action' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {


					//
                    $wc_os_settings['wc_os_ie'] = (array_key_exists('wc_os_woo_vendors', $_POST['split_action'])?'group_by_woo_vendors':$wc_os_settings['wc_os_ie']);
                    $wc_os_settings['wc_os_woo_vendors'] = ($wc_os_settings['wc_os_ie']=='group_by_woo_vendors'?sanitize_wc_os_data($_POST['wc_os_woo_vendors']):(isset($wc_os_settings['wc_os_woo_vendors'])?$wc_os_settings['wc_os_woo_vendors']:array()));
					$wc_os_settings['wc_os_all_vendors'] = array_key_exists('wc_os_all_vendors', $_POST['wc_os_settings'])?sanitize_wc_os_data($_POST['wc_os_settings']['wc_os_all_vendors']):'';
					
 					
					
					if(function_exists('wc_os_save_group_statuses')){ wc_os_save_group_statuses($wc_os_settings); }
                    update_option('wc_os_settings', sanitize_wc_os_data($wc_os_settings));

                }
            }			
			
			if(!empty($_POST) && (array_key_exists('wc_os_attributes', $_POST) || array_key_exists('wc_os_metadata', $_POST))){


				if (
					! isset( $_POST['wc_os_settings_field'] )
					|| ! wp_verify_nonce( $_POST['wc_os_settings_field'], 'wc_os_settings_action' )
				) {

				   _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
				   exit;

				} else {

					
					$_POST['split_action'] = isset($_POST['split_action'])?$_POST['split_action']:array();
					$wc_os_settings['wc_os_ie'] = (array_key_exists('wc_os_attributes', $_POST['split_action']) && array_key_exists('wc_os_attributes_group', $_POST['split_action'])?'group_by_attributes_only':$wc_os_settings['wc_os_ie']);
					$wc_os_settings['wc_os_ie'] = (array_key_exists('wc_os_attributes', $_POST['split_action']) && !array_key_exists('wc_os_attributes_group', $_POST['split_action'])?'group_by_attributes_value':$wc_os_settings['wc_os_ie']);

					
					
					$wc_os_metadata_updated = (in_array($wc_os_settings['wc_os_ie'], array('groups_by_meta'))?sanitize_wc_os_data($_POST['wc_os_metadata']):(isset($wc_os_settings['wc_os_metadata'])?$wc_os_settings['wc_os_metadata']:array()));
					$wc_os_metadata_updated = array_unique($wc_os_metadata_updated);
					
					$wc_os_attributes_updated = (in_array($wc_os_settings['wc_os_ie'], array('group_by_attributes_only'))?sanitize_wc_os_data($_POST['wc_os_attributes']):(isset($wc_os_settings['wc_os_attributes'])?$wc_os_settings['wc_os_attributes']:array()));
					$wc_os_attributes_updated = array_unique($wc_os_attributes_updated);
					
					$wc_os_attributes_values_updated = (in_array($wc_os_settings['wc_os_ie'], array('group_by_attributes_value'))?sanitize_wc_os_data($_POST['wc_os_attributes_values']):(isset($wc_os_settings['wc_os_attributes_values'])?$wc_os_settings['wc_os_attributes_values']:array()));					
					$wc_os_attributes_values_updated = array_unique($wc_os_attributes_values_updated);
					
					$wc_os_attributes_nodes_updated = (in_array($wc_os_settings['wc_os_ie'], array('group_by_attributes_value'))?sanitize_wc_os_data($_POST['wc_os_attributes_nodes']):(isset($wc_os_settings['wc_os_attributes_nodes'])?$wc_os_settings['wc_os_attributes_nodes']:array()));	
					$wc_os_attributes_nodes_updated = array_unique($wc_os_attributes_nodes_updated);

				  	$wos_valid_metadata_actions = array_key_exists('wos_valid_metadata_actions', $_POST)?sanitize_wc_os_data($_POST['wos_valid_metadata_actions']):'';
					$wos_valid_metadata_actions = ($wos_valid_metadata_actions!=''?explode(',', $wos_valid_metadata_actions):array());
					$wos_valid_metadata_actions = !empty($wos_valid_metadata_actions)?array_filter($wos_valid_metadata_actions):array();				
					
					
			   		$wos_valid_attrib_actions = array_key_exists('wos_valid_attrib_actions', $_POST)?sanitize_wc_os_data($_POST['wos_valid_attrib_actions']):'';
					$wos_valid_attrib_actions = ($wos_valid_attrib_actions!=''?explode(',', $wos_valid_attrib_actions):array());
					$wos_valid_attrib_actions = !empty($wos_valid_attrib_actions)?array_filter($wos_valid_attrib_actions):array();
					
					
			   		$wos_valid_order_item_meta = array_key_exists('wos_valid_order_item_meta', $_POST)?sanitize_wc_os_data($_POST['wos_valid_order_item_meta']):'';
					$wos_valid_order_item_meta = ($wos_valid_order_item_meta!=''?explode(',', $wos_valid_order_item_meta):array());
					$wos_valid_order_item_meta = !empty($wos_valid_order_item_meta)?array_filter($wos_valid_order_item_meta):array();					
					
					
					if($wc_os_settings['wc_os_ie']=='groups_by_meta'){
						
						$wc_os_settings['wc_os_metadata'] = array();
						
						foreach($wos_valid_metadata_actions as $valid_meta){
							if(in_array($valid_meta, $wc_os_metadata_updated) && !in_array($valid_meta, $wc_os_settings['wc_os_metadata'])){
								$wc_os_settings['wc_os_metadata'][] = $valid_meta;
							}elseif(!in_array($valid_meta, $wc_os_metadata_updated) && in_array($valid_meta, $wc_os_settings['wc_os_metadata'])){
								
								if (($meta_key = array_search($valid_meta, $wc_os_settings['wc_os_metadata'])) !== false) {
									unset($wc_os_settings['wc_os_metadata'][$meta_key]);
								}								
							}
						}
						
						
					}
					
					if(!empty($wos_valid_attrib_actions) && $wc_os_settings['wc_os_ie']=='group_by_attributes_value'){
						
						$wc_os_settings['wc_os_attributes_values'] = array();
						
						$wc_os_settings['wc_os_attributes_nodes'] = $wc_os_attributes_nodes_updated;
						
						foreach($wos_valid_attrib_actions as $valid_attrib){
							if(in_array($valid_attrib, $wc_os_attributes_values_updated) && !in_array($valid_attrib, $wc_os_settings['wc_os_attributes_values'])){
								$wc_os_settings['wc_os_attributes_values'][] = $valid_attrib;
							}elseif(!in_array($valid_attrib, $wc_os_attributes_values_updated) && in_array($valid_attrib, $wc_os_settings['wc_os_attributes_values'])){
								
								if (($attrib_key = array_search($valid_attrib, $wc_os_settings['wc_os_attributes_values'])) !== false) {
									unset($wc_os_settings['wc_os_attributes_values'][$attrib_key]);
								}								
							}
						}
						
						
					}
					
	
					if(!empty($wos_valid_attrib_actions) && $wc_os_settings['wc_os_ie']=='group_by_attributes_only'){
						foreach($wos_valid_attrib_actions as $valid_attrib){
							if(in_array($valid_attrib, $wc_os_attributes_updated) && !in_array($valid_attrib, $wc_os_settings['wc_os_attributes'])){
								$wc_os_settings['wc_os_attributes'][] = $valid_attrib;
							}elseif(!in_array($valid_attrib, $wc_os_attributes_updated) && in_array($valid_attrib, $wc_os_settings['wc_os_attributes'])){
								
								if (($attrib_key = array_search($valid_attrib, $wc_os_settings['wc_os_attributes'])) !== false) {
									unset($wc_os_settings['wc_os_attributes'][$attrib_key]);
								}								
							}
						}
					}
										
					
					
					
					$wc_os_settings['wc_os_metadata_group'] = ($wc_os_settings['wc_os_ie']=='groups_by_meta' && array_key_exists('wc_os_metadata_group', $_POST)?sanitize_wc_os_data($_POST['wc_os_metadata_group']):(isset($wc_os_settings['wc_os_metadata_group'])?$wc_os_settings['wc_os_metadata_group']:array()));
					
					$wc_os_settings['wc_os_attributes_group'] = ($wc_os_settings['wc_os_ie']=='group_by_attributes_only'?sanitize_wc_os_data($_POST['wc_os_attributes_group']):(isset($wc_os_settings['wc_os_attributes_group'])?$wc_os_settings['wc_os_attributes_group']:array()));
					
					$wc_os_settings['wc_os_order_item_meta_group'] = ($wc_os_settings['wc_os_ie']=='group_by_order_item_meta' && array_key_exists('wc_os_order_item_meta_group', $_POST)?sanitize_wc_os_data($_POST['wc_os_order_item_meta_group']):(isset($wc_os_settings['wc_os_order_item_meta_group'])?$wc_os_settings['wc_os_order_item_meta_group']:array()));
					
					
					//$wos_valid_attrib_actions
					
					$wc_os_settings['wc_os_order_item_meta'] = (array_key_exists('wc_os_order_item_meta', $wc_os_settings)?$wc_os_settings['wc_os_order_item_meta']:array());
					
					if(!empty($wos_valid_order_item_meta) && $wc_os_settings['wc_os_ie']=='group_by_order_item_meta'){						
						
						$wc_os_settings['wc_os_order_item_meta'] = (array_key_exists('wc_os_order_item_meta', $_POST)?sanitize_wc_os_data($_POST['wc_os_order_item_meta']):$wc_os_settings['wc_os_order_item_meta']);

					}

					if(function_exists('wc_os_save_group_statuses')){wc_os_save_group_statuses($wc_os_settings);}
					
					update_option('wc_os_settings', sanitize_wc_os_data($wc_os_settings));
			
					
					

				}
			}

            if(!empty($_POST) && isset($_POST['wc_os_acf_fields'])){

                if (
                    ! isset( $_POST['wc_os_acf_fields_nonce'] )
                    || ! wp_verify_nonce( $_POST['wc_os_acf_fields_nonce'], 'wc_os_acf_fields_action' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {

                    $wc_os_acf_settings = sanitize_wc_os_data($_POST['wc_os_acf_fields']);
                    update_option('wc_os_acf_settings', $wc_os_acf_settings);

                }
            }
			
			if(!empty($_POST) && isset($_POST['wc_os_partial_payment'])){

                if (
                    ! isset( $_POST['wc_os_partial_payment_nonce'] )
                    || ! wp_verify_nonce( $_POST['wc_os_partial_payment_nonce'], 'wc_os_partial_payment_action' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {

                    $wc_os_partial_payment = sanitize_wc_os_data($_POST['wc_os_partial_payment']);
                    update_option('wc_os_partial_payment', $wc_os_partial_payment);

                }
            }
			
			
			
			
			

		}
	}
	
	
	

	function wc_os_admin_menu()
	{
		global $wc_os_data;
		
		$title = str_replace('WooCommerce', 'WC', $wc_os_data['Name']);
		add_submenu_page('woocommerce', $title, $title, 'manage_woocommerce', 'wc_os_settings', 'wc_os_settings' );



	}

	function wc_os_settings(){ 



		if ( !current_user_can( 'administrator' ) )  {



			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-order-splitter' ) );



		}



		global $wpdb; 

		

			
		include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/wc_settings.php'));	

		

	}
	
	
	function wc_os_plugin_linx($links) { 

		global $wc_os_premium_copy, $wc_os_pro;


		$settings_link = '<a href="admin.php?page=wc_os_settings">'.__('Settings', 'woo-order-splitter').'</a>';

		
		if($wc_os_pro){
			array_unshift($links, $settings_link); 
		}else{
			 
			$wc_os_premium_link = '<a href="'.esc_url($wc_os_premium_copy).'" title="'.__('Go Premium', 'woo-order-splitter').'" target="_blank">'.__('Go Premium', 'woo-order-splitter').'</a>'; 
			array_unshift($links, $settings_link, $wc_os_premium_link); 
		
		}
				
		
		return $links; 
	}
	
	function wc_os_get_products($per_page = 0, $offset = 0){
		$results = array();
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => $per_page?$per_page:-1,
			'orderby'        => 'title',
			'order'          => 'ASC',

			);
		if($offset){
			$args['paged'] = $offset;
		}

		$results = get_posts( $args );		
	
		return $results;
	}		
	
	function wc_os_get_product_categories($offset=0, $wc_os_per_page=0){
	
		$cat_args = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		
		
		if($offset){
			$cat_args['offset'] = (($offset-1)*$wc_os_per_page); 
		}
		if($wc_os_per_page){
			$cat_args['number'] = $wc_os_per_page;
			
		}
		
		$product_categories = get_terms( 'product_cat', $cat_args );		
		
		return $product_categories;
	}		
		
	function wc_os_settings_refresh(){
		global $wc_os_settings;
		$wc_os_settings = get_option('wc_os_settings', array());		
		$wc_os_settings = (is_array($wc_os_settings)?$wc_os_settings:array());
		

		$wc_os_settings['wc_os_products'] = (isset($wc_os_settings['wc_os_products']) && is_array($wc_os_settings['wc_os_products']))?$wc_os_settings['wc_os_products']:array();
		$wc_os_settings['wc_os_additional'] = (isset($wc_os_settings['wc_os_additional']) && is_array($wc_os_settings['wc_os_additional']))?$wc_os_settings['wc_os_additional']:array();		
		$wc_os_settings['wc_os_ie'] =(isset($wc_os_settings['wc_os_ie']) && $wc_os_settings['wc_os_ie']!=''?$wc_os_settings['wc_os_ie']:'default');
		

		wc_os_update_cron_status_hooks();
	}
	
	
	
	
	if(!function_exists('wc_os_crons_wos_calculate_totals')){
		function wc_os_crons_wos_calculate_totals($order_id=0){
			
			global $wc_os_custom_orders_table_enabled, $wpdb;
			
			if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders')){
				
				
				if($order_id){
				
					$order_data = wc_get_order($order_id);
					
					if(is_object($order_data)){
						//pree('I #'.$order_id.' / '.(is_object($order_data)?'Object':'None'));exit;
						$valid_order_status = strpos($order_data->get_status(), 'drew');
						$valid_order_status = ($valid_order_status!='' && $valid_order_status>=0);
						
						if($valid_order_status){
							
							
							
							
							//pree('J #'.$order_id.' / '.(is_object($order_data)?'Object':'None'));exit;
							
							$order_data->calculate_taxes();
							$order_data->calculate_totals();
							
							
							
							wc_os_delete_order_meta($order_data, '_wos_calculate_totals');
							wc_os_update_order_meta($order_data, '__wos_calculate_totals_for_tax', true);
							
						}
						
					}
					
				}
				
			}else{
			
				$wc_os_get_post_type_default = wc_os_get_post_type_default();
				
				$cron_query = "SELECT p.ID FROM $wpdb->postmeta mt RIGHT JOIN $wpdb->posts p ON p.ID=mt.post_id AND p.post_type='$wc_os_get_post_type_default' WHERE p.post_status NOT IN ('trash') AND (mt.meta_key='_wos_calculate_totals')";
				
				if($order_id){ $cron_query = "SELECT p.ID FROM $wpdb->posts p WHERE p.post_status NOT IN ('trash') AND p.ID IN (".esc_sql($order_id).") AND p.post_type='$wc_os_get_post_type_default'"; }
	
				$wc_os_order_key_cron = $wpdb->get_results($cron_query);
				//pree($wc_os_order_key_cron);exit;
				if(!empty($wc_os_order_key_cron)){
					//pree($wc_os_order_key_cron);exit;
					foreach($wc_os_order_key_cron as $all_crons_items){			
					
						//pree($all_crons_items);exit;
						
						$order_data = new WC_Order( $all_crons_items->ID );
						
						if(is_object($order_data) && !empty($order_data)){
							
							
							$order_data->calculate_taxes();
							$order_data->calculate_totals();
							
							wc_os_delete_order_meta($order_data, '_wos_calculate_totals');
							wc_os_update_order_meta($order_data, '__wos_calculate_totals_for_tax', true);
							
						}
					}
					//exit;
				}
				
			}
		}
	}
	
	function wos_array_unique_recursive($array)
	{
		$array = array_unique($array, SORT_REGULAR);
	
		foreach ($array as $key => $elem) {
			if (is_array($elem)) {
				$array[$key] = wos_array_unique_recursive($elem);
			}
		}
	
		return $array;
	}	

	
	
	
	
	
	function wc_os_init(){
		
		
		
		global $wc_os_currency, $wc_os_settings, $wc_os_activated;
		
		
		
		if(!$wc_os_activated)
		return;
		
		
		$wc_os_currency = wc_os_currency_symbol();
		wc_os_settings_refresh();
		
		
		
		
	}
	
	
	
	if(!function_exists('get_order_title_status')){
		function get_splitted_order_title_status(){
			global $wc_os_general_settings;
			
			return array_key_exists('wc_os_order_title_splitted', $wc_os_general_settings);
		}
	}	
	
	function wc_os_get_order_item_meta_key_val($order){
		
		$meta_vals = array();
		
		if(!is_object($order)){
			if(is_numeric($order)){
				$order = wc_get_order($order);
			}
		}
		
		
		
		if(is_object($order) && !empty($order->get_items())){
			foreach($order->get_items() as $order_key => $values){
				$item_meta_data = wc_get_order_item_meta($order_key, '');
				if(!empty($item_meta_data)){
					foreach($item_meta_data as $meta_key=>$meta_data){
						$meta_vals[$meta_key] = current($meta_data);
					}
				}
			}
		}		
		
		return $meta_vals;
	}
	
	
	function wc_os_co_label($order_id=0, $order = array()){
		
		$ret_str = __('Order number', 'woocommerce').' ';
		$ret = $order_id;

		$wc_os_child_email = get_option( 'wc_os_child_email', array());
		
		if(array_key_exists('co_number', $wc_os_child_email) && $wc_os_child_email['co_number']){
			
			$template_string = $wc_os_child_email['co_number'];

			
			$ret = str_replace(array('ORDER_ID'), $order_id, $template_string);
			
			$meta_vals = wc_os_get_order_item_meta_key_val($order_id);
			
			foreach($meta_vals as $key=>$val){
	
				if(stristr($template_string, '[taxonomy:') && stristr($template_string, ',term:')){
					
					$for_tax = explode('[taxonomy:', $template_string);					
					$for_tax = explode(',', end($for_tax));
					$tax = current($for_tax);

					
					$for_term = explode('term:', end($for_tax));	
					$for_term = explode(']', end($for_term));
					$term = current($for_term);
					

					if($tax && $term && $term==$key){
		
						$term_data = get_term_by('id', $val, $tax);
						
						if(is_object($term_data)){
							$placeholder = '[taxonomy:'.$tax.',term:'.$term.']';
				
							$key = $placeholder;
							$val = '<span class="wc-os-'.$term_data->slug.'">'.$term_data->name.'</span>';
						}
					}					
				}else{
					$val = '<span class="wc-os-'.$key.'">'.$val.'</span>';
				}
				$ret = str_replace($key, $val, $ret);
				
				
				
			}

			
		}else{
			
			
			
		}
		$ret = $ret_str.apply_filters('wc_os_masked_child_order_number', $ret, $order->get_id(), $order);

		return $ret;
	}
			

	
	function __wos_change_order_received_text($order_id=0, $parent_order=array(), $deleted=true){
		ob_start();
		
		global $wpdb, $wc_os_general_settings, $wc_os_tax_cost, $wc_os_shipping, $wc_os_shipping_cost;
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();

		$child_orders = 0;

		$parent_order = (empty($parent_order)?wc_get_order($order_id):$parent_order);

		$wc_os_vendor_name = array_key_exists('wc_os_vendor_name', $wc_os_general_settings);
		
		$cart_total = 0;
		if(!empty($parent_order) && array_key_exists('wc_os_order_total', $wc_os_general_settings)){
			$cart_total = $parent_order->get_total();
		}
		
		
		$wos_cart_notices = get_option( 'wc_os_cart_notices', true);
		
		
		$co_total = (isset($wos_cart_notices['co_total'])?$wos_cart_notices['co_total']:'');
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'cat'         => '',
			'category_name'    => '',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => 'splitted_from',
			'meta_value'       => $order_id,
			'post_type'        => array($wc_os_get_post_type_default),
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => 'any',
			'suppress_filters' => true,
			'fields'           => '',
		);
		$posts_array = get_posts( $args );
		$child_orders = count($posts_array);

		if(!empty($posts_array)){
			
			$first_order = current($posts_array);
			$order_total = 0;
			foreach($posts_array as $post_data){
				$this_order = wc_get_order($post_data->ID);
				$_wos_keep_shipping = wc_os_get_order_meta($post_data->ID, '_wos_keep_shipping', true);
				$_wos_remove_shipping = wc_os_get_order_meta($post_data->ID, '_wos_remove_shipping', true);
		
				$total_amount = $this_order->get_total();
				$order_total += $total_amount;
				
				if(!$wc_os_shipping_cost){
					$child_order_shipping_items = $this_order->get_items('shipping');

					if(!empty($child_order_shipping_items)){
						foreach($child_order_shipping_items as $item_id=>$item_data){
	
							if(!$_wos_keep_shipping || ($_wos_keep_shipping && $item_id!=$_wos_keep_shipping)){
								
								if(class_exists('WC_OS_Shipping')){
									if($wc_os_shipping->get_selection() == 'no'){
										
										wc_delete_order_item($item_id);									
									}
								}else{
									
									wc_delete_order_item($item_id);
								}
							}
						}
						
					}
				}
				$this_order->calculate_totals();
				
				$child_order = wc_get_order($post_data->ID);
				$cart_total += $child_order->get_total();
				
				
				if($wc_os_shipping_cost){
					
					$_wc_os_set_status = wc_os_get_order_meta($child_order, '_wc_os_set_status', true);
					$_order_shipping = wc_os_get_order_meta($child_order, '_order_shipping', true);
					
					
					
					if($_wc_os_set_status){
						$child_order->set_status(wc_os_add_prefix($_wc_os_set_status, 'wc-'));
						$child_order->save();
						
						wc_os_delete_order_meta($child_order, '_wc_os_set_status');
					}
				}
			
			}
			
			
			$parent_items = array(); 
			$bright_items = array(); 
			if(count($parent_order->get_items())>0){
				foreach($parent_order->get_items() as $parent_arr){
					$product_id = $parent_arr->get_product_id();
					$parent_items[] = $product_id;
					
					$meta = get_post_meta($product_id, 'wos_force_display', true);
					if(!empty($meta)){
						$bright_items[] = $product_id;
					}
				}
			}
				
	?>
    
    <?php if(count($parent_order->get_items())==0 || (count($posts_array)>0 && array_key_exists('wc_os_display_parent', $wc_os_general_settings))): 
			
	?>
    <style type="text/css">
		.woocommerce-order-details:not(.child-section)<?php echo !empty($bright_items)?':not(.product-'.implode('):not(product-', $bright_items).')':''; ?>,
		ul.woocommerce-thankyou-order-details.order_details,
		.woocommerce-order-overview__order.order<?php echo !empty($bright_items)?':not(.product-'.implode('):not(product-', $bright_items).')':''; ?>{
			display:none !important;
		}
	</style>
    <?php endif; ?>    
	
    <?php if($deleted): ?>
	
	<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
	
        <li class="woocommerce-order-overview__order order">
        <?php _e('Order number', 'woo-order-splitter'); ?>: <strong><?php echo esc_html($order_id); ?></strong>
        </li>
        
        <li class="woocommerce-order-overview__date date">
        <?php _e('Date', 'woo-order-splitter'); ?>: <strong><?php echo date('F d, Y', strtotime($first_order->post_date)); ?></strong>
        </li>
        
        
        <li class="woocommerce-order-overview__total total">
        <?php _e('Total', 'woo-order-splitter'); ?>: <?php echo wp_kses_post(wc_price($cart_total)); ?>
        </li>
	
	</ul>
    <?php else: ?>

    <?php endif; ?>
    <div style="display:none">
    <script type="text/javascript" language="javascript">
		jQuery(document).ready(function($){
			$('ul.woocommerce-thankyou-order-details.order_details li.total strong').html('<?php echo wp_kses_post(wc_price($cart_total)); ?>');
			
<?php 
			if(!empty($parent_items)){
?>
			
			$('table.shop_table.order_details, .woocommerce-order-details').addClass('product-<?php echo wp_kses_post(implode(' product-', $parent_items)); ?>');
<?php 
			}
?>
		});
	</script>
    </div>
    <?php if(count($posts_array)>0 && !array_key_exists('wc_os_display_child', $wc_os_general_settings)): ?>
	<section class="woocommerce-order-details child-section">
	
	<h2 class="woocommerce-order-details__title"><?php echo apply_filters('wc_os_child_order_heading', isset($wos_cart_notices['co_heading'])?$wos_cart_notices['co_heading']:__('Child', 'woo-order-splitter').' '.(count($posts_array)>1?__('Orders', 'woocommerce'):__('Order', 'woocommerce')), count($posts_array)); ?></h2>
	
	
	<?php 
	$cart_total += $order_total;
	$child_counter = 1;

	$vendors = array();	
	foreach($posts_array as $post_data){ $child_order = wc_get_order($post_data->ID); 
	
		if(count($child_order->get_items())==0){ continue; }
	
		$child_order->calculate_totals();
		
		$_payment_method = $child_order->get_payment_method_title();
		$child_order_data = $child_order->get_data();	
	
		
		$_order_shipping_tax = (wc_os_get_order_meta($post_data->ID, '_order_shipping_tax', true));
		$_order_shipping_tax = ($_order_shipping_tax?$_order_shipping_tax:0);

		$deposit_value = (wc_os_get_order_meta($post_data->ID, 'deposit_value', true));
		$deposit_value = ($deposit_value?$deposit_value:0);
		
		$due_payment = (wc_os_get_order_meta($post_data->ID, 'due_payment', true));
		$due_payment = ($due_payment?$due_payment:0);
		


		$tax_string = '';
		if(wc_tax_enabled() && !is_null(WC()->cart) && WC()->cart->display_prices_including_tax()){
			$tax_string_array = array();
			$tax_string_array[] = wc_price($child_order_data['total_tax']);

			$tax_string = ' <small class="includes_tax">(' .__( 'includes', 'woo-order-splitter' ).' '. sprintf( '%s', implode( ', ', $tax_string_array ) .' '. WC()->countries->tax_or_vat() ). '</small>';

			$child_order_data['total_tax'] -= $_order_shipping_tax;
			$child_order_data['shipping_total'] += $_order_shipping_tax;

		}
			
					
		
		$child_id = $child_order->get_order_number();
		
		


		if(get_splitted_order_title_status()){

            $child_id = ($order_id. ' - ' .count($posts_array). ' - ' .$child_counter);

            $child_counter++;

        }		
		
	?>
	
	<h3 class="child_order_heading"><?php echo wc_os_co_label($child_id, $child_order); ?></h3>
    <?php do_action('wc_os_below_child_order_number', $child_id, $child_order); ?>
	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
	
	<thead>
	<tr>
	<th class="woocommerce-table__product-name product-name"><?php _e('Product', 'woo-order-splitter'); ?></th>
	<th class="woocommerce-table__product-table product-total"><?php _e('Total', 'woo-order-splitter'); ?></th>
	</tr>
	</thead>
	
	<tbody>
    
	
	<?php 
	
		foreach($child_order->get_items() as $item_key=>$order_items){ 
			$meta_data = array();
			$vendor_product_id = $order_items->get_product_id();
			if($wc_os_vendor_name){
					if(!array_key_exists($vendor_product_id, $vendors)){
						$post_vendor = get_post($vendor_product_id);
						$vendors[$vendor_product_id] = $post_vendor->post_author;
					}
					if(array_key_exists($vendor_product_id, $vendors)){
						
						$vendor_data = get_user_by( 'ID', $vendors[$vendor_product_id] );
						
						$full_name = '';
						
						if(is_object($vendor_data) && !empty($vendor_data)){
							$full_name = trim($vendor_data->first_name.' '.$vendor_data->last_name);		
							$full_name = ($full_name?$full_name:$vendor_data->display_name);				
						}
						
						$meta_data[] = __('Vendor', 'woo-order-splitter').': '.$full_name;
						
					}					
			}	
			$line_total = ($order_items->get_total());
			
			$child_order_total = ($deposit_value?$deposit_value:$child_order->get_total());
			
			if($child_order_data['shipping_total']>0)
			$child_order_total += $child_order_data['shipping_total'];
			
			
	
	?>    
	<tr class="woocommerce-table__line-item order_item">
	
	<td class="woocommerce-table__product-name product-name">
	<a href="<?php echo esc_attr(get_permalink($order_items->get_product_id())); ?>"><?php echo esc_html($order_items['name']); ?></a> <strong class="product-quantity">× <?php echo esc_html($order_items->get_quantity()); ?></strong>
    <div class="wos-item-meta"><?php echo wp_kses_post(implode(', ', $meta_data)); ?></div>
    </td>
	
	<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post(wc_price($line_total));  ?></td>
	
	</tr>
	<?php } ?>
    

	<?php if($child_order_data['total_tax']>0): ?>
    <tr>
    <td class="woocommerce-table__product-name product-name"><?php _e('Taxes', 'woo-order-splitter'); ?>:</td>	
	<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post(wc_price($child_order_data['total_tax'])); ?></td>	
	</tr>
    <?php endif; ?> 
    
    
	<?php if( $deposit_value): ?>
    <tr>
    <td class="woocommerce-table__product-name product-name"><?php _e('Deposit', 'woo-order-splitter'); ?>:</td>	
	<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post(wc_price( $deposit_value)); ?></td>	
	</tr>
    <?php endif; ?>  
        
	<?php if($child_order_data['shipping_total']): ?>
    <tr>
    <td class="woocommerce-table__product-name product-name"><?php _e('Shipment', 'woo-order-splitter'); ?>:</td>	
	<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post(wc_price($child_order_data['shipping_total'])); ?></td>	
	</tr>
    <?php endif; ?>   

	<?php if($_payment_method): ?>
    <tr>
    <td class="woocommerce-table__product-name product-name"><?php _e('Payment method', 'woo-order-splitter'); ?>:	</td>	
	<td class="woocommerce-table__product-total product-total"><?php echo esc_html($_payment_method); ?></td>	
	</tr>
    <?php endif; ?>
	
	</tbody>
	
	<tfoot>
					
		
				   <tr>
			<th scope="row"><?php _e('Total', 'woo-order-splitter'); ?>:</th>
			<td><?php echo wp_kses_post(wc_price($child_order_total).' '.$tax_string); ?></td>
		</tr>
        
   
	<?php if( $due_payment): ?>
    <tr>
    <th scope="row"><?php _e('Due Payment', 'woo-order-splitter'); ?>:</th>	
	<td><?php echo wp_kses_post(wc_price( $due_payment)); ?></td>	
	</tr>
    <?php endif; ?>  
            
							</tfoot>
	</table>
	<?php 
	}
	?>

    
    <?php if($co_total): ?>
    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
	
	<thead>
	<tr>
	<th class="woocommerce-table__product-name product-name"><?php _e('Order Total', 'woo-order-splitter'); ?></th>
	<th class="woocommerce-table__product-table product-total"><?php _e('Amount', 'woo-order-splitter'); ?></th>
	</tr>
	</thead>
	
	<tbody>
	
	
	<tr class="woocommerce-table__line-item order_item">
	
	<td class="woocommerce-table__product-name product-name"><?php _e('Total amount charged to billing method', 'woo-order-splitter'); ?></td>
	
	<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post(wc_price($cart_total)); ?></td>
	
	</tr>
	
	
	</tbody>
	
	
	</table>
    <?php endif;?>
	</section>
    <?php endif; ?>
    
	<?php					
	
			
		}	
		
		
		$str = ob_get_contents();
		ob_end_clean();	
		
		return array('child'=>$child_orders, 'content'=>$str, 'total'=>($deleted?$child_orders:($child_orders+1)));
	}
	
	add_filter( 'woocommerce_order_number', 'wos_change_woocommerce_order_number' );
	
	function wos_change_woocommerce_order_number( $order_id ) {
		
		global $wc_os_pro;
		if($wc_os_pro && get_splitted_order_title_status() && function_exists('wc_os_get_order_title_html')){
			
			$order_id = wc_os_get_order_title_html($order_id, 'return');
	
		}
		
		return $order_id;
		
	}
	
	add_filter('woocommerce_thankyou_order_received_text', 'wos_change_order_received_text', 10, 2 );
	
	function wos_change_order_received_text( $str='', $order=array() ) {
		
		if($str){ return $str; }
		
		
		$order_id = 0;
		$qs = $_SERVER['REQUEST_URI'];
	
		
		if(is_object($order) && !empty($order)){
			$order_id = $order->get_id();
		}else{
		
			
			$qs = explode('/', $qs);
			$qs = array_filter($qs, 'strlen');
	
			$last_uri = end($qs);
			if(is_numeric($last_uri)){
				$order_id = $last_uri;
			}else{
				$last_uri = explode('?', $last_uri);				
				$last_uri = current($last_uri);
				
				if(!is_numeric($last_uri)){
					array_pop($qs);
					$last_uri = end($qs);
				}
				
				if(is_numeric($last_uri)){
					$order_id = (int) filter_var($last_uri, FILTER_SANITIZE_NUMBER_INT); 
				}
				
			}
			
		}
		
		//wc_os_pree('$order_id: '.$order_id);
		
		if(is_numeric($order_id)){
			
			$deleted = false;	
			if(empty($order)){			
				$order = wc_get_order($order_id);	
				$deleted = true;
			}
				
			if(!empty($order)){	
				$received_text = __wos_change_order_received_text($order_id, $order, $deleted);	
				global $wc_os_general_settings;
				$wc_os_display_child_number = (array_key_exists('wc_os_display_child_number', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_display_child_number'] && $wc_os_general_settings['wc_os_display_child_number']>0)?str_replace('[NUMBER_OF_CHILD_ORDERS]', $received_text['total'], $wc_os_general_settings['wc_os_display_child_number']).'<br /><br />':'');
				
				$str = $wc_os_display_child_number.$str.$received_text['content'];
			}				
		
		}
		
		return $str;
	}
	
	add_action('woocommerce_thankyou', 'wc_os_checkout_order_processed', 10, 1);
	
	$wc_os_order_status_hooks = WC_OS_PLUGIN_DIR . '/inc/sections/wc_os_order_status_hooks.php';
	
	
	if(file_exists($wc_os_order_status_hooks)){
		include_once(realpath($wc_os_order_status_hooks));
	}


	
	function wc_os_checkout_order_processed($order_id){
		
		
		
		if ( ! $order_id )
        return;
		
		$order_data = wc_get_order( $order_id );
		

		
		global $wc_os_auto_forced;
		
		if(!$wc_os_auto_forced){ return; }
		
		//wc_os_pree('$order_id: '.$order_id);
		//update_post_meta($order_id, 'order_splitting', date('H:i:s A'));
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		
		$get_post_meta = wc_os_get_order_meta($order_id);
		
		$split_status = (array_key_exists('split_status', $get_post_meta) || array_key_exists('_wc_os_parent_order', $get_post_meta));
		
		$customer_permission_status = wc_os_get_customer_permission_status($order_id);
		


		do_action('wc_os_checkout_order_processed_before', $order_id, $split_status);
		
		if($order_id && $split_status && !is_admin() && is_checkout() && $customer_permission_status){
				
		}
		
		if(!$split_status){
			
			if(!$customer_permission_status){
				return;
			}
		}
		

		if(
				is_admin() 
			&& 
			(
					((isset($_GET['clone']) && $_GET['clone'] == 'yes') || (isset($_REQUEST['post_type']) && $_REQUEST['post_type']==$wc_os_get_post_type_default && $_REQUEST['action']=='clone'))
				||
					((isset($_GET['post_type']) && $_GET['post_type']==$wc_os_get_post_type_default && (isset($_GET['action']) && $_GET['action']=='split')))
			)
		){
			return;
		}
		
		
		$post = get_post($order_id);
		
		$wos_get_valid_status = wos_get_valid_status($post, $order_id);
		extract($wos_get_valid_status);		

		if(!$valid_post_type){
			return;			
		}
		
		order_details_page_saved( $order_id, $post );
		
		global $wc_os_settings, $wc_os_general_settings;
		
		
		$wc_os_shipping_methods = array_key_exists('wc_os_shipping_methods', $wc_os_general_settings);	
		
		if($wc_os_shipping_methods && wc_os_get_session( 'wc_os_customer_permitted')=='on'){
			wc_os_update_order_meta($order_id, '_wc_os_shipping_cost', wc_os_get_session('wc_os_calculated_shipping_cost'));
			wc_os_update_order_meta($order_id, '_wc_os_parcels_array', wc_os_get_session('wc_os_parcels_array'));

		}

		$split_lock = (isset($wc_os_settings['wc_os_additional']['split_lock'])?$wc_os_settings['wc_os_additional']['split_lock']:array());
		$split_lock = is_array($split_lock)?$split_lock:array();
		
		$status_lock_released = empty($split_lock);
		
		if(!empty($split_lock)){
			foreach($split_lock as $split_lock_i){
				if(!$status_lock_released){
					$status_lock_released = $order_data->has_status($split_lock_i);
				}
			}
		}		


		
		$wc_os_general_settings = get_option('wc_os_general_settings', array());

		if(function_exists('wc_os_order_remove_shipping_adjustment')){
			wc_os_order_remove_shipping_adjustment($order_id);//exit;
		}		
		
		if(!empty($order_data) && $status_lock_released){
		

			
			wc_os_crons_light($order_id);
			
			$wc_os_auto_forced = (array_key_exists('wc_os_auto_forced', $wc_os_general_settings) || $customer_permission_status);
			
			
			if(!empty($wc_os_general_settings) && $wc_os_auto_forced){

				wc_os_crons($order_id);
			}
			

		
		}
		
		
		if(!is_admin()){
			
			$received_text = __wos_change_order_received_text($order_id, array(), false);
			$wc_os_display_child_number = (array_key_exists('wc_os_display_child_number', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_display_child_number'] && $wc_os_general_settings['wc_os_display_child_number']>0)?str_replace('[NUMBER_OF_CHILD_ORDERS]', $received_text['total'], $wc_os_general_settings['wc_os_display_child_number']).'<br /><br />':'');
				
			$str = $wc_os_display_child_number.$received_text['content'];
			
			echo $str;
				
			for($c=0;$c<3;$c++){
				wc_os_crons();
			}
			wc_os_emails_to_admin_cron();
			wc_os_emails_to_resend_cron();
			WC()->session->set( 'wc_os_customer_permitted', 'off');

		}
		
		do_action('wc_os_checkout_order_processed_after', $order_id);
		
		
	}
	
	function wc_os_get_customer_permission_status($order_id=0){		
		
		$wc_os_general_settings = get_option('wc_os_general_settings', array());
		$wc_split_permit = true;						
		
		$wc_os_customer_permission = array_key_exists('wc_os_customer_permission', $wc_os_general_settings);
		
		//wc_os_pree('wc_os_customer_permission: '. $wc_os_general_settings['wc_os_customer_permission']);
		
		//wc_os_pree('$wc_os_customer_permission: '.$wc_os_customer_permission);
		
		if($wc_os_customer_permission && $order_id){				

			$wc_split_permit = wc_os_get_order_meta($order_id, '_wc_os_customer_permitted', true);

		}
		
		//wc_os_pree('$order_id: '.$order_id);
		
		//wc_os_pree('$wc_split_permit: '.$wc_split_permit.' Bool: '.gettype($wc_split_permit));
		
		$wc_split_permit = ($wc_split_permit=='yes' || (gettype($wc_split_permit)=='boolean' && $wc_split_permit==true));
		
		//wc_os_pree('$wc_split_permit: '.$wc_split_permit);
		
		return $wc_split_permit;
	}


	function wos_get_valid_status($post, $order_id = 0){
		$ret = array();
		$valid_post_type = true;
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		
		if(is_object($post) && isset($post->post_type) && !in_array($post->post_type, array($wc_os_get_post_type_default))){
			$valid_post_type = false;
			
			if(!$valid_post_type && $post->post_parent>0){
				//wcdp_payment //20/05/2020
			
				$expected_order = get_post($post->post_parent);
			
				if(is_object($expected_order) && isset($expected_order->post_type) && in_array($expected_order->post_type, array($wc_os_get_post_type_default))){
					$valid_post_type = true;
					$order_id = $expected_order->ID;
					$post = $expected_order;
				}
				
			}
		}	
		
		
		$ret = array('valid_post_type' => $valid_post_type, 'order_id' => $order_id, 'post' => $post);
		
		return $ret;	
	}
	
	if(!function_exists('wc_os_access_protected')){
		function wc_os_access_protected($obj, $prop) {
		  $reflection = new ReflectionClass($obj);
		  $property = $reflection->getProperty($prop);
		  $property->setAccessible(true);
		  return $property->getValue($obj);
		}
	}
	
	if(!function_exists('wc_os_order_remove_shipping')){
		function wc_os_order_remove_shipping($order_id=0){
		
			if($order_id){
				
				global $wpdb;
				
				$prepare_query = "SELECT order_item_id FROM ".$wpdb->prefix."woocommerce_order_items WHERE order_item_type='shipping' AND order_id=$order_id";
				
				
				
				$get_results = $wpdb->get_results($prepare_query);
				
				
				
				if(!empty($get_results)){
					
					foreach($get_results as $order_item){
						
						$prepare_meta_query = "FROM ".$wpdb->prefix."woocommerce_order_itemmeta WHERE order_item_id=$order_item->order_item_id";
						
						$prepare_meta_select = "SELECT meta_id ".$prepare_meta_query;
						
						$get_meta_results = $wpdb->get_results($prepare_meta_select);
						
						
						
						if(!empty($get_meta_results)){
														
							$delete_query = "DELETE ".$prepare_meta_query;		
							
							$wpdb->query($delete_query);
							
							$wpdb->query("DELETE FROM ".$wpdb->prefix."woocommerce_order_items WHERE order_item_id=$order_item->order_item_id");
							
							
							
						}
						
					}
					
					
					
				}
				
			}
		}
	}
		
	function order_details_page_saved( $order_id, $post=array() ) {
		
		$debug_backtrace = debug_backtrace();
		
		$function = $debug_backtrace[0]['function'];
		$function .= ' / '.$debug_backtrace[1]['function'];
		$function .= ' / '.$debug_backtrace[2]['function'];
		$function .= ' / '.$debug_backtrace[3]['function'];
		$function .= ' / '.$debug_backtrace[4]['function'];
		
		//pree($function);	
		
		global $wc_os_auto_forced;
		
		if(!$wc_os_auto_forced){ return; }
		
		global $wp;
		
		$order_details_page = (array_key_exists('wc_os_ps', $_POST));// || stripos(home_url( $wp->request ), 'order-received')!='');
		
		
		if(isset($_POST['_wc_os_status_locked']) && $order_id){
			
			$_wc_os_status_locked = sanitize_wc_os_data($_POST['_wc_os_status_locked']);
			$_wc_os_status_locked = ($_wc_os_status_locked=='yes');
		
			wc_os_update_order_meta($order_id, '_wc_os_status_locked', $_wc_os_status_locked);
		
		}
		
		
		// If this is just a revision, don't send the email.
		if (is_admin() && (!wc_os_is_order_ready_for_processing($order_id) || wp_is_post_revision( $order_id )))
		return;
		
	
		//if ( !$post && !$order_details_page )
		
		
		
		$wos_get_valid_status = wos_get_valid_status($post, $order_id);
		extract($wos_get_valid_status);
		
		
		$do_not_proceed = ( !$valid_post_type && !$order_details_page );
		
		

		
		if ($do_not_proceed)
		return; 
		
		

			
		global $wc_os_settings, $wc_os_general_settings;
		wc_os_settings_refresh();
		



		
		
		$_wc_os_shipping_method = (isset($_POST['shipping_method'])?sanitize_wc_os_data($_POST['shipping_method']):'');
		
		if(isset($_POST['wc_os_customer_permitted'])){
			
			$wc_os_customer_permitted = sanitize_wc_os_data($_POST['wc_os_customer_permitted']);
			
			$wc_os_customer_permitted = (in_array($wc_os_customer_permitted, array('yes', 'no'))?$wc_os_customer_permitted:'yes');
			
			wc_os_update_order_meta($order_id, '_wc_os_customer_permitted',  $wc_os_customer_permitted);
		
		}
		
		
		$split_permission_status = wc_os_get_customer_permission_status($order_id);
		
		
		
		$wc_os_order_split = wc_os_order_split($order_id);
		$proceed = ($wc_os_order_split && $split_permission_status);
		//pree('$proceed: '.$proceed.', $split_permission_status: '.$split_permission_status);exit;
		//pree('A '.($proceed?'Yes':'No'));exit;
		
		if($proceed){ //01/06/2024 // || $order_details_page
			
			
			
			
			$wc_os_all_products = (isset($wc_os_settings['wc_os_all_product']) && $wc_os_settings['wc_os_all_product']) ? true : false; //flag indicating to all products are subject to splitting
			$wc_os_products = $wc_os_settings['wc_os_products'];
			
			

			$wc_os_ps = false;
			$wc_os_ie = $wc_os_settings['wc_os_ie'];
			
			if($order_details_page){
				$_POST['wc_os_ps'] = (!empty($_POST['wc_os_ps'])?sanitize_wc_os_data($_POST['wc_os_ps']):array());
				$_POST['wc_os_ps'] = array_filter($_POST['wc_os_ps'], 'is_numeric');
				
				//pree($wc_os_products);
				$wc_os_ps = true;
			}
			$wc_os_products = (isset($wc_os_products[$wc_os_ie])?$wc_os_products[$wc_os_ie]:array());

			//pree($wc_os_ps);exit;
			$wc_os_order_splitter_cron_clear = wc_os_quick_get('wc_os_order_splitter_cron_clear');
			
			
			//pree('B '.($wc_os_order_splitter_cron_clear?'Yes':'No'));exit;
			
			if(
			
					($wc_os_ie=='cats')
				||
					(
							($wc_os_ie!='cats')
						&&
							($wc_os_all_products || (!empty($wc_os_products) && count($wc_os_products)>1))
					)
						
				||
					$order_details_page
				
			){


				
				
				$wc_os_order_splitter = new wc_os_order_splitter;		

				if($wc_os_ps){ //MANUAL SPLIT RESTORED 12/12/2020
					$wc_os_settings['wc_os_ie'] = 'default';
				}
				
				//pree($wc_os_settings['wc_os_ie']);exit;
				switch($wc_os_settings['wc_os_ie']){
					
						default:
						case 'default':
						
							//pree('C #'.$order_id.' / '.($order_details_page?'Yes':'No'));exit;
							
							if($order_details_page && $wc_os_order_split){
								$wc_os_products = $_POST['wc_os_ps'];
								//pree($wc_os_products);exit;
								$wc_os_order_splitter->split_order($order_id, $wc_os_products);
							}elseif(!$wc_os_order_splitter->cron_in_progress){
								$wc_os_order_splitter_cron[$order_id] = ($wc_os_all_products?true:false);
							}
							
						break;	
						
						case 'exclusive':
						case 'inclusive':
						case 'shredder':
						case 'io':
						case 'quantity_split':
						case 'subscription_split':
						case 'cats':
						case 'groups':
						case 'group_cats':
						
							//$this->auto_split = true;
							
							
							$wc_os_order_splitter_cron[$order_id] = true;
							
						break;
						
				}

				

				//pree('D #'.$order_id.' / '.$wc_os_settings['wc_os_ie']);
				
				if(((!is_admin() && !$wc_os_order_splitter->cron_in_progress) || $wc_os_ps)){


					wc_os_set_splitter_cron($order_id, true, 6596);
					
				}
				
				//pree('E #'.$order_id.' / '.$wc_os_settings['wc_os_ie']);exit;

				
				if(((!is_admin() && !$wc_os_order_splitter->cron_in_progress) || $wc_os_ps)){
					update_option('wc_os_order_splitter_cron', $wc_os_order_splitter_cron);

				}else{
					
				}
				
				
				
			}elseif(!is_admin() && $wc_os_auto_forced){
				
				
				
				$wc_os_order_splitter_cron[$order_id] = true;

				wc_os_set_splitter_cron($order_id, true, 6618);
				update_option('wc_os_order_splitter_cron', $wc_os_order_splitter_cron);

			}
			
			
			
			$_wc_os_shipping_method_old = wc_os_get_order_meta($order_id, '_wc_os_shipping_method',  true);
			
			//pree('F #'.$order_id.' / '.$_wc_os_shipping_method_old);exit;
			
			if(!is_admin() && !$_wc_os_shipping_method_old && $_wc_os_shipping_method){
				
				wc_os_update_order_meta($order_id, '_wc_os_shipping_method',  $_wc_os_shipping_method);
				
			}
			
			//pree('G #'.$order_id.' / '.$_wc_os_shipping_method);exit;

		}		
		
		
		
		
		if(function_exists('wc_os_crons_wos_calculate_totals')){
			wc_os_crons_wos_calculate_totals($order_id);
			//pree('H #'.$order_id.' / '.$wc_os_settings['wc_os_ie']);exit;
		}
		
	
		if((isset($_GET['refreshed']) && $_GET['refreshed']=='true') && $order_id){
			
			
			$wc_os_parent_order_email = !array_key_exists('wc_os_parent_order_email', $wc_os_general_settings);

			if($wc_os_parent_order_email){
				wc_os_process_parent_email($order_id);
			}
			
		}			
	
	}
	
	add_action( 'save_post', 'order_details_page_saved', 11 );	
	
	function wc_os_order_vendor_email_status(){
		global $wc_os_settings, $wc_os_general_settings;
		
		$group_by_vendors = ($wc_os_settings['wc_os_ie']=='group_by_vendors' || $wc_os_settings['wc_os_ie']=='group_by_woo_vendors');
		
		$wc_os_order_created_email_vendor = array_key_exists('wc_os_order_created_email_vendor', $wc_os_general_settings);
	
		$order_split_created_vendor = ($group_by_vendors && $wc_os_order_created_email_vendor);
	
		return $order_split_created_vendor;
	}
	
	function wc_os_get_order_vendor($order_id=0){
		
		$ret = array();

        $_vendor_id = wc_os_get_order_meta($order_id, '_vendor_id', true);
        $_vendor_by_term = wc_os_get_order_meta($order_id, '_vendor_term', true);

        if($_vendor_id){

            if($_vendor_by_term){

                if($_vendor_id != -1){

                    $vendor_data = get_term_meta( absint( $_vendor_id ), 'vendor_data', true );
                    $info = array('user_email' => $vendor_data['email']);
                    return (object) $info;

                }else{

                    $new_order_settings = get_option( 'woocommerce_new_order_settings', array() );
                    $new_order_settings = ! empty( $new_order_settings['recipient'] ) ? $new_order_settings['recipient'] : get_option( 'admin_email' );
                    $info = array('user_email' => $new_order_settings);
                    return (object) $info;
                }


            }else{

			    $ret = get_userdata($_vendor_id);

            }


		}
		
		return $ret;
		
	}
	
	function wc_os_process_parent_email($order_id){
	
		
		
		global $wc_os_settings, $wc_os_general_settings, $woocommerce;
			
		
		$post_meta = wc_os_get_order_meta($order_id);
		if(!empty($post_meta)){
			
			$body = '';
			
			
			
			$post_meta = array_keys($post_meta);
	
			$is_parent_order = (!in_array('splitted_from', $post_meta) && !in_array('cloned_from', $post_meta));
	
			if($is_parent_order){
				

					$has_child_orders = (count(wc_os_child_orders_by_order_id($order_id))>0);
					$_wos_parent_email = wc_os_get_order_meta($order_id, '_wos_parent_email', true);
					$_wos_parent_email = is_array($_wos_parent_email)?$_wos_parent_email:array();

					if(!$has_child_orders && !empty($_wos_parent_email)){
						
						
						$_billing_email = wc_os_get_order_meta($order_id, '_billing_email', true);
						$_customer_registered_id = wc_os_get_order_meta($order_id, '_customer_user', true);
						
						$new_order_settings = get_option('woocommerce_new_order_settings');
						$new_order_settings = (is_array($new_order_settings)?$new_order_settings:array('recipient'=>''));
						$new_order_settings['recipient'] = ($new_order_settings['recipient']!=''?$new_order_settings['recipient']:get_option('admin_email'));
						
						$mailer = $woocommerce->mailer();
						
						$co_efrom_name = get_bloginfo('name');
						$co_efrom_email = get_bloginfo('admin_email');
						$co_ereplyto_email = get_bloginfo('admin_email');						
							
						
						ob_start();
						wc_get_template( 'emails/email-header.php', array( 'email_heading' => get_bloginfo('name') ) );
						echo $body;
						wc_get_template( 'emails/email-footer.php' );
						$message = ob_get_clean();
						
						$headers = array(
							'Content-Type: text/html; charset=UTF-8',
							'From: '.$co_efrom_name.' <'.$co_efrom_email.'>',
							($co_ereplyto_email?'Reply-To: '.get_bloginfo('name').' <'.$co_ereplyto_email.'>':'')
						);
					
						$status = $mailer->send( $new_order_settings['recipient'], $_wos_parent_email['subject'], $_wos_parent_email['message'], $headers );
												
						
						$order_split_created_vendor = wc_os_order_vendor_email_status();
						if($order_split_created_vendor){
							$user_info = wc_os_get_order_vendor($order_id);
							if(!empty($user_info)){
								$status = $mailer->send( $user_info->user_email, $_wos_parent_email['subject'].'', $_wos_parent_email['message'], $headers );
								$wc_os_logger_str = $user_info->user_email.' - '.$_wos_parent_email['subject'].' '.($status?__('Success', 'woo-order-splitter'):__('Failed', 'woo-order-splitter')).' '.__('Vendor Email', 'woo-order-splitter');
								
							}
						}
													
						if($_billing_email){
							
							$status = $mailer->send( $_billing_email, $_wos_parent_email['subject'], $_wos_parent_email['message'], $headers );
							
							$wc_os_logger_str = $_billing_email.' - '.$_wos_parent_email['subject'].' '.($status?__('Success', 'woo-order-splitter'):__('Failed', 'woo-order-splitter')).' '.__('Billing Email', 'woo-order-splitter');
							
							
						}
						
						
						
						if($_customer_registered_id){
							$get_customer_by = get_user_by('ID', $_customer_registered_id);
							
							if($get_customer_by->user_email && $_billing_email!=$get_customer_by->user_email){
								$status = $mailer->send( $get_customer_by->user_email, $_wos_parent_email['subject'], $_wos_parent_email['message'], $headers );
								
								$wc_os_logger_str = $get_customer_by->user_email.' - '.$_wos_parent_email['subject'].' '.($status?__('Success', 'woo-order-splitter'):__('Failed', 'woo-order-splitter')).' '.__('Registered Email', 'woo-order-splitter');
								
							}
						}						
						
						wc_os_delete_order_meta($order_id, '_wos_parent_email');
						
						
						
					}
				
			}
		}		
	}
	
	if(!function_exists('wc_os_get_session')){
		function wc_os_get_session($key, $default='') {
			
			return ((!is_admin() && isset(WC()->session) && WC()->session->has_session())?WC()->session->get($key):$default);
			
		}
	}
	
	function wc_os_front_scripts() {	
	
		global $wp, $wc_os_settings, $wc_os_general_settings, $woocommerce, $wc_os_minified_js, $wc_os_minified_css, $product, $wc_os_pro;
	
		$wc_os_shipping_methods = array_key_exists('wc_os_shipping_methods', $wc_os_general_settings);
		$wc_os_customer_permission = array_key_exists('wc_os_customer_permission', $wc_os_general_settings);

		$front_script = ($wc_os_minified_js ? 'js/front-scripts-min.js' : 'js/front-scripts.js');
		$front_style = ($wc_os_minified_css ? 'css/front-style-min.css' : 'css/front-style.css');
				
		wp_enqueue_script(
			'wos_scripts',
			plugins_url($front_script, dirname(__FILE__)),
			array('jquery'),
			$wc_os_minified_js?date('Y'):time()
		);	

		$product_id = (is_object($product)?$product->get_id():0);
		
		
		wp_enqueue_style( 'wos-styles', plugins_url($front_style, dirname(__FILE__)), '', ($wc_os_minified_css?date('Y'):time()) );
		
		$chosen_methods = wc_os_get_session( 'chosen_shipping_methods' );
		
		$chosen_shipping = !empty($chosen_methods)?current($chosen_methods):''; 
		
		$cart_url = (function_exists('wc_get_cart_url')?wc_get_cart_url():$woocommerce->cart->get_cart_url());
		$checkout_url = (function_exists('wc_get_checkout_url')?wc_get_checkout_url():$woocommerce->cart->get_checkout_url());
		
		
		$translation_array = array(
			'wc_os_packages_overview' => array_key_exists('wc_os_packages_overview', $wc_os_general_settings) ? 'on' : 'off',
			'orders_page_refresh' => in_array('order-refresh', $wc_os_settings['wc_os_additional'])?'false':'true',
			'chosen_shipping_method' => $chosen_shipping,
			'total_shipping_cost' => wc_os_get_session('wc_os_total_shipping_cost'),
			'wc_os_actual_shipping_cost' => wc_os_get_session('wc_os_actual_shipping_cost'),
			'wc_os_parcels_count' => is_array(wc_os_get_session('wc_os_parcels_array'))?count(wc_os_get_session('wc_os_parcels_array')):0,
			'wc_os_customer_permitted' => (!$wc_os_customer_permission || ($wc_os_customer_permission && wc_os_get_session('wc_os_customer_permitted', 'on'))),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'wc_os_customer_permitted_nonce' => wp_create_nonce( 'wc_os_customer_permitted_action' ),
			'wc_os_shipping_methods' => $wc_os_shipping_methods,			
			'cart_url' => $cart_url,
			'checkout_url' => $checkout_url,
			'is_cart' => (is_cart()),// || $is_cart),
			'is_checkout' => (is_checkout()),// || $is_checkout),			
			'is_product' => (is_product()),
			'is_view_order' => stripos(home_url( $wp->request ), 'view-order')!='',
			'is_thank_you' => stripos(home_url( $wp->request ), 'order-received')!='',
			'product_url' => (is_product()?get_permalink():''),
			'this_url' => get_permalink(),
			'url' => plugin_dir_url( dirname(__FILE__) ),
			'posted' => is_array($_POST)?count($_POST):0,
			'is_pro' => $wc_os_pro, 
			'total_cart_items' => count(( !is_null(WC()) && !is_null(WC()->cart) && ! WC()->cart->is_empty() )?WC()->cart->get_cart():array()),
			'_wos_backorder_limit' => '', 
		


		);
		
		if($product_id){
			
			$product = wc_get_product($product_id);
		
			$_wos_backorder_limit = (int)get_post_meta($product_id,  '_wos_backorder_limit', true);
			$stock_quantity = (int)$product->get_stock_quantity();
			
			
			$translation_array['_wos_backorder_limit'] = ($stock_quantity>0 && $_wos_backorder_limit?($_wos_backorder_limit-$stock_quantity):($_wos_backorder_limit+$stock_quantity));
			
			$translation_array['stock_quantity'] = $stock_quantity;
			
			$translation_array['_wos_backorder_msg'] = __('Backorder limit reached.', 'woo-order-splitter');
			
			
		}
		
		
		wp_localize_script( 'wos_scripts', 'wos_obj', $translation_array );		
	}
		
	function wc_os_admin_scripts() {
		
		global $wpdb, $wc_os_css_arr, $wc_os_settings, $post, $wc_os_cust, $wos_actions_arr, $wc_os_general_settings, $wp_scripts, $wc_os_minified_js, $wc_os_minified_css, $post_type, $wc_os_order_statuses_class, $wc_os_pro, $wuos_wos, $pagenow;
		$is_wc_os_settings = (isset($_GET['page']) && $_GET['page']=='wc_os_settings');
        $admin_script = ($wc_os_minified_js ? 'js/admin-scripts-min.js' : 'js/admin-scripts.js');
        $admin_style = ($wc_os_minified_css ? 'css/admin-style-min.css' : 'css/admin-style.css');
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		
		$wc_os_order_splitter = new wc_os_order_splitter;
		
		if($is_wc_os_settings){
			wp_enqueue_style( 'wos_bs_grid_only', plugins_url('css/bootstrap-grid.min.css', dirname(__FILE__)), array(), true );
		}		
		$subscription_split = in_array('subscription_split', $wc_os_settings['wc_os_additional']);
		$is_edit_order_page = ($pagenow=='post.php' && is_object($post) && ($post->post_type==$wc_os_get_post_type_default || ($subscription_split && $post->post_type=='shop_subscription')));
		$is_edit_order_page = (!$is_edit_order_page && (isset($_GET['page']) && $_GET['page']=='wc-orders' && isset($_GET['action']) && $_GET['action']=='edit' && isset($_GET['id']) && $_GET['id']>0));
		$is_edit_profile_page = (in_array($pagenow, array('profile.php', 'user-edit.php', 'edit.php')));
		
		//wc_os_pre('$is_edit_order_page: '.$is_edit_order_page);
	
		if(($is_wc_os_settings && !array_key_exists('wc_os_fa', $wc_os_general_settings)) || $is_edit_order_page || $is_edit_profile_page){
			wp_enqueue_style( 'fontawesome', plugins_url('css/fontawesome.min.css', dirname(__FILE__)), array(), true );
			
			wp_enqueue_script(
				'fontawesome',
				plugins_url('js/fontawesome.min.js', dirname(__FILE__)),
				array('jquery'),
				$wc_os_minified_js?date('Y'):time()
			);	
		}
		if($is_wc_os_settings && !array_key_exists('wc_os_bs', $wc_os_general_settings)){	
			wp_enqueue_style( 'bootstrap', plugins_url('css/bootstrap.min.css', dirname(__FILE__)), array(), true );
			
			wp_enqueue_script(
				'bootstrap',
				plugins_url('js/bootstrap.min.js', dirname(__FILE__)),
				array('jquery'),
				$wc_os_minified_js?date('Y'):time()
			);				
		}
	
		if($is_wc_os_settings && !array_key_exists('jquery-ui-sortable', $wp_scripts->registered)){

		    wp_enqueue_script('jquery-ui-sortable');
        }		
		
		
		wp_enqueue_style( 'wos-admin', plugins_url($admin_style, dirname(__FILE__)), array(), time() );
		
		
		wp_enqueue_script(
			'blockUI',
			plugins_url('js/jquery.blockUI.js', dirname(__FILE__)),
			array('jquery'),
			$wc_os_minified_js?date('Ym'):time()
		);
		wp_enqueue_script(
			'wos_scripts',
			plugins_url($admin_script, dirname(__FILE__)),
			array('jquery', 'jquery-ui-sortable'),
			$wc_os_minified_js?date('Ym'):time()
		);	
		
		wp_enqueue_style( 'jquery.multiselect', plugins_url('css/jquery.multiselect.css', dirname(__FILE__)), array(), true );
		
		wp_enqueue_script(
			'jquery.multiselect',
			plugins_url('js/jquery.multiselect.js', dirname(__FILE__)),
			array('jquery'),
			$wc_os_minified_js?date('Y'):time()
		);		
	
		$order_id = (isset($_GET['post'])?$_GET['post']:(isset($_GET['id'])?$_GET['id']:0));
		$conflict_status_options = array();
		//$is_edit_order_page = (is_object($post) && isset($post->post_type) && $post->post_type==$wc_os_get_post_type_default && isset($_GET['action']) && $_GET['action']=='edit');
		
		$wc_os_group_meta = get_option('wc_os_group_meta', array());
		$wc_os_group_meta = (is_array($wc_os_group_meta) && !empty($wc_os_group_meta)?$wc_os_group_meta:array('wc_os_cats'=>array('group_cats'=>array())));
		
		
		$translation_array = array(
			'wuos_wos' => ($wc_os_pro?'yes':'no'),
			'wos_edit_custom_order_status' => esc_attr(__('If you want to update slug as well, please delete and create a new order status instead.', 'woo-order-splitter')),
			'combined_info' => '',
			'permission_text_warning' => __('This option will not work with Auto Split.', 'woo-order-splitter'),
			'wc_os_group_meta' => $wc_os_group_meta,
			'wc_os_nonce' => wp_create_nonce('wc_os_nonce_action'),
			'wc_os_vendor_nonce' => wp_create_nonce('wc_os_vendors_action'),
			'defined_rules_confirm' => __('Are you sure, you want delete this rule?', 'woo-order-splitter'),
			'this_url' => admin_url( 'admin.php?page=wc_os_settings' ),
			'conflict_status' => '',			
			'wc_os_defalut_split_1' => __('Manual split from order page will not follow the split rules which you defined on the settings page. Here you split with the manual selection.', 'woo-order-splitter'),
			'wc_os_defalut_split_2' => __('Split with the rules > Orders list page and the following icon', 'woo-order-splitter').' <span class="wc_os_manual_split"><a target="_blank" href="https://ibulb.wordpress.com/2021/04/05/auto-split-vs-manual-split/" class="wc_os_split"></a></span>',			
			'orders_list' => admin_url( 'edit.php?post_type=shop_order' ),
			'wc_os_ie' => $wc_os_settings['wc_os_ie'],
			'wc_os_tab' => (isset($_GET['t'])?esc_attr($_GET['t']):'0'),
			'wc_os_sub_tab' => (isset($_GET['sub_tab'])?esc_attr($_GET['sub_tab']):''),
			'wc_os_pg' => (isset($_GET['pg'])?esc_attr($_GET['pg']):'0'),
			'orders_list_refresh' => in_array('split-refresh', $wc_os_settings['wc_os_additional']),
			'mark_parent' => __('Click here to mark this item as parent/main order during this action', 'woo-order-splitter'),
			'wc_os_splitting' => (((isset($_GET['post_type']) && $_GET['post_type']==$wc_os_get_post_type_default) && 
			
							(
									
									((isset($_GET['order_id']) && is_numeric($_GET['order_id'])) && (isset($_GET['split']) && $_GET['split']=='init') && isset($_GET['split-session']))
								||
									isset($_GET['ids'])
									
							))?true:false),
			'wc_os_group_statuses' => (function_exists('wc_os_statuses_by_action') ? wc_os_statuses_by_action($wc_os_settings['wc_os_ie']) : array()),
			'in_stock_items' => array(),
			'backorder_items' => array(),
			'order_items' => (object)array(),
			'order_items_details' => array(),
			'wc_os_all_product' => (isset($wc_os_settings['wc_os_all_product']) && $wc_os_settings['wc_os_all_product']=='all_products'),	
		);
		
		if($is_edit_order_page){
			
			$conflict_status = wc_os_get_order_meta($order_id, 'conflict_status', true);
			
			$order_data = wc_get_order( $order_id );
				
				if(count($order_data->get_items())>0){			
					
					$order_items_details = array();
					
					foreach($order_data->get_items() as $order_item_key=>$order_item_data){
						
						$product_id = $order_item_data->get_product_id();
						$product_cats = get_the_terms ( $product_id, 'product_cat' );
						
						
						$order_items_details[$order_item_key] = array();
						
						if(!empty($product_cats)){
							$in_groups = array();
							switch($wc_os_settings['wc_os_ie']){
								case 'group_cats':
									$in_groups = $wc_os_settings['wc_os_cats']['group_cats'];
								break;
							}

							foreach($product_cats as $product_cat_data){
								
								$related_group = '';
								
								foreach($in_groups as $group_key=>$group_items){
									if(in_array($product_cat_data->term_id, $group_items)){
										$related_group = ucfirst($group_key);
									}
								}
								
								if($related_group){
									$order_items_details[$order_item_key]['cats'][$product_cat_data->term_id] = array('name'=>$product_cat_data->name, 'group'=>$related_group);
								}
							}
						}
						
						
					}
					$translation_array['order_items_details'] = $order_items_details;
					
					if($conflict_status){
					
			
						$wc_os_ie_selected = $wc_os_settings['wc_os_ie'];
						
						$products_with_actions = $wc_os_order_splitter->products_with_actions();
						$products_with_actions = (is_array($products_with_actions)?$products_with_actions:array());
						$actions_arr = array();
						foreach($order_data->get_items() as $item_data){				
	
							if(array_key_exists($item_data->get_product_id(), $products_with_actions)){
								$actions_arr[] = $products_with_actions[$item_data->get_product_id()];
							}
						}
						$actions_arr = array_unique($actions_arr);
			
						if(count($actions_arr)==0){ //BACKWARDS COMPATIBILITY
						}elseif(count($actions_arr)==1){ //REGULAR/VALID/NORMAL CASE
							$wc_os_ie_selected = current($actions_arr);
						}elseif(count($actions_arr)>1){ //EXPECTED/INVALID/CONFLICT CASE					
							$conflict_status_options[] = '<h6>'.__('Following product/items are configured with different split actions.', 'woo-order-splitter').'<br />'.__('Select one of these split actions to proceed for this order.', 'woo-order-splitter').'</h6>';
							if(!empty($actions_arr)){
								$conflict_status_options[] = '<ul>';
								$wos_forced_ie = wc_os_get_order_meta($order_id, 'wos_forced_ie', true);
								foreach($actions_arr as $action_title){
									$conflict_status_options[] = '<li><a data-action="'.$action_title.'" class="'.($wos_forced_ie==$action_title?'forced':'').'">'.ucwords(split_actions_display($action_title, 'title')).'</a></li>';
								}
								$conflict_status_options[] = '</ul><div class="wos_loading smart"></div>';
							}
						}				
					}
				
				
					$translation_array['conflict_status'] = (!empty($conflict_status_options)?implode('', $conflict_status_options):'');					
				
				}
		}
		
		
		switch($wc_os_settings['wc_os_ie']){
			case 'io':
				if(is_object($post) && $post->post_type==$wc_os_get_post_type_default){ //
					$wc_os_order_splitter->cron_light_in_progress = true;	
					
					$data = array();
					
					if(wc_os_order_split($post->ID)){
						$data = $wc_os_order_splitter->split_order_logic($post->ID);
					}
					
					$data = is_array($data)?$data:array();
					
					$items_io = (array_key_exists('items_io', $data)?$data['items_io']:array());
					
					$order_items = (array_key_exists('order_items', $items_io)?$items_io['order_items']:array());

					$in_stock_items = (isset($items_io['in_stock']) && !empty($items_io['in_stock']) && isset($items_io['in_stock']['items']) && !empty($items_io['in_stock']['items']))?$items_io['in_stock']['items']:array();
					$backorder_items = (isset($items_io['backorder']) && !empty($items_io['backorder']) && isset($items_io['backorder']['items']) && !empty($items_io['backorder']['items']))?$items_io['backorder']['items']:array();
					
					$translation_array['in_stock_items'] = $in_stock_items;
					$translation_array['backorder_items'] = $backorder_items;
					$translation_array['order_items'] = $order_items;
					
					//wc_os_pree('$translation_array:');wc_os_pree($translation_array);exit;

				}
			break;
		}
		
		
		$translation_array['booking_strings'] = array(
		        'd' => __('All booking items will be grouped by same date', 'woo-order-splitter'),
		        'm' => __('All booking items will be grouped by same month', 'woo-order-splitter'),
		        'y' => __('All booking items will be grouped by same year', 'woo-order-splitter'),
		        'd_m' => __('All booking items will be grouped by same day and month', 'woo-order-splitter'),
		        'd_y' => __('All booking items will be grouped by same day and year', 'woo-order-splitter'),
		        'm_y' => __('All booking items will be grouped by same month and year', 'woo-order-splitter'),
        );
		
		$translation_array['wos_email_tab'] = (isset($_GET['wos_email_tab']) ? $_GET['wos_email_tab'] : false);
		$translation_array['wc_os_product_search'] = (isset($_POST['wc_os_product_search']) ? sanitize_wc_os_data($_POST['wc_os_product_search']) : false);		
		$translation_array['wc_os_clear_log_nonce'] = wp_create_nonce('wc_os_clear_log_nonce');
		
		
		
		if($is_edit_order_page){
            $custom_order_statuses = (is_object($wc_os_order_statuses_class)?$wc_os_order_statuses_class->get_all_statuses():array());
            $shop_order_array = array(
				'is_premium' => $wc_os_pro?'yes':'no',
				'wuos_wos' => $wuos_wos?'yes':'no',
                'post_type' => $post_type,
                'disable_split' => in_array('split', $wc_os_settings['wc_os_additional']),
                'post_status' => (array_key_exists('post_status', $_GET) ? $_GET['post_status'] : ''),
                'wc_os_order_cloning' => wc_os_order_cloning(),
                'wos_pending_str' => esc_attr(__('Change status to pending', 'woo-order-splitter')),
                'wos_cancelled_str' => esc_attr(__('Change status to cancelled', 'woo-order-splitter')),
                'wos_restore_str' => esc_attr(__('Force Restore', 'woo-order-splitter')),
                'clone_str' => esc_attr(__('Clone Orders', 'woo-order-splitter')),
                'split_str' => esc_attr(__('Split Orders', 'woo-order-splitter')),
				'parent_str' => esc_attr(__('Parent Order', 'woo-order-splitter')),
				'child_str' => esc_attr(__('Child Order', 'woo-order-splitter')),
                'combine_str' => esc_attr(__('Consolidate/Combine/Merge Orders', 'woo-order-splitter')),
                'mark_str' => esc_attr(__('Mark', 'woo-order-splitter')),				
                'isset_mt' => isset($_GET['mt']),
                'isset_post' => isset($_GET['post']),
                'mt' => (isset($_GET['mt']) ? $_GET['mt'] : ''),
                'custom_order_statuses' => (!empty($custom_order_statuses) ? $custom_order_statuses : false),
				'ws_os_to_split' => __('Split Orders', 'woo-order-splitter'),
				'admin_url' => admin_url('post.php?post=ORDER_ID&action=edit'),
				
            );
            $translation_array = array_merge($translation_array, $shop_order_array);
			
			
			if($order_id){
				$po_number = wc_os_get_order_meta($order_id, 'po_number', true);
				$po_number = ($po_number?$po_number:wc_os_get_order_meta($order_id, '_po_number', true));
				$po_number = is_array($po_number)?$po_number:($po_number?array($po_number):array());

				if(!empty($po_number)){
					$translation_array['combined_info'] .= '<li><b>'.__('Ordered by / PO#', 'woo-order-splitter').':</b> '.implode(', ', $po_number).'</li>';
				}
				
				$_wos_child_orders = wc_os_get_order_meta($order_id, '_wos_child_orders', true);
				//pree($_wos_child_orders);
				$_wos_child_orders = is_array($_wos_child_orders)?$_wos_child_orders:array();
				
				
				
				if(!empty($_wos_child_orders)){				
					$translation_array['combined_info'] .= '<li><b>'.__('This invoice is a combination of', 'woo-order-splitter').':</b> '.implode(', ', $_wos_child_orders).'</li>';
				}
				
				$translation_array['splitted_from'] = wc_os_get_order_meta($order_id, 'splitted_from', true);
				$translation_array['splitted_from'] = ($translation_array['splitted_from']?$translation_array['splitted_from']:$order_id);
				$translation_array['sibling_orders'] = array();
				$translation_array['orders_statuses'] = array();
				
				if($translation_array['splitted_from']!='' && is_numeric($translation_array['splitted_from'])){
					
					$args = array(
	
						'numberposts' => -1,
						'post_type' => $wc_os_get_post_type_default,
						'post_status' => 'any',
						'fields' => 'ids',
						'exclude' => array($order_id),
						'meta_query' => array(
	
							array(
	
								'key' => 'splitted_from',
								'value' => $translation_array['splitted_from'],
								'compare' => '=',
							),
	
						),
	
	
					);
	
	
					$get_child_ids = get_posts($args);
					
					if(!empty($get_child_ids)){
						foreach($get_child_ids as $get_child_id){
							$translation_array['sibling_orders'][] = $get_child_id;
						}
						$parent_child_ids = $translation_array['sibling_orders'];
						$parent_child_ids[] = $translation_array['splitted_from'];
						
						$orders_statuses_query = "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (".implode(',', $parent_child_ids).")";
						$orders_statuses_results = $wpdb->get_results($orders_statuses_query);
						
						if(!empty($orders_statuses_results)){
							foreach($orders_statuses_results as $orders_statuses_result){
								$translation_array['orders_statuses'][$orders_statuses_result->ID] = ucwords(str_replace('wc-', '', $orders_statuses_result->post_status));
							}
						}
						
						
					}else{
						$translation_array['splitted_from'] = '';
					}
					
				}
				

			}
		
        }
		$translation_array = apply_filters('wc_os_translation_array', $translation_array);		

		wp_localize_script( 'wos_scripts', 'wos_obj', $translation_array );
		
		
		
	}		
	
	add_filter( 'woocommerce_checkout_fields' , 'wc_os_override_checkout_fields' );
	
	function wp_os_filter_bulk_actions_edit_shop_order( $array = array() ) { 
		
		//pree($array);
		
		global $wc_os_settings, $wc_os_order_statuses_class;
		
		$post_status = (array_key_exists('post_status', $_GET) ? $_GET['post_status'] : '');
		$disable_split = in_array('split', $wc_os_settings['wc_os_additional']);

		$array['wos_pending'] = esc_attr(__('Change status to pending', 'woo-order-splitter'));
		$array['wos_cancelled'] = esc_attr(__('Change status to cancelled', 'woo-order-splitter'));
		
		if($post_status == 'trash'){
			$array['wos_restore'] = esc_attr(__('Force Restore', 'woo-order-splitter'));
		}
		if(wc_os_order_cloning()){
			$array['clone'] = esc_attr(__('Clone Orders', 'woo-order-splitter'));
		}
		if(!$disable_split){
			$array['split'] = esc_attr(__('Split Orders', 'woo-order-splitter'));
		}
		$array['combine'] = esc_attr(__('Consolidate/Combine/Merge Orders', 'woo-order-splitter'));

		
        $custom_order_statuses = (is_object($wc_os_order_statuses_class)?$wc_os_order_statuses_class->get_all_statuses():array());
		
		if(!empty($custom_order_statuses)){ 
			foreach($custom_order_statuses as $status_key=>$status_text){			
				$array['mark_'.$status_key] = esc_attr(__('Mark', 'woo-order-splitter')).' '.$status_text['status_name'];
			}
		}
		
			


		return $array; 
	}; 
			 
	// add the filter 
	add_filter( 'bulk_actions-edit-shop_order', 'wp_os_filter_bulk_actions_edit_shop_order', 20, 1 ); 
	add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'wp_os_filter_bulk_actions_edit_shop_order', 20, 1 ); //02/04/2024
	
	
	function split_actions_display($key, $type){
		global $wc_os_cust, $wos_actions_arr;
		
		$action_type = ($type=='title'?'action':'description');
		$ret = ((isset($wc_os_cust[$key]) && isset($wc_os_cust[$key][$type]) && $wc_os_cust[$key][$type]!='')?$wc_os_cust[$key][$type]:((isset($wos_actions_arr[$key]) && isset($wos_actions_arr[$key][$action_type]))?$wos_actions_arr[$key][$action_type]:''));
		
		return $ret;
	}
	
	
	
	function wc_os_override_checkout_fields( $fields ) {
				
		global $wc_os_general_settings;
		$wc_os_general_settings = (is_array($wc_os_general_settings)?$wc_os_general_settings:array());

		if(!array_key_exists('wc_os_shipping_off', $wc_os_general_settings)){
			unset($fields['shipping']['shipping_first_name']);
			unset($fields['shipping']['shipping_last_name']);
			unset($fields['shipping']['shipping_company']);
			unset($fields['shipping']['shipping_address_1']);
			unset($fields['shipping']['shipping_address_2']);
			unset($fields['shipping']['shipping_city']);
			unset($fields['shipping']['shipping_postcode']);
			unset($fields['shipping']['shipping_country']);
			unset($fields['shipping']['shipping_state']);
			unset($fields['shipping']['shipping_phone']);
			unset($fields['shipping']['shipping_email']);

		}
		
		if(!array_key_exists('wc_os_billing_off', $wc_os_general_settings)){
			
			unset($fields['billing']['billing_first_name']);
			unset($fields['billing']['billing_last_name']);
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_address_1']);
			unset($fields['billing']['billing_address_2']);
			unset($fields['billing']['billing_city']);
			unset($fields['billing']['billing_postcode']);
			unset($fields['billing']['billing_country']);
			unset($fields['billing']['billing_state']);
			unset($fields['billing']['billing_phone']);	
			unset($fields['billing']['billing_email']);
		}
		
		if(!array_key_exists('wc_os_order_comments_off', $wc_os_general_settings))
		unset($fields['order']['order_comments']);
		
		return $fields;
	}	
	
	function wc_os_header_scripts(){
		
		global $wc_os_general_settings;
?>
	<style type="text/css">
	<?php
		if(!array_key_exists('wc_os_shipping_off', $wc_os_general_settings)){
?>
			.woocommerce-shipping-fields{
				display:none;	
			}
<?php			
		}
		if(!array_key_exists('wc_os_billing_off', $wc_os_general_settings)){
?>
			.woocommerce-billing-fields{
				display:none;	
			}
<?php			
		}
		if(!array_key_exists('wc_os_order_comments_off', $wc_os_general_settings)){
?>
			.woocommerce-additional-fields{
				display:none;
			}
<?php			
		}				
	?>
	</style>
<?php		
	}
	
	
	
	function wc_os_order_cloning(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$cloning = (in_array('cloning', $wc_os_settings['wc_os_additional']));		
		
		return $cloning;
	}
	function wc_os_order_split($order_id=0, $bypass=''){
		wc_os_settings_refresh();
		global $wc_os_settings, $post;
		
		$split = (!in_array('split', $wc_os_settings['wc_os_additional']));
		$split_lock = (isset($wc_os_settings['wc_os_additional']['split_lock'])?$wc_os_settings['wc_os_additional']['split_lock']:array());
		
		if(!is_array($split_lock)){ //BACKWARDS COMPATIBILITY
			$wc_os_settings['wc_os_additional']['split_lock'] = ($split_lock!=''?array($split_lock):array());
			update_option('wc_os_settings', sanitize_wc_os_data($wc_os_settings));
		}
		
		$split_lock = is_array($split_lock)?$split_lock:array();
		

		if($split && !empty($split_lock)){
			$split = false; //05/03/2022 - SETTING IT TO FALSE BECAUSE IT HAS TO BE TRUE AGAIN ON THE BASIS OF LOCK
			$wc_os_order_splitter = new wc_os_order_splitter;
			if(!$order_id){
				$order_id = (isset($_GET['order_id'])?sanitize_wc_os_data($_GET['order_id']):$wc_os_order_splitter->original_order_id);
				$order_id = (!$order_id?(isset($_POST['post_ID']) && $post->ID==$_POST['post_ID'])?sanitize_wc_os_data($_POST['post_ID']):$order_id:$order_id);
			}
			if(!$order_id){
				$order_id = (!$order_id?(isset($_GET['post']) && $post->ID==$_GET['post'])?sanitize_wc_os_data($_GET['post']):$order_id:$order_id);
			}

			if($order_id){
				$get_order_status = str_replace('wc-', '', get_post_status($order_id));
				$bypass_status = str_replace('wc-', '', $bypass);
				

				$split = empty($split_lock);
				
				if(!empty($split_lock)){
					foreach($split_lock as $split_lock_i){
						if(!$split){

							$split = ($get_order_status==$split_lock_i);
						}
					}
				}
				
				if(!$split && $bypass_status!='' && $bypass_status==$get_order_status){
					$split = true;
				}
			}
			
		}
		if($split){
			$split = !wc_os_get_order_meta($order_id, 'split_status');
		}
		
		return $split;		
	}
	
	function wc_os_order_removal(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$removal = (in_array('removal', $wc_os_settings['wc_os_additional']));
		
		return $removal;		
	}
	function wc_os_order_removal_action($type='boolean'){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$removal_lock = (array_key_exists('removal_lock', $wc_os_settings['wc_os_additional']) && $wc_os_settings['wc_os_additional']['removal_lock']);
		
		if($type=='string' && $removal_lock){
			$removal_lock = $wc_os_settings['wc_os_additional']['removal_lock'];
		}
		
		return $removal_lock;		
	}	
	
	function wc_os_order_split_removal_action($type='boolean', $line_no=''){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$removal_lock = (array_key_exists('removal_lock_split', $wc_os_settings['wc_os_additional']) && $wc_os_settings['wc_os_additional']['removal_lock_split']);
		
		if($type=='string' && $removal_lock){
			$removal_lock = $wc_os_settings['wc_os_additional']['removal_lock_split'];
		}

		$debug_backtrace = debug_backtrace();
		
		$function = $debug_backtrace[0]['function'];
		$function .= ' / '.$debug_backtrace[1]['function'];
		$function .= ' / '.$debug_backtrace[2]['function'];
		$function .= ' / '.$debug_backtrace[3]['function'];
		$function .= ' / '.$debug_backtrace[4]['function'];
		

				
		return $removal_lock;		
	}		
	
	function wc_os_order_split_status_action($order=array(), $include_items=array()){
		
		wc_os_settings_refresh();
		global $wc_os_settings;

		$split_status_lock = in_array('split_status_lock', $wc_os_settings['wc_os_additional']);
		$split_status_lock = (isset($wc_os_settings['wc_os_additional']['split_status_lock'])?$wc_os_settings['wc_os_additional']['split_status_lock']:'');
		
		wc_os_pre('$split_status_lock: '.$split_status_lock, '');
		
		if(is_object($order)){
			$split_status_lock = apply_filters('wc_os_split_order_status_logic_hook', $split_status_lock, $order, $include_items);
		}
		
		wc_os_pre('$split_status_lock: '.$split_status_lock, '');
		
		return $split_status_lock;		
	}	
		
	function wc_os_order_qty_split(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$qty_split = (in_array('qty_split', $wc_os_settings['wc_os_additional']));
		
		return $qty_split;		
	}	
	
	add_action( 'pre_get_posts', 'wos_shop_page_pre_get_posts' );
	//add_action( 'woocommerce_order_query_args', 'wos_shop_page_pre_get_posts' ); //02/04/2024
	
	function wos_shop_page_pre_get_posts( $query ) {
		
		$is_main_query = (!method_exists($query, 'is_main_query') || (method_exists($query, 'is_main_query') && $query->is_main_query()));
		
		//pree($is_main_query);
		
		if ($is_main_query && isset($_GET['vendor']) && is_numeric($_GET['vendor']) && $_GET['vendor']>0){
			
			//$query->set( 'author', sanitize_wc_os_data($_GET['vendor']) ); //02/04/2024

		}
		
		global $current_screen, $wc_os_order_statuses_class, $wpdb, $typenow;
		
		//pree($typenow);
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		
		if(is_admin()){
			
			$filter_order_items = (is_object($current_screen) && $current_screen->post_type==$wc_os_get_post_type_default && !array_key_exists('post_status', $_GET));
			
			//pree('$filter_order_items: '.$filter_order_items);
			
			if($filter_order_items){
				
				$order_statuses_available_only = array();//((is_object($wc_os_order_statuses_class) && method_exists($wc_os_order_statuses_class, 'order_statuses_available_only'))?$wc_os_order_statuses_class->order_statuses_available_only():array());
				
				
				if($is_main_query && !empty($order_statuses_available_only)){	
					
					//$query->set('post_status', $order_statuses_available_only); //02/04/2024
				}
				
			}
			
			
			
			//pree($query);
		}
	
	}
	
	
	add_action( 'woocommerce_my_account_my_orders_query', 'wc_os_woocommerce_my_account_my_orders_query' );
	
	function wc_os_woocommerce_my_account_my_orders_query($args){
	
		global $wc_os_order_statuses_class;
		
		$order_statuses_available_only = (is_object($wc_os_order_statuses_class)?$wc_os_order_statuses_class->order_statuses_available_only():array());
		
		if(!empty($order_statuses_available_only)){
		
			$args['status'] = $order_statuses_available_only;
		
		}
				
		return $args;
	}
	
	
	
	
	function wc_os_links($actions, $post=array()){
		
		//wc_os_pree($actions);wc_os_pree($post->ID);
		
		global $wc_os_settings, $wpdb;
		
		$subscription_split = in_array('subscription_split', $wc_os_settings['wc_os_additional']);
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		
		
		if (is_object($post) && $post->post_type==$wc_os_get_post_type_default && wc_os_order_cloning()) {
			
			$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $post->ID );
			
			$copy_link = wp_nonce_url( add_query_arg( array( 'clone' => 'yes', 'clone-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
			
			$actions = array_merge( $actions, 
				array(
					'clone' => sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $copy_link ), 
						__('Clone', 'woo-order-splitter')
					) 
				) 
			);
		}
	
		if (is_object($post) && $post->post_type==$wc_os_get_post_type_default && wc_os_order_split($post->ID)) {
		
			$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $post->ID );
			
			$copy_link = wp_nonce_url( add_query_arg( array( 'split' => 'init', 'split-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
			
			$actions = array_merge( $actions, 
				array(
					'split' => sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $copy_link ), 
						__( 'Split', 'woo-order-splitter' )
					) 
				) 
			);
		}
		if (is_object($post) && $post->post_type=='product' && $post->post_author>0 && !isset($_GET['vendor'])) {
		
			$url = admin_url().'user-edit.php?user_id='. $post->post_author;
			
			$display_name = wos_get_author_name($post->post_author);
			
			$copy_link = wp_nonce_url( add_query_arg( array(), $url ), 'edit_order_nonce' );
			
			$actions = array_merge( $actions, 
				array(
					'vendor_url' => sprintf( '<a href="%1$s" target="_blank">%2$s</a>',
						esc_url( $copy_link ), 
						$display_name
					) 
				) 
			);
			
		}		
		
		if (is_object($post) && $post->post_type=='shop_subscription' && $subscription_split) {
		
			$url = admin_url( 'post.php?post='.$post->ID.'&action=edit&subscription_id='.$post->ID);
			
			$subscription = wcs_get_subscription($post->ID);
			
			if(count($subscription->get_items())>1){
			
			
				
				$copy_link = wp_nonce_url( add_query_arg( array( 'split' => 'init', 'split-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
				
				$actions = array_merge( $actions, 
					array(
						'split' => sprintf( '<a href="%1$s">%2$s</a>',
							esc_url( $copy_link ), 
							__( 'Split', 'woo-order-splitter' )
						) 
					) 
				);
				//wc_os_pree($actions);
				
			}
		}		
		//wc_os_pree($actions);
		
		return $actions;
				
	}
	
	add_filter( 'post_row_actions', 'wc_os_links', 20, 2 );
	
	function wos_get_author_name($vendor_id=0){
		$display_name = '';
		if($vendor_id>0){
			$vendor_data = get_user_by( 'ID', $vendor_id );
			if(!empty($vendor_data) && is_object($vendor_data)){
				$full_name = trim($vendor_data->first_name.' '.$vendor_data->last_name);
				$display_name = trim($vendor_data->display_name?$vendor_data->user_login:$vendor_data->user_login).($full_name?' ('.$full_name.')':'');
				$display_name = ($display_name?$display_name:$vendor_data->user_email);		
			}
		}
		return $display_name;
	}
		
	function sv_wc_add_order_meta_box_action( $actions ) {
		global $theorder, $wc_os_settings;
		
		$get_post_meta = wc_os_get_order_meta($theorder);
		$get_post_meta = (is_array($get_post_meta)?$get_post_meta:array());

		if ( array_key_exists('split_status', $get_post_meta) || array_key_exists('splitted_from', $get_post_meta)) {
			return $actions;
		}
	
	
		// add "mark printed" custom action
		
		if(wc_os_order_split()){
			$actions['wc_os_split_action'] = __( 'Split Order', 'woo-order-splitter' );
		}
		
		return $actions;
	}
	add_action( 'woocommerce_order_actions', 'sv_wc_add_order_meta_box_action' );	
		
	function sv_wc_process_order_meta_box_action( $order ) {

		
		wc_os_checkout_order_processed($order->get_order_number());
		
	}
	
	//add_action( 'woocommerce_order_action_wc_custom_order_action', 'sv_wc_process_order_meta_box_action' );
	function wc_order_total_qty($order){
		$qty = 0;		
		foreach($order->get_items() as $item_id=>$item_data){
		
			$qty += $item_data->get_quantity();
			
		}
		
		return $qty;
	}
	
	
		
	add_filter( 'woocommerce_admin_order_actions', 'add_wc_os_order_status_actions_button', 100, 2 );
	function add_wc_os_order_status_actions_button( $actions, $order ) {
		// Display the button for all orders that have a 'processing' status
		global $wc_os_settings, $wc_os_woocommerce_shipping_multiple_addresses;
	
		$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $order->get_id() );

		$conflict_status = wc_os_get_order_meta($order, 'conflict_status', true);
		//pree($conflict_status);
		
		$split_lock = (isset($wc_os_settings['wc_os_additional']['split_lock'])?$wc_os_settings['wc_os_additional']['split_lock']:array());
		$split_lock = is_array($split_lock)?$split_lock:array();
		
		$group_by_vendors = (isset($wc_os_settings['wc_os_ie']) && $wc_os_settings['wc_os_ie']=='group_by_vendors');
		
		//$status_arr = array( 'processing', 'on-hold', 'completed', 'pending' );
		//$status_arr[] = str_replace('wc-', '', $split_lock);
		//$status_arr = array_unique($status_arr);
		
		$wc_os_order_statuses = wc_get_order_statuses(); 
		$wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);		
		$wc_os_order_statuses_keys = array_unique($wc_os_order_statuses_keys);
		

		$status_lock_released = empty($split_lock);
	
		if(!empty($split_lock)){
			foreach($split_lock as $split_lock_i){				
				if(!$status_lock_released){
					$status_lock_released = $order->has_status($split_lock_i);
				}
			}
		}
		

		if ( $status_lock_released && wc_os_order_split($order->get_id()) ) {
			
			$get_post_meta = wc_os_get_order_meta($order);
			
			//pree($get_post_meta);
			
			$order_total_qty = wc_order_total_qty($order);

			
			$is_bundle = false;
			$vendors_arr = array();
			
			if(count($order->get_items())>0){			
				foreach($order->get_items() as $item_id=>$item_values){
					$pid = $item_values->get_product_id();
					
					if(count($order->get_items())==1){
	
						$vid = $item_values->get_variation_id();
						$product_item = $vid?$vid:$pid;				
						$product = wc_get_product($pid);
						
	
						
						if(is_object($product) && method_exists($product, 'get_type')){
							$is_bundle = ($product->get_type()=='bundle');
						}
					}
				
					if($wc_os_settings['wc_os_ie']){
						$product_post = get_post($pid);
						$vendors_arr[] = $product_post->post_author;
					}
				}
				$vendors_arr = array_unique($vendors_arr);
			}
			
			
		
			$methods_based_condition = true;
			
			switch($wc_os_settings['wc_os_ie']){
				case 'subscription_split':
					$methods_based_condition = (
						(count($order->get_items())>=1 || $order_total_qty>1) //30/04/2021
					);
				break;
				case 'group_by_vendors':
					$methods_based_condition = (count($order->get_items())>1 && $group_by_vendors && count($vendors_arr)>1); //20/08/2020
				break;
				case 'io':
					$methods_based_condition = (
						(count($order->get_items())>=1) //17/03/2021
					);
				break;
				case 'group_cats':
					
					
					if($wc_os_woocommerce_shipping_multiple_addresses){
						$_multiple_shipping = wc_os_get_order_meta($order, '_multiple_shipping', true);
						
						$methods_based_condition = (
							(count($order->get_items())>0 && $order_total_qty>1 && $_multiple_shipping) //01/12/2022 - Hybrid split case written for Leslie
						);
					}
					
				break;
				default:
				
					$methods_based_condition = (
						(count($order->get_items())>1 || (count($order->get_items())==1 && $is_bundle))
					|| 
						($order_total_qty>1 && $wc_os_settings['wc_os_ie']=='quantity_split') //21/05/2019
					);
				break;
			}
			
			
			
			if(!array_key_exists('split_status', $get_post_meta) && !array_key_exists('splitted_from', $get_post_meta) && $methods_based_condition){
	
				
				
				$copy_link = wp_nonce_url( add_query_arg( array( 'split' => 'init', 'split-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
				
				// Get Order ID (compatibility all WC versions)
				$order_id = $order->get_order_number();
				// Set the action button
				$actions['split_order'] = array(
					'url'       => $copy_link,
					'name'      => __( 'Split Order', 'woo-order-splitter' ),
					'action'    => "wc_os_split", 
				);
				
			}
		}
		
		if(wc_os_order_cloning()){
				
			$copy_link = wp_nonce_url( add_query_arg( array( 'clone' => 'yes', 'clone-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );				
			$actions['clone_order'] = array(
				'url'       => $copy_link,
				'name'      => __( 'Clone Order', 'woo-order-splitter' ),
				'action'    => "wc_os_clone", 
			);	
		}
		//pree($conflict_status);exit;
		if($conflict_status){			

			$actions['clone_order'] = array(
				'url'       => $order->get_edit_order_url(),
				'name'      => __( 'Conflict Alert!', 'woo-order-splitter' ),
				'action'    => "wc_os_conflict", 
			);	
			
		}
		
		
		return $actions;
	}	
	
	function wos_clone_order_notes($original_order_id=0, $order_id=array()){
	
		global $wc_os_general_settings;
		
		$wc_os_customer_notes = array_key_exists('wc_os_customer_notes', $wc_os_general_settings);
		
		if(!$wc_os_customer_notes)
		return;
		
		if($original_order_id && ((is_numeric($order_id) && $original_order_id != $order_id) || (is_array($order_id) && !in_array($original_order_id, $order_id)))){
			
			$order_id = (is_numeric($order_id)?array($order_id):$order_id);

			global $wpdb;		
			$comments_query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID=$original_order_id ORDER BY comment_ID DESC LIMIT 1";

			$original_order = wc_get_order($original_order_id);
			$comments_results = $wpdb->get_results($comments_query);
			//$orginal_order_post = get_posts(array('include'=>$original_order_id, 'post_type'=>'shop_order', 'post_status'=>'any'));
			//$orginal_order_post = (!empty($orginal_order_post)?current($orginal_order_post):'');

			if(!empty($comments_results)) {

                foreach ($comments_results as $comments) {


                    unset($comments->comment_ID);
                    if (!empty($order_id)) {
                        foreach ($order_id as $new_order_id) {

                            if (is_array($comments)) {

                                $comments['comment_post_ID'] = $new_order_id;

                            } elseif(isset($comments->comment_post_ID)) {
                                $comments->comment_post_ID;
                            }
                            $comments = (array)$comments;

                            if (wp_insert_comment($comments)) {

                            } else {

                            }

                        }
                    }
                }
            }

			if(!empty($order_id) && !empty($original_order) && $original_order->get_customer_note()!=''){					
				foreach($order_id as $new_order_id){
					$my_order = array(
						  'ID'           => $new_order_id,
						  'post_excerpt' => $original_order->get_customer_note()
					  );
					wp_update_post( $my_order );
				}
			}

		}
	}	
	
	function wc_os_random_color_part() {
		return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	}
	
	function wc_os_random_color() {
		return wc_os_random_color_part() . wc_os_random_color_part() . wc_os_random_color_part();
	}	
	
	function wos_troubleshooting(){
		extract($_POST);
		$ret = array();
		$hex = '#'.wc_os_random_color();
		$ret['color_hex'] = $hex;
		list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
		$ret['color'] = array('r'=>$r, 'g'=>$g, 'b'=>$b);
		$order_data = new WC_Order($order_id);
		
		$order_data->calculate_totals();
		
		$get_items = $order_data->get_items();
		$shipping_items = $order_data->get_items('shipping');
		
		
		$meta = wc_os_get_order_meta($order_id);
		$wc_os_meta_keys = get_option('wc_os_meta_keys', array());
		
		$cloned_from = (isset($meta['cloned_from'])?$meta['cloned_from'][0]:false);
		$splitted_from = (isset($meta['splitted_from'])?$meta['splitted_from'][0]:false);
		
		$cloned_order = wc_get_order($cloned_from);
		$splitted_order = wc_get_order($splitted_from);
		
		$wc_os_meta_data = array(
			'parent_order' => (!$cloned_from && !$splitted_from)?'Yes':'No',
			'cloned_from' => $cloned_from?'<a target="_blank" href="'.(is_object($cloned_order)?$cloned_order->get_edit_order_url():'').'">'.$cloned_from.'</a>':'-',
			'splitted_from' => $splitted_from?'<a target="_blank" href="'.(is_object($splitted_order)?$splitted_order->get_edit_order_url():'').'">'.$splitted_from.'</a>':'-',
		);
				
		ob_start();
		
		foreach($get_items as $item_id=>$item_data){



			


		}
		
		wc_os_pree($wc_os_meta_data);
		
		wc_os_pree($order_data);
		
		wc_os_pree($meta);		
		
		wc_os_pree($get_items);		
		
		wc_os_pree($shipping_items);
		
		$ret['html'] = ob_get_contents();
		ob_end_clean();
		
		
		$ret = json_encode($ret);
		echo $ret;
		exit;
	}
	
	add_action( 'wp_ajax_wos_troubleshooting', 'wos_troubleshooting' );
	
	
	function wos_auto_settings(){
		
		global $wc_os_settings;
		$wc_os_products = $wc_os_settings['wc_os_products'];
		$ret = $selected_items = array();
		
		$wc_os_ie = sanitize_wc_os_data($_POST['wc_os_ie']);
		
		
		switch($wc_os_ie){
			default:
				if(array_key_exists($wc_os_ie, $wc_os_products)){
					$selected_items = $wc_os_products[$wc_os_ie];
					unset($wc_os_products[$wc_os_ie]);
				}
				
				$ret = array($selected_items, $wc_os_products);
				
			break;
			
			case 'cats':
				
				$ret = $wc_os_settings['wc_os_cats']['cats'];
				
			break;
			
			
			case 'group_cats':
				
				$ret = array_key_exists('group_cats', $wc_os_settings['wc_os_cats'])?$wc_os_settings['wc_os_cats']['group_cats']:array();

			break;
			
			
			case 'group_by_vendors':
				
				$ret = $wc_os_settings['wc_os_vendors'];
				
			break;		
            case 'group_by_woo_vendors':

                $ret = $wc_os_settings['wc_os_woo_vendors'];

			break;							
		}
				
		
		$ret = json_encode($ret);
		echo $ret;
		exit;
	}	
	
	add_action( 'wp_ajax_wos_auto_settings', 'wos_auto_settings' );
	
	function wos_forced_ie(){
		if(isset($_POST['order_action'])){
			$order_id = sanitize_wc_os_data($_POST['order_id']);
			$order_data = wc_get_order( $order_id );
			if(!empty($order_data)){
				wc_os_update_order_meta($order_id, 'wos_forced_ie', sanitize_wc_os_data($_POST['order_action']));

				wc_os_set_splitter_cron($order_id, true, 7859);			
				wc_os_delete_order_meta($order_id, 'conflict_status');
				wc_os_delete_order_meta($order_id, 'split_status');

			}
		}
		exit;
	}
	
	add_action( 'wp_ajax_wos_forced_ie', 'wos_forced_ie' );
	
	function wpdocs_filter_wp_title( $title, $sep ) {
		global $paged, $page;
	 
		if ( is_feed() )
		return $title;
	 
		// Add the site name.
		$title .= get_bloginfo( 'name' );
	 
		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			$title = "$title $sep $site_description";
	 
		// Add a page number if necessary.
		if ( $paged >= 2 || $page >= 2 )
			$title = "$title $sep " . __( 'Page', 'woo-order-splitter' ).' '.sprintf( '%s', max( $paged, $page ) );
	 
		return $title;
	}	
	
	add_filter( 'wp_title', 'wpdocs_filter_wp_title', 10, 2 );
	
	function wos_cart_notices($content=''){
		
		global $wos_notices_css;
		$notice_text = '';
		$content = (is_object($content)?'':$content);
		$wos_cart_notices = get_option( 'wc_os_cart_notices', true);
		$page_type = '';
		
		if(is_cart()){
			$notice_text = isset($wos_cart_notices['cart'])?$wos_cart_notices['cart']:'';
			$page_type = 'cart';
		}if(is_checkout()){
			$notice_text = isset($wos_cart_notices['checkout'])?$wos_cart_notices['checkout']:'';
			$page_type = 'checkout';
		}if(is_product()){
			$notice_text = isset($wos_cart_notices['product'])?$wos_cart_notices['product']:'';
			$page_type = 'product';
		}if(is_shop()){
			$notice_text = isset($wos_cart_notices['shop'])?$wos_cart_notices['shop']:'';
			$page_type = 'shop';
		}
		
		$wos_notices_css = ((isset($wos_cart_notices['styles']) && $wos_cart_notices['styles']!='')?$wos_cart_notices['styles']:$wos_notices_css);
		
		if($notice_text){
			$notice_text = '<style type="text/css">							
							'.$wos_notices_css.'
							</style><div class="wos_notice_div wos_'.$page_type.'">'.$notice_text.'</div>';
				
		}
		
		if(is_product()){
			return $notice_text.$content;
		}elseif(is_shop()){
			return $notice_text.$content;
		}else{
			return $content;
		}
	}
		
	add_filter('the_content', 'wos_cart_notices', 20);	
	
	add_filter('woocommerce_before_main_content', 'wos_cart_notices', 5);
	
	add_filter('woocommerce_before_single_product', 'wos_cart_notices', 10);
	
	function wos_woocommerce_can_reduce_order_stock_inner($order_id=0){
		$wos_processed_order = true;
		
		$post_meta = wc_os_get_order_meta($order_id);			
		if(!empty($post_meta)){			
			$post_meta = array_keys($post_meta);
			$wos_processed_order = (			
										in_array('_wc_os_parent_order', $post_meta)
										|| 
										in_array('_wc_os_child_order', $post_meta) 
										||
										in_array('cloned_from', $post_meta) 
										|| 
										in_array('splitted_from', $post_meta) 
										|| 
										in_array('split_status', $post_meta)
									);
		}
		
		return $wos_processed_order;
	}
	
	function wos_filter_woocommerce_can_reduce_order_stock( $return, $order ) { 
		
		global $wc_os_general_settings;
		
		$order_id = $order->get_id();

		$wc_os_logger_str = 'Before: reduce_order_stock #'.$order_id.' '.($return?'YES':'NO');
		
		
		if($order_id && !array_key_exists('wc_os_reduce_stock', $wc_os_general_settings)){
			$is_parent_or_child = wos_woocommerce_can_reduce_order_stock_inner($order_id);		
			if($is_parent_or_child){
				$return = false;
			}
		
		}

		$wc_os_logger_str .= ' ~ After: reduce_order_stock #'.$order_id.' '.($return?'YES':'NO');
	
		$debug_backtrace = debug_backtrace();
		
		$function = $debug_backtrace[0]['function'];
		$function .= ' / '.$debug_backtrace[1]['function'];
		$function .= ' / '.$debug_backtrace[2]['function'];
		$function .= ' / '.$debug_backtrace[3]['function'];
		$function .= ' / '.$debug_backtrace[4]['function'];
				
		
		
				
		return $return; 
	}
	
	
	add_action('wp_ajax_wos_quick_split', 'wos_quick_split');
	
	if(!function_exists('wos_quick_split')){
		function wos_quick_split(){
			$url = parse_url($_POST['url']);
			parse_str($url['query'], $query_string);
			
			global $wc_os_order_splitter, $wc_os_general_settings, $wc_os_shipping_cost;
			$ret = array();
			
			$child_orders_by_order_id = wc_os_child_orders_by_order_id($query_string['order_id'], true);
			
			if($wc_os_shipping_cost){

				if(!empty($child_orders_by_order_id)){
					foreach($child_orders_by_order_id as $child_order_id){
						$_wc_os_set_status = wc_os_get_order_meta($child_order_id, '_wc_os_set_status', true);
						
						if($_wc_os_set_status){
							$child_order = wc_get_order($child_order_id);
							$child_order->set_status(wc_os_add_prefix($_wc_os_set_status, 'wc-'));
							$child_order->save();
							
							wc_os_delete_order_meta($child_order_id, '_wc_os_set_status');
						}
					}
				}
				
			}
			
			
			$wc_os_parent_order_email = !array_key_exists('wc_os_parent_order_email', $wc_os_general_settings);
		
			$wc_os_order_splitter->splitCheck($query_string['split'], $query_string['_wpnonce'], $query_string['order_id'], true);
			
			$crons_order_id = wc_os_crons($query_string['order_id']);
			
			//wc_os_pree($query_string['order_id']);wc_os_pree($crons_order_id);exit;
			
			$crons_return_child_orders = wc_os_child_orders_by_order_id($query_string['order_id'], true);
			
			//pree('$crons_return_child_orders');
			//pree($crons_return_child_orders);
			
			$ret['query_string_order_id'] = $query_string['order_id'];
			
			$ret['child_orders'] = $crons_return_child_orders;
			
			$ret['refresh'] = (count($ret['child_orders'])>0?true:false);
			
			$ret['order_id'] = (($crons_order_id || $ret['refresh'])?$ret['query_string_order_id']:0);
			
			//pree($ret);//exit;
			
			echo json_encode($ret);
			
			if($crons_order_id && $wc_os_parent_order_email){								
				wc_os_process_parent_email($query_string['order_id']);
			}
			exit;
		}
	}
	if(!function_exists('wc_os_trash_post')){
		function wc_os_trash_post($originalorderid=0){
			if($originalorderid){
				$has_child_orders = (count(wc_os_child_orders_by_order_id($originalorderid))>0);
				if($has_child_orders){
					wp_trash_post($originalorderid);
				}
			}
		}
	}	
	if(!function_exists('wc_os_force_trash_post')){
		function wc_os_force_trash_post($originalorderid=0){
			if($originalorderid){
				//wc_os_logger('debug', 'wc_os_force_trash_post: #'.$originalorderid, true);
				//wp_delete_post($originalorderid, true);
				wp_trash_post($originalorderid);		
				
				
				global $wc_os_custom_orders_table_enabled, $wpdb;
				
				if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders')){
					$trash_query = "UPDATE ".$wpdb->prefix."wc_orders SET status='trash' WHERE id=$originalorderid";
					wc_os_logger('debug', $trash_query, true);
					$wpdb->query($trash_query);
				}
			}
		}
	}										


	
	if(!function_exists('wc_os_chosen_shipping_methods')){
		function wc_os_chosen_shipping_methods(){
				
			$chosen_methods = wc_os_get_session( 'chosen_shipping_methods' );
			$chosen_shipping = !empty($chosen_methods)?current($chosen_methods):''; 
			
			echo $chosen_shipping;exit;
			
		}
	}


	add_action('wp_ajax_wc_os_customer_permitted_method', 'wc_os_customer_permitted_method');
	add_action('wp_ajax_nopriv_wc_os_customer_permitted_method', 'wc_os_customer_permitted_method');


    if(!function_exists('wc_os_customer_permitted_method')){
        function wc_os_customer_permitted_method(){
			
				$resp = array();

                if(isset($_POST['wc_os_customer_permitted'])){

                    if (
                        ! isset( $_POST['wc_os_customer_permitted_nonce'] )
                        || ! wp_verify_nonce( $_POST['wc_os_customer_permitted_nonce'], 'wc_os_customer_permitted_action' )
                    ) {

                        _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                        exit;

                    } else {


                        $wc_os_customer_permitted = sanitize_wc_os_data($_POST['wc_os_customer_permitted']);

                        WC()->session->set( 'wc_os_customer_permitted', $wc_os_customer_permitted );
						

						if($wc_os_customer_permitted=='on'){
							$fee = (float)wc_os_get_session('wc_os_calculated_shipping_cost');
							
							$resp['total'] = wc_price(WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax() + ($fee));
						}else{
							$fee = 0;							
							$resp['total'] = '';
						}
						$resp['fee'] = wc_price($fee);
						
						
						

                    }

                }
				
				echo json_encode($resp);

                wp_die();

        }
    }
	
	add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'wc_os_item_get_formatted_meta_data', 10, 1 );
	
	function wc_os_item_get_formatted_meta_data($formatted_meta){
		
		$formatted_meta = ((isset($_GET['refreshed']) && $_GET['refreshed']=='true')?array():$formatted_meta);
		
		return $formatted_meta;
	}	
	
    function wc_os_save_order_log($order){



         $order_id = $order->get_id();


        if($order instanceof  WC_Order){

            
			$order_log = wc_os_logger('order');

            $order_log_string = "Order# $order_id- | ";
            $order_items = $order->get_items();
            $order_item_string = array();

            if(!empty($order_items)){


                foreach($order_items as $item_id => $item){

                    $order_item_string[] = $item->get_name(). ' &times; '. $item->get_quantity();

                }
            }

            $order_total = wc_price($order->get_total());
            $order_log_string .= implode(', ', $order_item_string);
            $order_log_string .= " | $order_total";


            $order_log[$order_id] = $order_log_string;
			
			


        }



    }

    add_action('wp_ajax_wc_os_clear_order_log', 'wc_os_clear_order_log');

    if(!function_exists('wc_os_clear_order_log')){
        function wc_os_clear_order_log($force=false){

            if((!empty($_POST) && isset($_POST['wc_os_clear_order_log'])) || $force){

                if (
					!$force
					&&
					(
						! isset( $_POST['wc_os_clear_email_log_field'] )
						|| ! wp_verify_nonce( $_POST['wc_os_clear_email_log_field'], 'wc_os_clear_log_nonce' )
					)
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {


                    if($force || $_POST['wc_os_clear_order_log']){

                        delete_option('wc_os_remove_order_log');
						_e('Order log removed!', 'woo-order-splitter');
						

                    }

                }
            }

            wp_die();
        }
    }
	
	add_action('wp_ajax_wc_os_email_log', 'wc_os_email_log');
	
    if(!function_exists('wc_os_email_log')){
        function wc_os_email_log(){

            if(!empty($_POST) && isset($_POST['wc_os_email_log'])){

                if (
                    ! isset( $_POST['wc_os_clear_email_log_field'] )
                    || ! wp_verify_nonce( $_POST['wc_os_clear_email_log_field'], 'wc_os_clear_log_nonce' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {

					
                    if($_POST['wc_os_email_log']){
						
						if($_POST['wc_os_email_log']=='no'){
	                        update_option('wc_os_email_log', true);
						}else{
							delete_option('wc_os_email_log');
						}

                    }

                }
            }

            wp_die();
        }
    }	
	
	
    add_action('wp_ajax_wc_os_clear_email_log', 'wc_os_clear_email_log');
	
    if(!function_exists('wc_os_clear_email_log')){
        function wc_os_clear_email_log(){

	        if(!empty($_POST) && isset($_POST['wc_os_clear_email_log'])){

		        if (
			        ! isset( $_POST['wc_os_clear_email_log_field'] )
			        || ! wp_verify_nonce( $_POST['wc_os_clear_email_log_field'], 'wc_os_clear_log_nonce' )
		        ) {

			        _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
			        exit;

		        } else {

						_e('Email log removed!', 'woo-order-splitter');
                        update_option('wc_os_logger', array());

                }
	        }

	        wp_die();
        }
    }   
		
	add_action('wp_ajax_wc_os_debug_log', 'wc_os_debug_log');
	
    if(!function_exists('wc_os_debug_log')){
        function wc_os_debug_log(){

            if(
					!empty($_POST) 
				&& 
					(
							isset($_POST['wc_os_debug_log'])
						||
							isset($_POST['wc_os_clear_debug_log'])
					)
			){
				


                if (
                    ! isset( $_POST['wc_os_debug_log_field'] )
                    || ! wp_verify_nonce( $_POST['wc_os_debug_log_field'], 'wc_os_clear_log_nonce' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {
					
					
                    if(array_key_exists('wc_os_debug_log', $_POST)){
						
						if($_POST['wc_os_debug_log']=='no'){
	                        update_option('wc_os_debug_log', true);
						}else{
							delete_option('wc_os_debug_log');
						}

                    }
					 if(array_key_exists('wc_os_clear_debug_log', $_POST)){
						update_option('wc_os_debug_logger', array());
					}

                }
            }

            wp_die();
        }
    }		
	
	add_action('wp_ajax_wc_os_order_log', 'wc_os_order_log');
	
    if(!function_exists('wc_os_order_log')){
        function wc_os_order_log(){

            if(!empty($_POST) && isset($_POST['wc_os_order_log'])){

                if (
                    ! isset( $_POST['wc_os_clear_email_log_field'] )
                    || ! wp_verify_nonce( $_POST['wc_os_clear_email_log_field'], 'wc_os_clear_log_nonce' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {

                    if($_POST['wc_os_order_log']){
						
						if($_POST['wc_os_order_log']=='no'){
	                        update_option('wc_os_order_log', true);
						}else{
							delete_option('wc_os_order_log');
						}

                    }

                }
            }

            wp_die();
        }
    }		

    // this function used for generate select and video html for quantity split method
    if(!function_exists('wc_os_get_quantity_split_extra')){
        function wc_os_get_quantity_split_extra(){
            global $wc_os_settings;
            ?>

            <div class="qty_split_options">

                <a title="<?php _e('Default (All qty. to x1) - Video Tutorial', 'woo-order-splitter'); ?>" class="split_default" href="https://www.youtube.com/embed/vspbAX8Krx4" target="_blank"><i class="fab fa-youtube"></i></a> &nbsp; <a title="<?php _e('Custom (Split only defined values) - Video Tutorial', 'woo-order-splitter'); ?>" class="split_custom" href="https://www.youtube.com/embed/7m05fzIQlqY" target="_blank"><i class="fab fa-youtube"></i></a>&nbsp; <a title="<?php _e('Eric (Selected + Defined values) - Video Tutorial', 'woo-order-splitter'); ?>" class="split_eric_logic" href="https://www.youtube.com/embed/KSl_5VC1PPs" target="_blank"><i class="fab fa-youtube"></i></a>

                <select name="wc_os_settings[wc_os_qty_split_option]">

                    <option value="default" <?php selected($wc_os_settings['wc_os_qty_split_option']=='default'); ?>><?php _e('Default (All qty. to x1)', 'woo-order-splitter'); ?></option>

                    <option value="custom" <?php selected($wc_os_settings['wc_os_qty_split_option']=='custom'); ?>><?php _e('Custom (Split only defined values)', 'woo-order-splitter'); ?></option>

                    <option value="eric_logic" <?php selected($wc_os_settings['wc_os_qty_split_option']=='eric_logic'); ?>><?php _e('Eric Logic (Selected+Defined items/quantity into new order)', 'woo-order-splitter'); ?></option>

                </select>


            </div>


            <?php

        }
    }

    // this function generate youtube video url section used inside ie html
    if(!function_exists('wc_os_get_video_url_section')){
        function wc_os_get_video_url_section($video_urls = array()){

            if(!empty($video_urls)):
            ?>

                <div class="qty_split_options">

                    <?php

                        foreach($video_urls as $url_data):
								list($url_title, $url) = $url_data;
								$url_title = ($url_title?$url_title:__('Video Tutorial', 'woo-order-splitter'));
								echo ($url?'<a class="split_default" title="'.$url_title.'" href="'.$url.'" target="_blank"><i class="fab fa-youtube"></i></a>':'');

                        endforeach;


                    ?>

                </div>

            <?php

            endif;
        }
    }

    // this function generate html for methods from array
    if(!function_exists('wc_os_get_ie_html')){
        function wc_os_get_ie_html(){

            global $wc_os_ie_arr, $wc_os_pro;

            ?>

            <ul class="wc_os_ie_wrapper">

                <?php

                        if(!empty($wc_os_ie_arr)){
                            foreach($wc_os_ie_arr as $id => $items_arr){


                                echo "<li class='$id'>";

                                    if($id == 'wc_os_ie_products'){

                                        ?>

                                            <label for="wc_os_ie_products"><input type="radio" value="default" id="wc_os_ie_products" name="wc_os_settings[wc_os_ie]" <?php //checked($wc_os_settings['wc_os_ie']=='default'); ?> /><?php echo __('Product Based', 'woo-order-splitter'); ?> <i>(<span class="i-default"><?php echo split_actions_display('default', 'title'); ?></span> | <span class="i-exclusive"><?php echo split_actions_display('exclusive', 'title'); ?></span> | <span class="i-inclusive"><?php echo split_actions_display('inclusive', 'title'); ?></span> | <span class="i-shredder"><?php echo split_actions_display('shredder', 'title'); ?></span> | <span class="i-io"><?php echo split_actions_display('io', 'title'); ?></span> | <span class="i-quantity_split"><?php echo split_actions_display('quantity_split', 'title'); ?></span>)</i> </label><div class="qty_split_options"><a title="<?php _e('Video Tutorial', 'woo-order-splitter'); ?>" class="split_default" href="https://www.youtube.com/embed/tOT4l7_GCIw" target="_blank"><i class="fab fa-youtube"></i></a></div>

                                        <?php

                                        wc_os_get_video_url_section(array(array(__('Video Tutorial', 'woo-order-splitter'),'https://www.youtube.com/embed/tOT4l7_GCIw')));

                                    }


                                ?>


                                <ul>
                                    <?php

                                    if(!empty($items_arr)):

                                        foreach($items_arr as $ie => $ie_data):

                                            $id = $ie_data['id'];
                                            $type = (array_key_exists('type', $ie_data) ? $ie_data['type'] : '');
											$classes = (array_key_exists('class', $ie_data) ? $ie_data['class'] : '');
                                            $videos = (array_key_exists('video', $ie_data) && is_array($ie_data['video'])? $ie_data['video'] : array());
                                            $image = (array_key_exists('img', $ie_data) && $ie_data['img']? $ie_data['img'] : '');
                                            $id_class = ($classes ? $id.' '.$classes : $id);
                                            $title = split_actions_display($ie, 'title');
                                            $description = split_actions_display($ie, 'description');
                                            $is_premium = (array_key_exists('premium', $ie_data) ? $ie_data['premium'] : false);
                                            $is_not_pro_item = ($is_premium && !$wc_os_pro);


                                            ?>

                                            <li data-type="<?php echo $type; ?>" class="<?php echo esc_attr($id_class); ?> <?php echo ($is_not_pro_item ? 'wc_os_premium' : ''); ?>">
                                                <label for="<?php echo esc_attr($id); ?>">
                                                    <input type="radio"  data-title="<?php echo esc_attr($title); ?>" value="<?php echo esc_attr($ie); ?>" id="<?php echo esc_attr($id); ?>" name="wc_os_settings[wc_os_ie]" />                                                    
                                                    <?php echo esc_html($title); ?> <i>(<?php echo esc_html($description); ?>)</i>
                                                </label>
                                                <?php

                                                    if(!empty($videos)){
														
                                                        wc_os_get_video_url_section($videos);
														
                                                    }

                                                    if($ie == 'quantity_split'){
                                                        wc_os_get_quantity_split_extra();
                                                    }
													
													
													if(!is_array($image)){
														$image = array($image);
													}
													
													if(!empty($image)){
														$counter = 0;
														foreach($image as $screenshot){ 
															echo ($screenshot?'<a style="right:'.($counter*16).'px;" class="wos-va" href="//ps.w.org/woo-order-splitter/assets/'.$screenshot.'" target="_blank"></a>':'');
															$counter++;
														}
													}
												?>
                                                <?php do_action('wc_os_ie_options_'.$ie, $ie); ?>
                                            </li>

                                    <?php
                                        endforeach;
                                    endif;
                                    ?>


                                </ul>

                                <?php


                                echo '</li>';

                            }
                        }

                ?>

            </ul>

            <?php



        }
    }

    //this function return single array of all available methods
    if(!function_exists('wc_os_get_ie_methods_list')){
        function wc_os_get_ie_methods_list(){

            global $wc_os_ie_arr;

            $ie_list = array();
            if(!empty($wc_os_ie_arr)){
                foreach($wc_os_ie_arr as $ie_group_key => $ie_array){
                    if(!empty($ie_array)){

                        $ie_list = array_merge($ie_list, $ie_array);
                    }
                }
            }

            return $ie_list;
        }
    }

    // this function get all the groups of acf fields and generate checkboxes according to these groups
    if(!function_exists('wc_os_acf_group_fields_screen_options')){
        function wc_os_acf_group_fields_screen_options($method_checked){

            global $is_acf, $wos_acf_string;
            $group_args = array(

                'numberposts' => -1,
                'post_status' => 'any',
                'post_type' => 'acf-field-group',

            );
            $acf_groups = ($is_acf? get_posts($group_args): array());
            $wc_os_acf_group_selection = get_option('wc_os_acf_group_selection', array());

            ?>

              <div class="wc_os_acf_group_selection" <?php echo $method_checked ? '' : 'style="display: none;"' ?>>



                <?php
                if(!empty($acf_groups)){

                    ?>

                    <h5>
                        <?php _e('Select ACF Groups to show fields', 'woo-order-splitter') ?>
                    </h5>

                    <?php

                    foreach($acf_groups as $group){

                        ?>

                        <label>
                            <input class="group_input" name="acf_group_selection" type="checkbox" value="<?php echo esc_attr($group->ID); ?>" <?php echo checked(in_array($group->ID, $wc_os_acf_group_selection)); ?>>
                            <?php echo esc_html($group->post_title); ?>
                        </label>

                        <?php

                    }


                }else {

                    if($is_acf):

                        ?>

                        <div>
                            <?php _e('No ACF group found ', 'woo-order-splitter') ?>
                            <a target="_blank" href="<?php echo admin_url('post-new.php?post_type=acf-field-group'); ?>"><?php _e('Click here', 'woo-order-splitter') ?> </a> <?php _e('to create new ACF group', 'woo-order-splitter')?>
                        </div>

                    <?php

                    else:

                        echo $wos_acf_string;
                    endif;

                }

                ?>

            </div>


            <?php

        }
    }

    //generate screen options wrapper and button for toggle
    if(!function_exists('wc_os_method_screen_option_html')){
        function wc_os_method_screen_option_html(){

            global $wc_os_url;


            $ie_method_list = wc_os_get_ie_methods_list();
            $wc_os_ie_method_selection = get_option('wc_os_ie_method_selection', false);

            if($wc_os_ie_method_selection == false){

                $wc_os_ie_method_selection = array_keys($ie_method_list);

                // update with all method on first time load when option is not set
                update_option('wc_os_ie_method_selection', $wc_os_ie_method_selection);
            }


            ?>

            <div class="wc_os_screen_option" style="display:none">

                <div id="screen-meta">
                    <div id="screen-options-wrap"  tabindex="-1" aria-label="Screen Options Tab" style="display: block;">



                        <fieldset>

                            <legend>
                                <?php _e('Split Settings (ON/OFF)', 'woo-order-splitter') ?>
                            </legend>

                            <?php

                            if(!empty($ie_method_list)){


                                foreach($ie_method_list as $method_key => $method){

                                    $method_title = split_actions_display($method_key, 'title');
                                    $method_checked = in_array($method_key, $wc_os_ie_method_selection);

                                    $method_id = $method['id'];
                                    ?>

                                    <label>
                                        <input class="group_input" name="method_selection" type="checkbox" value="<?php echo esc_attr($method_key); ?>" data-target="<?php echo esc_attr($method_id); ?>" <?php echo checked($method_checked); ?>>
                                        <?php echo esc_html($method_title); ?>
                                    </label>



                                    <?php

                                        if($method_key == 'group_by_acf_group_fields' && function_exists('wc_os_acf_group_fields_screen_options')){
                                            wc_os_acf_group_fields_screen_options($method_checked);
                                        }

                                }

                            }

                            ?>

                            <p class="submit">
                                <button id="wos_save_acf_groups" class="button button-primary">
                                    <?php _e('Save Changes', 'woo-order-splitter') ?>
                                </button>
                                <img src="<?php echo esc_url($wc_os_url.'/img/juggler.gif'); ?>" class="wc_os_loading">
                                <span class="dashicons dashicons-yes-alt result_success"></span>

                            </p>

                        </fieldset>

                    </div>
                </div>

                <div id="screen-meta-links">
                    <div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle" style="">
                        <button type="button" id="show-settings-link" class="button show-settings screen-meta-active" aria-controls="screen-options-wrap" aria-expanded="true"><?php _e('Screen Options', 'woo-order-splitter'); ?></button>
                    </div>
                </div>

            </div>

            <?php
        }
    }

    // save screen options for method selection and acf group selection with ajax request
    add_action('wp_ajax_wc_os_save_ie_method_selection', 'wc_os_save_ie_method_selection');
    if(!function_exists('wc_os_save_ie_method_selection')){


        function wc_os_save_ie_method_selection(){

            $result = array('status' => false);
            if(!empty($_POST) && isset($_POST['wc_os_ie_method_selection'])){


                if(!isset($_POST['wc_os_nonce']) || !wp_verify_nonce($_POST['wc_os_nonce'], 'wc_os_nonce_action')){

                    wp_die(__('Sorry, your nonce did not verify.', 'woo-order-splitter'));

                }else{

                    //your code here
                    $wc_os_ie_method_selection = sanitize_wc_os_data($_POST['wc_os_ie_method_selection']);
                    $method_update = update_option('wc_os_ie_method_selection', $wc_os_ie_method_selection);

                    $group_update = false;
                    if(isset($_POST['wc_os_acf_group_selection'])){

                        $wc_os_acf_group_selection = sanitize_wc_os_data($_POST['wc_os_acf_group_selection']);
                        $group_update = update_option('wc_os_acf_group_selection', $wc_os_acf_group_selection);

                    }

                    $result['status'] = ($method_update || $group_update);

                }

            }
            wp_send_json($result);
        }
    }
	
	

    if(!function_exists('wc_os_get_packages_strings')){
        function wc_os_get_packages_strings($type=''){
            global $wc_os_settings;
            $wos_packages_strings = get_option( 'wc_os_packages_strings', true);			
			$wos_packages_strings = (is_array($wos_packages_strings)?$wos_packages_strings:array());
            $_heading = '';
            switch($wc_os_settings['wc_os_ie']){
                default:
                    break;
                case 'io':
                    if($type){
                        $_heading = trim(wc_os_get_io_setting($type.'_heading'));
                    }
                break;
            }

            if($_heading!=''){
                $wos_packages_strings['parcel-heading'] = $_heading;
            }
           
            return $wos_packages_strings;
        }
    }

    if(!function_exists('wc_os_inclusive_method_options')){
        function wc_os_inclusive_method_options(){
            global $wc_os_pro, $wc_os_settings;
            $wc_os_order_statuses = wc_get_order_statuses();
            $wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);
            $method = 'inclusive';


            if($wc_os_pro){
                ?>

                    <div class="wc_os_io_options" data-method="inclusive" <?php echo ($wc_os_settings['wc_os_ie']==$method?'':'style="display:none;"'); ?>>

                        <div class="vendor-left ">
                            <div class="wc_os_select_wrapper">
                                <p><?php _e('Inclusive parcel status after split', 'woo-order-splitter'); ?></p>
                                <?php if(function_exists('wc_os_get_statuses_select_html')){wc_os_method_statuses_select_html($method, $method, $wc_os_order_statuses_keys);} ?>
                            </div>
                            <div class="wc_os_select_wrapper">
                                <p><?php _e('Remaining parcel status after split', 'woo-order-splitter'); ?></p>
                                <?php if(function_exists('wc_os_get_statuses_select_html')){wc_os_method_statuses_select_html($method, 'remaining',  $wc_os_order_statuses_keys);} ?>
                            </div>
                        </div>

                    </div>

                <?php

            }

        }
    }

    if(!function_exists('wc_os_update_method_options')){
        function wc_os_update_method_options($posted_method_options){

            global $wc_os_method_options;

            if(!empty($wc_os_method_options)){
                foreach($wc_os_method_options as $method => $method_options){
                    if(array_key_exists($method, $posted_method_options)){
                        $wc_os_method_options[$method] = $posted_method_options[$method];
                        unset($posted_method_options[$method]);
                    }
                }
            }

            if(!empty($posted_method_options)){
                foreach($posted_method_options as $p_method => $p_method_options){
                    $wc_os_method_options[$p_method] = $p_method_options;
                }
            }

            update_option('wc_os_method_options', $wc_os_method_options);
            $wc_os_method_options = get_option('wc_os_method_options', array());

        }
    }

    if(!function_exists('wc_os_get_method_option')){
        function wc_os_get_method_option($method, $option_name = false, $default = ''){
 
                global $wc_os_method_options;
				$default = ($default?$default:wc_os_order_split_status_action());
				



				
                $current_method_option =  (array_key_exists($method, $wc_os_method_options) ? $wc_os_method_options[$method] : '');
			
				



                if($option_name === false){
                    return $current_method_option;
                }else{

                    $current_option =  ((is_array($current_method_option) && array_key_exists($option_name, $current_method_option) && $current_method_option[$option_name]!='') ? $current_method_option[$option_name] : $default);

                    return $current_option;
                }


        }
    }

    if(!function_exists('wc_os_method_statuses_select_html')){
        function wc_os_method_statuses_select_html($method, $field_prefix, $wc_os_order_statuses){

            $input_name = 'wc_os_method_options['.$method.']';

            $status_key = $field_prefix.'_status';
            $selected_status =  wc_os_get_method_option($method, $status_key);


            ?>

            <select name="<?php echo esc_attr($input_name.'['.$field_prefix.'_status]');?>">

                <option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

                <?php foreach($wc_os_order_statuses as $order_status){ ?>

                    <option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status==$selected_status); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

                <?php } ?>

            </select>

    <?php
        }
    }

    if(!function_exists('wc_os_update_method_order_status')){
        function wc_os_update_method_order_status($child_order, $original_order_id){
			
			
            global $wc_os_settings;
            $method = $wc_os_settings['wc_os_ie'];
			
            $allowed_method = array(
                    'inclusive',
					'io',
            );
			
			$method_status =  wc_os_get_method_option($method, $method.'_status');
            $remain_status =  wc_os_get_method_option($method, 'remaining_status');
			
			
			

            if(!in_array($method, $allowed_method)){
                return;
            }

           
			
			
            switch($method){
                case 'inclusive':

                    $child_status = $method_status;
                    $parent_status = $remain_status;

                break;
                case 'io':
                    
					$parent_status = wc_os_method_based_default_order_status('', $original_order_id=0);
					$child_status = false;
					
                break;				
                default:

                    $parent_status = $method_status;
                    $child_status = $remain_status;

                break;
            }


            if($parent_status){

                wc_os_update_order_status($original_order_id, $parent_status);
				wc_os_update_order_meta($original_order_id, '_wos_custom_status_update', $parent_status);
            }

            if($child_status){

                if(!empty($child_order)){
                    foreach($child_order as $child_order_id)
						switch($method){
							case 'io':
								$child_status = wc_os_method_based_default_order_status('', $child_order_id);
							break;
						}
						
                        wc_os_update_order_status($child_order_id, $child_status);
						wc_os_update_order_meta($child_order_id, '_wos_custom_status_update', $child_status);
                }elseif(is_numeric($child_order)){
					
					switch($method){
						case 'io':
							$child_status = wc_os_method_based_default_order_status('', $child_order);
						break;
					}
					
					wc_os_update_order_status($child_order, $child_status);
					wc_os_update_order_meta($child_order, '_wos_custom_status_update', $child_status);
				
				}

            }

        }
    }

    if(!function_exists('wc_os_get_groups_range')){
        function wc_os_get_groups_range(){
            global $wc_os_general_settings;
            $wc_os_limit_alphabets = (array_key_exists('wc_os_limit_alphabets', $wc_os_general_settings) && $wc_os_general_settings['wc_os_limit_alphabets'] ? $wc_os_general_settings['wc_os_limit_alphabets'] : 26);
            $start_alpha = 'A';
            $range_group = array();
            for($a = 1; $a <= $wc_os_limit_alphabets; $a++ ){
                $range_group[] = $start_alpha;
                if($start_alpha == 'ZZ'){
                    break;
                }else{
                    $start_alpha++;
                }

            }

            return $range_group;


        }
    }

    function wc_os_rule_based_switch(){
        wc_os_settings_refresh();
        global $wc_os_general_settings;
        return array_key_exists('wo_os_rule_switch', $wc_os_general_settings);
    }


    function wc_os_implement_rule_status($order_id){

        global $wc_os_pro;
		
		

        $return_value = false;

        if(!$order_id || !wc_os_rule_based_switch() || !$wc_os_pro){
             return $return_value;
        }
        $order = new WC_Order($order_id);
        $order_items = $order->get_items();

        if(count($order_items) > 1){
            return $return_value;
        }elseif($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
			$classObj = new wc_os_bulk_order_splitter;
			$return_value = $classObj->get_order_status_by_rule($order_id);
        }


        return $return_value;
    }	
	
		
	if(!function_exists('wc_os_set_empty_order_status')){
		function wc_os_set_empty_order_status($order_id){
	
			global $wc_os_settings;
	
			
			
	
			if($order_id){
	
				$order_obj = wc_get_order($order_id);
				$order_items = $order_obj->get_items();
	
				if(empty($order_items)){	
					$wc_os_additional = (array_key_exists('wc_os_additional', $wc_os_settings) ? $wc_os_settings['wc_os_additional'] :array());		
					$empty_order_status = (array_key_exists('empty_order_status', $wc_os_additional) ? $wc_os_additional['empty_order_status'] :'');
					
					if($empty_order_status){
						switch($empty_order_status){
							default:
								wc_os_update_order_status($order_id, $empty_order_status);	
							break;
							case 'trash':
								wc_os_logger('debug', $order_id.' ~ '.$empty_order_status, true);
								wp_trash_post($order_id);
							break;
						}
					}
				}else{
					wc_os_update_order_meta($order_id, '_not_empty', true);
				}
	
	
			}
	
		}
	}	
		
	if(!function_exists('wc_os_is_empty_order_status')){
		function wc_os_is_empty_order_status($order_id){
	
			global $wc_os_settings;
	
			$wc_os_additional = (array_key_exists('wc_os_additional', $wc_os_settings) ? $wc_os_settings['wc_os_additional'] :array());
			$empty_order_status = (array_key_exists('empty_order_status', $wc_os_additional) ? $wc_os_additional['empty_order_status'] :'');
			$order_obj = wc_get_order($order_id);
			$order_items = (is_object($order_obj)?$order_obj->get_items():array());
			return ($empty_order_status && empty($order_items));
	
		}
	}	
	
    if(!function_exists('wc_os_status_options_html')){

	    function wc_os_status_options_html($selected){

            $wc_os_order_statuses = wc_get_order_statuses();
            $wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);

            ?>

            <option value=""><?php _e('Default', 'woo-order-splitter'); ?></option>

            <?php foreach($wc_os_order_statuses_keys as $order_status){ ?>

                <option value="<?php echo esc_attr($order_status); ?>" <?php selected($order_status == $selected); ?>><?php echo __('Change', 'woo-order-splitter').' to '.str_replace('WC-', '', (strtoupper($order_status))); ?></option>

            <?php

            }
        }

    }

	if(!function_exists('wc_os_get_auto_clone_status')){
	    function wc_os_get_auto_clone_status(){
            global $wc_os_general_settings;
            $auto_clone_selected_status = (array_key_exists('wc_os_auto_clone_status', $wc_os_general_settings) && $wc_os_general_settings['wc_os_auto_clone_status'] ? $wc_os_general_settings['wc_os_auto_clone_status'] : '');
            return $auto_clone_selected_status;
        }
    }
	
	if(!function_exists('wc_os_apply_coupon_child_orders')){

        function wc_os_apply_coupon_child_orders($child_ids = array(), $original_order_id = 0){
	

            global $wc_os_settings;
			
			$coupon_option = get_option('wc_os_coupon_option');
			$coupon_option = trim($coupon_option?$coupon_option:'');
			
			
			
            if(empty($child_ids) || empty($original_order_id) || !class_exists('WC_Coupon') || $coupon_option==''){
                return;
            }
			
			
			
            $original_order = new WC_Order($original_order_id);
            $coupon_items = $original_order->get_items('coupon');
            $effected_parent_method = array(
                'default', 'exclusive', 'inclusive', 'shredder', 'io',
            );



            if(empty($coupon_items)){

                return;

            }else{
				
			
			}
			
			
			

            $wc_os_method = $wc_os_settings['wc_os_ie'];

            $actual_effect_parent = wc_os_get_order_meta($original_order_id, '_wc_os_effected_order', true);

            if($actual_effect_parent && in_array($wc_os_method, $effected_parent_method)){
                $child_ids[] = $original_order_id;
                $effected_order = new WC_Order($original_order_id);

                foreach($coupon_items as $c_item_id => $c_item){
					

                    wc_os_remove_coupon($effected_order, $c_item->get_code());
                    $effected_order->calculate_taxes();
                    $effected_order->calculate_totals( false );
                    $effected_order->save();
					
                }

            }
			

            foreach($coupon_items as $item_id => $coupon_item){

                $coupon_code = $coupon_item->get_code();
                $coupon_obj = new WC_Coupon($coupon_code);
                $coupon_type = $coupon_obj->get_discount_type('edit');

                if($coupon_type != 'fixed_cart'){
					
					

                    foreach($child_ids as $child_id){
						
						

                        $child_order_obj = new WC_Order($child_id);
                        $result = wc_os_apply_coupon($child_order_obj, wc_format_coupon_code( wp_unslash( $coupon_code ) ));
                        $child_order_obj->calculate_taxes();
                        $child_order_obj->calculate_totals( false );
                        $child_order_obj->save();

                    }

                }else{

					
					
                    $total_cart_discount = $coupon_item->get_discount();

					

                    $all_orders_total = 0;

                    foreach($child_ids as $child_id){

                        $child_order_obj = new WC_Order($child_id);
                        $result = wc_os_apply_coupon($child_order_obj, $coupon_obj);


                        if($result){
							
							

                            $all_orders_total += $child_order_obj->get_subtotal();

                            wc_os_remove_coupon($child_order_obj, $coupon_code);
							
                            $child_order_obj->calculate_taxes();
                            $child_order_obj->calculate_totals( false );
                            $child_order_obj->save();
                        }



                    }
					
					
					
					
					
					if($coupon_option){


						foreach($child_ids as $child_id){
							
							//continue;
	
							$child_order_obj = new WC_Order($child_id);
							$coupon_obj_new = $coupon_obj;
							
							$current_total = $child_order_obj->get_subtotal();
							$discount_ratio = ($current_total / $all_orders_total);
							
							switch($coupon_option){
								case 'ratio':
									$current_order_discount = round(($total_cart_discount * $discount_ratio), 2);
								break;
								case 'clone':
									$current_order_discount = $total_cart_discount;
								break;
							}

	
							try {
								
								
								
								switch($coupon_option){
									case 'ratio':
	
										$actual_price = $coupon_obj_new->get_amount();
										$coupon_obj_new->set_amount($current_order_discount);
										$coupon_obj_new->save();
			
										$result = wc_os_apply_coupon($child_order_obj, $coupon_obj_new);
										
										
			
										$coupon_obj_new->set_amount($actual_price);
										$coupon_obj_new->save();
										
									break;					
									
									case 'clone':
									
										$result = wc_os_apply_coupon($child_order_obj, $coupon_obj_new);
									
									break;				
								}
								
								$child_order_obj->calculate_taxes();
								$child_order_obj->calculate_totals( false );
								$child_order_obj->save();
	
							}catch(Exception $e){}
	
						}
						
					}

                }

            }

        }

    }

    if(!function_exists('wc_os_apply_coupon')){

        function wc_os_apply_coupon(&$current_order, $raw_coupon) {
			
			
            if ( is_a( $raw_coupon, 'WC_Coupon' ) ) {
                $coupon = $raw_coupon;
            } elseif ( is_string( $raw_coupon ) ) {
                $code   = wc_format_coupon_code( $raw_coupon );
                $coupon = new WC_Coupon( $code );

                if ( $coupon->get_code() !== $code ) {
                    return false;
                }
            } else {
                return false;
            }



            // Check to make sure coupon is not already applied.
            $applied_coupons = $current_order->get_items( 'coupon' );
            foreach ( $applied_coupons as $applied_coupon ) {
                if ( $applied_coupon->get_code() === $coupon->get_code() ) {
                    return false;
                }
            }

            $discounts = new WC_Discounts( $current_order );

            $applied   = $discounts->apply_coupon( $coupon );

            if ( is_wp_error( $applied ) ) {
                return false;
            }

            $data_store = $coupon->get_data_store();

            // Check specific for guest checkouts here as well since WC_Cart handles that seperately in check_customer_coupons.


            wc_os_set_coupon_discount_amounts($current_order, $discounts);
            $current_order->save();

            // Recalculate totals and taxes.
            $current_order->recalculate_coupons();



            return true;
        }

    }

    if(!function_exists('wc_os_set_coupon_discount_amounts')){

        function wc_os_set_coupon_discount_amounts(&$order, $discounts) {
			
			

            $coupons           = $order->get_items( 'coupon' );
            $coupon_code_to_id = wc_list_pluck( $coupons, 'get_id', 'get_code' );
            $all_discounts     = $discounts->get_discounts();
            $coupon_discounts  = $discounts->get_discounts_by_coupon();

            if ( $coupon_discounts ) {
                foreach ( $coupon_discounts as $coupon_code => $amount ) {

                    $item_id = isset( $coupon_code_to_id[ $coupon_code ] ) ? $coupon_code_to_id[ $coupon_code ] : 0;

                    if ( ! $item_id ) {
                        $coupon_item = new WC_Order_Item_Coupon();
                        $coupon_item->set_code( $coupon_code );
                    } else {
                        $coupon_item = $order->get_item( $item_id, false );
                    }

                    $discount_tax = 0;

                    // Work out how much tax has been removed as a result of the discount from this coupon.
                    foreach ( $all_discounts[ $coupon_code ] as $item_id => $item_discount_amount ) {
                        $item = $order->get_item( $item_id, false );

                        if ( 'taxable' !== $item->get_tax_status() || ! wc_tax_enabled() ) {
                            continue;
                        }

                        $taxes = array_sum( WC_Tax::calc_tax( $item_discount_amount, WC_Tax::get_rates( $item->get_tax_class() ), $order->get_prices_include_tax() ) );
                        if ( 'yes' !== get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
                            $taxes = wc_round_tax_total( $taxes );
                        }

                        $discount_tax += $taxes;

                        if ( $order->get_prices_include_tax() ) {
                            $amount = $amount - $taxes;
                        }
                    }

                    $coupon_item->set_discount( $amount );
                    $coupon_item->set_discount_tax( $discount_tax );
					
                    $order->add_item( $coupon_item );
                }
            }
        }

    }


    if(!function_exists('wc_os_remove_coupon')){

        function wc_os_remove_coupon(&$order,  $coupon_code ) {
            $coupons = $order->get_items( 'coupon' );

            // Remove the coupon line.
            foreach ( $coupons as $item_id => $coupon ) {
                if ( $coupon->get_code() === $coupon_code ) {
                    $order->remove_item( $item_id );
                    $order->recalculate_coupons();
                    break;
                }
            }
        }
    }
	
	
    add_action('wp_ajax_wc_os_update_speed_optimization', 'wc_os_update_speed_optimization');

    if(!function_exists('wc_os_update_speed_optimization')){


        function wc_os_update_speed_optimization(){

            $result = array(
                    'status' => false,
            );

            if(!empty($_POST) && isset($_POST['wc_os_speed_optimization'])){


                if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wc_os_nonce_action')){
                    
                    wp_die(__('Sorry, your nonce did not verify.', 'woo-order-splitter'));
                }else{

                    //your code here

                    $wc_os_speed_optimization = sanitize_wc_os_data($_POST['wc_os_speed_optimization']);
                    $result['status'] = update_option('wc_os_speed_optimization', $wc_os_speed_optimization);
                }

            }

            wp_send_json($result);

        }
    }
	
	add_action('wc_os_ie_options_subscription_split', 'wc_os_ie_options_subscription_split_html');

	if(!function_exists('wc_os_ie_options_subscription_split_html')){
		function wc_os_ie_options_subscription_split_html($method_type){
			
				global $wc_os_delivery_date_activated, $is_woocommerce_subscriptions, $wc_os_schedule_delivery_for_woocommerce;
				
				
				$wc_os_delivery_selection = wc_os_get_method_option($method_type, 'wc_os_delivery_selection', array());
				
				$is_schedule_delivery = ($wc_os_delivery_selection=='schedule_delivery');
								
			?>
            	<div class="container-fluid wc_os_delivery_selection_wrapper p-0 mt-3">
                	
                
                    <div class="wc_os_schedule_delivery_selection <?php echo ($is_schedule_delivery?'selected':''); ?>"><i class="fas fa-calendar-alt"></i><br />
                    
                    <ul>
					<li><?php echo ($wc_os_schedule_delivery_for_woocommerce?'<i class="fas fa-check-circle wc-os-green"></i>':'<i class="fas fa-times-circle wc-os-red"></i>'); ?><span>Schedule Delivery for WooCommerce</span> <a title="<?php _e('How it works?', 'woo-order-splitter'); ?>" class="split_default" href="https://www.youtube.com/embed/ds-WRwhJVCc" target="_blank"><i class="fab fa-youtube"></i></a><div class="wc-os-plugin-icon"></div></li>
                    </ul>
                    
                    <i class="fas fa-plug"></i>
                    
                    </div>
                    <div class="wc_os_delivery_date_selection <?php echo (!$is_schedule_delivery?'selected':''); ?>"><i class="fas fa-tasks"></i><br />
                    
                    <ul>
					<li class="wc_os_delivery_date_li"><?php echo ($wc_os_delivery_date_activated?'<i class="fas fa-check-circle wc-os-green"></i>':'<i class="fas fa-times-circle wc-os-red"></i>'); ?><span>Order Delivery Date</span><div class="wc-os-plugin-icon"></div></li>
					<li class="woocommerce_subscriptions_li"><?php echo ($is_woocommerce_subscriptions?'<i class="fas fa-check-circle wc-os-green"></i>':'<i class="fas fa-times-circle wc-os-red"></i>'); ?><span>WooCommerce Subscriptions</span><div class="wc-os-plugin-icon"></div></li>
                    </ul>
                    
                    <i class="fas fa-plug"></i>
                    </div>
                    
				</div>

				<div class="container-fluid wc_os_delivery_date p-0 mt-3 <?php echo (!$is_schedule_delivery?'d-block':''); ?>">

					<div class="row">

						<?php wc_os_subscription_single_option('first_order', $method_type); ?>
						<div class="col-md-1" style="text-align:center; margin-top:35px;margin-left: 15px;">/</div>
						<?php wc_os_subscription_single_option('remaining_items', $method_type); ?>

					</div>

					

				</div>
                
                <div class="row my-3 float-left">
                    <div class="col-md-12 pl-3">
                    	<input type="hidden" name="wc_os_method_options[subscription_split][wc_os_delivery_selection]" id="wc_os_delivery_selection" value="<?php echo esc_attr($wc_os_delivery_selection); ?>" />
                        <button class="button ims_button_white" type="submit">
                            <?php _e('Save Changes', 'woo-order-splitter'); ?>
                        </button>
                    </div>
                </div>
				

			<?php
		}
	}

	if(!function_exists('wc_os_subscription_single_option')){
		function wc_os_subscription_single_option($option_type, $method_type){

				global $wc_os_delivery_date_activated;
				$label = '';
				
				


				$current_method_options = wc_os_get_method_option($method_type, 'delivery_date', array());
		
			
				$current_method_options = wc_os_get_value($current_method_options, $option_type, array());
				
				$selected_type = wc_os_get_value($current_method_options, 'type', '');
				$selected_weekday = wc_os_get_value($current_method_options, 'weekday', '');
				$selected_interval = wc_os_get_value($current_method_options, 'interval', '');

				$week_days = array(

					"monday" => "Monday",
					"tuesday"  => "Tuesday" ,
					"wednesday"  => "Wednesday" ,
					"thursday"  => "Thursday" ,
					"friday"  => "Friday" ,
					"saturday"  => "Saturday" ,
					"sunday"  => "Sunday" ,
				);

				$input_name_prefix = "wc_os_method_options[$method_type][delivery_date][$option_type]";
				

				$select_options = array(
					'order_date' => __('Order Created Date', 'woo-order-splitter'),
					'order_interval' => __('Progressive Order Delivery Date + Interval', 'woo-order-splitter'),
					'order_created_weekdays' => __('Order Created Date + Weekday Interval', 'woo-order-splitter'),
					'order_weekdays' => __('Progressive Order Delivery Date + Weekday Interval', 'woo-order-splitter'),
					'user_selection' => __('Progressive Order Delivery Date selected by Customer (Checkout Page)', 'woo-order-splitter'),
					
				);

				switch($option_type){
					case 'first_order':
						$label = __('First Order Delivery', 'woo-order-splitter');
						$select_options['order_interval'] = str_replace('Progressive ', '', $select_options['order_interval']);
						$select_options['order_weekdays'] = str_replace('Progressive ', '', $select_options['order_weekdays']);
						$select_options['user_selection'] = str_replace('Progressive ', '', $select_options['user_selection']);
					
					break;
					case 'remaining_items':
						$label = __('Remaining Items Delivery', 'woo-order-splitter');
						$select_options['first_order'] = __('Same as First Order', 'woo-order-splitter');

					break;
				}
				
				

?>


				<div class="col-md-5 single_section <?php echo esc_attr($option_type); ?> pt-2">

					<div class="row">
						
						<div class="col-md-6">

							
							<div class="mb-1">

								<label>
									<?php echo esc_html($label); ?>:
								</label>
							
							</div>


							<select name="<?php echo esc_attr($input_name_prefix); ?>[type]" class='delivery_type'>
								<option value=''><?php _e('Select delivery interval type', 'woo-order-splitter') ?></option>
								
								<?php 
								
									if(!empty($select_options)){

										foreach($select_options as $option_key => $option_name){
											$selected_option = selected($selected_type == $option_key, true, false);

											$disabled = '';

											if($option_key == 'user_selection' && !$wc_os_delivery_date_activated){
												$disabled = 'disabled';
												$selected_option = '';
											}

											echo "<option value='$option_key' $selected_option $disabled>$option_name</option>";
										}

									}

								?>
								
							</select>

						</div>						

						<div class="col-md-6 extended_options" style="display:none;">

							<div class="col-md-2" style="text-align:center;">
								<div style="margin-top:30px;">

									<strong>+</strong>

								</div>
							</div>
													

							<div class="col-md-10 weekdays_option">							
								<div class="mb-1">

									<label>
										<?php _e('Weekday Interval', 'woo-order-splitter'); ?>:
									</label>

								</div>


								<select name="<?php echo esc_attr($input_name_prefix); ?>[weekday]" id="" class="">
									<option value=""><?php _e('Select Weekdays', 'woo-order-splitter') ?></option>
									<?php

										if(!empty($week_days)){
											foreach($week_days as $day_key => $day_name){
												$selected_day = selected($selected_weekday == $day_key, true, false);
												echo "<option value='$day_key' $selected_day>$day_name</option>";
											}
										}								
									
									?>
								</select>
								
							</div>						

							<div class="col-md-10 interval_option">

								<div class="mb-1">

									<label>
									 	<?php _e('Days Interval', 'woo-order-splitter'); ?>:
									</label>

								</div>							

								<input type="number" min='1' name="<?php echo esc_attr($input_name_prefix); ?>[interval]" value="<?php echo esc_attr($selected_interval); ?>">
								
							</div>						
						
						</div>
						
					</div>					

				</div>

			<?php
		}
	}

	if(!function_exists('wc_os_get_value')){
		function wc_os_get_value($array, $key, $default = false){
			$return_val = ((is_array($array) && array_key_exists($key, $array)) ? $array[$key] : $default);
			if(is_array($default) && !is_array($return_val)){

				$return_val = array();
			}


			return $return_val;			
		}
	}	
	if(!function_exists('update_wcfm_order_status')){
		function update_wcfm_order_status($order_id, $order_status){
		
			global $wpdb, $wc_os_wcfm_installed, $wc_os_general_settings;
			
			
			
			
			if($wc_os_wcfm_installed && get_option('wcfmmp_table_install')){
				
				$order_status = str_replace('wc-', '', $order_status);
				
				$order_id = esc_sql($order_id);
				$order_status = esc_sql($order_status);
				
				
				$set_fields = array();

				$customer_id = wc_os_get_order_meta($order_id, '_customer_user', true);
				$payment_method = wc_os_get_order_meta($order_id, '_payment_method', true);
				
				$set_fields[] = 'customer_id="'.$customer_id.'"';
				$set_fields[] = 'payment_method="'.$payment_method.'"';
				
				if(array_key_exists('wcfm_order_status', $wc_os_general_settings)){
					$set_fields[] = 'order_status="'.$order_status.'"';
				}
				if(array_key_exists('wcfm_shipping_status', $wc_os_general_settings)){
					$set_fields[] = 'shipping_status="'.$order_status.'"';
				}
				if(array_key_exists('wcfm_commission_status', $wc_os_general_settings)){
					$set_fields[] = 'commission_status="'.$order_status.'"';
				}				
				
			
				
				if(is_numeric($order_id) && $order_id>0 && !empty($set_fields)){
					$update_query = 'UPDATE '.$wpdb->prefix.'wcfm_marketplace_orders SET '.implode(',', $set_fields).' WHERE order_id="'.$order_id.'"';
					
					
					
					$wpdb->query($update_query);
				}
			}
			
		}
	}
	
	if(!function_exists('wc_os_status_change_cron')){

		function wc_os_status_change_cron($new_status, $old_status, $post){
			
			if(is_checkout()){ return; }
			
			$wc_os_get_post_type_default = wc_os_get_post_type_default();
			
			$order_details_page = (array_key_exists('wc_os_ps', $_POST));

			if(is_object($post) && isset($post->post_type) && $post->post_type == $wc_os_get_post_type_default && !$order_details_page){
				//global $wos_status_change;
				//$wos_status_change = true;
				$post_meta = wc_os_get_order_meta($post->ID);
				
				//wc_os_logger('debug', 'wc_os_status_change_cron: #'.$post->ID, true);
				
				if(!empty($post_meta)){
					
					$post_meta = array_keys($post_meta);
					
					$is_parent_order = (!in_array('splitted_from', $post_meta) && !in_array('cloned_from', $post_meta));
					
					if($is_parent_order && !in_array('split_status', $post_meta)){

						wc_os_set_splitter_cron($post->ID, true, 9733);
						wc_os_crons($post->ID);
					}
					
				}
			}
	
			
		}
	}

	add_action('transition_post_status', 'wc_os_status_change_cron', 10, 3);
	
	if(!function_exists('wc_os_update_ignored_cron_array')){
		function wc_os_update_ignored_cron_array($order_id){

			$ignored_array = get_option('wc_os_ignore_cron_orders', array());

			if(is_array($ignored_array) && $order_id){
				$ignored_array[] = $order_id;
			}

			return update_option('wc_os_ignore_cron_orders', $ignored_array);

		}
	}

	if(!function_exists('wc_os_remove_order_from_ignored')){
		function wc_os_remove_order_from_ignored($order_id){

			$ignored_array = get_option('wc_os_ignore_cron_orders', array());

			if(is_array($ignored_array) && $order_id){
				$order_key = array_search($order_id, $ignored_array);

				if($order_key != false){
					unset($ignored_array[$order_key]);
				}
			}

			return update_option('wc_os_ignore_cron_orders', $ignored_array);

		}
	}

	if(!function_exists('wc_os_check_ignored_order')){
		function wc_os_check_ignored_order($order_id){

			$ignored_array = get_option('wc_os_ignore_cron_orders', array());
			return in_array($order_id, $ignored_array);
		}
	}

	


	
	add_filter( 'woocommerce_order_needs_shipping_address', '__return_true' );
	
	function wc_os_set_email_content_type(){
		return "text/html";
	}	
	
	function wos_admin_head(){
		
		global $pagenow, $post, $wc_os_general_settings, $wc_os_premium_copy;
		
		$wc_os_view_order_button = array_key_exists('wc_os_view_order_button', $wc_os_general_settings);

		$wc_os_get_post_type_default = wc_os_get_post_type_default();
	
		$status_colors = get_option('wc_os_status_colors');
		$status_colors = (is_array($status_colors) && array_key_exists('wc_os_colors', $status_colors)?$status_colors['wc_os_colors']:array());
		$bgc_colors = (array_key_exists('bgc', $status_colors)?$status_colors['bgc']:array());
		$fgc_colors = (array_key_exists('fgc', $status_colors)?$status_colors['fgc']:array());
		

?>	
<style type="text/css">
<?php
		if(!empty($bgc_colors)){
			foreach($bgc_colors as $k=>$v){
				
				if(array_key_exists($k, $fgc_colors) && $fgc_colors[$k]!=$v){
?>
mark.order-status.status-<?php echo $k; ?>{
	background-color: <?php echo $v; ?>;
	color: <?php echo $fgc_colors[$k]; ?>;
	<?php if($v=='#ffffff'): ?>
	border: 1px solid <?php echo $fgc_colors[$k]; ?>;
	<?php endif; ?>
}
mark.order-status.status-<?php echo $k; ?>:hover{
	background-color: <?php echo $fgc_colors[$k]; ?>;
	color: <?php echo $v; ?>;
	<?php if($v=='#ffffff'): ?>
	border: 1px solid <?php echo $v; ?>;
	<?php endif; ?>
}
<?php
				}
			}
			
		}
?>		
	
</style>	
		

<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
<?php
		
		if($pagenow=='post.php' && is_object($post) && $post->post_type==$wc_os_get_post_type_default){
			
			$_wc_os_status_locked = wc_os_get_order_meta($post->ID, '_wc_os_status_locked', true);
			
			if($wc_os_view_order_button){
				$wc_order = wc_get_order($post->ID);
				$order_received = wc_get_checkout_url().'order-received/'.$post->ID.'/?key='.$wc_order->get_order_key();
?>		
				$('<a href="<?php echo esc_url($order_received); ?>" class="page-title-action" target="_blank"><?php echo __('View order', 'woo-order-splitter'); ?></a>').insertAfter($('a.page-title-action'));
<?php

			}
?>
			$('p.wc-order-status label[for="order_status"]').append('<input type="hidden" id="_wc_os_status_locked" name="_wc_os_status_locked" value="<?php echo ($_wc_os_status_locked?'yes':'no'); ?>" /> <a class="wc-os-status-lock <?php echo ($_wc_os_status_locked?'locked':''); ?>" title="<?php echo __('Click here to lock/unlock the automatic status change from Order Splitter Plugin', 'woo-order-splitter'); ?>"><i class="fas fa-lock"></i></a>');
<?php
		}	
		
		if(wc_os_is_orders_list()){
?>		
			if($('.wc-os-yt-orders-list').length==0){
				$('<a href="https://www.youtube.com/embed/-i1hF_mIXZs" class="wc-os-yt-orders-list" target="_blank" title="<?php echo __('How it works?', 'woo-order-splitter'); ?>"><span class="dashicons dashicons-video-alt3"></span></a>').insertAfter($('#bulk-action-selector-top'));
				
			}
			if($('.wuos-wos').length==0 && wos_obj.wuos_wos!='yes'){// && $('select[name="action"]').val()=='combine'

					$(`<div class="alert alert-success wc_os_alert wuos-wos w-100" role="alert">
  `+(wos_obj.is_premium!='yes'?'<?php echo __('Bulk edit/combine features are available only in', 'woo-order-splitter'); ?> <a href="<?php echo esc_url($wc_os_premium_copy); ?>" target="_blank"><?php echo __('Premium Version', 'woo-order-splitter'); ?></a>.<br />':'')+ `<?php echo __('Optional:', 'woo-order-splitter').'<br />'.__('Do you want to automate the combine orders process by defining some rules?<br />Another awesome WordPress Plugin from the same Plugin Author is available as', 'woo-order-splitter'); ?> <a href="<?php echo esc_url(admin_url().'/plugin-install.php?s=woo%20ultimate%20order%20combination%20fahad%20mahmood&tab=search&type=term'); ?>" target="_blank"><?php echo __('Ultimate Order Combination', 'woo-order-splitter'); ?></a> / <a href="<?php echo esc_url('https://wordpress.org/plugins/woo-ultimate-order-combination/'); ?>" target="_blank"><?php echo __('Click here for details', 'woo-order-splitter'); ?></a>.
</div>`).insertBefore($('table.wp-list-table'));

					$(`<div class="alert alert-success wc_os_alert bulk-wos w-100" role="alert"><?php echo __('Bulk split orders option will ignore the split method rules and by default it splits every other item in a separate order.', 'woo-order-splitter').' <br />'.__('It is recommended that you use the split icon', 'woo-order-splitter').' <a class="button wc-action-button wc-action-button-wc_os_split wc_os_split" title="'.__('Split Icon', 'woo-order-splitter').'">&nbsp;</a> '.__('in each order row or auto split for the split according to the split method settings.', 'woo-order-splitter'); ?></div>`).insertBefore($('table.wp-list-table'));
				
				
			}
			
			
			
<?php			
		}
?>		
	});
</script>	
<?php		
	}
	
	
	
	add_action('woocommerce_account_content', 'wc_os_woocommerce_account_content');
	
	function wc_os_woocommerce_account_content(){
		
		global $wc_os_general_settings;
		
		if(is_array($wc_os_general_settings) && (empty($wc_os_general_settings) || (!empty($wc_os_general_settings) && array_key_exists('wc_os_cron_my_account_page', $wc_os_general_settings)))){
			wc_os_crons(); //CALLING CRON FUNCTION TO PERFORM A FEW SCHEDULED ACTIONS WITHOUT REQUIRING A VISIT/PAGE REFRESH FROM ADMIN ORDERS LIST
		}
		
	}
	add_filter( 'woocommerce_can_reduce_order_stock','wos_filter_woocommerce_can_reduce_order_stock', 10, 2 );
	
	/**
	 * Indicates if an order is ready to be processed. An order can be processed if it satisfies the
	 * following conditions:
	 * - It's not a draft.
	 * - It's not an autosave.
	 * - The nonce is validated.
	 * - The ID of the order being saved ($_POST['post_ID']) matches the current post ID.
	 * - The user has the permission to edit the post.
	 *
	 * @param int $post_id The post ID for the order.
	 * @return bool
	 * @author Aelia
	 */
	function wc_os_is_order_ready_for_processing($post_id) {
		
		$post_id = absint( $post_id );
		
		global $post;
		
		$post = (is_object($post)?$post:($post_id?get_post($post_id):array()));
		
		if ( empty( $post_id ) || (!is_object($post) || empty( $post )) ) {
			return false;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return false;
		}

		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return false;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return false;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}
	
	function wc_os_update_order_item_admin($order_id=0){
		if($order_id){
			
			
			$_wos_update_rounds = wc_os_get_order_meta($order_id, '_wos_update_rounds', true);
			$_wos_update_rounds = ($_wos_update_rounds?$_wos_update_rounds:0);
			$_wos_update_rounds++;
			$_wos_update_order_item = wc_os_get_order_meta($order_id, '_wos_update_order_item', true);
			$split_status = wc_os_child_orders_by_order_id($order_id);
			if($_wos_update_order_item && is_array($_wos_update_order_item) && !empty($_wos_update_order_item)){
				wc_os_update_order_meta($order_id, '_wos_update_rounds', $_wos_update_rounds);
				
				if(class_exists('wc_os_bulk_order_splitter')){
					$classObj = new wc_os_bulk_order_splitter;
					$classObj->wos_update_order_item($order_id, $_wos_update_order_item['include_items'], $_wos_update_order_item['include_items_qty']);
				}
				
				if($_wos_update_rounds>1){
					wc_os_delete_order_meta($order_id, '_wos_update_order_item');
					wc_os_delete_order_meta($order_id, '_wos_update_rounds');
				}
				$order_data = wc_get_order( $order_id );
				$order_data->calculate_totals();
				$order_data->save();
			}
			
			//exit;
		}
		
	}
	if(!function_exists('wc_os_update_order_status')){
        function wc_os_update_order_status($order_id, $status=''){

            global $wpdb;
            $order_post = get_post($order_id);
			$wc_os_get_post_type_default = wc_os_get_post_type_default();			

            if(is_object($order_post) && $order_post->post_type = $wc_os_get_post_type_default){

                $update_query = "UPDATE $wpdb->posts set post_status = '$status' WHERE ID = $order_id";
                $wpdb->query($update_query);

				$debug_backtrace = debug_backtrace();
				
				$function = $debug_backtrace[0]['function'];
				$function .= ' / '.$debug_backtrace[1]['function'];
				$function .= ' / '.$debug_backtrace[2]['function'];
				$function .= ' / '.$debug_backtrace[3]['function'];
				$function .= ' / '.$debug_backtrace[4]['function'];				

				//update_wcfm_order_status($order_id, $status);

            }


        }
    }
	function wc_os_update_split_status($order_id, $line_no='', $status=true, $splitted_orders=array()){
		
		
		$split_status = wc_os_get_order_meta($order_id, 'split_status', true);
		
		//pree($split_status);pree($status);exit;
		
		if(!in_array($split_status, array($status, '-'))){
		
			$debug_backtrace = debug_backtrace();
			
			$function = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];
			$function .= ' / line_no. '.$line_no;
			
			if(is_array($splitted_orders) && $status!=true){
				$status = (count($splitted_orders)>1?true:'-');
			}
			
			$function .= ' / status. '.$status;			
			
			
			
			
			
			if(in_array($status, array(true, false))){
				
				wc_os_update_order_meta($order_id, 'split_status', $status);
			}
			

			
		}

	}
	
	
	
	
	if(!function_exists('wc_os_set_order_status')){
		function wc_os_set_order_status($order_id=0, $status_org='', $force=false, $line_no=0, $consider_child=false){
			
			if($consider_child){
				wc_os_update_order_meta($order_id, '_wc_os_parent_order', 'no');				
			}
			
			
			$debug_backtrace = debug_backtrace();
				
			$function = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];
			$function .= ' / $status_org: '.$status_org;
			$function .= ' / $order_id: '.$order_id;
			$function .= ' / $line_no: '.$line_no;
			
			//wc_os_logger('debug', $function, true);

			$updated_status = '';
			
			if($order_id>0){
					
					$_wc_os_created_via = wc_os_get_order_meta($order_id, '_created_via', true);
					$_wc_os_status_locked = wc_os_get_order_meta($order_id, '_wc_os_status_locked', true);
					
					$do_not_change = ($_wc_os_created_via=='subscription' || $_wc_os_status_locked);
					
					wc_os_pre('$do_not_change: '.$do_not_change, '');
					
					if($do_not_change){
						wc_os_delete_order_meta($order_id, '_wc_os_set_status');
						return $updated_status;
					}
					
					
					wc_os_pre('$status_org: '.$status_org, '');
					wc_os_pre('$force: '.$force, '');
					
					if(!$status_org && !$force){
						
						$is_parent_order = (wc_os_get_order_meta($order_id, '_wc_os_parent_order', true)=='yes');
						
						wc_os_pre('$is_parent_order: '.$is_parent_order, '');
						
						if($is_parent_order){
					
							$split_removal_case = (count(wc_os_child_orders_by_order_id($order_id))>0);
							
							if($split_removal_case){
								$status_org = wc_os_order_split_removal_action('string', 10852);
								
							}else{
								
								$wc_os_rule_based_switch = wc_os_implement_rule_status($order_id);
								$status_org = ($wc_os_rule_based_switch != false ? $wc_os_rule_based_switch : wc_os_order_removal_action('string'));							
								
							}				
							
						}else{
							
							
							$status_org = ($status_org?$status_org:wc_os_order_split_status_action());
							
							wc_os_pre('$status_org: '.$status_org, '');
							
						}
						
						
				
						$status_org_updated = wc_os_method_based_default_order_status($status_org, $order_id);
						
						wc_os_pre('$status_org_updated: '.$status_org_updated, '');
						
						$status_org = ($status_org_updated?$status_org_updated:$status_org);
						
						wc_os_pre('$status_org: '.$status_org, '');
						
						
					}
					
					
					if($status_org){
					
						$_wc_os_set_status = wc_os_get_order_meta($order_id, '_wc_os_set_status', true);
						
						
						if(!$_wc_os_set_status || $force){
								
							global $wc_os_shipping_cost, $wc_os_is_combine;
							
							$status = str_replace('wc-', '', $status_org);
							$order = wc_get_order($order_id);
							$order_items = (is_object($order)?$order->get_items():array());
							$status = apply_filters('wc_os_split_order_status_logic_hook', $status, $order);
							
							if(is_object($order)){
							
								if($wc_os_shipping_cost && !$wc_os_is_combine && !$force){
									
									$updated_status = $status;
			
									wc_os_update_order_meta($order_id, '_wc_os_set_status', $status);
									
								}else{
									switch($status){
										default:
											$updated_status = wc_os_add_prefix($status, 'wc-');
											$order->set_status($updated_status);
											$order->save();
										break;
										case 'trash':
											if(empty($order_items)){
												wp_trash_post($order_id);
											}
										break;
									}
								}
							}
							
							update_wcfm_order_status($order_id, $status_org);
							
							
							
							
							
						}
					}
				
			}
			
			//wc_os_logger('debug', '$order_id: '.$updated_status, true);

			return $updated_status;
			
		}
	}		
	
	if(!function_exists('wc_os_set_splitter_cron')){
		function wc_os_set_splitter_cron($order_id=0, $status=true, $line_no=0){
			
			if($order_id>0){
					$wc_os_order_splitter_cron = wc_os_get_order_meta($order_id, 'wc_os_order_splitter_cron', true);	
					
					if($wc_os_order_splitter_cron!=$status){
	
						wc_os_update_order_meta($order_id, 'wc_os_order_splitter_cron', $status);
						
						$debug_backtrace = debug_backtrace();
			
						$function = $debug_backtrace[0]['function'];
						$function .= ' / '.$debug_backtrace[1]['function'];
						$function .= ' / '.$debug_backtrace[2]['function'];
						$function .= ' / '.$debug_backtrace[3]['function'];
						$function .= ' / '.$debug_backtrace[4]['function'];
						
						

						

						
					}
				
			}
			
		}
	}		
	

	
	if(!function_exists('wos_admin_init_basic')){
		function wos_admin_init_basic(){
			
			if(isset($_GET['get_keys'])){
				
				global $wc_os_pro, $wpdb;
				
				$order_id = (isset($_GET['post'])?sanitize_wc_os_data($_GET['post']):(isset($_GET['id'])?sanitize_wc_os_data($_GET['id']):0));
				
				wc_os_pre($order_id);
				
				$order = wc_get_order($order_id);
				
				$order->calculate_totals();
				
				$order_data_store = WC_Data_Store::load( 'order' );
			
				$internal_meta_keys = $order_data_store->get_internal_meta_keys();
				
				//pree(get_post_meta(131808));
				
				//pree($internal_meta_keys);
				
				//$this_original_order_id = 346;
				
					
				//wc_os_update_order_meta( $order_id, '_billing_city', wc_os_get_order_meta($this_original_order_id, '_billing_city', true));
				/*wc_os_update_order_meta( $order_id, '_billing_state', wc_os_get_order_meta($this->original_order_id, '_billing_state', true));
				wc_os_update_order_meta( $order_id, '_billing_postcode', wc_os_get_order_meta($this->original_order_id, '_billing_postcode', true));
				wc_os_update_order_meta( $order_id, '_billing_email', wc_os_get_order_meta($this->original_order_id, '_billing_email', true));
				wc_os_update_order_meta( $order_id, '_billing_phone', wc_os_get_order_meta($this->original_order_id, '_billing_phone', true));
				wc_os_update_order_meta( $order_id, '_billing_address_1', wc_os_get_order_meta($this->original_order_id, '_billing_address_1', true));
				wc_os_update_order_meta( $order_id, '_billing_address_2', wc_os_get_order_meta($this->original_order_id, '_billing_address_2', true));
				wc_os_update_order_meta( $order_id, '_billing_country', wc_os_get_order_meta($this->original_order_id, '_billing_country', true));
				wc_os_update_order_meta( $order_id, '_billing_first_name', wc_os_get_order_meta($this->original_order_id, '_billing_first_name', true));
				wc_os_update_order_meta( $order_id, '_billing_last_name', wc_os_get_order_meta($this->original_order_id, '_billing_last_name', true));
				wc_os_update_order_meta( $order_id, '_billing_company', wc_os_get_order_meta($this->original_order_id, '_billing_company', true));*/
				
				
				//wc_os_pre($order);
				//wc_os_update_shipping_for_multiple_addresses($order_id, $order);
				
				$order_meta = wc_os_get_order_meta($order_id);
				
				wc_os_pre('$order_meta: '.(is_array($order_meta)?'ARRAY':''));
				wc_os_pre($order_meta);
				
				$taxes = $order->get_items('tax');
				
				//wc_os_pre($taxes);
				
				foreach($taxes as $item_id => $item ) {
					wc_os_pre($item->get_tax_total());
				}
				
				if(is_object($order) && function_exists('wc_avatax')){
				
					wc_os_logger('debug', 'MANUAL EDIT: wc_avatax()->get_order_handler()->estimate_tax for #'.$order_id, true);
					
					wc_avatax()->get_order_handler()->estimate_tax( $order );
					
				}
				
				
				if(defined('WC_PRODUCT_VENDORS_TAXONOMY')){
					
					$vendor_id = wc_os_get_order_meta($order_id, '_vendor_id', true);
					
					wp_set_object_terms( $order_id, intval($vendor_id), WC_PRODUCT_VENDORS_TAXONOMY );
				}
				
				exit;
				
			}
		}
		
	}

			
	add_filter( 'woocommerce_prevent_adjust_line_item_product_stock', function($prevent=false){
		global $post, $wc_os_general_settings;
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		$is_order_page = (is_object($post) && !empty($post) && isset($post->post_type) && $post->post_type==$wc_os_get_post_type_default);
		if(!$is_order_page && is_array($_POST) && !empty($_POST) && array_key_exists('order_id', $_POST)){			
			$get_post = get_post($_POST['order_id']);
			if(is_object($get_post) && !empty($get_post)){
				$post = $get_post;				
			}
		}
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();
		
		if(
				is_object($post) 
			&& 
				!empty($post)
			&& 
				isset($post->post_type) 
			&& 
				$post->post_type==$wc_os_get_post_type_default
			/*&& 
				!array_key_exists('wc_os_reduce_stock', $wc_os_general_settings)*/
		){
			$wos_processed_order = wos_woocommerce_can_reduce_order_stock_inner($post->ID);			
			if($wos_processed_order){
				$prevent = true;
			}
		}
		
		
		
		return $prevent;
	});		
	
	
	function wc_os_init_functions(){
		
		
    	if(function_exists('wc_os_cron_jobs')){
			wc_os_cron_jobs();
		}		
    	if(function_exists('wc_os_init')){
			wc_os_init();
		}
    	if(function_exists('wc_os_import_export_init')){
			wc_os_import_export_init();
		}
    	if(function_exists('wc_os_init_functions_pro')){
			wc_os_init_functions_pro();
		}
		
		
		
        global  $wc_os_general_settings;

        $order_split_summary = array_key_exists('wc_os_order_split_email', $wc_os_general_settings);
        $order_split_created = array_key_exists('wc_os_order_created_email', $wc_os_general_settings);
        $order_split_created_admin = array_key_exists('wc_os_order_created_email_admin', $wc_os_general_settings);
        $order_split_created = $order_split_summary ? $order_split_created : false;
		
		if(!is_admin() && date('i')%2==0){
			wc_os_emails_to_admin_cron();
		}

		if(isset($_GET['reorder'])){
			
			$order_id = sanitize_wc_os_data($_GET['reorder']);
			if($order_id && is_numeric($order_id)){
				$order = wc_get_order($order_id);
				if(empty($order)){ 
				
					die(__('Invalid Order ID', 'woo-order-splitter'));
				}
				WC()->cart->empty_cart();
				WC()->session->set('cart', array());
				
				foreach($order->get_items() as $key=>$val){

					
					WC()->cart->add_to_cart( $val->get_product_id(), $val->get_quantity(), $val->get_variation_id() );
				}
				
				wp_redirect(wc_get_checkout_url());exit;
			}
			
		}

    }	

	function wc_os_admin_functions(){
		
		if(function_exists('shipping_items_test')){ shipping_items_test(); }
		if(function_exists('wc_os_clear_order_log_callback')){ wc_os_clear_order_log_callback(); }
		if(function_exists('wc_os_settings_update')){ wc_os_settings_update(); }
		if(function_exists('wos_admin_init_basic')){ wos_admin_init_basic(); }
		if(function_exists('wc_os_redirect_mails_test')){ wc_os_redirect_mails_test(); }
		if(function_exists('wos_admin_init')){ wos_admin_init(); }
		if(function_exists('wos_admin_footer')){ wos_admin_footer(); }
		if(function_exists('wc_os_temp_tasks')){ wc_os_temp_tasks(); }
		if(function_exists('wc_os_email_tester')){ wc_os_email_tester(); }
		
			
	}
	
	add_action('wp_head', 'wc_os_header_scripts');
	add_action('admin_head', 'wos_admin_head');
	add_action('admin_init', 'wc_os_admin_functions', 1);
	add_action('init', 'wc_os_init_functions');