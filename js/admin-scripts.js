jQuery(document).ready(function($){
		function pree(arr){
			console.log(arr);
		}	
		$('body').on('click', '.wc-os-status-lock', function(){
			$(this).toggleClass('locked');
			$('#_wc_os_status_locked').val($(this).hasClass('locked')?'yes':'no');
			
		});
		$('a.wc-os-cron-jobs').on('click', function(){
			$('a.advanced_tab').click();
			$('a[data-selection="cron_jobs"]').click();
		});
	
		$('.backorder-automation').on('click', function(){
			$(this).toggleClass('selected');
			if($('#backorder-automation').val()=='yes'){
				$('#backorder-automation').val('no');
			}else{
				$('#backorder-automation').val('yes');
			}
		});
		$('.wc-os-screen-options').on('click', function(){
			$('#show-settings-link').click();
		});

		function wos_hide_methods(){
			setTimeout(function(){

				$('.wc_settings_div #screen-options-wrap .group_input[name="method_selection"]').change();
			}, 1100);

		}
		$('.woo_inst_checkout_options').on('click', function(){
			if($(this).is(':checked')){
				$(this).parent().addClass('selected');
			}else{
				$(this).parent().removeClass('selected');
			}
		});
		
		$('#wc_os_packages_overview').on('change', function(){
			if($(this).is(':checked')){
				$('.wc_os_packages_overview_items').removeClass('hides').show();
			}else{
				$('.wc_os_packages_overview_items').hide();
			}
		});
		
		var wc_os_auto_forced = 'input[name="wc_os_general_settings[wc_os_auto_forced]"]';
		var wc_os_customer_permission = 'input[name="wc_os_general_settings[wc_os_customer_permission]"]';
		var wc_os_extend_groups = 'input[name="wc_os_general_settings[wc_os_extend_groups]"]';
		
		$('body').on('change', wc_os_auto_forced, function(){
			if($(this).is(':checked') && $(wc_os_customer_permission).is(':checked')){
				$(wc_os_customer_permission).click();
			}else{
			}
		});
		
		
		$('body').on('click', wc_os_customer_permission, function(){
			if($(this).is(':checked')){
				$('#wc_os_customer_permission_text_wrapper').show();
				if($(wc_os_auto_forced).is(':checked')){
					$('#wc_os_customer_permission_text_wrapper').append('<strong>'+wos_obj.permission_text_warning+'</strong>');
				}
			}else{
				$('#wc_os_customer_permission_text_wrapper').hide();
				$('#wc_os_customer_permission_text_wrapper strong').remove();
			}
		});	
		$('body').on('click', wc_os_extend_groups, function(){
			if($(this).is(':checked')){
				$('.wc_os_extend_groups').show();				
			}else{
				$('.wc_os_extend_groups').hide();				
			}
		});				
		
		
		
		$('body').on('change', 'select[name="wc_order_action"]', function(){

			var obj_wrapper = $('#order_line_items');
			if(obj_wrapper.length>0){
				obj_wrapper.removeClass('wc_os_selection')
				obj_wrapper.find('tr').removeClass('selected');
				$('.wc-os-defalut-split').remove();
				obj_wrapper.find('input[name^="wc_os_ps"]').remove();
				$('.woocommerce_order_items_wrapper .wc_os_split_selection').remove();


				if($(this).val()=='wc_os_split_action'){
					obj_wrapper.addClass('wc_os_selection')
					obj_wrapper.find('tr.item').addClass('selected');
					
					$.each($('tbody#order_line_items > tr.item'), function(){
						
						var item_id = (typeof $(this).data('order_item_id')!='unefined'?$(this).data('order_item_id'):0);
						//console.log(item_id);
						if(item_id in wos_obj.order_items){
							
							product_id = wos_obj.order_items[item_id];
						
							if(wos_obj.in_stock_items.includes(product_id)){
								$(this).addClass('instock');
							}
							if(wos_obj.backorder_items.includes(product_id)){
								$(this).addClass('outstock');
							}
							
						}

					});
								
					$('.woocommerce_order_items_wrapper').prepend('<div class="wc_os_split_selection"><iframe src="https://www.youtube.com/embed/wjClFEeYEzo" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>');				

					$('#woocommerce-order-items > div.inside').eq(0).find('.wc-order-items-editable').eq(0).prepend('<div class="wc-os-defalut-split">'+wos_obj.wc_os_defalut_split_1+'<br /><br />'+wos_obj.wc_os_defalut_split_2+'</div>');
					
					
					$.each(obj_wrapper.find('tr.item'), function(){
						var order_item_id = (typeof $(this).data('order_item_id')!='unefined'?$(this).data('order_item_id'):0);
						if(order_item_id){
							$(this).find('td.thumb').eq(0).append('<input type="hidden" name="wc_os_ps[]" value="'+order_item_id+'" />');
						}
					});
					
					$('ul.order_actions li button.save_order').hide();
					$('ul.order_actions li button.wc-reload').addClass('button-primary');
					
					
				}else{
					$('ul.order_actions li button.save_order').show();
					$('ul.order_actions li button.wc-reload').removeClass('button-primary');
				}
			}
		});
		

		
		$('tbody#order_line_items > tr.item').on('click', function(){
			if($(this).find('input[name^="wc_os_ps"]').length>0){
				$(this).find('input[name^="wc_os_ps"]').remove();
				$(this).removeClass('selected');
			}else{
				var order_item_id = (typeof $(this).data('order_item_id')!='unefined'?$(this).data('order_item_id'):0);
				if(order_item_id){
					$(this).find('td.thumb').eq(0).append('<input type="hidden" name="wc_os_ps[]" value="'+order_item_id+'" />');
					$(this).addClass('selected');
				}
			}
		});		
		
		if($('select[name="wc_order_action"]').length>0)
		$('select[name="wc_order_action"]').change();
		
		$('.wc-os-defined-rules').on('click', 'ul > li > a', function(){	
			var ask = confirm(wos_obj.defined_rules_confirm);
			if(ask){
				var elem = $(this).parents().eq(0);
				var data = {
					'action': 'wos_rules_action',
					'key': $(this).parents().eq(0).data('key')
				};
				
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					//alert('Got this from the server: ' + response);
					elem.remove();
				});
								
				
			}
		});
		
		
		function parse_query_string(query) {
		  var vars = query.split("&");
		  var query_string = {};
		  for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split("=");
			// If first entry with this name
			if (typeof query_string[pair[0]] === "undefined") {
			  query_string[pair[0]] = decodeURIComponent(pair[1]);
			  // If second entry with this name
			} else if (typeof query_string[pair[0]] === "string") {
			  var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
			  query_string[pair[0]] = arr;
			  // If third or later entry with this name
			} else {
			  query_string[pair[0]].push(decodeURIComponent(pair[1]));
			}
		  }
		  return query_string;
		}		

		$('.wc_settings_div').on('click', 'h2.nav-tab-wrapper a.nav-tab', function(){
			
			var tab_data = $(this).data('tab');

			if($('.nav-tab-content.tab-'+tab_data).length>0){
				

			
				wos_obj.wc_os_pg = parseInt(wos_obj.wc_os_pg);
				$(this).siblings().removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				
				$('.nav-tab-content, form:not(.wrap.wc_settings_div .nav-tab-content):not(.ignore)').hide();

				$('.nav-tab-content.tab-'+tab_data).removeClass('hides').show();
				
				switch(tab_data){
					case 'split-settings':
						$('.split-settings-dashboard').removeClass('hides').show();
					break;
				}				
				
				var state_url = wos_obj.this_url+'&t='+$(this).index()+(wos_obj.wc_os_pg>0?'&pg='+wos_obj.wc_os_pg:'');
				window.history.replaceState('', '', state_url);
				$('form input[name="wos_tn"]').val($(this).index());
				wos_obj.wc_os_tab = $(this).index();
				wos_trigger_selected_ie();
				$('.wrap.wc_settings_div').attr('class', 'wrap wc_settings_div tab-'+$(this).index());
				$('.wrap.wc_settings_div').parent().attr('class', 'wc_settings_wrapper tab-'+$(this).index());
				
	
				if(wos_obj.wc_os_pg>0){
					var current_pg = $('.wrap.wc_settings_div form.nav-tab-content:visible ul.wos_pagination li');
					if(current_pg.find('a.nums[data-num="'+wos_obj.wc_os_pg+'"]').length>0){
						current_pg.find('a.nums[data-num="'+wos_obj.wc_os_pg+'"]').parent().addClass('wos_current');
						//console.log(current_pg.find('a.nums[data-num="'+wos_obj.wc_os_pg+'"]').parent());
					}else if(current_pg.find('a.nums[data-num="1"]').length>0){
						current_pg.find('a.nums[data-num="1"]').parent().addClass('wos_current');
						//console.log(current_pg.find('a.nums[data-num="1"]'));
					}
				}
							
				if($(this).data('name') == 'shipping'){
				
					$('.wc_os_shipping .nav-tab[data-selection="'+wos_obj.shipping_selection+'"]').click();
				
				}
	
				if($(this).index() == 1){
					$('.wc_os_screen_option').show();
					wos_hide_methods();
				}else{
					$('.wc_os_screen_option').hide();
					$('.wc_os_screen_option #screen-meta').hide();
				}
				
				
				if($(this).hasClass('has-sub-tabs')){
					setTimeout(function(){
						var current_url = new URL(window.location.href);
						var sub_tab = current_url.searchParams.get('sub_tab');
						sub_tab = ((sub_tab==null)?wos_obj.wc_os_sub_tab:sub_tab);
						
						if(sub_tab == null){	
							$('.wc_settings_div h3.nav-tab-wrapper a.nav-tab:first-child').click();
						}else if(sub_tab!=''){
							console.log($('.wc_settings_div h3.nav-tab-wrapper a.nav-tab[data-selection="'+sub_tab+'"]'));
							$('.wc_settings_div h3.nav-tab-wrapper a.nav-tab[data-selection="'+sub_tab+'"]').click();
						}
	
	
					});
				}
	
				$('.wc_settings_div form').prop('action', state_url);		
				
				if($(this).hasClass('email_tab') && wos_obj.wos_email_tab){
	
					setTimeout(function(){
	
						$('div.wc_os_emails_section .wc_os_tab button.wc_os_tab_links[data-target="'+wos_obj.wos_email_tab+'"]').click();
	
					}, 1);
	
				}	
				
			}
		});				
		
		

		$('.wc_settings_div').on('click', 'h3.nav-tab-wrapper a.nav-tab', function(){

			$(this).siblings().removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
//			$('.nav-tab-content:visible > .sub-tab-content').hide();
//			$('.nav-tab-content:visible > .sub-tab-content').eq($(this).index()).show();
            var parent_content = $(this).parents('.nav-tab-content:visible');
            var parent_sub_content = parent_content.find('.sub-tab-content');
            parent_content.find('.sub-tab-content').hide();
			parent_content.find('.sub-tab-content').eq($(this).index()).show();

			var selection = $(this).data('selection');
			var current_url = new URL(window.location.href);
			var current_tab = current_url.searchParams.get('t');
            current_tab = parseInt(current_tab != undefined ? current_tab : 0)+1;
            var current_tab_content = $('.nav-tab-content').eq(current_tab);

            if(current_tab_content.find('.nav-tab[data-selection="'+selection+'"]').length > 0){

                parent_sub_content.find('input[name="sub_tab"]').val(selection);


                current_url.searchParams.set('sub_tab', selection);
                window.history.replaceState('', '', current_url.href);
				
				$('.order-log-toggle').hide();
				$('.email-log-toggle').hide();
				$('.debug-log-toggle').hide();
				
				switch(selection){
					case 'debug_log':
						$('.debug-log-toggle').show();
						
					break;
					case 'email_log':
						$('.email-log-toggle').show();
						
					break;
					case 'order_log':						
						$('.order-log-toggle').show();	
																
					break;
					default:
					break;
				}

            }
			
		});				
				
		var query = window.location.search.substring(1);
		var qs = parse_query_string(query);		
		
		if(typeof(qs.t)!='undefined'){
			$('.wc_settings_div a.nav-tab').eq(qs.t).click();
			
		}
		
		if(typeof(qs.clone)!='undefined'){
			window.history.replaceState('', '', wos_obj.orders_list);
		}
		
		if($('.wc_settings_div').length>0)
		$('.wc_settings_div').show();		
		
		$('.wc_os_console input[name="wc_os_order_test"]').on('click', function(){
			
			var order_id = $('input[name="wc_os_order_id"]');
			if(order_id.val()!=''){
				var data = {
					'action': 'wos_troubleshooting',
					'order_id': $.trim(order_id.val())
				};				
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					
					if($('.wc_os_console ul').length==0)
					$('.wc_os_console').append('<ul></ul>');
					
					//alert('Got this from the server: ' + response);
					var resp = $.parseJSON(response);
					

					$('.wc_os_console ul').prepend('<li style="background-color:rgba('+resp.color.r+','+resp.color.g+','+resp.color.b+',0.05);"></li>');
					$('.wc_os_console ul li').eq(0).html(resp.html);
				});
								
				
			}
		});	
		
		$('#woocommerce-order-items > div.inside .wc-order-items-editable').on('click', '.conflict_status ul li a', function(){
			
			var order_id = $('input[name="wc_os_order_id"]');
			
			if(order_id!=''){
				$('.wos_loading').fadeIn();
				$('.conflict_status ul li a.forced').removeClass('forced');
				$(this).addClass('forced');
				var data = {
					'action': 'wos_forced_ie',
					'order_id': $('#post_ID').val(),
					'order_action': $(this).data('action')
				};				
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					
					setTimeout(function(){ document.location.href = wos_obj.orders_list; }, 1000);
					
				});
								
				
			}
		});			
		$('.wc_settings_div').on('click', '.wos_products_list td.split-actions ul li input[type="radio"]', function(){
			var wc_os_ie_value = $(this).val();
			var row_id = $(this).attr('id').replace(wc_os_ie_value+'-', '');
			$('#wip-'+row_id).prop({'name':'wc_os_settings[wc_os_products]['+wc_os_ie_value+'][]', 'checked':'checked'}).removeClass('hides');
			
			if(!$('#wc_os_all_product').is(':checked')){
				$('.wc_os_ahead .active').removeClass('active');
				//$('.wc_os_ahead + .wc_os_notes, .wc_os_ahead + .wc_os_notes + p').hide();
			}else{
			}
			
		});
				
		$('.wc_settings_div').on('change', '.wos_products_list td.split-actions select[name^="split_action"]:not(.wos_product_action)', function(){
			
			$(this).parents().eq(1).find('input[type="checkbox"]').addClass('hides');//.removeAttr('checked')
			$(this).parents().eq(1).find('input[type="radio"]').removeAttr('checked');
			if($.trim($(this).val())!=''){
				$(this).parents().eq(1).find('input[type="checkbox"]').prop({'name':'wc_os_settings[wc_os_products]['+$(this).data('ie')+']['+$(this).val()+'][]', 'checked':'checked'}).removeClass('hides');
			}
		});	
		
		$('.wc_settings_div').on('change', 'form.wos_categories_list td.split-actions select[name^="split_action"]:not(.wos_product_action)', function(){		
			
			$(this).parents().eq(1).find('input[type="checkbox"]').addClass('hides');//.removeAttr('checked')
			$(this).parents().eq(1).find('input[type="radio"]').removeAttr('checked');
			
			if($.trim($(this).val())!=''){
				$(this).parents().eq(1).find('input[type="checkbox"]').prop({'name':'wc_os_cats['+$(this).data('ie')+']['+$(this).val()+'][]', 'checked':'checked'}).removeClass('hides');
			}

			var group_cats_selection = $('input[name^="wc_os_cats[group_cats]"][type="checkbox"]:checked:visible').length;
			
			$('.wc-os-gc').hide();
			if(group_cats_selection==0){
				$('.wc-os-gc.wc-os-gc-no-selection').show();
			}
			if(group_cats_selection==1){
				$('.wc-os-gc.wc-os-gc-one-selection').show();
			}
			if(group_cats_selection>=2){
				$('.wc-os-gc.wc-os-gc-multi-selection').show();
			}
			
		});
		
		$('.wc_settings_div').on('change', '.wos_vendors_list td.split-actions select[name^="split_action"]:not(.wos_product_action)', function(){	
			
			$(this).parents().eq(1).find('input[type="checkbox"]').addClass('hides');//.removeAttr('checked')
			$(this).parents().eq(1).find('input[type="radio"]').removeAttr('checked');
			
			var vendor_status_obj = $(this).parents().eq(1).find('input[type="checkbox"]');
			var group_val = $.trim($(this).val());
			
			if(group_val){ }else if(group_val==''){ group_val = 'default'; }
			vendor_status_obj.prop({'name':'wc_os_vendors['+group_val+'][]'}).removeClass('hides');
			
			
			//console.log(vendor_status_obj);
			if(vendor_status_obj.is(':checked')){
				vendor_status_obj.prop({'name':'wc_os_vendors['+group_val+'][]', 'checked':'checked'}).removeClass('hides');
			}else{
				
			}
		});		        
		$('.wc_settings_div').on('change', '.wos_woo_vendors_list td.split-actions select[name^="split_action"]:not(.wos_product_action)', function(){

            $(this).parents().eq(1).find('input[type="checkbox"]').addClass('hides');//.removeAttr('checked')
            $(this).parents().eq(1).find('input[type="radio"]').removeAttr('checked');
            if($.trim($(this).val())!=''){
                $(this).parents().eq(1).find('input[type="checkbox"]').prop({'name':'wc_os_woo_vendors['+$(this).val()+'][]', 'checked':'checked'}).removeClass('hides');
            }
        });		
		$('.wc_settings_div').on('click', '.wos_products_list td input[type="checkbox"]', function(){
			var obj = $(this).parents().eq(1);
			var cv_obj = obj.find('td.split-actions');
			var cv_obj_radio = cv_obj.find('ul li input[type="radio"]:checked');
			var cv_obj_select = cv_obj.find('select');
			if($(this).is(':checked')){
				//console.log(obj);//.data('cv')
				if(typeof obj.data('cv')!='undefined' && obj.data('cv')){
					obj.find('td.split-actions ul li input[id^="'+obj.data('cv')+'"]').prop('checked','checked');
					
				}else{
					return false;
				}
			}else{		
			
				if(cv_obj_radio.length>0){		
					obj.attr('data-cv',cv_obj_radio.val());
					cv_obj_radio.removeAttr('checked');
				}
				
				if(cv_obj_select.length>0){				
					cv_obj_select.val('');
				}
			}	
		});
		
		
		$('.wc_settings_div').on('click', '.wos_categories_list td input[type="checkbox"], .wos_vendors_list td input[type="checkbox"], .wos_woo_vendors_list td input[type="checkbox"]', function(){
			var obj = $(this).parents().eq(1);
			var cv_obj = obj.find('td.split-actions select');
			//console.log(cv_obj);
			//console.log($(this).is(':checked'));

			var wc_os_vendors = ($(this).attr('name').substr(0, 13)=='wc_os_vendors');
			if(wc_os_vendors && cv_obj.val()==''){
				$(this).prop('name', 'wc_os_vendors[default][]');
				//$(this).parents().eq(1).find('select').val('');
			}
			
			//console.log(cv_obj.val());

			if($(this).is(':checked') && cv_obj.val()!=''){
				
			}else{				
				//cv_obj.val('');
				//$(this).addClass('hides');
			}	
		});
		
		$('.wc_settings_div').on('click', '.wos_categories_list td.split-actions ul li input[type="radio"]', function(){
			var wc_os_ie_value = $(this).val();
			var row_id = $(this).attr('id').replace(wc_os_ie_value+'-', '');
			$('#wic-'+row_id).prop({'name':'wc_os_cats['+wc_os_ie_value+'][]', 'checked':'checked'});	
		});		
		
		$('.wc_settings_div').on('click', 'input[name="wc_os_settings[wc_os_ie]"]', function(){
			//console.log($(this));
			//$('.wos_vendors_list, .wos_woo_vendors_list, .wos_attributes_only_list, .wos_attributes_values_list, .group_by_order_item_meta_list, .group_by_gf_meta_list, .wos_categories_qty, .wos_categories_list, .wos_products_list, .wos_acf_wrapper, .wc_os_acf_values_list, .wc_os_partial_payment_list, .wos_products_search').hide();
			
			
			
			var method_name = $(this).val();
			var method_type = $(this).closest('li').data('type');			
			
			if(typeof method_type=='undefined'){
				
				if($('li.wc_os_ie_products i span.selected').length>0){

					var i_selected_method = $('li.wc_os_ie_products i span.selected').removeClass('selected').removeClass('active').attr('class').replace('i-', '');					
					var i_next_method = '.wc_settings_div input[name="wc_os_settings[wc_os_ie]"][value="'+i_selected_method+'"]';
					//console.log(i_next_method);
					if($(i_next_method).length>0){
						$('li.wc_os_ie_products i span.selected').removeClass('selected');
						$(i_next_method).click();
						return;
					}
					
				}else{
					$('.wc_os_ie_wrapper > li.wc_os_ie_products > ul').show();
					
				}

			}else{
				
			}
			//console.log(method_type);		
				
			switch(method_type){
				case 'product_related':
					$('.dashboard-settings-form-submit').show();
				break;
				default:
					$('.dashboard-settings-form-submit').hide();
				break;
			}
			
			switch($(this).attr('id')){
				
				case 'wc_os_ie_products':
					//$(this).parents().eq(1).find('ul').toggle();
				break;
				default:

					$('.wc_os_ie_wrapper').find('.active').removeClass('active');
					//$('.wc_os_ahead + .wc_os_notes, .wc_os_ahead + .wc_os_notes + p').show();
					var wc_os_ie = method_name;
					var data = {
						'action': 'wos_auto_settings',
						'wc_os_ie': wc_os_ie
					};				
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					$('input[name^="wc_os_settings[wc_os_products]"]').removeAttr('checked').addClass('hides');
					$('td.split-actions ul li input[type="radio"]').removeAttr('checked');
					$('input[name^="wc_os_settings[wc_os_products]"]').attr('name', 'wc_os_settings[wc_os_products]['+wc_os_ie+'][]');
					$('.wos_loading').fadeIn();
					//$('.wos_products_list, .wos_categories_qty, .wos_categories_list, .wos_vendors_list, .wos_woo_vendors_list, .wos_products_search').css('opacity', '0.2');
					$(this).parents().eq(1).addClass('active');
					//console.log($(this).parents().eq(1).hasClass('noproducts'));
					//console.log($(this).parents().eq(1).hasClass('nocategories'));
					//console.log($(this).parents().eq(1));
					if($(this).parents().eq(1).hasClass('noproducts') || $(this).parents().eq(1).hasClass('nocategories')){
						//$('.wc_os_ahead + .wc_os_notes, .wc_os_ahead + .wc_os_notes + p, .wos_products_list, .wos_products_list + div, .wos_products_list + div + p.submit, .wos_products_search').hide();
						if($(this).parents().eq(1).hasClass('nocategories')){
                            if($(this).parents().eq(1).hasClass('woo_vendors')){

                                //$('.wos_categories_qty, .wos_categories_list').hide();
                                //$('.wos_woo_vendors_list').show();
                                $('.wos_woo_vendors_list td input[type="checkbox"], .wos_woo_vendors_list td input[type="radio"]').removeAttr('checked');

                            }else{


                                //$('.wos_categories_qty, .wos_categories_list').hide();
                                //$('.wos_vendors_list').show();
                                $('.wos_vendors_list td input[type="checkbox"], .wos_vendors_list td input[type="radio"]').removeAttr('checked');

                            }
							
						}else{			
							switch(wc_os_ie){
								case 'cats':
									//$('.wos_categories_qty').show();
									$('.wos_categories_qty td input[type="checkbox"], .wos_categories_qty td input[type="radio"]').removeAttr('checked');								
								break;
								case 'group_cats':			
									//$('.wos_categories_list').show();
									$('.wos_categories_list td input[type="checkbox"], .wos_categories_list td input[type="radio"]').removeAttr('checked');
								break;
							}
							
						}
					//}else if(!$(this).parents().eq(1).hasClass('group_by_attributes_only') && !$(this).parents().eq(1).hasClass('group_by_attributes_value') && !$(this).parents().eq(1).hasClass('group_by_acf_group_fields')){
					}else if(!$(this).parents().eq(1).hasClass('group_by_attributes_only') && !$(this).parents().eq(1).hasClass('group_by_attributes_value') && !$(this).parents().eq(1).hasClass('group_by_order_item_meta') && !$(this).parents().eq(1).hasClass('group_by_acf_group_fields') && !$(this).parents().eq(1).hasClass('group_by_partial_payment')){
						//$('.wc_os_ahead + .wc_os_notes, .wc_os_ahead + .wc_os_notes + p, .wos_products_list, .wos_products_list + div:not(.wos_attributes_only_list):not(.wos_attributes_values_list):not(.group_by_order_item_meta_list):not(.group_by_gf_meta_list), .wos_products_list + div + p.submit, .wos_products_search').show();
						//$('.wos_categories_qty, .wos_categories_list').hide();
					}else if($(this).parents().eq(1).hasClass('group_by_attributes_only')){
						//$('.wos_attributes_only_list').parent().find('p.submit').show();
						//$('.wos_attributes_only_list, .wos_attributes_only_list > h3').show();
					}else if($(this).parents().eq(1).hasClass('group_by_attributes_value')){
						//$('.wos_attributes_values_list').parent().find('p.submit').show();
						//$('.wos_attributes_values_list, .wos_attributes_values_list > h3').show();
					}else if($(this).parents().eq(1).hasClass('group_by_order_item_meta')){
						//$('.group_by_order_item_meta_list').parent().find('p.submit').show();
						//$('.group_by_order_item_meta_list, .group_by_order_item_meta_list > h3').show();
					}else if($(this).parents().eq(1).hasClass('group_by_gf_meta')){
						//$('.group_by_gf_meta_list').parent().find('p.submit').show();
						//$('.group_by_gf_meta_list, .group_by_gf_meta_list > h3').show();
					}else if($(this).parents().eq(1).hasClass('group_by_acf_group_fields')){
						//$('.wc_os_ahead + .wc_os_notes, .wc_os_ahead + .wc_os_notes + p, .wos_products_list, .wos_products_list + div, .wos_products_list + div + p.submit, .wos_products_search').hide();
						//$('.wc_os_acf_values_list, .wc_os_acf_values_list > h3').show();
						//$('.wc_os_acf_values_list').parent().find('p.submit').show();

					}else if($(this).parents().eq(1).hasClass('group_by_partial_payment')){
						//$('.wc_os_ahead + .wc_os_notes, .wc_os_ahead + .wc_os_notes + p, .wos_products_list, .wos_products_list + div, .wos_products_list + div + p.submit, .wos_products_search').hide();
						//$('form.wc_os_partial_wrapper, .wc_os_partial_payment_list, .wc_os_partial_payment_list > h3').show();
						//$('.wc_os_partial_payment_list').parent().find('p.submit').show();

					}
					
					
					switch(wc_os_ie){
						default:
							//$('.wos_products_list td.split-actions ul').show();
							//$('.wos_products_list td.split-actions select.wos_product_action').show();
							//$('.wos_products_list td.split-actions select:not(.wos_product_action)').hide();
						break;
						case 'groups':
							//$('.wos_products_list td.split-actions select.wos_product_action').hide();
							//$('.wos_products_list td.split-actions select:not(.wos_product_action)').show();							
						break;
						case 'cats':
							//$('.wos_categories_qty td.split-actions ul').hide();						
							//$('.wos_categories_qty td.split-actions select').show();
							//$('.wos_categories_qty > label').html($(this).data('title')).show();
							
							
							$.each($('.wos_categories_qty td.split-actions select[name^="split_action["]'), function(sp, ac){
								var wic_id = $(this).parent().data('id');
								$(this).attr({'name':'split_action['+wc_os_ie+']['+wic_id+']', 'data-ie': wc_os_ie}).val('');
								$('input[name^="wc_os_cats["]#wic-'+wic_id).attr('name', 'wc_os_cats['+wc_os_ie+']['+$(this).val()+'][]');//.prop('checked', false).hide();
							});						
						break;
						case 'group_cats':
							//$('.wos_categories_list td.split-actions ul').hide();						
							//$('.wos_categories_list td.split-actions select').show();
							//$('.wos_categories_list > label').html($(this).data('title')).show();
							
							
							$.each($('.wos_categories_list td.split-actions select[name^="split_action["]'), function(sp, ac){
								var wic_id = $(this).parent().data('id');
								
								$(this).attr({'name':'split_action['+wc_os_ie+']['+wic_id+']', 'data-ie': wc_os_ie}).val('');
								$('input[name^="wc_os_cats["]#wic-'+wic_id).attr('name', 'wc_os_cats['+wc_os_ie+']['+$(this).val()+'][]');//.prop('checked', false).hide();
							});
						break;
						case 'group_by_vendors':
							//$('.wos_vendors_list > label').html($(this).data('title')).show();
							//$('.wos_vendors_list td.split-actions select').show();
						break;		
						case 'group_by_woo_vendors':
                            //$('.wos_woo_vendors_list > label').html($(this).data('title')).show();
                            $('.wos_woo_vendors_list td.split-actions select').show();
                        break;						
						case 'group_by_attributes_only':
						case 'group_by_attributes_value':
						case 'group_by_order_item_meta':
							
						break;
						case 'group_by_acf_group_fields':

							//$('.wos_acf_wrapper').show();
							//$('.wos_acf_wrapper form').show();
						break;
					}
					$.post(ajaxurl, data, function(response) {
						response = $.parseJSON(response);
						//console.log(response);
						if($('ul.wos_pagination:visible').length>0){
							var li_obj = $('ul.wos_pagination:visible a.nums[data-num="'+wos_obj.wc_os_pg+'"]');
							if(li_obj.length>0){
								li_obj.click();
							}else{
								 $('ul.wos_pagination:visible li.wos-first a').click();
							}
						}
						
						$('.wos_loading').fadeOut();
						//$('.wos_products_list, .wos_categories_qty, .wos_categories_list, .wos_vendors_list, .wos_woo_vendors_list, .wos_products_search').css('opacity', '1');
						
						switch(wc_os_ie){
							default:
									
									var selected_items = response[0];
									$.each(selected_items, function(i,v){
										var i_obj = '.wos_products_list td.split-actions ul li #'+wc_os_ie+'-'+v;
										$('#wip-'+v).prop('checked', 'checked').removeClass('hides');
										$(i_obj).prop('checked', true);	
										$(i_obj).parent().removeClass('wos-selected');
									});
									
									selected_items = response[1];
									$.each(selected_items, function(i,v){
										//$('#wip-'+v).attr('checked', 'checked').removeClass('hides');
										if(typeof v=='object'){
											$.each(v, function(j,k){
												var i_obj = '.wos_products_list td.split-actions ul li #'+i+'-'+k;
												$(i_obj).parent().addClass('wos-selected');												
											});
										}else{
											var i_obj = '.wos_products_list td.split-actions ul li #'+i+'-'+v;
											$(i_obj).parent().addClass('wos-selected');
										}
									});
							break;
							case 'group_cats':
								switch(wc_os_ie){
									case 'group_cats':											
										$.each(response, function(i,v){
											if(typeof v!='string'){							
												//$('.wos_categories_list td.split-actions ul li input[type="radio"]').removeAttr('checked');	
												$.each(v, function(ind,val){					
													//console.log(ind);console.log(val);
													$('.wos_categories_list td.split-actions select#group-cat-'+val).val(i);													
													//$('.wos_categories_list td.split-actions ul li #'+ind+'-'+val).prop('checked', true);								
												});
												
												$('.wos_categories_list td.split-actions select').trigger('change');
											}
										});
									break;
								}
							break;
							case 'cats':
							case 'groups':
							
								
								
								if(response.length>0){
									
									$.each(response, function(i,v){
									
										switch(wc_os_ie){								
											case 'groups':
												if(typeof v!='string'){							
													$.each(v, function(ind,val){	
														//console.log(ind);
														//console.log(val);
														//$('.wos_products_list td.split-actions select#group-'+val).val(i);
														//$('.wos_products_list td.split-actions ul li input[type="radio"]').removeAttr('checked');										
													});		
													$('.wos_products_list td.split-actions select').trigger('change');
												}
											break;
											case 'cats':
												if(typeof v!='string'){							
													$('.wos_categories_qty td.split-actions ul li input[type="radio"]').removeAttr('checked');	
													$.each(v, function(ind,val){					
														$('.wos_categories_qty td.split-actions select#group-cat-'+val).val(i);													
														$('.wos_categories_qty td.split-actions ul li #'+ind+'-'+val).prop('checked', true);								
													});
													
													//$('.wos_categories_qty td.split-actions select').trigger('change');
												}										
											break;											
											case 'catss':
												$.each(v, function(ind,val){
													//$('#wic-'+val).attr('name', 'wc_os_cats['+i+'][]').attr('checked', 'checked').removeClass('hides');
													$('.wos_categories_list td.split-actions ul li #'+i+'-'+val).prop('checked', true);
												});
											break;
										}
									});
							
								}else{
									
								}
								
								
							break;
							case 'group_by_vendors':
								$.each(response, function(i,v){
									if(typeof v!='string'){							
										//$('.wos_vendors_list td.split-actions ul li input[type="radio"]').removeAttr('checked');	
										$.each(v, function(ind, val){					
											$('.wos_vendors_list td.split-actions select#group-vendor-'+val).val(i);	
											//var vcb_sel = '.wos_vendors_list td.split-actions ul li #'+ind+'-'+val;												
											//console.log(vcb_sel);
											var vcb_sel = '.wos_vendors_list td #wic-'+val;												
											$(vcb_sel).prop('checked', true);
										});
										
										$('.wos_vendors_list td.split-actions select').trigger('change');
									}
								});
							break;
                            case 'group_by_woo_vendors':
                                $.each(response, function(i,v){
                                    if(typeof v!='string'){
                                        //$('.wos_woo_vendors_list td.split-actions ul li input[type="radio"]').removeAttr('checked');
                                        $.each(v, function(ind, val){
                                            $('.wos_woo_vendors_list td.split-actions select#group-vendor-'+val).val(i);
                                            //var vcb_sel = '.wos_woo_vendors_list td.split-actions ul li #'+ind+'-'+val;
                                            //console.log(vcb_sel);
                                            var vcb_sel = '.wos_woo_vendors_list td #wic-'+val;
                                            $(vcb_sel).prop('checked', true);
                                        });

                                        $('.wos_woo_vendors_list td.split-actions select').trigger('change');
                                    }
                                });
                            break;													
						}
						
					});
					
					
					split_action_toggle($(this));
					
				break;
			}

	
			if($('li.wc_os_ie_products i span.i-'+method_name).length>0){		
				$('li.wc_os_ie_products i span').removeClass('selected').removeClass('active');		
				$('li.wc_os_ie_products i span.i-'+method_name).addClass('active').addClass('selected');	
			}							
			$('.split-settings-dashboard').data({'selected-method': method_name, 'selected-method-type': method_type});
			$('.split-settings-dashboard').attr('class', 'nav-tab-content split-settings-dashboard '+method_type);
			$('div.nav-tab-content.tab-split-settings').attr('class', 'nav-tab-content tab-split-settings '+' wc_os_'+method_name);
			//console.log(method_name+' - '+method_type+' - '+$('.split-settings-dashboard').data('selected-method-type'));
			
		});			
		
		$('.wc_settings_div').on('change', 'select[name="wc_os_settings\[wc_os_qty_split_option\]"]', function(){
			var obj = $(this).parent();
			obj.find('a').removeClass('selected');
			obj.find('a.split_'+$(this).val()).addClass('selected');
		});
			
		//if 'All products' checked, disable checking individual products
		$('.wc_settings_div').on('change', '#wc_os_all_product', function(){
			//$('[name^="wc_os_settings\[wc_os_products\]"]').attr('disabled', this.checked ? 'disabled' : null);
			if($(this).is(':checked')){
				var action_main = $('.wc_os_ahead .active input:checked').val();
				//console.log(action_main);
				if(action_main){
					$('td.split-actions ul li input[type="radio"][id^="'+action_main+'"]').click();
				}
				
			}else{
				$('.wos_products_list input[name^="wc_os_settings[wc_os_products]"]:checked').click();
			}
		});
		
		$('.wc_settings_div').on('change', '#wc_os_all_vendors', function(){
			//$('[name^="wc_os_settings\[wc_os_products\]"]').attr('disabled', this.checked ? 'disabled' : null);
			if($(this).is(':checked')){
				$('.wos_woo_vendors_list input[name^="wc_os_woo_vendors["]').prop('checked', true);				
			}else{
				$('.wos_woo_vendors_list input[name^="wc_os_woo_vendors["]').prop('checked', false);
			}
		});
		
		//MOVED TO LINE 225
		//if a certain actions is selected, enable 'All products' checkbox, else disable
		//$('[name="wc_os_settings\[wc_os_ie\]"]').on('change', function(){
		//});
		
		//initialize split action
		//$('[name="wc_os_settings\[wc_os_ie\]"]:checked').trigger('change');
		function wos_trigger_selected_ie(){
			if(wos_obj.wc_os_tab==1){
				setTimeout(function(){
					$('input[name="wc_os_settings[wc_os_ie]"][value="'+wos_obj.wc_os_ie+'"]').trigger('click');
					switch(wos_obj.wc_os_ie){
						default:
						case 'cats':
							//$('.wc_os_ie_products').find('ul, li').show();
						break;
						
					}
					$('select[name="wc_os_settings\[wc_os_qty_split_option\]"]').change();
				}, 1000);
			}
		}
		
		
		wos_trigger_selected_ie();
		
		
		
		setTimeout(function(){
			if($('.split_lock_bars input[type="checkbox"]#wip-split').length>0){
				if(!$('.split_lock_bars input[type="checkbox"]#wip-split').is(':checked')){
					$('.inner_split_lock').show();
				}
			}
			if($('.order_removal_bars input[type="checkbox"]:not(#wo_os_rule_switch)').length>0){
				if(!$('.order_removal_bars input[type="checkbox"]:not(#wo_os_rule_switch)').is(':checked')){
					$('.inner_removal_lock').show();
				}
			}			
			if($(wc_os_extend_groups).length){
				
				if($(wc_os_extend_groups).is(':checked')){
					$('.wc_os_extend_groups').show();				
				}else{
					$('.wc_os_extend_groups').hide();				
				}				
			}
			
			wc_os_hide_product_group_status();
			
		}, 1000);
		
		$('.wc_settings_div').on('click', '.split_lock_bars input[type="checkbox"]#wip-split', function(){
			if($(this).is(':checked')){
				$('.inner_split_lock').hide();
			}else{
				$('.inner_split_lock').show();
			}
		});
		
		$('.wc_settings_div').on('click', '.order_removal_bars input[type="checkbox"]:not(#wo_os_rule_switch)', function(){
			if($(this).is(':checked')){
				$('.inner_removal_lock').hide();
			}else{
				$('.inner_removal_lock').show();
			}
		});
		
		if(wos_obj.wc_os_splitting){
			//$('body').css('opacity', '0.5');
			$('form#posts-filter').html('<div class="wos_loading"></div>');
			$('.wos_loading').show();
			setTimeout(function(){
				document.location.href = wos_obj.orders_list;
			}, 1000);
		}
		
		//functions
		function split_action_toggle(obj){
			//console.log(selected_value);
			obj.parents().eq(1).addClass('active');
			
			switch(obj.val())
			{
				case 'default':
				case 'exclusive':
				case 'io':
					if(wos_obj.wc_os_all_product){}else{
						$('#wc_os_all_product').removeAttr('checked').attr('disabled', null);
						$('#wc_os_all_product').trigger('change');
					}
					
				break;
				default:
			  		if(wos_obj.wc_os_all_product){}else{
						$('#wc_os_all_product').removeAttr('checked').attr('disabled', 'disabled');
						$('[name^="wc_os_settings\[wc_os_products\]"]').attr('disabled', null);
					}
					
				break;
			}
		}
		
		$('#woocommerce-order-items > div.inside').eq(0).find('.wc-order-items-editable').eq(0).prepend('<div class="conflict_status">'+wos_obj.conflict_status+'</div>');
		$('#woocommerce-order-items > div.inside').eq(0).find('.wc-order-items-editable').eq(0).prepend('<div class="conflict_status">'+wos_obj.conflict_status+'</div>');
		
		
		
		$('.wc_settings_div').on('click', '.wos_products_list input[name^="wc_os_settings[wc_os_products]"]', function(){
			if($(this).is(':checked')){
			}else{
				$(this).parents().eq(1).find('.split-actions input[type="radio"]').removeAttr('checked');
				$(this).addClass('hides');
			}
		});
		
		$('select#bulk-action-selector-top').on('change', function(){
			$('.wc-os-yt-orders-list, .wuos-wos, .bulk-wos').hide();

			switch($(this).val()){
				default:
					$('#the-list .wc_actions.column-wc_actions p .wc_os_parent, input[name="wc_os_parent"][type="hidden"]').remove();
				break;
				case 'combine':
				case 'wuoc_combine':
					$('#the-list input[name="post[]"][type="checkbox"]:checked').addClass('wos_parent_mark').prop('checked', false);
										
					var current_obj = $('#the-list input[name="post[]"][type="checkbox"].wos_parent_mark');
					
					$.each(current_obj, function () {
					var obj = $(this).parents().eq(1).find('.wc_actions.column-wc_actions p');
						obj.find('.wc_os_parent').remove();
					});
					
					current_obj.removeClass('wos_parent_mark').click();					
					
					$('.wc-os-yt-orders-list, .wuos-wos').css('display', 'inline-block');
					//$('#the-list input[name="post[]"][type="checkbox"].wos_parent_mark').removeClass('wos_parent_mark').click();
				break;
				case 'split':
					$('.bulk-wos').css('display', 'inline-block');
				break;
			}
		});
		$('#the-list input[name="post[]"][type="checkbox"]').on('click', function(){
			switch($('select#bulk-action-selector-top').val()){
				case 'combine':
				case 'wuoc_combine':
					var obj = $(this).parents().eq(1).find('.wc_actions.column-wc_actions p');
					if($(this).is(':checked')){
						if(obj.find('a.wos_parent').length==0){
							obj.append('<a title="'+wos_obj.mark_parent+'" class="button wc-action-button wc-action-button-wc_os_parent wc_os_parent"></a>');
						}					
					}else{
						obj.find('.wc_os_parent').remove();
					}
				break;
			}
		});
		
		$('#the-list .wc_actions.column-wc_actions').on('click', 'p .wc_os_parent', function(event){
			event.preventDefault();
			$('p .wc_os_parent.selected').not(this).removeClass('selected');
			$(this).toggleClass('selected');
			
			var cvalue = $(this).parents().eq(2).find('input[name="post[]"][type="checkbox"]:checked').val();
			if($('input[name="wc_os_parent"][type="hidden"]').length==0){
				$('form#posts-filter').prepend('<input type="hidden" name="wc_os_parent" value="'+cvalue+'">');
			}else{
				$('input[name="wc_os_parent"][type="hidden"]').val(cvalue);
			}
		});
		
		function wc_os_refresh_group_selection(){
			
			var wc_os_group_meta = wos_obj.wc_os_group_meta;
			var group_cats = wc_os_group_meta.wc_os_cats.group_cats;
			
			var wrapper = $('.wc_os_group_cat_meta_wrapper');
			
			var selected_group_val = $.trim(wrapper.find('.wc_os_selected_group').val());
			
			var select_meta = wrapper.find('.wc_os_selected_meta');
			var selected_option = wrapper.find('.wc_os_selected_meta option:selected');

			selected_option.removeAttr("selected");

			var selected_meta_value = group_cats[selected_group_val];
			
			
			
//			console.log(selected_group_val);
//			console.log(group_cats[selected_group_val]);
			
			select_meta.val(selected_meta_value);

			
		}
	
		$(wc_os_refresh_group_selection);
	
		
		$('.wc_os_group_cat_meta_wrapper').on('click', '.wc_os_group_cat_meta_save', function(e){
			e.preventDefault();
			
			var wrapper = $('.wc_os_group_cat_meta_wrapper');
			
			var selected_group_val = wrapper.find('.wc_os_selected_group').val();
			var selected_meta_val = wrapper.find('.wc_os_selected_meta').val();


			
			var data = {
				
				action: 'wc_os_group_cat_meta_post',
				wc_os_selected_group: selected_group_val,
				wc_os_selected_meta: selected_meta_val,
				
				
				
			}
			
//			console.log(selected_group_val);
//			console.log(selected_meta_val);
			
			
			$.post(ajaxurl, data, function(resp){
				
				resp = JSON.parse(resp);
//				console.log(resp);
				wos_obj.wc_os_group_meta = resp;
				// console.log(resp);
				$(wc_os_refresh_group_selection);
				$('.wc_os_group_cat_meta_msg').fadeIn().delay(3000).fadeOut();

			});
			
			
			
		});
	
		$('.wc_os_group_cat_meta_wrapper').on('change', '.wc_os_selected_group', function(){		
			
			
			$(wc_os_refresh_group_selection);

			
		});
	
		$('.wc_os_group_cat_meta_wrapper').on('change', '.wc_os_selected_meta', function(){		
				
				
			var meta_selection = $(this).val();
			var none_index = meta_selection.indexOf('none');
			if(none_index != -1){
				$(this).val([]);
				$(this).val(['none']);
			}
				
		});
	
		$('.wc_os_group_cat_meta_switch').on('click', function(){
			$(this).hide();
			$('.wc_os_group_cat_meta_wrapper').show();
		});
		
		$('a.wc-action-button-wc_os_split.wc_os_split').on('click', function(event){
			event.preventDefault();
			var data = {
				'action': 'wos_quick_split',
				'url': $(this).attr('href')
			};
			
			$(this).parent().append('<div class="wos_loading"></div>');
			$(this).parent().find('.wos_loading').show();
			$('a.wc-action-button-wc_os_split.wc_os_split').fadeOut();
			
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(response) {
				
				//response = parseInt($.trim(response));
				response = $.parseJSON(response);
				var order_id = response.order_id;
				//console.log(order_id);console.log(response.refresh);console.log(wos_obj.orders_list_refresh);
				if(order_id>0 && response.refresh==true){
					
					if(wos_obj.orders_list_refresh){
						if(order_id){
							document.location.href = wos_obj.orders_list+'&parent-order='+order_id+'&orderby=ID&order=desc';
						}else{					
							document.location.reload();
						}
					}else{
						if(response.child_orders.length>0){
							document.location.href = wos_obj.orders_list+'&parent-order='+order_id+'&orderby=ID&order=desc';
						}else{
							document.location.reload();
						}
					}
				}
				
			});
		});

		
	
		$('body').on('change', '#wc_os_vendor_role_selection, #wc_os_all_user_with_role', function(){
	
			var selected_val = $('#wc_os_vendor_role_selection').val();
			var checked_val = $('#wc_os_all_user_with_role').prop('checked');
	
	
			if(selected_val.length == 0 ){
	
				selected_val = false;
			}
	
			var data = {
	
				action: 'wc_os_update_vendor_role_selection',
				'wc_os_vendor_role_selection' : selected_val,
				'wc_os_all_user_with_role' : checked_val,
				'wc_os_vendors_field':wos_obj.wc_os_vendor_nonce,
			}
	
			// console.log(data);
			$.post(ajaxurl, data, function (response, code) {
	
				// console.log(response);
				if(code == 'success'){
	
					window.location.reload(true);
				//	
				}
	
			});
	
		});
	
		$('div.wc_os_emails_section .wc_os_tab button.wc_os_tab_links').on('click', function (e) {
			
			e.preventDefault();
			
			var obj_content = 'div.wc_os_emails_section #'+$(this).data('target')+'.wc_os_tab_content';
	
			$('div.wc_os_emails_section .wc_os_tab_content').hide();
			$('div.wc_os_emails_section .wc_os_tab button.wc_os_tab_links').removeClass('wc_os_active');
			$(this).addClass('wc_os_active');
			
			$(obj_content).show();
			$(obj_content).find('form').show();
			$('div.wc_os_emails_section input:submit[data-id="'+$(this).data('target')+'"]').show();
			
			var current_url = new URL(window.location.href);
			var active_tab = current_url.searchParams.get('t');
			var state_url = wos_obj.this_url+'&t='+active_tab;
			state_url += '&wos_email_tab='+$(this).data('target');
			window.history.replaceState('', '', state_url);
			$('.nav-tab-content.wc_os_emails_section form').prop('action', state_url);			
	
		});
		
		if(wos_obj.wc_os_tab==2){
			setTimeout(function(){
				$('div.wc_os_emails_section .wc_os_tab button.wc_os_tab_links.wc_os_active').click();
			}, 1000);
		}
	
		$('select[name="wc_os_meta_key"]').on('change', function(){
			document.location.href = $('form.wc_os_rules input[name="_wp_http_referer"]').val()+'&wc_os_mk='+$(this).val();
		});
		$('.wos_refresh_status').on('click', function(){
			$('.status_form').hide();
			$('.status_list, .wos_add_new_button').show();
		});
		
		var wos_num_prev = 0;
		var wos_num_next = 0;
	
		$('.wc_settings_div').on('click', 'ul.wos_pagination:visible li.wos-first a, ul.wos_pagination:visible li.wos-last a', function(){
			var ul_obj = $(this).parents().eq(1);
			var num; 
	
			if($(this).parent().hasClass('wos-first')){
				num = ul_obj.find('.wos-first a').data('num');			
			}else{
				num = ul_obj.find('.wos-last a').data('num');			
			}
			
			ul_obj.find('a.nums[data-num="'+num+'"]').click();
		});
			
		$('.wc_settings_div').on('click', 'ul.wos_pagination:visible li.wos-prev a, ul.wos_pagination:visible li.wos-next a', function(){
			var ul_obj = $(this).parents().eq(1);
			var num = (ul_obj.find('.wos_current a').length>0?ul_obj.find('.wos_current a').data('num'):ul_obj.find('.wos-first a').data('num'));
			wos_num_prev = num-1;
			wos_num_next = num+1;	
			
			wos_num_prev = (wos_num_prev>0?wos_num_prev:ul_obj.find('.wos-last a').data('num'));
			wos_num_next = (wos_num_next>ul_obj.find('.wos-last a').data('num')?ul_obj.find('.wos-first a').data('num'):wos_num_next);
			//console.log('wos_num_prev: '+wos_num_prev+' wos_num_next: '+wos_num_next);
			
			
			
			if($(this).parent().hasClass('wos-next')){
				ul_obj.find('a.nums[data-num="'+wos_num_next+'"]').click();
			}else{
				ul_obj.find('a.nums[data-num="'+wos_num_prev+'"]').click();
			}
		});
		$('.wc_settings_div').on('click', 'ul.wos_pagination:visible li a.nums', function(){
			
			
			var num = $(this).data('num');		
			wos_num_prev = num-1;
			wos_num_next = num+1;
			var ul_obj = $(this).parents().eq(1);
			
			//console.log(num);
			//console.log(num_next);
			
			ul_obj.find('.wos_current').removeClass('wos_current');
			ul_obj.find('a[data-num="'+num+'"]').parent().not('.wos-prev, .wos-next').addClass('wos_current');
			ul_obj.find('li.wos-prev a').attr('data-num', wos_num_prev);
			ul_obj.find('li.wos-next a').attr('data-num', wos_num_next);
			
	
			if(num>1){
				ul_obj.find('li.wos-prev').show();
			}else{
				ul_obj.find('li.wos-prev').hide();
			}
			
			if(num<ul_obj.find('.wos-last a').data('num')){
				ul_obj.find('li.wos-next').show();
			}else{
				ul_obj.find('li.wos-next').hide();
			}
			
			var pagination_parent = ul_obj;
			var active_li = pagination_parent.find('li.wos_current.wos_single');
			var control_parent = pagination_parent.find('li.wos_controls');
			var up_control = control_parent.find('.wos_up');
			var down_control = control_parent.find('.wos_down');
			var toggle_control = control_parent.find('.wos_toggle');
			var total_group = control_parent.data('total_group');
			var active_num_group = active_li.data('group');
			var all_groups = pagination_parent.find('li.wos_single');

			if(toggle_control.hasClass('wos_toggle_active')){

			}else{

				up_control.removeClass('wos_disabled');
				down_control.removeClass('wos_disabled');
				all_groups.removeClass('wos_active_group').addClass('wos_inactive_group');
				var active_num_group_obj = pagination_parent.find('li.wos_single.group_'+active_num_group);
				active_num_group_obj.addClass('wos_active_group').removeClass('wos_inactive_group');
	
				if(active_num_group == total_group){
					up_control.addClass('wos_disabled');
				}else{
					up_control.removeClass('wos_disabled');
				}
		
				if(active_num_group == 1){
					down_control.addClass('wos_disabled');
				}else{
					down_control.removeClass('wos_disabled');
				}

			}					
			
			var wos_list_type = '';
			wos_list_type = ($('table.wos_products_list:visible').length>0?'products_list':wos_list_type);
			wos_list_type = ($('.wos_attributes_only_list.attribs:visible').length>0?'attributes_list':wos_list_type);
			wos_list_type = ($('.wos_attributes_values_list.attribs:visible').length>0?'attributes_values':wos_list_type);
			wos_list_type = ($('form.wos_categories_qty:visible').length>0?'categories_qty':wos_list_type);
			wos_list_type = ($('form.wos_categories_list:visible').length>0?'categories_list':wos_list_type);
			wos_list_type = ($('div.group_by_order_item_meta_list.attribs:visible').length>0?'order_item_meta':wos_list_type);
			wos_list_type = ($('div.group_by_gf_meta_list.attribs:visible').length>0?'group_by_gf_meta_list':wos_list_type);
			
			
			
			
			
	
			var data = {
				'action': 'wos_load_paginated',
				'num': num,
				'wc_os_settings_field': $('input[name="wc_os_settings_field"]').val(),
				'wos_list_type': wos_list_type
			};
			
			switch(wos_list_type){
				case 'products_list':
					$('table.wos_products_list tbody').css('opacity', 0.3);
				break;
				case 'attributes_list':
					//.wos_attributes_only_list, .wos_attributes_values_list
					$('.wos_attributes_only_list.attribs').css('opacity', 0.3);
				break;
				case 'attributes_values':
					//.wos_attributes_only_list, .wos_attributes_values_list
					$('.wos_attributes_values_list.attribs').css('opacity', 0.3);
				break;		
				
				case 'order_item_meta':
					//.wos_attributes_only_list, .wos_attributes_values_list
					$('.group_by_order_item_meta_list.attribs').css('opacity', 0.3);
				break;		
				
				case 'group_by_gf_meta':
					//.wos_attributes_only_list, .wos_attributes_values_list
					$('.group_by_gf_meta_list.attribs').css('opacity', 0.3);
				break;						
								
				case 'categories_qty':
					$('form.wos_categories_qty:visible table').css('opacity', 0.3);
				break;								
				case 'categories_list':
					$('form.wos_categories_list:visible table').css('opacity', 0.3);
				break;								
			}
			
			if($('table.wos_products_list:visible').length>0){
				
			}
			if($('.wos_attributes_only_list.attribs:visible').length>0){
				$('.wos_attributes_only_list.attribs').css('opacity', 0.3);
			}
			if($('.wos_attributes_values_list.attribs:visible').length>0){
				$('.wos_attributes_values_list.attribs').css('opacity', 0.3);
			}			
			if($('.group_by_order_item_meta_list.attribs:visible').length>0){
				$('.group_by_order_item_meta_list.attribs').css('opacity', 0.3);
			}						
			
			
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			
			var updated_url = wos_obj.this_url+'&t='+$('.nav-tab-wrapper .nav-tab.nav-tab-active').index()+'&pg='+num;
			window.history.replaceState('', '', updated_url);
			
			
			$('form.nav-tab-content:visible input[name="wos_pg"]').val(num);	
			$('form.nav-tab-content:visible').attr('action', updated_url);
			
			$.post(ajaxurl, data, function(response) {
				
				//console.log(wos_list_type);
					
				switch(wos_list_type){
					case 'products_list':
							$('table.wos_products_list tbody').html(response).css('opacity', 1);
							
							switch($('.wc_os_ie_wrapper li.active input[name="wc_os_settings[wc_os_ie]"]').val()){
								
								case 'groups':								
									$('.wos_products_list td.split-actions select:not(.wos_product_action)').trigger('change');
									$('.wos_products_list td.split-actions select.wos_product_action').hide();
									$('.wos_products_list td.split-actions ul').hide();
									$('.wos_products_list td.split-actions select:not(.wos_product_action)').show();	
									$('.wos_products_list td.wc_os_group_status').show();							
									
								break;
								
								default:							
									$('.wos_products_list td.split-actions select:not(.wos_product_action)').hide();
									$('table.wos_products_list tbody td.split-actions select.wos_product_action').show();
									$('table.wos_products_list tbody td.split-actions ul').show();
								break;
								
							}
					break;
					case 'attributes_list':
						$('.wos_attributes_only_list.attribs table').remove();
						$('.wos_attributes_only_list.attribs').append(response);
						$('.wos_attributes_only_list.attribs').css('opacity', 1);
					break;
					case 'attributes_values':
						$('.wos_attributes_values_list.attribs table').remove();
						$('.wos_attributes_values_list.attribs').append(response);
						$('.wos_attributes_values_list.attribs').css('opacity', 1);
					break;			
					case 'order_item_meta':
						$('.group_by_order_item_meta_list.attribs table').remove();
						$('.group_by_order_item_meta_list.attribs').append(response);
						$('.group_by_order_item_meta_list.attribs').css('opacity', 1);
					break;		
					case 'group_by_gf_meta':
						$('.group_by_gf_meta_list.attribs table').remove();
						$('.group_by_gf_meta_list.attribs').append(response);
						$('.group_by_gf_meta_list.attribs').css('opacity', 1);
					break;							
						
					case 'categories_qty':
						$('form.wos_categories_qty:visible table').css('opacity', 1);
						$('form.wos_categories_qty:visible table.cheese-pocket tbody').html(response);
						$('form.wos_categories_qty:visible table.cheese-pocket tbody input[name="wc_os_cats[cats][]"]').removeClass('hides');
						$('form.wos_categories_qty:visible table.cheese-pocket tbody select').change().show();
					break;		
					case 'categories_list':
						$('form.wos_categories_list:visible table').css('opacity', 1);
						$('form.wos_categories_list:visible table tbody').html(response);
						$('form.wos_categories_list:visible table tbody input[name="wc_os_cats[]"]').removeClass('hides');
						$('form.wos_categories_list:visible table tbody select').change().show();
						

					break;								
				}
								

			});
						
		});
		
		$('.wc_settings_div').on('change', 'select[name="wos_pg_limit"]', function(){
			var data = {
				'action': 'wos_items_paginated',
				'wos_pg_limit': $(this).val(),
				'wc_os_settings_field': $('input[name="wc_os_settings_field"]').val(),
			};
			
		
			
			
			$.post(ajaxurl, data, function(response) {
				document.location.reload();
			});			
		});
		
		
		
		$('body').on('click', '.cbqse-wrapper ul li a', function(){
			//console.log($(this).data('id'));
			var img_id = $(this).data('id');
			img_id -= 1;
			$('.cbqse-wrapper > img').hide();
			$('.cbqse-wrapper > img').eq(img_id).css('display', 'block');
		});
		$('body').on('dblclick', '.cbqse-wrapper ul li a', function(){
			$('.cbqse-wrapper > img').hide();
		});
		
		var load_modal = $('#wc_os_load_modal');
		var success_alert = $('.wc_os_shipping .wc_os_alert.alert-success');
		
		$('.wc_os_shipping .nav-tab[data-selection="'+wos_obj.shipping_selection+'"]').click();
		$('.wc_os_shipping .wc_os_shipping_selection[value="'+wos_obj.shipping_selection+'"]').prop('checked', true);
		
		
		function wc_os_get_order_total_criteria(){
		
		var wrapper = $('.wc_os_order_total_criteria_wrapper');
		var actual_rows = wrapper.find('.wc_os_order_total_criteria_row');
		
		var criteria_array = [];
		var negative_value = false;
		
		if(actual_rows.length > 0){
			$.each(actual_rows, function(){
			
				var this_row = $(this);
				var single_criteria = {};
				
				single_criteria.min = this_row.find('input[name="min"]').val();
				single_criteria.max = this_row.find('input[name="max"]').val();
				single_criteria.cost = this_row.find('input[name="cost"]').val();
				
				
				if(single_criteria.min < 0 || single_criteria.max < 0 || single_criteria.cost < 0){
					negative_value = true;
				}
				
				criteria_array.push(single_criteria);
			
			});
		}
		
		if(negative_value){
		
			return false;
		
		}else{
		
			return criteria_array;
		
		}
		
		
		}
		
		
		$('body').on('click','.wc_os_shipping .save_changes_btn', function(){
		
			var shipping_selection = $('.wc_os_shipping .wc_os_shipping_selection:checked');
			var shipping_settings_obj = {};
			var parcel_shipment = $('input[name="wc_os_parcel_shipment"]').val();
			shipping_settings_obj.wc_os_shipping_selection = shipping_selection.val();
			var wc_os_shipping_platforms = [];
			$.each($('input[name^="wc_os_shipping_platforms"]:checked').serialize().split('&'), function(i,v){ 
				v = v.split('=');
				if(v.length==2){
					wc_os_shipping_platforms.push(v[1]);
				}
			});
		
		
		
		
		
		var data = {
		
			action : 'wc_os_save_shipping_settings',
			wc_os_nonce : wos_obj.shipping_nonce,
			wc_os_shipping_settings : shipping_settings_obj,
			wc_os_parcel_shipment : parcel_shipment,
			wc_os_shipping_platform_arr : wc_os_shipping_platforms,
		
		}
		
		var total_criteria = wc_os_get_order_total_criteria();
		
		if(total_criteria === false && shipping_selection.val() == 'order_total'){
		
			alert(wos_obj.negative_number_msg);
		
			return;
		
		}else{
		
			total_criteria = total_criteria !== false ? total_criteria : [];
			shipping_settings_obj.wc_os_order_total_criteria = total_criteria;
			
		}
		
		load_modal.show();
		shipping_settings_obj.wc_os_order_total_default = $('#wc_os_order_total_default').val();
		
		// console.log(shipping_settings_obj);
		
		
		
		$.post(ajaxurl, data, function(response, code){
			
			load_modal.hide();
			
			
			if(code == 'success' && response.status){
			
				success_alert.show();
				wos_obj.shipping_selection = shipping_selection.val();
				
				setTimeout(function(){
				
					success_alert.fadeOut();
				
				}, 5000);
			
			}
		
		
		});
		
		
		
		});
		
		
		$('body').on('click', '.wc_os_add_total_order_criteria button', function(){
		
		
			var wrapper = $('.wc_os_order_total_criteria_wrapper');
			
			
			
			var sample_row = $('.wc_os_order_total_criteria_row_sample');
			var clone = sample_row.clone().removeClass('wc_os_order_total_criteria_row_sample').removeClass('hides').addClass('wc_os_order_total_criteria_row');
			clone.show();
			wrapper.append(clone);
			
			var actual_rows = wrapper.find('.wc_os_order_total_criteria_row');
			
			if(actual_rows.length > 0){
				wrapper.show();
			}
		
		
		
		});
		
		$('body').on('click', '.wc_os_del_total_order_criteria', function(){
		
		var parent = $(this).parents('.wc_os_order_total_criteria_row:first');
		parent.remove();
		
		var wrapper = $('.wc_os_order_total_criteria_wrapper');
		var actual_rows = wrapper.find('.wc_os_order_total_criteria_row');
		
		if(actual_rows.length <= 0){
			wrapper.hide();
		}
		
		
		});
		
		function wc_os_render_order_total_criteria(){
		
			var order_total_default = wos_obj.shipping_settings.wc_os_order_total_default;
			var order_total_criteria = wos_obj.shipping_settings.wc_os_order_total_criteria;
			
			order_total_default = order_total_default != undefined ? order_total_default : 0;
			order_total_criteria = $.isArray(order_total_criteria) ? order_total_criteria : [];
			
			$('#wc_os_order_total_default').val(order_total_default);
			
			
			
			
			$.each(order_total_criteria, function(key, value){
			
			
				var wrapper = $('.wc_os_order_total_criteria_wrapper');
				var sample_row = $('.wc_os_order_total_criteria_row_sample');
				var clone = sample_row.clone().removeClass('wc_os_order_total_criteria_row_sample').addClass('wc_os_order_total_criteria_row');
				
				clone.find('input[name="min"]').val(value.min);
				clone.find('input[name="max"]').val(value.max);
				clone.find('input[name="cost"]').val(value.cost);
				
				clone.show();
				wrapper.append(clone);
				
				var actual_rows = wrapper.find('.wc_os_order_total_criteria_row');
				
				if(actual_rows.length > 0){
					wrapper.show();
				}
			
			
			});
		}
		
		wc_os_render_order_total_criteria();
		var order_status_section = $('.wos_order_statuses_section');
		var add_new_button_common = order_status_section.find('.wos_add_new_button');
		var add_new_button = order_status_section.find('.wos_add_new_button.new');
		var show_list_button = order_status_section.find('.wos_add_new_button.list');
		var status_table = order_status_section.find('.status_list');
		var status_form = order_status_section.find('.status_form');
		var add_new_save = order_status_section.find('.wos_save_status');
		var add_new_edit = order_status_section.find('.wos_edit_status');
		var order_status_paid = status_form.find('select.paid');
		var order_status_name = status_form.find('input.status_name');
		var order_status_slug = status_form.find('input.status_slug');
		var is_edit = false;
		var load_modal_status = $('#wc_os_load_modal_status');


	add_new_button_common.on('click', function(){



			is_edit = false;

			status_table.toggle();
			status_form.toggle();
			show_list_button.toggle();
			add_new_button.toggle();

			add_new_save.show();
			add_new_edit.hide();

			order_status_paid.val('yes');
			order_status_name.val('');
			order_status_slug.val('')

			order_status_paid.change();

		});

		$('body').on('mouseover','.wos_order_statuses_section .status_list tr', function(){

			$(this).find('td:first p.wos_status_action').show();

		});

		$('body').on('mouseleave','.wos_order_statuses_section .status_list tr', function(){

				$(this).find('td:first p.wos_status_action').hide();

		});

		$('body').on('click','.wos_status_action .edit', function(e){

			e.preventDefault();

			is_edit = true;

			status_table.toggle();
			status_form.toggle();
			show_list_button.toggle();
			add_new_button.toggle();
			add_new_save.hide();
			add_new_edit.show();

			var status = $(this).parents('td:first').data('status');
			var status_json = atob(status);
			status_json = JSON.parse(status_json);


			$.each(order_status_paid.find('option'), function(){

				$(this).prop('selected', false);

				if($(this).val() == status_json.paid){
					$(this).prop('selected', true);
				}

			});
			order_status_name.val(status_json.status_name);
			order_status_slug.val(status_json.status_slug)

			order_status_paid.change();
			$("html, body").animate({ scrollTop: 0 }, "slow");

		});

		function delete_order_status(slug_name, new_status){

			load_modal_status.show();
			var data = {

				action : 'wos_delete_order_status',
				nonce : wos_obj.order_status_nonce,
				slug : slug_name,
				new_status : new_status,
			}


			if(data.slug){

				$.post(ajaxurl, data, function(response, code){

					load_modal_status.hide();

					if(code == 'success' && response.status){

						var del_alert = order_status_section.find('.wc_os_alert.delete_status');
						del_alert.html(response.alert_string);
						del_alert.show();

						setTimeout(function(){
							del_alert.hide();
						}, 5000)

						status_table.find('tr td[data-slug="'+slug_name+'"]').parents('tr:first').remove();

					}

				});

			}

		}

		var del_slug = '';

		$('body').on('click', '#wc_os_delete_stuts_modal .wos_delete_status', function(e){

			e.preventDefault();

			var del_modal = $('#wc_os_delete_stuts_modal');


			var new_status = del_modal.find('select').val();

			if(del_slug && new_status){

				$('#wc_os_delete_stuts_modal').hide();

				delete_order_status(del_slug, new_status);

			}



		});


		$('body').on('click','.wos_status_action .delete', function(e){

			e.preventDefault();

			var del_modal = $('#wc_os_delete_stuts_modal');

			del_modal.show();
			var slug = $(this).parents('td:first').data('slug');
			del_slug = slug;

			del_modal.find('select option[value="wc-'+slug+'"]').prop('disabled', true);


			// delete_order_status(slug);


		});

		String.prototype.replaceAll = function(search, replacement) {
			var target = this;
			return target.split(search).join(replacement);
		};

		function wos_make_status_slug(this_val){
		
			this_val = $.trim(this_val);
			this_val = this_val.replaceAll(' ', '-');
			this_val = this_val.replaceAll('_', '-');
			this_val = this_val.replaceAll('--', '-');
			this_val = this_val.substring(0, 17);
			this_val = 'wc-'+this_val.toLowerCase();

			return this_val;
		}

		$('body').on('keyup', 'div.status_form input.status_name', function(){

			var this_val = $(this).val();


			if(is_edit){
				$('div.wc_os_alert.duplicate').html(wos_obj.wos_edit_custom_order_status).show();
				return;
			}else{
				$('div.wc_os_alert.duplicate').hide();
			}

			setTimeout(function(){

				this_val = wos_make_status_slug(this_val);
				order_status_slug.val(this_val);

			});

		});

		function wos_get_order_status_obj(){
			
			var status_slug = order_status_slug.val().replace('wc-', '');

			var obj = {

				name : order_status_name.val(),
				slug : status_slug,
				paid : order_status_paid.val(),

			}

			return obj;
		}

		$('.wos_save_status, .wos_edit_status ').on('click', function(){

			var save_alert = order_status_section.find('.wc_os_alert.delete_status');

			save_alert.hide();




			var data = {

				action : 'wos_save_order_status',
				nonce : wos_obj.order_status_nonce,
				wos_order_status_obj : wos_get_order_status_obj(),
			}

			if($(this).hasClass('wos_edit_status')){
				data.is_edit = true;
			}


			if(data.wos_order_status_obj.slug){

				load_modal_status.show();

				$.post(ajaxurl, data, function(response, code){

					load_modal_status.hide();


					if(code == 'success' && response.status){


						if(response.status == true){

							save_alert.html(response.alert_string);
							save_alert.show();

							setTimeout(function(){
								save_alert.hide();
							}, 5000)

							$('.wos_order_statuses_section #the-list').replaceWith(response.update_tbody);
							show_list_button.click();

						}else if(response.status == 'duplicate'){

							var info_alert = order_status_section.find('.wc_os_alert.duplicate');
							info_alert.show();

							setTimeout(function(){

								info_alert.hide();

							}, 5000)


						}


					}else{

						alert(wos_obj.could_not_str);

					}
				});

			}else{

				alert(wos_obj.enter_value);

			}



		});


		$('#wc_os_delete_stuts_modal .wos_close_del_modal').on('click', function(){

			$('#wc_os_delete_stuts_modal').hide();

		});
		


		$('.wc_os_clear_order_log').on('click', function (e) {

                e.preventDefault();
				$.blockUI({message:''});
                $('.wc_os_logger ul.order_log').html('');

                var data = {

                    action: 'wc_os_clear_order_log',
                    wc_os_clear_order_log: 'true',
                    wc_os_clear_email_log_field: wos_obj.wc_os_clear_log_nonce,
                }

                // console.log(data);
                $.post(ajaxurl, data, function (response, code) {

                    // console.log(response);
                    if (code == 'success') {

						$.unblockUI();
                        //
                    }

                });

        });		
		
		$('body').on('click', '.vendor-right .vendor-rbottom > div:not(.out-stock-amount)', function(){
			var text = $(this).data('text');
			$('input[name="vendors_remaining"][value="'+text+'"]').prop('checked', true);
			$(this).parent().find('div').toggle();
		});

		$('body').on('click', '.switch-right .switch-rbottom > div.io-remaining-items', function(){
			var text = $(this).data('text');
			$('input[name="wc_os_settings[io_options][io_items_remaining]"][value="'+text+'"]').prop('checked', true);
			$(this).parent().find('div').toggle();
		});

		$('body').on('click', '.switch-right .switch-rbottom > div.out-stock-amount', function(){
			var text = $(this).data('text');
			$('input[name="wc_os_settings[io_options][out_stock_amount]"][value="'+text+'"]').prop('checked', true);
			$(this).parent().find('div.out-stock-amount').toggle();
		});		


		$('body').on('change', 'input[name="wc_os_settings[wc_os_ie]"]', function(){
			
			var this_val = $(this).val();
			var wc_os_io_options = $('div.wc_os_io_options');
			var wc_os_current_option = $('div.wc_os_io_options[data-method="'+this_val+'"]');

			//Hide all option
			wc_os_io_options.hide();

			//show only current selected options if available
			if(wc_os_current_option.length > 0){
				wc_os_current_option.show();
			}


		});

		function wc_os_hide(obj, time = 3000){

			setTimeout(function(){
				obj.fadeOut();
			}, time);
		}

		$('.wc_settings_div').on('change', '#screen-options-wrap .group_input[name="method_selection"]', function(){

			var acf_group_options = $('.wc_settings_div #screen-options-wrap .wc_os_acf_group_selection');
			var target_wrapper = $('.wc_os_ie_wrapper');
			var this_target = $(this).data('target');

			if($(this).val() == 'group_by_acf_group_fields'){

				if($(this).prop('checked')){
					acf_group_options.show();
				}else{
					acf_group_options.hide();
				}

			}


			if($(this).prop('checked')){
				target_wrapper.find('.'+this_target).show();
			}else{
				target_wrapper.find('.'+this_target).hide();
			}

		});


		$('body').on('click','#wos_save_acf_groups', function(){

			var parent = $(this).parents('fieldset:first');
			var all_checkbox = parent.find('input.group_input:checked');
			var loading = $('.wc_os_screen_option img.wc_os_loading');
			var success = $('.wc_os_screen_option .result_success');
			success.hide();

			var checked_groups = [0];
			var checked_methods = ['none'];

			if(all_checkbox.length > 0){
				$.each(all_checkbox, function(){

					var value = $(this).val();
					switch($(this).prop('name')){

						case 'method_selection':
							checked_methods.push(value);
						break;
						case 'acf_group_selection':
							checked_groups.push(value);
						break;

					}
				});
			}

			var data = {
				action: 'wc_os_save_ie_method_selection',
				wc_os_nonce: wos_obj.wc_os_nonce,
				wc_os_acf_group_selection: checked_groups,
				wc_os_ie_method_selection: checked_methods,
			}
			loading.show();
			$.post(ajaxurl, data, function(resp, code){

				loading.hide();
				if(code == 'success'){

					success.css('display', 'inline-block');
					wc_os_hide(success);
					window.location.href = window.location.href;
				}


			});

		});

	$('.wos_acf_wrapper .wos-accordion').on('click', function(e){

		e.preventDefault();
	
		$('.wos_acf_wrapper .wos-accordion').not(this).find('.wos-accordion-plus').show();
		$('.wos_acf_wrapper .wos-accordion').not(this).find('.wos-accordion-minus').hide();
		
		$('.wos_acf_wrapper .wos-accordion-wrapper > .panel.current').removeClass('current');

		$(this).find('.wos-accordion-plus').toggle();
		$(this).find('.wos-accordion-minus').toggle();
		if($(this).next('.panel').is(':visible')){
			$(this).next('.panel').removeClass('current').hide();
		}else{
			$(this).next('.panel').addClass('current').show();
		}
		
		$('.wos_acf_wrapper .wos-accordion-wrapper > .panel:not(.current)').hide();
	


	});




	$('.wos_acf_values span.toggle').on('click', function(e){

			var parent = $(this).parents('.wos_acf_values:first');
			parent.toggleClass('open');
			parent.toggleClass('close');
			parent.find('ul').slideToggle();
	});

	$('.wos_acf_wrapper [name="wc_os_acf_fields[]"]').on('change', function(){

		var parent_row = $(this).parents('tr:first');
		var title = parent_row.find('td.title').text();
		var parent_panel = $(this).parents('.panel:first');
		$('div.wc_os_acf_notice.split').hide();
		parent_panel.find('div.wc_os_acf_notice.split').show();
		parent_panel.find('span.acf_split_notice_field').text(title.trim());

	});

	$('.wos_acf_wrapper [name="wc_os_acf_fields[]"]:checked').change();
	
	$('.wc_settings_div').on('change', '.wos_products_list td.split-actions select.wos_product_action', function(){
		var wc_os_ie_value = $(this).val().trim();
		var row_id = $(this).attr('id').replace('split_action_', '');
		var row_check_box = $('#wip-'+row_id);
		if(wc_os_ie_value != ''){

			row_check_box.prop({'name':'wc_os_settings[wc_os_products]['+wc_os_ie_value+'][]'})
			row_check_box.prop('checked', true).removeClass('hides');

		}else{
			row_check_box.prop('checked', false).addClass('hides');
		}


	});

	$('.wc_settings_div').on('change', 'td.split-actions select:not(.wos_product_action), td.split-group-actions select', function(){

		var parent_row = $(this).parents('tr:first');
		var parent_body = $(this).parents('tbody:first');
		var status_select = parent_row.find('td.wc_os_group_status select');
		var this_val = $(this).val();
		var this_action = status_select.data('action');
		var select_name = (this_val != '' ? 'wc_os_group_statuses['+this_action+']['+this_val+']' : '');
		status_select.prop('name', select_name);
		
		if(this_val != ''){

			var all_select_with_this_val = parent_body.find('tr td.split-actions select:not(.wos_product_action) option[value="'+this_val+'"]:selected, tr td.split-group-actions select option[value="'+this_val+'"]:selected');
			var existing_val = '';
			$.each(all_select_with_this_val, function(){

				var this_parent_tr = $(this).parents('tr');
				var this_status = this_parent_tr.find('td.wc_os_group_status select');
				if(!existing_val && this_status.val() != ''){
					existing_val = this_status.val();
				}

			});

			var all_parents = all_select_with_this_val.parents('tr');
			all_parents.find('td.wc_os_group_status select').val(existing_val);

		}


	});

	$('.wc_settings_div').on('change', 'td.wc_os_group_status select', function() {

		var parent_row = $(this).parents('tr:first');
		var parent_body = $(this).parents('tbody:first');


		var group_select = parent_row.find('td.split-actions select:not(.wos_product_action), td.split-group-actions select');
		var group_select_val = group_select.val();


		if(group_select_val != ''){

			var all_select_with_this_val = parent_body.find('tr td.split-actions select:not(.wos_product_action) option[value="'+group_select_val+'"]:selected, td.split-group-actions select option[value="'+group_select_val+'"]:selected');
			all_select_with_this_val.parents('tr').find('td.wc_os_group_status select').val($(this).val());

		}


	});

	function wc_os_hide_product_group_status(){

		
		var group_status_th =	$('body').find('.wc_settings_div .wos_products_list th.group_status_heading');
		var group_status_td =	$('body').find('.wc_settings_div .wos_products_list td.wc_os_group_status');
				
		var atrib_group_status_th =	$('body').find('.wc_settings_div .wos_attributes_only_list th.group_status_heading');
		var atrib_group_status_td =	$('body').find('.wc_settings_div .wos_attributes_only_list td.wc_os_group_status');
				
		
		group_status_th.hide();
		group_status_td.hide();
				
		atrib_group_status_th.hide();	
		atrib_group_status_td.hide();
		
		
			
		var wc_os_ie = $('.wc_settings_div [name="wc_os_settings[wc_os_ie]"]:checked').val();


		switch(wc_os_ie){
			case 'groups':						
				
				group_status_th.show();
				group_status_td.show();
				
			break;
			case 'group_by_attributes_only':
				
				atrib_group_status_th.show();				
				atrib_group_status_td.show();
				
			break;
		}
	}


	$('.wc_settings_div').on('change', '[name="wc_os_settings[wc_os_ie]"]', function() {
		
		setTimeout(function(){
			wc_os_hide_product_group_status();
		}, 1000);

	});

	$('.wos-list-item .wos-rule-move').on('mousedown', function(){
		$(this).css('cursor', 'grabbing');

	});

	$('.wos-list-item .wos-rule-move').on('mouseup', function(){
		$(this).css('cursor', 'grab');
	});

	$('.wos-list-item .wos-rule-move').on('mouseenter', function(){
		$(this).css('cursor', 'grab');
	});


	if($( ".wc-os-defined-rules .wos-list" ).length>0){
		$( ".wc-os-defined-rules .wos-list" ).sortable({
			handle: ".wos-rule-move"
		});
	}


	$( ".wc-os-defined-rules .wos-list" ).on('sortupdate', function(e, ui){

		var list_items = $('.wc-os-defined-rules ul.wos-list li');

		var rules_sorted = [];

		if(list_items.length > 0){
			$.each(list_items, function(){

				var this_item = $(this);
				var item_key = this_item.attr('data-key');

				rules_sorted.push(item_key);

			});
		}

		var data = {
			'action' : 'wc_os_update_rules_sorted',
			'nonce' : wos_obj.wc_os_nonce,
			'wc_os_rules_sorted' : rules_sorted
		}

		$.post(ajaxurl, data, function(resp){

		});

	});

	$('.wc_settings_div .wc_os_rules_case a').on('click', function(e){

		e.preventDefault();

		var target = $(this).attr('data-target');
		var sub_target = $(this).attr('data-sub_target');
		$('.wc_settings_div .advanced_tab.'+target).click();
		$('.wc_settings_div #'+sub_target).click();


	});


	setTimeout(function(){
		if($('#wc_os_rules').hasClass('nav-tab-active')){
			$('#wc_os_rules').click();
		}
		
		if(Object.keys(wos_obj.order_items_details).length>0){
			$.each(wos_obj.order_items_details, function(item_key, item_data){				
				var item_obj = $('tbody#order_line_items tr[data-order_item_id="'+item_key+'"]');
				if(item_obj.length>0){
					var item_cat_info = '';
					$.each(item_data.cats, function(cat_id, cat_data){
						item_cat_info += cat_data.name+' ('+cat_data.group+'), ';						
					});
					item_obj.attr('title', item_cat_info);
				}
			});
		}		
	}, 100);

    $('#wc_os_auto_clone').on('change', function(){

		var this_check = $(this);
		var this_li = this_check.parents('li:first');
		var this_status_wrapper = this_li.find('div');

		if(this_check.prop('checked')){
			this_status_wrapper.show();
		}else{
			this_status_wrapper.hide();
		}

	});
	
	$('input[name="wc_os_partial_payment[split_type]"]').on('change', function(){

    	if($(this).prop('checked') && $(this).val() == 'group_by'){

    		$(this).parents('.wc_os_partial_payment_list').find('div.wc_os_payment_group_by').show();

		}else{
			$(this).parents('.wc_os_partial_payment_list').find('div.wc_os_payment_group_by').hide();
		}

	});

    $('input[name="wc_os_partial_payment[group_by][date][]"]').on('change', function(){

    	var all_checkbox = $('input[name="wc_os_partial_payment[group_by][date][]"]');
    	var all_checked = [];

    	$.each(all_checkbox, function(){

    		if($(this).prop('checked')){
    			all_checked.push($(this).val());
			}

		});

    	var booking_str = wos_obj.booking_strings;

    	var is_day = $.inArray('day', all_checked) != -1;
    	var is_month = $.inArray('month', all_checked) != -1;
    	var is_year = $.inArray('year', all_checked) != -1;

		const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
			"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
		];

    	var message = '';

		var today = new Date();
		var day = today.getDate();
		var month = monthNames[today.getMonth()];
		var year = today.getFullYear();
		var date = day+", "+month+" "+year;



		if((is_day && !is_month && !is_year) || (is_day && is_month && is_year)){
			message = booking_str.d + " ("+date+")";
		}else if(is_month && !is_day && !is_year){
			message = booking_str.m;
		}else if(is_year && !is_month && !is_day){
			message = booking_str.y;
		}else if(is_month && is_year && !is_day){
			message = booking_str.m_y;
		}else if(is_month && is_day && !is_year ){
			message = booking_str.d_m;
		}else if(is_day && is_year && !is_month){
			message = booking_str.d_y;
		}

		if(message){
			$('div.wc_os_group_by_date_msg').show();
			$('div.wc_os_group_by_date_msg .wc_os_msg').html(message);

		}else{
			$('div.wc_os_group_by_date_msg').hide();
		}

	});

	$('input[name="wc_os_partial_payment[group_by][date][]"]').change();
	
	function wc_os_search_products(search_text){


		var data = {
			'action': 'wc_os_search_products_ajax',
			'search_text': search_text,
			'wc_os_settings_field': $('input[name="wc_os_settings_field"]').val(),
		};

		$('table.wos_products_list tbody').css('opacity', 0.3);
		$('ul.wos_pagination').hide();

		$.post(ajaxurl, data, function(response) {

			if(response.status && response.products_html){

				$('table.wos_products_list tbody').html(response.products_html).css('opacity', 1);

			}else{

				$('table.wos_products_list tbody').css('opacity', 1);

			}
			
			//$('.wos_products_list td.split-actions select.wos_product_action').hide();
			//$('.wos_products_list td.split-actions select:not(.wos_product_action)').show();
			//$('.wos_products_list td.wc_os_group_status').show();			

		});

	}


	$('.wos_products_search > button').on('click', function(e){

		e.preventDefault();

		var search_text = $('.wos_products_search input');
		var search_text = search_text.val();

		if(search_text.length > 0){
			wc_os_search_products(search_text);
		}else{
			$('ul.wos_pagination').show();
			$('ul.wos_pagination .wos_current a').click();
		}

	});

	if(wos_obj.wc_os_product_search){

		setTimeout(function (){

			$('.wos_products_search input').val(wos_obj.wc_os_product_search);
			$('.wos_products_search button').click();

		}, 1000);
	}
	
	$('body').on('keypress', 'input[name="wc_os_product_search"]', function(e){
		
		if (e.which == 13) {
			$(this).parent().find('button').click();
			return false;
		}
	});
	
	$('body').on('click', 'div.speed_optimization_wrapper button', function(e){

		e.preventDefault();

		var all_checkbox = $('.wos_speed_optimization input.speed_inputs');
		var speed_optimization = {};
		var check = $('div.wos_speed_optimization .fa-check');

		check.hide();

		$.each(all_checkbox, function(){

			speed_optimization[$(this).prop('name')] = $(this).prop('checked');

		});


		var data = {
			action: 'wc_os_update_speed_optimization',
			nonce: wos_obj.wc_os_nonce,
			wc_os_speed_optimization: speed_optimization

		}
		$('div.speed_optimization_wrapper').css('opacity', 0.3);

		$.post(ajaxurl, data, function(response){
			$('div.speed_optimization_wrapper').css('opacity', 1);
			if(response.status){

				check.show();

				setTimeout(function(){
					check.hide();

				}, 3000);
			}

		});


	});


	//	code moved from wc_os_import_export.php
	$('body').on('click', 'a.email-log-toggle', function (e) {

		e.preventDefault();
		$.blockUI({message:''});
		var data = {
			action: 'wc_os_email_log',
			wc_os_email_log: $(this).data('status'),
			wc_os_clear_email_log_field: wos_obj.wc_os_clear_log_nonce,
		}
		//console.log($(this).data('status')=='no');
		if($(this).data('status')=='no'){
			$(this).addClass('selected');
			$(this).data('status', 'yes');
			
		}else{
			$(this).removeClass('selected');
			$(this).data('status', 'no');
			
		}
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
		});

	});
	
	$('body').on('click', 'a.debug-log-toggle', function (e) {

		e.preventDefault();
		$.blockUI({message:''});
		var data = {
			action: 'wc_os_debug_log',
			wc_os_debug_log: $(this).data('status'),
			wc_os_debug_log_field: wos_obj.wc_os_clear_log_nonce,
		}
		//console.log($(this).data('status')=='no');
		if($(this).data('status')=='no'){
			$(this).addClass('selected');
			$(this).data('status', 'yes');
			
		}else{
			$(this).removeClass('selected');
			$(this).data('status', 'no');
			
		}
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
		});

	});
	
	$('body').on('click', 'a.order-log-toggle', function (e) {

		e.preventDefault();
		$.blockUI({message:''});
		var data = {
			action: 'wc_os_order_log',
			wc_os_order_log: $(this).data('status'),
			wc_os_clear_email_log_field: wos_obj.wc_os_clear_log_nonce,
		}
		//console.log($(this).data('status')=='no');
		if($(this).data('status')=='no'){
			$(this).addClass('selected');
			$(this).data('status', 'yes');
			
		}else{
			$(this).removeClass('selected');
			$(this).data('status', 'no');
			
		}
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
		});

	});	

	$('a.wc_os_email_clear_log').on('click', function (e) {

		e.preventDefault();
		$.blockUI({message:''});
		$('.wc_os_logger ul.email_log').html('');

		var data = {

			action: 'wc_os_clear_email_log',
			wc_os_clear_email_log: 'true',
			wc_os_clear_email_log_field: wos_obj.wc_os_clear_log_nonce,
		}

		// console.log(data);
		$.post(ajaxurl, data, function (response, code) {

			// console.log(response);
			if (code == 'success') {

				$.unblockUI();
				//
			}

		});

	});
	
	$('a.wc_os_debug_clear_log').on('click', function (e) {

		e.preventDefault();
		$.blockUI({message:''});
		$('.wc_os_logger ul.debug_log').html('');

		var data = {

			action: 'wc_os_debug_log',
			wc_os_clear_debug_log: 'true',
			wc_os_debug_log_field: wos_obj.wc_os_clear_log_nonce,
		}

		// console.log(data);
		$.post(ajaxurl, data, function (response, code) {

			// console.log(response);
			if (code == 'success') {
				$.unblockUI();

				//
			}

		});

	});	


	// code moved from wcos_pro.php

	function wc_os_shop_order_action(){

		if(wos_obj.post_type == 'shop_order' || wos_obj.post_type == 'shop_order_placehold'){

			if($('.woocommerce-order-data__heading').length>0){
				
				var order_heading = '';
				
				if(wos_obj.isset_mt && wos_obj.isset_post){
	
					
						order_heading += 'Order #'+wos_obj.mt;
					
	
				}
				
				if(wos_obj.splitted_from!=''){
					
					if(order_heading==''){
						order_heading = $('.woocommerce-order-data__heading').html();
					}
					
					order_heading += '<span class="wc-os-siblings-display"><a title="'+wos_obj.parent_str+'" class="wc-os-parent" target="_blank" href="'+wos_obj.admin_url.replace('ORDER_ID', wos_obj.splitted_from)+'">#'+wos_obj.splitted_from+' <span>'+wos_obj.orders_statuses[wos_obj.splitted_from]+'</span></a> ( ';

					$.each(wos_obj.sibling_orders, function(i,v){
						
						if(i>0){
							order_heading += ' | ';
						}
						
						order_heading += '<a title="'+wos_obj.child_str+'" class="wc-os-child" target="_blank" href="'+wos_obj.admin_url.replace('ORDER_ID', v)+'">#'+v+' <span>'+wos_obj.orders_statuses[v]+'</a>';
						
					});
					
					order_heading += ' ) </span>';
					
				}
				
				if(order_heading!=''){
					$('.woocommerce-order-data__heading').html(order_heading);
				}
			}
			
			$('.subsubsub').append('| <a href="'+wos_obj.orders_list+'&split_status=no&orderby=ID&order=desc'+'">'+wos_obj.ws_os_to_split+'</a> ');
			
			 

			
		}

	}

	wc_os_shop_order_action();
	
	$('.wc_os_delivery_date select.delivery_type').on('change', function(){

		var selected_val = $(this).val();
		var this_section = $(this).parents('div.single_section:first');
		var extended_option = this_section.find('div.extended_options');
		var weekday_option = this_section.find('div.weekdays_option');
		var interval_option = this_section.find('div.interval_option');

		var show_extended = false;
		var show_weekday = false;
		var show_interval = false;

		switch (selected_val) {
			case 'order_weekdays':
			case 'first_weekdays':

				show_extended = true;
				show_weekday = true;
				
			break;
		
			case 'order_interval':
			case 'first_interval':

				show_extended = true;
				show_interval = true;
				
			break;

		}

		if(show_extended){
			extended_option.css('display', 'flex');
		}else{
			extended_option.hide();
		}

		if(show_weekday){
			weekday_option.show();
		}else{
			weekday_option.hide();
		}

		//console.log(show_weekday);
		//console.log(show_interval);

		if(show_interval){
			interval_option.show();
		}else{
			interval_option.hide();
		}

		

	});

	$('.wc_os_delivery_date .first_order select.delivery_type').on('change', function(){

		var remaining_option_order_date = $('.wc_os_delivery_date .remaining_items select.delivery_type option[value="order_date"]');
		

		if($(this).val() != 'order_date'){

			remaining_option_order_date.prop('selected', false);
			remaining_option_order_date.prop('disabled', true);

		}else{

			remaining_option_order_date.prop('disabled', false);
		}	

	});

	$('.wc_os_delivery_date select.delivery_type').change();
	
		$('.wos_pagination').on('click','.wos_controls .svg-inline--fa', function(e){

		e.preventDefault();

		var control_type = $(this).data('control');
		var pagination_parent = $(this).parents('ul.wos_pagination');
		var active_li = pagination_parent.find('li.wos_current.wos_single');
		var control_parent = pagination_parent.find('li.wos_controls');
		var up_control = control_parent.find('.wos_up');
		var toggle_control = control_parent.find('.wos_toggle');
		var down_control = control_parent.find('.wos_down');
		var total_group = control_parent.data('total_group');
		var active_num = active_li.data('num');
		var active_num_group = active_li.data('group');
		var active_group_obj = pagination_parent.find('li.wos_active_group');
		var active_group = active_group_obj.data('group');

		var next_group = 0;

		switch (control_type) {
			case 'up':
				next_group = active_group + 1;
				
			break;
		
			case 'toggle':

				var all_groups = pagination_parent.find('li.wos_single');


				if(!$(this).hasClass('wos_toggle_active')){

					$(this).addClass('wos_toggle_active')
					up_control.addClass('wos_disabled');
					down_control.addClass('wos_disabled');
					all_groups.removeClass('wos_inactive_group').addClass('wos_active_group');

				}else{
					
					up_control.removeClass('wos_disabled');
					down_control.removeClass('wos_disabled');
					$(this).removeClass('wos_toggle_active');
					all_groups.removeClass('wos_active_group').addClass('wos_inactive_group');
					var active_num_group_obj = pagination_parent.find('li.wos_single.group_'+active_num_group);
					active_num_group_obj.addClass('wos_active_group').removeClass('wos_inactive_group');

					if(active_num_group == total_group){
						up_control.addClass('wos_disabled');
					}else{
						up_control.removeClass('wos_disabled');
					}
			
					if(active_num_group == 1){
						down_control.addClass('wos_disabled');
					}else{
						down_control.removeClass('wos_disabled');
					}

				}


				
			break;
		
			case 'down':
				next_group = active_group - 1;
			break;	

		}

		if((control_type == 'up' || control_type == 'down') && !$(this).hasClass('wos_disabled')){

			if(next_group >= total_group){
				up_control.addClass('wos_disabled');
			}else{
				up_control.removeClass('wos_disabled');
			}
	
			if(next_group <= 1){
				down_control.addClass('wos_disabled');
			}else{
				down_control.removeClass('wos_disabled');
			}
	
			if(next_group <= total_group && next_group > 0){
	
				var next_group_obj = pagination_parent.find('li.wos_single.group_'+next_group);
				var all_groups = pagination_parent.find('li.wos_single');
				all_groups.removeClass('wos_active_group wos_inactive_group').addClass('wos_inactive_group');
				next_group_obj.addClass('wos_active_group').removeClass('wos_inactive_group');
	
			}

		}



	});
	
	$('.wc_os_delivery_selection_wrapper > div').on('click', function(){
		$('.wc_os_delivery_selection_wrapper > div').removeClass('selected');
		$(this).addClass('selected');
		
		if($(this).hasClass('wc_os_delivery_date_selection')){
			$('div.wc_os_delivery_date').show();
			$('#wc_os_delivery_selection').val('delivery_date');
		}else{
			$('div.wc_os_delivery_date').hide();
			$('#wc_os_delivery_selection').val('schedule_delivery');
		}
	});
	
	if($('body.wp-admin #woocommerce-order-data #order_data .order_data_column_container').length>0){
		if(wos_obj.combined_info){
			$('<div class="wc-os-combined-info"><ul>'+wos_obj.combined_info+'</ul></div>').insertAfter($('body.wp-admin #woocommerce-order-data #order_data .order_data_column_container'));
		}
	}
	
	
	$('body').on('click', 'div.attrib_value_nodes > a', function(){
		$(this).parent().find('ul').toggle();
		$(this).parent().find('small').toggle();
		$(this).toggleClass('selected');
		$(this).closest('td').toggleClass('opened');
	});
	$('body').on('click', 'div.attrib_value_nodes ul li label', function(){
		var ticked = $(this).find('input[type="checkbox"]').is(':checked');
		$(this).closest('li').toggleClass(ticked?'ticked':'');
	});
	$('body').on('dblclick', 'form.split-settings-dashboard table tbody tr td input[type="radio"]', function(){
		$(this).removeAttr('checked');
		$(this).closest('tr').removeClass('selected');
	});	
	$('body').on('change, keyup', '#wc-os-statuses-list .wc-os-bgc, #wc-os-statuses-list .wc-os-fgc', function(){
		var updated_color = $(this).val();
		if(updated_color.substr(0, 1)!='#'){ updated_color = '#'+updated_color; }		
		$(this).parent().find('input[type="color"]').val(updated_color);
		$(this).val(updated_color);
		
	});
	$('body').on('change', '#wc-os-statuses-list .wc-os-bgc, #wc-os-statuses-list .wc-os-fgc', function(){
		wc_os_update_status_colors();
	});
	$('body').on('change', '#wc-os-statuses-list input[type="color"]', function(){
		var updated_color = $(this).val();
		$(this).parent().find('input[type="text"]').val(updated_color);
		wc_os_update_status_colors();
	});
	
	var status_colors_update_in_progress = false;
	
	function wc_os_update_status_colors() {
		
		if(status_colors_update_in_progress){ return; }
		status_colors_update_in_progress = true;
		
		$.blockUI({message:''});
		
		var data = {
			action: 'wos_update_status_colors',
			status_colors: $('#wc-os-statuses-list input[name^="wc_os_colors"]').serialize(),
			wc_os_nonce: wos_obj.order_status_nonce,
		};
		
		// console.log(data);
		$.post(ajaxurl, data, function (response, code) {
			status_colors_update_in_progress = false;
			$.unblockUI();
			// console.log(response);
			if (code == 'success') {
				

				//
			}

		});

	}	
	

});

