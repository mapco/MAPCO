<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<script>
		var id_account=0;
		var wait_dialog_timer;
		var item_submit_cancel=false;
		var items=new Array;
		
		function get_feed_submission_list()
		{
			$.post("<?php echo PATH ?>soa/", { API:"amazon", Action:"AmazonSubmit", id_account:1, url:"Action=SubmitFeed&FeedType=_POST_INVENTORY_AVAILABILITY_DATA_" }, function($data)
//			$.post("<?php echo PATH ?>soa/", { API:"amazon", Action:"AmazonSubmit", id_account:1, url:"Action=GetFeedSubmissionList&FeedType=_POST_PRODUCT_DATA_" }, function($data)
			{
				show_status2($data);
			});
		}

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

		function amazon_account(id)
		{
			id_account=id;
			view();
		}

		function account_add()
		{
			$("#account_add_title").val("");
			$("#account_add_description").val("");
			$("#account_add_AWSAccessKeyId").val("");
			$("#account_add_MarketplaceId").val("");
			$("#account_add_MerchantId").val("");
			$("#account_add_SecretKey").val("");
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
				width:600
			});
		}

		function account_add_save()
		{
			var title=$("#account_add_title").val();
			var description=$("#account_add_description").val();
			var AWSAccessKeyId=$("#account_add_AWSAccessKeyId").val();
			var MarketplaceId=$("#account_add_MarketplaceId").val();
			var MerchantId=$("#account_add_MerchantId").val();
			var SecretKey=$("#account_add_SecretKey").val();
			$.post("modules/backend_amazon_accounts_actions.php", { action:"account_add", title:title, description:description, AWSAccessKeyId:AWSAccessKeyId, MarketplaceId:MarketplaceId, MerchantId:MerchantId, SecretKey:SecretKey },
				   function(data)
				   {
					   if (data!="")
					   {
						   show_status2(data);
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

		function account_edit(id_account, title, description, AWSAccessKeyId, MarketplaceId, MerchantId, SecretKey)
		{
			$("#account_edit_id_account").val(id_account);
			$("#account_edit_title").val(title);
			$("#account_edit_description").val(description);
			$("#account_edit_AWSAccessKeyId").val(AWSAccessKeyId);
			$("#account_edit_MarketplaceId").val(MarketplaceId);
			$("#account_edit_MerchantId").val(MerchantId);
			$("#account_edit_SecretKey").val(SecretKey);
			$("#account_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { account_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Account bearbeiten",
				width:600
			});
		}

		function account_edit_save()
		{
			var id_account=$("#account_edit_id_account").val();
			var title=$("#account_edit_title").val();
			var description=$("#account_edit_description").val();
			var AWSAccessKeyId=$("#account_edit_AWSAccessKeyId").val();
			var MarketplaceId=$("#account_edit_MarketplaceId").val();
			var MerchantId=$("#account_edit_MerchantId").val();
			var SecretKey=$("#account_edit_SecretKey").val();
			$.post("modules/backend_amazon_accounts_actions.php", { action:"account_edit", id_account:id_account, title:title, description:description, AWSAccessKeyId:AWSAccessKeyId, MarketplaceId:MarketplaceId, MerchantId:MerchantId, SecretKey:SecretKey },
				   function(data)
				   {
					   if (data!="")
					   {
						   show_status2(data);
					   }
					   else
					   {
							$("#account_edit_dialog").dialog("close");
							show_status("Der Account wurde erfolgreich aktualisiert.");
							view();
					   }
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
			$.post("modules/backend_amazon_accounts_actions.php", { action:"account_remove", id_account:id_account },
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
						show_status2(data);
					}
				}
			);
		}
		
		function item_submit(i)
		{
			if (i<items.length)
			{
				var pricelist_id=$("#products_submit_options_pricelist_id").val();
				var comment=$("#products_submit_options_comment").val();

				$("#products_submit_dialog").html("Übertrage Shopartikel "+(i+1)+" von "+items.length);
				$.post("modules/backend_amazon_accounts_actions.php", { action:"item_activate", id_account:id_account, id_item:items[i], pricelist_id:pricelist_id, comment:comment },
					function(data)
					{
						show_status2(data);
						return;
						if (data!="")
						{
							show_status2(data);
							$("#products_submit_dialog").dialog("close");
						}
						else
						{
							if (item_submit_cancel)
							{
								$("#products_submit_dialog").html("Übertragung nach Amazon abgebrochen.");
							}
							else
							{
								if (i % 3 == 2)
								{
									var text = $("#products_submit_dialog").html();
									item_submit_delay(i, 130, text);
								}
								else
								{
									item_submit(i+1);
//									setTimeout("item_submit("+(i+1)+");", delay);
//									item_submit(items, i+1, pricelist_id);
								}
							}
						}
					}
				);
			}
			else
			{
				$('#products_submit_dialog').dialog('option', 'buttons', {});
				$("#products_submit_dialog").html("Alle Shopartikel erfolgreich übertragen.");
			}
		}
		
		function item_submit_delay(i, delay, text)
		{
			$("#products_submit_dialog").html(text+'<br /><span style="font-weight:bold; color:#ff0000;">Amazon Throttling Countdown: '+delay+' Sekunden</span>');
			if (delay>0) setTimeout("item_submit_delay("+i+", "+(delay-1)+", '"+text+"');", 1000);
			else item_submit(i+1);
		}

	
		function list_orders()
		{
			$.post("<?php echo PATH ?>soa/", { API:"amazon", Action:"AmazonSubmit", id_account:1, url:"Action=ListOrders" }, function($data)
			{
				show_status2($data);
			});
		}


		function products_submit_options()
		{
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
			
			if (items.length==0) alert("Es muss mindestens ein Shopartikel ausgewählt worden sein.");
			else
			{
				item_submit_cancel=false;
				$("#products_submit_options_dialog").dialog
				({	buttons:
					[
						{ text: "Übertragung starten", click: function() { products_submit(); } },
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
		}

		function products_submit()
		{
			$("#products_submit_options_dialog").dialog("close");
			$("#products_submit_dialog").dialog
			({	buttons:
				[
					{	text: "Abbrechen",
						click: function()
						{
							item_submit_cancel=true;
							$("#products_submit_dialog").html("Übertragung wird abgebrochen...");
							$('#products_submit_dialog').dialog('option', 'buttons', {});
						}
					}
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Shopartikel nach Amazon übertragen",
				width:400
			});
			
			item_submit(0);
		}

		function view()
		{
			wait_dialog_show();
			var id_menuitem=$("#id_menuitem").val();
			var deliverystatus=$("#deliverystatus").val();
			var fotostatus=$("#fotostatus").val();
			var needle=$("#needle").val();
//			if ( typeof(MakeKey) == "undefined" ) MakeKey='<?php if ( isset($_SESSION["MakeKey"]) ) echo $_SESSION["MakeKey"]; else echo 'Beliebig'; ?>';
			$.post("modules/backend_amazon_accounts_actions.php", { action:"view", id_account:id_account, id_menuitem:id_menuitem, deliverystatus:deliverystatus, fotostatus:fotostatus, needle:needle },
				function(data)
				{
					$("#view").html(data);
					$(function() {
						$( "#amazon_accounts" ).sortable({cancel: "#amazon_accounts_header"});
						$( "#amazon_accounts" ).disableSelection();
						$( "#amazon_accounts" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#amazon_accounts').sortable('toArray');
							$.post("modules/backend_amazon_accounts_actions.php", {action:"account_sort", list:list},
								function(data)
								{
									if (data=="")
									{
										show_status("Accounts erfolgreich sortiert.");
										view();
									}
									else
									{
										show_status2(data);
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
			$.post("modules/backend_amazon_accounts_actions.php", { action:"view", id_account:id_account, id_menuitem:id_menuitem },
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
	echo ' > <a href="backend_amazon_index.php">Amazon</a>';
	echo ' > Accounts';
	echo '</p>';

	echo '<h1>Amazon-Accounts</h1>';
	echo '<div id="view" style="display:inline; float:left;"></div>';
	echo '<script>view();</script>';
	
	//AMAZON REMOVE WINDOW
	echo '<div id="amazon_remove_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Account löschen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="amazon_remove_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="">Sind Sie sicher, dass Sie den Account löschen möchten?<br /><br />Bitte stellen Sie sicher, dass zuvor alle Auktionen zu diesem Account deaktiviert wurden.</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input type="hidden" id="amazon_remove_id_account" value="" />';
	echo '			<input class="formbutton" type="button" value="Löschen" onclick="amazon_remove_accept();" />';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="amazon_remove_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//ACCOUNT ADD DIALOG
	echo '<div id="account_add_dialog" style="display:none;">';
	echo '<table style="margin:5px; float:left;">';
	echo '	<tr>';
	echo '		<th colspan="2">Neuen Account anlegen</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td><input id="account_add_title" style="width:400px;" type="text" value="'.$row["title"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td><textarea id="account_add_description" style="width:400px; height:50px;">'.$row["description"].'</textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Händler ID</td>';
	echo '		<td><input id="account_add_MerchantId" style="width:400px;" type="text" value="'.$row["MerchantId"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Marktplatz-ID</td>';
	echo '		<td>';
	echo '			<select id="account_add_MarketplaceId">';
	echo '				<option value="A1PA6795UKMFR9">Deutschland</option>';
	echo '				<option value="A13V1IB3VIYZZH">Frankreich</option>';
	echo '				<option value="A1F83G8C2ARO7P">Großbritannien</option>';
	echo '				<option value="APJ6JRA9NG5V4">Italien</option>';
	echo '				<option value="A1RKKUPIHCS9HS">Spanien</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>AWS Zugangsschlüssel-ID</td>';
	echo '		<td><input id="account_add_AWSAccessKeyId" style="width:400px;" type="text" value="'.$row["devID_sandbox"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Geheimer Schlüssel</td>';
	echo '		<td><input id="account_add_SecretKey" style="width:400px;" type="text" value="'.$row["SecretKey"].'" /></td>';
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
	echo '		<td><input id="account_edit_title" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td><textarea id="account_edit_description" style="width:400px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Händler ID</td>';
	echo '		<td><input id="account_edit_MerchantId" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Marktplatz-ID</td>';
	echo '		<td>';
	echo '			<select id="account_edit_MarketplaceId">';
	echo '				<option value="A1PA6795UKMFR9">Deutschland</option>';
	echo '				<option value="A13V1IB3VIYZZH">Frankreich</option>';
	echo '				<option value="A1F83G8C2ARO7P">Großbritannien</option>';
	echo '				<option value="APJ6JRA9NG5V4">Italien</option>';
	echo '				<option value="A1RKKUPIHCS9HS">Spanien</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>AWS Zugangsschlüssel-ID</td>';
	echo '		<td><input id="account_edit_AWSAccessKeyId" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Geheimer Schlüssel</td>';
	echo '		<td><input id="account_edit_SecretKey" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input id="account_edit_id_account" type="hidden" value="" />';
	echo '</div>';

	//ACCOUNT REMOVE DIALOG
	echo '<div style="display:none;" id="account_remove_dialog">';
	echo '	<p>Wollen Sie den Account wirklich löschen?</p>';
	echo '	<input type="hidden" id="account_remove_id_account" value="" />';
	echo '</div>';
	
	//PRODUCTS SUBMIT OPTIONS DIALOG
	echo '<div style="display:none;" id="products_submit_options_dialog">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Preisliste</td>';
	echo '			<td>';
	echo '	<select id="products_submit_options_pricelist_id">';
	echo '		<option value="0">0 - Bruttopreisliste</option>';
//	echo '		<option selected="selected" value="1">1 - yellow Preisliste</option>';
	echo '		<option value="2">2 - Werksverkaufsliste</option>';
	echo '		<option value="3">3 - blaue Preisliste</option>';
	echo '		<option value="4" selected="selected">4 - grüne Preisliste</option>';
	echo '		<option value="5">5 - gelbe Preisliste</option>';
	echo '		<option value="6">6 - orange Preisliste</option>';
	echo '		<option value="7">7 - rote Preisliste</option>';
//	echo '		<option value="8">8 - red Preisliste</option>';
//	echo '		<option value="9">9 - GH-HR Preisliste</option>';
	echo '		<option value="18209">AUTOPARTNER - eBay-VP-Liste</option>';
	echo '		<option value="16815">MAPCO - eBay-VP-Liste</option>';
	echo '	</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Kommentar</td>';
	echo '			<td><textarea id="products_submit_options_comment" style="width:300px; height:50px;"></textarea></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	//PRODUCTS SUBMIT DIALOG
	echo '<div style="display:none;" id="products_submit_dialog">';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>