<?php
	//Testfunktion für emailversand
	include_once("../config.php");
	//include_once("shop_get_price.php");
	include_once("shop_get_prices.php");
	//include_once("shop_get_net_price.php");
	include_once("shop_itemstatus.php");	
	include_once("cms_send_html_mail.php");
	include_once("mapco_gewerblich.php");
	include_once("cms_t.php");


	///äöüÄÖÜ UTF-8
	if (!function_exists("mail_order2"))
	{
		function mail_order2($id_order, $mailto_buyer, $mailto_agent)
		{
			global $dbweb;
			global $dbshop;
			
		//	$gewerblich=gewerblich($_SESSION["id_user"]);
	
			$results=q("SELECT * FROM shop_orders WHERE id_order=".$id_order.";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			
			$customer_id=$row["customer_id"];
			$partner_id=$row["partner_id"];

			if($row["ship_adr_id"]>0) 
			{
				$results3=q("SELECT * FROM shop_ship_adr WHERE adr_id=".$row["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$country_id=$row3["country_id"];
			}
			elseif($row["bill_adr_id"]>0) 
			{
				$results3=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$row["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$country_id=$row3["country_id"];
			}
			else $country_id=0;			
			
			$gewerblich=gewerblich($customer_id);
			
			//shop-daten
			$results6=q("SELECT * FROM shop_shops WHERE id_shop=".$row["shop_id"].";", $dbshop, __FILE__, __LINE__);
			$row6=mysqli_fetch_array($results6);
			
			$shop_id=$row["shop_id"];
			$city=str_replace("Online-Shop ", "", $row6["description"]);
			$mail=$row6["mail"];
			$order_mail=$row6["order_mail"];
	
			//Daten für Borkheide
			$results2=q("SELECT * FROM cms_users WHERE id_user=".$row["customer_id"].";", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$usermail=$row2["usermail"];
			$text1  = '	Bestellzeit: '.date("d.m.Y H:i", $row["firstmod"]);
			$text1 .= '	<br />Kundennummer: '.$row2["username"];
			$text1 .= '	<br />Online-Shop-Bestellnummer: '.$row["id_order"];
			if ($row["Payments_TransactionID"]!="") $text1 .= ' <br /><b>PAYMENTS-TRANSACTION ID: </b>'.$row["Payments_TransactionID"];
			$text1 .= '	<br />Eigene Bestellnummer: '.$row["ordernr"];
			$text1 .= '	<br /><br>Anmerkung:<br />'.nl2br($row["comment"]);
			$text1 .= '</p>';
			$text1 .= '<p><b>Kontaktdaten:</b><br>';
			$text1 .= 'E-Mail: '.$row["usermail"];
			if ($row["userphone"]!="") $text1 .= '<br>Telefon:'.$row["userphone"];
			if ($row["userfax"]!="") $text1 .= '<br>Telefax:'.$row["userfax"];
			if ($row["usermobile"]!="") $text1 .= '<br>Handy:'.$row["usermobile"];
			$text1 .= '</p>';
			
			//Daten für den Kunden
			//CHECK AUF PAYPAL-Zahlung && ZAHLSTATUS
			$onlinePayment=false;
			$paymentState="";
			$text10='';
			if ($row["Payments_TransactionID"]!=""){
				$onlinePayment=true;
				if ($row["Payments_TransactionState"]=="Completed" || $row["Payments_TransactionState"]=="OK" || $row["Payments_TransactionState"]=="AUTHORIZED") 
				{
					//$text2  = '<p>Vielen Dank für Ihre Bestellung!</p>';
					//$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
					//$text2 .= '<p>Online-Shop-Bestellnummer: '.$row["id_order"].'</p>';
				}
				elseif ($row["Payments_TransactionState"]=="Pending")
				{
					$paypalState="Pending";
					
					//$text2  = '<p>Vielen Dank für Ihre Bestellung!</p>';
					//$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Der Versand erfolgt nachdem PayPal uns den Erhalt Ihrer Zahlung bestätigt hat. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
					//$text2 .= '<p>Online-Shop-Bestellnummer: '.$row["id_order"].'</p>';
					
					$text10 .= ' Der Versand erfolgt nachdem PayPal uns den Erhalt Ihrer Zahlung bestätigt hat.';
				}
			}
			//ZAHLUNG per Überweisung || Rechnung
			else {
				//$text2  = '<p>Vielen Dank für Ihre Bestellung!</p>';
				//$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
				//$text2 .= '<p>Bestellnummer: '.$row["id_order"].'</p>';
			}

			//bill address
			/*$text = '<p><b>Rechnungsanschrift:</b><br>';
			if ($row["bill_company"]!="") $text .= $row["bill_company"].'<br>';
			$text .= $row["bill_gender"].' '.$row["bill_title"].'<br>';
			$text .= $row["bill_firstname"].' '.$row["bill_lastname"].'<br>';
			$text .= $row["bill_street"].' '.$row["bill_number"].'<br>';
			$text .= $row["bill_zip"].' '.$row["bill_city"].'<br>';
			if ($row["bill_additional"]!="") $text .= $row["bill_additional"].'<br>';
			$text .= $row["bill_country"].'<br>';
			$text .= '</p>';
	
			//shipping address
			if ($row["ship_lastname"]!="")
			{
				$text .= '<p><b>Lieferanschrift:</b><br>';
				if ($row["ship_company"]!="") $text .= $row["ship_company"].'<br>';
				$text .= $row["ship_gender"].' '.$row["ship_title"].'<br>';
				$text .= $row["ship_firstname"].' '.$row["ship_lastname"].'<br>';
				$text .= $row["ship_street"].' '.$row["ship_number"].'<br>';
				$text .= $row["ship_zip"].' '.$row["ship_city"].'<br>';
				if ($row["ship_additional"]!="") $text .= $row["ship_additional"].'<br>';
				$text .= $row["ship_country"].'<br>';
				$text .= '</p>';
			}*/

//*************************************************************template******************************			
			//bill address Roma
			//bill address Roma
			if( $shop_id == 17 )
			{
				$text7 = '<p><b>Rechnungsanschrift:</b> ';
				if ($row["bill_company"]!="") $text7 .= $row["bill_company"].', ';
				$text7 .= $row["bill_gender"].' '.$row["bill_title"];
				$text7 .= $row["bill_firstname"].' '.$row["bill_lastname"];
				$text7 .= ', '.$row["bill_street"].' '.$row["bill_number"];
				$text7 .= ', '.$row["bill_zip"].' '.$row["bill_city"];
				if ($row["bill_additional"]!="") $text7 .= ', '.$row["bill_additional"].'<br>';
				$text7 .= ', '.$row["bill_country"].'<br>';
				$text7 .= '</p>';
			}
			//bill address
			else
			{
				$text7 = '<p><b>Rechnungsanschrift:</b><br>';
				if ($row["bill_company"]!="") $text7 .= $row["bill_company"].'<br>';
				$text7 .= $row["bill_gender"].' '.$row["bill_title"].'<br>';
				$text7 .= $row["bill_firstname"].' '.$row["bill_lastname"].'<br>';
				$text7 .= $row["bill_street"].' '.$row["bill_number"].'<br>';
				$text7 .= $row["bill_zip"].' '.$row["bill_city"].'<br>';
				if ($row["bill_additional"]!="") $text7 .= $row["bill_additional"].'<br>';
				$text7 .= $row["bill_country"].'<br>';
				$text7 .= '</p>';
			}
	
			//shipping address
			if ($row["ship_lastname"]!="")
			{
				$text7 .= '<p><b>Lieferanschrift:</b><br>';
				if ($row["ship_company"]!="") $text7 .= $row["ship_company"].'<br>';
				$text7 .= $row["ship_gender"].' '.$row["ship_title"].'<br>';
				$text7 .= $row["ship_firstname"].' '.$row["ship_lastname"].'<br>';
				$text7 .= $row["ship_street"].' '.$row["ship_number"].'<br>';
				$text7 .= $row["ship_zip"].' '.$row["ship_city"].'<br>';
				if ($row["ship_additional"]!="") $text7 .= $row["ship_additional"].'<br>';
				$text7 .= $row["ship_country"].'<br>';
				$text7 .= '</p>';
			}
			
			//bill Tabelle
			$text8 = '<table border="1" cellpadding="4">';
			$text8 .= '  <tr><th colspan="6">Bestellung</th></tr>';
			$text8 .= '  <tr>';
			$text8 .= '    <td>Artikel-Nr.</td>';
			$text8 .= '    <td>Menge</td>';
			$text8 .= '    <td>Bezeichnung</td>';
			$text8 .= '    <td>EK</td>';
			$text8 .= '    <td>Gesamt</td>';
			$text8 .= '    <td></td>';
			$text8 .= '  </tr>';
			
			$results2=q("SELECT a.price, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=".$row["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
			$total=0;
			$collateral_count=0;
			$collateral_sum=0;
			while($row2=mysqli_fetch_array($results2))
			{
				$text8 .= '  <tr>';
				$text8 .= '  <td>'.$row2["MPN"].'</td>';
				$text8 .= '  <td>'.number_format($row2["amount"], 2).'</td>';
				$text8 .= '  <td>'.$row2["title"];
				if($row2["collateral"]>0) 
				{
					$text8 .= '<br />zzgl. '.$row2["collateral"].' Euro Altteilpfand';
					$collateral_count=$collateral_count+$row2["amount"];
					$collateral_sum=$collateral_sum+(number_format($row2["collateral"]*$row2["amount"], 2));
				}
				$text8 .= '  </td>';
				$text8 .= '  <td>'.number_format($row2["price"], 2).' Euro</td>';
				$price=$row2["amount"]*$row2["price"];
				$total+=$price;
				$text8 .= '  <td>'.number_format($price, 2).' Euro</td>';
				if ($partner_id>0) 
			//	if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				{
					$text8 .= '  <td><span style="float:right;">'.itemstatus_rc($row2["id_item"], 1, $row2["amount"]).'</span></td>';					
				}
				else
				{
					$text8 .= '  <td><span style="float:right;">'.itemstatus($row2["id_item"], 1, $row2["amount"]).'</span></td>';
				}
				$text8 .= '  </tr>';
			}
			
			//Nettowarenwert
			$totalpos = $total;

			//Altteilpfand
			if($collateral_count>0 and $collateral_sum>0)
			{
				$text8 .= '  <tr>';
				$text8 .= '    <td colspan="4">';
				$text8 .= ' Altteilpfand für '.$collateral_count.' Artikel';
				if ( $collateral_count==1 ) $text8 .= '<br />Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.';
				else $text8 .= '<br />Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.';
				$text8 .= '<br />Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind.';
				$text8 .= '   <td colspan="2">'.number_format($collateral_sum, 2).' Euro</td>';
				$text8 .= '  </tr>';
				$total+=$collateral_sum;
			}

			//Versandkosten
			$text8 .= '  <tr>';
			$text8 .= '    <td colspan="4">'.$row["shipping_details"].'</td><td colspan="2">'.number_format($row["shipping_costs"], 2).' Euro</td>';
			$text8 .= '  </tr>';
			$total+=$row["shipping_costs"];
					
			//Gesamt Netto
			if ($gewerblich)
			{
/*				if ($country_id==1)
				{
					$discount = $totalpos*0.04;
					$totalpos = $totalpos-$discount;
					$total = $total-$discount;
					$text .= '  <tr>';
					$text .= '    <td colspan="4">Gesamtpreis Netto abzgl. 4% (€ '.number_format($discount, 2).') Online-Rabatt</td><td colspan="2">'.number_format($total, 2).' Euro</td>';
					$text .= '  </tr>';
					$ust=(UST/100)*$total;
					$total=((100+UST)/100)*$total;
				}
				else
*/
				{
					$discount = 0;
					$text8 .= '  <tr>';
					$text8 .= '    <td colspan="4">Gesamtpreis Netto</td><td colspan="2">'.number_format($total, 2).' Euro</td>';
					$text8 .= '  </tr>';
					$ust=(UST/100)*$total;
					$total=((100+UST)/100)*$total;
				}
			}
			else 
			{
				$discount = 0;
				$ust=$total/(100+UST)*UST;
				$totalpos/=((100+UST)/100);
			}
			$text8 .= '  <tr>';
			$text8 .= '    <td colspan="4">';
			if ($gewerblich)
			{
				$text8 .= 'zzgl. '.UST.'% gesetzliche';
			}
			else
			{
				$text8 .= 'darin enthalten, '.UST.'%';  
			}
			$text8 .= ' Umsatzsteuer</td><td colspan="2">'.number_format($ust, 2).' Euro</td>';
			$text8 .= '  </tr>';
		
			//Gesamt Brutto
			$text8 .= '  <tr>';
			$text8 .= '    <td colspan="4"><b>Gesamtpreis Brutto</b></td><td colspan="2"><b>'.number_format($total, 2).' Euro</b></td>';
			$text8 .= '  </tr>';
			$text8 .= '</table>';
			
			//bill Tabelle Italien
			$text9 = '<table border="1" cellpadding="4">';
			$text9 .= '  <tr><th colspan="7">'.t("Bestellung").'</th></tr>';
			$text9 .= '  <tr>';
			$text9 .= '    <td>Artikel-Nr.</td>';
			$text9 .= '    <td>Menge</td>';
			$text9 .= '    <td>Bezeichnung</td>';
			$text9 .= '    <td>Lagerort</td>';
			$text9 .= '    <td>EK</td>';
			$text9 .= '    <td>Gesamt</td>';
			$text9 .= '    <td></td>';
			$text9 .= '  </tr>';
			
			$results2=q("SELECT a.price, a.amount, a.collateral, c.title, b.MPN, b.id_item, d.pallet FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c, mapco_gls_roma AS d WHERE a.order_id=".$row["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item AND c.id_item=d.item_id ORDER BY d.pallet;", $dbshop, __FILE__, __LINE__);
			$amountsum=0;
			$total=0;
			$collateral_count=0;
			$collateral_sum=0;
			while($row2=mysqli_fetch_array($results2))
			{
				$text9 .= '  <tr>';
				$text9 .= '  <td>'.$row2["MPN"].'</td>';
				$amountsum=$amountsum+$row2["amount"];
				$text9 .= '  <td>'.number_format($row2["amount"], 2, ",", "").'</td>';
				$text9 .= '  <td>'.$row2["title"];
				if($row2["collateral"]>0) 
				{
					$text9 .= '<br />zzgl. '.$row2["collateral"].' '.t("Euro Altteilpfand").'';
					$collateral_count=$collateral_count+$row2["amount"];
					$collateral_sum=$collateral_sum+(number_format($row2["collateral"]*$row2["amount"], 2, ",", ""));
				}
				$text9 .= '  </td>';
				$text9 .= '  <td>'.$row2["pallet"].'</td>';
				$text9 .= '  <td>'.number_format($row2["price"], 2, ",", "").' Euro</td>';
				$price=$row2["amount"]*$row2["price"];
				$total+=$price;
				$text9 .= '  <td>'.number_format($price, 2, ",", "").' Euro</td>';
				if ($partner_id>0) 
			//	if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				{
					$text9 .= '  <td><span style="float:right;">'.itemstatus_rc($row2["id_item"], 1, $row2["amount"]).'</span></td>';					
				}
				else
				{
					$text9 .= '  <td><span style="float:right;">'.itemstatus($row2["id_item"], 1, $row2["amount"]).'</span></td>';
				}
				$text9 .= '  </tr>';
			}
			
			//Nettowarenwert
			$totalpos = $total;

			//Altteilpfand
			if($collateral_count>0 and $collateral_sum>0)
			{
				$text9 .= '  <tr>';
				$text9 .= '	   <td style="border-right: none"></td>';
				$text9 .= '	   <td style="border-left: none">'.number_format($amountsum, 2, ",", "").'</td>';
				$text9 .= '    <td colspan="3">';
				$text9 .= ' Altteilpfand für '.$collateral_count.' Artikel';
				if ( $collateral_count==1 ) $text9 .= '<br />Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.';
				else $text9 .= '<br />Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.';
				$text9 .= '<br />Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind.';
				$text9 .= '   <td colspan="2">'.number_format($collateral_sum, 2, ",", "").' Euro</td>';
				$text9 .= '  </tr>';
				$total+=$collateral_sum;
				
				//Versandkosten
				$text9 .= '  <tr>';
				$text9 .= '    <td colspan="5">'.$row["shipping_details"].'</td><td colspan="2">'.number_format($row["shipping_costs"], 2, ",", "").' Euro</td>';
				$text9 .= '  </tr>';
				$total+=$row["shipping_costs"];
			}
			else
			{
				//Versandkosten
				$text9 .= '  <tr>';
				$text9 .= '	   <td style="border-right: none"></td>';
				$text9 .= '	   <td style="border-left: none">'.number_format($amountsum, 2, ",", "").'</td>';
				$text9 .= '    <td colspan="3">'.$row["shipping_details"].'</td><td colspan="2">'.number_format($row["shipping_costs"], 2, ",", "").' Euro</td>';
				$text9 .= '  </tr>';
				$total+=$row["shipping_costs"];
			}
					
			//Gesamt Netto
			if ($gewerblich)
			{
/*				if ($country_id==1)
				{
					$discount = $totalpos*0.04;
					$totalpos = $totalpos-$discount;
					$total = $total-$discount;
					$text .= '  <tr>';
					$text .= '    <td colspan="4">Gesamtpreis Netto abzgl. 4% (€ '.number_format($discount, 2).') Online-Rabatt</td><td colspan="2">'.number_format($total, 2).' Euro</td>';
					$text .= '  </tr>';
					$ust=(UST/100)*$total;
					$total=((100+UST)/100)*$total;
				}
				else
*/
				{
					$discount = 0;
					$text9 .= '  <tr>';
					$text9 .= '    <td colspan="5">Gesamtpreis Netto</td><td colspan="2">'.number_format($total, 2, ",", "").' Euro</td>';
					$text9 .= '  </tr>';
					$ust=(UST/100)*$total;
					$total=((100+UST)/100)*$total;
				}
			}
			else 
			{
				$discount = 0;
				$ust=$total/(100+UST)*UST;
				$totalpos/=((100+UST)/100);
			}
			$text9 .= '  <tr>';
			$text9 .= '    <td colspan="5">';
			if ($gewerblich)
			{
				$text9 .= 'zzgl. '.UST.'% gesetzliche';
			}
			else
			{
				$text9 .= 'darin enthalten, '.UST.'%';  
			}
			$text9 .= ' Umsatzsteuer</td><td colspan="2">'.number_format($ust, 2, ",", "").' Euro</td>';
			$text9 .= '  </tr>';
		
			//Gesamt Brutto
			$text9 .= '  <tr>';
			$text9 .= '    <td colspan="5"><b>Gesamtpreis Brutto</b></td><td colspan="2"><b>'.number_format($total, 2, ",", "").' Euro</b></td>';
			$text9 .= '  </tr>';
			$text9 .= '</table>';
//*******************************************************************************************************
			
			//bill
		/*	$text .= '<table border="1" cellpadding="4">';
			$text .= '  <tr><th colspan="6">Bestellung</th></tr>';
			$text .= '  <tr>';
			$text .= '    <td>Artikel-Nr.</td>';
			$text .= '    <td>Menge</td>';
			$text .= '    <td>Bezeichnung</td>';
			$text .= '    <td>EK</td>';
			$text .= '    <td>Gesamt</td>';
			$text .= '    <td></td>';
			$text .= '  </tr>';
			
			$results2=q("SELECT a.price, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=".$row["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
			$total=0;
			$collateral_count=0;
			$collateral_sum=0;
			while($row2=mysqli_fetch_array($results2))
			{
				$text .= '  <tr>';
				$text .= '  <td>'.$row2["MPN"].'</td>';
				$text .= '  <td>'.number_format($row2["amount"], 2).'</td>';
				$text .= '  <td>'.$row2["title"];
				if($row2["collateral"]>0) 
				{
					$text .= '<br />zzgl. '.$row2["collateral"].' Euro Altteilpfand';
					$collateral_count=$collateral_count+$row2["amount"];
					$collateral_sum=$collateral_sum+(number_format($row2["collateral"]*$row2["amount"], 2));
				}
				$text .= '  </td>';
				$text .= '  <td>'.number_format($row2["price"], 2).' Euro</td>';
				$price=$row2["amount"]*$row2["price"];
				$total+=$price;
				$text .= '  <td>'.number_format($price, 2).' Euro</td>';
				if ($partner_id>0) 
			//	if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				{
					$text .= '  <td><span style="float:right;">'.itemstatus_rc($row2["id_item"], 1, $row2["amount"]).'</span></td>';					
				}
				else
				{
					$text .= '  <td><span style="float:right;">'.itemstatus($row2["id_item"], 1, $row2["amount"]).'</span></td>';
				}
				$text .= '  </tr>';
			}
			
			//Nettowarenwert
			$totalpos = $total;

			//Altteilpfand
			if($collateral_count>0 and $collateral_sum>0)
			{
				$text .= '  <tr>';
				$text .= '    <td colspan="4">';
				$text .= ' Altteilpfand für '.$collateral_count.' Artikel';
				if ( $collateral_count==1 ) $text .= '<br />Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.';
				else $text .= '<br />Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.';
				$text .= '<br />Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind.';
				$text .= '   <td colspan="2">'.number_format($collateral_sum, 2).' Euro</td>';
				$text .= '  </tr>';
				$total+=$collateral_sum;
			}

			//Versandkosten
			$text .= '  <tr>';
			$text .= '    <td colspan="4">'.$row["shipping_details"].'</td><td colspan="2">'.number_format($row["shipping_costs"], 2).' Euro</td>';
			$text .= '  </tr>';
			$total+=$row["shipping_costs"];
					
			//Gesamt Netto
			if ($gewerblich)
			{
				{
					$discount = 0;
					$text .= '  <tr>';
					$text .= '    <td colspan="4">Gesamtpreis Netto</td><td colspan="2">'.number_format($total, 2).' Euro</td>';
					$text .= '  </tr>';
					$ust=(UST/100)*$total;
					$total=((100+UST)/100)*$total;
				}
			}
			else 
			{
				$discount = 0;
				$ust=$total/(100+UST)*UST;
				$totalpos/=((100+UST)/100);
			}
			$text .= '  <tr>';
			$text .= '    <td colspan="4">';
			if ($gewerblich)
			{
				$text .= 'zzgl. '.UST.'% gesetzliche';
			}
			else
			{
				$text .= 'darin enthalten, '.UST.'%';  
			}
			$text .= ' Umsatzsteuer</td><td colspan="2">'.number_format($ust, 2).' Euro</td>';
			$text .= '  </tr>';
		
			//Gesamt Brutto
			$text .= '  <tr>';
			$text .= '    <td colspan="4"><b>Gesamtpreis Brutto</b></td><td colspan="2"><b>'.number_format($total, 2).' Euro</b></td>';
			$text .= '  </tr>';
			$text .= '</table>';*/
					
			//Versandart hervorgehoben für Borkheide
			$pos = strpos($row["shipping_details"], ", ")+2;
			$text3 = '<p>';
			$text3 .= '<b>'.substr($row["shipping_details"], $pos).'</b><br>';
			$text3 .= '<b>'.number_format($totalpos, 2).' € Netto-Warenwert</b><br>';
			if ($gewerblich)
			{
				$text3 .= '<b>Gewerbekunde</b>';
				if($discount>0)
				{
				$text3 .= '<br>';
				$text3 .= '<b>ACHTUNG 4% (€ '.number_format($discount, 2).') ONLINE-RABATT BEACHTEN!!!</b>';					
				}
			}
			else
			{
				$text3 .= '<b>Privatkunde</b>';
			}
			$text3 .= '</p>';
			
			if ($row["ordernr"]!="")
			{
				$text3 .= '<p><b>Bitte eigene OrderNr. des Kunden übernehmen!!!</b></p>';
			}
			if ($row["comment"]!="")
			{
				$text3 .= '<p><b>Bitte die Anmerkung des Kunden zur Bestellung beachten!!!</b></p>';
			}
			
			$text4="";

			if ($row["payments_type_id"]==2) {
				
				//MAPCO ONLINE PRIVATKUNDEN
				if ($row["shop_id"]==8)
				{
					//Hinweis für Vorkasse
					$text4 .= '<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td colspan="2">';
					$text4 .= '<br /><br />';
					$text4 .= t("Bei Zahlungsart Vorkasse bitte beachten").':<br />';
				//	$text4 .= t("Dies ist nur eine Bestellbestaetigung. Zahlung erst nach Erhalt der Rechnung durchfuehren!").'</p>';
					$text4 .= '</td></tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Name der Bank").'</p></td>';
					$text4 .= '<td><p>Berliner Volksbank eG</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Bankleitzahl").'</p></td>';
					$text4 .= '<td><p>100 900 00</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Kontonummer").'</p></td>';
					$text4 .= '<td><p>5 152 043 041</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>Swift-Code</p></td>';
					$text4 .= '<td><p>BEVO DE BB</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>IBAN</p></td>';
					$text4 .= '<td><p>DE 84 1009 0000 5152 0430 41</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Verwendungszweck").'</p></td>';			
					$text4 .= '<td><p>'.$row["id_order"].' / '.$row["bill_lastname"].', '.$row["bill_firstname"].'</p></td>';
					$text4 .= '</tr>';
					$text4 .= '</table>';
				}
				else
				{
				// MAPCO ONLINE GROßKUNDEN
					//Hinweis für Vorkasse
					$text4 .= '<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td colspan="2">';
					$text4 .= '<br /><br />';
					$text4 .= '<p>'.t("Bei Zahlungsart Vorkasse bitte beachten").':<br />';
					$text4 .= t("Dies ist nur eine Bestellbestaetigung. Zahlung erst nach Erhalt der Rechnung durchfuehren!").'</p>';
					$text4 .= '</td></tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Name der Bank").'</p></td>';
					$text4 .= '<td><p>Berliner Volksbank eG</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Bankleitzahl").'</p></td>';
					$text4 .= '<td><p>100 900 00</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Kontonummer").'</p></td>';
					$text4 .= '<td><p>5 152 043 009</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>Swift-Code</p></td>';
					$text4 .= '<td><p>BEVO DE BB</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>IBAN</p></td>';
					$text4 .= '<td><p>DE 75 1009 0000 5152 0430 09</p></td>';
					$text4 .= '</tr>';
					$text4 .= '<tr>';
					$text4 .= '<td><p>'.t("Verwendungszweck").'</p></td>';			
					$text4 .= '<td><p>'.$row["id_order"].' / '.$row["bill_lastname"].', '.$row["bill_firstname"].'</p></td>';
					$text4 .= '</tr>';
					$text4 .= '</table>';
				}
			}

			//Widerrufsbelehrung für den Kunden
			//if ($row["shop_id"]==8) $article=32588; else $article=28291;
			$article=28291;
			$results4=q("SELECT article FROM cms_articles WHERE id_article=".$article.";", $dbweb, __FILE__, __LINE__);
			$row4=mysqli_fetch_array($results4);
			$text5  = '<br /><br /><br />';
			$text5 .= $row4["article"];
			
			/*
			BEI WIEDERVERWENDUNG BEACHTEN: FUNCTION wird durch SHOP_CART & PAYPALCHECKSTATUS verwendet
			if ($gewerblich)
			{
				if ($_SESSION["rcid"]==16 and time()>mktime(0,0,0,8,1,2012) and time()<mktime(0,0,0,10,1,2012))
				{
					if($_SESSION["id_shipping"]==8 or $_SESSION["id_shipping"]==50) $special=10;
					else $special=5;
					$text .= '<br /><p><b>Bei dieser Bestellung wurden '.$special.'% Sonderrabatt berücksichtigt!</b></p>';
				}
			}
			*/
			
//***********************************************email template*************************************************************
			switch($shop_id)
			{
				case 1:
					$mail_article=32857; //Bestellbestätigung MAPCO Großkunden
					break;
				case 8:
					$mail_article=32861; //Bestellbestätigung MAPCO Privatkunden
					break;
				case 9:
					$mail_article=32870; //Bestellbestätigung RC Neubrandenburg
					break;
				case 10:
					$mail_article=32862; //Bestellbestätigung RC Leipzig
					break;
				case 11:
					$mail_article=32863; //Bestellbestätigung RC Sömmerda
					break;
				case 12:
					$mail_article=32864; //Bestellbestätigung RC Dresden
					break;
				case 13:
					$mail_article=32865; //Bestellbestätigung RC Magdeburg
					break;
				case 14:
					$mail_article=32868; //Bestellbestätigung RC Frankfurt/Main
					break;
				case 15:
					$mail_article=32866; //Bestellbestätigung RC Berlin
					break;
				case 16:
					$mail_article=32867; //Bestellbestätigung RC Essen
					break;
				case 17:
					$mail_article=32869; //Bestellbestätigung RC Rom
					break;									
			}
			$results5=q("SELECT article FROM cms_articles WHERE id_article=".$mail_article.";", $dbweb, __FILE__, __LINE__);
			$row5=mysqli_fetch_array($results5);
			$text6 = $row5["article"];
			$mail_label="MAPCO-Shop";
			
			$mail_article2=32858; // Bestellmeldung Borkheide/RC
			$results6=q("SELECT article FROM cms_articles WHERE id_article=".$mail_article2.";", $dbweb, __FILE__, __LINE__);
			$row6=mysqli_fetch_array($results6);
			$text11 = $row6["article"];
//*************************************************************************************************************************
		$customer_id="5";
		//Mail zum Kunden
			if ($customer_id=="358" or $customer_id=="22044" or $customer_id=="28625")
			{
				if ($mailto_buyer) 
				{
					send_html_mail("developer@mapco.de", "Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
					send_html_mail_ma("developer@mapco.de", " 2Neue Bestellung im Online-Shop", $text1.$text.$text3);
				}
			}
			else
			{
				if ($mailto_buyer) 
				{
					$text6=str_replace("<!-- PAYCHECK -->", $text10, $text6);
					$text6=str_replace("<!-- ORDER-ID -->", $row["id_order"], $text6);
					$text6=str_replace("<!-- ADDRESS -->", $text7, $text6);
					$text6=str_replace("<!-- TABLE -->", $text8, $text6);
					$text6=str_replace("<!-- ADPAY -->", $text4, $text6);
					$text6=str_replace("<!-- CANCEL -->", $text5, $text6);
					
					/*if ($row["shop_id"]==8)
					{
						send_html_mail2($usermail, "MAPCO-Shop <shop@mapco.de>", "Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
					}
					else
					{
						send_html_mail2($usermail, "MAPCO-Shop <bestellung@mapco-shop.de>", "Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
					//	send_html_mail("nputzing@mapco.de", "2 Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
					}*/
					
					
					if($shop_id<2 || $shop_id>7)
					{
						//send_html_mail2($usermail, $mail_label." <".$mail.">", "Eingangsbestätigung zu Ihrer Bestellung", $text6);
						//send_html_mail2("mwosgien@mapco.de", $mail_label." <".$mail.">", "Eingangsbestätigung zu Ihrer Bestellung", $text6);
					}
				}
//***********************************Borkheide/RCs************************************************************************************
				$text13=str_replace("<!-- DATA -->", $text1, $text11);
				$text13=str_replace("<!-- ADDRESS -->", $text7, $text13);
				$text13=str_replace("<!-- TABLE -->", $text8, $text13);
				$text13=str_replace("<!-- SHIPPINGTYPE -->", $text3, $text13);
				//Rom
				$text14=str_replace("<!-- DATA -->", $text1, $text11);
				$text14=str_replace("<!-- ADDRESS -->", $text7, $text14);
				$text14=str_replace("<!-- TABLE -->", $text9, $text14);
				$text14=str_replace("<!-- SHIPPINGTYPE -->", $text3, $text14);
				
				if ($shop_id>8)
				{
					if ($mailto_agent) 
					{
						if((strpos($row["shipping_details"], 'Lieferservice')!==false or strpos($row["shipping_details"], 'Selbstabholung')!==false) and ($shop_id > 8 and $shop_id < 17))
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung wird durch das RC '.$city.' bearbeitet!</b><br /><br />';
							$text12=str_replace("<!-- INFO -->", $info, $text13);
							//send_html_mail_ma($row3["MAIL"], "Neue Bestellung im Online-Shop ".$row["CITY"], $text1.$text.$text3);
							//send_html_mail_ma("bestellung@mapco-shop.de", "Kopie - Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
							
							//send_html_mail_ma("bestellung@mapco-shop.de", "Kopie - Neue Bestellung im Online-Shop ".$city, $text12);
							if($order_mail==1)
							{
								//send_html_mail_ma($mail, "Neue Bestellung im Online-Shop ".$city, $text13);
								//send_html_mail_ma("mwosgien@mapco.de", "Kopie - Neue Bestellung im Online-Shop ".$city, $text12);
							}
						}
						elseif ($shop_id == 17) // Rom
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung für das RC '.$city.' erfassen!</b><br /><br />';
							$text12=str_replace("<!-- INFO -->", $info, $text13);
							//send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
							//send_html_mail_ma($row3["MAIL"], "Neue Bestellung im Online-Shop ".$row3["CITY"], $text1.$text.$text3);
							
							//send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop ".$city, $text12);
							if($order_mail==1)
							{
								//send_html_mail_ma($mail, "Neue Bestellung im Online-Shop ".$city, $text14);
								send_html_mail_ma("mwosgien@mapco.de", "Neue Bestellung im Online-Shop ".$city, $text12);
								send_html_mail_ma("mwosgien@mapco.de", "Neue Bestellung im Online-Shop ".$city, $text14);
							}
						}
						else
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung wird von Borkheide bearbeitet!</b><br /><br />';
							$text12=str_replace("<!-- INFO -->", $info, $text13);
							//send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop", $text1.$text.$text3);
							//send_html_mail_ma($row3["MAIL"], "Kopie - Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
							
							//send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop", $text13);
							if($order_mail==1)
							{
								//send_html_mail_ma($mail, "Kopie - Neue Bestellung im Online-Shop ".$city, $text12);
								//send_html_mail_ma("mwosgien@mapco.de", "Neue Bestellung im Online-Shop", $text13);
							}
						}
					}
				}
				else
				{	
					if ($mailto_agent) 
					{
						if ($shop_id==2)
						{
						}
						elseif ($shop_id==7)
						{
						}
						elseif ($shop_id==8)
						{
							//Mail zu ebay@mapco.de
							//send_html_mail_ma("shop@mapco.de", "Neue Bestellung im Online-Shop", $text1.$text.$text3);
							//send_html_mail("nputzing@mapco.de", "Neue Bestellung im Online-Shop", $text1.$text);
							
							if($order_mail==1)
							{
								//send_html_mail_ma($mail, "Neue Bestellung im Online-Shop", $text13);
								//send_html_mail("nputzing@mapco.de", "Neue Bestellung im Online-Shop", $text13);
								//send_html_mail_ma("mwosgien@mapco.de", "Neue Bestellung im Online-Shop8", $text13);
							}
						}
						else
						{
							//Mail nach Borkheide
							//send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop", $text1.$text.$text3);
							
							//send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop", $text13);
							//send_html_mail_ma("mwosgien@mapco.de", "Neue Bestellung im Online-Shop", $text13);
						}
					}
				}
//************************************************************************************************************************************
				/*if ($partner_id>0)
				//if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				{
					if ($mailto_agent) 
					{
						//$results3=q("SELECT * FROM cms_locations where PID='".$partner_id."';", $dbweb, __FILE__, __LINE__);
						//$results3=q("SELECT * FROM cms_locations where RC_NR=".$_SESSION["rcid"].";", $dbweb, __FILE__, __LINE__);
						$row3=mysqli_fetch_array($results3);
						if((strpos($row["shipping_details"], 'Lieferservice')!==false or strpos($row["shipping_details"], 'Selbstabholung')!==false) and $row3["RC_NR"] < 35)
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung wird durch das RC '.$row3["CITY"].' bearbeitet!</b><br /><br />';
							send_html_mail_ma($row3["MAIL"], "Neue Bestellung im Online-Shop ".$row["CITY"], $text1.$text.$text3);
							send_html_mail_ma("bestellung@mapco-shop.de", "Kopie - Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
						}
						elseif ($row3["RC_NR"] > 35) // Rom
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung für das RC '.$row3["CITY"].' erfassen!</b><br /><br />';
							send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
							send_html_mail_ma($row3["MAIL"], "Neue Bestellung im Online-Shop ".$row3["CITY"], $text1.$text.$text3);
						}
						else
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung wird von Borkheide bearbeitet!</b><br /><br />';
							send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop", $text1.$text.$text3);
							send_html_mail_ma($row3["MAIL"], "Kopie - Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
						}
					}
				}
				else
				{	
					if ($mailto_agent) 
					{
						if ($row["shop_id"]==8)
						{
							//Mail zu ebay@mapco.de
							send_html_mail_ma("shop@mapco.de", "Neue Bestellung im Online-Shop", $text1.$text.$text3);
							send_html_mail("nputzing@mapco.de", "Neue Bestellung im Online-Shop", $text1.$text);
						}
						else
						{
							//Mail nach Borkheide
							send_html_mail_ma("bestellung@mapco-shop.de", "Neue Bestellung im Online-Shop", $text1.$text.$text3);
						//	send_html_mail("nputzing@mapco.de", "Neue Bestellung im Online-Shop", $text1.$text);
						}
					}
				}*/
			}
			
			//MAIL AN FRAU FRANKE
			/*if ($mailto_agent && $gewerblich && $onlinePayment)
			{
				send_html_mail_ma("nputzing@mapco.de", "Bestellung im Online-Shop - Gewerbekunde mit Online-Zahlung", $text1.$text.$text3);
				send_html_mail_ma("kfranke@mapco.de", "Bestellung im Online-Shop - Gewerbekunde mit Online-Zahlung", $text1.$text.$text3);
			}*/
//*****************************************template*********************************************************
			if ($mailto_agent && $gewerblich && $onlinePayment)
			{
				//send_html_mail_ma("nputzing@mapco.de", "Bestellung im Online-Shop - Gewerbekunde mit Online-Zahlung", $text13);
				//send_html_mail_ma("kfranke@mapco.de", "Bestellung im Online-Shop - Gewerbekunde mit Online-Zahlung", $text13);
				//send_html_mail_ma("mwosgien@mapco.de", "Bestellung im Online-Shop - Gewerbekunde mit Online-Zahlung", $text13);
			}
//**********************************************************************************************************			
			//change order status
		//	$results2=q("UPDATE shop_orders SET status_id=1 WHERE id_order=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		}
	}
	 mail_order2(1753096, false, true);
?>