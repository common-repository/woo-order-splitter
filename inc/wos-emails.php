<?php
	
	function wos_email_notification($pref=array(), $action='split'){
		
		global $wc_os_general_settings;
		
		$status = false;
		
		$myaccount_page_url = '';
		$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
		$wos_cart_notices = get_option( 'wc_os_child_email', true);
		if ( $myaccount_page_id ) {
			$myaccount_page_url = get_permalink( $myaccount_page_id );
		}		
		
		$wc_os_logger_str = 'wos_email_notification > '.$action.' > ';//.implode(', ', $pref);
		
		
		$to = array();
		$subject = '';
		$display_name = '';
		$body = 'BODY_1BODY_2BODY_3<br><br>'.get_bloginfo('name').'<br>'.get_bloginfo('description').'<br>'.get_bloginfo('wpurl');
		switch($action){
			case 'combine':

				$pref['new'] = is_array($pref['new'])?implode(' | ', $pref['new']):$pref['new'];
				$subject = __('Following orders are combined into order#', 'woo-order-splitter').' '.$pref['new'];
				$body_1 = __('Following orders are combined into one order#', 'woo-order-splitter').' <a href="'.$myaccount_page_url.'view-order/'.$pref['new'].'">'.$pref['new'].'</a>';
				$body_1 .= '<br><br><ul>';
				$order_id = 0;

				if(!empty($pref['original'])){
					foreach($pref['original'] as $order_id){

                        $post_author_id = wc_os_get_order_meta( $order_id, '_customer_user', true );

                        $body_1 .= '<li>Order# <a href="'.$myaccount_page_url.'view-order/'.$order_id.'">'.$order_id.'</a></li>';
					}
				}	

				$body_1 .= '</ul><br><br>';
				
				$body_2 = __('Order items will remain intact, same product (items) will be merged and quantity will be incremented.', 'woo-order-splitter').'<br><br>';
				
				$body_3 = ' <a href="'.$myaccount_page_url.'orders'.'">'.__('Click here', 'woo-order-splitter').'</a> '.__('to check your orders status in your account.', 'woo-order-splitter').'';
					

				$post_author = get_userdata( $post_author_id );

                if(array_key_exists('wc_os_order_combine_email', get_option('wc_os_general_settings', array()))){
					
					if(!empty($post_author) && isset($post_author->user_email)){
						$to[] = $post_author->user_email;
						$display_name = strtoupper($post_author->display_name);
					}
					
					$any_order = wc_get_order($order_id);
					if(!empty($any_order)){
						$to[] = $any_order->get_billing_email();
						$display_name = ($display_name?$display_name:$any_order->get_formatted_billing_full_name());
					}
					
					 
					
				}
				
				$body = str_replace(array('USER_NAME', 'BODY_1', 'BODY_2', 'BODY_3'), array($display_name, $body_1, $body_2, $body_3), $body);

			break;
			case 'split':
				$wc_os_logger_str = 'wos_email_notification > switch > '.$action;

				$wc_os_general_settings = get_option('wc_os_general_settings', array());
				
				$summary_email = array_key_exists('wc_os_order_split_email', $wc_os_general_settings);
				
				
                $order_id = (array_key_exists('original', $pref)?$pref['original']:0);

				$subject = __('Your order', 'woo-order-splitter').' '.__('has been split into', 'woo-order-splitter').' '.count($pref['new']).' '.'new order'.(count($pref['new'])>1?'s':'');
				
				$subject = apply_filters('wos_email_notification_hook_1', $subject);
				
				if(isset($wos_cart_notices['co_esubject']) && $wos_cart_notices['co_esubject']!=''){
					$subject = str_replace(array('ORDER_ID', 'ORDER_COUNT'), array($pref['original'], count($pref['new'])), $wos_cart_notices['co_esubject']);
				}
				
				
				
				$body_1 = $subject.' ORDERS_TABLE';
				if(isset($wos_cart_notices['co_ebody']) && trim($wos_cart_notices['co_ebody'])!=''){
					$body_1 = $wos_cart_notices['co_ebody'];
				}
				
				$wc_os_logger_str = 'wos_email_notification > split > '.$subject;
				$any_order = array();
				
				if($order_id){
					$any_order = wc_get_order($order_id);
					if(is_object($any_order) && $summary_email){
						$to[] = $any_order->get_billing_email();
						$display_name = ($display_name?$display_name:$any_order->get_formatted_billing_full_name());

					}					
				}
				
		
				$post_author_id = 0;
				if(isset($pref['new']) && !empty($pref['new'])){
					
					
					$order_received_text = __wos_change_order_received_text($pref['original']);
					
					$wc_os_display_child_number = (array_key_exists('wc_os_display_child_number', $wc_os_general_settings) && trim($wc_os_general_settings['wc_os_display_child_number'] && $wc_os_general_settings['wc_os_display_child_number']>0)?str_replace('[NUMBER_OF_CHILD_ORDERS]', $order_received_text['total'], $wc_os_general_settings['wc_os_display_child_number']).'<br /><br />':'');
					
					$order_received_text = $wc_os_display_child_number.$order_received_text['content'];

					$post_author_id = wc_os_get_order_meta( $pref['original'], '_customer_user', true );

					if(!$order_received_text){
						wc_os_update_order_meta($pref['original'], '_wos_consider_split_email', true);
					}else{
						$order_received_text = ($order_received_text?'<br /><br /><br />'.$order_received_text.'<br /><br /><br />':'');
					
						$body_1 = str_replace(array('USER_NAME', 'ORDERS_TABLE', 'ORDER_ID', 'ORDER_COUNT'), array($display_name, $order_received_text, $pref['original'], count($pref['new'])), $body_1);

						foreach($pref['new'] as $order_iter){
							if(!$post_author_id){
								$order_id = (is_array($order_iter)?current($order_iter):$order_id);
								$post_author_id = get_post_field( 'post_author', $order_id );
							}
							
						}
					}
				}	

				
				$body_2 = '';//__('Order items are intact with respective quantities.', 'woo-order-splitter').'<br><br>';
				
				$body_3 = ' <a href="'.$myaccount_page_url.'orders'.'">'.__('Click here', 'woo-order-splitter').'</a> '.__('to check your orders status in your account.', 'woo-order-splitter').'';

				if($post_author_id){
					
					$post_author = get_userdata( $post_author_id );
					$post_author = (is_object($post_author)?$post_author:array());
					$author_roles = (is_object($post_author)?$post_author->roles:array());
					$author_roles = (is_array($author_roles)?$author_roles:array());
					$admin_user = (user_can( $post_author_id, 'manage_options' ) || in_array('administrator', $author_roles));
					
                    if($summary_email){ //SUMMARY EMAIL

                        if(is_object($post_author) && isset($post_author->user_email) && !in_array($post_author->user_email, $to) && !$admin_user){

						    $to[] = $post_author->user_email;
							$display_name = strtoupper($post_author->display_name);

						}
						
					}
					
					$order_split_created_vendor = wc_os_order_vendor_email_status();

					if($order_split_created_vendor){
						$user_info = wc_os_get_order_vendor($order_id);
						if(!empty($user_info) && !in_array($user_info->user_email, $to)){
							$to[] = $user_info->user_email;
							
						}
					}	
					
					$body = str_replace(array('USER_NAME', 'BODY_1', 'BODY_2', 'BODY_3'), array($display_name, $body_1, $body_2, $body_3), $body);
					
				}
				
			break;

		}
		

		$co_efrom_name = ((isset($wos_cart_notices['co_efrom_name']) && $wos_cart_notices['co_efrom_name']!='')?$wos_cart_notices['co_efrom_name']:get_bloginfo('name'));
		$co_efrom_email = ((isset($wos_cart_notices['co_efrom_email']) && $wos_cart_notices['co_efrom_email']!='')?$wos_cart_notices['co_efrom_email']:get_bloginfo('admin_email'));
		$co_ereplyto_email = trim(((isset($wos_cart_notices['co_ereplyto_email']) && trim($wos_cart_notices['co_ereplyto_email'])!='')?$wos_cart_notices['co_ereplyto_email']:get_bloginfo('admin_email')));
		
		$headers = array(
						'Content-Type: text/html; charset=UTF-8',
						'From: '.$co_efrom_name.' <'.$co_efrom_email.'>',
						($co_ereplyto_email?'Reply-To: '.get_bloginfo('name').' <'.$co_ereplyto_email.'>':'')
					);


		
		$to = array_unique($to);
		
		
		if(!empty($to)){

			global $woocommerce;
			$mailer = $woocommerce->mailer();				

			foreach($to as $to_email){

				if(is_email($to_email)){//email_exists($to_email) || 
					
				
					
					ob_start();
					wc_get_template( 'emails/email-header.php', array( 'email_heading' => get_bloginfo('name') ) );
					echo $body;
					wc_get_template( 'emails/email-footer.php' );
					$message = ob_get_clean();
					
					$status = $mailer->send( $to_email, $subject, $message, $headers );

					
					$wc_os_logger_str = $to_email.' - '.$subject.' '.($status?__('Success', 'woo-order-splitter'):__('Failed', 'woo-order-splitter')).' #1';
					
					
					if(!$status){
						$headers = is_array($headers)?$headers:array();					
						$headers = implode('"\r\n"', $headers);
						
						$wc_os_logger_str = $to_email.' - '.$subject.' '.($status?__('Success', 'woo-order-splitter'):__('Failed', 'woo-order-splitter')).' #2';
						$status = wp_mail($to_email, $subject, $message, $headers);
						wc_os_logger('debug', 'FIRST '.($status?'SENT':'NOT SENT').' to '.$to_email, true);
						if(!$status && function_exists('mail')){
							$status = @mail($to_email, $subject, $message, $headers);
							wc_os_logger('debug', 'SECOND '.($status?'SENT':'NOT SENT').' to '.$to_email, true);
						}
						
					}
				}
				
			}
		}else{
			$wc_os_logger_str = 'Recipients are empty! -------------- SUMMARY EMAIL NOT SENT TO ANYONE';
			
		}



		return $status;
		
	}
	/**
	 * Unhook and remove WooCommerce default emails.
	 */
	

	function wc_os_redirect_mails_test(){
		if(isset($_GET['wc_os_redirect_mails'])){
			$upload_dir = wp_upload_dir();
			$basedir_html = $upload_dir['basedir'].'/'.'1616591203.html';
			$basedir_html_content = file_get_contents($basedir_html);
			$basedir_html_content_arr = unserialize($basedir_html_content);
			wc_os_redirect_mails($basedir_html_content_arr);
			exit;
		}
	}

	function wc_os_emails_to_admin_cron(){

		$emails_stack = get_option('wc_os_emails_to_admin', array());
		$emails_stack = (is_array($emails_stack)?$emails_stack:array());
		
		
		if(!empty($emails_stack)){
			$wc_mailer = WC()->mailer();
			$mails = $wc_mailer->get_emails();
			
			$sent_ids = array();
			
			$debug_backtrace = debug_backtrace();			
			$function  = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];			
						
			foreach ($emails_stack as $new_oder=>$status){
				$new_oder_status = wc_get_order($new_oder);
	
				if(is_object($new_oder_status) && !empty($new_oder_status)){
					if(!$status){
						
						$new_order_email_template = new WC_Email_New_Order();//$mails['WC_Email_New_Order'];

						$new_order_email_template->trigger($new_oder);	//New Orders Created After Split - Admin
											
						//sleep(2);
						
						$emails_stack[$new_oder] = true;
						
						$wc_os_logger_str = 'Clearing Stack Order# '.$new_oder;
						
						
						
						
						$sent_ids[] = $new_oder;
						
					}
					
				}else{
					$sent_ids[] = $new_oder;
				}
				
									
			}
			
			if(!empty($sent_ids)){
				$emails_stack = get_option('wc_os_emails_to_admin', array());
				foreach ($sent_ids as $new_oder){
					unset($emails_stack[$new_oder]);
				}
				update_option('wc_os_emails_to_admin', $emails_stack);
			}
		
		}
	}
	
	function wc_os_emails_to_resend_cron(){

		$emails_stack = get_option('wc_os_emails_to_resend_cron', array());
		$emails_stack = (is_array($emails_stack)?$emails_stack:array());

		if(!empty($emails_stack)){
			$wc_mailer = WC()->mailer();
			$mails = $wc_mailer->get_emails();
			
			$sent_ids = array();
						
			foreach ($emails_stack as $new_oder=>$status){
		
				if(!$status){
					
					$new_order_email_template = new WC_Email_New_Order();//$mails['WC_Email_New_Order'];
					wc_os_logger('debug', $new_oder.' - 331', true);
					$new_order_email_template->trigger($new_oder);	//New Orders Created After Split - Admin


					
					$emails_stack[$new_oder] = true;
					
					$wc_os_logger_str = 'Clearing Stack Order# '.$new_oder;
					
					
					
					
					$sent_ids[] = $new_oder;
					
				}
				
									
			}
			
			if(!empty($sent_ids)){
				$emails_stack = get_option('wc_os_emails_to_resend_cron', array());
				foreach ($sent_ids as $new_oder){
					unset($emails_stack[$new_oder]);
				}
				update_option('wc_os_emails_to_resend_cron', $emails_stack);
			}
			
			
	
		}
	}	
	

	if(!function_exists('wc_os_trigger_email')){
		function wc_os_trigger_email($order_id=0, $email_template='', $ret_type=''){

			
			$template = $email_template->template_base.$email_template->template_html;
			
			$ret = array('status' => false);
			
			if($order_id && file_exists($template)){
				
				if(function_exists('wc_get_template_html')){
						
					
					$wos_cart_notices = get_option( 'wc_os_child_email', true);
						
					
					
					
					
					$new_order_email_template = new $email_template();
					
					$replace = array('{site_title}', '{order_number}');
					$replace_with = array(get_bloginfo('name'), $order_id);
					
					$subject = str_replace($replace, $replace_with, $new_order_email_template->form_fields['subject']['placeholder']);
					$email_heading = str_replace($replace, $replace_with, $new_order_email_template->form_fields['heading']['placeholder']);
					
					$email = $sent_to_admin = $plain_text = '';
					$additional_content = $new_order_email_template->settings['additional_content'];
					$new_order_email_template->setup_locale();
					$new_order_email_template->object = $order = wc_get_order($order_id);					
					
					ob_start();
					include_once(realpath($template));
					$body = ob_get_clean();
					
					$message = str_replace(array('[', ']'), array('',''), $body);
					
				
					
					$address_0 = explode('<address', $message);
					$address_2 = explode('</address>', $address_0[1]);
					$body = $address_0[0].$address_2[1];					
			
					
					$co_efrom_name = ((isset($wos_cart_notices['co_efrom_name']) && $wos_cart_notices['co_efrom_name']!='')?$wos_cart_notices['co_efrom_name']:get_bloginfo('name'));
					$co_efrom_email = ((isset($wos_cart_notices['co_efrom_email']) && $wos_cart_notices['co_efrom_email']!='')?$wos_cart_notices['co_efrom_email']:get_bloginfo('admin_email'));
					$co_ereplyto_email = trim(((isset($wos_cart_notices['co_ereplyto_email']) && trim($wos_cart_notices['co_ereplyto_email'])!='')?$wos_cart_notices['co_ereplyto_email']:get_bloginfo('admin_email')));
					
					$headers = array(
						'Content-Type: text/html; charset=UTF-8',
						'From: '.$co_efrom_name.' <'.$co_efrom_email.'>',
						($co_ereplyto_email?'Reply-To: '.get_bloginfo('name').' <'.$co_ereplyto_email.'>':'')
					);
					
					
					switch($ret_type){
						default:
							$to_email = $new_order_email_template->recipient;
							
							$status = wp_mail($to_email, $subject, $body, $headers);
							wc_os_logger('debug', 'FIRST '.($status?'SENT':'NOT SENT').' to '.$to_email, true);
							
							if(!$status && function_exists('mail')){	
								$status = @mail($to_email, $subject, $body, $headers);
								wc_os_logger('debug', 'SECOND '.($status?'SENT':'NOT SENT').' to '.$to_email, true);
							}
							
							$ret = array('status' => $status);
						break;
						case 'message':
						case 'body':
							$ret = array($ret_type => $body, 'subject' => $subject, 'headers' => $headers);
						break;
					}
					
					//$wc_os_logger_str = 'B. wc_os_trigger_email Order# '.$order_id.' / '.$ret_type.' / '.$new_order_email_template->recipient;
					
				}
			}
			$wc_os_logger_str = 'Gold. wc_os_trigger_email Order# '.$order_id.' / '.$ret_type;

			
			return $ret;
			
		}
			
		
	}
	
		
	if(!function_exists('wc_os_get_admin_email')){
		function wc_os_get_admin_email(){
			

			$ret = false;
			$new_admin_email = get_option('new_admin_email');
			$new_admin_email = ($new_admin_email!='' && is_email($new_admin_email)?$new_admin_email:get_option('admin_email'));
			
			return $new_admin_email;
		}
	}

	if(!function_exists('wc_os_get_user_roles')){
		function wc_os_get_user_roles($user_email=''){		
			$roles = array();
			if(is_email($user_email)){
				$userdata = get_user_by( 'email', $user_email );
				if(is_object($userdata) && isset($userdata->roles) && !empty($userdata->roles)){
					$roles = $userdata->roles;
				}
			}
			return $roles;
		}
	}

	if(!function_exists('wc_os_is_admin_email')){
		function wc_os_is_admin_email($admin_email=array(), $admin_recipients=array()){		
			
			$is_admin = false;
			

			$admin_emails = (is_array($admin_email)?$admin_email:explode(',', $admin_email));
			$admin_emails = array_map('trim', $admin_emails);
			$admin_emails = array_map('is_email', $admin_emails);
			$admin_emails = array_filter($admin_emails);
			
			if(!empty($admin_emails)){
				foreach($admin_emails as $admin_email){
					
					if($is_admin){ continue; }
					
					
					
					$roles = wc_os_get_user_roles($admin_email);
					$is_admin = (in_array('administrator', $roles) || in_array($admin_email, $admin_recipients));
					
					
				}
			}

			return (array_key_exists(wc_os_get_admin_email(), $admin_emails) || $is_admin);
		}
	}	
	if(!function_exists('wc_os_is_vendor_email')){
		function wc_os_is_vendor_email($vendor_email=array()){		
			$vendor_emails = (is_array($vendor_email)?$vendor_email:array($vendor_email));
			$is_vendor = false;
			if(!empty($vendor_emails)){
				foreach($vendor_emails as $vendor_email){
					if($is_vendor){ continue; }
					$roles = wc_os_get_user_roles($vendor_email);
					$wc_os_selected_vendor = get_option('wc_os_vendor_role_selection', '');
					$is_vendor = in_array($wc_os_selected_vendor, $roles);		
				}
			}
			
			return $is_vendor;
		}
	}

	
	if(!function_exists('wc_os_get_order_id_from_str')){
		function wc_os_get_order_id_from_str($message='', $search_1='', $search_2=''){
			$order_id = 0;	
			$message_parts = explode($search_1, $message);
			if(count($message_parts)>1){
				
				$message_part = trim($message_parts[1]);
				
				
				
				
				
				$message_part = explode($search_2, $message_part);
				
				
				
				
				
				if(count($message_part)>1){
					$for_order_id = explode(' ', current($message_part));
					$for_child_number = explode('-', current($message_part));
					
					
					
					
					$expected_order_id = trim(current($for_order_id));
					
					
					
					
					if(count($for_child_number)>1){
						$child_number_str = trim(end($for_child_number));
						if(is_numeric($child_number_str)){
							$child_number = $child_number_str;	
						}
						
					}
					
					if(is_numeric($expected_order_id)){
						$order_id = $expected_order_id;
					}else{
						$order_id = (int) filter_var($expected_order_id, FILTER_SANITIZE_NUMBER_INT);
					}
					
					
				}
				
			}
			
			return $order_id;
		}
	}
	
	if(!function_exists('wc_os_filter_mails')){
		function wc_os_filter_mails($args, $order_id=0){
			
			
			
			$args['subject'] = (array_key_exists('subject', $args)?$args['subject']:'');
			$args['message'] = (array_key_exists('message', $args)?$args['message']:'');
			
			
			
			$return = array('return'=>true, 'to'=>$args['to'], 'order_id'=>$order_id, 'parent_id'=>0);



			global $wc_os_settings, $wc_os_general_settings, $wc_os_shipping_cost;
			$wc_os_general_settings = get_option('wc_os_general_settings', array());
			
			if(!array_key_exists('wc_os_ie', $wc_os_settings)){ return $return; }
			
			$is_customer = false;
			$is_admin = false;
			$is_vendor = false;
			
			$_wc_os_emails_sent_to = array();
			
			$order_number_pattern = trim(array_key_exists('order_number_pattern', $wc_os_general_settings)?$wc_os_general_settings['order_number_pattern']:'');
			$order_number_pattern = ($order_number_pattern?$order_number_pattern:'#');
			$order_split_created_admin = array_key_exists('wc_os_order_created_email_admin', $wc_os_general_settings);
			$order_split_created = array_key_exists('wc_os_order_created_email', $wc_os_general_settings);
			$wc_os_general_settings['wc_os_vendor_email_template'] = (array_key_exists('wc_os_vendor_email_template', $wc_os_general_settings)?$wc_os_general_settings['wc_os_vendor_email_template']:'');
			$wc_os_general_settings['wc_os_vendor_email_template'] = ($wc_os_general_settings['wc_os_vendor_email_template']==''?'WC_Email_New_Order':'');
			$vendor_split_created = ($wc_os_general_settings['wc_os_vendor_email_template']!='' && ($wc_os_settings['wc_os_ie']=='group_by_vendors' || $wc_os_settings['wc_os_ie']=='group_by_woo_vendors'));
			

			wc_os_logger('email', '618 INSIDE FILTER $order_id: '.$order_id, true);
			wc_os_logger('email', '619 INSIDE FILTER $order_number_pattern: '.$order_number_pattern, true);
			
			if(!$order_id && $order_number_pattern!='#'){
				$message = $args['message'];			
				$message = explode($order_number_pattern, $message);
				if(!empty($message)){
					foreach($message as $msg_str){
						if($order_id){ continue; }
						$msg_str = explode(' ', $msg_str);
						$msg_str = current($msg_str);
						$msg_str = trim($msg_str);
						
						$wc_os_logger_str = $order_number_pattern.' -> '.$msg_str.' - 614';

						
						if(is_numeric($msg_str)){
							$order_id = $msg_str;
						}
					}
				}

				$subject = str_replace(
					array(get_bloginfo( 'name' )),
					array('{site_title}'), 
					$args['subject']
				);
				
				$subject = explode($order_number_pattern, $subject);
				
				if(!$order_id){
					if(!empty($subject)){
						foreach($subject as $msg_str){
							if($order_id){ continue; }
							$msg_str = explode(' ', $msg_str);
							$msg_str = current($msg_str);
							$msg_str = trim($msg_str);
							
							$wc_os_logger_str = $order_number_pattern.' -> '.$msg_str.' - 639';

							
							if(is_numeric($msg_str)){
								$order_id = $msg_str;
							}
						}
					}				
				}

				$_wc_os_parent_order = wc_os_get_order_meta($order_id, '_wc_os_parent_order', true);

				if($_wc_os_parent_order=='yes'){
					
					$return['parent_id'] = $order_id;
					
					$_wc_os_masked_child_order_number = wc_os_get_order_meta($order_id, '_wc_os_masked_child_order_number', true);
					$_wc_os_masked_child_order_number = (is_array($_wc_os_masked_child_order_number)?$_wc_os_masked_child_order_number:array());

					if(is_array($_wc_os_masked_child_order_number) && !empty($_wc_os_masked_child_order_number)){
						$_wc_os_masked_child_order_number = array_unique($_wc_os_masked_child_order_number);

						$subject_last = trim(end($subject));

						foreach($_wc_os_masked_child_order_number as $masked_key=>$child_order_id){

							if($masked_key==$subject_last){
								$order_id = $child_order_id;
								$return['return'] = true;
							}
						}
						/*if(array_key_exists($subject_last, $_wc_os_masked_child_order_number)){
							$order_id = $_wc_os_masked_child_order_number[$subject_last];
						}*/
					}
				}
				
				

			}
			

			
			
			
			$splitted_order_email_counter = 0;
			$parent_order_email_counter = 0;
			
			if($order_split_created){ $splitted_order_email_counter++; }
			if($order_split_created_admin){ $splitted_order_email_counter++; }
			if($vendor_split_created){ $splitted_order_email_counter++; }
			
			$customer_email = '';
			$admin_email = '';

			$to = $args['to'];

			$tos = explode(',', $to);

			$tos = array_map('trim', $tos);

			$tos = array_map('is_email', $tos);

			
			$subject = str_replace(
							array(get_bloginfo( 'name' )),
							array('{site_title}'), 
							$args['subject']
						);
						
			$subject_parts = explode('-', $subject);
			$child_number = trim(end($subject_parts));
			$child_number = explode('#', $child_number);
			$child_number = trim(end($child_number));
			$child_number = (is_numeric($child_number)?$child_number:0);
			
			$order_id = ($order_id?$order_id:$child_number);
			
			$wc_os_parent_order_email_off = array_key_exists('wc_os_parent_order_email', $wc_os_general_settings);
			$wc_os_parent_order_email_off_admin_only = array_key_exists('wc_os_parent_order_email_admin', $wc_os_general_settings);
			$wc_os_parent_order_email_off_customer_only = array_key_exists('wc_os_parent_order_email_customer', $wc_os_general_settings);
			//$parent_order_email_counter = ($wc_os_parent_order_email_off?0:2);
			
			
			
			
			
			$wc_mailer = WC()->mailer();
			$mails = $wc_mailer->get_emails();		
			
			
			$admin_recipients = array();
			foreach($mails as $key=>$data){
				$recipient = explode(',', (string)$data->recipient);
				$recipients = array_map('trim', $recipient);
				if(!empty($recipients)){
					foreach($recipients as $recipient){
						if(!in_array($recipient, $admin_recipients)){
							$admin_recipients[] = $recipient;
						}
					}
				}
				$admin_recipients = array_filter($admin_recipients);
			}
			
			
			$WC_Email_New_Order_Subject = $mails['WC_Email_New_Order']->form_fields['subject']['placeholder'];
			$WC_Email_New_Order_parts = explode('#{', $WC_Email_New_Order_Subject);
			$WC_Email_New_Order_Subject_Prefix = trim($WC_Email_New_Order_parts[0].'#');
			
			wc_os_logger('email', '762 INSIDE FILTER $order_id: '.$order_id, true);
			
			$find = __('Order', 'woocommerce');

			if(!$order_id){
				$message = $args['message'];
				$order_id = wc_os_get_order_id_from_str($message, '['.$find.' ', ']');
				$order_id = ($order_id?$order_id:wc_os_get_order_id_from_str($message, $find.' ', ' ('));
			}
			wc_os_logger('email', '771 INSIDE FILTER $order_id: '.$order_id, true);
			if(!$order_id){
				$message = $args['message'];			
				$message = explode($find, $message);
				$message = array_map('trim', $message);
				if(!empty($message)){
					foreach($message as $msg_str){
						if(substr($msg_str, 0, 1)=='#'){							
							$order_id = wc_os_get_order_id_from_str($msg_str, '#', ']');
						}
					}
				}
				
			}
			

			$is_child_order = false;
			$is_parent_order = false;
			$has_child_order = false;
			

			
			if($order_id){
				
				$wc_os_logger_str = 'C. wc_os_filter_mails Order# '.$order_id.' > '.$return['to'];
				
				
				$post_meta = wc_os_get_order_meta($order_id);

				
				$customer_email = (array_key_exists('_billing_email', $post_meta)?$post_meta['_billing_email'][0]:$customer_email);
				
				$is_customer = ($customer_email!='' && is_email($customer_email) && in_array($customer_email, $tos));

				
				$vendor_info = wc_os_get_order_vendor($order_id);
				$is_vendor = (!empty($vendor_info) || wc_os_is_vendor_email($tos));

				$is_admin = wc_os_is_admin_email($tos, $admin_recipients);
				
				
				
				$_wc_os_emails_sent_to = wc_os_get_order_meta($order_id, '_wc_os_emails_sent_to', true);
				$_wc_os_emails_sent_to = (is_array($_wc_os_emails_sent_to)?$_wc_os_emails_sent_to:array());
				
				
				//$wc_os_logger_str = 'wc_os_filter_mails Order# '.$order_id.' / '.$return['to'].' - '.($return['return']?'GO':'NO GO');
				
				//$return['return'] = false;
				
				
				if(!empty($post_meta))
				$post_meta = array_keys($post_meta);
				
				$is_child_order = (in_array('splitted_from', $post_meta) || in_array('cloned_from', $post_meta) ||  in_array('_wc_os_child_order', $post_meta));
				$is_parent_order = (!in_array('splitted_from', $post_meta) && !in_array('cloned_from', $post_meta) &&  !in_array('_wc_os_child_order', $post_meta));
				
				
				
				if($is_parent_order){
					$wc_os_child_orders_by_order_id = wc_os_child_orders_by_order_id($order_id);
					$has_child_order = (count($wc_os_child_orders_by_order_id)>0);
					
					
					
					if(!$has_child_order){						
						$wc_os_parent_order_email_off = false;						
					}
					$parent_order_email_counter = ($wc_os_parent_order_email_off?0:2);
					
					
				}
				
			}
			
			$debug_backtrace = debug_backtrace();
			
			$function = $debug_backtrace[0]['function'];
			$function .= ' / '.$debug_backtrace[1]['function'];
			$function .= ' / '.$debug_backtrace[2]['function'];
			$function .= ' / '.$debug_backtrace[3]['function'];
			$function .= ' / '.$debug_backtrace[4]['function'];
			
			
			
			wc_os_logger('email', '857 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);
			
			if(strpos($subject, $WC_Email_New_Order_Subject_Prefix) !== false){
				//NEW ORDER EMAIL TO ADMIN
				 
				 
				 
				
				 
				if(
						$is_child_order
					
					&&
						(
				
								($order_split_created_admin && $is_admin)
							|| 
								($vendor_split_created && $is_vendor)
							||
								($order_split_created && $is_customer)
								
						)
				){
					 
				}else{
					
					if(
					 	(is_numeric($order_id) && $is_child_order)
						
						||
						
						($is_parent_order && $is_vendor)
					){
						$return['return'] = false;
						
						
					}
					 
				}
				

			}
			
			wc_os_logger('email', '900 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);
			
			
			
			
			
			
			
			if($return['return'] && $order_id){// && !in_array($to, $admin_recipients)
				
				
				/*WC_Email_Customer_On_Hold_Order 
				WC_Email_Customer_Processing_Order 
				WC_Email_Customer_Completed_Order 
				WC_Email_Customer_Refunded_Order*/
				
				
				
				//$_wc_os_parent_email_status = in_array('_wc_os_parent_email_status', $post_meta);


				
				
				
				wc_os_logger('email', '924 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);
				if($is_parent_order){
					
					$_wc_os_parent_email_status = wc_os_get_order_meta($order_id, '_wc_os_parent_email_status', true);
					$_wc_os_parent_email_status = (is_numeric($_wc_os_parent_email_status)?$_wc_os_parent_email_status:0)+1;
					//$_wc_os_parent_email_status = in_array('_wc_os_parent_email_status', $post_meta);//($_wc_os_parent_email_status==true);
											
					
					
					wc_os_logger('email', '933 INSIDE FILTER $order_id: '.$order_id.' $wc_os_parent_order_email_off: '.$wc_os_parent_order_email_off, true);
					wc_os_logger('email', '934 INSIDE FILTER $order_id: '.$order_id.' '.$_wc_os_parent_email_status.' <= '.$parent_order_email_counter, true);
					
					
					if(!$wc_os_parent_order_email_off){
						if($_wc_os_parent_email_status<=$parent_order_email_counter){
							wc_os_update_order_meta($order_id, '_wc_os_parent_email_status', $_wc_os_parent_email_status);
						}else{
							$return['return'] = false;
						}
					}else{
						
						
						$return['return'] = false;
						
					}
					
					wc_os_logger('email', '945 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);
					
				}else{
					
				}
				
				
				wc_os_logger('email', '954 INSIDE FILTER $order_id: '.$order_id.' $is_child_order '.($is_child_order?'TRUE':'FALSE').' return '.($return['return']?'TRUE':'FALSE'), true);
				
				
				if($return['return'] && ($is_child_order || is_numeric($order_id))){
					
					
				 
					
					$wc_order = new WC_Order($order_id);
					$order_status = $wc_order->get_status();
					
					
					
					$order_status = str_replace(' ', '_', ucwords(strtolower(str_replace('-', ' ', $order_status))));
					
					
					
					$status_based_email = str_replace('ORDER_STATUS', $order_status, 'WC_Email_Customer_ORDER_STATUS_Order');
					
					$consider_check = false;
					
					//$status_based_email = 'WC_Email_Customer_Processing_Order';
					
					wc_os_logger('email', '975 INSIDE FILTER $order_id: '.$order_id.' $status_based_email '.$status_based_email, true);
					
					switch($status_based_email){
						
						case 'WC_Email_Customer_On_Hold_Order':
							$WC_Email_Subject = $mails[$status_based_email]->form_fields['subject']['placeholder'];
							$consider_check = true;
						break;
						case 'WC_Email_Customer_Processing_Order':
							$WC_Email_Subject = $mails[$status_based_email]->form_fields['subject']['placeholder'];						
							$consider_check = true;
						break;
						case 'WC_Email_Customer_Completed_Order':
							$WC_Email_Subject = $mails[$status_based_email]->form_fields['subject']['placeholder'];
							$consider_check = true;
						break;
						/*case 'WC_Email_Customer_Refunded_Order':
							$WC_Email_Subject = $mails[$status_based_email]->form_fields['subject']['placeholder'];
						break;*/
					}
					
					if($status_based_email){
						$status_based_email_msg = str_replace('WC_', 'WC_OS_', $status_based_email);
						update_option($status_based_email_msg, $args['message']);
					}
					
					wc_os_logger('email', '997 INSIDE FILTER $order_id: '.$order_id.' $consider_check '.($consider_check?'TRUE':'FALSE'), true);
					
					if($consider_check){
						

						$_wc_os_child_email_status = wc_os_get_order_meta($order_id, '_wc_os_child_email_status', true);
						$_wc_os_child_email_status = (is_numeric($_wc_os_child_email_status)?$_wc_os_child_email_status:0)+1;

						
						//$order_split_created = array_key_exists('wc_os_order_created_email', $wc_os_general_settings);
						
						
						
						
						
						
						wc_os_logger('email', '1011 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);
						
						if($_wc_os_child_email_status<=$splitted_order_email_counter){
							
							wc_os_logger('email', '1017 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE').' is_customer: '.($is_customer?'YES':'NO'), true);
							
							if($is_customer){
								
								wc_os_logger('email', '1021 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE').' order_split_created: '.($order_split_created?'YES':'NO'), true);
								
								if($order_split_created){ //NEW ORDER CREATED AFTER SPLIT - EMAIL
									if(!empty($tos)){
										foreach($tos as $to){
											if(!in_array($to, $_wc_os_emails_sent_to)){
												wc_os_update_order_meta($order_id, '_wc_os_child_email_status', $_wc_os_child_email_status);
											}
										}
									}
									
								}else{
									
									if($is_child_order){ //23/02/2024 - SO IF IT IS A PARENT ORDER, LET THIS EMAIL GO
										
										
										$return['return'] = false;
										
										
									}else{
										if(
												$wc_os_parent_order_email_off
												
											||	
												($is_customer && $wc_os_parent_order_email_off_customer_only)
												
											||
												
												($is_admin && $wc_os_parent_order_email_off_admin_only)
										){
											
											$return['return'] = false;
											
										}
									}
									
								}
							}else{
								
								
								if(!empty($tos)){
									$updated_tos = array();
									foreach($tos as $to){
										if(									
											($order_split_created_admin && $is_admin)
										){
											
											if(!in_array($to, $_wc_os_emails_sent_to)){
												$updated_tos[] = $to;
												if(!$return['return']){
													$return['return'] = true;
												}
											}
											
										}
									}
									
								}
								
							}
							wc_os_logger('email', '1054 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);
							
						}else{
							
							//$return['return'] = false; //18/05/2023 - Ronny
							
						}
						
					}else{
						

						
						
						
						if(!empty($tos)){
							$updated_tos = array();
							foreach($tos as $to){
								
								$is_admin = wc_os_is_admin_email($to, $admin_recipients);
								
								
								if(									
									($order_split_created_admin && $is_admin)
								){
									
									if(!in_array($to, $_wc_os_emails_sent_to)){
										$updated_tos[] = $to;
										if(!$return['return']){
											$return['return'] = true;
										}
									}
									
								}else{
									
									
								}
								
								if(									
									($vendor_split_created && $is_vendor)
								){
									if(!in_array($to, $_wc_os_emails_sent_to)){
										$updated_tos[] = $to;
										if(!$return['return']){
											$return['return'] = true;
										}
									}
									
								}else{
									
									
								}
								
								
								
								
							}
							
							
						}
						
						
						$return['to'] = implode(',', $updated_tos);
						
						
					}
					
					
				}
				
				
				
			}else{
				
				
				
			}
			if($order_id && $return['return']){
				$tos = explode(',', $args['to']);
				if(!empty($tos)){
					foreach($tos as $to){
						$_wc_os_emails_sent_to[] = trim($to);
					}
					$_wc_os_emails_sent_to = array_unique($_wc_os_emails_sent_to);
					wc_os_update_order_meta($order_id, '_wc_os_emails_sent_to', $_wc_os_emails_sent_to);
				}
			}
			
			$return['order_id'] = $order_id;	
			
			//wc_os_logger('email', 'Order #'.$return['order_id'].' * Parent #'.$return['parent_id'].' '.($return['return']?'':'NOT').' SENT to '.$return['to'].'- 1128', true);
			
			
			$meta = wc_os_get_order_meta($order_id);
			$str_arr = array();
			if(is_array($meta) || is_object($meta)){
				foreach($meta as $k=>$v){
					$str_arr[] = '#'.$order_id.' [ '.$k.' = '.(is_array($v)?implode(', ', $v):$v).' ]';
					
				}
			}

			$_to = $return['to'];
			
			$_wc_os_parent_order = wc_os_get_order_meta($order_id, '_wc_os_parent_order', true);
			$_wc_os_child_order = wc_os_get_order_meta($order_id, '_wc_os_child_order', true);
			
			if($_wc_os_parent_order=='yes' && $wc_os_parent_order_email_off){
				$return['to'] = 'PARENT ORDER EMAILS - OFF';
			}
			if($_wc_os_child_order=='yes' && $order_split_created_admin){
				//$return['to'] = $_to;
			}
			//wc_os_logger('email', '#'.$order_id.' - Parent = '.$_wc_os_parent_order.' - '.$wc_os_parent_order_email_off.' - '.$return['to'], true);
			//wc_os_logger('email', '#'.$order_id.' - Child = '.$_wc_os_child_order.' - '.$order_split_created_admin.' - '.$return['to'], true);
			
			wc_os_logger('email', '1175 INSIDE FILTER $order_id: '.$order_id.' return '.($return['return']?'TRUE':'FALSE'), true);

			return $return;
			
		}
	}
		
		
	add_filter('wp_mail','wc_os_redirect_mails', 99, 1);
	function wc_os_redirect_mails($args=array()){
		
		if(is_array($_POST) && !empty($_POST) && array_key_exists('gform_submit', $_POST)){ return $args; }
		
		$args = is_array($args)?$args:array();
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();

		$args['subject'] = (array_key_exists('subject', $args)?$args['subject']:'');
		$args['message'] = (array_key_exists('message', $args)?$args['message']:'');		
		
		if(array_key_exists('to', $args)){
			$args['to'] = (is_array($args['to'])?implode(', ', $args['to']):$args['to']);
		}else{
			$args['to'] = '';
		}
		$wc_os_logger_str = $args['subject'].' > '.$args['to'].' > '.date('d M, Y h:i:s A');
		
		//if($args['to'])
		//wc_os_logger('debug', $args['to'].' AS RECIPIENT @ '.date('d M, Y H:i:s A').' - '.$args['subject'], true);
		
		$order_id = 0;
		
		//wc_os_logger('debug', $args['to'].'  #'.$order_id.' - 1192 message', true);
		
		if(is_admin() || empty($args)){ return $args; }
		
		
		
		
		
		$proceed_args = wc_os_filter_mails($args, $order_id);
		$order_id = $proceed_args['order_id'];
		$proceed = $proceed_args['return'];
		
		$wc_os_logger_str = 'Silver. wc_os_redirect_mails '.$args['to'].' / '.$proceed_args['to'].' / '.$args['subject'];
		//order_number_pattern

		//wc_os_logger('debug', '#'.$order_id.' - '.$wc_os_logger_str, true);
		
		$args['to'] = $proceed_args['to'];
		
		
		
		
		
		if($order_id){
			
			wc_os_logger('debug', '#'.$order_id.' - line no. 1217 $proceed='.($proceed?'YES':'NO'), true);
			
			if(!$proceed){
			
				$args['to'] = 'DENIED BY RULES - '.$args['to'];
				
			}
			
			wc_os_logger('debug', '#'.$order_id.' - line no. 1225 '.$args['to'], true);

			
			return $args;
			
		}elseif(!$order_id){
			
			return $args;
		}
		
		
		$wc_os_logger_str = '<br />+++++++ > Sending Email to '.$args['to'];
		wc_os_logger('debug', '#'.$order_id.' - '.$wc_os_logger_str, true);
		
		global $wc_os_general_settings, $is_booster_plus_for_woocommerce;  
		$wc_os_parent_order_email = !array_key_exists('wc_os_parent_order_email', $wc_os_general_settings);		

		
		

		if(!$order_id){
			$order_hash = explode(' ', $args['subject']);
	
			$order_hash = end($order_hash);
		
			
			if(is_numeric($order_hash)){
				$order_id = $order_hash;
			}else{
				$order_id = (int) filter_var($order_hash, FILTER_SANITIZE_NUMBER_INT);
				
			}			
			
			
			
			
		}
		
		wc_os_logger('debug', '#'.$order_id.' - 1251 '.$wc_os_logger_str, true);
				
		if(!$order_id){	
			$find = ' #';
			$order_hash = explode($find, $args['message']);
			if(!empty($order_hash)){
				foreach($order_hash as $tags){
					$tags = trim($tags);
					$tag_split = explode(']', $tags);
					if(count($tag_split)==2 && is_numeric($tag_split[0])){
						$order_id = $tag_split[0];
					}
				}			
			}
			wc_os_logger('debug', '#'.$order_id.' - 1265 '.$wc_os_logger_str, true);
			
			if(!$order_id){

				$find = '['.__('Order', 'woocommerce');
				

				
				$order_hash = explode($find, $args['message']);
				
				
				if(!empty($order_hash)){
					foreach($order_hash as $tags){
						$tags = trim($tags);
						$tag_split = explode(']', $tags);
						if(count($tag_split)==2 && !is_numeric($tag_split[0])){
							$order_id = $tag_split[0];
							$order_id = (int) filter_var($order_id, FILTER_SANITIZE_NUMBER_INT);
							
						}
					}			
				}
				
			}
		}
		
		
		wc_os_logger('debug', '#'.$order_id.' - 1292 '.$wc_os_logger_str, true);
		
		if($order_id){
			$order_id = (get_post_type($order_id)==$wc_os_get_post_type_default?$order_id:false);
		}
		
		
		
		

		//Booster Plus for WooCommerce - FIX - 18/05/2021
		if($is_booster_plus_for_woocommerce){//in_array('_wcj_order_number', $post_meta)){
			//$order_id = 
			$wc_os_logger_str = $order_id.' - '.get_post_type($order_id);
			
			
			$order_id_args = array(
				'numberposts'   => -1,
				'post_type'        => $wc_os_get_post_type_default,
				'post_status'        => 'any',
				'meta_key'         => '_wcj_order_number',
				'meta_value'       => $order_id
			);
			$order_id_result = get_posts( $order_id_args );
			$order_id_result = ((is_array($order_id_result) && !empty($order_id_result))?current($order_id_result):array());

			if(is_object($order_id_result)){
				$order_id = $order_id_result->ID;
				$wc_os_logger_str = $order_id.' - '.get_post_type($order_id);
				
			}


		}
		
		
		wc_os_logger('debug', '#'.$order_id.' - 1328 '.$wc_os_logger_str, true);
		
		if($order_id){
			
			
			$expected_orders = 0;
			if(class_exists('wc_os_order_splitter')){
				$expected_orders = wc_os_expected_child_orders_by_cart();				
			}
			if(
				//!$has_child_orders //DO NOT SEND EMAIL IS IRRELEVANT FOR THIS CASE + IT HAS NO CHILD ORDERS				
				$expected_orders<=1
			){				
				$wc_os_logger_str = '$expected_orders<=1 *** '.$order_id.' - '.$args['to'].' - '.$args['subject'];
				
				
				//$args['to'] = 'code.camphoenix@gmail.com';
				
				return $args;
			}			

			
			//if($wc_os_parent_order_email)
			
			
			
			$post_meta = wc_os_get_order_meta($order_id);
			
			
			
			if(!empty($post_meta)){
				$post_meta = array_keys($post_meta);
				
				
				
				$is_parent_order = (!in_array('splitted_from', $post_meta) && !in_array('cloned_from', $post_meta));
				
				$wc_os_logger_str = $order_id.' - '.($is_parent_order?'Parent Order':'Child Order').' - '.$args['to'].' - '.$args['subject'].' - '.($wc_os_parent_order_email?'Send Parent Email':'Do not send to Parent Email');
				
				
				
				
								
				if($is_parent_order){
					$wc_os_logger_str = $order_id.' - Parent Order Email - Sending Email to '.$args['to'];
					
					
					//LOGIC IS: IT IS A PARENT ORDER && (NOT YET DENIED || (DENIED ONCE && STILL NOT ALLOWED)
					if(!$wc_os_parent_order_email){
						$args['to'] = 'PARENT EMAIL - OFF - '.$args['to'];
					}
					wc_os_update_order_meta($order_id, '_wos_parent_email', $args);
				}else{
					$wc_os_logger_str = $order_id.' - Child Order - Sending Email to '.$args['to'];
					
				}
				

					
			}else{
				wc_os_update_order_meta($order_id, '_wos_allow_email_success', $args);
			}
		}
		
		/*if(!is_admin()){
			pree($args);exit;
		}*/
		
		
		return $args;
	}
	

	add_action( 'woocommerce_email', 'wc_os_unhook_wos_emails' );

	
	function wc_os_unhook_wos_emails( $email_class ) {

            global  $wc_os_general_settings;

			$disable_backorder_mail_notification = array_key_exists('wc_os_backorder_mail_notification', $wc_os_general_settings);

			if($disable_backorder_mail_notification) {
				// unhooks sending email backorders during store events
				remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
				remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );				
				remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );
			}


	}	
	
	if(!function_exists('wc_os_email_tester')){
		function wc_os_email_tester(){
			
			if(get_option('wc_os_email_log')){

				if(isset($_GET['wc-os-email-testing'])){
					echo ('MAIL TEST IN PROGRESS - START');
			
					if(function_exists('wc_os_filter_mails')){
						
						global $wpdb, $wc_os_recorded_templates_query;
						
						$wc_os_recorded_templates_query_one = $wc_os_recorded_templates_query.' LIMIT 1';
						$wc_os_recorded_template = $wpdb->get_row($wc_os_recorded_templates_query_one);
						
						$message = 'AAA';
						
						if(!empty($wc_os_recorded_template)){
							$message = $wc_os_recorded_template->option_value;
						}
						$args = array(
			
							'to' => get_bloginfo('admin_email'),
							'subject' => 'Testing Email - Order Splitter Plugin',
							'message' => $message,
			
			
						);
						wc_os_pree($message);
						$order_id = 0;
						$proceed = wc_os_filter_mails($args, $order_id);
			
						wc_os_pree('$proceed: '.($proceed['return']?'YES':'NO'));
					}
					exit;
					
				}
			
				if(isset($_GET['wc-os-quick-email-testing'])){
	
					$co_efrom_name = get_bloginfo('name').' User';
					$co_efrom_email = $co_ereplyto_email = 'test-user@gmail.com';
					$headers = array(
						'Content-Type: text/html; charset=UTF-8',
						'From: '.$co_efrom_name.' <'.$co_efrom_email.'>',
						($co_ereplyto_email?'Reply-To: '.get_bloginfo('name').' <'.$co_ereplyto_email.'>':'')
					);
					$to_email = get_bloginfo('admin_email');
					$subject = 'SPLITTER PLUGIN TEST EMAIL '.date('d M, Y H:i:s A');
					$message = '<h1>EMAIL BODY</h1><h2>TEXT</h2>';
					$status = wp_mail($to_email, 'FIRST '.$subject, $message, $headers);
					echo $status?'WENT':'NOT';
					if(!$status){
						$status = mail($to_email, 'SECOND '.$subject, $message, $headers);
					}
			
					echo $status?' & GONE':' & NOT';exit;
				}
				
			}

		
		}
	}