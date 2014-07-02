<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a> > ';
	echo 'Amazon';
	echo '</p>';
	
	echo '<h1>Berichte</h1>';

	function amazon_sendrequest($url, $datapost=array())
	{
		$method = "POST";
		$uri = "/";

		$AWSAccessKeyId = "AKIAIVV6BQ6NVVWUWEUA";
		$Marketplace = "A1PA6795UKMFR9";
		$Merchant = "A3UOJO2H7UZY88";
		$SecretKey = "B8k51dOOQFeWoaAmdcvTrOVEb7AyFvQJ0XlzEpMe";
		$host = "mws.amazonservices.de";

		$url .= "&AWSAccessKeyId=$AWSAccessKeyId&Marketplace=$Marketplace&Merchant=$Merchant&Timestamp=".gmdate("Y-m-d\TH:i:s\Z")."&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256";

		// Clean up and sort
		$url = explode('&',$url);
		
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
			$canonicalized_query[] = $param."=".$value;
		}

		$canonicalized_query = implode("&", $canonicalized_query);

		// create the string to sign
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		
		// calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $SecretKey, true));
		
		// encode the signature for the request
		$signature = str_replace("%7E", "~", rawurlencode($signature));
		
		// create request
		$url = "https://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "AmazonQuery/1.0 (Language=Amazon)");

		if ( !empty($datapost) )
		{
			echo '<br /><br />'.htmlentities($datapost["FeedContent"]).'<br /><br />';
			$feedHandle = fopen('php://temp', 'w');
			fwrite($feedHandle, $datapost["FeedContent"]);
			rewind($feedHandle);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1", "Content-MD5:".base64_encode(md5(stream_get_contents($feedHandle), true)) ));
			rewind($feedHandle);
			curl_setopt($ch, CURLOPT_POSTFIELDS, stream_get_contents($feedHandle));
		}
		else
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1"));
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return($response);
	}

	$Merchant = "A3UOJO2H7UZY88";
/*
	//CREATE PRODUCT
	echo 'Erstelle Produkt auf Amazon...<br /><br />';
	$request = "Action=SubmitFeed&FeedType=_POST_PRODUCT_DATA_";
	$datapost=array();
	$datapost["FeedContent"]='<?xml version="1.0" encoding="iso-8859-1"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
<Header>
<DocumentVersion>1.01</DocumentVersion>
<MerchantIdentifier>'.$Merchant.'</MerchantIdentifier>
</Header>
<MessageType>Product</MessageType>
<Message>
	<MessageID>1</MessageID>
	<OperationType>Update</OperationType>
	<Product>
		<SKU>26752/7-1</SKU>
		<DescriptionData>
			<Title>ABS-Ring22222222 hinten  AUDI  A3 (8L1) ,  TT (8N3) ,  TT Roadster (8N9) , SEAT  AROSA (6H) ,  CORDOBA (6K2) ,  CORDOBA (6K2/C2)</Title>
			<Brand>MAPCO</Brand>
			<Description>This is an example product description.</Description>
			<BulletPoint>Einbauseite: Hinterachse beidseitig</BulletPoint>
			<MSRP currency="EUR">50.99</MSRP>
			<Manufacturer>MAPCO Autotechnik GmbH</Manufacturer>
			<MfrPartNumber>26752/7</MfrPartNumber>
			<ItemType>AutoPart</ItemType>
			<RecommendedBrowseNode>78191031</RecommendedBrowseNode>
		</DescriptionData>
		<ProductData>
			<AutoAccessory>
				<ProductType>
					<AutoAccessoryMisc>
						<NumberOfItems>1</NumberOfItems>
					</AutoAccessoryMisc>
				</ProductType>
			</AutoAccessory>
		</ProductData>
	</Product>
</Message>
</AmazonEnvelope>
	';
	$results = amazon_sendrequest($request, $datapost);
	$xml = new SimpleXMLElement($results);
	$array = json_decode(json_encode($xml), TRUE);
	print_r($array);
	echo '<hr />';
*/
/*
	//SET QUANTITY
	echo 'Setze Menge...<br /><br />';
	$request = "Action=SubmitFeed&FeedType=_POST_PRODUCT_DATA_";
	$datapost=array();
	$datapost["FeedContent"]='<?xml version="1.0" encoding="iso-8859-1"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
<Header>
<DocumentVersion>1.01</DocumentVersion>
<MerchantIdentifier>'.$Merchant.'</MerchantIdentifier>
</Header>
<MessageType>Inventory</MessageType>
			<Message>
				<MessageID>1</MessageID>
				<Inventory>
					<SKU>26752/7-1</SKU>
					<Quantity>10</Quantity>
				</Inventory>
			</Message>
</AmazonEnvelope>
	';
	$results = amazon_sendrequest($request, $datapost);
	$xml = new SimpleXMLElement($results);
	$array = json_decode(json_encode($xml), TRUE);
	print_r($array);
	echo '<hr />';
*/

	//SET PRICE
	echo 'Setze Preis...<br /><br />';
	$request = "Action=SubmitFeed&FeedType=_POST_PRODUCT_PRICING_DATA_";
	$datapost=array();
	$datapost["FeedContent"]='<?xml version="1.0" encoding="iso-8859-1"?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$Merchant.'</MerchantIdentifier>
			</Header>
			<MessageType>Price</MessageType>
			<Message>
				<MessageID>1</MessageID>
				<Price>
					<SKU>56789</SKU>
					<StandardPrice currency="EUR">25.99</StandardPrice>
				</Price>
			</Message>
		</AmazonEnvelope>	';
	$results = amazon_sendrequest($request, $datapost);
	$xml = new SimpleXMLElement($results);
	$array = json_decode(json_encode($xml), TRUE);
	print_r($array);
	echo '<hr />';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>