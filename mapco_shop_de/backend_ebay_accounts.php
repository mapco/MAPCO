<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<script>
		var id_account;

		function ebay_account(id)
		{
			id_account=id;
			ebay_view();
		}

		function ebay_create()
		{
			var title = $("#ebay_title").val();
			var description = $("#ebay_description").val();
			var production = $("#ebay_production").val();
			var devID_sandbox = $("#ebay_devID_sandbox").val();
			var appID_sandbox = $("#ebay_appID_sandbox").val();
			var certID_sandbox = $("#ebay_certID_sandbox").val();
			var token_sandbox = $("#ebay_token_sandbox").val();
			var devID = $("#ebay_devID").val();
			var appID = $("#ebay_appID").val();
			var certID = $("#ebay_certID").val();
			var token = $("#ebay_token").val();
			$.post("modules/backend_ebay_accounts_actions.php", { action:"ebay_create", id_account:id_account, title:title, description:description, production:production, devID_sandbox:devID_sandbox, appID_sandbox:appID_sandbox, certID_sandbox:certID_sandbox, token_sandbox:token_sandbox, devID:devID, appID:appID, certID:certID, token:token },
				   function(data)
				   {
					   show_status(data);
					   ebay_view();
				   }
			);
		}

		function ebay_update()
		{
			var title = $("#ebay_title").val();
			var description = $("#ebay_description").val();
			var production = $("#ebay_production").val();
			var devID_sandbox = $("#ebay_devID_sandbox").val();
			var appID_sandbox = $("#ebay_appID_sandbox").val();
			var certID_sandbox = $("#ebay_certID_sandbox").val();
			var token_sandbox = $("#ebay_token_sandbox").val();
			var devID = $("#ebay_devID").val();
			var appID = $("#ebay_appID").val();
			var certID = $("#ebay_certID").val();
			var token = $("#ebay_token").val();
			$.post("modules/backend_ebay_accounts_actions.php", { action:"ebay_update", id_account:id_account, title:title, description:description, production:production, devID_sandbox:devID_sandbox, appID_sandbox:appID_sandbox, certID_sandbox:certID_sandbox, token_sandbox:token_sandbox, devID:devID, appID:appID, certID:certID, token:token },
				   function(data)
				   {
					   show_status(data);
					   ebay_view();
				   }
			);
		}

		function ebay_remove_show(id)
		{
			$("#ebay_remove_id_account").val(id);
			$("#ebay_remove_window").show();
		}
		
		function ebay_remove_cancel()
		{
			$("#ebay_remove_window").hide();
		}
		
		function ebay_remove_accept()
		{
			var id=$("#ebay_remove_id_account").val();
			$.post("modules/backend_ebay_accounts_actions.php", { action:"ebay_remove", id_account:id }, function(data) { show_status(data); } );
			$("#ebay_remove_window").hide();
			ebay_view();
		}
		
		function ebay_view()
		{
			$.post("modules/backend_ebay_accounts_actions.php", { action:"ebay_view", id_account:id_account },
				function(data)
				{
					$("#ebay_view").html(data);
					$(function() {
						$( "#ebay_accounts" ).sortable({cancel: "#ebay_accounts_header"});
						$( "#ebay_accounts" ).disableSelection();
						$( "#ebay_accounts" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#ebay_accounts').sortable('toArray');
							$.post("modules/backend_ebay_accounts_actions.php", {action:"ebay_sort", list:list}, function(data) { show_status(data); ebay_view(); });
						});
					});
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
	echo '<div id="ebay_view"></div>';
	echo '<script>ebay_view();</script>';
	
	//EBAY REMOVE WINDOW
	echo '<div id="ebay_remove_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Account löschen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_remove_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="">Sind Sie sicher, dass Sie den Account löschen möchten?<br /><br />Bitte stellen Sie sicher, dass zuvor alle Auktionen zu diesem Account deaktiviert wurden.</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input type="hidden" id="ebay_remove_id_account" value="" />';
	echo '			<input class="formbutton" type="button" value="Löschen" onclick="ebay_remove_accept();" />';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="ebay_remove_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>