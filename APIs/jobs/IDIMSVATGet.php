<?php

	$starttime=time()+microtime();

	$xml='';

	//GET ISO Country Codes (EU Countries) FROM shop_countries
	$res_countries = q("SELECT * FROM shop_countries WHERE EU = 1", $dbshop, __FILE__, __LINE__);
	while ($row_countries = mysqli_fetch_assoc($res_countries))
	{	
		$xml.='<UST_EU>'."\n";
		$xml.='	<LAND_ISO>'.$row_countries["country_code"].'</LAND_ISO>'."\n";
		$xml.='</UST_EU>'."\n";
	}
	echo $xml;

	$xml = str_replace("\n", "", $xml);
	$xml = str_replace("\t", "", $xml);


	$serverUrl='http://80.146.160.154/idims/service1.asmx/UST_EU';
	$fields = array(
						'Token' => "it@mapco.de",
						'opXML' => urlencode($xml),
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
	echo $responseXml;
?>