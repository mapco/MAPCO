<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<script>
		var id_account=0;
		var id_imageformat=0;
		var wait_dialog_timer;
		var item_submit_cancel=false;
		var items=new Array;
		var auction_id=new Array();
		var auction_action=new Array();
		var auction_counter=0;
		
		function checkAll()
		{
			var state = document.getElementById("selectall").checked;
			var theForm = document.itemform;
			for (i=0; i<theForm.elements.length; i++)
			{
				if (theForm.elements[i].name=='item_id[]')
					theForm.elements[i].checked = state;
			}
		}

		function account_settings($id_account)
		{
			wait_dialog_show();
			show_status("Lese Accounteinstellungen aus...");
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AccountGet", id_account:$id_account },				function($data)
			{
				try
				{
					$account = $($.parseXML($data));
					$ack = $account.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				var $ReturnsAccepted=$account.find("ReturnsAcceptedOption").text();
				var $ReturnsWithin=$account.find("ReturnsWithinOption").text();
				var $ShippingCostPaidBy=$account.find("ShippingCostPaidByOption").text();
				var $PaymentMethods=$account.find("PaymentMethods").text();
				$PaymentMethods=$PaymentMethods.split(", ");

				show_status("Lese mögliche Einstellungen bei eBay aus...");
				$("#account_settings_id_account").val($id_account);
				$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetEbayDetails", id_account:$id_account }, function($data)
				{
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2($data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
//					show_status2($data);
					$("#account_settings_ReturnsAcceptedOption").html("");
					$xml.find("ReturnsAccepted").each(function()
					{
						if( $ReturnsAccepted==$(this).find("ReturnsAcceptedOption").text() ) $selected=' selected="selected"'; else $selected='';
						$("#account_settings_ReturnsAcceptedOption").append('<option'+$selected+' value="'+$(this).find("ReturnsAcceptedOption").text()+'">'+$(this).find("ReturnsAcceptedOption").text()+' ('+$(this).find("Description").text()+')</option>');
						
					});
					$("#account_settings_ReturnsWithinOption").html("");
					$xml.find("ReturnsWithin").each(function()
					{
						if( $ReturnsWithin==$(this).find("ReturnsWithinOption").text() ) $selected=' selected="selected"'; else $selected='';
						$("#account_settings_ReturnsWithinOption").append('<option'+$selected+' value="'+$(this).find("ReturnsWithinOption").text()+'">'+$(this).find("ReturnsWithinOption").text()+' ('+$(this).find("Description").text()+')</option>');
						
					});
					$("#account_settings_ShippingCostPaidByOption").html("");
					$xml.find("ShippingCostPaidBy").each(function()
					{
						if( $ShippingCostPaidBy==$(this).find("ShippingCostPaidByOption").text() ) $selected=' selected="selected"'; else $selected='';
						$("#account_settings_ShippingCostPaidByOption").append('<option'+$selected+' value="'+$(this).find("ShippingCostPaidByOption").text()+'">'+$(this).find("ShippingCostPaidByOption").text()+' ('+$(this).find("Description").text()+')</option>');
						
					});

					$("#account_settings_PaymentOptions").html("");
					$xml.find("PaymentOptionDetails").each(function()
					{
						var $PaymentOption=$(this).find("PaymentOption").text();
						for($i=0; $i<$PaymentMethods.length; $i++)
						{
							if($PaymentMethods[$i]==$PaymentOption)
							{
								$checked=' checked="checked"';
								break;
							}
							else
							{
								$checked='';
							}
						}
						$("#account_settings_PaymentOptions").append('<input'+$checked+' value="'+$PaymentOption+'" type="checkbox" />'+$PaymentOption+' ('+$(this).find("Description").text()+')<br />');
						
					});
					hide_status();
					wait_dialog_hide();
					$("#account_settings_dialog").dialog
					({	buttons:
						[
							{ text: "Speichern", click: function() { account_settings_save(); } },
							{ text: "Schließen", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Account-Informationen",
						width:800
					});
				});
			});
		}
		
		
		function account_settings_save()
		{
			var $id_account=$("#account_settings_id_account").val();
			var $ReturnsAcceptedOption=$("#account_settings_ReturnsAcceptedOption").val();
			var $ReturnsWithinOption=$("#account_settings_ReturnsWithinOption").val();
			var $ShippingCostPaidByOption=$("#account_settings_ShippingCostPaidByOption").val();
			var $PaymentMethods="";
			$("#account_settings_PaymentOptions").children("input:checked").each(function()
			{
				if( $PaymentMethods!="" ) $PaymentMethods+=", ";
				$PaymentMethods+=$(this).val();
			});
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AccountEdit", id_account:$id_account, ReturnsAcceptedOption:$ReturnsAcceptedOption, ReturnsWithinOption:$ReturnsWithinOption, ShippingCostPaidByOption:$ShippingCostPaidByOption, PaymentMethods:$PaymentMethods }, function($data)
			{
				show_status2($data);
			});
		}


		function account_info(id_account)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetApiAccessRules", id_account:id_account },
				function($data)
				{
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2($data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
					var $htmldata="";
					$htmldata += '<table>';
					$htmldata += '<tr>';
					$htmldata += '<th>Befehl</th>';
					$htmldata += '<th>Tageslimit hart</th>';
					$htmldata += '<th>Tageslimit soft</th>';
					$htmldata += '<th>Tagesnutzung</th>';
					$htmldata += '<th>Stundenlimit hart</th>';
					$htmldata += '<th>Stundenlimit soft</th>';
					$htmldata += '<th>Stundennutzung</th>';
					$htmldata += '</tr>';
					$xml.find("ApiAccessRule").each(
						function()
						{
							$htmldata += '<tr>';
							$htmldata += '<td>'+$(this).find("CallName").text()+'</td>';
							$htmldata += '<td>'+$(this).find("DailyHardLimit").text()+'</td>';
							$htmldata += '<td>'+$(this).find("DailySoftLimit").text()+'</td>';
							$htmldata += '<td>'+$(this).find("DailyUsage").text()+'</td>';
							$htmldata += '<td>'+$(this).find("HourlyHardLimit").text()+'</td>';
							$htmldata += '<td>'+$(this).find("HourlySoftLimit").text()+'</td>';
							$htmldata += '<td>'+$(this).find("HourlyUsage").text()+'</td>';
							$htmldata += '</tr>';
						}
					);
					$htmldata += '<table>';
					$("#account_info_dialog").html($htmldata);
					wait_dialog_hide();
					$("#account_info_dialog").dialog
					({	buttons:
						[
							{ text: "Schließen", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Account-Informationen",
						width:800
					});
				}
			);
		}

		function account_select($id_account)
		{
			id_account=$id_account;
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AccountSitesGet", id_account:$id_account },	function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				var $html='<table class="hover">';
				$html += '<tr>';
				$html += '	<th>Nr.</th>';
				$html += '	<th>Seite</th>';
				$html += '	<th>';
				$html += '		<img alt="Seite hinzufügen" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Seite hinzufügen" />';
				$html += '	</th>';
				$html += '</tr>';
				$xml.find("Site").each(function()
				{
					$html += '<tr>';
					$html += '	<td>'+$(this).find("SiteID").text()+'</td>';
					$html += '	<td>';
					$html += '		<img alt="Seite bearbeiten" src="images/icons/24x24/edit.png" title="Seite bearbeiten" />';
					$html += '	</td>';
					$html += '</tr>';
				});
				$html += '</table>';
				$("#view_sites").html($html);
			});
		}

		function account_add()
		{
			$("#account_add_title").val("");
			$("#account_add_description").val("");
			$("#account_add_production").val("0");
			$("#account_add_devID").val("");
			$("#account_add_devID_sandbox").val("");
			$("#account_add_appID").val("");
			$("#account_add_appID_sandbox").val("");
			$("#account_add_certID").val("");
			$("#account_add_certID_sandbox").val("");
			$("#account_add_token").val("");
			$("#account_add_token_sandbox").val("");
			$("#account_add_DispatchTimeMax").val("1");
			$("#account_add_PaymentMethods").val("PayPal, CashOnPickup, MoneyXferAcceptedInCheckout");
			$("#account_add_PayPalEmailAddress").val("");
			$("#account_add_PostalCode").val("");
			$("#account_add_pricelist").val("");
			$("#account_add_imageformat_id").val("");
			$("#account_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { account_add_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Account hinzufügen",
				width:800
			});
		}

		function account_add_save()
		{
			var title=$("#account_add_title").val();
			var description=$("#account_add_description").val();
			var $language_id=$("#account_add_language_id").val();
			var $SiteID=$("#account_add_SiteID").val();
			var $active=$("#account_add_active").val();
			var production=$("#account_add_production").val();
			var devID=$("#account_add_devID").val();
			var devID_sandbox=$("#account_add_devID_sandbox").val();
			var appID=$("#account_add_appID").val();
			var appID_sandbox=$("#account_add_appID_sandbox").val();
			var certID=$("#account_add_certID").val();
			var certID_sandbox=$("#account_add_certID_sandbox").val();
			var token=$("#account_add_token").val();
			var token_sandbox=$("#account_add_token_sandbox").val();
			var DispatchTimeMax=$("#account_add_DispatchTimeMax").val();
			var PaymentMethods=$("#account_add_PaymentMethods").val();
			var PayPalEmailAddress=$("#account_add_PayPalEmailAddress").val();
			var PostalCode=$("#account_add_PostalCode").val();
			var pricelist=$("#account_add_pricelist").val();
			var id_imageformat=$("#account_add_imageformat_id").val();
			$.post("modules/backend_ebay_auction_actions.php", { action:"account_add", title:title, description:description, language_id:$language_id, active:$active, SiteID:$SiteID, production:production, devID:devID, devID_sandbox:devID_sandbox, appID:appID, appID_sandbox:appID_sandbox, certID:certID, certID_sandbox:certID_sandbox, token:token, token_sandbox:token_sandbox, DispatchTimeMax:DispatchTimeMax, PaymentMethods:PaymentMethods, PayPalEmailAddress:PayPalEmailAddress, PostalCode:PostalCode, pricelist:pricelist, id_imageformat:id_imageformat },
				   function(data)
				   {
					   if (data!="")
					   {
						   show_status(data);
					   }
					   else
					   {
							$("#account_add_dialog").dialog("close");
							show_status("Der Account wurde erfolgreich angelegt.");
							view();
					   }
				   }
			);
		}

		function account_edit(id_account, title, description, language_id, SiteID, active, production, devID, devID_sandbox, appID, appID_sandbox, certID, certID_sandbox, token, token_sandbox, DispatchTimeMax, PaymentMethods, PayPalEmailAddress, PostalCode, pricelist, id_imageformat)
		{
			$("#account_edit_id_account").val(id_account);
			$("#account_edit_title").val(title);
			$("#account_edit_description").val(description);
			$("#account_edit_language_id").val(language_id);
			$("#account_edit_SiteID").val(SiteID);
			$("#account_edit_active").val(active);
			$("#account_edit_production").val(production);
			$("#account_edit_devID").val(devID);
			$("#account_edit_devID_sandbox").val(devID_sandbox);
			$("#account_edit_appID").val(appID);
			$("#account_edit_appID_sandbox").val(appID_sandbox);
			$("#account_edit_certID").val(certID);
			$("#account_edit_certID_sandbox").val(certID_sandbox);
			$("#account_edit_token").val(token);
			$("#account_edit_token_sandbox").val(token_sandbox);
			$("#account_edit_DispatchTimeMax").val(DispatchTimeMax);
			$("#account_edit_PaymentMethods").val(PaymentMethods);
			$("#account_edit_PayPalEmailAddress").val(PayPalEmailAddress);
			$("#account_edit_PostalCode").val(PostalCode);
			$("#account_edit_pricelist").val(pricelist);
			$("#account_edit_imageformat_id").val(id_imageformat);
			$("#account_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { account_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Account bearbeiten",
				width:800
			});
		}

		function account_edit_save()
		{
			var id_account=$("#account_edit_id_account").val();
			var title=$("#account_edit_title").val();
			var description=$("#account_edit_description").val();
			var $language_id=$("#account_edit_language_id").val();
			var $SiteID=$("#account_edit_SiteID").val();
			var $active=$("#account_edit_active").val();
			var production=$("#account_edit_production").val();
			var devID=$("#account_edit_devID").val();
			var devID_sandbox=$("#account_edit_devID_sandbox").val();
			var appID=$("#account_edit_appID").val();
			var appID_sandbox=$("#account_edit_appID_sandbox").val();
			var certID=$("#account_edit_certID").val();
			var certID_sandbox=$("#account_edit_certID_sandbox").val();
			var token=$("#account_edit_token").val();
			var token_sandbox=$("#account_edit_token_sandbox").val();
			var DispatchTimeMax=$("#account_edit_DispatchTimeMax").val();
			var PaymentMethods=$("#account_edit_PaymentMethods").val();
			var PayPalEmailAddress=$("#account_edit_PayPalEmailAddress").val();
			var PostalCode=$("#account_edit_PostalCode").val();
			var pricelist=$("#account_edit_pricelist").val();
			var id_imageformat=$("#account_edit_imageformat_id").val();
			$.post("modules/backend_ebay_auction_actions.php", { action:"account_edit", id_account:id_account, title:title, description:description, language_id:$language_id, active:$active, SiteID:$SiteID, production:production, devID:devID, devID_sandbox:devID_sandbox, appID:appID, appID_sandbox:appID_sandbox, certID:certID, certID_sandbox:certID_sandbox, token:token, token_sandbox:token_sandbox, DispatchTimeMax:DispatchTimeMax, PaymentMethods:PaymentMethods, PayPalEmailAddress:PayPalEmailAddress, PostalCode:PostalCode, pricelist:pricelist, id_imageformat:id_imageformat },
				   function($data)
				   {
						wait_dialog_hide();
						try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" ) { show_status2($data); return; }

						$("#account_edit_dialog").dialog("close");
						show_status("Der Account wurde erfolgreich aktualisiert.");
						view();
				   }
			);
		}

		function account_remove(id_account)
		{
			$("#account_remove_id_account").val(id_account);
			$("#account_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() { account_remove_accept(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Account löschen",
			});
		}
	
		function account_remove_accept()
		{
			var id_account=$("#account_remove_id_account").val();
			$.post("modules/backend_ebay_auction_actions.php", { action:"account_remove", id_account:id_account },
				function(data)
				{
					if (data=="")
					{
						$("#account_remove_dialog").dialog("close");
						show_status("Der Account wurde erfolgreich gelöscht.");
						view();
					}
					else
					{
						show_status(data);
					}
				}
			);
		}
		
		function ebay_auctions(id_account, id_item)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemGetAuctions", id_account:id_account, id_item:id_item },
				   function(data)
				   {
						$("#ebay_auctions_dialog").html(data);
						$("#ebay_auctions_dialog").dialog
						({	buttons:
							[
								{ text: "OK", click: function() { $(this).dialog("close"); } }
							],
							closeText:"Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Auktionen zum Artikel",
							width:600
						});
				   }
			);
		}


		function item_create(i)
		{
			if ( i==items.length )
			{
				$('#items_submit_dialog').dialog('option', 'buttons', {});
				$("#items_submit_dialog").html("Alle Shopartikel erfolgreich übertragen.");
				return;
			}
			
			var id_pricelist=$("#items_submit_id_pricelist").val();
			var bestoffer = $("#items_submit_bestoffer:checked").val()
			if ( bestoffer=="on" ) bestoffer=1; else bestoffer=0;
			var ShippingServiceCost=$("#items_submit_ShippingServiceCost").val();
			var id_article=$("#items_submit_id_article").val();
			var comment=$("#items_submit_comment").val();

			$("#items_submit_dialog").html("Shopartikel "+(i+1)+" von "+items.length+"<br /><br />Erstelle Auktionen...");
			var response = $.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_account:id_account, id_item:items[i], pricelist_id:id_pricelist, bestoffer:bestoffer, ShippingServiceCost:ShippingServiceCost, comment:comment, id_article:id_article, id_imageformat:id_imageformat },
				function(data)
				{
					alert(data);
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					var j=0;
					auction_id=new Array();
					auction_action=new Array();
					$xml.find("AuctionID").each(
						function()
						{
							auction_id[j]=$(this).text();
							auction_action[j]=$(this).attr("action");
							j++;
						}
					);
					auction_counter=0;
					item_submit(i);
				}
			);
