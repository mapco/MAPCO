<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

/*	
	if( $_SESSION["id_user"]!=21371 )
	{
		echo 'Wartungsarbeiten. Bitte versuchen Sie es später erneut.';
		exit;
	}
*/

	echo '<style> .highlight_word { background-color: pink; } </style>';
?>
	<script>
		var $orders;
		var shipment_items=1;
	
		$(document).ready(function()
		{
			$("#orders_from").datepicker( { "dateFormat":"D dd.mm.yy", firstDay:1, onSelect:function() {orders_get(true)}, showOtherMonths: true, selectOtherMonths: true });
			$("#orders_to").datepicker( { "dateFormat":"D dd.mm.yy", firstDay:1, onSelect:function() {orders_get(true)}, showOtherMonths: true, selectOtherMonths: true });
			 orders_get(true);
/*			 
			window.onkeypress = function(e)
			{
				if( !$("#needle").is(":focus") )
				{
					$("#needle").focus();
				}
			}
*/
		});
		
		//trim function if not available
		if (!String.prototype.trim)
		{
			String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g, '');};
			
			String.prototype.ltrim=function(){return this.replace(/^\s+/,'');};
			
			String.prototype.rtrim=function(){return this.replace(/\s+$/,'');};
			
			String.prototype.fulltrim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};			
		}

		//printer dialog delay
		$(function()
		{
			$('#print_pdf').load(function()
			{
				if ( $("#print_pdf").attr("src") != "" )
				{
					//delay for bigger documents
					setTimeout(print_on_ready, 5000);
				}
			});
		});
		
		function print_on_ready()
		{
			wait_dialog_hide();
//			window.frames["printframe"].focus();
			window.frames["printframe"].print();
		}

		function preg_quote( str ) {
			// http://kevin.vanzonneveld.net
			// +   original by: booeyOH
			// +   improved by: Ates Goral (http://magnetiq.com)
			// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: Onno Marsman
			// *     example 1: preg_quote("$40");
			// *     returns 1: '\$40'
			// *     example 2: preg_quote("*RRRING* Hello?");
			// *     returns 2: '\*RRRING\* Hello\?'
			// *     example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
			// *     returns 3: '\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:'
		
			return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
		}
		function highlight( data, search )
		{
			return data.replace( new RegExp( "(" + preg_quote( search ) + ")" , 'gi' ), '<span class="highlight_word">$1</span>' );
		}

		function label_create()
		{
			//check for Express International
			if( $("#order_ProductCode").val()=="EXP" && $("#order_ReceiverOrigin").val()!="DE" )
			{
				alert("Internationale Expresssendungen lassen sich derzeit nur über Intraship anlegen.");
				return;
			}
			//check for Europaket
			if( $("#order_ProductCode").val()=="EPI" && $("#order_ReceiverOrigin").val()=="DE" )
			{
				alert("Europakete können nur ins europäische Ausland versendet werden.");
				return;
			}
			//check for Receiver Name
			$("#order_ReceiverCompany").val($.trim($("#order_ReceiverCompany").val()));
			if( $("#order_ReceiverCompany").val().length<2 )
			{
				alert("Das Feld Empfänger -> Firma / Name muss mindestens 2 Zeichen lang sein.");
				return;
			}
			//check for ReceiverCompany field length
			if( $("#order_ReceiverCompany").val().length>30 )
			{
				alert("Das Feld Empfänger -> Firma darf maximal 30 Zeichen lang sein.");
				return;
			}
			//check for ReceiverCompany2 field length
			if( $("#order_ReceiverCompany2").val().length>30 )
			{
				alert("Das Feld Empfänger -> Firma 2 darf maximal 30 Zeichen lang sein.");
				return;
			}
			//check for ReceiverContactPerson
			$("#order_ReceiverContactPerson").val($.trim($("#order_ReceiverContactPerson").val()));
			if( $("#order_ReceiverContactPerson").val()=="" )
			{
				$("#order_ReceiverContactPerson").val($("#order_ReceiverCompany").val());
			}
			if( $("#order_ReceiverContactPerson").val()=="" )
			{
				$("#order_ReceiverContactPerson").val($("#order_ReceiverCompany2").val());
			}
			if( $("#order_ReceiverContactPerson").val()=="" )
			{
				alert("Das Feld Empfänger -> Ansprechpartner muss ausgefüllt sein.");
				return;
			}
			//check for ReceiverContactPerson field length
			if( $("#order_ReceiverContactPerson").val().length>30 )
			{
				alert("Das Feld Empfänger -> Ansprechpartner darf maximal 30 Zeichen lang sein.");
				return;
			}
			//check for ReceiverStreetNumber number
			if( $("#order_ReceiverStreetName").val()=="" )
			{
				alert("Die Straße des Empfängers darf nicht leer sein.");
				return;
			}
			//check for Packstation
			if( $("#order_ReceiverStreetName").val().indexOf("Packstation") >-1 )
			{
				if( $("#order_ReceiverStreetName").val() != "Packstation"
					|| !is_numeric( $("#order_ReceiverStreetNumber").val() )
					|| !is_numeric( $("#order_ReceiverCompany2").val() ) )
				{
					alert("Bei Packstationssendungen an Firmen müssen die Felder wie folgt befüllt werden.\n\nEmpfänger -> Straße: 'Packstation'\n\nEmpfänger -> Hausnummer: die Packstationsnummer (z.B. 123)\n\nEmpfänger -> Firma 2 / Packstation: die Kundennummer (z.B. 12345678)");
					return;
				}
			}
			//check for ReceiverStreetNumber number
			if( $("#order_ReceiverStreetNumber").val()=="" )
			{
				alert("Die Hausnummer des Empfängers darf nicht leer sein.");
				return;
			}
			//check for order_ReceiverZip number
			$("#order_ReceiverZip").val($.trim($("#order_ReceiverZip").val()));
			if( $("#order_ReceiverZip").val()=="" )
			{
				alert("Die Postleitzahl des Empfängers darf nicht leer sein.");
				return;
			}
			//check for order_ReceiverZip DENMARK
			if( $("#order_ReceiverOrigin").val()=="DK" && $("#order_ReceiverZip").val().length!=4 )
			{
				alert("Die Postleitzahlen in Dänemark müssen 4 Stellen haben.");
				return;
			}
			//check for order_ReceiverZip FAROER
			if( $("#order_ReceiverOrigin").val()=="DK" && $("#order_ReceiverZip").val()=="700" )
			{
				$("#order_ReceiverOrigin").val("FO");
			}
			//check for order_ReceiverZip GERMANY
			if( $("#order_ReceiverOrigin").val()=="DE" && $("#order_ReceiverZip").val().length!=5 )
			{
				alert("Die Postleitzahlen in Deutschland müssen 5 Stellen haben.");
				return;
			}
			//check for order_ReceiverZip LITHUANIA
			if( $("#order_ReceiverOrigin").val()=="LT" && $("#order_ReceiverZip").val().length!=5 )
			{
				alert("Die Postleitzahlen in Litauen müssen 5 Stellen haben.");
				return;
			}
			//check for order_ReceiverZip NORWAY
			if( $("#order_ReceiverOrigin").val()=="NO" && $("#order_ReceiverZip").val().length!=4 )
			{
				alert("Die Postleitzahlen in Norwegen müssen 4 Stellen haben.");
				return;
			}
			//check for numeric zip code except where allowed 
			if( $("#order_ReceiverOrigin").val()=="AR" ) {} //Argentina
			else if( $("#order_ReceiverOrigin").val()=="GG" ) {} //Guernsey - Kanalinseln
			else if( $("#order_ReceiverOrigin").val()=="CA" ) {} //Canada
			else if( $("#order_ReceiverOrigin").val()=="NL" ) {} //Netherlands
			else if( $("#order_ReceiverOrigin").val()=="GB" ) {} //Great Britain
			else if( $("#order_ReceiverOrigin").val()=="UK" ) {} //United Kingdom
			else
			{
				if( !is_numeric($("#order_ReceiverZip").val()) )
				{
					alert("Die Postleitzahl in diesem Land muss numerisch sein.");
					return;
				}
			}
			//check for ReceiverCity number
			if( $("#order_ReceiverCity").val()=="" )
			{
				alert("Der Ort des Empfängers darf nicht leer sein.");
				return;
			}
			//check for ReceiverEmail field length
			if( $("#order_ReceiverEmail").val()!="" && ! isValidEmailAddress($("#order_ReceiverEmail").val()) )
			{
				alert("Das Feld Empfänger -> E-Mail enthält keine gültige E-Mail-Adresse.");
				return;
			}
			//check for comma in weight value
			$("#order_WeightInKG").val($("#order_WeightInKG").val().replace(",", "."));
			//check for correct weight values
			if( $("#order_WeightInKG").val()<=0 )
			{
				alert("Das Paketgewicht muss größer als 0 sein.");
				return;
			}
			//check for correct weight values
			if( $("#order_ReceiverOrigin").val()=="IT" && $("#order_WeightInKG").val()>=30 )
			{
				alert("Das Paketgewicht für Italien darf maximal 30kg betragen.");
				return;
			}
			//check for correct weight values
			if( $("#order_WeightInKG").val()>=31.5 )
			{
				alert("Das Paketgewicht darf maximal 31,5kg betragen.");
				return;
			}
			//check for international parcel but german product code
			if( $("#order_ProductCode").val()=="EPN" && $("#order_ReceiverOrigin").val()!="DE" )
			{
				$("#order_ProductCode").val("BPI");
			}

			//check for german parcel but international product code
			if( $("#order_ProductCode").val()=="BPI" && $("#order_ReceiverOrigin").val()=="DE" )
			{
				$("#order_ProductCode").val("EPN");
			}
			
			//get additional shipment items
			var $WeightInKG=$("#order_WeightInKG").val();
			var $LengthInCM=$("#order_LengthInCM").val();
			var $WidthInCM=$("#order_WidthInCM").val();
			var $HeightInCM=$("#order_HeightInCM").val();
			$("[name=order_WeightInKG]").each(function() { $WeightInKG += ";"+$(this).val(); });
			$("[name=order_LengthInCM]").each(function() { $LengthInCM += ";"+$(this).val(); });
			$("[name=order_WidthInCM]").each(function() { $WidthInCM += ";"+$(this).val(); });
			$("[name=order_HeightInCM]").each(function() { $HeightInCM += ";"+$(this).val(); });
			
			wait_dialog_show();
			wait_dialog_show("Erstelle Etikett bei DHL...");
			var $postdata=new Object();
			$postdata["API"]="dhl";
			$postdata["Action"]="CreateShipmentDD";
			$postdata["id_order"]=$("#order_id_order").val();
			$postdata["Customs"]=$("#order_customs").val();
			$postdata["CustomerReference"]=$("#order_CustomerReference").val();
			$postdata["ProductCode"]=$("#order_ProductCode").val();
			$postdata["WeightInKG"]=$WeightInKG;
			$postdata["LengthInCM"]=$LengthInCM;
			$postdata["WidthInCM"]=$WidthInCM;
			$postdata["HeightInCM"]=$HeightInCM;
			$postdata["ShipperEmail"]=$("#order_ShipperEmail").val();
			$postdata["ShipperPhone"]=$("#order_ShipperPhone").val();
			$postdata["ShipperCompany"]=$("#order_ShipperCompany").val();
			$postdata["ShipperCompany2"]=$("#order_ShipperCompany2").val();
			$postdata["ShipperZip"]=$("#order_ShipperZip").val();
			$postdata["ShipperCity"]=$("#order_ShipperCity").val();
			$postdata["ShipperStreetName"]=$("#order_ShipperStreetName").val();
			$postdata["ShipperStreetNumber"]=$("#order_ShipperStreetNumber").val();
			$postdata["ShipperOrigin"]=$("#order_ShipperOrigin").val();
			$postdata["ShipperContactPerson"]=$("#order_ShipperContactPerson").val();
			$postdata["ReceiverEmail"]=$("#order_ReceiverEmail").val();
			$postdata["ReceiverPhone"]=$("#order_ReceiverPhone").val();
			$postdata["ReceiverCompany"]=$("#order_ReceiverCompany").val();
			$postdata["ReceiverCompany2"]=$("#order_ReceiverCompany2").val();
			$postdata["ReceiverZip"]=$("#order_ReceiverZip").val();
			$postdata["ReceiverCity"]=$("#order_ReceiverCity").val();
			$postdata["ReceiverStreetName"]=$("#order_ReceiverStreetName").val();
			$postdata["ReceiverStreetNumber"]=$("#order_ReceiverStreetNumber").val();
			$postdata["ReceiverOrigin"]=$("#order_ReceiverOrigin").val();
			$postdata["ReceiverContactPerson"]=$("#order_ReceiverContactPerson").val();
			$postdata["CODAmount"]=$("#order_CODAmount").val();
			$postdata["CostCenter"]=$("#order_CostCenter").val();
			$.post("<?php echo PATH; ?>soa/", $postdata, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ( $ack!="Success" )
					{
						if( $data.indexOf("incoterms") > -1 )
						{
							if( $("#order_id_order").val()!="" )
							{
								wait_dialog_show("Zollsendung erkannt. Generiere Unterlagen...");
								$("#order_customs").val(1);
								label_create();
								return;
							}
							else
							{
								alert("Diese Sendung erfordert Zollpapiere, die jedoch nicht hinterlegt sind.\n\nBitte erstellen Sie das Etikett in Intraship!");
								return;
							}
						}
						
						$postdata=new Object();
						$postdata["API"]="cms";
						$postdata["APIRequest"]="ErrorAdd";
						$postdata["id_errortype"]=10;
						$postdata["id_errorcode"]=9879;
						$postdata["file"]="backend_dhl_shipments.php";
						$postdata["line"]="333";
						$postdata["text"]=$data;
						$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
						{
						});
						show_status2($data);
						wait_dialog_hide();
						return;
					}
				}
				catch (err)
				{
					$postdata=new Object();
					$postdata["API"]="cms";
					$postdata["APIRequest"]="ErrorAdd";
					$postdata["id_errortype"]=10;
					$postdata["id_errorcode"]=9879;
					$postdata["file"]="backend_dhl_shipments.php";
					$postdata["line"]="333";
					$postdata["text"]=$data;
					$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
					{
					});
					show_status2(err.message);
					wait_dialog_hide();
					return;
				}
				wait_dialog_show("Etikett erstellt. Schreibe Daten und starte Druckvorgang...");
				$("#order_ShipmentNumber").val($xml.find("ShipmentNumber").text());
				var $LabelPath=$xml.find("LabelPath").text();
				$("#order_LabelURLLocal").val($LabelPath);
				$("#order_LabelURLLocalLink").attr("href", $LabelPath);
				wait_dialog_hide();
				print_label();
			});
		}		
		
		
		function label_get()
		{
			wait_dialog_show();
			wait_dialog_show("Lese Etikett bei DHL...");
			$.post("<?php echo PATH; ?>soa/", { API:"dhl", Action:"GetLabelDD", id_order:$("#order_id_order").val(), ShipmentNumber:$("#order_ShipmentNumber").val(), WeightInKG:$("#order_WeightInKG").val() },
				function($data)
				{
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack").text();
						if ( $ack!="Success" )
						{
							show_status2($data);
							alert("Etikett konnte nicht (mehr) gefunden werden.");
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
					wait_dialog_show("Etikett ausgelesen. Schreibe Daten und starte Druckvorgang...");
					var $LabelPath=$xml.find("LabelPath").text();
					$("#order_LabelURLLocal").val($LabelPath);
					$("#order_LabelURLLocalLink").attr("href", $LabelPath);
					wait_dialog_hide();
					print_label();
				}
			);
		}
		
		function order_shipment_add()
		{
			//check for packages allowed (Germany only!)
			if( $("#order_ProductCode").val()!="EPN" )
			{
				alert("Packstücke sind nur bei nationalen Standard-Sendungen erlaubt.\n\nBitte erstellen Sie mehrere Etiketten für diese Sendung!");
				return;
			}
			//check for max count of packages
			var $i=0;
			$("[name=order_WeightInKG]").each(function() { $i++; });
			if($i>=10)
			{
				alert("Es sind maximal 11 Packstücke erlaubt!");
				return;
			}

			$("#order_HeightInCM").focus();
			$("#shipment_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { order_shipment_item_add(); } },
					{ text: "Schließen", click: function() { shipment_add_close(); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Mehrere Packstücke eintragen",
				width:700
			});
			$("#package_count").focus();
		}
		
		function order_shipment_item_add()
		{
			if($("#package_count").val()>11) 
			{
				alert("Es sind maximal 11 Packstücke erlaubt!");
				return;
			}			
			var $package_count=$("#package_count").val();
			var $single_package_weight=Math.round($("#package_weight").val()/$package_count*100)/100;

			$("#order_WeightInKG").val($single_package_weight);
			for($i=0; $i<$package_count-1; $i++)
			{
				$item  = '<tr name="shipment_item">';
				$item += '	<td>Länge in cm<br/><input name="order_LengthInCM" type="text" value="60" /></td>';
				$item += '	<td>Breite in cm<br/><input name="order_WidthInCM" type="text" value="40" /></td>';
				$item += '	<td>Höhe in cm<br/><input name="order_HeightInCM" type="text" value="30" /></td>';
				$item += '	<td>Paketgewicht in kg<br/><input name="order_WeightInKG" style="color:#ff0000;" type="text" value="' + $single_package_weight +'" onkeyup="print_onEnter();" /></td>';
				$item += '</tr>';
				$("#order_table").append($item);
			}
			shipment_add_close();
		}

		function shipment_add_close()
		{
			$("#package_count").val("");
			$("#package_weight").val("");
			$("#shipment_add_dialog").dialog("close");
			$("#order_WeightInKG").focus();
			$("#order_WeightInKG").select();
			view2();
		}


		function print_label()
		{
			if( $("#order_ShipmentNumber").val()=="" )
			{
				label_create();
				return;
			}

			if( $("#order_ShipmentNumber").val()!="" && $("#order_LabelURLLocal").val()=="" )
			{
				label_get();
				return;
			}

			//wait until pdf is loaded
			wait_dialog_hide();
			if( $("#order_ProductCode").val()=="EXP" )
			{
				alert("ACHTUNG: Es handelt sich um ein DHL-EXPRESS-Paket. Bitte in das DHL-EXPRESS-Regal legen!");
			}
			if( $("#order_customs").val()==1 && $("#order_ReceiverOrigin").val()=="CN" )
			{
				alert("ACHTUNG: Sendung nach China! 2x Zollunterlagen und 2x Rechnungskopie in einem Sichtschutzumschlag auf das Paket kleben!");
				var $printer=$("#order_printer").val();
				if ( $printer!="Druckdialog" ) print_export_documents();
			}
			else if( $("#order_customs").val()==1 )
			{
				alert("ACHTUNG: Sendung ist zollpflichtig. Zollunterlagen bitte aus dem Laserdrucker entnehmen und zusammen mit drei Rechnungskopien in einem Sichtschutzumschlag auf das Paket kleben!");
				var $printer=$("#order_printer").val();
				if ( $printer!="Druckdialog" ) print_export_documents();
			}
			
			//print label without dialog
			var $printer=$("#order_printer").val();
			if ( $printer!="Druckdialog" )
			{
				wait_dialog_show("Drucke Etikett...");
				$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:$printer, file:$("#order_LabelURLLocal").val() }, function($data)
				{
					if( $("#order_customs").val()==1 )
					{
						$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"HP LaserJet 600 Tagesabschluss", file:$("#order_LabelURLLocal").val() }, function($data)
						{
							wait_dialog_hide();
							order_close();
							return;
						});
					}
					else
					{
						wait_dialog_hide();
						order_close();
						return;
					}
				});
			}
			//print label with dialog
			else
			{
				wait_dialog_show("Druckdialog wird geladen");
				$("#print_pdf").attr("src", "<?php echo PATH ?>"+$("#order_LabelURLLocal").val());
				order_close();
			}
		}


		function print_export_documents()
		{
			wait_dialog_show("Exportdokumente werden abgerufen...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"GetExportDocDD", id_order:$("#order_id_order").val() }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}

				var $printer=$("#order_printer").val();
				if ( $printer!="Druckdialog" )
				{
					wait_dialog_show("Schicke Auftrag an Drucker...");
					$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"HP LaserJet 600 Tagesabschluss", file:$xml.find("LabelPath").text() }, function($data)
					{
						try
						{
							$xml = $($.parseXML($data));
							$ack = $xml.find("Ack");
							if ( $ack.text()!="Success" )
							{
								alert("call error: "+$data);
								return;
							}
						}
						catch (err)
						{
							alert(err.message+'<br />'+$data);
							return;
						}
						
						wait_dialog_hide();
						alert("Bitte entnehmen Sie die Exportdokumente aus dem Rechnungsdrucker.");
					});
				}
				else
				{
					wait_dialog_show("Druckdialog wird geladen");
					$("#print_pdf").attr("src", $xml.find("LabelPath").text());
					order_close();
				}
			});
		}


		function print_export_documents_dialog2()
		{
			wait_dialog_hide();
			$(this).show();
			document.getElementById('print_pdf').focus();
			document.getElementById('print_pdf').contentWindow.print();
			return false;
		}


		function close_onEnter(e)
		{
			if(!e) var e = event || window.event;
			if ((e.keyCode) == 13)  order_shipment_item_add();
		}

		function jump_onEnter(e)
		{
			if(!e) var e = event || window.event;
			if ((e.keyCode) == 13) 
			{
				if($("#package_count").val()>11) alert("Es sind maximal 11 Packstücke erlaubt!");
				else $("#package_weight").focus();
			}
		}

		function print_onEnter(e)
		{
			if(!e) var e = event || window.event;
			if ((e.keyCode) == 13)  
			{
				if($("#order_WeightInKG").val()=="") order_shipment_add();
				else
				{
					print_label();
				}
			}
		}
	
		function show_location_label($ReceiverCompany, $ReceiverCompany2, $ReceiverStreetName, $ReceiverStreetNumber, $ReceiverZip, $ReceiverCity, $ReceiverContactPerson, $ReceiverOrigin, $ReceiverPhone, $ReceiverEmail, $CostCenter)
		{
			$("[name=shipment_item]").remove();
			wait_dialog_show("Versenderdaten werden abgerufen...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"GetShipperAddress" }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						alert("call error: "+$data);
						return;
					}
				}
				catch (err)
				{
					alert(err.message+'<br />'+$data);
					return;
				}
				
				$("#order_ShipperCompany").val($xml.find("ShipperCompany").text());
				$("#order_ShipperCompany2").val($xml.find("ShipperCompany2").text());
				$("#order_ShipperStreetName").val($xml.find("ShipperStreetName").text());
				$("#order_ShipperStreetNumber").val($xml.find("ShipperStreetNumber").text());
				$("#order_ShipperZip").val($xml.find("ShipperZip").text());
				$("#order_ShipperCity").val($xml.find("ShipperCity").text());
				$("#order_ShipperContactPerson").val($xml.find("ShipperContactPerson").text());
				$("#order_ShipperOrigin").val($xml.find("ShipperOrigin").text());
				$("#order_ShipperPhone").val($xml.find("ShipperPhone").text());
				$("#order_ShipperEmail").val($xml.find("ShipperEmail").text());
	
				$("#order_ReceiverCompany").val($ReceiverCompany);
				$("#order_ReceiverCompany2").val($ReceiverCompany2);
				$("#order_ReceiverStreetName").val($ReceiverStreetName);
				$("#order_ReceiverStreetNumber").val($ReceiverStreetNumber);
				$("#order_ReceiverZip").val($ReceiverZip);
				$("#order_ReceiverCity").val($ReceiverCity);
				$("#order_ReceiverContactPerson").val($ReceiverContactPerson);
				$("#order_ReceiverOrigin").val($ReceiverOrigin);
				$("#order_ReceiverPhone").val($ReceiverPhone);
				$("#order_ReceiverEmail").val($ReceiverEmail);
	
				var $ProductCode="EPN";
				if( $ReceiverOrigin!="DE" ) $ProductCode="EPI";
				$("#order_ProductCode").val($ProductCode);
				$("#order_ShipmentNumber").val("");
				$("#order_LabelURLLocalLink").hide();
				$("#order_CustomsLabelURLLocalLink").hide();
				$("#order_customs").val(0);
				$("#order_CustomerReference").val("Standortetikett");
				$("#order_CODAmount").val(0);
				if($CostCenter=="") $CostCenter=1000;
				$("#order_CostCenter").val($CostCenter);

				$("#order_LengthInCM").val(60);
				$("#order_WidthInCM").val(40);
				$("#order_HeightInCM").val(30);
				$("#order_WeightInKG").val("");
				$("#order_id_order").val("");
	
				if( <?php echo $_SESSION["id_user"]; ?> == 30785 ) $("#order_printer").val("HP LaserJet 600 Paketscheine");
				var $buttons=new Object();
				$buttons[0]={ text: "Etikett drucken", click: function() { print_label(); } };
				if( $("#order_CODAmount").val()>0 ) {}
				else
				{
					$buttons[1]={ text: "Packstück hinzufügen", click: function() { order_shipment_add(); } };
				}
				$buttons[2]={ text: "Exportdokumente drucken", click: function() { print_export_documents(); } };
				$buttons[3]={ text: "Schließen", click: function() { order_close(); } };
				$("#order_dialog").dialog
				({	buttons:$buttons,
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Bestellung überprüfen und Etikett drucken",
					width:700
				});

				$( "#order_ReceiverCompany" ).bind( "keyup", function() { textlength('order_ReceiverCompany', 'order_ReceiverCompany_length', 30); } );
				textlength('order_ReceiverCompany', 'order_ReceiverCompany_length', 30);
				$( "#order_ReceiverCompany2" ).bind( "keyup", function() { textlength('order_ReceiverCompany2', 'order_ReceiverCompany2_length', 30); } );
				textlength('order_ReceiverCompany2', 'order_ReceiverCompany2_length', 30);
				$( "#order_ReceiverContactPerson" ).bind( "keyup", function() { textlength('order_ReceiverContactPerson', 'order_ReceiverContactPerson_length', 30); } );
				textlength('order_ReceiverContactPerson', 'order_ReceiverContactPerson_length', 30);
	
				$("#order_WeightInKG").focus();
				$("#order_WeightInKG").select();

				wait_dialog_hide();
			});
		}


		function show_order($id_order)
		{
			$("[name=shipment_item]").remove();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"OrderGet", id_order:$id_order },
				function($data)
				{
					wait_dialog_show();
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							alert("call error: "+$data);
							return;
						}
					}
					catch (err)
					{
						alert(err.message+'<br />'+$data);
						return;
					}

					//shop_id
					//1 = MAPCO-Shop
					//2 = AP-Shop
					//3 = eBay mapco-eu
					//4 = eBay ihr-autopartner
					$shop_id=$xml.find("shop_id").text();
					
					$("#order_id_order").val($id_order);
					
					//shipping label
					$id_file=$xml.find("shipping_label_file_id").text();
					if( $id_file==0 )
					{
						var $LabelURLLocal="";
						var $LabelURLLocalLink="";
					}
					else
					{
						$folder=Math.floor($id_file/1000);
						var $LabelURLLocal="files/"+$folder+"/"+$id_file+".pdf";
						var $LabelURLLocalLink="<?php echo PATH; ?>files/"+$folder+"/"+$id_file+".pdf";
					}
					if( $LabelURLLocal=="" ) $("#order_LabelURLLocalLink").hide();
					else $("#order_LabelURLLocalLink").show();
					$("#order_LabelURLLocal").val($LabelURLLocal);
					$("#order_LabelURLLocalLink").attr("href", $LabelURLLocalLink);
					
					//customs label
					$customs_id_file=$xml.find("shipping_customs_file_id").text();
					if( $customs_id_file==0 ) var $CustomsLabelURLLocalLink=""
					else
					{
						var $folder=Math.floor($customs_id_file/1000);
						var $CustomsLabelURLLocalLink="<?php echo PATH; ?>files/"+$folder+"/"+$customs_id_file+".pdf";
					}
					if( $CustomsLabelURLLocalLink=="" ) $("#order_CustomsLabelURLLocalLink").hide();
					else $("#order_CustomsLabelURLLocalLink").show();
					$("#order_CustomsLabelURLLocalLink").attr("href", $CustomsLabelURLLocalLink);

					$("#order_ShipmentNumber").val($xml.find("shipping_number").text());
					$("#order_WeightInKG").val($xml.find("shipping_WeightInKG").text());
					$("#order_LengthInCM").val($xml.find("shipping_LengthInCM").text());
					$("#order_WidthInCM").val($xml.find("shipping_WidthInCM").text());
					$("#order_HeightInCM").val($xml.find("shipping_HeightInCM").text());
					var $CODAmount=0;
					if( $xml.find("payments_type_id").text() == 3 )
					{
						$xml.find("OrderItem").each(function()
						{
							$CODAmount += Number($(this).find("amount").text()) * Math.round(Number($(this).find("netto").text())*119)/100;
						});
						$CODAmount += Math.round(Number($xml.find("shipping_net").text())*119)/100;
					}
					$CODAmount=Math.round($CODAmount*100)/100;
					$("#order_CODAmount").val($CODAmount);
					//costcenter
					if( $xml.find("shop_id").text()==1  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==2  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==3  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==4  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==5  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==6  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==7  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==8  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==9  ) $CostCenter=4215;
					else if( $xml.find("shop_id").text()==10  ) $CostCenter=4216;
					else if( $xml.find("shop_id").text()==11  ) $CostCenter=4217;
					else if( $xml.find("shop_id").text()==12  ) $CostCenter=4218;
					else if( $xml.find("shop_id").text()==13  ) $CostCenter=4219;
					else if( $xml.find("shop_id").text()==14  ) $CostCenter=4220;
					else if( $xml.find("shop_id").text()==15  ) $CostCenter=4221;
					else if( $xml.find("shop_id").text()==16  ) $CostCenter=4222;
					else if( $xml.find("shop_id").text()==17  ) $CostCenter=2000;
					else if( $xml.find("shop_id").text()==18  ) $CostCenter=1000;
					else if( $xml.find("shop_id").text()==22  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==23  ) $CostCenter=4399;
					else if( $xml.find("shop_id").text()==24  ) $CostCenter=4399;
					else $CostCenter=1000;
					$("#order_CostCenter").val($CostCenter);
					//CustomerReference
					var $CustomerReference=$id_order;
					var $ordernr=$xml.find("ordernr").text();
					if( $ordernr!="" ) $CustomerReference += ", "+$ordernr;
					$("#order_CustomerReference").val($CustomerReference);

					wait_dialog_show("Versenderdaten werden abgerufen...");
					$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"GetShipperAddress" }, function($data)
					{
						try
						{
							$xml2 = $($.parseXML($data));
							$ack2 = $xml2.find("Ack");
							if ( $ack2.text()!="Success" )
							{
								alert("call error: "+$data);
								return;
							}
						}
						catch (err)
						{
							alert(err.message+'<br />'+$data);
							return;
						}

						if( $shop_id==2 || $shop_id==4 || $shop_id==6 )
						{
							$("#order_ShipperEmail").val("info@ihr-autopartner.de");
							$("#order_ShipperPhone").val("+4933844758280");
							$("#order_ShipperCompany").val("Autopartner GmbH");
							$("#order_ShipperCompany2").val("Andre Mischke");
							$("#order_ShipperZip").val("14822");
							$("#order_ShipperCity").val("Brück");
							$("#order_ShipperStreetName").val("Gregor-von-Brück Ring");
							$("#order_ShipperStreetNumber").val("1");
							$("#order_ShipperOrigin").val("DE");
							$("#order_ShipperContactPerson").val("Andre Mischke");
						}
						else if( $shop_id==5 || $shop_id==22 )
						{
							$("#order_ShipperEmail").val("kfroehlich@mapco.de");
							$("#order_ShipperPhone").val("+4933844758228");
							$("#order_ShipperCompany").val("MAPCO Autotechnik GmbH");
							$("#order_ShipperCompany2").val("Kai Froehlich");
							$("#order_ShipperZip").val("14822");
							$("#order_ShipperCity").val("Brück");
							$("#order_ShipperStreetName").val("Gregor-von-Brück Ring");
							$("#order_ShipperStreetNumber").val("1");
							$("#order_ShipperOrigin").val("DE");
							$("#order_ShipperContactPerson").val("Kai Froehlich");
						}
						else if( $shop_id==1 || $shop_id==3 || $shop_id==8 )
						{
							$("#order_ShipperEmail").val("info@mapco.de");
							$("#order_ShipperPhone").val("+493384475820");
							$("#order_ShipperCompany").val("MAPCO Autotechnik GmbH");
							$("#order_ShipperCompany2").val("Tobias Buls");
							$("#order_ShipperZip").val("14822");
							$("#order_ShipperCity").val("Borkheide");
							$("#order_ShipperStreetName").val("Moosweg");
							$("#order_ShipperStreetNumber").val("1");
							$("#order_ShipperOrigin").val("DE");
							$("#order_ShipperContactPerson").val("Tobias Buls");
						}
						else
						{
							$("#order_ShipperCompany").val($xml2.find("ShipperCompany").text());
							$("#order_ShipperCompany2").val($xml2.find("ShipperCompany2").text());
							$("#order_ShipperStreetName").val($xml2.find("ShipperStreetName").text());
							$("#order_ShipperStreetNumber").val($xml2.find("ShipperStreetNumber").text());
							$("#order_ShipperZip").val($xml2.find("ShipperZip").text());
							$("#order_ShipperCity").val($xml2.find("ShipperCity").text());
							$("#order_ShipperContactPerson").val($xml2.find("ShipperContactPerson").text());
							$("#order_ShipperOrigin").val($xml2.find("ShipperOrigin").text());
							$("#order_ShipperPhone").val($xml2.find("ShipperPhone").text());
							$("#order_ShipperEmail").val($xml2.find("ShipperEmail").text());
						}
						
						$("#order_ReceiverEmail").val($xml.find("usermail").text());
						$("#order_ReceiverPhone").val($xml.find("userphone").text());

						$bill_adr_id=$xml.find("bill_adr_id").text();
						$ship_adr_id=$xml.find("ship_adr_id").text();
						if( $ship_adr_id>0 && $ship_adr_id!=$bill_adr_id )
						{
							$("#bill_address").html("");
							$bill_address="";
							//if bill and shipping address differ show bill address too
							if( $xml.find("bill_company").text()!="" ) $bill_address += $xml.find("bill_company").text();
							if( $bill_address!="" ) $bill_address += ", ";
							if( $xml.find("bill_firstname").text()!="" ) $bill_address += $xml.find("bill_firstname").text();
							if( $bill_address!="" ) $bill_address += " ";
							if( $xml.find("bill_lastname").text()!="" )	$bill_address += $xml.find("bill_lastname").text();
							if( $bill_address!="" ) $bill_address += ", ";
							if( $xml.find("bill_street").text()!="" ) $bill_address += $xml.find("bill_street").text();
							if( $bill_address!="" ) $bill_address += " ";
							if( $xml.find("bill_number").text()!="" ) $bill_address += $xml.find("bill_number").text();
							if( $bill_address!="" ) $bill_address += ", ";
							if( $xml.find("bill_zip").text()!="" ) $bill_address += $xml.find("bill_zip").text();
							if( $bill_address!="" ) $bill_address += " ";
							if( $xml.find("bill_city").text()!="" ) $bill_address += $xml.find("bill_city").text();
							if( $xml.find("bill_country").text()!="" )
							{
								if( $bill_address!="" ) $bill_address += ", ";
								$bill_address += $xml.find("bill_country").text();
							}
							if( $bill_address!="" )
							{
								$bill_address = "Abweichende Rechnungsanschrift: "+$bill_address;
								$("#bill_address").html($bill_address);
							}
							
							//fill form
							$("#order_ReceiverCompany2").val("");
							$("#order_ReceiverCompany").val( $.trim( $xml.find("ship_company").text() ) );
							if( $("#order_ReceiverCompany").val() == "" )
							{
								$("#order_ReceiverCompany").val( $.trim( $.trim($xml.find("ship_firstname").text())+" "+$.trim($xml.find("ship_lastname").text()) ) ) 
							}

							$("#order_ReceiverStreetName").val($xml.find("ship_street").text());
							//remove whitespace from the beginning and end of the street name
							$("#order_ReceiverStreetName").val($.trim($("#order_ReceiverStreetName").val()));
							//check for wrong "Packstation" spelling
							$("#order_ReceiverStreetName").val($("#order_ReceiverStreetName").val().replace("Packastion", "Packstation"));
							$("#order_ReceiverStreetName").val($("#order_ReceiverStreetName").val().replace("packstation", "Packstation"));
							//add Packstation customer number
							if( $xml.find("ship_additional").text() )
							{
								if ( $("#order_ReceiverCompany2").val()!="" )
								{
									$("#order_ReceiverCompany").val($("#order_ReceiverCompany").val() +" "+ $("#order_ReceiverCompany2").val());
								}
								$("#order_ReceiverCompany2").val($xml.find("ship_additional").text());
							}
							var $ReceiverZip=$xml.find("ship_zip").text();
							//remove non numeric characters from zip code except where allowed 
							if( $xml.find("ship_country_code").text()=="AR" ) {} //Argentina
							else if( $xml.find("ship_country_code").text()=="GG" ) {} //Guernsey - Kanalinseln
							else if( $xml.find("ship_country_code").text()=="CA" ) {} //Canada
							else if( $xml.find("ship_country_code").text()=="NL" ) {} //Netherlands
							else if( $xml.find("ship_country_code").text()=="GB" ) {} //Great Britain
							else if( $xml.find("ship_country_code").text()=="UK" ) {} //United Kingdom
							else
							{
								$ReceiverZip=$ReceiverZip.replace(/\D/g,'');
							}
							$("#order_ReceiverZip").val($ReceiverZip.trim());
							$("#order_ReceiverCity").val($xml.find("ship_city").text());
							$("#order_ReceiverStreetNumber").val($xml.find("ship_number").text());
							$("#order_ReceiverContactPerson").val( $.trim( $.trim($xml.find("ship_firstname").text())+" "+$.trim($xml.find("ship_lastname").text()) ) ) 
							if( $("#order_ReceiverContactPerson").val() == "" ) $("#order_ReceiverContactPerson").val( $("#order_ReceiverCompany").val() );
							$("#order_ReceiverOrigin").val($xml.find("ship_country_code").text());
						}
						else
						{
							$("#bill_address").html("");

							$("#order_ReceiverCompany2").val("");
							$("#order_ReceiverCompany").val( $.trim( $xml.find("bill_company").text() ) );
							if( $("#order_ReceiverCompany").val() == "" )
							{
								$("#order_ReceiverCompany").val( $.trim( $.trim($xml.find("bill_firstname").text())+" "+$.trim($xml.find("bill_lastname").text()) ) ) 
							}

							$("#order_ReceiverStreetName").val($xml.find("bill_street").text());
							//remove whitespace from the beginning and end of the street name
							$("#order_ReceiverStreetName").val($.trim($("#order_ReceiverStreetName").val()));
							//check for wrong "Packstation" spelling
							$("#order_ReceiverStreetName").val($("#order_ReceiverStreetName").val().replace("Packastion", "Packstation"));
							$("#order_ReceiverStreetName").val($("#order_ReceiverStreetName").val().replace("packstation", "Packstation"));
							//add Packstation customer number
							if( $xml.find("bill_additional").text() )
							{
								if ( $("#order_ReceiverCompany2").val()!="" )
								{
									$("#order_ReceiverCompany").val($("#order_ReceiverCompany").val() +" "+ $("#order_ReceiverCompany2").val());
								}
								$("#order_ReceiverCompany2").val($xml.find("bill_additional").text());
							}
							$("#order_ReceiverCompany2").val($xml.find("bill_additional").text());
							var $ReceiverZip=$xml.find("bill_zip").text();
							//remove non numeric characters from zip code except where allowed 
							if( $xml.find("bill_country_code").text()=="AR" ) {} //Argentina
							else if( $xml.find("bill_country_code").text()=="GG" ) {} //Guernsey - Kanalinseln
							else if( $xml.find("bill_country_code").text()=="CA" ) {} //Canada
							else if( $xml.find("bill_country_code").text()=="NL" ) {} //Netherlands
							else if( $xml.find("bill_country_code").text()=="GB" ) {} //Great Britain
							else if( $xml.find("bill_country_code").text()=="UK" ) {} //United Kingdom
							else
							{
								$ReceiverZip=$ReceiverZip.replace(/\D/g,'');
							}
							$("#order_ReceiverZip").val($ReceiverZip.trim());
							$("#order_ReceiverCity").val($xml.find("bill_city").text());
							$("#order_ReceiverStreetNumber").val($xml.find("bill_number").text());
							$("#order_ReceiverContactPerson").val( $.trim( $.trim($xml.find("bill_firstname").text())+" "+$.trim($xml.find("bill_lastname").text()) ) ) 
							if( $("#order_ReceiverContactPerson").val() == "" ) $("#order_ReceiverContactPerson").val( $("#order_ReceiverCompany").val() );
							$("#order_ReceiverOrigin").val($xml.find("bill_country_code").text());
						}
	
						//check for customs setting
						if($xml.find("shipping_customs_file_id").text()!=0) $("#order_customs").val(1);
						else $("#order_customs").val(0);
	
						//determine shipping type
						$shipping_type_id = $xml.find("shipping_type_id").text();
						if( $shipping_type_id==2 ) $("#order_ProductCode").val('EXP');
						else if( $shipping_type_id==19 ) $("#order_ProductCode").val('EPI');
						else if( ($shipping_type_id==5 || $shipping_type_id==16) && $("#order_ReceiverOrigin").val()!="DE" )
						{
							$("#order_ProductCode").val('BPI');
						}
						else $("#order_ProductCode").val('EPN');
						//set default package dimensions
						if( $("#order_WeightInKG").val()=="" ) $("#order_WeightInKG").val('');
						if( $("#order_LengthInCM").val()=="" ) $("#order_LengthInCM").val('60');
						if( $("#order_WidthInCM").val()=="" ) $("#order_WidthInCM").val('40');
						if( $("#order_HeightInCM").val()=="" ) $("#order_HeightInCM").val('30');
	
						if( <?php echo $_SESSION["id_user"]; ?> == 30785 ) $("#order_printer").val("HP LaserJet 600 Paketscheine");
						var $buttons=new Object();
						$buttons[0]={ text: "Etikett drucken", click: function() { print_label(); } };
						if( $("#order_CODAmount").val()>0 ) {}
						else
						{
							$buttons[1]={ text: "Packstück hinzufügen", click: function() { order_shipment_add(); } };
						}
						$buttons[2]={ text: "Exportdokumente drucken", click: function() { print_export_documents(); } };
						$buttons[3]={ text: "Schließen", click: function() { order_close(); } };
						//replace buttons with direct print if Lager Borkheide
						$("#order_dialog").dialog
						({	buttons:$buttons,
							closeText:"Fenster schließen",
							modal:true,
							resizable:false,
							title:"Bestellung überprüfen und Etikett drucken",
							width:700
						});
	
						$( "#order_ReceiverCompany" ).bind( "keyup", function() { textlength('order_ReceiverCompany', 'order_ReceiverCompany_length', 30); } );
						textlength('order_ReceiverCompany', 'order_ReceiverCompany_length', 30);
						$( "#order_ReceiverCompany2" ).bind( "keyup", function() { textlength('order_ReceiverCompany2', 'order_ReceiverCompany2_length', 30); } );
						textlength('order_ReceiverCompany2', 'order_ReceiverCompany2_length', 30);
						$( "#order_ReceiverContactPerson" ).bind( "keyup", function() { textlength('order_ReceiverContactPerson', 'order_ReceiverContactPerson_length', 30); } );
						textlength('order_ReceiverContactPerson', 'order_ReceiverContactPerson_length', 30);

						
						$("#order_WeightInKG").focus();
						$("#order_WeightInKG").select();
						wait_dialog_hide();
					});
					wait_dialog_hide();
				}
			);
		}
		
		
		function order_close()
		{
			$("#needle_id_order").val("");
			$("#order_dialog").dialog("close");
			$("#needle").val("");
			$("#needle").focus();
			view2();
		}
		
		function get_user($OrderID)
		{
			wait_dialog_show("Rufe Kontaktdaten bei eBay ab...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"GetUserContactDetails", id_account:1, OrderID:$OrderID }, function($data)
			{
				alert($data);
			});
		}


		function get_order($OrderID)
		{
			wait_dialog_show("Rufe Bestellung bei eBay ab...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"GetOrder", id_account:1, OrderID:$OrderID }, function($data)
			{
				alert($data);
			});
		}


		function set_shipment_tracking_info($id_order)
		{
			wait_dialog_show("Schicke Sendungsnummer an eBay...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"CompleteSale", id_order:$id_order }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ( $ack!="Success" )
					{
						alert("call error: "+$data);
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
				alert("Sendungsnummer erfolgreich auf eBay gespeichert.");
			});
		}


		function set_all_shipment_tracking_infos()
		{
			wait_dialog_show("Übertrage Daten an eBay...");
			$.post("<?php echo PATH ?>soa/", { API:"jobs", Action:"CompleteSales" }, function($data)
			{
				wait_dialog_hide();
				alert($data);
			});
		}


		function printer_list()
		{
			wait_dialog_show("Schicke Auftrag an Drucker...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"HP LaserJet 600 Paketscheine", file:"images/quali_doku.pdf" }, function($data)
			{
				wait_dialog_hide();
				alert($data);
			});
		}


		function view(e)
		{
			$("#view").html('<img alt="Bitte warten..." src="<?php echo PATH ?>images/icons/loaderb64.gif" title="Bitte warten..." />');
			var $filter=$("#filter").val();
			var $needle=$("#needle").val();
			if( $needle!=$("#needle").val() ) return;
			$.post("<?php echo PATH ?>soa/", { API:"shop", Action:"OrdersView", filter:$filter, needle:$needle },
				function($data)
				{
					$("#view").html($data);
				}
			);
		}
		
		function orders_get($show)
		{
			wait_dialog_show("Lese Bestellungen neu ein...");
			var $filter=$("input[name='choice']:checked").val();
			var $orders_from=Math.round($("#orders_from").datepicker('getDate') / 1000);
			var $orders_to=Math.round($("#orders_to").datepicker('getDate') / 1000)+24*3600-1;
			var $shippingtypes='';
			$('input[name=shippingtype_id]:checked').each(function()
			{
				if( $shippingtypes=="" ) $shippingtypes=$(this).val();
				else $shippingtypes+=", "+$(this).val();
			});
			$.post("<?php echo PATH ?>soa/", { API:"shop", Action:"OrdersGet", filter:$filter, from:$orders_from, to:$orders_to, shippingtypes:$shippingtypes }, function($data)
			{
				try
				{
					$ordersxml = $($.parseXML($data));
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
				$i=0;
				$orders=new Array();
				$ordersxml.find("Order").each(function()
				{
					$orders[$i]=new Array();
					$(this).children().each(
						function()
						{
							$orders[$i][this.tagName]=$(this).text();
						}
					);
					$i++;
				});
				wait_dialog_hide();
				if ( $show ) view2();
			});
		}
		
		function view2()
		{
			wait_dialog_show("Zeichne Tabelle", 100);
			var $filter=$("input[name='choice']:checked").val();
			var $needle=$("#needle").val();

			$ordershtml  = '<table>';
			$ordershtml += '<tr>';
			$ordershtml += '	<th>Nr.</th>';
			$ordershtml += '	<th>Order-ID</th>';
			$ordershtml += '	<th>Auftrags-ID</th>';
			$ordershtml += '	<th>Datum</th>';
			$ordershtml += '	<th>Shop</th>';
			$ordershtml += '	<th>Empfänger</th>';
			$ordershtml += '	<th>Versandart</th>';
			$ordershtml += '	<th>Versandkosten</th>';
			$ordershtml += '	<th>Sendungsnummer</th>';
			if( <?php echo $_SESSION["userrole_id"]; ?> == 1 )
			{
				$ordershtml += '	<th>Optionen</th>';
			}
			$ordershtml += '</tr>';
			var $nr=0;
			for($i=0; $i<$orders.length; $i++)
			{
				var $id_order=$orders[$i]["id_order"];
				var $AUF_ID='A'+$orders[$i]["AUF_ID"];
				if( $orders[$i]["ship_adr_id"]>0 )
				{
					var $company=$orders[$i]["ship_company"];
					var $firstname=$orders[$i]["ship_firstname"];
					var $lastname=$orders[$i]["ship_lastname"];
					var $street=$orders[$i]["ship_street"];
					var $number=$orders[$i]["ship_number"];
					var $zip=$orders[$i]["ship_zip"];
					var $city=$orders[$i]["ship_city"];
					var $country=$orders[$i]["ship_country"];
				}
				else
				{
					var $company=$orders[$i]["bill_company"];
					var $firstname=$orders[$i]["bill_firstname"];
					var $lastname=$orders[$i]["bill_lastname"];
					var $street=$orders[$i]["bill_street"];
					var $number=$orders[$i]["bill_number"];
					var $zip=$orders[$i]["bill_zip"];
					var $city=$orders[$i]["bill_city"];
					var $country=$orders[$i]["bill_country"];
				}

				var $found=false;
				if( $needle!="" )
				{
					$needles=$needle.split(" ");
					//trim left and right
					for($j=0; $j<$needles.length; $j++)
					{
						$needles[$j].replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');
					}
					//look for needle
					var $founds=new Array();
					for($j=0; $j<$needles.length; $j++)
					{
						$founds[$j]=false;
						if( $id_order.indexOf($needles[$j]) > -1 ) { $founds[$j]=true; }
						if( $AUF_ID.indexOf($needles[$j]) > -1 ) { $founds[$j]=true; }
						else if( $company.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $firstname.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $lastname.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $street.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $number.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $zip.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $city.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						else if( $country.toLowerCase().indexOf($needles[$j].toLowerCase()) > -1 ) { $founds[$j]=true; }
						if ( !$founds[$j] )
						{
							$found=false;
							break;
						}
					}
					for($j=0; $j<$founds.length; $j++)
					{
						if( $founds[$j] )
						{
							$found=true;
						}
						else
						{
							$found=false;
							break;
						}
					}
				}
				else $found=true;
				
				if($found)
				{
					var $needle_id_order=$orders[$i]["id_order"];
					$ordershtml += '<tr>';
					//Nr.
					$nr++;
					$ordershtml += '	<td>'+$nr+'</td>';
					//id_order
					$ordershtml += '	<td>'+$id_order+'</td>';
					//AUF-ID
					$ordershtml += '	<td>'+$AUF_ID+'</td>';
					//Datum
					var date = new Date($orders[$i]["firstmod"]*1000);
					$ordershtml += '	<td>'+date.getDate()+'.'+(date.getMonth()+1)+'.'+date.getFullYear()+' '+date.getHours()+':'+date.getMinutes()+'</td>';
					//Shop
					if( $orders[$i]["shop_id"]==4 ) $shop='AUTOPARTNER'; else $shop='MAPCO';
					$ordershtml += '	<td>'+$shop+'</td>';
					//Empfänger
					$ordershtml += '	<td>';
					$ordershtml += '		<a href="javascript:show_order('+$orders[$i]["id_order"]+');">';
					$ordershtml += $company+'<br />';
					$ordershtml += $firstname+' '+$lastname+'<br />';
					$ordershtml += $street+' '+$number+'<br />';
					$ordershtml += $zip+' '+$city+'<br />';
					$ordershtml += $country+'<br />';
					$ordershtml += '		</a>';
					$ordershtml += '	</td>';
					//Versandart
					$ordershtml += '	<td>'+$orders[$i]["shipping_details"]+'</td>';
					//Versandkosten
					$ordershtml += '	<td>'+$orders[$i]["shipping_costs"]+' €</td>';
					//Sendungsnummer
					$ordershtml += '	<td>'+$orders[$i]["shipping_number"]+'</td>';
					//Optionen
					if( <?php echo $_SESSION["userrole_id"]; ?> == 1 )
					{
						$ordershtml += '	<td>';
						if( $orders[$i]["foreign_OrderID"]!="" )
						{
							$ordershtml += '		<img alt="Bestellung bei eBay abrufen" src="<?php echo PATH; ?>images/icons/24x24/down.png" style="cursor:pointer;" title="Kontaktdaten bei eBay abrufen" onclick="get_order(\''+$orders[$i]["foreign_OrderID"]+'\');" />';
							$ordershtml += '		<img alt="Bestellung bei eBay abrufen" src="<?php echo PATH; ?>images/icons/24x24/down.png" style="cursor:pointer;" title="Bestellung bei eBay abrufen" onclick="get_order(\''+$orders[$i]["foreign_OrderID"]+'\');" />';
						}
						$ordershtml += '		<img alt="Sendungsverfolgungsnummer an eBay schicken" src="<?php echo PATH; ?>images/icons/24x24/up.png" style="cursor:pointer;" title="Sendungsverfolgungsnummer an eBay schicken" onclick="set_shipment_tracking_info('+$orders[$i]["id_order"]+');" />';
						$ordershtml += '	</td>';
					}
					$ordershtml += '</tr>';
				}
			}
			$ordershtml += '</table>';
			if( $nr!=1 ) $needle_id_order="";
			$ordershtml += '<input id="needle_id_order" type="hidden" value="'+$needle_id_order+'" />';
			$("#view").html($ordershtml);
			wait_dialog_hide();
			$("#needle").focus();
		}
	</script>
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_dhl_index.php">DHL</a>';
	echo ' > Versand';
	echo '</p>';
	echo '<h1>';
	echo 'DHL-Versandetiketten drucken';
	echo '<img alt="Alle Sendungsnummern an eBay schicken" src="'.PATH.'images/icons/24x24/up.png" style="cursor:pointer;" title="Alle Sendungsnummern an eBay schicken" onclick="set_all_shipment_tracking_infos();" />';
	echo '<img alt="Etikett drucken" src="'.PATH.'images/icons/24x24/printer.png" style="cursor:pointer;" title="Etikett drucken" onclick="printer_list();" />';
	echo '</h1>';

	echo '<br style="clear:both;" />';
	
	echo '<table>';
	echo '	<tr>';
	echo '		<th>Bestellungen</th>';
	echo '		<td>';
	echo '			<input checked="checked" type="radio" name="choice" onclick="orders_get(true);" value="open" /> Offene';
	echo '			<input type="radio" name="choice" onclick="orders_get(true);" value="timeframe" /> Alle';
	echo '			<input type="radio" name="choice" onclick="orders_get(true);" value="unknown" /> Unbekannte';
	echo '			<input type="radio" name="choice" onclick="orders_get(true);" value="return" /> Retoure';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr id="timeframe">';
	echo '		<th>Zeitraum</th>';
	echo '		<td>';
	echo '			<input id="orders_from" type="text" value="'.date("D d.m.Y", time()-7*24*3600).'" />';
	echo '			<input id="orders_to" type="text" value="'.date("D d.m.Y", time()).'" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<th>Suchtext</th>';
	echo '		<td>';
	echo '			<input id="needle" style="font-size:25px;" type="text" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<th>Versandarten</th>';
	echo '		<td>';
	$results=q("SELECT * FROM shop_shipping_types;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $row["id_shippingtype"]==1 or $row["id_shippingtype"]==2 or $row["id_shippingtype"]==5 or $row["id_shippingtype"]==15 ) $checked=' checked="checked"'; else $checked='';
		echo '	<input'.$checked.' id="shipping'.$row["id_shippingtype"].'" name="shippingtype_id" onclick="orders_get(true);" type="checkbox" value="'.$row["id_shippingtype"].'" /> '.$row["title"];
	}
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<th>Standortetiketten</th>';
	echo '		<td>';
	$results=q("SELECT * FROM cms_contacts_locations;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<input onclick="show_location_label(\''.$row["company"].'\', \''.$row["title"].'\', \''.$row["street"].'\', \''.$row["streetnr"].'\', \''.$row["zipcode"].'\', \''.$row["city"].'\', \''.$row["firstname"].' '.$row["lastname"].'\', \''.$row["country_code"].'\', \''.$row["phone"].'\', \''.$row["mail"].'\', \''.$row["cost_center"].'\');" style="float:left;" type="button" value="'.$row["location"].'" />';
	}
	echo '<input onclick="show_location_label(\'\', \'\', \'\', \'\', \'\', \'\', \'\', \'DE\', \'\', \'\', \'1000\');" style="float:left;" type="button" value="Freitext" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input onclick="orders_get(true);" style="float:left;" type="button" value="Bestellungen neu abrufen" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	
	echo '<br style="clear:both;" />';
	echo '<br style="clear:both;" />';
	
	echo '<div id="view"></div>';
?>
	<script type="text/javascript">
	     $('#needle').on('keyup', function(evt)
			{
				var $needle=$("#needle").val();
				if( $needle=="" || $needle.length>2 ) view2();
				var $id_order=$("#needle_id_order").val();
				var e = evt || window.event;
				var code = e.keyCode || e.which;
				if( code == 13 )
				{
					if( $needle.substr(0, 1)=="*" )
					{
						wait_dialog_show();
						$.post("<?php echo PATH; ?>soa/", { API:"idims", Action:"AddressGet", "search":$needle },	function($data)
						{
							wait_dialog_hide();
							try { $xml = $($.parseXML($data)); } catch ($err) { alert($err.message); return; }
							$ack = $xml.find("Ack");
							if ( $ack.text()!="Success" ) { alert($data); return; }
							//company1 and company2
							var $company1=$xml.find("ANSCHR_1").text();
							var $company2=$xml.find("ANSCHR_2").text();
							if( $company1=="" && $company2!="" )
							{
								$company1=$company2;
								$company2="";
							}
							//street and streetnr
							var $street=$xml.find("STRASSE").text();
							var $streetnr=$street.substr($street.lastIndexOf(" ")+1, $street.length);
							var $street=$street.substr(0, $street.lastIndexOf(" "));
							var $CostCenter=1000;
							if( $xml.find("country_code").text()=="FR" ) $CostCenter=4239;
//							else if( $xml.find("country_code").text()!="DE" ) $CostCenter=2000;
							
							show_location_label($company1, $company2, $street, $streetnr, $xml.find("PLZ").text(), $xml.find("ORT").text(), $xml.find("ANSCHR_3").text(), $xml.find("country_code").text(), '', '', $CostCenter);
						});
					}
					else if ($id_order!="") show_order($id_order);
					//********************************neu*************************
					else
					{
						if($needle.length==8 && $needle.substring(0, 1)==="A" && !isNaN($needle.substring(1, 8)))
						{
							wait_dialog_show("Lade Bestellung");
							$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"OrderGet", auf_id: $needle.substring(1, 8) },
								function($data)
								{
									wait_dialog_hide();
									$xml = $($.parseXML($data));
									$ack = $xml.find("Ack");
									if ( $ack.text()=="Success" )
									{
										show_order($xml.find("id_order").text());
									}
									else
										alert("Zu dieser Auftragsnummer konnte keine Bestellung gefunden werden.");
								});
							//show_order($needle.substring(1, 8));
						}
						else if( $needle.length==7 && is_numeric($needle) )
						{
							wait_dialog_show("Lade Bestellung");
							$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"OrderGet", id_order: $needle },
								function($data)
								{
									wait_dialog_hide();
									$xml = $($.parseXML($data));
									$ack = $xml.find("Ack");
									if ( $ack.text()=="Success" )
									{
										show_order($xml.find("id_order").text());
									}
									else
										alert("Zu dieser OrderID konnte keine Bestellung gefunden werden.");
								});
							//show_order($needle.substring(1, 8));
						}
						else
							alert("Die Eingabe ist keine gültige Auftragsnummer");
					}
					//*************************************************************
				}
			});
		window.setInterval("orders_get();", 300000);
	 </script>
