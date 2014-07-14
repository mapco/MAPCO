<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	var $customer_addresses = new Object;
	var $Pricelists = new Object
	
	

	function order_update_dialog(orderid, mode)
	{
		//update_view(orderid);
		//var for correlation check
		var shipping_type_id=0;
		
		var $stock = new Object;
		
		wait_dialog_show("Lade Bestelldaten", 0);
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				
			//	alert(data);
//show_status2(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					wait_dialog_show("Lade Lagerbestand", 0.5);
					
					$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderStockGet", order_id:orderid},
					function($data2)
					{
						//show_status2($data2);
						wait_dialog_hide();
						try { $xml2 = $($.parseXML($data2)); } catch ($err) { show_status($err.message); }
						$ack = $xml2.find("Ack");
						if ( $ack.text()!="Success" ) { show_status2($data2);  }
						else
						{
							
							$xml2.find('item').each( function ()
							{
								$stock[$(this).attr('MPN')] = new Object;
								$(this).children.each(function() {
									var stocknr = $(this).attr('nr');
									$stock[$(this).attr('MPN')]['stocknr'] = stocknr;
									var stocktitle = $(this).attr('title');
									$stock[$(this).attr('MPN')]['stocktitle'] = stocktitle;
									var stock_amount = $(this).text();
									$stock[$(this).attr('MPN')]['stock_amount'] = stock_amount;
								});
							});
							
						}
						show_status2(print_r($stock));
						//CREATE ADDRESS DIV
						var html= '';
						//alert($xml.find("customer_shop_id").text());
						if ($xml.find("customer_id").text()!=0)
						{
							html+='<table style="width:750px">';
							html+='<tr>';
							html+='	<td>User-ID: '+$xml.find("customer_id").text()+'</td>';
							if ($xml.find("customer_site_id").text()!=0)
							{
								html+='<td style="backface-color:#fff">';
								html+='<b>'+$xml.find("customer_site_name").text()+' - Kunde</b>';
								html+='</td>';
							}
							html+='	<td>';
							if ($xml.find("customer_name").text()!="")
							{
								html+=$xml.find("customer_name").text();
							}
							else if ($xml.find("customer_lastname").text()!="")
							{
								if ($xml.find("customer_firstname").text()!="")
								{
									html+=' - '+$xml.find("customer_firstname").text()+' '+$xml.find("customer_lastname").text();
								}
								else
								{
									html+=' - '+$xml.find("customer_lastname").text();
								}
							}
							html+='</td>';
							html+='</tr>';
							html+='</table>';
						}
						else
						{
							html+='<table style="width:750px">';
							html+='<tr>';
							html+='	<td><span style="color:red; font-weight:bold;">Kein Kunde ausgewählt. Kunde durch Eingabe einer Adresse neu anlegen oder bestehenden Kunden auswählen: <img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="customer_find_dialog('+orderid+', '+$xml.find("order_site_id").text()+');"/></span></td>';
							html+='</tr>';
							html+='</table>';
						}
	
						if ((orders[orderid]["bill_adr_id"]==orders[orderid]["ship_adr_id"]) || (orders[orderid]["ship_adr_id"]==0) || (orders[orderid]["bill_adr_id"]==0) )
						{
							var secondcolumn=false;
						}
						else
						{
							var secondcolumn=true;
						}
						
						html+='<table style="width:750px">';
						if (secondcolumn)
						{
							html+='<colgroup><col style="width:40%"><col style="width:30%"><col style="width:30%"></colgroup>';
						}
						else
						{
							html+='<colgroup><col style="width:40%"><col style="width:60%"></colgroup>';
						}
						html+='<tr>';
						html+='	<th></th>';
						if (secondcolumn)
						{
							html+='	<th>Rechnungsadresse';
							if (mode=="update")
							{
								html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Rechnungsadresse bearbeiten" title="Rechnungsadresse bearbeiten" onclick="order_address_update('+orderid+', \'bill\');"/>';
								//html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
							}
							html+='</th>';
							
							html+='	<th>Lieferadresse';
							if (mode=="update")
							{
								html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Lieferadresse bearbeiten" title="Lieferadresse bearbeiten" onclick="order_address_update('+orderid+', \'ship\');"/>';
								//html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
							}
	
							html+='</th>';
							
	
						}
						else
						{
							html+='	<th>Rechnungs- & Lieferadresse';
							if (mode=="update")
							{
								html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/page_add.png" alt="Abweichende Lieferadresse eingeben" title="Abweichende Lieferadresse eingeben" onclick="order_address_update('+orderid+', \'ship\');"/>';
								html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Rechnungs- &Lieferadresse bearbeiten" title="Rechnungs- &Lieferadresse bearbeiten" onclick="order_address_update('+orderid+', \'bill\');"/>';
							//if ($xml.find("customer_id").text()==0)	html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
							}
							html+='</th>';
						}
						
						html+='</tr>';
						//COMPAMY
						html+='<tr>';
						html+='	<td>Firma</td><td style="background-color:#fff">'+$xml.find("bill_adr_company").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_company").text()+'</td>';
						html+='</tr>';
						//ADDITIONAL
						html+='<tr>';
						html+='	<td>Adresszusatz <small>(Postnummer)</small></td><td style="background-color:#fff">'+$xml.find("bill_adr_additional").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_additional").text()+'</td>';
						html+='</tr>';
						//NAME
						html+='<tr>';
						html+='	<td>Name</td><td style="background-color:#fff">'+$xml.find("bill_adr_firstname").text()+' '+$xml.find("bill_adr_lastname").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_firstname").text()+' '+$xml.find("ship_adr_lastname").text()+'</td>';
						html+='</tr>';
						//STREET & NUMBER
						html+='<tr>';
						html+='	<td>Straße/Hausnummer <small>(Packst.Nr./Packstation)</small> </td><td style="background-color:#fff">'+$xml.find("bill_adr_street").text()+' '+$xml.find("bill_adr_number").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_street").text()+' '+$xml.find("ship_adr_number").text()+'</td>';
						html+='</tr>';
						//PLZ & CITY
						html+='<tr>';
						html+='	<td>Postleitzahl / Stadt</td><td style="background-color:#fff">'+$xml.find("bill_adr_zip").text()+' '+$xml.find("bill_adr_city").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_zip").text()+' '+$xml.find("ship_adr_city").text()+'</td>';
						html+='</tr>';
						//COUNTRY
						html+='<tr>';
						html+='	<td>Land</td><td style="background-color:#fff">'+$xml.find("bill_adr_country").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_country").text()+'</td>';
						html+='</tr>';
						// TELEFON
						html+='<tr>';
						html+='	<td>Telefon</td><td style="background-color:#fff">'+$xml.find("userphone").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff"></td>';
						html+='</tr>';
						// EMAIL
						html+='<tr>';
						html+='	<td>E-Mail</td><td style="background-color:#fff">'+$xml.find("usermail").text()+'</td>';
						if (secondcolumn) html+='<td style="background-color:#fff"></td>';
						html+='</tr>';
	
						html+='</table>';
						
						//KUNDEN ORDERNR & HINWEIS
						//FIRSTMOD_USER == VERKÄUFER -> FELDER BEARBEITBAR
						
						//if (typeof(Seller[orders[orderid]["firstmod_user"]])!=="undefined" && mode == "update")
						if ( 1 == 1)
						{
							html+='<table style="width:750px">';
							html+='<colgroup><col style="width:40%"><col style="width:54%"><col style="width:6%"></colgroup>';
							html+='<tr>';
							html+='	<th>Kunden Auftragsnummer</th>';
							html+='	<td>'+orders[orderid]["ordernr"]+'</td>';
							html+='	<td><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/edit.png" alt="Kunden Ordernummer bearbeiten" title="Kunden Ordernummer bearbeiten" onclick="order_update_customer_ordernr('+orderid+');"/></td>';
							html+='</tr>';
							html+='<tr>';
							html+='	<th>Kunden Bestellungshinweis</th>';
							html+='	<td>'+orders[orderid]["comment"]+'</td>';
							html+='	<td><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/edit.png" alt="Kunden Bestellungshinweis bearbeiten" title="Kunden Bestellungshinweis bearbeiten" onclick="order_update_customer_comment('+orderid+');"/></td>';
							html+='</tr>';
							html+='</table>';
						}
						/*
						else
						{
							
							if (orders[orderid]["ordernr"].length>0 || orders[orderid]["comment"].length>0 )
							{
								html+='<table style="width:750px">';
								html+='<colgroup><col style="width:40%"><col style="width:60%"></colgroup>';
								if (orders[orderid]["ordernr"].length>0)
								{
									html+='<tr>';
									html+='	<th>Kunden Auftragsnummer</th>';
									html+='	<td>'+orders[orderid]["ordernr"]+'</td>';
									html+='</tr>';
								}
								if ( orders[orderid]["comment"].length>0)
								{
									html+='<tr>';
									html+='	<th>Kunden Bestellungshinweis</th>';
									html+='	<td>'+orders[orderid]["comment"]+'</td>';
									html+='</tr>';
								}
								html+='</table>';
							}
						//}
						*/
						//CHECK if paymenttype is shown or selectable
							// if mode update and paymentstatus not completed -> selectable
						html+='<table>';
						html+='<tr>';
		
						if (mode == "update" && orders[orderid]["Payments_TransactionState"]!="Completed") 
						{
							html+='	<th>';
							html+='		Zahlungsart:';
							html+='	</th>';
							html+='	<td>';
	
							html+='<select id="send_order_dialog_PaymentBox_Payment" size="1" onchange="order_update_set_payment('+orderid+',this.value);">';
							$.each(PaymentTypes, function(paymentypeid, fields)
							{
								if (paymentypeid==0) fields["title"]="Zahlart wählen";
								//SELECTBOX FÜR EBAY-SHOPS
								if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]==2)
								{
									if (fields["PaymentMethod"]!="")
									{
										if (paymentypeid==orders[orderid]["PaymentTypeID"])
										{
											html+='<option value='+paymentypeid+' selected>'+fields["title"]+'</option>';
										}
										else
										{
											html+='<option value='+paymentypeid+'>'+fields["title"]+'</option>';
										}
									}
								}
								else
								//SELECTBOX FÜR ANDERE SHOPS
								{
									if ( paymentypeid !=  5 && paymentypeid != 6 )
									{
										if (paymentypeid==orders[orderid]["PaymentTypeID"])
										{
											html+='<option value='+paymentypeid+' selected>'+fields["title"]+'</option>';
										}
										else
										{
											html+='<option value='+paymentypeid+'>'+fields["title"]+'</option>';
										}
									}
								}
								
							});
							html+='</select>';
							
							html+=' </td>';
						}
						else
						{
							html+='	<th>';
							html+='		Zahlungsart:';
							html+='	</th>';
							html+='	<td>';
							if (orders[orderid]["PaymentTypeID"]==0)
							{
								html+='		<b>Keine Zahlart ausgewählt</b>';
							}
							else
							{
								html+='		<b>'+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'</b>';
							}
							html+=' </td>';
						}
						html+='	<th>';
						html+='		Mehrwertsteuer:';
						html+='	</th>';
						html+='	<td>';
						html+='		<span id="VATinfo" style="cursor:pointer" onclick="change_orderVAT_dialog('+orderid+');"><b>'+orders[orderid]["VAT"]+' %<b></span>';
						html+='	</td>';
	
						html+='</tr>';
						html+='</table>';
	
	
						html+='<table style="width:750px">';
						html+='<tr>';
						html+='	<th>MPN</th>';
						html+='	<th>ArtBez</th>';
						html+='	<th>Menge</th>';
						if ( orders[orderid]['shop_id'] == 22 )
						{
							html+= ' <th>Best. Lager 41</th>';
						}
						html+='	<th>einz.VK</th>';
						html+='	<th>ges. VK';
						html+='</th>';
						if(<?php echo $_SESSION["id_user"];?>==21371)
							html+='	<th>COS Marge</th>';
						html+='<th>';
						if (mode=="update")
						{
							//PARENTORDERID festlegen
							var parentorderid=0;
							var $orders = $xml.find("orders");
							$orders.find("order").each(
							function()
							{
							//	alert ($(this).find("combined_with").text());
								if (parentorderid==0)
								{
									if (1*$(this).find("combined_with").text()>0)
									{
										parentorderid=$(this).find("combined_with").text();							
									}
									else
									{
										parentorderid=$(this).find("orderid").text();
									}
								}
							});
							
							//html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Bestellposition hinzufügen" title="Bestellposition hinzufügen" onclick="add_orderpositions('+parentorderid+', '+$xml.find("shop_id").text()+');"/>';
							html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Bestellposition hinzufügen" title="Bestellposition hinzufügen" onclick="add_orderpositions('+parentorderid+', '+orders[orderid]["shop_id"]+');"/>';
						}
						html+='</th>';
						html+='</tr>';
						
						$xml.find("order").each(
						function()
						{
							shipping_type_id=$(this).find("shipping_type_id").text();
							var order_id=$(this).find("orderid").text();
						
							$xml.find("Item").each(
							function()
							{
								if (order_id == $(this).find("OrderItemOrderID").text())
								{
									if ($(this).find("OrderItemChecked").text()==1) var bgcolor='background-color:#dfd';
									else if ($(this).find("OrderItemChecked").text()==2) var bgcolor='background-color:#fdd';
									else var bgcolor='background-color:#fff';
									html+='<tr style="'+bgcolor+'">';
									html+='	<td>'+$(this).find("OrderItemMPN").text()+'</td>';
	
									html+='	<td>'+$(this).find("OrderItemDesc").text()+'</td>';
									html+='	<td>'+$(this).find("OrderItemAmount").text()+'</td>';
									if ( orders[orderid]['shop_id'] == 22 )
									{
										html+= ' <td>'+$(this).find("OrderItemStockAmount").text()+'</td>';
									}
	
									html+='	<td>EUR '+$(this).find("orderItemPriceGross").text()+'</td>';
									html+='	<td>EUR '+$(this).find("orderItemTotalGross").text()+'</td>';
									if(<?php echo $_SESSION["id_user"];?>==21371)
										html+='	<td>' + $(this).find("orderItemPriceCOS_M").text() + ' %</td>';
									html+='	<td>';
									if (mode=="update")
									{
										html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Bestellposition bearbeiten" title="Bestellposition bearbeiten" onclick="change_orderpositions('+orderid+', '+$(this).find("OrderItemID").text()+');"/>';
									}
									if (mode=="return")
									{
										html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Rückgabe / Umtausch bearbeiten" title="Rückgabe / Umtausch bearbeiten" onclick="return_dialog('+orderid+', '+$(this).find("OrderItemID").text()+');"/>';
									}
									
									html+='	</td>';
									html+='</tr>';
								}
							});
							html+='<tr>';
							html+='	<td></td>';
							
			
							if ($(this).find("shipping_type_id").text()==0)
							{
								html+='	<td><b>KEINE VERSANDART AUSGEWÄHLT!</b></td>';
							}
							else
							{
								html+='	<td><b>Versand per '+ShipmentTypes[$(this).find("shipping_type_id").text()]["title"]+'</b></td>'
							}
							html+='	<td></td>';
							html+='	<td></td>';
							if ( orders[orderid]['shop_id'] == 22 )
							{
								html+='	<td></td>';
							}
							html+='	<td>EUR '+$(this).find("shipping_costs").text()+'</td>';
							html+='	<td>';
							if (mode=="update")
							{
								html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Versand bearbeiten" title="Versand bearbeiten" onclick="change_ordershipping('+order_id+');"/>';
							}
							html+='	</td>';
							if(<?php echo $_SESSION["id_user"];?>==21371)
								html+='	<td></td>';
							html+='</tr>';
	
						});
	
						html+='<tr style="background-color:#fff">';
						html+='	<td></td>';
						html+='	<td><b>Gesamt</b></td>';
						html+='	<td>'+$xml.find("orderItemCount").text()+'</td>';
						if ( orders[orderid]['shop_id'] == 22 )
						{
							html+='	<td></td>';
						}
						html+='	<td></td>';
						html+='	<td><b>EUR '+$xml.find("orderTotalGross").text()+'</b></td>';
						html+='	<td>';
						html+='	</td>';
						if(<?php echo $_SESSION["id_user"];?>==21371)
								html+='	<td></td>';
						html+='</tr>';
						
						html+='</table>';
						
							//DIALOG ZUM BESTELLUNGVERSENDEN
						if (mode=="IDIMS")
						{
							if ($("#order_send_dialog").length==0)
							{
								$("body").append('<div id="order_send_dialog" style="display:none"></div>');
							}
							else
							{
								$("#order_send_dialog").html('');
							}
							
							$("#order_send_dialog").html(html);
							
							if (!$("#order_send_dialog").is(":visible"))
							{
								$("#order_send_dialog").dialog
									({	buttons:
										[
											{ id: "order_send_dialog_order_send_btn",  text: "Bestellung senden", click: function() { order_send(orderid);} },
											{ text: "Beenden", click: function() { $(this).dialog("close"); } }
										],
										closeText:"Fenster schließen",
										closeOnEscape: true,
										//hide: { effect: 'drop', direction: "up" },
										modal:true,
										resizable:false,
										//show: { effect: 'drop', direction: "up" },
										title:"Bestellung ans IDIMS senden",
										width:800,
										beforeClose: function() {}
									});	
							}
						}
						if (mode=="view")
						{
							if ($("#order_view_dialog").length==0)
							{
								$("body").append('<div id="order_view_dialog" style="display:none"></div>');
							}
							else
							{
								$("#order_view_dialog").html('');
							}
							
							//DIALOG TITLE
							if(orders[orderid]["ordertype_id"]==4)
							{
								var dialog_title = "Umtausch-Ansicht";
							}
							else
							{
								var dialog_title = "Bestellungsansicht";
							}
	
							
							$("#order_view_dialog").html(html);
							
							if (!$("#order_view_dialog").is(":visible"))
							{
	
								$("#order_view_dialog").dialog
									({	buttons:
										[
											{ text: "Schließen", click: function() { $(this).dialog("close"); } }
										],
										closeText:"Fenster schließen",
										closeOnEscape: true,
										//hide: { effect: 'drop', direction: "up" },
										modal:true,
										resizable:false,
										//show: { effect: 'drop', direction: "up" },
										title:dialog_title,
										width:800,
										beforeClose: function() {}
										
									});	
							}
						}
	
						//DIALOG ZUR BEARBEITUNG DER BESTELLUNG
						if (mode=="update")
						{
							if ($("#order_update_dialogbox").length==0)
							{
								$("body").append('<div id="order_update_dialogbox" style="display:none"></div>');
							}
							else
							{
								$("#order_update_dialogbox").html('');
							}
	
							$("#order_update_dialogbox").html(html);
							
							//DIALOG TITLE
							if(orders[orderid]["ordertype_id"]==4)
							{
								var dialog_title = "Umtausch bearbeiten";
							}
							else
							{
								var dialog_title = "Bestellung bearbeiten";
							}
							
							
							if (!$("#order_update_dialogbox").is(":visible"))
							{
	
							$("#order_update_dialogbox").dialog
								({	buttons:
									[
									//{ text: "Bestellung senden", click: function() { order_send(orderid);} },
										{ text: "Beenden", click: function() { $(this).dialog("close"); } }
									],
									closeOnEscape: true,
									closeText:"Fenster schließen",
									modal:true,
									resizable:false,
									title:dialog_title,
									width:800,
									beforeClose: function() 
									{ 
									/*
										//CHECK FOR PAYMENT METHOD
											//BEI UMTAUSCH MUSS KEINE ZAHLART DEFINIERT SEIN
										if (mode=="update" && orders[orderid]["ordertype_id"]!=4 && $("#send_order_dialog_PaymentBox_Payment").val()==0) 
										{
											order_update_dialog(orderid, mode); 
											$("#send_order_dialog_PaymentBox_Payment").focus(); 
											alert("Es muss eine Zahlart ausgewählt werden!");
											return;
										} 
										if (mode=="update" && orders[orderid]["shipping_type_id"]==0) 
										{
											order_update_dialog(orderid, mode); 
											change_ordershipping(orderid);
											alert("Es muss eine Versandart ausgewählt werden!");
											return;
										} 
	*/
										// CHECK FOR CORRELATION Payment Nachnahme (&& Shipment Nachnahme)
										ShipmentTypes
										if (mode=="update" && $("#send_order_dialog_PaymentBox_Payment").val()==3 && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]!=3) 
										{
											alert("Bitte den Versand auf Nachnahme und die Versandkosten setzen!");
											order_update_dialog(orderid, mode);
											change_ordershipping(orderid);
											return;
										}
										// CHECK FOR CORRELATION Shipment Nachnahme (&& Payment Nachnahme)
										if (mode=="update" && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]==3 && $("#send_order_dialog_PaymentBox_Payment").val()!=3) 
										{
											alert("Bitte den Versand auf Nachnahme und die Versandkosten setzen!");
											order_update_dialog(orderid, mode);
											change_ordershipping(orderid);
											return;
										}
	
									}
									
								});	
								
						}
							//DIALOG ZUR BEARBEITUNG DER BESTELLUNG
							if (mode=="return")
							{
								$("#send_order_dialog").dialog
									({	buttons:
										[
										//{ text: "Bestellung senden", click: function() { order_send(orderid);} },
											{ text: "Beenden", click: function() { $(this).dialog("close"); } }
										],
										closeText:"Fenster schließen",
										hide: { effect: 'drop', direction: "up" },
										modal:true,
										resizable:false,
										show: { effect: 'drop', direction: "up" },
										title:"Rückgabe / Umtausch bearbeiten",
										width:800
									});	
							}
	
						}
					});
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}

	function order_add()
	{

		//BUILD DIV FOR DIALOG
			//check if div exists
		if ($("#order_add_dialog").length == 0)
		{
			$("body").append('<div id="order_add_dialog" style="display:none">');
		}
		
		var html='';
		html+='<table>';
		html+='<tr>';
		html+=' <td>Für welchen Shop soll die neue Bestellung angelegt werden:</td>';
		html+='	<td>';
		html+='		<select id="order_add_site" size="1" onchange="set_order_type(this.value);">';
		html+='			<option value=0>Bitte Shop wählen</option>';
		for (var i = 0; i<UserSites.length; i++)
		{
			//PARENTSHOP
			for (var shop_id in Shop_Shops) 
			{
				if (Shop_Shops[shop_id]["active"]==1 && Shop_Shops[shop_id]["site_id"]==UserSites[i])
				{
					var parentshop_id = shop_id;
					html+='	<option value='+shop_id+'>'+Shop_Shops[shop_id]["title"]+'</option>';
				}
			}
			//CHILDSHOPS
			for (var shop_id in Shop_Shops) 
			{
				if (Shop_Shops[shop_id]["active"]==1 && Shop_Shops[shop_id]["parent_shop_id"]==parentshop_id)
				{
					html+='	<option value='+shop_id+'>'+Shop_Shops[shop_id]["title"]+'</option>';
				}
			}
		}
		html+='		</select>';
		html+='	</td>';
		html+='</tr><tr>';
		html+=' <td>Bitte Format festlegen:</td>';
		html+='	<td>';
		html+='		<select id="order_add_ordertype" size="1">';
		//html+='			<option value=0>Bitte wählen</option>';
		for (var ordertype_id in OrderTypes) 
		{
			if (ordertype_id!=4 && ordertype_id!=5 && ordertype_id!=6)
			{
				if (ordertype_id==2)
				{
					html+='	<option value='+ordertype_id+' selected>'+OrderTypes[ordertype_id]["title"]+'</option>';
				}
				else
				{
					html+='	<option value='+ordertype_id+'>'+OrderTypes[ordertype_id]["title"]+'</option>';
				}
			}
		}
		html+='		</select>';
		html+='	</td>';
		html+='</tr><tr>';
		html+=' <td>Bitte Währung festlegen:</td>';
		html+='	<td>';
		html+='		<select id="order_add_currency" size="1">';
		for (var currency in Currencies) 
		{
			if (currency=="EUR")
			{
				html+='	<option value='+currency+' selected>'+currency+'</option>';
			}
			else
			{
				html+='	<option value='+currency+'>'+currency+'</option>';
			}
		}
		html+='		</select>';

		html+='	</td>';
		
		html+='</tr>';
		html+='</table>';
		

		$("#order_add_dialog").html(html);
	
		
		
		//$("#order_add_shop").val(FILTER_Platform);
		//$("order_add_ordertype").val();
		
		
		$("#order_add_dialog").dialog
			({	buttons:
				[
					{ text: "Bestellung anlegen", click: function() { 
						if ($("#order_add_site").val()==0)
						{
							$("#order_add_site").focus();
							msg_box("Bitte zuerst einen Shop wählen!");	
						}
						else if($("#order_add_ordertype").val()==0)
						{
							$("#order_add_ordertype").focus();
							msg_box("Bitte zuerst einen Bestellungstyp wählen!");
						}
						else
						{
						
						var shop_id = $("#order_add_site").val();	
						var currency = $("#order_add_currency").val();
							
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderAdd", mode:"manual", Currency_Code:currency, shop_id:shop_id, ordertype_id:$("#order_add_ordertype").val(), status_id:1},
								function($data)
								{
									//alert ($data);
									wait_dialog_hide();
									//alert($data);
									try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
									$ack = $xml.find("Ack");
									if ( $ack.text()!="Success" ) { show_status2($data); return; }

									
										var orderid=$xml.find("id_order").text();
										//alert(orderid);
										if (orderid!=0 && orderid!="")
										{
											$("#order_add_dialog").dialog("close");
											view_box(orderid, "order_update_dialog");
										}

										else
										{
											msg_box("FEHLER BEIM ANLEGEN EINER NEUEN BESTELLUNG");
											
										}
								}
							);
						}
					} },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Beenden",
				//hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				//show: { effect: 'drop', direction: "up" },
				title:"Neue Bestellung anlegen",
				width:800
			});	

	}
	function create_address_select_string($addresses, $adr_id)
	{
		var adr_string = '';
		if ($addresses[$adr_id]["company"] != "")
		{
			adr_string += $addresses[$adr_id]["company"];
		}
		if ($addresses[$adr_id]["firstname"] != "")
		{
			if (adr_string != "") adr_string += ", ";
			adr_string += $addresses[$adr_id]["firstname"];
		}
		if ($addresses[$adr_id]["lastname"] != "")
		{
			if (adr_string != "") adr_string += " ";
			adr_string += $addresses[$adr_id]["lastname"];
		}
		if ($addresses[$adr_id]["additional"] != "")
		{
			if (adr_string != "") adr_string += ", ";
			adr_string += $addresses[$adr_id]["additional"];
		}
		if ($addresses[$adr_id]["street"] != "")
		{	
			if (adr_string != "") adr_string += ", ";
			adr_string += $addresses[$adr_id]["street"];
		}
		if ($addresses[$adr_id]["number"] != "")
		{
			if (adr_string != "") adr_string += " ";
			adr_string += $addresses[$adr_id]["number"];	
		}
		if ($addresses[$adr_id]["zip"] != "")
		{	
			if (adr_string != "") adr_string += ", ";
			adr_string += $addresses[$adr_id]["zip"];
		}
		if ($addresses[$adr_id]["city"] != "")
		{
			if (adr_string != "") adr_string += " ";
			adr_string += $addresses[$adr_id]["city"];
		}
		adr_string += ", "+$addresses[$adr_id]["country"];
		
		return adr_string;
	}

	
	function order_address_update(orderid, addresstype)
	{
		if ($("#order_address_update_dialog").length == 0)
		{
			$("body").append('<div id="order_address_update_dialog" style="display:none">');
		}

		$customer_addresses = new Object;

		//GET ADDRESSES FROM USER
		var $postfields = new Object();
		$postfields['API'] 				= "cms";
		$postfields['APIRequest'] 		= "TableDataSelect";
		$postfields['table']			= "shop_bill_adr";
		$postfields['db'] 				= "dbshop";
		$postfields['where']			= "WHERE user_id = "+orders[orderid]["customer_id"];

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", $postfields,
		function($data)
		{

			try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
			var $Ack = $xml.find("Ack").text();
			if ($Ack!="Success") {show_status2($data); return;}

			$xml.find("shop_bill_adr").each(function ()
			{
				var $adr_id = $(this).find("adr_id").text();
				$customer_addresses[$adr_id] = new Object;
				$(this).children().each(
					function()
					{
						var $tagname=this.tagName;
						$customer_addresses[$adr_id][$tagname]=$(this).text();
					}
												
				);
			});

			var $html = '';
	
			$html += '<table>';
			$html += '<input type="hidden" id="order_address_update_type" />';
			//ADRESSAUSWAHL
			$html += '<tr>';
			$html += '	<td colspan="2">Kundenadressenauswahl<br />';
			$html += '	<select id="order_address_update_address_select" size="1" onchange="order_address_update_set_address(this.value);">';
			$html += '		<option value=0>Bitte Kundenadresse auswählen</option>';
			//GET STANDART ADDRESS
			
			var $has_standard_address = false;
			var $address_count = 0;
			for (var $adrid in $customer_addresses)
			{
				$address_count ++;
				if ( ($customer_addresses[$adrid]["standard"] == 1 && addresstype == "bill") || ($customer_addresses[$adrid]["standard_ship_adr"] == 1 && addresstype == "ship") )
				{
					$has_standard_address = true;
					var $adr_string = create_address_select_string($customer_addresses, $adrid);
					
					if ( ( addresstype == "bill" && orders[orderid]["bill_adr_id"] == $adrid) || (addresstype == "ship" && orders[orderid]["ship_adr_id"] == $adrid) )
					{
						var $selected = 'selected';
					}
					else
					{
						var $selected = '';
					}
					if ( $customer_addresses[$adrid]["standard"] == 1 && addresstype == "bill" )
					{
						$html += '		<optgroup label="Standard Rechnungsadresse">';
					}
					if ( $customer_addresses[$adrid]["standard_ship_adr"] == 1 && addresstype == "ship" )
					{
						$html += '		<optgroup label="Standard Lieferadresse">';
					}
					$html += '			<option value='+$adrid+' '+$selected+'>'+$adr_string+'</option>';
					$html += '		</optgroup>';
				}
			}
			
			//LEERZEILE ZWISCHEN STANDARD UND OTHER
			if ( $has_standard_address )
			{
				$html += '		<option value=-1></option>';
			}
			if ( $has_standard_address && $address_count > 1)
			{
				$html += '		<optgroup label="Kundenadressen">';
			}
			//GET OTHER ADDRESSES
			for (var $adrid in $customer_addresses)
			{
				if ( (addresstype == "bill" && $customer_addresses[$adrid]["standard"] == 0) || (addresstype == "ship" && $customer_addresses[$adrid]["standard_ship_adr"] == 0) )
				{
					var $adr_string = create_address_select_string($customer_addresses, $adrid);
					if ( ( addresstype == "bill" && orders[orderid]["bill_adr_id"] == $adrid) || (addresstype == "ship" && orders[orderid]["ship_adr_id"] == $adrid) )
					{
						$html += '		<option value='+$adrid+' selected>'+$adr_string+'</option>';
					}
					else
					{
						$html += '		<option value='+$adrid+'>'+$adr_string+'</option>';
					}
				}
			}
			if ( $has_standard_address && $customer_addresses.length > 1)
			{
				$html += '		</optgroup>';
			}
			$html += '	</select></td>';
			$html += '</tr>';
			
			$html += '<tr>';
			$html += '	<td colspan="2">Firma<br />';
			$html += '	<input type="text" size="50" id="order_address_update_company" /></td>';
			$html += '</tr>';
			$html += '<tr>';
			$html += '	<td colspan="2">Adresszusatz / Postnummer<br />';
			$html += '	<input type="text" size="50" id="order_address_update_additional" /></td>';
			$html += '</tr>';
			$html += '<tr>';
			$html += '	<td>Vorname<br />';
			$html += '	<input type="text" size="20" id="order_address_update_firstname" /></td>';
			$html += '	<td>Nachname<br />';
			$html += '	<input type="text" size="20" id="order_address_update_lastname" /></td>';
			$html += '</tr>';
			$html += '<tr>';
			$html += '	<td>Straße / Packstation<br />';
			$html += '	<input type="text" size="20" id="order_address_update_street" /></td>';
			$html += '	<td>Nummer / Packstat.Nr.<br />';
			$html += '	<input type="text" size="5" id="order_address_update_number" /></td>';
			$html += '</tr>';
			$html += '<tr>';
			$html += '	<td>Postleitzahl<br />';
			$html += '	<input type="text" size="10" id="order_address_update_zip" /></td>';
			$html += '	<td>Stadt<br />';
			$html += '	<input type="text" size="20" id="order_address_update_city" /></td>';
			$html += '</tr>';
			$html += '<tr>';
			$html += '	<td colspan="2">Land<br />';
			$html += '	<select id="order_address_update_country_code" size="1">';
			for (var $country_id in  Countries)
			{
				$html += '<option value="'+Countries[$country_id]["country_code"]+'">'+Countries[$country_id]["country"]+'</option>';
			}
			$html += '	</select>';
			$html += '	</td>';
			$html += '</tr><tr>';
			$html += '	<td colspan="2">Telefon<br /><input type="text" size="50" id="order_address_update_userphone" /></td>';
			$html += '</tr><tr>';
			$html += '	<td colspan="2">E-Mail<br /><input type="text" size="50" id="order_address_update_usermail" /></td>';
			$html += '</tr>';
			$html += '<input type="hidden" id="order_address_update_customer_id" />';
			$html += '<input type="hidden" id="order_address_update_site_id" />';
			$html += '</table>';
	
			$("#order_address_update_dialog").html($html);
	
			//LÄNDERLISTE SORTIEREN
			list_sort("order_address_update_country_code");
			
			if (addresstype == "bill")
			{
				var $adr_id = orders[orderid]["bill_adr_id"];
			}
			if (addresstype == "ship")
			{
				var $adr_id = orders[orderid]["ship_adr_id"];
			}
	
			if ( $adr_id == 0 )
			{
				$("#order_address_update_type").val(addresstype);
				$("#order_address_update_company").val("");
				$("#order_address_update_firstname").val("");
				$("#order_address_update_lastname").val("");
				$("#order_address_update_street").val("");
				$("#order_address_update_number").val("");
				$("#order_address_update_additional").val("");
				$("#order_address_update_zip").val("");
				$("#order_address_update_city").val("");
				$("#order_address_update_country_code").val("DE");
			}
			else
			{
				$("#order_address_update_type").val(addresstype);
				$("#order_address_update_company").val($customer_addresses[$adr_id]["company"]);
				$("#order_address_update_firstname").val($customer_addresses[$adr_id]["firstname"]);
				$("#order_address_update_lastname").val($customer_addresses[$adr_id]["lastname"]);
				$("#order_address_update_street").val($customer_addresses[$adr_id]["street"]);
				$("#order_address_update_number").val($customer_addresses[$adr_id]["number"]);
				$("#order_address_update_additional").val($customer_addresses[$adr_id]["additional"]);
				$("#order_address_update_zip").val($customer_addresses[$adr_id]["zip"]);
				$("#order_address_update_city").val($customer_addresses[$adr_id]["city"]);
				$("#order_address_update_country_code").val(Countries[$customer_addresses[$adr_id]["country_id"]]["country_code"]);
			}
			//GET PHONE & MAIL
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
				function(data)
				{
					wait_dialog_hide();
					//show_status2(data);
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{

	
						$("#order_address_update_usermail").val($xml.find("usermail").text());
						$("#order_address_update_userphone").val($xml.find("userphone").text());
						
						$("#order_address_update_customer_id").val($xml.find("customer_id").text());
						$("#order_address_update_site_id").val($xml.find("order_site_id").text());
						//$("#order_address_update_shop_id").val($xml.find("shop_id").text());
	
	
						$("#order_address_update_dialog").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() { order_address_update_save(orderid);} },
								{ text: "Beenden", click: function() { $(this).dialog("close");} }
							],
							closeText:"Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Adresse bearbeiten",
							width:600
						});		
			
					}
					else
					{
						show_status2(data);
					}
				}
			);
		});
	}
	function order_address_update_set_address($adr_id)
	{
		$("#order_address_update_company").val($customer_addresses[$adr_id]["company"]);
		$("#order_address_update_firstname").val($customer_addresses[$adr_id]["firstname"]);
		$("#order_address_update_lastname").val($customer_addresses[$adr_id]["lastname"]);
		$("#order_address_update_street").val($customer_addresses[$adr_id]["street"]);
		$("#order_address_update_number").val($customer_addresses[$adr_id]["number"]);
		$("#order_address_update_additional").val($customer_addresses[$adr_id]["additional"]);
		$("#order_address_update_zip").val($customer_addresses[$adr_id]["zip"]);
		$("#order_address_update_city").val($customer_addresses[$adr_id]["city"]);
		$("#order_address_update_country_code").val(Countries[$customer_addresses[$adr_id]["country_id"]]["country_code"]);
	}
	function order_address_update_save(orderid)
	{
		//CHECK FOR CUSTOMER ID 
		// IF 0 -> CREATE NEW CMS_USER
		if ($("#order_address_update_customer_id").val()==0)
		{
			var usermail=$("#order_address_update_usermail").val();
			var firstname=$("#order_address_update_firstname").val();
			var lastname=$("#order_address_update_lastname").val();
			//var shop_id=$("#order_address_update_shop_id").val();
			var site_id = $("#order_address_update_site_id").val();

			//if (usermail=="") {alert("Bei Neukunden bitte eine E-Mailadresse angeben"); return;}
			if (lastname=="") {alert("Bei Neukunden bitte einen Nachnamen angeben"); return;} 
						
			$.post("<?php echo PATH;?>soa2/", { API: "cms", APIRequest: "CMS_UserCreate", usermail:usermail, firstname:firstname, lastname:lastname, site_id:site_id},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#order_address_update_customer_id").val($xml.find("customer_id").text());
					order_address_update_save2(orderid);
					return;
				}
				else
				{
					show_status2(data);
				}
			});
		}
		else
		{
			order_address_update_save2(orderid);
		}
	}
	function order_address_update_save2(orderid)
	{
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderAddressUpdate", 
			OrderID:orderid,
			addresstype:$("#order_address_update_type").val(),
			customer_id:$("#order_address_update_customer_id").val(),
		//	shop_id:$("#order_address_update_shop_id").val(),
			company:$("#order_address_update_company").val(),
			firstname:$("#order_address_update_firstname").val(),
			lastname:$("#order_address_update_lastname").val(),
			street:$("#order_address_update_street").val(),
			number:$("#order_address_update_number").val(),
			additional:$("#order_address_update_additional").val(),
			zip:$("#order_address_update_zip").val(),
			city:$("#order_address_update_city").val(),
			country_code:$("#order_address_update_country_code").val(),
			
			usermail:$("#order_address_update_usermail").val(),
			userphone:$("#order_address_update_userphone").val()
						
			 },
			function(data)
			{
				//show_status2(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					//NETTOPREIS KORREKTUR -> NUR FÜR MAPCO EBAY (DE & UK) && AP Ebay & Amazon
					if (orders[orderid]["shop_id"] == 3 || orders[orderid]["shop_id"] == 5 || orders[orderid]["shop_id"] == 4 || orders[orderid]["shop_id"] == 6)
					{
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderNetPriceCorrection", orderid:orderid},
						function($data)
						{
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") 
							{
								show_status2($data);
							}
							else
							{
								$("#order_address_update_dialog").dialog("close");
			

								//order_update_dialog(orderid, "update");
								update_view(orderid, "order_update_dialog");
							}
						});
					}
					//BRUTTOPREISKORREKTUR
					else
					{
						if (orders[orderid]["VAT"]!=0)
						{
							//GET ACTUAL VAT for country
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "CountriesGet"},
							function($data)
							{
								try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
								var $Ack = $xml.find("Ack").text();
								if ($Ack!="Success") {show_status2($data2); return;}
								
								var $VAT = 0;
								$xml.find("shop_countries").each( function()
								{
									if ($(this).find("country_code").text()==$("#order_address_update_country_code").val())
									{
										$VAT = $(this).find("VAT").text();
									}
								});
								
								if ($VAT == 0) $VAT = 19;
							
								$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, VAT:$VAT },
								function($data2)
								{
									try { $xml = $($.parseXML($data2));	} catch (err) {	show_status2(err.message); return;}
									var $Ack = $xml.find("Ack").text();
									if ($Ack!="Success") {show_status2($data2); return;}
						
									$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "OrderVATUpdate", order_id:orderid},
									function($data3)
									{
										wait_dialog_hide();
										//alert(data);
										try { $xml = $($.parseXML($data3));	} catch (err) {	show_status2(err.message); return;}
										var $Ack = $xml.find("Ack").text();
										if ($Ack!="Success") {show_status2($data3); return;}
										$("#order_address_update_dialog").dialog("close");
										update_view(orderid, "order_update_dialog");
									});
								});
							});
						}
						else
						{
							$("#order_address_update_dialog").dialog("close");
							update_view(orderid, "order_update_dialog");
						}
					}
				}
				else
				{
					show_status2(data);
				}
			}
		);
		
	}
	
	function order_update_customer_ordernr(orderid)
	{
		// CREATE DIALOG
		if ($("#order_update_customer_ordernr_dialog").length==0)
		{
			$("body").append('<div id="order_update_customer_ordernr_dialog" style="display:none"></div>');
		}

		var html='';
		html+='<table>';
		html+='<tr>';
		html+='	<td><input type="text" id="order_update_customer_ordernr_dialog_ordernr" size="50" value="'+orders[orderid]["ordernr"]+'" /></td>';
		html+='</tr>';
		html+='</table>';
		
		$("#order_update_customer_ordernr_dialog").html(html);
		$("#order_update_customer_ordernr_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() 
						{ 
							
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, ordernr:$("#order_update_customer_ordernr_dialog_ordernr").val()},
							function($data)
							{
								//alert ($data);
								wait_dialog_hide();
								try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
								$ack = $xml.find("Ack");
								if ( $ack.text()!="Success" ) { show_status2($data); return; }

								$("#order_update_customer_ordernr_dialog").dialog("close");
								//view_box(orderid, "order_update_dialog");
								//order_update_dialog(orderid, "update");
								update_view(orderid, "order_update_dialog");
							});
						}
					 },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Beenden",
				modal:true,
				resizable:false,
				title:"Kunden OrderNr bearbeiten",
				width:400
			});	
	}
	
	function order_update_customer_comment(orderid)
	{
		// CREATE DIALOG
		if ($("#order_update_customer_comment_dialog").length==0)
		{
			$("body").append('<div id="order_update_customer_comment_dialog" style="display:none"></div>');
		}

		var html='';
		html+='<table>';
		html+='<tr>';
		html+='	<td><textarea id="order_update_customer_comment_dialog_comment" cols="50" rows="5">'+orders[orderid]["comment"]+'</textarea></td>';
		html+='</tr>';
		html+='</table>';
		
		$("#order_update_customer_comment_dialog").html(html);
		$("#order_update_customer_comment_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() 
						{ 
							
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, comment:$("#order_update_customer_comment_dialog_comment").val()},
								function($data)
								{
									//alert ($data);
									wait_dialog_hide();
									try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
									$ack = $xml.find("Ack");
									if ( $ack.text()!="Success" ) { show_status2($data); return; }
									if (orderid!=0 && orderid!="")
									{
										$("#order_update_customer_comment_dialog").dialog("close");
										//view_box(orderid, "order_update_dialog");
										//order_update_dialog(orderid, "update");
										update_view(orderid, "order_update_dialog");
									}

								}
							);
						}
					 },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Beenden",
				modal:true,
				resizable:false,
				title:"Kunden Bestellhinweis bearbeiten",
				width:400
			});	
	}
	
	function order_update_set_payment(orderid, payment_type_id)
	{
		wait_dialog_show();
		
		//SUCHE ALLE KOMBINIERTEN ORDERS 
		$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderDetailGet_neu",  OrderID:orderid},
			function($data)
			{
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				var $orders=$xml.find("orders");
				$orders.find("order").each(
				function ()
				{
					//alert($(this).find("foreign_OrderID").text());
					if ($(this).find("orderid").text()!=0 && $(this).find("orderid").text()!=0)
					{
							var shipping_detail = ShipmentTypes[$(this).find("shipping_type_id").text()]["title"];
							if (payment_type_id == 0)
							{
								shipping_detail+=", Keine Zahlart gewählt";
							}
							else
							{
								shipping_detail+=", "+PaymentTypes[payment_type_id]["title"];
							}
						
						$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderUpdate", 
							SELECTOR_id_order:$(this).find("orderid").text(),
							payments_type_id:payment_type_id,
							Payments_TransactionState:"Pending",
							Payments_TransactionStateDate: Math.round(+new Date()/1000),
							shipping_details: shipping_detail },
						function(data)
						{
							var $xml=$($.parseXML(data));
							var Ack = $xml.find("Ack").text();
							if (Ack=="Success") 
							{
							}
							else
							{
								show_status2(data);
								order_update_dialog(orderid, "update");
								return;
							}
						});
					
					}
					
				});
				
				update_view(orderid, "order_update_dialog");
			}
		);
		
	}
	


	function customer_find_dialog($orderid, $ordersite_id)
	{
		
		if ($("#customer_find_dialog").length == 0)
		{
			$("body").append('<div id="customer_find_dialog" style="display:none">');
		}
	
		var $html = '';
		$html += '<table style="width:100%">';
		$html += '<tr>';
		$html +=	'<th>Suche nach</th>';
		$html +=	'<th>Suchetext</th>';
		$html +=	'<th></th>';
		$html += '</tr><tr>';
		$html +=	'<td><select id="customer_find_dialog_searchfor" size="1">';
		$html +=		'<option value="Kundennummer">Mapco Kundennummer</option>';
		$html +=		'<option value="BM_UserID">Backend UserID</option>';
		$html +=		'<option value="Mail">E-Mail</option>';
		$html +=		'<option value="Adresse" selected>Name / Adresse</option>';
//		$html +=		'<option value="Freitext">Freitextsuche</option>';
		$html +=	'</select></td>';
		$html += 	'<td><input type="text" id="customer_find_dialog_searchtext" size="60" /></td>';
		$html += 	'<td><input type="button" id="customer_find_dialog_searchbtn" value="Suchen" onclick="find_customer('+$ordersite_id+', '+$orderid+');" /></td>';
		$html += '</tr>';
		$html += '</table>';
		
		//DIV FOR RESULTS
		$html += '<div id="customer_find_dialog_results" style="width:100%; height:500px; overflow-y:auto"></div>';

		$("#customer_find_dialog").html($html);


		//ADD EVENTHANDLER
		$("#customer_find_dialog_searchtext").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				find_customer($ordersite_id, $orderid);
			}
		});


		$("#customer_find_dialog").dialog
			({	buttons:
				[
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kunde suchen",
				width:1200
			});	
	}
	function find_customer($ordersite_id, $orderid)
	{
		//var site_id=$("#find_customer_dialog_site_id").val();


		$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSearch", mode:"customer_search", search_for:$("#customer_find_dialog_searchfor").val(), ordersite_id:$ordersite_id, qry_string:$("#customer_find_dialog_searchtext").val()},
		function($data)
		{
			//show_status2($data);

			try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
			var $Ack = $xml.find("Ack").text();
			if ($Ack!="Success") {show_status2($data2); return;}
			
				
			var qry_string=$("#find_customer_qry_string").val();
			var $html='';
			
			$html += '<table style="width:100%">';
			
			var $shop_data = $xml.find("shop_data");
			
			var counter = 0;
			$shop_data.find("userdata").each(
			function()
			{
				counter ++;

				var $user_id=$(this).find("id_user").text();
				var $user_username=$(this).find("user_username").text();
				var $user_name=$(this).find("user_name").text();
				var $user_mail=$(this).find("user_mail").text();
				
				$html += '<tr id="find_customer_'+$user_id+'" style="cursor:pointer" ondblclick="set_order_customer('+$orderid+', '+$user_id+', 0);" title="Kunde OHNE Adresse zur Bestellung übernehmen">';
				$html +=	'<td>'+$user_username+'</td>';
				$html +=	'<td>'+$user_mail+'</td>';
				$html +=	'<td>'+$user_name+'</td>';
				$html += '</tr>'
				
				$(this).find("user_address").each( function ()
				{
					var $address = new Object;
					$address[0] = new Object;
					$(this).children().each(
					function()
					{
						var $tagname=this.tagName;
						$address[0][$tagname]=$(this).text();
					});
				
				
					$html += ' <tr style="cursor:pointer" ondblclick="set_order_customer('+$orderid+', '+$user_id+', '+$(this).find("adr_id").text()+');" title="Kunde mit Adresse zur Bestellung übernehmen">';
					$html += '	<td colspan=3 style="background-color:#FFFFFF">&nbsp;&nbsp;&nbsp;'+create_address_select_string($address, 0)+'</td>';
					$html += ' </tr>';
								
				});
				
			});
			$html += '</table>';
			$("#customer_find_dialog_results").html("");
			if ( counter > 0 )
			{
				$("#customer_find_dialog_results").html($html);
			}
			else
			{
				$("#customer_find_dialog_results").html("Keine Daten zur Suchanfrage gefunden!");
			}
		});
	}
	function highlight_searchstring(source, searchstring, $fontcolor)
	{
 		var searchstrings = searchstring.split(" "); 
		
		for (var i = 0; i<searchstrings.length; i++)
		{
			var source = source.split(searchstrings[i]).join('<font style="color:'+$fontcolor+'">'+searchstrings[i]+'</font>');

		}
		
		return source;
		
	}
	function set_order_customer(orderid, user_id, order_adr_id)
	{
		var company="";
		var firstname="";
		var lastname="";
		var street="";
		var number="";
		var additional="";
		var city="";
		var zip="";
		var country="";
		var country_id="";
		var usermail="";
		var userphone="";
		var usermobile="";
		var userfax="";
		var bill_adr_id=0;
		
		if (order_adr_id!=0)
		{
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSearch", mode:"customer_show", user_id:user_id, adr_id:order_adr_id},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					company=$xml.find("company").text();
					firstname=$xml.find("firstname").text();
					lastname=$xml.find("lastname").text();
					street=$xml.find("street").text();
					number=$xml.find("number").text();
					additional=$xml.find("additional").text();
					city=$xml.find("city").text();
					zip=$xml.find("zip").text();
					country=$xml.find("country").text();
					country_id=$xml.find("country_id").text();
					usermail=$xml.find("usermail").text();
					bill_adr_id=$xml.find("bill_adr_id").text();
					
					$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderAddressUpdate", 
					OrderID:orderid,
					customer_id:user_id,
					addresstype:"bill",
					country_id:country_id,
					company:company,
					firstname:firstname,
					lastname:lastname,
					street:street, 
					number:number,
					additional:additional,
					zip:zip,
					city:city,
					usermail:usermail},
					function(data)
					{
						var $xml=$($.parseXML(data));
						var Ack = $xml.find("Ack").text();
						if (Ack=="Success") 
						{
							$("#customer_find_dialog").dialog("close");
							update_view(orderid, "order_update_dialog");
							//order_update_dialog(orderid, "update");
						}
						else
						{
							show_status2(data);
						}
					});
							
				}
				else
				{
					show_status2(data);
				}
			});
		}
		else
		//NUR CUSTOMER ÜBERNEHMEN
		{
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", 
				SELECTOR_id_order:orderid,
				bill_company:company,
				bill_firstname:firstname,
				bill_lastname:lastname,
				bill_zip:zip,
				bill_city:city,
				bill_street:street,
				bill_number:number,
				bill_additional:additional,
				bill_country:country,
				bill_country_id:country_id,
				bill_adr_id:bill_adr_id,
				customer_id:user_id,
				usermail:usermail,
				userphone:userphone,
				usermobile:usermobile,
				userfax:userfax
			},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#customer_find_dialog").dialog("close");
					update_view(orderid, "order_update_dialog");
					//order_update_dialog(orderid, "update");
				}
				else
				{
					show_status2(data);
				}
			});
		}
	}

	
	function change_orderVAT_dialog(orderid)
	{
		if ($("#change_orderVAT_dialogbox").length==0)
		{
			$("body").append('<div id="change_orderVAT_dialogbox" style="display:none"></div>');
		}

		if (orders[orderid]["shop_id"] == 1 || orders[orderid]["shop_id"] == 3 || orders[orderid]["shop_id"] == 5 || orders[orderid]["shop_id"] == 7)
		{

			$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "CountriesGet"},
			function($data)
			{
				
				var html='';
				html+='<table>';
				html+='<tr>';
				html+='	<td>';
				
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") 
				{
					show_status2($data); 
					html+='		<select id="VAT_update" size=1>';
					if (orders[orderid]["VAT"]==0) var selected = 'selected'; else var selected ='';
					html+='		<option value=0 '+selected+'>0 %</option>';
					if (orders[orderid]["VAT"]==19) var selected = 'selected'; else var selected ='';
					html+='		<option value=19 '+selected+'>19 %</option>';
					html+='		</select>';
				}
				else
				{
					html+='		<select id="VAT_update" size=1>';
					if (orders[orderid]["VAT"]==0) var selected = 'selected'; else var selected ='';
					html+='		<option value=0 '+selected+'>0 %</option>';
					$xml.find("shop_countries").each( function()
					{
						if ($(this).find("country_code").text()==orders[orderid]["bill_country_code"])
						{
							if (orders[orderid]["VAT"]==$(this).find("VAT").text()) var selected = 'selected'; else var selected ='';
							html+='		<option value='+$(this).find("VAT").text()+' '+selected+'>'+$(this).find("VAT").text()+' %</option>';
						}
					});
					html+='		</select>';
					//html+='	Mehrwertsteuersatz:&nbsp;<input type="text" id="VAT_update" size = "3" value="'+orders[orderid]["VAT"]+'" />&nbsp;<b>%</b>';
				}
				html+='	</td>';
				html+='</tr>';
				html+='</table>';
				$("#change_orderVAT_dialogbox").html(html);

			});
		}
		else
		{
			var html='';
			html+='<table>';
			html+='<tr>';
			html+='	<td>';
			html+='		<select id="VAT_update" size=1>';
			if (orders[orderid]["VAT"]==0) var selected = 'selected'; else var selected ='';
			html+='		<option value=0 '+selected+'>0 %</option>';
			if (orders[orderid]["VAT"]==19) var selected = 'selected'; else var selected ='';
			html+='		<option value=19 '+selected+'>19 %</option>';
			html+='		</select>';
			html+='	</td>';
			html+='</tr>';
			html+='</table>';
			$("#change_orderVAT_dialogbox").html(html);
		}
		

		$("#change_orderVAT_dialogbox").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { change_order_VAT_save(orderid); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neuen Mehrwertsteuersatz eingeben",
			width:250
		});	
		
	}
	
	function change_order_VAT_save(orderid)
	{
		var $VAT = $("#VAT_update").val().replace(/,/g, ".")*1;
		if ($VAT !=19 && $VAT !=0)
	//	if ($VAT == "" || !isNumber($VAT))
		{
			msg_box("Bitte einen Mehrwertsteuersatz angeben (0 oder 19)");
			$("#VAT_update").focus();
			return;
		}

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, VAT:$VAT },
		function($data)
		{
			try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
			var $Ack = $xml.find("Ack").text();
			if ($Ack!="Success") {show_status2($data); return;}

			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "OrderVATUpdate", order_id:orderid},
			function($data)
			{
				wait_dialog_hide();
				//alert(data);
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				$("#change_orderVAT_dialogbox").dialog("close");
				update_view(orderid, "order_update_dialog");
			});
		});
				
		
	}


	function change_ordershipping(orderid)
	{
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{

				//	$("#change_orderpositions_Currency").val($xml.find("Currency_Code").text());
					var VAT=$xml.find("VAT").text();
					if (VAT == 0) $VAT = 1; else $VAT = (VAT/100 )+1;
					$("#change_ordershipping_VAT").val($VAT);

					var $payment_type_id = 0;
					
					$xml.find("order").each(
					function()
					{
						if (orderid==$(this).find("orderid").text())
						{
							$("#change_ordershipping_shipping_type_id").val($(this).find("shipping_type_id").text());
							$("#change_ordershipping_shipping_costsEURgross").val($(this).find("shipping_costs").text().toString().replace(".", ","));
							$("#change_ordershipping_shipping_costsEURnet").val($(this).find("shipping_net").text().toString().replace(".", ","));
							$("#change_ordershipping_shop_id").val($xml.find("shop_id").text());
							$("#currency_shipping_costs").text($xml.find("Currency_Code").text());
							
							$payment_type_id = $(this).find("payment_type_id").text();
							$xml.find("Item").each(
							function()
							{
								if ($(this).find("OrderItemOrderID").text()==orderid)
								{
									$("#change_ordershipping_exchangeratetoEUR").val($(this).find("OrderItemExchangeRateToEUR").text());
								}
							});
						}
					});
					
					//FIX FÜR 0 BESTELLPOSITIONEN
					if ($("#change_ordershipping_exchangeratetoEUR").val()=="") $("#change_ordershipping_exchangeratetoEUR").val(Currencies[$xml.find("Currency_Code").text()]["exchange_rate_to_EUR"])

					var shipping_costsFCgross=($("#change_ordershipping_shipping_costsEURgross").val().replace(",",".")*1)*$("#change_ordershipping_exchangeratetoEUR").val();
					var shipping_costsFCnet=($("#change_ordershipping_shipping_costsEURnet").val().replace(",",".")*1)*$("#change_ordershipping_exchangeratetoEUR").val();
					$("#change_ordershipping_shipping_costsFCgross").val(shipping_costsFCgross.toFixed(2).toString().replace(".", ","));
					$("#change_ordershipping_shipping_costsFCnet").val(shipping_costsFCnet.toFixed(2).toString().replace(".", ","));

					
					if ($xml.find("Currency_Code").text()!="EUR") $(".change_ordershipping_EUR_col").show(); else $(".change_ordershipping_EUR_col").hide();
					
					$("#change_ordershipping_dialog").dialog
					({	buttons:
						[
							{ text: "Speichern", click: function() { change_ordershipping_save(orderid, $payment_type_id);} },
							{ text: "Beenden", click: function() { $(this).dialog("close");} }
						],

						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Versand bearbeiten",
						width:400
					});		
				}
				else
				{
					
				}
			}
		);
	}

	function change_ordershipping_save(orderid, $payment_type_id)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var shipping_costs=($("#change_ordershipping_shipping_costsFCgross").val().replace(/,/g, "."))*1;
		var shipping_net=($("#change_ordershipping_shipping_costsFCnet").val().replace(/,/g, "."))*1;
		var shop_id=$("#change_ordershipping_shop_id").val();
		var shipping_type_id=$("#change_ordershipping_shipping_type_id").val();
		
		var shipping_detail = ShipmentTypes[shipping_type_id]["title"];
		shipping_detail+=", "+PaymentTypes[$payment_type_id]["title"];



		if (shipping_type_id==0)
		{
			$("#change_ordershipping_shipping_type_id").focus();
			msg_box("Es muss eine Versandart ausgewählt werden!");
			return;
		}
		else
		{
		
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, shipping_costs:shipping_costs, shipping_net:shipping_net, shipping_type_id:shipping_type_id, shipping_details:shipping_detail },
				function(data)
				{
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						/*
						//WENN EBAY-SHOP UND NICHT PAYPAL
						if (Shop_Shops[shop_id]["shop_type"]==2  && orders[orderid]["PaymentTypeID"]!=4)
						{
							$.post("<?php echo PATH; ?>soa/", { API: "ebay", Action: "ReviseCheckoutStatus", mode:"shipmentupdate", id_order:orderid},
								function (data)
								{
									var $xml=$($.parseXML(data));
									var Ack = $xml.find("Ack").text();
									if (Ack=="Success") 
									{
										$("#change_ordershipping_dialog").dialog("close");
										order_update_dialog(orderid, "update");
										return;
									}
									else
									{
										show_status2(data);
									}
								}
							);
						}
						else
						*/
						
						// SET MANUAL ADDRESS UPDATE -> VERSAND WIRD VON EBAY NICHT MEHR ÜBERNOMMEN
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, bill_address_manual_update:1 },
							function(data)
							{
								var $xml=$($.parseXML(data));
								var Ack = $xml.find("Ack").text();
								if (Ack=="Success") 
								{
									//alert(data);

									$("#change_ordershipping_dialog").dialog("close");
									update_view(orderid, "order_update_dialog");
									//order_update_dialog(orderid, "update");
									
								}
								else
								{
									show_status2(data);
								}
							});

					}
					else
					{
						show_status2(data);
					}
				}
			);
		}
	}

	
	function change_orderpositions(orderid, orderitemid)
	{
		
		//PREISLISTENFARBEN
		var $pricelistscolors = new Object;
		$pricelistscolors[3] = '#DFDFFF'; //BLAU
		$pricelistscolors[4] = '#DFF0D8'; //GRÜN
		$pricelistscolors[5] = '#FCF8E3'; //GELB
		$pricelistscolors[6] = '#FCE8D3'; //ORANGE
		$pricelistscolors[7] = '#F2DEDE'; //ROT
		$pricelistscolors[8] = '#F8F8F8'; //KUNDENLISTE
		
		var $pricelistname = new Object;
		$pricelistname[3] = "Blau";
		$pricelistname[4] = "Grün";
		$pricelistname[5] = "Gelb";
		$pricelistname[6] = "Orange";
		$pricelistname[7] = "Rot";
		$pricelistname[8] = "Kundenpreisliste";

		
		if ($("#change_orderpositions_dialog").length == 0)
		{
			$("body").append('<div id="change_orderpositions_dialog" style="display:none">');
		}

		
		var $html = '';
		$html += '<div style="width:45%; float:left">';

		$html += '<table>';
		$html += '<tr>';
		$html += '	<th>MPN</th>';
		$html += '	<td colspan="2"><input type="text" id="change_orderpositions_MPN" size="8" / onchange="change_orderpositions_changeMPN('+orderid+', \'update\');">';
		$html += '	<input type="hidden" id="change_orderpositions_ItemID" /></td>';
		$html += '</tr><tr>';
		$html += '	<th>Artikelbezeichnung</th>';
		$html += '	<td style="background-color:#fff" colspan="2"><span id="change_orderpositions_ArtBez"></td>';
		$html += '</tr><tr>';
		$html += '	<th>Anzahl</th>';
		$html += '	<td colspan="2"><input type="text" id="change_orderpositions_amount" size="2" onchange="change_orderpositions_setPrices(\'amount\');" /></td>';
		$html += '</tr><tr>';
		$html += '	<th></th><th>Brutto</th><th>Netto</th>';
		$html += '</tr><tr>';
		$html += '	<th>Einzel-VK</th>';
		$html += '	<td><span class="change_orderpositions_currency"></span>&nbsp;<input type="text" id="change_orderpositions_FCprice_gross" size="8" onchange="get_netto_from_FCbrutto(this.value);" /></td>';
		$html += '	<td><span class="change_orderpositions_currency"></span>&nbsp;<input type="text" id="change_orderpositions_FCprice_net" size="8" onchange="get_netto_from_FCnetto(this.value);" /></td>';
		$html += '</tr>';
		$html += '<tr class="change_orderpositions_colEUR">';
		$html += '	<th>Einzel-VK</th>';
		$html += '	<td>EUR&nbsp;<input type="text" id="change_orderpositions_price_gross" size="8" onchange="get_netto_from_brutto(this.value);" /></td>';
		$html += '	<td>EUR&nbsp;<input type="text" id="change_orderpositions_price_net" size="8" onchange="change_orderpositions_setPrices(this.value);" /></td>';
		$html += '</tr><tr>';
		$html += '	<th>Gesamt-VK</th>';
		$html += '	<td style="background-color:#fff"><span class="change_orderpositions_currency"></span>&nbsp;<span id="change_orderpositions_FCpriceTotal_gross">0,00</span></td>';
		$html += '	<td style="background-color:#fff"><span class="change_orderpositions_currency"></span>&nbsp;<span id="change_orderpositions_FCpriceTotal_net">0,00</span></td>';
		$html += '</tr>';
		$html += '<tr class="change_orderpositions_colEUR">';
		$html += '	<th>Gesamt-VK</th>';
		$html += '	<td style="background-color:#fff">EUR&nbsp;<span id="change_orderpositions_priceTotal_gross">0,00</span></td>';
		$html += '	<td style="background-color:#fff">EUR&nbsp;<span id="change_orderpositions_priceTotal_net">0,00</span></td>';
		$html += '</tr>';
		
			$html += '<input type="hidden" id="change_orderpositions_exchangeratetoEUR"; />';
			$html += '<input type="hidden" id="change_orderpositions_Currency"; />';
			$html += '<input type="hidden" id="change_orderpositions_VAT"; />';
		$html += '</table>';
		$html += '</div>';

		//PRICELIST VIEW
		$html += '<div id="change_orderpositions_pricelists" style="width:55%; float:left">';
		$html += '<table style="width:100%">';
		$html += '<tr>';
		$html += ' <th>Preisliste</th>';
		$html += ' <th>Preis Netto</th>';
		$html += ' <th>Preis Brutto</th>';
		$html += '</tr>';
		for (var $i= 3; $i < 9; $i++)
		{
			$html += '<tr style="background-color:'+$pricelistscolors[$i]+'; cursor:pointer" ondblclick="change_orderpositions_set_price_fromlist('+$i+');">';
			$html += ' <td>'+$pricelistname[$i]+'</td>';
			$html += ' <td style="text-align:right" id="change_orderpositions_pricelisttable_net'+$i+'"></td>';
			$html += ' <td style="text-align:right" id="change_orderpositions_pricelisttable_gross'+$i+'"></td>';
			$html += '</tr>';
		}
		$html += '</table>';
		$html += '</div>';
		
		$("#change_orderpositions_dialog").html($html);
		
		//FELDER "LEEREN"
		add_orderpositions_dialog_init(orderid)


		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					$("#change_orderpositions_Currency").val($xml.find("Currency_Code").text());
					//$("#change_orderpositions_VAT").val($xml.find("VAT").text());
					//$("#change_orderpositions_VATFree").val($xml.find("VATFree").text());
					var VAT=$xml.find("VAT").text();
					if (VAT == 0) $VAT = 1; else $VAT = (VAT/100 )+1;
					$("#change_orderpositions_VAT").val($VAT);
					$xml.find("Item").each(
					function()
					{
						if (orderitemid == $(this).find("OrderItemID").text())
						{
							$("#change_orderpositions_exchangeratetoEUR").val($(this).find("OrderItemExchangeRateToEUR").text());
							$("#change_orderpositions_Currency").val($(this).find("OrderItemCurrency_Code").text());

							var exchangerate=$(this).find("OrderItemExchangeRateToEUR").text()*1;
							var currency=$(this).find("OrderItemCurrency_Code").text();
														
							$("#change_orderpositions_MPN").val($(this).find("OrderItemMPN").text());
							$("#change_orderpositions_ItemID").val($(this).find("OrderItemItemID").text());
							$("#change_orderpositions_ArtBez").text($(this).find("OrderItemDesc").text());
							$("#change_orderpositions_amount").val($(this).find("OrderItemAmount").text());
							
							//FOREIGN CURRENCY
							var priceGross = $(this).find("orderItemPriceGross").text().replace(/,/g, ".")*exchangerate;
							var priceNet = $(this).find("orderItemPriceNet").text().replace(/,/g, ".")*exchangerate;
							$(".change_orderpositions_currency").text(currency);
							$("#change_orderpositions_FCprice_gross").val(priceGross.toFixed(2).toString().replace(".", ","));
							$("#change_orderpositions_FCprice_net").val(priceNet.toFixed(2).toString().replace(".", ","));
							
							var totalpriceGross = $(this).find("orderItemTotalGross").text().replace(/,/g, ".")*exchangerate;
							var totalpriceNet = $(this).find("orderItemTotalNet").text().replace(/,/g, ".")*exchangerate;
							$("#change_orderpositions_FCpriceTotal_gross").text(totalpriceGross.toFixed(2).toString().replace(".", ","));
							$("#change_orderpositions_FCpriceTotal_net").text(totalpriceNet.toFixed(2).toString().replace(".", ","));
							
							//EUR
							if (currency=="EUR") {$(".change_orderpositions_colEUR").hide();} else {$(".change_orderpositions_colEUR").show();}
							
							$("#change_orderpositions_price_gross").val($(this).find("orderItemPriceGross").text());
							$("#change_orderpositions_price_net").val($(this).find("orderItemPriceNet").text());
							$("#change_orderpositions_priceTotal_gross").text($(this).find("orderItemTotalGross").text());
							$("#change_orderpositions_priceTotal_net").text($(this).find("orderItemTotalNet").text());
							
							$("#change_orderpositions_Currency").val($(this).find("OrderItemCurrency_Code").text());
						}
					});
					
					$("#change_orderpositions_dialog").dialog
					({	buttons:
						[
							{ text: "Speichern", click: function() { change_orderpositions_save(orderid, orderitemid);} },
							{ text: "Beenden", click: function() { $(this).dialog("close");} }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Bestellpositionen bearbeiten",
						width:800
					});		
					change_orderpositions_changeMPN(orderid, 'init');
					
						//BIND EVENTHANDLER
						//MPN
						$("#change_orderpositions_MPN").bind("keypress", function(e) {
							if(e.keyCode==13)
							{
								change_orderpositions_changeMPN(orderid, 'update');
								//TAB / WORKFLOW
								//$("#change_orderpositions_amount").val("");
								$("#change_orderpositions_amount").focus();
							}
						});
				
						//AMOUNT
						$("#change_orderpositions_amount").bind("keypress", function(e) {
							if(e.keyCode==13)
							{
								change_orderpositions_setPrices('amount');
								//change_orderpositions_setPrices(($("#change_orderpositions_FCprice_net").val().replace(/,/g, "."))*1);
								//TAB / WORKFLOW
								if (orders[orderid]["gewerblich"] == 1 && Shop_Shops[orders[orderid]["shop_id"]]["shop_type"] == 1)
								{
									$("#change_orderpositions_FCprice_net").focus();
								}
								else
								{
									$("#change_orderpositions_FCprice_gross").focus();
								}
							}
						});
				
						//PRICE NET
						$("#change_orderpositions_FCprice_net").bind("keypress", function(e) {
							if(e.keyCode==13)
							{
								change_orderpositions_setPrices(($("#change_orderpositions_FCprice_net").val().replace(/,/g, "."))*1);
								//TAB / WORKFLOW
								$("#change_orderpositions_save_btn").focus();
							}
						});
				
						//PRICE GROSS
						$("#change_orderpositions_FCprice_gross").bind("keypress", function(e) {
							if(e.keyCode==13)
							{
								get_netto_from_FCbrutto($("#change_orderpositions_FCprice_gross").val());
								//TAB / WORKFLOW
								$("#change_orderpositions_save_btn").focus();
							}
						});

					
				}
				else
				{
				}
			}
		);
	}
	function change_orderpositions_save(orderid, orderitemid)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		
		var amount = $("#change_orderpositions_amount").val();
		var itemID = $("#change_orderpositions_ItemID").val();
		var price = ($("#change_orderpositions_FCprice_gross").val().replace(/,/g, "."))*1;
		var netto = ($("#change_orderpositions_FCprice_net").val().replace(/,/g, "."))*1;

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemUpdate", SELECTOR_id:orderitemid, order_id:orderid, amount:amount, item_id:itemID, price:price, netto:netto },
			function(data)
			{

				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#change_orderpositions_dialog").dialog("close");
					update_view(orderid, "order_update_dialog");
					//order_update_dialog(orderid, "update");
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}
	function add_orderpositions_dialog_init(parentorderid)
	{
		var $_VAT=orders[parentorderid]["VAT"];
		if ($_VAT == 0) var $VAT = 1; else var $VAT = ($_VAT/100 )+1;
		$("#change_orderpositions_VAT").val($VAT);

		var currency = orders[parentorderid]["Currency_Code"];
		$("#change_orderpositions_Currency").val(currency);
		//GET EXCHANGERATE
		var exchangerate=Currencies[currency]["exchange_rate_to_EUR"];
		$("#change_orderpositions_exchangeratetoEUR").val(exchangerate);
		//FOREIGN CURRENCY
		$(".change_orderpositions_currency").text(currency);
		
		//EUR
		if (currency=="EUR") {$(".change_orderpositions_colEUR").hide();} else {$(".change_orderpositions_colEUR").show();}


		$("#change_orderpositions_MPN").val("");
		$("#change_orderpositions_ArtBez").text("");

		$("#change_orderpositions_amount").val("0");

		$("#change_orderpositions_FCprice_gross").val("0,00");
		$("#change_orderpositions_FCprice_net").val("0,00");
		$("#change_orderpositions_price_gross").val("0,00");
		$("#change_orderpositions_price_net").val("0,00");
		$("#change_orderpositions_FCpriceTotal_gross").text("0,00");
		$("#change_orderpositions_FCpriceTotal_net").text("0,00");
		$("#change_orderpositions_priceTotal_gross").text("0,00");
		$("#change_orderpositions_priceTotal_net").text("0,00");
		
		//SET FOCUS
		$("#change_orderpositions_MPN").focus();

		//PRICELIST VIEW
		add_orderpositions_dialog_pricelist_init(parentorderid);
	
	}
	function add_orderpositions_dialog_pricelist_init(parentorderid)
	{	
		var currency_code = orders[parentorderid]["Currency_Code"];

		if ( currency_code != "EUR")
		{
			var $init_text = "0,00 "+currency_code+" (0,00 EUR)";	
		}
		else
		{
			var $init_text = "0,00 EUR";	
		}
		
		for (var $i= 3; $i < 9; $i++)
		{
			$("#change_orderpositions_pricelisttable_net"+$i).text($init_text);
			$("#change_orderpositions_pricelisttable_gross"+$i).text($init_text);
		}
	}
	function add_orderpositions(parentorderid, $shop_id)
	{
		//PREISLISTENFARBEN
		var $pricelistscolors = new Object;
		$pricelistscolors[3] = '#DFDFFF'; //BLAU
		$pricelistscolors[4] = '#DFF0D8'; //GRÜN
		$pricelistscolors[5] = '#FCF8E3'; //GELB
		$pricelistscolors[6] = '#FCE8D3'; //ORANGE
		$pricelistscolors[7] = '#F2DEDE'; //ROT
		$pricelistscolors[8] = '#F8F8F8'; //KUNDENLISTE
		
		var $pricelistname = new Object;
		$pricelistname[3] = "Blau";
		$pricelistname[4] = "Grün";
		$pricelistname[5] = "Gelb";
		$pricelistname[6] = "Orange";
		$pricelistname[7] = "Rot";
		$pricelistname[8] = "Kundenpreisliste";

		var combined_with=0;
//alert(shop_id);
		//FELDER LEEREN
		

		if ($("#change_orderpositions_dialog").length == 0)
		{
			$("body").append('<div id="change_orderpositions_dialog" style="display:none">');
		}

		
		var $html = '';
		$html += '<div style="width:45%; float:left">';
		$html += '<table>';
		$html += '<tr>';
		$html += '	<th>MPN</th>';
		$html += '	<td colspan="2"><input type="text" id="change_orderpositions_MPN" size="8" / onchange="change_orderpositions_changeMPN('+parentorderid+', \'add\');">';
		$html += '	<input type="hidden" id="change_orderpositions_ItemID" /></td>';
		$html += '</tr><tr>';
		$html += '	<th>Artikelbezeichnung</th>';
		$html += '	<td style="background-color:#fff" colspan="2"><span id="change_orderpositions_ArtBez"></td>';
		$html += '</tr><tr>';
		$html += '	<th>Anzahl</th>';
		$html += '	<td colspan="2"><input type="text" id="change_orderpositions_amount" size="2" onchange="change_orderpositions_setPrices(\'amount\');" /></td>';
		$html += '</tr><tr>';
		$html += '	<th></th><th>Brutto</th><th>Netto</th>';
		$html += '</tr><tr>';
		$html += '	<th>Einzel-VK</th>';
		$html += '	<td><span class="change_orderpositions_currency"></span>&nbsp;<input type="text" id="change_orderpositions_FCprice_gross" size="8" onchange="get_netto_from_FCbrutto(this.value);" /></td>';
		$html += '	<td><span class="change_orderpositions_currency"></span>&nbsp;<input type="text" id="change_orderpositions_FCprice_net" size="8" onchange="get_netto_from_FCnetto(this.value);" /></td>';
		$html += '</tr>';
		$html += '<tr class="change_orderpositions_colEUR">';
		$html += '	<th>Einzel-VK</th>';
		$html += '	<td>EUR&nbsp;<input type="text" id="change_orderpositions_price_gross" size="8" onchange="get_netto_from_brutto(this.value);" /></td>';
		$html += '	<td>EUR&nbsp;<input type="text" id="change_orderpositions_price_net" size="8" onchange="change_orderpositions_setPrices(this.value);" /></td>';
		$html += '</tr><tr>';
		$html += '	<th>Gesamt-VK</th>';
		$html += '	<td style="background-color:#fff"><span class="change_orderpositions_currency"></span>&nbsp;<span id="change_orderpositions_FCpriceTotal_gross">0,00</span></td>';
		$html += '	<td style="background-color:#fff"><span class="change_orderpositions_currency"></span>&nbsp;<span id="change_orderpositions_FCpriceTotal_net">0,00</span></td>';
		$html += '</tr>';
		$html += '<tr class="change_orderpositions_colEUR">';
		$html += '	<th>Gesamt-VK</th>';
		$html += '	<td style="background-color:#fff">EUR&nbsp;<span id="change_orderpositions_priceTotal_gross">0,00</span></td>';
		$html += '	<td style="background-color:#fff">EUR&nbsp;<span id="change_orderpositions_priceTotal_net">0,00</span></td>';
		$html += '</tr>';
		
			$html += '<input type="hidden" id="change_orderpositions_exchangeratetoEUR"; />';
			$html += '<input type="hidden" id="change_orderpositions_Currency"; />';
			$html += '<input type="hidden" id="change_orderpositions_VAT"; />';
		$html += '</table>';
		$html += '</div>';

		//PRICELIST VIEW
		$html += '<div id="change_orderpositions_pricelists" style="width:55%; float:left">';
		$html += '<table style="width:100%">';
		$html += '<tr>';
		$html += ' <th>Preisliste</th>';
		$html += ' <th>Preis Netto</th>';
		$html += ' <th>Preis Brutto</th>';
		$html += '</tr>';
		for (var $i= 3; $i < 9; $i++)
		{
			$html += '<tr style="background-color:'+$pricelistscolors[$i]+'; cursor:pointer" ondblclick="change_orderpositions_set_price_fromlist('+$i+');">';
			$html += ' <td>'+$pricelistname[$i]+'</td>';
			$html += ' <td style="text-align:right" id="change_orderpositions_pricelisttable_net'+$i+'"></td>';
			$html += ' <td style="text-align:right" id="change_orderpositions_pricelisttable_gross'+$i+'"></td>';
			$html += '</tr>';
		}
		$html += '</table>';
		$html += '</div>';


		$("#change_orderpositions_dialog").html($html);

		//FELDER "LEEREN"
		add_orderpositions_dialog_init(parentorderid)
		
		//SHOW DIALOG
		$("#change_orderpositions_dialog").dialog
		({	buttons:
			[
				{ id: "change_orderpositions_save_btn", text: "Speichern", click: function() { add_orderpositions_save(parentorderid, $shop_id, combined_with, orders[parentorderid]["VAT"]);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bestellpositionen bearbeiten",
			width:800
		});	
		
	//BIND EVENTHANDLER
		//MPN
		$("#change_orderpositions_MPN").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				change_orderpositions_changeMPN(parentorderid, 'add');
				//TAB / WORKFLOW
				$("#change_orderpositions_amount").val("");
				$("#change_orderpositions_amount").focus();
			}
		});

		//AMOUNT
		$("#change_orderpositions_amount").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				change_orderpositions_setPrices('amount');
				//change_orderpositions_setPrices(($("#change_orderpositions_FCprice_net").val().replace(/,/g, "."))*1);
				//TAB / WORKFLOW
				if (orders[parentorderid]["gewerblich"] == 1 && Shop_Shops[orders[parentorderid]["shop_id"]]["shop_type"] == 1)
				{
					$("#change_orderpositions_FCprice_net").focus();
				}
				else
				{
					$("#change_orderpositions_FCprice_gross").focus();
				}
			}
		});

		//PRICE NET
		$("#change_orderpositions_FCprice_net").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				change_orderpositions_setPrices(($("#change_orderpositions_FCprice_net").val().replace(/,/g, "."))*1);
				//TAB / WORKFLOW
				$("#change_orderpositions_save_btn").focus();
			}
		});

		//PRICE GROSS
		$("#change_orderpositions_FCprice_gross").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				get_netto_from_FCbrutto($("#change_orderpositions_FCprice_gross").val());
				//TAB / WORKFLOW
				$("#change_orderpositions_save_btn").focus();
			}
		});
	}
	
	function add_orderpositions_save(parentorderid, shop_id, combined_with, mwstmultiplier)
	{
	
		var item_id = $("#change_orderpositions_ItemID").val();
			if (item_id=="" || item_id==0) { alert("Es muss ein Artikel angegeben werden"); return;}
		var amount = $("#change_orderpositions_amount").val();

			if (amount=="" || amount==0) { alert("Es muss eine Stückzahl (größer 0) angegeben werden"); return;}
		var price = $("#change_orderpositions_FCprice_gross").val().replace(/,/g, ".")*1;
		var netto = $("#change_orderpositions_FCprice_net").val().replace(/,/g, ".")*1;

		var Currency_Code = $("#change_orderpositions_Currency").val();
		var exchange_rate_to_EUR = $("#change_orderpositions_exchangeratetoEUR").val();
		
		//IF SHOPTYPE = EBAY -> ITEM wird mit neuer Order angelegt und miteinander kombiniert
		var id_order=parentorderid;

		if (Shop_Shops[shop_id]["shop_type"]==2)
		{
			id_order=0;

			//CHECK, ob schon eine "neue" kombinierte nicht-Ebay Order existiert -> wenn ja, neuen Artikel dieser Order hinzufügen
			$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderDetailGet_neu",  OrderID:parentorderid},
			function($data)
			{
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				var $orders=$xml.find("orders");
				$orders.find("order").each(
				function ()
				{
					//alert($(this).find("foreign_OrderID").text());
					if ($(this).find("foreign_OrderID").text()=="") id_order = $(this).find("orderid").text();
				});

				//KEINE ANDERE ORDER VORHANDEN -> neue Order Anlegen und, kombinieren mit parantorde und artikel hinzufügen
				if (id_order == 0 || id_order == "")
				{
					//zu erstellende Order wird kombiniert
					if (combined_with<1) combined_with=parentorderid;
				
					$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderAdd", mode:"copy", id_order:parentorderid, foreign_OrderID:"", shipping_costs:0, shipping_net:0, combined_with:combined_with},
					function($data)
					{
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						id_order=$xml.find("id_order").text();

						//UPDATE PARENT ORDER -> COMBINED_WITH
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:parentorderid, combined_with:combined_with},
						function($data)
						{
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") {show_status2($data); return;}
							
							// INSERT ORDERITEM
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},

							function($data)
							{
								try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
								var $Ack = $xml.find("Ack").text();
								if ($Ack!="Success") {show_status2($data); return;}
								
								show_status("Bestellposition wurde erfolgreich eingefügt");
								//FELDER "LEEREN"
								add_orderpositions_dialog_init(parentorderid)

								update_view(parentorderid, "order_update_dialog");
								return;
							});
						});
					});
				} // END OF IF order_id==0
				else
				{
					//"neue" Order bereits vorhanden => Artikel dieser Order hinzufügen
					// INSERT ORDERITEM
					$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},
					function(data)
					{
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						show_status("Bestellposition wurde erfolgreich eingefügt");
						//FELDER "LEEREN"
						add_orderpositions_dialog_init(parentorderid)

						update_view(parentorderid, "order_update_dialog");
						return;
					});
				}
			});
		} // IF EBAYORDER
		else
		//SHOP ORDER
		{
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					show_status("Bestellposition wurde erfolgreich eingefügt");
					//FELDER "LEEREN"
					add_orderpositions_dialog_init(parentorderid)

					update_view(id_order, "order_update_dialog");
					return;
				}
				else
				{
				}
			});
		}
	}
	function change_orderpositions_changeMPN(parentorderid, mode)
	{
		
		wait_dialog_show();
		var MPN = $("#change_orderpositions_MPN").val();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ShopItemGet", MPN:MPN},
			function(data)
			{
				
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#change_orderpositions_ItemID").val($xml.find("id_item").text());
					$("#change_orderpositions_ArtBez").text($xml.find("title").text());
					
					//GET PRICE
					var $postfields = new Object;
					$postfields['API']			= 'crm';
					$postfields['APIRequest']	= 'UserPriceGet';
					$postfields['shop_id']		= orders[parentorderid]["shop_id"];
					$postfields['user_id']		= orders[parentorderid]["customer_id"];
					$postfields['MPN']			= MPN;

					$.post("<?php echo PATH; ?>soa2/", $postfields,
					function($data)
					{
						//show_status2($data);
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						var $userprice = $xml.find("userprice");
						var $userprice_net = $userprice.find("price_net").text();
						var $userpricelist = $userprice.find("price_listnr").text();
						//alert($price_net);
						
						//WENN ARTIKELPREISE ABWEICHEN -> DIALOG OB, geänderter Preis übernommen werden soll
						if (mode == "update" )
						{
							if ($userprice_net != $("#change_orderpositions_price_net").val().toString().replace(/,/g, ".")*1 && $userprice_net != 0)
							{
								if ($("#change_orderpositions_confirm_price").length == 0)
								{
									$("body").append('<div id="change_orderpositions_confirm_price" style="display:none">');
								}
								
								var $html = '';
								$html += '<p>Der Preis des neuen Artikels weicht vom vorherigen Artikel ab.</p><br />';
								$html += '<p><span style="background-color:yellow"><strong>Soll der Preis des neuen Artikels übernommen werden?</strong></span></p>';
								$("#change_orderpositions_confirm_price").html($html);
								
								$("#change_orderpositions_confirm_price").dialog
								({	buttons:
									[
										{ text: "Ja", click: function() { change_orderpositions_setPrices($userprice_net); $(this).dialog("close"); } },
										{ text: "Nein", click: function() { $(this).dialog("close");} }
									],
									closeText:"Fenster schließen",
									hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									show: { effect: 'drop', direction: "up" },
									title:"PREISÄNDERUNG",
									width:450
								});	
								
							}
						}
						if ( mode == "add" )
						{
							change_orderpositions_setPrices($userprice_net);
						}
						
						$Pricelists = new Object;
						$xml.find("pricelist").each(function ()
						{
							$Pricelists[$(this).find("price_listnr").text()] = $(this).find("price_net").text();
						});
						$Pricelists[8] = $userprice_net;
						
						
						change_orderpositions_drawPricelists(parentorderid);
						//show_status2(print_r($Pricelists));
					});
					
					
				}
				else
				{
					$("#change_orderpositions_ItemID").val(0);
					$("#change_orderpositions_ArtBez").text("ARTIKEL EXISTIERT NICHT!");
					$("#change_orderpositions_MPN").focus();
					
					// RESET PRICELIST VIEW
					add_orderpositions_dialog_pricelist_init(parentorderid);
				}
			}
		);
	}
	function change_orderpositions_drawPricelists($orderid)
	{
		var $_VAT=orders[$orderid]["VAT"];
		if ($_VAT == 0) var $VAT = 1; else var $VAT = ($_VAT/100 )+1;
				
		var $currency_code = orders[$orderid]["Currency_Code"];
		if ( typeof( orders[$orderid]["Items"][0] ) === "undefined" )
		{
			var $exrate = Currencies[$currency_code]["exchange_rate_to_EUR"];
		}
		else
		{
			var $exrate = orders[$orderid]["Items"][0]["OrderItemExchangeRate"];
		}
		for (var $pl in $Pricelists)
		{
			var $grossFC = $Pricelists[$pl]*$VAT*$exrate;
			$grossFC = $grossFC.toFixed(2).toString().replace(".", ",");
			var $netFC = $Pricelists[$pl]*1*$exrate;
			$netFC = $netFC.toFixed(2).toString().replace(".", ",");
			var $grossEUR = $Pricelists[$pl]*$VAT;
			$grossEUR = $grossEUR.toFixed(2).toString().replace(".", ",");
			var $netEUR = $Pricelists[$pl]*1;
			$netEUR = $netEUR.toFixed(2).toString().replace(".", ",");
			
			if ( $currency_code == "EUR")
			{
				$("#change_orderpositions_pricelisttable_net"+$pl).text($netFC+" "+$currency_code);
				$("#change_orderpositions_pricelisttable_gross"+$pl).text($grossFC+" "+$currency_code);
			}
			else
			{
				$("#change_orderpositions_pricelisttable_net"+$pl).text($netFC+" "+$currency_code+" ("+$netEUR+" EUR)");
				$("#change_orderpositions_pricelisttable_gross"+$pl).text($grossFC+" "+$currency_code+" ("+$grossEUR+" EUR)");
			}
		}
	}
	function change_orderpositions_set_price_fromlist($i)
	{
		var $netFC = $Pricelists[$i]*1;
		change_orderpositions_setPrices($netFC);
		//alert($netFC);
	}
	function change_orderpositions_setPrices(netto)
	{
		//IF FUNCTION  CALL from amount
		if (netto == "amount") netto = $("#change_orderpositions_price_net").val();
		
		var mwstmultiplier=$("#change_orderpositions_VAT").val();

		//BERECHNUNGEN LAUFEN IMMER VOM EINZELPREIS EUR NETTO
		var net = (netto.toString().replace(/,/g, "."))*1;
		
		var amount=$("#change_orderpositions_amount").val()*1;
		
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
	
			//PREIS POSITION GESAMT NETTO
			var netTotal = net*amount;
			//PREIS ARTIKEL EINZELN BRUTTO
			var gross = net*mwstmultiplier;
			//PREIS POSITION GESAMT BRUTTO
			var grossTotal = net*amount*mwstmultiplier;
			
		//FOREIGN CURRENCIES
			var FCnet = net*exchangerate;
			var FCnetTotal = net*amount*exchangerate;
			var FCgross = net*mwstmultiplier*exchangerate;
			var FCgrossTotal = net*amount*mwstmultiplier*exchangerate;
			
			$("#change_orderpositions_FCprice_gross").val(FCgross.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_FCprice_net").val(FCnet.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_price_gross").val(gross.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_price_net").val(net.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_FCpriceTotal_gross").text(FCgrossTotal.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_FCpriceTotal_net").text(FCnetTotal.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_priceTotal_gross").text(grossTotal.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_priceTotal_net").text(netTotal.toFixed(2).toString().replace(".", ","));
	}
	function get_netto_from_brutto(brutto)
	{
		var mwstmultiplier=$("#change_orderpositions_VAT").val();
		var gross=(brutto.replace(/,/g, "."))*1;
		
		if (gross!=0) var netto = gross/mwstmultiplier; else var netto = 0;
		
		change_orderpositions_setPrices(netto);
		
	}
	function get_netto_from_FCnetto(FCnetto)
	{
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
		var FCnet=(FCnetto.replace(/,/g, "."))*1;

		if (FCnet!=0) var netto = FCnet/exchangerate; else var netto = 0;
	//	alert(netto);
		change_orderpositions_setPrices(netto);
		
	}
	function get_netto_from_FCbrutto(FCbrutto)
	{
		var mwstmultiplier=$("#change_orderpositions_VAT").val();
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
		var FCgross=(FCbrutto.replace(/,/g, "."))*1;
		
		if (FCgross!=0) var netto = FCgross /exchangerate/ mwstmultiplier; else var netto = 0;
		change_orderpositions_setPrices(netto);
		
	}
	
	
	function change_ordershipping_setPrices(netto)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;
		
		var net = (netto.toString().replace(/,/g, "."))*1;

		var gross = net*mwstmultiplier;
		var netFC = net*exchangerate;
		var grossFC = net*exchangerate*mwstmultiplier;
		
		$("#change_ordershipping_shipping_costsFCgross").val(grossFC.toFixed(2).toString().replace(".", ","));
		$("#change_ordershipping_shipping_costsFCnet").val(netFC.toFixed(2).toString().replace(".", ","));
		$("#change_ordershipping_shipping_costsEURgross").val(gross.toFixed(2).toString().replace(".", ","));
		$("#change_ordershipping_shipping_costsEURnet").val(net.toFixed(2).toString().replace(".", ","));
		
	}
	function get_netto_from_FCbrutto_shipping(FCbrutto_shipping)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;

		var FCgross=(FCbrutto_shipping.replace(/,/g, "."))*1;
		if (FCgross!=0) var netto = FCgross / exchangerate / mwstmultiplier; else var netto = 0;
		
		change_ordershipping_setPrices(netto);
	}
	function get_netto_from_FCnetto_shipping(FCnetto_shipping)
	{
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;
		

		var FCnetto=(FCnetto_shipping.replace(/,/g, "."))*1;
		if (FCnetto!=0) var netto = FCnetto / exchangerate;
		
		change_ordershipping_setPrices(netto);
	}
	function get_netto_from_brutto_shipping(brutto_shipping)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var brutto=(brutto_shipping.replace(/,/g, "."))*1;
		if (brutto!=0) var netto = brutto / mwstmultiplier;
		
		change_ordershipping_setPrices(netto);
	}
	


	function change_paymenttype()
	{
		if ($("#paymentType").val()==4 || $("#paymentType").val()==5 || $("#paymentType").val()==6)
		{
			$("#update_Payment_Transaction_row").show();
		}
		else 
		{
			$("#update_Payment_Transaction_row").hide();
			$("#update_Payment_TransactionID").val("");
		}
		if ($("#paymentType").val()==0) 
		{
			$("#update_Payment_amount").val("0,00");
		}
		
	}


