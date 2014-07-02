<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

?>

<script type="text/javascript">
var PN_ID="";

	function TransformData()
	{
		var data=$("#PayPalTransactionID").val();
		if (data.indexOf("Transaktionscode: ")>-1)
		{
			data=data.substr(data.indexOf("Transaktionscode: ")+18,17);
			$("#PayPalTransactionID").val(data);
		}
		
		GetPayPalTransactionData();
	}

	function clear_input()
	{
		$("#IDIMS_Auftragsnummer").val("");
		$("#notificationtext").text("");
		$(".infofield").hide();
		$("#PayPalTransactionID").val("");
	}

	function GetPayPalTransactionData()
	{
		var PayPalTransactionID=$("#PayPalTransactionID").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "ebay", Action: "GetPayPalTransactionData", PayPalTransactionID:PayPalTransactionID},
			function(data)
			{
				$("#InfoField").html("");
				$("#notificationtext").val("");
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
						/*		$(xml).find("Tutorial").each(function()
					  {
						$("#output").append($(this).attr("author") + "<br />");
					  });

						*/
						
					PN_ID=$xml.find("transactionPN_ID").text();
					var state=$xml.find("transactionState").text();
					var IDIMS_ID=$xml.find("transactionIDIMS_ID").text();
					
					if (state=="Created" || state=="Pending")
					{
						$("#notificationtext").val("Die Zahlung ist noch ausstehend");
						$("#IDIMS_Auftragsnummer").attr("disabled", false);
						$("#IDIMS_Auftragsnummer").focus();

					}
					if (state=="Denied")
					{
						$("#notificationtext").val("Die Zahlung wurde abgebrochen");
						$("#IDIMS_Auftragsnummer").attr("disabled", false);
						$("#IDIMS_Auftragsnummer").focus();
					}
					if (state=="Refunded")
					{
						$("#notificationtext").text("Die Zahlung wurde bereits erstattet.");
						$("#IDIMS_Auftragsnummer").attr("disabled", false);
						$("#IDIMS_Auftragsnummer").focus();

						if (IDIMS_ID!="0") 
						{
							$("#notificationtext").append("\n Die Transaktion wurde bereits verknüpft");
							$("#IDIMS_Auftragsnummer").val(IDIMS_ID);
						}
					}
					if (state=="Completed" || state=="")
					{
						$("#IDIMS_Auftragsnummer").attr("disabled", false);
						$("#IDIMS_Auftragsnummer").focus();

						if (IDIMS_ID!="0") 
						{
							$("#notificationtext").append("Die Transaktion wurde bereits verknüpft");
							$("#IDIMS_Auftragsnummer").val(IDIMS_ID);
						}
					}
					
				
					$("#InfoField").append("<b>Plattform: </b>"+$xml.find("transactionPlatform").text()+"<br />");
					$("#InfoField").append("<b>PayPalSumme: </b>"+$xml.find("transactionPaymentTotal").text()+"<br />");
					$("#InfoField").append("<b>Bestellungssumme: </b>"+$xml.find("transactionOrderTotal").text()+"<br />");
					$("#InfoField").append("<b>KäuferID: </b>"+$xml.find("transactionBuyerID").text()+"<br />");
					$("#InfoField").append("<b>Käufername: </b>"+$xml.find("transactionShipName").text()+"<br /><br />");
										
					$($xml).find("transactionItem").each(function()
					{
						$("#InfoField").append("<b>Titel: </b>"+$(this).find("transactionItemTitle").text()+"<br />");
						$("#InfoField").append("<b>SKU: </b>"+$(this).find("transactionItemSKU").text()+"<br />");
						$("#InfoField").append("<b>Preis: </b>"+$(this).find("transactionItemPrice").text()+"<br />");
						$("#InfoField").append("<b>Anzahl: </b>"+$(this).find("transactionItemQuantity").text()+"<br /><br />");
					});
					
					$(".infofield").show();

				}
				else 
				{
					show_status2(data);
				
				}
			}
		);
	}

	function add_linkPayPalIDIMS()
	{
	
		$("#LinkPayPalIDIMS").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { save_linkPayPalIDIMS();} },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); clear_input();} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Zuordnung von PayPal TransactionID und IDIMS Auftragsnummer",
			width:800,
			height:600
		});	
	}
	
	function save_linkPayPalIDIMS()
	{
		var IDIMS_ID = $("#IDIMS_Auftragsnummer").val();
		if (!$("#IDIMS_Auftragsnummer").attr("disabled"))
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API: "ebay", Action: "LinkPayPalIDIMS", IDIMS_ID:IDIMS_ID, ID_PN:PN_ID},
				function(data)
				{
					wait_dialog_hide();
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						
						show_status("PayPal Transaktion und IDIMS Auftragsnummer wurden erfolgreich verknüpft");
						$("#LinkPayPalIDIMS").dialog("close");
						add_linkPayPalIDIMS();
						clear_input();
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
			alert("Zu dieser PayPal Transaktion kann keine Verknüpfung zu einem IDIMS Auftrag vorgenommen werden!");
		}
	}
	
	function keycheck()
	{
		if (event.keyCode == 13)
		{
			save_linkPayPalIDIMS();
		}
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

	echo '<div>';
	echo '<table>';
	echo '<tr>';
	echo '	<th>';
	echo '	Eine PayPal Transaktion mit einem IDIMS Auftrag verknüfen';
	echo '	</th>';
	echo '	<th>';
	echo '	<img src="'.PATH.'images/icons/24x24/add.png" style="cursor:pointer; float:right;" alt="Eine PayPal Transaktion mit einem IDIMS Auftrag verknüfen" title="Eine PayPal Transaktion mit einem IDIMS Auftrag verknüfen" onclick="add_linkPayPalIDIMS();" />';
	echo '	</th>';
	echo '</tr>';
	echo '</table>';	
	echo '</div>';
	
	//DIALOGBOX
	echo '<div id="LinkPayPalIDIMS" style="display:none;">';
	echo '<table style="width:750px;">';
	echo '	<tr>';
	echo '		<td style="width:350x;">PayPal Transaction ID</td>';
//	echo '		<td style="width:400x;"><input type="text" name="PayPalID" id="PayPalTransactionID" size="20" \>';
	echo '		<td style="width:400x;"><textarea name="PayPalID" id="PayPalTransactionID" cols="25" rows="1" \></textarea>';
//	echo '		<button name="btn1" onClick="GetPayPalTransactionData();">OK</button></td>';
	echo '		<button name="btn1" onClick="TransformData();">OK</button></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>IDIMS Auftragsnummer</td>';
	echo '		<td><input type="text" name="IDIMS" id="IDIMS_Auftragsnummer" size="10" disabled="disabled"\ onKeyUp="keycheck();"><span id="notificationtext" style="color:red"></span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2" class="infofield" style="display:none">Details zur PayPal Transaction ID</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2" class="infofield" style="display:none" id="InfoField"></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	echo '<script type="text/javascript">add_linkPayPalIDIMS();</script>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>