//			response.error(function() { alert("error"); })
		}


		function item_submit(item_counter)
		{
			if (auction_counter==auction_id.length)
			{
				item_create(item_counter+1);
				return;
			}
			if (auction_action[auction_counter]=="AddItem") actiontext="Erstelle Auktion";
			else if (auction_action[auction_counter]=="ReviseItem") actiontext="Aktualisiere Auktion";
			else if (auction_action[auction_counter]=="EndItem") actiontext="Beende Auktion";
			var status="Shopartikel "+(item_counter+1)+" von "+items.length;
			status+="<br /><br />Aktion "+(auction_counter+1)+" von "+auction_id.length+": "+actiontext+" "+auction_id[auction_counter];
			$("#items_submit_dialog").html(status);
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:auction_action[auction_counter], id_auction:auction_id[auction_counter] },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status(data);
						item_create(item_counter);
						return;
					}
					auction_counter++;
					item_submit(item_counter);
				}
			);
		}


		
		function items_submit_options()
		{
			items=new Array();
			var theForm = document.itemform;
			var j=0;
			for (i=0; i<theForm.elements.length; i++)
			{
				if (theForm.elements[i].checked)
				{
					if (theForm.elements[i].name=='item_id[]')
					{
						items[j]=theForm.elements[i].value;
						j++;
					}
				}
			}
			
			if (items.length==0)
			{
				alert("Es muss mindestens ein Shopartikel ausgewählt worden sein.");
				return;
			}

			item_submit_cancel=false;
			$("#items_submit_options_dialog").dialog
			({	buttons:
				[
					{ text: "Übertragung starten", click: function() { items_submit(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Übertragungsoptionen",
				width:450
			});
		}

		function items_submit()
		{
			$("#items_submit_options_dialog").dialog("close");
			$("#items_submit_dialog").dialog
			({	buttons:
				[
					{	text: "Abbrechen",
						click: function()
						{
							item_submit_cancel=true;
							$("#items_submit_dialog").html("Übertragung wird abgebrochen...");
							$('#items_submit_dialog').dialog('option', 'buttons', {});
						}
					}
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Shopartikel nach eBay übertragen",
				width:400
			});
			
			item_create(0);
		}

		function view()
		{
			wait_dialog_show();
			var id_menuitem=$("#id_menuitem").val();
			var deliverystatus=$("#deliverystatus").val();
			var fotostatus=$("#fotostatus").val();
			var needle=$("#needle").val();
			if ( typeof(deliverystatus) == "undefined" ) deliverystatus=4;
			if ( typeof(fotostatus) == "undefined" ) fotostatus=0;
			if ( typeof(needle) == "undefined" ) needle='';
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AuctionsView", id_account:id_account, id_menuitem:id_menuitem, deliverystatus:deliverystatus, fotostatus:fotostatus, needle:needle },
				function(data)
				{
					$("#view").html(data);
					$(function() {
						$( "#ebay_accounts" ).sortable({cancel: "#ebay_accounts_header"});
						$( "#ebay_accounts" ).disableSelection();
						$( "#ebay_accounts" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#ebay_accounts').sortable('toArray');
							$.post("modules/backend_ebay_auction_actions.php", {action:"account_sort", list:list},
								function(data)
								{
									if (data=="")
									{
										show_status("Accounts erfolgreich sortiert.");
										view();
									}
									else
									{
										show_status(data);
									}
								}
							);
						});
					});
					wait_dialog_hide();
				}
			);
		}

		function view_results(id_account, id_menuitem, status, img, text)
		{
			$.post("modules/backend_ebay_auction_actions.php", { action:"view", id_account:id_account, id_menuitem:id_menuitem },
				function(data)
				{
					$("#view_results").html(data);
				}
			);
		}
	</script>
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Accounts';
	echo '</p>';

	echo '<h1>eBay-Accounts</h1>';
	echo '<div id="view" style="display:inline; float:left;"></div>';
	echo '<div id="view_sites" style="display:inline; float:left;"></div>';
	echo '<script>view();</script>';
	
	//ACCOUNT ADD DIALOG
	echo '<div id="account_info_dialog" style="display:none;">';
	echo '</div>';


	//ACCOUNT ADD DIALOG
	echo '<div id="account_add_dialog" style="display:none;">';
	echo '<table style="margin:5px; float:left;">';
	echo '	<tr>';
	echo '		<th colspan="2">Neuen Account anlegen</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td><input id="account_add_title" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td><textarea id="account_add_description" style="width:300px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Marktplatz</td>';
	echo '		<td>';
	echo '			<select id="account_add_SiteID">';
	$results=q("SELECT * FROM ebay_sites ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["SiteID"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Sprache</td>';
	echo '		<td>';
	echo '			<select id="account_add_language_id">';
	$results=q("SELECT * FROM cms_languages ORDER BY language;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["id_language"].'">'.$row["language"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aktiv</td>';
	echo '		<td>';
	echo '			<select id="account_add_active">';
	echo '				<option value="0">Nein</option>';
	echo '				<option value="1">Ja</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Produktion</td>';
	echo '		<td>';
	echo '			<select id="account_add_production">';
	echo '				<option value="0">Nein</option>';
	echo '				<option value="1">Ja</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Bearbeitungszeit</td>';
	echo '		<td><input id="account_add_DispatchTimeMax" style="width:50px;" type="text" value="1" /> Tage</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zahlungsmethoden</td>';
	echo '		<td><input id="account_add_PaymentMethods" style="width:300px;" type="text" value="PayPal, CashOnPickup, MoneyXferAcceptedInCheckout" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>PayPal E-Mail</td>';
	echo '		<td><input id="account_add_PayPalEmailAddress" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Postleitzahl</td>';
	echo '		<td><input id="account_add_PostalCode" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisliste</td>';
	echo '		<td><input id="account_add_pricelist" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Imageformat ID</td>';
	echo '		<td><input id="account_add_imageformat_id" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<th></th>';
	echo '		<th>Produktion</th>';
	echo '		<th>Sandbox</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>devID</td>';
	echo '		<td><input id="account_add_devID" style="width:300px;" type="text" value="" /></td>';
	echo '		<td><input id="account_add_devID_sandbox" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>appID</td>';
	echo '		<td><input id="account_add_appID" style="width:300px;" type="text" value="" /></td>';
	echo '		<td><input id="account_add_appID_sandbox" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>certID</td>';
	echo '		<td><input id="account_add_certID" style="width:300px;" type="text" value="" /></td>';
	echo '		<td><input id="account_add_certID_sandbox" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Token</td>';
	echo '		<td><textarea id="account_add_token" style="width:300px; height:50px;"></textarea></td>';
	echo '		<td><textarea id="account_add_token_sandbox" style="width:300px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//ACCOUNT EDIT DIALOG
	echo '<div id="account_edit_dialog" style="display:none;">';
	echo '<table style="margin:5px; float:left;">';
	echo '	<tr>';
	echo '		<th colspan="2">Account bearbeiten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td colspan="2"><input id="account_edit_title" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td colspan="2"><textarea id="account_edit_description" style="width:300px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Sprache</td>';
	echo '		<td>';
	echo '			<select id="account_edit_language_id">';
	$results=q("SELECT * FROM cms_languages ORDER BY language;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["id_language"].'">'.$row["language"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Marktplatz</td>';
	echo '		<td colspan="2">';
	echo '			<select id="account_edit_SiteID">';
	$results=q("SELECT * FROM ebay_sites ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["SiteID"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aktiv<br /></td>';
	echo '		<td colspan="2">';
	echo '			<select id="account_edit_active">';
	echo '				<option value="0">Nein</option>';
	echo '				<option value="1">Ja</option>';
	echo '			</select>';
	echo '			<br /><i>Nur aktive Accounts werden über LMS hochgeladen.</i>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Produktion</td>';
	echo '		<td colspan="2">';
	echo '			<select id="account_edit_production">';
	echo '				<option value="0">Nein</option>';
	echo '				<option value="1">Ja</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Bearbeitungszeit</td>';
	echo '		<td><input id="account_edit_DispatchTimeMax" style="width:30px;" type="text" value="" /> Tage (0 = Versand am gleichen Tag)</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zahlungsmethoden</td>';
	echo '		<td><input id="account_edit_PaymentMethods" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>PayPal E-Mail</td>';
	echo '		<td><input id="account_edit_PayPalEmailAddress" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Postleitzahl</td>';
	echo '		<td><input id="account_edit_PostalCode" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisliste</td>';
	echo '		<td><input id="account_edit_pricelist" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Imageformat ID</td>';
	echo '		<td><input id="account_edit_imageformat_id" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<th></th>';
	echo '		<th>Produktion</th>';
	echo '		<th>Sandbox</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>devID</td>';
	echo '		<td><input id="account_edit_devID" style="width:300px;" type="text" value="" /></td>';
	echo '		<td><input id="account_edit_devID_sandbox" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>appID</td>';
	echo '		<td><input id="account_edit_appID" style="width:300px;" type="text" value="" /></td>';
	echo '		<td><input id="account_edit_appID_sandbox" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>certID</td>';
	echo '		<td><input id="account_edit_certID" style="width:300px;" type="text" value="" /></td>';
	echo '		<td><input id="account_edit_certID_sandbox" style="width:300px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Token</td>';
	echo '		<td><textarea id="account_edit_token" style="width:300px; height:50px;"></textarea></td>';
	echo '		<td><textarea id="account_edit_token_sandbox" style="width:300px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input id="account_edit_id_account" type="hidden" value="" />';
	echo '</div>';

	//ACCOUNT REMOVE DIALOG
	echo '<div style="display:none;" id="account_remove_dialog">';
	echo '	<p>Wollen Sie den Account wirklich löschen?</p>';
	echo '	<input type="hidden" id="account_remove_id_account" value="" />';
	echo '</div>';
	
	//ACCOUNT SETTINGS DIALOG
	echo '<div style="display:none;" id="account_settings_dialog">';
	echo '	<table>';
	echo '		</tr>';
	echo '			<td>Rückgaben</td>';
	echo '			<td>';
	echo '				<select id="account_settings_ReturnsAcceptedOption"></select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Rückgabezeitraum</td>';
	echo '			<td>';
	echo '				<select id="account_settings_ReturnsWithinOption"></select>';
	echo '			</td>';
	echo '		<tr>';
	echo '		<tr>';
	echo '			<td>Rückgabekosten</td>';
	echo '			<td>';
	echo '				<select id="account_settings_ShippingCostPaidByOption"></select>';
	echo '			</td>';
	echo '		<tr>';
	echo '		<tr>';
	echo '			<td>Zahlungsarten</td>';
	echo '			<td id="account_settings_PaymentOptions">';
	echo '			</td>';
	echo '		<tr>';
	echo '	</table>';
	echo '	<input id="account_settings_id_account" type="hidden" value="" />';
	echo '</div>';
	
	//ITEMS SUBMIT OPTIONS DIALOG
	echo '<div id="items_submit_options_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Design</td>';
	echo '		<td>';
	echo '			<select id="items_submit_id_article">';
	$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=8 AND a.id_article=b.article_id AND a.id_article<246;", $dbweb, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_article"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aktiv</td>';
	echo '		<td>';
	echo '			<select id="items_submit_activate">';
	echo '				<option value="1">Aktivieren</option>';
	echo '				<option value="0">Deaktivieren</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
/*
	echo '	<tr>';
	echo '		<td>Preisliste</td>';
	echo '		<td>';
	echo '	<select id="items_submit_id_pricelist">';
	if ( $_SESSION["userrole_id"]==1 )
	{
		echo '		<option value="0">0 - Bruttopreisliste</option>';
		echo '		<option selected="selected" value="1">1 - yellow Preisliste</option>';
		echo '		<option value="2">2 - Werksverkaufsliste</option>';
		echo '		<option value="3">3 - blaue Preisliste</option>';
		echo '		<option value="4" selected="selected">4 - grüne Preisliste</option>';
		echo '		<option value="5">5 - gelbe Preisliste</option>';
		echo '		<option value="6">6 - orange Preisliste</option>';
		echo '		<option value="7">7 - rote Preisliste</option>';
		echo '		<option value="8">8 - red Preisliste</option>';
		echo '		<option value="9">9 - GH-HR Preisliste</option>';
	}
	echo '		<option value="18209">AUTOPARTNER - eBay-VP-Liste</option>';
	echo '		<option value="16815">MAPCO - eBay-VP-Liste</option>';
	echo '	</select>';
	echo '		</td>';
	echo '	</tr>';
*/
	echo '	<tr>';
	echo '		<td>Preisvorschlag</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="items_submit_bestoffer" /> Preisvorschlag aktivieren';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandkosten</td>';
	echo '		<td>';
	echo '				<input style="width:50px;" type="input" id="items_submit_ShippingServiceCost" value="5.90" /> €';
	echo '		</td>';
	echo '	</tr>';
/*
	echo '	<tr>';
	echo '		<td>kostenloser Versand</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="items_submit_free_shipping" /> kostenloser Versand aktivieren';
	echo '		</td>';
	echo '	</tr>';
*/
	echo '	<tr>';
	echo '		<td>Kommentar</td>';
	echo '		<td>';
	echo '			<textarea id="items_submit_comment" style="width:300px; height:50px;" ></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//EBAY AUCTIONS DIALOG
	echo '<div style="display:none;" id="ebay_auctions_dialog">';
	echo '</div>';
	
	//ITEMS SUBMIT DIALOG
	echo '<div style="display:none;" id="items_submit_dialog">';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>