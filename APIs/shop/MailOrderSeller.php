<?php

	include("../functions/shop_get_prices.php");
	include("../functions/shop_itemstatus.php");	
	include("../functions/mapco_gewerblich.php");
	include("../functions/cms_t.php");

	check_man_params(array("order_id"	=> "numeric"));
	
	//order data
	$res_order=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
	$row_order=mysqli_fetch_array($res_order);
	
	$shop_id=$row_order["shop_id"];
	$status_id=$row_order["status_id"];
	$payments_type_id=$row_order["payments_type_id"];
	$bill_adr_id=$row_order["bill_adr_id"];
	$ship_adr_id=$row_order["ship_adr_id"];
	$customer_id=$row_order["customer_id"];
	$partner_id=$row_order["partner_id"];
	$shipping_details=$row_order["shipping_details"];
	
	$gewerblich=gewerblich($customer_id);
	
	//shop data
	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop=".$shop_id.";", $dbshop, __FILE__, __LINE__);
	$row_shop=mysqli_fetch_array($res_shop);
	$shop_mail=$row_shop["mail"];
	$customer_mail_article_id=$row_shop["customer_mail_article_id"];
	$city=str_replace("Online-Shop ", "", $row_shop["description"]);
	$order_mail=$row_shop["order_mail"];
	
	//user_data
	$res_user=q("SELECT * FROM cms_users WHERE id_user=".$customer_id.";", $dbweb, __FILE__, __LINE__);
	$row_user=mysqli_fetch_array($res_user);
	$usermail=$row_user["usermail"];
	if($usermail=='') $usermail=$row_order["usermail"];
	$username=$row_user["username"];
	
	//**************************************<!-- DATA -->**********************************************
	$text_data_01  = '	'.t("Bestellzeit").': '.date("d.m.Y H:i", $row_order["firstmod"]);
	$text_data_01 .= '	<br />'.t("Kundennummer").': '.$username;
	$text_data_01 .= '	<br />'.t("Online-Shop-Bestellnummer").': '.$row_order["id_order"];
	if ($row_order["Payments_TransactionID"]!="") $text_data_01 .= ' <br /><b>PAYMENTS-TRANSACTION ID: </b>'.$row_order["Payments_TransactionID"];
	if($row_order["ordernr"]!="") $text_data_01 .= '	<br />'.t("Eigene Bestellnummer").': '.$row_order["ordernr"];
	if($row_order["comment"]!="") $text_data_01 .= '	<br /><br>'.t("Anmerkung").':<br />'.nl2br($row_order["comment"]);
	$text_data_01 .= '</p>';
	$text_data_01 .= '<p><b>'.t("Kontaktdaten").':</b><br>';
	$text_data_01 .= 'E-Mail: '.$usermail;
	if ($row_order["userphone"]!="") $text_data_01 .= '<br>'.t("Telefon").':'.$row_order["userphone"];
	if ($row_order["userfax"]!="") $text_data_01 .= '<br>'.t("Telefax").':'.$row_order["userfax"];
	if ($row_order["usermobile"]!="") $text_data_01 .= '<br>'.t("Handy").':'.$row_order["usermobile"];
	$text_data_01 .= '</p>';
	
	if($shop_id==17)
	{
		$text_data_02=' <table>';
		$text_data_02.='	<tr>';
		$text_data_02.='		<td style="padding-right: 10px">'.t("Bestellzeit").': '.date("d.m.Y H:i", $row_order["firstmod"]).'<br />'.t("Kundennummer").': '.$username.'<br />'.t("Online-Shop-Bestellnummer").': '.$row_order["id_order"].'</td>';
		$text_data_02.='		<td><img src="'.PATH.'templates/shop/images/header_logo.jpg"></td>';
		$text_data_02.='	</tr>';
		$text_data_02.='</table><br /><br />';
	}
	//*************************************************************************************************
	
	//**************************************<!-- ADDRESS -->*******************************************
	$res_bill_adr=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$bill_adr_id.";", $dbshop, __FILE__, __LINE__);
	$row_bill_adr=mysqli_fetch_array($res_bill_adr);
	$bill_gender=t("Herr");
	if($row_bill_adr["gender"]==1) $bill_gender=t("Frau");
	if($ship_adr_id>0 and $ship_adr_id!=$bill_adr_id)
	{
		$res_ship_adr=q("SELECT *FROM shop_bill_adr WHERE adr_id=".$ship_adr_id.";", $dbshop, __FILE__, __LINE__);
		$row_ship_adr=mysqli_fetch_array($res_ship_adr);
		$ship_gender=t("Herr");
		if($row_ship_adr["gender"]==1) $ship_gender=t("Frau");
	}
	
	$adr_title=t("Rechnungsanschrift");
	if($bill_adr_id==$ship_adr_id) $adr_title=t("Rechnungs- und Lieferanschrift");
		
	$text_address_01='<p><b>'.$adr_title.':</b><br>';
	if($row_bill_adr["company"]!="") $text_address_01.=$row_bill_adr["company"].'<br>';
	$text_address_01.=$bill_gender.' '.$row_bill_adr["title"].'<br>';
	$text_address_01.=$row_bill_adr["firstname"].' '.$row_bill_adr["lastname"].'<br>';
	$text_address_01.=$row_bill_adr["street"].' '.$row_bill_adr["number"].'<br>';
	$text_address_01.=$row_bill_adr["zip"].' '.$row_bill_adr["city"].'<br>';
	if ($row_bill_adr["additional"]!="") $text_address_01 .= $row_bill_adr["additional"].'<br>';
	$text_address_01.=$row_bill_adr["country"].'<br>';
	$text_address_01.='</p>';
	if($ship_adr_id>0 and $ship_adr_id!=$bill_adr_id)
	{
		$text_address_01.='<p><b>'.t("Lieferanschrift").':</b><br>';
		if($row_ship_adr["company"]!="") $text_address_01.=$row_ship_adr["company"].'<br>';
		$text_address_01.=$ship_gender.' '.$row_ship_adr["title"].'<br>';
		$text_address_01.=$row_ship_adr["firstname"].' '.$row_ship_adr["lastname"].'<br>';
		$text_address_01.=$row_ship_adr["street"].' '.$row_ship_adr["number"].'<br>';
		$text_address_01.=$row_ship_adr["zip"].' '.$row_ship_adr["city"].'<br>';
		if ($row_ship_adr["additional"]!="") $text_address_01 .= $row_ship_adr["additional"].'<br>';
		$text_address_01.=$row_ship_adr["country"].'<br>';
		$text_address_01.='</p>';
	}
	
	if($shop_id==17)
	{
		$adr_title=t("Lieferanschrift");
		$text_address_02='<p><b>'.$adr_title.':</b><br />';
		if($ship_adr_id==0)
		{
			$text_address_02='<table>';
			$text_address_02.='<tr><td><b>'.$adr_title.':</b></td><td></td></tr>';
			if($row_bill_adr["company"]!="") $text_address_02.='<tr><td>'.t("Firma").':</td><td>'.$row_bill_adr["company"].'</td></tr>';
			if($row_bill_adr["title"]!='')
				$text_address_02.='<tr><td>'.t("Name").':</td><td>'.$row_bill_adr["title"].' '.$row_bill_adr["firstname"].' '.$row_bill_adr["lastname"].'</td></tr>';
			else
				$text_address_02.='<tr>'.t("Name").':<td></td><td>'.$row_bill_adr["firstname"].' '.$row_bill_adr["lastname"].'</td></tr>';
			$text_address_02.='<tr><td>'.t("Adresse").':</td><td>'.$row_bill_adr["street"].' '.$row_bill_adr["number"].'</td></tr>';
			if ($row_order["userphone"]!="") $text_address_02.='<tr><td>'.t("Telefon").':</td><td>'.$row_order["userphone"].'</td></tr>';
			$text_address_02.='<tr><td>'.t("PLZ-Ort-Land").':</td><td>'.$row_bill_adr["zip"].'-'.$row_bill_adr["city"].'-'.$row_bill_adr["country"].'</td></tr>';
			$text_address_02.='<tr><td>'.t("Email").':</td><td>'.$usermail.'</td></tr>';
			$text_address_02.='</table>';
		}
		else
		{
			$text_address_02='<table>';
			$text_address_02.='<tr><td><b>'.$adr_title.':</b></td><td></td></tr>';
			if($row_ship_adr["company"]!="") $text_address_02.='<tr><td>'.t("Firma").':</td><td>'.$row_ship_adr["company"].'</td></tr>';
			if($row_ship_adr["title"]!='')
				$text_address_02.='<tr><td>'.t("Name").':</td><td>'.$row_ship_adr["title"].' '.$row_ship_adr["firstname"].' '.$row_ship_adr["lastname"].'</td></tr>';
			else
				$text_address_02.='<tr>'.t("Name").':<td></td><td>'.$row_ship_adr["firstname"].' '.$row_ship_adr["lastname"].'</td></tr>';
			$text_address_02.='<tr><td>'.t("Adresse").':</td><td>'.$row_ship_adr["street"].' '.$row_ship_adr["number"].'</td></tr>';
			if ($row_order["userphone"]!="") $text_address_02.='<tr><td>'.t("Telefon").':</td><td>'.$row_order["userphone"].'</td></tr>';
			$text_address_02.='<tr><td>'.t("PLZ-Ort-Land").':</td><td>'.$row_ship_adr["zip"].'-'.$row_ship_adr["city"].'-'.$row_ship_adr["country"].'</td></tr>';
			$text_address_02.='<tr><td>'.t("Email").':</td><td>'.$usermail.'</td></tr>';
			$text_address_02.='</table>';
		}
	}
	//*************************************************************************************************
	
	//**************************************<!-- TABLE -->*********************************************
	$text_table_01 = '<table border="1" cellpadding="4">';
	$text_table_01 .= '  <tr><th colspan="6">'.t("Bestellung").'</th></tr>';
	$text_table_01 .= '  <tr>';
	$text_table_01 .= '    <td>'.t("Artikel-Nr.").'</td>';
	$text_table_01 .= '    <td>'.t("Menge").'</td>';
	$text_table_01 .= '    <td>'.t("Bezeichnung").'</td>';
	$text_table_01 .= '    <td>'.t("EK").'</td>';
	$text_table_01 .= '    <td>'.t("Gesamt").'</td>';
	$text_table_01 .= '    <td></td>';
	$text_table_01 .= '  </tr>';
	
	$results2=q("SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=".$row_order["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
	$total=0;
	$totalpos=0;
	$collateral_count=0;
	$collateral_sum=0;
	while($row2=mysqli_fetch_array($results2))
	{
		//Barcodes erzeugen
		$item_id_barcode=$row2["id_item"];
		$post_data=array();
		$post_data["API"]="shop";
		$post_data["APIRequest"]="BarcodeCreate";
		$post_data["item_id"]=$item_id_barcode;
		$response=soa2($post_data, __FILE__, __LINE__);
		
		$text_table_01 .= '  <tr>';
		$text_table_01 .= '  <td>'.$row2["MPN"].'</td>';
		$text_table_01 .= '  <td>'.number_format($row2["amount"], 0).'</td>';
		$text_table_01 .= '  <td>'.$row2["title"];
		if($row2["collateral"]>0) 
		{
			$text_table_01 .= '<br />zzgl. '.$row2["collateral"].' € '.t("Altteilpfand").'';
			$collateral_count=$collateral_count+$row2["amount"];
			$collateral_sum=$collateral_sum+(number_format($row2["collateral"]*$row2["amount"], 2));
		}
		$text_table_01 .= '  </td>';
		$text_table_01 .= '  <td>'.number_format($row2["price"], 2).' €</td>';
		$price=$row2["amount"]*$row2["price"];
		$total+=$price;
		$totalpos+=($row2["amount"]*$row2["netto"]);
		$text_table_01 .= '  <td>'.number_format($price, 2).' €</td>';
		//if ($partner_id>0) 
//		if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
		if($shop_id>8 and $shop_id<17)
		{
			$text_table_01 .= '  <td><span style="float:right;">'.itemstatus_rc($row2["id_item"], 1, $row2["amount"]).'</span></td>';					
		}
		else
		{
			$text_table_01 .= '  <td><span style="float:right;">'.itemstatus($row2["id_item"], 1, $row2["amount"]).'</span></td>';
		}
		$text_table_01 .= '  </tr>';
	}
	
	//Altteilpfand
	if($collateral_count>0 and $collateral_sum>0)
	{
		$text_table_01 .= '  <tr>';
		$text_table_01 .= '    <td colspan="4">';
		$text_table_01 .= ' '.t("Altteilpfand für").' '.$collateral_count.' '.t("Artikel").'';
		if ( $collateral_count==1 ) $text_table_01 .= '<br />'.t("Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.").'';
		else $text_table_01 .= '<br />'.t("Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.").'';
		$text_table_01 .= '<br />'.t("Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind.").'';
		$text_table_01 .= '   <td colspan="2">'.number_format($collateral_sum, 2).' €</td>';
		$text_table_01 .= '  </tr>';
		$total+=$collateral_sum;
	}

	//Versandkosten
	$text_table_01 .= '  <tr>';
	$text_table_01 .= '    <td colspan="4">'.$row_order["shipping_details"].'</td><td colspan="2">'.number_format($row_order["shipping_costs"], 2).' €</td>';
	$text_table_01 .= '  </tr>';
	$total+=$row_order["shipping_costs"];
			
	//Gesamt Netto
	if ($gewerblich)
	{
		$discount = 0;
		$text_table_01 .= '  <tr>';
		$text_table_01 .= '    <td colspan="4">'.t("Gesamtpreis Netto").'</td><td colspan="2">'.number_format($total, 2).' €</td>';
		$text_table_01 .= '  </tr>';
		$ust=(UST/100)*$total;
		$total=((100+UST)/100)*$total;
	}
	else 
	{
		$discount = 0;
		$ust=$total/(100+UST)*UST;
	}

	if($shop_id!=17)
	{
		$text_table_01 .= '  <tr>';
		$text_table_01 .= '    <td colspan="4">';
		if ($gewerblich)
		{
			$text_table_01 .= ''.t("zzgl.").' '.UST.'% '.t("gesetzliche").'';
		}
		else
		{
			$text_table_01 .= ''.t("darin enthalten").', '.UST.'%';  
		}
		$text_table_01 .= ' '.t("Umsatzsteuer").'</td><td colspan="2">'.number_format($ust, 2).' €</td>';
		$text_table_01 .= '  </tr>';
	
		//Gesamt Brutto
		$text_table_01 .= '  <tr>';
		$text_table_01 .= '    <td colspan="4"><b>'.t("Gesamtpreis Brutto").'</b></td><td colspan="2"><b>'.number_format($total, 2).' €</b></td>';
		$text_table_01 .= '  </tr>';
	}
	$text_table_01 .= '</table>';
	
	//ROM
	if($shop_id==17)
	{
		$text_table_02 = '<table border="1" cellpadding="4">';
		$text_table_02 .= '  <tr><th colspan="7">'.t("Bestellung").'</th></tr>';
		$text_table_02 .= '  <tr>';
		$text_table_02 .= '    <td>PLT</td>';
		$text_table_02 .= '    <td>ART</td>';
		$text_table_02 .= '    <td>PZ</td>';
		$text_table_02 .= '    <td>DESCRIZIONE</td>';
		$text_table_02 .= '    <td>BCD</td>';
		$text_table_02 .= '    <td>EK</td>';
		$text_table_02 .= '    <td>TOTALE</td>';
		//$text_table_02 .= '    <td></td>';
		$text_table_02 .= '  </tr>';
		
		$results2=q("SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item, b.EAN, d.pallet FROM shop_orders_items AS a, shop_items AS b, shop_items_it AS c LEFT JOIN mapco_gls_roma AS d ON ( c.id_item = d.item_id ) WHERE a.order_id=".$row_order["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item ORDER BY d.pallet;", $dbshop, __FILE__, __LINE__);
		$amountsum=0;
		$total=0;
		$collateral_count=0;
		$collateral_sum=0;
		while($row2=mysqli_fetch_array($results2))
		{
			//Barcodes erzeugen
			$item_id_barcode=$row2["id_item"];
			$post_data=array();
			$post_data["API"]="shop";
			$post_data["APIRequest"]="BarcodeCreate";
			$post_data["item_id"]=$item_id_barcode;
			$response=soa2($post_data, __FILE__, __LINE__);
			
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '  <td>'.$row2["pallet"].'</td>';
			$text_table_02 .= '  <td>'.$row2["MPN"].'</td>';
			$amountsum=$amountsum+$row2["amount"];
			$text_table_02 .= '  <td>'.number_format($row2["amount"], 0, ",", "").'</td>';
			$text_table_02 .= '  <td>'.$row2["title"];
			if($row2["collateral"]>0) 
			{
				$text_table_02 .= '<br />zzgl. '.$row2["collateral"].' '.t("€ Altteilpfand").'';
				$collateral_count=$collateral_count+$row2["amount"];
				$collateral_sum=$collateral_sum+(number_format($row2["collateral"]*$row2["amount"], 2, ",", ""));
			}
			$text_table_02 .= '  </td>';
			$text_table_02 .= '  <td><img src="'.PATH.'images/barcodes/'.$row2["EAN"].'.png"></td>';
			$text_table_02 .= '  <td>'.number_format($row2["price"], 2, ",", "").' €</td>';
			$price=$row2["amount"]*$row2["price"];
			$total+=$price;
			$text_table_02 .= '  <td>'.number_format($price, 2, ",", "").' €</td>';
/*			
			if ($partner_id>0) 
		//	if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
			{
				$text_table_02 .= '  <td><span style="float:right;">'.itemstatus_rc($row2["id_item"], 1, $row2["amount"]).'</span></td>';					
			}
			else
			{
				$text_table_02 .= '  <td><span style="float:right;">'.itemstatus($row2["id_item"], 1, $row2["amount"]).'</span></td>';
			}
*/
/*
			//Barcode
			$text_table_02 .= '  </tr>';
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '    <td colspan="3"></td>';
			$text_table_02 .= '    <td><img src="'.PATH.'images/barcodes/'.$row2["EAN"].'.png"></td>';
			$text_table_02 .= '    <td colspan="2"></td>';
			$text_table_02 .= '  </tr>';
*/			
		}
		
		//Altteilpfand
		if($collateral_count>0 and $collateral_sum>0)
		{
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '	   <td style="border-right: none"></td>';
			$text_table_02 .= '	   <td style="border-right: none; border-left: none;"></td>';
			$text_table_02 .= '	   <td style="border-left: none">'.number_format($amountsum, 0, ",", "").'</td>';
			$text_table_02 .= '    <td colspan="3">';
			$text_table_02 .= ' '.t("Altteilpfand für").' '.$collateral_count.' '.t("Artikel").'';
			if ( $collateral_count==1 ) $text_table_02 .= '<br />'.t("Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.").'';
			else $text_table_02 .= '<br />'.t("Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.").'';
			$text_table_02 .= '<br />'.t("Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind.").'';
			$text_table_02 .= '   <td>'.number_format($collateral_sum, 2, ",", "").' €</td>';
			$text_table_02 .= '  </tr>';
			$total+=$collateral_sum;
			
			//Versandkosten
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '    <td colspan="6">'.$row["shipping_details"].'</td><td>'.number_format($row_order["shipping_costs"], 2, ",", "").' €</td>';
			$text_table_02 .= '  </tr>';
			$total+=$row_order["shipping_costs"];
		}
		else
		{
			//Versandkosten
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '	   <td style="border-right: none"></td>';
			$text_table_02 .= '	   <td style="border-right: none; border-left: none;"></td>';
			$text_table_02 .= '	   <td style="border-left: none">'.number_format($amountsum, 0, ",", "").'</td>';
			$text_table_02 .= '    <td colspan="3">'.$row_order["shipping_details"].'</td><td>'.number_format($row_order["shipping_costs"], 2, ",", "").' €</td>';
			$text_table_02 .= '  </tr>';
			$total+=$row_order["shipping_costs"];
		}
				
		//Gesamt Netto
		if ($gewerblich)
		{
			$discount = 0;
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '    <td colspan="6"><b>'.t("Gesamtpreis Netto").'</b></td><td><b>'.number_format($total, 2, ",", "").' €</b></td>';
			$text_table_02 .= '  </tr>';
			//$ust=(UST/100)*$total;
			//$total=((100+UST)/100)*$total;
		}
		else 
		{
			/*$discount = 0;
			$ust=$total/(100+UST)*UST;
			
			$text9 .= '  <tr>';
			$text9 .= '    <td colspan="5">';
			$text9 .= ''.t("darin enthalten").', '.UST.'%';  
			$text9 .= ' '.t("Umsatzsteuer").'</td><td colspan="2">'.number_format($ust, 2, ",", "").' Euro</td>';
			$text9 .= '  </tr>';
		
			//Gesamt Brutto
			$text9 .= '  <tr>';
			$text9 .= '    <td colspan="5"><b>'.t("Gesamtpreis Brutto").'</b></td><td colspan="2"><b>'.number_format($total, 2, ",", "").' Euro</b></td>';
			$text9 .= '  </tr>';*/
			
			$discount = 0;
			$ust=$total/(100+UST)*UST;
			
			$total=$total-$ust;
			
			/*$text9 .= '  <tr>';
			$text9 .= '    <td colspan="5">';
			$text9 .= ''.t("darin enthalten").', '.UST.'%';  
			$text9 .= ' '.t("Umsatzsteuer").'</td><td colspan="2">'.number_format($ust, 2, ",", "").' Euro</td>';
			$text9 .= '  </tr>';*/
		
			//Gesamt Brutto
			$text_table_02 .= '  <tr>';
			$text_table_02 .= '    <td colspan="6"><b>'.t("Gesamtpreis Netto").'</b></td><td><b>'.number_format($total, 2, ",", "").' €</b></td>';
			$text_table_02 .= '  </tr>';
		}

		$text_table_02 .= '</table>';
	}
	//*************************************************************************************************
	
	//**************************************<!-- SHIPPINGTYPE -->**************************************
	
	$pos = strpos($shipping_details, ", ")+2;
	$text_shipping_type_01 = '<p>';
	$text_shipping_type_01 .= '<b>'.substr($row_order["shipping_details"], $pos).'</b><br>';
	$text_shipping_type_01 .= '<b>'.number_format($totalpos, 2).' € Netto-Warenwert</b><br>';
	if ($gewerblich)
	{
		$text_shipping_type_01 .= '<b>Gewerbekunde</b>';
		if($discount>0)
		{
			$text_shipping_type_01 .= '<br>';
			$text_shipping_type_01 .= '<b>ACHTUNG 4% (€ '.number_format($discount, 2).') ONLINE-RABATT BEACHTEN!!!</b>';					
		}
	}
	else
	{
		$text_shipping_type_01 .= '<b>Privatkunde</b>';
	}
	$text_shipping_type_01 .= '</p>';
	
	if ($row_order["ordernr"]!="")
	{
		$text_shipping_type_01 .= '<p><b>Bitte eigene OrderNr. des Kunden übernehmen!!!</b></p>';
	}
	if ($row_order["comment"]!="")
	{
		$text_shipping_type_01 .= '<p><b>Bitte die Anmerkung des Kunden zur Bestellung beachten!!!</b></p>';
	}

	//*************************************************************************************************
	
	//GET ARTICLE
	$subject='';
	$subject_b='';
	$msgtext='';
	$msgtext_b='';
	$info='';
	$info_b='';
	$data='';
	$data_b='';
	$address='';
	$address_b='';
	$table='';
	$table_b='';
	$shippingtype='';
	$shippingtype_b='';
	
	$mail_article_id=33551;
	$results=q("SELECT article FROM cms_articles WHERE id_article=".$mail_article_id.";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$msgtext = $row["article"];
	$msgtext_b=$row["article"];
/*	
	$text13=str_replace("<!-- DATA -->", $text1, $text11);
	$text13=str_replace("<!-- ADDRESS -->", $text7, $text13);
	$text13=str_replace("<!-- TABLE -->", $text8, $text13);
	$text13=str_replace("<!-- SHIPPINGTYPE -->", $text3, $text13);
	//Rom
	$text14=str_replace("<!-- DATA -->", $text1, $text11);
	$text14=str_replace("<!-- ADDRESS -->", $text7, $text14);
	$text14=str_replace("<!-- TABLE -->", $text9, $text14);
	$text14=str_replace("<!-- SHIPPINGTYPE -->", $text3, $text14);
*/	
	
	//BUILD MAIL DATA
	if ($shop_id>8 and $shop_id<18) //RCs
	{
		if((strpos($row_order["shipping_details"], 'Lieferservice')!==false or strpos($row_order["shipping_details"], 'Selbstabholung')!==false) and ($shop_id > 8 and $shop_id < 17))
		{
			//Borkheide
			$subject_b='MAPCO - '.t("Kopie - Neue Bestellung im Online-Shop ").$city;
			$info_b='<b style="color:red; font-size:14px;">'.t("Diese Bestellung wird durch das RC").' '.$city.' '.t("bearbeitet").'!</b><br /><br />';
			$data_b=$text_data_01;
			$address_b=$text_address_01;
			$table_b=$text_table_01;
			$shippingtype_b=$text_shipping_type_01;
			//Shop
			$subject=t("Neue Bestellung im Online-Shop ").$city;
			$data=$text_data_01;
			$address=$text_address_01;
			$table=$text_table_01;
			$shippingtype=$text_shipping_type_01;
/*
			$info='<b style="color:red; font-size:14px;">'.t("Diese Bestellung wird durch das RC").' '.$city.' '.t("bearbeitet").'!</b><br /><br />';
			$text12=str_replace("<!-- INFO -->", $info, $text13);
			
			send_html_mail_ma("bestellung@mapco-shop.de", t("Kopie - Neue Bestellung im Online-Shop ").$city, $text12);
			if($order_mail==1)
			{
				send_html_mail_ma($mail, t("Neue Bestellung im Online-Shop ").$city, $text13);
			}
*/
		}
		elseif ($shop_id == 17) // Rom
		{
			//Borkheide
			$subject_b='MAPCO - '.t("Neue Bestellung im Online-Shop ").$city;
			$info_b='<b style="color:red; font-size:14px;">'.t("Diese Bestellung für das RC").' '.$city.' '.t("erfassen").'!</b><br /><br />';
			$data_b=$text_data_01;
			$address_b=$text_address_01;
			$table_b=$text_table_01;
			$shippingtype_b=$text_shipping_type_01;
			//Shop
			$subject=t("Neue Bestellung im Online-Shop ").$city;
			$data=$text_data_02;
			$address=$text_address_02;
			$table=$text_table_02;
			$shippingtype=$text_shipping_type_01;
/*			
			$info='<b style="color:red; font-size:14px;">'.t("Diese Bestellung für das RC").' '.$city.' '.t("erfassen").'!</b><br /><br />';
			$text12=str_replace("<!-- INFO -->", $info, $text13);
			
			send_html_mail_ma("bestellung@mapco-shop.de", t("Neue Bestellung im Online-Shop ").$city, $text12);
			if($order_mail==1)
			{
				send_html_mail_ma($mail, t("Neue Bestellung im Online-Shop ").$city, $text14);
			}
*/
		}
		else
		{
			//Borkheide
			$subject_b='MAPCO - '.t("Neue Bestellung im Online-Shop ");
			$info_b='';
			$data_b=$text_data_01;
			$address_b=$text_address_01;
			$table_b=$text_table_01;
			$shippingtype_b=$text_shipping_type_01;
			//Shop
			$subject=t("Kopie - Neue Bestellung im Online-Shop ").$city;
			$info='<b style="color:red; font-size:14px;">'.t("Diese Bestellung wird von Borkheide bearbeitet").'!</b><br /><br />';
			$data=$text_data_01;
			$address=$text_address_01;
			$table=$text_table_01;
			$shippingtype=$text_shipping_type_01;
/*			
			$info='<b style="color:red; font-size:14px;">'.t("Diese Bestellung wird von Borkheide bearbeitet").'!</b><br /><br />';
			$text12=str_replace("<!-- INFO -->", $info, $text13);
			
			send_html_mail_ma("bestellung@mapco-shop.de", t("Neue Bestellung im Online-Shop"), $text13);
			if($order_mail==1)
			{
				send_html_mail_ma($mail, t("Kopie - Neue Bestellung im Online-Shop ").$city, $text12);
			}
*/			
		}
		
		$msgtext_b=str_replace("<!-- INFO -->", $info_b, $msgtext_b);
		$msgtext_b=str_replace("<!-- DATA -->", $data_b, $msgtext_b);
		$msgtext_b=str_replace("<!-- ADDRESS -->", $address_b, $msgtext_b);
		$msgtext_b=str_replace("<!-- TABLE -->", $table_b, $msgtext_b);
		$msgtext_b=str_replace("<!-- SHIPPINGTYPE -->", $shippingtype_b, $msgtext_b);
		
		if($customer_id!=28625 and $customer_id!=49352)
		{
			//MAIL NACH BORKHEIDE		
			$post_data=array();
			$post_data["API"]="cms";
			$post_data["APIRequest"]="MailSend";
			$post_data["ToReceiver"]="bestellung@mapco-shop.de";
			$post_data["FromSender"]="MAPCO-Shop <bestellung@mapco-shop.de>";
			$post_data["Subject"]=$subject_b;
			$post_data["MsgText"]=$msgtext_b;
			
			$postdata=http_build_query($post_data);
			
			$response=soa2($postdata, __FILE__, __LINE__);
		}
/*		
		//KOPIE AN MWOSGIEN
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="MailSend";
		$post_data["ToReceiver"]="mwosgien@mapco.de";
		$post_data["FromSender"]="MAPCO-Shop <bestellung@mapco-shop.de>";
		$post_data["Subject"]=$subject_b;
		$post_data["MsgText"]=$msgtext_b;
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);
*/		
	}
	else if($shop_id>18) //franchise
	{
		$subject=t("Neue Bestellung im Online-Shop ");
		$data=$text_data_01;
		$address=$text_address_01;
		$table=$text_table_01;
		//$shippingtype=$text_shipping_type_01;
	}
	else
	{
		$subject=t("Neue Bestellung im Online-Shop ");
		$data=$text_data_01;
		$address=$text_address_01;
		$table=$text_table_01;
		$shippingtype=$text_shipping_type_01;
	}
	
	$msgtext=str_replace("<!-- INFO -->", $info, $msgtext);
	$msgtext=str_replace("<!-- DATA -->", $data, $msgtext);
	$msgtext=str_replace("<!-- ADDRESS -->", $address, $msgtext);
	$msgtext=str_replace("<!-- TABLE -->", $table, $msgtext);
	$msgtext=str_replace("<!-- SHIPPINGTYPE -->", $shippingtype, $msgtext);
	
	if($customer_id!=28625 and $customer_id!=49352)
	{
		//MAIL AN SHOP	
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="MailSend";
		$post_data["ToReceiver"]=$shop_mail;	
		$post_data["FromSender"]=utf8_decode($row_shop["title"])." <".$shop_mail.">";
		$post_data["Subject"]=$subject;
		$post_data["MsgText"]=$msgtext;
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);
	
		
		//MAIL AN FRAU FRANKE
		if ($row_order["Payments_TransactionID"]!="" and $gewerblich and $shop_id>8 and $shop_id<19)
		{
			$post_data=array();
			$post_data["API"]="cms";
			$post_data["APIRequest"]="MailSend";
			$post_data["ToReceiver"]="kfranke@mapco.de";	
			$post_data["FromSender"]=utf8_decode($row_shop["title"])." <".$shop_mail.">";
			$post_data["Subject"]="Bestellung im Online-Shop - Gewerbekunde mit Online-Zahlung";
			$post_data["MsgText"]=$msgtext;
			
			$postdata=http_build_query($post_data);
			
			$response=soa2($postdata, __FILE__, __LINE__);
			
		}
	}

	if($customer_id==49352)
	{
		//KOPIE AN MWOSGIEN
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="MailSend";
		$post_data["ToReceiver"]="mwosgien@mapco.de";	
		$post_data["FromSender"]=utf8_decode($row_shop["title"]).' <'.$shop_mail.'>';
		$post_data["Subject"]=$subject;
		$post_data["MsgText"]=$msgtext;
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);
	}
?>