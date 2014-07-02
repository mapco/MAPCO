<?php
	if( isset($_GET["getvars1"]) ) $_GET["PayPalAction"]=$_GET["getvars1"];

	include("config.php");
	$login_required=true;
	$title="Warenkorb";
	
	//if ( $_SESSION[ 'id_user' ] == 49352 ) define ( "UST", 33 );

	include("templates/".TEMPLATE."/header.php");
	include("functions/shop_get_prices.php");
	include("functions/shop_mail_order.php");
include("functions/shop_mail_order2.php");
	include("functions/shop_itemstatus.php");	
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");
	include("functions/cms_tl.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_frachtpauschale.php");
	include("functions/shop_checkOnlinePayment.php");
	
	//if ( $_SESSION[ 'id_user' ] == 49352 ) echo '*****************UST: ' . UST . '******************';
?>
	<script type="text/javascript">
		window.onbeforeunload = function()
		{
			var ordernr = document.getElementById("ordernr").value;
			var comment = document.getElementById("comment").value;
			var usermail = document.getElementById("usermail").value;
			var userphone = document.getElementById("userphone").value;
			var userfax = document.getElementById("userfax").value;
			var usermobile = document.getElementById("usermobile").value;
			ajax("<?php echo PATH; ?>modules/shop_cart_actions.php?action=additional_save&ordernr="+ordernr+"&comment="+comment+"&usermail="+usermail+"&userphone="+userphone+"&userfax="+userfax+"&usermobile="+usermobile, false);
		}
		
		function agbs_accepted()
		{
			
			if ($("#form_agbs").is(":checked"))
			{
				$("#cart_agb_button").hide();
				$("#cart_submit_button2").show();
				$("#agb_td").css( "border-color", "lightgrey" );
			}
			else 
			{
				$("#cart_agb_button").show();
				$("#cart_submit_button2").hide();
				$("#agb_td").css( "border-color", "red" );
			}

		}


		function ga_track_transaction($id_order)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"OrderGet", id_order:$id_order }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				//add transaction
				var $storename=$xml.find("shop_id").text();
				var $total=0;
				$xml.find("OrderItem").each(function()
				{
					$total+=parseFloat($xml.find("net").text());
				});
				var $tax=Math.round($total*100*0.19)/100;
				var $shipping=$xml.find("shipping_costs").text();
				var $city=$xml.find("bill_city").text();
				var $country=$xml.find("bill_country").text();
				_gaq.push(['_addTrans',
						$id_order,	// transaction ID - required
						$storename, // affiliation or store name
						$total,		// total - required; Shown as Revenue in the
						// Transactions report. Does not include Tax and Shipping.
						$tax,		// tax
						$shipping,	// shipping
						$city,		// city
						'',			// state or province
						$country	// country
				   ]);
	
				//add transaction items
				$xml.find("OrderItem").each(function()
				{
					var $SKU=$(this).find("item_id").text();
					var $unit_price=$(this).find("net").text();
					var $quantity=$(this).find("amount").text();
					_gaq.push(['_addItem',
								  $id_order,	// transaction ID - necessary to associate item with transaction
								  $SKU,			// SKU/code - required
								  '',			// product name - necessary to associate revenue with product
								  '',			// category or variation
								  $unit_price,	// unit price - required
								  $quantity		// quantity - required
							   ]);
				});
	
				//send transaction
				_gaq.push(['_trackTrans']);
			});
		}

		function submit_cart(shipping_details)
		{
			if ( !$("#form_agbs").prop("checked") )
			{
				$("#agb_td").css( "border-color", "red" );
				alert("<?php echo t("Sie müssen den AGBs zustimmen"); ?>!");
				return;
			}

			if ( shipping_details!="" )
			{
				if(confirm("<?php echo t("Möchten Sie mit der Versandart"); ?> "+shipping_details+" <?php echo t("bestellen"); ?>?"))
				{
					document.cart.submit();
				} 
				else
				{
					payment_edit(); 
				}
			}
			else
			{
				document.cart.submit();
			}
		}		

		function view_cart()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"view", lang:"<?php echo $_GET["lang"]; ?>", id_user:<?php echo $_SESSION["id_user"]; ?> },
				function(data)
				{
					$("#view").html(data);
					waypoints_reload();
				}
			);
		}
		
		function payment_edit()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"payment_edit" },
				   	function(data)
					{
						$("#payment_window").html(data);
						$("#payment_window").dialog
						({	
							dialogClass: "no-close",						
							buttons:
							[
								{ text: "<?php echo t("Speichern"); ?>", click: function() { payment_save(); } },
							//	{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
							],
							closeText:"<?php echo t("Fenster schließen"); ?>",
						 	height:300,
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"<?php echo t("Zahlungsoptionen"); ?>",
							width:600
						});
					}
			);
		}

		function payment_save()
		{
			var id_payment=$("#id_payment_select").val();
			var id_shipping=$("#id_shipping_select").val();
			
			var paymenttype_id=$("#paymenttype_id").val();
			var shippingtype_id=$("#shippingtype_id").val();
			
			var shipping_net=$("#payment_shipping_net").val();
			var shipping_costs=$("#payment_shipping_costs").val();
			var shipping_details=$("#payment_shipping_details").val();
			var payment_memo=$("#payment_payment_memo").val();
			var shipping_memo=$("#payment_shipping_memo").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"payment_save", id_payment:id_payment, id_shipping:id_shipping, paymenttype_id:paymenttype_id, shippingtype_id:shippingtype_id, shipping_net:shipping_net, shipping_costs:shipping_costs, shipping_details:shipping_details, payment_memo:payment_memo, shipping_memo:shipping_memo },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#payment_window").dialog("close");
						view_cart();
					}
			);
		}

		function payment_select()
		{
			var id_payment_select=$("#id_payment_select").val();
			var id_shipping_select=$("#id_shipping_select").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"payment_edit", id_payment_select:id_payment_select, id_shipping_select:id_shipping_select }, function(data) { $("#payment_window").html(data); } );
		}
		
		function availability_edit()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"availability_edit" },
				   	function(data)
					{
						$("#availability_window").html(data);
						$("#availability_window").dialog
						({	buttons:
							[
								{ text: "<?php echo t("Speichern"); ?>", click: function() { availability_save(); } },
								{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
							],
							closeText:"<?php echo t("Fenster schließen"); ?>",
						 	height:300,
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"<?php echo t("Erreichbarkeit"); ?>",
							width:400
						});
					}
			);
		}


		function availability_save()
		{
			var usermail=$("#availability_usermail").val();
			var userphone=$("#availability_userphone").val();
			var userfax=$("#availability_userfax").val();
			var usermobile=$("#availability_usermobile").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"availability_save", usermail:usermail, userphone:userphone, userfax:userfax, usermobile:usermobile },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#availability_window").dialog("close");
						view_cart();
					}
			);
		}

		function additional_edit()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"additional_edit" },
				   	function(data)
					{
						$("#additional_window").html(data);
						$("#additional_window").dialog
						({	buttons:
							[
								{ text: "<?php echo t("Speichern"); ?>", click: function() { additional_save(); } },
								{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
							],
							closeText:"<?php echo t("Fenster schließen"); ?>",
						 	height:300,
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"<?php echo t("Zusätzliche Informationen"); ?>",
							width:400
						});
					}
			);
		}

		function additional_save()
		{
			var ordernr=$("#additional_ordernr").val();
			var comment=$("#additional_comment").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"additional_save", ordernr:ordernr, comment:comment },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#additional_window").dialog("close");
						view_cart();
					}
			);
		}

		function bill_delete()
		{
			if (confirm("<?php echo t("Wollen Sie die Rechnungsanschrift wirklich löschen?"); ?>"))
			{
				var bill_delete=$("#bill_adr_id").val();
				$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillEdit", action:"bill_edit", bill_delete:bill_delete }, function(data) { $("#bill_window").html(data); } );
			}
		}

		function bill_edit()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillEdit", action:"bill_edit" },
				   	function(data)
					{
						$("#bill_window").html(data);
						$("#bill_window").dialog
						({	buttons:
							[
								{ text: "<?php echo t("Speichern"); ?>", click: function() { bill_save(); cart_update();} },
								{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); view_cart(); } }
							],
							closeText:"<?php echo t("Fenster schließen"); ?>",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"<?php echo t("Rechnungsanschrift"); ?>",
							width:700
						});
					}
			);
		}

		function bill_save()
		{
			var bill_adr_id=$("#bill_adr_id").val();
			var bill_company=$("#bill_company").val();
			var bill_gender=$("#bill_gender").val();
			var bill_title=$("#bill_title").val();
			var bill_firstname=$("#bill_firstname").val();
			var bill_lastname=$("#bill_lastname").val();
			var bill_street=$("#bill_street").val();
			var bill_number=$("#bill_number").val();
			var bill_additional=$("#bill_additional").val();
			var bill_zip=$("#bill_zip").val();
			var bill_city=$("#bill_city").val();
			var bill_country_id=$("#bill_country_id").val();
			if ($("#bill_standard:checked").val()===undefined) { var bill_standard=0; }
			else { var bill_standard=1;	}			

			if ( bill_company=="" && bill_firstname=="" && bill_lastname=="" )
			{
				$("#bill_company").css("border", "1px solid red");
				$("#bill_firstname").css("border", "1px solid red");
				$("#bill_lastname").css("border", "1px solid red");
				alert("<?php echo t("Bitte Firma oder Vor- und Nachname ausfüllen!"); ?>");
				return;
			}
			else 
			{
				$("#bill_company").css("border", "1px solid green");
				$("#bill_firstname").css("border", "1px solid green");
				$("#bill_lastname").css("border", "1px solid green");
			}
			if ( bill_company=="" && bill_firstname=="" )	{ $("#bill_firstname").css("border", "1px solid red"); return; }
			else $("#bill_firstname").css("border", "1px solid green");
			if ( bill_company=="" && bill_lastname=="" ) { $("#bill_lastname").css("border", "1px solid red"); return; }
			else $("#bill_lastname").css("border", "1px solid green");
			if ( bill_street=="" ) { $("#bill_street").css("border", "1px solid red"); return; }
			else $("#bill_street").css("border", "1px solid green");
			if ( bill_number=="" ) { $("#bill_number").css("border", "1px solid red"); return; }
			else $("#bill_number").css("border", "1px solid green");
			if ( bill_zip=="" ) { $("#bill_zip").css("border", "1px solid red"); return; }
			else $("#bill_zip").css("border", "1px solid green");
			if ( bill_city=="" ) { $("#bill_city").css("border", "1px solid red"); return; }
			else $("#bill_city").css("border", "1px solid green");

			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillSave", bill_adr_id:bill_adr_id, bill_company:bill_company, bill_gender:bill_gender, bill_title:bill_title, bill_firstname:bill_firstname, bill_lastname:bill_lastname, bill_street:bill_street, bill_number:bill_number, bill_additional:bill_additional, bill_zip:bill_zip, bill_city:bill_city, bill_country_id:bill_country_id, bill_standard:bill_standard },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#bill_window").dialog("close");
						view_cart();
					}
			);
		}
		
		function bill_select()
		{
			var bill_select=$("#bill_adr_id").val();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillEdit", action:"bill_edit", bill_select:bill_select }, function(data) { $("#bill_window").html(data); } );
		}
		
		function bulb_set_add(bulb_set)
		{
			if(typeof bulb_set == 'undefined') return;
			$.post("<?php echo PATH; ?>soa2/", { API:"shop", APIRequest:"CartItemAdd", item_id: bulb_set }, 
				function($data) 
				{ 
					//alert($data);
					view_cart();
					cart_update();
				}
			);
		}
		
		function bulb_set_add_dialog()//Herbstaktion 2013
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"StockGet", id_item: "30702" }, 
				function($data) 
				{ 
					$xml=$($.parseXML($data));
					var stock_1 = $xml.find("Stock").text();
					$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"StockGet", id_item: "30781" }, 
						function($data) 
						{ 
							$xml=$($.parseXML($data));
							var stock_2 = $xml.find("Stock").text();
							//alert(stock_1);
							//alert(stock_2);
							$("#bulb_set_add_window").empty();
							html = '<p><?php echo T("Bitte wählen Sie ein Gratis-Lampen-Set aus:");?></p>';
							html += '<table>';
							html += '	<tr>';
							if(stock_1 != "0")
							{
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px"><input type="radio" name="bulb_set" value="30702" checked></td>';
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px">';
								html += '			<p style="display: inline; font-weight: bold"><?php echo t("Lampen-Set");?> 1 [MAPCO Art.Nr. 103374]</p><br />';
								html += '			<?php echo t("bestehend aus jeweils zwei Lampen der Serien:");?><br/>';
								html += '			12VH1 [MAPCO Art.Nr. 103202] x 2<br/>';
								html += '			12VH4 [MAPCO Art.Nr. 103200] x 2<br/>';
								html += '			12VH7 [MAPCO Art.Nr. 103230] x 2<br/>';
								html += '			12VH11 55W[MAPCO Art.Nr. 103211] x 2<br/>';
								html += '		</td>';
							}
							else
							{
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px"></td>';
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px">';
								html += '			<p style="display: inline; font-weight: bold"><?php echo t("Lampen-Set");?> 1 [MAPCO Art.Nr. 103374]</p><br />';
								html += '			<?php echo t("Leider momentan nicht verfügbar.");?><br/>';
								html += '		</td>';
							}
							html += '	</tr>';
							html += '	<tr>';
							if(stock_2 != "0" && stock_1 != "0")
							{
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px"><input type="radio" name="bulb_set" value="30781"></td>';
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px">';
								html += '			<p style="display: inline; font-weight: bold"><?php echo t("Lampen-Set");?> 3 [MAPCO Art.Nr. 103376]</p><br />';
								html += '			<?php echo t("bestehend aus jeweils einem 10er Set Glühlampen:");?><br/>';
								html += '			Schlussleuchte [MAPCO Art.Nr. 103234] x 10<br/>';
								html += '			Brems-/Schlussleuchte [MAPCO Art.Nr. 103280] x 10<br/>';
								html += '			Kennzeichenleuchte [MAPCO Art.Nr. 103290] x 10<br/>';
								html += '		</td>';
							}
							else if(stock_2 != "0" && stock_1 == "0")
							{
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px"><input type="radio" name="bulb_set" value="30781" checked></td>';
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px">';
								html += '			<p style="display: inline; font-weight: bold"><?php echo t("Lampen-Set");?> 3 [MAPCO Art.Nr. 103376]</p><br />';
								html += '			<?php echo t("bestehend aus jeweils einem 10er Set Glühlampen:");?><br/>';
								html += '			Schlussleuchte [MAPCO Art.Nr. 103234] x 10<br/>';
								html += '			Brems-/Schlussleuchte [MAPCO Art.Nr. 103280] x 10<br/>';
								html += '			Kennzeichenleuchte [MAPCO Art.Nr. 103290] x 10<br/>';
								html += '		</td>';
							}
							else if(stock_2 = "0")
							{
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px"></td>';
								html += '		<td style="border-color: #DDDDDD; border-style: solid; border-width: 1px">';
								html += '			<p style="display: inline; font-weight: bold"><?php echo t("Lampen-Set");?> 2 [MAPCO Art.Nr. 103376]</p><br />';
								html += '			<?php echo t("Leider momentan nicht verfügbar.");?><br/>';
								html += '		</td>';
							}
							html += '	</tr>';
							html += '</table';
							var set_to_add="none";
							if(stock_1 != "0" || stock_2 != "0")
								set_to_add = $("input[name='bulb_set']:checked").val();
							$("#bulb_set_add_window").html(html);
							$("#bulb_set_add_window").dialog
							({	buttons:
								[
									{ text: "<?php echo t("Ok"); ?>", click: function() {bulb_set_add($("input[name='bulb_set']:checked").val()); $(this).dialog("close");} },
									{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
								],
								closeText:"<?php echo t("Fenster schließen"); ?>",
								hide: { effect: 'drop', direction: "up" },
								modal:true,
								resizable:false,
								show: { effect: 'drop', direction: "up" },
								title:"<?php echo t("Lampenset auswählen"); ?>",
								width:450
							});
						} 
					);
				} 
			);
		}
		
