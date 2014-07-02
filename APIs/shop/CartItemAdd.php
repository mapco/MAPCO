<?php

	check_man_params(array(	"item_id" => "numeric",));
	
	$results=q("INSERT INTO shop_carts (item_id, amount, shop_id, session_id, user_id, customer_vehicle_id, lastmod) VALUES('".$_POST["item_id"]."', '1', ".$_SESSION["id_shop"].", '".session_id()."', '".$_SESSION["id_user"]."', 0, ".time().");", $dbshop, __FILE__, __LINE__);
?>
