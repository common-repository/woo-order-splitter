<?php


class WC_OS_Shipping
{

    private $selection;
    private $settings;
    private $order_total_default;
    private $order_total_criteria;
    private $original_order;

    /**
     * WC_OS_Shipping constructor.
     */
    public function __construct()
    {



        $this->settings = get_option('wc_os_shipping_settings', array());
        add_filter('wc_os_translation_array', array($this, 'wc_os_translation_array_filter'));
        add_action('wp_ajax_wc_os_save_shipping_settings', array($this, 'wc_os_save_shipping_settings_callback'));
		add_action('wp_ajax_wc_os_update_parcel_shipping_cost', array($this, 'wc_os_update_parcel_shipping_cost_callback'));
		add_action('wp_ajax_nopriv_wc_os_update_parcel_shipping_cost', array($this, 'wc_os_update_parcel_shipping_cost_callback'));
		
        add_action('wc_os_after_order_split', array($this, 'wc_os_shipping_management_after_split'), 8, 2);
        add_action('wc_os_before_order_split', array($this, 'wc_os_shipping_management_before_split'));
		if(!is_admin()){

			add_filter('woocommerce_package_rates', array($this, 'wc_os_custom_shipping_costs'), 10, 2 );
			add_action( 'woocommerce_cart_calculate_fees', array($this, 'wc_os_add_checkout_fee_for_gateway'), 10 );
			
		}


    }

    public function is_parent_modified(){

        global $wc_os_effect_parent, $wc_os_settings;

        $method = $wc_os_settings['wc_os_ie'];

        $array_modified = array(
                'io', 'inclusive', 'exclusive', 'groups'
        );

        return !wc_os_order_removal() && $wc_os_effect_parent && in_array($method, $array_modified);

    }

    /**
     * @return mixed
     */
    public function get_order_total_default()
    {
        $this->order_total_default = array_key_exists('wc_os_order_total_default', $this->settings) ? $this->settings['wc_os_order_total_default']: 0;

        return $this->order_total_default;
    }



    /**
     * @return mixed
     */
    public function get_order_total_criteria()
    {
        $this->order_total_criteria = array_key_exists('wc_os_order_total_criteria', $this->settings) ? $this->settings['wc_os_order_total_criteria']: array();

        return $this->order_total_criteria;
    }



    /**
     * @return mixed
     */
    public function get_selection()
    {

        $this->selection = array_key_exists('wc_os_shipping_selection', $this->settings) ? $this->settings['wc_os_shipping_selection']: 'no';

        return $this->selection;
    }

    /**
     * @return mixed
     */
    public function get_settings()
    {
        return $this->settings;
    }

    public function wc_os_shipping_management_before_split($original_order){

        $original_order = new WC_Order($original_order);
        $this->original_order = $original_order;

    }


    public function wc_os_shipping_management_after_split($child_order_ids, $original_order_id){



        switch ($this->get_selection()){



            case 'equal':
			

				$this->wc_os_equal_shipping($child_order_ids, $original_order_id);

			break;
			
			case 'equal_plus':
				
                $this->wc_os_equal_plus_shipping($child_order_ids, $original_order_id);

			break;

            case 'shipping_class':
				
                $this->wc_os_shipping_class_shipping($child_order_ids, $original_order_id);
				
				

			break;
			
			case 'category_shipping_class':
				
				$this->wc_os_category_based_shipping_class($child_order_ids, $original_order_id);

			break;				
				

            case 'clone':

                $this->wc_os_clone_shipping($child_order_ids, $original_order_id);

			break;			
			

            case 'order_total':


                $this->wc_os_order_total_shipping($child_order_ids, $original_order_id);

			break;


            default:



			break;

        }


    }



	public function wc_os_update_parcel_shipping_cost_callback(){
		
		$result = array(
            'status' => false,
			'msg' => '',
			'parcels_count' => 0
        );

        if(isset($_POST['wc_os_actual_shipping_cost']) && isset($_POST['wc_os_total_shipping_cost'])){

            if(!isset($_POST['wc_os_nonce']) || !wp_verify_nonce($_POST['wc_os_nonce'], 'wc_os_customer_permitted_action')){

                $result['msg'] = __('Sorry, your nonce did not verify.', 'woo-order-splitter');

            }else{
				global $wc_os_order_splitter;
				$parcels = $wc_os_order_splitter->split_order_logic(null, true, true);
				if(is_object($parcels) || is_array($parcels)){
					$result['parcels_count'] = count($parcels);
				}
				$result['status'] = true;
				
				
					

            }


        }

        wp_send_json($result);		
	}




    public function wc_os_save_shipping_settings_callback(){
		
		

        $result = array(
            'status' => false,
        );

        if(isset($_POST['wc_os_shipping_settings'])){

            if(!isset($_POST['wc_os_nonce']) || !wp_verify_nonce($_POST['wc_os_nonce'], 'wc_os_shipping_nonce_action')){

                wp_die(__('Sorry, your nonce did not verify.', 'woo-order-splitter'));

            }else{



                $wc_os_shipping_settings = sanitize_wc_os_data($_POST['wc_os_shipping_settings']);
				$wc_os_parcel_shipment = sanitize_wc_os_data($_POST['wc_os_parcel_shipment']);
				$wc_os_shipping_platforms = sanitize_wc_os_data($_POST['wc_os_shipping_platform_arr']);

				update_option('wc_os_parcel_shipment', $wc_os_parcel_shipment);
				update_option('wc_os_shipping_platforms', $wc_os_shipping_platforms);
                $result['status'] = update_option('wc_os_shipping_settings', $wc_os_shipping_settings);
				
				
				

            }


        }

        wp_send_json($result);


    }

    public function wc_os_translation_array_filter($translation_array){


        $shipping_translation = array(

            'shipping_nonce' => wp_create_nonce('wc_os_shipping_nonce_action'),
            'shipping_selection' => $this->get_selection(),
            'shipping_settings' => $this->settings,
            'negative_number_msg' => __('All values should be greater than or equal to zero.', 'woo-order-splitter')

        );

        $translation_array = array_merge($translation_array, $shipping_translation);

        return $translation_array;
    }

