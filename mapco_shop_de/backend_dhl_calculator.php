<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
?>
	<script>
		function gross_weights_check()
		{
			wait_dialog_show("Überprüfe Bruttogewichte...");
			var $postdata=new Object();
			$postdata["API"]="dhl";
			$postdata["APIRequest"]="GrossWeightsCheck";
			$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
			{
				wait_dialog_hide();
				show_status2($data);
			});
		}
	
		function calculate(b)
		{
			if(b=="b")
				$('#dhl_cargo').prop('checked', false);;
			if( $('#dhl_cargo:checked').prop('checked') !== true )
			{
				$('#dhl_cargo').removeAttr('checked');
				var mes = new Array();
				mes.push($('#length').val());
				mes.push($('#width').val());
				mes.push($('#height').val());
				mes.sort(function(a,b){return a-b;});
				
				var maxi = mes[2];
				var medi = mes[1];
				var mini = mes[0];
				var gm = maxi*1 + 2*medi + 2*mini;
				if(((maxi>120 || medi>60 || mini>60) || gm>300) && maxi<=200 && gm<=360)
				{
					$('#dhl_cargo').prop('checked', true);
				}
				if(maxi>200 || gm>360)
					alert("Das Paket ist zu groß für Sperrgut!");
			}
			
			var cod = '';
			if($('#dhl_cod:checked').val()=='cod') cod = 'cod';
			var cargo = '';
			if($('#dhl_cargo:checked').val()=='cargo') cargo = 'cargo';//hier auch noch ändern?

			var $id_country=$("#id_country").val();

			var $shipping_type=$("#shipping_type").val();
			if( $shipping_type==1 ) $("#cod").show(); else $("#cod").hide();
			if( $shipping_type==1 ) $("#cargo").show(); else $("#cargo").hide();
			if( $shipping_type==2 ) $("#special").show(); else $("#special").hide();
			if( $shipping_type==2 ) $("#volwei").show(); else $("#volwei").hide();
			if( $("#express8").is(':checked') ) var $express8="checked"; else $express8="";
			if( $("#express9").is(':checked') ) var $express9="checked"; else $express9="";
			if( $("#express10").is(':checked') ) var $express10="checked"; else $express10="";
			if( $("#express12").is(':checked') ) var $express12="checked"; else $express12="";
			if( $("#saturdayexpress").is(':checked') ) var $saturdayexpress="checked"; else $saturdayexpress="";
			if( $("#sundayexpress").is(':checked') ) var $sundayexpress="checked"; else $sundayexpress="";

			var $WeightInKG=$("#WeightInKG").val();
			if($WeightInKG=="")
			{
				$("#OurNetPrice").html("-");
				$("#OurNetPriceWithFuelSurcharge").html("-");
				$("#OurGrossPrice").html("-");
				$("#CustomerNetPrice").html("-");
				$("#CustomerGrossPrice").html("-");
				return;
			}
			
			if($shipping_type==2)
			{
				var vw = ($('#vwlength').val()*$('#vwwidth').val()*$('#vwheight').val())/5000
				$('#volweight').val(vw);
				if(vw>$WeightInKG)
					$WeightInKG=vw;
			}

			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"dhl", Action:"ShipmentCostsGet", shipping_type:$shipping_type, id_country:$("#id_country").val(), WeightInKG:$WeightInKG, express8:$express8, express9:$express9, express10:$express10, express12:$express12, saturdayexpress:$saturdayexpress, sundayexpress:$sundayexpress, COD: cod, cargo: cargo},
				function($data)
				{
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack").text();
						if ( $ack!="Success" )
						{
							$("#OurNetPrice").html("???");
							$("#OurNetPriceWithFuelSurcharge").html("???");
							$("#OurGrossPrice").html("???");
							$("#CustomerNetPrice").html("???");
							$("#CustomerGrossPrice").html("???");
							wait_dialog_hide();
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message+'<br />'+$data);
						wait_dialog_hide();
						return;
					}
					//additional
					if( $xml.find("Additional").length == 0 ) $("#additional").hide();
					else
					{
						$("#additional").show();
						$("#Additional").html($xml.find("Additional").text());
					}
					//runtime
					if( $xml.find("Runtime").length == 0 ) $("#runtime").hide();
					else
					{
						$("#runtime").show();
						$("#Runtime").html($xml.find("Runtime").text());
					}
					//net price
					$("#OurNetPrice").html($xml.find("OurNetPrice").text()+" €");
					//net price with fuel surcharge price
					if( $xml.find("OurNetPriceWithFuelSurcharge").length == 0 ) $("#fuel_price").hide();
					else
					{
						$("#fuel_price").show();
						$("#FuelSurcharge").html($xml.find("FuelSurcharge").text());
						$("#OurNetPriceWithFuelSurcharge").html($xml.find("OurNetPriceWithFuelSurcharge").text()+" €");
					}
					$("#OurGrossPrice").html($xml.find("OurGrossPrice").text()+" €");
					$("#CustomerNetPrice").html($xml.find("CustomerNetPrice").text()+" €");
					$("#CustomerGrossPrice").html($xml.find("CustomerGrossPrice").text()+" €");
					wait_dialog_hide();
				}
			);
		}
	</script>
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_dhl_index.php">DHL</a>';
	echo ' > Versandkostenrechner';
	echo '</p>';

	echo '<h1>Versandkostenrechner</h1>';
	echo '<img alt="Bruttogewichte prüfen" src="'.PATH.'images/icons/24x24/help.png" onclick="gross_weights_check();" style="cursor:pointer;" title="Bruttogewichte prüfen" />';

	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Versandart</td>';
	echo '			<td>';
	echo '				<select id="shipping_type" onchange="calculate();">';
	echo '					<option value="1">DHL</option>';
	echo '					<option value="2">DHL Express</option>';
	echo '					<option value="3">DHL Retoure</option>';
	echo '					<option value="4">DHL Economy Select Import (Abholung)</option>';
	echo '					<option value="5">DHL Economy Select Export</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Land</td>';
	echo '			<td>';
	echo '				<select id="id_country" onchange="calculate();">';
	$results=q("SELECT * FROM shop_countries ORDER BY country;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $row["country_code"]=="DE" ) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["id_country"].'">'.$row["country"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr id="special" style="display:none;">';
	echo '			<td>Sonderoptionen</td>';
	echo '			<td>';
	
	echo '				<input id="express8" onclick="calculate();" type="checkbox" /> Zustellung vor 8:00<br />';
	echo '				<input id="express9" onclick="calculate();" type="checkbox" /> Zustellung vor 9:00<br />';
	echo '				<input id="express10" onclick="calculate();" type="checkbox" /> Zustellung vor 10:00 <br />';
	echo '				<input id="express12" onclick="calculate();" type="checkbox" /> Zustellung vor 12:00<br />';
	echo '				<input id="saturdayexpress" onclick="calculate();" type="checkbox" /> Samstagszustellung<br />';
	echo '				<input id="sundayexpress" onclick="calculate();" type="checkbox" /> Sonn-/Feiertagszustellung<br />';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Paketgewicht</td>';
	echo '			<td><input style="width:30px;" type="text" id="WeightInKG" value="" onkeyup="calculate();" /> kg</td>';
	echo '		</tr>';
	echo '		<tr id="cargo">';
	echo '			<td>Sperrgut</td>';
	echo '			<td>
						<input type="checkbox" id="dhl_cargo" onclick="calculate()" value="cargo"><br />
						<input type="text" id="length" style="width: 30px" value="15" onkeyup="calculate(\'b\');"> Länge (in cm)<br />
						<input type="text" id="width" style="margin-top: 3px; width: 30px" value="11" onkeyup="calculate(\'b\');"> Breite (in cm)<br />
						<input type="text" id="height" style="margin-top: 3px; width: 30px" value="1" onkeyup="calculate(\'b\');"> Höhe (in cm)
					</td>';
	echo '		</tr>';
	echo '		<tr id="volwei" style="display: none">';
	echo '			<td>Volumengewicht</td>';
	echo '			<td>
						<input type="text" id="volweight" style="width: 50px" value="0.033" readonly>kg<br />
						<input type="text" id="vwlength" style="margin-top: 2px; width: 30px" value="15" onkeyup="calculate();"> Länge (in cm)<br />
						<input type="text" id="vwwidth" style="margin-top: 3px; width: 30px" value="11" onkeyup="calculate();"> Breite (in cm)<br />
						<input type="text" id="vwheight" style="margin-top: 3px; width: 30px" value="1" onkeyup="calculate();"> Höhe (in cm)
					</td>';
	echo '		</tr>';
	echo '		<tr id="cod">';
	echo '			<td>Nachnahme</td>';
	echo '			<td><input type="checkbox" id="dhl_cod" onclick="calculate()" value="cod"> (zzgl. 2€ Nachnahmegebühr)</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2"><input type="button" onclick="calculate();"  value="Berechnen" /></td>';
	echo '		</tr>';
	echo '		<tr id="runtime" style="display:none;">';
	echo '			<td>Laufzeit</td>';
	echo '			<td><span id="Runtime">-</span> Tage</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Unser Nettopreis</td>';
	echo '			<td><span id="OurNetPrice">-</span></td>';
	echo '		</tr>';
	echo '		<tr id="fuel_price" style="display:none;">';
	echo '			<td>mit <span id="FuelSurcharge">-</span>% Treibstoffzuschlag </td>';
	echo '			<td><span id="OurNetPriceWithFuelSurcharge">-</span></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Unser Bruttopreis</td>';
	echo '			<td><span id="OurGrossPrice">-</span></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Kunden-Nettopreis</td>';
	echo '			<td><span id="CustomerNetPrice">-</span></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Kunden-Bruttopreis</td>';
	echo '			<td><span style="font-weight:bold; font-size:20px;" id="CustomerGrossPrice">-</span></td>';
	echo '		</tr>';
	echo '		<tr id="additional" style="display:none;">';
	echo '			<td>Zusatzinformationen</td>';
	echo '			<td><span id="Additional"></span></td>';
	echo '		</tr>';
	echo '	</table>';
	
	echo '</div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>