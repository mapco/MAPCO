<?php

	$required=array("order_id" => "numericNN");
	check_man_params($required);					


	$user=array();
	$user[1]="SYSTEM";
	$xmldata='';
	
	$events=array();
	$res_eventtypes=q("SELECT * FROM shop_orders_events_types;", $dbshop, __FILE__, __LINE__);
	while($row_eventtypes=mysqli_fetch_array($res_eventtypes))
	{
		$events[$row_eventtypes["id_eventtype"]]=$row_eventtypes["title"];

	}
	
	$res=q("SELECT * FROM shop_orders_events WHERE order_id = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($res))
	{
		$xmldata.='<event>'."\n";
		$xmldata.='	<id_event>'.$row["id_event"].'</id_event>'."\n";
		$xmldata.='	<order_id>'.$row["order_id"].'</order_id>'."\n";
		$xmldata.='	<eventtype_id>'.$row["eventtype_id"].'</eventtype_id>'."\n";
		$xmldata.='	<eventtitle><![CDATA['.$events[$row["eventtype_id"]].']]></eventtitle>'."\n";
		$xmldata.='	<firstmod>'.$row["firstmod"].'</firstmod>'."\n";
		$xmldata.='	<firstmod_user>'.$row["firstmod_user"].'</firstmod_user>'."\n";
		
		if (!isset($user[$row["firstmod_user"]]))
		{
			$res_user=q("SELECT * FROM cms_users WHERE id_user = ".$row["firstmod_user"].";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_user)>0)
			{
				$row_user=mysqli_fetch_array($res_user);
				$user[$row["firstmod_user"]]=$row_user["name"];
			}
		}
		$xmldata.='	<username><![CDATA['.$user[$row["firstmod_user"]].']]></username>'."\n";
		$xmldata.='</event>'."\n";
	}
	
	//serviceresponse
	echo $xmldata;

?>