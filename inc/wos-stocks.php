<?php
	function wc_os_reduce_order_stock( $order=array(), $stock_forced=false ) { // you get an object $order as an argument
		
		$debug_backtrace = debug_backtrace();
		/*
		$function = $debug_backtrace[0]['function'];
		$function .= ' / '.$debug_backtrace[1]['function'];
		$function .= ' / '.$debug_backtrace[2]['function'];
		$function .= ' / '.$debug_backtrace[3]['function'];
		$function .= ' / '.$debug_backtrace[4]['function'];
		$function .= ' / '.$debug_backtrace[5]['function'];
		$function .= ' / '.$debug_backtrace[6]['function'];*/
		
		//wc_os_logger('debug', $function, true);
		
		//wc_os_logger('debug', '$order', true);
		//wc_os_logger('debug', $order, true);
		//wc_os_logger('debug', '$stock_forced', true);
		//wc_os_logger('debug', $stock_forced, true);
		
		
		$ret = true;
		
		global $wc_os_settings, $wc_os_general_settings;
		$order = (is_numeric($order)?wc_get_order($order):(is_object($order)?$order:array()));
		if(is_object($order)){
			
			$wc_os_reduce_stock = array_key_exists('wc_os_reduce_stock', $wc_os_general_settings);
			//wc_os_pree('$wc_os_reduce_stock: '.$wc_os_reduce_stock);//exit;
			$proceed = $wc_os_reduce_stock;//(in_array($wc_os_settings['wc_os_ie'], array('io', 'inclusive', 'default')) || $wc_os_reduce_stock);
			
			//wc_os_pree('wc_os_ie: '.$wc_os_settings['wc_os_ie']);
			//wc_os_pree('$proceed: '.$proceed, '');exit;
			//pree($proceed);exit;
			
			$order_id = (is_object($order)?$order->get_order_number():$order->get_id());
			
			//wc_os_logger('debug', '#'.$order_id.' $proceed: '.$proceed, true);
			
			
			
			
			if($proceed){
			
					
					
					//wc_os_pree('$order_id: '.$order_id, '');exit;
					
					$_order_stock_reduced = wc_os_get_order_meta($order_id, '_order_stock_reduced', true);
					
					
					//wc_os_pree('$_order_stock_reduced: '.$_order_stock_reduced);exit;
					
					//pree($_order_stock_reduced);exit;
					if($_order_stock_reduced=='yes'){
						//$ret = 'Already _order_stock_reduced';
						$ret = true;
					}
					
					$get_post_meta = wc_os_get_order_meta($order_id);
					
					$_wc_os_child_order = (array_key_exists('_wc_os_child_order', $get_post_meta)?$get_post_meta['_wc_os_child_order']:'');
					
					$_wc_os_child_order = ($_wc_os_child_order=='yes');
					
					$_wos_out_of_stock = (array_key_exists('_wos_out_of_stock', $get_post_meta)?$get_post_meta['_wos_out_of_stock']:'');
					
					$_wos_out_of_stock = ($_wos_out_of_stock==true);
					
					
					//wc_os_pree('$_wc_os_child_order: '.$_wc_os_child_order);
					//wc_os_pree('$_wos_out_of_stock: '.$_wos_out_of_stock);
					//exit;
					
					if(
								!$_wc_os_child_order
							
							&&
								is_array($get_post_meta) 
							&& 
								(
										(
												array_key_exists('split_status', $get_post_meta) 
											|| 
												array_key_exists('cloned_from', $get_post_meta)
										)
									
									||
									
										array_key_exists('_wc_os_child_order', $get_post_meta)
								)
					){
						$_wc_os_child_order = true;
					}
						
					
					//pree($get_post_meta);exit;
					//wc_os_pree('$_wc_os_child_order: '.$_wc_os_child_order);exit;
					//pree('$_wos_out_of_stock: '.$_wos_out_of_stock);exit;
					
					$proceed_further = (!$_wc_os_child_order || $wc_os_reduce_stock || $_wos_out_of_stock); // 30/01/2024 Steve Senella
					
					//wc_os_pree($proceed_further.' = ('.!$_wc_os_child_order.' || '.$wc_os_reduce_stock.' || '.$_wos_out_of_stock.')');exit;
					
					//wc_os_logger('debug', '#'.$order_id.' $proceed_further: '.$proceed_further, true);
					
					//wc_os_logger('debug', '#'.$order_id.' $proceed_further: '.$proceed_further, true);
					
					if($proceed_further){
					
						$items = $order->get_items();
						$items_ids = array();
						$child_order_notes = array();
						$wc_reduce_stock_levels = false;
						
						foreach( $items as $item_key=>$item ) {
							
							
							$updated_stock = 0;
							$product_parent = wc_get_product($item['product_id']);
							$product = wc_get_product($item['variation_id']);
							$product = (is_object($product)?$product:$product_parent);
							
							$qty_stock_status = $product->managing_stock()?$product->get_stock_quantity():$product_parent->get_stock_quantity();
							
							$item_id = ($item['variation_id']?$item['variation_id']:$item['product_id']);
							
							$_backorders = (wc_os_get_order_meta($item_id, '_backorders', true)!='no');		
							
							$_backorders_case = ($_wos_out_of_stock && $_backorders);					
							
							$wc_reduce_stock_levels = (!$_wos_out_of_stock || $_backorders_case); // 30/01/2024
							
							//wc_os_pree('$item_id: '.$item_id.' & $_backorders_case: '.$_backorders_case);
							
							
							if(!$wc_reduce_stock_levels){ continue; }
							
							//wc_os_pree('$wc_reduce_stock_levels: '.$wc_reduce_stock_levels);
							
							$items_ids[$item_key] = array(
														'product_id'=>$item['product_id'], 
														'variation_id'=>$item['variation_id'], 
														'qty_stock_status'=>$qty_stock_status, 
														'managing_stock'=>$product->managing_stock(), 
														'qty_ordered'=>$item->get_quantity()
												);
							$_sku = get_post_field( '_sku', $item['product_id'] ); 					
							
							$child_order_note = $items_ids[$item_key]['qty_stock_status'];
							
							
							//pree('qty_stock_status: '.$items_ids[$item_key]['qty_stock_status']);
							
							if($items_ids[$item_key]['qty_stock_status'] || $_backorders_case){
								
								if($stock_forced){								
									$updated_stock = ($items_ids[$item_key]['qty_stock_status']-$items_ids[$item_key]['qty_ordered']);									
								}else{						
									$updated_stock = ($items_ids[$item_key]['qty_stock_status']);							
								}
								
								//pree('$updated_stock: '.$updated_stock);
			
								wc_os_update_order_meta($item_id, '_stock', $updated_stock);
								wc_os_update_order_meta($item_id, '_wc_os_stock_quantity', 0);
								
							}
							
							//pree($updated_stock.' < '.$child_order_note);
							
							if(($updated_stock>0 || $_backorders_case) && $updated_stock<$child_order_note){
								$child_order_notes[] = $item->get_name().($_sku?' ('.$_sku.')':'').' '.$child_order_note.'&rarr;'.$updated_stock;
							}
						
						
							
						}
						//pree($child_order_notes);//exit;
						if(!empty($child_order_notes)){
							$str_stock_reduced = __( 'Stock levels reduced:', 'woocommerce' );
							$order->add_order_note($str_stock_reduced.' '.implode(', ', $child_order_notes));
							//wc_os_update_order_meta($order_id, '_order_stock_reduced', 'yes');
						}
						
						

						//exit;
					}
					
			}

		}
		
		//wc_os_logger('debug', '#'.$order_id.' '.$ret, true);
		return $ret;
	}
	add_action( 'woocommerce_reduce_order_stock', 'wc_os_reduce_order_stock', 10, 2 );
	
	add_action('woocommerce_product_set_stock', 'wc_os_stock_updated', 10, 1);
	add_action('woocommerce_variation_set_stock', 'wc_os_stock_updated', 10, 1);
	function wc_os_stock_updated( $product=array() ) {
		
		if(is_object($product)){
			$get_data = $product->get_data();
			$old_stock_quantity = $get_data['stock_quantity'];
			$new_stock_quantity = $product->get_stock_quantity();
			$stock_updated = ($new_stock_quantity-$old_stock_quantity);
			update_post_meta($product->get_id(), '_wc_os_stock_quantity', $stock_updated);
		}else{
		}
	}
	
	