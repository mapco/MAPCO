<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

?>

<script type="text/javascript">

	function clear_input()
	{
		$("#missingItem").val("");
		$("#missingItemQty").val("");
		$("#missingItemComment").val("");
	}
	
	function add_missingItem()
	{
	
		$("#MissingItemDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { add_missingItem_save();} },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); clear_input();} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Fehlenden Artikel eingeben",
			width:400
		});	
	}
	
	function add_missingItem_save()
	{
		var missingItem = $("#missingItem").val();
		var missingQty = $("#missingItemQty").val();
		var missingComment = $("#missingItemComment").val();

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "MissingItemAdd", missingItem:missingItem, missingQty:missingQty, missingComment:missingComment},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					wait_dialog_hide();
					show_status("Fehlender Artikel wurde erfolgreich gespeichert");
					clear_input();
					$("#MissingItemDialog").dialog("close");
				}
				else 
				{
					wait_dialog_hide();
					var ErrorCode=$xml.find("ErrorCode").val();
					var errorlongmsg=$xml.find("longMsg").text();
					alert(errorlongmsg);
					if (ErrorCode==1)
					{
						$("#missingItem").focus();
					}
					if (ErrorCode==2)
					{
						$("#missingItemQty").focus();
					}

					return;
				}
			}
		);

	}

</script>


<?

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Fehlende Artikel';
	echo '</p>';
	echo '<h1>Fehlende Artikel</h1>';
	
	echo '<div>';
	echo '<table>';
	echo '<tr>';
	echo '	<th>';
	echo '	Einen Mapco-Artikel als fehlend vermerken (Lagerbestand 0 etc.)';
	echo '	</th>';
	echo '	<th>';
	echo '	<img src="'.PATH.'images/icons/24x24/add.png" style="cursor:pointer; float:right;" alt="Einen MAPCO Artikel als fehlend vermerken" title="Einen MAPCO Artikel als fehlend vermerken" onclick="add_missingItem();" />';
	echo '	</th>';
	echo '</tr>';
	echo '</table>';	
	echo '</div>';
	
	//DIALOGBOX
	echo '<div id="MissingItemDialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>MAPCO-Artikelnummer</td>';
	echo '		<td><input type="text" name="Artikel" id="missingItem" size="10" \></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>fehlende Anzahl</td>';
	echo '		<td><input type="text" name="Anzahl" id="missingItemQty" size="3" \></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Anmerkung</td>';
	echo '		<td><textarea name="Kommentar" id="missingItemComment" cols="30" rows="5"></textarea></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	echo '<script type="text/javascript">add_missingItem();</script>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>