<?php
	$starttime = time()+microtime();
	
	if ( !isset($_POST["xml"]) )
	{
		echo '<ItemsUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein XML-Feld übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemsUpdateResponse>'."\n";
		exit;
	}
	
	$xml=new SimpleXMLElement($_POST["xml"]);
	for($i=0; $i<count($xml); $i++)
	{
		q("	UPDATE shop_items
			SET		COS='".(float)str_replace(",", ".", $xml->Item[$i]->COS)."',
					BRUTTO='".(float)str_replace(",", ".", $xml->Item[$i]->BRUTTO)."',
					MINDEST_VK='".(float)str_replace(",", ".", $xml->Item[$i]->MINDEST_VK)."',
					MENGE_360_TAGE='".$xml->Item[$i]->MENGE_360_TAGE."',
					KFZ_BESTAND_TECDOC='".$xml->Item[$i]->KFZ_BESTAND_TECDOC."',
					BESTAND_INL_ZENTRALE='".$xml->Item[$i]->BESTAND_INL_ZENTRALE."',
					BESTELLT='".$xml->Item[$i]->BESTELLT."'
			WHERE MPN='".$xml->Item[$i]->SKU."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ItemsUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ItemsUpdateResponse>'."\n";
?>