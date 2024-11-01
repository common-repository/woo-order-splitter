<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="wos_woo_vendors_list">
 	<div class="wc-os-plugin-icon"></div>
    <label></label>

    <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

    <?php wp_nonce_field( 'wc_os_woo_vendors_action', 'wc_os_woo_vendors_field' ); ?>

    <?php
	
	if(class_exists('WC_Product_Vendors_Utils')):
	
    $wc_os_ie_name = 'wc_os_woo_vendors[]';

    switch($wc_os_settings['wc_os_ie']){

        case 'group_by_woo_vendors':

            $woo_args = array(

                    'taxonomy' => 'wcpv_product_vendors',
                    'hide_empty' => false,

            );

            $wc_os_woo_vendors_terms = get_terms($woo_args);


            ?>

            <div class="wc_os_vendor_role_selection_wrapper">



                <b><?php _e('Note:','woo-order-splitter')?> (<?php _e('Leave unselected for each split order with unique vendor items.','woo-order-splitter')?>)</b>



            </div>

            <?php



            if( !empty($wc_os_woo_vendors_terms) ){

                $groups_arr = wc_os_get_groups_range();

                $blog_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
                $shop_link = get_permalink( wc_get_page_id( 'shop' ) );

                $term_obj = array(
                        'term_id' => -1,
                        'name' => $blog_name,

                );

                $wc_os_woo_vendors_terms[] = (object) $term_obj;

                $wc_os_woo_vendors = array_key_exists('wc_os_woo_vendors', $wc_os_settings) ? $wc_os_settings['wc_os_woo_vendors'] : array();
                $wc_os_woo_vendors_selected = array();
                if(!empty($wc_os_woo_vendors)){
                    foreach($wc_os_woo_vendors as $v_group=>$v_ids){
                        if(is_array($v_ids) && !empty($v_ids)){
                            $wc_os_woo_vendors_selected = array_merge($wc_os_woo_vendors_selected, $v_ids);
                        }
                    }
                    $wc_os_woo_vendors_selected = array_filter($wc_os_woo_vendors_selected);
                }

				$wc_os_all_vendors = (isset($wc_os_settings['wc_os_all_vendors']) && $wc_os_settings['wc_os_all_vendors']=='all_vendors');	
				//wc_os_pree($wc_os_settings);
                ?>

                <table border="0" class="">

                    <thead>

                    <th title="<?php _e('Enable/Disable', 'woo-order-splitter'); ?>"><input <?php checked($wc_os_all_vendors); ?> id="wc_os_all_vendors" name="wc_os_settings[wc_os_all_vendors]" type="checkbox" value="all_vendors" title="<?php _e('All vendors listed and all future vendors added.','woo-order-splitter'); ?>" /></th>

                    <th><?php _e('Vendor Name', 'woo-order-splitter'); ?></th>

                    <th><?php _e('Split Group', 'woo-order-splitter'); ?></th>
                    
                    <th class=""><?php _e('Group Status', 'woo-order-splitter'); ?></th>

                    <th><?php _e('Actions', 'woo-order-splitter'); ?></th>

                    </thead>

                    <tbody>

                    <?php







                    $ticked = '';
                    foreach ($wc_os_woo_vendors_terms as $key => $vendor) {

                        $display_name = $vendor->name;



                        



                        if($display_name):
						
                            $ticked = (in_array($vendor->term_id, $wc_os_woo_vendors_selected) || $wc_os_all_vendors);

                        if($vendor->term_id != -1){

                            $vendor_edit_url = get_edit_term_link($vendor->term_id);
                            $vendor_view_shop_url = get_term_link($vendor);

                        }else{

                            $vendor_edit_url = get_edit_user_link();
                            $vendor_view_shop_url = $shop_link;
                        }
						$woo_vendor_group = '';


                            ?>



                            <tr>

                                <td><input id="wic-<?php echo esc_attr($vendor->term_id); ?>" type="checkbox" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="<?php echo esc_attr($vendor->term_id); ?>" <?php checked($ticked); ?> class="<?php //echo $ticked?'':'hides'; ?>" /></td>

                                <td><a target="_blank" href="<?php echo esc_url($vendor_edit_url); ?>"><?php echo esc_html($display_name); ?></a></td>



                                <td class="split-actions" data-id="<?php echo esc_attr($vendor->term_id); ?>">







                                    <select id="group-woo-vendor-<?php echo esc_attr($vendor->term_id); ?>" name="split_action[wc_os_woo_vendors][<?php echo esc_attr($vendor->term_id); ?>]" data-ie="group_by_woo_vendors">

                                        <option value=""></option>

                                        <?php foreach($groups_arr as $v):

                                            $k = strtolower($v);

                                            $groups_selected = (isset($wc_os_settings['wc_os_woo_vendors'][$k])?$wc_os_settings['wc_os_woo_vendors'][$k]:array());
                                            if(!$woo_vendor_group && in_array($vendor->term_id, $groups_selected)){
                                                $woo_vendor_group = $k;
                                            }
                                            ?>

                                            <option <?php selected(in_array($vendor->term_id, $groups_selected)); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>

                                        <?php endforeach; ?>

                                    </select>

                                </td>
								<?php if(function_exists('wc_os_get_group_status_select')){ wc_os_get_group_status_select('group_by_woo_vendors', $woo_vendor_group); } ?>
                                <td><a href="<?php echo esc_url($vendor_edit_url); ?>" target="_blank"><?php _e('Edit', 'woo-order-splitter'); ?></a> - <a href="<?php echo esc_url($vendor_view_shop_url); ?>" target="_blank"><?php _e('View', 'woo-order-splitter'); ?></a>

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
            <div class="wc_os_save_changes_alert dashicons-before dashicons-arrow-down-alt inside-wos_woo_vendors_list">
                <?php _e('"Save Changes" to continue with this split method.', 'woo-order-splitter'); ?>
            </div>
            <?php
            break;
    }



    ?>


    <input type="hidden" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="0" />



    <input type="hidden" name="split_action[wc_os_woo_vendors][0]" value="" />

    <input type="hidden" name="wc_os_woo_vendors[0][]" value="" />

    <p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>
<?php

	else:
		
		$compatible_string = __('Only Compatible With WooCommerce Product Vendors');
		echo "<div style='text-align: center'>$compatible_string</div>";
		
	endif;

?>
</form>