<?php


	//ORDER DIALOG
	echo '<style> input { font-weight:bold; } </style>';
	echo '<div id="order_dialog" style="display:none;">';
	echo '	<p id="bill_address" style="color:#ff0000; font-size:14px; font-weight:bold;"></p>';
	echo '	<table id="order_table">';
	echo '		<tr>';
	echo '			<th colspan="2">Absender</th>';
	echo '			<th colspan="2">Empfänger</th>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Firma<br/><input id="order_ShipperCompany" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">';
	echo '			Firma / Name (<span id="order_ReceiverCompany_length"></span>)<br/>';
	echo '			<input id="order_ReceiverCompany" style="width:300px;" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Firma 2<br/><input id="order_ShipperCompany2" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">';
	echo '			Firma 2 / Packstation (<span id="order_ReceiverCompany2_length"></span>)';
	echo '			<br/><input id="order_ReceiverCompany2" style="width:300px;" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Ansprechpartner<br/><input id="order_ShipperContactPerson" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">';
	echo '				Ansprechpartner (<span id="order_ReceiverContactPerson_length"></span>)';
	echo '				<br/><input id="order_ReceiverContactPerson" style="width:300px;" type="text" value="" />';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Straße<br /><input id="order_ShipperStreetName" type="text" value="" /></td>';
	echo '			<td>Hausnummer<br /><input id="order_ShipperStreetNumber" type="text" value="" /></td>';
	echo '			<td>Straße<br /><input id="order_ReceiverStreetName" type="text" value="" /></td>';
	echo '			<td>Hausnummer<br /><input id="order_ReceiverStreetNumber" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>PLZ<br /><input id="order_ShipperZip" type="text" value="" /></td>';
	echo '			<td>Ort<br /><input id="order_ShipperCity" type="text" value="" /></td>';
	echo '			<td>PLZ<br /><input id="order_ReceiverZip" type="text" value="" /></td>';
	echo '			<td>Ort<br /><input id="order_ReceiverCity" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Land<br/>';
	echo '				<select id="order_ShipperOrigin">';
	$results=q("SELECT * FROM cms_countries ORDER BY country;", $dbweb, __FILE__, __LINE__);
	while($cms_countries=mysqli_fetch_array($results))
	{
		echo '	<option value="'.$cms_countries["country_code"].'">'.$cms_countries["country"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '			<td colspan="2">Land<br/>';
	echo '				<select id="order_ReceiverOrigin">';
	$results=q("SELECT * FROM cms_countries ORDER BY country;", $dbweb, __FILE__, __LINE__);
	while($cms_countries=mysqli_fetch_array($results))
	{
		echo '	<option value="'.$cms_countries["country_code"].'">'.$cms_countries["country"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Telefon<br /><input id="order_ShipperPhone" type="text" value="" /></td>';
	echo '			<td>E-Mail<br /><input id="order_ShipperEmail" type="text" value="" /></td>';
	echo '			<td>Telefon<br /><input id="order_ReceiverPhone" type="text" value="" /></td>';
	echo '			<td>E-Mail<br /><input id="order_ReceiverEmail" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<th colspan="4">Sendungsdaten</th>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Versandart<br/>';
	echo '				<select id="order_ProductCode">';
	echo '					<option value="EPN">DHL Deutschland</option>';
	echo '					<option value="EPI">DHL Europaket</option>';
	echo '					<option value="BPI">DHL Weltpaket</option>';
	echo '					<option value="EXP">DHL Express</option>';
	echo '				</select>';
	echo '			</td>';
	echo '			<td>Sendungsnummer<br/><input id="order_ShipmentNumber" type="text" value="" /></td>';
	echo '			<td>';
	echo '				<a target="_blank" id="order_LabelURLLocalLink" href="">Etikett</a><br />';
	echo '				<input id="order_LabelURLLocal" type="hidden" value="" />';
	echo '				<a target="_blank" id="order_CustomsLabelURLLocalLink" href="">Zollpapiere</a>';
	echo '				<input id="order_LabelPath" type="hidden" value="" />';
	echo '			</td>';
	echo '			<td>Zoll<br/>';
	echo '				<select id="order_customs"><option value="0">Nein</option><option value="1">Ja</option></select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Sendungsreferenz<br/><input id="order_CustomerReference" type="text" value="" /></td>';
	echo '			<td>Nachnahmegebühr<br/><input id="order_CODAmount" style="width:50px;" type="text" value="" /> Euro</td>';
	echo '			<td>Kostenstelle<br/><input id="order_CostCenter" type="text" value="" disabled="disabled" /></td>';
	echo '			<td>';
	echo '				Drucker<br/>';
	echo '				<select id="order_printer">';
	echo '					<option value="Druckdialog">Druckdialog</option>';
	echo '					<option value="HP LaserJet 600 Paketscheine">Borkheide Paketscheine</option>';
	echo '					<option value="HP LaserJet 600 Tagesabschluss">Borkheide Tagesabschluss</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Länge in cm<br/><input id="order_LengthInCM" type="text" value="0" /></td>';
	echo '			<td>Breite in cm<br/><input id="order_WidthInCM" type="text" value="0" /></td>';
	echo '			<td>Höhe in cm<br/><input id="order_HeightInCM" type="text" value="0" /></td>';
	echo '			<td>Paketgewicht in kg<br/><input id="order_WeightInKG" style="color:#ff0000;" type="text" value="" onkeyup="print_onEnter();" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input id="order_id_order" type="hidden" value="" />';
	echo '</div>';

	//SHIPMENT_ADD DIALOG
	echo '<style> input { font-weight:bold; } </style>';
	echo '<div id="shipment_add_dialog" style="display:none;">';
	echo '	<table id="shipment_add_table">';
	echo '		<tr>';
	echo '			<th>Anzahl Packstücke</th>';
	echo '			<th>Gesamtgewicht</th>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td><input id="package_count" style="width:300px;" type="text" value="" onkeyup="jump_onEnter();" /></td>';
	echo '			<td><input id="package_weight" style="width:300px;" type="text" value="" onkeyup="close_onEnter();" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';

	//PRINT DIALOG
	echo '	<iframe id="print_pdf" name="printframe" src="" style="display:none" type="application/pdf">';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>