<?php
	session_start();
	//äöü
	if (!isset($skip ) or !$skip)
	{
		include_once("../config.php");
	}
	
	//clear cart
	$results=q("DELETE FROM shop_carts WHERE user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
?>