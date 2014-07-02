<?php
	check_man_params(array("id_auction" => "numericNN"));

	$results=q("SELECT premium FROM ebay_auctions WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);


	$data=array();
	if( $row["premium"]>0 )
	{
		$data["premium"]=0;
	}
	else
	{
		$data["premium"]=1;
	}
	q_update("ebay_auctions", $data, "WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	
?>