
<?php
	
	$wc_os_parent_order_email_off = array_key_exists('wc_os_parent_order_email', $wc_os_general_settings);
	
	$wc_os_parent_order_email_off_admin = array_key_exists('wc_os_parent_order_email_admin', $wc_os_general_settings);
	
	$wc_os_parent_order_email_off_customer = array_key_exists('wc_os_parent_order_email_customer', $wc_os_general_settings);

	if(isset($_POST['wc_os_email_settings'])){
		

	}
	
	$wc_mailer = WC()->mailer();
	$mails = $wc_mailer->get_emails();

	$wc_os_order_statuses = wc_get_order_statuses(); 
	$wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);		
	$wc_os_order_statuses_keys = array_unique($wc_os_order_statuses_keys);		
?>

<div class="nav-tab-content hides wc_os_emails_section tab-emails">




    <div class="wc_os_tab">

        <button class="wc_os_tab_links wc_os_active" data-target="wc_os_email_on_off"><?php _e('ON/OFF', 'woo-order-splitter'); ?> <i class="fas fa-toggle-on"></i></button>
        <button class="wc_os_tab_links" data-target="wc_os_email_placeholders"><?php _e('Placeholders', 'woo-order-splitter'); ?> <i class="far fa-comment-dots"></i></button>
        <button class="wc_os_tab_links" data-target="wc_os_email_smtp_settings"><?php _e('SMTP Settings', 'woo-order-splitter'); ?> <i class="fas fa-key"></i></button>
        <button class="wc_os_tab_links" data-target="wc_os_email_test"><?php _e('Test Email', 'woo-order-splitter'); ?> <i class="fas fa-envelope-open-text"></i></button>
        <button class="wc_os_tab_links" data-target="wc_os_email_templates"><?php _e('Default Emails', 'woo-order-splitter'); ?> <i class="far fa-file-alt"></i></button>
        <button class="wc_os_tab_links" data-target="wc_os_email_messages"><?php _e('Recorded Emails', 'woo-order-splitter'); ?> <i class="fas fa-lightbulb"></i></button>

    </div>

    <div class="wc_os_tab_content" id="wc_os_email_on_off">

    <form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<?php 
		

