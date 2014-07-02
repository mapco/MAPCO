<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	
?>
	<script>
		var id_account;
		var wait_dialog_timer;
		var categories="";
		
		function ebay_account(id)
		{
			id_account=id;
			if (id_account>0)
			{
				wait_dialog_show();
				$.post("modules/backend_ebay_categories_actions.php", { action:"ebay_categories", id_account:id_account },
					function(data)
					{
						categories=data;
						wait_dialog_hide();
						ebay_view();
					}
				);
			}
		}
		
		function category_edit(id_account, GART, StoreCategory, StoreCategory2)
		{
			$("#category_edit_id_account").val(id_account);
			$("#category_edit_GART").val(GART);
			$("#category_edit_StoreCategory").val(StoreCategory);
			$("#category_edit_StoreCategory2").val(StoreCategory2);
//			$("#StoreCategory"+StoreCategory).attr("selected", true);
//			$("#StoreCategory2"+StoreCategory2).attr("selected", true);
			$("#category_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { category_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kategorien ändern",
				width:500
			});
		}

		function category_edit_save()
		{
			var id_account=$("#category_edit_id_account").val();
			var GART=$("#category_edit_GART").val();
			var StoreCategory=$("#category_edit_StoreCategory").val();
			var StoreCategory2=$("#category_edit_StoreCategory2").val();
			$.post("modules/backend_ebay_categories_actions.php", { action:"category_edit_save", id_account:id_account, GART:GART, StoreCategory:StoreCategory, StoreCategory2:StoreCategory2 },
				function(data)
				{
					if ( data!="" ) show_status(data);
					else
					{
						show_status("eBay-Artikelgruppen erfolgreich gespeichert.");
//						$("#category_edit_dialog").dialog("close");
						$("#category_edit_dialog").remove();
						ebay_view();
					}
				}
			);
		}

		function ebay_view()
		{
//			wait_dialog_show();
			$.post("modules/backend_ebay_categories_actions.php", { action:"ebay_view", id_account:id_account, categories:categories },
				function(data)
				{
					$("#view").html(data);
					$(function()
					{
						$( "#ebay_accounts" ).sortable({cancel: "#ebay_accounts_header"});
						$( "#ebay_accounts" ).disableSelection();
						$( "#ebay_accounts" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#ebay_accounts').sortable('toArray');
							$.post("modules/backend_ebay_accounts_actions.php", {action:"ebay_sort", list:list}, function(data) { show_status(data); ebay_view(); });
						});
					});
//					wait_dialog_hide();
				}
			);
		}
	</script>
<?php

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Shop-Kategorien';
	echo '</p>';

	//VIEW
	echo '<div id="view">...</div>';
	echo '<script> ebay_view(); </script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>