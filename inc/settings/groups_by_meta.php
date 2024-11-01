<?php
	wc_os_get_metadata_list(false, ($current-1)*$wc_os_per_page, false, false);
	function wc_os_get_metadata_list($attrib_only=false, $offset=0, $ajax=false, $alphabetic_grouping=true){
		

		global $wc_os_settings, $wc_os_multiple_warning, $wc_os_pro, $wpdb, $wc_os_per_page;
		
		$LIMIT = ($wc_os_per_page?'LIMIT '.($offset>0?$offset:0).','.$wc_os_per_page:'');
		
		$attrib_jar = array();		
		
		$metadata_query = "SELECT pm.meta_key, pm.meta_value, p.post_type FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p ON p.ID=pm.post_id WHERE p.post_type='product' AND pm.meta_value NOT LIKE '%{s:%' GROUP BY pm.meta_value $LIMIT";
		
		$metadata_results = $wpdb->get_results($metadata_query);
		if(!empty($metadata_results)){
			foreach($metadata_results as $metadata_result){
				if($metadata_result->post_type=='product'){
					$attrib_jar[$metadata_result->meta_key]['name'] = $metadata_result->meta_key;
					$attrib_jar[$metadata_result->meta_key]['label'] = $metadata_result->meta_key;
					$attrib_jar[$metadata_result->meta_key]['values'] = (array_key_exists('values', $attrib_jar[$metadata_result->meta_key])?$attrib_jar[$metadata_result->meta_key]['values']:array());
					if(
							!in_array($metadata_result->meta_value, $attrib_jar[$metadata_result->meta_key]['values'])
					){
						$attrib_jar[$metadata_result->meta_key]['values'][] = $metadata_result->meta_value;
					}
				}
			}
		}

		$groups_arr = wc_os_get_groups_range();

		$selected_attribs = $wc_os_settings['wc_os_metadata'];
		
		$wc_os_attributes_group = $wc_os_settings['wc_os_metadata_group'];
		
		
		
		$current = (isset($_POST['wos_pg'])?sanitize_wc_os_data($_POST['wos_pg']):0);
		
		$get_count = $wpdb->get_row("SELECT COUNT(*) AS total FROM ".$wpdb->prefix."postmeta WHERE meta_key='_product_attributes' GROUP BY meta_value");

		$total_attributes = $get_count->total;
						
		$total_pages = ($wc_os_per_page?ceil($total_attributes/$wc_os_per_page):0);	
		$radius = floor($total_pages/2);
		
				
?>
<?php if($total_pages>1 && !$ajax): ?>
<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_pagination.php')); ?>
<?php endif; ?>
<?php if(!$ajax): ?>
<?php get_wos_pg_limit_select('A'); ?>
<?php endif; ?>
<table border="0" class="wos_metadata_list_items dash-1">
<thead>

<th><input style="display:none" <?php checked(isset($wc_os_settings['wc_os_all_metadata']) && $wc_os_settings['wc_os_all_metadata']=='all_metadata')?> id="wc_os_all_metadata" name="wc_os_settings[wc_os_all_metadata]" type="checkbox" value="all_metadata" title="<?php _e('All listed and future metadata are included.','woo-order-splitter'); ?>" /></th>

<th><?php _e('Meta key', 'woo-order-splitter'); ?></th>

<th><?php _e('Meta value', 'woo-order-splitter'); ?></th>

<?php if($alphabetic_grouping): ?>
<th><?php _e('Split Group', 'woo-order-splitter'); ?></th>
<?php if(function_exists('wc_os_get_group_status_select')){ ?>
<th class="group_status_heading"><?php _e('Group Status', 'woo-order-splitter'); ?></th>
<?php } ?>
<?php endif; ?>


</thead>

<tbody>
<?php

		foreach($attrib_jar as $attrib_key=>$attrib_data){
		
					$wc_os_ie_name = 'wc_os_metadata[]';
					
					$attribute_group = '';
					
					
		
		?>
		
		
		
		<tr class="<?php echo in_array($attrib_key, $selected_attribs)?'selected':''; ?>">
		
		<td valign="top">
		<input <?php checked(in_array($attrib_key, $selected_attribs)); ?>  id="wia-<?php echo esc_attr($attrib_key); ?>" type="radio" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($attrib_key); ?>" />
		
		</td>
		
		
		<td valign="top" class="label"><label for="wia-<?php echo esc_attr($attrib_key); ?>"><?php echo esc_html($attrib_data['label']); ?></label></td>
		
		<td class="description"><label class="avs-attribs" for="wia-<?php echo esc_attr($attrib_key); ?>"><?php echo (is_array($attrib_data['values'])?wp_kses_post(implode(', ',  $attrib_data['values'])):''); ?></label>

        </td>

<?php if($alphabetic_grouping): ?>        
        <td class="split-group-actions">
            
        <select id="group-variation-<?php echo esc_attr($attrib_key); ?>" name="wc_os_metadata_group[<?php echo esc_attr($attrib_key); ?>]" data-ie="group_by_attributes">
    
        <option value=""></option>
    
            <?php foreach($groups_arr as $v): 
    
                $k = strtolower($v);
    
                $groups_selected = (array_key_exists($attrib_key, $wc_os_attributes_group)?$wc_os_attributes_group[$attrib_key]:array());

                if(!$attribute_group && $groups_selected == $k){
                    $attribute_group = $k;
                }                
    
            ?>
    
            <option <?php selected(array_key_exists($attrib_key, $wc_os_attributes_group) && $wc_os_attributes_group[$attrib_key]==$k); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
    
            <?php endforeach; ?>
    
        </select>        
        
        </td>
        
        
<?php endif; ?>		
		
		</tr>

<?php

		}
?>

		<tr style="display:none">
        <td colspan="<?php echo $alphabetic_grouping?4:3; ?>"><textarea name="wos_valid_metadata_actions"><?php echo esc_textarea((is_array($attrib_jar) && !empty($attrib_jar))?implode(',', array_keys($attrib_jar)):''); ?></textarea></td>
        </tr>
</tbody>
</table>
<?php		
		
	}		
?>	