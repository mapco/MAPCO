<?php

	$handle=fopen("PriceSugestionsExport.csv", "w");
	echo '<PriceSuggestionResponse>'."\n";
	$results=q("SELECT * FROM shop_price_suggestions WHERE imported=0 AND (status=1 OR status=2 OR status=4);", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$price_ap=round($row["price"]/119*100, 2);
		$price_mapco=round($price_ap*1.1, 2);
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		fwrite($handle, '"'.$row2["MPN"].'";"'.str_replace(".", ",", $price_ap).'";"'.$row2["MPN"].'";"'.str_replace(".", ",", $price_mapco).'"'."\n");
		echo '	<Price id_pricesuggestion="'.$row["id_pricesuggestion"].'" SKU="'.$row2["MPN"].'">'.$price_ap.'</Price>'."\n";
	}
	echo '	<Ack>Success</Ack>'."\n";
	echo '</PriceSuggestionResponse>'."\n";
	fclose($handle);
?>