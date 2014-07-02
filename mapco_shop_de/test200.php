<?php

	/**************************************************
	 * IMPORTS AMAZON PRICES INTO shop_price_research *
	 **************************************************/

	include("config.php");

	if( !isset($_GET["ASIN"]) ) $_GET["ASIN"]="B003FFOBSE";
	$response=post("http://www.amazon.de/gp/offer-listing/".$_GET["ASIN"], array());
	
	while( $pos = strpos($response, '<span class="a-size-large a-color-price olpOfferPrice a-text-bold">') )
	{
		//cut beginning
		$response=substr($response, $pos+67);
		
		//get price
		$price=substr($response, 0, strpos($response, '</span>'));
		$price=str_replace("EUR", "", $price);
		echo $price=trim($price);
		echo '<br />';
		
		//get shipping costs
		$pos = strpos($response, '<span class="olpShippingPrice">')+31;
		$response=substr($response, $pos);
		$shipping=substr($response, 0, strpos($response, '</span>'));
		$shipping=str_replace("EUR", "", $shipping);
		echo $shipping=trim($shipping);
		echo '<br />';
		
		
		//get seller reference code
		$pos = strpos($response, '<a href="http://www.amazon.de/shops/')+36;
		$response=substr($response, $pos);
		$sellerref=substr($response, 0, strpos($response, '/ref='));
		
		//get seller name
		$results=q("SELECT * FROM amazon_sellers WHERE SellerRef='".$sellerref."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			$response2=post("http://www.amazon.de/gp/aag/details/ref=olp_merch_cust_glance_1?ie=UTF8&asin=B003FFOBSE&isAmazonFulfilled=0&seller=".$sellerref, array());
			$pos = strpos($response2, '<div class="sellerLogo">')+22;
			$response2=substr($response2, $pos);
			$pos = strpos($response2, 'alt="')+5;
			$response2=substr($response2, $pos);
			$seller=substr($response2, 0, strpos($response2, '" height='));
			$data=array();
			$data["SellerRef"]=$sellerref;
			$data["SellerName"]=$seller;
			q_insert(amazon_sellers, $data, $dbshop, __FILE__, __LINE__);
			$results=q("SELECT * FROM amazon_sellers WHERE SellerRef='".$sellerref."';", $dbshop, __FILE__, __LINE__);
		}
		$row=mysqli_fetch_array($results);
		echo $seller=$row["SellerName"];
		echo '<br />';
		echo '<br />';
	}
	
    
?>