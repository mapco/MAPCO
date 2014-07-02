<?php
	include("config.php");

	$results=q("SELECT * FROM shop_price_suggestions WHERE status=0 AND firstmod_user=21371;", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		//gelbe Liste
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM prpos WHERE ARTNR='".$row2["MPN"]."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$gelb_brutto=$row2["POS_0_WERT"]*1.19;
		
		if( ($row["price"]+1)>=$gelb_brutto )
		{
			$i++;
			echo $i.' '.$row["item_id"].'<br />';
//			q("UPDATE shop_price_suggestions SET price=".$gelb_brutto.", status=4, lastmod=".time().", lastmod_user=21371 WHERE id_pricesuggestion=".$row["id_pricesuggestion"].";", $dbshop, __FILE__, __LINE__);
		}
	}
	exit;
?>