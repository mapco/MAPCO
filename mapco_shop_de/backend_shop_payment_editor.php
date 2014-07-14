<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");


	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Zahlungs- und Versandmöglichkeiten';
	echo '</p>';
?>

<style type="text/css">

#payment-editor-filters {
	list-style:none;

	border: 1px solid #333;
	padding:10px;
	margin: 5px;
	font-size: 14px;
	border-radius: 5px;	
	background-color: #fff;
}
.payment-item-panel {
	max-height:300px;
}
.payment-item-tab-wrap {
	position:relative;	
}
table.payment-editor-table {
	border:0;
	border-color:none;
}

table.payment-editor-table td{
	border:0;
	border-color:none;
}

.payment-list-item {
	height:30px;
	cursor:pointer;
}

.payment-list-details {
	position:relative;
}

li.payment-item {
	list-style:none;

	border: 1px solid #333;
	padding:10px;
	margin: 5px;
	font-size: 14px;
	border-radius: 5px;	
	background-color: #fff;
}

li.payment-item:hover {
	background-color: #BDC5EC;
}

ul.payment-item-customertypes {
	list-style-type:none;
	clear:both;
}

li.payment-item-customertype {
	list-style:none;
	float:left;
	width: 200px;
	height: 20px;
	padding-right: 5px;
	padding-bottom: 5px;
}
#payment-item-customertypes {
	overflow:auto;
	height:250px;
}

ul.payment-item-countries {
	list-style-type:none;
	max-height:220px;
	overflow:auto;
	padding:0px;
	margin:0px;
}

li.payment-item-country {
	list-style:none;
	float:left;
	width: 250px;
	height: 20px;
	padding-right: 5px;
	padding-bottom: 5px;
}
#payment-item-countries {
	overflow:auto;
	max-height:250px;
}

.payment-state {
	cursor:pointer;	
}
.payment-signal-0 {
	width:10px;
	height:10px;
	border-radius: 5px;	
	background-color:#C72326;	
	border: 1px solid #747474;
}

.payment-signal-1 {
	width:10px;
	height:10px;
	border-radius: 5px;	
	background-color:#37B44D;
	border: 1px solid #747474;	
}

</style>

