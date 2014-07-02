<?php
	/***************************************
	 * find auctions with wrong collateral *
	 ***************************************/
	require_once("config.php");
	
	$items=array();
	$results=q("SELECT id_auction, SKU, Description FROM ebay_auctions WHERE Description LIKE '%Altteil%';", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		$results2=q("SELECT * FROM t_200 WHERE ArtNr='".$row["SKU"]."';", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		if ($row2["ATWERT"]==0)
		{
			echo $row["SKU"].'<br />';
//			echo $i.' '.$row["SKU"].': '.$row2["ATWERT"].'<br />';
		}
	}
?>