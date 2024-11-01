<?php

    global $wc_os_import_status, $wc_os_export_status, $wc_os_all_settings;

    $wc_os_import_status = false;
    $wc_os_export_status = false;
    $wc_os_all_settings = array();


    $wc_os_settings_array = array(

            'wc_os_general_settings' => array(),
            'wc_os_settings' => array(),
            'wc_os_cuztomization' => array(),
            'wc_os_cart_notices' => array(),
            'wc_os_rules' => array(),
            'wc_os_child_email' => array(),
			'wc_os_shipping_settings' => array(),
            'wc_os_group_meta' => array(),
            'wc_os_meta_keys' => array(),
            'wc_os_vendor_role_selection' => '',
            'wc_os_all_user_with_role' => false,
        );

    if(!empty($wc_os_settings_array)){


        foreach ($wc_os_settings_array as $option_name => $default_value){

            $wc_os_all_settings[$option_name] = get_option($option_name, $default_value);

        }

    }

   
    
 

    if(!function_exists('wc_os_settings_console')){

        function wc_os_settings_console($wc_os_all_settings){

            if(!empty($wc_os_all_settings)){


                foreach ($wc_os_all_settings as $option_name => $value){

	                if(!is_array($value)){
                        echo "<li>$option_name : $value</li>";
                    }else{

                        if(!empty($value)){

	                        echo "<li><h3>$option_name:</h3>";
	                        echo "<ul class='inner_ul'>";
	                        wc_os_settings_console($value);
	                        echo "</ul></li>";
                        }

                    }

                }

            }
        }
    }




    if(!function_exists('wc_os_import_export_init')){
        function wc_os_import_export_init(){



            global $wc_os_import_status, $wc_os_export_status, $wc_os_all_settings;



	        if(!empty($_POST) && isset($_POST['wos_export_settings_submit'])){

		        if (
			        ! isset( $_POST['wc_os_import_export_field'] )
			        || ! wp_verify_nonce( $_POST['wc_os_import_export_field'], 'wc_os_import_export_action' )
		        ) {

			        _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
			        exit;

		        } else {


			        $wc_os_all_settings_str = serialize($wc_os_all_settings);
			        $wc_os_all_settings_str = base64_encode($wc_os_all_settings_str);

			        update_option('wc_os_settings_encoded', array('time' => time(), 'settings' => $wc_os_all_settings_str));
			        $wc_os_export_status = true;


		        }
	        }


	        if(!empty($_POST) && isset($_POST['wos_import_settings_submit'])){

		        if (
			        ! isset( $_POST['wc_os_import_export_field'] )
			        || ! wp_verify_nonce( $_POST['wc_os_import_export_field'], 'wc_os_import_export_action' )
		        ) {

			        _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
			        exit;

		        } else {


			        $wc_os_settings_string = isset($_POST['wc_os_settings_string']) ? sanitize_wc_os_data($_POST['wc_os_settings_string']) : '';

			        if($wc_os_settings_string){

				        $wc_os_all_settings_import = trim($wc_os_settings_string);
				        $wc_os_all_settings_import = base64_decode($wc_os_all_settings_import);
				        $wc_os_all_settings_import = maybe_unserialize($wc_os_all_settings_import);

//				        pree($wc_os_all_settings_import);exit;


				        if(is_array($wc_os_all_settings_import) && !empty($wc_os_all_settings_import)){

					        foreach ($wc_os_all_settings_import as $option_name => $option_data){

						        update_option($option_name, $option_data);

					        }

					        $wc_os_import_status = true;

				        }

			        }



		        }

	        }

        }
    }



    if(!function_exists('wc_os_import_export_section')){

        function wc_os_import_export_section(){

            global $wc_os_import_status, $wc_os_export_status, $wc_os_all_settings;

            $wc_os_settings_encoded = get_option('wc_os_settings_encoded', array());
            $export_time = isset($wc_os_settings_encoded['time']) ? $wc_os_settings_encoded['time'] : false;
            $export_settings = isset($wc_os_settings_encoded['settings']) ? $wc_os_settings_encoded['settings'] : '';



            ?>



			<div class="sub-tab-content hides wc_os_import_export_section import_export">
            

                <?php if($wc_os_import_status): ?>
                     <div class='notice <?php echo 'notice-success'; ?> my-dismiss-notice is-dismissible'>

                         <p>
                             <?php _e('Settings imported successfully.', 'woo-order-splitter') ?>
                             <a href="<?php echo admin_url('admin.php?page=wc_os_settings') ?>"><?php _e('Click here', 'woo-order-splitter') ?></a>
	                         <?php _e(' to refresh page.', 'woo-order-splitter') ?>
                         </p>

                     </div>
                <?php endif; ?>

	            <?php if($wc_os_export_status): ?>
                    <div class='notice <?php echo 'notice-success'; ?> my-dismiss-notice is-dismissible'>

                        <p>
				            <?php _e('Settings exported successfully.', 'woo-order-splitter') ?>
                        </p>

                    </div>
	            <?php endif; ?>

                <form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

                    <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
                    <input type="hidden" name="sub_tab" value="<?php echo isset($_GET['sub_tab'])?esc_attr($_GET['sub_tab']):'0'; ?>" />


                    <?php wp_nonce_field( 'wc_os_import_export_action', 'wc_os_import_export_field' ); ?>

                    <p class="submit">
                        <input type="submit" name="wos_export_settings_submit" value="<?php _e('Export Settings', 'woo-order-splitter'); ?>" class="button button-primary" id="submit-test-mail" />
                    </p>
                    
                    <?php if($export_time): ?>

                    <p class="description"><strong><?php _e('Last Export Time', 'woo-order-splitter'); ?>:</strong> <?php echo date('d M, Y h:i:s A', $export_time) ?></p>
                    
                    <?php endif; ?>
                    
                    <textarea name="wc_os_settings_string" class="wc_os_settings_string" id="wc_os_settings_string" rows="15" spellcheck="false"><?php echo esc_textarea(trim($export_settings)); ?></textarea><br>
                    <p class="description"><?php _e('Use encoded string to import settings.', 'woo-order-splitter'); ?></p>

                    <p class="submit">
                        <input type="submit" name="wos_import_settings_submit" value="<?php _e('Import Settings', 'woo-order-splitter'); ?>" class="button button-primary" id="submit-import-settings" />
                    </p>


                </form>

                <hr />
                <h3><?php _e('Settings Summary', 'woo-order-splitter'); ?>:</h3>


                <ul>
                    <?php wc_os_settings_console($wc_os_all_settings) ?>
                </ul>


            </div>

            <?php


        }

    }