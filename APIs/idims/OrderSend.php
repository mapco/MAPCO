<?php

	include("../functions/shop_get_prices.php");


	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		/*
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		*/
		
		$xml=$data;
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}
	
	
	function checkEncoding ( $string, $string_encoding = 'ISO-8859-1')
	{
		//PHP FUNCTION mb_check_encoding checkt nur den korrekten "byte stream" => Sonderzeichen können u.U. als 2 Zeichen geprüft und somit korrekt sein
		// daher "EIGENBAU"
		
		$fs = $string_encoding;
		
		$ts = 'UTF-8';
		
		return $string === mb_convert_encoding ( mb_convert_encoding ( $string, $fs, $ts ), $ts, $fs );
	}


	
	$order=array();
	
	if ( !isset($_POST["id_order"]) )
	{
		show_error(0, 5, __FILE__, __LINE__, explode("; ", $_POST));
		exit;
	}
//>>>>>>>>>>>>>>>>	
	//CHECK FOR NETPRICE CORRECTION 
		//GET COUNTRY VAT
		$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)==0) { show_error(9864, 7, __FILE__, __LINE__); exit; }
		$order[0]=mysqli_fetch_array($results);
		
		$res_country_VAT = q("SELECT * FROM shop_countries WHERE country_code = '".$order[0]["bill_country_code"]."' AND EU = 1 LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_country_VAT)==0)
		{
			$VAT = 19;	
		}
		else
		{
			$row_country_VAT = mysqli_fetch_assoc($res_country_VAT);
			$VAT = $row_country_VAT["VAT"];
		}
		
		// WENN UNTERSCHIEDLICHE VAT -> KORREKTUR
		if ($order["VAT"] != $VAT)
		{
			$fieldlist = array();
			$fieldlist["API"] = "shop";
			$fieldlist["APIRequest"] = "OrderNetPriceCorrection";
			$fieldlist["orderid"] = $order[0]["id_order"];
			
			$responseXML=post(PATH."soa2/", $fieldlist);
	
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
			//	show_error(9775, 7, __FILE__, __LINE__, $responseXML);
			//	exit;
			}
			
		}
//>>>>>>>>>>>

	
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==0) { show_error(9863, 7, __FILE__, __LINE__); exit; }
	$order[0]=mysqli_fetch_array($results);
	$auf_id_check[]=$order[0]["id_order"];

	if ($order[0]["combined_with"]>0)
	{
		$tmp_orderid=$order[0]["combined_with"];
		
		//GET ALL ORDERS OF COMBINATION; ORDER[0] ->"MOTHER"
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
		$order[0]=mysqli_fetch_array($res_orders);
		$auf_id_check[]=$order[0]["id_order"];
		
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND NOT id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
		while ($row_orders=mysqli_fetch_array($res_orders))
		{
			$order[sizeof($order)]=$row_orders;
			$auf_id_check[]=$row_orders["id_order"];
		}
	}
	
	//AUF ID CHECK - WURDE AUFTRAG ODER TEILE BEREITS AN IDIMS VERSCHICKT?
	$AUF_ID=0;
	$res_check=q("SELECT * FROM shop_orders_auf_id WHERE order_id IN (".implode(", ", $auf_id_check).");", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($res_check)>0 ) { show_error(9862, 7, __FILE__, __LINE__); exit; }

	//get shops	
	$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order[0]["shop_id"].";", $dbshop, __FILE__, __LINE__);
	$shop=mysqli_fetch_array($results);

	//get payment method


	//build request XML
	$requestXml  = '<ORDER>'."\n";

	//USR_ID - what is that?
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 ) { show_error(9861, 7, __FILE__, __LINE__); exit; }
	$row=mysqli_fetch_array($results);
