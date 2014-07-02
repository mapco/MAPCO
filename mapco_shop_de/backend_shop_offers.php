<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
	
		function offer_add()
		{
			$("#offer_add_artnr").val("");
			$("#offer_add_discount").val("0");
//			$("#offer_add_from").val("");
//			$("#offer_add_until").val("");
			$("#offer_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { offer_add_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Angebot hinzufügen",
				width:350,
				height:350
			});
		}
		
		
		function offer_add_save()
		{
			var artnr=$("#offer_add_artnr").val();
			var offertype_id=$("#offer_add_offertype_id").val();
			alert(offertype_id);
			var discount=$("#offer_add_discount").val();
			var from=$("#offer_add_from").val();
			var until=$("#offer_add_until").val();
			$.post("modules/backend_shop_offers_actions.php", { action:"offer_add", artnr:artnr, offertype_id:offertype_id, discount:discount, from:from, until:until },
				function(data)
				{
					if (data!="") show_status(data);
					else
					{
						show_status("Angebot erfolgreich abgespeichert.");
						view();
						$("#offer_add_dialog").dialog("close");
					}
				}
			);
		}


		function offer_add_results(artnr)
		{
			$.post("modules/backend_shop_offers_actions.php", { action:"offer_add_results", artnr:artnr },
				function(data)
				{
					$("#offer_add_results").show();
					$("#offer_add_results").html(data);
				}
			);
		}


		function offer_add_select(artnr)
		{
			$("#offer_add_artnr").val(artnr);
			$("#offer_add_results").hide();
		}


		function offer_edit(id_offer, offertype_id, artnr, discount, from, until)
		{
			$("#offer_edit_id_offer").val(id_offer);
			$("#offer_edit_offertype_id").val(offertype_id);
			$("#offer_edit_artnr").val(artnr);
			$("#offer_edit_discount").val(discount);
			$("#offer_edit_from").val(from);
			$("#offer_edit_until").val(until);
			$("#offer_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { offer_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Angebot bearbeiten",
				width:350,
				height:350
			});
		}
		
		
		function offer_edit_results(artnr)
		{
			$.post("modules/backend_shop_offers_actions.php", { action:"offer_edit_results", artnr:artnr },
				function(data)
				{
					$("#offer_edit_results").show();
					$("#offer_edit_results").html(data);
				}
			);
		}


		function offer_edit_save()
		{
			var id_offer=$("#offer_edit_id_offer").val();
			var offertype_id=$("#offer_edit_offertype_id").val();
			var artnr=$("#offer_edit_artnr").val();
			var discount=$("#offer_edit_discount").val();
			var from=$("#offer_edit_from").val();
			var until=$("#offer_edit_until").val();
			$.post("modules/backend_shop_offers_actions.php", { action:"offer_edit", id_offer:id_offer, offertype_id:offertype_id, artnr:artnr, discount:discount, from:from, until:until },
				function(data)
				{
					if (data!="") show_status(data);
					else
					{
						show_status("Angebot erfolgreich aktualisiert.");
						view();
						$("#offer_edit_dialog").dialog("close");
					}
				}
			);
		}


		function offer_edit_select(artnr)
		{
			$("#offer_edit_artnr").val(artnr);
			$("#offer_edit_results").hide();
		}


		function offer_remove(id_offer)
		{
			$("#offer_remove_id_offer").val(id_offer);
			$("#offer_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() { offer_remove_accept(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Angebot wirklich löschen?",
				width:350
			});
		}
		
		
		function offer_remove_accept()
		{
			var id_offer=$("#offer_remove_id_offer").val();
			$.post("modules/backend_shop_offers_actions.php", { action:"offer_remove", id_offer:id_offer },
				function(data)
				{
					if (data!="") show_status(data);
					else
					{
						show_status("Angebot erfolgreich gelöscht.");
						view();
						$("#offer_remove_dialog").dialog("close");
					}
				}
			);
		}


		function view()
		{
			var from = $("#datepicker_from").val();
			var to = $("#datepicker_to").val();
			$.post("modules/backend_shop_offers_actions.php", { action:"view", from:from, to:to },
				function(data)
				{
					$("#view").html(data);
				$.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
								closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
								prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
								nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
								currentText: 'heute', currentStatus: '',
								monthNames: ['Januar','Februar','März','April','Mai','Juni',
								'Juli','August','September','Oktober','November','Dezember'],
								monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
								'Jul','Aug','Sep','Okt','Nov','Dez'],
								monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
								weekHeader: 'Wo', weekStatus: 'Woche des Monats',
								dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
								dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
								dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
								dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
								dateFormat: 'dd.mm.yy', firstDay: 1, 
								initStatus: 'Wähle ein Datum', isRTL: false};
					$.datepicker.setDefaults($.datepicker.regional['de']);
					$(function()
					{
						$( "#datepicker_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1, onSelect: function(date) {view();} });
					});
					$(function()
					{
						$( "#datepicker_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1, onSelect: function(date) {view();} });
					});
					$(function()
					{
						$( "#offer_add_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					$(function()
					{
						$( "#offer_add_until" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					$(function()
					{
						$( "#offer_edit_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					$(function()
					{
						$( "#offer_edit_until" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
				}
			)
		}
	
	</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Angebote';
	echo '</p>';
	
	//FILTER
	$dow=date("w", time()) - 1;
	if ( $dow<0 ) $dow=6;
	$from=date("d.m.Y", time() - ($dow*24*3600));
	$until=date("d.m.Y", time() + ((6-$dow)*24*3600));
	$from2=date("d.m.Y", time() + (7*24*3500) - ($dow*24*3600));
	$until2=date("d.m.Y", time() + (7*24*3600) + ((6-$dow)*24*3600));
	echo '<table>';
	echo '	<tr><th colspan="3">Anzeigefilter</th></tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			Zeitraum von<br />';
//	echo '			<div id="datepicker_from"></div>';
	echo '			<input type="text" id="datepicker_from" value="'.$from.'" />';
	echo '		</td>';
	echo '		<td>';
	echo '			Zeitraum bis<br />';
	echo '			<input type="text" id="datepicker_to" value="'.$until.'" />';
	echo '		</td>';
//	echo '		<td>';
//	echo '			<input type="button" value="Anzeigen" onclick="view();" />';
//	echo '		</td>';
	echo '	</tr>';
	echo '</table>';

	//VIEW
	echo '<div id="view"></div>';	
	echo '<script type="text/javascript"> view(); </script>';

	//OFFER ADD DIALOG
	echo '<div id="offer_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Artikelnummer</td>';
	echo '			<td>';
	echo '				<input id="offer_add_artnr" type="text" value="" onkeyup="offer_add_results(this.value);" onclick="offer_add_results(this.value);" />';
	echo '				<div style="display:none; position:absolute; border:1px solid red; padding:3px; background:#ffffff;" id="offer_add_results"></div>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Angebotstyp</td>';
	echo '			<td>';
	echo '				<select id="offer_add_offertype_id">';
	$results=q("SELECT * FROM shop_offertypes ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '	<option value="'.$row["id_offertype"].'">'.$row["title"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Rabatt</td>';
	echo '			<td><input id="offer_add_discount" type="text" value="0" style="width:30px;" />%</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zeitraum von</td>';
	echo '			<td><input id="offer_add_from" type="text" value="'.$from2.'" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zeitraum bis</td>';
	echo '			<td><input id="offer_add_until" type="text" value="'.$until2.'" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';

	//OFFER EDIT DIALOG
	echo '<div id="offer_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Artikelnummer</td>';
	echo '			<td>';
	echo '				<input id="offer_edit_artnr" type="text" value="" onkeyup="offer_edit_results(this.value);" onclick="offer_edit_results(this.value);" />';
	echo '				<div style="display:none; position:absolute; border:1px solid red; padding:3px; background:#ffffff;" id="offer_edit_results"></div>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Angebotstyp</td>';
	echo '			<td>';
	echo '				<select id="offer_edit_offertype_id">';
	$results=q("SELECT * FROM shop_offertypes ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '	<option value="'.$row["id_offertype"].'">'.$row["title"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Rabatt</td>';
	echo '			<td><input id="offer_edit_discount" type="text" value="" style="width:30px;" />%</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zeitraum von</td>';
	echo '			<td><input id="offer_edit_from" type="text" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zeitraum bis</td>';
	echo '			<td><input id="offer_edit_until" type="text" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input id="offer_edit_id_offer" type="hidden" value="" />';
	echo '</div>';

	//OFFER REMOVE DIALOG
	echo '<div id="offer_remove_dialog" style="display:none;">';
	echo '	Wollen Sie das Angebot wirklich löschen?';
	echo '	<input id="offer_remove_id_offer" type="hidden" value="" />';
	echo '</div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>