    public function wc_os_shipping_tab_html(){

        global $wc_os_url, $is_woocommerce_shipping_usps, $wc_os_parcel_shipment, $wc_os_parcel_shipment_adjustment;
		$wc_os_shipping_platforms = get_option('wc_os_shipping_platforms');
		$wc_os_shipping_platforms = (is_array($wc_os_shipping_platforms)?$wc_os_shipping_platforms:array());	
?>

        <div class="nav-tab-content hides wc_os_shipping tab-shipping">






                <h3 class="nav-tab-wrapper" >

                    <a class="nav-tab nav-tab-active" data-selection="no">
                        <?php _e("Default Shipping",'woo-order-splitter'); ?>
                    </a>

                    <a class="nav-tab" data-selection="equal">
                        <?php _e("Equal Shipping",'woo-order-splitter'); ?>
                    </a>

                    <a class="nav-tab" data-selection="product_based_shipping_class">
                        <?php _e("Product Based Shipping Class",'woo-order-splitter'); ?>
                    </a>
                    
                    <a class="nav-tab" data-selection="category_based_shipping_class">
                        <?php _e("Category Based Shipping Class",'woo-order-splitter'); ?>
                    </a>

                    <a class="nav-tab" data-selection="clone">
                        <?php _e("Clone Shipping",'woo-order-splitter'); ?>
                    </a>

                    <a class="nav-tab" data-selection="order_total">
                        <?php _e("Order Total Based",'woo-order-splitter'); ?>
                    </a>
                    
                    <a class="nav-tab" data-selection="other_shipping">
                        <?php _e("Other Shipping Compatibilities",'woo-order-splitter'); ?>
                    </a>
                                       
                    

                </h3>


                <div class="wc_os_alert alert-success hides">
                    <?php _e("Settings saved successfully.",'woo-order-splitter'); ?>
                </div>


                <div class="sub-tab-content no_shipping">

                    <div class="wc_os_alert">
                        <?php _e("Shipping cost selection options",'woo-order-splitter'); ?>
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="" id="wc_os_default_shipping">
                        <label for="wc_os_default_shipping"><?php _e("Default",'woo-order-splitter'); ?></label>
                    </div>
                    
                    <div class="equal_plus">
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="equal_plus" id="wc_os_equal_shipping_plus">
                        <label for="wc_os_equal_shipping_plus"><?php _e("Charge shippping cost for each parcel",'woo-order-splitter'); ?> <span><?php _e("Shipping Fee Label",'woo-order-splitter'); ?>: <input placholder="<?php _e("Parcel Shipment",'woo-order-splitter'); ?>" name="wc_os_parcel_shipment" value="<?php echo $wc_os_parcel_shipment; ?>" /></span> <small title="<?php _e("Premium Feature",'woo-order-splitter'); ?>">(<?php _e("This option will require:",'woo-order-splitter'); ?> <?php _e("Split Overview on Checkout Page",'woo-order-splitter'); ?>)</small></label>
                        
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="no" id="wc_os_no_shipping">
                        <label for="wc_os_no_shipping"><?php _e("No shipping cost for child orders",'woo-order-splitter'); ?></label>
                    </div>



                    



                </div>

                <div class="sub-tab-content equal_shipping hides">

					
                    
                    <div class="wc_os_alert">
                        <?php _e("Shipping cost will be divided equally among child orders.",'woo-order-splitter'); ?>
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="equal" id="wc_os_equal_shipping">
                        <label for="wc_os_equal_shipping"><?php _e("Divide parent order shipping cost among child orders equally",'woo-order-splitter'); ?></label>
                    </div>

                </div>

                <div class="sub-tab-content product_based_shipping_class hides">

                    <div class="wc_os_alert">
                        <?php _e("Shipping cost will be added as per selected shipping class in individual product.",'woo-order-splitter'); ?>
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="shipping_class"  id="wc_os_product_shipping_class">
                        <label for="wc_os_product_shipping_class"><?php _e("Add shipping cost as per shipping class selected for Product",'woo-order-splitter'); ?></label>
                    </div>

    <a class="wc-os-gc-shipping-class-order" href="https://ps.w.org/woo-order-splitter/assets/screenshot-59.png" target="_blank"><img src="<?php echo esc_url($wc_os_url); ?>img/shipping-class-1.png" /></a>
    <div class="alert alert-warning" role="alert">
      <?php _e('Assign a shipping class to a product under shipping section using Edit Product page.', 'woo-order-splitter'); ?>      
    </div>
                 
                 
                </div>
                
                <div class="sub-tab-content category_based_shipping_class hides">

                    <div class="wc_os_alert">
                        <?php _e("Shipping cost will be added as per selected shipping class for a product category.",'woo-order-splitter'); ?>
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="category_shipping_class"  id="wc_os_category_shipping_class">
                        <label for="wc_os_category_shipping_class"><?php _e("Add shipping cost as per shipping class selected for a Category",'woo-order-splitter'); ?></label>
                    </div>
                    

	
    <a class="wc-os-gc-shipping-class-order" href="https://ps.w.org/woo-order-splitter/assets/screenshot-60.png" target="_blank"><img src="<?php echo esc_url($wc_os_url); ?>img/shipping-class-2.png" /></a>
    <div class="alert alert-warning" role="alert">
      <?php _e('Assign a shipping class to a category using Edit Category page.', 'woo-order-splitter'); ?>      
    </div>
                    

                </div>

                <div class="sub-tab-content clone_shipping hides">

                    <div class="wc_os_alert">
                        <?php _e("Shipping cost will be cloned from parent order.",'woo-order-splitter'); ?>
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="clone" id="wc_os_clone_shipping">
                        <label for="wc_os_clone_shipping"><?php _e("Clone shipping cost from parent order",'woo-order-splitter'); ?></label>
                    </div>


                </div>

                <div class="sub-tab-content order_total hides">

                    <div class="wc_os_alert">
                        <?php _e("Shipping cost will be added as per defined criteria according to the order total.",'woo-order-splitter'); ?>
                    </div>

                    <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="order_total" id="wc_os_order_total_shipping">
                        <label for="wc_os_order_total_shipping"><?php _e("Apply shipping cost as per each order total",'woo-order-splitter'); ?></label>
                    </div>


                    <div>
                        <label for="wc_os_order_total_default" class="wc_os_label"><?php _e("Default Value (If no criteria matched)",'woo-order-splitter'); ?></label>
                        <input type="number" min="0" id="wc_os_order_total_default" value="0">
                    </div>

                    <div>
                        <h3><?php _e("Define Criteria",'woo-order-splitter'); ?></h3>
                    </div>


                    <div class="wc_os_order_total_criteria_wrapper hides">
                        <span>
                           <?php _e("Min total",'woo-order-splitter'); ?>
                        </span>

                        <span>
                           <?php _e("Max total",'woo-order-splitter'); ?>
                        </span>

                        <span>
                           <?php _e("Shipping Cost",'woo-order-splitter'); ?>
                        </span>

                    </div>

                    <div class="wc_os_order_total_criteria_row_sample hides" >

                        <input type="number" min="0" value="" name="min">
                        <input type="number" min="0" value="" name="max">
                        <input type="number" min="0" value="" name="cost">
                        <button class="button button-secondary wc_os_del_total_order_criteria" title="<?php _e("Delete row",'woo-order-splitter'); ?>"><span class="dashicons dashicons-trash"></span></button>


                    </div>

                    <div class="wc_os_add_total_order_criteria">
                        <button class="button button-secondary" title="<?php _e("Add row",'woo-order-splitter'); ?>"><span class="dashicons dashicons-plus"></span> <?php _e("Add",'woo-order-splitter'); ?></button>
                    </div>




                </div>
                
                
                <div class="sub-tab-content other_shipping hides">

                    <div class="wc_os_alert">
                        <?php _e("Shipping cost will be cloned from parent order.",'woo-order-splitter'); ?>
                    </div>

                    
                    
                    <ul>

                    	<li title="<?php echo $is_woocommerce_shipping_usps?__('WooCommerce USPS Shipping - Installed','woo-order-splitter'):__('WooCommerce USPS Shipping - Missing','woo-order-splitter'); ?>"><?php echo ($is_woocommerce_shipping_usps?'<i class="far fa-check-circle wc-os-green"></i>':'<i class="far fa-times-circle wc-os-red"></i>'); ?> WooCommerce USPS Shipping
                        
                        <div>
                        <input type="radio" name="wc_os_shipping_selection" class="wc_os_shipping_selection" value="other" id="wc_os_other_shipping">
                        <label for="wc_os_other_shipping"><?php _e("Clone shipping cost from parent order",'woo-order-splitter'); ?></label>
                    	</div>
                    
                        </li>
                        
                        <li>
                        
                        <div>
                        


                        <input <?php checked(in_array('shipstation', $wc_os_shipping_platforms)); ?>  type="checkbox" name="wc_os_shipping_platforms[]" class="wc_os_shipping_selection" value="shipstation" id="wc_os_shipstation" />

                        <label for="wc_os_shipstation" style="font-size:16px; font-weight:bold; font-style:italic;">ShipStati<i class="fas fa-cog" style="color:#92c43e"></i>n</label>
                        
                        
	                    </div>
                        
                        
                        </li>
                        
                    </ul>


                </div>



                <button class="button button-primary save_changes_btn">
                    <?php _e("Save Changes",'woo-order-splitter'); ?>
                </button>


                <div id="wc_os_load_modal" class="wc_os_modal">

                    <!-- Modal content -->
                    <div class="wc_os_modal_content">
                        <img src="<?php echo esc_url($wc_os_url.'/img/juggler.gif'); ?>" alt="" style="width: 50px; height: auto" />
                    </div>

                </div>





        </div>




        <?php

    }

