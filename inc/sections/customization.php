<form class="nav-tab-content tab-customization hides" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

<input type="hidden" name="wos_tn" value="<?php echo isset($_GET['t'])?esc_attr($_GET['t']):'0'; ?>" />


<?php wp_nonce_field( 'wc_os_cuztomization', 'wc_os_cuztomization_field' ); ?>



        <h3 class="nav-tab-wrapper">

            <a class="nav-tab nav-tab-active" data-selection="woo_notices"><?php _e("WooCommerce Notices",'woo-order-splitter'); ?></a>

            <a class="nav-tab" data-selection="split_method"><?php _e("Split Methods Labels",'woo-order-splitter'); ?></a>
            
            <a class="nav-tab" data-selection="packages"><?php _e("Packages/Parcels",'woo-order-splitter'); ?></a>
            
		</h3>


<div class="wos_cart_notices sub-tab-content">

    <input type="hidden" name="sub_tab" value="<?php echo isset($_GET['sub_tab'])?esc_attr($_GET['sub_tab']):'0'; ?>" />

<div class="wos_cart_notices_left">

<h3><?php _e('WooCommerce Notices:', 'woo-order-splitter'); ?></h3>

<?php $wos_cart_notices = get_option( 'wc_os_cart_notices', true); ?>

<ul>



<li><textarea name="wos_cart_notices[shop]" placeholder="<?php _e('Shop page notice', 'woo-order-splitter'); ?>"><?php echo esc_textarea(isset($wos_cart_notices['shop'])?$wos_cart_notices['shop']:''); ?></textarea></li>

<li><textarea name="wos_cart_notices[product]" placeholder="<?php _e('Product page notice', 'woo-order-splitter'); ?>"><?php echo esc_textarea(isset($wos_cart_notices['product'])?$wos_cart_notices['product']:''); ?></textarea></li>

<li><textarea name="wos_cart_notices[cart]" placeholder="<?php _e('Cart page notice', 'woo-order-splitter'); ?>"><?php echo esc_textarea(isset($wos_cart_notices['cart'])?$wos_cart_notices['cart']:''); ?></textarea></li>

<li><textarea name="wos_cart_notices[checkout]" placeholder="<?php _e('Checkout page notice', 'woo-order-splitter'); ?>"><?php echo esc_textarea(isset($wos_cart_notices['checkout'])?$wos_cart_notices['checkout']:''); ?></textarea></li>

<li style="display:none"><textarea name="wos_cart_notices[thankyou]" placeholder="<?php _e('Thankyou page notice', 'woo-order-splitter'); ?>"><?php echo esc_textarea(isset($wos_cart_notices['thankyou'])?$wos_cart_notices['thankyou']:''); ?></textarea></li>



</ul>

<small><?php _e('Note: Keep these fields empty to disable.', 'woo-order-splitter'); ?></small>

</div>

<div class="wos_cart_notices_right">

<h3><?php _e('Notices Styles:', 'woo-order-splitter'); ?></h3>

<ul>

<li>

	<textarea name="wos_cart_notices[styles]" placeholder=".wos_notice_div {} .woocommerce-checkout .wos_notice_div{} .post-type-archive-product .wos_notice_div{}"><?php echo esc_textarea((isset($wos_cart_notices['styles']) && trim($wos_cart_notices['styles'])!=''?$wos_cart_notices['styles']:$wos_notices_css)); ?></textarea>

</li>

</ul>

</div>

</div>



<?php if(!empty($wos_actions_arr)): ?>

<div class="wos_actions_arr_section sub-tab-content hides">

<h3><?php _e('You can change the label and/or description for each split action(Products):', 'woo-order-splitter'); ?></h3>

<ul>

<?php foreach($wos_actions_arr as $key=>$action_data): ?>

	<li>

    	<div class="actions_left">

    	<input type="text" name="wos_actions[<?php echo esc_attr($key); ?>][title]" placeholder="<?php echo esc_attr($action_data['action']); ?>" value="<?php echo ((isset($wc_os_cust[$key]) && $wc_os_cust[$key]['title']!='')?$wc_os_cust[$key]['title']:''); ?>" />

        <span><?php echo esc_html($action_data['action']); ?></span>

        </div>

        <div class="actions_right">

        <input type="text" name="wos_actions[<?php echo esc_attr($key); ?>][description]" placeholder="<?php echo esc_attr($action_data['description']); ?>" value="<?php echo ((isset($wc_os_cust[$key]) && $wc_os_cust[$key]['description']!='')?$wc_os_cust[$key]['description']:''); ?>" />

        <span><?php echo esc_html($action_data['description']); ?></span>

        </div>

    </li>

<?php endforeach; ?>

</ul>

<small><?php _e('Note: It is for your convenience only. It will not change the functionality in any aspect.', 'woo-order-splitter'); ?></small>

</div>

<?php endif; ?>



<div class="wos_cart_notices sub-tab-content hides">

<div class="wos_cart_notices_left">


<?php 
	//$wos_packages_strings = get_option( 'wc_os_packages_strings', true); 
	$wos_packages_strings = (function_exists('wc_os_get_packages_strings')?wc_os_get_packages_strings():array());

	
?>

<ul>



<li><textarea name="wos_packages_strings[heading]" placeholder="<?php _e('Distribution of items in packages', 'woo-order-splitter'); ?>"><?php echo isset($wos_packages_strings['heading'])?esc_textarea($wos_packages_strings['heading']):''; ?></textarea></li>

<li><textarea name="wos_packages_strings[parcel-heading]" placeholder="<?php _e('Parcel #', 'woo-order-splitter'); ?>"><?php echo esc_textarea(isset($wos_packages_strings['parcel-heading'])?$wos_packages_strings['parcel-heading']:''); ?></textarea></li>



</ul>

<small><a href="https://gumpyguy.files.wordpress.com/2021/01/screenshot-33.png" target="_blank"><?php _e('Note', 'woo-order-splitter'); ?>: <?php _e('These labels will appear on the checkout page.', 'woo-order-splitter'); ?></a></small>


<ul style="margin:40px 0 0 0">
    <li title="<?php _e('Thank you page child orders heading filter.', 'woo-order-splitter'); ?>"><code><small>add_filter('wc_os_child_order_heading', '<?php echo $wc_os_current_theme; ?>_wc_os_child_order_heading', 2, 10);</small> <i class="fas fa-lightbulb"></i></code></li>
</ul>

</div>



</div>




<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>







</form>