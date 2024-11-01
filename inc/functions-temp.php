<?php
	if(!function_exists('wc_os_temp_tasks')){
		function wc_os_temp_tasks(){
			global $wc_os_data, $wc_os_general_settings;
			
			$wc_os_temp_task_performed = get_option('wc_os_temp_task_performed');
			$wc_os_temp_task_performed = ($wc_os_temp_task_performed?$wc_os_temp_task_performed:0);
			
			$version_int = (int)$wc_os_data['Version'];
			$performed_int = (int)$wc_os_temp_task_performed;

			if(!$performed_int || $performed_int<$version_int){
				switch($wc_os_data['Version']){
					default:
						$wc_os_general_settings['wc_os_shipping_off'] = 1;
						$wc_os_general_settings['wc_os_order_comments_off'] = 1;
						$wc_os_general_settings['wc_os_billing_off'] = 1;
						
						update_option( 'wc_os_general_settings', $wc_os_general_settings );
						update_option( 'wc_os_temp_task_performed', $wc_os_data['Version'] );
					break;
				}
			}else{

			}

		}
	}