//	if( $_SESSION["id_user"]==21371 ) $row["idims_user_id"]=336; //als Stefan Habermann
	$requestXml .= '	<USR_ID>'.$row["idims_user_id"].'</USR_ID>'."\n";
		
	//KUN_ID - linked to shop_id
	$results=q("SELECT * FROM cms_users WHERE id_user=".$order[0]["customer_id"].";", $dbweb, __FILE__, __LINE__);
	$cms_users=mysqli_fetch_array($results);
	$results=q("SELECT * FROM kunde WHERE ADR_ID='".$cms_users["idims_adr_id"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		$requestXml .= '	<KUN_ID>'.$row["IDIMS_ID"].'</KUN_ID>'."\n";
	}
	else
	{
		$requestXml .= '	<KUN_ID>'.$shop["KUN_ID"].'</KUN_ID>'."\n";
	
		//AD_NR - linked to shop_id
		if($shop["AD_NR"]>0)
		{
			$requestXml .= '	<AD_NR>'.$shop["AD_NR"].'</AD_NR>'."\n";
		}
	}
	
	//select bill or ship address
	if( $order[0]["ship_adr_id"]>0 )
	{
		$results=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$order[0]["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$order[0]["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}
	if( mysqli_num_rows($results)==0 ) { show_error(9860, 7, __FILE__, __LINE__); exit; }
	$row=mysqli_fetch_array($results);
	
	
	//CHECK ADDRESS FOR VALID 'ISO-8859-1' CHARSET (LATIN-1)
	if (!checkEncoding($row["company"])) { show_error(9883,5, __FILE__, __LINE__, $row["company"]); exit;}
	if (!checkEncoding($row["firstname"])) { show_error(9884,5, __FILE__, __LINE__, $row["firstname"]); exit;}
	if (!checkEncoding($row["lastname"])) { show_error(9885,5, __FILE__, __LINE__, $row["lastname"]); exit;}
	if (!checkEncoding($row["street"])) { show_error(9886,5, __FILE__, __LINE__, $row["street"]); exit;}
	if (!checkEncoding($row["number"])) { show_error(9887,5, __FILE__, __LINE__, $row["number"]); exit;}
	if (!checkEncoding($row["additional"])) { show_error(9888,5, __FILE__, __LINE__, $row["additional"]); exit;}
	if (!checkEncoding($row["zip"])) { show_error(9889,5, __FILE__, __LINE__, $row["zip"]); exit;}
	if (!checkEncoding($row["city"])) { show_error(9890,5, __FILE__, __LINE__, $row["city"]); exit;}


	//KUN_SPRACHE - could be detected by user default language
	if( $row["country_id"]>3) 
	{
		$KUN_SPRACHE=4; 
		$ART_KTXT=1;
	}
	else 
	{
		$KUN_SPRACHE=0; //0=Artikelbeschreibung laut Artikelstamm (nicht TecDoc Beschreibung)
		$ART_KTXT=0;
	}
	$requestXml .= '	<KUN_SPRACHE>'.$KUN_SPRACHE.'</KUN_SPRACHE>'."\n";
	$requestXml .= '	<ART_KTXT>'.$ART_KTXT.'</ART_KTXT>'."\n";
	
	//ADR - either bill_address or ship_address from shop_orders
	//EBAYNAME
	if ($order[0]["shop_id"]==8)
	{
		$requestXml .= '	<ADR_ORG_NR>SHOP</ADR_ORG_NR>'."\n";
	}
	else
	{
		$res_ebay_order=q("SELECT * FROM ebay_orders WHERE OrderID = '".$order[0]["foreign_OrderID"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ebay_order)==0)
		{	
			$requestXml .= '	<ADR_ORG_NR></ADR_ORG_NR>'."\n";
			
		}
		else
		{
			$row_ebay_order=mysqli_fetch_array($res_ebay_order);
			$requestXml .= '	<ADR_ORG_NR>'.$row_ebay_order["BuyerUserID"].'</ADR_ORG_NR>'."\n";
			
		}
	}

	if($row["ADR__ID"]>0) 
	{
		$requestXml .= '	<ADR_ID>'.$row["ADR__ID"].'</ADR_ID>'."\n";
	}
	else
	{
		$requestXml .= '	<ADR_ID>0</ADR_ID>'."\n";
		
		//ADR_ANREDE
		if( $row["gender"]=="Frau" ) $ADR_ANREDE='Frau'; else $ADR_ANREDE='Herr';
		$requestXml .= '	<ADR_ANREDE>'.$ADR_ANREDE.'</ADR_ANREDE>'."\n";
		
		
		//ADR_NAME
		if($row["company"]!="")
		{
			$ADR_NAME1=$row["company"];
			$ADR_NAME2=$row["firstname"].' '.$row["lastname"];
		}
		else
		{
			$ADR_NAME1=$row["firstname"].' '.$row["lastname"];
			$ADR_NAME2='';
		}
		$requestXml .= '	<ADR_NAME_1>'.$ADR_NAME1.'</ADR_NAME_1>'."\n";
		$requestXml .= '	<ADR_NAME_2>'.$ADR_NAME2.'</ADR_NAME_2>'."\n";
			
		//ADR_STR1
		$ADR_STR1=$row["street"].' '.$row["number"];
		$requestXml .= '	<ADR_STR_1>'.$ADR_STR1.'</ADR_STR_1>'."\n";
			$data["ADR_STR_1"]=$ADR_STR1;
		
		//ADR_STR2
		if( $row["additional"]!="" ) $ADR_STR2=$row["additional"]; else $ADR_STR2='';
		$requestXml .= '	<ADR_STR_2>'.$ADR_STR2.'</ADR_STR_2>'."\n";
		
		//ADR_PLZ
		$ADR_PLZ=$row["zip"];
		$requestXml .= '	<ADR_PLZ>'.$ADR_PLZ.'</ADR_PLZ>'."\n";
		
		//ADR_ORT
		$ADR_ORT=$row["city"];
		$requestXml .= '	<ADR_ORT>'.$ADR_ORT.'</ADR_ORT>'."\n";
		
		//ADR_LKZ
		$results2=q("SELECT * FROM shop_countries WHERE id_country=".$row["country_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$ADR_LKZ=$row2["country_code"];
		$requestXml .= '	<ADR_LKZ>'.$ADR_LKZ.'</ADR_LKZ>'."\n";

		//ADR_LAND
	//	$ADR_LAND=$row2["country"];
	//	$requestXml .= '	<ADR_LAND>'.$ADR_LAND.'</ADR_LAND>'."\n";

		if( $ADR_NAME1=="" or $ADR_STR1=="" or $ADR_PLZ=="" or $ADR_ORT=="" ) { show_error(9859, 7, __FILE__, __LINE__); exit; }
	}
	

	//ADR_MAIL
	$ADR_MAIL=$order[0]["usermail"];
	$requestXml .= '	<ADR_MAIL>'.$ADR_MAIL.'</ADR_MAIL>'."\n";

	//ZLG Zahlungsart - 0=Rechnung, 1=Überweisung/Barzahlung, 2=PayPal, 3=Nachnahme, 4=Kreditkarte, 5=Sofortüberweisung
	$results=q("SELECT * FROM shop_payment_types WHERE id_paymenttype=".$order[0]["payments_type_id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ZLG=$row["ZLG"];
	$requestXml .= '	<ZLG>'.$ZLG.'</ZLG>'."\n";
	
	//Vorabumtausch
	if($order[0]["ordertype_id"]==4) $vorab=1; else $vorab=0;
	$requestXml .= '	<VORAB>'.$vorab.'</VORAB>'."\n";
	
	//VERS_ID Versandart - ID laut IDIMS
	$results=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype=".$order[0]["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	if( $order[0]["shop_id"]==2 or $order[0]["shop_id"]==4 ) $VERS_ID=$row["AP_VERS_ID"];
	else $VERS_ID=$row["VERS_ID"];
	$requestXml .= '	<VERS_ID>'.$VERS_ID.'</VERS_ID>'."\n";
	
	//PACK Packstelle - 0=Zentrale, ab 15=RCs
	//$requestXml .= '	<PACK>0</PACK>'."\n";
	$requestXml .= '	<PACK>' . $shop[ 'packing_location' ] . '</PACK>'."\n";	

/*
	//ORDER NR 
	if( $order[0]["ordernr"]!="" )
	{
		$requestXml .= '	<PRODUKT>'."\n";
		$requestXml .= '		<ART_NR>Text</ART_NR>'."\n";
		$requestXml .= '		<ART_BEZ>Bestellnummer: '.$order[0]["ordernr"].'</ART_BEZ>'."\n";
		$requestXml .= '		<ART_UST>1</ART_UST>'."\n";
		$requestXml .= '		<MENGE>1</MENGE>'."\n";
		$requestXml .= '		<VK>0,00</VK>'."\n";
		$requestXml .= '	</PRODUKT>'."\n";
	}

	//ORDER COMMENT
	if( $order[0]["comment"]!="" )
	{
		$requestXml .= '	<PRODUKT>'."\n";
		$requestXml .= '		<ART_NR>Text</ART_NR>'."\n";
		$requestXml .= '		<ART_BEZ>Anmerkung: '.$order[0]["comment"].'</ART_BEZ>'."\n";
		$requestXml .= '		<ART_UST>1</ART_UST>'."\n";
		$requestXml .= '		<MENGE>1</MENGE>'."\n";
		$requestXml .= '		<VK>0,00</VK>'."\n";
		$requestXml .= '	</PRODUKT>'."\n";
	}
*/

	//PRODUKT - data from shop_orders_items
		//$results=q("SELECT * FROM shop_orders_items WHERE order_id=".$order["id_order"].";", $dbshop, __FILE__, __LINE__);
	$in_orders="";
	for ($j=0; $j<sizeof($order); $j++)
	{
		if ($in_orders=="") $in_orders.=$order[$j]["id_order"]; else $in_orders.=", ".$order[$j]["id_order"];
	}
	$results=q("SELECT * FROM shop_orders_items WHERE order_id IN (".$in_orders.");", $dbshop, __FILE__, __LINE__);

	$art_collateral=0;
	$item_collateral=array();
	$order_collateral=0;
	$exchangerates=array();
	while($row=mysqli_fetch_array($results))
	{
		if ($row["item_id"]!=0 && $row["amount"]!=0)
		{
			$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$ART_NR=$row2["MPN"];
			
			//Collateral add for Check
			if($row["item_id"]!=28093 and $row["item_id"]!=28624) $art_collateral+=($row2["collateral"]*$row["amount"]);
			else $order_collateral+=($row["netto"]*(1/$row["exchange_rate_to_EUR"])*$row["amount"]);
			
			if( $row2["collateral"]>0 ) $item_collateral[]=$row2["MPN"];

			//Price Check
			if( $_SESSION["id_user"]!=21371 or $_POST["approved"]!="true" )
			{
				if($ART_NR!="29999/1" and $ART_NR!="29998/1")
				{
					$results3=q("SELECT POS_0_WERT FROM prpos WHERE ARTNR='".$ART_NR."' AND LST_NR='".$shop["pricelist"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($results3)>0)
					{
						$row3=mysqli_fetch_array($results3);
						$_vat = 1;
						if ( $order[0]["VAT"]!=0)
						{
							$_vat = ($order[0]["VAT"]/100)+1;	
						}
						$normal=round($row3["POS_0_WERT"]*$_vat, 2);
						//convert from other currency
						$item_price=round($row["price"]*(1/$row["exchange_rate_to_EUR"]), 2);
						$okprice=round($item_price*0.9, 2);
						//decrease by shipping costs when shipping is free
						if( $order[0]["shipping_costs"]==0 )
						{
							if( $order[0]["bill_country_code"]=="DE" )
							{
								$item_price-=4.9;
								$okprice-=4.9;
							}
							else
							{
								$item_price-=15.9;
								$okprice-=15.9;
							}
						}
						if($item_price<$okprice) $checked=1;
						else $checked=0;
					}
					else 
					{
						$normal=0;
						$okprice=0;
						$checked=0;						
					}
					
					if($item_price<$okprice)
					{
						$error_txt= 'Die Bestellung konnte nicht übermittelt werden, weil der Preis ('.number_format($item_price, 2, ",", "").' €) von Artikel '.$ART_NR.' den Verkaufspreis '.number_format($normal, 2, ",", "").' und den Mindestverkaufspreis von '.number_format($okprice, 2, ",", "").' € unterschreitet.';
						show_error(9865, 7, __FILE__, __LINE__, $error_txt);
						exit;
					}
				}
			}
			
			$exchange_rate_to_EUR=1/$row["exchange_rate_to_EUR"];
			$exchangerates[$row["order_id"]]=$exchange_rate_to_EUR;

			$VK=number_format($row["price"]*$exchange_rate_to_EUR, 2, ",", ".");
			$requestXml .= '	<PRODUKT>'."\n";
			$requestXml .= '		<ART_NR>'.$ART_NR.'</ART_NR>'."\n";
			//UST 0=keine UST, 1=Nettopreis+UST, 2=Bruttopreis inkl. UST
			if( $order[0]["VAT"]==0 ) $ART_UST=0; else $ART_UST=2;
			$requestXml .= '		<ART_UST>'.$ART_UST.'</ART_UST>'."\n";
			$requestXml .= '		<MENGE>'.$row["amount"].'</MENGE>'."\n";
			$requestXml .= '		<VK>'.$VK.'</VK>'."\n";
			$requestXml .= '	</PRODUKT>'."\n";
		}
	}
	
	//Collateral Check
	if( $_POST["approved"]!="true" )
	{
		if( round($art_collateral-$order_collateral, 0)>0 ) { show_error(9858, 7, __FILE__, __LINE__, $art_collateral.' / '.$order_collateral); exit; }
	}

	$shipping_net_sum=0;
	for ($i=0; $i<sizeof($order); $i++)
	{
		if (isset($exchangerates[$order[$i]["id_order"]]))
		{
			$shipping_net_sum+=$order[$i]["shipping_net"]*$exchangerates[$order[$i]["id_order"]];
		}
		else
		{
			$res_ex=q("SELECT * FROM shop_currencies WHERE currency_code = '".$order[$i]["Currency_Code"]."';", $dbshop, __FILE__, __LINE__);
			$row_ex=mysqli_fetch_array($res_ex);
			$shipping_net_sum+=$order[$i]["shipping_net"]*(1/$row_ex["exchange_rate_to_EUR"]);
		}
	}
	
	//FRACHT if shipping costs are special
	if( $shipping_net_sum>0 )
	{
		$requestXml .= '	<PRODUKT>'."\n";
		$requestXml .= '		<ART_NR>FRACHT</ART_NR>'."\n";
		$results2=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype=".$order[0]["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$ART_BEZ='Versandkosten '.$row2["title"];
		$requestXml .= '		<ART_BEZ>'.$ART_BEZ.'</ART_BEZ>'."\n";
		if( $order[0]["VAT"]==0 ) $FRACHT_UST=0; else $FRACHT_UST=1;
		$requestXml .= '		<ART_UST>'.$FRACHT_UST.'</ART_UST>'."\n";
		$requestXml .= '		<MENGE>1</MENGE>'."\n";
		$VK=number_format($shipping_net_sum, 2, ",", ".");
		$requestXml .= '		<VK>'.$VK.'</VK>'."\n";
		$requestXml .= '	</PRODUKT>'."\n";
	}
	
	//DATA FOR SAVE ORDEREVENT
	$data=array();
	$data=$requestXml;
  
	$requestXml .= '</ORDER>'."\n";

	$requestXml = str_replace("'", "´", $requestXml);
	$requestXml = str_replace("&", "+", $requestXml);
	$requestXml = str_replace("\n", "", $requestXml);
	$requestXml = str_replace("\t", "", $requestXml);
//	$requestXml = str_replace("?", "%3F", $requestXml);

	//it@mapco.de
	//it@mapco.de<TESTDB/>
	
	//echo $requestXml;

	if($_SESSION["id_user"]==22044)
	{
	//	echo $requestXml;
	//	exit;
	}

	$serverUrl='http://80.146.160.154/idims/service1.asmx/BUILD_ORDER';
	$fields = array(
						'Token' => "it@mapco.de",
						'orderXML' => urlencode($requestXml),
					);

	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');

	$connection = curl_init();
	curl_setopt($connection, CURLOPT_FORBID_REUSE, true); 
	curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($connection, CURLOPT_POST, true);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
	$responseXml = curl_exec($connection);
	curl_close($connection);

	//xml validation fix
	$responseXml=str_replace('&lt;', '<', $responseXml);
	$responseXml=str_replace('&gt;', '>', $responseXml);
	$responseXml=str_replace('KUN_ID><', '/KUN_ID><', $responseXml);

	//read response
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		show_error(9857, 7, __FILE__, __LINE__, $responseXml);
		
		if ($_SESSION["id_user"]==28625)
		{
			echo $responseXml;
		}
		
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	if (strpos($responseXml, "<ERROR>")>0) 
	{
		if(strpos($responseXml, "geringer Bestand")>0)
		{
			echo '<OrderSendResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zu geringer Bestand</shortMsg>'."\n";
			echo '		<longMsg>Der Auftrag konnte wegen zu geringem Bestand nicht an IDIMS übermittelt werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '  <Response><![CDATA['.$responseXml.']]></Response>';
			echo '</OrderSendResponse>'."\n";
			show_error(9875, 7, __FILE__, __LINE__, $responseXml, false);
			exit;
		}
		else { show_error(9854, 7, __FILE__, __LINE__, $responseXml, true); exit; }
	}

	$AUF_ID=$response->AUF_ID[0];
	$AP_AUF_ID=$response->AP_AUF_ID[0];
	$MA_AUF_ID=$response->MA_AUF_ID[0];
	$MO_AUF_ID=$response->MO_AUF_ID[0];
	$ADR_ID=$response->ADR_ID[0];
	
	if( $AUF_ID==0 or $ADR_ID=="" ) { show_error(9856, 7, __FILE__, __LINE__, $responseXml); exit; }
	

	// UPDATE ORDER AUF_ID (shop_order_auf_id)
	$inserted=false;
  	if ($AP_AUF_ID>0)
	{
		q("	INSERT INTO shop_orders_auf_id (AUF_ID, order_id, man_id, parent_auf_id) 
			VALUES (".$AP_AUF_ID.", ".$order[0]["id_order"].", 2, ".$AUF_ID.");", $dbshop, __FILE__, __LINE__);
		$inserted=true;
	}
  	if ($MA_AUF_ID>0)
	{
		q("	INSERT INTO shop_orders_auf_id (AUF_ID, order_id, man_id, parent_auf_id) 
			VALUES (".$MA_AUF_ID.", ".$order[0]["id_order"].", 1, ".$AUF_ID.");", $dbshop, __FILE__, __LINE__);
		$inserted=true;
	}
  	if ($MO_AUF_ID>0)
	{
		q("	INSERT INTO shop_orders_auf_id (AUF_ID, order_id, man_id, parent_auf_id) 
			VALUES (".$MO_AUF_ID.", ".$order[0]["id_order"].", 8, ".$AUF_ID.");", $dbshop, __FILE__, __LINE__);
		$inserted=true;
	}
  	if (!$inserted)
	{
		q("	INSERT INTO shop_orders_auf_id (AUF_ID, order_id, man_id, parent_auf_id) 
			VALUES (".$AUF_ID.", ".$order[0]["id_order"].", 1, ".$AUF_ID.");", $dbshop, __FILE__, __LINE__);
	}

	// UPDATE ORDER (shop_order) -> Status_id = 2
  	if ($order[0]["combined_with"]>0)
	{
		q("	UPDATE shop_orders
			SET AUF_ID=".$AUF_ID.",
				auf_id_date=".time().",
				status_id=2,
				status_date=".time().",
				lastmod=".time().",
				lastmod_user=".$_SESSION["id_user"]."
			WHERE combined_with=".$order[0]["combined_with"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("	UPDATE shop_orders
			SET AUF_ID=".$AUF_ID.",
				auf_id_date=".time().",
				status_id=2,
				status_date=".time().",
				lastmod=".time().",
				lastmod_user=".$_SESSION["id_user"]."
			WHERE id_order=".$order[0]["id_order"].";", $dbshop, __FILE__, __LINE__);
	}

	if( $order["ship_adr_id"]>0 )
	{
		q("UPDATE shop_bill_adr SET ADR__ID=".$ADR_ID." WHERE adr_id=".$order[0]["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("UPDATE shop_bill_adr SET ADR__ID=".$ADR_ID." WHERE adr_id=".$order[0]["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}


	$data.= '	<IDIMS_Response_AUF_ID>'.$AUF_ID.'</IDIMS_Response_AUF_ID>'."\n";
	$data.= '	<IDIMS_Response_ADR_ID>'.$ADR_ID.'</IDIMS_Response_ADR_ID>'."\n";
	$data.= '</ORDER>'."\n";
	
	//SAVE ORDEREVENT
	$id_event=save_order_event(11, $order[0]["id_order"], $data);


//	echo '<OrderSendResponse>'."\n";
//	echo '	<Ack>Success</Ack>'."\n";
	echo '  <Response><![CDATA['.$responseXml.']]></Response>';
//	echo '</OrderSendResponse>'."\n";

?>