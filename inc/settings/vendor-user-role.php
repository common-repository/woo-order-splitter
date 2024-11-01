<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="wos_vendors_list">

<label></label>

<input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

<?php wp_nonce_field( 'wc_os_vendors_action', 'wc_os_vendors_field' ); ?>

<?php
	$wc_os_ie_name = 'wc_os_vendors[]';
	
	switch($wc_os_settings['wc_os_ie']){ 
		
		case 'group_by_vendors': 

			$wc_os_all_user_with_role = get_option('wc_os_all_user_with_role');
			
			$wc_os_all_user_with_role = ($wc_os_all_user_with_role==true);
			
			$product_vendors = (function_exists('wos_get_vendors_array')?wos_get_vendors_array():array());









	?>

    <div class="wc_os_vendor_role_selection_wrapper">
    
    	<div class="vendor-left">

        <label for="wc_os_vendor_role_selection"><?php _e('Select a user role or taxonomy for Vendors','woo-order-splitter')?></label>

        <select name="wc_os_vendor_role_selection" id="wc_os_vendor_role_selection">

        <option value=""><?php _e('All','woo-order-splitter')?></option>
		

        <?php


        $editable_roles = array_reverse(get_editable_roles());
		$wc_os_selected_vendor = get_option('wc_os_vendor_role_selection', '');
		$vendor_terms = get_terms( 'wc-os-vendor', array( 'hide_empty' => false ) );
		
		if(!empty($editable_roles)){
?>
		<optgroup label="<?php _e('User Roles','woo-order-splitter')?>">
<?php			
			foreach($editable_roles as $role=>$details){
				$selected_role = ($role == $wc_os_selected_vendor ? 'selected="selected"' : '');
				$name = translate_user_role($details['name']);
				echo "<option value='".esc_attr($role)."' $selected_role >$name</option>";
			}
?>
		</optgroup>
<?php			
			
		}
		
		if(!empty($vendor_terms)){
		
?>
		<optgroup label="<?php _e('User Taxonomies','woo-order-splitter')?>">
<?php			
			
			
			foreach($vendor_terms as $vendor_term){ 
				$selected_term = ($vendor_term->term_id == $wc_os_selected_vendor ? 'selected="selected"' : '');
				$name = $vendor_term->name;
				echo "<option value='".$vendor_term->term_id."' $selected_term >$name</option>";
				
			}
?>
		</optgroup>
<?php

		}
		$wc_os_vendors_remaining = get_option('wc_os_vendors_remaining', 'group');
		
        ?>

        </select>

        <label for="wc_os_all_user_with_role" style="margin-top: 15px;">
            <input type="checkbox" name="wc_os_all_user_with_role" class="wc_os_all_user_with_role" id="wc_os_all_user_with_role" value="1" <?php checked($wc_os_all_user_with_role) ?>/>
            <?php _e('Users with active products only','woo-order-splitter')?>
        </label>
		<b><?php _e('Note:','woo-order-splitter')?> (<?php _e('Leave unselected for each split order with unique vendor items.','woo-order-splitter')?>)</b>


		</div>
        
        <div class="vendor-right">
        	<input type="radio" value="group" <?php checked($wc_os_vendors_remaining=='group'); ?> name="vendors_remaining" />
            <input type="radio" value="separate" <?php checked($wc_os_vendors_remaining=='separate'); ?> name="vendors_remaining" />
            <div class="vendor-rtop">
				<?php _e('Group selected vendors together','woo-order-splitter')?>
                
                <i class="fas fa-boxes"></i>
                
            </div>
            <div class="vendor-rmiddle">
            	<?php _e('and','woo-order-splitter')?>
            </div>
            <div class="vendor-rbottom">
            
                <div class="vendor-group-remaining" data-text="separate" <?php echo ($wc_os_vendors_remaining=='group'?'style="display:block;"':''); ?>>
                <?php _e('Group all remaining vendors together','woo-order-splitter')?>
                
                <i class="fas fa-boxes"></i>
                </div>
                
                <div class="vendor-split-remaining" data-text="group" <?php echo ($wc_os_vendors_remaining=='separate'?'style="display:block;"':''); ?>>
                <?php _e('Separate all remaining vendors','woo-order-splitter')?>
                    <i class="fas fa-square"></i>
                    <i class="fas fa-square"></i>
                    <i class="fas fa-square"></i>
                
                </div>
            
       	 	</div>
            
		</div>            
    </div>

    <?php


	
	if( !empty($product_vendors) ){

		$groups_arr = wc_os_get_groups_range();
		
		$wc_os_vendors = array_key_exists('wc_os_vendors', $wc_os_settings) ? $wc_os_settings['wc_os_vendors'] : array();
		$wc_os_vendors_selected = array();
		if(!empty($wc_os_vendors)){			
			foreach($wc_os_vendors as $v_group=>$v_ids){
				if(is_array($v_ids) && !empty($v_ids)){
					$wc_os_vendors_selected = array_merge($wc_os_vendors_selected, $v_ids);
				}
			}
			$wc_os_vendors_selected = array_filter($wc_os_vendors_selected);
		}



?>

<table border="0" class="">

<thead>

<th><?php _e('Enable/Disable', 'woo-order-splitter'); ?></th>

<th><?php _e('Vendor Name', 'woo-order-splitter'); ?></th>

<th><?php _e('Split Group', 'woo-order-splitter'); ?></th>

<th class="wc_os_group_status wc_group_by_vendors"><?php _e('Group Status', 'woo-order-splitter'); ?></th>

<th><?php _e('Actions', 'woo-order-splitter'); ?></th>

</thead>

<tbody>

<?php	

			

		

		

			
			$ticked = '';
			foreach ($product_vendors as $key => $vendor) {
				
				$vendor = (array)$vendor->post_author;

				$display_name = $vendor['name'];//wos_get_author_name($vendor->post_author);
				
			
			if($display_name):
			

	
				$ticked = in_array($vendor['id'], $wc_os_vendors_selected);
	
				$vendor_edit_shop_url = admin_url().'edit.php?post_type=product&vendor='.$vendor['id'];
	
				$vendor_view_shop_url = get_permalink( wc_get_page_id( 'shop' ) ).'?vendor='.$vendor['id'];
				
				$vendor_group = "";

?>



<tr>

<td><input id="wic-<?php echo esc_attr($vendor['id']); ?>" type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($vendor['id']); ?>" <?php checked($ticked); ?> class="<?php //echo $ticked?'':'hides'; ?>" /></td>

<td><a target="_blank" href="<?php echo $vendor_url = esc_url(admin_url().'user-edit.php?user_id='.$vendor['id']); ?>"><?php echo esc_html($display_name.' ('.$vendor['email'].')'); ?></a></td>



<td class="split-actions" data-id="<?php echo esc_attr($vendor['id']); ?>">



	

    

    <select id="group-vendor-<?php echo esc_attr($vendor['id']); ?>" name="split_action[wc_os_vendors][<?php echo esc_attr($vendor['id']); ?>]" data-ie="group_by_vendors">

    <option value=""></option>

    	<?php foreach($groups_arr as $v): 

			$k = strtolower($v);

			$groups_selected = (isset($wc_os_settings['wc_os_vendors'][$k])?$wc_os_settings['wc_os_vendors'][$k]:array());
			 if(!$vendor_group && in_array($vendor['id'], $groups_selected)){
                $vendor_group = $k;
            }

		?>

        <option <?php selected(in_array($vendor['id'], $groups_selected)); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>

        <?php endforeach; ?>

    </select>

</td>

<?php if(function_exists('wc_os_get_group_status_select')){wc_os_get_group_status_select('group_by_vendors', $vendor_group);} ?>

<td><a href="<?php echo esc_url($vendor_edit_shop_url); ?>" target="_blank"><?php _e('Edit', 'woo-order-splitter'); ?></a> - <a href="<?php echo esc_url($vendor_view_shop_url); ?>" target="_blank"><?php _e('View', 'woo-order-splitter'); ?></a>

</td>

</tr>

<?php

			endif;

		}

?>

</tbody>

</table>



<div><br />

<small><?php _e('Note: Products by unselected vendors will remain in the parent order.', 'woo-order-splitter'); ?></small>

</div>

<?php	

	}

?>	

<?php break; 
	default:
?>	
<div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_vendors_list">
<?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
</div>
<?php
	break;	
	}
?>	

<input type="hidden" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="0" />



<input type="hidden" name="split_action[wc_os_vendors][0]" value="" />

<input type="hidden" name="wc_os_vendors[0][]" value="" />

<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>


</form>