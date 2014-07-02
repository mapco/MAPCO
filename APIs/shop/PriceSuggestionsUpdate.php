<?php
	if ( !isset($_POST["data"]) )
	{
		echo '<PriceSuggestionsUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>XML nicht gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceSuggestionsUpdateResponse>'."\n";
		exit;
	}

	$xml=new SimpleXMLElement($_POST["data"]);
	for($i=0; $i<count($xml->Price); $i++)
	{
		//update price suggestions
/*
		echo $xml->Price[$i]["id_pricesuggestion"]."\n";
		echo $xml->Price[$i]["SKU"]."\n";
		echo $xml->Price[$i]."\n";
*/
		q("UPDATE shop_price_suggestions SET imported=1 WHERE id_pricesuggestion=".$xml->Price[$i]["id_pricesuggestion"].";", $dbshop, __FILE__, __LINE__);
		
		//update autopartner pricelist
		$timestamp = time();
		$GUELTIG_AB = date("Y-m-j", $timestamp);
		$timestamp = $timestamp+(3600*24*365*10);
		$GUELTIG_BIS = date("Y-m-j", $timestamp);
		$results=q("SELECT * FROM prpos WHERE LST_NR=18209 AND ARTNR='".$xml->Price[$i]["SKU"]."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			q("UPDATE prpos SET POS_0_WERT='".$xml->Price[$i]."', GUELTIG_AB='".$GUELTIG_AB."', GUELTIG_BIS='".$GUELTIG_BIS."' WHERE LST_NR=18209 AND ARTNR='".$xml->Price[$i]["SKU"]."';", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			q("INSERT INTO prpos(ARTNR, LSt_NR, POS_0_WERT, POS_0_PE, LSt_AKTIV_CHK, POS_AKTIV_CHK, AKTION_CHK, NETTO_CHK, GUELTIG_AB, GUELTIG_BIS, NEU, GEAND, MAN_ID) VALUES('".$xml->Price[$i]["SKU"]."', 18209, '".$xml->Price[$i]."', 0, 1, 1, 0, 1, '".$GUELTIG_AB."', '".$GUELTIG_BIS."', '".date("Y-m-j H:i:s")."', '".date("Y-m-j H:i:s")."',	1) ;", $dbshop, __FILE__, __LINE__);
		}
		
		//update mapco pricelist
		$mapco_price=$xml->Price[$i];
//		$mapco_price*=1.1;
		$results=q("SELECT * FROM prpos WHERE LST_NR=16815 AND ARTNR='".$xml->Price[$i]["SKU"]."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			q("UPDATE prpos SET POS_0_WERT='".$mapco_price."', GUELTIG_AB='".$GUELTIG_AB."', GUELTIG_BIS='".$GUELTIG_BIS."' WHERE LST_NR=16815 AND ARTNR='".$xml->Price[$i]["SKU"]."';", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			q("INSERT INTO prpos(ARTNR, LSt_NR, POS_0_WERT, POS_0_PE, LSt_AKTIV_CHK, POS_AKTIV_CHK, AKTION_CHK, NETTO_CHK, GUELTIG_AB, GUELTIG_BIS, NEU, GEAND, MAN_ID) VALUES('".$xml->Price[$i]["SKU"]."', 16815, '".$mapco_price."', 0, 1, 1, 0, 1, '".$GUELTIG_AB."', '".$GUELTIG_BIS."', '".date("Y-m-j H:i:s")."', '".date("Y-m-j H:i:s")."',	1) ;", $dbshop, __FILE__, __LINE__);
		}
	}

	echo '<PriceSuggestionsUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</PriceSuggestionsUpdateResponse>'."\n";
?>