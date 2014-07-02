<?php
	check_man_params( array("item_id" => "numericNN", "pricelist" => "numericNN", "price" => "numericNN", "link" => "text") );

	$data=$_POST;
	unset($data["API"]);
	unset($data["APIRequest"]);
	$data["expires"]=time()+(3*31*24*3600); //3 months expiration
	$data["firstmod"]=time();
	$data["firstmod_user"]=$_SESSION["id_user"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_insert("shop_price_research", $data, $dbshop, __FILE__, __LINE__);

?>