/*		function order_deposit_add(order_id)
		{
			$.post("<?php echo PATH; ?>soa2/", { API:"shop", APIRequest:"OrderDepositAdd", order_id: order_id }, 
				function($data) 
				{ 
					//alert($data);
					//view_cart();
					//cart_update();
				}
			);
		}*/
		
		function ship_delete()
		{
			if (confirm("<?php echo t("Wollen Sie die Lieferanschrift wirklich löschen?"); ?>"))
			{
				var ship_delete=$("#ship_adr_id").val();
				$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingEdit", action:"ship_edit", ship_delete:ship_delete }, function(data) { $("#ship_window").html(data); } );
			}
		}

		function ship_edit()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingEdit", action:"ship_edit" },
				   	function(data)
					{
						$("#ship_window").html(data);
						$("#ship_window").dialog
						({	buttons:
							[
								{ text: "<?php echo t("Speichern"); ?>", click: function() { ship_save(); } },
								{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); view_cart(); } }
							],
							closeText:"<?php echo t("Fenster schließen"); ?>",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"<?php echo t("Lieferanschrift"); ?>",
							width:700
						});
					}
			);
		}
		
		function ship_save()
		{
			var ship_adr_id=$("#ship_adr_id").val();
			var ship_company=$("#ship_company").val();
			var ship_gender=$("#ship_gender").val();
			var ship_title=$("#ship_title").val();
			var ship_firstname=$("#ship_firstname").val();
			var ship_lastname=$("#ship_lastname").val();
			var ship_street=$("#ship_street").val();
			var ship_number=$("#ship_number").val();
			var ship_additional=$("#ship_additional").val();
			var ship_zip=$("#ship_zip").val();
			var ship_city=$("#ship_city").val();
			var ship_country_id=$("#ship_country_id").val();
			var ship_country_call=$("#ship_country_call").val();
			if ($("#ship_standard:checked").val()===undefined) { var ship_standard=0; }
			else { var ship_standard=1;	}			

			if ( ship_company=="" && ship_firstname=="" && ship_lastname=="" )
			{
				$("#ship_company").css("border", "1px solid red");
				$("#ship_firstname").css("border", "1px solid red");
				$("#ship_lastname").css("border", "1px solid red");
				alert("<?php echo t("Bitte Firma oder Vor- und Nachname ausfüllen!"); ?>");
				return;
			}
			else 
			{
				$("#ship_company").css("border", "1px solid green");
				$("#ship_firstname").css("border", "1px solid green");
				$("#ship_lastname").css("border", "1px solid green");
			}
			if ( ship_company=="" && ship_firstname=="" )	{ $("#ship_firstname").css("border", "1px solid red"); return; }
			else $("#ship_firstname").css("border", "1px solid green");
			if ( ship_company=="" && ship_lastname=="" ) { $("#ship_lastname").css("border", "1px solid red"); return; }
			else $("#ship_lastname").css("border", "1px solid green");
			if ( ship_street=="" ) { $("#ship_street").css("border", "1px solid red"); return; }
			else $("#ship_street").css("border", "1px solid green");
			if ( ship_zip=="" ) { $("#ship_zip").css("border", "1px solid red"); return; }
			else $("#ship_zip").css("border", "1px solid green");
			if ( ship_city=="" ) { $("#ship_city").css("border", "1px solid red"); return; }
			else $("#ship_city").css("border", "1px solid green");

			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingSave", ship_adr_id:ship_adr_id, ship_company:ship_company, ship_gender:ship_gender, ship_title:ship_title, ship_firstname:ship_firstname, ship_lastname:ship_lastname, ship_street:ship_street, ship_number:ship_number, ship_additional:ship_additional, ship_zip:ship_zip, ship_city:ship_city, ship_country_id:ship_country_id, ship_standard:ship_standard },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#ship_window").dialog("close");
						if (ship_country_call!="" && ship_country_call!=ship_country_id) { payment_edit(); }
						view_cart();
					}
			);
		}
		
		function ship_select()
		{
			var ship_select=$("#ship_adr_id").val();
			var ship_country_call=$("#ship_country_call").val();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingEdit", action:"ship_edit", ship_select:ship_select, ship_country_call:ship_country_call }, function(data) { $("#ship_window").html(data); } );
		}
		
		function SetPayPalCheckout(id_user, firstname, lastname, zip, city, street1, streetnr, street2, countryname, phone, language)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"payments", Action:"PayPalSetExpressCheckout", 
				id_user:id_user, firstname:firstname, lastname:lastname, zip:zip, city:city, street1:street1, streetnr:streetnr, street2:street2, countryname:countryname, phone:phone, language:language }, 
			
				function(data) {
					$xml=$($.parseXML(data));
					var state = $xml.find("state").text();
					if (state=="Success") {
						var paypal_href=$xml.find("paypal_href").text();
						//show_status2(paypal_href);
					
						//Manuelle Weiterleitung zu PayPal
						$("#PayPalLink").html("<a href='"+paypal_href+"' style='cursor:pointer'><?php echo t("Klicken Sie hier, wenn Sie nicht innerhalb weniger Sekunden automatisch weitergeleitet werden."); ?></a>");
						//$("#shop_cart_error").html("Sollten Sie nicht automatisch innerhlab weniger Sekunden zu PayPal weitergeleitet werden, nutzen Sie bitte den folgenden <a href='"+paypal_href+"' style='cursor:pointer'><b> Weiterleitungslink</b></a>");
					//	$("#shop_cart_error").show();
					//	$(".success").hide();
						//automatische WEITERLEITUNG ZU PAYPAL
					//	show_status2(paypal_href);

						window.location = paypal_href;
						
					}
					 else
					{
						
						show_status2(data);
						$("#shop_cart_error").html("<?php echo t("Die Weiterleitung zu PayPal war nicht erfolgreich. Bitte versuchen Sie erneut die Bestellung zu abzuschließen, oder wenden Sie sich an unseren Kundenservice."); ?>");
						$("#shop_cart_error").show();
						$.post("<?php echo PATH; ?>soa/", { API:"payments", Action:"PaymentErrorMail", Paymentplatform: "PayPal", id_user:id_user, firstname:firstname, lastname:lastname, zip:zip, city:city, street1:street1, streetnr:streetnr, street2:street2, countryname:countryname, phone:phone, language:language, data:data}, function(data2) {});
					}
					
			} );
			
		}
		
		function GetPayPalCheckout() {
			$.post("<?php echo PATH; ?>soa/", { API:"payments", Action:"PayPalGetExpressCheckout"}, 
				function (data)
				{
					var $xml=$($.parseXML(data));
					var state = $xml.find("state").text();
					//var state = "Success";
					if (state=="Success") {
						DoPayPalCheckout();
					}
					else
					{
						$("#shop_cart_error").html("<?php echo t("Bei der Übermittlung der Daten von PayPal ist ein Fehler aufgetreten. Ihre PayPal-Zahlung ist nicht erfolgt. Bitte versuchen Sie erneut die Bestellung zu abzuschließen, oder wenden Sie sich an unseren Kundenservice."); ?>");
						$("#shop_cart_error").show();
						$.post("<?php echo PATH; ?>soa/", { API:"payments", Action:"PaymentErrorMail", Paymentplatform: "PayPal", data:data}, function(data2) {});

					}
				}
			);			
		
		}
			
		function DoPayPalCheckout()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"payments", Action:"PayPalDoExpressCheckout" }, 
			
				function (data)
				{
					var $xml=$($.parseXML(data));
					var state = $xml.find("state").text();
				//var state = "Success";
					if (state=="Success") {
						//MANUELLER BESTELLABSCHLUSS
						$("#shop_cart_error").html("<?php echo t("Sollte die Bestellung nicht automatisch innerhlab weniger Sekunden abgeschlossen sein, nutzen Sie bitte den folgenden"); ?> <a href='<?php echo PATHLANG.tl(663, "alias").'?PayPalAction=paymentdone'; ?>' style='cursor:pointer'><b> <?php echo t("Weiterleitungslink"); ?></b></a>");
						$("#shop_cart_error").show();
						//AUTOMATISCHER BESTELLABSCHLUSS
						window.location = "<?php echo PATHLANG.tl(663, "alias").'?PayPalAction=paymentdone'; ?>";
					}
					else
					{
						$("#shop_cart_error").html("<?php echo t("Bei der Übermittlung der Daten von PayPal ist ein Fehler aufgetreten. Ihre PayPal-Zahlung ist nicht erfolgt. Bitte versuchen Sie erneut die Bestellung zu abzuschließen, oder wenden Sie sich an unseren Kundenservice."); ?>");
						$("#shop_cart_error").show();
						$.post("<?php echo PATH; ?>soa/", { API:"payments", Action:"PaymentErrorMail", Paymentplatform: "PayPal", data:data}, function(data2) {});

					}
				}
			);	
		}
		

	</script>

