<?php

//SERVICE FOR SOA2

		$required=array("order_id" =>"numericNN", 
						"eventtype_id" =>"numericNN");	
		
		check_man_params($required);					
		

/*
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
*/		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $_POST["data"])."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		
	//SERVICE RESPONSE
	echo '<id_event>'.mysqli_insert_id($dbshop).'</id_event>';


?>