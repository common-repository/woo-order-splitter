<?php
	// Register Custom Taxonomy
	function wc_os_taxonomies() {
	
	  $labels = array(
		'name'                       => _x( 'Vendors', 'Vendors Name', 'text_domain' ),
		'singular_name'              => _x( 'Vendor', 'Vendor Name', 'text_domain' ),
		'menu_name'                  => __( 'Vendors', 'text_domain' ),
		'all_items'                  => __( 'All Vendors', 'text_domain' ),
		'parent_item'                => __( 'Parent Vendor', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Vendor:', 'text_domain' ),
		'new_item_name'              => __( 'New Vendor Name', 'text_domain' ),
		'add_new_item'               => __( 'Add Vendor', 'text_domain' ),
		'edit_item'                  => __( 'Edit Vendor', 'text_domain' ),
		'update_item'                => __( 'Update Vendor', 'text_domain' ),
		'view_item'                  => __( 'View Vendor', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate department with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove vendors', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Vendors', 'text_domain' ),
		'search_items'               => __( 'Search Vendors', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No vendors', 'text_domain' ),
		'items_list'                 => __( 'Vendors list', 'text_domain' ),
		'items_list_navigation'      => __( 'Vendors list navigation', 'text_domain' ),
	  );
	  $args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => true,
		'capabilities' => array(
			'manage_terms' => 'manage_options', // Using 'edit_users' cap to keep this simple.
			'edit_terms'   => 'manage_options',
			'delete_terms' => 'manage_options',
			'assign_terms' => 'read',
		),
		'update_count_callback' => 'wc_os_update_vendor_count'
	  );
	  register_taxonomy( 'wc-os-vendor', 'user', $args );
	
	}
	add_action( 'init', 'wc_os_taxonomies', 0 );
	
	function wc_os_update_vendor_count( $terms, $taxonomy ) {
		global $wpdb;
	
		foreach ( (array) $terms as $term ) {
	
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
	
			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}
	 /**
	 * Admin page for the 'wc-os-vendor' taxonomy
	 */
	function wc_os_add_vendors_taxonomy_admin_page() {
	
	  $tax = get_taxonomy( 'wc-os-vendor' );
	
	  add_users_page(
		esc_attr( $tax->labels->menu_name ),
		esc_attr( $tax->labels->menu_name ),
		$tax->cap->manage_terms,
		'edit-tags.php?taxonomy=' . $tax->name
	  );
	
	}
	add_action( 'admin_menu', 'wc_os_add_vendors_taxonomy_admin_page' );
	
	/**
	 * Unsets the 'posts' column and adds a 'users' column on the manage vendors admin page.
	 */
	function wc_os_manage_vendors_user_column( $columns ) {
	
	  unset( $columns['posts'] );
	
	  $columns['users'] = __( 'Users' );
	
	  return $columns;
	}
	add_filter( 'manage_edit-vendors_columns', 'wc_os_manage_vendors_user_column' );
	
	/**
	 * @param string $display WP just passes an empty string here.
	 * @param string $column The name of the custom column.
	 * @param int $term_id The ID of the term being displayed in the table.
	 */
	function wc_os_manage_vendors_column( $display, $column, $term_id ) {
	
	  if ( 'users' === $column ) {
		$term = get_term( $term_id, 'wc-os-vendor' );
		echo $term->count;
	  }
	}
	add_filter( 'manage_vendors_custom_column', 'wc_os_manage_vendors_column', 10, 3 );
	
	/**
	 * @param object $user The user object currently being edited.
	 */
	function wc_os_edit_user_vendor_section( $user, $inner=false ) {
		
		if(!is_object($user)){ return; }		
		
		global $pagenow;
		
		$tax = get_taxonomy( 'wc-os-vendor' );
		
		
	  /* Make sure the user can assign terms of the vendors taxonomy before proceeding. */
		if ( !current_user_can( $tax->cap->assign_terms ) && !$inner)
		return;
		
		$roles = ( array ) $user->roles;
	  /* Get the terms of the 'wc-os-vendor' taxonomy. */
		$terms = get_terms( 'wc-os-vendor', array( 'hide_empty' => false ) );
		$matched_roles = array();
		if(!empty($terms)){ foreach($terms as $term){ 
			$wc_os_selected_vendor = get_term_meta( $term->term_id, 'wc_os_vendor_role_selection', true );
			if(in_array($wc_os_selected_vendor, $roles)){
				$matched_roles[] = $wc_os_selected_vendor;
			}
		}}
	
		if(empty($matched_roles)){
			return;
		}
		
		
		$wc_os_shipping_platforms = get_option('wc_os_shipping_platforms');
		$wc_os_shipping_platforms = (is_array($wc_os_shipping_platforms)?$wc_os_shipping_platforms:array());	
		
		
		
?>
		
	
	  <table class="form-table">
<?php
	if ( !empty( $terms ) && !$inner) {
?>				
		<tr <?php echo (!is_user_admin()?'style="display:none;"':''); ?>>
		  <th><label for="wc-os-vendor"><?php _e( 'Vendor Type' ); ?></label></th>	
		  <td><?php	
			  echo wc_os_custom_form_field('wc-os-vendor', $terms, $user->ID, 'dropdown');?>
          </td>
		</tr>
<?php
	}
	
	$str = home_url();
	$url_pattern = array('https://', 'http://', 'www.', '/');
	$domain_name = $user->ID.'.'.strtoupper(str_replace($url_pattern, '', $str));	
	
	if(in_array('shipstation', $wc_os_shipping_platforms)){
			
			$ss = '<span style="font-size:16px; font-weight:bold; font-style:italic;">ShipStati'.($inner?'o':'<i class="fas fa-cog" style="color:#92c43e"></i>').'n</span>';			
			
			$ss_arr = array(
	
					'URL'=>array('type'=>'text', 'value'=>home_url('?wc-os-shipstationxml'), 'readonly'=>'readonly', 'tooltip'=>'URL to Custom XML Page'), 
					'username'=>array('type'=>'text', 'value'=>$domain_name, 'readonly'=>'readonly', 'tooltip'=>'Custom Store Connection Username'), 
					'password'=>array('type'=>'text', 'value'=>md5(base64_encode($user->ID)), 'readonly'=>'readonly', 'tooltip'=>'Custom Store Connection Password')
				
			);
			
			if(!empty($ss_arr)){
				foreach($ss_arr as $ss_index=>$ss_attrib){
?>
		<tr>
		  <th><label for="ss-<?php echo $ss_index; ?>"><?php echo $ss; ?> <?php echo ucwords(str_replace('_', ' ', $ss_index)); ?></label></th>	
		  	<td>
            	<input type="<?php echo $ss_attrib['type']; ?>" <?php echo ($ss_attrib['tooltip']?'title="'.$ss_attrib['tooltip'].'"':''); ?> <?php echo ($ss_attrib['readonly']?'readonly="'.$ss_attrib['readonly'].'"':''); ?> id="ss-<?php echo $ss_index; ?>" name="wc-os-ss-credentials[<?php echo $ss_index; ?>]" value="<?php echo ($ss_attrib['value']?$ss_attrib['value']:get_wc_os_ss_credentials($ss_index)); ?>" /><?php echo ($ss_attrib['tooltip']?'<small>('.$ss_attrib['tooltip'].')':''); ?>
			</td>
		</tr>

<?php		
				}
			}
	}
	
	
?>	
	  </table>
	<?php }
	
	add_action( 'show_user_profile', 'wc_os_edit_user_vendor_section' );
	add_action( 'edit_user_profile', 'wc_os_edit_user_vendor_section' );
	add_action( 'user_new_form', 'wc_os_edit_user_vendor_section' );
	
	function get_wc_os_ss_credentials($user_id=0, $index=''){
		
		$ret = '';
		
		$wc_os_ss_credentials = wc_os_get_order_meta($user_id, 'wc-os-ss-credentials', true);
		
		if(array_key_exists($index, $wc_os_ss_credentials)){
			$ret = $wc_os_ss_credentials[$index];
		}
		
		return $ret;
	}
	
	/**
	 * return field as dropdown or checkbox, by default checkbox if no field type given
	 * @param: name = taxonomy, options = terms avaliable, userId = user id to get linked terms
	 */
	function wc_os_custom_form_field( $name, $options, $userId, $type = 'checkbox') {
		
		
		switch ($type) {
		case 'checkbox':
		  foreach ( $options as $term ) : 
		  	
		  ?>
			<label for="vendors-<?php echo esc_attr( $term->slug ); ?>">
			  <input type="checkbox" name="<?php echo $name; ?>[]" id="vendors-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo $term->slug; ?>" <?php if ( $pagenow !== 'user-new.php' ) checked( true, is_object_in_term( $userId, 'wc-os-vendor', $term->slug ) ); ?>>
			  <?php echo $term->name; ?>
			</label><br/>
		  <?php
		  endforeach;
		  break;
		case 'dropdown':
			
			
		
			// Dropdown
			echo "<select name='".$name."' id='".$name."'>";
			echo "<option value=''>-Select-</option>";
			foreach( $options as $term ) {
				$selected = ( is_object_in_term( $userId, 'wc-os-vendor', $term->slug ) ) ? " selected='selected'" : "";
				echo "<option value='".$term->term_id."' {$selected}>".$term->name."</option>";
			}
			echo "</select>";
		  break;
		}
	}
	
	/**
	 * @param int $user_id The ID of the user to save the terms for.
	 */
	function wc_os_save_user_vendor_terms( $user_id ) {
	
		$tax = get_taxonomy( 'wc-os-vendor' );
		
		/* Make sure the current user can edit the user and assign terms before proceeding. */
		if ( !current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
		return false;
		
		
		$term = (array_key_exists('wc-os-vendor', $_POST)?sanitize_wc_os_data($_POST['wc-os-vendor']):0);
		$terms = is_array($term) ? $term : (int) $term; // fix for checkbox and select input field

		/* Sets the terms (we're just using a single term) for the user. */
		wp_set_object_terms( $user_id, $terms, 'wc-os-vendor', true);		
		clean_object_term_cache( $user_id, 'wc-os-vendor' );
	}
	
	add_action( 'personal_options_update', 'wc_os_save_user_vendor_terms' );
	add_action( 'edit_user_profile_update', 'wc_os_save_user_vendor_terms' );
	add_action( 'user_register', 'wc_os_save_user_vendor_terms' );
	
	/**
	 * @param string $username The username of the user before registration is complete.
	 */
	function wc_os_disable_vendors_username( $username ) {
	
		if ( 'wc-os-vendor' === $username )
		$username = '';
	
		return $username;
	}
	add_filter( 'sanitize_user', 'wc_os_disable_vendors_username' );
	
	/**
	 * Update parent file name to fix the selected menu issue
	 */
	function wc_os_change_vendor_parent_file($parent_file)
	{
		global $submenu_file;
	
		if (
			isset($_GET['taxonomy']) && 
			$_GET['taxonomy'] == 'wc-os-vendor' &&
			$submenu_file == 'edit-tags.php?taxonomy=vendors'
		) 
		$parent_file = 'users.php';
	
		return $parent_file;
	}
	add_filter('parent_file', 'wc_os_change_vendor_parent_file');
	

	if(!function_exists('wos_register_meta_boxes')){

		function wos_register_meta_boxes() {
			global $wc_os_settings;
			if(is_array($wc_os_settings) && $wc_os_settings['wc_os_ie']=='group_by_vendors'){
				add_meta_box( 'wos-vendor-id', __( 'Product Vendor', 'woo-order-splitter' ), 'wos_vendors_list_display_callback', 'product', 'side', 'high' );
			}
		}
		add_action( 'add_meta_boxes', 'wos_register_meta_boxes' );
	}
	add_action( 'wc-os-vendor_add_form_fields', 'wc_os_vendors_add_term_fields' );
	function wc_os_vendors_add_term_fields( $taxonomy ) {
		

		$editable_roles = array_reverse(get_editable_roles());
		echo '<div class="form-field">
		<label for="wc-os-vendor-role">'.__('Associated User Role','woo-order-splitter').'</label>
		<p>';
		echo '<select name="wc_os_vendor_role_selection" id="wc_os_vendor_role_selection">';
		foreach($editable_roles as $role=>$details){

			$name = translate_user_role($details['name']);
			echo "<option value='".esc_attr($role)."'>$name</option>";
		}	
		echo '</select>';
		echo '</p>
		</div>';
	}	
	add_action( 'wc-os-vendor_edit_form_fields', 'wc_os_vendors_edit_term_fields' );
	function wc_os_vendors_edit_term_fields( $taxonomy ) {
	
		$wc_os_selected_vendor = get_term_meta( $taxonomy->term_id, 'wc_os_vendor_role_selection', true );
		$editable_roles = array_reverse(get_editable_roles());
		
?>
<tr class="form-field form-required term-name-wrap">
<th scope="row"><label for="wc-os-vendor-role"><?php echo __('Associated User Role','woo-order-splitter'); ?></label></th>
<td>
<select name="wc_os_vendor_role_selection" id="wc_os_vendor_role_selection">;
<?php		foreach($editable_roles as $role=>$details){
			$selected_role = ($role == $wc_os_selected_vendor ? 'selected="selected"' : '');
			$name = translate_user_role($details['name']);
			echo "<option value='".esc_attr($role)."' $selected_role >$name</option>";
		}	
?>		
</select>
</tr>
<?php		
	}
	
			
	add_action( 'created_wc-os-vendor', 'wc_os_vendor_save_term_fields' );
	add_action( 'edited_wc-os-vendor', 'wc_os_vendor_save_term_fields' );
	function wc_os_vendor_save_term_fields( $term_id ) {
		update_term_meta(
			$term_id,
			'wc_os_vendor_role_selection',
			sanitize_wc_os_data( $_POST[ 'wc_os_vendor_role_selection' ] )
		);
	}
	/**
	 * Meta box display callback.
	 *
	 * @param WP_Post $post Current post object.
	 */
	if(!function_exists('wos_vendors_list_display_callback')){ 
		function wos_vendors_list_display_callback( $post ) {
		// Display code/markup goes here. Don't forget to include nonces!
		global $post, $wpdb;
		$vendor_id_selected = $post->post_author;
		$post_author_query = "SELECT post_author FROM $wpdb->posts WHERE ID=$post->ID";
		$post_author_obj = $wpdb->get_row($post_author_query);
		if(is_object($post_author_obj) && !empty($post_author_obj)){
			$vendor_id_selected = $post_author_obj->post_author;
		}
		
		$vendors = (function_exists('wos_get_vendors_array')?wos_get_vendors_array():array());
		
		if(!empty($vendors)){
?>
			<?php wp_nonce_field( 'wos_vendor_naction', 'wos_vendor_nafield' ); ?>
			<select name="wos_vendor_id">
            <option value=""><?php _e('Select', 'woo-order-splitter'); ?></option>
<?php	
			foreach($vendors as $vendor_id => $vendor_data){ $vendor_data = (array)(is_object($vendor_data)?current($vendor_data):$vendor_data);
				
?>
			<option <?php selected($vendor_id_selected==$vendor_id); ?> value="<?php echo esc_attr($vendor_id); ?>"><?php echo $vendor_data['name'].' ('.$vendor_data['email'].')'.' - ID: '.$vendor_id; ?></option>
<?php				
				
			}
?>
			</select>			
<?php			
		}
	}	
	}
	
	if(!function_exists('wos_save_vendor_metabox_callback')){ 
		function wos_save_vendor_metabox_callback( $post_id ) {
		
	 	
		if(function_exists('wc_os_save_io_prices')){wc_os_save_io_prices($post_id);}
		if ( ! isset( $_POST['wos_vendor_nafield'] ) ) {
			return;
		}
	 	//echo ( ! wp_verify_nonce( $_POST['wos_vendor_nafield'], 'wos_vendor_naction' ) );exit;
		if ( ! wp_verify_nonce( $_POST['wos_vendor_nafield'], 'wos_vendor_naction' ) ) {
			return;
		}
	 	//echo ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE );exit;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	 	//echo ( ! current_user_can( 'edit_post', $post_id ) );exit;
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	 	//echo ( isset( $_POST['post_type'] ) && 'product' === $_POST['post_type'] && isset($_POST['wos_vendor_id']));exit;
		if ( isset( $_POST['post_type'] ) && 'product' === $_POST['post_type'] && isset($_POST['wos_vendor_id'])) {
	 
			// do stuff
			$wos_vendor_id = sanitize_wc_os_data($_POST['wos_vendor_id']);
			//echo $wos_vendor_id;exit;
			if($wos_vendor_id && is_numeric($wos_vendor_id)){
				global $wpdb;
				$vendor_post = array(
					'ID'           => $post_id,
					'post_author'   => $wos_vendor_id,
				);
				$wpdb->query("UPDATE $wpdb->posts SET post_author=$wos_vendor_id WHERE ID=$post_id");

				$wpdb->query("UPDATE $wpdb->posts SET post_author=$wos_vendor_id WHERE post_parent=$post_id");


				
			}
	 
		}
	 
		// Check if $_POST field(s) are available
	 
		// Sanitize
	 
		// Save
		 
	}
		add_action( 'save_post', 'wos_save_vendor_metabox_callback' );	
	}	
	


    if(!function_exists('wos_get_vendors_array')){

        function wos_get_vendors_array(){

            global $wpdb;

            $wc_os_selected_vendor = get_option('wc_os_vendor_role_selection', '');
            $wc_os_all_user_with_role = get_option('wc_os_all_user_with_role', false);


            $wc_os_user_ids = (function_exists('wc_os_user_ids_by_vendor')?wc_os_user_ids_by_vendor($wc_os_selected_vendor, $wc_os_all_user_with_role):array());
			
            $product_vendors = $wc_os_user_ids;

            if($wc_os_all_user_with_role){

                if(!$wc_os_selected_vendor){

                    $vendors_query = "SELECT post_author FROM $wpdb->posts WHERE (post_author > 0  AND post_type='product' AND post_status='publish') GROUP BY post_author";
					
					$product_vendors = $wc_os_selected_vendor && empty($wc_os_user_ids) ? array() : $wpdb->get_results($vendors_query);

					if(!empty($product_vendors)){
						$product_vendors_found = array();
						foreach($product_vendors as $product_vendor){
							if(array_key_exists($product_vendor->post_author, $wc_os_user_ids)){
								$product_vendors_found[$product_vendor->post_author] = $wc_os_user_ids[$product_vendor->post_author];
							}
						}
						$product_vendors = (!empty($product_vendors_found)?$product_vendors_found:$product_vendors);
					}
                }else{
                    $vendors_query = "SELECT post_author FROM $wpdb->posts WHERE (post_author IN (".implode(',', array_keys($wc_os_user_ids)).") AND post_type='product' AND post_status='publish') GROUP BY post_author";

					$product_vendors = $wc_os_user_ids;
                }
				
               
				
				

            }

            return $product_vendors;


        }

    }	
   if(!function_exists('wc_os_user_ids_by_vendor')){

        function wc_os_user_ids_by_vendor($vendor = '', $array = true, $users=array()){

			global $wpdb;
			
            $user_ids = array();


            if($vendor){


                $args = array(
                    'role' => $vendor,
                );
				
					
				//SELECT user_id, meta_key, meta_value FROM wp_usermeta WHERE user_id IN (2434) ORDER BY umeta_id ASC
				$users_query = "SELECT 
								u.ID,
								u.display_name,
								u.user_email
								
								FROM $wpdb->users u
								INNER JOIN $wpdb->usermeta um
								ON ( u.ID = um.user_id )
								WHERE ( ( ( um.meta_key = '".$wpdb->prefix."capabilities'
								AND um.meta_value LIKE '%$vendor%' ) ) )
								ORDER BY u.user_login ASC";
				
				if(is_numeric($vendor)){
					
					$users_query = "
					
						SELECT 						
							r.object_id AS ID, 
							u.display_name, 
							u.user_email 						
						FROM 
							`$wpdb->term_relationships` r, `$wpdb->users` u 						
						WHERE 
							r.object_id=u.ID 
						AND 
							r.term_taxonomy_id=$vendor 
						ORDER BY 
							r.term_order 
						ASC
					
					";
					
					
					
				}
				
				if(empty($users)){ //pree($users_query);
					$users = $wpdb->get_results($users_query);
				}
				


            }else{

                $args = array();
				
				$users = (function_exists('get_users')?get_users($args):array());
            }

								
            
			

            if(!empty($users)){
                foreach ($users as $user){
                    $user_ids[$user->ID] = array('id'=>$user->ID, 'name'=>$user->display_name, 'email'=>$user->user_email);
                }
            }
			
            if($array){

                return $user_ids;

            }else{


                $user_ids = array_map(function ($user_id){

                    return array('post_author' => $user_id );

                }, $user_ids);


                return json_decode(json_encode($user_ids));
            }

        }

    }


    add_action('wp_ajax_wc_os_update_vendor_role_selection', 'wc_os_update_vendor_role_selection');
    if(!function_exists('wc_os_update_vendor_role_selection')){

        function wc_os_update_vendor_role_selection(){


            if(isset($_POST['wc_os_vendor_role_selection'])){


                if (
                    ! isset( $_POST['wc_os_vendors_field'] )
                    || ! wp_verify_nonce( $_POST['wc_os_vendors_field'], 'wc_os_vendors_action' )
                ) {

                    _e('Sorry, your nonce did not verify.', 'woo-order-splitter');
                    exit;

                } else {
					

                    global $wc_os_settings;
                    $wc_os_vendors = array_key_exists('wc_os_vendors', $wc_os_settings) ? $wc_os_settings['wc_os_vendors'] : array();
				

                    $wc_os_vendor_role_selection = ($_POST['wc_os_vendor_role_selection'] ==  'false' ? '' : sanitize_wc_os_data($_POST['wc_os_vendor_role_selection']));
                    $wc_os_all_user_with_role = isset($_POST['wc_os_all_user_with_role']) && $_POST['wc_os_all_user_with_role'] ==  'true' ? true : false;
					
					
                    $wc_os_user_ids = wc_os_user_ids_by_vendor($wc_os_vendor_role_selection);

                    if(!empty($wc_os_vendors)){

                        $new_os_vendors = array();
						
						$wc_os_user_ids = array_keys($wc_os_user_ids);

                        foreach ($wc_os_vendors as $group => $users){

                            if($group !== 0){

                                $users =  array_intersect($wc_os_user_ids, $users);


                            }

                            if(!empty($users)){

                                $new_os_vendors[$group] = $users;

                            }

                        }

                        $wc_os_settings['wc_os_vendors'] = $new_os_vendors;
                        update_option('wc_os_settings', $wc_os_settings);


                    }


                    update_option('wc_os_vendor_role_selection', $wc_os_vendor_role_selection);
                    update_option('wc_os_all_user_with_role', $wc_os_all_user_with_role);
				

                }

            }


            wp_die();

        }
    }	
		
	add_filter ( 'woocommerce_account_menu_items', 'wc_os_shipstation_my_account_link', 40 );
	function wc_os_shipstation_my_account_link( $menu_links ){
		$wc_os_shipping_platforms = get_option('wc_os_shipping_platforms');
		$wc_os_shipping_platforms = (is_array($wc_os_shipping_platforms)?$wc_os_shipping_platforms:array());			
		if(in_array('shipstation', $wc_os_shipping_platforms)){		
			$menu_links = array_slice( $menu_links, 0, 5, true ) 
			+ array( 'wc_os_shipstation' => 'ShipStation' )
			+ array_slice( $menu_links, 5, NULL, true );
		}
		return $menu_links;
	
	}
	// register permalink endpoint
	add_action( 'init', 'wc_os_shipstation_my_account_endpoint' );
	function wc_os_shipstation_my_account_endpoint() {
	
		add_rewrite_endpoint( 'wc_os_shipstation', EP_ROOT | EP_PAGES );
	
	}
	function wc_os_shipstation_my_account_query_vars( $vars ) {
		$vars[] = 'wc-os-shipstation';
		return $vars;
	}  
	add_filter( 'query_vars', 'wc_os_shipstation_my_account_query_vars', 0 );  
	
	function wc_os_shipstation_my_account_content() {
		$user = wp_get_current_user();
		$ss = '<span style="font-style:italic;color:#92c43e;">ShipStation</span>';
		echo '<h3>Connect '.$ss.'</h3><p>Copy/Paste the following details to your ShipStation account under connect marketplace area with custom store option.</p>';

		wc_os_edit_user_vendor_section($user, true);
	
	}  
	add_action( 'woocommerce_account_wc_os_shipstation_endpoint', 'wc_os_shipstation_my_account_content' );	