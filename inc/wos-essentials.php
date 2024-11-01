<?php

	if(!function_exists('wc_os_db_tables')){
		function wc_os_db_tables(){
			$tbl_arr = array();
			global $wpdb;
			$result = $wpdb->get_results("SHOW TABLES");
			if(!empty($result)){
				foreach($result as $table){
					$tbl_arr[] = current((array)$table);
				}
			}
			
			return $tbl_arr;
		}
	}
	
	if(!function_exists('wc_os_table_exists')){
		function wc_os_table_exists($tbl=''){
			
			global $wpdb;
			
			$db_tables = wc_os_db_tables();
			
			return in_array($wpdb->prefix.$tbl, $db_tables);
		}
	}

	if(!function_exists('wc_os_update_order_meta')){
		function wc_os_update_order_meta($order_id=0, $meta_key_1='', $meta_value_1=''){
			//return;
			//pree($order_id);pree($meta_key_1);pree($meta_value_1);
			
			$order = (is_object($order_id)?$order_id:(is_numeric($order_id)?wc_get_order($order_id):0));
			$order_id = (is_object($order)? (method_exists($order, 'get_id')?$order->get_id():(method_exists($order, 'get_order_number')?$order->get_order_number():$order->id)) : $order_id);
			
			if(is_object($order)){
				if (!($order->customer_id>0)){
					$order = $order_id;
				}
			} else {
				if (is_numeric($order_id)) {
					$order = $order_id;
				}
			}
			
			$order = (is_object($order)?$order:(is_numeric($order)?wc_get_order($order):0));
			$order_id = (is_object($order)? (method_exists($order, 'get_id')?$order->get_id():(method_exists($order, 'get_order_number')?$order->get_order_number():$order->id)) : $order_id);
		
			if(is_object($order)){
				
				$order_data_store = WC_Data_Store::load( 'order' );
			
				$internal_meta_keys = $order_data_store->get_internal_meta_keys();
				$internal_meta_keys[] = '_cart_hash';
				
				
				$internal_meta_keys = array_unique($internal_meta_keys);
				//pree($internal_meta_keys);
				
				if(in_array($meta_key_1, $internal_meta_keys)){
					
					if(in_array($meta_key_1, array('_cart_discount','_order_tax','_order_shipping_tax','_cart_discount_tax')) && is_array($meta_value_1)) {
						$meta_value_1 = array_sum($meta_value_1);
					}
					
					$set_key = 'set_' . ltrim( $meta_key_1, '_' );
					$get_key = 'get_' . ltrim( $meta_key_1, '_' );
					
					$has_setter_or_getter = is_callable( array( $order_data_store,  $set_key) ) || is_callable( array( $order_data_store, $get_key ) );
					
					//pree($meta_key_1.' = '.$has_setter_or_getter);exit;
					
					if($has_setter_or_getter){
						
						$method_exists = method_exists($order, $set_key);
						
						//pree('$method_exists = '.$method_exists);
						
						//pree($order);
						
						if($method_exists){
							$meta_value_1 = is_array($meta_value_1)?end($meta_value_1):$meta_value_1;
							call_user_func( array( $order, $set_key ), $meta_value_1 );
							//wc_os_logger('debug', $set_key.' > '.($has_setter_or_getter?'GETTER SETTER':'NOTHING'), true);
							update_post_meta($order_id, $meta_key_1, $meta_value_1);
						}else{
							
							
							
							wc_os_delete_order_meta($order, $meta_key_1);
							$order->add_meta_data( $meta_key_1, $meta_value_1 );
							$order->save();
							
							
							
						}
						
				
					}
					
					
				}else{
					//wc_os_logger('debug', $meta_key_1.' > '.$meta_value_1.'', true);
					
					//pree('$meta_key_1 = '.$meta_key_1);
					//pree('$meta_value_1 = '.$meta_value_1);
					if(in_array($meta_key_1, array('_cart_discount','_order_tax','_order_shipping_tax','_cart_discount_tax')) && is_array($meta_value_1)) {
						$meta_value_1 = array_sum($meta_value_1);
					}

					$order->update_meta_data( $meta_key_1, $meta_value_1 );
					$order->save();
				}
				
				
			}
		} 
	}
	
	if(!function_exists('wc_os_get_order_meta')){
		function wc_os_get_order_meta($order_id=0, $meta_key_1='', $flag=true){
			
			
			//return;
			global $wpdb, $wc_os_custom_orders_table_enabled;
			
			$meta_value_1 = '';
			
			$order = (is_object($order_id)?$order_id:(is_numeric($order_id)?wc_get_order($order_id):0));
			$order_id = is_object($order) ? $order->get_id() : $order_id;
			
			//pree('#'.$order_id.' '.$meta_key_1.' ');
			//pree(get_post_meta($order_id, $meta_key_1, true));
			
			if(is_object($order)){
				
				$order_id = $order->get_id();
				
				$order_data_store = WC_Data_Store::load( 'order' );
			
				$internal_meta_keys = $order_data_store->get_internal_meta_keys();
				
				$set_key = 'set_' . ltrim( $meta_key_1, '_' );
				$get_key = 'get_' . ltrim( $meta_key_1, '_' );
				
				$has_setter_or_getter = is_callable( array( $order_data_store,  $set_key) ) || is_callable( array( $order_data_store, $get_key ) );
				
				if($has_setter_or_getter){
					
					$method_exists = method_exists($order, $get_key);
					
					//pree('$method_exists = '.$method_exists.' ('.$get_key.')');
					
					if($method_exists){
						switch($get_key){
							case 'get_order_currency':
								$get_key = str_replace('order_', '', $get_key);
							break;
						}
						
						$meta_value_1 = call_user_func( array( $order, $get_key ), $meta_key_1 );
						
						//wc_os_logger('debug', $meta_key_1.' > '.$has_setter_or_getter.'('.$meta_key_1.')', true);
						
						
					}else{
						
						
						
						
						$wc_hpos_exists = ($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders_meta') && wc_os_table_exists('wc_order_addresses'));
						if($wc_hpos_exists){
							
							$order_meta_data = $wpdb->get_results("SELECT meta_key,meta_value FROM ".$wpdb->prefix."wc_orders_meta WHERE order_id=$order_id");
							$order_address_data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_order_addresses WHERE order_id=$order_id");
							
							$meta_value_1 = array();
							
							if(!empty($order_meta_data)){
								foreach($order_meta_data as $order_meta){
									$meta_value_1[$order_meta->meta_key] = $order_meta->meta_value;
								}
							}
							
							if(!empty($order_address_data)){
								foreach($order_address_data as $address_data){
									foreach($address_data as $k=>$v){
										if(!in_array($k, array('id', 'order_id'))){
											$meta_key = ('_'.$address_data->address_type.'_'.$k);
											$meta_value_1[$meta_key] = $v;
										}
									}
								}
							}
							
							
							
						}else{
							$meta_value_1 = get_post_meta($order_id);
						}
						//pree($order_id.' ~ '.($wc_hpos_exists?'A':'B').' ~ '.$wc_os_custom_orders_table_enabled);
						//$meta_value_1 = get_post_meta($order_id);
						//pree($meta_key_1);
						if($meta_key_1){
							if(array_key_exists($meta_key_1, $meta_value_1)){
								$meta_value_1 = (is_array($meta_value_1[$meta_key_1])?current($meta_value_1[$meta_key_1]):$meta_value_1[$meta_key_1]);
								$meta_value_1 = maybe_unserialize($meta_value_1);
							}else{
								$meta_value_1 = '';
							}							
						}
						//pree($meta_value_1);
					}
					
			
				}
				
				
				switch($meta_key_1){
					case '_date_completed':
						$meta_value_1 = is_array($meta_value_1)?end($meta_value_1):$meta_value_1;
						
						if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders_meta')){
						}else{
							update_post_meta($order_id, $meta_key_1, $meta_value_1);
						}
					break;
					default:
					
						if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders_meta')){
						}else{
							if(is_array($meta_value_1)){
								foreach($meta_value_1 as $meta_value_key=>$meta_value){
									if(in_array($meta_value_key, array('_date_completed'))){
										
										$meta_value = is_array($meta_value)?end($meta_value):$meta_value;
										update_post_meta($order_id, $meta_value_key, $meta_value);
										
										$meta_value_1[$meta_value_key] = $meta_value;
									}
								}
							}
						}
					break;
				}
				
			}
			
			$meta_value_1 = ($meta_key_1?$meta_value_1:(is_array($meta_value_1)?$meta_value_1:array()));
			
			return $meta_value_1;
		}
	}	
	
	if(!function_exists('wc_os_delete_order_meta')){
		function wc_os_delete_order_meta($order_id=0, $meta_key_1='', $meta_value_1=''){
			
			$order = (is_object($order_id)?$order_id:(is_numeric($order_id)?wc_get_order($order_id):0));
			$order_id = is_object($order) ? $order->get_id() : $order_id;

			//pree($order);
			
			//wc_os_logger('debug', $order_id.' ~ '.$meta_key_1, true);
			
			if(is_object($order)){
				
				
				
				if($meta_value_1!=''){
					$order->delete_meta_data( $meta_key_1, $meta_value_1 );
					$order->save();
				}else{
					//pree($meta_key_1);
					//$order->delete_meta_data( $meta_key_1 );
					
					global $wpdb, $wc_os_custom_orders_table_enabled;
					
					if($wc_os_custom_orders_table_enabled && wc_os_table_exists('wc_orders_meta')) {
						$where = array('order_id'=>$order_id, 'meta_key'=>$meta_key_1);
						$wpdb->delete($wpdb->prefix.'wc_orders_meta', $where);
						
					}else{
					
						
						
					}
					
					delete_post_meta($order_id, $meta_key_1);
				}
				
				
			}
		}
	}	

	if(!function_exists('wc_os_create_order')){
		function wc_os_create_order($arr=array(), $flag=true){
			
			//wc_os_logger('debug', 'wc_os_create_order #', true);
				
			$debug_backtrace = debug_backtrace();
			
			$function = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];			
			$function .= ' / '.$debug_backtrace[5]['function'];			
		
			
			$order = wc_create_order();
			
			//wc_os_logger('debug', 'wc_os_create_order: '.$order->get_id().' ~ '.$function, true);
			
			
			
			return $order->get_id();
		}
	}		