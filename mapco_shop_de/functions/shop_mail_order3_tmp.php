<?php
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
	
			$results=q("SELECT * FROM shop_orders3 WHERE id_order=".$id_order.";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			
			$customer_id=$row["customer_id"];
			$partner_id=$row["partner_id"];

			if($row["ship_adr_id"]>0) 
			{
				$results3=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$row["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
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
	
			//Daten für Borkheide
			$results2=q("SELECT * FROM cms_users WHERE id_user=".$row["customer_id"].";", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$usermail=$row2["usermail"];
			$text1  = '	Bestellzeit: '.date("d.m.Y H:i", $row["firstmod"]);
			$text1 .= '	<br>Kundennummer: '.$row2["username"];
			$text1 .= '	<br>Online-Shop-Bestellnummer: '.$row["id_order"];
			$text1 .= '	<br>Eigene Bestellnummer: '.$row["ordernr"];
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
			if ($row["Payments_TransactionID"]!=""){
				$onlinePayment=true;
				if ($row["Payments_TransactionState"]=="Completed" || $row["Payments_TransactionState"]=="OK" || $row["Payments_TransactionState"]=="AUTHORIZED") 
				{
					$text2  = '<p>Vielen Dank für Ihre Bestellung!</p>';
					$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
					$text2 .= '<p>Online-Shop-Bestellnummer: '.$row["id_order"].'</p>';
				}
				elseif ($row["Payments_TransactionState"]=="Pending")
				{
					$paypalState="Pending";
					
					$text2  = '<p>Vielen Dank für Ihre Bestellung!</p>';
					$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Der Versand erfolgt nachdem PayPal uns den Erhalt Ihrer Zahlung bestätigt hat. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
					$text2 .= '<p>Online-Shop-Bestellnummer: '.$row["id_order"].'</p>';
				}
			}
			//ZAHLUNG per Überweisung || Rechnung
			else {
				$text2  = '<p>Vielen Dank für Ihre Bestellung!</p>';
				$text2 .= '<p>Ihre Order ist bei uns erfolgreich eingegangen und wird umgehend bearbeitet. Der Versand erfolgt nach dem Eingang Ihrer Zahlung. Nachfolgend finden Sie noch einmal alle zugehörigen Angaben im Überblick.</p>';
				$text2 .= '<p>Bestellnummer: '.$row["id_order"].'</p>';
			}

			//bill address
			$text = '<p><b>Rechnungsanschrift:</b><br>';
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
			}
			
			//bill
			$text .= '<table border="1" cellpadding="4">';
			$text .= '  <tr><th colspan="6">Bestellung</th></tr>';
			$text .= '  <tr>';
			$text .= '    <td>Artikel-Nr.</td>';
			$text .= '    <td>Menge</td>';
			$text .= '    <td>Bezeichnung</td>';
			$text .= '    <td>EK</td>';
			$text .= '    <td>Gesamt</td>';
			$text .= '    <td></td>';
			$text .= '  </tr>';
			
			$results2=q("SELECT a.price, a.amount, c.title, b.MPN, b.id_item FROM shop_orders_items3 AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=".$row["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
			$total=0;
			while($row2=mysqli_fetch_array($results2))
			{
				$text .= '  <tr>';
				$text .= '  <td>'.$row2["MPN"].'</td>';
				$text .= '  <td>'.number_format($row2["amount"], 2).'</td>';
				$text .= '  <td>'.$row2["title"].'</td>';
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
			
			//Versandkosten
			$text .= '  <tr>';
			$text .= '    <td colspan="4">'.$row["shipping_details"].'</td><td colspan="2">'.number_format($row["shipping_costs"], 2).' Euro</td>';
			$text .= '  </tr>';
			$totalpos = $total;
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
			$text .= '</table>';
					
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

			if (!$onlinePayment) {
			
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

			//Widerrufsbelehrung für den Kunden
			$results4=q("SELECT article FROM cms_articles WHERE id_article=28291;", $dbweb, __FILE__, __LINE__);
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
			
		//Mail zum Kunden
			if ($customer_id=="358" or $customer_id=="22044" or $customer_id=="28625")
			{
				if ($mailto_buyer) 
				{
				//	send_html_mail("developer@mapco.de", "Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
					send_html_mail_ma("developer@mapco.de", " 2Neue Bestellung im Online-Shop", $text1.$text.$text3);
				}
			}
			else
			{
				if ($mailto_buyer) 
				{
				//	send_html_mail($usermail, "Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
					send_html_mail("nputzing@mapco.de", "2 Eingangsbestätigung zu Ihrer Bestellung", $text2.$text.$text4.$text5);
				}
				
				if ($partner_id>0)
				//if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				{
					if ($mailto_agent) 
					{
						$results3=q("SELECT * FROM cms_locations where PID='".$partner_id."';", $dbweb, __FILE__, __LINE__);
						//$results3=q("SELECT * FROM cms_locations where RC_NR=".$_SESSION["rcid"].";", $dbweb, __FILE__, __LINE__);
						$row3=mysqli_fetch_array($results3);
						if(strpos($row["shipping_details"], 'Lieferservice')!==false or strpos($row["shipping_details"], 'Selbstabholung')!==false)
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung wird durch das RC '.$row3["CITY"].' bearbeitet!</b><br /><br />';
							send_html_mail_ma($row3["MAIL"], "KORREKTUR: Neue Bestellung im Online-Shop ".$row["CITY"], $text1.$text.$text3);
							send_html_mail_ma("bestellung@mapco-shop.de", "KORREKTUR: Kopie - Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
						}
						else
						{
							$info='<b style="color:red; font-size:14px;">Diese Bestellung wird von Borkheide bearbeitet!</b><br /><br />';
							send_html_mail_ma("bestellung@mapco-shop.de", "KORREKTUR: Neue Bestellung im Online-Shop", $text1.$text.$text3);
							send_html_mail_ma($row3["MAIL"], "KORREKTUR: Kopie - Neue Bestellung im Online-Shop ".$row3["CITY"], $info.$text1.$text.$text3);
						}
					}
				}
				else
				{	
					if ($mailto_agent) 
					{
						//Mail nach Borkheide
						send_html_mail_ma("bestellung@mapco-shop.de", "KORREKTUR:Neue Bestellung im Online-Shop", $text1.$text.$text3);
						send_html_mail("nputzing@mapco.de", "KORREKTUR:Neue Bestellung im Online-Shop", $text1.$text);
					}
				}
			}
			
			//change order status
			//$results2=q("UPDATE shop_orders SET status_id=1 WHERE id_order=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		}
	}
?>