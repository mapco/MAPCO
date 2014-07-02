<?php
	/*****************************************
	 * find items with more than 15 auctions *
	 *****************************************/
	require_once("config.php");
	
	$items=array();
	$results=q("SELECT SKU FROM ebay_auctions WHERE account_id=2;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( !isset($items[$row["SKU"]]) ) $items[$row["SKU"]]=1;
		else $items[$row["SKU"]]++;
	}

	$keys=array_keys($items);
	foreach($keys as $key)
	{
		if ( $items[$key]>15 )
		{
			echo $key.'<br />';
		}
	}
?>