<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	orderitems = new Object;
	returns = new Object;
	exchange = new Object;
	creditreasons = new Object;
	
	$credit = new Object;
	
	function update_credits_array($data)
	{
		var $xml=$($.parseXML($data));
		
		$xml.find("credit").each( function()
		{

	//		$credit['type'] = $(this).attr('type').text();	
			// READ "ROOT"
			$(this).children().each( function()
			{
				var $tagname=this.tagName;

				switch ($tagname)
				{
					case 'creditpositions': 
						$credit['creditposition'] = new Object;
						var $credit_pos_index = 0;
						$(this).find('creditposition').each( function ()
						{
							$credit['creditposition'][$credit_pos_index] = new Object();
							$(this).children().each( function ()
							{
								var $tagname2=this.tagName;
								
								switch ( $tagname2 )
								{
									// GET RETURN
									case 'return':
										$credit['creditposition'][$credit_pos_index]['return'] = new Object;
										$(this).children().each( function ()
										{
											var $tagname3=this.tagName;
											
											switch ( $tagname3 )
											{
												// GET RETURNITEMS
												case 'returnitems':
													$credit['creditposition'][$credit_pos_index]['return']['returnitem'] = new Object;
													var $return_item_index = 0;
													$(this).find('returnitem').each( function ()
													{
														$credit['creditposition'][$credit_pos_index]['return']['returnitem'][$return_item_index] = new Object;
														$(this).children().each( function ()
														{
															var $tagname4=this.tagName;
															$credit['creditposition'][$credit_pos_index]['return']['returnitem'][$return_item_index][$tagname4] = $(this).text();
														});
														$return_item_index ++;
													});
													break;
													
												default:
													$credit['creditposition'][$credit_pos_index]['return'][$tagname3]=$(this).text();
													break;

											}
										});
										break;

									// GET EXCHANGEORDER
									case 'exchange_order':
										$credit['creditposition'][$credit_pos_index]['exchange_order'] = new Object;
										$(this).children().each( function ()
										{
											var $tagname3=this.tagName;
											
											switch ( $tagname3 )
											{
												// GET EXCHANGEORDERITEMS
												case 'exchangeorderitems':

													$credit['creditposition'][$credit_pos_index]['exchange_order']['exchangeorderitem'] = new Object;
													var $exchange_item_index = 0;
													$(this).find('exchangeorderitem').each( function ()
													{
														$credit['creditposition'][$credit_pos_index]['exchange_order']['exchangeorderitem'][$exchange_item_index] = new Object;
														$(this).children().each( function ()
														{
															var $tagname4=this.tagName;
															$credit['creditposition'][$credit_pos_index]['exchange_order']['exchangeorderitem'][$exchange_item_index][$tagname4] = $(this).text();
														});
														$exchange_item_index ++;
													});
													break;
													
												default:
													$credit['creditposition'][$credit_pos_index]['exchange_order'][$tagname3]=$(this).text();
													break;

											}
										});
										break;
										
									default:
										$credit['creditposition'][$credit_pos_index][$tagname2] = $(this).text();
										break;

								}
								
								
							});
							$credit_pos_index ++;	
						});
						break;

					default:
						$credit[$tagname] = $(this).text();
						break;

				}
			});
		});
	}

