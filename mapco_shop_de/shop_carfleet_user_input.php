<?php
	include("config.php");
	$title='MAPCO Autoteile Shop / KFZ Teile 24 Stunden am Tag günstig kaufen!';
	include("templates/".TEMPLATE."/header.php");
	include("functions/shop_show_item.php");
/*	include("functions/shop_itemstatus.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_get_titles.php");	
	include("functions/mapco_motorart.php");
	include("functions/mapco_baujahr.php");
	include("functions/cms_url_encode.php");*/
//	echo '*****'.$_SESSION["id_user"];
	include("modules/cms_leftcolumn_shop.php");
//	include("modules/shop_login_box.php");
//	include("modules/shop_searchbycar.php");
?>

<script src="modules/CRM/OM_vehicle_orderitem_correlation.js" type="text/javascript" /></script>
<script type="text/javascript">

	function do_reset_color()
	{
		document.getElementById("tsn").style.color = "";
		document.getElementById("hsn").style.color = "";
	}
		
	function get_car_data()
	{
		do_reset_color();
		var hsn=$("#hsn").val();
	    var tsn=$("#tsn").val();
		
		hsn = hsn.trim();
		if(hsn.length<4)
		{
			while(hsn.length<4)
			{
				hsn = "0" + hsn;
			}
		}
		$("#hsn").val(hsn);
		
		tsn = tsn.trim();
		if(tsn.length<3)
		{
			show_message_dialog("Bitte mindestens die ersten drei Ziffern der TSN-Nr. eingeben!");
			document.getElementById("tsn").style.color = "red";
			$("#brand").val("");
			$("#model").val("");
			$("#type").val("");
			$("#vehicle_id").val("");
			return;
		}
		tsn = tsn.substr(0,3);
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "get_vehicle_byKBA", TSN: tsn, HSN: hsn},
			function (data)
			{
				//alert(data);
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						if($xml.find("vehicleBrand").text()=="")
						{
							show_message_dialog("Zu diesen HSN/TSN-Nummern wurde kein Fahrzeug gefunden.");
							$("#brand").val("");
							$("#model").val("");
							$("#type").val("");
							$("#vehicle_id").val("");
							document.getElementById("tsn").style.color = "red";
							document.getElementById("hsn").style.color = "red";
							return;
						}
						$("#brand").val($xml.find("vehicleBrand").text());
						$("#model").val($xml.find("vehicleModel").text());
						$("#type").val($xml.find("vehicleModelType").text());
						$("#vehicle_id").val($xml.find("vehicleID").text());
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
			}
		);
	}
	
	function safe_car_data()
	{
		do_reset_color();
		var hsn=$("#hsn").val();
	    var tsn=$("#tsn").val();
		
		hsn = hsn.trim();
		if(hsn.length<4)
		{
			while(hsn.length<4)
			{
				hsn = "0" + hsn;
			}
		}
		$("#hsn").val(hsn);
		
		tsn = tsn.trim();
		if(tsn.length<3)
		{
			show_message_dialog("Bitte mindestens die ersten drei Ziffern der TSN-Nr. eingeben!");
			document.getElementById("tsn").style.color = "red";
			$("#brand").val("");
			$("#model").val("");
			$("#type").val("");
			return;
		}
		tsn = tsn.substr(0,3);
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate"},
			function (data)
			{
				wait_dialog_hide();
				//alert(data);
			}
		);
	}
	
	function show_message_dialog(message)
	{
		$("#message").html(message);
		$("#message").dialog
		({	buttons:
			[
				{ text: "Ok", click: function() {$(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Achtung!",
			width:300
		});
	}
	
</script>

<?php
	echo '<div id="mid_right_column">';
			echo '<table>';
			
			echo '<tr><div id="search_by_car_manufacturer_box"></div>
				      <div id="search_by_car_modell_box"></div>
					  <div id="search_by_car_type_box"></div></tr>';
			?>
    <script type="text/javascript">
		soa_path = "<?php echo PATH; ?>soa/";
		show_select_search_by_car_manufacturer();
    </script>
    <?php
			
	
			echo '<tr>';
				echo '<td>Hersteller:</td>';
				echo '<td><input type="text" id="brand" readonly="readonly"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Modell:</td>';
				echo '<td><input type="text" id="model" readonly="readonly"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Typ:</td>';
				echo '<td><input type="text" id="type" readonly="readonly"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Fahrzeugschein (zu 2(HSN)/ zu 3(TSN)):</td>';
				echo '<td><input type="text" id="hsn" size="3" maxlength="4">/
				          <input type="text" id="tsn"></td>';
				echo '<td><button onclick="get_car_data();">Fahrzeugdaten laden</button></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Erstzulassung (mm/jjjj):</td>';
				echo '<td><input type="text" id="month" size="3" maxlength="2">/
				          <input type="text" id="year" size="3" maxlength="4"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Fahrgestellnummer:</td>';
				echo '<td><input type="text" id="fin" name="fin" maxlength="17"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td><button onclick="safe_car_data();">Fahrzeugdaten speichern</button></td>';
				echo '<td></td>';
				echo '<td></td>';
			echo '</tr>';
		echo '</table>';
		echo '<input id="vehicle_id">';
	echo '</div>';
	echo  '<p id="message" style="display:none;">dddd</p>';
	
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">
	$("#hsn").val("0600");
	$("#tsn").val("607");
	$("#month").val("08");
	$("#year").val("1992");
	$("#fin").val("ksejdirhn56leo345");
</script>