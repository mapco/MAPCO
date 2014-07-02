<?php
	include("../../config.php");
	include("../../functions/cms_t.php");
	include("../../functions/mapco_gewerblich.php");
	
	global $dbweb;
	global $dbshop;
			
	
			$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);

			$gewerblich=gewerblich($row["customer_id"]);
	//Daten für den Kunden
			
			//CHECK AUF PAYPAL-Zahlung && ZAHLSTATUS
			$onlinePayment=false;
			$onlinePaymentState="";
			if ($row["Payment_TransactionID"]!=""){
				$onlinePayment=true;
				if ($row["Payment_TransactionState"]=="Completed" || $row["Payment_TransactionState"]=="OK" || $row["Payment_TransactionState"]=="AUTHORIZED") 
				{
					$text2  = '<p style="font-size:16; font-weight:bold">Vielen Dank für Ihre Bestellung!</p>';
					$text2 .= '<p>Ihre Bestellung ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
					$text2 .= '<p>Bestellnummer: '.$row["id_order"].'</p>';
				}
				elseif ($row["Payment_TransactionState"]=="Pending")
				{
					$onlinePaymentState="Pending";
					
					$text2  = '<p style="font-size:16; font-weight:bold">Vielen Dank für Ihre Bestellung!</p>';
					$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Der Versand erfolgt nachdem PayPal uns den Erhalt Ihrer Zahlung bestätigt hat. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
					$text2 .= '<p>Bestellnummer: '.$row["id_order"].'</p>';
				}
			}
			//ZAHLUNG per Überweisung
			else {
				$text2  = '<p style="font-size:16; font-weight:bold">Vielen Dank für Ihre Bestellung!</p>';
				$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Der Versand erfolgt nach dem Eingang Ihrer Zahlung. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
				$text2 .= '<p>Bestellnummer: '.$row["id_order"].'</p>';
			}
			
			$text = "";
			//bill address