<?php
//echo "++++".$_SESSION["id_shop"];
	//FELD FÜR FEHLERMELDUNGEN
	echo '<div class="warning" id="shop_cart_error" style="display:none"></div>';
//print_r($_GET);	
	
	//Gewerbskunde?
	$gewerblich=gewerblich($_SESSION["id_user"]);

	//Frachtpauschale?
	if($gewerblich)
	{
		$frachtpauschale=frachtpauschale($_SESSION["id_user"]);
	}

	//Lösche Flag für PayPal bei Fehler oder Abbruch -> erneut Bestellbutton
	if (isset($_POST["form_button"])) unset($_SESSION["PayPalCheckout"]);

	//PAYPAL ABBRUCH DURCH KUNDEN
	if (isset($_GET["PayPalAction"]) && $_GET["PayPalAction"]=="abort") {
		echo '<div class="warning">'.t("Die Zahlung der Bestellung per PayPal wurde abgebrochen. Bitte wählen Sie eine andere Zahlungsart.").'</div>';
		echo '<script type="text/javascript">payment_select();</script>';
	}



	//ZAHLUNGSMETHODE AUF ONLINEZAHLUNGEN PRÜFEN
	if (isset($_SESSION["id_payment"]))	
	{
		$onlinePayment = checkOnlinePayment($_SESSION["id_payment"]);
	}


	$onlinepaymentcheckout=false;

	//CHECK, OB PAYPAL-ZAHLUNG DURCHGEFÜHRT WURDE -> Redirect von PayPal
	$onlinePaymentTyp="";
	$onlinepaymentOK=false;
	if (isset($_GET["PayPalAction"]) && $_GET["PayPalAction"]=="payment") {
		//TOKEN VERHGLEICHEN
		if ($_SESSION["paypaltoken"]==$_GET["token"]) {
			$onlinepaymentOK=true;
			$onlinePaymentTyp="PayPal";
			/*
			if ($_SESSION["id_user"]==28625)
			{
				echo "TOKEN".$_GET["token"];
			}
			*/
		}
	}
	
	
	//PAYGENIC
	if (isset($_POST["Userdata"]) && $_POST["Userdata"]=="paygenic_mapco")
	{
		if (isset($_POST["Status"])) 
		{
			if ($_POST["Status"]=="OK" || $_POST["Status"]=="AUTHORIZED")
			{
				$onlinepaymentOK=true;
				$onlinepaymentcheckout=true;
				$_SESSION["PaymentStatus"]=$_POST["Status"];
				$_SESSION["PaymentTransactionID"]=$_POST["PayID"];
				$_POST["formagbs"]="checked";
				if (isset($_POST["PaymentType"]))
				{
					switch ($_POST["PaymentType"]*1)
					{
						case 1: $onlinePaymentTyp="Kreditkarte"; break;	
						case 2: $onlinePaymentTyp="Lastschrift"; break;
						case 3: $onlinePaymentTyp="Sofortüberweisung"; break;				
						default: $onlinePaymentTyp=$_POST["PaymentType"];
					}
				}
			}
			elseif ($_POST["Status"]=="FAILED" && $_POST["Code"]*1==2561)
			{
				echo '<div class="warning">Die Kreditkartendaten wurden zu oft falsch eingegeben. Bitte leiten Sie die Zahlung mittels des Buttons `Kostenpflichtig bestellen` erneut ein und verwenden Sie ggf. eine andere Zahlungsart.</div>';	
			}
			elseif ($_POST["Status"]=="FAILED" && $_POST["Code"]*1==2562)
			{
				echo '<div class="warning">Die Kontodaten wurden zu oft falsch eingegeben. Bitte leiten Sie die Zahlung mittels des Buttons `Kostenpflichtig bestellen` erneut ein und verwenden Sie ggf. eine andere Zahlungsart.</div>';	
			}
			elseif ($_POST["Status"]=="FAILED" && $_POST["Code"]*1==2563)
			{
				echo '<div class="warning">Die Zahlung der Bestellung wurde abgebrochen. Bitte leiten Sie die Zahlung mittels des Buttons `Kostenpflichtig bestellen` erneut ein und verwenden Sie ggf. eine andere Zahlungsart.</div>';	
			}
			elseif ($_POST["Status"]=="FAILED" && $_POST["Code"]*1==2564)
			{
				echo '<div class="warning">Während der Verarbeitung der Payment Transaktion ist ein interner technischer Fehler aufgetreten.  Bitte leiten Sie die Zahlung mittels des Buttons `Kostenpflichtig bestellen` erneut ein und verwenden Sie ggf. eine andere Zahlungsart.</div>';	
			}
			elseif ($_POST["Status"]=="FAILED")
			{
				echo '<div class="warning">Während der Verarbeitung der Payment Transaktion ist ein Fehler aufgetreten.  Bitte leiten Sie die Zahlung mittels des Buttons `Kostenpflichtig bestellen` erneut ein und verwenden Sie ggf. eine andere Zahlungsart. Sollten Sie diese Meldung zum wiederholten Mal erhalten, wenden Sie sich bitte an unseren Kundenservice. Fehlercode('.$_POST["Code"].')</div>';	
			}
			
			//ERRORMAIL
			if ($_POST["Status"]=="FAILED") 
			{
				if(isset($_SESSION["bill_additional"])) $street2=$_SESSION["bill_additional"]; else $street2="";
				if(isset($_SESSION["userphone"])) $phone=$_SESSION["userphone"]; else $phone="";
				
				$response=post(PATH."soa", array ( "API" => "payments", "Action" => "PaymentErrorMail", "Paymentplatform" => "Paygenic", "firstname" => $_SESSION["bill_firstname"], "lastname" => $_SESSION["bill_lastname"], "street1" => $_SESSION["bill_street"], "streetnr" => $_SESSION["bill_number"], "street2" => $street2, "zip" => $_SESSION["bill_zip"], "countryname" => $_SESSION["bill_country"], "phone" => $phone,"code" => $_POST["Code"], "error_description" => $_POST["Description"] ));
			}
		}
	}

	//CHECK, OB PAYPAL TRANSACTION KOMPLETT
	if (isset($_GET["PayPalAction"]) && $_GET["PayPalAction"]=="paymentdone")
	{
		$onlinepaymentcheckout=true; 
		$_POST["formagbs"]="checked";
	} 
	elseif (isset($onlinePayment["paymenttype_id"]) && $onlinePayment["paymenttype_id"]==4)
	{
		$onlinepaymentcheckout=false;
	}
	
	//ZWEITER (+DRITTER) PAYPAL-DURCHLAUF ->GetPayPalCheckout (+DoPayPalCheckout)
	if (isset($onlinePayment) && $onlinePayment["selected"] && $onlinePayment["paymenttype_id"]==4 && $onlinepaymentOK && !$onlinepaymentcheckout) {
		echo '<div class="success">'.t("Ihre Bestellung wird übertragen").'....!</div>';
	/*
		if ($_SESSION["id_user"] == 28625)
		{
			echo "CHECKOUT";
			exit;
		}
		else
		{
			echo '<script type="text/javascript">GetPayPalCheckout();</script>'; 
		}
	*/
	echo '<script type="text/javascript">GetPayPalCheckout();</script>'; 
		include("templates/".TEMPLATE."/footer.php");
		exit;
		// -> Führt zu "paymentdone"
	}

	//Bestellung abschicken
	if ((isset($_POST["form_button"]) || $onlinepaymentcheckout) and !isset($_POST["cartupdate"]) and !isset($_POST["cartclear"]))
	{
		$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)==0) echo '<div class="failure">'.t("Der Warenkorb darf nicht leer sein").'!</div>';
		elseif ($_SESSION["usermail"]=="") echo '<div class="failure">'.t("Sie müssen eine E-Mail-Adresse angeben").'!</div>';
		elseif ($_SESSION["bill_firstname"]=="") echo '<div class="failure">'.t("Sie müssen eine Rechnungsanschrift angeben").'!</div>';
		elseif ($_SESSION["bill_lastname"]=="") echo '<div class="failure">'.t("Sie müssen einen Nachnamen angeben").'!</div>';
		elseif ($_SESSION["bill_zip"]=="") echo '<div class="failure">'.t("Sie müssen eine Postleitzahl angeben").'!</div>';
		elseif ($_SESSION["bill_city"]=="") echo '<div class="failure">'.t("Sie müssen eine Stadt angeben").'!</div>';
		elseif ($_SESSION["bill_street"]=="") echo '<div class="failure">'.t("Sie müssen eine Straße angeben").'!</div>';
		elseif ($_SESSION["bill_number"]=="") echo '<div class="failure">'.t("Sie müssen eine Hausnummer angeben").'!</div>';
		elseif ($_SESSION["bill_country"]=="") echo '<div class="failure">'.t("Sie müssen ein Land angeben").'!</div>';
		elseif (!isset($_POST["formagbs"])) echo '<div class="failure">'.t("Sie müssen den AGBs zustimmen").'!</div>';


		else
		{
			//ERSTER PAYPAL-DURCHLAUF ->SetPayPalCheckout
			if ($onlinePayment["selected"] && !$onlinepaymentOK && !$onlinepaymentcheckout)
			{
				if ($onlinePayment["paymenttype_id"]==4) 
				{
					if ($_SESSION["id_shop"] == 22)
					{
						mail("nputzing@mapco.de", "PAYPAL MAPCO ES", print_r($_SESSION)."<br>".print_r($onlinePayment));	
					}
					if (isset($_SESSION["bill_firstname"])) $firstname=$_SESSION["bill_firstname"];
					if (isset($_SESSION["bill_lastname"])) $lastname=$_SESSION["bill_lastname"];
					if (isset($_SESSION["bill_zip"])) $zip=$_SESSION["bill_zip"];
					if (isset($_SESSION["bill_city"])) $city=$_SESSION["bill_city"];
					if (isset($_SESSION["bill_street"])) $street1=$_SESSION["bill_street"];
					if (isset($_SESSION["bill_number"])) $streetnr=$_SESSION["bill_number"];
					if (isset($_SESSION["bill_additional"])) $street2=$_SESSION["bill_additional"];
					if (isset($_SESSION["bill_country"])) $countryname=$_SESSION["bill_country"];
					if (isset($_SESSION["userphone"])) $phone=$_SESSION["userphone"];
					$language=$_GET["lang"];

					if ($_SESSION["id_user"]!=30234)	// DEMO Kunde
					{
						echo '<div class="success">';
						echo t("Sie werden auf die PayPal-Seite weitergeleitet").'...';
						echo '<br /><br />';
						echo '<span id="PayPalLink">';
						echo '<a href="javascript:SetPayPalCheckout(\''.$_SESSION["id_user"].'\', \''.$firstname.'\', \''.$lastname.'\', \''.$zip.'\', \''.$city.'\', \''.$street1.'\', \''.$streetnr.'\', \''.$street2.'\', \''.$countryname.'\', \''.$phone.'\', \''.$language.'\');">';
						echo 'Klicken Sie hier, wenn Sie nicht innerhalb weniger Sekunden automatisch weitergeleitet werden.</a>';
						echo '</span>';
						echo '</div>';
						echo '<script type="text/javascript">SetPayPalCheckout(\''.$_SESSION["id_user"].'\', \''.$firstname.'\', \''.$lastname.'\', \''.$zip.'\', \''.$city.'\', \''.$street1.'\', \''.$streetnr.'\', \''.$street2.'\', \''.$countryname.'\', \''.$phone.'\', \''.$language.'\');</script>';
					}
					include("templates/".TEMPLATE."/footer.php");
					exit;
					
				}
				/*
				else
				{
					if (isset($_POST["Status"])) 
					echo '<div class="success">';
					echo t("Sie werden zur Zahlung auf die EasyCash - Paygenic-Seite weitergeleitet").'...';
					echo '<br /><br />';
					echo '<span id="PayLink">';
					echo '<a href="javascript:DoPayGenicCheckout(\''.$_SESSION["id_user"].'\', \''.$onlinePayment["method"].'\');">';
					echo 'Klicken Sie hier, wenn Sie nicht innerhalb weniger Sekunden automatisch weitergeleitet werden.</a>';
					echo '</span>';
					echo '</div>';
					echo '<script type="text/javascript">DoPayGenicCheckout(\''.$_SESSION["id_user"].'\', \''.$onlinePayment["method"].'\');</script>';
					include("templates/".TEMPLATE."/footer.php");
					exit;
				}
				*/
			}

			if (isset($_POST["form_button"]) || $onlinepaymentcheckout) {

			if (!isset($_SESSION["PaymentTransactionID"])) $_SESSION["PaymentTransactionID"]="";
			if (!isset($_SESSION["PaymentStatus"])) $_SESSION["PaymentStatus"]="";
			if (!isset($_SESSION["bill_PayPalNote"])) $_SESSION["bill_PayPalNote"]="";

			if (!isset($_SESSION["PayPalPendingReason"])) $_SESSION["PayPalPendingReason"]="";

			$PaymentTransactionStateDate=0;
		//	if ($onlinepaymentcheckout)
			{
			 	$PaymentTransactionStateDate=time();
			}
			
			//Bestellung abspeichern
			$results3=q("SELECT username, password FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			if (!isset($_SESSION["pid"]) or !($_SESSION["pid"]>0)) $pid=0; else $pid=$_SESSION["pid"];
			if(!isset($_SESSION["bill_gender"])) $_SESSION["bill_gender"]="0";
			if(!isset($_SESSION["ship_gender"])) $_SESSION["ship_gender"]="0";
			if ($_SESSION["bill_gender"]==0) $bill_gender='Herr';
			else $bill_gender='Frau';
			if ($_SESSION["ship_gender"]==0) $ship_gender='Herr';
			else $ship_gender='Frau';
			if(!isset($_SESSION["ordernr"])) $_SESSION["ordernr"]="";
			if(!isset($_SESSION["comment"])) $_SESSION["comment"]="";
			
			if(!isset($_SESSION["ship_company"])) $_SESSION["ship_company"]="";
			if(!isset($_SESSION["ship_title"])) $_SESSION["ship_title"]="";
			if(!isset($_SESSION["ship_firstname"])) $_SESSION["ship_firstname"]="";
			if(!isset($_SESSION["ship_lastname"])) $_SESSION["ship_lastname"]="";
			if(!isset($_SESSION["ship_zip"])) $_SESSION["ship_zip"]="";
			if(!isset($_SESSION["ship_city"])) $_SESSION["ship_city"]="";
			if(!isset($_SESSION["ship_street"])) $_SESSION["ship_street"]="";
			if(!isset($_SESSION["ship_number"])) $_SESSION["ship_number"]="";
			if(!isset($_SESSION["ship_additional"])) $_SESSION["ship_additional"]="";

			if($frachtpauschale and stripos($_SESSION["shipping_details"], '(Frachtpauschale)')===FALSE)
			{
				$_SESSION["shipping_details"]=$_SESSION["shipping_details"].' (Frachtpauschale)';
			}
			
			if ($_SESSION["id_user"]!=30234)	// DEMO Kunde
			{
				if (!isset($_SESSION["ship_adr_id"]) or $_SESSION["ship_adr_id"]=='') $_SESSION["ship_adr_id"]=0;
				
				//GET COUNTRY CODE
				$res_codes=q("SELECT * FROM shop_countries;", $dbshop, __FILE__, __LINE__);
				while ($row_codes=mysqli_fetch_array($res_codes))
				{
					$country_code[$row_codes["country"]] = $row_codes["country_code"];
				}
			/*	
				if( stripos($_SESSION["shipping_details"], "DHL Weltpaket") !==false) $shipping_type_id=5;
				elseif( stripos($_SESSION["shipping_details"], "DHL") !== false ) $shipping_type_id=1;
				elseif( stripos($_SESSION["shipping_details"], "Nachnahme, DPD") !== false ) $shipping_type_id=9;
				elseif( stripos($_SESSION["shipping_details"], "DPD") !== false ) $shipping_type_id=3;
				elseif( stripos($_SESSION["shipping_details"], "Nachtversand") !== false ) $shipping_type_id=4;
				elseif( stripos($_SESSION["shipping_details"], "Lieferservice") !== false ) $shipping_type_id=0;
				elseif( stripos($_SESSION["shipping_details"], "Selbstabholung") !== false ) $shipping_type_id=8;
				else $shipping_type_id=10;
			*/	
				
			//	if (isset($_SESSION["shippingtype_id"])) $shipping_type_id = $_SESSION["shippingtype_id"];
				//GET SHIPPING TYPE ID
				$shipping_type_id=0;
				if (isset($_SESSION["id_shipping"]))
				{
					$res_shippingtype_id=q("SELECT * FROM shop_shipping WHERE id_shipping = ".$_SESSION["id_shipping"].";", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_shippingtype_id)==0)
					{
						$shipping_type_id=0;
					}
					else
					{
						$row_shippingtype_id=mysqli_fetch_array($res_shippingtype_id);
						$shipping_type_id=$row_shippingtype_id["shippingtype_id"];
					}
				}

				//GET PAYMENT TYPE ID
				$payment_type_id=0;
				if (isset($_SESSION["id_payment"]))
				{
					$res_paymenttype_id=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND id_payment = ".$_SESSION["id_payment"].";", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_paymenttype_id)==0)
					{
						$payment_type_id=0;
					}
					else
					{
						$row_paymenttype_id=mysqli_fetch_array($res_paymenttype_id);
						$payment_type_id=$row_paymenttype_id["paymenttype_id"];
					}
				}
				
				
				/*
				$res_payments=q("SELECT * FROM shop_payment_types;", $dbshop, __FILE__, __LINE__);
				while ($row_payments=mysqli_fetch_array($res_payments))
				{
					if( stripos($_SESSION["shipping_details"], $row_payments["title"]) !== false ) $payment_type_id=$row_payments["id_paymenttype"];
				}
				*/
				if (isset($_SESSION["paymenttype_id"])) $payment_type_id = $_SESSION["paymenttype_id"];

				//shop id 8
				$id_shop=$_SESSION["id_shop"];
				/*
				if( $_SESSION["id_site"]==1 )
				{
					$res_user=q("SELECT * FROM cms_users WHERE id_user = ".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
					if (mysqli_num_rows($res_user)>0)
					{
						$user=mysqli_fetch_array($res_user);
						if( $user["shop_id"]==8 ) $id_shop=8;
					}
				}
				*/
				$status_id=1;
				$payment_status = "Created";
				//ZAHLUNGSSTATUS SETZEN
				if ($payment_type_id == 5) //KREDITKARTE
				{
					if ($_SESSION["PaymentStatus"]=="OK")
					{
						$payment_status = "Completed";
						$status_id = 7;
					}
					else
					{
						$payment_status = "Denied";
						$status_id = 1;
					}
				}
				
				if ($payment_type_id == 4) // PAYPAL
				{
					$payment_status=$_SESSION["PaymentStatus"];
					if ($_SESSION["PaymentStatus"]=="Completed")
					{
						$status_id = 7;
					}
					else
					{
						$status_id = 7;
					}
				}
				if ($payment_type_id == 3) // NACHNAHME
				{
					$payment_status	= "Pending";
				}

				if ($payment_type_id == 2 || $payment_type_id == 7) // Vorkasse || Barzahlung
				{
					$payment_status	= "Created";
				}

				if ($payment_type_id == 1) // Rechnung
				{
					$payment_status	= "Pending";
				}
				
				//if ($_SESSION["PaymentStatus"]=="Completed" || $_SESSION["PaymentStatus"]=="OK" || $payment_type_id == 1 || $payment_type_id == 3) $status_id=7;
				

				//ADD ORDER TO SHOP ORDER
				
				$vat=19;
				$vat_temp=19;
				$country_id=1;
				
				//GET COUNTRY DATA AND SET VAT
				$eu=1;
				if(isset($_SESSION["bill_country_id"]))
				{
					$res6=q("SELECT * FROM shop_countries WHERE id_country=".$_SESSION["bill_country_id"], $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res6)==1)
					{
						$shop_countries=mysqli_fetch_assoc($res6);
						$eu=$shop_countries["EU"];
						$vat_temp=$shop_countries["VAT"];
						$country_id=$_SESSION["bill_country_id"];
					}
				}
				elseif(isset($_SESSION["origin"]))
				{
					$res7=q("SELECT * FROM shop_countries WHERE country_code='".$_SESSION["origin"]."'", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res7)==1)
					{
						$shop_countries=mysqli_fetch_assoc($res7);
						$eu=$shop_countries["EU"];
						$vat_temp=$shop_countries["VAT"];
						$country_id=$shop_countries["id_country"];
					}
				}
				
				//autopartner und franchise Korrektur
				if( $_SESSION['id_shop'] == 2 or $_SESSION['id_shop'] == 4 or $_SESSION['id_shop'] == 6 or $_SESSION['id_shop'] == 21 ) {
					$vat_temp = 19;
				}
				if ( $_SESSION[ 'id_shop' ] == 19 ) {
					$vat_temp = 21;
				}
				if ( $_SESSION['id_shop'] == 20 ) {
					$vat_temp = 20;
				}
				
				
				if($gewerblich and $country_id!=1) $vat=0;
				elseif($gewerblich and $country_id==1) $vat=$vat_temp;
				elseif(!$gewerblich and $eu==1) $vat=$vat_temp;
				elseif(!$gewerblich and $eu==0) $vat=0;
				
				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderAdd";
				$fieldlist["mode"]="shop";
				
				//FIELDLIST FOR INSERT
				$fieldlist["shop_id"]=$id_shop;
				$fieldlist["ordertype_id"]=1;		// ONLINESHOP BESTELLUNG
				$fieldlist["status_id"]=$status_id;
				$fieldlist["status_date"]=time();
				$fieldlist["Currency_Code"]='EUR';
				$fieldlist["VAT"]=$vat;
				$fieldlist["customer_id"]=$_SESSION["id_user"];
				$fieldlist["ordernr"]=$_SESSION["ordernr"];
				$fieldlist["comment"]=$_SESSION["comment"];
				$fieldlist["usermail"]=$_SESSION["usermail"];
				$fieldlist["userphone"]=$_SESSION["userphone"];
				$fieldlist["userfax"]=$_SESSION["userfax"];
				$fieldlist["usermobile"]=$_SESSION["usermobile"];
				$fieldlist["bill_company"]=$_SESSION["bill_company"];
				$fieldlist["bill_gender"]=$bill_gender;
				$fieldlist["bill_title"]=$_SESSION["bill_title"];
				$fieldlist["bill_firstname"]=$_SESSION["bill_firstname"];
				$fieldlist["bill_lastname"]=$_SESSION["bill_lastname"];
				$fieldlist["bill_zip"]=$_SESSION["bill_zip"];
				$fieldlist["bill_city"]=$_SESSION["bill_city"];
				$fieldlist["bill_street"]=$_SESSION["bill_street"];
				$fieldlist["bill_number"]=$_SESSION["bill_number"];
				$fieldlist["bill_additional"]=$_SESSION["bill_additional"];
				$fieldlist["bill_country"]=$_SESSION["bill_country"];
				$fieldlist["bill_country_code"]=$country_code[$_SESSION["bill_country"]];
				$fieldlist["ship_company"]=$_SESSION["ship_company"];
				$fieldlist["ship_gender"]=$ship_gender;
				$fieldlist["ship_title"]=$_SESSION["ship_title"];
				$fieldlist["ship_firstname"]=$_SESSION["ship_firstname"];
				$fieldlist["ship_lastname"]=$_SESSION["ship_lastname"];
				$fieldlist["ship_zip"]=$_SESSION["ship_zip"];
				$fieldlist["ship_city"]=$_SESSION["ship_city"];
				$fieldlist["ship_street"]=$_SESSION["ship_street"];
				$fieldlist["ship_number"]=$_SESSION["ship_number"];
				$fieldlist["ship_additional"]=$_SESSION["ship_additional"];
				$fieldlist["ship_country"]=$_SESSION["ship_country"];
				$fieldlist["ship_country_code"]=$country_code[$_SESSION["ship_country"]];
				$fieldlist["shipping_costs"]=$_SESSION["shipping_costs"];
				$fieldlist["shipping_type_id"]=$shipping_type_id;
				$fieldlist["shipping_details"]=$_SESSION["shipping_details"];
				$fieldlist["shipping_details_memo"]=$_SESSION["shipping_details_memo"];
				$fieldlist["Payments_TransactionID"]=$_SESSION["PaymentTransactionID"];
				$fieldlist["Payments_TransactionState"]=$payment_status;
				$fieldlist["Payments_TransactionStateDate"]=$PaymentTransactionStateDate;
				$fieldlist["Payments_Type"]=$onlinePaymentTyp;
				$fieldlist["payments_type_id"]=$payment_type_id;
				$fieldlist["PayPal_PendingReason"]=$_SESSION["PayPalPendingReason"];
				$fieldlist["PayPal_BuyerNote"]=$_SESSION["bill_PayPalNote"];
				$fieldlist["partner_id"]=$pid;
				$fieldlist["bill_adr_id"]=$_SESSION["bill_adr_id"];
				$fieldlist["ship_adr_id"]=$_SESSION["ship_adr_id"];
				$fieldlist["firstmod"]=time();
				$fieldlist["firstmod_user"]=$_SESSION["id_user"];
				$fieldlist["lastmod"]=time();
				$fieldlist["lastmod_user"]=$_SESSION["id_user"];
//				$fieldlist["username"]=$row3["username"];
//				$fieldlist["password"]=$row3["password"];
				$fieldlist["shipping_net"]=$_SESSION["shipping_net"];
				
				$responseXML=post(PATH."soa2/", $fieldlist);
		
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXML);
				}
				catch(Exception $e)
				{
					show_error(9756, 7, __FILE__, __LINE__, $responseXML);
					echo '<div class="failure">'.t("Die Bestellung konnte nicht gespeichert werden").'!</div>';
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				if ($response->Ack[0]=="Success")
				{
					$order_id=(int)$response->id_order[0];
					$event_id=(int)$response->id_event[0];
				}
				else
				{
					show_error(9772, 7, __FILE__, __LINE__, $responseXML.print_r($fieldlist, true));
					echo '<div class="failure">'.t("Die Bestellung konnte nicht gespeichert werden").'!</div>';
					exit;
				}
				
				unset($response);
				unset($responseXML);


				//Bestellte Artikel und Preise abspeichern
				$gross_total=0;
				$net_total=0;
				$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
					$price = get_prices($row["item_id"], $row["amount"]);

					$gross_total+=$price["total"]*$row["amount"];//Herbstaktion
					$net_total+=$price["net"];
					
					$fieldlist=array();
					//BASISFELDER FÜR API-AUFRUF
					$fieldlist["API"]="shop";
					$fieldlist["APIRequest"]="OrderItemAdd";
					$fieldlist["mode"]="shop";

					if($row["item_id"]!="30781" and $row["item_id"]!="30702")
					{
						//FIELDLIST FOR UPDATE
						$fieldlist["order_id"]=$order_id;
						$fieldlist["item_id"]=$row["item_id"];
						$fieldlist["amount"]=$row["amount"];
						$fieldlist["price"]=round( $price["total"], 2 );
						$fieldlist["netto"]=round( $price["net"], 2 );
						$fieldlist["collateral"]=round( $price["collateral_total"], 2 );
						$fieldlist["Currency_Code"]='EUR';
						$fieldlist["exchange_rate_to_EUR"]=1;
						$fieldlist["customer_vehicle_id"]=$row["customer_vehicle_id"];
					}
					else if($row["item_id"]=="30781" or $row["item_id"]=="30702")
					{
						//FIELDLIST FOR UPDATE
						$fieldlist["order_id"]=$order_id;
						$fieldlist["item_id"]=$row["item_id"];
						$fieldlist["amount"]=1;
						$fieldlist["price"]=0;
						$fieldlist["netto"]=0;
						$fieldlist["collateral"]=round( $price["collateral_total"], 2 );
						$fieldlist["Currency_Code"]='EUR';
						$fieldlist["exchange_rate_to_EUR"]=1;
						$fieldlist["customer_vehicle_id"]=0;
					}
					
					$responseXML=post(PATH."soa2/", $fieldlist);
		
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXML);
					}
					catch(Exception $e)
					{
						show_error(9756, 7, __FILE__, __LINE__, $responseXML);
						echo '<div class="failure">'.t("Eine Position aus Ihrer Bestellung konnte nicht gespeichert werden").'!</div>';
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					if ($response->Ack[0]!="Success")
					{
						show_error(9773, 7, __FILE__, __LINE__, $responseXML);
						echo '<div class="failure">'.t("Eine Position aus Ihrer Bestellung konnte nicht gespeichert werden").'!</div>';
						exit;
					}
					
					unset($response);
					unset($responseXML);
				}
				
				// Warenkorb leeren
				$res = q( "DELETE FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id='".$_SESSION["id_user"]."'", $dbshop, __FILE__, __LINE__ );
		
				if ( $_SESSION["id_user"] != 49352 )
				{
		
					//PAYMENTNOTIFICATIONHANDLER 
					$response = "";
					$responseXML = "";
					$fieldlist=array();
					//BASISFELDER FÜR API-AUFRUF
					$fieldlist["API"]="payments";
					$fieldlist["APIRequest"]="PaymentNotificationHandler";
					$fieldlist["mode"]="OrderAdd";
					$fieldlist["orderid"]=$order_id;
					$fieldlist["order_event_id"]=$event_id;
					
					$responseXML=post(PATH."soa2/", $fieldlist);
				
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXML);
					}
					catch(Exception $e)
					{
						show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
						//exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					if ($response->Ack[0]!="Success")
					{
						show_error(9773, 7, __FILE__, __LINE__, $responseXML, false);
						//exit;
					}
					
					unset($response);
					unset($responseXML);
				}
				
				
				//Herbstaktion 2013
				//if(time()>=1382306400 and time()<=1385333999 and $_SESSION["origin"]=="DE")
				if($_SESSION["origin"]=="DE")
				{
					if(!$gewerblich)
					{
						$user_deposit=0;
						$results2=q("SELECT * FROM shop_user_deposit WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($results2)>0)
						{
							$row2=mysqli_fetch_array($results2);
							$user_deposit=$row2["deposit"];
						}
						
						//$user_deposit_new=0;
						//if(($gross_total-$user_deposit)>=79)
							//$user_deposit_new=round(($gross_total-$user_deposit)/10, 2);
							
						if($user_deposit>0 and $user_deposit<=$gross_total)
						{
							q("INSERT INTO shop_orders_items (order_id, item_id, amount, price, netto, collateral, Currency_Code, exchange_rate_to_EUR, customer_vehicle_id) VALUES('".$order_id."', 28760, -1, '".round($user_deposit, 2)."', '".round(($user_deposit/1.19), 2)."', '0', 'EUR', 1, 0);", $dbshop, __FILE__, __LINE__);
							q("DELETE FROM shop_user_deposit WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
							//if($user_deposit_new>0)
							//q("INSERT INTO shop_user_deposit (user_id, deposit) VALUES (".$_SESSION["id_user"].", ".$user_deposit_new.");", $dbshop, __FILE__, __LINE__);
						}
						else if($user_deposit>$gross_total)
						{
							q("INSERT INTO shop_orders_items (order_id, item_id, amount, price, netto, collateral, Currency_Code, exchange_rate_to_EUR, customer_vehicle_id) VALUES('".$order_id."', 28760, -1, '".round($gross_total, 2)."', '".round(($gross_total/1.19), 2)."', '0', 'EUR', 1, 0);", $dbshop, __FILE__, __LINE__);
							q("UPDATE shop_user_deposit SET deposit=".round(($user_deposit-$gross_total), 2)." WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
							//q("UPDATE shop_user_deposit SET deposit=".round(($user_deposit-$gross_total+$user_deposit_new), 2)." WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
						}
						else if($user_deposit==0 and $user_deposit_new>0)
						{
							//q("INSERT INTO shop_user_deposit (user_id, deposit) VALUES (".$_SESSION["id_user"].", ".$user_deposit_new.");", $dbshop, __FILE__, __LINE__);
						}
						//*******testing***********
						//echo '<script type="text/javascript"> order_deposit_add('.$order_id.');';
						//*************************
/*
						//SET USER DEPOSIT HERBSTAKTION
						if ($_SESSION["PaymentStatus"]=="Completed" || $_SESSION["PaymentStatus"]=="OK")
						{
									   $responseXml = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "OrderDepositAdd", "order_id" => $order_id));
													   
									   $use_errors = libxml_use_internal_errors(true);
									   try
									   {
													   $response = new SimpleXMLElement($responseXml);
									   }
									   catch(Exception $e)
									   {
													   show_error(9756, 7, __FILE__, __LINE__, $responseXml);
													   exit;
						//track_error(9756, 7, __FILE__, __LINE__, $responseXml);
						
									   }
									   libxml_clear_errors();
									   libxml_use_internal_errors($use_errors);
									   if( $response->Ack[0]!="Success")
									   {
													   show_error(9781, 7, __FILE__, __LINE__, $responseXml);
													   exit;
						//track_error(9781, 7, __FILE__, __LINE__, $responseXml);
									   }
						}
*/
					}
				}//Ende Herbstaktion
				
				q("UPDATE cms_users SET payment_id=".$_SESSION["id_payment"].", shipping_id=".$_SESSION["id_shipping"]." WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);

				
				//Google Analytics ecommerce api
				echo '<script type="text/javascript"> ga_track_transaction('.$order_id.'); </script>';

				//Bestellung nach Borkheide schicken
				if (!$onlinePayment["selected"] || $_SESSION["PaymentStatus"]=="Completed" || $_SESSION["PaymentStatus"]=="OK" || $_SESSION["PaymentStatus"]=="AUTHORIZED" )
				{
					//mail_order($order_id);
//					mail_order2($order_id, false, true);
					
					unset($response);
					unset($responseXML);
					
					// MAIL ZUM SHOP					
					$post_data=array();
					$post_data["API"]="shop";
					$post_data["APIRequest"]="MailOrderSeller";
					$post_data["order_id"]=$order_id;
					
					$postdata=http_build_query($post_data);
					
					$responseXml = post(PATH."soa2/",$postdata);
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
						echo $e;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					
				}
				// MAIL ZUM KUNDEN
				//mail_order2($order_id, true, false);
				
				unset($response);
				unset($responseXML);
				// MAIL ZUM KUNDEN NEU
				$post_data=array();
				$post_data["API"]="shop";
				$post_data["APIRequest"]="MailOrderConfirmation";
				$post_data["order_id"]=$order_id;
				
				$postdata=http_build_query($post_data);
				
				$responseXml = post(PATH."soa2/",$postdata);
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo $e;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				

				//Google Analytics ecommerce api
				echo '<script type="text/javascript"> ga_track_transaction('.$order_id.'); </script>';
			}
			//echo print_r($_SESSION);
			//Warenkorb leeren
//			echo '<script type="text/javascript"> cart_clear2(); <x/script>';
			//Bestellinformationen leeren
			unset($_SESSION["comment"]);
			unset($_SESSION["ordernr"]);

			unset($_SESSION["PayPalPendingReason"]);
			unset($_SESSION["PAYMENTREQUEST_0_ITEMAMT"]);
			unset($_SESSION["PAYMENTREQUEST_0_SHIPPINGAMT"]);
			unset($_SESSION["PAYMENTREQUEST_0_TAXAMT"]);
			unset($_SESSION["PAYMENTREQUEST_0_AMT"]);
			unset($_SESSION["paypaltoken"]);
			unset($_SESSION["PayPalCheckout"]);
			unset($_SESSION["PaymentTransactionID"]);
			unset($_SESSION["PaymentStatus"]);
			unset($_SESSION["bill_PayPalPayerID"]);

			unset($_POST["form_agbs"]);
//mail("pmueller@mapco.de", "Session", print_r($_SESSION, true).session_id());
			//echo '<div class="success">'.t("Bestellung erfolgreich versendet").'.</div>';
			echo '<script type="text/javascript">location.href = "'.PATHLANG.tl(663, "alias").'";</script>';
			exit;
			} // ELSEIF isset($_POST["form_button"])
		}
	}
	
	//Warenkorb aktualisieren
	if ( isset($_POST["cartupdate"]) or (isset($_POST["cartupdate_x"]) and isset($_POST["cartupdate_y"])) )
	{		
		for($i=0; $i<sizeof($_POST["item_id"]); $i++)
		{
			$responseXml = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "CartItemGet", "item_id" => $_POST["item_id"][$i]));
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				echo '<div class="failure">'.t("Bei der Abfrage des Cartbestands ist ein Fehler aufgetreten.").'.</div>';
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			$amount_old=$response->amount[0];
			
			$results=q("SELECT * FROM shop_items WHERE id_item='".$_POST["item_id"][$i]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$shop_items=mysqli_fetch_array($results);
			if ($shop_items["GART"]==82 and strpos($shop_items["MPN"] ,'/2')=== false and fmod($_POST["amount"][$i],2)!=0) 
			{
				echo '<div class="failure">'.t("Die Stückzahl von Artikel").' '.$shop_items["MPN"].' '.t("wurde nicht verändert").'. '.t("Bremsscheiben werden nur als Satz verkauft!").'.</div>';
			}
			else
			{
				if($_POST["amount"][$i]>$amount_old)
				{
					$amount_add=$_POST["amount"][$i]-$amount_old;
					$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "CartAdd", "id_item" => $_POST["item_id"][$i], "amount" => $amount_add));
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						echo '<div class="warning">'.$responseXml.'.</div>';
					}
					catch(Exception $e)
					{
						//echo '<div class="failure">'.$e.'.</div>';
						echo '<div class="failure">'.t("Beim Update des Warenkorbs ist ein Fehler aufgetreten.").'.</div>';
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
				}
				else
				{
					if ($_POST["amount"][$i]>0)
					{
						q("UPDATE shop_carts SET amount=".$_POST["amount"][$i].", lastmod=".time()." WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["item_id"][$i]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("DELETE FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["item_id"][$i]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
					}
				}
			}
		}
		echo '<script type="text/javascript"> cart_update(); </script>';
		echo '<div class="success">'.t("Warenkorb erfolgreich aktualisiert").'!</div>';

	}
		
	//Gutschein verwerten
	$coupon_value=0;
	$total=0;
	if ( isset($_POST["coupon_code"]) and $_POST["coupon_code"]=="12345" )
	{
		$results=q("SELECT * FROM shop_carts AS a, shop_items AS b, shop_items_".$_GET["lang"]." AS c WHERE a.shop_id=".$_SESSION["id_shop"]." AND a.user_id='".$_SESSION["id_user"]."' AND item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$total+=$row["amount"]*get_net_price($row["id_item"]);
		}
		$total*=((100+UST)/100);
		if ($total>50)
		{
			if ($gewerblich) $coupon_value=8.10;
			else $coupon_value=10;
			echo '<div class="success">Ihr Gutschein wurde erfolgreich erfasst.</div>';
		}
		else
		{
			echo '<div class="failure">Der nötige Bestellwert von 50 Euro ist für diesen Gutschein noch nicht erreicht.</div>';
		}
	}
	elseif( isset($_POST["coupon_code"]) and $_POST["coupon_code"]!="")
	{
		echo '<div class="failure">Ihr Gutscheincode ist leider nicht gültig. Bitte überprüfen Sie Ihre Eingabe!</div>';
	}
	
	
	//Artikel aus dem Warenkorb entfernen
	if ( isset($_POST["item_id"]) )
	{
		for($i=0; $i<sizeof($_POST["item_id"]); $i++)
		{
			if (isset($_POST["removeitem".$_POST["item_id"][$i]."_x"]))
			{
				q("DELETE FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["item_id"][$i]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
				echo '<script type="text/javascript"> cart_update(); </script>';
				echo '<div class="success">'.t("Artikel erfolgreich aus dem Warenkorb entfernt").'!</div>';
			}
		}
	}


	//Warenkorb leeren
	if (isset($_POST["cartclear"]))
	{
		q("DELETE FROM shop_carts WHERE user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		echo '<script type="text/javascript">cart_update(); </script>';
		echo '<div class="success">'.t("Warenkorb erfolgreich geleert").'</div>';
	}

	//read user data
	if(!isset($_SESSION["bill_adr_id"]))
	{
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$kundennr=$row["username"];
		
		if($row["payment_id"]>0 and $row["shipping_id"]>0)
		{
			//Standard Zahlungsart
			$results2=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND id_payment=".$row["payment_id"].";", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)>0 )
			{
				$row2=mysqli_fetch_array($results2);
				$_SESSION["id_payment"]=$row2["id_payment"];
				$_SESSION["payment_memo"]=$row2["payment_memo"];
				$_SESSION["shipping_details"]=$row2["payment"];
			
				//Standard Versandart
				$results3=q("SELECT * FROM shop_shipping WHERE id_shipping=".$row["shipping_id"].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$_SESSION["id_shipping"]=$row3["id_shipping"];
				$_SESSION["shipping_net"]=$row3["price"];
				if (gewerblich($_SESSION["id_user"])) $_SESSION["shipping_costs"]=$row3["price"];
				else $_SESSION["shipping_costs"]=((100+UST)/100)*$row3["price"];
				$_SESSION["shipping_details"].=', '.$row3["shipping"];
				$_SESSION["shipping_memo"]=$row3["shipping_memo"];
			}
		}
		
		if ( !isset($_POST["usermail"]) or $_POST["usermail"]=="") $_POST["usermail"]=$row["usermail"];
		
		$results=q("SELECT * FROM shop_orders WHERE customer_id=".$_SESSION["id_user"]." ORDER BY firstmod DESC LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			if (!isset($_SESSION["usermail"]) or $_SESSION["usermail"]=="") $_SESSION["usermail"]=$row["usermail"];
			if (!isset($_SESSION["userphone"]) or $_SESSION["userphone"]=="") $_SESSION["userphone"]=$row["userphone"];
			if (!isset($_SESSION["userfax"]) or $_SESSION["userfax"]=="") $_SESSION["userfax"]=$row["userfax"];
			if (!isset($_SESSION["usermobile"]) or $_SESSION["usermobile"]=="") $_SESSION["usermobile"]=$row["usermobile"];
		}

		$results2=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 and standard=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results2)>0)
		{
			$row2=mysqli_fetch_array($results2);
			$_SESSION["bill_adr_id"]=$row2["adr_id"];
			$_SESSION["bill_company"]=$row2["company"];
			$_SESSION["bill_gender"]=$row2["gender"];
			$_SESSION["bill_title"]=$row2["title"];
			$_SESSION["bill_firstname"]=$row2["firstname"];
			$_SESSION["bill_lastname"]=$row2["lastname"];
			$_SESSION["bill_street"]=$row2["street"];
			$_SESSION["bill_number"]=$row2["number"];
			$_SESSION["bill_additional"]=$row2["additional"];
			$_SESSION["bill_zip"]=$row2["zip"];
			$_SESSION["bill_city"]=$row2["city"];
			$_SESSION["bill_country_id"]=$row2["country_id"];
			$_SESSION["bill_country"]=$row2["country"];
			$_SESSION["bill_standard"]=$row2["standard"];
		}
		else
		{
			if (mysqli_num_rows($results)>0)
			{
				$results3=q("SELECT * FROM shop_countries WHERE country='".$row["bill_country"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results3)>0)
				{
					$row3=mysqli_fetch_array($results3);
					$_SESSION["bill_company"]=$row["bill_company"];
					if ($row["bill_gender"]=="Frau") $_SESSION["bill_gender"]=1; else $_SESSION["bill_gender"]=0;
					$_SESSION["bill_title"]=$row["bill_title"];
					$_SESSION["bill_firstname"]=$row["bill_firstname"];
					$_SESSION["bill_lastname"]=$row["bill_lastname"];
					$_SESSION["bill_street"]=$row["bill_street"];
					$_SESSION["bill_number"]=$row["bill_number"];
					$_SESSION["bill_additional"]=$row["bill_additional"];
					$_SESSION["bill_zip"]=$row["bill_zip"];
					$_SESSION["bill_city"]=$row["bill_city"];
					$_SESSION["bill_country"]=$row["bill_country"];
					$_SESSION["bill_country_id"]=$row3["id_country"];	
					q("INSERT INTO shop_bill_adr (user_id, company, gender, title, firstname, lastname, street, number, additional, zip, city, country, country_id, standard) VALUES(".$_SESSION["id_user"].", '".mysqli_real_escape_string($dbshop, $row["bill_company"])."', '".$_SESSION["bill_gender"]."', '".mysqli_real_escape_string($dbshop, $row["bill_title"])."', '".mysqli_real_escape_string($dbshop, $row["bill_firstname"])."', '".mysqli_real_escape_string($dbshop, $row["bill_lastname"])."', '".mysqli_real_escape_string($dbshop, $row["bill_street"])."', '".mysqli_real_escape_string($dbshop, $row["bill_number"])."', '".mysqli_real_escape_string($dbshop, $row["bill_additional"])."', '".mysqli_real_escape_string($dbshop, $row["bill_zip"])."', '".mysqli_real_escape_string($dbshop, $row["bill_city"])."', '".$row["bill_country"]."', ".$row3["id_country"].", 1);", $dbshop, __FILE__, __LINE__);	
		
					$_SESSION["bill_adr_id"]=mysqli_insert_id($dbshop);
				}
				else
				{
					$_SESSION["bill_adr_id"]="";
					$_SESSION["bill_company"]="";
					$_SESSION["bill_gender"]=0;
					$_SESSION["bill_title"]="";
					$_SESSION["bill_firstname"]="";
					$_SESSION["bill_lastname"]="";
					$_SESSION["bill_street"]="";
					$_SESSION["bill_number"]="";
					$_SESSION["bill_additional"]="";
					$_SESSION["bill_zip"]="";
					$_SESSION["bill_city"]="";
					$_SESSION["bill_country"]="";
					$_SESSION["bill_country_id"]="";
				}

				$_SESSION["ship_company"]="";
				$_SESSION["ship_gender"]=0;
				$_SESSION["ship_title"]="";
				$_SESSION["ship_firstname"]="";
				$_SESSION["ship_lastname"]="";
				$_SESSION["ship_street"]="";
				$_SESSION["ship_number"]="";
				$_SESSION["ship_additional"]="";
				$_SESSION["ship_zip"]="";
				$_SESSION["ship_city"]="";
				$_SESSION["ship_country"]="";
				$_SESSION["ship_country_id"]="";
			}
		}
	}

	
	//VIEW
	$shop_cart=TRUE;
	echo '<div id="left_mid_right_column">';
/*
	if ($gewerblich)
	{
		if($_SESSION["bill_country"]=="Deutschland")
		{
			echo '<div class="warning">&nbsp;&nbsp;&nbsp; ACHTUNG! &nbsp; Ab dem 01.04.2012 erhöhen sich unsere Inlandsversandkosten für DPD und Nachtversand um jeweils 50Cent!</div>';
		}
	}
*/
	echo '<div id="view"></div>';
	echo '<script type="text/javascript"> view_cart(); </script>';
	echo '</div>';
		
	//ADDITIONAL WINDOW
	echo '<div id="additional_window" style="display:none;">';
	echo '</div>';
	
	//AVAILABILITY WINDOW
	echo '<div id="availability_window" style="display:none;">';
	echo '</div>';
	
	//BILL WINDOW
	echo '<div id="bill_window" style="display:none;">';
	echo '</div>';
	
	//SHIP WINDOW
	echo '<div id="ship_window" style="display:none;">';
	echo '</div>';

	//PAYMENT WINDOW
	echo '<div id="payment_window" style="display:none;">';
	echo '</div>';

	// PayPal Redirect Window
	echo '<div id="paypal_redirect_window" class="warning" style="display:none;">';
	echo '</div>';

	// Herbstaktion Window
	echo '<div id="bulb_set_add_window" style="display:none;">';
	echo '</div>';


	if( $_SESSION["id_user"]==21371 )
	{
		echo '<input type="button" value="ga-test" onclick="ga_track_transaction(1763632);" />';
	}

	include("templates/".TEMPLATE."/footer.php");
?>