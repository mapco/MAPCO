<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("id_list"	=> "numeric");
	
	check_man_params($required);
	
	$offer_start=0;
	$offer_end=0;
	$percentage=0;
/*	
	$res=q("SELECT * FROM shop_lists WHERE id_list=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		$shop_lists=mysqli_fetch_assoc($res);
		$offer_start=$shop_lists["offer_start"];
		$offer_end=$shop_lists["offer_end"];
	}
*/
	
	$res=q("SELECT * FROM shop_offers WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		$shop_offers=mysqli_fetch_assoc($res);
		$offer_start=$shop_offers["offer_start"];
		$offer_end=$shop_offers["offer_end"];
		$percentage=$shop_offers["percentage"];
	}
		
	$xml='';
	$xml.='<offer_start><![CDATA['.$offer_start.']]></offer_start>'."\n";
	$xml.='<offer_end><![CDATA['.$offer_end.']]></offer_end>'."\n";
	$xml.='<percentage><![CDATA['.$percentage.']]></percentage>'."\n";
	
	echo $xml;
	
?>