?>		
        <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
        
    
        <?php wp_nonce_field( 'wc_os_child_email', 'wc_os_child_email_field' ); ?>


	    <h3><?php _e('ON/OFF Settings', 'woo-order-splitter'); ?></h3>
        
        


	    <?php if(function_exists('wos_email_notification')): ?>
            <ul>

                <li>
                	<ul>
                        
                        <li>
                            <input id="wc_os_parent_order_email" name="wc_os_email_settings[wc_os_parent_order_email]" type="checkbox" value="1" <?php echo($wc_os_parent_order_email_off?'checked="checked"':''); ?> /><label for="wc_os_parent_order_email"><?php _e("Do not send parent order email to anyone",'woo-order-splitter'); ?> <small>(<?php _e("If order does not split, normal order emails will as usual.",'woo-order-splitter'); ?>)</small></label>
                        </li>
                        
                        <li>
                            <input id="wc_os_parent_order_email_admin" name="wc_os_email_settings[wc_os_parent_order_email_admin]" type="checkbox" value="1" <?php echo($wc_os_parent_order_email_off_admin?'checked="checked"':''); ?> /><label for="wc_os_parent_order_email_admin"><?php _e("Do not send parent order email to admin only",'woo-order-splitter'); ?> </label>
                        </li>
                        
                        <li>
                            <input id="wc_os_parent_order_email_customer" name="wc_os_email_settings[wc_os_parent_order_email_customer]" type="checkbox" value="1" <?php echo($wc_os_parent_order_email_off_customer?'checked="checked"':''); ?> /><label for="wc_os_parent_order_email_customer"><?php _e("Do not send parent order email to customer only",'woo-order-splitter'); ?></label>
                        </li>
                        
					</ul>                        

                </li>

                <li>
                    <h4><?php _e('Send email notifications to customer', 'woo-order-splitter'); ?></h4>

                </li>

                <li>
                    <input id="wc_os_order_combine_email" name="wc_os_email_settings[wc_os_order_combine_email]" type="checkbox" value="1" <?php echo(array_key_exists('wc_os_order_combine_email', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_combine_email"><?php _e("Orders Combined",'woo-order-splitter'); ?></label>

                </li>


                <li>
                    <input id="wc_os_order_split_email" name="wc_os_email_settings[wc_os_order_split_email]" type="checkbox" value="1" <?php echo(array_key_exists('wc_os_order_split_email', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_split_email"><?php _e("Orders Splitted Summary",'woo-order-splitter'); ?></label>

                </li>

                <li>
                    <input id="wc_os_order_created_email" name="wc_os_email_settings[wc_os_order_created_email]" type="checkbox" value="1" <?php echo(array_key_exists('wc_os_order_created_email', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_created_email"><?php _e("New Orders Created After Split",'woo-order-splitter'); ?></label>

<?php if(function_exists('wc_os_get_statuses_select_html')){wc_os_get_statuses_select_html('wc_os_status_setting', 'customer_email', $wc_os_order_statuses_keys, false, __("Send email on",'woo-order-splitter').' ', 'email');} ?>
                </li>

                <li>
                    <h4><?php _e('Send email notifications to admin', 'woo-order-splitter'); ?></h4>

                </li>

                <li>
                    <input id="wc_os_order_created_email_admin" name="wc_os_email_settings[wc_os_order_created_email_admin]" type="checkbox" value="1" <?php echo(array_key_exists('wc_os_order_created_email_admin', $wc_os_general_settings)?'checked="checked"':''); ?> /><label for="wc_os_order_created_email_admin"><?php _e("New Orders Created After Split",'woo-order-splitter'); ?></label>

                </li>
                
                
                
<?php 
				
				if($wc_os_settings['wc_os_ie']=='group_by_vendors' || $wc_os_settings['wc_os_ie']=='group_by_woo_vendors'):
				
?>
                <li>
                    <h4><?php _e('Send email notifications to Vendor', 'woo-order-splitter'); ?> <small>(<?php _e('Group by Vendors Selected?', 'woo-order-splitter'); ?>)</small></h4>

                </li>

                <li>
                    <input id="wc_os_order_created_email_vendor" name="wc_os_email_settings[wc_os_order_created_email_vendor]" type="checkbox" value="1" <?php echo (array_key_exists('wc_os_order_created_email_vendor', $wc_os_general_settings)?'checked="checked"':''); ?> />
<?php
				$wc_mailer = WC()->mailer();
                $mails = $wc_mailer->get_emails();
				if(!empty($mails)){
					$mail_templates = array_keys($mails);
?>
				<select name="wc_os_email_settings[wc_os_vendor_email_template]" title="<?php _e("New Orders Created After Split",'woo-order-splitter'); ?>">					
                	<option value=""><?php _e("Default",'woo-order-splitter'); ?> - WC_Email_New_Order</option>
<?php                
					foreach($mail_templates as $mail_template){
?>						                    
                    <option <?php selected((array_key_exists('wc_os_vendor_email_template', $wc_os_general_settings) && $wc_os_general_settings['wc_os_vendor_email_template']==$mail_template));?> value="<?php echo esc_attr($mail_template); ?>"><?php echo esc_html($mail_template); ?></option>
                    
<?php 
					}
?>
				</select>
                
<?php					
				}else{
?>
<label for="wc_os_order_created_email_vendor"><?php _e("New Orders Created After Split",'woo-order-splitter'); ?> 



<?php					
				}
?>				
                    <small>(<?php _e('Vendor should get emails instead of admin?', 'woo-order-splitter'); ?>)</small></label>

                </li>    
<?php 
				endif;
?>
				            

            </ul>

			<input name="wc_os_email_settings[_extra]" type="hidden" value="1" />
            <p class="submit">
                <input type="submit" name="wos_save_changes_submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit-changes" />
            </p>
            
            <div class="alert alert-warning mt-3 clearfix" role="alert">
			  <?php _e('Order emails should have the following format in body content so the outgoing email can be recogonized as a parent or child email with the order number.', 'woo-order-splitter'); ?><br /><br />
              <?php _e('Subject:', 'woo-order-splitter'); ?> <strong><?php _e('order #000000', 'woo-order-splitter'); ?></strong><br />
              <?php _e('Message:', 'woo-order-splitter'); ?> <strong><?php _e('[Order #000000]', 'woo-order-splitter'); ?></strong><br /><br />

              
              <?php _e('Alternatively, provide the pattern here:', 'woo-order-splitter'); ?> <strong><input type="text" name="wc_os_email_settings[order_number_pattern]" value="<?php echo (array_key_exists('order_number_pattern', $wc_os_general_settings)?$wc_os_general_settings['order_number_pattern']:''); ?>" /></strong> <small>(e.g. <u>Order #</u> or <u>n°</u> or <u>Order number:</u>)</small>
              
            </div>
<?php
	
?>
	    <?php endif; ?>
	</form>
    </div>


    <div class="wc_os_tab_content hides" id="wc_os_email_placeholders">
    
    <form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
    
        <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
        
    
        <?php wp_nonce_field( 'wc_os_child_email', 'wc_os_child_email_field' ); ?>
        
        
        <div class="wos_cart_notices_left">

            <h3><?php _e('Child Order Page Labels:', 'woo-order-splitter'); ?></h3>

		    <?php $wc_os_child_email = get_option( 'wc_os_child_email', array()); ?>

            <ul>

                <li><input type="text" name="wc_os_child_email[co_heading]" placeholder="<?php _e('Child Order', 'woo-order-splitter'); ?>" value="<?php echo array_key_exists('co_heading', $wc_os_child_email) ? $wc_os_child_email['co_heading'] : ''; ?>" /></li>

                <li><input type="text" name="wc_os_child_email[co_number]" placeholder="<?php _e('Order number', 'woo-order-splitter'); ?>" value="<?php echo array_key_exists('co_number', $wc_os_child_email) ? $wc_os_child_email['co_number'] : ''; ?>" />
                <small><?php _e('Item Meta Example', 'woo-order-splitter'); ?>: <?php _e('Order number', 'woo-order-splitter'); ?> # ORDER_ID - [taxonomy:location,term:_stock_location]</small>
                
                </li>

                <li><label for="co_total"><input type="checkbox" name="wc_os_child_email[co_total]" id="co_total" value="1" <?php echo checked(array_key_exists('co_total', $wc_os_child_email)); ?> /><?php _e('Orders Grand Total Display', 'woo-order-splitter'); ?></label></li>

            </ul>

            <small><?php _e('Note: Keep these fields empty to work with default values.', 'woo-order-splitter'); ?></small>

        </div>

        <div class="wos_cart_notices_left">

            <h3><?php _e('Child Order Emails Text:', 'woo-order-splitter'); ?></h3>

		    <?php $wc_os_child_email = get_option( 'wc_os_child_email', array()); ?>



            <ul>

                <li>

                    <input type="text" name="wc_os_child_email[co_efrom_name]" placeholder="<?php _e('Email From Name:', 'woo-order-splitter'); ?> <?php echo esc_attr(get_bloginfo('name')); ?>" value="<?php echo array_key_exists('co_efrom_name', $wc_os_child_email) ? $wc_os_child_email['co_efrom_name']: ''; ?>" />

                </li>



                <li>

                    <input type="text" name="wc_os_child_email[co_efrom_email]" placeholder="<?php _e('Email From Email:', 'woo-order-splitter'); ?> <?php echo esc_attr(get_bloginfo('admin_email')); ?>" value="<?php echo array_key_exists('co_efrom_email', $wc_os_child_email) ? $wc_os_child_email['co_efrom_email']: ''; ?>" />

                </li>



                <li>

                    <input type="text" name="wc_os_child_email[co_ereplyto_email]" placeholder="<?php _e('Email ReplyTo Email:', 'woo-order-splitter'); ?> <?php echo esc_attr(get_bloginfo('admin_email')); ?>" value="<?php echo array_key_exists('co_ereplyto_email', $wc_os_child_email) ? $wc_os_child_email['co_ereplyto_email']: ''; ?>" />

                </li>



                <li>

                    <input type="text" name="wc_os_child_email[co_esubject]" placeholder="<?php _e('Email Subject:', 'woo-order-splitter'); ?> <?php _e('Order# ORDER_ID splitted into ORDER_COUNT new order(s)', 'woo-order-splitter'); ?>" value="<?php echo array_key_exists('co_esubject', $wc_os_child_email) ? $wc_os_child_email['co_esubject'] : ''; ?>" />

                </li>



                <li>

                    <textarea type="text" name="wc_os_child_email[co_ebody]" placeholder="<?php _e('Email Body:', 'woo-order-splitter'); ?> <?php _e('ORDERS_TABLE', 'woo-order-splitter'); ?>" rows="5" style="width: 100%;"><?php echo esc_textarea(array_key_exists('co_ebody', $wc_os_child_email) ? $wc_os_child_email['co_ebody']: ''); ?></textarea>

                </li>
                
                
				<li>
                <small><?php _e('Note: Keep these fields empty to work with default values.', 'woo-order-splitter'); ?></small>
                </li>
                <li><br />
                
                <?php _e('Shortcodes', 'woo-order-splitter'); ?>: USER_NAME | ORDERS_TABLE | ORDER_ID | ORDER_COUNT
                	
                </li>


            </ul>



            



        </div>

        <p class="submit">
            <input type="submit" name="wos_save_changes_submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit-templates" />
        </p>
        
	</form>

    </div>

    <div class="wc_os_tab_content hides" id="wc_os_email_smtp_settings">

        <?php

        $wc_os_smtp = isset($wc_os_child_email['smtp']) ? $wc_os_child_email['smtp'] : array();
		
		$smtp_status = array_key_exists('status', $wc_os_smtp) ? true : false;
		$smtp_host = array_key_exists('host', $wc_os_smtp) ? $wc_os_smtp['host'] : '';
		$smtp_username = array_key_exists('username', $wc_os_smtp) ? $wc_os_smtp['username'] : '';
		$smtp_password = array_key_exists('password', $wc_os_smtp) ? $wc_os_smtp['password'] : '';
		$smtp_port = array_key_exists('port', $wc_os_smtp) ? $wc_os_smtp['port'] : '';

        ?>



        <h3><?php _e('SMTP Settings', 'woo-order-splitter'); ?></h3>

        <form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        
        <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />
        
        
        <?php wp_nonce_field( 'wc_os_child_email', 'wc_os_child_email_field' ); ?>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><?php _e('Turn SMTP', 'woo-order-splitter'); ?> <small>(<?php _e('ON', 'woo-order-splitter'); ?>/<?php _e('OFF', 'woo-order-splitter'); ?>)</small>:</th>
                <td>
                    <input type="checkbox" name="wc_os_child_email[smtp][status]" value="1" <?php checked($smtp_status); ?> />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('SMTP Host', 'woo-order-splitter'); ?>:</th>
                <td>
                    <input type="text"  name="wc_os_child_email[smtp][host]" placeholder="<?php _e('SMTP Host', 'woo-order-splitter'); ?>"
                           value="<?php echo ($smtp_host && $smtp_username && $smtp_password)?$smtp_host:''; ?>">
                    <p class="description"><?php _e('Your mail server', 'woo-order-splitter'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('SMTP Username', 'woo-order-splitter'); ?>:</th>
                <td>
                    <input type="text" autocomplete="off"  name="wc_os_child_email[smtp][username]" placeholder="<?php _e('SMTP Username', 'woo-order-splitter'); ?>"
                           value="<?php echo ($smtp_host && $smtp_username && $smtp_password)?$smtp_username:''; ?>">
                    <p class="description"><?php _e('The username to login to your mail server', 'woo-order-splitter'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('SMTP Password', 'woo-order-splitter'); ?>:</th>
                <td>
                    <input type="password"  name="wc_os_child_email[smtp][password]" placeholder="<?php _e('SMTP Password', 'woo-order-splitter'); ?>"
                           value="<?php echo ($smtp_host && $smtp_username && $smtp_password)?$smtp_password:''; ?>">
                    <p class="description"><?php _e('The password to login to your mail server', 'woo-order-splitter'); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('SMTP Port', 'woo-order-splitter'); ?>:</th>
                <td>
                    <input type="text"  name="wc_os_child_email[smtp][port]" placeholder="465"
                           value="<?php echo ($smtp_host && $smtp_username && $smtp_password)?$smtp_port:''; ?>">
                </td>
            </tr>            
            
            <tr valign="top">
            	<th></th>
                <td>
                <input type="submit" name="wc_os_save_changes_submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit-smtp" />
                <div style="font-size:12px; margin:20px 0 0 0;">
	        	<?php _e('Do you need SMTP Server?', 'woo-order-splitter'); ?>
        		</div>
                </td>
                
            </tr>



            </tbody>
        </table>




	</form>

    </div>

    <div class="wc_os_tab_content hides" id="wc_os_email_test">

        <?php

            $email_status = false;
            $email_post = false;
            if(isset($_POST['wos_test_email_submit'])){

				global $woocommerce;
				$mailer = $woocommerce->mailer();


                $email_post = true;
                $wos_test_email_to = isset($_POST['wos_test_email_to']) ? sanitize_wc_os_data($_POST['wos_test_email_to']) : '';
                $wos_test_email_subject = trim((isset($_POST['wos_test_email_subject']) ? sanitize_wc_os_data($_POST['wos_test_email_subject']) : '').' '.date('d M, Y H:i:s A'));
                $wc_os_test_email_message = isset($_POST['wc_os_test_email_message']) ? sanitize_wc_os_data($_POST['wc_os_test_email_message']) : '';
				$headers = "Content-Type: text/html\r\n";

				ob_start();
				wc_get_template( 'emails/email-header.php', array( 'email_heading' => get_bloginfo('name') ) );
				echo $wc_os_test_email_message;
				wc_get_template( 'emails/email-footer.php' );
				$message = ob_get_clean();


                //$wc_os_mailer = WOS_Mailer::get_instance();

                //$result = $wc_os_mailer->send_mail($wos_test_email_to, $wos_test_email_subject, $message);
				$result = $mailer->send( $wos_test_email_to, $wos_test_email_subject, $message, $headers );

				
				if(!is_array($result)){
					$result = array('status_msg' => __('Successfully sent.', 'woo-order-splitter'), 'status' => true);
				}

                $email_status = $result['status'];
				$status_msg = $result['status_msg'];


				

            }


        ?>



        <h3><?php _e('Test Email', 'woo-order-splitter'); ?></h3>

        <p><?php _e('You can use this section to send an email from your server using the above configured SMTP details to see if the email gets delivered.', 'woo-order-splitter') ?></p>

        <?php if($email_post): ?>
        <div class='notice <?php echo esc_attr($email_status ? 'notice-success' : 'notice-error'); ?> my-dismiss-notice is-dismissible'>
            <p>

                <?php
                    if($email_status):
					
						if($status_msg){
							echo $status_msg;
						}else{
	                        _e('Successfully sent.', 'woo-order-splitter');
						}

                    else:
						if($status_msg){
							echo $status_msg;
						}else{
	                    	_e($result['error'], 'woo-order-splitter');
						}


                    endif;
                ?>



            </p>
        </div>
        <?php endif; ?>



        <form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        
        <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />        
        
        <?php wp_nonce_field( 'wc_os_child_email', 'wc_os_child_email_field' ); ?>
        
        <table class="form-table">
            <tbody><tr valign="top">
                <th scope="row">
                    <?php _e('To', 'woo-order-splitter'); ?>:
                </th>
                <td>
                    <input id="wos_test_email_to" autocomplete="off" type="email" class="ignore-change" name="wos_test_email_to" value=""><br>
                    <p class="description"><?php _e("Enter the recipient's email address", 'woo-order-splitter'); ?></p>

                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Subject', 'woo-order-splitter'); ?>:
                </th>
                <td>
                    <input id="wos_test_email_subject" type="text" class="ignore-change" name="wos_test_email_subject" value=""><br>
                    <p class="description"><?php _e('Enter a subject for your message', 'woo-order-splitter'); ?></p>

                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Message', 'woo-order-splitter'); ?>:
                </th>
                <td>
                    <textarea name="wc_os_test_email_message" id="wos_test_email_message" rows="5" spellcheck="false"></textarea><br>
                    <p class="description"><?php _e('Write your email message', 'woo-order-splitter'); ?></p>
               </td>
            </tr>
            
            <tr>
            	<th></th>
                <td>
                    <p class="submit">
                        <input type="submit" name="wos_test_email_submit" value="<?php _e('Send Test Email', 'woo-order-splitter'); ?>" class="button button-primary" id="submit-test-mail" />
                    </p>                
                </td>
            </tr>


            </tbody>
        </table>


        
        </form>
</div>
<div class="wc_os_tab_content hides" id="wc_os_email_templates">        
        
    	<form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<?php
if(!empty($mails)){
?>
<ul>
<?php	
		foreach($mails as $key=>$data){
			
			$subject = (array_key_exists('subject', $data->form_fields) && array_key_exists('placeholder', $data->form_fields['subject'])?$data->form_fields['subject']['placeholder']:'');
			
			/*$subject = str_replace(
							array('{site_title}'), 
							array(get_bloginfo( 'name' )),							
							$subject
						);	*/
			
?>
<li>
<strong><?php echo wc_os_highlighter($key); ?> | <?php echo wc_os_highlighter($data->title); ?></strong>
<div><?php echo wc_os_highlighter($data->description); ?></div>
<div><?php echo wc_os_highlighter($subject); ?></div>

</li>
<?php			
			
		}
?>
</ul>
<?php		
	}	
?>        
        </form>        
    </div>
    
    <div class="wc_os_tab_content hides" id="wc_os_email_messages">        
        
    	<form class="ignore" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<?php
	
	$wc_os_recorded_templates = $wpdb->get_results($wc_os_recorded_templates_query);
	
	if(!empty($wc_os_recorded_templates)){
?>
<ul>
<?php		
		foreach($wc_os_recorded_templates as $wc_os_recorded_template){
?>
<li data-id="<?php echo $wc_os_recorded_template->option_id; ?>"><?php echo $wc_os_recorded_template->option_name; ?></li>
<?php			
		}
?>
</ul>
<?php		
	}
?>        
        
    	</form>
    </div>
</div>    