//			$text  = '<div style="width:100%; padding-bottom:15px;">';
			$text .= '<table border="0" cellpadding="0" cellspacing="0" style="width:49%; float:left; padding-bottom:25px;">';
			$text .= '<tr>';
			$text .= '<th colspan="2">Rechnungsanschrift</th>';
			$text .= '</tr><tr>';
			if ($row["bill_company"]!="") $text .= '<tr><td style="width:8px"></td><td>'.$row["bill_company"].'</td></tr>';
			$text .= '<tr><td style="width:8px"></td><td>'.$row["bill_gender"].' '.$row["bill_title"].'</td></tr>';
			$text .= '<tr><td style="width:8px"></td><td>'.$row["bill_firstname"].' '.$row["bill_lastname"].'</td></tr>';
			$text .= '<tr><td style="width:8px"></td><td>'.$row["bill_street"].' '.$row["bill_number"].'</td></tr>';
			$text .= '<tr><td style="width:8px"></td><td>'.$row["bill_zip"].' '.$row["bill_city"].'</td></tr>';
			if ($row["bill_additional"]!="") $text .= '<tr><td style="width:8px"></td><td>'.$row["bill_additional"].'</td></tr>';
			$text .= '<tr><td style="width:8px"></td><td>'.$row["bill_country"].'</td></tr>';
			$text .= '</table>';
	
			//shipping address
			if ($row["ship_lastname"]!="")
			{
				$text .= '<table border="0" cellpadding="0" cellspacing="0" style="width:49%; float:right; margin-left:15px; padding-bottom:"5px;">';
				$text .= '<tr>';
				$text .= '<th colspan="2">Lieferanschrift</th>';
				$text .= '</tr><tr>';
				if ($row["ship_company"]!="") $text .= '<tr><td style="width:8px"></td><td>'.$row["ship_company"].'</td></tr>';
				$text .= '<tr><td style="width:8px"></td><td>'.$row["ship_gender"].' '.$row["ship_title"].'</td></tr>';
				$text .= '<tr><td style="width:8px"></td><td>'.$row["ship_firstname"].' '.$row["ship_lastname"].'</td></tr>';
				$text .= '<tr><td style="width:8px"></td><td>'.$row["ship_street"].' '.$row["ship_number"].'</td></tr>';
				$text .= '<tr><td style="width:8px"></td><td>'.$row["ship_zip"].' '.$row["ship_city"].'</td></tr>';
				if ($row["ship_additional"]!="") $text .= '<tr><td style="width:8px"></td><td>'.$row["ship_additional"].'</td></tr>';
				$text .= '<tr><td style="width:8px"></td><td>'.$row["ship_country"].'</td></tr>';
				$text .= '</table>';
			}
			
			//bill

			$text .= '<table border="0" cellpadding="0" cellspacing="0" style="padding-bottom:10px;">';
			$text .= '  <tr><th colspan="7">Bestellung</th>';
			$text .= '</tr>';
			$text .= '</table>';
			$text .= '<table width="100%" cellspacing="0" cellpadding="5" style="padding-bottom:25px;">';
			$text .= '  <tr>';
			$text .= '    <td style="width:8px">&nbsp;</td>';
			$text .= '    <td><b>Artikel-Nr.</b></td>';
			$text .= '    <td><b>Menge</b></td>';
			$text .= '    <td><b>Bezeichnung</b></td>';
			$text .= '    <td style="text-align:right"><b>Einzelpreis</b></td>';
			$text .= '    <td style="text-align:right"><b>Gesamt</b></td>';
			$text .= '    <td style="width:10px">&nbsp;</td>';
			$text .= '  </tr>';
			
			$results2=q("SELECT price, amount, item_id FROM shop_orders_items WHERE order_id=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
			$total=0;
			while($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT a.title, b.ArtNr FROM shop_items_de AS a, shop_items_artnr AS b WHERE a.id_item=".$row2["item_id"]." AND b.item_id=a.id_item LIMIT 1;", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$text .= '  <tr>';
				$text .= '	<td></td>';
				$text .= '  <td>'.$row3["ArtNr"].'</td>';
				$text .= '  <td>'.number_format($row2["amount"], 0).'</td>';
				$text .= '  <td>'.str_replace('('.$row3["ArtNr"].')','',$row3["title"]).'</td>';
				$text .= '  <td style="text-align:right">'.number_format($row2["price"], 2,",",".").' Euro</td>';
				$price=$row2["amount"]*$row2["price"];
				$total+=$price;
				$text .= '  <td style="text-align:right">'.number_format($price, 2,",",".").' Euro</td>';
				$text .= '	<td></td>';
				$text .= '  </tr>';
			}
			
			//Versandkosten
			$text .= '  <tr>';
			$text .= '	<td></td>';
			$text .= '    <td  style="border-top-style:solid; border-top-width:1px;" colspan="4">'.$row["shipping_details"].'</td><td  style="border-top-style:solid; border-top-width:1px; text-align:right">'.number_format($row["shipping_costs"], 2,",",".").' Euro</td>';
			$text .= '	  <td></td>';
			$text .= '  </tr>';
			$totalpos = $total;
			$total+=$row["shipping_costs"];
					
			//Gesamt Netto
				$ust=$total/(100+UST)*UST;
				$totalpos/=((100+UST)/100);
			$text .= '  <tr>';
			$text .= '	<td></td>';
			$text .= '    <td colspan="4">';
			$text .= 'darin enthalten, '.UST.'%';  
			$text .= ' Mehrwertsteuer</td><td style="text-align:right">'.number_format($ust, 2,",",".").' Euro</td>';
			$text .= '  </tr>';
		
			//Gesamt Brutto
			$text .= '  <tr>';
			$text .= ' 	  <td></td>';

			$text .= '    <td style="border-top-style:solid; border-top-width:1px;" colspan="4"><b>Gesamtpreis Brutto</b></td><td style="border-top-style:solid; border-top-width:1px; text-align:right"><b>'.number_format($total, 2,",",".").' Euro</b></td>';
			$text .= '	  <td></td>';
			$text .= '  </tr>';
			$text .= '</table>';
			
//			$text .= '</div>';
					
			$text4="";
			
			if (!$onlinePayment) {
			
			$text4 .= '<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5" style="padding-bottom:10px;">';
			$text4 .= '<tr>';
			$text4 .= '<th colspan="3">'.t("Zahlung bitte an folgende Bankverbindung").'</th>';
			$text4 .= '</tr>';
			$text4 .= '</table>';
			$text4 .= '<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5" style="padding-bottom:25px;">';
			$text4 .= '<tr>';
			$text4 .= '		<td style="width:8px"></td>';
			$text4 .= '		<td>'.t("Name der Bank").'</td>';
			$text4 .= '		<td>Berliner Volksbank eG</td>';
			$text4 .= '</tr>';
			$text4 .= '<tr>';
			$text4 .= '		<td style="width:8px"></td>';
			$text4 .= '		<td>'.t("Bankleitzahl").'</td>';
			$text4 .= '		<td>100 900 00</td>';
			$text4 .= '</tr>';
			$text4 .= '<tr>';
			$text4 .= '		<td style="width:8px"></td>';
			$text4 .= '		<td>'.t("Kontonummer").'</td>';
			$text4 .= '		<td>5 152 044 005</td>';
			$text4 .= '</tr>';
			$text4 .= '<tr>';
			$text4 .= '		<td style="width:8px"></td>';
			$text4 .= '		<td>Swift-Code</td>';
			$text4 .= '		<td>BEVO DE BB</td>';
			$text4 .= '</tr>';
			$text4 .= '<tr>';
			$text4 .= '		<td style="width:8px"></td>';
			$text4 .= '		<td>IBAN</td>';
			$text4 .= '		<td>DE 52 1009 0000 5152 0440 05</td>';
			$text4 .= '</tr>';
			$text4 .= '<tr>';
			$text4 .= '		<td style="width:8px"></td>';
			$text4 .= '		<td>'.t("Verwendungszweck").'</td>';
			$text4 .= '		<td>'.$row["id_order"].' / '.$row["bill_firstname"].' '.$row["bill_lastname"].'</td>';
			$text4 .= '</tr>';
			$text4 .= '</table>';
			
			}

		if (!$gewerblich){
			//$text4 .= '<b>Widerrufsbelehrung</b>';
			$text4 .= '<table border="0" cellpadding="0" cellspacing="0" style="float:left; padding-bottom:10px;">';
			$text4 .= '<tr>';
			$text4 .= '<th colspan="3">Widerrufsbelehrung</th>';
			$text4 .= '</tr>';
			$text4 .= '</table>';
			$text4 .= '<table>';
			$text4 .= '<tr><td style="width:8px;"></td>';
		//	$text4 .= '<td style="font-size:10pt;">';
		//	$text4 .= '<p>';
		/*	$text4 .= 'Widerrufsrecht: Der Verbraucher kann die Vertragserklärung innerhalb von 14 Tagen ohne Angabe von Gründen in Textform (z. B. Brief, Fax, E-Mail) oder – wenn die Sache vor Fristablauf überlassen wird – durch Rücksendung der Sache widerrufen. Die Frist beginnt nach Erhalt dieser Belehrung in Textform, jedoch nicht vor Eingang der Ware beim Empfänger (bei der wiederkehrenden Lieferung gleichartiger Waren nicht vor Eingang der ersten Teillieferung) und auch nicht vor Erfüllung unserer Informationspflichten gemäß Artikel 246 § 2 in Verbindung mit § 1 Absatz. 1 und 2 EGBGB sowie unserer Pflichten gemäß § 312g Absatz. 1 Satz 1 BGB in Verbindung mit Artikel 246 § 3 EGBGB. Zur Wahrung der Widerrufsfrist genügt die rechtzeitige Absendung des Widerrufs oder der Sache.';
		//	$text4 .= '</p><p>';
			$text4 .= '</td><td style="width:8px"></td></tr>';
			$text4 .= '<tr><td style="width:8px"></td><td style="font-size:10pt;">';
			$text4 .= 'Der Widerruf ist zu richten an: <br />';
			$text4 .= 'Autopartner GmbH, Gregor-von-Brück Ring 1, 14822 Brück, Deutschland. <br />';
			$text4 .= 'Geschäftsführer: Uta Seeliger, E-Mail: bestellung@ihr-autopartner.de, Fax: 033844 / 758299';
//			$text4 .= '</p><p>';
			$text4 .= '</td><td style="width:8px"></td></tr>';
			$text4 .= '<tr><td style="width:8px"></td><td style="font-size:10pt;">';
			$text4 .= 'Widerrufsfolgen: Im Falle eines wirksamen Widerrufs sind die beiderseits empfangenen Leistungen zurückzugewähren und gegebenenfalls gezogene Nutzungen (z. B. Zinsen) herauszugeben. Kann der Verbraucher dem Verkäufer die empfangene Leistung ganz oder teilweise nicht oder nur in verschlechtertem Zustand zurückgewähren, müssen, muss er insoweit gegebenenfalls Wertersatz leisten. Bei der Überlassung von Sachen gilt dies nicht, wenn die Verschlechterung der Sache ausschließlich auf deren Prüfung – wie sie etwa im Ladengeschäft möglich gewesen wäre – zurückzuführen ist. Im Übrigen kann der Verbraucher die Pflicht zum Wertersatz für eine durch die bestimmungsgemäße Ingebrauchnahme der Sache entstandene Verschlechterung vermeiden, indem er die Sache nicht wie sein Eigentum in Gebrauch nimmt und alles unterlässt, was deren Wert beeinträchtigt. Paketversandfähige Sachen sind auf Gefahr des Verkäufers zurückzusenden. Der Verbraucher hat die Kosten der Rücksendung zu tragen, wenn die gelieferte Ware der bestellten entspricht und wenn der Preis der zurückzusendenden Sache einen Betrag von 40 Euro nicht übersteigt oder wenn der Verbraucher bei einem höheren Preis der Sache zum Zeitpunkt des Widerrufs noch nicht die Gegenleistung oder eine vertraglich vereinbarte Teilzahlung erbracht hat. Anderenfalls ist die Rücksendung für den Verbraucher kostenfrei. Nicht paketversandfähige Sachen werden bei dem Verbraucher abgeholt. Verpflichtungen zur Erstattung von Zahlungen müssen innerhalb von 30 Tagen erfüllt werden. Die Frist beginnt für den Verbraucher mit der Absendung der Widerrufserklärung oder der Sache, für den Verkäufer mit deren Empfang.';
//			$text4 .= '</p><p>';
			$text4 .= '</td><td style="width:8px"></td></tr>';
			$text4 .= '<tr><td style="width:8px"></td><td style="font-size:10pt;">';
			$text4 .= 'Das Widerrufsrecht besteht gemäß § 312d Abs. 4 Nr. 1 BGB nicht bei Fernabsatzverträgen zur Lieferung von Waren, die nach Kundenspezifikation angefertigt werden oder eindeutig auf die persönlichen Bedürfnisse zugeschnitten sind oder die auf Grund ihrer Beschaffenheit nicht für eine Rücksendung geeignet sind.';
//			$text4 .= '</p>';
			$text4 .= '</td><td style="width:8px"></td></tr>';
		*/
			$text4 .= '<td>';
			$res=q("SELECT * FROM cms_articles WHERE id_articles = 28291;", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($res);
			$text4 .=$row["article"];
			$text4 .='</td>';
			$text4 .='</tr>';
			$text4 .= '</table>';
		}
			


	echo '<shop_cart_orderViewResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$text2.$text.$text4.']]></Response>'."\n";
	echo '</shop_cart_orderViewResponse>'."\n";

?>