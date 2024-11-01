 <?php defined( 'ABSPATH' ) or die( __('No script kiddies please!', 'woo-order-splitter') );

	if ( !current_user_can( 'administrator' ) ) {

		wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-order-splitter' ) );

	}
	


	global $wc_os_url, $wpdb, $wc_os_data, $wc_os_pro, $wc_os_activated, $wc_os_settings, $wc_os_currency, $wc_os_premium_copy, $wos_actions_arr, $wc_os_cust, $wos_notices_css, $wc_os_general_settings, $wc_os_multiple_warning, $wc_os_per_page, $wc_os_shipping, $wc_os_order_statuses_class, $wc_os_wcfm_installed, $wc_os_shipping_cost, $wc_os_gf, $wc_os_current_theme, $wc_os_recorded_templates_query;


	$wc_os_cust = get_option( 'wc_os_cuztomization', array() );
    
	$wc_os_order_statuses = wc_get_order_statuses();

	$wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);

	$current = (isset($_POST['wos_pg'])?sanitize_wc_os_data($_POST['wos_pg']):(isset($_GET['pg'])?sanitize_wc_os_data($_GET['pg']):0));

	$WC_OS_Shipping = new WC_OS_Shipping();
	
	$WC_OS_Shipping_Settings = ($WC_OS_Shipping->get_settings());	
	
	$products_related_methods = false;
	switch($wc_os_settings['wc_os_ie']){ 
		case 'default':
		case 'exclusive':
		case 'inclusive':
		case 'shredder':
		case 'io':
		case 'quantity_split':
		case 'groups':
			$products_related_methods = true;
		break;
	}


	//wc_os_extend_groups

	

	





?>

<span style="float:right; color:orange; font-size:12px; margin:0 20px 0 0;"><?php echo $wc_os_current_theme; ?></span>





<div class="wrap wc_settings_div tab-<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>">


        <?php if(function_exists('wc_os_method_screen_option_html')){wc_os_method_screen_option_html();} ?>







        <div class="icon32" id="icon-options-general"><br></div><h2><?php echo esc_html($wc_os_data['Name']); ?> <?php echo esc_html('('.$wc_os_data['Version'].($wc_os_pro?') Pro':')')); ?> - <?php _e("Settings",'woo-order-splitter'); ?> <?php if(!$wc_os_pro){ ?><a class="gopro" target="_blank" href="<?php echo esc_url($wc_os_premium_copy); ?>"><?php _e("Go Premium",'woo-order-splitter'); ?></a><?php } ?></h2> 

    

         

           

        <h2 class="nav-tab-wrapper">

            <a class="nav-tab nav-tab-active" data-tab="general-settings"><?php _e("General Settings",'woo-order-splitter'); ?> <i class="fas fa-tools"></i></a>

            <a class="nav-tab" data-tab="split-settings"><?php _e("Order Split Settings",'woo-order-splitter'); ?> <i class="fas fa-sliders-h"></i> <?php echo $wc_os_settings['wc_os_ie']?'<span class="split-method-short" title="'.$wos_actions_arr[$wc_os_settings['wc_os_ie']]['action'].'">'.$wc_os_settings['wc_os_ie'].'</span>':''; ?></a>

            <a class="nav-tab has-sub-tabs" data-tab="customization"><?php _e("Customization",'woo-order-splitter'); ?> <i class="far fa-edit"></i></a>

            <a class="nav-tab email_tab" data-tab="emails"><?php _e("Emails",'woo-order-splitter'); ?> <i class="far fa-envelope"></i></a>

            <a class="nav-tab has-sub-tabs" data-name="shipping" data-tab="shipping"><?php _e("Shipping",'woo-order-splitter'); ?> <i class="fas fa-cubes"></i></a>
            
            <a class="nav-tab" data-name="coupons" data-tab="coupons"><?php _e("Coupons",'woo-order-splitter'); ?> <i class="fas fa-tags"></i></a>

            <a class="nav-tab" data-tab="order-statuses"><?php _e("Order Statuses",'woo-order-splitter'); ?> <i class="far fa-clipboard"></i></a>


            <a class="nav-tab advanced_tab has-sub-tabs" data-tab="advanced-settings"><?php _e("Advanced Settings",'woo-order-splitter'); ?> <i class="fas fa-cogs"></i></a>

			<a class="nav-tab has-sub-tabs" id="wc_os_logger" data-tab="logs"><?php _e("Logs",'woo-order-splitter'); ?> <i class="fas fa-route"></i></a>
            
            <a class="nav-tab" style="float:right" id="wc_os_help" data-tab="help"><?php _e("Help",'woo-order-splitter'); ?> <i class="fas fa-question-circle"></i></a>

            

        </h2>      







