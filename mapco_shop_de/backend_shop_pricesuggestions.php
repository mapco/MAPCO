<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<script type="text/javascript">
	var $lines=new Array();
	var import_cancel=false;

	function prices_import_cancel()
	{
		import_cancel=true;
		$("#prices_import_dialog").html("Vorgang wird abgebrochen. Bitte warten!");
	}
  
  	function handleFileSelect(evt)
	{
		var files = evt.target.files; // FileList object
		var f= files[0];

		if ( f.type != "application/vnd.ms-excel" ) $("#prices_import_dialog_status").html("Datei ist nicht CSV.");
		else
		{

		var files = document.getElementById('files').files;
		if (!files.length)
		{
		  alert('Please select a file!');
		  return;
		}
		
		var file = files[0];
//		var start = parseInt(opt_startByte) || 0;
		var start=0;
//		var stop = parseInt(opt_stopByte) || file.size - 1;
		var stop = file.size - 1;
		
		var reader = new FileReader();
		
		// If we use onloadend, we need to check the readyState.
		reader.onloadend = function(evt)
		{
			if (evt.target.readyState == FileReader.DONE)
			{ // DONE == 2
				import_cancel=false;
				$lines=evt.target.result.split("\n");
				$filesize=Math.round((f.size*100)/1048576)/100;
				$("#prices_import_dialog_status").html('<strong>'+f.name+'</strong> ('+$filesize+' Mb)<br />'+$lines.length+" Einträge gefunden.");
				$('#prices_import_dialog').dialog('option', 'buttons', 
				[
					{ text: "Importieren", click: function() { prices_import(0); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				] );
			}
		};
		
		var blob = file.slice(start, stop + 1) || file.mozSlice(start, stop + 1) || file.webkitSlice(start, stop + 1);
		reader.readAsBinaryString(blob);
		}		
	}

	function prices_import_options()
	{
		$("#prices_import_dialog").html('<table><tr><td>CSV-Datei</td><td><input type="file" id="files" name="files[]" multiple /></td></tr><tr><td>Status</td><td id="prices_import_dialog_status"></td></tr></table>');
		document.getElementById('files').addEventListener('change', handleFileSelect, false);
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			$("#prices_import_dialog_status").html("Browser ist HTML5-fähig.");
		}
		else
		{
			$("#prices_import_dialog").html("Die Datei-APIs werden in diesem Browser nicht unterstützt. Bitte benutzen Sie einen HTML5-fähigen Browser.");
		}

		$("#prices_import_dialog").dialog
		({	buttons:
			[
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Preise importieren",
			width:400
		});
	}
	
	function prices_import($i)
	{
		if ($i>=$lines.length)
		{
			$("#prices_import_dialog").html("CSV-Datei erfolgreich importiert.");
			$('#prices_import_dialog').dialog('option', 'buttons', [{ text: "Schließen", click: function() { $(this).dialog("close"); } }] );
			return;
		}

		if (import_cancel)
		{
			$("#prices_import_dialog").html("Vorgang abgebrochen.");
			$('#prices_import_dialog').dialog('option', 'buttons', [{ text: "Schließen", click: function() { $(this).dialog("close"); } }] );
			return;
		}

		$('#prices_import_dialog').dialog('option', 'buttons', [{ text: "Abbrechen", click: prices_import_cancel }] );
		$("#prices_import_dialog").html($i+" von "+$lines.length+" importiert.");
		$xml='<ShopItemUpdate>'+"\n";
		var $j=0;
		for($i; $i<$lines.length; $i++)
		{
			$line=$lines[$i].split(";");
			$xml+='<Item>'+"\n";
			$xml+='	<SKU>'+$line[0]+'</SKU>'+"\n";
			$xml+='	<COS>'+$line[3]+'</COS>'+"\n";
			$xml+='	<BRUTTO>'+$line[5]+'</BRUTTO>'+"\n";
			$xml+='	<MINDEST_VK>'+$line[9]+'</MINDEST_VK>'+"\n";
			$xml+='	<MENGE_360_TAGE>'+$line[14]+'</MENGE_360_TAGE>'+"\n";
			$xml+='	<KFZ_BESTAND_TECDOC>'+$line[15]+'</KFZ_BESTAND_TECDOC>'+"\n";
			$xml+='	<BESTAND_INL_ZENTRALE>'+$line[16]+'</BESTAND_INL_ZENTRALE>'+"\n";
			$xml+='	<BESTELLT>'+$line[17]+'</BESTELLT>'+"\n";
			$xml+='</Item>'+"\n";
			$j++;
			if ($j>50) break;
		}
		$xml+='</ShopItemUpdate>'+"\n";
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsUpdate", xml:$xml },
			function(data)
			{
				prices_import($i);
			}
		);
	}


	function price_check_options()
	{
		$("#price_check_dialog").dialog
		({	buttons:
			[
				{ text: "Preis prüfen", click: function() { price_check(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Preis prüfen",
			width:400
		});
	}


	function price_set($price)
	{
		var price = $("#price").val($price);
		view();
	}

	function price_check()
	{
		var artnr = $("#price_check_artnr").val();
		var price = $("#price_check_price").val();
		if ( typeof price === "undefined") price = 0;
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionsView", artnr:artnr, price:price },
			function(data)
			{
				$("#view").html(data);
				wait_dialog_hide();
				$("#price_check_dialog").dialog("close");
			}
		);
	}

	function prices_export()
	{
		$("#prices_export_dialog").dialog
		({	buttons:
			[
				{ text: "Preise herunterladen", click: function() { prices_export_download(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Preise exportieren",
			width:400
		});
	}


	function prices_export_download()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionsExport" },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				$("#prices_export_dialog").html('<p>Bitte laden Sie die Datei herunter, importieren Sie die Preise in der Warenwirtschaft und bestätigen Sie hinterher mit einem Klick auf den Button "Preisimport abschließen".;</p><br /><a href="<?php echo PATH; ?>soa/PriceSugestionsExport.csv">Download</a>');
				$('#prices_export_dialog').dialog('option', 'buttons', [{ text: "Preisimport abschließen", click: function() { prices_export_update(data); } }] );
				wait_dialog_hide();
			}
		);
	}


	function prices_export_update(data)
	{
		wait_dialog_show();
		$('#prices_export_dialog').dialog('option', 'buttons', [{ }] );
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionsUpdate", data:data },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				$("#prices_export_dialog").html('<p>Die Preise wurden erfolgreich übernommen.</p>');
				$('#prices_export_dialog').dialog('option', 'buttons', [{ text: "Schließen", click: function() { $(this).dialog("close"); } }] );
				wait_dialog_hide();
			}
		);
	}


	function price_accept()
	{
		var id_pricesuggestion=$("#id_pricesuggestion").val();
		var price=$("#price").val();
		var status=2; // ggf zu status 4
		wait_dialog_show("Speichere Preis...");
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionUpdate", id_pricesuggestion:id_pricesuggestion, price:price, status:status }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); return; }

			wait_dialog_show("Sende Preis an IDIMS...");
			$.post("<?php echo PATH; ?>soa/", { API:"idims", Action:"PriceUpdate", id_pricesuggestion:id_pricesuggestion }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); return; }
				
				show_status("Preisvorschlag erfolgreich akzeptiert und übernommen.");
				$("#price").val("");
				view();
				return;
			});
		});
	}

	function price_reject()
	{
		var id_pricesuggestion=$("#id_pricesuggestion").val();
		var price=$("#price").val();
		var status=3;
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionUpdate", id_pricesuggestion:id_pricesuggestion, price:price, status:status },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Preisvorschlag erfolgreich abgelehnt.");
				$("#price").val("");
				view();
			}
		);
	}

	function view()
	{
		var price = $("#price").val();
		if ( typeof price === "undefined") price = 0;
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionsView", price:price },
			function(data)
			{
				$("#view").html(data);
				wait_dialog_hide();
			}
		);
	}
