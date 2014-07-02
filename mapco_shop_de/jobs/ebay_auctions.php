<?php
	include("../config.php");
	
	/*********************************************************************
	 * Checks for ebay auctions to be closed because of low availability *
	 *********************************************************************/

	$results=q("SELECT * FROM ebay_auctions LIMIT 1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results=q("SELECT * FROM shop_items WHERE id_item=".$row["shopitem_id"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		
		$results=q("SELECT * FROM lager WHERE ArtNr='".$row["MPN"]."';", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		print_r($row);
	}
	 
?>