<?php if(!$wc_os_activated): ?>

<div class="wc_os_notes">

<h2><?php _e("You need WooCommerce plugin to be installed and activated.",'woo-order-splitter'); ?> <?php _e("Please",'woo-order-splitter'); ?> <a href="plugin-install.php?s=woocommerce&tab=search&type=term" target="_blank"><?php _e("Install",'woo-order-splitter'); ?></a> <?php _e("and",'woo-order-splitter'); ?>/<?php _e("or",'woo-order-splitter'); ?> <a href="plugins.php?plugin_status=inactive" target="_blank"><?php _e("Activate",'woo-order-splitter'); ?></a> WooCommerce <?php _e("plugin to proceed",'woo-order-splitter'); ?>.</h2>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
</div>

<?php exit; endif; ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/general-settings.php')); ?>


<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/split-settings.php')); ?>


<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/customization.php')); ?>


<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/wc_os_email_section.php')); ?>


<?php if(class_exists('WC_OS_Shipping')){ $wc_os_shipping->wc_os_shipping_tab_html(); }else{ ?>
<div class="nav-tab-content hides tab-shipping mt-4">
    <div class="alert alert-primary" role="alert">
      <?php _e('This feature is available in premium version.', 'woo-order-splitter'); ?> <a class="btn-sm btn-warning" href="<?php echo $wc_os_premium_copy; ?>" target="_blank"><?php _e('Go Premium', 'woo-order-splitter'); ?></a>
    </div>
</div>
<?php } ?>


<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/settings/coupons.php')); ?>


<?php if(class_exists('WC_OS_Order_Status')){ $wc_os_order_statuses_class->wcos_order_statuses_html(); }else{ ?>
<div class="nav-tab-content hides tab-order-statuses mt-4">
    <div class="alert alert-primary" role="alert">
      <?php _e('This feature is available in premium version.', 'woo-order-splitter'); ?> <a class="btn-sm btn-warning" href="<?php echo $wc_os_premium_copy; ?>" target="_blank"><?php _e('Go Premium', 'woo-order-splitter'); ?></a>
    </div>
</div>
<?php } ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/advanced-settings.php')); ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/logger.php')); ?>

<?php include_once(realpath(WC_OS_PLUGIN_DIR.'/inc/sections/help.php')); ?>


</div>
<script type="text/javascript" language="javascript">

jQuery(document).ready(function($) {

	$('select[name="wc_os_settings[wc_os_additional][split_lock][]"').multiselect({
		columns: 1,
		placeholder: '<?php _e('No Lock (Default)', 'woo-order-splitter'); ?>',
		search: false,
		searchOptions: {
			'default': '<?php _e('No Lock (Default)', 'woo-order-splitter'); ?>'
		},
		selectAll: false,
		maxWidth: 505
	});

	<?php if(isset($_POST['wos_tn'])): ?>
		$('h2.nav-tab-wrapper .nav-tab:nth-child(<?php echo esc_attr($_POST['wos_tn'])+1; ?>)').click();

	<?php endif; ?>

	<?php if((isset($_GET['sub_tab']) || isset($_POST['sub_tab']))  && (isset($_GET['t']) || isset($_POST['wos_tn']))): ?>

        <?php

            $t = isset($_POST['wos_tn']) ? esc_attr($_POST['wos_tn']) : esc_attr($_GET['t']);
            $sub_tab = isset($_POST['sub_tab']) ? esc_attr($_POST['sub_tab']) : esc_attr($_GET['sub_tab']);


        ?>


		$('.nav-tab-content').eq(<?php echo esc_attr($t); ?>).find('.nav-tab[data-selection="<?php echo esc_attr($sub_tab); ?>"]').click();


	<?php endif; ?>
	
	<?php if(isset($_GET['wc_os_mk'])){ ?>
		$('h2 a.nav-tab.advanced_tab').click();
	<?php } ?>	

});	

