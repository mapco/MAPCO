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
				if( $(this).css("display")=="none" )
				{
					$(this).show();
					document.getElementById('print_pdf').focus();
					document.getElementById('print_pdf').contentWindow.print();
				}
			});
		});

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

		function label_create($dialog)
		{
			if( $("#order_WeightInKG").val()<=0 )
			{
				alert("Das Paketgewicht muss größer als 0 sein.");
				return;
			}
			if( $("#order_ReceiverStreetNumber").val()=="" )
			{
				alert("Die Hausnummer des Empfängers darf nicht leer sein.");
				return;
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
			show_status("Erstelle Etikett bei DHL...");
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
			$postdata["ReceiverCompanyFirstname"]=$("#order_ReceiverCompanyFirstname").val();
			$postdata["ReceiverCompanyLastname"]=$("#order_ReceiverCompanyLastname").val();
			$postdata["ReceiverZip"]=$("#order_ReceiverZip").val();
			$postdata["ReceiverCity"]=$("#order_ReceiverCity").val();
			$postdata["ReceiverStreetName"]=$("#order_ReceiverStreetName").val();
			$postdata["ReceiverStreetNumber"]=$("#order_ReceiverStreetNumber").val();
			$postdata["ReceiverOrigin"]=$("#order_ReceiverOrigin").val();
			$postdata["ReceiverContactPerson"]=$("#order_ReceiverContactPerson").val();
			$postdata["CODAmount"]=$("#order_CODAmount").val();
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
							show_status("Zollsendung erkannt. Generiere Unterlagen...");
							$("#order_customs").val(1);
							label_create();
							return;
						}
						
						//hier noch Fehler mit ErrorAdd speichern
						show_status2($data);
						return;
						var $status='Es ist mindestens ein Fehler aufgetreten.<br /><br />';
						$reponse = $xml.find("Response").text();
						$xml = $($.parseXML($reponse));
						$xml.find("StatusMessage").each(function()
						{
							$status += $(this).text()+'<br />';
						});
						show_status($status);
//							show_status2("call error: "+$data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				show_status("Etikett erstellt. Schreibe Daten und starte Druckvorgang...");
				$("#order_ShipmentNumber").val($xml.find("ShipmentNumber").text());
				$("#order_LabelURLLocal").val($xml.find("LabelPath").text());
				$("#order_LabelPath").val($xml.find("LabelPath").text());
				wait_dialog_hide();
				print_label_dialog();
			});
		}		
		
		
		function label_get($dialog)
		{
			wait_dialog_show();
			show_status("Lese Etikett bei DHL...");
			$.post("<?php echo PATH; ?>soa/", { API:"dhl", Action:"GetLabelDD", id_order:$("#order_id_order").val(), ShipmentNumber:$("#order_ShipmentNumber").val(), WeightInKG:$("#order_WeightInKG").val() },
				function($data)
				{
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack").text();
						if ( $ack!="Success" )
						{
							show_status("Etikett konnte nicht (mehr) gefunden werden.");
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
					show_status("Etikett ausgelesen. Schreibe Daten und starte Druckvorgang...");
					$("#order_LabelURLLocal").val($xml.find("LabelPath").text());
					$("#order_LabelPath").val($xml.find("LabelPath").text());
					wait_dialog_hide();
					print_label_dialog();
				}
			);
		}
		
		function order_shipment_add()
		{
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
			view2();
		}
		
		function print_label()
		{
			if( $("#order_ShipmentNumber").val()=="" )
			{
				label_create(false);
				return;
			}

			if( $("#order_ShipmentNumber").val()!="" && $("#order_LabelURLLocal").val()=="" )
			{
				label_get(false);
				return;
			}

			//wait until pdf is loaded
			hide_status();
			$("#print_pdf").hide();
			if( $("#order_ProductCode").val()=="EXP" )
			{
				alert("ACHTUNG: Es handelt sich um ein DHL-EXPRESS-Paket. Bitte in das DHL-EXPRESS-Regal legen!");
			}
			if( $("#order_customs").val()==1 )
			{
				alert("ACHTUNG: Sendung ist zollpflichtig. Zollunterlagen bitte aus dem Laserdrucker entnehmen und zusammen mit drei Rechnungskopien in einem Sichtschutzumschlag auf das Paket kleben!");
				print_export_documents();
			}
			show_status("Drucke Etikett...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"Brother HL-6180DW DHL Paketscheine", file:$("#order_LabelURLLocal").val() }, function($data)
			{
				if( $("#order_customs").val()==1 )
				{
					$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"Brother HL-6180DW DHL Paketscheine", file:$("#order_LabelURLLocal").val() }, function($data)
					{
						hide_status();
						order_close();
						return;
					});
				}
				else
				{
					hide_status();
					order_close();
					return;
				}
			});
		}


		function print_export_documents()
		{
			show_status("Exportdokumente werden abgerufen...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"GetExportDocDD", id_order:$("#order_id_order").val() }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2("call error: "+$data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}

				show_status("Schicke Auftrag an Drucker...");
				$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"Brother HL-6180DW Tagesabschluss", file:$xml.find("LabelPath").text() }, function($data)
				{
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2("call error: "+$data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message+'<br />'+$data);
						return;
					}
					
					hide_status();
					alert("Bitte entnehmen Sie die Exportdokumente aus dem Rechnungsdrucker.");
				});
			});
		}


		function print_export_documents_dialog()
		{
			show_status("Exportdokumente werden abgerufen...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"GetExportDocDD", id_order:$("#order_id_order").val() }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2("call error: "+$data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
				
				hide_status();
				$("#print_pdf").hide();
				$("#print_pdf").attr("src", $xml.find("LabelURLLocal").text());			
			});
		}


		function print_label_dialog()
		{
			if( $("#order_ShipmentNumber").val()=="" )
			{
				label_create(true);
				return;
			}

			if( $("#order_ShipmentNumber").val()!="" && $("#order_LabelURLLocal").val()=="" )
			{
				label_get(true);
				return;
			}
			
			if( $("#order_ProductCode").val()=="EXP" )
			{
				alert("ACHTUNG: Es handelt sich um ein DHL-EXPRESS-Paket. Bitte in das DHL-EXPRESS-Regal legen!");
			}
			if( $("#order_customs").val()==1 )
			{
				alert("ACHTUNG: Sendung ist zollpflichtig. Zollunterlagen bitte aus dem Laserdrucker entnehmen und zusammen mit drei Rechnungskopien in einem Sichtschutzumschlag auf das Paket kleben!");
				print_export_documents();
			}

/*
			//wait until pdf is loaded
			$("#print_pdf").hide();
			$(function()
			{
			    $('#print_pdf').load(function()
				{
					if( $(this).css("display")=="none" )
					{
						$(this).show();
						document.getElementById('print_pdf').focus();
						document.getElementById('print_pdf').contentWindow.print();
					}
			    });
			});
*/
			hide_status();
			$("#print_pdf").hide();
			$("#print_pdf").attr("src", "<?php echo PATH ?>"+$("#order_LabelURLLocal").val());
			order_close();
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
				else print_label();
			}
		}
	
		function show_location_label($ReceiverCompany, $ReceiverCompany2, $ReceiverCompanyFirstname, $ReceiverCompanyLastname, $ReceiverStreetName, $ReceiverStreetNumber, $ReceiverZip, $ReceiverCity, $ReceiverContactPerson, $ReceiverOrigin, $ReceiverPhone, $ReceiverEmail)
		{
			$("#order_ShipperCompany").val("MAPCO Autotechnik GmbH");
			$("#order_ShipperCompany2").val("Lager Borkheide");
			$("#order_ShipperCompanyFirstname").val("Hans-Joachim");
			$("#order_ShipperCompanyLastname").val("Lange");
			$("#order_ShipperStreetName").val("Moosweg");
			$("#order_ShipperStreetNumber").val("1");
			$("#order_ShipperZip").val("14822");
			$("#order_ShipperCity").val("Borkheide");
			$("#order_ShipperContactPerson").val("Hans-Joachim Lange");
			$("#order_ShipperOrigin").val("DE");
			$("#order_ShipperPhone").val("+493384560035");
			$("#order_ShipperEmail").val("hjlange@mapo.de");

			$("#order_ReceiverCompany").val($ReceiverCompany);
			$("#order_ReceiverCompany2").val($ReceiverCompany2);
			$("#order_ReceiverCompanyFirstname").val($ReceiverCompanyFirstname);
			$("#order_ReceiverCompanyLastname").val($ReceiverCompanyLastname);
			$("#order_ReceiverStreetName").val($ReceiverStreetName);
			$("#order_ReceiverStreetNumber").val($ReceiverStreetNumber);
			$("#order_ReceiverZip").val($ReceiverZip);
			$("#order_ReceiverCity").val($ReceiverCity);
			$("#order_ReceiverContactPerson").val($ReceiverContactPerson);
			$("#order_ReceiverOrigin").val($ReceiverOrigin);
			$("#order_ReceiverPhone").val($ReceiverPhone);
			$("#order_ReceiverEmail").val($ReceiverEmail);

			var $ProductCode="EPN";
			if( $ReceiverOrigin!="DE" ) $ProductCode="BPI";
			$("#order_ProductCode").val($ProductCode);
			$("#order_ShipmentNumber").val("");
			$("#order_customs").val(0);
			$("#order_CustomerReference").val("Standortetikett");
			$("#order_CODAmount").val(0);
			$("#order_LengthInCM").val(60);
			$("#order_WidthInCM").val(40);
			$("#order_HeightInCM").val(30);
			$("#order_id_order").val("");

			$("#order_dialog").dialog
			({	buttons:
				[
					{ text: "Exportdokumente drucken", click: function() { print_export_documents_dialog(); } },
					{ text: "Etikett mit Druckdialog drucken", click: function() { print_label_dialog(); } },
					{ text: "Packstück hinzufügen", click: function() { order_shipment_add(); } },
					{ text: "Etikett drucken", click: function() { print_label_dialog(); } },
					{ text: "Schließen", click: function() { order_close(); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Bestellung überprüfen und Etikett drucken",
				width:700
			});

			$("#order_WeightInKG").focus();
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
							show_status2("call error: "+$data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message+'<br />'+$data);
						return;
					}

					//shop_id
					//1 = MAPCO-Shop
					//2 = AP-Shop
					//3 = eBay mapco-eu
					//4 = eBay ihr-autopartner
					$shop_id=$xml.find("shop_id").text();
					
					$("#order_id_order").val($id_order);
					$id_file=$xml.find("shipping_label_file_id").text();
					if( $id_file==0 ) $LabelURLLocal=""
					else
					{
						$folder=Math.floor($id_file/1000);
						$LabelURLLocal="files/"+$folder+"/"+$id_file+".pdf";
					}
					$("#order_LabelURLLocal").val($LabelURLLocal);
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
							$CODAmount += Number($(this).find("amount").text()) * Number($(this).find("netto").text());
						});
						$CODAmount += Number($xml.find("shipping_net").text());
						$CODAmount=Math.round($CODAmount*119)/100;
					}
					$("#order_CODAmount").val($CODAmount);
					//CustomerReference
					var $CustomerReference=$id_order;
					var $ordernr=$xml.find("ordernr").text();
					if( $ordernr!="" ) $CustomerReference += ", "+$ordernr;
					$("#order_CustomerReference").val($CustomerReference);

					if( $shop_id==2 || $shop_id==4 )
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
					else if( $shop_id==5 )
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
					else
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
					
					$("#order_ReceiverEmail").val($xml.find("usermail").text());
					$("#order_ReceiverPhone").val($xml.find("userphone").text());
					$ship_adr_id=$xml.find("ship_adr_id").text();
					if( $ship_adr_id>0 )
					{
						$("#order_ReceiverCompany").val($xml.find("ship_company").text());
						if( $xml.find("ship_street").text()=="Packstation" ) $("#order_ReceiverCompany2").val($xml.find("ship_additional").text());
						$("#order_ReceiverCompanyFirstname").val($xml.find("ship_firstname").text());
						$("#order_ReceiverCompanyLastname").val($xml.find("ship_lastname").text());
						var $ReceiverZip=$xml.find("ship_zip").text();
						if( $xml.find("ship_country_code").text()!="GB" ) $ReceiverZip=$ReceiverZip.replace(/\D/g,''); //remove non numeric characters from zip code
						$("#order_ReceiverZip").val($ReceiverZip.trim());
						$("#order_ReceiverCity").val($xml.find("ship_city").text());
						$("#order_ReceiverStreetName").val($xml.find("ship_street").text());
						$("#order_ReceiverStreetNumber").val($xml.find("ship_number").text());
						$ReceiverContactPerson="";
						if( $xml.find("bill_title").text()!="" ) $ReceiverContactPerson+=$xml.find("ship_title").text();
						if( $xml.find("bill_firstname").text()!="" ) $ReceiverContactPerson+=' '+$xml.find("ship_firstname").text();
						if( $xml.find("bill_lastname").text()!="" ) $ReceiverContactPerson+=' '+$xml.find("ship_lastname").text();
						$("#order_ReceiverContactPerson").val($ReceiverContactPerson);
						$("#order_ReceiverOrigin").val($xml.find("ship_country_code").text());
					}
					else
					{
						$("#order_ReceiverCompany").val($xml.find("bill_company").text());
						if( $xml.find("bill_street").text()=="Packstation" ) $("#order_ReceiverCompany2").val($xml.find("bill_additional").text());
						$("#order_ReceiverCompany2").val($xml.find("bill_additional").text());
						$("#order_ReceiverCompanyFirstname").val($xml.find("bill_firstname").text());
						$("#order_ReceiverCompanyLastname").val($xml.find("bill_lastname").text());
						var $ReceiverZip=$xml.find("bill_zip").text();
						if( $xml.find("bill_country_code").text()!="GB" ) $ReceiverZip=$ReceiverZip.replace(/\D/g,''); //remove non numeric characters from zip code
						$("#order_ReceiverZip").val($ReceiverZip.trim());
						$("#order_ReceiverCity").val($xml.find("bill_city").text());
						$("#order_ReceiverStreetName").val($xml.find("bill_street").text());
						$("#order_ReceiverStreetNumber").val($xml.find("bill_number").text());
						$ReceiverContactPerson="";
						if( $xml.find("bill_title").text()!="" ) $ReceiverContactPerson+=$xml.find("bill_title").text();
						if( $xml.find("bill_firstname").text()!="" ) $ReceiverContactPerson+=' '+$xml.find("bill_firstname").text();
						if( $xml.find("bill_lastname").text()!="" ) $ReceiverContactPerson+=' '+$xml.find("bill_lastname").text();
						$("#order_ReceiverContactPerson").val($ReceiverContactPerson);
						$("#order_ReceiverOrigin").val($xml.find("bill_country_code").text());
					}

					//check for customs setting
					if($xml.find("shipping_customs_file_id").text()!=0) $("#order_customs").val(1);
					else $("#order_customs").val(0);

					//determine shipping type
					$shipping_type_id = $xml.find("shipping_type_id").text();
					if( $shipping_type_id==2 ) $("#order_ProductCode").val('EXP');
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

					$("#order_dialog").dialog
					({	buttons:
						[
							{ text: "Exportdokumente drucken", click: function() { print_export_documents_dialog(); } },
							{ text: "Etikett mit Druckdialog drucken", click: function() { print_label_dialog(); } },
							{ text: "Packstück hinzufügen", click: function() { order_shipment_add(); } },
							{ text: "Etikett drucken", click: function() { print_label_dialog(); } },
							{ text: "Schließen", click: function() { order_close(); } }
						],
						closeText:"Fenster schließen",
						modal:true,
						resizable:false,
						title:"Bestellung überprüfen und Etikett drucken",
						width:700
					});

					$("#order_WeightInKG").focus();
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
			show_status("Rufe Kontaktdaten bei eBay ab...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"GetUserContactDetails", id_account:1, OrderID:$OrderID }, function($data)
			{
				show_status2($data);
			});
		}


		function get_order($OrderID)
		{
			show_status("Rufe Bestellung bei eBay ab...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"GetOrder", id_account:1, OrderID:$OrderID }, function($data)
			{
				show_status2($data);
			});
		}


		function set_shipment_tracking_info($id_order)
		{
			show_status("Schicke Sendungsnummer an eBay...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"CompleteSale", id_order:$id_order }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ( $ack!="Success" )
					{
						show_status2("call error: "+$data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				show_status("Sendungsnummer erfolgreich auf eBay gespeichert.");
			});
		}


		function set_all_shipment_tracking_infos()
		{
			show_status("Übertrage Daten an eBay...");
			$.post("<?php echo PATH ?>soa/", { API:"jobs", Action:"CompleteSales" }, function($data)
			{
				show_status2($data);
			});
		}


		function printer_list()
		{
			show_status("Schicke Auftrag an Drucker...");
			$.post("<?php echo PATH ?>soa/", { API:"dhl", Action:"PrintPDF", PrinterName:"Brother HL-6180DW DHL Paketscheine", file:"images/quali_doku.pdf" }, function($data)
			{
				show_status2($data);
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
			show_status("Lese Bestellungen neu ein...");
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
					show_status2(err.message);
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
/*
					$orders[$i]["id_order"]=$(this).find("id_order").text();
					$orders[$i]["AUF_ID"]=$(this).find("AUF_ID").text();
					$orders[$i]["shop_id"]=$(this).find("shop_id").text();
					$orders[$i]["foreign_OrderID"]=$(this).find("foreign_OrderID").text();
					$orders[$i]["shipping_type_id"]=$(this).find("shipping_type_id").text();
					$orders[$i]["bill_company"]=$(this).find("bill_company").text();
					$orders[$i]["bill_firstname"]=$(this).find("bill_firstname").text();
					$orders[$i]["bill_lastname"]=$(this).find("bill_lastname").text();
					$orders[$i]["bill_street"]=$(this).find("bill_street").text();
					$orders[$i]["bill_number"]=$(this).find("bill_number").text();
					$orders[$i]["bill_zip"]=$(this).find("bill_zip").text();
					$orders[$i]["bill_city"]=$(this).find("bill_city").text();
					$orders[$i]["bill_country"]=$(this).find("bill_country").text();
					$orders[$i]["shipping_details"]=$(this).find("shipping_details").text();
					$orders[$i]["shipping_costs"]=$(this).find("shipping_costs").text();
					$orders[$i]["shipping_number"]=$(this).find("shipping_number").text();
					$orders[$i]["firstmod"]=$(this).find("firstmod").text();
*/
					$i++;
				});
				$("#status").hide();
				if ( $show ) view2();
			});
		}
		
		function view2()
		{
			var $filter=$("input[name='choice']:checked").val();
			var $needle=$("#needle").val();
//			if( $filter!="timeframe" ) $("#timeframe").hide(); else $("#timeframe").show();

			$ordershtml  = '<table>';
			$ordershtml += '<tr>';
			$ordershtml += '	<th>Nr.</th>';
			$ordershtml += '	<th>Auftrags-ID</th>';
			$ordershtml += '	<th>Datum</th>';
			$ordershtml += '	<th>Shop</th>';
			$ordershtml += '	<th>Empfänger</th>';
			$ordershtml += '	<th>Ware</th>';
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
				if( $orders[$i]["ship_adr_id"]>0 )
				{
					var $AUF_ID='A'+$orders[$i]["AUF_ID"];
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
					var $AUF_ID='A'+$orders[$i]["AUF_ID"];
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
					//Ware
					$ordershtml += '	<td></td>';
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
		echo '<input onclick="show_location_label(\''.$row["company"].'\', \''.$row["title"].'\', \''.$row["firstname"].'\', \''.$row["lastname"].'\', \''.$row["street"].'\', \''.$row["streetnr"].'\', \''.$row["zipcode"].'\', \''.$row["city"].'\', \''.$row["firstname"].' '.$row["lastname"].'\', \''.$row["country_code"].'\', \''.$row["phone"].'\', \''.$row["mail"].'\');" style="float:left;" type="button" value="'.$row["location"].'" />';
	}
	echo '<input onclick="show_location_label(\'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'DE\', \'\', \'\');" style="float:left;" type="button" value="Freitext" />';
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
							try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
							$ack = $xml.find("Ack");
							if ( $ack.text()!="Success" ) { show_status2($data); return; }
							//company1 and company2
							var $company1=$xml.find("ANSCHR_1").text();
							var $company2=$xml.find("ANSCHR_2").text();
							if( $company1=="" && $company2!="" )
							{
								$company1=$company2;
								$company2="";
							}
							//firstname and lastname
							var $firstname=$xml.find("ANSCHR_3").text();
							var $lastname=$firstname.substr($firstname.search(" ")+1);
							$firstname=$firstname.substr(0, $firstname.search(" "));
							//street and streetnr
							var $street=$xml.find("STRASSE").text();
							var $streetnr=$street.substr($street.lastIndexOf(" ")+1, $street.length);
							var $street=$street.substr(0, $street.lastIndexOf(" "));
							
							show_location_label($company1, $company2, $firstname, $lastname, $street, $streetnr, $xml.find("PLZ").text(), $xml.find("ORT").text(), $xml.find("ANSCHR_3").text(), 'DE', '', '');
						});
					}
					else if ($id_order!="") show_order($id_order);
					//********************************neu*************************
					else
					{
						if($needle.length==8 && $needle.substring(0, 1)==="A" && !isNaN($needle.substring(1, 8)))
						{
							$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"OrderGet", auf_id: $needle.substring(1, 8) },
								function($data)
								{
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
	echo '	<table id="order_table">';
	echo '		<tr>';
	echo '			<th colspan="2">Absender</th>';
	echo '			<th colspan="2">Empfänger</th>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Firma<br/><input id="order_ShipperCompany" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">Firma<br/><input id="order_ReceiverCompany" style="width:300px;" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Firma 2<br/><input id="order_ShipperCompany2" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">Firma 2 / Packstation<br/><input id="order_ReceiverCompany2" style="width:300px;" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Vorname<br /><input id="order_ShipperCompanyFirstname" type="text" value="" /></td>';
	echo '			<td>Nachname<br /><input id="order_ShipperCompanyLastname" type="text" value="" /></td>';
	echo '			<td>Vorname<br /><input id="order_ReceiverCompanyFirstname" type="text" value="" /></td>';
	echo '			<td>Nachname<br /><input id="order_ReceiverCompanyLastname" type="text" value="" /></td>';
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
	echo '			<td colspan="2">Land<br/><input id="order_ShipperOrigin" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">Land<br/><input id="order_ReceiverOrigin" style="width:300px;" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td colspan="2">Ansprechpartner<br/><input id="order_ShipperContactPerson" style="width:300px;" type="text" value="" /></td>';
	echo '			<td colspan="2">Ansprechpartner<br/><input id="order_ReceiverContactPerson" style="width:300px;" type="text" value="" /></td>';
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
	echo '			<td>Versandart<br/><input id="order_ProductCode" type="text" value="" /></td>';
	echo '			<td>Sendungsnummer<br/><input id="order_ShipmentNumber" type="text" value="" /></td>';
	echo '			<td>Lokales Etikett<br/><input id="order_LabelURLLocal" type="text" value="" /><input id="order_LabelPath" type="hidden" value="" /></td>';
	echo '			<td>Zoll<br/>';
	echo '				<select id="order_customs"><option value="0">Nein</option><option value="1">Ja</option></select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Sendungsreferenz<br/><input id="order_CustomerReference" type="text" value="" /></td>';
	echo '			<td>Nachnahmegebühr<br/><input id="order_CODAmount" style="width:50px;" type="text" value="" /> Euro</td>';
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
	echo '<div id="print_dialog" style="display:none;">';
	echo '	<div id="print_status" style="font-size:20px; font-weight:bold;"></div>';
	echo '	<br style="clear:both;" />';
	echo '	<iframe id="print_pdf" style="width:750px; height:400px; display:none;" type="application/pdf" />';
	echo '</div>';
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>