<script type="text/javascript">

	/*
	*	PaymentEditor globals
	*/

	var countries_array = new Object();
	var countries_options = '';
	
	var customer_types_array = new Object();
	var customer_types_options = '';
	
	var payment_types_array = new Object();
	var payment_types_options = '';
	
	var shop_payment_types_array = new Object();
	
	
	var search_filter = new Array();
		search_filter['shop_id'] = '<?php echo $_SESSION['id_shop']; ?>';
		search_filter['customer_type_id'] = 0;
		search_filter['payment_type_id'] = 0;
		search_filter['country_id'] = 0;
		search_filter['active'] = '';
	
	var paymethods_array = new Array();
	
	/*
	*	PaymentTypes
	*/
	function getPaymentTypes() 
	{
		wait_dialog_show();
		PostData = new Object();
		PostData['API'] = 'shop';
		PostData['APIRequest'] = '_PaymentTypesGet';
		soa2(PostData, "setPaymentTypes");
	}
	
	function setPaymentTypes($xml) 
	{
		$xml.find('payment_type').each(function() 
		{
				var payment_type_id = $(this).find('id_paymenttype').text();
				payment_types_array[payment_type_id] = $(this).find('title').text();
		});
		
		$.each(payment_types_array, function(key, value)
		{
			payment_types_options += '<option value="'+ key +'">'+ value +'</option>';
		});
		$('#filter-payment-types').html('<select id="select-payment-type"><option value="0" selected>Typ (Alle)</option>'+payment_types_options+'</select>');

		$('#select-payment-type').focusout(function() 
		{
			$(this).trigger('change');	
		});		
		
		$('#select-payment-type').change(function() 
		{
			$('#select-payment-type option:selected').each(function() 
			{
				search_filter['payment_type_id'] =	$(this).val();
			});	
		});
		
		$(document).trigger("afterLoadPaymentTypes");
			
	}
	
	/*
	*	ShopCountries
	*/
	function getCountries() 
	{
		wait_dialog_show();
		PostData = new Object();
		PostData['API'] = "shop";
		PostData['APIRequest'] = "_CountriesGetSlim";
		soa2(PostData, "setCountries");
	}
	
	function setCountries($xml) 
	{
		$xml.find('shop_countries').each(function() 
		{
			countries_array[$(this).find('id_country').text()] = $(this).find('country').text();	
		});
		$.each(countries_array, function(key, value)
		{
			countries_options += '<option value="'+ key +'">'+ value +'</option>';
		});
		$('#filter-countries').html('<select id="select-payment-country"><option value="0" selected>Land</option>'+countries_options+'</select>');

		$('#select-payment-country').focusout(function() 
		{
			$(this).trigger('change');	
		});		
		
		$('#select-payment-country').change(function() 
		{
			$('#select-payment-country option:selected').each(function() 
			{
				search_filter['country_id'] =	$(this).val();
			});	
		});
		
		$(document).trigger("afterLoadCountries");
			
	}

	/*
	*	ShopCustomerTypes
	*/
	function getCustomerTypes() 
	{
		wait_dialog_show();
		PostData = new Object();
		PostData['API'] 			= "shop";
		PostData['APIRequest'] 		= "_CustomerTypesGet";
		soa2(PostData, "setCustomerTypes");
	}
	
	function setCustomerTypes($xml)
	{
		$xml.find('customer_types').each(function() 
		{
			customer_types_array[$(this).find('id_customer_types').text()] = $(this).find('label').text();	
		});
		
		$.each(customer_types_array, function(key, value) 
		{
			customer_types_options += '<option value="'+ key +'">'+ value +'</option>';
		});
		$('#filter-customer-types').html('<select id="select-payment-customertype"><option value="0" selected>Kundengruppe</option>'+customer_types_options+'</select>');

		$('#select-payment-customertype').focusout(function()
		{
			$(this).trigger('change');
		});
		
		$('#select-payment-customertype').change(function() 
		{
			$('#select-payment-customertype option:selected').each(function() 
			{
				search_filter['customer_type_id'] =	$(this).val();
				
			});	
		});
		
		$(document).trigger("afterLoadCustomerTypes");
	}
	
	function getShopPaymentTypes() 
	{
		var PostData = new Object();
			PostData['API'] = "shop";
			PostData['APIRequest'] = "_ShopPaymentTypesGet";
			PostData['shop_id'] = '<?php echo $_SESSION['id_shop']; ?>';
			soa2(PostData, "setShopPaymentTypes");
	}
	
	function setShopPaymentTypes($xml)
	{
		$xml.find('payment_type').each(function() 
		{
			shop_payment_types_array[$(this).find('id_shops_payment_types').text()] = payment_types_array[$(this).find('payment_type_id').text()];	
		});
		
		$(document).trigger("afterLoadShopPaymentTypes");
		
	}
	
	/*
	*	main
	*/

	function main() 
	{
		
		/*
		*	base layout structure
		*/
		
		var base_content = '<section id="payment-editor">';
			base_content 	+='<div id="payment-editor-tabs">';
			base_content		+='<ul>';
			base_content			+='<li><a href="#tab-types">Zahlungsarten</a></li>';
			base_content			+='<li><a href="#tab-payments">Zahlungsmethoden</a></li>';
			base_content		+='</ul>';			
			base_content		+='<div class="payment-panel" id="tab-payments">';
			base_content 			+='<div id="payment-editor-filters"></div>';
			base_content			+='<div id="payment-editor-results"></div>';
			base_content		+='</div>';
			base_content		+='<div class="payment-panel" id="tab-types">';
			base_content 			+='<div id="payment-type-filters"></div>';
			base_content			+='<div id="payment-type-results></div>';
			base_content		+='</div>';			
			base_content 	+='</div>';

			base_content+= '</section>';
		$("#content").append(base_content);
		
		$("#payment-editor-tabs").tabs();
		/*
		*	filter layout structure
		*/
		
		var filter_content = '<span id="filter-countries"></span>';
			filter_content += '<span id="filter-customer-types"></span>';
			filter_content += '<span id="filter-active"><select id="select-payment-active"><option value="" selected>Status (Alle)</option><option value="1">aktive</option><option value="0">inaktive</option></select></span>';
			filter_content += '<span id="filter-payment-types"></span>';
			filter_content += '<button id="button-payment-search">suchen</button><button id="button-payment-add">Neu</button>';
		$("#payment-editor-filters").html(filter_content);
		
		/*
		*	filter data requests
		*/
		
		getPaymentTypes();
		
		$(document).on('afterLoadPaymentTypes', function()
		{
			getShopPaymentTypes();
		});

		$(document).on('afterLoadShopPaymentTypes', function()
		{
			getCountries();
		});		

		$(document).on('afterLoadCountries', function()
		{
			getCustomerTypes();
		});			

		
		$(document).on('afterLoadCustomerTypes', function()
		{
			
			/*
			*	filter search button click event
			*/
			
			$('#button-payment-search').click(function() 
			{
				searchPayments();
			});
			
			$('#select-payment-active').focusout(function()
			{
				$(this).trigger('change');
			});
			
			$('#select-payment-active').change(function() 
			{
				$('#select-payment-active option:selected').each(function() 
				{
					search_filter['active'] =	$(this).val();
					
				});	
			});
			searchPayments();
		});
	}
	
	/*
	*	searchPayments
	*/
	
	function searchPayments() 
	{
		PostData = new Object();
		PostData['API'] 				= "shop";
		PostData['APIRequest'] 			= "_PaymentMethodGet";
		PostData['shop_id'] 			= search_filter['shop_id'];
		PostData['country_id'] 			= search_filter['country_id'];
		PostData['customer_type_id'] 	= search_filter['customer_type_id'];
		PostData['payment_type_id'] 	= search_filter['payment_type_id'];
		PostData['active'] 				= search_filter['active'];
		soa2(PostData, "view");	
	}
	
	/*
	*	addPaymentMethod
	*/

	function addPaymentMethod() 
	{
			PostData = new Object();
			PostData['API']					= "shop";
			PostData['APIRequest']			= "_PaymentMethodAdd";
	}

	function view($xml) 
	{
		paymethods = '<ul class="paymethods-list">';
		$xml.find('Payment').each(function() 
		{
			p_payment = $(this).find('payment').text();
			p_id = $(this).find('id_payment').text();
			p_state = $(this).find('active').text();
			var p_memo = $(this).find('payment_memo').text();
			var state_title = "";
			if (p_state == 1)
			{
				state_title = "Payment deaktivieren!";
			} 
			else 
			{
				state_title = "Payment aktivieren!";
			}
			payment_type_id = $(this).find('paymenttype_id').text();
			payment_type_title = payment_types_array[payment_type_id];
			paymethods += '<li id="payment-'+p_id+'" class="payment-item" data-id="'+p_id+'"><table><tr><td><div class="payment-state payment-signal-'+p_state+'" title="'+state_title+'" data-id="'+p_id+'" data-state="'+p_state+'"></div></td><td><div class="payment-list-item" data-id="'+p_id+'" valign="absmiddle"><p>'+ p_payment +' (Typ: '+payment_type_title+')</p><p><small>'+p_memo+'</small></p></div></td></tr></table><div id="payment-list-details-'+p_id+'" class="payment-list-details" style="display:none;"></div></li>'; 
			
		});
		paymethods +='</ul>';
		
		var content = '<table width="100%" class="payment-editor-table">';
		/*
		content +='<tr>';
		content		+='<th width="50%">Zahlungsmethoden</th>';
		content		+='<th width="50%">Details</th>';
		content +='</tr>';
		*/
		content +='<tr>';
		content 	+='<td valign="top" width="50%"><div id="payment-results">'+paymethods+'</div></td>';
		content 	+='<td valign="top" width="50%"><div id="payment-details"></div></td>';
		content +='</tr>';
		content +='</table>';
		
		$('#payment-editor-results').html(content);
		$('#button-payment-add').click(function() 
		{
			searchPayments();
		});
		$('.payment-list-item').click(function(){ 
			payment_id = $(this).data('id');

			 
			var PostData = new Object();
				PostData['API']		= 'shop';
				PostData['APIRequest'] = '_PaymentMethodById';
				
				PostData['payment_id'] = payment_id;
				soa2(PostData, "PaymentDetailsView");
				
				$('#payment-list-details-'+payment_id).toggle();
		});
		
		$("ul.paymethods-list").sortable({
			connectWidth: 'ul.paymethods-list'
		});
		
		$('.payment-state').click(function() 
		{
				var PostData = new Object();
				PostData['API']		= 'shop';
				PostData['APIRequest'] = '_PaymentMethodChangeState';
				
				PostData['payment_id'] = $(this).data('id');

				var state = $(this).data('state');
				if (state == 1) 
				{
					$(this).removeClass('payment-signal-1');
					$(this).addClass('payment-signal-0');
					$(this).data('state','0');
					$(this).attr('title','Payment aktivieren');
					PostData['active'] = 0;
				}
				else if (state == 0)
				{
					$(this).removeClass('payment-signal-0');
					$(this).addClass('payment-signal-1');
					$(this).data('state','1');
					$(this).attr('title','Payment deaktivieren');
					PostData['active'] = 1;
				}
				
				soa2(PostData, "noFeedback");
		});
		
	}
	
	function PaymentDetailsView($xml)
	{
		$xml.find('Payment').each(function() 
		{
			
			$payment = this;
			var p_payment = $(this).find('payment').text();
			var p_id = $(this).find('id_payment').text();
			var p_memo = $(this).find('payment_memo').text();
			
			// Payment Detail Layout
			
			var payment_content_base = '<div id="payment-item-tabs-'+p_id+'" class="payment-item-tab-wrap">';
				payment_content_base 	+='<ul>';
					payment_content_base 	+='<li><a href="#payment-item-details-'+p_id+'">Details</a></li>';
					payment_content_base 	+='<li><a href="#payment-item-countries-'+p_id+'">Länder</a></li>';
					payment_content_base 	+='<li><a href="#payment-item-customertypes-'+p_id+'">Kunden</a></li>';
					payment_content_base 	+='<li><a href="#payment-item-shippings-'+p_id+'">Versandarten</a></li>';
					payment_content_base 	+='<li><a href="#payment-item-operations-'+p_id+'">Operationen</a></li>';
				payment_content_base 	+='</ul>';
				
				payment_content_base 	+='<div class="payment-item-panel" id="payment-item-details-'+p_id+'"></div>';
				payment_content_base 	+='<div class="payment-item-panel" id="payment-item-countries-'+p_id+'"></div>';
				payment_content_base 	+='<div class="payment-item-panel" id="payment-item-customertypes-'+p_id+'"></div>';
				payment_content_base 	+='<div class="payment-item-panel" id="payment-item-shippings-'+p_id+'"></div>';
				payment_content_base 	+='<div class="payment-item-panel" id="payment-item-operations-'+p_id+'">';
					payment_content_base 	+='<button>Payment duplizieren</button>';
					payment_content_base 	+='<button>Payment löschen</button>';
				payment_content_base 	+='</div>';
				payment_content_base+='</div>';	
			$("#payment-list-details-"+p_id).html(payment_content_base);
			// enable tabbing
			$( "#payment-item-tabs-"+p_id).tabs({});
			
			/*
			*	Payment Details
			*/ 
			
			var details_content ='<h2>Details</h2>';
				details_content +='<table>';
				details_content		+='<tr>';
				details_content			+='<td>Label:</td>';
				details_content			+='<td><input type="text" class="payment-detail-update" id="payment-detail-label-'+p_id+'" value="'+p_payment+'" data-id="'+p_id+'" data-field="payment"></td>';
				details_content		+='</tr>';
				details_content		+='<tr>';
				details_content			+='<td>Memo:</td>';
				details_content			+='<td><textarea class="payment-detail-update" id="payment-detail-memo-'+p_id+'" style="min-height:80px; min-width:300px;" data-id="'+p_id+'" data-field="payment_memo">'+p_memo+'</textarea></td>';
				details_content		+='</tr>';
				details_content	+='</table>';

			$("#payment-item-details-"+p_id).html(details_content);

			/*
			*	Payment Countries
			*/			
			var payment_countries_options = ""
			var payment_countries = new Array();

			$.each(countries_array, function(key, value) 
			{
				// defaults
				payment_countries[key] = false; //default false
				var checked = ""; 	//default not checked
				
				// override defaults	
				$($payment).find('country').each(function() 
				{
					var country_id = $(this).find('country_id').text();
					if (country_id === key)
					{
						payment_countries[key] = true;
						checked = "checked";
					}
				});
				
				payment_countries_options += '<li class="payment-item-country"><input type="checkbox" class="payment-countries" name="payment_countries[]" value="'+key+'" '+checked+' data-paymentid="'+p_id+'"> '+value+'</li>';
			});
		
			var payment_countries_content = '<h2>Länder</h2>';
				payment_countries_content += '<ul class="payment-item-countries">'+payment_countries_options+'<li style="clear:both;"></li></ul>';
			$("#payment-item-countries-"+p_id).html(payment_countries_content);

			/*
			*	Payment Customer types
			*/
			
			var ct_array = new Array();
			var ct_options = "";
			
			$.each(customer_types_array, function (key, value) 
			{
				// defaults
				ct_array[key] = false;
				var checked = "";
				
				// override defaults
				$($payment).find('customer_type').each(function()
				{
					var customer_type_id = $(this).find('customer_type_id').text();
					if (customer_type_id === key) 
					{
						ct_array[key] = true;
						checked = "checked";
					}
					
				});
				
				ct_options += '<li class="payment-item-customertype"><input type="checkbox" class="payment-customertypes" name="payment_customertypes[]" data-paymentid="'+p_id+'" value="'+key+'" '+checked+'> '+value+'</li>';
			});
		
			var ct_content = '<h2>Kundengruppen</h2>';
				ct_content += '<ul class="payment-item-customertypes>'+ct_options+'</ul>';
			$("#payment-item-customertypes-"+p_id).html(ct_content);
			
		});
		
		$('input.payment-countries').click(function() 
		{
			PostData = new Object();
			PostData['API'] = 'shop';
			PostData['APIRequest'] = '_PaymentMethodChangeCountry';
			
			PostData['country_id'] = $(this).val();
			PostData['payment_id'] = $(this).data('paymentid');
			soa2(PostData, "noFeedback");
		});
		
		$('input.payment-customertypes').click(function() 
		{
			PostData = new Object();
			PostData['API'] = 'shop';
			PostData['APIRequest'] = '_PaymentMethodChangeCustomerType';
			
			PostData['customer_type_id'] = $(this).val();
			PostData['payment_id'] = $(this).data('paymentid');
			soa2(PostData, "noFeedback");
		});
		
		$('.payment-detail-update').change(function()
		{
			PostData = new Object();
			PostData['API'] = 'shop';
			PostData['APIRequest'] = '_PaymentMethodUpdate';
			
			//if($(this).tagName === 'input')
			//{
				PostData[$(this).data('field')] = $(this).val();
			//}

			
			PostData['id_payment'] = $(this).data('id');
			soa2(PostData, "noFeedback");
		});
	}
	
	function noFeedback($xml) 
	{
	}
	
	
	$(document).ready(function() 
	{
		main();
	});


</script>
 
<?
	
	//include("templates/".TEMPLATE_BACKEND."/footer.php");
?>