<?php


class WC_OS_Order_Status extends WC_OS_Single_Option
{

    const option_name = "wc_os_order_statues";

    public $status_name;

    public $status_slug;


    public $paid;

    public $type;

    public $core_statuses;


    public function __construct($slug = '')
    {

        parent::__construct(self::option_name);
        $this->init_status($slug);
        add_filter('wc_order_statuses', array($this, 'filter_order_statuses'));

        add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'order_needs_payment_statuses' ), 11 );
        add_filter( 'woocommerce_valid_order_statuses_for_cancel',  array( $this, 'order_needs_payment_statuses' ), 11 );

        add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'filter_paid_order_statuses' ), 11 );


        add_filter( 'wc_order_is_editable', array( $this, 'is_order_editable' ), 11, 2 );


        add_action( 'woocommerce_order_status_changed', array( $this, 'regenerate_download_permissions' ), 10, 3 );

        add_filter('woocommerce_order_is_paid_statuses', array($this, 'filter_paid_order_statuses'), 11);
        add_filter('woocommerce_order_is_pending_statuses', array($this, 'filter_pending_order_statuses'), 11);
        add_filter('wc_os_translation_array', array($this, 'translation_array_filter'));
        add_action('wp_ajax_wos_delete_order_status', array($this, 'wos_delete_order_status'));
		add_action('wp_ajax_wos_update_status_colors', array($this, 'wc_os_update_status_colors'));
		
        add_action('wp_ajax_wos_save_order_status', array($this, 'wos_save_order_status'));
        add_action('init', array($this, 'register_post_status'));

       // add_action( 'admin_footer', array( $this, 'bulk_admin_footer' ), 1 );



    }
	
	public function wc_os_update_status_colors(){
		
		 $return_array = array(
            'status' => 'no'
        );

        if(!empty($_POST) && isset($_POST['status_colors'])){


            if(!isset($_POST['wc_os_nonce']) || !wp_verify_nonce($_POST['wc_os_nonce'], 'wcos_order_status_nonce')){


                wp_die(__('Sorry, your nonce did not verify.', 'woo-order-splitter'));

            }else{
				
				$status_colors = array();				
				
				parse_str($_POST['status_colors'], $status_colors);
				
				$status_colors = sanitize_wc_os_data($status_colors);				

				update_option('wc_os_status_colors', $status_colors);
				
				$return_array['status'] = true;
			}
			
		}
		
		wp_send_json($return_array);
	}

    public function register_post_status() {

        foreach ( wc_get_order_statuses() as $slug => $name ) {


            register_post_status( $slug, array(
                'label'                     => $name,
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( $name . ' <span class="count">(%s)</span>', $name . ' <span class="count">(%s)</span>', 'woo-order-splitter' ),
            ) );
        }
    }


    public function regenerate_download_permissions( $order_id, $old_order_status, $new_order_status ) {
        global $wpdb;


        $paid_order_statuses = wc_get_is_paid_statuses();

        if ( ! empty( $paid_order_statuses ) && in_array( $new_order_status, $paid_order_statuses, true ) ) {

            wc_os_delete_order_meta( $order_id, '_download_permissions_granted' );

            $wpdb->delete(
                $wpdb->prefix . 'woocommerce_downloadable_product_permissions',
                array( 'order_id' => $order_id ),
                array( '%d' )
            );

            wc_downloadable_product_permissions( $order_id );
        }
    }



    public function is_order_editable( $maybe_editable, $order ) {

        $order_status = $order->get_status();

        $all_custom_status = $this->get_all_statuses();

        if(array_key_exists($order_status, $all_custom_status)){

            $status_data = $all_custom_status[$order_status];

            return 'yes' !== $status_data['paid'];
        }

        return $maybe_editable;
    }


    public function order_needs_payment_statuses($nee_payment_status){

        $all_custom_status = $this->get_all_statuses();

        if(!empty($all_custom_status)){

            foreach ($all_custom_status as $status => $status_data ){

                if($status_data['paid'] == 'needs_payment'){

                    $nee_payment_status[] = $status;
                }

            }

        }

        return $nee_payment_status;
    }


    public function order_statuses_valid_for_payment_complete($payment_complete_status){

        $all_custom_status = $this->get_all_statuses();

        if(!empty($all_custom_status)){

            foreach ($all_custom_status as $status => $status_data ){

                if($status_data['paid'] == 'yes'){

                    $payment_complete_status[] = $status;
                }

            }

        }

        return $payment_complete_status;
    }
	
    public function order_statuses_hidden_type(){

        $all_custom_status = $this->get_all_statuses();

        if(!empty($all_custom_status)){

            foreach ($all_custom_status as $status => $status_data ){

                if($status_data['paid'] == 'hidden'){

                    $payment_complete_status[] = 'wc-'.str_replace('wc-','', $status);
                }

            }

        }

        return $payment_complete_status;
    }	

    public function order_statuses_available_only(){
		
        $all_custom_status = $this->get_all_statuses();
		
		$statuses = function_exists('wc_get_order_statuses')?wc_get_order_statuses():array();  
		$statuses_keys = array_keys($statuses);		
        if(!empty($all_custom_status) && !empty($statuses_keys)){
			
            foreach ($all_custom_status as $status => $status_data ){
				
				$slug = 'wc-'.str_replace('wc-','', $status);
				
                if($status_data['paid'] == 'hidden'){
					$key = array_search($slug, $statuses_keys);
					if($key && in_array($slug, $statuses_keys)){
                    	unset($statuses_keys[$key]);
					}
                }

            }

        }
        return $statuses_keys;
    }	




    public function filter_paid_order_statuses($paid_status){

        $all_custom_status = $this->get_all_statuses();

        if(!empty($all_custom_status)){

            foreach ($all_custom_status as $status => $status_data ){

                if($status_data['paid'] == 'yes'){

                    $paid_status[] = $status;
                }

            }

        }

        return $paid_status;
    }


    public function filter_pending_order_statuses($pending_status){

        $all_custom_status = $this->get_all_statuses();

        if(!empty($all_custom_status)){

            foreach ($all_custom_status as $status => $status_data ){

                if($status_data['paid'] == 'needs_payment'){

                    $pending_status[] = $status;
                }

            }

        }

        return $pending_status;
    }


    public function wos_save_order_status(){

        $return_array = array(
            'status' => 'no'
        );

        if(!empty($_POST) && isset($_POST['wos_order_status_obj'])){


            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcos_order_status_nonce')){


                wp_die(__('Sorry, your nonce did not verify.', 'woo-order-splitter'));

            }else{

                $status_obj = sanitize_wc_os_data($_POST['wos_order_status_obj']);
                $is_edit = isset($_POST['is_edit']);
                $all_statuses = $this->get_all_statuses();
                $slug = $status_obj['slug'];
                $name = $status_obj['name'];
                $paid = $status_obj['paid'];

                if(!array_key_exists($slug, $all_statuses) || $is_edit){

                    $this->set_status_name($name);
                    $this->set_status_slug($slug);
                    $this->set_paid($paid);

                    $return_array['status'] = $this->save_to_db();

                    if($return_array['status']){

                        ob_start();

                        $this->wcos_order_statuses_table_body();

                        $tbody = ob_get_clean();

                        $return_array['update_tbody'] = $tbody;
                        $return_array['alert_string'] = $is_edit ? __('Order status updated.', 'woo-order-splitter') : __('New order status added.', 'woo-order-splitter');


                    }


                }else{

                    $return_array['status'] = 'duplicate';

                }




            }



        }

        wp_send_json($return_array);

    }

    public function wos_delete_order_status(){

        global $wpdb;

        $return_array = array(
            'status' => false
        );
		
		$wc_os_get_post_type_default = wc_os_get_post_type_default();

        if(!empty($_POST) && isset($_POST['slug'])){


            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcos_order_status_nonce')){


                wp_die(__('Sorry, your nonce did not verify.', 'woo-order-splitter'));

            }else{

                $slug = sanitize_wc_os_data($_POST['slug']);
                $new_status = sanitize_wc_os_data($_POST['new_status']);

                if($slug && $new_status){

                    $old_status = 'wc-'.$slug;

                    $order_rows = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts}
			                        WHERE post_type = '$wc_os_get_post_type_default' AND post_status = %s", $old_status ), ARRAY_A );

                    $num_updated = 0;

                    if ( ! empty( $order_rows ) ) {
						
						//$wc_os_order_splitter = new wc_os_order_splitter;

                        foreach ( $order_rows as $order_row ) {

                            $order = wc_get_order( $order_row['ID'] );
                            /* translators: Order status updated from %1$s = old status to %2$s = new status */                            
							$order->update_status( $new_status, sprintf( __( 'Order status updated from', 'woo-order-splitter').' %1$s '.__('to', 'woo-order-splitter').' %2$s '.__('because the former status was deleted.', 'woo-order-splitter' ), $old_status, $new_status ) );
							 
							//update_wcfm_order_status($order_row['ID'], $new_status);

                            $num_updated++;
                        }
                    }

                    $return_array['status'] = $this->delete_option($slug);
                    $return_array['updated_order'] = $num_updated;
                    $return_array['alert_string'] = _n("Order status deleted successfully and $num_updated order is updated with new status", "Order status deleted successfully and $num_updated orders are updated with new status" , $num_updated, 'woo-order-splitter');


                }


            }



        }

        wp_send_json($return_array);

    }


    public function translation_array_filter($translation_array){


        $shipping_translation = array(

            'order_status_nonce' => wp_create_nonce('wcos_order_status_nonce'),
            'enter_value' => __('Enter value to add new status', 'woo-order-splitter'),
            'could_not_str' => __('Could not proceed your request. Please try again', 'woo-order-splitter'),

        );

        $translation_array = array_merge($translation_array, $shipping_translation);

        return $translation_array;
    }

    public function filter_order_statuses($order_statuses){


        $this->core_statuses = $order_statuses;
        $pairs_array = $this->get_status_slug_name_pairs();

        if(!empty($pairs_array)){

            $order_statuses = array_merge($order_statuses, $pairs_array);
        }

        return $order_statuses;

    }

    public function get_status_slug_name_pairs(){

        $all_status = $this->get_all_statuses();
        $pairs_array = array();

        if(!empty($all_status)){

            foreach ($all_status as $status_index => $single_status){

                $pairs_array['wc-' .$single_status['status_slug']] = _x($single_status['status_name'], 'Order status', 'woo-order-splitter');

            }
        }


        return $pairs_array;

    }

    public function save_to_db(){

        if($this->get_status_slug()){

            $data = array(

                'status_name' => $this->get_status_name(),
                'status_slug' => $this->get_status_slug(),
                'paid' => $this->is_paid(),


            );

            return $this->update_option($this->get_status_slug(), $data);

        }else{


            false;

        }



    }


    public function get_status_name()
    {
        return $this->status_name;
    }

    public function set_status_name($status_name)
    {
        $this->status_name = $status_name;
    }


    public function get_status_slug()
    {
        return $this->status_slug;
    }


    public function set_status_slug($status_slug)
    {
        $this->status_slug = $status_slug;
    }


    public function is_paid()
    {
        return $this->paid;
    }

    public function set_paid($paid): void
    {
        $this->paid = $paid;
    }


    public function get_core_statuses()
    {
        return $this->core_statuses;
    }


    public function set_core_statuses($core_statuses): void
    {
        $this->core_statuses = $core_statuses;
    }





    public function get_type()
    {
        return $this->type;
    }


    public function set_type($type)
    {
        $this->type = $type;
    }


    public function get_all_statuses(){
        return $this->get_main_option_data();
    }

    public function delete_all_statuses(){
        return $this->delete_main_option_data();
    }


    public function get_status_by($field, $value){

        if(property_exists($this, $field)){

            $all_statuses = $this->get_all_statuses();

            if(empty($all_statuses)){
                return  false;
            }

            array_filter($all_statuses, function (WC_OS_Order_Status $order_Status) use ($field, $value){

                $function_name = 'get_'.$field;
                return $order_Status->$function_name() == $value;

            });

        }else{

            return false;
        }

    }

    public function init_status($slug){

        $order_status = $this->get_option($slug);

        if($order_status){

            $this->set_status_name($order_status['status_name']);
            $this->set_status_slug($order_status['status_slug']);
            $this->set_type($order_status['type']);
        }



    }

    public function wcos_order_statuses_table_body(){


        $order_statuses = wc_get_order_statuses();
        $custom_statuses = $this->get_all_statuses();
        $paid_statuses = wc_get_is_paid_statuses();
		
?>

        <tbody id="wc-os-statuses-list">


        <?php

        if(!empty($order_statuses)){
			$status_colors = get_option('wc_os_status_colors');
			$status_colors = (is_array($status_colors) && array_key_exists('wc_os_colors', $status_colors)?$status_colors['wc_os_colors']:array());
			$bgc_colors = (array_key_exists('bgc', $status_colors)?$status_colors['bgc']:array());
			$fgc_colors = (array_key_exists('fgc', $status_colors)?$status_colors['fgc']:array());
			
            foreach ($order_statuses as $order_status_slug => $order_status_name){



                $status   = 'wc-' === substr( $order_status_slug, 0, 3 ) ? substr( $order_status_slug, 3 ) : $order_status_slug;

                $is_core = array_key_exists($order_status_slug, $this->core_statuses);
                $define_by_wos = array_key_exists($status, $custom_statuses);


                $current_status = $define_by_wos ? $custom_statuses[$status] : array();
                $current_status_64 = base64_encode(json_encode($current_status));

                ?>

                <tr class="iedit author-self level-0 hentry">
                    <td data-slug="<?php echo esc_attr($status); ?>" data-status="<?php echo esc_attr($current_status_64); ?>">
                        <?php echo esc_html($order_status_name); ?>
                        <?php if(!$is_core && $define_by_wos):?>
                            <p class="wos_status_action hides"><a class="edit" href=""><?php _e('Edit', 'woo-order-splitter') ?></a> | <a href="" class="delete"><?php _e('Delete', 'woo-order-splitter') ?></a></p>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($order_status_slug); ?></td>
                    <td><?php in_array($status, $paid_statuses) ? _e('Yes', 'woo_order_splitter') : _e('No', 'woo_order_splitter') ?></td>
                    <td><?php $is_core ? _e('core', 'woo_order_splitter') : _e('custom', 'woo_order_splitter') ?></td>
                    <td><input type="text" class="wc-os-bgc" value="<?php echo $bgc_val = (array_key_exists($status, $bgc_colors)?$bgc_colors[$status]:''); ?>" /><input type="color" id="<?php echo $status; ?>-bgc" name="wc_os_colors[bgc][<?php echo $status; ?>]" value="<?php echo $bgc_val; ?>" /> </td>
                    <td><input type="text" class="wc-os-fgc" value="<?php echo $fgc_val = (array_key_exists($status, $fgc_colors)?$fgc_colors[$status]:''); ?>" /><input type="color" id="<?php echo $status; ?>-fgc" name="wc_os_colors[fgc][<?php echo $status; ?>]" value="<?php echo $fgc_val; ?>" /> </td>

                </tr>



                <?php


            }
        }
        ?>

        </tbody>


        <?php




    }

    public function wcos_order_statuses_html(){

        global $wc_os_url;


        ?>

            <div class="nav-tab-content hides wos_order_statuses_section tab-order-statuses">


                <div class="status_header">
                    <h1 class="wp-heading-inline">

                        <?php _e('Order Statuses', 'woo-order-splitter') ?>

                    </h1>
                    <a class="page-title-action wos_add_new_button new">
                        <?php _e('Add New', 'woo-order-splitter') ?>
                    </a>
                    <a class="page-title-action wos_add_new_button list hides">
                        <?php _e('Show List', 'woo-order-splitter') ?>
                    </a>
                    
                    <a class="wc-os-video-tutorial-link" href="https://www.youtube.com/embed/o_iGBT5iSRA" target="_blank"><?php _e('Video Tutorial', 'woo-order-splitter') ?> <i class="fab fa-youtube"></i></a>
                </div>

                <div class="wc_os_alert hides duplicate">
                    <?php _e('Slug already in used please try a different name.', 'woo-order-splitter') ?>
                </div>

                <div class="wc_os_alert hides delete_status alert-success">
                </div>



                <div class="status_list">

                    <table class="wp-list-table widefat fixed striped table-view-list posts">
                        <thead>
                        <tr>
                            <th scope="col" id="title" class="manage-column column-title column-primary">
                                <?php _e('Name', 'woo-order-splitter') ?>
                            </th>

                            <th scope="col" id="slug" class="manage-column column-slug">
                                <?php _e('Slug', 'woo-order-splitter') ?>

                            </th>



                            <th scope="col" id="paid" class="manage-column column-slug">
                                <?php _e('Paid', 'woo-order-splitter') ?>

                            </th>


                            <th scope="col" id="type" class="manage-column column-type">
                                <?php _e('Type', 'woo-order-splitter') ?>
                            </th>
                            
                            <th scope="col" id="bgc" class="manage-column column-type">
                                <?php _e('Background Color', 'woo-order-splitter') ?>
                            </th>
                            
                            <th scope="col" id="fgc" class="manage-column column-type">
                                <?php _e('Text Color', 'woo-order-splitter') ?>
                            </th>

                        </tr>
                        </thead>

                            <?php

                                $this->wcos_order_statuses_table_body();

                            ?>


                    </table>

                </div>

                <div class="status_form hides">


                    <table>

                        <tr>
                            <td class="wos_label">
                                <?php _e('Status Name', 'woo-order-splitter') ?>
                            </td>
                            <td class="wos_input">
                                <input type="text" class="status_name">
                            </td>
                        </tr>

                        <tr>
                            <td class="wos_label">
                                <?php _e('Status Slug', 'woo-order-splitter') ?>
                            </td>
                            <td class="wos_input">
                                <input type="text" class="status_slug" readonly>
                                <p class="info"><?php _e('Max Length 20', 'woo-order-splitter') ?></p>
                            </td>
                        </tr>

                        <tr>
                            <td class="wos_label">
                                <?php _e('Paid', 'woo-order-splitter') ?>
                            </td>
                            <td class="wos_input">
                                <select class="paid">
                                    <option value="yes"><?php _e('Orders with this status have been paid.', 'woo-order-splitter') ?></option>
                                    <option value="needs_payment"><?php _e('Orders with this status require payment (similar to "pending").', 'woo-order-splitter') ?></option>
                                    <option value="no"><?php _e('Orders are neither paid nor require payment (similar to "on-hold" or "refunded").', 'woo-order-splitter') ?></option>
                                    <option value="hidden"><?php _e('Orders are paid but hidden, if you want to keep but do not want to show.', 'woo-order-splitter') ?></option>
                                </select>
                            </td>
                        </tr>


                        <tr>
                            <td>
                                <button class="button button-primary wos_save_status">
                                    <?php _e('Add New', 'woo-order-splitter') ?>
                                </button>
                                <button class="button button-primary wos_edit_status hides">
                                    <?php _e('Update', 'woo-order-splitter') ?>
                                </button>
                                <button class="button button-secondary wos_refresh_status">
                                    <?php _e('Back', 'woo-order-splitter') ?> <i class="fas fa-redo"></i>
                                </button>
                            </td>
                            <td>

                            </td>
                        </tr>

                    </table>


                </div>



                <div id="wc_os_load_modal_status" class="wc_os_modal">

                    <!-- Modal content -->
                    <div class="wc_os_modal_content">
                        <img src="<?php echo esc_url($wc_os_url).'/img/juggler.gif' ?>" alt="" style="width: 50px; height: auto" />
                    </div>

                </div>

                <div id="wc_os_delete_stuts_modal" class="wc_os_modal">

                    <!-- Modal content -->
                    <div class="wc_os_modal_content" style="background: #ffffff; border-radius: 10px; box-shadow: 0px 0px 20px black;">
                        <div class="delete_status_form">

                            <?php

                            $all_available_status = wc_get_order_statuses();

                            ?>

                            <h3>
                                <?php _e('Delete Confirmation?', 'woo-order-splitter') ?>
                            </h3>

                            <table>

                                <tr>
                                    <td class="wos_label" colspan="2">
                                        <?php _e('After deletion of order status, all orders having this status will be changed to newly assigned status.', 'woo-order-splitter') ?>
                                    </td>
                                </tr>



                                <tr>
                                    <td class="wos_label">
                                        <?php _e('Chose assigned status ', 'woo-order-splitter') ?> <strong class="del_status"></strong>
                                    </td>
                                    <td class="wos_input">
                                        <select class="change_status_selection">

                                            <?php


                                            foreach ($all_available_status as $slug => $name){

                                                echo "<option value='$slug'>$name</option>";

                                            }

                                            ?>

                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="2">
                                        <button class="button button-primary wos_delete_status">
                                            <?php _e('Confirm delete & change status', 'woo-order-splitter') ?>
                                        </button>
                                        <button class="button button-primary wos_close_del_modal">
                                            <?php _e('close', 'woo-order-splitter') ?>
                                        </button>
                                    </td>
                                    <td>

                                    </td>
                                </tr>

                            </table>

                        </div>
                    </div>

                </div>


            </div>


        <?php

    }




}