/*
	function update_returns_array(data)
	{
		var $xml=$($.parseXML(data));
		$xml.find("orderreturn").each(
			function()
			{
				
			//	var order_id = $(this).find("id_return").text();
				//delete orders[order_id];
			//	orders[order_id] = new Array();
				
				$(this).children().each(
					function()
					{
						var $tagname=this.tagName;
						
						switch ($tagname)
						{
							case "returnitems": 
								returns["returnitem"] = new Array();
								var i=0;
								$(this).find("returnitem").each(
								function ()
								{
									returns["returnitem"][i] = new Array();
									$(this).children().each(
									function ()
									{
										var $tagname2=this.tagName;
										returns["returnitem"][i][$tagname2] = $(this).text();
									});
									
									
									i++;
								});
								break;
							case "returncredits": 
								returns["returncredit"] = new Array();
								var i=0;
								$(this).find("returncredit").each(
								function ()
								{
									returns["returncredit"][i] = new Array();
									$(this).children().each(
									function ()
									{
										var $tagname2=this.tagName;
										returns["returncredit"][i][$tagname2] = $(this).text();
									});
									
									
									i++;
								});
								break;
							default:
								returns[$tagname]=$(this).text();
								break;
						}
					}
				);
			}
		);
		
		
	}
*/
	
	function order_credit_add( $orderid )
	{
		if (confirm("Soll eine neue Gutschrift angelegt werden?"))
		{
			wait_dialog_show();
			$postfields 					= new Object;
			$postfields["API"]				= "shop";
			$postfields["APIRequest"]		= "OrderReturnSet";
			$postfields["credit_action"]	= "credit_add";
			$postfields["order_id"]			= $orderid;

			$.post("<?php echo PATH; ?>soa2/", $postfields, function ($data)
			{
				//show_status2($data);
				//return;
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				order_credit_start( $xml.find("credit_id").text() );
				update_view($orderid);
			});
		}
	}
	

	function order_returns_add( $orderid )
	{
		if (confirm("Soll eine neue Rückgabe angelegt werden?"))
		{
			wait_dialog_show();
			$postfields 					= new Object;
			$postfields["API"]				= "shop";
			$postfields["APIRequest"]		= "OrderReturnSet";
			$postfields["credit_action"]	= "return_add";
			$postfields["return_type"]		= "return";
			$postfields["order_id"]			= $orderid;
			$.post("<?php echo PATH; ?>soa2/", $postfields, function ($data)
			{
				//show_status2($data);
				//return;
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				order_credit_start( $xml.find("credit_id").text() );
				//order_returns_dialog($xml.find("return_id").text());
				update_view($orderid);
			});
		}
	} 
	
	function order_exchange_add( $orderid )
	{
		if (confirm("Soll ein neuer Umtausch angelegt werden?"))
		{
			wait_dialog_show();
			$postfields 					= new Object;
			$postfields["API"]				= "shop";
			$postfields["APIRequest"]		= "OrderReturnSet";
			$postfields["credit_action"]	= "return_add";
			$postfields["return_type"]		= "exchange";
			$postfields["order_id"]			= $orderid;
			$.post("<?php echo PATH; ?>soa2/", $postfields, function ($data)
			{
				//show_status2($data);
				//return;
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}

				order_credit_start( $xml.find("credit_id").text() );
				//order_returns_dialog($xml.find("return_id").text());
				update_view($orderid);
			});
		}
	}
	
	
	

	function order_returns_partial_dialog()
	{
		if ($("#order_returns_partial_dialog").length == 0)
		{
			$("body").append('<div id="order_returns_partial_dialog" style="display:none">');
		}
		
		var $html = '';
		$html+= '<b>Um einen Teilumtausch (Teile eines Kits / Satzes) durchzuführen, sind folgende Schritte abzuarbeiten: </b>';
		$html+= '<table style="width:100%" >';
		$html+=	'<tr>';
		$html+=	'	<th>';
		$html+=	'	</th>';
		$html+=	'	<th>';
		$html+= '		Schritt';
		$html+=	'	</th>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	1.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	<b>Teilgutschriften können nur vom Teamleiter durchgeführt werden</b>';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	2.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Rabatt gegenüber Einzelteilkauf ausrechnen';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	3.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Preis der gutzuschreibenden Einzelteile ausrechnen und Rabatt abziehen</b>';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	4.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Gutschrift über die errechnete Summe schreiben';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	5.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Im Notizfeld eintragen, welche Teile der Kunde zurücksendet (KEINE Artikel über den Dialog auswählen)';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	6.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Die Rechnungsnummer der neu erstellten Rechnung ebenfalls im Notizfeld vermerken';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	7.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	In der angelegten Umtausch-Bestellung die dem Kunden zu sendenden Artikel eintragen (Aktionen -> Umtausch bearbeiten -> Artikel hinzufügen)';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '</table>';

		$("#order_returns_partial_dialog").html($html);

		$("#order_returns_partial_dialog").dialog
		({	buttons:
			[
				{ text: "OK", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title: "Teilumtausch anlegen",
			width:800
		});		

	}
	
	function order_credit_start( $credit_id )
	{
		wait_dialog_show();
		
		var $postfield 				= new Object;
		$postfield['API'] 			= 'shop';
		$postfield['APIRequest'] 	= 'OrderCreditGet';
		$postfield['id_credit'] 	= $credit_id;
		
		$.post("<?php echo PATH; ?>soa3/", $postfield, function ($data)
		{
			wait_dialog_hide();
		//	show_status2($data);
			$string = ''
			try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
			/*
				//$xml.find('credit\\:*').each(function()
				$xml.find('lastmod_user').each(function()
				{
					var $tagname=this.tagName;
					$string += $tagname + $(this).text();
				});
			*/	
			update_credits_array($data);
			
			show_status2(print_r($credit));
			
			if ( $credit['type'] == 'credit')
			{
				order_credit_dialog();
			}
			else
			{
				order_returns_dialog();
			}

		});
	}


	function order_credit_dialog()
	{
		if ($("#order_credit_dialog").length == 0)
		{
			$("body").append('<div id="order_credit_dialog" style="display:none">');
		}
	
		var html = '';
		//RETURNS AGENT INFO
		var firstmod_user = $credit["firstmod_user_name"];
		if ($credit["firstmod"] != 0 ) var firstmod = convert_time_from_timestamp ( $credit["firstmod"] , "complete" ); else var firstmod = '';
	
		var lastmod_user = $credit["lastmod_user_name"];
		if ($credit["lastmod"]!=0) var lastmod = convert_time_from_timestamp ($credit["lastmod"], "complete"); else var lastmod = '';

		if ( $credit["auf_id_date"]!=0 ) var $auf_id_date = convert_time_from_timestamp ($credit["auf_id_date"], "complete"); else var auf_id_date = ''; 

		if ( $credit['auf_id'] != 0 ) var $auf_id = $credit['auf_id']; else var $auf_id = '';

		if ( $credit['status'] == 1)
		{
			var closed_by_user = $credit["closed_by_user_name"];
			if ($credit["date_closed"]!=0) var date_closed = convert_time_from_timestamp ($credit["date_closed"], "complete"); else var date_closed = '';
		}
		else
		{
			var closed_by_user = '';
			var date_closed = '';
		}
		
	
		html+='<table style="width:100%">';
		html+='<colgroup><col style="width:33%"><col style="width:33%"><col style="width:34%"></colgroup>';
		html+='<tr>';
		html+='	<td><b>Fall angelegt von: </b>'+firstmod_user+' <b>am: </b>'+firstmod+'</td>';
		html+='	<td><b>letzte Bearbeitung von: </b>'+lastmod_user+' <b>am: </b>'+lastmod+'</td>';
		if (returns["status"] != 1 || <?php echo $_SESSION["userrole_id"]; ?> == 1)
		{
			html+='	<td><b>Bearbeitungstatus</b> ';
			html+=' <select id="order_credit_update_state" size="1" onchange="order_credit_state_check();">';
			html+='		<option value=0>offen</option>';
			html+='		<option value=1>geschlossen</option>';
			html+='	<select></td>';
		}
		else
		{
			html+='	<td style="background-color:#cfc"><b>Fall geschlossen von: </b>'+closed_by_user+' <b>am: </b>'+date_closed+'</td>';
		}
		html+='</tr>';
		html+='</table>';
		
		//RETURNS DATA
	
			
		if ($credit["status"] == 1 || ( $auf_id != '' && $auf_id_date != ''))
		{
			var $disable='disabled';
		}
		else
		{
			var $disable='';
		}
		
		html+='<table style="width:100%">';
		html+='<colgroup><col style="width:33%"><col style="width:33%"><col style="width:33%"></colgroup>';
		html+='<tr>';
		html+='	<td><small><b>Gutschrift geschrieben am:</b></small>';
		html+='	 <input type="text" id="order_credit_auf_id_date" class="order_credit_update" size="10" value="'+$auf_id_date+'" '+$disable+' />';
		html+='	</td>';
		html+='	<td><small><b>Gutschrift AufID</b></small>';
		html+='	 <input type="text" id="order_credit_auf_id" class="order_credit_update" size="10" value="'+$auf_id+'" '+$disable+'/>';
		html+='	</td>';
		html+='	<td>';
		html+='		<b>Rechnungsnummer: </b>'+$credit["invoice_nr"];
		html+='	</td>';
		html+='</tr>';
		html+='</table>';

		//CREDITPOSITIONS		
		html+='<table style="width:100%">';
		html+='<tr>';
		html+='	<th>Gutschrift</th>';
		html+='	<th>Gutschriftdetails</th>';
		html+='	<th>Summe</th>';
		if ($disable=="") 
		{
			html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/note_add.png" alt="Gutschrift hinzufügen" title="Gutschriftposition hinzufügen" onclick="order_creditposition_set_dialog( 0 );"/></th>';
		}
		html+='</tr>';
			

		var $has_positions = false;
		for ( var $index in $credit['creditposition'] )
		{
			var $has_positions = true;	
			html+='<tr>';
			html+=' <td>';
			html+='	<b>'+$credit["creditposition"][$index]["reason_title"]+'</b>';
			html+='</td>';
			html+=' <td>';
			html+=	$credit["creditposition"][$index]["reason_description"];
			html+='</td>';

			html+=' <td style="text-align:right">'+($credit["creditposition"][$index]["gross"]*1).toFixed(2).toString().replace(".", ",")+' EUR</td>';
			
			if ($disable=="") 
			{
				html+='	<td><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Gutschrift bearbeiten" title="Gutschrift bearbeiten" onclick="order_creditposition_set_dialog('+$credit["creditposition"][$index]["id_shop_order_credit_position"]+');"/></td>';
			}
			html+='<tr>';
		}
		if ( $has_positions != true )
		{
			html+='<tr>';
			if ($disable=="") 
			{
				html+=' <td colspan = 4 style="text-align:center">';
			}
			else
			{
				html+=' <td colspan = 3 style="text-align:center">';	
			}
			html+='<strong>Diese Gutschrift enthält noch keine Einträge</strong>';
			html+='	</td>';
			html+='</tr>';
		}
		else
		{
			//SHOW CREDIT SUM
			$credit_sum_net = 0;
			$credit_sum_gross = 0;
			for ( var $index in $credit["creditposition"] )
			{
				$credit_sum_net += $credit["creditposition"][$index]["net"]*1;
				$credit_sum_gross += $credit["creditposition"][$index]["gross"]*1;
			}
			
			html+='<tr style="background-color:#fff">';
			html+=' <td></td>';
			html+='	<td><b>Gesamtsumme der Erstattung</b></td>';
			html+='	<td style="text-align:right"><b>'+$credit_sum_gross.toFixed(2).toString().replace(".", ",")+' EUR</b></td>';
			if ($disable=="") 
			{
				html+=' <td></td>';
			}
		
			html+='</tr>';
		}
		
		html+='</table>';
	
		$("#order_credit_dialog").html(html);
		
		//SET RETURN STATE
	//	$("#order_returns_state").val(returns["state"]);
		
		
		//SET DATEPICKER
		$( "#order_credit_auf_id_date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	
	
		//BIND EVENTHANDLER ONCHANGE
		$(".order_credit_update").bind("change", function(e) {
			order_credit_update($(this).attr("id"));
		});
	
	
		//BIND EVENTHANDLER "ENTER"
		$("#order_credit_auf_id").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				order_credit_update($(this).attr("id"));
			}
		});
	
	
		$("#order_credit_dialog").dialog
		({	buttons:
			[
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Gutschrift bearbeiten",
			width:1200,
			beforeClose: function() { order_credit_close_check(); }
		});		
	}
	
	function order_credit_close_check()
	{
		// CHECK, ob status "offen" && Gutschriftdatum gesetzt && Gutschrift AufID gesetzt && min. 1 Gutschriftposition -> Dialog "Gutschrift schließen?"
		if ( $credit['auf_id'] != 0 && $credit['auf_id_date'] != 0 && $credit['status'] == 0)
		{
			var $has_position = false;
			for ( var $index in $credit["creditposition"] )
			{
				$has_position = true;
			}
			
			if ( $has_position )
			{
				var $confirm_close = confirm('Soll die Gutschrift geschlossen werden?');

				if ( $confirm_close === true )
				{

					$post_field	= new Object();
					$post_field['API']								= 'shop';
					$post_field['APIRequest']						= 'OrderReturnSet';
					$post_field['credit_action']					= 'credit_close';
					$post_field['credit_id']						= $credit['id_shop_order_credit'];
					wait_dialog_show();
					$.post('<?php echo PATH; ?>soa2/index.php', $post_field, function ($data)
					{
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						wait_dialog_hide();
						$("#order_credit_dialog").dialog('close');
					});
				}
			}
		
		}
	}

	function order_credit_update ( $element_id )
	{
		//STRIP FIELDNAME
		var $element = $element_id.substr(13);
		
		if ( $element == 'auf_id_date' )
		{
			var $fieldvalue = Math.round($('#'+$element_id).datepicker('getDate') / 1000);
		}
		else
		{
			var $fieldvalue = $("#"+$element_id).val();
		}
		var $fieldname = $element;						

		//CHECK IF VALUE HAS CHANGED
		if ( $fieldvalue != $credit[$element] )
		{
			alert('WRITE DATA');
			//WRITE DATA
			$post_field	= new Object();
			
			if ( $element == 'status')
			{
				$post_field['API']								= 'shop';
				$post_field['APIRequest']						= 'OrderReturnSet';
				$post_field['credit_action']					= 'credit_close';
				$post_field['credit_id']						= $credit['id_shop_order_credit'];
			}
			else
			{
				$post_field['API']								= 'shop';
				$post_field['APIRequest']						= 'OrderReturnSet';
				$post_field['credit_action']					= 'credit_update';
				$post_field['credit_id']						= $credit['id_shop_order_credit'];
				$post_field['shop_orders_credits_'+$element]	= $fieldvalue;
			}
			wait_dialog_show();
			$.post('<?php echo PATH; ?>soa2/index.php', $post_field, function ($data)
			{
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				wait_dialog_hide();
				order_credit_start( $credit['id_shop_order_credit']);
			});
		}
	}

	function order_credit_state_check()
	{
		//FALL GESCHLOSSEN
		if ( $("#order_credit_update_state").val() == 1 )
		{
			//CHECK MANDANTORY FIELDS
				//RÜCKSENDUNG ERHALTEN
				//RÜCKSENDUNG ERHALTEN
			if ( $("#order_credit_auf_id_date").val() == 0 || $("#order_credit_auf_id_date").val() == "" )
			{
				$("#order_credit_auf_id_date").focus();
				msg_box("Die Gutschrift kann nicht geschlossen werden, da noch kein Gutschriftsdatum vermerkt wurde!");
				$("#order_credit_update_state").val(0);
				return;
			}
				//AUFID gespeichert
			if ( $("#order_credit_auf_id").val() == "" || $("#order_credit_auf_id").val() == 0 )
			{
				$("#order_credit_auf_id").focus();
				msg_box("Die Gutschrift kann nicht geschlossen werden, da noch keine IDIMS-Gutschrifts AufID vermerkt wurde!");
				$("#order_credit_update_state").val(0);
				return;
			}

			// CHECK OB POSITION MIT REASON "SONSTIGE" EINE BESCHREIBUNG HABEN
			$has_creditpositions = false;
			for ( var $index in $credit['creditposition'] )
			{
				$has_creditpositions = true;
				if ( $credit['creditposition'][$index]["reason_id"] == 4 && $credit['creditposition'][$index]["reason_description"] == "" )
				{
					msg_box("Die Gutschrift kann nicht geschlossen werden, da nicht  bei allen Gutschriftspositionen mit dem Grund ´sonstige´ eine Detailinfo vorliegt!");
					$("#order_credit_update_state").val(0);
					return;
				}
			}
			
			if ( !$has_creditpositions )
			{
				msg_box("Die Gutschrift kann nicht geschlossen werden, da noch keine Gutschriftposition eingetragen wurde!");
				$("#order_credit_update_state").val(0);
				return;
			}
						
		}

		order_credit_update("order_credit_update_state");
	}


	function order_returns_dialog()
	{
		
		if ($("#order_return_dialog").length == 0)
		{
			$("body").append('<div id="order_return_dialog" style="display:none">');
		}
	
		var html = '';
		
		//SET RETURN DATA
		for ( var $index in $credit['creditposition'] )
		{
			if ( $credit['creditposition'][$index]['reason_id'] == 1 )
			{
				returns = $credit['creditposition'][$index]['return'];
				exchange = $credit['creditposition'][$index]['exchange_order'];
			}
			
			
		}
		
		//show_status2(print_r(returns)+print_r(exchange));
	//	show_status2(print_r($credit));
	//				return;
		
		//RETURNS AGENT INFO
		//if (typeof (Seller[returns["firstmod_user"]])!=="undefined") var firstmod_user = Seller[returns["firstmod_user"]]; else var firstmod_user = 'UserID: '+returns["firstmod_user"];
		var firstmod_user = returns["firstmod_user_name"];
	
		if (returns["firstmod"]!=0) var firstmod = convert_time_from_timestamp (returns["firstmod"], "complete"); else var firstmod = '';
	
		if (typeof (Seller[returns["closed_by_user"]])!=="undefined") var closed_by_user = Seller[returns["firstmod_user"]]; else var closed_by_user = 'UserID: '+returns["firstmod_user"];
	
		if (returns["date_closed"]!=0) var date_closed = convert_time_from_timestamp (returns["date_closed"], "complete"); else var date_closed = '';
	
		//if (typeof (Seller[returns["lastmod_user"]])!=="undefined") var lastmod_user = Seller[returns["lastmod_user"]]; else var lastmod_user = 'UserID: '+returns["lastmod_user"];
		var lastmod_user = returns["lastmod_user_name"];
	
		if (returns["lastmod"]!=0) var lastmod = convert_time_from_timestamp (returns["lastmod"], "complete"); else var lastmod = '';
		
		//KAUFDATUM 
		var order_firstmod = convert_time_from_timestamp (orders[$credit['order_id']]["firstmod"], "date");
		//EXCHANGE SENT
		var date_exchange_sent = '';
		if ( typeof( exchange ) != 'undefined' )
		{
			//VERSENDET
			if (exchange['status_id'] == 3 ) 
			{
				date_exchange_sent = convert_time_from_timestamp (exchange['status_date'], 'date');
			}
		}
						
		html+='<table style="width:100%">';
		html+='<colgroup><col style="width:33%"><col style="width:33%"><col style="width:34%"></colgroup>';
		html+='<tr>';
		html+='	<td><b>Fall angelegt von: </b>'+firstmod_user+' <b>am: </b>'+firstmod+'</td>';
		html+='	<td><b>letzte Bearbeitung von: </b>'+lastmod_user+' <b>am: </b>'+lastmod+'</td>';
		if (returns["date_closed"]==1 || <?php echo $_SESSION["userrole_id"]; ?> == 1)
		{
			html+='	<td><b>Bearbeitungstatus</b> ';
			html+=' <select id="order_returns_state" size="1" onchange="order_returns_state_check();">';
			html+='		<option value=0>offen</option>';
			html+='		<option value=1>geschlossen</option>';
			html+='	<select></td>';
		}
		else
		{
			html+='	<td style="background-color:#cfc"><b>Fall geschlossen von: </b>'+closed_by_user+' <b>am: </b>'+date_closed+'</td>';
		}
		html+='</tr>';
		html+='<tr>';
		html+='	<td><b>Rechnungsnummer: </b>'+returns["invoice_nr"]+'</td>';
		html+='	<td><b>Kaufdatum: </b>'+order_firstmod+'</td>';
		if (returns["return_type"]=="exchange")
		{
			html+='	<td><b>Umtausch versendet am: </b>'+date_exchange_sent+'</td>';
		}
		else
		{
			html+='	<td></td>';
		}
	
		html+='</tr>';
		html+='</table>';
		
		//RETURNS DATA
	
			//DATUM ARTIKEL ZURÜCK 
			if (returns["date_return"]!=0) var date_return = convert_time_from_timestamp (returns["date_return"], "date"); else var date_return = '';	
	
			//DATUM ERSTATTUNG
			if (returns["date_refund"]!=0)	var date_refund = convert_time_from_timestamp (returns["date_refund"], "date"); else var date_refund = '';
	
			//ERSTATTUNGSSUMME
		//	if (returns["refund"]!=0) var refund = returns["refund"].toString().replace(".", ","); else var refund = '0,00';
		
			//if (returns["returncreditsum"]!=0) var refund = returns["returncreditsum"].toFixed(2).toString().replace(".", ","); else var refund = '0,00';
			var refund  = returns["returncreditsum"]*1;
			refund = refund.toFixed(2).toString().replace(".", ",");
	
			//ERSTATTUNGSSUMME FÜR VERSANDKOSTEN
			//if (returns["refund_shipment"]!=0) var refund_shipment = returns["refund_shipment"].toString().replace(".", ","); else var refund_shipment = '0,00';
	
			//EBAY DEMAND CLOSING 1
			if (returns["ebay_demand_closing1"]!=0) var ebay_demand_closing1 = convert_time_from_timestamp (returns["ebay_demand_closing1"], "date"); else var ebay_demand_closing1 = '';
	
			//EBAY DEMAND CLOSING 2
			if (returns["ebay_demand_closing2"]!=0) var ebay_demand_closing2 = convert_time_from_timestamp (returns["ebay_demand_closing2"], "date"); else var ebay_demand_closing2 = '';
			
			if (returns["refund_aufid"]!=0) var refund_aufid = returns["refund_aufid"]; else refund_aufid="";
		
			//if (returns["refund_order_shipment"]!=0) var refund_order_shipment = returns["refund_order_shipment"].toString().replace(".", ","); else var refund_order_shipment = '0,00';
			
		if (returns["state"] == 1)
		{
			var $disable='disabled';
		}
		else
		{
			var $disable='';
		}
		
		html+='<table style="width:100%">';
		html+='<colgroup><col style="width:25%"><col style="width:25%"><col style="width:25%"><col style="width:25%"></colgroup>';
		html+='<tr>';
		html+='	<td style="align-content:right"><small><b>Rücksendung erhalten am:</b></small>';
		html+=' <input type="text" id="order_returns_date_return" class="order_returns_update" size="10" value="'+date_return+'" '+$disable+' /></td>';
		html+='	<td><small><b>Erstattung durchgeführt am:</b></small>';
		html+=' <input type="text" id="order_returns_date_refund" class="order_returns_update" size="10" value="'+date_refund+'" '+$disable+' /></td>';
		html+='<td></td>';
	//				html+='	<td><small><b>Erstattungsumme</b></small>';
	//				html+=' <input type="text" id="order_returns_refund" class="order_returns_update" size="10" value="'+refund+'" '+$disable+' /><small>EUR</small></td>';
		html+='	<td><small><b>Gutschrift AufID</b></small> <input type="text" id="order_returns_refund_aufid" class="order_returns_update" size="10" value="'+refund_aufid+'" /></td>';
	//				html+='	<td><small><b>Erstattung Rücksendekosten</b></small><br />';
	//				html+=' <input type="text" id="order_returns_refund_shipment" class="order_returns_update" size="10" value="'+refund_shipment+'" '+$disable+' /><small>EUR</small></td>';
		html+='</tr><tr>';
		if (returns["return_type"]=="return")
		{
			html+='	<td><small><b>Aufford. Ebay-Rückgabe 1</b></small>';
			html+=' <input type="text" id="order_returns_ebay_demand_closing1" class="order_returns_update" size="10" value="'+ebay_demand_closing1+'" /></td>';
			html+='	<td><small><b>Aufford. Ebay-Rückgabe 2</b></small>';
			html+=' <input type="text" id="order_returns_ebay_demand_closing2" class="order_returns_update" size="10" value="'+ebay_demand_closing2+'" /></td>';
			html+='	<td><small><b>Ebay-Verkaufsprovision gutgeschrieben</b></small>';
			if (returns["ebay_fee_refund"]==1)
			{
				html+=' <input type="checkbox" id="order_returns_ebay_fee_refund" class="order_returns_update" value=1 checked="checked"/></td>';
			}
			else
			{
				html+=' <input type="checkbox" id="order_returns_ebay_fee_refund" class="order_returns_update" value=0 /></td>';
			}
			html+='<td></td>';
		}
		
		html+='</tr><tr>';
		html+='	<td colspan=3"><small><b>Notizen</b></small><br />';
		html+='	<textarea id="order_returns_return_note" class="order_returns_update" cols="150" rows="3">'+returns["return_note"]+'</textarea></td>';
		//RETOURLABEL
		if 	(orders[returns["order_id"]]["RetourLabelID"]!="")
		{
			html+='	<td>Retoulabel versendet <br />';
			html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_Returned.png" alt="Retourlabel versendet" title="Retourlabel versendet" /></span>';	
			html+='		<span>'+convert_time_from_timestamp(orders[returns["order_id"]]["RetourLabelTimestamp"], "complete")+'</span>';
			html+='	<br /><span><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+orders[returns["order_id"]]["RetourLabelID"]+'" target="_blank">'+orders[returns["order_id"]]["RetourLabelID"]+'</a></span>';
			if( typeof DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]] !== "undefined" )
			{
				var dhl_parameter = DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]]["dhl_parameter"];
				html+='	<br /><a href="javascript:send_DHLretourlabel('+returns["order_id"]+', \''+dhl_parameter+'\');">DHL-Retourlabel erneut senden</a>';
			}
			else 
			{
				html+='DHL-Retourlabel nicht möglich. Bitte Economy Select Import prüfen.';
			}
			html+='	</td>';
	
		}
		//else if (returns["returnitem"].length>0 && returns["state"] == 0)
		else if (returns["state"] == 0)
		{
			html+='	<td>';
			if( typeof DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]] !== "undefined" )
			{
				var dhl_parameter = DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]]["dhl_parameter"];
				html+='<a href="javascript:send_DHLretourlabel('+returns["order_id"]+', \''+dhl_parameter+'\');">DHL-Retourlabel senden</a>';
			}
			else 
			{
				html+='DHL-Retourlabel nicht möglich. Bitte Economy Select Import prüfen.';
			}
			html+='	</td>';
		}
	
		html+='</tr>';
		html+='</table>';
		
		//RETURNS ITEMS DATA
			//TYPE: RETURNS
		html+='<table style="width:100%">';
		
		//if (returns["return_type"]=="return")
		if ( $credit['type'] == 'return' )
		{
			html+='<tr>';
			html+='	<th>MPN</th>';
			html+='	<th>Artikelbezeichnung</th>';
			html+='	<th>Summe</th>';
			html+='	<th>Anzahl</th>';
			html+='	<th>Rückgabegrund</th>';
			if ($disable=="") 
			{
				html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/note_add.png" alt="Gutschrift hinzufügen" title="Gutschrift hinzufügen" onclick="order_creditposition_set_dialog( 0 );"/>';
				html+=' <img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel der Rückgabe hinzufügen" title="Artikel der Rückgabe hinzufügen" onclick="order_returnitem_set_dialog( 0 );"/></th>';
			}
			html+='</tr>';
			
			for (var i  in returns["returnitem"] )
			{
				if (returns["returnitem"][i]["return_reason_description"]!="")
				{
					var $style='style=\'border-bottom:0\'';
				}
				else
				{
					var $style='';
				}
				html+='<tr style="background-color:#fff">';
				html+='	<td '+$style+'>'+returns["returnitem"][i]["returnitem_MPN"]+'</td>';
				html+='	<td '+$style+'>'+returns["returnitem"][i]["returnitem_title"]+'</td>';
				var $itemtotal = returns["returnitem"][i]["returnitem_price"]*returns["returnitem"][i]["amount"] / returns["returnitem"][i]["returnitem_exchange_rate_to_EUR"];
				html+='	<td '+$style+' style="text-align:right">'+$itemtotal.toFixed(2).toString().replace(".", ",")+' EUR</td>';
				html+='	<td '+$style+' style="text-align:right">'+returns["returnitem"][i]["amount"]+'</td>';
				html+='	<td '+$style+'>'+ReturnsReasons[returns["returnitem"][i]["return_reason"]]["title"]+'</td>';
				//html+='	<td '+$style+'>'+returns["returnitem"][i]["return_reason"]+'</td>';
				if ($disable=="")
				{
					html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Artikel bearbeiten" title="Artikel bearbeiten" onclick="order_returnitem_set_dialog('+returns["returnitem"][i]["id_returnitem"]+');"/></td>';
					html+='<tr>';
				}
				if (returns["returnitem"][i]["return_reason_description"]!="")
				{
					if ($disable=="") var $cols=5; else var $cols=4;
					html+='<tr style="background-color:#FC7;"><td colspan="'+$cols+'" style="border-top:0">'+returns["returnitem"][i]["return_reason_description"]+'</td></tr>';
				}
			}
		}
		
			//TYPE: EXCHNAGE
		//if (returns["return_type"]=="exchange")
		if ( $credit['type'] == 'exchange' )
		{
			html+='<tr>';
			html+='	<th>MPN</th>';
			html+='	<th>Artikelbezeichnung</th>';
			html+='	<th>Summe</th>';
			html+='	<th>Anzahl</th>';
			html+='	<th>Umtauschgrund</th>';
			html+='	<th style="background-color:#999">U-MPN</th>';
			html+='	<th style="background-color:#999">U-Artikelbezeichnung</th>';
			html+='	<th style="background-color:#999">U-Anzahl</th>';
			if ( $disable == '' ) 
			{
				html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/note_add.png" alt="Gutschrift hinzufügen" title="Gutschrift hinzufügen" onclick="order_creditposition_set_dialog( 0 );"/>';
				html+=' <img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel dem Umtausch hinzufügen" title="Artikel dem Umtausch hinzufügen" onclick="order_exchangeitem_set_dialog( 0 );"/></th>';
			}
			html+='</tr>';
			
			//alert(Object.keys(returns["returnitem"]).length);
			//alert(returns["returnitem"].length);
	//					for (var i  = 0; i<returns["returnitem"].length; i++)
			for (var i  in returns["returnitem"] )
			{
				if (returns["returnitem"][i]["return_reason_description"]!="")
				{
					var $style='style=\'border-bottom:0\'';
				}
				else
				{
					var $style='';
				}
				html+='<tr style="background-color:#fff">';
				html+='	<td '+$style+'>'+returns["returnitem"][i]["returnitem_MPN"]+'</td>';
				html+='	<td '+$style+'>'+returns["returnitem"][i]["returnitem_title"]+'</td>';
				var $itemtotal = returns["returnitem"][i]["returnitem_price"]*returns["returnitem"][i]["amount"] / returns["returnitem"][i]["returnitem_exchange_rate_to_EUR"];
				html+='	<td '+$style+' style="text-align:right">'+$itemtotal.toFixed(2).toString().replace(".", ",")+' EUR</td>';
				html+='	<td '+$style+' style="text-align:right">'+returns["returnitem"][i]["amount"]+'</td>';
				html+='	<td '+$style+'>'+ReturnsReasons[returns["returnitem"][i]["return_reason"]]["title"]+'</td>';
				for ( var j in exchange['exchangeorderitem'] )
				{
					if ( exchange['exchangeorderitem'][j]['id'] == returns["returnitem"][i]['exchange_shop_orders_item'] )	
					{
						var $exchangeMPN 	= exchange['exchangeorderitem'][j]['exchangeorderitem_MPN'];
						var $exchangetitle 	= exchange['exchangeorderitem'][j]['exchangeorderitem_title'];
						var $exchangeamount = exchange['exchangeorderitem'][j]['amount'];
					}
				}
				
				html+='	<td '+$style+'>'+$exchangeMPN+'</td>';
				html+='	<td '+$style+'>'+$exchangetitle+'</td>';
				html+='	<td '+$style+'>'+$exchangeamount+'</td>';
	
				//html+='	<td '+$style+'>'+returns["returnitem"][i]["return_reason"]+'</td>';
				if ($disable=="") 
				{
					html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Artikel bearbeiten" title="Artikel bearbeiten" onclick="order_exchangeitem_set_dialog('+returns["returnitem"][i]["id_returnitem"]+');"/></td>';					
					html+='<tr>';
				}
				if (returns["returnitem"][i]["return_reason_description"]!="")
				{
					if ($disable=="") var $cols=8; else var $cols=7;
					html+='<tr style="background-color:#FC7;"><td colspan="'+$cols+'" style="border-top:0">'+returns["returnitem"][i]["return_reason_description"]+'</td></tr>';
				}
			}
		}
		
		//CHECK FOR REFUNDABLE SHIPPING COSTS
		//if (orders[returns["order_id"]]["shipping_costs"]!="0,00")
		if ( true )
		{
			//GET SHIPPING CREDITS
			var $shipping_credits = 0;
			var $credit_position_id = 0;
			for ( var $index in $credit["creditposition"] )
			{
				if ($credit["creditposition"][$index]["reason_id"] == 2)
				{
					$shipping_credits+=	$credit["creditposition"][$index]["gross"]*1;
					$credit_position_id = $credit["creditposition"][$index]["id_shop_order_credit_position"];
				}
				
			}
	
			html+='<tr>';
			html+=' <td>FRACHT</td>';
			if ($shipping_credits == 0)
			{
				html+=' <td><b>Keine Versandkosten erstattet</b> (bezahlte Versandkosten: '+orders[returns["order_id"]]["OrderShippingCosts"]+')</td>';
			}
			else
			{
				html+=' <td>bezahlte Versandkosten: '+orders[returns["order_id"]]["OrderShippingCosts"]+'</td>';
			}
			html+=' <td style="text-align:right">'+$shipping_credits.toFixed(2).toString().replace(".", ",")+' EUR</td>';
			if ( returns["return_type"]=="return" )
			{
				html+=' <th colspan="2"></th>';
			}
			if ( returns["return_type"]=="exchange" )
			{
				html+=' <th colspan="5"></th>';
			}
	
			if ($disable=="") 
			{
				html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Versandkosten erstatten" title="Versandkosten erstatten" onclick="order_return_shipping_costs_update_dialog('+$credit_position_id+');"/></td>';
			}
			html+='</tr>';
		}
		
		//CHECK FOR OTHER CREDITS
		for ( var $index in $credit["creditposition"] )
		{
			
			// NICHT VERSANDKOSTENERSTATTUNG && NICHT RETURN
			if ($credit["creditposition"][$index]["reason_id"] != 2 && $credit["creditposition"][$index]["reason_id"] != 1)
			{
				html+='<tr>';
				html+=' <td>Gutschrift</td>';
				html+=' <td>';
				html+='	<b>'+$credit["creditposition"][$index]["reason_title"]+'</b>';
				html+='</td>';
				html+=' <td style="text-align:right">'+($credit["creditposition"][$index]["gross"]*1).toFixed(2).toString().replace(".", ",")+' EUR</td>';
				
				if ( returns["return_type"]=="return" )
				{
					html+=' <th colspan="2"></th>';
				}
				if ( returns["return_type"]=="exchange" )
				{
					html+=' <th colspan="5"></th>';
				}
				
				if ($disable=="") 
				{
					html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Gutschrift bearbeiten" title="Gutschrift bearbeiten" onclick="order_credit_update_dialog('+$credit["creditposition"][$index]["id_shop_order_credit_position"]+');"/></td>';
				}
				html+='<tr>';
			}
		}
		
		//SHOW CREDIT SUM
		$credit_sum_net = 0;
		$credit_sum_gross = 0;
		for ( var $index in $credit["creditposition"] )
		{
			$credit_sum_net += $credit["creditposition"][$index]["net"]*1;
			$credit_sum_gross += $credit["creditposition"][$index]["gross"]*1;
		}
		
		html+='<tr style="background-color:#fff">';
		html+=' <td></td>';
		html+='	<td><b>Gesamtsumme der Erstattung</b></td>';
		html+='	<td style="text-align:right"><b>'+$credit_sum_gross.toFixed(2).toString().replace(".", ",")+' EUR</b></td>';
		if ( returns["return_type"]=="return" )
		{
			if ($disable=="") 
			{
				html+=' <td colspan="3"></td>';
			}
			else
			{
				html+=' <td colspan="2"></td>';
			}
		}
		if ( returns["return_type"]=="exchange" )
		{
			if ($disable=="") 
			{
				html+=' <td colspan="6"></td>';
			}
			else
			{
				html+=' <td colspan="5"></td>';
			}
		}
	
		html+='</tr>';
		
		
		html+='</table>';
	
		$("#order_return_dialog").html(html);
		
		//SET RETURN STATE
		$("#order_returns_state").val(returns["state"]);
		
		
		//SET DATEPICKERs
		$( "#order_returns_date_return" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		$( "#order_returns_date_refund" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	
		if (returns["return_type"]=="return")
		{
			$( "#order_returns_ebay_demand_closing1" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
			$( "#order_returns_ebay_demand_closing2" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		}
	
		//BIND EVENTHANDLER ONCHANGE
		$(".order_returns_update").bind("change", function(e) {
			order_returns_update($(this).attr("id"), return_id);
		});
	
	
		//BIND EVENTHANDLER "ENTER"
		$("#order_returns_billnumber").bind("keypress", function(e) {
			if(e.keyCode==13)
			{
				order_returns_update($(this).attr("id"), return_id);
			}
		});
	
		if (returns["return_type"]=="return")
		{
			var $dialogtitle="Rückgabe bearbeiten";
		}
		else
		{
			var $dialogtitle="Umtausch bearbeiten";
		}
		
		$("#order_return_dialog").dialog
		({	buttons:
			[
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:$dialogtitle,
			width:1200
		});		
				
	}
	
	function order_return_shipping_costs_update_dialog( $credit_position_id )
	{
		
		if ($("#order_return_shipping_costs_update_dialog").length == 0)
		{
			$("body").append('<div id="order_return_shipping_costs_update_dialog" style="display:none">');
		}

		//SET SHIPPING CREDIT SUGGESTION
		var $shipping_credit_suggestion = 0;
		$shipping_credit_suggestion = ((orders[returns["order_id"]]["OrderShippingCosts"].replace(/,/g, "."))*1);
		var $shipping_credit_suggestion_string = $shipping_credit_suggestion.toFixed(2).toString().replace(".", ",");
		
		for ( var $index in $credit['creditposition'] )
		{
			if ( $credit['creditposition'] [$index] ['id_shop_order_credit_position'] == $credit_position_id )	
			{
				var $credit_gross = 0;
				$credit_gross = $credit['creditposition'] [$index] ['gross']*1;
				$shipping_credit_suggestion_string = $credit_gross.toFixed(2).toString().replace(".", ",");
			}
		}
		
		
		var $html='';
		$html+='<table style="width:100%">';
		$html+='<tr>';
		$html+='	<th>bezahlte Versandkosten</th>';
		$html+='	<td>'+orders[$credit["order_id"]]["OrderShippingCosts"]+' EUR</td>';
		$html+='</tr>';
		$html+='<tr>';
		$html+='	<th>Versandkosten erstatten</th>';
		$html+='	<td><input type="text" id="order_return_shipping_costs_update_dialog_shipping_credit" size="10" value="'+$shipping_credit_suggestion_string+'" /> EUR</td>';
		$html+='</tr>';
		$html+='</table>';
		
		$("#order_return_shipping_costs_update_dialog").html($html);
		
		$("#order_return_shipping_costs_update_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { order_return_shipping_costs_update( $credit_position_id );} },
				{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Versandkosten erstatten",
			width:400
		});		
	}
	
	function order_return_shipping_costs_update( $credit_position_id )
	{
		
		var $shipping_credit = $("#order_return_shipping_costs_update_dialog_shipping_credit").val();
		
		if ($shipping_credit == "" || $shipping_credit == 0 )
		{
			alert("Bitte einen gültigen Betrag eingeben");
			$("#order_return_shipping_costs_update_dialog_shipping_credit").focus();
			return;
		}
		
		// GET NEW SHIPPING CREDITS
		$shipping_credit = $shipping_credit.replace(/,/g, ".")*1;

		//GET SHIPPING COSTS
		var $shipping_costs = orders[returns["order_id"]]["OrderShippingCosts"].replace(/,/g, ".")*1;
		
		// CHECK IF SUM OF CREDITS <= SHIPPINGCOSTS
		if ($shipping_costs <  $shipping_credit)
		{
			var $max_credit = $shipping_costs;
			$max_credit = $max_credit.toFixed(2).toString().replace(".", ",");
			alert("Betrag kann nicht gutgeschrieben werden. Maximalbetrag: "+$max_credit+" EUR");
			$("#order_return_shipping_costs_update_dialog_shipping_credit").val($max_credit);
			$("#order_return_shipping_costs_update_dialog_shipping_credit").focus();
			return;
		}
		
		var $shipping_credit_net = 0;
		
		if (orders[$credit['order_id']]['VAT'] == 0)
		{
			$shipping_credit_net = $shipping_credit;
		}
		else
		{
			$shipping_credit_net= $shipping_credit/((orders[$credit['order_id']]["VAT"]/100)+1);
		}
		
		
		
		var post_object 									= new Object();
		
		if ( $credit_position_id == 0 )
		{
			//ADD
			post_object['API'] 				= 'shop';
			post_object['APIRequest'] 		= 'OrderReturnSet'; // => HAS TO BE RENAMED TO OrderCreditSet
			post_object['credit_action'] 	= 'credit_position_add';
			post_object['credit_id'] 		= $credit['id_shop_order_credit'];
			post_object['reason_id'] 		= 2;
			post_object['return_id'] 		= $credit['return_id'];
			post_object['net'] 				= $shipping_credit_net;
			post_object['gross'] 			= $shipping_credit;
			
		}
		else
		{
			//UPDATE	
			post_object['API'] 									= 'shop';
			post_object['APIRequest'] 							= 'OrderReturnSet'; // => HAS TO BE RENAMED TO OrderCreditSet
			post_object['credit_action'] 						= 'credit_position_update';
			post_object['creditposition_id'] 					= $credit_position_id;
			//FIELDS FOR UPDATE IN TABLE MUST HAVE PREFIX: shop_orders_credits_positions_
			post_object['shop_orders_credits_positions_gross'] 	= $shipping_credit;
			post_object['shop_orders_credits_positions_net'] 	= $shipping_credit_net.toFixed(2);
		}
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
	
	
			$("#order_return_shipping_costs_update_dialog").dialog("close");
			order_credit_start( $credit['id_shop_order_credit']);
		});
		
	}

/*	
	function order_creditposition_add_dialog()
	{
		// GET CREDIT REASONS - WITHOUT "Umtausch/Rückgabe" & "Versandkosten"
		var $reasonselect = '';

		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="shop_orders_credits_reasons";
		postfields['db'] = "dbshop";
		postfields['where'] =  "WHERE NOT id_reason = 1 AND NOT id_reason = 2";
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			//return;
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			//show_status2($data);
			
			$reasonselect+= '<select id = "order_creditposition_add_reason" size = "1">';
			$reasonselect+= '	<option value = 0>Bitte Gutschriftgrund angeben</option>';
			$xml.find("shop_orders_credits_reasons").each(function()
			{
				$reasonselect+= '	<option value = '+$(this).find("id_reason").text()+'>'+$(this).find("title").text()+'</option>';
			});
			$reasonselect+= '</select>';
			
		
			var $html = '';
			$html+= '<table style="width:100%">';
			$html+=	'<tr>';
			$html+= '	<th>Gutschrift</th>';
			$html+=	'	<td>'+$reasonselect+'</td>';
			$html+=	'</tr><tr>';
			$html+=	'	<th>Gutschriftdetails</th>';
			$html+=	'	<td><textarea cols = "30" rows = "4" id = "order_creditposition_add_reason_detail"></textarea></td>';
			$html+=	'</tr><tr>';
			$html+=	'	<th>Gutschriftbetrag</th>';
			$html+=	'	<td><input type = "text" id = "order_creditposition_add_reason_creditgross" size = "10" value = "0,00"/> <b>EUR</b></td>';
			$html+=	'</tr>';
			$html+=	'</table>';
			
			if ($("#order_creditposition_add_dialog").length == 0)
			{
				$("body").append('<div id="order_creditposition_add_dialog" style="display:none">');
			}
	
			$("#order_creditposition_add_dialog").html($html);
			
			$("#order_creditposition_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { order_creditposition_add();} },
					{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
				],
				closeOnEscape: false,
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Gutschrift erstellen",
				width:400
			});		
		});

	}

	function order_creditposition_add()
	{
		var $creditgross = ($("#order_creditposition_add_reason_creditgross").val().replace(/,/g, "."))*1;
		
		if ($creditgross == 0 || isNaN($creditgross) )
		{
			alert("Bitte eine korrekte Gutschriftsumme größer 0 angeben!");
			$("#order_creditposition_add_reason_creditgross").focus();
			return;
		}
		
		//CHECK IF REASON IS SELECTED
		if ($("#order_creditposition_add_reason").val() == 0)
		{
			alert("Bitte eine Gutschriftart festlegen!");
			$("#order_creditposition_add_reason").focus();
			return;
		}
		//CHECK FOR DETAIL FOR "Sonstige"
		if ($("#order_creditposition_add_reason").val() == 4 && $("#order_creditposition_add_reason_detail").val() == "")
		{
			alert("Bitte eine Erläuterung zur Gutschrift `Sonstige` angeben!");
			$("#order_creditposition_add_reason_detail").focus();
			return;
		}

// UPDATE FOR SWITCH BRUTTOPREIS OR NETTOPREIS BASIS
		$creditnet= $creditgross/((orders[$credit["order_id"]]["VAT"]/100)+1);
		
		var post_object 					= new Object();
		post_object['API'] 					= 'shop';
		post_object['APIRequest'] 			= 'OrderReturnSet'; // => HAS TO BE RENAMED TO OrderCreditSet
		post_object['credit_action'] 		= 'credit_position_add';
		post_object['credit_id'] 			= $credit['id_shop_order_credit'];
		post_object['reason_id'] 			= $("#order_creditposition_add_reason").val();
		post_object['reason_description'] 	= $("#order_creditposition_add_reason_detail").val();
		post_object['gross'] 				= $creditgross;
		post_object['net'] 					= $creditnet.toFixed(2);
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
	
			
			$("#order_creditposition_add_dialog").dialog("close");
			//order_returns_dialog2($return_id);
			order_credit_start( $credit['id_shop_order_credit']);
		});
		
	}
*/


	function order_creditposition_set_dialog( $credit_position_id )
	{
	
		if ( typeof( $credit_position_id ) === 'undefined' )
		{
			$credit_position_id = 0;
		}
		
		
		// WENN $credit_position_id == 0 => addcreditposition 
		// WENN $credit_position_id != 0 => updatecreditposition

		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="shop_orders_credits_reasons";
		postfields['db'] = "dbshop";
		postfields['where'] =  "WHERE NOT id_reason = 1 AND NOT id_reason = 2";
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data2)
		{
			wait_dialog_hide();
			try { $xml2 = $($.parseXML($data2)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml2.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data2); return; }
			
			var $reasonselect = '';
			$reasonselect+= '<select id = "order_credit_update_dialog_reason" size = "1">';
			$reasonselect+= '	<option value = 0>Bitte Gutschriftgrund angeben</option>';
			$xml2.find("shop_orders_credits_reasons").each(function()
			{
				$reasonselect+= '	<option value = '+$(this).find("id_reason").text()+'>'+$(this).find("title").text()+'</option>';
			});
			$reasonselect+= '</select>';
			
		
			var $html = '';
			$html+= '<table style="width:100%">';
			$html+=	'<tr>';
			$html+= '	<th>Gutschrift</th>';
			$html+=	'	<td>'+$reasonselect+'</td>';
			$html+=	'</tr><tr>';
			$html+=	'	<th>Gutschriftdetails</th>';
			$html+=	'	<td><textarea cols = "30" rows = "4" id = "order_credit_update_dialog_reason_detail"></textarea></td>';
			$html+=	'</tr><tr>';
			$html+=	'	<th>Gutschriftbetrag</th>';
			$html+=	'	<td><input type = "text" id = "order_credit_update_dialog_gross" size = "10" value = "0,00"/> <b>EUR</b></td>';
			$html+=	'</tr>';
			$html+=	'</table>';
			
			if ($("#order_creditposition_set_dialog").length == 0)
			{
				$("body").append('<div id="order_creditposition_set_dialog" style="display:none">');
			}
	
			$("#order_creditposition_set_dialog").html($html);


			$pos_index = 0;
			for ( var $index in $credit['creditposition'] )
			{
				if ( $credit['creditposition'][$index]['id_shop_order_credit_position'] == $credit_position_id )	
				{
					$pos_index = $index;
				}
			}
			

			$dialog_title = 'Gutschriftposition anlegen';
			//FELDER BELEGEN
			if ( $credit_position_id != 0 )
			{
				$dialog_title = 'Gutschriftposition bearbeiten';
				
				//GUTSCHRIFT ART
				$("#order_credit_update_dialog_reason").val( $credit['creditposition'] [$pos_index] ['reason_id'] );

				//GUTSCHRIFT DETAIL
				$("#order_credit_update_dialog_reason_detail").val( $credit['creditposition'] [$pos_index] ['reason_description'] );

				//GUTSCHRIFT Gross
				var $credit_gross = $credit['creditposition'] [$pos_index] ['gross'];
				$("#order_credit_update_dialog_gross").val( $credit_gross.replace(".", ",") );
			}
			
			$("#order_creditposition_set_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { order_creditposition_set( $credit_position_id );} },
					{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
				],
				closeOnEscape: false,
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:$dialog_title,
				width:400
			});		
		});
	}

	function order_creditposition_set( $credit_position_id )
	{
		var $creditgross = ($("#order_credit_update_dialog_gross").val().replace(/,/g, "."))*1;
		
		if ($creditgross == 0 || isNaN($creditgross) )
		{
			alert("Bitte eine korrekte Gutschriftsumme größer 0 angeben!");
			$("#order_credit_update_dialog_gross").focus();
			return;
		}
		
		//CHECK IF REASON IS SELECTED
		if ($("#order_credit_update_dialog_reason").val() == 0)
		{
			alert("Bitte eine Gutschriftart festlegen!");
			$("#order_credit_update_dialog_reason").focus();
			return;
		}
		//CHECK FOR DETAIL FOR "Sonstige"
		if ($("#order_credit_update_dialog_reason").val() == 4 && $("#order_credit_update_dialog_reason_detail").val() == "")
		{
			alert("Bitte eine Erläuterung zur Gutschrift `Sonstige` angeben!");
			$("#order_credit_update_dialog_reason_detail").focus();
			return;
		}

		$creditnet= $creditgross/((orders[$credit["order_id"]]["VAT"]/100)+1);

		var post_object = new Object();
		
		if ( $credit_position_id == 0 )
		{ 
			post_object['API'] 					= 'shop';
			post_object['APIRequest'] 			= 'OrderReturnSet'; // => HAS TO BE RENAMED TO OrderCreditSet
			post_object['credit_action'] 		= 'credit_position_add';
			post_object['credit_id'] 			= $credit['id_shop_order_credit'];
			post_object['reason_id'] 			= $("#order_credit_update_dialog_reason").val();
			post_object['reason_description'] 	= $("#order_credit_update_dialog_reason_detail").val();
			post_object['gross'] 				= $creditgross;
			post_object['net'] 					= $creditnet.toFixed(2);

		}
		else
		{
			post_object['API'] 												= 'shop';
			post_object['APIRequest'] 										= 'OrderReturnSet'; // => HAS TO BE RENAMED TO OrderCreditSet
			post_object['credit_action'] 									= 'credit_position_update';
			post_object['creditposition_id'] 								= $credit_position_id;
			//FIELDS FOR UPDATE IN TABLE MUST HAVE PREFIX: shop_orders_credits_positions_
			post_object['shop_orders_credits_positions_gross'] 				= $creditgross;
			post_object['shop_orders_credits_positions_net'] 				= $creditnet.toFixed(2);
			post_object['shop_orders_credits_positions_reason_id'] 			= $("#order_credit_update_dialog_reason").val();
			post_object['shop_orders_credits_positions_reason_description'] = $("#order_credit_update_dialog_reason_detail").val();
		}
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
	
	
			$("#order_creditposition_set_dialog").dialog("close");
			order_credit_start( $credit['id_shop_order_credit'] );

		});
	}
	
	
	function order_returns_update( element_id )
	{
		var return_id = returns['id_return'];
		//STRIP FIELDNAME
		var element = element_id.substr(14);
		// DATE TO TIMESTAMP
		if (element == 'date_return' || element == 'date_refund' || element == 'ebay_demand_closing1' || element == 'ebay_demand_closing2')
		{
			var fieldvalue = Math.round($('#'+element_id).datepicker('getDate') / 1000);
		}
		else if (element == 'refund' || element == 'refund_shipment' || element == 'refund_order_shipment')
		{
			var fieldvalue = ($('#'+element_id).val().replace(/,/g, '.'))*1;
		}
		else if (element == 'state')
		{
		//	if ($("#order_returns_ebay_fee_refund").is(":checked")) fieldvalue=1; else fieldvalue=0;
			var fieldvalue = $('#'+element_id).val();
		}
		else if(element == 'refund_aufid')
		{
			var fieldvalue = $('#'+element_id).val();
		}

		else
		{
			var fieldvalue=$('#'+element_id).val();
		}

		//CHECK IF VALUE HAS CHANGED
		if (fieldvalue != returns[element])
		{
			
			$post_field 									= new Object();
			$post_field['API']								= 'shop';
			$post_field['APIRequest']						= 'OrderReturnSet';
			$post_field['credit_action']					= 'return_update';
			$post_field['return_id']						= returns['id_return'];
			$post_field['shop_orders_returns_'+element]		= fieldvalue;
		
			wait_dialog_show();
			$.post('<?php echo PATH; ?>soa2/index.php', $post_field, function ($data)
			{
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				wait_dialog_hide();
				order_credit_start( $credit['id_shop_order_credit']);
		
				
/*								//CALL PAYMENTNOTIFICATIONHANDLER
								field = new Object();
								
								field["API"]="payments";
								field["APIRequest"]="PaymentNotificationHandler";
								field["mode"]="OrderReturn";
								field["orderid"]=returns["order_id"];
								field["returnid"]=return_id;
								field["order_event_id"]=id_event;
								
							//	wait_dialog_show();
								$.post("<?php echo PATH; ?>soa2/index.php", 
								field,
								function ($data)
								{
									
									wait_dialog_hide();
									try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
									var $Ack = $xml.find("Ack").text();
									if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}
									
								//	alert("OK3");
									show_status("Änderung durchgeführt, Rückzahlung wurde gebucht!");
									order_returns_dialog(returns["id_return"]);
								});
						*/	
			}); // WRITE ORDERRETURNUPDATE
		}
	}
	
	
	function order_returns_state_check()
	{
		//FALL GESCHLOSSEN
		if ( $("#order_returns_state").val() == 1 )
		{
			//CHECK MANDANTORY FIELDS
				//RÜCKSENDUNG ERHALTEN
			if ( $("#order_returns_date_return").val() == 0 || $("#order_returns_date_return").val() == "" )
			{
				$("#order_returns_date_return").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da noch keine Rücksendung durch den Kunden vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}
				//RÜCKSENDUNG ERHALTEN
			if ( $("#order_returns_date_refund").val() == 0 || $("#order_returns_date_refund").val() == "" )
			{
				$("#order_returns_date_refund").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da noch keine Erstattung/Gutschrift vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}
				//AUFID gespeichert
			if ( $("#order_returns_refund_aufid").val() == "" )
			{
				$("#order_returns_refund_aufid").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da keine IDIMS Erstattungs AUF ID vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}

				// CHECK OB ARTIKEL MIT RÜCKGABEGRUND "SONSTIGE" EINE BESCHREIBUNG DER RÜCKSENDUNG HABEN
			for ( var $index in returns["returnitem"] )
			{
				if ( returns["returnitem"][$index]["return_reason"] == 100 && returns["returnitem"][$index]["return_reason_description"] == "" )
				{
					msg_box("Der Fall kann nicht geschlossen werden, da nicht  bei allen Artikeln mit dem Rückgabegrund ´sonstige´ eine Detailinfo vorliegt!");
					$("#order_returns_state").val(0);
					return;
				}
			}
						
		}

		order_returns_update("order_returns_state");

	//	order_credit_start( $credit['id_shop_order_credit'] );

	//	update_view(returns["order_id"]);		

	}


	function order_returnitem_set_dialog( $return_item_id )
	{
		if ( typeof( $return_item_id ) === 'undefined' )
		{
			$return_item_id = 0;
		}
		
		
		// WENN $return_item_id == 0 => addexchangeitem 
		// WENN $return_item_id != 0 => updateexchangeitem 
		
		
		//GET ORDER ITEMS
		$postfield 					= new Object;
		$postfield['API'] 			= 'shop',
		$postfield['APIRequest'] 	= 'OrderDetailGet';
		$postfield['OrderID'] 		= $credit['order_id'];
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", $postfield, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#order_returnitem_set_dialog").length == 0)
			{
				$("body").append('<div id="order_returnitem_set_dialog" style="display:none">');
			}
			
			var html = '';
			
			html+='<table>';
			html+='<tr>';
			html+='	<th>Artikel</th>';
			html+='	<td><select id="order_returnitem_add_item" size="1">';
			html+='		<option value="">Bitte einen Artikel wählen</option>';
			// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE STEHEN
			
			$xml.find("Item").each( function ()
			{
				if ( typeof( returns["returnitem"] ) !== "undefined" )
				{
					
					var matchingItem = false;
					
					if ( $return_item_id == 0 ) 
					{
						// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE/UMTAUSCH STEHEN
						for ( var $index in returns["returnitem"] ) 
						{
							if ( $(this).find("OrderItemID").text() == returns["returnitem"][$index]["shop_orders_items_id"] )
							{
								matchingItem = true;
							}
						}
					}
					//ES WERDEN NUR AUSGEWÄHLTE ARTIKEL UND ANDERE NOCH NICHT GEWÄHLTE ARTIKEL ANGEZEIT
					else
					{
						for ( var $index in returns["returnitem"] ) 
						{
							if ( $(this).find("OrderItemID").text() == returns["returnitem"][$index]["shop_orders_items_id"] )
							{
								if ( returns["returnitem"][$index]['id_returnitem'] != $return_item_id )
								{
									matchingItem = true;
								}
							}
						}
					}
					
					if ( !matchingItem ) 
					{
						html+='		<option value="'+$(this).find("OrderItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
					}
				}
				else
				{
					html+='		<option value="'+$(this).find("OrderItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';					
				}
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Anzahl</th>';
			html+='	<td><input type="text" size="2" id="order_returnitem_add_amount" value = 1 /><input type="hidden" id="order_returnitem_add_amount_max" value ='+$(this).find("OrderItemAmount").text()+' /></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabegrund</th>';
			html+='	<td><select id="order_returnitem_add_reason" size="1">';
			html+='		<option value=0>Bitte Umtauschgrund wählen</option>';
			
			$.each(ReturnsReasons, function($key, returnreason)
			{
				html+='<option value='+returnreason["id_returnreason"]+'  title="'+returnreason["description"]+'">'+returnreason["title"]+'</option>';
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabeerläuterung</th>';
			html+='	<td><textarea id="order_returnitem_add_reason_description" cols="20" rows="5"></textarea></td>';
			html+='</tr>';
			html+='</table>';
			
			$("#order_returnitem_set_dialog").html(html);
			
			//VORBELEGEN DER FELDER
			if ( $return_item_id != 0 )
			{
				for ( var $index in returns['returnitem'] )
				{
					if ( returns['returnitem'][$index]['id_returnitem'] == $return_item_id )
					{
						//RETURNITEM
						$('#order_exchangeitem_add_item').val( returns['returnitem'][$index]['shop_orders_items_id'] );
						$('#order_exchangeitem_add_amount').val( returns['returnitem'][$index]['amount'] );
						$('#order_exchangeitem_add_reason').val( returns['returnitem'][$index]['return_reason'] );
						$('#order_exchangeitem_add_reason_description').val( returns['returnitem'][$index]['return_reason_description'] );
					}
				}

			} // ENDE - VORBELEGEN DER FELDER


			// DEFINE DILOG TITLE
			if ( $return_item_id == 0 ) 
			{
				var $dialog_title = 'Rückgabe: Artikel hinzufügen';
			}
			else
			{
				var $dialog_title = 'Rückgabe: Artikel bearbeiten';
			}


			if ( !$("#order_returnitem_set_dialog").is(":visible") )
			{
				$("#order_returnitem_set_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_returnitem_set( $return_item_id );} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:$dialog_title,
					width:500
				});		
			}
		});
	}
	
	function order_returnitem_set( $return_item_id )
	{
		//CHECK USER INPUT
			//ITEM
		if ( $("#order_returnitem_add_item").val() == "" )
		{
			$("#order_returnitem_add_item").focus();
			msg_box("Bitte einen Artikel auswählen");
			return;
		}
			//ANZAHL
		if ( $("#order_returnitem_add_amount").val() == "" || $("#order_returnitem_add_amount").val() == 0 )
		{
			$("#order_returnitem_add_amount").focus();
			msg_box("Bitte eine gültige Anzahl der Artikel eingeben");
			return;
		}
			//RÜCKGABEGRUND
		if ( $("#order_returnitem_add_reason").val() == 0 )
		{
			$("#order_returnitem_add_reason").focus();
			msg_box("Bitte einen Rückgabegrund angeben");
			return;
		}
			//RÜCKGABEERLÄUTERUNG -> nur zwingend bei "sonstige"
		if ( $("#order_returnitem_add_reason").val() == 100 && $("#order_returnitem_add_reason_description").val() == '' )
		{
			$("#order_returnitem_add_reason_description").focus();
			msg_box("Bitte eine Erläuterung zur Rückgabe angeben");
			return;
		}
		//CHECKS FOR EXCHANGES
		if ( $credit['type'] == 'exchange' )
		{
			if ( $("#order_returnitem_add_exchange_itemID").val() == "" || $("#order_returnitem_add_exchange_itemID").val() == 0 )
			{
				$("#order_returnitem_add_exchange_MPN").focus();
				msg_box("Bitte einen Umtauschartikel angeben");
				return;
			}
			if ( $("#order_returnitem_add_exchange_amount").val() == "" || $("#order_returnitem_add_exchange_amount").val() == 0 )
			{
				$("#order_returnitem_add_exchange_amount").focus();
				msg_box("Bitte eine Anzahl der Umtauschartikel angeben");
				return;
			}
			
		}
		
		
		wait_dialog_show();
		$postfield = new Object;
		
		var $item_id = 0;
		for ( var $index in returns['returnitem'] )
		{
			if ( returns['returnitem'][$index]['shop_orders_items_id'] == $("#order_returnitem_add_item").val() )
			{
				$item_id = returns['returnitem'][$index]['item_id'];	
			}
		}
		
		
		// DATEN SCHREIBEN
		if ( $return_item_id == 0 )
		{
			$postfield['API'] 								= 'shop';
			$postfield['APIRequest'] 						= 'OrderReturnSet';
			$postfield['credit_action']						= 'return_item_add';
			$postfield['return_type'] 						= $credit['type'];
			$postfield['return_id'] 						= $credit['return_id'];
			$postfield['shop_orders_items_id'] 				= $item_id;
			$postfield['shop_orders_items_amount'] 			= $("#order_returnitem_add_amount").val();
			$postfield['return_reason'] 					= $("#order_returnitem_add_reason").val();
			$postfield['return_reason_description'] 		= $("#order_returnitem_add_reason_description").val();
		}
		else
		{
			$postfield['API'] 													= 'shop';
			$postfield['APIRequest'] 											= 'OrderReturnSet';
			$postfield['credit_action']											= 'return_item_update';
			$postfield['returnitem_id'] 										= $return_item_id;
			$postfield['shop_orders_returns_items_items_id']					= $item_id;
			$postfield['shop_orders_returns_items_amount'] 						= $("#order_returnitem_add_amount").val();
			$postfield['shop_orders_returns_items_return_reason'] 				= $("#order_returnitem_add_reason").val();
			$postfield['shop_orders_returns_items_return_reason_description'] 	= $("#order_returnitem_add_reason_description").val();
		}

		$.post("<?php echo PATH; ?>soa2/", $postfield, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$("#order_returnitem_set_dialog").dialog("close");
			order_credit_start( $credit['id_shop_order_credit'] );
			
		});
	}

	
	function order_exchangeitem_set_dialog( $return_item_id )
	{
		if ( typeof( $return_item_id ) === 'undefined' )
		{
			$return_item_id = 0;
		}
		
		
		// WENN $return_item_id == 0 => addexchangeitem 
		// WENN $return_item_id != 0 => updateexchangeitem 
		
		//GET ORDER ITEMS
		
		$postfield 					= new Object;
		$postfield['API'] 			= 'shop',
		$postfield['APIRequest'] 	= 'OrderDetailGet';
		$postfield['OrderID'] 		= $credit['order_id'];
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", $postfield, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#order_exchangeitem_set_dialog").length == 0)
			{
				$("body").append('<div id="order_exchangeitem_set_dialog" style="display:none">');
			}
			
			var html = '';
			
			html+='<table>';
			html+='<tr>';
			html+='	<th>Artikel</th>';
			html+='	<td colspan="2"><select id="order_exchangeitem_add_item" size="1" onchange="order_exchangeitem_add_setprices(this.value);">';
			html+='		<option value="">Bitte einen Artikel wählen</option>';
			
			
			orderitems = null;
			orderitems = new Object;
			
			$xml.find("Item").each( function ()
			{
				var $shop_order_item_id = $(this).find("OrderItemID").text();
				orderitems[$shop_order_item_id] = new Object;
				orderitems[$shop_order_item_id]["item_id"] = $(this).find("OrderItemItemID").text();
				orderitems[$shop_order_item_id]["amount"] = $(this).find("OrderItemAmount").text();
				orderitems[$shop_order_item_id]["CurrencyCode"] = $(this).find("OrderItemCurrency_Code").text();
				orderitems[$shop_order_item_id]["ExchangeRateToEUR"] = $(this).find("OrderItemExchangeRateToEUR").text();
//				orderitems[ItemID]["PriceGross"] = $(this).find("orderItemPriceGross").text();
				orderitems[$shop_order_item_id]["PriceNet"] = $(this).find("orderItemPriceNet").text();
	
				
				if ( typeof( returns["returnitem"] ) !== "undefined" )
				{
					
					var matchingItem = false;
					
					if ( $return_item_id == 0 ) 
					{
						// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE/UMTAUSCH STEHEN
						for ( var $index in returns["returnitem"] ) 
						{
							if ( $(this).find("OrderItemID").text() == returns["returnitem"][$index]["shop_orders_items_id"] )
							{
								matchingItem = true;
							}
						}
					}
					//ES WERDEN NUR AUSGEWÄHLTE ARTIKEL UND ANDERE NOCH NICHT GEWÄHLTE ARTIKEL ANGEZEIT
					else
					{
						for ( var $index in returns["returnitem"] ) 
						{
							if ( $(this).find("OrderItemID").text() == returns["returnitem"][$index]["shop_orders_items_id"] )
							{
								if ( returns["returnitem"][$index]['id_returnitem'] != $return_item_id )
								{
									matchingItem = true;
								}
							}
						}
					}
					
					if ( !matchingItem ) 
					{
						html+='		<option value="'+$(this).find("OrderItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
					}
				}
				else
				{
					html+='		<option value="'+$(this).find("OrderItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';					
				}
			});
			
		
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Anzahl</th>';
			html+='	<td colspan="2"><input type="text" id="order_exchangeitem_add_amount" size="3" /></td>';
			html+='</tr><tr>';
			html+='	<th>Umtauschgrund</th>';
			html+='	<td colspan="2"><select id="order_exchangeitem_add_reason" size="1">';
			html+='		<option value=0>Bitte Umtauschgrund wählen</option>';
			
			$.each(ReturnsReasons, function($key, returnreason)
			{
				
				html+='<option value='+returnreason["id_returnreason"]+'  title="'+returnreason["description"]+'">'+returnreason["title"]+'</option>';
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Umtauscherläuterung</th>';
			html+='	<td colspan="2"><textarea id="order_exchangeitem_add_reason_description" cols="20" rows="5"></textarea></td>';
			html+='</tr><tr>';
			html+='	<th></th>';
			html+=' <th>brutto</th>';
			html+=' <th>netto</th>';
			html+='</tr><tr>';
			html+='	<th>Artikel Einzel-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span id="order_exchangeitem_add_FC_price" size="6" ></span></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span id="order_exchangeitem_add_FC_netto" size="6" ></span></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Artikel Einzel-VK-Preis</th>';
			html+='	<td>EUR <span id="order_exchangeitem_add_EUR_price" size="6" ></span></td>';
			html+='	<td>EUR <span id="order_exchangeitem_add_EUR_netto" size="6" ></span></td>';
			html+='</tr><tr>';
			html+='	<th>Artikel Gesamt-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_FC_price_total"></span></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_FC_netto_total"></span></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Artikel Gesamt-VK-Preis</th>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_EUR_price_total"></span></td>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_EUR_netto_total"></span></td>';
			html+='	<input type="hidden" id = "order_exchangeitem_exchangerate">';
			html+='</tr><tr>';
			html+='	<th>Umtausch-MPN</th>';
			html+='	<td colspan="2"><input type="text" id="order_exchangeitem_add_exchange_MPN" size="10" onchange="order_exchangeitem_changeMPN(this.value)" />';
				html+='<input type="hidden" id="order_exchangeitem_add_exchange_itemID" /></td>';
			html+='</tr><tr>';
			html+='	<th>Umtausch-Artikelbezeichnung</th>';
			html+='	<td colspan="2"><span id="order_exchangeitem_add_exchange_title"></span></td>';
			html+='</tr><tr>';
			html+='	<th>Umtausch-Anzahl</th>';
			html+='	<td colspan="2"><input type="text" id="order_exchangeitem_add_exchange_amount" size="3" onchange="exchange_orderpositions_setPrices(\'amount\');"/></td>';
			html+='</tr><tr>';
			html+='	<th></th>';
			html+=' <th>brutto</th>';
			html+=' <th>netto</th>';
			html+='</tr><tr>';
// VORBELEGUNG DURCH LISTENPREIS AUS SHOP
	// ANHAND GETITEM-SERVICE??			
			html+='	<th>Umtausch-Artikel Einzel-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <input type="text" id="order_exchangeitem_add_exchange_FC_price" size="6" onchange="ex_get_netto_from_FCbrutto(this.value)" /></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <input type="text" id="order_exchangeitem_add_exchange_FC_netto" size="6" onchange="ex_get_netto_from_FCnetto(this.value)" /></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Umtausch-Artikel Einzel-VK-Preis</th>';
			html+='	<td>EUR <input type="text" id="order_exchangeitem_add_exchange_EUR_price" size="6" /></td>';
			html+='	<td>EUR <input type="text" id="order_exchangeitem_add_exchange_EUR_netto" size="6" /></td>';
			html+='</tr><tr>';
			html+='	<th>Umtausch-Artikel Gesamt-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_exchange_FC_price_total"></span></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_exchange_FC_netto_total"></span></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Umtausch-Artikel Gesamt-VK-Preis</th>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_exchange_EUR_price_total"></span></td>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_exchange_EUR_netto_total"></span></td>';
			html+='</tr>';
			html+='</table>';
			
			$("#order_exchangeitem_set_dialog").html(html);
			
			//VORBELEGEN DER FELDER
			if ( $return_item_id != 0 )
			{
				for ( var $index in returns['returnitem'] )
				{
					if ( returns['returnitem'][$index]['id_returnitem'] == $return_item_id )
					{
						//RETURNITEM
						$('#order_exchangeitem_add_item').val( returns['returnitem'][$index]['shop_orders_items_id'] );
						$('#order_exchangeitem_add_amount').val( returns['returnitem'][$index]['amount'] );
						$('#order_exchangeitem_add_reason').val( returns['returnitem'][$index]['return_reason'] );
						$('#order_exchangeitem_add_reason_description').val( returns['returnitem'][$index]['return_reason_description'] );
						order_exchangeitem_add_setprices( returns['returnitem'][$index]['shop_orders_items_id'] );

						//EXCHANGEORDERITEM
						var $exchange_shop_orders_item = returns['returnitem'][$index]['exchange_shop_orders_item'];
						for ( var $index2 in exchange['exchangeorderitem'] )
						{
							if ( exchange['exchangeorderitem'][$index2]['id'] == $exchange_shop_orders_item )	
							{
								$('#order_exchangeitem_add_exchange_MPN').val( exchange['exchangeorderitem'][$index2]['exchangeorderitem_MPN'] );
								$('#order_exchangeitem_add_exchange_title').text( exchange['exchangeorderitem'][$index2]['exchangeorderitem_title'] );
								var $exchangeitemnettoFC = exchange['exchangeorderitem'][$index2]['netto']*1;
								$('#order_exchangeitem_add_exchange_FC_netto').val( $exchangeitemnettoFC.toFixed(2).toString().replace(".", ",") );
								ex_get_netto_from_FCnetto( $('#order_exchangeitem_add_exchange_FC_netto').val() );
							}
						}
					}
				}

			} // ENDE - VORBELEGEN DER FELDER


			// DEFINE DILOG TITLE
			if ( $return_item_id == 0 ) 
			{
				var $dialog_title = 'Umtausch: Artikel hinzufügen';
			}
			else
			{
				var $dialog_title = 'Umtausch: Artikel bearbeiten';
			}

			if (!$("#order_exchangeitem_set_dialog").is(":visible"))
			{
				$("#order_exchangeitem_set_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_exchangeitem_set( $return_item_id );} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:$dialog_title,
					width:550
				});		
			}
		});
	}

	function order_exchangeitem_set( $return_item_id )
	{
		// CHECK USER INPUT
		if ( $("#order_exchangeitem_add_item").val()=="" )
		{
			$("#order_exchangeitem_add_item").focus();
			msg_box("Bitte einen Artikel auswählen");
			return;
		}
			//ANZAHL
		if ( $("#order_exchangeitem_add_amount").val() == "" || $("#order_exchangeitem_add_amount").val() == 0 )
		{
			$("#order_exchangeitem_add_amount").focus();
			msg_box("Bitte eine gültige Anzahl der Artikel eingeben");
			return;
		}
			//UMTAUSCHGRUND
		if ( $("#order_exchangeitem_add_reason").val() == 0 )
		{
			$("#order_exchangeitem_add_reason").focus();
			msg_box("Bitte einen Rückgabegrund angeben");
			return;
		}
			//UMTAUSCHERLÄUTERUNG -> nur zwingend bei "sonstige"
		if ( $("#order_exchangeitem_add_reason").val() == 100 && $("#order_exchangeitem_add_reason_description").val() == "" )
		{
			$("#order_exchangeitem_add_reason_description").focus();
			msg_box("Bitte eine Erläuterung zum Umtausch angeben");
			return;
		}
		//CHECKS FOR EXCHANGES
		//UMTAUSCH ARTIKEL
		if ( $("#order_exchangeitem_add_exchange_itemID").val() == "" || $("#order_exchangeitem_add_exchange_itemID").val() == 0 )
		{
			$("#order_returnitem_add_exchange_MPN").focus();
			msg_box("Bitte einen Umtauschartikel angeben");
			return;
		}
		//UMTAUSCH ANZAHL
		if ( $("#order_exchangeitem_add_exchange_amount").val() == "" || $("#order_exchangeitem_add_exchange_amount").val() == 0 )
		{
			$("#order_exchangeitem_add_exchange_amount").focus();
			msg_box("Bitte eine Anzahl der Umtauschartikel angeben");
			return;
		}
		//UMTAUSCH ARTIKELPREIS
		if ( $("#order_exchangeitem_add_exchange_FC_netto").val() == "" || $("#order_exchangeitem_add_exchange_FC_netto").val() == 0 )
		{
			$("#order_exchangeitem_add_exchange_FC_brutto").focus();
			msg_box("Bitte den Preis des Umtauschartikels eingeben");
			return;
		}
		
		var $postfield = new Object;
		// DATEN SCHREIBEN
		if ( $return_item_id == 0 )
		{
			//ADD
			$postfield['API'] 								= 'shop';
			$postfield['APIRequest'] 						= 'OrderReturnSet';
			$postfield['credit_action']						= 'return_item_add';
			$postfield['return_type'] 						= $credit['type'];
			$postfield['return_id'] 						= $credit['return_id'];
			//$postfield['shop_orders_items_id'] 				= orderitems[$("#order_exchangeitem_add_item").val()]["id"];
			$postfield['shop_orders_items_id'] 				= $("#order_exchangeitem_add_item").val();
			$postfield['shop_orders_items_amount'] 			= $("#order_exchangeitem_add_amount").val();
			$postfield['return_reason'] 					= $("#order_exchangeitem_add_reason").val();
			$postfield['return_reason_description'] 		= $("#order_exchangeitem_add_reason_description").val();
			$postfield['shop_orders_exchange_id_item'] 		= $("#order_exchangeitem_add_exchange_itemID").val();
			$postfield['shop_orders_exchange_item_amount'] 	= $("#order_exchangeitem_add_exchange_amount").val();
			$postfield['shop_orders_exchange_item_FCnetto'] = ($("#order_exchangeitem_add_exchange_FC_netto").val().toString().replace(/,/g, "."))*1;

		}
		else
		{
			//UPDATE
			$postfield['API'] 													= 'shop';
			$postfield['APIRequest'] 											= 'OrderReturnSet';
			$postfield['credit_action']											= 'return_item_update';
			$postfield['returnitem_id'] 										= $return_item_id;
			$postfield['shop_orders_returns_items_shop_orders_items_id'] 		= orderitems[$("#order_exchangeitem_add_item").val()]["id"];
			$postfield['shop_orders_returns_items_amount'] 						= $("#order_exchangeitem_add_amount").val();
			$postfield['shop_orders_returns_items_return_reason'] 				= $("#order_exchangeitem_add_reason").val();
			$postfield['shop_orders_returns_items_return_reason_description'] 	= $("#order_exchangeitem_add_reason_description").val();
			$postfield['shop_orders_exchange_items_item_id'] 					= $("#order_exchangeitem_add_exchange_itemID").val();
			$postfield['shop_orders_exchange_items_amount'] 					= $("#order_exchangeitem_add_exchange_amount").val();
			$postfield['shop_orders_exchange_items_netto'] 						= ($("#order_exchangeitem_add_exchange_FC_netto").val().toString().replace(/,/g, "."))*1;
			$postfield['shop_orders_exchange_items_price'] 						= ($("#order_exchangeitem_add_exchange_FC_price").val().toString().replace(/,/g, "."))*1;
		}
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", $postfield, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$('#order_exchangeitem_set_dialog').dialog('close');
			order_credit_start( $credit['id_shop_order_credit'] );
		});

	}

	function order_exchangeitem_add_setprices( $shop_orders_items_id )
	{
		//ERSTBELEGUNG DER FELDER NACH AUSWAHLE DES UMZUTAUSCHENDEN ARTIKELS

		//CURRENCYCODE
		$(".order_exchangeitem_add_CurrencyCode").text(orderitems[$shop_orders_items_id]["CurrencyCode"]);
		
		//AMOUNT
		var amount = orderitems[$shop_orders_items_id]["amount"];
		$("#order_exchangeitem_add_amount").val(amount);
		$("#order_exchangeitem_add_exchange_amount").val(amount);
		
		//VK EINZEL NETTO
		var net = (orderitems[$shop_orders_items_id]["PriceNet"].toString().replace(/,/g, "."))*1;
		$("#order_exchangeitem_add_FC_netto").text(net.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_netto").val(net.toFixed(2).toString().replace(".", ","));
		
		//VK EINZEL BRUTTO
		var gross = net * mwstmultiplier;
		$("#order_exchangeitem_add_FC_price").text(gross.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_price").val(gross.toFixed(2).toString().replace(".", ","));

		//VK GESAMT NETTO
		var total_net = net * amount;
		$("#order_exchangeitem_add_FC_netto_total").text(total_net.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_netto_total").text(total_net.toFixed(2).toString().replace(".", ","));
		
		//VK GESAMT BRUTTO
		var total_gross = net * amount * mwstmultiplier;
		$("#order_exchangeitem_add_FC_price_total").text(total_gross.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_price_total").text(total_gross.toFixed(2).toString().replace(".", ","));
		
		if (orderitems[$shop_orders_items_id]["CurrencyCode"]!="EUR") 
		{
			$(".exchangeitem_add_EUR_col").show();
		}
		else
		{
			$(".exchangeitem_add_EUR_col").hide();
		}
		
		var exchangerate = orderitems[$shop_orders_items_id]["ExchangeRateToEUR"];
		$("#order_exchangeitem_exchangerate").val(exchangerate);
		
		//VK EUR EINZEL NETTO
		var netEUR = net / exchangerate;
		$("#order_exchangeitem_add_EUR_netto").text(netEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_netto").val(netEUR.toFixed(2).toString().replace(".", ","));
		
		//VK EUR EINZEL BRUTTO
		var grossEUR = net / exchangerate * mwstmultiplier;
		$("#order_exchangeitem_add_EUR_price").text(grossEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_price").val(grossEUR.toFixed(2).toString().replace(".", ","));

		//VK EUR GESAMT NETTO
		var total_netEUR = net / exchangerate * amount;
		$("#order_exchangeitem_add_EUR_netto_total").text(total_netEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_netto_total").text(total_netEUR.toFixed(2).toString().replace(".", ","));
		
		//VK EUR GESAMT BRUTTO
		var total_grossEUR = net / exchangerate * amount * mwstmultiplier;
		$("#order_exchangeitem_add_EUR_price_total").text(total_grossEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_price_total").text(total_grossEUR.toFixed(2).toString().replace(".", ","));
	}
	
	function order_exchangeitem_changeMPN(MPN)
	{
		wait_dialog_show();
		//var MPN = $("#change_orderpositions_MPN").val();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ShopItemGet", MPN:MPN},
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#order_exchangeitem_add_exchange_itemID").val($xml.find("id_item").text());
					$("#order_exchangeitem_add_exchange_title").text($xml.find("title").text());
				}
				else
				{
					$("#order_exchangeitem_add_exchange_itemID").val(0);
					$("#order_exchangeitem_add_exchange_title").text("ARTIKEL EXISTIERT NICHT!");
					$("#order_exchangeitem_add_exchange_MPN").focus();
				}
			}
		);
	}
	

/*	
	function order_returnitem_update_dialog(return_id, orderid, returnitem_item_id)
	{
//		alert(returnitem_item_id);
		//GET ORDER ITEMS
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet", OrderID:returns["order_id"]},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#order_returnitem_update_dialog").length == 0)
			{
				$("body").append('<div id="order_returnitem_update_dialog" style="display:none">');
			}
			
			var html = '';
			
			html+='<table>';
			html+='<tr>';
			html+='	<th>Artikel</th>';
			html+='	<td><select id="order_returnitem_update_item" size="1">';
			//html+='		<option value="">Bitte einen Artikel wählen</option>';
			
			// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE STEHEN & der aktuell zu bearbeitende Artikel
			$xml.find("Item").each( function ()
			{
				if (typeof(returns["returnitem"]) !== "undefined" )
				{
					var matchingItem = false;
					//for (var i  = 0; i<returns["returnitem"].length; i++)
					for ( var $index in returns["returnitem"] )
					{
						if ($(this).find("OrderItemMPN").text()==returns["returnitem"][$index]["MPN"] && $(this).find("OrderItemItemID").text()!=returnitem_item_id) matchingItem = true;
					}
					if (!matchingItem) html+='		<option value="'+$(this).find("OrderItemItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
				}
				else
				{
					html+='		<option value="'+$(this).find("OrderItemMPN").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';					
				}
			});
			
			//GET RETURNITEM DATA
			//for (var i  = 0; i<returns["returnitem"].length; i++)
			for ( var $index in returns["returnitem"] )
			{
				if (returns["returnitem"][$index]["item_id"]==returnitem_item_id)
				{
					var amount = returns["returnitem"][$index]["amount"];
					var reason = returns["returnitem"][$index]["return_reason"];
					var reason_description = returns["returnitem"][$index]["return_reason_description"];
					var id_returnitem = returns["returnitem"][$index]["id_returnitem"];
				}
			}
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Anzahl</th>';
			html+='	<td><input type="text" size="2" id="order_returnitem_update_amount" value = '+amount+' /><input type="hidden" id="order_returnitem_update_amount_max" value ='+$(this).find("OrderItemAmount").text()+' /></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabegrund</th>';
			html+='	<td><select id="order_returnitem_update_reason" size="1">';
			html+='		<option value=0>Bitte Rückgabegrund wählen</option>';
			
			$.each(ReturnsReasons, function($key, returnreason)
			{
				//if (reason == returnreason["id_returnreason"]) var $selected = 'selected'; else var $selected = '';
				var $selected = '';
				html+='<option value='+returnreason["id_returnreason"]+' title="'+returnreason["description"]+'" '+$selected+'>'+returnreason["title"]+'</option>';
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabeerläuterung</th>';
			html+='	<td><textarea id="order_returnitem_update_reason_description" cols="20" rows="5">'+reason_description+'</textarea></td>';
			html+='</tr>';
			html+='</table>';
			
			$("#order_returnitem_update_dialog").html(html);
			
			//SELECTs vorbelegen
			$("#order_returnitem_update_reason").val(reason);
			$("#order_returnitem_update_item").val(returnitem_item_id);
			
			if (!$("#order_returnitem_update_dialog").is(":visible"))
			{
				$("#order_returnitem_update_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_returnitem_update(id_returnitem);} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Rückgabe Artikel bearbeiten",
					width:500
				});		
			}
		});
	}

	function order_returnitem_update( $returnitem_id )
	{
		//alert(returnitem_id);
		
		//CHECK USER INPUT
			//ITEM
		if ( $("#order_returnitem_update_item").val() == "" )
		{
			$("#order_returnitem_update_item").focus();
			msg_box("Bitte einen Artikel auswählen");
			return;
		}
			//ANZAHL
		if ( $("#order_returnitem_update_amount").val( ) == "" || $("#order_returnitem_update_amount").val() == 0 )
		{
			$("#order_returnitem_update_amount").focus();
			msg_box("Bitte eine gültige Anzahl der Artikel eingeben");
			return;
		}
			//RÜCKGABEGRUND
		if ( $("#order_returnitem_update_reason").val() == 0 )
		{
			$("#order_returnitem_update_reason").focus();
			msg_box("Bitte einen Rückgabegrund angeben");
			return;
		}
			//RÜCKGABEERLÄUTERUNG -> nur zwingend bei "sonstige"
		if ( $("#order_returnitem_update_reason").val() == 100 && $("#order_returnitem_update_reason_description").val() == '' )
		{
			$("#order_returnitem_update_reason_description").focus();
			msg_box("Bitte eine Erläuterung zur Rückgabe angeben");
			return;
		}

		// DATEN SCHREIBEN
		$postfield 													= new Object;
		$postfield['API'] 											= 'shop';
		$postfield['APIRequest'] 									= 'OrderReturnSet';
		$postfield['credit_action']									= 'return_item_update';
		$postfield['return_type'] 									= $credit['type'];
		$postfield['returnitem_id'] 								= $returnitem_id;
		$postfield['shop_orders_returns_items_items_id'] 			= $("#order_returnitem_update_item").val();
		$postfield['shop_orders_returns_items_amount'] 				= $("#order_returnitem_update_amount").val();
		$postfield['shop_orders_returns_items_reason_id'] 			= $("#order_returnitem_update_reason").val();
		$postfield['shop_orders_returns_items_reason_description'] 	= $("#order_returnitem_update_reason_description").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", $postfield, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$("#order_returnitem_update_dialog").dialog("close");
			
			order_credit_start( $credit['id_shop_order_credit'] );
		});
	}
*/
	function exchange_orderpositions_setPrices(netto)
	{

		//IF FUNCTION  CALL from amount
		if (netto == "amount") netto = $("#order_exchangeitem_add_exchange_EUR_netto").val();
		
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		//BERECHNUNGEN LAUFEN IMMER VOM EINZELPREIS EUR NETTO
		var net = (netto.toString().replace(/,/g, "."))*1;
		
		var amount=$("#order_exchangeitem_add_exchange_amount").val()*1;
		
		var exchangerate=$("#order_exchangeitem_exchangerate").val()*1;

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
			
			order_exchangeitem_add_exchange_FC_price
			$("#order_exchangeitem_add_exchange_FC_price").val(FCgross.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_FC_netto").val(FCnet.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_price").val(gross.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_netto").val(net.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_FC_price_total").text(FCgrossTotal.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_FC_netto_total").text(FCnetTotal.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_price_total").text(grossTotal.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_netto_total").text(netTotal.toFixed(2).toString().replace(".", ","));
		
	}
	
	function set_exchange_amount()
	{
		exchange_orderpositions_setPrices($("#order_exchangeitem_add_exchange_EUR_netto").val().replace(/,/g, ".")*1);
	}

	
	function ex_get_netto_from_brutto(brutto)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var gross=(brutto.replace(/,/g, "."))*1;
		
		if (gross!=0) var netto = gross/mwstmultiplier; else var netto = 0;
		
		exchange_orderpositions_setPrices(netto);
		
	}
	
	function ex_get_netto_from_FCnetto(FCnetto)
	{
		var exchangerate=$("#order_exchangeitem_exchangerate").val()*1;
		var FCnet=(FCnetto.replace(/,/g, "."))*1;

		if (FCnet!=0) var netto = FCnet/exchangerate; else var netto = 0;
		exchange_orderpositions_setPrices(netto);
	
	}
	
	function ex_get_netto_from_FCbrutto(FCbrutto)
	{

		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var exchangerate=$("#order_exchangeitem_exchangerate").val()*1;
		var FCgross=(FCbrutto.replace(/,/g, "."))*1;
		
		if (FCgross!=0) var netto = FCgross /exchangerate/ mwstmultiplier; else var netto = 0;
		exchange_orderpositions_setPrices(netto);
		
	}


	function send_DHLretourlabel(dhl_parameter)
	{
		wait_dialog_show();		
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu_test", OrderID:credit['order_id']},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
					//DHL RETOUR LABEL 
					if ($("#DHLretourlabelDialog").length == 0)
					{
						$("body").append('<div id="DHLretourlabelDialog" style="display:none">');
					}
					
					var $html = '';					
					$html += '<table>';
					$html += '<tr>';
					$html += '	<td colspan="2">Adresszusatz / Postnummer<br />';
					$html += '	<input type="text" size="50" id="DHLretourlabel_address_additional" /></td>';
					$html += '</tr>';
					$html += '<tr>';
					$html += '	<td>Vorname<br />';
					$html += '	<input type="text" size="20" id="DHLretourlabel_address_firstname" /></td>';
					$html += '	<td>Nachname<br />';
					$html += '	<input type="text" size="20" id="DHLretourlabel_address_lastname" /></td>';
					$html += '</tr>';
					$html += '<tr>';
					$html += '	<td>Straße / Packstation<br />';
					$html += '	<input type="text" size="20" id="DHLretourlabel_address_street" /></td>';
					$html += '	<td>Nummer / Packstat.Nr.<br />';
					$html += '	<input type="text" size="3" id="DHLretourlabel_address_number" /></td>';
					$html += '</tr>';
					$html += '<tr>';
					$html += '	<td>Postleitzahl<br />';
					$html += '	<input type="text" size="6" id="DHLretourlabel_address_zip" /></td>';
					$html += '	<td>Stadt<br />';
					$html += '	<input type="text" size="20" id="DHLretourlabel_address_city" /></td>';
					$html += '</tr>';
					$html += '<tr>';
					$html += '	<td colspan="2">Land<br />';
					$html += '	<input type="text" size="50" id="DHLretourlabel_address_country" /></td>';
					$html += '</tr>';
					$html += '<tr>';
					$html += '	<td colspan="2">E-Mail Adresse<br />';
					$html += '	<input type="text" size="50" id="DHLretourlabel_usermail" /></td>';
					$html += '</tr>';
					$html += '</table>';

				$("#DHLretourlabelDialog").html($html);
				
				         
				if ($xml.find("ship_adr_id").text()==0)
				{
					$("#DHLretourlabel_address_company").val($xml.find("bill_adr_company").text());
					$("#DHLretourlabel_address_additional").val($xml.find("bill_adr_additional").text());
					$("#DHLretourlabel_address_firstname").val($xml.find("bill_adr_firstname").text());
					$("#DHLretourlabel_address_lastname").val($xml.find("bill_adr_lastname").text());
					$("#DHLretourlabel_address_street").val($xml.find("bill_adr_street").text());
					$("#DHLretourlabel_address_number").val($xml.find("bill_adr_number").text());
					$("#DHLretourlabel_address_zip").val($xml.find("bill_adr_zip").text());
					$("#DHLretourlabel_address_city").val($xml.find("bill_adr_city").text());
					$("#DHLretourlabel_address_country").val($xml.find("bill_adr_country").text());
					$("#DHLretourlabel_address_country_code").val($xml.find("bill_adr_country_code").text());
					$("#DHLretourlabel_usermail").val($xml.find("usermail").text());
				}
				else
				{
					$("#DHLretourlabel_address_company").val($xml.find("ship_adr_company").text());
					$("#DHLretourlabel_address_additional").val($xml.find("ship_adr_additional").text());
					$("#DHLretourlabel_address_firstname").val($xml.find("ship_adr_firstname").text());
					$("#DHLretourlabel_address_lastname").val($xml.find("ship_adr_lastname").text());
					$("#DHLretourlabel_address_street").val($xml.find("ship_adr_street").text());
					$("#DHLretourlabel_address_number").val($xml.find("ship_adr_number").text());
					$("#DHLretourlabel_address_zip").val($xml.find("ship_adr_zip").text());
					$("#DHLretourlabel_address_city").val($xml.find("ship_adr_city").text());
					$("#DHLretourlabel_address_country").val($xml.find("ship_adr_country").text());
					$("#DHLretourlabel_address_country_code").val($xml.find("ship_adr_country_code").text());
					$("#DHLretourlabel_usermail").val($xml.find("usermail").text());
				}

				$("#DHLretourlabelDialog").dialog
				({	buttons:
					[
						{ text: "DHL Retourlabel senden", click: function() { do_send_DHLretourlabel(dhl_parameter);} },
						{ text: "Beenden", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
				//	hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
				//	show: { effect: 'drop', direction: "up" },
					title:"DHL Retourlabel senden",
					width:400
				});		

				
			}
			else
			{
				show_status2(data);
			}
		});

	}

	function do_send_DHLretourlabel(dhl_parameter)
	{
		$("#DHLretourlabelDialog").dialog("close");
		save_DHLretourlabelID();
		
	
		//var href='https://amsel.dpwn.net/abholportal/gw/lp/portal/mapco/customer/RpOrder.action?delivery=RetourenLager01';
		var href='https://amsel.dpwn.net/abholportal/gw/lp/portal/mapco/customer/RpOrder.action?delivery='+dhl_parameter;
		href+='&SHIPMENT_REFERENCE='+orderid;
		href+='&ADDR_SEND_STREET_ADD='+orderid;
		href+='&ADDR_SEND_EMAIL='+escape($("#DHLretourlabel_usermail").val());
		href+='&ADDR_SEND_FIRST_NAME='+escape($("#DHLretourlabel_address_firstname").val());
		href+='&ADDR_SEND_LAST_NAME='+escape($("#DHLretourlabel_address_lastname").val());
		href+='&ADDR_SEND_NAME_ADD='+escape($("#DHLretourlabel_address_additional").val());
		href+='&ADDR_SEND_STREET='+escape($("#DHLretourlabel_address_street").val()+' '+$("#DHLretourlabel_address_number").val());
		href+='&ADDR_SEND_ZIP='+escape($("#DHLretourlabel_address_zip").val());
		href+='&ADDR_SEND_CITY='+escape($("#DHLretourlabel_address_city").val());
	//	alert(href);
		window.open(href);
		
	}
	
	function save_DHLretourlabelID()
	{
		$("#DHLretourlabelID_LabelID").val("");
		$("#DHLretourlabelIDDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { do_save_DHLretourlabelID();} },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"DHL Retourlabel ID zur Bestellung speichern",
			width:400
		});		
		
	}
	
	function do_save_DHLretourlabelID()
	{
		var LabelID = $("#DHLretourlabelID_LabelID").val();
		
		//check if Field is not empty
		if (LabelID != "")
		{
			$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "set_DHLRetourLabelID", OrderID:credit["order_id"], LabelID:LabelID, return_id:credit["return_id"]},
				function(data)
				{
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						show_status("Retourlabel ID wurde erfolgreich gespeichert");
						$("#DHLretourlabelIDDialog").dialog("close");
						update_view(orderid);
						order_returns_dialog(returns["id_return"]);
					}
					else
					{
						show_status2(data);
					}
				}
			);
			
		}
		else
		{
			msg_box("Es muss eine Retourlabel ID angegeben werden");
			$("#DHLretourlabelID_LabelID").focus();
		}
		
	}
