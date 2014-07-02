<?php
	include("config.php");
	include("functions/shop_get_prices.php");
	
	$i=0;
	$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//gelbe Liste
		$results2=q("SELECT * FROM prpos WHERE ARTNR='".$row["MPN"]."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		
		//eBay-Preis
		$prices=get_prices($row["id_item"], 1, 27991);

		if( $prices["net"]<$row2["POS_0_WERT"] )
		{
			$results3=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$row["id_item"]." AND firstmod_user=21371;", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results3)==0 )
			{
				$i++;
				echo $i.': '.$row["MPN"].' '.$prices["gross"].'<br />';
				q("INSERT INTO shop_price_suggestions (item_id, price, status, imported, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$row["id_item"].", ".$prices["gross"].", 0, 0, ".time().", 21371, ".time().", 21371);", $dbshop, __FILE__, __LINE__);
			}
		}
	}

?>