</script>
<?php
	//IMPORT PRICES
	if ( isset($_FILES["file"]["tmp_name"]) and $_FILES["file"]["tmp_name"]!="" )
	{
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		while( $line=fgetcsv($handle, 4096, ";") )
		{
			q("	UPDATE shop_items
				SET		VORGABE_VK='".(float)str_replace(",", ".", $line[2])."',
						COS='".(float)str_replace(",", ".", $line[3])."',
						BRUTTO='".(float)str_replace(",", ".", $line[5])."',
						MINDEST_VK='".(float)str_replace(",", ".", $line[9])."',
						MENGE_360_TAGE='".$line[14]."',
						KFZ_BESTAND_TECDOC='".$line[15]."',
						BESTAND_INL_ZENTRALE='".$line[16]."',
						BESTELLT='".$line[17]."'
				WHERE MPN='".$line[0]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		}
		echo 'Preise erfolgreich importiert.';
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Preisvorschläge';
	echo '</p>';

	echo '<h1>Preisvorschläge';
	if ($_SESSION["userrole_id"]==1)
	{
		echo '	<img alt="Preis prüfen" onclick="price_check_options();" src="images/icons/24x24/help.png" style="cursor:pointer;" title="Preis prüfen" />';
		echo '	<img alt="Preise importieren" onclick="prices_import_options();" src="images/icons/24x24/up.png" style="cursor:pointer;" title="Preise importieren" />';
		echo '	<img alt="Preise exportieren" onclick="prices_export();" src="images/icons/24x24/down.png" style="cursor:pointer;" title="Preise exportieren" />';
	}
	echo '</h1>';


	//VIEW
	echo '<div id="view">';
	echo '</div>';
	echo '<script type="text/javascript"> view(); </script>';


	//PRICE CHECK DIALOG
	echo '<div id="price_check_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Artikelnummer</td>';
	echo '			<td>';
	echo '				<input id="price_check_artnr" type="text" value="" />';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Preis brutto</td>';
	echo '			<td>';
	echo '				<input id="price_check_price" type="text" value="" />';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';


	//PRICES IMPORT DIALOG
	echo '<div id="prices_export_dialog" style="display:none;">';
	echo '	<p>Bitte laden Sie die Preisliste herunter, indem Sie den Knopf "Preise herunterladen" anklicken.</p>';
	echo '</div>';


	//PRICES EXPORT DIALOG
	echo '<div id="prices_import_dialog" style="display:none;">';
	echo '</div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>