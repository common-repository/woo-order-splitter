<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
	Plugin Name: Order Splitter for WooCommerce
	Plugin URI: https://wordpress.org/plugins/woo-order-splitter
	Description: Split, merge, clone, your crowd/combined/bulk orders using intelligent rules.
	Version: 5.2.9
	Author: Fahad Mahmood
	Author URI: http://androidbubble.com/blog/
	Text Domain: woo-order-splitter
	Domain Path: /languages/	
	License: GPL2
	
	This WordPress plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This WordPress plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License	along with this WordPress plugin. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/
	
	if(!isset($_GET['wos-run'])){
		//return;
	}

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}else{
		 clearstatcache();
	}
	//return;

//    ini_set('display_errors', 1);
//    ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);

	require_once(realpath(ABSPATH . 'wp-admin/includes/upgrade.php'));
	
	//$wc_os_all_plugins = get_plugins();


	
	
	$wc_os_all_plugins = get_plugins();
	
	
	$wc_os_active_plugins = get_site_option( 'active_sitewide_plugins' );
	
	$wc_os_active_plugins = is_array($wc_os_active_plugins)?$wc_os_active_plugins:array();
	$wc_os_network_active_plugins = is_array($wc_os_active_plugins)?apply_filters( 'active_plugins', array_keys($wc_os_active_plugins) ):array();
	$wc_os_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
	//print_r($wc_os_active_plugins);exit;

    if(!function_exists('wc_os_check_plugin_active_status')){
        function wc_os_check_plugin_active_status($plugin = ''){


            $wc_os_active_plugins = get_site_option( 'active_sitewide_plugins' );
            $wc_os_active_plugins = is_array($wc_os_active_plugins)?$wc_os_active_plugins:array();
            $wc_os_network_active_plugins = is_array($wc_os_active_plugins)?apply_filters( 'active_plugins', array_keys($wc_os_active_plugins) ):array();
            $wc_os_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

            $plugin_status = ((is_multisite() && in_array($plugin, $wc_os_network_active_plugins))
                ||
                in_array($plugin, $wc_os_active_plugins)
            );

            return $plugin_status;

        }
    }	
	
	
	if ( 
			array_key_exists('woocommerce/woocommerce.php', $wc_os_all_plugins) 
		&& 
			(
					(is_multisite() && 	in_array('woocommerce/woocommerce.php', $wc_os_network_active_plugins))
				||
					in_array('woocommerce/woocommerce.php', $wc_os_active_plugins) 
			)
			
	) {
		
		
		
		
		global $wpdb, $wc_os_url, $wc_os_data, $wc_os_pro, $wc_os_activated, $wc_os_premium_copy, $yith_pre_order, $wc_os_bulk_instantiated,
               $wc_os_debug, $wos_actions_arr, $wc_os_cust, $wos_notices_css, $wc_os_general_settings, $wc_os_order_items,
               $wc_os_shipping_cost, $wc_os_tax_cost, $wc_os_effect_parent, $wc_os_multiple_warning, $wc_os_per_page,
               $wc_os_cron_settings, $woocommerce_account_funds, $wc_os_shipping, $wc_os_order_statuses_class,
               $wc_os_cart_item_meta_keys, $wc_os_wcfm_installed, $is_acf, $wc_os_ie_arr, $wos_acf_string, $wc_os_acf_settings,
			   $wc_os_method_options, $wc_os_meta_handling_arr, $is_wc_booking, $is_partial_addon, $wc_os_speed_optimization, $wc_os_minified_js, 
			   $wc_os_minified_css, $wc_os_delivery_date_activated, $is_booster_plus_for_woocommerce, $is_woocommerce_subscriptions, $wc_os_settings, 
			   $wc_os_product_cat_shipping_classes, $wc_os_schedule_delivery_for_woocommerce, $wc_os_is_combine, $is_woocommerce_shipping_usps, $wuos_wos,
			   $wc_os_woo_order_status_manager, $wc_os_cosfw, $wc_os_gf, $wc_os_days, $wc_os_cloud, $wc_os_parcel_shipment, $wc_os_parcel_shipment_adjustment, 
			   $wc_os_advanced_partial_payment, $wc_os_advanced_partial_payment_pro, $wc_os_current_theme, $wc_os_products_per_order, 
			   $wc_os_woocommerce_shipping_multiple_addresses, $wc_os_woo_delivery, $wc_os_recorded_templates_query, $wc_os_orders_meta_keys_to_string, $wc_os_order_post_type, 
			   $wc_os_auto_forced, $wc_os_custom_orders_table_enabled;
		
	
		
		$wc_os_custom_orders_table_enabled = (get_option('woocommerce_custom_orders_table_enabled', true)=='yes');
		$wc_os_auto_forced = false;
		$wc_os_order_post_type = 'shop_order';	   
		$wc_os_current_theme = str_replace(array('-', ' '), '_', strtolower(wp_get_theme()));
		$wc_os_parcel_shipment = get_option('wc_os_parcel_shipment');
		$wc_os_parcel_shipment = ($wc_os_parcel_shipment?$wc_os_parcel_shipment:__('Parcel Shipment', 'woo-order-splitter'));
		
		$wc_os_parcel_shipment_adjustment = __('Shipment Adjustment', 'woo-order-splitter');
		
		$wc_os_cloud = ($wc_os_cloud?$wc_os_cloud:0);
		$wc_os_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
		
		$wc_os_settings = get_option('wc_os_settings', array());
		$wc_os_settings = (is_array($wc_os_settings)?$wc_os_settings:array());
		
		$wc_os_product_cat_shipping_classes = get_option( "wc_os_product_cat_shipping_classes");
		$wc_os_product_cat_shipping_classes = is_array($wc_os_product_cat_shipping_classes)?$wc_os_product_cat_shipping_classes:array();
				
		$wc_os_delivery_date_activated = wc_os_check_plugin_active_status('order-delivery-date/order_delivery_date.php');
		$wc_os_schedule_delivery_for_woocommerce = wc_os_check_plugin_active_status('schedule-delivery-for-woocommerce/schedule-delivery-for-woocommerce.php');
		$wuos_wos = wc_os_check_plugin_active_status('woo-ultimate-order-combination/index.php');
		
		$wc_os_method_options = get_option('wc_os_method_options', array());
		$wos_acf_string = __('ACF not installed/activated, this method only works with ACF', 'woo-order-splitter').
                                    '&nbsp;<a href="'.admin_url('plugin-install.php?s=Advanced Custom Fields&tab=search&type=term').'">'.__('Click here', 'woo-order-splitter').'</a>&nbsp;'.__('to install/activate ACF', 'woo-order-splitter');
        $is_acf = class_exists('ACF');
        $is_wc_booking = wc_os_check_plugin_active_status('woocommerce-booking/woocommerce-booking.php');
        $is_partial_addon = wc_os_check_plugin_active_status('bkap-deposits/deposits.php');
		$is_booster_plus_for_woocommerce = wc_os_check_plugin_active_status('booster-plus-for-woocommerce/booster-plus-for-woocommerce.php');
		$is_woocommerce_subscriptions = wc_os_check_plugin_active_status('woocommerce-subscriptions/woocommerce-subscriptions.php');
		$is_woocommerce_shipping_usps = wc_os_check_plugin_active_status('woocommerce-shipping-usps/woocommerce-shipping-usps.php');
		$wc_os_woo_order_status_manager = wc_os_check_plugin_active_status('woocommerce-order-status-manager/woocommerce-order-status-manager.php');
		$wc_os_cosfw = wc_os_check_plugin_active_status('custom-order-statuses-woocommerce/custom-order-statuses-for-woocommerce.php');
		$wc_os_gf = wc_os_check_plugin_active_status('gravityforms/gravityforms.php');
		$wc_os_advanced_partial_payment = wc_os_check_plugin_active_status('advanced-partial-payment-or-deposit-for-woocommerce/advanced_partial_payment.php');
		$wc_os_advanced_partial_payment_pro = wc_os_check_plugin_active_status('mage-partial-payment-pro/mage_partial_pro.php');
		$wc_os_woocommerce_shipping_multiple_addresses = wc_os_check_plugin_active_status('woocommerce-shipping-multiple-addresses/woocommerce-shipping-multiple-addresses.php');
		$wc_os_woo_delivery = wc_os_check_plugin_active_status('coderockz-woocommerce-delivery-date-time-pro/coderockz-woo-delivery.php');
		
        $wc_os_acf_settings = get_option('wc_os_acf_settings', array());
		$wc_os_speed_optimization = get_option('wc_os_speed_optimization', array());
        $wc_os_minified_js = (array_key_exists('minified_js', $wc_os_speed_optimization) && $wc_os_speed_optimization['minified_js'] == 'true');
        $wc_os_minified_css = (array_key_exists('minified_css', $wc_os_speed_optimization) && $wc_os_speed_optimization['minified_css'] == 'true');
		
		
		
        $wc_os_wcfm_installed = wc_os_check_plugin_active_status('wc-multivendor-marketplace/wc-multivendor-marketplace.php');

		$wc_os_cart_item_meta_keys = unserialize('a:12:{i:0;s:3:"key";i:1;s:10:"product_id";i:2;s:12:"variation_id";i:3;s:9:"variation";i:4;s:8:"quantity";i:5;s:9:"data_hash";i:6;s:13:"line_tax_data";i:7;s:13:"line_subtotal";i:8;s:17:"line_subtotal_tax";i:9;s:10:"line_total";i:10;s:8:"line_tax";i:11;s:4:"data";}');
		$wc_os_url = plugin_dir_url( __FILE__ );
		$woocommerce_account_funds = ( array_key_exists('woocommerce-account-funds/woocommerce-account-funds.php', $wc_os_all_plugins) && in_array('woocommerce-account-funds/woocommerce-account-funds.php', $wc_os_active_plugins) );
		$wc_os_products_per_order = get_option('wc_os_products_per_order');
		$wc_os_products_per_order = (is_numeric($wc_os_products_per_order) && $wc_os_products_per_order>0?$wc_os_products_per_order:1);
		
		$wc_os_cron_settings = get_option('wc_os_cron_settings');
		$wc_os_cron_settings = is_array($wc_os_cron_settings)?$wc_os_cron_settings:array();
		$wc_os_cron_settings['statuses'] = array_key_exists('statuses', $wc_os_cron_settings)?$wc_os_cron_settings['statuses']:array();
		
		$wc_os_cron_settings = is_array($wc_os_cron_settings)?$wc_os_cron_settings:array();		
		
		$wc_os_per_page = get_option('wos_pg_limit', 0);
		$wc_os_per_page = ($wc_os_per_page?preg_replace( '/[^0-9]/', '', $wc_os_per_page):0);
		$wc_os_per_page = ($wc_os_per_page?$wc_os_per_page:5);

			
		$wc_os_multiple_warning = __('Warning: Products in multiple categories might will produce unexpected results. It is not recommended to split product items in multiple categories.', 'woo-order-splitter');
		
        $wc_os_general_settings = get_option('wc_os_general_settings', array());
		$wc_os_auto_forced = array_key_exists('wc_os_auto_forced', $wc_os_general_settings);
		$wos_notices_css = '.wos_notice_div {
								width: 98%;
								float: left;
								text-align: center;
								font-size: 16px;
								text-transform: uppercase;
								background-color: rgba(255,255,255,0.9);
								padding: 16px 0;
								border: 1px solid rgba(122,122,122, 0.3);
								margin: 0 0 20px 0;
								displey:none;
							}
							.woocommerce-checkout .wos_notice_div {
								width:100%;
							}
							.post-type-archive-product .wos_notice_div {
								margin: 0 auto;
								width: 78%;
								float: none;
							}';
		
		if(!function_exists('wc_os_get_post_type_default')){
			function wc_os_get_post_type_default(){
				global $woocommerce, $wc_os_order_post_type;
				if( version_compare( $woocommerce->version, '8.2.0', ">=" ) ) {
					$wc_os_order_post_type = 'shop_order';				
				}
				return $wc_os_order_post_type;
			}		
		}
		
		$wc_os_cust = get_option( 'wc_os_cuztomization', array() );
		$wc_os_shipping_cost = array_key_exists('wc_os_shipping_cost', $wc_os_general_settings);
		
		$wc_os_tax_cost = true;//26/02/2024 MAKING IT CONDITIONAL OS IF PARENT ORDER HAS NO TAXES, CHILD ORDERS WILL NOT HAVE TOO 
		//array_key_exists('wc_os_tax_cost', $wc_os_general_settings);
		
		$wc_os_effect_parent = array_key_exists('wc_os_effect_parent', $wc_os_general_settings);
		$wc_os_order_items = array();
		$wos_actions_arr = array(
			
			'none' => array('action'=>__('None', 'woo-order-splitter'), 'description'=>__('Do not perform split action of any type.', 'woo-order-splitter')),
			'default' => array('action'=>__('Default', 'woo-order-splitter'), 'description'=>__('Perform split action if any of the following product items are found in the order. So every single item in the order will be added in a separate order.', 'woo-order-splitter')),
			'exclusive' => array('action'=>__('Exclusive', 'woo-order-splitter'), 'description'=>__('If any of the following product items are found in the order, separate them in new orders exclusively. So each selected item will be in a separate order.', 'woo-order-splitter')),
			'inclusive' => array('action'=>__('Inclusive', 'woo-order-splitter'), 'description'=>__('If any of the following product items are found in the order, separate them in a new order inclusively. So selected items will be grouped in another order separately.', 'woo-order-splitter')),
			'shredder' => array('action'=>__('Shredder', 'woo-order-splitter'), 'description'=>__('Group selected items in a new order. And all other items will be separated individually.', 'woo-order-splitter')),
			'io' => array('action'=>__('In stock', 'woo-order-splitter').'/'.__('out of stock', 'woo-order-splitter'), 'description'=>__('If this option is selected, plugin will separate in stock and out of stock items. So items will be grouped as in stock items in one and remaining items in other order.', 'woo-order-splitter')),
			'quantity_split' => array('action'=>__('Quantity Split', 'woo-order-splitter'), 'description'=>__('If this option is selected, multiple orders can be created with default or defined quantity ratio.', 'woo-order-splitter')),
			'subscription_split' => array('action'=>__('Group by Delivery Dates / Subscription', 'woo-order-splitter'), 'description'=>__('This option will split items according to delivery dates selected.', 'woo-order-splitter')),
			'cats' => array('action'=>__('Category Based Quantity Split', 'woo-order-splitter'), 'description'=>__('You can define quantity ratio for each category to split products into child orders.', 'woo-order-splitter')),
			'group_cats' => array('action'=>__('Grouped Categories', 'woo-order-splitter'), 'description'=>__('If this option is selected, plugin will separate category based items or group of category based items into assigned groups.', 'woo-order-splitter')),
			'groups' => array('action'=>__('Grouped Products', 'woo-order-splitter'), 'description'=>__('If this option is selected, plugin will separate items or group of items into assigned groups including variations.', 'woo-order-splitter')),
			'groups_by_meta' => array('action'=>__('Grouped Products by metadata', 'woo-order-splitter'), 'description'=>__('If this option is selected, plugin will separate items or group of items having same metadata/values.', 'woo-order-splitter')),
			'group_by_vendors' => array('action'=>__('Group by Vendors (User Role or Taxonomy)', 'woo-order-splitter'), 'description'=>__('If this option is selected, plugin will separate items or group of items by multi-vendors into assigned groups.', 'woo-order-splitter')),
			'group_by_woo_vendors' => array('action'=>__('Group by Vendors (User Terms)', 'woo-order-splitter'), 'description'=>__('If this option is selected, plugin will separate items or group of items by multi WooCommerce vendors into assigned groups.', 'woo-order-splitter')),
			
			'group_by_attributes_only' => array('action'=>__('Group by Attributes', 'woo-order-splitter'), 'description'=>__('Variable Products can have multiple attributes and you can set rules to split and/or group having selected attributes.', 'woo-order-splitter')),
			'group_by_attributes_value' => array('action'=>__('Group by Attributes Values', 'woo-order-splitter'), 'description'=>__('Variable Products will be grouped by the values they will be ordered with.', 'woo-order-splitter')),
			'group_by_acf_group_fields' => array('action'=>__('Group by ACF Field Values', 'woo-order-splitter'), 'description'=>__('Orders will be grouped according to the identical values found in product meta.', 'woo-order-splitter')),
			'group_by_partial_payment' => array('action'=>__('Group by WooCommerce Booking (Partial Payment)', 'woo-order-splitter'), 'description'=>__('Orders will be grouped according to booking date and partial or full payment mode.', 'woo-order-splitter')),			
			'group_by_order_item_meta' => array('action'=>__('Group by Order Item Meta Values', 'woo-order-splitter'), 'description'=>__('Order items related metadata will be used to group the items in splitted orders.', 'woo-order-splitter')),
			
			'group_by_gf_meta' => array('action'=>__('Group by Gravity Forms Meta from Product Page', 'woo-order-splitter'), 'description'=>__('Product items metadata will be used to group the items in splitted orders.', 'woo-order-splitter')),

		);

        $wc_os_ie_arr = array(

            'wc_os_ie_products' => array(
                'default' => array(
                    'id' => 'wc_os_ie_one',
                    'img' => 'screenshot-2.png',
                    'video' => array(),
                    'premium' => false,
					'type' => 'product_related',
                ),
                'exclusive' => array(
                    'id' => 'wc_os_ie_two',
                    'img' => 'screenshot-3.png',
                    'video' => array(),
                    'premium' => false,
					'type' => 'product_related',
                ),
                'inclusive' => array(
                    'id' => 'wc_os_ie_three',
                    'img' => 'screenshot-4.png',
                    'video' => array(),
                    'premium' => false,
					'type' => 'product_related',
                ),
                'shredder' => array(
                    'id' => 'wc_os_ie_four',
                    'img' => 'screenshot-5.png',
                    'video' => array(),
                    'premium' => false,
					'type' => 'product_related',
                ),
                'io' => array(
                    'id' => 'wc_os_ie_five',
                    'img' => 'screenshot-6.png',
                    'video' => array(),
                    'premium' => true,
					'type' => 'product_related',
                ),
                'quantity_split' => array(
                    'id' => 'wc_os_ie_seven',
                    'img' => 'screenshot-7.png',
                    'video' => array(),
                    'premium' => true,
					'type' => 'product_related',
                ),

            ),
            'wc_os_ie_groups' => array(
                'cats' => array(
                    'id' => 'wc_os_ie_six',
                    'img' => 'screenshot-8.png',
                    'class' => 'noproducts',
                    'video' => array(),
                    'premium' => true,
					'type' => 'group_based',
                ),
                'group_cats' => array(
                    'id' => 'wc_os_ie_nine',
                    'img' => array('screenshot-9.png', 'screenshot-64.png'),
                    'class' => 'noproducts',
                    'video' => array(
                        array('', 'https://www.youtube.com/embed/HvnF9uvRsGQ'),
						array('', 'https://www.youtube.com/embed/SbRLrhwgd1M')
                    ),
                    'premium' => true,
					'type' => 'group_based',
                ),
                'groups' => array(
                    'id' => 'wc_os_ie_eight',
                    'img' => 'screenshot-10.png',
                    'video' => array(
                        array('', 'https://www.youtube.com/embed/1T2ZYOzMJn8'),
                    ),
                    'premium' => true,
					'type' => 'product_related',
                ),
				'groups_by_meta' => array(
                    'id' => 'groups_by_meta',
                    'img' => '',
                    'video' => array(
                        array('', ''),
                    ),
                    'premium' => true,
					'type' => 'product_related',
                ),
                'group_by_vendors' => array(
                    'id' => 'wc_os_ie_ten',
                    'img' => 'screenshot-44.png',
                    'video' => array(
                       array('', 'https://www.youtube.com/embed/lMwE_2qkoFs'),
                       array('', 'https://www.youtube.com/embed/hMQavLSYdvI'),
                    ),
                    'class' => 'noproducts nocategories',
                    'premium' => true,
					'type' => 'group_based',
                ),
                'group_by_woo_vendors' => array(
                    'id' => 'wc_os_ie_eleven',
                    'img' => 'screenshot-43.png',
                    'video' => array(
                        array('', 'https://www.youtube.com/embed/S7EL4z-Kd7k'),
                    ),
                    'class' => 'noproducts nocategories woo_vendors',
                    'premium' => true,
					'type' => 'group_based',
                ),
                'group_by_attributes_only' => array(
                    'id' => 'group_by_attributes_only',
                    'img' => 'screenshot-30.png',
                    'video' => array(
                        array('', 'https://www.youtube.com/embed/sD1-jW1kqQs'),
                        array('', 'https://www.youtube.com/embed/Vvjmxq1Exm8'),
                    ),
                    'premium' => true,
					'type' => 'product_related',
                ),
                'group_by_attributes_value' => array(
                    'id' => 'group_by_attributes_value',
                    'img' => 'screenshot-32.png',
                    'video' => array(
                        array('', 'https://www.youtube.com/embed/xn5bemnJECQ'),
                    ),
                    'premium' => true,
					'type' => 'product_related',
                ),
				'group_by_order_item_meta' => array(
                    'id' => 'group_by_order_item_meta',
                    'img' => array('screenshot-57.png', 'screenshot-58.png'),
                    'video' => array(
                        array('', 'https://www.youtube.com/embed/Mmzhb7g477c'),
                    ),
                    'premium' => true,
					'type' => 'product_related',
                ),				
                'group_by_acf_group_fields' => array(
                    'id' => 'group_by_acf_group_fields',
                    'img' => 'screenshot-47.png',
                    'video' => array(
						array('', 'https://www.youtube.com/embed/pZfQKghwlvk'),			
					),
                    'premium' => true,
					'type' => 'group_based',
                ),
                'group_by_partial_payment' => array(
                    'id' => 'group_by_partial_payment',
                    'img' => 'screenshot-47.png',
                    'video' => array(
                        array('', ''),
                    ),
                    'premium' => true,
					'type' => 'group_based',
                ),
                'subscription_split' => array(
                    'id' => 'wc_os_subscription_split',
                    'img' => 'screenshot-53.png',
                    'video' => array(
							array('', 'https://www.youtube.com/embed/QHcih1FzPyQ')
							),
                    'premium' => true,
					'type' => 'group_based',
                ),
                'group_by_gf_meta' => array(
                    'id' => 'wc_os_group_by_gf_meta',
                    'img' => 'screenshot-63.png',
                    'video' => array(
							array('', 'https://www.youtube.com/embed/QHcih1FzPyQ')
							),
                    'premium' => true,
					'type' => 'group_based',
                ),				
            )

        );
		
        if(!$is_wc_booking){
            unset($wc_os_ie_arr['wc_os_ie_groups']['group_by_partial_payment']);
        }		
		$wc_os_meta_handling_arr = array(
            'exclusive',
            'inclusive',
			'group_by_partial_payment',
			'group_by_order_item_meta',
			'group_by_gf_meta',
        );		
		$wc_os_debug = isset($_GET['wc_os_debug']);
		
		$yith_pre_order = (in_array( 'yith-pre-order-for-woocommerce/init.php',  $wc_os_active_plugins) || in_array( 'yith-woocommerce-pre-order.premium/init.php',  $wc_os_active_plugins));
		
		$wc_os_activated = true;
		
		$wc_os_bulk_instantiated = false;
		
		$wc_os_premium_copy = 'https://shop.androidbubbles.com/product/woo-order-splitter';//https://shop.androidbubble.com/products/wordpress-plugin?variant=36439508058267';//
		$wc_os_data = get_plugin_data(__FILE__);
		
		
		define( 'WC_OS_PLUGIN_DIR', dirname( __FILE__ ) );
		
		$wc_os_orders_meta_keys_to_string = '_paid_date,_date_paid,_order_version,_billing_email,_download_permissions_granted,_edit_lock,_payment_method,_customer_ip_address,_customer_user_agent,_billing_city,_billing_state,_billing_postcode,_billing_phone,_billing_address_1,_billing_country,_billing_company,_payment_method_title,_billing_address_index,_shipping_address_index,_cart_hash,_shipping_phone,delivery_date,_recorded_sales,_new_order_email_sent,splitted_from,cloned_from,_delivery_date,_shipping_date,_wc_order_attribution_source_type,_wc_order_attribution_utm_source,_wc_order_attribution_session_entry,_wc_order_attribution_session_start_time,_wc_order_attribution_session_pages,_wc_order_attribution_session_count';
		
		
		$wc_os_pro_file = WC_OS_PLUGIN_DIR . '/pro/wcos-pro.php';
		
		
		$wc_os_pro =  file_exists($wc_os_pro_file);
		
		
		if($wc_os_pro && $wc_os_effect_parent){
			$wos_actions_arr['exclusive']['description'] = __('If any of the following selected items are found in the order, separate them in new orders exclusively. Rest of the items will remain in parent order.', 'woo-order-splitter');
			$wos_actions_arr['inclusive']['description'] = __('If any of the following selected items are found in the order, separate them in a new order inclusively. Rest of the items will remain in parent order.', 'woo-order-splitter');	
			$wos_actions_arr['shredder']['description'] = __('Keep selected items in parent order. And all other items will be separated individually.', 'woo-order-splitter');
		}
		
		if(!class_exists('WC_OS_Shipping')){

		
			include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/classes/WC_OS_Shipping.php'));
			if(class_exists('WC_OS_Shipping')){
				$wc_os_shipping = new WC_OS_Shipping();
				
			}
		
		}		
				
		if(!class_exists('WC_OS_Single_Option')){
		
			include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/classes/WC_OS_Single_Option.php'));
		
		}
		
		if(!class_exists('WC_OS_Order_Status') && !$wc_os_woo_order_status_manager && !$wc_os_cosfw){
		
			include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/classes/WC_OS_Order_Status.php'));
			if(class_exists('WC_OS_Order_Status')){
				$wc_os_order_statuses_class = new WC_OS_Order_Status();
			}
		
		}else{
		}

		include_once(realpath(WC_OS_PLUGIN_DIR . '/inc/wos-essentials.php'));
		include_once(realpath(WC_OS_PLUGIN_DIR . '/inc/wos-queries.php'));
		include_once(realpath(WC_OS_PLUGIN_DIR . '/inc/functions-crons.php'));
		include_once(realpath(WC_OS_PLUGIN_DIR . '/inc/functions.php'));
		include_once(realpath(WC_OS_PLUGIN_DIR . '/inc/wos_mailer.php'));
		
		$wc_os_is_combine = (is_admin() && array_key_exists('action', $_GET) && sanitize_wc_os_data($_GET['action'])=='combine');
		
		
		if($wc_os_pro){
			include_once(realpath($wc_os_pro_file));
		}
		
		if(is_admin()){
			
			include_once(realpath(WC_OS_PLUGIN_DIR . '/inc/wc_os_import_export.php'));
			
			//if(!is_multisite())
			add_action( 'admin_menu', 'wc_os_admin_menu' );	
			//else
			//add_action('network_admin_menu', 'wc_os_menu');
			
			
			add_filter('acf/settings/remove_wp_meta_box', '__return_false');

			if(function_exists('wc_os_plugin_linx')){
				$plugin = plugin_basename(__FILE__); 
				add_filter("plugin_action_links_$plugin", 'wc_os_plugin_linx' );	
			}
			
			if(function_exists('wc_os_admin_scripts'))
			add_action( 'admin_enqueue_scripts', 'wc_os_admin_scripts', 99 );	
			
		}else{
			if(function_exists('wc_os_front_scripts'))
			add_action( 'wp_enqueue_scripts', 'wc_os_front_scripts', 99 );	
		}
		
		
	}