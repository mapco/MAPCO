<?php
	/*****************************************************************
	 * set gross weight for items who have net weight and dimensions *
	 *****************************************************************/
	include("config.php");
	
	$i=0;
	$user=array();
	$results=q("SELECT * FROM shop_carts WHERE lastmod>".(time()-10*3600).";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$user[$row["session_id"]]+=1;
	}
	print_r($user);
	
?>