</script>



<style type="text/css">

	
		
	div#ms-list-1 > button {
		background-color: transparent !important;
		cursor: pointer;
		margin: 0 0 0 12px;
		padding: 6px 12px;
		border-radius: 2px;
		border-color: #7e8993;
		width: 60%;
	}	
	div#ms-list-1 div.ms-options {
		margin-left: 35px;
		background-color: rgba(201,201,222,1);
	}
	div#ms-list-1 div.ms-options li label {
		padding-left: 26px;
		padding-top: 5px;
	}
	div#ms-list-1 div.ms-options li.selected{
		background-color: rgba(163,73,164,0.4);
	}
	div#ms-list-1 div.ms-options li.selected label{
		background-color: rgba(255,255,255,0.8);
		color:#000;
	}
	
	#wpfooter{

		display:none;

	}

<?php if(!$wc_os_pro): ?>



	#adminmenu li.current a.current {

		font-size: 12px !important;

		font-weight: bold !important;

		padding: 6px 0px 6px 12px !important;

	}

	#adminmenu li.current a.current,

	#adminmenu li.current a.current span:hover{

		color:#9B5C8F;

	}

	#adminmenu li.current a.current:hover,

	#adminmenu li.current a.current span{

		color:#fff;

	}	

<?php endif; ?>

	.woocommerce-message, .update-nag, #message, .notice.notice-error, .error.notice, div.notice, div.fs-notice, div.wrap > div.updated{ display:none !important; }

	li.current {		
		background-color: rgba(155,92,143, 0.2);
	}
	li.current:hover {
		background-color: rgba(155,92,143, 0.8);
	}
	/*! ========================================================================
	 * Bootstrap Toggle: bootstrap-toggle.css v2.2.0
	 * http://www.bootstraptoggle.com
	 * ========================================================================
	 * Copyright 2014 Min Hur, The New York Times Company
	 * Licensed under MIT
	 * ======================================================================== */
	.checkbox label .toggle,.checkbox-inline .toggle{margin-left:-20px;margin-right:5px}
	.toggle{position:relative;overflow:hidden}
	.toggle input[type=checkbox]{display:none}
	.toggle-group{position:absolute;width:200%;top:0;bottom:0;left:0;transition:left .35s;-webkit-transition:left .35s;-moz-user-select:none;-webkit-user-select:none}
	.toggle.off .toggle-group{left:-100%}
	.toggle-on{position:absolute;top:0;bottom:0;left:0;right:50%;margin:0;border:0;border-radius:0}
	.toggle-off{position:absolute;top:0;bottom:0;left:50%;right:0;margin:0;border:0;border-radius:0}
	.toggle-handle{position:relative;margin:0 auto;padding-top:0;padding-bottom:0;height:100%;width:0;border-width:0 1px}
	.toggle.btn{min-width:59px;min-height:34px}
	.toggle-on.btn{padding-right:24px}
	.toggle-off.btn{padding-left:24px}
	.toggle.btn-lg{min-width:79px;min-height:45px}
	.toggle-on.btn-lg{padding-right:31px}
	.toggle-off.btn-lg{padding-left:31px}
	.toggle-handle.btn-lg{width:40px}
	.toggle.btn-sm{min-width:50px;min-height:30px}
	.toggle-on.btn-sm{padding-right:20px}
	.toggle-off.btn-sm{padding-left:20px}
	.toggle.btn-xs{min-width:35px;min-height:22px}
	.toggle-on.btn-xs{padding-right:12px}
	.toggle-off.btn-xs{padding-left:12px}
</style>