    public function wc_get_min_max_cost($get_default_criteria){

        $min_max_array = array();
        if(!empty($get_default_criteria)){

            $min_index = -1;
            $max_index = -1;
            $min_val = -1;
            $max_val = -1;

            foreach ($get_default_criteria as $index => $single_criteria){

                $min = $single_criteria['min'];
                $max = $single_criteria['max'];
                $cost = $single_criteria['cost'];

                if($min_val == -1){
                    $min_val = $min;
                    $min_index = $index;


                }else{

                    if($min < $min_val){

                        $min_val = $min;
                        $min_index = $index;
                    }


                }

                if($max_val == -1){

                    $max_val = $max;
                    $max_index = $index;



                }else{


                    if($max > $max_val){

                        $max_val = $max;
                        $max_index = $index;

                    }

                }



            }

            $min_max_array = array(

                    'min' => $min_index,
                    'max' => $max_index,
            );

        }


        return $min_max_array;



    }

    public function wc_os_get_shipping_by_order_total($order_total){

        $default_shipping = $this->get_order_total_default();
        $get_default_criteria = $this->get_order_total_criteria();


        usort($get_default_criteria, function($a, $b){

            return $a['min'] > $b['min'];

        });

        $cost_actual = null;


        if(!empty($get_default_criteria)){

            foreach ($get_default_criteria as $index => $single_criteria){

                $min = $single_criteria['min'];
                $max = $single_criteria['max'];
                $cost = $single_criteria['cost'];

                if(!$min){


                    if($order_total <= $max){

                        $cost_actual = $cost;
                        break;

                    }

                }else{

                    if(!$max){

                        if($order_total >= $min){

                            $cost_actual = $cost;
                            break;

                        }

                    }else{

                        if($order_total > $min && $order_total < $max){

                            $cost_actual = $cost;
                            break;
                        }

                    }

                }

            }





        }else{
            $cost_actual = $default_shipping;

        }

        if(is_null($cost_actual)){


            $cost_actual = $default_shipping;


            $min_max = $this->wc_get_min_max_cost($get_default_criteria);

            if(!empty($min_max)){

                $min_val = $get_default_criteria[$min_max['min']]['min'];
                $min_cost = $get_default_criteria[$min_max['min']]['cost'];
                $max_val = $get_default_criteria[$min_max['max']]['max'];
                $max_cost = $get_default_criteria[$min_max['max']]['cost'];

                if($order_total < $min_val){
                    $cost_actual = $min_cost;
                }elseif($order_total > $max_val){

                    $cost_actual = $max_cost;

                }

            }


        }



        return $cost_actual;

    }

