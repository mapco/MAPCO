<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("id_list"		=> "numeric",
					"offer_start"	=> "numeric",
					"offer_end"		=> "numeric",
					"percentage"	=> "numeric");
	
	check_man_params($required);
	
	$data=array();
	$data["offer_start"]=$_POST["offer_start"];
	$data["offer_end"]=$_POST["offer_end"];
	$data["percentage"]=$_POST["percentage"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	
	$res=q("SELECT * FROM shop_offers WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		$res_update=q_update("shop_offers", $data, "WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$data["firstmod"]=time();
		$data["firstmod_user"]=$_SESSION["id_user"];
		$data["list_id"]=$_POST["id_list"];
		$res_insert=q_insert("shop_offers", $data, $dbshop, __FILE__, __LINE__);
	}
	
	//$res=q_update("shop_lists", $data, "WHERE id_list=".$_POST["id_list"], $dbshop, __FILE__, __LINE__);
	
	$active=0;
	if($_POST["offer_start"]<time() and $_POST["offer_end"]>time())
		$active=1;
		
	$xml='<active><![CDATA['.$active.']]></active>'."\n";
	
	echo $xml;
	
?>