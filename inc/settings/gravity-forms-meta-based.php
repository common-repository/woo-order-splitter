<?php
	if(!empty($_POST) && isset($_POST['gf_field'])){
	
		if (
			! isset( $_POST['wc_os_gf_meta_field'] )
			|| ! wp_verify_nonce( $_POST['wc_os_gf_meta_field'], 'wc_os_gf_meta' )
		) {
	
			_e('Sorry, your nonce did not verify.', 'woo-order-splitter');
			exit;
	
		} else {
	
	
			$gf_field = sanitize_wc_os_data($_POST['gf_field']);

			update_option('wc_os_gf_fields', $gf_field);
			
			
	
		}
	}
	$wc_os_gf_fields = get_option('wc_os_gf_fields');
	$wc_os_gf_fields = is_array($wc_os_gf_fields)?$wc_os_gf_fields:array();
	
?>			
<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="wos_gf_meta">
 	<div class="wc-os-plugin-icon"></div>
    <label></label>

    <input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />

    <?php wp_nonce_field( 'wc_os_gf_meta', 'wc_os_gf_meta_field' ); ?>

    <?php
	
	if($wc_os_gf):
?>
<div style="text-align:center;" class="mb-4">
<a href="https://plugins.svn.wordpress.org/woo-order-splitter/assets/Order-Splitter-Gravity-Forms.pdf" target="_blank"><i style="color:#F20F00; font-size:50px;" class="fas fa-file-pdf"></i></a>
<a class="ml-4" href="https://plugins.svn.wordpress.org/woo-order-splitter/assets/Order-Splitter-Gravity-Forms.ppsx" target="_blank"><i style="color:#CA4223; font-size:50px;" class="fas fa-file-powerpoint"></i></a>
</div>
<?php	
	
    $wc_os_ie_name = 'wc_os_gf_meta[]';
	

    switch($wc_os_settings['wc_os_ie']){

        case 'group_by_gf_meta':


			$gf_forms = $wpdb->get_results('SELECT * FROM `'.$wpdb->prefix.'gf_form` WHERE is_active=1 ORDER BY title ASC');
			if(!empty($gf_forms)){
?>

<?php if(!isset($_GET['gf']) || (isset($_GET['gf']) && $_GET['gf']!='all')): ?>
<a href="<?php echo admin_url(); ?>/admin.php?page=wc_os_settings&t=1&gf=all" title="<?php _e('Click here to expand all forms', 'woo-order-splitter'); ?>"><i class="fas fa-expand-arrows-alt"></i></a>
<?php endif; ?>
<ul>
<?php				
				foreach($gf_forms as $gf_form){
					
					$fields = array();
					$arrow_class = 'fa-angle-right';
					
					if(isset($_GET['gf']) && ($gf_form->id==$_GET['gf'] || $_GET['gf']=='all')){
						$gf_forms_meta = $wpdb->get_row('SELECT display_meta FROM `'.$wpdb->prefix.'gf_form_meta` WHERE form_id='.$gf_form->id);
						$display_meta = json_decode($gf_forms_meta->display_meta);
						$fields = $display_meta->fields;
						$arrow_class = 'fa-angle-down';
					}
?>
<li data-id="<?php echo $gf_form->id; ?>" data-title="<?php echo $gf_form->title; ?>"><strong><?php echo $gf_form->id; ?>: <?php echo $gf_form->title; ?></strong> <a href="<?php echo admin_url(); ?>/admin.php?page=wc_os_settings&t=1&gf=<?php echo $gf_form->id; ?>"><i class="fas <?php echo $arrow_class; ?>"></i></a>
<?php
	if(!empty($fields)){
?>

<ul title="<?php echo $display_meta->title; ?>">
<?php
		if(!empty($wc_os_gf_fields) && (!isset($_GET['gf']) || (isset($_GET['gf']) && $_GET['gf']!='all'))){
			foreach($wc_os_gf_fields as $wc_os_gf_field_form=>$wc_os_gf_field_data){
				if($gf_form->id!=$wc_os_gf_field_form && $wc_os_gf_field_form>0){
					if(!empty($wc_os_gf_field_data)){ 
						foreach($wc_os_gf_field_data as $wc_os_gf_field_id=>$wc_os_gf_field_status){
?>
<input style="display:none" checked="checked" type="checkbox" name="gf_field[<?php echo $wc_os_gf_field_form; ?>][<?php echo $wc_os_gf_field_id; ?>]" value="<?php echo $wc_os_gf_field_status; ?>" />
<?php				
						}
					}
				}
			}
		}
		foreach($fields as $field){ if($field->visibility!='visible' || $field->label==''){ continue; }
		
		$ticked = false;
		if(array_key_exists($gf_form->id, $wc_os_gf_fields)){
			if(array_key_exists($field->id, $wc_os_gf_fields[$gf_form->id])){
				$ticked = ($wc_os_gf_fields[$gf_form->id][$field->id]==1);
			}
		}
		 
?>	
	<li title="ID: <?php echo $field->id; ?>" data-checked="<?php echo $ticked?'yes':'no'; ?>" data-id="<?php echo $field->id; ?>">
    	<label><input <?php checked($ticked); ?> type="checkbox" name="gf_field[<?php echo $gf_form->id; ?>][<?php echo $field->id; ?>]" value="1" /><i><?php echo $field->label; ?></i> / <b><?php echo $field->type; ?></b> / <?php echo $field->description; ?></label>
      
    </li>
<?php
		}
?>		
</ul>
<?php
	}
?>	


</li>
<?php
				}
?>
</ul>				
<?php				
			}

?>


<?php





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

	<input type="hidden" name="gf_field[0][0]" value="1" />
    <input type="hidden" name="<?php echo esc_attr($wc_os_ie_name); ?>" value="0" />

    <p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>
<?php

	else:
		
		$compatible_string = __('Only Compatible With Gravity Forms');
		echo "<div style='text-align: center'>$compatible_string</div>";
?>
<div style="text-align:center;" class="mt-4">
<a href="https://plugins.svn.wordpress.org/woo-order-splitter/assets/Order-Splitter-Gravity-Forms.pdf" target="_blank"><i style="color:#F20F00; font-size:100px;" class="fas fa-file-pdf"></i></a>
<a class="ml-4" href="https://plugins.svn.wordpress.org/woo-order-splitter/assets/Order-Splitter-Gravity-Forms.ppsx" target="_blank"><i style="color:#CA4223; font-size:100px;" class="fas fa-file-powerpoint"></i></a>
</div>
<?php		
		
	endif;

?>
</form>
