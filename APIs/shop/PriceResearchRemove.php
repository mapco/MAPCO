<?php
	check_man_params( array("id" => "numericNN") );

	$data=$_POST;
	unset($data["API"]);
	unset($data["APIRequest"]);
	$data["expires"]=time();
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_update("shop_price_research", $data, "WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);

?>