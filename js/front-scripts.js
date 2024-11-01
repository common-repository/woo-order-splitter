// JavaScript Document
function insertParam(key, value)
{
    key = encodeURI(key); value = encodeURI(value);

    var kvp = document.location.search.substr(1).split('&');

    var i=kvp.length; var x; while(i--) 
    {
        x = kvp[i].split('=');

        if (x[0]==key)
        {
            x[1] = value;
            kvp[i] = x.join('=');
            break;
        }
    }

    if(i<0) {kvp[kvp.length] = [key,value].join('=');}

    //this will reload the page, it's likely better to store this until finished
    document.location.search = kvp.join('&'); 
}
function wc_os_trigger_permission_for_spit($, internal){

		var wc_os_customer_permitted = $('input[name="wc_os_customer_permitted"][value="yes"]').prop('checked') ? 'on' : 'off';
		
		if(wc_os_customer_permitted=='on'){
			if($('.wc_os_review_order').length>0){
				$('.wc_os_review_order').show();
			}else{
				
				if(wos_obj.wc_os_packages_overview == 'on'){
					$.blockUI({message:''});
					setTimeout(function(){ document.location.reload(); }, 3000);
				}

			}
		}else{
			$('.wc_os_review_order').hide();
		}

		
		var total_shipping_cost = (wc_os_customer_permitted == 'on' ? wos_obj.total_shipping_cost : 0);

		var data = {
			action : 'wc_os_customer_permitted_method',
			wc_os_customer_permitted_nonce: wos_obj.wc_os_customer_permitted_nonce,
			wc_os_customer_permitted : wc_os_customer_permitted,
			total_shipping_cost: total_shipping_cost,
		}
		
		$(this).prop('disabled', true);
		
		$.blockUI({message:''});
		
		if($.active>0){return false;}

		$.post(wos_obj.ajax_url, data, function(response){
			
			$.unblockUI();
			
			$(this).prop('disabled', false);
			
			var resp = $.parseJSON(response);
			
			if($('tr.fee td').length>0){
				$('tr.fee').eq(0).find('td').html(resp.fee);
			}
			if($('tr.order-total td').length>0 && $.trim(resp.total)!=''){
				$('tr.order-total td').html(resp.total);
			}			
			
			//$('tr.fee').show();
			
			//console.log(0);
			
			if(!internal){
			
				if(
							$('.woocommerce-checkout-review-order-table .woocommerce-shipping-totals').length>0 
						|| 
							$('.woocommerce-checkout-review-order-table tr.fee').length>0
				){
					//console.log(1);
					if(wos_obj.wc_os_shipping_methods==1){					
						//console.log(2);
							
						if(wc_os_customer_permitted=='on'){
							//console.log(3);
							$('tr.fee').show();

						}else{			
							//console.log(4);
							$('tr.fee').hide();

						}
						
						document.location.reload();
					}
				}else{
				
				
					
				}
			}
			
			setTimeout(function(){ document.location.reload(); }, 1000);
			
		});
		
		if(wc_os_customer_permitted=='on'){
			
			if($('.wc_os_review_order').length>0){
				$('.woocommerce-checkout-review-order-table').find('thead, tbody').hide();
			}
			//$('.wc_os_review_order').show();
			//console.log(wos_obj.chosen_shipping_method);
			if(wos_obj.chosen_shipping_method!=''){
				var input_obj = $('input[value="'+wos_obj.chosen_shipping_method+'"]');
				

				
				var input_label = input_obj.parent().find('label');
				if(input_label.length>0){
					var shipping_cost = $.trim($('.wc_os_update_shipping_cost').html());
					//input_label.find('span').html(shipping_cost);
				}
				//shipping_method_0_lpc_sign
				//shipping_method_0_lpc_relay
			}
		}else{
			//$('.wc_os_review_order').hide();
			$('.woocommerce-checkout-review-order-table').find('thead, tbody').show();
			
		}
	
}
jQuery(document).ready(function($){
	
	if(wos_obj.is_pro && wos_obj._wos_backorder_limit!=''){
		
		var qty_obj_str = 'div.quantity input[name="quantity"]';
		
		$('body.single-product').on('change, keydown, keyup', qty_obj_str, function(){
			
			var qty = $(this).val();
			
			if(wos_obj._wos_backorder_limit<qty){
				
				wos_obj._wos_backorder_limit = (wos_obj._wos_backorder_limit>0?wos_obj._wos_backorder_limit:1);
				
				$('div.quantity input[name="quantity"]').attr({'max':wos_obj._wos_backorder_limit});
				$('button[type="submit"]').attr({'title':wos_obj._wos_backorder_msg});
				
			}else{
				$('div.quantity input[name="quantity"]').removeAttr('max');
				$('button[type="submit"]').attr({'title':''});
				
			}
			
		});
		
		if($(qty_obj_str).length>0){
		
			$(qty_obj_str).trigger('change');
		}
	
	}
	
	$('body').on('change', 'select#coderockz_woo_delivery_delivery_selection_box', function(){
		var v = $(this).val();
		$('div.wc_os_review_order label.wos-delivery-date').hide();
		switch(v){
			case 'delivery':
				$('div.wc_os_review_order label.wos-delivery-date').show();
				var dp_obj = $('div.wc_os_review_order label.wos-delivery-date input[type="text"]');
				if($('#coderockz_woo_delivery_date_datepicker').length>0){
					$.each($('#coderockz_woo_delivery_date_datepicker').data(), function(a, v){
						if(typeof v=='object'){							
							dp_obj.attr('data-'+a, v);//"['"+v.join("','")+"']");
						}else{
							dp_obj.attr('data-'+a, v);
						}
					});
				}
				dp_obj = $('div.wc_os_review_order label.wos-delivery-date input[type="text"]');
				var bZ = dp_obj.data("date_format");
								
				var arr = dp_obj.data("disable_week_days");
				var arr2 = arr.split(",");
				var arr3 = [];
				jQuery.each(arr2, function(i, v){
					v = parseInt(v);
					arr3.push(v);
				});
			
								
				dp_obj.flatpickr({
					dateFormat: bZ,
					minDate: "today",
					disable:[
						function(date) {
					
						return (jQuery.inArray(date.getDay(), arr3)!==-1);
						}
					]
				});
			break;
		}
	});
	
	setTimeout(function(){
		if(wos_obj.is_thank_you==true && $('.woocommerce-order-details').length>0 && wos_obj.posted==0){		
			if($('.woocommerce-order-details').length>0){
				$('.woocommerce-order-details').eq(0).insertAfter($('.woocommerce-order-details').eq(1));
			}
			if(window.location.search.indexOf('refreshed') > -1){			
			}else{
				if(wos_obj.orders_page_refresh=='true'){
					insertParam('refreshed', 'true');
				}
			}
		}
		
	}, 500);
	
	$('body').on('click', 'input[name="wc_os_customer_permitted"]', function(){
		
		wc_os_trigger_permission_for_spit($, false);		
	});
	
	setTimeout(function(){
			
		if(wos_obj.is_checkout==true && $('.wc-block-components-order-summary').length>0 && wos_obj.wc_os_customer_permitted){
			
			var data = {
							action : 'wc_os_review_order',
							wc_os_nonce: wos_obj.wc_os_customer_permitted_nonce,				
					
			};
			
			$.blockUI({message:''});
					
			$.post(wos_obj.ajax_url, data, function(response){
				$('.wc-block-components-order-summary').parent().prepend(response);
				$('.wc-block-components-panel__button').click();
				
				$.unblockUI();
			});
					
	
		}
		
	}, 1000);
	
	if(wos_obj.is_checkout==true || wos_obj.is_cart==true){
		
		setTimeout(function(){
			//$('#nasa-before-load').remove();
			if($('input[name="wc_os_customer_permitted"]').length>0){

				if(wos_obj.wc_os_customer_permitted == 'on'){	


					wc_os_trigger_permission_for_spit($, true); //23/10/2020
				}
			}
		}, 1000);
		
		var wocp1 = setInterval(function(){
			if($('.wc_os_customer_permission').length>1){ 
				$('.wc_os_customer_permission').eq(0).remove();
				wc_os_trigger_permission_for_spit($, true);
				//clearInterval(wocp1);
				//console.log('wocp1: '+wocp1);
			}	
			if($('tr.fee').eq(1).length>0){
				$('tr.fee').eq(1).remove();
			}
					
			
		}, 500);
		
		var wocp2 = setInterval(function(){			
			if($('.wc_os_review_order').length>1){ 
				$('.wc_os_review_order').eq(0).remove();
			}		
			
			
		}, 500);	
		
		$( 'form.checkout' ).on( 'change', 'input[name^="payment_method"]', function() {
			$('body').trigger('update_checkout');
		});
		
	}
	
	if($(".wos_notice_div.wos_product").length>1){
		$(".wos_notice_div.wos_product").eq(0).remove();
	}

	if($(".wos_notice_div.wos_product").length>0){
		$(".wos_notice_div.wos_product").eq(0).show();
	}	
	
	
	if(wos_obj.total_cart_items>0 && wos_obj.wc_os_actual_shipping_cost>0 && (wos_obj.is_checkout==true || wos_obj.is_cart==true) && wos_obj.is_thank_you==false){
		
		var shipping_cost_interval = setInterval(function(){
		
			var data = {
				action : 'wc_os_update_parcel_shipping_cost',
				wc_os_nonce: wos_obj.wc_os_customer_permitted_nonce,
				wc_os_total_shipping_cost: wos_obj.total_shipping_cost,
				wc_os_actual_shipping_cost: wos_obj.wc_os_actual_shipping_cost,
				wc_os_parcels_count: wos_obj.wc_os_parcels_count,
				
			}
			
			$(this).prop('disabled', true);
			
			if($.active>0){clearInterval(shipping_cost_interval); return false;}
	
			$.post(wos_obj.ajax_url, data, function(response){
				
				var expected_shipping_cost = (wos_obj.wc_os_parcels_count>1?(wos_obj.shipping_selection=='equal_plus'?wos_obj.wc_os_actual_shipping_cost*wos_obj.wc_os_parcels_count:wos_obj.wc_os_actual_shipping_cost):0);
				//console.log((wos_obj.wc_os_parcels_count+'==1 && '+wos_obj.total_shipping_cost+'>0'));
				//console.log(response.parcels_count+'!='+wos_obj.wc_os_parcels_count);
				//console.log(expected_shipping_cost+'!='+wos_obj.total_shipping_cost);
				if(
						(wos_obj.wc_os_parcels_count==1 && wos_obj.total_shipping_cost>0)
					||	
						response.parcels_count!=wos_obj.wc_os_parcels_count
					||
						expected_shipping_cost!=wos_obj.total_shipping_cost
				){
					//$.blockUI({message:''});
					//document.location.reload();					
				}
				
			});
			
		}, 2000);
	}
});