    public function wc_os_save_shipping_item_against_order(WC_Order_Item_Shipping $shipping_item){

        global $wpdb;
        $order_item_table = $wpdb->prefix.'woocommerce_order_items';
        $order_item_meta_table = $wpdb->prefix.'woocommerce_order_itemmeta';
        $order_id = $shipping_item->get_order_id();
        $order_item_type = 'shipping';
        $order_item_name = $shipping_item->get_name();

        $method_id = $shipping_item->get_method_id();
        $instance_id = $shipping_item->get_instance_id();
        $cost = $shipping_item->get_total();
        $total_tax = $shipping_item->get_total_tax();
        $taxes = maybe_serialize($shipping_item->get_taxes());

        $insert_query = "INSERT INTO $order_item_table (order_item_name, order_item_type, order_id) VALUES ('$order_item_name', '$order_item_type', $order_id)";

        $result = $wpdb->query($insert_query);

        if($result){

            $item_id_query = "SELECT order_item_id FROM $order_item_table WHERE order_id = $order_id AND order_item_type = '$order_item_type'";

            $order_item_id = $wpdb->get_var($item_id_query);

            $insert_query_meta = "INSERT INTO $order_item_meta_table (order_item_id, meta_key, meta_value) 
                            VALUES
                            
                             ($order_item_id, 'method_id', '$method_id'),
                             ($order_item_id, 'instance_id', '$instance_id'),
                             ($order_item_id, 'cost', '$cost'),
                             ($order_item_id, 'total_tax', $total_tax),
                             ($order_item_id, 'taxes', '$taxes')
                             
                             
                             ";

            $wpdb->query($insert_query_meta);


            $item_id_query = "SELECT * FROM $order_item_meta_table WHERE order_item_id = $order_item_id";

            $order_item_id = $wpdb->get_results($item_id_query);





        }





    }

    public function wc_os_get_shipping_item($parent_shipping_data){


        $new_shipping_item = new WC_Order_Item_Shipping();

        $new_shipping_item->set_instance_id($parent_shipping_data['instance_id']);
        $new_shipping_item->set_method_id($parent_shipping_data['method_id']);
        $new_shipping_item->set_method_title('WC OS Custom Shipping');
        $new_shipping_item->set_name('WC OS Custom Shipping');
        return $new_shipping_item;

    }

    private function wc_os_equal_shipping($child_order_ids, $original_order_id)
    {
		

        $parent_order = $this->original_order;
		$parent_order = (!empty($parent_order)?$parent_order:wc_get_order($original_order_id));
		
        $parent_shipping = $parent_order->get_items('shipping');
        $parent_shipping_id = current(array_keys($parent_shipping));
        if(!$parent_shipping){return;}

        $parent_shipping = current($parent_shipping);
        $parent_shipping_data = $parent_shipping->get_data();
        $shipping_total = $parent_shipping_data['total'];

        if(empty($child_order_ids)){return;}

        if($this->is_parent_modified()){
			
            wc_delete_order_item($parent_shipping_id);
            $child_order_ids[] = $original_order_id;
        }

        $total_child = count($child_order_ids);
        $each_child_shipping = $shipping_total/$total_child;
        $each_child_shipping = round($each_child_shipping, 2);


        if(!empty($child_order_ids)){
            foreach ($child_order_ids as $child_order_id){

                $child_order = new WC_Order($child_order_id);
				$this->wc_os_shipping_remove_shipping_items($child_order);


                $shipping_item = $this->wc_os_get_shipping_item($parent_shipping_data);

                $shipping_item->set_method_title($parent_shipping_data['method_title']);
                $shipping_item->set_name($parent_shipping_data['name']);
                $shipping_item->set_total($each_child_shipping);
                $shipping_item->set_order_id($child_order_id);
                $child_order->add_item($shipping_item);
				
                //$child_order->calculate_totals();
                $child_order->save();


            }


        }



    }

    private function wc_os_order_total_shipping($child_order_ids, $original_order_id)
    {


		
		
        $parent_order = $this->original_order;
		$parent_order = (!empty($parent_order)?$parent_order:wc_get_order($original_order_id));
		
        $parent_shipping = $parent_order->get_items('shipping');
        $parent_shipping_id = current(array_keys($parent_shipping));
        if(!$parent_shipping){return;}
        $parent_shipping = current($parent_shipping);
        $parent_shipping_data = $parent_shipping->get_data();

        if($this->is_parent_modified()){
			
            wc_delete_order_item($parent_shipping_id);
            $child_order_ids[] = $original_order_id;
        }

        if(!empty($child_order_ids)){
            foreach ($child_order_ids as $child_order_id){

                $child_order = new WC_Order($child_order_id);
				$this->wc_os_shipping_remove_shipping_items($child_order);
				
                $child_order_total = $child_order->get_total();

                $shipping_total = $this->wc_os_get_shipping_by_order_total($child_order_total);

                $shipping_item = $this->wc_os_get_shipping_item($parent_shipping_data);

                $shipping_item->set_total($shipping_total);
                $shipping_item->set_order_id($child_order_id);
				

                $child_order->add_item($shipping_item);
				
                //$child_order->calculate_totals();
                $child_order->save();


            }


        }



    }
	
	
	private function wc_os_equal_plus_shipping($child_order_ids, $original_order_id){

		
		global $wc_os_parcel_shipment, $wc_os_parcel_shipment_adjustment;
		
        $parent_order = $this->original_order;
		$parent_order = (!empty($parent_order)?$parent_order:wc_get_order($original_order_id));
		
		
		
        $parent_shipping = $parent_order->get_items('shipping');
        $parent_shipping_id = current(array_keys($parent_shipping));
        if(!$parent_shipping){return;}

        $parent_shipping = current($parent_shipping);
        $parent_shipping_data = $parent_shipping->get_data();
        $shipping_total = $parent_shipping_data['total'];

        if(empty($child_order_ids)){return;}

        if($this->is_parent_modified()){
			
            wc_delete_order_item($parent_shipping_id);
            $child_order_ids[] = $original_order_id;
        }


        if(!empty($child_order_ids)){

            foreach ($child_order_ids as $child_order_id){
				

							

                $child_order = wc_get_order($child_order_id);
				
				
				
				$this->wc_os_shipping_remove_shipping_items($child_order);


                $shipping_item = $this->wc_os_get_shipping_item($parent_shipping_data);

                $shipping_item->set_method_title($parent_shipping_data['method_title']);
                $shipping_item->set_name($parent_shipping_data['name']);
                $shipping_item->set_total($shipping_total);
                $shipping_item->set_order_id($child_order_id);
                $child_order->add_item($shipping_item);
				
                //$child_order->calculate_totals();
                $child_order->save();
				
				$parcel_shipping = $child_order->get_items('fee');
				if(!empty($parcel_shipping)){
					foreach($parcel_shipping as $parcel_shipping_obj){
						$fee_id = ($parcel_shipping_obj->get_id());
						if($fee_id){
							$fee_name = $parcel_shipping_obj->get_data()['name'];
							if(in_array($fee_name, array($wc_os_parcel_shipment, $wc_os_parcel_shipment_adjustment))){
									$child_order = wc_get_order($child_order_id);
									$child_order->remove_item( $fee_id );
									$child_order->calculate_shipping();
							}
						}
						
						
					}
				}	


            }


        }



    }
    private function wc_os_clone_shipping($child_order_ids, $original_order_id)
    {

		
		
        $parent_order = $this->original_order;
		$parent_order = (!empty($parent_order)?$parent_order:wc_get_order($original_order_id));
        $parent_shipping = $parent_order->get_items('shipping');
        $parent_shipping_id = current(array_keys($parent_shipping));
        if(!$parent_shipping){return;}

        $parent_shipping = current($parent_shipping);
        $parent_shipping_data = $parent_shipping->get_data();
        $shipping_total = $parent_shipping_data['total'];

        if(empty($child_order_ids)){return;}

        if($this->is_parent_modified()){
			
            wc_delete_order_item($parent_shipping_id);
            $child_order_ids[] = $original_order_id;
        }

        if(!empty($child_order_ids)){

            foreach ($child_order_ids as $child_order_id){

                $child_order = new WC_Order($child_order_id);
				$this->wc_os_shipping_remove_shipping_items($child_order);


                $shipping_item = $this->wc_os_get_shipping_item($parent_shipping_data);

                $shipping_item->set_method_title($parent_shipping_data['method_title']);
                $shipping_item->set_name($parent_shipping_data['name']);
                $shipping_item->set_total($shipping_total);
                $shipping_item->set_order_id($child_order_id);
                $child_order->add_item($shipping_item);
				
                //$child_order->calculate_totals();
                $child_order->save();


            }


        }



    }
	
