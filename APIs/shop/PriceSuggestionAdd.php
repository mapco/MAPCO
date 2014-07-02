<?php
	check_man_params( array("item_id" => "numericNN", "price" => "numericNN", "pricelist" => "numericNN", ) );

	//fix comma
	$_POST["price"]=str_replace(",", ".", $_POST["price"]);

	//decide status 0=pending, 1=auto accepted, 2=chief accepted, 3=rejected, 4=accepted (chief price changed)

	//if greater than yellow pricelist then auto accept
	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM prpos WHERE ArtNr='".$row["MPN"]."' AND LST_NR=5;", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		$yellow=round($row["POS_0_WERT"]*1.19, 2);
/*
		if ( $_POST["price"] > (2*$yellow) )
		{
			echo '<PriceSuggestionResponse>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Der Preis ist zu hoch.</shortMsg>'."\n";
			echo '		<longMsg>Der Preis übersteigt den gelben Preis um das doppelte und kann somit nicht real sein.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</PriceSuggestionResponse>'."\n";
			exit;
		}
*/
		if ( $_POST["price"]>=$yellow ) $status=1; else $status=0;
	}
	else
	{
		$status=1;
	}
	
	//add to table
//	$_POST["price"]=round(round($_POST["price"]/1.19, 2)*1.19, 2);
	$results=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$_POST["item_id"]." AND imported=0 AND pricelist=".$_POST["pricelist"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		$id_pricesuggestion=$row["id_pricesuggestion"];
		$data=$_POST;
		unset($data["API"]);
		unset($data["APIRequest"]);
		$data["status"]=$status;
		$data["lastmod"]=time();
		$data["lastmod_user"]=$_SESSION["id_user"];
		q_update("shop_price_suggestions", $data, "WHERE id_pricesuggestion=".$id_pricesuggestion.";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$data=$_POST;
		unset($data["API"]);
		unset($data["APIRequest"]);
		$data["status"]=$status;
		$data["firstmod"]=time();
		$data["firstmod_user"]=$_SESSION["id_user"];
		$data["lastmod"]=time();
		$data["lastmod_user"]=$_SESSION["id_user"];
		q_insert("shop_price_suggestions", $data, $dbshop, __FILE__, __LINE__);
		$id_pricesuggestion=mysqli_insert_id($dbshop);
	}

	//REMOVE FLAG FROM SHOP_ITEMS
	q("UPDATE shop_items SET InPriceResearch = 0 WHERE id_item = ".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
	
	//update in idims if possible
	if($status==1)
	{
		$postdata=array();
		$postdata["API"]="idims";
		$postdata["Action"]="PriceUpdate";
		$postdata["id_pricesuggestion"]=$id_pricesuggestion;
		post(PATH."soa/", $postdata);
	}

	echo '<id_pricesuggestion>'.$id_pricesuggestion.'</id_pricesuggestion>'."\n";
	echo '<status>'.$status.'</status>'."\n";
?>