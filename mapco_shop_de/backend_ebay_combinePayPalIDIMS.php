<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

?>

<script type="text/javascript">

	function add_missingItem()
	{
	
		$("#LinkPayPalIDIMS").dialog
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
			title:"Zuordnung von PayPal TransactionID und IDIMS Auftragsnummer",
			width:600
		});	
	}

</script>


<?

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">Ebay</a>';
	echo ' > Verknüpfung PayPal <-> IDIMS';
	echo '</p>';
	echo '<h1>Verknüpfung PayPal TransactionID <-> IDIMS Auftragsnummer</h1>';
	
	
	//DIALOGBOX
	echo '<div id="LinkPayPalIDIMS" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>PayPal Transaction ID</td>';
	echo '		<td><input type="text" name="PayPalID" id="PayPalTransactionID" size="20" \>';
	echo '		<input type="button" name="btn1">OK</button></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>IDIMS Auftragsnummer</td>';
	echo '		<td><input type="text" name="Anzahl" id="missingItemQty" size="3" \></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Details zur PayPal Transaction ID</td>';
	echo '		<td></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2" id="paypaldetails"></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>