// JavaScript Document
/*! ========================================================================
 * Bootstrap Toggle: bootstrap-toggle.js v2.2.0
 * http://www.bootstraptoggle.com
 * ========================================================================
 * Copyright 2014 Min Hur, The New York Times Company
 * Licensed under MIT
 * ======================================================================== */
+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.toggle"),f="object"==typeof b&&b;e||d.data("bs.toggle",e=new c(this,f)),"string"==typeof b&&e[b]&&e[b]()})}var c=function(b,c){this.$element=a(b),this.options=a.extend({},this.defaults(),c),this.render()};c.VERSION="2.2.0",c.DEFAULTS={on:"On",off:"Off",onstyle:"primary",offstyle:"default",size:"normal",style:"",width:null,height:null},c.prototype.defaults=function(){return{on:this.$element.attr("data-on")||c.DEFAULTS.on,off:this.$element.attr("data-off")||c.DEFAULTS.off,onstyle:this.$element.attr("data-onstyle")||c.DEFAULTS.onstyle,offstyle:this.$element.attr("data-offstyle")||c.DEFAULTS.offstyle,size:this.$element.attr("data-size")||c.DEFAULTS.size,style:this.$element.attr("data-style")||c.DEFAULTS.style,width:this.$element.attr("data-width")||c.DEFAULTS.width,height:this.$element.attr("data-height")||c.DEFAULTS.height}},c.prototype.render=function(){this._onstyle="btn-"+this.options.onstyle,this._offstyle="btn-"+this.options.offstyle;var b="large"===this.options.size?"btn-lg":"small"===this.options.size?"btn-sm":"mini"===this.options.size?"btn-xs":"",c=a('<label class="btn">').html(this.options.on).addClass(this._onstyle+" "+b),d=a('<label class="btn">').html(this.options.off).addClass(this._offstyle+" "+b+" active"),e=a('<span class="toggle-handle btn btn-default">').addClass(b),f=a('<div class="toggle-group">').append(c,d,e),g=a('<div class="toggle btn" data-toggle="toggle">').addClass(this.$element.prop("checked")?this._onstyle:this._offstyle+" off").addClass(b).addClass(this.options.style);this.$element.wrap(g),a.extend(this,{$toggle:this.$element.parent(),$toggleOn:c,$toggleOff:d,$toggleGroup:f}),this.$toggle.append(f);var h=this.options.width||Math.max(c.outerWidth(),d.outerWidth())+e.outerWidth()/2,i=this.options.height||Math.max(c.outerHeight(),d.outerHeight());c.addClass("toggle-on"),d.addClass("toggle-off"),this.$toggle.css({width:h,height:i}),this.options.height&&(c.css("line-height",c.height()+"px"),d.css("line-height",d.height()+"px")),this.update(!0),this.trigger(!0)},c.prototype.toggle=function(){this.$element.prop("checked")?this.off():this.on()},c.prototype.on=function(a){return this.$element.prop("disabled")?!1:(this.$toggle.removeClass(this._offstyle+" off").addClass(this._onstyle),this.$element.prop("checked",!0),void(a||this.trigger()))},c.prototype.off=function(a){return this.$element.prop("disabled")?!1:(this.$toggle.removeClass(this._onstyle).addClass(this._offstyle+" off"),this.$element.prop("checked",!1),void(a||this.trigger()))},c.prototype.enable=function(){this.$toggle.removeAttr("disabled"),this.$element.prop("disabled",!1)},c.prototype.disable=function(){this.$toggle.attr("disabled","disabled"),this.$element.prop("disabled",!0)},c.prototype.update=function(a){this.$element.prop("disabled")?this.disable():this.enable(),this.$element.prop("checked")?this.on(a):this.off(a)},c.prototype.trigger=function(b){this.$element.off("change.bs.toggle"),b||this.$element.change(),this.$element.on("change.bs.toggle",a.proxy(function(){this.update()},this))},c.prototype.destroy=function(){this.$element.off("change.bs.toggle"),this.$toggleGroup.remove(),this.$element.removeData("bs.toggle"),this.$element.unwrap()};var d=a.fn.bootstrapToggle;a.fn.bootstrapToggle=b,a.fn.bootstrapToggle.Constructor=c,a.fn.toggle.noConflict=function(){return a.fn.bootstrapToggle=d,this},a(function(){a("input[type=checkbox][data-toggle^=toggle]").bootstrapToggle()}),a(document).on("click.bs.toggle","div[data-toggle^=toggle]",function(b){var c=a(this).find("input[type=checkbox]");c.bootstrapToggle("toggle"),b.preventDefault()})}(jQuery);
//# sourceMappingURL=bootstrap-toggle.min.js.map