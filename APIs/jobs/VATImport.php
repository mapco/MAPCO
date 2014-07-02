<?php

	$starttime = time()+microtime();
	
	echo 'Folgende UST-Werte wurden aktualisiert:'."\n";
	
	$cnt=1;
	
	$res=q("SELECT * FROM shop_countries WHERE EU=1", $dbshop, __FILE__, __LINE__);
	while($shop_countries=mysqli_fetch_assoc($res))
	{
		$serverUrl='http://80.146.160.154/idims/service1.asmx/UST_EU';
		$fields = array(
							'Token' => "it@mapco.de",
							'LAND_ISO' => $shop_countries["country_code"],
							'ManID' => 1,
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
		unset($fields);
		unset($fields_string);
	
		//xml validation fix
		$responseXml=str_replace('&lt;', '<', $responseXml);
		$responseXml=str_replace('&gt;', '>', $responseXml);
	
		//read response
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<InvoicesDataGetResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '  <Response><![CDATA['.$responseXml.']]></Response>';
			echo '</InvoicesDataGetResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		//echo 'aaa'."\n";
		//print_r($response);
		$res2=q("SELECT * FROM shop_countries WHERE country_code='".(string)$response->LAND_ISO[0]."'", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res2)==1)
		{
			$shop_countries_2=mysqli_fetch_assoc($res2);
			if($shop_countries_2["VAT"]!=(int)$response->UST[0] and (int)$response->UST[0]>0)
			{
				$data=array("VAT" => (int)$response->UST[0]);
				$res3=q_update("shop_countries", $data, "WHERE country_code='".(string)$response->LAND_ISO[0]."'", $dbshop, __FILE__, __LINE__);
				echo $cnt.". ".$response->LAND_ISO[0]." alt: ".$shop_countries_2["VAT"]." neu: ".$response->UST[0]." aktualisiert"."\n";
			}
			else
			{
				echo $cnt.". ".$response->LAND_ISO[0]." alt: ".$shop_countries_2["VAT"]." neu: ".$response->UST[0]."\n";
			}
		}
		$cnt++;
	}
	
	$stoptime = time()+microtime();
	echo "Dauer: ".number_format($stoptime-$starttime, 2)." Sekunden";
/*
	//stop job if time limit is reached
//	$stoptime = time()+microtime();
//	if ($stoptime-$starttime>60) break;
*/	
?>