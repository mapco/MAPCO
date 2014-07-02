<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<script>
		var id_account=0;
		var wait_dialog_timer;
		var item_submit_cancel=false;
		
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
						   show_status(data);
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
						show_status(data);
					}
				}
			);
		}
		
		function item_submit(items, i)
		{
			if (i<items.length)
			{
				var pricelist_id=$("#products_submit_options_pricelist_id").val();
				var comment=$("#products_submit_options_comment").val();

				$("#products_submit_dialog").html("Übertrage Shopartikel "+(i+1)+" von "+items.length);
				$.post("modules/backend_amazon_accounts_actions.php", { action:"item_activate", id_account:id_account, id_item:items[i], pricelist_id:pricelist_id, comment:comment },
					function(data)
					{
						if (data!="")
						{
							show_status(data);
							$("#products_submit_dialog").dialog("close");
						}
						else
						{
							if (item_submit_cancel)
							{
								$("#products_submit_dialog").html("Übertragung nach Amazon agebrochen.");
							}
							else
							{
								item_submit(items, i+1, pricelist_id);
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
		
		function products_submit_options()
		{
			var items=Array();
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
						{ text: "Übertragung starten", click: function() { products_submit(items); } },
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

		function generate_items()
		{
			$("#generate_catalog_dialog").html("Shopartikel-Array wird erstellt...");
			$.post("modules/backend_shop_catalog_actions.php", { action:"generate_items" },
				function(data)
				{
					if (data!="")
					{
						show_status(data);
					}
					else
					{
						generate_vehicles();
					}
				}
			);
		}

		function generate_vehicles()
		{
			$("#generate_catalog_dialog").html("Fahrzeug-Array wird erstellt...");
			$.post("modules/backend_shop_catalog_actions.php", { action:"generate_vehicles" },
				function(data)
				{
					if (data!="")
					{
						show_status(data);
					}
					else
					{
//						generate_vehicles2items();
						generate_catalog_end();
					}
				}
			);
		}

		function generate_vehicles2items()
		{
			$("#generate_catalog_dialog").html("Fahrzeugzuordnungen werden erstellt...");
			$.post("modules/backend_shop_catalog_actions.php", { action:"generate_vehicles2items" },
				function(data)
				{
					if (data!="")
					{
						show_status(data);
					}
					else
					{
						generate_catalog_end();
					}
				}
			);
		}

		function generate_catalog_end()
		{
			$("#generate_catalog_dialog").html("Der CD-Katalog wurde erfolgreich generiert.");
			$('#generate_catalog_dialog').dialog('option', 'buttons', { "OK": function () { $(this).dialog("close"); } });
		}

		function generate_catalog_start()
		{
			$("#generate_catalog_dialog").html("Beginne mit der Erstellung des CD-Katalogs...");
			$("#generate_catalog_dialog").dialog
			({	buttons:
				[
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"CD-Katalog generieren",
				width:450
			});
			generate_items();
		}
	</script>
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > CD-Katalog-Generator';
	echo '</p>';

	echo '<h1>CD-Katalog-Generator</h1>';
	echo '	<div id="view">';
	echo '	<input type="button" value="Katalog generieren" onclick="generate_catalog_start();" />';
	echo '	<p><a href="http://www.mapco.de/catalog/index.html" target="_blank">Katalog ansehen</a></p>';
	echo '	</div>';
	
	//GENERATE CATALOG DIALOG
	echo '<div style="display:none;" id="generate_catalog_dialog">';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>