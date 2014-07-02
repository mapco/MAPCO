<?php

	//clear cart
	$results=q("DELETE FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);

?>