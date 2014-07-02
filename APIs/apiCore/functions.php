<?php

function amazonSubmit($post)
{
	$host = "mws.amazonservices.de";
	
	if (!empty($post['method'])) {
		$method= $post['method'];
	} else {
		$method = "POST";
	}
	
	if (!empty($post['type'])) {
		$uri = "/" . $post['type'];	
	} else {
		$uri = "/";
	}
		
	// Clean up and sort
	$url = explode('&', $post['url']);
	
	foreach ($url as $key => $value)
	{
		$t = explode("=",$value);
		$params[$t[0]] = $t[1];
	}
	unset($url);

	ksort($params);

	foreach ($params as $param=>$value)
	{
		$param = str_replace("%7E", "~", rawurlencode($param));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$canonicalized_query[] = $param . "=" . $value;
	}

	$canonicalized_query = implode("&", $canonicalized_query);

	// create the string to sign	
	$string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;
	
	// calculate HMAC with SHA256 and base64-encoding
	$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $post['SecretKey'], true));
	
	// encode the signature for the request
	$signature = str_replace("%7E", "~", rawurlencode($signature));
	
	// create request
	$requestUrl = "https://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "AmazonQuery/1.0 (Language=Amazon)");

	if (!empty($_POST['data'])) {
		$feedHandle = fopen('php://temp', 'w');
		fwrite($feedHandle, $_POST['data']);
		rewind($feedHandle);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1", "Content-MD5:".base64_encode(md5(stream_get_contents($feedHandle), true)) ));
		rewind($feedHandle);
		curl_setopt($ch, CURLOPT_POSTFIELDS, stream_get_contents($feedHandle));
	} else {
		curl_setopt($ch, CURLOPT_USERAGENT, "AmazonQuery/1.0 (Language=Amazon)");
	}
	
	curl_setopt($ch, CURLOPT_URL, $requestUrl);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;	
}