	private function wc_os_shipping_remove_shipping_items($order){
		
		$child_shipping = $order->get_items('shipping');
		
		
		
		if(!empty($child_shipping)){			
			$child_shipping_ids = array_keys($child_shipping);			
			foreach($child_shipping_ids as $child_shipping_id){			
				//wc_delete_order_item($child_shipping_id);		
				
				
				$order->remove_item( $child_shipping_id );	
			}
		}
		
		$order->calculate_shipping();
	
	}
	
	private function wc_os_eval($str){
		$str = str_replace(' ', '', $str);

		$p = $str;
		preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)(\d+)/', $str, $matches);

		if(is_array($matches) && !empty($matches) && $matches !== FALSE){
			$operator = $matches[2];

			switch($operator){
				case '+':
					$p = $matches[1] + $matches[3];
					break;
				case '-':
					$p = $matches[1] - $matches[3];
					break;
				case '*':
					$p = $matches[1] * $matches[3];
					break;
				case '/':
					$p = $matches[1] / $matches[3];
					break;
			}
		
			
		}	
		return $p;	
	}
	
	private function wc_os_category_based_shipping_class($child_order_ids, $original_order_id){
		
		$packages = array();
		$parent_order = $this->original_order;
		$parent_order = (!empty($parent_order)?$parent_order:wc_get_order($original_order_id));
		
		if (empty($parent_order)) {
			return;
		}
	
		$parent_shipping_items = $parent_order->get_items('shipping');
		
	
		if (empty($parent_shipping_items)) {
			return;
		}
	
		if (empty($child_order_ids)) {
			return;
		}
		$shipping_method_id = '';
		$shipping_method_instance_id = 0;
		
		foreach( $parent_shipping_items as $item_id => $parent_shipping_item ){
			$shipping_method_id = $parent_shipping_item->get_method_id();
			$shipping_method_instance_id = $parent_shipping_item->get_instance_id();
			
		}
		
		if ($shipping_method_id=='' || !$shipping_method_instance_id) {
			return;
		}
		$shipping_method_rates_key = 'woocommerce_'.$shipping_method_id.'_'.$shipping_method_instance_id.'_settings';
		$rates = get_option($shipping_method_rates_key);
		$shipping_method_type = $rates['type'];
		
		global $wc_os_product_cat_shipping_classes;
		$shipping_classes = get_terms( array('taxonomy' => 'product_shipping_class', 'hide_empty' => false ) );
		
		$category_to_shipping_rates = array();
		
		if(!empty($shipping_classes)){
			$category_to_shipping_rates[0] = $rates['no_class_cost'];
			foreach($shipping_classes as $shipping_class){
				if(in_array($shipping_class->term_id, $wc_os_product_cat_shipping_classes)){
					
					$class_cost_key = 'class_cost_'.$shipping_class->term_id;
					if(array_key_exists($class_cost_key, $rates)){
						$cat_id = (array_search($shipping_class->term_id, $wc_os_product_cat_shipping_classes));
						
						$category_to_shipping_rates[$cat_id] = $rates[$class_cost_key];
					}else{
						
					}
				}
			}
		}
		
		
		$method_ids_selected_parent = array();
		$parent_shipping_data = array();
		$parent_rate_ids = array();

		
		if (!empty($child_order_ids)) {
			
			
	
			foreach($child_order_ids as $child_order_id) {
				
				$child_order_shipping_rate = array();
				
				$_wc_os_cats_involved = wc_os_get_order_meta($child_order_id, '_wc_os_cats_involved', true);
				
				if(!empty($_wc_os_cats_involved)){
					
					foreach($_wc_os_cats_involved as $cid){
						
						if(array_key_exists($cid, $category_to_shipping_rates)){						
							$child_order_shipping_rate[$cid] = $category_to_shipping_rates[$cid];							
						}else{
							$child_order_shipping_rate[0] = $category_to_shipping_rates[0];
						}
					}
				}
				
				$child_order = new WC_Order($child_order_id);
				
				$this->wc_os_shipping_remove_shipping_items($child_order);
				
				if(!is_admin()){
		
					if (is_object(WC()->cart) && !WC()->cart->is_empty() ) {
						WC()->cart->empty_cart();
					}else{
						global $woocommerce;
						if(is_object($woocommerce)){
							$woocommerce->cart->empty_cart();
						}
					}					
				}
				$qty = 0;
				if(count($child_order->get_items())>0){
					foreach($child_order->get_items() as $item_key=>$item_obj){
						$qty += $item_obj->get_quantity();
					}
				}
				
				
				$child_order_shipping_rate = array_map(function($str) use ($qty){ return $this->wc_os_eval(str_replace(array('[qty]'), array($qty), $str)); }, $child_order_shipping_rate);
				
				
				
				switch($shipping_method_type){
					case 'class':
						$shipping_total = array_sum($child_order_shipping_rate);
					break;
					case 'order':
						$shipping_total = max($child_order_shipping_rate);
					break;
				}
				$shipping_item = new WC_Order_Item_Shipping();

				$shipping_item->set_method_title($parent_shipping_item->get_method_title());
				$shipping_item->set_method_id($parent_shipping_item->get_method_id());
				$shipping_item->set_instance_id($parent_shipping_item->get_instance_id());
				$shipping_item->set_total($shipping_total);
				$shipping_item->set_taxes(0);
				
				$shipping_item->set_order_id($child_order_id);
				$child_order->add_item($shipping_item);
				
				$shipping_included[] = true;


	
				$child_order->calculate_shipping();
				//$child_order->calculate_totals();
				$child_order->save();
	
				WC()->cart->empty_cart();
				
	
			}
			

	
		}
	}
	private function wc_os_shipping_class_shipping($child_order_ids, $original_order_id) {
		

		global $wc_os_shipping_cost, $wpdb;
		$packages = array();
		$parent_order = $this->original_order;
		$parent_order = (!empty($parent_order)?$parent_order:wc_get_order($original_order_id));
	
		if (empty($parent_order)) {
			return;
		}
	
		$parent_shipping_items = $parent_order->get_items('shipping');
	
		
		
		if (empty($parent_shipping_items)) {
			return;
		}
		
		if (empty($child_order_ids)) {
			return;
		}
		
		$_wc_os_shipping_methods = wc_os_get_order_meta($original_order_id, '_wc_os_shipping_method', true);
		$_wc_os_shipping_methods = (is_array($_wc_os_shipping_methods)?$_wc_os_shipping_methods:array());
		$_wc_os_shipping_all_methods_arr = array();
		$_wc_os_shipping_methods_arr = array();
		$_wc_os_shipping_order_items = array();
		
		
		$class_methods_obj = $wpdb->get_results('SELECT p.ID, p.post_title, pm.meta_value FROM '.$wpdb->prefix.'posts p, '.$wpdb->prefix.'postmeta pm  WHERE p.ID=pm.post_id AND p.post_type="was" AND pm.meta_key="_was_shipping_method_conditions"');
		if(!empty($class_methods_obj)){
			foreach($class_methods_obj as $class_method_obj){
				$shipping_meta_value = maybe_unserialize($class_method_obj->meta_value);
				$shipping_meta_value = is_array($shipping_meta_value)?$shipping_meta_value:array();
				$shipping_meta_value = array_key_exists(0, $shipping_meta_value)?$shipping_meta_value[0]:array();
				if(!empty($shipping_meta_value)){
					foreach($shipping_meta_value as $value_data){
			
						if(array_key_exists('condition', $value_data) && array_key_exists('operator', $value_data)){
							
							switch($value_data['condition']){
								case 'contains_shipping_class':
									if($value_data['operator']=='=='){
										
										$shipping_class_term = get_term_by('slug', $value_data['value'], 'product_shipping_class');
										
										if(in_array($class_method_obj->ID, $_wc_os_shipping_methods)){
										//$_wc_os_shipping_methods_arr[$class_method_obj->post_title][$class_method_obj->ID] = array('id'=>$class_method_obj->ID, 'slug'=>$value_data['value']);
											$_wc_os_shipping_methods_arr[$class_method_obj->post_title] = $class_method_obj->ID;
										}
										
										if(is_object($shipping_class_term) && !empty($shipping_class_term)){
											$_wc_os_shipping_all_methods_arr[$shipping_class_term->term_id][] = $class_method_obj->ID;
										}
									}
								break;
							}
							
						}
					}
				}
			}
		}
		
	
		$method_ids_selected_parent = array();
		$parent_shipping_data = array();
		$parent_rate_ids = array();
		foreach($parent_shipping_items as $shiping_item_id => $parent_shipping) {
	
			$parent_shipping_data = $parent_shipping->get_data();
			$parent_method_id = $parent_shipping->get_method_id();
			$parent_instance = $parent_shipping->get_instance_id();
			
			$shipping_method_name = (array_key_exists('name', $parent_shipping_data)?$parent_shipping_data['name']:'');
			$shipping_method_id = 0;
			if($shipping_method_name && array_key_exists($shipping_method_name, $_wc_os_shipping_methods_arr)){
				$shipping_method_id = $_wc_os_shipping_methods_arr[$shipping_method_name];
				
			}
			
			$rate_id = $parent_method_id.
			':'.$parent_instance;
			$parent_rate_ids[] = $rate_id;
	
	
			$method_ids_selected_parent[] = $parent_method_id;
	
			$shipping_related_items = $parent_shipping->get_meta_data();
			$shipping_related_items = (is_array($shipping_related_items)?current($shipping_related_items):$shipping_related_items);
	
			if(is_object($shipping_related_items)){

				$item_data = $shipping_related_items->get_data();


			}
			//exit;
			
			$vendor_id = $parent_shipping->get_meta('vendor_id');
			$method_slug = $parent_shipping->get_meta('method_slug');

			if ($vendor_id) {
	
				$parent_shipping_data[$vendor_id]['shipping'] = $parent_shipping;
				$parent_shipping_data[$vendor_id]['method_slug'] = $method_slug;
				$parent_shipping_data[$vendor_id]['shipping_method_id'] = $shipping_method_id;
	
	
			} else {
	
				$parent_shipping_data['shipping'] = $parent_shipping;
				$parent_shipping_data['method_slug'] = $method_slug;
				$parent_shipping_data['shipping_method_id'] = $shipping_method_id;
			}
	
		}
	
	
		if ($this->is_parent_modified()) {
	
			$parent_shipping_ids = array_keys($parent_shipping_items);
	
			foreach($parent_shipping_ids as $parent_shipping_id) {
	
				//wc_delete_order_item($parent_shipping_id);
				
				$parent_order->remove_item($parent_shipping_id);
				$parent_order->calculate_shipping();
	
			}
	
			$child_order_ids[] = $original_order_id;
		}
	
		if (!empty($child_order_ids)) {
	
			foreach($child_order_ids as $child_order_id) {
	
				$child_order = new WC_Order($child_order_id);
				$child_vendor_ids = wc_os_get_order_meta($child_order_id, '_group_vendor_ids', true);
				
				
				
				if(!$wc_os_shipping_cost){
					$this->wc_os_shipping_remove_shipping_items($child_order);
					
				}
				
				if(!is_admin()){
		
					if (is_object(WC()->cart) && !WC()->cart->is_empty() ) {
						WC()->cart->empty_cart();
					}else{
						global $woocommerce;
						if(is_object($woocommerce)){
							$woocommerce->cart->empty_cart();
						}
					}
					
				}
				$valid_shipping_classes = array();
				
				
				foreach($child_order->get_items() as $order_Item) {
					$order_Item_Data = $order_Item->get_data();
	
					$order_Item_id = $order_Item_Data['product_id'];
					$order_Item_qty = $order_Item->get_quantity();
					
					$product_id = ($order_Item->get_variation_id()?$order_Item->get_variation_id():$order_Item->get_product_id());
					$product = wc_get_product($product_id);
					
					$_wc_os_shipping_order_items[] = ($order_Item->get_name().' x '.$order_Item->get_quantity());
					
					$term_id = $product->get_shipping_class_id();
					if(array_key_exists($term_id, $_wc_os_shipping_all_methods_arr)){						
						
						$valid_shipping_classes = $_wc_os_shipping_all_methods_arr[$term_id];
						
						
					}else{
						$valid_shipping_classes[] = $product->get_shipping_class_id();
					}
					
					if(!is_admin()){
						WC()->cart->add_to_cart($order_Item_id, $order_Item_qty);
					}
	
				}
				
				if(!is_admin()){	
					$packages = WC()->shipping()->get_packages();
				}

				if (empty($packages)) {
					
					$this->wc_os_shipping_add_shipping_items($parent_shipping_data, $child_order, $valid_shipping_classes, $_wc_os_shipping_order_items);
					
					continue;
				}
	
				$current_package = current($packages);
				$rates = $current_package['rates'];
	
				
				
	
				if (!empty($rates)) {
	
					foreach($rates as $rate_key => $rate_object) {
	
						
						
						$shipping_included = array();
						if (!empty($child_vendor_ids)) {
	
							foreach($child_vendor_ids as $child_vendor_id) {
	
	
	
								if (array_key_exists($child_vendor_id, $parent_shipping_data)) {
	
									$parent_shipping_c = $parent_shipping_data[$child_vendor_id];
	
	
								} else {
	
									$parent_shipping_c = array_key_exists(0, $parent_shipping_data) ? $parent_shipping_data[0] : false;
	
								}
	
	
								if ($parent_shipping_c) {
	
									$parent_shipping_item = $parent_shipping_c['shipping'];
									$method_slug_vendor = $parent_shipping_c['method_slug'];
									$rate_object_id = $rate_object->get_id();
									$rate_object_slug = explode(':', $rate_object_id);
									$rate_object_slug = current($rate_object_slug);
									// $meta_data = $parent_shipping_item->get_meta_data();
	
									if ($rate_object_slug == $method_slug_vendor) {
	
										$shipping_item = new WC_Order_Item_Shipping();
	
										$shipping_item->set_method_title($parent_shipping_item->get_method_title());
										$shipping_item->set_method_id($parent_shipping_item->get_method_id());
										$shipping_item->set_instance_id($parent_shipping_item->get_instance_id());
										$shipping_item->set_total($parent_shipping_item->get_total());
										$shipping_item->set_taxes($parent_shipping_item->get_taxes());
	
										$parent_meta = $parent_shipping_item->get_meta_data();
	
										if (!empty($parent_meta)) {
	
											foreach($parent_meta as $meta_data) {
	
	
												$data_array = $meta_data->get_data();
	
												$shipping_item->add_meta_data($data_array['key'], $data_array['value']);
	
											}
	
										}
	
										$shipping_item->save_meta_data();
										$shipping_item->set_order_id($child_order_id);
										$child_order->add_item($shipping_item);
										
										$shipping_included[] = true;
	
	
									}
	
								}
	
							}
	
						}
						
	
						if (empty($child_vendor_ids) || empty($shipping_included)) {
	
							if (in_array($rate_key, $parent_rate_ids)) {
	
								$shipping_item = new WC_Order_Item_Shipping();
	
								$shipping_item->set_shipping_rate($rate_object);
								$shipping_item->set_order_id($child_order_id);
								
								$child_order->add_item($shipping_item);
	
	
							}
	
						}
	
					}
	
				}
	
				$child_order->calculate_shipping();
				//$child_order->calculate_totals();
				$child_order->save();
	
				if(!is_admin()){
					WC()->cart->empty_cart();
				}
	
	
	
			}
			
	
		}

		
	}
	
	public function wc_os_add_checkout_fee_for_gateway() {	
		
		
		$ret = $shipping_cost = 0;
		
		global $wc_os_general_settings, $wc_os_parcel_shipment, $wc_os_parcel_shipment_adjustment;	
		$wc_os_shipping_methods = (is_array($wc_os_general_settings) && array_key_exists('wc_os_shipping_methods', $wc_os_general_settings));
		$wc_os_shipping_settings = get_option('wc_os_shipping_settings', array());
		$wc_os_shipping_selection = array_key_exists('wc_os_shipping_selection', $wc_os_shipping_settings)?$wc_os_shipping_settings['wc_os_shipping_selection']:'';
	
		$packages = WC()->shipping->get_packages();
		$chosen_method = '';
		foreach ($packages as $i => $package) {
			$chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
		}		
		
		$rate = ($chosen_method?$package['rates'][$chosen_method]->cost:0);//WC()->cart->set_shipping_total($rate*2);
		
		$wc_os_customer_permission = array_key_exists('wc_os_customer_permission', $wc_os_general_settings);	
		if(!$wc_os_customer_permission || ($wc_os_customer_permission && function_exists('wc_os_get_session') && wc_os_get_session( 'wc_os_customer_permitted')=='on')){
			
			
			
			$equal_plus = ($wc_os_shipping_selection=='equal_plus');
			
			
			
			if($wc_os_shipping_methods){ 
			
				$shipping_cost = wc_os_get_session('wc_os_total_shipping_cost');
				$actual_shipping_cost = wc_os_get_session('wc_os_actual_shipping_cost');
				$wc_os_parcels_array = wc_os_get_session('wc_os_parcels_array');
				
				

				if($shipping_cost && $shipping_cost > 0){
					
					WC()->cart->add_fee( $wc_os_parcel_shipment,  $actual_shipping_cost*count($wc_os_parcels_array));
					
					if($rate>0){
						WC()->cart->add_fee( $wc_os_parcel_shipment_adjustment, -($rate) );
					}
										
				}
				
			}
			if(!$shipping_cost && $equal_plus && $rate>0){
				global $wc_os_order_splitter;
				$parcels = $wc_os_order_splitter->split_order_logic(null, true, true);
				$ret = $parcels_total = (is_array($parcels)?(count($parcels)-1):0);
				if($parcels_total>0){
					WC()->cart->add_fee( $wc_os_parcel_shipment,  $rate*$parcels_total);
				}else{
					WC()->cart->add_fee( $wc_os_parcel_shipment,  0);
					
				}
			}
		}
		return $ret;
	}
	
	public function wc_os_custom_shipping_costs( $rates, $package ){		
		return $rates;
	}
	private function wc_os_shipping_add_shipping_items($parent_shipping_data, $child_order, $valid_shipping_classes=array(), $_wc_os_shipping_order_items=array()){
		
		global $wc_os_shipping_cost;
		$child_order_id = $child_order->get_order_number();
		if($wc_os_shipping_cost){
			
						
			if(!empty($parent_shipping_data) && array_key_exists('shipping_method_id', $parent_shipping_data)){
				$shipping_method_id = $parent_shipping_data['shipping_method_id'];
				
				if(in_array($shipping_method_id, $valid_shipping_classes)){ 
				
					
					$shipping_item = new WC_Order_Item_Shipping();
					
					$parent_shipping_item = (array_key_exists('shipping', $parent_shipping_data)?$parent_shipping_data['shipping']:array());
			
					if(is_object($parent_shipping_item) && !empty($parent_shipping_item)){
						
						$shipping_item->set_method_title($parent_shipping_item->get_method_title());
						$shipping_item->set_method_id($parent_shipping_item->get_method_id());
						$shipping_item->set_instance_id($parent_shipping_item->get_instance_id());
						$shipping_item->set_total($parent_shipping_item->get_total());
						$shipping_item->set_taxes($parent_shipping_item->get_taxes());
	
						$parent_meta = $parent_shipping_item->get_meta_data();
	
						if (!empty($parent_meta)) {
	
							foreach($parent_meta as $meta_data) {
	
	
								$data_array = $meta_data->get_data();
								
								
								
								switch($data_array['key']){
									case 'Items':
										$data_array['value'] = implode(', ', $_wc_os_shipping_order_items);
									break;
								}
	
								$shipping_item->add_meta_data($data_array['key'], $data_array['value']);
								
								
	
							}
	
						}
	
						$shipping_item->save_meta_data();
						$shipping_item->set_order_id($child_order_id);
						
						$child_order->add_item($shipping_item);
						$child_order->save();
					}
					
				
					
				}
					
				
				
			}
		}
	}
	
	public function wc_os_shipping_cost_calculation($shipping_packages=array(), $order_index=0, $parcels_array=array(), $chosen_shipping='', $total_shipping_cost=0){
		$shipping_package_options = '';
		$actual_shipping_cost = 0;
		$ret = array(
						'parcels_array'=>array(),
						'actual_shipping_cost' => $actual_shipping_cost,
						'total_shipping_cost' => 0,
						'shipping_package_options' => $shipping_package_options,
						
				);
		
		if(!empty($shipping_packages)){
			
			$wc_os_shipping_settings = get_option('wc_os_shipping_settings', array());
			$wc_os_shipping_selection = array_key_exists('wc_os_shipping_selection', $wc_os_shipping_settings)?$wc_os_shipping_settings['wc_os_shipping_selection']:'';

		
			foreach($shipping_packages as $shipping_package){
				

				if(array_key_exists('rates', $shipping_package) && is_array($shipping_package['rates'])){
					foreach($shipping_package['rates'] as $method){
						
						$wc_os_po_shipping = $method->get_method_id().'-'.$method->get_instance_id().'-'.$order_index;


						if($chosen_shipping==$method->get_id()){
						
							$parcels_array[$order_index]['method'] = $chosen_shipping;
							$parcels_array[$order_index]['instance_id'] = $method->get_instance_id();
							$parcels_array[$order_index]['method_label'] = $method->get_label();
							
							$actual_shipping_cost = ($method->cost + $method->get_shipping_tax());
							switch($wc_os_shipping_selection){
								default:
								case 'equal':
								case 'clone':
								//	$total_shipping_cost = $actual_shipping_cost;
								//break;
								case 'equal_plus':
									$total_shipping_cost += $actual_shipping_cost;
								break;
								case 'no':
									$actual_shipping_cost = 0;
									$total_shipping_cost = 0;
								break;
							}
							
							
							
							$shipping_package_options .= '<label for="'.$wc_os_po_shipping.'">'.($method->get_label().': '.wc_price( $actual_shipping_cost )).'</label>';
							
							$parcels_array[$order_index]['method_price'] = $method->cost;
							
							$parcels_array[$order_index]['method_tax'] = $method->get_shipping_tax();
							
						}

						
					}
				}
			}
			
			$ret['parcels_array'] = $parcels_array;
			$ret['actual_shipping_cost'] = $actual_shipping_cost;
			$ret['total_shipping_cost'] = $total_shipping_cost;
			$ret['shipping_package_options'] = $shipping_package_options;
		}
		
		
		return $ret;		
	}
}
