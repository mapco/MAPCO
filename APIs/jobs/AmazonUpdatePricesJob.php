<?php
	session_start();
	include("../config.php");

	function amazon_sendrequest($url, $datapost=array())
	{
		$method = "POST";
		$uri = "/";
		
		//account
		global $AWSAccessKeyId;
		global $Marketplace;
		global $Merchant;
		global $SecretKey;
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
//			echo '<br /><br />'.htmlentities($datapost["FeedContent"]).'<br /><br />';
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
	
	//get account
	$results=q("SELECT * FROM amazon_accounts WHERE id_account=1;", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		$AWSAccessKeyId = $row["AWSAccessKeyId"]; //"AKIAIVV6BQ6NVVWUWEUA";
		$Marketplace = $row["MarketplaceId"]; //"A1PA6795UKMFR9";
		$Merchant = $row["MerchantId"]; //"A3UOJO2H7UZY88";
		$SecretKey = $row["SecretKey"]; //"B8k51dOOQFeWoaAmdcvTrOVEb7AyFvQJ0XlzEpMe";
		$host = "mws.amazonservices.de";
	}

	//submit prices
	$request = "Action=SubmitFeed&FeedType=_POST_PRODUCT_PRICING_DATA_";
	$datapost=array();
	$datapost["FeedContent"]='<?xml version="1.0" encoding="utf-8" ?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$Merchant.'</MerchantIdentifier>
			</Header>
			<MessageType>Price</MessageType>';
	$results=q("SELECT * FROM amazon_products WHERE account_id=1 AND lastpriceupdate<".(time()-24*3600)." ORDER BY lastpriceupdate;", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<AmazonUpdatePricesJobResponse>';
		echo '	<Ack>Success</Ack>';
		echo '	<Results>All prices have been updated within the last 24 hours.</Results>';
		echo '</AmazonUpdatePricesJobResponse>';
		exit;
	}
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		q("UPDATE amazon_products SET lastpriceupdate=".time()." WHERE id_product=".$row["id_product"].";", $dbshop, __FILE__, __LINE__);
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$shop_items=mysqli_fetch_array($results2);
		
		$results6=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR=5;", $dbshop, __FILE__, __LINE__);
		$row6=mysqli_fetch_array($results6);
		$StandardPrice=number_format($row6["POS_0_WERT"]*((100+UST)/100), 2); //mandatory
		if( $StandardPrice<1 ) $StandardPrice=1;
		$datapost["FeedContent"] .= '
			<Message>
				<MessageID>'.$i.'</MessageID>
				<Price>
					<SKU>'.$row["SKU"].'</SKU>
					<StandardPrice currency="EUR">'.$StandardPrice.'</StandardPrice>
				</Price>
			</Message>';
	}
	$datapost["FeedContent"] .= '</AmazonEnvelope>';
	$datapost["FeedContent"];
	$results = amazon_sendrequest($request, $datapost);
	$xml = new SimpleXMLElement($results);
	$array = json_decode(json_encode($xml), TRUE);
	if ( $array["SubmitFeedResult"]["FeedSubmissionInfo"]["FeedProcessingStatus"]!="_SUBMITTED_" )
	{
		echo 'ERROR:';
		print_r($array);
	}
	
	echo '<AmazonUpdatePricesJobResponse>';
	echo '	<Ack>Success</Ack>';
	echo '</AmazonUpdatePricesJobResponse>';
?>