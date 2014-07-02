<?php
	include("../config.php");
	include("../modules/cms_functions.php");

	//leere Bestellungen entfernen
	/*
	$results=q("SELECT * FROM shop_orders;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results2)==0) echo $row["id_order"].'<br />';
	}
	exit();
	*/

	//clear tables
	
	
	$results=q("SELECT * FROM shop_orders WHERE status_id=0;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		mail_order($row["id_order"]);
	}
?>